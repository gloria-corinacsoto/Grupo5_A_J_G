<?php
include "conexion.php";

// Si no hay sesión iniciada, mandamos al login
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION["id"];

// ¿Está abierta la votación ahora mismo? (devuelve 1 si está abierta y 0 si no)
$resultado = mysqli_query($conexion, "SELECT COUNT(*) AS abierta FROM configuracion WHERE id = 1 AND NOW() BETWEEN votacion_inicio AND votacion_fin");
$fila = mysqli_fetch_assoc($resultado);
$abierta = $fila["abierta"];

// ¿Ya he votado? Si he votado guardamos el id del stand, y si no, un 0
$mi_voto = 0;
$sentencia = mysqli_prepare($conexion, "SELECT stand_id FROM votos WHERE usuario_id = ?");
mysqli_stmt_bind_param($sentencia, "i", $id_usuario);
mysqli_stmt_execute($sentencia);
$resultado = mysqli_stmt_get_result($sentencia);
$fila = mysqli_fetch_assoc($resultado);
if ($fila != null) {
    $mi_voto = $fila["stand_id"];
}

// Cogemos todos los stands y los guardamos en un array
$resultado = mysqli_query($conexion, "SELECT * FROM stands ORDER BY nombre");
$stands = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

$titulo = "Stands";
include "cabecera.php";

// Mensaje que llega desde votar.php (por ejemplo "¡Voto registrado!")
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
        // Este es el stand que he votado
        echo "<strong>Tu voto</strong>";
    } else if ($stand["spot_url"] != "" && $abierta == 1 && $mi_voto == 0 && $stand["usuario_id"] != $id_usuario) {
        // El botón solo sale si el stand tiene spot, la votación está abierta,
        // todavía no he votado y el stand no es el mío
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
