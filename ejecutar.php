<?php
include("includes/conect_srv.php");
$Consulta="Select * From tbl_Usuarios";
$SQL=sqlsrv_query($conexion,$Consulta);
$sw=0;
$vOk="";
$cnt=1;
while($row=sqlsrv_fetch_array($SQL)){
	$Update="Update tbl_Usuarios set Password='".md5($row['Usuario'])."', CambioClave=1 Where ID_Usuario='".$row['ID_Usuario']."'";
	if(!sqlsrv_query($conexion,$Update)){
		$sw=1;
	}else{
		$vOk=$vOk.$cnt.". ".$row['Usuario']." [OK]"."<br>";
		$cnt++;
	}
}
if($sw==0){
	echo $vOk;
	echo "<br>";
	echo "Procesos ejecutados correctamente!!";	
}else{
	echo "Ocurrio un error al ejecutar los procesos.";
}
sqlsrv_close($conexion);
?>