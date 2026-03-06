<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Repositories\CompraRepository;
use App\Repositories\ReservaRepository;
use App\Repositories\RecorridoRepository;
use App\Models\Reserva;
use App\Models\Recorrido;

class UsuarioController
{
    private AuthService $auth;
    private CompraRepository $compraRepo;
    private ReservaRepository $reservaRepo;
    private RecorridoRepository $recorridoRepo;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->compraRepo = new CompraRepository();
        $this->reservaRepo = new ReservaRepository();
        $this->recorridoRepo = new RecorridoRepository();
    }

    public function historial(): void
    {
        if (!$this->auth->check()) {
            header('Location: index.php?r=login');
            exit;
        }

        $usuario = $this->auth->user();
        $cliente = $this->compraRepo->findClienteByUsuario($usuario->id_usuario);
        $clienteId = $cliente['id_cliente'] ?? 0;

        // Obtener Tickets (Compras)
        $compras = $this->compraRepo->findByCliente($clienteId);

        // Obtener Reservas Grupales
        $reservasRaw = $this->reservaRepo->findAllWithExtras($clienteId);
        
        $reservas = [];
        foreach ($reservasRaw as $item) {
            $r = $item['reserva'];
            $e = $item['extras'];
            
            $recorridoData = $this->recorridoRepo->findById((int)$r['id_recorrido']);
            if ($recorridoData) {
                $recorrido = new Recorrido(
                    (int)($recorridoData['id_recorrido'] ?? $recorridoData['id']),
                    (string)$recorridoData['nombre'],
                    (string)$recorridoData['tipo'],
                    (float)$recorridoData['precio'],
                    (int)$recorridoData['duracion'],
                    (int)$recorridoData['capacidad']
                );
            } else {
                $recorrido = new Recorrido(0, 'Desconocido', '', 0, 0, 0);
            }
            
            $reservaObj = new Reserva(
                (int)$r['id_reserva'],
                (string)$r['hora'],
                (string)$r['fecha'],
                (int)$r['cupos'],
                (string)$r['institucion'],
                $recorrido
            );

            // Asegurar monto_total si falta en sesión
            if (!isset($e['monto_total']) || empty($e['monto_total'])) {
                $e['monto_total'] = $recorrido->getPrecio() * $reservaObj->getCupos();
            }
            
            $reservas[] = ['reserva' => $reservaObj, 'extras' => $e];
        }

        $isLoggedIn = true;
        $user = $usuario;

        require APP_PATH . '/views/usuarios/historial.php';
    }
}
