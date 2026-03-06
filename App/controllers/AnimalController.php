<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Repositories\AnimalRepository;
use App\Repositories\AreaRepository;

class AnimalController
{
    private AuthService $auth;
    private AnimalRepository $repo;
    private AreaRepository $areaRepo;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->repo  = new AnimalRepository();
        $this->areaRepo = new AreaRepository();
    }

    /**
     * Verifica que el usuario esté logueado y sea administrador.
     * Si no lo está, redirige y termina la ejecución.
     *
     * @return \App\Models\Usuario
     */
    private function checkAuth(): \App\Models\Usuario
    {
        if (!$this->auth->check()) {
            header('Location: index.php?r=login');
            exit;
        }

        $user = $this->auth->user();
        if (!$user || !$user->esAdministrador()) {
            header('Location: index.php');
            exit;
        }

        return $user;
    }

    /* ---------- acciones CRUD ---------- */

    public function index(): void
    {
        $this->checkAuth();
        
        // capturar búsqueda y filtro de área
        $searchQuery = trim($_GET['q'] ?? '');
        $areaFilter  = (int)($_GET['area'] ?? 0);

        if ($searchQuery !== '' && $areaFilter > 0) {
            $animales = $this->repo->search($searchQuery, $areaFilter);
        } elseif ($searchQuery !== '') {
            $animales = $this->repo->search($searchQuery);
        } elseif ($areaFilter > 0) {
            $animales = $this->repo->findByArea($areaFilter);
        } else {
            $animales = $this->repo->findAll();
        }
        
        // también necesitamos la lista de áreas para el filtro
        $areas = $this->areaRepo->findAll();

        require APP_PATH . '/Views/admin/animales.php';
    }

    public function crear(): void
    {
        $this->checkAuth();
        $animal = null; // indica creación
        $action = 'guardar';
        $areas  = $this->areaRepo->findAll();
        require APP_PATH . '/Views/admin/animal_form.php';
    }

    public function guardar(): void
    {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?r=admin/animales');
            exit;
        }

        $especie     = trim($_POST['especie'] ?? '');
        $nombre      = trim($_POST['nombre'] ?? '');
        $habitat     = trim($_POST['habitat'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $estado      = trim($_POST['estado'] ?? 'Activo');
        $areaId      = (int)($_POST['area_id'] ?? 0);
        // si se seleccionó un área, verificar existencia
        if ($areaId > 0 && !$this->areaRepo->findById($areaId)) {
            $areaId = 0; //ignorar si no existe
        }

        // validación mínima
        if ($especie === '' || $habitat === '' || $descripcion === '') {
            // volver al formulario con errores simples (no se implementa flash)
            $_SESSION['form_errors'] = 'Especie, hábitat y descripción son obligatorios.';
            header('Location: index.php?r=admin/animales/crear');
            exit;
        }

        $this->repo->create($especie, $nombre, $habitat, $descripcion, $estado, $areaId);
        header('Location: index.php?r=admin/animales');
        exit;
    }

    public function editar(): void
    {
        $this->checkAuth();
        $id = (int)($_GET['id'] ?? 0);
        $animal = $this->repo->findById($id);
        if (!$animal) {
            header('Location: index.php?r=admin/animales');
            exit;
        }
        $action = 'actualizar&id=' . $id;
        $areas  = $this->areaRepo->findAll();
        require APP_PATH . '/Views/admin/animal_form.php';
    }

    public function actualizar(): void
    {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?r=admin/animales');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        // verificar existencia
        $animal = $this->repo->findById($id);
        if (!$animal) {
            header('Location: index.php?r=admin/animales');
            exit;
        }

        $areaId = (int)($_POST['area_id'] ?? 0);
        if ($areaId > 0 && !$this->areaRepo->findById($areaId)) {
            $areaId = 0;
        }

        $data = [
            'especie'     => trim($_POST['especie'] ?? ''),
            'nombre'      => trim($_POST['nombre'] ?? ''),
            'habitat'     => trim($_POST['habitat'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'estado'      => trim($_POST['estado'] ?? ''),
            'areaId'      => $areaId,
        ];

        $this->repo->update($id, $data);
        header('Location: index.php?r=admin/animales');
        exit;
    }

    public function eliminar(): void
    {
        $this->checkAuth();
        $id = (int)($_GET['id'] ?? 0);
        $this->repo->delete($id);
        header('Location: index.php?r=admin/animales');
        exit;
    }
}
