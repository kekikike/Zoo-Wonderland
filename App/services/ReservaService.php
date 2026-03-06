<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Reserva;
use App\Models\Recorrido;
use App\Repositories\ReservaRepository;
use App\Repositories\RecorridoRepository;
use Dompdf\Dompdf;

/**
 * Servicio para la gestión de reservas grupales (HU-04)
 */
class ReservaService
{
    private ReservaRepository $reservaRepo;
    private RecorridoRepository $recorridoRepo;

    public function __construct()
    {
        $this->reservaRepo   = new ReservaRepository();
        $this->recorridoRepo = new RecorridoRepository();
    }

    /**
     * Retorna todos los recorridos disponibles (guiados) para reservas grupales.
     */
    public function obtenerRecorridosGuiados(): array
    {
        return $this->recorridoRepo->findByTipo('Guiado');
    }

    /**
     * Valida los datos del formulario de reserva grupal.
     */
    public function validarReserva(
        int    $recorridoId,
        string $institucion,
        string $tipoInstitucion,
        string $contactoNombre,
        string $contactoTelefono,
        string $contactoEmail,
        int    $numerPersonas,
        string $fecha,
        string $hora,
        string $observaciones
    ): array {
        $errores = [];

        // Validar recorrido
        $recorridoData = $this->recorridoRepo->findById($recorridoId);
        if (!$recorridoData) {
            $errores['recorrido_id'] = 'Debe seleccionar un recorrido válido.';
        }

        // Validar institución
        $institucion = trim($institucion);
        if (empty($institucion)) {
            $errores['institucion'] = 'El nombre de la institución es obligatorio.';
        } elseif (strlen($institucion) < 3) {
            $errores['institucion'] = 'El nombre debe tener al menos 3 caracteres.';
        } elseif (strlen($institucion) > 150) {
            $errores['institucion'] = 'El nombre no puede superar 150 caracteres.';
        }

        // Validar tipo institución
        $tiposValidos = ['colegio', 'universidad', 'empresa', 'ong', 'gobierno', 'otro'];
        if (!in_array($tipoInstitucion, $tiposValidos, true)) {
            $errores['tipo_institucion'] = 'Seleccione un tipo de institución válido.';
        }

        // Validar nombre de contacto
        $contactoNombre = trim($contactoNombre);
        if (empty($contactoNombre)) {
            $errores['contacto_nombre'] = 'El nombre del contacto es obligatorio.';
        } elseif (strlen($contactoNombre) < 3) {
            $errores['contacto_nombre'] = 'El nombre del contacto debe tener al menos 3 caracteres.';
        }

        // Validar teléfono (Bolivia: 8 dígitos, empieza en 6 o 7)
        $contactoTelefono = trim($contactoTelefono);
        if (empty($contactoTelefono)) {
            $errores['contacto_telefono'] = 'El teléfono de contacto es obligatorio.';
        } elseif (!preg_match('/^[67]\d{7}$/', $contactoTelefono)) {
            $errores['contacto_telefono'] = 'Ingrese un número de celular boliviano válido (ej: 71234567).';
        }

        // Validar correo electrónico
        $contactoEmail = trim($contactoEmail);
        if (empty($contactoEmail)) {
            $errores['contacto_email'] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($contactoEmail, FILTER_VALIDATE_EMAIL)) {
            $errores['contacto_email'] = 'Ingrese un correo electrónico válido.';
        }

        // Validar número de personas
        if ($numerPersonas < 10) {
            $errores['numero_personas'] = 'El mínimo para una reserva grupal es 10 personas.';
        } elseif ($numerPersonas > 200) {
            $errores['numero_personas'] = 'El máximo permitido es 200 personas por reserva.';
        }

        // Si el recorrido existe, verificar capacidad
        if ($recorridoData && $numerPersonas > $recorridoData['capacidad']) {
            $errores['numero_personas'] = "El recorrido seleccionado tiene capacidad máxima de {$recorridoData['capacidad']} personas.";
        }

        // comprobar cupos ya reservados para la misma fecha/hora
        if ($recorridoData && $fecha && $hora) {
            $ocupados = $this->reservaRepo->countByRecorridoFechaHora($recorridoId, $fecha, $hora);
            $disponibles = $recorridoData['capacidad'] - $ocupados;
            if ($numerPersonas > $disponibles) {
                $errores['numero_personas'] = "Solo quedan $disponibles cupos disponibles para ese horario.";
            }
        }

        // Validar fecha (al menos 3 días en el futuro)
        if (empty($fecha)) {
            $errores['fecha'] = 'La fecha es obligatoria.';
        } else {
            $fechaTs  = strtotime($fecha);
            $minFecha = strtotime('+3 days');
            if ($fechaTs === false || $fechaTs < $minFecha) {
                $errores['fecha'] = 'Las reservas grupales deben realizarse con al menos 3 días de anticipación.';
            }
        }

        // Validar hora (09:00 – 15:00)
        if (empty($hora)) {
            $errores['hora'] = 'La hora es obligatoria.';
        } else {
            $horaInt = (int)str_replace(':', '', $hora);
            if ($horaInt < 900 || $horaInt > 1500) {
                $errores['hora'] = 'El horario para grupos es entre 09:00 y 15:00.';
            }
        }

        if (!empty($errores)) {
            return ['valido' => false, 'mensaje' => 'Por favor corrija los errores en el formulario.', 'errores' => $errores];
        }

        return ['valido' => true, 'mensaje' => '', 'errores' => [], 'recorridoData' => $recorridoData];
    }

