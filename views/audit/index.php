<!-- views/audit/index.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
            System Security & Audit Trail Ledger
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
            Immutable operational activity logs tracking disaster incidents, supply allocations, account authentications, and shelter updates.
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=dashboard" class="btn btn-outline">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div class="card-title">
            <i class="fa fa-shield-halved text-primary"></i> System Event Ledger (Last <?= count($logs) ?> Events)
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">
            Compliance: ISO 9126 Security & Auditability Requirements
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" style="font-size: 0.85rem;">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User / Actor</th>
                    <th>Action</th>
                    <th>Module / Target</th>
                    <th>Record ID</th>
                    <th>IP Address</th>
                    <th>Details & Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">
                            No audit trail events recorded in system.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                            $badgeType = 'info';
                            $action = $log['action_name'] ?? '';
                            if (stripos($action, 'APPROV') !== false || stripos($action, 'VERIF') !== false) {
                                $badgeType = 'success';
                            } elseif (stripos($action, 'REJECT') !== false || stripos($action, 'DECLIN') !== false) {
                                $badgeType = 'danger';
                            } elseif (stripos($action, 'SOS') !== false || stripos($action, 'EMERGENCY') !== false) {
                                $badgeType = 'urgent';
                            } elseif (stripos($action, 'LOGIN') !== false) {
                                $badgeType = 'primary';
                            }
                        ?>
                        <tr>
                            <td style="white-space: nowrap; font-family: monospace; font-size: 0.8rem; color: var(--text-muted);">
                                <?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($log['user_name'] ?? 'System') ?></strong>
                                <?php if (!empty($log['user_id'])): ?>
                                    <small style="color: var(--text-muted);"> (ID: <?= (int)$log['user_id'] ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $badgeType ?>"><?= htmlspecialchars($log['action_name']) ?></span>
                            </td>
                            <td>
                                <code><?= htmlspecialchars($log['table_name'] ?? '—') ?></code>
                            </td>
                            <td>
                                <?= !empty($log['record_id']) ? '#' . (int)$log['record_id'] : '—' ?>
                            </td>
                            <td style="font-family: monospace; font-size: 0.78rem; color: var(--text-muted);">
                                <?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?>
                            </td>
                            <td style="max-width: 320px;">
                                <div style="color: #334155; line-height: 1.4; word-break: break-word;">
                                    <?= htmlspecialchars($log['details'] ?? '—') ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
