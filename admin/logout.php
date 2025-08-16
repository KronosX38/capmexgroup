<?php
require_once __DIR__ . '/config.php';

// Destruir sesión (servidor)
$_SESSION = [];
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"] ?? false, $params["httponly"] ?? true);
}
session_destroy();

// Redirigir SIEMPRE a login.php con ruta correcta (funciona en subcarpetas)
$base = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');  // ej: /admin o /sitio/admin
header('Location: ' . $base . '/login.php');
exit;
