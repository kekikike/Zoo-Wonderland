<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\UsuarioRepository;
use Exception;

class Auth
{
    private UsuarioRepository $repository;

    public function __construct(UsuarioRepository $repository)
    {
        $this->repository = $repository;
    }

    public function attempt(string $username, string $password): array
    {
        try {

            $username = trim($username);
            $password = trim($password);

            if (empty($username) || empty($password)) {
                throw new Exception('Todos los campos son obligatorios.');
            }

            if (preg_match('/\s/', $username) || strlen($username) < 4) {
                throw new Exception('El nombre de usuario no puede tener espacios y debe tener al menos 4 caracteres.');
            }

            if (strlen($password) < 4) {
                throw new Exception('La contraseña debe tener al menos 4 caracteres.');
            }

            $usuario = $this->repository->findByUsername($username);

            if (!$usuario || !$usuario->verificarPassword($password)) {
                throw new Exception('Credenciales incorrectas.');
            }

            $_SESSION['usuario'] = $usuario;

            return [
                'success' => true,
                'message' => 'Login exitoso.'
            ];

        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];

        } finally {
            error_log("Intento de login para usuario: " . $username);
        }
    }

    public function logout(): void
    {
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['usuario']);
    }

    public static function user()
    {
        return $_SESSION['usuario'] ?? null;
    }
}