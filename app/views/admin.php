<?php
// views/admin.php
if (!defined('BASE_URL')) exit;
$tab = $_GET['tab'] ?? 'dashboard';
$side_req_count = (int)$db->query("SELECT COUNT(*) c FROM reading_requests WHERE status='Pending'")->fetch_assoc()['c'];
$side_prt_count = (int)$db->query("SELECT COUNT(*) c FROM print_requests WHERE status='Pending'")->fetch_assoc()['c'];
?>
<div class="admin-wrapper">
    
    <!-- Sidebar Nav with Icons -->
    <div class="sidebar">
        <a href="?action=admin&tab=dashboard" class="<?= $tab === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-chart-pie"></i> Live Analytics</a>
        <a href="?action=admin&tab=categories" class="<?= $tab === 'categories' ? 'active' : '' ?>"><i class="fa-solid fa-folder-open"></i> E-Book Categories</a>
        <a href="?action=admin&tab=ebooks" class="<?= $tab === 'ebooks' ? 'active' : '' ?>"><i class="fa-solid fa-file-pdf"></i> Manage E-Books</a>
        <a href="?action=admin&tab=view_ebooks" class="<?= $tab === 'view_ebooks' ? 'active' : '' ?>"><i class="fa-solid fa-book-open"></i> View E-Books</a>
        <a href="?action=admin&tab=physical" class="<?= $tab === 'physical' ? 'active' : '' ?>"><i class="fa-solid fa-book"></i> Manage Physical Books</a>
        <a href="?action=admin&tab=view_physical" class="<?= $tab === 'view_physical' ? 'active' : '' ?>"><i class="fa-solid fa-boxes-stacked"></i> View Physical Books</a>
        <a href="?action=admin&tab=requests" class="<?= $tab === 'requests' ? 'active' : '' ?>">
            <i class="fa-solid fa-receipt"></i> Reading Requests
            <span id="sidebarReadingBadge" class="badge badge-orange" style="margin-left:auto; font-size:10px; padding:2px 6px; font-weight:700; <?= $side_req_count > 0 ? '' : 'display:none;' ?>"><?= $side_req_count ?></span>
        </a>
        <a href="?action=admin&tab=prints" class="<?= $tab === 'prints' ? 'active' : '' ?>">
            <i class="fa-solid fa-print"></i> Print Requests
            <span id="sidebarPrintBadge" class="badge badge-blue" style="margin-left:auto; font-size:10px; padding:2px 6px; font-weight:700; <?= $side_prt_count > 0 ? '' : 'display:none;' ?>"><?= $side_prt_count ?></span>
        </a>
        <a href="?action=admin&tab=members" class="<?= $tab === 'members' ? 'active' : '' ?>"><i class="fa-solid fa-user-plus"></i> Add Membership</a>
        <a href="?action=admin&tab=view_members" class="<?= $tab === 'view_members' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> View Membership</a>
        <a href="?action=admin&tab=plans" class="<?= $tab === 'plans' ? 'active' : '' ?>"><i class="fa-solid fa-address-book"></i> Membership Plans</a>
        <a href="?action=admin&tab=lending" class="<?= $tab === 'lending' ? 'active' : '' ?>"><i class="fa-solid fa-hand-holding-hand"></i> Lend Book / Lookup</a>
        <a href="?action=admin&tab=view_lending" class="<?= $tab === 'view_lending' ? 'active' : '' ?>"><i class="fa-solid fa-clock-rotate-left"></i> View Lending List</a>
        <a href="?action=admin&tab=profile" class="<?= $tab === 'profile' ? 'active' : '' ?>"><i class="fa-solid fa-user-shield"></i> Librarian Profile</a>
        <a href="?action=logout" style="margin-top:15px; color:#ef4444; border-top:1px solid var(--border-color); padding-top:12px; font-weight:600;"><i class="fa-solid fa-right-from-bracket"></i> Logout Panel</a>
    </div>

    <!-- Admin Dynamic Sub-view Content -->
    <div class="admin-content">
        
        <style>
        @keyframes bellRing {
            0% { transform: rotate(0); }
            15% { transform: rotate(10deg); }
            30% { transform: rotate(-10deg); }
            45% { transform: rotate(5deg); }
            60% { transform: rotate(-5deg); }
            75% { transform: rotate(2deg); }
            85% { transform: rotate(-2deg); }
            100% { transform: rotate(0); }
        }
        .notification-bell-btn:focus {
            outline: none;
        }
        </style>

        <!-- Persistent Top Header & Notification Bar -->
        <?php
        $bell_reqs = $side_req_count;
        $bell_prints = $side_prt_count;
        $bell_total = $bell_reqs + $bell_prints;
        ?>
        <div style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:12px; padding:12px 24px; margin-bottom:25px; display:flex; align-items:center; justify-content:space-between; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:18px; font-weight:700; color:var(--navy-dark);"><i class="fa-solid fa-user-shield" style="color:var(--primary);"></i> Librarian Panel</span>
                <span class="badge badge-green" style="font-size:11px; padding:4px 8px;"><i class="fa-solid fa-circle-check"></i> Connected</span>
            </div>
            
            <div style="display:flex; align-items:center; gap:20px;">
                <!-- Notification Bell Component -->
                <div style="position:relative; display:inline-block;" id="adminNotificationBellContainer">
                    <button id="bellBtn" class="notification-bell-btn" style="background:none; border:none; padding:8px; cursor:pointer; position:relative; display:flex; align-items:center; justify-content:center; color:var(--text-color); transition:color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-color)'">
                        <i id="bellIcon" class="fa-solid fa-bell" style="font-size:20px; <?= $bell_total > 0 ? 'animation: bellRing 1.5s ease infinite;' : '' ?>"></i>
                        <span id="bellBadge" class="notification-badge" style="position:absolute; top:-2px; right:-2px; background:var(--accent-red); color:white; border-radius:50%; width:18px; height:18px; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; border:2px solid var(--card-bg); <?= $bell_total > 0 ? '' : 'display:none;' ?>"><?= $bell_total ?></span>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div id="bellDropdown" style="display:none; position:absolute; right:0; top:42px; background:var(--card-bg); border:1px solid var(--border-color); border-radius:12px; width:300px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index:999; padding:15px; font-size:13px; text-align:left;">
                        <h4 style="margin:0 0 10px 0; padding-bottom:8px; border-bottom:1px solid var(--border-color); display:flex; align-items:center; justify-content:space-between; font-size:14px;">
                            <span><i class="fa-solid fa-bell"></i> Pending Requests</span>
                            <span class="badge badge-blue" style="font-size:10px;"><span id="bellDropdownCount"><?= $bell_total ?></span> New</span>
                        </h4>
                        <div id="bellDropdownList" style="max-height:220px; overflow-y:auto; display:flex; flex-direction:column; gap:8px;">
                            <?php if ($bell_total === 0): ?>
                                <p style="margin:10px 0; text-align:center; color:var(--text-muted);"><i class="fa-solid fa-circle-check" style="color:var(--accent-green);"></i> All caught up! No pending requests.</p>
                            <?php else: ?>
                                <?php if ($bell_reqs > 0): ?>
                                    <a href="?action=admin&tab=requests" style="display:flex; align-items:center; gap:10px; padding:8px 12px; border-radius:8px; background:var(--bg-slate); text-decoration:none; color:var(--text-color); transition:background 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='var(--bg-slate)'">
                                        <span style="font-size:18px; color:var(--primary);"><i class="fa-solid fa-book-open"></i></span>
                                        <div>
                                            <strong style="display:block; font-size:12px;">e-Reading Requests (<?= $bell_reqs ?>)</strong>
                                            <span style="font-size:11px; color:var(--text-muted);">Awaiting permission grant</span>
                                        </div>
                                    </a>
                                <?php endif; ?>
                                <?php if ($bell_prints > 0): ?>
                                    <a href="?action=admin&tab=prints" style="display:flex; align-items:center; gap:10px; padding:8px 12px; border-radius:8px; background:var(--bg-slate); text-decoration:none; color:var(--text-color); transition:background 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='var(--bg-slate)'">
                                        <span style="font-size:18px; color:var(--accent-orange);"><i class="fa-solid fa-print"></i></span>
                                        <div>
                                            <strong style="display:block; font-size:12px;">Page Print Jobs (<?= $bell_prints ?>)</strong>
                                            <span style="font-size:11px; color:var(--text-muted);">Awaiting printing handout</span>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Label & Logout -->
                <div style="display:flex; align-items:center; gap:12px; border-left:1px solid var(--border-color); padding-left:15px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:32px; height:32px; border-radius:50%; background:var(--primary); color:white; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px;"><i class="fa-solid fa-user-tie"></i></div>
                        <span style="font-size:13px; font-weight:600; color:var(--navy-dark);"><?= e($_SESSION['admin_user'] ?? 'Librarian') ?></span>
                    </div>
                    <a href="?action=logout" class="btn btn-danger" style="padding:6px 12px; font-size:12px; border-radius:6px; text-decoration:none; display:inline-flex; align-items:center; gap:5px; font-weight:600;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bellBtn = document.getElementById('bellBtn');
            const bellDropdown = document.getElementById('bellDropdown');
            if (bellBtn && bellDropdown) {
                bellBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    bellDropdown.style.display = bellDropdown.style.display === 'none' ? 'block' : 'none';
                });
                document.addEventListener('click', function(e) {
                    if (!bellDropdown.contains(e.target) && e.target !== bellBtn && !bellBtn.contains(e.target)) {
                        bellDropdown.style.display = 'none';
                    }
                });
            }

            // Real-time admin request poller and dynamic UI updater
            let prevReadingCount = <?= $side_req_count ?>;
            let prevPrintCount = <?= $side_prt_count ?>;
            let isInitialAdminPoll = true;

            function showAdminToastNotification(message, type = 'info') {
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
                
                const toast = document.createElement('div');
                toast.style.cssText = `
                    background: var(--card-bg, #ffffff);
                    color: var(--text-color, #1e293b);
                    border-radius: 12px;
                    padding: 16px;
                    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
                    border: 1px solid var(--border-color, #e2e8f0);
                    border-left: 5px solid var(--accent-orange, #f59e0b);
                    display: flex;
                    align-items: flex-start;
                    gap: 12px;
                    pointer-events: auto;
                    transform: translateX(120%);
                    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s, margin 0.4s;
                    opacity: 0;
                `;
                
                if (type === 'print') {
                    toast.style.borderLeftColor = 'var(--accent-blue, #3b82f6)';
                }
                
                let iconHtml = '<i class="fa-solid fa-bell" style="color:var(--accent-orange); font-size:18px;"></i>';
                if (type === 'print') {
                    iconHtml = '<i class="fa-solid fa-print" style="color:var(--accent-blue); font-size:18px;"></i>';
                }
                
                toast.innerHTML = `
                    <div style="flex-shrink:0;">${iconHtml}</div>
                    <div style="flex:1; font-size:13px; line-height:1.5;">
                        <div style="font-weight:700; margin-bottom:2px; color:var(--navy-dark);">${type === 'print' ? 'New Print Job Request' : 'New Reading Permission Request'}</div>
                        <div>${message}</div>
                    </div>
                    <button onclick="this.parentElement.remove()" style="background:none; border:none; padding:0; cursor:pointer; color:var(--text-muted); font-size:14px;"><i class="fa-solid fa-xmark"></i></button>
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
                }, 8000);
            }

            function pollAdminNotifications() {
                fetch(window.BASE_URL + 'index.php?action=poll_admin_notifications')
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) return;
                        
                        const reqCount = data.reading_pending_count;
                        const prtCount = data.print_pending_count;
                        const totalCount = reqCount + prtCount;
                        
                        let hasNewRequest = false;
                        
                        // Check for reading requests changes
                        if (reqCount !== prevReadingCount) {
                            hasNewRequest = true;
                            if (reqCount > prevReadingCount && !isInitialAdminPoll) {
                                const newReq = data.recent_reading[0];
                                if (newReq) {
                                    showAdminToastNotification(`<strong>${newReq.member}</strong> requested e-reading permission for <strong>"${newReq.title}"</strong>.`, 'reading');
                                }
                            }
                        }
                        
                        // Check for print requests changes
                        if (prtCount !== prevPrintCount) {
                            hasNewRequest = true;
                            if (prtCount > prevPrintCount && !isInitialAdminPoll) {
                                const newPrt = data.recent_print[0];
                                if (newPrt) {
                                    showAdminToastNotification(`<strong>${newPrt.member}</strong> requested page printing (Pages: ${newPrt.pages}) for <strong>"${newPrt.title}"</strong>.`, 'print');
                                }
                            }
                        }
                        
                        prevReadingCount = reqCount;
                        prevPrintCount = prtCount;
                        isInitialAdminPoll = false;
                        
                        // Update DOM elements
                        const sidebarReadingBadge = document.getElementById('sidebarReadingBadge');
                        const sidebarPrintBadge = document.getElementById('sidebarPrintBadge');
                        const bellBadge = document.getElementById('bellBadge');
                        const bellIcon = document.getElementById('bellIcon');
                        const bellDropdownCount = document.getElementById('bellDropdownCount');
                        const bellDropdownList = document.getElementById('bellDropdownList');
                        
                        if (sidebarReadingBadge) {
                            sidebarReadingBadge.textContent = reqCount;
                            sidebarReadingBadge.style.display = reqCount > 0 ? '' : 'none';
                        }
                        if (sidebarPrintBadge) {
                            sidebarPrintBadge.textContent = prtCount;
                            sidebarPrintBadge.style.display = prtCount > 0 ? '' : 'none';
                        }
                        if (bellBadge) {
                            bellBadge.textContent = totalCount;
                            bellBadge.style.display = totalCount > 0 ? '' : 'none';
                        }
                        if (bellIcon) {
                            if (totalCount > 0) {
                                bellIcon.style.animation = 'bellRing 1.5s ease infinite';
                            } else {
                                bellIcon.style.animation = 'none';
                            }
                        }
                        if (bellDropdownCount) {
                            bellDropdownCount.textContent = totalCount;
                        }
                        
                        if (bellDropdownList) {
                            if (totalCount === 0) {
                                bellDropdownList.innerHTML = `<p style="margin:10px 0; text-align:center; color:var(--text-muted);"><i class="fa-solid fa-circle-check" style="color:var(--accent-green);"></i> All caught up! No pending requests.</p>`;
                            } else {
                                let html = '';
                                if (reqCount > 0) {
                                    html += `
                                        <a href="?action=admin&tab=requests" style="display:flex; align-items:center; gap:10px; padding:8px 12px; border-radius:8px; background:var(--bg-slate); text-decoration:none; color:var(--text-color); transition:background 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='var(--bg-slate)'">
                                            <span style="font-size:18px; color:var(--primary);"><i class="fa-solid fa-book-open"></i></span>
                                            <div>
                                                <strong style="display:block; font-size:12px;">e-Reading Requests (${reqCount})</strong>
                                                <span style="font-size:11px; color:var(--text-muted);">Awaiting permission grant</span>
                                            </div>
                                        </a>
                                    `;
                                }
                                if (prtCount > 0) {
                                    html += `
                                        <a href="?action=admin&tab=prints" style="display:flex; align-items:center; gap:10px; padding:8px 12px; border-radius:8px; background:var(--bg-slate); text-decoration:none; color:var(--text-color); transition:background 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='var(--bg-slate)'">
                                            <span style="font-size:18px; color:var(--accent-orange);"><i class="fa-solid fa-print"></i></span>
                                            <div>
                                                <strong style="display:block; font-size:12px;">Page Print Jobs (${prtCount})</strong>
                                                <span style="font-size:11px; color:var(--text-muted);">Awaiting printing handout</span>
                                            </div>
                                        </a>
                                    `;
                                }
                                bellDropdownList.innerHTML = html;
                            }
                        }
                        
                        // Soft reload if on target admin page
                        if (hasNewRequest) {
                            const activeTab = new URLSearchParams(window.location.search).get('tab');
                            if (activeTab === 'requests' || activeTab === 'prints' || activeTab === 'dashboard' || !activeTab) {
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            }
                        }
                    })
                    .catch(err => console.error(err));
            }

            // Poll every 5 seconds
            setInterval(pollAdminNotifications, 5000);
            pollAdminNotifications();
        });
        </script>

        <?php
        // Include modular tab view
        $allowed_tabs = ['dashboard', 'categories', 'ebooks', 'view_ebooks', 'physical', 'view_physical', 'requests', 'prints', 'members', 'view_members', 'plans', 'lending', 'view_lending', 'profile'];
        if (in_array($tab, $allowed_tabs)) {
            require __DIR__ . "/admin/{$tab}.php";
        } else {
            require __DIR__ . "/admin/dashboard.php";
        }
        ?>
    </div>
</div>
