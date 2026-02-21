<?php
declare(strict_types=1);

namespace App\Models;

class Recorrido {

    private int $id;
    private string $nombre;
    private string $tipo;
    private float $precio;
    private int $duracion;
    private int $capacidad;
    private array $areas = [];

    public function __construct(
        int $id,
        string $nombre,
        string $tipo,
        float $precio,
        int $duracion,
        int $capacidad
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->precio = $precio;
        $this->duracion = $duracion;
        $this->capacidad = $capacidad;
    }

    // Getters

    public function getId(): int {
        return $this->id;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    public function getTipo(): string {
        return $this->tipo;
    }

    public function getPrecio(): float {
        return $this->precio;
    }

    public function getDuracion(): int {
        return $this->duracion;
    }

    public function getCapacidad(): int {
        return $this->capacidad;
    }

    public function getAreas(): array {
        return $this->areas;
    }

    // Métodos

    public function agregarArea(Area $area): void {
        $this->areas[] = $area;
    }

    public function getInfo(): string {
        return "{$this->nombre} - {$this->tipo} (Bs. {$this->precio})";
    }
}