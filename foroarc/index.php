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
    <link type="text/css" rel="stylesheet" href="./css/inicio.css">
    <title>Inicio</title>
</head>
<body>
    <header>
        <?php
        include("fragmentos_php/header.php");
        ?>
    </header>
    <main>
        <div id="contenido">
            <div id="categorias">
                <h2>Categorías</h2>
                <?php
                // Si se envió el formulario para crear una nueva categoría
                if (isset($_POST['crear']) && isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
                    $nombre_categoria = trim($_POST["nombre_categoria"]);
                    $descripcion = trim($_POST["descripcion"]);
                
                    if (strlen($nombre_categoria) > 255) {
                        echo "El nombre de la categoría excede el límite de 255 caracteres.";
                        return;
                    }
                
                    if (strlen($descripcion) > 1000) {
                        echo "La descripción excede el límite de 1000 caracteres.";
                        return;
                    }

                    if (strlen($nombre_categoria) < 1) {
                        echo "El nombre de la categoría debe contener al menos 1 carácter.";
                        return;
                    }

                    $stmt = $conexion->prepare("INSERT INTO categorias (nombre_categoria, descripcion) VALUES (?, ?)");
                    $stmt->bind_param("ss", $nombre_categoria, $descripcion);
                    $stmt->execute();
                    echo '<script>window.location.href="index.php";</script>';
                }                

                // Consultar las categorías disponibles en el foro
                $sql = "SELECT id_categoria, nombre_categoria, descripcion FROM categorias";
                $resultado = $conexion->query($sql);
                
                if ($resultado->num_rows > 0) {
                    while($fila = $resultado->fetch_assoc()) {
                        echo '<div class="enlaceCategoria"><a href="mostrarCategoria.php?id=' . $fila["id_categoria"] . '">';
                        echo '<h2>' . $fila["nombre_categoria"]. '<hr></h2>';
                        echo '<p>' . $fila["descripcion"]. '</p>';
                        echo '</a>';
                
                        // Si el usuario está logueado y es un administrador, mostramos el botón de borrar
                        if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
                            echo '<form method="POST" action="index.php">';
                            echo '<input type="hidden" name="id_categoria_borrar" value="' . $fila["id_categoria"] . '">';
                            echo '<input type="submit" name="borrar" value="Borrar">';
                            echo '</form>';
                        }
                
                        echo '</div>';
                    }
                } else {
                    echo "No hay categorías.";
                }

                // Si se pulsa el botón de borrar, se borra la categoría de la base de datos
                if (isset($_SESSION['id_rol'])) {
                    if (isset($_POST['borrar']) && $_SESSION['id_rol'] == 1) {
                        $id_categoria_borrar = $_POST['id_categoria_borrar'];
                        $stmt = $conexion->prepare("DELETE FROM categorias WHERE id_categoria = ?");
                        $stmt->bind_param("i", $id_categoria_borrar);
                        $stmt->execute();
                    
                        // Recargamos la página para que se vea que la categoría ha sido borrada
                        echo "<script>window.location.href = 'index.php';</script>";
                    }
                
                    if ($_SESSION['id_rol'] == 1): // Si el usuario es un administrador ?>
                        <button onclick="document.getElementById('nuevaCategoria').style.display='block'">Crear nueva categoría</button>
                        <!-- Aquí añadimos el formulario para crear una nueva categoría -->
                        <div id="nuevaCategoria" style="display:none;">
                            <form method="POST" action="index.php">
                                <label for="nombre_categoria">Nombre de la categoría:</label><br>
                                <input type="text" id="nombre_categoria" name="nombre_categoria" maxlength="255" required><br>
                                <label for="descripcion">Descripción:</label><br>
                                <textarea id="descripcion" name="descripcion" maxlength="1000"></textarea><br>
                                <input type="submit" name="crear" value="Crear categoría">
                            </form>
                        </div>
                    <?php endif; ?>
                <?php } ?>                    
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
