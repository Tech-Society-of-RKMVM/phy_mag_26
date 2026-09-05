<?php
/**
 * About Us & Behind-the-Scenes Photo Gallery
 * Department of Physics Wall Magazine
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();

// Fetch dynamic site settings
$heroTitle    = get_setting('about_hero_title', 'About Our Wall Magazine');
$heroSubtitle = get_setting('about_hero_subtitle', 'The Department of Physics Wall Magazine at Ramakrishna Mission Vidyamandira is a creative and intellectual platform for undergraduate and postgraduate students.');
$visionText   = get_setting('about_vision_text', '<p>Physics is more than mathematical equations and laboratory experiments; it is a profound way of understanding nature and human existence. Through this wall magazine, students articulate scientific insights, question boundaries, and share knowledge across diverse topics ranging from quantum optics and solid-state devices to cosmological horizons and computational intelligence.</p>');

$btsTitle = get_setting('about_bts_title', 'Behind The Scenes: Making of the Magazine');
$btsDesc  = get_setting('about_bts_desc',  'A glimpse into the editorial team brainstorming, designing, and bringing the magazine to life.');

// Fetch BTS photos (safety: table may not exist on older installs)
$btsPhotos = [];
try {
    $btsPhotos = $pdo->query("SELECT * FROM bts_photos ORDER BY sort_order ASC, id ASC")->fetchAll();
} catch (Exception $e) {
    // Table not yet created — silently skip
}

$pageTitle  = 'About Us - Department of Physics';
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

  <!-- Behind the Scenes Photo Gallery -->
  <?php if (!empty($btsPhotos)): ?>
    <section class="about-section bts-section">
      <div class="section-title-wrap">
        <h3>📸 <?= e($btsTitle) ?></h3>
        <div class="section-divider"></div>
        <?php if (!empty($btsDesc)): ?>
          <p class="section-subtitle"><?= e($btsDesc) ?></p>
        <?php endif; ?>
      </div>

      <div class="bts-photo-grid">
        <?php foreach ($btsPhotos as $photo): ?>
          <div class="bts-photo-item" onclick="openBtsLightbox(this)">
            <img src="<?= e($photo['image_path']) ?>"
                 alt="<?= e($photo['caption'] ?: 'Behind the scenes') ?>"
                 loading="lazy">
            <?php if (!empty($photo['caption'])): ?>
              <div class="bts-photo-caption"><?= e($photo['caption']) ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <div style="margin-top: 3.5rem; text-align: center;">
    <a href="index.php" class="back-link">&larr; Back to This Year's Articles</a>
  </div>
</main>

<!-- ── Lightbox ── -->
<div id="bts-lightbox" onclick="closeBtsLightbox()"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.88);
            z-index:9999; align-items:center; justify-content:center; cursor:zoom-out;">
  <img id="bts-lightbox-img" src="" alt=""
       style="max-width:92vw; max-height:90vh; border-radius:8px;
              box-shadow:0 20px 60px rgba(0,0,0,0.7); object-fit:contain;">
  <p id="bts-lightbox-cap"
     style="position:absolute; bottom:1.5rem; left:50%; transform:translateX(-50%);
            color:#fff; font-size:0.95rem; background:rgba(0,0,0,0.5);
            padding:0.4rem 1rem; border-radius:20px; white-space:nowrap;
            max-width:80vw; overflow:hidden; text-overflow:ellipsis;"></p>
  <button onclick="shiftBtsLightbox(-1)"
          style="position:absolute; left:1rem; top:50%; transform:translateY(-50%);
                 background:rgba(255,255,255,0.15); border:none; color:#fff;
                 font-size:1.8rem; width:44px; height:44px; border-radius:50%;
                 cursor:pointer; backdrop-filter:blur(4px);">&#8249;</button>
  <button onclick="shiftBtsLightbox(1)"
          style="position:absolute; right:1rem; top:50%; transform:translateY(-50%);
                 background:rgba(255,255,255,0.15); border:none; color:#fff;
                 font-size:1.8rem; width:44px; height:44px; border-radius:50%;
                 cursor:pointer; backdrop-filter:blur(4px);">&#8250;</button>
</div>

<script>
(function () {
    const items = Array.from(document.querySelectorAll('.bts-photo-item'));
    let current = 0;

    window.openBtsLightbox = function (el) {
        current = items.indexOf(el);
        showLightbox(current);
    };

    window.shiftBtsLightbox = function (dir) {
        event.stopPropagation();
        current = (current + dir + items.length) % items.length;
        showLightbox(current);
    };

    window.closeBtsLightbox = function () {
        document.getElementById('bts-lightbox').style.display = 'none';
    };

    function showLightbox(idx) {
        const img = items[idx].querySelector('img');
        const capEl = items[idx].querySelector('.bts-photo-caption');
        document.getElementById('bts-lightbox-img').src = img.src;
        document.getElementById('bts-lightbox-cap').textContent = capEl ? capEl.textContent : '';
        const lb = document.getElementById('bts-lightbox');
        lb.style.display = 'flex';
    }

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeBtsLightbox();
        if (e.key === 'ArrowRight') shiftBtsLightbox(1);
        if (e.key === 'ArrowLeft')  shiftBtsLightbox(-1);
    });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
