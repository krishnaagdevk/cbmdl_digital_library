<?php 
require 'config.php';
date_default_timezone_set('Asia/Kolkata');

$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$pathRoute = trim(str_replace('/cbmdl', '', $requestUri), '/');

if (isset($_GET['action'])) {
    $action = $_GET['action'];
} elseif ($pathRoute === 'member-login') {
    $action = 'member_login';
} elseif ($pathRoute === 'admin-login') {
    $action = 'admin_login';
} else {
    $action = 'home';
}

// Redirect default root/home to member-login or active dashboard
if ($action === 'home' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if (admin()) {
        go('?action=admin');
    } elseif (member()) {
        go('?action=user');
    } else {
        go('member-login');
    }
}

// CSRF Verification for all state-changing POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

// If already logged in, redirect away from login pages (GET requests)
if (in_array($action, ['admin_login', 'member_login']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if (admin()) {
        go('?action=admin');
    } elseif (member()) {
        go('?action=user');
    }
}

// 1. Authentication Handlers with Session Fixation Defense
if ($action === 'logout') {
    $was_admin = admin();
    session_destroy();
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    flash('Logged out successfully.');
    go($was_admin ? 'admin-login' : 'member-login');
}

if ($action === 'admin_login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_regenerate_id(true); // Mitigate Session Fixation
    (new App\Controllers\AuthController($db))->adminLogin();
}

if ($action === 'member_login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_regenerate_id(true); // Mitigate Session Fixation
    (new App\Controllers\AuthController($db))->memberLogin();
}



// 2. Automated Fine Calculation Helper
function calculate_fine($due_date, $returned_at = null) {
    $due = strtotime($due_date);
    $end = $returned_at ? strtotime($returned_at) : time();
    
    // Normalize timestamps to date only
    $due_day = strtotime(date('Y-m-d', $due));
    $end_day = strtotime(date('Y-m-d', $end));
    
    if ($end_day > $due_day) {
        $diff = $end_day - $due_day;
        $days = (int)ceil($diff / (60 * 60 * 24));
        $rate = 0.00; // ₹5 per day
        return [
            'days' => $days,
            'fine' => $days * $rate
        ];
    }
    return ['days' => 0, 'fine' => 0.00];
}

// 3. Secure PDF Streaming Routes (Prepared Statements & Access Control)
if ($action === 'download_pdf') {
    if (!admin()) exit('Unauthorized');
    $id = (int)($_GET['id'] ?? 0);
    
    $stmt = $db->prepare("SELECT * FROM ebooks WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $b = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($b) {
        $file = 'uploads/' . basename($b['pdf_file']);
        if (is_file($file)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($b['title']) . '.pdf"');
            header('X-Content-Type-Options: nosniff');
            readfile($file);
            exit;
        }
    }
    exit('File not found.');
}

if ($action === 'read_pdf') {
    if (!member()) exit('Unauthorized');
    $id = (int)($_GET['id'] ?? 0);
    $mid = (int)$_SESSION['member'];
    
    $stmt = $db->prepare("SELECT r.*, e.pdf_file, e.title FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.member_id = ? AND r.ebook_id = ? AND r.status = 'Approved' AND r.expires_at > NOW() ORDER BY r.id DESC LIMIT 1");
    $stmt->bind_param("ii", $mid, $id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$r) exit('No active permission for this book.');
    $file = 'uploads/' . basename($r['pdf_file']);
    if (is_file($file)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        readfile($file);
        exit;
    }
    exit('File not found.');
}

// 4. Role-based Navigation Guard
if (!admin() && !member() && !in_array($action, ['home', 'admin_login', 'member_login'])) {
    go('index.php');
}

