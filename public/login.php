<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\UsuarioRepository;

$repo = new UsuarioRepository();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // VALIDACIONES BACKEND
    if (empty($username) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (preg_match('/\s/', $username)) {
        $error = "El nombre de usuario no puede contener espacios.";
    } else {

        $usuario = $repo->findByUsername($username);

        if ($usuario && $usuario->verificarPassword($password)) {

            // Guardamos el objeto usuario en sesión
            $_SESSION['usuario'] = $usuario;

            header("Location: index.php");
            exit;

        } else {
            $error = "Credenciales incorrectas.";
        }
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
    font-family: Arial, sans-serif;
    background-color: #fee2a0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.login-container {
    background-color: #967b5d;
    padding: 40px;
    border-radius: 12px;
    width: 350px;
    box-shadow: 0 0 15px rgba(0,0,0,0.2);
    text-align: center;
}

h1 {
    color: #68672e;
    margin-bottom: 5px;
}

h2 {
    color: #bfb641;
    margin-bottom: 25px;
}

input {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 2px solid #a3712a;
    border-radius: 6px;
    outline: none;
}

input:focus {
    border-color: #7eaeb0;
}

button {
    width: 100%;
    padding: 10px;
    background-color: #68672e;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

button:hover {
    background-color: #a3712a;
}

.error {
    color: red;
    margin-bottom: 10px;
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
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validarFormulario();">
        <input type="text" name="username" id="username" placeholder="Nombre de usuario" required>
        <input type="password" name="password" id="password" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button>
    </form>
</div>

</body>
</html>