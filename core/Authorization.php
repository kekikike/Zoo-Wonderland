<?php
// core/Authorization.php
declare(strict_types=1);

namespace Core;

use App\Services\AuthService;
use App\Models\Usuario;
use Exception;

class Authorization
{
    /**
     * Requiere que el usuario esté autenticado
     * @throws Exception si no está logueado
     * @return Usuario El usuario autenticado
     */
    public static function requireLogin(): Usuario
    {
        if (!AuthService::check()) {
            header('Location: index.php?r=login');
            exit;
        }

        $user = AuthService::user();
        if (!$user) {
            // Sesión corrupta → destruir y redirigir
            session_destroy();
            header('Location: index.php?r=login');
            exit;
        }

        return $user;
    }

    /**
     * Requiere que el usuario sea cliente
     * @throws Exception si no es cliente
     * @return Usuario El usuario cliente
     */
    public static function requireCliente(): Usuario
    {
        $user = self::requireLogin();

        if (!$user->esCliente()) {
            http_response_code(403);
            require_once APP_PATH . '/Views/errors/403.php';
            exit;
        }

        return $user;
    }

    /**
     * Requiere que el usuario sea administrador
     * @throws Exception si no es admin
     * @return Usuario El usuario administrador
     */
    public static function requireAdmin(): Usuario
    {
        $user = self::requireLogin();

        if (!$user->esAdministrador()) {
            http_response_code(403);
            require_once APP_PATH . '/Views/errors/403.php';
            exit;
        }

        return $user;
    }

    /**
     * Requiere que el usuario sea guía
     * @throws Exception si no es guía
     * @return Usuario El usuario guía
     */
    public static function requireGuia(): Usuario
    {
        $user = self::requireLogin();

        if (!$user->esGuia()) {
            http_response_code(403);
            require_once APP_PATH . '/Views/errors/403.php';
            exit;
        }

        return $user;
    }
}