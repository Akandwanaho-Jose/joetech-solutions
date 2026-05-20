<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('view_reports');

$page_title = 'Reports';
$stats = ['orders' => 0, 'revenue' => 0, 'messages' => 0, 'requests' => 0, 'repairs' => 0, 'posts' => 0];
$top_products = [];
$order_status = [];

try {
    $stats['orders'] = (int) (db_one("SELECT COUNT(*) AS total FROM orders")['total'] ?? 0);
    $stats['revenue'] = (float) (db_one("SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders WHERE payment_status IN ('paid','partially_paid')")['total'] ?? 0);
    $stats['messages'] = (int) (db_one("SELECT COUNT(*) AS total FROM contact_messages")['total'] ?? 0);
    $stats['requests'] = (int) (db_one("SELECT COUNT(*) AS total FROM service_requests")['total'] ?? 0);
    $stats['repairs'] = (int) (db_one("SELECT COUNT(*) AS total FROM repair_jobs")['total'] ?? 0);
    $stats['posts'] = (int) (db_one("SELECT COUNT(*) AS total FROM blog_posts WHERE deleted_at IS NULL")['total'] ?? 0);
    $top_products = db_all(
        "SELECT product_name, SUM(quantity) AS qty_sold
         FROM order_items
         GROUP BY product_name
         ORDER BY qty_sold DESC, product_name ASC
         LIMIT 5"
    );
    $order_status = db_all(
        "SELECT delivery_status, COUNT(*) AS total
         FROM orders
         GROUP BY delivery_status
         ORDER BY total DESC"
    );
} catch (Throwable $e) {
    // Keep reports page usable even if some tables are not yet populated.
}

include INCLUDES . '/admin_header.php';
?>
<div class="section-heading"><p class="eyebrow">Admin</p><h2>Reports</h2><p>Operational reporting for orders, requests, repairs, content, and commercial performance.</p></div>
<div class="stat-strip">
  <div class="stat-card"><strong><?= e((string) $stats['orders']) ?></strong><span>Total orders</span></div>
  <div class="stat-card"><strong><?= money((float) $stats['revenue']) ?></strong><span>Paid revenue</span></div>
  <div class="stat-card"><strong><?= e((string) $stats['messages']) ?></strong><span>Messages</span></div>
  <div class="stat-card"><strong><?= e((string) $stats['requests']) ?></strong><span>Service requests</span></div>
  <div class="stat-card"><strong><?= e((string) $stats['repairs']) ?></strong><span>Repair jobs</span></div>
  <div class="stat-card"><strong><?= e((string) $stats['posts']) ?></strong><span>Blog posts</span></div>
</div>

<div class="dashboard-grid">
  <div class="story-card">
    <h3>Best-selling products</h3>
    <?php if ($top_products): ?>
      <div class="table-list">
        <?php foreach ($top_products as $item): ?>
          <article class="table-item">
            <div><strong><?= e($item['product_name']) ?></strong></div>
            <div><strong><?= e((string) $item['qty_sold']) ?></strong><span>Units sold</span></div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>No product sales data yet.</p>
    <?php endif; ?>
  </div>

  <div class="story-card">
    <h3>Order status mix</h3>
    <?php if ($order_status): ?>
      <div class="table-list">
        <?php foreach ($order_status as $status): ?>
          <article class="table-item">
            <div><strong><?= e(ucwords(str_replace('_', ' ', (string) $status['delivery_status']))) ?></strong></div>
            <div><strong><?= e((string) $status['total']) ?></strong><span>Orders</span></div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>No order status data yet.</p>
    <?php endif; ?>
  </div>
</div>
<?php include INCLUDES . '/admin_footer.php'; ?>
