<?php
namespace App\Models;
//Clase base
abstract class Usuario
{
    protected int $id;
    protected string $nombre1;
    protected string $nombre2;
    protected string $apellido1;
    protected string $apellido2;
    protected string $correo;
    protected string $telefono;
    protected string $nombreUsuario;
    protected string $password;

    public function __construct(
        int $id,
        string $nombre1,
        string $nombre2,
        string $apellido1,
        string $apellido2,
        string $correo,
        string $telefono,
        string $nombreUsuario,
        string $password
    ) {
        $this->id = $id;
        $this->nombre1 = $nombre1;
        $this->nombre2 = $nombre2;
        $this->apellido1 = $apellido1;
        $this->apellido2 = $apellido2;
        $this->correo = $correo;
        $this->telefono = $telefono;
        $this->nombreUsuario = $nombreUsuario;
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNombreCompleto(): string
    {
        return "$this->nombre1 $this->nombre2 $this->apellido1 $this->apellido2";
    }

    public function getUsuario(): string
    {
        return $this->nombreUsuario;
    }

    public function verificarPassword(string $pass): bool
    {
        return password_verify($pass, $this->password);
    }

    public function login(string $usuario, string $password): bool
    {
        return $this->nombreUsuario === $usuario &&
               $this->verificarPassword($password);
    }

    public function logout(): void
    {
        session_destroy();
    }
}

//Clases hijas

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