<!-- views/dashboard/index.php -->
<?php use App\Core\View; ?>

<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--primary-dark);">
            Barangay Hinaplanon Disaster Operations Center
        </h2>
        <p style="color: var(--text-muted); font-size: 0.85rem;">
            Iligan City Disaster Risk Reduction and Management Council (ICDRRMC) Network
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <?php if (in_array($role, ['Barangay Head', 'ICDRRMO'])): ?>
            <a href="index.php?route=disaster-requests-create" class="btn btn-primary">
                <i class="fa fa-plus-circle"></i> New Disaster Request
            </a>
            <a href="index.php?route=hazard-map" class="btn btn-outline">
                <i class="fa fa-map"></i> View Hazard Map
            </a>
        <?php elseif ($role === 'Responder'): ?>
            <a href="index.php?route=field-reports-create" class="btn btn-primary">
                <i class="fa fa-paper-plane"></i> Submit Field Status
            </a>
        <?php else: ?>
            <a href="index.php?route=preparedness-calendar" class="btn btn-primary">
                <i class="fa fa-calendar-days"></i> Activity Calendar
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="grid-cols-4">
    <div class="stat-card">
        <div class="stat-label"><i class="fa fa-bullhorn text-primary"></i> Total Disaster Requests</div>
        <div class="stat-value"><?= (int)$requestCounts['total'] ?></div>
        <div class="stat-sub">
            <span class="badge badge-warning"><?= (int)$requestCounts['pending'] ?> Pending</span>
            <span class="badge badge-success"><?= (int)$requestCounts['approved'] ?> Approved</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label"><i class="fa fa-boxes-stacked text-primary"></i> Remaining Supplies</div>
        <div class="stat-value"><?= number_format((int)$inventorySummary['total_remaining']) ?></div>
        <div class="stat-sub">
            Total recorded: <?= number_format((int)$inventorySummary['total_quantity']) ?> | Released: <?= number_format((int)$inventorySummary['total_released']) ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label"><i class="fa fa-triangle-exclamation text-danger"></i> Low Stock / Critical</div>
        <div class="stat-value" style="color: <?= (int)$inventorySummary['low_stock_count'] > 0 ? 'var(--danger)' : 'var(--success)' ?>;">
            <?= (int)$inventorySummary['low_stock_count'] ?>
        </div>
        <div class="stat-sub">
            <?= (int)$inventorySummary['low_stock_count'] > 0 ? 'Urgent replenishment needed' : 'All items sufficiently stocked' ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label"><i class="fa fa-calendar-check text-primary"></i> Upcoming Drills</div>
        <div class="stat-value"><?= count($upcomingActivities) ?></div>
        <div class="stat-sub">Active community activities scheduled</div>
    </div>
</div>

<!-- Hazard Zone Breakdown Card -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-map-location-dot"></i> Hinaplanon Purok Risk Zones (From CamScanner Hazard Map)</div>
        <a href="index.php?route=hazard-map" class="btn btn-outline btn-sm">Interactive Map Viewer</a>
    </div>
    <div class="card-body" style="display: flex; gap: 16px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 180px; padding: 12px; background: var(--hazard-pink-bg); border: 1px solid #fda4af; border-radius: 6px;">
            <div style="font-weight: 700; color: #9f1239;">HIGH RISK (Pink)</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: #be123c;"><?= (int)($purokRiskSummary['High'] ?? 0) ?> Puroks</div>
            <div style="font-size: 0.75rem; color: #9f1239;">Bayug Island, P-1, P-2A, P-2B, P-3A, P-5A, P-9</div>
        </div>
        <div style="flex: 1; min-width: 180px; padding: 12px; background: var(--hazard-orange-bg); border: 1px solid #fdba74; border-radius: 6px;">
            <div style="font-weight: 700; color: #9a3412;">MEDIUM RISK (Orange)</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: #c2410c;"><?= (int)($purokRiskSummary['Medium'] ?? 0) ?> Puroks</div>
            <div style="font-size: 0.75rem; color: #9a3412;">P-2, P-2C, P-2D, P-3, P-4A, P-5, P-6A, Phase 3&5</div>
        </div>
        <div style="flex: 1; min-width: 180px; padding: 12px; background: var(--hazard-yellow-bg); border: 1px solid #fef08a; border-radius: 6px;">
            <div style="font-weight: 700; color: #854d0e;">LOW RISK (Yellow)</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: #a16207;"><?= (int)($purokRiskSummary['Low'] ?? 0) ?> Puroks</div>
            <div style="font-size: 0.75rem; color: #854d0e;">Purok 6, 6B, 6C, 7 (Evacuation Center Area)</div>
        </div>
        <div style="flex: 1; min-width: 180px; padding: 12px; background: var(--hazard-gray-bg); border: 1px solid #cbd5e1; border-radius: 6px;">
            <div style="font-weight: 700; color: #475569;">PENDING VERIFICATION</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: #334155;"><?= (int)($purokRiskSummary['Pending Verification'] ?? 0) ?> Purok</div>
            <div style="font-size: 0.75rem; color: #475569;">Purok 8 (Multi-zone boundary gradient)</div>
        </div>
    </div>
