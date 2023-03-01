<?php
require_once "includes/conexion.php";

$Title = "Crear nuevo registro";
$Metodo = 1;

$edit = isset($_POST['edit']) ? $_POST['edit'] : 0;
$doc = isset($_POST['doc']) ? $_POST['doc'] : "";
$id = isset($_POST['id']) ? $_POST['id'] : "";

$SQL_FamiliasModal = Seleccionar('tbl_Plagas_Familias', '*');
$SQL_IconosModal = Seleccionar('tbl_PuntoControl_Iconos', '*');
$SQL_SNZonasModal = Seleccionar('tbl_SociosNegocios_Zonas', '*');

$ids_perfiles = array();
$SQL_PerfilesUsuarios = Seleccionar('uvw_tbl_PerfilesUsuarios', '*');

if ($edit == 1 && $id != "") {
    $Title = "Editar registro";
    $Metodo = 2;

    if ($doc == "Familia") {
        $SQL = Seleccionar('tbl_PuntoControl', '*', "ID='" . $id . "'");
        $row = sqlsrv_fetch_array($SQL);
    } elseif ($doc == "Icono") {
        $SQL = Seleccionar('tbl_PuntoControl_Iconos', '*', "ID='" . $id . "'");
        $row = sqlsrv_fetch_array($SQL);
    } elseif ($doc == "Tipo") {
        $SQL = Seleccionar('tbl_PuntoControl_Tipos', '*', "ID='" . $id . "'");
        $row = sqlsrv_fetch_array($SQL);
    }

    $ids_perfiles = isset($row['Perfiles']) ? explode(";", $row['Perfiles']) : [];
}

$Cons_Lista = "EXEC sp_tables @table_owner = 'dbo', @table_type = \"'VIEW'\"";
$SQL_Lista = sqlsrv_query($conexion, $Cons_Lista);
?>

<style>
	.select2-container {
		z-index: 10000;
	}
	.select2-search--inline {
    display: contents;
	}
	.select2-search__field:placeholder-shown {
		width: 100% !important;
	}
</style>

<form id="frm_NewParam" method="post" action="punto_control_tipos.php" enctype="multipart/form-data">

<div class="modal-header">
	<h4 class="modal-title">
		<?php echo "Crear Nueva $doc"; ?>
	</h4>
</div>

<div class="modal-body">
	<div class="form-group">
		<div class="ibox-content">
			<?php include "includes/spinner.php";?>

			<?php if ($doc == "Familia") {?>

				<!-- Inicio Familia -->
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">ID Familia Plaga <span class="text-danger">*</span></label>
						<input type="text" class="form-control" autocomplete="off" required id="id_familia_plaga" name="id_familia_plaga" value="<?php if ($edit == 1) {echo $row['id_familia_plaga'];}?>">
					</div>

					<div class="col-md-6">
						<label class="control-label">Familia Plaga <span class="text-danger">*</span></label>
						<input type="text" class="form-control" autocomplete="off" required id="familia_plaga" name="familia_plaga" value="<?php if ($edit == 1) {echo $row['familia_plaga'];}?>">
					</div>
				</div> <!-- form-group -->

				<br><br>
				<!-- Fin Familia -->

			<?php } elseif ($doc == "Icono") {?>

				<!-- Inicio Icono -->
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">ID Icono <span class="text-danger">*</span></label>
						<input type="text" class="form-control" autocomplete="off" required name="id_icono" id="id_icono" value="<?php if ($edit == 1) {echo $row['id_icono'];}?>">
					</div>

					<div class="col-md-6">
						<label class="control-label">Icono <span class="text-danger">*</span></label>
						<input type="text" class="form-control" autocomplete="off" required name="icono" id="icono" value="<?php if ($edit == 1) {echo $row['icono'];}?>">
					</div>
				</div>

				<br><br>
				<!-- Fin Icono -->

			<?php } elseif ($doc == "Tipo") {?>

				<!-- Inicio Tipo -->
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">ID Tipo Punto Control</label>
						<input type="text" class="form-control" autocomplete="off" id="id_tipo_punto_control" name="id_tipo_punto_control" value="<?php if ($edit == 1) {echo $row['id_tipo_punto_control'];}?>">
					</div>

					<div class="col-md-6">
						<label class="control-label">Tipo Punto Control</label>
						<input type="text" class="form-control" autocomplete="off" id="tipo_punto_control" name="tipo_punto_control" value="<?php if ($edit == 1) {echo $row['tipo_punto_control'];}?>">
					</div>
				</div> <!-- form-group -->

				<br><br><br><br>
				<div class="form-group">
				<div class="col-md-6">
						<label class="control-label">ID Familia Plaga</label>
						<select id="id_familia_plaga" name="id_familia_plaga" class="form-control">
							<option value="" <?php if ($edit == 0) {echo "disabled selected";}?>>Seleccione...</option>

							<?php while ($row_Familia = sqlsrv_fetch_array($SQL_FamiliasModal)) {?>
								<option value="<?php echo $row_Familia['id_familia_plaga']; ?>" <?php if (isset($row['id_familia_plaga']) && ($row['id_familia_plaga'] == $row_Familia['id_familia_plaga'])) {echo "selected";}?>><?php echo $row_Familia['id_familia_plaga'] . " - " . $row_Familia['familia_plaga']; ?></option>
							<?php }?>
						</select>
					</div>
				</div> <!-- form-group -->

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">Estado <span class="text-danger">*</span></label>
						<select class="form-control" id="estado" name="estado" required>
							<option value="Y" <?php if (($edit == 1) && ($row['estado'] == "Y")) {echo "selected";}?>>ACTIVO</option>
							<option value="N" <?php if (($edit == 1) && ($row['estado'] == "N")) {echo "selected";}?>>INACTIVO</option>
						</select>
					</div>

					<div class="col-md-6">
						<label class="control-label">ID Icono</label>
						<select id="id_icono" name="id_icono" class="form-control">
							<option value="" <?php if ($edit == 0) {echo "disabled selected";}?>>Seleccione...</option>

							<?php while ($row_Icono = sqlsrv_fetch_array($SQL_IconosModal)) {?>
								<option value="<?php echo $row_Icono['id_icono']; ?>" <?php if (isset($row['id_icono']) && ($row['id_icono'] == $row_Icono['id_icono'])) {echo "selected";}?>><?php echo $row_Icono['id_icono'] . " - " . $row_Icono['icono']; ?></option>
							<?php }?>
						</select>
					</div>
				</div> <!-- form-group -->

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">ID Clase Control</label>
						<input type="text" class="form-control" autocomplete="off" id="id_clase_control" name="id_clase_control" value="<?php if ($edit == 1) {echo $row['id_clase_control'];}?>">
					</div>

					<div class="col-md-6">
						<label class="control-label">Clase Control</label>
						<input type="text" class="form-control" autocomplete="off" id="clase_control" name="clase_control" value="<?php if ($edit == 1) {echo $row['clase_control'];}?>">
					</div>
				</div> <!-- form-group -->

				<br><br><br><br>
				<div class="form-group">
					<div class="row">
						<div class="col-md-6">
						<label class="control-label">ID Color</label>
						</div>
						<div class="col-md-6">
						<label class="control-label">Código Prefijo</label>
						</div>
					</div>

					<div class="row">
						<div class="col-md-2">
						<input type="color" class="form-control" autocomplete="off" id="id_color" name="id_color" value="<?php if ($edit == 1) {echo $row['id_color'];}?>" oninput="$('#color').val(this.value);">
						</div>
						<div class="col-md-4">
						<input type="text" class="form-control" id="color" value="<?php if ($edit == 1) {echo $row['id_color'];}?>" readonly>
						</div>
						<div class="col-md-6">
						<div class="form-group">
							<input type="text" class="form-control" autocomplete="off" id="codigo_prefijo" name="codigo_prefijo" value="<?php if ($edit == 1) {echo $row['codigo_prefijo'];}?>">
						</div>
						</div>
					</div>
				</div> <!-- form-group -->

				<div class="form-group">
					<div class="col-md-12">
						<label class="control-label">Descripción</label>
						<textarea name="descripcion" rows="3" maxlength="250" class="form-control" id="descripcion" type="text"><?php if ($edit == 1) {echo $row['descripcion'];}?></textarea>
					</div>
				</div>

				<br><br>
				<!-- Fin Tipo -->

			<?php }?>
		</div> <!-- ibox-content -->
	</div> <!-- form-group -->
