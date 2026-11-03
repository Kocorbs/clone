<!-- views/disaster_requests/create.php -->
<?php
use App\Core\Auth;
$currentRole = Auth::role();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 12px; max-width: 780px; margin-left: auto; margin-right: auto;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
            <i class="fa fa-file-circle-plus text-primary"></i> Fill Up Disaster Request Form
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
            Official disaster incident logging and relief assistance intake for automated Decision Tree ML prioritization.
        </p>
    </div>
    <?php if (in_array($currentRole, ['Barangay Head', 'ICDRRMO'])): ?>
        <div style="display: flex; gap: 8px;">
            <a href="index.php?route=disaster-requests" class="btn btn-outline btn-sm">
                <i class="fa fa-list-check"></i> View Request Records
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="card" style="max-width: 780px; margin: 0 auto; border-top: 4px solid var(--primary-light);">
    <div class="card-header" style="background: #f8fafc;">
        <div class="card-title"><i class="fa fa-file-signature text-primary"></i> Incident Details & Resource Intake</div>
        <span class="badge badge-primary"><i class="fa fa-bolt"></i> Real-time AI Classification</span>
    </div>
    <div class="card-body">
        <form action="index.php?route=disaster-requests-create" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Affected Purok (Barangay Hinaplanon) *</label>
                    <select name="purok_id" id="purok_id" class="form-control" required>
                        <option value="">-- Select Affected Purok --</option>
                        <?php foreach ($puroks as $p): ?>
                            <option value="<?= $p['purok_id'] ?>">
                                <?= htmlspecialchars($p['purok_name']) ?> (Risk: <?= htmlspecialchars($p['risk_classification']) ?> / <?= htmlspecialchars($p['risk_zone_color']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Incident / Request Date *</label>
                    <input type="date" name="disaster_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Disaster Type *</label>
                    <select name="disaster_type" class="form-control" required>
                        <option value="Flood">Flood (Riverbank Overflow)</option>
                        <option value="Flash Flood">Flash Flood</option>
                        <option value="Typhoon">Typhoon / Strong Winds</option>
                        <option value="Landslide">Landslide / Soil Erosion</option>
                        <option value="Fire">Community Fire</option>
                        <option value="Storm Surge">Storm Surge</option>
                        <option value="Other">Other Emergency</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Weather Condition *</label>
                    <input type="text" name="weather_name" class="form-control" placeholder="e.g. Heavy Continuous Rain / Shear Line" value="Heavy Rain" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Severity Level *</label>
                    <select name="severity_level" class="form-control" required>
                        <option value="Critical">Critical (Severe flooding / structural collapse)</option>
                        <option value="High" selected>High (Significant inundation / water rising)</option>
                        <option value="Medium">Medium (Moderate water / accessibility issues)</option>
                        <option value="Low">Low (Precautionary monitoring)</option>
                    </select>
                </div>

                <div class="form-group" style="display: flex; align-items: center; padding-top: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; font-size: 0.9rem;">
                        <input type="checkbox" name="is_urgent" value="1" style="width: 18px; height: 18px;">
                        <span style="color: var(--danger);"><i class="fa fa-bolt"></i> Mark as High Urgency (Immediate Rescue / Evacuation)</span>
                    </label>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Affected Households (HH) *</label>
                    <input type="number" id="affected_households" name="affected_households" class="form-control" min="1" value="25" required>
                    <small style="color: var(--text-muted); font-size: 0.75rem;">Used for demand calculation (1 pack per household ratio).</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Affected Population (Est. Individuals) *</label>
                    <input type="number" id="affected_population" name="affected_population" class="form-control" min="1" value="100" required>
                    <small style="color: var(--text-muted); font-size: 0.75rem;">Auto-calculated as 4x households; editable.</small>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Disaster Description & Field Observations *</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Describe the water level, river swelling, road conditions, trapped families, or urgent relief needs..." required></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Latitude (Optional Geotag)</label>
                    <input type="number" step="any" name="latitude" class="form-control" placeholder="e.g. 8.2412">
                </div>
                <div class="form-group">
                    <label class="form-label">Longitude (Optional Geotag)</label>
                    <input type="number" step="any" name="longitude" class="form-control" placeholder="e.g. 124.2543">
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 18px;">
                    <i class="fa fa-paper-plane"></i> Submit Disaster Request
                </button>
                <a href="index.php?route=disaster-requests" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
