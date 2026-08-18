<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');

$phpVersion = PHP_VERSION;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$time = date('Y-m-d H:i:s');
$appEnv = getenv('APP_ENV') ?: 'production';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon -->
    <link rel="icon" type="assets/image/png" href="assets/images/favicon.png">
    <link rel="shortcut icon" href="assets/images/favicon.png">

    <title>Portable PHP App</title>

    <style>
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; padding:24px; font-family:'Courier New',monospace; background:#05050c; color:#00ffcc; overflow:hidden; position:relative; }
        .bg-particles { position:absolute; inset:0; z-index:1; pointer-events:none; }
        .particle { position:absolute; background:rgba(0,255,204,.3); border-radius:50%; }
        .card { width:min(680px,100%); background:rgba(10,10,25,.85); border:1px solid #00ffcc; border-radius:18px; padding:32px; box-shadow:0 0 20px rgba(0,255,204,.2), inset 0 0 15px rgba(0,255,204,.1); backdrop-filter:blur(10px); z-index:2; opacity:0; transform:translateY(50px); }
        .brand { display:flex; justify-content:center; margin-bottom:20px; }
        .brand img { display:block; width:auto; max-width:260px; max-height:100px; object-fit:contain; }
        .ok { display:inline-block; padding:8px 16px; border-radius:4px; background:rgba(0,255,204,.1); border:1px solid #00ffcc; font-weight:700; letter-spacing:1px; margin-bottom:15px; }
        h1 { margin-top:0; font-size:1.8rem; letter-spacing:2px; }
        table { width:100%; border-collapse:collapse; margin-top:24px; }
        td { padding:16px 0; border-bottom:1px solid rgba(0,255,204,.2); }
        td:first-child { font-weight:700; width:40%; color:#ff00ff; }
        code { color:#ff00ff; }
    </style>
</head>
<body>
<div class="bg-particles" id="particles"></div>
<main class="card">
    <div class="brand">
        <img src="assets/images/logo.svg" alt="Logo" loading="eager">
    </div>

    <span class="ok">SYSTEM ONLINE</span>
    <h1>Portable PHP Application</h1>
    <p>Aplikasi PHP ini menggunakan <strong>public/</strong> sebagai document root.</p>
    <table>
        <tr><td>PHP Version</td><td><?= htmlspecialchars($phpVersion, ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td>Request Method</td><td><?= htmlspecialchars($method, ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td>Server Time</td><td><?= htmlspecialchars($time, ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td>APP_ENV</td><td><code><?= htmlspecialchars($appEnv, ENT_QUOTES, 'UTF-8') ?></code></td></tr>
    </table>
</main>
<script>
const container=document.getElementById('particles');
for(let i=0;i<40;i++){const p=document.createElement('div');p.className='particle';const s=Math.random()*6+2;p.style.width=s+'px';p.style.height=s+'px';p.style.left=Math.random()*100+'vw';p.style.top=Math.random()*100+'vh';container.appendChild(p)}
document.querySelector('.card').animate([{opacity:0,transform:'translateY(50px)'},{opacity:1,transform:'translateY(0)'}],{duration:1000,easing:'ease-out',fill:'forwards'});
</script>
</body>
</html>
