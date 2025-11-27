@extends('layouts.admin', ['title' => 'Beranda'])

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
          crossorigin=""/>
    <style>
        #landing-map { height: 100%; min-height: 600px; width: 100%; z-index: 1; }
        
        /* Mobile responsive map */
        @media (max-width: 768px) {
            #landing-map { min-height: 400px; }
        }
        
        @media (max-width: 480px) {
            #landing-map { min-height: 350px; }
        }
        .leaflet-popup-content-wrapper { border-radius: 8px; }
        .dark .leaflet-popup-content-wrapper {
            background: var(--color-surface);
            color: var(--color-text);
            border: 1px solid var(--color-border);
        }
        .dark .leaflet-popup-tip {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            box-shadow: none;
        }
        .dark .leaflet-popup-content {
            color: var(--color-text);
        }
        .dark .leaflet-popup-close-button {
            color: var(--color-text);
        }
        .metric-card {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            border-radius: 12px;
            padding: 1.5rem;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        /* Mobile responsive padding */
        @media (max-width: 640px) {
            .metric-card {
                padding: 1rem;
                border-radius: 10px;
            }
        }
        .metric-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }
        .metric-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        .metric-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
            filter: brightness(1.1) saturate(1.1);
        }
        .metric-card:active {
            transform: translateY(-4px) scale(1.01);
        }
        
        /* Hover effects untuk panel */
        .panel-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .panel-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }
        
        /* Hover effects untuk wilayah info */
        .wilayah-info-item {
            transition: all 0.2s ease;
        }
        .wilayah-info-item:hover {
            background-color: rgba(0, 155, 77, 0.05);
            transform: translateX(4px);
        }
        
        /* Disable hover effects on mobile */
        @media (max-width: 768px) {
            .metric-card:hover {
                transform: none;
                box-shadow: none;
                filter: none;
            }
            .panel-hover:hover {
                transform: none;
                box-shadow: none;
            }
            .wilayah-info-item:hover {
                background-color: transparent;
                transform: none;
            }
        }
        .metric-card-primary { --gradient-start: #009B4D; --gradient-end: #007a3d; }
        .metric-card-male { --gradient-start: #4f7df3; --gradient-end: #2b5fcf; }
        .metric-card-female { --gradient-start: #e85b9d; --gradient-end: #c3387c; }
        .dark .metric-card-primary { --gradient-start: #00b261; --gradient-end: #008b45; }
        .dark .metric-card-male { --gradient-start: #60a5fa; --gradient-end: #3b82f6; }
        .dark .metric-card-female { --gradient-start: #f472b6; --gradient-end: #db2777; }
        .progress-bar {
            height: 4px;
            background: rgba(255,255,255,0.3);
            border-radius: 2px;
            overflow: hidden;
            margin-top: 0.75rem;
        }
        .progress-fill {
            height: 100%;
            background: white;
            border-radius: 2px;
            transition: width 0.6s ease;
        }
        
        /* Animation Keyframes */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* Animation Classes */
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }
        
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
            opacity: 0;
        }
        
        .animate-slide-in-left {
            animation: slideInLeft 0.6s ease-out forwards;
            opacity: 0;
        }
        
        .animate-slide-in-right {
            animation: slideInRight 0.6s ease-out forwards;
            opacity: 0;
        }
        
        .animate-scale-in {
            animation: scaleIn 0.6s ease-out forwards;
            opacity: 0;
        }
        
        /* Stagger Delays */
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-500 { animation-delay: 0.5s; }
        .delay-600 { animation-delay: 0.6s; }
        .delay-700 { animation-delay: 0.7s; }
        .delay-800 { animation-delay: 0.8s; }
        
        /* Mobile optimizations */
        @media (max-width: 640px) {
            /* Reduce animation delays on mobile */
            .delay-100, .delay-200, .delay-300, .delay-400, .delay-500, .delay-600, .delay-700, .delay-800 {
                animation-delay: 0s;
            }
            
            /* Faster animations on mobile */
            .animate-fade-in-up,
            .animate-fade-in,
            .animate-slide-in-left,
            .animate-slide-in-right,
            .animate-scale-in {
                animation-duration: 0.4s;
            }
        }
    </style>
@endpush

@section('content')
    <div class="space-y-4 sm:space-y-6">
        @if (!$period)
            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-lg shadow-sm">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-amber-800 mb-1">Data belum tersedia</h3>
                        <p class="text-sm text-amber-700">Silakan unggah dataset terlebih dahulu melalui halaman admin.</p>
                    </div>
                </div>
            </div>
        @else
            @php
                $totalPop = $totals['population'] ?? 0;
                $malePop = $totals['male'] ?? 0;
                $femalePop = $totals['female'] ?? 0;
                $wajibKtpTotal = $wajibKtp['total'] ?? 0;
                $malePercent = $totalPop > 0 ? ($malePop / $totalPop) * 100 : 0;
                $femalePercent = $totalPop > 0 ? ($femalePop / $totalPop) * 100 : 0;
                $wajibKtpPercent = $totalPop > 0 ? ($wajibKtpTotal / $totalPop) * 100 : 0;
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
                <!-- Statistics Panel -->
                <div class="lg:col-span-5 xl:col-span-4 flex flex-col">
                    <!-- Overview Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 flex-1 animate-slide-in-left panel-hover">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 sm:gap-3 mb-4 sm:mb-6">
                            <h2 class="text-base sm:text-lg font-semibold text-gray-900">Data Agregat Kependudukan Terbaru</h2>
                            @if($period)
                                <div class="flex-shrink-0 px-2 py-0.5 sm:px-2.5 sm:py-1 lg:px-3 lg:py-1 bg-[#009B4D] text-white text-[10px] sm:text-[11px] lg:text-xs font-medium rounded-full whitespace-nowrap self-start sm:self-auto">
                                    Semester {{ $period['semester'] }} Tahun {{ $period['year'] }}
                                </div>
                            @endif
                        </div>
                        
                        <!-- Wilayah Info -->
                        <div class="mb-4 sm:mb-6">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Wilayah</h3>
                            <div class="bg-gray-50 rounded-lg p-3 sm:p-4 space-y-2">
                                <div class="flex justify-between items-center wilayah-info-item rounded-md px-2 py-1 -mx-2">
                                    <span class="text-xs sm:text-sm font-medium text-gray-700">Nama Wilayah</span>
                                    <span class="text-xs sm:text-sm text-gray-900 font-semibold">Madiun</span>
                                </div>
                                <div class="flex justify-between items-center wilayah-info-item rounded-md px-2 py-1 -mx-2">
                                    <span class="text-xs sm:text-sm font-medium text-gray-700">Jumlah Kecamatan</span>
                                    <span class="text-xs sm:text-sm text-gray-900 font-semibold">{{ number_format($districtCount) }}</span>
                                </div>
                                <div class="flex justify-between items-center wilayah-info-item rounded-md px-2 py-1 -mx-2">
                                    <span class="text-xs sm:text-sm font-medium text-gray-700">Jumlah Desa/Kelurahan</span>
                                    <span class="text-xs sm:text-sm text-gray-900 font-semibold">{{ number_format($villageCount) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Metrics Grid -->
                        <div>
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Jumlah Penduduk</h3>
                            <div class="space-y-3 sm:space-y-4">
                                <!-- Total Penduduk -->
                                <div class="animate-fade-in-up delay-200">
                                    <div class="metric-card metric-card-primary">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <img src="{{ asset('img/penduduk.png') }}" alt="Total Penduduk" class="w-6 h-6 sm:w-8 sm:h-8">
                                                <span class="text-xs sm:text-sm font-medium text-white/90">Total Penduduk</span>
                                            </div>
                                        </div>
                                        <div class="text-xl sm:text-2xl font-bold mb-1">{{ number_format($totalPop) }}</div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: 100%"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Laki-laki dan Perempuan - Side by Side -->
                                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                                    <!-- Laki-laki -->
                                    <div class="animate-fade-in-up delay-300">
                                        <div class="metric-card metric-card-male h-full">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center gap-2">
                                                    <img src="{{ asset('img/l.png') }}" alt="Laki-laki" class="w-6 h-6 flex-shrink-0 object-contain brightness-0 invert">
                                                    <span class="text-xs font-medium text-white/90">Laki-laki</span>
                                                </div>
                                            </div>
                                            <div class="text-xl font-bold mb-1">{{ number_format($malePop) }}</div>
                                            <div class="text-xs text-white/80 mb-2">{{ number_format($malePercent, 1) }}%</div>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: {{ $malePercent }}%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Perempuan -->
                                    <div class="animate-fade-in-up delay-400">
                                        <div class="metric-card metric-card-female h-full">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center gap-2">
                                                    <img src="{{ asset('img/p.png') }}" alt="Perempuan" class="w-6 h-6 flex-shrink-0 object-contain brightness-0 invert">
                                                    <span class="text-xs font-medium text-white/90">Perempuan</span>
                                                </div>
                                            </div>
                                            <div class="text-xl font-bold mb-1">{{ number_format($femalePop) }}</div>
                                            <div class="text-xs text-white/80 mb-2">{{ number_format($femalePercent, 1) }}%</div>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: {{ $femalePercent }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Wajib KTP -->
                                <div class="animate-fade-in-up delay-500">
                                    <div class="metric-card metric-card-primary">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <img src="{{ asset('img/ktp.png') }}" alt="Wajib KTP" class="w-6 h-6">
                                                <span class="text-xs font-medium text-white/90">Wajib KTP (≥ 17 tahun)</span>
                                            </div>
                                        </div>
                                        <div class="text-xl font-bold mb-1">{{ number_format($wajibKtpTotal) }}</div>
                                        <div class="text-xs text-white/80 mb-2">{{ number_format($wajibKtpPercent, 1) }}%</div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: {{ $wajibKtpPercent }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Panel -->
                <div class="lg:col-span-7 xl:col-span-8 flex flex-col">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex-1 flex flex-col animate-slide-in-right delay-100 panel-hover">
                        <div class="p-4 sm:p-6 border-b border-gray-200">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <h2 class="text-base sm:text-lg font-semibold text-gray-900">Peta Persebaran Penduduk Kabupaten Madiun</h2>
                                @if (!empty($districtOptions) && $districtOptions->count())
                                    <div class="lg:w-auto w-full">
                                        <label for="landing-district-filter" class="block text-xs font-medium text-gray-700 mb-1.5">Kecamatan</label>
                                        <select 
                                            id="landing-district-filter" 
                                            class="w-full lg:w-64 text-sm border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#009B4D] focus:border-transparent transition-colors"
                                        >
                                            <option value="">Semua Kecamatan</option>
                                            @foreach($districtOptions as $district)
                                                <option value="{{ $district->code }}" data-slug="{{ \Illuminate\Support\Str::slug($district->name) }}">
                                                    {{ \Illuminate\Support\Str::title($district->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="relative flex-1">
                            <div id="landing-map-loader" class="absolute inset-0 bg-white/90 backdrop-blur-sm z-10 flex items-center justify-center">
                                <div class="text-center">
                                    <div class="inline-block w-8 h-8 border-4 border-[#009B4D] border-t-transparent rounded-full animate-spin mb-3"></div>
                                    <div class="text-sm text-gray-600 font-medium">Memuat peta...</div>
                                </div>
                            </div>
                            <div id="landing-map" class="w-full h-full"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Laju Pertumbuhan Penduduk Section -->
            @if (!empty($populationGrowth['labels']) && count($populationGrowth['labels']) > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 animate-fade-in-up delay-600 panel-hover">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 sm:mb-6">Laju Pertumbuhan Penduduk</h2>
                    <div class="chart-container" style="height: 300px; position: relative;">
                        <canvas id="population-growth-chart"></canvas>
                    </div>
                    
                    <style>
                        @media (min-width: 640px) {
                            .chart-container {
                                height: 400px !important;
                            }
                        }
                    </style>
                </div>
            @endif
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
            crossorigin=""></script>
    <script src="{{ asset('map/Peta Madiun/kab.js') }}"></script>
    <script src="{{ asset('map/Peta Madiun/kec.js') }}"></script>
    <script src="{{ asset('map/Peta Madiun/kel.js') }}"></script>
    @if (!empty($populationGrowth['labels']) && count($populationGrowth['labels']) > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endif

    @php
        $mapStats = $mapStats ?? [
            'districts' => ['by_code' => [], 'by_slug' => []],
            'villages' => ['by_code' => [], 'by_slug' => []],
        ];
        $districtsForMap = $districtsForMap ?? [];
    @endphp

    <script>
        (function () {
            const mapStats = @json($mapStats);
            const districtsData = @json($districtsForMap);

            function ensureStats(section) {
                section = section || {};
                return {
                    by_code: section.by_code || {},
                    by_slug: section.by_slug || {},
                };
            }

            const districtStatsIndex = ensureStats(mapStats.districts);
            const villageStatsIndex = ensureStats(mapStats.villages);

            // Tile layers
            const carto = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                attribution: '&copy; Serdadu | &copy; OpenStreetMap contributors &copy; CARTO',
            });
            const cartoDark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                attribution: '&copy; Serdadu | &copy; OpenStreetMap contributors &copy; CARTO',
            });
            const cartoVoyager = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                attribution: '&copy; Serdadu | &copy; OpenStreetMap contributors &copy; CARTO',
            });
            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; Serdadu | &copy; OpenStreetMap contributors',
            });
            const googleSat = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Serdadu | Imagery &copy; Google'
            });

            // Initialize map
            const map = L.map('landing-map', {
                center: [-7.629, 111.515],
                zoom: 11,
                layers: [cartoVoyager],
            });
            const TARGET_VIEW_WIDTH_KM = 15;

            map.whenReady(function() {
                const loader = document.getElementById('landing-map-loader');
                if (loader) {
                    loader.style.opacity = '0';
                    loader.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => {
                        loader.style.display = 'none';
                    }, 300);
                }
            });

            // Create panes
            map.createPane('kelPane');
            map.getPane('kelPane').style.zIndex = 470;
            map.createPane('kecPane');
            map.getPane('kecPane').style.zIndex = 460;
            map.createPane('kabPane');
            map.getPane('kabPane').style.zIndex = 480;
            map.getPane('kabPane').style.pointerEvents = 'none';
            map.createPane('hoverPane');
            map.getPane('hoverPane').style.zIndex = 600;
            map.getPane('hoverPane').style.pointerEvents = 'none';
            map.createPane('labelPane');
            map.getPane('labelPane').style.zIndex = 650;
            map.getPane('labelPane').style.pointerEvents = 'none';

            const districtLabelLayer = L.layerGroup().addTo(map);
            districtLabelLayer.setZIndex(650);

            L.control.scale({ imperial: false, maxWidth: 160 }).addTo(map);

            function styleKab() {
                return { color: '#c0392b', weight: 2, fillOpacity: 0, fill: false };
            }

            function styleKec() {
                return { color: '#63d199', weight: 1.7, fillColor: '#63d199', fillOpacity: 0 };
            }

            function styleKel() {
                return { color: '#00b4d8', weight: 1.3, fillColor: '#48cae4', fillOpacity: 0 };
            }

            function formatNumber(value) {
                const num = Number(value);
                return Number.isFinite(num) ? num.toLocaleString('id-ID') : '-';
            }

            function escapeHtml(value) {
                if (value === null || value === undefined) return '';
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function buildPopupContent(title, rows) {
                let html = '<div class="p-2">';
                if (title) {
                    html += '<strong class="block mb-2 text-gray-900">' + escapeHtml(title) + '</strong>';
                }
                if (rows && rows.length) {
                    html += '<table class="min-w-full text-sm">';
                    rows.forEach(row => {
                        html += '<tr class="border-b border-gray-200">';
                        html += '<td class="py-1 pr-4 font-medium text-gray-700">' + escapeHtml(row.label) + '</td>';
                        html += '<td class="py-1 text-gray-900">' + escapeHtml(row.value) + '</td>';
                        html += '</tr>';
                    });
                    html += '</table>';
                }
                html += '</div>';
                return html;
            }

            function normalizeName(value) {
                if (value === null || value === undefined) return null;
                let text = String(value).toLowerCase();
                if (text.normalize) {
                    text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                }
                text = text.replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
                return text || null;
            }

            function slugVariants(slug) {
                if (!slug) return [];
                const variants = [slug];
                const noDash = slug.replace(/-/g, '');
                if (noDash && variants.indexOf(noDash) === -1) {
                    variants.push(noDash);
                }
                return variants;
            }

            function codeAliases(value) {
                const digits = value === undefined || value === null ? '' : String(value).replace(/\D+/g, '');
                if (!digits) return [];
                const aliases = [digits];
                if (digits.length >= 3) {
                    aliases.push(digits.slice(-3).padStart(3, '0'));
                }
                if (digits.length >= 4) {
                    aliases.push(digits.slice(-4).padStart(4, '0'));
                }
                if (digits.length >= 5) {
                    aliases.push(digits.slice(-5).padStart(5, '0'));
                }
                return Array.from(new Set(aliases));
            }

            let kabLayer = window.kab ? L.geoJSON(window.kab, { style: styleKab, pane: 'kabPane' }).addTo(map) : L.layerGroup().addTo(map);
            let kecLayer = null;
            let kelLayer = null;
            let hoverHighlightLayer = null;
            const villageLabelLayer = L.layerGroup().addTo(map);

            function ensureLayerOrder() {
                if (kelLayer && kelLayer.bringToFront) kelLayer.bringToFront();
                if (kecLayer && kecLayer.bringToFront) kecLayer.bringToFront();
                if (kabLayer && kabLayer.bringToFront) kabLayer.bringToFront();
            }

            function removeLayer(layer) {
                if (layer && map.hasLayer(layer)) {
                    map.removeLayer(layer);
                }
            }

            function computeRingCentroid(ring) {
                if (!Array.isArray(ring) || ring.length < 3) return null;
                let twiceArea = 0;
                let x = 0;
                let y = 0;
                for (let i = 0; i < ring.length - 1; i++) {
                    const p1 = ring[i];
                    const p2 = ring[i + 1];
                    if (!p1 || !p2) continue;
                    const f = (p1[0] * p2[1]) - (p2[0] * p1[1]);
                    twiceArea += f;
                    x += (p1[0] + p2[0]) * f;
                    y += (p1[1] + p2[1]) * f;
                }
                if (!twiceArea) return null;
                const areaFactor = twiceArea * 3;
                return [x / areaFactor, y / areaFactor];
            }

            function computeFeatureCenter(feature) {
                if (!feature || !feature.geometry) return null;
                const geom = feature.geometry;
                const type = geom.type;
                const coords = geom.coordinates;
                if (!coords) return null;

                let result = null;
                let bestArea = 0;

                function accumulateCentroid(rings) {
                    if (!Array.isArray(rings) || !rings.length) return;
                    const outerRing = rings[0];
                    if (!Array.isArray(outerRing) || outerRing.length < 4) return;
                    const centroid = computeRingCentroid(outerRing);
                    if (!centroid) return;
                    let twiceArea = 0;
                    for (let i = 0; i < outerRing.length - 1; i++) {
                        const p1 = outerRing[i];
                        const p2 = outerRing[i + 1];
                        twiceArea += (p1[0] * p2[1]) - (p2[0] * p1[1]);
                    }
                    const area = Math.abs(twiceArea / 2);
                    if (!area) return;
                    if (area > bestArea) {
                        bestArea = area;
                        result = centroid;
                    }
                }

                if (type === 'Polygon') {
                    accumulateCentroid(coords);
                } else if (type === 'MultiPolygon') {
                    for (let i = 0; i < coords.length; i++) {
                        accumulateCentroid(coords[i]);
                    }
                }

                if (result && Number.isFinite(result[0]) && Number.isFinite(result[1])) {
                    return L.latLng(result[1], result[0]);
                }

                try {
                    const tempLayer = L.geoJSON(feature);
                    const bounds = tempLayer.getBounds();
                    if (bounds && bounds.isValid()) {
                        return bounds.getCenter();
                    }
                } catch (err) {
                    // ignore
                }
                return null;
            }

            function clearHoverHighlight() {
                if (hoverHighlightLayer && map.hasLayer(hoverHighlightLayer)) {
                    map.removeLayer(hoverHighlightLayer);
                }
                hoverHighlightLayer = null;
            }

            function highlightFeature(layer, color, weight, fillOpacity) {
                if (!layer) return;
                clearHoverHighlight();
                if (layer.bringToFront) layer.bringToFront();
                if (typeof layer.toGeoJSON === 'function') {
                    const geoJson = layer.toGeoJSON();
                    const strokeWeight = typeof weight === 'number' ? weight : 2;
                    const fillAlpha = typeof fillOpacity === 'number' ? fillOpacity : 0;
                    hoverHighlightLayer = L.geoJSON(geoJson, {
                        style: () => ({
                            color: color,
                            weight: strokeWeight,
                            opacity: 1,
                            fillColor: color,
                            fillOpacity: fillAlpha
                        }),
                        pane: 'hoverPane',
                        interactive: false
                    }).addTo(map);
                }
                ensureLayerOrder();
            }

            function resetFeatureStyle(layer, styleFn) {
                clearHoverHighlight();
                if (!layer || !layer.setStyle || typeof styleFn !== 'function') return;
                const baseStyle = styleFn(layer.feature);
                layer.setStyle(baseStyle);
                ensureLayerOrder();
            }

            function findDistrictStats(props) {
                const aliases = codeAliases(props && props.kd_kecamatan);
                for (let i = 0; i < aliases.length; i++) {
                    if (districtStatsIndex.by_code[aliases[i]]) {
                        return districtStatsIndex.by_code[aliases[i]];
                    }
                }
                const slug = normalizeName(props && props.nm_kecamatan);
                const variants = slugVariants(slug);
                for (let j = 0; j < variants.length; j++) {
                    if (districtStatsIndex.by_slug[variants[j]]) {
                        return districtStatsIndex.by_slug[variants[j]];
                    }
                }
                return null;
            }

            function findVillageStats(props, districtState) {
                let stats = null;
                const districtAliases = districtState && Array.isArray(districtState.codeAliases) && districtState.codeAliases.length
                    ? districtState.codeAliases
                    : codeAliases(props && props.kd_kecamatan);
                const villageAliases = codeAliases(props && props.kd_kelurahan);

                districtAliases.some(dAlias => {
                    return villageAliases.some(vAlias => {
                        const key = dAlias + '-' + vAlias;
                        if (villageStatsIndex.by_code[key]) {
                            stats = villageStatsIndex.by_code[key];
                            return true;
                        }
                        return false;
                    });
                });

                if (!stats) {
                    const districtVariants = districtState && Array.isArray(districtState.slugVariants) && districtState.slugVariants.length
                        ? districtState.slugVariants
                        : slugVariants(normalizeName(props && props.nm_kecamatan));
                    const villageVariants = slugVariants(normalizeName(props && props.nm_kelurahan));
                    districtVariants.some(dSlug => {
                        return villageVariants.some(vSlug => {
                            const key = dSlug + '-' + vSlug;
                            if (villageStatsIndex.by_slug[key]) {
                                stats = villageStatsIndex.by_slug[key];
                                return true;
                            }
                            return false;
                        });
                    });
                }
                return stats;
            }

            function bindDistrictFeature(feature, layer) {
                const props = feature && feature.properties ? feature.properties : {};
                const stats = findDistrictStats(props);
                const name = stats && stats.name ? stats.name : (props.nm_kecamatan || 'Kecamatan');
                const rows = [];

                if (stats) {
                    rows.push({ label: 'L (Laki-laki)', value: formatNumber(stats.male) });
                    rows.push({ label: 'P (Perempuan)', value: formatNumber(stats.female) });
                    rows.push({ label: 'Total Penduduk', value: formatNumber(stats.total) });
                } else {
                    rows.push({ label: 'Informasi', value: 'Data penduduk belum tersedia' });
                }

                if (!layer._hoverColor) {
                    layer._hoverColor = '#00b4d8';
                }

                layer.on({
                    mouseover: (e) => highlightFeature(e.target, e.target._hoverColor || '#00b4d8', 2, 0.18),
                    mouseout: (e) => resetFeatureStyle(e.target, styleKec),
                    popupopen: (e) => highlightFeature(e.target, e.target._hoverColor || '#00b4d8', 2.2, 0.2),
                    popupclose: (e) => resetFeatureStyle(e.target, styleKec)
                });

                layer.bindPopup(buildPopupContent('Kecamatan ' + name, rows));
            }

            function bindVillageFeatureFactory(districtState) {
                return function(feature, layer) {
                    const props = feature && feature.properties ? feature.properties : {};
                    const stats = districtState ? findVillageStats(props, districtState) : null;
                    const districtFallback = findDistrictStats(props);

                    const districtName = (districtState && districtState.name) ||
                        (stats && stats.district_name) ||
                        (districtFallback && districtFallback.name) ||
                        props.nm_kecamatan || '-';
                    const villageName = (stats && stats.name) || props.nm_kelurahan || 'Desa/Kelurahan';

                    const rows = [
                        { label: 'Kecamatan', value: districtName }
                    ];

                    if (stats) {
                        rows.push({ label: 'L (Laki-laki)', value: formatNumber(stats.male) });
                        rows.push({ label: 'P (Perempuan)', value: formatNumber(stats.female) });
                        rows.push({ label: 'Total Penduduk', value: formatNumber(stats.total) });
                    } else {
                        rows.push({ label: 'Informasi', value: 'Data penduduk belum tersedia' });
                    }

                    if (!layer._hoverColor) {
                        layer._hoverColor = '#00b4d8';
                    }

                    layer.on({
                        mouseover: (e) => highlightFeature(e.target, e.target._hoverColor || '#00b4d8', 1.4, 0.2),
                        mouseout: (e) => resetFeatureStyle(e.target, styleKel),
                        popupopen: (e) => highlightFeature(e.target, e.target._hoverColor || '#00b4d8', 1.6, 0.24),
                        popupclose: (e) => resetFeatureStyle(e.target, styleKel)
                    });

                    layer.bindPopup(buildPopupContent('Desa/Kelurahan ' + villageName, rows));
                };
            }

            function buildDistrictFilter(selectedCode, selectedSlug) {
                const codeVal = selectedCode ? String(selectedCode).trim() : '';
                const slugVal = selectedSlug ? normalizeName(selectedSlug) : '';
                if (!codeVal && !slugVal) {
                    return () => true;
                }
                const selectedAliases = codeAliases(codeVal);
                const slugOptions = slugVariants(slugVal);
                return (feature) => {
                    const props = feature && feature.properties ? feature.properties : {};
                    const featureAliases = codeAliases(props.kd_kecamatan);
                    for (let i = 0; i < selectedAliases.length; i++) {
                        if (featureAliases.indexOf(selectedAliases[i]) !== -1) {
                            return true;
                        }
                    }
                    if (slugOptions.length) {
                        const featureSlug = normalizeName(props.nm_kecamatan);
                        const featureVariants = slugVariants(featureSlug);
                        for (let j = 0; j < slugOptions.length; j++) {
                            if (featureVariants.indexOf(slugOptions[j]) !== -1) {
                                return true;
                            }
                        }
                    }
                    return false;
                };
            }

            function createDistrictLabel(feature, districtName) {
                const center = computeFeatureCenter(feature);
                if (!center) return null;

                const labelDiv = document.createElement('div');
                labelDiv.textContent = districtName;
                labelDiv.style.cssText = `
                    background: rgba(255, 255, 255, 0.9);
                    color: #004934;
                    padding: 2px 7px;
                    border-radius: 3px;
                    font-size: 9px;
                    font-weight: 600;
                    white-space: nowrap;
                    pointer-events: none;
                    border: 0.7px solid rgba(0, 113, 81, 0.35);
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.18);
                    text-align: center;
                    user-select: none;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                    letter-spacing: 0.02em;
                    text-transform: uppercase;
                    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
                `;

                const labelIcon = L.divIcon({
                    html: labelDiv,
                    className: 'district-label-icon',
                    iconSize: null,
                    iconAnchor: [0, 0]
                });

                return L.marker(center, {
                    icon: labelIcon,
                    pane: 'labelPane',
                    interactive: false,
                    zIndexOffset: 0
                });
            }

            function createVillageLabel(feature, villageName) {
                const center = computeFeatureCenter(feature);
                if (!center) return null;

                const labelDiv = document.createElement('div');
                labelDiv.textContent = villageName;
                labelDiv.style.cssText = `
                    background: rgba(255, 255, 255, 0.9);
                    color: #004934;
                    padding: 2px 7px;
                    border-radius: 3px;
                    font-size: 9px;
                    font-weight: 600;
                    white-space: nowrap;
                    pointer-events: none;
                    border: 0.7px solid rgba(0, 113, 81, 0.35);
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.18);
                    text-align: center;
                    user-select: none;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                    letter-spacing: 0.02em;
                    text-transform: uppercase;
                    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
                `;

                const labelIcon = L.divIcon({
                    html: labelDiv,
                    className: 'village-label-icon',
                    iconSize: null,
                    iconAnchor: [0, 0]
                });

                return L.marker(center, {
                    icon: labelIcon,
                    pane: 'labelPane',
                    interactive: false,
                    zIndexOffset: 0
                });
            }

            function addVillageLabels(layer) {
                if (!layer || !map.hasLayer(layer)) {
                    villageLabelLayer.clearLayers();
                    return;
                }

                villageLabelLayer.clearLayers();
                const bounds = map.getBounds();
                const boundsValid = bounds && typeof bounds.isValid === 'function' ? bounds.isValid() : false;

                layer.eachLayer(childLayer => {
                    const feature = childLayer && childLayer.feature ? childLayer.feature : null;
                    if (!feature || !feature.properties) return;

                    const center = computeFeatureCenter(feature);
                    if (!center) {
                        return;
                    }
                    if (bounds && boundsValid && !bounds.contains(center)) {
                        return;
                    }

                    const props = feature.properties;
                    const name = props.nm_kelurahan || props.nm_desa || props.nm_desa_kelurahan || 'Desa';
                    const label = createVillageLabel(feature, name);
                    if (label) {
                        villageLabelLayer.addLayer(label);
                    }
                });
            }

            function addDistrictLabels(layer, filterFn) {
                if (!window.kec || !districtsData || !Array.isArray(districtsData) || districtsData.length === 0) {
                    districtLabelLayer.clearLayers();
                    return;
                }
                
                if (!layer || !map.hasLayer(layer)) {
                    return;
                }
                
                districtLabelLayer.clearLayers();
                
                const mapBounds = map.getBounds();
                if (!mapBounds || !mapBounds.isValid()) {
                    return;
                }
                
                window.kec.features.forEach((feature) => {
                    if (!feature || !feature.properties) return;
                    
                    if (typeof filterFn === 'function' && !filterFn(feature)) {
                        return;
                    }
                    
                    const center = computeFeatureCenter(feature);
                    if (!center || !mapBounds.contains(center)) {
                        return;
                    }
                    
                    const props = feature.properties;
                    const featureCode = props.kd_kecamatan;
                    const featureName = props.nm_kecamatan;
                    
                    let districtName = null;
                    let matchedDistrict = null;
                    
                    if (featureCode) {
                        const aliases = codeAliases(featureCode);
                        for (let i = 0; i < aliases.length && !matchedDistrict; i++) {
                            matchedDistrict = districtsData.find(d => {
                                if (!d || !d.code) return false;
                                const districtAliases = codeAliases(d.code);
                                return districtAliases.some(da => aliases.indexOf(da) !== -1);
                            });
                            if (matchedDistrict) {
                                districtName = matchedDistrict.name;
                                break;
                            }
                        }
                    }
                    
                    if (!matchedDistrict && featureName) {
                        const featureSlug = normalizeName(featureName);
                        if (featureSlug) {
                            const variants = slugVariants(featureSlug);
                            for (let j = 0; j < variants.length && !matchedDistrict; j++) {
                                matchedDistrict = districtsData.find(d => {
                                    if (!d || !d.name) return false;
                                    const districtSlug = normalizeName(d.name);
                                    if (!districtSlug) return false;
                                    const districtVariants = slugVariants(districtSlug);
                                    return districtVariants.some(dv => variants.indexOf(dv) !== -1);
                                });
                                if (matchedDistrict) {
                                    districtName = matchedDistrict.name;
                                    break;
                                }
                            }
                        }
                    }
                    
                    if (!districtName && featureName) {
                        districtName = featureName;
                    }
                    
                    if (districtName) {
                        const label = createDistrictLabel(feature, districtName);
                        if (label) {
                            districtLabelLayer.addLayer(label);
                        }
                    }
                });
            }

            function createKecamatanLayer(filterFn) {
                if (!window.kec) return L.layerGroup();
                const options = {
                    style: styleKec,
                    onEachFeature: bindDistrictFeature,
                    pane: 'kecPane'
                };
                if (typeof filterFn === 'function') {
                    options.filter = filterFn;
                }
                return L.geoJSON(window.kec, options);
            }

            function createKelurahanLayer(districtState, filterFn) {
                if (!window.kel) return L.layerGroup();
                const options = {
                    style: styleKel,
                    onEachFeature: bindVillageFeatureFactory(districtState),
                    pane: 'kelPane'
                };
                if (typeof filterFn === 'function') {
                    options.filter = filterFn;
                }
                return L.geoJSON(window.kel, options);
            }

            function addInteractiveLayers(layers) {
                layers = Array.isArray(layers) ? layers : [layers];
                layers.forEach(layer => {
                    if (layer && layer.addTo) {
                        layer.addTo(map);
                    }
                });
                if (!map.hasLayer(kabLayer)) {
                    kabLayer.addTo(map);
                }
                ensureLayerOrder();
            }

            function fitToLayers(primaryLayers) {
                const layers = Array.isArray(primaryLayers) ? primaryLayers : [primaryLayers];
                let bounds = null;
                layers.some(layer => {
                    if (!layer || !layer.getBounds) return false;
                    const layerBounds = layer.getBounds();
                    if (layerBounds && layerBounds.isValid()) {
                        bounds = layerBounds;
                        return true;
                    }
                    return false;
                });
                if (!bounds && kabLayer && kabLayer.getBounds) {
                    const kabBounds = kabLayer.getBounds();
                    if (kabBounds && kabBounds.isValid()) {
                        bounds = kabBounds;
                    }
                }
                if (bounds && bounds.isValid()) {
                    map.fitBounds(bounds.pad(0.05));
                }
            }

            function buildSelectedDistrictState(filterState) {
                filterState = filterState || { code: '', slug: '' };
                const hasSelection = Boolean(filterState.code || filterState.slug);
                if (!hasSelection || !window.kec || !Array.isArray(window.kec.features)) {
                    return null;
                }
                const filterFn = buildDistrictFilter(filterState.code, filterState.slug);
                const aliasSet = {};
                const slugSet = {};
                let districtName = null;
                let matchCount = 0;

                window.kec.features.forEach(feature => {
                    if (!filterFn(feature)) return;
                    matchCount += 1;
                    const props = feature && feature.properties ? feature.properties : {};
                    codeAliases(props.kd_kecamatan).forEach(alias => {
                        if (alias) aliasSet[alias] = true;
                    });
                    slugVariants(normalizeName(props.nm_kecamatan)).forEach(slug => {
                        if (slug) slugSet[slug] = true;
                    });
                    if (!districtName && props.nm_kecamatan) {
                        districtName = props.nm_kecamatan;
                    }
                });

                if (!matchCount) return null;

                if (filterState.slug) {
                    slugVariants(normalizeName(filterState.slug)).forEach(slug => {
                        if (slug) slugSet[slug] = true;
                    });
                }

                return {
                    code: filterState.code || '',
                    slug: filterState.slug || '',
                    filterFn: filterFn,
                    aliasSet: aliasSet,
                    slugSet: Object.keys(slugSet).length ? slugSet : null,
                    name: districtName
                };
            }

            function renderKabupatenOverview() {
                removeLayer(kecLayer);
                removeLayer(kelLayer);
                districtLabelLayer.clearLayers();
                villageLabelLayer.clearLayers();
                kecLayer = createKecamatanLayer(null);
                kelLayer = null;
                addInteractiveLayers(kecLayer);
                fitToLayers(kecLayer);
                map.whenReady(function() {
                    setTimeout(function() {
                        if (kecLayer && map.hasLayer(kecLayer)) {
                            addDistrictLabels(kecLayer, null);
                        }
                    }, 600);
                });
            }

            function renderSelectedDistrict(selectionState) {
                removeLayer(kecLayer);
                removeLayer(kelLayer);
                districtLabelLayer.clearLayers();
                villageLabelLayer.clearLayers();

                const districtState = {
                    codeAliases: Object.keys(selectionState.aliasSet || {}),
                    slugVariants: selectionState.slugSet ? Object.keys(selectionState.slugSet) : [],
                    name: selectionState.name
                };

                kecLayer = createKecamatanLayer(selectionState.filterFn);
                const kelFilterFn = (feature) => {
                    const props = feature && feature.properties ? feature.properties : {};
                    const aliases = codeAliases(props.kd_kecamatan);
                    for (let i = 0; i < aliases.length; i++) {
                        if (selectionState.aliasSet[aliases[i]]) {
                            return true;
                        }
                    }
                    if (selectionState.slugSet) {
                        const slug = normalizeName(props.nm_kecamatan);
                        if (slug && selectionState.slugSet[slug]) {
                            return true;
                        }
                    }
                    return false;
                };

                kelLayer = createKelurahanLayer(districtState, kelFilterFn);
                if (kelLayer && typeof kelLayer.getLayers === 'function' && kelLayer.getLayers().length === 0) {
                    kelLayer = null;
                }
                addInteractiveLayers([kecLayer, kelLayer]);
                fitToLayers([kelLayer, kecLayer]);
                
                if (kelLayer && kelLayer.getLayers && kelLayer.getLayers().length > 0) {
                    districtLabelLayer.clearLayers();
                    map.whenReady(function() {
                        setTimeout(function() {
                            if (kelLayer && map.hasLayer(kelLayer)) {
                                addVillageLabels(kelLayer);
                            }
                        }, 400);
                    });
                } else {
                    map.whenReady(function() {
                        setTimeout(function() {
                            if (kecLayer && map.hasLayer(kecLayer)) {
                                addDistrictLabels(kecLayer, selectionState.filterFn);
                            }
                        }, 600);
                    });
                }
            }

            function rebuildDistrictLayers(filterState) {
                filterState = filterState || { code: '', slug: '' };
                const selectionState = buildSelectedDistrictState(filterState);
                if (selectionState) {
                    renderSelectedDistrict(selectionState);
                } else {
                    renderKabupatenOverview();
                }
            }

            const districtFilterEl = document.getElementById('landing-district-filter');
            let currentDistrictFilter = { code: '', slug: '' };

            if (districtFilterEl) {
                const initialOption = districtFilterEl.selectedOptions && districtFilterEl.selectedOptions.length
                    ? districtFilterEl.selectedOptions[0]
                    : null;
                currentDistrictFilter = {
                    code: districtFilterEl.value || '',
                    slug: initialOption ? (initialOption.getAttribute('data-slug') || '') : ''
                };

                districtFilterEl.addEventListener('change', function() {
                    const option = this.selectedOptions && this.selectedOptions.length ? this.selectedOptions[0] : null;
                    currentDistrictFilter = {
                        code: this.value || '',
                        slug: option ? (option.getAttribute('data-slug') || '') : ''
                    };
                    rebuildDistrictLayers(currentDistrictFilter);
                });
            }

            const baseLayers = {
                'Default': cartoVoyager,
                'Light': carto,
                'Dark': cartoDark,
                'OSM': osm,
                'Satellite': googleSat
            };

            L.control.layers(baseLayers, {}, { collapsed: true, position: 'topright' }).addTo(map);

            rebuildDistrictLayers(currentDistrictFilter);
            
            map.on('zoomend', function() {
                if (kecLayer && !kelLayer) {
                    setTimeout(function() {
                        const currentFilter = currentDistrictFilter && currentDistrictFilter.code 
                            ? buildSelectedDistrictState(currentDistrictFilter) 
                            : null;
                        const filterFn = currentFilter ? currentFilter.filterFn : null;
                        addDistrictLabels(kecLayer, filterFn);
                    }, 200);
                }
            });
            
            map.on('moveend', function() {
                if (kecLayer && !kelLayer) {
                    setTimeout(function() {
                        const currentFilter = currentDistrictFilter && currentDistrictFilter.code 
                            ? buildSelectedDistrictState(currentDistrictFilter) 
                            : null;
                        const filterFn = currentFilter ? currentFilter.filterFn : null;
                        addDistrictLabels(kecLayer, filterFn);
                    }, 200);
                }
            });
        })();

        @if (!empty($populationGrowth['labels']) && count($populationGrowth['labels']) > 0)
        // Population Growth Chart
        document.addEventListener('DOMContentLoaded', function() {
            // Tunggu sedikit untuk memastikan Chart.js sudah dimuat
            setTimeout(function() {
                // Pastikan Chart.js sudah dimuat
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js is not loaded');
                    return;
                }

                const growthData = @json($populationGrowth);
                const canvas = document.getElementById('population-growth-chart');
                
                if (!canvas) {
                    console.error('Canvas element not found');
                    return;
                }

                if (!growthData || !growthData.labels || growthData.labels.length === 0) {
                    console.error('Growth data is empty or invalid', growthData);
                    return;
                }

                // Validasi data
                if (!growthData.data || !Array.isArray(growthData.data) || growthData.data.length === 0) {
                    console.error('Growth data.data is empty or invalid', growthData);
                    return;
                }

                console.log('Initializing population growth chart with data:', growthData);

                const ctx = canvas.getContext('2d');
                const getCssColor = (name, fallback) => {
                    const value = getComputedStyle(document.documentElement).getPropertyValue(name);
                    return value ? value.trim() || fallback : fallback;
                };
                const themeAxis = getCssColor('--color-text', '#e2e8f0');
                const themeGrid = getCssColor('--color-chart-grid', 'rgba(148,163,184,0.35)');
                
                // Proses data untuk menghandle null values dengan benar
                // Growth rate dihitung dengan membandingkan periode sebelumnya
                // Null di periode pertama adalah normal (tidak ada periode sebelumnya untuk dibandingkan)
                const processedGrowthRates = (growthData.growthRates || []).map((rate, index) => {
                    return rate === null ? null : rate;
                });
                
                try {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: growthData.labels || [],
                            datasets: [
                                {
                                    label: 'Jumlah Penduduk',
                                    data: growthData.data || [],
                                    borderColor: '#009B4D',
                                    backgroundColor: 'rgba(0, 113, 81, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4,
                                    yAxisID: 'y',
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                },
                                {
                                    label: 'Laju Pertumbuhan (%)',
                                    data: processedGrowthRates,
                                    borderColor: '#009B4D',
                                    backgroundColor: 'rgba(0, 168, 118, 0.1)',
                                    borderWidth: 2,
                                    fill: false,
                                    tension: 0.4,
                                    yAxisID: 'y1',
                                    borderDash: [5, 5],
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    spanGaps: true, // Hubungkan titik meskipun ada null (untuk menampilkan garis kontinyu)
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 15,
                                        font: {
                                            size: 12,
                                            family: "'Inter', 'Poppins', sans-serif"
                                        },
                                        color: themeAxis
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    titleFont: {
                                        size: 13,
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        size: 12
                                    },
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.datasetIndex === 0) {
                                                // Jumlah Penduduk
                                                label += new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                            } else {
                                                // Laju Pertumbuhan (%)
                                                const value = context.parsed.y;
                                                if (value === null || value === undefined || isNaN(value)) {
                                                    label += '-';
                                                } else {
                                                    label += value.toFixed(2) + '%';
                                                }
                                            }
                                            return label;
                                        },
                                        filter: function(tooltipItem) {
                                            // Tampilkan tooltip bahkan jika value null (untuk informatif)
                                            return true;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    display: true,
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 11,
                                            family: "'Inter', 'Poppins', sans-serif"
                                        },
                                        color: themeAxis
                                    }
                                },
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    beginAtZero: false,
                                    grid: {
                                        color: themeGrid
                                    },
                                    ticks: {
                                        font: {
                                            size: 11,
                                            family: "'Inter', 'Poppins', sans-serif"
                                        },
                                        color: themeAxis,
                                        callback: function(value) {
                                            return new Intl.NumberFormat('id-ID').format(value);
                                        }
                                    },
                                    title: {
                                        display: true,
                                        text: 'Jumlah Penduduk',
                                        font: {
                                            size: 12,
                                            weight: 'bold',
                                            family: "'Inter', 'Poppins', sans-serif"
                                        },
                                        color: themeAxis
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    beginAtZero: false,
                                    grid: {
                                        drawOnChartArea: false,
                                        color: themeGrid,
                                    },
                                    ticks: {
                                        font: {
                                            size: 11,
                                            family: "'Inter', 'Poppins', sans-serif"
                                        },
                                        color: themeAxis,
                                        callback: function(value) {
                                            if (value === null || isNaN(value)) {
                                                return '';
                                            }
                                            return value.toFixed(2) + '%';
                                        }
                                    },
                                    title: {
                                        display: true,
                                        text: 'Laju Pertumbuhan (%)',
                                        font: {
                                            size: 12,
                                            weight: 'bold',
                                            family: "'Inter', 'Poppins', sans-serif"
                                        },
                                        color: themeAxis
                                    }
                                }
                            }
                        }
                    });
                } catch (error) {
                    console.error('Error initializing chart:', error);
                }
            }, 100);
        });
        @endif
    </script>
@endpush
