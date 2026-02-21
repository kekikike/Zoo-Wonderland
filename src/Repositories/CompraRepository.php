<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Compra;
use App\Models\Cliente;
use App\Models\Recorrido;

/**
 * Repositorio de compras (simulado en memoria)
 */
class CompraRepository
{
    private array $compras = [];
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
        // Datos simulados básicos (sin dependencias reales)
        $this->compras = [];
    }

    /**
     * Obtiene todas las compras
     */
    public function findAll(): array
    {
        return array_values($this->compras);
    }

    /**
     * Busca por ID
     */
    public function findById(int $id): ?Compra
    {
        return $this->compras[$id] ?? null;
    }

    /**
     * Busca por cliente
     */
    public function findByCliente(int $clienteId): array
    {
        return array_filter(
            $this->compras,
            fn($c) => $c->getCliente()->getId() === $clienteId
        );
    }

    /**
     * Agrega compra
     */
    public function add(Compra $compra): void
    {
        $this->compras[$compra->getId()] = $compra;
    }

    /**
     * Elimina compra
     */
    public function delete(int $id): bool
    {
        if (!isset($this->compras[$id])) {
            return false;
        }

        unset($this->compras[$id]);
        return true;
    }
}