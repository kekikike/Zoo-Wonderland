<?php
namespace App\Models;
class Administrador extends Usuario
{
    public function gestionarUsuarios(): string
    {
        return "Usuarios gestionados";
    }

    public function generarReportes(): string
    {
        return "Reporte generado";
    }
}

?>