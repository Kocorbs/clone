<!-- views/hazard_map/index.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
            Barangay Hinaplanon Hazard Map & Risk Zones
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
            Georeferenced spatial risk classifications derived from the official CamScanner handwritten BDRRMC hazard map.
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" id="btnToggleMapMode" class="btn btn-primary" onclick="toggleMapMode()">
            <i class="fa fa-image"></i> Switch to CamScanner Scanned Map
        </button>
        <button type="button" id="btnMapRefresh" class="btn btn-outline" title="Refresh Live Map">
            <i class="fa fa-rotate"></i> Sync Live Map
        </button>
    </div>
</div>

<!-- Risk Zone Breakdown Cards -->
<div class="grid-cols-4" style="margin-bottom: 24px;">
    <div style="padding: 14px; background: var(--hazard-pink-bg); border: 1px solid #fda4af; border-radius: 6px;">
        <div style="font-weight: 700; color: #9f1239; font-size: 0.85rem;">HIGH RISK (Pink)</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #be123c; margin: 4px 0;">
            <?= (int)($riskSummary['High'] ?? 0) ?> <span style="font-size: 0.85rem; font-weight: 600;">Puroks</span>
        </div>
        <div style="font-size: 0.75rem; color: #9f1239; line-height: 1.3;">
            Bayug Island, P-1, P-2A, P-2B, P-3A, P-5A, P-9 (Mandulog Riverbanks)
        </div>
    </div>

    <div style="padding: 14px; background: var(--hazard-orange-bg); border: 1px solid #fdba74; border-radius: 6px;">
        <div style="font-weight: 700; color: #9a3412; font-size: 0.85rem;">MEDIUM RISK (Orange)</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #c2410c; margin: 4px 0;">
            <?= (int)($riskSummary['Medium'] ?? 0) ?> <span style="font-size: 0.85rem; font-weight: 600;">Puroks</span>
        </div>
        <div style="font-size: 0.75rem; color: #9a3412; line-height: 1.3;">
            P-2, P-2C, P-2D, P-3, P-4A, P-5, P-6A, Phase 3&5 (Drainage & creek buffers)
        </div>
    </div>

    <div style="padding: 14px; background: var(--hazard-yellow-bg); border: 1px solid #fef08a; border-radius: 6px;">
        <div style="font-weight: 700; color: #854d0e; font-size: 0.85rem;">LOW RISK (Yellow)</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #a16207; margin: 4px 0;">
            <?= (int)($riskSummary['Low'] ?? 0) ?> <span style="font-size: 0.85rem; font-weight: 600;">Puroks</span>
        </div>
        <div style="font-size: 0.75rem; color: #854d0e; line-height: 1.3;">
            Purok 6, Purok 6B, Purok 6C, Purok 7 (Designated evacuation grounds)
        </div>
    </div>

    <div style="padding: 14px; background: var(--hazard-gray-bg); border: 1px solid #cbd5e1; border-radius: 6px;">
        <div style="font-weight: 700; color: #475569; font-size: 0.85rem;">PENDING VERIFICATION</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #334155; margin: 4px 0;">
            <?= (int)($riskSummary['Pending Verification'] ?? 0) ?> <span style="font-size: 0.85rem; font-weight: 600;">Purok</span>
        </div>
        <div style="font-size: 0.75rem; color: #475569; line-height: 1.3;">
            Purok 8 (Multi-zone boundary gradient awaiting field boundary survey)
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 1. LIVE INTERACTIVE LEAFLET HAZARD MAP CONTAINER -->
<!-- ============================================== -->
<div id="interactiveMapContainer" class="card" style="margin-bottom: 24px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div class="card-title" style="margin: 0;">
                <i class="fa fa-map-location-dot text-primary"></i> Live Interactive Hazard & Operations Map
            </div>
            <span class="badge badge-success" style="font-size: 0.72rem;">
                <i class="fa fa-wifi"></i> Live Polling (20s)
            </span>
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">
            Last synced: <span id="mapSyncTime" style="font-weight: 600;">Connecting...</span>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div id="interactiveHazardMap" style="height: 520px; width: 100%; border-radius: 0 0 6px 6px;"></div>
    </div>
    <div class="card-footer" style="background: #f8fafc; padding: 10px 16px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; font-size: 0.8rem;">
        <div style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
            <span style="font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Legend:</span>
            <span style="display: inline-flex; align-items: center; gap: 4px;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: var(--hazard-pink); border: 1px solid #fda4af;"></span>
                High Risk (Pink)
            </span>
            <span style="display: inline-flex; align-items: center; gap: 4px;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: var(--hazard-orange); border: 1px solid #fdba74;"></span>
                Medium Risk (Orange)
            </span>
            <span style="display: inline-flex; align-items: center; gap: 4px;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: var(--hazard-yellow); border: 1px solid #fef08a;"></span>
                Low Risk (Yellow)
            </span>
            <span style="display: inline-flex; align-items: center; gap: 4px;">
                <span style="display: inline-flex; width: 14px; height: 14px; border-radius: 50%; background-color: #16a34a; color: #fff; align-items: center; justify-content: center; font-size: 9px;"><i class="fa fa-person-shelter"></i></span>
                Evacuation Shelter
            </span>
            <span style="display: inline-flex; align-items: center; gap: 4px;">
                <span style="display: inline-flex; width: 14px; height: 14px; border-radius: 3px; background-color: #dc2626; color: #fff; align-items: center; justify-content: center; font-size: 9px;"><i class="fa fa-triangle-exclamation"></i></span>
                Incident / SOS
            </span>
        </div>
        <div style="color: var(--text-muted);">
            Barangay Hinaplanon Center: 8.2412° N, 124.2543° E
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 2. SCANNED REFERENCE CAMSCANNER MAP CONTAINER -->
<!-- ============================================== -->
<div id="scannedMapContainer" class="card" style="display: none; margin-bottom: 24px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="card-title">
            <i class="fa fa-file-image"></i> Official Hand-Drawn Reference Hazard Map (CamScanner)
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">Barangay Disaster Risk Reduction & Management Council Archives</div>
    </div>
    <div class="card-body">
        <div class="hazard-map-viewer" style="text-align: center; background: #1e293b; padding: 16px; border-radius: 6px; overflow: auto; max-height: 600px;">
            <img src="assets/images/hinaplanon_hazard_map.jpg" alt="Barangay Hinaplanon Hazard Map" style="max-width: 100%; height: auto; border-radius: 4px; box-shadow: 0 4px 16px rgba(0,0,0,0.4);">
        </div>
        <div class="alert alert-info" style="margin-top: 16px; margin-bottom: 0;">
            <i class="fa fa-circle-info"></i>
            <div>
                <strong>Academic Scope & Thesis Integrity:</strong> The CamScanner map represents the verified empirical ground data drawn by local barangay officials. The system maps these hand-drawn colored hazard zones to discrete geocoded coordinates, serving as verified feature inputs into the Decision Tree classifier.
            </div>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- 3. PUROK RISK CLASSIFICATION & OFFICIAL CORRECTION FORM -->
