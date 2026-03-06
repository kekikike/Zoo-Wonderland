<?php
declare(strict_types=1);

namespace App\Repositories;

use Core\Database;

/**
 * Repositorio que opera sobre la tabla `animales` de la base de datos.
 */
class AnimalRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Devuelve todos los animales (cada fila como array asociativo).
     * Los 29 animales presentes en el volcado de la base estarán listados.
     *
     * @return array[]
     */
    public function findAll(): array
    {
        // incluimos nombre de área para facilitar la vista
        $sql = 'SELECT a.*, ar.nombre AS area_nombre
                FROM animales a
                LEFT JOIN areas ar ON a.id_area = ar.id_area
                ORDER BY a.id_animal';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene animales por área
     */
    public function findByArea(int $areaId): array
    {
        $sql = 'SELECT a.*, ar.nombre AS area_nombre
                FROM animales a
                LEFT JOIN areas ar ON a.id_area = ar.id_area
                WHERE a.id_area = ?
                ORDER BY a.id_animal';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$areaId]);
        return $stmt->fetchAll();
    }

    /**
     * Búsqueda combinada por texto y opcionalmente por área.
     */
    public function search(string $query, ?int $areaId = null): array
    {
        $q = '%' . strtolower($query) . '%';
        $params = [$q, $q, $q, $q];
        $sql = 'SELECT a.*, ar.nombre AS area_nombre
                FROM animales a
                LEFT JOIN areas ar ON a.id_area = ar.id_area
                WHERE (LOWER(a.especie) LIKE ?
                   OR LOWER(a.nombre_comun) LIKE ?
                   OR LOWER(a.descripcion) LIKE ?
                   OR LOWER(a.habitat) LIKE ?)';
        if ($areaId && $areaId > 0) {
            $sql .= ' AND a.id_area = ?';
            $params[] = $areaId;
        }
        $sql .= ' ORDER BY a.id_animal';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Busca animal por id.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT a.*, ar.nombre AS area_nombre
                FROM animales a
                LEFT JOIN areas ar ON a.id_area = ar.id_area
                WHERE a.id_animal = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Inserta un nuevo animal y retorna el id generado.
     */
    public function create(
        string $especie,
        string $nombreComun,
        string $habitat,
        string $descripcion,
        string $estado,
        int $areaId
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO animales (especie,nombre_comun,habitat,descripcion,estado,id_area)
             VALUES (?,?,?,?,?,?)'
        );
        $stmt->execute([$especie, $nombreComun, $habitat, $descripcion, $estado, $areaId]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Actualiza un registro existente. Retorna true si afectó fila.
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        if (isset($data['especie'])) {
            $fields[] = 'especie = ?';
            $values[] = $data['especie'];
        }
        if (isset($data['nombre'])) {
            $fields[] = 'nombre_comun = ?';
            $values[] = $data['nombre'];
        }
        if (isset($data['habitat'])) {
            $fields[] = 'habitat = ?';
            $values[] = $data['habitat'];
        }
        if (isset($data['descripcion'])) {
            $fields[] = 'descripcion = ?';
            $values[] = $data['descripcion'];
        }
        if (isset($data['estado'])) {
            $fields[] = 'estado = ?';
            $values[] = $data['estado'];
        }
        if (isset($data['areaId'])) {
            $fields[] = 'id_area = ?';
            $values[] = $data['areaId'];
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $sql = 'UPDATE animales SET ' . implode(', ', $fields) . ' WHERE id_animal = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Elimina un animal por id
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM animales WHERE id_animal = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Búsqueda de animales por texto en especie, nombre_comun, descripcion o habitat.
     * Retorna array con resultados, incluye area_nombre.
     */

}
