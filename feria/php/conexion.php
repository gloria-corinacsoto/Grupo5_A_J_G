<?php

session_start();

mysqli_report(MYSQLI_REPORT_OFF);

$bd_servidor = "localhost";
$bd_usuario = "feria_app";
$bd_clave = "app_pass";
$bd_nombre = "feria_lsbm";

$conexion = mysqli_connect($bd_servidor, $bd_usuario, $bd_clave, $bd_nombre);

if (!$conexion) {
    die("No se pudo conectar con la base de datos");
}

mysqli_set_charset($conexion, "utf8mb4");

function limpiar($texto){
    if ($texto == null) {
        $texto = "";
    }
    return htmlspecialchars($texto, ENT_QUOTES);
}
?>
