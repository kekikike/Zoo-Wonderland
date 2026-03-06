<?php
/** @var array $recorridosSinReporte */
/** @var \App\Models\Usuario $user */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seleccionar Recorrido para Reportar - ZooWonderland</title>
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
        body { 
            font-family: 'Open Sans', sans-serif; 
            background: var(--gris-claro); 
            padding: 2rem; 
        }
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }
        h1 { 
            font-family: 'Montserrat', sans-serif; 
            color: var(--oscuro); 
            font-size: 2.2rem;
            margin: 0;
        }
        .info-box {
            background: #e3f2fd;
            padding: 1.2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border-left: 5px solid #2196f3;
            font-size: 0.95rem;
        }
        .recorridos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }
        .recorrido-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--sombra);
            border: 2px solid #f0f0f0;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .recorrido-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(46, 125, 50, 0.15);
            border-color: var(--verde-selva);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        .recorrido-nombre {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--verde-selva);
            font-family: 'Montserrat', sans-serif;
            flex: 1;
        }
        .recorrido-tipo {
            background: var(--amarillo-sol);
            color: #000;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
            margin-left: 0.5rem;
        }
        .card-info {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            margin-bottom: 1rem;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
            font-size: 0.9rem;
        }
        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #555;
        }
        .info-item i {
            color: var(--verde-selva);
            width: 20px;
            text-align: center;
        }
        .tickets-info {
            background: #f0f4f0;
            padding: 0.8rem;
            border-radius: 8px;
            font-size: 0.85rem;
            border-left: 4px solid var(--verde-selva);
        }
        .tickets-info strong {
            color: var(--verde-oscuro);
        }
        .select-btn {
            display: inline-block;
            background: var(--verde-selva);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 700;
            text-align: center;
            width: 100%;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .select-btn:hover {
            background: var(--verde-oscuro);
            transform: translateY(-2px);
        }
        .back-link {
            color: #666;
            text-decoration: none;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .back-link:hover {
            color: var(--verde-selva);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Registrar Reportes</h1>
        <a href="index.php?r=guias/dashboard" class="back-link">
            <i class="fa-solid fa-house"></i> Panel
        </a>
    </div>

    <div class="info-box">
        <i class="fa-solid fa-info-circle"></i>
        Selecciona un recorrido ya realizado para registrar observaciones. 
        Se muestran solo los recorridos con clientes confirmados.
    </div>

    <div class="recorridos-grid">
        <?php foreach ($recorridosSinReporte as $recorrido): ?>
            <a href="index.php?r=guias/reportes-crear&id_gr=<?= (int)$recorrido['id_guia_recorrido'] ?>" class="recorrido-card">
                <div class="card-header">
                    <div class="recorrido-nombre">
                        <?= htmlspecialchars($recorrido['recorrido_nombre']) ?>
                    </div>
                    <span class="recorrido-tipo">
                        <?= htmlspecialchars($recorrido['tipo']) ?>
                    </span>
                </div>

                <div class="card-info">
                    <div class="info-item">
                        <i class="fa-solid fa-calendar-days"></i>
                        <strong>Fecha:</strong> <?= (new DateTime($recorrido['fecha_asignacion']))->format('d/m/Y') ?>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-clock"></i>
                        <strong>Asignado:</strong> <?= (new DateTime($recorrido['fecha_asignacion']))->format('d \\de M') ?>
                    </div>
                </div>

                <div class="tickets-info">
                    <i class="fa-solid fa-ticket"></i>
                    <strong><?= (int)$recorrido['tickets_confirmados'] ?> cliente(s)</strong> confirmado(s)
                </div>

                <button type="button" class="select-btn">
                    Registrar Reporte
                </button>
            </a>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
