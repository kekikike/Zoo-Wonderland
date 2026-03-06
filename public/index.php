<?php
// public/index.php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);

define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('CORE_PATH',   ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

require_once ROOT_PATH . '/vendor/autoload.php';

require_once CORE_PATH . '/Session.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Authorization.php';

\Core\Session::iniciarSesionSegura();
\Core\Database::getInstance();

$r = $_GET['r'] ?? '/';
$r = trim($r, '/');
$r = $r === '' ? '/' : $r;

switch ($r) {
    case '/':
    case '':
        $controller = new \App\Controllers\HomeController();
        $controller->index();
        break;

    case 'login':
        $authCtrl = new \App\Controllers\AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authCtrl->login();
        } else {
            $authCtrl->showLogin();
        }
        break;

    case 'registro':
        $authCtrl = new \App\Controllers\AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authCtrl->register();
        } else {
            $authCtrl->showRegister();
        }
        break;

    case 'logout':
        $authCtrl = new \App\Controllers\AuthController();
        $authCtrl->logout();
        break;

    // ── COMPRAS ─────────────────────────────────────────────────
    case 'compras/crear':
        \Core\Authorization::requireCliente();
        $compraCtrl = new \App\Controllers\CompraController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $compraCtrl->procesar();
        } else {
            $compraCtrl->crear();
        }
        break;

    case 'compras/pagoqr':
        \Core\Authorization::requireCliente();
        $compraCtrl = new \App\Controllers\CompraController();
        $compraCtrl->showPagoQR();
        break;

    case 'compras/pdf':
        \Core\Authorization::requireCliente();
        $compraCtrl = new \App\Controllers\CompraController();
        $compraCtrl->downloadPdf();
        break;

    case 'historial':
        $userCtrl = new \App\Controllers\UsuarioController();
        $userCtrl->historial();
        break;

    case 'compras/historial':
        \Core\Authorization::requireCliente();
        $compraCtrl = new \App\Controllers\CompraController();
        $compraCtrl->historial();
        break;

    // ── RESERVAS ────────────────────────────────────────────────
    case 'reservar':
        \Core\Authorization::requireCliente();
        $reservaCtrl = new \App\Controllers\ReservaController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reservaCtrl->processForm();
        } else {
            $reservaCtrl->showForm();
        }
        break;

    case 'reservas/pagoqr':
        \Core\Authorization::requireCliente();
        $reservaCtrl = new \App\Controllers\ReservaController();
        $reservaCtrl->showPagoQR();
        break;

    case 'reservas/historial':
        \Core\Authorization::requireCliente();
        $reservaCtrl = new \App\Controllers\ReservaController();
        $reservaCtrl->showHistorial();
        break;

    case 'reservas/pdf':
        \Core\Authorization::requireCliente();
        $reservaCtrl = new \App\Controllers\ReservaController();
        $reservaCtrl->downloadPdf();
        break;

    // ── GUÍA ────────────────────────────────────────────────────
    case 'guias/dashboard':
        $user = \Core\Authorization::requireGuia();
        $guiaCtrl = new \App\Controllers\GuiaController();
        $guiaCtrl->dashboard();
        break;

    case 'guias/horarios':
        $user = \Core\Authorization::requireGuia();

        $semanaOffset = (int)($_GET['semana'] ?? 0);
        $semanaOffset = max(0, min(1, $semanaOffset));

        $hoy   = new DateTime();
        $lunes = clone $hoy;
        $lunes->modify('Monday this week');

        if ($semanaOffset === 1) {
            $lunes->modify('+7 days');
        }

        $inicioSemana = (clone $lunes)->modify('+1 day')->format('Y-m-d');
        $finSemana    = (clone $lunes)->modify('+6 days')->format('Y-m-d');

        $guiaRepo            = new \App\Repositories\GuiaRepository();
        $datosGuia           = $guiaRepo->getHorariosGuia($user->id_usuario);
        $recorridosPorSemana = $guiaRepo->getRecorridosPorSemana(
            $user->id_usuario,
            $inicioSemana,
            $finSemana
        );

        require_once APP_PATH . '/Views/guias/horarios.php';
        break;

    case 'guias/detalle-recorrido':
        $user         = \Core\Authorization::requireGuia();
        $id_recorrido = (int)($_GET['id'] ?? 0);

        if ($id_recorrido <= 0) {
            http_response_code(400);
            echo "<h1>400 Bad Request</h1>";
            exit;
        }

        $guiaRepo  = new \App\Repositories\GuiaRepository();
        $recorrido = $guiaRepo->getDetalleRecorrido($id_recorrido, $user->id_usuario);

        if (!$recorrido) {
            http_response_code(404);
            echo "<h1>404 Recorrido no encontrado</h1>";
            exit;
        }

        $areas = $guiaRepo->getAreasPorRecorrido($id_recorrido);
        require_once APP_PATH . '/Views/guias/detalle_recorrido.php';
        break;

    case 'guias/reportes-crear':
    case 'guias/reportes-guardar':
    case 'guias/reportes-historial':
        $user     = \Core\Authorization::requireGuia();
        $guiaCtrl = new \App\Controllers\GuiaController();
        if ($r === 'guias/reportes-crear') {
            $guiaCtrl->showReportForm();
        } elseif ($r === 'guias/reportes-guardar') {
            $guiaCtrl->processReport();
        } else {
            $guiaCtrl->showReportHistory();
        }
        break;

    // ── ADMIN ───────────────────────────────────────────────────
    case 'admin/dashboard':
    case 'admin/recorridos':
    case 'admin/animales':
    case 'admin/animales/crear':
    case 'admin/animales/guardar':
    case 'admin/animales/editar':
    case 'admin/animales/actualizar':
    case 'admin/animales/eliminar':
        $user      = \Core\Authorization::requireAdmin();
        $adminCtrl = new \App\Controllers\AdminController();

        if ($r === 'admin/dashboard') {
            $adminCtrl->dashboard();
        } elseif ($r === 'admin/recorridos') {
            $adminCtrl->recorridos();
        } else {
            $animCtrl = new \App\Controllers\AnimalController();
            if ($r === 'admin/animales') {
                $animCtrl->index();
            } elseif ($r === 'admin/animales/crear') {
                $animCtrl->crear();
            } elseif ($r === 'admin/animales/guardar') {
                $animCtrl->guardar();
            } elseif ($r === 'admin/animales/editar') {
                $animCtrl->editar();
            } elseif ($r === 'admin/animales/actualizar') {
                $animCtrl->actualizar();
            } elseif ($r === 'admin/animales/eliminar') {
                $animCtrl->eliminar();
            }
        }
        break;

    // ── ADMIN: gestión de usuarios (HU-10) ──────────────────────
    case 'admin/usuarios':
        $user      = \Core\Authorization::requireAdmin();
        $adminCtrl = new \App\Controllers\AdminController();
        $adminCtrl->usuarios();
        break;

    case 'admin/usuario-editar':
        $user      = \Core\Authorization::requireAdmin();
        $adminCtrl = new \App\Controllers\AdminController();
        $adminCtrl->editarUsuarioForm();
        break;

    case 'admin/usuario-editar-post':
        $user      = \Core\Authorization::requireAdmin();
        $adminCtrl = new \App\Controllers\AdminController();
        $adminCtrl->editarUsuarioPost();
        break;

    case 'admin/usuario-toggle':
        $user      = \Core\Authorization::requireAdmin();
        $adminCtrl = new \App\Controllers\AdminController();
        $adminCtrl->toggleEstado();
        break;

    // ── 404 ─────────────────────────────────────────────────────
    default:
        http_response_code(404);
        echo "<h1>404 - Ruta no encontrada: " . htmlspecialchars($r) . "</h1>";
        exit;
}