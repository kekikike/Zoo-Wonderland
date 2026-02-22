<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Reserva;
use App\Models\Recorrido;
use App\Repositories\ReservaRepository;
use App\Repositories\RecorridoRepository;
use Dompdf\Dompdf;
use Exception;

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
        $recorrido = $this->recorridoRepo->findById($recorridoId);
        if (!$recorrido) {
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
        if ($recorrido && $numerPersonas > $recorrido['capacidad']) {
            $errores['numero_personas'] = "El recorrido seleccionado tiene capacidad máxima de {$recorrido['capacidad']} personas.";
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

        return ['valido' => true, 'mensaje' => '', 'errores' => [], 'recorrido' => $recorrido];
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
        string $observaciones
    ): ?array {
        $validacion = $this->validarReserva(
            $recorridoId, $institucion, $tipoInstitucion,
            $contactoNombre, $contactoTelefono, $contactoEmail,
            $numerPersonas, $fecha, $hora, $observaciones
        );

        if (!$validacion['valido']) {
            return null;
        }

        $recorridoData = $validacion['recorrido'];

        // Construir objeto Recorrido
        $recorrido = new Recorrido(
            $recorridoData['id'],
            $recorridoData['nombre'],
            $recorridoData['tipo'],
            $recorridoData['precio'],
            $recorridoData['duracion'],
            $recorridoData['capacidad']
        );

        // Calcular monto total
        $montoTotal = $recorrido->getPrecio() * $numerPersonas;

        // Construir objeto Reserva
        $reservaId = $this->reservaRepo->getNextId();
        $reserva   = new Reserva(
            $reservaId,
            $hora,
            $fecha,
            $numerPersonas,
            $institucion,
            $recorrido
        );

        // Guardar reserva en repositorio (sesión)
        $this->reservaRepo->add($reserva);

        // Persistir datos extra indexados por ID para el historial
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

        // Código de confirmación único
        $codigoConfirmacion = strtoupper(substr(md5($reservaId . $institucion . $fecha), 0, 10));

        // QR de pago (imagen base igual que en compras)
        $qrPago = 'img/qr.jpeg';

        $resultado = [
            'reserva'             => $reserva,
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
        return $dompdf->output();
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
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .header h1  { font-size: 20px; margin-bottom: 4px; }
  .header p   { font-size: 11px; opacity: .85; }
  .header .badge {
    background: #bfb641;
    color: #333;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: bold;
  }

  .codigo-box {
    background: #fffaf0;
    border: 2px dashed #bfb641;
    text-align: center;
    padding: 14px;
    margin: 18px 30px;
    border-radius: 8px;
  }
  .codigo-box small { display: block; font-size: 10px; color: #777; margin-bottom: 4px; }
  .codigo-box strong { font-size: 22px; letter-spacing: 4px; color: #a3712a; font-family: 'Courier New', monospace; }

  .body { padding: 0 30px 30px; }

  .section-title {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
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
  tr:nth-child(even) td { background: #fafaf5; }

  .total-box {
    background: #ffe2a0;
    border-radius: 8px;
    padding: 12px 16px;
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .total-box .label { font-size: 13px; font-weight: bold; color: #68672e; }
  .total-box .amount { font-size: 22px; font-weight: bold; color: #a3712a; }

  .notice {
    margin-top: 16px;
    background: #fff8e1;
    border-left: 4px solid #bfb641;
    padding: 10px 14px;
    font-size: 11px;
    color: #6b5900;
    border-radius: 0 6px 6px 0;
  }

  .footer {
    border-top: 1px solid #eee;
    text-align: center;
    padding: 14px 30px 10px;
    font-size: 10px;
    color: #999;
    margin-top: 24px;
  }

  .two-col { display: flex; gap: 20px; }
  .two-col > div { flex: 1; }
</style>
</head>
<body>

<div class='header'>
  <div>
    <h1>Zoo Wonderland</h1>
    <p>Comprobante de Reserva Grupal</p>
    <p>Reserva N° {$reserva->getId()} &nbsp;|&nbsp; Generado: " . date('d/m/Y H:i') . "</p>
  </div>
  <span class='badge'>TOUR GRUPAL</span>
</div>

<div class='codigo-box'>
  <small>Código de Confirmación</small>
  <strong>{$datosExtra['codigo']}</strong>
</div>

<div class='body'>

  <div class='two-col'>
    <div>
      <div class='section-title'>Institución</div>
      <table>
        <tr><td>Nombre:</td><td>" . htmlspecialchars($reserva->getInstitucion()) . "</td></tr>
        <tr><td>Tipo:</td><td>{$tipoLabel}</td></tr>
        <tr><td>N° de personas:</td><td>{$reserva->getCupos()} personas</td></tr>
      </table>
    </div>
    <div>
      <div class='section-title'>Contacto Responsable</div>
      <table>
        <tr><td>Nombre:</td><td>" . htmlspecialchars($datosExtra['contacto_nombre']) . "</td></tr>
        <tr><td>Teléfono:</td><td>" . htmlspecialchars($datosExtra['contacto_telefono']) . "</td></tr>
        <tr><td>Email:</td><td>" . htmlspecialchars($datosExtra['contacto_email']) . "</td></tr>
      </table>
    </div>
  </div>

  <div class='two-col'>
    <div>
      <div class='section-title'>Tour / Recorrido</div>
      <table>
        <tr><td>Recorrido:</td><td>" . htmlspecialchars($recorrido->getNombre()) . "</td></tr>
        <tr><td>Tipo:</td><td>" . htmlspecialchars($recorrido->getTipo()) . "</td></tr>
        <tr><td>Duración:</td><td>{$recorrido->getDuracion()} minutos</td></tr>
        <tr><td>Precio/persona:</td><td>Bs. {$precioPorP}</td></tr>
      </table>
    </div>
    <div>
      <div class='section-title'>Fecha y Hora</div>
      <table>
        <tr><td>Fecha:</td><td>{$fechaFmt}</td></tr>
        <tr><td>Hora:</td><td>{$reserva->getHora()}</td></tr>
        " . (!empty($obs) ? "<tr><td>Observaciones:</td><td>{$obs}</td></tr>" : "") . "
      </table>
    </div>
  </div>

  <div class='total-box'>
    <span class='label'>TOTAL ESTIMADO A PAGAR</span>
    <span class='amount'>Bs. {$montoFmt}</span>
  </div>

  <div class='notice'>
    <strong>Instrucciones:</strong> Presente este comprobante (impreso o digital) en la entrada del zoológico el día del tour.
    El pago deberá coordinarse con administración al confirmar la reserva. Este documento es válido únicamente con el
    código de confirmación <strong>{$datosExtra['codigo']}</strong>.
  </div>

</div>

<div class='footer'>
  Zoo Wonderland &copy; " . date('Y') . " &nbsp;|&nbsp; Documento generado automáticamente &nbsp;|&nbsp; No requiere firma
</div>

</body>
</html>";
    }
}
