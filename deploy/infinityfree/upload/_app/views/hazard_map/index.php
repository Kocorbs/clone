<!-- views/hazard_map/index.php -->
<?php use App\Core\View; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary-dark);">Barangay Hinaplanon Hazard Map & Risk Zones</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">
            Digitized spatial risk classification derived from the official CamScanner handwritten hazard map.
        </p>
    </div>
</div>

<div class="alert alert-info">
    <i class="fa fa-circle-info"></i>
    <div>
        <strong>Thesis Scope & Geographical Limitations:</strong> This system uses stored Purok risk records and reference-map viewing rather than a full GIS platform. In accordance with thesis limitations (Chapter 1), full satellite tracking and GIS polygons are excluded. Ambiguous multi-zone boundaries are marked as <strong>Pending Verification</strong> for official review.
    </div>
</div>

<!-- Scanned Hazard Map Display Card -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-map"></i> Official Reference Map: Barangay Hinaplanon (CamScanner)</div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">Source: Barangay Hinaplanon BDRRMC Records</div>
    </div>
    <div class="card-body">
        <div class="hazard-map-viewer">
            <img src="assets/images/hinaplanon_hazard_map.jpg" alt="Barangay Hinaplanon Hazard Map">
        </div>

        <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center; padding: 12px; background: #f8fafc; border-radius: 6px; border: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem;">
                <span style="display: inline-block; width: 16px; height: 16px; background-color: var(--hazard-pink); border-radius: 3px;"></span>
                <strong>High Risk (Pink):</strong> Riverbank & coastal zones (Sendong inundation)
            </div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem;">
                <span style="display: inline-block; width: 16px; height: 16px; background-color: var(--hazard-orange); border-radius: 3px;"></span>
                <strong>Medium Risk (Orange):</strong> Inland drainage & creek buffers
            </div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem;">
                <span style="display: inline-block; width: 16px; height: 16px; background-color: var(--hazard-yellow); border-radius: 3px;"></span>
                <strong>Low Risk (Yellow):</strong> Designated safe & evacuation sectors
            </div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem;">
                <span style="display: inline-block; width: 16px; height: 16px; background-color: var(--hazard-gray); border-radius: 3px;"></span>
                <strong>Pending Verification (Gray):</strong> Overlapping boundary sectors
            </div>
        </div>
    </div>
</div>

<!-- Purok Risk Table & Correction Interface -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-table-list"></i> Purok Risk Classifications (Input Features to Decision Tree)</div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">Total: <?= count($puroks) ?> Hinaplanon Puroks</div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Purok Name</th>
                    <th>Risk Classification</th>
                    <th>Hazard Color Zone</th>
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
                        <td>
                            <div style="font-size: 0.85rem;"><?= htmlspecialchars($p['notes'] ?? '—') ?></div>
                        </td>
                        <td>
                            <small style="color: var(--text-muted); font-family: monospace;"><?= htmlspecialchars($p['hazard_source_reference']) ?></small>
                        </td>
                        <?php if (in_array($currentRole, ['Barangay Head', 'ICDRRMO'])): ?>
                            <td>
                                <form action="index.php?route=hazard-map-update" method="POST" style="display: flex; gap: 4px; align-items: center;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <input type="hidden" name="purok_id" value="<?= $p['purok_id'] ?>">
                                    <select name="risk_classification" class="form-control" style="width: 140px; padding: 4px 6px; font-size: 0.78rem;">
                                        <option value="High" <?= $p['risk_classification'] === 'High' ? 'selected' : '' ?>>High (Pink)</option>
                                        <option value="Medium" <?= $p['risk_classification'] === 'Medium' ? 'selected' : '' ?>>Medium (Orange)</option>
                                        <option value="Low" <?= $p['risk_classification'] === 'Low' ? 'selected' : '' ?>>Low (Yellow)</option>
                                        <option value="Pending Verification" <?= $p['risk_classification'] === 'Pending Verification' ? 'selected' : '' ?>>Pending Verif.</option>
                                    </select>
                                    <input type="text" name="notes" class="form-control" placeholder="Update notes" value="<?= htmlspecialchars($p['notes'] ?? '') ?>" style="width: 160px; padding: 4px 6px; font-size: 0.78rem;">
                                    <button type="submit" class="btn btn-primary btn-sm" title="Save verified correction">
                                        <i class="fa fa-save"></i>
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
