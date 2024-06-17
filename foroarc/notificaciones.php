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
    <link type="text/css" rel="stylesheet" href="./css/notificaciones.css">
    <title>Notificaciones</title>
</head>
<body>
    <header>
        <?php
        include("fragmentos_php/header.php");
        ?>
    </header>
    <main>
        <div id="contenido">
            <div id="notificaciones">
                <h2>Notificaciones</h2>
                <?php 
                if (isset($_SESSION["id_rol"])) {
                    $nombre_usuario = $_SESSION["username"];
                
                    // Notificaciones no leídas
                    echo '<h3>No leídas</h3>';
                    $stmt = $conexion->prepare("SELECT n.id_notificacion, u.id_usuario, u.nombre_usuario, r.contenido_respuesta, h.titulo_tema, r.id_hilo FROM notificaciones n JOIN respuestas r ON n.id_respuesta = r.id_respuesta JOIN usuarios u ON r.id_usuario = u.id_usuario JOIN hilos h ON r.id_hilo = h.id_hilo WHERE n.id_usuario = ? AND n.leida = 0 ORDER BY r.fecha_respuesta DESC");
                    $stmt->bind_param("i", $_SESSION['id_usuario']);
                    $stmt->execute();
                    $resultado = $stmt->get_result();
                    if ($resultado->num_rows > 0) {
                        while($fila = $resultado->fetch_assoc()) {
                            echo '<div class="notificacion">';
                            echo '<b><a id="enlace" href="mostrarHilo.php?id='.$fila["id_hilo"].'">'.$fila["titulo_tema"].'</a></b>';
                            echo '<p><a id="enlace" href="perfil.php?id_usuario=' . $fila["id_usuario"] . '">' . $fila["nombre_usuario"]. '</a></p>';
                            echo '<p>'.$fila['contenido_respuesta'].'</p>';
                            echo '<form method="POST" action="notificaciones.php">';
                            echo '<input type="hidden" name="id_notificacion" value="' . $fila["id_notificacion"] . '">';
                            echo '<input type="submit" name="marcar_como_leido" value="Marcar como leído">';
                            echo '<input type="submit" name="borrar" value="Borrar">';
                            echo '</form>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>No hay notificaciones no leídas en este momento.</p>';
                    }

                    // Notificaciones leídas
                    echo '<h3>Leídas</h3>';
                    $stmt = $conexion->prepare("SELECT n.id_notificacion, u.id_usuario, u.nombre_usuario, r.contenido_respuesta, h.titulo_tema, r.id_hilo FROM notificaciones n JOIN respuestas r ON n.id_respuesta = r.id_respuesta JOIN usuarios u ON r.id_usuario = u.id_usuario JOIN hilos h ON r.id_hilo = h.id_hilo WHERE n.id_usuario = ? AND n.leida = 1");
                    $stmt->bind_param("i", $_SESSION['id_usuario']);
                    $stmt->execute();
                    $resultado = $stmt->get_result();
                    if ($resultado->num_rows > 0) {
                        while($fila = $resultado->fetch_assoc()) {
                            echo '<div class="notificacion">';
                            echo '<b><a id="enlace" href="mostrarHilo.php?id='.$fila["id_hilo"].'">'.$fila["titulo_tema"].'</a></b>';
                            echo '<p><a id="enlace" href="perfil.php?id_usuario=' . $fila["id_usuario"] . '">' . $fila["nombre_usuario"]. '</a></p>';
                            echo '<p>'.$fila['contenido_respuesta'].'</p>';
                            echo '<form method="POST" action="notificaciones.php">';
                            echo '<input type="hidden" name="id_notificacion" value="' . $fila["id_notificacion"] . '">';
                            echo '<input type="submit" name="borrar" value="Borrar">';
                            echo '</form>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>No hay notificaciones leídas en este momento.</p>';
                    }
                }

                // Aquí recogemos y procesamos los datos del formulario al borrar o marcar como leído
                if (isset($_SESSION['id_rol']) && (isset($_POST['borrar']) || isset($_POST['marcar_como_leido']))) {
                    $id_notificacion = $_POST['id_notificacion'];
                    if (isset($_POST['borrar'])) {
                        $stmt = $conexion->prepare("DELETE FROM notificaciones WHERE id_notificacion = ?");
                    } else { // Marcar como leído
                        $stmt = $conexion->prepare("UPDATE notificaciones SET leida = 1 WHERE id_notificacion = ?");
                    }
                    $stmt->bind_param("i", $id_notificacion);
                    $stmt->execute();
                    echo '<script>window.location.href="notificaciones.php"</script>';
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