<!-- views/inventory/index.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--primary-dark);">Barangay Hinaplanon Centralized Inventory</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">Real-time monitoring of relief goods, medical supplies, and rescue equipment.</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=inventory-export" class="btn btn-outline"><i class="fa fa-file-csv"></i> Export CSV</a>
        <?php if ($currentRole === 'ICDRRMO'): ?>
            <a href="index.php?route=inventory-create" class="btn btn-primary"><i class="fa fa-plus"></i> Add Supply Item</a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($lowStock)): ?>
    <div class="alert alert-danger" style="margin-bottom: 20px;">
        <i class="fa fa-triangle-exclamation"></i>
        <div>
            <strong>Low Inventory Warning:</strong> <?= count($lowStock) ?> items are running low or depleted. Coordinate immediate replenishment with ICDRRMC.
        </div>
    </div>
<?php endif; ?>

<div class="grid-cols-4" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-label">Total Registered Items</div>
        <div class="stat-value"><?= (int)$summary['total_items'] ?></div>
        <div class="stat-sub">Across 6 disaster categories</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Initial Stock</div>
        <div class="stat-value"><?= number_format((int)$summary['total_quantity']) ?></div>
        <div class="stat-sub">Recorded baseline capacity</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Current Available Remaining</div>
        <div class="stat-value" style="color: var(--success);"><?= number_format((int)$summary['total_remaining']) ?></div>
        <div class="stat-sub">Available for immediate allocation</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Released / Distributed</div>
        <div class="stat-value" style="color: var(--primary);"><?= number_format((int)$summary['total_released']) ?></div>
        <div class="stat-sub">Audited disaster releases</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-warehouse"></i> Disaster Preparedness Supplies & Equipment</div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Category</th>
                    <th>Supply Name</th>
                    <th>Total Stock</th>
                    <th>Remaining Stock</th>
                    <th>Total Released</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head'])): ?>
                        <th>Replenish</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
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
                                    <input type="number" name="replenish_quantity" class="form-control" style="width: 80px; padding: 4px 6px; font-size: 0.8rem;" min="1" placeholder="+Qty" required>
                                    <button type="submit" class="btn btn-primary btn-sm" title="Replenish stock">
                                        <i class="fa fa-arrow-up"></i>
                                    </button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
