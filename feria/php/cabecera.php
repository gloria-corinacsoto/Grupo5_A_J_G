<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title><?php echo limpiar($titulo); ?> - LSBM 2027</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
        <link href="../css/style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <header>
            <p>LSBM <span class="Amarillo">2027</span></p>
            <ul>
                <li><a class="EnlaceMenu" href="../index.html">INICIO</a></li>
                <?php

                if (isset($_SESSION["id"])) {
                    echo "<li><a class='EnlaceMenu' href='stands.php'>STANDS</a></li>";
                    if ($_SESSION["tipo"] == "empresa") {
                        echo "<li><a class='EnlaceMenu' href='mi_stand.php'>MI STAND</a></li>";
                    }
                    echo "<li><a class='boton2' href='logout.php'>Salir</a></li>";
                } else {
                    echo "<li><a class='boton2' href='login.php'>Iniciar sesión</a></li>";
                }
                ?>
            </ul>
        </header>
        <main class="container Seccion">
            <h1 class="TituloSeccion"><?php echo limpiar($titulo); ?></h1>
