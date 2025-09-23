<?php
// save-home-highlights.php
// Seguridad básica: SOLO POST con JSON desde el mismo dominio
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido'); }
$raw = file_get_contents('php://input');
if (!$raw) { http_response_code(400); exit('Payload vacío'); }

$data = json_decode($raw, true);
if (!$data) { http_response_code(400); exit('JSON inválido'); }

// Validación mínima
function str_ok($s){ return is_string($s) ? trim($s) : ''; }
function url_ok($u){ return filter_var($u, FILTER_VALIDATE_URL) || preg_match('~^(assets/)~', $u); }

$data['magazine']['auto']  = !empty($data['magazine']['auto']);
$data['magazine']['cover'] = str_ok($data['magazine']['cover']);
$data['magazine']['doc']   = str_ok($data['magazine']['doc']);
$data['magazine']['title'] = str_ok($data['magazine']['title']);
$data['magazine']['desc']  = str_ok($data['magazine']['desc']);

$data['youtube']['url']    = str_ok($data['youtube']['url']);
$data['youtube']['title']  = str_ok($data['youtube']['title']);
$data['youtube']['desc']   = str_ok($data['youtube']['desc']);

$data['podcast']['url']    = str_ok($data['podcast']['url']);
$data['podcast']['title']  = str_ok($data['podcast']['title']);
$data['podcast']['desc']   = str_ok($data['podcast']['desc']);

// (Opcional) más validaciones de URL
// if ($data['youtube']['url'] && !url_ok($data['youtube']['url'])) { http_response_code(400); exit('URL YouTube inválida'); }

$target = __DIR__ . '/../assets/data/home-highlights.json';

// Asegurar carpeta
@mkdir(dirname($target), 0775, true);

// Guardar bonito
$json = json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
if (file_put_contents($target, $json) === false) {
  http_response_code(500); exit('No se pudo escribir el archivo');
}

header('Content-Type: text/plain; charset=utf-8');
echo 'OK';
