-- SQLite Database Schema for Intelligent Resource Allocation Recommender
-- Barangay Hinaplanon, Iligan City

CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_type TEXT NOT NULL CHECK(user_type IN ('ICDRRMO', 'Barangay Head', 'Responder', 'Resident')),
    id_number TEXT,
    image TEXT,
    lastname TEXT NOT NULL,
    firstname TEXT NOT NULL,
    gender TEXT CHECK(gender IN ('Male', 'Female', 'Other', '')),
    cellphone_number TEXT,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    plain_password TEXT,
    is_approved INTEGER NOT NULL DEFAULT 1, -- Auto-approved for admin/official; 0 for new Residents
    approved_by INTEGER,
    approved_at DATETIME,
    rejection_reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME
);

CREATE TABLE IF NOT EXISTS barangay (
    barangay_id INTEGER PRIMARY KEY AUTOINCREMENT,
    barangay_name TEXT NOT NULL UNIQUE,
    location TEXT,
    latitude REAL,
    longitude REAL,
    population_size INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME
);

CREATE TABLE IF NOT EXISTS purok (
    purok_id INTEGER PRIMARY KEY AUTOINCREMENT,
    barangay_id INTEGER NOT NULL,
    purok_name TEXT NOT NULL,
    risk_classification TEXT NOT NULL CHECK(risk_classification IN ('High', 'Medium', 'Low', 'Pending Verification')),
    risk_zone_color TEXT NOT NULL CHECK(risk_zone_color IN ('Pink', 'Orange', 'Yellow', 'Gray')),
    hazard_source_reference TEXT DEFAULT 'Handwritten Hazard Map Hinaplanon CamScanner',
    notes TEXT,
    latitude REAL,
    longitude REAL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME,
    FOREIGN KEY (barangay_id) REFERENCES barangay(barangay_id)
);

CREATE TABLE IF NOT EXISTS supply (
    supply_id INTEGER PRIMARY KEY AUTOINCREMENT,
    barangay_id INTEGER NOT NULL,
    supply_type TEXT NOT NULL CHECK(supply_type IN ('Relief Goods', 'Medical Supplies', 'Rescue Equipment', 'Clean Water', 'Hygiene Kits', 'Shelter Kits')),
    supply_name TEXT NOT NULL,
    supply_quantity INTEGER NOT NULL DEFAULT 0,  -- Total stock recorded
    supply_remaining INTEGER NOT NULL DEFAULT 0, -- Available current inventory
    total_release INTEGER NOT NULL DEFAULT 0,    -- Cumulative released quantity
    supply_status TEXT NOT NULL DEFAULT 'In Stock' CHECK(supply_status IN ('In Stock', 'Low Stock', 'Critical', 'Out of Stock')),
    unit_of_measure TEXT DEFAULT 'packs',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME,
    FOREIGN KEY (barangay_id) REFERENCES barangay(barangay_id)
);

CREATE TABLE IF NOT EXISTS disaster_request_form (
    disaster_request_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    barangay_id INTEGER NOT NULL,
    purok_id INTEGER NOT NULL,
    request_id INTEGER,
    disaster_type TEXT NOT NULL CHECK(disaster_type IN ('Flood', 'Flash Flood', 'Typhoon', 'Landslide', 'Fire', 'Storm Surge', 'Earthquake', 'Other')),
    weather_name TEXT NOT NULL,
    description TEXT NOT NULL,
    severity_level TEXT NOT NULL CHECK(severity_level IN ('Critical', 'High', 'Medium', 'Low')),
    is_urgent INTEGER NOT NULL DEFAULT 0, -- 1=Urgent, 0=Standard
    affected_households INTEGER NOT NULL DEFAULT 1,
    affected_population INTEGER NOT NULL DEFAULT 1,
    disaster_date DATE NOT NULL,
    status TEXT NOT NULL DEFAULT 'Pending' CHECK(status IN ('Pending', 'Recommended', 'Approved', 'Declined', 'Completed', 'Reported', 'Under Verification', 'Verified', 'Assigned', 'Responding', 'Resolved', 'Rejected')),
    latitude REAL,
    longitude REAL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (barangay_id) REFERENCES barangay(barangay_id),
    FOREIGN KEY (purok_id) REFERENCES purok(purok_id)
);

