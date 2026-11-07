<!-- views/auth/login.php — Modern Split-Card Sign In Page (Inspiration Layout) -->
<div class="auth-split-canvas">
    <div class="auth-split-modal">
        <!-- LEFT SIDE: Modern Form -->
        <div class="auth-split-form-col">
            <!-- Brand Pill Badge at top-left -->
            <div class="auth-pill-badge">
                <img src="assets/images/hinaplanon_seal.png" alt="Seal" class="auth-pill-img">
                <span>Padayon Hinaplanon</span>
            </div>

            <div class="auth-form-intro">
                <h1 class="auth-form-title">Welcome Back</h1>
                <p class="auth-form-sub">Sign in to access your disaster & resident portal</p>
            </div>

            <!-- Flash messages -->
            <?php if (!empty($flashes)): ?>
                <?php foreach ($flashes as $type => $messages): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="alert alert-<?= htmlspecialchars($type) ?>" style="padding: 10px 16px; font-size: 0.82rem; margin-bottom: 16px; border-radius: 12px;">
                            <i class="fa fa-circle-info" style="margin-top: 2px;"></i>
                            <div><?= htmlspecialchars($msg) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Login Form with modern pill inputs -->
            <form action="index.php?route=login" method="POST" id="portalLoginForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="auth-input-group">
                    <label class="auth-input-label" for="identifier">Barangay ID or Email</label>
                    <div class="auth-pill-input-box">
                        <i class="fa-regular fa-id-card auth-field-icon"></i>
                        <input type="text" id="identifier" name="identifier" class="auth-pill-input"
                            placeholder="e.g. RES-2026-001 or email" required autofocus
                            value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>">
                    </div>
                </div>

                <div class="auth-input-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="auth-input-label" for="password" style="margin-bottom: 0;">Password</label>
                        <a href="index.php?route=forgot-password" class="auth-forgot-link">Forgot Password?</a>
                    </div>
                    <div class="auth-pill-input-box">
                        <i class="fa-solid fa-lock auth-field-icon"></i>
                        <input type="password" id="password" name="password" class="auth-pill-input"
                            placeholder="Enter your password" required style="padding-right: 44px;">
                        <button type="button" class="auth-eye-btn" id="togglePassword" aria-label="Toggle password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button with Pill Styling -->
                <button type="submit" class="auth-pill-submit-btn">
                    <span>Log In</span>
                    <i class="fa-solid fa-arrow-right" style="font-size: 0.88rem;"></i>
                </button>

                <!-- Demo Accounts Quick Fill Pills -->
                <div class="auth-demo-section">
                    <div class="auth-demo-header">
                        <i class="fa-solid fa-bolt" style="color: #2C968C;"></i>
                        <span>Demo One-Click Accounts:</span>
                    </div>
                    <div class="auth-demo-pills-row">
                        <button type="button" class="auth-demo-pill" data-demo-identifier="icdrrmo@iligan.gov.ph" data-demo-pass="icdrrmo123" title="Iligan City DRRMO">
                            <i class="fa-solid fa-shield text-danger"></i> <span>ICDRRMO</span>
                        </button>
                        <button type="button" class="auth-demo-pill" data-demo-identifier="brgyhead@hinaplanon.gov.ph" data-demo-pass="brgyhead123" title="Barangay Head">
                            <i class="fa-solid fa-user-tie text-warning"></i> <span>Brgy Head</span>
                        </button>
                        <button type="button" class="auth-demo-pill" data-demo-identifier="responder1@hinaplanon.gov.ph" data-demo-pass="responder123" title="Responder">
                            <i class="fa-solid fa-truck-medical text-success"></i> <span>Responder</span>
                        </button>
                        <button type="button" class="auth-demo-pill" data-demo-identifier="RES-2026-001" data-demo-pass="resident123" title="Resident">
                            <i class="fa-solid fa-house-user" style="color: #00665B;"></i> <span>Resident</span>
                        </button>
                    </div>
                </div>

                <!-- Bottom Links -->
                <div class="auth-split-bottom-row">
                    <div class="auth-switch-link">
                        Don't have an account? <a href="index.php?route=register">Register</a>
                    </div>
                    <a href="index.php?route=landing" class="auth-back-link">
                        <i class="fa fa-arrow-left"></i> Back to Portal
                    </a>
                </div>
            </form>
        </div>

        <!-- RIGHT SIDE: Logo Panel -->
        <div class="auth-split-logo-col">
            <img src="assets/images/hinaplanon_seal.png" alt="Barangay Hinaplanon Seal" class="auth-logo-centered">
        </div>
    </div>
</div>
