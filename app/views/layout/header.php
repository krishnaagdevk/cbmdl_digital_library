<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <script>window.BASE_URL = "<?= BASE_URL ?>";</script>
    <title>MCB e-Library</title>
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-slate: #f8fafc;
            --bg-card: #ffffff;
            --navy-dark: #0f172a;
            --navy-light: #1e293b;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --accent-orange: #f59e0b;
        }

        * {
            box-sizing: border-box;
            transition: all 0.2s ease-in-out;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #f8fafc, #eff6ff);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3, h4, strong {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--navy-dark);
        }

        /* Premium Nav */
        nav {
            background: linear-gradient(135deg, var(--navy-dark), var(--navy-light));
            color: #fff;
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.15);
            z-index: 1000;
        }

        nav .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        nav .logo-area i {
            color: var(--primary);
            font-size: 26px;
        }

        nav .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        nav a {
            color: #cbd5e1;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            padding: 8px 16px;
            border-radius: 8px;
        }

        nav a:hover, nav a.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }

        nav .logout-btn {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            color: white;
            font-weight: 600;
        }

        nav .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        /* Banner & Branding */
        .header-logo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 5%;
            background: white;
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }

        .header-logo img {
            max-height: 80px;
            object-fit: contain;
        }

        .header-logo h1 {
            margin: 0;
            font-size: 24px;
            text-align: center;
            flex: 1;
            line-height: 1.4;
            font-weight: 700;
            color: var(--navy-dark);
        }

        .header-title-sub {
            text-align: center;
            margin: 10px 0 5px 0;
            font-size: 28px;
            font-weight: 700;
            color: var(--navy-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Main Workspace Layout */
        main {
            flex: 1;
            padding: 30px 5%;
            max-width: 1600px;
            margin: 0 auto;
            width: 100%;
        }

        /* Dashboard Sidebar Layout */
        .admin-wrapper {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
            margin-top: 20px;
        }

        @media (max-width: 1024px) {
            .admin-wrapper {
                grid-template-columns: 1fr;
            }
            .sidebar {
                display: none !important; /* Managed by mobile drawer JS */
            }
            .header-logo {
                flex-direction: column;
                gap: 15px;
            }
            .header-logo h1 {
                font-size: 18px;
            }
        }

        /* Elegant Sidebar */
        .sidebar {
            background: white;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            padding: 20px 15px;
            height: fit-content;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            border-radius: 10px;
            font-size: 14px;
        }

        .sidebar a:hover {
            background: #eff6ff;
            color: var(--primary);
        }

        .sidebar a.active {
            background: var(--primary);
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.25);
        }

        /* Cards & Surfaces */
        .card {
            background: var(--bg-card);
            padding: 26px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(22, 52, 91, 0.04);
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), #60a5fa);
            opacity: 0;
        }

        .card:hover::before {
            opacity: 1;
        }

        .card h3 {
            margin-top: 0;
            font-size: 20px;
            font-weight: 600;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
        }

        /* Quick Stat Widgets */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 22px;
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 24px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-card .stat-info h4 {
            margin: 0;
            font-size: 13px;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        .stat-card .stat-info p {
            margin: 5px 0 0 0;
            font-size: 28px;
            font-weight: 700;
            color: var(--navy-dark);
        }

        .stat-card .stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .stat-blue { background: #eff6ff; color: var(--primary); }
        .stat-green { background: #ecfdf5; color: var(--accent-green); }
        .stat-red { background: #fef2f2; color: var(--accent-red); }
        .stat-orange { background: #fffbeb; color: var(--accent-orange); }

        /* Login Layout & Elements */
        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
            margin: 40px auto;
            max-width: 1100px;
            padding: 0 20px;
        }

        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        .login-section {
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(15,23,42,0.06);
            border: 1px solid var(--border-color);
        }

        .external-menu {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .external-menu a {
            background: #fff;
            border: 1px solid var(--border-color);
            color: var(--navy-dark);
            padding: 16px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .external-menu a:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateX(6px);
        }

        /* Forms, Inputs, Buttons */
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 14px;
            color: var(--navy-dark);
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 16px;
            margin: 6px 0 18px 0;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        button, .btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            color: white;
            border: 0;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        button:hover, .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.25);
        }

        button:active, .btn:active {
            transform: translateY(0);
        }

        .btn-danger, .btn.danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
        }

        .btn-danger:hover, .btn.danger:hover {
            box-shadow: 0 6px 15px rgba(239, 68, 68, 0.25);
        }

        /* Table Styling */
        .table-responsive {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            margin-top: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            background: #ffffff;
        }

        th {
            background: #f1f5f9;
            color: var(--navy-dark);
            font-weight: 600;
            padding: 14px 18px;
            text-align: left;
            border-bottom: 2px solid var(--border-color);
        }

        td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-dark);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        /* Status Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            gap: 5px;
        }

        .badge-green { background: #ecfdf5; color: var(--accent-green); }
        .badge-red { background: #fef2f2; color: var(--accent-red); }
        .badge-orange { background: #fffbeb; color: var(--accent-orange); }
        .badge-blue { background: #eff6ff; color: var(--primary); }

        /* Notification Banners */
        .notice {
            background: #ecfdf5;
            color: var(--accent-green);
            padding: 15px 24px;
            border-radius: 12px;
            font-weight: 600;
            border-left: 5px solid var(--accent-green);
            margin: 20px auto;
            max-width: 1200px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08);
        }
        .notice-red {
            background: #fef2f2 !important;
            color: var(--accent-red, #ef4444) !important;
            border-left: 5px solid var(--accent-red, #ef4444) !important;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.08) !important;
        }

        /* Modals */
        .pdf-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
        }

        .pdf-modal.show {
            display: flex;
        }

        .pdf-modal-content {
            background: white;
            width: 90%;
            max-width: 1300px;
            height: 90%;
            border-radius: 18px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            animation: modalScale 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            transition: all 0.3s ease;
        }

        .pdf-modal-content.is-fullscreen {
            width: 100vw !important;
            max-width: 100vw !important;
            height: 100vh !important;
            border-radius: 0 !important;
        }

        @keyframes modalScale {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .pdf-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 26px;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-slate);
        }

        .pdf-modal-header h3 {
            margin: 0;
            font-size: 18px;
        }

        .pdf-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-muted);
        }

        .pdf-modal-close:hover {
            color: var(--accent-red);
        }

        .pdf-modal-body {
            flex: 1;
        }

        .pdf-modal iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Chart Canvas Layouts */
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
        }

        .chart-wrapper {
            background: white;
            padding: 24px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(0,0,0,0.01);
            min-height: 330px;
            display: flex;
            flex-direction: column;
        }

        .chart-wrapper h4 {
            margin: 0 0 15px 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--navy-dark);
            width: 100%;
        }

        .chart-canvas-container {
            position: relative;
            width: 100%;
            height: 250px;
            min-height: 250px;
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, var(--navy-dark), var(--navy-light));
            color: #94a3b8;
            text-align: center;
            padding: 25px;
            margin-top: auto;
            font-size: 14px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        footer strong {
            color: #ffffff;
        }

        .password-wrapper {
            position: relative;
            width: 100%;
            margin: 6px 0 18px 0;
            display: block;
        }
        .password-toggle-btn {
            position: absolute;
            right: 14px;
            top: 10%;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            padding: 4px;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .password-toggle-btn:hover {
            color: #10b981;
        }
    </style>
</head>
<body>

    <!-- Dynamic Session Flash Messages -->
    <?php if ($f = flash()): ?>
        <?php
        $is_error_flash = (
            stristr($f, 'invalid') !== false ||
            stristr($f, 'error') !== false ||
            stristr($f, 'warning') !== false ||
            stristr($f, 'expired') !== false ||
            stristr($f, 'suspended') !== false ||
            stristr($f, 'failed') !== false ||
            stristr($f, 'rejected') !== false ||
            stristr($f, 'cannot') !== false ||
            stristr($f, 'required') !== false ||
            stristr($f, '⚠️') !== false
        );
        ?>
        <div class="notice <?= $is_error_flash ? 'notice-red' : '' ?>">
            <i class="fa-solid <?= $is_error_flash ? 'fa-circle-xmark' : 'fa-circle-check' ?>" style="<?= $is_error_flash ? 'color:var(--accent-red);' : '' ?>"></i>
            <span><?= e($f) ?></span>
        </div>
        <?php if ($is_error_flash): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const msg = <?= json_encode($f) ?>;
                    // Custom Modal Dialog Popup
                    let modal = document.getElementById('errorNoticeModalDialog');
                    if (!modal) {
                        modal = document.createElement('div');
                        modal.id = 'errorNoticeModalDialog';
                        modal.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:99999; display:flex; align-items:center; justify-content:center; padding:20px;';
                        modal.innerHTML = `
                            <div style="background:var(--bg-card, #ffffff); border-radius:16px; border:1px solid var(--border-color, #e2e8f0); width:100%; max-width:440px; padding:28px 24px 24px 24px; box-shadow:0 20px 40px rgba(0,0,0,0.2); text-align:center; animation:modalScale 0.25s ease-out;">
                                <div style="width:60px; height:60px; border-radius:50%; background:#fef2f2; color:#ef4444; display:flex; align-items:center; justify-content:center; margin:0 auto 16px auto; font-size:28px; border:2px solid #fca5a5;">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <h3 style="margin:0 0 10px 0; font-size:18px; font-weight:700; color:var(--navy-dark, #0f172a);">Duplicate / Invalid Entry Alert</h3>
                                <p style="font-size:14px; color:var(--text-dark, #1e293b); line-height:1.5; margin:0 0 24px 0; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0; text-align:left;">${typeof escapeHtml === 'function' ? escapeHtml(msg) : msg}</p>
                                <button onclick="document.getElementById('errorNoticeModalDialog').remove()" style="width:100%; background:linear-gradient(135deg, #ef4444, #dc2626); color:white; border:none; padding:12px 20px; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:8px;">
                                    <i class="fa-solid fa-circle-xmark"></i> Close & Acknowledge
                                </button>
                            </div>
                        `;
                        document.body.appendChild(modal);
                    }
                });
            </script>
        <?php else: ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const msg = <?= json_encode($f) ?>;
                    // Toast Notification Popup Component
                    let container = document.getElementById('toastNotificationContainer');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'toastNotificationContainer';
                        container.style.cssText = 'position:fixed; top:24px; right:24px; display:flex; flex-direction:column; gap:12px; z-index:99999; pointer-events:none; max-width:380px; width:calc(100% - 48px);';
                        document.body.appendChild(container);
                    }
                    
                    const toast = document.createElement('div');
                    toast.style.cssText = `
                        background: #ffffff;
                        color: #0f172a;
                        border-radius: 12px;
                        padding: 16px;
                        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
                        border: 1px solid #e2e8f0;
                        border-left: 5px solid #10b981;
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        pointer-events: auto;
                        transform: translateX(120%);
                        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s;
                        opacity: 0;
                    `;
                    
                    toast.innerHTML = `
                        <div style="width:32px; height:32px; border-radius:50%; background:#ecfdf5; color:#10b981; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:16px;">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div style="flex:1; font-size:13px; font-weight:600; color:#0f172a; line-height:1.4;">
                            ${typeof escapeHtml === 'function' ? escapeHtml(msg) : msg}
                        </div>
                        <button onclick="this.parentElement.remove()" style="background:none; border:none; padding:0; cursor:pointer; color:#94a3b8; font-size:14px;"><i class="fa-solid fa-xmark"></i></button>
                    `;
                    
                    container.appendChild(toast);
                    
                    requestAnimationFrame(() => {
                        toast.style.transform = 'translateX(0)';
                        toast.style.opacity = '1';
                    });
                    
                    setTimeout(() => {
                        toast.style.transform = 'translateX(120%)';
                        toast.style.opacity = '0';
                        setTimeout(() => toast.remove(), 400);
                    }, 5000);
                });
            </script>
        <?php endif; ?>
        <?php if ($f === 'Reading request sent to librarian.'): ?>
            <script>
                alert("🎉 Your e-reading permission request has been submitted successfully to the librarian!\n\nPlease wait for the librarian to review and approve your request.");
            </script>
        <?php elseif ($f === 'Print request sent to librarian.'): ?>
            <script>
                alert("🖨️ Your print job request has been submitted successfully to the librarian!\n\nPlease check with the library front desk for printed handouts.");
            </script>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Navigation Bar -->
    <!-- <nav>
        <div class="logo-area">
            <i class="fa-solid fa-book-bookmark"></i>
            <span>CBMDL e-Library</span>
        </div>
        <div class="nav-links">
            <?php if (admin() && $action !== 'admin_login' && $action !== 'member_login'): ?>
                <a href="?action=admin" class="<?= $action === 'admin' ? 'active' : '' ?>"><i class="fa-solid fa-house-chimney"></i> Dashboard <span id="headerUploadBadge" style="display:none; margin-left:6px;"></span></a>
                <a href="?action=logout" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            <?php elseif (member() && $action !== 'admin_login' && $action !== 'member_login'): ?>
                <a href="?action=user" class="<?= $action === 'user' ? 'active' : '' ?>"><i class="fa-solid fa-house-chimney"></i> My Portal</a>
                <a href="?action=logout" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            <?php else: ?>
                <a href="member-login" class="<?= $action === 'member_login' ? 'active' : '' ?>"><i class="fa-solid fa-user"></i> Member Login</a>
                <a href="admin-login" class="<?= $action === 'admin_login' ? 'active' : '' ?>"><i class="fa-solid fa-user-tie"></i> Librarian Login</a>
            <?php endif; ?>
        </div>
    </nav> -->

    <!-- Header Branding -->
    <div class="header-logo">
        <img src="<?= BASE_URL ?>images/header_banner.png" alt="Branding Left" />
        <h1>श्री प्यारे लाल चिरन्जी लाल<br><span style="font-size:18px; font-weight:500; color:var(--text-muted);">कैन्टोनमेन्ट पुस्तकालय मेरठ</span></h1>
        <img src="<?= BASE_URL ?>images/2022111817.png" alt="Branding Right" />
    </div>

    <!-- Main Content Area -->
    <main>
