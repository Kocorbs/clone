<!-- views/notifications/index.php -->
<?php 
use App\Core\View;
use App\Models\Beneficiary;

$residentName = trim(($user['firstname'] ?? 'Resident') . ' ' . ($user['lastname'] ?? ''));
$purokName = $user['purok_name'] ?? 'Barangay Hinaplanon';
$status = $myBeneficiary['assistance_status'] ?? 'Pending';
$priority = $myBeneficiary['priority_level'] ?? 'Not Yet Assessed';
$isApproved = !empty($myBeneficiary['is_approved']);

// Helper for status badge styling
if (!function_exists('getStatusBadge')) {
    function getStatusBadge(string $status): string {
        switch ($status) {
            case 'Eligible':
                return '<span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-weight:700;"><i class="fa fa-circle-check"></i> Eligible</span>';
            case 'Pending':
                return '<span class="badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-weight:700;"><i class="fa fa-clock"></i> Pending Review</span>';
            case 'Approved':
                return '<span class="badge" style="background:#e0e7ff; color:#4338ca; border:1px solid #c7d2fe; font-weight:700;"><i class="fa fa-shield-check"></i> Approved</span>';
            case 'Ready to Claim':
                return '<span class="badge" style="background:#fef08a; color:#854d0e; border:1px solid #fde047; font-weight:700;"><i class="fa fa-box-open"></i> Ready to Claim</span>';
            case 'Claimed':
                return '<span class="badge" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; font-weight:700;"><i class="fa fa-box-archive"></i> Claimed</span>';
            case 'Not Eligible':
                return '<span class="badge" style="background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; font-weight:700;"><i class="fa fa-circle-xmark"></i> Not Eligible</span>';
            default:
                return '<span class="badge" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-weight:600;">' . htmlspecialchars($status) . '</span>';
        }
    }
}

// Helper for priority badge styling
if (!function_exists('getPriorityBadge')) {
    function getPriorityBadge(string $priority): string {
        switch ($priority) {
            case 'High':
            case 'Urgent':
                return '<span class="badge" style="background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; font-weight:700;"><i class="fa fa-triangle-exclamation"></i> High Priority</span>';
            case 'Medium':
                return '<span class="badge" style="background:#fef3c7; color:#92400e; border:1px solid #fcd34d; font-weight:700;"><i class="fa fa-circle-exclamation"></i> Medium Priority</span>';
            case 'Low':
                return '<span class="badge" style="background:#dcfce7; color:#166534; border:1px solid #86efac; font-weight:700;"><i class="fa fa-circle-info"></i> Low Priority</span>';
            case 'Not Yet Assessed':
            default:
                return '<span class="badge" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-weight:600;"><i class="fa fa-hourglass-half"></i> Not Yet Assessed</span>';
        }
    }
}
?>

