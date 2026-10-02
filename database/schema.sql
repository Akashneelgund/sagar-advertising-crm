-- ========================================================
-- SAGAR ADVERTISING CRM & QUOTATION MANAGEMENT SYSTEM
-- Database Schema
-- Version: 1.0.0
-- Compatible: MySQL 8.0+ / MariaDB 10.4+
-- ========================================================

CREATE DATABASE IF NOT EXISTS `sagar_advertising_crm` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sagar_advertising_crm`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `email_logs`;
DROP TABLE IF EXISTS `email_unsubscribes`;
DROP TABLE IF EXISTS `email_campaign_recipients`;
DROP TABLE IF EXISTS `email_campaigns`;
DROP TABLE IF EXISTS `email_templates`;
DROP TABLE IF EXISTS `quotation_templates`;
DROP TABLE IF EXISTS `quotation_status_history`;
DROP TABLE IF EXISTS `quotation_items`;
DROP TABLE IF EXISTS `quotations`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `service_categories`;
DROP TABLE IF EXISTS `customer_followups`;
DROP TABLE IF EXISTS `customer_notes`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Users Table
-- --------------------------------------------------------
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `phone` VARCHAR(30) NULL,
    `role` ENUM('admin', 'manager', 'employee') NOT NULL DEFAULT 'employee',
    `department` VARCHAR(100) DEFAULT 'Sales',
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `remember_token` VARCHAR(255) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_role` (`role`),
    INDEX `idx_user_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Permissions Matrix
-- --------------------------------------------------------
CREATE TABLE `permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `module` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role` ENUM('admin', 'manager', 'employee') NOT NULL,
    `permission_slug` VARCHAR(100) NOT NULL,
    UNIQUE KEY `uk_role_perm` (`role`, `permission_slug`),
    FOREIGN KEY (`permission_slug`) REFERENCES `permissions`(`slug`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Customers Table
-- --------------------------------------------------------
CREATE TABLE `customers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_code` VARCHAR(50) NOT NULL UNIQUE,
    `company_name` VARCHAR(200) NOT NULL,
    `contact_person` VARCHAR(150) NOT NULL,
    `mobile` VARCHAR(30) NOT NULL,
    `alternate_mobile` VARCHAR(30) NULL,
    `email` VARCHAR(191) NULL,
    `whatsapp` VARCHAR(30) NULL,
    `address` TEXT NULL,
    `city` VARCHAR(100) DEFAULT 'Hubballi',
    `state` VARCHAR(100) DEFAULT 'Karnataka',
    `pincode` VARCHAR(20) DEFAULT '580024',
    `gstin` VARCHAR(50) NULL,
    `customer_type` ENUM('Individual', 'Business', 'Dealer', 'Corporate', 'Existing Client', 'New Client') DEFAULT 'Business',
    `source` VARCHAR(100) DEFAULT 'Direct Visit',
    `notes` TEXT NULL,
    `assigned_employee_id` INT NULL,
    `status` ENUM('Lead', 'Active', 'Inactive', 'Lost') DEFAULT 'Active',
    `marketing_opt_in` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    FOREIGN KEY (`assigned_employee_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_cust_mobile` (`mobile`),
    INDEX `idx_cust_type` (`customer_type`),
    INDEX `idx_cust_status` (`status`),
    INDEX `idx_cust_city` (`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Customer Notes
-- --------------------------------------------------------
CREATE TABLE `customer_notes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `user_id` INT NULL,
    `note` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Customer Followups
-- --------------------------------------------------------
CREATE TABLE `customer_followups` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `user_id` INT NULL,
    `followup_date` DATE NOT NULL,
    `followup_time` TIME NULL,
    `followup_type` ENUM('Call', 'Email', 'Meeting', 'WhatsApp', 'Other') DEFAULT 'Call',
    `notes` TEXT NULL,
    `status` ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_followup_date` (`followup_date`),
    INDEX `idx_followup_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Service Categories & Master
-- --------------------------------------------------------
CREATE TABLE `service_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NULL,
    `name` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `default_unit` ENUM('Sq Ft', 'Sq Inch', 'Piece', 'Running Ft', 'Day', 'Hour', 'Unit', 'Custom') DEFAULT 'Sq Ft',
    `default_price` DECIMAL(12,2) DEFAULT 0.00,
    `default_gst_rate` DECIMAL(5,2) DEFAULT 18.00,
    `default_commission_rate` DECIMAL(5,2) DEFAULT 15.00,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `service_categories`(`id`) ON DELETE SET NULL,
    INDEX `idx_service_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Quotations Master
-- --------------------------------------------------------
CREATE TABLE `quotations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `quotation_number` VARCHAR(100) NOT NULL UNIQUE,
    `quotation_date` DATE NOT NULL,
    `valid_until` DATE NOT NULL,
    `customer_id` INT NOT NULL,
    `sales_person_id` INT NULL,
    `project_name` VARCHAR(255) NULL,
    `reference` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `terms_and_conditions` TEXT NULL,
    `payment_terms` VARCHAR(255) DEFAULT '50% Advance, 50% upon delivery/installation',
    `delivery_time` VARCHAR(100) DEFAULT '3 to 7 working days',
    `commission_mode` ENUM('markup', 'margin') DEFAULT 'markup',
    `subtotal` DECIMAL(14,2) DEFAULT 0.00,
    `total_commission` DECIMAL(14,2) DEFAULT 0.00,
    `discount_amount` DECIMAL(14,2) DEFAULT 0.00,
    `taxable_amount` DECIMAL(14,2) DEFAULT 0.00,
    `gst_rate` DECIMAL(5,2) DEFAULT 18.00,
    `gst_amount` DECIMAL(14,2) DEFAULT 0.00,
    `transportation_charges` DECIMAL(14,2) DEFAULT 0.00,
    `installation_charges` DECIMAL(14,2) DEFAULT 0.00,
    `round_off` DECIMAL(8,2) DEFAULT 0.00,
    `grand_total` DECIMAL(14,2) DEFAULT 0.00,
    `status` ENUM('Draft', 'Sent', 'Viewed', 'Under Discussion', 'Approved', 'Rejected', 'Expired', 'Converted') DEFAULT 'Draft',
    `view_token` VARCHAR(64) UNIQUE,
    `viewed_at` DATETIME NULL,
    `sent_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`sales_person_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_quote_status` (`status`),
    INDEX `idx_quote_date` (`quotation_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Quotation Line Items
