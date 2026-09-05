-- Migration 03: Replace BTS video with BTS photo gallery
-- Run this against your phy_mag database

-- Create bts_photos table for storing behind-the-scenes images
CREATE TABLE IF NOT EXISTS `bts_photos` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `image_path`  VARCHAR(500)    NOT NULL,
  `caption`     VARCHAR(300)    NOT NULL DEFAULT '',
  `sort_order`  SMALLINT        NOT NULL DEFAULT 0,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (Optional) Remove old BTS video setting row if it exists
-- DELETE FROM site_settings WHERE setting_key = 'about_bts_video_path';
