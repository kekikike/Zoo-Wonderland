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

            // Validar nombres y apellidos (solo letras y espacios)
            $soloLetras = '/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/';

            foreach (['nombre1', 'nombre2', 'apellido1', 'apellido2'] as $campo) {
                if (!empty($data[$campo]) && !preg_match($soloLetras, $data[$campo])) {
                    throw new Exception("El campo {$campo} no debe contener números ni caracteres inválidos.");
                }
            }

            // Validar correo
            if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('El correo electrónico no es válido.');
            }

            // Validar username sin espacios
            if (preg_match('/\s/', $data['username'])) {
                throw new Exception('El nombre de usuario no puede contener espacios.');
            }

            // Validar teléfono (7 u 8 dígitos)
            if (!preg_match('/^\d{7,8}$/', $data['telefono'])) {
                throw new Exception('El teléfono debe tener entre 7 y 8 dígitos numéricos.');
            }

            // Validar CI/NIT (6 o 7 dígitos)
            if (!preg_match('/^\d{6,7}$/', $data['nit'])) {
                throw new Exception('El CI/NIT debe tener entre 6 y 7 dígitos numéricos.');
            }

            // Validar contraseña mínima 4 caracteres
            if (strlen($data['password']) < 4) {
                throw new Exception('La contraseña debe tener mínimo 4 caracteres.');
            }

            // Verificar username existente
            if ($this->repository->findByUsername($data['username'])) {
                throw new Exception('El nombre de usuario ya está en uso.');
            }

            // Crear el cliente
            $cliente = new Cliente(
                rand(1000, 9999),                   
                trim($data['nombre1']),
                trim($data['nombre2'] ?? ''),
                trim($data['apellido1']),
                trim($data['apellido2'] ?? ''),
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

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}