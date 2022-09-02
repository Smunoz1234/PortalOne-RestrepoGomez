<?php  
require_once("includes/conexion.php");

$edit = isset($_POST['edit']) ? $_POST['edit'] : 0;
$id = isset($_POST['id']) ? $_POST['id'] : "";
$doc = isset($_POST['doc']) ? $_POST['doc'] : "";

$Title="Crear nuevo campo adicional";
$Type=1;


$SQL_TipoDoc=Seleccionar("uvw_tbl_ObjetosSAP","*",'','CategoriaObjeto, DeTipoDocumento');

if($edit==1){
	$SQL_Data=Seleccionar("uvw_tbl_CamposAdicionalesDoc","*","ID='".$id."'");
	$row_Data=sqlsrv_fetch_array($SQL_Data);
	$Title="Editar campo adicional";
	$Type=2;
}

?>
<form id="frm_NewParam" method="post" action="parametros_campos_adicionales.php">
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
					<label class="control-label">Tipo de documento <span class="text-danger">*</span></label>
					<select name="TipoDoc" class="form-control" id="TipoDoc" required <?php if($edit==1){echo "disabled";}?>>
							<option value="">Seleccione...</option>
					  <?php $CatActual="";
						while($row_TipoDoc=sqlsrv_fetch_array($SQL_TipoDoc)){
							if($CatActual!=$row_TipoDoc['CategoriaObjeto']){
								echo "<optgroup label='".$row_TipoDoc['CategoriaObjeto']."'></optgroup>";
								$CatActual=$row_TipoDoc['CategoriaObjeto'];
							}
						?>
							<option value="<?php echo $row_TipoDoc['IdTipoDocumento'];?>" <?php if((($edit==1)&&(isset($row_Data['TipoObjeto']))&&(strcmp($row_TipoDoc['IdTipoDocumento'],$row_Data['TipoObjeto'])==0))||(($edit==0)&&($doc!="")&&($doc==$row_TipoDoc['IdTipoDocumento']))){ echo "selected=\"selected\"";}?>><?php echo $row_TipoDoc['DeTipoDocumento'];?></option>
					  <?php }?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Nombre interno del campo <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="NombreCampo" id="NombreCampo" required autocomplete="off" value="<?php if($edit==1){echo $row_Data['NombreCampo'];}?>">
				</div>
				<div class="form-group">
					<label class="control-label">Nombre a mostrar <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="LabelCampo" id="LabelCampo" required autocomplete="off" value="<?php if($edit==1){echo $row_Data['LabelCampo'];}?>">
				</div>
				<div class="form-group">
					<label class="control-label">Tipo de campo <span class="text-danger">*</span></label>
					<select name="TipoCampo" class="form-control" id="TipoCampo" required>
						<option value="">Seleccione...</option>
						<option value="Texto" <?php if(($edit==1)&&($row_Data['TipoCampo']=="Texto")){ echo "selected=\"selected\"";}?>>Campo de texto</option>
						<option value="Comentario" <?php if(($edit==1)&&($row_Data['TipoCampo']=="Comentario")){ echo "selected=\"selected\"";}?>>Campo de comentarios</option>
						<option value="Fecha" <?php if(($edit==1)&&($row_Data['TipoCampo']=="Fecha")){ echo "selected=\"selected\"";}?>>Campo de fecha</option>
						<option value="Multiple" <?php if(($edit==1)&&($row_Data['TipoCampo']=="Multiple")){ echo "selected=\"selected\"";}?>>Campo de opción multiple</option>
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
	<input type="hidden" id="doc" name="doc" value="<?php echo $doc;?>" />
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
	 
 });
</script>