    </main>

    <!-- Secure e-Reading PDF Modal -->
    <div id="pdfModal" class="pdf-modal">
        <div class="pdf-modal-content">
            <div class="pdf-modal-header">
                <h3 id="pdfModalTitle">📖 e-Library Interactive Secure Reader</h3>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span id="pdfTimerBadge" class="badge badge-orange" style="font-size:12px; font-weight:600; padding:6px 12px; display:none;">⏱️ 00m 00s remaining</span>
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
                'fa-users-rectangle':'🧾',
                'fa-circle-plus':'➕',
                'fa-list-check':'📋',
                'fa-box-archive':'💾',
                'fa-database':'🗃️',
                'fa-hard-drive':'💿',
                'fa-calendar-check':'🔒'
            };
            
            document.querySelectorAll('i[class*="fa-"]').forEach(el => {
                const classes = el.className.split(' ');
                for (let cls of classes) {
                    if (cls.startsWith('fa-') && iconMap[cls]) {
                        el.outerHTML = `<span style="font-style: normal; margin-right: 6px; display: inline-block;">${iconMap[cls]}</span>`;
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

            // 5. Client-Side Table Pagination
            const tablesToPaginate = ['ebooksTable', 'physicalTable', 'membersTable', 'viewMembersTable', 'lendingLogTable', 'requestsTable', 'printRequestsTable'];
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

        function closePdfModal() {
            var modal = document.getElementById('pdfModal');
            if (!modal) return;
            modal.classList.remove('show');
            
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
            var a = e.target.closest('a[href*="delete_"]');
            if (a && !confirm('Are you absolutely certain you want to delete this catalog record permanently? This cannot be undone.')) {
                e.preventDefault();
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

        // SPA-like navigation logic
        function navigateToUrl(url, pushState = true) {
            if (window.activeUploads && Object.keys(window.activeUploads).length > 0) {
                if (!confirm("⚠️ WARNING: You have active background uploads in progress!\n\nNavigating away will abort these uploads. Do you want to cancel the uploads and proceed?")) {
                    return;
                }
                // Cancel all active uploads cleanly
                Object.values(window.activeUploads).forEach(upload => {
                    upload.cancel();
                });
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
            
            // Clear any active update poller intervals to avoid multiple loops running concurrently
            if (window.offlinePollerInterval) {
                clearInterval(window.offlinePollerInterval);
                window.offlinePollerInterval = null;
            }
            
            const cacheBustUrl = url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now();
            fetch(cacheBustUrl, { cache: 'no-store' })
                .then(response => {
                    progressBar.style.width = '70%';
                    if (!response.ok) throw new Error("Navigation request failed");
                    return response.text();
                })
                .then(htmlText => {
                    progressBar.style.width = '100%';
                    
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(htmlText, 'text/html');
                    
                    // Update tab/page title
                    document.title = newDoc.title;
                    
                    // Replace main content
                    const currentMain = document.querySelector('main');
                    const newMain = newDoc.querySelector('main');
                    if (currentMain && newMain) {
                        currentMain.innerHTML = newMain.innerHTML;
                    }
                    
                    // Update active nav links
                    const currentNavLinks = document.querySelector('.nav-links');
                    const newNavLinks = newDoc.querySelector('.nav-links');
                    if (currentNavLinks && newNavLinks) {
                        currentNavLinks.innerHTML = newNavLinks.innerHTML;
                    }
                    
                    // Update active sidebar selection
                    const currentSidebar = document.querySelector('.sidebar');
                    const newSidebar = newDoc.querySelector('.sidebar');
                    if (currentSidebar && newSidebar) {
                        currentSidebar.innerHTML = newSidebar.innerHTML;
                    }
                    
                    // Inject & execute the tab-specific dynamic scripts
                    newDoc.querySelectorAll('script.dynamic-script').forEach(script => {
                        const newScript = document.createElement('script');
                        newScript.className = 'dynamic-script';
                        newScript.text = script.text;
                        document.body.appendChild(newScript);
                        newScript.remove(); // Cleanup executed script tag
                    });

                    // Re-initialize core forms/features on new nodes
                    initializePageFeatures();
                    
                    if (pushState) {
                        history.pushState({ url: url }, '', url);
                    }
                    
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    
                    setTimeout(() => {
                        progressBar.style.opacity = '0';
                        setTimeout(() => { progressBar.style.width = '0%'; }, 400);
                    }, 200);
                })
                .catch(err => {
                    console.error("PJAX Navigation Error:", err);
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
                navigateToUrl(href);
            }
        });

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

        // Centralized event-delegated form interceptor for all record deletions
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const action = form.getAttribute('action') || '';
            const actionLower = action.toLowerCase();
            if (actionLower.includes('delete_') || actionLower.includes('action=delete_')) {
                const confirmMsg = "⚠️ WARNING: Are you absolutely sure you want to delete this record permanently?\n\nThis action is irreversible and cannot be undone.";
                if (!confirm(confirmMsg)) {
                    e.preventDefault();
                }
            }
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
