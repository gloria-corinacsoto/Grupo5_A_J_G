<?php
include "conexion.php";

// Si no hay sesión iniciada, mandamos al login
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

// Si no llega el stand desde el formulario, volvemos al listado
if (!isset($_POST["stand_id"])) {
    header("Location: stands.php");
    exit;
}

$id_usuario = $_SESSION["id"];
$id_stand = $_POST["stand_id"];
settype($id_stand, "integer"); // nos aseguramos de que es un número entero

// Guardamos el voto. Las reglas las comprueba la base de datos con el trigger trg_validar_voto
// (votación abierta, stand con spot, no votar al propio stand) y con la clave primaria (un solo voto)
$sentencia = mysqli_prepare($conexion, "INSERT INTO votos (usuario_id, stand_id) VALUES (?, ?)");
mysqli_stmt_bind_param($sentencia, "ii", $id_usuario, $id_stand);

if (mysqli_stmt_execute($sentencia)) {
    $mensaje = "¡Voto registrado!";
} else {
    $error = mysqli_stmt_errno($sentencia);
    if ($error == 1062) {
        $mensaje = "Ya has votado.";
    } else if ($error == 1644) {
        // 1644 = lo ha rechazado el trigger, y su mensaje explica el motivo
        $mensaje = mysqli_stmt_error($sentencia);
    } else {
        $mensaje = "No se pudo registrar el voto.";
    }
}

// Volvemos al listado enseñando el mensaje
header("Location: stands.php?mensaje=".urlencode($mensaje));
exit;
?>
