<?php
require_once __DIR__ . '/config.php';
if (empty($_SESSION['auth']) || $_SESSION['auth'] !== true) {
  $base = rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); // ej: /admin
  header('Location: ' . $base . '/login.php');
  exit;
}