    /**
     * Procesa y guarda la reserva grupal en sesión.
     * Guarda también todos los datos extra en $_SESSION para recuperarlos en pagoqr_reserva.php
     */
    public function procesarReserva(
        int    $recorridoId,
        string $institucion,
        string $tipoInstitucion,
        string $contactoNombre,
        string $contactoTelefono,
        string $contactoEmail,
        int    $numerPersonas,
        string $fecha,
        string $hora,
        string $observaciones,
        int    $clienteId
    ): ?array {
        $validacion = $this->validarReserva(
            $recorridoId, $institucion, $tipoInstitucion,
            $contactoNombre, $contactoTelefono, $contactoEmail,
            $numerPersonas, $fecha, $hora, $observaciones
        );

        if (!$clienteId) {
            return null; // no hay cliente válido
        }

        if (!$validacion['valido']) {
            return null;
        }

        $recorridoData = $validacion['recorridoData'];

        // Construir objeto Recorrido
        $recorrido = new Recorrido(
            (int)$recorridoData['id'],
            (string)$recorridoData['nombre'],
            (string)$recorridoData['tipo'],
            (float)$recorridoData['precio'],
            (int)$recorridoData['duracion'],
            (int)$recorridoData['capacidad']
        );

        // Calcular monto total
        $montoTotal = $recorrido->getPrecio() * $numerPersonas;

        // clienteId ya viene como parámetro de la función
        if (!$clienteId) {
            return null; // no hay cliente válido
        }

        // Persistir reserva en la base de datos
        $reservaId = $this->reservaRepo->create([
            'fecha'       => $fecha,
            'hora'        => $hora,
            'cupos'       => $numerPersonas,
            'institucion' => $institucion,
            'comentario'  => $observaciones,
            'estado_pago' => 0,
            'estado'      => 1,
            'id_cliente'  => $clienteId,
            'id_recorrido'=> $recorridoId,
        ]);

        // Código de confirmación único
        $codigoConfirmacion = strtoupper(substr(md5($reservaId . $institucion . $fecha), 0, 10));

        // QR de pago (puede ser estático por ahora)
        $qrPago = 'img/qr.jpeg';

        // Persistir datos extra en sesión para el historial
        $this->reservaRepo->saveExtras($reservaId, [
            'tipo_institucion'  => $tipoInstitucion,
            'contacto_nombre'   => $contactoNombre,
            'contacto_telefono' => $contactoTelefono,
            'contacto_email'    => $contactoEmail,
            'observaciones'     => $observaciones,
            'monto_total'       => $montoTotal,
            'codigo'            => $codigoConfirmacion,
            'qr_pago'           => $qrPago,
            'fecha_registro'    => date('Y-m-d H:i:s'),
        ]);

        $resultado = [
            'reserva_id'          => $reservaId,
            'recorrido'           => $recorrido,
            'monto_total'         => $montoTotal,
            'tipo_institucion'    => $tipoInstitucion,
            'contacto_nombre'     => $contactoNombre,
            'contacto_telefono'   => $contactoTelefono,
            'contacto_email'      => $contactoEmail,
            'observaciones'       => $observaciones,
            'codigo_confirmacion' => $codigoConfirmacion,
            'qr_pago'             => $qrPago,
        ];

        // Persistir datos extra en sesión para que pagoqr_reserva.php los recupere
        $_SESSION['ultima_reserva_id']     = $reservaId;
        $_SESSION['ultima_reserva_datos']  = [
            'tipo_institucion'  => $tipoInstitucion,
            'contacto_nombre'   => $contactoNombre,
            'contacto_telefono' => $contactoTelefono,
            'contacto_email'    => $contactoEmail,
            'observaciones'     => $observaciones,
            'monto_total'       => $montoTotal,
            'codigo'            => $codigoConfirmacion,
            'qr_pago'           => $qrPago,
        ];

        return $resultado;
    }

