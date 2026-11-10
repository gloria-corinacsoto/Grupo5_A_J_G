<?php
include "conexion.php";

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION["id"];

$resultado = mysqli_query($conexion, "SELECT COUNT(*) AS abierta FROM configuracion WHERE id = 1 AND NOW() BETWEEN votacion_inicio AND votacion_fin");
$fila = mysqli_fetch_assoc($resultado);
$abierta = $fila["abierta"];

$mi_voto = 0;
$sentencia = mysqli_prepare($conexion, "SELECT stand_id FROM votos WHERE usuario_id = ?");
mysqli_stmt_bind_param($sentencia, "i", $id_usuario);
mysqli_stmt_execute($sentencia);
$resultado = mysqli_stmt_get_result($sentencia);
$fila = mysqli_fetch_assoc($resultado);
if ($fila != null) {
    $mi_voto = $fila["stand_id"];
}

$resultado = mysqli_query($conexion, "SELECT * FROM stands ORDER BY nombre");
$stands = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

$titulo = "Stands";
include "cabecera.php";

if (isset($_GET["mensaje"])) {
    echo "<div class='alert alert-info'>".limpiar($_GET["mensaje"])."</div>";
}

if (count($stands) == 0) {
    echo "<p>Todavía no hay stands publicados.</p>";
}

echo "<div class='row g-4'>";
foreach ($stands as $stand) {
    if ($stand["horario"] == "manana") {
        $horario = "mañana";
    } else {
        $horario = "tarde";
    }

    echo "<div class='col-md-6 col-lg-4'>";
    echo "<div class='tarjeta'>";
    echo "<img src='".limpiar($stand["logo_url"])."' alt='Logotipo' style='max-height:70px; max-width:100%;'>";
    echo "<h2 class='h5 mt-3'>".limpiar($stand["nombre"])."</h2>";
    echo "<p>Horario: ".$horario."</p>";
    echo "<p><a href='".limpiar($stand["web_url"])."' target='_blank'>Web</a> · ";
    echo "<a href='".limpiar($stand["videoconferencia_url"])."' target='_blank'>Videoconferencia</a>";
    if ($stand["spot_url"] != "") {
        echo " · <a href='".limpiar($stand["spot_url"])."' target='_blank'>Ver spot</a>";
    }
    echo "</p>";

    if ($mi_voto == $stand["id"]) {

        echo "<strong>Tu voto</strong>";
    } else if ($stand["spot_url"] != "" && $abierta == 1 && $mi_voto == 0 && $stand["usuario_id"] != $id_usuario) {

        echo "<form action='votar.php' method='post'>";
        echo "<input type='hidden' name='stand_id' value='".$stand["id"]."'>";
        echo "<button type='submit' class='boton1'>Votar</button>";
        echo "</form>";
    }

    echo "</div>";
    echo "</div>";
}
echo "</div>";

if ($abierta == 0) {
    echo "<p class='mt-4'>La votación está cerrada o todavía no ha empezado.</p>";
}

include "pie.php";
?>
