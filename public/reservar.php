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
require_once SRC_PATH . '/Repositories/RecorridoRepository.php';

use App\Services\Auth;
use App\Services\ReservaService;
use App\Models\Cliente;

// ── Autenticación: solo clientes pueden reservar ────────────────────────
if (!Auth::check()) {
    header('Location: login.php?redirect=reservar.php');
    exit;
}

$usuario = Auth::user();
if (!$usuario instanceof Cliente) {
    header('Location: index.php');
    exit;
}

// ── Servicio ─────────────────────────────────────────────────────────────
$reservaService  = new ReservaService();
$recorridosGuiados = array_values($reservaService->obtenerRecorridosGuiados());

// ── Estado de la vista ───────────────────────────────────────────────────
$errores    = [];
$mensaje    = '';
$tipoMensaje = ''; // 'exito' | 'error'
$resultado  = null;   // datos de confirmación tras reserva exitosa
$paso       = 'formulario'; // 'formulario' | 'confirmacion'

// ── Tipos de institución disponibles ─────────────────────────────────────
$tiposInstitucion = [
    'colegio'     => '🏫 Colegio / Unidad Educativa',
    'universidad' => '🎓 Universidad / Instituto',
    'empresa'     => '🏢 Empresa',
    'ong'         => '🌿 ONG / Fundación',
    'gobierno'    => '🏛️ Entidad Gubernamental',
    'otro'        => '🔹 Otro',
];

// ── Valores del formulario (para repintar en caso de error) ──────────────
$form = [
    'recorrido_id'      => 0,
    'institucion'       => '',
    'tipo_institucion'  => '',
    'contacto_nombre'   => '',
    'contacto_telefono' => '',
    'contacto_email'    => '',
    'numero_personas'   => 10,
    'fecha'             => '',
    'hora'              => '',
    'observaciones'     => '',
];

// ── Procesamiento del formulario ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Leer y sanear entradas
    $form['recorrido_id']      = (int)($_POST['recorrido_id']      ?? 0);
    $form['institucion']       = trim($_POST['institucion']        ?? '');
    $form['tipo_institucion']  = trim($_POST['tipo_institucion']   ?? '');
    $form['contacto_nombre']   = trim($_POST['contacto_nombre']    ?? '');
    $form['contacto_telefono'] = trim($_POST['contacto_telefono']  ?? '');
    $form['contacto_email']    = trim($_POST['contacto_email']     ?? '');
    $form['numero_personas']   = (int)($_POST['numero_personas']   ?? 0);
    $form['fecha']             = trim($_POST['fecha']              ?? '');
    $form['hora']              = trim($_POST['hora']               ?? '');
    $form['observaciones']     = trim($_POST['observaciones']      ?? '');

    // Validar primero
    $validacion = $reservaService->validarReserva(
        $form['recorrido_id'],
        $form['institucion'],
        $form['tipo_institucion'],
        $form['contacto_nombre'],
        $form['contacto_telefono'],
        $form['contacto_email'],
        $form['numero_personas'],
        $form['fecha'],
        $form['hora'],
        $form['observaciones']
    );

    if (!$validacion['valido']) {
        $errores     = $validacion['errores'];
        $mensaje     = $validacion['mensaje'];
        $tipoMensaje = 'error';
    } else {
        // Procesar reserva
        $resultado = $reservaService->procesarReserva(
            $form['recorrido_id'],
            $form['institucion'],
            $form['tipo_institucion'],
            $form['contacto_nombre'],
            $form['contacto_telefono'],
            $form['contacto_email'],
            $form['numero_personas'],
            $form['fecha'],
            $form['hora'],
            $form['observaciones']
        );

        if ($resultado) {
            // Redirigir a la página de QR + PDF (igual que comprar.php → pagoqr.php)
            header('Location: pagoqr_reserva.php');
            exit;
        } else {
            $mensaje     = 'Hubo un error al procesar la reserva. Por favor intente nuevamente.';
            $tipoMensaje = 'error';
        }
    }
}

