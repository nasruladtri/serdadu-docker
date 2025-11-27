<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\District;
use App\Models\Village;
use App\Models\DownloadLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class PublicDashboardController extends Controller
{
    private const AGE_GROUPS = [
        '00-04',
        '05-09',
        '10-14',
        '15-19',
        '20-24',
        '25-29',
        '30-34',
        '35-39',
        '40-44',
        '45-49',
        '50-54',
        '55-59',
        '60-64',
        '65-69',
        '70-74',
        '75+',
    ];

    public function landing()
    {
        return view('public.landing', $this->getLandingData());
    }

    protected function getLandingData(): array
    {
        $period = $this->latestPeriod();
        $districts = District::orderBy('name')->get(['id', 'name', 'code']);

        if (!$period) {
            $districtsForMap = $districts->map(function($district) {
                return [
                    'id' => $district->id,
                    'code' => $district->code,
                    'name' => $district->name,
                    'slug' => Str::slug($district->name),
                ];
            })->values();

            return [
                'title' => 'Beranda',
                'period' => null,
                'mapStats' => $this->emptyMapStats(),
                'districtOptions' => $districts,
                'districtsForMap' => $districtsForMap,
                'districtCount' => $districts->count(),
                'populationGrowth' => $this->populationGrowthRate(),
            ];
        }

        $gender = $this->genderSummary($period);
        $wajibKtp = $this->wajibKtpSummary($period);
        $ageGroups = $this->ageGroupSummary($period);
        $education = $this->educationSummary($period);
        $districtRanking = $this->districtRanking($period);
        $totals = [
            'population' => $gender['total'] ?? 0,
            'male' => $gender['male'] ?? 0,
            'female' => $gender['female'] ?? 0,
        ];

        $populationGrowth = $this->populationGrowthRate();

        $districtsForMap = $districts->map(function($district) {
            return [
                'id' => $district->id,
                'code' => $district->code,
                'name' => $district->name,
                'slug' => Str::slug($district->name),
            ];
        })->values();

        return [
            'title' => 'Beranda',
            'period' => $period,
            'totals' => $totals,
            'gender' => $gender,
            'wajibKtp' => $wajibKtp,
            'ageGroups' => $ageGroups,
            'districtRanking' => $districtRanking,
            'districtCount' => $districts->count(),
            'villageCount' => Village::count(),
            'education' => $education,
            'mapStats' => $this->mapPopulationSummary($period),
            'districtOptions' => $districts,
            'districts' => $districts,
            'districtsForMap' => $districtsForMap,
            'populationGrowth' => $populationGrowth,
        ];
    }

    public function data(Request $request)
    {
        return view('public.data', $this->getDataViewData($request));
    }

    protected function getDataViewData(Request $request): array
    {
        [
            'periods' => $periods,
            'period' => $period,
            'districts' => $districts,
            'villages' => $villages,
            'selectedDistrict' => $selectedDistrict,
            'selectedVillage' => $selectedVillage,
            'filters' => $filters,
            'years' => $years,
            'semesterOptions' => $availableSemesters,
            'selectedYear' => $selectedYear,
            'selectedSemester' => $selectedSemester,
        ] = $this->prepareFilterContext($request);

        $gender = $period ? $this->genderSummary($period, $filters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $wajibKtp = $period ? $this->wajibKtpSummary($period, $filters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $ageGroups = $period ? $this->ageGroupSummary($period, $filters) : [];
        $singleAges = $period ? $this->singleAgeSummary($period, $filters) : [];
        $education = $period ? $this->educationSummary($period, $filters) : [];
        $topOccupations = $period ? $this->occupationHighlights($period, $filters) : [];
        $marital = $period ? $this->maritalStatusSummary($period, $filters) : [];
        $headHouseholds = $period ? $this->headOfHouseholdSummary($period, $filters) : [];
        $religions = $period ? $this->religionSummary($period, $filters) : [];
        $areaTable = $this->areaPopulationTable($period, $filters);
        $educationMatrix = $this->educationMatrix($period, $filters);
        $wajibKtpMatrix = $this->wajibKtpMatrix($period, $filters);
        $maritalMatrix = $this->maritalMatrix($period, $filters);
        $headHouseholdMatrix = $this->headHouseholdMatrix($period, $filters);
        $religionMatrix = $this->religionMatrix($period, $filters);

        return [
            'title' => 'Data Agregat',
            'period' => $period,
            'periods' => $periods,
            'years' => $years,
            'semesterOptions' => $availableSemesters,
            'selectedYear' => $selectedYear,
            'selectedSemester' => $selectedSemester,
            'districts' => $districts,
            'villages' => $villages,
            'selectedDistrict' => $selectedDistrict,
            'selectedVillage' => $selectedVillage,
            'gender' => $gender,
            'wajibKtp' => $wajibKtp,
            'ageGroups' => $ageGroups,
            'singleAges' => $singleAges,
            'education' => $education,
            'topOccupations' => $topOccupations,
            'marital' => $marital,
            'headHouseholds' => $headHouseholds,
            'religions' => $religions,
            'areaTable' => $areaTable,
            'educationMatrix' => $educationMatrix,
            'wajibKtpMatrix' => $wajibKtpMatrix,
            'maritalMatrix' => $maritalMatrix,
            'headHouseholdMatrix' => $headHouseholdMatrix,
            'religionMatrix' => $religionMatrix,
        ];
    }

    public function fullscreen(Request $request)
    {
        $data = $this->getFullscreenViewData($request);
        return view('public.data-fullscreen', $data);
    }

    protected function getFullscreenViewData(Request $request): array
    {
        [
            'periods' => $periods,
            'period' => $period,
            'districts' => $districts,
            'villages' => $villages,
            'selectedDistrict' => $selectedDistrict,
            'selectedVillage' => $selectedVillage,
            'filters' => $filters,
            'years' => $years,
            'semesterOptions' => $availableSemesters,
            'selectedYear' => $selectedYear,
            'selectedSemester' => $selectedSemester,
        ] = $this->prepareFilterContext($request);

        $category = $this->sanitizeCategory($request->get('category'));

        $gender = $period ? $this->genderSummary($period, $filters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $wajibKtp = $period ? $this->wajibKtpSummary($period, $filters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $ageGroups = $period ? $this->ageGroupSummary($period, $filters) : [];
        $singleAges = $period ? $this->singleAgeSummary($period, $filters) : [];
        $education = $period ? $this->educationSummary($period, $filters) : [];
        $topOccupations = $period ? $this->occupationHighlights($period, $filters) : [];
        $marital = $period ? $this->maritalStatusSummary($period, $filters) : [];
        $headHouseholds = $period ? $this->headOfHouseholdSummary($period, $filters) : [];
        $religions = $period ? $this->religionSummary($period, $filters) : [];
        $areaTable = $this->areaPopulationTable($period, $filters);
        $educationMatrix = $this->educationMatrix($period, $filters);
        $wajibKtpMatrix = $this->wajibKtpMatrix($period, $filters);
        $maritalMatrix = $this->maritalMatrix($period, $filters);
        $headHouseholdMatrix = $this->headHouseholdMatrix($period, $filters);
        $religionMatrix = $this->religionMatrix($period, $filters);

        return [
            'title' => 'Data Agregat - Fullscreen',
            'period' => $period,
            'periods' => $periods,
            'years' => $years,
            'semesterOptions' => $availableSemesters,
            'selectedYear' => $selectedYear,
            'selectedSemester' => $selectedSemester,
            'districts' => $districts,
            'villages' => $villages,
            'selectedDistrict' => $selectedDistrict,
            'selectedVillage' => $selectedVillage,
            'gender' => $gender,
            'wajibKtp' => $wajibKtp,
            'ageGroups' => $ageGroups,
            'singleAges' => $singleAges,
            'education' => $education,
            'topOccupations' => $topOccupations,
            'marital' => $marital,
            'headHouseholds' => $headHouseholds,
            'religions' => $religions,
            'areaTable' => $areaTable,
            'educationMatrix' => $educationMatrix,
            'wajibKtpMatrix' => $wajibKtpMatrix,
            'maritalMatrix' => $maritalMatrix,
            'headHouseholdMatrix' => $headHouseholdMatrix,
            'religionMatrix' => $religionMatrix,
            'category' => $category,
        ];
    }

    public function charts(Request $request)
    {
        return view('public.charts', $this->getChartsViewData($request));
    }

    protected function getChartsViewData(Request $request): array
    {
        [
            'periods' => $periods,
            'period' => $period,
            'districts' => $districts,
            'villages' => $villages,
            'selectedDistrict' => $selectedDistrict,
            'selectedVillage' => $selectedVillage,
            'filters' => $filters,
            'years' => $years,
            'semesterOptions' => $availableSemesters,
            'selectedYear' => $selectedYear,
            'selectedSemester' => $selectedSemester,
        ] = $this->prepareFilterContext($request);

        $gender = $period ? $this->genderSummary($period, $filters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $wajibKtp = $period ? $this->wajibKtpSummary($period, $filters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $ageGroups = $period ? $this->ageGroupSummary($period, $filters) : [];
        $singleAges = $period ? $this->singleAgeSummary($period, $filters) : [];
        $education = $period ? $this->educationSummary($period, $filters) : [];
        $topOccupations = $period ? $this->occupationHighlights($period, $filters) : [];
        $marital = $period ? $this->maritalStatusSummary($period, $filters) : [];
        $headHouseholds = $period ? $this->headOfHouseholdSummary($period, $filters) : [];
        $religions = $period ? $this->religionSummary($period, $filters) : [];

        $chartTitles = [
            'gender' => 'Jenis Kelamin',
            'age' => 'Kelompok Umur',
            'single-age' => 'Umur Tunggal',
            'education' => 'Pendidikan',
            'occupation' => 'Pekerjaan',
            'marital' => 'Status Perkawinan',
            'household' => 'Kepala Keluarga',
            'religion' => 'Agama',
            'wajib-ktp' => 'Wajib KTP',
        ];
        $chartsNeedingTags = [
            'age',
            'single-age',
            'education',
            'occupation',
            'marital',
            'household',
            'religion',
        ];
        $chartsAngledTags = [
            'single-age',
            'occupation',
        ];

        $charts = [
            'gender' => $this->buildGenderChart($chartTitles['gender'], $gender),
            'age' => $this->buildSeriesChart($chartTitles['age'], $ageGroups),
            'single-age' => $this->buildSeriesChart($chartTitles['single-age'], $singleAges),
            'education' => $this->buildSeriesChart($chartTitles['education'], $education),
            'occupation' => $this->buildSeriesChart($chartTitles['occupation'], $topOccupations),
            'marital' => $this->buildSeriesChart($chartTitles['marital'], $marital),
            'household' => $this->buildSeriesChart($chartTitles['household'], $headHouseholds),
            'religion' => $this->buildSeriesChart($chartTitles['religion'], $religions),
            'wajib-ktp' => $this->buildWajibKtpChart($chartTitles['wajib-ktp'], $wajibKtp),
        ];

        return [
            'title' => 'Grafik Data',
            'period' => $period,
            'periods' => $periods,
            'years' => $years,
            'semesterOptions' => $availableSemesters,
            'selectedYear' => $selectedYear,
            'selectedSemester' => $selectedSemester,
            'districts' => $districts,
            'villages' => $villages,
            'selectedDistrict' => $selectedDistrict,
            'selectedVillage' => $selectedVillage,
            'charts' => $charts,
            'chartTitles' => $chartTitles,
            'chartsNeedingTags' => $chartsNeedingTags,
            'chartsAngledTags' => $chartsAngledTags,
        ];
    }

    public function compare(Request $request)
    {
        return view('public.compare', $this->getCompareViewData($request));
    }

    protected function getCompareViewData(Request $request): array
    {
        // Get all periods
        $periods = $this->availablePeriods();
        $districts = District::orderBy('name')->get();
        $years = collect($periods)->pluck('year')->unique()->sortDesc()->values()->all();
        
        // Get all available semesters (not filtered by year) - untuk dropdown compare
        $allAvailableSemesters = collect($periods)
            ->pluck('semester')
            ->unique()
            ->sort()
            ->values()
            ->all();

        // Prepare context untuk primary data
        $primaryYear = $request->input('year');
        $primarySemester = $request->input('semester');
        $primaryDistrict = $request->input('district_id');
        $primaryVillage = $request->input('village_id');
        
        // Normalize inputs - treat empty strings as null, but preserve numeric values
        $primaryYear = ($primaryYear === '' || $primaryYear === null) ? null : (int) $primaryYear;
        $primarySemester = ($primarySemester === '' || $primarySemester === null) ? null : (int) $primarySemester;
        $primaryDistrict = ($primaryDistrict === '' || $primaryDistrict === null) ? null : (int) $primaryDistrict;
        $primaryVillage = ($primaryVillage === '' || $primaryVillage === null) ? null : (int) $primaryVillage;

        // Get available semesters for primary (filtered by selected year if year is selected)
        // Jika tahun sudah dipilih, hanya tampilkan semester untuk tahun tersebut
        // Jika tahun belum dipilih, kosongkan agar view bisa menggunakan $allAvailableSemesters
        if ($primaryYear !== null) {
            $primaryAvailableSemesters = collect($periods)
                ->where('year', (int)$primaryYear)
                ->pluck('semester')
                ->unique()
                ->sort()
                ->values()
                ->all();
        } else {
            $primaryAvailableSemesters = [];
        }

        // Get primary period - hanya resolve jika tahun dan semester keduanya ada DAN semester tersebut valid untuk tahun tersebut
        $primaryPeriod = null;
        $primarySemesterValid = true;
        if ($primaryYear && $primarySemester) {
            // Pastikan semester yang dipilih benar-benar ada untuk tahun yang dipilih
            $isValidSemester = empty($primaryAvailableSemesters) || in_array((int)$primarySemester, $primaryAvailableSemesters);
            if ($isValidSemester) {
                // Cari periode yang sesuai dengan input user
                foreach ($periods as $period) {
                    if ($period['year'] == (int)$primaryYear && $period['semester'] == (int)$primarySemester) {
                        $primaryPeriod = $period;
                        break;
                    }
                }
            } else {
                // Semester tidak valid untuk tahun tersebut
                $primarySemesterValid = false;
                // Reset semester yang tidak valid
                $primarySemester = null;
            }
            // Jika semester tidak valid untuk tahun tersebut, $primaryPeriod tetap null sehingga data tidak ditampilkan
        } else {
            // Jika tidak ada input, gunakan resolvePeriod untuk mendapatkan periode terbaru
            $primaryPeriod = $this->resolvePeriod($primaryYear, $primarySemester, $periods);
        }

        // Get primary villages
        $primaryVillages = collect();
        if ($primaryDistrict) {
            $primaryVillages = Village::where('district_id', $primaryDistrict)->orderBy('name')->get();
            // Validate: if village is selected but not valid for the selected district, reset it
            if ($primaryVillage && !$primaryVillages->contains('id', (int)$primaryVillage)) {
                $primaryVillage = null;
            }
        } else {
            // If no district is selected, reset village
            $primaryVillage = null;
        }

        $primaryFilters = [
            'district_id' => $primaryDistrict ? (int) $primaryDistrict : null,
            'village_id' => $primaryVillage ? (int) $primaryVillage : null,
        ];

        // Prepare context untuk compare data (dari query parameter compare_*)
        $compareYear = $request->input('compare_year');
        $compareSemester = $request->input('compare_semester');
        $compareDistrict = $request->input('compare_district_id');
        $compareVillage = $request->input('compare_village_id');
        
        // Normalize inputs - treat empty strings as null, but preserve numeric values
        $compareYear = ($compareYear === '' || $compareYear === null) ? null : (int) $compareYear;
        $compareSemester = ($compareSemester === '' || $compareSemester === null) ? null : (int) $compareSemester;
        $compareDistrict = ($compareDistrict === '' || $compareDistrict === null) ? null : (int) $compareDistrict;
        $compareVillage = ($compareVillage === '' || $compareVillage === null) ? null : (int) $compareVillage;

        // Get available semesters for compare (filtered by selected year if year is selected)
        // Jika tahun sudah dipilih, hanya tampilkan semester untuk tahun tersebut
        // Jika tahun belum dipilih, kosongkan agar view bisa menggunakan $allAvailableSemesters
        if ($compareYear !== null) {
            $compareAvailableSemesters = collect($periods)
                ->where('year', (int)$compareYear)
                ->pluck('semester')
                ->unique()
                ->sort()
                ->values()
                ->all();
        } else {
            $compareAvailableSemesters = [];
        }

        // Resolve compare period - hanya resolve jika tahun dan semester keduanya ada DAN semester tersebut valid untuk tahun tersebut
        $comparePeriod = null;
        $compareSemesterValid = true;
        if ($compareYear && $compareSemester) {
            // Pastikan semester yang dipilih benar-benar ada untuk tahun yang dipilih
            $isValidSemester = !empty($compareAvailableSemesters) && in_array((int)$compareSemester, $compareAvailableSemesters);
            if ($isValidSemester) {
                // Cari periode yang sesuai dengan input user
                foreach ($periods as $period) {
                    if ($period['year'] == (int)$compareYear && $period['semester'] == (int)$compareSemester) {
                        $comparePeriod = $period;
                        break;
                    }
                }
            } else {
                // Semester tidak valid untuk tahun tersebut
                $compareSemesterValid = false;
                // Reset semester yang tidak valid
                $compareSemester = null;
            }
            // Jika semester tidak valid untuk tahun tersebut, $comparePeriod tetap null sehingga data tidak ditampilkan
        }

        // Get compare villages if district is selected
        $compareVillages = collect();
        if ($compareDistrict) {
            $compareVillages = Village::where('district_id', $compareDistrict)
                ->orderBy('name')
                ->get();
            // Validate: if village is selected but not valid for the selected district, reset it
            if ($compareVillage && !$compareVillages->contains('id', (int)$compareVillage)) {
                $compareVillage = null;
            }
        } else {
            // If no district is selected, reset village
            $compareVillage = null;
        }

        // Prepare compare filters (after validation)
        $compareFilters = [
            'district_id' => $compareDistrict ? (int) $compareDistrict : null,
            'village_id' => $compareVillage ? (int) $compareVillage : null,
        ];

        // Get data for primary
        $primaryGender = $primaryPeriod ? $this->genderSummary($primaryPeriod, $primaryFilters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $primaryWajibKtp = $primaryPeriod ? $this->wajibKtpSummary($primaryPeriod, $primaryFilters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $primaryAgeGroups = $primaryPeriod ? $this->ageGroupSummary($primaryPeriod, $primaryFilters) : [];
        $primarySingleAges = $primaryPeriod ? $this->singleAgeSummary($primaryPeriod, $primaryFilters) : [];
        $primaryEducation = $primaryPeriod ? $this->educationSummary($primaryPeriod, $primaryFilters) : [];
        $primaryOccupations = $primaryPeriod ? $this->occupationHighlights($primaryPeriod, $primaryFilters) : [];
        $primaryMarital = $primaryPeriod ? $this->maritalStatusSummary($primaryPeriod, $primaryFilters) : [];
        $primaryHeadHouseholds = $primaryPeriod ? $this->headOfHouseholdSummary($primaryPeriod, $primaryFilters) : [];
        $primaryReligions = $primaryPeriod ? $this->religionSummary($primaryPeriod, $primaryFilters) : [];

        // Get data for compare
        $compareGender = $comparePeriod ? $this->genderSummary($comparePeriod, $compareFilters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $compareWajibKtp = $comparePeriod ? $this->wajibKtpSummary($comparePeriod, $compareFilters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $compareAgeGroups = $comparePeriod ? $this->ageGroupSummary($comparePeriod, $compareFilters) : [];
        $compareSingleAges = $comparePeriod ? $this->singleAgeSummary($comparePeriod, $compareFilters) : [];
        $compareEducation = $comparePeriod ? $this->educationSummary($comparePeriod, $compareFilters) : [];
        $compareOccupations = $comparePeriod ? $this->occupationHighlights($comparePeriod, $compareFilters) : [];
        $compareMarital = $comparePeriod ? $this->maritalStatusSummary($comparePeriod, $compareFilters) : [];
        $compareHeadHouseholds = $comparePeriod ? $this->headOfHouseholdSummary($comparePeriod, $compareFilters) : [];
        $compareReligions = $comparePeriod ? $this->religionSummary($comparePeriod, $compareFilters) : [];

        $chartTitles = [
            'gender' => 'Jenis Kelamin',
            'age' => 'Kelompok Umur',
            'single-age' => 'Umur Tunggal',
            'education' => 'Pendidikan',
            'occupation' => 'Pekerjaan',
            'marital' => 'Status Perkawinan',
            'household' => 'Kepala Keluarga',
            'religion' => 'Agama',
            'wajib-ktp' => 'Wajib KTP',
        ];

        $chartsNeedingTags = [
            'age',
            'marital',
            'household',
            'religion',
        ];

        $chartsAngledTags = [];

        $horizontalChartKeys = [
            'single-age',
            'education',
            'occupation',
        ];

        // Build charts for primary
        $primaryCharts = [
            'gender' => $this->buildGenderChart($chartTitles['gender'], $primaryGender),
            'age' => $this->buildSeriesChart($chartTitles['age'], $primaryAgeGroups),
            'single-age' => $this->buildSeriesChart($chartTitles['single-age'], $primarySingleAges),
            'education' => $this->buildSeriesChart($chartTitles['education'], $primaryEducation),
            'occupation' => $this->buildSeriesChart($chartTitles['occupation'], $primaryOccupations),
            'marital' => $this->buildSeriesChart($chartTitles['marital'], $primaryMarital),
            'household' => $this->buildSeriesChart($chartTitles['household'], $primaryHeadHouseholds),
            'religion' => $this->buildSeriesChart($chartTitles['religion'], $primaryReligions),
            'wajib-ktp' => $this->buildWajibKtpChart($chartTitles['wajib-ktp'], $primaryWajibKtp),
        ];

        // Build charts for compare
        $compareCharts = [
            'gender' => $this->buildGenderChart($chartTitles['gender'], $compareGender),
            'age' => $this->buildSeriesChart($chartTitles['age'], $compareAgeGroups),
            'single-age' => $this->buildSeriesChart($chartTitles['single-age'], $compareSingleAges),
            'education' => $this->buildSeriesChart($chartTitles['education'], $compareEducation),
            'occupation' => $this->buildSeriesChart($chartTitles['occupation'], $compareOccupations),
            'marital' => $this->buildSeriesChart($chartTitles['marital'], $compareMarital),
            'household' => $this->buildSeriesChart($chartTitles['household'], $compareHeadHouseholds),
            'religion' => $this->buildSeriesChart($chartTitles['religion'], $compareReligions),
            'wajib-ktp' => $this->buildWajibKtpChart($chartTitles['wajib-ktp'], $compareWajibKtp),
        ];

        return [
            'title' => 'Perbandingan Data',
            'primaryPeriod' => $primaryPeriod,
            'comparePeriod' => $comparePeriod,
            'periods' => $periods,
            'years' => $years,
            'primaryAvailableSemesters' => $primaryAvailableSemesters,
            'compareAvailableSemesters' => $compareAvailableSemesters,
            'allAvailableSemesters' => $allAvailableSemesters,
            'primaryYear' => $primaryYear,
            'primarySemester' => $primarySemester,
            'compareYear' => $compareYear,
            'compareSemester' => $compareSemester,
            'districts' => $districts,
            'primaryVillages' => $primaryVillages,
            'compareVillages' => $compareVillages,
            'primaryDistrict' => $primaryDistrict,
            'primaryVillage' => $primaryVillage,
            'compareDistrict' => $compareDistrict,
            'compareVillage' => $compareVillage,
            'primaryCharts' => $primaryCharts,
            'compareCharts' => $compareCharts,
            'chartTitles' => $chartTitles,
            'chartsNeedingTags' => $chartsNeedingTags,
            'chartsAngledTags' => $chartsAngledTags,
            'horizontalChartKeys' => $horizontalChartKeys,
        ];
    }

    public function chartsFullscreen(Request $request)
    {
        $data = $this->getChartsFullscreenViewData($request);
        return view('public.charts-fullscreen', $data);
    }

    protected function getChartsFullscreenViewData(Request $request): array
    {
        [
            'periods' => $periods,
            'period' => $period,
            'districts' => $districts,
            'villages' => $villages,
            'selectedDistrict' => $selectedDistrict,
            'selectedVillage' => $selectedVillage,
            'filters' => $filters,
            'years' => $years,
            'semesterOptions' => $availableSemesters,
            'selectedYear' => $selectedYear,
            'selectedSemester' => $selectedSemester,
        ] = $this->prepareFilterContext($request);

        $category = $this->sanitizeCategory($request->get('category'));

        $gender = $period ? $this->genderSummary($period, $filters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $wajibKtp = $period ? $this->wajibKtpSummary($period, $filters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $ageGroups = $period ? $this->ageGroupSummary($period, $filters) : [];
        $singleAges = $period ? $this->singleAgeSummary($period, $filters) : [];
        $education = $period ? $this->educationSummary($period, $filters) : [];
        $topOccupations = $period ? $this->occupationHighlights($period, $filters) : [];
        $marital = $period ? $this->maritalStatusSummary($period, $filters) : [];
        $headHouseholds = $period ? $this->headOfHouseholdSummary($period, $filters) : [];
        $religions = $period ? $this->religionSummary($period, $filters) : [];

        $chartTitles = [
            'gender' => 'Jenis Kelamin',
            'age' => 'Kelompok Umur',
            'single-age' => 'Umur Tunggal',
            'education' => 'Pendidikan',
            'occupation' => 'Pekerjaan',
            'marital' => 'Status Perkawinan',
            'household' => 'Kepala Keluarga',
            'religion' => 'Agama',
            'wajib-ktp' => 'Wajib KTP',
        ];
        $chartsNeedingTags = [
            'age',
            'single-age',
            'education',
            'occupation',
            'marital',
            'household',
            'religion',
        ];
        $chartsAngledTags = [
            'single-age',
            'occupation',
        ];

        $charts = [
            'gender' => $this->buildGenderChart($chartTitles['gender'], $gender),
            'age' => $this->buildSeriesChart($chartTitles['age'], $ageGroups),
            'single-age' => $this->buildSeriesChart($chartTitles['single-age'], $singleAges),
            'education' => $this->buildSeriesChart($chartTitles['education'], $education),
            'occupation' => $this->buildSeriesChart($chartTitles['occupation'], $topOccupations),
            'marital' => $this->buildSeriesChart($chartTitles['marital'], $marital),
            'household' => $this->buildSeriesChart($chartTitles['household'], $headHouseholds),
            'religion' => $this->buildSeriesChart($chartTitles['religion'], $religions),
            'wajib-ktp' => $this->buildWajibKtpChart($chartTitles['wajib-ktp'], $wajibKtp),
        ];

        return [
            'title' => 'Grafik Data - Fullscreen',
            'period' => $period,
            'periods' => $periods,
            'years' => $years,
            'semesterOptions' => $availableSemesters,
            'selectedYear' => $selectedYear,
            'selectedSemester' => $selectedSemester,
            'districts' => $districts,
            'villages' => $villages,
            'selectedDistrict' => $selectedDistrict,
            'selectedVillage' => $selectedVillage,
            'charts' => $charts,
            'chartTitles' => $chartTitles,
            'chartsNeedingTags' => $chartsNeedingTags,
            'chartsAngledTags' => $chartsAngledTags,
            'category' => $category,
        ];
    }

    public function compareFullscreen(Request $request)
    {
        $data = $this->getCompareFullscreenViewData($request);
        return view('public.compare-fullscreen', $data);
    }

    protected function getCompareFullscreenViewData(Request $request): array
    {
        // Get all periods
        $periods = $this->availablePeriods();
        $districts = District::orderBy('name')->get();
        $years = collect($periods)->pluck('year')->unique()->sortDesc()->values()->all();
        
        // Get all available semesters (not filtered by year) - untuk dropdown compare
        $allAvailableSemesters = collect($periods)
            ->pluck('semester')
            ->unique()
            ->sort()
            ->values()
            ->all();

        // Prepare context untuk primary data
        $primaryYear = $request->input('year');
        $primarySemester = $request->input('semester');
        $primaryDistrict = $request->input('district_id');
        $primaryVillage = $request->input('village_id');
        
        // Normalize inputs - treat empty strings as null, but preserve numeric values
        $primaryYear = ($primaryYear === '' || $primaryYear === null) ? null : (int) $primaryYear;
        $primarySemester = ($primarySemester === '' || $primarySemester === null) ? null : (int) $primarySemester;
        $primaryDistrict = ($primaryDistrict === '' || $primaryDistrict === null) ? null : (int) $primaryDistrict;
        $primaryVillage = ($primaryVillage === '' || $primaryVillage === null) ? null : (int) $primaryVillage;
        
        // Prepare context untuk compare data
        $compareYear = $request->input('compare_year');
        $compareSemester = $request->input('compare_semester');
        $compareDistrict = $request->input('compare_district_id');
        $compareVillage = $request->input('compare_village_id');
        
        // Normalize inputs
        $compareYear = ($compareYear === '' || $compareYear === null) ? null : (int) $compareYear;
        $compareSemester = ($compareSemester === '' || $compareSemester === null) ? null : (int) $compareSemester;
        $compareDistrict = ($compareDistrict === '' || $compareDistrict === null) ? null : (int) $compareDistrict;
        $compareVillage = ($compareVillage === '' || $compareVillage === null) ? null : (int) $compareVillage;

        $category = $this->sanitizeCategory($request->get('category'));

        // Resolve periods untuk primary
        $primaryPeriod = null;
        if ($primaryYear && $primarySemester) {
            $primaryPeriod = collect($periods)->first(function ($p) use ($primaryYear, $primarySemester) {
                return $p['year'] === $primaryYear && $p['semester'] === $primarySemester;
            });
        }

        // Resolve periods untuk compare
        $comparePeriod = null;
        if ($compareYear && $compareSemester) {
            $comparePeriod = collect($periods)->first(function ($p) use ($compareYear, $compareSemester) {
                return $p['year'] === $compareYear && $p['semester'] === $compareSemester;
            });
        }

        // Get available semesters for selected years
        $primaryAvailableSemesters = $primaryYear
            ? collect($periods)
                ->where('year', $primaryYear)
                ->pluck('semester')
                ->unique()
                ->sort()
                ->values()
                ->all()
            : [];

        $compareAvailableSemesters = $compareYear
            ? collect($periods)
                ->where('year', $compareYear)
                ->pluck('semester')
                ->unique()
                ->sort()
                ->values()
                ->all()
            : [];

        // Get villages untuk primary
        $primaryVillages = $primaryDistrict
            ? Village::where('district_id', $primaryDistrict)->orderBy('name')->get()
            : collect();

        // Get villages untuk compare
        $compareVillages = $compareDistrict
            ? Village::where('district_id', $compareDistrict)->orderBy('name')->get()
            : collect();

        // Build filters
        $primaryFilters = [];
        if ($primaryDistrict) {
            $primaryFilters['district_id'] = $primaryDistrict;
        }
        if ($primaryVillage) {
            $primaryFilters['village_id'] = $primaryVillage;
        }

        $compareFilters = [];
        if ($compareDistrict) {
            $compareFilters['district_id'] = $compareDistrict;
        }
        if ($compareVillage) {
            $compareFilters['village_id'] = $compareVillage;
        }

        // Get summary data untuk primary
        $primaryGender = $primaryPeriod ? $this->genderSummary($primaryPeriod, $primaryFilters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $primaryWajibKtp = $primaryPeriod ? $this->wajibKtpSummary($primaryPeriod, $primaryFilters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $primaryAgeGroups = $primaryPeriod ? $this->ageGroupSummary($primaryPeriod, $primaryFilters) : [];
        $primarySingleAges = $primaryPeriod ? $this->singleAgeSummary($primaryPeriod, $primaryFilters) : [];
        $primaryEducation = $primaryPeriod ? $this->educationSummary($primaryPeriod, $primaryFilters) : [];
        $primaryOccupations = $primaryPeriod ? $this->occupationHighlights($primaryPeriod, $primaryFilters) : [];
        $primaryMarital = $primaryPeriod ? $this->maritalStatusSummary($primaryPeriod, $primaryFilters) : [];
        $primaryHeadHouseholds = $primaryPeriod ? $this->headOfHouseholdSummary($primaryPeriod, $primaryFilters) : [];
        $primaryReligions = $primaryPeriod ? $this->religionSummary($primaryPeriod, $primaryFilters) : [];

        // Get summary data untuk compare
        $compareGender = $comparePeriod ? $this->genderSummary($comparePeriod, $compareFilters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $compareWajibKtp = $comparePeriod ? $this->wajibKtpSummary($comparePeriod, $compareFilters) : ['male' => 0, 'female' => 0, 'total' => 0];
        $compareAgeGroups = $comparePeriod ? $this->ageGroupSummary($comparePeriod, $compareFilters) : [];
        $compareSingleAges = $comparePeriod ? $this->singleAgeSummary($comparePeriod, $compareFilters) : [];
        $compareEducation = $comparePeriod ? $this->educationSummary($comparePeriod, $compareFilters) : [];
        $compareOccupations = $comparePeriod ? $this->occupationHighlights($comparePeriod, $compareFilters) : [];
        $compareMarital = $comparePeriod ? $this->maritalStatusSummary($comparePeriod, $compareFilters) : [];
        $compareHeadHouseholds = $comparePeriod ? $this->headOfHouseholdSummary($comparePeriod, $compareFilters) : [];
        $compareReligions = $comparePeriod ? $this->religionSummary($comparePeriod, $compareFilters) : [];

        $chartTitles = [
            'gender' => 'Jenis Kelamin',
            'age' => 'Kelompok Umur',
            'single-age' => 'Umur Tunggal',
            'education' => 'Pendidikan',
            'occupation' => 'Pekerjaan',
            'marital' => 'Status Perkawinan',
            'household' => 'Kepala Keluarga',
            'religion' => 'Agama',
            'wajib-ktp' => 'Wajib KTP',
        ];

        $chartsNeedingTags = [
            'age',
            'marital',
            'household',
            'religion',
        ];

        $chartsAngledTags = [];

        $horizontalChartKeys = [
            'single-age',
            'education',
            'occupation',
        ];

        // Build charts for primary
        $primaryCharts = [
            'gender' => $this->buildGenderChart($chartTitles['gender'], $primaryGender),
            'age' => $this->buildSeriesChart($chartTitles['age'], $primaryAgeGroups),
            'single-age' => $this->buildSeriesChart($chartTitles['single-age'], $primarySingleAges),
            'education' => $this->buildSeriesChart($chartTitles['education'], $primaryEducation),
            'occupation' => $this->buildSeriesChart($chartTitles['occupation'], $primaryOccupations),
            'marital' => $this->buildSeriesChart($chartTitles['marital'], $primaryMarital),
            'household' => $this->buildSeriesChart($chartTitles['household'], $primaryHeadHouseholds),
            'religion' => $this->buildSeriesChart($chartTitles['religion'], $primaryReligions),
            'wajib-ktp' => $this->buildWajibKtpChart($chartTitles['wajib-ktp'], $primaryWajibKtp),
        ];

        // Build charts for compare
        $compareCharts = [
            'gender' => $this->buildGenderChart($chartTitles['gender'], $compareGender),
            'age' => $this->buildSeriesChart($chartTitles['age'], $compareAgeGroups),
            'single-age' => $this->buildSeriesChart($chartTitles['single-age'], $compareSingleAges),
            'education' => $this->buildSeriesChart($chartTitles['education'], $compareEducation),
            'occupation' => $this->buildSeriesChart($chartTitles['occupation'], $compareOccupations),
            'marital' => $this->buildSeriesChart($chartTitles['marital'], $compareMarital),
            'household' => $this->buildSeriesChart($chartTitles['household'], $compareHeadHouseholds),
            'religion' => $this->buildSeriesChart($chartTitles['religion'], $compareReligions),
            'wajib-ktp' => $this->buildWajibKtpChart($chartTitles['wajib-ktp'], $compareWajibKtp),
        ];

        return [
            'title' => 'Perbandingan Data - Fullscreen',
            'primaryPeriod' => $primaryPeriod,
            'comparePeriod' => $comparePeriod,
            'periods' => $periods,
            'years' => $years,
            'primaryAvailableSemesters' => $primaryAvailableSemesters,
            'compareAvailableSemesters' => $compareAvailableSemesters,
            'allAvailableSemesters' => $allAvailableSemesters,
            'primaryYear' => $primaryYear,
            'primarySemester' => $primarySemester,
            'compareYear' => $compareYear,
            'compareSemester' => $compareSemester,
            'districts' => $districts,
            'primaryVillages' => $primaryVillages,
            'compareVillages' => $compareVillages,
            'primaryDistrict' => $primaryDistrict,
            'primaryVillage' => $primaryVillage,
            'compareDistrict' => $compareDistrict,
            'compareVillage' => $compareVillage,
            'primaryCharts' => $primaryCharts,
            'compareCharts' => $compareCharts,
            'chartTitles' => $chartTitles,
            'chartsNeedingTags' => $chartsNeedingTags,
            'chartsAngledTags' => $chartsAngledTags,
            'horizontalChartKeys' => $horizontalChartKeys,
            'category' => $category,
        ];
    }

    protected function availablePeriods(): array
    {
        return DB::table('pop_age_group')
            ->select('year', 'semester')
            ->groupBy('year', 'semester')
            ->orderByDesc('year')
            ->orderByDesc('semester')
            ->get()
            ->map(function ($row) {
                return [
                    'year' => (int) $row->year,
                    'semester' => (int) $row->semester,
                ];
            })
            ->toArray();
    }

    protected function prepareFilterContext(Request $request): array
    {
        $periods = $this->availablePeriods();
        
        // Get inputs from request - treat empty strings as null
        $yearInput = $request->input('year');
        $semesterInput = $request->input('semester');
        $yearInput = ($yearInput === '' || $yearInput === null) ? null : $yearInput;
        $semesterInput = ($semesterInput === '' || $semesterInput === null) ? null : $semesterInput;

        $districts = District::orderBy('name')->get();

        $selectedDistrict = $request->input('district_id');
        $selectedVillage = $request->input('village_id');
        $selectedDistrict = ($selectedDistrict === '' || $selectedDistrict === null) ? null : $selectedDistrict;
        $selectedVillage = ($selectedVillage === '' || $selectedVillage === null) ? null : $selectedVillage;

        if ($selectedDistrict && !$districts->contains('id', (int) $selectedDistrict)) {
            $selectedDistrict = null;
        }

        $villages = collect();
        if ($selectedDistrict) {
            $villages = Village::where('district_id', $selectedDistrict)
                ->orderBy('name')
                ->get();
            if ($selectedVillage && !$villages->contains('id', (int) $selectedVillage)) {
                $selectedVillage = null;
            }
        } else {
            $selectedVillage = null;
        }

        $filters = [
            'district_id' => $selectedDistrict ? (int) $selectedDistrict : null,
            'village_id' => $selectedVillage ? (int) $selectedVillage : null,
        ];

        $years = collect($periods)->pluck('year')->unique()->sortDesc()->values()->all();
        
        // Get all available semesters (not filtered by year) for initial dropdown
        $allAvailableSemesters = collect($periods)
            ->pluck('semester')
            ->unique()
            ->sort()
            ->values()
            ->all();
        
        // Only set selected values if inputs were provided (not empty)
        $selectedYear = $yearInput !== null ? (int) $yearInput : null;
        $selectedSemester = $semesterInput !== null ? (int) $semesterInput : null;
        
        // Get available semesters based on selected year (if year is selected)
        $availableSemesters = collect($periods)
            ->when($selectedYear, fn($c) => $c->where('year', $selectedYear))
            ->pluck('semester')
            ->unique()
            ->sort()
            ->values()
            ->all();
        
        // If no year is selected, show all available semesters
        if (!$selectedYear) {
            $availableSemesters = $allAvailableSemesters;
        }
        
        // Validate: if year is selected but semester is not valid for that year, reset semester
        if ($selectedYear && $selectedSemester !== null && !in_array($selectedSemester, $availableSemesters)) {
            $selectedSemester = null;
        }
        
        // Only resolve period if BOTH year AND semester are provided (after validation)
        // This ensures data only appears when both filters are selected and valid
        $period = null;
        if ($selectedYear !== null && $selectedSemester !== null) {
            $period = $this->resolvePeriod((string) $selectedYear, (string) $selectedSemester, $periods);
        }

        return [
            'periods' => $periods,
            'period' => $period,
            'districts' => $districts,
            'villages' => $villages,
            'selectedDistrict' => $selectedDistrict,
            'selectedVillage' => $selectedVillage,
            'filters' => $filters,
            'years' => $years,
            'semesterOptions' => $availableSemesters,
            'selectedYear' => $selectedYear,
            'selectedSemester' => $selectedSemester,
        ];
    }

    protected function resolvePeriod(?string $yearInput, ?string $semesterInput, array $periods): ?array
    {
        if (empty($periods)) {
            return null;
        }

        $year = $yearInput !== null ? (int) $yearInput : null;
        $semester = $semesterInput !== null ? (int) $semesterInput : null;

        if ($year !== null && $semester !== null) {
            foreach ($periods as $period) {
                if ($period['year'] === $year && $period['semester'] === $semester) {
                    return $period;
                }
            }
        }

        if ($year !== null && $semester === null) {
            foreach ($periods as $period) {
                if ($period['year'] === $year) {
                    return $period;
                }
            }
        }

        if ($semester !== null && $year === null) {
            foreach ($periods as $period) {
                if ($period['semester'] === $semester) {
                    return $period;
                }
            }
        }

        return $periods[0];
    }

    protected function latestPeriod(): ?array
    {
        $periods = $this->availablePeriods();
        return $periods[0] ?? null;
    }

    protected function genderSummary(?array $period, array $filters = []): array
    {
        if (!$period) {
            return ['male' => 0, 'female' => 0, 'total' => 0];
        }

        $query = DB::table('pop_gender')
            ->where('year', $period['year'])
            ->where('semester', $period['semester']);

        $this->applyAreaScope($query, $filters);

        $row = $query
            ->selectRaw('SUM(male) as male, SUM(female) as female, SUM(total) as total')
            ->first();

        if (!$row) {
            return ['male' => 0, 'female' => 0, 'total' => 0];
        }

        return [
            'male' => (int) $row->male,
            'female' => (int) $row->female,
            'total' => (int) $row->total,
        ];
    }

    protected function wajibKtpSummary(?array $period, array $filters = []): array
    {
        if (!$period) {
            return ['male' => 0, 'female' => 0, 'total' => 0];
        }

        $query = DB::table('pop_wajib_ktp')
            ->where('year', $period['year'])
            ->where('semester', $period['semester']);

        $this->applyAreaScope($query, $filters);

        $row = $query
            ->selectRaw('SUM(male) as male, SUM(female) as female, SUM(total) as total')
            ->first();

        if (!$row) {
            return ['male' => 0, 'female' => 0, 'total' => 0];
        }

        return [
            'male' => (int) $row->male,
            'female' => (int) $row->female,
            'total' => (int) $row->total,
        ];
    }

    protected function ageGroupSummary(?array $period, array $filters = []): array
    {
        if (!$period) {
            return [];
        }

        $query = DB::table('pop_age_group')
            ->where('year', $period['year'])
            ->where('semester', $period['semester']);

        $this->applyAreaScope($query, $filters);

        $rows = $query
            ->select('age_group')
            ->selectRaw('SUM(male) as male')
            ->selectRaw('SUM(female) as female')
            ->selectRaw('SUM(total) as total')
            ->groupBy('age_group')
            ->get()
            ->keyBy(function ($item) {
                return strtoupper(trim((string) $item->age_group));
            });

        $ordered = [];
        foreach (self::AGE_GROUPS as $group) {
            $key = strtoupper($group);
            $row = $rows->get($key);
            $ordered[] = [
                'label' => $group,
                'male' => $row ? (int) $row->male : 0,
                'female' => $row ? (int) $row->female : 0,
                'total' => $row ? (int) $row->total : 0,
            ];
        }

        return $ordered;
    }

    protected function districtRanking(?array $period, int $limit = 5)
    {
        if (!$period) {
            return collect();
        }

        return DB::table('pop_gender')
            ->join('districts', 'districts.id', '=', 'pop_gender.district_id')
            ->select('districts.name')
            ->selectRaw('SUM(pop_gender.total) as total')
            ->selectRaw('SUM(pop_gender.male) as male')
            ->selectRaw('SUM(pop_gender.female) as female')
            ->where('pop_gender.year', $period['year'])
            ->where('pop_gender.semester', $period['semester'])
            ->groupBy('districts.id', 'districts.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    protected function mapPopulationSummary(?array $period): array
    {
        if (!$period) {
            return $this->emptyMapStats();
        }

        $districtRows = DB::table('pop_gender')
            ->join('districts', 'districts.id', '=', 'pop_gender.district_id')
            ->select('districts.id', 'districts.code', 'districts.name')
            ->selectRaw('SUM(pop_gender.male) as male')
            ->selectRaw('SUM(pop_gender.female) as female')
            ->selectRaw('SUM(pop_gender.total) as total')
            ->where('pop_gender.year', $period['year'])
            ->where('pop_gender.semester', $period['semester'])
            ->groupBy('districts.id', 'districts.code', 'districts.name')
            ->get();

        $districtsByCode = [];
        $districtsBySlug = [];
        foreach ($districtRows as $row) {
            $entry = [
                'code' => $row->code,
                'name' => $row->name,
                'male' => (int) ($row->male ?? 0),
                'female' => (int) ($row->female ?? 0),
                'total' => (int) ($row->total ?? 0),
            ];

            foreach ($this->codeAliases($row->code ?? null) as $alias) {
                $districtsByCode[$alias] = $entry;
            }

            if ($slug = $this->normalizeNameKey($row->name ?? null)) {
                $districtsBySlug[$slug] = $entry;
            }
        }

        $villageRows = DB::table('pop_gender')
            ->join('villages', 'villages.id', '=', 'pop_gender.village_id')
            ->join('districts', 'districts.id', '=', 'villages.district_id')
            ->select(
                'villages.id as village_id',
                'villages.code as village_code',
                'villages.name as village_name',
                'districts.id as district_id',
                'districts.code as district_code',
                'districts.name as district_name'
            )
            ->selectRaw('SUM(pop_gender.male) as male')
            ->selectRaw('SUM(pop_gender.female) as female')
            ->selectRaw('SUM(pop_gender.total) as total')
            ->where('pop_gender.year', $period['year'])
            ->where('pop_gender.semester', $period['semester'])
            ->groupBy(
                'villages.id',
                'villages.code',
                'villages.name',
                'districts.id',
                'districts.code',
                'districts.name'
            )
            ->get();

        $villagesByCode = [];
        $villagesBySlug = [];
        foreach ($villageRows as $row) {
            $entry = [
                'code' => $row->village_code,
                'name' => $row->village_name,
                'district_code' => $row->district_code,
                'district_name' => $row->district_name,
                'male' => (int) ($row->male ?? 0),
                'female' => (int) ($row->female ?? 0),
                'total' => (int) ($row->total ?? 0),
            ];

            $districtAliases = $this->codeAliases($row->district_code ?? null);
            $villageAliases = $this->codeAliases($row->village_code ?? null);
            foreach ($districtAliases as $districtAlias) {
                foreach ($villageAliases as $villageAlias) {
                    $villagesByCode[$districtAlias . '-' . $villageAlias] = $entry;
                }
            }

            $districtSlug = $this->normalizeNameKey($row->district_name ?? null);
            $villageSlug = $this->normalizeNameKey($row->village_name ?? null);
            if ($districtSlug && $villageSlug) {
                $villagesBySlug[$districtSlug . '-' . $villageSlug] = $entry;
            }
        }

        return [
            'districts' => [
                'by_code' => $districtsByCode,
                'by_slug' => $districtsBySlug,
            ],
            'villages' => [
                'by_code' => $villagesByCode,
                'by_slug' => $villagesBySlug,
            ],
        ];
    }

    protected function codeAliases($code): array
    {
        if ($code === null) {
            return [];
        }

        $digits = preg_replace('/\D/', '', (string) $code);
        if ($digits === '') {
            return [];
        }

        $aliases = [$digits];
        if (strlen($digits) >= 3) {
            $aliases[] = str_pad(substr($digits, -3), 3, '0', STR_PAD_LEFT);
        }
        if (strlen($digits) >= 4) {
            $aliases[] = str_pad(substr($digits, -4), 4, '0', STR_PAD_LEFT);
        }
        if (strlen($digits) >= 5) {
            $aliases[] = str_pad(substr($digits, -5), 5, '0', STR_PAD_LEFT);
        }

        return array_values(array_unique(array_filter($aliases)));
    }

    protected function normalizeNameKey(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $slug = Str::of($name)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->value();

        return $slug === '' ? null : $slug;
    }

    protected function emptyMapStats(): array
    {
        return [
            'districts' => [
                'by_code' => [],
                'by_slug' => [],
            ],
            'villages' => [
                'by_code' => [],
                'by_slug' => [],
            ],
        ];
    }

    protected function areaPopulationTable(?array $period, array $filters): array
    {
        $context = $this->resolveAreaContext($filters);
        $titles = [
            'district' => 'Tabel Jumlah Penduduk per Kecamatan',
            'village' => 'Tabel Jumlah Penduduk per Desa/Kelurahan',
            'single' => 'Tabel Jumlah Penduduk Desa/Kelurahan',
        ];

        if (!$period) {
            return [
                'level' => $context['level'],
                'column' => $context['columnLabel'],
                'title' => $titles[$context['level']],
                'subtitle' => null,
                'rows' => [],
                'totals' => ['male' => 0, 'female' => 0, 'total' => 0],
            ];
        }

        $subtitleParts = [];
        $periodLabel = $this->formatPeriodLabel($period);

        switch ($context['level']) {
            case 'single':
                $village = Village::with('district')->find($filters['village_id']);
                if ($village) {
                    $titles['single'] = 'Tabel Jumlah Penduduk Desa/Kelurahan ' . $village->name;
                    if ($village->district) {
                        $subtitleParts[] = 'Kecamatan ' . $village->district->name;
                    }
                } elseif (!empty($filters['district_id'])) {
                    $districtName = optional(District::find($filters['district_id']))->name;
                    if ($districtName) {
                        $subtitleParts[] = 'Kecamatan ' . $districtName;
                    }
                }
                break;
            case 'village':
                $districtName = optional(District::find($filters['district_id']))->name;
                if ($districtName) {
                    $subtitleParts[] = 'Kecamatan ' . $districtName;
                }
                break;
            default:
                $subtitleParts[] = 'Kabupaten Madiun';
                break;
        }

        if ($periodLabel) {
            $subtitleParts[] = $periodLabel;
        }

        $contextQuery = $this->prepareAreaQuery('pop_gender', $period, $filters);
        $query = $contextQuery['query']
            ->selectRaw('SUM(pop_gender.male) as male')
            ->selectRaw('SUM(pop_gender.female) as female')
            ->selectRaw('SUM(pop_gender.total) as total');

        $this->applyGroupBy($query, $contextQuery['groupBy']);
        $query->orderBy('area_name');

        $results = $query->get();

        $rows = $results->map(function ($row) use ($contextQuery) {
            return [
                'area_id' => $row->area_id ?? null,
                'name' => $row->area_name ?? '-',
                'male' => (int) ($row->male ?? 0),
                'female' => (int) ($row->female ?? 0),
                'total' => (int) ($row->total ?? 0),
                'highlight' => isset($contextQuery['highlightId']) && $contextQuery['highlightId'] !== null
                    ? ((int) $contextQuery['highlightId'] === (int) ($row->area_id ?? 0))
                    : false,
            ];
        })->toArray();

        $totals = $this->summarizeRows(array_map(function ($row) {
            return [
                'male' => $row['male'],
                'female' => $row['female'],
                'total' => $row['total'],
            ];
        }, $rows));

        return [
            'level' => $contextQuery['level'],
            'column' => $contextQuery['columnLabel'],
            'title' => $titles[$contextQuery['level']],
            'subtitle' => $this->buildSubtitle($subtitleParts),
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    protected function buildSubtitle(array $parts): ?string
    {
        $parts = array_values(array_filter(array_map('trim', $parts)));
        return empty($parts) ? null : implode(' • ', $parts);
    }

    protected function summarizeRows(array $rows): array
    {
        $summary = ['male' => 0, 'female' => 0, 'total' => 0];

        foreach ($rows as $row) {
            $summary['male'] += (int) ($row['male'] ?? 0);
            $summary['female'] += (int) ($row['female'] ?? 0);
            $summary['total'] += (int) ($row['total'] ?? 0);
        }

        return $summary;
    }

    protected function educationMatrix(?array $period, array $filters): array
    {
        $labels = $this->educationLabels();
        if (!$period) {
            return $this->buildEmptyMatrix($labels, $this->resolveAreaContext($filters));
        }

        $context = $this->prepareAreaQuery('pop_education', $period, $filters);
        $query = $context['query'];
        foreach (array_keys($labels) as $key) {
            $query->selectRaw("SUM({$key}_m) as {$key}_m");
            $query->selectRaw("SUM({$key}_f) as {$key}_f");
        }

        $this->applyGroupBy($query, $context['groupBy']);
        $query->orderBy('area_name');

        $results = $query->get();

        return $this->formatMatrixResult($results, $labels, $context);
    }

    protected function wajibKtpMatrix(?array $period, array $filters): array
    {
        $labels = ['wajib_ktp' => 'Wajib KTP'];
        if (!$period) {
            return $this->buildEmptyMatrix($labels, $this->resolveAreaContext($filters));
        }

        $context = $this->prepareAreaQuery('pop_wajib_ktp', $period, $filters);
        $query = $context['query']
            ->selectRaw('SUM(pop_wajib_ktp.male) as wajib_ktp_m')
            ->selectRaw('SUM(pop_wajib_ktp.female) as wajib_ktp_f');

        $this->applyGroupBy($query, $context['groupBy']);
        $query->orderBy('area_name');

        $results = $query->get();

        return $this->formatMatrixResult($results, $labels, $context);
    }

    protected function maritalMatrix(?array $period, array $filters): array
    {
        $labels = $this->maritalLabels();
        if (!$period) {
            return $this->buildEmptyMatrix($labels, $this->resolveAreaContext($filters));
        }

        $context = $this->prepareAreaQuery('pop_marital_status', $period, $filters);
        $query = $context['query'];
        foreach (array_keys($labels) as $key) {
            $query->selectRaw("SUM({$key}_m) as {$key}_m");
            $query->selectRaw("SUM({$key}_f) as {$key}_f");
        }

        $this->applyGroupBy($query, $context['groupBy']);
        $query->orderBy('area_name');

        $results = $query->get();

        return $this->formatMatrixResult($results, $labels, $context);
    }

    protected function maritalLabels(): array
    {
        return [
            'belum_kawin' => 'Belum Kawin',
            'kawin' => 'Kawin',
            'cerai_hidup' => 'Cerai Hidup',
            'cerai_mati' => 'Cerai Mati',
        ];
    }

    protected function headHouseholdMatrix(?array $period, array $filters): array
    {
        $labels = $this->headHouseholdLabels();
        if (!$period) {
            return $this->buildEmptyMatrix($labels, $this->resolveAreaContext($filters));
        }

        $context = $this->prepareAreaQuery('pop_head_of_household', $period, $filters);
        $query = $context['query'];
        foreach (array_keys($labels) as $key) {
            $query->selectRaw("SUM({$key}_m) as {$key}_m");
            $query->selectRaw("SUM({$key}_f) as {$key}_f");
        }

        $this->applyGroupBy($query, $context['groupBy']);
        $query->orderBy('area_name');

        $results = $query->get();

        return $this->formatMatrixResult($results, $labels, $context);
    }

    protected function headHouseholdLabels(): array
    {
        return [
            'belum_kawin' => 'Belum Kawin',
            'kawin' => 'Kawin',
            'cerai_hidup' => 'Cerai Hidup',
            'cerai_mati' => 'Cerai Mati',
        ];
    }

    protected function religionMatrix(?array $period, array $filters): array
    {
        $labels = $this->religionLabels();
        if (!$period) {
            return $this->buildEmptyMatrix($labels, $this->resolveAreaContext($filters));
        }

        $context = $this->prepareAreaQuery('pop_religion', $period, $filters);
        $query = $context['query'];
        foreach (array_keys($labels) as $key) {
            $query->selectRaw("SUM({$key}_m) as {$key}_m");
            $query->selectRaw("SUM({$key}_f) as {$key}_f");
        }

        $this->applyGroupBy($query, $context['groupBy']);
        $query->orderBy('area_name');

        $results = $query->get();

        return $this->formatMatrixResult($results, $labels, $context);
    }

    protected function religionLabels(): array
    {
        return [
            'islam' => 'Islam',
            'kristen' => 'Kristen',
            'katolik' => 'Katolik',
            'hindu' => 'Hindu',
            'buddha' => 'Buddha',
            'konghucu' => 'Konghucu',
            'aliran_kepercayaan' => 'Aliran Kepercayaan',
        ];
    }

    protected function resolveAreaContext(array $filters): array
    {
        $districtId = $filters['district_id'] ?? null;
        $villageId = $filters['village_id'] ?? null;

        if ($districtId) {
            if ($villageId) {
                return [
                    'level' => 'single',
                    'columnLabel' => 'Desa/Kelurahan',
                    'highlightId' => (int) $villageId,
                ];
            }

            return [
                'level' => 'village',
                'columnLabel' => 'Desa/Kelurahan',
                'highlightId' => null,
            ];
        }

        return [
            'level' => 'district',
            'columnLabel' => 'Kecamatan',
            'highlightId' => null,
        ];
    }

    protected function prepareAreaQuery(string $table, array $period, array $filters): array
    {
        $context = $this->resolveAreaContext($filters);
        $districtId = $filters['district_id'] ?? null;
        $villageId = $filters['village_id'] ?? null;

        $query = DB::table($table)
            ->where("{$table}.year", $period['year'])
            ->where("{$table}.semester", $period['semester']);

        switch ($context['level']) {
            case 'single':
                $query->join('villages', 'villages.id', '=', "{$table}.village_id")
                    ->select("{$table}.village_id as area_id", 'villages.name as area_name')
                    ->whereNotNull("{$table}.village_id");
                if ($districtId) {
                    $query->where("{$table}.district_id", $districtId);
                }
                if ($villageId) {
                    $query->where("{$table}.village_id", $villageId);
                }
                $groupBy = ["{$table}.village_id", 'villages.name'];
                break;

            case 'village':
                $query->join('villages', 'villages.id', '=', "{$table}.village_id")
                    ->select("{$table}.village_id as area_id", 'villages.name as area_name')
                    ->whereNotNull("{$table}.village_id");
                if ($districtId) {
                    $query->where("{$table}.district_id", $districtId);
                }
                $groupBy = ["{$table}.village_id", 'villages.name'];
                break;

            default:
                $query->join('districts', 'districts.id', '=', "{$table}.district_id")
                    ->select("{$table}.district_id as area_id", 'districts.name as area_name')
                    ->whereNotNull("{$table}.district_id");
                $groupBy = ["{$table}.district_id", 'districts.name'];
                break;
        }

        return array_merge($context, [
            'query' => $query,
            'groupBy' => $groupBy,
        ]);
    }

    protected function applyGroupBy($query, array $groupBy): void
    {
        if (!empty($groupBy)) {
            $query->groupBy($groupBy);
        }
    }

    protected function buildEmptyMatrix(array $labels, array $context): array
    {
        $columns = [];
        $totals = [];
        foreach ($labels as $key => $label) {
            $columns[] = ['key' => $key, 'label' => $label];
            $totals[$key] = ['male' => 0, 'female' => 0, 'total' => 0];
        }

        return [
            'level' => $context['level'],
            'columnLabel' => $context['columnLabel'],
            'columns' => $columns,
            'rows' => [],
            'totals' => $totals,
            'highlightAreaId' => $context['highlightId'] ?? null,
        ];
    }

    protected function formatMatrixResult($results, array $labels, array $context): array
    {
        $columns = [];
        $totals = [];
        foreach ($labels as $key => $label) {
            $columns[] = ['key' => $key, 'label' => $label];
            $totals[$key] = ['male' => 0, 'female' => 0, 'total' => 0];
        }

        $rows = [];
        foreach ($results as $row) {
            $values = [];
            foreach ($labels as $key => $label) {
                $male = (int) ($row->{$key . '_m'} ?? 0);
                $female = (int) ($row->{$key . '_f'} ?? 0);
                $total = $male + $female;

                $values[$key] = [
                    'male' => $male,
                    'female' => $female,
                    'total' => $total,
                ];

                $totals[$key]['male'] += $male;
                $totals[$key]['female'] += $female;
                $totals[$key]['total'] += $total;
            }

            $rows[] = [
                'area_id' => $row->area_id ?? null,
                'name' => $row->area_name ?? '-',
                'values' => $values,
                'highlight' => isset($context['highlightId']) && $context['highlightId'] !== null
                    ? ((int) $context['highlightId'] === (int) ($row->area_id ?? 0))
                    : false,
            ];
        }

        return [
            'level' => $context['level'],
            'columnLabel' => $context['columnLabel'],
            'columns' => $columns,
            'rows' => $rows,
            'totals' => $totals,
            'highlightAreaId' => $context['highlightId'] ?? null,
        ];
    }

    protected function educationSummary(?array $period, array $filters = []): array
    {
        if (!$period) {
            return [];
        }

        $config = config('dukcapil_import.sheets.pendidikan.cols', []);
        $columns = array_filter($config, function ($col) {
            return !in_array($col, [
                'year',
                'semester',
                'district_code',
                'district_name',
                'village_code',
                'village_name',
            ]);
        });

        $query = DB::table('pop_education')
            ->where('year', $period['year'])
            ->where('semester', $period['semester']);

        $this->applyAreaScope($query, $filters);

        foreach ($columns as $col) {
            $query->selectRaw("SUM($col) as $col");
        }

        $row = $query->first();
        if (!$row) {
            return [];
        }

        $buckets = [];
        foreach ($columns as $col) {
            $value = (int) ($row->$col ?? 0);
            if (str_ends_with($col, '_m') || str_ends_with($col, '_f')) {
                $base = substr($col, 0, -2);
                if (!isset($buckets[$base])) {
                    $buckets[$base] = ['male' => 0, 'female' => 0, 'total' => null];
                }
                if (str_ends_with($col, '_m')) {
                    $buckets[$base]['male'] = $value;
                } else {
                    $buckets[$base]['female'] = $value;
                }
            } elseif (str_ends_with($col, '_total')) {
                $base = substr($col, 0, -6);
                if (!isset($buckets[$base])) {
                    $buckets[$base] = ['male' => 0, 'female' => 0, 'total' => null];
                }
                $buckets[$base]['total'] = $value;
            }
        }

        foreach ($buckets as $key => &$values) {
            $values['male'] = (int) ($values['male'] ?? 0);
            $values['female'] = (int) ($values['female'] ?? 0);
            $total = $values['total'] ?? null;
            if ($total === null) {
                $total = $values['male'] + $values['female'];
            }
            $values['total'] = (int) $total;
        }

        $labels = $this->educationLabels();
        $ordered = [];
        foreach ($labels as $key => $label) {
            if (isset($buckets[$key])) {
                $ordered[] = [
                    'key' => $key,
                    'label' => $label,
                    'male' => $buckets[$key]['male'],
                    'female' => $buckets[$key]['female'],
                    'total' => $buckets[$key]['total'],
                ];
                unset($buckets[$key]);
            }
        }

        foreach ($buckets as $key => $values) {
            $ordered[] = [
                'key' => $key,
                'label' => $this->labelize($key),
                'male' => $values['male'],
                'female' => $values['female'],
                'total' => $values['total'],
            ];
        }

        return $ordered;
    }

    protected function singleAgeSummary(?array $period, array $filters = []): array
    {
        if (!$period) {
            return [];
        }

        $query = DB::table('pop_single_age')
            ->where('year', $period['year'])
            ->where('semester', $period['semester']);

        $this->applyAreaScope($query, $filters);

        $rows = $query
            ->select('age')
            ->selectRaw('SUM(male) as male')
            ->selectRaw('SUM(female) as female')
            ->selectRaw('SUM(total) as total')
            ->groupBy('age')
            ->orderBy('age')
            ->get();

        return $rows->map(function ($row) {
            $male = (int) ($row->male ?? 0);
            $female = (int) ($row->female ?? 0);
            $total = (int) ($row->total ?? ($male + $female));

            return [
                'label' => $row->age,
                'male' => $male,
                'female' => $female,
                'total' => $total,
            ];
        })->toArray();
    }

    protected function occupationHighlights(?array $period, array $filters = []): array
    {
        if (!$period) {
            return [];
        }

        $config = config('dukcapil_import.sheets.pekerjaan.cols', []);
        $columns = array_filter($config, function ($col) {
            return !in_array($col, [
                'year',
                'semester',
                'district_code',
                'district_name',
                'village_code',
                'village_name',
            ]);
        });

        $query = DB::table('pop_occupation')
            ->where('year', $period['year'])
            ->where('semester', $period['semester']);

        $this->applyAreaScope($query, $filters);

        foreach ($columns as $col) {
            $query->selectRaw("SUM($col) as $col");
        }

        $row = $query->first();
        if (!$row) {
            return [];
        }

        $series = [];
        foreach ($columns as $col) {
            if (str_ends_with($col, '_m') || str_ends_with($col, '_f')) {
                $base = substr($col, 0, -2);
                if (!isset($series[$base])) {
                    $series[$base] = ['male' => 0, 'female' => 0, 'total' => 0];
                }
                if (str_ends_with($col, '_m')) {
                    $series[$base]['male'] = (int) $row->$col;
                } else {
                    $series[$base]['female'] = (int) $row->$col;
                }
            } elseif ($col === 'total') {
                continue;
            }
        }

        foreach ($series as $key => &$values) {
            $values['male'] = (int) ($values['male'] ?? 0);
            $values['female'] = (int) ($values['female'] ?? 0);
            $values['total'] = $values['male'] + $values['female'];
        }
        unset($values);

        $labels = $this->occupationLabelsCache();
        $ordered = [];
        foreach ($config as $col) {
            if (in_array($col, ['year','semester','district_code','district_name','village_code','village_name','total'])) {
                continue;
            }
            if (str_ends_with($col, '_m') || str_ends_with($col, '_f')) {
                $base = substr($col, 0, -2);
                if (isset($series[$base]) && !isset($ordered[$base])) {
                    $ordered[$base] = $series[$base] + [
                        'label' => $labels[$base] ?? $this->labelize($base),
                    ];
                    if (($ordered[$base]['total'] ?? 0) === 0) {
                        unset($ordered[$base]);
                    }
                }
            }
        }

        foreach ($series as $key => $values) {
            if (!isset($ordered[$key])) {
                if (($values['total'] ?? 0) === 0) {
                    continue;
                }
                $ordered[$key] = $values + [
                    'label' => $labels[$key] ?? $this->labelize($key),
                ];
            }
        }

        return array_values($ordered);
    }

    protected function buildGenderChart(string $title, array $summary): array
    {
        $labels = ['Laki-laki', 'Perempuan'];
        $data = [
            (int) ($summary['male'] ?? 0),
            (int) ($summary['female'] ?? 0),
        ];

        return [
            'title' => $title,
            'labels' => $labels,
            'datasets' => [
                $this->makeDataset('Jumlah Penduduk', $data, ['#377dff', '#ff5c8d']),
            ],
        ];
    }

    protected function buildWajibKtpChart(string $title, array $summary): array
    {
        $labels = ['Laki-laki', 'Perempuan', 'Total'];
        $data = [
            (int) ($summary['male'] ?? 0),
            (int) ($summary['female'] ?? 0),
            (int) ($summary['total'] ?? 0),
        ];
        $colors = ['#377dff', '#ff5c8d', '#28a745'];

        return [
            'title' => $title,
            'labels' => $labels,
            'datasets' => [
                $this->makeDataset('Wajib KTP', $data, $colors),
            ],
            'legendItems' => array_map(
                fn($label, $color) => ['label' => $label, 'color' => $color],
                $labels,
                $colors
            ),
        ];
    }

    protected function buildSeriesChart(string $title, array $rows): array
    {
        if (empty($rows)) {
            return [
                'title' => $title,
                'labels' => [],
                'datasets' => [],
            ];
        }

        $labels = array_map(fn($row) => $row['label'] ?? '-', $rows);
        $male = array_map(fn($row) => (int) ($row['male'] ?? 0), $rows);
        $female = array_map(fn($row) => (int) ($row['female'] ?? 0), $rows);
        $total = array_map(fn($row) => (int) ($row['total'] ?? 0), $rows);

        return [
            'title' => $title,
            'labels' => $labels,
            'datasets' => [
                $this->makeDataset('Laki-laki', $male, '#377dff'),
                $this->makeDataset('Perempuan', $female, '#ff5c8d'),
                $this->makeDataset('Total', $total, '#28a745'),
            ],
        ];
    }

    protected function makeDataset(string $label, array $data, $color): array
    {
        $count = count($data);
        $background = is_array($color) ? $color : array_fill(0, $count, $color);
        $border = $background;

        return [
            'label' => $label,
            'data' => array_map('intval', $data),
            'backgroundColor' => $background,
            'borderColor' => $border,
            'borderWidth' => 1,
        ];
    }

    // ---------------------------------------------------------------------
    // Pyramid Chart Helper
    // ---------------------------------------------------------------------
    /**
     * Build data for the pyramid chart (age vs gender).
     * Male values are negated to appear on the left side of the chart.
     */
    protected function buildPyramidChart(string $title, array $rows): array
    {
        if (empty($rows)) {
            return [
                'title' => $title,
                'labels' => [],
                'datasets' => [],
            ];
        }

        // Extract labels and data
        $labels = array_map(fn($row) => $row['label'] ?? '-', $rows);
        $male = array_map(fn($row) => (int) ($row['male'] ?? 0), $rows);
        $female = array_map(fn($row) => (int) ($row['female'] ?? 0), $rows);

        // For pyramid chart, male values should be negative (left side)
        $maleNegative = array_map(fn($val) => -1 * $val, $male);

        return [
            'title' => $title,
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Laki-laki',
                    'data' => $maleNegative,
                    'backgroundColor' => '#377dff',
                    'borderColor' => '#377dff',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Perempuan',
                    'data' => $female,
                    'backgroundColor' => '#ff5c8d',
                    'borderColor' => '#ff5c8d',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function maritalStatusSummary(?array $period, array $filters = []): array
    {
        return $this->sumPairColumns('pop_marital_status', $period, $filters, [
            'belum_kawin',
            'kawin',
            'cerai_hidup',
            'cerai_mati',
        ]);
    }

    protected function headOfHouseholdSummary(?array $period, array $filters = []): array
    {
        return $this->sumPairColumns('pop_head_of_household', $period, $filters, [
            'belum_kawin',
            'kawin',
            'cerai_hidup',
            'cerai_mati',
        ]);
    }

    protected function religionSummary(?array $period, array $filters = []): array
    {
        return $this->sumPairColumns('pop_religion', $period, $filters, [
            'islam',
            'kristen',
            'katolik',
            'hindu',
            'buddha',
            'konghucu',
            'aliran_kepercayaan',
        ]);
    }

    protected function sumPairColumns(string $table, ?array $period, array $filters, array $keys): array
    {
        if (!$period) {
            return [];
        }

        $query = DB::table($table)
            ->where('year', $period['year'])
            ->where('semester', $period['semester']);

        $this->applyAreaScope($query, $filters);

        foreach ($keys as $key) {
            $query->selectRaw("SUM({$key}_m) as {$key}_m");
            $query->selectRaw("SUM({$key}_f) as {$key}_f");
        }

        $row = $query->first();
        if (!$row) {
            return [];
        }

        $results = [];
        foreach ($keys as $key) {
            $male = (int) ($row->{$key . '_m'} ?? 0);
            $female = (int) ($row->{$key . '_f'} ?? 0);
            $results[] = [
                'label' => $this->labelize($key),
                'male' => $male,
                'female' => $female,
                'total' => $male + $female,
            ];
        }

        return $results;
    }

    protected function formatPeriodLabel(?array $period): ?string
    {
        if (!$period) {
            return null;
        }

        $year = $period['year'] ?? null;
        $semester = $period['semester'] ?? null;

        if ($year === null && $semester === null) {
            return null;
        }

        if ($year !== null && $semester !== null) {
            return 'Semester ' . $semester . ' Tahun ' . $year;
        }

        if ($year !== null) {
            return 'Tahun ' . $year;
        }

        return 'Semester ' . $semester;
    }

    protected function labelize(string $key): string
    {
        return ucwords(str_replace('_', ' ', $key));
    }

    protected function educationLabels(): array
    {
        return [
            'belum_sekolah' => 'Belum / Tidak Sekolah',
            'belum_tamat_sd' => 'Belum Tamat SD',
            'tamat_sd' => 'Tamat SD',
            'tamat_sltp' => 'Tamat SLTP',
            'tamat_slta' => 'Tamat SLTA',
            'd1d2' => 'Diploma I/II',
            'd3' => 'Diploma III',
            's1' => 'Strata 1',
            's2' => 'Strata 2',
            's3' => 'Strata 3',
        ];
    }

    private array $occupationLabelCache = [];

    protected function occupationLabelsCache(): array
    {
        if ($this->occupationLabelCache) {
            return $this->occupationLabelCache;
        }

        $config = config('dukcapil_import.sheets.pekerjaan.cols', []);
        $labels = [];

        foreach ($config as $col) {
            if (preg_match('/^(.*)_(m|f)$/', $col, $m)) {
                $base = $m[1];
                if (!isset($labels[$base])) {
                    $labels[$base] = $this->labelize($base);
                }
            }
        }

        return $this->occupationLabelCache = $labels;
    }

    protected function applyAreaScope($query, array $filters): void
    {
        if (!empty($filters['district_id'])) {
            $query->where('district_id', $filters['district_id']);
        }
        if (!empty($filters['village_id'])) {
            $query->where('village_id', $filters['village_id']);
        }
    }

    protected function populationGrowthRate(int $limit = 10): array
    {
        // Ambil periode dari pop_gender (bukan pop_age_group)
        // pop_gender adalah tabel agregat utama untuk data populasi
        $periods = DB::table('pop_gender')
            ->select('year', 'semester')
            ->groupBy('year', 'semester')
            ->orderByDesc('year')
            ->orderByDesc('semester')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                return [
                    'year' => (int) $row->year,
                    'semester' => (int) $row->semester,
                ];
            })
            ->toArray();
        
        if (empty($periods)) {
            return [
                'labels' => [],
                'data' => [],
                'growthRates' => [],
            ];
        }

        // Reverse untuk mengurutkan dari terlama ke terbaru sebelum perhitungan
        // Ini memastikan growth rate dihitung dengan benar (periode sebelumnya = periode sebelumnya yang sebenarnya)
        $periods = array_reverse($periods);
        
        $labels = [];
        $data = [];
        $growthRates = [];
        
        $previousTotal = null;
        
        foreach ($periods as $period) {
            // Gunakan genderSummary yang mengambil dari pop_gender
            $gender = $this->genderSummary($period);
            $total = $gender['total'] ?? 0;
            
            // Format label periode
            $label = 'S' . $period['semester'] . ' ' . $period['year'];
            $labels[] = $label;
            $data[] = $total;
            
            // Hitung laju pertumbuhan berdasarkan periode sebelumnya yang ADA
            // Growth rate hanya bisa dihitung jika ada periode sebelumnya
            if ($previousTotal !== null && $previousTotal > 0) {
                $growthRate = (($total - $previousTotal) / $previousTotal) * 100;
                $growthRates[] = round($growthRate, 2);
            } else {
                // Periode pertama tidak memiliki growth rate
                $growthRates[] = null;
            }
            
            // Update untuk periode berikutnya
            $previousTotal = $total;
        }
        
        // Data sudah dalam urutan dari terlama ke terbaru
        return [
            'labels' => $labels,
            'data' => $data,
            'growthRates' => $growthRates,
        ];
    }

    protected function sanitizeCategory(?string $category): string
    {
        $allowedCategories = [
            'gender',
            'age',
            'single-age',
            'education',
            'occupation',
            'marital',
            'household',
            'religion',
            'wajib-ktp',
        ];

        return in_array($category, $allowedCategories, true) ? $category : 'gender';
    }

    public function downloadTablePdf(Request $request)
    {
        [
            'periods' => $periods,
            'period' => $period,
            'districts' => $districts,
            'villages' => $villages,
            'selectedDistrict' => $selectedDistrict,
            'selectedVillage' => $selectedVillage,
            'filters' => $filters,
            'years' => $years,
            'semesterOptions' => $availableSemesters,
            'selectedYear' => $selectedYear,
            'selectedSemester' => $selectedSemester,
        ] = $this->prepareFilterContext($request);

        $category = $this->sanitizeCategory($request->get('category'));

        if (!$period) {
            return redirect()->route('public.data')->with('error', 'Pilih periode terlebih dahulu');
        }

        // Get data based on category
        $gender = $this->genderSummary($period, $filters);
        $wajibKtp = $this->wajibKtpSummary($period, $filters);
        $ageGroups = $this->ageGroupSummary($period, $filters);
        $singleAges = $this->singleAgeSummary($period, $filters);
        $education = $this->educationSummary($period, $filters);
        $topOccupations = $this->occupationHighlights($period, $filters);
        $marital = $this->maritalStatusSummary($period, $filters);
        $headHouseholds = $this->headOfHouseholdSummary($period, $filters);
        $religions = $this->religionSummary($period, $filters);
        $areaTable = $this->areaPopulationTable($period, $filters);
        $educationMatrix = $this->educationMatrix($period, $filters);
        $wajibKtpMatrix = $this->wajibKtpMatrix($period, $filters);
        $maritalMatrix = $this->maritalMatrix($period, $filters);
        $headHouseholdMatrix = $this->headHouseholdMatrix($period, $filters);
        $religionMatrix = $this->religionMatrix($period, $filters);

        $tabs = [
            'gender' => 'Jenis Kelamin',
            'age' => 'Kelompok Umur',
            'single-age' => 'Umur Tunggal',
            'education' => 'Pendidikan',
            'occupation' => 'Pekerjaan',
            'marital' => 'Status Perkawinan',
            'household' => 'Kepala Keluarga',
            'religion' => 'Agama',
            'wajib-ktp' => 'Wajib KTP',
        ];

        $districtName = $selectedDistrict ? optional($districts->firstWhere('id', (int) $selectedDistrict))->name : null;
        $villageName = $selectedVillage ? optional($villages->firstWhere('id', (int) $selectedVillage))->name : null;
        $kabupatenName = config('app.region_name', 'Kabupaten Madiun');
        $areaSegments = [$kabupatenName];
        if ($districtName) {
            $areaSegments[] = 'Kecamatan ' . Str::title($districtName);
            $areaSegments[] = $villageName ? ('Desa/Kelurahan ' . Str::title($villageName)) : 'Semua Desa/Kelurahan';
        } else {
            $areaSegments[] = 'Semua Kecamatan';
            $areaSegments[] = 'Semua Desa/Kelurahan';
        }
        $areaDescriptor = implode(' > ', array_filter($areaSegments));
        $periodLabel = $this->formatPeriodLabel($period);

        $pdf = Pdf::loadView('public.exports.table-pdf', [
            'category' => $category,
            'categoryLabel' => $tabs[$category] ?? 'Data',
            'period' => $period,
            'periodLabel' => $periodLabel,
            'areaDescriptor' => $areaDescriptor,
            'gender' => $gender,
            'wajibKtp' => $wajibKtp,
            'ageGroups' => $ageGroups,
            'singleAges' => $singleAges,
            'education' => $education,
            'topOccupations' => $topOccupations,
            'marital' => $marital,
            'headHouseholds' => $headHouseholds,
            'religions' => $religions,
            'areaTable' => $areaTable,
            'educationMatrix' => $educationMatrix,
            'wajibKtpMatrix' => $wajibKtpMatrix,
            'maritalMatrix' => $maritalMatrix,
            'headHouseholdMatrix' => $headHouseholdMatrix,
            'religionMatrix' => $religionMatrix,
        ])->setPaper('a4', 'landscape');

        $filename = 'data-' . $category . '-' . $period['year'] . '-s' . $period['semester'] . '.pdf';
        $this->logDownload($request, 'table', 'pdf', [
            'category' => $category,
            'period' => $period,
            'filters' => $filters,
        ]);
        return $pdf->download($filename);
    }

    public function downloadTableExcel(Request $request)
    {
        [
            'periods' => $periods,
            'period' => $period,
            'districts' => $districts,
            'villages' => $villages,
            'selectedDistrict' => $selectedDistrict,
            'selectedVillage' => $selectedVillage,
            'filters' => $filters,
            'years' => $years,
            'semesterOptions' => $availableSemesters,
            'selectedYear' => $selectedYear,
            'selectedSemester' => $selectedSemester,
        ] = $this->prepareFilterContext($request);

        $category = $this->sanitizeCategory($request->get('category'));

        if (!$period) {
            return redirect()->route('public.data')->with('error', 'Pilih periode terlebih dahulu');
        }

        // Get data based on category
        $gender = $this->genderSummary($period, $filters);
        $wajibKtp = $this->wajibKtpSummary($period, $filters);
        $ageGroups = $this->ageGroupSummary($period, $filters);
        $singleAges = $this->singleAgeSummary($period, $filters);
        $education = $this->educationSummary($period, $filters);
        $topOccupations = $this->occupationHighlights($period, $filters);
        $marital = $this->maritalStatusSummary($period, $filters);
        $headHouseholds = $this->headOfHouseholdSummary($period, $filters);
        $religions = $this->religionSummary($period, $filters);
        $areaTable = $this->areaPopulationTable($period, $filters);
        $educationMatrix = $this->educationMatrix($period, $filters);
        $wajibKtpMatrix = $this->wajibKtpMatrix($period, $filters);
        $maritalMatrix = $this->maritalMatrix($period, $filters);
        $headHouseholdMatrix = $this->headHouseholdMatrix($period, $filters);
        $religionMatrix = $this->religionMatrix($period, $filters);

        $tabs = [
            'gender' => 'Jenis Kelamin',
            'age' => 'Kelompok Umur',
            'single-age' => 'Umur Tunggal',
            'education' => 'Pendidikan',
            'occupation' => 'Pekerjaan',
            'marital' => 'Status Perkawinan',
            'household' => 'Kepala Keluarga',
            'religion' => 'Agama',
            'wajib-ktp' => 'Wajib KTP',
        ];

        $districtName = $selectedDistrict ? optional($districts->firstWhere('id', (int) $selectedDistrict))->name : null;
        $villageName = $selectedVillage ? optional($villages->firstWhere('id', (int) $selectedVillage))->name : null;
        $kabupatenName = config('app.region_name', 'Kabupaten Madiun');
        $areaSegments = [$kabupatenName];
        if ($districtName) {
            $areaSegments[] = 'Kecamatan ' . Str::title($districtName);
            $areaSegments[] = $villageName ? ('Desa/Kelurahan ' . Str::title($villageName)) : 'Semua Desa/Kelurahan';
        } else {
            $areaSegments[] = 'Semua Kecamatan';
            $areaSegments[] = 'Semua Desa/Kelurahan';
        }
        $areaDescriptor = implode(' > ', array_filter($areaSegments));
        $periodLabel = $this->formatPeriodLabel($period);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', $tabs[$category] ?? 'Data');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->setCellValue('A2', $areaDescriptor);
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        if ($periodLabel) {
            $sheet->setCellValue('E1', $periodLabel);
            $sheet->mergeCells('E1:E2');
            $sheet->getStyle('E1')->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                ->setVertical(Alignment::VERTICAL_CENTER);
        } else {
            $sheet->setCellValue('E1', '');
            $sheet->setCellValue('E2', '');
        }

        $row = 5;

        // Set header style
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        if ($category === 'gender') {
            $sheet->setCellValue('A' . $row, 'No');
            $sheet->setCellValue('B' . $row, $areaTable['column'] ?? 'Wilayah');
            $sheet->setCellValue('C' . $row, 'Laki-laki');
            $sheet->setCellValue('D' . $row, 'Perempuan');
            $sheet->setCellValue('E' . $row, 'Jumlah');
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($headerStyle);

            $row++;
            $areaRows = $areaTable['rows'] ?? [];
            foreach ($areaRows as $index => $areaRow) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, Str::title($areaRow['name']));
                $sheet->setCellValue('C' . $row, $areaRow['male']);
                $sheet->setCellValue('D' . $row, $areaRow['female']);
                $sheet->setCellValue('E' . $row, $areaRow['total']);
                $row++;
            }

            // Total
            if (!empty($areaRows)) {
                $totals = $areaTable['totals'] ?? ['male' => 0, 'female' => 0, 'total' => 0];
                $sheet->setCellValue('A' . $row, '');
                $sheet->setCellValue('B' . $row, 'Jumlah Keseluruhan');
                $sheet->setCellValue('C' . $row, $totals['male']);
                $sheet->setCellValue('D' . $row, $totals['female']);
                $sheet->setCellValue('E' . $row, $totals['total']);
                $sheet->getStyle('B' . $row . ':E' . $row)->getFont()->setBold(true);
                $row++;
            }
        } elseif ($category === 'age') {
            $sheet->setCellValue('A' . $row, 'No');
            $sheet->setCellValue('B' . $row, 'Kelompok Umur');
            $sheet->setCellValue('C' . $row, 'Laki-laki');
            $sheet->setCellValue('D' . $row, 'Perempuan');
            $sheet->setCellValue('E' . $row, 'Jumlah');
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($headerStyle);

            $row++;
            foreach ($ageGroups as $index => $ageGroup) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $ageGroup['label']);
                $sheet->setCellValue('C' . $row, $ageGroup['male']);
                $sheet->setCellValue('D' . $row, $ageGroup['female']);
                $sheet->setCellValue('E' . $row, $ageGroup['total']);
                $row++;
            }

            // Total
            if (!empty($ageGroups)) {
                $ageMale = array_sum(array_column($ageGroups, 'male'));
                $ageFemale = array_sum(array_column($ageGroups, 'female'));
                $ageTotal = array_sum(array_column($ageGroups, 'total'));
                $sheet->setCellValue('A' . $row, '');
                $sheet->setCellValue('B' . $row, 'Jumlah Keseluruhan');
                $sheet->setCellValue('C' . $row, $ageMale);
                $sheet->setCellValue('D' . $row, $ageFemale);
                $sheet->setCellValue('E' . $row, $ageTotal);
                $sheet->getStyle('B' . $row . ':E' . $row)->getFont()->setBold(true);
                $row++;
            }
        } elseif ($category === 'single-age') {
            $sheet->setCellValue('A' . $row, 'No');
            $sheet->setCellValue('B' . $row, 'Usia');
            $sheet->setCellValue('C' . $row, 'Laki-laki');
            $sheet->setCellValue('D' . $row, 'Perempuan');
            $sheet->setCellValue('E' . $row, 'Jumlah');
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($headerStyle);

            $row++;
            foreach ($singleAges as $index => $singleAge) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $singleAge['label']);
                $sheet->setCellValue('C' . $row, $singleAge['male']);
                $sheet->setCellValue('D' . $row, $singleAge['female']);
                $sheet->setCellValue('E' . $row, $singleAge['total']);
                $row++;
            }

            // Total
            if (!empty($singleAges)) {
                $singleMale = array_sum(array_column($singleAges, 'male'));
                $singleFemale = array_sum(array_column($singleAges, 'female'));
                $singleTotal = array_sum(array_column($singleAges, 'total'));
                $sheet->setCellValue('A' . $row, '');
                $sheet->setCellValue('B' . $row, 'Jumlah Keseluruhan');
                $sheet->setCellValue('C' . $row, $singleMale);
                $sheet->setCellValue('D' . $row, $singleFemale);
                $sheet->setCellValue('E' . $row, $singleTotal);
                $sheet->getStyle('B' . $row . ':E' . $row)->getFont()->setBold(true);
                $row++;
            }
        } elseif ($category === 'occupation') {
            $sheet->setCellValue('A' . $row, 'No');
            $sheet->setCellValue('B' . $row, 'Pekerjaan');
            $sheet->setCellValue('C' . $row, 'Laki-laki');
            $sheet->setCellValue('D' . $row, 'Perempuan');
            $sheet->setCellValue('E' . $row, 'Jumlah');
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($headerStyle);

            $row++;
            foreach ($topOccupations as $index => $occupation) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $occupation['label']);
                $sheet->setCellValue('C' . $row, $occupation['male']);
                $sheet->setCellValue('D' . $row, $occupation['female']);
                $sheet->setCellValue('E' . $row, $occupation['total']);
                $row++;
            }

            // Total
            if (!empty($topOccupations)) {
                $jobMale = array_sum(array_column($topOccupations, 'male'));
                $jobFemale = array_sum(array_column($topOccupations, 'female'));
                $jobTotal = array_sum(array_column($topOccupations, 'total'));
                $sheet->setCellValue('A' . $row, '');
                $sheet->setCellValue('B' . $row, 'Jumlah Keseluruhan');
                $sheet->setCellValue('C' . $row, $jobMale);
                $sheet->setCellValue('D' . $row, $jobFemale);
                $sheet->setCellValue('E' . $row, $jobTotal);
                $sheet->getStyle('B' . $row . ':E' . $row)->getFont()->setBold(true);
                $row++;
            }
        } elseif (in_array($category, ['education', 'wajib-ktp', 'marital', 'household', 'religion'])) {
            $matrix = match($category) {
                'education' => $educationMatrix,
                'wajib-ktp' => $wajibKtpMatrix,
                'marital' => $maritalMatrix,
                'household' => $headHouseholdMatrix,
                'religion' => $religionMatrix,
                default => [],
            };

            $columns = $matrix['columns'] ?? [];
            $rows = $matrix['rows'] ?? [];
            $columnLabel = $matrix['columnLabel'] ?? 'Wilayah';

            // Calculate total columns needed
            $totalCols = 2 + (count($columns) * 3);
            $startCol = 2; // B is index 1, so start from C which is index 2
            
            // Header row 1
            $sheet->setCellValue('A' . $row, 'No');
            $sheet->setCellValue('B' . $row, $columnLabel);
            $colIndex = $startCol;
            foreach ($columns as $column) {
                $colLetter = $this->getColumnLetter($colIndex);
                $mergeEndLetter = $this->getColumnLetter($colIndex + 2);
                $sheet->mergeCells($colLetter . $row . ':' . $mergeEndLetter . $row);
                $sheet->setCellValue($colLetter . $row, $column['label']);
                $colIndex += 3;
            }
            $lastCol = $this->getColumnLetter($startCol + (count($columns) * 3) - 1);
            $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray($headerStyle);

            // Header row 2
            $row++;
            $sheet->setCellValue('A' . $row, '');
            $sheet->setCellValue('B' . $row, '');
            $colIndex = $startCol;
            foreach ($columns as $column) {
                $colLetter = $this->getColumnLetter($colIndex);
                $sheet->setCellValue($colLetter . $row, 'L');
                $sheet->setCellValue($this->getColumnLetter($colIndex + 1) . $row, 'P');
                $sheet->setCellValue($this->getColumnLetter($colIndex + 2) . $row, 'Jumlah');
                $colIndex += 3;
            }
            $lastCol = $this->getColumnLetter($startCol + (count($columns) * 3) - 1);
            $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray($headerStyle);

            $row++;
            foreach ($rows as $index => $matrixRow) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, Str::title($matrixRow['name']));
                $colIndex = $startCol;
                foreach ($columns as $column) {
                    $key = $column['key'];
                    $value = $matrixRow['values'][$key] ?? ['male' => 0, 'female' => 0, 'total' => 0];
                    $colLetter = $this->getColumnLetter($colIndex);
                    $sheet->setCellValue($colLetter . $row, $value['male']);
                    $sheet->setCellValue($this->getColumnLetter($colIndex + 1) . $row, $value['female']);
                    $sheet->setCellValue($this->getColumnLetter($colIndex + 2) . $row, $value['total']);
                    $colIndex += 3;
                }
                $row++;
            }

            // Total
            if (!empty($rows)) {
                $totals = $matrix['totals'] ?? [];
                $sheet->setCellValue('A' . $row, '');
                $sheet->setCellValue('B' . $row, 'Jumlah Keseluruhan');
                $colIndex = $startCol;
                foreach ($columns as $column) {
                    $key = $column['key'];
                    $total = $totals[$key] ?? ['male' => 0, 'female' => 0, 'total' => 0];
                    $colLetter = $this->getColumnLetter($colIndex);
                    $sheet->setCellValue($colLetter . $row, $total['male']);
                    $sheet->setCellValue($this->getColumnLetter($colIndex + 1) . $row, $total['female']);
                    $sheet->setCellValue($this->getColumnLetter($colIndex + 2) . $row, $total['total']);
                    $colIndex += 3;
                }
                $lastCol = $this->getColumnLetter($startCol + (count($columns) * 3) - 1);
                $sheet->getStyle('B' . $row . ':' . $lastCol . $row)->getFont()->setBold(true);
                $row++;
            }
        }

        // Auto size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'data-' . $category . '-' . $period['year'] . '-s' . $period['semester'] . '.xlsx';

        $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
        $writer->save($tempFile);

        $this->logDownload($request, 'table', 'excel', [
            'category' => $category,
            'period' => $period,
            'filters' => $filters,
        ]);
        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    protected function getColumnLetter($num)
    {
        // Convert 0-based index to Excel column letter using PhpSpreadsheet's Coordinate class
        // Input: 0 = A, 1 = B, 2 = C, etc.
        return Coordinate::stringFromColumnIndex($num + 1);
    }

    public function downloadChartPdf(Request $request)
    {
        [
            'periods' => $periods,
            'period' => $period,
            'districts' => $districts,
            'villages' => $villages,
            'selectedDistrict' => $selectedDistrict,
            'selectedVillage' => $selectedVillage,
            'filters' => $filters,
            'years' => $years,
            'semesterOptions' => $availableSemesters,
            'selectedYear' => $selectedYear,
            'selectedSemester' => $selectedSemester,
        ] = $this->prepareFilterContext($request);

        $category = $this->sanitizeCategory($request->get('category'));

        if (!$period) {
            return redirect()->route('public.charts')->with('error', 'Pilih periode terlebih dahulu');
        }

        $gender = $this->genderSummary($period, $filters);
        $wajibKtp = $this->wajibKtpSummary($period, $filters);
        $ageGroups = $this->ageGroupSummary($period, $filters);
        $singleAges = $this->singleAgeSummary($period, $filters);
        $education = $this->educationSummary($period, $filters);
        $topOccupations = $this->occupationHighlights($period, $filters);
        $marital = $this->maritalStatusSummary($period, $filters);
        $headHouseholds = $this->headOfHouseholdSummary($period, $filters);
        $religions = $this->religionSummary($period, $filters);

        $chartTitles = [
            'gender' => 'Jenis Kelamin',
            'age' => 'Kelompok Umur',
            'single-age' => 'Umur Tunggal',
            'education' => 'Pendidikan',
            'occupation' => 'Pekerjaan',
            'marital' => 'Status Perkawinan',
            'household' => 'Kepala Keluarga',
            'religion' => 'Agama',
            'wajib-ktp' => 'Wajib KTP',
        ];

        $charts = [
            'gender' => $this->buildGenderChart($chartTitles['gender'], $gender),
            'age' => $this->buildSeriesChart($chartTitles['age'], $ageGroups),
            'single-age' => $this->buildSeriesChart($chartTitles['single-age'], $singleAges),
            'education' => $this->buildSeriesChart($chartTitles['education'], $education),
            'occupation' => $this->buildSeriesChart($chartTitles['occupation'], $topOccupations),
            'marital' => $this->buildSeriesChart($chartTitles['marital'], $marital),
            'household' => $this->buildSeriesChart($chartTitles['household'], $headHouseholds),
            'religion' => $this->buildSeriesChart($chartTitles['religion'], $religions),
            'wajib-ktp' => $this->buildWajibKtpChart($chartTitles['wajib-ktp'], $wajibKtp),
        ];

        $districtName = $selectedDistrict ? optional($districts->firstWhere('id', (int) $selectedDistrict))->name : null;
        $villageName = $selectedVillage ? optional($villages->firstWhere('id', (int) $selectedVillage))->name : null;
        $kabupatenName = config('app.region_name', 'Kabupaten Madiun');
        $areaSegments = [$kabupatenName];
        if ($districtName) {
            $areaSegments[] = 'Kecamatan ' . Str::title($districtName);
            $areaSegments[] = $villageName ? ('Desa/Kelurahan ' . Str::title($villageName)) : 'Semua Desa/Kelurahan';
        } else {
            $areaSegments[] = 'Semua Kecamatan';
            $areaSegments[] = 'Semua Desa/Kelurahan';
        }
        $areaDescriptor = implode(' > ', array_filter($areaSegments));
        $periodLabel = $this->formatPeriodLabel($period);

        $pdf = Pdf::loadView('public.exports.chart-pdf', [
            'category' => $category,
            'categoryLabel' => $chartTitles[$category] ?? 'Grafik Data',
            'period' => $period,
            'periodLabel' => $periodLabel,
            'areaDescriptor' => $areaDescriptor,
            'chart' => $charts[$category] ?? null,
        ])->setPaper('a4', 'landscape');

        $filename = 'grafik-' . $category . '-' . $period['year'] . '-s' . $period['semester'] . '.pdf';
        $this->logDownload($request, 'chart', 'pdf', [
            'category' => $category,
            'period' => $period,
            'filters' => $filters,
        ]);
        return $pdf->download($filename);
    }

    public function downloadComparePdf(Request $request)
    {
        $periods = $this->availablePeriods();
        $districts = District::orderBy('name')->get();
        
        $primaryYear = $request->input('year');
        $primarySemester = $request->input('semester');
        $primaryDistrict = $request->input('district_id');
        $primaryVillage = $request->input('village_id');
        
        $primaryYear = ($primaryYear === '' || $primaryYear === null) ? null : (int) $primaryYear;
        $primarySemester = ($primarySemester === '' || $primarySemester === null) ? null : (int) $primarySemester;
        $primaryDistrict = ($primaryDistrict === '' || $primaryDistrict === null) ? null : (int) $primaryDistrict;
        $primaryVillage = ($primaryVillage === '' || $primaryVillage === null) ? null : (int) $primaryVillage;

        $compareYear = $request->input('compare_year');
        $compareSemester = $request->input('compare_semester');
        $compareDistrict = $request->input('compare_district_id');
        $compareVillage = $request->input('compare_village_id');
        
        $compareYear = ($compareYear === '' || $compareYear === null) ? null : (int) $compareYear;
        $compareSemester = ($compareSemester === '' || $compareSemester === null) ? null : (int) $compareSemester;
        $compareDistrict = ($compareDistrict === '' || $compareDistrict === null) ? null : (int) $compareDistrict;
        $compareVillage = ($compareVillage === '' || $compareVillage === null) ? null : (int) $compareVillage;

        $category = $this->sanitizeCategory($request->get('category'));

        // Resolve periods
        $primaryPeriod = null;
        if ($primaryYear && $primarySemester) {
            foreach ($periods as $period) {
                if ($period['year'] == $primaryYear && $period['semester'] == $primarySemester) {
                    $primaryPeriod = $period;
                    break;
                }
            }
        }

        $comparePeriod = null;
        if ($compareYear && $compareSemester) {
            foreach ($periods as $period) {
                if ($period['year'] == $compareYear && $period['semester'] == $compareSemester) {
                    $comparePeriod = $period;
                    break;
                }
            }
        }

        if (!$primaryPeriod || !$comparePeriod) {
            return redirect()->route('public.compare')->with('error', 'Pilih periode untuk Data Utama dan Data Pembanding');
        }

        $regionName = config('app.region_name', 'Kabupaten Madiun');
        $primaryDistrictName = $primaryDistrict ? Str::title(optional($districts->firstWhere('id', $primaryDistrict))->name ?? '') : null;
        $primaryVillageName = $primaryVillage ? Str::title(Village::where('id', $primaryVillage)->value('name') ?? '') : null;
        $compareDistrictName = $compareDistrict ? Str::title(optional($districts->firstWhere('id', $compareDistrict))->name ?? '') : null;
        $compareVillageName = $compareVillage ? Str::title(Village::where('id', $compareVillage)->value('name') ?? '') : null;

        $primaryAreaSegments = [$regionName];
        if ($primaryDistrictName) {
            $primaryAreaSegments[] = 'Kecamatan ' . $primaryDistrictName;
            $primaryAreaSegments[] = $primaryVillageName ? 'Desa/Kelurahan ' . $primaryVillageName : 'Semua Desa/Kelurahan';
        } else {
            $primaryAreaSegments[] = 'Semua Kecamatan';
            $primaryAreaSegments[] = 'Semua Desa/Kelurahan';
        }
        $primaryAreaDescriptor = implode(' > ', array_filter($primaryAreaSegments));

        $compareAreaSegments = [$regionName];
        if ($compareDistrictName) {
            $compareAreaSegments[] = 'Kecamatan ' . $compareDistrictName;
            $compareAreaSegments[] = $compareVillageName ? 'Desa/Kelurahan ' . $compareVillageName : 'Semua Desa/Kelurahan';
        } else {
            $compareAreaSegments[] = 'Semua Kecamatan';
            $compareAreaSegments[] = 'Semua Desa/Kelurahan';
        }
        $compareAreaDescriptor = implode(' > ', array_filter($compareAreaSegments));

        $primaryFilters = [
            'district_id' => $primaryDistrict ? (int) $primaryDistrict : null,
            'village_id' => $primaryVillage ? (int) $primaryVillage : null,
        ];

        $compareFilters = [
            'district_id' => $compareDistrict ? (int) $compareDistrict : null,
            'village_id' => $compareVillage ? (int) $compareVillage : null,
        ];

        // Get data
        $primaryGender = $this->genderSummary($primaryPeriod, $primaryFilters);
        $primaryWajibKtp = $this->wajibKtpSummary($primaryPeriod, $primaryFilters);
        $primaryAgeGroups = $this->ageGroupSummary($primaryPeriod, $primaryFilters);
        $primarySingleAges = $this->singleAgeSummary($primaryPeriod, $primaryFilters);
        $primaryEducation = $this->educationSummary($primaryPeriod, $primaryFilters);
        $primaryOccupations = $this->occupationHighlights($primaryPeriod, $primaryFilters);
        $primaryMarital = $this->maritalStatusSummary($primaryPeriod, $primaryFilters);
        $primaryHeadHouseholds = $this->headOfHouseholdSummary($primaryPeriod, $primaryFilters);
        $primaryReligions = $this->religionSummary($primaryPeriod, $primaryFilters);

        $compareGender = $this->genderSummary($comparePeriod, $compareFilters);
        $compareWajibKtp = $this->wajibKtpSummary($comparePeriod, $compareFilters);
        $compareAgeGroups = $this->ageGroupSummary($comparePeriod, $compareFilters);
        $compareSingleAges = $this->singleAgeSummary($comparePeriod, $compareFilters);
        $compareEducation = $this->educationSummary($comparePeriod, $compareFilters);
        $compareOccupations = $this->occupationHighlights($comparePeriod, $compareFilters);
        $compareMarital = $this->maritalStatusSummary($comparePeriod, $compareFilters);
        $compareHeadHouseholds = $this->headOfHouseholdSummary($comparePeriod, $compareFilters);
        $compareReligions = $this->religionSummary($comparePeriod, $compareFilters);

        $chartTitles = [
            'gender' => 'Jenis Kelamin',
            'age' => 'Kelompok Umur',
            'single-age' => 'Umur Tunggal',
            'education' => 'Pendidikan',
            'occupation' => 'Pekerjaan',
            'marital' => 'Status Perkawinan',
            'household' => 'Kepala Keluarga',
            'religion' => 'Agama',
            'wajib-ktp' => 'Wajib KTP',
        ];

        $primaryCharts = [
            'gender' => $this->buildGenderChart($chartTitles['gender'], $primaryGender),
            'age' => $this->buildSeriesChart($chartTitles['age'], $primaryAgeGroups),
            'single-age' => $this->buildSeriesChart($chartTitles['single-age'], $primarySingleAges),
            'education' => $this->buildSeriesChart($chartTitles['education'], $primaryEducation),
            'occupation' => $this->buildSeriesChart($chartTitles['occupation'], $primaryOccupations),
            'marital' => $this->buildSeriesChart($chartTitles['marital'], $primaryMarital),
            'household' => $this->buildSeriesChart($chartTitles['household'], $primaryHeadHouseholds),
            'religion' => $this->buildSeriesChart($chartTitles['religion'], $primaryReligions),
            'wajib-ktp' => $this->buildWajibKtpChart($chartTitles['wajib-ktp'], $primaryWajibKtp),
        ];

        $compareCharts = [
            'gender' => $this->buildGenderChart($chartTitles['gender'], $compareGender),
            'age' => $this->buildSeriesChart($chartTitles['age'], $compareAgeGroups),
            'single-age' => $this->buildSeriesChart($chartTitles['single-age'], $compareSingleAges),
            'education' => $this->buildSeriesChart($chartTitles['education'], $compareEducation),
            'occupation' => $this->buildSeriesChart($chartTitles['occupation'], $compareOccupations),
            'marital' => $this->buildSeriesChart($chartTitles['marital'], $compareMarital),
            'household' => $this->buildSeriesChart($chartTitles['household'], $compareHeadHouseholds),
            'religion' => $this->buildSeriesChart($chartTitles['religion'], $compareReligions),
            'wajib-ktp' => $this->buildWajibKtpChart($chartTitles['wajib-ktp'], $compareWajibKtp),
        ];

        // Build labels
        $primaryLabel = 'S' . $primaryPeriod['semester'] . ' ' . $primaryPeriod['year'];
        if ($primaryDistrictName) {
            $primaryLabel .= ' - ' . $primaryDistrictName;
            if ($primaryVillageName) {
                $primaryLabel .= ' - ' . $primaryVillageName;
            }
        }

        $compareLabel = 'S' . $comparePeriod['semester'] . ' ' . $comparePeriod['year'];
        if ($compareDistrictName) {
            $compareLabel .= ' - ' . $compareDistrictName;
            if ($compareVillageName) {
                $compareLabel .= ' - ' . $compareVillageName;
            }
        }

        $pdf = Pdf::loadView('public.exports.compare-pdf', [
            'category' => $category,
            'categoryLabel' => $chartTitles[$category] ?? 'Perbandingan Data',
            'primaryPeriod' => $primaryPeriod,
            'comparePeriod' => $comparePeriod,
            'primaryLabel' => $primaryLabel,
            'compareLabel' => $compareLabel,
            'primaryAreaDescriptor' => $primaryAreaDescriptor,
            'compareAreaDescriptor' => $compareAreaDescriptor,
            'primaryChart' => $primaryCharts[$category] ?? null,
            'compareChart' => $compareCharts[$category] ?? null,
        ])->setPaper('a4', 'landscape');

        $filename = 'perbandingan-' . $category . '-' . $primaryPeriod['year'] . '-s' . $primaryPeriod['semester'] . '-vs-' . $comparePeriod['year'] . '-s' . $comparePeriod['semester'] . '.pdf';
        $this->logDownload($request, 'compare', 'pdf', [
            'category' => $category,
            'primary_period' => $primaryPeriod,
            'compare_period' => $comparePeriod,
            'primary_filters' => $primaryFilters,
            'compare_filters' => $compareFilters,
        ]);
        return $pdf->download($filename);
    }

    public function terms()
    {
        return view('public.terms', [
            'title' => 'Syarat & Ketentuan',
        ]);
    }

    protected function logDownload(Request $request, string $downloadType, string $fileType, array $meta = []): void
    {
        try {
            $category = $meta['category'] ?? null;
            $recentDuplicate = DownloadLog::when($category, function ($query) use ($category) {
                    $query->where('category', $category);
                })
                ->where('download_type', $downloadType)
                ->where('file_type', $fileType)
                ->where('ip_address', $request->ip())
                ->where('created_at', '>=', now()->subSeconds(5))
                ->exists();

            if ($recentDuplicate) {
                return;
            }

            DownloadLog::create([
                'full_name' => $request->get('nama_lengkap'),
                'address' => $request->get('alamat'),
                'occupation' => $request->get('pekerjaan'),
                'institution' => $request->get('instansi'),
                'phone_number' => $request->get('nomor_telepon'),
                'purpose' => $request->get('tujuan_penggunaan'),
                'download_type' => $downloadType,
                'file_type' => $fileType,
                'category' => $category,
                'filters' => !empty($meta) ? $meta : null,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);
        } catch (\Throwable $e) {
            logger()->warning('Failed to log download', [
                'message' => $e->getMessage(),
                'download_type' => $downloadType,
                'file_type' => $fileType,
            ]);
        }
    }
}
