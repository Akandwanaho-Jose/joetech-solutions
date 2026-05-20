<?php
require_once __DIR__ . '/../includes/init.php';

$page_title = 'Shop';
$meta_desc  = 'Browse laptops, accessories, components, and other products from Joetech Solutions.';

$selected_category = get('category');
$search = get('q');

$categories = [];
$products = [];
$db_error = null;

try {
    $categories = db_all(
        "SELECT id, name, slug
         FROM product_categories
         WHERE status = 'active'
         ORDER BY sort_order ASC, name ASC"
    );

    $where = ["p.status = 'active'", "p.deleted_at IS NULL"];
    $params = [];

    if ($selected_category !== '') {
        $where[] = 'c.slug = ?';
        $params[] = $selected_category;
    }

    if ($search !== '') {
        $where[] = '(p.name LIKE ? OR p.brand LIKE ? OR p.model LIKE ? OR p.description LIKE ?)';
        $term = '%' . $search . '%';
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    $products = db_all(
        "SELECT
            p.id,
            p.name,
            p.slug,
            p.description,
            p.price,
            p.old_price,
            p.stock_qty,
            p.condition_type,
            p.brand,
            p.model,
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
         WHERE " . implode(' AND ', $where) . "
         ORDER BY p.created_at DESC
         LIMIT 24",
        $params
    );
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development'
        ? $e->getMessage()
        : 'The shop is temporarily unavailable.';
}

$has_filters = $selected_category !== '' || $search !== '';

include INCLUDES . '/header.php';
?>

<section class="hero shop-hero">
  <div class="wrapper hero-grid">
    <div class="hero-copy-block">
      <p class="eyebrow">Shop</p>
      <h1>Technology products for work, study, and everyday operations.</h1>
      <p class="hero-copy">
        Browse current inventory, compare options, and move from product selection to cart and checkout through a clear purchase flow.
      </p>
      <div class="hero-actions">
        <a class="button" href="<?= SITE_URL ?>/public/cart.php">View cart</a>
        <a class="button button-secondary" href="<?= SITE_URL ?>/public/contact.php">Ask about a product</a>
      </div>
    </div>

    <aside class="hero-panel landing-panel">
      <p class="panel-kicker">Categories</p>
      <?php if ($categories): ?>
        <div class="chip-list">
          <?php foreach ($categories as $category): ?>
            <a class="chip<?= $selected_category === $category['slug'] ? ' chip-active' : '' ?>" href="<?= SITE_URL ?>/public/shop.php?category=<?= e($category['slug']) ?>">
              <?= e($category['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>Categories will appear here as products are published to the catalog.</p>
      <?php endif; ?>
    </aside>
  </div>
</section>

<section class="section">
  <div class="wrapper">
    <form class="filter-bar" method="get" action="<?= SITE_URL ?>/public/shop.php">
      <div class="filter-field">
        <label for="q">Search products</label>
        <input id="q" name="q" type="text" value="<?= e($search) ?>" placeholder="Laptop, SSD, mouse...">
      </div>

      <div class="filter-field">
        <label for="category">Category</label>
        <select id="category" name="category">
          <option value="">All categories</option>
          <?php foreach ($categories as $category): ?>
            <option value="<?= e($category['slug']) ?>"<?= $selected_category === $category['slug'] ? ' selected' : '' ?>>
              <?= e($category['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="filter-actions">
        <button class="button" type="submit">Filter</button>
        <a class="button button-secondary" href="<?= SITE_URL ?>/public/shop.php">Reset</a>
      </div>
    </form>

    <?php if ($db_error): ?>
      <div class="inline-error"><?= e($db_error) ?></div>
    <?php endif; ?>

    <div class="section-heading shop-heading">
      <p class="eyebrow">Catalog</p>
      <h2>Available products</h2>
      <p>Explore the products currently published in the Joetech catalog.</p>
    </div>

    <?php if ($products): ?>
      <div class="product-grid">
        <?php foreach ($products as $product): ?>
          <article class="product-card">
            <div class="product-media">
              <?php if (!empty($product['image_url'])): ?>
                <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>">
              <?php else: ?>
                <div class="product-placeholder"><?= e(substr($product['name'], 0, 1)) ?></div>
              <?php endif; ?>
            </div>

            <div class="product-body">
              <div class="product-meta">
                <span class="chip"><?= e($product['category_name'] ?? 'Product') ?></span>
                <span class="stock-tag<?= (int) ($product['stock_qty'] ?? 0) > 0 ? '' : ' stock-tag-out' ?>">
                  <?= (int) ($product['stock_qty'] ?? 0) > 0 ? 'In stock' : 'Out of stock' ?>
                </span>
              </div>

              <h3><?= e($product['name']) ?></h3>
              <p><?= e(excerpt($product['description'] ?? 'Product available on request.', 18)) ?></p>

              <div class="product-spec">
                <?php if (!empty($product['brand'])): ?><span><?= e($product['brand']) ?></span><?php endif; ?>
                <?php if (!empty($product['model'])): ?><span><?= e($product['model']) ?></span><?php endif; ?>
                <?php if (!empty($product['condition_type'])): ?><span><?= e(ucfirst((string) $product['condition_type'])) ?></span><?php endif; ?>
              </div>

              <div class="product-footer">
                <div class="price-block">
                  <strong><?= money((float) $product['price']) ?></strong>
                  <?php if (!empty($product['old_price'])): ?>
                    <span><?= money((float) $product['old_price']) ?></span>
                  <?php endif; ?>
                </div>

                <p class="buy-note">
                  <?= (int) ($product['stock_qty'] ?? 0) > 0 ? 'Ready to add to cart' : 'Currently unavailable for checkout' ?>
                </p>

                <div class="product-actions">
                  <?php if ((int) ($product['stock_qty'] ?? 0) > 0): ?>
                    <form method="post" action="<?= SITE_URL ?>/public/cart.php" class="inline-form">
                      <?= csrf_field(); ?>
                      <input type="hidden" name="action" value="add">
                      <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>">
                      <input type="hidden" name="quantity" value="1">
                      <button class="button product-cta" type="submit">Add to cart</button>
                    </form>
                  <?php else: ?>
                    <a class="button button-secondary product-cta" href="<?= SITE_URL ?>/public/contact.php">Ask about availability</a>
                  <?php endif; ?>
                  <a class="text-link product-link" href="<?= SITE_URL ?>/public/product.php?slug=<?= e($product['slug']) ?>">View details</a>
                </div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <h3><?= $has_filters ? 'No products matched your filter.' : 'No products are published yet.' ?></h3>
        <p>
          <?= $has_filters
              ? 'Try a different search term or browse all categories.'
              : 'Publish products from the admin side and they will appear here automatically.' ?>
        </p>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
