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
            text-align: center;
            margin-bottom: 20px;
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
            display: inline-block;
            padding: 4px 12px;
            background-color: #dbeafe;
            color: #1e40af;
            border-radius: 4px;
            font-size: 8pt;
            margin-top: 5px;
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
        <h1>{{ $categoryLabel }}</h1>
        <p>{{ $areaDescriptor }}</p>
        @if ($periodLabel)
            <span class="period">{{ $periodLabel }}</span>
        @endif
    </div>

    @if ($chart && !empty($chart['labels']))
        <table>
            <thead>
                <tr>
                    <th style="width: 40px">No</th>
                    <th>Kategori</th>
                    @if (!empty($chart['datasets']))
                        @foreach ($chart['datasets'] as $dataset)
                            <th class="text-right">{{ $dataset['label'] }}</th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($chart['labels'] as $index => $label)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $label }}</td>
                        @if (!empty($chart['datasets']))
                            @foreach ($chart['datasets'] as $dataset)
                                <td class="text-right">{{ number_format($dataset['data'][$index] ?? 0) }}</td>
                            @endforeach
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="note">
            <p>Catatan: Data ditampilkan dalam bentuk tabel. Untuk melihat grafik visual, silakan akses halaman web.</p>
        </div>
    @else
        <p style="text-align: center; color: #999; padding: 20px;">Data belum tersedia.</p>
    @endif
</body>
</html>

