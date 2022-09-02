<?php 
if(!isset($_GET['id'])||($_GET['id']=="")){
	echo "<option value=''>Seleccione...</option>";
	exit();
}else{
	require("includes/conexion.php");
	/*if(PermitirFuncion(105)){
		$Where="WHERE U_NDG_PrjCode = ''".$_GET['U_NDG_PrjCode']."''";
		$SQL_Alm=Seleccionar("uvw_Sap_TodosProyectosAlmacenes","U_NDG_WhsCode, U_NDG_WhsName",$Where);	
	}else{
		$Where="WHERE U_NDG_IdEmp=''".$_SESSION['CodSAP']."'' and U_NDG_PrjCode = ''".$_GET['U_NDG_PrjCode']."''";
		$SQL_Alm=Seleccionar("uvw_Sap_ProyectosAlmacenes","U_NDG_WhsCode, U_NDG_WhsName",$Where);
	}*/
	$Cons_Sucursal="Select * From uvw_Sap_tbl_Clientes_Sucursales Where CodigoCliente='".$_GET['id']."' Order by NombreSucursal";
	$SQL_Sucursal=sqlsrv_query($conexion,$Cons_Sucursal);
	if($SQL_Sucursal){
		//echo "<select name=\"Almacen\" id=\"Almacen\" class=\"form-control\" onChange=\"CargarFrame();\">";
		while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){
			echo "<option value=\"".$row_Sucursal['NombreSucursal']."\">".$row_Sucursal['NombreSucursal']."</option>";
		}
		//echo "</select>";
	}else{
		echo "<option value=''>Seleccione...</option>";
	}
		sqlsrv_close($conexion);
}
?>