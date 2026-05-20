<?php
// Variables expected: $page_title (optional), $meta_desc (optional)
$site_name = (string) site_setting('site_name', SITE_NAME);
$site_tagline = (string) site_setting('site_tagline', 'Technology support and digital solutions');
$default_meta = (string) site_setting('meta_description', 'Your complete tech hub in Mbarara, Uganda.');
$title = isset($page_title) ? e($page_title) . ' | ' . e($site_name) : e($site_name);
$desc  = e($meta_desc ?? $default_meta);
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$app_css_path = ROOT . '/public/assets/css/app.css';
$app_css_version = is_file($app_css_path) ? filemtime($app_css_path) : time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>
  <meta name="description" content="<?= $desc ?>">
  <link rel="stylesheet" href="<?= SITE_URL ?>/public/assets/css/app.css?v=<?= e((string) $app_css_version) ?>">
</head>
<body>
<header class="site-header">
  <div class="wrapper site-bar">
    <div class="site-bar-top">
      <a href="<?= SITE_URL ?>/public/" class="brand">
        <span class="brand-mark">JT</span>
        <span class="brand-text">
          <strong><?= e($site_name) ?></strong>
          <small><?= e($site_tagline) ?></small>
        </span>
      </a>

      <div class="nav-actions">
        <?php if (logged_in()): ?>
          <a href="<?= SITE_URL ?>/public/account.php">My Account</a>
          <a href="<?= SITE_URL ?>/public/logout.php">Logout</a>
        <?php else: ?>
          <a href="<?= SITE_URL ?>/public/login.php">Login</a>
        <?php endif; ?>
        <a class="cart-link" href="<?= SITE_URL ?>/public/cart.php">Cart<span><?= $cart_count ?></span></a>
      </div>
    </div>

    <nav class="main-nav" aria-label="Primary navigation">
      <a href="<?= SITE_URL ?>/public/">Home</a>
      <a href="<?= SITE_URL ?>/public/shop.php">Shop</a>
      <a href="<?= SITE_URL ?>/public/services.php">Services</a>
      <a class="nav-pill" href="<?= SITE_URL ?>/public/service-request.php">Request Service</a>
      <a class="nav-pill" href="<?= SITE_URL ?>/public/repair-request.php">Book Repair</a>
      <a href="<?= SITE_URL ?>/public/request-status.php">Track Request</a>
      <a href="<?= SITE_URL ?>/public/blog.php">Blog</a>
      <a href="<?= SITE_URL ?>/public/portfolio.php">Portfolio</a>
      <a href="<?= SITE_URL ?>/public/about.php">About</a>
      <a href="<?= SITE_URL ?>/public/contact.php">Contact</a>
    </nav>
  </div>
</header>

<?php foreach (['success', 'error', 'info', 'warning'] as $type): ?>
  <?php if (has_flash($type)): ?>
    <div class="flash flash-<?= $type ?> wrapper"><?= e(get_flash($type)) ?></div>
  <?php endif; ?>
<?php endforeach; ?>