CREATE TABLE IF NOT EXISTS resource_request (
    request_id INTEGER PRIMARY KEY AUTOINCREMENT,
    disaster_request_id INTEGER,
    supply_id INTEGER,
    barangay_id INTEGER NOT NULL,
    purok_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    requested_by TEXT NOT NULL,
    request_date DATE NOT NULL,
    request_quantity INTEGER NOT NULL DEFAULT 1,
    request_status TEXT NOT NULL DEFAULT 'Pending' CHECK(request_status IN ('Pending', 'Recommended', 'Approved', 'Rejected', 'Fulfilled')),
    remarks TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME,
    FOREIGN KEY (disaster_request_id) REFERENCES disaster_request_form(disaster_request_id),
    FOREIGN KEY (supply_id) REFERENCES supply(supply_id),
    FOREIGN KEY (barangay_id) REFERENCES barangay(barangay_id),
    FOREIGN KEY (purok_id) REFERENCES purok(purok_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS requested_supplies (
    requested_supplies_id INTEGER PRIMARY KEY AUTOINCREMENT,
    request_id INTEGER NOT NULL,
    supply_id INTEGER NOT NULL,
    requested_supplies_quantity INTEGER NOT NULL,
    FOREIGN KEY (request_id) REFERENCES resource_request(request_id),
    FOREIGN KEY (supply_id) REFERENCES supply(supply_id)
);

CREATE TABLE IF NOT EXISTS recommendation (
    recommendation_id INTEGER PRIMARY KEY AUTOINCREMENT,
    request_id INTEGER,
    disaster_request_id INTEGER,
    purok_id INTEGER NOT NULL,
    predicted_priority TEXT NOT NULL CHECK(predicted_priority IN ('Urgent', 'High', 'Medium', 'Low')),
    recommended_supply_id INTEGER,
    recommended_quantity INTEGER NOT NULL,
    confidence_score REAL NOT NULL DEFAULT 0.0,
    recommendation_reason TEXT NOT NULL,
    explanation_details TEXT, -- JSON breakdown of tree decision path & rules
    model_version TEXT NOT NULL DEFAULT 'v1.0-decision-tree',
    recommendation_status TEXT NOT NULL DEFAULT 'Generated' CHECK(recommendation_status IN ('Generated', 'Approved', 'Modified', 'Rejected')),
    justification TEXT,
    reviewed_by INTEGER,
    reviewed_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME,
    FOREIGN KEY (request_id) REFERENCES resource_request(request_id),
    FOREIGN KEY (disaster_request_id) REFERENCES disaster_request_form(disaster_request_id),
    FOREIGN KEY (purok_id) REFERENCES purok(purok_id),
    FOREIGN KEY (recommended_supply_id) REFERENCES supply(supply_id),
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS resource_request_log (
    log_id INTEGER PRIMARY KEY AUTOINCREMENT,
    request_id INTEGER,
    recommendation_id INTEGER,
    recommended_by TEXT,
    action_taken TEXT NOT NULL,
    action_by TEXT NOT NULL,
    action_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    remarks TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME,
    FOREIGN KEY (request_id) REFERENCES resource_request(request_id),
    FOREIGN KEY (recommendation_id) REFERENCES recommendation(recommendation_id)
);

CREATE TABLE IF NOT EXISTS preparedness_activities (
    preparedness_activities_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    barangay_id INTEGER NOT NULL,
    activity_type TEXT NOT NULL CHECK(activity_type IN ('Disaster Drill', 'Training & Seminar', 'Information Campaign', 'Evacuation Simulation', 'Community Meeting')),
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    target_purok_id INTEGER,
    schedule_date DATETIME NOT NULL,
    location TEXT NOT NULL,
    assigned_personnel TEXT,
    volunteer_person TEXT,
    status TEXT NOT NULL DEFAULT 'Scheduled' CHECK(status IN ('Scheduled', 'Ongoing', 'Completed', 'Cancelled')),
    max_participants INTEGER DEFAULT 100,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (barangay_id) REFERENCES barangay(barangay_id),
    FOREIGN KEY (target_purok_id) REFERENCES purok(purok_id)
);

CREATE TABLE IF NOT EXISTS field_reports (
    report_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    disaster_request_id INTEGER,
    purok_id INTEGER NOT NULL,
    responder_name TEXT NOT NULL,
    status_update TEXT NOT NULL,
    road_accessibility TEXT NOT NULL CHECK(road_accessibility IN ('Passable', 'Passable to Heavy Vehicles Only', 'Not Passable / Blocked', 'Flooded')),
    casualties_count INTEGER NOT NULL DEFAULT 0,
    damaged_houses INTEGER NOT NULL DEFAULT 0,
    urgent_needs TEXT,
    latitude REAL,
    longitude REAL,
    report_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status TEXT NOT NULL DEFAULT 'Submitted' CHECK(status IN ('Submitted', 'Verified', 'Rejected')),
    reviewed_by INTEGER,
    reviewed_at DATETIME,
    review_notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (disaster_request_id) REFERENCES disaster_request_form(disaster_request_id),
    FOREIGN KEY (purok_id) REFERENCES purok(purok_id)
);

CREATE TABLE IF NOT EXISTS event_evaluations (
    evaluation_id INTEGER PRIMARY KEY AUTOINCREMENT,
    preparedness_activities_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    actual_turnout INTEGER NOT NULL DEFAULT 0,
    objectives_met_rating INTEGER NOT NULL CHECK(objectives_met_rating BETWEEN 1 AND 5),
    problems_encountered TEXT,
    supplies_used TEXT,
    recommendations_for_improvement TEXT,
    evaluated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (preparedness_activities_id) REFERENCES preparedness_activities(preparedness_activities_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS historical_disaster_records (
    record_id INTEGER PRIMARY KEY AUTOINCREMENT,
    disaster_date DATE NOT NULL,
    disaster_type TEXT NOT NULL,
    weather_condition TEXT NOT NULL,
    affected_barangay TEXT NOT NULL DEFAULT 'Hinaplanon',
    affected_purok TEXT NOT NULL,
    purok_risk_level TEXT NOT NULL CHECK(purok_risk_level IN ('High', 'Medium', 'Low', 'Pending Verification')),
    severity_level TEXT NOT NULL CHECK(severity_level IN ('Critical', 'High', 'Medium', 'Low')),
    is_urgent INTEGER NOT NULL DEFAULT 0,
    affected_households INTEGER NOT NULL DEFAULT 1,
    affected_population INTEGER NOT NULL DEFAULT 1,
    requested_supply_type TEXT NOT NULL,
    requested_quantity INTEGER NOT NULL,
    allocated_quantity INTEGER NOT NULL,
    priority_label TEXT NOT NULL CHECK(priority_label IN ('Urgent', 'High', 'Medium', 'Low')),
    data_source TEXT NOT NULL, -- e.g., 'Historical BDRRMC Incident Log', 'Expert Validated', 'Synthetic Demo'
    is_synthetic INTEGER NOT NULL DEFAULT 0, -- 1=Synthetic demonstration only, 0=Verified official record
    is_verified INTEGER NOT NULL DEFAULT 1,
    verified_by TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS model_versions (
    model_version_id INTEGER PRIMARY KEY AUTOINCREMENT,
    version_name TEXT NOT NULL UNIQUE,
    algorithm TEXT NOT NULL DEFAULT 'DecisionTreeClassifier (CART/ID3)',
    hyperparameters_json TEXT,
    dataset_source TEXT NOT NULL,
    training_samples_count INTEGER NOT NULL,
    test_samples_count INTEGER NOT NULL,
    accuracy REAL NOT NULL,
    precision_score REAL NOT NULL,
    recall_score REAL NOT NULL,
    f1_score REAL NOT NULL,
    confusion_matrix_json TEXT NOT NULL,
    tree_rules_json TEXT NOT NULL,
    is_active INTEGER NOT NULL DEFAULT 0,
    trained_by TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tam_evaluations (
    tam_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    respondent_type TEXT NOT NULL CHECK(respondent_type IN ('BDRRM Head', 'Barangay Official', 'Personnel/Volunteer', 'Resident')),
    -- TAM Core Constructs (1 to 5 scale)
    pu_q1 INTEGER NOT NULL CHECK(pu_q1 BETWEEN 1 AND 5),
    pu_q2 INTEGER NOT NULL CHECK(pu_q2 BETWEEN 1 AND 5),
    pu_q3 INTEGER NOT NULL CHECK(pu_q3 BETWEEN 1 AND 5),
    pu_q4 INTEGER NOT NULL CHECK(pu_q4 BETWEEN 1 AND 5),
    peou_q1 INTEGER NOT NULL CHECK(peou_q1 BETWEEN 1 AND 5),
    peou_q2 INTEGER NOT NULL CHECK(peou_q2 BETWEEN 1 AND 5),
    peou_q3 INTEGER NOT NULL CHECK(peou_q3 BETWEEN 1 AND 5),
    peou_q4 INTEGER NOT NULL CHECK(peou_q4 BETWEEN 1 AND 5),
    -- ISO 9126 Criteria (1 to 5 scale)
    iso_functionality INTEGER NOT NULL CHECK(iso_functionality BETWEEN 1 AND 5),
    iso_reliability INTEGER NOT NULL CHECK(iso_reliability BETWEEN 1 AND 5),
    iso_usability INTEGER NOT NULL CHECK(iso_usability BETWEEN 1 AND 5),
    iso_efficiency INTEGER NOT NULL CHECK(iso_efficiency BETWEEN 1 AND 5),
    iso_maintainability INTEGER NOT NULL CHECK(iso_maintainability BETWEEN 1 AND 5),
    iso_portability INTEGER NOT NULL CHECK(iso_portability BETWEEN 1 AND 5),
    qualitative_feedback TEXT,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
