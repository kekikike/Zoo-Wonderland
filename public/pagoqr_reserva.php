<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

session_start();
require_once __DIR__ . '/../config/constants.php';
require_once SRC_PATH . '/Services/autoload_session.php';
require_once SRC_PATH . '/Services/Auth.php';
require_once SRC_PATH . '/Services/ReservaService.php';
require_once SRC_PATH . '/Models/Reserva.php';
require_once SRC_PATH . '/Models/Recorrido.php';
require_once SRC_PATH . '/Models/Cliente.php';
require_once SRC_PATH . '/Repositories/ReservaRepository.php';

use App\Services\Auth;
use App\Services\ReservaService;
use App\Models\Cliente;
use App\Repositories\ReservaRepository;

// ── Autenticación ────────────────────────────────────────────────────────
if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$usuario = Auth::user();
if (!$usuario instanceof Cliente) {
    header('Location: index.php');
    exit;
}

// ── Recuperar datos de la reserva desde sesión ───────────────────────────
$reservaId   = (int)($_SESSION['ultima_reserva_id']    ?? 0);
$datosExtra  = $_SESSION['ultima_reserva_datos']       ?? null;

$reservaRepo    = new ReservaRepository();
$reserva        = $reservaRepo->findById($reservaId);

if (!$reserva || !$datosExtra) {
    header('Location: reservar.php');
    exit;
}

// ── Generar PDF ──────────────────────────────────────────────────────────
$reservaService = new ReservaService();
$pdfContent     = $reservaService->generarComprobanteReserva($reserva, $datosExtra);

// ── Si el usuario quiere descargar directamente ──────────────────────────
if (isset($_GET['descargar'])) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="reserva_' . $reserva->getId() . '_' . $datosExtra['codigo'] . '.pdf"');
    echo $pdfContent;
    exit;
}

