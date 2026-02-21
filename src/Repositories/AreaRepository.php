<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Area;

/**
 * Repositorio de áreas (simulado en memoria)
 */
class AreaRepository
{
    private array $areas = [];
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
            ['Zona General', false, 'Área abierta al público'],
            ['Área de Felinos', true, 'Zona restringida'],
            ['Área de Osos', true, 'Zona restringida'],
            ['Área de Acuario', true, 'Zona protegida'],
            ['Zona de Aves', true, 'Área cerrada al público'],
            ['Zona Interactiva', true, 'Área de interacción con los animales'],

        ];

        foreach ($datos as $data) {

            [$nombre, $restringida, $descripcion] = $data;

            $area = new Area(
                $this->nextId++,
                $nombre,
                $restringida,
                $descripcion
            );

            $this->areas[$area->getId()] = $area;
        }
    }

    /**
     * Obtiene todas las áreas
     */
    public function findAll(): array
    {
        return array_values($this->areas);
    }

    /**
     * Busca por ID
     */
    public function findById(int $id): ?Area
    {
        return $this->areas[$id] ?? null;
    }

    /**
     * Busca por tipo (restringida / no restringida)
     */
    public function findByRestriccion(bool $restringida): array
    {
        return array_filter(
            $this->areas,
            fn($a) => $a->isRestringida() === $restringida
        );
    }

    /**
     * Búsqueda general
     */
    public function search(string $query): array
    {
        return array_filter($this->areas, function ($area) use ($query) {

            $info = implode(' ', $area->getInfo());

            return str_contains(
                strtolower($info),
                strtolower($query)
            );
        });
    }

    /**
     * Elimina área
     */
    public function delete(int $id): bool
    {
        if (!isset($this->areas[$id])) {
            return false;
        }

        unset($this->areas[$id]);
        return true;
    }
}