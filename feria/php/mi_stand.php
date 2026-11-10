<?php
include "conexion.php";

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION["id"];
$mensaje = "";

if ($_SESSION["tipo"] != "empresa") {
    $titulo = "Mi stand";
    include "cabecera.php";
    echo "<p>Solo las empresas tienen stand.</p>";
    include "pie.php";
    exit;
}

$sentencia = mysqli_prepare($conexion, "SELECT * FROM stands WHERE usuario_id = ?");
mysqli_stmt_bind_param($sentencia, "i", $id_usuario);
mysqli_stmt_execute($sentencia);
$resultado = mysqli_stmt_get_result($sentencia);
$stand = mysqli_fetch_assoc($resultado);

if ($stand == null) {

    $stand_existe = false;
    $stand = array("nombre"=>"", "logo_url"=>"", "web_url"=>"", "videoconferencia_url"=>"", "horario"=>"manana", "spot_url"=>"");
} else {
    $stand_existe = true;
}

if (isset($_POST["nombre"]) && isset($_POST["logo_url"]) && isset($_POST["web_url"]) && isset($_POST["videoconferencia_url"]) && isset($_POST["horario"]) && isset($_POST["spot_url"])) {

    $nombre = trim($_POST["nombre"]);
    $logo_url = trim($_POST["logo_url"]);
    $web_url = trim($_POST["web_url"]);
    $videoconferencia_url = trim($_POST["videoconferencia_url"]);
    $horario = $_POST["horario"];
    $spot_url = trim($_POST["spot_url"]);

    $stand["nombre"] = $nombre;
    $stand["logo_url"] = $logo_url;
    $stand["web_url"] = $web_url;
    $stand["videoconferencia_url"] = $videoconferencia_url;
    $stand["horario"] = $horario;
    $stand["spot_url"] = $spot_url;

    $datos_ok = true;
    if ($nombre == "") { $datos_ok = false; }
    if (!filter_var($logo_url, FILTER_VALIDATE_URL)) { $datos_ok = false; }
    if (!filter_var($web_url, FILTER_VALIDATE_URL)) { $datos_ok = false; }
    if (!filter_var($videoconferencia_url, FILTER_VALIDATE_URL)) { $datos_ok = false; }

    if ($spot_url != "" && !filter_var($spot_url, FILTER_VALIDATE_URL)) { $datos_ok = false; }
    if ($horario != "manana" && $horario != "tarde") { $datos_ok = false; }

    if (!$datos_ok) {
        $mensaje = "Revisa los datos: faltan campos o alguna dirección web no es válida.";
    } else {

        $spot_bd = $spot_url;
        if ($spot_bd == "") {
            $spot_bd = null;
        }

        if ($stand_existe) {

            $sentencia = mysqli_prepare($conexion, "UPDATE stands SET nombre = ?, logo_url = ?, web_url = ?, videoconferencia_url = ?, horario = ?, spot_url = ? WHERE usuario_id = ?");
            mysqli_stmt_bind_param($sentencia, "ssssssi", $nombre, $logo_url, $web_url, $videoconferencia_url, $horario, $spot_bd, $id_usuario);
        } else {

            $sentencia = mysqli_prepare($conexion, "INSERT INTO stands (usuario_id, nombre, logo_url, web_url, videoconferencia_url, horario, spot_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($sentencia, "issssss", $id_usuario, $nombre, $logo_url, $web_url, $videoconferencia_url, $horario, $spot_bd);
        }

        if (mysqli_stmt_execute($sentencia)) {
            $mensaje = "Stand guardado.";
            $stand_existe = true;
        } else {
            $mensaje = "No se pudo guardar el stand.";
        }
    }
}

$titulo = "Mi stand";
include "cabecera.php";

if ($mensaje != "") {
    echo "<div class='alert alert-info'>".limpiar($mensaje)."</div>";
}
?>
<div id="FormularioRegistro">
    <form action="mi_stand.php" method="post">
        <label for="nombre">Nombre del stand</label>
        <input class="form-control mb-3" type="text" id="nombre" name="nombre" value="<?php echo limpiar($stand["nombre"]); ?>" required>

        <label for="logo_url">Dirección del logotipo (URL de la imagen)</label>
        <input class="form-control mb-3" type="text" id="logo_url" name="logo_url" value="<?php echo limpiar($stand["logo_url"]); ?>" required>

        <label for="web_url">Página web</label>
        <input class="form-control mb-3" type="text" id="web_url" name="web_url" value="<?php echo limpiar($stand["web_url"]); ?>" required>

        <label for="videoconferencia_url">Enlace de videoconferencia (recepción)</label>
        <input class="form-control mb-3" type="text" id="videoconferencia_url" name="videoconferencia_url" value="<?php echo limpiar($stand["videoconferencia_url"]); ?>" required>

        <label for="horario">Horario de atención</label>
        <select class="form-select mb-3" id="horario" name="horario">
            <option value="manana" <?php if ($stand["horario"] == "manana") { echo "selected"; } ?>>Mañana</option>
            <option value="tarde" <?php if ($stand["horario"] == "tarde") { echo "selected"; } ?>>Tarde</option>
        </select>

        <label for="spot_url">Spot publicitario (URL del vídeo, opcional)</label>
        <input class="form-control mb-4" type="text" id="spot_url" name="spot_url" value="<?php echo limpiar($stand["spot_url"]); ?>">

        <button type="submit" class="boton1">Guardar stand</button>
    </form>
</div>
<?php
include "pie.php";
?>
