<?php
include "conexion.php";

$mensaje = "";

// Comprobamos que llegan todos los datos del formulario (método POST)
// Usamos POST y no GET para que la contraseña no se vea en la dirección
if (isset($_POST["nombre"]) && isset($_POST["correo"]) && isset($_POST["clave"]) && isset($_POST["tipo"])) {

    $nombre = trim($_POST["nombre"]);               // trim quita los espacios sobrantes
    $correo = strtolower(trim($_POST["correo"]));   // strtolower pasa todo a minúsculas
    $clave = $_POST["clave"];
    $tipo = $_POST["tipo"];

    if ($nombre == "" || $correo == "" || strlen($clave) < 8) {
        $mensaje = "Revisa los datos. La contraseña necesita al menos 8 caracteres.";
    } else if ($tipo != "empresa" && $tipo != "visitante") {
        $mensaje = "El tipo de usuario no es válido.";
    } else {
        // Guardamos la contraseña cifrada, nunca la contraseña tal cual
        $clave_hash = password_hash($clave, PASSWORD_DEFAULT);

        // Los ? se rellenan después con los datos. Así nadie puede colar código SQL
        $sentencia = mysqli_prepare($conexion, "INSERT INTO usuarios (nombre, tipo, correo, clave_hash) VALUES (?, ?, ?, ?)");
        // "ssss" significa que los 4 datos son texto (string)
        mysqli_stmt_bind_param($sentencia, "ssss", $nombre, $tipo, $correo, $clave_hash);

        if (mysqli_stmt_execute($sentencia)) {
            $mensaje = "Registro completado. Ya puedes iniciar sesión.";
        } else {
            // Si falla, miramos el número de error de MySQL
            $error = mysqli_stmt_errno($sentencia);
            if ($error == 1062) {
                $mensaje = "Ese correo ya está registrado.";
            } else if ($error == 1644) {
                // 1644 = lo ha rechazado un trigger (por ejemplo, correo no autorizado)
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
