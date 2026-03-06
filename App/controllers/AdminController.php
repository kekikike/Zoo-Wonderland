<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Repositories\RecorridoRepository;
use App\Repositories\UsuarioRepository;

class AdminController
{
    private AuthService         $auth;
    private RecorridoRepository $recorridoRepo;
    private UsuarioRepository   $usuarioRepo;

    public function __construct()
    {
        $this->auth          = new AuthService();
        $this->recorridoRepo = new RecorridoRepository();
        $this->usuarioRepo   = new UsuarioRepository();
    }

    // ── Auth: redirige si no es admin ─────────────────────────────
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

    // ── Auth: lanza 403 para acciones POST ────────────────────────
    private function requireAdmin(): \App\Models\Usuario
    {
        if (!$this->auth->check()) {
            header('Location: index.php?r=login');
            exit;
        }
        $user = $this->auth->user();
        if (!$user || !$user->esAdministrador()) {
            http_response_code(403);
            require_once APP_PATH . '/Views/errors/403.php';
            exit;
        }
        return $user;
    }

    // ════════════════════════════════════════════════════════════
    // FUNCIONES EXISTENTES — sin cambios
    // ════════════════════════════════════════════════════════════

    public function dashboard(): void
    {
        $user = $this->checkAuth();

        $recorridos      = $this->recorridoRepo->findAll() ?? [];
        $totalRecorridos = count($recorridos);

        $db   = \Core\Database::getInstance();
        $conn = $db->getConnection();

        $areaResult     = $conn->query("SELECT COUNT(*) as total FROM areas");
        $totalAreas     = $areaResult ? $areaResult->fetch(\PDO::FETCH_ASSOC)['total'] : 0;

        $reservasResult = $conn->query("SELECT COUNT(*) as total FROM reservas WHERE estado = 1");
        $totalReservas  = $reservasResult ? $reservasResult->fetch(\PDO::FETCH_ASSOC)['total'] : 0;

        $ingresosResult = $conn->query("SELECT SUM(monto) as total FROM compras WHERE estado_pago = 1");
        $totalIngresos  = $ingresosResult ? ($ingresosResult->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0) : 0;

        $animalesResult = $conn->query("SELECT COUNT(*) as total FROM animales");
        $totalAnimales  = $animalesResult ? $animalesResult->fetch(\PDO::FETCH_ASSOC)['total'] : 0;

        $guiasResult = $conn->query("SELECT COUNT(*) as total FROM guias");
        $totalGuias  = $guiasResult ? $guiasResult->fetch(\PDO::FETCH_ASSOC)['total'] : 0;

        require APP_PATH . '/Views/admin/dashboard.php';
    }

    public function recorridos(): void
    {
        $user       = $this->checkAuth();
        $recorridos = $this->recorridoRepo->findAll() ?? [];
        require APP_PATH . '/Views/admin/recorridos.php';
    }

    // ════════════════════════════════════════════════════════════
    // NUEVAS FUNCIONES — gestión de usuarios (HU-10)
    // ════════════════════════════════════════════════════════════

    /**
     * Lista de usuarios con filtros.
     * El admin autenticado nunca aparece en la tabla.
     */
    public function usuarios(): void
    {
        $user = $this->checkAuth();

        $busqueda     = trim($_GET['busqueda']   ?? '');
        $rolFiltro    = trim($_GET['rol']        ?? '');
        $recorrido    = (int)($_GET['recorrido'] ?? 0);
        $estadoFiltro = trim($_GET['estado']     ?? '');

        $usuarios   = $this->usuarioRepo->getUsuariosFiltrados(
            $busqueda,
            $rolFiltro,
            $recorrido,
            $estadoFiltro,
            $user->id_usuario   // ← excluye al admin autenticado
        );
        $recorridos = $this->usuarioRepo->getRecorridosParaFiltro();

        $mensaje = $_SESSION['flash_mensaje'] ?? null;
        $tipoMsg = $_SESSION['flash_tipo']    ?? 'ok';
        unset($_SESSION['flash_mensaje'], $_SESSION['flash_tipo']);

        require APP_PATH . '/Views/admin/usuarios.php';
    }

