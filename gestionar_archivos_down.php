<?php
if (isset($_GET['file']) && $_GET['file'] != "") {
	require_once("includes/conexion.php");

	$file = base64_decode($_GET['file']);

	//Selecciono los datos del archivo
	if ((!isset($_GET['dir'])) && (!isset($_GET['api']))) {
		// SMM, 05/10/2023
		$CardName = ($_GET["type"] == 2) ? "Proveedores" : "Clientes";

		$Cons = "SELECT * From uvw_tbl_Portal$CardName" . "_Archivos WHERE id_archivo='$file'";
		$SQL = sqlsrv_query($conexion, $Cons);
		$row = sqlsrv_fetch_array($SQL);
	}

	if (isset($_GET['dir']) && $_GET['dir'] != "") { //Saber si le paso la ruta del archivo por parámetro o si tomo la estandar
		$filename = base64_decode($_GET['dir']) . $file;
		$NombreArchivo = $file;
	} elseif (isset($_GET['api']) && $_GET['api'] != "") { //Descargar un archivo proveniente de una API
		$Result = DescargarFileAPI($file);
		$dir_temp = CrearObtenerDirTemp();
		$filename = $dir_temp . $_SESSION['User'] . '.pdf';
		file_put_contents($filename, $Result);
		$NombreArchivo = $_SESSION['User'] . "_" . date('YmdHi') . '.pdf';
	} else {
		$carp_archivos = ObtenerVariable("RutaArchivos");
		$filename = $_SESSION['BD'] . "/" . $carp_archivos . "/" . $row['cardcode'] . "/" . $row['id_categoria'] . "/" . $row['archivo'];
		$NombreArchivo = $row['archivo'];
	}

	$size = filesize($filename);

	header("Content-Transfer-Encoding: binary");
	//header("Content-type: application/octet-stream");
	header('Content-type: application/pdf', true);
	header("Content-Type: application/force-download");
	header('Content-Disposition: attachment; filename="' . $NombreArchivo . '"');
	header("Content-Length: $size");
	readfile($filename);

	//echo $filename;
}




?>