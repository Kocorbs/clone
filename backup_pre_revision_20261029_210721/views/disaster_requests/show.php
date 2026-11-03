<!-- views/disaster_requests/show.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--primary-dark);">
            Disaster Request #<?= $request['disaster_request_id'] ?> — <?= htmlspecialchars($request['purok_name']) ?>
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">
            Reported by <?= htmlspecialchars($request['requester_first'] . ' ' . $request['requester_last']) ?> (<?= htmlspecialchars($request['user_type']) ?>) on <?= date('F d, Y', strtotime($request['disaster_date'])) ?>
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=disaster-requests" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back to Requests</a>
    </div>
</div>

<div class="grid-cols-2">
    <!-- Incident Details Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa fa-circle-info"></i> Incident Parameters</div>
            <?= View::statusBadge($request['status']) ?>
        </div>
        <div class="card-body">
            <div style="margin-bottom: 12px;">
                <strong>Affected Purok:</strong> <?= htmlspecialchars($request['purok_name']) ?> (Barangay <?= htmlspecialchars($request['barangay_name']) ?>)
                <div style="margin-top: 4px;"><?= View::riskBadge($request['purok_risk'] ?? 'Pending Verification') ?></div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div><strong>Disaster Type:</strong> <?= htmlspecialchars($request['disaster_type']) ?></div>
                <div><strong>Weather:</strong> <?= htmlspecialchars($request['weather_name']) ?></div>
                <div><strong>Severity:</strong> <?= htmlspecialchars($request['severity_level']) ?></div>
                <div><strong>Urgency:</strong> <?= $request['is_urgent'] ? '<span class="badge badge-urgent">URGENT</span>' : '<span class="badge badge-secondary">Standard</span>' ?></div>
                <div><strong>Affected Households:</strong> <?= (int)$request['affected_households'] ?> HH</div>
                <div><strong>Affected Population:</strong> <?= (int)$request['affected_population'] ?> individuals</div>
            </div>

            <div style="margin-bottom: 12px;">
                <strong>Description / Observations:</strong>
                <div style="background-color: #f8fafc; border-left: 3px solid var(--border); padding: 8px 12px; margin-top: 4px; font-size: 0.9rem;">
                    <?= nl2br(htmlspecialchars($request['description'])) ?>
                </div>
            </div>

            <?php if (!empty($request['latitude']) && !empty($request['longitude'])): ?>
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                    <i class="fa fa-location-crosshairs"></i> Geotag Coordinates: <?= $request['latitude'] ?>, <?= $request['longitude'] ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- AI Recommendation Engine Action Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa fa-microchip"></i> Decision Tree Recommendation Engine</div>
        </div>
        <div class="card-body">
            <?php if ($recommendation): ?>
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i>
                    <div>An intelligent resource allocation recommendation has been generated for this request.</div>
                </div>

                <div style="padding: 14px; background: #f8fafc; border: 1px solid var(--border); border-radius: 6px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">PREDICTED PRIORITY:</span>
                        <?= View::priorityBadge($recommendation['predicted_priority']) ?>
                    </div>
                    <div style="font-size: 1.1rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 6px;">
                        Recommended Allocation: <?= (int)$recommendation['recommended_quantity'] ?> units
                    </div>
                    <div style="font-size: 0.82rem; color: var(--text-muted);">
                        Confidence: <?= number_format((float)$recommendation['confidence_score'], 1) ?>% | Model: <?= htmlspecialchars($recommendation['model_version']) ?>
                    </div>
                    <div style="margin-top: 8px; font-size: 0.85rem;">
                        Status: <?= View::statusBadge($recommendation['recommendation_status']) ?>
                    </div>
                </div>

                <a href="index.php?route=recommendations-show&id=<?= $recommendation['recommendation_id'] ?>" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fa fa-arrow-right"></i> View Full Explainability & Official Review
                </a>
            <?php else: ?>
                <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 16px;">
                    Run the supervised Decision Tree classification pipeline to predict allocation priority based on verified Purok risk, disaster severity, urgency, and affected population.
                </p>

                <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head'])): ?>
                    <form action="index.php?route=disaster-requests-recommend" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="disaster_request_id" value="<?= $request['disaster_request_id'] ?>">

                        <div class="form-group">
                            <label class="form-label">Target Supply Resource Category:</label>
                            <select name="supply_id" class="form-control">
                                <?php foreach ($supplies as $s): ?>
                                    <option value="<?= $s['supply_id'] ?>">
                                        <?= htmlspecialchars($s['supply_name']) ?> (<?= htmlspecialchars($s['supply_type']) ?> — Avail: <?= (int)$s['supply_remaining'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center; padding: 10px;">
                            <i class="fa fa-network-wired"></i> Run Decision Tree & Generate Recommendation
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">
                        Awaiting review and recommendation generation by authorized Barangay Head or ICDRRMO personnel.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
