<?php
// public/index.php
require_once __DIR__ . '/../private/controllers/AuthController.php';
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($request === '/login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        AuthController::handleLogin();
        exit;
    }
    require __DIR__ . '/../private/views/pages/login.php';
    exit;
}

if ($request === '/logout') {
    AuthController::logout();
    exit;
}
require_once __DIR__ . '/../private/bootstrap.php';

$request = $_SERVER['REQUEST_URI'];

switch ($request) {
    case '/':
    case '/home':
        AuthController::checkAuth();
        require '../private/views/pages/home.php';
        break;
    case '/reparaties':
        AuthController::checkAuth();
        require '../private/views/pages/reparaties.php';
        break;
    case '/bak':
        AuthController::checkAuth();
        require '../private/views/pages/bak.php';
        break;
    case '/complete':
        AuthController::checkAuth();
        require '../private/views/pages/complete.php';
        break;
    default:
        http_response_code(404);
        require '../private/views/pages/404.php';
        break;
}