// Fecha mínima: hoy + 3 días
$fechaMin = date('Y-m-d', strtotime('+3 days'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Tour Grupal – <?= APP_NAME ?></title>
    <meta name="description" content="Reserva tours grupales para colegios, universidades y organizaciones en Zoo Wonderland. Experiencias únicas con guías especializados.">
    <style>
        /* ── Variables del sistema de diseño ── */
        :root {
            --color-primary:   #a3712a;
            --color-secondary: #977c66;
            --color-accent:    #bfb641;
            --color-light:     #ffe2a0;
            --color-dark:      #68672e;
            --color-info:      #7eaeb0;
            --color-bg:        #fffaf0;
            --color-success:   #2d6a4f;
            --color-error:     #9b2335;
            --radius:          10px;
            --shadow-sm:       0 2px 8px rgba(0,0,0,.10);
            --shadow-md:       0 4px 20px rgba(0,0,0,.15);
            --transition:      .25s ease;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--color-bg);
            color: #333;
            line-height: 1.7;
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

        .logo { font-size: 1.8rem; font-weight: 800; color: var(--color-light); letter-spacing: -.5px; }

        .nav-links { display: flex; gap: 2rem; list-style: none; }
        .nav-links a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: color var(--transition);
        }
        .nav-links a:hover, .nav-links a.active { color: var(--color-accent); }

        .auth-links { display: flex; align-items: center; gap: .8rem; }
        .auth-links span { color: var(--color-light); font-size: .9rem; }
        .auth-links a {
            padding: 7px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: .9rem;
            transition: opacity var(--transition);
        }
        .auth-links a:hover { opacity: .85; }
        .btn-logout { color: var(--color-light); border: 1px solid rgba(255,255,255,.4); }
        .btn-login  { background: var(--color-accent); color: var(--color-dark); font-weight: 700; }
        .btn-register { background: #fff; color: var(--color-primary); font-weight: 700; }

        /* ── Hero banner de la página ── */
        .page-hero {
            background: linear-gradient(160deg, var(--color-dark) 0%, var(--color-primary) 50%, var(--color-accent) 100%);
            color: #fff;
            text-align: center;
            padding: 3.5rem 1.5rem 2.5rem;
            position: relative;
            overflow: hidden;
        }
        .page-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Ccircle cx='50' cy='50' r='40' fill='none' stroke='rgba(255,255,255,.05)' stroke-width='20'/%3E%3C/svg%3E") repeat;
            pointer-events: none;
        }
        .page-hero h1 { font-size: 2.4rem; font-weight: 800; margin-bottom: .5rem; }
        .page-hero p  { font-size: 1.1rem; opacity: .9; max-width: 560px; margin: 0 auto; }

        /* ── Layout principal ── */
        main {
            max-width: 1100px;
            margin: 2.5rem auto;
            padding: 0 1.5rem;
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 2rem;
            align-items: start;
        }

        /* ── Sidebar de información ── */
        .sidebar { display: flex; flex-direction: column; gap: 1.2rem; }

        .info-card {
            background: #fff;
            border-radius: var(--radius);
            padding: 1.4rem;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--color-accent);
        }
        .info-card h3 { color: var(--color-primary); margin-bottom: .8rem; font-size: 1rem; }
        .info-card ul { list-style: none; }
        .info-card ul li {
            padding: .35rem 0;
            font-size: .9rem;
            display: flex;
            align-items: flex-start;
            gap: .5rem;
        }
        .info-card ul li::before { content: '✓'; color: var(--color-success); font-weight: 700; flex-shrink: 0; }

        .recorrido-mini {
            background: #fff;
            border-radius: var(--radius);
            padding: 1rem 1.2rem;
            box-shadow: var(--shadow-sm);
            border-top: 3px solid var(--color-info);
        }
        .recorrido-mini h4 { font-size: .9rem; color: var(--color-secondary); margin-bottom: .3rem; }
        .recorrido-mini p  { font-size: .82rem; color: #555; }
        .price-badge {
            display: inline-block;
            background: var(--color-light);
            color: var(--color-dark);
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .8rem;
            margin-top: .3rem;
        }

        /* ── Formulario ── */
        .form-card {
            background: #fff;
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-md);
        }
        .form-card h2 {
            color: var(--color-primary);
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        /* Secciones del formulario */
        .form-section {
            margin-bottom: 1.6rem;
            padding-bottom: 1.4rem;
            border-bottom: 1px solid #f0e8d8;
        }
        .form-section:last-of-type { border-bottom: none; }
        .form-section-title {
            font-size: .85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--color-secondary);
            margin-bottom: 1rem;
        }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-grid.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
        .col-span-2 { grid-column: span 2; }

        .form-group { display: flex; flex-direction: column; }
        .form-group label {
            font-size: .88rem;
            font-weight: 600;
            color: #444;
            margin-bottom: .35rem;
        }
        .form-group label .req { color: #c0392b; margin-left: 2px; }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: .65rem .85rem;
            border: 1.5px solid #ddd;
            border-radius: 7px;
            font-size: .95rem;
            font-family: inherit;
            transition: border-color var(--transition), box-shadow var(--transition);
            background: #fafafa;
            color: #333;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(163,113,42,.15);
            background: #fff;
        }
        .form-group textarea { resize: vertical; min-height: 80px; }

        .field-error {
            font-size: .8rem;
            color: var(--color-error);
            margin-top: .25rem;
            display: flex;
            align-items: center;
            gap: .3rem;
        }
        .field-error::before { content: '⚠'; }

        /* Estado de error en input */
        .form-group input.has-error,
        .form-group select.has-error,
        .form-group textarea.has-error {
            border-color: var(--color-error);
            background: #fff5f5;
        }

        /* ── Botón enviar ── */
        .btn-submit {
            width: 100%;
            padding: .9rem;
            background: linear-gradient(135deg, var(--color-primary), var(--color-dark));
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform var(--transition), box-shadow var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            margin-top: .5rem;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(163,113,42,.35);
        }
        .btn-submit:active { transform: translateY(0); }

        /* ── Alertas generales ── */
        .alert {
            padding: 1rem 1.2rem;
            border-radius: 8px;
            margin-bottom: 1.4rem;
            font-size: .95rem;
            display: flex;
            align-items: flex-start;
            gap: .6rem;
        }
        .alert-error {
            background: #fdecea;
            color: var(--color-error);
            border: 1px solid #f5c6cb;
        }
        .alert-exito {
            background: #d6eedd;
            color: var(--color-success);
            border: 1px solid #a8d5b5;
        }

        /* ── Recuadro de confirmación ── */
        .confirmacion-card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            grid-column: 1 / -1;  /* ocupa todo el ancho */
        }
        .conf-header {
            background: linear-gradient(135deg, var(--color-success), #40916c);
            color: #fff;
            padding: 2.2rem 2rem;
            text-align: center;
        }
        .conf-header .icon { font-size: 3.5rem; margin-bottom: .5rem; }
        .conf-header h2   { font-size: 1.8rem; margin-bottom: .3rem; }
        .conf-header p    { opacity: .9; }

        .conf-body { padding: 2rem; }

        .conf-code {
            text-align: center;
            background: var(--color-bg);
            border: 2px dashed var(--color-accent);
            border-radius: var(--radius);
            padding: 1.2rem;
            margin-bottom: 2rem;
        }
        .conf-code small { font-size: .8rem; color: #777; display: block; margin-bottom: .3rem; }
        .conf-code strong { font-size: 1.8rem; letter-spacing: .15em; color: var(--color-primary); font-family: monospace; }

        .conf-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.8rem;
        }

        .conf-section h4 {
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--color-secondary);
            margin-bottom: .7rem;
            padding-bottom: .3rem;
            border-bottom: 2px solid var(--color-light);
        }
        .conf-row {
            display: flex;
            justify-content: space-between;
            font-size: .9rem;
            padding: .3rem 0;
        }
        .conf-row span:first-child { color: #666; }
        .conf-row span:last-child  { font-weight: 600; color: #222; }

        .conf-total {
            background: var(--color-light);
            border-radius: 8px;
            padding: .9rem 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.6rem;
        }
        .conf-total span:first-child { font-size: 1rem; font-weight: 700; color: var(--color-dark); }
        .conf-total span:last-child  { font-size: 1.5rem; font-weight: 800; color: var(--color-primary); }

        .conf-notice {
            background: #fff8e1;
            border-left: 4px solid var(--color-accent);
            padding: .9rem 1rem;
            border-radius: 0 8px 8px 0;
            font-size: .88rem;
            color: #6b5900;
            margin-bottom: 1.5rem;
        }

        .conf-actions { display: flex; gap: 1rem; flex-wrap: wrap; }
        .btn-action {
            padding: .75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: .95rem;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            transition: opacity var(--transition), transform var(--transition);
            cursor: pointer;
            border: none;
            font-family: inherit;
        }
        .btn-action:hover { opacity: .88; transform: translateY(-1px); }
        .btn-primary { background: linear-gradient(135deg, var(--color-primary), var(--color-dark)); color: #fff; }
        .btn-outline { background: transparent; border: 2px solid var(--color-primary); color: var(--color-primary); }

        /* ── Tooltip del precio dinámico ── */
        #precio-resumen {
            display: none;
            background: var(--color-light);
            border-radius: 8px;
            padding: .7rem 1rem;
            margin-top: .8rem;
            font-size: .88rem;
            color: var(--color-dark);
        }
        #precio-resumen strong { font-size: 1.1rem; color: var(--color-primary); }

        /* ── Footer ── */
        footer {
            background: var(--color-secondary);
            color: #fff;
            text-align: center;
            padding: 2rem;
            margin-top: 4rem;
            font-size: .9rem;
        }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            main { grid-template-columns: 1fr; }
            .sidebar { order: 2; }
            .conf-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .form-grid, .form-grid.cols-3 { grid-template-columns: 1fr; }
            .col-span-2 { grid-column: unset; }
            .page-hero h1 { font-size: 1.7rem; }
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
            <li><a href="reservar.php" class="active">Tours Grupales</a></li>
            <li><a href="historial.php">Historial</a></li>
        </ul>
        <div class="auth-links">
            <?php if (Auth::check()): ?>
                <span>👤 <?= htmlspecialchars($usuario->getNombreCompleto() ?? 'Usuario') ?></span>
                <a href="logout.php" class="btn-action btn-logout">Salir</a>
            <?php else: ?>
                <a href="login.php" class="btn-action btn-login">Iniciar sesión</a>
                <a href="registrar.php" class="btn-action btn-register">Registrarse</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<!-- ===== HERO ===== -->
<div class="page-hero">
    <h1>🦁 Tours Grupales</h1>
    <p>Reserva una visita especial para tu colegio, universidad u organización con guías especializados.</p>
</div>

<!-- ===== MAIN ===== -->
<main>


<!-- ===== CONTENIDO PRINCIPAL: SIDEBAR + FORMULARIO ===== -->

    <!-- ======================================================= -->
    <!-- SIDEBAR                                                   -->
    <!-- ======================================================= -->
    <aside class="sidebar">


        <div class="info-card">
            <h3>📋 Requisitos para Tour Grupal</h3>
            <ul>
                <li>Mínimo 10 personas por grupo</li>
                <li>Máximo 200 personas por reserva</li>
                <li>Reserva con 3 días de anticipación</li>
                <li>Solo recorridos guiados disponibles</li>
                <li>Horarios: 09:00 a 15:00</li>
                <li>Datos de contacto institucional requeridos</li>
            </ul>
        </div>

        <div class="info-card">
            <h3>🌟 Beneficios del Tour Grupal</h3>
            <ul>
                <li>Guía especializado exclusivo</li>
                <li>Material educativo incluido</li>
                <li>Descripción técnica de los animales</li>
                <li>Fotografía profesional opcional</li>
                <li>Coordinación previa con administración</li>
            </ul>
        </div>

        <?php if (!empty($recorridosGuiados)): ?>
        <div class="info-card" style="border-left-color: var(--color-info);">
            <h3>🗺️ Recorridos Disponibles</h3>
            <?php foreach ($recorridosGuiados as $r): ?>
            <div class="recorrido-mini">
                <h4><?= htmlspecialchars($r['nombre']) ?></h4>
                <p>⏱ <?= $r['duracion'] ?> min &nbsp;|&nbsp; 👥 Hasta <?= $r['capacidad'] ?> personas</p>
                <span class="price-badge">Bs. <?= number_format($r['precio'], 2) ?> / persona</span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </aside>

    <!-- ======================================================= -->
    <!-- FORMULARIO                                               -->
    <!-- ======================================================= -->
    <div class="form-card">
        <h2>📝 Formulario de Reserva Grupal</h2>

        <?php if ($mensaje && $tipoMensaje === 'error'): ?>
        <div class="alert alert-error" role="alert">
            ⚠️ <?= htmlspecialchars($mensaje) ?>
        </div>
        <?php endif; ?>

        <form id="form-reserva" method="post" novalidate>

            <!-- ── Sección 1: Institución ── -->
            <div class="form-section">
                <div class="form-section-title">1. Datos de la Institución</div>
                <div class="form-grid">

                    <div class="form-group col-span-2">
                        <label for="institucion">Nombre de la Institución <span class="req">*</span></label>
                        <input
                            type="text"
                            id="institucion"
                            name="institucion"
                            placeholder="Ej: Unidad Educativa Simón Bolívar"
                            value="<?= htmlspecialchars($form['institucion']) ?>"
                            maxlength="150"
                            class="<?= isset($errores['institucion']) ? 'has-error' : '' ?>"
                        >
                        <?php if (isset($errores['institucion'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errores['institucion']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="tipo_institucion">Tipo de Institución <span class="req">*</span></label>
                        <select
                            id="tipo_institucion"
                            name="tipo_institucion"
                            class="<?= isset($errores['tipo_institucion']) ? 'has-error' : '' ?>"
                        >
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($tiposInstitucion as $valor => $etiqueta): ?>
                            <option value="<?= $valor ?>" <?= $form['tipo_institucion'] === $valor ? 'selected' : '' ?>>
                                <?= htmlspecialchars($etiqueta) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errores['tipo_institucion'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errores['tipo_institucion']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="numero_personas">Número de Personas <span class="req">*</span></label>
                        <input
                            type="number"
                            id="numero_personas"
                            name="numero_personas"
                            min="10"
                            max="200"
                            placeholder="Mínimo 10"
                            value="<?= (int)$form['numero_personas'] ?: '' ?>"
                            class="<?= isset($errores['numero_personas']) ? 'has-error' : '' ?>"
                        >
                        <?php if (isset($errores['numero_personas'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errores['numero_personas']) ?></span>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- ── Sección 2: Contacto ── -->
            <div class="form-section">
                <div class="form-section-title">2. Datos del Contacto Responsable</div>
                <div class="form-grid cols-3">

                    <div class="form-group col-span-2">
                        <label for="contacto_nombre">Nombre Completo del Responsable <span class="req">*</span></label>
                        <input
                            type="text"
                            id="contacto_nombre"
                            name="contacto_nombre"
                            placeholder="Ej: Juan Pérez García"
                            value="<?= htmlspecialchars($form['contacto_nombre']) ?>"
                            class="<?= isset($errores['contacto_nombre']) ? 'has-error' : '' ?>"
                        >
                        <?php if (isset($errores['contacto_nombre'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errores['contacto_nombre']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="contacto_telefono">Teléfono / Celular <span class="req">*</span></label>
                        <input
                            type="tel"
                            id="contacto_telefono"
                            name="contacto_telefono"
                            placeholder="Ej: 71234567"
                            maxlength="8"
                            value="<?= htmlspecialchars($form['contacto_telefono']) ?>"
                            class="<?= isset($errores['contacto_telefono']) ? 'has-error' : '' ?>"
                        >
                        <?php if (isset($errores['contacto_telefono'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errores['contacto_telefono']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group col-span-2">
                        <label for="contacto_email">Correo Electrónico Institucional <span class="req">*</span></label>
                        <input
                            type="email"
                            id="contacto_email"
                            name="contacto_email"
                            placeholder="Ej: contacto@miinstitucion.edu.bo"
                            value="<?= htmlspecialchars($form['contacto_email']) ?>"
                            class="<?= isset($errores['contacto_email']) ? 'has-error' : '' ?>"
                        >
                        <?php if (isset($errores['contacto_email'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errores['contacto_email']) ?></span>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- ── Sección 3: Recorrido y Fecha ── -->
            <div class="form-section">
                <div class="form-section-title">3. Recorrido y Fecha del Tour</div>
                <div class="form-grid">

                    <div class="form-group col-span-2">
                        <label for="recorrido_id">Recorrido Guiado <span class="req">*</span></label>
                        <select
                            id="recorrido_id"
                            name="recorrido_id"
                            class="<?= isset($errores['recorrido_id']) ? 'has-error' : '' ?>"
                        >
                            <option value="">-- Seleccionar recorrido --</option>
                            <?php foreach ($recorridosGuiados as $r): ?>
                            <option
                                value="<?= $r['id'] ?>"
                                data-precio="<?= $r['precio'] ?>"
                                data-capacidad="<?= $r['capacidad'] ?>"
                                <?= (int)$form['recorrido_id'] === $r['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($r['nombre']) ?> — <?= $r['duracion'] ?> min — Bs. <?= number_format($r['precio'], 2) ?>/persona
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errores['recorrido_id'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errores['recorrido_id']) ?></span>
                        <?php endif; ?>
                        <div id="precio-resumen">
                            💰 Total estimado: <strong id="total-display">Bs. 0.00</strong>
                            <span id="capacidad-info"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha del Tour <span class="req">*</span></label>
                        <input
                            type="date"
                            id="fecha"
                            name="fecha"
                            min="<?= $fechaMin ?>"
                            value="<?= htmlspecialchars($form['fecha']) ?>"
                            class="<?= isset($errores['fecha']) ? 'has-error' : '' ?>"
                        >
                        <?php if (isset($errores['fecha'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errores['fecha']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="hora">Hora de Inicio <span class="req">*</span></label>
                        <input
                            type="time"
                            id="hora"
                            name="hora"
                            min="09:00"
                            max="15:00"
                            value="<?= htmlspecialchars($form['hora']) ?>"
                            class="<?= isset($errores['hora']) ? 'has-error' : '' ?>"
                        >
                        <?php if (isset($errores['hora'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errores['hora']) ?></span>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- ── Sección 4: Observaciones ── -->
            <div class="form-section">
                <div class="form-section-title">4. Información Adicional (opcional)</div>
                <div class="form-group">
                    <label for="observaciones">Observaciones / Requerimientos especiales</label>
                    <textarea
                        id="observaciones"
                        name="observaciones"
                        placeholder="Ej: Necesitamos acceso para silla de ruedas; hay 3 personas con alergias..."
                        maxlength="500"
                    ><?= htmlspecialchars($form['observaciones']) ?></textarea>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="btn-enviar">
                📋 Confirmar Reserva Grupal
            </button>

        </form>

</main>


<footer>
    <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> — Todos los derechos reservados</p>
</footer>

<script>
    // ── Cálculo dinámico del total estimado ─────────────────────────────
    const selectRecorrido = document.getElementById('recorrido_id');
    const inputPersonas   = document.getElementById('numero_personas');
    const precioResumen   = document.getElementById('precio-resumen');
    const totalDisplay    = document.getElementById('total-display');
    const capacidadInfo   = document.getElementById('capacidad-info');

    function actualizarTotal() {
        const opt      = selectRecorrido?.selectedOptions[0];
        const precio   = parseFloat(opt?.dataset?.precio || 0);
        const cap      = parseInt(opt?.dataset?.capacidad || 0);
        const personas = parseInt(inputPersonas?.value || 0);

        if (precio > 0 && personas >= 1) {
            const total = precio * personas;
            totalDisplay.textContent = 'Bs. ' + total.toFixed(2);
            capacidadInfo.textContent = cap > 0 ? `  (capacidad máxima: ${cap} personas)` : '';
            precioResumen.style.display = 'block';
        } else {
            precioResumen.style.display = 'none';
        }
    }

    selectRecorrido?.addEventListener('change', actualizarTotal);
    inputPersonas?.addEventListener('input', actualizarTotal);

    // Ejecutar al cargar (por si hay valores previos)
    actualizarTotal();

    // ── Validación client-side básica ───────────────────────────────────
    const formReserva = document.getElementById('form-reserva');
    formReserva?.addEventListener('submit', function(e) {
        const personas = parseInt(inputPersonas?.value || 0);
        if (personas < 10) {
            e.preventDefault();
            inputPersonas.classList.add('has-error');
            inputPersonas.focus();
            alert('El número mínimo de personas para un tour grupal es 10.');
            return;
        }
        if (personas > 200) {
            e.preventDefault();
            inputPersonas.classList.add('has-error');
            inputPersonas.focus();
            alert('El número máximo de personas por reserva es 200.');
            return;
        }

        // Deshabilitar botón para evitar doble envío
        const btn = document.getElementById('btn-enviar');
        if (btn) {
            btn.disabled = true;
            btn.textContent = '⏳ Procesando reserva...';
        }
    });

    // Limpiar clase de error al escribir
    document.querySelectorAll('.has-error').forEach(el => {
        el.addEventListener('input', () => el.classList.remove('has-error'));
        el.addEventListener('change', () => el.classList.remove('has-error'));
    });
</script>

</body>
</html>
