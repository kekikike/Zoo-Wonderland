<?php
namespace App\Models;
class Cliente extends Usuario
{
    private int $nit;
    private string $tipoCuenta;

    public function __construct(
        int $id,
        string $nombre1,
        string $nombre2,
        string $apellido1,
        string $apellido2,
        string $correo,
        string $telefono,
        string $usuario,
        string $password,
        int $nit,
        string $tipoCuenta
    ) {
        parent::__construct(
            $id,
            $nombre1,
            $nombre2,
            $apellido1,
            $apellido2,
            $correo,
            $telefono,
            $usuario,
            $password
        );

        $this->nit = $nit;
        $this->tipoCuenta = $tipoCuenta;
    }

    public function comprarEntrada(): string
    {
        return "Entrada comprada";
    }
}
?>