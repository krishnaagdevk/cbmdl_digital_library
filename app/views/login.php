<h2 class="header-title-sub" style="margin-bottom:12px;">Welcome To Cantonment Digital Library</h2>

<style>
@media (max-width: 992px) {
    .login-container {
        grid-template-columns: 1fr !important;
    }
    .bottom-tiles-row {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}
@media (max-width: 576px) {
    .bottom-tiles-row {
        grid-template-columns: 1fr !important;
    }
}
</style>

<div class="login-container" style="max-width:1100px; margin:10px auto; padding:0 15px; display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:start;">
    
    <!-- LEFT COLUMN: Heritage Showcase Gallery -->
    <div class="left-showcase-column" style="display:flex; flex-direction:column; gap:12px;">
        <!-- Showcase Gallery Card -->
        <div class="library-gallery-card" style="background:white; border-radius:14px; border:1px solid var(--border-color); overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.04);">
            <div style="position:relative; width:100%; aspect-ratio:16/9.5; max-height:285px; background:#1e293b; overflow:hidden;">
                <!-- Slide 1: Historical Plaque -->
                <div class="gallery-slide active-slide" id="slide-0" style="position:absolute; inset:0; opacity:1; transition:opacity 0.5s ease;">
                    <img src="<?= BASE_URL ?>images/library_plaque.jpg" alt="Inauguration Plaque" style="width:100%; height:100%; object-fit:cover; display:block;">
                    <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(transparent, rgba(15,23,42,0.92)); color:white; padding:10px 14px;">
                        <span style="font-size:10px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; color:var(--accent-orange); display:block;">Historical Foundation (1953)</span>
                        <strong style="font-size:12.5px; display:block; line-height:1.25;">Inauguration Plaque by Hon'ble Shri Satish Chandra</strong>
                    </div>
                </div>

                <!-- Slide 2: Heritage Building -->
                <div class="gallery-slide" id="slide-1" style="position:absolute; inset:0; opacity:0; transition:opacity 0.5s ease;">
                    <img src="<?= BASE_URL ?>images/library_building.png" alt="Library Entrance" style="width:100%; height:100%; object-fit:cover; display:block;">
                    <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(transparent, rgba(15,23,42,0.92)); color:white; padding:10px 14px;">
                        <span style="font-size:10px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; color:#60a5fa; display:block;">Library Campus</span>
                        <strong style="font-size:12.5px; display:block; line-height:1.25;">Cantonment Library Heritage Building</strong>
                    </div>
                </div>

                <!-- Slide 3: Digital Reading Hall -->
                <div class="gallery-slide" id="slide-2" style="position:absolute; inset:0; opacity:0; transition:opacity 0.5s ease;">
                    <img src="<?= BASE_URL ?>images/library_hall.png" alt="Digital Reading Hall" style="width:100%; height:100%; object-fit:cover; display:block;">
                    <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(transparent, rgba(15,23,42,0.92)); color:white; padding:10px 14px;">
                        <span style="font-size:10px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; color:#34d399; display:block;">Modern Facilities</span>
                        <strong style="font-size:12.5px; display:block; line-height:1.25;">E-Resource Center & Reading Hall</strong>
                    </div>
                </div>
            </div>

            <!-- Thumbnail Selector Buttons -->
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px; padding:6px; background:#f8fafc; border-top:1px solid var(--border-color);">
                <button type="button" class="thumb-btn active-thumb" onclick="switchGallerySlide(0)" style="border:2px solid var(--primary); padding:0; border-radius:6px; overflow:hidden; cursor:pointer; height:48px; background:none; transition:all 0.2s;" title="Historical Foundation Plaque">
                    <img src="<?= BASE_URL ?>images/library_plaque.jpg" style="width:100%; height:100%; object-fit:cover; display:block;">
                </button>
                <button type="button" class="thumb-btn" onclick="switchGallerySlide(1)" style="border:2px solid transparent; padding:0; border-radius:6px; overflow:hidden; cursor:pointer; height:48px; background:none; opacity:0.65; transition:all 0.2s;" title="Library Heritage Building">
                    <img src="<?= BASE_URL ?>images/library_building.png" style="width:100%; height:100%; object-fit:cover; display:block;">
                </button>
                <button type="button" class="thumb-btn" onclick="switchGallerySlide(2)" style="border:2px solid transparent; padding:0; border-radius:6px; overflow:hidden; cursor:pointer; height:48px; background:none; opacity:0.65; transition:all 0.2s;" title="Digital Reading Hall">
                    <img src="<?= BASE_URL ?>images/library_hall.png" style="width:100%; height:100%; object-fit:cover; display:block;">
                </button>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN: Login Form -->
    <div class="right-login-column" style="display:flex; flex-direction:column; gap:12px;">
        <div class="login-section" style="background:#fff; padding:26px 28px; border-radius:14px; box-shadow:0 8px 25px rgba(15,23,42,0.05); border:1px solid var(--border-color);">
            <?php if ($action === 'admin_login'): ?>
                <h3 style="text-align:center; border:none; margin-bottom:20px; font-size:18px;"><i class="fa-solid fa-user-shield" style="color:var(--primary);"></i> Librarian Login</h3>
                <form method="post" action="?action=admin_login" autocomplete="off">
                    <?= csrf_input() ?>
                    <label for="admin_uid" style="font-size:12.5px; font-weight:600; margin-bottom:5px; display:block;"><i class="fa-solid fa-user"></i> User ID</label>
                    <input id="admin_uid" name="username" required placeholder="Librarian Username" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" style="padding:11px 14px; margin-bottom:12px; font-size:13.5px;">
                    
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                        <label for="admin_pwd" style="font-size:12.5px; font-weight:600; margin:0;"><i class="fa-solid fa-key"></i> Password</label>
                        <a href="admin-forgot-password" style="font-size:12px; color:var(--primary); font-weight:600; text-decoration:none;"><i class="fa-solid fa-unlock-keyhole"></i> Forgot Password?</a>
                    </div>
                    <input id="admin_pwd" type="password" name="password" required placeholder="Enter password" maxlength="15" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" style="padding:11px 14px; margin-bottom:16px; font-size:13.5px;">
                    
                    <button style="width:100%; padding:13px; margin-top:4px; font-weight:600; font-size:14px;"><i class="fa-solid fa-shield-halved"></i> Verify & Authorize</button>
                </form>
            <?php else: ?>
                <h3 style="text-align:center; border:none; margin-bottom:20px; font-size:18px;"><i class="fa-solid fa-user-graduate" style="color:var(--primary);"></i> Member Login</h3>
                <form method="post" action="?action=member_login" autocomplete="off">
                    <?= csrf_input() ?>
                    <label for="member_mob" style="font-size:12.5px; font-weight:600; margin-bottom:5px; display:block;"><i class="fa-solid fa-phone"></i> Mobile Number</label>
                    <input id="member_mob" name="mobile" required placeholder="Registered Mobile No" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" style="padding:11px 14px; margin-bottom:12px; font-size:13.5px;">
                    
                    <label for="member_pwd" style="font-size:12.5px; font-weight:600; margin-bottom:5px; display:block;"><i class="fa-solid fa-key"></i> Password</label>
                    <input id="member_pwd" type="password" name="password" required placeholder="Enter password" maxlength="15" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" style="padding:11px 14px; margin-bottom:16px; font-size:13.5px;">
                    
                    <button style="width:100%; padding:13px; margin-top:4px; font-weight:600; font-size:14px;"><i class="fa-solid fa-right-to-bracket"></i> Login Account</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- FULL WIDTH SINGLE ROW: All 4 Tiles Aligned in One Line Across Spanning Both Columns -->
    <div class="bottom-tiles-row" style="grid-column: 1 / -1; display:grid; grid-template-columns: repeat(4, 1fr); gap:10px; width:100%;">
        
        <!-- Tile 1: Official Email -->
        <div style="background:#fff; border:1px solid var(--border-color); border-radius:12px; padding:10px 12px; display:flex; align-items:center; gap:10px; text-decoration:none; color:inherit; box-shadow:0 4px 12px rgba(0,0,0,0.02); transition:all 0.2s ease;" onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='translateY(-1px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='none';">
            <div style="width:36px; height:36px; border-radius:10px; background:#eff6ff; color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                ✉️
            </div>
            <div style="overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">
                <span style="display:block; font-size:9.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">Official Email</span>
                <strong style="display:block; font-size:11.5px; color:var(--navy-dark); text-overflow:ellipsis; overflow:hidden;">cbmeerut1@gmail.com</strong>
            </div>
            </div>

        <!-- Tile 2: Helpline Desk (with 📞 phone emoji in green box) -->
        <div style="background:#fff; border:1px solid var(--border-color); border-radius:12px; padding:10px 12px; display:flex; align-items:center; gap:10px; text-decoration:none; color:inherit; box-shadow:0 4px 12px rgba(0,0,0,0.02); transition:all 0.2s ease;" onmouseover="this.style.borderColor='var(--accent-green)'; this.style.transform='translateY(-1px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='none';">
            <div style="width:36px; height:36px; border-radius:10px; background:#ecfdf5; color:var(--accent-green); display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                📞
            </div>
            <div style="overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">
                <span style="display:block; font-size:9.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">Helpline Desk</span>
                <strong style="display:block; font-size:11.5px; color:var(--navy-dark);">0121-2652292</strong>
            </div>
            </div>

        <!-- Tile 3: MCB Official Website -->
        <a href="https://meerut.cantt.gov.in/" target="_blank" style="background:#fff; border:1px solid var(--border-color); border-radius:12px; padding:10px 12px; display:flex; align-items:center; justify-content:space-between; text-decoration:none; color:inherit; box-shadow:0 4px 12px rgba(0,0,0,0.02); transition:all 0.2s ease;" onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='translateY(-1px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='none';">
            <div style="display:flex; align-items:center; gap:10px; overflow:hidden;">
                <div style="width:36px; height:36px; border-radius:10px; background:#e0f2fe; color:#0284c7; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                    🌐
                </div>
                <div style="overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">
                    <span style="display:block; font-size:9.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">Meerut Cantt Board  🏛️</span>
                    <strong style="display:block; font-size:11.5px; color:var(--navy-dark); text-overflow:ellipsis; overflow:hidden;">View Official Website</strong>
                </div>
            </div>
            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px; opacity:0.5; color:var(--navy-dark); flex-shrink:0; margin-left:2px;"></i>
        </a>

        <!-- Tile 4: e-Chhawani Services -->
        <a href="https://echhawani.gov.in/" target="_blank" style="background:#fff; border:1px solid var(--border-color); border-radius:12px; padding:10px 12px; display:flex; align-items:center; justify-content:space-between; text-decoration:none; color:inherit; box-shadow:0 4px 12px rgba(0,0,0,0.02); transition:all 0.2s ease;" onmouseover="this.style.borderColor='var(--accent-orange)'; this.style.transform='translateY(-1px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='none';">
            <div style="display:flex; align-items:center; gap:10px; overflow:hidden;">
                <div style="width:36px; height:36px; border-radius:10px; background:#fff7ed; color:#ea580c; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                    🇮🇳
                </div>
                <div style="overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">
                    <span style="display:block; font-size:9.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">View 🏢</span>
                    <strong style="display:block; font-size:11.5px; color:var(--navy-dark); text-overflow:ellipsis; overflow:hidden;">e-Chhawani Portal</strong>
                </div>
            </div>
            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px; opacity:0.5; color:var(--navy-dark); flex-shrink:0; margin-left:2px;"></i>
        </a>

    </div>
</div>

<script class="dynamic-script">
(function() {
    let currentSlide = 0;
    const totalSlides = 3;
    let slideTimer = null;

    window.switchGallerySlide = function(index) {
        currentSlide = index;
        for (let i = 0; i < totalSlides; i++) {
            const slide = document.getElementById('slide-' + i);
            if (slide) {
                slide.style.opacity = (i === index) ? '1' : '0';
            }
        }
        
        const thumbs = document.querySelectorAll('.thumb-btn');
        thumbs.forEach((thumb, i) => {
            if (i === index) {
                thumb.style.borderColor = 'var(--primary)';
                thumb.style.opacity = '1';
            } else {
                thumb.style.borderColor = 'transparent';
                thumb.style.opacity = '0.65';
            }
        });

        resetSlideTimer();
    };

    function autoNextSlide() {
        let next = (currentSlide + 1) % totalSlides;
        window.switchGallerySlide(next);
    }

    function resetSlideTimer() {
        if (slideTimer) clearInterval(slideTimer);
        slideTimer = setInterval(autoNextSlide, 4500);
    }

    resetSlideTimer();

    // Ensure web browser storage is clean on login page render & disable autofill
    try {
        sessionStorage.clear();
        localStorage.clear();
    } catch(e) {}

    document.querySelectorAll('.login-section input').forEach(function(input) {
        input.setAttribute('autocomplete', input.type === 'password' ? 'new-password' : 'off');
        input.addEventListener('click', function() {
            this.removeAttribute('readonly');
        });
        input.addEventListener('focus', function() {
            this.removeAttribute('readonly');
        });
    });
})();
</script>
