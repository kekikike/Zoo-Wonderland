<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

session_start();
require_once __DIR__ . '/../config/constants.php';
require_once SRC_PATH . '/Services/autoload_session.php';
require_once SRC_PATH . '/Services/Auth.php';
require_once SRC_PATH . '/Services/CompraService.php';
require_once SRC_PATH . '/Models/Cliente.php';
require_once SRC_PATH . '/Repositories/CompraRepository.php';

use App\Services\Auth;
use App\Services\CompraService;
use App\Models\Cliente;
use App\Repositories\CompraRepository;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$usuario = Auth::user();
if (!$usuario instanceof Cliente) {
    header('Location: index.php');
    exit;
}

$compraService = new CompraService();
$compraRepo = new CompraRepository();

$compraId = (int)($_SESSION['ultima_compra_id'] ?? 0);
$compra = $compraRepo->findById($compraId);

error_log("En pagoqr.php: compraId=$compraId, compra encontrada=" . ($compra ? 'si' : 'no'));

if (!$compra) {
    error_log("No se encontró compra, redirigiendo a comprar.php");
    header('Location: comprar.php');
    exit;
}

$pdfContent = $compraService->generarComprobante($compra);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago QR - <?= APP_NAME ?></title>
    <style>
        :root {
            --color-primary:    #a3712a;
            --color-secondary:  #977c66;
            --color-accent:     #bfb641;
            --color-light:      #ffe2a0;
            --color-dark:       #68672e;
            --color-info:       #7eaeb0;
            --color-bg:         #fffaf0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--color-bg);
            color: #333;
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: white;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--color-light);
        }

        .nav-links {
            display: flex;
            gap: 2.2rem;
            list-style: none;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--color-accent);
        }

        .auth-links a {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
        }

        .login-btn {
            background: var(--color-accent);
            color: var(--color-dark);
        }

        .register-btn {
            background: white;
            color: var(--color-primary);
        }

        main {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: var(--color-primary);
            text-align: center;
        }

        .pago-section {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }

        .qr-part {
            flex: 1;
            text-align: center;
        }

        .comprobante-part {
            flex: 2;
        }

        .qr-part img {
            width: 200px;
            height: 200px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            margin: 0.5rem;
            transition: all 0.2s;
        }

        .btn-comprar {
            background: var(--color-info);
            color: white;
        }

        footer {
            background: var(--color-secondary);
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 4rem;
        }

        @media (max-width: 768px) {
            .nav-links { gap: 1.2rem; font-size: 0.95rem; }
            .pago-section { flex-direction: column; }
        }
    </style>
</head>
<body>

<header>
    <nav>
        <div class="logo"><?= APP_NAME ?></div>
        <ul class="nav-links">
            <li><a href="index.php#inicio">Inicio</a></li>
            <li><a href="index.php#nosotros">Nosotros</a></li>
            <li><a href="index.php#visitanos">Visítanos</a></li>
            <li><a href="comprar.php">Comprar</a></li>
            <li><a href="historial.php">Historial</a></li>
        </ul>
        <div class="auth-links">
            <?php if (Auth::check()): ?>
                <span>Bienvenido, <?= htmlspecialchars(Auth::user()->getNombreCompleto() ?? 'Usuario') ?></span>
                <a href="logout.php" style="margin-left:1.5rem;color:#ffe2a0;">Cerrar sesión</a>
            <?php else: ?>
                <a href="login.php" class="btn login-btn">Iniciar sesión</a>
                <a href="registrar.php" class="btn register-btn">Registrarse</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main>
    <div class="container">
        <h1>Pago y Comprobante</h1>

        <div class="pago-section">
            <div class="qr-part">
                <h2>Código QR para Pago</h2>
                <img src="./img/qr.jpeg" alt="QR de Pago" />
                <p>Escanee este QR para realizar el pago.</p>
            </div>

            <div class="comprobante-part">
                <h2>Comprobante de Compra</h2>
                <embed src="data:application/pdf;base64,<?= base64_encode($pdfContent) ?>" width="100%" height="600px" type="application/pdf" />
                <br>
                <a href="data:application/pdf;base64,<?= base64_encode($pdfContent) ?>" download="comprobante_<?= $compra->getId() ?>.pdf" class="btn btn-comprar">Descargar Comprobante</a>
            </div>
        </div>
    </div>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> - Todos los derechos reservados</p>
</footer>

</body>
</html>