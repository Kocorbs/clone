<!-- views/reports/index.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary-dark);">Disaster Preparedness & Resource Allocation Reports</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">
            Official printable records and exportable audit trail ledgers for Barangay Hinaplanon & ICDRRMC.
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <button onclick="window.print();" class="btn btn-outline"><i class="fa fa-print"></i> Print Document</button>
        <a href="index.php?route=reports-export-allocations" class="btn btn-primary"><i class="fa fa-file-csv"></i> Export Allocations CSV</a>
    </div>
</div>

<!-- Allocations Summary Report Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-file-invoice"></i> Official Resource Allocations Ledger</div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Rec #</th>
                    <th>Req #</th>
                    <th>Affected Purok</th>
                    <th>Priority</th>
                    <th>Resource Allocated</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Reviewed By</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recommendations)): ?>
                    <tr><td colspan="9" style="text-align: center; color: var(--text-muted);">No allocation records generated.</td></tr>
                <?php else: ?>
                    <?php foreach ($recommendations as $r): ?>
                        <tr>
                            <td><strong>#<?= $r['recommendation_id'] ?></strong></td>
                            <td>#<?= $r['disaster_request_id'] ?></td>
                            <td><?= htmlspecialchars($r['purok_name']) ?></td>
                            <td><?= View::priorityBadge($r['predicted_priority']) ?></td>
                            <td><?= htmlspecialchars($r['supply_name']) ?></td>
                            <td><strong><?= (int)$r['recommended_quantity'] ?></strong></td>
                            <td><?= View::statusBadge($r['recommendation_status']) ?></td>
                            <td><?= $r['reviewer_first'] ? htmlspecialchars($r['reviewer_first'] . ' ' . $r['reviewer_last']) : '<span style="color: var(--text-muted);">Pending</span>' ?></td>
                            <td><?= date('M d, Y h:i A', strtotime($r['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Immutable Audit Trail Ledger -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-scale-balanced"></i> Resource Request & Recommendation Audit Trail (`resource_request_log`)</div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Action Taken</th>
                    <th>Personnel / Actor</th>
                    <th>Timestamp</th>
                    <th>Remarks & Justification</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No transactions recorded in audit ledger.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td><code>LOG-<?= str_pad((string)$l['log_id'], 4, '0', STR_PAD_LEFT) ?></code></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($l['action_taken']) ?></span></td>
                            <td><strong><?= htmlspecialchars($l['action_by']) ?></strong></td>
                            <td><?= date('M d, Y h:i:s A', strtotime($l['action_date'])) ?></td>
                            <td><small><?= htmlspecialchars($l['remarks'] ?? '—') ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
