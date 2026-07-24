<?php 
ob_start();
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
} elseif ($pathRoute === 'admin-forgot-password') {
    $action = 'admin_forgot_password';
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
if (in_array($action, ['admin_login', 'member_login', 'admin_forgot_password']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if (admin()) {
        go('?action=admin');
    } elseif (member()) {
        go('?action=user');
    }
}

// 1. Authentication Handlers with Session Fixation Defense
if ($action === 'logout') {
    $was_admin = admin();
    if (member()) {
        expire_member_reading_requests($_SESSION['member'], $db);
    }
    
    // Clear all session array variables in memory
    $_SESSION = array();

    // Delete session cookie from browser storage
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destroy session storage on server
    session_destroy();

    // Start a fresh, clean session for flash message & CSRF token
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);

    csrf_token(); // Re-initialize valid CSRF token in new session
    flash('Logged out successfully.');
    go(($was_admin ? 'admin-login' : 'member-login') . '&logged_out=1');
}

if ($action === 'admin_login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new App\Controllers\AuthController($db))->adminLogin();
}

if ($action === 'process_admin_forgot_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new App\Controllers\AuthController($db))->adminForgotPassword();
}

if ($action === 'member_login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
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

// 2b. Membership History Audit Helper
function log_membership_history($db, $member_id, $action_type = 'Initial Joining') {
    $stmt = $db->prepare("SELECT m.*, p.name as plan_name FROM members m LEFT JOIN membership_plans p ON m.membership_plan_id = p.id WHERE m.id = ?");
    if (!$stmt) return;
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $m = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($m && !empty($m['membership_id'])) {
        $pName = !empty($m['plan_name']) ? $m['plan_name'] : $m['duration'];
        $fee = (float)($m['membership_fee'] ?? 0.00);
        $histStmt = $db->prepare("INSERT INTO membership_history (member_id, membership_id, membership_plan_id, plan_name, duration, shift, start_date, end_date, membership_fee, payment_id, action_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($histStmt) {
            $histStmt->bind_param("isisssssdss", $m['id'], $m['membership_id'], $m['membership_plan_id'], $pName, $m['duration'], $m['shift'], $m['start_date'], $m['end_date'], $fee, $m['payment_id'], $action_type);
            $histStmt->execute();
            $histStmt->close();
        }
    }
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
        $file = __DIR__ . '/uploads/' . basename($b['pdf_file']);
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
    $file = __DIR__ . '/uploads/' . basename($r['pdf_file']);
    if (is_file($file)) {
        stream_file_ranged($file, 'application/pdf', true, 300);
    }
    exit('File not found.');
}

// 4. Role-based Navigation Guard
if (!admin() && !member() && !in_array($action, ['home', 'admin_login', 'member_login', 'admin_forgot_password', 'process_admin_forgot_password'])) {
    go('index.php');
}

if ($action === 'secure_pdf_viewer') {
    $id = (int)($_GET['id'] ?? 0);
    $source = $_GET['source'] ?? '';
    
    $pdfTitle = 'Secure Interactive Reader';
    $streamUrl = '';
    $expiresAtUnix = 0;
    
    if ($source === 'admin' || admin()) {
        if (!admin()) exit('Unauthorized');
        go(BASE_URL . '?action=view_pdf_content&id=' . $id);
    } elseif ($source === 'member') {
        if (!member()) exit('Unauthorized');
        $mid = (int)$_SESSION['member'];
        $stmt = $db->prepare("SELECT r.id, r.duration_minutes, r.started_reading_at, r.expires_at, e.title, e.pdf_file FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE (r.id = ? OR r.ebook_id = ?) AND r.member_id = ? AND r.status = 'Approved' AND (r.started_reading_at IS NULL OR r.expires_at > NOW()) ORDER BY r.id DESC LIMIT 1");
        $stmt->bind_param("iii", $id, $id, $mid);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$r || empty($r['pdf_file'])) {
            exit('<div style="font-family:system-ui, sans-serif; text-align:center; padding:60px 20px; color:#ef4444; background:#0b0f19; height:100vh; box-sizing:border-box;"><h2 style="font-size:24px; margin-bottom:12px;">⚠️ Permission Expired or Book Not Found</h2><p style="color:#9ca3af; font-size:15px; max-width:500px; margin:0 auto 20px;">Your e-reading request for this book is either not approved or your active reading session has expired.</p><a href="' . BASE_URL . '?action=user&tab=books" style="display:inline-block; padding:10px 20px; background:#3b82f6; color:#fff; text-decoration:none; border-radius:8px; font-weight:600;">Return to Dashboard</a></div>');
        }

        // Start session timer on first read click if not already started
        if (empty($r['started_reading_at'])) {
            $duration = !empty($r['duration_minutes']) ? (int)$r['duration_minutes'] : 15;
            $upStmt = $db->prepare("UPDATE reading_requests SET started_reading_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?");
            $upStmt->bind_param("ii", $duration, $r['id']);
            $upStmt->execute();
            $upStmt->close();
            $r['expires_at'] = date('Y-m-d H:i:s', time() + ($duration * 60));
        }

        $pdfTitle = $r['title'];
        $streamUrl = BASE_URL . '?action=read_member_pdf_content&id=' . (int)$r['id'];
        $expiresAtUnix = !empty($r['expires_at']) ? strtotime($r['expires_at']) : 0;
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

            const pdfUrl = '<?= $streamUrl ?>';
            let pdfDoc = null;
            let numPages = 0;
            let currentPageNum = 1;
            let currentScale = 1.0;
            let currentRotation = 0;

            // Per-page state for the virtualized continuous-scroll viewport.
            // Pages are only rasterized to a <canvas> while near the visible area;
            // canvases outside the buffer are torn down to bound memory on huge PDFs,
            // while the already-fetched PDFPageProxy (pageProxies) and its base
            // dimensions (pageBaseDims) stay cached so scrolling back is instant.
            let pageWrappers = [];
            const pageProxies = {};
            const pageBaseDims = {};
            const renderedPages = new Set();
            const pendingRenders = new Set();
            const renderTasks = {};

            const loader = document.getElementById('loader');
            const loaderText = document.getElementById('loaderText');
            const pageNumInput = document.getElementById('pageNumInput');
            const pageCountLabel = document.getElementById('pageCount');
            const zoomPercentLabel = document.getElementById('zoomPercent');
            const viewportScrollArea = document.querySelector('.viewport-scroll-area');

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
                numPages = doc.numPages;
                pageCountLabel.textContent = numPages;
                pageNumInput.max = numPages;

                return getPageProxy(1).then(function() {
                    buildPagesLayout();
                    return renderPageIfNeeded(1);
                });
            }).then(function() {
                loader.style.opacity = '0';
                setTimeout(() => loader.style.display = 'none', 200);
                updateVirtualization();
                setTimeout(() => {
                    buildThumbnailSidebar();
                }, 100);
            }).catch(function(err) {
                console.error('Error loading secure PDF: ', err);
                loaderText.innerHTML = '<span style="color:#ef4444;"><span class="fa-solid fa-triangle-exclamation"></span> Error accessing file content or your e-reading permission has expired.</span>';
            });

            // Fetches (once) and caches the PDFPageProxy for a page, along with
            // its unscaled/unrotated base size used to lay out not-yet-rendered pages.
            function getPageProxy(num) {
                if (!pageProxies[num]) {
                    pageProxies[num] = pdfDoc.getPage(num).then(function(page) {
                        const base = page.getViewport({ scale: 1.0, rotation: 0 });
                        pageBaseDims[num] = { width: base.width, height: base.height };
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

            // Resizes every page wrapper for the current zoom/rotation. Pages whose real
            // dimensions we already know (pageBaseDims) size exactly; others fall back to
            // page 1's template, which is corrected the moment that page actually renders.
            function applyPageSizing() {
                const template = pageBaseDims[1] || { width: 800, height: 1120 };
                const rotated = (currentRotation % 180) !== 0;

                for (let i = 1; i <= numPages; i++) {
                    const wrapper = pageWrappers[i];
                    if (!wrapper) continue;
                    const dims = pageBaseDims[i] || template;
                    const w = (rotated ? dims.height : dims.width) * currentScale;
                    const h = (rotated ? dims.width : dims.height) * currentScale;
                    wrapper.style.width = w + 'px';
                    wrapper.style.height = h + 'px';
                }
            }

            function renderPageIfNeeded(num) {
                if (renderedPages.has(num) || pendingRenders.has(num)) return Promise.resolve();
                pendingRenders.add(num);

                return getPageProxy(num).then(function(page) {
                    pendingRenders.delete(num);
                    const wrapper = pageWrappers[num];
                    if (!wrapper) return;

                    const viewport = page.getViewport({ scale: currentScale, rotation: currentRotation });
                    wrapper.style.width = viewport.width + 'px';
                    wrapper.style.height = viewport.height + 'px';

                    const inner = wrapper.querySelector('.page-inner');
                    let canvas = inner.querySelector('canvas');
                    if (!canvas) {
                        canvas = document.createElement('canvas');
                        canvas.className = 'pdf-page-canvas';
                        inner.appendChild(canvas);
                    }
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    const pageCtx = canvas.getContext('2d');

                    const renderTask = page.render({ canvasContext: pageCtx, viewport: viewport });
                    renderTasks[num] = renderTask;

                    return renderTask.promise.then(function() {
                        delete renderTasks[num];

                        // Apply security watermark overlay on canvas
                        pageCtx.save();
                        pageCtx.font = 'bold ' + Math.max(16, Math.round(viewport.width / 25)) + 'px sans-serif';
                        pageCtx.fillStyle = 'rgba(150, 150, 150, 0.18)';
                        pageCtx.textAlign = 'center';
                        pageCtx.textBaseline = 'middle';
                        pageCtx.translate(viewport.width / 2, viewport.height / 2);
                        pageCtx.rotate(-Math.PI / 6);
                        pageCtx.fillText('CBMDLM SECURE DIGITAL READER - AUTHORIZED COPY', 0, 0);
                        pageCtx.restore();

                        const placeholder = inner.querySelector('.page-placeholder');
                        if (placeholder) placeholder.remove();
                        renderedPages.add(num);
                    }).catch(function(err) {
                        delete renderTasks[num];
                        if (err && err.name !== 'RenderingCancelledException') {
                            console.error('Page render error:', err);
                        }
                    });
                });
            }

            // Tears down a page's canvas to free memory once it scrolls well out of
            // view, but keeps its cached PDFPageProxy so re-entering view re-renders
            // instantly with no re-fetch.
            function unrenderPage(num) {
                if (renderTasks[num]) {
                    renderTasks[num].cancel();
                    delete renderTasks[num];
                }
                pendingRenders.delete(num);
                if (!renderedPages.has(num)) return;

                const wrapper = pageWrappers[num];
                if (!wrapper) return;
                const inner = wrapper.querySelector('.page-inner');
                const canvas = inner && inner.querySelector('canvas');
                if (canvas) {
                    canvas.width = 0;
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

            // Single geometry pass driving both virtualization (render pages near the
            // viewport, free pages far from it) and the page-number indicator (whichever
            // page occupies the most visible area). Runs rAF-throttled off scroll/resize.
            function updateVirtualization() {
                if (!pdfDoc) return;
                const containerRect = viewportScrollArea.getBoundingClientRect();
                const buffer = Math.max(containerRect.height, 600);

                let bestPage = currentPageNum;
                let bestVisibleArea = -1;

                for (let i = 1; i <= numPages; i++) {
                    const wrapper = pageWrappers[i];
                    if (!wrapper) continue;
                    const r = wrapper.getBoundingClientRect();
                    const relTop = r.top - containerRect.top;
                    const relBottom = r.bottom - containerRect.top;

                    const inBufferRange = relBottom >= -buffer && relTop <= containerRect.height + buffer;
                    if (inBufferRange) {
                        renderPageIfNeeded(i);
                    } else if (renderedPages.has(i)) {
                        unrenderPage(i);
                    }

                    const visibleTop = Math.max(relTop, 0);
                    const visibleBottom = Math.min(relBottom, containerRect.height);
                    const visibleArea = Math.max(0, visibleBottom - visibleTop);
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

            window.addEventListener('resize', function() {
                requestAnimationFrame(updateVirtualization);
            });

            function scrollToPage(num, smooth) {
                const wrapper = pageWrappers[num];
                if (!wrapper) return;
                wrapper.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'start' });
            }

            // Re-lays out every page for a new zoom/rotation, keeping the page the
            // user was reading anchored at the top instead of letting the resize
            // scroll them to an arbitrary spot.
            function relayoutAndRerender() {
                const anchorPage = currentPageNum;
                applyPageSizing();

                Object.keys(renderTasks).forEach(function(num) {
                    renderTasks[num].cancel();
                    delete renderTasks[num];
                });
                Array.from(renderedPages).forEach(function(num) {
                    const wrapper = pageWrappers[num];
                    const canvas = wrapper && wrapper.querySelector('canvas');
                    if (canvas) canvas.remove();
                });
                renderedPages.clear();

                requestAnimationFrame(function() {
                    scrollToPage(anchorPage, false);
                    updateVirtualization();
                });
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

                for (let i = 1; i <= numPages; i++) {
                    const card = document.createElement('div');
                    card.className = 'thumbnail-card';
                    card.id = 'thumb-card-' + i;
                    card.dataset.page = i;
                    card.onclick = () => {
                        scrollToPage(i, true);
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
                if (currentScale >= 3.0) return;
                currentScale = +(currentScale + 0.2).toFixed(2);
                zoomPercentLabel.textContent = Math.round(currentScale * 100) + '%';
                relayoutAndRerender();
            };

            document.getElementById('zoomOutBtn').onclick = () => {
                if (currentScale <= 0.5) return;
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

            // Live e-Reading Countdown Timer
            const expiresAtUnix = <?= (int)$expiresAtUnix ?>;
            if (expiresAtUnix > 0) {
                const timerText = document.getElementById('pdfTimerText');
                const timerBadge = document.getElementById('pdfTimerBadge');

                function updateReaderTimer() {
                    const now = Math.floor(Date.now() / 1000);
                    const diff = expiresAtUnix - now;

                    if (diff <= 0) {
                        if (timerText) timerText.textContent = '00m 00s Expired';
                        if (timerBadge) {
                            timerBadge.style.background = 'rgba(239, 68, 68, 0.4)';
                            timerBadge.style.borderColor = '#ef4444';
                            timerBadge.style.color = '#ffffff';
                        }
                        alert('⏱️ Your active e-reading session time has expired.');
                        window.close();
                        window.location.href = '<?= BASE_URL ?>?action=user&tab=books';
                        return;
                    }

                    const mins = Math.floor(diff / 60);
                    const secs = diff % 60;
                    const formatted = String(mins).padStart(2, '0') + 'm ' + String(secs).padStart(2, '0') + 's';
                    if (timerText) timerText.textContent = formatted;

                    if (diff <= 60) {
                        if (timerBadge) {
                            timerBadge.style.background = 'rgba(220, 38, 38, 0.35)';
                            timerBadge.style.borderColor = '#dc2626';
                            timerBadge.style.color = '#fca5a5';
                        }
                    } else if (diff <= 180) {
                        if (timerBadge) {
                            timerBadge.style.background = 'rgba(245, 158, 11, 0.25)';
                            timerBadge.style.borderColor = 'rgba(245, 158, 11, 0.6)';
                            timerBadge.style.color = '#fbbf24';
                        }
                    }
                }

                updateReaderTimer();
                setInterval(updateReaderTimer, 1000);
            }
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
        session_write_close();
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        if (!admin()) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        
        $req_count = (int)$db->query("SELECT COUNT(*) c FROM reading_requests WHERE status='Pending'")->fetch_assoc()['c'];
        $prt_count = (int)$db->query("SELECT COUNT(*) c FROM print_requests WHERE status='Pending'")->fetch_assoc()['c'];
        
        $read_query = $db->query("
            SELECT r.id, e.title, m.name as member_name, r.requested_at,
                   TIMESTAMPDIFF(SECOND, r.requested_at, NOW()) as age_secs
            FROM reading_requests r 
            JOIN ebooks e ON e.id = r.ebook_id 
            JOIN members m ON m.id = r.member_id 
            WHERE r.status = 'Pending' 
            ORDER BY r.id DESC LIMIT 15
        ");
        $recent_reading = [];
        while ($row = $read_query->fetch_assoc()) {
            $recent_reading[] = [
                'id' => (int)$row['id'],
                'type' => 'reading',
                'title' => $row['title'],
                'member' => $row['member_name'],
                'time' => $row['requested_at'],
                'age_secs' => (int)$row['age_secs']
            ];
        }
        
        $print_query = $db->query("
            SELECT p.id, e.title, m.name as member_name, p.pages, p.requested_at,
                   TIMESTAMPDIFF(SECOND, p.requested_at, NOW()) as age_secs
            FROM print_requests p 
            JOIN ebooks e ON e.id = p.ebook_id 
            JOIN members m ON m.id = p.member_id 
            WHERE p.status = 'Pending' 
            ORDER BY p.id DESC LIMIT 15
        ");
        $recent_print = [];
        while ($row = $print_query->fetch_assoc()) {
            $recent_print[] = [
                'id' => (int)$row['id'],
                'type' => 'print',
                'title' => $row['title'],
                'member' => $row['member_name'],
                'pages' => $row['pages'],
                'time' => $row['requested_at'],
                'age_secs' => (int)$row['age_secs']
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
            flash('⚠️ User ID is required.');
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
        
        try {
            $ok = $stmt->execute();
            $stmt->close();
            flash($ok ? 'Librarian profile updated successfully.' : '⚠️ User ID already exists.');
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ User ID already exists in system.');
        }
        go('?action=admin&tab=profile');
    }

    // --- BACKUP & RESTORE MANAGEMENT ROUTES ---
    if ($action === 'generate_db_backup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $backupService = new \App\Services\BackupService($db);
            $res = $backupService->createDatabaseBackup();
            flash("✅ Database backup created successfully: {$res['filename']} ({$res['size_formatted']})");
        } catch (\Exception $e) {
            flash("⚠️ Backup failed: " . $e->getMessage());
        }
        go('?action=admin&tab=backups');
    }

    if ($action === 'generate_full_backup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $backupService = new \App\Services\BackupService($db);
            $res = $backupService->createFullSystemBackup();
            flash("✅ Complete system backup (.zip) created successfully: {$res['filename']} ({$res['size_formatted']})");
        } catch (\Exception $e) {
            flash("⚠️ Full system backup failed: " . $e->getMessage());
        }
        go('?action=admin&tab=backups');
    }

    if ($action === 'download_backup') {
        $file = $_GET['file'] ?? '';
        if ($file === '') {
            flash('⚠️ No backup file specified.');
            go('?action=admin&tab=backups');
        }
        $backupService = new \App\Services\BackupService($db);
        $backupService->downloadBackup($file);
        exit;
    }

    if ($action === 'delete_backup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $file = $_POST['file'] ?? '';
        if ($file !== '') {
            $backupService = new \App\Services\BackupService($db);
            if ($backupService->deleteBackup($file)) {
                flash("✅ Backup file '{$file}' was deleted successfully.");
            } else {
                flash("⚠️ Failed to delete backup file or file not found.");
            }
        }
        go('?action=admin&tab=backups');
    }

    if ($action === 'restore_backup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $file = $_POST['file'] ?? '';
        
        // Check if an external .sql file was uploaded
        if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
            $uploadedTmp = $_FILES['backup_file']['tmp_name'];
            $uploadedName = $_FILES['backup_file']['name'];
            $ext = strtolower(pathinfo($uploadedName, PATHINFO_EXTENSION));
            if ($ext !== 'sql') {
                flash("⚠️ Only .sql backup files can be uploaded for database restoration.");
                go('?action=admin&tab=backups');
            }
            $safeName = 'uploaded_restore_' . date('Y-m-d_H-i-s') . '.sql';
            $backupService = new \App\Services\BackupService($db);
            $targetPath = $backupService->getBackupDir() . '/' . $safeName;
            if (move_uploaded_file($uploadedTmp, $targetPath)) {
                $file = $safeName;
            }
        }

        if ($file === '') {
            flash('⚠️ Please select or upload a valid .sql backup file to restore.');
            go('?action=admin&tab=backups');
        }

        try {
            $backupService = new \App\Services\BackupService($db);
            $res = $backupService->restoreDatabaseBackup($file);
            flash("✅ " . $res['message']);
        } catch (\Exception $e) {
            flash("⚠️ Database restoration failed: " . $e->getMessage());
        }
        go('?action=admin&tab=backups');
    }

    if ($action === 'add_category' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $n = trim($_POST['name'] ?? '');
        if ($n !== '') {
            $stmt = $db->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $n);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            if ($affected > 0) {
                flash('Category "' . e($n) . '" created successfully.');
            } else {
                flash('⚠️ Category name "' . e($n) . '" already exists.');
            }
        } else {
            flash('⚠️ Category name cannot be empty.');
        }
        go('?action=admin&tab=categories');
    }

    if ($action === 'delete_category' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Fetch category name for clearer toast message
            $catName = 'Category';
            $cStmt = $db->prepare("SELECT name FROM categories WHERE id = ? LIMIT 1");
            if ($cStmt) {
                $cStmt->bind_param("i", $id);
                $cStmt->execute();
                $cRes = $cStmt->get_result()->fetch_assoc();
                if ($cRes) $catName = '"' . e($cRes['name']) . '"';
                $cStmt->close();
            }

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

            try {
                $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $affected = $stmt->affected_rows;
                $stmt->close();
                if ($affected > 0) {
                    flash($catName . ' and all associated e-books deleted successfully.');
                } else {
                    flash('⚠️ Category not found or already deleted.');
                }
            } catch (\mysqli_sql_exception $e) {
                flash('⚠️ Failed to delete category due to a database constraint.');
            }
        } else {
            flash('⚠️ Invalid category ID specified.');
        }
        go('?action=admin&tab=categories');
    }

    if ($action === 'view_ebook_pdf') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM ebooks WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $b = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$b || !$b['pdf_file']) exit('Book not found.');
        
        $file = 'uploads/' . basename($b['pdf_file']);
        if (!is_file($file)) exit('PDF file does not exist on server.');
        
        // Stream directly using inline Content-Disposition for standard browser built-in PDF viewer
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($b['pdf_file']) . '"');
        header('Content-Length: ' . filesize($file));
        header('Accept-Ranges: bytes');
        readfile($file);
        exit;
    }

    if ($action === 'view_pdf_content') {
        if (!admin()) {
            http_response_code(403);
            exit('Unauthorized');
        }
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM ebooks WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $b = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($b && !empty($b['pdf_file'])) {
            $file = __DIR__ . '/uploads/' . basename($b['pdf_file']);
            if (is_file($file)) {
                stream_file_ranged($file, 'application/pdf', true, 300);
            }
        }
        http_response_code(404);
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
            mkdir($chunk_dir, 0755, true);
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
            mkdir('uploads', 0755, true);
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
        
        try {
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
        } catch (\mysqli_sql_exception $e) {
            if (is_file($out_path)) @unlink($out_path);
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Duplicate E-Book Title: An E-book with title "' . $t . '" already exists in this category.']);
        }
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
        fputcsv($output, ['Category', 'Book Code', 'Title', 'Author', 'Publisher', 'Price', 'Rack Location', 'Shelf Number']);
        
        $query = $db->query("SELECT * FROM physical_books ORDER BY id ASC");
        while ($row = $query->fetch_assoc()) {
            fputcsv($output, [
                'Physical Books',
                $row['book_code'],
                $row['title'],
                $row['author'],
                $row['publisher'] ?? '',
                $row['price'] ?? 0.00,
                $row['shelf_number'] ?? 'Shelf A1',
                $row['shelf_number'] ?? ''
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
                        // Format: Category, Book Code, Title, Author, Publisher, Price, Rack Location / Shelf Number
                        $code = trim($data[1]);
                        $title = trim($data[2]);
                        $author = trim($data[3] ?? 'Unknown');
                        $publisher = trim($data[4] ?? '');
                        $price = (float)($data[5] ?? 0.00);
                        $rack = trim($data[6] ?? '');
                        $shelf_num = trim($data[7] ?? $rack);
                        
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
                        
                        $insStmt = $db->prepare("INSERT INTO physical_books (book_code, title, author, publisher, price, shelf_number) VALUES (?, ?, ?, ?, ?, ?)");
                        $insStmt->bind_param("ssssds", $code, $title, $author, $publisher, $price, $shelf_num);
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
                flash("⚠️ Error opening uploaded CSV file.");
            }
        } else {
            flash("⚠️ Error uploading file. Please ensure it is a valid CSV.");
        }
        go('?action=admin&tab=' . $type);
    }

    if ($action === 'add_ebook' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_dir('uploads')) mkdir('uploads', 0755, true);
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
                
                try {
                    $stmt = $db->prepare("INSERT INTO ebooks (category_id, title, keywords, pdf_file) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isss", $c, $t, $k, $name);
                    $stmt->execute();
                    $stmt->close();
                    flash('E-book "' . e($t) . '" uploaded successfully.');
                } catch (\mysqli_sql_exception $e) {
                    flash('⚠️ Duplicate E-Book Title: An E-book titled "' . e($t) . '" already exists in this category.');
                }
            } else {
                flash('⚠️ Uploaded file is not a valid PDF MIME-type.');
            }
        } else {
            flash('⚠️ Only PDF files are allowed.');
        }
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
        
        flash('E-book deleted successfully.');
        go('?action=admin&tab=ebooks');
    }

    if ($action === 'add_member' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $d = trim($_POST['duration'] ?? '');
        $shift = trim($_POST['shift'] ?? '');
        if ($shift === '') {
            $shift = 'Morning';
        }
        $plan_id = isset($_POST['plan_id']) && $_POST['plan_id'] !== '' ? (int)$_POST['plan_id'] : null;
        $start = date('Y-m-d');
        
        $v = ['name', 'gender', 'guardian_name', 'mobile', 'password', 'email', 'address', 'aadhar_no', 'membership_fee', 'payment_id'];
        $data = [];
        foreach ($v as $k) {
            $data[$k] = trim($_POST[$k] ?? '');
        }
        // Ensure payment_id is never empty string (required NOT NULL column)
        if ($data['payment_id'] === '') {
            flash('⚠️ Error: Payment / Transaction ID is required.');
            go('?action=admin&tab=members');
        }
        
        $gender = in_array($data['gender'], ['Male', 'Female', 'Other']) ? $data['gender'] : 'Male';
        
        if ($data['name'] === '' || $data['mobile'] === '' || $data['aadhar_no'] === '') {
            flash('⚠️ Error: Name, Mobile, and Aadhar Number are required.');
            go('?action=admin&tab=members');
        }
        
        $fee = (float)$data['membership_fee'];
        $feeStr = '';
        
        // Auto-lookup amount & duration from selected membership_plan_id
        if ($plan_id) {
            $pStmt = $db->prepare("SELECT duration, amount FROM membership_plans WHERE id = ?");
            $pStmt->bind_param("i", $plan_id);
            $pStmt->execute();
            $pRes = $pStmt->get_result()->fetch_assoc();
            if ($pRes) {
                $fee = (float)$pRes['amount'];
                $d = $pRes['duration'];
                $data['membership_fee'] = $fee;
            }
            $pStmt->close();
        }
        // Validate duration against enum values
        $validDurations = ['Yearly', 'Half Yearly', 'Quarterly', 'Monthly', 'Daily'];
        if (!in_array($d, $validDurations)) $d = 'Yearly';
        $feeStr = (string)$fee;
        $end = membership_end($d);
        
        $temp_id = 'TEMP_M_' . uniqid('', true);
        
        // Hash password securely
        $hashed = password_hash($data['password'], PASSWORD_BCRYPT);
        
        // Check individual field uniqueness in database before attempting insert (Aadhar No & Transaction ID must be unique; Mobile can be duplicate)
        $dup_errors = [];
        $dup_fields = [];
        
        $chkAadhar = $db->prepare("SELECT id FROM members WHERE aadhar_no = ? LIMIT 1");
        $chkAadhar->bind_param("s", $data['aadhar_no']);
        $chkAadhar->execute();
        if ($chkAadhar->get_result()->num_rows > 0) {
            $dup_errors[] = "Aadhar Number ('" . e($data['aadhar_no']) . "') is already registered with an existing member.";
            $dup_fields[] = 'aadhar_no';
        }
        $chkAadhar->close();
        
        if ($data['payment_id'] !== '') {
            $chkPay = $db->prepare("SELECT id FROM members WHERE LOWER(payment_id) = LOWER(?) LIMIT 1");
            $chkPay->bind_param("s", $data['payment_id']);
            $chkPay->execute();
            $memPayExists = ($chkPay->get_result()->num_rows > 0);
            $chkPay->close();

            $chkHist = $db->prepare("SELECT id FROM membership_history WHERE LOWER(payment_id) = LOWER(?) LIMIT 1");
            $chkHist->bind_param("s", $data['payment_id']);
            $chkHist->execute();
            $histPayExists = ($chkHist->get_result()->num_rows > 0);
            $chkHist->close();

            if ($memPayExists || $histPayExists) {
                $dup_errors[] = "Transaction / Payment ID ('" . e($data['payment_id']) . "') has already been recorded in the database.";
                $dup_fields[] = 'payment_id';
            }
        }

        $chkMobile = $db->prepare("SELECT id FROM members WHERE mobile = ? LIMIT 1");
        $chkMobile->bind_param("s", $data['mobile']);
        $chkMobile->execute();
        if ($chkMobile->get_result()->num_rows > 0) {
            $dup_errors[] = "Mobile Number ('" . e($data['mobile']) . "') is already registered with another member account.";
            $dup_fields[] = 'mobile';
        }
        $chkMobile->close();

        if (count($dup_errors) > 0) {
            // Save form input draft to session, clearing only rejected duplicate fields
            $draft = $data;
            $draft['shift'] = $shift;
            $draft['plan_id'] = $plan_id;
            $draft['gender'] = $gender;
            
            foreach ($dup_fields as $fKey) {
                $draft[$fKey] = ''; // Clear rejected duplicate field
            }
            $_SESSION['reg_member_draft'] = $draft;
            
            $alertMsg = "⚠️ Membership Registration Blocked (Duplicate Records Found):\n\n" . implode("\n", $dup_errors) . "\n\nNote: Rejected duplicate fields have been emptied. Non-duplicate details have been preserved.";
            flash($alertMsg);
            go('?action=admin&tab=members');
        }
        
        try {
            $stmt = $db->prepare("INSERT INTO members (membership_id, name, gender, guardian_name, mobile, password, email, address, aadhar_no, duration, shift, start_date, end_date, payment_id, membership_plan_id, membership_fee) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                flash('⚠️ Error: ' . $db->error);
                go('?action=admin&tab=members');
            }
            // membership_fee is varchar(50) so use 's'; plan_id nullable int uses 'i'
            $stmt->bind_param("ssssssssssssssis",
                $temp_id, $data['name'], $gender, $data['guardian_name'],
                $data['mobile'], $hashed, $data['email'], $data['address'],
                $data['aadhar_no'], $d, $shift, $start, $end,
                $data['payment_id'], $plan_id, $feeStr
            );
            $ok = $stmt->execute();
            if (!$ok) {
                flash('⚠️ Error saving member: ' . $stmt->error);
                $stmt->close();
                go('?action=admin&tab=members');
            }
            $stmt->close();
            
            if ($ok) {
                unset($_SESSION['reg_member_draft']);
                $new_id = $db->insert_id;
                $mid_code = 'CBMDLM' . $new_id;
                $upStmt = $db->prepare("UPDATE members SET membership_id = ? WHERE id = ?");
                $upStmt->bind_param("si", $mid_code, $new_id);
                $upStmt->execute();
                $upStmt->close();
                log_membership_history($db, $new_id, 'Initial Joining');
                flash('✅ Member created successfully. Issued Membership ID: ' . $mid_code);
            }
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Duplicate Entry Error: A member with these details already exists. ' . $e->getMessage());
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
        $d = trim($_POST['duration'] ?? '');
        $shift = trim($_POST['shift'] ?? '');
        if ($shift === '') {
            $shift = 'Morning';
        }
        $fee = (float)($_POST['membership_fee'] ?? 0.00);
        $payment_id = trim($_POST['payment_id'] ?? '');

        if ($payment_id === '') {
            flash('⚠️ Error: Transaction / Payment Reference ID is required to approve membership.');
            go('?action=admin&tab=members&view=' . $id);
        }

        // Auto-lookup amount & duration from selected membership_plan_id
        if ($plan_id) {
            $pStmt = $db->prepare("SELECT duration, amount FROM membership_plans WHERE id = ?");
            $pStmt->bind_param("i", $plan_id);
            $pStmt->execute();
            $pRes = $pStmt->get_result()->fetch_assoc();
            if ($pRes) {
                $fee = (float)$pRes['amount'];
                $d = $pRes['duration'];
            }
            $pStmt->close();
        }
        if ($d === '') $d = 'Yearly';

        // Check for duplicate payment_id in membership_history or members
        $chkHist = $db->prepare("SELECT id FROM membership_history WHERE LOWER(payment_id) = LOWER(?) LIMIT 1");
        $chkHist->bind_param("s", $payment_id);
        $chkHist->execute();
        $histDup = ($chkHist->get_result()->num_rows > 0);
        $chkHist->close();

        $chkMem = $db->prepare("SELECT id FROM members WHERE LOWER(payment_id) = LOWER(?) AND id != ? LIMIT 1");
        $chkMem->bind_param("si", $payment_id, $id);
        $chkMem->execute();
        $memDup = ($chkMem->get_result()->num_rows > 0);
        $chkMem->close();

        if ($histDup || $memDup) {
            flash("⚠️ Duplicate Transaction Error: Payment ID ('" . e($payment_id) . "') has already been recorded for another transaction or membership. Approval rejected.");
            go('?action=admin&tab=members&view=' . $id);
        }

        $start = date('Y-m-d');
        $end = membership_end($d);
        $mid_code = 'CBMDLM' . $id;

        try {
            $stmt = $db->prepare("UPDATE members SET membership_id = ?, membership_plan_id = ?, membership_fee = ?, payment_id = ?, duration = ?, shift = ?, start_date = ?, end_date = ?, is_active = 1, approved = 1 WHERE id = ?");
            $stmt->bind_param("sissssssi", $mid_code, $plan_id, $fee, $payment_id, $d, $shift, $start, $end, $id);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                log_membership_history($db, $id, 'Initial Joining');
                flash('✅ Member application approved successfully! Issued Membership ID: ' . $mid_code);
            } else {
                flash('⚠️ Error approving member application.');
            }
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Duplicate Entry Error: Could not approve membership due to a duplicate transaction reference or database conflict.');
        }

        go('?action=admin&tab=members&view=' . $id);
    }

    if ($action === 'update_member' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $gender = in_array($_POST['gender'] ?? '', ['Male', 'Female', 'Other']) ? $_POST['gender'] : 'Male';
        $g_name = trim($_POST['guardian_name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $aadhar = trim($_POST['aadhar_no'] ?? '');
        $shift = in_array($_POST['shift'] ?? '', ['Both', 'Morning', 'Evening']) ? $_POST['shift'] : 'Both';
        $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
        $pass = trim($_POST['password'] ?? '');
        
        try {
            if ($pass !== '') {
                $hashed = password_hash($pass, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE members SET name = ?, gender = ?, guardian_name = ?, mobile = ?, email = ?, address = ?, aadhar_no = ?, shift = ?, is_active = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssssssssisi", $name, $gender, $g_name, $mobile, $email, $address, $aadhar, $shift, $is_active, $hashed, $id);
            } else {
                $stmt = $db->prepare("UPDATE members SET name = ?, gender = ?, guardian_name = ?, mobile = ?, email = ?, address = ?, aadhar_no = ?, shift = ?, is_active = ? WHERE id = ?");
                $stmt->bind_param("ssssssssii", $name, $gender, $g_name, $mobile, $email, $address, $aadhar, $shift, $is_active, $id);
            }
            $stmt->execute();
            $stmt->close();
            flash('Member profile updated successfully.');
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Duplicate Entry Error: A member with this Mobile number or Aadhar number already exists.');
        }
        $refTab = $_GET['tab'] ?? 'members';
        go('?action=admin&tab=' . $refTab . '&view=' . $id);
    }


    if ($action === 'renew_member' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        $plan_id = isset($_POST['plan_id']) && $_POST['plan_id'] !== '' ? (int)$_POST['plan_id'] : null;
        $payment_id = trim($_POST['payment_id'] ?? '');
        $fee = (float)($_POST['membership_fee'] ?? 0.00);
        $d = trim($_POST['duration'] ?? '');
        $refTab = $_GET['tab'] ?? 'members';

        if ($payment_id === '') {
            flash('⚠️ Error: Transaction / Payment Reference ID is required to process renewal.');
            go('?action=admin&tab=' . $refTab . '&view=' . $id);
        }

        // Fetch duration & amount from selected membership_plan_id FIRST before calculating end date
        if ($plan_id) {
            $pStmt = $db->prepare("SELECT duration, amount FROM membership_plans WHERE id = ?");
            $pStmt->bind_param("i", $plan_id);
            $pStmt->execute();
            $pRes = $pStmt->get_result()->fetch_assoc();
            if ($pRes) {
                $d = $pRes['duration'];
                $fee = (float)$pRes['amount'];
            }
            $pStmt->close();
        }

        $validDurations = ['Yearly', 'Half Yearly', 'Quarterly', 'Monthly', 'Daily'];
        if (!in_array($d, $validDurations)) $d = 'Yearly';

        // Fetch member details to check current active status and pass expiration
        $mStmt = $db->prepare("SELECT name, start_date, end_date, is_active, approved FROM members WHERE id = ?");
        $mStmt->bind_param("i", $id);
        $mStmt->execute();
        $mRes = $mStmt->get_result()->fetch_assoc();
        $mName = $mRes['name'] ?? 'Member';
        $mStmt->close();

        $today = date('Y-m-d');
        $isCurrentlyActive = ($mRes && !empty($mRes['end_date']) && $mRes['end_date'] >= $today && ($mRes['is_active'] ?? 1) == 1 && ($mRes['approved'] ?? 1) == 1);

        if ($isCurrentlyActive) {
            // Active membership: renewal term starts on the NEXT DAY after current pass expires
            $start = date('Y-m-d', strtotime($mRes['end_date'] . ' +1 day'));
        } else {
            // Expired or inactive membership: renewal term starts TODAY
            $start = $today;
        }

        // Calculate end date based on new start date AND updated duration $d
        $end = membership_end($d, $start);

        // Check for duplicate payment_id in membership_history or members
        $chkHist = $db->prepare("SELECT id FROM membership_history WHERE LOWER(payment_id) = LOWER(?) LIMIT 1");
        $chkHist->bind_param("s", $payment_id);
        $chkHist->execute();
        $histDup = ($chkHist->get_result()->num_rows > 0);
        $chkHist->close();

        $chkMem = $db->prepare("SELECT id FROM members WHERE LOWER(payment_id) = LOWER(?) AND id != ? LIMIT 1");
        $chkMem->bind_param("si", $payment_id, $id);
        $chkMem->execute();
        $memDup = ($chkMem->get_result()->num_rows > 0);
        $chkMem->close();

        if ($histDup || $memDup) {
            flash("⚠️ Duplicate Transaction Error: Transaction / Payment ID ('" . e($payment_id) . "') has already been recorded in the database. Renewal failed.");
            go('?action=admin&tab=' . $refTab . '&view=' . $id);
        }

        $shift = trim($_POST['shift'] ?? '');

        $db->begin_transaction();
        try {
            if ($shift !== '') {
                $stmt = $db->prepare("UPDATE members SET duration = ?, start_date = ?, end_date = ?, payment_id = ?, membership_plan_id = ?, membership_fee = ?, shift = ?, is_active = 1 WHERE id = ?");
                $stmt->bind_param("ssssidsi", $d, $start, $end, $payment_id, $plan_id, $fee, $shift, $id);
            } else {
                $stmt = $db->prepare("UPDATE members SET duration = ?, start_date = ?, end_date = ?, payment_id = ?, membership_plan_id = ?, membership_fee = ?, is_active = 1 WHERE id = ?");
                $stmt->bind_param("ssssidi", $d, $start, $end, $payment_id, $plan_id, $fee, $id);
            }
            $stmt->execute();
            $stmt->close();

            log_membership_history($db, $id, 'Renewal');
            $db->commit();

            if ($isCurrentlyActive) {
                $msg = '✅ Early Renewal Applied for ' . e($mName) . '! Active pass extended: New term starts ' . date('d M Y', strtotime($start)) . ' and valid until ' . date('d M Y', strtotime($end)) . ' (' . e($d) . ', Fee: ₹' . number_format($fee, 2) . ').';
            } else {
                $msg = '✅ Membership Renewed Successfully for ' . e($mName) . '! New active term: ' . date('d M Y', strtotime($start)) . ' to ' . date('d M Y', strtotime($end)) . ' (' . e($d) . ', Fee: ₹' . number_format($fee, 2) . ').';
            }
            flash($msg);
        } catch (\Throwable $e) {
            $db->rollback();
            flash('⚠️ Duplicate Entry Error: Could not process renewal due to a duplicate transaction reference or database constraint.');
        }

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
            try {
                $stmt = $db->prepare("INSERT INTO membership_plans (name, duration, amount) VALUES (?, ?, ?)");
                $stmt->bind_param("ssd", $name, $duration, $amount);
                $stmt->execute();
                $stmt->close();
                flash('Membership plan "' . e($name) . '" created successfully.');
            } catch (\mysqli_sql_exception $e) {
                flash('⚠️ Duplicate Plan Name: A membership plan named "' . e($name) . '" already exists.');
            }
        } else {
            flash('⚠️ Plan name is required.');
        }
        go('?action=admin&tab=active_plans');
    }

    if ($action === 'update_plan' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $duration = trim($_POST['duration'] ?? 'Yearly');
        $amount = (float)($_POST['amount'] ?? 0.00);
        
        if ($name !== '') {
            try {
                $stmt = $db->prepare("UPDATE membership_plans SET name = ?, duration = ?, amount = ? WHERE id = ?");
                $stmt->bind_param("ssdi", $name, $duration, $amount, $id);
                $stmt->execute();
                $stmt->close();
                flash('Membership plan "' . e($name) . '" updated successfully.');
            } catch (\mysqli_sql_exception $e) {
                flash('⚠️ Duplicate Plan Name: A membership plan named "' . e($name) . '" already exists.');
            }
        } else {
            flash('⚠️ Plan name is required.');
        }
        go('?action=admin&tab=active_plans');
    }

    if ($action === 'delete_plan' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        try {
            $stmt = $db->prepare("DELETE FROM membership_plans WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            flash('Membership plan deleted successfully.');
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Integrity Warning: Cannot delete a membership plan that is assigned to existing members.');
        }
        go('?action=admin&tab=active_plans');
    }

    if ($action === 'save_shift_times' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $shift_names = $_POST['shift_name'] ?? [];
        $start_times = $_POST['start_time'] ?? [];
        $end_times = $_POST['end_time'] ?? [];
        
        for ($i = 0; $i < count($shift_names); $i++) {
            $sname = trim($shift_names[$i]);
            $stime = trim($start_times[$i]);
            $etime = trim($end_times[$i]);
            
            if ($sname !== '' && $stime !== '' && $etime !== '') {
                // Ensure HH:MM:SS format
                if (strlen($stime) == 5) $stime .= ':00';
                if (strlen($etime) == 5) $etime .= ':00';
                
                $stmt = $db->prepare("INSERT INTO work_shifts (name, start_time, end_time) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE start_time = VALUES(start_time), end_time = VALUES(end_time)");
                $stmt->bind_param("sss", $sname, $stime, $etime);
                $stmt->execute();
                $stmt->close();
            }
        }
        
        // Handle optional dynamic custom shift addition
        $custom_name = trim($_POST['custom_shift_name'] ?? '');
        $custom_start = trim($_POST['custom_start_time'] ?? '');
        $custom_end = trim($_POST['custom_end_time'] ?? '');
        
        if ($custom_name !== '' && $custom_start !== '' && $custom_end !== '') {
            if (strlen($custom_start) == 5) $custom_start .= ':00';
            if (strlen($custom_end) == 5) $custom_end .= ':00';
            
            $stmt = $db->prepare("INSERT INTO work_shifts (name, start_time, end_time) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE start_time = VALUES(start_time), end_time = VALUES(end_time)");
            $stmt->bind_param("sss", $custom_name, $custom_start, $custom_end);
            $stmt->execute();
            $stmt->close();
            flash("Work Shift configuration and custom shift '" . $custom_name . "' saved.");
        } else {
            flash('Work Shift timing windows updated successfully.');
        }
        go('?action=admin&tab=shift_timings');
    }

    if ($action === 'settle_fine' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        $status = $_POST['fine_status'] ?? 'Paid';
        if (!in_array($status, ['Paid', 'Waived'])) {
            $status = 'Paid';
        }
        $pay_id = trim($_POST['fine_payment_id'] ?? '');
        $amount = max(0.00, (float)($_POST['fine_amount'] ?? 0.00));
        
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
        $shelf_number = trim($_POST['shelf_number'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $book_code = trim($_POST['book_code'] ?? '');
        $price = (float)($_POST['price'] ?? 0.00);
        $author = trim($_POST['author'] ?? '');
        $publisher = trim($_POST['publisher'] ?? '');
        
        try {
            $stmt = $db->prepare("UPDATE physical_books SET shelf_number = ?, title = ?, book_code = ?, price = ?, author = ?, publisher = ? WHERE id = ?");
            $stmt->bind_param("sssdssi", $shelf_number, $title, $book_code, $price, $author, $publisher, $id);
            $stmt->execute();
            $stmt->close();
            flash('Physical book updated successfully.');
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Duplicate Book Code / Bar Code: A physical book with ID "' . e($book_code) . '" already exists in the catalog database.');
        }
        go('?action=admin&tab=physical');
    }

    if ($action === 'update_ebook' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        $category_id = (int)$_POST['category_id'];
        $title = trim($_POST['title'] ?? '');
        $keywords = trim($_POST['keywords'] ?? '');
        
        try {
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
            
            $stmt->execute();
            $stmt->close();
            flash('E-book updated successfully.');
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Duplicate E-Book Title: An E-book titled "' . e($title) . '" already exists in this category.');
        }
        go('?action=admin&tab=ebooks');
    }

    if ($action === 'add_physical' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $shelf_number = trim($_POST['shelf_number'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $book_code = trim($_POST['book_code'] ?? '');
        $price = (float)($_POST['price'] ?? 0.00);
        $author = trim($_POST['author'] ?? '');
        $publisher = trim($_POST['publisher'] ?? '');
        
        try {
            $stmt = $db->prepare("INSERT INTO physical_books (shelf_number, title, book_code, price, author, publisher) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdss", $shelf_number, $title, $book_code, $price, $author, $publisher);
            $stmt->execute();
            $stmt->close();
            flash('Physical book added successfully.');
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Duplicate Book Code / Bar Code: A physical book with ID "' . e($book_code) . '" already exists in the catalog database.');
        }
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
        
        $stmt = $db->prepare("UPDATE reading_requests SET status = 'Approved', approved_at = NOW(), duration_minutes = ?, started_reading_at = NULL, expires_at = NULL WHERE id = ?");
        $stmt->bind_param("ii", $mins, $id);
        $stmt->execute();
        $stmt->close();
        
        flash('Reading permission approved successfully. Session timer will start when member clicks Read Now.');
        go('?action=admin&tab=requests');
    }

    if ($action === 'reject' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['request_id'] ?? $_POST['id'] ?? 0);
        $stmt = $db->prepare("UPDATE reading_requests SET status = 'Rejected' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        flash('Request rejected successfully.');
        go('?action=admin&tab=requests');
    }

    if ($action === 'lend' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $find = trim($_POST['member'] ?? '');
        $b = trim($_POST['book_code'] ?? '');
        $date = date('Y-m-d H:i:s');
        $due = $_POST['due_date'] ?? '';
        $tx = trim($_POST['transaction_id'] ?? '');
        
        if (!$tx) {
            flash('⚠️ Payment / Transaction ID is mandatory.');
            go('?action=admin&tab=lending');
        }
        
        // Find Member
        $mStmt = $db->prepare("SELECT id, is_active, end_date FROM members WHERE membership_id = ? OR mobile = ? LIMIT 1");
        $mStmt->bind_param("ss", $find, $find);
        $mStmt->execute();
        $m = $mStmt->get_result()->fetch_assoc();
        $mStmt->close();
        
        if ($m) {
            if ($m['is_active'] == 0) {
                flash('⚠️ Issue Blocked: This member account is currently suspended/inactive.');
                go('?action=admin&tab=lending');
            }
            if (strtotime($m['end_date']) < time()) {
                flash('⚠️ Issue Blocked: This member account has expired.');
                go('?action=admin&tab=lending');
            }
        }
        
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
            
            // Auto-complete any active/awaiting holds for this member and book
            $db->query("UPDATE hold_requests SET status = 'Completed' WHERE physical_book_id = " . (int)$book['id'] . " AND member_id = " . (int)$m['id'] . " AND status IN ('Active', 'Awaiting Collection')");
            
            flash('Book issued successfully.');
        } else {
            flash('⚠️ Member or available book copy was not found.');
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
                flash('Book returned successfully and marked as available.');
            }
        } else {
            flash('Book returned successfully.');
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
        flash('Print request marked as completed successfully.');
        go('?action=admin&tab=prints');
    }

    if ($action === 'reject_print' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("UPDATE print_requests SET status = 'Rejected' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        flash('Print request rejected successfully.');
        go('?action=admin&tab=prints');
    }
}

// 6. Member Controllers (Prepared Statements & Secure Actions)
if (member()) {
    $mid = (int)$_SESSION['member'];

    if ($action === 'read_member_pdf') {
        $rid = (int)($_GET['id'] ?? 0);
        
        $stmt = $db->prepare("SELECT r.*, e.pdf_file, e.title FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE (r.id = ? OR r.ebook_id = ?) AND r.member_id = ? AND r.status = 'Approved' AND (r.started_reading_at IS NULL OR r.expires_at > NOW()) ORDER BY r.id DESC LIMIT 1");
        $stmt->bind_param("iii", $rid, $rid, $mid);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$r || empty($r['pdf_file'])) {
            exit('<div style="font-family:system-ui, sans-serif; text-align:center; padding:60px 20px; color:#ef4444; background:#0b0f19; height:100vh; box-sizing:border-box;"><h2 style="font-size:24px; margin-bottom:12px;">⚠️ Permission Expired or Book Not Found</h2><p style="color:#9ca3af; font-size:15px; max-width:500px; margin:0 auto 20px;">Your e-reading request for this book is either not approved or your active reading session has expired.</p><a href="' . BASE_URL . '?action=user&tab=books" style="display:inline-block; padding:10px 20px; background:#3b82f6; color:#fff; text-decoration:none; border-radius:8px; font-weight:600;">Return to Dashboard</a></div>');
        }

        if (empty($r['started_reading_at'])) {
            $duration = !empty($r['duration_minutes']) ? (int)$r['duration_minutes'] : 15;
            $upStmt = $db->prepare("UPDATE reading_requests SET started_reading_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?");
            $upStmt->bind_param("ii", $duration, $r['id']);
            $upStmt->execute();
            $upStmt->close();
        }
        
        go(BASE_URL . '?action=secure_pdf_viewer&source=member&id=' . urlencode($r['id']));
    }

    if ($action === 'read_member_pdf_content') {
        $rid = (int)($_GET['id'] ?? 0);
        
        $stmt = $db->prepare("SELECT r.id, r.duration_minutes, r.started_reading_at, r.expires_at, e.pdf_file FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE (r.id = ? OR r.ebook_id = ?) AND r.member_id = ? AND r.status = 'Approved' AND (r.started_reading_at IS NULL OR r.expires_at > NOW()) ORDER BY r.id DESC LIMIT 1");
        $stmt->bind_param("iii", $rid, $rid, $mid);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($r && !empty($r['pdf_file'])) {
            if (empty($r['started_reading_at'])) {
                $duration = !empty($r['duration_minutes']) ? (int)$r['duration_minutes'] : 15;
                $upStmt = $db->prepare("UPDATE reading_requests SET started_reading_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?");
                $upStmt->bind_param("ii", $duration, $r['id']);
                $upStmt->execute();
                $upStmt->close();
            }
            $file = __DIR__ . '/uploads/' . basename($r['pdf_file']);
            if (is_file($file)) {
                stream_file_ranged($file, 'application/pdf', true, 300);
            }
        }
        http_response_code(403);
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
        $cntStmt = $db->prepare("SELECT COUNT(*) c FROM reading_requests WHERE member_id = ? AND (status = 'Pending' OR (status = 'Approved' AND (started_reading_at IS NULL OR expires_at > NOW())))");
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
            flash('Reading request submitted successfully to the librarian.');
        } else {
            flash('⚠️ Reading request already submitted and pending approval.');
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
        $query = $db->query("SELECT r.id, r.ebook_id, r.status, r.started_reading_at, r.expires_at, e.title FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.member_id = $mid AND r.id = (SELECT MAX(r2.id) FROM reading_requests r2 WHERE r2.member_id = $mid AND r2.ebook_id = r.ebook_id)");
        $updates = [];
        while ($row = $query->fetch_assoc()) {
            $updates[] = [
                'request_id' => (int)$row['id'],
                'ebook_id' => (int)$row['ebook_id'],
                'status' => $row['status'],
                'title' => $row['title'],
                'active' => ($row['status'] === 'Approved' && (empty($row['started_reading_at']) || strtotime($row['expires_at']) > time()))
            ];
        }
        echo json_encode($updates);
        exit;
    }

    if ($action === 'poll_member_notifications') {
        session_write_close();
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
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
        
        if ($p === '') {
            flash('⚠️ Please specify valid page numbers for printing.');
            go('?action=user&tab=books');
        }
        
        $stmt = $db->prepare("INSERT INTO print_requests (member_id, ebook_id, pages) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $mid, $id, $p);
        $stmt->execute();
        $stmt->close();
        
        flash('Print request submitted successfully to the librarian.');
        go('?action=user&tab=books');
    }
}

// Render layout and views modularly
require 'app/views/layout/header.php';

if ($action === 'admin_login' || $action === 'member_login') {
    require 'app/views/login.php';
} elseif ($action === 'admin_forgot_password') {
    require 'app/views/admin_forgot_password.php';
} elseif ($action === 'admin') {
    require 'app/views/admin.php';
} elseif ($action === 'user') {
    require 'app/views/user.php';
}

require 'app/views/layout/footer.php';
