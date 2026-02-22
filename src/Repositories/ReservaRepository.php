<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Reserva;
use App\Models\Recorrido;

/**
 * Repositorio de reservas grupales — persistencia en sesión (HU-04)
 */
class ReservaRepository
{
    private const SESSION_KEY = 'zoo_reservas';
    private const NEXT_ID_KEY = 'zoo_reservas_next_id';

    // ------------------------------------------------------------------
    // Persistencia en $_SESSION
    // ------------------------------------------------------------------

    private function &getStore(): array
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
        return $_SESSION[self::SESSION_KEY];
    }

    public function getNextId(): int
    {
        if (!isset($_SESSION[self::NEXT_ID_KEY])) {
            $_SESSION[self::NEXT_ID_KEY] = 1;
        }
        return $_SESSION[self::NEXT_ID_KEY]++;
    }

    // ------------------------------------------------------------------
    // CRUD
    // ------------------------------------------------------------------

    /**
     * Obtiene todas las reservas (todas las sesiones del cliente).
     */
    public function findAll(): array
    {
        return array_values($this->getStore());
    }

    /**
     * Busca una reserva por su ID.
     */
    public function findById(int $id): ?Reserva
    {
        $store = $this->getStore();
        return $store[$id] ?? null;
    }

    /**
     * Filtra reservas por nombre de institución (búsqueda insensible a mayúsculas).
     */
    public function findByInstitucion(string $institucion): array
    {
        return array_filter(
            $this->getStore(),
            fn($r) => strtolower($r->getInstitucion()) === strtolower($institucion)
        );
    }

    /**
     * Agrega una reserva al store de sesión.
     */
    public function add(Reserva $reserva): void
    {
        $_SESSION[self::SESSION_KEY][$reserva->getId()] = $reserva;
    }

    /**
     * Elimina una reserva por ID.
     */
    public function delete(int $id): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY][$id])) {
            return false;
        }
        unset($_SESSION[self::SESSION_KEY][$id]);
        return true;
    }
}