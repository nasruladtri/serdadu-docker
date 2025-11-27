<?php

namespace App\Services;

use App\Models\District;
use App\Models\Village;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DukcapilImportService
{
    private bool $cachePrimed = false;
    /** @var array<string,\App\Models\District> */
    private array $districtCacheByCode = [];
    /** @var array<string,\App\Models\District> */
    private array $districtCacheByName = [];
    /** @var array<string,\App\Models\Village> */
    private array $villageCacheByCode = [];
    /** @var array<string,\App\Models\Village> */
    private array $villageCacheByName = [];
    /** @var array<int,array<int,array<int,array<string,array{male:int,female:int,source:string}>>>>> */
    private array $genderRollups = [];
    /** @var array<int,array<int,array<int,array<string,array{male:int,female:int}>>>>> */
    private array $wajibKtpRollups = [];
    private bool $hasDirectWajibKtpImport = false;
    private const AGE_GROUP_BUCKETS = [
        '00-04','05-09','10-14','15-19','20-24','25-29','30-34','35-39',
        '40-44','45-49','50-54','55-59','60-64','65-69','70-74','75+',
    ];

    /**
     * @param string $filePath  path file .xlsx
     * @param int|null $forceYear      tahun yang dipaksa dari form
     * @param int|null $forceSemester  semester (1/2) dipaksa dari form
     */
    public function import(string $filePath, ?int $forceYear = null, ?int $forceSemester = null): array
    {
        $conf        = config('dukcapil_import');
        $summary     = [];
        $usedYears   = [];
        $usedSemesters = [];
        $this->genderRollups = [];
        $this->wajibKtpRollups = [];
        $this->hasDirectWajibKtpImport = false;

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);

        foreach ($reader->getAllSheets() as $sheetObj) {
            $rawName = trim($sheetObj->getTitle());
            $keyName = strtolower($rawName);

            $ruleKey = $this->matchSheet($keyName, array_keys($conf['sheets']));
            if (!$ruleKey) {
                $this->log($filePath, $rawName, 'skipped', 0, 0, ['unmapped sheet']);
                $summary[] = [$rawName, 'skipped', 0, 0, ['unmapped sheet']];
                continue;
            }

            $rule  = $conf['sheets'][$ruleKey];
            $table = $rule['table'];

            if ($table === 'pop_wajib_ktp') {
                $this->hasDirectWajibKtpImport = true;
                $this->wajibKtpRollups = [];
            }

            $rows_ok = 0; $rows_fail = 0; $errors = []; $bulk = [];

            // Baca sebagai index numerik
            $sheet = $sheetObj->toArray(null, true, true, false);
            if (count($sheet) < 2) {
                $this->log($filePath, $rawName, 'skipped', 0, 0, ['empty']);
                $summary[] = [$rawName, 'skipped', 0, 0, ['empty']];
                continue;
            }

            $headersRaw = $sheet[0];
            $headers    = array_map(fn ($h) => $this->normHeader($h), $headersRaw);

            for ($i = 1; $i < count($sheet); $i++) {
                $row = $sheet[$i];
                if ($this->rowIsEmpty($row)) continue;

                try {
                    // header -> nilai
                    $assoc = [];
                    foreach ($headers as $colIndex => $h) {
                        $val = array_key_exists($colIndex, $row) ? $row[$colIndex] : null;
                        $assoc[$h] = is_string($val) ? trim($val) : $val;
                    }

                    // alias & cleaning
                    $assoc = $this->applyAliases($assoc);
                    // normalize bucket-style headers so age-group / single-age columns
                    // are recognized even if the header order/format varies (e.g. "00_04_l" or "l_00_04" or "00-04-L").
                    $assoc = $this->normalizeBucketKeys($assoc);

                    if ($this->shouldSkipRow($assoc)) {
                        continue;
                    }

                    // >>> isi year/semester dari form jika kolom kosong <<<
                    if ($forceYear !== null && empty($assoc['year'])) {
                        $assoc['year'] = $forceYear;
                    }
                    if ($forceSemester !== null && empty($assoc['semester'])) {
                        $assoc['semester'] = $forceSemester;
                    }

                    $expandedRows = $this->expandRow($rule, $assoc);
                    if (!$expandedRows) {
                        throw new \Exception('Baris tidak memuat data nilai yang dikenali.');
                    }

                    foreach ($expandedRows as $expanded) {
                        $payload = $this->buildPayload($rule, $expanded);
                        $this->registerAggregates($table, $payload);
                        $bulk[]  = $payload;
                        $rows_ok++;

                        if (isset($payload['year'])) {
                            $usedYears[$payload['year']] = true;
                        }
                        if (isset($payload['semester'])) {
                            $usedSemesters[$payload['semester']] = true;
                        }

                        if (count($bulk) >= 2000) {
                            $this->flushUpsert($table, $rule['keys'], $bulk);
                            $bulk = [];
                        }
                    }
                } catch (\Throwable $e) {
                    $rows_fail++;
                    $errors[] = 'Row ' . ($i + 1) . ': ' . $e->getMessage();
                }
            }

            if ($bulk) $this->flushUpsert($table, $rule['keys'], $bulk);

            $status = $rows_fail ? 'partial' : 'success';
            $this->log($filePath, $rawName, $status, $rows_ok, $rows_fail, $errors);
            $summary[] = [$rawName, $status, $rows_ok, $rows_fail, $errors];
        }

        $this->persistAggregates();

        $yearFilter     = array_keys($usedYears);
        $semesterFilter = array_keys($usedSemesters);
        sort($yearFilter);
        sort($semesterFilter);
        $highlights     = $this->collectHighlights($conf, $yearFilter, $semesterFilter);

        return [
            'summary'    => $summary,
            'highlights' => $highlights,
            'filters'    => [
                'years'     => $yearFilter,
                'semesters' => $semesterFilter,
            ],
        ];
    }

    /* ======================= UPSERT ======================= */

    private function flushUpsert(string $table, array $uniqueBy, array $rows): void
    {
        if (!$rows) return;
        $updateCols = array_values(array_diff(array_keys($rows[0]), $uniqueBy));
        $updateCols = array_values(array_diff($updateCols, ['created_at']));
        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table($table)->upsert($chunk, $uniqueBy, $updateCols);
        }
    }

    private function buildPayload(array $rule, array $row): array
    {
        $year = (int)($row['year'] ?? 0);
        $sem  = (int)$this->normalizeSemester($row['semester'] ?? 0);
        if (!$year || !in_array($sem, [1,2], true)) {
            throw new \Exception('year/semester tidak valid');
        }

        [$districtId, $villageId] = $this->resolveArea($row);

        $payload = [
            'year'        => $year,
            'semester'    => $sem,
            'district_id' => $districtId,
            'village_id'  => $villageId,
        ];

        $cols = $rule['cols'] ?? [];
        foreach ($cols as $c) {
            if (in_array($c, ['year','semester','district_code','district_name','village_code','village_name'])) continue;
            if (array_key_exists($c, $row)) {
                if ($c === 'age_group') {
                    $payload[$c] = (string) $row[$c];
                    continue;
                }
                $value = $row[$c];
                if (is_numeric($value)) {
                    $payload[$c] = (int) $value;
                } elseif (is_string($value)) {
                    $normalized = $this->normalizeNumericString($value);
                    $payload[$c] = $normalized ?? $value;
                } else {
                    $payload[$c] = $value;
                }
            }
        }

        if (isset($rule['calc_total'])) {
            $payload['total'] = $this->calcTotal($payload, $rule['calc_total']);
        } elseif (!isset($payload['total'])) {
            $payload['total'] = 0;
        }

        // Khusus untuk pop_kk: hitung total_printed dan total_not_printed jika belum ada
        if ($rule['table'] === 'pop_kk') {
            if (!isset($payload['total_printed'])) {
                $payload['total_printed'] = (int)($payload['male_printed'] ?? 0) + (int)($payload['female_printed'] ?? 0);
            }
            if (!isset($payload['total_not_printed'])) {
                $payload['total_not_printed'] = (int)($payload['male_not_printed'] ?? 0) + (int)($payload['female_not_printed'] ?? 0);
            }
        }

        return $payload;
    }

    private function expandRow(array $rule, array $row): array
    {
        $table = $rule['table'] ?? '';

        return match ($table) {
            'pop_age_group'   => $this->expandAgeGroupRow($row),
            'pop_single_age'  => $this->expandSingleAgeRow($row),
            default           => [$row],
        };
    }

    /* ================== MATCH & NORMALIZE ================= */

    private function matchSheet(string $sheet, array $keys): ?string
    {
        foreach ($keys as $k) if (strtolower($k) === $sheet) return $k;
        $s = preg_replace('/[^a-z0-9]/', '', $sheet);
        foreach ($keys as $k) {
            $kk = preg_replace('/[^a-z0-9]/', '', strtolower($k));
            if ($kk === $s) return $k;
            if ($kk !== '' && str_contains($s, $kk)) return $k;
            if ($s !== '' && str_contains($kk, $s)) return $k;
        }
        return null;
    }

    private function normHeader($h): string
    {
        $h = is_string($h) ? strtolower(trim($h)) : (string)$h;
        $h = preg_replace('/\s+/', '_', $h);
        $h = str_replace(['(',')','/','-','.','\'','"'],'_', $h);
        $h = preg_replace('/_+/', '_', $h);
        return trim($h, '_');
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $v) {
            if (!is_null($v) && trim((string)$v) !== '') return false;
        }
        return true;
    }

    private function normalizeSemester($val): ?int
    {
        if ($val === null || $val === '') return null;
        $s = strtolower(trim((string)$val));
        $s = preg_replace('/[^0-9iv]/', '', $s);
        if (is_numeric($s)) { $n=(int)$s; return in_array($n,[1,2],true)?$n:null; }
        if (in_array($s, ['i','ii'], true)) return $s==='i'?1:2;
        return null;
    }

    /* ========================= AREA ======================= */

    private function resolveArea(array $row): array
    {
        $this->primeCaches();

        $district = $this->resolveDistrictFromRow($row);
        if (!$district) {
            $looseVillage = $this->findVillageWithoutDistrict(
                $row['village_code'] ?? null,
                $row['village_name'] ?? null
            );

            if ($looseVillage) {
                return [$looseVillage->district_id, $looseVillage->id];
            }

            throw new \Exception('Kecamatan tidak dikenali (butuh code & name)');
        }

        $vCode = $row['village_code'] ?? null;
        $vName = $row['village_name'] ?? null;
        $villageId = null;

        if ($vCode || $vName) {
            $village = $this->fetchVillage($district->id, $vCode, $vName);

            if (!$village) {
                if ($vCode && $vName) {
                    $village = Village::create([
                        'district_id' => $district->id,
                        'code'        => $vCode,
                        'name'        => $vName,
                    ]);
                    $this->rememberVillage($village);
                } else {
                    throw new \Exception('Desa tidak dikenali (butuh code & name) atau kosongkan untuk level kecamatan');
                }
            }
            $villageId = $village->id;
        }

        return [$district->id, $villageId];
    }

    private function resolveDistrictFromRow(array $row): ?District
    {
        $dCode = $row['district_code'] ?? null;
        $dName = $row['district_name'] ?? null;

        if (!$dCode && !$dName) {
            return null;
        }

        $district = $this->fetchDistrict($dCode, $dName);

        if (!$district) {
            if ($dCode && $dName) {
                $district = District::create(['code' => $dCode, 'name' => $dName]);
                $this->rememberDistrict($district);
            } else {
                return null;
            }
        }

        return $district;
    }

    /* ==================== TOTAL & LOG ===================== */

    private function calcTotal(array $payload, $rule)
    {
        if ($rule === 'all_mf') {
            $sum = 0;
            foreach ($payload as $k=>$v) {
                if (preg_match('/_m$|_f$|^male$|^female$/', $k)) $sum += (int)$v;
            }
            return $sum;
        }
        $sum = 0;
        foreach ((array)$rule as $k) { $sum += (int)($payload[$k] ?? 0); }
        return $sum;
    }

    private function log(string $filePath, string $sheet, string $status, int $ok, int $fail, array $errors): void
    {
        DB::table('import_logs')->insert([
            'filename'   => basename($filePath),
            'sheet'      => $sheet,
            'status'     => $status,
            'rows_ok'    => $ok,
            'rows_fail'  => $fail,
            'errors'     => $errors ? json_encode($errors) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function registerAggregates(string $table, array $payload): void
    {
        // Hanya aggregasi gender dari pop_single_age karena data lebih akurat
        // pop_age_group tidak digunakan untuk aggregasi gender untuk menghindari duplikasi/kesalahan
        if ($table === 'pop_single_age') {
            $this->addGenderAggregate($payload, 'single_age');
            if (!$this->hasDirectWajibKtpImport) {
                $this->addWajibKtpAggregate($payload);
            }
        }
    }

    private function addGenderAggregate(array $payload, string $source): void
    {
        if (!isset($payload['year'], $payload['semester'], $payload['district_id'])) {
            return;
        }

        $year      = (int) $payload['year'];
        $semester  = (int) $payload['semester'];
        $districtId = (int) $payload['district_id'];
        $villageKey = array_key_exists('village_id', $payload) && $payload['village_id'] !== null
            ? (string) $payload['village_id']
            : 'null';

        $male   = (int) ($payload['male'] ?? 0);
        $female = (int) ($payload['female'] ?? 0);

        if (!isset($this->genderRollups[$year][$semester][$districtId][$villageKey])) {
            $this->genderRollups[$year][$semester][$districtId][$villageKey] = [
                'male'   => 0,
                'female' => 0,
                'source' => $source,
            ];
        }

        $bucket =& $this->genderRollups[$year][$semester][$districtId][$villageKey];

        // Hanya gunakan single_age untuk aggregasi gender (data lebih akurat)
        // Jika sudah ada data dari source lain, skip untuk menghindari konflik
        if ($bucket['source'] !== $source) {
            // Prefer single_age karena data lebih granular dan akurat
            if ($source !== 'single_age') {
                return;
            }
            // Reset jika switching ke single_age
            $bucket = ['male' => 0, 'female' => 0, 'source' => 'single_age'];
        }

        $bucket['male'] += $male;
        $bucket['female'] += $female;
    }

    private function addWajibKtpAggregate(array $payload): void
    {
        if (!isset($payload['year'], $payload['semester'], $payload['district_id'], $payload['age'])) {
            return;
        }

        $age = (int) $payload['age'];
        if ($age < 17) {
            return;
        }

        $year       = (int) $payload['year'];
        $semester   = (int) $payload['semester'];
        $districtId = (int) $payload['district_id'];
        $villageKey = array_key_exists('village_id', $payload) && $payload['village_id'] !== null
            ? (string) $payload['village_id']
            : 'null';

        if (!isset($this->wajibKtpRollups[$year][$semester][$districtId][$villageKey])) {
            $this->wajibKtpRollups[$year][$semester][$districtId][$villageKey] = [
                'male'   => 0,
                'female' => 0,
            ];
        }

        $this->wajibKtpRollups[$year][$semester][$districtId][$villageKey]['male'] += (int) ($payload['male'] ?? 0);
        $this->wajibKtpRollups[$year][$semester][$districtId][$villageKey]['female'] += (int) ($payload['female'] ?? 0);
    }

    private function persistAggregates(): void
    {
        $genderRows = [];
        foreach ($this->genderRollups as $year => $semesters) {
            foreach ($semesters as $semester => $districts) {
                foreach ($districts as $districtId => $villages) {
                    foreach ($villages as $villageKey => $counts) {
                        $villageId = $villageKey === 'null' ? null : (int) $villageKey;
                        $male      = $counts['male'];
                        $female    = $counts['female'];
                        $genderRows[] = [
                            'year'        => (int) $year,
                            'semester'    => (int) $semester,
                            'district_id' => (int) $districtId,
                            'village_id'  => $villageId,
                            'male'        => $male,
                            'female'      => $female,
                            'total'       => $male + $female,
                            'updated_at'  => now(),
                            'created_at'  => now(),
                        ];
                    }
                }
            }
        }

        if ($genderRows) {
            $this->flushUpsert('pop_gender', ['year','semester','district_id','village_id'], $genderRows);
        }

        $wajibRows = [];
        foreach ($this->wajibKtpRollups as $year => $semesters) {
            foreach ($semesters as $semester => $districts) {
                foreach ($districts as $districtId => $villages) {
                    foreach ($villages as $villageKey => $counts) {
                        $villageId = $villageKey === 'null' ? null : (int) $villageKey;
                        $male      = $counts['male'];
                        $female    = $counts['female'];
                        $wajibRows[] = [
                            'year'        => (int) $year,
                            'semester'    => (int) $semester,
                            'district_id' => (int) $districtId,
                            'village_id'  => $villageId,
                            'male'        => $male,
                            'female'      => $female,
                            'total'       => $male + $female,
                            'updated_at'  => now(),
                            'created_at'  => now(),
                        ];
                    }
                }
            }
        }

        if ($wajibRows) {
            $this->flushUpsert('pop_wajib_ktp', ['year','semester','district_id','village_id'], $wajibRows);
        }
    }

    private function collectHighlights(array $conf, array $years, array $semesters): array
    {
        $highlights = [];
        $yearFilter = array_values($years);
        $semesterFilter = array_values($semesters);

        foreach ($conf['sheets'] as $sheetKey => $rule) {
            $table = $rule['table'] ?? null;
            $label = Str::title($sheetKey);

            if (!$table || !Schema::hasTable($table)) {
                $highlights[] = [
                    'key'           => $sheetKey,
                    'label'         => $label,
                    'table'         => $table,
                    'columns'       => [],
                    'rows'          => [],
                    'missing_table' => true,
                ];
                continue;
            }

            if ($table === 'pop_age_group') {
                $highlights[] = $this->buildAgeGroupHighlight($sheetKey, $label, $yearFilter, $semesterFilter);
                continue;
            }

            if ($table === 'pop_single_age') {
                $highlights[] = $this->buildSingleAgeHighlight($sheetKey, $label, $yearFilter, $semesterFilter);
                continue;
            }

            $query = DB::table($table)
                ->select("$table.*", 'districts.code as district_code', 'districts.name as district_name', 'villages.code as village_code', 'villages.name as village_name')
                ->leftJoin('districts', 'districts.id', '=', "$table.district_id")
                ->leftJoin('villages', 'villages.id', '=', "$table.village_id")
                ->orderByDesc("$table.updated_at")
                ->orderByDesc("$table.id")
                ->limit(5);

            if ($yearFilter) {
                $query->whereIn("$table.year", $yearFilter);
            }
            if ($semesterFilter) {
                $query->whereIn("$table.semester", $semesterFilter);
            }

            $rows = $query->get();

            $formatted = [];
            foreach ($rows as $row) {
                $formatted[] = $this->formatHighlightRow($row);
            }

            $highlights[] = [
                'key'           => $sheetKey,
                'label'         => $label,
                'table'         => $table,
                'columns'       => $formatted ? array_keys($formatted[0]) : [],
                'rows'          => $formatted,
                'missing_table' => false,
            ];
        }

        return $highlights;
    }

    private function buildAgeGroupHighlight(string $sheetKey, string $label, array $years, array $semesters): array
    {
        $table = 'pop_age_group';

        $baseRows = DB::table("$table as pag")
            ->selectRaw('pag.year, pag.semester, pag.district_id, pag.village_id')
            ->selectRaw('MAX(pag.updated_at) as last_updated')
            ->selectRaw('MAX(pag.id) as last_id')
            ->selectRaw('districts.code as district_code, districts.name as district_name')
            ->selectRaw('villages.code as village_code, villages.name as village_name')
            ->leftJoin('districts', 'districts.id', '=', 'pag.district_id')
            ->leftJoin('villages', 'villages.id', '=', 'pag.village_id')
            ->when($years, fn ($q) => $q->whereIn('pag.year', $years))
            ->when($semesters, fn ($q) => $q->whereIn('pag.semester', $semesters))
            ->groupBy('pag.year', 'pag.semester', 'pag.district_id', 'pag.village_id', 'districts.code', 'districts.name', 'villages.code', 'villages.name')
            ->orderByDesc('last_updated')
            ->orderByDesc('last_id')
            ->limit(5)
            ->get();

        $rows = [];
        foreach ($baseRows as $base) {
            $detailQuery = DB::table($table)
                ->select('age_group', 'male', 'female', 'total')
                ->where('year', $base->year)
                ->where('semester', $base->semester)
                ->where('district_id', $base->district_id);

            if ($base->village_id !== null) {
                $detailQuery->where('village_id', $base->village_id);
            } else {
                $detailQuery->whereNull('village_id');
            }

            $detailRows = $detailQuery->get();

            $pivot = [];
            foreach ($detailRows as $detail) {
                $key = strtoupper(trim((string) $detail->age_group));
                $pivot[$key] = [
                    'male'   => (int) $detail->male,
                    'female' => (int) $detail->female,
                    'total'  => (int) ($detail->total ?? ($detail->male + $detail->female)),
                ];
            }

            $code = $base->village_code ?: $base->district_code;
            $name = $base->village_name ?: $base->district_name;

            $rowData = [
                'Year'          => (int) $base->year,
                'Semester'      => (int) $base->semester,
                'Kode Wilayah'  => $code ?: 'N/A',
                'Wilayah'       => $name ?: 'N/A',
            ];

            foreach (self::AGE_GROUP_BUCKETS as $bucket) {
                $metrics = $pivot[$bucket] ?? ['male' => 0, 'female' => 0, 'total' => 0];
                $male   = (int) ($metrics['male'] ?? 0);
                $female = (int) ($metrics['female'] ?? 0);
                $total  = (int) ($metrics['total'] ?? ($male + $female));

                $rowData['L ' . $bucket]   = $male;
                $rowData['P ' . $bucket]   = $female;
                $rowData['JML ' . $bucket] = $total;
            }

            $rows[] = $rowData;
        }

        $columns = [];
        if ($rows) {
            $columns = array_keys($rows[0]);
        } else {
            $columns = array_merge(
                ['Year', 'Semester', 'Kode Wilayah', 'Wilayah'],
                $this->buildAgeGroupColumnList()
            );
        }

        return [
            'key'           => $sheetKey,
            'label'         => $label,
            'table'         => $table,
            'columns'       => $columns,
            'rows'          => $rows,
            'missing_table' => false,
        ];
    }

    private function buildSingleAgeHighlight(string $sheetKey, string $label, array $years, array $semesters): array
    {
        $table = 'pop_single_age';

        $baseRows = DB::table("$table as psa")
            ->selectRaw('psa.year, psa.semester, psa.district_id, psa.village_id')
            ->selectRaw('MAX(psa.updated_at) as last_updated')
            ->selectRaw('MAX(psa.id) as last_id')
            ->selectRaw('districts.code as district_code, districts.name as district_name')
            ->selectRaw('villages.code as village_code, villages.name as village_name')
            ->leftJoin('districts', 'districts.id', '=', 'psa.district_id')
            ->leftJoin('villages', 'villages.id', '=', 'psa.village_id')
            ->when($years, fn ($q) => $q->whereIn('psa.year', $years))
            ->when($semesters, fn ($q) => $q->whereIn('psa.semester', $semesters))
            ->groupBy('psa.year', 'psa.semester', 'psa.district_id', 'psa.village_id', 'districts.code', 'districts.name', 'villages.code', 'villages.name')
            ->orderByDesc('last_updated')
            ->orderByDesc('last_id')
            ->limit(5)
            ->get();

        $rows = [];
        $ageSet = [];

        foreach ($baseRows as $base) {
            $detailQuery = DB::table($table)
                ->select('age', 'male', 'female', 'total')
                ->where('year', $base->year)
                ->where('semester', $base->semester)
                ->where('district_id', $base->district_id)
                ->orderBy('age');

            if ($base->village_id !== null) {
                $detailQuery->where('village_id', $base->village_id);
            } else {
                $detailQuery->whereNull('village_id');
            }

            $detailRows = $detailQuery->get();

            $code = $base->village_code ?: $base->district_code;
            $name = $base->village_name ?: $base->district_name;

            $rowData = [
                'Year'          => (int) $base->year,
                'Semester'      => (int) $base->semester,
                'Kode Wilayah'  => $code ?: 'N/A',
                'Wilayah'       => $name ?: 'N/A',
            ];

            foreach ($detailRows as $detail) {
                $age = (int) $detail->age;
                $ageSet[$age] = true;

                $male   = (int) $detail->male;
                $female = (int) $detail->female;
                $total  = (int) ($detail->total ?? ($male + $female));

                $rowData['L ' . $age]   = $male;
                $rowData['P ' . $age]   = $female;
                $rowData['JML ' . $age] = $total;
            }

            $rows[] = $rowData;
        }

        $ages = array_keys($ageSet);
        sort($ages, SORT_NUMERIC);

        $columns = ['Year', 'Semester', 'Kode Wilayah', 'Wilayah'];
        foreach ($ages as $age) {
            $columns[] = 'L ' . $age;
            $columns[] = 'P ' . $age;
            $columns[] = 'JML ' . $age;
        }

        if (!$rows) {
            $columns = ['Year', 'Semester', 'Kode Wilayah', 'Wilayah'];
        } else {
            foreach ($rows as &$rowData) {
                foreach ($ages as $age) {
                    foreach (['L ' . $age, 'P ' . $age, 'JML ' . $age] as $metricColumn) {
                        if (!array_key_exists($metricColumn, $rowData)) {
                            $rowData[$metricColumn] = 0;
                        }
                    }
                }
            }
            unset($rowData);
        }

        return [
            'key'           => $sheetKey,
            'label'         => $label,
            'table'         => $table,
            'columns'       => $columns,
            'rows'          => $rows,
            'missing_table' => false,
        ];
    }

    private function buildAgeGroupColumnList(): array
    {
        $columns = [];
        foreach (self::AGE_GROUP_BUCKETS as $bucket) {
            $columns[] = 'L ' . $bucket;
            $columns[] = 'P ' . $bucket;
            $columns[] = 'JML ' . $bucket;
        }
        return $columns;
    }
    private function formatHighlightRow(object $row): array
    {
        $data = (array) $row;

        $year     = $data['year'] ?? null;
        $semester = $data['semester'] ?? null;
        $district = $this->formatAreaLabel($data['district_name'] ?? null, $data['district_code'] ?? null);
        $village  = $this->formatAreaLabel($data['village_name'] ?? null, $data['village_code'] ?? null, true);

        unset(
            $data['id'],
            $data['district_id'],
            $data['village_id'],
            $data['created_at'],
            $data['updated_at'],
            $data['district_name'],
            $data['district_code'],
            $data['village_name'],
            $data['village_code'],
            $data['year'],
            $data['semester']
        );

        $ordered = [
            'year'     => $year,
            'semester' => $semester,
            'district' => $district,
            'village'  => $village,
        ];

        foreach ($data as $key => $value) {
            $ordered[$key] = $value;
        }

        return $ordered;
    }

    private function formatAreaLabel(?string $name, ?string $code, bool $allowEmpty = false): string
    {
        $name = trim((string) ($name ?? ''));
        $code = trim((string) ($code ?? ''));

        $label = $name;
        if ($code !== '') {
            $label = $label !== '' ? "{$label} ({$code})" : $code;
        }

        if ($label === '') {
            return $allowEmpty ? '-' : 'N/A';
        }

        return $label;
    }

    /* ======================= ALIASES ====================== */

    private function applyAliases(array $row): array
    {
        $norm = [];
        foreach ($row as $k => $v) {
            $kk = $this->normHeader($k);
            $norm[$kk] = $v;
        }

        $map = [
            'tahun'           => 'year',
            'th'              => 'year',
            'thn'             => 'year',
            'semester'        => 'semester',
            'no_prop'         => 'province_code',
            'nama_prop'       => 'province_name',
            'no_kab'          => 'city_code',
            'nama_kab'        => 'city_name',
            'no_kec'          => 'district_code',
            'nama_kec'        => 'district_name',
            'kode_kecamatan'  => 'district_code',
            'kodekecamatan'   => 'district_code',
            'no_kel'          => 'village_code',
            'nama_kel'        => 'village_name',
            'desa'            => 'village_name',
            'kelurahan'       => 'village_name',
            'kode_desa'       => 'village_code',
            'kodedesa'        => 'village_code',
            'wktp_lk'         => 'male',
            'wktp_pr'         => 'female',
            'wktp'            => 'total',
            'kode'            => 'village_code',
            'kode_wilayah'    => 'region_code',
            'wilayah'         => 'village_name',
            'nama_wilayah'    => 'region_name',
            'kecamatan'       => 'district_name',
            'desa_kelurahan'  => 'village_name',
            'tidak_belum_sekolah_l'          => 'belum_sekolah_m',
            'tidak_belum_sekolah_p'          => 'belum_sekolah_f',
                'tidak_belum_sekolah'        => 'belum_sekolah_total',
            'belum_tamat_sd_sederajat_l'     => 'belum_tamat_sd_m',
            'belum_tamat_sd_sederajat_p'     => 'belum_tamat_sd_f',
                'belum_tamat_sd_sederajat'   => 'belum_tamat_sd_total',
            'tamat_sd_sederajat_l'           => 'tamat_sd_m',
            'tamat_sd_sederajat_p'           => 'tamat_sd_f',
                'tamat_sd_sederajat'         => 'tamat_sd_total',
            'sltp_sederajat_l'               => 'tamat_sltp_m',
            'sltp_sederajat_p'               => 'tamat_sltp_f',
                'sltp_sederajat'             => 'tamat_sltp_total',
            'slta_sederajat_l'               => 'tamat_slta_m',
            'slta_sederajat_p'               => 'tamat_slta_f',
                'slta_sederajat'             => 'tamat_slta_total',
            'diploma_i_ii_l'                 => 'd1d2_m',
            'diploma_i_ii_p'                 => 'd1d2_f',
                'diploma_i_ii'               => 'd1d2_total',
            'akademi_diploma_iii_s_muda_j_l' => 'd3_m',
            'akademi_diploma_iii_s_muda_j_p' => 'd3_f',
                'akademi_diploma_iii_s_muda_j' => 'd3_total',
            'diploma_iv_strata_i_l'          => 's1_m',
            'diploma_iv_strata_i_p'          => 's1_f',
                'diploma_iv_strata_i'        => 's1_total',
            'strata_ii_l'                    => 's2_m',
            'strata_ii_p'                    => 's2_f',
                'strata_ii'                  => 's2_total',
            'strata_iii_l'                   => 's3_m',
            'strata_iii_p'                   => 's3_f',
                'strata_iii'                 => 's3_total',
            'lk_blm_kawin'                   => 'belum_kawin_m',
            'pr_blm_kwn'                     => 'belum_kawin_f',
            'lk_kawin'                       => 'kawin_m',
            'pr_kwn'                         => 'kawin_f',
            'lk_cerai_hidup'                 => 'cerai_hidup_m',
            'pr_cerai_hidup'                 => 'cerai_hidup_f',
            'lk_cerai_mati'                  => 'cerai_mati_m',
            'pr_cerai_mati'                  => 'cerai_mati_f',
            'belum_kawin_l'                  => 'belum_kawin_m',
            'belum_kawin_p'                  => 'belum_kawin_f',
            'kawin_l'                        => 'kawin_m',
            'kawin_p'                        => 'kawin_f',
            'cerai_hidup_l'                  => 'cerai_hidup_m',
            'cerai_hidup_p'                  => 'cerai_hidup_f',
            'cerai_mati_l'                   => 'cerai_mati_m',
            'cerai_mati_p'                   => 'cerai_mati_f',
            'islam_l'                        => 'islam_m',
            'islam_p'                        => 'islam_f',
            'kristen_l'                      => 'kristen_m',
            'kristen_p'                      => 'kristen_f',
            'katholik_l'                     => 'katolik_m',
            'katholik_p'                     => 'katolik_f',
            'katolik_l'                      => 'katolik_m',
            'katolik_p'                      => 'katolik_f',
            'hindu_l'                        => 'hindu_m',
            'hindu_p'                        => 'hindu_f',
            'budha_l'                        => 'buddha_m',
            'budha_p'                        => 'buddha_f',
            'buddha_l'                       => 'buddha_m',
            'buddha_p'                       => 'buddha_f',
            'khonghucu_l'                    => 'konghucu_m',
            'khonghucu_p'                    => 'konghucu_f',
            'konghucu_l'                     => 'konghucu_m',
            'konghucu_p'                     => 'konghucu_f',
            'kepercayaan_l'                  => 'aliran_kepercayaan_m',
            'kepercayaan_p'                  => 'aliran_kepercayaan_f',
            'aliran_kepercayaan_l'           => 'aliran_kepercayaan_m',
            'aliran_kepercayaan_p'           => 'aliran_kepercayaan_f',
            'l'               => 'male',
            'p'               => 'female',
            'lk'              => 'male',
            'pr'              => 'female',
            'laki_laki'       => 'male',
            'laki'            => 'male',
            'perempuan'       => 'female',
            'wanita'          => 'female',
            'jumlah_total'    => 'total',
            'total_penduduk'  => 'total',
            'total_jumlah'    => 'total',
            'jumlah'          => 'total',
            'jml'             => 'total',
            'umur'            => 'age',
            'kelompok_umur'   => 'age_group',
            'golongan_umur'   => 'age_group',
            // Kartu Keluarga (KK)
            'l_kk'                    => 'male',
            'p_kk'                    => 'female',
            'jml_kk'                  => 'total',
            'lk_kk'                   => 'male',
            'pr_kk'                   => 'female',
            'l_cetak_kk'              => 'male_printed',
            'p_cetak_kk'              => 'female_printed',
            'jml_cetak_kk'            => 'total_printed',
            'lk_cetak_kk'             => 'male_printed',
            'pr_cetak_kk'             => 'female_printed',
            'blm_cetak_kk_l'          => 'male_not_printed',
            'blm_cetak_kk_p'          => 'female_not_printed',
            'blm_cetak_kk_jml'        => 'total_not_printed',
            'l_mmlk_kk_dinamis'       => 'male_printed',
            'p_mmlk_kk_dinamis'       => 'female_printed',
            'mmlk_kk_dinamis'         => 'total_printed',
            'l_blm_mmlk_kk_dinamis'   => 'male_not_printed',
            'p_blm_mmlk_kk_dinamis'   => 'female_not_printed',
            'blm_mmlk_kk_dinamis'     => 'total_not_printed',
            'belum_cetak_kk_l'        => 'male_not_printed',
            'belum_cetak_kk_p'        => 'female_not_printed',
            'belum_cetak_kk_jml'      => 'total_not_printed',
        ];
        foreach ($map as $from => $to) {
            if (array_key_exists($from, $norm) && !array_key_exists($to, $norm)) {
                $norm[$to] = $norm[$from];
            }
        }

        if (isset($norm['village_code']) && !isset($norm['region_code'])) {
            $norm['region_code'] = $norm['village_code'];
        }
        if (isset($norm['region_code']) && !isset($norm['village_code'])) {
            $norm['village_code'] = $norm['region_code'];
        }

        if (isset($norm['village_name']) && !isset($norm['region_name'])) {
            $norm['region_name'] = $norm['village_name'];
        }
        if (isset($norm['region_name']) && !isset($norm['village_name'])) {
            $norm['village_name'] = $norm['region_name'];
        }

        foreach (array_keys($norm) as $key) {
            if (preg_match('/_l$/', $key)) {
                $t = preg_replace('/_l$/', '_m', $key);
                if (!array_key_exists($t, $norm)) $norm[$t] = $norm[$key];
            }
            if (preg_match('/_p$/', $key)) {
                $t = preg_replace('/_p$/', '_f', $key);
                if (!array_key_exists($t, $norm)) $norm[$t] = $norm[$key];
            }
        }

        foreach (['province_code','city_code','district_code','village_code','region_code'] as $codeKey) {
            if (isset($norm[$codeKey])) {
                $norm[$codeKey] = preg_replace('/\D+/', '', (string)$norm[$codeKey]);
            }
        }

        $prov = $norm['province_code'] ?? null;
        $city = $norm['city_code'] ?? null;
        $dist = $norm['district_code'] ?? null;
        if ($prov && $city && $dist) {
            if (strlen($dist) <= 2) {
                $norm['district_code'] = str_pad($prov, 2, '0', STR_PAD_LEFT)
                    . str_pad($city, 2, '0', STR_PAD_LEFT)
                    . str_pad($dist, 2, '0', STR_PAD_LEFT);
            }
        }

        if (isset($norm['district_code'], $norm['village_code']) && $norm['district_code'] !== '') {
            if (strlen($norm['village_code']) <= 4) {
                $norm['village_code'] = $norm['district_code']
                    . str_pad($norm['village_code'], 4, '0', STR_PAD_LEFT);
            }
        } elseif (!isset($norm['district_code']) && isset($norm['village_code']) && strlen($norm['village_code']) >= 6) {
            $norm['district_code'] = substr($norm['village_code'], 0, 6);
        }

        $regionCode = $norm['region_code'] ?? null;
        if ($regionCode) {
            if (!isset($norm['district_code']) || $norm['district_code'] === '') {
                $norm['district_code'] = strlen($regionCode) >= 6 ? substr($regionCode, 0, 6) : $regionCode;
            }
            if ((!isset($norm['village_code']) || $norm['village_code'] === '') && strlen($regionCode) >= 10) {
                $norm['village_code'] = substr($regionCode, 0, 10);
            }
        }

        $regionName = $norm['region_name'] ?? null;
        if ($regionName) {
            if (!isset($norm['district_name']) || $norm['district_name'] === '') {
                $norm['district_name'] = $regionName;
            }
            if ((!isset($norm['village_name']) || $norm['village_name'] === '') && !empty($norm['village_code'])) {
                $norm['village_name'] = $regionName;
            }
        }

        unset($norm['region_code'], $norm['region_name']);

        foreach (['male','female','total'] as $n) {
            if (isset($norm[$n]) && is_string($norm[$n])) {
                $norm[$n] = (int)preg_replace('/[^\d-]/', '', $norm[$n]);
            }
        }

        if (isset($norm['semester'])) {
            $norm['semester'] = $this->normalizeSemester($norm['semester']);
        }

        return $norm;
    }

    private function shouldSkipRow(array $row): bool
    {
        $districtCode = trim((string)($row['district_code'] ?? ''));
        $districtName = strtolower(trim((string)($row['district_name'] ?? '')));
        $villageCode  = trim((string)($row['village_code'] ?? ''));
        $villageName  = strtolower(trim((string)($row['village_name'] ?? '')));

        $skipNames = ['jumlah', 'total', 'grand total', 'total keseluruhan'];

        if ($districtCode === '' && $districtName === '' && $villageCode === '' && $villageName === '') {
            return true;
        }

        if ($districtCode === '' && in_array($districtName, $skipNames, true)) {
            return true;
        }

        if ($villageCode === '' && in_array($villageName, $skipNames, true)) {
            return true;
        }

        return false;
    }

    private function primeCaches(): void
    {
        if ($this->cachePrimed) {
            return;
        }
        $this->cachePrimed = true;

        foreach (District::select('id', 'code', 'name')->get() as $district) {
            $this->rememberDistrict($district);
        }

        foreach (Village::select('id', 'district_id', 'code', 'name')->get() as $village) {
            $this->rememberVillage($village);
        }
    }

    private function rememberDistrict(District $district): void
    {
        if ($district->code) {
            $this->districtCacheByCode[$district->code] = $district;
        }
        if ($district->name) {
            $this->districtCacheByName[strtolower($district->name)] = $district;
        }
    }

    private function rememberVillage(Village $village): void
    {
        if ($village->code) {
            $this->villageCacheByCode[$village->code] = $village;
        }
        if ($village->name) {
            $key = $this->villageNameKey($village->district_id, $village->name);
            $this->villageCacheByName[$key] = $village;
        }
    }

    private function fetchDistrict(?string $code, ?string $name): ?District
    {
        $district = null;
        if ($code) {
            $district = $this->districtCacheByCode[$code] ?? null;
        }
        if (!$district && $name) {
            $district = $this->districtCacheByName[strtolower($name)] ?? null;
        }

        if ($district) {
            return $district;
        }

        $district = District::query()
            ->when($code, fn($q) => $q->where('code', $code))
            ->when(!$code && $name, fn($q) => $q->where('name', $name))
            ->first();

        if ($district) {
            $this->rememberDistrict($district);
        }

        return $district;
    }

    private function fetchVillage(int $districtId, ?string $code, ?string $name): ?Village
    {
        $village = null;
        if ($code) {
            $candidate = $this->villageCacheByCode[$code] ?? null;
            if ($candidate && $candidate->district_id === $districtId) {
                $village = $candidate;
            }
        }
        if (!$village && $name) {
            $key = $this->villageNameKey($districtId, $name);
            $village = $this->villageCacheByName[$key] ?? null;
        }

        if ($village) {
            return $village;
        }

        $village = Village::query()
            ->where('district_id', $districtId)
            ->when($code, fn($q)=>$q->where('code',$code))
            ->when(!$code && $name, fn($q)=>$q->where('name',$name))
            ->first();

        if ($village) {
            $this->rememberVillage($village);
        }

        return $village;
    }

    private function findVillageWithoutDistrict(?string $code, ?string $name): ?Village
    {
        $code = $code ? trim($code) : null;
        if ($code) {
            $village = $this->villageCacheByCode[$code] ?? null;
            if (!$village) {
                $village = Village::where('code', $code)->first();
                if ($village) {
                    $this->rememberVillage($village);
                }
            }
            if ($village) {
                return $village;
            }
        }

        $name = $name ? trim($name) : '';
        if ($name === '') {
            return null;
        }

        return $this->findVillageByNameOnly($name);
    }

    private function findVillageByNameOnly(string $name): ?Village
    {
        $normalized = strtolower($name);

        $matches = [];
        foreach ($this->villageCacheByName as $key => $candidate) {
            $parts = explode('::', $key, 2);
            if (($parts[1] ?? null) === $normalized) {
                $matches[] = $candidate;
            }
        }

        if (count($matches) === 1) {
            return $matches[0];
        }
        if (count($matches) > 1) {
            return null; // ambiguous
        }

        $found = Village::query()
            ->whereRaw('LOWER(name) = ?', [$normalized])
            ->limit(2)
            ->get();

        if ($found->count() === 1) {
            $village = $found->first();
            $this->rememberVillage($village);
            return $village;
        }

        return null;
    }

    private function villageNameKey(int $districtId, string $name): string
    {
        return $districtId . '::' . strtolower($name);
    }

    private function expandAgeGroupRow(array $row): array
    {
        $base = $this->extractBaseColumns($row);
        $result = [];

        foreach ($row as $key => $value) {
            if (!preg_match('/^l_(.+)$/', $key, $match)) {
                continue;
            }
            $suffix = $match[1];
            $label  = $this->formatAgeLabel($suffix);

            $maleKey   = 'l_' . $suffix;
            $femaleKey = 'p_' . $suffix;
            $totalKey  = 'jml_' . $suffix;

            $male   = $this->toInt($row[$maleKey] ?? 0);
            $female = $this->toInt($row[$femaleKey] ?? 0);
            $total  = $row[$totalKey] ?? null;
            $total  = $total !== null ? $this->toInt($total) : ($male + $female);

            if ($male === 0 && $female === 0 && $total === 0) {
                continue;
            }

            $result[] = $base + [
                'age_group' => $label,
                'male'      => $male,
                'female'    => $female,
                'total'     => $total,
            ];
        }

        return $result;
    }

    /**
     * Normalize bucket-style column keys to a canonical form used by expansion helpers.
     *
     * Examples of accepted input keys (after header normalization / aliasing):
     * - l_00_04, p_00_04, jml_00_04 (already canonical)
     * - 00_04_l, 00_04_p, 00_04_jml (bucket first)
     * - l00_04, p00_04, 00_04-l, 00-04-l
     * - 75+_l or l_75+
     *
     * This will rewrite keys into l_{bucket}, p_{bucket}, jml_{bucket} form.
     */
    private function normalizeBucketKeys(array $row): array
    {
        $out = [];
        foreach ($row as $key => $value) {
            $k = (string)$key;

            // already canonical
            if (preg_match('/^(l|p|jml)_(.+)$/', $k, $m)) {
                $out[$k] = $value;
                continue;
            }

            // patterns like 00_04_l or 00_04_laki or 00_04_lk
            if (preg_match('/^(.+?)_((?:lk|l|pr|p|male|female|jml|jumlah|total))$/', $k, $m)) {
                $bucket = $m[1];
                $tag = $m[2];
                $bucket = trim($bucket, '_-');
                $tag = strtolower($tag);
                if (in_array($tag, ['l','lk','male'], true)) {
                    $out['l_'.$bucket] = $value;
                    continue;
                }
                if (in_array($tag, ['p','pr','female'], true)) {
                    $out['p_'.$bucket] = $value;
                    continue;
                }
                if (in_array($tag, ['jml','jumlah','total'], true)) {
                    $out['jml_'.$bucket] = $value;
                    continue;
                }
            }

            // patterns like l00_04, p00_04, l00-04
            if (preg_match('/^(l|p)([_\-]?)(.+)$/', $k, $m)) {
                $side = $m[1];
                $bucket = $m[3];
                $bucket = trim($bucket, '_-');
                $out[$side.'_'.$bucket] = $value;
                continue;
            }

            // patterns like 00-04-l or 00-04-laki (with dashes)
            if (preg_match('/^(.+?)[_\-](l|lk|p|pr|jml|jumlah|total)$/', $k, $m)) {
                $bucket = $m[1];
                $tag = $m[2];
                $bucket = trim(str_replace('-', '_', $bucket), '_');
                $tag = strtolower($tag);
                if (in_array($tag, ['l','lk','male'], true)) {
                    $out['l_'.$bucket] = $value;
                    continue;
                }
                if (in_array($tag, ['p','pr','female'], true)) {
                    $out['p_'.$bucket] = $value;
                    continue;
                }
                if (in_array($tag, ['jml','jumlah','total'], true)) {
                    $out['jml_'.$bucket] = $value;
                    continue;
                }
            }

            // default: keep as-is
            $out[$k] = $value;
        }

        return $out;
    }

    private function expandSingleAgeRow(array $row): array
    {
        $base = $this->extractBaseColumns($row);
        $result = [];

        foreach ($row as $key => $value) {
            if (!preg_match('/^l_(.+)$/', $key, $match)) {
                continue;
            }

            $suffix   = $match[1];
            $ageValue = $this->parseSingleAgeLabel($suffix);

            if ($ageValue === null) {
                continue;
            }

            $maleKey   = 'l_' . $suffix;
            $femaleKey = 'p_' . $suffix;
            $totalKey  = 'jml_' . $suffix;

            $male   = $this->toInt($row[$maleKey] ?? 0);
            $female = $this->toInt($row[$femaleKey] ?? 0);
            $total  = $row[$totalKey] ?? null;
            $total  = $total !== null ? $this->toInt($total) : ($male + $female);

            if ($male === 0 && $female === 0 && $total === 0) {
                continue;
            }

            $result[] = $base + [
                'age'    => $ageValue,
                'male'   => $male,
                'female' => $female,
                'total'  => $total,
            ];
        }

        return $result;
    }

    private function extractBaseColumns(array $row): array
    {
        $filtered = [];
        foreach ($row as $key => $value) {
            if (preg_match('/^(l|p|jml)_/', $key)) {
                continue;
            }
            $filtered[$key] = $value;
        }
        return $filtered;
    }

    private function formatAgeLabel(string $suffix): string
    {
        $label = str_replace('_', '-', $suffix);
        $label = str_replace('--', '-', $label);
        $label = strtoupper($label);
        return $label;
    }

    private function parseSingleAgeLabel(string $suffix)
    {
        $clean = str_replace('_', '', $suffix);
        if (preg_match('/^\d+$/', $clean)) {
            return (int) $clean;
        }

        if (preg_match('/^\d+\+$/', $suffix)) {
            return $suffix;
        }

        return null;
    }

    private function toInt($value): int
    {
        if (is_null($value) || $value === '') {
            return 0;
        }
        if (is_int($value)) {
            return $value;
        }
        if (is_float($value)) {
            return (int) round($value);
        }

        $string = trim((string) $value);
        if ($string === '') {
            return 0;
        }

        $clean = preg_replace('/[^\d-]/', '', $string);
        if ($clean === '' || $clean === '-' || $clean === '--') {
            return 0;
        }

        return (int) $clean;
    }

    private function normalizeNumericString(string $value): ?int
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $normalized = str_replace([',', '.', ' '], '', $trimmed);
        if (preg_match('/^-?\d+$/', $normalized)) {
            return (int) $normalized;
        }

        $digitsOnly = preg_replace('/[^\d-]/', '', $trimmed);
        if ($digitsOnly === '' || $digitsOnly === '-' || $digitsOnly === '--') {
            return null;
        }

        return (int) $digitsOnly;
    }
}
