<?php
/**
 * About Us Page
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About Us - Department of Physics';
$activePage = 'about';
$isArticlePage = false;

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <h2>About Our Wall Magazine</h2>
  <p>The Department of Physics Wall Magazine at Ramakrishna Mission Vidyamandira is a creative and intellectual platform for undergraduate and postgraduate students to explore frontier science, modern technological breakthroughs, and fundamental philosophical questions shaping the universe.</p>
</section>

<main>
  <div style="max-width: 900px; margin: auto; line-height: 1.8; color: #444; font-size: 1.05rem;">
    <h3 style="font-size: 1.5rem; margin-bottom: 1rem; color: #2e2e2e;">Our Vision</h3>
    <p style="margin-bottom: 1.5rem; text-align: justify;">
      Physics is more than mathematical equations and laboratory experiments; it is a profound way of understanding nature and human existence. Through this wall magazine, students articulate scientific insights, question boundaries, and share knowledge across diverse topics ranging from quantum optics and solid-state devices to cosmological horizons and computational intelligence.
    </p>

    <h3 style="font-size: 1.5rem; margin-bottom: 1rem; color: #2e2e2e;">Editorial & Publishing Committee</h3>
    <p style="margin-bottom: 1.5rem; text-align: justify;">
      Edited and managed collectively by the faculty and students of the Department of Physics, Ramakrishna Mission Vidyamandira, Belur Math.
    </p>

    <div style="margin-top: 2.5rem; text-align: center;">
      <a href="index.php" class="back-link">&larr; Back to This Year's Articles</a>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
