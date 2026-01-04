<?php require_once 'views/layout/header.php'; ?>

<?php
$category = mb_strtolower(trim($product['category'] ?? ''), 'UTF-8');

$isClothing = ($category === 'ropa');
$isShoes = ($category === 'zapatillas');

$clothingSizes = ['S','M','L','XL','XXL'];
$shoeSizes = ['37.5','38','39','40','41','42','43','44','45','46'];
?>

<div class="row g-4">
  <div class="col-md-5">
    <div class="card shadow-sm p-3">
      <img
        src="<?= ASSETS_URL . '/img/products/' . basename($product['image'] ?? '') ?>"
        class="img-fluid"
        alt="<?= htmlspecialchars($product['name'] ?? 'Producto') ?>"
        onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/img/products/placeholder.png';"
      >
    </div>
  </div>

  <div class="col-md-7">
    <h2><?= htmlspecialchars($product['name']) ?></h2>
    <p class="text-muted">
      <?= htmlspecialchars($product['brand'] ?? '') ?>
      <?php if (!empty($product['brand'])): ?> · <?php endif; ?>
      <?= htmlspecialchars($product['category'] ?? '') ?>
    </p>

    <p class="h3 text-danger fw-bold"><?= number_format((float)$product['price'], 2) ?>€</p>

    <?php if (!empty($product['short_description'])): ?>
      <p class="mt-2 text-muted"><?= htmlspecialchars($product['short_description']) ?></p>
    <?php endif; ?>

    <p class="mt-3"><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></p>

    <form action="<?= BASE_URL ?>/cart/add" method="POST" class="mt-4">
      <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">

      <?php if ($isClothing): ?>
        <div class="mb-3" style="max-width: 240px;">
          <label class="form-label fw-semibold">Talla (Ropa)</label>
          <select class="form-select" name="size" required>
            <option value="">Selecciona talla</option>
            <?php foreach ($clothingSizes as $s): ?>
              <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="text-muted">De S a XXL</small>
        </div>
      <?php elseif ($isShoes): ?>
        <div class="mb-3" style="max-width: 240px;">
          <label class="form-label fw-semibold">Talla (Zapatillas)</label>
          <select class="form-select" name="size" required>
            <option value="">Selecciona talla</option>
            <?php foreach ($shoeSizes as $s): ?>
              <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="text-muted">De 37.5 a 46</small>
        </div>
      <?php endif; ?>

      <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-warning btn-lg">
          <i class="fas fa-cart-plus"></i> Añadir al carrito
        </button>
        <a class="btn btn-outline-secondary btn-lg" href="<?= BASE_URL ?>/products">Volver</a>
      </div>
    </form>

    <div class="mt-3 text-muted small">
      <i class="fas fa-truck"></i> Envío 24/48h ·
      <i class="fas fa-rotate-left"></i> Devolución 14 días
    </div>
  </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>