    /**
     * Formulario de edición de usuario (GET).
     */
    public function editarUsuarioForm(): void
    {
        $user = $this->checkAuth();

        $id = (int)($_GET['id'] ?? 0);
        if ($id === 0) {
            header('Location: index.php?r=admin/usuarios');
            exit;
        }

        $usuarioEditar = $this->usuarioRepo->getUsuarioPorId($id);
        if (!$usuarioEditar) {
            header('Location: index.php?r=admin/usuarios');
            exit;
        }

        // Protección: el admin no puede editarse a sí mismo
        if ((int)$usuarioEditar['id_usuario'] === (int)$user->id_usuario) {
            $_SESSION['flash_mensaje'] = 'No puedes editarte a ti mismo desde este panel.';
            $_SESSION['flash_tipo']    = 'error';
            header('Location: index.php?r=admin/usuarios');
            exit;
        }

        $errores = $_SESSION['form_errores'] ?? [];
        $datos   = $_SESSION['form_datos']   ?? [];
        unset($_SESSION['form_errores'], $_SESSION['form_datos']);

        require APP_PATH . '/Views/admin/usuario_form.php';
    }

    /**
     * Procesa el formulario de edición de usuario (POST).
     */
    public function editarUsuarioPost(): void
    {
        $user = $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?r=admin/usuarios');
            exit;
        }

        $id             = (int)($_POST['id_usuario']     ?? 0);
        $nombre1        = trim($_POST['nombre1']         ?? '');
        $nombre2        = trim($_POST['nombre2']         ?? '');
        $apellido1      = trim($_POST['apellido1']       ?? '');
        $apellido2      = trim($_POST['apellido2']       ?? '');
        $ci             = (int)($_POST['ci']             ?? 0);
        $telefono       = trim($_POST['telefono']        ?? '');
        $rol            = trim($_POST['rol']             ?? '');
        $correo         = trim($_POST['correo']          ?? '');
        $nombre_usuario = trim($_POST['nombre_usuario']  ?? '');

        // Validaciones básicas
        $errores = [];
        if ($nombre1 === '')        $errores[] = 'El primer nombre es obligatorio.';
        if ($apellido1 === '')      $errores[] = 'El primer apellido es obligatorio.';
        if ($ci <= 0)               $errores[] = 'El CI debe ser un número positivo.';
        if ($correo === '')         $errores[] = 'El correo es obligatorio.';
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo no tiene un formato válido.';
        }
        if ($nombre_usuario === '') $errores[] = 'El nombre de usuario es obligatorio.';
        if (!in_array($rol, ['cliente', 'administrador', 'guia'], true)) {
            $errores[] = 'Rol inválido.';
        }

        if (!empty($errores)) {
            $_SESSION['form_errores'] = $errores;
            $_SESSION['form_datos']   = $_POST;
            header("Location: index.php?r=admin/usuario-editar&id={$id}");
            exit;
        }

        try {
            $ok = $this->usuarioRepo->actualizarUsuario(
                $id, $nombre1, $nombre2, $apellido1, $apellido2,
                $ci, $telefono, $rol, $correo, $nombre_usuario
            );

            $_SESSION['flash_mensaje'] = $ok
                ? 'Usuario actualizado correctamente.'
                : 'Error al actualizar el usuario.';
            $_SESSION['flash_tipo'] = $ok ? 'ok' : 'error';

        } catch (\Exception $e) {
            // Correo o nombre_usuario duplicado
            $_SESSION['form_errores'] = [$e->getMessage()];
            $_SESSION['form_datos']   = $_POST;
            header("Location: index.php?r=admin/usuario-editar&id={$id}");
            exit;
        }

        header('Location: index.php?r=admin/usuarios');
        exit;
    }

    /**
     * Activa o desactiva la cuenta de un usuario (POST).
     */
    public function toggleEstado(): void
    {
        $user = $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?r=admin/usuarios');
            exit;
        }

        $id     = (int)($_POST['id_usuario'] ?? 0);
        $estado = (int)($_POST['estado']     ?? 1);

        if ($id === 0) {
            header('Location: index.php?r=admin/usuarios');
            exit;
        }

        // No puede desactivarse a sí mismo
        if ((int)$user->id_usuario === $id) {
            $_SESSION['flash_mensaje'] = 'No puedes desactivar tu propia cuenta.';
            $_SESSION['flash_tipo']    = 'error';
            header('Location: index.php?r=admin/usuarios');
            exit;
        }

        $nuevoEstado = $estado === 1 ? 0 : 1;
        $ok = $this->usuarioRepo->cambiarEstado($id, $nuevoEstado);

        $_SESSION['flash_mensaje'] = $ok
            ? ($nuevoEstado === 1 ? 'Cuenta activada correctamente.' : 'Cuenta desactivada correctamente.')
            : 'Error al cambiar el estado.';
        $_SESSION['flash_tipo'] = $ok ? 'ok' : 'error';

        header('Location: index.php?r=admin/usuarios');
        exit;
    }
}