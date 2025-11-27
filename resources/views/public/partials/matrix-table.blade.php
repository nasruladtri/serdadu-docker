@php
    $matrix = $matrix ?? [];
    $columns = $matrix['columns'] ?? [];
    $rows = $matrix['rows'] ?? [];
    $columnLabel = $matrix['columnLabel'] ?? 'Wilayah';
    $highlightId = $matrix['highlightAreaId'] ?? null;
    $totals = $matrix['totals'] ?? [];
    // allow explicit flag from caller or from matrix payload
    $showOverallSum = $matrix['showOverallSum'] ?? ($showOverallSum ?? false);
    $colspan = 2 + (count($columns) * 3) + ($showOverallSum ? 1 : 0);
@endphp

<table class="w-full text-sm dk-table mb-0">
    <thead>
        <tr>
            <th rowspan="2" style="width: 64px">No</th>
            <th rowspan="2">{{ $columnLabel }}</th>
            @foreach ($columns as $column)
                <th class="text-center" colspan="3">{{ $column['label'] }}</th>
            @endforeach
            @if ($showOverallSum)
                <th rowspan="2" class="text-right">Jumlah</th>
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
            @php
                $values = $row['values'] ?? [];
                $highlight = $row['highlight'] ?? ($highlightId !== null && (int) ($row['area_id'] ?? 0) === (int) $highlightId);
                $rowSum = 0;
            @endphp
            <tr class="{{ $highlight ? 'bg-gray-100' : '' }}">
                <td>{{ $index + 1 }}</td>
                <td>{{ \Illuminate\Support\Str::title($row['name']) }}</td>
                @foreach ($columns as $column)
                    @php
                        $key = $column['key'];
                        $value = $values[$key] ?? ['male' => 0, 'female' => 0, 'total' => 0];
                        $rowSum += (int) ($value['total'] ?? 0);
                    @endphp
                    <td class="text-right">{{ number_format($value['male']) }}</td>
                    <td class="text-right">{{ number_format($value['female']) }}</td>
                    <td class="text-right font-semibold">{{ number_format($value['total']) }}</td>
                @endforeach
                @if ($showOverallSum)
                    <td class="text-right font-semibold">{{ number_format($rowSum) }}</td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ max(2, $colspan) }}" class="text-center text-gray-500">
                    {{ $emptyMessage ?? 'Data belum tersedia.' }}
                </td>
            </tr>
        @endforelse
    </tbody>
    @if (!empty($rows))
        <tfoot>
            <tr>
                <th colspan="2">Jumlah Keseluruhan</th>
                @php $overallTotal = 0; @endphp
                @foreach ($columns as $column)
                    @php
                        $key = $column['key'];
                        $total = $totals[$key] ?? ['male' => 0, 'female' => 0, 'total' => 0];
                        $overallTotal += (int) ($total['total'] ?? 0);
                    @endphp
                    <th class="text-right">{{ number_format($total['male']) }}</th>
                    <th class="text-right">{{ number_format($total['female']) }}</th>
                    <th class="text-right">{{ number_format($total['total']) }}</th>
                @endforeach
                @if ($showOverallSum)
                    <th class="text-right font-semibold">{{ number_format($overallTotal) }}</th>
                @endif
            </tr>
        </tfoot>
    @endif
</table>
