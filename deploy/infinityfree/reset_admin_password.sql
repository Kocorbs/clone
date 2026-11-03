-- Restore only this admin's password to the existing private login file.
UPDATE `if0_43310179_hinaplanon_drrm`.`users`
SET `password_hash` = '$2y$10$DDyB.cM3RrMcQi2B8ZxKKupOen20RTuvKcmHQ2YGqa.DfyamDr/ce'
WHERE `user_id` = 1 AND `email` = 'icdrrmo@iligan.gov.ph' AND `user_type` = 'ICDRRMO' AND `deleted_at` IS NULL;
