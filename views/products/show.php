<?php require_once 'views/layout/header.php'; ?>

<?php
// Mensaje flash (lo setea el CartController)
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

$flashSuccess = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

// ✅ Stock general
$stockGeneral = (int)($product['stock'] ?? 0);

// $sizeStocks viene del ProductController::show()
$sizeStocks = isset($sizeStocks) && is_array($sizeStocks) ? $sizeStocks : [];

// ✅ Modo automático
$hasSizes = !empty($sizeStocks);

// Total stock por tallas
$sizeTotalStock = 0;
if ($hasSizes) {
  foreach ($sizeStocks as $st) $sizeTotalStock += (int)$st;
}

// Sin stock?
$outOfStock = $hasSizes ? ($sizeTotalStock <= 0) : ($stockGeneral <= 0);

// Lista de tallas
$sizeList = array_keys($sizeStocks);

// Orden bonito tallas
$preferred = ['XS','S','M','L','XL','XXL','XXXL'];
usort($sizeList, function($a, $b) use ($preferred) {
  $a = (string)$a; $b = (string)$b;

  $ia = array_search($a, $preferred, true);
  $ib = array_search($b, $preferred, true);

  $aIsPref = ($ia !== false);
  $bIsPref = ($ib !== false);

  if ($aIsPref && $bIsPref) return $ia <=> $ib;
  if ($aIsPref && !$bIsPref) return -1;
  if (!$aIsPref && $bIsPref) return 1;

  $aNum = is_numeric($a);
  $bNum = is_numeric($b);
  if ($aNum && $bNum) return ((float)$a) <=> ((float)$b);

  return strcmp($a, $b);
});

// =====================
// ✅ GALERÍA AUTOMÁTICA
// =====================
$productId = (int)($product['id'] ?? 0);

// Imagen principal (la de products/)
$mainImgUrl = ASSETS_URL . '/img/products/' . basename($product['image'] ?? '');

// Buscamos imágenes extra en public/img/products_gallery con patrón "ID_*.ext"
$galleryUrls = [];
$galleryDir = __DIR__ . '/../../public/img/products_gallery';

