-- Update request table to support two-step approval process
ALTER TABLE `request` 
MODIFY COLUMN `status` ENUM('pending_gso', 'pending_vpo', 'approved', 'declined_gso', 'declined_vpo') DEFAULT 'pending_gso';

-- Create audit trail table for logging all actions
CREATE TABLE IF NOT EXISTS `audit_trail` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `request_id` INT NOT NULL,
  `reference_number` VARCHAR(50),
  `action` ENUM('created', 'gso_approved', 'gso_declined', 'vpo_approved', 'vpo_declined') NOT NULL,
  `performed_by` VARCHAR(100) NOT NULL,
  `performed_by_role` ENUM('user', 'gso', 'vpo') NOT NULL,
  `reason` TEXT,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_request_id` (`request_id`),
  INDEX `idx_reference_number` (`reference_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Update approved_request table to track approval steps
ALTER TABLE `approved_request` 
ADD COLUMN `gso_approved_by` VARCHAR(255) DEFAULT NULL,
ADD COLUMN `gso_approved_at` TIMESTAMP NULL,
ADD COLUMN `vpo_approved_by` VARCHAR(255) DEFAULT NULL,
ADD COLUMN `vpo_approved_at` TIMESTAMP NULL;

-- Update declined_request table to track which step declined
ALTER TABLE `declined_request` 
ADD COLUMN `declined_by_role` ENUM('gso', 'vpo') NOT NULL DEFAULT 'gso';

-- Create a view for easy status checking
CREATE OR REPLACE VIEW `request_status_view` AS
SELECT 
    r.id,
    r.reference_number,
    r.request_by,
    r.department_organization,
    r.activity,
    r.date_need_from,
    r.venue,
    r.status,
    CASE 
        WHEN r.status = 'pending_gso' THEN 'Pending (GSO)'
        WHEN r.status = 'pending_vpo' THEN 'Pending (VPO)'
        WHEN r.status = 'approved' THEN 'Approved'
        WHEN r.status = 'declined_gso' THEN 'Declined by GSO'
        WHEN r.status = 'declined_vpo' THEN 'Declined by VPO'
        ELSE r.status
    END as status_display,
    (SELECT COUNT(*) FROM audit_trail WHERE request_id = r.id) as audit_count
FROM request r;