<?php
// app/Views/guias/horarios.php
declare(strict_types=1);
$currentTab = 'horarios';

/* ══════════════════════════════════════════════════════════════
   LÓGICA PHP ORIGINAL (SIN CAMBIOS)
   ══════════════════════════════════════════════════════════════ */

// Semana seleccionada (0 = esta semana, 1 = siguiente)
$semanaOffset = (int)($_GET['semana'] ?? 0);
$semanaOffset = max(0, min(1, $semanaOffset)); 

// Calcular lunes de la semana seleccionada
$hoy   = new \DateTime();
$lunes = clone $hoy;
$lunes->modify('Monday this week');
if ($semanaOffset === 1) {
    $lunes->modify('+7 days');
}

// Días mar → dom (índices 1-6 desde lunes)
$diasSemana = [];
for ($i = 1; $i <= 6; $i++) {
    $d = clone $lunes;
    $d->modify("+{$i} days");
    $diasSemana[] = $d;
}

$fechaInicio = $diasSemana[0]->format('Y-m-d');
$fechaFin    = $diasSemana[5]->format('Y-m-d');

// Parsear horario laboral
$horaInicioLaboral = '09:00';
$horaFinLaboral    = '15:00';
if ($datosGuia && !empty($datosGuia['horarios'])) {
    $partes = array_map('trim', explode('-', $datosGuia['horarios']));
    if (count($partes) === 2) {
        $horaInicioLaboral = $partes[0];
        $horaFinLaboral    = $partes[1];
    }
}

if (!isset($recorridosPorSemana)) {
    $recorridosPorSemana = [];
}

/**
 * Calcula hora inicio y fin de cada recorrido del día
 */
function calcularHorariosDelDia(array $recorridos, string $horaInicioBase): array
{
    $cursor = strtotime("1970-01-01 {$horaInicioBase}:00");
    foreach ($recorridos as &$rec) {
        $durSeg             = (int)$rec['duracion'] * 60;
        $rec['hora_inicio'] = date('H:i', $cursor);
        $rec['hora_fin']    = date('H:i', $cursor + $durSeg);
        $cursor += $durSeg + 15 * 60; // +15 min pausa
    }
    unset($rec);
    return $recorridos;
}

$nombreDiaEs = [
    'Monday'    => 'Lunes', 'Tuesday'   => 'Martes', 'Wednesday' => 'Miércoles',
    'Thursday'  => 'Jueves', 'Friday'    => 'Viernes', 'Saturday'  => 'Sábado', 'Sunday'    => 'Domingo',
];

$meses = [
    1=>'ene',2=>'feb',3=>'mar',4=>'abr',5=>'may',6=>'jun',
    7=>'jul',8=>'ago',9=>'sep',10=>'oct',11=>'nov',12=>'dic'
];

$semanaLabel = $diasSemana[0]->format('j ') . $meses[(int)$diasSemana[0]->format('n')]
    . ' – ' . $diasSemana[5]->format('j ') . $meses[(int)$diasSemana[5]->format('n')]
    . ' ' . $diasSemana[0]->format('Y');

