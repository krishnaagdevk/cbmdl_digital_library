    </main>

    <!-- Secure e-Reading PDF Modal -->
    <div id="pdfModal" class="pdf-modal">
        <div class="pdf-modal-content">
            <div class="pdf-modal-header">
                <h3 id="pdfModalTitle">📖 e-Library Interactive Secure Reader</h3>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span id="pdfTimerBadge" class="badge badge-orange" style="font-size:12px; font-weight:600; padding:6px 12px; display:none;">⏱️ 00m 00s remaining</span>
                    
                    <!-- Zoom Controls -->
                    <div style="display:inline-flex; align-items:center; gap:6px; background:#f1f5f9; padding:4px 8px; border-radius:8px; border:1px solid var(--border-color);">
                        <button type="button" class="btn" style="padding:4px 10px; font-size:13px; font-weight:700; background:#ffffff !important; color:#0f172a !important; border:1px solid #cbd5e1 !important; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 1px 2px rgba(0,0,0,0.06);" onclick="zoomPdfModal(-0.15)" title="Zoom Out">
                            <i class="fa-solid fa-magnifying-glass-minus"></i>
                        </button>
                        <span id="pdfModalZoomVal" style="font-size:12px; font-weight:700; color:#0f172a; min-width:42px; text-align:center;">100%</span>
                        <button type="button" class="btn" style="padding:4px 10px; font-size:13px; font-weight:700; background:#ffffff !important; color:#0f172a !important; border:1px solid #cbd5e1 !important; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 1px 2px rgba(0,0,0,0.06);" onclick="zoomPdfModal(0.15)" title="Zoom In">
                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                        </button>
                        <button type="button" class="btn" style="padding:4px 10px; font-size:13px; font-weight:700; background:#ffffff !important; color:#0f172a !important; border:1px solid #cbd5e1 !important; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 1px 2px rgba(0,0,0,0.06);" onclick="resetPdfModalZoom()" title="Reset Zoom">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                    </div>

                    <button id="pdfFullscreenBtn" class="btn" style="padding:6px 12px; font-size:12px; background:var(--primary);" onclick="togglePdfFullscreen()"><i class="fa-solid fa-expand"></i> Fullscreen</button>
                    <button class="pdf-modal-close" onclick="closePdfModal()">&times;</button>
                </div>
            </div>
            <div class="pdf-modal-body">
                <iframe id="pdfFrame" style="pointer-events:auto;" src="about:blank"></iframe>
            </div>
        </div>
    </div>

    <!-- Footer Area -->
    <footer>
        &copy; <?= date('Y') ?> <strong>MCB e-Library</strong> · All Rights Reserved. Designed & Developed by <strong>KD Technologies</strong>
    </footer>

    <!-- Client-Side JavaScript Logic -->
    <script>
        // Global initialization function
        function initializePageFeatures() {
            // 1. Icon Mapper
            const iconMap = {
                'fa-book-bookmark': '📖',
                'fa-book-open': '📖',
                'fa-book': '📚',
                'fa-house-chimney': '🏠',
                'fa-right-from-bracket': '🚪',
                'fa-right-to-bracket': '🚪',
                'fa-user': '👤',
                'fa-user-tie': '👤',
                'fa-user-shield': '🛡️',
                'fa-user-graduate': '🎓',
                'fa-graduation-cap': '🎓',
                'fa-arrow-up-right-from-square': '↗️',
                'fa-external-link': '↗️',
                'fa-lock': '🔒',
                'fa-key': '🔑',
                'fa-chart-pie': '📊',
                'fa-folder-open': '📁',
                'fa-file-pdf': '📄',
                'fa-magnifying-glass': '🔍',
                'fa-receipt': '🧾',
                'fa-print': '🖨️',
                'fa-user-plus': '➕',
                'fa-users': '👥',
                'fa-hand-holding-hand': '🤝',
                'fa-hand-holding': '🤝',
                'fa-clock-rotate-left': '⏳',
                'fa-envelope': '✉️',
                'fa-phone': '📞',
                'fa-chevron-right': '➔',
                'fa-circle-check': '✔️',
                'fa-circle-xmark': '❌',
                'fa-trash-can': '🗑️',
                'fa-circle-exclamation': '⚠️',
                'fa-globe': '🌐',
                'fa-clock': '🕒',
                'fa-square-plus': '➕',
                'fa-clipboard-list': '📋',
                'fa-cloud-arrow-up': '☁️',
                'fa-book-atlas': '📚',
                'fa-eye': '👁️',
                'fa-layer-group': '🗂️',
                'fa-warehouse': '🏠',
                'fa-inbox': '📥',
                'fa-user-tag': '🏷️',
                'fa-hourglass-half': '⏳',
                'fa-circle-minus': '⛔',
                'fa-shield-halved': '🛡️',
                'fa-file-arrow-down': '📥',
                'fa-chart-line': '📈',
                'fa-gauge-high': '📊',
                'fa-user-gear' : '🧑‍🦱',
                'fa-user-master': '👮‍♂️',
                'fa-id-card-clip': '📜',
                'fa-sliders':'🧐',
                'fa-circle-info':'ℹ️',
                'fa-users-rectangle':'🧾',
                'fa-circle-plus':'➕',
                'fa-list-check':'📋',
                'fa-box-archive':'💾',
                'fa-database':'🗃️',
                'fa-hard-drive':'💿',
                'fa-calendar-check':'🔒',
                'fa-rotate-right': '🔄',
                'fa-rotate': '🔄',
                'fa-rotate-left': '🔄',
                'fa-arrows-rotate': '🔄',
                'fa-right-left': '🔀',
                'fa-pen-to-square': '✏️',
                'fa-magnifying-glass-minus': '🔍-',
                'fa-magnifying-glass-plus': '🔍+',
                'fa-expand': '⛶',
                'fa-arrows-alt': '⤢'
            };
            
            document.querySelectorAll('i[class*="fa-"], span[class*="fa-"]').forEach(el => {
                const classes = el.className.split(' ');
                for (let cls of classes) {
                    if (cls.startsWith('fa-') && iconMap[cls]) {
                        const hasNextText = el.nextSibling && el.nextSibling.textContent.trim().length > 0;
                        const marginRight = hasNextText ? '6px' : '0px';
                        el.outerHTML = `<span style="font-style: normal; margin-right: ${marginRight}; display: inline-block;">${iconMap[cls]}</span>`;
                        break;
                    }
                }
            });

            // 2. Mobile Number Input Limiter & Numeric-Only Filter
            const mobileInputs = document.querySelectorAll('input[name="mobile"], input[id="m_mobile"], input[id="member_mob"], input[id="mobile_login"]');
            mobileInputs.forEach(input => {
                input.type = 'tel';
                input.maxLength = 10;
                
                if (input.dataset.limiterInitialized) return;
                input.dataset.limiterInitialized = 'true';
                
                input.addEventListener('keypress', function(e) {
                    if (e.key < '0' || e.key > '9') {
                        e.preventDefault();
                    }
                });

                input.addEventListener('input', function(e) {
                    let cleaned = this.value.replace(/\D/g, '');
                    if (cleaned.length > 10) {
                        cleaned = cleaned.substring(0, 10);
                    }
                    this.value = cleaned;
                });
            });

            // 3. Password Toggle and AI Icon Magic Injection + 15-char Limit
            document.querySelectorAll('input[type="password"]').forEach(input => {
                input.setAttribute('maxlength', '15');
                input.addEventListener('input', function() {
                    if (this.value.length > 15) {
                        this.value = this.value.substring(0, 15);
                    }
                });
                
                if (input.dataset.toggleInitialized) return;
                input.dataset.toggleInitialized = 'true';
                
                const wrapper = document.createElement('div');
                wrapper.className = 'password-wrapper';
                
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);
                input.style.margin = '0';
                input.style.paddingRight = '45px';
                
                const toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.className = 'password-toggle-btn';
                toggleBtn.title = "Toggle Password Visibility";
                toggleBtn.innerHTML = '🫣';
                
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (input.type === 'password') {
                        input.type = 'text';
                        toggleBtn.innerHTML = '😀';
                        toggleBtn.style.color = '#10b981';
                    } else {
                        input.type = 'password';
                        toggleBtn.innerHTML = '🫣';
                        toggleBtn.style.color = '#2563eb';
                    }
                });
                
                wrapper.appendChild(toggleBtn);
            });

            // 4. Live Client-Side Searching / Filtering Utility Register
            registerLiveFilter('catFilterInput', 'categoriesTable');
            registerLiveFilter('ebookFilterInput', 'ebooksTable');
            registerLiveFilter('physFilterInput', 'physicalTable');
            registerLiveFilter('memberFilterInput', 'membersTable');
            registerLiveFilter('viewMembersFilter', 'viewMembersTable');
            registerLiveFilter('lendingFilterInput', 'lendingLogTable');

            // 5. Client-Side Table Pagination (Disabled as tables use server-side SQL pagination)
            const tablesToPaginate = [];
            tablesToPaginate.forEach(id => {
                paginateTable(id, 10);
            });
        }

        function paginateTable(tableId, rowsPerPage = 10) {
            const table = document.getElementById(tableId);
            if (!table) return;
            const tbody = table.querySelector('tbody');
            if (!tbody) return;
            const rows = Array.from(tbody.querySelectorAll('tr'));
            if (rows.length <= rowsPerPage) return;
            
            let pagContainer = table.parentNode.querySelector('.pagination-controls');
            if (pagContainer) pagContainer.remove();
            
            pagContainer = document.createElement('div');
            pagContainer.className = 'pagination-controls';
            pagContainer.style.display = 'flex';
            pagContainer.style.gap = '6px';
            pagContainer.style.justifyContent = 'center';
            pagContainer.style.marginTop = '15px';
            
            const pageCount = Math.ceil(rows.length / rowsPerPage);
            let currentPage = 1;
            
            function showPage(page) {
                currentPage = page;
                rows.forEach((row, idx) => {
                    const start = (page - 1) * rowsPerPage;
                    const end = page * rowsPerPage;
                    if (idx >= start && idx < end) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                Array.from(pagContainer.querySelectorAll('button')).forEach((btn, idx) => {
                    if (idx + 1 === page) {
                        btn.style.background = 'var(--primary)';
                        btn.style.color = 'white';
                    } else {
                        btn.style.background = 'var(--bg-slate)';
                        btn.style.color = 'var(--text-dark)';
                    }
                });
            }
            
            for (let i = 1; i <= pageCount; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.innerText = i;
                btn.style.padding = '5px 10px';
                btn.style.fontSize = '12px';
                btn.style.border = '1px solid var(--border-color)';
                btn.style.borderRadius = '4px';
                btn.style.cursor = 'pointer';
                btn.addEventListener('click', () => showPage(i));
                pagContainer.appendChild(btn);
            }
            
            table.parentNode.appendChild(pagContainer);
            showPage(1);
        }

        // Initialize features on initial DOM load
        document.addEventListener('DOMContentLoaded', function() {
            initializePageFeatures();
        });

        function printElement(elemId) {
            const elem = document.getElementById(elemId);
            if (!elem) return;
            const originalContent = document.body.innerHTML;
            document.body.innerHTML = elem.outerHTML;
            window.print();
            document.body.innerHTML = originalContent;
            window.location.reload();
        }

        // 1. e-Reading PDF Reader New-Tab Launcher
        function openPdfModal(requestId, expiresAtUnix, title, isAdmin = false) {
            let targetUrl = '';
            if (isAdmin) {
                targetUrl = '?action=view_pdf_content&id=' + requestId;
            } else {
                targetUrl = '?action=read_member_pdf&id=' + requestId;
            }
            window.open(targetUrl, '_blank');
        }

        let pdfModalZoom = 1.0;

        function zoomPdfModal(delta) {
            pdfModalZoom = Math.min(3.0, Math.max(0.5, parseFloat((pdfModalZoom + delta).toFixed(2))));
            const zoomVal = document.getElementById('pdfModalZoomVal');
            if (zoomVal) zoomVal.textContent = Math.round(pdfModalZoom * 100) + '%';
            
            const frame = document.getElementById('pdfFrame');
            if (frame) {
                frame.style.transform = `scale(${pdfModalZoom})`;
                frame.style.transformOrigin = 'top center';
            }
        }

        function resetPdfModalZoom() {
            pdfModalZoom = 1.0;
            const zoomVal = document.getElementById('pdfModalZoomVal');
            if (zoomVal) zoomVal.textContent = '100%';
            
            const frame = document.getElementById('pdfFrame');
            if (frame) {
                frame.style.transform = 'scale(1)';
                frame.style.transformOrigin = 'top center';
            }
        }

        function closePdfModal() {
            var modal = document.getElementById('pdfModal');
            if (!modal) return;
            modal.classList.remove('show');
            resetPdfModalZoom();
            
            // Exit fullscreen if active
            if (document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
                if (document.exitFullscreen) document.exitFullscreen();
                else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
            }
            const modalContent = document.querySelector('.pdf-modal-content');
            if (modalContent) modalContent.classList.remove('is-fullscreen');
            
            document.getElementById('pdfFrame').src = 'about:blank';
            document.body.style.overflow = 'auto';
            
            if (window.pdfTimerInterval) {
                clearInterval(window.pdfTimerInterval);
                window.pdfTimerInterval = null;
            }
            const timerBadge = document.getElementById('pdfTimerBadge');
            if (timerBadge) timerBadge.style.display = 'none';
        }

        function togglePdfFullscreen() {
            const modal = document.getElementById('pdfModal');
            const modalContent = document.querySelector('.pdf-modal-content');
            const fsBtn = document.getElementById('pdfFullscreenBtn');
            
            if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                if (modal.requestFullscreen) {
                    modal.requestFullscreen();
                } else if (modal.webkitRequestFullscreen) {
                    modal.webkitRequestFullscreen();
                }
                if (modalContent) modalContent.classList.add('is-fullscreen');
                if (fsBtn) fsBtn.innerHTML = '<i class="fa-solid fa-compress"></i> Exit Fullscreen';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
                if (modalContent) modalContent.classList.remove('is-fullscreen');
                if (fsBtn) fsBtn.innerHTML = '<i class="fa-solid fa-expand"></i> Fullscreen';
            }
        }

        document.addEventListener('fullscreenchange', function() {
            const modalContent = document.querySelector('.pdf-modal-content');
            const fsBtn = document.getElementById('pdfFullscreenBtn');
            if (!document.fullscreenElement) {
                if (modalContent) modalContent.classList.remove('is-fullscreen');
                if (fsBtn) fsBtn.innerHTML = '<i class="fa-solid fa-expand"></i> Fullscreen';
            }
        });

        // Security guard against direct printing/saving keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.key === 'P' || e.key === 's' || e.key === 'S')) {
                const modal = document.getElementById('pdfModal');
                if (modal && modal.classList.contains('show')) {
                    if (window.showToast) {
                        window.showToast("🔒 Security Warning: Direct printing and downloading of e-library PDFs is restricted.", "warning");
                    }
                }
            }
        });

        document.getElementById('pdfModal').addEventListener('click', function(e) {
            if (e.target === this) closePdfModal();
        });

        // 2. Automated Confirmation Guard for deletions
        document.addEventListener('click', function(e) {
            if (e.defaultPrevented) return;
            var a = e.target.closest('a[href*="delete_"]');
            if (a) {
                var onclickAttr = a.getAttribute('onclick') || '';
                if (onclickAttr.includes('confirm')) return;
                if (!confirm('Are you absolutely certain you want to delete this catalog record permanently? This cannot be undone.')) {
                    e.preventDefault();
                }
            }
        });

        // 4. Live Client-Side Searching / Filtering Utility
        function registerLiveFilter(inputId, tableId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            
            if (input.dataset.filterInitialized) return;
            input.dataset.filterInitialized = 'true';
            
            input.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const table = document.getElementById(tableId);
                if (!table) return;
                
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    let match = false;
                    const cells = row.querySelectorAll('td');
                    cells.forEach(cell => {
                        if (cell.textContent.toLowerCase().includes(query)) {
                            match = true;
                        }
                    });
                    row.style.display = match ? '' : 'none';
                });
            });
        }

        // Clean spa=1 from address bar on page load if present
        function getCleanSpaUrl(url) {
            if (!url) return '';
            return url.replace(/([?&])spa=1(&|$)/, function(match, p1, p2) {
                return p2 === '&' ? p1 : '';
            }).replace(/([?&])_t=\d+(&|$)/, function(match, p1, p2) {
                return p2 === '&' ? p1 : '';
            }).replace(/[?&]$/, '');
        }

        if (window.history && window.history.replaceState && window.location.search.includes('spa=1')) {
            window.history.replaceState(history.state, '', getCleanSpaUrl(window.location.href));
        }

        // SPA In-Memory Tab Cache
        window.spaTabCache = window.spaTabCache || new Map();

        // Helper to extract and re-execute scripts from injected HTML content
        function executeInjectedScripts(container) {
            if (!container) return;
            const scripts = container.querySelectorAll('script');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.text = oldScript.textContent;
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
        }

        // Optimized High-Performance SPA Navigation Engine
        function navigateToUrl(url, pushState = true, useCache = true) {
            // Track unique navigation token and abort controller to prevent race conditions on rapid tab switching
            window.currentSpaNavToken = (window.currentSpaNavToken || 0) + 1;
            const navToken = window.currentSpaNavToken;

            if (window.currentSpaAbortController) {
                try { window.currentSpaAbortController.abort(); } catch(e) {}
            }
            if (typeof AbortController !== 'undefined') {
                window.currentSpaAbortController = new AbortController();
            } else {
                window.currentSpaAbortController = null;
            }

            if (window.activeUploads && Object.keys(window.activeUploads).length > 0) {
                if (!confirm("⚠️ WARNING: You have active background uploads in progress!\n\nNavigating away will abort these uploads. Do you want to cancel the uploads and proceed?")) {
                    return;
                }
                Object.values(window.activeUploads).forEach(upload => upload.cancel());
                window.activeUploads = {};
                updateHeaderUploadBadge();
            }

            let progressBar = document.getElementById('top-progress-bar');
            if (!progressBar) {
                progressBar = document.createElement('div');
                progressBar.id = 'top-progress-bar';
                document.body.appendChild(progressBar);
            }
            
            progressBar.style.opacity = '1';
            progressBar.style.width = '30%';

            if (window.offlinePollerInterval) {
                clearInterval(window.offlinePollerInterval);
                window.offlinePollerInterval = null;
            }

            // Normalize URL for cache key and browser history (strip spa=1 & timestamp)
            const cleanUrl = getCleanSpaUrl(url);
            
            // Fast UI update function
            function renderSpaData(data, isBackgroundRefresh = false) {
                if (navToken !== window.currentSpaNavToken) return;

                if (data.title) {
                    document.title = data.title;
                }
                
                // Target specific tab content containers to keep header/profile banners permanently intact
                const adminTabContent = document.getElementById('adminTabContent');
                const userTabContent = document.getElementById('userTabContent');
                const adminContent = document.querySelector('.admin-content');
                const mainArea = document.querySelector('main');

                const updateContainer = (container) => {
                    if (!container || !data.content) return;
                    if (isBackgroundRefresh && container.innerHTML === data.content) return;

                    container.innerHTML = data.content;
                    container.classList.remove('spa-loading-overlay');
                    if (!isBackgroundRefresh) {
                        container.classList.add('spa-fade-in');
                        setTimeout(() => container.classList.remove('spa-fade-in'), 250);
                    }
                    executeInjectedScripts(container);
                };

                if (adminTabContent && data.content) {
                    updateContainer(adminTabContent);
                } else if (userTabContent && data.content) {
                    updateContainer(userTabContent);
                } else if (adminContent && data.content) {
                    updateContainer(adminContent);
                } else if (mainArea && data.content) {
                    updateContainer(mainArea);
                }

                // Update sidebar if returned
                if (data.sidebar) {
                    const currentSidebar = document.querySelector('.sidebar');
                    if (currentSidebar && (!isBackgroundRefresh || currentSidebar.innerHTML !== data.sidebar)) {
                        currentSidebar.innerHTML = data.sidebar;
                        executeInjectedScripts(currentSidebar);
                    }
                }

                // Update badges if returned
                if (data.reading_count !== undefined) {
                    const badge = document.getElementById('sidebarReadingBadge');
                    if (badge) {
                        badge.textContent = data.reading_count;
                        badge.style.display = data.reading_count > 0 ? '' : 'none';
                    }
                }
                if (data.print_count !== undefined) {
                    const badge = document.getElementById('sidebarPrintBadge');
                    if (badge) {
                        badge.textContent = data.print_count;
                        badge.style.display = data.print_count > 0 ? '' : 'none';
                    }
                }

                // Trigger toast notification instantly if flash message exists in JSON response
                if (data.flash && window.showToast) {
                    const msg = data.flash;
                    const isSuccess = msg.includes('🎉') || msg.includes('✓') || msg.toLowerCase().startsWith('success');
                    const isWarning = !isSuccess && (msg.toLowerCase().includes('inactive') || msg.toLowerCase().includes('expired'));
                    const isError = !isSuccess && !isWarning && (
                        msg.includes('Duplicate') || msg.includes('⚠️') || msg.includes('Error') ||
                        msg.includes('Invalid') || msg.toLowerCase().includes('warning') ||
                        msg.toLowerCase().includes('suspended') || msg.toLowerCase().includes('failed') ||
                        msg.toLowerCase().includes('rejected') || msg.toLowerCase().includes('cannot') ||
                        msg.toLowerCase().includes('required') || msg.toLowerCase().includes('empty') ||
                        msg.toLowerCase().includes('already exists') || msg.toLowerCase().includes('no book found') ||
                        msg.toLowerCase().includes('already issued')
                    );
                    const type = isWarning ? 'warning' : (isError ? 'error' : 'success');
                    window.showToast(msg, type);
                }

                initializePageFeatures();
            }

            window.renderSpaData = renderSpaData;

            // 1. Instant Cache Render (0ms Latency) if available
            const cachedData = window.spaTabCache.get(cleanUrl);
            let renderedFromCache = false;
            if (useCache && cachedData) {
                renderSpaData(cachedData, false);
                renderedFromCache = true;
                progressBar.style.width = '100%';
                setTimeout(() => {
                    progressBar.style.opacity = '0';
                    setTimeout(() => { progressBar.style.width = '0%'; }, 200);
                }, 100);
            }

            // Immediate visual feedback if not cached
            if (!renderedFromCache) {
                const adminContent = document.querySelector('.admin-content');
                if (adminContent) {
                    adminContent.classList.add('spa-loading-overlay');
                }
            }

            // 2. Fetch fresh SPA payload (&spa=1)
            const spaFetchUrl = cleanUrl + (cleanUrl.includes('?') ? '&' : '?') + 'spa=1&_t=' + Date.now();
            
            const fetchOptions = {
                headers: {
                    'X-SPA-Request': '1',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-store'
            };
            if (window.currentSpaAbortController) {
                fetchOptions.signal = window.currentSpaAbortController.signal;
            }

            fetch(spaFetchUrl, fetchOptions)
            .then(res => {
                if (navToken !== window.currentSpaNavToken) return null;
                progressBar.style.width = '80%';
                const contentType = res.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    return res.json();
                } else {
                    return res.text().then(text => ({ isHtml: true, text: text }));
                }
            })
            .then(data => {
                if (!data || navToken !== window.currentSpaNavToken) return;

                progressBar.style.width = '100%';

                if (data.isHtml) {
                    // Fallback DOMParser for HTML response
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(data.text, 'text/html');
                    document.title = newDoc.title;

                    const currentMain = document.querySelector('main');
                    const newMain = newDoc.querySelector('main');
                    if (currentMain && newMain) {
                        currentMain.innerHTML = newMain.innerHTML;
                        executeInjectedScripts(currentMain);
                    }
                    initializePageFeatures();
                } else if (data.success) {
                    const isContentIdentical = cachedData && (cachedData.content === data.content);
                    // Cache the JSON payload (without flash message to avoid replaying on cache hit)
                    const cacheData = { ...data };
                    delete cacheData.flash;
                    window.spaTabCache.set(cleanUrl, cacheData);
                    
                    // Only re-render if not already rendered from cache or if server data actually changed
                    if (!renderedFromCache || !isContentIdentical) {
                        renderSpaData(data, renderedFromCache);
                    }
                }

                if (pushState) {
                    history.pushState({ url: cleanUrl }, '', cleanUrl);
                }

                const adminContent = document.querySelector('.admin-content');
                if (adminContent) {
                    adminContent.classList.remove('spa-loading-overlay');
                }

                setTimeout(() => {
                    progressBar.style.opacity = '0';
                    setTimeout(() => { progressBar.style.width = '0%'; }, 200);
                }, 150);
            })
            .catch(err => {
                if (err.name === 'AbortError') return; // Cleanly ignore aborted requests
                if (navToken !== window.currentSpaNavToken) return;
                console.error("SPA Navigation Error:", err);
                const adminContent = document.querySelector('.admin-content');
                if (adminContent) {
                    adminContent.classList.remove('spa-loading-overlay');
                }
                progressBar.style.opacity = '0';
            });
        }
        window.navigateToUrl = navigateToUrl;

        // Intercept clicks on links that change sub-tabs in admin or user views
        document.addEventListener('click', function(e) {
            const anchor = e.target.closest('a');
            if (!anchor) return;
            
            const href = anchor.getAttribute('href');
            if (!href) return;
            
            // Only capture internal navigation parameters
            if (href.startsWith('?action=admin') || href.startsWith('?action=user')) {
                if (href.includes('action=delete_') || href.includes('action=logout') || href.includes('action=request_read') || href.includes('action=return_book')) {
                    return;
                }
                e.preventDefault();

                // Immediately highlight target sidebar link in 0ms for instant feel
                const sidebarLinks = document.querySelectorAll('.sidebar a');
                sidebarLinks.forEach(link => {
                    if (link.getAttribute('href') === href) {
                        link.classList.add('active');
                    } else {
                        link.classList.remove('active');
                    }
                });

                navigateToUrl(href);
            }
        });

        // Instant Hover / Pointer-Over Preloader for 0ms tab switching
        document.addEventListener('pointerover', function(e) {
            const anchor = e.target.closest('a');
            if (!anchor) return;
            const href = anchor.getAttribute('href');
            if (!href) return;
            if (href.startsWith('?action=admin') || href.startsWith('?action=user')) {
                if (href.includes('action=delete_') || href.includes('action=logout') || href.includes('action=request_read') || href.includes('action=return_book')) {
                    return;
                }
                const cleanUrl = getCleanSpaUrl(href);
                if (window.spaTabCache && !window.spaTabCache.has(cleanUrl)) {
                    const spaFetchUrl = cleanUrl + (cleanUrl.includes('?') ? '&' : '?') + 'spa=1';
                    fetch(spaFetchUrl, {
                        headers: {
                            'X-SPA-Request': '1',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        cache: 'no-store'
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.success && window.spaTabCache) {
                            const cacheData = { ...data };
                            delete cacheData.flash;
                            window.spaTabCache.set(cleanUrl, cacheData);
                        }
                    })
                    .catch(() => {});
                }
            }
        }, { passive: true });

        // Proactive Background Tab Warmer: Pre-load ALL sidebar menu tabs on page load during browser idle time
        function warmAllSidebarTabCaches() {
            const links = document.querySelectorAll('.sidebar a');
            let delay = 25;
            links.forEach(link => {
                const href = link.getAttribute('href');
                if (!href) return;
                if (href.startsWith('?action=admin') || href.startsWith('?action=user')) {
                    if (href.includes('action=delete_') || href.includes('action=logout') || href.includes('action=request_read') || href.includes('action=return_book')) {
                        return;
                    }
                    const cleanUrl = getCleanSpaUrl(href);
                    if (window.spaTabCache && !window.spaTabCache.has(cleanUrl)) {
                        setTimeout(() => {
                            if (window.spaTabCache && !window.spaTabCache.has(cleanUrl)) {
                                const spaFetchUrl = cleanUrl + (cleanUrl.includes('?') ? '&' : '?') + 'spa=1';
                                fetch(spaFetchUrl, {
                                    headers: { 'X-SPA-Request': '1', 'X-Requested-With': 'XMLHttpRequest' },
                                    cache: 'no-store'
                                })
                                .then(res => res.json())
                                .then(data => {
                                    if (data && data.success && window.spaTabCache) {
                                        const cacheData = { ...data };
                                        delete cacheData.flash;
                                        window.spaTabCache.set(cleanUrl, cacheData);
                                    }
                                })
                                .catch(() => {});
                            }
                        }, delay);
                        delay += 25; // Stagger requests by 25ms so all tabs are ready within ~200ms
                    }
                }
            });
        }

        if ('requestIdleCallback' in window) {
            requestIdleCallback(warmAllSidebarTabCaches, { timeout: 1000 });
        } else {
            setTimeout(warmAllSidebarTabCaches, 200);
        }


        // Global Active Background Parallel Uploads tracker
        window.activeUploads = window.activeUploads || {};

        function updateHeaderUploadBadge() {
            const badge = document.getElementById('headerUploadBadge');
            if (!badge) return;
            const count = Object.keys(window.activeUploads).length;
            if (count > 0) {
                badge.style.display = 'inline-block';
                badge.innerHTML = `<i class="fa-solid fa-spinner fa-spin" style="margin-right:4px;"></i> Uploads (${count})`;
                badge.style.background = 'var(--accent-orange)';
                badge.style.color = 'white';
                badge.style.borderRadius = '20px';
                badge.style.padding = '2px 8px';
                badge.style.fontSize = '11px';
                badge.style.fontWeight = '700';
            } else {
                badge.style.display = 'none';
            }
        }

        class UploadManager {
            constructor(file, metadata, onProgress, onComplete, onError) {
                this.file = file;
                this.metadata = metadata;
                this.onProgress = onProgress || (() => {});
                this.onComplete = onComplete || (() => {});
                this.onError = onError || (() => {});
                
                this.chunkSize = 1 * 1024 * 1024; // 1MB chunks (100% compatible with default php.ini 2MB upload_max_filesize!)
                
                if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
                    this.uploadId = crypto.randomUUID();
                } else {
                    // Fallback browser UUID v4 generator for HTTP IP/LAN environments
                    this.uploadId = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                        const r = Math.random() * 16 | 0;
                        const v = c === 'x' ? r : (r & 0x3 | 0x8);
                        return v.toString(16);
                    });
                }
                
                this.totalChunks = Math.ceil(file.size / this.chunkSize);
                this.uploadedChunks = 0;
                this.isCancelled = false;
                
                window.activeUploads[this.uploadId] = this;
                updateHeaderUploadBadge();
            }
            
            async start() {
                try {
                    for (let i = 0; i < this.totalChunks; i += 3) {
                        if (this.isCancelled) return;
                        const batch = [];
                        for (let j = 0; j < 3 && (i + j) < this.totalChunks; j++) {
                            batch.push(this.uploadChunk(i + j));
                        }
                        await Promise.all(batch);
                    }
                    if (this.isCancelled) return;
                    await this.assemble();
                } catch (err) {
                    if (!this.isCancelled) {
                        delete window.activeUploads[this.uploadId];
                        updateHeaderUploadBadge();
                        this.onError(err.message || err);
                    }
                }
            }
            
            async uploadChunk(index) {
                if (this.isCancelled) return;
                const start = index * this.chunkSize;
                const end = Math.min(start + this.chunkSize, this.file.size);
                const blob = this.file.slice(start, end);
                
                const fd = new FormData();
                fd.append('upload_id', this.uploadId);
                fd.append('chunk_index', index);
                fd.append('total_chunks', this.totalChunks);
                fd.append('chunk', blob);
                
                const response = await fetch('?action=upload_chunk', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': '<?= csrf_token() ?>'
                    },
                    body: fd
                });
                
                if (!response.ok) {
                    throw new Error(`Upload server responded with error ${response.status}`);
                }
                
                const data = await response.json();
                if (!data.ok) {
                    throw new Error(data.error || 'Server rejected uploaded chunk slice');
                }
                
                if (this.isCancelled) return;
                this.uploadedChunks++;
                this.onProgress(this.uploadedChunks, this.totalChunks);
            }
            
            async assemble() {
                if (this.isCancelled) return;
                const fd = new FormData();
                fd.append('upload_id', this.uploadId);
                fd.append('total_chunks', this.totalChunks);
                fd.append('category_id', this.metadata.category_id);
                fd.append('title', this.metadata.title);
                fd.append('keywords', this.metadata.keywords);
                fd.append('csrf_token', '<?= csrf_token() ?>');
                if (this.metadata.ebook_id) {
                    fd.append('ebook_id', this.metadata.ebook_id);
                }
                
                const response = await fetch('?action=assemble_upload', {
                    method: 'POST',
                    body: fd
                });
                
                if (!response.ok) {
                    throw new Error(`Assembly request failed with status ${response.status}`);
                }
                
                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.error || 'Assembling chunk slices failed on server side');
                }
                
                delete window.activeUploads[this.uploadId];
                updateHeaderUploadBadge();
                this.onComplete(data);
            }
            
            cancel() {
                this.isCancelled = true;
                delete window.activeUploads[this.uploadId];
                updateHeaderUploadBadge();
                
                const fd = new FormData();
                fd.append('upload_id', this.uploadId);
                fd.append('csrf_token', '<?= csrf_token() ?>');
                fetch('?action=cancel_upload', {
                    method: 'POST',
                    body: fd
                }).catch(() => {});
            }
        }

        // Sync standard back & forward browser navigation events
        window.addEventListener('popstate', function(e) {
            if (e.state && e.state.url) {
                navigateToUrl(e.state.url, false);
            } else {
                navigateToUrl(window.location.href, false);
            }
        });

        // Warn users before closing the tab or reloading if background uploads are in progress
        window.addEventListener('beforeunload', function(e) {
            if (window.activeUploads && Object.keys(window.activeUploads).length > 0) {
                e.preventDefault();
                e.returnValue = 'You have active background library uploads in progress. Leaving now will cancel them.';
                return 'You have active background library uploads in progress. Leaving now will cancel them.';
            }
        });

        // Centralized event-delegated form interceptor & SPA form submitter
        document.addEventListener('submit', function(e) {
            if (e.defaultPrevented) return;

            // Invalidate SPA Cache on every form submission to ensure fresh data
            if (window.spaTabCache) {
                window.spaTabCache.clear();
            }

            const form = e.target;
            const submitter = e.submitter;

            // Prevent rapid double form submissions
            if (form.dataset.isSubmitting === 'true') {
                e.preventDefault();
                return;
            }

            // 1. Confirm dialog check for delete actions
            const onsubmitAttr = form.getAttribute('onsubmit') || '';
            const onclickAttr = submitter ? (submitter.getAttribute('onclick') || '') : '';
            const action = form.getAttribute('action') || (submitter ? submitter.getAttribute('formaction') : '') || '';
            const actionLower = action.toLowerCase();

            if (!onsubmitAttr.includes('confirm') && !onclickAttr.includes('confirm')) {
                if (actionLower.includes('delete_') || actionLower.includes('action=delete_')) {
                    const confirmMsg = "⚠️ WARNING: Are you absolutely sure you want to delete this record permanently?\n\nThis action is irreversible and cannot be undone.";
                    if (!confirm(confirmMsg)) {
                        e.preventDefault();
                        return;
                    }
                }
            }

            // Validate HTML5 required fields before applying disabled loader state
            if (form.checkValidity && !form.checkValidity()) {
                return;
            }

            // Replace button content with spinner loader and disable to enforce single click
            let originalSubmitterHtml = null;
            let originalSubmitterVal = null;
            if (submitter) {
                submitter.disabled = true;
                submitter.style.opacity = '0.75';
                submitter.style.cursor = 'not-allowed';
                submitter.style.pointerEvents = 'none';

                const btnTxt = (submitter.textContent || '').trim();
                let spinnerText = 'Processing...';
                if (btnTxt.includes('Grant')) spinnerText = 'Granting...';
                else if (btnTxt.includes('Reject')) spinnerText = 'Rejecting...';
                else if (btnTxt.includes('Complete')) spinnerText = 'Completing...';
                else if (btnTxt.includes('Return')) spinnerText = 'Returning...';

                if (submitter.tagName === 'INPUT') {
                    originalSubmitterVal = submitter.value;
                    submitter.value = spinnerText;
                } else {
                    originalSubmitterHtml = submitter.innerHTML;
                    submitter.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> ' + spinnerText;
                }
            }

            // 2. SPA Form submission for single-click instant execution
            // Skip GET forms, file uploads, external targets, auth/login forms, or pages outside SPA panels
            if (
                (form.method || 'POST').toUpperCase() === 'GET' ||
                form.enctype === 'multipart/form-data' || 
                form.target === '_blank' || 
                form.id === 'ebFormAdd' || 
                form.id === 'ebFormEdit' ||
                actionLower.includes('login') ||
                actionLower.includes('logout') ||
                actionLower.includes('forgot_password') ||
                !document.querySelector('.admin-content, .user-layout, .sidebar')
            ) {
                form.dataset.isSubmitting = 'true';
                return;
            }

            e.preventDefault();
            form.dataset.isSubmitting = 'true';

            const formData = new FormData(form);
            if (submitter && submitter.name) {
                formData.append(submitter.name, submitter.value);
            }

            const targetUrl = action || window.location.href;

            fetch(targetUrl, {
                method: (form.method || 'POST').toUpperCase(),
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-SPA-Request': '1'
                }
            })
            .then(res => {
                if (window.spaTabCache) window.spaTabCache.clear();
                const redirectUrl = res.url || window.location.href;
                const contentType = res.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    return res.json().then(data => ({ data, redirectUrl }));
                } else {
                    return res.text().then(html => ({ html, redirectUrl }));
                }
            })
            .then(({ data, html, redirectUrl }) => {
                if (data && data.success) {
                    renderSpaData(data);
                    const cleanUrl = redirectUrl.replace(/([?&])spa=1(&|$)/, function(match, p1, p2) {
                        return p2 === '&' ? p1 : '';
                    }).replace(/([?&])_t=\d+(&|$)/, function(match, p1, p2) {
                        return p2 === '&' ? p1 : '';
                    }).replace(/[?&]$/, '');
                    history.pushState({ url: cleanUrl }, '', cleanUrl);
                } else if (html) {
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(html, 'text/html');
                    if (newDoc.title) document.title = newDoc.title;

                    const adminTabContent = document.getElementById('adminTabContent');
                    const userTabContent = document.getElementById('userTabContent');
                    const currentMain = document.querySelector('main');
                    
                    const newAdminTab = newDoc.getElementById('adminTabContent');
                    const newUserTab = newDoc.getElementById('userTabContent');
                    const newMain = newDoc.querySelector('main');

                    if (adminTabContent && newAdminTab) {
                        adminTabContent.innerHTML = newAdminTab.innerHTML;
                        executeInjectedScripts(adminTabContent);
                    } else if (userTabContent && newUserTab) {
                        userTabContent.innerHTML = newUserTab.innerHTML;
                        executeInjectedScripts(userTabContent);
                    } else if (currentMain && newMain) {
                        currentMain.innerHTML = newMain.innerHTML;
                        executeInjectedScripts(currentMain);
                    }
                    executeInjectedScripts(newDoc.body);
                    if (typeof initializePageFeatures === 'function') initializePageFeatures();

                    const cleanUrl = redirectUrl.replace(/([?&])spa=1(&|$)/, function(match, p1, p2) {
                        return p2 === '&' ? p1 : '';
                    }).replace(/([?&])_t=\d+(&|$)/, function(match, p1, p2) {
                        return p2 === '&' ? p1 : '';
                    }).replace(/[?&]$/, '');
                    history.pushState({ url: cleanUrl }, '', cleanUrl);
                } else {
                    navigateToUrl(redirectUrl, true, false);
                }
            })
            .catch(err => {
                console.error("SPA Form Submission Error:", err);
                form.submit();
            })
            .finally(() => {
                delete form.dataset.isSubmitting;
                if (submitter) {
                    submitter.disabled = false;
                    submitter.style.opacity = '';
                    submitter.style.cursor = '';
                    submitter.style.pointerEvents = '';
                    if (originalSubmitterVal !== null) {
                        submitter.value = originalSubmitterVal;
                    }
                    if (originalSubmitterHtml !== null) {
                        submitter.innerHTML = originalSubmitterHtml;
                    }
                }
            });
        });

        // Web Browser Storage Cleanup on Logout
        (function() {
            function purgeBrowserStorage() {
                try {
                    sessionStorage.clear();
                    localStorage.clear();
                } catch (e) {
                    console.warn("Unable to purge web browser storage:", e);
                }
            }

            // 1. Intercept clicks on all logout buttons/links
            document.addEventListener('click', function(e) {
                const logoutAnchor = e.target.closest('a[href*="action=logout"]');
                if (logoutAnchor) {
                    purgeBrowserStorage();
                }
            });

            // 2. Check if current URL indicates a logout action or post-logout redirect
            const query = window.location.search || '';
            if (query.includes('action=logout') || query.includes('logged_out=1')) {
                purgeBrowserStorage();
            }
        })();
    </script>
</body>
</html>
