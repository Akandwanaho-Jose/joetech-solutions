<?php
require_once __DIR__ . '/../includes/init.php';

$_SESSION['cart'] = $_SESSION['cart'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = post('action');
    $product_id = post_int('product_id');
    $quantity = max(1, post_int('quantity', 1));

    if ($action === 'add' && $product_id > 0) {
        $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $quantity;
        flash('success', 'Item added to cart. Review it, then continue to checkout.');
        redirect('public/cart.php');
    }

    if ($action === 'update') {
        foreach ($_POST['qty'] ?? [] as $id => $qty) {
            $id = (int) $id;
            $qty = (int) $qty;

            if ($id <= 0) {
                continue;
            }

            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
            } else {
                $_SESSION['cart'][$id] = $qty;
            }
        }

        flash('success', 'Cart updated.');
        redirect('public/cart.php');
    }

    if ($action === 'remove' && $product_id > 0) {
        unset($_SESSION['cart'][$product_id]);
        flash('success', 'Item removed from cart.');
        redirect('public/cart.php');
    }
}

$page_title = 'Cart';
$meta_desc  = 'Review your cart before checkout.';

$cart_ids = array_keys($_SESSION['cart']);
$cart_items = [];
$subtotal = 0.0;
$db_error = null;

if ($cart_ids) {
    try {
        $placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
        $products = db_all(
            "SELECT p.id, p.name, p.slug, p.price, p.stock_qty, p.status, p.sku
             FROM products p
             WHERE p.id IN ($placeholders)
               AND p.deleted_at IS NULL",
            $cart_ids
        );

        $products_by_id = [];
        foreach ($products as $product) {
            $products_by_id[(int) $product['id']] = $product;
        }

        foreach ($_SESSION['cart'] as $product_id => $qty) {
            $product_id = (int) $product_id;
            if (!isset($products_by_id[$product_id])) {
                continue;
            }

            $product = $products_by_id[$product_id];
            $line_total = (float) $product['price'] * (int) $qty;
            $subtotal += $line_total;

            $cart_items[] = [
                'product' => $product,
                'qty' => (int) $qty,
                'line_total' => $line_total,
            ];
        }
    } catch (Throwable $e) {
        $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Cart products are temporarily unavailable.';
    }
}

include INCLUDES . '/header.php';
?>

<section class="section">
  <div class="wrapper cart-grid">
    <div class="account-content">
      <div class="section-heading">
        <p class="eyebrow">Cart</p>
        <h1>Your selected items</h1>
        <p>Keep this step simple. The cart should help someone review items and move forward without friction.</p>
      </div>

      <?php if ($db_error): ?>
        <div class="inline-error"><?= e($db_error) ?></div>
      <?php endif; ?>

      <?php if ($cart_items): ?>
        <form method="post" action="<?= SITE_URL ?>/public/cart.php" class="table-list">
          <?= csrf_field(); ?>
          <input type="hidden" name="action" value="update">

          <?php foreach ($cart_items as $item): ?>
            <article class="table-item cart-item">
              <div>
                <strong><?= e($item['product']['name']) ?></strong>
                <span><?= e($item['product']['sku'] ?? '') ?></span>
              </div>
              <div>
                <label for="qty-<?= e((string) $item['product']['id']) ?>">Qty</label>
                <input id="qty-<?= e((string) $item['product']['id']) ?>" class="qty-input" type="number" min="0" name="qty[<?= e((string) $item['product']['id']) ?>]" value="<?= e((string) $item['qty']) ?>">
              </div>
              <div>
                <strong><?= money((float) $item['line_total']) ?></strong>
                <span><?= money((float) $item['product']['price']) ?> each</span>
              </div>
              <div class="table-actions">
                <a class="text-link" href="<?= SITE_URL ?>/public/product.php?slug=<?= e($item['product']['slug']) ?>">View</a>
                <button class="button button-secondary button-small" type="submit">Save</button>
              </div>
            </article>
          <?php endforeach; ?>
        </form>
      <?php else: ?>
        <div class="empty-state">
          <h3>Your cart is empty.</h3>
          <p>Add products from the shop to start building an order.</p>
          <a class="button" href="<?= SITE_URL ?>/public/shop.php">Go to shop</a>
        </div>
      <?php endif; ?>
    </div>

    <aside class="hero-panel landing-panel">
      <p class="panel-kicker">Summary</p>
      <h2><?= money($subtotal) ?></h2>
      <p><?= count($cart_items) ?> item(s) currently in your cart.</p>
      <div class="hero-actions">
        <a class="button" href="<?= SITE_URL ?>/public/checkout.php">Proceed to checkout</a>
        <a class="button button-secondary" href="<?= SITE_URL ?>/public/shop.php">Continue shopping</a>
      </div>
    </aside>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
