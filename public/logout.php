<?php
declare(strict_types=1);


require_once __DIR__ . '/../vendor/autoload.php';
session_start();

use App\Services\Auth;
use App\Repositories\UsuarioRepository;  

$usuarioRepo = new UsuarioRepository();
$auth = new Auth($usuarioRepo);

$auth->logout();           

$_SESSION = [];
session_unset();
session_destroy();

header('Location: index.php');
exit;