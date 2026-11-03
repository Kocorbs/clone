<!-- views/recommendations/show.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary-dark);">
            Recommendation #<?= $rec['recommendation_id'] ?> — Explainability & Review
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">
            Disaster Request #<?= $rec['disaster_request_id'] ?> | Generated on <?= date('F d, Y h:i A', strtotime($rec['created_at'])) ?>
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=recommendations" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back to Recommendations</a>
    </div>
</div>

<div class="grid-cols-2">
    <!-- AI Decision Tree Explanation -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa fa-diagram-project"></i> Decision Tree Classification & Explainability</div>
            <?= View::statusBadge($rec['recommendation_status']) ?>
        </div>
        <div class="card-body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding: 12px; background: #f8fafc; border-radius: 6px; border: 1px solid var(--border);">
                <div>
                    <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Predicted Priority:</span>
                    <div style="margin-top: 4px;"><?= View::priorityBadge($rec['predicted_priority']) ?></div>
                </div>
                <div>
                    <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Confidence Score:</span>
                    <div style="font-size: 1.25rem; font-weight: 800; color: var(--primary-dark);"><?= number_format((float)$rec['confidence_score'], 1) ?>%</div>
                </div>
                <div>
                    <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Model Version:</span>
                    <div style="font-size: 0.85rem; font-family: monospace; font-weight: 600;"><?= htmlspecialchars($rec['model_version']) ?></div>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label">Classification Decision Path (Tree Conditions Traversed):</label>
                <div class="explainability-card">
                    <?php if (!empty($explanation['decision_path'])): ?>
                        <?php foreach ($explanation['decision_path'] as $step): ?>
                            <div class="decision-path-step">
                                <i class="fa fa-code-fork text-primary"></i> <?= htmlspecialchars($step) ?>
                            </div>
                        <?php endforeach; ?>
                        <div style="margin-top: 8px; font-weight: bold; color: var(--primary-dark);">
                            &rarr; Output Leaf Node: Priority = <?= htmlspecialchars($rec['predicted_priority']) ?>
                        </div>
                    <?php else: ?>
                        <p>No decision path metadata available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label">Validated Feature Inputs to Classifier:</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 0.85rem; background: #ffffff; padding: 10px; border: 1px solid var(--border); border-radius: 6px;">
                    <div><strong>Disaster Type:</strong> <?= htmlspecialchars($rec['disaster_type'] ?? 'Flood') ?></div>
                    <div><strong>Severity:</strong> <?= htmlspecialchars($rec['severity_level'] ?? 'High') ?></div>
                    <div><strong>Urgency Flag:</strong> <?= !empty($rec['is_urgent']) ? 'Urgent (1)' : 'Standard (0)' ?></div>
                    <div><strong>Purok Risk Zone:</strong> <?= htmlspecialchars($rec['purok_risk'] ?? 'Pending') ?> (<?= htmlspecialchars($rec['risk_zone_color'] ?? 'Pink') ?>)</div>
                    <div><strong>Affected Households:</strong> <?= (int)($rec['affected_households'] ?? 1) ?> HH</div>
                    <div><strong>Affected Population:</strong> <?= (int)($rec['affected_population'] ?? 1) ?> persons</div>
                </div>
            </div>

            <div>
                <label class="form-label">Natural Language Recommendation Explanation:</label>
                <div style="padding: 12px; background: #f0fdf4; border-left: 4px solid var(--success); font-size: 0.88rem; border-radius: 0 6px 6px 0;">
                    <?= htmlspecialchars($rec['recommendation_reason']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Demand Calculation & Official Review Action Panel -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa fa-clipboard-check"></i> Resource Need Calculation & Official Action</div>
        </div>
        <div class="card-body">
            <!-- Resource Need Breakdown -->
            <div style="padding: 14px; background: #f8fafc; border: 1px solid var(--border); border-radius: 6px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Target Supply Item:</span>
                    <strong><?= htmlspecialchars($rec['supply_name'] ?? 'Relief Goods') ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Remaining In Stock:</span>
                    <strong style="color: <?= (int)($rec['supply_remaining'] ?? 0) <= 0 ? 'var(--danger)' : 'var(--success)' ?>;">
                        <?= (int)($rec['supply_remaining'] ?? 0) ?> <?= htmlspecialchars($rec['unit_of_measure'] ?? 'units') ?>
                    </strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; padding-top: 6px; border-top: 1px dashed var(--border);">
                    <span style="font-weight: 700; color: var(--primary-dark);">Recommended Allocation:</span>
                    <strong style="font-size: 1.2rem; color: var(--primary);"><?= (int)$rec['recommended_quantity'] ?> <?= htmlspecialchars($rec['unit_of_measure'] ?? 'units') ?></strong>
                </div>
            </div>

            <?php if ($rec['recommendation_status'] === 'Approved'): ?>
                <div class="alert alert-success">
                    <i class="fa fa-circle-check"></i>
                    <div>
                        <strong>Approved & Released:</strong> This recommendation was approved by 
                        <?= htmlspecialchars($rec['reviewer_first'] . ' ' . $rec['reviewer_last']) ?> on <?= date('M d, Y h:i A', strtotime($rec['reviewed_at'])) ?>.<br>
                        <em>Justification:</em> <?= htmlspecialchars($rec['justification'] ?? 'None recorded.') ?>
                    </div>
                </div>
            <?php elseif ($rec['recommendation_status'] === 'Rejected'): ?>
                <div class="alert alert-danger">
                    <i class="fa fa-circle-xmark"></i>
                    <div>
                        <strong>Declined:</strong> Rejected by <?= htmlspecialchars($rec['reviewer_first'] . ' ' . $rec['reviewer_last']) ?>.<br>
                        <em>Reason:</em> <?= htmlspecialchars($rec['justification'] ?? 'None recorded.') ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Official Review Form -->
                <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head'])): ?>
                    <form action="index.php?route=recommendations-review" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="recommendation_id" value="<?= $rec['recommendation_id'] ?>">

                        <div class="form-group">
                            <label class="form-label">Official Review Decision *</label>
                            <select name="action" id="review_action" class="form-control" required>
                                <option value="approve">Approve Recommendation (Deduct from Inventory)</option>
                                <option value="modify">Modify Allocation Quantity</option>
                                <option value="reject">Reject / Decline Recommendation</option>
                            </select>
                        </div>

                        <div class="form-group" id="modified_qty_group" style="display: none;">
                            <label class="form-label">Adjusted Quantity to Allocate:</label>
                            <input type="number" name="modified_quantity" class="form-control" min="1" max="<?= (int)$rec['supply_remaining'] ?>" value="<?= (int)$rec['recommended_quantity'] ?>">
                            <small style="color: var(--text-muted);">Cannot exceed remaining inventory (<?= (int)$rec['supply_remaining'] ?> units).</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Official Written Justification *</label>
                            <textarea name="justification" class="form-control" rows="3" placeholder="Provide operational reasoning, ground verification notes, or ICDRRMC authorization remarks..." required></textarea>
                            <small style="color: var(--text-muted); font-size: 0.75rem;">Required by Chapter 1 thesis specifications for auditability.</small>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px;">
                            <i class="fa fa-stamp"></i> Execute Official Review Decision
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fa fa-lock"></i> Only the Barangay Head or authorized ICDRRMO Personnel can approve and allocate inventory resources.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
