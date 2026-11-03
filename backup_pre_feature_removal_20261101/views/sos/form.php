<!-- views/sos/form.php -->
<div class="card" style="max-width: 720px; margin: 0 auto; border-top: 5px solid var(--danger);">
    <div class="card-header" style="background: #fef2f2;">
        <div class="card-title" style="color: var(--danger); font-size: 1.15rem;">
            <i class="fa fa-life-ring"></i> Emergency SOS Assistance Request
        </div>
        <span class="badge badge-priority-urgent">HIGH PRIORITY CHANNEL</span>
    </div>
    <div class="card-body">
        <!-- Emergency Fallback Advisory -->
        <div style="background-color: #fee2e2; border: 1px solid #fecaca; border-radius: 8px; padding: 14px; margin-bottom: 20px; font-size: 0.88rem; color: #991b1b;">
            <div style="font-weight: 800; font-size: 0.95rem; margin-bottom: 4px;">
                <i class="fa fa-phone-volume"></i> Immediate Life-Safety Notice:
            </div>
            Submitting this SOS notifies the BDRRMC command center and staging rescuers. If you or your family are in immediate physical danger from rapid river swelling, also call the <strong>Iligan City 911 Hotline</strong> or <strong>Hinaplanon BDRRM Hotline: 0917-555-0199</strong>.
        </div>

        <form action="index.php?route=emergency-sos" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Emergency / Hazard Type *</label>
                    <select name="disaster_type" class="form-control" required>
                        <option value="Flash Flood">Flash Flood / Rapid River Swell</option>
                        <option value="Flood">Trapped by Floodwaters</option>
                        <option value="Landslide">Landslide / Slope Collapse</option>
                        <option value="Medical Emergency">Disaster Medical Casualty / Injury</option>
                        <option value="Structural Damage">Collapsed Roof / Structure</option>
                        <option value="Fire">Emergency Fire</option>
                        <option value="Other">Other Urgent Calamity</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Your Purok Location *</label>
                    <select name="purok_id" id="purok_id" class="form-control" required>
                        <option value="">-- Select Your Purok --</option>
                        <?php foreach ($puroks as $p): ?>
                            <option value="<?= $p['purok_id'] ?>" <?= ($user['purok_id'] ?? null) == $p['purok_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['purok_name']) ?> (<?= htmlspecialchars($p['risk_classification']) ?> Hazard)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Number of Persons Trapped / In Need *</label>
                    <input type="number" name="affected_persons" class="form-control" min="1" max="100" value="4" required>
                    <small style="color: var(--text-muted); font-size: 0.74rem;">Include elderly, children, or disabled individuals.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Emergency Contact Phone Number *</label>
                    <input type="tel" name="contact_number" class="form-control" value="<?= htmlspecialchars($user['cellphone_number'] ?? '') ?>" placeholder="0917-000-0000" required>
                    <small style="color: var(--text-muted); font-size: 0.74rem;">Responders will dial this number for directions.</small>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Describe Exact Location & Immediate Danger *</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Provide landmarks, house color, water height (e.g. knee-deep, chest-deep, on rooftop), if any person is injured..." required></textarea>
            </div>

            <!-- Optional GPS Geolocation Pin -->
            <div style="background: #f8fafc; border: 1px solid var(--border-light); border-radius: 8px; padding: 12px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label class="form-label" style="margin-bottom: 0;">GPS Location Pin (Optional):</label>
                    <button type="button" id="btnGetLocation" class="btn btn-outline btn-sm">
                        <i class="fa fa-crosshairs text-primary"></i> Detect My Current Coordinates
                    </button>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <input type="number" step="any" id="latInput" name="latitude" class="form-control" placeholder="Latitude (e.g. 8.2412)">
                    <input type="number" step="any" id="lngInput" name="longitude" class="form-control" placeholder="Longitude (e.g. 124.2543)">
                </div>
                <div id="geoNotice" style="font-size: 0.74rem; color: var(--text-muted); margin-top: 4px;"></div>
            </div>

            <button type="submit" class="btn btn-danger btn-lg" style="width: 100%; justify-content: center; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);">
                <i class="fa fa-paper-plane"></i> TRANSMIT EMERGENCY SOS NOW
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnGetLocation');
    const latIn = document.getElementById('latInput');
    const lngIn = document.getElementById('lngInput');
    const notice = document.getElementById('geoNotice');

    if (btn && navigator.geolocation) {
        btn.addEventListener('click', function () {
            notice.textContent = "Acquiring GPS location...";
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    latIn.value = pos.coords.latitude.toFixed(6);
                    lngIn.value = pos.coords.longitude.toFixed(6);
                    notice.innerHTML = "<span style='color:var(--success);'><i class='fa fa-check'></i> GPS Coordinates detected successfully.</span>";
                },
                function (err) {
                    notice.innerHTML = "<span style='color:var(--danger);'><i class='fa fa-triangle-exclamation'></i> Could not detect GPS. Responders will navigate by Purok and description.</span>";
                },
                { timeout: 10000, enableHighAccuracy: true }
            );
        });
    }
});
</script>
