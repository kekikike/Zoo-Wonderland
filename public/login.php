<?php
declare(strict_types=1);


require_once __DIR__ . '/../vendor/autoload.php';
session_start();

use App\Repositories\UsuarioRepository;

$repo = new UsuarioRepository();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            throw new Exception("Todos los campos son obligatorios.");
        }

        if (preg_match('/\s/', $username)) {
            throw new Exception("El nombre de usuario no puede contener espacios.");
        }

        $usuario = $repo->findByUsername($username);

        if (!$usuario || !$usuario->verificarPassword($password)) {
            throw new Exception("Credenciales incorrectas.");
        }

        $_SESSION['usuario'] = $usuario;

        header("Location: index.php");
        exit;

    } catch (Exception $e) {

        $error = $e->getMessage();
    } finally {        
        echo "Intento de login con username: " . htmlspecialchars($username) . " - Resultado: " . ($error ? "Error: " . $error : "Éxito") . "\n";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Zoo Wonderland - Login</title>

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(135deg, #fee2a0, #f6c667);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.login-container {
    background-color: #ffffff;
    padding: 45px;
    border-radius: 16px;
    width: 360px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    text-align: center;
    transition: transform 0.2s ease;
}

.login-container:hover {
    transform: translateY(-3px);
}

h1 {
    color: #68672e;
    margin-bottom: 8px;
    font-size: 24px;
}

h2 {
    color: #a3712a;
    margin-bottom: 25px;
    font-weight: normal;
    font-size: 16px;
}

input {
    width: 100%;
    padding: 12px;
    margin-bottom: 18px;
    border: 1.8px solid #d4b483;
    border-radius: 8px;
    outline: none;
    transition: all 0.2s ease;
    font-size: 14px;
}

input:focus {
    border-color: #7eaeb0;
    box-shadow: 0 0 5px rgba(126,174,176,0.4);
}

button {
    width: 100%;
    padding: 12px;
    background-color: #68672e;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    transition: all 0.2s ease;
}

button:hover {
    background-color: #a3712a;
    transform: translateY(-2px);
}

.error {
    background-color: #ffe5e5;
    color: #b30000;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 14px;
}
</style>

<script>
function validarFormulario() {

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    if (username.trim() === "" || password.trim() === "") {
        alert("Todos los campos son obligatorios.");
        return false;
    }

    if (username.includes(" ")) {
        alert("El nombre de usuario no puede contener espacios.");
        return false;
    }

    if (password.length < 4) {
        alert("La contraseña debe tener al menos 4 caracteres.");
        return false;
    }

    return true;
}
</script>

</head>
<body>

<div class="login-container">
    <h1>Zoo Wonderland</h1>
    <h2>Ingreso de Cliente</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validarFormulario();">
        <input type="text" name="username" id="username" placeholder="Nombre de usuario" required>
        <input type="password" name="password" id="password" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button>
    </form>
</div>

</body>
</html>