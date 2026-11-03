-- MySQL Schema for Intelligent Resource Allocation Recommender
-- Barangay Hinaplanon, Iligan City

-- Import into an existing, selected database (also supports shared hosting).
-- Create/select the database in the hosting control panel before importing.

CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_type` ENUM('ICDRRMO', 'Barangay Head', 'Responder', 'Resident') NOT NULL,
    `id_number` VARCHAR(50) NULL,
    `image` VARCHAR(255) NULL,
    `lastname` VARCHAR(100) NOT NULL,
    `firstname` VARCHAR(100) NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other', '') DEFAULT '',
    `cellphone_number` VARCHAR(20) NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `plain_password` VARCHAR(255) NULL,
    `is_approved` TINYINT(1) NOT NULL DEFAULT 1,
    `approved_by` INT NULL,
    `approved_at` DATETIME NULL,
    `rejection_reason` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `barangay` (
    `barangay_id` INT AUTO_INCREMENT PRIMARY KEY,
    `barangay_name` VARCHAR(100) NOT NULL UNIQUE,
    `location` TEXT NULL,
    `latitude` DECIMAL(10, 7) NULL,
    `longitude` DECIMAL(10, 7) NULL,
    `population_size` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `purok` (
    `purok_id` INT AUTO_INCREMENT PRIMARY KEY,
    `barangay_id` INT NOT NULL,
    `purok_name` VARCHAR(100) NOT NULL,
    `risk_classification` ENUM('High', 'Medium', 'Low', 'Pending Verification') NOT NULL,
    `risk_zone_color` ENUM('Pink', 'Orange', 'Yellow', 'Gray') NOT NULL,
    `hazard_source_reference` VARCHAR(255) DEFAULT 'Handwritten Hazard Map Hinaplanon CamScanner',
    `notes` TEXT NULL,
    `latitude` DECIMAL(10, 7) NULL,
    `longitude` DECIMAL(10, 7) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`barangay_id`) REFERENCES `barangay`(`barangay_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `supply` (
    `supply_id` INT AUTO_INCREMENT PRIMARY KEY,
    `barangay_id` INT NOT NULL,
    `supply_type` ENUM('Relief Goods', 'Medical Supplies', 'Rescue Equipment', 'Clean Water', 'Hygiene Kits', 'Shelter Kits') NOT NULL,
    `supply_name` VARCHAR(100) NOT NULL,
    `supply_quantity` INT NOT NULL DEFAULT 0,
    `supply_remaining` INT NOT NULL DEFAULT 0,
    `total_release` INT NOT NULL DEFAULT 0,
    `supply_status` ENUM('In Stock', 'Low Stock', 'Critical', 'Out of Stock') NOT NULL DEFAULT 'In Stock',
    `unit_of_measure` VARCHAR(50) DEFAULT 'packs',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`barangay_id`) REFERENCES `barangay`(`barangay_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `disaster_request_form` (
    `disaster_request_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `barangay_id` INT NOT NULL,
    `purok_id` INT NOT NULL,
    `request_id` INT NULL,
    `disaster_type` ENUM('Flood', 'Flash Flood', 'Typhoon', 'Landslide', 'Fire', 'Storm Surge', 'Earthquake', 'Other') NOT NULL,
    `weather_name` VARCHAR(100) NOT NULL,
    `description` TEXT NOT NULL,
    `severity_level` ENUM('Critical', 'High', 'Medium', 'Low') NOT NULL,
    `is_urgent` TINYINT(1) NOT NULL DEFAULT 0,
    `affected_households` INT NOT NULL DEFAULT 1,
    `affected_population` INT NOT NULL DEFAULT 1,
    `disaster_date` DATE NOT NULL,
    `status` ENUM('Pending', 'Recommended', 'Approved', 'Declined', 'Completed') NOT NULL DEFAULT 'Pending',
    `latitude` DECIMAL(10, 7) NULL,
    `longitude` DECIMAL(10, 7) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`),
    FOREIGN KEY (`barangay_id`) REFERENCES `barangay`(`barangay_id`),
    FOREIGN KEY (`purok_id`) REFERENCES `purok`(`purok_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `resource_request` (
    `request_id` INT AUTO_INCREMENT PRIMARY KEY,
    `disaster_request_id` INT NULL,
    `supply_id` INT NULL,
    `barangay_id` INT NOT NULL,
    `purok_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `requested_by` VARCHAR(100) NOT NULL,
    `request_date` DATE NOT NULL,
    `request_quantity` INT NOT NULL DEFAULT 1,
    `request_status` ENUM('Pending', 'Recommended', 'Approved', 'Rejected', 'Fulfilled') NOT NULL DEFAULT 'Pending',
    `remarks` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`disaster_request_id`) REFERENCES `disaster_request_form`(`disaster_request_id`),
    FOREIGN KEY (`supply_id`) REFERENCES `supply`(`supply_id`),
    FOREIGN KEY (`barangay_id`) REFERENCES `barangay`(`barangay_id`),
    FOREIGN KEY (`purok_id`) REFERENCES `purok`(`purok_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `requested_supplies` (
    `requested_supplies_id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL,
    `supply_id` INT NOT NULL,
    `requested_supplies_quantity` INT NOT NULL,
    FOREIGN KEY (`request_id`) REFERENCES `resource_request`(`request_id`) ON DELETE CASCADE,
    FOREIGN KEY (`supply_id`) REFERENCES `supply`(`supply_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `recommendation` (
    `recommendation_id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NULL,
    `disaster_request_id` INT NULL,
    `purok_id` INT NOT NULL,
    `predicted_priority` ENUM('Urgent', 'High', 'Medium', 'Low') NOT NULL,
    `recommended_supply_id` INT NULL,
    `recommended_quantity` INT NOT NULL,
    `confidence_score` DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    `recommendation_reason` TEXT NOT NULL,
    `explanation_details` LONGTEXT NULL,
    `model_version` VARCHAR(50) NOT NULL DEFAULT 'v1.0-decision-tree',
    `recommendation_status` ENUM('Generated', 'Approved', 'Modified', 'Rejected') NOT NULL DEFAULT 'Generated',
    `justification` TEXT NULL,
    `reviewed_by` INT NULL,
    `reviewed_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`request_id`) REFERENCES `resource_request`(`request_id`),
    FOREIGN KEY (`disaster_request_id`) REFERENCES `disaster_request_form`(`disaster_request_id`),
    FOREIGN KEY (`purok_id`) REFERENCES `purok`(`purok_id`),
    FOREIGN KEY (`recommended_supply_id`) REFERENCES `supply`(`supply_id`),
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `resource_request_log` (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NULL,
    `recommendation_id` INT NULL,
    `recommended_by` VARCHAR(100) NULL,
    `action_taken` VARCHAR(100) NOT NULL,
    `action_by` VARCHAR(100) NOT NULL,
    `action_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `remarks` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`request_id`) REFERENCES `resource_request`(`request_id`),
    FOREIGN KEY (`recommendation_id`) REFERENCES `recommendation`(`recommendation_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `preparedness_activities` (
    `preparedness_activities_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `barangay_id` INT NOT NULL,
    `activity_type` ENUM('Disaster Drill', 'Training & Seminar', 'Information Campaign', 'Evacuation Simulation', 'Community Meeting') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `target_purok_id` INT NULL,
    `schedule_date` DATETIME NOT NULL,
    `location` VARCHAR(255) NOT NULL,
    `assigned_personnel` VARCHAR(255) NULL,
    `volunteer_person` VARCHAR(255) NULL,
    `status` ENUM('Scheduled', 'Ongoing', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Scheduled',
    `max_participants` INT DEFAULT 100,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`),
    FOREIGN KEY (`barangay_id`) REFERENCES `barangay`(`barangay_id`),
    FOREIGN KEY (`target_purok_id`) REFERENCES `purok`(`purok_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `field_reports` (
    `report_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `disaster_request_id` INT NULL,
    `purok_id` INT NOT NULL,
    `responder_name` VARCHAR(100) NOT NULL,
    `status_update` TEXT NOT NULL,
    `road_accessibility` ENUM('Passable', 'Passable to Heavy Vehicles Only', 'Not Passable / Blocked', 'Flooded') NOT NULL,
    `casualties_count` INT NOT NULL DEFAULT 0,
    `damaged_houses` INT NOT NULL DEFAULT 0,
    `urgent_needs` TEXT NULL,
    `latitude` DECIMAL(10, 7) NULL,
    `longitude` DECIMAL(10, 7) NULL,
    `report_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('Submitted', 'Verified', 'Rejected') NOT NULL DEFAULT 'Submitted',
    `reviewed_by` INT NULL,
    `reviewed_at` DATETIME NULL,
    `review_notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`),
    FOREIGN KEY (`disaster_request_id`) REFERENCES `disaster_request_form`(`disaster_request_id`),
    FOREIGN KEY (`purok_id`) REFERENCES `purok`(`purok_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `event_evaluations` (
    `evaluation_id` INT AUTO_INCREMENT PRIMARY KEY,
    `preparedness_activities_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `actual_turnout` INT NOT NULL DEFAULT 0,
    `objectives_met_rating` TINYINT NOT NULL CHECK(`objectives_met_rating` BETWEEN 1 AND 5),
    `problems_encountered` TEXT NULL,
    `supplies_used` TEXT NULL,
    `recommendations_for_improvement` TEXT NULL,
    `evaluated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`preparedness_activities_id`) REFERENCES `preparedness_activities`(`preparedness_activities_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `historical_disaster_records` (
    `record_id` INT AUTO_INCREMENT PRIMARY KEY,
    `disaster_date` DATE NOT NULL,
    `disaster_type` VARCHAR(100) NOT NULL,
    `weather_condition` VARCHAR(100) NOT NULL,
    `affected_barangay` VARCHAR(100) NOT NULL DEFAULT 'Hinaplanon',
    `affected_purok` VARCHAR(100) NOT NULL,
    `purok_risk_level` ENUM('High', 'Medium', 'Low', 'Pending Verification') NOT NULL,
    `severity_level` ENUM('Critical', 'High', 'Medium', 'Low') NOT NULL,
    `is_urgent` TINYINT(1) NOT NULL DEFAULT 0,
    `affected_households` INT NOT NULL DEFAULT 1,
    `affected_population` INT NOT NULL DEFAULT 1,
    `requested_supply_type` VARCHAR(100) NOT NULL,
    `requested_quantity` INT NOT NULL,
    `allocated_quantity` INT NOT NULL,
    `priority_label` ENUM('Urgent', 'High', 'Medium', 'Low') NOT NULL,
    `data_source` VARCHAR(150) NOT NULL,
    `is_synthetic` TINYINT(1) NOT NULL DEFAULT 0,
    `is_verified` TINYINT(1) NOT NULL DEFAULT 1,
    `verified_by` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `model_versions` (
    `model_version_id` INT AUTO_INCREMENT PRIMARY KEY,
    `version_name` VARCHAR(100) NOT NULL UNIQUE,
    `algorithm` VARCHAR(100) NOT NULL DEFAULT 'DecisionTreeClassifier (CART/ID3)',
    `hyperparameters_json` LONGTEXT NULL,
    `dataset_source` VARCHAR(255) NOT NULL,
    `training_samples_count` INT NOT NULL,
    `test_samples_count` INT NOT NULL,
    `accuracy` DECIMAL(6, 4) NOT NULL,
    `precision_score` DECIMAL(6, 4) NOT NULL,
    `recall_score` DECIMAL(6, 4) NOT NULL,
    `f1_score` DECIMAL(6, 4) NOT NULL,
    `confusion_matrix_json` LONGTEXT NOT NULL,
    `tree_rules_json` LONGTEXT NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 0,
    `trained_by` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `tam_evaluations` (
    `tam_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `respondent_type` ENUM('BDRRM Head', 'Barangay Official', 'Personnel/Volunteer', 'Resident') NOT NULL,
    `pu_q1` TINYINT NOT NULL CHECK(`pu_q1` BETWEEN 1 AND 5),
    `pu_q2` TINYINT NOT NULL CHECK(`pu_q2` BETWEEN 1 AND 5),
    `pu_q3` TINYINT NOT NULL CHECK(`pu_q3` BETWEEN 1 AND 5),
    `pu_q4` TINYINT NOT NULL CHECK(`pu_q4` BETWEEN 1 AND 5),
    `peou_q1` TINYINT NOT NULL CHECK(`peou_q1` BETWEEN 1 AND 5),
    `peou_q2` TINYINT NOT NULL CHECK(`peou_q2` BETWEEN 1 AND 5),
    `peou_q3` TINYINT NOT NULL CHECK(`peou_q3` BETWEEN 1 AND 5),
    `peou_q4` TINYINT NOT NULL CHECK(`peou_q4` BETWEEN 1 AND 5),
    `iso_functionality` TINYINT NOT NULL CHECK(`iso_functionality` BETWEEN 1 AND 5),
    `iso_reliability` TINYINT NOT NULL CHECK(`iso_reliability` BETWEEN 1 AND 5),
    `iso_usability` TINYINT NOT NULL CHECK(`iso_usability` BETWEEN 1 AND 5),
    `iso_efficiency` TINYINT NOT NULL CHECK(`iso_efficiency` BETWEEN 1 AND 5),
    `iso_maintainability` TINYINT NOT NULL CHECK(`iso_maintainability` BETWEEN 1 AND 5),
    `iso_portability` TINYINT NOT NULL CHECK(`iso_portability` BETWEEN 1 AND 5),
    `qualitative_feedback` TEXT NULL,
    `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB;
