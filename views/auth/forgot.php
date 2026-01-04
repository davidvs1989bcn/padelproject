<?php require_once 'views/layout/header.php'; ?>

<h2 class="mb-3">Restablecer contraseña</h2>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/forgot-password" class="card p-4 shadow-sm" style="max-width:520px;">
  <div class="mb-3">
    <label class="form-label">Introduce tu email</label>
    <input class="form-control" type="email" name="email" required>
    <small class="text-muted">Te mostraremos tu pregunta de seguridad.</small>
  </div>

  <button class="btn btn-primary w-100">Continuar</button>

  <div class="text-center mt-3">
    <a href="<?= BASE_URL ?>/login">Volver al login</a>
  </div>
</form>

<?php require_once 'views/layout/footer.php'; ?>
