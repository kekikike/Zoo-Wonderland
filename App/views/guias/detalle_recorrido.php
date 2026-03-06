<?php
// app/Views/guias/detalle_recorrido.php
declare(strict_types=1);
$currentTab = 'recorridos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Recorrido – ZooWonderland</title>
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
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Open Sans', sans-serif;
            background-color: var(--gris-claro);
            color: #333;
            line-height: 1.6;
        }

        h1, h2, h3, .logo, .hero-nombre { font-family: 'Montserrat', sans-serif; }

        /* ── HEADER (Igual al Dashboard) ── */
        header {
            background: var(--blanco);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .header-main {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 3%; 
        }

        .logo { 
            font-size: 1.6rem; 
            font-weight: 800; 
            color: var(--verde-selva); 
            text-transform: uppercase; 
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-badge { 
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f0f4f0; 
            padding: 0.6rem 1.2rem; 
            border-radius: 12px; 
            font-weight: 700; 
            color: var(--verde-selva);
            font-size: 0.9rem;
        }

        /* ── NAVEGACIÓN ── */
        .nav-container {
            background: var(--oscuro);
            padding: 0 3%;
        }

        .nav-tabs {
            display: flex;
            gap: 5px;
            list-style: none;
        }

        .nav-tabs a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            padding: 1rem 2rem;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            transition: var(--transicion);
            border-bottom: 4px solid transparent;
        }

        .nav-tabs a.active {
            color: var(--amarillo-sol);
            border-bottom-color: var(--amarillo-sol);
            background: rgba(255,255,255,0.05);
        }

        /* ── CONTENIDO ── */
        main {
            max-width: 1200px; 
            margin: 2.5rem auto;
            padding: 0 2rem;
        }

        .breadcrumb {
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .breadcrumb a { color: var(--verde-selva); text-decoration: none; }
        .breadcrumb span { color: #999; margin: 0 8px; }

        /* ── HERO CARD (ESTILO MEJORADO) ── */
        .hero-card {
            background: var(--blanco);
            border-radius: 25px;
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            box-shadow: var(--sombra);
            border-top: 8px solid var(--azul-cielo);
        }

        .hero-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .hero-nombre {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--oscuro);
        }

        .badge-tipo {
            font-weight: 800;
            padding: 0.6rem 1.4rem;
            border-radius: 50px;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        .badge-guiado { background: #e8f5e9; color: #2e7d32; }
        .badge-noguiado { background: #fff3e0; color: #e65100; }

        /* ── META GRID (5 EN LÍNEA IGUAL QUE DASHBOARD) ── */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr); 
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .meta-item {
            background: #f9fbf9;
            padding: 1.2rem 0.5rem;
            border-radius: 18px;
            border: 1px solid #eee;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .meta-item i {
            font-size: 1.3rem;
            color: var(--verde-selva);
            margin-bottom: 10px;
        }

        .meta-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            color: #666;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .meta-value {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--oscuro);
        }

        /* ── ÁREAS ── */
        .areas-section h3 {
            font-size: 1.6rem;
            color: var(--oscuro);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .areas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .area-card {
            background: var(--blanco);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.04);
            border: 1px solid #eee;
            transition: var(--transicion);
        }

        .area-card:hover {
            border-color: var(--verde-selva);
            transform: translateY(-5px);
        }

        .area-nombre {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--verde-selva);
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .area-desc {
            font-size: 0.88rem;
            color: #555;
            margin-bottom: 1.2rem;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.4rem 0.8rem;
            border-radius: 10px;
            text-transform: uppercase;
        }
        .status-restringido { background: #ffebee; color: #c62828; }
        .status-libre { background: #e8f5e9; color: #2e7d32; }

        /* Barra Ocupación */
        .ocupacion-wrap { margin-top: 1.5rem; }
        .bar-track { background: #eee; height: 12px; border-radius: 10px; overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 10px; }
        .bar-low { background: var(--verde-selva); }
        .bar-medium { background: var(--amarillo-sol); }
        .bar-high { background: var(--naranja-tigre); }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 3rem;
            color: var(--verde-selva);
            text-decoration: none;
            font-weight: 700;
            transition: var(--transicion);
        }
        .btn-back:hover { color: var(--oscuro); transform: translateX(-5px); }

        footer {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--oscuro);
            color: rgba(255,255,255,0.5);
            margin-top: 5rem;
        }
        footer a { color: var(--amarillo-sol); text-decoration: none; }

        @media (max-width: 900px) {
            .meta-grid { grid-template-columns: repeat(3, 1fr); }
            .hero-nombre { font-size: 1.6rem; }
        }
    </style>
</head>
<body>

<header>
    <div class="header-main">
        <a href="index.php" class="logo">
            <i class="fa-solid fa-leaf"></i>
            <span>ZooWonderland</span>
        </a>
        <div class="user-badge">
            <i class="fa-solid fa-circle-user"></i> 
            <?= htmlspecialchars($user->getNombreParaMostrar()) ?>
        </div>
    </div>
    <nav class="nav-container">
        <div class="nav-tabs">
            <a href="index.php?r=guias/dashboard" class="active">Mis Recorridos</a>
            <a href="index.php?r=guias/horarios">Mis Horarios</a>
        </div>
    </nav>
</header>

<main>
    <div class="breadcrumb">
        <a href="index.php?r=guias/dashboard">Panel</a> 
        <span><i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i></span> 
        Detalle del Recorrido
    </div>

    <?php
        $personas  = (int)$recorrido['personas_asignadas'];
        $capacidad = (int)$recorrido['capacidad'];
        $pct       = $capacidad > 0 ? round($personas / $capacidad * 100) : 0;
        $barClass  = $pct < 50 ? 'bar-low' : ($pct < 80 ? 'bar-medium' : 'bar-high');
        $esGuiado  = strtolower($recorrido['tipo']) === 'guiado';
        $fechaFmt  = date('d/m/Y', strtotime($recorrido['fecha_asignacion']));
        $durMin    = (int)$recorrido['duracion'];
        $horaInicio = '09:00';
        $horaFin    = date('H:i', strtotime("1970-01-01 {$horaInicio}:00") + $durMin * 60);
    ?>

   <div class="hero-card">
    <div class="hero-top">
        <h2 class="hero-nombre"><?= htmlspecialchars($recorrido['nombre']) ?></h2>
        <span class="badge-tipo <?= $esGuiado ? 'badge-guiado' : 'badge-noguiado' ?>">
            <i class="fa-solid <?= $esGuiado ? 'fa-person-chalkboard' : 'fa-shoe-prints' ?>"></i>
            <?= htmlspecialchars($recorrido['tipo']) ?>
        </span>
    </div>

        <div class="meta-grid">
            <div class="meta-item">
                <div class="meta-label">Fecha</div>
                <i class="fa-solid fa-calendar-day"></i>
                <div class="meta-value"><?= $fechaFmt ?></div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Inicio</div>
                <i class="fa-regular fa-clock"></i>
                <div class="meta-value"><?= $horaInicio ?></div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Fin Estimado</div>
                <i class="fa-solid fa-hourglass-end"></i>
                <div class="meta-value"><?= $horaFin ?></div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Duración</div>
                <i class="fa-solid fa-stopwatch"></i>
                <div class="meta-value"><?= $durMin ?> min</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Precio</div>
                <i class="fa-solid fa-tags"></i>
                <div class="meta-value">Bs <?= number_format((float)$recorrido['precio'], 2) ?></div>
            </div>
            <div class="meta-item">
            <div class="meta-label">Compradores</div>
            <i class="fa-solid fa-users"></i>
            <div class="meta-value">
                <?= $recorrido['personas_asignadas'] ?? 0 ?> / <?= $recorrido['capacidad'] ?? '?' ?>
            </div>
        </div>
        </div>

        <div class="ocupacion-wrap">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-weight:700;">
                <span>Ocupación actual</span>
                <span><?= $personas ?> / <?= $capacidad ?> visitantes</span>
            </div>
            <div class="bar-track">
                <div class="bar-fill <?= $barClass ?>" style="width:<?= $pct ?>%"></div>
            </div>
        </div>
    </div>

    <div class="areas-section">
        <h3><i class="fa-solid fa-map-location-dot" style="color: var(--amarillo-sol);"></i> Áreas del Recorrido</h3>

        <?php if (empty($areas)): ?>
            <p style="color: #888; font-style: italic; background: white; padding: 2rem; border-radius: 15px; text-align: center;">
                No hay áreas registradas para este recorrido.
            </p>
        <?php else: ?>
            <div class="areas-grid">
                <?php foreach ($areas as $area): ?>
                <div class="area-card">
                    <div class="area-nombre">
                        <i class="fa-solid fa-location-dot"></i>
                        <?= htmlspecialchars($area['nombre']) ?>
                    </div>
                    <?php if (!empty($area['descripcion'])): ?>
                        <div class="area-desc"><?= htmlspecialchars($area['descripcion']) ?></div>
                    <?php endif; ?>
                    
                    <?php if ((int)$area['restringida'] === 1): ?>
                        <span class="badge-status status-restringido">
                            <i class="fa-solid fa-lock"></i> Acceso restringido
                        </span>
                    <?php else: ?>
                        <span class="badge-status status-libre">
                            <i class="fa-solid fa-door-open"></i> Acceso libre
                        </span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($recorrido['nombres_compradores'])): ?>
        <div style="margin-top: 2rem; background: #f9fff9; padding: 1.5rem; border-radius: 12px; border: 1px solid #e0f2e9;">
            <h3 style="color: #2e7d32; margin-bottom: 1rem;">
                <i class="fa-solid fa-user-check"></i> Clientes que compraron este recorrido
            </h3>
            <ul style="list-style: none; padding: 0; font-size: 1.05rem; line-height: 1.7;">
                <?php
                $nombres = explode(', ', $recorrido['nombres_compradores']);
                foreach ($nombres as $nombre):
                ?>
                    <li style="padding: 6px 0; border-bottom: 1px dashed #ccc;">
                        <i class="fa-solid fa-user" style="color:#2e7d32; margin-right:8px;"></i>
                        <?= htmlspecialchars($nombre) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <div style="margin-top: 2rem; text-align:center; color:#777; font-style:italic;">
            Aún no hay compradores/tickets para este recorrido.
        </div>
    <?php endif; ?>
    </div>

    <a href="index.php?r=guias/dashboard" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Volver a mis recorridos
    </a>
</main>

<footer>
    <p><strong>ZooWonderland</strong> &copy; <?= date('Y') ?> | Panel de Gestión de Guías</p>
    <div style="margin-top:15px">
        <a href="index.php">Inicio Publico</a> • <a href="index.php?r=logout">Cerrar Sesión</a>
    </div>
</footer>

</body>
</html>