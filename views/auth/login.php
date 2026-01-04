<?php require_once 'views/layout/header.php'; ?>

<h2 class="mb-3">Login</h2>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/login" class="card p-4 shadow-sm" style="max-width:520px;">
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input class="form-control" type="email" name="email" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Contraseña</label>
    <input class="form-control" type="password" name="password" required>
  </div>

  <button class="btn btn-warning w-100">Entrar</button>

  <div class="text-center mt-3">
    <a href="<?= BASE_URL ?>/forgot-password">¿Has olvidado tu contraseña?</a>
  </div>

  <div class="text-center mt-3">
    ¿No tienes cuenta?
    <a href="<?= BASE_URL ?>/register">Regístrate</a>
  </div>
</form>

<?php require_once 'views/layout/footer.php'; ?>
