<?php
declare(strict_types=1);

namespace App\Repositories;

use Core\Database;

class CompraRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Obtener todos los registros de compras
    public function findAll(): array
    {
        $sql = "SELECT * FROM compras ORDER BY fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Obtener compra por su ID
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM compras WHERE id_compra = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Obtener todas las compras de un cliente
    public function findByCliente(int $clienteId, ?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $sql = "SELECT * FROM compras WHERE id_cliente = :id";

        // Filtrado por fechas si se pasan
        $params = ['id' => $clienteId];
        if ($fechaInicio) {
            $sql .= " AND fecha >= :fechaInicio";
            $params['fechaInicio'] = $fechaInicio;
        }
        if ($fechaFin) {
            $sql .= " AND fecha <= :fechaFin";
            $params['fechaFin'] = $fechaFin;
        }

        $sql .= " ORDER BY fecha DESC, hora DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    // Crear una nueva compra y devolver el ID generado
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO compras (id_cliente, fecha, hora, monto, estado_pago)
            VALUES (:id_cliente, :fecha, :hora, :monto, :estado_pago)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    // Obtener cliente por id_usuario
    public function findClienteByUsuario(int $usuarioId): ?array
    {
        $sql = "SELECT * FROM clientes WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $usuarioId]);
        $cliente = $stmt->fetch();
        return $cliente ?: null;
    }

    // Registrar detalle de compra para un ticket específico
    public function addDetalle(int $compraId, int $ticketId, float $precioUnitario, int $cantidad = 1): void
    {
        $sql = "INSERT INTO detalle_compra
                (id_compra, id_ticket, precio_unitario, cantidad)
                VALUES (:compra, :ticket, :precio, :cantidad)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':compra'  => $compraId,
            ':ticket'  => $ticketId,
            ':precio'  => $precioUnitario,
            ':cantidad'=> $cantidad,
        ]);
    }

    /**
     * Devuelve la cantidad de tickets vendidos para un recorrido en fecha/hora.
     */
    public function countTicketsSold(int $recorridoId, string $fecha, string $hora): int
    {
        $sql = "SELECT COUNT(*) FROM tickets WHERE id_recorrido = :recorrido AND fecha = :fecha AND hora = :hora";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':recorrido' => $recorridoId,
            ':fecha'      => $fecha,
            ':hora'       => $hora,
        ]);
        return (int) $stmt->fetchColumn();
    }
}