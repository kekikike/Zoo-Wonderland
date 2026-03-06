<?php
// public/logout.php
declare(strict_types=1);


// Iniciar sesión de forma segura (usando la misma función que recomendaremos)
require_once __DIR__ . '/../core/session.php'; // o directamente la función
use Core\Session;
Session::iniciarSesionSegura();

use App\Services\AuthService;

$auth = new AuthService();
$auth->logout();

// Destrucción adicional por seguridad
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

header('Location: /');
exit;