<?php
/**
 * About Us, Behind-the-Scenes & Contributors Showcase
 * Department of Physics Wall Magazine
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();

// Fetch dynamic site settings
$heroTitle = get_setting('about_hero_title', 'About Our Wall Magazine');
$heroSubtitle = get_setting('about_hero_subtitle', 'The Department of Physics Wall Magazine at Ramakrishna Mission Vidyamandira is a creative and intellectual platform for undergraduate and postgraduate students.');
$visionText = get_setting('about_vision_text', '<p>Physics is more than mathematical equations and laboratory experiments; it is a profound way of understanding nature and human existence. Through this wall magazine, students articulate scientific insights, question boundaries, and share knowledge across diverse topics ranging from quantum optics and solid-state devices to cosmological horizons and computational intelligence.</p>');

$btsTitle = get_setting('about_bts_title', 'Behind The Scenes: Making of the Magazine');
$btsDesc = get_setting('about_bts_desc', 'Watch our editorial team brainstorming scientific themes, designing interactive layouts, and bringing cutting-edge theoretical physics to life.');
$btsVideoUrl = get_setting('about_bts_video_url', '');
$embedVideoUrl = get_embed_video_url($btsVideoUrl);

// Fetch contributors
$stmt = $pdo->query("SELECT id, name, role, batch, avatar_path, bio FROM contributors ORDER BY sort_order ASC, id ASC");
$contributors = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'About Us & Contributors - Department of Physics';
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
  <?php if (!empty($btsVideoUrl)): ?>
    <section class="about-section bts-section">
      <div class="section-title-wrap">
        <h3>🎬 <?= e($btsTitle) ?></h3>
        <div class="section-divider"></div>
      </div>

      <?php if (!empty($btsDesc)): ?>
        <p class="bts-description"><?= e($btsDesc) ?></p>
      <?php endif; ?>

      <div class="video-player-wrapper">
        <?php if (strpos($embedVideoUrl, 'youtube.com/embed') !== false || strpos($embedVideoUrl, 'player.vimeo.com') !== false): ?>
          <iframe 
            src="<?= e($embedVideoUrl) ?>" 
            title="<?= e($btsTitle) ?>" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
            allowfullscreen>
          </iframe>
        <?php elseif (preg_match('/\.(mp4|webm|ogg)$/i', $btsVideoUrl)): ?>
          <video controls preload="metadata" poster="assets/images/dept.jpg">
            <source src="<?= e($btsVideoUrl) ?>" type="video/mp4">
            Your browser does not support the video tag.
          </video>
        <?php else: ?>
          <div class="video-link-fallback">
            <a href="<?= e($btsVideoUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-video-watch">
              ▶ Watch Behind The Scenes Video
            </a>
          </div>
        <?php endif; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- Contributors & Editorial Board -->
  <section class="about-section contributors-section">
    <div class="section-title-wrap">
      <h3>👥 Editorial Board & Contributors</h3>
      <div class="section-divider"></div>
      <p class="section-subtitle">The passionate faculty advisors, student writers, illustrators, and developers behind this edition.</p>
    </div>

    <?php if (empty($contributors)): ?>
      <p style="text-align: center; color: #64748b; padding: 2rem;">No contributors listed yet.</p>
    <?php else: ?>
      <div class="contributors-grid">
        <?php foreach ($contributors as $c): ?>
          <div class="contributor-card">
            <div class="card-avatar-wrap">
              <?php if (!empty($c['avatar_path']) && file_exists(__DIR__ . '/' . $c['avatar_path'])): ?>
                <img src="<?= e($c['avatar_path']) ?>" alt="<?= e($c['name']) ?>" class="contributor-avatar">
              <?php else: ?>
                <div class="avatar-fallback">
                  <?= strtoupper(mb_substr($c['name'], 0, 1, 'UTF-8')) ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="contributor-info">
              <h4 class="contributor-name"><?= e($c['name']) ?></h4>
              <div class="contributor-tags">
                <span class="role-badge"><?= e($c['role']) ?></span>
                <?php if (!empty($c['batch'])): ?>
                  <span class="batch-badge"><?= e($c['batch']) ?></span>
                <?php endif; ?>
              </div>
              <?php if (!empty($c['bio'])): ?>
                <p class="contributor-bio"><?= e($c['bio']) ?></p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <div style="margin-top: 3.5rem; text-align: center;">
    <a href="index.php" class="back-link">&larr; Back to This Year's Articles</a>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
