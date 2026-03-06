<?php
declare(strict_types=1);

namespace App\Repositories;

use Core\Database;

/**
 * Repositorio de reservas grupales persistido en la base de datos.
 */
class ReservaRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Inserta una nueva reserva y devuelve el ID generado.
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO reservas (fecha, hora, cupos, institucion, comentario, estado_pago, estado, id_cliente, id_recorrido) 
                VALUES (:fecha, :hora, :cupos, :institucion, :comentario, :estado_pago, :estado, :cliente, :recorrido)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':fecha'       => $data['fecha'],
            ':hora'        => $data['hora'],
            ':cupos'       => $data['cupos'],
            ':institucion' => $data['institucion'],
            ':comentario'  => $data['comentario'] ?? null,
            ':estado_pago' => $data['estado_pago'] ?? 0,
            ':estado'      => $data['estado'] ?? 1,
            ':cliente'     => $data['id_cliente'],
            ':recorrido'   => $data['id_recorrido'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Obtiene todas las reservas (opcionalmente filtradas por cliente).
     */
    public function findAll(int $clienteId = null): array
    {
        if ($clienteId) {
            $stmt = $this->db->prepare("SELECT * FROM reservas WHERE id_cliente = :c ORDER BY fecha DESC, hora DESC");
            $stmt->execute([':c' => $clienteId]);
        } else {
            $stmt = $this->db->query("SELECT * FROM reservas ORDER BY fecha DESC, hora DESC");
        }
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM reservas WHERE id_reserva = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByCliente(int $clienteId): array
    {
        return $this->findAll($clienteId);
    }

    /**
     * Retorna todas las reservas junto con sus datos extra almacenados en sesión.
     */
    public function findAllWithExtras(int $clienteId = null): array
    {
        $reservas = $this->findAll($clienteId);
        $extras = $_SESSION['zoo_reservas_extras'] ?? [];
        return array_map(function ($r) use ($extras) {
            return ['reserva' => $r, 'extras' => $extras[$r['id_reserva']] ?? []];
        }, $reservas);
    }

    /**
     * Guarda los datos extra de una reserva en sesión.
     */
    public function saveExtras(int $reservaId, array $datos): void
    {
        if (!isset($_SESSION['zoo_reservas_extras'])) {
            $_SESSION['zoo_reservas_extras'] = [];
        }
        $_SESSION['zoo_reservas_extras'][$reservaId] = $datos;
    }

    /**
     * Cuenta cantidad total de cupos ya reservados para un recorrido en fecha/hora.
     */
    public function countByRecorridoFechaHora(int $recorridoId, string $fecha, string $hora): int
    {
        $stmt = $this->db->prepare("SELECT SUM(cupos) FROM reservas WHERE id_recorrido = :recorrido AND fecha = :fecha AND hora = :hora");
        $stmt->execute([
            ':recorrido' => $recorridoId,
            ':fecha'      => $fecha,
            ':hora'       => $hora,
        ]);
        return (int)$stmt->fetchColumn();
    }
}
