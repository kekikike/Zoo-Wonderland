<?php
/** @var array $todasLasReservas */
/** @var \App\Models\Usuario $usuario */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Reservas - ZooWonderland</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    :root {
        --verde-selva:   #2e7d32;
        --amarillo-sol:  #ffca28;
        --gris-claro:    #f8faf8;
        --oscuro:        #0d3a1f;
        --blanco:        #ffffff;
        --sombra:        0 10px 30px rgba(0,0,0,0.05);
        --transicion:    all 0.3s ease;
    }

    body {
        font-family: 'Open Sans', sans-serif;
        background-color: var(--gris-claro);
        margin: 0;
        padding: 0;
        color: #333;
    }

    h1, h2, h3, .logo { font-family: 'Montserrat', sans-serif; }

    header {
        background: var(--blanco);
        padding: 1rem 5%;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .logo { font-size: 1.5rem; font-weight: 800; color: var(--verde-selva); text-decoration: none; }

    .container { max-width: 1100px; margin: 3rem auto; padding: 0 2rem; }

    .header-page { margin-bottom: 3rem; text-align: center; }
    .header-page h1 { font-size: 2.2rem; color: var(--oscuro); margin-bottom: 0.5rem; }
    .header-page p { color: #666; font-size: 1.1rem; }

    .reservas-list { display: flex; flex-direction: column; gap: 1.5rem; }

    .reserva-card {
        background: var(--blanco);
        border-radius: 20px;
        padding: 1.8rem;
        box-shadow: var(--sombra);
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: center;
        gap: 2rem;
        transition: var(--transicion);
        border-left: 6px solid var(--verde-selva);
    }
    .reserva-card:hover { transform: scale(1.02); box-shadow: 0 15px 40px rgba(0,0,0,0.1); }

    .reserva-id {
        background: #f0f4f0;
        color: var(--verde-selva);
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.2rem;
        font-family: 'Montserrat', sans-serif;
    }

    .reserva-info h3 { font-size: 1.3rem; margin-bottom: 0.5rem; color: var(--oscuro); }
    .reserva-meta { display: flex; gap: 2rem; flex-wrap: wrap; font-size: 0.9rem; color: #666; }
    .reserva-meta span { display: flex; align-items: center; gap: 8px; }
    .reserva-meta i { color: var(--verde-selva); }

    .reserva-price { text-align: right; }
    .reserva-price .amount { display: block; font-size: 1.4rem; font-weight: 800; color: var(--verde-oscuro); font-family: 'Montserrat', sans-serif; }
    .reserva-price .status { 
        font-size: 0.75rem; 
        font-weight: 700; 
        text-transform: uppercase; 
        padding: 4px 10px; 
        background: var(--amarillo-sol); 
        border-radius: 50px; 
        color: #000;
        display: inline-block;
        margin-top: 5px;
    }

    .btn-action {
        background: var(--verde-selva);
        color: white;
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: var(--transicion);
    }
    .btn-action:hover { background: var(--verde-oscuro); transform: rotate(10deg); }

    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        background: white;
        border-radius: 30px;
        box-shadow: var(--sombra);
    }
    .empty-state i { font-size: 5rem; color: #eee; margin-bottom: 1.5rem; }
    .empty-state h2 { color: #bbb; }

    @media (max-width: 768px) {
        .reserva-card { grid-template-columns: 1fr auto; }
        .reserva-id { display: none; }
        .reserva-price { text-align: left; grid-column: 1 / span 2; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 1rem; }
    }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">🍃 ZooWonderland</a>
    <div style="font-size: 0.9rem; font-weight: 600; color: #666;">
        <i class="fa-solid fa-user-circle"></i> <?= htmlspecialchars($usuario->getNombreParaMostrar()) ?>
    </div>
</header>

<div class="container">
    <div class="header-page">
        <h1>Mi Historial de Reservas</h1>
        <p>Gestiona tus visitas grupales y descarga comprobantes</p>
    </div>

    <?php if (empty($todasLasReservas)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-calendar-xmark"></i>
            <h2>Aún no tienes reservas registradas</h2>
            <a href="index.php?r=reservar" style="color: var(--verde-selva); text-decoration: none; font-weight: 700; margin-top: 2rem; display: inline-block;">
                ¡Haz tu primera reserva ahora!
            </a>
        </div>
    <?php else: ?>
        <div class="reservas-list">
            <?php foreach ($todasLasReservas as $item): 
                $r = $item['reserva'];
                $e = $item['extras'];
            ?>
                <div class="reserva-card">
                    <div class="reserva-id"><?= $r->getId() ?></div>
                    <div class="reserva-info">
                        <h3><?= htmlspecialchars($r->getInstitucion()) ?></h3>
                        <div class="reserva-meta">
                            <span><i class="fa-solid fa-calendar"></i> <?= date('d/m/Y', strtotime($r->getFecha())) ?></span>
                            <span><i class="fa-solid fa-clock"></i> <?= $r->getHora() ?></span>
                            <span><i class="fa-solid fa-users"></i> <?= $r->getCupos() ?> personas</span>
                            <span><i class="fa-solid fa-route"></i> <?= htmlspecialchars($r->getRecorrido()->getNombre()) ?></span>
                        </div>
                    </div>
                    <div class="reserva-price">
                        <span class="amount">Bs. <?= number_format($e['monto_total'], 2) ?></span>
                        <span class="status">Pendiente</span>
                        <div style="margin-top: 10px; display: flex; gap: 10px; justify-content: flex-end;">
                            <a href="index.php?r=reservas/pdf&id=<?= $r->getId() ?>" class="btn-action" title="Descargar PDF">
                                <i class="fa-solid fa-file-pdf"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div style="margin-top: 4rem; text-align: center;">
        <a href="index.php" style="color: #666; text-decoration: none; font-weight: 600;">
            <i class="fa-solid fa-arrow-left"></i> Volver al Inicio
        </a>
    </div>
</div>

</body>
</html>
