<?php require_once 'views/layout/header.php'; ?>

<?php
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

$flashSuccess = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

$img = $product['image'] ?? '';
$imgSrc = $img ? (ASSETS_URL . '/img/products/' . basename($img)) : (ASSETS_URL . '/img/products/placeholder.png');

$valRating = (int)($existing['rating'] ?? 0);
$valTitle  = (string)($existing['title'] ?? '');
$valBody   = (string)($existing['body'] ?? '');

$productId = (int)($productId ?? ($product['id'] ?? 0));
$orderId = (int)($orderId ?? 0);
$orderItemId = (int)($orderItemId ?? 0);

$redirect = trim((string)($redirect ?? ($_GET['redirect'] ?? 'product')));
$redirect = ($redirect === 'order') ? 'order' : 'product';

function starRow(int $selected): string {
  $html = '';
  for ($i = 5; $i >= 1; $i--) {
    $checked = ($selected === $i) ? 'checked' : '';
    $html .= '
      <input type="radio" id="star'.$i.'" name="rating" value="'.$i.'" '.$checked.' required>
      <label for="star'.$i.'" title="'.$i.' estrellas">★</label>
    ';
  }
  return $html;
}
?>

<style>
.pp-stars {
  display: inline-flex;
  flex-direction: row-reverse;
  gap: 6px;
}
.pp-stars input { display:none; }
.pp-stars label {
  font-size: 32px;
  line-height: 1;
  cursor: pointer;
  color: #d1d5db;
  user-select: none;
}
.pp-stars input:checked ~ label { color: #f59e0b; }
.pp-stars label:hover,
.pp-stars label:hover ~ label { color: #fbbf24; }
</style>

<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="m-0"><?= !empty($existing) ? 'Editar reseña' : 'Escribir reseña' ?></h2>

    <?php if ($redirect === 'order' && $orderId > 0): ?>
      <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/order/<?= (int)$orderId ?>">
        <i class="fas fa-arrow-left"></i> Volver al pedido
      </a>
    <?php else: ?>
      <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/product/<?= (int)$productId ?>">
        <i class="fas fa-arrow-left"></i> Volver al producto
      </a>
    <?php endif; ?>
  </div>

  <?php if (!empty($flashError)): ?>
    <div class="alert alert-warning">
      <i class="fas fa-triangle-exclamation"></i> <?= htmlspecialchars($flashError) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success">
      <i class="fas fa-check"></i> <?= htmlspecialchars($flashSuccess) ?>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm border-0">
    <div class="card-body">
      <div class="row g-4 align-items-start">
        <div class="col-md-4">
          <div class="border rounded bg-white d-flex align-items-center justify-content-center" style="width:100%;height:240px;">
            <img src="<?= $imgSrc ?>"
                 alt="<?= htmlspecialchars($product['name'] ?? 'Producto') ?>"
                 style="max-width:100%;max-height:220px;object-fit:contain;"
                 onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/img/products/placeholder.png';">
          </div>
          <div class="mt-2 fw-semibold"><?= htmlspecialchars($product['name'] ?? '') ?></div>
          <div class="text-muted small">
            <?= htmlspecialchars($product['brand_name'] ?? ($product['brand'] ?? '')) ?>
            <?php if (!empty($product['category'])): ?> • <?= htmlspecialchars($product['category']) ?><?php endif; ?>
          </div>
        </div>

        <div class="col-md-8">
          <form method="POST" action="<?= BASE_URL ?>/review">
            <input type="hidden" name="product_id" value="<?= (int)$productId ?>">
            <input type="hidden" name="order_id" value="<?= (int)$orderId ?>">
            <input type="hidden" name="order_item_id" value="<?= (int)$orderItemId ?>">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

            <div class="mb-3">
              <label class="form-label fw-semibold">Tu valoración</label><br>
              <div class="pp-stars" aria-label="Selecciona estrellas">
                <?= starRow($valRating) ?>
              </div>
              <div class="text-muted small mt-1">Selecciona de 1 a 5 estrellas.</div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Título</label>
              <input class="form-control" type="text" name="title" maxlength="120"
                     value="<?= htmlspecialchars($valTitle) ?>" placeholder="Resumen de tu experiencia">
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Comentario</label>
              <textarea class="form-control" name="body" rows="5" placeholder="Cuéntanos qué te ha parecido..."><?= htmlspecialchars($valBody) ?></textarea>
            </div>

            <button class="btn btn-warning" type="submit">
              <i class="fas fa-star"></i> Guardar reseña
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>