<!-- ======================================================= -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div class="card-title">
            <i class="fa fa-table-list"></i> Purok Risk Classifications (Decision Tree Feature Inputs)
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">
            Total: <?= count($puroks) ?> Hinaplanon Puroks
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Purok Name</th>
                    <th>Risk Classification</th>
                    <th>Color Zone</th>
                    <th>Coordinates (Lat, Lng)</th>
                    <th>Landmarks & Spatial Context</th>
                    <th>Source Reference</th>
                    <?php if (in_array($currentRole, ['Barangay Head', 'ICDRRMO'])): ?>
                        <th>Official Correction</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($puroks as $p): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($p['purok_name']) ?></strong></td>
                        <td><?= View::riskBadge($p['risk_classification']) ?></td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; font-size: 0.82rem;">
                                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 2px; background-color: <?= $p['risk_zone_color'] === 'Pink' ? 'var(--hazard-pink)' : ($p['risk_zone_color'] === 'Orange' ? 'var(--hazard-orange)' : ($p['risk_zone_color'] === 'Yellow' ? 'var(--hazard-yellow)' : 'var(--hazard-gray)')) ?>;"></span>
                                <?= htmlspecialchars($p['risk_zone_color']) ?>
                            </span>
                        </td>
                        <td style="font-family: monospace; font-size: 0.78rem; color: var(--text-muted);">
                            <?= $p['latitude'] ? number_format((float)$p['latitude'], 4) . ', ' . number_format((float)$p['longitude'], 4) : '—' ?>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; max-width: 280px;"><?= htmlspecialchars($p['notes'] ?? '—') ?></div>
                        </td>
                        <td>
                            <small style="color: var(--text-muted); font-family: monospace;"><?= htmlspecialchars($p['hazard_source_reference'] ?? 'CamScanner 2026') ?></small>
                        </td>
                        <?php if (in_array($currentRole, ['Barangay Head', 'ICDRRMO'])): ?>
                            <td>
                                <form action="index.php?route=hazard-map-update" method="POST" style="display: flex; gap: 4px; align-items: center;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <input type="hidden" name="purok_id" value="<?= $p['purok_id'] ?>">
                                    <select name="risk_classification" class="form-control" style="width: 130px; padding: 4px 6px; font-size: 0.78rem;">
                                        <option value="High" <?= $p['risk_classification'] === 'High' ? 'selected' : '' ?>>High (Pink)</option>
                                        <option value="Medium" <?= $p['risk_classification'] === 'Medium' ? 'selected' : '' ?>>Medium (Orange)</option>
                                        <option value="Low" <?= $p['risk_classification'] === 'Low' ? 'selected' : '' ?>>Low (Yellow)</option>
                                        <option value="Pending Verification" <?= $p['risk_classification'] === 'Pending Verification' ? 'selected' : '' ?>>Pending Verif.</option>
                                    </select>
                                    <input type="text" name="notes" class="form-control" placeholder="Update notes" value="<?= htmlspecialchars($p['notes'] ?? '') ?>" style="width: 140px; padding: 4px 6px; font-size: 0.78rem;">
                                    <button type="submit" class="btn btn-outline btn-sm" title="Save Correction">
                                        <i class="fa fa-check"></i>
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

<script>
    let currentMapMode = 'interactive';

    function toggleMapMode() {
        const interactiveBox = document.getElementById('interactiveMapContainer');
        const scannedBox = document.getElementById('scannedMapContainer');
        const btn = document.getElementById('btnToggleMapMode');

        if (currentMapMode === 'interactive') {
            interactiveBox.style.display = 'none';
            scannedBox.style.display = 'block';
            btn.innerHTML = '<i class="fa fa-map-location-dot"></i> Switch to Live Leaflet Map';
            currentMapMode = 'scanned';
        } else {
            scannedBox.style.display = 'none';
            interactiveBox.style.display = 'block';
            btn.innerHTML = '<i class="fa fa-image"></i> Switch to CamScanner Scanned Map';
            currentMapMode = 'interactive';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.initHazardMap === 'function') {
            window.initHazardMap('interactiveHazardMap', { zoom: 15 });
        }
    });
</script>
