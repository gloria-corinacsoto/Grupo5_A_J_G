<?php
include "conexion.php";

session_destroy();
header("Location: ../index.html");
exit;
?>
