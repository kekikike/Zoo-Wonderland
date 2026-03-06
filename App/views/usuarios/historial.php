<?php
/** @var array $compras */
/** @var array $reservas */
/** @var \App\Models\Usuario $user */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Historial Global - ZooWonderland</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    :root {
        --verde-selva:   #2e7d32;
        --amarillo-sol:  #ffca28;
        --naranja-tigre: #f57c00;
        --azul-cielo:    #0288d1;
        --gris-claro:    #f8faf8;
        --oscuro:        #0d3a1f;
        --blanco:        #ffffff;
        --sombra:        0 10px 30px rgba(0,0,0,0.08);
        --transicion:    all 0.3s ease;
    }

    body {
        font-family: 'Open Sans', sans-serif;
        background-color: var(--gris-claro);
        margin: 0;
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

    /* Tabs */
    .tabs {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 3rem;
    }
    .tab-btn {
        padding: 1rem 2rem;
        background: white;
        border: none;
        border-radius: 50px;
        font-weight: 700;
        cursor: pointer;
        color: #666;
        box-shadow: var(--sombra);
        transition: var(--transicion);
    }
    .tab-btn.active {
        background: var(--verde-selva);
        color: white;
    }

    .historial-section { display: none; flex-direction: column; gap: 1.5rem; }
    .historial-section.active { display: flex; }

    .card-item {
        background: var(--blanco);
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: var(--sombra);
        display: grid;
        grid-template-columns: 60px 1fr auto auto;
        align-items: center;
        gap: 1.5rem;
        transition: var(--transicion);
    }
    .card-item:hover { transform: scale(1.01); box-shadow: 0 15px 40px rgba(0,0,0,0.1); }

    .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .icon-ticket { background: #e3f2fd; color: var(--azul-cielo); }
    .icon-reserva { background: #f1f8e9; color: var(--verde-selva); }

    .info-box h3 { font-size: 1.2rem; margin: 0 0 0.4rem; color: var(--oscuro); }
    .meta { display: flex; gap: 1.5rem; font-size: 0.85rem; color: #666; }
    .meta span { display: flex; align-items: center; gap: 6px; }

    .status-badge {
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .status-pagado { background: #e8f5e9; color: var(--verde-selva); }
    .status-pendiente { background: #fff3e0; color: var(--naranja-tigre); }

    .price-box { text-align: right; min-width: 120px; }
    .price-box .amount { display: block; font-size: 1.3rem; font-weight: 800; color: var(--oscuro); }

    .btn-ver {
        background: #f0f0f0;
        color: #333;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: var(--transicion);
    }
    .btn-ver:hover { background: var(--verde-selva); color: white; }

    .empty-state {
        text-align: center;
        padding: 4rem;
        background: white;
        border-radius: 30px;
        box-shadow: var(--sombra);
    }
    .empty-state i { font-size: 4rem; color: #eee; margin-bottom: 1rem; }

    @media (max-width: 768px) {
        .card-item { grid-template-columns: 1fr auto; }
        .icon-box { display: none; }
        .price-box { text-align: left; grid-column: 1; }
    }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">🍃 ZooWonderland</a>
    <div style="font-size: 0.9rem; font-weight: 600; color: #666;">
        <i class="fa-solid fa-circle-user"></i> <?= htmlspecialchars($user->getNombreParaMostrar()) ?>
    </div>
</header>

<div class="container">
    <div class="header-page">
        <h1>Mi Historial</h1>
        <p>Consulta tus tickets de entrada y reservas grupales en un solo lugar</p>
    </div>

    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('tickets')">Mis Tickets</button>
        <button class="tab-btn" onclick="showTab('reservas')">Mis Reservas Grupales</button>
    </div>

    <!-- Sección Tickets -->
    <div id="tickets" class="historial-section active">
        <?php if (empty($compras)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-ticket"></i>
                <h2>No tienes tickets comprados</h2>
                <a href="index.php?r=compras/crear" style="color: var(--verde-selva); font-weight: 700;">¡Comprar ahora!</a>
            </div>
        <?php else: ?>
            <?php foreach ($compras as $c): ?>
                <div class="card-item">
                    <div class="icon-box icon-ticket"><i class="fa-solid fa-ticket"></i></div>
                    <div class="info-box">
                        <h3>Ticket #<?= $c['id_compra'] ?></h3>
                        <div class="meta">
                            <span><i class="fa-solid fa-calendar"></i> <?= date('d/m/Y', strtotime($c['fecha'])) ?></span>
                            <span><i class="fa-solid fa-clock"></i> <?= $c['hora'] ?></span>
                        </div>
                    </div>
                    <div class="price-box">
                        <span class="amount">Bs. <?= number_format($c['monto'], 2) ?></span>
                        <span class="status-badge <?= $c['estado_pago'] ? 'status-pagado' : 'status-pendiente' ?>">
                            <?= $c['estado_pago'] ? 'Pagado' : 'Pendiente' ?>
                        </span>
                    </div>
                    <a href="index.php?r=compras/pdf&id=<?= $c['id_compra'] ?>" class="btn-ver" title="Ver/Descargar PDF">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Sección Reservas -->
    <div id="reservas" class="historial-section">
        <?php if (empty($reservas)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-calendar-check"></i>
                <h2>No tienes reservas grupales</h2>
                <a href="index.php?r=reservar" style="color: var(--verde-selva); font-weight: 700;">Reservar ahora</a>
            </div>
        <?php else: ?>
            <?php foreach ($reservas as $item): 
                $r = $item['reserva'];
                $e = $item['extras'];
            ?>
                <div class="card-item">
                    <div class="icon-box icon-reserva"><i class="fa-solid fa-users"></i></div>
                    <div class="info-box">
                        <h3><?= htmlspecialchars($r->getInstitucion()) ?></h3>
                        <div class="meta">
                            <span><i class="fa-solid fa-calendar"></i> <?= date('d/m/Y', strtotime($r->getFecha())) ?></span>
                            <span><i class="fa-solid fa-users"></i> <?= $r->getCupos() ?> pers.</span>
                            <span><i class="fa-solid fa-route"></i> <?= htmlspecialchars($r->getRecorrido()->getNombre()) ?></span>
                        </div>
                    </div>
                    <div class="price-box">
                        <span class="amount">Bs. <?= number_format($e['monto_total'], 2) ?></span>
                        <span class="status-badge status-pendiente">Pendiente</span>
                    </div>
                    <a href="index.php?r=reservas/pdf&id=<?= $r->getId() ?>" class="btn-ver" title="Ver/Descargar PDF">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div style="margin-top: 4rem; text-align: center;">
        <a href="index.php" style="color: #666; text-decoration: none; font-weight: 600;">
            <i class="fa-solid fa-arrow-left"></i> Volver al Inicio
        </a>
    </div>
</div>

<script>
function showTab(tabId) {
    document.querySelectorAll('.historial-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    
    document.getElementById(tabId).classList.add('active');
    event.currentTarget.classList.add('active');
}
</script>

</body>
</html>
