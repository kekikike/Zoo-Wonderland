<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\CompraRepository;
use App\Repositories\TicketRepository;
use App\Repositories\RecorridoRepository;
use Dompdf\Dompdf;

/**
 * Servicio para la gestión de compras de tickets individuales (HU-03)
 */
class CompraService
{
    private CompraRepository $compraRepo;
    private TicketRepository $ticketRepo;
    private RecorridoRepository $recorridoRepo;

    public function __construct()
    {
        $this->compraRepo = new CompraRepository();
        $this->ticketRepo = new TicketRepository();
        $this->recorridoRepo = new RecorridoRepository();
    }

    /**
     * Valida los datos del formulario de compra.
     * Retorna array con 'valido' (bool), 'errores' (array), 'recorrido' (array)
     */
    public function validarCompra(
        int $recorridoId,
        int $cantidad,
        string $fecha,
        string $hora
    ): array {
        $errores = [];

        // Validar recorrido
        $recorridoData = $this->recorridoRepo->findById($recorridoId);
        if (!$recorridoData) {
            $errores['recorrido_id'] = 'Debe seleccionar un recorrido válido.';
        }

        // Validar cantidad
        if ($cantidad < 1) {
            $errores['cantidad'] = 'La cantidad debe ser al menos 1.';
        } elseif ($cantidad > 10) {
            $errores['cantidad'] = 'La cantidad máxima por compra es 10 tickets.';
        }

        // Validar fecha (no puede ser en el pasado)
        if (empty($fecha)) {
            $errores['fecha'] = 'La fecha es obligatoria.';
        } else {
            $fechaTs = strtotime($fecha);
            $hoyTs = strtotime(date('Y-m-d'));
            if ($fechaTs === false || $fechaTs < $hoyTs) {
                $errores['fecha'] = 'La fecha no puede ser en el pasado.';
            }
        }

        // Validar hora (09:00 – 15:00)
        if (empty($hora)) {
            $errores['hora'] = 'La hora es obligatoria.';
        } else {
            $horaInt = (int)str_replace(':', '', $hora);
            if ($horaInt < 900 || $horaInt > 1500) {
                $errores['hora'] = 'El horario disponible es de 09:00 a 15:00.';
            }
        }

        // Verificar capacidad si el recorrido existe
        if ($recorridoData && $fecha && $hora && !isset($errores['fecha']) && !isset($errores['hora'])) {
            $vendidos = $this->compraRepo->countTicketsSold(
                $recorridoId, $fecha, $hora
            );
            $disponibles = (int)$recorridoData['capacidad'] - $vendidos;
            if ($cantidad > $disponibles) {
                $errores['cantidad'] = "Solo quedan $disponibles entradas disponibles para ese horario.";
            }
        }

        if (!empty($errores)) {
            return ['valido' => false, 'errores' => $errores, 'recorrido' => $recorridoData ?? null];
        }

        return ['valido' => true, 'errores' => [], 'recorrido' => $recorridoData];
    }

    /**
     * Procesa y persiste la compra en la base de datos.
     * Inserta compra, tickets, y detalles de compra.
     */
    public function procesarCompra(
        int $clienteId,
        int $recorridoId,
        int $cantidad,
        string $fecha,
        string $hora
    ): ?array {
        // Validar primero
        $validacion = $this->validarCompra($recorridoId, $cantidad, $fecha, $hora);
        if (!$validacion['valido']) {
            return null;
        }

        $recorrido = $validacion['recorrido'];
        $precio = (float)$recorrido['precio'];
        $montoTotal = $precio * $cantidad;

        // Crear compra
        $compraId = $this->compraRepo->create([
            'id_cliente' => $clienteId,
            'fecha'      => date('Y-m-d'),
            'hora'       => date('H:i:s'),
            'monto'      => $montoTotal,
            'estado_pago'=> 0,
        ]);

        if (!$compraId) {
            return null;
        }

        // Crear tickets individuales usando el repositorio adecuado
        for ($i = 0; $i < $cantidad; $i++) {
            $codigo = strtoupper(uniqid('TK'));
            $ticketId = $this->ticketRepo->create(
                $compraId,
                $recorridoId,
                $fecha,
                $hora,
                $codigo
            );

            // Crear detalle de compra
            $this->compraRepo->addDetalle($compraId, $ticketId, $precio, 1);
        }

        // Preparar datos para sesión (para pagoqr.php)
        $_SESSION['ultima_compra_id'] = $compraId;
        $_SESSION['ultima_compra_datos'] = [
            'compra_id'     => $compraId,
            'recorrido'     => $recorrido['nombre'],
            'cantidad'      => $cantidad,
            'fecha'         => $fecha,
            'hora'          => $hora,
            'precio_unit'   => $precio,
            'monto_total'   => $montoTotal,
            'codigo'        => strtoupper(substr(md5($compraId . $fecha), 0, 10)),
            'qr_pago'       => 'img/qr.jpeg',
            'fecha_compra'  => date('Y-m-d H:i:s'),
        ];

        return [
            'compra_id'     => $compraId,
            'cantidad'      => $cantidad,
            'monto_total'   => $montoTotal,
            'fecha'         => $fecha,
            'hora'          => $hora,
        ];
    }

