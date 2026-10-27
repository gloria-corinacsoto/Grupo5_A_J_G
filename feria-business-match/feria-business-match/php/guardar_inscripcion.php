<?php
require "conexion.php";

// 1. Solo aceptamos datos enviados desde el formulario (método POST)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.html");
    exit;
}

// 2. Recogemos los datos (trim quita espacios sobrantes)
$nombre    = trim($_POST["nombre"] ?? "");
$apellidos = trim($_POST["apellidos"] ?? "");
$correo    = trim($_POST["correo"] ?? "");
$tipo      = $_POST["tipo"] ?? "";

// 3. Validamos en el servidor (el navegador se puede saltar)
$mensaje = "";
if ($nombre === "" || $apellidos === "" || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $mensaje = "Revisa los datos: hay campos vacíos o el correo no es válido.";
} elseif (!in_array($tipo, ["empresa", "visitante"])) {
    $mensaje = "Tipo de inscripción no válido.";
} else {
    // 4. Guardamos con una consulta PREPARADA (evita la inyección SQL)
    try {
        $sql = "INSERT INTO inscripciones (nombre, apellidos, correo, tipo)
                VALUES (:nombre, :apellidos, :correo, :tipo)";
        $consulta = $pdo->prepare($sql);
        $consulta->execute([
            ":nombre"    => $nombre,
            ":apellidos" => $apellidos,
            ":correo"    => $correo,
            ":tipo"      => $tipo,
        ]);
        $mensaje = "¡Gracias, $nombre! Tu inscripción se ha guardado.";
    } catch (PDOException $e) {
        // El código 23000 significa "correo duplicado" (por el UNIQUE)
        $mensaje = ($e->getCode() == 23000)
            ? "Ese correo ya está inscrito."
            : "No se pudo guardar la inscripción. Inténtalo más tarde.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inscripción</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
  <div class="text-center">
    <!-- htmlspecialchars evita que se cuele código HTML en el mensaje -->
    <p class="fs-4"><?= htmlspecialchars($mensaje) ?></p>
    <a class="boton" href="../index.html">Volver a la web</a>
  </div>
</body>
</html>
