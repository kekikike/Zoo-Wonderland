<?php
declare(strict_types=1);

namespace App\Repositories;

use Core\Database;
use App\Models\Reporte;

class ReporteRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Guarda un nuevo reporte en la base de datos.
     */
    public function save(int $id_guia_recorrido, string $observaciones): bool
    {
        $sql = "INSERT INTO reportes (id_guia_recorrido, observaciones) VALUES (:id_gr, :obs)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_gr' => $id_guia_recorrido,
            ':obs'   => $observaciones
        ]);
    }

    /**
     * Obtiene el reporte asociado a un recorrido asignado.
     */
    public function findByGuiaRecorrido(int $id_guia_recorrido): ?array
    {
        $sql = "SELECT * FROM reportes WHERE id_guia_recorrido = :id_gr LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_gr' => $id_guia_recorrido]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Obtiene todos los reportes realizados por un guía.
     */
    public function getReportesPorGuia(int $id_usuario): array
    {
        $sql = "
            SELECT 
                rep.*,
                r.nombre AS recorrido_nombre,
                gr.fecha_asignacion
            FROM reportes rep
            INNER JOIN guia_recorrido gr ON gr.id_guia_recorrido = rep.id_guia_recorrido
            INNER JOIN guias g          ON g.id_guia           = gr.id_guia
            INNER JOIN recorridos r     ON r.id_recorrido      = gr.id_recorrido
            WHERE g.id_usuario = :id_usuario
            ORDER BY rep.fecha_reporte DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene recorridos asignados a un guía que YA SE REALIZARON y NO tienen reporte
     */
    public function getRecorridosSinReportePorGuia(int $id_usuario): array
    {
        $sql = "
            SELECT 
                gr.id_guia_recorrido,
                gr.id_guia,
                gr.id_recorrido,
                gr.fecha_asignacion,
                r.nombre AS recorrido_nombre,
                r.tipo,
                COUNT(t.id_ticket) AS total_tickets,
                SUM(CASE WHEN c.estado_pago = 1 THEN 1 ELSE 0 END) AS tickets_confirmados
            FROM guia_recorrido gr
            INNER JOIN guias g ON g.id_guia = gr.id_guia
            INNER JOIN recorridos r ON r.id_recorrido = gr.id_recorrido
            LEFT JOIN tickets t ON t.id_recorrido = gr.id_recorrido
            LEFT JOIN compras c ON c.id_compra = t.id_compra
            WHERE g.id_usuario = :id_usuario
            AND DATE(gr.fecha_asignacion) <= CURDATE()
            AND NOT EXISTS (
                SELECT 1 FROM reportes rep 
                WHERE rep.id_guia_recorrido = gr.id_guia_recorrido
            )
            GROUP BY gr.id_guia_recorrido
            HAVING tickets_confirmados > 0
            ORDER BY gr.fecha_asignacion DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Valida si existe reporte para un id_guia_recorrido
     */
    public function existeReporte(int $id_guia_recorrido): bool
    {
        $sql = "SELECT COUNT(*) as cnt FROM reportes WHERE id_guia_recorrido = :id_gr LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_gr' => $id_guia_recorrido]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($result['cnt'] ?? 0) > 0;
    }

    /**
     * Obtiene detalles completos de un recorrido asignado
     */
    public function getDetalleGuiaRecorrido(int $id_guia_recorrido): ?array
    {
        $sql = "
            SELECT 
                gr.id_guia_recorrido,
                gr.id_guia,
                gr.id_recorrido,
                gr.fecha_asignacion,
                r.nombre AS recorrido_nombre,
                r.tipo,
                COUNT(t.id_ticket) AS total_tickets,
                SUM(CASE WHEN c.estado_pago = 1 THEN 1 ELSE 0 END) AS tickets_confirmados
            FROM guia_recorrido gr
            INNER JOIN recorridos r ON r.id_recorrido = gr.id_recorrido
            LEFT JOIN tickets t ON t.id_recorrido = gr.id_recorrido
            LEFT JOIN compras c ON c.id_compra = t.id_compra
            WHERE gr.id_guia_recorrido = :id_gr
            GROUP BY gr.id_guia_recorrido
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_gr' => $id_guia_recorrido]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
