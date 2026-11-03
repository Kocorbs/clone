// public/assets/js/app.js
// Barangay Hinaplanon Disaster Management & Leaflet.js Hazard Map Controller

document.addEventListener('DOMContentLoaded', function () {
    // 1. Password Visibility Toggle
    const togglePassBtn = document.getElementById('togglePassword');
    const passInput = document.getElementById('password');
    if (togglePassBtn && passInput) {
        togglePassBtn.addEventListener('click', function () {
            const isPass = passInput.getAttribute('type') === 'password';
            passInput.setAttribute('type', isPass ? 'text' : 'password');
            this.innerHTML = isPass ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
        });
    }

    // 2. Demo Account Quick-Fill Helper on Login Screen
    const demoButtons = document.querySelectorAll('[data-demo-identifier]');
    demoButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const idVal = this.getAttribute('data-demo-identifier');
            const passVal = this.getAttribute('data-demo-pass');
            const idInput = document.getElementById('identifier');
            const pInput = document.getElementById('password');
            if (idInput && pInput) {
                idInput.value = idVal;
                pInput.value = passVal;
            }
        });
    });

    // 3. Auto-Calculate Affected Population from Households (1 HH ~ 4-5 persons)
    const hhInput = document.getElementById('affected_households');
    const popInput = document.getElementById('affected_population');
    if (hhInput && popInput) {
        hhInput.addEventListener('input', function () {
            const hh = parseInt(this.value, 10) || 0;
            if (!popInput.dataset.manualEdited) {
                popInput.value = hh * 4;
            }
        });
        popInput.addEventListener('input', function () {
            popInput.dataset.manualEdited = "true";
        });
    }

    // 4. Recommendation Review Toggle Helper
    const reviewActionSelect = document.getElementById('review_action');
    const modQtyGroup = document.getElementById('modified_qty_group');
    if (reviewActionSelect && modQtyGroup) {
        reviewActionSelect.addEventListener('change', function () {
            if (this.value === 'modify') {
                modQtyGroup.style.display = 'block';
            } else {
                modQtyGroup.style.display = 'none';
            }
        });
    }

    // 5. Real-Time Notification Poller (Polls every 20s if authenticated)
    const notifBadge = document.getElementById('notifBadgeCount');
    if (notifBadge) {
        setInterval(function () {
            fetch('index.php?route=notifications-poll')
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        const count = parseInt(data.unread_count, 10) || 0;
                        notifBadge.textContent = count;
                        if (count > 0) {
                            notifBadge.style.display = 'flex';
                        } else {
                            notifBadge.style.display = 'none';
                        }
                    }
                })
                .catch(() => {});
        }, 20000);
    }
});

/**
 * Universal Leaflet.js Interactive Hazard Map Initializer
 * Renders verified Hinaplanon hazard zones, evacuation centers, and active incidents.
 */
