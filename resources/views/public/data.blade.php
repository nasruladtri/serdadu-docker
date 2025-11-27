@extends('layouts.dukcapil', ['title' => 'Data Agregat'])

@push('styles')
    <style>
        /* Pastikan table heading memiliki jarak yang cukup dengan konten di bawahnya */
        .dk-table-heading {
            margin-bottom: 2rem;
        }

        /* Row highlight styling for tables (light + dark) */
        .table-row-highlight {
            background-color: #f3f6fb;
        }
        .dark .table-row-highlight {
            background-color: #152540;
        }
        
        /* Tambahkan jarak tambahan antara label dan tabel pada layar kecil */
        @media (max-width: 640px) {
            .dk-table-heading {
                margin-bottom: 1.5rem;
            }
            
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
    @endphp


    {{-- Kartu filter untuk memilih wilayah dan periode data agregat yang ditampilkan --}}
    <div class="dk-card mb-4 animate-fade-in-up">
        <div class="p-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-end">
                <div class="lg:col-span-3">
                    <h6 class="dk-card__title mb-1">Wilayah & Periode</h6>
                    <p class="text-xs text-gray-500 mb-0">
                        Pilih tahun, semester, kecamatan, atau desa/kelurahan untuk menampilkan data spesifik.
                    </p>
                </div>
                <form method="GET" class="lg:col-span-9">
                    {{-- Set filter wilayah dan periode; setiap perubahan auto submit agar data langsung diperbarui --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 items-end">
                        <div class="sm:col-span-1 md:col-span-1 lg:col-span-1">
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Tahun</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="year" onchange="this.form.submit()">
                                <option value="">Pilih Tahun</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" {{ (int) ($selectedYear ?? 0) === $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-1 md:col-span-1 lg:col-span-1">
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Semester</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed" name="semester" onchange="this.form.submit()" {{ !$selectedYear ? 'disabled' : '' }}>
                                <option value="">Pilih Semester</option>
                                @forelse ($semesterOptions as $option)
                                    <option value="{{ $option }}" {{ (int) ($selectedSemester ?? 0) === $option ? 'selected' : '' }}>
                                        Semester {{ $option }}
                                    </option>
                                @empty
                                    <option value="" disabled>Belum tersedia</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="sm:col-span-1 md:col-span-1 lg:col-span-1">
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Kecamatan</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="district_id" onchange="this.form.submit()">
                                <option value="">Semua Kecamatan</option>
                                @foreach ($districts as $district)
                                    <option value="{{ $district->id }}" {{ (int) $selectedDistrict === $district->id ? 'selected' : '' }}>
                                        {{ \Illuminate\Support\Str::title($district->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-1 md:col-span-1 lg:col-span-1 dk-filter-village">
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Desa/Kelurahan</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed" name="village_id" onchange="this.form.submit()" {{ $villages->isEmpty() ? 'disabled' : '' }}>
                                <option value="">Semua Desa/Kelurahan</option>
                                @foreach ($villages as $village)
                                    <option value="{{ $village->id }}" {{ (int) $selectedVillage === $village->id ? 'selected' : '' }}>
                                        {{ \Illuminate\Support\Str::title($village->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-1 md:col-span-1 lg:col-span-1">
                            <a href="{{ route('public.data') }}" class="inline-flex items-center justify-center w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Tampilkan pesan jika belum ada dataset yang dipilih atau diunggah --}}
    @if (!$period)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 dk-card text-[#009B4D]">
            @if (empty($periods))
                <strong class="text-[#009B4D]">Data belum tersedia.</strong> <span class="text-[#009B4D]">Unggah dataset terlebih dahulu untuk menampilkan ringkasan agregat.</span>
            @else
                <strong class="text-[#009B4D]">Pilih Filter.</strong> <span class="text-[#009B4D]">Silakan pilih tahun, semester, kecamatan, atau desa/kelurahan untuk menampilkan ringkasan agregat.</span>
            @endif
        </div>
    @else
        {{-- Inisialisasi variabel bantu untuk menyusun informasi tabel dan label tampilan --}}
        @php
            $areaRows = $areaTable['rows'] ?? [];
            $areaTotals = $areaTable['totals'] ?? ['male' => 0, 'female' => 0, 'total' => 0];
            $areaColumn = $areaTable['column'] ?? 'Wilayah';
            if ($areaColumn === 'SEMUA' || $areaColumn === 'Wilayah') {
                $areaColumn = 'Kecamatan';
            }
            $districtName = isset($selectedDistrict) && $selectedDistrict ? optional($districts->firstWhere('id', (int) $selectedDistrict))->name : null;
            $villageName = isset($selectedVillage) && $selectedVillage ? optional($villages->firstWhere('id', (int) $selectedVillage))->name : null;
            $kabupatenName = config('app.region_name', 'Kabupaten Madiun');
            $areaSegments = [$kabupatenName];
            if ($districtName) {
                $areaSegments[] = 'Kecamatan ' . \Illuminate\Support\Str::title($districtName);
                $areaSegments[] = $villageName ? ('Desa/Kelurahan ' . \Illuminate\Support\Str::title($villageName)) : 'Semua Desa/Kelurahan';
            } else {
                $areaSegments[] = 'Semua Kecamatan';
                $areaSegments[] = 'Semua Desa/Kelurahan';
            }
            $areaDescriptor = implode(' > ', array_filter($areaSegments));
            $periodLabelParts = [];
            if (isset($period) && is_array($period)) {
                if (isset($period['semester']) && !empty($period['semester'])) {
                    $periodLabelParts[] = 'Semester ' . $period['semester'];
                }
                if (isset($period['year']) && !empty($period['year'])) {
                    $periodLabelParts[] = 'Tahun ' . $period['year'];
                }
            }
            $periodLabel = !empty($periodLabelParts) ? implode(' ', $periodLabelParts) : null;
        @endphp

        <div class="dk-card mt-4 animate-fade-in-up delay-200">
            {{-- Navigasi tab pada layar desktop untuk berpindah antar kategori data --}}
            <ul class="dk-tabs" id="aggregateTabs" role="tablist">
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

            <div class="dk-tab-content mt-0" id="aggregateTabsContent">
                {{-- Tab ringkasan berdasarkan jenis kelamin --}}
                <div class="dk-tab-pane show active" id="tab-gender" role="tabpanel"
                    aria-labelledby="tab-gender-tab">
                    @include('public.partials.table-heading', [
                        'title' => $tabs['gender'],
                        'areaDescriptor' => $areaDescriptor,
                        'periodLabel' => $periodLabel,
                        'showDownloadButtons' => true,
                        'activeCategory' => 'gender',
                    ])
                    <div class="overflow-x-auto dk-table-scroll relative">
                        <table class="w-full text-sm dk-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 64px">No</th>
                                    <th>{{ $areaColumn }}</th>
                                    <th class="text-right">L</th>
                                    <th class="text-right">P</th>
                                    <th class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($areaRows as $index => $row)
                                    @php
                                        $isHighlighted = isset($row['highlight']) && !empty($row['highlight']);
                                        $rowName = isset($row['name']) ? $row['name'] : '-';
                                        $rowMale = isset($row['male']) ? $row['male'] : 0;
                                        $rowFemale = isset($row['female']) ? $row['female'] : 0;
                                        $rowTotal = isset($row['total']) ? $row['total'] : 0;
                                    @endphp
                                    @php
                                        $rowClass = $isHighlighted ? 'table-row-highlight' : '';
                                    @endphp
                                    <tr class="{{ $rowClass }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Illuminate\Support\Str::title($rowName) }}</td>
                                        <td class="text-right">{{ number_format($rowMale) }}</td>
                                        <td class="text-right">{{ number_format($rowFemale) }}</td>
                                        <td class="text-right font-semibold">{{ number_format($rowTotal) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-gray-500">Data jenis kelamin belum tersedia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if (!empty($areaRows))
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Jumlah Keseluruhan</th>
                                        <th class="text-right">{{ number_format($areaTotals['male'] ?? 0) }}</th>
                                        <th class="text-right">{{ number_format($areaTotals['female'] ?? 0) }}</th>
                                        <th class="text-right">{{ number_format($areaTotals['total'] ?? 0) }}</th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- Tab ringkasan berdasarkan kelompok umur --}}
                <div class="dk-tab-pane hidden" id="tab-age" role="tabpanel"
                    aria-labelledby="tab-age-tab">
                    @include('public.partials.table-heading', [
                        'title' => $tabs['age'],
                        'areaDescriptor' => $areaDescriptor,
                        'periodLabel' => $periodLabel,
                        'showDownloadButtons' => true,
                        'activeCategory' => 'age',
                    ])
                    <div class="overflow-x-auto dk-table-scroll">
                        <table class="w-full text-sm dk-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 64px">No</th>
                                    <th>Kelompok</th>
                                    <th class="text-right">L</th>
                                    <th class="text-right">P</th>
                                    <th class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ageGroups as $index => $row)
                                    @php
                                        $rowLabel = isset($row['label']) ? $row['label'] : '-';
                                        $rowMale = isset($row['male']) ? $row['male'] : 0;
                                        $rowFemale = isset($row['female']) ? $row['female'] : 0;
                                        $rowTotal = isset($row['total']) ? $row['total'] : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $rowLabel }}</td>
                                        <td class="text-right">{{ number_format($rowMale) }}</td>
                                        <td class="text-right">{{ number_format($rowFemale) }}</td>
                                        <td class="text-right font-semibold">{{ number_format($rowTotal) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-gray-500">Data kelompok umur belum tersedia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if (!empty($ageGroups) && is_array($ageGroups))
                                @php
                                    $ageMale = array_sum(array_column($ageGroups, 'male'));
                                    $ageFemale = array_sum(array_column($ageGroups, 'female'));
                                    $ageTotal = array_sum(array_column($ageGroups, 'total'));
                                @endphp
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Jumlah Keseluruhan</th>
                                        <th class="text-right">{{ number_format($ageMale) }}</th>
                                        <th class="text-right">{{ number_format($ageFemale) }}</th>
                                        <th class="text-right">{{ number_format($ageTotal) }}</th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                        
                    </div>
                </div>

                {{-- Tab matriks pendidikan penduduk --}}
                <div class="dk-tab-pane hidden" id="tab-education" role="tabpanel"
                    aria-labelledby="tab-education-tab">
                    @include('public.partials.table-heading', [
                        'title' => $tabs['education'],
                        'areaDescriptor' => $areaDescriptor,
                        'periodLabel' => $periodLabel,
                        'showDownloadButtons' => true,
                        'activeCategory' => 'education',
                    ])
                    <div class="overflow-x-auto dk-table-scroll">
                        @include('public.partials.matrix-table', [
                            'matrix' => $educationMatrix,
                            'emptyMessage' => 'Data pendidikan belum tersedia.'
                        ])
                        
                    </div>
                </div>

                {{-- Tab data pekerjaan terbanyak --}}
                <div class="dk-tab-pane hidden" id="tab-occupation" role="tabpanel"
                    aria-labelledby="tab-occupation-tab">
                    @include('public.partials.table-heading', [
                        'title' => $tabs['occupation'],
                        'areaDescriptor' => $areaDescriptor,
                        'periodLabel' => $periodLabel,
                        'showDownloadButtons' => true,
                        'activeCategory' => 'occupation',
                    ])
                    <div class="overflow-x-auto dk-table-scroll">
                        <table class="w-full text-sm dk-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 64px">No</th>
                                    <th>Pekerjaan</th>
                                    <th class="text-right">L</th>
                                    <th class="text-right">P</th>
                                    <th class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topOccupations as $item)
                                    @php
                                        $itemLabel = isset($item['label']) ? $item['label'] : '-';
                                        $itemMale = isset($item['male']) ? $item['male'] : 0;
                                        $itemFemale = isset($item['female']) ? $item['female'] : 0;
                                        $itemTotal = isset($item['total']) ? $item['total'] : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $itemLabel }}</td>
                                        <td class="text-right">{{ number_format($itemMale) }}</td>
                                        <td class="text-right">{{ number_format($itemFemale) }}</td>
                                        <td class="text-right font-semibold">{{ number_format($itemTotal) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-gray-500">Data pekerjaan belum tersedia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if (!empty($topOccupations))
                                @php
                                    $jobMale = is_array($topOccupations) ? array_sum(array_column($topOccupations, 'male')) : 0;
                                    $jobFemale = is_array($topOccupations) ? array_sum(array_column($topOccupations, 'female')) : 0;
                                    $jobTotal = is_array($topOccupations) ? array_sum(array_column($topOccupations, 'total')) : 0;
                                @endphp
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Jumlah Keseluruhan</th>
                                        <th class="text-right">{{ number_format($jobMale) }}</th>
                                        <th class="text-right">{{ number_format($jobFemale) }}</th>
                                        <th class="text-right">{{ number_format($jobTotal) }}</th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                        
                    </div>
                </div>

                {{-- Tab distribusi umur tunggal (setiap usia) --}}
                <div class="dk-tab-pane hidden" id="tab-single-age" role="tabpanel"
                    aria-labelledby="tab-single-age-tab">
                    @include('public.partials.table-heading', [
                        'title' => $tabs['single-age'],
                        'areaDescriptor' => $areaDescriptor,
                        'periodLabel' => $periodLabel,
                        'showDownloadButtons' => true,
                        'activeCategory' => 'single-age',
                    ])
                    <div class="overflow-x-auto dk-table-scroll">
                        <table class="w-full text-sm dk-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 64px">No</th>
                                    <th>Usia</th>
                                    <th class="text-right">L</th>
                                    <th class="text-right">P</th>
                                    <th class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($singleAges as $row)
                                    @php
                                        $rowLabel = isset($row['label']) ? $row['label'] : '-';
                                        $rowMale = isset($row['male']) ? $row['male'] : 0;
                                        $rowFemale = isset($row['female']) ? $row['female'] : 0;
                                        $rowTotal = isset($row['total']) ? $row['total'] : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $rowLabel }}</td>
                                        <td class="text-right">{{ number_format($rowMale) }}</td>
                                        <td class="text-right">{{ number_format($rowFemale) }}</td>
                                        <td class="text-right font-semibold">{{ number_format($rowTotal) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-gray-500">Data umur tunggal belum tersedia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if (!empty($singleAges) && is_array($singleAges))
                                @php
                                $singleMale = array_sum(array_column($singleAges, 'male'));
                                $singleFemale = array_sum(array_column($singleAges, 'female'));
                                $singleTotal = array_sum(array_column($singleAges, 'total'));
                                @endphp
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Jumlah Keseluruhan</th>
                                        <th class="text-right">{{ number_format($singleMale) }}</th>
                                        <th class="text-right">{{ number_format($singleFemale) }}</th>
                                        <th class="text-right">{{ number_format($singleTotal) }}</th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                        
                    </div>
                </div>

                {{-- Tab matriks penduduk wajib KTP --}}
                <div class="dk-tab-pane hidden" id="tab-wajib-ktp" role="tabpanel"
                    aria-labelledby="tab-wajib-ktp-tab">
                    @include('public.partials.table-heading', [
                        'title' => $tabs['wajib-ktp'],
                        'areaDescriptor' => $areaDescriptor,
                        'periodLabel' => $periodLabel,
                        'showDownloadButtons' => true,
                        'activeCategory' => 'wajib-ktp',
                    ])
                    <div class="overflow-x-auto dk-table-scroll">
                        @include('public.partials.matrix-table', [
                            'matrix' => $wajibKtpMatrix,
                            'emptyMessage' => 'Data wajib KTP belum tersedia.'
                        ])
                        
                    </div>
                </div>

                {{-- Tab matriks status perkawinan --}}
                <div class="dk-tab-pane hidden" id="tab-marital" role="tabpanel"
                    aria-labelledby="tab-marital-tab">
                    @include('public.partials.table-heading', [
                        'title' => $tabs['marital'],
                        'areaDescriptor' => $areaDescriptor,
                        'periodLabel' => $periodLabel,
                        'showDownloadButtons' => true,
                        'activeCategory' => 'marital',
                    ])
                    <div class="overflow-x-auto dk-table-scroll">
                        @include('public.partials.matrix-table', [
                            'matrix' => $maritalMatrix,
                            'emptyMessage' => 'Data status perkawinan belum tersedia.'
                        ])
                        
                    </div>
                </div>

                {{-- Tab matriks kepala keluarga --}}
                <div class="dk-tab-pane hidden" id="tab-household" role="tabpanel"
                    aria-labelledby="tab-household-tab">
                    @include('public.partials.table-heading', [
                        'title' => $tabs['household'],
                        'areaDescriptor' => $areaDescriptor,
                        'periodLabel' => $periodLabel,
                        'showDownloadButtons' => true,
                        'activeCategory' => 'household',
                    ])
                    <div class="overflow-x-auto dk-table-scroll">
                        @include('public.partials.matrix-table', [
                            'matrix' => $headHouseholdMatrix,
                            'emptyMessage' => 'Data kepala keluarga belum tersedia.',
                            'showOverallSum' => true
                        ])
                        
                    </div>
                </div>

                {{-- Tab matriks agama penduduk --}}
                <div class="dk-tab-pane hidden" id="tab-religion" role="tabpanel"
                    aria-labelledby="tab-religion-tab">
                    @include('public.partials.table-heading', [
                        'title' => $tabs['religion'],
                        'areaDescriptor' => $areaDescriptor,
                        'periodLabel' => $periodLabel,
                        'showDownloadButtons' => true,
                        'activeCategory' => 'religion',
                    ])
                    <div class="overflow-x-auto dk-table-scroll">
                        @include('public.partials.matrix-table', [
                            'matrix' => $religionMatrix,
                            'emptyMessage' => 'Data agama belum tersedia.'
                        ])
                        
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    {{-- Sinkronisasi antara dropdown kategori dan tab --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Baca parameter category dari URL saat halaman dimuat
            var urlParams = new URLSearchParams(window.location.search);
            var categoryParam = urlParams.get('category');
            
            // Jika ada parameter category, aktifkan tab yang sesuai
            if (categoryParam) {
                var targetTabId = '#tab-' + categoryParam;
                var targetTab = document.querySelector(targetTabId);
                if (targetTab) {
                    // Update tab button
                    document.querySelectorAll('#aggregateTabs button').forEach(function(btn) {
                        btn.classList.remove('active');
                        btn.setAttribute('aria-selected', 'false');
                    });
                    var activeButton = document.querySelector('#aggregateTabs button[data-tab-target="' + targetTabId + '"]');
                    if (activeButton) {
                        activeButton.classList.add('active');
                        activeButton.setAttribute('aria-selected', 'true');
                    }
                    
                    // Update tab pane
                    document.querySelectorAll('.dk-tab-pane').forEach(function(pane) {
                        pane.classList.add('hidden');
                        pane.classList.remove('show', 'active');
                    });
                    targetTab.classList.remove('hidden');
                    targetTab.classList.add('show', 'active');
                }
            }
            
            // Function untuk update fullscreen button URL (didefinisikan sebelum digunakan)
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
            
            // Helper function untuk show tab
            function showTab(targetId) {
                // Hide all tab panes
                document.querySelectorAll('.dk-tab-pane').forEach(function(pane) {
                    pane.classList.add('hidden');
                    pane.classList.remove('show', 'active');
                });
                
                // Show target tab pane
                var targetPane = document.querySelector(targetId);
                if (targetPane) {
                    targetPane.classList.remove('hidden');
                    targetPane.classList.add('show', 'active');
                }
                
                // Update tab buttons
                document.querySelectorAll('#aggregateTabs button').forEach(function(btn) {
                    btn.classList.remove('active');
                    btn.setAttribute('aria-selected', 'false');
                });
                
                var activeButton = document.querySelector('#aggregateTabs button[data-tab-target="' + targetId + '"]');
                if (activeButton) {
                    activeButton.classList.add('active');
                    activeButton.setAttribute('aria-selected', 'true');
                }
            }

            // Ambil elemen tombol tab
            var tabButtons = document.querySelectorAll('#aggregateTabs button[data-tab-target]');

            // Event listener untuk tombol tab
            tabButtons.forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    var targetSelector = this.getAttribute('data-tab-target');
                    if (!targetSelector) {
                        return;
                    }

                    showTab(targetSelector);
                    
                    // Update URL dengan parameter category
                    var category = targetSelector.replace('#tab-', '');
                    var url = new URL(window.location.href);
                    url.searchParams.set('category', category);
                    window.history.pushState({}, '', url.toString());
                    
                    // Update download buttons URLs
                    updateDownloadButtons(category);
                    
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
                });
            });

            // Function to update download buttons URLs
            function updateDownloadButtons(category) {
                var activePane = document.querySelector('.dk-tab-pane.active');
                if (!activePane) return;
                
                var downloadSection = activePane.querySelector('.mt-4.flex.flex-wrap');
                if (!downloadSection) return;
                
                var currentQuery = new URLSearchParams(window.location.search);
                currentQuery.set('category', category);
                
                var pdfButton = downloadSection.querySelector('.js-download-btn[data-download-format="pdf"]');
                var excelButton = downloadSection.querySelector('.js-download-btn[data-download-format="excel"]');
                
                if (pdfButton) {
                    var pdfUrl = '{{ route("public.data.download.pdf", []) }}?' + currentQuery.toString();
                    pdfButton.setAttribute('data-download-url', pdfUrl);
                    // Remove handler flag to allow re-attachment
                    pdfButton.removeAttribute('data-handler-attached');
                    
                    var pdfYear = currentQuery.get('year') || pdfButton.getAttribute('data-year-default') || '{{ $period['year'] ?? now()->year }}';
                    var pdfSemester = currentQuery.get('semester') || pdfButton.getAttribute('data-semester-default') || '{{ $period['semester'] ?? 1 }}';
                    var pdfLabelBase = 'data-' + category + '-' + pdfYear + '-s' + pdfSemester;
                    pdfButton.setAttribute('data-download-label', pdfLabelBase + '.pdf');
                }
                
                if (excelButton) {
                    var excelUrl = '{{ route("public.data.download.excel", []) }}?' + currentQuery.toString();
                    excelButton.setAttribute('data-download-url', excelUrl);
                    // Remove handler flag to allow re-attachment
                    excelButton.removeAttribute('data-handler-attached');
                    
                    var excelYear = currentQuery.get('year') || excelButton.getAttribute('data-year-default') || '{{ $period['year'] ?? now()->year }}';
                    var excelSemester = currentQuery.get('semester') || excelButton.getAttribute('data-semester-default') || '{{ $period['semester'] ?? 1 }}';
                    var excelLabelBase = 'data-' + category + '-' + excelYear + '-s' + excelSemester;
                    excelButton.setAttribute('data-download-label', excelLabelBase + '.xlsx');
                }
                
                // Re-attach handlers after URL update
                if (typeof window.attachDirectHandlers === 'function') {
                    window.attachDirectHandlers();
                }
            }

            // Fungsi untuk mengatur scrollbar tabel berdasarkan jumlah baris
            function setupTableScroll() {
                var tableContainers = document.querySelectorAll('.dk-tab-content .dk-table-scroll');
                
                tableContainers.forEach(function(container) {
                    var table = container.querySelector('.dk-table');
                    if (!table) return;
                    
                    var tbody = table.querySelector('tbody');
                    if (!tbody) return;
                    
                    // Hitung semua baris data (tidak termasuk baris kosong/empty state)
                    var rows = tbody.querySelectorAll('tr');
                    var dataRows = Array.from(rows).filter(function(row) {
                        var cells = row.querySelectorAll('td');
                        if (cells.length === 0) return false;
                        // Skip baris dengan class text-center text-muted (empty state)
                        var firstCell = cells[0];
                        var isEmptyState = firstCell.classList.contains('text-center') && firstCell.classList.contains('text-muted');
                        return !isEmptyState;
                    });
                    
                    var totalRows = dataRows.length;
                    
                    // Jika lebih dari 17 baris, aktifkan scrollbar
                    if (totalRows > 17) {
                        // Hitung tinggi untuk 17 baris
                        if (dataRows.length > 0) {
                            var firstRow = dataRows[0];
                            var rowHeight = firstRow.offsetHeight || 50; // fallback 50px jika tidak bisa dihitung
                            
                            var thead = table.querySelector('thead');
                            var tfoot = table.querySelector('tfoot');
                            var headerHeight = thead ? thead.offsetHeight : 0;
                            var footerHeight = tfoot ? tfoot.offsetHeight : 0;
                            
                            // Tinggi untuk 17 baris + header + footer + sedikit padding
                            var maxHeight = (rowHeight * 17) + headerHeight + footerHeight + 10;
                            
                            container.style.maxHeight = maxHeight + 'px';
                            container.classList.add('has-scroll');
                        }
                    } else {
                        container.style.maxHeight = 'none';
                        container.classList.remove('has-scroll');
                    }
                });
            }

            // Inisialisasi URL fullscreen button setelah tab diaktifkan
            // Tunggu sedikit untuk memastikan DOM sudah ter-render
            setTimeout(function() {
                updateFullscreenButtons();
            }, 100);

            // Wrap showTab untuk menambahkan side effects
            var originalShowTab = showTab;
            showTab = function(targetId) {
                originalShowTab(targetId);
                // Tunggu sedikit agar DOM sudah ter-render
                setTimeout(function() {
                    setupTableScroll();
                    updateFullscreenButtons();
                    // Pastikan class active tetap ada
                    var activeButton = document.querySelector('#aggregateTabs button[data-tab-target="' + targetId + '"]');
                    if (activeButton && !activeButton.classList.contains('active')) {
                        activeButton.classList.add('active');
                        activeButton.setAttribute('aria-selected', 'true');
                    }
                }, 100);
            };
            
            // Jalankan setupTableScroll saat halaman dimuat
            setupTableScroll();
            
            // Update fullscreen button saat dropdown berubah (sudah ditangani di showTab)
        });
    </script>
@endpush

@include('public.partials.download-modal', ['type' => 'table'])

