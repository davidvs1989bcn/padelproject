<?php
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $it) $cartCount += (int)$it['quantity'];
}
$user = $_SESSION['user'] ?? null;

// Mantener filtros en el buscador
$currentQ = trim($_GET['q'] ?? '');
$currentSection = trim($_GET['section'] ?? '');

// Mantener filtros (para no perderlos si buscas)
$currentMin = trim($_GET['min_price'] ?? '');
$currentMax = trim($_GET['max_price'] ?? '');
$currentBrands = $_GET['brands'] ?? [];
if (!is_array($currentBrands)) $currentBrands = [];
$currentSort = trim($_GET['sort'] ?? '');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= APP_NAME ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Favicon -->
  <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon/favicon.ico">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= ASSETS_URL ?>/img/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= ASSETS_URL ?>/img/favicon/favicon-16x16.png">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">

    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/home">
      <img src="<?= ASSETS_URL ?>/img/logo.png" alt="Logo" class="logo">
      <span><?= APP_NAME ?></span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse gap-3" id="nav">

      <!-- ☰ MENÚ (estilo Amazon) -->
      <ul class="navbar-nav align-items-lg-center gap-lg-2">
        <li class="nav-item dropdown">
          <a
            class="nav-link d-inline-flex align-items-center gap-2 px-2 py-1 rounded amazon-menu-btn"
            href="#"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            <i class="fas fa-bars"></i>
            <span class="fw-semibold">Menú</span>
          </a>

          <ul class="dropdown-menu dropdown-menu-start amazon-menu-dropdown">
            <li><h6 class="dropdown-header">Catálogo</h6></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/products"><i class="fas fa-store me-2"></i>Ver catálogo</a></li>
            <li><hr class="dropdown-divider"></li>

            <li><h6 class="dropdown-header">Secciones</h6></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/products?section=palas">Palas</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/products?section=zapatillas">Zapatillas</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/products?section=ropa">Ropa</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/products?section=bolsas">Bolsas</a></li>

            <li><hr class="dropdown-divider"></li>

            <li><h6 class="dropdown-header">Tu cuenta</h6></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/cart"><i class="fas fa-shopping-cart me-2"></i>Carrito</a></li>

            <?php if ($user): ?>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/orders"><i class="fas fa-receipt me-2"></i>Mis pedidos</a></li>
              <?php if (($user['role'] ?? '') === 'admin'): ?>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin"><i class="fas fa-user-shield me-2"></i>Admin</a></li>
              <?php endif; ?>
            <?php else: ?>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/login"><i class="fas fa-right-to-bracket me-2"></i>Login</a></li>
            <?php endif; ?>
          </ul>
        </li>
      </ul>

      <!-- Buscador (manteniendo filtros) -->
      <form class="d-flex flex-grow-1" method="GET" action="<?= BASE_URL ?>/products">
        <input type="hidden" name="section" value="<?= htmlspecialchars($currentSection) ?>">
        <input type="hidden" name="min_price" value="<?= htmlspecialchars($currentMin) ?>">
        <input type="hidden" name="max_price" value="<?= htmlspecialchars($currentMax) ?>">
        <input type="hidden" name="sort" value="<?= htmlspecialchars($currentSort) ?>">
        <?php foreach ($currentBrands as $b): ?>
          <input type="hidden" name="brands[]" value="<?= htmlspecialchars((string)$b) ?>">
        <?php endforeach; ?>

        <input
          class="form-control me-2 search-input"
          type="search"
          name="q"
          placeholder="Buscar productos..."
          value="<?= htmlspecialchars($currentQ) ?>"
        >
        <button class="btn btn-warning" type="submit">
          <i class="fas fa-search"></i>
        </button>
      </form>

      <!-- Zona derecha -->
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/cart">
            <i class="fas fa-shopping-cart"></i> Carrito
            <span class="badge bg-warning text-dark"><?= $cartCount ?></span>
          </a>
        </li>

        <?php if ($user): ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/orders">Mis pedidos</a></li>

          <?php if (($user['role'] ?? '') === 'admin'): ?>
            <li class="nav-item">
              <a class="nav-link text-warning" href="<?= BASE_URL ?>/admin">
                <i class="fas fa-user-shield"></i> Admin
              </a>
            </li>
          <?php endif; ?>

          <li class="nav-item"><span class="nav-link text-white-50">Hola, <?= htmlspecialchars($user['name']) ?></span></li>
          <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>/logout">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="btn btn-warning btn-sm" href="<?= BASE_URL ?>/login">Login</a></li>
        <?php endif; ?>
      </ul>

    </div>
  </div>
</nav>

<main class="container py-4">
