<!-- views/users/pending_residents.php -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-user-check"></i> Resident Registration Verification Queue</div>
    </div>
    <div class="card-body">
        <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 16px;">
            In accordance with Capstone Project Module 1, the Barangay Head verifies and either approves or declines resident registration requests before granting system access.
        </p>

        <?php if (empty($pendingResidents)): ?>
            <div class="alert alert-info">
                <i class="fa fa-check-circle"></i> No pending resident registration requests. All registered resident accounts are verified.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Temporary ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Contact Phone</th>
                            <th>Gender</th>
                            <th>Registration Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingResidents as $res): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($res['id_number']) ?></code></td>
                                <td><strong><?= htmlspecialchars($res['firstname'] . ' ' . $res['lastname']) ?></strong></td>
                                <td><?= htmlspecialchars($res['email']) ?></td>
                                <td><?= htmlspecialchars($res['cellphone_number'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($res['gender'] ?? '—') ?></td>
                                <td><?= date('M d, Y h:i A', strtotime($res['created_at'])) ?></td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <!-- Approve Form -->
                                        <form action="index.php?route=approve-resident" method="POST" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="user_id" value="<?= $res['user_id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fa fa-check"></i> Approve
                                            </button>
                                        </form>

                                        <!-- Decline Form -->
                                        <form action="index.php?route=reject-resident" method="POST" style="display: inline;" onsubmit="return confirm('Decline this resident registration?');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="user_id" value="<?= $res['user_id'] ?>">
                                            <input type="hidden" name="rejection_reason" value="Identity not confirmed in Barangay Hinaplanon Census">
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