$tiposLabel = [
    'colegio'     => 'Colegio / Unidad Educativa',
    'universidad' => 'Universidad / Instituto',
    'empresa'     => 'Empresa',
    'ong'         => 'ONG / Fundación',
    'gobierno'    => 'Entidad Gubernamental',
    'otro'        => 'Otro',
];
$tipoLabel = $tiposLabel[$datosExtra['tipo_institucion']] ?? $datosExtra['tipo_institucion'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Reserva – <?= APP_NAME ?></title>
    <meta name="description" content="Comprobante y pago QR para tu reserva de tour grupal en Zoo Wonderland.">
    <style>
        :root {
            --color-primary:   #a3712a;
            --color-secondary: #977c66;
            --color-accent:    #bfb641;
            --color-light:     #ffe2a0;
            --color-dark:      #68672e;
            --color-info:      #7eaeb0;
            --color-bg:        #fffaf0;
            --color-success:   #2d6a4f;
            --radius:          10px;
            --shadow-md:       0 4px 20px rgba(0,0,0,.12);
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--color-bg);
            color: #333;
            line-height: 1.6;
        }

        /* ── Header ── */
        header {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: #fff;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,.2);
        }
        nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { font-size: 1.8rem; font-weight: 800; color: var(--color-light); }
        .nav-links { display: flex; gap: 2rem; list-style: none; }
        .nav-links a { color: #fff; text-decoration: none; font-weight: 500; transition: color .25s; }
        .nav-links a:hover { color: var(--color-accent); }
        .auth-links { display: flex; align-items: center; gap: .8rem; }
        .auth-links span { color: var(--color-light); font-size: .9rem; }
        .btn-logout {
            padding: 7px 14px; border-radius: 6px; text-decoration: none;
            font-size: .9rem; color: var(--color-light);
            border: 1px solid rgba(255,255,255,.4); transition: opacity .25s;
        }
        .btn-logout:hover { opacity: .8; }

        /* ── Banner de éxito ── */
        .success-banner {
            background: linear-gradient(135deg, var(--color-success), #40916c);
            color: #fff;
            text-align: center;
            padding: 2.5rem 1.5rem 2rem;
        }
        .success-banner .icon { font-size: 3.5rem; display: block; margin-bottom: .5rem; }
        .success-banner h1 { font-size: 2rem; margin-bottom: .4rem; }
        .success-banner p  { opacity: .9; font-size: 1rem; }

        /* ── Layout ── */
        main {
            max-width: 1100px;
            margin: 2.5rem auto;
            padding: 0 1.5rem;
        }

        .pago-section {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 2rem;
            align-items: start;
        }

        /* ── Panel QR ── */
        .qr-panel {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }
        .qr-panel-header {
            background: linear-gradient(135deg, var(--color-primary), var(--color-dark));
            color: #fff;
            padding: 1.2rem;
            text-align: center;
        }
        .qr-panel-header h2 { font-size: 1.1rem; margin-bottom: .2rem; }
        .qr-panel-header p  { font-size: .82rem; opacity: .85; }
        .qr-panel-body { padding: 1.5rem; text-align: center; }

        .qr-image {
            width: 200px;
            height: 200px;
            border: 3px solid var(--color-accent);
            border-radius: var(--radius);
            margin: 0 auto 1rem;
            display: block;
            object-fit: cover;
        }

        .monto-badge {
            display: inline-block;
            background: var(--color-light);
            color: var(--color-dark);
            font-size: 1.4rem;
            font-weight: 800;
            padding: .5rem 1.2rem;
            border-radius: 30px;
            margin-bottom: 1rem;
        }
        .monto-label { font-size: .82rem; color: #888; margin-bottom: .5rem; }

        .codigo-mini {
            background: #f5f5f5;
            border-radius: 8px;
            padding: .6rem .9rem;
            font-family: monospace;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: .1em;
            color: var(--color-primary);
            margin-bottom: 1.2rem;
            word-break: break-all;
        }

        .qr-divider { border: none; border-top: 1px dashed #ddd; margin: 1rem 0; }

        .resumen-mini { text-align: left; }
        .resumen-mini .row {
            display: flex;
            justify-content: space-between;
            font-size: .85rem;
            padding: .3rem 0;
        }
        .resumen-mini .row span:first-child { color: #777; }
        .resumen-mini .row span:last-child  { font-weight: 600; }

        /* ── Panel PDF ── */
        .pdf-panel {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }
        .pdf-panel-header {
            background: var(--color-bg);
            border-bottom: 2px solid var(--color-light);
            padding: 1.1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pdf-panel-header h2 { color: var(--color-primary); font-size: 1.1rem; }

        .pdf-actions { display: flex; gap: .7rem; flex-wrap: wrap; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .6rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: .88rem;
            transition: opacity .2s, transform .2s;
            cursor: pointer;
            border: none;
            font-family: inherit;
        }
        .btn:hover { opacity: .88; transform: translateY(-1px); }

        .btn-download {
            background: linear-gradient(135deg, var(--color-primary), var(--color-dark));
            color: #fff;
        }
        .btn-new-reserva {
            background: var(--color-accent);
            color: var(--color-dark);
        }
        .btn-home {
            background: #fff;
            color: var(--color-primary);
            border: 2px solid var(--color-primary);
        }

        .pdf-embed-wrapper { padding: 0; }
        .pdf-embed-wrapper embed {
            display: block;
            width: 100%;
            height: 640px;
            border: none;
        }

        .pdf-fallback {
            padding: 2rem;
            text-align: center;
            color: #666;
        }
        .pdf-fallback p { margin-bottom: 1rem; }

        /* ── Notice ── */
        .notice-bar {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: var(--radius);
            padding: 1rem 1.4rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-start;
            gap: .7rem;
            font-size: .9rem;
            color: #6b5900;
        }
        .notice-bar .icon { font-size: 1.3rem; flex-shrink: 0; }

        /* ── Footer ── */
        footer {
            background: var(--color-secondary);
            color: #fff;
            text-align: center;
            padding: 2rem;
            margin-top: 4rem;
            font-size: .9rem;
        }

        @media (max-width: 900px) {
            .pago-section { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- ===== HEADER ===== -->
<header>
    <nav>
        <div class="logo"><?= APP_NAME ?></div>
        <ul class="nav-links">
            <li><a href="index.php#inicio">Inicio</a></li>
            <li><a href="index.php#nosotros">Nosotros</a></li>
            <li><a href="comprar.php">Comprar Entradas</a></li>
            <li><a href="reservar.php">Tours Grupales</a></li>
            <li><a href="historial.php">Historial</a></li>
        </ul>
        <div class="auth-links">
            <?php if (Auth::check()): ?>
                <span>👤 <?= htmlspecialchars($usuario->getNombreCompleto() ?? 'Usuario') ?></span>
                <a href="logout.php" class="btn-logout">Salir</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<!-- ===== BANNER ÉXITO ===== -->
<div class="success-banner">
    <span class="icon">🎉</span>
    <h1>¡Reserva Confirmada!</h1>
    <p>Tu tour grupal ha sido registrado. Realiza el pago escaneando el QR y descarga tu comprobante.</p>
</div>

<!-- ===== MAIN ===== -->
<main>

    <!-- Aviso informativo -->
    <div class="notice-bar">
        <span class="icon">ℹ️</span>
        <div>
            <strong>Próximos pasos:</strong> Escanea el QR para coordinar el pago con el zoológico.
            Descarga y guarda tu comprobante en PDF. El equipo de administración se pondrá en contacto
            con <strong><?= htmlspecialchars($datosExtra['contacto_email']) ?></strong> para confirmar
            los detalles finales del tour.
        </div>
    </div>

    <div class="pago-section">

        <!-- ======= PANEL QR ======= -->
        <div class="qr-panel">
            <div class="qr-panel-header">
                <h2>📱 Código QR de Pago</h2>
                <p>Escanea para coordinar el pago</p>
            </div>
            <div class="qr-panel-body">

                <img
                    src="./img/qr.jpeg"
                    alt="QR de Pago – Zoo Wonderland"
                    class="qr-image"
                    id="qr-img"
                >

                <p class="monto-label">Total estimado</p>
                <div class="monto-badge">
                    Bs. <?= number_format((float)$datosExtra['monto_total'], 2) ?>
                </div>

                <p style="font-size:.8rem; color:#888; margin-bottom:.7rem;">
                    <?= $reserva->getCupos() ?> personas ×
                    Bs. <?= number_format($reserva->getRecorrido()->getPrecio(), 2) ?>/persona
                </p>

                <div class="codigo-mini">
                    <?= htmlspecialchars($datosExtra['codigo']) ?>
                </div>

                <hr class="qr-divider">

                <div class="resumen-mini">
                    <div class="row">
                        <span>Institución:</span>
                        <span><?= htmlspecialchars($reserva->getInstitucion()) ?></span>
                    </div>
                    <div class="row">
                        <span>Tipo:</span>
                        <span><?= htmlspecialchars($tipoLabel) ?></span>
                    </div>
                    <div class="row">
                        <span>Recorrido:</span>
                        <span><?= htmlspecialchars($reserva->getRecorrido()->getNombre()) ?></span>
                    </div>
                    <div class="row">
                        <span>Fecha:</span>
                        <span><?= date('d/m/Y', strtotime($reserva->getFecha())) ?></span>
                    </div>
                    <div class="row">
                        <span>Hora:</span>
                        <span><?= htmlspecialchars($reserva->getHora()) ?></span>
                    </div>
                    <div class="row">
                        <span>Contacto:</span>
                        <span><?= htmlspecialchars($datosExtra['contacto_nombre']) ?></span>
                    </div>
                    <div class="row">
                        <span>Teléfono:</span>
                        <span><?= htmlspecialchars($datosExtra['contacto_telefono']) ?></span>
                    </div>
                </div>

                <hr class="qr-divider">

                <a
                    href="pagoqr_reserva.php?descargar=1"
                    class="btn btn-download"
                    style="width:100%; justify-content:center; margin-top:.5rem;"
                >
                    📄 Descargar Comprobante PDF
                </a>

            </div>
        </div>

        <!-- ======= PANEL PDF ======= -->
        <div class="pdf-panel">
            <div class="pdf-panel-header">
                <h2>📋 Comprobante de Reserva Grupal</h2>
                <div class="pdf-actions">
                    <a href="pagoqr_reserva.php?descargar=1" class="btn btn-download">
                        ⬇️ Descargar PDF
                    </a>
                    <a href="reservar.php" class="btn btn-new-reserva">
                        📋 Nueva Reserva
                    </a>
                    <a href="index.php" class="btn btn-home">
                        🏠 Inicio
                    </a>
                </div>
            </div>

            <div class="pdf-embed-wrapper">
                <embed
                    src="data:application/pdf;base64,<?= base64_encode($pdfContent) ?>"
                    type="application/pdf"
                    width="100%"
                    height="640px"
                    id="pdf-embed"
                >
                </embed>

                <!-- Fallback si el navegador no soporta embed de PDF -->
                <noscript>
                    <div class="pdf-fallback">
                        <p>Tu navegador no puede mostrar el PDF inline.</p>
                        <a href="pagoqr_reserva.php?descargar=1" class="btn btn-download">
                            ⬇️ Descargar Comprobante PDF
                        </a>
                    </div>
                </noscript>
            </div>
        </div>

    </div><!-- /.pago-section -->

</main>

<footer>
    <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> — Todos los derechos reservados</p>
</footer>

<script>
    // Fallback si el embed de PDF no carga (algunos navegadores móviles)
    const embed = document.getElementById('pdf-embed');
    if (embed) {
        embed.addEventListener('error', function() {
            this.insertAdjacentHTML('afterend',
                '<div class="pdf-fallback">' +
                '<p>Tu navegador no puede mostrar el PDF directamente.</p>' +
                '<a href="pagoqr_reserva.php?descargar=1" class="btn btn-download">⬇️ Descargar Comprobante PDF</a>' +
                '</div>'
            );
            this.remove();
        });
    }
</script>

</body>
</html>
