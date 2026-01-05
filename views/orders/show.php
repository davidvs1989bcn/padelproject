<?php require_once 'views/layout/header.php'; ?>

<?php
function formatDateES(?string $dt): string {
  if (!$dt) return '';
  $ts = strtotime($dt);
  if (!$ts) return htmlspecialchars($dt);
  return date('d/m/Y H:i', $ts);
}

function statusBadge(string $status): array {
  $s = strtolower(trim($status));
  if ($s === 'paid' || $s === 'pagado') return ['Pagado', 'bg-success'];
  if ($s === 'pending' || $s === 'pendiente') return ['Pendiente', 'bg-warning text-dark'];
  if ($s === 'cancelled' || $s === 'cancelado') return ['Cancelado', 'bg-danger'];
  if ($s === 'shipped' || $s === 'enviado') return ['Enviado', 'bg-primary'];
  return [htmlspecialchars($status), 'bg-secondary'];
}

[$statusText, $badgeClass] = statusBadge($order['status'] ?? '');
$itemCount = 0;
foreach ($items as $it) $itemCount += (int)($it['quantity'] ?? 0);

$orderId = (int)($order['id'] ?? 0);
$reviewedMap = $reviewedMap ?? []; // product_id => rating
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h2 class="m-0">Pedido #<?= (int)$order['id'] ?></h2>
    <div class="text-muted small">
      <?= $itemCount ?> artículo(s)
      <?php if (!empty($order['created_at'])): ?>
        • <?= formatDateES($order['created_at']) ?>
      <?php endif; ?>
    </div>
  </div>

  <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/orders">
    <i class="fas fa-arrow-left"></i> Volver
  </a>
</div>

<div class="card shadow-sm border-0 mb-4">
  <div class="card-body">
    <div class="row g-3 align-items-center">
      <div class="col-md-4">
        <div class="small text-muted">Fecha</div>
        <div class="fw-semibold"><?= formatDateES($order['created_at'] ?? '') ?></div>
      </div>

      <div class="col-md-4">
        <div class="small text-muted">Estado</div>
        <div><span class="badge <?= $badgeClass ?>"><?= $statusText ?></span></div>
      </div>

      <div class="col-md-4 text-md-end">
        <div class="small text-muted">Total</div>
        <div class="fs-4 fw-bold"><?= number_format((float)$order['total'], 2) ?> €</div>
      </div>
    </div>
  </div>
</div>

<h5 class="mb-3">Artículos</h5>

<div class="card shadow-sm border-0">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:110px;">Producto</th>
            <th>Nombre</th>
            <th class="text-end">Precio</th>
            <th class="text-end">Cantidad</th>
            <th class="text-end">Subtotal</th>
            <th class="text-end" style="width:170px;">Reseña</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <?php
              $img = $it['product_image'] ?? '';
              $imgSrc = $img ? (ASSETS_URL . '/img/products/' . basename($img)) : (ASSETS_URL . '/img/products/placeholder.png');
              $size = trim((string)($it['size'] ?? ''));
              $pid = (int)($it['product_id'] ?? 0);

              $already = ($pid > 0 && isset($reviewedMap[$pid]));
            ?>
            <tr>
              <td>
                <div class="border rounded bg-white d-flex align-items-center justify-content-center" style="width:90px;height:90px;">
                  <img
                    src="<?= $imgSrc ?>"
                    alt="<?= htmlspecialchars($it['product_name'] ?? 'Producto') ?>"
                    style="width:80px;height:80px;object-fit:contain;"
                    onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/img/products/placeholder.png';"
                  >
                </div>
              </td>

              <td>
                <div class="fw-semibold"><?= htmlspecialchars($it['product_name'] ?? '') ?></div>

                <div class="small text-muted">
                  <?php
                    $meta = [];
                    $brand = trim((string)($it['product_brand'] ?? ''));
                    $cat = trim((string)($it['product_category'] ?? ''));

                    if ($brand !== '') $meta[] = htmlspecialchars($brand);
                    if ($cat !== '') $meta[] = htmlspecialchars($cat);
                    if ($size !== '') $meta[] = 'Talla: <strong>' . htmlspecialchars($size) . '</strong>';

                    echo implode(' • ', $meta);
                  ?>
                </div>
              </td>

              <td class="text-end"><?= number_format((float)($it['unit_price'] ?? 0), 2) ?> €</td>
              <td class="text-end"><?= (int)($it['quantity'] ?? 0) ?></td>
              <td class="text-end fw-semibold"><?= number_format((float)($it['subtotal'] ?? 0), 2) ?> €</td>

              <td class="text-end">
                <?php if ($pid <= 0): ?>
                  <span class="text-muted small">—</span>

                <?php elseif ($already): ?>
                  <span class="badge bg-light text-dark border">
                    <?= (int)$reviewedMap[$pid] ?>/5 <i class="fas fa-star text-warning"></i>
                  </span>

                <?php else: ?>
                  <a class="btn btn-outline-warning btn-sm"
                     href="<?= BASE_URL ?>/review?product_id=<?= $pid ?>&order_id=<?= (int)$orderId ?>&redirect=order">
                    <i class="fas fa-star"></i> Reseñar
                  </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot class="table-light">
          <tr>
            <td colspan="5" class="text-end fw-semibold">Total</td>
            <td class="text-end fw-bold"><?= number_format((float)$order['total'], 2) ?> €</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<div class="mt-4 d-flex flex-wrap gap-2">
  <a class="btn btn-primary" href="<?= BASE_URL ?>/products">
    <i class="fas fa-store"></i> Seguir comprando
  </a>
  <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/orders">
    Ver todos mis pedidos
  </a>
</div>

<?php require_once 'views/layout/footer.php'; ?>
