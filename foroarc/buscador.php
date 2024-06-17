<?php
    include("conexion.php");
    $conexion->select_db("foroarc");

    if(session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;800&display=swap" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="./css/header.css">
    <link type="text/css" rel="stylesheet" href="./css/footer.css">
    <link type="text/css" rel="stylesheet" href="./css/buscador.css">
    <title>Buscador</title>
</head>
<body>
    <header>
        <?php
        include("fragmentos_php/header.php");
        ?>
    </header>
    <main>
        <div id="contenido">
            <div id="resultados">
                <h2>Resultados de búsqueda</h2>
                <?php
                if (isset($_GET['buscador'])) {
                    $busqueda = '%' . trim($_GET['buscador']) . '%';
                    if (!strlen($busqueda > 502)) {
                        // Buscar usuarios
                        echo '<h3>Usuarios</h3>';
                        $stmt = $conexion->prepare("SELECT id_usuario, nombre_usuario FROM usuarios WHERE nombre_usuario LIKE ?");
                        $stmt->bind_param("s", $busqueda);
                        $stmt->execute();
                        $resultado = $stmt->get_result();
                        if ($resultado->num_rows > 0) {
                            while ($fila = $resultado->fetch_assoc()) {
                                echo '<p><a id="enlace" href="perfil.php?id_usuario=' . $fila["id_usuario"] . '">' . $fila["nombre_usuario"] . '</a></p>';
                            }
                        } else {
                            echo '<p>No se encontraron usuarios.</p>';
                        }

                        // Buscar hilos
                        echo '<h3>Hilos</h3>';
                        $stmt = $conexion->prepare("SELECT id_hilo, titulo_tema FROM hilos WHERE titulo_tema LIKE ? OR contenido_tema LIKE ?");
                        $stmt->bind_param("ss", $busqueda, $busqueda);
                        $stmt->execute();
                        $resultado = $stmt->get_result();
                        if ($resultado->num_rows > 0) {
                            while ($fila = $resultado->fetch_assoc()) {
                                echo '<p><a id="enlace" href="mostrarHilo.php?id=' . $fila["id_hilo"] . '">' . $fila["titulo_tema"] . '</a></p>';
                            }
                        } else {
                            echo '<p>No se encontraron hilos.</p>';
                        }

                        // Buscar respuestas
                        echo '<h3>Respuestas</h3>';
                        $stmt = $conexion->prepare("SELECT id_respuesta, id_hilo, contenido_respuesta FROM respuestas WHERE contenido_respuesta LIKE ?");
                        $stmt->bind_param("s", $busqueda);
                        $stmt->execute();
                        $resultado = $stmt->get_result();
                        if ($resultado->num_rows > 0) {
                            while ($fila = $resultado->fetch_assoc()) {
                                echo '<p><a id="enlace" href="mostrarHilo.php?id=' . $fila["id_hilo"] . '">' . $fila["contenido_respuesta"] . '</a></p>';
                            }
                        } else {
                            echo '<p>No se encontraron respuestas.</p>';
                        }
                    }
                }
                ?>
            </div>
            <div id="extra">
                <?php include("fragmentos_php/extra.php"); ?>
            </div>
        </div>
    </main>
    <footer>
        <?php include("fragmentos_php/footer.php"); ?>
    </footer>
</body>
</html>