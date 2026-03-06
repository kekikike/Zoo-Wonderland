<?php
// app/Views/home.php
$isLoggedIn = $isLoggedIn ?? false;
$esCliente  = $esCliente  ?? false;
$user       = $user       ?? null;
$recorridos = $recorridos ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ZooWonderland - Naturaleza en Vivo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    :root {
        --verde-selva:   #2e7d32;
        --verde-oscuro:  #1b5e20;
        --amarillo-sol:  #ffca28;
        --naranja-tigre: #f57c00;
        --azul-cielo:    #0288d1;
        --gris-claro:    #f8faf8;
        --oscuro:        #0d3a1f;
        --blanco:        #ffffff;
        --sombra:        0 10px 30px rgba(0,0,0,0.08);
        --transicion:    all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --negro: #000000;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Open Sans', sans-serif;
        background-color: var(--gris-claro);
        color: #333;
        line-height: 1.6;
    }

    h1, h2, h3, .logo { font-family: 'Montserrat', sans-serif; }

    /* ────────────── NAV REFINADA ────────────── */
    header {
        background: var(--blanco);
        padding: 1rem 5%;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    }

    nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1400px;
        margin: 0 auto;
    }

    .logo {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--verde-selva);
        text-transform: uppercase;
        letter-spacing: -1px;
    }

    .menu { display: flex; gap: 2rem; }

    .menu a {
        color: var(--oscuro);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: var(--transicion);
    }

    .menu a:hover { color: var(--verde-selva); }

    .auth-area { display: flex; align-items: center; gap: 1rem; }

    /* ────────────── BOTONES GENERALES ────────────── */
    .btn {
        padding: 0.8rem 1.8rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: var(--transicion);
        border: none;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .btn-login { background: var(--verde-selva); color: white; }
    .btn-login:hover { background: var(--verde-oscuro); transform: translateY(-2px); }

    .btn-registro { background: var(--amarillo-sol); color: var(--blanco); }
    .btn-registro:hover { filter: brightness(0.9); transform: translateY(-2px); }

    /* ────────────── USER AREA ────────────── */
    .user-welcome { display: flex; align-items: center; gap: 1.5rem; background: #f0f4f0; padding: 0.5rem 1rem; border-radius: 50px; }
    .user-name { font-weight: 700; color: var(--verde-selva); font-size: 0.9rem; }
    .user-links { display: flex; gap: 1rem; font-size: 0.85rem; align-items: center; }
    .user-links a { color: #666; text-decoration: none; }
    .user-links a.logout { color: #d32f2f; font-weight: bold; font-size: 1.1rem; }

    /* ────────────── BANNER ────────────── */
    .banner {
        background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.5)), 
                    url('https://perujungletrips.com/wp-content/uploads/2025/07/Oso-de-Anteojos-Ucumari-12.webp') no-repeat;
        background-position: 40% 35%;
        background-size: cover;
        height: 350px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-align: center;
        margin-bottom: 4rem;
    }

    .banner h1 { font-size: 3.5rem; margin-bottom: 1rem; font-weight: 800; }
    .banner p { font-size: 1.2rem; opacity: 0.9; font-weight: 300; }

    /* ────────────── SECCIONES ────────────── */
    main { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }
    section { margin-bottom: 6rem; }
    h2 { font-size: 2.2rem; color: var(--oscuro); margin-bottom: 3rem; text-align: center; }

    /* ────────────── RECORRIDOS ────────────── */
    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2.5rem;
    }

    .card {
        background: var(--blanco);
        border-radius: 25px; 
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 2px solid transparent;
        display: flex;
        flex-direction: column;
    }

    .card:hover { 
        transform: translateY(-12px); 
        box-shadow: 0 20px 40px rgba(46, 125, 50, 0.15);
        border-color: var(--verde-selva);
    }

    .card-header {
        background: var(--amarillo-sol); 
        padding: 1.5rem;
        font-size: 1.4rem;
        font-weight: 800;
        text-align: center;
        border-bottom: none;
        color: #000000;
    }

    .card-body { padding: 2rem; flex-grow: 1; }
    
    .card-body p {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.6rem;
        border-bottom: 1px dashed #eee;
        font-weight: 600;
    }

    .card-body p span i {
        color: var(--verde-selva);
        margin-right: 8px;
        width: 20px;
    }

    .card-body p strong { color: var(--oscuro); }

    .btn-reservar { 
        background: transparent; 
        color: var(--verde-selva); 
        border: 2px solid var(--verde-selva); 
        width: 100%; 
        margin-bottom: 0.8rem; 
    }
    .btn-reservar:hover { background: #f0fdf4; }

    .btn-comprar { 
        background: var(--verde-selva); 
        color: white; 
        width: 100%; 
        font-size: 1rem;
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.2);
    }
    .btn-comprar:hover { 
        background: var(--naranja-tigre); 
        transform: scale(1.03); 
    }

    /* Footer */
    footer {
        background: var(--oscuro);
        color: rgba(255,255,255,0.7);
        text-align: center;
        padding: 4rem 1rem;
        margin-top: 6rem;
    }
    .footer-contact {
        margin-top: 15px;
        display: flex;
        justify-content: center;
        gap: 15px;
        flex-wrap: wrap; 
    }

    .footer-link {
        color: rgba(255, 255, 255, 0.6);
        text-decoration: none;
        font-size: 0.85rem;
        transition: var(--transicion);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .footer-link:hover {
        color: var(--amarillo-sol); 
    }

    .footer-sep {
        color: rgba(255, 255, 255, 0.2);
    }

    @media (max-width: 480px) {
        .footer-sep { display: none; } 
        .footer-contact { flex-direction: column; gap: 10px; }
    }

    @media (max-width: 768px) {
        nav { flex-direction: column; gap: 1.5rem; }
        .banner h1 { font-size: 2.2rem; }
    }
    </style>
</head>

<body>

<header>
    <nav>
        <div class="logo">🍃 ZooWonderland</div>

        <div class="menu">
            <a href="index.php">Inicio</a>
            <a href="#nosotros">Nosotros</a>
            <a href="#visitanos">Recorridos</a>
            <?php if ($esCliente): ?>
                <a href="index.php?r=reservar">Tours Grupales</a>
            <?php endif; ?>
            <?php if ($isLoggedIn && $user && $user->esAdministrador()): ?>
                <a href="index.php?r=admin/dashboard">Panel Admin</a>
            <?php endif; ?>
        </div>

        <div class="auth-area">
            <?php if ($isLoggedIn && $user): ?>
                <div class="user-welcome">
                    <span class="user-name">
                        <i class="fa-solid fa-user-check"></i> <?= htmlspecialchars($user->getNombreParaMostrar() ?? $user->nombre_usuario ?? 'Usuario') ?>
                    </span>
                    <div class="user-links">
                        <?php if ($esCliente): ?>
                            <a href="index.php?r=historial" title="Mi Historial"><i class="fa-solid fa-clock-rotate-left"></i></a>
                        <?php endif; ?>
                        <a href="index.php?r=perfil" title="Mi Perfil"><i class="fa-solid fa-circle-user"></i></a>
                        <a href="index.php?r=logout" class="logout" title="Cerrar Sesión"><i class="fa-solid fa-power-off"></i></a>
                    </div>
                </div>
            <?php else: ?>
                <a href="index.php?r=login" class="btn btn-login">Ingresar</a>
                <a href="index.php?r=registro" class="btn btn-registro">Registrarse</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<div class="banner">
    <div class="banner-content">
        <h1>Conecta con lo salvaje</h1>
        <p>Una aventura educativa para toda la familia en el corazón de la ciudad.</p>
    </div>
</div>

<main>
    <section id="nosotros">
        <h2>Nuestra Misión</h2>
        <p style="text-align:center; max-width:800px; margin:auto; font-size:1.1rem; color:#555; line-height: 1.8;">
            Desde 1998, en <strong>ZooWonderland</strong> trabajamos incansablemente por la conservación de especies en peligro 
            y la educación ambiental. No somos solo un zoológico, somos un santuario de biodiversidad.
        </p>
    </section>

    <section id="visitanos">
        <h2>Experiencias Disponibles</h2>

        <div class="grid">
            <?php if (empty($recorridos)): ?>
                <div style="grid-column:1/-1; text-align:center; padding: 3rem; background: white; border-radius: 20px;">
                    <p style="font-size:1.2rem; color:#888;">Estamos preparando nuevas rutas para ti. ¡Vuelve pronto!</p>
                </div>
            <?php else: ?>
                <?php foreach ($recorridos as $r): ?>
                    <div class="card">
                        <div class="card-header">
                            <?= htmlspecialchars($r['nombre'] ?? 'Recorrido Especial') ?>
                        </div>
                        <div class="card-body">
                            <p>
                                <span><i class="fa-solid fa-mountain-sun"></i> Tipo</span> 
                                <strong><?= htmlspecialchars($r['tipo'] ?? '-') ?></strong>
                            </p>
                            <p>
                                <span><i class="fa-solid fa-ticket"></i> Precio</span> 
                                <strong>Bs. <?= number_format($r['precio'] ?? 0, 2) ?></strong>
                            </p>
                            <p>
                                <span><i class="fa-solid fa-clock"></i> Tiempo</span> 
                                <strong><?= $r['duracion'] ?? '?' ?> min</strong>
                            </p>

                            <div style="margin-top:2rem;">
                                <?php if ($esCliente): ?>
                                    <a href="index.php?r=reservar&recorrido=<?= htmlspecialchars((string)($r['id_recorrido'] ?? $r['id'] ?? 0)) ?>"
                                       class="btn btn-reservar"><i class="fa-solid fa-calendar-check"></i> Reserva Grupal</a>

                                    <a href="index.php?r=compras/crear&recorrido=<?= htmlspecialchars((string)($r['id_recorrido'] ?? $r['id'] ?? 0)) ?>"
                                       class="btn btn-comprar"><i class="fa-solid fa-cart-shopping"></i> Comprar Ticket</a>
                                <?php elseif ($isLoggedIn): ?>
                                    <p style="color:#d32f2f; font-size: 0.85rem; text-align: center; font-weight: 700; padding: 10px; background: #fee2e2; border-radius: 10px;">
                                         <i class="fa-solid fa-circle-exclamation"></i> Solo clientes pueden comprar
                                    </p>
                                <?php else: ?>
                                    <a href="index.php?r=login" class="btn btn-comprar" style="width:100%">
                                        <i class="fa-solid fa-right-to-bracket"></i> Ingresa para comprar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>
<footer>
    <p style="font-size: 0.8rem; margin-top: 10px;">© <?= date('Y') ?> Compromiso con la Naturaleza. Todos los derechos reservados.</p>
    
    <div class="footer-contact">
        <a href="mailto:soporte@zoowonderland.com" class="footer-link">
            <i class="fa-solid fa-envelope"></i> soporte@zoowonderland.com
        </a>
        <span class="footer-sep">|</span>
        <a href="https://wa.me/59173216929?text=Hola!%20Me%20gustaría%20recibir%20más%20información%20sobre%20las%20visitas%20guiadas"
           target="_blank" 
           class="footer-link">
            <i class="fa-brands fa-whatsapp" style="color: #171817;"></i> 73216929
        </a>
    </div>

</footer>

</body>
</html>