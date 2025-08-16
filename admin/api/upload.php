<?php
// /admin/api/upload.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../guard.php';
require_csrf();

// Rutas ABSOLUTAS correctas (desde /admin/api/ hacia raíz del sitio)
$ROOT      = realpath(__DIR__ . '/../../'); // sube de /admin/api a /
$PDF_DIR   = $ROOT . '/assets/revistas';
$COVER_DIR = $ROOT . '/assets/revistas/portadas';
$JSON_FILE = $ROOT . '/revistas.json';

header('Content-Type: application/json; charset=utf-8');

function bail($msg, $code=400){
  http_response_code($code);
  echo json_encode(['ok'=>false,'error'=>$msg], JSON_UNESCAPED_UNICODE);
  exit;
}

// Crea carpetas si no existen
foreach ([$PDF_DIR, $COVER_DIR] as $d){
  if (!is_dir($d) && !mkdir($d, 0775, true)) bail('No se pudo crear carpeta destino', 500);
}

// Validación
if (!isset($_FILES['pdf']) || !isset($_FILES['thumb']) || !isset($_POST['meta'])) bail('Parámetros incompletos', 400);

$meta = json_decode($_POST['meta'], true);
if (!$meta) bail('Meta inválida', 400);

$slug = preg_replace('/[^a-z0-9\-]+/','', strtolower($meta['id'] ?? 'revista'));
if (!$slug) bail('ID inválido', 400);

// PDF
$pdf = $_FILES['pdf'];
if (($pdf['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) bail('Error subiendo PDF', 400);
$ext = strtolower(pathinfo($pdf['name'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') bail('Archivo PDF inválido', 400);

// Límite
$MAX_MB = 25;
if (($pdf['size'] ?? 0) > $MAX_MB * 1024 * 1024) bail("PDF demasiado grande (máx {$MAX_MB} MB)", 400);

// Miniatura
$thumb = $_FILES['thumb'];
if (($thumb['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) bail('Error subiendo miniatura', 400);

// Destinos
$pdfPath   = $PDF_DIR   . '/' . $slug . '.pdf';
$coverPath = $COVER_DIR . '/' . $slug . '.jpg';

// Guardar archivos
if (!move_uploaded_file($pdf['tmp_name'], $pdfPath)) bail('No se pudo guardar PDF', 500);
if (!move_uploaded_file($thumb['tmp_name'], $coverPath)) bail('No se pudo guardar miniatura', 500);

// (Opcional) Compresión con Ghostscript si existe
$gs = trim((string)@shell_exec('which gs'));
if ($gs){
  $tmpOut = $PDF_DIR . '/' . $slug . '.tmp.pdf';
  $cmd = $gs . ' -sDEVICE=pdfwrite -dCompatibilityLevel=1.6 -dPDFSETTINGS=/ebook -dDownsampleColorImages=true -dColorImageResolution=150 -dNOPAUSE -dQUIET -dBATCH '
       . '-sOutputFile=' . escapeshellarg($tmpOut) . ' ' . escapeshellarg($pdfPath);
  @exec($cmd, $o, $rc);
  if ($rc === 0 && is_file($tmpOut) && filesize($tmpOut) > 0){
    @rename($tmpOut, $pdfPath);
  } else {
    @unlink($tmpOut);
  }
}

// Leer JSON maestro
$items = [];
if (is_file($JSON_FILE)) {
  $raw = file_get_contents($JSON_FILE);
  $items = json_decode($raw, true);
  if (!is_array($items)) $items = [];
}

$item = [
  'id'     => $slug,
  'titulo' => $meta['titulo'] ?? $slug,
  'fecha'  => $meta['fecha'] ?? date('Y-m-01'),
  // OJO: Rutas públicas (relativas a la raíz del sitio)
  'pdf'    => 'assets/revistas/'.$slug.'.pdf',
  'cover'  => 'assets/revistas/portadas/'.$slug.'.jpg',
  'tags'   => (isset($meta['tags']) && is_array($meta['tags'])) ? $meta['tags'] : []
];

// Insertar/Reemplazar por id
$replaced = false;
foreach($items as $i => $it){
  if (($it['id'] ?? '') === $slug) { $items[$i] = $item; $replaced = true; break; }
}
if (!$replaced) array_unshift($items, $item);

// Guardar JSON con bloqueo para evitar “perdidas” por concurrencia
$fh = @fopen($JSON_FILE, 'c+'); // crea si no existe
if (!$fh) bail('No se pudo abrir revistas.json', 500);
flock($fh, LOCK_EX);
ftruncate($fh, 0);
fwrite($fh, json_encode($items, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
fflush($fh);
flock($fh, LOCK_UN);
fclose($fh);

// Permisos amables (opcional)
@chmod($JSON_FILE, 0664);

echo json_encode(['ok'=>true, 'item'=>$item], JSON_UNESCAPED_UNICODE);
