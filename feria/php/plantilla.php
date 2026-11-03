<?php
// Cabecera, pie y funciones comunes. Así no repetimos el HTML en cada página.
require_once "conexion.php";

// e() protege de que se cuele código HTML en los textos que mostramos
function e($texto) { return htmlspecialchars((string)$texto); }

// Si no hay sesión iniciada, manda al login
function exigir_login() {
    if (!isset($_SESSION["usuario"])) { header("Location: login.php"); exit; }
}

function cabecera($titulo) { ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo) ?> - LSBM 2027</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
</head>
<body>
  <header>
    <p>LSBM <span class="Amarillo">2027</span></p>
    <ul>
      <li><a class="EnlaceMenu" href="../index.html">INICIO</a></li>
      <?php if (isset($_SESSION["usuario"])): ?>
        <li><a class="EnlaceMenu" href="stands.php">STANDS</a></li>
        <?php if ($_SESSION["usuario"]["tipo"] === "empresa"): ?>
          <li><a class="EnlaceMenu" href="mi_stand.php">MI STAND</a></li>
        <?php endif; ?>
        <li><a class="boton2" href="logout.php">Salir</a></li>
      <?php else: ?>
        <li><a class="boton2" href="login.php">Iniciar sesión</a></li>
      <?php endif; ?>
    </ul>
  </header>
  <main class="container Seccion">
    <h1 class="TituloSeccion"><?= e($titulo) ?></h1>
<?php }

function pie() { ?>
  </main>
</body>
</html>
<?php }
