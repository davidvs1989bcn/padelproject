<?php require_once 'views/layout/header.php'; ?>

<h2 class="mb-3">Checkout</h2>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card shadow-sm border-0 mb-3">
      <div class="card-body">
        <h5 class="mb-2">Resumen de compra</h5>
        <div class="small text-muted">Revisa los artículos antes de confirmar.</div>

        <hr>

        <?php foreach ($cart as $item): ?>
          <div class="d-flex gap-3 align-items-center py-2 border-bottom">
            <img
              src="<?= ASSETS_URL . '/img/products/' . basename($item['image'] ?? '') ?>"
              alt=""
              style="width:70px;height:70px;object-fit:contain;background:#fff;border:1px solid #eee;border-radius:10px;padding:6px;"
              onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/img/products/placeholder.png';"
            >

            <div class="flex-grow-1">
              <div class="fw-semibold"><?= htmlspecialchars($item['name']) ?></div>
              <div class="small text-muted">
                <?= htmlspecialchars($item['category'] ?? '') ?>
                <?php if (!empty($item['size'])): ?>
                  • Talla: <strong><?= htmlspecialchars($item['size']) ?></strong>
                <?php endif; ?>
              </div>
              <div class="small text-muted">
                Cantidad: <?= (int)$item['quantity'] ?>
              </div>
            </div>

            <div class="text-end">
              <div class="fw-semibold"><?= number_format((float)$item['price'], 2) ?> €</div>
              <div class="small text-muted">
                Subtotal: <?= number_format((float)$item['price'] * (int)$item['quantity'], 2) ?> €
              </div>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="text-muted">Total</div>
          <div class="fs-4 fw-bold"><?= number_format((float)$total, 2) ?> €</div>
        </div>
      </div>
    </div>

    <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/cart">
      <i class="fas fa-arrow-left"></i> Volver al carrito
    </a>
  </div>

  <div class="col-lg-5">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5 class="mb-3">Confirmación</h5>

        <div class="mb-3">
          <div class="small text-muted">Envío</div>
          <div class="fw-semibold">24/48h (estándar)</div>
        </div>

        <div class="mb-3">
          <div class="small text-muted">Pago</div>
          <div class="fw-semibold">Tarjeta (simulado)</div>
        </div>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/checkout">
          <button class="btn btn-success btn-lg w-100">
            <i class="fas fa-lock"></i> Confirmar compra
          </button>
        </form>

        <div class="small text-muted mt-2">
          Al confirmar, se generará el pedido y se vaciará el carrito.
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>
