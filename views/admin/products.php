<?php require_once 'views/layout/header.php'; ?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="m-0">Admin - Productos</h2>
    <a class="btn btn-success" href="<?= BASE_URL ?>/admin/product/create">+ Añadir producto</a>
  </div>

  <?php if (empty($products)): ?>
    <div class="alert alert-info">No hay productos.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Imagen</th>
            <th>Nombre</th>
            <th>Categoría</th>
            <th class="text-end">Precio</th>
            <th class="text-end">Stock</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td><?= (int)$p['id'] ?></td>
              <td style="width:90px;">
                <img
                  src="<?= ASSETS_URL . '/img/products/' . basename($p['image']) ?>"
                  style="width:70px;height:70px;object-fit:contain;background:#fff;"
                  alt="<?= htmlspecialchars($p['name']) ?>"
                >
              </td>
              <td><?= htmlspecialchars($p['name']) ?></td>
              <td><?= htmlspecialchars($p['category'] ?? '') ?></td>
              <td class="text-end"><?= number_format((float)$p['price'], 2) ?> €</td>
              <td class="text-end"><?= (int)($p['stock'] ?? 0) ?></td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary"
                   href="<?= BASE_URL ?>/admin/product/edit/<?= (int)$p['id'] ?>">Editar</a>

                <form method="POST" action="<?= BASE_URL ?>/admin/product/delete" class="d-inline"
                      onsubmit="return confirm('¿Eliminar este producto?');">
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <div class="mt-3">
    <a class="btn btn-secondary" href="<?= BASE_URL ?>/admin">Volver al panel</a>
  </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>