<style>
.hub-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.9fr) minmax(0, 1.1fr);
    gap: 20px;
    align-items: start;
}
@media (max-width: 992px) {
    .hub-grid {
        grid-template-columns: 1fr !important;
    }
}
.hub-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 2px 6px -1px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 20px;
}
.hub-card-header {
    padding: 14px 18px;
    background: #f8fafc;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
.hub-card-title {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
}
.hub-card-body {
    padding: 18px;
}
.beneficiary-table th {
    background: #f1f5f9;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 10px 12px;
    border-bottom: 1px solid #e2e8f0;
}
.beneficiary-table td {
    padding: 11px 12px;
    font-size: 0.85rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}
.highlight-row {
    background-color: #f0fdf4 !important;
    border-left: 4px solid #10b981 !important;
}
@media (max-width: 640px) {
    .tracker-steps {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 14px !important;
    }
}
@keyframes fadeInModal {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
</style>

<!-- Top Hub Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <h2 style="font-size: 1.45rem; font-weight: 800; color: #064e3b; margin: 0; display: flex; align-items: center; gap: 10px;">
                <i class="fa fa-hand-holding-heart text-success"></i> 
                Relief Goods & Community Preparedness Hub
            </h2>
            <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
                <span class="badge badge-danger" style="font-size: 0.78rem;">
                    <?= (int)$unreadCount ?> Unread Alert<?= $unreadCount > 1 ? 's' : '' ?>
                </span>
            <?php endif; ?>
        </div>
        <p style="font-size: 0.85rem; color: #475569; margin-top: 4px; margin-bottom: 0;">
            Barangay Hinaplanon Citizen Welfare Portal • Relief Allocation Tracking, Ayuda Claiming & Emergency Advisories
        </p>
    </div>

    <div style="display: flex; gap: 8px; align-items: center;">
        <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
            <form action="index.php?route=notifications-mark-all" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                <button type="submit" class="btn btn-outline btn-sm" style="color: #047857; border-color: #a7f3d0; background: #ecfdf5;">
                    <i class="fa fa-envelope-open"></i> Mark Alerts Read
                </button>
            </form>
        <?php endif; ?>
        <a href="index.php?route=dashboard" class="btn btn-outline btn-sm">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<!-- ============================================== -->
<!-- 1. WELCOME BANNER (Personalized & Responsive)  -->
<!-- ============================================== -->
<div style="background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 50%, #f0f8f7 100%); border: 1px solid #bbf7d0; border-radius: 14px; padding: 20px 24px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div style="flex: 1; min-width: 280px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="font-size: 1.25rem; font-weight: 800; color: #065f46;">
                    Mabuhay, <?= htmlspecialchars($residentName) ?>!
                </span>
                <span class="badge" style="background: #ffffff; color: #047857; border: 1px solid #a7f3d0; font-size: 0.78rem;">
                    <i class="fa fa-map-pin"></i> <?= htmlspecialchars($purokName) ?>
                </span>
            </div>

            <!-- Personalized Program Inclusion Message -->
            <?php if ($status === 'Eligible'): ?>
                <div style="font-size: 0.92rem; color: #047857; line-height: 1.5;">
                    <strong style="color: #065f46;"><i class="fa fa-circle-check"></i> Officially Included in Active Relief Program:</strong>
                    Your household is approved to claim official relief assistance for this relief cycle. Please review your designated schedule below and click <strong>Claim Ayuda</strong> to confirm your slot.
                </div>
            <?php elseif ($status === 'Pending'): ?>
                <div style="font-size: 0.92rem; color: #92400e; line-height: 1.5;">
                    <strong style="color: #78350f;"><i class="fa fa-clock"></i> Enrollment Verification in Progress:</strong>
                    Your household registration and claim request are currently undergoing validation by Barangay Officials and the BDRRMC triage committee. Your status will update once claiming eligibility is confirmed.
                </div>
            <?php elseif ($status === 'Approved'): ?>
                <div style="font-size: 0.92rem; color: #4338ca; line-height: 1.5;">
                    <strong style="color: #312e81;"><i class="fa fa-shield-check"></i> Relief Claim Officially Approved:</strong>
                    Your household claim request has been verified and approved by Barangay Officials. Your relief goods are being prepared and will be staged at your assigned pickup station.
                </div>
            <?php elseif ($status === 'Ready to Claim'): ?>
                <div style="font-size: 0.92rem; color: #854d0e; line-height: 1.5;">
                    <strong style="color: #713f12;"><i class="fa fa-box-open"></i> Relief Package Ready for Pickup:</strong>
                    Your allocated family relief pack is staged and ready for pickup at your designated station. Please bring a valid ID and present your details during your assigned claiming window.
                </div>
            <?php elseif ($status === 'Claimed'): ?>
                <div style="font-size: 0.92rem; color: #0284c7; line-height: 1.5;">
                    <strong style="color: #0369a1;"><i class="fa fa-box-archive"></i> Relief Package Successfully Claimed:</strong>
                    Our records confirm your household received allocated relief goods on <?= !empty($myBeneficiary['claimed_at']) ? date('M d, Y h:i A', strtotime($myBeneficiary['claimed_at'])) : 'schedule' ?>. Thank you for coordinating with the BDRRMC distribution desk.
                </div>
            <?php else: ?>
                <div style="font-size: 0.92rem; color: #991b1b; line-height: 1.5;">
                    <strong style="color: #7f1d1d;"><i class="fa fa-circle-info"></i> Notice Regarding Relief Cycle:</strong>
                    Your sector is currently outside the immediate evacuation or disaster hazard perimeter. If your household requires emergency assistance, please inform your Purok Leader.
                </div>
            <?php endif; ?>
        </div>

        <div style="text-align: right; min-width: 200px;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #047857; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 4px;">
                PROGRAM ENROLLMENT STATUS
            </div>
            <div>
                <?= getStatusBadge($status) ?>
            </div>
            <div style="font-size: 0.78rem; color: #64748b; margin-top: 6px;">
                Priority: <strong><?= htmlspecialchars($priority) ?></strong>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MAIN TWO-COLUMN DASHBOARD GRID                 -->
<!-- ============================================== -->
<div class="hub-grid">

    <!-- ============================================== -->
    <!-- LEFT COLUMN: WELFARE, AYUDA & INVENTORY        -->
    <!-- ============================================== -->
    <div style="display: flex; flex-direction: column;">

        <!-- 3. MY AYUDA PRIORITY & CLAIMING STATUS -->
        <div class="hub-card" style="border-top: 4px solid #10b981;">
            <div class="hub-card-header" style="background: #ffffff;">
                <div class="hub-card-title" style="color: #065f46;">
                    <i class="fa fa-hand-holding-heart text-success" style="font-size: 1.15rem;"></i> 
                    My Ayuda Priority & Claiming Status
                </div>
                <div>
                    <?= getPriorityBadge($priority) ?>
                </div>
            </div>

            <div class="hub-card-body">
                <!-- Actionable CTA Banner for Eligible Residents -->
                <?php if ($status === 'Eligible'): ?>
                    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1.5px solid #86efac; border-radius: 12px; padding: 18px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                        <div style="flex: 1; min-width: 260px;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <span class="badge" style="background: #15803d; color: #ffffff; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;">Ready to Claim</span>
                                <strong style="color: #14532d; font-size: 1.05rem;">Confirm & Claim Your Relief Assistance</strong>
                            </div>
                            <p style="color: #166534; font-size: 0.88rem; margin: 0; line-height: 1.45;">
                                Your household is approved to receive relief supplies. Click <strong>Claim Ayuda</strong> below to verify your pickup station, agree to distribution guidelines, and submit your official claim.
                            </p>
                        </div>
                        <div>
                            <button type="button" class="btn btn-success" onclick="openClaimAyudaModal()" style="font-weight: 800; font-size: 0.98rem; padding: 12px 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35); display: inline-flex; align-items: center; gap: 8px; cursor: pointer; border: none;">
                                <i class="fa fa-hand-holding-heart" style="font-size: 1.15rem;"></i> Claim Ayuda
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                // Determine step states for the 4-stage Ayuda Claim Lifecycle Tracker:
                // 0 = Inactive / Upcoming, 1 = Current / Active, 2 = Completed
                $step1State = 0;
                $step2State = 0;
                $step3State = 0;
                $step4State = 0;

                if ($status === 'Pending') {
                    $step1State = 1;
                } elseif ($status === 'Approved') {
                    $step1State = 2;
                    $step2State = 1;
                } elseif ($status === 'Ready to Claim') {
                    $step1State = 2;
                    $step2State = 2;
                    $step3State = 1;
                } elseif ($status === 'Claimed') {
                    $step1State = 2;
                    $step2State = 2;
                    $step3State = 2;
                    $step4State = 2;
                } elseif ($status === 'Eligible') {
                    $step1State = 0;
                }
                ?>

                <!-- 4-Stage Ayuda Claim Status Tracker -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
                        <div style="font-size: 0.82rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; display: flex; align-items: center; gap: 8px;">
                            <i class="fa fa-timeline text-primary"></i> Ayuda Claim Lifecycle Tracker
                        </div>
                        <div>
                            <?= getStatusBadge($status) ?>
                        </div>
                    </div>

                    <!-- Tracker Stepper (Responsive 4-Stage Flow) -->
                    <div class="tracker-steps" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; position: relative;">
                        <!-- Step 1: Pending Review (Claim Submitted) -->
                        <div class="tracker-step" style="text-align: center; padding: 10px 6px;">
                            <div class="step-icon-wrap" style="width: 40px; height: 40px; border-radius: 50%; margin: 0 auto 8px auto; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; <?= $step1State === 2 ? 'background: #dcfce7; color: #15803d; border: 2px solid #86efac;' : ($step1State === 1 ? 'background: #fef3c7; color: #b45309; border: 2px solid #fde047; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);' : 'background: #f1f5f9; color: #94a3b8; border: 2px solid #cbd5e1;') ?>">
                                <i class="fa <?= $step1State === 2 ? 'fa-check' : 'fa-clipboard-check' ?>"></i>
                            </div>
                            <div style="font-weight: 700; font-size: 0.82rem; color: <?= $step1State === 1 ? '#b45309' : ($step1State === 2 ? '#15803d' : '#475569') ?>;">
                                1. Claim Submitted
                            </div>
                            <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">
                                <?= !empty($myBeneficiary['claim_requested_at']) ? date('M d, h:i A', strtotime($myBeneficiary['claim_requested_at'])) : ($status === 'Eligible' ? 'Awaiting action' : 'Pending review') ?>
                            </div>
                        </div>

                        <!-- Step 2: Approved (Barangay Verified) -->
                        <div class="tracker-step" style="text-align: center; padding: 10px 6px;">
                            <div class="step-icon-wrap" style="width: 40px; height: 40px; border-radius: 50%; margin: 0 auto 8px auto; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; <?= $step2State === 2 ? 'background: #dcfce7; color: #15803d; border: 2px solid #86efac;' : ($step2State === 1 ? 'background: #e0e7ff; color: #4338ca; border: 2px solid #a5b4fc; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);' : 'background: #f1f5f9; color: #94a3b8; border: 2px solid #cbd5e1;') ?>">
                                <i class="fa <?= $step2State === 2 ? 'fa-check' : 'fa-shield-check' ?>"></i>
                            </div>
                            <div style="font-weight: 700; font-size: 0.82rem; color: <?= $step2State === 1 ? '#4338ca' : ($step2State === 2 ? '#15803d' : '#475569') ?>;">
                                2. Barangay Approved
                            </div>
                            <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">
                                <?= !empty($myBeneficiary['verified_at']) ? date('M d, h:i A', strtotime($myBeneficiary['verified_at'])) : ($step2State === 1 ? 'Approved' : 'Official triage') ?>
                            </div>
                        </div>

                        <!-- Step 3: Ready to Claim (Staged for Pickup) -->
                        <div class="tracker-step" style="text-align: center; padding: 10px 6px;">
                            <div class="step-icon-wrap" style="width: 40px; height: 40px; border-radius: 50%; margin: 0 auto 8px auto; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; <?= $step3State === 2 ? 'background: #dcfce7; color: #15803d; border: 2px solid #86efac;' : ($step3State === 1 ? 'background: #fef08a; color: #854d0e; border: 2px solid #fde047; box-shadow: 0 0 0 4px rgba(234, 179, 8, 0.2);' : 'background: #f1f5f9; color: #94a3b8; border: 2px solid #cbd5e1;') ?>">
                                <i class="fa <?= $step3State === 2 ? 'fa-check' : 'fa-box-open' ?>"></i>
                            </div>
                            <div style="font-weight: 700; font-size: 0.82rem; color: <?= $step3State === 1 ? '#854d0e' : ($step3State === 2 ? '#15803d' : '#475569') ?>;">
                                3. Ready to Claim
                            </div>
                            <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">
                                <?= $step3State === 1 ? 'Staged at station' : ($step3State === 2 ? 'Staged' : 'Logistics prep') ?>
                            </div>
                        </div>

                        <!-- Step 4: Claimed & Dispensed -->
                        <div class="tracker-step" style="text-align: center; padding: 10px 6px;">
                            <div class="step-icon-wrap" style="width: 40px; height: 40px; border-radius: 50%; margin: 0 auto 8px auto; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; <?= $step4State === 2 ? 'background: #e0f2fe; color: #0369a1; border: 2px solid #7dd3fc; box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);' : 'background: #f1f5f9; color: #94a3b8; border: 2px solid #cbd5e1;' ?>">
                                <i class="fa fa-hand-holding-heart"></i>
                            </div>
                            <div style="font-weight: 700; font-size: 0.82rem; color: <?= $step4State === 2 ? '#0369a1' : '#475569' ?>;">
                                4. Ayuda Claimed
                            </div>
                            <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">
                                <?= !empty($myBeneficiary['claimed_at']) ? date('M d, h:i A', strtotime($myBeneficiary['claimed_at'])) : 'Distribution' ?>
                            </div>
                        </div>
                    </div>

                    <!-- Current Step Advisory Callouts -->
                    <?php if ($status === 'Pending'): ?>
                        <div style="margin-top: 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 14px; font-size: 0.84rem; color: #92400e; display: flex; align-items: flex-start; gap: 10px;">
                            <i class="fa fa-clock" style="color: #d97706; font-size: 1.1rem; margin-top: 2px;"></i>
                            <div>
                                <strong>Claim Request Under Review:</strong> Your claim request was submitted on <?= !empty($myBeneficiary['claim_requested_at']) ? date('M d, Y h:i A', strtotime($myBeneficiary['claim_requested_at'])) : 'today' ?>. The Barangay Head / BDRRMC triage committee is currently validating your household allocation. Duplicate claims are strictly prevented while a request is in progress.
                            </div>
                        </div>
                    <?php elseif ($status === 'Approved'): ?>
                        <div style="margin-top: 14px; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 8px; padding: 12px 14px; font-size: 0.84rem; color: #3730a3; display: flex; align-items: flex-start; gap: 10px;">
                            <i class="fa fa-shield-check" style="color: #4f46e5; font-size: 1.1rem; margin-top: 2px;"></i>
                            <div>
                                <strong>Claim Request Officially Approved!</strong> Your relief allocation has been verified by Barangay Officials. Personnel are now packaging and staging goods at your assigned station. Your status will change to <em>Ready to Claim</em> once supplies arrive at the desk.
                            </div>
                        </div>
                    <?php elseif ($status === 'Ready to Claim'): ?>
                        <div style="margin-top: 14px; background: #fefce8; border: 1px solid #fef08a; border-radius: 8px; padding: 12px 14px; font-size: 0.84rem; color: #854d0e; display: flex; align-items: flex-start; gap: 10px;">
                            <i class="fa fa-box-open" style="color: #ca8a04; font-size: 1.1rem; margin-top: 2px;"></i>
                            <div>
                                <strong>Your Relief Package is Ready for Pickup!</strong> Your supplies are currently staged at <strong><?= htmlspecialchars($myBeneficiary['claiming_location'] ?? 'Hinaplanon Covered Court') ?></strong>. Please present a valid government or barangay ID to the dispensing staff during your assigned claiming window.
                            </div>
                        </div>
                    <?php elseif ($status === 'Claimed'): ?>
                        <div style="margin-top: 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 14px; font-size: 0.84rem; color: #166534; display: flex; align-items: flex-start; gap: 10px;">
                            <i class="fa fa-circle-check" style="color: #16a34a; font-size: 1.1rem; margin-top: 2px;"></i>
                            <div>
                                <strong>Relief Goods Successfully Claimed & Released:</strong> Distributed on <?= !empty($myBeneficiary['claimed_at']) ? date('M d, Y h:i A', strtotime($myBeneficiary['claimed_at'])) : 'schedule' ?><?= !empty($myBeneficiary['distributor_firstname']) ? ' by ' . htmlspecialchars($myBeneficiary['distributor_firstname'] . ' ' . $myBeneficiary['distributor_lastname']) : '' ?>. Thank you for coordinating with the BDRRMC distribution team!
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Status & Allocation Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 16px;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Claiming Authorization</span>
                        <div style="margin-top: 4px; font-size: 0.95rem; font-weight: 700; color: #0f172a;">
                            <?php if ($status === 'Eligible' || $isApproved): ?>
                                <span style="color: #15803d; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa fa-circle-check"></i> Approved to Claim
                                </span>
                            <?php elseif ($status === 'Approved'): ?>
                                <span style="color: #4338ca; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa fa-shield-check"></i> Claim Approved
                                </span>
                            <?php elseif ($status === 'Ready to Claim'): ?>
                                <span style="color: #854d0e; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa fa-box-open"></i> Ready to Claim
                                </span>
                            <?php elseif ($status === 'Claimed'): ?>
                                <span style="color: #0369a1; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa fa-box-archive"></i> Already Claimed
                                </span>
                            <?php elseif ($status === 'Pending'): ?>
                                <span style="color: #b45309; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa fa-clock"></i> Pending Official Approval
                                </span>
                            <?php else: ?>
                                <span style="color: #b91c1c; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa fa-circle-xmark"></i> Not Eligible
                                </span>
                            <?php endif; ?>
                        </div>
                        <small style="color: #64748b; font-size: 0.78rem; display: block; margin-top: 2px;">
                            <?= ($isApproved || in_array($status, ['Approved', 'Ready to Claim', 'Claimed'])) ? 'Verified by Barangay Official' : 'Under assessment' ?>
                        </small>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Allocated Assistance</span>
                        <div style="margin-top: 4px; font-size: 0.95rem; font-weight: 700; color: #0f172a;">
                            <?= htmlspecialchars((string)($myBeneficiary['allocated_quantity'] ?? 1)) ?>x <?= htmlspecialchars($myBeneficiary['assistance_type'] ?? 'Standard Family Food Pack') ?>
                        </div>
                        <small style="color: #64748b; font-size: 0.78rem; display: block; margin-top: 2px;">
                            Rice, canned goods, clean water & food provisions
                        </small>
                    </div>
                </div>

                <!-- Claiming Schedule & Location Box -->
                <div style="background: #f0f8f7; border: 1px solid #b6e1db; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="font-size: 0.78rem; font-weight: 700; color: #1e40af; text-transform: uppercase; margin-bottom: 6px;">
                        <i class="fa fa-calendar-day"></i> Official Claiming Schedule & Pickup Station
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <div style="font-size: 1rem; font-weight: 800; color: #00665B;">
                                <?= !empty($myBeneficiary['claiming_date']) ? date('l, F d, Y', strtotime($myBeneficiary['claiming_date'])) : 'Schedule Announcement Pending' ?>
                            </div>
                            <div style="font-size: 0.85rem; color: #1d4ed8; margin-top: 2px;">
                                Window: <strong><?= htmlspecialchars($myBeneficiary['claiming_time'] ?? '08:00 AM - 12:00 PM') ?></strong>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span class="badge" style="background: #dbeafe; color: #1e40af; font-size: 0.8rem; padding: 6px 12px;">
                                <i class="fa fa-location-dot"></i> <?= htmlspecialchars($myBeneficiary['claiming_location'] ?? 'Hinaplanon Covered Court, Station B') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Explanation of Prioritization (Machine Learning & Hazard Basis) -->
                <div style="background: #f0fdf4; border-left: 4px solid #10b981; border-radius: 0 8px 8px 0; padding: 12px 14px; font-size: 0.85rem; color: #14532d;">
                    <div style="font-weight: 700; color: #065f46; display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                        <i class="fa fa-brain text-success"></i> Why was your household prioritized?
                    </div>
                    <div>
                        <?= htmlspecialchars($myBeneficiary['priority_reason'] ?? "Prioritized based on residence in {$purokName} and supervised decision tree hazard risk evaluation.") ?>
                    </div>
                    <div style="font-size: 0.78rem; color: #047857; margin-top: 4px; font-style: italic;">
                        * Priority classifications are synthesized from the BDRRMC CamScanner Hazard Map & CART Decision Tree Recommender. Priority recommendations do not automatically grant claiming eligibility until confirmed by Barangay Officials.
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. RELIEF DISTRIBUTION BENEFICIARY LIST -->
        <div class="hub-card">
            <div class="hub-card-header">
                <div>
                    <div class="hub-card-title">
                        <i class="fa fa-clipboard-list text-primary"></i> 
                        Relief Distribution Beneficiary List
                    </div>
                    <p style="font-size: 0.78rem; color: #64748b; margin: 2px 0 0 0;">
                        Public masterlist of residents in the active BDRRMC relief distribution program. Fellow residents' names are anonymized for privacy.
                    </p>
                </div>
                <div style="display: flex; gap: 6px; align-items: center;">
                    <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 0.75rem;">
                        <?= (int)($beneficiaryStats['total'] ?? count($beneficiaries)) ?> Total Enrolled
                    </span>
                </div>
            </div>

            <!-- Filter Controls -->
            <div style="padding: 12px 18px; background: #f8fafc; border-bottom: 1px solid #edf2f7; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <!-- Status Pills -->
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <a href="index.php?route=notifications" class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.78rem;">
                        All (<?= (int)$beneficiaryStats['total'] ?>)
                    </a>
                    <a href="index.php?route=notifications&beneficiary_status=Eligible<?= !empty($purokFilter) ? '&purok_id='.$purokFilter : '' ?>" class="btn btn-sm <?= $statusFilter === 'Eligible' ? 'btn-success' : 'btn-outline' ?>" style="font-size: 0.78rem;">
                        Eligible (<?= (int)$beneficiaryStats['eligible'] ?>)
                    </a>
                    <a href="index.php?route=notifications&beneficiary_status=Pending<?= !empty($purokFilter) ? '&purok_id='.$purokFilter : '' ?>" class="btn btn-sm <?= $statusFilter === 'Pending' ? 'btn-warning' : 'btn-outline' ?>" style="font-size: 0.78rem;">
                        Pending (<?= (int)$beneficiaryStats['pending'] ?>)
                    </a>
                    <a href="index.php?route=notifications&beneficiary_status=Approved<?= !empty($purokFilter) ? '&purok_id='.$purokFilter : '' ?>" class="btn btn-sm <?= $statusFilter === 'Approved' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.78rem;">
                        Approved (<?= (int)($beneficiaryStats['approved'] ?? 0) ?>)
                    </a>
                    <a href="index.php?route=notifications&beneficiary_status=Ready+to+Claim<?= !empty($purokFilter) ? '&purok_id='.$purokFilter : '' ?>" class="btn btn-sm <?= $statusFilter === 'Ready to Claim' ? 'btn-warning' : 'btn-outline' ?>" style="font-size: 0.78rem;">
                        Ready (<?= (int)($beneficiaryStats['ready_to_claim'] ?? 0) ?>)
                    </a>
                    <a href="index.php?route=notifications&beneficiary_status=Claimed<?= !empty($purokFilter) ? '&purok_id='.$purokFilter : '' ?>" class="btn btn-sm <?= $statusFilter === 'Claimed' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.78rem;">
                        Claimed (<?= (int)$beneficiaryStats['claimed'] ?>)
                    </a>
                </div>

                <!-- Purok Filter Dropdown -->
                <form method="GET" action="index.php" style="display: flex; gap: 6px; align-items: center; margin: 0;">
                    <input type="hidden" name="route" value="notifications">
                    <?php if (!empty($statusFilter)): ?>
                        <input type="hidden" name="beneficiary_status" value="<?= htmlspecialchars($statusFilter) ?>">
                    <?php endif; ?>
                    <select name="purok_id" class="form-control" style="font-size: 0.8rem; padding: 4px 8px; height: 30px; width: 180px;" onchange="this.form.submit()">
                        <option value="">-- All Hinaplanon Puroks --</option>
                        <?php foreach ($puroks as $p): ?>
                            <option value="<?= $p['purok_id'] ?>" <?= ((string)$purokFilter === (string)$p['purok_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['purok_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <div class="hub-card-body" style="padding: 0;">
                <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                    <table class="table beneficiary-table" style="margin: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>Resident Name</th>
                                <th>Purok / Sector</th>
                                <th>Allocated Aid</th>
                                <th>Status</th>
                                <th style="text-align: right;">Pickup Station</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($beneficiaries)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #94a3b8; padding: 24px;">
                                        No beneficiary records found matching the active filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($beneficiaries as $b): ?>
                                    <?php 
                                        $isCurrentUser = ((int)$b['user_id'] === (int)$user['user_id']);
                                    ?>
                                    <tr class="<?= $isCurrentUser ? 'highlight-row' : '' ?>">
                                        <td>
                                            <?php if ($isCurrentUser): ?>
                                                <strong style="color: #065f46; font-weight: 800;">
                                                    <i class="fa fa-user-check text-success"></i> <?= htmlspecialchars($b['firstname'] . ' ' . $b['lastname']) ?>
                                                </strong>
                                                <span class="badge badge-success" style="font-size: 0.68rem; margin-left: 4px;">YOU</span>
                                            <?php else: ?>
                                                <span style="color: #475569;">
                                                    <i class="fa fa-user-shield" style="opacity: 0.4;"></i> 
                                                    <?= htmlspecialchars(Beneficiary::anonymizeName($b['firstname'], $b['lastname'])) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($b['purok_name'] ?? 'Hinaplanon') ?></span>
                                            <?php if (!empty($b['purok_risk'])): ?>
                                                <span style="font-size: 0.72rem; color: #64748b; display: block;">
                                                    Risk: <?= htmlspecialchars($b['purok_risk']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.82rem; color: #334155;">
                                                <?= (int)$b['allocated_quantity'] ?>x Food Pack
                                            </span>
                                        </td>
                                        <td>
                                            <?= getStatusBadge($b['assistance_status']) ?>
                                        </td>
                                        <td style="text-align: right; font-size: 0.8rem; color: #64748b;">
                                            <?= htmlspecialchars(mb_strimwidth($b['claiming_location'] ?? 'Hinaplanon Gym', 0, 24, '...')) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. RELIEF GOODS INVENTORY & DISTRIBUTION -->
        <div class="hub-card">
            <div class="hub-card-header">
                <div>
                    <div class="hub-card-title">
                        <i class="fa fa-boxes-stacked text-primary"></i> 
                        Relief Goods Inventory & Centralized Distribution
                    </div>
                    <p style="font-size: 0.78rem; color: #64748b; margin: 2px 0 0 0;">
                        Live status of available disaster relief goods, hygiene provisions, and emergency supplies stocked at BDRRMC depot.
                    </p>
                </div>
                <div>
                    <span class="badge badge-info" style="font-size: 0.75rem;">
                        <i class="fa fa-warehouse"></i> Central Depot
                    </span>
                </div>
            </div>

            <div class="hub-card-body" style="padding: 14px 18px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 14px;">
                    <?php if (empty($reliefSupplies)): ?>
                        <div style="color: #64748b; padding: 12px;">Inventory information currently updating...</div>
                    <?php else: ?>
                        <?php foreach (array_slice($reliefSupplies, 0, 6) as $sup): ?>
                            <?php 
                                $statusBadge = 'badge-success';
                                if ($sup['supply_status'] === 'Low Stock') $statusBadge = 'badge-warning';
                                elseif ($sup['supply_status'] === 'Critical' || $sup['supply_status'] === 'Out of Stock') $statusBadge = 'badge-danger';
                                
                                $icon = 'fa-box';
                                if ($sup['supply_type'] === 'Relief Goods') $icon = 'fa-bowl-rice';
                                elseif ($sup['supply_type'] === 'Clean Water') $icon = 'fa-bottle-water';
                                elseif ($sup['supply_type'] === 'Hygiene Kits') $icon = 'fa-soap';
                                elseif ($sup['supply_type'] === 'Medical Supplies') $icon = 'fa-kit-medical';
                                elseif ($sup['supply_type'] === 'Shelter Kits') $icon = 'fa-tent';
                            ?>
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                                        <div style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">
                                            <i class="fa <?= $icon ?> text-primary"></i> <?= htmlspecialchars($sup['supply_name']) ?>
                                        </div>
                                        <span class="badge <?= $statusBadge ?>" style="font-size: 0.68rem;">
                                            <?= htmlspecialchars($sup['supply_status']) ?>
                                        </span>
                                    </div>
                                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 4px;">
                                        Category: <?= htmlspecialchars($sup['supply_type']) ?>
                                    </div>
                                </div>

                                <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed #e2e8f0; display: flex; justify-content: space-between; align-items: baseline;">
                                    <span style="font-size: 0.78rem; color: #64748b;">Stock Remaining:</span>
                                    <span style="font-size: 0.95rem; font-weight: 800; color: #0f172a;">
                                        <?= number_format((int)$sup['supply_remaining']) ?> <small style="font-weight: 500; font-size: 0.75rem; color: #64748b;"><?= htmlspecialchars($sup['unit_of_measure'] ?? 'units') ?></small>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Assistance Request Guidance (System-Integrated Policy) -->
                <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fa fa-circle-info" style="color: #0284c7; font-size: 1.25rem;"></i>
                        <div style="font-size: 0.84rem; color: #0369a1; line-height: 1.4;">
                            <strong>Relief Assistance & Special Requests:</strong> Goods distribution is systematically allocated based on Purok hazard priority. If your household has infants, elderly, or bedridden members requiring special supplies, please notify your Purok Leader.
                        </div>
                    </div>
                    <div>
                        <span class="badge" style="background: #ffffff; color: #0369a1; border: 1px solid #7dd3fc; font-size: 0.78rem; padding: 6px 12px;">
                            <i class="fa fa-phone"></i> BDRRMC Desk: (063) 223-1122
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ============================================== -->
    <!-- RIGHT COLUMN: ALERTS, REMINDERS & PREPAREDNESS -->
    <!-- ============================================== -->
    <div style="display: flex; flex-direction: column;">

        <!-- 5. COMMUNITY GENERAL ALERTS -->
        <div class="hub-card" style="border-top: 4px solid #ef4444;">
            <div class="hub-card-header">
                <div class="hub-card-title" style="color: #991b1b;">
                    <i class="fa fa-triangle-exclamation text-danger"></i> 
                    Community General Alerts
                </div>
                <div style="font-size: 0.75rem; color: #64748b;">
                    PAGASA & BDRRMC Advisories
                </div>
            </div>

            <div class="hub-card-body" style="padding: 0;">
                <?php if (empty($notifications)): ?>
                    <div style="padding: 24px 16px; text-align: center; color: #64748b;">
                        <i class="fa fa-check-double text-success" style="font-size: 1.8rem; margin-bottom: 8px; display: block;"></i>
                        <strong style="color: #1e293b; font-size: 0.9rem;">No Critical Alerts Active</strong>
                        <p style="font-size: 0.8rem; margin: 4px 0 0 0;">All Hinaplanon community sectors are operating under normal conditions.</p>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column;">
                        <?php foreach (array_slice($notifications, 0, 5) as $n): ?>
                            <?php 
                                $isCritical = in_array(strtolower($n['priority'] ?? ''), ['urgent', 'critical']);
                                $isWarning = in_array(strtolower($n['priority'] ?? ''), ['high', 'warning']);
                                
                                $badgeColor = '#f0f8f7';
                                $badgeText = '#1d4ed8';
                                $badgeBorder = '#b6e1db';
                                $badgeLabel = 'INFO';
                                $iconColor = '#3b82f6';
                                
                                if ($isCritical) {
                                    $badgeColor = '#fee2e2';
                                    $badgeText = '#991b1b';
                                    $badgeBorder = '#fca5a5';
                                    $badgeLabel = 'CRITICAL';
                                    $iconColor = '#ef4444';
                                } elseif ($isWarning) {
                                    $badgeColor = '#fef3c7';
                                    $badgeText = '#92400e';
                                    $badgeBorder = '#fcd34d';
                                    $badgeLabel = 'WARNING';
                                    $iconColor = '#f59e0b';
                                }
                                
                                $isUnread = empty($n['is_read']);
                            ?>
                            <div style="padding: 14px 18px; border-bottom: 1px solid #f1f5f9; background: <?= $isUnread ? '#f8fafc' : '#ffffff' ?>; position: relative;">
                                <?php if ($isUnread): ?>
                                    <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: <?= $iconColor ?>;"></div>
                                <?php endif; ?>

                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span class="badge" style="background: <?= $badgeColor ?>; color: <?= $badgeText ?>; border: 1px solid <?= $badgeBorder ?>; font-size: 0.68rem; padding: 2px 8px; font-weight: 700;">
                                        <?= $badgeLabel ?>
                                    </span>
                                    <span style="font-size: 0.72rem; color: #94a3b8;">
                                        <i class="fa fa-clock"></i> <?= date('M d, h:i A', strtotime($n['created_at'])) ?>
                                    </span>
                                </div>

                                <strong style="font-size: 0.88rem; color: #0f172a; display: block;">
                                    <?= htmlspecialchars($n['title']) ?>
                                </strong>

                                <p style="font-size: 0.82rem; color: #475569; margin: 4px 0 8px 0; line-height: 1.45;">
                                    <?= nl2br(htmlspecialchars($n['message'])) ?>
                                </p>

                                <?php if ($isUnread): ?>
                                    <form action="index.php?route=notifications-mark-read" method="POST" style="margin: 0; display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                        <input type="hidden" name="notification_id" value="<?= (int)$n['notification_id'] ?>">
                                        <button type="submit" class="btn btn-outline btn-sm" style="font-size: 0.72rem; padding: 2px 8px; color: #64748b;">
                                            <i class="fa fa-check"></i> Mark read
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 6. IMPORTANT REMINDERS (Claiming Protocol & Identification) -->
        <div class="hub-card" style="border-top: 4px solid #3b82f6;">
            <div class="hub-card-header">
                <div class="hub-card-title" style="color: #1e40af;">
                    <i class="fa fa-circle-exclamation text-primary"></i> 
                    Important Claiming Reminders
                </div>
                <div style="font-size: 0.75rem; color: #64748b;">
                    Official Protocols
                </div>
            </div>

            <div class="hub-card-body" style="padding: 16px;">
                <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.85rem; color: #334155;">
                    <div style="display: flex; gap: 10px; align-items: flex-start;">
                        <span style="display: flex; width: 22px; height: 22px; border-radius: 50%; background: #f0f8f7; color: #1B7E74; font-weight: 700; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.78rem;">1</span>
                        <div>
                            <strong style="color: #0f172a;">Valid Identification Requirement:</strong>
                            Present at least one (1) valid government photo ID, Barangay ID, or Barangay Certificate of Residency at the intake triage desk.
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; align-items: flex-start;">
                        <span style="display: flex; width: 22px; height: 22px; border-radius: 50%; background: #f0f8f7; color: #1B7E74; font-weight: 700; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.78rem;">2</span>
                        <div>
                            <strong style="color: #0f172a;">Authorized Representative (Proxy) Policy:</strong>
                            If the registered household head is unable to claim in person due to medical or elderly reasons, an immediate family member may claim with a signed Authorization Letter and valid IDs of both.
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; align-items: flex-start;">
                        <span style="display: flex; width: 22px; height: 22px; border-radius: 50%; background: #f0f8f7; color: #1B7E74; font-weight: 700; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.78rem;">3</span>
                        <div>
                            <strong style="color: #0f172a;">Strict Schedule Adherence:</strong>
                            Please arrive strictly within your assigned Purok batch schedule to ensure smooth crowd control and avoid delays at Station B.
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; align-items: flex-start;">
                        <span style="display: flex; width: 22px; height: 22px; border-radius: 50%; background: #f0f8f7; color: #1B7E74; font-weight: 700; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.78rem;">4</span>
                        <div>
                            <strong style="color: #0f172a;">Verification Stub / QR Code:</strong>
                            Keep your confirmation SMS, QR stub, or issued Barangay Claim Stub handy for immediate barcode scanning at the dispensing station.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. COMMUNITY PREPAREDNESS ANNOUNCEMENTS -->
        <div class="hub-card">
            <div class="hub-card-header">
                <div class="hub-card-title">
                    <i class="fa fa-bullhorn text-warning"></i> 
                    Community Preparedness Announcements
                </div>
                <div style="font-size: 0.75rem; color: #64748b;">
                    BDRRMC & ICDRRMO Drills
                </div>
            </div>

            <div class="hub-card-body" style="padding: 14px 18px;">
                <?php if (empty($preparednessActivities)): ?>
                    <p style="font-size: 0.82rem; color: #64748b; margin: 0;">No upcoming preparedness activities scheduled at this time.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach (array_slice($preparednessActivities, 0, 3) as $act): ?>
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                                    <strong style="font-size: 0.88rem; color: #0f172a;">
                                        <?= htmlspecialchars($act['title']) ?>
                                    </strong>
                                    <span class="badge badge-info" style="font-size: 0.68rem;">
                                        <?= htmlspecialchars($act['activity_type'] ?? 'Preparedness') ?>
                                    </span>
                                </div>
                                <div style="font-size: 0.78rem; color: #475569; margin-top: 4px;">
                                    <i class="fa fa-calendar text-primary"></i> <?= date('M d, Y h:i A', strtotime($act['schedule_date'])) ?> | 
                                    <i class="fa fa-location-dot text-danger"></i> <?= htmlspecialchars($act['location']) ?>
                                </div>
                                <?php if (!empty($act['description'])): ?>
                                    <p style="font-size: 0.78rem; color: #64748b; margin: 6px 0 0 0;">
                                        <?= htmlspecialchars(mb_strimwidth($act['description'], 0, 95, '...')) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Emergency Contact Hotline Footer -->
                <div style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed #e2e8f0; font-size: 0.8rem; color: #475569;">
                    <strong style="color: #0f172a; display: block; margin-bottom: 4px;"><i class="fa fa-tower-broadcast text-danger"></i> Emergency Hotlines (24/7):</strong>
                    ICDRRMO Iligan City: <strong>(063) 221-4444 / 911</strong><br>
                    Hinaplanon BDRRM Operations: <strong>(063) 223-1122</strong> | Mobile: <strong>0917-555-4422</strong>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ============================================== -->
<!-- CLAIM AYUDA CONFIRMATION MODAL                 -->
<!-- ============================================== -->
<div id="claimAyudaModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15,23,42,0.65); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 14px; max-width: 520px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); overflow: hidden; animation: fadeInModal 0.2s ease-out;">
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 16px 20px; color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa fa-hand-holding-heart" style="font-size: 1.25rem;"></i>
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: #ffffff;">Confirm Ayuda Claim Request</h3>
            </div>
            <button type="button" onclick="closeClaimAyudaModal()" style="background: none; border: none; color: #ffffff; font-size: 1.25rem; cursor: pointer; padding: 4px;">
                <i class="fa fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Form -->
        <form action="index.php?route=residents-claim-ayuda" method="POST" style="padding: 22px;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

            <p style="font-size: 0.88rem; color: #475569; margin-top: 0; margin-bottom: 16px; line-height: 1.45;">
                Please confirm your relief assistance claim. Review your designated pickup details below:
            </p>

            <!-- Household Allocation Summary Box -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 16px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.85rem;">
                    <div>
                        <span style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Beneficiary Name</span>
                        <strong style="color: #0f172a;"><?= htmlspecialchars($residentName) ?></strong>
                    </div>
                    <div>
                        <span style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Purok / Sector</span>
                        <strong style="color: #0f172a;"><?= htmlspecialchars($purokName) ?></strong>
                    </div>
                    <div style="grid-column: span 2; padding-top: 6px; border-top: 1px dashed #e2e8f0;">
                        <span style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Allocated Assistance</span>
                        <strong style="color: #065f46; font-size: 0.95rem;">
                            <i class="fa fa-box text-success"></i> <?= htmlspecialchars((string)($myBeneficiary['allocated_quantity'] ?? 1)) ?>x <?= htmlspecialchars($myBeneficiary['assistance_type'] ?? 'Standard Family Food Pack') ?>
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Pickup Schedule & Station Details -->
            <div style="background: #f0f8f7; border: 1px solid #b6e1db; border-radius: 10px; padding: 14px; margin-bottom: 16px; font-size: 0.85rem;">
                <div style="color: #1e40af; font-weight: 700; font-size: 0.78rem; text-transform: uppercase; margin-bottom: 6px;">
                    <i class="fa fa-calendar-check"></i> Pickup Schedule & Station
                </div>
                <div style="color: #00665B; font-weight: 700; font-size: 0.95rem;">
                    <?= !empty($myBeneficiary['claiming_date']) ? date('l, F d, Y', strtotime($myBeneficiary['claiming_date'])) : 'Official Date Announced Soon' ?>
                </div>
                <div style="color: #1B7E74; font-size: 0.82rem; margin-top: 2px;">
                    Time Window: <strong><?= htmlspecialchars($myBeneficiary['claiming_time'] ?? '08:00 AM - 12:00 PM') ?></strong>
                </div>
                <div style="color: #1e40af; font-size: 0.82rem; margin-top: 2px;">
                    Location: <strong><?= htmlspecialchars($myBeneficiary['claiming_location'] ?? 'Hinaplanon Covered Court, Station B') ?></strong>
                </div>
            </div>

            <!-- Anti-Duplicate Policy Notice & Checkbox -->
            <div style="background: #fefce8; border: 1px solid #fef08a; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                <div style="display: flex; gap: 8px; font-size: 0.8rem; color: #854d0e; line-height: 1.4;">
                    <i class="fa fa-shield-halved" style="color: #ca8a04; font-size: 1rem; flex-shrink: 0; margin-top: 2px;"></i>
                    <div>
                        <strong>Strict Single-Claim Rule:</strong> Only one claim submission is permitted per registered household per relief cycle. Once confirmed, your claim will be locked and routed to Barangay Officials for verification.
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.84rem; color: #334155; cursor: pointer;">
                    <input type="checkbox" name="confirm_claim" id="confirmClaimCheckbox" required style="margin-top: 3px; accent-color: #059669;">
                    <span>I confirm this claim on behalf of my household and certify that I will present a valid ID upon claiming at the designated pickup station.</span>
                </label>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeClaimAyudaModal()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-success" style="font-weight: 700; padding: 8px 20px;">
                    <i class="fa fa-check"></i> Submit Claim Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openClaimAyudaModal() {
    const modal = document.getElementById('claimAyudaModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeClaimAyudaModal() {
    const modal = document.getElementById('claimAyudaModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside of modal card
window.addEventListener('click', function(e) {
    const modal = document.getElementById('claimAyudaModal');
    if (e.target === modal) {
        closeClaimAyudaModal();
    }
});
</script>