if ($productId > 0 && is_dir($galleryDir)) {
  $matches = glob($galleryDir . '/' . $productId . '_*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
  if (is_array($matches)) {
    natsort($matches);
    foreach ($matches as $filePath) {
      $galleryUrls[] = ASSETS_URL . '/img/products_gallery/' . basename($filePath);
    }
  }
}

$thumbs = array_merge([$mainImgUrl], $galleryUrls);

// ✅ Reseñas (vienen del controller)
$reviewStats = $reviewStats ?? ['avg_rating' => 0, 'total_reviews' => 0];
$reviews = $reviews ?? [];
$canReview = $canReview ?? false;

function renderStars(float $rating): string {
  $r = max(0, min(5, $rating));
  $full = (int)floor($r);
  $half = ($r - $full) >= 0.5 ? 1 : 0;
  $empty = 5 - $full - $half;

  $html = '';
  for ($i=0; $i<$full; $i++) $html .= '<i class="fas fa-star text-warning"></i>';
  if ($half) $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
  for ($i=0; $i<$empty; $i++) $html .= '<i class="far fa-star text-warning"></i>';
  return $html;
}
?>

<div class="row g-4 align-items-start">
  <!-- ✅ COLUMNA GALERÍA -->
  <div class="col-12 col-lg-6">
    <div class="pp-gallery">
      <div class="pp-thumbs" aria-label="Miniaturas">
        <?php foreach ($thumbs as $i => $u): ?>
          <button
            type="button"
            class="pp-thumb <?= $i === 0 ? 'is-active' : '' ?>"
            data-img="<?= htmlspecialchars($u) ?>"
            aria-label="Ver imagen <?= $i+1 ?>"
          >
            <img
              src="<?= htmlspecialchars($u) ?>"
              alt=""
              onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/img/products/placeholder.png';"
            >
          </button>
        <?php endforeach; ?>
      </div>

      <div class="pp-mainwrap">
        <div class="pp-maincard">
          <img
            id="ppMainImg"
            src="<?= htmlspecialchars($mainImgUrl) ?>"
            alt="<?= htmlspecialchars($product['name'] ?? 'Producto') ?>"
            onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/img/products/placeholder.png';"
          >
          <div id="ppLens" class="pp-lens" aria-hidden="true"></div>
        </div>

        <div id="ppZoom" class="pp-zoom" aria-hidden="true"></div>

        <div class="pp-hint">
          Pasa el ratón por la imagen para ampliar
        </div>
      </div>
    </div>
  </div>

  <!-- ✅ COLUMNA INFO -->
  <div class="col-12 col-lg-6">
    <h2 class="mb-1"><?= htmlspecialchars($product['name']) ?></h2>

    <p class="text-muted mb-2">
      <?= htmlspecialchars($product['brand_name'] ?? ($product['brand'] ?? '')) ?>
      <?php if (!empty($product['category'])): ?> · <?= htmlspecialchars($product['category']) ?><?php endif; ?>
    </p>

    <div class="d-flex align-items-center gap-2 mb-2">
      <div><?= renderStars((float)$reviewStats['avg_rating']) ?></div>
      <div class="small text-muted">
        <?= number_format((float)$reviewStats['avg_rating'], 1) ?>/5 · <?= (int)$reviewStats['total_reviews'] ?> reseña(s)
      </div>
      <a class="small text-decoration-none" href="#reviews">Ver reseñas</a>
    </div>

    <p class="h3 text-danger fw-bold mb-2"><?= number_format((float)$product['price'], 2) ?>€</p>

    <?php if (!empty($flashError)): ?>
      <div class="alert alert-warning mt-2">
        <i class="fas fa-triangle-exclamation"></i> <?= htmlspecialchars($flashError) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($flashSuccess)): ?>
      <div class="alert alert-success mt-2">
        <i class="fas fa-check"></i> <?= htmlspecialchars($flashSuccess) ?>
      </div>
    <?php endif; ?>

    <!-- ✅ Indicador de stock -->
    <?php if (!$hasSizes): ?>
      <?php if ($stockGeneral <= 0): ?>
        <span class="badge bg-danger mb-2"><i class="fas fa-box-open"></i> Sin stock</span>
      <?php else: ?>
        <span class="badge bg-light text-dark border mb-2">
          <i class="fas fa-box"></i> Stock disponible: <strong><?= (int)$stockGeneral ?></strong>
        </span>
      <?php endif; ?>
    <?php else: ?>
      <?php if ($outOfStock): ?>
        <span class="badge bg-danger mb-2"><i class="fas fa-box-open"></i> Sin stock</span>
      <?php else: ?>
        <span class="badge bg-light text-dark border mb-2">
          <i class="fas fa-boxes-stacked"></i> Stock total (tallas): <strong><?= (int)$sizeTotalStock ?></strong>
        </span>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($product['short_description'])): ?>
      <p class="mt-2 text-muted"><?= htmlspecialchars($product['short_description']) ?></p>
    <?php endif; ?>

    <p class="mt-3"><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></p>

    <form action="<?= BASE_URL ?>/cart/add" method="POST" class="mt-4">
      <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">

      <?php if ($hasSizes): ?>
        <div class="mb-3" style="max-width: 320px;">
          <label class="form-label fw-semibold">Talla</label>

          <select class="form-select" name="size" required <?= $outOfStock ? 'disabled' : '' ?>>
            <option value="">Selecciona talla</option>
            <?php foreach ($sizeList as $s): ?>
              <?php
                $st = (int)($sizeStocks[$s] ?? 0);
                $disabled = ($st <= 0) ? 'disabled' : '';
              ?>
              <option value="<?= htmlspecialchars($s) ?>" <?= $disabled ?>>
                <?= htmlspecialchars($s) ?> — <?= (int)$st ?> uds<?= $st <= 0 ? ' (agotada)' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>

          <div class="mt-3">
            <div class="small fw-semibold mb-1">Stock por talla:</div>
            <div class="d-flex flex-wrap gap-2">
              <?php foreach ($sizeList as $s): ?>
                <?php $st = (int)($sizeStocks[$s] ?? 0); ?>
                <span class="badge <?= $st > 0 ? 'bg-light text-dark border' : 'bg-danger' ?>">
                  <?= htmlspecialchars($s) ?>: <?= (int)$st ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <div class="d-flex flex-wrap gap-2">
        <?php if ($outOfStock): ?>
          <button class="btn btn-secondary btn-lg" type="button" disabled>
            <i class="fas fa-ban"></i> Sin stock
          </button>
        <?php else: ?>
          <button class="btn btn-warning btn-lg" type="submit">
            <i class="fas fa-cart-plus"></i> Añadir al carrito
          </button>
        <?php endif; ?>

        <a class="btn btn-outline-secondary btn-lg" href="<?= BASE_URL ?>/products">Volver</a>

        <?php if ($canReview): ?>
          <a class="btn btn-outline-warning btn-lg" href="<?= BASE_URL ?>/review?product_id=<?= (int)$product['id'] ?>&redirect=product">
            <i class="fas fa-star"></i> Escribir reseña
          </a>
        <?php endif; ?>
      </div>
    </form>

    <div class="mt-3 text-muted small">
      <i class="fas fa-truck"></i> Envío 24/48h ·
      <i class="fas fa-rotate-left"></i> Devolución 14 días
    </div>
  </div>
</div>

<hr class="my-4">

