<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

session_start();
require_once __DIR__ . '/../config/constants.php';
require_once SRC_PATH . '/Services/autoload_session.php';
require_once SRC_PATH . '/Services/Auth.php';
require_once SRC_PATH . '/Repositories/RecorridoRepository.php';
require_once SRC_PATH . '/Models/Recorrido.php';

use App\Services\Auth;
use App\Repositories\RecorridoRepository;

$recorridoRepo = new RecorridoRepository();

$recorridos = $recorridoRepo->findAll();

$isLoggedIn = Auth::check();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Bienvenidos al Zoológico</title>
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

        .banner {
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.6)), 
                        url('img/fondo1.jpg') center/cover no-repeat;
            height: 60vh;
            min-height: 420px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 0 1rem;
        }

        .banner-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-shadow: 3px 3px 8px rgba(0,0,0,0.7);
        }

        .banner-content p {
            font-size: 1.4rem;
            max-width: 800px;
            margin: 0 auto;
        }

        main {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }

        section {
            margin-bottom: 5rem;
        }

        h2 {
            color: var(--color-primary);
            font-size: 2.4rem;
            margin-bottom: 1.5rem;
            text-align: center;
            position: relative;
        }

        h2::after {
            content: '';
            width: 80px;
            height: 4px;
            background: var(--color-accent);
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
        }

        .nosotros {
            background: var(--color-light);
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .recorridos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-top: 2.5rem;
        }

        .card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
            transition: transform 0.25s, box-shadow 0.25s;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }

        .card-header {
            background: var(--color-primary);
            color: white;
            padding: 1.3rem;
            text-align: center;
            font-size: 1.4rem;
            font-weight: bold;
        }

        .card-body {
            padding: 1.6rem;
        }

        .card-info {
            margin: 1rem 0;
            color: #555;
        }

        .precio {
            font-size: 1.8rem;
            color: var(--color-primary);
            font-weight: bold;
            margin: 1rem 0;
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

        .btn-reservar {
            background: var(--color-accent);
            color: var(--color-dark);
        }

        .btn-comprar {
            background: var(--color-info);
            color: white;
        }

        .btn-login {
            background: var(--color-dark);
            color: white;
            margin-top: 1rem;
        }

        .actions {
            text-align: center;
            margin-top: 1.5rem;
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
            .banner-content h1 { font-size: 2.6rem; }
        }
    </style>
</head>
<body>

<header>
    <nav>
        <div class="logo"><?= APP_NAME ?></div>
        <ul class="nav-links">
            <li><a href="#inicio">Inicio</a></li>
            <li><a href="#nosotros">Nosotros</a></li>
            <li><a href="#visitanos">Visítanos</a></li>
        </ul>
        <div class="auth-links">
            <?php if ($isLoggedIn): ?>
                <span>Bienvenido, <?= htmlspecialchars(Auth::user()->getNombreCompleto() ?? 'Usuario') ?></span>
                <a href="logout.php" style="margin-left:1.5rem;color:#ffe2a0;">Cerrar sesión</a>
            <?php else: ?>
                <a href="login.php" class="btn login-btn">Iniciar sesión</a>
                <a href="registrar.php" class="btn register-btn">Registrarse</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<div class="banner" id="inicio">
    <div class="banner-content">
        <h1>Bienvenidos a ZooWonderland</h1>
        <p>Descubre la magia de la naturaleza, conoce especies increíbles y vive experiencias únicas en el corazón de la biodiversidad.</p>
    </div>
</div>

<main>

    <section id="nosotros" class="nosotros">
        <h2>Nosotros</h2>
        <p>
            El Zoológico Municipal ZooWonderland fue fundado en 1999 con la firme misión de conservar la biodiversidad, educar a la población y fortalecer el vínculo entre las personas y el mundo animal. Desde sus inicios, se ha consolidado como un espacio dedicado a la protección de la fauna silvestre, promoviendo el respeto por los ecosistemas y el uso responsable de los recursos naturales.<br>
        Actualmente, el zoológico es hogar de más de 50 especies, tanto nativas como exóticas, muchas de las cuales forman parte de programas especializados de reproducción y conservación in situ y ex situ. A través de estas iniciativas, se contribuye activamente a la preservación de especies en riesgo y al fortalecimiento de la biodiversidad del país. Asimismo, ZooWonderland desarrolla actividades educativas, eventos y visitas guiadas, convirtiéndose en un referente regional en materia de conservación, aprendizaje ambiental y sensibilización ciudadana.
        </p>
        <p style="margin-top:1.2rem;">
            Contamos con 42 hectáreas de áreas naturales, recorridos temáticos, programas educativos y experiencias interactivas 
            que buscan generar conciencia sobre la importancia de proteger nuestro planeta y sus habitantes.
        </p>
    </section>

    <section id="visitanos">
        <h2>Visítanos - Nuestros Recorridos</h2>

        <div class="recorridos-grid">
            <?php foreach ($recorridos as $r): ?>
                <div class="card">
                    <div class="card-header">
                        <?= htmlspecialchars($r['nombre']) ?>
                    </div>
                    <div class="card-body">
                        <div class="card-info">
                            <strong>Tipo:</strong> <?= htmlspecialchars($r['tipo']) ?><br>
                            <strong>Duración:</strong> <?= $r['duracion'] ?> minutos<br>
                            <strong>Capacidad:</strong> <?= $r['capacidad'] ?> personas
                        </div>
                        <div class="precio">
                            Bs. <?= number_format($r['precio'], 2) ?>
                        </div>

                        <div class="actions">
                            <?php if ($isLoggedIn): ?>
                                <a href="reservar.php?recorrido=<?= $r['id'] ?>" class="btn btn-reservar">Reservar</a>
                                <a href="comprar.php?recorrido=<?= $r['id'] ?>" class="btn btn-comprar">Comprar Ticket</a>
                            <?php else: ?>
                                <a href="login.php?redirect=visitanos" class="btn btn-login">Inicia sesión para reservar o comprar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

</main>

<footer>
    <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> - Todos los derechos reservados</p>
</footer>

</body>
</html>