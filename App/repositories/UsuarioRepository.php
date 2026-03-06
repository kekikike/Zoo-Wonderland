<?php
// app/Repositories/UsuarioRepository.php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Usuario;
use Core\Database;

class UsuarioRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ════════════════════════════════════════════════════════════
    // MÉTODOS EXISTENTES — sin cambios
    // ════════════════════════════════════════════════════════════

    /**
     * Busca usuario por nombre_usuario o correo + verifica contraseña.
     */
    public function authenticate(string $login, string $password): ?Usuario
    {
        $stmt = $this->db->prepare("
            SELECT 
                u.id_usuario, u.nombre1, u.nombre2, u.apellido1, u.apellido2,
                u.ci, u.correo, u.telefono, u.nombre_usuario, u.rol,
                u.contrasena AS password
            FROM usuarios u
            WHERE u.nombre_usuario = :usuario OR u.correo = :correo
            LIMIT 1
        ");

        $stmt->execute([':usuario' => $login, ':correo' => $login]);
        $row = $stmt->fetch();

        if (!$row) return null;
        if (!password_verify($password, $row['password'])) return null;

        unset($row['password']);
        return new Usuario($row);
    }

    /**
     * Busca usuario por ID.
     */
    public function findById(int $id): ?Usuario
    {
        $stmt = $this->db->prepare("
            SELECT 
                id_usuario, nombre1, nombre2, apellido1, apellido2,
                ci, correo, telefono, nombre_usuario, rol
            FROM usuarios
            WHERE id_usuario = :id
            LIMIT 1
        ");

        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? new Usuario($row) : null;
    }

    /**
     * Verifica si el usuario tiene registro en la tabla clientes.
     */
    public function esCliente(int $id_usuario): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM clientes WHERE id_usuario = :id LIMIT 1");
        $stmt->execute([':id' => $id_usuario]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Verifica si el usuario tiene registro en la tabla guias.
     */
    public function esGuia(int $id_usuario): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM guias WHERE id_usuario = :id LIMIT 1");
        $stmt->execute([':id' => $id_usuario]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Crea un nuevo usuario y retorna su ID.
     * @throws \Exception si falla la inserción
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (
                nombre1, nombre2, apellido1, apellido2,
                ci, correo, telefono, nombre_usuario,
                contrasena, rol
            ) VALUES (
                :nombre1, :nombre2, :apellido1, :apellido2,
                :ci, :correo, :telefono, :nombre_usuario,
                :contrasena, :rol
            )
        ");

        $success = $stmt->execute([
            ':nombre1'        => $data['nombre1'],
            ':nombre2'        => $data['nombre2']   ?? null,
            ':apellido1'      => $data['apellido1'],
            ':apellido2'      => $data['apellido2'] ?? null,
            ':ci'             => $data['ci']         ?? null,
            ':correo'         => $data['correo'],
            ':telefono'       => $data['telefono']   ?? null,
            ':nombre_usuario' => $data['nombre_usuario'],
            ':contrasena'     => password_hash($data['password'], PASSWORD_DEFAULT),
            ':rol'            => 'cliente'
        ]);

        if (!$success) {
            throw new \Exception("No se pudo crear el usuario");
        }

        return (int) $this->db->lastInsertId();
    }

    // ════════════════════════════════════════════════════════════
    // MÉTODOS NUEVOS — gestión de usuarios (HU-10)
    // ════════════════════════════════════════════════════════════

    /**
     * Obtiene todos los usuarios con filtros opcionales.
     * Excluye siempre al admin autenticado para que no aparezca en la tabla.
     *
     * PDO no permite repetir el mismo named parameter en múltiples posiciones,
     * por eso cada campo LIKE tiene su propio parámetro (:b1 … :b6).
     *
     * @param string $busqueda        Texto libre: CI, nombre o apellido
     * @param string $rol             '' | 'cliente' | 'guia' | 'administrador'
     * @param int    $idRecorrido     0 = todos, >0 = filtra guías de ese recorrido
     * @param string $estado          '' = todos | '1' = activos | '0' = inactivos
     * @param int    $excluirUsuario  ID del admin autenticado (se excluye del resultado)
     */
    public function getUsuariosFiltrados(
        string $busqueda       = '',
        string $rol            = '',
        int    $idRecorrido    = 0,
        string $estado         = '',
        int    $excluirUsuario = 0
    ): array {
        $sql = "
            SELECT
                u.id_usuario,
                u.nombre1,
                u.nombre2,
                u.apellido1,
                u.apellido2,
                u.ci,
                u.correo,
                u.telefono,
                u.nombre_usuario,
                u.rol,
                u.estado,
                g.horarios        AS guia_horarios,
                g.dias_trabajo    AS guia_dias,
                g.id_guia,
                c.nit             AS cliente_nit,
                c.tipo_cuenta     AS cliente_tipo,
                GROUP_CONCAT(DISTINCT r.nombre ORDER BY r.nombre SEPARATOR ', ') AS recorridos_asignados
            FROM usuarios u
            LEFT JOIN guias           g  ON g.id_usuario   = u.id_usuario
            LEFT JOIN clientes        c  ON c.id_usuario   = u.id_usuario
            LEFT JOIN guia_recorrido  gr ON gr.id_guia     = g.id_guia
            LEFT JOIN recorridos      r  ON r.id_recorrido = gr.id_recorrido
            WHERE 1=1
        ";

        $params = [];

        // Excluir siempre al admin autenticado
        if ($excluirUsuario > 0) {
            $sql .= " AND u.id_usuario != :excluir";
            $params[':excluir'] = $excluirUsuario;
        }

        // Búsqueda: parámetros únicos por campo (limitación de PDO)
        if ($busqueda !== '') {
            $like = '%' . $busqueda . '%';
            $sql .= " AND (
                u.ci           LIKE :b1
                OR u.nombre1   LIKE :b2
                OR u.nombre2   LIKE :b3
                OR u.apellido1 LIKE :b4
                OR u.apellido2 LIKE :b5
                OR CONCAT(u.nombre1, ' ', u.apellido1) LIKE :b6
            )";
            $params[':b1'] = $like;
            $params[':b2'] = $like;
            $params[':b3'] = $like;
            $params[':b4'] = $like;
            $params[':b5'] = $like;
            $params[':b6'] = $like;
        }

        if ($rol !== '') {
            $sql .= " AND u.rol = :rol";
            $params[':rol'] = $rol;
        }

        if ($idRecorrido > 0) {
            $sql .= " AND gr.id_recorrido = :id_recorrido";
            $params[':id_recorrido'] = $idRecorrido;
        }

        if ($estado !== '') {
            $sql .= " AND u.estado = :estado";
            $params[':estado'] = (int) $estado;
        }

        $sql .= " GROUP BY u.id_usuario ORDER BY u.apellido1 ASC, u.nombre1 ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un usuario por ID con sus datos extra de guía o cliente.
     */
    public function getUsuarioPorId(int $id_usuario): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                u.id_usuario, u.nombre1, u.nombre2, u.apellido1, u.apellido2,
                u.ci, u.correo, u.telefono, u.nombre_usuario, u.rol, u.estado,
                g.horarios     AS guia_horarios,
                g.dias_trabajo AS guia_dias,
                g.id_guia,
                c.nit          AS cliente_nit,
                c.tipo_cuenta  AS cliente_tipo,
                c.id_cliente
            FROM usuarios u
            LEFT JOIN guias    g ON g.id_usuario = u.id_usuario
            LEFT JOIN clientes c ON c.id_usuario = u.id_usuario
            WHERE u.id_usuario = :id
            LIMIT 1
        ");

        $stmt->execute([':id' => $id_usuario]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Verifica si un correo ya existe en otro usuario distinto al indicado.
     *
     * @param string $correo      Correo a verificar
     * @param int    $excluir_id  ID del usuario que se está editando (se excluye)
     * @return bool               true si el correo YA está en uso por otro
     */
    public function correoEnUso(string $correo, int $excluir_id): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM usuarios
            WHERE correo = :correo AND id_usuario != :id
            LIMIT 1
        ");
        $stmt->execute([':correo' => $correo, ':id' => $excluir_id]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Verifica si un nombre_usuario ya existe en otro usuario distinto al indicado.
     *
     * @param string $nombre_usuario  Nombre de usuario a verificar
     * @param int    $excluir_id      ID del usuario que se está editando (se excluye)
     * @return bool                   true si ya está en uso por otro
     */
    public function nombreUsuarioEnUso(string $nombre_usuario, int $excluir_id): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM usuarios
            WHERE nombre_usuario = :nombre_usuario AND id_usuario != :id
            LIMIT 1
        ");
        $stmt->execute([':nombre_usuario' => $nombre_usuario, ':id' => $excluir_id]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Actualiza los datos editables de un usuario, incluyendo correo y nombre_usuario.
     * Solo el administrador debe llamar este método.
     *
     * @throws \Exception si correo o nombre_usuario ya están en uso por otro usuario
     */
    public function actualizarUsuario(
        int    $id_usuario,
        string $nombre1,
        string $nombre2,
        string $apellido1,
        string $apellido2,
        int    $ci,
        string $telefono,
        string $rol,
        string $correo,
        string $nombre_usuario
    ): bool {
        // Validar unicidad de correo
        if ($this->correoEnUso($correo, $id_usuario)) {
            throw new \Exception("El correo '{$correo}' ya está registrado en otra cuenta.");
        }

        // Validar unicidad de nombre_usuario
        if ($this->nombreUsuarioEnUso($nombre_usuario, $id_usuario)) {
            throw new \Exception("El nombre de usuario '{$nombre_usuario}' ya está en uso.");
        }

        $stmt = $this->db->prepare("
            UPDATE usuarios SET
                nombre1        = :nombre1,
                nombre2        = :nombre2,
                apellido1      = :apellido1,
                apellido2      = :apellido2,
                ci             = :ci,
                telefono       = :telefono,
                rol            = :rol,
                correo         = :correo,
                nombre_usuario = :nombre_usuario
            WHERE id_usuario   = :id_usuario
        ");

        return $stmt->execute([
            ':nombre1'        => $nombre1,
            ':nombre2'        => $nombre2   !== '' ? $nombre2   : null,
            ':apellido1'      => $apellido1,
            ':apellido2'      => $apellido2 !== '' ? $apellido2 : null,
            ':ci'             => $ci,
            ':telefono'       => $telefono  !== '' ? $telefono  : null,
            ':rol'            => $rol,
            ':correo'         => $correo,
            ':nombre_usuario' => $nombre_usuario,
            ':id_usuario'     => $id_usuario,
        ]);
    }

    /**
     * Cambia el estado de una cuenta: 1 = activa, 0 = inactiva.
     */
    public function cambiarEstado(int $id_usuario, int $estado): bool
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios SET estado = :estado WHERE id_usuario = :id
        ");
        return $stmt->execute([':estado' => $estado, ':id' => $id_usuario]);
    }

    /**
     * Obtiene la lista de recorridos para el select de filtros en la vista.
     */
    public function getRecorridosParaFiltro(): array
    {
        $stmt = $this->db->query("
            SELECT id_recorrido, nombre FROM recorridos ORDER BY nombre ASC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}