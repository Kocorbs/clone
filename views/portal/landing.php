<!-- views/portal/landing.php — Public DRRM Landing Page -->
<div class="portal-page">
    <!-- Sticky Collapsible Portal Header -->
    <div class="portal-sticky-header" id="portalStickyHeader">
        <!-- 1. Top Emergency / Monitoring Alert Ticker Bar (Right-to-Left Live Crawl) -->
        <div class="portal-alert-bar" role="region" aria-label="Emergency Alerts Ticker" title="Emergency Advisory Ticker — Hover to pause">
            <div class="portal-ticker-track">
                <div class="portal-ticker-content">
                    <div class="portal-alert-item alert-primary-item">
                        <i class="fa fa-triangle-exclamation" style="color: #facc15;"></i>
                        <span><strong style="color: #fef08a;">ALERT:</strong> Mandulog River monitoring active &bull; Check your Purok risk level</span>
                    </div>
                    <span class="portal-ticker-divider">&bull;</span>
                    <div class="portal-alert-item alert-secondary-item">
                        <i class="fa fa-phone" style="color: #facc15;"></i>
                        <span>CDRRMO Emergency Hotline: <strong>911</strong></span>
                    </div>
                    <span class="portal-ticker-divider">&bull;</span>
                    <div class="portal-alert-item alert-tertiary-item">
                        <i class="fa fa-cloud-bolt" style="color: #facc15;"></i>
                        <span>PAGASA Update: <strong>Normal / Alert Level 0</strong> &bull; Hinaplanon DRRM</span>
                    </div>
                    <span class="portal-ticker-divider">&bull;</span>
                    <div class="portal-alert-item alert-primary-item">
                        <i class="fa fa-triangle-exclamation" style="color: #facc15;"></i>
                        <span><strong style="color: #fef08a;">ALERT:</strong> Mandulog River monitoring active &bull; Check your Purok risk level</span>
                    </div>
                    <span class="portal-ticker-divider">&bull;</span>
                    <div class="portal-alert-item alert-secondary-item">
                        <i class="fa fa-phone" style="color: #facc15;"></i>
                        <span>CDRRMO Emergency Hotline: <strong>911</strong></span>
                    </div>
                    <span class="portal-ticker-divider">&bull;</span>
                    <div class="portal-alert-item alert-tertiary-item">
                        <i class="fa fa-cloud-bolt" style="color: #facc15;"></i>
                        <span>PAGASA Update: <strong>Normal / Alert Level 0</strong> &bull; Hinaplanon DRRM</span>
                    </div>
                    <span class="portal-ticker-divider">&bull;</span>
                </div>
                <div class="portal-ticker-content" aria-hidden="true">
                    <div class="portal-alert-item alert-primary-item">
                        <i class="fa fa-triangle-exclamation" style="color: #facc15;"></i>
                        <span><strong style="color: #fef08a;">ALERT:</strong> Mandulog River monitoring active &bull; Check your Purok risk level</span>
                    </div>
                    <span class="portal-ticker-divider">&bull;</span>
                    <div class="portal-alert-item alert-secondary-item">
                        <i class="fa fa-phone" style="color: #facc15;"></i>
                        <span>CDRRMO Emergency Hotline: <strong>911</strong></span>
                    </div>
                    <span class="portal-ticker-divider">&bull;</span>
                    <div class="portal-alert-item alert-tertiary-item">
                        <i class="fa fa-cloud-bolt" style="color: #facc15;"></i>
                        <span>PAGASA Update: <strong>Normal / Alert Level 0</strong> &bull; Hinaplanon DRRM</span>
                    </div>
                    <span class="portal-ticker-divider">&bull;</span>
                    <div class="portal-alert-item alert-primary-item">
                        <i class="fa fa-triangle-exclamation" style="color: #facc15;"></i>
                        <span><strong style="color: #fef08a;">ALERT:</strong> Mandulog River monitoring active &bull; Check your Purok risk level</span>
                    </div>
                    <span class="portal-ticker-divider">&bull;</span>
                    <div class="portal-alert-item alert-secondary-item">
                        <i class="fa fa-phone" style="color: #facc15;"></i>
                        <span>CDRRMO Emergency Hotline: <strong>911</strong></span>
                    </div>
                    <span class="portal-ticker-divider">&bull;</span>
                    <div class="portal-alert-item alert-tertiary-item">
                        <i class="fa fa-cloud-bolt" style="color: #facc15;"></i>
                        <span>PAGASA Update: <strong>Normal / Alert Level 0</strong> &bull; Hinaplanon DRRM</span>
                    </div>
                    <span class="portal-ticker-divider">&bull;</span>
                </div>
            </div>
        </div>

        <!-- 2. Primary Navigation Header -->
        <header class="portal-header">
            <a href="index.php?route=landing" class="portal-brand">
                <img src="assets/images/hinaplanon_seal.png" alt="Barangay Hinaplanon Official Seal" class="portal-seal-img">
                <div class="portal-brand-text">
                    <span class="portal-brand-title">Padayon Hinaplanon</span>
                    <span class="portal-brand-sub">ILIGAN CITY &bull; DRRM PORTAL</span>
                </div>
            </a>

            <div class="portal-header-actions">
                <a href="index.php?route=login" class="portal-header-login-link" style="background: rgba(255,255,255,0.15); border-radius: 6px; padding: 7px 16px;">
                    <i class="fa fa-right-to-bracket"></i> Log In
                </a>
                <button type="button" class="portal-emergency-pill" onclick="openPortalModal('modalEmergency', event)">
                    <i class="fa fa-phone-volume"></i>
                    <span class="emergency-label-desktop">Emergency <strong>119</strong></span>
                    <span class="emergency-label-mobile"><strong>119</strong></span>
                </button>
            </div>
        </header>
    </div>

    <!-- 4. Hero Section — Public Landing Showcase (No Login Form) -->
    <main class="portal-hero-container" id="home">
        <!-- Official Barangay Seal Background Watermark with Dark Vignette Overlay -->
        <div class="portal-bg-seal-watermark" aria-hidden="true">
            <img src="assets/images/hinaplanon_seal.png" alt="Barangay Hinaplanon Official Seal" class="portal-watermark-seal">
            <div class="portal-seal-vignette"></div>
        </div>

        <div class="portal-hero-grid">
            <div class="portal-hero-showcase">
                <!-- Clean, Authoritative Headline -->
                <h1 class="portal-hero-h1">
                    Protecting Our <span class="portal-text-highlight">Barangay</span>,<br>
                    <span class="portal-text-subhighlight">Community Together</span>
                </h1>

                <!-- Clear, High-Contrast Description -->
                <p class="portal-hero-lead">
                    Official disaster preparedness, river flood surveillance, and explainable AI resource distribution system for the residents and officials of Barangay Hinaplanon, Iligan City.
                </p>

                <!-- Professional Action Buttons -->
                <div class="portal-hero-actions">
                    <a href="index.php?route=login" class="portal-hero-btn btn-primary-portal">
                        <i class="fa fa-right-to-bracket"></i>
                        <span>Sign In to Portal</span>
                    </a>
                    <a href="index.php?route=register" class="portal-hero-btn btn-secondary-portal">
                        <i class="fa fa-id-card"></i>
                        <span>Register Barangay ID</span>
                    </a>
                    <button type="button" class="portal-hero-btn btn-tertiary-portal" onclick="openPortalModal('modalEmergency', event)">
                        <i class="fa fa-phone-volume"></i>
                        <span>Emergency Hotline</span>
                    </button>
                </div>
            </div>
        </div>
    </main>
    <footer style="text-align: center; font-size: 0.76rem; color: rgba(255, 255, 255, 0.45); padding: 12px 16px; background: rgba(0, 20, 18, 0.7); border-top: 1px solid rgba(255, 255, 255, 0.08);">
        &copy; <?= date('Y') ?> Barangay Hinaplanon Disaster Risk Reduction & Management Council &bull; Iligan City
    </footer>
