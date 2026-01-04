<?php require_once 'views/layout/header.php'; ?>

<h2 class="mb-3">Login</h2>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form id="loginForm" method="POST" action="<?= BASE_URL ?>/login"
      class="card p-4 shadow-sm needs-validation" style="max-width:520px;" novalidate>

  <div class="mb-3">
    <label class="form-label">Email</label>
    <input class="form-control" type="email" name="email" id="loginEmail" required>
    <div class="invalid-feedback">Introduce un email válido.</div>
  </div>

  <div class="mb-3">
    <label class="form-label">Contraseña</label>
    <input class="form-control" type="password" name="password" id="loginPassword" required minlength="4">
    <div class="invalid-feedback">La contraseña debe tener al menos 4 caracteres.</div>
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

<script>
(() => {
  const form = document.getElementById('loginForm');

  form.addEventListener('submit', (e) => {
    // Validación nativa HTML5
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add('was-validated');
  });
})();
</script>

<?php require_once 'views/layout/footer.php'; ?>
