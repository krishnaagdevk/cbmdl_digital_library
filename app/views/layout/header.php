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
            padding: 16px 5%;
            background: #ffffff;
            border-bottom: 2px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.03);
        }

        .header-logo img {
            max-height: 85px;
            object-fit: contain;
            mix-blend-mode: multiply;
            filter: contrast(115%) brightness(102%);
        }

        .header-logo h1 {
            margin: 0;
            font-size: 26px;
            text-align: center;
            flex: 1;
            line-height: 1.35;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }

        .header-logo h1 .header-sub-text {
            display: inline-block;
            margin-top: 4px;
            font-size: 18px;
            font-weight: 600;
            color: #15803d;
            letter-spacing: 0.8px;
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
            padding: 15px 3%;
            max-width: 1600px;
            margin: 0 auto;
            width: 100%;
        }

        /* Dashboard Sidebar Layout */
        .admin-wrapper {
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 18px;
            margin-top: 10px;
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
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 12px 10px;
            height: fit-content;
            box-shadow: 0 6px 20px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            border-radius: 8px;
            font-size: 13.5px;
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

        /* Sidebar Accordion Submenu Styles */
        .sidebar-item-group {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .sidebar-parent-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 9px 14px;
            background: none;
            border: none;
            color: var(--text-dark);
            font-weight: 500;
            border-radius: 8px;
            font-size: 13.5px;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .sidebar-parent-btn:hover {
            background: #eff6ff;
            color: var(--primary);
        }

        .sidebar-parent-btn.active-parent {
            background: rgba(37, 99, 235, 0.08);
            color: var(--primary);
            font-weight: 700;
            border: 1px solid rgba(37, 99, 235, 0.2);
        }

        .sidebar-parent-btn .toggle-icon {
            font-size: 11px;
            transition: transform 0.25s ease;
            color: var(--text-muted);
        }

        .sidebar-parent-btn.active-parent .toggle-icon,
        .sidebar-parent-btn:hover .toggle-icon {
            color: var(--primary);
        }

        .sidebar-item-group.open .toggle-icon {
            transform: rotate(180deg);
        }

        .sidebar-submenu {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding-left: 10px;
            margin-top: 3px;
            margin-bottom: 4px;
            border-left: 2.5px solid rgba(37, 99, 235, 0.25);
            margin-left: 12px;
        }

        .sidebar-submenu a {
            padding: 7.5px 10px;
            font-size: 12.5px;
            border-radius: 6px;
        }

        /* Cards & Surfaces */
        .card {
            background: var(--bg-card);
            padding: 18px 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 6px 20px rgba(22, 52, 91, 0.03);
            margin-bottom: 16px;
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
            font-size: 17px;
            font-weight: 600;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 8px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 16px;
        }

        /* Quick Stat Widgets */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 14px 18px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 16px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-card .stat-info h4 {
            margin: 0;
            font-size: 12px;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        .stat-card .stat-info p {
            margin: 3px 0 0 0;
            font-size: 22px;
            font-weight: 700;
            color: var(--navy-dark);
        }

        .stat-card .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .stat-blue { background: #eff6ff; color: var(--primary); }
        .stat-green { background: #ecfdf5; color: var(--accent-green); }
        .stat-red { background: #fef2f2; color: var(--accent-red); }
        .stat-orange { background: #fffbeb; color: var(--accent-orange); }
        .stat-purple { background: #f3e8ff; color: #8b5cf6; }

        /* Login Layout & Elements */
        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: start;
            margin: 10px auto;
            max-width: 1100px;
            padding: 0 15px;
        }

        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }

        .login-section {
            background: #fff;
            padding: 26px 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15,23,42,0.05);
            border: 1px solid var(--border-color);
        }

        .external-menu {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .external-menu a {
            background: #fff;
            border: 1px solid var(--border-color);
            color: var(--navy-dark);
            padding: 12px 18px;
            border-radius: 10px;
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
            transform: translateX(4px);
        }

        /* Forms, Inputs, Buttons */
        label {
            display: block;
            margin-bottom: 4px;
            font-weight: 600;
            font-size: 13px;
            color: var(--navy-dark);
        }

        input, select, textarea {
            width: 100%;
            padding: 8px 12px;
            margin: 4px 0 12px 0;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
            font-size: 13.5px;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        button, .btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            color: white;
            border: 0;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            box-shadow: 0 3px 10px rgba(37, 99, 235, 0.15);
        }

        button:hover, .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        button:active, .btn:active {
            transform: translateY(0);
        }

        .btn-danger, .btn.danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 3px 10px rgba(239, 68, 68, 0.15);
        }

        .btn-danger:hover, .btn.danger:hover {
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        /* Table Styling */
        .table-responsive {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            background: #ffffff;
        }

        th {
            background: #f1f5f9;
            color: var(--navy-dark);
            font-weight: 600;
            padding: 9px 12px;
            text-align: left;
            border-bottom: 2px solid var(--border-color);
        }

        td {
            padding: 8px 12px;
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
        .notice-orange {
            background: #fffbeb !important;
            color: #b45309 !important;
            border-left: 5px solid #f59e0b !important;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.12) !important;
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
        /* Login Page Header Spacing & Font Adjustments */
        body.login-page .header-logo {
            padding: 12px 5% 6px 5% !important;
            margin-bottom: 0 !important;
        }

        body.login-page .header-logo img {
            max-height: 80px !important;
        }

        body.login-page .header-logo h1 {
            font-size: 26px !important;
            line-height: 1.3 !important;
        }

        body.login-page .header-logo h1 .header-sub-text {
            font-size: 18px !important;
            margin-top: 3px !important;
        }

        body.login-page .header-title-sub {
            margin: 4px 0 6px 0 !important;
            font-size: 26px !important;
        }

        body.login-page main {
            padding: 4px 3% 10px 3% !important;
        }

        body.login-page footer {
            padding: 8px 15px !important;
            font-size: 12px !important;
        }
    </style>
</head>
<?php
$is_login_view = in_array($_GET['action'] ?? '', ['admin-login', 'member-login', 'login']) || (empty($_SESSION['admin']) && empty($_SESSION['member']));
?>
<body class="<?= $is_login_view ? 'login-page' : '' ?>">

    <!-- Global Toast Notification System -->
    <script>
        window.showToast = function(msg, type, targetUrl) {
            if (!msg) return;

            // Auto-assign targetUrl based on toast type if not explicitly supplied
            if (!targetUrl) {
                if (type === 'reading') {
                    targetUrl = (window.BASE_URL || '') + 'index.php?action=admin&tab=requests';
                } else if (type === 'print') {
                    targetUrl = (window.BASE_URL || '') + 'index.php?action=admin&tab=prints';
                }
            }

            var container = document.getElementById('toastNotificationContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toastNotificationContainer';
                container.style.position = 'fixed';
                container.style.top = '24px';
                container.style.right = '24px';
                container.style.display = 'flex';
                container.style.flexDirection = 'column';
                container.style.gap = '12px';
                container.style.zIndex = '999999';
                container.style.pointerEvents = 'none';
                container.style.maxWidth = '420px';
                container.style.width = 'calc(100% - 48px)';
                document.body.appendChild(container);
            }

            var isError   = (type === 'error' || type === 'danger');
            var isWarning = (type === 'warning' || type === 'orange');
            var isReading = (type === 'reading');
            var isPrint   = (type === 'print');
            var isInfo    = (type === 'info');

            var borderColor = isWarning ? '#f59e0b' : (isError ? '#ef4444' : (isReading ? '#8b5cf6' : (isPrint ? '#3b82f6' : (isInfo ? '#0284c7' : '#10b981'))));
            var iconBg     = isWarning ? '#fffbeb' : (isError ? '#fef2f2' : (isReading ? '#f5f3ff' : (isPrint ? '#eff6ff' : (isInfo ? '#f0f9ff' : '#ecfdf5'))));
            var iconColor  = isWarning ? '#f59e0b' : (isError ? '#ef4444' : (isReading ? '#8b5cf6' : (isPrint ? '#3b82f6' : (isInfo ? '#0284c7' : '#10b981'))));
            var iconClass  = isWarning ? 'fa-triangle-exclamation' : (isError ? 'fa-circle-xmark' : (isReading ? 'fa-book-open' : (isPrint ? 'fa-print' : (isInfo ? 'fa-circle-info' : 'fa-circle-check'))));

            var toast = document.createElement('div');
            var toastStyles = [
                'background:#ffffff',
                'color:#0f172a',
                'border-radius:12px',
                'padding:16px',
                'box-shadow:0 12px 30px -5px rgba(0,0,0,.2),0 8px 10px -6px rgba(0,0,0,.1)',
                'border:1px solid #e2e8f0',
                'border-left:6px solid ' + borderColor,
                'display:flex',
                'align-items:flex-start',
                'gap:12px',
                'pointer-events:auto',
                'transform:translateX(120%)',
                'transition:transform .4s cubic-bezier(.16,1,.3,1),opacity .4s,box-shadow .2s',
                'opacity:0'
            ];

            if (targetUrl) {
                toastStyles.push('cursor:pointer');
            }

            toast.style.cssText = toastStyles.join(';');

            if (targetUrl) {
                toast.addEventListener('mouseenter', function() {
                    toast.style.boxShadow = '0 16px 35px -5px rgba(0,0,0,.25),0 10px 12px -6px rgba(0,0,0,.15)';
                });
                toast.addEventListener('mouseleave', function() {
                    toast.style.boxShadow = '0 12px 30px -5px rgba(0,0,0,.2),0 8px 10px -6px rgba(0,0,0,.1)';
                });
                toast.addEventListener('click', function(e) {
                    if (e.target.closest('.toast-close-btn')) return;
                    if (typeof window.navigateToUrl === 'function') {
                        window.navigateToUrl(targetUrl);
                    } else {
                        window.location.href = targetUrl;
                    }
                });
            }

            var iconWrap = document.createElement('div');
            iconWrap.style.cssText = 'width:32px;height:32px;border-radius:50%;background:' + iconBg + ';color:' + iconColor + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:16px;';
            var emojiMap = {
                'fa-triangle-exclamation': '⚠️',
                'fa-circle-xmark': '❌',
                'fa-xmark': '✖',
                'fa-book-open': '📖',
                'fa-print': '🖨️',
                'fa-circle-info': 'ℹ️',
                'fa-circle-check': '✔️'
            };
            iconWrap.innerHTML = emojiMap[iconClass] || '🔔';

            var msgDiv = document.createElement('div');
            msgDiv.style.cssText = 'flex:1;font-size:13px;font-weight:600;color:#0f172a;line-height:1.5;white-space:pre-line;word-break:break-word;';
            msgDiv.innerHTML = msg;
            if (targetUrl) {
                msgDiv.innerHTML += '<div style="font-size:11px;color:' + iconColor + ';margin-top:4px;font-weight:700;"><i class="fa-solid fa-arrow-right"></i> Click to view request</div>';
            }

            var closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'toast-close-btn';
            closeBtn.style.cssText = 'background:none;border:none;padding:0;cursor:pointer;color:#94a3b8;font-size:14px;flex-shrink:0;';
            var closeIcon = document.createElement('i');
            closeIcon.className = 'fa-solid fa-xmark';
            closeBtn.appendChild(closeIcon);
            closeBtn.addEventListener('click', function(e) { 
                e.stopPropagation();
                toast.remove(); 
            });

            toast.appendChild(iconWrap);
            toast.appendChild(msgDiv);
            toast.appendChild(closeBtn);
            container.appendChild(toast);

            setTimeout(function() {
                toast.style.transform = 'translateX(0)';
                toast.style.opacity = '1';
            }, 30);

            setTimeout(function() {
                toast.style.transform = 'translateX(120%)';
                toast.style.opacity = '0';
                setTimeout(function() { if (toast.parentNode) toast.remove(); }, 420);
            }, 7000);
        };
    </script>

    <!-- Dynamic Session Flash Messages -->
    <?php if ($f = flash()): ?>
        <?php
        $is_inactive_flash = (
            stristr($f, 'inactive') !== false ||
            stristr($f, 'expired') !== false
        );
        $is_error_flash = !$is_inactive_flash && (
            str_contains($f, 'Duplicate') || 
            str_contains($f, '⚠️') || 
            str_contains($f, 'Error') || 
            str_contains($f, 'Invalid') ||
            stristr($f, 'warning') !== false ||
            stristr($f, 'suspended') !== false ||
            stristr($f, 'failed') !== false ||
            stristr($f, 'rejected') !== false ||
            stristr($f, 'cannot') !== false ||
            stristr($f, 'required') !== false ||
            stristr($f, 'empty') !== false ||
            stristr($f, 'already exists') !== false
        );
        ?>
        <script class="dynamic-script">
            (function() {
                var msg = <?= json_encode($f) ?>;
                var isError = <?= json_encode($is_error_flash) ?>;
                var isWarning = <?= json_encode($is_inactive_flash) ?>;
                var type = isWarning ? 'warning' : (isError ? 'error' : 'success');
                var fireToast = function() {
                    if (window.showToast) window.showToast(msg, type);
                };
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', fireToast);
                } else {
                    fireToast();
                }
            })();
        </script>
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
        <h1>श्री प्यारे लाल चिरन्जी लाल<br><span class="header-sub-text">कैन्टोनमेन्ट पुस्तकालय मेरठ</span></h1>
        <img src="<?= BASE_URL ?>images/2022111817.png" alt="Branding Right" />
    </div>

    <!-- Main Content Area -->
    <main>
