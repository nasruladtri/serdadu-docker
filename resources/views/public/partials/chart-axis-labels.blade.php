@php
    $axis = (isset($axis) && is_array($axis)) ? $axis : [];
    $horizontal = $horizontal ?? ($axis['horizontal'] ?? null);
    $vertical = $vertical ?? ($axis['vertical'] ?? 'Jumlah penduduk (jiwa)');
    $flipAxes = !empty($flipAxes);

    if ($flipAxes) {
        [$horizontal, $vertical] = [$vertical, $horizontal];
    }
@endphp

@if ($horizontal || $vertical)
    <style>
        /* Ensure axis info stays visible in light mode; override for dark mode */
        .chart-axis-info {
            color: #000;
        }
        .chart-axis-info .chart-axis-label-title {
            color: #000;
        }
        .dark .chart-axis-info {
            color: #fff;
        }
        .dark .chart-axis-info .chart-axis-label-title {
            color: #fff;
        }
    </style>
    <div class="chart-axis-info text-center text-xs sm:text-sm leading-relaxed mt-3 space-y-1">
        @if ($vertical)
            <p class="m-0">
                <span class="chart-axis-label-title font-semibold">Sumbu Vertikal:</span> {{ $vertical }}
            </p>
        @endif
        @if ($horizontal)
            <p class="m-0">
                <span class="chart-axis-label-title font-semibold">Sumbu Horizontal:</span> {{ $horizontal }}
            </p>
        @endif
    </div>
@endif
