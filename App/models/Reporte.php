<?php
declare(strict_types=1);

namespace App\Models;

class Reporte
{
    private int $id_reporte;
    private int $id_guia_recorrido;
    private string $observaciones;
    private string $fecha_reporte;

    public function __construct(
        int $id_reporte,
        int $id_guia_recorrido,
        string $observaciones,
        string $fecha_reporte
    ) {
        $this->id_reporte        = $id_reporte;
        $this->id_guia_recorrido = $id_guia_recorrido;
        $this->observaciones     = $observaciones;
        $this->fecha_reporte     = $fecha_reporte;
    }

    // Getters
    public function getIdReporte(): int { return $this->id_reporte; }
    public function getIdGuiaRecorrido(): int { return $this->id_guia_recorrido; }
    public function getObservaciones(): string { return $this->observaciones; }
    public function getFechaReporte(): string { return $this->fecha_reporte; }
}
