<?php
// App/views/compras/historial.php
$isLoggedIn = $isLoggedIn ?? true; 
$esCliente  = $esCliente  ?? true;
$user       = $user       ?? null;
$compras    = $compras    ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Historial - ZooWonderland</title>
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
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Open Sans', sans-serif;
        background-color: var(--gris-claro);
        color: #333;
        line-height: 1.6;
    }

    h1, h2, h3, .logo { font-family: 'Montserrat', sans-serif; }

    /* ────────────── NAV ────────────── */
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
        text-decoration: none;
    }

    .user-welcome { display: flex; align-items: center; gap: 1.5rem; background: #f0f4f0; padding: 0.5rem 1rem; border-radius: 50px; }
    .user-links { display: flex; gap: 1rem; font-size: 0.85rem; align-items: center; }
    .user-links a { color: #666; text-decoration: none; font-weight: 600; }
    .user-links a.logout { color: #d32f2f; }

    /* ────────────── CONTENEDOR ────────────── */
    .historial-container {
        max-width: 1200px;
        margin: 3rem auto;
        padding: 0 2rem;
    }

    .page-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2.5rem;
    }

    .page-header-flex h1 { font-size: 2.2rem; color: var(--oscuro); }

    /* ────────────── BOTONES ────────────── */
    .btn {
        padding: 0.8rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: var(--transicion);
        border: none;
        cursor: pointer;
    }

    .btn-volver { background: var(--blanco); color: var(--verde-selva); border: 2px solid var(--verde-selva); }
    .btn-comprar-mas { background: var(--verde-selva); color: white; }

    /* ────────────── FILTRO ────────────── */
    .filtro-card {
        background: var(--blanco);
        padding: 1.5rem 2rem;
        border-radius: 20px;
        box-shadow: var(--sombra);
        margin-bottom: 3rem;
        display: flex;
        align-items: flex-end;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .campo { display: flex; flex-direction: column; gap: 8px; flex: 1; min-width: 200px; }
    .campo label { font-family: 'Montserrat'; font-weight: 700; font-size: 0.85rem; color: var(--verde-oscuro); text-transform: uppercase; }
    .input-date { padding: 0.8rem; border: 2px solid #eee; border-radius: 12px; outline: none; }
    .btn-filtrar { background: var(--amarillo-sol); color: var(--oscuro); font-weight: 800; padding: 0.9rem 2rem; border-radius: 12px; }

    /* ────────────── LISTADO EN FILAS ────────────── */
    .grid-historial {
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
    }

    .card-compra {
        background: var(--blanco);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--sombra);
        border: 1px solid rgba(0,0,0,0.05);
        display: grid;
        grid-template-columns: 80px 150px 1fr 150px 180px;
        align-items: center;
        transition: var(--transicion);
    }

    .card-compra:hover { border-color: var(--verde-selva); transform: scale(1.01); }

    .card-compra-header {
        background: var(--oscuro);
        color: var(--blanco);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .id-ticket { padding-left: 1.5rem; font-family: 'Montserrat'; font-weight: 800; }
    .detalles-fila { display: flex; flex-direction: column; padding: 0 1rem; }
    .estado-pago { text-align: center; }
    
    .badge { padding: 5px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; }
    .badge-pagado { background: #e8f5e9; color: var(--verde-selva); }
    .badge-pendiente { background: #fff3e0; color: var(--naranja-tigre); }

    .monto-final {
        background: #f0fdf4;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Montserrat';
        font-weight: 800;
        font-size: 1.2rem;
        color: var(--verde-oscuro);
        border-left: 1px solid #e0eee0;
    }

    .empty-state { text-align: center; padding: 5rem; background: white; border-radius: 30px; }

    footer { background: var(--oscuro); color: rgba(255,255,255,0.7); text-align: center; padding: 4rem 1rem; margin-top: 6rem; }

    @media (max-width: 900px) {
        .card-compra { grid-template-columns: 1fr 1fr; padding: 1rem; gap: 1rem; }
        .card-compra-header, .monto-final { background: transparent; color: var(--oscuro); border: none; height: auto; padding: 0; }
    }
    </style>
</head>
<body>

<header>
    <nav>
        <a href="index.php" class="logo">🍃 ZooWonderland</a>
        <div class="auth-area">
            <?php if ($isLoggedIn && $user): ?>
                <div class="user-welcome">
                    <div class="user-links">
                        <a href="index.php?r=perfil"><i class="fa-solid fa-circle-user"></i> Ver Perfil</a>
                        <a href="index.php?r=logout" class="logout"><i class="fa-solid fa-door-open"></i></a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main class="historial-container">
    
    <div class="page-header-flex">
        <a href="index.php" class="btn btn-volver">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
        <h1>Mis Compras</h1>
        <a href="index.php#visitanos" class="btn btn-comprar-mas">
            <i class="fa-solid fa-cart-plus"></i> Realizar nueva compra
        </a>
    </div>

    <form method="GET" class="filtro-card">
        <input type="hidden" name="r" value="compras/historial">
        <div class="campo">
            <label><i class="fa-solid fa-calendar-day"></i> Fecha Inicio</label>
            <input type="date" name="fecha_inicio" class="input-date">
        </div>
        <div class="campo">
            <label><i class="fa-solid fa-calendar-check"></i> Fecha Fin</label>
            <input type="date" name="fecha_fin" class="input-date">
        </div>
        <button type="submit" class="btn btn-filtrar">Filtrar</button>
    </form>

    <div class="grid-historial">
        <?php if (empty($compras)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-paw" style="font-size: 3rem; color: var(--amarillo-sol);"></i>
                <h2>No hay registros</h2>
                <p>Parece que aún no tienes compras en este período.</p>
            </div>
        <?php else: ?>
            <?php foreach ($compras as $c): ?>
                <div class="card-compra">
                    <div class="card-compra-header">
                        <i class="fa-solid fa-receipt"></i>
                        <span style="font-size: 0.7rem;">TICKET</span>
                    </div>

                    <div class="id-ticket">
                        #<?= htmlspecialchars($c['id_compra']) ?>
                    </div>

                    <div class="detalles-fila">
                        <span><i class="fa-solid fa-calendar"></i> <?= htmlspecialchars($c['fecha']) ?></span>
                        <span style="color: #888; font-size: 0.8rem;"><i class="fa-solid fa-clock"></i> <?= htmlspecialchars($c['hora']) ?></span>
                    </div>

                    <div class="estado-pago">
                        <?php if ($c['estado_pago']): ?>
                            <span class="badge badge-pagado">Pagado ✓</span>
                        <?php else: ?>
                            <span class="badge badge-pendiente">Pendiente</span>
                        <?php endif; ?>
                    </div>

                    <div class="monto-final">
                        Bs. <?= number_format($c['monto'], 2) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p><strong>ZooWonderland</strong></p>
    <p>© <?= date('Y') ?> Compromiso con la Naturaleza.</p>
</footer>

</body>
</html>