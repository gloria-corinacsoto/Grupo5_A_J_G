<?php
// Conexión a MySQL. Si cambias la clave en el SQL, cámbiala también aquí.
$servidor = "localhost";
$base     = "feria_lsbm";
$usuario  = "feria";
$clave    = "cambia_esta_clave";

try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$base;charset=utf8mb4", $usuario, $clave);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("No se pudo conectar con la base de datos.");
}
