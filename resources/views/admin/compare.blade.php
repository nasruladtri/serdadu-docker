@extends('layouts.admin', ['title' => 'Perbandingan Data'])

@push('styles')
    <style>
        .dk-tab-pane {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chart-container {
            min-height: 400px;
            width: 100%;
            position: relative;
        }
        
        .chart-wrapper-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            width: 100%;
        }
        
        .chart-wrapper {
            position: relative;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            width: 100%;
        }

        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            align-items: center;
        }
        
        .chart-legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #374151;
        }
        
        .chart-legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            flex-shrink: 0;
        }

        .compare-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-primary {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-compare {
            background-color: #fef3c7;
            color: #92400e;
        }

        .compare-chart-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            width: 100%;
        }

        @media (min-width: 1024px) {
            .compare-chart-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1.5rem;
            }
        }
        
        /* Mobile responsive styling */
        @media (max-width: 640px) {
            /* Compact filter section */
            .dk-card .p-4 {
                padding: 0.75rem !important;
            }
            
            /* Smaller select inputs */
            select {
                font-size: 0.875rem;
                padding: 0.5rem 0.75rem;
            }
            
            /* Compact labels */
            label {
                font-size: 0.625rem;
                margin-bottom: 0.25rem;
            }
            
            /* Smaller badges */
            .compare-badge {
                font-size: 0.625rem;
                padding: 0.25rem 0.5rem;
            }
            
            /* Compact chart wrapper */
            .chart-wrapper {
                padding: 0.75rem !important;
                border-radius: 1rem !important;
            }
            
            /* Compact tab content */
            .dk-tab-content {
                padding: 0.75rem !important;
            }
            
            /* Smaller chart legend */
            .chart-legend {
                font-size: 0.75rem;
                gap: 0.5rem;
                margin-top: 0.75rem;
                padding-top: 0.75rem;
            }
            
            .chart-legend-color {
                width: 12px;
                height: 12px;
            }
            
            /* Smaller chart container */
            .chart-container {
                min-height: 300px !important;
            }
            
            /* Compact grid gap */
            .compare-chart-grid {
                gap: 0.75rem;
            }
        }
        
        /* Tab navigation scrollable on mobile */
        @media (max-width: 768px) {
            .dk-tabs {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }
            
            .dk-tabs::-webkit-scrollbar {
                display: none;
            }
            
            .dk-tab-button-text {
                font-size: 0.8125rem;
                white-space: nowrap;
            }
        }
    </style>
@endpush

