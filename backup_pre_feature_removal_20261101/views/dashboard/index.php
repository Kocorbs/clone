<!-- views/dashboard/index.php -->
<?php use App\Core\View; ?>

<!-- Emergency Alert Broadcast Banner (if any active alerts) -->
<?php if (!empty($alerts)): ?>
    <div style="margin-bottom: 20px;">
        <?php foreach ($alerts as $alt): ?>
            <div class="alert alert-danger" style="margin-bottom: 8px; border-left: 5px solid #b91c1c; display: flex; align-items: center; justify-content: space-between; gap: 12px; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="display: inline-flex; width: 36px; height: 36px; border-radius: 50%; background: #fee2e2; color: #dc2626; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                        <i class="fa fa-triangle-exclamation"></i>
                    </span>
                    <div>
                        <strong style="font-size: 0.95rem; color: #991b1b;"><?= htmlspecialchars($alt['title']) ?></strong>
                        <div style="font-size: 0.85rem; color: #7f1d1d; margin-top: 2px;"><?= htmlspecialchars($alt['message']) ?></div>
                    </div>
                </div>
                <div style="font-size: 0.75rem; color: #991b1b; white-space: nowrap;">
                    <i class="fa fa-clock"></i> <?= date('M d, h:i A', strtotime($alt['created_at'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Header / Welcome & Quick Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
    <div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <h2 style="font-size: 1.45rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
                Barangay Hinaplanon Disaster Operations Center
            </h2>
            <span class="badge badge-primary" style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                <?= htmlspecialchars($role) ?> VIEW
            </span>
        </div>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px;">
            Iligan City Disaster Risk Reduction & Management Council (ICDRRMC) | Spatial Reference: CamScanner Hazard Map
        </p>
    </div>

    <!-- Contextual Action Buttons -->
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <?php if ($role === 'Resident'): ?>
            <a href="index.php?route=emergency-sos" class="btn btn-danger" style="animation: pulse 2s infinite;">
                <i class="fa fa-life-ring"></i> Send Emergency SOS
            </a>
            <a href="index.php?route=disaster-requests-create" class="btn btn-primary">
                <i class="fa fa-hand-holding-hand"></i> Request Relief Supplies
            </a>
            <a href="index.php?route=shelters" class="btn btn-outline">
                <i class="fa fa-person-shelter"></i> Safe Shelters
            </a>
        <?php elseif ($role === 'Responder'): ?>
            <a href="index.php?route=field-reports-create" class="btn btn-primary">
                <i class="fa fa-paper-plane"></i> Submit Ground Field Report
            </a>
            <a href="index.php?route=shelters" class="btn btn-outline">
                <i class="fa fa-person-shelter"></i> Evacuation Centers
            </a>
        <?php else: /* ICDRRMO & Barangay Head */ ?>
            <a href="index.php?route=disaster-requests-create" class="btn btn-primary">
                <i class="fa fa-plus-circle"></i> Log Disaster Incident
            </a>
            <a href="index.php?route=recommendations" class="btn btn-outline">
                <i class="fa fa-microchip"></i> AI Recommendations
            </a>
            <a href="index.php?route=shelters" class="btn btn-outline">
                <i class="fa fa-person-shelter"></i> Evacuation Centers
            </a>
            <?php if (!empty($pendingResidents)): ?>
                <a href="index.php?route=pending-residents" class="btn btn-warning">
                    <i class="fa fa-user-clock"></i> Resident Approvals (<?= count($pendingResidents) ?>)
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================== -->
<!-- 1. KEY PERFORMANCE INDICATOR (KPI) METRICS -->
<!-- ========================================== -->
<?php if ($role === 'Resident'): ?>
    <!-- Resident Personalized Metric Cards -->
    <div class="grid-cols-4" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-label"><i class="fa fa-bullhorn text-primary"></i> My Active Requests</div>
            <div class="stat-value"><?= count($myRequests) ?></div>
            <div class="stat-sub">Disaster relief or SOS submitted</div>
        </div>

        <div class="stat-card">
            <div class="stat-label"><i class="fa fa-person-shelter text-success"></i> Open Evacuation Centers</div>
            <div class="stat-value"><?= (int)$shelterSummary['total_shelters'] ?></div>
            <div class="stat-sub">
                Capacity: <?= (int)$shelterSummary['total_occupancy'] ?> / <?= (int)$shelterSummary['total_capacity'] ?> evacuees
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label"><i class="fa fa-map-pin text-primary"></i> My Barangay Zone</div>
            <div class="stat-value" style="font-size: 1.25rem;">Barangay Hinaplanon</div>
            <div class="stat-sub">Purok risk classified via hazard map</div>
        </div>

        <div class="stat-card">
            <div class="stat-label"><i class="fa fa-bell text-info"></i> Unread Notifications</div>
            <div class="stat-value"><?= (int)$unreadNotifs ?></div>
            <div class="stat-sub">Emergency advisories and updates</div>
        </div>
    </div>

<?php elseif ($role === 'Responder'): ?>
    <!-- Responder Operational Metric Cards -->
    <div class="grid-cols-4" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-label"><i class="fa fa-truck-medical text-primary"></i> Assigned Incidents</div>
            <div class="stat-value" style="color: <?= count($assignedIncidents) > 0 ? 'var(--primary)' : 'var(--text-muted)' ?>;">
                <?= count($assignedIncidents) ?>
            </div>
            <div class="stat-sub">Emergency responses awaiting your deployment</div>
        </div>

        <div class="stat-card">
            <div class="stat-label"><i class="fa fa-person-shelter text-success"></i> Evacuation Centers</div>
            <div class="stat-value"><?= (int)$shelterSummary['total_shelters'] ?></div>
            <div class="stat-sub"><?= (int)$shelterSummary['total_occupancy'] ?> / <?= (int)$shelterSummary['total_capacity'] ?> persons accommodated</div>
        </div>

        <div class="stat-card">
            <div class="stat-label"><i class="fa fa-file-waveform text-info"></i> Recent Field Reports</div>
            <div class="stat-value"><?= count($recentReports) ?></div>
            <div class="stat-sub">Road accessibility & ground assessments</div>
        </div>

        <div class="stat-card">
            <div class="stat-label"><i class="fa fa-triangle-exclamation text-danger"></i> High-Risk Puroks</div>
            <div class="stat-value" style="color: var(--danger);"><?= (int)($purokRiskSummary['High'] ?? 0) ?></div>
            <div class="stat-sub">Riverbanks & Mandulog flood zones</div>
        </div>
    </div>

<?php else: /* ICDRRMO & Barangay Head Command Cards */ ?>
    <!-- Command Staff System-Wide KPI Cards -->
    <div class="grid-cols-4" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-label"><i class="fa fa-triangle-exclamation text-danger"></i> Active Disaster Incidents</div>
            <div class="stat-value" style="color: <?= (int)$requestCounts['pending'] > 0 ? 'var(--danger)' : 'var(--primary-dark)' ?>;">
                <?= (int)$requestCounts['total'] ?>
            </div>
            <div class="stat-sub">
                <span class="badge badge-warning"><?= (int)$requestCounts['pending'] ?> Pending</span>
                <span class="badge badge-success"><?= (int)$requestCounts['approved'] ?> Handled</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label"><i class="fa fa-person-shelter text-primary"></i> Evacuees in Shelters</div>
            <div class="stat-value">
                <?= number_format((int)$shelterSummary['total_occupancy']) ?>
                <span style="font-size: 0.85rem; font-weight: normal; color: var(--text-muted);">/ <?= number_format((int)$shelterSummary['total_capacity']) ?></span>
            </div>
            <div class="stat-sub">
                <?= round(((int)$shelterSummary['total_occupancy'] / max(1, (int)$shelterSummary['total_capacity'])) * 100, 1) ?>% aggregate capacity utilized
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label"><i class="fa fa-boxes-stacked text-primary"></i> Remaining Relief Supplies</div>
            <div class="stat-value"><?= number_format((int)$inventorySummary['total_remaining']) ?></div>
            <div class="stat-sub">
                <?php if ((int)$inventorySummary['low_stock_count'] > 0): ?>
                    <span class="badge badge-danger"><?= (int)$inventorySummary['low_stock_count'] ?> Low Stock Alert</span>
                <?php else: ?>
                    <span class="badge badge-success">Stock Adequate</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label"><i class="fa fa-map-location-dot text-danger"></i> Critical High-Risk Puroks</div>
            <div class="stat-value" style="color: #be123c;"><?= (int)($purokRiskSummary['High'] ?? 0) ?></div>
            <div class="stat-sub">Sendong flood-prone riverbank sectors</div>
        </div>
    </div>
<?php endif; ?>

<!-- ==================================================== -->
<!-- 2. LIVE INTERACTIVE LEAFLET HAZARD & DISASTER MAP -->
<!-- ==================================================== -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div class="card-title" style="margin: 0;">
                <i class="fa fa-map-location-dot text-primary"></i> Live Disaster & Hazard Operations Map
            </div>
            <span class="badge badge-primary" style="font-size: 0.72rem;">
                <i class="fa fa-satellite-dish"></i> Live Polling (20s)
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 0.78rem; color: var(--text-muted);">
                Last synced: <span id="mapSyncTime" style="font-weight: 600;">Connecting...</span>
            </span>
            <button type="button" id="btnMapRefresh" class="btn btn-outline btn-sm" title="Refresh Map Data Now">
                <i class="fa fa-rotate"></i> Sync
            </button>
            <a href="index.php?route=hazard-map" class="btn btn-outline btn-sm">
                <i class="fa fa-expand"></i> Full Map & CamScanner Reference
            </a>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <!-- Leaflet Map Container -->
        <div id="dashboardHazardMap" style="height: 440px; width: 100%; border-radius: 0 0 6px 6px;"></div>
    </div>
    <div class="card-footer" style="background: #f8fafc; padding: 10px 16px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; font-size: 0.8rem;">
        <div style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
            <span style="font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Map Legend:</span>
            <span style="display: inline-flex; align-items: center; gap: 4px;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: var(--hazard-pink); border: 1px solid #fda4af;"></span>
                High Risk (Riverbank/Sendong)
            </span>
            <span style="display: inline-flex; align-items: center; gap: 4px;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: var(--hazard-orange); border: 1px solid #fdba74;"></span>
                Medium Risk (Drainage Buffers)
            </span>
            <span style="display: inline-flex; align-items: center; gap: 4px;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: var(--hazard-yellow); border: 1px solid #fef08a;"></span>
                Low Risk (Safe Zones)
            </span>
            <span style="display: inline-flex; align-items: center; gap: 4px;">
                <span style="display: inline-flex; width: 14px; height: 14px; border-radius: 50%; background-color: #16a34a; color: #fff; align-items: center; justify-content: center; font-size: 9px;"><i class="fa fa-person-shelter"></i></span>
                Evacuation Shelter
            </span>
            <span style="display: inline-flex; align-items: center; gap: 4px;">
                <span style="display: inline-flex; width: 14px; height: 14px; border-radius: 3px; background-color: #dc2626; color: #fff; align-items: center; justify-content: center; font-size: 9px;"><i class="fa fa-triangle-exclamation"></i></span>
                Active Incident / SOS
            </span>
        </div>
        <div style="color: var(--text-muted);">
            Click on any shelter or incident pin to view details and actions.
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 3. ROLE-SPECIFIC TABLES & OPERATIONAL PANELS -->
<!-- ============================================== -->

<?php if ($role === 'Resident'): ?>
    <!-- Resident Specific Section: My Submissions & Evacuation Shelters -->
    <div class="grid-cols-2">
        <!-- My Disaster Requests & SOS -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa fa-clock-rotate-left"></i> My Emergency & Relief Requests</div>
                <a href="index.php?route=disaster-requests-create" class="btn btn-outline btn-sm"><i class="fa fa-plus"></i> New Request</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Disaster Type</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($myRequests)): ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 24px;">You have no active emergency or supply requests logged.</td></tr>
                        <?php else: ?>
                            <?php foreach ($myRequests as $req): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($req['reference_no'] ?? ('#'.$req['disaster_request_id'])) ?></strong>
                                        <?php if (!empty($req['is_sos'])): ?>
                                            <span class="badge badge-urgent">SOS</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><strong><?= htmlspecialchars($req['disaster_type']) ?></strong></div>
                                        <small style="color: var(--text-muted);"><?= htmlspecialchars($req['severity_level']) ?> Severity</small>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($req['disaster_date'])) ?></td>
                                    <td><?= View::statusBadge($req['status']) ?></td>
                                    <td>
                                        <a href="index.php?route=disaster-requests-show&id=<?= $req['disaster_request_id'] ?>" class="btn btn-outline btn-sm">View Status</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Designated Evacuation Shelters for Residents -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa fa-person-shelter"></i> Barangay Hinaplanon Evacuation Shelters</div>
                <a href="index.php?route=shelters" class="btn btn-outline btn-sm">View All Shelters</a>
            </div>
            <div class="card-body">
                <?php foreach ($shelters as $sh): ?>
                    <?php 
                        $pct = round(($sh['current_occupancy'] / max(1, $sh['max_capacity'])) * 100);
                        $barColor = $pct >= 90 ? 'var(--danger)' : ($pct >= 70 ? 'var(--warning)' : 'var(--success)');
                    ?>
                    <div style="padding: 12px; border: 1px solid var(--border); border-radius: 6px; margin-bottom: 12px; background: #ffffff;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <strong style="color: var(--primary-dark); font-size: 0.95rem;">
                                <i class="fa fa-building-shield text-primary"></i> <?= htmlspecialchars($sh['shelter_name']) ?>
                            </strong>
                            <span class="badge badge-<?= $sh['shelter_status'] === 'Full' ? 'danger' : ($sh['shelter_status'] === 'Near Full' ? 'warning' : 'success') ?>">
                                <?= htmlspecialchars($sh['shelter_status']) ?>
                            </span>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">
                            <i class="fa fa-location-dot"></i> <?= htmlspecialchars($sh['location_address']) ?> | 
                            <i class="fa fa-phone"></i> <?= htmlspecialchars($sh['contact_number'] ?? 'BDRRMC Hotline') ?>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.78rem; margin-bottom: 4px;">
                            <span>Space Occupied: <strong><?= (int)$sh['current_occupancy'] ?> / <?= (int)$sh['max_capacity'] ?> persons</strong></span>
                            <span style="font-weight: 700; color: <?= $barColor ?>;"><?= $pct ?>%</span>
                        </div>
                        <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                            <div style="width: <?= min(100, $pct) ?>%; height: 100%; background: <?= $barColor ?>; transition: width 0.3s ease;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

<?php elseif ($role === 'Responder'): ?>
    <!-- Responder Specific Section: Assigned Deployments & Verified Field Updates -->
    <div class="grid-cols-2">
        <!-- Assigned Deployments -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa fa-person-running"></i> My Assigned Emergency Operations</div>
                <span class="badge badge-primary"><?= count($assignedIncidents) ?> Active</span>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ref #</th>
                            <th>Purok & Risk</th>
                            <th>Type & Severity</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assignedIncidents)): ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 24px;">No active operations currently assigned to you. Stand by for BDRRMC dispatch.</td></tr>
                        <?php else: ?>
                            <?php foreach ($assignedIncidents as $inc): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($inc['reference_no'] ?? ('#'.$inc['disaster_request_id'])) ?></strong>
                                        <?php if (!empty($inc['is_sos'])): ?><span class="badge badge-urgent">SOS</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <div><strong><?= htmlspecialchars($inc['purok_name']) ?></strong></div>
                                        <?= View::riskBadge($inc['purok_risk'] ?? 'Pending Verification') ?>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($inc['disaster_type']) ?></div>
                                        <small style="color: var(--text-muted);"><?= htmlspecialchars($inc['severity_level']) ?></small>
                                    </td>
                                    <td><?= View::statusBadge($inc['status']) ?></td>
                                    <td>
                                        <a href="index.php?route=disaster-requests-show&id=<?= $inc['disaster_request_id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fa fa-shield"></i> Respond
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Field Status Reports -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa fa-road"></i> Ground Status & Road Passability</div>
                <a href="index.php?route=field-reports-create" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Submit Report</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentReports)): ?>
                    <p style="color: var(--text-muted); font-size: 0.88rem;">No ground status reports submitted yet.</p>
                <?php else: ?>
                    <?php foreach ($recentReports as $rep): ?>
                        <div style="padding: 10px 0; border-bottom: 1px solid var(--border);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong style="color: var(--primary-dark);"><?= htmlspecialchars($rep['purok_name']) ?></strong>
                                <span class="badge badge-<?= $rep['road_accessibility'] === 'Passable' ? 'success' : 'danger' ?>">
                                    <i class="fa fa-car"></i> <?= htmlspecialchars($rep['road_accessibility']) ?>
                                </span>
                            </div>
                            <div style="font-size: 0.85rem; color: #334155; margin-top: 4px;">
                                <?= htmlspecialchars($rep['status_update']) ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                                By: <?= htmlspecialchars($rep['responder_name']) ?> | <?= date('M d, h:i A', strtotime($rep['report_time'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php else: /* ICDRRMO & Barangay Head Main Operational Split */ ?>
    <!-- Staff Section: Active Requests & AI Recommendation Engine Highlights -->
    <div class="grid-cols-2">
        <!-- Active Disaster Requests -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa fa-triangle-exclamation"></i> Recent Disaster Requests & Incidents</div>
                <a href="index.php?route=disaster-requests" class="btn btn-outline btn-sm">View All (<?= (int)$requestCounts['total'] ?>)</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ref / ID</th>
                            <th>Purok & Risk</th>
                            <th>Type & Severity</th>
                            <th>Households</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentRequests)): ?>
                            <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">No disaster incidents logged in system.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentRequests as $req): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($req['reference_no'] ?? ('#'.$req['disaster_request_id'])) ?></strong>
                                        <?php if (!empty($req['is_sos'])): ?><span class="badge badge-urgent">SOS</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <div><strong><?= htmlspecialchars($req['purok_name']) ?></strong></div>
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
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Decision Tree Recommendations -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa fa-microchip"></i> Decision Tree Allocation Recommendations</div>
                <a href="index.php?route=recommendations" class="btn btn-outline btn-sm">All Recommendations</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Rec #</th>
                            <th>Purok</th>
                            <th>Priority</th>
                            <th>Recommended Qty</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentRecommendations)): ?>
                            <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">No recommendations generated yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentRecommendations as $rec): ?>
                                <tr>
                                    <td><strong>#<?= $rec['recommendation_id'] ?></strong></td>
                                    <td>
                                        <strong><?= htmlspecialchars($rec['purok_name']) ?></strong>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($rec['supply_name'] ?? 'Relief Goods') ?></div>
                                    </td>
                                    <td><?= View::priorityBadge($rec['predicted_priority']) ?></td>
                                    <td>
                                        <strong style="color: var(--primary-dark); font-size: 1rem;"><?= (int)$rec['recommended_quantity'] ?></strong> 
                                        <small><?= htmlspecialchars($rec['unit_of_measure'] ?? 'units') ?></small>
                                    </td>
                                    <td><?= View::statusBadge($rec['recommendation_status']) ?></td>
                                    <td>
                                        <a href="index.php?route=recommendations-show&id=<?= $rec['recommendation_id'] ?>" class="btn btn-primary btn-sm">
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

    <!-- Shelters & Audit Logs row -->
    <div class="grid-cols-2">
        <!-- Shelter Occupancy Status -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa fa-person-shelter"></i> Evacuation Shelters Live Monitoring</div>
                <a href="index.php?route=shelters" class="btn btn-outline btn-sm">Update Occupancies</a>
            </div>
            <div class="card-body">
                <?php foreach ($shelters as $sh): ?>
                    <?php 
                        $pct = round(($sh['current_occupancy'] / max(1, $sh['max_capacity'])) * 100);
                        $barColor = $pct >= 90 ? 'var(--danger)' : ($pct >= 70 ? 'var(--warning)' : 'var(--success)');
                    ?>
                    <div style="padding: 10px 0; border-bottom: 1px solid var(--border);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <strong><?= htmlspecialchars($sh['shelter_name']) ?></strong>
                            <span class="badge badge-<?= $sh['shelter_status'] === 'Full' ? 'danger' : ($sh['shelter_status'] === 'Near Full' ? 'warning' : 'success') ?>">
                                <?= htmlspecialchars($sh['shelter_status']) ?>
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.78rem; margin-bottom: 4px; color: var(--text-muted);">
                            <span><?= (int)$sh['current_occupancy'] ?> of <?= (int)$sh['max_capacity'] ?> persons</span>
                            <span style="font-weight: 700; color: <?= $barColor ?>;"><?= $pct ?>%</span>
                        </div>
                        <div style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                            <div style="width: <?= min(100, $pct) ?>%; height: 100%; background: <?= $barColor ?>;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Audit Trail Ledger -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa fa-shield-halved"></i> Recent Audit & Security Events</div>
                <a href="index.php?route=audit-logs" class="btn btn-outline btn-sm">Full Ledger</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table" style="font-size: 0.82rem;">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentLogs)): ?>
                                <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 16px;">No recent security events recorded.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentLogs as $log): ?>
                                    <tr>
                                        <td style="white-space: nowrap;"><?= date('M d, H:i', strtotime($log['created_at'])) ?></td>
                                        <td><strong><?= htmlspecialchars($log['user_name'] ?? 'System') ?></strong></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($log['action_name']) ?></span></td>
                                        <td><?= htmlspecialchars($log['details'] ?? '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Initialize Leaflet Hazard Map for Dashboard -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.initHazardMap === 'function') {
            window.initHazardMap('dashboardHazardMap', { zoom: 14 });
        }
    });
</script>
