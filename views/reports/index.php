<!-- views/reports/index.php -->
<?php 
use App\Core\View; 
use App\Core\Auth;

$currentRole = Auth::role();
$csrfToken = Auth::csrfToken();
$summary = $summary ?? ['total_items' => count($supplies), 'total_quantity' => 0, 'total_remaining' => 0, 'total_released' => 0];
$lowStock = $lowStock ?? [];
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
            Barangay Hinaplanon Resource Management
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
            Unified resource management: centralized inventory tracking, stock replenishment, AI-driven allocations, and transaction ledgers in one frame.
        </p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <button onclick="window.print();" class="btn btn-outline"><i class="fa fa-print"></i> Print Document</button>
        <a href="index.php?route=inventory-export" class="btn btn-outline"><i class="fa fa-file-csv"></i> Export Inventory CSV</a>
        <a href="index.php?route=reports-export-allocations" class="btn btn-primary"><i class="fa fa-file-invoice"></i> Export Allocations CSV</a>
        <?php if ($currentRole === 'ICDRRMO'): ?>
            <a href="index.php?route=inventory-create" class="btn btn-success"><i class="fa fa-plus"></i> Add Supply Item</a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($lowStock)): ?>
    <div class="alert alert-danger" style="margin-bottom: 20px;">
        <i class="fa fa-triangle-exclamation"></i>
        <div>
            <strong>Low Inventory Warning:</strong> <?= count($lowStock) ?> items are running low or depleted in centralized storage. Immediate replenishment is recommended.
        </div>
    </div>
<?php endif; ?>

<!-- ============================================== -->
<!-- 1. RESOURCE KPI SUMMARY CARDS -->
<!-- ============================================== -->
<div class="grid-cols-4" style="margin-bottom: 22px;">
    <div class="stat-card" style="border-left: 4px solid var(--primary-light);">
        <div class="stat-label"><i class="fa fa-boxes-stacked text-primary"></i> Registered Supply Items</div>
        <div class="stat-value"><?= (int)($summary['total_items'] ?? count($supplies)) ?></div>
        <div class="stat-sub">Across centralized disaster categories</div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #6366f1;">
        <div class="stat-label"><i class="fa fa-layer-group text-info"></i> Total Baseline Stock</div>
        <div class="stat-value"><?= number_format((int)($summary['total_quantity'] ?? 0)) ?></div>
        <div class="stat-sub">Overall recorded warehouse capacity</div>
    </div>

    <div class="stat-card" style="border-left: 4px solid var(--success); background: linear-gradient(to bottom, #ffffff, #f0fdf4);">
        <div class="stat-label"><i class="fa fa-box-open text-success"></i> Available Stock Remaining</div>
        <div class="stat-value" style="color: var(--success);"><?= number_format((int)($summary['total_remaining'] ?? 0)) ?></div>
        <div class="stat-sub">Immediately available for allocation</div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #f59e0b;">
        <div class="stat-label"><i class="fa fa-truck-ramp-box text-warning"></i> Total Released / Distributed</div>
        <div class="stat-value" style="color: #b45309;"><?= number_format((int)($summary['total_released'] ?? 0)) ?></div>
        <div class="stat-sub">Audited disaster relief releases</div>
    </div>
</div>

