<?php
require "includes/conexion.php";
$temp = ObtenerVariable("CarpetaTmp");
$log = ObtenerVariable("RutaAnexosPortalOne"); // SMM, 03/03/2023

// print_r($_FILES);
$imgID = date("Ymd_His");

$path = $_FILES['image']['name'];
$imgFN = pathinfo($path, PATHINFO_FILENAME);
$imgEXT = pathinfo($path, PATHINFO_EXTENSION);

$nombreArchivo = NormalizarNombreImagen($imgID, $imgFN, $imgEXT);
sqlsrv_close($conexion);

$persistent = $_REQUEST['persistent'] ?? ""; // SMM, 10/03/2022
if ($persistent == "") {
    // Los archivos se borran cuando se ejecuta "LimpiarDirTemp()"
    $route = $temp . "/" . $_SESSION['CodUser'] . "/";
    $route_log = $log . "/" . $_SESSION['CodUser'] . "/";
} else {
    // Usar cuando se quiera que los archivos sean persistentes.
    $route = CrearObtenerDirRuta($temp . "/$persistent/" . $_SESSION['CodUser'] . "/");
    $route_log = CrearObtenerDirRuta($log . "/$persistent/" . $_SESSION['CodUser'] . "/");
}

if (!file_exists($route)) {
    mkdir($route, 0777);
}

// SMM, 03/03/2023
if (!file_exists($route_log)) {
    mkdir($route_log, 0777);
}

if (($_FILES["image"]["type"] == "image/pjpeg")
    || ($_FILES["image"]["type"] == "image/jpeg")
    || ($_FILES["image"]["type"] == "image/png")
    || ($_FILES["image"]["type"] == "image/gif")) {
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $route . $nombreArchivo)) {
        echo json_encode(["directorio" => $route, "nombre" => $nombreArchivo]);
    } else {
        echo 0;
    }
} else {
    echo 0;
}
