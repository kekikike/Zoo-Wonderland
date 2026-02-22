<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Compra;
use App\Models\Ticket;
use App\Models\Cliente;
use App\Models\Recorrido;
use App\Repositories\CompraRepository;
use App\Repositories\RecorridoRepository;
use Dompdf\Dompdf;

class CompraService
{
    private CompraRepository $compraRepo;
    private RecorridoRepository $recorridoRepo;

    public function __construct()
    {
        $this->compraRepo = new CompraRepository();
        $this->recorridoRepo = new RecorridoRepository();
    }

    public function obtenerRecorridosDisponibles(): array
    {
        return $this->recorridoRepo->findAll();
    }

    public function validarCompra(int $recorridoId, int $cantidad, string $fecha, string $hora): array
    {
        $recorrido = $this->recorridoRepo->findById($recorridoId);
        if (!$recorrido) {
            return ['valido' => false, 'mensaje' => 'Recorrido no encontrado'];
        }

        if ($cantidad <= 0 || $cantidad > 10) {
            return ['valido' => false, 'mensaje' => 'Cantidad inválida (1-10)'];
        }

        // Validar fecha (debe ser futura)
        $fechaCompra = strtotime($fecha);
        if ($fechaCompra < strtotime('today')) {
            return ['valido' => false, 'mensaje' => 'Fecha debe ser futura'];
        }

        // Validar hora (asumir horarios de 9:00 a 17:00)
        $horaInt = (int)str_replace(':', '', $hora);
        if ($horaInt < 900 || $horaInt > 1700) {
            return ['valido' => false, 'mensaje' => 'Horario inválido (9:00-17:00)'];
        }

        // Verificar cupos disponibles (simplificado: capacidad total)
        if ($cantidad > $recorrido['capacidad']) {
            return ['valido' => false, 'mensaje' => 'No hay suficientes cupos'];
        }

        return ['valido' => true, 'recorrido' => $recorrido];
    }

    public function procesarCompra(Cliente $cliente, int $recorridoId, int $cantidad, string $fecha, string $hora): ?array
    {
        $validacion = $this->validarCompra($recorridoId, $cantidad, $fecha, $hora);
        if (!$validacion['valido']) {
            return null;
        }

        $recorridoData = $validacion['recorrido'];
        $recorrido = new Recorrido(
            $recorridoData['id'],
            $recorridoData['nombre'],
            $recorridoData['tipo'],
            $recorridoData['precio'],
            $recorridoData['duracion'],
            $recorridoData['capacidad']
        );

        $monto = $recorrido->getPrecio() * $cantidad;

        // Crear compra
        $compraId = $this->compraRepo->getNextId();
        $compra = new Compra($compraId, $fecha, $hora, $monto, $cliente);

        // Crear tickets
        for ($i = 0; $i < $cantidad; $i++) {
            $ticketId = $compraId * 100 + $i + 1; // ID único
            $codigoQR = $this->generarCodigoQR($ticketId, $fecha, $hora);
            $ticket = $compra->crearTicket($ticketId, $hora, $fecha, $codigoQR, $recorrido);
        }

        $this->compraRepo->add($compra);

        // Generar QR de pago
        $qrPago = $this->generarQRPago($compra);

        return ['compra' => $compra, 'qr_pago' => $qrPago];
    }

    private function generarCodigoQR(int $ticketId, string $fecha, string $hora): string
    {
        // Usar imagen base
        return 'img/qr.jpeg';
    }

    private function generarQRPago(Compra $compra): string
    {
        // Usar imagen base
        return 'img/qr.jpeg';
    }

    public function generarComprobante(Compra $compra): string
    {
        $html = $this->generarHTMLComprobante($compra);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->output();
    }

    private function generarHTMLComprobante(Compra $compra): string
    {
        $cliente = $compra->getCliente();
        $tickets = $compra->getTickets();

        $html = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { text-align: center; margin-bottom: 20px; }
                .ticket { border: 1px solid #000; padding: 10px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Zoo Wonderland - Comprobante de Compra</h1>
                <p>Compra ID: {$compra->getId()}</p>
                <p>Fecha: {$compra->getFecha()} Hora: {$compra->getHora()}</p>
                <p>Cliente: {$cliente->getNombreCompleto()}</p>
                <p>Total: Bs. {$compra->getMonto()}</p>
            </div>
            <h2>Tickets:</h2>
        ";

        foreach ($tickets as $ticket) {
            $html .= "
            <div class='ticket'>
                <p>Ticket ID: {$ticket->getId()}</p>
                <p>Recorrido: {$ticket->getRecorrido()->getNombre()}</p>
                <img src='{$ticket->getCodigoQR()}' alt='QR Code' />
            </div>
            ";
        }

        $html .= "</body></html>";
        return $html;
    }

    public function obtenerCuposDisponibles(int $recorridoId, string $fecha, string $hora): int
    {
        $recorrido = $this->recorridoRepo->findById($recorridoId);
        if (!$recorrido) return 0;

        // Simplificado: capacidad total menos compras existentes (no implementado)
        return $recorrido['capacidad'];
    }
}