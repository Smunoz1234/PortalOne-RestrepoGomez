<?php 
if(!isset($_GET['CardCode'])||($_GET['CardCode']=="")){?>
	<option value="">(Todos)</option>
<?php }else{
	require_once("includes/conexion.php");
	//Todos
	$Todos=(isset($_GET['todos'])) ? $_GET['todos'] : 1;
	$Selec=(isset($_GET['selec'])) ? $_GET['selec'] : 0;
	$TipoDir="";
	
	if(isset($_GET['tdir'])){
		switch ($_GET['tdir']){
			case 'S':
				$TipoDir="and TipoDireccion='S'";
				break;
			case 'B':
				$TipoDir="and TipoDireccion='B'";
				break;
			default:
				$TipoDir="and TipoDireccion='S'";
		}			
	}
	
	//Sucursales
	if(PermitirFuncion(205)){
		$Where="CodigoCliente='".$_GET['CardCode']."' $TipoDir";
		$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","NombreSucursal, NumeroLinea",$Where);
	}else{
		$Where="CodigoCliente='".$_GET['CardCode']."' $TipoDir and ID_Usuario = ".$_SESSION['CodUser'];
		$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal, NumeroLinea",$Where);	
	}?>
	<?php if($Todos==1&&$Selec==0){?><option value="">(Todos)</option><?php }?>
	<?php if($Selec==1){?><option value="">Seleccione...</option><?php }?>
	<?php 
		while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){
			if(isset($_GET['sucline'])&&($_GET['sucline']==1)){?>		
				<option value="<?php echo $row_Sucursal['NumeroLinea'];?>"><?php echo $row_Sucursal['NombreSucursal'];?></option>
	  <?php }else{?>
				<option value="<?php echo $row_Sucursal['NombreSucursal'];?>"><?php echo $row_Sucursal['NombreSucursal'];?></option>
<?php 	    }
		}
}?>