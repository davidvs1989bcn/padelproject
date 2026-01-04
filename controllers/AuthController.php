<?php
class AuthController {

    private function redirect(string $path): void {
        header("Location: " . BASE_URL . $path);
        exit;
    }

    public function login(): void {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($email === '' || $password === '') {
                $error = "Rellena email y contraseña.";
            } else {
                $userModel = new User();
                $user = $userModel->verifyLogin($email, $password);

                if (!$user) {
                    $error = "Credenciales incorrectas.";
                } else {
                    $_SESSION['user'] = [
                        'id' => (int)$user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ];
                    $this->redirect('/home');
                }
            }
        }

        require 'views/auth/login.php';
    }

    public function register(): void {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $question = trim($_POST['security_question'] ?? '');
            $answer = trim($_POST['security_answer'] ?? '');

            if ($name === '' || $email === '' || $password === '' || $question === '' || $answer === '') {
                $error = "Rellena todos los campos (incluida la pregunta de seguridad).";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Email no válido.";
            } elseif (strlen($password) < 4) {
                $error = "La contraseña debe tener al menos 4 caracteres.";
            } elseif (strlen($answer) < 2) {
                $error = "La respuesta de seguridad es demasiado corta.";
            } else {
                $userModel = new User();
                if ($userModel->findByEmail($email)) {
                    $error = "Ese email ya está registrado.";
                } else {
                    $userModel->create($name, $email, $password, $question, $answer);
                    $this->redirect('/login');
                }
            }
        }

        require 'views/auth/register.php';
    }

    public function logout(): void {
        unset($_SESSION['user']);
        $this->redirect('/home');
    }

    // ✅ AJAX: comprobar email
    public function checkEmail(): void {
        header('Content-Type: application/json; charset=utf-8');

        $email = trim($_GET['email'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['exists' => false]);
            return;
        }

        $userModel = new User();
        $exists = $userModel->findByEmail($email) ? true : false;

        echo json_encode(['exists' => $exists]);
    }

    // ✅ 1) Form de "olvidé la contraseña"
    public function forgot(): void {
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Introduce un email válido.";
            } else {
                $userModel = new User();
                $question = $userModel->getSecurityQuestionByEmail($email);

                if (!$question) {
                    $error = "No existe un usuario con ese email.";
                } else {
                    // Guardamos email en sesión para el siguiente paso
                    $_SESSION['reset_email'] = $email;
                    $this->redirect('/reset-password');
                }
            }
        }

        require 'views/auth/forgot.php';
    }

    // ✅ 2) Form de reset: pregunta + respuesta + nueva contraseña
    public function reset(): void {
        $error = null;
        $success = null;

        $email = $_SESSION['reset_email'] ?? '';
        if ($email === '') {
            $this->redirect('/forgot-password');
        }

        $userModel = new User();
        $question = $userModel->getSecurityQuestionByEmail($email);

        if (!$question) {
            unset($_SESSION['reset_email']);
            $this->redirect('/forgot-password');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $answer = trim($_POST['security_answer'] ?? '');
            $newPass = $_POST['new_password'] ?? '';

            if ($answer === '' || $newPass === '') {
                $error = "Rellena respuesta y nueva contraseña.";
            } elseif (strlen($newPass) < 4) {
                $error = "La nueva contraseña debe tener al menos 4 caracteres.";
            } else {
                $okAnswer = $userModel->verifySecurityAnswer($email, $answer);

                if (!$okAnswer) {
                    $error = "Respuesta incorrecta.";
                } else {
                    $userModel->updatePasswordByEmail($email, $newPass);
                    unset($_SESSION['reset_email']);
                    $success = "Contraseña cambiada correctamente.";
                }
            }
        }

        require 'views/auth/reset.php';
    }
}
