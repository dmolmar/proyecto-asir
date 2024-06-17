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
    <link type="text/css" rel="stylesheet" href="./css/mostrarHilo.css">
    <title>Visor de Hilo</title>
</head>
<body>
    <header>
        <?php
        include("fragmentos_php/header.php");
        ?>
    </header>
    <main>
        <div id="contenido">
            <div id="hilo">
            <?php
                // Consulta para obtener información del hilo
                $id_hilo = $_GET['id'];
                $sql = "SELECT h.id_hilo, c.id_categoria, c.nombre_categoria, h.id_usuario, h.titulo_tema, h.contenido_tema, h.fecha_publicacion, u.nombre_usuario, u.cita, u.avatar, r.id_rol, r.nombre as nombre_rol
                    FROM hilos h 
                    JOIN categorias c ON h.id_categoria = c.id_categoria 
                    JOIN usuarios u ON h.id_usuario = u.id_usuario
                    JOIN roles_usuario r ON u.id_rol = r.id_rol
                    WHERE h.id_hilo = $id_hilo";
                $resultado = $conexion->query($sql);
                
                if ($resultado->num_rows > 0) {
                    $fila = $resultado->fetch_assoc();
                    echo '<h2>' . $fila["titulo_tema"]. ' | <a id="enlace" href="mostrarCategoria.php?id='.$fila['id_categoria'].'">' . $fila["nombre_categoria"]. '</a></h2>';
                    echo "<div id='contenedor_hilo'>";
                    echo "<div id='usuario_hilo'><div id='info_usuario'>";
                    if (!empty($fila["avatar"])) {
                        echo '<img src="data:image/jpeg;base64,'.base64_encode( $fila['avatar'] ).'"/>';
                    }
                    echo '<div><p><a id="enlace" href="perfil.php?id_usuario=' . $fila['id_usuario'] . '">' . $fila["nombre_usuario"]. '</a></p>';
                    echo '<p> Rol: ' . $fila["nombre_rol"]. '</p></div>';
                    echo "</div></div><hr>";
                    echo "<div id='contenido_hilo'>";
                    echo '<p>' . $fila["contenido_tema"]. '</p>';
                    echo "</div><hr>";
                    echo "<div id='info'>";
                    if (!empty($fila["cita"])) {
                        echo '<p>'.$fila["cita"]. '</p>';
                    }
                    echo '<p>'. $fila["fecha_publicacion"]. '</p>';
                    echo "</div>";
                    echo "</div>";
                ?>
                    <?php if (isset($_SESSION["id_rol"])): ?>
                        <button onclick="document.getElementById('nuevaRespuesta').style.display='block'">Responder al hilo</button>
                        <div id="nuevaRespuesta" style="display:none;">
                            <form method="POST" action="mostrarHilo.php?id=<?php echo $_GET['id']; ?>">
                                <label for="contenido_respuesta">Contenido de la respuesta:</label><br>
                                <textarea id="contenido_respuesta" name="contenido_respuesta" maxlength="10000" required></textarea><br>
                                <input type="submit" value="Responder">
                            </form>
                        </div>
                    <?php endif; ?>
                    <?php
                    // Consulta para extraer info de los usuarios que responden a los hilos
                    $sql_respuestas = "SELECT r.id_respuesta, r.id_hilo, r.id_usuario, r.contenido_respuesta, r.fecha_respuesta, r.respondiendo, r.id_respuesta_referencia, u.id_rol, ru.nombre as nombre_rol, u.nombre_usuario, u.cita, u.avatar
                        FROM respuestas r 
                        JOIN usuarios u ON r.id_usuario = u.id_usuario
                        JOIN roles_usuario ru ON u.id_rol = ru.id_rol
                        WHERE r.id_hilo = $id_hilo ORDER BY r.fecha_respuesta ASC";

                    $resultado_respuestas = $conexion->query($sql_respuestas);

                    if ($resultado_respuestas->num_rows > 0) {
                        while($fila_respuesta = $resultado_respuestas->fetch_assoc()) {
                            $clase = $fila_respuesta["respondiendo"] == 1 ? 'visible' : 'hidden';
                            $id_usuario_respuesta = $fila_respuesta["id_usuario"];
                            echo "<div id='contenedor_hilo'>";
                            echo "<div id='usuario_hilo'><div id='info_usuario'>";
                            if (!empty($fila_respuesta["avatar"])) {
                                echo '<img src="data:image/jpeg;base64,'.base64_encode( $fila_respuesta['avatar'] ).'"/>';
                            }
                            echo '<div><p><a id="enlace" href="perfil.php?id_usuario=' . $fila_respuesta['id_usuario'] . '">' . $fila_respuesta["nombre_usuario"]. '</a></p>';
                            echo '<p> Rol: ' . $fila_respuesta["nombre_rol"]. '</p></div>';
                            echo "</div><div id='opciones_respuesta'>";
                            // Si el usuario ha iniciado sesión, mostrar el botón de responder
                            if (isset($_SESSION["id_rol"])) {
                                echo '<button onclick="document.getElementById(\'respuesta-' . $fila_respuesta["id_respuesta"] . '\').style.display=\'block\'">Responder</button>';
                                echo '<div id="respuesta-' . $fila_respuesta["id_respuesta"] . '" style="display:none;">';
                                echo '<form method="POST" action="mostrarHilo.php?id=' . $id_hilo . '">';
                                echo '<label for="contenido_respuesta">Contenido de la respuesta:</label><br>';
                                echo '<textarea id="contenido_respuesta" name="contenido_respuesta" maxlength="10000" required></textarea><br>';
                                echo '<input type="hidden" name="id_respuesta" value="' . $fila_respuesta["id_respuesta"] . '">';
                                echo '<input type="submit" value="Responder">';
                                echo '</form>';
                                echo '</div>';
                            }
                            if (isset($_SESSION["id_rol"])) {
                                $nombre_usuario = $_SESSION["username"];
                            
                                // Si el usuario está logueado y es un administrador, o es un moderador y el autor de la respuesta no es un administrador ni un moderador, o el usuario es el autor de la respuesta, mostramos el botón de borrar
                                if ($_SESSION['id_rol'] == 1 || ($_SESSION['id_rol'] == 2 && $fila_respuesta["id_rol"] != 1 && $fila_respuesta["id_rol"] != 2) || $id_usuario_respuesta == $_SESSION["id_usuario"]) {
                                    echo '<form method="POST" action="">';
                                    echo '<input type="hidden" name="id_respuesta_borrar" value="' . $fila_respuesta["id_respuesta"] . '">';
                                    echo '<input type="submit" name="borrar" value="Borrar">';
                                    echo '</form>';
                                }
                            }
                            echo "</div></div><hr>";
                            echo "<div id='contenido_hilo'><div id='contenido_respuesta' class=".$clase.">";
                            if ($fila_respuesta["respondiendo"] == 1) {
                                if ($fila_respuesta["id_respuesta_referencia"] != null) {
                                    // Realizamos una consulta adicional para obtener el contenido de la respuesta referenciada
                                    $stmt_referencia = $conexion->prepare("SELECT contenido_respuesta, nombre_usuario, r.id_usuario FROM respuestas r JOIN usuarios u ON r.id_usuario = u.id_usuario WHERE id_respuesta = ?");
                                    $stmt_referencia->bind_param("i", $fila_respuesta["id_respuesta_referencia"]);
                                    $stmt_referencia->execute();
                                    $resultado_referencia = $stmt_referencia->get_result();
                                    if ($resultado_referencia->num_rows > 0) {
                                        $fila_referencia = $resultado_referencia->fetch_assoc();
                                        echo '<p><a id="enlace" href="perfil.php?id_usuario=' . $fila_referencia["id_usuario"] . '">' . $fila_referencia["nombre_usuario"] . ' dijo:</a> ' . $fila_referencia["contenido_respuesta"] . '</p>';
                                    }
                                } else {
                                    echo '<p>¡MENSAJE BORRADO!</p>';
                                }
                            }
                            echo '</div><p>' . $fila_respuesta["contenido_respuesta"]. '</p>';
                            echo "</div><hr>";
                            echo "<div id='info'>";
                            if (!empty($fila_respuesta["cita"])) {
                                echo '<p>'.$fila_respuesta["cita"]. '</p>';
                            }
                            echo '<p>'.$fila_respuesta["fecha_respuesta"]. '</p>';
                            echo "</div>";
                            echo "</div>";
                        }
                    } else {
                        echo "No hay respuestas a este hilo.";
                    }
                } else {
                    echo "No se encontró el hilo.";
                }

                // Aquí recogemos y procesamos los datos del formulario
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["id_rol"])) {
                    $nombre_usuario = $_SESSION["username"];

                    // Si el usuario ha hecho clic en el botón de borrar, eliminamos la respuesta de la base de datos
                    if (isset($_POST['borrar']) && ($_SESSION['id_rol'] == 1 || ($_SESSION['id_rol'] == 2 && $fila_respuesta["id_rol"] != 1 && $fila_respuesta["id_rol"] != 2) || $id_usuario_respuesta == $_SESSION["id_usuario"])) {
                        $id_respuesta_borrar = $_POST['id_respuesta_borrar'];

                        // Primero, actualizamos las referencias a la respuesta que vamos a borrar
                        $stmt = $conexion->prepare("UPDATE respuestas SET id_respuesta_referencia = NULL WHERE id_respuesta_referencia = ?");
                        $stmt->bind_param("i", $id_respuesta_borrar);
                        $stmt->execute();

                        // Luego, eliminamos la respuesta
                        $stmt = $conexion->prepare("DELETE FROM respuestas WHERE id_respuesta = ?");
                        $stmt->bind_param("i", $id_respuesta_borrar);
                        $stmt->execute();

                        // Recargamos la página para que se vea que la respuesta ha sido borrada
                        echo "<script>window.location.href = 'mostrarHilo.php?id=$id_hilo';</script>";
                    }

                    // Aquí recogemos y procesamos los datos del formulario
                    $contenido_respuesta = $_POST["contenido_respuesta"];
                    $id_respuesta_referencia = isset($_POST["id_respuesta"]) ? $_POST["id_respuesta"] : null; // La id de la respuesta a la que se está respondiendo

                    // Establecemos el valor de 'respondiendo' dependiendo de si 'id_respuesta' está establecido o no
                    $respondiendo = isset($_POST["id_respuesta"]) ? 1 : 0;

                    $fecha_respuesta = date("Y-m-d H:i:s");

                    if (strlen($contenido_respuesta) <= 10000) {
                        $stmt = $conexion->prepare("INSERT INTO respuestas (id_hilo, id_usuario, id_respuesta_referencia, contenido_respuesta, fecha_respuesta, respondiendo) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("iiissi", $id_hilo, $_SESSION['id_usuario'], $id_respuesta_referencia, $contenido_respuesta, $fecha_respuesta, $respondiendo);
                        $stmt->execute();
                        $id_respuesta_nueva = $stmt->insert_id; // Aquí obtenemos el ID de la respuesta que acabamos de insertar

                        // Primero, obtenemos el id_usuario de la respuesta a la que se está respondiendo
                        if ($id_respuesta_referencia != null) {
                            $stmt = $conexion->prepare("SELECT id_usuario FROM respuestas WHERE id_respuesta = ?");
                            $stmt->bind_param("i", $id_respuesta_referencia);
                            $stmt->execute();
                            $resultado = $stmt->get_result();
                            if ($resultado->num_rows > 0) {
                                $fila = $resultado->fetch_assoc();
                                $id_usuario_notificacion = $fila["id_usuario"]; // Este es el id_usuario de la persona a la que se está respondiendo
                            }
                        } else {
                            // Aquí obtenemos el id_usuario del creador del hilo
                            $stmt = $conexion->prepare("SELECT id_usuario FROM hilos WHERE id_hilo = ?");
                            $stmt->bind_param("i", $id_hilo);
                            $stmt->execute();
                            $resultado = $stmt->get_result();
                            if ($resultado->num_rows > 0) {
                                $fila = $resultado->fetch_assoc();
                                $id_usuario_notificacion = $fila["id_usuario"]; // Este es el id_usuario del creador del hilo
                            }
                        }
                        $stmt_notificacion = $conexion->prepare("INSERT INTO notificaciones (id_usuario, id_respuesta, leida) VALUES (?, ?, 0)");
                        $stmt_notificacion->bind_param("ii", $id_usuario_notificacion, $id_respuesta_nueva);
                        $stmt_notificacion->execute();
                        echo "<script>window.location.href = 'mostrarHilo.php?id=$id_hilo';</script>"; 
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