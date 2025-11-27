{{-- Skeleton loading untuk chart --}}
<div class="skeleton-chart">
    <div class="skeleton-chart-header">
        <div class="skeleton-title"></div>
        <div class="skeleton-subtitle"></div>
    </div>
    <div class="skeleton-chart-body">
        <div class="skeleton-bars">
            @for ($i = 0; $i < 8; $i++)
            <div class="skeleton-bar" style="height: {{ rand(40, 90) }}%;"></div>
            @endfor
        </div>
    </div>
    <div class="skeleton-chart-legend">
        <div class="skeleton-legend-item"></div>
        <div class="skeleton-legend-item"></div>
        <div class="skeleton-legend-item"></div>
    </div>
</div>

<style>
.skeleton-chart {
    width: 100%;
    padding: 1.5rem;
    background: var(--color-surface);
    border-radius: 1rem;
}

.skeleton-chart-header {
    margin-bottom: 2rem;
}

.skeleton-title {
    height: 1.5rem;
    width: 40%;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
}

.skeleton-subtitle {
    height: 1rem;
    width: 60%;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
    border-radius: 0.375rem;
}

.skeleton-chart-body {
    height: 300px;
    display: flex;
    align-items: flex-end;
    padding: 1rem 0;
}

.skeleton-bars {
    display: flex;
    align-items: flex-end;
    justify-content: space-around;
    width: 100%;
    height: 100%;
    gap: 0.5rem;
}

.skeleton-bar {
    flex: 1;
    background: linear-gradient(90deg, #e0e0e0 25%, #d0d0d0 50%, #e0e0e0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
    border-radius: 0.25rem 0.25rem 0 0;
    min-height: 20%;
}

.skeleton-chart-legend {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--color-border);
}

.skeleton-legend-item {
    height: 1rem;
    width: 5rem;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
    border-radius: 0.375rem;
}

@keyframes skeleton-loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.dark .skeleton-title,
.dark .skeleton-subtitle,
.dark .skeleton-legend-item {
    background: linear-gradient(90deg, #1e293b 25%, #334155 50%, #1e293b 75%);
    background-size: 200% 100%;
}

.dark .skeleton-bar {
    background: linear-gradient(90deg, #334155 25%, #475569 50%, #334155 75%);
    background-size: 200% 100%;
}
</style>
