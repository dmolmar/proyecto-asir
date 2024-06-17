<?php
$servidor = "mysql-service";
$usuario = "root";
$password = "abcde";

$conexion = new mysqli("$servidor", "$usuario", "$password");

if ($conexion->connect_error) {
    die("La conexión ha fallado: " . $conexion->connect_error);
}
?>