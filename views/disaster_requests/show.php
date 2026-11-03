<!-- views/disaster_requests/show.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <h2 style="font-size: 1.45rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
                Incident <?= htmlspecialchars($request['reference_no'] ?? ('#' . $request['disaster_request_id'])) ?>
            </h2>
            <?php if (!empty($request['is_sos'])): ?>
                <span class="badge badge-urgent" style="font-size: 0.8rem; animation: pulse 2s infinite;">
                    <i class="fa fa-life-ring"></i> EMERGENCY SOS
                </span>
            <?php endif; ?>
            <?= View::statusBadge($request['status']) ?>
        </div>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
            Barangay Hinaplanon, <?= htmlspecialchars($request['purok_name']) ?> | Reported by <?= htmlspecialchars(($request['requester_first'] ?? '') . ' ' . ($request['requester_last'] ?? '')) ?> (<?= htmlspecialchars($request['user_type'] ?? 'Resident') ?>) on <?= date('F d, Y h:i A', strtotime($request['created_at'] ?? $request['disaster_date'])) ?>
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?route=disaster-requests" class="btn btn-outline">
            <i class="fa fa-arrow-left"></i> All Requests
        </a>
    </div>
</div>

<!-- ============================================== -->
<!-- 1. INCIDENT LIFECYCLE PROGRESS STEPPER -->
<!-- ============================================== -->
<?php
    $statuses = ['Reported', 'Under Verification', 'Verified', 'Assigned', 'Responding', 'Resolved'];
    $currentStatus = $request['status'];
    if ($currentStatus === 'Pending') $currentStatus = 'Reported';
    $currentIndex = array_search($currentStatus, $statuses);
    if ($currentIndex === false) $currentIndex = 0;
