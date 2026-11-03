<?php
include "conexion.php";

// Cerramos la sesión y volvemos a la página de inicio
session_destroy();
header("Location: ../index.html");
exit;
?>
