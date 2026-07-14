-- =====================================================================================
-- KUMPULAN UPDATE DATABASE MANUAL (MIGRATIONS YANG BELUM DIEKSEKUSI DI PRODUCTION)
-- =====================================================================================

-- 1. Penambahan Foto Profil Driver
ALTER TABLE `drivers` ADD `profile_photo_path` VARCHAR(255) NULL AFTER `foto_ktp`;

-- 2. Penambahan QR Code Field pada Driver & Vehicle
ALTER TABLE `drivers` ADD `qr_code_string` VARCHAR(255) NULL AFTER `profile_photo_path`;
ALTER TABLE `drivers` ADD `qr_code_path` VARCHAR(255) NULL AFTER `qr_code_string`;
ALTER TABLE `vehicles` ADD `qr_code_string` VARCHAR(255) NULL AFTER `current_km`;
ALTER TABLE `vehicles` ADD `qr_code_path` VARCHAR(255) NULL AFTER `qr_code_string`;

-- 3. Penambahan Tahun Pembuatan & Engine Number pada Vehicle
ALTER TABLE `vehicles` ADD `tahun_pembuatan` INT NULL AFTER `type`;
ALTER TABLE `vehicles` ADD `engine_number` VARCHAR(100) NULL AFTER `tahun_pembuatan`;
ALTER TABLE `vehicles` ADD `chassis_number` VARCHAR(100) NULL AFTER `engine_number`;

-- 4. Perubahan panjang tipe data tipe SIM Driver
ALTER TABLE `drivers` MODIFY `sim_type` VARCHAR(10) NULL;

