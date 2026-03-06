<?php
namespace App\Models;
class Area
{
    private int $id;
    private string $nombre;
    private bool $restringida;
    private array $animales = [];

    public function __construct(
        int $id,
        string $nombre,
        bool $restringida
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->restringida = $restringida;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function agregarAnimal(Animal $animal): void
    {
        $this->animales[] = $animal;
    }

    public function getAnimales(): array
    {
        return $this->animales;
    }

    public function getInfo(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'restringida' => $this->restringida
        ];
    }
}