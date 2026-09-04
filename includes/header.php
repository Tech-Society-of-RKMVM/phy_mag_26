<?php
/**
 * Shared Header Component
 */
if (!isset($pageTitle)) {
  $pageTitle = 'Department Of Physics - Wall Magazine';
}
if (!isset($activePage)) {
  $activePage = 'this_year';
}
$isArticleHeader = !empty($isArticlePage);
$isIndexPage = ($activePage === 'this_year' && empty($isArticlePage));

// Determine background image for index page
$indexBgImage = 'assets/images/backgrounds/WhatsApp_Image_2026-09-gi .jpeg';
if ($isIndexPage && function_exists('get_active_backgrounds')) {
  $activeBgs = get_active_backgrounds();
  if (!empty($activeBgs[0]['image_path'])) {
    $indexBgImage = $activeBgs[0]['image_path'];
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($pageTitle) ?></title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <?php if ($isIndexPage): ?>
    <style>
      body.index-page main {
        width: 100% !important;
        max-width: 100% !important;
        background-image:
          linear-gradient(rgba(24, 18, 12, 0.30), rgba(24, 18, 12, 0.45)),
          url('<?= e($indexBgImage) ?>') !important;
        background-size: cover !important;
        background-position: top center !important;
        background-attachment: scroll !important;
        background-repeat: no-repeat !important;
        background-color: #1a1a1a !important;
        min-height: 100vh !important;
      }
    </style>
  <?php endif; ?>
</head>

<body class="<?= $isIndexPage ? 'index-page' : '' ?>">


  <?php if ($isArticleHeader): ?>
    <header class="article-header">
      <a href="index.php"><img src="assets/images/rkm.jpg" alt="RKMV Logo" class="logo"></a>
      <h1>RKMV Physics Department Annual Magazine</h1>
      <nav>
        <ul>
          <li><a class="<?= $activePage === 'this_year' ? 'active' : '' ?>" href="index.php">This Year</a></li>
          <li><a class="<?= $activePage === 'comic' ? 'active' : '' ?>" href="comic.php">Comic</a></li>
          <li><a class="<?= $activePage === 'about' ? 'active' : '' ?>" href="about.php">About Us</a></li>
        </ul>
      </nav>
    </header>
  <?php else: ?>
    <header>
      <a href="index.php"><img src="assets/images/rkm-removebg-preview.png" alt="RKMV Logo" class="logo"></a>
      <h3>Ramakrishna Mission Vidyamandira</h3>
      <h1>Department Of Physics</h1>
      <h1>Wall Magazine</h1>
      <nav>
        <ul>
          <li><a class="<?= $activePage === 'this_year' ? 'active' : '' ?>" href="index.php">This Year</a></li>
          <li><a class="<?= $activePage === 'comic' ? 'active' : '' ?>" href="comic.php">Comic</a></li>
          <li><a class="<?= $activePage === 'about' ? 'active' : '' ?>" href="about.php">About Us</a></li>
        </ul>
      </nav>
    </header>
  <?php endif; ?>