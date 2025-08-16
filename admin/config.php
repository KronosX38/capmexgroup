<?php
// /admin/config.php
declare(strict_types=1);

session_name('capmex_admin');
session_start([
  'cookie_httponly' => true,
  'cookie_samesite' => 'Lax',
]);

// --- CREDENCIALES ---
// Genera un hash para tu contraseña con: password_hash('TU_PASSWORD', PASSWORD_DEFAULT)
// PÉGALO abajo:
const ADMIN_USER = 'admin';
const ADMIN_PASS_HASH = '$2y$10$x3qBsBd48OHMfMYrP5CvB.h8NrkOxAOLEu49KaS/B2rYHeJE4XeHS'; // <-- EJEMPLO, reemplaza

// CSRF helper
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
function require_csrf(): void {
  $hdr = $_SERVER['HTTP_X_CSRF'] ?? '';
  if (!$hdr || !hash_equals($_SESSION['csrf'], $hdr)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok'=>false,'error'=>'CSRF inválido']);
    exit;
  }
}
