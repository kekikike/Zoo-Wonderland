<?php
declare(strict_types=1);

namespace App\Repositories;

use Core\Database;

/**
 * Repositorio de tickets persistente en base de datos.
 */
class TicketRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Cuenta tickets vendidos para un recorrido/fecha/hora.
     */
    public function countByRecorridoFechaHora(int $recorridoId, string $fecha, string $hora): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as cnt FROM tickets WHERE id_recorrido = :r AND fecha = :f AND hora = :h"
        );
        $stmt->execute([':r' => $recorridoId, ':f' => $fecha, ':h' => $hora]);
        $row = $stmt->fetch();
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Crea un ticket y devuelve su id.
     */
    public function create(int $compraId, int $recorridoId, string $fecha, string $hora, string $codigoQR): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tickets (hora, fecha, codigo_qr, id_compra, id_recorrido) VALUES (:h, :f, :c, :comp, :rec)"
        );
        $stmt->execute([
            ':h'   => $hora,
            ':f'   => $fecha,
            ':c'   => $codigoQR,
            ':comp'=> $compraId,
            ':rec' => $recorridoId
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findByCompra(int $compraId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM tickets WHERE id_compra = :c");
        $stmt->execute([':c' => $compraId]);
        return $stmt->fetchAll();
    }

    public function findByCodigoQR(string $codigo): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tickets WHERE codigo_qr = :c LIMIT 1");
        $stmt->execute([':c' => $codigo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM tickets WHERE id_ticket = :id");
        return $stmt->execute([':id' => $id]);
    }
}