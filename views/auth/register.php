<!-- views/auth/register.php -->
<div class="auth-page-wrapper" style="padding: 40px 20px;">
    <div class="auth-box" style="max-width: 620px;">
        <div class="auth-header">
            <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 8px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #2563eb, #1e3a8a); display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 1.1rem;">
                    <i class="fa fa-id-card"></i>
                </div>
                <h2 style="font-size: 1.25rem; margin-bottom: 0;">Resident Registration</h2>
            </div>
            <p style="font-size: 0.8rem; color: var(--text-muted);">
                Barangay Hinaplanon, Iligan City Disaster Preparedness Portal
            </p>
        </div>

        <div class="alert alert-info" style="font-size: 0.82rem; margin-bottom: 20px;">
            <i class="fa fa-circle-info" style="margin-top: 2px;"></i>
            <div>
                <strong>Barangay ID Verification:</strong> An email address is <strong>not required</strong>. You will use your official Barangay ID Number to sign in. Upon registration, your account is queued for verification by the Barangay Head.
            </div>
        </div>

        <?php if (!empty($flashes)): ?>
            <?php foreach ($flashes as $type => $messages): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="alert alert-<?= htmlspecialchars($type) ?>" style="font-size: 0.84rem;">
                        <div><?= htmlspecialchars($msg) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <form action="index.php?route=register" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <!-- Official Barangay ID -->
            <div class="form-group">
                <label class="form-label">Barangay ID Number *</label>
                <input type="text" name="id_number" class="form-control" placeholder="e.g. HIN-2026-XXXX or official ID format" required autofocus>
                <small style="color: var(--text-muted); font-size: 0.74rem;">Must be your unique Barangay Hinaplanon resident identification number.</small>
            </div>

            <!-- Name Fields -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                <div class="form-group">
                    <label class="form-label">First Name *</label>
                    <input type="text" name="firstname" class="form-control" placeholder="Juan" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" class="form-control" placeholder="Optional">
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="lastname" class="form-control" placeholder="Dela Cruz" required>
                </div>
            </div>

            <!-- Residence Location -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">Barangay</label>
                    <input type="text" class="form-control" value="Barangay Hinaplanon, Iligan City" readonly style="background-color: #f1f5f9;">
                </div>
                <div class="form-group">
                    <label class="form-label">Purok / Zone *</label>
                    <select name="purok_id" class="form-control" required>
                        <option value="">-- Select Your Purok --</option>
                        <?php foreach ($puroks as $p): ?>
                            <option value="<?= $p['purok_id'] ?>">
                                <?= htmlspecialchars($p['purok_name']) ?> (<?= htmlspecialchars($p['risk_classification']) ?> Risk)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Demographics & Optional Contact -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Age</label>
                    <input type="number" name="age" class="form-control" min="15" max="120" placeholder="e.g. 35">
                </div>
                <div class="form-group">
                    <label class="form-label">Cellphone Number</label>
                    <input type="tel" name="cellphone_number" class="form-control" placeholder="0917-000-0000">
                </div>
            </div>

            <!-- Password Fields -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 4px;">
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" name="password_confirm" class="form-control" placeholder="Re-type password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 11px; font-weight: 700; margin-top: 10px;">
                <i class="fa fa-user-plus"></i> Submit Resident Registration
            </button>

            <div style="text-align: center; margin-top: 16px; font-size: 0.85rem;">
                Already have an account? <a href="index.php?route=login" style="font-weight: 700;">Sign In here</a>
            </div>
        </form>
    </div>
</div>
