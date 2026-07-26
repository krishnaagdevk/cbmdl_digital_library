<?php
/**
 * Interactive Secure PDF Reader View
 *
 * Variables expected:
 * @var string $pdfTitle
 * @var string $streamUrl
 * @var int $expiresAtUnix
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pdfTitle) ?> - Interactive Secure Reader</title>
    <script src="<?= BASE_URL ?>js/pdf.min.js"></script>
    <style>
        :root {
            --bg-primary: #0b0f19;
            --bg-secondary: #111827;
            --bg-accent: #1f2937;
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --accent-blue: #3b82f6;
            --accent-hover: #2563eb;
            --border-color: #374151;
        }

        * {
            box-sizing: border-box;
            user-select: none !important;
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
        }

        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            overflow: hidden;
        }

        .viewer-toolbar {
            height: 56px;
            background-color: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            z-index: 100;
            position: relative;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }

        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toolbar-btn {
            background-color: transparent;
            border: 1px solid transparent;
            color: var(--text-secondary);
            height: 38px;
            padding: 0 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .toolbar-btn:hover {
            background-color: var(--bg-accent);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .toolbar-btn.active {
            background-color: var(--accent-blue);
            color: #ffffff;
        }

        .toolbar-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
            background-color: transparent !important;
            border-color: transparent !important;
            color: var(--text-secondary) !important;
        }

        .toolbar-divider {
            height: 24px;
            width: 1px;
            background-color: var(--border-color);
            margin: 0 4px;
        }

        .page-indicator {
            font-size: 14px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .page-num-input {
            width: 56px;
            height: 30px;
            background-color: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            outline: none;
            padding: 0 4px;
            transition: border-color 0.2s;
        }

        .page-num-input:focus {
            border-color: var(--accent-blue);
        }

        .page-num-input::-webkit-outer-spin-button,
        .page-num-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .page-num-input[type=number] {
            -moz-appearance: textfield;
        }

        .doc-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            max-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .viewer-layout {
            display: flex;
            height: calc(100% - 56px);
            width: 100%;
            overflow: hidden;
            position: relative;
        }

        .viewer-sidebar {
            width: 260px;
            height: 100%;
            background-color: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 10;
        }

        .viewer-sidebar.collapsed {
            margin-left: -260px;
        }

        .sidebar-header {
            height: 48px;
            min-height: 48px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            padding: 0 16px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--text-secondary);
        }

        .thumbnail-container {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .thumbnail-container::-webkit-scrollbar,
        .viewport-scroll-area::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .thumbnail-container::-webkit-scrollbar-track,
        .viewport-scroll-area::-webkit-scrollbar-track {
            background: transparent;
        }
        .thumbnail-container::-webkit-scrollbar-thumb,
        .viewport-scroll-area::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .thumbnail-container::-webkit-scrollbar-thumb:hover,
        .viewport-scroll-area::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .thumbnail-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 10px;
            border-radius: 10px;
            background-color: var(--bg-primary);
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .thumbnail-card:hover {
            border-color: var(--border-color);
            transform: translateY(-2px);
        }

        .thumbnail-card.active {
            border-color: var(--accent-blue);
            background-color: rgba(59, 130, 246, 0.08);
        }

        .thumbnail-viewport {
            width: 100%;
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-accent);
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            position: relative;
        }

        .thumbnail-viewport canvas {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
        }

        .thumbnail-placeholder {
            position: absolute;
            color: var(--text-secondary);
            font-size: 24px;
            opacity: 0.5;
            animation: pulse 1.5s infinite ease-in-out;
        }

        .thumbnail-num {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .thumbnail-card.active .thumbnail-num {
            color: var(--accent-blue);
        }

        .viewport-scroll-area {
            flex: 1;
            height: 100%;
            overflow: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            gap: 24px;
            padding: 40px 20px;
            background-color: var(--bg-primary);
            position: relative;
            box-sizing: border-box;
        }

        .pdf-page-wrapper {
            flex-shrink: 0;
        }

        .page-inner {
            position: relative;
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            border-radius: 4px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .pdf-page-canvas {
            display: block;
            width: 100%;
            height: 100%;
        }

        .page-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: var(--text-secondary);
            opacity: 0.4;
            font-size: 32px;
        }

        .page-placeholder-num {
            font-size: 13px;
            font-weight: 600;
        }

        .loading-screen {
            position: absolute;
            inset: 0;
            background-color: var(--bg-primary);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
            z-index: 500;
            transition: opacity 0.3s ease;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--bg-accent);
            border-top-color: var(--accent-blue);
            border-radius: 50%;
            animation: rotate-spin 1s linear infinite;
        }

        .loading-text {
            font-size: 16px;
            font-weight: 500;
            color: var(--text-secondary);
        }

        @keyframes rotate-spin {
            to { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.7; }
        }

        @media print {
            body, html, .viewer-toolbar, .viewer-layout, canvas {
                display: none !important;
                visibility: hidden !important;
            }
        }
    </style>
</head>
<body>

    <div id="loader" class="loading-screen">
        <div class="loading-spinner"></div>
        <div id="loaderText" class="loading-text">Connecting to secure document stream...</div>
    </div>

    <header class="viewer-toolbar">
        <div class="toolbar-group">
            <button id="sidebarToggle" class="toolbar-btn active" title="Toggle Previews Sidebar">
                <span class="fa-solid fa-list-ul"></span> Previews
            </button>
            <div class="toolbar-divider"></div>
            <div id="docTitle" class="doc-title" title="<?= e($pdfTitle) ?>"><?= e($pdfTitle) ?></div>
        </div>

        <?php if ($expiresAtUnix > 0): ?>
        <div class="toolbar-group">
            <div id="pdfTimerBadge" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.4); color: #60a5fa; padding: 5px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; letter-spacing: 0.5px; white-space: nowrap;">
                <span class="fa-solid fa-clock" style="color: #3b82f6;"></span>
                <span>Session: <span id="pdfTimerText">--m --s</span></span>
            </div>
        </div>
        <?php endif; ?>

        <div class="toolbar-group">
            <button id="prevPageBtn" class="toolbar-btn" title="Previous Page">
                <span class="fa-solid fa-chevron-left"></span>
            </button>
            <div class="page-indicator">
                <input type="number" id="pageNumInput" class="page-num-input" value="1">
                <span>/</span>
                <span id="pageCount">--</span>
            </div>
            <button id="nextPageBtn" class="toolbar-btn" title="Next Page">
                <span class="fa-solid fa-chevron-right"></span>
            </button>
        </div>

        <div class="toolbar-group">
            <button id="zoomOutBtn" class="toolbar-btn" title="Zoom Out">
                <span class="fa-solid fa-magnifying-glass-minus"></span>
            </button>
            <span id="zoomPercent" style="font-size: 14px; font-weight: 600; min-width: 45px; text-align: center; color: var(--text-secondary)">100%</span>
            <button id="zoomInBtn" class="toolbar-btn" title="Zoom In">
                <span class="fa-solid fa-magnifying-glass-plus"></span>
            </button>
            <button id="zoomFitBtn" class="toolbar-btn" title="Fit Page Width">
                <span class="fa-solid fa-arrows-alt"></span> Fit
            </button>
            <span style="font-size: 11px; color: #f70404ff; background: rgba(255, 255, 255, 0.93); padding: 4px 10px; border-radius: 6px; border: 1px solid rgba(255, 255, 255, 0.12); white-space: nowrap; display: inline-flex; align-items: center; gap: 5px;" title="Tip">
                <i class="fa-solid fa-circle-info" style="color: #e63318ff;text-weight:bold"></i> Click "Fit" if page is not visible correctly
            </span>
            <div class="toolbar-divider"></div>
            <button id="rotateBtn" class="toolbar-btn" title="Rotate Clockwise 90°">
                <span class="fa-solid fa-rotate-right"></span> Rotate
            </button>
            <button id="fullscreenBtn" class="toolbar-btn" title="Toggle Fullscreen">
                <span class="fa-solid fa-expand"></span> Fullscreen
            </button>
        </div>
    </header>

    <main class="viewer-layout">
        <aside id="sidebar" class="viewer-sidebar">
            <div class="sidebar-header">Document Pages</div>
            <div id="thumbnailList" class="thumbnail-container"></div>
        </aside>

        <section class="viewport-scroll-area"></section>
    </main>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = '<?= BASE_URL ?>js/pdf.worker.min.js';

        const pdfUrl        = '<?= $streamUrl ?>';
        const expiresAtUnix = <?= (int)$expiresAtUnix ?>;
        let pdfDoc = null;
        let numPages = 0;
        let currentPageNum = 1;
        let currentScale = 1.0;
        let currentRotation = 0;

        // Per-page state for the virtualised continuous-scroll viewport
        let pageWrappers = [];
        const pageProxies = {};      // shared cache — used by BOTH main render & thumbnails
        const pageBaseDims = {};
        const renderedPages = new Set();
        const pendingRenders = new Set();
        const renderTasks = {};

        // ── Thumbnail serial queue (max 2 concurrent renders so they never
        //    starve visible page rendering) ──────────────────────────────────
        let thumbRunning = 0;
        const THUMB_CONCURRENCY = 2;
        const thumbQueue = [];        // { num, cardElement }

        function thumbEnqueue(num, cardElement) {
            thumbQueue.push({ num, cardElement });
            thumbDrain();
        }

        function thumbDrain() {
            while (thumbRunning < THUMB_CONCURRENCY && thumbQueue.length > 0) {
                const { num, cardElement } = thumbQueue.shift();
                thumbRunning++;
                renderThumbnail(num, cardElement).finally(() => {
                    thumbRunning--;
                    thumbDrain();
                });
            }
        }
        // ──────────────────────────────────────────────────────────────────

        const loader      = document.getElementById('loader');
        const loaderText  = document.getElementById('loaderText');
        const pageNumInput       = document.getElementById('pageNumInput');
        const pageCountLabel     = document.getElementById('pageCount');
        const zoomPercentLabel   = document.getElementById('zoomPercent');
        const viewportScrollArea = document.querySelector('.viewport-scroll-area');

        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && ['s', 'S', 'p', 'P', 'u', 'U'].includes(e.key)) {
                e.preventDefault(); return false;
            }
            if (e.key === 'F12' || ((e.ctrlKey || e.metaKey) && e.shiftKey && ['I','i','C','c','J','j'].includes(e.key))) {
                e.preventDefault(); return false;
            }
        });

        // ── IndexedDB Local Offline Cache Engine ───────────────────────────
        const CBMDL_PDFCache = {
            dbName: 'CBMDL_PDF_Cache',
            storeName: 'pdf_documents',
            dbPromise: null,

            open() {
                if (!this.dbPromise) {
                    this.dbPromise = new Promise((resolve, reject) => {
                        const request = indexedDB.open(this.dbName, 1);
                        request.onupgradeneeded = (e) => {
                            const db = e.target.result;
                            if (!db.objectStoreNames.contains(this.storeName)) {
                                db.createObjectStore(this.storeName, { keyPath: 'url' });
                            }
                        };
                        request.onsuccess = (e) => resolve(e.target.result);
                        request.onerror = (e) => reject(e.target.error);
                    });
                }
                return this.dbPromise;
            },

            async get(url) {
                try {
                    const db = await this.open();
                    return new Promise((resolve) => {
                        const tx = db.transaction(this.storeName, 'readonly');
                        const store = tx.objectStore(this.storeName);
                        const req = store.get(url);
                        req.onsuccess = () => {
                            const result = req.result;
                            if (result) {
                                const now = Math.floor(Date.now() / 1000);
                                if (result.expiresAt && result.expiresAt > 0 && now >= result.expiresAt) {
                                    this.remove(url);
                                    resolve(null);
                                } else {
                                    resolve(result.buffer);
                                }
                            } else {
                                resolve(null);
                            }
                        };
                        req.onerror = () => resolve(null);
                    });
                } catch (e) {
                    console.warn('IndexedDB read error:', e);
                    return null;
                }
            },

            async put(url, buffer, expiresAt) {
                try {
                    const db = await this.open();
                    return new Promise((resolve) => {
                        const tx = db.transaction(this.storeName, 'readwrite');
                        const store = tx.objectStore(this.storeName);
                        const req = store.put({ url: url, buffer: buffer, expiresAt: expiresAt, cachedAt: Date.now() });
                        req.onsuccess = () => resolve(true);
                        req.onerror = () => resolve(false);
                    });
                } catch (e) {
                    console.warn('IndexedDB write error:', e);
                    return false;
                }
            },

            async remove(url) {
                try {
                    const db = await this.open();
                    return new Promise((resolve) => {
                        const tx = db.transaction(this.storeName, 'readwrite');
                        const store = tx.objectStore(this.storeName);
                        const req = store.delete(url);
                        req.onsuccess = () => resolve(true);
                        req.onerror = () => resolve(false);
                    });
                } catch (e) {
                    return false;
                }
            },

            async clearAll() {
                try {
                    const db = await this.open();
                    return new Promise((resolve) => {
                        const tx = db.transaction(this.storeName, 'readwrite');
                        const store = tx.objectStore(this.storeName);
                        const req = store.clear();
                        req.onsuccess = () => resolve(true);
                        req.onerror = () => resolve(false);
                    });
                } catch (e) {
                    try { indexedDB.deleteDatabase(this.dbName); } catch(err) {}
                    return false;
                }
            }
        };

        async function loadPdfDocument() {
            loaderText.textContent = 'Checking local offline cache...';
            const cachedBuffer = await CBMDL_PDFCache.get(pdfUrl);
            
            if (cachedBuffer) {
                loaderText.textContent = 'Loading pages from local offline cache...';
                return pdfjsLib.getDocument({ data: new Uint8Array(cachedBuffer) }).promise;
            } else {
                loaderText.textContent = 'Streaming secure document from server...';
                const response = await fetch(pdfUrl, { credentials: 'include' });
                if (!response.ok) {
                    throw new Error('Server HTTP status ' + response.status);
                }
                const buffer = await response.arrayBuffer();
                // Cache into browser IndexedDB for instant offline access
                CBMDL_PDFCache.put(pdfUrl, buffer, expiresAtUnix);
                return pdfjsLib.getDocument({ data: new Uint8Array(buffer) }).promise;
            }
        }

        loadPdfDocument().then(function(doc) {
            pdfDoc = doc;
            numPages = doc.numPages;
            pageCountLabel.textContent = numPages;
            pageNumInput.max = numPages;

            return getPageProxy(1).then(function(page) {
                const originalViewport = page.getViewport({ scale: 1.0, rotation: 0 });
                const fitWidth = viewportScrollArea.clientWidth - 80;
                if (fitWidth > 0 && originalViewport.width > 0) {
                    // Use exact fit-width scale — same as the Fit button, no clamping
                    currentScale = fitWidth / originalViewport.width;
                    zoomPercentLabel.textContent = Math.round(currentScale * 100) + '%';
                }
                buildPagesLayout();
                // Render page 1 first; only then reveal UI & start sidebar build
                return renderPageIfNeeded(1);
            });
        }).then(function() {
            loader.style.opacity = '0';
            setTimeout(() => loader.style.display = 'none', 200);
            updateVirtualization();
            // Defer sidebar until after page-1 render completes + one paint frame
            requestAnimationFrame(() => buildThumbnailSidebar());
        }).catch(function(err) {
            console.error('Error loading secure PDF: ', err);
            CBMDL_PDFCache.clearAll();
            loaderText.innerHTML = '<div style="text-align:center; padding:24px 16px; background:rgba(239, 68, 68, 0.12); border:1px solid rgba(239, 68, 68, 0.35); border-radius:12px; margin:20px auto; max-width:460px;"><h3 style="color:#ef4444; margin:0 0 8px 0; font-size:17px;"><span class="fa-solid fa-clock-rotate-left"></span> e-Reading Permission Expired</h3><p style="color:#d1d5db; font-size:13px; margin:0 0 16px 0; line-height:1.5;">Your allocated reading session time for this book has expired or permission was revoked. Please request a new reading pass from your portal.</p><a href="<?= BASE_URL ?>?action=user&tab=books" style="display:inline-flex; align-items:center; gap:8px; background:#3b82f6; color:#ffffff; font-weight:600; font-size:13px; padding:10px 18px; border-radius:8px; text-decoration:none;"><i class="fa-solid fa-arrow-left"></i> Return to Books Catalog</a></div>';
        });

        // ── Shared page proxy cache (main render + thumbnails share this) ──
        function getPageProxy(num) {
            if (!pageProxies[num]) {
                pageProxies[num] = pdfDoc.getPage(num).then(function(page) {
                    const rot = ((page.rotate || 0) + currentRotation) % 360;
                    const base = page.getViewport({ scale: 1.0, rotation: rot });
                    pageBaseDims[num] = { width: base.width, height: base.height };
                    // Correct wrapper size once real dims are available
                    const wrapper = pageWrappers[num];
                    if (wrapper && !renderedPages.has(num)) {
                        wrapper.style.width  = Math.floor(base.width  * currentScale) + 'px';
                        wrapper.style.height = Math.floor(base.height * currentScale) + 'px';
                    }
                    return page;
                });
            }
            return pageProxies[num];
        }

        function buildPagesLayout() {
            viewportScrollArea.innerHTML = '';
            pageWrappers = new Array(numPages + 1).fill(null);
            renderedPages.clear();

            for (let i = 1; i <= numPages; i++) {
                const wrapper = document.createElement('div');
                wrapper.className = 'pdf-page-wrapper';
                wrapper.dataset.page = i;

                const inner = document.createElement('div');
                inner.className = 'page-inner';

                const placeholder = document.createElement('div');
                placeholder.className = 'page-placeholder';
                placeholder.innerHTML = '<span class="fa-solid fa-file-pdf"></span><span class="page-placeholder-num">' + i + '</span>';
                inner.appendChild(placeholder);

                wrapper.appendChild(inner);
                viewportScrollArea.appendChild(wrapper);
                pageWrappers[i] = wrapper;
            }

            applyPageSizing();
        }

        function applyPageSizing() {
            // Only size pages we have real dims for. Unknown pages are left alone
            // and corrected when getPageProxy(i) resolves — avoids applying
            // portrait dimensions to landscape pages (mixed-orientation PDFs).
            const template = pageBaseDims[1] || { width: 800, height: 1120 };

            for (let i = 1; i <= numPages; i++) {
                const wrapper = pageWrappers[i];
                if (!wrapper) continue;
                const dims = pageBaseDims[i] || (i === 1 ? template : null);
                if (!dims) continue;   // leave unknown pages at zero — getPageProxy will fix
                wrapper.style.width  = Math.floor(dims.width  * currentScale) + 'px';
                wrapper.style.height = Math.floor(dims.height * currentScale) + 'px';
            }
        }

        function renderPageIfNeeded(num) {
            if (renderedPages.has(num) || pendingRenders.has(num)) return Promise.resolve();
            pendingRenders.add(num);

            return getPageProxy(num).then(function(page) {
                pendingRenders.delete(num);
                const wrapper = pageWrappers[num];
                if (!wrapper) return;

                const dpr      = window.devicePixelRatio || 1;
                const rot      = ((page.rotate || 0) + currentRotation) % 360;
                const viewport = page.getViewport({ scale: currentScale * dpr, rotation: rot });

                const cssWidth  = Math.floor(viewport.width  / dpr);
                const cssHeight = Math.floor(viewport.height / dpr);

                wrapper.style.width  = cssWidth  + 'px';
                wrapper.style.height = cssHeight + 'px';

                const inner = wrapper.querySelector('.page-inner');
                let canvas = inner.querySelector('canvas');
                if (!canvas) {
                    canvas = document.createElement('canvas');
                    canvas.className = 'pdf-page-canvas';
                    inner.appendChild(canvas);
                }

                canvas.width  = Math.floor(viewport.width);
                canvas.height = Math.floor(viewport.height);
                canvas.style.width  = cssWidth  + 'px';
                canvas.style.height = cssHeight + 'px';

                const pageCtx = canvas.getContext('2d');
                pageCtx.imageSmoothingEnabled = true;
                pageCtx.imageSmoothingQuality = 'high';

                const renderTask = page.render({ canvasContext: pageCtx, viewport });
                renderTasks[num] = renderTask;

                return renderTask.promise.then(function() {
                    delete renderTasks[num];

                    // Security watermark
                    pageCtx.save();
                    pageCtx.font = 'bold ' + Math.max(16 * dpr, Math.round(canvas.width / 25)) + 'px sans-serif';
                    pageCtx.fillStyle = 'rgba(150, 150, 150, 0.18)';
                    pageCtx.textAlign = 'center';
                    pageCtx.textBaseline = 'middle';
                    pageCtx.translate(canvas.width / 2, canvas.height / 2);
                    pageCtx.rotate(-Math.PI / 6);
                    pageCtx.fillText('CBMDL SECURE DIGITAL READER - AUTHORIZED COPY', 0, 0);
                    pageCtx.restore();

                    const placeholder = inner.querySelector('.page-placeholder');
                    if (placeholder) placeholder.remove();
                    renderedPages.add(num);
                }).catch(function(err) {
                    delete renderTasks[num];
                    pendingRenders.delete(num);
                    if (err && err.name !== 'RenderingCancelledException') {
                        console.error('Page render error p' + num + ':', err);
                    }
                });
            });
        }

        function unrenderPage(num) {
            if (renderTasks[num]) {
                renderTasks[num].cancel();
                delete renderTasks[num];
            }
            pendingRenders.delete(num);
            if (!renderedPages.has(num)) return;

            const wrapper = pageWrappers[num];
            if (!wrapper) return;
            const inner  = wrapper.querySelector('.page-inner');
            const canvas = inner && inner.querySelector('canvas');
            if (canvas) {
                canvas.width  = 0;
                canvas.height = 0;
                canvas.remove();
            }
            if (inner && !inner.querySelector('.page-placeholder')) {
                const placeholder = document.createElement('div');
                placeholder.className = 'page-placeholder';
                placeholder.innerHTML = '<span class="fa-solid fa-file-pdf"></span><span class="page-placeholder-num">' + num + '</span>';
                inner.appendChild(placeholder);
            }
            renderedPages.delete(num);
        }

        function updateVirtualization() {
            if (!pdfDoc) return;
            const containerRect = viewportScrollArea.getBoundingClientRect();
            // Tighter buffer: half a viewport ahead/behind instead of a full one.
            // Prevents 4-6 simultaneous renders on load of tall monitors.
            const buffer = Math.max(containerRect.height * 0.5, 300);

            let bestPage = currentPageNum;
            let bestVisibleArea = -1;

            for (let i = 1; i <= numPages; i++) {
                const wrapper = pageWrappers[i];
                if (!wrapper) continue;
                const r = wrapper.getBoundingClientRect();
                const relTop    = r.top    - containerRect.top;
                const relBottom = r.bottom - containerRect.top;

                const inBufferRange = relBottom >= -buffer && relTop <= containerRect.height + buffer;
                if (inBufferRange) {
                    renderPageIfNeeded(i);
                } else if (renderedPages.has(i)) {
                    unrenderPage(i);
                }

                const visibleTop    = Math.max(relTop, 0);
                const visibleBottom = Math.min(relBottom, containerRect.height);
                const visibleArea   = Math.max(0, visibleBottom - visibleTop);
                if (visibleArea > bestVisibleArea) {
                    bestVisibleArea = visibleArea;
                    bestPage = i;
                }
            }

            if (bestVisibleArea > 0 && bestPage !== currentPageNum) {
                currentPageNum = bestPage;
                pageNumInput.value = bestPage;
                updateActiveThumbnail(bestPage);
            }
        }

        let scrollTicking = false;
        viewportScrollArea.addEventListener('scroll', function() {
            if (scrollTicking) return;
            scrollTicking = true;
            requestAnimationFrame(function() {
                updateVirtualization();
                scrollTicking = false;
            });
        }, { passive: true });

        let resizeTicking = false;
        window.addEventListener('resize', function() {
            if (resizeTicking) return;
            resizeTicking = true;
            requestAnimationFrame(function() {
                resizeTicking = false;
                if (!pdfDoc) return;
                // Recalculate fit-width scale for the new window size, then relayout
                getPageProxy(currentPageNum).then(function(page) {
                    const rot = ((page.rotate || 0) + currentRotation) % 360;
                    const ov  = page.getViewport({ scale: 1.0, rotation: rot });
                    const fw  = viewportScrollArea.clientWidth - 80;
                    if (fw > 0 && ov.width > 0) {
                        currentScale = fw / ov.width;
                        zoomPercentLabel.textContent = Math.round(currentScale * 100) + '%';
                        relayoutAndRerender();
                    }
                });
            });
        });

        function scrollToPage(num, smooth) {
            const wrapper = pageWrappers[num];
            if (!wrapper) return;
            wrapper.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'start' });
        }

        function relayoutAndRerender() {
            const anchorPage = currentPageNum;

            // Invalidate cached dims for the new rotation/scale
            Object.keys(pageProxies).forEach(function(num) {
                if (pageProxies[num]) {
                    pageProxies[num].then(function(page) {
                        const rot  = ((page.rotate || 0) + currentRotation) % 360;
                        const base = page.getViewport({ scale: 1.0, rotation: rot });
                        pageBaseDims[num] = { width: base.width, height: base.height };
                    });
                }
            });

            applyPageSizing();

            Object.keys(renderTasks).forEach(function(num) {
                renderTasks[num].cancel();
                delete renderTasks[num];
            });
            Array.from(renderedPages).forEach(function(num) {
                const wrapper = pageWrappers[num];
                const canvas  = wrapper && wrapper.querySelector('canvas');
                if (canvas) canvas.remove();
            });
            renderedPages.clear();

            requestAnimationFrame(function() {
                scrollToPage(anchorPage, false);
                updateVirtualization();
            });
        }

        // ── Thumbnail sidebar ─────────────────────────────────────────────
        function buildThumbnailSidebar() {
            const listContainer = document.getElementById('thumbnailList');
            listContainer.innerHTML = '';

            // Tighter rootMargin (60 px, was 120 px) + threshold 0 ensures the
            // observer fires only when the card edge actually enters the sidebar
            // scroll area, not speculatively for all cards at once.
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const pageNum = parseInt(entry.target.dataset.page);
                        observer.unobserve(entry.target);      // stop watching immediately
                        thumbEnqueue(pageNum, entry.target);   // through the concurrency queue
                    }
                });
            }, {
                root: listContainer,
                rootMargin: '60px 0px',
                threshold: 0
            });

            for (let i = 1; i <= numPages; i++) {
                const card = document.createElement('div');
                card.className = 'thumbnail-card';
                card.id = 'thumb-card-' + i;
                card.dataset.page = i;
                card.onclick = () => scrollToPage(i, true);

                const viewportDiv = document.createElement('div');
                viewportDiv.className = 'thumbnail-viewport';

                const icon = document.createElement('span');
                icon.className = 'thumbnail-placeholder fa-solid fa-file-pdf';
                viewportDiv.appendChild(icon);

                const canvas = document.createElement('canvas');
                canvas.id = 'thumb-canvas-' + i;
                canvas.style.display = 'none';  // hidden until painted to avoid flicker
                viewportDiv.appendChild(canvas);

                card.appendChild(viewportDiv);

                const label = document.createElement('div');
                label.className = 'thumbnail-num';
                label.textContent = i;
                card.appendChild(label);

                listContainer.appendChild(card);
                observer.observe(card);
            }

            updateActiveThumbnail(1);
        }

        // Returns a Promise — required for the concurrency queue's .finally()
        function renderThumbnail(num, cardElement) {
            // Reuse the shared proxy cache — zero duplicate page fetches
            return getPageProxy(num).then(function(page) {
                const canvas = document.getElementById('thumb-canvas-' + num);
                const icon   = cardElement ? cardElement.querySelector('.thumbnail-placeholder') : null;
                if (!canvas) return;

                const originalViewport = page.getViewport({ scale: 1.0 });
                const baseScale = 140 / originalViewport.width;
                const dpr = window.devicePixelRatio || 1;
                const viewport = page.getViewport({ scale: baseScale * dpr });

                const cssWidth  = Math.floor(viewport.width  / dpr);
                const cssHeight = Math.floor(viewport.height / dpr);

                canvas.width  = Math.floor(viewport.width);
                canvas.height = Math.floor(viewport.height);
                canvas.style.width  = cssWidth  + 'px';
                canvas.style.height = cssHeight + 'px';

                const thumbCtx = canvas.getContext('2d');
                thumbCtx.imageSmoothingEnabled = true;
                thumbCtx.imageSmoothingQuality = 'high';

                return page.render({ canvasContext: thumbCtx, viewport }).promise.then(() => {
                    canvas.style.display = '';   // reveal only after paint is complete
                    if (icon) icon.remove();
                });
            }).catch(function(err) {
                if (err && err.name !== 'RenderingCancelledException') {
                    console.warn('Thumbnail render error p' + num + ':', err);
                }
            });
        }

        function updateActiveThumbnail(num) {
            document.querySelectorAll('.thumbnail-card').forEach(c => c.classList.remove('active'));
            const card = document.getElementById('thumb-card-' + num);
            if (card) {
                card.classList.add('active');
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        document.getElementById('prevPageBtn').onclick = () => {
            if (currentPageNum <= 1) return;
            scrollToPage(currentPageNum - 1, true);
        };

        document.getElementById('nextPageBtn').onclick = () => {
            if (currentPageNum >= numPages) return;
            scrollToPage(currentPageNum + 1, true);
        };

        pageNumInput.onchange = (e) => {
            let val = parseInt(e.target.value);
            if (isNaN(val) || val < 1) val = 1;
            if (val > numPages) val = numPages;
            scrollToPage(val, false);
        };

        document.getElementById('zoomInBtn').onclick = () => {
            if (currentScale >= 4.0) return;
            currentScale = +(currentScale + 0.2).toFixed(2);
            zoomPercentLabel.textContent = Math.round(currentScale * 100) + '%';
            relayoutAndRerender();
        };

        document.getElementById('zoomOutBtn').onclick = () => {
            if (currentScale <= 0.3) return;
            currentScale = +(currentScale - 0.2).toFixed(2);
            zoomPercentLabel.textContent = Math.round(currentScale * 100) + '%';
            relayoutAndRerender();
        };

        document.getElementById('zoomFitBtn').onclick = () => {
            getPageProxy(currentPageNum).then(function(page) {
                const originalViewport = page.getViewport({ scale: 1.0, rotation: currentRotation });
                const fitWidth = viewportScrollArea.clientWidth - 80;
                currentScale = fitWidth / originalViewport.width;
                zoomPercentLabel.textContent = Math.round(currentScale * 100) + '%';
                relayoutAndRerender();
            });
        };

        // Ctrl + Mouse Wheel Zooming
        viewportScrollArea.addEventListener('wheel', function(e) {
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                if (e.deltaY < 0) {
                    if (currentScale < 4.0) {
                        currentScale = +(currentScale + 0.15).toFixed(2);
                        zoomPercentLabel.textContent = Math.round(currentScale * 100) + '%';
                        relayoutAndRerender();
                    }
                } else if (e.deltaY > 0) {
                    if (currentScale > 0.3) {
                        currentScale = +(currentScale - 0.15).toFixed(2);
                        zoomPercentLabel.textContent = Math.round(currentScale * 100) + '%';
                        relayoutAndRerender();
                    }
                }
            }
        }, { passive: false });

        // Keyboard shortcuts (+ / - / 0) for Zoom
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                if (e.key === '=' || e.key === '+' || e.code === 'NumpadAdd') {
                    e.preventDefault();
                    document.getElementById('zoomInBtn').click();
                } else if (e.key === '-' || e.code === 'NumpadSubtract') {
                    e.preventDefault();
                    document.getElementById('zoomOutBtn').click();
                } else if (e.key === '0' || e.code === 'Numpad0') {
                    e.preventDefault();
                    document.getElementById('zoomFitBtn').click();
                }
            }
        });

        document.getElementById('rotateBtn').onclick = () => {
            currentRotation = (currentRotation + 90) % 360;
            relayoutAndRerender();
        };

        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        sidebarToggle.onclick = () => {
            sidebar.classList.toggle('collapsed');
            sidebarToggle.classList.toggle('active');
        };

        const fullscreenBtn = document.getElementById('fullscreenBtn');
        fullscreenBtn.onclick = () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => {
                    fullscreenBtn.innerHTML = '<span class="fa-solid fa-compress"></span> Exit';
                }).catch(err => console.error(err));
            } else {
                document.exitFullscreen().then(() => {
                    fullscreenBtn.innerHTML = '<span class="fa-expand fa-solid"></span> Fullscreen';
                });
            }
        };

        document.addEventListener('fullscreenchange', () => {
            if (!document.fullscreenElement) {
                fullscreenBtn.innerHTML = '<span class="fa-expand fa-solid"></span> Fullscreen';
            }
        });

        // ── Live e-Reading Countdown Timer ────────────────────────────────
        let remainingSeconds = <?= max(0, $expiresAtUnix - time()) ?>;
        if (expiresAtUnix > 0) {
            const timerText  = document.getElementById('pdfTimerText');
            const timerBadge = document.getElementById('pdfTimerBadge');

            function updateReaderTimer() {
                if (remainingSeconds <= 0) {
                    if (timerText)  timerText.textContent = '00m 00s Expired';
                    if (timerBadge) {
                        timerBadge.style.background  = 'rgba(239, 68, 68, 0.4)';
                        timerBadge.style.borderColor = '#ef4444';
                        timerBadge.style.color       = '#ffffff';
                    }
                    CBMDL_PDFCache.clearAll().finally(() => {
                        if (window.opener && !window.opener.closed) {
                            try {
                                if (window.opener.spaTabCache) window.opener.spaTabCache.clear();
                                if (typeof window.opener.navigateToUrl === 'function') {
                                    window.opener.navigateToUrl(window.opener.location.href, false, false);
                                } else {
                                    window.opener.location.reload();
                                }
                            } catch(e) {}
                        }
                        alert('⏱️ Your active e-reading session time has expired.');
                        window.close();
                        window.location.href = '<?= BASE_URL ?>?action=user&tab=books';
                    });
                    return;
                }

                const mins = Math.floor(remainingSeconds / 60);
                const secs = remainingSeconds % 60;
                if (timerText) timerText.textContent = String(mins).padStart(2, '0') + 'm ' + String(secs).padStart(2, '0') + 's';

                if (remainingSeconds <= 60) {
                    if (timerBadge) {
                        timerBadge.style.background  = 'rgba(220, 38, 38, 0.35)';
                        timerBadge.style.borderColor = '#dc2626';
                        timerBadge.style.color       = '#fca5a5';
                    }
                } else if (remainingSeconds <= 180) {
                    if (timerBadge) {
                        timerBadge.style.background  = 'rgba(245, 158, 11, 0.25)';
                        timerBadge.style.borderColor = 'rgba(245, 158, 11, 0.6)';
                        timerBadge.style.color       = '#fbbf24';
                    }
                }
                remainingSeconds--;
            }

            updateReaderTimer();
            setInterval(updateReaderTimer, 1000);
        }
    </script>
</body>
</html>