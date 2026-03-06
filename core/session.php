<?php
// core/Session.php
declare(strict_types=1);

namespace Core;

class Session
{
    public static function iniciarSesionSegura(): void
    {
        // Configuraciones de seguridad recomendadas
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', '1');           // Solo HTTPS (en producción)
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.gc_maxlifetime', '3600');       // 1 hora
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        // Iniciar sesión
        session_start();

        // Regenerar ID en la primera carga o tras login exitoso
        if (empty($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }
}