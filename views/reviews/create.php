<?php require_once 'views/layout/header.php'; ?>

<?php
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

$productId   = (int)($product['id'] ?? 0);
$productName = (string)($product['name'] ?? 'Producto');
$orderId     = (int)($orderId ?? 0);

$img = (string)($product['image'] ?? '');
$imgSrc = $img
    ? (ASSETS_URL . '/img/products/' . basename($img))
    : (ASSETS_URL . '/img/products/placeholder.png');

$redirect = trim((string)($_GET['redirect'] ?? 'product'));
$redirect = ($redirect === 'order') ? 'order' : 'product';
?>

<div class="container py-4" style="max-width: 820px;">
  <h2 class="mb-3">Escribir reseña</h2>

  <?php if (!empty($flashError)): ?>
    <div class="alert alert-warning">
      <i class="fas fa-triangle-exclamation"></i>
      <?= htmlspecialchars($flashError) ?>
    </div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-body">

      <div class="d-flex gap-3 align-items-center mb-3">
        <div class="border rounded bg-white d-flex align-items-center justify-content-center"
             style="width:90px;height:90px;">
          <img
            src="<?= $imgSrc ?>"
            alt="<?= htmlspecialchars($productName) ?>"
            style="width:80px;height:80px;object-fit:contain;"
            onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/img/products/placeholder.png';"
          >
        </div>

        <div>
          <div class="fw-semibold"><?= htmlspecialchars($productName) ?></div>
          <div class="small text-muted">Comparte tu experiencia con este producto</div>
        </div>
      </div>

      <form action="<?= BASE_URL ?>/review" method="POST">

        <!-- IDs necesarios -->
        <input type="hidden" name="product_id" value="<?= $productId ?>">
        <input type="hidden" name="order_id" value="<?= $orderId ?>">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

        <!-- PUNTUACIÓN -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Puntuación</label>

          <div class="d-flex gap-2 flex-wrap">
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <input
                class="btn-check"
                type="radio"
                name="rating"
                id="star<?= $i ?>"
                value="<?= $i ?>"
                required
              >
              <label class="btn btn-outline-warning" for="star<?= $i ?>">
                <?= $i ?> <i class="fas fa-star"></i>
              </label>
            <?php endfor; ?>
          </div>

          <div class="form-text">Elige de 1 a 5 estrellas.</div>
        </div>

        <!-- TÍTULO -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Título (opcional)</label>
          <input
            class="form-control"
            type="text"
            name="title"
            maxlength="120"
            placeholder="Ej: Muy buena calidad"
          >
        </div>

        <!-- COMENTARIO -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Comentario</label>
          <textarea
            class="form-control"
            name="body"
            rows="4"
            required
            placeholder="Cuéntanos qué te ha parecido el producto..."
          ></textarea>
          <div class="form-text">Este campo es obligatorio.</div>
        </div>

        <!-- BOTONES -->
        <div class="d-flex flex-wrap gap-2">
          <button class="btn btn-warning" type="submit">
            <i class="fas fa-paper-plane"></i> Publicar reseña
          </button>

          <a class="btn btn-outline-secondary"
             href="<?= BASE_URL ?>/product/<?= $productId ?>">
            Cancelar
          </a>
        </div>

      </form>

    </div>
  </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>
