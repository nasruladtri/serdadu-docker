<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $categoryLabel }} - Perbandingan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
            color: #1f3f7a;
        }
        .comparison-container {
            display: table;
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .comparison-section {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }
        .section-meta {
            font-size: 7pt;
            color: #555;
            text-align: center;
            margin-bottom: 8px;
        }
        .section-header {
            background-color: #4472C4;
            color: white;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
            border-radius: 4px 4px 0 0;
        }
        .section-header.primary {
            background-color: #3b82f6;
        }
        .section-header.compare {
            background-color: #f59e0b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #f0f0f0;
            color: #000;
            font-weight: bold;
            padding: 6px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 8pt;
        }
        td {
            padding: 5px;
            border: 1px solid #ddd;
            font-size: 8pt;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .note {
            margin-top: 15px;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 4px;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $categoryLabel }} - Perbandingan</h1>
        @if(!empty($primaryAreaDescriptor) || !empty($compareAreaDescriptor))
            <p style="font-size:8pt; color:#555; margin:4px 0 0;">
                Wilayah Data Utama: {{ $primaryAreaDescriptor ?? 'Semua Kecamatan > Semua Desa/Kelurahan' }}
            </p>
            <p style="font-size:8pt; color:#555; margin:2px 0 0;">
                Wilayah Data Pembanding: {{ $compareAreaDescriptor ?? 'Semua Kecamatan > Semua Desa/Kelurahan' }}
            </p>
        @endif
    </div>

    @php
        $rowCount = max(count($primaryChart['labels'] ?? []), count($compareChart['labels'] ?? []));
        $chunkSize = 15;
        $chunkCount = max(1, (int) ceil($rowCount / $chunkSize));
    @endphp
    @if ($primaryChart && $compareChart && !empty($primaryChart['labels']))
        @for ($chunkIndex = 0; $chunkIndex < $chunkCount; $chunkIndex++)
            @php
                $start = $chunkIndex * $chunkSize;
                $primaryLabelsChunk = array_slice($primaryChart['labels'] ?? [], $start, $chunkSize);
                $compareLabelsChunk = array_slice($compareChart['labels'] ?? [], $start, $chunkSize);
                $primaryDatasetsChunk = [];
                foreach ($primaryChart['datasets'] ?? [] as $dataset) {
                    $primaryDatasetsChunk[] = [
                        'label' => $dataset['label'],
                        'data' => array_slice($dataset['data'] ?? [], $start, $chunkSize),
                    ];
                }
                $compareDatasetsChunk = [];
                foreach ($compareChart['datasets'] ?? [] as $dataset) {
                    $compareDatasetsChunk[] = [
                        'label' => $dataset['label'],
                        'data' => array_slice($dataset['data'] ?? [], $start, $chunkSize),
                    ];
                }
            @endphp
            <div class="comparison-container">
                <div class="comparison-section">
                    <div class="section-header primary">{{ $primaryLabel }}</div>
                    @if(!empty($primaryAreaDescriptor))
                        <div class="section-meta">{{ $primaryAreaDescriptor }}</div>
                    @endif
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 30px">No</th>
                                <th>Kategori</th>
                                @foreach ($primaryDatasetsChunk as $dataset)
                                    <th class="text-right">{{ $dataset['label'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($primaryLabelsChunk as $index => $label)
                                <tr>
                                    <td class="text-center">{{ $start + $loop->index + 1 }}</td>
                                    <td>{{ $label }}</td>
                                    @foreach ($primaryDatasetsChunk as $dataset)
                                        <td class="text-right">{{ number_format($dataset['data'][$index] ?? 0) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="comparison-section">
                    <div class="section-header compare">{{ $compareLabel }}</div>
                    @if(!empty($compareAreaDescriptor))
                        <div class="section-meta">{{ $compareAreaDescriptor }}</div>
                    @endif
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 30px">No</th>
                                <th>Kategori</th>
                                @foreach ($compareDatasetsChunk as $dataset)
                                    <th class="text-right">{{ $dataset['label'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($compareLabelsChunk as $index => $label)
                                <tr>
                                    <td class="text-center">{{ $start + $loop->index + 1 }}</td>
                                    <td>{{ $label }}</td>
                                    @foreach ($compareDatasetsChunk as $dataset)
                                        <td class="text-right">{{ number_format($dataset['data'][$index] ?? 0) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endfor
        <div class="note">
            <p>Catatan: Data ditampilkan dalam bentuk tabel perbandingan. Untuk melihat grafik visual, silakan akses halaman web.</p>
        </div>
    @else
        <p style="text-align: center; color: #999; padding: 20px;">Data belum tersedia.</p>
    @endif
</body>
</html>

