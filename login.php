<?php
require_once 'config.php';
session_start();

if (!empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['usuario'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($user === AUTH_USER && $pass === AUTH_PASS) {
        $_SESSION['logged_in'] = true;
        $_SESSION['usuario']   = $user;
        header('Location: index.php');
        exit;
    }
    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ManageTracking - Iniciar sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-sm">

    <!-- Logo -->
    <div class="flex flex-col items-center mb-8">
        <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold">ManageTracking</h1>
        <p class="text-gray-400 text-sm mt-1">Panel de control</p>
    </div>

    <!-- Card -->
    <div class="bg-gray-800 rounded-2xl border border-gray-700 p-6 shadow-2xl">
        <h2 class="text-lg font-semibold mb-5">Iniciar sesión</h2>

        <?php if ($error): ?>
            <div class="bg-red-900/40 border border-red-700 text-red-300 text-sm px-4 py-3 rounded-lg mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs text-gray-400 mb-1">Usuario</label>
                <input name="usuario" type="text" autocomplete="username"
                       value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500 transition"
                       placeholder="admin" required autofocus>
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Contraseña</label>
                <input name="password" type="password" autocomplete="current-password"
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500 transition"
                       placeholder="••••••••" required>
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white py-2 rounded-lg text-sm font-medium transition mt-2">
                Entrar
            </button>
        </form>
    </div>
</div>

</body>
</html>
