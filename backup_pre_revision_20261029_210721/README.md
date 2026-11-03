# Intelligent Resource Allocation Recommender for Barangay Disaster Preparedness Activities

**Academic Capstone Project — Bachelor of Science in Information Technology**  
**Institution:** College of Computer Studies, St. Peter's College, Iligan City, Philippines  
**Authors:** Angel Mae R. Nadayag, Jerry Nadayag, Marc Erfred Mabuti (May 2026)  
**Study Location:** Barangay Hinaplanon, Iligan City, Philippines  
**Theoretical Framework:** Technology Acceptance Model (TAM) & ISO 9126 Software Quality Model  

---

## 1. System Overview & Architecture

The **Intelligent Resource Allocation Recommender** is a specialized, web-based decision-support system designed to assist the Barangay Disaster Risk Reduction and Management Committee (BDRRMC) of Barangay Hinaplanon and the Iligan City Disaster Risk Reduction and Management Council (ICDRRMC). It automates and structures disaster resource planning through an explainable **Decision Tree Machine Learning** pipeline, preventing arbitrary resource distribution and stock deficits.

### Three-Tier Architectural Structure
- **Presentation Layer:** Responsive, accessible web interface developed with semantic HTML5, modern CSS3 (with hazard zone and priority color codes), and JavaScript for dynamic calculations.
- **Application Layer:** Modular PHP backend implementing Role-Based Access Control (RBAC), atomic database transactions, documented demand calculation formulas, and a native CART Decision Tree Classifier.
- **Data Layer:** Relational database layer with dual support for embedded **SQLite** (zero-configuration, instant execution) and **MySQL / MariaDB** (standard XAMPP deployment) implementing the complete Entity Relationship Diagram (ERD).

---

## 2. Seven Implemented Thesis Modules

| Module | Thesis Section & Diagrams | Features Implemented |
|---|---|---|
| **Module 1: User Management** | Section 1.1; Fig 5, 7–10 | Role-Based Access Control for 4 user types (ICDRRMO Personnel, Barangay Head, Responder/Rescuer, Resident); Resident account verification queue with Barangay Head approval/decline; secure password hashing with bcrypt; profile and session management. |
| **Module 2: Resource & Inventory Management** | Section 1.2; Table 1.5–1.7 | Centralized tracking for Relief Goods, Medical Supplies, Rescue Equipment, Clean Water, and Hygiene Kits; real-time Total Stock, Remaining Stock, Released Quantities, and Low Stock warnings; strict atomic constraints preventing allocations from exceeding available stock; exportable CSV reports. |
| **Module 3: Disaster Request Form** | Section 1.3; Table 1.4 | Comprehensive incident logging capturing disaster type (Flood, Flash Flood, Typhoon, Landslide), weather conditions (Shear Line, Heavy Rain), severity level, urgency flag, incident date, affected households (HH), affected population, and coordinates; auto-retrieves Purok risk classification. |
| **Module 4: Decision Tree ML & AI Recommendations** | Section 1.4; Table 1.8 | Supervised Decision Tree classification (CART/Gini) predicting priority (Urgent, High, Medium, Low); documented household demand calculations; transparent explainability showing exact decision path, conditions, confidence scores, and model version; mandatory official review with written justification; atomic inventory deduction upon approval. |
| **Module 5: Preparedness Activity Scheduling** | Section 1.5; Table 1.10 | Scheduling of evacuation drills, seminars, and awareness campaigns by Barangay Head; resident activity calendar; post-activity event evaluation recording turnout, ratings (1–5), problems encountered, and resource consumption. |
| **Module 6: Field Reporting** | Section 1.6; Fig 9 | Real-time ground status reporting by emergency responders; captures road and bridge accessibility (Passable, Flooded, Blocked), casualties, damaged houses, and immediate urgent needs; directly updates ongoing disaster requests. |
| **Module 7: Dashboard and Reporting** | Section 1.7; Fig 7–10 | Role-specific dashboards for ICDRRMO, Barangay Head, Responder, and Resident; key metrics, stock alerts, risk zone breakdown, exportable allocation ledger, and audit trail ledger (`resource_request_log`). |