?>
<div class="card" style="margin-bottom: 24px; background: #ffffff;">
    <div class="card-body" style="padding: 18px 24px;">
        <div style="display: flex; justify-content: space-between; position: relative; align-items: center; flex-wrap: wrap; gap: 12px;">
            <?php foreach ($statuses as $idx => $stName): ?>
                <?php 
                    $isDone = ($idx <= $currentIndex); 
                    $isCurrent = ($idx === $currentIndex);
                    $stepColor = $isCurrent ? 'var(--primary)' : ($isDone ? 'var(--success)' : '#94a3b8');
                    $stepBg = $isCurrent ? '#eff6ff' : ($isDone ? '#f0fdf4' : '#f8fafc');
                ?>
                <div style="display: flex; flex-direction: column; align-items: center; text-align: center; z-index: 2; min-width: 90px;">
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: <?= $stepBg ?>; border: 2px solid <?= $stepColor ?>; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; color: <?= $stepColor ?>; margin-bottom: 6px;">
                        <?php if ($idx < $currentIndex): ?>
                            <i class="fa fa-check"></i>
                        <?php else: ?>
                            <?= $idx + 1 ?>
                        <?php endif; ?>
                    </div>
                    <span style="font-size: 0.78rem; font-weight: <?= $isCurrent ? '800' : '600' ?>; color: <?= $isCurrent ? 'var(--primary-dark)' : ($isDone ? '#1e293b' : 'var(--text-muted)') ?>;">
                        <?= $stName ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="grid-cols-2">
    <!-- Incident Parameters Details -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa fa-clipboard-list text-primary"></i> Incident Parameters & Field Report</div>
            <div><?= View::riskBadge($request['purok_risk'] ?? 'Pending Verification') ?></div>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; background: #f8fafc; padding: 14px; border-radius: 6px; border: 1px solid var(--border);">
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Disaster Type:</span>
                    <div style="font-weight: 700; color: #0f172a; font-size: 1rem;"><?= htmlspecialchars($request['disaster_type']) ?></div>
                </div>
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Severity & Urgency:</span>
                    <div>
                        <strong><?= htmlspecialchars($request['severity_level']) ?></strong>
                        <?= !empty($request['is_urgent']) ? '<span class="badge badge-urgent" style="font-size: 0.7rem;">URGENT</span>' : '' ?>
                    </div>
                </div>
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Affected Families:</span>
                    <div style="font-weight: 700;"><?= (int)$request['affected_households'] ?> Households</div>
                </div>
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Affected Population:</span>
                    <div style="font-weight: 700;"><?= (int)$request['affected_population'] ?> Individuals</div>
                </div>
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Weather Condition:</span>
                    <div><?= htmlspecialchars($request['weather_name'] ?? 'Heavy Rain') ?></div>
                </div>
                <div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Contact Number:</span>
                    <div><?= htmlspecialchars($request['contact_number'] ?? 'Not provided') ?></div>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label">Description / Field Ground Observation:</label>
                <div style="padding: 12px; background: #ffffff; border: 1px solid var(--border); border-left: 4px solid var(--primary); border-radius: 4px; font-size: 0.9rem; line-height: 1.45;">
                    <?= nl2br(htmlspecialchars($request['description'])) ?>
                </div>
            </div>

            <?php if (!empty($request['latitude']) && !empty($request['longitude'])): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #eff6ff; border-radius: 4px; border: 1px solid #bfdbfe; font-size: 0.82rem;">
                    <div>
                        <i class="fa fa-location-crosshairs text-primary"></i> 
                        <strong>Coordinates:</strong> <?= number_format((float)$request['latitude'], 4) ?>, <?= number_format((float)$request['longitude'], 4) ?>
                    </div>
                    <a href="https://maps.google.com/?q=<?= $request['latitude'] ?>,<?= $request['longitude'] ?>" target="_blank" class="btn btn-outline btn-sm" style="font-size: 0.75rem;">
                        <i class="fa fa-external-link"></i> Google Maps
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: Verification, Assignment, and AI Recommendations -->
    <div>
        <!-- 2. OPERATIONAL ACTION WORKFLOW (Verify, Assign, Update Status) -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <div class="card-title"><i class="fa fa-shield-halved text-primary"></i> Operational Response Controls</div>
            </div>
            <div class="card-body">
                <!-- Verification Status Box -->
                <div style="margin-bottom: 18px; padding: 12px; border-radius: 6px; background: #f8fafc; border: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Verification Status:</span>
                            <div style="font-weight: 700; margin-top: 2px;">
                                <?php if (!empty($request['verified_at'])): ?>
                                    <span style="color: var(--success);"><i class="fa fa-circle-check"></i> Officially Verified</span>
                                    <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: normal;">
                                        By <?= htmlspecialchars(($request['verified_first'] ?? '') . ' ' . ($request['verified_last'] ?? '')) ?> on <?= date('M d, Y h:i A', strtotime($request['verified_at'])) ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color: var(--warning);"><i class="fa fa-clock"></i> Unverified Incident</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Verification Button (Barangay Head & ICDRRMO) -->
                        <?php if (empty($request['verified_at']) && in_array($currentRole, ['Barangay Head', 'ICDRRMO'])): ?>
                            <form action="index.php?route=disaster-requests-verify" method="POST" style="margin: 0;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="disaster_request_id" value="<?= (int)$request['disaster_request_id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fa fa-check"></i> Verify Incident
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Responder Assignment Box -->
                <div style="margin-bottom: 18px; padding: 12px; border-radius: 6px; background: #f8fafc; border: 1px solid var(--border);">
                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Assigned Responders:</span>
                    <?php if (!empty($request['assigned_to_user_id'])): ?>
                        <div style="margin-top: 4px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: var(--primary-dark); font-size: 0.95rem;">
                                    <i class="fa fa-person-running"></i> <?= htmlspecialchars(($request['assigned_first'] ?? '') . ' ' . ($request['assigned_last'] ?? '')) ?>
                                </strong>
                                <div style="font-size: 0.78rem; color: var(--text-muted);">
                                    Contact: <?= htmlspecialchars($request['assigned_phone'] ?? 'BDRRMC Radio') ?> | Dispatched: <?= date('M d, h:i A', strtotime($request['assigned_at'])) ?>
                                </div>
                            </div>
                            <span class="badge badge-info">Dispatched</span>
                        </div>
                    <?php else: ?>
                        <div style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px;">
                            No responder has been dispatched to this location yet.
                        </div>
                    <?php endif; ?>

                    <!-- Responder Assignment Form for Officers -->
                    <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head'])): ?>
                        <form action="index.php?route=disaster-requests-assign" method="POST" style="margin-top: 10px; display: flex; gap: 8px;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="disaster_request_id" value="<?= (int)$request['disaster_request_id'] ?>">
                            <select name="responder_id" class="form-control" style="font-size: 0.82rem;" required>
                                <option value="">-- Assign/Reassign Responder --</option>
                                <?php foreach ($responders as $resp): ?>
                                    <option value="<?= (int)$resp['user_id'] ?>" <?= ($request['assigned_to_user_id'] == $resp['user_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($resp['firstname'] . ' ' . $resp['lastname']) ?> (<?= htmlspecialchars($resp['cellphone_number'] ?? 'Radio') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm" style="white-space: nowrap;">
                                <i class="fa fa-paper-plane"></i> Dispatch
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Operational Status Progression Form -->
                <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head', 'Responder'])): ?>
                    <form action="index.php?route=disaster-requests-status" method="POST" style="padding: 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 6px;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="disaster_request_id" value="<?= (int)$request['disaster_request_id'] ?>">

                        <div class="form-group" style="margin-bottom: 10px;">
                            <label class="form-label" style="font-size: 0.8rem;">Update Incident Operational Status:</label>
                            <select name="status" class="form-control" style="font-size: 0.85rem;" required>
                                <option value="Under Verification" <?= $request['status'] === 'Under Verification' ? 'selected' : '' ?>>Under Verification</option>
                                <option value="Verified" <?= $request['status'] === 'Verified' ? 'selected' : '' ?>>Verified</option>
                                <option value="Assigned" <?= $request['status'] === 'Assigned' ? 'selected' : '' ?>>Assigned</option>
                                <option value="Responding" <?= $request['status'] === 'Responding' ? 'selected' : '' ?>>Responding</option>
                                <option value="Resolved" <?= $request['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 10px;">
                            <input type="text" name="remarks" class="form-control" placeholder="Ground update or operational remarks..." style="font-size: 0.82rem;">
                        </div>

                        <button type="submit" class="btn btn-outline btn-sm" style="width: 100%; justify-content: center;">
                            <i class="fa fa-arrows-rotate"></i> Update Operational Status
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3. DECISION TREE RECOMMENDATION ENGINE PANEL -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa fa-microchip text-primary"></i> Decision Tree Allocation Recommendation</div>
            </div>
            <div class="card-body">
                <?php if ($recommendation): ?>
                    <div style="padding: 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.8rem; font-weight: 700; color: #166534;">PREDICTED PRIORITY:</span>
                            <?= View::priorityBadge($recommendation['predicted_priority']) ?>
                        </div>
                        <div style="font-size: 1.15rem; font-weight: 800; color: #14532d; margin-bottom: 4px;">
                            Recommended: <?= (int)$recommendation['recommended_quantity'] ?> units
                        </div>
                        <div style="font-size: 0.82rem; color: #15803d;">
                            Target Resource: <strong><?= htmlspecialchars($recommendation['supply_name'] ?? 'Relief Goods') ?></strong>
                        </div>
                        <div style="margin-top: 8px; font-size: 0.82rem;">
                            Status: <?= View::statusBadge($recommendation['recommendation_status']) ?>
                        </div>
                    </div>

                    <a href="index.php?route=recommendations-show&id=<?= (int)$recommendation['recommendation_id'] ?>" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px;">
                        <i class="fa fa-magnifying-glass"></i> View 5-Question AI Brief & Official Review
                    </a>
                <?php else: ?>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">
                        Run the supervised CART Decision Tree pipeline to classify allocation priority and determine resource quantities based on hazard zone, severity, and population.
                    </p>

                    <?php if (in_array($currentRole, ['ICDRRMO', 'Barangay Head'])): ?>
                        <form action="index.php?route=disaster-requests-recommend" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="disaster_request_id" value="<?= (int)$request['disaster_request_id'] ?>">

                            <div class="form-group" style="margin-bottom: 12px;">
                                <label class="form-label" style="font-size: 0.82rem;">Target Supply Resource Item:</label>
                                <select name="supply_id" class="form-control" style="font-size: 0.85rem;">
                                    <?php foreach ($supplies as $s): ?>
                                        <option value="<?= (int)$s['supply_id'] ?>">
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
                        <div class="alert alert-info" style="font-size: 0.82rem; margin: 0;">
                            Awaiting official review and Decision Tree recommendation by Barangay Head or ICDRRMO.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 4. INCIDENT STATUS TRANSITION TIMELINE -->
<!-- ============================================== -->
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-timeline text-primary"></i> Incident Status History & Audit Log</div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">Immutable chronological log</div>
    </div>
    <div class="card-body">
        <?php if (empty($history)): ?>
            <p style="color: var(--text-muted); font-size: 0.85rem;">No historical status transitions recorded yet.</p>
        <?php else: ?>
            <div style="position: relative; padding-left: 24px; border-left: 2px solid var(--border);">
                <?php foreach ($history as $hist): ?>
                    <div style="position: relative; margin-bottom: 20px;">
                        <!-- Bullet dot -->
                        <div style="position: absolute; left: -31px; top: 2px; width: 14px; height: 14px; border-radius: 50%; background: var(--primary); border: 2px solid #ffffff;"></div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 8px;">
                            <div>
                                <span class="badge badge-info"><?= htmlspecialchars($hist['status_from'] ?? 'Initial') ?></span>
                                &rarr;
                                <span class="badge badge-success"><?= htmlspecialchars($hist['status_to']) ?></span>
                                <span style="font-size: 0.85rem; font-weight: 600; margin-left: 6px;">
                                    by <?= htmlspecialchars(($hist['first_name'] ?? '') . ' ' . ($hist['last_name'] ?? 'System')) ?>
                                </span>
                            </div>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">
                                <?= date('F d, Y h:i A', strtotime($hist['created_at'])) ?>
                            </span>
                        </div>

                        <?php if (!empty($hist['remarks'])): ?>
                            <div style="margin-top: 6px; font-size: 0.85rem; color: #475569; background: #f8fafc; padding: 6px 10px; border-radius: 4px; border: 1px solid var(--border);">
                                <?= htmlspecialchars($hist['remarks']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
