<?php
declare(strict_types=1);

namespace App\Models;


class Reserva {

    private int $id;
    private string $hora;
    private string $fecha;
    private int $cupos;
    private string $institucion;
    private Recorrido $recorrido;

    public function __construct(
        int $id,
        string $hora,
        string $fecha,
        int $cupos,
        string $institucion,
        Recorrido $recorrido
    ) {
        $this->id = $id;
        $this->hora = $hora;
        $this->fecha = $fecha;
        $this->cupos = $cupos;
        $this->institucion = $institucion;
        $this->recorrido = $recorrido;
    }

    // Getters

    public function getId(): int {
        return $this->id;
    }

    public function getHora(): string {
        return $this->hora;
    }

    public function getFecha(): string {
        return $this->fecha;
    }

    public function getCupos(): int {
        return $this->cupos;
    }

    public function getInstitucion(): string {
        return $this->institucion;
    }

    public function getRecorrido(): Recorrido {
        return $this->recorrido;
    }

    // Métodos

    public function editarReserva(
        string $hora,
        string $fecha,
        int $cupos
    ): void {
        $this->hora = $hora;
        $this->fecha = $fecha;
        $this->cupos = $cupos;
    }

    public function mostrarReserva(): string {
        return "Reserva {$this->id} - {$this->institucion} ({$this->fecha})";
    }
}