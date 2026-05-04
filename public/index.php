<?php
// Bootstrap
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Autoload controllers and models
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../app/controllers/' . $class . '.php',
        __DIR__ . '/../app/models/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

session_start();

// Simple Router
$request = $_GET['url'] ?? 'dashboard';
$request = rtrim($request, '/');
$parts = explode('/', $request);

$controllerName = ucfirst($parts[0] ?? 'dashboard') . 'Controller';
$action = $parts[1] ?? 'index';
$param = $parts[2] ?? null;

// Auth check
$publicRoutes = ['auth'];
if (!isset($_SESSION['user_id']) && !in_array($parts[0], $publicRoutes)) {
    header('Location: ' . BASE_URL . '/index.php?url=auth/login');
    exit;
}

$controllerFile = __DIR__ . '/../app/controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerName();
    if (method_exists($controller, $action)) {
        $controller->$action($param);
    } else {
        http_response_code(404);
        echo '<h1>404 - Action not found</h1>';
    }
} else {
    // Default to dashboard
    require_once __DIR__ . '/../app/controllers/DashboardController.php';
    $controller = new DashboardController();
    $controller->index();
}
