<?php
// app/Repositories/GuiaRepository.php
declare(strict_types=1);

namespace App\Repositories;

use Core\Database;

class GuiaRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todos los recorridos asignados al guía autenticado.
     * Cuenta personas por TICKETS comprados.
     */
    public function getRecorridosAsignados(int $id_usuario): array
    {
        $sql = "
            SELECT 
                gr.fecha_asignacion,
                gr.id_guia_recorrido,
                r.id_recorrido,
                r.nombre,
                r.tipo,
                r.precio,
                r.duracion,
                r.capacidad,
                COUNT(DISTINCT t.id_ticket) AS personas_asignadas,
                EXISTS(SELECT 1 FROM reportes WHERE id_guia_recorrido = gr.id_guia_recorrido) AS tiene_reporte
            FROM guia_recorrido gr
            INNER JOIN guias g       ON g.id_guia       = gr.id_guia
            INNER JOIN recorridos r  ON r.id_recorrido  = gr.id_recorrido
            LEFT  JOIN tickets   t   ON t.id_recorrido  = r.id_recorrido
            WHERE g.id_usuario = :id_usuario
            GROUP BY gr.id_guia_recorrido, r.id_recorrido, gr.fecha_asignacion
            ORDER BY gr.fecha_asignacion ASC, r.nombre ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los datos de horarios y días de trabajo del guía autenticado.
     */
    public function getHorariosGuia(int $id_usuario): ?array
    {
        $stmt = $this->db->prepare("
            SELECT horarios, dias_trabajo
            FROM guias
            WHERE id_usuario = :id_usuario
            LIMIT 1
        ");

        $stmt->execute([':id_usuario' => $id_usuario]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Obtiene los recorridos asignados al guía en un rango de fechas.
     * Devuelve agrupados por fecha: [ 'YYYY-MM-DD' => [ [...], ... ], ... ]
     */
    public function getRecorridosPorSemana(int $id_usuario, string $fechaInicio, string $fechaFin): array
    {
        $sql = "
            SELECT
                gr.fecha_asignacion,
                r.id_recorrido,
                r.nombre,
                r.tipo,
                r.duracion,
                r.capacidad,
                COUNT(DISTINCT t.id_ticket) AS personas_asignadas
            FROM guia_recorrido gr
            INNER JOIN guias g      ON g.id_guia      = gr.id_guia
            INNER JOIN recorridos r ON r.id_recorrido = gr.id_recorrido
            LEFT  JOIN tickets   t  ON t.id_recorrido = r.id_recorrido
            WHERE g.id_usuario          = :id_usuario
              AND gr.fecha_asignacion  BETWEEN :fecha_inicio AND :fecha_fin
            GROUP BY gr.id_guia_recorrido, r.id_recorrido, gr.fecha_asignacion
            ORDER BY gr.fecha_asignacion ASC, r.nombre ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_usuario'   => $id_usuario,
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin'    => $fechaFin,
        ]);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Agrupar por fecha
        $porFecha = [];
        foreach ($rows as $row) {
            $porFecha[$row['fecha_asignacion']][] = $row;
        }

        return $porFecha;
    }

    /**
     * Obtiene las áreas que cubre un recorrido específico.
     */
    public function getAreasPorRecorrido(int $id_recorrido): array
    {
        $stmt = $this->db->prepare("
            SELECT a.id_area, a.nombre, a.descripcion, a.restringida
            FROM recorrido_area ra
            INNER JOIN areas a ON a.id_area = ra.id_area
            WHERE ra.id_recorrido = :id_recorrido
            ORDER BY a.nombre ASC
        ");

        $stmt->execute([':id_recorrido' => $id_recorrido]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un recorrido específico validando que pertenezca al guía.
     */
    public function getDetalleRecorrido(int $id_recorrido, int $id_usuario): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
    r.id_recorrido,
    r.nombre,
    r.tipo,
    r.precio,
    r.duracion,
    r.capacidad,
    gr.fecha_asignacion,
    COUNT(DISTINCT t.id_ticket) AS personas_asignadas,
    
    GROUP_CONCAT(
        DISTINCT CONCAT_WS(' ', u.nombre1, u.apellido1)
        SEPARATOR ', '
    ) AS nombres_compradores

FROM guia_recorrido gr
INNER JOIN guias g          ON g.id_guia      = gr.id_guia
INNER JOIN recorridos r     ON r.id_recorrido = gr.id_recorrido
LEFT  JOIN tickets t        ON t.id_recorrido = r.id_recorrido
LEFT  JOIN compras c         ON c.id_compra    = t.id_compra
LEFT  JOIN clientes cl       ON cl.id_cliente  = c.id_cliente
LEFT  JOIN usuarios u       ON u.id_usuario   = cl.id_usuario

WHERE r.id_recorrido = :id_recorrido
  AND g.id_usuario   = :id_usuario

GROUP BY r.id_recorrido, gr.fecha_asignacion
LIMIT 1
        ");

        $stmt->execute([
            ':id_recorrido' => $id_recorrido,
            ':id_usuario'   => $id_usuario,
        ]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}