@php
    $type = $type ?? 'table'; // 'table', 'chart', 'compare'
    $format = $format ?? 'pdf'; // 'pdf', 'excel'
    $title = $title ?? 'Download Data Agregat';
@endphp

<!-- Download Modal -->
<div id="downloadModal" class="fixed inset-0 z-[9999] hidden" aria-labelledby="download-modal-title" role="dialog" aria-modal="true" style="display: none;">
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-gray-500/75 transition-opacity" id="downloadModalOverlay" style="z-index: 99998;"></div>
    
    <!-- Modal container -->
    <div class="relative z-[99999] flex min-h-full w-full h-full items-center justify-center px-4 py-8 pointer-events-none">
        <!-- Modal panel -->
        <div id="downloadModalPanel" class="download-modal-panel pointer-events-auto mx-auto inline-block w-full max-w-xl transform overflow-hidden rounded-2xl shadow-xl transition-all">
            <div class="download-modal-content px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="download-modal-title text-lg font-medium" id="download-modal-title">{{ $title }}</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none" id="closeDownloadModal">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Instruction text -->
                <p class="download-modal-text text-sm mb-4">
                    Silahkan isi data diri dan tujuan penggunaan data agregat terlebih dahulu
                </p>

                <!-- Form -->
                <form id="downloadForm" method="GET" novalidate>
                    <input type="hidden" name="download_type" id="downloadType" value="{{ $type }}">
                    <input type="hidden" name="download_format" id="downloadFormat" value="{{ $format }}">
                    <input type="hidden" name="download_url" id="downloadUrl" value="">
                    <input type="hidden" name="download_label" id="downloadLabel" value="">

                    <div class="space-y-3">
                        <!-- Nama Lengkap -->
                        <div>
                            <label for="nama_lengkap" class="download-modal-label block text-sm font-medium mb-0.5">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_lengkap" id="nama_lengkap" required
                                class="download-modal-input w-full px-3 py-1.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#009B4D] focus:border-[#009B4D]"
                                placeholder="Masukkan nama lengkap">
                        </div>

                        <!-- Alamat -->
                        <div>
                            <label for="alamat" class="download-modal-label block text-sm font-medium mb-0.5">
                                Alamat <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="alamat" id="alamat" required
                                class="download-modal-input w-full px-3 py-1.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#009B4D] focus:border-[#009B4D]"
                                placeholder="Masukkan alamat">
                        </div>

                        <!-- Pekerjaan -->
                        <div>
                            <label for="pekerjaan" class="download-modal-label block text-sm font-medium mb-0.5">
                                Pekerjaan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="pekerjaan" id="pekerjaan" required
                                class="download-modal-input w-full px-3 py-1.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#009B4D] focus:border-[#009B4D]"
                                placeholder="Masukkan pekerjaan">
                        </div>

                        <!-- Instansi / Tempat Kerja -->
                        <div>
                            <label for="instansi" class="download-modal-label block text-sm font-medium mb-0.5">
                                Instansi / Tempat Kerja <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="instansi" id="instansi" required
                                class="download-modal-input w-full px-3 py-1.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#009B4D] focus:border-[#009B4D]"
                                placeholder="Masukkan instansi atau tempat kerja">
                        </div>

                        <!-- Nomor Telepon -->
                        <div>
                            <label for="nomor_telepon" class="download-modal-label block text-sm font-medium mb-0.5">
                                Nomor Telepon <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" name="nomor_telepon" id="nomor_telepon" required
                                class="download-modal-input w-full px-3 py-1.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#009B4D] focus:border-[#009B4D]"
                                placeholder="Masukkan nomor telepon">
                        </div>

                        <!-- Tujuan Penggunaan -->
                        <div>
                            <label for="tujuan_penggunaan" class="download-modal-label block text-sm font-medium mb-0.5">
                                Tujuan Penggunaan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="tujuan_penggunaan" id="tujuan_penggunaan" rows="3" required
                                class="download-modal-input w-full px-3 py-1.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#009B4D] focus:border-[#009B4D]"
                                placeholder="Masukkan tujuan penggunaan data"></textarea>
                        </div>

                        <!-- Syarat & Ketentuan Checkbox -->
                        <div class="flex items-start">
                            <input type="checkbox" name="syarat_ketentuan" id="syarat_ketentuan" required
                                class="mt-1 h-4 w-4 text-[#009B4D] focus:ring-[#009B4D] border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded">
                            <label for="syarat_ketentuan" class="download-modal-label ml-2 text-sm">
                                Saya telah membaca 
                                <a href="#" id="termsLink" class="text-[#009B4D] hover:text-[#007a3d] dark:text-green-400 dark:hover:text-green-300 underline" target="_blank">
                                    Syarat & Ketentuan Layanan Serdadu
                                </a>
                                <span class="text-red-500">*</span>
                            </label>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" id="cancelDownload" class="download-modal-button px-4 py-2 text-sm font-medium border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#009B4D]">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-[#009B4D] border border-transparent rounded-md hover:bg-[#007a3d] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#009B4D]">
                            Download
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Override browser autofill styles - SCOPED TO DOWNLOAD MODAL ONLY */
    /* Light Mode */
    #downloadModal input:-webkit-autofill,
    #downloadModal input:-webkit-autofill:hover, 
    #downloadModal input:-webkit-autofill:focus, 
    #downloadModal input:-webkit-autofill:active,
    #downloadModal textarea:-webkit-autofill,
    #downloadModal textarea:-webkit-autofill:hover, 
    #downloadModal textarea:-webkit-autofill:focus, 
    #downloadModal textarea:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 30px white inset !important;
        -webkit-text-fill-color: black !important;
        transition: background-color 5000s ease-in-out 0s;
    }

    /* Dark Mode */
    .dark #downloadModal input:-webkit-autofill,
    .dark #downloadModal input:-webkit-autofill:hover, 
    .dark #downloadModal input:-webkit-autofill:focus, 
    .dark #downloadModal input:-webkit-autofill:active,
    .dark #downloadModal textarea:-webkit-autofill,
    .dark #downloadModal textarea:-webkit-autofill:hover, 
    .dark #downloadModal textarea:-webkit-autofill:focus, 
    .dark #downloadModal textarea:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 30px #374151 inset !important; /* gray-700 */
        -webkit-text-fill-color: white !important;
    }
    
    /* Modal Animations - Match Help Menu */
    @keyframes modalFadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    @keyframes modalFadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
    
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(1rem) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    @keyframes modalSlideOut {
        from {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        to {
            opacity: 0;
            transform: translateY(1rem) scale(0.95);
        }
    }
    
    /* Apply animations */
    #downloadModal.modal-entering {
        animation: modalFadeIn 300ms ease-out forwards;
    }
    
    #downloadModal.modal-leaving {
        animation: modalFadeOut 200ms ease-in forwards;
    }
    
    #downloadModal.modal-entering #downloadModalPanel {
        animation: modalSlideIn 300ms ease-out forwards;
    }
    
    #downloadModal.modal-leaving #downloadModalPanel {
        animation: modalSlideOut 200ms ease-in forwards;
    }
    
    /* Overlay animations */
    #downloadModalOverlay.overlay-entering {
        animation: modalFadeIn 300ms ease-out forwards;
    }
    
    #downloadModalOverlay.overlay-leaving {
        animation: modalFadeOut 200ms ease-in forwards;
    }
    
    /* Force Light Mode Styles (when NOT in dark mode) */
    :not(.dark) .download-modal-panel {
        background-color: white !important;
    }
    
    :not(.dark) .download-modal-content {
        background-color: white !important;
    }
    
    :not(.dark) .download-modal-title {
        color: #111827 !important;
    }
    
    :not(.dark) .download-modal-text {
        color: #4B5563 !important;
    }
    
    :not(.dark) .download-modal-label {
        color: #374151 !important;
    }
    
    :not(.dark) .download-modal-input {
        background-color: white !important;
        color: #111827 !important;
    }
    
    :not(.dark) .download-modal-button {
        background-color: white !important;
        color: #374151 !important;
    }
    
    /* Dark Mode Styles */
    .dark .download-modal-panel {
        background-color: #0F172A !important; /* slate-900 - darker to match help menu */
    }
    
    .dark .download-modal-content {
        background-color: #0F172A !important; /* slate-900 - darker to match help menu */
    }
    
    .dark .download-modal-title {
        color: white !important;
    }
    
    .dark .download-modal-text {
        color: #D1D5DB !important; /* gray-300 */
    }
    
    .dark .download-modal-label {
        color: #D1D5DB !important; /* gray-300 */
    }
    
    .dark .download-modal-input {
        background-color: #374151 !important; /* gray-700 */
        color: white !important;
        border-color: #4B5563 !important; /* gray-600 */
    }
    
    .dark .download-modal-input::placeholder {
        color: #9CA3AF !important; /* gray-400 */
    }
    
    .dark .download-modal-button {
        background-color: #374151 !important; /* gray-700 */
        color: #E5E7EB !important; /* gray-200 */
        border-color: #4B5563 !important; /* gray-600 */
    }
    
    .dark .download-modal-button:hover {
        background-color: #4B5563 !important; /* gray-600 */
    }