</div>

<div class="grid-cols-2">
    <!-- Active Disaster Requests -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa fa-triangle-exclamation"></i> Recent Disaster Requests</div>
            <a href="index.php?route=disaster-requests" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Req #</th>
                        <th>Purok & Risk</th>
                        <th>Type & Severity</th>
                        <th>Households</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentRequests)): ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No disaster requests recorded.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentRequests as $req): ?>
                            <tr>
                                <td><strong>#<?= $req['disaster_request_id'] ?></strong></td>
                                <td>
                                    <div><?= htmlspecialchars($req['purok_name']) ?></div>
                                    <?= View::riskBadge($req['purok_risk'] ?? 'Pending Verification') ?>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($req['disaster_type']) ?></div>
                                    <small><?= htmlspecialchars($req['severity_level']) ?> <?= $req['is_urgent'] ? '<span class="badge badge-urgent">URGENT</span>' : '' ?></small>
                                </td>
                                <td><?= (int)$req['affected_households'] ?> HH</td>
                                <td><?= View::statusBadge($req['status']) ?></td>
                                <td>
                                    <a href="index.php?route=disaster-requests-show&id=<?= $req['disaster_request_id'] ?>" class="btn btn-outline btn-sm">
                                        Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- AI Recommendations -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa fa-microchip"></i> Recent Decision Tree Recommendations</div>
            <a href="index.php?route=recommendations" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Rec #</th>
                        <th>Purok</th>
                        <th>Priority</th>
                        <th>Allocated Qty</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentRecommendations)): ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No AI recommendations generated yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentRecommendations as $rec): ?>
                            <tr>
                                <td><strong>#<?= $rec['recommendation_id'] ?></strong></td>
                                <td><?= htmlspecialchars($rec['purok_name']) ?></td>
                                <td><?= View::priorityBadge($rec['predicted_priority']) ?></td>
                                <td>
                                    <strong><?= (int)$rec['recommended_quantity'] ?></strong> 
                                    <small><?= htmlspecialchars($rec['unit_of_measure'] ?? 'units') ?></small>
                                </td>
                                <td><?= View::statusBadge($rec['recommendation_status']) ?></td>
                                <td>
                                    <a href="index.php?route=recommendations-show&id=<?= $rec['recommendation_id'] ?>" class="btn btn-outline btn-sm">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Community Activities & Field Updates -->
<div class="grid-cols-2">
    <!-- Preparedness Activities -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa fa-calendar-days"></i> Upcoming Preparedness Activities</div>
            <a href="index.php?route=preparedness" class="btn btn-outline btn-sm">Calendar</a>
        </div>
        <div class="card-body">
            <?php if (empty($upcomingActivities)): ?>
                <p style="color: var(--text-muted); font-size: 0.88rem;">No scheduled drills or seminars currently pending.</p>
            <?php else: ?>
                <?php foreach ($upcomingActivities as $act): ?>
                    <div style="padding: 10px 0; border-bottom: 1px solid var(--border);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <strong style="color: var(--primary-dark); font-size: 0.95rem;"><?= htmlspecialchars($act['title']) ?></strong>
                            <span class="badge badge-info"><?= htmlspecialchars($act['activity_type']) ?></span>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">
                            <i class="fa fa-clock"></i> <?= date('F d, Y h:i A', strtotime($act['schedule_date'])) ?> | 
                            <i class="fa fa-location-dot"></i> <?= htmlspecialchars($act['location']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Responder Field Updates -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa fa-truck-medical"></i> Verified Responder Field Status</div>
            <a href="index.php?route=field-reports" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="card-body">
            <?php if (empty($recentReports)): ?>
                <p style="color: var(--text-muted); font-size: 0.88rem;">No field reports submitted recently.</p>
            <?php else: ?>
                <?php foreach ($recentReports as $rep): ?>
                    <div style="padding: 10px 0; border-bottom: 1px solid var(--border);">
                        <div style="display: flex; justify-content: space-between;">
                            <strong><?= htmlspecialchars($rep['purok_name']) ?></strong>
                            <span class="badge badge-<?= $rep['road_accessibility'] === 'Passable' ? 'success' : 'danger' ?>">
                                <?= htmlspecialchars($rep['road_accessibility']) ?>
                            </span>
                        </div>
                        <div style="font-size: 0.85rem; margin-top: 4px;">
                            <?= htmlspecialchars($rep['status_update']) ?>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                            Reported by: <?= htmlspecialchars($rep['responder_name']) ?> | <?= date('M d, h:i A', strtotime($rep['report_time'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
