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
    <link type="text/css" rel="stylesheet" href="./css/perfil.css">
    <title>Perfil</title>
</head>
<body>
    <header>
        <?php
        include("fragmentos_php/header.php");
        ?>
    </header>
    <main>
        <div id="contenido">
            <div id="perfil">
                <?php
                if (isset($_GET['id_usuario'])) {
                    $id_usuario = $_GET['id_usuario'];
                } else {
                    $id_usuario = $_SESSION["id_usuario"];
                }
                $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
                $stmt->bind_param("i", $id_usuario);
                $stmt->execute();
                $resultado = $stmt->get_result();
                if ($resultado->num_rows > 0) {
                    $fila = $resultado->fetch_assoc();
                    echo '<h2>Perfil de ' . $fila["nombre_usuario"]. '</h2>';

                    // Obtener el número de respuestas del usuario
                    $stmt_respuestas = $conexion->prepare("SELECT COUNT(*) AS num_respuestas FROM respuestas WHERE id_usuario = ?");
                    $stmt_respuestas->bind_param("i", $id_usuario);
                    $stmt_respuestas->execute();
                    $resultado_respuestas = $stmt_respuestas->get_result();
                    $fila_respuestas = $resultado_respuestas->fetch_assoc();
                    $num_respuestas = $fila_respuestas["num_respuestas"];

                    // Obtener el número de hilos creados por el usuario
                    $stmt_hilos = $conexion->prepare("SELECT COUNT(*) AS num_hilos FROM hilos WHERE id_usuario = ?");
                    $stmt_hilos->bind_param("i", $id_usuario);
                    $stmt_hilos->execute();
                    $resultado_hilos = $stmt_hilos->get_result();
                    $fila_hilos = $resultado_hilos->fetch_assoc();
                    $num_hilos = $fila_hilos["num_hilos"];

                    // Suma el número de respuestas y hilos para obtener el total de mensajes publicados
                    $contador_mensajes = $num_respuestas + $num_hilos;
                    echo '<p>Número de mensajes: ' . $contador_mensajes. '</p>';

                    // Mostrar tabla de perfil
                    echo '<table>';
                    echo '<tr><td><b>Correo electrónico:</b></td><td>' . $fila["correo_electronico"]. '</td>';
                    echo '<tr><td><b>Cita:</b></td><td>' . $fila["cita"]. '</td>';
                    echo '<tr><td><b>Biografía:</b></td><td>' . $fila["biografia"]. '</td>';
                    echo '<tr><td><b>Ubicación:</b></td><td>' . $fila["ubicacion"]. '</td>';
                    echo '<tr><td><b>Fecha de nacimiento:</b></td><td>' . $fila["fecha_nacimiento"]. '</td>';
                    echo '<tr><td><b>Avatar:</b></td><td>';
                    if (!empty($fila["avatar"])) {
                        echo '<img src="data:image/jpeg;base64,'.base64_encode( $fila['avatar'] ).'"/>';
                    }
                    echo '</td></tr>';
                    echo '</table>';

                        if (isset($_SESSION["id_rol"])) {
                        // Si el usuario está viendo su propio perfil, mostrar el formulario para editar el perfil
                        if ($id_usuario == $_SESSION["id_usuario"]) {
                            echo '<form method="POST" action="perfil.php?id_usuario=' . $id_usuario . '" enctype="multipart/form-data">';
                            echo '<table>';
                            echo '<tr><td><input type="email" id="correo_electronico" name="correo_electronico" value="' . $fila["correo_electronico"] . '" maxlength="255" placeholder="Correo electrónico" required></td></tr>';
                            echo '<tr><td><input type="text" id="cita" name="cita" value="' . $fila["cita"] . '" maxlength="100" placeholder="Cita"></td></tr>';
                            echo '<tr><td><textarea id="biografia" name="biografia" maxlength="10000" placeholder="Biografía">' . $fila["biografia"] . '</textarea></td></tr>';
                            echo '<tr><td><input type="text" id="ubicacion" name="ubicacion" value="' . $fila["ubicacion"] . '" maxlength="255" placeholder="Ubicación"></td></tr>';
                            echo '<tr><td><input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="' . $fila["fecha_nacimiento"] . '" placeholder="Fecha de nacimiento"></td></tr>';
                            echo '<tr><td>';
                            echo '<input type="radio" id="modificar_avatar" name="opcion_avatar" value="modificar">';
                            echo '<label for="modificar_avatar">Modificar avatar</label><br>';
                            echo '<input type="radio" id="no_modificar_avatar" name="opcion_avatar" value="no_modificar" checked>';
                            echo '<label for="no_modificar_avatar">No modificar avatar</label>';
                            echo '<input type="file" id="avatar" name="avatar" accept=".jpg,.jpeg,.png,.webm" /><br>';
                            echo '</td></tr>';
                            echo '</table>';
                            echo '<input type="submit" name="actualizar" value="Actualizar perfil">';
                            echo '</form>';
                        }

                        echo "<div id='extra_forms' style='visibility:hidden'>";
                        // Si el usuario es un administrador, mostrar el formulario para cambiar el nivel de privilegios
                        if ($_SESSION['id_rol'] == 1) {
                            echo '<form method="POST" action="perfil.php?id_usuario=' . $id_usuario . '">';
                            echo "<script>document.getElementById('extra_forms').style.visibility = 'visible';</script>";
                            echo '<label for="id_rol">Nivel de privilegios:</label><br>';
                            echo '<select id="id_rol" name="id_rol">';
                            echo '<option value="1"' . ($fila["id_rol"] == 1 ? " selected" : "") . '>Administrador</option>';
                            echo '<option value="2"' . ($fila["id_rol"] == 2 ? " selected" : "") . '>Moderador</option>';
                            echo '<option value="3"' . ($fila["id_rol"] == 3 ? " selected" : "") . '>Usuario</option>';
                            echo '</select><br>';
                            echo '<input type="submit" name="cambiar_privilegios" value="Modificar privilegios">';
                            echo '</form>';
                            
                        }

                        // Si el usuario es un administrador o un moderador, y el usuario del perfil no es un administrador o moderador, mostrar el formulario para banear/desbanear
                        if ($_SESSION['id_rol'] == 1 || $_SESSION['id_rol'] == 2 && $fila["id_rol"] != 1 && $fila["id_rol"] != 2) {
                            echo '<form method="POST" action="perfil.php?id_usuario=' . $id_usuario . '">';
                            echo "<script>document.getElementById('extra_forms').style.visibility = 'visible';</script>";
                            echo '<label for="estado">Estado de la cuenta:</label><br>';
                            echo '<input type="radio" id="estado_activo" name="estado" value="1"' . ($fila["estado"] == 1 ? " checked" : "") . '><label for="estado_activo">Cuenta activa</label><br>';
                            echo '<input type="radio" id="estado_baneado" name="estado" value="0"' . ($fila["estado"] == 0 ? " checked" : "") . '><label for="estado_baneado">Cuenta baneada</label><br>';
                            echo '<input type="submit" name="cambiar_estado" value="Modificar estado">';
                            echo '</form>';
                        }
                        echo "</div>";
                    }
                } else {
                    echo "No se encontró el usuario.";
                }

                if (isset($_SESSION['id_rol'])) {
                    // Actualizar el perfil del usuario
                    if (isset($_POST['actualizar']) && $id_usuario == $_SESSION["id_usuario"]) {
                        $correo_electronico = trim($_POST["correo_electronico"]);
                        $cita = trim($_POST["cita"]);
                        $biografia = trim($_POST["biografia"]);
                        $ubicacion = trim($_POST["ubicacion"]);
                        $fecha_nacimiento = trim($_POST["fecha_nacimiento"]);
                        $avatar = NULL;
                
                        if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
                            echo "El formato del correo electrónico no es válido.";
                            return;
                        }
                
                        if (strlen($correo_electronico) > 255 || strlen($cita) > 100 || strlen($biografia) > 10000 || strlen($ubicacion) > 255) {
                            echo "Uno de los campos excede el límite de caracteres permitido.";
                            return;
                        }
                
                        // Verificar si se ha seleccionado la opción "Modificar avatar"
                        if (isset($_POST["opcion_avatar"]) && $_POST["opcion_avatar"] == "modificar") {
                            if (isset($_FILES["avatar"]) && $_FILES["avatar"]["size"] <= 16777215 && in_array(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION), array("jpg", "jpeg", "png", "webm"))) {
                                $avatar = file_get_contents($_FILES["avatar"]["tmp_name"]);
                            } else {
                                // Si se seleccionó "Modificar avatar" pero no se subió ningún archivo, establecer $avatar en una cadena vacía para borrar el avatar existente
                                $avatar = "";
                            }
                        } else {
                            // Si se seleccionó "No modificar", mantener el avatar actual
                            $stmt = $conexion->prepare("SELECT avatar FROM usuarios WHERE id_usuario = ?");
                            $stmt->bind_param("i", $id_usuario);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();
                            $avatar = $row['avatar'];
                        }
                
                        $stmt = $conexion->prepare("UPDATE usuarios SET correo_electronico = ?, cita = ?, biografia = ?, ubicacion = ?, fecha_nacimiento = ?, avatar = ? WHERE id_usuario = ?");
                        $stmt->bind_param("ssssssi", $correo_electronico, $cita, $biografia, $ubicacion, $fecha_nacimiento, $avatar, $id_usuario);
                        $stmt->execute();
                
                        echo "<script>window.location.href = 'perfil.php?id_usuario=$id_usuario';</script>";
                    }

                    // Cambiar el rol del usuario (si eres administrador)
                    if (isset($_POST['cambiar_privilegios']) && $_SESSION['id_rol'] == 1) {
                        $id_rol = $_POST["id_rol"];
                        $stmt = $conexion->prepare("UPDATE usuarios SET id_rol = ? WHERE id_usuario = ?");
                        $stmt->bind_param("ii", $id_rol, $id_usuario);
                        $stmt->execute();

                        echo "<script>window.location.href = 'perfil.php?id_usuario=$id_usuario';</script>";
                    }

                    // Banear o desbanear a usuario (si eres administrador o moderador)
                    if (isset($_POST['cambiar_estado']) && ($_SESSION['id_rol'] == 1 || $_SESSION['id_rol'] == 2)) {
                        $estado = $_POST['estado'];
                        $stmt = $conexion->prepare("UPDATE usuarios SET estado = ? WHERE id_usuario = ?");
                        $stmt->bind_param("ii", $estado, $id_usuario);
                        $stmt->execute();

                        echo "<script>window.location.href = 'perfil.php?id_usuario=$id_usuario';</script>";
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