<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_products');

$product_id = get_int('id');
$page_title = 'Edit Product';
$errors = [];
$categories = db_all("SELECT id, name FROM product_categories WHERE status = 'active' ORDER BY sort_order ASC, name ASC");
$product = db_one("SELECT * FROM products WHERE id = ? AND deleted_at IS NULL LIMIT 1", [$product_id]);
$primary_image = $product_id > 0
    ? db_one(
        "SELECT id, image_url, alt_text
         FROM product_images
         WHERE product_id = ? AND is_primary = 1
         ORDER BY sort_order ASC, id ASC
         LIMIT 1",
        [$product_id]
    )
    : false;

if (!$product) {
    flash('error', 'Product not found.');
    redirect('public/admin/products.php');
}

$form = [
    'category_id' => (string) $product['category_id'],
    'name' => (string) $product['name'],
    'slug' => (string) $product['slug'],
    'sku' => (string) ($product['sku'] ?? ''),
    'description' => (string) ($product['description'] ?? ''),
    'price' => (string) $product['price'],
    'old_price' => (string) ($product['old_price'] ?? ''),
    'stock_qty' => (string) $product['stock_qty'],
    'condition_type' => (string) $product['condition_type'],
    'brand' => (string) ($product['brand'] ?? ''),
    'model' => (string) ($product['model'] ?? ''),
    'status' => (string) $product['status'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    foreach ($form as $key => $value) {
        $form[$key] = post($key, (string) $value);
    }

    if ($form['category_id'] === '') $errors['category_id'] = 'Choose a category.';
    if ($form['name'] === '') $errors['name'] = 'Enter product name.';
    if ($form['price'] === '' || !is_numeric($form['price'])) $errors['price'] = 'Enter a valid price.';
    if ($form['sku'] !== '' && mb_strlen($form['sku']) < 3) $errors['sku'] = 'SKU should be at least 3 characters if you use one.';
    if ($form['old_price'] !== '' && !is_numeric($form['old_price'])) $errors['old_price'] = 'Enter a valid old price.';
    if (
        $form['old_price'] !== ''
        && is_numeric($form['old_price'])
        && is_numeric($form['price'])
        && (float) $form['old_price'] < (float) $form['price']
    ) {
        $errors['old_price'] = 'Old price must be greater than or equal to the current price.';
    }
    if ($form['slug'] === '') $form['slug'] = make_slug($form['name']);

    if (
        !isset($errors['slug'])
        && db_one("SELECT id FROM products WHERE slug = ? AND id <> ? LIMIT 1", [$form['slug'], $product_id])
    ) {
        $errors['slug'] = 'This slug is already in use. Change it slightly.';
    }

    if (
        $form['sku'] !== ''
        && !isset($errors['sku'])
        && db_one("SELECT id FROM products WHERE sku = ? AND id <> ? LIMIT 1", [$form['sku'], $product_id])
    ) {
        $errors['sku'] = 'This SKU already exists. Use a unique code.';
    }

    if (!$errors) {
        try {
            db_run(
                "UPDATE products
                 SET category_id = ?, updated_by = ?, name = ?, slug = ?, sku = ?, description = ?, price = ?, old_price = ?, stock_qty = ?, condition_type = ?, brand = ?, model = ?, status = ?, updated_at = NOW()
                 WHERE id = ?",
                [
                    (int) $form['category_id'],
                    (int) $_SESSION['staff_id'],
                    $form['name'],
                    $form['slug'],
                    $form['sku'] !== '' ? $form['sku'] : null,
                    $form['description'] !== '' ? $form['description'] : null,
                    (float) $form['price'],
                    $form['old_price'] !== '' ? (float) $form['old_price'] : null,
                    (int) $form['stock_qty'],
                    $form['condition_type'],
                    $form['brand'] !== '' ? $form['brand'] : null,
                    $form['model'] !== '' ? $form['model'] : null,
                    $form['status'],
                    $product_id,
                ]
            );
        } catch (Throwable $e) {
            $message = APP_ENV === 'development' ? $e->getMessage() : '';

            if (str_contains($message, "Duplicate entry") && str_contains($message, "sku")) {
                $errors['sku'] = 'This SKU already exists. Use a unique code.';
            } elseif (str_contains($message, "Duplicate entry") && str_contains($message, "slug")) {
                $errors['slug'] = 'This slug is already in use. Change it slightly.';
            } else {
                $errors['form'] = 'The product could not be updated right now.';
            }
        }

        if (!$errors && isset($_POST['remove_primary_image']) && $_POST['remove_primary_image'] === '1') {
            db_run("DELETE FROM product_images WHERE product_id = ? AND is_primary = 1", [$product_id]);
            $primary_image = false;
        }

        if (!$errors && isset($_FILES['primary_image']) && $_FILES['primary_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $stored_path = upload_image($_FILES['primary_image'], 'products');

            if ($stored_path === false) {
                $errors['primary_image'] = 'Image upload failed. Use JPG, PNG, or WebP under 5MB.';
            } else {
                db_run("UPDATE product_images SET is_primary = 0 WHERE product_id = ?", [$product_id]);
                db_insert(
                    "INSERT INTO product_images (product_id, image_url, alt_text, is_primary, sort_order)
                     VALUES (?, ?, ?, 1, 0)",
                    [
                        $product_id,
                        UPLOAD_URL . '/' . $stored_path,
                        $form['name'] !== '' ? $form['name'] : null,
                    ]
                );
                $primary_image = db_one(
                    "SELECT id, image_url, alt_text
                     FROM product_images
                     WHERE product_id = ? AND is_primary = 1
                     ORDER BY sort_order ASC, id ASC
                     LIMIT 1",
                    [$product_id]
                );
            }
        }

        if (!$errors) {
            flash('success', 'Product updated.');
            redirect('public/admin/products.php');
        }
    }
}

include INCLUDES . '/admin_header.php';
?>

<div class="section-heading">
  <p class="eyebrow">Catalog</p>
  <h2>Edit product</h2>
</div>

<div class="form-card">
  <?php if ($errors): ?>
    <div class="inline-error">Please correct the highlighted product details and try again.</div>
  <?php endif; ?>
  <?php if (isset($errors['form'])): ?>
    <div class="inline-error"><?= e($errors['form']) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= SITE_URL ?>/public/admin/product-edit.php?id=<?= e((string) $product_id) ?>" class="contact-form" enctype="multipart/form-data">
    <?= csrf_field(); ?>

    <label for="category_id">Category</label>
    <select id="category_id" name="category_id">
      <?php foreach ($categories as $category): ?>
        <option value="<?= e((string) $category['id']) ?>"<?= $form['category_id'] === (string) $category['id'] ? ' selected' : '' ?>>
          <?= e($category['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <?php if (isset($errors['category_id'])): ?><p class="field-error"><?= e($errors['category_id']) ?></p><?php endif; ?>

    <label for="name">Name</label>
    <input id="name" name="name" type="text" value="<?= e($form['name']) ?>">
    <?php if (isset($errors['name'])): ?><p class="field-error"><?= e($errors['name']) ?></p><?php endif; ?>

    <label for="slug">Slug</label>
    <input id="slug" name="slug" type="text" value="<?= e($form['slug']) ?>">
    <?php if (isset($errors['slug'])): ?><p class="field-error"><?= e($errors['slug']) ?></p><?php endif; ?>

    <label for="sku">SKU</label>
    <input id="sku" name="sku" type="text" value="<?= e($form['sku']) ?>">
    <?php if (isset($errors['sku'])): ?><p class="field-error"><?= e($errors['sku']) ?></p><?php endif; ?>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="4"><?= e($form['description']) ?></textarea>

    <label for="price">Price</label>
    <input id="price" name="price" type="text" value="<?= e($form['price']) ?>">
    <?php if (isset($errors['price'])): ?><p class="field-error"><?= e($errors['price']) ?></p><?php endif; ?>

    <label for="old_price">Old price</label>
    <input id="old_price" name="old_price" type="text" value="<?= e($form['old_price']) ?>">
    <?php if (isset($errors['old_price'])): ?><p class="field-error"><?= e($errors['old_price']) ?></p><?php endif; ?>

    <label for="stock_qty">Stock quantity</label>
    <input id="stock_qty" name="stock_qty" type="number" min="0" value="<?= e($form['stock_qty']) ?>">

    <label for="condition_type">Condition</label>
    <select id="condition_type" name="condition_type">
      <?php foreach (['new', 'used', 'refurbished'] as $condition): ?>
        <option value="<?= e($condition) ?>"<?= $form['condition_type'] === $condition ? ' selected' : '' ?>><?= e(ucfirst($condition)) ?></option>
      <?php endforeach; ?>
    </select>

    <label for="brand">Brand</label>
    <input id="brand" name="brand" type="text" value="<?= e($form['brand']) ?>">

    <label for="model">Model</label>
    <input id="model" name="model" type="text" value="<?= e($form['model']) ?>">

    <label for="status">Status</label>
    <select id="status" name="status">
      <?php foreach (['draft', 'active', 'inactive', 'out_of_stock', 'archived'] as $status): ?>
        <option value="<?= e($status) ?>"<?= $form['status'] === $status ? ' selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $status))) ?></option>
      <?php endforeach; ?>
    </select>

    <div class="image-field">
      <label for="primary_image">Primary image</label>
      <?php if ($primary_image): ?>
        <div class="admin-image-preview">
          <img src="<?= e($primary_image['image_url']) ?>" alt="<?= e($primary_image['alt_text'] ?? $form['name']) ?>">
        </div>
        <label class="checkbox-row">
          <input type="checkbox" name="remove_primary_image" value="1">
          <span>Remove current primary image</span>
        </label>
      <?php endif; ?>

      <input id="primary_image" name="primary_image" type="file" accept="image/jpeg,image/png,image/webp">
      <p class="field-hint">Upload a new JPG, PNG, or WebP image up to 5MB. A new upload becomes the primary product image.</p>
      <?php if (isset($errors['primary_image'])): ?><p class="field-error"><?= e($errors['primary_image']) ?></p><?php endif; ?>
    </div>

    <button class="button" type="submit">Save product</button>
  </form>
</div>

<?php include INCLUDES . '/admin_footer.php'; ?>
