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
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h2 class="m-0">Mis pedidos</h2>
  <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/products">
    <i class="fas fa-store"></i> Seguir comprando
  </a>
</div>

<?php if (empty($orders)): ?>
  <div class="alert alert-info">
    Aún no has realizado pedidos.
  </div>
  <a class="btn btn-primary" href="<?= BASE_URL ?>/products">Comprar ahora</a>

<?php else: ?>

  <div class="row g-3">
    <?php foreach ($orders as $o): ?>
      <?php
        [$statusText, $badgeClass] = statusBadge($o['status'] ?? '');
        $img = $o['first_image'] ?? '';
        $imgSrc = $img ? (ASSETS_URL . '/img/products/' . basename($img)) : (ASSETS_URL . '/img/products/placeholder.png');
        $firstName = $o['first_product_name'] ?? 'Pedido';
        $itemCount = (int)($o['item_count'] ?? 0);
        $hasSizes = (int)($o['has_sizes'] ?? 0);
      ?>
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">

              <div class="d-flex gap-3 align-items-center">
                <div class="border rounded bg-white d-flex align-items-center justify-content-center" style="width:90px;height:90px;">
                  <img src="<?= $imgSrc ?>"
                       alt="<?= htmlspecialchars($firstName) ?>"
                       style="width:80px;height:80px;object-fit:contain;"
                       onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/img/products/placeholder.png';">
                </div>

                <div>
                  <div class="small text-muted">
                    Pedido #<?= (int)$o['id'] ?> • <?= formatDateES($o['created_at'] ?? '') ?>
                  </div>

                  <div class="fw-semibold"><?= htmlspecialchars($firstName) ?></div>

                  <div class="small text-muted d-flex flex-wrap gap-2 align-items-center">
                    <span><?= $itemCount ?> artículo(s)</span>

                    <?php if ($hasSizes === 1): ?>
                      <span class="badge text-bg-light border">
                        <i class="fas fa-ruler-combined"></i> Incluye tallas
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="d-flex gap-3 align-items-center justify-content-end">
                <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>

                <div class="text-end">
                  <div class="small text-muted">Total</div>
                  <div class="fw-bold"><?= number_format((float)$o['total'], 2) ?> €</div>
                </div>

                <a class="btn btn-primary" href="<?= BASE_URL ?>/order/<?= (int)$o['id'] ?>">
                  Ver pedido
                </a>
              </div>

            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

<?php endif; ?>

<?php require_once 'views/layout/footer.php'; ?>
