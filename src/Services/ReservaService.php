<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Reserva;
use App\Models\Recorrido;
use App\Repositories\ReservaRepository;
use App\Repositories\RecorridoRepository;
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
     * Retorna un array con ['valido' => bool, 'mensaje' => string, 'errores' => array]
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

        // Validar teléfono (Bolivia: 7-8 dígitos)
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

        // Validar fecha (debe ser al menos 3 días en el futuro para reservas grupales)
        if (empty($fecha)) {
            $errores['fecha'] = 'La fecha es obligatoria.';
        } else {
            $fechaTs = strtotime($fecha);
            $minFecha = strtotime('+3 days');
            if ($fechaTs === false || $fechaTs < $minFecha) {
                $errores['fecha'] = 'Las reservas grupales deben realizarse con al menos 3 días de anticipación.';
            }
        }

        // Validar hora (09:00 – 15:00 para grupos, para terminar antes de las 17:00)
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
     * Retorna la Reserva creada o null si hay error.
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
        // Extendemos el objeto Reserva con datos adicionales vía un array enriquecido en sesión
        $reserva = new Reserva(
            $reservaId,
            $hora,
            $fecha,
            $numerPersonas,
            $institucion,
            $recorrido
        );

        // Guardar en repositorio (sesión)
        $this->reservaRepo->add($reserva);

        // Código de confirmación único
        $codigoConfirmacion = strtoupper(substr(md5($reservaId . $institucion . $fecha), 0, 10));

        return [
            'reserva'             => $reserva,
            'recorrido'           => $recorrido,
            'monto_total'         => $montoTotal,
            'tipo_institucion'    => $tipoInstitucion,
            'contacto_nombre'     => $contactoNombre,
            'contacto_telefono'   => $contactoTelefono,
            'contacto_email'      => $contactoEmail,
            'observaciones'       => $observaciones,
            'codigo_confirmacion' => $codigoConfirmacion,
        ];
    }
}