-- --------------------------------------------------------
CREATE TABLE `quotation_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `quotation_id` INT NOT NULL,
    `service_id` INT NULL,
    `item_name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `size_dimension` VARCHAR(100) NULL,
    `quantity` DECIMAL(10,2) DEFAULT 1.00,
    `unit` VARCHAR(50) DEFAULT 'Sq Ft',
    `actual_price` DECIMAL(12,2) DEFAULT 0.00,
    `commission_rate` DECIMAL(5,2) DEFAULT 15.00,
    `commission_amount` DECIMAL(12,2) DEFAULT 0.00,
    `selling_price` DECIMAL(12,2) DEFAULT 0.00,
    `total_amount` DECIMAL(14,2) DEFAULT 0.00,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`quotation_id`) REFERENCES `quotations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Quotation Status History & Activity Timeline
-- --------------------------------------------------------
CREATE TABLE `quotation_status_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `quotation_id` INT NOT NULL,
    `user_id` INT NULL,
    `old_status` VARCHAR(50) NULL,
    `new_status` VARCHAR(50) NOT NULL,
    `comments` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`quotation_id`) REFERENCES `quotations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Quotation Templates
-- --------------------------------------------------------
CREATE TABLE `quotation_templates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `default_notes` TEXT NULL,
    `default_terms` TEXT NULL,
    `items_json` LONGTEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Email Templates
-- --------------------------------------------------------
CREATE TABLE `email_templates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `category` ENUM('festival', 'promotional', 'quotation', 'custom') DEFAULT 'custom',
    `subject` VARCHAR(255) NOT NULL,
    `body_html` LONGTEXT NOT NULL,
    `variables_hint` VARCHAR(255) DEFAULT '{{customer_name}}, {{company_name}}, {{email}}, {{phone}}, {{city}}',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Email Campaigns Master
-- --------------------------------------------------------
CREATE TABLE `email_campaigns` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `template_id` INT NULL,
    `from_name` VARCHAR(150) DEFAULT 'Sagar Advertising',
    `from_email` VARCHAR(191) DEFAULT 'sagaradvertising7@gmail.com',
    `body_html` LONGTEXT NOT NULL,
    `recipient_filter` VARCHAR(255) DEFAULT 'all',
    `total_recipients` INT DEFAULT 0,
    `pending_count` INT DEFAULT 0,
    `sent_count` INT DEFAULT 0,
    `failed_count` INT DEFAULT 0,
    `status` ENUM('draft', 'scheduled', 'queued', 'processing', 'paused', 'completed', 'cancelled') DEFAULT 'draft',
    `scheduled_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`template_id`) REFERENCES `email_templates`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Campaign Recipients Queue
-- --------------------------------------------------------
CREATE TABLE `email_campaign_recipients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `campaign_id` INT NOT NULL,
    `customer_id` INT NULL,
    `recipient_email` VARCHAR(191) NOT NULL,
    `recipient_name` VARCHAR(150) NOT NULL,
    `status` ENUM('pending', 'sent', 'failed', 'unsubscribed') DEFAULT 'pending',
    `sent_at` DATETIME NULL,
    `error_message` TEXT NULL,
    FOREIGN KEY (`campaign_id`) REFERENCES `email_campaigns`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
    INDEX `idx_recip_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Email Unsubscribes
-- --------------------------------------------------------
CREATE TABLE `email_unsubscribes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `reason` VARCHAR(255) DEFAULT 'User requested unsubscribe',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Email Activity Logs
-- --------------------------------------------------------
CREATE TABLE `email_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `campaign_id` INT NULL,
    `quotation_id` INT NULL,
    `recipient_email` VARCHAR(191) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `status` ENUM('queued', 'sent', 'failed') DEFAULT 'sent',
    `provider_message` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email_log_recipient` (`recipient_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Audit Activity Logs
-- --------------------------------------------------------
CREATE TABLE `activity_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `module` VARCHAR(100) NOT NULL,
    `record_id` INT NULL,
    `details` TEXT NULL,
    `ip_address` VARCHAR(50) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_act_module` (`module`),
    INDEX `idx_act_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- System Settings
-- --------------------------------------------------------
CREATE TABLE `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` LONGTEXT NULL,
    `category` VARCHAR(50) DEFAULT 'general',
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
