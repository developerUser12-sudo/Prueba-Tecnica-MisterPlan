<?php
require_once __DIR__ . '/../Database/Conexion.php';

class ReservaRepository
{
    private PDO $pdo;
    public function __construct()
    {
        $this->pdo = Conexion::conectar();
    }
    public function crear($nombreHuesped, $emailHuesped, $nombreAlojamiento, $fechaEntrada, $fechaSalida, $importe, $notas)
    {
        $this->pdo->beginTransaction();
        try {
            $consulta = $this->pdo->prepare('INSERT INTO reserva (
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
                'nombre' => trim($nombreHuesped),
                'email' => trim($emailHuesped),
                'alojamiento' => trim($nombreAlojamiento),
                'entrada' => $fechaEntrada,
                'salida' => $fechaSalida,
                'importe' => $importe,
                'notas' => $notas
            ]);
            $idReserva = (int) $this->pdo->lastInsertId();
            $consultaEvento = $this->pdo->prepare(
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
            $this->pdo->commit();
            return $idReserva;
        } catch (\Throwable $th) {
            if (isset($this->pdo) && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log($th->getMessage());
            throw $th;
        }
    }
    public function detalle($id)
    {
        try {
            $consulta = $this->pdo->prepare('SELECT * FROM reserva WHERE id=:id');
            $consulta->execute(['id' => $id]);
            return $consulta->fetch();
        } catch (\Throwable $th) {

            error_log($th->getMessage());
            throw $th;
        }
    }
    public function cambiarEstado($id, $estado)
    {
        $this->pdo->beginTransaction();
        try {

            $consultaSelect = $this->pdo->prepare('SELECT * FROM reserva WHERE id=:id');
            $consultaSelect->execute(['id' => $id]);
            $reserva = $consultaSelect->fetch();
            if (!$reserva) {
                $this->pdo->rollBack();
                return false;
            }
            $consultaUpdate = $this->pdo->prepare('UPDATE  reserva SET estado=:estado WHERE id=:id');
            $consultaUpdate->execute(['estado' => $estado, 'id' => $id]);
            $consultaEvento = $this->pdo->prepare('INSERT INTO reserva_evento (reserva_id, tipo, descripcion) VALUES (:id,:tipo,:descripcion)');
            $consultaEvento->execute(['id' => $id, 'tipo' => $estado, 'descripcion' => 'Estado cambiado de ' . $reserva['estado'] . ' a ' . $estado]);
            $this->pdo->commit();
            return true;
        } catch (\Throwable $th) {
            if (isset($this->pdo) && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log($th->getMessage());
            throw $th;
        }
    }
    public function filtrar($estado, $nombreHuesped, $fechaEntrada, $fechaSalida)
    {
        $sql = 'SELECT * FROM reserva';
        $parametros = [];
        $condiciones = [];
        if ($estado != null) {
            $estados = ['pendiente', 'confirmada', 'cancelada'];
            if (!in_array($estado, $estados, true)) {
                return null;
            }
            $condiciones[] = 'estado=:estado';
            $parametros['estado'] = $estado;
        }
        if ($nombreHuesped != null) {
            $condiciones[] = 'nombre_huesped LIKE :nombre_huesped';
            $parametros['nombre_huesped'] = '%' . trim($nombreHuesped) . '%';
        }
        if ($fechaEntrada != null) {
            $condiciones[] = 'fecha_entrada>=:fecha_entrada';
            $parametros['fecha_entrada'] = $fechaEntrada;
        }
        if ($fechaSalida != null) {
            $condiciones[] = 'fecha_salida<=:fecha_salida';
            $parametros['fecha_salida'] = $fechaSalida;
        }

        if (!empty($condiciones)) {
            $sql .= ' WHERE ' . implode(' AND ', $condiciones);
        }

        try {
            $consulta = $this->pdo->prepare($sql);
            $consulta->execute($parametros);
            $reserva = $consulta->fetchAll();
            return $reserva;
        } catch (\Throwable $th) {
            if (isset($this->pdo) && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log($th->getMessage());
            throw $th;
        }
    }
    public function obtenerEventos($id)
    {
        $consulta = $this->pdo->prepare('SELECT * FROM reserva_evento WHERE reserva_id=:id ORDER BY fecha_creacion ASC, id ASC');
        $consulta->execute(['id' => $id]);
        return $consulta->fetchAll();
    }
}