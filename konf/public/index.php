<?php
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (PHP_SAPI === 'cli-server' && $path !== '/' && is_file(__DIR__ . $path)) {
    return false;
}

require __DIR__ . '/../src/bootstrap.php';

use Konf\Controller\AdminController;
use Konf\Controller\ApplicationController;
use Konf\Controller\AuthController;
use Konf\Controller\PageController;
use Konf\Http;

$routes = [
    'GET /'                     => [PageController::class, 'home'],
    'GET /register'             => [AuthController::class, 'registerForm'],
    'POST /register'            => [AuthController::class, 'register'],
    'GET /login'                => [AuthController::class, 'loginForm'],
    'POST /login'               => [AuthController::class, 'login'],
    'POST /logout'              => [AuthController::class, 'logout'],
    'GET /profile'              => [ApplicationController::class, 'profile'],
    'GET /applications'         => [ApplicationController::class, 'index'],
    'GET /applications/new'     => [ApplicationController::class, 'createForm'],
    'POST /applications'        => [ApplicationController::class, 'create'],
    'POST /applications/cancel' => [ApplicationController::class, 'cancel'],
    'POST /reviews'             => [ApplicationController::class, 'review'],
    'GET /admin'                => [AdminController::class, 'index'],
    'POST /admin/status'        => [AdminController::class, 'status'],
];

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$key = $method . ' ' . rtrim($path, '/');
$key = $key === $method . ' ' ? $method . ' /' : $key;

if (!isset($routes[$key])) {
    $known = array_filter(array_keys($routes), static fn ($r) => str_ends_with($r, ' ' . (rtrim($path, '/') ?: '/')));
    if ($known) {
        Http::abort(405, 'Метод не поддерживается');
    }
    Http::abort(404, 'Страница не найдена');
}

try {
    [$class, $action] = $routes[$key];
    (new $class())->$action();
} catch (Throwable $e) {
    error_log((string) $e);
    Http::abort(500, 'Внутренняя ошибка сервера. Попробуйте позже.');
}
