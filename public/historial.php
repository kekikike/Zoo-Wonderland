<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

session_start();
require_once __DIR__ . '/../config/constants.php';
require_once SRC_PATH . '/Services/autoload_session.php';
require_once SRC_PATH . '/Services/Auth.php';
require_once SRC_PATH . '/Repositories/CompraRepository.php';
require_once SRC_PATH . '/Repositories/ReservaRepository.php';
require_once SRC_PATH . '/Models/Cliente.php';
require_once SRC_PATH . '/Models/Reserva.php';
require_once SRC_PATH . '/Models/Recorrido.php';

use App\Services\Auth;
use App\Repositories\CompraRepository;
use App\Repositories\ReservaRepository;
use App\Models\Cliente;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$usuario = Auth::user();
if (!$usuario instanceof Cliente) {
    header('Location: index.php');
    exit;
}

// ── Compras ──────────────────────────────────────────────────────────────
$compraRepo = new CompraRepository();
$compras    = $compraRepo->findByCliente($usuario->getId());

// ── Reservas grupales ─────────────────────────────────────────────────────
$reservaRepo    = new ReservaRepository();
$reservasExtras = $reservaRepo->findAllWithExtras();  // [{reserva, extras}, ...]

// ── Filtro por fecha (aplica a compras) ───────────────────────────────────
$fechaInicio = $_GET['fechaInicio'] ?? '';
$fechaFin    = $_GET['fechaFin']    ?? '';
$errorFiltro = '';

try {
    if (!empty($fechaInicio) && !empty($fechaFin)) {
        $inicio = new DateTime($fechaInicio);
        $fin    = new DateTime($fechaFin);
        $hoy    = new DateTime('today');

        if ($inicio > $hoy) throw new Exception('La fecha de inicio no puede ser futura.');
        if ($fin    > $hoy) throw new Exception('La fecha de fin no puede ser futura.');
        if ($inicio > $fin) throw new Exception('La fecha inicial no puede ser mayor a la final.');

        // Filtrar compras
        $compras = array_filter($compras, function ($c) use ($inicio, $fin) {
            $f = new DateTime($c->getFecha());
            return $f >= $inicio && $f <= $fin;
        });

        // Filtrar reservas (por fecha del tour)
        $reservasExtras = array_filter($reservasExtras, function ($item) use ($inicio, $fin) {
            $f = new DateTime($item['reserva']->getFecha());
            return $f >= $inicio && $f <= $fin;
        });
    }
} catch (Exception $e) {
    $errorFiltro = $e->getMessage();
}

