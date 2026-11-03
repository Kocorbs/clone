<!-- views/users/create.php -->
<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-user-plus"></i> Create Official / Personnel Account</div>
    </div>
    <div class="card-body">
        <form action="index.php?route=users-create" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">Role / User Type *</label>
                <select name="user_type" class="form-control" required>
                    <option value="ICDRRMO">ICDRRMO Personnel</option>
                    <option value="Barangay Head">Barangay Head</option>
                    <option value="Responder">Responder / Rescuer</option>
                    <option value="Resident">Resident</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">First Name *</label>
                    <input type="text" name="firstname" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="lastname" class="form-control" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">ID / Badge Number</label>
                    <input type="text" name="id_number" class="form-control" placeholder="e.g. RESCUE-099">
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
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Cellphone Number</label>
                    <input type="tel" name="cellphone_number" class="form-control" placeholder="0917-123-4567">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Temporary Password *</label>
                <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Account</button>
                <a href="index.php?route=users" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