</div> <!-- modal-body -->

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
$(document).ready(function() {
	// Activación del componente "tagsinput"
	$('input[data-role=tagsinput]').tagsinput({
		confirmKeys: [32, 44] // Espacio y coma.
	});

	// Ajusto el ancho del componente "tagsinput"
	$('.bootstrap-tagsinput').css("display", "block");
	$('.bootstrap-tagsinput > input').css("width", "100%");

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
	$(".select2").select2();

	$("#TipoCampo").on("change", function() {
		if($(this).val() == "Sucursal" || $(this).val() == "Lista") {
			$("#Multiple").prop("disabled", false);
		} else {
			$("#Multiple").prop("disabled", true);
		}

		if($(this).val() == "Lista") {
			$("#CamposVista").css("display", "block");
		} else {
			$("#CamposVista").css("display", "none");
		}
	});

	// Cargar lista de campos dependiendo de la vista.
	$("#VistaLista").on("change", function() {
		$.ajax({
			type: "POST",
			url: `ajx_cbo_select.php?type=12&id=${$(this).val()}&obligatorio=1`,
			success: function(response){
				$('#EtiquetaLista').html(response).fadeIn();
				$('#ValorLista').html(response).fadeIn();

				<?php if (($edit == 1) && ($id != "")) {?>
					$('#EtiquetaLista').val("<?php echo $row['EtiquetaLista'] ?? ""; ?>");
					$('#ValorLista').val("<?php echo $row['ValorLista'] ?? ""; ?>");
				<?php }?>

				$('#EtiquetaLista').trigger('change');
				$('#ValorLista').trigger('change');
			}
		});
	});

	// Cargar Tipos dependiendo de la Icono.
	$("#ID_Icono").on("change", function() {
		$.ajax({
			type: "POST",
			url: `ajx_cbo_select.php?type=44&id=${$(this).val()}&input=<?php echo $row['ParametroTipo'] ?? ""; ?>`,
			success: function(response){
				$('#ParametroTipo').html(response).fadeIn();
				$('#ParametroTipo').trigger('change');
			}
		});
	});


	<?php if (($edit == 1) && ($id != "")) {?>
		$('#VistaLista').trigger('change');

		$('#ID_Icono').trigger('change');
		$('#TipoCampo').trigger('change');
	<?php }?>
 });
</script>
