<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Grafik Data - Layar Penuh' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/kabupaten-madiun.png') }}?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-31on1Uwx1PcT6zG17Q6C7GdYr387cMGX5CujjJVOk+3O8VjMBYPWaFzx5b9mzfFh1YgUo10xXMYN9bB+FsSjVg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="m-0 p-0 min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 font-sans">
    <a href="{{ route('public.charts', request()->query()) }}" class="fixed top-6 right-6 z-[1000] bg-white border-2 border-slate-300 rounded-xl px-5 py-3 text-slate-700 text-sm font-medium no-underline inline-flex items-center gap-2 transition-all duration-200 shadow-md hover:bg-primary hover:text-white hover:border-primary hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" title="Kembali ke halaman grafik">
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
        $categoryLabel = $tabs[$category] ?? 'Grafik Data';
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
                <strong class="text-yellow-800">Data belum tersedia.</strong> <span class="text-yellow-700">Unggah dataset terlebih dahulu untuk menampilkan grafik agregat.</span>
            </div>
        @else
            @php
                $chart = $charts[$category] ?? null;
                $chartHeight = '700px';
                if ($category === 'single-age' && $chart && !empty($chart['labels']) && is_array($chart['labels'])) {
                    $chartHeight = max(1100, count($chart['labels']) * 16) . 'px';
                }
            @endphp
            @if (!$chart || empty($chart['labels']))
                <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                    <div class="w-24 h-24 mb-6 text-slate-400 opacity-50">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <p class="text-base text-slate-500 font-medium">Data {{ strtolower($categoryLabel) }} belum tersedia untuk filter yang dipilih.</p>
                </div>
            @else
                <div class="flex-1 flex flex-col min-h-0 w-full">
                    <div class="relative flex-1 flex flex-col min-h-0 w-full bg-gradient-to-br from-white via-slate-50 to-white rounded-2xl p-8 md:p-4 shadow-sm border border-slate-200">
                        <div class="relative min-h-[600px] md:min-h-[400px] w-full flex-1" style="height: {{ $chartHeight }}; min-height: {{ $chartHeight }};">
                            <canvas id="chart-{{ $category }}" data-chart-key="{{ $category }}" class="w-full h-full"></canvas>
                        </div>
                        @include('public.partials.chart-axis-labels', [
                            'axis' => $axisDescriptions[$category] ?? [],
                            'flipAxes' => in_array($category, $horizontalChartKeys),
                        ])
                        <div class="flex flex-wrap gap-4 justify-center items-center mt-6 pt-4 border-t border-slate-200" id="legend-{{ $category }}"></div>
                    </div>
                </div>
            @endif
        @endif
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartConfigs = @json($charts);
            const chartsNeedingTags = @json($chartsNeedingTags);
            const chartsAngledTags = @json($chartsAngledTags);
            const horizontalChartKeys = @json($horizontalChartKeys);
            const chartInstances = {};
            const category = @json($category);
            const getCssColor = (name, fallback) => {
                const value = getComputedStyle(document.documentElement).getPropertyValue(name);
                return value ? value.trim() || fallback : fallback;
            };
            const getChartColors = () => ({
                axis: getCssColor('--color-chart-axis', '#4b5563'),
                grid: getCssColor('--color-chart-grid', 'rgba(148, 163, 184, 0.35)'),
                surface: getCssColor('--color-surface', '#0f172a'),
                text: getCssColor('--color-text', '#0f172a'),
            });

            // Plugin kustom Chart.js untuk menampilkan label kategori
            const categoryTagPlugin = {
                id: 'categoryTagPlugin',
                afterDraw(chart, args, pluginOptions) {
                    const key = chart.canvas.dataset.chartKey;
                    if (!chartsNeedingTags.includes(key) || horizontalChartKeys.includes(key)) {
                        return;
                    }
                    const labels = pluginOptions?.labels ?? chart.config.data.labels;
                    const tagTextColor = getCssColor('--color-text', '#1f3f7a');
                    if (!labels || !labels.length) {
                        return;
                    }

                    const { ctx, chartArea, scales } = chart;
                    const xScale = scales.x;
                    if (!xScale) {
                        return;
                    }

                    const fontSize = 10;
                    ctx.save();
                    ctx.font = `${fontSize}px "Inter", "Poppins", sans-serif`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';

                    const pluginAngled = !!pluginOptions?.angled;
                    const isAngled = pluginAngled || chartsAngledTags.includes(key);
                    const isFullVertical = chartsAngledTags.includes(key);
                    const needsRotation = isFullVertical;

                    labels.forEach((label, index) => {
                        const x = xScale.getPixelForValue(index);
                        const text = label.length > 24 ? label.slice(0, 24) + '…' : label;

                        if (needsRotation) {
                            ctx.save();
                            const yZero = chart.scales.y ? chart.scales.y.getPixelForValue(0) : chartArea.bottom;
                            ctx.translate(x, yZero + 6);
                            ctx.rotate(-Math.PI / 2);
                            ctx.fillStyle = tagTextColor;
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

                            ctx.fillStyle = tagTextColor;
                            ctx.textAlign = 'center';
                            ctx.fillText(text, x, boxY + boxHeight / 2);
                        }
                    });

                    ctx.restore();
                }
            };

            Chart.register(categoryTagPlugin);

            // Fungsi untuk memastikan grafik benar-benar terpasang
            const ensureChart = (key) => {
                if (chartInstances[key]) {
                    setTimeout(() => {
                        chartInstances[key].resize();
                    }, 100);
                    return chartInstances[key];
                }

                const config = chartConfigs[key];
                if (!config || !Array.isArray(config.labels) || !config.labels.length || !Array.isArray(config.datasets) || !config.datasets.length) {
                    return null;
                }

                const canvas = document.getElementById(`chart-${key}`);
                if (!canvas) {
                    return null;
                }

                setTimeout(() => {
                    const themeColors = getChartColors();
                    Chart.defaults.color = themeColors.axis;
                    Chart.defaults.borderColor = themeColors.grid;

                    const ctx = canvas.getContext('2d');
                    canvas.dataset.chartKey = key;
                    const needsTags = chartsNeedingTags.includes(key);
                    const angledTags = chartsAngledTags.includes(key);
                    const longestLabel = config.labels.reduce((max, label) => Math.max(max, (label || '').length), 0);
                    const bottomPadding = angledTags
                        ? Math.min(260, Math.max(160, longestLabel * 6 + 32))
                        : (needsTags ? 70 : 16);

                    chartInstances[key] = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: config.labels,
                            datasets: config.datasets
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
                                    grid: { color: themeColors.grid, drawBorder: false },
                                    ticks: {
                                        callback(value) {
                                            return new Intl.NumberFormat('id-ID').format(value);
                                        },
                                        color: themeColors.axis
                                    }
                                },
                                x: {
                                    grid: { color: themeColors.grid, drawBorder: false },
                                    ticks: {
                                        autoSkip: false,
                                        maxRotation: 45,
                                        minRotation: 0,
                                        callback(value, index, ticks) {
                                            const label = (ticks[index] && ticks[index].label) || '';
                                            return label.length > 20 ? label.substring(0, 20) + '…' : label;
                                        },
                                        color: themeColors.axis
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
                                            const raw = context.parsed.y ?? context.parsed;
                                            return `${label}: ${new Intl.NumberFormat('id-ID').format(raw)}`;
                                        }
                                    }
                                },
                                categoryTagPlugin: {
                                    labels: config.labels,
                                    angled: angledTags
                                }
                            }
                        }
                    });
                    
                    // Bangun legenda di bawah grafik
                    const legendElement = document.getElementById('legend-' + key);
                    if (legendElement) {
                        legendElement.innerHTML = '';
                        const legendItems = Array.isArray(config.legendItems) && config.legendItems.length
                            ? config.legendItems
                            : (config.datasets || []).map((dataset) => ({
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
            };

            const refreshChartsForTheme = () => {
                Object.keys(chartInstances).forEach((key) => {
                    if (chartInstances[key]) {
                        chartInstances[key].destroy();
                        chartInstances[key] = null;
                    }
                });
                setTimeout(() => ensureChart(category), 120);
            };

            document.addEventListener('theme-changed', refreshChartsForTheme);

            // Inisialisasi grafik untuk kategori yang dipilih
            setTimeout(function() {
                ensureChart(category);
            }, 300);
        });
    </script>

</body>
</html>

