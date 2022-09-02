<?php  
require_once("includes/conexion.php");

$edit = isset($_POST['edit']) ? $_POST['edit'] : 0;
$ext = isset($_POST['ext']) ? $_POST['ext'] : 0;
$idDetalle = isset($_POST['idDetalle']) ? $_POST['idDetalle'] : "";
$codPlant = isset($_POST['codPlant']) ? $_POST['codPlant'] : "";

$Title="Crear nuevo registro";
$Type=1;


$SQL_Campos=Seleccionar("tbl_Parametros_Asistentes","*","TipoObjeto=66","LabelCampo");

if($edit==1){
	
	$Title="Editar registros";
	$Type=2;
}

?>
<form id="frm_NewParam" method="post" action="plantilla_actividades.php">
	<div class="modal-header">
		<h4 class="modal-title">
			<?php echo $Title; ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="form-group">
			<div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
				<?php 
					while($row_Campos=sqlsrv_fetch_array($SQL_Campos)){
						$SQL_Data=Seleccionar('uvw_tbl_PlantillaActividades_Datos','*',"ID_Campo='".$row_Campos['ID_Campo']."' and IdDetalle='".$idDetalle."' and CodigoPlantilla='".$codPlant."'");
						$row_Data=sqlsrv_fetch_array($SQL_Data);
				?>
					<div class="form-group">
						<label class="control-label"><?php echo $row_Campos['LabelCampo'];?></label>
						<input type="text" class="form-control" name="<?php echo $row_Campos['ID_Campo'];?>" id="<?php echo $row_Campos['ID_Campo'];?>" autocomplete="off" value="<?php if($edit==1){echo $row_Data['Valor'];}?>">
					</div>
				<?php }?>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i> Aceptar</button>
		<button type="button" class="btn btn-danger m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
	</div>
	<input type="hidden" id="MM_Insert" name="MM_Insert" value="1" />
	<input type="hidden" id="idDetalle" name="idDetalle" value="<?php echo $idDetalle;?>" />
	<input type="hidden" id="type" name="type" value="<?php echo $Type;?>" />
	<input type="hidden" id="codPlantilla" name="codPlantilla" value="<?php echo $codPlant;?>" />
	<input type="hidden" id="tl" name="tl" value="<?php echo $edit;?>" />
	<input type="hidden" id="ext" name="ext" value="<?php echo $ext;?>" />
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