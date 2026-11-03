<!-- views/auth/login.php -->
<form action="index.php?route=login" method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="user@domain.gov.ph" required autofocus>
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px;">
        <i class="fa fa-lock-open"></i> Sign In to Portal
    </button>

    <div style="text-align: center; margin-top: 16px; font-size: 0.85rem;">
        Are you a Hinaplanon Resident? 
        <a href="index.php?route=register" style="font-weight: 600;">Register Here</a>
    </div>

    <div class="demo-account-pills">
        <div style="font-weight: 700; color: var(--text-muted); margin-bottom: 6px;">
            <i class="fa fa-key"></i> Quick-Fill Demo Roles (Click to auto-fill):
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 4px;">
            <span class="demo-badge-btn" data-demo-email="icdrrmo@iligan.gov.ph" data-demo-pass="icdrrmo123">
                <i class="fa fa-user-shield"></i> ICDRRMO Admin
            </span>
            <span class="demo-badge-btn" data-demo-email="brgyhead@hinaplanon.gov.ph" data-demo-pass="brgyhead123">
                <i class="fa fa-user-tie"></i> Barangay Head
            </span>
            <span class="demo-badge-btn" data-demo-email="responder1@hinaplanon.gov.ph" data-demo-pass="responder123">
                <i class="fa fa-truck-medical"></i> Responder / Rescuer
            </span>
            <span class="demo-badge-btn" data-demo-email="juan.delacruz@gmail.com" data-demo-pass="resident123">
                <i class="fa fa-house-user"></i> Resident
            </span>
        </div>
    </div>
</form>
