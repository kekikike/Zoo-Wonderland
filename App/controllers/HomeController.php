<?php
// app/Controllers/HomeController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Repositories\RecorridoRepository;

class HomeController
{
    private AuthService $auth;
    private RecorridoRepository $recorridoRepo;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->recorridoRepo = new RecorridoRepository();
    }


    public function index(): void
{
    $isLoggedIn = $this->auth->check();
    $user       = $isLoggedIn ? $this->auth->user() : null;
    $esCliente  = $isLoggedIn && $user && $user->esCliente();
    $esGuia     = $isLoggedIn && $user && $user->esGuia();   // ← NUEVO

    $recorridos = $this->recorridoRepo->findAll();

    require_once APP_PATH . '/Views/home.php';
}
}