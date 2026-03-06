<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Repositories\GuiaRepository;
use App\Repositories\ReporteRepository;

class GuiaController
{
    private AuthService $auth;
    private GuiaRepository $guiaRepo;
    private ReporteRepository $reporteRepo;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->guiaRepo = new GuiaRepository();
        $this->reporteRepo = new ReporteRepository();
    }

    private function checkAuth(): \App\Models\Usuario
    {
        if (!$this->auth->check()) {
            header('Location: index.php?r=login');
            exit;
        }
        $user = $this->auth->user();
        if (!$user->esGuia()) {
            header('Location: index.php');
            exit;
        }
        return $user;
    }

    public function dashboard(): void
    {
        $user = $this->checkAuth();
        $recorridosAsignados = $this->guiaRepo->getRecorridosAsignados($user->id_usuario);
        require APP_PATH . '/Views/guias/dashboard.php';
    }

    public function showReportForm(): void
    {
        $user = $this->checkAuth();
        $id_gr = (int)($_GET['id_gr'] ?? 0);
        $mensaje = null;

        if ($id_gr === 0) {
            // Mostrar lista de recorridos sin reporte
            $recorridosSinReporte = $this->reporteRepo->getRecorridosSinReportePorGuia($user->id_usuario);
            
            if (empty($recorridosSinReporte)) {
                $_SESSION['info'] = 'No hay recorridos pendientes para reportar en este momento.';
                header('Location: index.php?r=guias/dashboard');
                exit;
            }
            
            require APP_PATH . '/Views/guias/reporte_seleccionar.php';
            return;
        }

        // Obtener detalles del recorrido asignado
        $detalleRecorrido = $this->reporteRepo->getDetalleGuiaRecorrido($id_gr);
        
        if (!$detalleRecorrido) {
            $mensaje = 'El recorrido no existe.';
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        // Validacion 1: El recorrido debe haber sido realizado
        $fechaRecorrido = new \DateTime($detalleRecorrido['fecha_asignacion']);
        if ($fechaRecorrido > new \DateTime()) {
            $mensaje = 'Este recorrido aun no ha sido realizado. No se puede generar reporte.';
            $_SESSION['error'] = $mensaje;
            header('Location: index.php?r=guias/dashboard');
            exit;
        }

        // Validacion 2: Debe haber tickets confirmados
        if ((int)$detalleRecorrido['tickets_confirmados'] === 0) {
            $mensaje = 'No hay clientes confirmados para este recorrido. No se puede generar reporte.';
            $_SESSION['error'] = $mensaje;
            header('Location: index.php?r=guias/dashboard');
            exit;
        }

        // Validacion 3: Verificar que el recorrido NO tiene reporte previo
        if ($this->reporteRepo->existeReporte($id_gr)) {
            $mensaje = 'Este recorrido ya tiene un reporte registrado. No puede ser modificado.';
            $_SESSION['warning'] = $mensaje;
            header('Location: index.php?r=guias/reportes-historial');
            exit;
        }

        require APP_PATH . '/Views/guias/reporte_crear.php';
    }

    public function processReport(): void
    {
        $user = $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Metodo no permitido');
        }

        $id_gr = (int)($_POST['id_guia_recorrido'] ?? 0);
        $observaciones = trim($_POST['observaciones'] ?? '');

        try {
            // Validacion 1: ID valido
            if ($id_gr <= 0) {
                throw new \Exception('ID de guia-recorrido invalido.');
            }

            // Validacion 2: Observaciones con rango correcto
            if (strlen($observaciones) < 10) {
                throw new \Exception('Las observaciones deben tener minimo 10 caracteres.');
            }
            if (strlen($observaciones) > 1000) {
                throw new \Exception('Las observaciones no pueden exceder 1000 caracteres.');
            }

            // Validacion 3: Obtener detalles del recorrido
            $detalleRecorrido = $this->reporteRepo->getDetalleGuiaRecorrido($id_gr);
            if (!$detalleRecorrido) {
                throw new \Exception('El recorrido no existe.');
            }

            // Validacion 4: El recorrido debe haber sido realizado
            $fechaRecorrido = new \DateTime($detalleRecorrido['fecha_asignacion']);
            if ($fechaRecorrido > new \DateTime()) {
                throw new \Exception('El recorrido aun no ha sido realizado.');
            }

            // Validacion 5: Debe haber tickets confirmados
            if ((int)$detalleRecorrido['tickets_confirmados'] === 0) {
                throw new \Exception('No hay clientes confirmados para este recorrido.');
            }

            // Validacion 6: NO debe existir reporte previo
            if ($this->reporteRepo->existeReporte($id_gr)) {
                throw new \Exception('Este recorrido ya tiene un reporte. No puede ser modificado.');
            }

            // Validacion 7: Guardar el reporte
            if ($this->reporteRepo->save($id_gr, $observaciones)) {
                $_SESSION['mensaje_exito'] = 'Reporte guardado correctamente. Ya no puede ser modificado.';
                header('Location: index.php?r=guias/reportes-historial');
                exit;
            } else {
                throw new \Exception('Hubo un error al guardar el reporte. Intente nuevamente.');
            }

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?r=guias/reportes-crear&id_gr=' . $id_gr);
            exit;
        }
    }

    public function showReportHistory(): void
    {
        $user = $this->checkAuth();
        $reportes = $this->reporteRepo->getReportesPorGuia($user->id_usuario);
        require APP_PATH . '/Views/guias/reporte_historial.php';
    }
}
