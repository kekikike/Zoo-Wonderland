<?php
declare(strict_types=1);

namespace App\Models;

class Ticket {

    private int $id;
    private string $hora;
    private string $fecha;
    private string $codigoQR;
    private Recorrido $recorrido;

    public function __construct(
        int $id,
        string $hora,
        string $fecha,
        string $codigoQR,
        Recorrido $recorrido
    ) {
        $this->id = $id;
        $this->hora = $hora;
        $this->fecha = $fecha;
        $this->codigoQR = $codigoQR;
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

    public function getCodigoQR(): string {
        return $this->codigoQR;
    }

    public function getRecorrido(): Recorrido {
        return $this->recorrido;
    }

    // Métodos

    public function getInfo(): string {
        return "Ticket #{$this->id} - {$this->fecha} {$this->hora}";
    }
}