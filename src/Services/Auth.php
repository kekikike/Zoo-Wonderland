<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\UsuarioRepository;

class Auth
{
    private UsuarioRepository $repository;

    public function __construct(UsuarioRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Intenta autenticar un usuario
     */
    public function attempt(string $username, string $password): array
    {
        $username = trim($username);
        $password = trim($password);

        // 🔐 VALIDACIÓN 1: Campos obligatorios
        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Todos los campos son obligatorios.'
            ];
        }

        // 🔐 VALIDACIÓN 2: Usuario sin espacios y mínimo 4 caracteres
        if (preg_match('/\s/', $username) || strlen($username) < 4) {
            return [
                'success' => false,
                'message' => 'El nombre de usuario no puede tener espacios y debe tener al menos 4 caracteres.'
            ];
        }

        // 🔐 VALIDACIÓN 3: Longitud mínima de contraseña
        if (strlen($password) < 4) {
            return [
                'success' => false,
                'message' => 'La contraseña debe tener al menos 4 caracteres.'
            ];
        }

        $usuario = $this->repository->findByUsername($username);

        if (!$usuario || !$usuario->verificarPassword($password)) {
            return [
                'success' => false,
                'message' => 'Credenciales incorrectas.'
            ];
        }

        // Si todo está bien, guardamos sesión
        $_SESSION['usuario'] = $usuario;

        return [
            'success' => true,
            'message' => 'Login exitoso.'
        ];
    }

    /**
     * Cerrar sesión
     */
    public function logout(): void
    {
        session_destroy();
    }

    /**
     * Verifica si hay usuario autenticado
     */
    public static function check(): bool
    {
        return isset($_SESSION['usuario']);
    }

    /**
     * Devuelve el usuario actual
     */
    public static function user()
    {
        return $_SESSION['usuario'] ?? null;
    }
}