@section('content')
    @php
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
        $axisDescriptions = [
            'gender' => [
                'horizontal' => 'Kategori jenis kelamin (Laki-laki dan Perempuan)',
                'vertical' => 'Jumlah penduduk (jiwa)',
            ],
            'age' => [
                'horizontal' => 'Kelompok umur (rentang 5 tahunan)',
                'vertical' => 'Jumlah penduduk (jiwa)',
            ],
            'single-age' => [
                'horizontal' => 'Umur tunggal (setiap tahun usia)',
                'vertical' => 'Jumlah penduduk (jiwa)',
            ],
            'education' => [
                'horizontal' => 'Jenjang pendidikan terakhir yang ditamatkan',
                'vertical' => 'Jumlah penduduk (jiwa)',
            ],
            'occupation' => [
                'horizontal' => 'Jenis pekerjaan penduduk',
                'vertical' => 'Jumlah penduduk (jiwa)',
            ],
            'marital' => [
                'horizontal' => 'Kategori status perkawinan',
                'vertical' => 'Jumlah penduduk (jiwa)',
            ],
            'household' => [
                'horizontal' => 'Jenis kepala keluarga',
                'vertical' => 'Jumlah kepala keluarga',
            ],
            'religion' => [
                'horizontal' => 'Agama yang dianut penduduk',
                'vertical' => 'Jumlah penduduk (jiwa)',
            ],
            'wajib-ktp' => [
                'horizontal' => 'Kategori wajib KTP-el',
                'vertical' => 'Jumlah penduduk (jiwa)',
            ],
        ];
        $horizontalChartKeys = ['single-age', 'education', 'occupation'];

        $regionName = config('app.region_name', 'Kabupaten Madiun');
        $primaryDistrictName = $primaryDistrict ? optional($districts->firstWhere('id', (int) $primaryDistrict))->name : null;
        $primaryVillageName = $primaryVillage ? optional($primaryVillages->firstWhere('id', (int) $primaryVillage))->name : null;
        $compareDistrictName = $compareDistrict ? optional($districts->firstWhere('id', (int) $compareDistrict))->name : null;
        $compareVillageName = $compareVillage ? optional($compareVillages->firstWhere('id', (int) $compareVillage))->name : null;

        $primaryAreaSegments = [$regionName];
        if ($primaryDistrictName) {
            $primaryAreaSegments[] = 'Kecamatan ' . \Illuminate\Support\Str::title($primaryDistrictName);
            $primaryAreaSegments[] = $primaryVillageName ? 'Desa/Kelurahan ' . \Illuminate\Support\Str::title($primaryVillageName) : 'Semua Desa/Kelurahan';
        } else {
            $primaryAreaSegments[] = 'Semua Kecamatan';
            $primaryAreaSegments[] = 'Semua Desa/Kelurahan';
        }
        $primaryAreaDescriptor = implode(' > ', array_filter($primaryAreaSegments));

        $compareAreaSegments = [$regionName];
        if ($compareDistrictName) {
            $compareAreaSegments[] = 'Kecamatan ' . \Illuminate\Support\Str::title($compareDistrictName);
            $compareAreaSegments[] = $compareVillageName ? 'Desa/Kelurahan ' . \Illuminate\Support\Str::title($compareVillageName) : 'Semua Desa/Kelurahan';
        } else {
            $compareAreaSegments[] = 'Semua Kecamatan';
            $compareAreaSegments[] = 'Semua Desa/Kelurahan';
        }
        $compareAreaDescriptor = implode(' > ', array_filter($compareAreaSegments));

        // Build labels untuk primary dan compare - gunakan input user yang sebenarnya
        $primaryLabel = 'Data Utama';
        if ($primaryYear && $primarySemester) {
            $primaryLabel = 'S' . $primarySemester . ' ' . $primaryYear;
            if ($primaryDistrictName) {
                $primaryLabel .= ' - ' . \Illuminate\Support\Str::title($primaryDistrictName);
                
                // Tambahkan desa/kelurahan jika dipilih
                if ($primaryVillageName) {
                    $primaryLabel .= ' - ' . \Illuminate\Support\Str::title($primaryVillageName);
                }
            }
        } elseif ($primaryPeriod) {
            // Fallback jika tidak ada input, gunakan period yang di-resolve
            $primaryLabel = 'S' . $primaryPeriod['semester'] . ' ' . $primaryPeriod['year'];
            if ($primaryDistrictName) {
                $primaryLabel .= ' - ' . \Illuminate\Support\Str::title($primaryDistrictName);
                
                // Tambahkan desa/kelurahan jika dipilih
                if ($primaryVillageName) {
                    $primaryLabel .= ' - ' . \Illuminate\Support\Str::title($primaryVillageName);
                }
            }
        }

        $compareLabel = 'Data Pembanding';
        if ($compareYear && $compareSemester) {
            $compareLabel = 'S' . $compareSemester . ' ' . $compareYear;
            if ($compareDistrictName) {
                $compareLabel .= ' - ' . \Illuminate\Support\Str::title($compareDistrictName);
                
                // Tambahkan desa/kelurahan jika dipilih
                if ($compareVillageName) {
                    $compareLabel .= ' - ' . \Illuminate\Support\Str::title($compareVillageName);
                }
            }
        } elseif ($comparePeriod) {
            // Fallback jika tidak ada input, gunakan period yang di-resolve
            $compareLabel = 'S' . $comparePeriod['semester'] . ' ' . $comparePeriod['year'];
            if ($compareDistrict) {
                $districtName = \Illuminate\Support\Str::title($districts->firstWhere('id', $compareDistrict)->name ?? '');
                $compareLabel .= ' - ' . $districtName;
                
                // Tambahkan desa/kelurahan jika dipilih
                if ($compareVillage) {
                    $village = $compareVillages->firstWhere('id', $compareVillage);
                    if ($village) {
                        $villageName = \Illuminate\Support\Str::title($village->name ?? '');
                        if ($villageName) {
                            $compareLabel .= ' - ' . $villageName;
                        }
                    }

            }
        }
        }
    @endphp

    {{-- Filter Section --}}
    <div class="dk-card mb-4 animate-fade-in-up">
        <div class="p-4">
            <h6 class="dk-card__title mb-4">Pengaturan Perbandingan</h6>
            
            <form method="GET" action="{{ route('admin.compare') }}">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Primary Data Filters --}}
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="compare-badge badge-primary">Data Utama</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Tahun</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="year" onchange="this.form.submit()">
                                    <option value="">Pilih Tahun</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}" {{ (int) ($primaryYear ?? 0) === $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Semester</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed" name="semester" id="primary-semester" onchange="this.form.submit()" {{ !$primaryYear ? 'disabled' : '' }}>
                                    <option value="">Pilih Semester</option>
                                    @if ($primaryYear)
                                        {{-- Jika tahun sudah dipilih, tampilkan semester yang tersedia untuk tahun tersebut saja --}}
                                        @if (!empty($primaryAvailableSemesters))
                                            @foreach ($primaryAvailableSemesters as $option)
                                                <option value="{{ $option }}" {{ (int) ($primarySemester ?? 0) === $option ? 'selected' : '' }}>
                                                    Semester {{ $option }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="" disabled>Belum tersedia untuk tahun ini</option>
                                        @endif
                                    @else
                                        <option value="" disabled>Pilih tahun terlebih dahulu</option>
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Kecamatan</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="district_id" onchange="this.form.submit()">
                                    <option value="">Semua Kecamatan</option>
                                    @foreach ($districts as $district)
                                        <option value="{{ $district->id }}" {{ (int) ($primaryDistrict ?? 0) === $district->id ? 'selected' : '' }}>
                                            {{ \Illuminate\Support\Str::title($district->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Desa/Kelurahan</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed" name="village_id" onchange="this.form.submit()" {{ !$primaryDistrict || $primaryVillages->isEmpty() ? 'disabled' : '' }}>
                                    <option value="">Semua Desa/Kelurahan</option>
                                    @foreach ($primaryVillages as $village)
                                        <option value="{{ $village->id }}" {{ (int) ($primaryVillage ?? 0) === $village->id ? 'selected' : '' }}>
                                            {{ \Illuminate\Support\Str::title($village->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Compare Data Filters --}}
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="compare-badge badge-compare">Data Pembanding</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Tahun</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="compare_year" id="compare-year" onchange="this.form.submit()">
                                    <option value="">Pilih Tahun</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}" {{ (int) ($compareYear ?? 0) === $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Semester</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed" name="compare_semester" id="compare-semester" onchange="this.form.submit()" {{ !$compareYear ? 'disabled' : '' }}>
                                    <option value="">Pilih Semester</option>
                                    @if ($compareYear)
                                        {{-- Jika tahun sudah dipilih, tampilkan semester yang tersedia untuk tahun tersebut saja --}}
                                        @if (!empty($compareAvailableSemesters))
                                            @foreach ($compareAvailableSemesters as $option)
                                                <option value="{{ $option }}" {{ (int) ($compareSemester ?? 0) === $option ? 'selected' : '' }}>
                                                    Semester {{ $option }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="" disabled>Belum tersedia untuk tahun ini</option>
                                        @endif
                                    @else
                                        <option value="" disabled>Pilih tahun terlebih dahulu</option>
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Kecamatan</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="compare_district_id" onchange="this.form.submit()">
                                    <option value="">Semua Kecamatan</option>
                                    @foreach ($districts as $district)
                                        <option value="{{ $district->id }}" {{ (int) ($compareDistrict ?? 0) === $district->id ? 'selected' : '' }}>
                                            {{ \Illuminate\Support\Str::title($district->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Desa/Kelurahan</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed" name="compare_village_id" onchange="this.form.submit()" {{ !$compareDistrict || $compareVillages->isEmpty() ? 'disabled' : '' }}>
                                    <option value="">Semua Desa/Kelurahan</option>
                                    @foreach ($compareVillages as $village)
                                        <option value="{{ $village->id }}" {{ (int) ($compareVillage ?? 0) === $village->id ? 'selected' : '' }}>
                                            {{ \Illuminate\Support\Str::title($village->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-[#009B4D] text-white rounded-lg hover:bg-[#007a3d] transition-colors font-medium">
                        Bandingkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tampilkan pesan jika belum ada data yang dipilih --}}
    @if (!$primaryPeriod || !$comparePeriod)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 dk-card mt-4">
            <strong class="text-[#009B4D]">Pilih Filter.</strong> <span class="text-[#009B4D]">Silakan pilih periode dan wilayah untuk Data Utama dan Data Pembanding, lalu klik tombol "Bandingkan" di atas.</span>
        </div>
    @else
        {{-- Tab Navigation - Only Visible when data is selected --}}
        <div class="dk-card mt-4 animate-fade-in-up delay-200">
            <ul class="dk-tabs" id="chartTabs" role="tablist">
                @foreach ($tabs as $key => $label)
                    <li role="presentation">
                        <button class="dk-tab-button {{ $loop->first ? 'active' : '' }}" id="tab-{{ $key }}-tab"
                            data-tab-target="#tab-{{ $key }}" type="button" role="tab"
                            aria-controls="tab-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                            <span class="dk-tab-button-text">{{ $label }}</span>
                            <span class="dk-tab-button-indicator"></span>
                        </button>
                    </li>
                @endforeach
            </ul>

            <div class="dk-tab-content mt-0 p-3 sm:p-4 lg:p-6" id="chartTabsContent">
                @foreach ($tabs as $key => $label)
                    <div class="dk-tab-pane {{ $loop->first ? 'show active' : 'hidden' }}" id="tab-{{ $key }}" role="tabpanel"
                        aria-labelledby="tab-{{ $key }}-tab">
                        
                        {{-- Header dengan tombol fullscreen dan download --}}
                        <div class="mb-4 flex flex-wrap gap-3 items-start justify-between">
                            <div>
                                <div class="space-y-1">
                                    <h6 class="text-lg font-semibold text-gray-900 tracking-tight">{{ $label }}</h6>
                                    <p class="text-sm text-gray-500">Perbandingan {{ strtolower($label) }} antara data utama dan data pembanding.</p>
                                </div>
                                <div class="mt-2 space-y-1 text-sm text-gray-600">
                                    <div class="flex flex-col sm:flex-row sm:items-start sm:gap-2">
                                        <span class="font-medium text-gray-700">Wilayah Data Utama:</span>
                                        <span class="text-gray-500">{{ $primaryAreaDescriptor }}</span>
                                    </div>
                                    <div class="flex flex-col sm:flex-row sm:items-start sm:gap-2">
                                        <span class="font-medium text-gray-700">Wilayah Data Pembanding:</span>
                                        <span class="text-gray-500">{{ $compareAreaDescriptor }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap sm:flex-nowrap items-center justify-end gap-3 text-right w-full lg:w-auto lg:ml-auto">
                                @php
                                    $fullscreenUrl = route('admin.compare.fullscreen', array_merge(request()->query(), ['category' => $key]));
                                    $downloadUrl = route('admin.compare.download.pdf', array_merge(request()->query(), ['category' => $key]));
                                    $primaryYear = request()->query('primary_year', request()->query('year', now()->year));
                                    $primarySemester = request()->query('primary_semester', request()->query('semester', 1));
                                    $compareYear = request()->query('compare_year');
                                    $compareSemester = request()->query('compare_semester');
                                    $downloadLabelBase = 'compare-' . $key . '-' . $primaryYear . '-s' . $primarySemester;
                                    if ($compareYear) {
                                        $downloadLabelBase .= '-vs-' . $compareYear;
                                        if ($compareSemester) {
                                            $downloadLabelBase .= '-s' . $compareSemester;
                                        }
                                    }
                                @endphp
                                <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 justify-end">
                                    <span 
                                        class="js-download-btn chart-action-btn cursor-pointer select-none"
                                        data-download-type="compare"
                                        data-download-format="pdf"
                                        data-download-url="{{ $downloadUrl }}"
                                        data-download-label="{{ $downloadLabelBase }}.pdf"
                                        data-year-default="{{ $primaryYear }}"
                                        data-semester-default="{{ $primarySemester }}"
                                        role="button"
                                        tabindex="0"
                                        aria-label="Download PDF">
                                        <img src="{{ asset('img/pdf.png') }}" alt="PDF icon" class="w-7 h-7 md:w-8 md:h-8 object-contain" style="width:1.3rem;height:1.3rem;">
                                    </span>
                                </div>
                                <a href="{{ $fullscreenUrl }}" target="_blank" class="chart-action-btn dk-table-heading__fullscreen-btn js-fullscreen-btn ml-0 sm:ml-4" data-base-url="{{ route('admin.compare.fullscreen', request()->query()) }}" title="Buka di tab baru (Fullscreen)">
                                    <img src="{{ asset('img/maximize.png') }}" alt="" class="w-7 h-7 md:w-8 md:h-8 object-contain" style="width:1.3rem;height:1.3rem;" aria-hidden="true">
                                    <span class="sr-only">Buka di tab baru (Fullscreen)</span>
                                </a>
                            </div>
                        </div>
                        
                        {{-- Side-by-side Charts --}}
                        @php
                            $primaryChart = $primaryCharts[$key] ?? null;
                            $compareChart = $compareCharts[$key] ?? null;
                            $primaryLabelCount = isset($primaryChart['labels']) && is_array($primaryChart['labels']) ? count($primaryChart['labels']) : 0;
                            $compareLabelCount = isset($compareChart['labels']) && is_array($compareChart['labels']) ? count($compareChart['labels']) : 0;
                            $singleAgeLabelCount = max($primaryLabelCount, $compareLabelCount);
                            $chartHeight = match ($key) {
                                'single-age' => $singleAgeLabelCount ? max(1100, $singleAgeLabelCount * 16) . 'px' : '700px',
                                'occupation' => max(900, max($singleAgeLabelCount, 1) * 22) . 'px',
                                default => '600px',
                            };
                        @endphp

                        <div class="compare-chart-grid grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                            {{-- Primary Chart (Left) --}}
                            <div class="chart-wrapper-container">
                                <div class="chart-wrapper bg-gradient-to-br from-white via-gray-50 to-white rounded-2xl sm:rounded-3xl p-3 sm:p-4 md:p-6 shadow-sm border border-gray-100">
                                    <div class="mb-4 flex items-center justify-center">
                                        <span class="compare-badge badge-primary">{{ $primaryLabel }}</span>
                                    </div>
                                    @if (!$primaryChart || empty($primaryChart['labels']))
                                        <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                                            <div class="w-12 h-12 mb-3 text-gray-400 opacity-50">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                            </div>
                                            <p class="text-sm text-gray-500 font-medium">Data belum tersedia</p>
                                        </div>
                                    @else
                                        <div class="relative chart-container w-full" style="height: {{ $chartHeight }}; min-height: {{ $chartHeight }};">
                                            <canvas id="chart-primary-{{ $key }}" data-chart-key="primary-{{ $key }}" class="w-full h-full"></canvas>
                                        </div>
                                        @include('public.partials.chart-axis-labels', [
                                            'axis' => $axisDescriptions[$key] ?? [],
                                            'flipAxes' => in_array($key, $horizontalChartKeys),
                                        ])
                                        <div class="chart-legend mt-4 pt-4 border-t border-gray-200" id="legend-primary-{{ $key }}"></div>
                                    @endif
                                </div>
                            </div>

                            {{-- Compare Chart (Right) --}}
                            <div class="chart-wrapper-container">
                                <div class="chart-wrapper bg-gradient-to-br from-white via-gray-50 to-white rounded-2xl sm:rounded-3xl p-3 sm:p-4 md:p-6 shadow-sm border border-gray-100">
                                    <div class="mb-4 flex items-center justify-center">
                                        <span class="compare-badge badge-compare">{{ $compareLabel }}</span>
                                    </div>
                                    @if (!$compareChart || empty($compareChart['labels']))
                                        <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                                            <div class="w-12 h-12 mb-3 text-gray-400 opacity-50">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                            </div>
                                            <p class="text-sm text-gray-500 font-medium">Data belum tersedia</p>
                                        </div>
                                    @else
                                        <div class="relative chart-container w-full" style="height: {{ $chartHeight }}; min-height: {{ $chartHeight }};">
                                            <canvas id="chart-compare-{{ $key }}" data-chart-key="compare-{{ $key }}" class="w-full h-full"></canvas>
                                        </div>
                                        @include('public.partials.chart-axis-labels', [
                                            'axis' => $axisDescriptions[$key] ?? [],
                                            'flipAxes' => in_array($key, $horizontalChartKeys),
                                        ])
                                        <div class="chart-legend mt-4 pt-4 border-t border-gray-200" id="legend-compare-{{ $key }}"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if ($primaryPeriod && $comparePeriod)
            // Function untuk update fullscreen button URL
            function updateFullscreenButtons() {
                var activePane = document.querySelector('.dk-tab-pane.active');
                if (activePane) {
                    var category = activePane.id.replace('tab-', '');
                    var fullscreenButtons = document.querySelectorAll('.js-fullscreen-btn');
                    fullscreenButtons.forEach(function(btn) {
                        var baseUrl = btn.getAttribute('data-base-url');
                        if (baseUrl) {
                            var url = new URL(baseUrl, window.location.origin);
                            url.searchParams.set('category', category);
                            btn.href = url.toString();
                        }
                    });
                }
            }

            // Tab navigation - only available when data is selected
            function showTab(targetId) {
                document.querySelectorAll('.dk-tab-pane').forEach(function(pane) {
                    pane.classList.add('hidden');
                    pane.classList.remove('show', 'active');
                });
                
                var targetPane = document.querySelector(targetId);
                if (targetPane) {
                    targetPane.classList.remove('hidden');
                    targetPane.classList.add('show', 'active');
                }
                
                document.querySelectorAll('#chartTabs button').forEach(function(btn) {
                    btn.classList.remove('active');
                    btn.setAttribute('aria-selected', 'false');
                });
                
                var activeButton = document.querySelector('#chartTabs button[data-tab-target="' + targetId + '"]');
                if (activeButton) {
                    activeButton.classList.add('active');
                    activeButton.setAttribute('aria-selected', 'true');
                }
                
                // Update fullscreen button URL
                updateFullscreenButtons();
            }

            // Initialize tab navigation
            var urlParams = new URLSearchParams(window.location.search);
            var categoryParam = urlParams.get('category');
            var initialCategory = categoryParam || 'gender';
            
            if (categoryParam) {
                var targetTabId = '#tab-' + categoryParam;
                var targetTab = document.querySelector(targetTabId);
                if (targetTab) {
                    showTab(targetTabId);
                } else {
                    showTab('#tab-gender');
                    initialCategory = 'gender';
                }
            } else {
                showTab('#tab-gender');
            }

            // Inisialisasi URL fullscreen button setelah tab diaktifkan
            setTimeout(function() {
                updateFullscreenButtons();
            }, 100);

            // Tab button event listeners - setup once
            var tabButtons = document.querySelectorAll('#chartTabs button[data-tab-target]');
            var ensureChartFunction = null;
            @else
            // Tab navigation not available when data is not selected
            var tabButtons = [];
            var ensureChartFunction = null;
            @endif

            @if ($primaryPeriod && $comparePeriod)
            // Chart rendering - only when data is available
            const primaryCharts = @json($primaryCharts);
            const compareCharts = @json($compareCharts);
            const chartsNeedingTags = @json($chartsNeedingTags);
            const chartsAngledTags = @json($chartsAngledTags);
            const horizontalChartKeys = @json($horizontalChartKeys);
            const chartInstances = {};
            const chartsWithValueLabels = Object.keys(primaryCharts || {});
            const totalLabelTargets = ['Total', 'Jumlah Penduduk', 'Wajib KTP'];
            const getCssColor = (name, fallback) => {
                const value = getComputedStyle(document.documentElement).getPropertyValue(name);
                return value ? value.trim() || fallback : fallback;
            };
            const getThemeTextColor = () => {
                const isDark = document.documentElement.classList.contains('dark');
                return isDark ? '#e2e8f0' : '#0f172a';
            };
            // Apply default colors once on load (will be refreshed per render below)
            if (typeof Chart !== 'undefined') {
                const isDark = document.documentElement.classList.contains('dark');
                const axisColor = isDark ? '#e2e8f0' : '#0f172a';
                const gridColor = getCssColor('--color-chart-grid', 'rgba(148, 163, 184, 0.35)');
                Chart.defaults.color = axisColor;
                Chart.defaults.borderColor = gridColor;
            }

            const primaryLabel = @json($primaryLabel);
            const compareLabel = @json($compareLabel);
            const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(value);

            // Category tag plugin
            const categoryTagPlugin = {
                id: 'categoryTagPlugin',
                afterDraw(chart, args, pluginOptions) {
                    const chartKey = chart.canvas.dataset.chartKey;
                    // Extract the base key (remove 'primary-' or 'compare-' prefix)
                    const key = chartKey.replace('primary-', '').replace('compare-', '');
                    if (!chartsNeedingTags.includes(key) || horizontalChartKeys.includes(key)) return;
                    
                    const labels = pluginOptions?.labels ?? chart.config.data.labels;
                    if (!labels || !labels.length) return;

                    const { ctx, scales } = chart;
                    const xScale = scales.x;
                    if (!xScale) return;

                    const fontSize = 10;
                    const isDark = document.documentElement.classList.contains('dark');
                    const labelColor = isDark ? '#e2e8f0' : '#0f172a';
                    ctx.save();
                    ctx.font = `${fontSize}px "Inter", "Poppins", sans-serif`;
                    ctx.fillStyle = labelColor;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';

                    const isAngled = chartsAngledTags.includes(key);
                    const needsRotation = isAngled;

                    labels.forEach((label, index) => {
                        const x = xScale.getPixelForValue(index);
                        const y = chart.chartArea.bottom + (needsRotation ? 20 : 10);
                        
                        ctx.save();
                        ctx.translate(x, y);
                        if (needsRotation) {
                            ctx.rotate(-Math.PI / 2);
                        }
                        ctx.fillText(label || '', 0, 0);
                        ctx.restore();
                    });
                    ctx.restore();
                }
            };

            if (typeof Chart !== 'undefined') {
                const valueLabelPlugin = {
                    id: 'valueLabelPlugin',
                    afterDatasetsDraw(chart, args, pluginOptions) {
                        if (!pluginOptions?.show) {
                            return;
                        }
                        const { ctx } = chart;
                        ctx.save();
                        ctx.font = pluginOptions.font || '10px "Inter", "Poppins", sans-serif';
                        const labelColor = getThemeTextColor();
                        ctx.fillStyle = pluginOptions.color || labelColor;
                        const horizontal = typeof pluginOptions.horizontal === 'boolean'
                            ? pluginOptions.horizontal
                            : chart.config?.options?.indexAxis === 'y';
                        const targetLabels = Array.isArray(pluginOptions.targetLabels) && pluginOptions.targetLabels.length
                            ? pluginOptions.targetLabels
                            : null;
                        chart.data.datasets.forEach((dataset, datasetIndex) => {
                            const meta = chart.getDatasetMeta(datasetIndex);
                            if (meta.hidden) {
                                return;
                            }
                            if (targetLabels && (!dataset?.label || !targetLabels.includes(dataset.label))) {
                                return;
                            }
                            meta.data.forEach((element, index) => {
                                const rawValue = dataset.data?.[index];
                                if (rawValue === null || rawValue === undefined) {
                                    return;
                                }
                                const numericValue = Number(rawValue);
                                if (!Number.isFinite(numericValue)) {
                                    return;
                                }
                                const formatted = formatNumber(numericValue);
                                const position = element.tooltipPosition();
                                let x = position.x;
                                let y = position.y;
                                if (horizontal) {
                                    x += 6;
                                    ctx.textAlign = 'left';
                                    ctx.textBaseline = 'middle';
                                } else {
                                    y -= 6;
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'bottom';
                                }
                                ctx.fillText(formatted, x, y);
                            });
                        });
                        ctx.restore();
                    }
                };

                Chart.register(categoryTagPlugin, valueLabelPlugin);
            }

            function ensureChart(key) {
                // Render primary chart (left)
                const primaryCanvas = document.getElementById('chart-primary-' + key);
                if (primaryCanvas && !chartInstances['primary-' + key]) {
                    const primaryConfig = primaryCharts[key];
                    if (primaryConfig && primaryConfig.labels && primaryConfig.labels.length > 0) {
                        renderChart('primary-' + key, primaryCanvas, primaryConfig, primaryLabel);
                    }
                }

                // Render compare chart (right)
                const compareCanvas = document.getElementById('chart-compare-' + key);
                if (compareCanvas && !chartInstances['compare-' + key]) {
                    const compareConfig = compareCharts[key];
                    if (compareConfig && compareConfig.labels && compareConfig.labels.length > 0) {
                        renderChart('compare-' + key, compareCanvas, compareConfig, compareLabel);
                    }
                }
            }

            function renderChart(chartKey, canvas, config, label) {
                if (chartInstances[chartKey]) return;

                setTimeout(() => {
                    if (typeof Chart === 'undefined') return;
                    
                    const key = chartKey.replace('primary-', '').replace('compare-', '');
                    const ctx = canvas.getContext('2d');
                    const isDark = document.documentElement.classList.contains('dark');
                    const themeColors = {
                        axis: isDark ? '#e2e8f0' : '#0f172a',
                        grid: isDark ? 'rgba(148, 163, 184, 0.35)' : getCssColor('--color-chart-grid', 'rgba(148, 163, 184, 0.35)'),
                        surface: getCssColor('--color-surface', '#0f172a'),
                        text: isDark ? '#e2e8f0' : '#0f172a',
                    };
                    Chart.defaults.color = themeColors.axis;
                    Chart.defaults.borderColor = themeColors.grid;
                    canvas.dataset.chartKey = chartKey;
                    const labels = config.labels || [];
                    const datasets = config.datasets || [];
                    const isHorizontal = horizontalChartKeys.includes(key);
                    const showValueLabels = chartsWithValueLabels.includes(key);
                    const needsTags = chartsNeedingTags.includes(key) && !isHorizontal;
                    const angledTags = chartsAngledTags.includes(key);
                    const longestLabel = labels.reduce((max, label) => Math.max(max, (label || '').length), 0);
                    const bottomPadding = isHorizontal
                        ? 36
                        : angledTags
                            ? Math.min(260, Math.max(160, longestLabel * 6 + 32))
                            : (needsTags ? 70 : 16);

                    chartInstances[chartKey] = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: isHorizontal ? 'y' : 'x',
                            layout: {
                                padding: {
                                    bottom: bottomPadding
                                }
                            },
                            scales: {
                                y: isHorizontal
                                    ? {
                                        beginAtZero: false,
                                        grid: { drawBorder: false, color: themeColors.grid },
                                        ticks: {
                                            autoSkip: false,
                                            padding: 4,
                        font: { size: key === 'single-age' ? 9 : 11 },
                                            color: themeColors.axis,
                                            backdropColor: 'transparent'
                                        }
                                    }
                                    : {
                                        beginAtZero: true,
                                        grid: { color: themeColors.grid, drawBorder: false },
                                        ticks: {
                                            callback(value) {
                                                return formatNumber(value);
                                            },
                                            color: themeColors.axis,
                                            backdropColor: 'transparent'
                                        }
                                    },
                                x: isHorizontal
                                    ? {
                                        beginAtZero: true,
                                        grid: { color: themeColors.grid, drawBorder: false },
                                        ticks: {
                                            callback(value) {
                                                return formatNumber(value);
                                            },
                                            color: themeColors.axis,
                                            backdropColor: 'transparent'
                                        }
                                    }
                                    : {
                                        grid: { color: themeColors.grid, drawBorder: false },
                                        ticks: {
                                            autoSkip: false,
                                            maxRotation: 45,
                                            minRotation: 0,
                                            callback(value, index, ticks) {
                                                const label = (ticks[index] && ticks[index].label) || '';
                                                return label.length > 20 ? label.substring(0, 20) + '…' : label;
                                            },
                                            color: themeColors.axis,
                                            backdropColor: 'transparent'
                                        }
                                    }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: themeColors.surface,
                                    titleColor: themeColors.text,
                                    bodyColor: themeColors.text,
                                    borderColor: themeColors.grid,
                                    borderWidth: 1,
                                    callbacks: {
                                        label(context) {
                                            const label = context.dataset.label || '';
                                            const raw = isHorizontal
                                                ? (context.parsed.x ?? context.parsed)
                                                : (context.parsed.y ?? context.parsed);
                                            return `${label}: ${formatNumber(raw)}`;
                                        }
                                    }
                                },
                                categoryTagPlugin: {
                                    labels: labels,
                                    angled: angledTags
                                },
                                valueLabelPlugin: {
                                    show: showValueLabels,
                                    horizontal: isHorizontal,
                                    targetLabels: totalLabelTargets
                                }
                            }
                        }
                    });

                    // Buat legend
                    const legendElement = document.getElementById('legend-' + chartKey);
                    if (legendElement) {
                        legendElement.innerHTML = '';
                        const legendItems = Array.isArray(config.legendItems) && config.legendItems.length
                            ? config.legendItems
                            : datasets.map((dataset) => ({
                                label: dataset.label || '',
                                color: Array.isArray(dataset.backgroundColor)
                                    ? dataset.backgroundColor[0]
                                    : dataset.backgroundColor
                            }));

                        legendItems.forEach((item) => {
                            if (!item || !item.label) {
                                return;
                            }
                            const legendItem = document.createElement('div');
                            legendItem.className = 'chart-legend-item';
                            legendItem.innerHTML = `
                                <div class="chart-legend-color" style="background-color: ${item.color || '#999'};"></div>
                                <span>${item.label}</span>
                            `;
                            legendElement.appendChild(legendItem);
                        });
                    }
                }, 50);
            }

            ensureChartFunction = ensureChart;

            // Initialize first chart
            setTimeout(function() {
                if (typeof Chart !== 'undefined' && ensureChartFunction) {
                    ensureChartFunction(initialCategory);
                }
            }, 300);
            @endif

            // Tab button event listeners - only if data is available
            @if ($primaryPeriod && $comparePeriod)
            if (tabButtons && tabButtons.length > 0) {
                tabButtons.forEach(function (button) {
                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        var targetSelector = this.getAttribute('data-tab-target');
                        if (!targetSelector) return;

                        showTab(targetSelector);
                        
                        var category = targetSelector.replace('#tab-', '');
                        var url = new URL(window.location.href);
                        url.searchParams.set('category', category);
                        window.history.pushState({}, '', url.toString());

                        // Update breadcrumb jika ada
                        var breadcrumbCategoryText = document.querySelector('.breadcrumb-category-text');
                        if (breadcrumbCategoryText) {
                            var categoryLabels = {
                                'gender': 'Jenis Kelamin',
                                'age': 'Kelompok Umur',
                                'single-age': 'Umur Tunggal',
                                'education': 'Pendidikan',
                                'occupation': 'Pekerjaan',
                                'marital': 'Status Perkawinan',
                                'household': 'Kepala Keluarga',
                                'religion': 'Agama',
                                'wajib-ktp': 'Wajib KTP'
                            };
                            breadcrumbCategoryText.textContent = categoryLabels[category] || 'Jenis Kelamin';
                        }

                        // Initialize chart if data is available
                        if (ensureChartFunction && typeof Chart !== 'undefined') {
                            setTimeout(function() {
                                ensureChartFunction(category);
                            }, 150);
                        }
                    });
                });
            }
            @endif

            // Enable/disable semester dropdown based on year selection
            // This ensures semester dropdown is properly enabled/disabled based on year selection
            function setupYearSemesterDependency(yearSelectName, semesterSelectName) {
                var yearSelect = document.querySelector('select[name="' + yearSelectName + '"]');
                var semesterSelect = document.querySelector('select[name="' + semesterSelectName + '"]');
                
                if (yearSelect && semesterSelect) {
                    // Function to update semester dropdown state
                    function updateSemesterState() {
                        if (yearSelect.value && yearSelect.value !== '') {
                            // Enable semester dropdown when year is selected
                            semesterSelect.disabled = false;
                        } else {
                            // Disable semester dropdown when year is not selected
                            semesterSelect.disabled = true;
                            semesterSelect.value = '';
                        }
                    }
                    
                    // Set initial state
                    updateSemesterState();
                    
                    // Update when year changes
                    yearSelect.addEventListener('change', function() {
                        updateSemesterState();
                        // Form will auto-submit via onchange="this.form.submit()" attribute
                    });
                }
            }

            // Setup dependencies for primary and compare
            setupYearSemesterDependency('year', 'semester');
            setupYearSemesterDependency('compare_year', 'compare_semester');

        });
    </script>
@endpush

@include('public.partials.download-modal', ['type' => 'compare'])
