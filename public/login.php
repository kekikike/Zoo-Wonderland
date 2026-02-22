<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\UsuarioRepository;
use App\Services\Auth;

$repo = new UsuarioRepository();
$auth = new Auth($repo);

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $resultado = $auth->attempt(
            $_POST['username'] ?? '',
            $_POST['password'] ?? ''
        );

        if ($resultado['success']) {
            header("Location: index.php");
            exit;
        }

        $error = $resultado['message'];

    } catch (Exception $e) {

        $error = "Ocurrió un error inesperado.";

    } finally {

        error_log("Proceso de autenticación ejecutado.");
    }
}
?>