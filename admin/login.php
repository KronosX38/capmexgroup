<?php
// /admin/login.php
require_once __DIR__ . '/config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user = $_POST['user'] ?? '';
  $pass = $_POST['pass'] ?? '';
  if (hash_equals(ADMIN_USER, $user) && password_verify($pass, ADMIN_PASS_HASH)) {
    $_SESSION['auth'] = true;
    // refresca token CSRF por seguridad
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    header('Location: ./index.php');
    exit;
  } else {
    $error = 'Usuario o contraseña incorrectos';
  }
}

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ingreso | CAPMEX Admin</title>
  <style>
    :root{--bg:#0f0f10;--panel:#161617;--txt:#eaeaea;--muted:#a0a0a0;--accent:#ff9f43}
    *{box-sizing:border-box} html,body{height:100%}
    body{margin:0;background:var(--bg);color:var(--txt);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;display:grid;place-items:center}
    .card{background:var(--panel);border:1px solid #2a2a2c;border-radius:14px;padding:22px;min-width:min(96vw,360px);box-shadow:0 10px 30px rgba(0,0,0,.4)}
    h1{margin:0 0 12px 0;font-size:20px}
    .row{display:flex;flex-direction:column;gap:8px;margin:10px 0}
    input{width:100%;background:#1f1f21;color:var(--txt);border:1px solid #2a2a2c;border-radius:10px;padding:10px 12px}
    button{width:100%;background:var(--accent);color:#111;border:0;border-radius:10px;padding:10px 12px;font-weight:600;cursor:pointer}
    .err{color:#ff6b6b;font-size:14px;margin-top:6px;min-height:18px}
    .muted{color:var(--muted);font-size:12px;margin-top:10px}
  </style>
</head>
<body>
  <form class="card" method="post" action="">
    <h1>Iniciar Sesión - CAPMEX</h1>
    <div class="row">
      <label>Usuario</label>
      <input name="user" autocomplete="username" required>
    </div>
    <div class="row">
      <label>Contraseña</label>
      <input name="pass" type="password" autocomplete="current-password" required>
    </div>
    <button type="submit">Entrar</button>
    <div class="err"><?= htmlspecialchars($error ?: '', ENT_QUOTES, 'UTF-8') ?></div>
    <div class="muted">Acceso restringido. Si olvidaste tus datos, contacta al administrador.</div>
  </form>
</body>
</html>
