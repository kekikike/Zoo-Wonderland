<?php

namespace App\Models;
class Animal
{
    private int $id;
    private string $especie;
    private string $nombreComun;
    private string $habitat;
    private string $descripcion;
    private string $estado;
    private int $areaId;

    public function __construct(
        int $id,
        string $especie,
        string $nombreComun,
        string $habitat,
        string $descripcion,
        string $estado,
        int $areaId
    ) {
        $this->id = $id;
        $this->especie = $especie;
        $this->nombreComun = $nombreComun;
        $this->habitat = $habitat;
        $this->descripcion = $descripcion;
        $this->estado = $estado;
        $this->areaId = $areaId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAreaId(): int
    {
        return $this->areaId;
    }

    public function getInfo(): array
    {
        return [
            'id' => $this->id,
            'especie' => $this->especie,
            'nombre' => $this->nombreComun,
            'habitat' => $this->habitat,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'areaId' => $this->areaId
        ];
    }

    public function actualizarEstado(string $estado): void
    {
        $this->estado = $estado;
    }
}