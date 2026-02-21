<?php
declare(strict_types=1);

namespace App\Repositories;

/**
 * Repositorio de recorridos (simulado en memoria)
 */
class RecorridoRepository
{
    private array $recorridos = [];
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
            ['Recorrido General', 'No Guiado', 15, 60, 50],
            ['Felinos VIP', 'Guiado', 50, 60, 50],
            ['Osos Andinos', 'Guiado', 45, 30, 50],
            ['Condores', 'Guiado', 40, 30, 50],
            ['Acuario', 'Guiado', 20, 30, 50],
            ['Recorrido Interactivo', 'Guiado', 30, 60, 50],
        ];

        foreach ($datos as $data) {

            [$nombre, $tipo, $precio, $duracion, $capacidad] = $data;

            $this->recorridos[$this->nextId] = [
                'id' => $this->nextId,
                'nombre' => $nombre,
                'tipo' => $tipo,
                'precio' => $precio,
                'duracion' => $duracion,
                'capacidad' => $capacidad,
            ];

            $this->nextId++;
        }
    }

    /**
     * Obtiene todos
     */
    public function findAll(): array
    {
        return array_values($this->recorridos);
    }

    /**
     * Busca por ID
     */
    public function findById(int $id): ?array
    {
        return $this->recorridos[$id] ?? null;
    }

    /**
     * Filtra por tipo
     */
    public function findByTipo(string $tipo): array
    {
        return array_filter(
            $this->recorridos,
            fn($r) => $r['tipo'] === $tipo
        );
    }

    /**
     * Busca por nombre
     */
    public function search(string $query): array
    {
        return array_filter($this->recorridos, function ($r) use ($query) {

            return str_contains(
                strtolower($r['nombre']),
                strtolower($query)
            );
        });
    }

    /**
     * Estadísticas básicas
     */
    public function getEstadisticas(): array
    {
        $total = count($this->recorridos);

        $guiados = count($this->findByTipo('Guiado'));
        $noGuiados = count($this->findByTipo('No Guiado'));

        return [
            'total' => $total,
            'guiados' => $guiados,
            'no_guiados' => $noGuiados,
        ];
    }
}