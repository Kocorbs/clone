<!-- views/recommendations/index.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--primary-dark);">Intelligent Resource Allocation Recommendations</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">Explainable Decision Tree prioritization and documented demand calculations.</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=reports-export-allocations" class="btn btn-outline"><i class="fa fa-file-csv"></i> Export Allocations</a>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 10px;">
        <div class="card-title"><i class="fa fa-microchip"></i> System Generated Recommendations</div>
        <div style="display: flex; gap: 8px;">
            <a href="index.php?route=recommendations" class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline' ?>">All</a>
            <a href="index.php?route=recommendations&status=Generated" class="btn btn-sm <?= $statusFilter === 'Generated' ? 'btn-primary' : 'btn-outline' ?>">Awaiting Review</a>
            <a href="index.php?route=recommendations&status=Approved" class="btn btn-sm <?= $statusFilter === 'Approved' ? 'btn-primary' : 'btn-outline' ?>">Approved</a>
            <a href="index.php?route=recommendations&status=Modified" class="btn btn-sm <?= $statusFilter === 'Modified' ? 'btn-primary' : 'btn-outline' ?>">Modified</a>
            <a href="index.php?route=recommendations&status=Rejected" class="btn btn-sm <?= $statusFilter === 'Rejected' ? 'btn-primary' : 'btn-outline' ?>">Rejected</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Rec #</th>
                    <th>Purok & Risk</th>
                    <th>Predicted Priority</th>
                    <th>Recommended Resource</th>
                    <th>Recommended Qty</th>
                    <th>Confidence</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recommendations)): ?>
                    <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">No recommendations found matching the filter.</td></tr>
                <?php else: ?>
                    <?php foreach ($recommendations as $rec): ?>
                        <tr>
                            <td><strong>#<?= $rec['recommendation_id'] ?></strong></td>
                            <td>
                                <div><strong><?= htmlspecialchars($rec['purok_name']) ?></strong></div>
                                <?= View::riskBadge($rec['purok_risk'] ?? 'Pending Verification') ?>
                            </td>
                            <td><?= View::priorityBadge($rec['predicted_priority']) ?></td>
                            <td>
                                <div><?= htmlspecialchars($rec['supply_name'] ?? 'Relief Supplies') ?></div>
                                <small style="color: var(--text-muted);"><?= htmlspecialchars($rec['supply_type'] ?? 'General') ?></small>
                            </td>
                            <td>
                                <strong style="font-size: 1rem; color: var(--primary);"><?= (int)$rec['recommended_quantity'] ?></strong>
                                <small><?= htmlspecialchars($rec['unit_of_measure'] ?? 'units') ?></small>
                            </td>
                            <td>
                                <strong><?= number_format((float)$rec['confidence_score'], 1) ?>%</strong>
                            </td>
                            <td><?= View::statusBadge($rec['recommendation_status']) ?></td>
                            <td>
                                <a href="index.php?route=recommendations-show&id=<?= $rec['recommendation_id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fa fa-magnifying-glass"></i> Review
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
