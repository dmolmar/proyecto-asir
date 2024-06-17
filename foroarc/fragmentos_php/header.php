<div>
    <a href="./index.php" id="logo">
        <img src="./img/logoARC.png" alt="Logo del ForoARC">
        <h1>ForoARC</h1>
    </a>
    <div id="buscador">
        <form method="GET" action="buscador.php">
            <input type="text" name="buscador" placeholder="Buscar..." maxlength="500" required>
            <input type="submit" value="Buscar">
        </form>
    </div>

    <div id="cosasCuenta">
        <?php
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if(!isset($_SESSION["id_rol"])){
            echo '<a id="btnIniciarSesion" class="btnSesion" href="iniciarSesion.php">Iniciar sesión</a>';
        } else {
            echo '<p>¡Bienvenido, <b>' . htmlspecialchars($_SESSION["username"]) . '</b>!</p>';
            echo '<div id="botonesSesion">';
            echo '<a id="btnNotificaciones" class="btnSesion" href="notificaciones.php">Notificaciones</a>';
            echo '<a id="btnPerfil" class="btnSesion" href="perfil.php">Perfil</a>';
            echo '<a id="btnCerrarSesion" class="btnSesion" href="cerrarSesion.php">Cerrar sesión</a>';
            echo '</div>';
        }
        ?>
    </div>
</div>