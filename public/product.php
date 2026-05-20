<?php
require_once __DIR__ . '/../includes/init.php';

$slug = get('slug');
$product = null;
$related_products = [];
$db_error = null;

try {
    if ($slug !== '') {
        $product = db_one(
            "SELECT
                p.id,
                p.name,
                p.slug,
                p.sku,
                p.description,
                p.specifications,
                p.price,
                p.old_price,
                p.stock_qty,
                p.condition_type,
                p.brand,
                p.model,
                p.views,
                c.name AS category_name,
                c.slug AS category_slug,
                (
                    SELECT pi.image_url
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC
                    LIMIT 1
                ) AS image_url
             FROM products p
             INNER JOIN product_categories c ON c.id = p.category_id
             WHERE p.slug = ?
               AND p.status = 'active'
               AND p.deleted_at IS NULL
             LIMIT 1",
            [$slug]
        );
    }

    if ($product) {
        $related_products = db_all(
            "SELECT p.name, p.slug, p.price
             FROM products p
             WHERE p.category_id = (
                 SELECT category_id FROM products WHERE id = ?
             )
               AND p.id <> ?
               AND p.status = 'active'
               AND p.deleted_at IS NULL
             ORDER BY p.created_at DESC
             LIMIT 3",
            [$product['id'], $product['id']]
        );
    }
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development'
        ? $e->getMessage()
        : 'The product page is temporarily unavailable.';
}

$page_title = $product ? $product['name'] : 'Product';
$meta_desc = $product && $product['description'] !== ''
    ? excerpt((string) $product['description'], 22)
    : 'Product details from Joetech Solutions.';

$specs = [];
if ($product && !empty($product['specifications'])) {
    $decoded = json_decode((string) $product['specifications'], true);
    if (is_array($decoded)) {
        $specs = $decoded;
    }
}

include INCLUDES . '/header.php';
?>

<?php if (!$product): ?>
  <section class="section">
    <div class="wrapper">
      <?php if ($db_error): ?>
        <div class="inline-error"><?= e($db_error) ?></div>
      <?php endif; ?>

      <div class="empty-state empty-state-large">
        <p class="eyebrow">Product</p>
        <h1>Product not found</h1>
        <p>The item you are looking for is not available, may have been removed, or the link may be incorrect.</p>
        <div class="hero-actions">
          <a class="button" href="<?= SITE_URL ?>/public/shop.php">Back to shop</a>
          <a class="button button-secondary" href="<?= SITE_URL ?>/public/contact.php">Ask about a product</a>
        </div>
      </div>
    </div>
  </section>
<?php else: ?>
  <section class="section">
    <div class="wrapper product-detail-grid">
      <div class="detail-media">
        <?php if (!empty($product['image_url'])): ?>
          <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>">
        <?php else: ?>
          <div class="detail-placeholder"><?= e(substr((string) $product['name'], 0, 1)) ?></div>
        <?php endif; ?>
      </div>

      <div class="detail-panel">
        <?php if ($db_error): ?>
          <div class="inline-error"><?= e($db_error) ?></div>
        <?php endif; ?>

        <p class="eyebrow"><?= e($product['category_name'] ?? 'Product') ?></p>
        <h1><?= e($product['name']) ?></h1>
        <p class="hero-copy"><?= e((string) $product['description']) ?></p>

        <div class="product-spec">
          <?php if (!empty($product['brand'])): ?><span><?= e($product['brand']) ?></span><?php endif; ?>
          <?php if (!empty($product['model'])): ?><span><?= e($product['model']) ?></span><?php endif; ?>
          <?php if (!empty($product['condition_type'])): ?><span><?= e(ucfirst((string) $product['condition_type'])) ?></span><?php endif; ?>
          <?php if (!empty($product['sku'])): ?><span>SKU: <?= e($product['sku']) ?></span><?php endif; ?>
        </div>

        <div class="detail-price">
          <strong><?= money((float) $product['price']) ?></strong>
          <?php if (!empty($product['old_price'])): ?>
            <span><?= money((float) $product['old_price']) ?></span>
          <?php endif; ?>
        </div>

        <div class="product-meta">
          <span class="stock-tag<?= (int) ($product['stock_qty'] ?? 0) > 0 ? '' : ' stock-tag-out' ?>">
            <?= (int) ($product['stock_qty'] ?? 0) > 0 ? 'Available' : 'Out of stock' ?>
          </span>
        </div>

        <div class="hero-actions">
          <?php if ((int) ($product['stock_qty'] ?? 0) > 0): ?>
            <form method="post" action="<?= SITE_URL ?>/public/cart.php" class="inline-form">
              <?= csrf_field(); ?>
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>">
              <input type="hidden" name="quantity" value="1">
              <button class="button" type="submit">Add to cart</button>
            </form>
            <a class="button button-secondary" href="<?= SITE_URL ?>/public/cart.php">Go to cart</a>
          <?php else: ?>
            <a class="button" href="<?= SITE_URL ?>/public/contact.php">Ask about availability</a>
          <?php endif; ?>
          <a class="button button-secondary" href="<?= SITE_URL ?>/public/shop.php">Back to shop</a>
        </div>
      </div>
    </div>
  </section>

  <?php if ($specs): ?>
    <section class="section section-alt">
      <div class="wrapper">
        <div class="section-heading">
          <p class="eyebrow">Specifications</p>
          <h2>Quick details</h2>
        </div>

        <div class="spec-grid">
          <?php foreach ($specs as $label => $value): ?>
            <article class="spec-card">
              <h3><?= e((string) $label) ?></h3>
              <p><?= e(is_scalar($value) ? (string) $value : json_encode($value)) ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($related_products): ?>
    <section class="section">
      <div class="wrapper">
        <div class="section-heading">
          <p class="eyebrow">Related products</p>
          <h2>You may also want to see</h2>
        </div>

        <div class="card-grid">
          <?php foreach ($related_products as $related): ?>
            <article class="card">
              <h3><?= e($related['name']) ?></h3>
              <p><?= money((float) $related['price']) ?></p>
              <a class="text-link" href="<?= SITE_URL ?>/public/product.php?slug=<?= e($related['slug']) ?>">View product</a>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>
<?php endif; ?>

<?php include INCLUDES . '/footer.php'; ?>
