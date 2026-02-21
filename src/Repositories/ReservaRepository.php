<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Reserva;

/**
 * Repositorio de reservas (simulado en memoria)
 */
class ReservaRepository
{
    private array $reservas = [];
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
        $this->reservas = [];
    }

    /**
     * Obtiene todas las reservas
     */
    public function findAll(): array
    {
        return array_values($this->reservas);
    }

    /**
     * Busca por ID
     */
    public function findById(int $id): ?Reserva
    {
        return $this->reservas[$id] ?? null;
    }

    /**
     * Busca por institución
     */
    public function findByInstitucion(string $institucion): array
    {
        return array_filter(
            $this->reservas,
            fn($r) => strtolower($r->getInstitucion()) === strtolower($institucion)
        );
    }

    /**
     * Agrega reserva
     */
    public function add(Reserva $reserva): void
    {
        $this->reservas[$reserva->getId()] = $reserva;
    }

    /**
     * Elimina reserva
     */
    public function delete(int $id): bool
    {
        if (!isset($this->reservas[$id])) {
            return false;
        }

        unset($this->reservas[$id]);
        return true;
    }
}