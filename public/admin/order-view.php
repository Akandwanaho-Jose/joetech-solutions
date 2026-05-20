<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_orders');

$order_id = get_int('id');
$order = null;
$items = [];
$db_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $order_id > 0) {
    verify_csrf();

    $delivery_status = post('delivery_status');
    $payment_status = post('payment_status');
    $admin_note = post('admin_note');

    $allowed_delivery = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
    $allowed_payment = ['pending', 'partially_paid', 'paid', 'failed', 'refunded'];

    if (in_array($delivery_status, $allowed_delivery, true) && in_array($payment_status, $allowed_payment, true)) {
        try {
            db_run(
                "UPDATE orders
                 SET delivery_status = ?, payment_status = ?, notes = ?, updated_at = NOW()
                 WHERE id = ?",
                [$delivery_status, $payment_status, $admin_note !== '' ? $admin_note : null, $order_id]
            );

            $updated_order = db_one("SELECT * FROM orders WHERE id = ? LIMIT 1", [$order_id]);
            if ($updated_order) {
                notify_order_status($updated_order);
            }

            flash('success', 'Order updated successfully.');
            redirect('public/admin/order-view.php?id=' . $order_id);
        } catch (Throwable $e) {
            $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Could not update this order.';
        }
    } else {
        $db_error = 'Choose valid order statuses before saving.';
    }
}

try {
    if ($order_id > 0) {
        $order = db_one(
            "SELECT *
             FROM orders
             WHERE id = ?
             LIMIT 1",
            [$order_id]
        );

        if ($order) {
            $items = db_all(
                "SELECT product_name, product_sku, unit_price, quantity, subtotal
                 FROM order_items
                 WHERE order_id = ?
                 ORDER BY id ASC",
                [$order_id]
            );
        }
    }
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Order details are temporarily unavailable.';
}

$page_title = $order ? 'Order ' . $order['order_ref'] : 'Order Detail';
include INCLUDES . '/admin_header.php';
?>

<?php if ($db_error): ?>
  <div class="inline-error"><?= e($db_error) ?></div>
<?php endif; ?>

<?php if ($order): ?>
  <div class="section-heading">
    <p class="eyebrow">Order Detail</p>
    <h2><?= e($order['order_ref']) ?></h2>
    <p><?= e($order['full_name']) ?> | <?= e($order['email']) ?> | <?= e($order['phone']) ?></p>
  </div>

  <div class="stat-strip">
    <article class="stat-card">
      <strong><?= money((float) $order['total_amount']) ?></strong>
      <span>Total</span>
    </article>
    <article class="stat-card">
      <strong><?= e(ucwords(str_replace('_', ' ', (string) $order['payment_method']))) ?></strong>
      <span>Payment method</span>
    </article>
    <article class="stat-card">
      <strong><?= e(ucwords(str_replace('_', ' ', (string) $order['delivery_status']))) ?></strong>
      <span>Delivery status</span>
    </article>
  </div>

  <div class="form-card">
    <h3>Update order</h3>
    <form method="post" action="<?= SITE_URL ?>/public/admin/order-view.php?id=<?= e((string) $order_id) ?>" class="contact-form">
      <?= csrf_field(); ?>

      <label for="delivery_status">Delivery status</label>
      <select id="delivery_status" name="delivery_status">
        <?php foreach (['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $status): ?>
          <option value="<?= e($status) ?>"<?= $order['delivery_status'] === $status ? ' selected' : '' ?>>
            <?= e(ucwords(str_replace('_', ' ', $status))) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label for="payment_status">Payment status</label>
      <select id="payment_status" name="payment_status">
        <?php foreach (['pending', 'partially_paid', 'paid', 'failed', 'refunded'] as $status): ?>
          <option value="<?= e($status) ?>"<?= $order['payment_status'] === $status ? ' selected' : '' ?>>
            <?= e(ucwords(str_replace('_', ' ', $status))) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label for="admin_note">Internal note</label>
      <textarea id="admin_note" name="admin_note" rows="3"><?= e((string) ($order['notes'] ?? '')) ?></textarea>

      <button class="button" type="submit">Save changes</button>
    </form>
  </div>

  <div class="story-card">
    <h3>Delivery address</h3>
    <p><?= nl2br(e((string) $order['delivery_address'])) ?></p>
    <p><?= e($order['city']) ?>, <?= e($order['country']) ?></p>
    <?php if (!empty($order['notes'])): ?>
      <h3>Notes</h3>
      <p><?= nl2br(e((string) $order['notes'])) ?></p>
    <?php endif; ?>
  </div>

  <div class="table-list">
    <?php foreach ($items as $item): ?>
      <article class="table-item">
        <div>
          <strong><?= e($item['product_name']) ?></strong>
          <span><?= e($item['product_sku'] ?? '') ?></span>
        </div>
        <div>
          <strong><?= e((string) $item['quantity']) ?> pcs</strong>
          <span><?= money((float) $item['unit_price']) ?> each</span>
        </div>
        <div>
          <strong><?= money((float) $item['subtotal']) ?></strong>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <p><a class="text-link" href="<?= SITE_URL ?>/public/admin/orders.php">Back to orders</a></p>
<?php else: ?>
  <div class="empty-state">
    <h3>Order not found.</h3>
    <p>Return to the orders list and try again.</p>
  </div>
<?php endif; ?>

<?php include INCLUDES . '/admin_footer.php'; ?>
