<?php  
require_once("includes/conexion.php");

$edit = isset($_POST['edit']) ? $_POST['edit'] : 0;
$id = isset($_POST['id']) ? $_POST['id'] : "";

$Title="Crear nuevo registro";
$Type=1;

//Metodo de apliacion
$SQL_MetodoAplicacion=Seleccionar("uvw_Sap_tbl_OrdenesVentasDetalleMetodoAplicacion","*","","DeMetodoAplicacion");

if($edit==1){
	$SQL_Data=Seleccionar("uvw_tbl_Dosificaciones","*","ID='".$id."'");
	$row_Data=sqlsrv_fetch_array($SQL_Data);
	$Title="Editar registro";
	$Type=2;
}

?>
<form id="frm_NewParam" method="post" action="parametros_dosificaciones.php">
	<div class="modal-header">
		<h4 class="modal-title">
			<?php echo $Title; ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="form-group">
			<div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
				<div class="form-group">
					<label class="col-xs-12" style="padding-left: 0px;padding-right: 0px;"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-cube"></i> Artículo inicial</h3></label>
				</div>
				<div class="form-group">
					<label class="control-label">Artículo inicial <span class="text-danger">*</span></label>
					<input name="Articulo" type="hidden" id="Articulo" value="<?php if($edit==1){echo $row_Data['CodArticulo'];}?>">
					<input name="NombreArticulo" type="text" class="form-control" id="NombreArticulo" placeholder="Ingrese para buscar..." value="<?php if($edit==1){echo $row_Data['NombreArticulo'];}?>" required>
				</div>
				<div class="form-group">
					<label class="control-label">Cantidad <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="Cantidad" id="Cantidad" required autocomplete="off" value="<?php if($edit==1){echo number_format($row_Data['Cantidad'],2);}?>" onKeyPress="return justNumbers(event,this.value);">
				</div>
				<div class="form-group">
					<label class="control-label">Método de aplicación <span class="text-danger">*</span></label>
					<select name="MetodoAplicacion" class="form-control" id="MetodoAplicacion" required>
						<option value="">Seleccione...</option>
					  <?php	while($row_MetodoAplicacion=sqlsrv_fetch_array($SQL_MetodoAplicacion)){?>
								<option value="<?php echo $row_MetodoAplicacion['IdMetodoAplicacion'];?>" <?php if(($edit==1)&&(strcmp($row_MetodoAplicacion['IdMetodoAplicacion'],$row_Data['IdMetodoAplicacion'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_MetodoAplicacion['DeMetodoAplicacion'];?></option>
						<?php }?>
					</select>
				</div>
				<div class="form-group">
					<label class="col-xs-12" style="padding-left: 0px;padding-right: 0px;"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-exchange"></i> Artículo de cambio</h3></label>
				</div>
				<div class="form-group">
					<label class="control-label">Artículo de cambio <span class="text-danger">*</span></label>
					<input name="ArticuloEqui" type="hidden" id="ArticuloEqui" value="<?php if($edit==1){echo $row_Data['CodArticuloEqui'];}?>">
					<input name="NombreArticuloEqui" type="text" class="form-control" id="NombreArticuloEqui" placeholder="Ingrese para buscar..." value="<?php if($edit==1){echo $row_Data['NombreArticuloEqui'];}?>" required>
				</div>
				<div class="form-group">
					<label class="control-label">Cantidad <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="CantidadEqui" id="CantidadEqui" required autocomplete="off" value="<?php if($edit==1){echo number_format($row_Data['CantidadEqui'],2);}?>" onKeyPress="return justNumbers(event,this.value);">
				</div>
				<div class="form-group">
					<label class="control-label">Método de aplicación <span class="text-danger">*</span></label>
					<select name="MetodoAplicacionEqui" class="form-control" id="MetodoAplicacionEqui" required>
						<option value="">Seleccione...</option>
					  <?php	$SQL_MetodoAplicacion=Seleccionar("uvw_Sap_tbl_OrdenesVentasDetalleMetodoAplicacion","*","","DeMetodoAplicacion");
						while($row_MetodoAplicacion=sqlsrv_fetch_array($SQL_MetodoAplicacion)){?>
								<option value="<?php echo $row_MetodoAplicacion['IdMetodoAplicacion'];?>" <?php if(($edit==1)&&(strcmp($row_MetodoAplicacion['IdMetodoAplicacion'],$row_Data['IdMetodoAplicacionEqui'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_MetodoAplicacion['DeMetodoAplicacion'];?></option>
						<?php }?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Factor <span class="text-danger">*</span></label>
					<select name="Factor" class="form-control" id="Factor">
						<option value="M" <?php if(($edit==1)&&($row_Data['Factor']=="M")){ echo "selected=\"selected\"";}?>>Multiplicar</option>
						<option value="D" <?php if(($edit==1)&&($row_Data['Factor']=="D")){ echo "selected=\"selected\"";}?>>Dividir</option>
					</select>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i> Aceptar</button>
		<button type="button" class="btn btn-danger m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
	</div>
	<input type="hidden" id="MM_Insert" name="MM_Insert" value="1" />
	<input type="hidden" id="id" name="id" value="<?php echo $id;?>" />
	<input type="hidden" id="type" name="type" value="<?php echo $Type;?>" />
</form>
<script>
 $(document).ready(function(){
	 $("#frm_NewParam").validate({
		 submitHandler: function(form){
			Swal.fire({
				title: "¿Está seguro que desea guardar los datos?",
				icon: "question",
				showCancelButton: true,
				confirmButtonText: "Si, confirmo",
				cancelButtonText: "No"
			}).then((result) => {
				if (result.isConfirmed) {
					$('.ibox-content').toggleClass('sk-loading',true);
					form.submit();
				}
			});
		}
	 });
	 
	var options = {
		url: function(phrase) {
			return "ajx_buscar_datos_json.php?type=24&id="+phrase;
		},

		getValue: "NombreBuscarArticulo",
		requestDelay: 400,
		list: {
			match: {
				enabled: true
			},
			onClickEvent: function() {
				var value = $("#NombreArticulo").getSelectedItemData().IdArticulo;
				$("#Articulo").val(value).trigger("change");
			}
		}
	};

	var options2 = {
		url: function(phrase) {
			return "ajx_buscar_datos_json.php?type=24&id="+phrase;
		},

		getValue: "NombreBuscarArticulo",
		requestDelay: 400,
		list: {
			match: {
				enabled: true
			},
			onClickEvent: function() {
				var value = $("#NombreArticuloEqui").getSelectedItemData().IdArticulo;
				$("#ArticuloEqui").val(value).trigger("change");
			}
		}
	};

	$("#NombreArticulo").easyAutocomplete(options);
	$("#NombreArticuloEqui").easyAutocomplete(options2);
	 
 });
</script>