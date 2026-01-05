<?php require_once 'views/layout/header.php'; ?>

<?php
$category = mb_strtolower(trim($product['category'] ?? ''), 'UTF-8');

$isClothing = ($category === 'ropa');
$isShoes = ($category === 'zapatillas');

// ✅ Calcetines -> talla de zapatillas
$nameLower = mb_strtolower($product['name'] ?? '', 'UTF-8');
$isSocks = (strpos($nameLower, 'calcet') !== false);

$hasSizes = ($isClothing || $isShoes || $isSocks);

$clothingSizes = ['S','M','L','XL','XXL'];
$shoeSizes = ['37.5','38','39','40','41','42','43','44','45','46'];

// Mensaje flash (lo setea el CartController)
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

// ✅ Stock
$stockGeneral = (int)($product['stock'] ?? 0);

// $sizeStocks viene del ProductController::show()
// Si no existe, por seguridad lo dejamos en array vacío.
$sizeStocks = isset($sizeStocks) && is_array($sizeStocks) ? $sizeStocks : [];

// total stock por tallas (solo si hay tallas)
$sizeTotalStock = 0;
if ($hasSizes) {
  foreach ($sizeStocks as $st) $sizeTotalStock += (int)$st;
}

// Sin stock?
$outOfStock = $hasSizes ? ($sizeTotalStock <= 0) : ($stockGeneral <= 0);
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
    <h2 class="mb-1"><?= htmlspecialchars($product['name']) ?></h2>

    <p class="text-muted mb-2">
      <?= htmlspecialchars($product['brand'] ?? '') ?>
      <?php if (!empty($product['brand'])): ?> · <?php endif; ?>
      <?= htmlspecialchars($product['category'] ?? '') ?>
    </p>

    <p class="h3 text-danger fw-bold mb-2"><?= number_format((float)$product['price'], 2) ?>€</p>

    <!-- ✅ Indicador de stock -->
    <?php if (!$hasSizes): ?>
      <?php if ($stockGeneral <= 0): ?>
        <span class="badge bg-danger mb-2"><i class="fas fa-box-open"></i> Sin stock</span>
      <?php else: ?>
        <span class="badge bg-light text-dark border mb-2">
          <i class="fas fa-box"></i> Stock disponible: <strong><?= (int)$stockGeneral ?></strong>
        </span>
      <?php endif; ?>
    <?php else: ?>
      <?php if ($outOfStock): ?>
        <span class="badge bg-danger mb-2"><i class="fas fa-box-open"></i> Sin stock</span>
      <?php else: ?>
        <span class="badge bg-light text-dark border mb-2">
          <i class="fas fa-boxes-stacked"></i> Stock total (tallas): <strong><?= (int)$sizeTotalStock ?></strong>
        </span>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
      <div class="alert alert-warning mt-2">
        <i class="fas fa-triangle-exclamation"></i> <?= htmlspecialchars($flashError) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($product['short_description'])): ?>
      <p class="mt-2 text-muted"><?= htmlspecialchars($product['short_description']) ?></p>
    <?php endif; ?>

    <p class="mt-3"><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></p>

    <form action="<?= BASE_URL ?>/cart/add" method="POST" class="mt-4">
      <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">

      <?php if ($isClothing): ?>
        <div class="mb-3" style="max-width: 260px;">
          <label class="form-label fw-semibold">Talla (Ropa)</label>

          <select class="form-select" name="size" required <?= $outOfStock ? 'disabled' : '' ?>>
            <option value="">Selecciona talla</option>
            <?php foreach ($clothingSizes as $s): ?>
              <?php
                $st = (int)($sizeStocks[$s] ?? 0);
                $disabled = ($st <= 0) ? 'disabled' : '';
              ?>
              <option value="<?= htmlspecialchars($s) ?>" <?= $disabled ?>>
                <?= htmlspecialchars($s) ?><?= $st <= 0 ? ' (agotada)' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>

          <small class="text-muted">De S a XXL</small>
        </div>

      <?php elseif ($isShoes || $isSocks): ?>
        <div class="mb-3" style="max-width: 260px;">
          <label class="form-label fw-semibold">Talla (<?= $isSocks ? 'Calcetines' : 'Zapatillas' ?>)</label>

          <select class="form-select" name="size" required <?= $outOfStock ? 'disabled' : '' ?>>
            <option value="">Selecciona talla</option>
            <?php foreach ($shoeSizes as $s): ?>
              <?php
                $st = (int)($sizeStocks[$s] ?? 0);
                $disabled = ($st <= 0) ? 'disabled' : '';
              ?>
              <option value="<?= htmlspecialchars($s) ?>" <?= $disabled ?>>
                <?= htmlspecialchars($s) ?><?= $st <= 0 ? ' (agotada)' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>

          <small class="text-muted">De 37.5 a 46</small>
        </div>
      <?php endif; ?>

      <div class="d-flex flex-wrap gap-2">
        <?php if ($outOfStock): ?>
          <button class="btn btn-secondary btn-lg" type="button" disabled>
            <i class="fas fa-ban"></i> Sin stock
          </button>
        <?php else: ?>
          <button class="btn btn-warning btn-lg" type="submit">
            <i class="fas fa-cart-plus"></i> Añadir al carrito
          </button>
        <?php endif; ?>

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
