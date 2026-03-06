<?php
// app/Services/RegisterService.php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\UsuarioRepository;
use Core\Database;

use Exception;

class RegisterService
{
    private UsuarioRepository $repo;

    public function __construct()
    {
        $this->repo = new UsuarioRepository();
    }

    public function register(array $data): array
    {
        try {
            // Validaciones básicas
            if (empty($data['nombre1']) || empty($data['apellido1'])) {
                throw new Exception("Nombre y apellido son obligatorios");
            }

            if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Correo electrónico inválido");
            }

            if (strlen($data['password']) < 6) {
                throw new Exception("La contraseña debe tener al menos 6 caracteres");
            }

            if ($data['password'] !== $data['password_confirm']) {
                throw new Exception("Las contraseñas no coinciden");
            }

            if (empty($data['nombre_usuario'])) {
                throw new Exception("El nombre de usuario es obligatorio");
            }

            // Verificar si ya existe el usuario o correo
            $existing = $this->repo->authenticate($data['nombre_usuario'], ''); // solo para chequear existencia
            if ($existing) {
                throw new Exception("El nombre de usuario ya está en uso");
            }

            // Crear usuario
            $userId = $this->repo->create($data);

            // Crear entrada en tabla Cliente (obligatorio para ser cliente)
            $this->createCliente($userId, $data);

            return ['success' => true, 'message' => 'Registro exitoso. Ahora puedes iniciar sesión.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function createCliente(int $userId, array $data): void
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO clientes (nit, tipo_cuenta, id_usuario)
            VALUES (:nit, :tipo_cuenta, :id_usuario)
        ");

        $stmt->execute([
            ':nit'         => $data['nit'] ?? '0000000',          // Puedes hacerlo obligatorio después
            ':tipo_cuenta' => $data['tipo_cuenta'] ?? 'Personal',
            ':id_usuario'  => $userId
        ]);
    }
}