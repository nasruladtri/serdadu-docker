<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Perbandingan Data - Fullscreen' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/kabupaten-madiun.png') }}?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-31on1Uwx1PcT6zG17Q6C7GdYr387cMGX5CujjJVOk+3O8VjMBYPWaFzx5b9mzfFh1YgUo10xXMYN9bB+FsSjVg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .compare-chart-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            width: 100%;
        }

        @media (min-width: 1024px) {
            .compare-chart-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 2rem;
            }
        }
    </style>
</head>
<body class="m-0 p-0 min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 font-sans">
    <a href="{{ route('public.compare', request()->query()) }}" class="fixed top-6 right-6 z-[1000] bg-white border-2 border-slate-300 rounded-xl px-5 py-3 text-slate-700 text-sm font-medium no-underline inline-flex items-center gap-2 transition-all duration-200 shadow-md hover:bg-primary hover:text-white hover:border-primary hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 md:top-4 md:right-4 md:px-4 md:py-2.5 md:text-xs" title="Kembali ke halaman perbandingan">
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
        $categoryLabel = $tabs[$category] ?? 'Perbandingan Data';
        
        // Build labels untuk primary dan compare
        $primaryLabel = 'Data Utama';
        if ($primaryYear && $primarySemester) {
            $primaryLabel = 'S' . $primarySemester . ' ' . $primaryYear;
            if ($primaryDistrict) {
                $districtName = \Illuminate\Support\Str::title($districts->firstWhere('id', $primaryDistrict)->name ?? '');
                $primaryLabel .= ' - ' . $districtName;
                if ($primaryVillage) {
                    $village = $primaryVillages->firstWhere('id', $primaryVillage);
                    if ($village) {
                        $villageName = \Illuminate\Support\Str::title($village->name ?? '');
                        if ($villageName) {
                            $primaryLabel .= ' - ' . $villageName;
                        }
                    }
                }
            }
        } elseif ($primaryPeriod) {
            $primaryLabel = 'S' . $primaryPeriod['semester'] . ' ' . $primaryPeriod['year'];
            if ($primaryDistrict) {
                $districtName = \Illuminate\Support\Str::title($districts->firstWhere('id', $primaryDistrict)->name ?? '');
                $primaryLabel .= ' - ' . $districtName;
                if ($primaryVillage) {
                    $village = $primaryVillages->firstWhere('id', $primaryVillage);
                    if ($village) {
                        $villageName = \Illuminate\Support\Str::title($village->name ?? '');
                        if ($villageName) {
                            $primaryLabel .= ' - ' . $villageName;
                        }
                    }
                }
            }
        }

        $compareLabel = 'Data Pembanding';
        if ($compareYear && $compareSemester) {
            $compareLabel = 'S' . $compareSemester . ' ' . $compareYear;
            if ($compareDistrict) {
                $districtName = \Illuminate\Support\Str::title($districts->firstWhere('id', $compareDistrict)->name ?? '');
                $compareLabel .= ' - ' . $districtName;
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
        } elseif ($comparePeriod) {
            $compareLabel = 'S' . $comparePeriod['semester'] . ' ' . $comparePeriod['year'];
            if ($compareDistrict) {
                $districtName = \Illuminate\Support\Str::title($districts->firstWhere('id', $compareDistrict)->name ?? '');
                $compareLabel .= ' - ' . $districtName;
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
    
    <div class="bg-white rounded-2xl shadow-2xl p-10 md:p-6 mt-4">
        <div class="border-b-2 border-slate-200 pb-6 mb-8">
            <h1 class="text-3xl md:text-2xl font-bold text-primary m-0 mb-3 leading-tight">{{ $categoryLabel }}</h1>
        </div>

        @if (!$primaryPeriod || !$comparePeriod)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <strong class="text-yellow-800">Data belum tersedia.</strong> <span class="text-yellow-700">Pilih periode dan wilayah untuk Data Utama dan Data Pembanding terlebih dahulu.</span>
            </div>
        @else
            @php
                $primaryChart = $primaryCharts[$category] ?? null;
                $compareChart = $compareCharts[$category] ?? null;
                $primaryLabelCount = isset($primaryChart['labels']) && is_array($primaryChart['labels']) ? count($primaryChart['labels']) : 0;
                $compareLabelCount = isset($compareChart['labels']) && is_array($compareChart['labels']) ? count($compareChart['labels']) : 0;
                $singleAgeLabelCount = max($primaryLabelCount, $compareLabelCount);
                $chartHeight = match ($category) {
                    'single-age' => $singleAgeLabelCount ? max(1100, $singleAgeLabelCount * 16) . 'px' : '700px',
                    'occupation' => max(900, max($singleAgeLabelCount, 1) * 22) . 'px',
                    default => '600px',
                };
            @endphp

            <div class="compare-chart-grid grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-6">
                {{-- Primary Chart (Left) --}}
                <div class="flex-1 flex flex-col min-h-0 w-full">
                    <div class="relative flex-1 flex flex-col min-h-0 w-full bg-gradient-to-br from-white via-slate-50 to-white rounded-2xl p-8 md:p-4 shadow-sm border border-slate-200">
                        <div class="mb-4 flex items-center justify-center">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-blue-100 text-blue-800">{{ $primaryLabel }}</span>
                        </div>
                        @if (!$primaryChart || empty($primaryChart['labels']))
                            <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                                <div class="w-12 h-12 mb-3 text-slate-400 opacity-50">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm text-slate-500 font-medium">Data belum tersedia</p>
                            </div>
                        @else
                            <div class="relative min-h-[600px] md:min-h-[400px] w-full flex-1" style="height: {{ $chartHeight }}; min-height: {{ $chartHeight }};">
                                <canvas id="chart-primary-{{ $category }}" data-chart-key="primary-{{ $category }}" class="w-full h-full"></canvas>
                            </div>
                            @include('public.partials.chart-axis-labels', [
                                'axis' => $axisDescriptions[$category] ?? [],
                                'flipAxes' => in_array($category, $horizontalChartKeys),
                            ])
                            <div class="flex flex-wrap gap-4 justify-center items-center mt-6 pt-4 border-t border-slate-200" id="legend-primary-{{ $category }}"></div>
                        @endif
                    </div>
                </div>

                {{-- Compare Chart (Right) --}}
                <div class="flex-1 flex flex-col min-h-0 w-full">
                    <div class="relative flex-1 flex flex-col min-h-0 w-full bg-gradient-to-br from-white via-slate-50 to-white rounded-2xl p-8 md:p-4 shadow-sm border border-slate-200">
                        <div class="mb-4 flex items-center justify-center">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-amber-100 text-amber-800">{{ $compareLabel }}</span>
                        </div>
                        @if (!$compareChart || empty($compareChart['labels']))
                            <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                                <div class="w-12 h-12 mb-3 text-slate-400 opacity-50">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm text-slate-500 font-medium">Data belum tersedia</p>
                            </div>
                        @else
                            <div class="relative min-h-[600px] md:min-h-[400px] w-full flex-1" style="height: {{ $chartHeight }}; min-height: {{ $chartHeight }};">
                                <canvas id="chart-compare-{{ $category }}" data-chart-key="compare-{{ $category }}" class="w-full h-full"></canvas>
                            </div>
                            @include('public.partials.chart-axis-labels', [
                                'axis' => $axisDescriptions[$category] ?? [],
                                'flipAxes' => in_array($category, $horizontalChartKeys),
                            ])
                            <div class="flex flex-wrap gap-4 justify-center items-center mt-6 pt-4 border-t border-slate-200" id="legend-compare-{{ $category }}"></div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if ($primaryPeriod && $comparePeriod)
            const primaryCharts = @json($primaryCharts);
            const compareCharts = @json($compareCharts);
            const chartsNeedingTags = @json($chartsNeedingTags);
            const chartsAngledTags = @json($chartsAngledTags);
            const horizontalChartKeys = @json($horizontalChartKeys);
            const chartInstances = {};
            const chartsWithValueLabels = Object.keys(primaryCharts || {});
            const totalLabelTargets = ['Total', 'Jumlah Penduduk', 'Wajib KTP'];
            const category = @json($category);
            const primaryLabel = @json($primaryLabel);
            const compareLabel = @json($compareLabel);

            // Category tag plugin
            const categoryTagPlugin = {
                id: 'categoryTagPlugin',
                afterDraw(chart, args, pluginOptions) {
                    const chartKey = chart.canvas.dataset.chartKey;
                    const key = chartKey.replace('primary-', '').replace('compare-', '');
                    if (!chartsNeedingTags.includes(key) || horizontalChartKeys.includes(key)) return;
                    
                    const labels = pluginOptions?.labels ?? chart.config.data.labels;
                    if (!labels || !labels.length) return;

                    const { ctx, chartArea, scales } = chart;
                    const xScale = scales.x;
                    if (!xScale) return;

                    const fontSize = 10;
                    ctx.save();
                    ctx.font = `${fontSize}px "Inter", "Poppins", sans-serif`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';

                    const isAngled = chartsAngledTags.includes(key);
                    const needsRotation = isAngled;

                    labels.forEach((label, index) => {
                        const x = xScale.getPixelForValue(index);
                        const text = label.length > 24 ? label.slice(0, 24) + '…' : label;

                        if (needsRotation) {
                            ctx.save();
                            const yZero = chart.scales.y ? chart.scales.y.getPixelForValue(0) : chartArea.bottom;
                            ctx.translate(x, yZero + 6);
                            ctx.rotate(-Math.PI / 2);
                            ctx.fillStyle = '#1f3f7a';
                            ctx.textAlign = 'right';
                            ctx.textBaseline = 'middle';
                            ctx.fillText(text, 0, 0);
                            ctx.restore();
                        } else {
                            const metrics = ctx.measureText(text);
                            const paddingX = 6;
                            const paddingY = 4;
                            const boxWidth = metrics.width + paddingX * 2;
                            const boxHeight = fontSize + paddingY * 2;
                            const boxX = x - boxWidth / 2;
                            const boxY = chartArea.bottom + 6;

                            ctx.fillStyle = 'rgba(55, 125, 255, 0.12)';
                            ctx.beginPath();
                            if (ctx.roundRect) {
                                ctx.roundRect(boxX, boxY, boxWidth, boxHeight, 6);
                            } else {
                                ctx.rect(boxX, boxY, boxWidth, boxHeight);
                            }
                            ctx.fill();

                            ctx.fillStyle = '#1f3f7a';
                            ctx.textAlign = 'center';
                            ctx.fillText(text, x, boxY + boxHeight / 2);
                        }
                    });

                    ctx.restore();
                }
            };

            const valueLabelPlugin = {
                id: 'valueLabelPlugin',
                afterDatasetsDraw(chart, args, pluginOptions) {
                    if (!pluginOptions?.show) {
                        return;
                    }
                    const horizontal = typeof pluginOptions.horizontal === 'boolean'
                        ? pluginOptions.horizontal
                        : chart.config?.options?.indexAxis === 'y';
                    const targetLabels = Array.isArray(pluginOptions.targetLabels) && pluginOptions.targetLabels.length
                        ? pluginOptions.targetLabels
                        : null;
                    const { ctx } = chart;
                    ctx.save();
                    ctx.font = pluginOptions.font || '10px "Inter", "Poppins", sans-serif';
                    ctx.fillStyle = pluginOptions.color || '#1f2937';
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
                            const formatted = new Intl.NumberFormat('id-ID').format(numericValue);
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

            function renderChart(chartKey, canvas, config, label) {
                if (chartInstances[chartKey]) return;

                setTimeout(() => {
                    const key = chartKey.replace('primary-', '').replace('compare-', '');
                    const ctx = canvas.getContext('2d');
                    canvas.dataset.chartKey = chartKey;
                    const labels = config.labels || [];
                    const datasets = config.datasets || [];
                    const needsTags = chartsNeedingTags.includes(key);
                    const angledTags = chartsAngledTags.includes(key);
                    const showValueLabels = chartsWithValueLabels.includes(key);
                    const longestLabel = labels.reduce((max, label) => Math.max(max, (label || '').length), 0);
                    const bottomPadding = angledTags
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
                            layout: {
                                padding: {
                                    bottom: bottomPadding
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback(value) {
                                            return new Intl.NumberFormat('id-ID').format(value);
                                        }
                                    }
                                },
                                x: {
                                    ticks: {
                                        autoSkip: false,
                                        maxRotation: 45,
                                        minRotation: 0,
                                        callback(value, index, ticks) {
                                            const label = (ticks[index] && ticks[index].label) || '';
                                            return label.length > 20 ? label.substring(0, 20) + '…' : label;
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label(context) {
                                            const label = context.dataset.label || '';
                                            const raw = context.parsed.y ?? context.parsed;
                                            return `${label}: ${new Intl.NumberFormat('id-ID').format(raw)}`;
                                        }
                                    }
                                },
                                categoryTagPlugin: {
                                    labels: labels,
                                    angled: angledTags
                                },
                                valueLabelPlugin: {
                                    show: showValueLabels,
                                    horizontal: false,
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
                            legendItem.className = 'flex items-center gap-2 text-sm text-slate-700';
                            legendItem.innerHTML = `
                                <div class="w-4 h-4 rounded flex-shrink-0" style="background-color: ${item.color || '#999'};"></div>
                                <span>${item.label}</span>
                            `;
                            legendElement.appendChild(legendItem);
                        });
                    }
                }, 50);
            }

            // Render primary chart
            const primaryCanvas = document.getElementById('chart-primary-' + category);
            if (primaryCanvas) {
                const primaryConfig = primaryCharts[category];
                if (primaryConfig && primaryConfig.labels && primaryConfig.labels.length > 0) {
                    renderChart('primary-' + category, primaryCanvas, primaryConfig, primaryLabel);
                }
            }

            // Render compare chart
            const compareCanvas = document.getElementById('chart-compare-' + category);
            if (compareCanvas) {
                const compareConfig = compareCharts[category];
                if (compareConfig && compareConfig.labels && compareConfig.labels.length > 0) {
                    renderChart('compare-' + category, compareCanvas, compareConfig, compareLabel);
                }
            }
            @endif
        });
    </script>

</body>
</html>
