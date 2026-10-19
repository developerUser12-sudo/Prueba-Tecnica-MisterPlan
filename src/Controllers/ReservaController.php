<?php
require_once __DIR__ . '/../Database/Conexion.php';
require_once __DIR__ . '/../Repositories/ReservaRepository.php';
class ReservaController
{
    private function validarFecha($fecha)
    {
        $fechaValidada = DateTime::createFromFormat('!Y-m-d', $fecha);
        if (!$fechaValidada || $fechaValidada->format('Y-m-d') != $fecha) {
            return null;
        }
        return $fechaValidada;
    }
    public function crear()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Metodo no permitido']);
            return;
        }
        $datosReserva = json_decode(file_get_contents('php://input'), true);
        if (!is_array($datosReserva)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos no válidos']);
            return;
        }
        $camposObligatorios = ['nombre_huesped', 'email_huesped', 'nombre_alojamiento', 'fecha_entrada', 'fecha_salida', 'importe'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datosReserva[$campo]) || trim((string) $datosReserva[$campo]) == '') {
                http_response_code(422);
                echo json_encode(['error' => 'Falta un campo obligatorio']);
                return;
            }
        }
        if (!filter_var($datosReserva['email_huesped'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['error' => 'Email no válido']);
            return;
        }
        if (!is_numeric($datosReserva['importe']) || $datosReserva['importe'] < 0) {
            http_response_code(422);
            echo json_encode(['error' => 'Importe no válido']);
            return;
        }
        if ($this->validarFecha($datosReserva['fecha_entrada']) == null || $this->validarFecha($datosReserva['fecha_salida']) == null) {
            http_response_code(422);
            echo json_encode(['error' => 'Fechas no válidas']);
            return;

        }
        if ($datosReserva['fecha_entrada'] >= $datosReserva['fecha_salida']) {
            http_response_code(422);
            echo json_encode(['error' => 'Fechas no válidas']);
            return;
        }

        try {
            $repository = new ReservaRepository();
            $idReserva = $repository->crear(
                $datosReserva['nombre_huesped'],
                $datosReserva['email_huesped'],
                $datosReserva['nombre_alojamiento'],
                $datosReserva['fecha_entrada'],
                $datosReserva['fecha_salida'],
                $datosReserva['importe'],
                $datosReserva['notas'] ?? null
            );
            http_response_code(201);
            echo json_encode(['mensaje' => 'Reserva creada correctamente', 'id' => $idReserva]);
        } catch (\Throwable $th) {

            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear la reserva']);
        }
    }
    public function detalle($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Metodo no permitido']);
            return;
        }
        try {
            $repository = new ReservaRepository();
            $reserva = $repository->detalle($id);
            if (!$reserva) {
                http_response_code(404);
                echo json_encode(['error' => 'Reserva no encontrada']);
                return;
            }
            http_response_code(200);
            echo json_encode($reserva);
        } catch (\Throwable $th) {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo encontrar la reserva']);
        }
    }
    public function cambiarEstado($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            http_response_code(405);
            echo json_encode(['error' => 'Metodo no permitido']);
            return;
        }
        $datos = json_decode(file_get_contents('php://input'), true);
        if (!is_array($datos) || !isset($datos['estado'])) {
            http_response_code(422);
            echo json_encode(['error' => 'El estado es obligatorio']);
            return;
        }
        $estados = ['pendiente', 'confirmada', 'cancelada'];
        if (!in_array($datos['estado'], $estados, true)) {
            http_response_code(422);
            echo json_encode(['error' => 'Estado no válido']);
            return;
        }
        try {
            $repository = new ReservaRepository();
            $reserva = $repository->cambiarEstado($id, $datos['estado']);
            if (!$reserva) {
                http_response_code(404);
                echo json_encode(['error' => 'Reserva no encontrada']);
                return;
            }
            http_response_code(200);
            echo json_encode(['mensaje' => 'Estado cambiado', 'estado' => 'Estado cambiado a ' . $datos['estado'], 'id' => $id]);

        } catch (\Throwable $th) {

            http_response_code(500);
            echo json_encode(['error' => 'No se pudo cambiar el estado']);
        }
    }
    public function filtrar()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Metodo no permitido']);
            return;
        }
        if ((isset($_GET['fecha_entrada']) && $this->validarFecha($_GET['fecha_entrada']) == null) || (isset($_GET['fecha_salida']) && $this->validarFecha($_GET['fecha_salida']) == null)) {
            http_response_code(422);
            echo json_encode(['error' => 'Fechas no válidas']);
            return;

        }
        if (isset($_GET['fecha_entrada']) && isset($_GET['fecha_salida'])) {
            if ($_GET['fecha_entrada'] >= $_GET['fecha_salida']) {
                http_response_code(422);
                echo json_encode(['error' => 'Fechas no válidas']);
                return;
            }
        }

        try {
            $repository = new ReservaRepository();
            $reserva = $repository->filtrar($_GET['estado'] ?? null, $_GET['nombre_huesped'] ?? null, $_GET['fecha_entrada'] ?? null, $_GET['fecha_salida'] ?? null);
            if ($reserva === null) {
                http_response_code(422);
                echo json_encode(['error' => 'Estado no válido']);
                return;
            }
            http_response_code(200);
            echo json_encode($reserva);
        } catch (\Throwable $th) {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo encontrar la reserva']);
        }
    }
}