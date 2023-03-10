<?php
require "includes/conexion.php";

// Enter the name of directory
$dir_temp = CrearObtenerDirRuta("tmp_download") . "/";

// Limpiar y volver a llenar carpeta temporal
LimpiarDirRuta("tmp_download");

if (isset($_GET['id']) && ($_GET['id'] != "")) {
    $dir_main = CrearObtenerDirRuta(ObtenerVariable("RutaAnexosRecepcionVehiculo")) . "/";

    $SQL_Fotografias = Seleccionar('tbl_RecepcionVehiculos_Fotografias', '*', 'id_recepcion_vehiculo = ' . $_GET['id']);
    $row_Fotografias = sqlsrv_fetch_array($SQL_Fotografias);

    // Fuentes y destinos
    $f1 = $dir_main . $row_Fotografias["anexo_frente"];
    $d1 = $dir_temp . "Frente_" . $row_Fotografias["anexo_frente"];

    $f2 = $dir_main . $row_Fotografias["anexo_lateral_izquierdo"];
    $d2 = $dir_temp . "LateralIzquierdo_" . $row_Fotografias["anexo_lateral_izquierdo"];

    $f3 = $dir_main . $row_Fotografias["anexo_lateral_derecho"];
    $d3 = $dir_temp . "LateralDerecho_" . $row_Fotografias["anexo_lateral_derecho"];

    $f4 = $dir_main . $row_Fotografias["anexo_trasero"];
    $d4 = $dir_temp . "Trasero_" . $row_Fotografias["anexo_trasero"];

    $f5 = $dir_main . $row_Fotografias["anexo_capot"];
    $d5 = $dir_temp . "Capo_" . $row_Fotografias["anexo_capot"];

    // Copiar archivos
    copy($f1, $d1);
    copy($f2, $d2);
    copy($f3, $d3);
    copy($f4, $d4);
    copy($f5, $d5);

    // Fotos adicionales
    $SQL_RV = Seleccionar('tbl_RecepcionVehiculos', '*', 'id_recepcion_vehiculo = ' . $_GET['id']);
    $row_RV = sqlsrv_fetch_array($SQL_RV);

    $SQL_Anexos = Seleccionar('tbl_ArchivosAnexosDetalle', '*', 'id_anexo = ' . $row_RV["id_anexo"]);

    while ($row_Anexo = sqlsrv_fetch_array($SQL_Anexos)) {
        $anexo = $row_Anexo["archivo"] . $row_Anexo["ext_archivo"];
        copy(($row_Anexo["ruta"] . $anexo), ($dir_temp . $anexo));
    }
}

// Enter the name to creating zipped directory
$zipname = "Recepcion_" . ($_GET['id'] ?? "") . "_" . date('YmdHi') . ".zip";
$zipcreated = $dir_temp . $zipname;

// Create new zip class
$zip = new ZipArchive;

$cant = 0;
if ($zip->open($zipcreated, ZipArchive::CREATE) === true) {

    // Store the path into the variable
    $dir = opendir($dir_temp);

    while ($file = readdir($dir)) {
        $pathfile = $dir_temp . $file;

        if (is_file($pathfile)) {
            $zip->addFile($pathfile, $file);
            $cant++;
        }
    }
    $zip->close();
}

if ($cant == 0) {
    echo "No hay archivos para descargar";
} else {
    ob_end_clean(); // Reference, https://stackoverflow.com/questions/19963382/php-zip-file-download-error-when-opening
    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename=' . $zipname);
    header('Content-Length: ' . filesize($zipcreated));
    readfile($zipcreated);
}
