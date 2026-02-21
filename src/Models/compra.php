<?php
declare(strict_types=1);

namespace App\Models;

class Compra {

    private int $id;
    private string $fecha;
    private string $hora;
    private float $monto;
    private Cliente $cliente;
    private array $tickets = [];

    public function __construct(
        int $id,
        string $fecha,
        string $hora,
        float $monto,
        Cliente $cliente
    ) {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->hora = $hora;
        $this->monto = $monto;
        $this->cliente = $cliente;
    }

    // Getters

    public function getId(): int {
        return $this->id;
    }

    public function getFecha(): string {
        return $this->fecha;
    }

    public function getHora(): string {
        return $this->hora;
    }

    public function getMonto(): float {
        return $this->monto;
    }

    public function getCliente(): Cliente {
        return $this->cliente;
    }

    public function getTickets(): array {
        return $this->tickets;
    }

    // Métodos

    public function agregarTicket(Ticket $ticket): void {
        $this->tickets[] = $ticket;
    }

    public function crearTicket(
        int $id,
        string $hora,
        string $fecha,
        string $codigoQR,
        Recorrido $recorrido
    ): Ticket {

        $ticket = new Ticket(
            $id,
            $hora,
            $fecha,
            $codigoQR,
            $recorrido
        );

        $this->agregarTicket($ticket);

        return $ticket;
    }

    public function mostrarCompra(): string {
        return "Compra #{$this->id} - Total: Bs. {$this->monto}";
    }

    public function getInfo(): array {
        return [
            'id' => $this->id,
            'fecha' => $this->fecha,
            'hora' => $this->hora,
            'monto' => $this->monto,
            'tickets' => count($this->tickets)
        ];
    }
}