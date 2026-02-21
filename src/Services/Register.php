<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\UsuarioRepository;

class Register
{
    private UsuarioRepository $repository;

    public function __construct(UsuarioRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): array
    {
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
                return [
                    'success' => false,
                    'message' => 'Todos los campos obligatorios deben completarse.'
                ];
            }
        }

        if (preg_match('/\s/', $data['username'])) {
            return [
                'success' => false,
                'message' => 'El nombre de usuario no puede contener espacios.'
            ];
        }

        if ($this->repository->findByUsername($data['username'])) {
            return [
                'success' => false,
                'message' => 'El nombre de usuario ya está en uso.'
            ];
        }

        $cliente = new \App\Models\Cliente(
            rand(1000, 9999),
            $data['nombre1'],
            $data['nombre2'] ?? '',
            $data['apellido1'],
            $data['apellido2'] ?? '',
            $data['correo'],
            $data['telefono'],
            $data['username'],
            $data['password'],
            (int)$data['nit'],
            'Normal'
        );

        $this->repository->add($cliente);

        return [
            'success' => true,
            'message' => 'Cliente registrado correctamente.'
        ];
    }
}