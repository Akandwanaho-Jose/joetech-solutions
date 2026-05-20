<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();

$page_title = 'Dashboard';
$cards = [
    'new_orders' => 0,
    'unread_messages' => 0,
    'pending_requests' => 0,
    'low_stock_products' => 0,
];
$recent_orders = [];
$recent_activity = [];

try {
    $cards['new_orders'] = (int) (db_one("SELECT COUNT(*) AS total FROM orders WHERE delivery_status IN ('pending','confirmed','processing')")['total'] ?? 0);
    $cards['unread_messages'] = (int) (db_one("SELECT COUNT(*) AS total FROM contact_messages WHERE status = 'unread'")['total'] ?? 0);
    $cards['pending_requests'] = (int) (db_one("SELECT COUNT(*) AS total FROM service_requests WHERE status IN ('new','reviewing','quoted','approved','in_progress')")['total'] ?? 0);
    $cards['low_stock_products'] = (int) (db_one("SELECT COUNT(*) AS total FROM products WHERE deleted_at IS NULL AND status = 'active' AND stock_qty <= min_stock_level")['total'] ?? 0);

    $recent_orders = db_all(
        "SELECT id, order_ref, full_name, total_amount, delivery_status, created_at
         FROM orders
         ORDER BY created_at DESC
         LIMIT 5"
    );

    $recent_activity = db_all(
        "SELECT activity_type, activity_label, activity_meta, activity_time, activity_link
         FROM (
            SELECT
                'order' AS activity_type,
                CONCAT('New order ', order_ref) AS activity_label,
                full_name AS activity_meta,
                created_at AS activity_time,
                CONCAT('order-view.php?id=', id) AS activity_link
            FROM orders

            UNION ALL

            SELECT
                'message' AS activity_type,
                CONCAT('Message: ', subject) AS activity_label,
                name AS activity_meta,
                created_at AS activity_time,
                CONCAT('message-view.php?id=', id) AS activity_link
            FROM contact_messages

            UNION ALL

            SELECT
                'request' AS activity_type,
                CONCAT('Service request ', request_ref) AS activity_label,
                full_name AS activity_meta,
                created_at AS activity_time,
                CONCAT('request-view.php?id=', id) AS activity_link
            FROM service_requests
         ) activity_feed
         ORDER BY activity_time DESC
         LIMIT 8"
    );
} catch (Throwable $e) {
    // Keep dashboard usable even when some modules are not ready.
}

include INCLUDES . '/admin_header.php';
?>

<div class="section-heading">
  <p class="eyebrow">Dashboard</p>
  <h2>Admin overview</h2>
  <p>Start with what needs attention first: new orders, unread messages, pending requests, stock risk, and the latest activity across the site.</p>
</div>

<div class="stat-strip">
  <div class="stat-card">
    <strong><?= e((string) $cards['new_orders']) ?></strong>
    <span>New orders</span>
  </div>
  <div class="stat-card">
    <strong><?= e((string) $cards['unread_messages']) ?></strong>
    <span>Unread messages</span>
  </div>
  <div class="stat-card">
    <strong><?= e((string) $cards['pending_requests']) ?></strong>
    <span>Pending service requests</span>
  </div>
  <div class="stat-card">
    <strong><?= e((string) $cards['low_stock_products']) ?></strong>
    <span>Low stock products</span>
  </div>
</div>

<div class="quick-grid">
  <?php if (has_permission('manage_orders')): ?>
    <a class="quick-link" href="<?= SITE_URL ?>/public/admin/orders.php">
      <strong>Review orders</strong>
      <span>Track new checkouts and move them through fulfillment.</span>
    </a>
  <?php endif; ?>
  <?php if (has_permission('manage_products')): ?>
    <a class="quick-link" href="<?= SITE_URL ?>/public/admin/products.php">
      <strong>Manage products</strong>
      <span>Update pricing, stock, and what appears in the shop.</span>
    </a>
  <?php endif; ?>
  <?php if (has_permission('manage_messages')): ?>
    <a class="quick-link" href="<?= SITE_URL ?>/public/admin/messages.php">
      <strong>View messages</strong>
      <span>Respond to enquiries and keep leads from going cold.</span>
    </a>
  <?php endif; ?>
  <?php if (has_permission('manage_requests')): ?>
    <a class="quick-link" href="<?= SITE_URL ?>/public/admin/requests.php">
      <strong>Track requests</strong>
      <span>Review quotes, approvals, and active service opportunities.</span>
    </a>
  <?php endif; ?>
  <?php if (has_permission('manage_repairs')): ?>
    <a class="quick-link" href="<?= SITE_URL ?>/public/admin/repairs.php">
      <strong>Handle repairs</strong>
      <span>Move devices from intake to diagnosis, repair, and collection.</span>
    </a>
  <?php endif; ?>
</div>

<div class="dashboard-grid">
  <div class="story-card">
    <h3>Recent orders</h3>
    <?php if ($recent_orders): ?>
      <div class="table-list">
        <?php foreach ($recent_orders as $order): ?>
          <article class="table-item">
            <div>
              <strong><?= e($order['order_ref']) ?></strong>
              <span><?= e($order['full_name']) ?></span>
            </div>
            <div>
              <strong><?= money((float) $order['total_amount']) ?></strong>
              <span><?= e(date_fmt((string) $order['created_at'])) ?></span>
            </div>
            <div>
              <span class="chip"><?= e(ucwords(str_replace('_', ' ', (string) $order['delivery_status']))) ?></span>
            </div>
            <div class="table-actions">
              <a class="text-link" href="<?= SITE_URL ?>/public/admin/order-view.php?id=<?= e((string) $order['id']) ?>">Open order</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>No orders yet.</p>
    <?php endif; ?>
  </div>

  <div class="story-card">
    <h3>Recent activity</h3>
    <?php if ($recent_activity): ?>
      <div class="activity-list">
        <?php foreach ($recent_activity as $activity): ?>
          <article class="activity-item">
            <div>
              <strong><?= e($activity['activity_label']) ?></strong>
              <span><?= e($activity['activity_meta']) ?></span>
            </div>
            <div>
              <span class="chip"><?= e(ucfirst((string) $activity['activity_type'])) ?></span>
              <span><?= e(date_fmt((string) $activity['activity_time'])) ?></span>
            </div>
            <div class="table-actions">
              <a class="text-link" href="<?= SITE_URL ?>/public/admin/<?= e($activity['activity_link']) ?>">Open</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>No activity yet.</p>
    <?php endif; ?>
  </div>
</div>

<?php include INCLUDES . '/admin_footer.php'; ?>
