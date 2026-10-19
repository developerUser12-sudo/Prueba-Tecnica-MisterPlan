<?php
require_once __DIR__ . '/../src/Controllers/ReservaController.php';
require_once __DIR__ . '/../src/Controllers/VistaController.php';
require_once __DIR__ . '/../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(
    __DIR__ . '/../templates'
);

$twig = new \Twig\Environment($loader, [
    'autoescape' => 'html',
    'cache' => false,
]);

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
    if ($_GET['ruta'] == 'reservas') {
        $controller = new VistaController($twig);
        $controller->listar();
        exit;
    }

    if ($_GET['ruta'] == 'reservas/detalle') {
        $controller = new VistaController($twig);
        $controller->detalle($_GET['id'] ?? null);
        exit;
    }
    if ($_GET['ruta'] == 'reservas/filtrar') {
        $controller = new VistaController($twig);
        $controller->filtrar($_GET['estado'] ?? null,$_GET['nombre_huesped'] ?? null, $_GET['fecha_entrada'] ?? null, $_GET['fecha_salida'] ?? null);
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