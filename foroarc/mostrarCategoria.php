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
    <link type="text/css" rel="stylesheet" href="./css/mostrarCategoria.css">
    <title>Visor de Hilos</title>
</head>
<body>
    <header>
        <?php
        include("fragmentos_php/header.php");
        ?>
    </header>
    <main>
        <div id="contenido">
            <div id="hilos">
                <!-- Aquí añadimos el botón para crear un nuevo hilo solo si el usuario está logueado -->
                <?php
                $id_categoria = $_GET['id'];
                $stmt = $conexion->prepare("SELECT nombre_categoria FROM categorias WHERE id_categoria = ?");
                $stmt->bind_param("i", $id_categoria);
                $stmt->execute();
                $resultado = $stmt->get_result();
                if ($resultado->num_rows > 0) {
                    $fila = $resultado->fetch_assoc();
                    $nombre_categoria = $fila["nombre_categoria"];
                    echo "<h2>Hilos en " . $nombre_categoria . "</h2>";
                }
                if (isset($_SESSION["id_rol"])): ?>
                <button onclick="document.getElementById('nuevoHilo').style.display='block'">Crear nuevo hilo</button>
                <!-- Aquí añadimos el formulario para crear un nuevo hilo -->
                <div id="nuevoHilo" style="display:none;">
                    <form method="POST" action="mostrarCategoria.php?id=<?php echo $_GET['id']; ?>">
                        <label for="titulo_tema">Título del hilo:</label><br>
                        <input type="text" id="titulo_tema" name="titulo_tema" maxlength="100" required><br>
                        <label for="contenido_tema">Contenido del hilo:</label><br>
                        <textarea id="contenido_tema" name="contenido_tema" maxlength="10000" required></textarea><br>
                        <input type="submit" name="crear" value="Crear hilo">
                    </form>
                </div>
                <?php endif; ?>
                <div id="listaHilos">
                    <?php
                    // Se obtienen datos de los hilos existentes en la categoría
                    $id_categoria = $_GET['id'];
                    $sql = "SELECT h.id_hilo, u.nombre_usuario, h.id_usuario, h.titulo_tema, h.contenido_tema, h.fecha_publicacion, u.id_rol FROM hilos h JOIN usuarios u ON h.id_usuario=u.id_usuario WHERE id_categoria = $id_categoria ORDER BY fecha_publicacion DESC";
                    $resultado = $conexion->query($sql);
                    if ($resultado->num_rows > 0) {
                        while($fila = $resultado->fetch_assoc()) {
                            echo '<div class="hilo">';
                            echo '<div><a href="mostrarHilo.php?id=' . $fila["id_hilo"] . '"><h2>' . $fila["titulo_tema"]. '</h2><hr></a>';
                            echo '<p> Publicado por <a id="enlace" href="perfil.php?id_usuario='.$fila["id_usuario"].'">' .$fila["nombre_usuario"]. '</a> con fecha '.$fila["fecha_publicacion"].'</p></div>';
                            // Si el usuario está logueado y es un administrador, o es un moderador y el autor del hilo no es un administrador ni un moderador, o el usuario está viendo su propio hilo, mostramos el botón de borrar
                            if (isset($_SESSION['id_rol']) && ($_SESSION['id_rol'] == 1 || ($_SESSION['id_rol'] == 2 && $fila["id_rol"] != 1 && $fila["id_rol"] != 2) || $fila['id_usuario'] == $_SESSION["id_usuario"])) {
                                echo '<form method="POST" action="mostrarCategoria.php?id='.$id_categoria.'">';
                                echo '<input type="hidden" name="id_hilo_borrar" value="' . $fila["id_hilo"] . '">';
                                echo '<input type="hidden" name="id_usuario_hilo_borrar" value="' . $fila["id_usuario"] . '">';
                                echo '<input type="submit" name="borrar" value="Borrar">';
                                echo '</form>';
                            }
                            echo '</div>';                      
                        }
                    } else {
                        echo "No hay hilos en esta categoría.";
                    }                    
                    
                    if (isset($_SESSION['id_rol'])) {
                        // Aquí recogemos y procesamos los datos del formulario al borrar hilo
                        if (isset($_POST['borrar']) && (($_SESSION['id_rol'] == 1 || ($_SESSION['id_rol'] == 2 && $fila["id_rol"] != 1 && $fila["id_rol"] != 2) || $_POST['id_usuario_hilo_borrar'] == $_SESSION["id_usuario"]))) {
                            $id_hilo_borrar = $_POST['id_hilo_borrar'];
                            $stmt = $conexion->prepare("DELETE FROM hilos WHERE id_hilo = ?");
                            $stmt->bind_param("i", $id_hilo_borrar);
                            $stmt->execute();
                            if ($stmt->affected_rows > 0) {
                                // Refrescamos la página
                                echo "<script>window.location.href = 'mostrarCategoria.php?id=$id_categoria';</script>";
                            }
                        }
                        // Aquí recogemos y procesamos los datos del formulario al crear hilo
                        if (isset($_POST['crear'])) {
                            $titulo_tema = $_POST["titulo_tema"];
                            $contenido_tema = $_POST["contenido_tema"];
                            $nombre_usuario = $_SESSION["username"];
                            $id_usuario = $_SESSION['id_usuario'];
                            $fecha_publicacion = date("Y-m-d H:i:s");

                            if (strlen($titulo_tema) > 0 && strlen($titulo_tema) <= 100 && strlen($contenido_tema) > 0 && strlen($contenido_tema) <= 10000) {
                                $stmt = $conexion->prepare("INSERT INTO hilos (id_categoria, id_usuario, titulo_tema, contenido_tema, fecha_publicacion) VALUES (?, ?, ?, ?, ?)");
                                $stmt->bind_param("iisss", $id_categoria, $id_usuario, $titulo_tema, $contenido_tema, $fecha_publicacion);
                                $stmt->execute();
                                // Redirigimos al usuario a mostrarHilo.php con la id del hilo recién creado
                                echo "<script>window.location.href = 'mostrarHilo.php?id=$conexion->insert_id';</script>";
                            }
                        }
                    }
                    ?>
                </div>
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