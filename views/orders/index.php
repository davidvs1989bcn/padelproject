<?php require_once 'views/layout/header.php'; ?>

<h2 class="mb-3">Mis pedidos</h2>

<?php if (empty($orders)): ?>
  <div class="alert alert-info">Aún no has realizado pedidos.</div>
  <a class="btn btn-primary" href="<?= BASE_URL ?>/products">Comprar ahora</a>
<?php else: ?>

  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Fecha</th>
          <th>Estado</th>
          <th class="text-end">Total</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><?= (int)$o['id'] ?></td>
            <td><?= htmlspecialchars($o['created_at']) ?></td>
            <td><span class="badge bg-success"><?= htmlspecialchars($o['status']) ?></span></td>
            <td class="text-end fw-semibold"><?= number_format((float)$o['total'], 2) ?>€</td>
            <td class="text-end">
              <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>/order/<?= (int)$o['id'] ?>">Ver</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>

<?php require_once 'views/layout/footer.php'; ?>
