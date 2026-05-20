<?php
require_once __DIR__ . '/../includes/init.php';

$_SESSION['cart'] = $_SESSION['cart'] ?? [];

$page_title = 'Checkout';
$meta_desc  = 'Complete your order details.';

$errors = [];
$db_error = null;
$cart_items = [];
$subtotal = 0.0;
$delivery_fee = 0.0;

$form = [
    'full_name' => logged_in() ? (current_user()['full_name'] ?? '') : '',
    'email' => logged_in() ? (current_user()['email'] ?? '') : '',
    'phone' => logged_in() ? (current_user()['phone'] ?? '') : '',
    'delivery_address' => '',
    'city' => 'Mbarara',
    'country' => 'Uganda',
    'payment_method' => 'cash_on_delivery',
    'notes' => '',
];

if ($_SESSION['cart']) {
    try {
        $cart_ids = array_keys($_SESSION['cart']);
        $placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
        $products = db_all(
            "SELECT id, name, slug, sku, price
             FROM products
             WHERE id IN ($placeholders)
               AND status = 'active'
               AND deleted_at IS NULL",
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
        $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Checkout items are temporarily unavailable.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $form['full_name'] = post('full_name');
    $form['email'] = post('email');
    $form['phone'] = post('phone');
    $form['delivery_address'] = post('delivery_address');
    $form['city'] = post('city', 'Mbarara');
    $form['country'] = post('country', 'Uganda');
    $form['payment_method'] = post('payment_method', 'cash_on_delivery');
    $form['notes'] = post('notes');

    if (!in_array($form['payment_method'], ['cash_on_delivery'], true)) {
        $form['payment_method'] = 'cash_on_delivery';
    }

    if (!$cart_items) {
        $errors['form'] = 'Your cart is empty.';
    }
    if ($form['full_name'] === '') $errors['full_name'] = 'Please enter your full name.';
    if ($form['email'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email.';
    if ($form['phone'] === '') $errors['phone'] = 'Please enter your phone number.';
    if ($form['delivery_address'] === '') $errors['delivery_address'] = 'Please enter a delivery address.';

    if (!$errors) {
        try {
            db()->beginTransaction();

            $cart_id = db_insert(
                "INSERT INTO carts (user_id, session_key, status) VALUES (?, ?, 'converted')",
                [$_SESSION['user_id'] ?? null, session_id()]
            );

            foreach ($cart_items as $item) {
                db_insert(
                    "INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)",
                    [$cart_id, $item['product']['id'], $item['qty']]
                );
            }

            $order_ref = order_ref();
            $order_id = db_insert(
                "INSERT INTO orders
                (user_id, cart_id, order_ref, full_name, email, phone, delivery_address, city, country, payment_method, payment_status, delivery_status, subtotal, delivery_fee, total_amount, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?, ?, ?)",
                [
                    $_SESSION['user_id'] ?? null,
                    $cart_id,
                    $order_ref,
                    $form['full_name'],
                    $form['email'],
                    $form['phone'],
                    $form['delivery_address'],
                    $form['city'],
                    $form['country'],
                    $form['payment_method'],
                    $subtotal,
                    $delivery_fee,
                    $subtotal + $delivery_fee,
                    $form['notes'] !== '' ? $form['notes'] : null,
                ]
            );

            foreach ($cart_items as $item) {
                db_insert(
                    "INSERT INTO order_items (order_id, product_id, product_name, product_sku, unit_price, quantity, subtotal)
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [
                        $order_id,
                        $item['product']['id'],
                        $item['product']['name'],
                        $item['product']['sku'] ?? null,
                        $item['product']['price'],
                        $item['qty'],
                        $item['line_total'],
                    ]
                );
            }

            db()->commit();

            notify_order_created($form, $cart_items, $order_ref, $subtotal + $delivery_fee);

            $_SESSION['cart'] = [];
            $_SESSION['last_order_ref'] = $order_ref;
            flash('success', 'Order placed successfully.');
            redirect('public/order-success.php?ref=' . urlencode($order_ref));
        } catch (Throwable $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $errors['form'] = APP_ENV === 'development' ? $e->getMessage() : 'Could not complete checkout right now.';
        }
    }
}

include INCLUDES . '/header.php';
?>

<section class="section">
  <div class="wrapper checkout-grid">
    <div class="form-card form-card-modern" data-reveal="up">
      <div class="section-heading">
        <p class="eyebrow">Checkout</p>
        <h1>Complete your order</h1>
      </div>

      <?php if ($db_error): ?><div class="inline-error"><?= e($db_error) ?></div><?php endif; ?>
      <?php if (isset($errors['form'])): ?><div class="inline-error"><?= e($errors['form']) ?></div><?php endif; ?>

      <form method="post" action="<?= SITE_URL ?>/public/checkout.php" class="contact-form form-shell" novalidate>
        <?= csrf_field(); ?>
        <div class="form-section">
          <div class="form-section-head">
            <strong>Customer details</strong>
            <span>We use these details for order confirmation and delivery coordination.</span>
          </div>

          <div class="form-grid">
            <div class="field">
              <label for="full_name">Full name</label>
              <input id="full_name" name="full_name" type="text" value="<?= e($form['full_name']) ?>">
              <?php if (isset($errors['full_name'])): ?><p class="field-error"><?= e($errors['full_name']) ?></p><?php endif; ?>
            </div>

            <div class="field">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" value="<?= e($form['email']) ?>">
              <?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>
            </div>

            <div class="field field-wide">
              <label for="phone">Phone</label>
              <input id="phone" name="phone" type="text" value="<?= e($form['phone']) ?>">
              <?php if (isset($errors['phone'])): ?><p class="field-error"><?= e($errors['phone']) ?></p><?php endif; ?>
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="form-section-head">
            <strong>Delivery details</strong>
            <span>Tell us where the order should go and any logistics we should know.</span>
          </div>

          <div class="form-grid">
            <div class="field field-wide">
              <label for="delivery_address">Delivery address</label>
              <textarea id="delivery_address" name="delivery_address" rows="4"><?= e($form['delivery_address']) ?></textarea>
              <?php if (isset($errors['delivery_address'])): ?><p class="field-error"><?= e($errors['delivery_address']) ?></p><?php endif; ?>
            </div>

            <div class="field">
              <label for="city">City</label>
              <input id="city" name="city" type="text" value="<?= e($form['city']) ?>">
            </div>

            <div class="field">
              <label for="country">Country</label>
              <input id="country" name="country" type="text" value="<?= e($form['country']) ?>">
            </div>

            <div class="field field-wide">
              <label for="notes">Notes</label>
              <textarea id="notes" name="notes" rows="3"><?= e($form['notes']) ?></textarea>
              <p class="field-hint">Add landmarks, preferred call times, or delivery instructions.</p>
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="form-section-head">
            <strong>Payment method</strong>
            <span>Select the option that fits how you want to complete the order.</span>
          </div>

          <div class="choice-grid">
            <?php
            $payment_options = [
                'cash_on_delivery' => ['label' => 'Cash on delivery', 'text' => 'Pay when the order arrives.', 'disabled' => false],
                'mobile_money' => ['label' => 'Mobile money', 'text' => 'Not available.', 'disabled' => true],
                'bank_transfer' => ['label' => 'Bank transfer', 'text' => 'Not available.', 'disabled' => true],
            ];
            ?>
            <?php foreach ($payment_options as $value => $option): ?>
              <label class="choice-card<?= $form['payment_method'] === $value ? ' is-selected' : '' ?><?= $option['disabled'] ? ' is-disabled' : '' ?>">
                <input
                  type="radio"
                  name="payment_method"
                  value="<?= e($value) ?>"
                  <?= $form['payment_method'] === $value ? ' checked' : '' ?>
                  <?= $option['disabled'] ? ' disabled' : '' ?>
                >
                <span class="choice-card-copy">
                  <strong><?= e($option['label']) ?></strong>
                  <span><?= e($option['text']) ?></span>
                </span>
                <?php if ($option['disabled']): ?><span class="coming-soon">Not available</span><?php endif; ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="form-submit-row">
          <div class="form-submit-copy">
            <strong>Place order</strong>
            <span>Your order summary will be created and sent into the admin workflow immediately.</span>
          </div>
          <button class="button" type="submit">Place order</button>
        </div>
      </form>
    </div>

    <aside class="hero-panel landing-panel">
      <p class="panel-kicker">Order summary</p>
      <?php if ($cart_items): ?>
        <div class="side-list">
          <?php foreach ($cart_items as $item): ?>
            <div class="side-item">
              <strong><?= e($item['product']['name']) ?> x<?= e((string) $item['qty']) ?></strong>
              <span><?= money((float) $item['line_total']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="detail-price">
          <strong><?= money($subtotal + $delivery_fee) ?></strong>
          <span>Total payable</span>
        </div>
      <?php else: ?>
        <p>Your cart is empty.</p>
      <?php endif; ?>
    </aside>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
