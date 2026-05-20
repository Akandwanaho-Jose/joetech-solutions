<?php
require_once __DIR__ . '/../includes/init.php';

$page_title = 'Order Placed';
$meta_desc  = 'Order confirmation page';

$order_ref = get('ref', $_SESSION['last_order_ref'] ?? '');
$order = null;

if ($order_ref !== '') {
    try {
        $order = db_one(
            "SELECT order_ref, full_name, email, total_amount, payment_method, delivery_status, created_at
             FROM orders
             WHERE order_ref = ?
             LIMIT 1",
            [$order_ref]
        );
    } catch (Throwable $e) {
        $order = null;
    }
}

include INCLUDES . '/header.php';
?>

<section class="section">
  <div class="wrapper success-wrap">
    <div class="story-card success-card">
      <p class="eyebrow">Order Confirmed</p>
      <h1>Your order has been placed.</h1>
      <p>This gives the site a complete basic commerce path from product browsing to order completion.</p>

      <?php if ($order): ?>
        <div class="spec-grid">
          <article class="spec-card">
            <h3>Reference</h3>
            <p><?= e($order['order_ref']) ?></p>
          </article>
          <article class="spec-card">
            <h3>Total</h3>
            <p><?= money((float) $order['total_amount']) ?></p>
          </article>
          <article class="spec-card">
            <h3>Payment</h3>
            <p><?= e(ucwords(str_replace('_', ' ', (string) $order['payment_method']))) ?></p>
          </article>
        </div>
      <?php endif; ?>

      <div class="hero-actions">
        <a class="button" href="<?= SITE_URL ?>/public/shop.php">Continue shopping</a>
        <a class="button button-secondary" href="<?= SITE_URL ?>/public/account-orders.php">View my orders</a>
      </div>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
