<?php require_once 'views/layout/header.php'; ?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h2 class="m-0">Carrito</h2>

  <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/products">
    <i class="fas fa-store"></i> Seguir comprando
  </a>
</div>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-warning">
    <i class="fas fa-triangle-exclamation"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (empty($cart)): ?>
  <div class="alert alert-info">Tu carrito está vacío.</div>
  <a class="btn btn-primary" href="<?= BASE_URL ?>/products">Ir a productos</a>
<?php else: ?>

<form action="<?= BASE_URL ?>/cart/update" method="POST">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>Producto</th>
          <th class="text-center" style="width:140px;">Cantidad</th>
          <th class="text-end">Precio</th>
          <th class="text-end">Subtotal</th>
          <th style="width:120px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cart as $item): ?>
          <?php $sub = (float)$item['price'] * (int)$item['quantity']; ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-3">
                <img
                  src="<?= ASSETS_URL . '/img/products/' . basename($item['image'] ?? '') ?>"
                  class="cart-img"
                  alt=""
                  onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/img/products/placeholder.png';"
                >
                <div>
                  <div class="fw-semibold"><?= htmlspecialchars($item['name']) ?></div>

                  <?php if (!empty($item['size'])): ?>
                    <div class="text-muted small">Talla: <strong><?= htmlspecialchars($item['size']) ?></strong></div>
                  <?php endif; ?>

                  <?php if (!empty($item['category'])): ?>
                    <div class="text-muted small"><?= htmlspecialchars($item['category']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </td>

            <td class="text-center">
              <input class="form-control text-center" type="number" min="0"
                     name="qty[<?= htmlspecialchars($item['key']) ?>]" value="<?= (int)$item['quantity'] ?>">
              <small class="text-muted">0 elimina</small>
            </td>

            <td class="text-end"><?= number_format((float)$item['price'], 2) ?>€</td>
            <td class="text-end fw-semibold"><?= number_format((float)$sub, 2) ?>€</td>

            <td class="text-end">
              <form action="<?= BASE_URL ?>/cart/remove" method="POST">
                <input type="hidden" name="key" value="<?= htmlspecialchars($item['key']) ?>">
                <button class="btn btn-outline-danger btn-sm">Quitar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="d-flex justify-content-between align-items-center">
    <button class="btn btn-outline-primary" type="submit">Actualizar cantidades</button>
    <div class="h4 m-0">Total: <?= number_format((float)$total, 2) ?>€</div>
  </div>
</form>

<hr class="my-4">

<?php if (!isset($_SESSION['user'])): ?>
  <div class="alert alert-warning">
    Para comprar necesitas iniciar sesión.
    <a href="<?= BASE_URL ?>/login" class="btn btn-warning btn-sm ms-2">Login</a>
  </div>
<?php else: ?>
  <a class="btn btn-success btn-lg" href="<?= BASE_URL ?>/checkout">
    <i class="fas fa-credit-card"></i> Finalizar compra
  </a>
<?php endif; ?>

<?php endif; ?>

<?php require_once 'views/layout/footer.php'; ?>
