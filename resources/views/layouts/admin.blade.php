<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Serdadu') - Sistem Rekap Data Terpadu</title>
    <script>
        (function() {
            const storageKey = 'theme';
            const root = document.documentElement;
            const getStoredTheme = () => {
                try {
                    const stored = localStorage.getItem(storageKey);
                    if (stored === 'dark' || stored === 'light') return stored;
                } catch (e) {
                    /* ignore storage errors */
                }
                return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            };
            const initialTheme = getStoredTheme();
            if (initialTheme === 'dark') {
                root.classList.add('dark');
            } else {
                root.classList.remove('dark');
            }
            root.dataset.theme = initialTheme;
        })();
    </script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    
    {{-- Download Modal Script - Load early to ensure function and event listeners are available --}}
    <script>
    (function() {
        'use strict';
        
        // Define openDownloadModal function early (before DOM is ready)
        // This function MUST be available immediately when page loads
        window.openDownloadModal = function(type, format, url, label) {
            console.log('=== openDownloadModal CALLED ===');
            console.log('Parameters:', {type: type, format: format, url: url});
            console.log('Document ready state:', document.readyState);
            
            // Helper function to show modal
            function showModal(modalElement) {
                console.log('=== showModal function called ===');
                console.log('Modal element:', modalElement);
                
                try {
                    // Remove hidden class
                    console.log('Step 1: Removing hidden class');
                    modalElement.classList.remove('hidden');
                    
                    // Remove inline display:none
                    console.log('Step 2: Checking inline styles');
                    if (modalElement.style.display === 'none') {
                        console.log('Found inline display:none, removing...');
                        modalElement.style.removeProperty('display');
                    }
                    
                    // Remove hidden attribute
                    console.log('Step 3: Removing hidden attribute');
                    modalElement.removeAttribute('hidden');
                    
                    // Force show with !important
                    console.log('Step 4: Setting display:block with !important');
                    modalElement.style.setProperty('display', 'flex', 'important');
                    modalElement.style.setProperty('visibility', 'visible', 'important');
                    modalElement.style.setProperty('opacity', '1', 'important');
                    modalElement.style.setProperty('z-index', '99999', 'important');
                    modalElement.style.setProperty('position', 'fixed', 'important');
                    modalElement.style.setProperty('top', '0', 'important');
                    modalElement.style.setProperty('left', '0', 'important');
                    modalElement.style.setProperty('right', '0', 'important');
                    modalElement.style.setProperty('bottom', '0', 'important');
                    modalElement.style.setProperty('width', '100%', 'important');
                    modalElement.style.setProperty('height', '100%', 'important');
                    
                    // Also ensure overlay is visible
                    var overlay = document.getElementById('downloadModalOverlay');
                    if (overlay) {
                        console.log('Ensuring overlay is visible');
                        overlay.style.setProperty('display', 'block', 'important');
                        overlay.style.setProperty('opacity', '0.75', 'important');
                        overlay.style.setProperty('z-index', '99998', 'important');
                        overlay.style.setProperty('position', 'fixed', 'important');
                        overlay.style.setProperty('top', '0', 'important');
                        overlay.style.setProperty('left', '0', 'important');
                        overlay.style.setProperty('right', '0', 'important');
                        overlay.style.setProperty('bottom', '0', 'important');
                        overlay.style.setProperty('width', '100%', 'important');
                        overlay.style.setProperty('height', '100%', 'important');
                        overlay.classList.remove('hidden');
                    } else {
                        console.warn('Overlay not found!');
                    }
                    
                    // Find modal content panel by ID first, then fallback
                    var modalPanel = document.getElementById('downloadModalPanel');
                    if (!modalPanel) {
                        // Fallback: try to find by class
                        modalPanel = modalElement.querySelector('.inline-block');
                        if (!modalPanel) {
                            // Try to find any white panel
                            var panels = modalElement.querySelectorAll('div[class*="bg-white"]');
                            if (panels.length > 0) {
                                modalPanel = panels[0]; // Get the first one (should be the panel)
                            }
                        }
                    }
                    if (modalPanel) {
                        console.log('Modal panel found, ensuring visibility:', modalPanel);
                        modalPanel.style.setProperty('display', 'inline-block', 'important');
                        modalPanel.style.setProperty('visibility', 'visible', 'important');
                        modalPanel.style.setProperty('opacity', '1', 'important');
                        modalPanel.style.setProperty('z-index', '100000', 'important');
                        modalPanel.style.setProperty('position', 'relative', 'important');
                        modalPanel.classList.remove('hidden');
                    } else {
                        console.warn('Modal panel not found!');
                    }
                    
                    // Ensure all child divs are visible too
                    var allChildren = modalElement.querySelectorAll('div');
                    console.log('Found', allChildren.length, 'child divs in modal');
                    allChildren.forEach(function(child, index) {
                        var childComputed = window.getComputedStyle(child);
                        if (childComputed.display === 'none' && !child.id.includes('Overlay')) {
                            console.log('Child', index, 'is hidden, making visible');
                            child.style.setProperty('display', '', 'important');
                        }
                    });
                    
                    // Update form fields
                    console.log('Step 5: Updating form fields');
                    var downloadType = document.getElementById('downloadType');
                    var downloadFormat = document.getElementById('downloadFormat');
                    var downloadUrl = document.getElementById('downloadUrl');
                    var downloadForm = document.getElementById('downloadForm');
                    var titleElement = document.getElementById('download-modal-title');
                    var downloadLabelInput = document.getElementById('downloadLabel');
                    
                    if (downloadType) downloadType.value = type || 'table';
                    if (downloadFormat) downloadFormat.value = format || 'pdf';
                    if (downloadUrl) downloadUrl.value = url || '';
                    if (downloadForm) {
                        downloadForm.setAttribute('action', url || '#');
                        downloadForm.setAttribute('method', 'GET');
                    }
                    if (downloadLabelInput) {
                        downloadLabelInput.value = (label || '').trim();
                    }
                    
                    if (titleElement) {
                        if (type === 'table') {
                            titleElement.textContent = format === 'excel' ? 'Download Data Agregat (Excel)' : 'Download Data Agregat (PDF)';
                        } else if (type === 'chart') {
                            titleElement.textContent = 'Download Grafik Data (PDF)';
                        } else if (type === 'compare') {
                            titleElement.textContent = 'Download Perbandingan Data (PDF)';
                        } else {
                            titleElement.textContent = 'Download Data Agregat';
                        }
                    }
                    
                    // Prevent body scroll
                    console.log('Step 6: Preventing body scroll');
                    document.body.style.overflow = 'hidden';
                    document.documentElement.style.overflow = 'hidden';
                    
                    // Force reflow
                    console.log('Step 7: Forcing reflow');
                    void modalElement.offsetHeight;
                    if (modalPanel) void modalPanel.offsetHeight;
                    
                    // Verify modal and children
                    var computed = window.getComputedStyle(modalElement);
                    console.log('=== MODAL STATE AFTER SHOW ===');
                    console.log('Modal computed display:', computed.display);
                    console.log('Modal computed visibility:', computed.visibility);
                    console.log('Modal computed opacity:', computed.opacity);
                    console.log('Modal computed z-index:', computed.zIndex);
                    console.log('Modal computed position:', computed.position);
                    console.log('Modal computed top:', computed.top);
                    console.log('Modal computed left:', computed.left);
                    console.log('Modal computed width:', computed.width);
                    console.log('Modal computed height:', computed.height);
                    console.log('Has hidden class:', modalElement.classList.contains('hidden'));
                    console.log('Inline display:', modalElement.style.display);
                    
                    if (modalPanel) {
                        var panelComputed = window.getComputedStyle(modalPanel);
                        console.log('=== MODAL PANEL STATE ===');
                        console.log('Panel computed display:', panelComputed.display);
                        console.log('Panel computed visibility:', panelComputed.visibility);
                        console.log('Panel computed opacity:', panelComputed.opacity);
                        console.log('Panel computed z-index:', panelComputed.zIndex);
                    }
                    
                    if (overlay) {
                        var overlayComputed = window.getComputedStyle(overlay);
                        console.log('=== OVERLAY STATE ===');
                        console.log('Overlay computed display:', overlayComputed.display);
                        console.log('Overlay computed opacity:', overlayComputed.opacity);
                        console.log('Overlay computed z-index:', overlayComputed.zIndex);
                    }
                    
                    // Double check after a moment
                    setTimeout(function() {
                        var finalComputed = window.getComputedStyle(modalElement);
                        console.log('=== AFTER 50ms TIMEOUT ===');
                        console.log('Final computed display:', finalComputed.display);
                        console.log('Modal in viewport?', modalElement.getBoundingClientRect());
                        
                        if (finalComputed.display === 'none' || modalElement.classList.contains('hidden')) {
                            console.error('MODAL STILL HIDDEN! Forcing again...');
                            modalElement.classList.remove('hidden');
                            modalElement.style.setProperty('display', 'flex', 'important');
                            modalElement.style.setProperty('visibility', 'visible', 'important');
                            modalElement.style.setProperty('opacity', '1', 'important');
                            modalElement.style.setProperty('z-index', '99999', 'important');
                        } else {
                            console.log('✅ MODAL SHOULD BE VISIBLE!');
                            console.log('Modal bounding rect:', JSON.stringify(modalElement.getBoundingClientRect()));
                            console.log('If modal is not visible, check:');
                            console.log('1. Is it behind another element?');
                            console.log('2. Is it outside viewport?');
                            console.log('3. Are child elements hidden?');
                        }
                    }, 50);
                    
                    return true;
                } catch (err) {
                    console.error('Error in showModal:', err);
                    console.error('Error stack:', err.stack);
                    return false;
                }
            }
            
            // Main logic
            try {
                // Wait for DOM if not ready
                if (document.readyState === 'loading') {
                    console.log('DOM still loading, waiting 50ms...');
                    setTimeout(function() {
                        window.openDownloadModal(type, format, url, label);
                    }, 50);
                    return false;
                }
                
                console.log('DOM ready, searching for modal...');
                
                // Try to find modal - with retry logic
                var modal = document.getElementById('downloadModal');
                var retryCount = 0;
                var maxRetries = 10;
                
                while (!modal && retryCount < maxRetries) {
                    console.log('Modal not found, retry', retryCount + 1, 'of', maxRetries);
                    // Wait a bit and try again
                    if (retryCount > 0) {
                        // This shouldn't happen in sync, but just in case
                        break;
                    }
                    modal = document.getElementById('downloadModal');
                    retryCount++;
                }
                
                if (!modal) {
                    console.error('❌ Download modal NOT FOUND after', maxRetries, 'attempts');
                    console.error('Searching for elements with "download" in ID...');
                    var allDownloadElements = document.querySelectorAll('[id*="download"]');
                    console.error('Found elements:', allDownloadElements.length);
                    allDownloadElements.forEach(function(el, idx) {
                        console.error('Element', idx + ':', el.id, el.tagName, el.className);
                    });
                    
                    // Try to wait a bit more and show modal
                    console.log('Waiting 200ms and trying again...');
                    setTimeout(function() {
                        var retryModal = document.getElementById('downloadModal');
                        if (retryModal) {
                            console.log('✅ Modal found on retry!');
                            showModal(retryModal);
                        } else {
                            console.error('❌ Modal still not found after retry');
                            alert('Modal download tidak ditemukan. Silakan refresh halaman.');
                        }
                    }, 200);
                    return false;
                }
                
                console.log('✅ Modal found!', modal);
                console.log('Modal tag:', modal.tagName);
                console.log('Modal ID:', modal.id);
                console.log('Modal classes:', modal.className);
                
                // Show the modal
                var success = showModal(modal);
                
                if (!success) {
                    console.error('Failed to show modal');
                    alert('Gagal menampilkan modal download. Silakan refresh halaman.');
                }
                
                return false;
            } catch (error) {
                console.error('❌ ERROR in openDownloadModal:', error);
                console.error('Error name:', error.name);
                console.error('Error message:', error.message);
                console.error('Error stack:', error.stack);
                alert('Terjadi kesalahan: ' + error.message);
                return false;
            }
        };
        
        // ULTRA AGGRESSIVE event handling - use mousedown to catch BEFORE click
        function getDownloadLabelFromButton(btn) {
            if (!btn) {
                return '';
            }
            var labelAttr = btn.getAttribute('data-download-label');
            if (labelAttr && labelAttr.trim().length) {
                return labelAttr.trim();
            }
            var ariaLabel = btn.getAttribute('aria-label');
            if (ariaLabel && ariaLabel.trim().length) {
                return ariaLabel.trim();
            }
            return (btn.textContent || '').trim();
        }
        
        function handleDownloadEvent(e) {
            var target = e.target;
            var downloadBtn = null;
            
            // Walk up DOM tree to find download button
            while (target && target !== document && target !== document.documentElement && target !== document.body) {
                if (target.classList && target.classList.contains('js-download-btn')) {
                    downloadBtn = target;
                    break;
                }
                target = target.parentNode || target.parentElement;
            }
            
            if (downloadBtn) {
                console.log('Download button clicked - preventing navigation', downloadBtn);
                
                // STOP EVERYTHING IMMEDIATELY
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                // Cancel for all browsers
                if (e.cancelBubble !== undefined) e.cancelBubble = true;
                if (e.returnValue !== undefined) e.returnValue = false;
                
                // Get data
                var type = downloadBtn.getAttribute('data-download-type');
                var format = downloadBtn.getAttribute('data-download-format');
                var url = downloadBtn.getAttribute('data-download-url');
                var label = getDownloadLabelFromButton(downloadBtn);
                
                console.log('Download button data:', {type: type, format: format, url: url});
                
                if (type && format && url) {
                    // Open modal immediately - no delay
                    if (typeof window.openDownloadModal === 'function') {
                        console.log('Calling openDownloadModal');
                        window.openDownloadModal(type, format, url, label);
                    } else {
                        console.error('openDownloadModal not available');
                        alert('Fungsi download belum tersedia. Silakan refresh halaman.');
                    }
                } else {
                    console.error('Missing download button attributes');
                }
                
                return false;
            }
        }
        
        // Register on MOUSEDOWN (fires BEFORE click) with capture phase
        // This is the earliest we can catch the event
        if (document.addEventListener) {
            document.addEventListener('mousedown', handleDownloadEvent, true);
            document.addEventListener('click', handleDownloadEvent, true);
            document.addEventListener('touchstart', handleDownloadEvent, true);
            // Also on window
            window.addEventListener('mousedown', handleDownloadEvent, true);
            window.addEventListener('click', handleDownloadEvent, true);
            
            console.log('Download event listeners registered');
        }
        
        // Setup direct handlers when DOM is ready
        // Make function global so it can be called from other scripts
        function attachDirectHandlers() {
            var buttons = document.querySelectorAll('.js-download-btn');
            console.log('Found download buttons:', buttons.length);
            
            buttons.forEach(function(btn) {
                // Skip if already has our custom handler
                if (btn.hasAttribute('data-handler-attached')) {
                    return;
                }
                
                // Mark as handled
                btn.setAttribute('data-handler-attached', 'true');
                
                // Add handler that prevents everything
                function handler(e) {
                    console.log('Direct handler called for button', btn, 'Event type:', e.type);
                    
                    // Check if it's a keyboard event (Enter or Space)
                    if (e.type === 'keydown') {
                        if (e.key !== 'Enter' && e.key !== ' ') {
                            return;
                        }
                    }
                    
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    
                    var type = btn.getAttribute('data-download-type');
                    var format = btn.getAttribute('data-download-format');
                    var url = btn.getAttribute('data-download-url');
                    var label = getDownloadLabelFromButton(btn);
                    
                    console.log('Direct handler data:', {type: type, format: format, url: url});
                    
                    if (type && format && url && typeof window.openDownloadModal === 'function') {
                        console.log('Calling openDownloadModal from direct handler');
                        window.openDownloadModal(type, format, url, label);
                    } else {
                        console.error('Cannot open modal:', {
                            hasType: !!type,
                            hasFormat: !!format,
                            hasUrl: !!url,
                            hasFunction: typeof window.openDownloadModal === 'function'
                        });
                    }
                    
                    return false;
                }
                
                // Attach to multiple events including keyboard
                ['mousedown', 'click', 'touchstart', 'keydown'].forEach(function(eventType) {
                    btn.addEventListener(eventType, handler, true);
                });
                
                // Make span focusable and handle keyboard
                btn.setAttribute('tabindex', '0');
                btn.style.cursor = 'pointer';
                
                console.log('Direct handlers attached to button:', btn);
            });
        }
        
        // Make function global
        window.attachDirectHandlers = attachDirectHandlers;
        
        // Run immediately and also on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM Content Loaded - attaching handlers');
                attachDirectHandlers();
            });
        } else {
            console.log('DOM already loaded - attaching handlers immediately');
            attachDirectHandlers();
        }
        
        // Also run after a short delay to catch dynamically added buttons
        setTimeout(function() {
            console.log('Timeout 100ms - re-attaching handlers');
            attachDirectHandlers();
        }, 100);
        setTimeout(function() {
            console.log('Timeout 500ms - re-attaching handlers');
            attachDirectHandlers();
        }, 500);
    })();
    </script>
    
    <style>
        /* Icon color styling untuk sidebar navigation */
        .sidebar-nav-icon {
            filter: brightness(0) saturate(100%);
            opacity: 0.7;
            transition: all 0.2s ease;
        }
        
        /* Icon hijau saat hover */
        .sidebar-nav-link:hover .sidebar-nav-icon {
            filter: brightness(0) saturate(100%) invert(27%) sepia(95%) saturate(1352%) hue-rotate(120deg) brightness(0.4);
            opacity: 1;
        }
        
        /* Icon putih saat active (dipilih) */
        .sidebar-nav-link.active .sidebar-nav-icon {
            filter: brightness(0) invert(1);
            opacity: 1;
        }

        /* Dark mode: semua ikon sidebar jadi putih */
        .dark .sidebar-nav-icon {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }

        .dark .sidebar-nav-link:hover .sidebar-nav-icon,
        .dark .sidebar-nav-link.active .sidebar-nav-icon {
            filter: brightness(0) invert(1);
            opacity: 1;
        }

        .breadcrumb-icon-image {
            display: inline-block;
        }

        .breadcrumb-icon-chart {
            filter: invert(1); /* turn dark background into white, bars into black */
        }

        .breadcrumb-icon-compare {
            filter: brightness(0); /* make white arrows visible on white background */
        }

        .breadcrumb-icon-terms {
            filter: brightness(0);
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-slate-900 antialiased transition-colors duration-300" data-sidebar-state x-data="{ isHelpModalOpen: false }">
    @php
        $breadcrumbIcon = function ($relativePath) {
            $assetUrl = asset($relativePath);
            $fullPath = public_path($relativePath);
            if (file_exists($fullPath)) {
                $assetUrl .= '?v=' . filemtime($fullPath);
            }
            return $assetUrl;
        };

        $compareIconAsset = $breadcrumbIcon('img/compare.png');
        $chartIconAsset = $breadcrumbIcon('img/bar-stats.png');
        $termsIconAsset = $breadcrumbIcon('img/terms.png');
    @endphp
    <!-- Sidebar Desktop -->
    <aside 
        id="desktop-sidebar"
        class="fixed top-0 left-0 z-40 h-screen transition-all duration-300 ease-in-out bg-white dark:bg-slate-800 border-r border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden flex-col hidden lg:flex w-20 hover:w-64 group"
        data-sidebar
    >
        <!-- Brand Section -->
        <div class="flex items-center border-b border-gray-200 dark:border-slate-700 px-4 h-16" data-sidebar-brand>
            <div class="flex items-center gap-3 min-w-0 flex-1 pl-2" data-sidebar-brand-content>
                <img 
                    src="{{ asset('img/kabupaten-madiun.png') }}" 
                    alt="Logo" 
                    class="w-8 h-8 flex-shrink-0 object-contain hover:opacity-80 transition-opacity"
                    data-sidebar-logo
                >
                <div class="min-w-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden group-hover:block" data-sidebar-text>
                    <div class="font-semibold text-gray-900 dark:text-white truncate">Serdadu</div>
                    <div class="text-xs text-gray-500 dark:text-slate-400 truncate">Sistem Rekap Data Terpadu</div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto overflow-x-hidden px-4 py-4 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:'none'] [scrollbar-width:'none']" data-sidebar-nav>
            <div class="space-y-1">
                <a 
                    href="{{ route('admin.landing') }}"
                    class="sidebar-nav-link flex items-center gap-5 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.landing') ? 'bg-[#009B4D] text-white active' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}"
                    title="Home"
                    data-sidebar-nav-item
                >
                    <img src="{{ asset('img/home.png') }}" alt="" class="sidebar-nav-icon w-5 h-5 flex-shrink-0">
                    <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden group-hover:inline" data-sidebar-nav-text>Beranda</span>
                </a>
                
                <a 
                    href="{{ route('admin.data') }}"
                    class="sidebar-nav-link flex items-center gap-5 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.data') ? 'bg-[#009B4D] text-white active' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}"
                    title="Tabel"
                    data-sidebar-nav-item
                >
                    <img src="{{ asset('img/table.png') }}" alt="" class="sidebar-nav-icon w-5 h-5 flex-shrink-0">
                    <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden group-hover:inline" data-sidebar-nav-text>Tabel</span>
                </a>
                
                <a 
                    href="{{ route('admin.charts') }}"
                    class="sidebar-nav-link flex items-center gap-5 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.charts') ? 'bg-[#009B4D] text-white active' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}"
                    title="Grafik"
                    data-sidebar-nav-item
                >
                    <img src="{{ $chartIconAsset }}" alt="" class="sidebar-nav-icon w-5 h-5 flex-shrink-0">
                    <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden group-hover:inline" data-sidebar-nav-text>Grafik</span>
                </a>
                
                <a 
                    href="{{ route('admin.compare') }}"
                    class="sidebar-nav-link flex items-center gap-5 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.compare') ? 'bg-[#009B4D] text-white active' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}"
                    title="Compare"
                    data-sidebar-nav-item
                >
                    <img src="{{ $compareIconAsset }}" alt="" class="sidebar-nav-icon w-5 h-5 flex-shrink-0">
                    <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden group-hover:inline" data-sidebar-nav-text>Perbandingan</span>
                </a>

                <div class="pt-4 pb-2">
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden group-hover:block">Admin</div>
                    <div class="h-px bg-gray-200 mx-3 mb-2 group-hover:hidden"></div>
                </div>

                <a 
                    href="{{ route('admin.import') }}"
                    class="sidebar-nav-link flex items-center gap-5 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.import') ? 'bg-[#009B4D] text-white active' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}"
                    title="Import Data"
                    data-sidebar-nav-item
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden group-hover:inline" data-sidebar-nav-text>Import Data</span>
                </a>

                <a 
                    href="{{ route('admin.download-logs') }}"
                    class="sidebar-nav-link flex items-center gap-5 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.download-logs') ? 'bg-[#009B4D] text-white active' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}"
                    title="User Download"
                    data-sidebar-nav-item
                >
                    <div class="relative flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <!-- Notification Badge -->
                        <span 
                            x-data="{ count: {{ $unseenDownloadLogsCount ?? 0 }} }"
                            x-init="
                                setInterval(() => {
                                    fetch('{{ route('admin.download-logs.count') }}')
                                        .then(res => res.json())
                                        .then(data => count = data.count)
                                        .catch(err => console.error(err));
                                }, 5000);
                            "
                            x-show="count > 0"
                            x-text="count"
                            class="absolute -top-2 -right-2 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold leading-none text-white bg-blue-600 rounded-full border-2 border-white dark:border-slate-800"
                            style="display: none;"
                        >
                            {{ $unseenDownloadLogsCount ?? 0 }}
                        </span>
                    </div>
                    <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden group-hover:inline" data-sidebar-nav-text>
                        User Download
                    </span>
                </a>

                <a 
                    href="{{ route('admin.account') }}"
                    class="sidebar-nav-link flex items-center gap-5 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.account') ? 'bg-[#009B4D] text-white active' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}"
                    title="Akun"
                    data-sidebar-nav-item
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden group-hover:inline" data-sidebar-nav-text>Akun</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button 
                        type="submit" 
                        class="w-full sidebar-nav-link flex items-center gap-5 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300"
                        title="Keluar"
                        data-sidebar-nav-item
                    >
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden group-hover:inline" data-sidebar-nav-text>Keluar</span>
                    </button>
                </form>
            </div>
        </nav>

        <!-- Help Menu -->
        <div class="border-t border-gray-200 px-4 py-4">
            <a 
                href="#"
                @click.prevent="isHelpModalOpen = true"
                class="sidebar-nav-link flex items-center gap-5 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700"
                title="Bantuan"
                data-sidebar-nav-item
            >
                <img src="{{ asset('img/help.png') }}" alt="" class="sidebar-nav-icon w-5 h-5 flex-shrink-0">
                <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden group-hover:inline" data-sidebar-nav-text>Bantuan</span>
            </a>
        </div>


