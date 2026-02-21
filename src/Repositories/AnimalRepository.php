<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Animal;

/**
 * Repositorio de animales (simulado en memoria)
 */
class AnimalRepository
{
    private array $animales = [];
    private int $nextId = 1;

    public function __construct()
    {
        $this->seedData();
    }

    /**
     * Datos de prueba
     */
    private function seedData(): void
    {
        $datos = [
            ['León', 'León Africano', 'Sabana', 'Rey de la selva', 'Activo', 1],
            ['Oso', 'Oso Pardo', 'Bosque', 'Gran tamaño', 'Activo', 2],
            ['Tigre', 'Tigre de Bengala', 'Selva', 'Depredador', 'Activo', 1],
            ['Mono', 'Mono Capuchino', 'Selva', 'Ágil', 'En observación', 3],
        ];

        foreach ($datos as $data) {

            [$especie, $nombre, $habitat, $desc, $estado, $areaId] = $data;

            $animal = new Animal(
                $this->nextId++,
                $especie,
                $nombre,
                $habitat,
                $desc,
                $estado,
                $areaId
            );

            $this->animales[$animal->getId()] = $animal;
        }
    }

    /**
     * Obtiene todos
     */
    public function findAll(): array
    {
        return array_values($this->animales);
    }

    /**
     * Busca por ID
     */
    public function findById(int $id): ?Animal
    {
        return $this->animales[$id] ?? null;
    }

    /**
     * Busca por área
     */
    public function findByArea(int $areaId): array
    {
        return array_filter(
            $this->animales,
            fn($a) => $a->getAreaId() === $areaId
        );
    }

    /**
     * Busca por estado
     */
    public function findByEstado(string $estado): array
    {
        return array_filter(
            $this->animales,
            fn($a) => $a->getInfo()['estado'] === $estado
        );
    }

    /**
     * Búsqueda general
     */
    public function search(string $query): array
    {
        return array_filter($this->animales, function ($animal) use ($query) {

            $info = implode(' ', $animal->getInfo());

            return str_contains(
                strtolower($info),
                strtolower($query)
            );
        });
    }

    /**
     * Elimina animal
     */
    public function delete(int $id): bool
    {
        if (!isset($this->animales[$id])) {
            return false;
        }

        unset($this->animales[$id]);
        return true;
    }
}