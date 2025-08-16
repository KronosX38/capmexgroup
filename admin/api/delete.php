<?php
// /admin/api/delete.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../guard.php';
require_csrf();

header('Content-Type: application/json; charset=utf-8');

// Rutas ABSOLUTAS correctas
$ROOT      = realpath(__DIR__ . '/../../');
$PDF_DIR   = $ROOT . '/assets/revistas';
$COVER_DIR = $ROOT . '/assets/revistas/portadas';
$JSON_FILE = $ROOT . '/revistas.json';

function bail($msg, $code=400){
  http_response_code($code);
  echo json_encode(['ok'=>false,'error'=>$msg], JSON_UNESCAPED_UNICODE);
  exit;
}

// Leer entrada JSON
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true);
if (!$in || !isset($in['id'])) bail('Parámetros incompletos', 400);

$slug = preg_replace('/[^a-z0-9\-]+/','', strtolower($in['id']));
if (!$slug) bail('ID inválido', 400);

// Leer JSON
$items = [];
if (is_file($JSON_FILE)) {
  $rawJs = file_get_contents($JSON_FILE);
  $items = json_decode($rawJs, true);
  if (!is_array($items)) $items = [];
}

$found = false;
foreach($items as $i => $it){
  if (($it['id'] ?? '') === $slug){
    $found = true;

    // Eliminar archivos físicos
    $pdfPath   = $PDF_DIR   . '/' . $slug . '.pdf';
    $coverPath = $COVER_DIR . '/' . $slug . '.jpg';
    if (is_file($pdfPath))   @unlink($pdfPath);
    if (is_file($coverPath)) @unlink($coverPath);

    // Quitar del array
    array_splice($items, $i, 1);
    break;
  }
}
if (!$found) bail('No se encontró la revista con ese id', 404);

// Guardar JSON con bloqueo
$fh = @fopen($JSON_FILE, 'c+');
if (!$fh) bail('No se pudo abrir revistas.json', 500);
flock($fh, LOCK_EX);
ftruncate($fh, 0);
fwrite($fh, json_encode($items, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
fflush($fh);
flock($fh, LOCK_UN);
fclose($fh);

echo json_encode(['ok'=>true]);
