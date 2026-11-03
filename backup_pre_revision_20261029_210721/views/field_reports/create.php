<!-- views/field_reports/create.php -->
<div class="card" style="max-width: 700px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-paper-plane"></i> Submit Responder Field Status Update</div>
    </div>
    <div class="card-body">
        <form action="index.php?route=field-reports-create" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Affected Purok Location *</label>
                    <select name="purok_id" class="form-control" required>
                        <option value="">-- Select Purok --</option>
                        <?php foreach ($puroks as $p): ?>
                            <option value="<?= $p['purok_id'] ?>">
                                <?= htmlspecialchars($p['purok_name']) ?> (Risk: <?= htmlspecialchars($p['risk_classification']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Link to Disaster Request (Optional)</label>
                    <select name="disaster_request_id" class="form-control">
                        <option value="">-- Standalone Field Report --</option>
                        <?php foreach ($activeRequests as $ar): ?>
                            <option value="<?= $ar['disaster_request_id'] ?>">
                                Req #<?= $ar['disaster_request_id'] ?> — <?= htmlspecialchars($ar['disaster_type']) ?> (<?= htmlspecialchars($ar['purok_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Road & Bridge Accessibility *</label>
                    <select name="road_accessibility" class="form-control" required>
                        <option value="Passable">Passable (All vehicles)</option>
                        <option value="Passable to Heavy Vehicles Only">Passable to Heavy Vehicles / 6x6 Trucks Only</option>
                        <option value="Flooded">Flooded / Water Over Roadway</option>
                        <option value="Not Passable / Blocked">Not Passable / Blocked (Debris / Collapsed)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Casualties / Injured Count</label>
                    <input type="number" name="casualties_count" class="form-control" min="0" value="0">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Damaged / Inundated Houses Count</label>
                    <input type="number" name="damaged_houses" class="form-control" min="0" value="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Immediate Urgent Needs</label>
                    <input type="text" name="urgent_needs" class="form-control" placeholder="e.g. Inflatable boat, life vests, drinking water">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Detailed Ground Situational Update *</label>
                <textarea name="status_update" class="form-control" rows="4" placeholder="Report water level marks, evacuation flow, trapped victims, bridge stability, power outages..." required></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Field Latitude (Optional GPS)</label>
                    <input type="number" step="any" name="latitude" class="form-control" placeholder="8.2412">
                </div>
                <div class="form-group">
                    <label class="form-label">Field Longitude (Optional GPS)</label>
                    <input type="number" step="any" name="longitude" class="form-control" placeholder="124.2543">
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Submit Field Report</button>
                <a href="index.php?route=field-reports" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
