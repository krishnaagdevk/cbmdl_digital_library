<?php
// views/admin.php
if (!defined('BASE_URL')) exit;
$tab = $_GET['tab'] ?? 'dashboard';
$side_req_count = (int)$db->query("SELECT COUNT(*) c FROM reading_requests WHERE status='Pending'")->fetch_assoc()['c'];
$side_prt_count = (int)$db->query("SELECT COUNT(*) c FROM print_requests WHERE status='Pending'")->fetch_assoc()['c'];

$master_data_tabs = ['plans', 'active_plans', 'create_plan', 'shift_timings', 'login_window', 'admin_login_logs', 'member_login_logs'];
$is_master_data_active = in_array($tab, $master_data_tabs);
?>
<script>
if (typeof window.toggleSidebarSubmenu !== 'function') {
    window.toggleSidebarSubmenu = function(btn) {
        const itemGroup = btn.closest('.sidebar-item-group');
        if (!itemGroup) return;
        const submenu = itemGroup.querySelector('.sidebar-submenu');
        if (!submenu) return;
        
        if (submenu.style.display === 'none' || !submenu.style.display) {
            submenu.style.display = 'flex';
            itemGroup.classList.add('open');
        } else {
            submenu.style.display = 'none';
            itemGroup.classList.remove('open');
        }
    };
}
</script>
<div class="admin-wrapper">
    
    <!-- Sidebar Nav with Icons -->
    <div class="sidebar">
        <a href="?action=admin&tab=dashboard" class="<?= $tab === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-chart-pie"></i>Home</a>
        <a href="?action=admin&tab=ebooks" class="<?= $tab === 'ebooks' ? 'active' : '' ?>"><i class="fa-solid fa-file-pdf"></i> Manage E-Books</a>
        <a href="?action=admin&tab=view_ebooks" class="<?= $tab === 'view_ebooks' ? 'active' : '' ?>"><i class="fa-solid fa-book-open"></i> View E-Books</a>
        <a href="?action=admin&tab=requests" class="<?= $tab === 'requests' ? 'active' : '' ?>">
            <i class="fa-solid fa-receipt"></i> Reading Requests
            <span id="sidebarReadingBadge" class="badge badge-orange" style="margin-left:auto; font-size:10px; padding:2px 6px; font-weight:700; <?= $side_req_count > 0 ? '' : 'display:none;' ?>"><?= $side_req_count ?></span>
        </a>
        <a href="?action=admin&tab=prints" class="<?= $tab === 'prints' ? 'active' : '' ?>">
            <i class="fa-solid fa-print"></i> Print Requests
            <span id="sidebarPrintBadge" class="badge badge-blue" style="margin-left:auto; font-size:10px; padding:2px 6px; font-weight:700; <?= $side_prt_count > 0 ? '' : 'display:none;' ?>"><?= $side_prt_count ?></span>
        </a>
        <a href="?action=admin&tab=physical" class="<?= $tab === 'physical' ? 'active' : '' ?>"><i class="fa-solid fa-book"></i> Manage Physical Books</a>
        <a href="?action=admin&tab=view_physical" class="<?= $tab === 'view_physical' ? 'active' : '' ?>"><i class="fa-solid fa-book"></i> View Physical Books</a>
        <a href="?action=admin&tab=lending" class="<?= $tab === 'lending' ? 'active' : '' ?>"><i class="fa-solid fa-hand-holding-hand"></i>Issue Physical Book</a>
        <a href="?action=admin&tab=view_lending" class="<?= $tab === 'view_lending' ? 'active' : '' ?>"><i class="fa-solid fa-clock-rotate-left"></i>View Lending List</a>
        <a href="?action=admin&tab=members" class="<?= $tab === 'members' ? 'active' : '' ?>"><i class="fa-solid fa-user-plus"></i> Add Membership</a>
        <a href="?action=admin&tab=view_members" class="<?= $tab === 'view_members' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i>View Membership</a>
        <a href="?action=admin&tab=membership_history" class="<?= $tab === 'membership_history' ? 'active' : '' ?>"><i class="fa-solid fa-clock-rotate-left"></i> Membership History</a>
        
        <!-- Master Data Collapsible Submenu -->
        <div class="sidebar-item-group <?= $is_master_data_active ? 'open' : '' ?>">
            <button type="button" class="sidebar-parent-btn <?= $is_master_data_active ? 'active-parent' : '' ?>" onclick="toggleSidebarSubmenu(this)">
                <span><i class="fa-solid fa-database"></i> Master Data</span>
                <i class="fa-solid fa-chevron-down toggle-icon"></i>
            </button>
            <div class="sidebar-submenu" style="<?= $is_master_data_active ? 'display:flex;' : 'display:none;' ?>">
                <a href="?action=admin&tab=active_plans" class="<?= ($tab === 'active_plans' || $tab === 'plans') ? 'active' : '' ?>">
                    <i class="fa-solid fa-list-check"></i> Active Membership Plans
                </a>
                <a href="?action=admin&tab=create_plan" class="<?= $tab === 'create_plan' ? 'active' : '' ?>">
                    <i class="fa-solid fa-circle-plus"></i> Create New Membership Plans
                </a>
                <a href="?action=admin&tab=shift_timings" class="<?= $tab === 'shift_timings' ? 'active' : '' ?>">
                    <i class="fa-solid fa-clock"></i> Library Shift Timings
                </a>
                <a href="?action=admin&tab=login_window" class="<?= $tab === 'login_window' ? 'active' : '' ?>">
                    <i class="fa-solid fa-sliders"></i> Login Window Master Control
                </a>
                <a href="?action=admin&tab=admin_login_logs" class="<?= $tab === 'admin_login_logs' ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-shield"></i> Admin Login Logs
                </a>
                <a href="?action=admin&tab=member_login_logs" class="<?= $tab === 'member_login_logs' ? 'active' : '' ?>">
                    <i class="fa-solid fa-users-rectangle"></i> Member Login Logs
                </a>
            </div>
        </div>

        <a href="?action=admin&tab=categories" class="<?= $tab === 'categories' ? 'active' : '' ?>"><i class="fa-solid fa-folder-open"></i>E-Book Categories</a>
        <a href="?action=admin&tab=profile" class="<?= $tab === 'profile' ? 'active' : '' ?>"><i class="fa-solid fa-user-shield"></i>Librarian Profile</a>
        <a href="?action=logout" style="margin-top:15px; color:#ef4444; border-top:1px solid var(--border-color); padding-top:12px; font-weight:600;"><i class="fa-solid fa-right-from-bracket"></i>Logout Panel</a>
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

        <script class="dynamic-script">
        (function() {
            const bellBtn = document.getElementById('bellBtn');
            const bellDropdown = document.getElementById('bellDropdown');
            if (bellBtn && bellDropdown) {
                bellBtn.onclick = function(e) {
                    e.stopPropagation();
                    bellDropdown.style.display = bellDropdown.style.display === 'none' ? 'block' : 'none';
                };
                document.onclick = function(e) {
                    if (!bellDropdown.contains(e.target) && e.target !== bellBtn && !bellBtn.contains(e.target)) {
                        bellDropdown.style.display = 'none';
                    }
                };
            }

            // Real-time admin request poller and dynamic UI updater
            let prevReadingCount = <?= $side_req_count ?>;
            let prevPrintCount = <?= $side_prt_count ?>;

            // Load known IDs from sessionStorage so tab navigation retains memory
            let knownReadingIds = new Set(JSON.parse(sessionStorage.getItem('cbmdl_known_reading_ids') || '[]'));
            let knownPrintIds = new Set(JSON.parse(sessionStorage.getItem('cbmdl_known_print_ids') || '[]'));
            let isInitialAdminPoll = true;

            function showAdminToastNotification(message, type = 'info') {
                if (window.showToast) {
                    window.showToast(message, type);
                }
            }

            function playAdminAlertSound() {
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) return;
                    const ctx = new AudioCtx();
                    const now = ctx.currentTime;
                    
                    const osc1 = ctx.createOscillator();
                    const gain1 = ctx.createGain();
                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(587.33, now); // D5
                    gain1.gain.setValueAtTime(0.2, now);
                    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
                    osc1.connect(gain1);
                    gain1.connect(ctx.destination);
                    osc1.start(now);
                    osc1.stop(now + 0.3);

                    const osc2 = ctx.createOscillator();
                    const gain2 = ctx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(880, now + 0.15); // A5
                    gain2.gain.setValueAtTime(0.25, now + 0.15);
                    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.start(now + 0.15);
                    osc2.stop(now + 0.5);
                } catch(e) {}
            }

            let prevReadingIdsStr = '';
            let prevPrintIdsStr = '';

            function pollAdminNotifications() {
                fetch(window.BASE_URL + 'index.php?action=poll_admin_notifications&_t=' + Date.now(), { cache: 'no-store' })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) return;
                        
                        const reqCount = data.reading_pending_count;
                        const prtCount = data.print_pending_count;
                        const totalCount = reqCount + prtCount;
                        
                        const currentReading = data.recent_reading || [];
                        const currentPrint = data.recent_print || [];

                        const currentReadingIdsStr = currentReading.map(r => r.id).sort().join(',');
                        const currentPrintIdsStr = currentPrint.map(p => p.id).sort().join(',');

                        let hasNewReading = false;
                        let hasNewPrint = false;

                        currentReading.forEach(r => {
                            if (!knownReadingIds.has(r.id)) {
                                knownReadingIds.add(r.id);
                                if (!isInitialAdminPoll || (r.age_secs !== undefined && r.age_secs <= 300)) {
                                    hasNewReading = true;
                                    showAdminToastNotification(`📖 <strong>${r.member}</strong> requested e-reading permission for <strong>"${r.title}"</strong>.`, 'reading');
                                }
                            }
                        });

                        currentPrint.forEach(p => {
                            if (!knownPrintIds.has(p.id)) {
                                knownPrintIds.add(p.id);
                                if (!isInitialAdminPoll || (p.age_secs !== undefined && p.age_secs <= 300)) {
                                    hasNewPrint = true;
                                    showAdminToastNotification(`🖨️ <strong>${p.member}</strong> requested page printing (Pages: ${p.pages}) for <strong>"${p.title}"</strong>.`, 'print');
                                }
                            }
                        });

                        const countChanged = (!isInitialAdminPoll) && (reqCount !== prevReadingCount || prtCount !== prevPrintCount);
                        const pendingSetChanged = (!isInitialAdminPoll) && (currentReadingIdsStr !== prevReadingIdsStr || currentPrintIdsStr !== prevPrintIdsStr);

                        if (hasNewReading || hasNewPrint) {
                            playAdminAlertSound();
                        }

                        // Auto-refresh active tab content in real-time without full page reload
                        if (countChanged || pendingSetChanged || hasNewReading || hasNewPrint) {
                            const activeTab = new URLSearchParams(window.location.search).get('tab') || 'dashboard';
                            if (['requests', 'prints', 'dashboard'].includes(activeTab)) {
                                const isTyping = document.activeElement && ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName);
                                if (!isTyping && typeof window.navigateToUrl === 'function') {
                                    window.navigateToUrl(window.location.href, false);
                                }
                            }
                        }

                        isInitialAdminPoll = false;

                        // Save updated known IDs to sessionStorage
                        try {
                            sessionStorage.setItem('cbmdl_known_reading_ids', JSON.stringify(Array.from(knownReadingIds)));
                            sessionStorage.setItem('cbmdl_known_print_ids', JSON.stringify(Array.from(knownPrintIds)));
                        } catch(e) {}
                        
                        prevReadingCount = reqCount;
                        prevPrintCount = prtCount;
                        prevReadingIdsStr = currentReadingIdsStr;
                        prevPrintIdsStr = currentPrintIdsStr;
                        
                        // Update DOM elements dynamically
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
                    })
                    .catch(err => console.error(err));
            }

            if (window.adminPollerInterval) {
                clearInterval(window.adminPollerInterval);
            }

            // Snappy 2.5 second polling
            window.adminPollerInterval = setInterval(pollAdminNotifications, 2500);
            pollAdminNotifications();
        })();
        </script>

        <?php
        // Include modular tab view
        $allowed_tabs = [
            'dashboard', 'categories', 'ebooks', 'view_ebooks', 
            'physical', 'view_physical', 'requests', 'prints', 
            'members', 'view_members', 'membership_history', 'plans', 
            'active_plans', 'create_plan', 'shift_timings', 'login_window', 
            'admin_login_logs', 'member_login_logs', 'lending', 'view_lending', 'profile'
        ];
        if (in_array($tab, $allowed_tabs)) {
            require __DIR__ . "/admin/{$tab}.php";
        } else {
            require __DIR__ . "/admin/dashboard.php";
        }
        ?>
    </div>
</div>
