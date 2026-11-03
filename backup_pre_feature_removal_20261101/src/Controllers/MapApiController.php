<?php
// src/Controllers/MapApiController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\Purok;
use App\Models\Shelter;
use App\Models\DisasterRequest;
use App\Models\FieldReport;
use App\Models\Notification;
use App\Models\Recommendation;

class MapApiController {
    public function getMapData(): void {
        $isAuth = Auth::check();
        $role = $isAuth ? (string)Auth::role() : 'Public';
        $userId = $isAuth ? (int)Auth::id() : 0;

        // 1. All Puroks with risk classifications & colors
        $puroks = Purok::all();
        $formattedPuroks = [];
        foreach ($puroks as $p) {
            $formattedPuroks[] = [
                'id' => (int)$p['purok_id'],
                'name' => $p['purok_name'],
                'risk' => $p['risk_classification'],
                'color' => $p['risk_zone_color'],
                'lat' => (float)$p['latitude'],
                'lng' => (float)$p['longitude'],
                'notes' => $p['notes'] ?? ''
            ];
        }

        // 2. Shelters (Public & Private)
        $shelters = Shelter::all();
        $formattedShelters = [];
        foreach ($shelters as $s) {
            $formattedShelters[] = [
                'id' => (int)$s['shelter_id'],
                'name' => $s['shelter_name'],
                'purok' => $s['purok_name'],
                'location' => $s['location'],
                'occupancy' => (int)$s['current_occupancy'],
                'max_capacity' => (int)$s['max_capacity'],
                'status' => $s['availability_status'],
                'contact' => $isAuth ? ($s['contact_person'] . ' (' . $s['contact_number'] . ')') : 'Inquire via BDRRM',
                'lat' => (float)$s['latitude'],
                'lng' => (float)$s['longitude']
            ];
        }

        // 3. Incidents based on Authorization Level
        $formattedIncidents = [];
        if ($isAuth) {
            $filters = [];
            if ($role === 'Responder') {
                $filters['assigned_to_user_id'] = $userId;
            }

            $incidents = DisasterRequest::all($filters);
            foreach ($incidents as $inc) {
                // Find associated recommendation if any
                $rec = Recommendation::findByDisasterRequestId((int)$inc['disaster_request_id']);

                $lat = !empty($inc['latitude']) ? (float)$inc['latitude'] : null;
                $lng = !empty($inc['longitude']) ? (float)$inc['longitude'] : null;

                // Fall back to Purok centroid if incident latitude/longitude was not geotagged
                if (!$lat || !$lng) {
                    $p = Purok::findById((int)$inc['purok_id']);
                    $lat = (float)($p['latitude'] ?? 8.2412);
                    $lng = (float)($p['longitude'] ?? 124.2543);
                }

                $formattedIncidents[] = [
                    'id' => (int)$inc['disaster_request_id'],
                    'ref' => $inc['reference_no'] ?? ('DR-' . $inc['disaster_request_id']),
                    'purok' => $inc['purok_name'],
                    'disaster_type' => $inc['disaster_type'],
                    'severity' => $inc['severity_level'],
                    'is_urgent' => (bool)$inc['is_urgent'],
                    'is_sos' => (bool)($inc['is_sos'] ?? 0),
                    'status' => $inc['status'],
                    'households' => (int)$inc['affected_households'],
                    'population' => (int)$inc['affected_population'],
                    'description' => substr($inc['description'], 0, 150),
                    'responder' => $inc['responder_first'] ? ($inc['responder_first'] . ' ' . $inc['responder_last']) : 'Unassigned',
                    'predicted_priority' => $rec['predicted_priority'] ?? null,
                    'recommended_qty' => isset($rec['recommended_quantity']) ? (int)$rec['recommended_quantity'] : null,
                    'lat' => $lat,
                    'lng' => $lng
                ];
            }
        }

        // 4. Approved Public Disaster Alerts (Visible on Login & Dashboards)
        $publicAlerts = Notification::getForUser(0, 'Resident', 5, 'emergency_alert');

        // 5. Responder Field Updates
        $formattedFieldReports = [];
        if ($isAuth && in_array($role, ['ICDRRMO', 'Barangay Head', 'Responder'])) {
            $fieldReports = FieldReport::all();
            foreach ($fieldReports as $fr) {
                $formattedFieldReports[] = [
                    'id' => (int)$fr['report_id'],
                    'purok' => $fr['purok_name'],
                    'responder' => $fr['responder_name'],
                    'accessibility' => $fr['road_accessibility'],
                    'casualties' => (int)$fr['casualties_count'],
                    'damaged_houses' => (int)$fr['damaged_houses'],
                    'urgent_needs' => $fr['urgent_needs'] ?? '',
                    'status_update' => $fr['status_update'],
                    'time' => date('h:i A', strtotime($fr['report_time'])),
                    'lat' => (float)$fr['latitude'],
                    'lng' => (float)$fr['longitude']
                ];
            }
        }

        View::json([
            'success' => true,
            'role' => $role,
            'is_auth' => $isAuth,
            'timestamp' => date('Y-m-d H:i:s'),
            'puroks' => $formattedPuroks,
            'shelters' => $formattedShelters,
            'incidents' => $formattedIncidents,
            'alerts' => $publicAlerts,
            'field_reports' => $formattedFieldReports
        ]);
    }
}
