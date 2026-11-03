<!-- views/users/pending_residents.php -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div class="card-title">
            <i class="fa fa-user-check text-primary"></i> Resident Registration Verification Queue
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">
            Barangay Hinaplanon Citizen Identity Gatekeeper
        </div>
    </div>
    <div class="card-body">
        <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 20px;">
            In accordance with Capstone Project Module 1, resident registrations submitted with a <strong>Barangay ID Number</strong> require validation and approval by the <strong>Barangay Head</strong> before granting access to disaster relief requests and community features.
        </p>

        <?php if (empty($pendingResidents)): ?>
            <div class="alert alert-info" style="display: flex; align-items: center; gap: 12px; padding: 20px;">
                <i class="fa fa-circle-check" style="font-size: 1.5rem; color: var(--info);"></i>
                <div>
                    <strong>No pending resident registrations!</strong>
                    <div style="font-size: 0.85rem; margin-top: 2px;">All registered residents have been processed. New submissions with Barangay IDs will appear here automatically.</div>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Barangay ID Number</th>
                            <th>Resident Full Name</th>
                            <th>Purok Assignment</th>
                            <th>Age / Gender</th>
                            <th>Contact Mobile</th>
                            <th>Registration Date</th>
                            <th>Verification Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingResidents as $res): ?>
                            <tr>
                                <td>
                                    <strong style="font-family: monospace; font-size: 0.95rem; color: var(--primary-dark);">
                                        <?= htmlspecialchars($res['id_number'] ?? '—') ?>
                                    </strong>
                                </td>
                                <td>
                                    <div><strong><?= htmlspecialchars($res['firstname'] . ' ' . ($res['middle_name'] ? $res['middle_name'] . ' ' : '') . $res['lastname']) ?></strong></div>
                                    <small style="color: var(--text-muted);"><?= htmlspecialchars($res['email']) ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($res['purok_name'] ?? 'Not Specified') ?></strong>
                                </td>
                                <td>
                                    <?= !empty($res['age']) ? (int)$res['age'] . ' yrs' : '—' ?>
                                    <small style="color: var(--text-muted);">(<?= htmlspecialchars($res['gender'] ?? '—') ?>)</small>
                                </td>
                                <td><?= htmlspecialchars($res['cellphone_number'] ?? '—') ?></td>
                                <td><?= date('M d, Y h:i A', strtotime($res['created_at'])) ?></td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <!-- Approve Form -->
                                        <form action="index.php?route=approve-resident" method="POST" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$res['user_id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fa fa-check"></i> Approve
                                            </button>
                                        </form>

                                        <!-- Decline Form -->
                                        <form action="index.php?route=reject-resident" method="POST" style="display: inline;" onsubmit="return confirm('Decline this resident registration?');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$res['user_id'] ?>">
                                            <input type="hidden" name="rejection_reason" value="Identity not verified against Barangay Hinaplanon Census records">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fa fa-xmark"></i> Decline
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