if ($action === 'secure_pdf_viewer') {
    $id = (int)($_GET['id'] ?? 0);
    $source = $_GET['source'] ?? '';
    
    $pdfTitle = 'Secure Interactive Reader';
    $streamUrl = '';
    
    if ($source === 'admin') {
        if (!admin()) exit('Unauthorized');
        $stmt = $db->prepare("SELECT title FROM ebooks WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $b = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$b) exit('Book not found.');
        $pdfTitle = $b['title'];
        $streamUrl = BASE_URL . '?action=view_pdf_content&id=' . $id;
    } elseif ($source === 'member') {
        if (!member()) exit('Unauthorized');
        $mid = (int)$_SESSION['member'];
        $stmt = $db->prepare("SELECT r.id, e.title FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.id = ? AND r.member_id = ? AND r.status = 'Approved' AND r.expires_at > NOW() LIMIT 1");
        $stmt->bind_param("ii", $id, $mid);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$r) exit('No active permission for this book.');
        $pdfTitle = $r['title'];
        $streamUrl = BASE_URL . '?action=read_member_pdf_content&id=' . $id;
    } else {
        exit('Invalid source specifier.');
    }
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
                align-items: center;
                justify-content: center;
                padding: 40px;
                background-color: var(--bg-primary);
                position: relative;
            }

            .canvas-shadow-box {
                position: relative;
                background-color: #ffffff;
                border-radius: 4px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.5);
                transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }

            #mainCanvas {
                display: block;
                max-width: 100%;
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

            <section class="viewport-scroll-area">
                <div class="canvas-shadow-box" id="canvasBox">
                    <canvas id="mainCanvas"></canvas>
                </div>
            </section>
        </main>

        <script>
            pdfjsLib.GlobalWorkerOptions.workerSrc = '<?= BASE_URL ?>js/pdf.worker.min.js';

            const pdfUrl = '<?= $streamUrl ?>';
            let pdfDoc = null;
            let currentPageNum = 1;
            let currentScale = 1.0;
            let currentRotation = 0;
            let pageRendering = false;
            let pageNumPending = null;

            const mainCanvas = document.getElementById('mainCanvas');
            const ctx = mainCanvas.getContext('2d');
            const canvasBox = document.getElementById('canvasBox');
            const loader = document.getElementById('loader');
            const loaderText = document.getElementById('loaderText');
            const pageNumInput = document.getElementById('pageNumInput');
            const pageCountLabel = document.getElementById('pageCount');
            const zoomPercentLabel = document.getElementById('zoomPercent');
            const viewportScrollArea = document.querySelector('.viewport-scroll-area');
            let lastTransitionTime = 0;
            const COOLDOWN_MS = 600;

            document.addEventListener('contextmenu', e => e.preventDefault());
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && ['s', 'S', 'p', 'P', 'u', 'U'].includes(e.key)) {
                    e.preventDefault();
                    return false;
                }
                if (e.key === 'F12' || ((e.ctrlKey || e.metaKey) && e.shiftKey && ['I', 'i', 'C', 'c', 'J', 'j'].includes(e.key))) {
                    e.preventDefault();
                    return false;
                }
            });

            loaderText.textContent = 'Loading pages securely...';
            
            pdfjsLib.getDocument({
                url: pdfUrl,
                withCredentials: true
            }).promise.then(function(doc) {
                pdfDoc = doc;
                pageCountLabel.textContent = doc.numPages;
                pageNumInput.max = doc.numPages;
                
                buildThumbnailSidebar();
                
                renderPage(currentPageNum).then(() => {
                    loader.style.opacity = '0';
                    setTimeout(() => loader.style.display = 'none', 300);
                });
            }).catch(function(err) {
                console.error('Error loading secure PDF: ', err);
                loaderText.innerHTML = '<span style="color:#ef4444;"><span class="fa-solid fa-triangle-exclamation"></span> Error accessing file content or your e-reading permission has expired.</span>';
            });

            function renderPage(num) {
                pageRendering = true;
                return pdfDoc.getPage(num).then(function(page) {
                    const viewport = page.getViewport({ scale: currentScale, rotation: currentRotation });
                    
                    mainCanvas.height = viewport.height;
                    mainCanvas.width = viewport.width;
                    
                    canvasBox.style.width = viewport.width + 'px';
                    canvasBox.style.height = viewport.height + 'px';

                    const renderContext = {
                        canvasContext: ctx,
                        viewport: viewport
                    };
                    
                    const renderTask = page.render(renderContext);
                    
                    return renderTask.promise.then(function() {
                        // Apply security watermark overlay on canvas
                        ctx.save();
                        ctx.font = 'bold ' + Math.max(16, Math.round(viewport.width / 25)) + 'px sans-serif';
                        ctx.fillStyle = 'rgba(150, 150, 150, 0.18)';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.translate(viewport.width / 2, viewport.height / 2);
                        ctx.rotate(-Math.PI / 6);
                        ctx.fillText('CBMDL SECURE DIGITAL READER - AUTHORIZED COPY', 0, 0);
                        ctx.restore();

                        pageRendering = false;
                        updateActiveThumbnail(num);
                        
                        if (pageNumPending !== null) {
                            renderPage(pageNumPending);
                            pageNumPending = null;
                        }
                    });
                });
            }

            function queueRenderPage(num) {
                pageNumInput.value = num;
                if (pageRendering) {
                    pageNumPending = num;
                    return new Promise((resolve) => {
                        const checkInterval = setInterval(() => {
                            if (!pageRendering) {
                                clearInterval(checkInterval);
                                resolve();
                            }
                        }, 30);
                    });
                } else {
                    return renderPage(num);
                }
            }

            function buildThumbnailSidebar() {
                const listContainer = document.getElementById('thumbnailList');
                listContainer.innerHTML = '';
                
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const pageNum = parseInt(entry.target.dataset.page);
                            renderThumbnail(pageNum, entry.target);
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    root: listContainer,
                    rootMargin: '120px 0px',
                    threshold: 0.05
                });

                for (let i = 1; i <= pdfDoc.numPages; i++) {
                    const card = document.createElement('div');
                    card.className = 'thumbnail-card';
                    card.id = 'thumb-card-' + i;
                    card.dataset.page = i;
                    card.onclick = () => {
                        currentPageNum = i;
                        queueRenderPage(i).then(() => {
                            viewportScrollArea.scrollTop = 0;
                        });
                    };

                    const viewportDiv = document.createElement('div');
                    viewportDiv.className = 'thumbnail-viewport';
                    
                    const icon = document.createElement('span');
                    icon.className = 'thumbnail-placeholder fa-solid fa-file-pdf';
                    viewportDiv.appendChild(icon);

                    const canvas = document.createElement('canvas');
                    canvas.id = 'thumb-canvas-' + i;
                    viewportDiv.appendChild(canvas);

                    card.appendChild(viewportDiv);

                    const label = document.createElement('div');
                    label.className = 'thumbnail-num';
                    label.textContent = i;
                    card.appendChild(label);

                    listContainer.appendChild(card);
                    observer.observe(card);
                }
            }

            function renderThumbnail(num, cardElement) {
                pdfDoc.getPage(num).then(function(page) {
                    const canvas = document.getElementById('thumb-canvas-' + num);
                    const icon = cardElement.querySelector('.thumbnail-placeholder');
                    if (!canvas) return;
                    
                    const thumbCtx = canvas.getContext('2d');
                    
                    const originalViewport = page.getViewport({ scale: 1.0 });
                    const scale = 140 / originalViewport.width;
                    const viewport = page.getViewport({ scale: scale });

                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    const renderContext = {
                        canvasContext: thumbCtx,
                        viewport: viewport
                    };

                    page.render(renderContext).promise.then(() => {
                        if (icon) icon.remove();
                    });
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
                currentPageNum--;
                queueRenderPage(currentPageNum).then(() => {
                    viewportScrollArea.scrollTop = 0;
                });
            };

            document.getElementById('nextPageBtn').onclick = () => {
                if (currentPageNum >= pdfDoc.numPages) return;
                currentPageNum++;
                queueRenderPage(currentPageNum).then(() => {
                    viewportScrollArea.scrollTop = 0;
                });
            };

            pageNumInput.onchange = (e) => {
                let val = parseInt(e.target.value);
                if (isNaN(val) || val < 1) val = 1;
                if (val > pdfDoc.numPages) val = pdfDoc.numPages;
                currentPageNum = val;
                queueRenderPage(val).then(() => {
                    viewportScrollArea.scrollTop = 0;
                });
            };

            document.getElementById('zoomInBtn').onclick = () => {
                if (currentScale >= 3.0) return;
                currentScale += 0.2;
                zoomPercentLabel.textContent = Math.round(currentScale * 100) + '%';
                queueRenderPage(currentPageNum);
            };

            document.getElementById('zoomOutBtn').onclick = () => {
                if (currentScale <= 0.5) return;
                currentScale -= 0.2;
                zoomPercentLabel.textContent = Math.round(currentScale * 100) + '%';
                queueRenderPage(currentPageNum);
            };

            document.getElementById('zoomFitBtn').onclick = () => {
                const viewportScrollArea = document.querySelector('.viewport-scroll-area');
                pdfDoc.getPage(currentPageNum).then(function(page) {
                    const originalViewport = page.getViewport({ scale: 1.0, rotation: currentRotation });
                    const fitWidth = viewportScrollArea.clientWidth - 80;
                    currentScale = fitWidth / originalViewport.width;
                    zoomPercentLabel.textContent = Math.round(currentScale * 100) + '%';
                    queueRenderPage(currentPageNum);
                });
            };

            document.getElementById('rotateBtn').onclick = () => {
                currentRotation = (currentRotation + 90) % 360;
                queueRenderPage(currentPageNum);
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

            // Smooth hybrid continuous scroll transition handlers (up-to-down & top-to-up)
            viewportScrollArea.addEventListener('wheel', function(e) {
                const now = Date.now();
                if (now - lastTransitionTime < COOLDOWN_MS) {
                    return;
                }

                if (e.deltaY > 0) {
                    // Scroll down: detect if viewport scroll has reached the bottom
                    const isAtBottom = viewportScrollArea.scrollHeight - viewportScrollArea.scrollTop <= viewportScrollArea.clientHeight + 10;
                    if (isAtBottom) {
                        if (currentPageNum < pdfDoc.numPages) {
                            lastTransitionTime = now;
                            currentPageNum++;
                            queueRenderPage(currentPageNum).then(() => {
                                viewportScrollArea.scrollTop = 0; // instantly reset next page to top
                            });
                            e.preventDefault();
                        }
                    }
                } else if (e.deltaY < 0) {
                    // Scroll up: detect if viewport scroll has reached the top
                    const isAtTop = viewportScrollArea.scrollTop <= 10;
                    if (isAtTop) {
                        if (currentPageNum > 1) {
                            lastTransitionTime = now;
                            currentPageNum--;
                            queueRenderPage(currentPageNum).then(() => {
                                // Jump directly to the bottom of the previous page
                                setTimeout(() => {
                                    viewportScrollArea.scrollTop = viewportScrollArea.scrollHeight - viewportScrollArea.clientHeight;
                                }, 30);
                            });
                            e.preventDefault();
                        }
                    }
                }
            }, { passive: false });

            // Mobile and trackpad touch swipe-based transition handlers
            let touchStartY = 0;
            viewportScrollArea.addEventListener('touchstart', function(e) {
                if (e.touches.length === 1) {
                    touchStartY = e.touches[0].clientY;
                }
            }, { passive: true });

            viewportScrollArea.addEventListener('touchmove', function(e) {
                if (e.touches.length !== 1) return;
                const now = Date.now();
                if (now - lastTransitionTime < COOLDOWN_MS) return;

                const touchCurrentY = e.touches[0].clientY;
                const diffY = touchStartY - touchCurrentY;

                if (diffY > 15) { // Swipe up (scroll down)
                    const isAtBottom = viewportScrollArea.scrollHeight - viewportScrollArea.scrollTop <= viewportScrollArea.clientHeight + 15;
                    if (isAtBottom && currentPageNum < pdfDoc.numPages) {
                        lastTransitionTime = now;
                        currentPageNum++;
                        queueRenderPage(currentPageNum).then(() => {
                            viewportScrollArea.scrollTop = 0;
                        });
                    }
                } else if (diffY < -15) { // Swipe down (scroll up)
                    const isAtTop = viewportScrollArea.scrollTop <= 15;
                    if (isAtTop && currentPageNum > 1) {
                        lastTransitionTime = now;
                        currentPageNum--;
                        queueRenderPage(currentPageNum).then(() => {
                            setTimeout(() => {
                                viewportScrollArea.scrollTop = viewportScrollArea.scrollHeight - viewportScrollArea.clientHeight;
                            }, 30);
                        });
                    }
                }
            }, { passive: true });
        </script>
    </body>
    </html>
    <?php
    exit;
}

// 5. Admin Controllers (Prepared Statements & Secure Actions)
if (admin()) {
    $admin_id = (int)$_SESSION['admin'];

    if ($action === 'poll_admin_notifications') {
        header('Content-Type: application/json');
        
        $req_count = (int)$db->query("SELECT COUNT(*) c FROM reading_requests WHERE status='Pending'")->fetch_assoc()['c'];
        $prt_count = (int)$db->query("SELECT COUNT(*) c FROM print_requests WHERE status='Pending'")->fetch_assoc()['c'];
        
        $read_query = $db->query("
            SELECT r.id, e.title, m.name as member_name, r.requested_at 
            FROM reading_requests r 
            JOIN ebooks e ON e.id = r.ebook_id 
            JOIN members m ON m.id = r.member_id 
            WHERE r.status = 'Pending' 
            ORDER BY r.id DESC LIMIT 5
        ");
        $recent_reading = [];
        while ($row = $read_query->fetch_assoc()) {
            $recent_reading[] = [
                'id' => (int)$row['id'],
                'type' => 'reading',
                'title' => $row['title'],
                'member' => $row['member_name'],
                'time' => $row['requested_at']
            ];
        }
        
        $print_query = $db->query("
            SELECT p.id, e.title, m.name as member_name, p.pages, p.requested_at 
            FROM print_requests p 
            JOIN ebooks e ON e.id = p.ebook_id 
            JOIN members m ON m.id = p.member_id 
            WHERE p.status = 'Pending' 
            ORDER BY p.id DESC LIMIT 5
        ");
        $recent_print = [];
        while ($row = $print_query->fetch_assoc()) {
            $recent_print[] = [
                'id' => (int)$row['id'],
                'type' => 'print',
                'title' => $row['title'],
                'member' => $row['member_name'],
                'pages' => $row['pages'],
                'time' => $row['requested_at']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'reading_pending_count' => $req_count,
            'print_pending_count' => $prt_count,
            'recent_reading' => $recent_reading,
            'recent_print' => $recent_print
        ]);
        exit;
    }

    if ($action === 'update_admin_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        if ($username === '') {
            flash('User ID is required.');
            go('?action=admin&tab=profile');
        }
        
        if ($password !== '') {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE admins SET username = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssi", $username, $hashed, $admin_id);
        } else {
            $stmt = $db->prepare("UPDATE admins SET username = ? WHERE id = ?");
            $stmt->bind_param("si", $username, $admin_id);
        }
        
        $ok = $stmt->execute();
        $stmt->close();
        flash($ok ? 'Admin profile updated successfully.' : 'User ID already exists.');
        go('?action=admin&tab=profile');
    }

    if ($action === 'add_category' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $n = trim($_POST['name'] ?? '');
        if ($n !== '') {
            $stmt = $db->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $n);
            $stmt->execute();
            $stmt->close();
            flash('Category saved.');
        } else {
            flash('Category name cannot be empty.');
        }
        go('?action=admin&tab=categories');
    }

    if ($action === 'delete_category' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        // Clean up E-book PDF files from disk belonging to ebooks under this category to avoid storage leaks
        $ebsStmt = $db->prepare("SELECT pdf_file FROM ebooks WHERE category_id = ?");
        if ($ebsStmt) {
            $ebsStmt->bind_param("i", $id);
            $ebsStmt->execute();
            $res = $ebsStmt->get_result();
            while ($eb = $res->fetch_assoc()) {
                $f_file = 'uploads/' . basename($eb['pdf_file']);
                if (is_file($f_file)) {
                    @unlink($f_file);
                }
            }
            $ebsStmt->close();
        }
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        flash('Category and all associated e-books deleted.');
        go('?action=admin&tab=categories');
    }

    if ($action === 'view_ebook_pdf') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM ebooks WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $b = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$b) exit('Book not found.');
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <link rel="icon" type="image/x-icon" href="images/favicon.ico">
            <meta charset="utf-8">
            <title><?= e($b['title']) ?> - Reader</title>
            <style>
                body { margin:0; padding:0; background-color:#1e293b; }
                iframe { width:100%; height:100vh; border:none; }
                * { user-select:none; -webkit-user-select:none; -moz-user-select:none; -ms-user-select:none; }
                @media print { body { display:none !important; } }
            </style>
        </head>
        <body>
            <iframe src="<?= BASE_URL ?>?action=secure_pdf_viewer&source=admin&id=<?= urlencode($b['id']) ?>" style="pointer-events:auto;"></iframe>
        </body>
        </html>
        <?php 
        exit;
    }

    if ($action === 'view_pdf_content') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM ebooks WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $b = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($b) {
            $file = 'uploads/' . basename($b['pdf_file']);
            stream_file_ranged($file, 'application/pdf', true, 300);
        }
        exit('File not found.');
    }

    if ($action === 'upload_chunk' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (!admin()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $csrf_header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($csrf_header) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf_header)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'CSRF verification failed']);
            exit;
        }
        
        if (isset($_FILES['chunk']['error']) && $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
            $err_code = $_FILES['chunk']['error'];
            $err_msg = "PHP Upload Error Code: " . $err_code;
            if ($err_code === 1 || $err_code === 2) {
                $err_msg = "The uploaded chunk exceeds the upload_max_filesize directive in php.ini.";
            } elseif ($err_code === 3) {
                $err_msg = "The file was only partially uploaded.";
            } elseif ($err_code === 4) {
                $err_msg = "No file chunk was uploaded.";
            }
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $err_msg]);
            exit;
        }
        
        $upload_id = $_POST['upload_id'] ?? '';
        if (!preg_match('/^[a-f0-9\-]{36}$/i', $upload_id)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid upload UUID format']);
            exit;
        }
        
        $chunk_index = isset($_POST['chunk_index']) ? (int)$_POST['chunk_index'] : -1;
        $total_chunks = isset($_POST['total_chunks']) ? (int)$_POST['total_chunks'] : 0;
        
        if ($chunk_index < 0 || $total_chunks <= 0 || empty($_FILES['chunk']['tmp_name'])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Malformed chunk upload request parameters or empty temporary chunk file.']);
            exit;
        }
        
        $chunk_dir = 'uploads/chunks/' . $upload_id;
        if (!is_dir($chunk_dir)) {
            mkdir($chunk_dir, 0777, true);
        }
        
        $padded_index = str_pad($chunk_index, 5, '0', STR_PAD_LEFT);
        $dest_file = $chunk_dir . '/' . $padded_index;
        
        if (move_uploaded_file($_FILES['chunk']['tmp_name'], $dest_file)) {
            echo json_encode(['ok' => true, 'received' => $chunk_index]);
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Failed to persist temporary chunk slice to disk']);
        }
        exit;
    }

    if ($action === 'assemble_upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (!admin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'CSRF verification failed']);
            exit;
        }
        
        $upload_id = $_POST['upload_id'] ?? '';
        if (!preg_match('/^[a-f0-9\-]{36}$/i', $upload_id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid upload UUID format']);
            exit;
        }
        
        $total_chunks = isset($_POST['total_chunks']) ? (int)$_POST['total_chunks'] : 0;
        $chunk_dir = 'uploads/chunks/' . $upload_id;
        
        if (!is_dir($chunk_dir) || $total_chunks <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No active chunk directory found for this session']);
            exit;
        }
        
        $chunks = glob($chunk_dir . '/*');
        if (count($chunks) !== $total_chunks) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Chunk count mismatch. Expected ' . $total_chunks . ', found ' . count($chunks)]);
            exit;
        }
        
        sort($chunks);
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        
        $out_name = uniqid('book_') . '.pdf';
        $out_path = 'uploads/' . $out_name;
        
        $out = fopen($out_path, 'wb');
        if (!$out) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to initialize output destination file stream']);
            exit;
        }
        
        foreach ($chunks as $c_file) {
            $in = fopen($c_file, 'rb');
            if ($in) {
                while (!feof($in)) {
                    $buf = fread($in, 8192);
                    fwrite($out, $buf);
                }
                fclose($in);
            }
        }
        fclose($out);
        
        // Verify MIME type using PHP finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $out_path);
        finfo_close($finfo);
        
        if ($mime !== 'application/pdf') {
            unlink($out_path);
            array_map('unlink', glob($chunk_dir . '/*'));
            rmdir($chunk_dir);
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Validation error: Assembled file content-type is not a valid PDF.']);
            exit;
        }
        
        // Delete raw chunk files and sub-directory
        array_map('unlink', glob($chunk_dir . '/*'));
        rmdir($chunk_dir);
        
        $c = (int)($_POST['category_id'] ?? 0);
        $t = trim($_POST['title'] ?? '');
        $k = trim($_POST['keywords'] ?? '');
        $edit_id = (int)($_POST['ebook_id'] ?? 0);
        
        if ($edit_id > 0) {
            // Delete old file
            $oldStmt = $db->prepare("SELECT pdf_file FROM ebooks WHERE id = ?");
            $oldStmt->bind_param("i", $edit_id);
            $oldStmt->execute();
            $old_row = $oldStmt->get_result()->fetch_assoc();
            $old_pdf = $old_row['pdf_file'] ?? '';
            $oldStmt->close();
            
            if ($old_pdf !== '') {
                $old_file = 'uploads/' . basename($old_pdf);
                if (is_file($old_file)) {
                    unlink($old_file);
                }
            }
            
            $stmt = $db->prepare("UPDATE ebooks SET category_id = ?, title = ?, keywords = ?, pdf_file = ? WHERE id = ?");
            $stmt->bind_param("isssi", $c, $t, $k, $out_name, $edit_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $db->prepare("INSERT INTO ebooks (category_id, title, keywords, pdf_file) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $c, $t, $k, $out_name);
            $stmt->execute();
            $stmt->close();
        }
        
        echo json_encode(['success' => true, 'filename' => $out_name]);
        exit;
    }

    if ($action === 'cancel_upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (!admin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'CSRF verification failed']);
            exit;
        }
        
        $upload_id = $_POST['upload_id'] ?? '';
        if (preg_match('/^[a-f0-9\-]{36}$/i', $upload_id)) {
            $chunk_dir = 'uploads/chunks/' . $upload_id;
            if (is_dir($chunk_dir)) {
                array_map('unlink', glob($chunk_dir . '/*'));
                rmdir($chunk_dir);
            }
        }
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'export_ebooks_csv') {
        if (!admin()) {
            http_response_code(403);
            exit('Unauthorized');
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=ebooks_backup_' . date('Ymd_His') . '.csv');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        fputcsv($output, ['Category', 'Title', 'Keywords']);
        
        $query = $db->query("SELECT e.*, c.name as category_name FROM ebooks e LEFT JOIN categories c ON c.id = e.category_id ORDER BY e.id ASC");
        while ($row = $query->fetch_assoc()) {
            fputcsv($output, [
                $row['category_name'] ?? 'General',
                $row['title'],
                $row['keywords']
            ]);
        }
        fclose($output);
        exit;
    }

    if ($action === 'export_physical_csv') {
        if (!admin()) {
            http_response_code(403);
            exit('Unauthorized');
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=physical_books_backup_' . date('Ymd_His') . '.csv');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        fputcsv($output, ['Category', 'Book Code', 'Title', 'Author', 'Publisher', 'Price', 'Rack Location']);
        
        $query = $db->query("SELECT * FROM physical_books ORDER BY id ASC");
        while ($row = $query->fetch_assoc()) {
            fputcsv($output, [
                'Physical Books',
                $row['book_code'],
                $row['title'],
                $row['author'],
                $row['publisher'] ?? '',
                $row['price'] ?? 0.00,
                'Shelf A1'
            ]);
        }
        fclose($output);
        exit;
    }

    if ($action === 'import_books_csv' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!admin()) {
            http_response_code(403);
            exit('Unauthorized');
        }
        
        $type = $_POST['import_type'] ?? 'physical';
        
        if (!empty($_FILES['csv_file']['name']) && $_FILES['csv_file']['error'] === 0) {
            $filename = $_FILES['csv_file']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if ($ext === 'pdf') {
                flash("⚠️ Error: You selected a PDF file instead of a CSV metadata file. Please select a valid CSV spreadsheet.");
                go('?action=admin&tab=' . $type);
            }
            
            if ($ext === 'xlsx' || $ext === 'xls') {
                flash("⚠️ Error: Direct Excel (.xlsx/.xls) imports are not supported. Please click 'Save As' in Excel and choose 'CSV (Comma delimited) (*.csv)' format, then upload the CSV file.");
                go('?action=admin&tab=' . $type);
            }
            
            if ($ext !== 'csv' && $ext !== 'txt') {
                flash("⚠️ Error: Invalid file format selected. Please upload a valid CSV (.csv) file.");
                go('?action=admin&tab=' . $type);
            }

            $tmp = $_FILES['csv_file']['tmp_name'];
            if (($handle = fopen($tmp, "r")) !== FALSE) {
                // Detect delimiter
                $firstLine = fgets($handle);
                rewind($handle);
                
                // Check and strip BOM
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }
                
                $delimiter = ',';
                if ($firstLine !== false) {
                    $numCommas = substr_count($firstLine, ',');
                    $numSemicolons = substr_count($firstLine, ';');
                    $numTabs = substr_count($firstLine, "\t");
                    
                    if ($numSemicolons > $numCommas && $numSemicolons > $numTabs) {
                        $delimiter = ';';
                    } elseif ($numTabs > $numCommas && $numTabs > $numSemicolons) {
                        $delimiter = "\t";
                    }
                }
                
                // Read & validate headers
                $headers = fgetcsv($handle, 1000, $delimiter);
                
                $imported = 0;
                $skipped = 0;
                
                while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                    if (count($data) < 3) {
                        $skipped++;
                        continue;
                    }
                    
                    $cat_name = trim($data[0]);
                    
                    // Settle Category
                    $cat_id = 1;
                    if ($cat_name !== '') {
                        $cStmt = $db->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
                        $cStmt->bind_param("s", $cat_name);
                        $cStmt->execute();
                        $cRow = $cStmt->get_result()->fetch_assoc();
                        $cStmt->close();
                        
                        if ($cRow) {
                            $cat_id = $cRow['id'];
                        } else {
                            $insCat = $db->prepare("INSERT INTO categories (name) VALUES (?)");
                            $insCat->bind_param("s", $cat_name);
                            if ($insCat->execute()) {
                                $cat_id = $db->insert_id;
                            }
                            $insCat->close();
                        }
                    }
                    
                    if ($type === 'physical') {
                        // Format: Category, Book Code, Title, Author, Publisher, Price, Rack Location
                        $code = trim($data[1]);
                        $title = trim($data[2]);
                        $author = trim($data[3] ?? 'Unknown');
                        $publisher = trim($data[4] ?? '');
                        $price = (float)($data[5] ?? 0.00);
                        $rack = trim($data[6] ?? 'Shelf A1');
                        
                        if ($code === '' || $title === '') {
                            $skipped++;
                            continue;
                        }
                        
                        // Check if code already exists
                        $chkCode = $db->prepare("SELECT id FROM physical_books WHERE book_code = ? LIMIT 1");
                        $chkCode->bind_param("s", $code);
                        $chkCode->execute();
                        $codeExists = $chkCode->get_result()->fetch_assoc();
                        $chkCode->close();
                        
                        if ($codeExists) {
                            $skipped++;
                            continue;
                        }
                        
                        $insStmt = $db->prepare("INSERT INTO physical_books (book_code, title, author, publisher, price) VALUES (?, ?, ?, ?, ?)");
                        $insStmt->bind_param("ssssd", $code, $title, $author, $publisher, $price);
                        if ($insStmt->execute()) {
                            $imported++;
                        } else {
                            $skipped++;
                        }
                        $insStmt->close();
                    } else {
                        // Format: Category, Title, Keywords
                        $title = trim($data[1]);
                        $keywords = trim($data[2] ?? '');
                        $pdf_placeholder = 'pending_upload.pdf'; // Must be updated or uploaded locally later
                        
                        if ($title === '') {
                            $skipped++;
                            continue;
                        }
                        
                        $insStmt = $db->prepare("INSERT INTO ebooks (category_id, title, keywords, pdf_file) VALUES (?, ?, ?, ?)");
                        $insStmt->bind_param("isss", $cat_id, $title, $keywords, $pdf_placeholder);
                        if ($insStmt->execute()) {
                            $imported++;
                        } else {
                            $skipped++;
                        }
                        $insStmt->close();
                    }
                }
                fclose($handle);
                flash("🎉 Catalog Ingestion Complete: Imported {$imported} items. (Skipped/Duplicate: {$skipped})");
            } else {
                flash("Error opening uploaded CSV file.");
            }
        } else {
            flash("Error uploading file. Please ensure it is a valid CSV.");
        }
        go('?action=admin&tab=' . $type);
    }

    if ($action === 'add_ebook' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);
        $f = $_FILES['pdf']['name'] ?? '';
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        
        if ($ext === 'pdf' && $_FILES['pdf']['error'] === 0) {
            $tmp = $_FILES['pdf']['tmp_name'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmp);
            finfo_close($finfo);
            
            if ($mime === 'application/pdf') {
                $name = uniqid('book_') . '.pdf';
                move_uploaded_file($tmp, 'uploads/' . $name);
                $c = (int)$_POST['category_id'];
                $t = trim($_POST['title'] ?? '');
                $k = trim($_POST['keywords'] ?? '');
                
                $stmt = $db->prepare("INSERT INTO ebooks (category_id, title, keywords, pdf_file) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $c, $t, $k, $name);
                $stmt->execute();
                $stmt->close();
                flash('E-book uploaded.');
            } else {
                flash('Uploaded file is not a valid PDF MIME-type.');
            }
        } else {
            flash('Only PDF file is allowed.');
        }
        go('?action=admin&tab=ebooks');
    }

    if ($action === 'update_ebook' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!admin()) {
            http_response_code(403);
            exit('Unauthorized');
        }
        verify_csrf();
        
        $id = (int)$_POST['id'];
        $c = (int)$_POST['category_id'];
        $t = trim($_POST['title'] ?? '');
        $k = trim($_POST['keywords'] ?? '');
        
        $file_name = null;
        if (!empty($_FILES['pdf']['name']) && $_FILES['pdf']['error'] === 0) {
            $f = $_FILES['pdf']['name'];
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                $tmp = $_FILES['pdf']['tmp_name'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $tmp);
                finfo_close($finfo);
                
                if ($mime === 'application/pdf') {
                    $file_name = uniqid('book_') . '.pdf';
                    move_uploaded_file($tmp, 'uploads/' . $file_name);
                    
                    // Delete old file
                    $oldStmt = $db->prepare("SELECT pdf_file FROM ebooks WHERE id = ?");
                    $oldStmt->bind_param("i", $id);
                    $oldStmt->execute();
                    $old_row = $oldStmt->get_result()->fetch_assoc();
                    $old_pdf = $old_row['pdf_file'] ?? '';
                    $oldStmt->close();
                    
                    if ($old_pdf !== '') {
                        $old_file = 'uploads/' . basename($old_pdf);
                        if (is_file($old_file)) {
                            unlink($old_file);
                        }
                    }
                }
            }
        }
        
        if ($file_name) {
            $stmt = $db->prepare("UPDATE ebooks SET category_id = ?, title = ?, keywords = ?, pdf_file = ? WHERE id = ?");
            $stmt->bind_param("isssi", $c, $t, $k, $file_name, $id);
        } else {
            $stmt = $db->prepare("UPDATE ebooks SET category_id = ?, title = ?, keywords = ? WHERE id = ?");
            $stmt->bind_param("issi", $c, $t, $k, $id);
        }
        $stmt->execute();
        $stmt->close();
        
        flash('E-book updated.');
        go('?action=admin&tab=ebooks');
    }

    if ($action === 'delete_ebook' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("SELECT pdf_file FROM ebooks WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $x = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($x && is_file('uploads/' . $x['pdf_file'])) {
            unlink('uploads/' . $x['pdf_file']);
        }
        
        $stmt = $db->prepare("DELETE FROM ebooks WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        flash('E-book deleted.');
        go('?action=admin&tab=ebooks');
    }

    if ($action === 'add_member' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $d = $_POST['duration'] ?? 'Yearly';
        $plan_id = isset($_POST['plan_id']) && $_POST['plan_id'] !== '' ? (int)$_POST['plan_id'] : null;
        $start = date('Y-m-d');
        $end = membership_end($d);
        
        $temp_id = 'TEMP_M_' . uniqid('', true);
        
        $v = ['name', 'guardian_name', 'mobile', 'password', 'email', 'address', 'aadhar_no', 'membership_fee', 'payment_id'];
        $data = [];
        foreach ($v as $k) {
            $data[$k] = trim($_POST[$k] ?? '');
        }
        
        if ($data['name'] === '' || $data['mobile'] === '' || $data['aadhar_no'] === '') {
            flash('Error: Name, Mobile, and Aadhar Number are required.');
            go('?action=admin&tab=members');
        }
        
        // Hash password securely
        $hashed = password_hash($data['password'], PASSWORD_BCRYPT);
        $fee = (float)$data['membership_fee'];
        
        $stmt = $db->prepare("INSERT INTO members (membership_id, name, guardian_name, mobile, password, email, address, aadhar_no, duration, start_date, end_date, payment_id, membership_plan_id, membership_fee) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssssid", $temp_id, $data['name'], $data['guardian_name'], $data['mobile'], $hashed, $data['email'], $data['address'], $data['aadhar_no'], $d, $start, $end, $data['payment_id'], $plan_id, $fee);
        $ok = $stmt->execute();
        $stmt->close();
        
        if ($ok) {
            $new_id = $db->insert_id;
            $mid_code = 'CBMDL' . $new_id;
            $upStmt = $db->prepare("UPDATE members SET membership_id = ? WHERE id = ?");
            $upStmt->bind_param("si", $mid_code, $new_id);
            $upStmt->execute();
            $upStmt->close();
            flash('Member created. Membership ID: ' . $mid_code);
        } else {
            flash('Error creating member. Mobile/Aadhar already registered.');
        }
        go('?action=admin&tab=members');
    }

    if ($action === 'approve_member' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!admin()) {
            http_response_code(403);
            exit('Unauthorized');
        }
        $id = (int)$_POST['id'];
        $plan_id = isset($_POST['plan_id']) && $_POST['plan_id'] !== '' ? (int)$_POST['plan_id'] : null;
        $d = $_POST['duration'] ?? 'Yearly';
        $fee = (float)($_POST['membership_fee'] ?? 0.00);
        $payment_id = trim($_POST['payment_id'] ?? '');
        
        $start = date('Y-m-d');
        $end = membership_end($d);
        $mid_code = 'CBMDL' . $id;
        
        $stmt = $db->prepare("UPDATE members SET membership_id = ?, membership_plan_id = ?, membership_fee = ?, payment_id = ?, duration = ?, start_date = ?, end_date = ?, is_active = 1, approved = 1 WHERE id = ?");
        $stmt->bind_param("sisssssi", $mid_code, $plan_id, $fee, $payment_id, $d, $start, $end, $id);
        $ok = $stmt->execute();
        $stmt->close();
        
        if ($ok) {
            flash('Member application approved. Membership ID issued: ' . $mid_code);
        } else {
            flash('Error approving member application.');
        }
        go('?action=admin&tab=members');
    }

    if ($action === 'update_member' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $g_name = trim($_POST['guardian_name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $aadhar = trim($_POST['aadhar_no'] ?? '');
        $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
        $pass = trim($_POST['password'] ?? '');
        
        if ($pass !== '') {
            $hashed = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE members SET name = ?, guardian_name = ?, mobile = ?, email = ?, address = ?, aadhar_no = ?, is_active = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssssisi", $name, $g_name, $mobile, $email, $address, $aadhar, $is_active, $hashed, $id);
        } else {
            $stmt = $db->prepare("UPDATE members SET name = ?, guardian_name = ?, mobile = ?, email = ?, address = ?, aadhar_no = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param("ssssssii", $name, $g_name, $mobile, $email, $address, $aadhar, $is_active, $id);
        }
        $stmt->execute();
        $stmt->close();
        
        flash('Member profile updated.');
        $refTab = $_GET['tab'] ?? 'members';
        go('?action=admin&tab=' . $refTab . '&view=' . $id);
    }

    if ($action === 'renew_member' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        $d = $_POST['duration'] ?? 'Yearly';
        $plan_id = isset($_POST['plan_id']) && $_POST['plan_id'] !== '' ? (int)$_POST['plan_id'] : null;
        $payment_id = trim($_POST['payment_id'] ?? '');
        $fee = (float)($_POST['membership_fee'] ?? 0.00);
        
        $start = date('Y-m-d');
        $end = membership_end($d);
        
        $stmt = $db->prepare("UPDATE members SET duration = ?, start_date = ?, end_date = ?, payment_id = ?, membership_plan_id = ?, membership_fee = ? WHERE id = ?");
        $stmt->bind_param("ssssidi", $d, $start, $end, $payment_id, $plan_id, $fee, $id);
        $stmt->execute();
        $stmt->close();
        
        flash('Membership renewed successfully.');
        $refTab = $_GET['tab'] ?? 'members';
        go('?action=admin&tab=' . $refTab . '&view=' . $id);
    }

    if ($action === 'delete_member' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        try {
            $stmt = $db->prepare("DELETE FROM members WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            flash('Member deleted successfully.');
        } catch (mysqli_sql_exception $e) {
            flash('⚠️ Integrity Warning: This member account cannot be deleted because they have associated physical book lending logs. Please preserve circulation history.');
        }
        go('?action=admin&tab=members');
    }

    if ($action === 'add_plan' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $duration = trim($_POST['duration'] ?? 'Yearly');
        $amount = (float)($_POST['amount'] ?? 0.00);
        
        if ($name !== '') {
            $stmt = $db->prepare("INSERT INTO membership_plans (name, duration, amount) VALUES (?, ?, ?)");
            $stmt->bind_param("ssd", $name, $duration, $amount);
            $stmt->execute();
            $stmt->close();
            flash('Membership plan added successfully.');
        } else {
            flash('Plan name is required.');
        }
        go('?action=admin&tab=plans');
    }

    if ($action === 'update_plan' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $duration = trim($_POST['duration'] ?? 'Yearly');
        $amount = (float)($_POST['amount'] ?? 0.00);
        
        if ($name !== '') {
            $stmt = $db->prepare("UPDATE membership_plans SET name = ?, duration = ?, amount = ? WHERE id = ?");
            $stmt->bind_param("ssdi", $name, $duration, $amount, $id);
            $stmt->execute();
            $stmt->close();
            flash('Membership plan updated successfully.');
        } else {
            flash('Plan name is required.');
        }
        go('?action=admin&tab=plans');
    }

    if ($action === 'delete_plan' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM membership_plans WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        flash('Membership plan deleted successfully.');
        go('?action=admin&tab=plans');
    }

    if ($action === 'settle_fine' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        $status = $_POST['fine_status'] ?? 'Paid';
        if (!in_array($status, ['Paid', 'Waived'])) {
            $status = 'Paid';
        }
        $pay_id = trim($_POST['fine_payment_id'] ?? '');
        $amount = (float)($_POST['fine_amount'] ?? 0.00);
        
        $stmt = $db->prepare("UPDATE lendings SET fine_amount = ?, fine_status = ?, fine_payment_id = ? WHERE id = ?");
        $stmt->bind_param("dssi", $amount, $status, $pay_id, $id);
        $stmt->execute();
        $stmt->close();
        
        flash('Fine settled successfully.');
        $ref = $_POST['tab'] ?? 'view_lending';
        if (!in_array($ref, ['lending', 'view_lending', 'dashboard'])) {
            $ref = 'view_lending';
        }
        go('?action=admin&tab=' . $ref);
    }

    if ($action === 'update_physical' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        $title = trim($_POST['title'] ?? '');
        $book_code = trim($_POST['book_code'] ?? '');
        $price = (float)($_POST['price'] ?? 0.00);
        $author = trim($_POST['author'] ?? '');
        $publisher = trim($_POST['publisher'] ?? '');
        
        $stmt = $db->prepare("UPDATE physical_books SET title = ?, book_code = ?, price = ?, author = ?, publisher = ? WHERE id = ?");
        $stmt->bind_param("ssdssi", $title, $book_code, $price, $author, $publisher, $id);
        $ok = $stmt->execute();
        $stmt->close();
        
        flash($ok ? 'Physical book updated.' : 'Error updating book: book code must be unique.');
        go('?action=admin&tab=physical');
    }

    if ($action === 'update_ebook' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        $category_id = (int)$_POST['category_id'];
        $title = trim($_POST['title'] ?? '');
        $keywords = trim($_POST['keywords'] ?? '');
        
        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === 0) {
            $f = $_FILES['pdf']['name'] ?? '';
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                $oldStmt = $db->prepare("SELECT pdf_file FROM ebooks WHERE id = ?");
                $oldStmt->bind_param("i", $id);
                $oldStmt->execute();
                $old = $oldStmt->get_result()->fetch_assoc();
                $oldStmt->close();
                if ($old && is_file('uploads/' . $old['pdf_file'])) {
                    unlink('uploads/' . $old['pdf_file']);
                }
                
                $name = uniqid('book_') . '.pdf';
                move_uploaded_file($_FILES['pdf']['tmp_name'], 'uploads/' . $name);
                
                $stmt = $db->prepare("UPDATE ebooks SET category_id = ?, title = ?, keywords = ?, pdf_file = ? WHERE id = ?");
                $stmt->bind_param("isssi", $category_id, $title, $keywords, $name, $id);
            } else {
                flash('Only PDF files are allowed.');
                go('?action=admin&tab=ebooks');
            }
        } else {
            $stmt = $db->prepare("UPDATE ebooks SET category_id = ?, title = ?, keywords = ? WHERE id = ?");
            $stmt->bind_param("issi", $category_id, $title, $keywords, $id);
        }
        
        $ok = $stmt->execute();
        $stmt->close();
        flash($ok ? 'E-book updated.' : 'Error updating E-book.');
        go('?action=admin&tab=ebooks');
    }

    if ($action === 'add_physical' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title'] ?? '');
        $book_code = trim($_POST['book_code'] ?? '');
        $price = (float)($_POST['price'] ?? 0.00);
        $author = trim($_POST['author'] ?? '');
        $publisher = trim($_POST['publisher'] ?? '');
        
        $stmt = $db->prepare("INSERT INTO physical_books (title, book_code, price, author, publisher) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $title, $book_code, $price, $author, $publisher);
        $ok = $stmt->execute();
        $stmt->close();
        
        flash($ok ? 'Physical book added.' : 'Error: Book code must be unique.');
        go('?action=admin&tab=physical');
    }

    if ($action === 'delete_physical' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $db->prepare("DELETE FROM physical_books WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            flash('Physical book deleted.');
        } catch (mysqli_sql_exception $e) {
            flash('⚠️ Integrity Warning: This book volume cannot be deleted because it has active/past lending transactions logged.');
        }
        go('?action=admin&tab=physical');
    }

    if ($action === 'approve' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['request_id'];
        $mins = max(1, (int)$_POST['minutes']);
        
        $stmt = $db->prepare("UPDATE reading_requests SET status = 'Approved', approved_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?");
        $stmt->bind_param("ii", $mins, $id);
        $stmt->execute();
        $stmt->close();
        
        flash('Reading permission approved.');
        go('?action=admin&tab=requests');
    }

    if ($action === 'reject' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['request_id'] ?? $_POST['id'] ?? 0);
        $stmt = $db->prepare("UPDATE reading_requests SET status = 'Rejected' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        flash('Request rejected.');
        go('?action=admin&tab=requests');
    }

    if ($action === 'lend' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $find = trim($_POST['member'] ?? '');
        $b = trim($_POST['book_code'] ?? '');
        $date = date('Y-m-d H:i:s');
        $due = $_POST['due_date'] ?? '';
        $tx = trim($_POST['transaction_id'] ?? '');
        
        if (!$tx) {
            flash('Payment / Transaction ID is mandatory.');
            go('?action=admin&tab=lending');
        }
        
        // Find Member
        $mStmt = $db->prepare("SELECT id FROM members WHERE membership_id = ? OR mobile = ? LIMIT 1");
        $mStmt->bind_param("ss", $find, $find);
        $mStmt->execute();
        $m = $mStmt->get_result()->fetch_assoc();
        $mStmt->close();
        
        // Find Available Physical Book
        $bStmt = $db->prepare("SELECT p.id FROM physical_books p WHERE p.book_code = ? AND NOT EXISTS (SELECT 1 FROM lendings l WHERE l.physical_book_id = p.id AND l.returned_at IS NULL) LIMIT 1");
        $bStmt->bind_param("s", $b);
        $bStmt->execute();
        $book = $bStmt->get_result()->fetch_assoc();
        $bStmt->close();
        
        if ($m && $book) {
            $lStmt = $db->prepare("INSERT INTO lendings (member_id, physical_book_id, lent_at, due_date, transaction_id) VALUES (?, ?, ?, ?, ?)");
            $lStmt->bind_param("iisss", $m['id'], $book['id'], $date, $due, $tx);
            $lStmt->execute();
            $lStmt->close();
            
            // Availability is tracked dynamically via lendings table. No direct is_available column is required.
            
            // Auto-complete any active/awaiting holds for this member and book
            $db->query("UPDATE hold_requests SET status = 'Completed' WHERE physical_book_id = " . (int)$book['id'] . " AND member_id = " . (int)$m['id'] . " AND status IN ('Active', 'Awaiting Collection')");
            
            flash('Book lent successfully.');
        } else {
            flash('Member or available book was not found.');
        }
        go('?action=admin&tab=lending');
    }

    if ($action === 'return_book' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        
        $pbStmt = $db->prepare("SELECT physical_book_id FROM lendings WHERE id = ? LIMIT 1");
        $pbStmt->bind_param("i", $id);
        $pbStmt->execute();
        $pbRow = $pbStmt->get_result()->fetch_assoc();
        $pbStmt->close();
        
        $stmt = $db->prepare("UPDATE lendings SET returned_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        if ($pbRow) {
            $book_id = $pbRow['physical_book_id'];
            // Availability is tracked dynamically via lendings table. No direct is_available column is required.
            
            // Check if there is an active hold reservation on this book
            $holdStmt = $db->prepare("SELECT h.id, m.name FROM hold_requests h JOIN members m ON m.id = h.member_id WHERE h.physical_book_id = ? AND h.status = 'Active' ORDER BY h.id ASC LIMIT 1");
            $holdStmt->bind_param("i", $book_id);
            $holdStmt->execute();
            $holdRow = $holdStmt->get_result()->fetch_assoc();
            $holdStmt->close();
            
            if ($holdRow) {
                $db->query("UPDATE hold_requests SET status = 'Awaiting Collection' WHERE id = " . (int)$holdRow['id']);
                flash("🎉 Book returned successfully! NOTE: This book has an active hold reservation by member '" . e($holdRow['name']) . "'. It has been placed in 'Awaiting Collection' status.");
            } else {
                flash('Book marked as returned and available.');
            }
        } else {
            flash('Book marked as returned.');
        }
        $ref = $_POST['tab'] ?? 'lending';
        go('?action=admin&tab=' . $ref);
    }

    if ($action === 'complete_print' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("UPDATE print_requests SET status = 'Completed' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        flash('Print request completed.');
        go('?action=admin&tab=prints');
    }
}

// 6. Member Controllers (Prepared Statements & Secure Actions)
if (member()) {
    $mid = (int)$_SESSION['member'];

    if ($action === 'read_member_pdf') {
        $rid = (int)($_GET['id'] ?? 0);
        
        $stmt = $db->prepare("SELECT r.*, e.pdf_file, e.title FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.id = ? AND r.member_id = ? AND r.status = 'Approved' AND r.expires_at > NOW() LIMIT 1");
        $stmt->bind_param("ii", $rid, $mid);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$r || !$r['pdf_file']) exit('No active permission for this book.');
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <link rel="icon" type="image/x-icon" href="images/favicon.ico">
            <meta charset="utf-8">
            <title>Reader - <?= e($r['title']) ?></title>
            <style>
                body { margin:0; padding:0; background-color:#1e293b; }
                iframe { width:100%; height:100vh; border:none; }
                * { user-select:none; -webkit-user-select:none; -moz-user-select:none; -ms-user-select:none; }
                @media print { body { display:none !important; } }
            </style>
        </head>
        <body>
            <iframe src="<?= BASE_URL ?>?action=secure_pdf_viewer&source=member&id=<?= urlencode($r['id']) ?>" style="pointer-events:auto;"></iframe>
        </body>
        </html>
        <?php 
        exit;
    }

    if ($action === 'read_member_pdf_content') {
        $rid = (int)($_GET['id'] ?? 0);
        
        $stmt = $db->prepare("SELECT r.id, r.expires_at, e.pdf_file FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.id = ? AND r.member_id = ? AND r.status = 'Approved' AND r.expires_at > NOW() LIMIT 1");
        $stmt->bind_param("ii", $rid, $mid);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($r) {
            $file = 'uploads/' . basename($r['pdf_file']);
            stream_file_ranged($file, 'application/pdf', true, 300);
        }
        exit('File not found or e-reading permission has expired.');
    }

    if ($action === 'update_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $pass = trim($_POST['password'] ?? '');
        
        if ($pass !== '') {
            $hashed = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE members SET email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssi", $email, $hashed, $mid);
        } else {
            $stmt = $db->prepare("UPDATE members SET email = ? WHERE id = ?");
            $stmt->bind_param("si", $email, $mid);
        }
        $stmt->execute();
        $stmt->close();
        flash('Profile updated successfully.');
        go('?action=user&tab=profile');
    }

    if ($action === 'request_read') {
        $id = (int)$_GET['id'];
        
        // Check current active/pending request count (limit 5)
        $cntStmt = $db->prepare("SELECT COUNT(*) c FROM reading_requests WHERE member_id = ? AND (status = 'Pending' OR (status = 'Approved' AND expires_at > NOW()))");
        $cntStmt->bind_param("i", $mid);
        $cntStmt->execute();
        $active_count = (int)($cntStmt->get_result()->fetch_assoc()['c'] ?? 0);
        $cntStmt->close();

        if ($active_count >= 5) {
            flash('⚠️ Request Limit Reached: You can have a maximum of 5 active or pending e-book reading requests at a time.');
            go('?action=user&tab=books');
        }
        
        $oldStmt = $db->prepare("SELECT id FROM reading_requests WHERE member_id = ? AND ebook_id = ? AND status = 'Pending'");
        $oldStmt->bind_param("ii", $mid, $id);
        $oldStmt->execute();
        $old = $oldStmt->get_result()->fetch_assoc();
        $oldStmt->close();
        
        if (!$old) {
            $stmt = $db->prepare("INSERT INTO reading_requests (member_id, ebook_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $mid, $id);
            $stmt->execute();
            $stmt->close();
            flash('Reading request sent to librarian.');
        } else {
            flash('Reading request already submitted and pending approval.');
        }
        go('?action=user&tab=books');
    }

    if ($action === 'request_hold') {
        go('?action=user&tab=physical_books');
    }

    if ($action === 'check_request_updates') {
        header('Content-Type: application/json');
        if (!member()) {
            echo json_encode([]);
            exit;
        }
        $mid = (int)$_SESSION['member'];
        $query = $db->query("SELECT r.id, r.ebook_id, r.status, r.expires_at, e.title FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.member_id = $mid AND r.id = (SELECT MAX(r2.id) FROM reading_requests r2 WHERE r2.member_id = $mid AND r2.ebook_id = r.ebook_id)");
        $updates = [];
        while ($row = $query->fetch_assoc()) {
            $updates[] = [
                'request_id' => (int)$row['id'],
                'ebook_id' => (int)$row['ebook_id'],
                'status' => $row['status'],
                'title' => $row['title'],
                'active' => ($row['status'] === 'Approved' && strtotime($row['expires_at']) > time())
            ];
        }
        echo json_encode($updates);
        exit;
    }

    if ($action === 'poll_member_notifications') {
        header('Content-Type: application/json');
        if (!member()) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $mid = (int)$_SESSION['member'];
        
        $read_query = $db->query("
            SELECT r.id, r.status, e.title, r.expires_at 
            FROM reading_requests r 
            JOIN ebooks e ON e.id = r.ebook_id 
            WHERE r.member_id = $mid AND r.requested_at > NOW() - INTERVAL 12 HOUR
            ORDER BY r.id DESC
        ");
        $reading_reqs = [];
        while ($row = $read_query->fetch_assoc()) {
            $reading_reqs[] = [
                'id' => (int)$row['id'],
                'type' => 'reading',
                'title' => $row['title'],
                'status' => $row['status'],
                'expires_at' => $row['expires_at']
            ];
        }
        
        $print_query = $db->query("
            SELECT p.id, p.status, e.title, p.pages 
            FROM print_requests p 
            JOIN ebooks e ON e.id = p.ebook_id 
            WHERE p.member_id = $mid AND p.requested_at > NOW() - INTERVAL 12 HOUR
            ORDER BY p.id DESC
        ");
        $print_reqs = [];
        while ($row = $print_query->fetch_assoc()) {
            $print_reqs[] = [
                'id' => (int)$row['id'],
                'type' => 'print',
                'title' => $row['title'],
                'status' => $row['status'],
                'pages' => $row['pages']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'reading' => $reading_reqs,
            'print' => $print_reqs
        ]);
        exit;
    }



    if ($action === 'request_print' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['ebook_id'];
        $p = trim($_POST['pages'] ?? '');
        
        $stmt = $db->prepare("INSERT INTO print_requests (member_id, ebook_id, pages) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $mid, $id, $p);
        $stmt->execute();
        $stmt->close();
        
        flash('Print request sent to librarian.');
        go('?action=user&tab=books');
    }
}

// Render layout and views modularly
require 'app/views/layout/header.php';

if ($action === 'admin_login' || $action === 'member_login') {
    require 'app/views/login.php';
} elseif ($action === 'admin') {
    require 'app/views/admin.php';
} elseif ($action === 'user') {
    require 'app/views/user.php';
}

require 'app/views/layout/footer.php';
