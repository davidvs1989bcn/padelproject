<?php require_once 'views/layout/header.php'; ?>

<div class="container py-5" style="max-width: 900px;">
  <h2 class="mb-3">Editar producto #<?= (int)$product['id'] ?></h2>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form id="adminEditForm" method="POST" action="<?= BASE_URL ?>/admin/products/edit/<?= (int)$product['id'] ?>"
        class="card p-4 shadow-sm needs-validation" novalidate>

    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Nombre *</label>
        <input class="form-control" type="text" name="name" id="pName" required minlength="3"
               value="<?= htmlspecialchars($product['name']) ?>">
        <div class="invalid-feedback">El nombre es obligatorio (mínimo 3 caracteres).</div>
      </div>

      <div class="col-md-4">
        <label class="form-label">Marca</label>
        <input class="form-control" type="text" name="brand"
               value="<?= htmlspecialchars($product['brand'] ?? '') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Categoría</label>
        <input class="form-control" type="text" name="category"
               value="<?= htmlspecialchars($product['category'] ?? '') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Precio (€) *</label>
        <input class="form-control" type="number" step="0.01" min="0" name="price" id="pPrice" required
               value="<?= htmlspecialchars($product['price']) ?>">
        <div class="invalid-feedback">Introduce un precio válido (0 o mayor).</div>
      </div>

      <div class="col-md-4">
        <label class="form-label">Stock</label>
        <input class="form-control" type="number" min="0" step="1" name="stock" id="pStock"
               value="<?= htmlspecialchars($product['stock'] ?? 0) ?>">
        <div class="invalid-feedback">El stock debe ser un número entero (0 o mayor).</div>
      </div>

      <div class="col-12">
        <label class="form-label">Imagen (ruta) *</label>
        <input class="form-control" type="text" name="image" id="pImage" required
               value="<?= htmlspecialchars($product['image']) ?>">
        <div class="invalid-feedback">
          La imagen es obligatoria y debe empezar por <code>/public/img/products/</code>
        </div>
        <small class="text-muted">Se mostrará en catálogo y detalle.</small>
      </div>

      <div class="col-12">
        <label class="form-label">Vista previa</label>
        <div class="border rounded p-3 bg-white" style="max-width:220px;">
          <img
            src="<?= ASSETS_URL . '/img/products/' . basename($product['image']) ?>"
            style="width:100%;height:180px;object-fit:contain;background:#fff;"
            alt="<?= htmlspecialchars($product['name']) ?>"
          >
        </div>
      </div>

      <div class="col-12">
        <label class="form-label">Descripción corta</label>
        <input class="form-control" type="text" name="short_description"
               value="<?= htmlspecialchars($product['short_description'] ?? '') ?>">
      </div>

      <div class="col-12">
        <label class="form-label">Descripción completa</label>
        <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button class="btn btn-primary">Guardar cambios</button>
      <a class="btn btn-secondary" href="<?= BASE_URL ?>/admin/products">Cancelar</a>
    </div>
  </form>
</div>

<script>
(() => {
  const form = document.getElementById('adminEditForm');
  const image = document.getElementById('pImage');
  const stock = document.getElementById('pStock');

  function validateImagePath() {
    const v = (image.value || '').trim();
    if (v === '' || !v.startsWith('/public/img/products/')) {
      image.setCustomValidity('bad');
    } else {
      image.setCustomValidity('');
    }
  }

  function validateStockInteger() {
    const v = (stock.value || '').trim();
    if (v === '') {
      stock.setCustomValidity('');
      return;
    }
    const n = Number(v);
    if (!Number.isInteger(n) || n < 0) {
      stock.setCustomValidity('bad');
    } else {
      stock.setCustomValidity('');
    }
  }

  image.addEventListener('input', validateImagePath);
  stock.addEventListener('input', validateStockInteger);

  form.addEventListener('submit', (e) => {
    validateImagePath();
    validateStockInteger();

    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add('was-validated');
  });
})();
</script>

<?php require_once 'views/layout/footer.php'; ?>
