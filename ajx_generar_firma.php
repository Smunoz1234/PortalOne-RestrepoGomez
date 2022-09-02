<?php 
if(!isset($_POST['base64'])){//Saber que combo voy a consultar
	exit();
}else{
	require_once("includes/conexion.php");
	$dir=CrearObtenerDirTempFirma();
	
	//LimpiarDirTempFirma();
	
	$baseFromJavascript = $_POST['base64'];
	
	$Hash=substr(uniqid(rand()),0,8);

	$base_to_php = explode(',', $baseFromJavascript);
	$data = base64_decode($base_to_php[1]);
	
	$NombreFileFirma="Sig_".$Hash.".jpg";	
	$filepath = $dir.$NombreFileFirma;
	
	file_put_contents($filepath, $data);
	echo base64_encode($NombreFileFirma);
	
	/*if(){
		if($NuevoNombre=ConvertirPNGtoJPG($NombreFileFirma,$filepath,$dir)){
			echo base64_encode($NuevoNombre);
		}else{
			echo "Error al convertir el formato de la firma";
		}
	}else{
		echo "No se pudo cargar la firma";
	}*/
	
	sqlsrv_close($conexion);
}
?>