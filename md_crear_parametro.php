<?php  
require_once("includes/conexion.php");

?>
<form id="frm_NewParam" method="post" action="parametros_generales.php">
	<div class="modal-header">
		<h4 class="modal-title">
			Crear nuevo parámetro global
		</h4>
	</div>
	<div class="modal-body">
		<div class="form-group">
			<div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
				<div class="form-group">
					<label class="control-label">Plataforma <span class="text-danger">*</span></label>
					<select name="Plataforma" class="form-control" id="Plataforma" required>
						<option value="">Seleccione...</option>
						<option value="1">PortalOne</option>
						<option value="2">ServiceOne</option>
						<option value="3">SalesOne</option>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Nombre de variable <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="NombreVariable" id="NombreVariable" required autocomplete="off">
				</div>
				<div class="form-group">
					<label class="control-label">Nombre a mostrar <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="NombreMostrar" id="NombreMostrar" required autocomplete="off">
				</div>
				<div class="form-group">
					<label class="control-label">Tipo de campo <span class="text-danger">*</span></label>
					<select name="TipoCampo" class="form-control" id="TipoCampo" required>
						<option value="text">Campo de texto</option>
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
</form>
<script>
 $(document).ready(function(){
	 $("#frm_NewParam").validate({
		 submitHandler: function(form){
			 if(Validar()){
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
		}
	 });
	 
 });
</script>
<script>
function Validar(){
	var result=true;
	
	$('.ibox-content').toggleClass('sk-loading',true);
	
	var variable=document.getElementById("NombreVariable").value;
	var plataforma=document.getElementById("Plataforma").value;
	
	$.ajax({
		url:"ajx_buscar_datos_json.php",
		data:{type:35,
			  nomvar:variable,
			  plat:plataforma
			 },
		dataType:'json',
		async: false,
		success: function(data){
			if(data.Result=='1'){
				result=false;
				Swal.fire({
					title: '¡Advertencia!',
					text: 'Ya existe el nombre de esta variable. Por favor verifique.',
					icon: 'warning',
				});	
				$('.ibox-content').toggleClass('sk-loading',false);
			}else{
				$('.ibox-content').toggleClass('sk-loading',false);
			}
		}
	});	
	
	return result;
}
</script>