    // ─────────────────────────────────────────────────────────────────────
    // PDF
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Genera el PDF del comprobante de reserva grupal usando Dompdf.
     */
    public function generarComprobanteReserva(Reserva $reserva, array $datosExtra): string
    {
        $html    = $this->generarHTMLComprobante($reserva, $datosExtra);
        $dompdf  = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->output() ?: '';
    }

    /**
     * Genera el HTML del comprobante de reserva para el PDF.
     */
    private function generarHTMLComprobante(Reserva $reserva, array $datosExtra): string
    {
        $recorrido  = $reserva->getRecorrido();
        $tiposLabel = [
            'colegio'     => 'Colegio / Unidad Educativa',
            'universidad' => 'Universidad / Instituto',
            'empresa'     => 'Empresa',
            'ong'         => 'ONG / Fundación',
            'gobierno'    => 'Entidad Gubernamental',
            'otro'        => 'Otro',
        ];
        $tipoLabel   = $tiposLabel[$datosExtra['tipo_institucion']] ?? $datosExtra['tipo_institucion'];
        $fechaFmt    = date('d/m/Y', strtotime($reserva->getFecha()));
        $montoFmt    = number_format((float)$datosExtra['monto_total'], 2);
        $precioPorP  = number_format($recorrido->getPrecio(), 2);
        $obs         = htmlspecialchars($datosExtra['observaciones'] ?? '');

        return "
<!DOCTYPE html>
<html lang='es'>
<head>
<meta charset='UTF-8'>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: Arial, Helvetica, sans-serif; color: #222; font-size: 12px; }

  .header {
    background: #a3712a;
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
  .codigo-box strong { font-size: 22px; color: #a3712a; font-family: 'Courier New', monospace; }

  .body { padding: 0 30px 30px; }

  .section-title {
    font-size: 10px;
    text-transform: uppercase;
    color: #977c66;
    font-weight: bold;
    margin: 18px 0 8px;
    border-bottom: 2px solid #ffe2a0;
    padding-bottom: 4px;
  }

  table { width: 100%; border-collapse: collapse; }
  td { padding: 5px 6px; vertical-align: top; }
  td:first-child { color: #666; width: 42%; }
  td:last-child  { font-weight: bold; color: #222; }

  .total-box {
    background: #ffe2a0;
    border-radius: 8px;
    padding: 12px 16px;
    margin-top: 20px;
    text-align: right;
  }
  .total-box .amount { font-size: 22px; font-weight: bold; color: #a3712a; }

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
  <p>Comprobante de Reserva Grupal</p>
  <p>Reserva N° {$reserva->getId()} | Generado: " . date('d/m/Y H:i') . "</p>
</div>

<div class='codigo-box'>
  <strong>{$datosExtra['codigo']}</strong>
</div>

<div class='body'>
  <div class='section-title'>Detalles de la Reserva</div>
  <table>
    <tr><td>Institución:</td><td>" . htmlspecialchars($reserva->getInstitucion()) . " ({$tipoLabel})</td></tr>
    <tr><td>N° Personas:</td><td>{$reserva->getCupos()}</td></tr>
    <tr><td>Recorrido:</td><td>" . htmlspecialchars($recorrido->getNombre()) . "</td></tr>
    <tr><td>Fecha:</td><td>{$fechaFmt}</td></tr>
    <tr><td>Hora:</td><td>{$reserva->getHora()}</td></tr>
    <tr><td>Responsable:</td><td>" . htmlspecialchars($datosExtra['contacto_nombre'] ?? '') . "</td></tr>
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
