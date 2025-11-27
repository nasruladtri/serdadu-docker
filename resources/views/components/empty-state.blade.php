@props([
    'type' => 'no-data', // 'no-data', 'no-filter', 'no-results'
    'title' => '',
    'message' => '',
    'icon' => 'chart'
])

@php
    $icons = [
        'chart' => '<svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>',
        'filter' => '<svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>',
        'search' => '<svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>',
        'database' => '<svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                    </svg>',
        'folder' => '<svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>'
    ];

    $defaultTitles = [
        'no-data' => 'Data Belum Tersedia',
        'no-filter' => 'Pilih Filter',
        'no-results' => 'Tidak Ada Hasil'
    ];

    $defaultMessages = [
        'no-data' => 'Belum ada data untuk periode dan wilayah yang dipilih. Silakan pilih filter lain atau hubungi administrator.',
        'no-filter' => 'Silakan pilih tahun, semester, dan wilayah untuk menampilkan data.',
        'no-results' => 'Pencarian Anda tidak menghasilkan data. Coba ubah kriteria pencarian Anda.'
    ];

    $displayTitle = $title ?: ($defaultTitles[$type] ?? 'Tidak Ada Data');
    $displayMessage = $message ?: ($defaultMessages[$type] ?? 'Silakan coba lagi.');
@endphp

<div class="empty-state">
    <div class="empty-state-icon">
        {!! $icons[$icon] ?? $icons['chart'] !!}
    </div>
    <h3 class="empty-state-title">{{ $displayTitle }}</h3>
    <p class="empty-state-message">{{ $displayMessage }}</p>
    @if(isset($action))
        <div class="empty-state-action">
            {{ $action }}
        </div>
    @endif
</div>

<style>
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1.5rem;
    text-align: center;
    min-height: 300px;
}

.empty-state-icon {
    margin-bottom: 1.5rem;
    opacity: 0.5;
    animation: empty-state-float 3s ease-in-out infinite;
}

.empty-state-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--color-text);
    margin-bottom: 0.5rem;
}

.empty-state-message {
    font-size: 0.875rem;
    color: var(--color-text-muted);
    max-width: 28rem;
    line-height: 1.5;
    margin-bottom: 1.5rem;
}

.empty-state-action {
    margin-top: 0.5rem;
}

@keyframes empty-state-float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

.dark .empty-state-icon svg {
    color: #475569;
}

@media (max-width: 640px) {
    .empty-state {
        padding: 2rem 1rem;
        min-height: 250px;
    }
    
    .empty-state-icon svg {
        width: 3rem;
        height: 3rem;
    }
    
    .empty-state-title {
        font-size: 1.125rem;
    }
    
    .empty-state-message {
        font-size: 0.8125rem;
    }
}
</style>
