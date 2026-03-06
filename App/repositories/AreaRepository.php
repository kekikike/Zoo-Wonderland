<?php
declare(strict_types=1);

namespace App\Repositories;

use Core\Database;

/**
 * Acceso a la tabla `areas`.
 */
class AreaRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Todas las áreas ordenadas por id.
     * @return array[]
     */
    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM areas ORDER BY id_area');
        return $stmt->fetchAll();
    }

    /**
     * Busca área por id.
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM areas WHERE id_area = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Busca por restricción (0/1)
     * @return array[]
     */
    public function findByRestriccion(bool $restringida): array
    {
        $stmt = $this->db->prepare('SELECT * FROM areas WHERE restringida = ?');
        $stmt->execute([$restringida ? 1 : 0]);
        return $stmt->fetchAll();
    }

    /**
     * Búsqueda de texto en nombre o descripción.
     */
    public function search(string $query): array
    {
        $q = '%' . strtolower($query) . '%';
        $stmt = $this->db->prepare('SELECT * FROM areas WHERE LOWER(nombre) LIKE ? OR LOWER(descripcion) LIKE ?');
        $stmt->execute([$q, $q]);
        return $stmt->fetchAll();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM areas WHERE id_area = ?');
        return $stmt->execute([$id]);
    }
}
