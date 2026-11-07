<!-- views/recommendations/purok_show.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <span style="background: #1e293b; color: #fff; padding: 3px 10px; border-radius: 6px; font-weight: 800; font-size: 0.9rem;">
                Rank #<?= (int)$assessment['priority_rank'] ?>
            </span>
            <h2 style="font-size: 1.45rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
                <?= htmlspecialchars($assessment['purok_name']) ?> — Relief Goods Allocation Dossier
            </h2>
            <?php 
                $priority = $assessment['priority_level'];
                $badgeStyle = match($priority) {
                    'Critical' => 'background: #fee2e2; color: #991b1b; border: 1px solid #f87171;',
                    'High'     => 'background: #ffedd5; color: #9a3412; border: 1px solid #fb923c;',
                    'Moderate' => 'background: #fef9c3; color: #854d0e; border: 1px solid #facc15;',
                    default    => 'background: #dcfce7; color: #166534; border: 1px solid #86efac;'
                };
            ?>
            <span style="padding: 4px 10px; border-radius: 4px; font-weight: 800; font-size: 0.85rem; text-transform: uppercase; <?= $badgeStyle ?>">
                <?= htmlspecialchars($priority) ?> Priority
            </span>
            <?= View::statusBadge($assessment['allocation_status'] ?? 'Recommended') ?>
        </div>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
            Composite Risk Score: <strong><?= number_format((float)$assessment['composite_risk_score'], 1) ?>/100</strong> | Method: <?= htmlspecialchars($assessment['assessment_method']) ?> [<?= htmlspecialchars($assessment['model_version']) ?>]
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=recommendations" class="btn btn-outline">
            <i class="fa fa-arrow-left"></i> Back to Rankings
        </a>
        <a href="index.php?route=hazard-map" class="btn btn-outline">
            <i class="fa fa-map-location-dot"></i> View on Map
        </a>
    </div>
</div>

