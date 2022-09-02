<?php 
if(isset($_GET['file'])&&$_GET['file']!=""){
	require("includes/conexion.php");
	
	$file=base64_decode($_GET['file']);
	
	//Selecciono los datos del archivo
	$Cons="Select * From uvw_tbl_Productos Where ID_Producto='".$file."'";
	$SQL=sqlsrv_query($conexion,$Cons);
	$row=sqlsrv_fetch_array($SQL);
	
	$carp_archivos=ObtenerVariable("RutaArchivos");
	$carp_productos="productos";
	//PHP en general
	$filename = $_SESSION['BD']."/".$carp_archivos."/".$carp_productos."/".$row['ItemCode']."/".$row['ID_CategoriaProductos']."/".$row['Archivo'];
	$size = filesize($filename);
	header("Content-Transfer-Encoding: binary"); 
	//header("Content-type: application/octet-stream"); 
	header("Content-Type: application/force-download"); 
	header("Content-Disposition: attachment; filename=".$row['Archivo']); 
	header("Content-Length: $size"); 
	readfile($filename);
	
	//echo $filename;
}




?>