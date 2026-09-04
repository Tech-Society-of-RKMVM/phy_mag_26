<?php
/**
 * About Us & Behind-the-Scenes
 * Department of Physics Wall Magazine
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Fetch dynamic site settings
$heroTitle = get_setting('about_hero_title', 'About Our Wall Magazine');
$heroSubtitle = get_setting('about_hero_subtitle', 'The Department of Physics Wall Magazine at Ramakrishna Mission Vidyamandira is a creative and intellectual platform for undergraduate and postgraduate students.');
$visionText = get_setting('about_vision_text', '<p>Physics is more than mathematical equations and laboratory experiments; it is a profound way of understanding nature and human existence. Through this wall magazine, students articulate scientific insights, question boundaries, and share knowledge across diverse topics ranging from quantum optics and solid-state devices to cosmological horizons and computational intelligence.</p>');

$btsTitle = get_setting('about_bts_title', 'Behind The Scenes: Making of the Magazine');
$btsDesc = get_setting('about_bts_desc', 'Watch our editorial team brainstorming scientific themes, designing interactive layouts, and bringing cutting-edge theoretical physics to life.');
$btsVideoPath = get_setting('about_bts_video_path', '');

$pageTitle = 'About Us - Department of Physics';
$activePage = 'about';
$isArticlePage = false;

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero about-hero">
  <h2><?= e($heroTitle) ?></h2>
  <p><?= e($heroSubtitle) ?></p>
</section>

<main class="about-main-container">
  
  <!-- Vision Section -->
  <section class="about-section vision-section">
    <div class="section-title-wrap">
      <h3>🌌 Our Vision</h3>
      <div class="section-divider"></div>
    </div>
    <div class="about-rich-content">
      <?= $visionText ?>
    </div>
  </section>

  <!-- Behind the Scenes Video Showcase -->
  <?php if (!empty($btsVideoPath) && file_exists(__DIR__ . '/' . $btsVideoPath)): ?>
    <section class="about-section bts-section">
      <div class="section-title-wrap">
        <h3>🎬 <?= e($btsTitle) ?></h3>
        <div class="section-divider"></div>
      </div>

      <?php if (!empty($btsDesc)): ?>
        <p class="bts-description"><?= e($btsDesc) ?></p>
      <?php endif; ?>

      <div class="video-player-wrapper">
        <video controls preload="metadata">
          <source src="<?= e($btsVideoPath) ?>" type="video/<?= strtolower(pathinfo($btsVideoPath, PATHINFO_EXTENSION)) ?>">
          Your browser does not support the video tag.
        </video>
      </div>
    </section>
  <?php endif; ?>



  <div style="margin-top: 3.5rem; text-align: center;">
    <a href="index.php" class="back-link">&larr; Back to This Year's Articles</a>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
