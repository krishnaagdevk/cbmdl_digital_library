<h2 class="header-title-sub">Welcome To Cantonment Digital Library</h2>

<div class="login-container">
    <div class="external-menu">
        <!-- Interactive 4:3 Library Heritage Showcase Slider -->
        <div class="library-gallery-card" style="background:white; border-radius:16px; border:1px solid var(--border-color); overflow:hidden; box-shadow:0 8px 25px rgba(0,0,0,0.04); margin-bottom:12px;">
            <div style="position:relative; width:100%; aspect-ratio:4/3; background:#1e293b; overflow:hidden;">
                <!-- Slide 1: Historical Plaque -->
                <div class="gallery-slide active-slide" id="slide-0" style="position:absolute; inset:0; opacity:1; transition:opacity 0.5s ease;">
                    <img src="<?= BASE_URL ?>images/library_plaque.jpg" alt="Inauguration Plaque" style="width:100%; height:100%; object-fit:cover; display:block;">
                    <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(transparent, rgba(15,23,42,0.9)); color:white; padding:12px 16px;">
                        <span style="font-size:10px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; color:var(--accent-orange); display:block;">Historical Foundation (1953)</span>
                        <strong style="font-size:12.5px; display:block; line-height:1.3;">Inauguration Plaque by Hon'ble Shri Satish Chandra</strong>
                    </div>
                </div>

                <!-- Slide 2: Heritage Building -->
                <div class="gallery-slide" id="slide-1" style="position:absolute; inset:0; opacity:0; transition:opacity 0.5s ease;">
                    <img src="<?= BASE_URL ?>images/library_building.png" alt="Library Entrance" style="width:100%; height:100%; object-fit:cover; display:block;">
                    <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(transparent, rgba(15,23,42,0.9)); color:white; padding:12px 16px;">
                        <span style="font-size:10px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; color:#60a5fa; display:block;">Library Campus</span>
                        <strong style="font-size:12.5px; display:block; line-height:1.3;">Cantonment Library Heritage Building</strong>
                    </div>
                </div>

                <!-- Slide 3: Digital Reading Hall -->
                <div class="gallery-slide" id="slide-2" style="position:absolute; inset:0; opacity:0; transition:opacity 0.5s ease;">
                    <img src="<?= BASE_URL ?>images/library_hall.png" alt="Digital Reading Hall" style="width:100%; height:100%; object-fit:cover; display:block;">
                    <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(transparent, rgba(15,23,42,0.9)); color:white; padding:12px 16px;">
                        <span style="font-size:10px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; color:#34d399; display:block;">Modern Facilities</span>
                        <strong style="font-size:12.5px; display:block; line-height:1.3;">E-Resource Center & Reading Hall</strong>
                    </div>
                </div>
            </div>

            <!-- Thumbnail Selector Buttons -->
            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:6px; padding:10px; background:#f8fafc; border-top:1px solid var(--border-color);">
                <button type="button" class="thumb-btn active-thumb" onclick="switchGallerySlide(0)" style="border:2px solid var(--primary); padding:0; border-radius:8px; overflow:hidden; cursor:pointer; height:52px; background:none; transition:all 0.2s;" title="Historical Foundation Plaque">
                    <img src="<?= BASE_URL ?>images/library_plaque.jpg" style="width:100%; height:100%; object-fit:cover; display:block;">
                </button>
                <button type="button" class="thumb-btn" onclick="switchGallerySlide(1)" style="border:2px solid transparent; padding:0; border-radius:8px; overflow:hidden; cursor:pointer; height:52px; background:none; opacity:0.65; transition:all 0.2s;" title="Library Heritage Building">
                    <img src="<?= BASE_URL ?>images/library_building.png" style="width:100%; height:100%; object-fit:cover; display:block;">
                </button>
                <button type="button" class="thumb-btn" onclick="switchGallerySlide(2)" style="border:2px solid transparent; padding:0; border-radius:8px; overflow:hidden; cursor:pointer; height:52px; background:none; opacity:0.65; transition:all 0.2s;" title="Digital Reading Hall">
                    <img src="<?= BASE_URL ?>images/library_hall.png" style="width:100%; height:100%; object-fit:cover; display:block;">
                </button>
            </div>
        </div>

        <a href="https://meerut.cantt.gov.in/" target="_blank">
            <span><i class="fa-solid fa-globe"></i> MCB Official Website</span>
            <i class="fa-solid fa-chevron-right"></i>
        </a>
        <a href="https://echhawani.gov.in/" target="_blank">
            <span><i class="fa-solid fa-circle-info"></i> E-CHHAWANI Portal</span>
            <i class="fa-solid fa-chevron-right"></i>
        </a>
        <div style="background:white; padding:16px 20px; border-radius:12px; border:1px solid var(--border-color); display:flex; flex-direction:column; gap:8px;">
            <p style="margin:0; font-weight:600; color:var(--navy-dark); font-size:13px;"><i class="fa-solid fa-envelope" style="color:var(--primary);"></i> Email: cbmeerut1@gmail.com</p>
            <p style="margin:0; font-weight:600; color:var(--navy-dark); font-size:13px;"><i class="fa-solid fa-phone" style="color:var(--primary);"></i> Help Desk: 0121-2652292</p>
        </div>
    </div>

    <div class="login-section">
        <?php if ($action === 'admin_login'): ?>
            <h3 style="text-align:center; border:none; margin-bottom:20px;"><i class="fa-solid fa-user-shield" style="color:var(--primary);"></i> Librarian Login</h3>
            <form method="post" action="?action=admin_login">
                <?= csrf_input() ?>
                <label for="admin_uid"><i class="fa-solid fa-user"></i> User ID</label>
                <input id="admin_uid" name="username" required placeholder="Librarian Username">
                
                <label for="admin_pwd"><i class="fa-solid fa-key"></i> Password</label>
                <input id="admin_pwd" type="password" name="password" required placeholder="Enter password" maxlength="15">
                
                <button style="width:100%; padding:14px; margin-top:10px;"><i class="fa-solid fa-shield-halved"></i> Verify & Authorize</button>
            </form>
        <?php else: ?>
            <h3 style="text-align:center; border:none; margin-bottom:20px;"><i class="fa-solid fa-user-graduate" style="color:var(--primary);"></i> Member Login</h3>
            <form method="post" action="?action=member_login">
                <?= csrf_input() ?>
                <label for="member_mob"><i class="fa-solid fa-phone"></i> Mobile Number</label>
                <input id="member_mob" name="mobile" required placeholder="Registered Mobile No">
                
                <label for="member_pwd"><i class="fa-solid fa-key"></i> Password</label>
                <input id="member_pwd" type="password" name="password" required placeholder="Enter password" maxlength="15">
                
                <button style="width:100%; padding:14px; margin-top:10px;"><i class="fa-solid fa-right-to-bracket"></i> Login Account</button>
            </form>
        <?php endif; ?>
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
})();
</script>
