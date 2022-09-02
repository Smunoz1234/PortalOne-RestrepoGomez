<?php 
if(isset($_GET['file'])&&$_GET['file']!=""){
	require_once("includes/conexion.php");
	
	$file=base64_decode($_GET['file']);
	if(!isset($_GET['line'])||$_GET['line']==""){
		$line=1;
	}else{
		$line=base64_decode($_GET['line']);
	}	
	$NombreArchivo="";
	$size=0;
	
	$RutaAttachSAP=ObtenerDirAttach();

	$SQL=Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos','NombreArchivo',"AbsEntry='".$file."' AND Line='".$line."'");
	$row=sqlsrv_fetch_array($SQL);
	
	$filename = $RutaAttachSAP[0].$row['NombreArchivo'];
		
	$NombreArchivo=$row['NombreArchivo'];
	$size = filesize($filename);
	
	header("Content-Transfer-Encoding: binary"); 
	header('Content-type: application/pdf', true);
	header("Content-Type: application/force-download"); 
	header('Content-Disposition: attachment; filename="'.$NombreArchivo.'"');
	header("Content-Length: $size"); 
	readfile($filename);
	
	//echo $filename;
}




?>