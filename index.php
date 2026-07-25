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

// 1. Authentication Handlers
if ($action === 'logout') {
    $was_admin = admin();
    if (member()) {
        expire_member_reading_requests($_SESSION['member'], $db);
    }
    
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);

    csrf_token();
    flash('Logged out successfully.');
    go(($was_admin ? 'admin-login' : 'member-login') . '&logged_out=1');
}

$authController = new App\Controllers\AuthController($db);

if ($action === 'admin_login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController->adminLogin();
}

if ($action === 'process_admin_forgot_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController->adminForgotPassword();
}

if ($action === 'member_login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController->memberLogin();
}

// 2. PDF & Document Streaming Handlers
$pdfController = new App\Controllers\PdfController($db);

if ($action === 'download_pdf') {
    $pdfController->downloadPdf();
}

if ($action === 'read_pdf') {
    $pdfController->readPdf();
}

if ($action === 'view_ebook_pdf') {
    $pdfController->viewEbookPdf();
}

if ($action === 'view_pdf_content') {
    $pdfController->viewPdfContent();
}

if ($action === 'read_member_pdf') {
    $pdfController->readMemberPdf();
}

if ($action === 'read_member_pdf_content') {
    $pdfController->readMemberPdfContent();
}

if ($action === 'secure_pdf_viewer') {
    $pdfController->securePdfViewer();
}

// 3. Role-based Navigation Guard
if (!admin() && !member() && !in_array($action, ['home', 'admin_login', 'member_login', 'admin_forgot_password', 'process_admin_forgot_password'])) {
    go('index.php');
}

// 4. Admin Action Handlers
if (admin()) {
    $adminController = new App\Controllers\AdminController($db);

    switch ($action) {
        case 'poll_admin_notifications':
            $adminController->pollNotifications();
            break;
        case 'update_admin_profile':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->updateProfile();
            break;
        case 'generate_db_backup':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->generateDbBackup();
            break;
        case 'generate_full_backup':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->generateFullBackup();
            break;
        case 'download_backup':
            $adminController->downloadBackup();
            break;
        case 'delete_backup':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->deleteBackup();
            break;
        case 'restore_backup':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->restoreBackup();
            break;
        case 'add_category':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->addCategory();
            break;
        case 'delete_category':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->deleteCategory();
            break;
        case 'upload_chunk':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->uploadChunk();
            break;
        case 'assemble_upload':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->assembleUpload();
            break;
        case 'cancel_upload':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->cancelUpload();
            break;
        case 'export_ebooks_csv':
            $adminController->exportEbooksCsv();
            break;
        case 'export_physical_csv':
            $adminController->exportPhysicalCsv();
            break;
        case 'import_books_csv':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->importBooksCsv();
            break;
        case 'add_ebook':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->addEbook();
            break;
        case 'delete_ebook':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->deleteEbook();
            break;
        case 'update_ebook':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->updateEbook();
            break;
        case 'add_member':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->addMember();
            break;
        case 'approve_member':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->approveMember();
            break;
        case 'update_member':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->updateMember();
            break;
        case 'renew_member':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->renewMember();
            break;
        case 'delete_member':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->deleteMember();
            break;
        case 'add_plan':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->addPlan();
            break;
        case 'update_plan':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->updatePlan();
            break;
        case 'delete_plan':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->deletePlan();
            break;
        case 'delete_shift':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->deleteShift();
            break;
        case 'save_shift_times':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->saveShiftTimes();
            break;
        case 'settle_fine':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->settleFine();
            break;
        case 'add_physical':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->addPhysical();
            break;
        case 'update_physical':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->updatePhysical();
            break;
        case 'delete_physical':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->deletePhysical();
            break;
        case 'approve':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->approveRequest();
            break;
        case 'reject':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->rejectRequest();
            break;
        case 'lend':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->lendBook();
            break;
        case 'return_book':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->returnBook();
            break;
        case 'complete_print':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->completePrint();
            break;
        case 'reject_print':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $adminController->rejectPrint();
            break;
    }
}

// 5. Member Action Handlers
if (member()) {
    $memberController = new App\Controllers\MemberController($db);

    switch ($action) {
        case 'update_profile':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $memberController->updateProfile();
            break;
        case 'request_read':
            $memberController->requestRead();
            break;
        case 'request_hold':
            $memberController->requestHold();
            break;
        case 'check_request_updates':
            $memberController->checkRequestUpdates();
            break;
        case 'poll_member_notifications':
            $memberController->pollNotifications();
            break;
        case 'request_print':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $memberController->requestPrint();
            break;
    }
}

// 6. View Rendering
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