</style>
@endpush

@push('scripts')
<script>
// Initialize modal handlers when DOM is ready
// Note: Event listeners for download buttons are already setup in layout head
// But we also setup direct handlers here as backup
(function() {
    'use strict';
    
    // Helper to sanitize file names (remove invalid chars, collapse spaces)
    function sanitizeFilename(name) {
        if (!name) {
            return '';
        }
        return name
            .toString()
            .trim()
            .replace(/[\\/:*?"<>|]/g, '')
            .replace(/\s+/g, '-');
    }

    function ensureExtension(filename, extension) {
        if (!filename) {
            return '';
        }
        return filename.toLowerCase().endsWith(extension.toLowerCase())
            ? filename
            : filename + extension;
    }

    function buildFilenameFromParams(urlObj, extension) {
        if (!urlObj || !urlObj.searchParams) {
            return '';
        }
        const category = urlObj.searchParams.get('category') || 'data';
        const year = urlObj.searchParams.get('year');
        const semester = urlObj.searchParams.get('semester');
        let base = 'data-' + category;
        if (year) {
            base += '-' + year;
        }
        if (semester) {
            base += '-s' + semester;
        }
        return ensureExtension(sanitizeFilename(base), extension);
    }
    
    // Also initialize when DOM is fully ready
    function initDownloadModal() {
        const modal = document.getElementById('downloadModal');
        const overlay = document.getElementById('downloadModalOverlay');
        const closeBtn = document.getElementById('closeDownloadModal');
        const cancelBtn = document.getElementById('cancelDownload');
        const form = document.getElementById('downloadForm');
        const termsLink = document.getElementById('termsLink');
        const downloadLabelInput = document.getElementById('downloadLabel');

        if (!modal) {
            console.warn('Download modal element not found');
            return;
        }

    // Function to close modal with animation
    function closeDownloadModal() {
        if (modal) {
            // Add leaving animation classes
            modal.classList.add('modal-leaving');
            modal.classList.remove('modal-entering');
            if (overlay) {
                overlay.classList.add('overlay-leaving');
                overlay.classList.remove('overlay-entering');
            }
            
            // Wait for animation to complete before hiding
            setTimeout(function() {
                modal.classList.add('hidden');
                modal.style.removeProperty('display');
                modal.classList.remove('modal-leaving');
                document.body.style.overflow = '';
                document.documentElement.style.overflow = '';
                if (overlay) {
                    overlay.classList.add('hidden');
                    overlay.style.removeProperty('display');
                    overlay.classList.remove('overlay-leaving');
                }
                if (form) form.reset();
            }, 200); // Match animation duration
        }
    }
    window.__closeDownloadModal = closeDownloadModal;

    // Close modal events
    if (closeBtn) {
        closeBtn.addEventListener('click', closeDownloadModal);
    }
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeDownloadModal);
    }
    if (overlay) {
        overlay.addEventListener('click', closeDownloadModal);
    }

    // Handle form submission
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const explicitUrl = form.getAttribute('action') || formData.get('download_url');
            
            if (!explicitUrl) {
                alert('URL download tidak valid');
                return;
            }
            
            if (!formData.get('syarat_ketentuan')) {
                alert('Anda harus menyetujui Syarat & Ketentuan terlebih dahulu');
                return;
            }
            
            const format = (formData.get('download_format') || 'pdf').toLowerCase();
            const extension = format === 'excel' ? '.xlsx' : '.pdf';
            const labelValue = downloadLabelInput ? downloadLabelInput.value : '';
            const sanitizedLabel = sanitizeFilename(labelValue);
            let finalUrl = explicitUrl || '';
            try {
                const url = new URL(explicitUrl, window.location.origin);
                
                formData.forEach((value, key) => {
                    if (['_token', 'download_url'].includes(key)) {
                        return;
                    }
                    url.searchParams.set(key, value || '');
                });
                
                closeDownloadModal();
                
                finalUrl = url.toString();
                const response = await fetch(finalUrl, {
                    method: 'GET',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/pdf, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/octet-stream'
                    }
                });
                
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }
                
                if (!response.ok) {
                    throw new Error('Gagal mengunduh file (status ' + response.status + ')');
                }
                
                const blob = await response.blob();
                const contentType = response.headers.get('Content-Type') || '';
                if (contentType.includes('text/html')) {
                    const text = await blob.text();
                    console.warn('Unexpected HTML response when downloading file', text.slice(0, 200));
                    alert('Gagal mengunduh file. Silakan periksa kembali filter yang dipilih.');
                    return;
                }
                const disposition = response.headers.get('Content-Disposition') || '';
                let filename = '';
                const match = disposition.match(/filename\*=UTF-8''([^;]+)|filename="?([^"]+)"?/i);
                if (match) {
                    filename = decodeURIComponent(match[1] || match[2]);
                }

                if (!filename && sanitizedLabel) {
                    filename = ensureExtension(sanitizedLabel, extension);
                }

                if (!filename) {
                    filename = buildFilenameFromParams(url, extension);
                }

                if (!filename) {
                    filename = 'download' + extension;
                }
                
                const blobUrl = window.URL.createObjectURL(blob);
                const tempLink = document.createElement('a');
                tempLink.href = blobUrl;
                tempLink.download = filename;
                document.body.appendChild(tempLink);
                tempLink.click();
                document.body.removeChild(tempLink);
                window.URL.revokeObjectURL(blobUrl);
            } catch (error) {
                console.error('Error creating download:', error);
                alert('Terjadi kesalahan saat memproses download. Silakan coba lagi.');
                if (finalUrl) {
                    window.open(finalUrl, '_blank');
                }
            }
        });
    }

    // Set terms link to open in new tab
    if (termsLink) {
        termsLink.href = '{{ route("public.terms") }}';
    }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDownloadModal);
    } else {
        initDownloadModal();
    }
})();

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('downloadModal');
        if (modal && !modal.classList.contains('hidden')) {
            if (typeof window.__closeDownloadModal === 'function') {
                window.__closeDownloadModal();
            }
        }
    }
});
</script>
@endpush