<!-- ✅ SECCIÓN RESEÑAS -->
<div id="reviews" class="mb-5">
  <div class="d-flex align-items-center justify-content-between mb-2">
    <h4 class="m-0">Reseñas</h4>

    <?php if ($canReview): ?>
      <a class="btn btn-warning btn-sm" href="<?= BASE_URL ?>/review?product_id=<?= (int)$product['id'] ?>&redirect=product">
        <i class="fas fa-pen"></i> Escribir reseña
      </a>
    <?php elseif (!isset($_SESSION['user'])): ?>
      <a class="btn btn-outline-secondary btn-sm" href="<?= BASE_URL ?>/login">
        Inicia sesión para reseñar
      </a>
    <?php endif; ?>
  </div>

  <div class="d-flex align-items-center gap-2 mb-3">
    <div class="fs-5"><?= renderStars((float)$reviewStats['avg_rating']) ?></div>
    <div class="text-muted">
      <strong><?= number_format((float)$reviewStats['avg_rating'], 1) ?></strong>/5
      · <?= (int)$reviewStats['total_reviews'] ?> reseña(s)
    </div>
  </div>

  <?php if (empty($reviews)): ?>
    <div class="alert alert-light border">
      Aún no hay reseñas para este producto.
    </div>
  <?php else: ?>
    <div class="d-flex flex-column gap-3">
      <?php foreach ($reviews as $r): ?>
        <?php
          $rt = (int)($r['rating'] ?? 0);
          $title = trim((string)($r['title'] ?? ''));
        $body = trim((string)($r['comment'] ?? ''));

          $date = (string)($r['created_at'] ?? '');
        ?>
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <div><?= renderStars((float)$rt) ?></div>
                <?php if ($title !== ''): ?>
                  <div class="fw-semibold"><?= htmlspecialchars($title) ?></div>
                <?php endif; ?>
              </div>
              <div class="small text-muted">
                <?= $date ? date('d/m/Y', strtotime($date)) : '' ?>
              </div>
            </div>

            <?php if ($body !== ''): ?>
              <div class="mt-2 text-muted"><?= nl2br(htmlspecialchars($body)) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const mainImg = document.getElementById('ppMainImg');
  const zoom = document.getElementById('ppZoom');
  const lens = document.getElementById('ppLens');
  const thumbs = document.querySelectorAll('.pp-thumb');

  let zoomFactor = 2.6;

  function setActiveThumb(btn) {
    thumbs.forEach(b => b.classList.remove('is-active'));
    btn.classList.add('is-active');
  }

  function setMainImage(url) {
    mainImg.src = url;
    zoom.style.backgroundImage = `url("${url}")`;
  }

  thumbs.forEach(btn => {
    btn.addEventListener('click', () => {
      const url = btn.dataset.img;
      setActiveThumb(btn);
      setMainImage(url);
    });
  });

  function showZoom() {
    if (window.matchMedia('(min-width: 992px)').matches) {
      zoom.classList.add('is-visible');
      lens.classList.add('is-visible');
    }
  }

  function hideZoom() {
    zoom.classList.remove('is-visible');
    lens.classList.remove('is-visible');
  }

  function moveZoom(e) {
    if (!zoom.classList.contains('is-visible')) return;

    const rect = mainImg.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    if (x < 0 || y < 0 || x > rect.width || y > rect.height) return;

    const lensW = rect.width * 0.28;
    const lensH = rect.height * 0.28;

    let lx = x - lensW / 2;
    let ly = y - lensH / 2;

    lx = Math.max(0, Math.min(lx, rect.width - lensW));
    ly = Math.max(0, Math.min(ly, rect.height - lensH));

    lens.style.width = lensW + 'px';
    lens.style.height = lensH + 'px';
    lens.style.left = lx + 'px';
    lens.style.top = ly + 'px';

    const bgW = rect.width * zoomFactor;
    const bgH = rect.height * zoomFactor;

    zoom.style.backgroundSize = `${bgW}px ${bgH}px`;

    const fx = (lx + lensW/2) / rect.width;
    const fy = (ly + lensH/2) / rect.height;

    const bgX = fx * bgW;
    const bgY = fy * bgH;

    const zoomRect = zoom.getBoundingClientRect();
    const zx = bgX - zoomRect.width / 2;
    const zy = bgY - zoomRect.height / 2;

    zoom.style.backgroundPosition = `-${zx}px -${zy}px`;
  }

  zoom.style.backgroundImage = `url("${mainImg.src}")`;

  mainImg.addEventListener('mouseenter', showZoom);
  mainImg.addEventListener('mouseleave', hideZoom);
  mainImg.addEventListener('mousemove', moveZoom);

  mainImg.parentElement.addEventListener('mouseleave', hideZoom);
});
</script>

<?php require_once 'views/layout/footer.php'; ?>
