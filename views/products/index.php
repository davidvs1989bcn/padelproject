<?php require_once 'views/layout/header.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h2 class="m-0"><?= htmlspecialchars($title ?? 'Catálogo Completo') ?></h2>

  <?php if (!empty($_GET['section']) || !empty($_GET['q'])): ?>
    <a class="btn btn-outline-secondary btn-sm" href="<?= BASE_URL ?>/products">
      Quitar filtros
    </a>
  <?php endif; ?>
</div>

<?php if (empty($products)): ?>
  <div class="alert alert-info">
    No se encontraron productos con esos filtros.
  </div>
<?php else: ?>
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

          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>

            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="price"><?= number_format((float)$p['price'], 2) ?>€</div>
              <span class="badge text-bg-light border">
                <?= htmlspecialchars($p['category'] ?? '') ?>
              </span>
            </div>

            <p class="text-muted mb-3">
              <?php
                $txt = trim(($p['short_description'] ?? '') . ' ' . ($p['description'] ?? ''));
                $txt = preg_replace('/\s+/', ' ', $txt);
                echo htmlspecialchars(mb_strimwidth($txt, 0, 120, '...', 'UTF-8'));
              ?>
            </p>

            <form action="<?= BASE_URL ?>/cart/add" method="POST" class="mt-auto">
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
<?php endif; ?>

<?php require_once 'views/layout/footer.php'; ?>
