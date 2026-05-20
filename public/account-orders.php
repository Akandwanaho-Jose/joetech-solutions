<?php
require_once __DIR__ . '/../includes/init.php';

require_login();

$page_title = 'My Orders';
$meta_desc  = 'Customer order history';

$orders = [];
$db_error = null;

try {
    $orders = db_all(
        "SELECT order_ref, total_amount, payment_status, delivery_status, created_at
         FROM orders
         WHERE user_id = ?
         ORDER BY created_at DESC",
        [$_SESSION['user_id']]
    );
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development'
        ? $e->getMessage()
        : 'Orders are temporarily unavailable.';
}

include INCLUDES . '/header.php';
?>

<section class="section">
  <div class="wrapper account-grid">
    <aside class="hero-panel landing-panel">
      <p class="panel-kicker">Orders</p>
      <h2>Your account menu</h2>
      <div class="side-list">
        <a class="side-item" href="<?= SITE_URL ?>/public/account.php"><strong>Overview</strong><span>Dashboard</span></a>
        <a class="side-item" href="<?= SITE_URL ?>/public/account-orders.php"><strong>Orders</strong><span>History</span></a>
      </div>
    </aside>

    <div class="account-content">
      <div class="section-heading">
        <p class="eyebrow">Order History</p>
        <h1>Your orders</h1>
      </div>

      <?php if ($db_error): ?>
        <div class="inline-error"><?= e($db_error) ?></div>
      <?php endif; ?>

      <?php if ($orders): ?>
        <div class="table-list">
          <?php foreach ($orders as $order): ?>
            <article class="table-item">
              <div>
                <strong><?= e($order['order_ref']) ?></strong>
                <span><?= e(date_fmt((string) $order['created_at'])) ?></span>
              </div>
              <div>
                <strong><?= money((float) $order['total_amount']) ?></strong>
                <span><?= e(ucwords(str_replace('_', ' ', (string) $order['delivery_status']))) ?></span>
              </div>
              <div>
                <span class="chip"><?= e(ucwords(str_replace('_', ' ', (string) $order['payment_status']))) ?></span>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <h3>No orders yet.</h3>
          <p>Your future orders will appear here once checkout is connected.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
