<!-- views/recommendations/show.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
                Resource Allocation Recommendation #<?= (int)$rec['recommendation_id'] ?>
            </h2>
            <?= View::statusBadge($rec['recommendation_status']) ?>
        </div>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
            Targeted Incident: <a href="index.php?route=disaster-requests-show&id=<?= (int)$rec['disaster_request_id'] ?>" style="font-weight: 700; color: var(--primary);">Incident #<?= (int)$rec['disaster_request_id'] ?></a> | Generated on <?= date('F d, Y h:i A', strtotime($rec['created_at'])) ?>
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=recommendations" class="btn btn-outline">
            <i class="fa fa-arrow-left"></i> All Recommendations
        </a>
    </div>
</div>

<!-- =================================================================== -->
<!-- HUMAN-CENTERED AI: 5 ESSENTIAL DISASTER DECISION QUESTIONS (PHASE 14) -->
<!-- =================================================================== -->
<div class="card" style="margin-bottom: 24px; border-left: 5px solid var(--primary);">
    <div class="card-header" style="background: #f8fafc;">
        <div class="card-title" style="color: var(--primary-dark); font-size: 1.05rem;">
            <i class="fa fa-circle-question text-primary"></i> Human-Centered Decision Brief: 5 Key Questions for Barangay Officials
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 0.78rem; color: var(--text-muted);">AI Confidence:</span>
            <strong style="font-size: 1.1rem; color: var(--primary);"><?= number_format((float)$rec['confidence_score'], 1) ?>%</strong>
        </div>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
            
            <!-- Question 1: Which purok needs help? -->
            <div style="padding: 16px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                    1. Which Purok Needs Help?
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">
                    <?= htmlspecialchars($rec['purok_name']) ?>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <?= View::riskBadge($rec['purok_risk'] ?? 'Pending Verification') ?>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Zone Color: <strong><?= htmlspecialchars($rec['risk_zone_color'] ?? 'Pink') ?></strong></span>
                </div>
            </div>

            <!-- Question 2: Why does it need help? -->
            <div style="padding: 16px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                    2. Why Does It Need Help?
                </div>
                <div style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                    <?= htmlspecialchars($rec['disaster_type'] ?? 'Flood') ?> — <?= htmlspecialchars($rec['severity_level'] ?? 'High') ?> Severity
                </div>
                <div style="font-size: 0.82rem; color: #475569;">
                    <strong><?= (int)($rec['affected_households'] ?? 1) ?> Families</strong> (<?= (int)($rec['affected_population'] ?? 1) ?> people) affected.
                    <?php if (!empty($rec['is_urgent'])): ?>
                        <span class="badge badge-urgent" style="font-size: 0.7rem; margin-left: 4px;">URGENT</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Question 3: What supplies are needed? -->
            <div style="padding: 16px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                    3. What Supplies Are Needed?
                </div>
                <div style="font-size: 1.2rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 4px;">
                    <?= (int)$rec['recommended_quantity'] ?> <?= htmlspecialchars($rec['unit_of_measure'] ?? 'units') ?>
                </div>
                <div style="font-size: 0.85rem; color: #334155;">
                    <?= htmlspecialchars($rec['supply_name'] ?? 'Relief Goods') ?> <small style="color: var(--text-muted);">(<?= htmlspecialchars($rec['supply_type'] ?? 'General') ?>)</small>
                </div>
            </div>

            <!-- Question 4: How much inventory is available? -->
            <div style="padding: 16px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                    4. How Much Inventory Is Available?
                </div>
                <div style="font-size: 1.2rem; font-weight: 800; color: <?= (int)($rec['supply_remaining'] ?? 0) >= (int)$rec['recommended_quantity'] ? 'var(--success)' : 'var(--danger)' ?>; margin-bottom: 4px;">
                    <?= (int)($rec['supply_remaining'] ?? 0) ?> <?= htmlspecialchars($rec['unit_of_measure'] ?? 'units') ?> in stock
                </div>
                <div style="font-size: 0.82rem; color: #475569;">
                    <?php if ((int)($rec['supply_remaining'] ?? 0) >= (int)$rec['recommended_quantity']): ?>
                        <span style="color: var(--success); font-weight: 600;"><i class="fa fa-circle-check"></i> Sufficient stock to fulfill full allocation.</span>
                    <?php else: ?>
                        <span style="color: var(--danger); font-weight: 600;"><i class="fa fa-triangle-exclamation"></i> Insufficient inventory! Stock replenishment required.</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Question 5: What action should the official take? -->
            <div style="padding: 16px; background: #f0f8f7; border: 1px solid #b6e1db; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); grid-column: 1 / -1;">
                <div style="font-size: 0.75rem; font-weight: 800; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                    5. What Action Should the Official Take?
                </div>
                <div style="font-size: 0.95rem; font-weight: 600; color: #00665B; line-height: 1.45;">
                    <?= htmlspecialchars($rec['recommendation_reason']) ?>
                </div>
                <div style="margin-top: 8px; font-size: 0.82rem; color: #1B7E74;">
                    Assigned Priority: <strong><?= View::priorityBadge($rec['predicted_priority']) ?></strong> — Review and authorize allocation in the Action Panel below.
                </div>
            </div>

        </div>
    </div>
