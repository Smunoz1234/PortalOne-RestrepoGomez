<?php
require_once "includes/conexion.php";

$Title = "Crear nuevo registro";
$Metodo = 1;

$edit = isset($_POST['edit']) ? $_POST['edit'] : 0;
$doc = isset($_POST['doc']) ? $_POST['doc'] : "";
$id = isset($_POST['id']) ? $_POST['id'] : "";

// Subconsultas, SMM 07/08/2022
$SQL_Entidad = Seleccionar('tbl_TipoEntidadSN', '*');
$SQL_Municipio = Seleccionar('uvw_tbl_Municipios', '*');

if ($edit == 1 && $id != "") {
    $Title = "Editar registro";
    $Metodo = 2;

    if ($doc == "Retencion") {
        $SQL = Seleccionar('tbl_MunicipiosRetenciones', '*', "id='" . $id . "'");
        $row = sqlsrv_fetch_array($SQL);
    }
}
?>

<style>
	/**
	* Estilos para el uso del componente select2-multiple en un modal.
	*
	* @author Stiven Muñoz Murillo
	* @version 26/07/2022
	*/

	.select2-container {
		z-index: 10000;
	}

	.select2-search--inline {
    display: contents;
	}

	.select2-search__field:placeholder-shown {
		width: 100% !important;
	}

	/**
	* Iconos del panel de información.
	* SMM, 18/08/2022
	*/
	.panel-heading  a:before {
		font-family: 'Glyphicons Halflings';
		content: "\e114";
		float: right;
		transition: all 0.5s;
	}
	.panel-heading.active a:before {
		-webkit-transform: rotate(180deg);
		-moz-transform: rotate(180deg);
		transform: rotate(180deg);
	}
</style>

<form id="frm_NewParam" method="post" action="parametros_asistente_socios_negocio.php" enctype="multipart/form-data">
<div class="modal-header">
	<h4 class="modal-title">
		<?php echo $Title; ?>
	</h4>
</div>
<div class="modal-body">
	<div class="form-group">
		<div class="ibox-content">
			<?php include "includes/spinner.php";?>
			<?php if ($doc == "Retencion") {?>
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">ID Retencion <span class="text-danger">*</span></label>
						<input type="text" class="form-control" name="id_retencion" id="id_retencion" required autocomplete="off" value="<?php if ($edit == 1) {echo $row['id_retencion'];}?>">
					</div>
					<div class="col-md-6">
						<label class="control-label">Tipo Entidad <span class="text-danger">*</span></label>
						<select name="id_tipo_entidad" class="form-control select2" id="id_tipo_entidad" required>
							<option value="">Seleccione...</option>
							<?php while ($row_Entidad = sqlsrv_fetch_array($SQL_Entidad)) {?>
								<option value="<?php echo $row_Entidad['ID_TipoEntidad']; ?>" <?php if ((isset($row['id_tipo_entidad'])) && (strcmp($row_Entidad['ID_TipoEntidad'], $row['id_tipo_entidad']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Entidad['NombreEntidad']; ?></option>
							<?php }?>
						</select>
					</div>
				</div>

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">Municipio <span class="text-danger">*</span></label>
						<select name="id_municipio" class="form-control select2" id="id_municipio" required>
							<option value="">Seleccione...</option>
							<?php while ($row_Municipio = sqlsrv_fetch_array($SQL_Municipio)) {?>
								<option value="<?php echo $row_Municipio['Codigo']; ?>" <?php if ((isset($row['id_municipio'])) && (strcmp($row_Municipio['Codigo'], $row['id_municipio']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Municipio['Ciudad']; ?></option>
							<?php }?>
						</select>
					</div>
					<div class="col-md-6">
						<label class="control-label">Estado <span class="text-danger">*</span></label>
						<select class="form-control" id="estado" name="estado">
							<option value="Y" <?php if (($edit == 1) && ($row['estado'] == "Y")) {echo "selected=\"selected\"";}?>>ACTIVO</option>
							<option value="N" <?php if (($edit == 1) && ($row['estado'] == "N")) {echo "selected=\"selected\"";}?>>INACTIVO</option>
						</select>
					</div>
				</div>
			<?php }?>
			<!-- Fin Retención -->
		</div>
	</div>
</div>
<div class="modal-footer">
	<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i> Aceptar</button>
	<button type="button" class="btn btn-warning m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
</div>
	<input type="hidden" id="TipoDoc" name="TipoDoc" value="<?php echo $doc; ?>" />
	<input type="hidden" id="ID_Actual" name="ID_Actual" value="<?php echo $id; ?>" />
	<input type="hidden" id="Metodo" name="Metodo" value="<?php echo $Metodo; ?>" />
	<input type="hidden" id="frmType" name="frmType" value="1" />
</form>
<script>
$(document).ready(function(){
	// SMM, 19/08/2022
	$('.panel-collapse').on('show.bs.collapse', function () {
    $(this).siblings('.panel-heading').addClass('active');
  });

  $('.panel-collapse').on('hide.bs.collapse', function () {
    $(this).siblings('.panel-heading').removeClass('active');
  });
  // Hasta aquí, 19/08/2022

	$("#frm_NewParam").validate({
		submitHandler: function(form){
			let Metodo = document.getElementById("Metodo").value;
			if(Metodo!="3"){
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
			}else{
			$('.ibox-content').toggleClass('sk-loading',true);
			form.submit();
			}
	}
	 });
	$('.chosen-select').chosen({width: "100%"});

	// SMM, 26/07/2022
	$(".select2").select2();
 });
</script>

<script>
function Eliminar(doc,id){
	var result=true;

	Swal.fire({
		title: "¿Está seguro que desea eliminar este registro?",
		icon: "question",
		showCancelButton: true,
		confirmButtonText: "Si, confirmo",
		cancelButtonText: "No"
	}).then((result) => {
		if (result.isConfirmed) {
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:34,
					  item:doc,
					  id:id
					 },
				dataType:'json',
				async: false,
				success: function(data){
					if(data.Estado=='0'){
						result=false;
						Swal.fire({
							title: data.Title,
							text: data.Mensaje,
							icon: data.Icon,
						});
						$('.ibox-content').toggleClass('sk-loading',false);
					}else{
						document.getElementById("Metodo").value="3";
						$("#frm_NewParam").submit();
					}
				}
			});
		}
	});

	return result;
}

// SMM, 27/07/2022
// doc(useless) -> nombre del formulario
// id(useless) -> identificador del la fila
function Validar(doc, id){
	Swal.fire({
		title: "¿Está seguro que desea ejecutar la consulta?",
		icon: "question",
		showCancelButton: true,
		confirmButtonText: "Si, confirmo",
		cancelButtonText: "No"
	}).then((result) => {
		if (result.isConfirmed) {
			// Cargando...
			$('.ibox-content').toggleClass('sk-loading', true);

			$.ajax({
				url:"ajx_ejecutar_query.php",
				data: {
					type: 1,
					query: $("#Condiciones").val()
				},
				dataType:'json',
				async: false,
				success: function(data) {
					console.log(data);

					$("#CondicionesContainer").css("display", "block");
					$("#Validacion").val(JSON.stringify(data, null, '\t'));

					// Raw Response.
					$("#Raw").prop("href", `ajx_ejecutar_query.php?type=1&query=${$("#Condiciones").val()}`);

					// Carga terminada.
					$('.ibox-content').toggleClass('sk-loading', false);
				},
				error: function(error) {
					console.error("Error en Línea 322");
					$('.ibox-content').toggleClass('sk-loading', false);
				}
			});
		}
	});
}
</script>