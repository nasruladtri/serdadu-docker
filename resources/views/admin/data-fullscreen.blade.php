<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Data Agregat - Fullscreen' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/kabupaten-madiun.png') }}?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-31on1Uwx1PcT6zG17Q6C7GdYr387cMGX5CujjJVOk+3O8VjMBYPWaFzx5b9mzfFh1YgUo10xXMYN9bB+FsSjVg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .table-wrapper::-webkit-scrollbar {
            height: 8px;
        }
        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .table-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="m-0 p-0 min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 font-sans">
    <a href="{{ route('admin.data', request()->query()) }}" class="fixed top-6 right-6 z-[1000] bg-white border-2 border-slate-300 rounded-xl px-5 py-3 text-slate-700 text-sm font-medium no-underline inline-flex items-center gap-2 transition-all duration-200 shadow-md hover:bg-primary hover:text-white hover:border-primary hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 md:top-4 md:right-4 md:px-4 md:py-2.5 md:text-xs" title="Kembali ke halaman utama">
        <i class="fas fa-times"></i>
        <span>Tutup</span>
    </a>
    
    <div class="max-w-full mx-auto p-8 md:p-4">
    
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
        $categoryLabel = $tabs[$category] ?? 'Data Agregat';
        $districtName = $selectedDistrict ? optional($districts->firstWhere('id', (int) $selectedDistrict))->name : null;
        $villageName = $selectedVillage ? optional($villages->firstWhere('id', (int) $selectedVillage))->name : null;
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
        if (!empty($period['semester'])) {
            $periodLabelParts[] = 'Semester ' . $period['semester'];
        }
        if (!empty($period['year'])) {
            $periodLabelParts[] = 'Tahun ' . $period['year'];
        }
        $periodLabel = !empty($periodLabelParts) ? implode(' ', $periodLabelParts) : null;
        $areaRows = $areaTable['rows'] ?? [];
        $areaTotals = $areaTable['totals'] ?? ['male' => 0, 'female' => 0, 'total' => 0];
        $areaColumn = $areaTable['column'] ?? 'Wilayah';
        if ($areaColumn === 'SEMUA' || $areaColumn === 'Wilayah') {
            $areaColumn = 'Kecamatan';
        }
    @endphp
    
    <div class="bg-white rounded-2xl shadow-2xl p-10 md:p-6 mt-4">
        <div class="border-b-2 border-slate-200 pb-6 mb-8">
            <h1 class="text-3xl md:text-2xl font-bold text-primary m-0 mb-3 leading-tight">{{ $categoryLabel }}</h1>
            @if (!empty($areaDescriptor))
                <p class="text-sm text-slate-500 my-2 leading-relaxed">{{ $areaDescriptor }}</p>
            @endif
            @if ($periodLabel)
                <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-2">{{ $periodLabel }}</span>
            @endif
        </div>

        @if (!$period)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <strong class="text-yellow-800">Data belum tersedia.</strong> <span class="text-yellow-700">Unggah dataset terlebih dahulu untuk menampilkan ringkasan agregat.</span>
            </div>
        @else
            <div class="overflow-x-auto w-full -webkit-overflow-scrolling-touch table-wrapper">
                @if ($category === 'gender')
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
                                    $isHighlighted = !empty($row['highlight']);
                                @endphp
                                <tr class="{{ $isHighlighted ? 'bg-gray-100' : '' }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ \Illuminate\Support\Str::title($row['name']) }}</td>
                                    <td class="text-right">{{ number_format($row['male']) }}</td>
                                    <td class="text-right">{{ number_format($row['female']) }}</td>
                                    <td class="text-right font-semibold">{{ number_format($row['total']) }}</td>
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
                @elseif ($category === 'age')
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
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row['label'] }}</td>
                                    <td class="text-right">{{ number_format($row['male']) }}</td>
                                    <td class="text-right">{{ number_format($row['female']) }}</td>
                                    <td class="text-right font-semibold">{{ number_format($row['total']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-gray-500">Data kelompok umur belum tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if (!empty($ageGroups))
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
                @elseif ($category === 'single-age')
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
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row['label'] }}</td>
                                    <td class="text-right">{{ number_format($row['male']) }}</td>
                                    <td class="text-right">{{ number_format($row['female']) }}</td>
                                    <td class="text-right font-semibold">{{ number_format($row['total']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-gray-500">Data umur tunggal belum tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if (!empty($singleAges))
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
                @elseif ($category === 'occupation')
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
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item['label'] }}</td>
                                    <td class="text-right">{{ number_format($item['male']) }}</td>
                                    <td class="text-right">{{ number_format($item['female']) }}</td>
                                    <td class="text-right font-semibold">{{ number_format($item['total']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-gray-500">Data pekerjaan belum tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if (!empty($topOccupations))
                            @php
                                $jobMale = array_sum(array_column($topOccupations, 'male'));
                                $jobFemale = array_sum(array_column($topOccupations, 'female'));
                                $jobTotal = array_sum(array_column($topOccupations, 'total'));
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
                @elseif ($category === 'education')
                    @include('public.partials.matrix-table', [
                        'matrix' => $educationMatrix,
                        'emptyMessage' => 'Data pendidikan belum tersedia.'
                    ])
                @elseif ($category === 'wajib-ktp')
                    @include('public.partials.matrix-table', [
                        'matrix' => $wajibKtpMatrix,
                        'emptyMessage' => 'Data wajib KTP belum tersedia.'
                    ])
                @elseif ($category === 'marital')
                    @include('public.partials.matrix-table', [
                        'matrix' => $maritalMatrix,
                        'emptyMessage' => 'Data status perkawinan belum tersedia.'
                    ])
                @elseif ($category === 'household')
                    @include('public.partials.matrix-table', [
                        'matrix' => $headHouseholdMatrix,
                        'emptyMessage' => 'Data kepala keluarga belum tersedia.',
                        'showOverallSum' => true
                    ])
                @elseif ($category === 'religion')
                    @include('public.partials.matrix-table', [
                        'matrix' => $religionMatrix,
                        'emptyMessage' => 'Data agama belum tersedia.'
                    ])
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <strong class="text-yellow-800">Kategori tidak ditemukan.</strong> <span class="text-yellow-700">Kategori "{{ $category }}" belum didukung.</span>
                    </div>
                @endif
            </div>
        @endif
    </div>
    </div>

</body>
</html>