</div>

<!-- ======================================================================== -->
<!-- MODAL OVERLAYS                                                           -->
<!-- ======================================================================== -->
<div id="modalEmergency" class="portal-modal-overlay" onclick="closePortalModalOnBackdrop(event, 'modalEmergency')">
    <div class="portal-modal-box">
        <div class="portal-modal-header" style="background: #dc2626;">
            <h3 class="portal-modal-title"><i class="fa fa-phone-volume"></i> Emergency Quick Connect</h3>
            <button type="button" class="portal-modal-close" onclick="closePortalModal('modalEmergency')">&times;</button>
        </div>
        <div class="portal-modal-body" style="text-align: center; padding: clamp(16px, 4vw, 28px) 16px;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; margin: 0 auto 14px;">
                <i class="fa fa-bell-concierge"></i>
            </div>
            <h4 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Barangay Hinaplanon Emergency</h4>
            <p style="font-size: 0.84rem; color: #64748b; margin-bottom: 18px; line-height: 1.5;">
                Choose an emergency contact service to initiate an immediate telephone call:
            </p>
            <div style="display: flex; flex-direction: column; gap: 10px; max-width: 320px; width: 100%; margin: 0 auto;">
                <a href="tel:911" class="btn btn-danger" style="padding: 12px; font-weight: 800; font-size: 0.95rem; justify-content: center;">
                    <i class="fa fa-phone"></i> Dial 911 (City CDRRMO)
                </a>
                <a href="tel:123" class="btn btn-outline" style="padding: 10px; font-weight: 700; justify-content: center; border-color: #cbd5e1;">
                    <i class="fa fa-tower-broadcast"></i> Dial 123 (Hinaplanon BDRRM)
                </a>
                <a href="tel:09175550199" class="btn btn-outline" style="padding: 10px; font-weight: 700; justify-content: center; border-color: #cbd5e1;">
                    <i class="fa fa-mobile-screen"></i> 0917-555-0199 (Mobile Dispatch)
                </a>
            </div>
        </div>
    </div>
</div>
