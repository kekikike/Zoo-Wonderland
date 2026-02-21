<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Ticket;

/**
 * Repositorio de tickets (simulado en memoria)
 */
class TicketRepository
{
    private array $tickets = [];
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
        $this->tickets = [];
    }

    /**
     * Obtiene todos los tickets
     */
    public function findAll(): array
    {
        return array_values($this->tickets);
    }

    /**
     * Busca por ID
     */
    public function findById(int $id): ?Ticket
    {
        return $this->tickets[$id] ?? null;
    }

    /**
     * Busca por código QR
     */
    public function findByCodigoQR(string $codigo): ?Ticket
    {
        foreach ($this->tickets as $ticket) {
            if ($ticket->getCodigoQR() === $codigo) {
                return $ticket;
            }
        }

        return null;
    }

    /**
     * Busca por fecha
     */
    public function findByFecha(string $fecha): array
    {
        return array_filter(
            $this->tickets,
            fn($t) => $t->getFecha() === $fecha
        );
    }

    /**
     * Agrega ticket
     */
    public function add(Ticket $ticket): void
    {
        $this->tickets[$ticket->getId()] = $ticket;
    }

    /**
     * Elimina ticket
     */
    public function delete(int $id): bool
    {
        if (!isset($this->tickets[$id])) {
            return false;
        }

        unset($this->tickets[$id]);
        return true;
    }
}