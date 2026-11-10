<?php
include "conexion.php";

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_POST["stand_id"])) {
    header("Location: stands.php");
    exit;
}

$id_usuario = $_SESSION["id"];
$id_stand = $_POST["stand_id"];
settype($id_stand, "integer");

$sentencia = mysqli_prepare($conexion, "INSERT INTO votos (usuario_id, stand_id) VALUES (?, ?)");
mysqli_stmt_bind_param($sentencia, "ii", $id_usuario, $id_stand);

if (mysqli_stmt_execute($sentencia)) {
    $mensaje = "¡Voto registrado!";
} else {
    $error = mysqli_stmt_errno($sentencia);
    if ($error == 1062) {
        $mensaje = "Ya has votado.";
    } else if ($error == 1644) {

        $mensaje = mysqli_stmt_error($sentencia);
    } else {
        $mensaje = "No se pudo registrar el voto.";
    }
}

header("Location: stands.php?mensaje=".urlencode($mensaje));
exit;
?>
