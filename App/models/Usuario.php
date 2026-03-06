<?php
// app/Models/Usuario.php
declare(strict_types=1);

namespace App\Models;

class Usuario
{
    public int $id_usuario;
    public string $nombre1;
    public string $nombre2;
    public string $apellido1;
    public string $apellido2;
    public ?int $ci;
    public string $correo;
    public string $telefono;
    public string $nombre_usuario;
    public string $rol;  // 'cliente', 'administrador', 'guia'

    // No guardamos password aquí (seguridad)

    public function __construct(array $data)
    {
        $this->id_usuario     = (int) ($data['id_usuario'] ?? 0);
        $this->nombre1        = $data['nombre1'] ?? '';
        $this->nombre2        = $data['nombre2'] ?? '';
        $this->apellido1      = $data['apellido1'] ?? '';
        $this->apellido2      = $data['apellido2'] ?? '';
        $this->ci             = isset($data['ci']) ? (int)$data['ci'] : null;
        $this->correo         = $data['correo'] ?? '';
        $this->telefono       = $data['telefono'] ?? '';
        $this->nombre_usuario = $data['nombre_usuario'] ?? '';
        $this->rol            = $data['rol'] ?? 'cliente';
    }

    public function getNombreCompleto(): string
    {
        $partes = array_filter([$this->nombre1, $this->nombre2, $this->apellido1, $this->apellido2]);
        return implode(' ', $partes) ?: $this->nombre_usuario;
    }

    public function getNombreParaMostrar(): string
    {
        return $this->getNombreCompleto() ?: $this->nombre_usuario;
    }

    public function esCliente(): bool
    {
        return $this->rol === 'cliente';
    }

    public function esGuia(): bool
{
    return $this->rol === 'guia';
}

    public function esAdministrador(): bool
    {
        return $this->rol === 'administrador';
    }


   
}