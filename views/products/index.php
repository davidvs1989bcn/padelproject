<?php require_once 'views/layout/header.php'; ?>

<h2 class="mb-4">Catálogo Completo</h2>

<div class="row row-cols-1 row-cols-md-3 g-4">
  <?php foreach($products as $p): ?>
    <div class="col">
      <div class="card h-100 border-0 product-card">
        <a href="<?= BASE_URL ?>/product/<?= (int)$p['id'] ?>" class="text-decoration-none">
          <div class="img-wrap">
            <img
              src="<?= ASSETS_URL . '/img/products/' . basename($p['image']) ?>"
              alt="<?= htmlspecialchars($p['name']) ?>"
              loading="lazy"
              onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/img/products/placeholder.png';"
            >
          </div>
        </a>

        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
          <div class="price"><?= number_format((float)$p['price'], 2) ?>€</div>
          <p class="text-muted mb-3"><?= htmlspecialchars($p['short_description'] ?? '') ?></p>

          <form action="<?= BASE_URL ?>/cart/add" method="POST">
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <button class="btn btn-warning w-100">
              <i class="fas fa-cart-plus"></i> Añadir
            </button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php require_once 'views/layout/footer.php'; ?>
