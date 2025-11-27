<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $categoryLabel }} - {{ $periodLabel }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 20px;
        }
        .header {
            margin-bottom: 20px;
        }
        .header .title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 12px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
            color: #1f3f7a;
        }
        .header p {
            font-size: 9pt;
            margin: 3px 0;
            color: #666;
        }
        .header .period {
            display: inline-flex;
            padding: 4px 12px;
            background-color: #dbeafe;
            color: #1e40af;
            border-radius: 4px;
            font-size: 8pt;
            margin-top: 5px;
            white-space: nowrap;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 9pt;
        }
        td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 9pt;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tfoot th {
            background-color: #f0f0f0;
            color: #000;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title-row">
            <div>
                <h1>{{ $categoryLabel }}</h1>
                <p>{{ $areaDescriptor }}</p>
            </div>
            @if ($periodLabel)
                <span class="period">{{ $periodLabel }}</span>
            @endif
        </div>
    </div>

    @if ($category === 'gender')
        <table>
            <thead>
                <tr>
                    <th style="width: 40px">No</th>
                    <th>{{ $areaTable['column'] ?? 'Wilayah' }}</th>
                    <th class="text-right">Laki-laki</th>
                    <th class="text-right">Perempuan</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($areaTable['rows'] ?? [] as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ \Illuminate\Support\Str::title($row['name']) }}</td>
                        <td class="text-right">{{ number_format($row['male']) }}</td>
                        <td class="text-right">{{ number_format($row['female']) }}</td>
                        <td class="text-right">{{ number_format($row['total']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Data belum tersedia.</td>
                    </tr>
                @endforelse
            </tbody>
            @if (!empty($areaTable['rows']))
                <tfoot>
                    <tr>
                        <th colspan="2">Jumlah Keseluruhan</th>
                        <th class="text-right">{{ number_format($areaTable['totals']['male'] ?? 0) }}</th>
                        <th class="text-right">{{ number_format($areaTable['totals']['female'] ?? 0) }}</th>
                        <th class="text-right">{{ number_format($areaTable['totals']['total'] ?? 0) }}</th>
                    </tr>
                </tfoot>
            @endif
        </table>
    @elseif ($category === 'age')
        <table>
            <thead>
                <tr>
                    <th style="width: 40px">No</th>
                    <th>Kelompok Umur</th>
                    <th class="text-right">Laki-laki</th>
                    <th class="text-right">Perempuan</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ageGroups as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row['label'] }}</td>
                        <td class="text-right">{{ number_format($row['male']) }}</td>
                        <td class="text-right">{{ number_format($row['female']) }}</td>
                        <td class="text-right">{{ number_format($row['total']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Data belum tersedia.</td>
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
        <table>
            <thead>
                <tr>
                    <th style="width: 40px">No</th>
                    <th>Usia</th>
                    <th class="text-right">Laki-laki</th>
                    <th class="text-right">Perempuan</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($singleAges as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row['label'] }}</td>
                        <td class="text-right">{{ number_format($row['male']) }}</td>
                        <td class="text-right">{{ number_format($row['female']) }}</td>
                        <td class="text-right">{{ number_format($row['total']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Data belum tersedia.</td>
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
        <table>
            <thead>
                <tr>
                    <th style="width: 40px">No</th>
                    <th>Pekerjaan</th>
                    <th class="text-right">Laki-laki</th>
                    <th class="text-right">Perempuan</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($topOccupations as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item['label'] }}</td>
                        <td class="text-right">{{ number_format($item['male']) }}</td>
                        <td class="text-right">{{ number_format($item['female']) }}</td>
                        <td class="text-right">{{ number_format($item['total']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Data belum tersedia.</td>
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
    @elseif (in_array($category, ['education', 'wajib-ktp', 'marital', 'household', 'religion']))
        @php
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
            $showOverallSum = $category === 'household';
        @endphp
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 40px">No</th>
                    <th rowspan="2">{{ $columnLabel }}</th>
                    @foreach ($columns as $column)
                        <th colspan="3" class="text-center">{{ $column['label'] }}</th>
                    @endforeach
                    @if ($showOverallSum)
                        <th rowspan="2" class="text-right">Jumlah Keseluruhan</th>
                    @endif
                </tr>
                <tr>
                    @foreach ($columns as $column)
                        <th class="text-right">L</th>
                        <th class="text-right">P</th>
                        <th class="text-right">Jumlah</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $index => $row)
                    @php $rowSum = 0; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ \Illuminate\Support\Str::title($row['name']) }}</td>
                        @foreach ($columns as $column)
                            @php
                                $key = $column['key'];
                                $value = $row['values'][$key] ?? ['male' => 0, 'female' => 0, 'total' => 0];
                                $rowSum += (int) ($value['total'] ?? 0);
                            @endphp
                            <td class="text-right">{{ number_format($value['male']) }}</td>
                            <td class="text-right">{{ number_format($value['female']) }}</td>
                            <td class="text-right">{{ number_format($value['total']) }}</td>
                        @endforeach
                        @if ($showOverallSum)
                            <td class="text-right">{{ number_format($rowSum) }}</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 2 + (count($columns) * 3) + ($showOverallSum ? 1 : 0) }}" class="text-center">Data belum tersedia.</td>
                    </tr>
                @endforelse
            </tbody>
            @if (!empty($rows))
                @php $overallTotal = 0; @endphp
                <tfoot>
                    <tr>
                        <th colspan="2">Jumlah Keseluruhan</th>
                        @foreach ($columns as $column)
                            @php
                                $key = $column['key'];
                                $total = $matrix['totals'][$key] ?? ['male' => 0, 'female' => 0, 'total' => 0];
                                $overallTotal += (int) ($total['total'] ?? 0);
                            @endphp
                            <th class="text-right">{{ number_format($total['male']) }}</th>
                            <th class="text-right">{{ number_format($total['female']) }}</th>
                            <th class="text-right">{{ number_format($total['total']) }}</th>
                        @endforeach
                        @if ($showOverallSum)
                            <th class="text-right">{{ number_format($overallTotal) }}</th>
                        @endif
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif
</body>
</html>

