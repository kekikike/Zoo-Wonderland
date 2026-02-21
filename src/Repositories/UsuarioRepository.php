<?php
declare(strict_types=1);

namespace App\Repositories;


use App\Models\Cliente;
use App\Models\Administrador;
use App\Models\Guia;

/**
 * Repositorio de usuarios (simulado en memoria)
 */
class UsuarioRepository
{
    private array $usuarios = [];
    private int $nextId = 1;

    public function __construct()
    {
        $this->seedData();
    }

    /**
     * Datos de prueba
     */
    private function seedData(): void
    {
        $datos = [
            // Clientes
            ['cliente', 'Juan', 'Carlos', 'Perez', 'Lopez', 'juan@mail.com', '78945612', 'juan123', '1234', 123456, 'Normal'],
            ['cliente', 'Maria', 'Elena', 'Gomez', 'Rojas', 'maria@mail.com', '71234567', 'maria23', '1234', 456789, 'Premium'],

            // Admin
            ['admin', 'Pedro', 'Luis', 'Mamani', 'Quispe', 'admin@mail.com', '70123456', 'admin', 'admin123', null, null],

            // Guía
            ['guia', 'Luis', 'Andres', 'Condori', 'Flores', 'guia@mail.com', '79874561', 'guia01', '1234', null, null],
        ];

        foreach ($datos as $data) {

            [
                $tipo,
                $n1,
                $n2,
                $a1,
                $a2,
                $correo,
                $tel,
                $user,
                $pass,
                $nit,
                $tipoCuenta
            ] = $data;

            switch ($tipo) {

                case 'cliente':
                    $usuario = new Cliente(
                        $this->nextId++,
                        $n1,
                        $n2,
                        $a1,
                        $a2,
                        $correo,
                        $tel,
                        $user,
                        $pass,
                        $nit,
                        $tipoCuenta
                    );
                    break;

                case 'admin':
                    $usuario = new Administrador(
                        $this->nextId++,
                        $n1,
                        $n2,
                        $a1,
                        $a2,
                        $correo,
                        $tel,
                        $user,
                        $pass
                    );
                    break;

                case 'guia':
                    $usuario = new Guia(
                        $this->nextId++,
                        $n1,
                        $n2,
                        $a1,
                        $a2,
                        $correo,
                        $tel,
                        $user,
                        $pass
                    );
                    break;
            }

            $this->usuarios[$usuario->getId()] = $usuario;
        }
    }

    /**
     * Obtiene todos los usuarios
     */
    public function findAll(): array
    {
        return array_values($this->usuarios);
    }

    /**
     * Busca por ID
     */
    public function findById(int $id)
    {
        return $this->usuarios[$id] ?? null;
    }

    /**
     * Busca por nombre de usuario
     */
    public function findByUsername(string $username)
    {
        return array_values(array_filter(
            $this->usuarios,
            fn($u) => $u->getUsuario() === $username
        ))[0] ?? null;
    }

    /**
     * Login
     */
    public function login(string $user, string $pass): bool
    {
        $usuario = $this->findByUsername($user);

        return $usuario && $usuario->verificarPassword($pass);
    }

    /**
     * Búsqueda por nombre
     */
    public function search(string $query): array
    {
        return array_filter($this->usuarios, function ($u) use ($query) {
            return str_contains(
                strtolower($u->getNombreCompleto()),
                strtolower($query)
            );
        });
    }
}