</div>

<div class="grid-cols-2">
    <!-- Official Review & Execution Action Panel -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa fa-stamp text-primary"></i> Official Review & Authorization Panel</div>
        </div>
        <div class="card-body">
            <?php if ($rec['recommendation_status'] === 'Approved'): ?>
                <div class="alert alert-success" style="border-left: 4px solid var(--success);">
                    <i class="fa fa-circle-check"></i>
                    <div>
                        <strong>Approved & Stock Deducted:</strong> Authorized by 
                        <strong><?= htmlspecialchars(($rec['reviewer_first'] ?? '') . ' ' . ($rec['reviewer_last'] ?? '')) ?></strong> on <?= date('M d, Y h:i A', strtotime($rec['reviewed_at'])) ?>.<br>
                        <div style="margin-top: 6px; font-size: 0.85rem; color: #14532d;">
                            <em>Justification:</em> <?= htmlspecialchars($rec['justification'] ?? 'No justification recorded.') ?>
                        </div>
                    </div>
                </div>
            <?php elseif ($rec['recommendation_status'] === 'Rejected'): ?>
                <div class="alert alert-danger" style="border-left: 4px solid var(--danger);">
                    <i class="fa fa-circle-xmark"></i>
                    <div>
                        <strong>Declined / Rejected:</strong> Rejected by 
                        <strong><?= htmlspecialchars(($rec['reviewer_first'] ?? '') . ' ' . ($rec['reviewer_last'] ?? '')) ?></strong> on <?= date('M d, Y h:i A', strtotime($rec['reviewed_at'])) ?>.<br>
                        <div style="margin-top: 6px; font-size: 0.85rem; color: #7f1d1d;">
                            <em>Reason:</em> <?= htmlspecialchars($rec['justification'] ?? 'No justification recorded.') ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Official Review Form for Barangay Head and ICDRRMO -->
                <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head'])): ?>
                    <form action="index.php?route=recommendations-review" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="recommendation_id" value="<?= (int)$rec['recommendation_id'] ?>">

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label class="form-label">Review Action Decision *</label>
                            <select name="action" id="review_action" class="form-control" required style="font-size: 0.95rem; font-weight: 600;">
                                <option value="approve">Approve Recommendation (Deduct <?= (int)$rec['recommended_quantity'] ?> <?= htmlspecialchars($rec['unit_of_measure'] ?? 'units') ?> from stock)</option>
                                <option value="modify">Modify Allocation Quantity</option>
                                <option value="reject">Reject / Decline Recommendation</option>
                            </select>
                        </div>

                        <div class="form-group" id="modified_qty_group" style="display: none; margin-bottom: 16px;">
                            <label class="form-label">Adjusted Quantity to Release:</label>
                            <input type="number" name="modified_quantity" class="form-control" min="1" max="<?= (int)($rec['supply_remaining'] ?? 0) ?>" value="<?= (int)$rec['recommended_quantity'] ?>">
                            <small style="color: var(--text-muted);">Must be &le; remaining stock (<?= (int)($rec['supply_remaining'] ?? 0) ?> <?= htmlspecialchars($rec['unit_of_measure'] ?? 'units') ?>).</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label class="form-label">Official Written Justification *</label>
                            <textarea name="justification" class="form-control" rows="3" placeholder="Provide ground assessment observations, BDRRMC command directives, or ICDRRMC clearance notes..." required></textarea>
                            <small style="color: var(--text-muted); font-size: 0.75rem;">Required by thesis operational specifications for auditing and traceability.</small>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 0.95rem;">
                            <i class="fa fa-stamp"></i> Execute Official Review Decision
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fa fa-lock"></i> Only the Barangay Head or ICDRRMO Personnel have authorization to approve and release relief resources.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Supply & Purok Snapshot Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa fa-boxes-stacked text-primary"></i> Inventory & Purok Allocation Context</div>
        </div>
        <div class="card-body">
            <div style="padding: 14px; background: #f8fafc; border: 1px solid var(--border); border-radius: 6px; margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Supply Item:</span>
                    <strong><?= htmlspecialchars($rec['supply_name'] ?? 'Relief Goods') ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Category:</span>
                    <span><?= htmlspecialchars($rec['supply_type'] ?? 'General') ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Current In-Stock:</span>
                    <strong><?= (int)($rec['supply_remaining'] ?? 0) ?> <?= htmlspecialchars($rec['unit_of_measure'] ?? 'units') ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; padding-top: 8px; border-top: 1px dashed var(--border);">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Projected Remaining After Approval:</span>
                    <strong style="color: <?= ((int)($rec['supply_remaining'] ?? 0) - (int)$rec['recommended_quantity']) < 0 ? 'var(--danger)' : 'var(--primary-dark)' ?>;">
                        <?= max(0, (int)($rec['supply_remaining'] ?? 0) - (int)$rec['recommended_quantity']) ?> <?= htmlspecialchars($rec['unit_of_measure'] ?? 'units') ?>
                    </strong>
                </div>
            </div>

            <div style="font-size: 0.85rem; color: #475569; line-height: 1.5;">
                <p><strong>Thesis Formulation (Chapter 1):</strong> In accordance with standard disaster logistics, priority allocation formulas multiply affected household units by priority coefficients (Urgent: 1.10x, High: 1.00x, Moderate: 0.75x, Low: 0.50x).</p>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- TECHNICAL EXPANDABLE ACCORDION: DECISION TREE & CART METRICS -->
