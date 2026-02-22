<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

session_start();
require_once __DIR__ . '/../config/constants.php';
require_once SRC_PATH . '/Services/autoload_session.php';
require_once SRC_PATH . '/Services/Auth.php';
require_once SRC_PATH . '/Services/CompraService.php';
require_once SRC_PATH . '/Models/Cliente.php';
require_once SRC_PATH . '/Repositories/CompraRepository.php';

use App\Services\Auth;
use App\Services\CompraService;
use App\Models\Cliente;
use App\Repositories\CompraRepository;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$compraService = new CompraService();
$recorridos = $compraService->obtenerRecorridosDisponibles();
$compraRepo = new CompraRepository();

// Obtener cliente de la sesión
$usuario = Auth::user();
if (!$usuario instanceof Cliente) {
    header('Location: index.php');
    exit;
}
$cliente = $usuario;

// Preseleccionar recorrido si viene por GET
$recorridoSeleccionado = null;
if (isset($_GET['recorrido'])) {
    $recorridoId = (int)$_GET['recorrido'];
    foreach ($recorridos as $r) {
        if ($r['id'] === $recorridoId) {
            $recorridoSeleccionado = $r;
            break;
        }
    }
}

$mensaje = '';
$compra = null;
$qrPago = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recorridoId = (int)($_POST['recorrido_id'] ?? 0);
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';

    error_log("POST en comprar.php: recorridoId=$recorridoId, cantidad=$cantidad, fecha=$fecha, hora=$hora");

    $resultado = $compraService->procesarCompra($cliente, $recorridoId, $cantidad, $fecha, $hora);

    if ($resultado) {
        $compra = $resultado['compra'];
        $_SESSION['ultima_compra_id'] = $compra->getId();
        error_log("Compra exitosa, redirigiendo a pagoqr.php, compra ID: " . $compra->getId());
        header('Location: pagoqr.php');
        exit;
    } else {
        $mensaje = 'Error en la compra. Verifique los datos. Recorrido ID: ' . $recorridoId . ', Cantidad: ' . $cantidad . ', Fecha: ' . $fecha . ', Hora: ' . $hora;
        error_log("Compra fallida: $mensaje");
    }
}

// Si se solicita descargar comprobante
if (isset($_GET['descargar'])) {
    $compraId = (int)($_SESSION['ultima_compra_id'] ?? 0);
    $compra = $compraRepo->findById($compraId);
    if ($compra) {
        $pdfContent = $compraService->generarComprobante($compra);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="comprobante_' . $compra->getId() . '.pdf"');
        echo $pdfContent;
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Entradas - <?= APP_NAME ?></title>
    <style>
        :root {
            --color-primary:    #a3712a;
            --color-secondary:  #977c66;
            --color-accent:     #bfb641;
            --color-light:      #ffe2a0;
            --color-dark:       #68672e;
            --color-info:       #7eaeb0;
            --color-bg:         #fffaf0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--color-bg);
            color: #333;
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: white;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--color-light);
        }

        .nav-links {
            display: flex;
            gap: 2.2rem;
            list-style: none;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--color-accent);
        }

        .auth-links a {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
        }

        .login-btn {
            background: var(--color-accent);
            color: var(--color-dark);
        }

        .register-btn {
            background: white;
            color: var(--color-primary);
        }

        main {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: var(--color-primary);
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
        }

        select, input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background-color: var(--color-primary);
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: var(--color-dark);
        }

        .mensaje {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .recorrido {
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .recorrido-info {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            background: #f9f9f9;
        }

        .qr-section {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f9f9f9;
        }

        footer {
            background: var(--color-secondary);
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 4rem;
        }

        @media (max-width: 768px) {
            .nav-links { gap: 1.2rem; font-size: 0.95rem; }
        }
    </style>
</head>
<body>

<header>
    <nav>
        <div class="logo"><?= APP_NAME ?></div>
        <ul class="nav-links">
            <li><a href="index.php#inicio">Inicio</a></li>
            <li><a href="index.php#nosotros">Nosotros</a></li>
            <li><a href="index.php#visitanos">Visítanos</a></li>
            <li><a href="historial.php">Historial</a></li>
        </ul>
        <div class="auth-links">
            <?php if (Auth::check()): ?>
                <span>Bienvenido, <?= htmlspecialchars(Auth::user()->getNombreCompleto() ?? 'Usuario') ?></span>
                <a href="logout.php" style="margin-left:1.5rem;color:#ffe2a0;">Cerrar sesión</a>
            <?php else: ?>
                <a href="login.php" class="btn login-btn">Iniciar sesión</a>
                <a href="registrar.php" class="btn register-btn">Registrarse</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main>
    <div class="container">
        <h1>Comprar Entradas</h1>

        <?php if ($mensaje): ?>
            <div class="mensaje error">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <?php if ($recorridoSeleccionado): ?>
            <div class="recorrido-info">
                <h2>Recorrido Seleccionado</h2>
                <p><strong>Nombre:</strong> <?= htmlspecialchars($recorridoSeleccionado['nombre']) ?> (<?= htmlspecialchars($recorridoSeleccionado['tipo']) ?>)</p>
                <p><strong>Precio:</strong> Bs. <?= $recorridoSeleccionado['precio'] ?> | <strong>Duración:</strong> <?= $recorridoSeleccionado['duracion'] ?> min | <strong>Capacidad:</strong> <?= $recorridoSeleccionado['capacidad'] ?></p>
            </div>
        <?php else: ?>
            <div class="recorrido-info">
                <h2>Seleccionar Recorrido</h2>
                <p>Selecciona un recorrido para continuar.</p>
            </div>
        <?php endif; ?>

        <form method="post">
            <?php if ($recorridoSeleccionado): ?>
                <input type="hidden" name="recorrido_id" value="<?= $recorridoSeleccionado['id'] ?>">
            <?php else: ?>
                <label for="recorrido_id">Seleccionar Recorrido:</label>
                <select name="recorrido_id" id="recorrido_id" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($recorridos as $recorrido): ?>
                        <option value="<?= $recorrido['id'] ?>">
                            <?= htmlspecialchars($recorrido['nombre']) ?> - Bs. <?= $recorrido['precio'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <label for="cantidad">Cantidad de Entradas:</label>
            <input type="number" name="cantidad" id="cantidad" min="1" max="10" required>

            <label for="fecha">Fecha:</label>
            <input type="date" name="fecha" id="fecha" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">

            <label for="hora">Hora:</label>
            <input type="time" name="hora" id="hora" required min="09:00" max="17:00">

            <button type="submit">Comprar y Generar Comprobante</button>
        </form>
    </div>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> - Todos los derechos reservados</p>
</footer>

</body>
</html>
