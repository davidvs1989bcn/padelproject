<?php require_once 'views/layout/header.php'; ?>

<?php
$brandsList = $brandsList ?? [];      // [ ['id'=>1,'name'=>'Adidas'], ... ]
$selectedBrands = $selectedBrands ?? [];
if (!is_array($selectedBrands)) $selectedBrands = [];

$minPrice = $minPrice ?? ($_GET['min_price'] ?? '');
$maxPrice = $maxPrice ?? ($_GET['max_price'] ?? '');
$sort = $sort ?? ($_GET['sort'] ?? '');

$section = trim($_GET['section'] ?? '');
$q = trim($_GET['q'] ?? '');

$hasFilters =
  ($section !== '') ||
  ($q !== '') ||
  (!empty($selectedBrands)) ||
  (trim((string)$minPrice) !== '' || trim((string)$maxPrice) !== '') ||
  ($sort !== '');
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h2 class="m-0"><?= htmlspecialchars($title ?? 'Catálogo Completo') ?></h2>

  <?php if ($hasFilters): ?>
    <a class="btn btn-outline-secondary btn-sm" href="<?= BASE_URL ?>/products">
      Quitar filtros
    </a>
  <?php endif; ?>
</div>

<div class="row g-4">
  <!-- FILTROS -->
  <div class="col-12 col-lg-3">
    <div class="card border-0 shadow-sm filters-card sticky-lg-top" style="top: 16px;">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h5 class="m-0">Filtros</h5>
          <?php if ($hasFilters): ?>
            <a class="small text-decoration-none" href="<?= BASE_URL ?>/products">Reset</a>
          <?php endif; ?>
        </div>

        <form method="GET" action="<?= BASE_URL ?>/products">
          <input type="hidden" name="section" value="<?= htmlspecialchars($section) ?>">
          <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">

          <div class="mb-3">
            <label class="form-label fw-semibold">Ordenar</label>
            <select class="form-select" name="sort">
              <option value="" <?= $sort===''?'selected':'' ?>>Recomendados</option>
              <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Más nuevos</option>
              <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Precio: menor a mayor</option>
              <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Precio: mayor a menor</option>
              <option value="name_asc" <?= $sort==='name_asc'?'selected':'' ?>>Nombre: A-Z</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Precio (€)</label>
            <div class="d-flex gap-2">
              <input class="form-control" type="number" step="0.01" name="min_price" placeholder="Min"
                     value="<?= htmlspecialchars((string)$minPrice) ?>">
              <input class="form-control" type="number" step="0.01" name="max_price" placeholder="Max"
                     value="<?= htmlspecialchars((string)$maxPrice) ?>">
            </div>
            <small class="text-muted">Ej: 20 - 150</small>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Marcas</label>

            <?php if (empty($brandsList)): ?>
              <div class="text-muted small">No hay marcas para filtrar.</div>
            <?php else: ?>
              <div class="border rounded p-2" style="max-height: 220px; overflow:auto;">
                <?php foreach ($brandsList as $br): ?>
                  <?php
                    $bid = (int)($br['id'] ?? 0);
                    $bname = (string)($br['name'] ?? '');
                    if ($bid <= 0 || $bname === '') continue;

                    $checked = in_array($bid, $selectedBrands, true);
                    $id = 'brand_' . $bid;
                  ?>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="brands[]" value="<?= $bid ?>"
                           id="<?= $id ?>" <?= $checked ? 'checked' : '' ?>>
                    <label class="form-check-label" for="<?= $id ?>">
                      <?= htmlspecialchars($bname) ?>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <button class="btn btn-warning w-100" type="submit">
            <i class="fas fa-filter"></i> Aplicar filtros
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- LISTADO -->
  <div class="col-12 col-lg-9">
    <?php if (empty($products)): ?>
      <div class="alert alert-info">
        No se encontraron productos con esos filtros.
      </div>
    <?php else: ?>
      <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach($products as $p): ?>
          <?php
            // ✅ Modo automático: viene del ProductController::index()
            $needsSize = !empty($p['has_sizes']);
          ?>
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
                <h5 class="card-title mb-1"><?= htmlspecialchars($p['name']) ?></h5>

                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div class="price"><?= number_format((float)$p['price'], 2) ?>€</div>
                  <span class="badge text-bg-light border">
                    <?= htmlspecialchars($p['category'] ?? '') ?>
                  </span>
                </div>

                <div class="small text-muted mb-2">
                  <?= htmlspecialchars($p['brand_name'] ?? ($p['brand'] ?? '')) ?>
                </div>

                <p class="text-muted mb-3">
                  <?php
                    $txt = trim(($p['short_description'] ?? '') . ' ' . ($p['description'] ?? ''));
                    $txt = preg_replace('/\s+/', ' ', $txt);
                    echo htmlspecialchars(mb_strimwidth($txt, 0, 120, '...', 'UTF-8'));
                  ?>
                </p>

                <?php if ($needsSize): ?>
                  <a class="btn btn-outline-warning w-100 mt-auto" href="<?= BASE_URL ?>/product/<?= (int)$p['id'] ?>">
                    <i class="fas fa-ruler-combined"></i> Ver tallas
                  </a>
                <?php else: ?>
                  <form action="<?= BASE_URL ?>/cart/add" method="POST" class="mt-auto">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button class="btn btn-warning w-100">
                      <i class="fas fa-cart-plus"></i> Añadir
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>
