{{-- Skeleton loading untuk tabel --}}
<div class="skeleton-table">
    <div class="skeleton-table-header">
        <div class="skeleton-row">
            <div class="skeleton-cell skeleton-cell-sm"></div>
            <div class="skeleton-cell"></div>
            <div class="skeleton-cell skeleton-cell-sm"></div>
            <div class="skeleton-cell skeleton-cell-sm"></div>
            <div class="skeleton-cell skeleton-cell-sm"></div>
        </div>
    </div>
    <div class="skeleton-table-body">
        @for ($i = 0; $i < 8; $i++)
        <div class="skeleton-row">
            <div class="skeleton-cell skeleton-cell-sm"></div>
            <div class="skeleton-cell"></div>
            <div class="skeleton-cell skeleton-cell-sm"></div>
            <div class="skeleton-cell skeleton-cell-sm"></div>
            <div class="skeleton-cell skeleton-cell-sm"></div>
        </div>
        @endfor
    </div>
</div>

<style>
.skeleton-table {
    width: 100%;
    padding: 1rem;
}

.skeleton-table-header {
    margin-bottom: 0.5rem;
}

.skeleton-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.skeleton-cell {
    height: 2rem;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
    border-radius: 0.375rem;
    flex: 1;
}

.skeleton-cell-sm {
    flex: 0 0 4rem;
}

.skeleton-table-header .skeleton-cell {
    height: 2.5rem;
    background: linear-gradient(90deg, #e0e0e0 25%, #d0d0d0 50%, #e0e0e0 75%);
    background-size: 200% 100%;
}

@keyframes skeleton-loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.dark .skeleton-cell {
    background: linear-gradient(90deg, #1e293b 25%, #334155 50%, #1e293b 75%);
    background-size: 200% 100%;
}

.dark .skeleton-table-header .skeleton-cell {
    background: linear-gradient(90deg, #334155 25%, #475569 50%, #334155 75%);
    background-size: 200% 100%;
}
</style>