-- 5. Penambahan PDF dan Kolom Finance pada Service Reports
ALTER TABLE `service_reports` ADD `finance_pdf_path` VARCHAR(255) NULL AFTER `customer_signed_pdf_path`;
ALTER TABLE `service_reports` ADD `is_submitted_to_finance` TINYINT(1) NOT NULL DEFAULT '0' AFTER `finance_pdf_path`;
ALTER TABLE `service_reports` ADD `submitted_to_finance_at` TIMESTAMP NULL AFTER `is_submitted_to_finance`;
ALTER TABLE `service_reports` ADD `submitted_to_finance_by` BIGINT UNSIGNED NULL AFTER `submitted_to_finance_at`;
ALTER TABLE `service_reports` ADD CONSTRAINT `service_reports_submitted_to_finance_by_foreign` FOREIGN KEY (`submitted_to_finance_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `service_reports` ADD `finance_status` VARCHAR(30) NOT NULL DEFAULT 'pending' AFTER `submitted_to_finance_by`;
ALTER TABLE `service_reports` ADD `finance_notes` TEXT NULL AFTER `finance_status`;

-- 6. Penambahan Field Baru Service Reports
ALTER TABLE `service_reports` ADD `unit_status_before_service` VARCHAR(30) NULL AFTER `problem_category`;
ALTER TABLE `service_reports` ADD `unit_status_after_service` VARCHAR(30) NULL AFTER `unit_status_before_service`;
ALTER TABLE `service_reports` ADD `sparepart_cost` DECIMAL(12,2) NULL AFTER `cost_estimate`;
ALTER TABLE `service_reports` ADD `service_cost` DECIMAL(12,2) NULL AFTER `sparepart_cost`;

-- 7. Modifikasi Status Service Reports
ALTER TABLE `service_reports` MODIFY `status` VARCHAR(50) NOT NULL DEFAULT 'pending_admin';

-- 8. Penambahan Entry Manual Method pada Attendances (Absensi)
ALTER TABLE `attendances` ADD `vehicle_entry_method` VARCHAR(20) NOT NULL DEFAULT 'qr' AFTER `notes`;
ALTER TABLE `attendances` ADD `manual_vehicle_reason` VARCHAR(255) NULL AFTER `vehicle_entry_method`;
ALTER TABLE `attendances` ADD `manual_vehicle_photo_path` VARCHAR(255) NULL AFTER `manual_vehicle_reason`;

-- 9. Penambahan Verification Fields pada Vehicles
ALTER TABLE `vehicles` ADD `verification_status` VARCHAR(30) NOT NULL DEFAULT 'verified' AFTER `qr_code_path`;
ALTER TABLE `vehicles` ADD `verification_notes` TEXT NULL AFTER `verification_status`;
ALTER TABLE `vehicles` ADD `verified_by` BIGINT UNSIGNED NULL AFTER `verification_notes`;
ALTER TABLE `vehicles` ADD `verified_at` TIMESTAMP NULL AFTER `verified_by`;
ALTER TABLE `vehicles` ADD CONSTRAINT `vehicles_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `vehicles` ADD INDEX `vehicles_verification_status_index`(`verification_status`);

-- 10. Pembuatan Tabel Vehicle Replacements (Mobil Pengganti)
CREATE TABLE `vehicle_replacements` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, 
  `original_vehicle_id` BIGINT UNSIGNED NULL, 
  `replacement_vehicle_id` BIGINT UNSIGNED NOT NULL, 
  `driver_id` BIGINT UNSIGNED NULL, 
  `service_report_id` BIGINT UNSIGNED NULL, 
  `start_at` DATETIME NOT NULL, 
  `end_at` DATETIME NULL, 
  `reason` VARCHAR(255) NULL, 
  `status` VARCHAR(30) NOT NULL DEFAULT 'active', 
  `notes` TEXT NULL, 
  `created_by` BIGINT UNSIGNED NULL, 
  `created_at` TIMESTAMP NULL, 
  `updated_at` TIMESTAMP NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci';

ALTER TABLE `vehicle_replacements` ADD CONSTRAINT `vehicle_replacements_original_vehicle_id_foreign` FOREIGN KEY (`original_vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL;
ALTER TABLE `vehicle_replacements` ADD CONSTRAINT `vehicle_replacements_replacement_vehicle_id_foreign` FOREIGN KEY (`replacement_vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;
ALTER TABLE `vehicle_replacements` ADD CONSTRAINT `vehicle_replacements_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL;
ALTER TABLE `vehicle_replacements` ADD CONSTRAINT `vehicle_replacements_service_report_id_foreign` FOREIGN KEY (`service_report_id`) REFERENCES `service_reports` (`id`) ON DELETE SET NULL;
ALTER TABLE `vehicle_replacements` ADD CONSTRAINT `vehicle_replacements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `vehicle_replacements` ADD INDEX `vehicle_replacements_replacement_vehicle_id_status_index`(`replacement_vehicle_id`, `status`);
ALTER TABLE `vehicle_replacements` ADD INDEX `vehicle_replacements_driver_id_status_index`(`driver_id`, `status`);
ALTER TABLE `vehicle_replacements` ADD INDEX `vehicle_replacements_start_at_index`(`start_at`);

-- 11. Update Service Reports (Split Flow & Source)
ALTER TABLE `service_reports` MODIFY `receipt_photo_path` VARCHAR(255) NULL;
ALTER TABLE `service_reports` ADD `report_source` VARCHAR(40) NOT NULL DEFAULT 'driver_damage' AFTER `ticket_number`;
ALTER TABLE `service_reports` ADD `location_source` VARCHAR(20) NULL AFTER `gps_location`;
ALTER TABLE `service_reports` ADD `service_completed_at` TIMESTAMP NULL AFTER `unit_status_after_service`;
ALTER TABLE `service_reports` ADD `completed_by_driver_id` BIGINT UNSIGNED NULL AFTER `service_completed_at`;
ALTER TABLE `service_reports` ADD CONSTRAINT `service_reports_completed_by_driver_id_foreign` FOREIGN KEY (`completed_by_driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL;

-- 12. Update Laporan Darurat (Follow Up)
ALTER TABLE `emergency_reports` ADD `follow_up_status` VARCHAR(30) NOT NULL DEFAULT 'new' AFTER `proof_photo_path`;
ALTER TABLE `emergency_reports` ADD `follow_up_notes` TEXT NULL AFTER `follow_up_status`;
ALTER TABLE `emergency_reports` ADD `service_report_id` BIGINT UNSIGNED NULL AFTER `follow_up_notes`;
ALTER TABLE `emergency_reports` ADD CONSTRAINT `emergency_reports_service_report_id_foreign` FOREIGN KEY (`service_report_id`) REFERENCES `service_reports` (`id`) ON DELETE SET NULL;
ALTER TABLE `emergency_reports` ADD `processed_by` BIGINT UNSIGNED NULL AFTER `service_report_id`;
ALTER TABLE `emergency_reports` ADD CONSTRAINT `emergency_reports_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `emergency_reports` ADD `processed_at` TIMESTAMP NULL AFTER `processed_by`;

-- =====================================================================================
-- (OPSIONAL JIKA ANDA BELUM MENJALANKAN YANG TADI)
-- 13. Tambahan Document Maintenance Schedules
-- =====================================================================================
-- ALTER TABLE `maintenance_schedules` ADD `receipt_photo_path` VARCHAR(255) NULL AFTER `notes`;
-- ALTER TABLE `maintenance_schedules` ADD `odometer_photo_path` VARCHAR(255) NULL AFTER `receipt_photo_path`;
-- ALTER TABLE `maintenance_schedules` ADD `finance_pdf_path` VARCHAR(255) NULL AFTER `odometer_photo_path`;
-- ALTER TABLE `maintenance_schedules` ADD `admin_signature_path` VARCHAR(255) NULL AFTER `finance_pdf_path`;
-- ALTER TABLE `maintenance_schedules` ADD `admin_signer_name` VARCHAR(255) NULL AFTER `admin_signature_path`;
-- ALTER TABLE `maintenance_schedules` ADD `admin_signer_role` VARCHAR(255) NULL AFTER `admin_signer_name`;
