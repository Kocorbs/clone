-- Initial setup: one admin and existing project hazard classifications.
-- No resident records, sample inventory, or demo passwords are imported.
SET NAMES utf8mb4;
START TRANSACTION;
INSERT INTO barangay (barangay_name, location, latitude, longitude, population_size)
SELECT 'Hinaplanon', 'Iligan City, Lanao del Norte, Northern Mindanao', 8.2412, 124.2543, 15636
WHERE NOT EXISTS (SELECT 1 FROM barangay WHERE barangay_name = 'Hinaplanon');
SET @barangay_id = (SELECT barangay_id FROM barangay WHERE barangay_name = 'Hinaplanon' LIMIT 1);
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Bayug Island', 'High', 'Pink' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Bayug Island');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 1', 'High', 'Pink' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 1');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 2', 'Medium', 'Orange' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 2');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 2A', 'High', 'Pink' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 2A');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 2B', 'High', 'Pink' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 2B');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 2C', 'Medium', 'Orange' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 2C');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 2D', 'Medium', 'Orange' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 2D');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 3', 'Medium', 'Orange' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 3');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 3A', 'High', 'Pink' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 3A');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 4A', 'Medium', 'Orange' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 4A');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 5 (Cabugason)', 'Medium', 'Orange' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 5 (Cabugason)');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 5A', 'High', 'Pink' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 5A');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 6', 'Low', 'Yellow' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 6');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 6A', 'Medium', 'Orange' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 6A');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 6B', 'Low', 'Yellow' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 6B');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 6C', 'Low', 'Yellow' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 6C');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 7', 'Low', 'Yellow' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 7');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 8', 'Pending Verification', 'Gray' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 8');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Purok 9', 'High', 'Pink' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Purok 9');
INSERT INTO purok (barangay_id, purok_name, risk_classification, risk_zone_color)
SELECT @barangay_id, 'Phase 3 & 5 (Bayan)', 'Medium', 'Orange' WHERE NOT EXISTS (SELECT 1 FROM purok WHERE barangay_id = @barangay_id AND purok_name = 'Phase 3 & 5 (Bayan)');
INSERT INTO users (user_type, id_number, lastname, firstname, email, password_hash, is_approved)
SELECT 'ICDRRMO', 'ADMIN-001', 'Administrator', 'System', 'icdrrmo@iligan.gov.ph', '$2y$10$1pxBPKNEvxf40gZP3lRGMudifbNaRQHlDAnLMsMi7maY2EHfcWS7m', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'icdrrmo@iligan.gov.ph');
COMMIT;