<!-- =================================================================== -->
<!-- 1. HUMAN-CENTERED AI: 5 ESSENTIAL DISASTER DECISION QUESTIONS       -->
<!-- =================================================================== -->
<div class="card" style="margin-bottom: 24px; border-left: 5px solid var(--primary);">
    <div class="card-header" style="background: #f8fafc;">
        <div class="card-title" style="color: var(--primary-dark); font-size: 1.05rem;">
            <i class="fa fa-circle-question text-primary"></i> Human-Centered Decision Brief: 5 Key Questions for Barangay & ICDRRMO Officials
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 0.78rem; color: var(--text-muted);">AI Confidence:</span>
            <strong style="font-size: 1.1rem; color: var(--primary);"><?= number_format((float)$assessment['confidence_score'], 1) ?>%</strong>
        </div>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
            
            <!-- Question 1: Which purok needs help? -->
            <div style="padding: 16px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                    1. Which Purok Needs Help?
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">
                    <?= htmlspecialchars($assessment['purok_name']) ?>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <?= View::riskBadge($assessment['risk_classification'] ?? 'Pending Verification') ?>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Zone: <strong><?= htmlspecialchars($assessment['risk_zone_color'] ?? 'Pink') ?></strong></span>
                </div>
                <div style="font-size: 0.78rem; color: #475569;">
                    <strong>Designated Evacuation Haven:</strong> <?= htmlspecialchars($assessment['evacuation_center'] ?? 'Hinaplanon Central School') ?>
                </div>
            </div>

            <!-- Question 2: Why does it need help? -->
            <div style="padding: 16px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                    2. Why Does It Need Help?
                </div>
                <div style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin-bottom: 4px;">
                    Flood Hazard Exposure: <?= htmlspecialchars($assessment['flood_hazard_exposure'] ?? 'Moderate') ?>
                </div>
                <div style="font-size: 0.82rem; color: #475569; line-height: 1.4;">
                    <strong><?= number_format((int)$assessment['affected_households']) ?> Families</strong> (<?= number_format((int)$assessment['affected_population']) ?> residents).
                    <?php if ((int)$assessment['totally_damaged_houses'] > 0): ?>
                        <div style="color: #dc2626; font-weight: 700; margin-top: 2px;">
                            <i class="fa fa-triangle-exclamation"></i> <?= (int)$assessment['totally_damaged_houses'] ?> Totally Destroyed Homes
                        </div>
                    <?php endif; ?>
                    <div style="color: #475569; font-size: 0.75rem; margin-top: 2px;">
                        Vulnerable members: <?= (int)$assessment['vulnerable_members'] ?> (Seniors/PWDs)
                    </div>
                </div>
            </div>

            <!-- Question 3: What supplies are needed? -->
            <div style="padding: 16px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                    3. What Supplies Are Needed?
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: #16a34a; margin-bottom: 4px;">
                    <?= number_format((int)$assessment['recommended_rice_kg']) ?> kg Rice
                </div>
                <div style="font-size: 0.82rem; color: #334155; line-height: 1.35;">
                    <div>• <?= number_format((int)$assessment['recommended_canned_tins']) ?> Canned Goods (4 tins/HH)</div>
                    <div>• <?= number_format((int)$assessment['recommended_water_bottles']) ?> Potable Water Units</div>
                    <div>• <?= number_format((int)$assessment['recommended_hygiene_kits']) ?> Family Hygiene Kits</div>
                    <div>• <?= number_format((int)$assessment['recommended_blankets']) ?> Emergency Blankets</div>
                </div>
            </div>

            <!-- Question 4: How much inventory is available? -->
            <div style="padding: 16px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                    4. How Much Inventory Is Available?
                </div>
                <?php if (!empty($assessment['stock_capped'])): ?>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #dc2626; margin-bottom: 4px;">
                        <i class="fa fa-triangle-exclamation"></i> Limited Stock
                    </div>
                    <div style="font-size: 0.78rem; color: #991b1b; line-height: 1.3;">
                        <?= htmlspecialchars($assessment['shortage_notice'] ?? 'Allocations capped to current available inventory.') ?>
                    </div>
                <?php else: ?>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #16a34a; margin-bottom: 4px;">
                        <i class="fa fa-circle-check"></i> Stock Sufficient
                    </div>
                    <div style="font-size: 0.78rem; color: #15803d; line-height: 1.3;">
                        Sufficient centralized inventory available in Barangay storage to fulfill full proposed distribution.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Question 5: What action should the official take? -->
            <div style="padding: 16px; background: #f0f8f7; border: 1px solid #b6e1db; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); grid-column: 1 / -1;">
                <div style="font-size: 0.75rem; font-weight: 800; color: #1e40af; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                    5. What Action Should the Official Take?
                </div>
                <div style="font-size: 0.9rem; color: #00665B; line-height: 1.4;">
                    Official status is currently <strong><?= htmlspecialchars($assessment['allocation_status'] ?? 'Recommended') ?></strong>.
                    <?php if (in_array($currentRole, ['Barangay Head', 'ICDRRMO'])): ?>
                        Authorized officials may <strong>Approve</strong> the allocation as recommended, <strong>Modify</strong> specific supply quantities based on ground realities, or <strong>Reject / Return</strong> with written justification.
                    <?php else: ?>
                        Available for situational awareness. Final approval requires authorization by the Barangay Head or ICDRRMO.
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- 2. EXPLAINABLE AI REASONING & FACTOR ATTRIBUTION BREAKDOWN          -->
<!-- =================================================================== -->
<div class="grid-cols-2" style="margin-bottom: 24px;">
    <!-- Narrative Box -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa fa-brain text-primary"></i> Explainable AI Narrative Rationale
            </div>
        </div>
        <div class="card-body">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid var(--primary); padding: 16px; border-radius: 6px; margin-bottom: 16px;">
                <p style="margin: 0; font-size: 0.95rem; color: #0f172a; line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($assessment['explanation_narrative'])) ?>
                </p>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">
                <div><strong>Data Traceability:</strong> Derived from official Barangay Hinaplanon BDRRM Plan 2024–2026 Table 18/26, TS Basyang damage census, and verified ground field status reports.</div>
                <div style="margin-top: 4px;"><strong>Data Completeness:</strong> <?= htmlspecialchars($assessment['data_completeness'] ?? 'High (Verified)') ?></div>
            </div>
        </div>
    </div>

    <!-- Contributing Factors Bar Chart -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa fa-chart-pie text-primary"></i> Quantitative Risk Factor Contributions
            </div>
        </div>
        <div class="card-body">
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php if (!empty($breakdown) && is_array($breakdown)): ?>
                    <?php foreach ($breakdown as $factor): ?>
                        <div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 3px;">
                                <strong style="color: #1e293b;"><?= htmlspecialchars($factor['factor']) ?></strong>
                                <span style="font-weight: 700; color: #0f172a;"><?= number_format((float)$factor['contribution_pct'], 1) ?>%</span>
                            </div>
                            <div style="background: #e2e8f0; border-radius: 4px; height: 8px; overflow: hidden; margin-bottom: 3px;">
                                <div style="background: <?= (float)$factor['contribution_pct'] >= 25 ? '#dc2626' : ((float)$factor['contribution_pct'] >= 18 ? '#ea580c' : '#1B7E74') ?>; width: <?= min(100, (float)$factor['contribution_pct'] * 3.5) ?>%; height: 100%;"></div>
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= htmlspecialchars($factor['description']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">Standard multi-factor attribution active.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- 3. OFFICIAL ADMINISTRATIVE REVIEW FORM (BARANGAY HEAD & ICDRRMO)    -->
<!-- =================================================================== -->
<?php if (in_array($currentRole, ['Barangay Head', 'ICDRRMO'])): ?>
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa fa-signature text-primary"></i> Official Allocation Review & Approval Console
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">
                Role: <strong><?= htmlspecialchars($currentRole) ?></strong> (Authorized Human Decision-Maker)
            </div>
        </div>
        <div class="card-body">
            <form action="index.php?route=recommendations-purok-review" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="assessment_id" value="<?= (int)$assessment['assessment_id'] ?>">

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="font-weight: 700; font-size: 0.85rem; display: block; margin-bottom: 6px;">Select Review Action:</label>
                        <select name="action" id="reviewActionSelect" class="form-control" onchange="toggleModifyFields(this.value)" required>
                            <option value="approve">Approve Allocation Recommendation</option>
                            <option value="modify">Modify Supply Quantities</option>
                            <option value="reject">Reject / Return with Justification</option>
                        </select>
                    </div>

                    <div>
                        <label style="font-weight: 700; font-size: 0.85rem; display: block; margin-bottom: 6px;">Reviewer Identity:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['user']['firstname'] ?? 'Official') ?> <?= htmlspecialchars($_SESSION['user']['lastname'] ?? '') ?> (<?= htmlspecialchars($currentRole) ?>)" disabled style="background: #f1f5f9;">
                    </div>
                </div>

                <!-- Optional Modification Fields (Visible when Action is Modify) -->
                <div id="modifyFieldsContainer" style="display: none; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: var(--primary-dark); margin-bottom: 10px;">
                        <i class="fa fa-sliders text-primary"></i> Adjust Supply Quantities:
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 600;">Rice (kg):</label>
                            <input type="number" name="modified_rice_kg" class="form-control" value="<?= (int)$assessment['recommended_rice_kg'] ?>" min="0">
                        </div>
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 600;">Canned Goods (tins):</label>
                            <input type="number" name="modified_canned_tins" class="form-control" value="<?= (int)$assessment['recommended_canned_tins'] ?>" min="0">
                        </div>
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 600;">Water (units):</label>
                            <input type="number" name="modified_water_bottles" class="form-control" value="<?= (int)$assessment['recommended_water_bottles'] ?>" min="0">
                        </div>
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 600;">Hygiene Kits (packs):</label>
                            <input type="number" name="modified_hygiene_kits" class="form-control" value="<?= (int)$assessment['recommended_hygiene_kits'] ?>" min="0">
                        </div>
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 600;">Blankets (units):</label>
                            <input type="number" name="modified_blankets" class="form-control" value="<?= (int)$assessment['recommended_blankets'] ?>" min="0">
                        </div>
                    </div>
                </div>

                <!-- Written Justification (Mandatory) -->
                <div style="margin-bottom: 16px;">
                    <label style="font-weight: 700; font-size: 0.85rem; display: block; margin-bottom: 6px;">
                        Written Review Justification & Administrative Decision Notes: <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea name="justification" class="form-control" rows="3" placeholder="Provide clear written justification explaining why this allocation is approved, modified, or rejected based on ground observations..." required><?= htmlspecialchars($assessment['admin_justification'] ?? '') ?></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                    <a href="index.php?route=recommendations" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check-double"></i> Submit Official Decision
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
    function toggleModifyFields(action) {
        const container = document.getElementById('modifyFieldsContainer');
        if (action === 'modify') {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }
</script>

