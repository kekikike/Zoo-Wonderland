<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\ReservaService;
use App\Repositories\ReservaRepository;
use App\Repositories\RecorridoRepository;
use App\Repositories\CompraRepository;
use App\Models\Reserva;
use App\Models\Recorrido;

class ReservaController
{
    private AuthService $auth;
    private ReservaService $service;
    private ReservaRepository $repo;
    private RecorridoRepository $recorridoRepo;
    private CompraRepository $compraRepo;

    public function __construct()
    {
        $this->auth    = new AuthService();
        $this->service = new ReservaService();
        $this->repo    = new ReservaRepository();
        $this->recorridoRepo = new RecorridoRepository();
        $this->compraRepo = new CompraRepository();
    }

    public function showForm(): void
    {
        if (!$this->auth->check()) {
            header('Location: index.php?r=login');
            exit;
        }

        $usuario           = $this->auth->user();
        $recorridosGuiados = $this->service->obtenerRecorridosGuiados();
        $fechaMin          = date('Y-m-d', strtotime('+3 days'));

        $selectedRecorridoId = (int)($_GET['recorrido'] ?? 0);

        $form = [
            'recorrido_id'     => $selectedRecorridoId,
            'institucion'      => '',
            'tipo_institucion' => 'colegio',
            'contacto_nombre'  => '',
            'contacto_telefono'=> '',
            'contacto_email'   => '',
            'numero_personas'  => 10,
            'fecha'            => '',
            'hora'             => '',
            'observaciones'    => ''
        ];
        $errores = [];
        $mensaje = '';
        $disponibles = null;

        require APP_PATH . '/views/reservas/reservar.php';
    }

    public function processForm(): void
    {
        if (!$this->auth->check()) {
            header('Location: index.php?r=login');
            exit;
        }

        $usuario = $this->auth->user();

        $form = [
            'recorrido_id'     => (int)($_POST['recorrido_id'] ?? 0),
            'institucion'      => trim($_POST['institucion'] ?? ''),
            'tipo_institucion' => trim($_POST['tipo_institucion'] ?? ''),
            'contacto_nombre'  => trim($_POST['contacto_nombre'] ?? ''),
            'contacto_telefono'=> trim($_POST['contacto_telefono'] ?? ''),
            'contacto_email'   => trim($_POST['contacto_email'] ?? ''),
            'numero_personas'  => (int)($_POST['numero_personas'] ?? 0),
            'fecha'            => trim($_POST['fecha'] ?? ''),
            'hora'             => trim($_POST['hora'] ?? ''),
            'observaciones'    => trim($_POST['observaciones'] ?? '')
        ];

        $validacion = $this->service->validarReserva(
            $form['recorrido_id'], $form['institucion'], $form['tipo_institucion'],
            $form['contacto_nombre'], $form['contacto_telefono'], $form['contacto_email'],
            $form['numero_personas'], $form['fecha'], $form['hora'], $form['observaciones']
        );

        if (!$validacion['valido']) {
            $errores           = $validacion['errores'];
            $mensaje           = $validacion['mensaje'];
            $recorridosGuiados = $this->service->obtenerRecorridosGuiados();
            $fechaMin          = date('Y-m-d', strtotime('+3 days'));
            require APP_PATH . '/views/reservas/reservar.php';
        } else {
            $cliente = $this->compraRepo->findClienteByUsuario($usuario->id_usuario);
        $clienteId = $cliente['id_cliente'] ?? 0;

        $resultado = $this->service->procesarReserva(
                $form['recorrido_id'], $form['institucion'], $form['tipo_institucion'],
                $form['contacto_nombre'], $form['contacto_telefono'], $form['contacto_email'],
                $form['numero_personas'], $form['fecha'], $form['hora'], $form['observaciones'],
                $clienteId
            );
            if ($resultado) {
                header('Location: index.php?r=reservas/pagoqr');
                exit;
            } else {
                // Error inesperado
                header('Location: index.php?r=reservar');
                exit;
            }
        }
    }

    public function showPagoQR(): void
    {
        if (!$this->auth->check() || !isset($_SESSION['ultima_reserva_id'])) {
            header('Location: index.php');
            exit;
        }

        $reservaId = (int)($_SESSION['ultima_reserva_id'] ?? 0);
        $reservaData = $this->repo->findById($reservaId);
        if (!$reservaData) {
            header('Location: index.php');
            exit;
        }

        // Obtener datos extras de la sesión
        $datos = $_SESSION['ultima_reserva_datos'] ?? [];
        
        // Construir objeto Recorrido (necesario para el monto si falta la sesión)
        $recorridoData = $this->recorridoRepo->findById((int)$reservaData['id_recorrido']);

        // Si no hay datos en sesión (por ej. recarga de página tras expirar), proveer mínimos
        if (empty($datos)) {
            $monto = (float)($recorridoData['precio'] ?? 0) * (int)$reservaData['cupos'];
            $datos = [
                'monto_total' => $monto,
                'codigo'      => strtoupper(substr(md5($reservaData['id_reserva'] . $reservaData['institucion'] . $reservaData['fecha']), 0, 10)),
                'qr_pago'     => 'img/qr.jpeg'
            ];
        }

        $recorrido = new Recorrido(
            (int)($recorridoData['id_recorrido'] ?? $recorridoData['id']),
            (string)$recorridoData['nombre'],
            (string)$recorridoData['tipo'],
            (float)$recorridoData['precio'],
            (int)$recorridoData['duracion'],
            (int)$recorridoData['capacidad']
        );

        // Construir objeto Reserva
        $reserva = new Reserva(
            (int)$reservaData['id_reserva'],
            (string)$reservaData['hora'],
            (string)$reservaData['fecha'],
            (int)$reservaData['cupos'],
            (string)$reservaData['institucion'],
            $recorrido
        );

        $usuario = $this->auth->user();

        require APP_PATH . '/views/reservas/pagoqr.php';
    }