    /**
     * Genera el PDF del comprobante de compra usando Dompdf.
     */
    public function generarComprobante(array $datos): string
    {
        $html = $this->generarHtmlComprobante($datos);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->output() ?: '';
    }

    /**
     * Genera el HTML del comprobante para el PDF.
     */
    private function generarHtmlComprobante(array $datos): string
    {
        $compraId  = htmlspecialchars((string)($datos['id'] ?? $datos['compra_id'] ?? ''));
        $recorrido = htmlspecialchars((string)($datos['recorrido'] ?? ''));
        $cantidad   = (int)($datos['cantidad'] ?? 0);
        $fecha      = htmlspecialchars((string)($datos['fecha'] ?? ''));
        $hora       = htmlspecialchars((string)($datos['hora'] ?? ''));
        $precioUnit = number_format((float)($datos['precio_unit'] ?? 0), 2);
        $montoFmt   = number_format((float)($datos['monto_total'] ?? 0), 2);
        $codigo     = htmlspecialchars((string)($datos['codigo'] ?? ''));

        return "
<!DOCTYPE html>
<html lang='es'>
<head>
<meta charset='UTF-8'>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: Arial, Helvetica, sans-serif; color: #222; font-size: 12px; }

  .header {
    background: #2d5016;
    color: #fff;
    padding: 20px 30px;
  }
  .header h1  { font-size: 20px; margin-bottom: 4px; }
  .header p   { font-size: 11px; opacity: .85; }

  .codigo-box {
    background: #fffaf0;
    border: 2px dashed #bfb641;
    text-align: center;
    padding: 14px;
    margin: 18px 30px;
    border-radius: 8px;
  }
  .codigo-box strong { font-size: 22px; color: #2d5016; font-family: 'Courier New', monospace; }

  .body { padding: 0 30px 30px; }

  .section-title {
    font-size: 10px;
    text-transform: uppercase;
    color: #2d5016;
    font-weight: bold;
    margin: 18px 0 8px;
    border-bottom: 2px solid #d4e4c1;
    padding-bottom: 4px;
  }

  table { width: 100%; border-collapse: collapse; }
  thead tr { background: #f0f0f0; }
  th, td { padding: 8px 6px; text-align: left; }
  th { font-weight: bold; color: #2d5016; border-bottom: 2px solid #d4e4c1; }
  td { border-bottom: 1px solid #eee; }

  .total-box {
    background: #d4e4c1;
    border-radius: 8px;
    padding: 12px 16px;
    margin-top: 20px;
    text-align: right;
  }
  .total-box .amount { font-size: 22px; font-weight: bold; color: #2d5016; }

  .footer {
    border-top: 1px solid #eee;
    text-align: center;
    padding: 14px 30px 10px;
    font-size: 10px;
    color: #999;
  }
</style>
</head>
<body>
<div class='header'>
  <h1>Zoo Wonderland</h1>
  <p>Comprobante de Compra de Tickets</p>
  <p>Compra N° {$compraId} | Generado: " . date('d/m/Y H:i') . "</p>
</div>

<div class='codigo-box'>
  <strong>{$codigo}</strong>
</div>

<div class='body'>
  <div class='section-title'>Detalles de la Compra</div>
  <table>
    <thead>
      <tr>
        <th>Item</th>
        <th>Cantidad</th>
        <th>Precio Unit.</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>{$recorrido}</td>
        <td>{$cantidad}</td>
        <td>Bs. {$precioUnit}</td>
        <td>Bs. {$montoFmt}</td>
      </tr>
    </tbody>
  </table>

  <div class='section-title' style='margin-top: 20px;'>Información de los Tickets</div>
  <table>
    <tr><td><strong>Recorrido:</strong></td><td>{$recorrido}</td></tr>
    <tr><td><strong>Fecha:</strong></td><td>{$fecha}</td></tr>
    <tr><td><strong>Hora:</strong></td><td>{$hora}</td></tr>
    <tr><td><strong>Cantidad de Tickets:</strong></td><td>{$cantidad}</td></tr>
  </table>

  <div class='total-box'>
    <div class='amount'>Total: Bs. {$montoFmt}</div>
  </div>
</div>

<div class='footer'>
  Zoo Wonderland &copy; " . date('Y') . "
</div>
</body>
</html>";
    }
}
