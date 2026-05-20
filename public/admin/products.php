<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_products');

$page_title = 'Products';
$products = [];
$db_error = null;

try {
    $products = db_all(
        "SELECT
            p.id,
            p.name,
            p.slug,
            p.price,
            p.stock_qty,
            p.status,
            c.name AS category_name,
            (
                SELECT pi.image_url
                FROM product_images pi
                WHERE pi.product_id = p.id
                ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC
                LIMIT 1
            ) AS image_url
         FROM products p
         INNER JOIN product_categories c ON c.id = p.category_id
         WHERE p.deleted_at IS NULL
         ORDER BY p.created_at DESC
         LIMIT 100"
    );
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Products are temporarily unavailable.';
}

include INCLUDES . '/admin_header.php';
?>

<div class="section-heading">
  <p class="eyebrow">Catalog</p>
  <h2>Manage products</h2>
  <p>Add, edit, and monitor the products that appear in the shop.</p>
</div>

<p><a class="button" href="<?= SITE_URL ?>/public/admin/product-add.php">Add product</a></p>

<?php if ($db_error): ?>
  <div class="inline-error"><?= e($db_error) ?></div>
<?php endif; ?>

<?php if ($products): ?>
  <div class="table-list">
    <?php foreach ($products as $product): ?>
      <article class="table-item">
        <div>
          <?php if (!empty($product['image_url'])): ?>
            <span class="table-thumb"><img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>"></span>
          <?php endif; ?>
          <strong><?= e($product['name']) ?></strong>
          <span><?= e($product['category_name']) ?> | <?= e($product['slug']) ?></span>
        </div>
        <div>
          <strong><?= money((float) $product['price']) ?></strong>
          <span>Stock: <?= e((string) $product['stock_qty']) ?></span>
        </div>
        <div>
          <span class="chip"><?= e(ucwords(str_replace('_', ' ', (string) $product['status']))) ?></span>
        </div>
        <div class="table-actions">
          <a class="text-link" href="<?= SITE_URL ?>/public/admin/product-edit.php?id=<?= e((string) $product['id']) ?>">Edit</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="empty-state">
    <h3>No products yet.</h3>
    <p>Add your first product to start populating the shop.</p>
  </div>
<?php endif; ?>

<?php include INCLUDES . '/admin_footer.php'; ?>
