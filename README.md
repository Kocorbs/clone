# Intelligent Resource Allocation Recommender for Barangay Disaster Preparedness Activities

**Academic Capstone Project — Bachelor of Science in Information Technology**  
**Institution:** College of Computer Studies, St. Peter's College, Iligan City, Philippines  
**Authors:** Angel Mae R. Nadayag, Jerry Nadayag, Marc Erfred Mabuti (May 2026)  
**Study Location:** Barangay Hinaplanon, Iligan City, Philippines  
**Theoretical Framework:** Technology Acceptance Model (TAM; Davis, 1989) & ISO 9126 Software Quality Model  

---

## 1. System Overview & Architecture

The **Intelligent Resource Allocation Recommender** is an advanced, explainable disaster decision-support system built specifically for the Barangay Disaster Risk Reduction and Management Committee (BDRRMC) of Barangay Hinaplanon and the Iligan City Disaster Risk Reduction and Management Council (ICDRRMC). It automates and structures disaster resource planning through an explainable **Decision Tree Machine Learning (CART / Gini)** pipeline, preventing arbitrary resource distribution and stock deficits.

### Three-Tier Architectural Structure
- **Presentation Layer (Frontend):** Modern, clean government emergency command dashboard interface developed with HTML5, CSS3, JavaScript, and **Leaflet.js (OpenStreetMap)** for live GIS spatial mapping. Responsive on desktop, tablet, and mobile.
- **Application Layer (Business Logic):** Modular, object-oriented PHP 8.2 MVC backend with native Role-Based Access Control (RBAC), atomic database transactions, 6-stage incident lifecycle management, role-based real-time notification dispatch, and a native CART Decision Tree Classifier.
- **Data Layer (Storage & Persistence):** Relational database layer with dual support for embedded **SQLite** (zero-configuration, instant execution) and **MySQL / MariaDB** (standard XAMPP deployment) implementing the complete Entity Relationship Diagram (ERD).

---

## 2. Seven Core Thesis Modules & Extended Capabilities

| Module | Scope & Standards | Features Implemented |
|---|---|---|
| **Module 1: User Management & Barangay ID Gatekeeper** | Capstone Module 1; RBAC | 4 user roles: ICDRRMO Personnel, Barangay Head, Responder/Rescuer, Resident. Resident registration using official **Barangay ID Number** with Barangay Head verification queue; multi-identifier login (Barangay ID, Email, or Username); Bcrypt password hashing; local session store in `database/sessions`. |
| **Module 2: Resource & Inventory Management** | Capstone Module 2 | Centralized inventory tracking for Relief Goods, Medical Supplies, Rescue Equipment, Clean Water, and Hygiene Kits; real-time Total Stock, Remaining Stock, Released Quantities, and Low Stock warnings; strict atomic constraints preventing over-allocation; exportable CSV reports. |
| **Module 3: Disaster Request Form & Emergency SOS** | Capstone Module 3 | Comprehensive incident logging capturing disaster type, weather, severity, urgency, households, population, and geotag coordinates; public and authenticated **Emergency SOS submission** generating official tracking reference numbers (`SOS-YYYYMMDD-XXXXX`). |
| **Module 4: Decision Tree ML & Explainable AI** | Capstone Module 4; CART / Gini | Supervised Decision Tree classification (CART with Gini impurity) predicting priority (Urgent, High, Medium, Low); documented household demand calculations (1.10x buffer for Urgent); **Human-Centered 5-Question AI Brief** for barangay leaders with expandable technical Decision Tree node inspection; mandatory written justification; atomic inventory deduction upon approval. |
| **Module 5: Preparedness Activity Scheduling** | Capstone Module 5 | Scheduling of community drills, seminars, and flood readiness campaigns; resident activity calendar; post-activity evaluation tracking community turnout, satisfaction ratings (1–5), problems encountered, and resource consumption. |
| **Module 6: Field Reporting & Verification Workflow** | Capstone Module 6 | Real-time ground status reporting by responders; captures road passability, casualties, and infrastructure damage; 6-stage operational lifecycle (`Reported` &rarr; `Under Verification` &rarr; `Verified` &rarr; `Assigned` &rarr; `Responding` &rarr; `Resolved`) with immutable status history. |
| **Module 7: Command Dashboard & Security Ledger** | Capstone Module 7; ISO 9126 | Role-specific dashboards for ICDRRMO, Barangay Head, Responder, and Resident; embedded live Leaflet hazard map; real-time notifications with unread topbar badge counter; Evacuation Shelter monitoring; and immutable **System Audit Trail Ledger** (`audit_logs`). |

---

## 3. Interactive Leaflet.js Hazard Map & CamScanner Reference

