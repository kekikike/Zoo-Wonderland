<?php
// app/Services/AuthService.php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\UsuarioRepository;
use App\Models\Usuario;

class AuthService
{
    private static ?UsuarioRepository $repo = null;

    // Inicializamos el repo una sola vez (lazy loading)
    private static function getRepo(): UsuarioRepository
    {
        if (self::$repo === null) {
            self::$repo = new UsuarioRepository();
        }
        return self::$repo;
    }

    public static function attempt(string $login, string $password): array
    {
        $usuario = self::getRepo()->authenticate($login, $password);

        if (!$usuario) {
            return ['success' => false, 'message' => 'Credenciales incorrectas o usuario no encontrado.'];
        }

        $_SESSION['user_id'] = $usuario->id_usuario;
        session_regenerate_id(true);

        return ['success' => true, 'message' => 'Inicio de sesión exitoso.'];
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']) && is_int($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }

    public static function user(): ?Usuario
    {
        if (!self::check()) {
            return null;
        }

        static $cached = null;
        if ($cached === null) {
            $cached = self::getRepo()->findById((int) $_SESSION['user_id']);
        }

        return $cached;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        session_regenerate_id(true);
    }
}