function colorRecorrido(string $nombre): array {
    $colores = [
        'Felinos VIP'           => ['#fff3e0', '#e65100', '#ffcc80'],
        'Osos Andinos'          => ['#e8f5e9', '#2e7d32', '#a5d6a7'],
        'Cóndores'              => ['#e3f2fd', '#1565c0', '#90caf9'],
        'Acuario'               => ['#e0f7fa', '#006064', '#80deea'],
        'Recorrido General'     => ['#f3e5f5', '#6a1b9a', '#ce93d8'],
        'Recorrido Interactivo' => ['#fce4ec', '#880e4f', '#f48fb1'],
    ];
    foreach ($colores as $key => $val) {
        if (stripos($nombre, explode(' ', $key)[0]) !== false) return $val;
    }
    return ['#f5f5f5', '#424242', '#bdbdbd'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Horarios – ZooWonderland</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --verde-selva:   #2e7d32;
            --amarillo-sol:  #ffca28;
            --gris-claro:    #f8faf8;
            --blanco:        #ffffff;
            --oscuro:        #0d3a1f;
            --texto:         #333333;
            --border:        #e0eadd;
            --sombra:        0 10px 30px rgba(0,0,0,0.06);
            --transicion:    all 0.3s ease;
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        body { 
            font-family: 'Open Sans', sans-serif; 
            background-color: var(--gris-claro); 
            color: var(--texto); 
        }
        h1, h2, h3, .logo { font-family: 'Montserrat', sans-serif; }

        header { 
            background: var(--blanco); 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
        }
        .header-main { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 1rem 5%; 
        }
        .logo { 
            font-size: 1.5rem; 
            font-weight: 800; 
            color: var(--verde-selva); 
            text-decoration: none; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        .user-badge { 
            background: #f0f4f0; 
            padding: 0.5rem 1rem;
            border-radius: 12px; 
            font-weight: 700;
            color: var(--verde-selva); 
            font-size: 0.85rem; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
        }

        .nav-container { 
            background: var(--oscuro); 
            padding: 0 5%; }
        .nav-tabs { 
            display: flex; 
            list-style: none; 
            overflow-x: auto; 
        }
        .nav-tabs a { 
            color: rgba(255,255,255,0.7); 
            text-decoration: none; 
            padding: 1rem 1.5rem; 
            font-weight: 700; 
            font-size: 0.8rem; 
            text-transform: uppercase; 
            border-bottom: 4px solid transparent; 
            transition: var(--transicion); 
            white-space: nowrap; }
        .nav-tabs a.active { 
            color: var(--amarillo-sol); 
            border-bottom-color: var(--amarillo-sol); 
            background: rgba(255,255,255,0.05); 
        }

        main { 
            max-width: 1200px; 
            margin: 2rem auto; 
            padding: 0 1.5rem; 
        }

        .top-bar { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 2rem; 
            flex-wrap: wrap; 
            gap: 1rem; 
        }
        .semana-nav { 
            display: flex; 
            align-items: center; 
            background: var(--blanco); 
            padding: 0.4rem; 
            border-radius: 50px; 
            box-shadow: var(--sombra); 
            border: 1px solid var(--border); 
        }
        .btn-semana { 
            padding: 0.6rem 1.2rem; 
            border-radius: 50px; 
            text-decoration: none; 
            font-weight: 700; 
            font-size: 0.75rem; 
            color: var(--verde-selva); 
            transition: var(--transicion); 
        }
        .btn-semana:hover:not(.disabled) { 
            background: var(--verde-selva); 
            color: var(--blanco); 
        }
        .btn-semana.disabled { 
            color: #ccc; 
            cursor: not-allowed; 
        }
        .semana-label { 
            font-weight: 800; 
            color: var(--oscuro); 
            padding: 0 1.5rem; 
            font-size: 0.9rem; 
        }

        .horario-general { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); 
            gap: 1rem; 
            margin-bottom: 2rem; 
        }
        .info-chip { 
            background: var(--blanco); 
            padding: 1.2rem; 
            border-radius: 16px; 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            border: 1px solid var(--border); 
            box-shadow: 0 4px 6px rgba(0,0,0,0.02); 
        }
        .info-chip i { 
            font-size: 1.2rem; 
            color: var(--verde-selva); 
            background: #f0f7f0; 
            width: 42px; 
            height: 42px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border-radius: 10px; 
        }
        .chip-label { 
            font-size: 0.65rem; 
            text-transform: uppercase; 
            color: #757575; 
            font-weight: 800; 
            letter-spacing: 0.5px; 
        }
        .chip-val { 
            font-size: 1rem; 
            font-weight: 700; 
            color: var(--oscuro); 
        }

        .calendar-grid { 
            display: grid; 
            grid-template-columns: repeat(6, 1fr); 
            gap: 12px; 
        }
        .day-card { 
            background: var(--blanco); 
            border-radius: 18px; 
            border: 1px solid var(--border); 
            overflow: hidden; 
            display: flex; 
            flex-direction: column; 
            transition: var(--transicion); 
        }
        .day-card.today { 
            border: 2px solid var(--amarillo-sol); 
            position: relative;
        }
        .day-card.no-trabajo { 
            background: #fdfdfd; 
            opacity: 0.65; 
        }
        
        .day-header { 
            padding: 1rem; 
            background: #fbfcfb; 
            border-bottom: 1px solid var(--border); 
            text-align: center; 
        }
        .day-name { 
            font-weight: 800; 
            font-size: 0.75rem; 
            color: var(--verde-selva); 
            text-transform: uppercase; 
        }
        .day-date { 
            font-size: 0.75rem; 
            color: #757575; 
            font-weight: 600; 
        }
        
        .today-badge { 
            position: absolute; 
            top: -10px; left: 50%; 
            transform: translateX(-50%); 
            background: var(--amarillo-sol); 
            color: var(--oscuro); 
            font-size: 0.6rem; 
            font-weight: 800; 
            padding: 2px 10px; 
            border-radius: 10px; 
            z-index: 5; 
        }

        .turno-bar { 
            background: var(--oscuro); 
            color: var(--blanco); 
            font-size: 0.65rem; 
            padding: 4px; 
            text-align: center; 
            font-weight: 700; 
        }
        .day-body { 
            padding: 10px; 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            gap: 8px; 
        }

        .recorrido-slot { 
            padding: 0.7rem; 
            border-radius: 12px; 
            border-left: 4px solid; 
            font-size: 0.8rem; 
        }
        .slot-nombre { 
            font-size: 0.75rem; 
            font-weight: 800; 
            margin-bottom: 4px; 
            line-height: 1.2; 
        }
        .slot-hora { 
            font-size: 0.68rem; 
            font-weight: 700; 
            display: flex; 
            align-items: center; 
            gap: 4px; 
            margin-bottom: 2px; 
        }
        .slot-personas { 
            font-size: 0.65rem; 
            opacity: 0.8; 
            font-weight: 600; 
        }

        .dia-libre { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            font-size: 0.7rem; 
            color: #bbb; 
            font-weight: 600; 
            padding: 2rem 0; 
            text-align: center; 
        }
        .dia-libre i { 
            font-size: 1.5rem; 
            margin-bottom: 8px;
            opacity: 0.3; 
        }

        .leyenda { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 1.2rem; 
            margin-top: 2rem; 
            background: var(--blanco); 
            padding: 1.2rem; 
            border-radius: 15px; 
            border: 1px solid var(--border); 
        }
        .leyenda-item { 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            font-size: 0.7rem; 
            font-weight: 700; 
            color: #757575; 
        }
        .leyenda-dot { 
            width: 10px; 
            height: 10px; 
            border-radius: 50%; 
        }
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
        .btn-back:hover { 
            color: var(--oscuro);
            transform: translateX(-5px); 
        }

        footer { 
            text-align: center; 
            padding: 3rem 1rem; 
            background: var(--oscuro);
            color: rgba(255,255,255,0.4); 
            margin-top: 4rem; 
            font-size: 0.8rem; 
        }
        footer a { 
            color: var(--amarillo-sol); 
            text-decoration: none; 
            font-weight: 700; 
        }

        @media (max-width: 1100px) { .calendar-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 768px) { .calendar-grid { grid-template-columns: repeat(2, 1fr); } .top-bar { flex-direction: column; } }
        @media (max-width: 480px) { .calendar-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<header>
    <div class="header-main">
        <a href="index.php" class="logo"><i class="fa-solid fa-leaf"></i> <span>ZooWonderland</span></a>
        <div class="user-badge"><i class="fa-solid fa-circle-user"></i> <?= htmlspecialchars($user->getNombreParaMostrar()) ?></div>
    </div>
    <nav class="nav-container">
        <div class="nav-tabs">
            <a href="index.php?r=guias/dashboard">Mis Recorridos</a>
            <a href="index.php?r=guias/horarios" class="active">Mis Horarios</a>
        </div>
    </nav>
</header>

<main>
    <div class="top-bar">
        <h2 class="section-title">Calendario Semanal</h2>
        <div class="semana-nav">
            <a href="index.php?r=guias/horarios&semana=0" class="btn-semana <?= $semanaOffset === 0 ? 'disabled' : '' ?>"><i class="fa-solid fa-chevron-left"></i> Esta semana</a>
            <span class="semana-label"><?= $semanaLabel ?></span>
            <a href="index.php?r=guias/horarios&semana=1" class="btn-semana <?= $semanaOffset === 1 ? 'disabled' : '' ?>">Siguiente <i class="fa-solid fa-chevron-right"></i></a>
        </div>
    </div>

    <?php if (!$datosGuia): ?>
        <div style="text-align:center; padding:5rem 2rem; background:white; border-radius:20px; border:2px dashed var(--border);">
            <i class="fa-solid fa-calendar-xmark" style="font-size:3rem; color:#ccc; margin-bottom:1rem;"></i>
            <p style="color:#757575; font-weight:600;">No tienes horarios asignados para este periodo.</p>
        </div>
    <?php else: ?>

        <div class="horario-general">
            <div class="info-chip"><i class="fa-solid fa-clock"></i><div><div class="chip-label">Entrada</div><div class="chip-val"><?= htmlspecialchars($horaInicioLaboral) ?></div></div></div>
            <div class="info-chip"><i class="fa-solid fa-door-open"></i><div><div class="chip-label">Salida</div><div class="chip-val"><?= htmlspecialchars($horaFinLaboral) ?></div></div></div>
            <div class="info-chip"><i class="fa-solid fa-calendar-check"></i><div><div class="chip-label">Jornada</div><div class="chip-val"><?= htmlspecialchars($datosGuia['dias_trabajo']) ?></div></div></div>
        </div>

        <div class="calendar-grid">
            <?php foreach ($diasSemana as $dia):
                $fechaKey = $dia->format('Y-m-d');
                $esHoy    = $fechaKey === $hoy->format('Y-m-d');
                $enNombre = $dia->format('l');

                $diasTrabajoStr = strtolower($datosGuia['dias_trabajo'] ?? '');
                $diaNombreEs    = strtolower($nombreDiaEs[$enNombre] ?? '');
                $trabaja = false;
                if (strpos($diasTrabajoStr, ' a ') !== false) {
                    preg_match('/(\w+)\s+a\s+(\w+)/', $diasTrabajoStr, $m);
                    if (count($m) === 3) {
                        $orden   = ['lunes','martes','miércoles','jueves','viernes','sábado','domingo'];
                        $desde   = array_search($m[1], $orden);
                        $hasta   = array_search($m[2], $orden);
                        $pos     = array_search($diaNombreEs, $orden);
                        $trabaja = ($desde !== false && $hasta !== false && $pos !== false && $pos >= $desde && $pos <= $hasta);
                    }
                } else {
                    $trabaja = strpos($diasTrabajoStr, $diaNombreEs) !== false;
                }

                $recorridosDia = isset($recorridosPorSemana[$fechaKey]) ? calcularHorariosDelDia($recorridosPorSemana[$fechaKey], $horaInicioLaboral) : [];
            ?>
                <div class="day-card <?= $esHoy ? 'today' : '' ?> <?= !$trabaja ? 'no-trabajo' : '' ?>">
                    <?php if ($esHoy): ?><span class="today-badge">HOY</span><?php endif; ?>
                    <div class="day-header">
                        <div class="day-name"><?= $nombreDiaEs[$enNombre] ?? $enNombre ?></div>
                        <div class="day-date"><?= $dia->format('d') ?> / <?= $meses[(int)$dia->format('n')] ?></div>
                    </div>
                    <?php if ($trabaja): ?>
                        <div class="turno-bar"><?= $horaInicioLaboral ?> - <?= $horaFinLaboral ?></div>
                        <div class="day-body">
                            <?php if (empty($recorridosDia)): ?>
                                <div class="dia-libre"><i class="fa-solid fa-circle-info"></i>Sin tareas</div>
                            <?php else: ?>
                                <?php foreach ($recorridosDia as $rec): 
                                    [$bg, $text, $border] = colorRecorrido($rec['nombre']);
                                ?>
                                    <div class="recorrido-slot" style="background:<?= $bg ?>; border-color:<?= $border ?>; color:<?= $text ?>;">
                                        <div class="slot-nombre"><?= htmlspecialchars($rec['nombre']) ?></div>
                                        <div class="slot-hora"><i class="fa-regular fa-clock"></i> <?= $rec['hora_inicio'] ?> – <?= $rec['hora_fin'] ?></div>
                                        <div class="slot-personas"><i class="fa-solid fa-users"></i> <?= (int)$rec['personas_asignadas'] ?>/<?= (int)$rec['capacidad'] ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="dia-libre"><i class="fa-solid fa-mug-hot"></i>Descanso</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="leyenda">
            <div class="leyenda-item"><div class="leyenda-dot" style="background:#e65100;"></div> Felinos VIP</div>
            <div class="leyenda-item"><div class="leyenda-dot" style="background:#2e7d32;"></div> Osos Andinos</div>
            <div class="leyenda-item"><div class="leyenda-dot" style="background:#1565c0;"></div> Cóndores</div>
            <div class="leyenda-item"><div class="leyenda-dot" style="background:#006064;"></div> Acuario</div>
            <div class="leyenda-item"><div class="leyenda-dot" style="background:#6a1b9a;"></div> General</div>
            <div class="leyenda-item"><div class="leyenda-dot" style="background:#880e4f;"></div> Interactivo</div>
        </div>
        <a href="index.php?r=guias/dashboard" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Volver a mis recorridos
    </a>
    <?php endif; ?>
</main>

<footer>
    <p><strong>ZooWonderland</strong> &copy; <?= date('Y') ?> | Panel de Gestión de Guías</p>
    <div style="margin-top:15px">
        <a href="index.php">Inicio Publico</a> • <a href="index.php?r=logout">Cerrar Sesión</a>
    </div>
</footer>

</body>
</html>