The system bridges authentic empirical ground data with modern interactive web GIS:
- **Spatial Georeferenced Coordinates:** Centered at Barangay Hinaplanon (8.2412° N, 124.2543° E) bounded by the Mandulog River and Bayug Island to the north, traversed by the National Highway and interior creeks.
- **Risk Zones from Hand-Drawn CamScanner Hazard Map:**
  - **High Risk (Pink):** Bayug Island, Purok 1 (riverbank), Purok 2A, Purok 2B, Purok 3A, Purok 5A, Purok 9 (historically inundated during Tropical Storm Sendong).
  - **Medium Risk (Orange):** Purok 2, Purok 2C, Purok 2D, Purok 3, Purok 4A, Purok 5 (Cabugason), Purok 6A, Phase 3 & 5 (Bayan).
  - **Low Risk (Yellow):** Purok 6, Purok 6B, Purok 6C, Purok 7 (designated safe evacuation zones and Hinaplanon Evacuation Center).
  - **Pending Verification (Gray):** Purok 8 (overlapping multi-zone boundary gradient with Barangay Del Carmen).
- **Dual Map Modes:**
  1. **Live Interactive Leaflet GIS Map:** Real-time auto-polling (every 20s) with active incident markers, evacuation shelter status, purok risk circles, and manual refresh controls.
  2. **Official CamScanner Scanned Map Viewer:** High-resolution zoomable viewer of the authentic hand-drawn BDRRMC hazard map.
- **Privacy Protection:** Public login map protects victim privacy by displaying only public risk zones, shelter capacities, and general alerts; victim names and private coordinates are restricted to authenticated emergency personnel.

---

## 4. Evacuation Shelter Live Monitoring

- Tracks all 3 official evacuation centers in Barangay Hinaplanon:
  1. **Hinaplanon Evacuation Center (South)** — Purok 7 (Capacity: 350 evacuees)
  2. **Hinaplanon Barangay Gym** — Purok 1 (Capacity: 200 evacuees)
  3. **Hinaplanon Central School Shelters** — Purok 6 (Capacity: 400 evacuees)
- Real-time occupancy status: `Open`, `Near Full` (&ge;85%), and `Full` (100%).
- Real-time occupancy progress bars and quick update modal for shelter managers.

---

## 5. Pre-Seeded Demonstration Accounts

| Role | Username / Identifier | Password | Access Scope |
|---|---|---|---|
| **ICDRRMO Personnel** | `icdrrmo@iligan.gov.ph` | `icdrrmo123` | Full city-level disaster command, supply inventory, AI recommendations, system audit logs, user management. |
| **Barangay Head** | `brgyhead@hinaplanon.gov.ph` | `brgyhead123` | Resident registration approvals, incident verification, responder dispatch, allocation approval, shelter management. |
| **Responder / Rescuer** | `responder1@hinaplanon.gov.ph` | `responder123` | Assigned emergency operations, ground status reports, road passability assessments, activity evaluation. |
| **Resident (Approved)** | `RES-2026-001` or `juan.delacruz@gmail.com` | `resident123` | Emergency SOS submission, relief supply requests, shelter lookup, preparedness calendar, hazard map. |
| **Resident (Pending Verification)** | `maria.santos@gmail.com` | `resident123` | Demonstrates the resident registration approval queue; blocked from login until Barangay Head approves. |

---

## 6. Execution & Verification Instructions

### A. Run the Quality Assurance Test Suite
Execute the comprehensive automated test suite (68 unit and integration tests across 11 test suites):
```bash
php tests/run_all_tests.php
```

### B. Run the MVC Route Endpoint Prober
Verify all web endpoints and view templates render cleanly:
```bash
php tests/probe_routes.php
```

### C. Run the Decision Tree Training Pipeline
Train and evaluate the Decision Tree classifier on the demonstration dataset:
```bash
php ml/train.php --source=demo
```

### D. Start the Local Web Server
Start the PHP built-in web server:
```bash
php -S localhost:8000 -t public
```
Then open your web browser at:
`http://localhost:8000/index.php?route=login`

---

## 7. Research Evaluation & Compliance

- **Theoretical Framework:**
  - **Technology Acceptance Model (TAM; Davis, 1989):** Perceived Usefulness (PU) and Perceived Ease of Use (PEOU).
  - **ISO 9126 Software Quality Model:** Functionality, Reliability, Usability, Efficiency, Maintainability, Portability.
- **Statistical Treatment:** Automated computation of Weighted Mean ($\bar{x}$), Standard Deviation ($SD$), and Likert 5-point verbal interpretation (Strongly Agree, Agree, Neutral, Disagree, Strongly Disagree).
- **Sampling Framework:** Total Enumeration sampling targeting 100 respondents (1 BDRRM Head, 15 Barangay Officials, 20 Responders, 64 Residents).
