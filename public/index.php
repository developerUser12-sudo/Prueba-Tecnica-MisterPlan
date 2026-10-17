<?php
require_once __DIR__ . '/../src/Controllers/ReservaController.php';
$metodo = $_SERVER['REQUEST_METHOD'];
if ($metodo === 'POST' && $_GET['ruta'] == 'api/crear-reserva') {
    $controller = new ReservaController();
    $controller->crear();
    exit;
}
if ($metodo === 'GET') {
    if ($_GET['ruta'] == 'api/detalle-reserva') {
        $controller = new ReservaController();
        $controller->detalle($_GET['id']);
        exit;
    }
    if ($_GET['ruta'] == 'api/filtrar') {
        $controller = new ReservaController();
        $controller->filtrar();
        exit;

    }
}
if ($metodo === 'PATCH' && $_GET['ruta'] == 'api/cambiar-estado') {
    $controller = new ReservaController();
    $controller->cambiarEstado($_GET['id']);
    exit;
}
http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['error' => 'Ruta no encontrada']);