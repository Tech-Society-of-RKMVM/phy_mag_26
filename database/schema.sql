-- Physics Department Wall Magazine Database Schema

CREATE DATABASE IF NOT EXISTS `phy_mag_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `phy_mag_db`;

-- Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Articles Table
CREATE TABLE IF NOT EXISTS `articles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `summary` VARCHAR(500) NOT NULL,
    `author_name` VARCHAR(150) NOT NULL,
    `author_batch` VARCHAR(50) NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `content` MEDIUMTEXT NOT NULL,
    `edition_year` INT NOT NULL DEFAULT 2026,
    `published_date` DATE NOT NULL,
    `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
    `sort_order` INT NOT NULL DEFAULT 0,
    `views_count` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_edition_status` (`edition_year`, `status`),
    INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site Settings Table (for Editorial, titles, etc.)
CREATE TABLE IF NOT EXISTS `settings` (
    `setting_key` VARCHAR(100) PRIMARY KEY,
    `setting_value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
