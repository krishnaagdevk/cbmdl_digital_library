<?php
// views/user.php
if (!defined('BASE_URL')) exit;

$mid = (int)$_SESSION['member'];

$stmt = $db->prepare("SELECT * FROM members WHERE id = ?");
$stmt->bind_param("i", $mid);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$me || $me['is_active'] == 0 || $me['end_date'] < date('Y-m-d')) {
    unset($_SESSION['member']);
    flash('Your membership has expired or has been suspended. Please contact the librarian.');
    go('member-login');
}

$shift = $me['shift'] ?? 'Both';
if (!is_member_within_shift_time($shift, $db)) {
    unset($_SESSION['member']);
    $time_win = get_shift_time_window($shift, $db);
    $fmt_start = date('h:i A', strtotime($time_win['start_time']));
    $fmt_end = date('h:i A', strtotime($time_win['end_time']));
    flash("🔒 Shift Access Terminated: Your assigned shift ('" . $shift . "' : " . $fmt_start . " - " . $fmt_end . ") has ended. You have been logged out.");
    go('member-login');
}

$tab = $_GET['tab'] ?? 'dashboard';
$search = trim($_GET['search'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);
$sort = $_GET['sort'] ?? 'title_asc';

$orderBy = 'e.title ASC';
if ($sort === 'title_desc') {
    $orderBy = 'e.title DESC';
} elseif ($sort === 'category_asc') {
    $orderBy = 'c.name ASC';
} elseif ($sort === 'category_desc') {
    $orderBy = 'c.name DESC';
} elseif ($sort === 'id_desc') {
    $orderBy = 'e.id DESC';
} elseif ($sort === 'id_asc') {
    $orderBy = 'e.id ASC';
}

$whereStr = 'WHERE 1';
$params = [];
$types = '';
if ($cat) {
    $whereStr .= " AND e.category_id = ?";
    $params[] = $cat;
    $types .= 'i';
}
if ($search !== '') {
    $whereStr .= " AND e.title LIKE ?";
    $params[] = '%' . $search . '%';
    $types .= 's';
}

// Expiring soon check
$expiringSoon = false;
$daysLeft = 0;
$diff = strtotime($me['end_date']) - time();
if ($diff > 0 && $diff <= 7 * 24 * 60 * 60) {
    $expiringSoon = true;
    $daysLeft = (int)ceil($diff / (24 * 60 * 60));
}
// Fetch active or pending e-book reading requests for member sidebar
$active_reading_requests = [];
$actStmt = $db->prepare("SELECT r.id, r.status, r.duration_minutes, r.started_reading_at, r.expires_at, e.id as ebook_id, e.title FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.member_id = ? AND (r.status = 'Pending' OR (r.status = 'Approved' AND (r.started_reading_at IS NULL OR r.expires_at > NOW()))) ORDER BY r.status DESC, r.requested_at DESC");
$actStmt->bind_param("i", $mid);
$actStmt->execute();
$actRes = $actStmt->get_result();
while ($row = $actRes->fetch_assoc()) {
    $active_reading_requests[] = $row;
}
$actStmt->close();
?>
<div class="admin-wrapper">
    
    <!-- Member Navigation Sidebar -->
    <div class="sidebar">
        <a href="?action=user&tab=dashboard" class="<?= $tab === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-house-chimney"></i> Home</a>
        <a href="?action=user&tab=books" class="<?= $tab === 'books' ? 'active' : '' ?>"><i class="fa-solid fa-book-open"></i> Explore e-Library</a>
        <a href="?action=user&tab=reading_history" class="<?= $tab === 'reading_history' ? 'active' : '' ?>"><i class="fa-solid fa-clock-rotate-left"></i> e-book Reading History</a>
        <a href="?action=user&tab=print_requests" class="<?= $tab === 'print_requests' ? 'active' : '' ?>"><i class="fa-solid fa-print"></i> e-book Print History</a>
        <a href="?action=user&tab=physical_books" class="<?= $tab === 'physical_books' ? 'active' : '' ?>"><i class="fa-solid fa-book"></i> Explore Physical Books</a>
        <a href="?action=user&tab=lending" class="<?= $tab === 'lending' ? 'active' : '' ?>"><i class="fa-solid fa-clipboard-list"></i> Physical Book Lending History</a>
        <a href="?action=user&tab=membership_history" class="<?= $tab === 'membership_history' ? 'active' : '' ?>"><i class="fa-solid fa-id-card-clip"></i> My Membership History</a>
        <a href="?action=user&tab=profile" class="<?= $tab === 'profile' ? 'active' : '' ?>"><i class="fa-solid fa-user-gear"></i> My Profile</a>

        <?php if (!empty($active_reading_requests)): ?>
            <div style="margin-top:15px; padding-top:15px; border-top:1px solid var(--border-color);">
                <div style="font-size:11px; font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                    <i class="fa-solid fa-bolt" style="color:var(--accent-orange);"></i> Active Reading Requests
                </div>
                <?php foreach ($active_reading_requests as $arr): ?>
                    <?php if ($arr['status'] === 'Approved'): ?>
                        <?php 
                        $isStarted = !empty($arr['started_reading_at']);
                        $durMins = !empty($arr['duration_minutes']) ? (int)$arr['duration_minutes'] : 15;
                        $remMins = $durMins;
                        if ($isStarted && !empty($arr['expires_at'])) {
                            $remSecs = strtotime($arr['expires_at']) - time();
                            $remMins = max(1, (int)ceil($remSecs / 60));
                        }
                        ?>
                        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:10px; margin-bottom:8px; display:flex; flex-direction:column; gap:6px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:4px;">
                                <span style="font-size:12px; font-weight:700; color:#166534; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:130px;" title="<?= e($arr['title']) ?>"><i class="fa-solid fa-book-open"></i> <?= e($arr['title']) ?></span>
                                <span class="badge badge-green" style="font-size:10px; padding:2px 6px;"><i class="fa-solid fa-clock"></i> <?= $isStarted ? $remMins . 'm' : $durMins . 'm Granted' ?></span>
                            </div>
                            <button class="btn" style="width:100%; font-size:11px; padding:5px 8px; background:var(--accent-green);" onclick="openPdfModal(<?= $arr['id'] ?>, <?= $isStarted && !empty($arr['expires_at']) ? strtotime($arr['expires_at']) : 0 ?>, '<?= addslashes(e($arr['title'])) ?>')"><i class="fa-solid fa-book-open"></i> <?= $isStarted ? 'Continue Reading' : 'Read Now (Start Timer)' ?></button>
                        </div>
                    <?php else: ?>
                        <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:10px; margin-bottom:8px; display:flex; flex-direction:column; gap:4px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:4px;">
                                <span style="font-size:12px; font-weight:700; color:#92400e; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:130px;" title="<?= e($arr['title']) ?>"><i class="fa-solid fa-book"></i> <?= e($arr['title']) ?></span>
                                <span class="badge badge-orange" style="font-size:10px; padding:2px 6px;"><i class="fa-solid fa-clock"></i> Pending</span>
                            </div>
                            <span style="font-size:10px; color:#b45309;"><i class="fa-solid fa-hourglass-half"></i> Awaiting Admin Approval</span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Dynamic Member View Container -->
    <div class="admin-content">
        <!-- Member Greeting Header Banner -->
        <div class="user-greeting-banner" style="background: linear-gradient(135deg, var(--navy-dark), var(--navy-light)); color: white; padding: 18px 24px; border-radius: 14px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px rgba(15, 23, 42, 0.12); flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; background: rgba(37, 99, 235, 0.2); border: 2px solid var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #60a5fa; flex-shrink: 0;">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                    <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        Welcome, <?= e($me['name']) ?>
                      
                    </h2>
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: #94a3b8; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <span><i class="fa-solid fa-id-card" style="color: var(--primary);"></i> Membership ID: <strong style="color: #f1f5f9; font-weight: 600;"><?= e($me['membership_id']) ?></strong></span>
                        <span style="color: rgba(255,255,255,0.2);">•</span>
                        <span><i class="fa-solid fa-calendar-check" style="color: #34d399;"></i> Valid Until: <strong style="color: #f1f5f9; font-weight: 600;"><?= date('d-m-Y', strtotime($me['end_date'])) ?></strong></span>
                    </p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="?action=user&tab=profile" class="btn" style="background: rgba(255, 255, 255, 0.1); color: #ffffff; padding: 8px 14px; font-size: 13px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-weight: 500;">
                    <i class="fa-solid fa-user-gear"></i> Profile
                </a>
                <a href="?action=logout" class="btn" style="background: linear-gradient(135deg, #ef4444, #b91c1c); color: #ffffff; padding: 8px 14px; font-size: 13px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>
        <?php if ($expiringSoon): ?>
            <div class="notice" style="background:#fffbeb; color:var(--accent-orange); border-left:5px solid var(--accent-orange); box-shadow:0 4px 12px rgba(245, 158, 11, 0.08); margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>⚠️ Your e-Library pass is expiring in <strong><?= $daysLeft ?> day(s)</strong>. Kindly contact Librarian if you want to renew your membership</span>
                </div>
            </div>
        <?php endif; ?>

        <?php
        $allowed_tabs = ['dashboard', 'books', 'physical_books', 'lending', 'reading_history', 'print_requests', 'membership_history', 'profile'];
        if (in_array($tab, $allowed_tabs)) {
            require __DIR__ . "/user/{$tab}.php";
        } else {
            require __DIR__ . "/user/dashboard.php";
        }
        ?>
    </div>
</div>

<script class="dynamic-script">
(function() {
    let isInitialMemberPoll = true;

    function showToastNotification(message, type = 'info') {
        if (window.showToast) {
            window.showToast(message, type);
        }
    }

    function showNotificationModal(heading, message, type = 'info', actionUrl = null, actionText = null, onDismiss = null) {
        let overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 20000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;

        let badgeBg = 'var(--primary, #3b82f6)';
        let emojiIcon = '🔔';
        let headerTitle = heading || 'Notification';
        
        if (type === 'success') {
            badgeBg = 'var(--accent-green, #10b981)';
            emojiIcon = '🎉';
        } else if (type === 'danger') {
            badgeBg = 'var(--accent-red, #ef4444)';
            emojiIcon = '❌';
        } else if (type === 'warning') {
            badgeBg = 'var(--accent-orange, #f59e0b)';
            emojiIcon = '⚠️';
        }

        const modal = document.createElement('div');
        modal.style.cssText = `
            background: var(--card-bg, #ffffff);
            color: var(--text-color, #1e293b);
            border-radius: 16px;
            max-width: 440px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid var(--border-color, #e2e8f0);
        `;

        modal.innerHTML = `
            <div style="background: ${badgeBg}; padding: 24px 20px; color: #ffffff; text-align: center; position: relative;">
                <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.25); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto; font-size: 30px; line-height: 1;">
                    ${emojiIcon}
                </div>
                <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #ffffff;">${headerTitle}</h3>
            </div>
            <div style="padding: 24px; text-align: center;">
                <div style="font-size: 15px; color: var(--text-color, #334155); line-height: 1.6; margin-bottom: 24px;">${message}</div>
                <div style="display: flex; justify-content: center;">
                    <button id="closeNotificationModalBtn" style="padding: 12px 40px; background: ${badgeBg}; color: #ffffff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 15px; transition: opacity 0.2s; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">
                        OK
                    </button>
                </div>
            </div>
        `;

        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
            modal.style.transform = 'scale(1)';
        });

        const closeModal = () => {
            overlay.style.opacity = '0';
            modal.style.transform = 'scale(0.9)';
            setTimeout(() => {
                overlay.remove();
                if (typeof onDismiss === 'function') {
                    onDismiss();
                }
            }, 300);
        };

        const closeBtn = modal.querySelector('#closeNotificationModalBtn');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }
    }

    function pollMemberNotifications() {
        fetch(window.BASE_URL + 'index.php?action=poll_member_notifications&_t=' + Date.now(), { cache: 'no-store' })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;
                
                // Get previously stored state
                let prevReadingState = JSON.parse(sessionStorage.getItem('member_reading_state') || '{}');
                let prevPrintState = JSON.parse(sessionStorage.getItem('member_print_state') || '{}');
                
                let newReadingState = {};
                let newPrintState = {};
                
                let mustReloadTab = false;
                
                // Process Reading Requests
                data.reading.forEach(req => {
                    newReadingState[req.id] = req.status;
                    
                    const handleStatusNotification = () => {
                        mustReloadTab = true;
                        const onDismissReload = () => {
                            if (typeof window.navigateToUrl === 'function') {
                                window.navigateToUrl(window.location.href, false);
                            } else {
                                window.location.reload();
                            }
                        };

                        if (req.status === 'Approved') {
                            showToastNotification(`Your reading request for <strong>"${req.title}"</strong> has been approved! You can now read the E-Book.`, 'success');
                            showNotificationModal(
                                'Reading Request Approved!',
                                `Your reading request for <strong>"${req.title}"</strong> has been approved! You can now access and read this E-Book.`,
                                'success',
                                null,
                                null,
                                onDismissReload
                            );
                        } else if (req.status === 'Rejected') {
                            showToastNotification(`Your reading request for <strong>"${req.title}"</strong> was rejected by the librarian.`, 'danger');
                            showNotificationModal(
                                'Reading Request Rejected',
                                `Your reading request for <strong>"${req.title}"</strong> was rejected by the librarian.`,
                                'danger',
                                null,
                                null,
                                onDismissReload
                            );
                        }
                    };

                    // If we already had this request in state, check if status changed
                    if (prevReadingState[req.id] !== undefined) {
                        if (prevReadingState[req.id] !== req.status && req.status !== 'Pending') {
                            handleStatusNotification();
                        }
                    } else if (!isInitialMemberPoll && req.status !== 'Pending') {
                        handleStatusNotification();
                    }
                });
                
                // Process Print Requests
                data.print.forEach(req => {
                    newPrintState[req.id] = req.status;
                    
                    const handlePrintNotification = () => {
                        mustReloadTab = true;
                        const onDismissReload = () => {
                            if (typeof window.navigateToUrl === 'function') {
                                window.navigateToUrl(window.location.href, false);
                            } else {
                                window.location.reload();
                            }
                        };

                        if (req.status === 'Completed') {
                            showToastNotification(`Your print job request for <strong>"${req.title}"</strong> (Pages: ${req.pages}) has been completed! Please collect it from the front desk.`, 'success');
                            showNotificationModal(
                                'Print Job Completed',
                                `Your print job request for <strong>"${req.title}"</strong> (Pages: ${req.pages}) has been completed! Please collect it from the front desk.`,
                                'success',
                                null,
                                null,
                                onDismissReload
                            );
                        } else if (req.status === 'Rejected') {
                            showToastNotification(`Your print job request for <strong>"${req.title}"</strong> (Pages: ${req.pages}) was rejected by the librarian.`, 'danger');
                            showNotificationModal(
                                'Print Request Rejected',
                                `Your print job request for <strong>"${req.title}"</strong> (Pages: ${req.pages}) was rejected by the librarian.`,
                                'danger',
                                null,
                                null,
                                onDismissReload
                            );
                        }
                    };

                    if (prevPrintState[req.id] !== undefined) {
                        if (prevPrintState[req.id] !== req.status && req.status !== 'Pending') {
                            handlePrintNotification();
                        }
                    } else if (!isInitialMemberPoll && req.status !== 'Pending') {
                        handlePrintNotification();
                    }
                });
                
                // Save current states
                sessionStorage.setItem('member_reading_state', JSON.stringify(newReadingState));
                sessionStorage.setItem('member_print_state', JSON.stringify(newPrintState));
                
                isInitialMemberPoll = false;
            })
            .catch(err => console.error(err));
    }

    if (window.memberPollerInterval) {
        clearInterval(window.memberPollerInterval);
    }

    // Poll every 4 seconds
    window.memberPollerInterval = setInterval(pollMemberNotifications, 4000);
    pollMemberNotifications();
})();
</script>
