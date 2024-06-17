<div id="hilos-activos">
    <h3>Hilos más activos</h3>
    <?php
        // Obtén la fecha y hora actuales
        $ahora = new DateTime();
        $ayer = $ahora->sub(new DateInterval('P1D'))->format('Y-m-d H:i:s');

        // Prepara la consulta SQL
        $sql = "
            SELECT h.id_hilo, h.titulo_tema, u.id_usuario, u.nombre_usuario, c.id_categoria, c.nombre_categoria, COUNT(r.id_respuesta) AS num_respuestas
            FROM hilos h
            JOIN usuarios u ON h.id_usuario = u.id_usuario
            JOIN categorias c ON h.id_categoria = c.id_categoria
            LEFT JOIN respuestas r ON h.id_hilo = r.id_hilo AND r.fecha_respuesta > ?
            GROUP BY h.id_hilo, h.titulo_tema, u.id_usuario, u.nombre_usuario, c.id_categoria, c.nombre_categoria
            ORDER BY num_respuestas DESC
            LIMIT 3
        ";
        $stmt = $conexion->prepare($sql);

        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conexion->error));
        }

        $stmt->bind_param("s", $ayer);

        // Ejecuta la consulta
        $stmt->execute();
        $resultado = $stmt->get_result();

        // Muestra los hilos más activos
        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                echo '<div class="hilo-activo">';
                echo '<p><a href="mostrarHilo.php?id=' . $fila["id_hilo"] . '">' . $fila["titulo_tema"] . '</a> en la categoría <a href="mostrarCategoria.php?id=' . $fila["id_categoria"] . '">' . $fila["nombre_categoria"] . '</a> por <a href="perfil.php?id_usuario=' . $fila["id_usuario"] . '">' . $fila["nombre_usuario"] . '</a></p>';
                echo '</div>';
            }
        } else {
            echo "No hay hilos activos en este momento.";
        }
    ?>

</div>
<div id="estadisticas">
    <h3>Estadísticas del foro</h3>
    <?php
        // Hora y fecha actual
        date_default_timezone_set('Europe/Madrid'); // Ajusta esto a tu zona horaria
        echo '<p>Hora y fecha actual: ' . date('H:i d-m-Y') . '</p>';

        // Número de categorías
        $stmt = $conexion->prepare("SELECT COUNT(*) AS num_categorias FROM categorias");
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        echo '<p>Número de categorías: ' . $fila["num_categorias"] . '</p>';

        // Número de hilos
        $stmt = $conexion->prepare("SELECT COUNT(*) AS num_hilos FROM hilos");
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        echo '<p>Número de hilos: ' . $fila["num_hilos"] . '</p>';

        // Número de respuestas
        $stmt = $conexion->prepare("SELECT COUNT(*) AS num_respuestas FROM respuestas");
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        echo '<p>Número de respuestas: ' . $fila["num_respuestas"] . '</p>';

        // Número de usuarios activos
        $stmt = $conexion->prepare("SELECT COUNT(*) AS num_usuarios_activos FROM usuarios WHERE estado = 1");
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        echo '<p>Número de usuarios activos: ' . $fila["num_usuarios_activos"] . '</p>';

        // Número de usuarios baneados
        $stmt = $conexion->prepare("SELECT COUNT(*) AS num_usuarios_baneados FROM usuarios WHERE estado = 0");
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        echo '<p>Número de usuarios baneados: ' . $fila["num_usuarios_baneados"] . '</p>';
    ?>
</div>