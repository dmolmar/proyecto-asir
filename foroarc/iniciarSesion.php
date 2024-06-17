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
    <link type="text/css" rel="stylesheet" href="./css/iniciarSesion.css">
    <title>Iniciar Sesión</title>
</head>
<body>
    <header>
        <?php
        include("fragmentos_php/header.php");
        ?>
    </header>
    <main>
        <div id="contenido">
            <div id="form_registro">
                <form action="iniciarSesion.php" method="post">
                    <h2>Crear cuenta</h2>
                    <label for="reg_username">Nombre de usuario:</label><br>
                    <input type="text" id="reg_username" name="username" required maxlength="30"><br>
                    <label for="reg_email">Correo electrónico:</label><br>
                    <input type="email" id="reg_email" name="email" required maxlength="60"><br>
                    <label for="reg_password">Contraseña:</label><br>
                    <input type="password" id="reg_password" name="password" required maxlength="30"><br>
                    <input type="submit" name="register" value="Crear cuenta">
                </form>

                <form action="iniciarSesion.php" method="post">
                    <h2>Iniciar sesión</h2>
                    <label for="log_username">Nombre de usuario:</label><br>
                    <input type="text" id="log_username" name="username" required maxlength="30"><br>
                    <label for="log_password">Contraseña:</label><br>
                    <input type="password" id="log_password" name="password" required maxlength="30"><br>
                    <input type="submit" name="login" value="Iniciar sesión">
                </form>
            </div>
            <p id="mensajeEstado">
                <?php

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $username = $_POST["username"];
                    $password = $_POST["password"];

                    if (strlen($username) > 30 || strlen($password) > 30) {
                        echo "El nombre de usuario y la contraseña no pueden tener más de 30 carácteres.";
                    } elseif (strlen($password) < 4) {
                        echo "La contraseña debe tener al menos 4 carácteres";
                    } else {
                        if (isset($_POST['register'])) {
                            $username = $_POST["username"];
                            $email = $_POST["email"];
                            if (strlen($email) > 60) {
                                echo "El email no puede contener más de 60 carácteres.";
                            }
                            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                echo "El correo electrónico no es válido.";
                            } else {
                                // Verificar si el nombre de usuario ya existe
                                $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ?");
                                $stmt->bind_param("s", $username);
                                $stmt->execute();
                                $resultado = $stmt->get_result();
                                if ($resultado->num_rows > 0) {
                                    echo "El nombre de usuario ya existe.";
                                } else {
                                    $sql = "INSERT INTO usuarios (nombre_usuario, correo_electronico, contrasena) VALUES (?, ?, ?)";
                                    $stmt = $conexion->prepare($sql);
                                    $stmt->bind_param("sss", $username, $email, $password);
                                    if ($stmt->execute()) {
                                        $_SESSION["username"] = $username;
                                
                                        // Obtenemos el id_usuario y el id_rol a partir del nombre_usuario
                                        $stmt = $conexion->prepare("SELECT id_usuario, id_rol FROM usuarios WHERE nombre_usuario = ?");
                                        $stmt->bind_param("s", $username);
                                        $stmt->execute();
                                        $resultado = $stmt->get_result();
                                        if ($resultado->num_rows > 0) {
                                            $fila = $resultado->fetch_assoc();
                                            $id_usuario = $fila["id_usuario"];
                                            $id_rol = $fila["id_rol"];
                                
                                            // Almacenamos el id_usuario y el id_rol en la variable de sesión
                                            $_SESSION["id_usuario"] = $id_usuario;
                                            $_SESSION["id_rol"] = $id_rol;
                                        }
                                        echo "<script>window.location.href = 'index.php';</script>";
                                    } else {
                                        echo "Hubo un error al crear la cuenta.";
                                    }
                                }
                            }
                        } elseif (isset($_POST['login'])) {
                            $sql = "SELECT id_usuario, estado FROM usuarios WHERE nombre_usuario = ? AND contrasena = ?";
                            $stmt = $conexion->prepare($sql);
                            $stmt->bind_param("ss", $username, $password);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows > 0) {
                                $fila = $result->fetch_assoc();
                                if ($fila["estado"] == 0) { // Si el estado es 0, el usuario está baneado
                                    echo "Lo sentimos, esta cuenta ha sido baneada.";
                                } else {
                                    $_SESSION["username"] = $username;
                                    // Primero, obtenemos el id_usuario y el id_rol a partir del nombre_usuario
                                    $stmt = $conexion->prepare("SELECT id_usuario, id_rol FROM usuarios WHERE nombre_usuario = ?");
                                    $stmt->bind_param("s", $username);
                                    $stmt->execute();
                                    $resultado = $stmt->get_result();
                                    if ($resultado->num_rows > 0) {
                                        $fila = $resultado->fetch_assoc();
                                        $id_usuario = $fila["id_usuario"];
                                        $id_rol = $fila["id_rol"];
                                        $_SESSION["id_usuario"] = $id_usuario;
                                        $_SESSION["id_rol"] = $id_rol;
                                    }
                                    echo "<script>window.location.href = 'index.php';</script>";
                                }
                            } else {
                                echo "Nombre de usuario o contraseña incorrectos.";
                            }
                        }                        
                        if (isset($stmt)) {
                            $stmt->close();
                        }
                    }
                }
                $conexion->close();
                ?>
            </p>
        </div>
    </main>
    <footer>
        <?php include("fragmentos_php/footer.php"); ?>
    </footer>
</body>
</html>

