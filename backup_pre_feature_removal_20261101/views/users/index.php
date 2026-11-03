<!-- views/users/index.php -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-users-gear"></i> System User Accounts</div>
        <a href="index.php?route=users-create" class="btn btn-primary btn-sm"><i class="fa fa-user-plus"></i> New Account</a>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap;">
            <a href="index.php?route=users" class="btn btn-sm <?= empty($roleFilter) ? 'btn-primary' : 'btn-outline' ?>">All Roles (<?= array_sum($roleCounts) ?>)</a>
            <a href="index.php?route=users&role=ICDRRMO" class="btn btn-sm <?= $roleFilter === 'ICDRRMO' ? 'btn-primary' : 'btn-outline' ?>">ICDRRMO (<?= $roleCounts['ICDRRMO'] ?? 0 ?>)</a>
            <a href="index.php?route=users&role=Barangay+Head" class="btn btn-sm <?= $roleFilter === 'Barangay Head' ? 'btn-primary' : 'btn-outline' ?>">Barangay Head (<?= $roleCounts['Barangay Head'] ?? 0 ?>)</a>
            <a href="index.php?route=users&role=Responder" class="btn btn-sm <?= $roleFilter === 'Responder' ? 'btn-primary' : 'btn-outline' ?>">Responders (<?= $roleCounts['Responder'] ?? 0 ?>)</a>
            <a href="index.php?route=users&role=Resident" class="btn btn-sm <?= $roleFilter === 'Resident' ? 'btn-primary' : 'btn-outline' ?>">Residents (<?= $roleCounts['Resident'] ?? 0 ?>)</a>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Number</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Approval Status</th>
                        <th>Registered Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($u['id_number'] ?? '—') ?></code></td>
                            <td><strong><?= htmlspecialchars($u['firstname'] . ' ' . $u['lastname']) ?></strong></td>
                            <td><span class="role-pill role-<?= strtolower(str_replace(' ', '-', $u['user_type'])) ?>"><?= htmlspecialchars($u['user_type']) ?></span></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['cellphone_number'] ?? '—') ?></td>
                            <td>
                                <?php if ($u['is_approved'] == 1): ?>
                                    <span class="badge badge-success">Approved / Active</span>
                                <?php elseif ($u['is_approved'] == 0): ?>
                                    <span class="badge badge-warning">Pending Review</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Declined</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
