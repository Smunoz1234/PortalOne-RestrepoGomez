<?php 
if(!isset($_GET['CardCode'])||($_GET['CardCode']=="")){?>
	<select id="Sucursal" name="Sucursal[]" data-placeholder="(Todos)" class="chosen-select" multiple>
	</select>
<?php }else{
	require("includes/conexion.php");
	//Sucursales
	if(PermitirFuncion(205)){
		$Where="CodigoCliente=''".$_GET['CardCode']."''";
		$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","NombreSucursal",$Where);
	}else{
		$Where="CodigoCliente=''".$_GET['CardCode']."'' and ID_Usuario = ".$_SESSION['CodUser'];
		$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal",$Where);	
	}?>
	<select id="Sucursal" name="Sucursal[]" data-placeholder="(Todos)" class="chosen-select" multiple>
	<?php 
		while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>		
			<option value="<?php echo $row_Sucursal['NombreSucursal'];?>"><?php echo $row_Sucursal['NombreSucursal'];?></option>
	<?php }?>
	</select>
<?php } ?>
<script>
$(document).ready(function(){
	$('.chosen-select').chosen({width: "100%"});
});
</script>