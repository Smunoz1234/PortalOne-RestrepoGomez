<?php  
require_once("includes/conexion.php");

?>
<form id="frm_NewParam" method="post" action="parametros_asistentes.php">
	<div class="modal-header">
		<h4 class="modal-title">
			Crear nuevo valor
		</h4>
	</div>
	<div class="modal-body">
		<div class="form-group">
			<div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
				<div class="form-group">
					<label class="control-label">Tipo de objeto <span class="text-danger">*</span></label>
					<select name="TipoObjeto" class="form-control" id="TipoObjeto" required>
						<option value="">Seleccione...</option>
						<option value="66">Rutas (actividades)</option>
						<option value="191">Llamadas de servicio</option>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Nombre de parámetro <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="NombreVariable" id="NombreVariable" required autocomplete="off">
				</div>
				<div class="form-group">
					<label class="control-label">Nombre a mostrar <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="NombreMostrar" id="NombreMostrar" required autocomplete="off">
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i> Aceptar</button>
		<button type="button" class="btn btn-danger m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
	</div>
	<input type="hidden" id="MM_Insert" name="MM_Insert" value="1" />
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