---

## 3. Barangay Hinaplanon Hazard Map Integration

Digitized directly from the official **CamScanner handwritten hazard map** of Barangay Hinaplanon:
- **Spatial Landmark Context:** Bounded by the Mandulog River and Bayug Island to the north/north-west; traversed by the National Highway, concrete bridges, and interior drainage creeks; and bordered by adjacent barangays (Sta. Filomena, San Roque, Luinab, Del Carmen, Sto. Rosario, Bagong Silang).
- **Risk Zone Colors & Classifications:**
  - **High Risk (Pink):** Bayug Island, Purok 1 (riverbank), Purok 2A, Purok 2B, Purok 3A, Purok 5A, Purok 9 (historically devastated during Tropical Storm Sendong).
  - **Medium Risk (Orange):** Purok 2, Purok 2C, Purok 2D, Purok 3, Purok 4A, Purok 5 (Cabugason), Purok 6A, Phase 3 & 5 (Bayan).
  - **Low Risk (Yellow):** Purok 6, Purok 6B, Purok 6C, Purok 7 (designated safe evacuation zones and South Evacuation Center).
  - **Pending Verification (Gray):** Purok 8 (overlapping multi-zone boundary gradient with Barangay Del Carmen).
- **Official Review & Editing:** Authorized barangay officials can update classifications and add ground notes directly in the portal.

---

## 4. Machine Learning Pipeline & Data Integrity Protocol

In strict compliance with academic research standards:
- **Separation of Real vs Demo Data:** The supplied CamScanner document is an authentic hazard map, not a historical incident dataset. The system provides an empty structured CSV template for official record collection and an isolated **Synthetic Demonstration Dataset** (120 records derived from Sendong and Basyang scenarios) for demonstration and validation.
- **Ethics Guarantee:** If verified historical records are insufficient (< 10 records), the system transparently reports **"Not Trained — Insufficient Verified Data"** rather than fabricating predictions.
- **Evaluation Metrics (Held-out 20% Test Set):**
  - Multiclass Accuracy
  - Macro and Weighted Precision
  - Macro and Weighted Recall
  - Macro and Weighted F1-Score
  - Full 4×4 Confusion Matrix (Urgent, High, Medium, Low)
  - Feature Importances breakdown

---

## 5. Technology Acceptance Model (TAM) & ISO 9126 Quality Module

The system incorporates the exact empirical survey instrument specified in Chapter 3 of the thesis:
- **TAM Constructs:** Perceived Usefulness (PU) and Perceived Ease of Use (PEOU).
- **ISO 9126 Software Quality:** Functionality, Reliability, Usability, Efficiency, Maintainability, Portability.
- **Statistical Processing:** Automated calculation of Weighted Mean $\bar{x} = \frac{\sum (f \cdot x)}{N}$, Standard Deviation $SD$, and Likert verbal interpretation (5-point scale: 4.21–5.00 Strongly Agree/Excellent, 3.41–4.20 Agree/Very Satisfactory, etc.).
- **Sampling Support:** Total Enumeration sampling targeting 100 respondents (1 BDRRM Head, 15 Barangay Officials, 20 Responders/Personnel, 64 Residents).

---

## 6. Pre-Seeded Demonstration Accounts

| Role | Email | Password | Access Scope |
|---|---|---|---|
| **ICDRRMO Personnel** | `icdrrmo@iligan.gov.ph` | `icdrrmo123` | Full administrative control, inventory management, AI recommendations, user management, audit logs. |
| **Barangay Head** | `brgyhead@hinaplanon.gov.ph` | `brgyhead123` | Resident account approvals, disaster request submission, official review/approval of allocations, activity scheduling. |
| **Responder / Rescuer** | `responder1@hinaplanon.gov.ph` | `responder123` | Ground status submissions, event evaluation, disaster request viewing, activity calendar. |
| **Resident (Approved)** | `juan.delacruz@gmail.com` | `resident123` | Activity calendar, completed event history, hazard map reference, profile management. |
| **Resident (Pending)** | `maria.santos@gmail.com` | `resident123` | Registration submitted; demonstrates pending approval block by Barangay Head. |

