<?php require_once 'views/layout/header.php'; ?>

<h2 class="mb-3">Carrito</h2>

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
          <?php $sub = $item['price'] * $item['quantity']; ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-3">
                <img src="<?= htmlspecialchars($item['image']) ?>" class="cart-img" alt="">
                <div>
                  <div class="fw-semibold"><?= htmlspecialchars($item['name']) ?></div>
                </div>
              </div>
            </td>

            <td class="text-center">
              <input class="form-control text-center" type="number" min="0"
                     name="qty[<?= (int)$item['id'] ?>]" value="<?= (int)$item['quantity'] ?>">
              <small class="text-muted">0 elimina</small>
            </td>

            <td class="text-end"><?= number_format((float)$item['price'], 2) ?>€</td>
            <td class="text-end fw-semibold"><?= number_format((float)$sub, 2) ?>€</td>

            <td class="text-end">
              <form action="<?= BASE_URL ?>/cart/remove" method="POST">
                <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
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
  <form action="<?= BASE_URL ?>/checkout" method="POST">
    <button class="btn btn-success btn-lg">
      <i class="fas fa-credit-card"></i> Finalizar compra
    </button>
  </form>
<?php endif; ?>

<?php endif; ?>

<?php require_once 'views/layout/footer.php'; ?>
