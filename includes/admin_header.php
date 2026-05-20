<?php
// All admin pages call require_staff() before including this.
$staff = current_staff();
$nav_page = basename($_SERVER['PHP_SELF'], '.php');
$admin_css_path = ROOT . '/public/assets/css/admin.css';
$admin_css_version = is_file($admin_css_path) ? filemtime($admin_css_path) : time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php $admin_site_name = (string) site_setting('site_name', SITE_NAME); ?>
  <title><?= isset($page_title) ? e($page_title) . ' - Admin' : 'Admin - ' . e($admin_site_name) ?></title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/public/assets/css/admin.css?v=<?= e((string) $admin_css_version) ?>">
</head>
<body class="admin">

<div class="admin-wrap">
  <aside class="sidebar">
      <div class="sidebar-brand"><?= e($admin_site_name) ?></div>
    <nav class="sidebar-nav">
      <a href="index.php" class="<?= $nav_page === 'index' ? 'active' : '' ?>">Dashboard</a>
      <?php if (has_permission('manage_products')): ?>
        <a href="products.php" class="<?= $nav_page === 'products' || $nav_page === 'product-add' || $nav_page === 'product-edit' ? 'active' : '' ?>">Products</a>
      <?php endif; ?>
      <?php if (has_permission('manage_orders')): ?>
        <a href="orders.php" class="<?= $nav_page === 'orders' || $nav_page === 'order-view' ? 'active' : '' ?>">Orders</a>
      <?php endif; ?>
      <?php if (has_permission('manage_repairs')): ?>
        <a href="repairs.php" class="<?= $nav_page === 'repairs' ? 'active' : '' ?>">Repairs</a>
      <?php endif; ?>
      <?php if (has_permission('manage_requests')): ?>
        <a href="requests.php" class="<?= $nav_page === 'requests' ? 'active' : '' ?>">Requests</a>
      <?php endif; ?>
      <?php if (has_permission('manage_blog')): ?>
        <a href="blog.php" class="<?= in_array($nav_page, ['blog', 'blog-add', 'blog-edit', 'blog-comments'], true) ? 'active' : '' ?>">Blog</a>
      <?php endif; ?>
      <?php if (has_permission('manage_portfolio')): ?>
        <a href="portfolio.php" class="<?= in_array($nav_page, ['portfolio', 'portfolio-add', 'portfolio-edit'], true) ? 'active' : '' ?>">Portfolio</a>
      <?php endif; ?>
      <?php if (has_permission('manage_services')): ?>
        <a href="services.php" class="<?= in_array($nav_page, ['services', 'service-add', 'service-edit'], true) ? 'active' : '' ?>">Services</a>
      <?php endif; ?>
      <?php if (has_permission('manage_messages')): ?>
        <a href="messages.php" class="<?= $nav_page === 'messages' ? 'active' : '' ?>">Messages</a>
      <?php endif; ?>
      <?php if (has_permission('manage_staff')): ?>
        <a href="staff.php" class="<?= in_array($nav_page, ['staff', 'staff-add', 'staff-edit'], true) ? 'active' : '' ?>">Staff</a>
      <?php endif; ?>
      <?php if (has_permission('view_reports')): ?>
        <a href="reports.php" class="<?= $nav_page === 'reports' ? 'active' : '' ?>">Reports</a>
      <?php endif; ?>
      <?php if (has_permission('manage_settings')): ?>
        <a href="settings.php" class="<?= $nav_page === 'settings' ? 'active' : '' ?>">Settings</a>
      <?php endif; ?>
    </nav>
    <a href="logout.php" class="sidebar-logout">Logout</a>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <h1 class="topbar-title"><?= e($page_title ?? 'Dashboard') ?></h1>
      <div class="topbar-user">
        <?= e($staff['full_name'] ?? '') ?> -
        <span class="role-badge"><?= e($_SESSION['staff_role'] ?? '') ?></span>
      </div>
    </header>

    <?php foreach (['success', 'error', 'info', 'warning'] as $type): ?>
      <?php if (has_flash($type)): ?>
        <div class="flash flash-<?= e($type) ?>"><?= e(get_flash($type)) ?></div>
      <?php endif; ?>
    <?php endforeach; ?>

    <div class="admin-content">
