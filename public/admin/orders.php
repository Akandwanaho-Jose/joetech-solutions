<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_orders');

$page_title = 'Orders';
$orders = [];
$db_error = null;

try {
    $orders = db_all(
        "SELECT
            o.id,
            o.order_ref,
            o.full_name,
            o.email,
            o.phone,
            o.total_amount,
            o.payment_status,
            o.delivery_status,
            o.created_at,
            (
                SELECT COUNT(*)
                FROM order_items oi
                WHERE oi.order_id = o.id
            ) AS item_count
         FROM orders o
         ORDER BY o.created_at DESC
         LIMIT 100"
    );
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Orders are temporarily unavailable.';
}

include INCLUDES . '/admin_header.php';
?>

<div class="section-heading">
  <p class="eyebrow">Admin Orders</p>
  <h2>Customer orders received</h2>
  <p>This is where new ecommerce orders now appear after checkout.</p>
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
          <span><?= e($order['full_name']) ?> | <?= e($order['email']) ?></span>
        </div>
        <div>
          <strong><?= money((float) $order['total_amount']) ?></strong>
          <span><?= e((string) $order['item_count']) ?> item(s)</span>
        </div>
        <div>
          <span class="chip"><?= e(ucwords(str_replace('_', ' ', (string) $order['payment_status']))) ?></span>
          <span class="chip"><?= e(ucwords(str_replace('_', ' ', (string) $order['delivery_status']))) ?></span>
        </div>
        <div class="table-actions">
          <a class="text-link" href="<?= SITE_URL ?>/public/admin/order-view.php?id=<?= e((string) $order['id']) ?>">Open order</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="empty-state">
    <h3>No orders yet.</h3>
    <p>Completed checkouts will appear here.</p>
  </div>
<?php endif; ?>

<?php include INCLUDES . '/admin_footer.php'; ?>
