<?php
include "conexion.php";

$error = "";

if (isset($_POST["correo"]) && isset($_POST["clave"])) {
    $correo = strtolower(trim($_POST["correo"]));
    $clave = $_POST["clave"];

    $sentencia = mysqli_prepare($conexion, "SELECT id, nombre, tipo, clave_hash, bloqueado FROM usuarios WHERE correo = ?");
    mysqli_stmt_bind_param($sentencia, "s", $correo);
    mysqli_stmt_execute($sentencia);
    $resultado = mysqli_stmt_get_result($sentencia);
    $usuario = mysqli_fetch_assoc($resultado);

    if ($usuario == null) {
        $error = "Correo o contraseña incorrectos.";
    } else if ($usuario["bloqueado"] == 1) {
        $error = "Cuenta bloqueada por demasiados intentos fallidos. Contacta con la organización.";
    } else if (password_verify($clave, $usuario["clave_hash"])) {

        $sentencia = mysqli_prepare($conexion, "UPDATE usuarios SET intentos_fallidos = 0 WHERE id = ?");
        mysqli_stmt_bind_param($sentencia, "i", $usuario["id"]);
        mysqli_stmt_execute($sentencia);

        session_regenerate_id(true);
        $_SESSION["id"] = $usuario["id"];
        $_SESSION["nombre"] = $usuario["nombre"];
        $_SESSION["tipo"] = $usuario["tipo"];

        header("Location: stands.php");
        exit;
    } else {

        $sentencia = mysqli_prepare($conexion, "UPDATE usuarios SET intentos_fallidos = intentos_fallidos + 1 WHERE id = ?");
        mysqli_stmt_bind_param($sentencia, "i", $usuario["id"]);
        mysqli_stmt_execute($sentencia);

        $error = "Correo o contraseña incorrectos.";
    }
}

$titulo = "Iniciar sesión";
include "cabecera.php";

if ($error != "") {
    echo "<div class='alert alert-danger'>".limpiar($error)."</div>";
}
?>
<div id="FormularioRegistro">
    <form action="login.php" method="post">
        <label for="correo">Correo electrónico</label>
        <input class="form-control mb-3" type="email" id="correo" name="correo" required>

        <label for="clave">Contraseña</label>
        <input class="form-control mb-4" type="password" id="clave" name="clave" required>

        <button type="submit" class="boton1">Entrar</button>
    </form>
</div>
<?php
include "pie.php";
?>
