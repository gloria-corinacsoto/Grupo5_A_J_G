<?php
include "conexion.php";

$mensaje = "";

if (isset($_POST["nombre"]) && isset($_POST["correo"]) && isset($_POST["clave"]) && isset($_POST["tipo"])) {

    $nombre = trim($_POST["nombre"]);
    $correo = strtolower(trim($_POST["correo"]));
    $clave = $_POST["clave"];
    $tipo = $_POST["tipo"];

    if ($nombre == "" || $correo == "" || strlen($clave) < 8) {
        $mensaje = "Revisa los datos. La contraseña necesita al menos 8 caracteres.";
    } else if ($tipo != "empresa" && $tipo != "visitante") {
        $mensaje = "El tipo de usuario no es válido.";
    } else {

        $clave_hash = password_hash($clave, PASSWORD_DEFAULT);

        $sentencia = mysqli_prepare($conexion, "INSERT INTO usuarios (nombre, tipo, correo, clave_hash) VALUES (?, ?, ?, ?)");

        mysqli_stmt_bind_param($sentencia, "ssss", $nombre, $tipo, $correo, $clave_hash);

        if (mysqli_stmt_execute($sentencia)) {
            $mensaje = "Registro completado. Ya puedes iniciar sesión.";
        } else {

            $error = mysqli_stmt_errno($sentencia);
            if ($error == 1062) {
                $mensaje = "Ese correo ya está registrado.";
            } else if ($error == 1644) {

                $mensaje = mysqli_stmt_error($sentencia);
            } else {
                $mensaje = "No se pudo registrar. Inténtalo más tarde.";
            }
        }
    }
} else {
    $mensaje = "Faltan datos.";
}

$titulo = "Registro";
include "cabecera.php";

echo "<p class='fs-5'>".limpiar($mensaje)."</p>";
?>
<a class="boton1" href="login.php">Iniciar sesión</a>
<?php
include "pie.php";
?>
