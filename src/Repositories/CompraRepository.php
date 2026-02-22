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
    private array $compras;
    private int $nextId = 1;

    public function __construct()
    {
        if (!isset($_SESSION['compras'])) {
            $_SESSION['compras'] = [];
        }
        $this->compras = &$_SESSION['compras'];
        if (!isset($_SESSION['compra_next_id'])) {
            $_SESSION['compra_next_id'] = 1;
        }
        $this->nextId = $_SESSION['compra_next_id'];
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
     * Obtiene el siguiente ID
     */
    public function getNextId(): int
    {
        $id = $this->nextId;
        $this->nextId++;
        $_SESSION['compra_next_id'] = $this->nextId;
        return $id;
    }
}