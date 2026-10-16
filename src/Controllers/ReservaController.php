<?php
require_once __DIR__ . '/../Database/Conexion.php';
class ReservaController
{
    public function crear()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
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
            if (!isset($datos[$campo])) {
                http_response_code(422);
                echo json_encode(['error' => 'Falta un campo obligatorio']);
                return;
            }
        }
        try {
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO reserva (
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
            $stmt->execute([
                'nombre' => trim($datosReserva['nombre_huesped']),
                'email' => trim($datosReserva['email_huesped']),
                'alojamiento' => trim($datosReserva['nombre_alojamiento']),
                'entrada' => $datosReserva['fecha_entrada'],
                'salida' => $datosReserva['fecha_salida'],
                'importe' => $datosReserva['importe'],
                'notas' => $datosReserva
            ]);
            $idReserva = (int) $pdo->lastInsertId();
            $stmtEvento = $pdo->prepare(
                'INSERT INTO reserva_evento
                    (reserva_id, tipo, descripcion)
                 VALUES
                    (:reserva_id, :tipo, :descripcion)'
            );
            $stmtEvento->execute([
                'reserva_id' => $idReserva,
                'tipo' => 'creacion',
                'descripcion' => 'Reserva creada'
            ]);

            $pdo->commit();
            http_response_code(201);
            echo json_encode(['mensaje' => 'Reserva creada correctamente']);
        } catch (\Throwable $th) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log($th->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear la reserva']);
        }
    }
}