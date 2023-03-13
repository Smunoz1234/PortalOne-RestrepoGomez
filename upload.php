<?php
require "includes/conexion.php";

$temp = ObtenerVariable("CarpetaTmp");
$persistent = $_REQUEST['persistent'] ?? ""; // SMM, 10/03/2022

if ($persistent == "") {
    $route = $temp . "/" . $_SESSION['CodUser'] . "/";
} else {
    $route = CrearObtenerDirRuta($temp . "/$persistent/" . $_SESSION['CodUser'] . "/");
}

$cant = count($_FILES['File']['name']);

if (!file_exists($route)) {
    mkdir($route, 0777, true);
}

$i = 0;
while ($i < $cant) {
    $NombreArchivo = NormalizarNombreArchivo(str_replace(" ", "_", $_FILES['File']['name'][$i]));
    move_uploaded_file($_FILES['File']['tmp_name'][$i], $route . $NombreArchivo);
    $i++;
}

sqlsrv_close($conexion);