$tiposLabel = [
    'colegio'     => 'Colegio / Unidad Educativa',
    'universidad' => 'Universidad / Instituto',
    'empresa'     => 'Empresa',
    'ong'         => 'ONG / Fundación',
    'gobierno'    => 'Entidad Gubernamental',
    'otro'        => 'Otro',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial – <?= APP_NAME ?></title>
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
            --shadow-sm:       0 2px 8px rgba(0,0,0,.08);
            --shadow-md:       0 4px 18px rgba(0,0,0,.12);
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--color-bg); color: #333; line-height: 1.6; }

        /* ── Nav ── */
        header { background: linear-gradient(135deg, var(--color-primary), var(--color-secondary)); color: #fff; padding: 1rem 0; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 10px rgba(0,0,0,.2); }
        nav { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.8rem; font-weight: 800; color: var(--color-light); }
        .nav-links { display: flex; gap: 2rem; list-style: none; }
        .nav-links a { color: #fff; text-decoration: none; font-weight: 500; transition: color .25s; }
        .nav-links a:hover, .nav-links a.active { color: var(--color-accent); }
        .auth-links { display: flex; align-items: center; gap: .8rem; }
        .auth-links span { color: var(--color-light); font-size: .9rem; }
        .btn-auth { padding: 7px 14px; border-radius: 6px; text-decoration: none; font-size: .9rem; font-weight: 700; transition: opacity .2s; }
        .btn-auth:hover { opacity: .85; }
        .btn-logout { color: var(--color-light); border: 1px solid rgba(255,255,255,.4); }
        .btn-login  { background: var(--color-accent); color: var(--color-dark); }

        /* ── Main ── */
        main { max-width: 1100px; margin: 2.5rem auto; padding: 0 1.5rem; }

        /* ── Título de sección ── */
        .section-heading {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--color-primary);
            margin-bottom: 1.2rem;
            padding-bottom: .6rem;
            border-bottom: 3px solid var(--color-accent);
        }
        .section-heading .badge {
            font-size: .72rem;
            font-weight: 700;
            padding: .2rem .6rem;
            border-radius: 20px;
            vertical-align: middle;
        }
        .badge-compra  { background: var(--color-info); color: #fff; }
        .badge-reserva { background: var(--color-success); color: #fff; }

        /* ── Filtro fechas ── */
        .filtro-card {
            background: #fff;
            border-radius: var(--radius);
            padding: 1rem 1.4rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .filtro-card label { font-weight: 600; font-size: .9rem; color: var(--color-dark); }
        .filtro-card input[type="date"] {
            padding: 6px 10px;
            border: 1.5px solid #ddd;
            border-radius: 7px;
            font-size: .9rem;
            font-family: inherit;
            transition: border-color .2s;
        }
        .filtro-card input[type="date"]:focus { outline: none; border-color: var(--color-primary); }
        .filtro-card button {
            padding: 7px 16px;
            background: var(--color-light);
            border: none;
            border-radius: 7px;
            color: var(--color-dark);
            font-weight: 700;
            font-size: .9rem;
            cursor: pointer;
            transition: background .2s;
        }
        .filtro-card button:hover { background: var(--color-accent); }
        .filtro-card .limpiar { color: var(--color-primary); text-decoration: none; font-weight: 600; font-size: .88rem; }
        .filtro-card .limpiar:hover { text-decoration: underline; }
        .error-filtro { color: #c0392b; font-size: .9rem; font-weight: 600; margin-bottom: 1rem; }

        /* ── Tarjeta de compra ── */
        .compra-card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.2rem;
            overflow: hidden;
            border-left: 5px solid var(--color-info);
            transition: box-shadow .2s;
        }
        .compra-card:hover { box-shadow: var(--shadow-md); }

        .compra-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .9rem 1.3rem;
            background: #f8fbff;
            border-bottom: 1px solid #e8f0f8;
            cursor: pointer;
            user-select: none;
        }
        .compra-header h3 { font-size: 1rem; color: var(--color-primary); }
        .compra-header .meta { font-size: .85rem; color: #666; margin-top: .15rem; }
        .compra-header .monto { font-size: 1.1rem; font-weight: 800; color: var(--color-dark); text-align: right; }
        .compra-header .toggle-icon { font-size: .9rem; color: #aaa; margin-left: .5rem; transition: transform .25s; }
        .compra-header.open .toggle-icon { transform: rotate(180deg); }

        .compra-body { padding: 1rem 1.3rem; display: none; }
        .compra-body.visible { display: block; }

        .ticket-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #f5f8fc;
            border-radius: 8px;
            padding: .7rem 1rem;
            margin-bottom: .6rem;
            font-size: .88rem;
        }
        .ticket-item img { width: 60px; height: 60px; border-radius: 6px; border: 1px solid #ddd; flex-shrink: 0; }
        .ticket-info span { display: block; color: #555; }
        .ticket-info strong { color: #222; }

        /* ── Tarjeta de reserva ── */
        .reserva-card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.2rem;
            overflow: hidden;
            border-left: 5px solid var(--color-success);
            transition: box-shadow .2s;
        }
        .reserva-card:hover { box-shadow: var(--shadow-md); }

        .reserva-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .9rem 1.3rem;
            background: #f4fbf7;
            border-bottom: 1px solid #d6eedd;
            cursor: pointer;
            user-select: none;
        }
        .reserva-header h3 { font-size: 1rem; color: var(--color-success); }
        .reserva-header .meta { font-size: .85rem; color: #666; margin-top: .15rem; }
        .reserva-header .monto { font-size: 1.1rem; font-weight: 800; color: var(--color-success); text-align: right; }
        .reserva-header .tipo-badge {
            display: inline-block;
            background: #e8f5e9;
            color: var(--color-success);
            font-size: .72rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: .4rem;
        }

        .reserva-body { padding: 1rem 1.3rem; display: none; }
        .reserva-body.visible { display: block; }

        .reserva-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .reserva-section h4 {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--color-secondary);
            margin-bottom: .5rem;
            padding-bottom: .25rem;
            border-bottom: 2px solid var(--color-light);
        }
        .info-row { display: flex; justify-content: space-between; font-size: .87rem; padding: .22rem 0; }
        .info-row span:first-child { color: #777; }
        .info-row span:last-child  { font-weight: 600; color: #222; }

        .codigo-chip {
            display: inline-block;
            background: var(--color-light);
            color: var(--color-dark);
            font-family: monospace;
            font-size: .9rem;
            font-weight: 700;
            letter-spacing: .1em;
            padding: .3rem .9rem;
            border-radius: 20px;
            margin-bottom: .7rem;
        }

        .reserva-actions { display: flex; gap: .7rem; flex-wrap: wrap; margin-top: .7rem; }
        .btn-sm {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .45rem 1rem;
            border-radius: 7px;
            font-size: .85rem;
            font-weight: 700;
            text-decoration: none;
            transition: opacity .2s, transform .2s;
        }
        .btn-sm:hover { opacity: .85; transform: translateY(-1px); }
        .btn-pdf { background: linear-gradient(135deg, var(--color-primary), var(--color-dark)); color: #fff; }

        /* ── Estado vacío ── */
        .empty-state {
            text-align: center;
            padding: 2.5rem;
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            color: #888;
            margin-bottom: 1.2rem;
        }
        .empty-state .icon { font-size: 2.5rem; margin-bottom: .5rem; }
        .empty-state p { margin-bottom: 1rem; }
        .empty-state a {
            display: inline-block;
            padding: .6rem 1.4rem;
            background: var(--color-primary);
            color: #fff;
            border-radius: 7px;
            text-decoration: none;
            font-weight: 700;
            font-size: .9rem;
        }

        /* ── Separador de sección ── */
        .section-gap { margin-bottom: 3rem; }

        /* ── Footer ── */
        footer { background: var(--color-secondary); color: #fff; text-align: center; padding: 2rem; margin-top: 4rem; font-size: .9rem; }

        @media (max-width: 768px) {
            .nav-links { gap: 1rem; font-size: .9rem; }
            .reserva-grid { grid-template-columns: 1fr; }
            .compra-header, .reserva-header { flex-wrap: wrap; gap: .4rem; }
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
            <li><a href="index.php#visitanos">Visítanos</a></li>
            <li><a href="comprar.php">Comprar Entradas</a></li>
            <li><a href="reservar.php">Tours Grupales</a></li>
            <li><a href="historial.php" class="active">Historial</a></li>
        </ul>
        <div class="auth-links">
            <?php if (Auth::check()): ?>
                <span>👤 <?= htmlspecialchars($usuario->getNombreCompleto() ?? 'Usuario') ?></span>
                <a href="logout.php" class="btn-auth btn-logout">Salir</a>
            <?php else: ?>
                <a href="login.php" class="btn-auth btn-login">Iniciar sesión</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main>

    <h1 style="color:var(--color-primary); margin-bottom:1.5rem; font-size:1.9rem;">
        📋 Mi Historial
    </h1>

    <!-- ── Filtro de fechas ── -->
    <?php if (!empty($errorFiltro)): ?>
        <p class="error-filtro">⚠️ <?= htmlspecialchars($errorFiltro) ?></p>
    <?php endif; ?>

    <div class="filtro-card">
        <form method="get" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <label for="fechaInicio">Desde:</label>
            <input type="date" name="fechaInicio" id="fechaInicio"
                   value="<?= htmlspecialchars($fechaInicio) ?>" required>

            <label for="fechaFin">Hasta:</label>
            <input type="date" name="fechaFin" id="fechaFin"
                   value="<?= htmlspecialchars($fechaFin) ?>" required>

            <button type="submit">🔍 Filtrar</button>
            <a href="historial.php" class="limpiar">✕ Limpiar filtro</a>
        </form>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- SECCIÓN 1: COMPRAS DE ENTRADAS                                 -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="section-gap">
        <div class="section-heading">
            🎟️ Compras de Entradas
            <span class="badge badge-compra"><?= count($compras) ?> registro<?= count($compras) !== 1 ? 's' : '' ?></span>
        </div>

        <?php if (empty($compras)): ?>
            <div class="empty-state">
                <div class="icon">🎫</div>
                <p>No tienes compras de entradas registradas.</p>
                <a href="comprar.php">Comprar entradas</a>
            </div>
        <?php else: ?>
            <?php foreach ($compras as $compra): ?>
            <div class="compra-card">
                <div class="compra-header" onclick="toggleCard(this)">
                    <div>
                        <h3>Compra #<?= $compra->getId() ?></h3>
                        <div class="meta">
                            📅 <?= htmlspecialchars($compra->getFecha()) ?>
                            &nbsp;|&nbsp; 🕐 <?= htmlspecialchars($compra->getHora()) ?>
                            &nbsp;|&nbsp; <?= count($compra->getTickets()) ?> ticket<?= count($compra->getTickets()) !== 1 ? 's' : '' ?>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:.6rem;">
                        <div class="monto">Bs. <?= number_format($compra->getMonto(), 2) ?></div>
                        <span class="toggle-icon">▼</span>
                    </div>
                </div>
                <div class="compra-body">
                    <?php foreach ($compra->getTickets() as $ticket): ?>
                    <div class="ticket-item">
                        <img src="<?= htmlspecialchars($ticket->getCodigoQR()) ?>" alt="QR Ticket">
                        <div class="ticket-info">
                            <strong>Ticket #<?= $ticket->getId() ?></strong>
                            <span>Recorrido: <?= htmlspecialchars($ticket->getRecorrido()->getNombre()) ?></span>
                            <span>📅 <?= htmlspecialchars($ticket->getFecha()) ?> &nbsp;🕐 <?= htmlspecialchars($ticket->getHora()) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- SECCIÓN 2: RESERVAS GRUPALES                                   -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="section-gap">
        <div class="section-heading">
            🦁 Reservas de Tours Grupales
            <span class="badge badge-reserva"><?= count($reservasExtras) ?> registro<?= count($reservasExtras) !== 1 ? 's' : '' ?></span>
        </div>

        <?php if (empty($reservasExtras)): ?>
            <div class="empty-state">
                <div class="icon">🗓️</div>
                <p>No tienes reservas grupales registradas.</p>
                <a href="reservar.php">Reservar un tour grupal</a>
            </div>
        <?php else: ?>
            <?php foreach ($reservasExtras as $item):
                $reserva = $item['reserva'];
                $extras  = $item['extras'];
                $tipoLabel = $tiposLabel[$extras['tipo_institucion'] ?? ''] ?? ($extras['tipo_institucion'] ?? '—');
            ?>
            <div class="reserva-card">
                <div class="reserva-header" onclick="toggleCard(this)">
                    <div>
                        <h3>
                            Reserva #<?= $reserva->getId() ?>
                            <span class="tipo-badge">Tour Grupal</span>
                        </h3>
                        <div class="meta">
                            🏛️ <?= htmlspecialchars($reserva->getInstitucion()) ?>
                            &nbsp;|&nbsp; 📅 <?= htmlspecialchars(date('d/m/Y', strtotime($reserva->getFecha()))) ?>
                            &nbsp;|&nbsp; 👥 <?= $reserva->getCupos() ?> personas
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:.6rem;">
                        <div class="monto">
                            <?php if (!empty($extras['monto_total'])): ?>
                                Bs. <?= number_format((float)$extras['monto_total'], 2) ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </div>
                        <span class="toggle-icon">▼</span>
                    </div>
                </div>

                <div class="reserva-body">

                    <?php if (!empty($extras['codigo'])): ?>
                    <div>
                        <small style="font-size:.78rem; color:#888;">Código de confirmación:</small><br>
                        <span class="codigo-chip"><?= htmlspecialchars($extras['codigo']) ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="reserva-grid">

                        <div class="reserva-section">
                            <h4>📋 Institución</h4>
                            <div class="info-row"><span>Nombre:</span><span><?= htmlspecialchars($reserva->getInstitucion()) ?></span></div>
                            <div class="info-row"><span>Tipo:</span><span><?= htmlspecialchars($tipoLabel) ?></span></div>
                            <div class="info-row"><span>Personas:</span><span><?= $reserva->getCupos() ?></span></div>
                        </div>

                        <div class="reserva-section">
                            <h4>👤 Contacto</h4>
                            <?php if (!empty($extras['contacto_nombre'])): ?>
                            <div class="info-row"><span>Responsable:</span><span><?= htmlspecialchars($extras['contacto_nombre']) ?></span></div>
                            <?php endif; ?>
                            <?php if (!empty($extras['contacto_telefono'])): ?>
                            <div class="info-row"><span>Teléfono:</span><span><?= htmlspecialchars($extras['contacto_telefono']) ?></span></div>
                            <?php endif; ?>
                            <?php if (!empty($extras['contacto_email'])): ?>
                            <div class="info-row"><span>Email:</span><span><?= htmlspecialchars($extras['contacto_email']) ?></span></div>
                            <?php endif; ?>
                        </div>

                        <div class="reserva-section">
                            <h4>🗺️ Tour</h4>
                            <div class="info-row"><span>Recorrido:</span><span><?= htmlspecialchars($reserva->getRecorrido()->getNombre()) ?></span></div>
                            <div class="info-row"><span>Tipo:</span><span><?= htmlspecialchars($reserva->getRecorrido()->getTipo()) ?></span></div>
                            <div class="info-row"><span>Duración:</span><span><?= $reserva->getRecorrido()->getDuracion() ?> min</span></div>
                            <div class="info-row">
                                <span>Precio/persona:</span>
                                <span>Bs. <?= number_format($reserva->getRecorrido()->getPrecio(), 2) ?></span>
                            </div>
                        </div>

                        <div class="reserva-section">
                            <h4>📅 Fecha y Hora</h4>
                            <div class="info-row"><span>Fecha:</span><span><?= htmlspecialchars(date('d/m/Y', strtotime($reserva->getFecha()))) ?></span></div>
                            <div class="info-row"><span>Hora:</span><span><?= htmlspecialchars($reserva->getHora()) ?></span></div>
                            <?php if (!empty($extras['monto_total'])): ?>
                            <div class="info-row">
                                <span>Total estimado:</span>
                                <span style="color:var(--color-primary); font-size:1rem;">Bs. <?= number_format((float)$extras['monto_total'], 2) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($extras['fecha_registro'])): ?>
                            <div class="info-row"><span>Registrado:</span><span><?= htmlspecialchars($extras['fecha_registro']) ?></span></div>
                            <?php endif; ?>
                        </div>

                    </div>

                    <?php if (!empty($extras['observaciones'])): ?>
                    <div style="background:#fffdf0; border-left:3px solid var(--color-accent); padding:.6rem .9rem; border-radius:0 6px 6px 0; font-size:.87rem; margin-bottom:.8rem;">
                        <strong>Observaciones:</strong> <?= htmlspecialchars($extras['observaciones']) ?>
                    </div>
                    <?php endif; ?>

                    <div class="reserva-actions">
                        <a href="pagoqr_reserva.php" class="btn-sm btn-pdf">
                            📄 Ver comprobante PDF
                        </a>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</main>

<footer>
    <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> — Todos los derechos reservados</p>
</footer>

<script>
    function toggleCard(header) {
        const body = header.nextElementSibling;
        const isOpen = body.classList.contains('visible');
        body.classList.toggle('visible', !isOpen);
        header.classList.toggle('open', !isOpen);
    }
</script>

</body>
</html>
