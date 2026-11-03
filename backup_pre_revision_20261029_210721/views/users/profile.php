<!-- views/users/profile.php -->
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-id-card"></i> User Profile Management</div>
    </div>
    <div class="card-body">
        <form action="index.php?route=profile" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 3rem; color: var(--primary);"><i class="fa fa-circle-user"></i></div>
                <h3 style="margin-top: 6px;"><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></h3>
                <span class="role-pill role-<?= strtolower(str_replace(' ', '-', $user['user_type'])) ?>">
                    <?= htmlspecialchars($user['user_type']) ?>
                </span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" name="firstname" class="form-control" value="<?= htmlspecialchars($user['firstname']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lastname" class="form-control" value="<?= htmlspecialchars($user['lastname']) ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">Email Address (Read-only)</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly style="background-color: #f1f5f9;">
                </div>
                <div class="form-group">
                    <label class="form-label">Cellphone Number</label>
                    <input type="tel" name="cellphone_number" class="form-control" value="<?= htmlspecialchars($user['cellphone_number'] ?? '') ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">ID Number / Badge</label>
                    <input type="text" name="id_number" class="form-control" value="<?= htmlspecialchars($user['id_number'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="Male" <?= $user['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $user['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= $user['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top: 16px; padding-top: 16px; border-top: 1px dashed var(--border);">
                <label class="form-label">Change Password (Leave blank to keep current)</label>
                <input type="password" name="new_password" class="form-control" placeholder="Enter new password">
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 12px;">
                <i class="fa fa-save"></i> Save Profile Changes
            </button>
        </form>
    </div>
</div>
