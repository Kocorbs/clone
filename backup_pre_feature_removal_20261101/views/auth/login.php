<!-- views/auth/login.php -->
<div class="auth-split-wrapper">
    <!-- Left Section: Authentication Form -->
    <div class="auth-split-left">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, #2563eb, #1e3a8a); display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 1.4rem; box-shadow: 0 4px 10px rgba(37,99,235,0.3);">
                    <i class="fa fa-shield-halved"></i>
                </div>
                <div>
                    <h1 style="font-size: 1.25rem; font-weight: 800; color: var(--primary-dark); line-height: 1.2;">
                        Hinaplanon DRRM
                    </h1>
                    <div style="font-size: 0.76rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                        Disaster Preparedness & AI Allocation
                    </div>
                </div>
            </div>

            <!-- Active Public Alert Callout -->
            <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px 14px; margin-bottom: 20px; font-size: 0.82rem; color: #1e40af; display: flex; align-items: flex-start; gap: 10px;">
                <i class="fa fa-circle-exclamation" style="margin-top: 2px;"></i>
                <div>
                    <strong>Mandulog River Basin Monitoring:</strong> High-risk riverbank zones are actively monitored. Verify your Purok risk classification on the interactive map.
                </div>
            </div>

            <!-- Flash Alert Messages -->
            <?php if (!empty($flashes)): ?>
                <?php foreach ($flashes as $type => $messages): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="alert alert-<?= htmlspecialchars($type) ?>" style="padding: 10px 14px; font-size: 0.84rem;">
                            <i class="fa fa-circle-info" style="margin-top: 2px;"></i>
                            <div><?= htmlspecialchars($msg) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <form action="index.php?route=login" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="form-group">
                    <label for="identifier" class="form-label">Barangay ID Number or Email *</label>
                    <div style="position: relative;">
                        <input type="text" id="identifier" name="identifier" class="form-control" placeholder="e.g. RES-2026-001 or official email" required autofocus style="padding-left: 36px;">
                        <i class="fa fa-id-card" style="position: absolute; left: 12px; top: 11px; color: var(--text-muted);"></i>
                    </div>
                    <small style="color: var(--text-muted); font-size: 0.74rem;">Residents sign in with Barangay ID. Officials use official ID or email.</small>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <label for="password" class="form-label" style="margin-bottom: 0;">Password *</label>
                        <a href="index.php?route=forgot-password" style="font-size: 0.76rem; color: var(--primary-light); font-weight: 600;">Forgot Password?</a>
                    </div>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required style="padding-left: 36px; padding-right: 40px;">
                        <i class="fa fa-lock" style="position: absolute; left: 12px; top: 11px; color: var(--text-muted);"></i>
                        <button type="button" id="togglePassword" style="position: absolute; right: 10px; top: 8px; background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px;" title="Show/Hide Password">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 11px; font-weight: 700; margin-top: 10px;">
                    <i class="fa fa-right-to-bracket"></i> Sign In to Command Portal
                </button>
            </form>

            <div style="text-align: center; margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--border-light); font-size: 0.85rem;">
                New Hinaplanon Resident? 
                <a href="index.php?route=register" style="font-weight: 700; color: var(--primary-light);">
                    Register with Barangay ID
                </a>
            </div>
        </div>

        <!-- Demo Accounts Quick Fill Helpers -->
        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px dashed var(--border-light); font-size: 0.78rem;">
            <div style="font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                <i class="fa fa-key text-warning"></i> Role Login Quick-Fill (Click to auto-fill):
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                <span class="btn btn-outline btn-sm" data-demo-identifier="icdrrmo@iligan.gov.ph" data-demo-pass="icdrrmo123" style="cursor: pointer;">
                    <i class="fa fa-user-shield text-danger"></i> ICDRRMO
                </span>
                <span class="btn btn-outline btn-sm" data-demo-identifier="brgyhead@hinaplanon.gov.ph" data-demo-pass="brgyhead123" style="cursor: pointer;">
                    <i class="fa fa-user-tie text-warning"></i> Barangay Head
                </span>
                <span class="btn btn-outline btn-sm" data-demo-identifier="responder1@hinaplanon.gov.ph" data-demo-pass="responder123" style="cursor: pointer;">
                    <i class="fa fa-truck-medical text-success"></i> Responder
                </span>
                <span class="btn btn-outline btn-sm" data-demo-identifier="RES-2026-001" data-demo-pass="resident123" style="cursor: pointer;">
                    <i class="fa fa-house-user text-primary"></i> Resident (Brgy ID)
                </span>
            </div>
        </div>
    </div>

    <!-- Right Section: Interactive Leaflet.js Public Hazard Map -->
    <div class="auth-split-right">
        <!-- Floating Information & Legend Card -->
        <div class="map-floating-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <strong style="font-size: 0.88rem; color: var(--primary-dark);">
                    <i class="fa fa-map-location-dot text-primary"></i> Hinaplanon Hazard Map
                </strong>
                <span class="badge badge-status-approved" style="font-size: 0.68rem;">Live Data</span>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 10px;">
                Spatial hazard zones & verified evacuation shelters in Barangay Hinaplanon, Iligan City.
            </div>

            <!-- Map Legend -->
            <div style="display: flex; flex-direction: column; gap: 5px; font-size: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="width: 14px; height: 14px; border-radius: 3px; background: var(--hazard-pink); display: inline-block;"></span>
                    <span><strong>High Risk:</strong> Riverbank / Sendong zone</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="width: 14px; height: 14px; border-radius: 3px; background: var(--hazard-orange); display: inline-block;"></span>
                    <span><strong>Medium Risk:</strong> Inland drainage buffer</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="width: 14px; height: 14px; border-radius: 3px; background: var(--hazard-yellow); display: inline-block;"></span>
                    <span><strong>Low Risk:</strong> Safe evacuation areas</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="width: 14px; height: 14px; border-radius: 50%; background: #16a34a; display: inline-block; color: #fff; font-size: 8px; text-align: center; line-height: 14px;">
                        <i class="fa fa-house"></i>
                    </span>
                    <span><strong>Evacuation Shelter:</strong> Capacity & occupancy</span>
                </div>
            </div>

            <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed var(--border-light); font-size: 0.7rem; color: var(--text-muted);">
                <i class="fa fa-shield-halved"></i> <em>Public View: Exact household coordinates protected.</em>
            </div>
        </div>

        <!-- Leaflet Map Container -->
        <div id="loginPublicMap"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Public Leaflet Map
    if (typeof window.initHazardMap === 'function') {
        window.initHazardMap('loginPublicMap', { zoom: 14 });
    }
});
</script>