<!-- ============================================================== -->
<details class="technical-accordion" style="margin-top: 24px; background: #ffffff; border: 1px solid var(--border); border-radius: 6px; padding: 16px;">
    <summary style="font-weight: 700; color: var(--primary-dark); cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
        <span><i class="fa fa-code-branch text-primary"></i> Technical Machine Learning Details: CART Decision Tree & Gini Impurity (For Academic Defense)</span>
        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal;">Click to expand / collapse</span>
    </summary>
    
    <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);">
        <div class="grid-cols-2" style="margin-bottom: 16px;">
            <div>
                <label class="form-label">Classification Model Metadata:</label>
                <div style="padding: 10px; background: #f8fafc; border: 1px solid var(--border); border-radius: 4px; font-family: monospace; font-size: 0.8rem;">
                    <div>Algorithm: CART (Classification and Regression Trees)</div>
                    <div>Impurity Metric: Gini Impurity</div>
                    <div>Model Version: <?= htmlspecialchars($rec['model_version'] ?? 'CART-GINI-v1.0') ?></div>
                    <div>Confidence Metric: Leaf Class Frequency Distribution (<?= number_format((float)$rec['confidence_score'], 1) ?>%)</div>
                </div>
            </div>

            <div>
                <label class="form-label">Feature Input Vector:</label>
                <div style="padding: 10px; background: #f8fafc; border: 1px solid var(--border); border-radius: 4px; font-family: monospace; font-size: 0.8rem;">
                    <div>purok_risk: <?= htmlspecialchars($rec['purok_risk'] ?? 'High') ?></div>
                    <div>disaster_type: <?= htmlspecialchars($rec['disaster_type'] ?? 'Flood') ?></div>
                    <div>severity_level: <?= htmlspecialchars($rec['severity_level'] ?? 'High') ?></div>
                    <div>is_urgent: <?= !empty($rec['is_urgent']) ? 1 : 0 ?></div>
                    <div>affected_households: <?= (int)($rec['affected_households'] ?? 1) ?></div>
                </div>
            </div>
        </div>

        <div>
            <label class="form-label">Decision Path Traversal (Rule Sequence from Root to Leaf):</label>
            <div class="explainability-card">
                <?php if (!empty($explanation['decision_path'])): ?>
                    <?php foreach ($explanation['decision_path'] as $step): ?>
                        <div class="decision-path-step">
                            <i class="fa fa-code-fork text-primary"></i> <?= htmlspecialchars($step) ?>
                        </div>
                    <?php endforeach; ?>
                    <div style="margin-top: 8px; font-weight: bold; color: var(--primary-dark);">
                        &rarr; Predicted Class: <?= htmlspecialchars($rec['predicted_priority']) ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">Decision path metadata preserved in trained decision tree artifacts.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</details>
