<?php require_once 'views/layout/header.php'; ?>

<h2 class="mb-3">Pedido #<?= (int)$order['id'] ?></h2>

<div class="card p-3 shadow-sm mb-4">
  <div class="row">
    <div class="col-md-4"><strong>Fecha:</strong> <?= htmlspecialchars($order['created_at']) ?></div>
    <div class="col-md-4"><strong>Estado:</strong> <?= htmlspecialchars($order['status']) ?></div>
    <div class="col-md-4 text-md-end"><strong>Total:</strong> <?= number_format((float)$order['total'], 2) ?>€</div>
  </div>
</div>

<div class="table-responsive">
  <table class="table align-middle">
    <thead>
      <tr>
        <th>Producto</th>
        <th class="text-end">Precio</th>
        <th class="text-center">Cantidad</th>
        <th class="text-end">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?= htmlspecialchars($it['product_name']) ?></td>
          <td class="text-end"><?= number_format((float)$it['unit_price'], 2) ?>€</td>
          <td class="text-center"><?= (int)$it['quantity'] ?></td>
          <td class="text-end fw-semibold"><?= number_format((float)$it['subtotal'], 2) ?>€</td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/orders">Volver</a>

<?php require_once 'views/layout/footer.php'; ?>