window.initHazardMap = function (containerId, options = {}) {
    const container = document.getElementById(containerId);
    if (!container || typeof L === 'undefined') return null;

    // Centered at Barangay Hinaplanon, Iligan City (8.2412, 124.2543)
    const map = L.map(containerId, {
        center: [8.2412, 124.2543],
        zoom: options.zoom || 14,
        zoomControl: true
    });

    // OpenStreetMap standard tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> | BDRRMC Hinaplanon',
        maxZoom: 19
    }).addTo(map);

    // Layer Groups
    const hazardLayer = L.layerGroup().addTo(map);
    const shelterLayer = L.layerGroup().addTo(map);
    const incidentLayer = L.layerGroup().addTo(map);
    const reportLayer = L.layerGroup().addTo(map);

    function getRiskColor(risk) {
        switch (String(risk).toLowerCase()) {
            case 'high': return '#e11d48';    // Pink
            case 'medium': return '#ea580c';  // Orange
            case 'low': return '#ca8a04';     // Yellow
            default: return '#64748b';        // Gray
        }
    }

    function renderMapData(data) {
        hazardLayer.clearLayers();
        shelterLayer.clearLayers();
        incidentLayer.clearLayers();
        reportLayer.clearLayers();

        // 1. Render Puroks (Hazard Zones)
        if (data.puroks && Array.isArray(data.puroks)) {
            data.puroks.forEach(p => {
                if (p.lat && p.lng) {
                    const color = getRiskColor(p.risk);
                    const circle = L.circle([p.lat, p.lng], {
                        color: color,
                        fillColor: color,
                        fillOpacity: 0.35,
                        radius: p.risk === 'High' ? 260 : 200,
                        weight: 2
                    });

                    const popup = `
                        <div style="font-family: inherit; font-size: 13px; line-height: 1.4;">
                            <div style="font-weight: 800; font-size: 14px; color: #0f172a; margin-bottom: 2px;">
                                <i class="fa fa-map-pin"></i> ${p.name}
                            </div>
                            <div style="margin-bottom: 6px;">
                                <span style="background:${color}; color:#ffffff; padding: 2px 6px; border-radius: 4px; font-weight:700; font-size: 11px;">
                                    ${p.risk} Risk Zone (${p.color})
                                </span>
                            </div>
                            <div style="color: #475569; font-size: 12px;">${p.notes || 'Official hazard reference zone.'}</div>
                        </div>
                    `;
                    circle.bindPopup(popup);
                    hazardLayer.addLayer(circle);
                }
            });
        }

        // 2. Render Evacuation Shelters
        if (data.shelters && Array.isArray(data.shelters)) {
            data.shelters.forEach(s => {
                if (s.lat && s.lng) {
                    const statusColor = s.status === 'Full' ? '#dc2626' : (s.status === 'Near Full' ? '#ea580c' : '#16a34a');
                    const shelterIcon = L.divIcon({
                        className: 'custom-shelter-marker',
                        html: `<div style="background-color: ${statusColor}; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ffffff; border: 2px solid #ffffff; box-shadow: 0 2px 6px rgba(0,0,0,0.3); font-size: 14px;"><i class="fa fa-person-shelter"></i></div>`,
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    });

                    const marker = L.marker([s.lat, s.lng], { icon: shelterIcon });
                    const popup = `
                        <div style="font-family: inherit; font-size: 13px;">
                            <div style="font-weight: 800; color: #0f172a; font-size: 14px; margin-bottom: 4px;">
                                <i class="fa fa-building-shield text-primary"></i> ${s.name}
                            </div>
                            <div style="margin-bottom: 4px;">
                                <span style="background: ${statusColor}; color: #ffffff; padding: 2px 6px; border-radius: 3px; font-weight: 700; font-size: 11px;">
                                    ${s.status} (${s.occupancy} / ${s.max_capacity} Evacuees)
                                </span>
                            </div>
                            <div style="color: #475569; font-size: 12px;"><strong>Location:</strong> ${s.location}</div>
                            <div style="color: #475569; font-size: 12px; margin-top: 2px;"><strong>Contact:</strong> ${s.contact}</div>
                        </div>
                    `;
                    marker.bindPopup(popup);
                    shelterLayer.addLayer(marker);
                }
            });
        }

        // 3. Render Incidents (If authorized)
        if (data.incidents && Array.isArray(data.incidents)) {
            data.incidents.forEach(inc => {
                if (inc.lat && inc.lng) {
                    const isUrgent = inc.is_urgent || inc.severity === 'Critical' || inc.is_sos;
                    const markerColor = isUrgent ? '#dc2626' : '#2563eb';
                    const iconName = inc.is_sos ? 'fa-life-ring' : 'fa-triangle-exclamation';

                    const incidentIcon = L.divIcon({
                        className: 'custom-incident-marker',
                        html: `<div style="background-color: ${markerColor}; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #ffffff; border: 2px solid #ffffff; box-shadow: 0 3px 8px rgba(0,0,0,0.35); font-size: 13px;"><i class="fa ${iconName}"></i></div>`,
                        iconSize: [28, 28],
                        iconAnchor: [14, 14]
                    });

                    const marker = L.marker([inc.lat, inc.lng], { icon: incidentIcon });
                    const popup = `
                        <div style="font-family: inherit; font-size: 13px; min-width: 180px;">
                            <div style="font-weight: 800; font-size: 14px; color: ${markerColor}; margin-bottom: 2px;">
                                ${inc.is_sos ? 'EMERGENCY SOS: ' : ''}${inc.disaster_type}
                            </div>
                            <div style="font-size: 11px; color: #64748b; margin-bottom: 6px;">Ref: ${inc.ref} | ${inc.purok}</div>
                            <div style="display: flex; gap: 4px; margin-bottom: 6px;">
                                <span style="background: #fee2e2; color: #991b1b; padding: 1px 6px; border-radius: 3px; font-weight: 700; font-size: 11px;">
                                    ${inc.severity} Severity
                                </span>
                                <span style="background: #e0f2fe; color: #0369a1; padding: 1px 6px; border-radius: 3px; font-weight: 700; font-size: 11px;">
                                    ${inc.status}
                                </span>
                            </div>
                            <div style="font-size: 12px; color: #334155; margin-bottom: 6px;">
                                <strong>Affected:</strong> ${inc.households} Families (${inc.population} persons)
                            </div>
                            ${inc.predicted_priority ? `
                                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 4px 6px; border-radius: 4px; font-size: 11px; margin-bottom: 6px;">
                                    <strong>ML Priority:</strong> ${inc.predicted_priority} | Need: ${inc.recommended_qty || 0} packs
                                </div>
                            ` : ''}
                            <div style="font-size: 11px; color: #475569; margin-bottom: 8px;">${inc.description}</div>
                            <a href="index.php?route=disaster-requests-show&id=${inc.id}" class="btn btn-primary btn-sm" style="color: #ffffff !important; display: inline-block;">
                                View Full Incident Details
                            </a>
                        </div>
                    `;
                    marker.bindPopup(popup);
                    incidentLayer.addLayer(marker);
                }
            });
        }

        // Update sync timestamp
        const syncTimeEl = document.getElementById('mapSyncTime');
        if (syncTimeEl) {
            syncTimeEl.textContent = new Date().toLocaleTimeString();
        }
    }

    function fetchAndRefresh() {
        fetch('index.php?route=api-map-data')
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    renderMapData(data);
                }
            })
            .catch(err => {
                console.warn('Map sync failed:', err);
            });
    }

    // Initial load
    fetchAndRefresh();

    // Auto-refresh polling every 20 seconds
    const intervalId = setInterval(fetchAndRefresh, 20000);

    // Manual Refresh button
    const refreshBtn = document.getElementById('btnMapRefresh');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            fetchAndRefresh();
        });
    }

    // Clean up when navigating away
    window.addEventListener('beforeunload', () => clearInterval(intervalId));

    return map;
};
