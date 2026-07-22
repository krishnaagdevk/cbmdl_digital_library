<h2 class="header-title-sub">Digital Portal Gateway</h2>
<div class="login-container">
    <div class="external-menu">
        <a href="https://meerut.cantt.gov.in/" target="_blank">
            <span><i class="fa-solid fa-globe"></i> MCB Official Website</span>
            <i class="fa-solid fa-chevron-right"></i>
        </a>
        <a href="https://echhawani.gov.in/" target="_blank">
            <span><i class="fa-solid fa-circle-info"></i> E-CHHAWANI Portal</span>
            <i class="fa-solid fa-chevron-right"></i>
        </a>
        <div style="background:white; padding:20px; border-radius:12px; border:1px solid var(--border-color); display:flex; flex-direction:column; gap:8px;">
            <p style="margin:0; font-weight:600; color:var(--navy-dark);"><i class="fa-solid fa-envelope" style="color:var(--primary);"></i> Email: cbmeerut@gmail.com</p>
            <p style="margin:0; font-weight:600; color:var(--navy-dark);"><i class="fa-solid fa-phone" style="color:var(--primary);"></i> Help Desk: 0121-2652292</p>
        </div>
    </div>

    <div class="login-section">
        <?php if ($action === 'admin_login'): ?>
            <h3 style="text-align:center; border:none; margin-bottom:20px;"><i class="fa-solid fa-user-shield" style="color:var(--primary);"></i> Librarian Authorization</h3>
            <form method="post" action="?action=admin_login">
                <?= csrf_input() ?>
                <label for="admin_uid"><i class="fa-solid fa-user"></i> User ID</label>
                <input id="admin_uid" name="username" required placeholder="Librarian Username">
                
                <label for="admin_pwd"><i class="fa-solid fa-key"></i> Password</label>
                <input id="admin_pwd" type="password" name="password" required placeholder="Enter password">
                
                <button style="width:100%; padding:14px; margin-top:10px;"><i class="fa-solid fa-shield-halved"></i> Verify & Authorize</button>
            </form>
        <?php else: ?>
            <h3 style="text-align:center; border:none; margin-bottom:20px;"><i class="fa-solid fa-user-graduate" style="color:var(--primary);"></i> Member Login</h3>
            <form method="post" action="?action=member_login">
                <?= csrf_input() ?>
                <label for="member_mob"><i class="fa-solid fa-phone"></i> Mobile Number</label>
                <input id="member_mob" name="mobile" required placeholder="Registered Mobile No">
                
                <label for="member_pwd"><i class="fa-solid fa-key"></i> Password</label>
                <input id="member_pwd" type="password" name="password" required placeholder="Enter password">
                
                <button style="width:100%; padding:14px; margin-top:10px;"><i class="fa-solid fa-right-to-bracket"></i> Login Account</button>
            </form>

        <?php endif; ?>
    </div>
</div>
