<?php
namespace App\Models;
class Guia extends Usuario
{
    private array $horarios = [];
    private array $diasTrabajo = [];

    public function setHorario(string $hora): void
    {
        $this->horarios[] = $hora;
    }

    public function verHorarios(): array
    {
        return $this->horarios;
    }
}
?>