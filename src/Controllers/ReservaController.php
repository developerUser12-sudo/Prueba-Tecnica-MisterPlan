<?php
require_once __DIR__ . '/../Database/Conexion.php';
class ReservaController
{
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
        try {
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();
            $consulta = $pdo->prepare('INSERT INTO reserva (
                nombre_huesped,
                email_huesped,
                nombre_alojamiento,
                fecha_entrada,
                fecha_salida,
                importe,
                notas
            ) VALUES (
                :nombre,
                :email,
                :alojamiento,
                :entrada,
                :salida,
                :importe,
                :notas
            )');
            $consulta->execute([
                'nombre' => trim($datosReserva['nombre_huesped']),
                'email' => trim($datosReserva['email_huesped']),
                'alojamiento' => trim($datosReserva['nombre_alojamiento']),
                'entrada' => $datosReserva['fecha_entrada'],
                'salida' => $datosReserva['fecha_salida'],
                'importe' => $datosReserva['importe'],
                'notas' => $datosReserva['notas'] ?? null
            ]);
            $idReserva = (int) $pdo->lastInsertId();
            $consultaEvento = $pdo->prepare(
                'INSERT INTO reserva_evento
                    (reserva_id, tipo, descripcion)
                 VALUES
                    (:reserva_id, :tipo, :descripcion)'
            );
            $consultaEvento->execute([
                'reserva_id' => $idReserva,
                'tipo' => 'creacion',
                'descripcion' => 'Reserva creada'
            ]);

            $pdo->commit();
            http_response_code(201);
            echo json_encode(['mensaje' => 'Reserva creada correctamente', 'id' => $idReserva]);
        } catch (\Throwable $th) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log($th->getMessage());
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
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();
            $consulta = $pdo->prepare('SELECT * FROM reserva WHERE id=:id');
            $consulta->execute(['id' => $id]);
            $reserva = $consulta->fetch();
            if (!$reserva) {
                http_response_code(404);
                echo json_encode(['error' => 'Reserva no encontrada']);
                return;
            }
            http_response_code(200);
            echo json_encode($reserva);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
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
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();
            $consultaSelect = $pdo->prepare('SELECT * FROM reserva WHERE id=:id');
            $consultaSelect->execute(['id' => $id]);
            $reserva = $consultaSelect->fetch();
            if (!$reserva) {
                http_response_code(404);
                echo json_encode(['error' => 'Reserva no encontrada']);
                return;
            }
            $consultaUpdate = $pdo->prepare('UPDATE  reserva SET estado=:estado WHERE id=:id');
            $consultaUpdate->execute(['estado' => $datos['estado'], 'id' => $id]);
            $consultaEvento = $pdo->prepare('INSERT INTO reserva_evento (reserva_id, tipo, descripcion) VALUES (:id,:tipo,:descripcion)');
            $consultaEvento->execute(['id' => $id, 'tipo' => $datos['estado'], 'descripcion' => 'Estado cambiado de ' . $reserva['estado'] . ' a ' . $datos['estado']]);
            $pdo->commit();
            http_response_code(200);
            echo json_encode(['mensaje' => 'Estado cambiado', 'estado' => $datos['estado'], 'id' => $id]);

        } catch (\Throwable $th) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log($th->getMessage());
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
        $sql = 'SELECT * FROM reserva';
        $parametros = [];
        $condiciones = [];
        if (isset($_GET['estado']) && $_GET['estado'] != '') {
            $estados = ['pendiente', 'confirmada', 'cancelada'];
            if (!in_array($_GET['estado'], $estados, true)) {
                http_response_code(422);
                echo json_encode(['error' => 'Estado no válido']);
                return;
            }
            $condiciones[] = 'estado=:estado';
            $parametros['estado'] = $_GET['estado'];
        }
        if (isset($_GET['nombre_huesped']) && $_GET['nombre_huesped'] != '') {
            $condiciones[] = 'nombre_huesped LIKE :nombre_huesped';
            $parametros['nombre_huesped'] = '%' . trim($_GET['nombre_huesped']) . '%';
        }
        if (isset($_GET['fecha_entrada']) && $_GET['fecha_entrada'] != '') {
            $condiciones[] = 'fecha_entrada>=:fecha_entrada';
            $parametros['fecha_entrada'] = $_GET['fecha_entrada'];
        }
        if (isset($_GET['fecha_salida']) && $_GET['fecha_salida'] != '') {
            $condiciones[] = 'fecha_salida<=:fecha_salida';
            $parametros['fecha_salida'] = $_GET['fecha_salida'];
        }

        if (!empty($condiciones)) {
            $sql.=' WHERE '.implode(' AND ',$condiciones);
        }
        try {
            $pdo = Conexion::conectar();
            $consulta = $pdo->prepare($sql);
            $consulta->execute($parametros);
            $reserva = $consulta->fetchAll();
            http_response_code(200);
            echo json_encode($reserva);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo encontrar la reserva']);
        }
    }
}