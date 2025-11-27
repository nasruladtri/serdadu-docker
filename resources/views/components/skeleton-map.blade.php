{{-- Skeleton loading untuk peta --}}
<div class="skeleton-map">
    <div class="skeleton-map-container">
        <div class="skeleton-map-overlay">
            <div class="skeleton-spinner"></div>
            <div class="skeleton-map-text">Memuat peta...</div>
        </div>
        <div class="skeleton-map-bg"></div>
    </div>
</div>

<style>
.skeleton-map {
    width: 100%;
    height: 100%;
    min-height: 600px;
    position: relative;
    border-radius: 0.5rem;
    overflow: hidden;
}

.skeleton-map-container {
    width: 100%;
    height: 100%;
    position: relative;
}

.skeleton-map-bg {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 400% 400%;
    animation: skeleton-map-loading 3s ease infinite;
}

.skeleton-map-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    z-index: 10;
}

.skeleton-spinner {
    width: 3rem;
    height: 3rem;
    border: 4px solid #e0e0e0;
    border-top-color: #009B4D;
    border-radius: 50%;
    animation: skeleton-spin 1s linear infinite;
}

.skeleton-map-text {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
}

@keyframes skeleton-map-loading {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

@keyframes skeleton-spin {
    to { transform: rotate(360deg); }
}

.dark .skeleton-map-bg {
    background: linear-gradient(135deg, #1e293b 25%, #334155 50%, #1e293b 75%);
    background-size: 400% 400%;
}

.dark .skeleton-spinner {
    border-color: #334155;
    border-top-color: #009B4D;
}

.dark .skeleton-map-text {
    color: #cbd5e1;
}

@media (max-width: 640px) {
    .skeleton-map {
        min-height: 400px;
    }
}
</style>
