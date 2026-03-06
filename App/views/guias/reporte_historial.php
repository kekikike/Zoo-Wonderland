<?php
/** @var array $reportes */
/** @var \App\Models\Usuario $user */
$mensajeExito = $_SESSION['mensaje_exito'] ?? null;
if ($mensajeExito) unset($_SESSION['mensaje_exito']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Reportes - ZooWonderland</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --verde-selva:   #2e7d32;
            --verde-oscuro:  #1b5e20;
            --amarillo-sol:  #ffca28;
            --gris-claro:    #f8faf8;
            --oscuro:        #0d3a1f;
            --blanco:        #ffffff;
            --sombra:        0 10px 30px rgba(0,0,0,0.08);
        }
        body { font-family: 'Open Sans', sans-serif; background: var(--gris-claro); padding: 2rem; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        h1 { font-family: 'Montserrat', sans-serif; color: var(--oscuro); margin: 0; }
        .alert { padding: 1.2rem; border-radius: 12px; margin-bottom: 2rem; background: #e8f5e9; color: var(--verde-oscuro); border: 1px solid #c8e6c9; font-weight: 700; display: flex; align-items: center; gap: 0.8rem; }
        .btn-new { display: inline-flex; align-items: center; gap: 0.5rem; background: var(--verde-selva); color: white; padding: 0.8rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 700; }
        .btn-new:hover { background: var(--verde-oscuro); }
        .reporte-card { 
            background: white; 
            border-radius: 15px; 
            padding: 1.5rem; 
            margin-bottom: 1.5rem; 
            box-shadow: var(--sombra); 
            border-left: 6px solid var(--amarillo-sol);
        }
        .reporte-top { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            margin-bottom: 1rem; 
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }
        .recorrido-title { 
            font-size: 1.2rem; 
            margin: 0;
            color: var(--verde-selva); 
            font-weight: 800; 
            font-family: 'Montserrat', sans-serif;
            flex: 1;
        }
        .fecha-guardado { 
            font-size: 0.85rem; 
            color: #999;
        }
        .obs-text { 
            line-height: 1.8; 
            color: #444; 
            background: #f9fbf9; 
            padding: 1.2rem; 
            border-radius: 10px; 
            border: 1px solid #eee; 
            font-style: italic;
            margin-bottom: 1rem;
        }
        .empty-state { 
            text-align: center; 
            padding: 5rem; 
            background: white; 
            border-radius: 20px; 
            color: #bbb;
            box-shadow: var(--sombra);
        }
        .empty-icon { font-size: 4rem; color: #ddd; margin-bottom: 1rem; }
        .badge-inmutable {
            background: #f3e5f5;
            color: #6a1b9a;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Historial de Reportes</h1>
        <a href="index.php?r=guias/reportes-crear" class="btn-new">
            <i class="fa-solid fa-plus"></i> Nuevo Reporte
        </a>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div style="font-weight: 800; color: var(--verde-selva);">
            <i class="fa-solid fa-user"></i> Guia: <?= htmlspecialchars($user->getNombreParaMostrar()) ?>
        </div>
        <a href="index.php?r=guias/dashboard" style="color: #666; text-decoration: none; font-weight: 700;">
            <i class="fa-solid fa-house"></i> Panel
        </a>
    </div>

    <?php if ($mensajeExito): ?>
        <div class="alert">
            <i class="fa-solid fa-circle-check"></i>
            <?= htmlspecialchars($mensajeExito) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($reportes)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fa-solid fa-file-alt"></i>
            </div>
            <h2>No hay reportes aun</h2>
            <p>Cuando registres reportes de tus recorridos, apareceran aqui</p>
            <a href="index.php?r=guias/reportes-crear" style="color: var(--verde-selva); text-decoration: none; font-weight: 700; margin-top: 1rem;">
                <i class="fa-solid fa-plus"></i> Registrar tu primer reporte
            </a>
        </div>
    <?php else: ?>
        <div>
            <?php foreach ($reportes as $reporte): ?>
                <div class="reporte-card">
                    <div class="reporte-top">
                        <h2 class="recorrido-title">
                            <i class="fa-solid fa-map"></i>
                            <?= htmlspecialchars($reporte['recorrido_nombre']) ?>
                        </h2>
                        <div style="text-align: right;">
                            <div class="fecha-guardado">
                                <i class="fa-solid fa-calendar-days"></i>
                                <?= (new DateTime($reporte['fecha_reporte']))->format('d/m/Y H:i') ?>
                            </div>
                            <div class="badge-inmutable">
                                <i class="fa-solid fa-lock"></i> Inmutable
                            </div>
                        </div>
                    </div>

                    <div class="obs-text">
                        <?= nl2br(htmlspecialchars($reporte['observaciones'])) ?>
                    </div>

                    <div style="font-size: 0.85rem; color: #999; border-top: 1px solid #eee; padding-top: 0.8rem;">
                        <i class="fa-solid fa-info-circle"></i>
                        Guardado el <?= (new DateTime($reporte['fecha_reporte']))->format('d \\de M \\de Y \\a \\la\\s H:i') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
