<?php
// index.php - Página principal / Router y Dashboard completo (versión extendida)
// Mantiene nombres de archivos y rutas existentes (register.php, login.php, create_post.php, upload_file.php, book_appointment.php, logout.php, conexion.php, csrf.php)
// Sidebar fijo en pantallas grandes y deslizante en móviles.
// Tema: opción 2 (estilo moderno profesional). No se cambian nombres de rutas ni clases usadas por el backend.

session_start();
require 'conexion.php';
require 'csrf.php';

// Obtener usuario actual si hay sesión
$currentUser = null;
if (!empty($_SESSION['user_id'])) {
    $stmt = $mysqli->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $currentUser = $res->fetch_assoc();
    $stmt->close();
}

// Obtener posts para sección INICIO (últimos 50)
$posts = [];
$res = $mysqli->query("SELECT p.*, u.name FROM posts p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 50");
if ($res) {
    while ($r = $res->fetch_assoc()) $posts[] = $r;
}

// Obtener archivos (últimos 50)
$files = [];
$res = $mysqli->query("SELECT f.*, u.name FROM files f LEFT JOIN users u ON f.uploaded_by = u.id ORDER BY f.uploaded_at DESC LIMIT 50");
if ($res) {
    while ($r = $res->fetch_assoc()) $files[] = $r;
}

// Obtener citas próximas (limit 100)
$appts = [];
$res = $mysqli->query("SELECT a.*, u.name FROM appointments a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.date, a.time_slot LIMIT 100");
if ($res) {
    while ($r = $res->fetch_assoc()) $appts[] = $r;
}

// Estadísticas simples (consultas rápidas)
// total usuarios
$totalUsers = 0;
$res = $mysqli->query("SELECT COUNT(*) as c FROM users");
if ($res) {
    $row = $res->fetch_assoc();
    $totalUsers = (int)$row['c'];
}

// total posts
$totalPosts = 0;
$res = $mysqli->query("SELECT COUNT(*) as c FROM posts");
if ($res) {
    $row = $res->fetch_assoc();
    $totalPosts = (int)$row['c'];
}

// total archivos
$totalFiles = 0;
$res = $mysqli->query("SELECT COUNT(*) as c FROM files");
if ($res) {
    $row = $res->fetch_assoc();
    $totalFiles = (int)$row['c'];
}

