<!-- views/sos/confirmation.php -->
<?php use App\Core\View; ?>

<div class="card" style="max-width: 650px; margin: 30px auto; text-align: center; border-top: 5px solid var(--danger);">
    <div class="card-body" style="padding: 36px 28px;">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 16px auto;">
            <i class="fa fa-life-ring"></i>
        </div>

        <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 6px;">
            Emergency SOS Transmitted Successfully
        </h2>
        <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 20px;">
            Your emergency report has been received by the Barangay Hinaplanon BDRRMC Operations Center.
        </p>

        <!-- Reference Callout -->
        <div style="background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px; padding: 18px; margin-bottom: 24px;">
            <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted); letter-spacing: 0.05em;">
                OFFICIAL EMERGENCY TRACKING REFERENCE
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--danger); font-family: monospace; margin: 6px 0;">
                <?= htmlspecialchars($incident['reference_no']) ?>
            </div>
            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                Status: <?= View::statusBadge($incident['status']) ?> | Location: <strong><?= htmlspecialchars($incident['purok_name']) ?></strong>
            </div>
        </div>

        <!-- Safety Guidelines Box -->
        <div style="text-align: left; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 16px; margin-bottom: 24px; font-size: 0.85rem;">
            <strong style="color: #92400e; display: block; margin-bottom: 8px;">
                <i class="fa fa-shield-halved"></i> Safety Actions While Awaiting Responders:
            </strong>
            <ul style="margin-left: 20px; line-height: 1.6; color: #78350f;">
                <li>Keep your mobile phone line (<strong><?= htmlspecialchars($incident['contact_number'] ?? 'on file') ?></strong>) open for incoming calls from rescuers.</li>
                <li>If water is rising rapidly, move to higher ground or second-floor rooftop. Do not wade into swift flood currents.</li>
                <li>Wave a brightly colored cloth, flashlight, or mirror toward open corridors if visually signaling rescuers.</li>
                <li>Ensure children and elderly family members wear life vests or improvised flotation devices.</li>
            </ul>
        </div>

        <div style="display: flex; gap: 10px; justify-content: center;">
            <a href="index.php?route=disaster-requests-show&id=<?= $incident['disaster_request_id'] ?>" class="btn btn-primary">
                <i class="fa fa-eye"></i> Track Incident Progress
            </a>
            <a href="index.php?route=dashboard" class="btn btn-outline">
                <i class="fa fa-home"></i> Return to Dashboard
            </a>
        </div>
    </div>
</div>