---

## 7. Quick Start & Execution Instructions

### A. Run the Quality Assurance Test Suite
Execute all 37 automated unit and integration tests:
```bash
php tests/run_all_tests.php
```

### B. Run the Decision Tree Training Pipeline
Train and evaluate the Decision Tree on the demonstration dataset:
```bash
php ml/train.php --source=demo
```

### C. Start the Web Application
Double-click `serve.bat` or run:
```bash
php -S 0.0.0.0:8000 -t public
```
Then open your web browser and navigate to:
```
http://localhost:8000
```
Use the quick-fill buttons on the login page to immediately test any of the 4 user roles.

---

## 8. Directory Structure

```
c:\Users\User\Downloads\thesis\
├── config\
│   ├── app.php                   # Core application configuration
│   └── database.php              # Dual SQLite/MySQL database configuration
├── database\
│   ├── schema_sqlite.sql         # SQLite schema matching ERD
│   ├── schema_mysql.sql          # MySQL schema matching ERD
│   ├── seed.php                  # Database migration and seeder
│   └── disaster_recommender.sqlite # Embedded database file
├── src\
│   ├── Core\
│   │   ├── Database.php          # Singleton PDO connection wrapper
│   │   ├── Auth.php              # Session, RBAC, password security
│   │   ├── Request.php           # Sanitization and CSRF verification
│   │   ├── Router.php            # Route dispatcher
│   │   └── View.php              # Template renderer with layout support
│   ├── Models\
│   │   ├── User.php              # User accounts & resident approvals
│   │   ├── Barangay.php          # Barangay information
│   │   ├── Purok.php             # Hinaplanon Puroks & risk classifications
│   │   ├── Supply.php            # Centralized inventory with atomic deduction
│   │   ├── DisasterRequest.php   # Disaster incident request forms
│   │   ├── ResourceRequest.php   # Resource request linkages
│   │   ├── Recommendation.php    # AI recommendation reviews & justifications
│   │   ├── RequestLog.php        # Immutable audit ledger
│   │   ├── PreparednessActivity.php # Activity scheduling & calendar
│   │   ├── FieldReport.php       # Ground responder reports
│   │   ├── EventEvaluation.php   # Post-drill evaluations
│   │   ├── HistoricalRecord.php  # CSV import/export & datasets
│   │   └── TamEvaluation.php     # TAM & ISO 9126 statistical processor
│   ├── ML\
│   │   ├── DecisionTreeClassifier.php # CART Decision Tree with Gini Impurity
│   │   ├── FeatureExtractor.php       # Structured input encoder
│   │   ├── ModelEvaluator.php         # Multiclass Accuracy, Precision, Recall, F1, CM
│   │   ├── DemandCalculator.php       # Documented household demand formulas
│   │   └── RecommenderEngine.php      # End-to-end prioritization & explanation
│   └── Controllers\
│       ├── AuthController.php
│       ├── DashboardController.php
│       ├── UserController.php
│       ├── InventoryController.php
│       ├── DisasterRequestController.php
│       ├── RecommendationController.php
│       ├── PreparednessController.php
│       ├── FieldReportController.php
│       ├── HazardMapController.php
│       ├── MLController.php
│       ├── ReportController.php
│       └── EvaluationController.php
├── public\
│   ├── index.php                 # Front controller & PSR-4 autoloader
│   └── assets\
│       ├── css\style.css         # Responsive disaster management UI stylesheet
│       ├── js\app.js             # Client interactivity & demo autofill
│       └── images\
│           └── hinaplanon_hazard_map.jpg # Authentic CamScanner Hazard Map
├── views\                        # Template views for all 7 modules
├── ml\
│   ├── train.php                 # CLI model training pipeline
│   ├── train_decision_tree.py    # Python scikit-learn script
│   └── data\
│       ├── template_historical_disaster_records.csv
│       ├── hinaplanon_synthetic_demonstration_records.csv
│       └── active_model.json     # Serialized active Decision Tree model
├── tests\
│   └── run_all_tests.php         # Complete QA test suite
├── README.md
└── serve.bat                     # Windows web server launcher
```
