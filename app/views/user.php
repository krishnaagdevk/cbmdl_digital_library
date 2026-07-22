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

$tab = $_GET['tab'] ?? 'books';
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
?>
<div class="admin-wrapper">
    
    <!-- Member Navigation Sidebar -->
    <div class="sidebar">
        <a href="?action=user&tab=books" class="<?= $tab === 'books' ? 'active' : '' ?>"><i class="fa-solid fa-book-open"></i> Explore e-Library</a>
        <a href="?action=user&tab=physical_books" class="<?= $tab === 'physical_books' ? 'active' : '' ?>"><i class="fa-solid fa-book"></i> Explore Physical Books</a>
        <a href="?action=user&tab=lending" class="<?= $tab === 'lending' ? 'active' : '' ?>"><i class="fa-solid fa-clipboard-list"></i> My Lending History</a>
        <a href="?action=user&tab=reading_history" class="<?= $tab === 'reading_history' ? 'active' : '' ?>"><i class="fa-solid fa-clock-rotate-left"></i> My Reading History</a>
        <a href="?action=user&tab=id_card" class="<?= $tab === 'id_card' ? 'active' : '' ?>"><i class="fa-solid fa-address-card"></i> Member ID Card</a>
        <a href="?action=user&tab=profile" class="<?= $tab === 'profile' ? 'active' : '' ?>"><i class="fa-solid fa-user-gear"></i> Update My Profile</a>
    </div>

    <!-- Dynamic Member View Container -->
    <div class="admin-content">
        <?php if ($expiringSoon): ?>
            <div class="notice" style="background:#fffbeb; color:var(--accent-orange); border-left:5px solid var(--accent-orange); box-shadow:0 4px 12px rgba(245, 158, 11, 0.08); margin-bottom:20px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>⚠️ Your membership is expiring in <?= $daysLeft ?> day(s). Please contact the librarian to renew your subscription.</span>
            </div>
        <?php endif; ?>

        <?php
        $allowed_tabs = ['books', 'physical_books', 'lending', 'reading_history', 'id_card', 'profile'];
        if (in_array($tab, $allowed_tabs)) {
            require __DIR__ . "/user/{$tab}.php";
        } else {
            require __DIR__ . "/user/books.php";
        }
        ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let isInitialMemberPoll = true;

    function showToastNotification(message, type = 'info') {
        // Create container if not exists
        let container = document.getElementById('toastNotificationContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastNotificationContainer';
            container.style.cssText = `
                position: fixed;
                top: 24px;
                right: 24px;
                display: flex;
                flex-direction: column;
                gap: 12px;
                z-index: 10000;
                pointer-events: none;
                max-width: 380px;
                width: calc(100% - 48px);
            `;
            document.body.appendChild(container);
        }
        
        // Create toast
        const toast = document.createElement('div');
        toast.style.cssText = `
            background: var(--card-bg, #ffffff);
            color: var(--text-color, #1e293b);
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color, #e2e8f0);
            border-left: 5px solid var(--primary, #3b82f6);
            display: flex;
            align-items: flex-start;
            gap: 12px;
            pointer-events: auto;
            transform: translateX(120%);
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s, margin 0.4s;
            opacity: 0;
        `;
        
        if (type === 'success') {
            toast.style.borderLeftColor = 'var(--accent-green, #10b981)';
        } else if (type === 'danger') {
            toast.style.borderLeftColor = 'var(--accent-red, #ef4444)';
        } else if (type === 'warning') {
            toast.style.borderLeftColor = 'var(--accent-orange, #f59e0b)';
        }
        
        // Icon
        let iconHtml = '<i class="fa-solid fa-circle-info" style="color:var(--primary); font-size:18px;"></i>';
        if (type === 'success') {
            iconHtml = '<i class="fa-solid fa-circle-check" style="color:var(--accent-green); font-size:18px;"></i>';
        } else if (type === 'danger') {
            iconHtml = '<i class="fa-solid fa-circle-xmark" style="color:var(--accent-red); font-size:18px;"></i>';
        } else if (type === 'warning') {
            iconHtml = '<i class="fa-solid fa-bell" style="color:var(--accent-orange); font-size:18px;"></i>';
        }
        
        toast.innerHTML = `
            <div style="flex-shrink:0;">${iconHtml}</div>
            <div style="flex:1; font-size:13px; line-height:1.5;">
                <div style="font-weight:700; margin-bottom:2px; color:var(--navy-dark);">${type === 'success' ? 'Request Approved!' : type === 'danger' ? 'Request Rejected' : 'Update Notification'}</div>
                <div>${message}</div>
            </div>
            <button onclick="this.parentElement.remove()" style="background:none; border:none; padding:0; cursor:pointer; color:var(--text-muted); font-size:14px;"><i class="fa-solid fa-xmark"></i></button>
        `;
        
        container.appendChild(toast);
        
        // Trigger entry animation
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        });
        
        // Auto remove
        setTimeout(() => {
            toast.style.transform = 'translateX(120%)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, 6000);
    }

    function pollMemberNotifications() {
        fetch(window.BASE_URL + 'index.php?action=poll_member_notifications')
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
                    
                    // If we already had this request in state, check if status changed
                    if (prevReadingState[req.id] !== undefined) {
                        if (prevReadingState[req.id] !== req.status && req.status !== 'Pending') {
                            mustReloadTab = true;
                            if (req.status === 'Approved') {
                                showToastNotification(`Your reading request for <strong>"${req.title}"</strong> has been approved! You can now read the E-Book.`, 'success');
                            } else if (req.status === 'Rejected') {
                                showToastNotification(`Your reading request for <strong>"${req.title}"</strong> was rejected by the librarian.`, 'danger');
                            }
                        }
                    } else if (!isInitialMemberPoll && req.status !== 'Pending') {
                        mustReloadTab = true;
                        if (req.status === 'Approved') {
                            showToastNotification(`Your reading request for <strong>"${req.title}"</strong> has been approved! You can now read the E-Book.`, 'success');
                        }
                    }
                });
                
                // Process Print Requests
                data.print.forEach(req => {
                    newPrintState[req.id] = req.status;
                    
                    if (prevPrintState[req.id] !== undefined) {
                        if (prevPrintState[req.id] !== req.status && req.status === 'Completed') {
                            mustReloadTab = true;
                            showToastNotification(`Your print job request for <strong>"${req.title}"</strong> (Pages: ${req.pages}) has been completed! Please collect it from the front desk.`, 'success');
                        }
                    } else if (!isInitialMemberPoll && req.status === 'Completed') {
                        mustReloadTab = true;
                        showToastNotification(`Your print job request for <strong>"${req.title}"</strong> (Pages: ${req.pages}) has been completed! Please collect it from the front desk.`, 'success');
                    }
                });
                
                // Save current states
                sessionStorage.setItem('member_reading_state', JSON.stringify(newReadingState));
                sessionStorage.setItem('member_print_state', JSON.stringify(newPrintState));
                
                isInitialMemberPoll = false;
                
                // If tab needs refreshing, trigger reload
                if (mustReloadTab) {
                    const activeTab = new URLSearchParams(window.location.search).get('tab');
                    if (activeTab === 'books' || activeTab === 'reading_history' || !activeTab) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                }
            })
            .catch(err => console.error(err));
    }

    // Poll every 5 seconds
    setInterval(pollMemberNotifications, 5000);
    pollMemberNotifications();
});
</script>
