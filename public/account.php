<?php
require_once __DIR__ . '/../includes/init.php';

require_login();

$user = current_user();
$page_title = 'My Account';
$meta_desc  = 'Customer account dashboard';

$stats = [
    'orders' => 0,
    'messages' => 0,
];

try {
    $stats['orders'] = (int) (db_one('SELECT COUNT(*) AS total FROM orders WHERE user_id = ?', [$_SESSION['user_id']])['total'] ?? 0);
    $stats['messages'] = (int) (db_one('SELECT COUNT(*) AS total FROM contact_messages WHERE user_id = ?', [$_SESSION['user_id']])['total'] ?? 0);
} catch (Throwable $e) {
    // Keep the dashboard readable even if some tables are still empty or unavailable.
}

include INCLUDES . '/header.php';
?>

<section class="section">
  <div class="wrapper account-grid">
    <aside class="hero-panel landing-panel">
      <p class="panel-kicker">Account</p>
      <h2><?= e($user['full_name'] ?? 'Customer') ?></h2>
      <p><?= e($user['email'] ?? '') ?></p>
      <?php if (!empty($user['phone'])): ?><p><?= e($user['phone']) ?></p><?php endif; ?>
      <div class="side-list">
        <a class="side-item" href="<?= SITE_URL ?>/public/account.php"><strong>Overview</strong><span>Dashboard</span></a>
        <a class="side-item" href="<?= SITE_URL ?>/public/account-orders.php"><strong>Orders</strong><span>History</span></a>
        <a class="side-item" href="<?= SITE_URL ?>/public/contact.php"><strong>Contact</strong><span>Send message</span></a>
      </div>
    </aside>

    <div class="account-content">
      <div class="section-heading">
        <p class="eyebrow">Dashboard</p>
        <h1>Welcome back</h1>
        <p>Your account area is starting simple and useful: core profile details, order visibility, and room for future features.</p>
      </div>

      <div class="stat-strip">
        <div class="stat-card">
          <strong><?= e((string) $stats['orders']) ?></strong>
          <span>Total orders</span>
        </div>
        <div class="stat-card">
          <strong><?= e((string) $stats['messages']) ?></strong>
          <span>Messages sent</span>
        </div>
        <div class="stat-card">
          <strong><?= !empty($user['email_verified']) ? 'Yes' : 'No' ?></strong>
          <span>Email verified</span>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
