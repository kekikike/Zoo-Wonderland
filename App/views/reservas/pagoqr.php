<?php
/** @var array $datos */
/** @var \App\Models\Reserva $reserva */
/** @var \App\Models\Usuario $usuario */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Reserva - ZooWonderland</title>
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
        --sombra:        0 15px 40px rgba(0,0,0,0.1);
        --success:       #2e7d32;
    }

    body {
        font-family: 'Open Sans', sans-serif;
        background-color: var(--gris-claro);
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 2rem;
    }

    .card {
        background: var(--blanco);
        max-width: 650px;
        width: 100%;
        border-radius: 30px;
        box-shadow: var(--sombra);
        overflow: hidden;
        text-align: center;
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    .header-status {
        background: var(--verde-selva);
        color: white;
        padding: 3rem 2rem;
    }
    .header-status i { font-size: 4rem; margin-bottom: 1rem; color: var(--amarillo-sol); }
    .header-status h1 { font-family: 'Montserrat', sans-serif; font-size: 2rem; margin-bottom: 0.5rem; }

    .content { padding: 3rem; }

    .code-box {
        background: #f0fdf4;
        border: 2px dashed var(--verde-selva);
        border-radius: 15px;
        padding: 1.5rem;
        margin: 2rem 0;
    }
    .code-box small { display: block; text-transform: uppercase; letter-spacing: 2px; color: #666; font-size: 0.8rem; margin-bottom: 0.5rem; font-weight: 700; }
    .code-box strong { font-family: 'Montserrat', sans-serif; font-size: 2.2rem; color: var(--verde-oscuro); letter-spacing: 5px; }

    .details { text-align: left; background: #fafafa; padding: 1.5rem; border-radius: 15px; margin-bottom: 2rem; font-size: 0.95rem; }
    .details p { margin-bottom: 0.8rem; display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
    .details strong { color: var(--oscuro); }

    .qr-section { margin: 2rem 0; }
    .qr-section p { font-weight: 600; color: #555; margin-bottom: 1.5rem; }
    .qr-section img { 
        max-width: 250px; 
        border-radius: 20px; 
        padding: 10px; 
        background: white; 
        box-shadow: 0 5px 20px rgba(0,0,0,0.1); 
        border: 2px solid #eee;
    }

    .actions { display: flex; flex-direction: column; gap: 1rem; }
    .btn {
        padding: 1.1rem;
        border-radius: 15px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s;
        font-family: 'Montserrat', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .btn-pdf { background: var(--verde-selva); color: white; box-shadow: 0 5px 15px rgba(46, 125, 50, 0.3); }
    .btn-pdf:hover { background: var(--verde-oscuro); transform: translateY(-3px); }
    .btn-home { background: transparent; color: var(--oscuro); border: 2px solid #ddd; }
    .btn-home:hover { background: #eee; }

    @media (max-width: 500px) { .content { padding: 1.5rem; } .header-status { padding: 2rem 1rem; } }
    </style>
</head>
<body>

<div class="card">
    <div class="header-status">
        <i class="fa-solid fa-circle-check"></i>
        <h1>¡Reserva Registrada!</h1>
        <p>Tu solicitud para <strong><?= htmlspecialchars($reserva->getInstitucion()) ?></strong> ha sido enviada al sistema.</p>
    </div>

    <div class="content">
        <div class="code-box">
            <small>Código de Gestión</small>
            <strong><?= $datos['codigo'] ?></strong>
        </div>

        <div class="details">
            <p><span><i class="fa-solid fa-calendar"></i> Fecha:</span> <strong><?= $reserva->getFecha() ?></strong></p>
            <p><span><i class="fa-solid fa-clock"></i> Hora:</span> <strong><?= $reserva->getHora() ?></strong></p>
            <p><span><i class="fa-solid fa-users"></i> Visitantes:</span> <strong><?= $reserva->getCupos() ?> personas</strong></p>
            <p><span><i class="fa-solid fa-sack-dollar"></i> Monto estimado:</span> <strong>Bs. <?= number_format($datos['monto_total'], 2) ?></strong></p>
        </div>

        <div class="qr-section">
            <p>Escanea para coordinar el pago reserva:</p>
            <img src="img/qr.jpeg" alt="QR Pago">
        </div>

        <div class="actions">
            <a href="index.php?r=reservas/pdf&id=<?= $reserva->getId() ?>" class="btn btn-pdf">
                <i class="fa-solid fa-file-pdf"></i> Descargar Comprobante PDF
            </a>
            <a href="index.php" class="btn btn-home">
                <i class="fa-solid fa-house"></i> Volver al Inicio
            </a>
        </div>
    </div>
</div>

</body>
</html>
