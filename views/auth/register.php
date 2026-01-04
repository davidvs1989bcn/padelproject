<?php require_once 'views/layout/header.php'; ?>

<h2 class="mb-3">Registro</h2>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/register" class="card p-4 shadow-sm" style="max-width:520px;">
  <div class="mb-3">
    <label class="form-label">Nombre</label>
    <input class="form-control" type="text" name="name" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Email</label>
    <input class="form-control" type="email" name="email" id="email" required>
    <div id="emailFeedback" class="form-text"></div>
  </div>

  <div class="mb-3">
    <label class="form-label">Contraseña</label>
    <input class="form-control" type="password" name="password" required>
    <small class="text-muted">Mínimo 4 caracteres.</small>
  </div>

  <hr class="my-3">

  <h5 class="mb-3">Recuperación de contraseña (pregunta de seguridad)</h5>

  <div class="mb-3">
    <label class="form-label">Pregunta de seguridad</label>
    <select class="form-select" name="security_question" required>
      <option value="">-- Selecciona una pregunta --</option>
      <option value="¿Cuál es tu comida favorita?">¿Cuál es tu comida favorita?</option>
      <option value="¿Cómo se llamaba tu primera mascota?">¿Cómo se llamaba tu primera mascota?</option>
      <option value="¿En qué ciudad naciste?">¿En qué ciudad naciste?</option>
      <option value="¿Cuál es el nombre de tu mejor amigo/a de la infancia?">¿Cuál es el nombre de tu mejor amigo/a de la infancia?</option>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Respuesta (guárdala, la necesitarás)</label>
    <input class="form-control" type="text" name="security_answer" id="security_answer" required>
    <small class="text-muted">No distingas mayúsculas/minúsculas al recordarla.</small>
  </div>

  <button id="registerBtn" class="btn btn-success w-100">Crear cuenta</button>

  <div class="text-center mt-3">
    ¿Ya tienes cuenta?
    <a href="<?= BASE_URL ?>/login">Login</a>
  </div>
</form>

<script>
(() => {
  const emailInput = document.getElementById('email');
  const feedback = document.getElementById('emailFeedback');
  const btn = document.getElementById('registerBtn');

  let timer = null;
  let lastChecked = '';

  function setState(className, message, disabled) {
    feedback.className = 'form-text ' + className;
    feedback.textContent = message;
    btn.disabled = !!disabled;
  }

  async function checkEmail(email) {
    if (!email) {
      setState('', '', false);
      lastChecked = '';
      return;
    }
    if (email === lastChecked) return;
    lastChecked = email;

    if (!emailInput.checkValidity()) {
      setState('text-danger', 'Formato de email no válido.', true);
      return;
    }

    setState('text-muted', 'Comprobando email...', true);

    try {
      const response = await fetch(
        "<?= BASE_URL ?>/check-email?email=" + encodeURIComponent(email)
      );
      const data = await response.json();

      if (data.exists) {
        setState('text-danger', 'Ese email ya está en uso.', true);
      } else {
        setState('text-success', 'Email disponible ✅', false);
      }
    } catch {
      setState('text-danger', 'Error comprobando el email.', true);
    }
  }

  emailInput.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => checkEmail(emailInput.value.trim()), 400);
  });

  emailInput.addEventListener('blur', () => checkEmail(emailInput.value.trim()));
})();
</script>

<?php require_once 'views/layout/footer.php'; ?>