    public function showHistorial(): void
    {
        if (!$this->auth->check()) {
            header('Location: index.php?r=login');
            exit;
        }

        $usuario = $this->auth->user();
        $cliente = $this->compraRepo->findClienteByUsuario($usuario->id_usuario);
        $clienteId = $cliente['id_cliente'] ?? null;
        
        $reservasRaw = $this->repo->findAllWithExtras($clienteId);
        
        // Construir objetos Reserva a partir de los arrays
        $todasLasReservas = [];
        foreach ($reservasRaw as $item) {
            $r = $item['reserva'];
            $e = $item['extras'];
            
            // Obtener el recorrido
            $recorridoData = $this->recorridoRepo->findById($r['id_recorrido']);
            if ($recorridoData) {
                $recorrido = new Recorrido(
                    (int)($recorridoData['id_recorrido'] ?? $recorridoData['id']),
                    (string)$recorridoData['nombre'],
                    (string)$recorridoData['tipo'],
                    (float)$recorridoData['precio'],
                    (int)$recorridoData['duracion'],
                    (int)$recorridoData['capacidad']
                );
            } else {
                $recorrido = new Recorrido(0, 'Desconocido', '', 0, 0, 0);
            }
            
            // Construir objeto Reserva
            $reservaObj = new Reserva(
                (int)$r['id_reserva'],
                (string)$r['hora'],
                (string)$r['fecha'],
                (int)$r['cupos'],
                (string)$r['institucion'],
                $recorrido
            );
            
            // Si faltan los datos extras (por sesión expirada), calculamos el monto
            if (!isset($e['monto_total']) || empty($e['monto_total'])) {
                $e['monto_total'] = $recorrido->getPrecio() * $reservaObj->getCupos();
            }
            
            $todasLasReservas[] = ['reserva' => $reservaObj, 'extras' => $e];
        }

        require APP_PATH . '/views/reservas/historial.php';
    }

    public function downloadPdf(): void
    {
        if (!$this->auth->check()) {
            exit('No autorizado');
        }

        $id = (int)($_GET['id'] ?? 0);
        $reserva = $this->repo->findById($id);
        
        $usuario = $this->auth->user();
        $cliente = $this->compraRepo->findClienteByUsuario($usuario->id_usuario);
        $clienteId = $cliente['id_cliente'] ?? 0;

        if (!$reserva || $reserva['id_cliente'] != $clienteId) {
            exit('No autorizado o reserva no encontrada');
        }

        // Obtener extras de la sesión
        $extras = ($_SESSION['zoo_reservas_extras'] ?? [])[$id] ?? [];
        
        // Si no hay extras en sesión (por ej. sesión nueva), proveer mínimos para el PDF
        if (!isset($extras['monto_total']) || empty($extras['monto_total'])) {
            $recorridoData = $this->recorridoRepo->findById($reserva['id_recorrido']);
            $extras['monto_total'] = ($recorridoData['precio'] ?? 0) * $reserva['cupos'];
        }
        if (!isset($extras['codigo'])) {
            $extras['codigo'] = strtoupper(substr(md5($reserva['id_reserva'] . $reserva['institucion'] . $reserva['fecha']), 0, 10));
        }
        if (!isset($extras['tipo_institucion'])) $extras['tipo_institucion'] = 'otro';
        if (!isset($extras['contacto_nombre'])) $extras['contacto_nombre'] = 'N/A';

        // Construir objeto Recorrido
        $recorridoData = $this->recorridoRepo->findById((int)$reserva['id_recorrido']);
        if (!$recorridoData) {
            exit('Recorrido no encontrado');
        }

        $recorrido = new Recorrido(
            (int)$recorridoData['id_recorrido'] ?? $recorridoData['id'],
            (string)$recorridoData['nombre'],
            (string)$recorridoData['tipo'],
            (float)$recorridoData['precio'],
            (int)$recorridoData['duracion'],
            (int)$recorridoData['capacidad']
        );

        // Construir objeto Reserva
        $reservaObj = new Reserva(
            (int)$reserva['id_reserva'],
            (string)$reserva['hora'],
            (string)$reserva['fecha'],
            (int)$reserva['cupos'],
            (string)$reserva['institucion'],
            $recorrido
        );

        $pdfContent = $this->service->generarComprobanteReserva($reservaObj, $extras);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="comprobante_reserva_'.$id.'.pdf"');
        echo $pdfContent;
        exit;
    }
}
