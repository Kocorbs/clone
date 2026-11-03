<!-- views/auth/register.php -->
<form action="index.php?route=register" method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">
        <i class="fa fa-circle-info text-primary"></i> Registration creates a resident account. Under Barangay Hinaplanon policy, resident registrations must be verified and approved by the Barangay Head before login is permitted.
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <div class="form-group">
            <label class="form-label">First Name *</label>
            <input type="text" name="firstname" class="form-control" placeholder="Juan" required>
        </div>
        <div class="form-group">
            <label class="form-label">Last Name *</label>
            <input type="text" name="lastname" class="form-control" placeholder="Dela Cruz" required>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Email Address *</label>
        <input type="email" name="email" class="form-control" placeholder="juan.delacruz@example.com" required>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <div class="form-group">
            <label class="form-label">Cellphone Number</label>
            <input type="tel" name="cellphone_number" class="form-control" placeholder="0917-000-0000">
        </div>
        <div class="form-group">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-control">
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <div class="form-group">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control" placeholder="Min. 6 chars" required>
        </div>
        <div class="form-group">
            <label class="form-label">Confirm Password *</label>
            <input type="password" name="password_confirm" class="form-control" placeholder="Repeat password" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px;">
        <i class="fa fa-user-plus"></i> Submit Resident Registration
    </button>

    <div style="text-align: center; margin-top: 16px; font-size: 0.85rem;">
        Already registered? <a href="index.php?route=login" style="font-weight: 600;">Sign In</a>
    </div>
</form>