<!-- ============================================== -->
<!-- 2. UNIFIED RESOURCE MANAGEMENT FRAME -->
<!-- ============================================== -->
<div class="card" style="margin-bottom: 24px; border-top: 4px solid var(--primary-light);">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: #f8fafc;">
        <div class="card-title" style="margin: 0;">
            <i class="fa fa-cubes text-primary"></i> Resource Management Control Frame
        </div>

        <!-- Frame Navigation Filter Tabs -->
        <div style="display: flex; gap: 6px; flex-wrap: wrap;" id="resourceTabGroup">
            <button type="button" class="btn btn-sm btn-primary" onclick="switchResourceSection('all', this)">
                <i class="fa fa-table-cells"></i> All-in-One Frame
            </button>
            <button type="button" class="btn btn-sm btn-outline" onclick="switchResourceSection('inventory', this)">
                <i class="fa fa-warehouse"></i> Supply Inventory (<?= count($supplies) ?>)
            </button>
            <button type="button" class="btn btn-sm btn-outline" onclick="switchResourceSection('allocations', this)">
                <i class="fa fa-file-invoice"></i> Allocation Ledgers (<?= count($recommendations) ?>)
            </button>
            <button type="button" class="btn btn-sm btn-outline" onclick="switchResourceSection('audit', this)">
                <i class="fa fa-scale-balanced"></i> Transaction Audit (<?= count($logs) ?>)
            </button>
        </div>
    </div>

    <div class="card-body" style="padding: 20px; display: flex; flex-direction: column; gap: 24px;">

        <!-- -------------------------------------------------------- -->
        <!-- SECTION 1: CENTRALIZED SUPPLY INVENTORY & REPLENISHMENT  -->
        <!-- -------------------------------------------------------- -->
        <div class="resource-section-block" id="frame-section-inventory">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">
                <div>
                    <h3 style="font-size: 1.12rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
                        <i class="fa fa-warehouse text-primary"></i> Centralized Supply Inventory & Stock Tracking
                    </h3>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                        Real-time quantities, units of measure, allocation readiness, and fast stock replenishment.
                    </div>
                </div>
                <div style="display: flex; gap: 6px;">
                    <?php if ($currentRole === 'ICDRRMO'): ?>
                        <a href="index.php?route=inventory-create" class="btn btn-sm btn-primary">
                            <i class="fa fa-plus"></i> Add Item
                        </a>
                    <?php endif; ?>
                    <a href="index.php?route=inventory-export" class="btn btn-sm btn-outline">
                        <i class="fa fa-file-csv"></i> CSV
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Category</th>
                            <th>Supply Name</th>
                            <th>Baseline Total</th>
                            <th>Remaining Available</th>
                            <th>Total Released</th>
                            <th>Unit</th>
                            <th>Stock Status</th>
                            <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head'])): ?>
                                <th style="min-width: 150px;">Stock Replenishment</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($supplies)): ?>
                            <tr><td colspan="9" style="text-align: center; color: var(--text-muted); padding: 24px;">No inventory supplies registered yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($supplies as $s): ?>
                                <tr>
                                    <td><code>SUP-<?= str_pad((string)$s['supply_id'], 3, '0', STR_PAD_LEFT) ?></code></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($s['supply_type']) ?></span></td>
                                    <td><strong><?= htmlspecialchars($s['supply_name']) ?></strong></td>
                                    <td><?= number_format((int)$s['supply_quantity']) ?></td>
                                    <td>
                                        <strong style="font-size: 1rem; color: <?= (int)$s['supply_remaining'] <= 0 ? 'var(--danger)' : 'var(--success)' ?>;">
                                            <?= number_format((int)$s['supply_remaining']) ?>
                                        </strong>
                                    </td>
                                    <td><?= number_format((int)$s['total_release']) ?></td>
                                    <td><?= htmlspecialchars($s['unit_of_measure']) ?></td>
                                    <td><?= View::statusBadge($s['supply_status']) ?></td>
                                    <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head'])): ?>
                                        <td>
                                            <form action="index.php?route=inventory-replenish" method="POST" style="display: flex; gap: 4px; align-items: center;">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                <input type="hidden" name="supply_id" value="<?= $s['supply_id'] ?>">
                                                <input type="hidden" name="redirect_to" value="index.php?route=reports#frame-section-inventory">
                                                <input type="number" name="replenish_quantity" class="form-control" style="width: 75px; padding: 4px 6px; font-size: 0.8rem;" min="1" placeholder="+Qty" required>
                                                <button type="submit" class="btn btn-primary btn-sm" title="Replenish stock for <?= htmlspecialchars($s['supply_name']) ?>">
                                                    <i class="fa fa-arrow-up"></i>
                                                </button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- -------------------------------------------------------- -->
        <!-- SECTION 2: OFFICIAL RESOURCE ALLOCATIONS LEDGER          -->
        <!-- -------------------------------------------------------- -->
        <div class="resource-section-block" id="frame-section-allocations">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">
                <div>
                    <h3 style="font-size: 1.12rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
                        <i class="fa fa-file-invoice text-primary"></i> Official Resource Allocations Ledger
                    </h3>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                        Decision Tree ML supply recommendations, official quantities approved, and release timeline.
                    </div>
                </div>
                <div style="display: flex; gap: 6px;">
                    <a href="index.php?route=reports-export-allocations" class="btn btn-sm btn-outline">
                        <i class="fa fa-file-csv"></i> Export Allocations
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
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
                            <tr><td colspan="9" style="text-align: center; color: var(--text-muted); padding: 24px;">No allocation records generated.</td></tr>
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
                                    <td><?= $r['reviewer_first'] ? htmlspecialchars($r['reviewer_first'] . ' ' . $r['reviewer_last']) : '<span style="color: var(--text-muted);">Pending Review</span>' ?></td>
                                    <td><?= date('M d, Y h:i A', strtotime($r['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- -------------------------------------------------------- -->
        <!-- SECTION 3: TRANSACTION AUDIT TRAIL LEDGER                -->
        <!-- -------------------------------------------------------- -->
        <div class="resource-section-block" id="frame-section-audit">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">
                <div>
                    <h3 style="font-size: 1.12rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
                        <i class="fa fa-scale-balanced text-primary"></i> Resource Request & Recommendation Audit Trail (`resource_request_log`)
                    </h3>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                        Immutable system transaction ledger recording all inventory deductions, replenishments, and approvals.
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
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
                            <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 24px;">No transactions recorded in audit ledger.</td></tr>
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

    </div>
</div>

<script>
    function switchResourceSection(section, btn) {
        // Toggle button active styling
        const buttons = document.querySelectorAll('#resourceTabGroup button');
        buttons.forEach(b => {
            b.classList.remove('btn-primary');
            b.classList.add('btn-outline');
        });
        btn.classList.remove('btn-outline');
        btn.classList.add('btn-primary');

        // Toggle sections
        const sections = document.querySelectorAll('.resource-section-block');
        sections.forEach(sec => {
            if (section === 'all') {
                sec.style.display = 'block';
            } else if (sec.id === 'frame-section-' + section) {
                sec.style.display = 'block';
            } else {
                sec.style.display = 'none';
            }
        });
    }

    // Auto-scroll or switch if URL has hash (e.g. #frame-section-inventory)
    window.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash;
        if (hash === '#inventory' || hash === '#frame-section-inventory') {
            const invBtn = document.querySelector("#resourceTabGroup button:nth-child(2)");
            if (invBtn) switchResourceSection('inventory', invBtn);
        } else if (hash === '#allocations' || hash === '#frame-section-allocations') {
            const allocBtn = document.querySelector("#resourceTabGroup button:nth-child(3)");
            if (allocBtn) switchResourceSection('allocations', allocBtn);
        } else if (hash === '#audit' || hash === '#frame-section-audit') {
            const auditBtn = document.querySelector("#resourceTabGroup button:nth-child(4)");
            if (auditBtn) switchResourceSection('audit', auditBtn);
        }
    });
</script>