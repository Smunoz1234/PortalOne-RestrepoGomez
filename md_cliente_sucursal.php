<?php  
require_once("includes/conexion.php");

$SQL_Cliente=Seleccionar('uvw_Sap_tbl_Clientes','CodigoCliente, NombreCliente','','NombreCliente');

if(isset($_POST['CodigoCliente'])&&$_POST['CodigoCliente']!=""){
	$Parametros=array(
		"'".$_POST['CodigoCliente']."'",
		"'S'",
		"'".$_SESSION['CodUser']."'"
	);
	$SQL_SucursalCliente=EjecutarSP('sp_ConsultarSucursalesClientes',$Parametros);
}



?>
<div class="modal-header">
	<h4 class="modal-title">Seleccionar cliente</h4>
</div>
<div class="modal-body">
	<div class="form-group">
		<div class="ibox-content">
			<div class="form-group">
				<label class="control-label">Cliente</label>
				<select name="ClienteModal" class="form-control chosen-select" style="width: 100%" id="ClienteModal" onChange="BuscarSucursalModal();">
					<option value="">Seleccione...</option>
					<?php while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){?>
						<option value="<?php echo $row_Cliente['CodigoCliente'];?>" <?php if((isset($_POST['CodigoCliente']))&&(strcmp($row_Cliente['CodigoCliente'],$_POST['CodigoCliente'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Cliente['NombreCliente'];?></option>
					<?php }?>
				</select>	
			</div>
			<div class="form-group">
				<label class="control-label">Sucursal</label>
				<select name="SucursalModal" class="form-control chosen-select" style="width: 100%" id="SucursalModal">
				  <option value="">Seleccione...</option>
					<?php 
					if(isset($_POST['CodigoCliente'])&&$_POST['CodigoCliente']!=""){
						while($row_SucursalCliente=sqlsrv_fetch_array($SQL_SucursalCliente)){?>
							<option value="<?php echo $row_SucursalCliente['NombreSucursal'];?>" <?php if((isset($_POST['SucursalCliente']))&&(strcmp($row_SucursalCliente['NombreSucursal'],$_POST['SucursalCliente'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_SucursalCliente['NombreSucursal'];?></option>
				  <?php }
					}?>
				</select>
			</div>
		</div>
	</div>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-success m-t-md" onClick="GuardarDatos('<?php echo $_POST['Id'];?>');"><i class="fa fa-check"></i> Aceptar</button>
	<button type="button" class="btn btn-danger m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
</div>
	
<script>
 $(document).ready(function(){
		$('.chosen-select').chosen({width: "100%"});
 });
</script>
<script>
function BuscarSucursalModal(){
	$('.ibox-content').toggleClass('sk-loading',true);
	var ClienteModal=document.getElementById('ClienteModal').value;
	$.ajax({
		type: "POST",
		async: false,
		url: "ajx_cbo_select.php?type=3&id="+ClienteModal,
		success: function(response){
			$("#SucursalModal").chosen("destroy");
			$('#SucursalModal').html(response).fadeIn();
			$('#SucursalModal').chosen({width: "100%"});
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
	$('.ibox-content').toggleClass('sk-loading',false);
}
	
function GuardarDatos(id){
	let ClienteModal=document.getElementById('ClienteModal').value;
	let NombreClienteModal=$('#ClienteModal option:selected').text();
	let SucursalModal=document.getElementById('SucursalModal').value;
	
	if(ClienteModal==""){
		NombreClienteModal="";
	}
	
	document.getElementById('ClienteBodega'+id).value=ClienteModal;
	document.getElementById('NombreClienteBodega'+id).value=NombreClienteModal;
	document.getElementById('SucursalBodega'+id).value=SucursalModal;
	document.getElementById('MetodoBodega'+id).value=2;
	
	$('#myModal').modal("hide");
}
</script>