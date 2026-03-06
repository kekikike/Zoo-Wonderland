<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\CompraService;
use App\Repositories\CompraRepository;


class CompraController
{
    private AuthService $auth;
    private CompraRepository $compraRepo;
    private CompraService $compraService;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->compraRepo = new CompraRepository();
        $this->compraService = new CompraService();
    }

    public function historial(): void
    {
        if (!$this->auth->check()) {
            header("Location: index.php?r=login");
            exit;
        }

        $user = $this->auth->user();
        $cliente = $this->compraRepo->findClienteByUsuario($user->id_usuario);
        if ($cliente) {
            $compras = $this->compraRepo->findByCliente($cliente['id_cliente']);
        } else {
            $compras = [];
        }

        require APP_PATH . '/views/compras/historial.php';
    }

    /**
     * Muestra el formulario de compra de tickets.
     */
    public function crear(): void
    {
        if (!$this->auth->check()) {
            header('Location: index.php?r=login');
            exit;
        }

        $usuario = $this->auth->user();
        $esCliente = $usuario ? $usuario->esCliente() : false;
        if (!$esCliente) {
            header('Location: index.php');
            exit;
        }

        $recorridoId = (int)($_GET['recorrido'] ?? 0);
        $recorridoRepo = new \App\Repositories\RecorridoRepository();
        $recorridos = $recorridoRepo->findAll();

        $form = [
            'recorrido_id' => $recorridoId,
            'cantidad'     => 1,
            'fecha'        => '',
            'hora'         => '',
        ];
        $errores = [];
        $mensaje = '';
        $disponibles = null;

        require APP_PATH . '/views/compras/crear.php';
    }

    /**
     * Procesa el POST del formulario de compra.
     */
    public function procesar(): void
    {
        if (!$this->auth->check()) {
            header('Location: index.php?r=login');
            exit;
        }

        $usuario = $this->auth->user();
        $esCliente = $usuario ? $usuario->esCliente() : false;
        if (!$esCliente) {
            header('Location: index.php');
            exit;
        }

        $form = [
            'recorrido_id' => (int)($_POST['recorrido_id'] ?? 0),
            'cantidad'     => (int)($_POST['cantidad'] ?? 0),
            'fecha'        => trim($_POST['fecha'] ?? ''),
            'hora'         => trim($_POST['hora'] ?? ''),
        ];

        $valid = $this->compraService->validarCompra(
            $form['recorrido_id'],
            $form['cantidad'],
            $form['fecha'],
            $form['hora']
        );

        if (!$valid['valido']) {
            $errores = $valid['errores'];
            $mensaje = 'Por favor corrige los errores.';
            $recorridoRepo = new \App\Repositories\RecorridoRepository();
            $recorridos = $recorridoRepo->findAll();
            // calcular disponibles si tenemos fecha/hora/recorrido
            $disponibles = null;
            if ($form['recorrido_id'] && $form['fecha'] && $form['hora']) {
                $rec = $recorridoRepo->findById($form['recorrido_id']);
                if ($rec) {
                    $vendidos = $this->compraRepo->countTicketsSold(
                        $form['recorrido_id'], $form['fecha'], $form['hora']
                    );
                    $disponibles = (int)$rec['capacidad'] - $vendidos;
                }
            }
            require APP_PATH . '/views/compras/crear.php';
        } else {
            $cliente = $this->compraRepo->findClienteByUsuario($usuario->id_usuario);
            $clienteId = $cliente['id_cliente'] ?? 0;
            $resultado = $this->compraService->procesarCompra(
                $clienteId,
                $form['recorrido_id'],
                $form['cantidad'],
                $form['fecha'],
                $form['hora']
            );
            if ($resultado) {
                header('Location: index.php?r=compras/pagoqr');
                exit;
            } else {
                $mensaje = 'Ocurrió un error al procesar la compra.';
                $recorridoRepo = new \App\Repositories\RecorridoRepository();
                $recorridos = $recorridoRepo->findAll();
                require APP_PATH . '/views/compras/crear.php';
            }
        }
    }

    public function showPagoQR(): void
    {
        if (!$this->auth->check() || !isset($_SESSION['ultima_compra_id'])) {
            header('Location: index.php');
            exit;
        }
        $compraId = $_SESSION['ultima_compra_id'];
        $datos = $_SESSION['ultima_compra_datos'];
        require APP_PATH . '/views/compras/pagoqr.php';
    }

    public function downloadPdf(): void
    {
        if (!$this->auth->check()) {
            exit('No autorizado');
        }

        $id = (int)($_GET['id'] ?? 0);
        $compra = $this->compraRepo->findById($id);
        if (!$compra) {
            exit('Compra no encontrada');
        }

        $extras = $_SESSION['ultima_compra_datos'] ?? [];
        
        // Si no hay datos en sesión, intentamos reconstruir los mínimos necesarios para el PDF
        if (empty($extras) || (isset($extras['compra_id']) && $extras['compra_id'] != $id)) {
            $detallesRepo = new \App\Repositories\RecorridoRepository();
            // Para tickets individuales, el flujo es algo distinto pero podemos sacar datos básicos
            // Aquí simplificamos para que al menos genere el PDF con montos base
            $extras = [
                'compra_id'   => $compra['id_compra'],
                'monto_total' => $compra['monto'],
                'fecha'       => $compra['fecha'],
                'hora'        => $compra['hora'],
                'codigo'      => strtoupper(substr(md5($compra['id_compra'] . $compra['fecha']), 0, 10)),
                'recorrido'   => 'Recorrido Zoo' // Dato genérico si no queremos hacer join complejo aquí
            ];
        }

        $pdfContent = $this->compraService->generarComprobante($extras + ['id' => $id]);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="comprobante_compra_'. $id .'.pdf"');
        echo $pdfContent;
        exit;
    }
}