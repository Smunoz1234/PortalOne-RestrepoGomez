<?php 
require_once("includes/conexion.php");

$Result= DescargarFileAPI("MonitoreoTemperaturas/DescargarFormatos/15/aordonez");

//$Result=DescargarFileAPI($file);
$dir_temp=CrearObtenerDirTemp();
$filename=$dir_temp.$_SESSION['User'].'.pdf';
file_put_contents($filename, $Result);
$NombreArchivo=$_SESSION['User']."_".date('YmdHi').'.pdf';

header("Content-Transfer-Encoding: binary"); 
header('Content-type: application/pdf', true);
header("Content-Type: application/force-download");
header('Content-Disposition: attachment; filename="'.$NombreArchivo.'"');
header('Content-Length: '.filesize($filename));
readfile($filename);

/*header('Cache-Control: public'); 
header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="new.pdf"');
header('Content-Length: '.filesize($file));
readfile($file);*/
//echo $Result;

?>