</aside>

    <!-- Mobile Header -->
    <header class="lg:hidden fixed top-0 left-0 right-0 z-50 h-16 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 shadow-sm">
        <div class="flex items-center justify-between h-full px-4">
            <div class="flex items-center gap-3">
                <img 
                    src="{{ asset('img/kabupaten-madiun.png') }}" 
                    alt="Logo" 
                    class="w-10 h-10 object-contain"
                >
                <div>
                    <div class="font-semibold text-gray-900 dark:text-white text-sm">Serdadu</div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Sistem Rekap Data Terpadu</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    type="button"
                    class="theme-toggle-btn p-2 rounded-lg transition-colors text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700"
                    data-theme-toggle
                    aria-label="Toggle theme"
                >
                    <svg class="icon-sun w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364l-1.414 1.414M7.05 16.95l-1.414 1.414m12.728 0l-1.414-1.414M7.05 7.05 5.636 5.636M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                    </svg>
                    <svg class="icon-moon w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                    </svg>
                </button>
                <button 
                    id="mobile-menu-toggle"
                    class="p-2 text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                    aria-label="Toggle menu"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Sidebar Overlay -->
    <div 
        id="mobile-sidebar-overlay"
        class="lg:hidden fixed inset-0 z-30 bg-black bg-opacity-50 hidden transition-opacity duration-300"
    ></div>

    <!-- Mobile Sidebar -->
    <aside 
        id="mobile-sidebar"
        class="lg:hidden fixed top-0 left-0 z-40 h-full w-64 bg-white dark:bg-slate-800 shadow-xl transform -translate-x-full transition-transform duration-300 flex flex-col"
    >
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-slate-700 flex-shrink-0">
            <div class="flex items-center gap-3">
                <img src="{{ asset('img/kabupaten-madiun.png') }}" alt="Logo" class="w-10 h-10 object-contain">
                <div>
                    <div class="font-semibold text-gray-900 dark:text-white text-sm">Serdadu</div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Sistem Rekap Data Terpadu</div>
                </div>
            </div>
            <button 
                id="mobile-menu-close"
                class="p-2 text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                aria-label="Close menu"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
            <a 
                href="{{ route('admin.landing') }}"
                class="mobile-menu-link sidebar-nav-link flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.landing') ? 'bg-[#009B4D] text-white active' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}"
            >
                <img src="{{ asset('img/home.png') }}" alt="" class="sidebar-nav-icon w-5 h-5 flex-shrink-0">
                <span>Beranda</span>
            </a>
            
            <a 
                href="{{ route('admin.data') }}"
                class="mobile-menu-link sidebar-nav-link flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.data') ? 'bg-[#009B4D] text-white active' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}"
            >
                <img src="{{ asset('img/table.png') }}" alt="" class="sidebar-nav-icon w-5 h-5 flex-shrink-0">
                <span>Tabel</span>
            </a>
            
            <a 
                href="{{ route('admin.charts') }}"
                class="mobile-menu-link sidebar-nav-link flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.charts') ? 'bg-[#009B4D] text-white active' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}"
            >
                <img src="{{ $chartIconAsset }}" alt="" class="sidebar-nav-icon w-5 h-5 flex-shrink-0">
                <span>Grafik</span>
            </a>
            
            <a 
                href="{{ route('admin.compare') }}"
                class="mobile-menu-link sidebar-nav-link flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.compare') ? 'bg-[#009B4D] text-white active' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}"
            >
                <img src="{{ $compareIconAsset }}" alt="" class="sidebar-nav-icon w-5 h-5 flex-shrink-0">
                <span>Perbandingan</span>
            </a>

            <div class="pt-4 pb-2">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-1">Admin</div>
            </div>

            <a 
                href="{{ route('admin.import') }}"
                class="mobile-menu-link sidebar-nav-link flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.import') ? 'bg-[#009B4D] text-white active' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}"
            >
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <span>Import Data</span>
            </a>

            <a 
                href="{{ route('admin.download-logs') }}"
                class="mobile-menu-link sidebar-nav-link flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.download-logs') ? 'bg-[#009B4D] text-white active' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}"
            >
                <div class="relative flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <!-- Notification Badge -->
                    <span 
                        x-data="{ count: {{ $unseenDownloadLogsCount ?? 0 }} }"
                        x-init="
                            setInterval(() => {
                                fetch('{{ route('admin.download-logs.count') }}')
                                    .then(res => res.json())
                                    .then(data => count = data.count)
                                    .catch(err => console.error(err));
                            }, 5000);
                        "
                        x-show="count > 0"
                        x-text="count"
                        class="absolute -top-2 -right-2 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold leading-none text-white bg-green-600 rounded-full border-2 border-white dark:border-slate-800"
                        style="display: none;"
                    >
                        {{ $unseenDownloadLogsCount ?? 0 }}
                    </span>
                </div>
                <div class="flex items-center justify-between w-full">
                    <span>User Download</span>
                </div>
            </a>

            <a 
                href="{{ route('admin.account') }}"
                class="mobile-menu-link sidebar-nav-link flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.account') ? 'bg-[#009B4D] text-white active' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}"
            >
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span>Akun</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button 
                    type="submit" 
                    class="w-full mobile-menu-link sidebar-nav-link flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </nav>
        
        <!-- Help Menu -->
        <div class="border-t border-gray-200 px-4 py-4 mt-auto">
            <a 
                href="#"
                @click.prevent="isHelpModalOpen = true"
                class="mobile-menu-link sidebar-nav-link flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700"
            >
                <img src="{{ asset('img/help.png') }}" alt="" class="sidebar-nav-icon w-5 h-5 flex-shrink-0">
                <span>Bantuan</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main 
        id="main-content"
        class="transition-all duration-300 ease-in-out min-h-screen lg:pt-0 pt-16 lg:ml-20"
        style="will-change: margin-left;"
    >
        <!-- Breadcrumb Navigation -->
        <div class="border-b border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 lg:px-6 py-3">
            <div class="flex items-center justify-between gap-4">
            <nav class="flex items-center gap-2 text-sm" aria-label="Breadcrumb">
                @php
                    $breadcrumbs = [];
                    
                    // Home/Beranda
                    if (request()->routeIs('admin.landing')) {
                        $breadcrumbs[] = [
                            'label' => 'Beranda',
                            'route' => 'admin.landing',
                            'icon' => 'home',
                            'active' => true
                        ];
                    } else {
                        $breadcrumbs[] = [
                            'label' => 'Beranda',
                            'route' => 'admin.landing',
                            'icon' => 'home',
                            'active' => false
                        ];
                        
                        // Tabel/Data
                        if (request()->routeIs('admin.data') || request()->routeIs('admin.data.fullscreen')) {
                            $breadcrumbs[] = [
                                'label' => 'Tabel',
                                'route' => 'admin.data',
                                'icon' => 'table',
                                'active' => false
                            ];
                            
                            // Cek apakah tahun dan semester sudah dipilih (data sudah ditampilkan)
                            $yearInput = request()->query('year');
                            $semesterInput = request()->query('semester');
                            $hasYear = !empty($yearInput) && $yearInput !== '';
                            $hasSemester = !empty($semesterInput) && $semesterInput !== '';
                            $hasData = $hasYear && $hasSemester;
                            
                            // Tambahkan kategori tab aktif hanya jika data sudah dipilih
                            if ($hasData) {
                                $category = request()->query('category', 'gender');
                                $categoryLabels = [
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
                                $categoryLabel = $categoryLabels[$category] ?? 'Jenis Kelamin';
                                
                                if (request()->routeIs('admin.data.fullscreen')) {
                                    $breadcrumbs[] = [
                                        'label' => $categoryLabel,
                                        'route' => null,
                                        'icon' => 'table',
                                        'active' => false
                                    ];
                                    $breadcrumbs[] = [
                                        'label' => 'Fullscreen',
                                        'route' => null,
                                        'icon' => 'maximize',
                                        'active' => true
                                    ];
                                } else {
                                    $breadcrumbs[] = [
                                        'label' => $categoryLabel,
                                        'route' => null,
                                        'icon' => 'table',
                                        'active' => true
                                    ];
                                }
                            } else {
                                // Jika data belum dipilih, Tabel menjadi active
                                $breadcrumbs[count($breadcrumbs) - 1]['active'] = true;
                            }
                        }
                        
                        // Grafik
                        if (request()->routeIs('admin.charts')) {
                            $breadcrumbs[] = [
                                'label' => 'Grafik',
                                'route' => 'admin.charts',
                                'icon' => 'chart',
                                'active' => false
                            ];
                            
                            // Cek apakah tahun dan semester sudah dipilih (data sudah ditampilkan)
                            $yearInput = request()->query('year');
                            $semesterInput = request()->query('semester');
                            $hasYear = !empty($yearInput) && $yearInput !== '';
                            $hasSemester = !empty($semesterInput) && $semesterInput !== '';
                            $hasData = $hasYear && $hasSemester;
                            
                            // Tambahkan kategori tab aktif hanya jika data sudah dipilih
                            if ($hasData) {
                                $category = request()->query('category', 'gender');
                                $categoryLabels = [
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
                                $categoryLabel = $categoryLabels[$category] ?? 'Jenis Kelamin';
                                
                                $breadcrumbs[] = [
                                    'label' => $categoryLabel,
                                    'route' => null,
                                    'icon' => 'chart',
                                    'active' => true
                                ];
                            } else {
                                // Jika data belum dipilih, Grafik menjadi active
                                $breadcrumbs[count($breadcrumbs) - 1]['active'] = true;
                            }
                        }
                        
                        // Perbandingan/Compare
                        if (request()->routeIs('admin.compare')) {
                            $breadcrumbs[] = [
                                'label' => 'Perbandingan',
                                'route' => 'admin.compare',
                                'icon' => 'compare',
                                'active' => false
                            ];
                            
                            // Cek apakah primary year, primary semester, compare year, dan compare semester sudah dipilih
                            $primaryYear = request()->query('year');
                            $primarySemester = request()->query('semester');
                            $compareYear = request()->query('compare_year');
                            $compareSemester = request()->query('compare_semester');
                            $hasPrimaryYear = !empty($primaryYear) && $primaryYear !== '';
                            $hasPrimarySemester = !empty($primarySemester) && $primarySemester !== '';
                            $hasCompareYear = !empty($compareYear) && $compareYear !== '';
                            $hasCompareSemester = !empty($compareSemester) && $compareSemester !== '';
                            $hasData = $hasPrimaryYear && $hasPrimarySemester && $hasCompareYear && $hasCompareSemester;
                            
                            // Tambahkan kategori tab aktif hanya jika data sudah dipilih
                            if ($hasData) {
                                $category = request()->query('category', 'gender');
                                $categoryLabels = [
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
                                $categoryLabel = $categoryLabels[$category] ?? 'Jenis Kelamin';
                                
                                $breadcrumbs[] = [
                                    'label' => $categoryLabel,
                                    'route' => null,
                                    'icon' => 'compare',
                                    'active' => true
                                ];
                            } else {
                                // Jika data belum dipilih, Perbandingan menjadi active
                                $breadcrumbs[count($breadcrumbs) - 1]['active'] = true;
                            }
                        }

                        // Import Data
                        if (request()->routeIs('admin.import')) {
                            $breadcrumbs[] = [
                                'label' => 'Import Data',
                                'route' => 'admin.import',
                                'icon' => 'table',
                                'active' => true
                            ];
                        }

                        // User Download
                        if (request()->routeIs('admin.download-logs')) {
                            $breadcrumbs[] = [
                                'label' => 'User Download',
                                'route' => 'admin.download-logs',
                                'icon' => 'table',
                                'active' => true
                            ];
                        }

                        // Akun
                        if (request()->routeIs('admin.account')) {
                            $breadcrumbs[] = [
                                'label' => 'Akun',
                                'route' => 'admin.account',
                                'icon' => 'home', // Using home icon for account for now, or could use table
                                'active' => true
                            ];
                        }
                    }
                @endphp
                
                @php $canGoBack = count($breadcrumbs) > 1; @endphp
                <button 
                    @if($canGoBack) onclick="window.history.back()" @else disabled aria-disabled="true" @endif
                    class="flex items-center justify-center w-7 h-7 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-[#009B4D] focus:ring-offset-2 {{ $canGoBack ? 'text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-700' : 'text-gray-300 dark:text-slate-600 cursor-default' }}"
                    title="Kembali"
                    aria-label="Kembali"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <span class="text-gray-300">|</span>
                
                <ol class="flex items-center gap-2">
                    @foreach($breadcrumbs as $index => $breadcrumb)
                        <li class="flex items-center gap-2">
                            @if($index > 0)
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            @endif
                            
                            @if($breadcrumb['route'] && !$breadcrumb['active'])
                                <a 
                                    href="{{ route($breadcrumb['route']) }}" 
                                    class="flex items-center gap-1.5 text-gray-600 dark:text-slate-400 text-sm hover:text-gray-900 dark:hover:text-white transition-colors {{ (isset($categoryLabel) && $breadcrumb['label'] === $categoryLabel) ? 'breadcrumb-category' : '' }}"
                                >
                                    @if($breadcrumb['icon'] === 'home')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                        </svg>
                                    @elseif($breadcrumb['icon'] === 'table')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    @elseif($breadcrumb['icon'] === 'chart')
                                        <img src="{{ $chartIconAsset }}" alt="Ikon Grafik" class="w-4 h-4 object-contain breadcrumb-icon-image breadcrumb-icon-chart">
                                    @elseif($breadcrumb['icon'] === 'compare')
                                        <img src="{{ $compareIconAsset }}" alt="Ikon Perbandingan" class="w-4 h-4 object-contain breadcrumb-icon-image breadcrumb-icon-compare">
                                    @elseif($breadcrumb['icon'] === 'terms')
                                        <img src="{{ $termsIconAsset }}" alt="Ikon Syarat & Ketentuan" class="w-4 h-4 object-contain breadcrumb-icon-image breadcrumb-icon-terms">
                                    @elseif($breadcrumb['icon'] === 'maximize')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                        </svg>
                                    @endif
                                    <span>{{ $breadcrumb['label'] }}</span>
                                </a>
                            @else
                                <span class="flex items-center gap-1.5 text-gray-900 dark:text-white text-sm font-medium {{ isset($categoryLabel) && $breadcrumb['label'] === $categoryLabel ? 'breadcrumb-category' : '' }}">
                                    @if($breadcrumb['icon'] === 'home')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                        </svg>
                                    @elseif($breadcrumb['icon'] === 'table')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    @elseif($breadcrumb['icon'] === 'chart')
                                        <img src="{{ $chartIconAsset }}" alt="Ikon Grafik" class="w-4 h-4 object-contain breadcrumb-icon-image breadcrumb-icon-chart">
                                    @elseif($breadcrumb['icon'] === 'compare')
                                        <img src="{{ $compareIconAsset }}" alt="Ikon Perbandingan" class="w-4 h-4 object-contain breadcrumb-icon-image breadcrumb-icon-compare">
                                    @elseif($breadcrumb['icon'] === 'terms')
                                        <img src="{{ $termsIconAsset }}" alt="Ikon Syarat & Ketentuan" class="w-4 h-4 object-contain breadcrumb-icon-image breadcrumb-icon-terms">
                                    @elseif($breadcrumb['icon'] === 'maximize')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                        </svg>
                                    @endif
                                    <span class="breadcrumb-category-text">{{ $breadcrumb['label'] }}</span>
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
            <button 
                type="button"
                class="theme-toggle-btn hidden lg:inline-flex items-center gap-2 px-3 py-2 rounded-lg transition-colors text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700"
                data-theme-toggle
                aria-label="Toggle theme"
            >
                <svg class="icon-sun w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364l-1.414 1.414M7.05 16.95l-1.414 1.414m12.728 0l-1.414-1.414M7.05 7.05 5.636 5.636M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                </svg>
                <svg class="icon-moon w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
                <span class="hidden sm:inline text-sm font-medium">Mode</span>
            </button>
            </div>
        </div>
        
        <div class="p-4 lg:p-6 max-w-full">
            @yield('content')
        </div>
    </main>
    
    <!-- Website Footer -->
    <footer id="website-footer" class="bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 p-3 lg:p-4 lg:ml-20 transition-all duration-300 ease-in-out">
        <div class="flex flex-col gap-3 text-center sm:flex-row sm:items-center sm:justify-between sm:text-left">
            <div class="text-xs text-gray-500 whitespace-normal sm:whitespace-nowrap">
                Copyright © 2025 
                <a href="{{ url('/') }}" class="text-[#009B4D] hover:underline" target="_blank" rel="noopener">Serdadu</a>
                <a href="https://dukcapil.madiunkab.go.id" class="text-[#009B4D] hover:underline" target="_blank" rel="noopener">Dukcapil Kab. Madiun</a>
                <span class="mx-2">|</span>
                <a href="{{ route('public.terms') }}" class="text-[#009B4D] hover:underline">Syarat & Ketentuan</a>
            </div>
            <div class="flex items-center justify-center gap-2 sm:justify-end">
                <a href="https://www.youtube.com/@dukcapilkabupatenmadiun" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-red-600 transition-colors" aria-label="YouTube" title="YouTube">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                </a>
                <a href="https://www.instagram.com/dukcapil.kabupatenmadiun/" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-pink-600 transition-colors" aria-label="Instagram" title="Instagram">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
                <a href="https://www.facebook.com/people/Dukcapil-Kabupaten-Madiun/pfbid0FrM8jLzQi6zH5TC1nsCn95UHWCA3mWbr94B7x7zF73dpLZhPhNtWDcYSgdNcViXLl/?mibextid=LQQJ4d" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-blue-600 transition-colors" aria-label="Facebook" title="Facebook">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                <a href="https://x.com/Capil_Kab_Mdn" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-gray-900 transition-colors" aria-label="X (Twitter)" title="X (Twitter)">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                </a>
            </div>
        </div>
    </footer>

    <!-- Help Modal -->
    <div
        x-show="isHelpModalOpen"
        style="display: none;"
        x-on:keydown.escape.window="isHelpModalOpen = false"
        class="fixed inset-0 z-[9999]"
        aria-labelledby="help-modal-title"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div
            x-show="isHelpModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500/75 transition-opacity"
            @click="isHelpModalOpen = false"
        ></div>

        <!-- Modal Container -->
        <div class="fixed inset-0 z-[99999] flex min-h-full w-full h-full items-center justify-center px-4 py-8 pointer-events-none">
            <!-- Modal Panel -->
            <div
                x-show="isHelpModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="pointer-events-auto mx-auto inline-block w-full max-w-xl transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 shadow-xl transition-all"
            >
                <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="help-modal-title">Pusat Bantuan</h3>
                        <button @click="isHelpModalOpen = false" type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Tabs -->
                    <div x-data="{ activeTab: 'faq' }" class="w-full">
                        <div class="border-b border-gray-200 mb-4">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button 
                                    @click="activeTab = 'faq'"
                                    :class="activeTab === 'faq' ? 'border-[#009B4D] text-[#009B4D]' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                    class="whitespace-nowrap border-b-2 py-2 px-1 text-sm font-medium transition-colors"
                                >
                                    FAQ (Tanya Jawab)
                                </button>
                                <button 
                                    @click="activeTab = 'contact'"
                                    :class="activeTab === 'contact' ? 'border-[#009B4D] text-[#009B4D]' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                    class="whitespace-nowrap border-b-2 py-2 px-1 text-sm font-medium transition-colors"
                                >
                                    Kontak Kami
                                </button>
                            </nav>
                        </div>

                        <!-- FAQ Content -->
                        <div x-show="activeTab === 'faq'" class="space-y-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                            <!-- FAQ Item 1 -->
                            <div x-data="{ expanded: false }" class="border border-gray-200 rounded-lg overflow-hidden">
                                <button @click="expanded = !expanded" class="flex w-full items-center justify-between bg-gray-50 dark:bg-slate-700 px-4 py-3 text-left text-sm font-medium text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
                                    <span>Bagaimana cara mengunduh data?</span>
                                    <svg :class="expanded ? 'rotate-180' : ''" class="h-5 w-5 text-gray-500 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div x-show="expanded" x-collapse class="px-4 py-3 text-sm text-gray-600 dark:text-slate-300 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-600">
                                    Anda dapat mengunduh data melalui halaman <strong>Tabel</strong>. Pilih filter Tahun dan Semester, lalu klik tombol <strong>Download</strong> di pojok kanan atas tabel (tersedia format Excel dan PDF).
                                </div>
                            </div>

                            <!-- FAQ Item 2 -->
                            <div x-data="{ expanded: false }" class="border border-gray-200 rounded-lg overflow-hidden">
                                <button @click="expanded = !expanded" class="flex w-full items-center justify-between bg-gray-50 dark:bg-slate-700 px-4 py-3 text-left text-sm font-medium text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
                                    <span>Mengapa grafik tidak muncul?</span>
                                    <svg :class="expanded ? 'rotate-180' : ''" class="h-5 w-5 text-gray-500 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div x-show="expanded" x-collapse class="px-4 py-3 text-sm text-gray-600 dark:text-slate-300 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-600">
                                    Pastikan koneksi internet Anda stabil. Jika masih bermasalah, coba muat ulang halaman (refresh) atau bersihkan cache browser Anda.
                                </div>
                            </div>

                            <!-- FAQ Item 3 -->
                            <div x-data="{ expanded: false }" class="border border-gray-200 rounded-lg overflow-hidden">
                                <button @click="expanded = !expanded" class="flex w-full items-center justify-between bg-gray-50 dark:bg-slate-700 px-4 py-3 text-left text-sm font-medium text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
                                    <span>Apakah data ini update?</span>
                                    <svg :class="expanded ? 'rotate-180' : ''" class="h-5 w-5 text-gray-500 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div x-show="expanded" x-collapse class="px-4 py-3 text-sm text-gray-600 dark:text-slate-300 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-600">
                                    Ya, data diperbarui secara berkala setiap semester oleh Dinas Kependudukan dan Pencatatan Sipil Kabupaten Madiun.
                                </div>
                            </div>

                            <!-- FAQ Item 4 -->
                            <div x-data="{ expanded: false }" class="border border-gray-200 rounded-lg overflow-hidden">
                                <button @click="expanded = !expanded" class="flex w-full items-center justify-between bg-gray-50 dark:bg-slate-700 px-4 py-3 text-left text-sm font-medium text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
                                    <span>Bagaimana jika saya menemukan kesalahan data?</span>
                                    <svg :class="expanded ? 'rotate-180' : ''" class="h-5 w-5 text-gray-500 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div x-show="expanded" x-collapse class="px-4 py-3 text-sm text-gray-600 dark:text-slate-300 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-600">
                                    Silakan hubungi kami melalui kontak WhatsApp atau Email yang tersedia di tab "Kontak Kami" dengan menyertakan detail kesalahan yang ditemukan.
                                </div>
                            </div>

                            <!-- FAQ Item 5 -->
                            <div x-data="{ expanded: false }" class="border border-gray-200 rounded-lg overflow-hidden">
                                <button @click="expanded = !expanded" class="flex w-full items-center justify-between bg-gray-50 dark:bg-slate-700 px-4 py-3 text-left text-sm font-medium text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
                                    <span>Apakah layanan ini gratis?</span>
                                    <svg :class="expanded ? 'rotate-180' : ''" class="h-5 w-5 text-gray-500 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div x-show="expanded" x-collapse class="px-4 py-3 text-sm text-gray-600 dark:text-slate-300 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-600">
                                    Ya, seluruh data dan fitur yang tersedia di website Serdadu ini dapat diakses dan diunduh secara gratis oleh publik.
                                </div>
                            </div>
                        </div>

                        <!-- Contact Content -->
                        <div x-show="activeTab === 'contact'" class="space-y-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                            <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                                <h4 class="text-sm font-semibold text-green-800 mb-2">Layanan Pengaduan & Informasi</h4>
                                <p class="text-sm text-green-700 mb-4">
                                    Jika Anda memiliki pertanyaan lebih lanjut atau kendala teknis, silakan hubungi kami melalui saluran berikut:
                                </p>
                                <div class="space-y-3">
                                    <a href="https://wa.me/6285784699144" target="_blank" class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg border border-green-200 dark:border-green-800 hover:shadow-sm transition-shadow group">
                                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">WhatsApp</div>
                                            <div class="text-xs text-gray-500">0857-8469-9144</div>
                                        </div>
                                    </a>
                                    
                                    <a href="mailto:dukcapil@madiunkab.go.id" class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg border border-green-200 dark:border-green-800 hover:shadow-sm transition-shadow group">
                                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">Email</div>
                                            <div class="text-xs text-gray-500">dukcapil@madiunkab.go.id</div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <button 
                            type="button" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-300 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-md hover:bg-gray-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            @click="isHelpModalOpen = false"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
    
    <script>
        (function() {
            'use strict';
            const storageKey = 'theme';
            const root = document.documentElement;

            const updateToggleIcons = () => {
                const isDark = root.classList.contains('dark');
                document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
                    const sun = btn.querySelector('.icon-sun');
                    const moon = btn.querySelector('.icon-moon');
                    if (sun && moon) {
                        sun.classList.toggle('hidden', isDark);
                        moon.classList.toggle('hidden', !isDark);
                    }
                    btn.setAttribute('aria-pressed', String(isDark));
                });
            };

            const applyTheme = (theme) => {
                root.classList.toggle('dark', theme === 'dark');
                root.dataset.theme = theme;
            };

            const setTheme = (theme) => {
                applyTheme(theme);
                try {
                    localStorage.setItem(storageKey, theme);
                } catch (e) {
                    console.warn('Cannot persist theme preference');
                }
                document.dispatchEvent(new Event('theme-changed'));
                updateToggleIcons();
            };

            const toggleTheme = () => {
                const isDark = root.classList.contains('dark');
                const nextTheme = isDark ? 'light' : 'dark';
                setTheme(nextTheme);
                // Reload to ensure all charts and text colors fully re-render in the new theme
                setTimeout(() => {
                    window.location.reload();
                }, 30);
            };

            document.addEventListener('DOMContentLoaded', () => {
                updateToggleIcons();
                document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
                    btn.addEventListener('click', toggleTheme);
                });
            });
        })();
    </script>
    
    <script>
        // Vanilla JavaScript untuk sidebar collapse (CSP-friendly)
        (function() {
            'use strict';
            
            // Mobile Sidebar
            const mobileSidebar = document.getElementById('mobile-sidebar');
            const mobileOverlay = document.getElementById('mobile-sidebar-overlay');
            const mobileToggle = document.getElementById('mobile-menu-toggle');
            const mobileClose = document.getElementById('mobile-menu-close');
            const mobileMenuLinks = document.querySelectorAll('.mobile-menu-link');
            
            // Mobile sidebar functions
            function openMobileSidebar() {
                if (mobileSidebar && mobileOverlay) {
                    mobileSidebar.classList.remove('-translate-x-full');
                    mobileOverlay.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            }
            
            function closeMobileSidebar() {
                if (mobileSidebar && mobileOverlay) {
                    mobileSidebar.classList.add('-translate-x-full');
                    mobileOverlay.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            }
            
            if (mobileToggle) {
                mobileToggle.addEventListener('click', openMobileSidebar);
            }
            
            if (mobileClose) {
                mobileClose.addEventListener('click', closeMobileSidebar);
            }
            
            if (mobileOverlay) {
                mobileOverlay.addEventListener('click', closeMobileSidebar);
            }
            
            mobileMenuLinks.forEach(link => {
                link.addEventListener('click', closeMobileSidebar);
            });
        })();
    </script>
</body>
</html>
