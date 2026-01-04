<?php require_once 'views/layout/header.php'; ?>

<h2 class="mb-3">Nueva contraseña</h2>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success">
    <?= htmlspecialchars($success) ?>
    <div class="mt-2">
      <a class="btn btn-success" href="<?= BASE_URL ?>/login">Ir al login</a>
    </div>
  </div>
<?php endif; ?>

<?php if (empty($success)): ?>
<form method="POST" action="<?= BASE_URL ?>/reset-password" class="card p-4 shadow-sm" style="max-width:520px;">
  <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '') ?>">

  <div class="mb-3">
    <label class="form-label">Email</label>
    <input class="form-control" type="email" value="<?= htmlspecialchars($email ?? '') ?>" disabled>
  </div>

  <div class="mb-3">
    <label class="form-label">Pregunta de seguridad</label>
    <input class="form-control" type="text" value="<?= htmlspecialchars($question ?? '') ?>" disabled>
  </div>

  <div class="mb-3">
    <label class="form-label">Tu respuesta</label>
    <input class="form-control" type="text" name="security_answer" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Nueva contraseña</label>
    <input class="form-control" type="password" name="new_password" required>
    <small class="text-muted">Mínimo 4 caracteres.</small>
  </div>

  <button class="btn btn-primary w-100">Cambiar contraseña</button>

  <div class="text-center mt-3">
    <a href="<?= BASE_URL ?>/login">Volver al login</a>
  </div>
</form>
<?php endif; ?>

<?php require_once 'views/layout/footer.php'; ?>
