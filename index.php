<?php
session_start();
require_once 'config/config.php';

// Autoload
spl_autoload_register(function ($class) {
    $paths = ['models/', 'controllers/', 'config/']; // ✅ añadimos config
    foreach ($paths as $path) {
        if (file_exists($path . $class . '.php')) {
            require_once $path . $class . '.php';
            return;
        }
    }
});


// === ROUTER LOGIC V5 (CORREGIDO PARA WINDOWS) ===
$request = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$dirName = dirname($scriptName);

// 1. Quitar Query String
$request = strtok($request, '?');

// 2. Normalizar barras para Windows
$backslash = chr(92);
$request = str_replace($backslash, '/', $request);
$dirName = str_replace($backslash, '/', $dirName);

// 3. Eliminar la carpeta base de la request
if ($dirName !== '/' && strpos($request, $dirName) === 0) {
    $request = substr($request, strlen($dirName));
}

// 4. Eliminar index.php si está en la URL
$request = str_replace('/index.php', '', $request);

// 5. Normalizar ruta final
$route = $request;
if ($route === '' || $route === '/') {
    $route = '/home';
}
if (strlen($route) > 1) {
    $route = rtrim($route, '/');
}
// === FIN ROUTER ===

switch ($route) {
    case '/home':
        $controller = new ProductController();
        $controller->index();
        break;

    case '/products':
        $controller = new ProductController();
        $controller->index();
        break;

    case (preg_match('/^\/product\/(\d+)$/', $route, $matches) ? true : false):
        $controller = new ProductController();
        $controller->show((int)$matches[1]);
        break;

    // === CARRITO ===
    case '/cart':
        $controller = new CartController();
        $controller->index();
        break;

    case '/cart/add':
        $controller = new CartController();
        $controller->add();
        break;

    case '/cart/remove':
        $controller = new CartController();
        $controller->remove();
        break;

    // ✅ NUEVO: actualizar cantidades
    case '/cart/update':
        $controller = new CartController();
        $controller->update();
        break;

    // === AUTH ===
    case '/login':
        $controller = new AuthController();
        $controller->login();
        break;

    // ✅ NUEVO: register
    case '/register':
        $controller = new AuthController();
        $controller->register();
        break;

    case '/logout':
        $controller = new AuthController();
        $controller->logout();
        break;

    // === PEDIDOS ===
    // ✅ NUEVO: lista de pedidos
    case '/orders':
        $controller = new OrderController();
        $controller->index();
        break;

    // ✅ NUEVO: checkout (compra)
    case '/checkout':
        $controller = new OrderController();
        $controller->checkout();
        break;

    // ✅ NUEVO: ver pedido por id
    case (preg_match('/^\/order\/(\d+)$/', $route, $matches) ? true : false):
        $controller = new OrderController();
        $controller->show((int)$matches[1]);
        break;

    // === ADMIN (opcional) ===
        // === ADMIN ===
    case '/admin':
        $controller = new AdminController();
        $controller->dashboard();
        break;

    case '/admin/products':
        $controller = new AdminController();
        $controller->products();
        break;

    case '/admin/product/create':
        $controller = new AdminController();
        $controller->create();
        break;

    case (preg_match('/^\/admin\/product\/edit\/(\d+)$/', $route, $matches) ? true : false):
        $controller = new AdminController();
        $controller->edit((int)$matches[1]);
        break;

    case '/admin/product/delete':
        $controller = new AdminController();
        $controller->delete();
        break;

    // ✅ AJAX: comprobar email en registro
    case '/check-email':
        $controller = new AuthController();
        $controller->checkEmail();
        break;
            // ✅ Restablecer contraseña (paso 1)
    case '/forgot-password':
        $controller = new AuthController();
        $controller->forgot();
        break;

    // ✅ Restablecer contraseña (paso 2)
    case '/reset-password':
        $controller = new AuthController();
        $controller->reset();
        break;


    default:
        http_response_code(404);
        require_once 'views/layout/header.php';
        echo "<div class='container py-5 text-center'><h1>404</h1><p>Página no encontrada</p>";
        echo "<a href='".BASE_URL."/home' class='btn btn-primary'>Volver al Inicio</a></div>";
        require_once 'views/layout/footer.php';
        break;
}
?>
