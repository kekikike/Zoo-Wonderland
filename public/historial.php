<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

session_start();
require_once __DIR__ . '/../config/constants.php';
require_once SRC_PATH . '/Services/autoload_session.php';
require_once SRC_PATH . '/Services/Auth.php';
require_once SRC_PATH . '/Repositories/CompraRepository.php';
require_once SRC_PATH . '/Models/Cliente.php';

use App\Services\Auth;
use App\Repositories\CompraRepository;
use App\Models\Cliente;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$usuario = Auth::user();
if (!$usuario instanceof Cliente) {
    header('Location: index.php');
    exit;
}

$compraRepo = new CompraRepository();
$compras = $compraRepo->findByCliente($usuario->getId());

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Compras - <?= APP_NAME ?></title>
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

        .compra {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .compra h3 {
            margin-top: 0;
        }

        .ticket {
            background: #f9f9f9;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
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
            <li><a href="comprar.php">Comprar Entradas</a></li>
            <li><a href="reservar.php">Tours Grupales</a></li>
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
        <h1>Historial de Compras</h1>

        <?php if (empty($compras)): ?>
            <p>No tienes compras registradas.</p>
        <?php else: ?>
            <?php foreach ($compras as $compra): ?>
                <div class="compra">
                    <h3>Compra #<?= $compra->getId() ?> - Total: Bs. <?= $compra->getMonto() ?></h3>
                    <p>Fecha: <?= $compra->getFecha() ?> | Hora: <?= $compra->getHora() ?></p>
                    <h4>Tickets:</h4>
                    <?php foreach ($compra->getTickets() as $ticket): ?>
                        <div class="ticket">
                            <p>Ticket ID: <?= $ticket->getId() ?> | Recorrido: <?= $ticket->getRecorrido()->getNombre() ?> | Fecha: <?= $ticket->getFecha() ?> | Hora: <?= $ticket->getHora() ?></p>
                            <img src="<?= $ticket->getCodigoQR() ?>" alt="QR Code" style="width: 100px; height: 100px;" />
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> - Todos los derechos reservados</p>
</footer>

</body>
</html>
