<?php require_once 'views/layout/header.php'; ?>

<div class="row g-4">
  <div class="col-md-5">
    <div class="card shadow-sm">
   <img
  src="<?= ASSETS_URL . '/img/products/' . basename($product['image'] ?? $p['image'] ?? '') ?>"
  class="img-fluid"
  alt="<?= htmlspecialchars($product['name'] ?? $p['name'] ?? 'Producto') ?>"
>

    </div>
  </div>

  <div class="col-md-7">
    <h2><?= htmlspecialchars($product['name']) ?></h2>
    <p class="text-muted"><?= htmlspecialchars($product['brand'] ?? '') ?> · <?= htmlspecialchars($product['category'] ?? '') ?></p>

    <p class="h3 text-danger fw-bold"><?= number_format((float)$product['price'], 2) ?>€</p>
    <p class="mt-3"><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></p>

    <form action="<?= BASE_URL ?>/cart/add" method="POST" class="mt-4">
      <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
      <button class="btn btn-warning btn-lg">
        <i class="fas fa-cart-plus"></i> Añadir al carrito
      </button>
      <a class="btn btn-outline-secondary btn-lg" href="<?= BASE_URL ?>/products">Volver</a>
    </form>
  </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>
