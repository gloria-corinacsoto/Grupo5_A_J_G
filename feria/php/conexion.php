<?php
// Este archivo se incluye en todas las páginas con: include "conexion.php";

session_start(); // arranca la sesión: así la web recuerda quién ha iniciado sesión

// Los errores de MySQL no paran la página: los comprobamos nosotros con if
mysqli_report(MYSQLI_REPORT_OFF);

// Datos de la conexión (el usuario feria_app lo creamos en el archivo SQL)
$bd_servidor = "localhost";
$bd_usuario = "feria_app";
$bd_clave = "app_pass";
$bd_nombre = "feria_lsbm";

$conexion = mysqli_connect($bd_servidor, $bd_usuario, $bd_clave, $bd_nombre);

if (!$conexion) {
    die("No se pudo conectar con la base de datos");
}

// Para que las tildes y la ñ se vean bien
mysqli_set_charset($conexion, "utf8mb4");

// Función para mostrar textos de forma segura (evita que se cuele código HTML)
function limpiar($texto){
    if ($texto == null) {
        $texto = "";
    }
    return htmlspecialchars($texto, ENT_QUOTES);
}
?>
