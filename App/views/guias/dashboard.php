<?php
// app/Views/guias/dashboard.php
declare(strict_types=1);
$currentTab = 'recorridos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Recorridos – ZooWonderland</title>
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

        h1, h2, h3, .logo { font-family: 'Montserrat', sans-serif; }

        /* ── HEADER  ── */
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

        /* ── NAVEGACIÓN (TABS) INTEGRADA ── */
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
            letter-spacing: 0.5px;
            transition: var(--transicion);
            border-bottom: 4px solid transparent;
        }

        .nav-tabs a:hover {
            color: var(--blanco);
            background: rgba(255,255,255,0.05);
        }

        .nav-tabs a.active {
            color: var(--amarillo-sol);
            border-bottom: 4px solid var(--amarillo-sol);
            background: rgba(255,255,255,0.05);
        }

        /* ── CONTENIDO ── */
        main {
            max-width: 1200px; 
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .section-title {
            font-size: 2.2rem;
            color: var(--oscuro);
            margin-bottom: 2.5rem;
            text-align: center;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 5px;
            background: var(--amarillo-sol);
            margin: 10px auto;
            border-radius: 10px;
        }

        /* ── CARDS RECORRIDO ── */
        .recorrido-card {
            background: var(--blanco);
            border-radius: 25px;
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            box-shadow: var(--sombra);
            border-left: 8px solid var(--verde-selva);
            transition: var(--transicion);
        }
        
        .recorrido-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .card-fecha {
            font-weight: 700;
            color: var(--verde-selva);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .badge-tipo {
            font-weight: 800;
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            font-size: 0.75rem;
            text-transform: uppercase;
        }
        .badge-guiado { background: #e8f5e9; color: #2e7d32; }
        .badge-noguiado { background: #fff3e0; color: #e65100; }

        .card-nombre {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--oscuro);
            margin-bottom: 2rem;
        }

        /* ── META ITEMS  ── */
        .card-meta {
            display: grid;
            grid-template-columns: repeat(5, 1fr); 
            gap: 1rem;
            margin-bottom: 2.5rem;
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
            transition: var(--transicion);
        }

        .meta-item:hover {
            border-color: var(--amarillo-sol);
            transform: translateY(-3px);
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
            font-size: 1rem;
            font-weight: 700;
            color: var(--oscuro);
        }

        /* ── OCUPACIÓN ── */
        .ocupacion-bar { margin-bottom: 2.5rem; }
        .ocupacion-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .bar-track {
            background: #eee;
            border-radius: 50px;
            height: 14px;
            overflow: hidden;
        }
        .bar-fill { height: 100%; border-radius: 50px; transition: width 1s ease; }
        .bar-low { background: var(--verde-selva); }
        .bar-medium { background: var(--amarillo-sol); }
        .bar-high { background: var(--naranja-tigre); }

        .btn-detalle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 1.2rem;
            background: var(--verde-selva);
            color: white;
            border-radius: 18px;
            text-decoration: none;
            font-weight: 700;
            transition: var(--transicion);
        }
        
        .btn-detalle:hover {
            background: var(--oscuro);
            transform: scale(1.01);
        }

        footer {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--oscuro);
            color: rgba(255,255,255,0.5);
            margin-top: 5rem;
        }
        footer a { color: var(--amarillo-sol); text-decoration: none; }

        /* Responsive */
        @media (max-width: 992px) {
            .card-meta { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 600px) {
            .card-meta { grid-template-columns: repeat(2, 1fr); }
            .header-main { flex-direction: column; gap: 1rem; }
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

        <div class="header-right" style="display: flex; gap: 15px; align-items: center;">
            <div class="user-badge">
                <i class="fa-solid fa-circle-user"></i> 
                <?= htmlspecialchars($user->getNombreParaMostrar()) ?>
            </div>
            <a href="index.php?r=logout" style="color: #c62828; font-size: 1.3rem;" title="Cerrar Sesión">
                <i class="fa-solid fa-door-open"></i>
            </a>
        </div>
    </div>

    <nav class="nav-container">
        <?php require_once APP_PATH . '/Views/guias/partials/tabs.php'; ?>
    </nav>
</header>

<main>
    <h2 class="section-title">Panel de Recorridos</h2>

    <?php if (empty($recorridosAsignados)): ?>
        <div class="empty-state" style="text-align: center; padding: 5rem; background: white; border-radius: 25px;">
            <i class="fa-solid fa-calendar-xmark fa-4x" style="color:#ddd; margin-bottom:1.5rem;"></i>
            <p>No tienes recorridos asignados en este momento.</p>
        </div>
    <?php else: ?>
        <?php foreach ($recorridosAsignados as $rec): 
            $personas = (int)$rec['personas_asignadas'];
            $capacidad = (int)$rec['capacidad'];
            $pct = $capacidad > 0 ? round($personas / $capacidad * 100) : 0;
            $barClass = $pct < 50 ? 'bar-low' : ($pct < 80 ? 'bar-medium' : 'bar-high');
            $esGuiado = strtolower($rec['tipo']) === 'guiado';
            $fechaFmt = date('d/m/Y', strtotime($rec['fecha_asignacion']));
            
            // Lógica de horas
            $horaInicio = '09:00'; 
            $durMin = (int)$rec['duracion'];
            $horaFinTs = strtotime("1970-01-01 {$horaInicio}:00") + $durMin * 60;
            $horaFin = date('H:i', $horaFinTs);
        ?>
        <div class="recorrido-card">
            <div class="card-top">
                <div class="card-fecha">
                    <i class="fa-solid fa-calendar-check"></i> <?= $fechaFmt ?>
                </div>
                <span class="badge-tipo <?= $esGuiado ? 'badge-guiado' : 'badge-noguiado' ?>">
                    <?= htmlspecialchars($rec['tipo']) ?>
                </span>
            </div>

            <div class="card-nombre"><?= htmlspecialchars($rec['nombre']) ?></div>

            <div class="card-meta">
                <div class="meta-item">
                    <div class="meta-label">Duración</div>
                    <i class="fa-solid fa-stopwatch"></i>
                    <div class="meta-value"><?= $durMin ?> min</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Inicio</div>
                    <i class="fa-regular fa-clock"></i>
                    <div class="meta-value"><?= $horaInicio ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Fin Aprox.</div>
                    <i class="fa-solid fa-hourglass-end"></i>
                    <div class="meta-value"><?= $horaFin ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Precio</div>
                    <i class="fa-solid fa-tags"></i>
                    <div class="meta-value">Bs <?= number_format((float)$rec['precio'], 2) ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Cupos Máx.</div>
                    <i class="fa-solid fa-users-viewfinder"></i>
                    <div class="meta-value"><?= $capacidad ?></div>
                </div>
            </div>

            <div class="ocupacion-bar">
                <div class="ocupacion-label">
                    <span>Nivel de ocupación</span>
                    <strong><?= $personas ?> / <?= $capacidad ?> visitantes</strong>
                </div>
                <div class="bar-track">
                    <div class="bar-fill <?= $barClass ?>" style="width:<?= $pct ?>%"></div>
                </div>
            </div>

            <div class="actions" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <a href="index.php?r=guias/detalle-recorrido&id=<?= $rec['id_recorrido'] ?>" class="btn-detalle" style="padding: 0.8rem; font-size: 0.85rem;">
                    Áreas y detalles <i class="fa-solid fa-arrow-right"></i>
                </a>

                <?php if ($rec['tiene_reporte']): ?>
                    <a href="index.php?r=guias/reportes-historial" class="btn-detalle" style="padding: 0.8rem; font-size: 0.85rem; background: var(--amarillo-sol); color: #000;">
                        <i class="fa-solid fa-check-circle"></i> Ver Reporte
                    </a>
                <?php else: ?>
                    <a href="index.php?r=guias/reportes-crear&id_gr=<?= $rec['id_guia_recorrido'] ?>" class="btn-detalle" style="padding: 0.8rem; font-size: 0.85rem; background: var(--naranja-tigre);">
                        <i class="fa-solid fa-pen-to-square"></i> Reportar Final
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<footer>
    <p><strong>ZooWonderland</strong> &copy; <?= date('Y') ?> | Panel de Gestión de Guías</p>
    <div style="margin-top:15px">
        <a href="index.php">Inicio Público</a> • <a href="index.php?r=logout">Cerrar Sesión</a>
    </div>
</footer>

</body>
</html>