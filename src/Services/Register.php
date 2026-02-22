<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\UsuarioRepository;
use App\Models\Cliente;
use Exception;

class Register
{
    private UsuarioRepository $repository;

    public function __construct(UsuarioRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): array
    {
        $errorMessage = null;

        try {

            $required = [
                'nombre1',
                'apellido1',
                'correo',
                'telefono',
                'username',
                'password',
                'nit'
            ];

            foreach ($required as $field) {
                if (empty(trim($data[$field] ?? ''))) {
                    throw new Exception('Todos los campos obligatorios deben completarse.');
                }
            }

            if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('El correo electrónico no es válido.');
            }

            if (preg_match('/\s/', $data['username'])) {
                throw new Exception('El nombre de usuario no puede contener espacios.');
            }

            if ($this->repository->findByUsername($data['username'])) {
                throw new Exception('El nombre de usuario ya está en uso.');
            }

            $cliente = new Cliente(
                rand(1000, 9999),
                $data['nombre1'],
                $data['nombre2'] ?? '',
                $data['apellido1'],
                $data['apellido2'] ?? '',
                $data['correo'],
                $data['telefono'],
                $data['username'],
                $data['password'], // SIN doble hash
                (int)$data['nit'],
                'Normal'
            );

            $this->repository->add($cliente);

            return [
                'success' => true,
                'message' => 'Cliente registrado correctamente.'
            ];

        } catch (Exception $e) {

            $errorMessage = $e->getMessage();

            return [
                'success' => false,
                'message' => $errorMessage
            ];

        } finally {

            echo "Intento de registro con username: "
                . htmlspecialchars($data['username'] ?? '')
                . " - Resultado: "
                . ($errorMessage ?? 'Éxito')
                . "<br>";
        }
    }
}