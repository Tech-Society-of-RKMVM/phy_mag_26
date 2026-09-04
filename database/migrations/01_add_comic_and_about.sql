USE phy_mag_db;

DROP TABLE IF EXISTS `comic_panels`;
CREATE TABLE `comic_panels` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `contributors`;
CREATE TABLE `contributors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `role` VARCHAR(100) NOT NULL,
  `batch` VARCHAR(50) NOT NULL,
  `avatar_path` VARCHAR(255) NULL,
  `bio` TEXT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('comic_title', 'Department Of Physics Comic Issue #1'),
('comic_top_text', '<p>Welcome to the official physics department comic edition! Explore this cosmic adventure illustrated and written by students of the department.</p>'),
('comic_bottom_text', '<p>Thank you for reading! Created with passion by the physics editorial & creative arts team.</p>'),
('about_hero_title', 'About Our Wall Magazine'),
('about_hero_subtitle', 'The Department of Physics Wall Magazine at Ramakrishna Mission Vidyamandira is a creative and intellectual platform for undergraduate and postgraduate students.'),
('about_vision_text', '<p>Physics is more than mathematical equations and laboratory experiments; it is a profound way of understanding nature and human existence. Through this wall magazine, students articulate scientific insights, question boundaries, and share knowledge across diverse topics ranging from quantum optics and solid-state devices to cosmological horizons and computational intelligence.</p>'),
('about_bts_title', 'Behind The Scenes: Making of the Magazine'),
('about_bts_desc', 'Watch our team in action brainstorming topics, designing the wall magazine, illustrating the articles, and bringing theoretical physics concepts to life!'),
('about_bts_video_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');

INSERT INTO `contributors` (`id`, `name`, `role`, `batch`, `bio`, `sort_order`) VALUES
(1, 'Dr. B. K. Ghosh', 'Faculty Advisor & Head of Dept.', 'Faculty', 'Guiding the academic vision and experimental physics curriculum of the department.', 1),
(2, 'Aman Mondal', 'Editor-in-Chief', 'UG 3', 'Coordinating student submissions, theme curation, and scientific review.', 2),
(3, 'Manaswi Dutta', 'Webmaster & Technical Lead', 'UG 2', 'Designing and developing the digital physics wall magazine portal and interactive experiences.', 3),
(4, 'Arka Biswas', 'Senior Writer & Illustrator', 'Passed out 2026', 'Contributing frontier research analysis on quantum computing and AGI.', 4),
(5, 'Debarghya Chakladar', 'Creative Illustrator', 'UG 3', 'Crafting original physics diagrams, celestial artwork, and department comics.', 5),
(6, 'Surya Mal', 'Editorial Contributor', 'UG 2', 'Writing on renewable energies, applied physics, and materials science.', 6);

INSERT INTO `comic_panels` (`id`, `title`, `image_path`, `sort_order`) VALUES
(1, 'Episode 1: The Quantum Leap', 'assets/images/bridge.webp', 1),
(2, 'Episode 2: Crystals of Time', 'assets/images/timecrysta.jpg', 2),
(3, 'Episode 3: The Holographic Universe', 'assets/images/llm.webp', 3);