// Helper para escapar
function e($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Rubén Jaramillo — TRONIX</title>
  <!-- Enlazamos el style.css ya existente (no se cambia el nombre) -->
  <link rel="stylesheet" href="style.css">
  <!-- app.js maneja la navegación, sidebar y UI (no cambiar nombre) -->
  <script src="app.js" defer></script>
  <style>
  /* style_superior.css — Estilo "Premium Social" para TRONIX
   Autor: ChatGPT (adaptado para Diego A.L.B.)
   Requisitos: mantiene todos los nombres de clases existentes del proyecto
   Objetivo: interfaz tipo red social (Facebook/Twitter feel), sidebar fijo a la izquierda,
   layout responsivo, componentes pulidos, accesibilidad, animaciones suaves.

   Paleta requerida (no cambiar nombres, respetado):
     --verde-oscuro  #2E402D
     --verde-oliva   #5E6F4E
     --beige         #D2C8A4
     --blanco        #ffffff
     --text          #1b1b1b
     --muted         #777777
*/

:root{
  --verde-oscuro:#2E402D;
  --verde-oliva:#5E6F4E;
  --beige:#D2C8A4;
  --blanco:#ffffff;
  --text:#1b1b1b;
  --muted:#777777;

  /* extra tokens */
  --accent: #055b96; /* complemento azul para acciones */
  --glass: rgba(255,255,255,0.85);
  --bg:#f3f4f6;
  --card-shadow: 0 8px 30px rgba(2,6,23,0.06);
  --card-shadow-strong: 0 14px 50px rgba(2,6,23,0.09);
  --radius:12px;
  --ease: cubic-bezier(.2,.9,.3,1);
  --nav-height:72px;
}

/* ======================
   RESET / TIPOGRAFÍA
   ======================
*/
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{
  font-family: "Inter", "Poppins", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
  background:var(--bg);
  color:var(--text);
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
  line-height:1.45;
  font-size:15px;
}

a{color:var(--accent);text-decoration:none}
a:hover{text-decoration:underline}

/* Accessibility focus outline */
:focus{outline:3px solid rgba(5,91,150,0.15);outline-offset:2px}

/* Utility classes (do not rename) */
.container{max-width:1200px;margin:24px auto;padding:0 20px}
.hidden{display:none!important}
.row{display:flex;gap:12px;align-items:center}
.col{flex:1}

/* ======================
   HEADER / NAV
   ======================
*/
.site-header{
  height:var(--nav-height);
  display:flex;align-items:center;justify-content:space-between;
  padding:12px 18px;
  background: linear-gradient(90deg,var(--verde-oscuro),var(--verde-oliva));
  color:var(--blanco);
  position:sticky;top:0;z-index:1200;
  box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}

.logo{display:flex;align-items:center;gap:12px}
.logo img{width:48px;height:48px;border-radius:10px;object-fit:cover;border:2px solid rgba(255,255,255,0.08)}
.logo h1{font-size:18px;margin:0;font-weight:800;letter-spacing:0.2px}
.logo small{display:block;font-size:12px;opacity:0.9}

.main-nav{display:flex;gap:8px;align-items:center}
.main-nav a{color:var(--blanco);padding:10px 12px;border-radius:10px;font-weight:700;transition:all 220ms var(--ease)}
.main-nav a:hover,.main-nav a.active{background:rgba(255,255,255,0.08);transform:translateY(-2px)}

.header-actions{display:flex;gap:10px;align-items:center}
.user-badge{background:rgba(255,255,255,0.08);padding:6px 12px;border-radius:20px;font-weight:700}
.btn.small{background:transparent;border:1px solid rgba(255,255,255,0.12);color:#fff;padding:6px 10px;border-radius:8px}
.btn.small:hover{background:rgba(255,255,255,0.12)}
.menu-toggle{display:none;background:none;border:none;color:#fff;font-size:22px}

/* Small header thin shadow when scrolled */
.site-header.scrolled{box-shadow:0 8px 40px rgba(0,0,0,0.18)}

/* ======================
   LAYOUT: SIDEBAR + MAIN
   ======================
*/


/* Sidebar styling (left fixed) */
.sidebar{
  position:fixed;left:18px;top:calc(var(--nav-height) + 18px);
  width:260px;height:calc(100vh - var(--nav-height) - 36px);
  background:linear-gradient(180deg,#012a11 0%, #06321a 60%);
  color:#fff;padding:16px;border-radius:14px;box-shadow:var(--card-shadow);
  display:flex;flex-direction:column;gap:12px;z-index:1000;overflow:auto;padding-bottom:24px
}

/* When window narrow, make it overlaying */
.sidebar.collapsed{left:-320px;transition:left .28s var(--ease)}
.sidebar .brand{display:flex;gap:10px;align-items:center}
.sidebar .brand img{width:56px;height:56px;border-radius:12px;object-fit:cover}
.sidebar h3, .sidebar .brand h3{margin:0;font-size:16px}
.sidebar small{opacity:0.85}

.sidebar-nav{display:flex;flex-direction:column;margin-top:6px}
.sidebar-nav a{padding:12px 10px;border-radius:10px;color:#f7fbff;margin:4px 0;display:flex;align-items:center;gap:10px;font-weight:600}
.sidebar-nav a:hover{background:rgba(255,255,255,0.06);transform:translateX(6px)}
.sidebar .section-title{text-transform:uppercase;font-size:12px;color:rgba(255,255,255,0.7);margin-top:12px}

.quick-stats{margin-top:12px;display:flex;flex-direction:column;gap:8px}
.stat{background:linear-gradient(180deg,rgba(255,255,255,0.02),rgba(255,255,255,0.01));padding:10px;border-radius:10px;display:flex;justify-content:space-between;align-items:center}
.stat small{opacity:0.75}

.sidebar-footer{margin-top:auto;padding-top:10px;border-top:1px solid rgba(255,255,255,0.04);text-align:center}
.sidebar-footer .btn{background:#fff;color:#042b16;padding:8px 12px;border-radius:8px;font-weight:700}

/* Main content moves right to leave space for fixed sidebar */
.main-content{margin-left:320px;padding:18px 12px}

/* If no sidebar (mobile), full width */
.main-content.full{margin-left:0}

/* ======================
   CARDS / WIDGETS
   ======================
*/
.card{background:var(--glass);border-radius:var(--radius);padding:16px;box-shadow:var(--card-shadow);backdrop-filter: blur(6px)}
.card h4{margin:0 0 10px 0}
.widget{min-height:120px}
.widgets{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:18px}
@media (max-width:1100px){.widgets{grid-template-columns:repeat(2,1fr)}}
@media (max-width:700px){.widgets{grid-template-columns:1fr}}

.top-banner{display:flex;justify-content:space-between;align-items:center;padding:12px;border-radius:12px;background:linear-gradient(90deg,var(--verde-oscuro),var(--verde-oliva));color:#fff}
.top-banner h3{margin:0}
.top-banner p{margin:0;opacity:0.95}

/* ======================
   FEED / POSTS (look & feel tipo FB/TW)
   ======================
*/
.feed{display:flex;flex-direction:column;gap:12px}
.post-card{display:flex;gap:14px;padding:14px;background:#fff;border-radius:12px;align-items:flex-start;box-shadow:var(--card-shadow);transition:transform .18s var(--ease),box-shadow .18s var(--ease)}
.post-card:hover{transform:translateY(-4px);box-shadow:var(--card-shadow-strong)}
.post-image{width:220px;height:130px;border-radius:10px;object-fit:cover;flex-shrink:0}
.post-body{flex:1}
.post-body h3{margin:0 0 6px;font-size:18px}
.post-body p{margin-top:8px;color:#333}
.meta{color:var(--muted);font-size:13px}

/* Post header: avatar + name + time */
.post-header{display:flex;gap:12px;align-items:center}
.avatar{width:44px;height:44px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-weight:800;color:#fff;background:linear-gradient(90deg,#06b6d4,#3b82f6)}
.post-owner{display:flex;flex-direction:column}
.post-owner strong{font-size:14px}
.post-owner small{font-size:12px;color:var(--muted)}

/* Action row (like/comment/share) */
.post-actions{display:flex;gap:8px;margin-top:10px;align-items:center}
.btn{background:linear-gradient(90deg,var(--verde-oscuro),var(--verde-oliva));color:var(--blanco);padding:8px 12px;border-radius:10px;border:none;cursor:pointer;font-weight:700}
.btn.ghost{background:transparent;border:1px solid #e6e6e6;color:#333}
.btn.small{padding:6px 10px;font-size:14px}
.btn-icon{background:transparent;border:none;cursor:pointer;font-size:16px}

/* Like/comment counters */
.reactions{display:flex;gap:8px;align-items:center;color:var(--muted);font-size:14px}
.reactions .dot{width:8px;height:8px;border-radius:50%;background:var(--verde-oliva);display:inline-block}

/* Feed grid (two columns on desktop) */
.feed-grid{display:grid;grid-template-columns:1fr 340px;gap:18px}
@media (max-width:1000px){.feed-grid{grid-template-columns:1fr}}

/* ======================
   SOCIAL / SOCIAL-POST
   ======================
*/
.social-post{background:#fff;border-radius:12px;padding:12px;box-shadow:var(--card-shadow);display:flex;flex-direction:column;gap:8px}
.social-head{display:flex;justify-content:space-between;align-items:center}
.social-img{width:100%;border-radius:10px;max-height:420px;object-fit:cover}

/* Like/Comment/Share buttons style */
.actions-row{display:flex;justify-content:space-between;align-items:center;margin-top:6px}
.action-btn{display:inline-flex;align-items:center;gap:8px;padding:8px 10px;border-radius:10px;background:transparent;border:1px solid #f0f0f0;cursor:pointer}
.action-btn:hover{background:#f7fbff}

/* ======================
   COMMENTS
   ======================
*/
.comments-box{margin-top:12px;border-top:1px solid #eee;padding-top:12px}
.comment-form{display:flex;gap:8px}
.comment-form input{flex:1;padding:8px 10px;border-radius:10px;border:1px solid #e2e8f0}
.comment-form button{padding:8px 12px;border-radius:8px;border:none;background:var(--accent);color:#fff;cursor:pointer}
.comment{background:#f7fafc;border-radius:8px;padding:8px;margin-bottom:8px}
.comment strong{display:block;font-weight:700}
.comment small{display:block;color:var(--muted);font-size:12px;margin-top:6px}

/* ======================
   ARCHIVOS (FILES)
   ======================
*/
.file-item{display:flex;justify-content:space-between;align-items:center;padding:12px;border-radius:10px;background:#fff;border:1px solid #f0f0f0}
.file-download{color:var(--verde-oscuro);font-weight:700}
.file-meta{font-size:13px;color:var(--muted)}

/* Upload area nice box */
.upload-box{border:2px dashed rgba(2,6,23,0.06);padding:12px;border-radius:10px;background:linear-gradient(180deg,#fff,#fbfdff)}
.upload-box input[type=file]{display:block}

/* ======================
   ACCOUNT / PROFILE PANEL
   ======================
*/
.profile-card{display:flex;gap:12px;align-items:center}
.profile-card .avatar-lg{width:72px;height:72px;border-radius:14px;font-size:28px}
.profile-actions{display:flex;gap:8px}

/* My posts list */
.my-posts li{padding:8px 0;border-bottom:1px dashed #eee}

/* ======================
   NOTIFICATIONS / TOASTS (simple)
   ======================
*/
.toast{position:fixed;right:20px;bottom:20px;background:#0b3f2d;color:#fff;padding:12px 16px;border-radius:8px;box-shadow:var(--card-shadow);z-index:1500}
.toast.hide{opacity:0;transform:translateY(10px);pointer-events:none}

/* ======================
   TABLES / ADMIN LISTS (if used)
   ======================
*/
.table{width:100%;border-collapse:collapse}
.table th,.table td{padding:10px;border-bottom:1px solid #f2f4f7;text-align:left}
.table th{background:#fafafa}

/* ======================
   RESPONSIVE RULES
   ======================
*/
@media (max-width:1100px){
  .layout{grid-template-columns:1fr}
  .sidebar{position:fixed;left:18px;top:calc(var(--nav-height)+14px);transform:translateX(-360px);transition:transform .28s var(--ease)}
  .sidebar.open{transform:translateX(0)}
  .main-content{margin-left:0;padding:12px}
  .main-content.overlay-active::before{content:'';position:fixed;inset:0;background:rgba(0,0,0,0.36);z-index:900}
}

@media (max-width:700px){
  .site-header{padding:10px}
  .logo h1{font-size:16px}
  .menu-toggle{display:inline-block}
  .main-nav{display:none}
  .post-image{display:none}
  .widgets{grid-template-columns:1fr}
  .feed-grid{grid-template-columns:1fr}
}

/* ======================
   ANIMACIONES SUBTILES
   ======================
*/
@keyframes floatUp{0%{transform:translateY(6px);opacity:0}100%{transform:translateY(0);opacity:1}}
.fade-in{animation:floatUp .4s var(--ease) forwards}

/* ======================
   MICRO-INTERACTIONS & HELPERS
   ======================
*/
.icon-circle{display:inline-flex;width:36px;height:36px;border-radius:10px;align-items:center;justify-content:center;background:#fff;box-shadow:0 6px 20px rgba(2,6,23,0.05)}
.kicker{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.9px}
.muted{color:var(--muted)}

/* ======================
   OVERRIDES to keep compatibility with existing classes
   (safe, do not rename or remove these) 
   ======================
*/
.page{display:block}
.site-footer{padding:18px 12px;background:transparent;color:var(--muted);text-align:center}

/* Buttons variations kept */
button.btn{transition:transform .12s var(--ease);border-radius:10px}
button.btn:active{transform:translateY(1px)}

/* form inputs consistent look */
input,textarea,select{border-radius:10px;border:1px solid #e6e6e6;padding:10px;font-size:15px}
input:focus,textarea:focus,select:focus{box-shadow:0 3px 18px rgba(3,72,126,0.06);border-color:var(--verde-oliva)}

/* ======================
   PRINT / LONG PAGES
   ======================
*/
@media print{
  .sidebar,.site-header,.footer,.btn{display:none}
  body{background:#fff;color:#000}
}

/* ======================
   END - Additional notes:
   - This file purposely targets the existing classes used in your index.php.
   - It keeps the color tokens you requested; uses richer spacing, shadows and micro-interactions.
   - If your app.js toggles .sidebar.open / .sidebar.collapsed, both states are supported.
   - To enable the left sidebar fixed spacing, ensure the .main-content element gets the class "main-content" (already present).

*/

  </style>
</head>
<body>
  <!-- HEADER (mantener) -->
  <header class="site-header">
    <div class="logo">
      <img src="imagenes.pnj/LOGO-TEMIXCO_VERTICAL-1536x1536.png" alt="Logo" onerror="this.style.display='none'">
      <div>
        <h1>Rubén Jaramillo</h1>
        <small>Portal Temixco — TRONIX </small>
      </div>
    </div>

    <nav class="main-nav" role="navigation" aria-label="Main navigation">
      <a href="#home" class="nav-link active" data-target="home">INICIO</a>
      <a href="#social" class="nav-link" data-target="social">SOCIAL</a>
      <a href="#files" class="nav-link" data-target="files">ARCHIVOS</a>
      <a href="#nosotros" class="nav-link" data-target="nosotros">NOSOTROS</a>
      <a href="#account" class="nav-link" data-target="account">CUENTA</a>
    </nav>

    <div class="header-actions">
      <!-- Botón visible en móviles para abrir sidebar -->
      <button class="menu-toggle" id="menuToggle">☰</button>

      <?php if ($currentUser): ?>
        <span class="user-badge"><?= e($currentUser['name']) ?></span>
        <a href="logout.php" class="btn small">Cerrar sesión</a>
      <?php else: ?>
        <a href="#" class="btn small" id="openLogin">Iniciar</a>
      <?php endif; ?>
    </div>
    <button id="toggleSidebar" class="btn-menu">☰</button>
  </header>

  <!-- Layout principal: sidebar + contenido -->
  <div class="container layout" id="layoutRoot">

    <!-- SIDEBAR: fijo en escritorio, deslizable en móvil -->
    <aside class="sidebar" id="sidebar" aria-label="Sidebar">
      <!-- Marca / Brand -->
      <div class="brand">
        <img src="logo.png" alt="Logo" onerror="this.style.display='none'">
        <div>
          <h3>TRONIX - Temixco</h3>
          <small>Delegacion Ruben Jaramillo</small>
        </div>
      </div>

      <!-- Perfil rápido -->
      <div class="profile card" style="background:transparent; padding:0;">
        <?php if ($currentUser): ?>
          <div style="display:flex; gap:12px; align-items:center;">
            <div style="width:48px;height:48px;border-radius:10px;background:linear-gradient(90deg,#06b6d4,#3b82f6);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;">
              <?= strtoupper(substr(e($currentUser['name']),0,1)) ?>
            </div>
            <div>
              <strong style="color:#fff; display:block;"><?= e($currentUser['name']) ?></strong>
              <small style="color:rgba(255,255,255,0.8)"><?= e($currentUser['email']) ?></small>
            </div>
          </div>
        <?php else: ?>
          <div style="padding:8px; color:#fff;">
            <strong>Invitado</strong>
            <div style="margin-top:6px; color:rgba(255,255,255,0.8)">Inicia sesión para más opciones</div>
            <div style="margin-top:10px;">
              <a href="#" onclick="navigate('account')" class="btn small" style="display:inline-block;">Iniciar / Registrar</a>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Navegación secundaria (sidebar) -->
      <nav>
        <div class="section-title">Navegación</div>
        <a href="#home" data-target="home" class="side-link">🏠 Inicio</a>
        <a href="#social" data-target="social" class="side-link">💬 Social</a>
        <a href="#files" data-target="files" class="side-link">📁 Archivos</a>
        <a href="#nosotros" data-target="nosotros" class="side-link">📚 Nosotros</a>
        <a href="#account" data-target="account" class="side-link">🔐 Cuenta</a>

        <div class="section-title">Herramientas</div>
        <a href="#" onclick="navigate('home'); document.getElementById('registerCard') && (document.getElementById('registerCard').style.display='block');" class="side-link">➕ Crear cuenta demo</a>
        <a href="#files" class="side-link">📥 Subir / Descargar</a>
        <a href="#nosotros" class="side-link">📅 Reservar cita</a>
      </nav>

      <!-- Estadísticas rápidas -->
      <div class="section-title">Estadísticas</div>
      <div class="quick-stats">
        <div class="stat">
          <div>
            <small>Usuarios</small>
            <strong><?= e($totalUsers) ?></strong>
          </div>
        </div>
        <div class="stat">
          <div>
            <small>Publicaciones</small>
            <strong><?= e($totalPosts) ?></strong>
          </div>
        </div>
        <div class="stat">
          <div>
            <small>Archivos</small>
            <strong><?= e($totalFiles) ?></strong>
          </div>
        </div>
      </div>

      <!-- CTA lateral -->
      <div class="side-cta">
        <?php if ($currentUser): ?>
          <a href="#social" class="btn small">Crear publicación</a>
        <?php else: ?>
          <a href="#" onclick="navigate('account')" class="btn small">Inicia / Regístrate</a>
        <?php endif; ?>
        <a href="#nosotros" class="btn small" style="background:transparent;border:1px solid rgba(255,255,255,0.08);">Reservar cita</a>
      </div>

      <div style="height:14px"></div>
      <div class="panel-footer">TRONIX</div>
    </aside>

    <!-- OVERLAY para móviles -->
    <div class="overlay" id="overlay"></div>

    <!-- CONTENIDO PRINCIPAL -->
    <section class="main-content">

      <!-- TOP BANNER / NOTIFICACIONES -->
      <div class="top-banner">
        <div class="left">
          <div style="width:54px;height:54px;border-radius:10px;background:rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;font-weight:800;">T</div>
          <div>
            <h3>Bienvenido al Portal TRONIX </h3>
            <p>Noticias, archivos y servicios de la Delegacion Ruben Jaramillo en un solo lugar.</p>
          </div>
        </div>
        <div class="right" style="display:flex; gap:10px; align-items:center;">
          <div style="text-align:right;">
            <small style="color:rgba(255,255,255,0.9)">Estado del servidor</small>
            <div style="font-weight:700">Operativo</div>
          </div>
          <div>
            <button class="btn" onclick="window.location.reload()">Actualizar</button>
          </div>
        </div>
      </div>

      <!-- WIDGETS (estadísticas grandes) -->
      <div class="widgets" role="region" aria-label="Estadísticas">
        <div class="card widget">
          <h4>Usuarios registrados</h4>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
              <div style="font-size:28px;font-weight:800;"><?= e($totalUsers) ?></div>
              <small class="muted">Total usuarios</small>
            </div>
            <div style="text-align:right;">
              <small class="muted">Activos hoy</small>
              <div style="font-weight:700">—</div>
            </div>
          </div>
          <div class="panel-footer">Actualizado en tiempo real </div>
        </div>

        <div class="card widget">
          <h4>Publicaciones recientes</h4>
          <div style="margin-top:8px;">
            <?php if (!empty($posts)): ?>
              <?php for ($i=0; $i<min(3, count($posts)); $i++): $p = $posts[$i]; ?>
                <div style="padding:8px 0;border-bottom:1px dashed #eef2f7;">
                  <strong><?= e(mb_strimwidth($p['title']?:'Sin título',0,40,'...')) ?></strong>
                  <div class="muted" style="font-size:12px;"><?= e($p['created_at']) ?> · <?= e($p['name']?:'Anon') ?></div>
                </div>
              <?php endfor; ?>
            <?php else: ?>
              <div class="empty">Aún no hay publicaciones.</div>
            <?php endif; ?>
          </div>
        </div>

        <div class="card widget">
          <h4>Archivos más recientes</h4>
          <div style="margin-top:8px;">
            <?php if (!empty($files)): ?>
              <?php for ($i=0; $i<min(3, count($files)); $i++): $f = $files[$i]; ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;">
                  <div>
                    <strong><?= e(mb_strimwidth($f['original_name'],0,30,'...')) ?></strong>
                    <div class="muted" style="font-size:12px;">Por <?= e($f['name']?:'Anon') ?></div>
                  </div>
                  <div>
                    <a class="file-download" href="files_store/<?= e($f['filename']) ?>" download="<?= e($f['original_name']) ?>">Descargar</a>
                  </div>
                </div>
              <?php endfor; ?>
            <?php else: ?>
              <div class="empty">No hay archivos subidos.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- SECCIÓN: INICIO (Noticias + feed ampliado) -->
      <main id="home" class="page active" role="main" aria-label="Inicio">
        <h2>Noticias recientes</h2>

        <div style="display:flex; gap:16px; align-items:flex-start; flex-wrap:wrap;">
          <!-- Columna principal -->
          <div style="flex:1 1 720px; min-width:300px;">
            <?php if (empty($posts)): ?>
              <div class="empty card">Abrimos nuestros servidores 🤜🤛 — aún no hay noticias.</div>
            <?php else: ?>
              <?php foreach ($posts as $p): ?>
                <article class="post-card">
                  <?php if (!empty($p['image'])): ?>
                    <img src="uploads/<?= e($p['image']) ?>" alt="<?= e($p['title']) ?>" class="post-image">
                  <?php else: ?>
                    <img src="https://via.placeholder.com/220x130?text=Noticia" alt="placeholder" class="post-image">
                  <?php endif; ?>
                  <div class="post-body">
                    <h3><?= e($p['title']?:'Sin título') ?></h3>
                    <div class="meta">Por <?= e($p['name']?:'Anónimo') ?> · <?= e($p['created_at']) ?></div>
                    <p><?= nl2br(e(mb_strimwidth($p['content'],0,600,"..."))) ?></p>
                    <div style="margin-top:10px; display:flex; gap:8px;">
                      <a href="#" class="btn small" onclick="alert('Vista detallada (demo)')">Ver detalle</a>
                      <?php if ($currentUser): ?>
                        <a href="#social" class="btn ghost" onclick="navigate('social')">Comentar</a>
                      <?php endif; ?>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>

            <!-- Paginación simple (visual) -->
            <div style="display:flex; justify-content:center; margin-top:12px;">
              <button class="btn ghost" disabled>« Anterior</button>
              <div style="width:8px"></div>
              <button class="btn">Siguiente »</button>
            </div>
          </div>

          <!-- Columna lateral derecha: actividad y tips -->
          <aside style="width:320px; min-width:260px;">
            <div class="card">
              <h4>Actividad reciente</h4>
              <div class="activity-list" style="margin-top:10px;">
                <?php if (!empty($posts)): ?>
                  <?php for ($i=0; $i<min(5, count($posts)); $i++): $p = $posts[$i]; ?>
                    <div class="activity-item">
                      <div style="width:42px;height:42px;border-radius:8px;background:linear-gradient(90deg,#06b6d4,#3b82f6);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;">
                        <?= strtoupper(substr(e($p['name']?:'U'),0,1)) ?>
                      </div>
                      <div style="flex:1;">
                        <div style="font-weight:700"><?= e(mb_strimwidth($p['title']?:'Sin título',0,42,'...')) ?></div>
                        <div class="muted" style="font-size:13px"><?= e($p['created_at']) ?> · <?= e($p['name']?:'Anon') ?></div>
                      </div>
                    </div>
                  <?php endfor; ?>
                <?php else: ?>
                  <div class="empty">Sin actividad reciente.</div>
                <?php endif; ?>
              </div>
            </div>

            <div class="card" style="margin-top:12px;">
              <h4>Consejo rápido</h4>
              <p class="muted">Mantén tu contraseña segura y no compartas tus datos personales. Usa la sección de archivos para subir documentos oficiales en formato PDF.</p>
              <div style="margin-top:8px;">
                <a href="#files" class="btn small" onclick="navigate('files')">Ir a archivos</a>
              </div>
            </div>
          </aside>
        </div>
      </main>

      <!-- SECCIÓN: SOCIAL -->
      <section id="social" class="page" role="region" aria-label="Social">
        <h2>Red Social — Publicaciones</h2>

        <!-- Crear nueva publicación (solo para usuarios logueados) -->
        <?php if ($currentUser): ?>
          <div class="card">
            <form action="create_post.php" method="post" enctype="multipart/form-data" class="card">
              <?= csrf_field() ?>
              <label>Título</label>
              <input name="title" maxlength="255" placeholder="Título breve">
              <label>Contenido</label>
              <textarea name="content" rows="4" required placeholder="Escribe lo que quieras compartir..."></textarea>
              <label>Imagen (opcional)</label>
              <input type="file" name="image" accept="image/*">
              <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:8px;">
                <button type="submit" class="btn">Publicar</button>
                <button type="button" class="btn ghost" onclick="navigate('home')">Cancelar</button>
              </div>
            </form>
          </div>
        <?php else: ?>
          <div class="empty card">Inicia sesión para poder publicar en la red social.</div>
        <?php endif; ?>

        <!-- Feed -->
        <div style="display:grid; gap:12px; grid-template-columns: 1fr 1fr;">
          <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $p): ?>
              <div class="feed">
                <div class="social-post">
                 

                  <div class="social-head">
                    <strong><?= e($p['name']?:'Usuario') ?></strong>
                    <span class="meta"><?= e($p['created_at']) ?></span>
                  </div>
                  <?php if (!empty($p['image'])): ?>
                    <img class="social-img" src="uploads/<?= e($p['image']) ?>" alt="">
                  <?php endif; ?>
                  <p><?= nl2br(e($p['content'])) ?></p>
                  <div style="display:flex; gap:8px; margin-top:8px;">
                    <button class="btn ghost" onclick="alert('Función Me gusta (demo)')">👍 Me gusta</button>
                    <button class="btn ghost" onclick="alert('Función comentar (demo)')">💬 Comentar</button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty card">No hay publicaciones todavía.</div>
          <?php endif; ?>
        </div>

      </section>

      <!-- SECCIÓN: ARCHIVOS -->
      <section id="files" class="page" role="region" aria-label="Archivos">
        <h2>Archivos disponibles</h2>

        <?php if ($currentUser): ?>
          <div class="card">
            <form action="upload_file.php" method="post" enctype="multipart/form-data">
              <?= csrf_field() ?>
              <label>Subir nuevo archivo (PDF, JPG, PNG, ZIP)</label>
              <input type="file" name="file" required>
              <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:8px;">
                <button class="btn" type="submit">Subir</button>
              </div>
            </form>
          </div>
        <?php else: ?>
          <div class="empty card">Inicia sesión para subir archivos.</div>
        <?php endif; ?>

        <div class="card">
          <h4>Lista de archivos</h4>
          <?php if (empty($files)): ?>
            <div class="empty">No hay archivos para descargar.</div>
          <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:10px; margin-top:10px;">
              <?php foreach ($files as $f): ?>
                <div class="file-item">
                  <div>
                    <strong><?= e($f['original_name']) ?></strong>
                    <div class="muted" style="font-size:13px;">Subido por <?= e($f['name']?:'Anon') ?> • <?= e($f['uploaded_at']) ?></div>
                  </div>
                  <div style="display:flex; gap:8px; align-items:center;">
                    <a class="file-download" href="files_store/<?= e($f['filename']) ?>" download="<?= e($f['original_name']) ?>">Descargar</a>
                    <?php if ($currentUser): ?>
                      <button class="btn ghost" onclick="alert('Función eliminar (solo admin en producción)')">Eliminar</button>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </section>

      <!-- SECCIÓN: NOSOTROS -->
      <section id="nosotros" class="page" role="region" aria-label="Nosotros">
        <h2>Nosotros</h2>

        <div class="card">
          <h3>Quiénes somos</h3>
          <p class="muted">Somos TRONIX — equipo responsable de gestionar información, actividades y servicios estudiantiles. Este portal es una demo que muestra funcionalidades de administración y publicación.</p>
        </div>

        <div class="card org-chart">
          <h4>Organigrama</h4>
          <div style="display:flex; gap:10px; align-items:center; justify-content:center; flex-wrap:wrap;">
            <div class="org-box">Dirección General</div>
            <div class="org-box">Coordinación</div>
            <div class="org-box">Servicios Escolares</div>
          </div>
        </div>

        <div class="card">
          <h3>Reservar cita</h3>
          <?php if ($currentUser): ?>
            <form action="book_appointment.php" method="post" class="card">
              <?= csrf_field() ?>
              <label>Fecha</label>
              <input type="date" name="date" required>
              <label>Hora</label>
              <select name="time_slot">
                <option>09:00</option><option>10:00</option><option>11:00</option><option>12:00</option>
              </select>
              <label>Motivo</label>
              <input name="purpose" maxlength="255" required>
              <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:8px;">
                <button class="btn" type="submit">Reservar</button>
              </div>
            </form>
          <?php else: ?>
            <div class="empty">Inicia sesión para reservar una cita.</div>
          <?php endif; ?>

          <h4 style="margin-top:12px;">Citas próximas</h4>
          <div class="appointments" style="margin-top:8px;">
            <?php if (empty($appts)): ?>
              <div class="empty">No hay citas registradas.</div>
            <?php else: ?>
              <?php foreach ($appts as $a): ?>
                <div class="appointment-item">
                  <strong><?= e($a['name']?:'Usuario') ?></strong>
                  <div class="muted"><?= e($a['date']) ?> • <?= e($a['time_slot']) ?></div>
                  <div><?= e($a['purpose']) ?></div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <!-- SECCIÓN: CUENTA -->
      <section id="account" class="page" role="region" aria-label="Cuenta">
        <h2>Cuenta</h2>

        <?php if ($currentUser): ?>
          <div class="card">
            <p>Bienvenido, <strong><?= e($currentUser['name']) ?></strong></p>
            <p class="muted"><?= e($currentUser['email']) ?></p>
            <div style="display:flex; gap:8px; margin-top:8px;">
              <a href="logout.php" class="btn">Cerrar sesión</a>
              <a href="#" class="btn ghost" onclick="alert('Incluido en proximas actualizaciones')">Editar perfil</a>
            </div>
          </div>

          <div class="card">
            <h4>Mis publicaciones</h4>
            <?php
            // Cargar publicaciones del usuario actual
            $myPosts = [];
            $stmt = $mysqli->prepare("SELECT id, title, content, created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $r = $stmt->get_result();
            while ($row = $r->fetch_assoc()) $myPosts[] = $row;
            $stmt->close();
            ?>
            <?php if (empty($myPosts)): ?>
              <div class="empty">Aún no has creado publicaciones.</div>
            <?php else: ?>
              <ul style="list-style:none; padding-left:0; margin:0;">
                <?php foreach ($myPosts as $mp): ?>
                  <li style="padding:8px 0; border-bottom:1px dashed #eee;">
                    <strong><?= e(mb_strimwidth($mp['title']?:'Sin título',0,60,'...')) ?></strong>
                    <div class="muted" style="font-size:13px"><?= e($mp['created_at']) ?></div>
                    <div style="margin-top:6px;">
                      <a href="#" class="btn small" onclick="alert('Editar publicación (demo)')">Editar</a>
                      <button class="btn ghost" onclick="alert('Eliminar publicación (demo)')">Eliminar</button>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>

        <?php else: ?>
  <!-- Login -->
  <div class="card" id="loginCard">
    <h3>Iniciar sesión</h3>
    <form action="login.php" method="post">
      <?= csrf_field() ?>
      <label>Correo electrónico</label>
      <input type="email" name="email" required placeholder="tu@correo.com">
      <label>Contraseña</label>
      <input type="password" name="password" required placeholder="••••••">
      <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:8px;">
        <button class="btn" type="submit">Entrar</button>
      </div>
    </form>
  </div>

  <!-- Registro -->
  <div class="card" id="registerCard" style="margin-top:14px;">
    <h3>Crear cuenta nueva</h3>
    <form action="register.php" method="post">
      <?= csrf_field() ?>
      <label>Nombre completo</label>
      <input type="text" name="name" required maxlength="100" placeholder="Tu nombre completo">
      <label>Correo electrónico</label>
      <input type="email" name="email" required placeholder="tu@correo.com">
      <label>Contraseña</label>
      <input type="password" name="password" required placeholder="••••••">
      <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:8px;">
        <button class="btn" type="submit">Registrarme</button>
      </div>
    </form>
  </div>
<?php endif; ?>

</section>


      <!-- Pequeño footer dentro del main -->
      <div style="margin-top:18px; margin-bottom:30px; color:#6b7280; font-size:13px;">
        <div>© <?= date('Y') ?> Plantel Temixco — Delegacion Ruben Jaramillo. Diseñado para pruebas locales en XAMPP.</div>
        <div class="muted">No almacenes datos sensibles aquí en producción sin medidas de seguridad adicionales.</div>
      </div>

    </section>
  </div> <!-- end container layout -->
<!-- ==== SCRIPT DE LIKE Y COMENTARIO ==== -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    // === Manejar Likes ===
    document.querySelectorAll(".like-btn").forEach(btn => {
        btn.addEventListener("click", async () => {
            const postId = btn.dataset.post;
            try {
                const res = await fetch("añadir_likes.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `post_id=${encodeURIComponent(postId)}`
                });
                const data = await res.json();

                if (data.status === "ok") {
                    if (data.action === "liked") {
                        btn.classList.add("liked");
                        btn.textContent = "❤️ Me gusta";
                    } else {
                        btn.classList.remove("liked");
                        btn.textContent = "🤍 Me gusta";
                    }
                } else {
                    alert(data.message || "Error al dar like");
                }
            } catch (e) {
                alert("Error al conectar con el servidor");
            }
        });
    });

    // === Manejar Comentarios ===
    document.querySelectorAll(".comment-form").forEach(form => {
        form.addEventListener("submit", async e => {
            e.preventDefault();
            const postId = form.dataset.post;
            const input = form.querySelector(".comment-input");
            const comment = input.value.trim();

            if (!comment) {
                alert("Escribe un comentario antes de enviar");
                return;
            }

            try {
                const res = await fetch("añadir_comentarios.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `post_id=${encodeURIComponent(postId)}&comment=${encodeURIComponent(comment)}`
                });
                const data = await res.json();

                if (data.status === "ok") {
                    const list = document.querySelector(`#comments-${postId}`);
                    const nuevo = document.createElement("div");
                    nuevo.classList.add("comment");
                    nuevo.innerHTML = `<strong>Tú:</strong> ${comment}`;
                    list.prepend(nuevo);
                    input.value = "";
                } else {
                    alert(data.message || "No se pudo comentar");
                }
            } catch (e) {
                alert("Error de conexión al enviar comentario");
            }
        });
    });
});
</script>

</body>
</html>

