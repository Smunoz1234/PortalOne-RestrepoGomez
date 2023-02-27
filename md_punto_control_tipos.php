<?php
require_once "includes/conexion.php";

$Title = "Crear nuevo registro";
$Metodo = 1;

$edit = isset($_POST['edit']) ? $_POST['edit'] : 0;
$doc = isset($_POST['doc']) ? $_POST['doc'] : "";
$id = isset($_POST['id']) ? $_POST['id'] : "";

$SQL_FamiliasModal = Seleccionar('tbl_PuntoControl', '*');
$SQL_IconosModal = Seleccionar('tbl_PuntoControl_Iconos', '*');

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

<form id="frm_NewParam" method="post" action="parametros_Iconos_sap.php" enctype="multipart/form-data">

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
						<label class="control-label">Nombre Familia <span class="text-danger">*</span></label>
						<input type="text" class="form-control" autocomplete="off" required name="NombreFamilia" id="NombreFamilia" value="<?php if ($edit == 1) {echo $row['NombreFamilia'];}?>">
					</div>
					<div class="col-md-6">
						<label class="control-label">Estado <span class="text-danger">*</span></label>
						<select class="form-control" id="Estado" name="Estado">
							<option value="Y" <?php if (($edit == 1) && ($row['Estado'] == "Y")) {echo "selected=\"selected\"";}?>>ACTIVO</option>
							<option value="N" <?php if (($edit == 1) && ($row['Estado'] == "N")) {echo "selected=\"selected\"";}?>>INACTIVO</option>
						</select>
					</div>
				</div>

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-12">
						<label class="control-label">Familia Padre</label>
						<select name="ID_FamiliaPadre" class="form-control select2" id="ID_FamiliaPadre">
							<option value="">[Raíz]</option>
							<?php while ($row_FamiliaModal = sqlsrv_fetch_array($SQL_FamiliasModal)) {?>
								<option value="<?php echo $row_FamiliaModal['ID']; ?>" <?php if ((isset($row['ID_FamiliaPadre'])) && (strcmp($row_FamiliaModal['ID'], $row['ID_FamiliaPadre']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_FamiliaModal['NombreFamilia']; ?></option>
							<?php }?>
						</select>
					</div>
				</div>

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-12">
						<label class="control-label">Perfiles Usuarios</label>
						<select data-placeholder="Digite para buscar..." name="Perfiles[]" class="form-control select2" id="Perfiles" multiple>
							<?php while ($row_Perfil = sqlsrv_fetch_array($SQL_PerfilesUsuarios)) {?>
								<option value="<?php echo $row_Perfil['ID_PerfilUsuario']; ?>"
								<?php if (in_array($row_Perfil['ID_PerfilUsuario'], $ids_perfiles)) {echo "selected";}?>>
									<?php echo $row_Perfil['PerfilUsuario']; ?>
								</option>
							<?php }?>
						</select>
					</div>
				</div>

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-12">
						<label class="control-label">Comentarios</label>
						<textarea name="Comentarios" rows="3" maxlength="3000" class="form-control" id="Comentarios" type="text"><?php if ($edit == 1) {echo $row['Comentarios'];}?></textarea>
					</div>
				</div>
				<br><br>
				<!-- Fin Familia -->

			<?php } elseif ($doc == "Icono") {?>

				<!-- Inicio Icono -->
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">Familia <span class="text-danger">*</span></label>
						<select name="ID_Familia" class="form-control select2" id="ID_Familia" required>
							<option value="" disabled selected>Seleccione...</option>
							<?php while ($row_FamiliaModal = sqlsrv_fetch_array($SQL_FamiliasModal)) {?>
								<option value="<?php echo $row_FamiliaModal['ID']; ?>" <?php if ((isset($row['ID_Familia'])) && (strcmp($row_FamiliaModal['ID'], $row['ID_Familia']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_FamiliaModal['NombreFamilia']; ?></option>
							<?php }?>
						</select>
					</div>
					<div class="col-md-6">
						<label class="control-label">Estado <span class="text-danger">*</span></label>
						<select class="form-control" id="Estado" name="Estado">
							<option value="Y" <?php if (($edit == 1) && ($row['Estado'] == "Y")) {echo "selected=\"selected\"";}?>>ACTIVO</option>
							<option value="N" <?php if (($edit == 1) && ($row['Estado'] == "N")) {echo "selected=\"selected\"";}?>>INACTIVO</option>
						</select>
					</div>
				</div>

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">Procedimiento <span class="text-danger">*</span></label>
						<input type="text" class="form-control" autocomplete="off" required name="ProcedimientoIcono" id="ProcedimientoIcono" value="<?php if ($edit == 1) {echo $row['ProcedimientoIcono'];}?>">
					</div>

					<div class="col-md-6">
						<label class="control-label">Etiqueta <span class="text-danger">*</span></label>
						<input type="text" class="form-control" autocomplete="off" name="EtiquetaIcono" id="EtiquetaIcono" value="<?php if ($edit == 1) {echo $row['EtiquetaIcono'];}?>">
					</div>
				</div>

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-12">
						<label class="control-label">Parámetros</label>
						<input type="text" class="form-control" autocomplete="off" data-role="tagsinput" name="ParametrosTipo" id="ParametrosTipo" value="<?php if ($edit == 1) {echo $row['ParametrosTipo'];}?>" placeholder= "Ingrese una Tipo y utilice la tecla [ESP] para agregar">
					</div>
				</div>

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-12">
						<label class="control-label">Perfiles Usuarios</label>
						<select data-placeholder="Digite para buscar..." name="Perfiles[]" class="form-control select2" id="Perfiles" multiple>
							<?php while ($row_Perfil = sqlsrv_fetch_array($SQL_PerfilesUsuarios)) {?>
								<option value="<?php echo $row_Perfil['ID_PerfilUsuario']; ?>"
								<?php if (in_array($row_Perfil['ID_PerfilUsuario'], $ids_perfiles)) {echo "selected";}?>>
									<?php echo $row_Perfil['PerfilUsuario']; ?>
								</option>
							<?php }?>
						</select>
					</div>
				</div>

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-12">
						<label class="control-label">Comentarios</label>
						<textarea name="Comentarios" rows="3" maxlength="3000" class="form-control" id="Comentarios" type="text"><?php if ($edit == 1) {echo $row['Comentarios'];}?></textarea>
					</div>
				</div>
				<br><br>
				<!-- Fin Icono -->

			<?php } elseif ($doc == "Tipo") {?>

				<!-- Inicio Tipo -->
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">Icono <span class="text-danger">*</span></label>
						<select name="ID_Icono" class="form-control select2" id="ID_Icono" required>
							<option value="" disabled selected>Seleccione...</option>
							<?php while ($row_IconoModal = sqlsrv_fetch_array($SQL_IconosModal)) {?>
								<option value="<?php echo $row_IconoModal['ID']; ?>" <?php if ((isset($row['ID_Icono'])) && (strcmp($row_IconoModal['ID'], $row['ID_Icono']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_IconoModal['ProcedimientoIcono']; ?></option>
							<?php }?>
						</select>
					</div>

					<div class="col-md-6">
						<label class="control-label">Estado <span class="text-danger">*</span></label>
						<select class="form-control" id="Estado" name="Estado" required>
							<option value="Y" <?php if (($edit == 1) && ($row['Estado'] == "Y")) {echo "selected=\"selected\"";}?>>ACTIVO</option>
							<option value="N" <?php if (($edit == 1) && ($row['Estado'] == "N")) {echo "selected=\"selected\"";}?>>INACTIVO</option>
						</select>
					</div>
				</div>

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">Parámetro <span class="text-danger">*</span></label>
						<select name="ParametroTipo" class="form-control" id="ParametroTipo" required>
							<option value="">Seleccione...</option>
							<!-- Las demás opciones dependen de la Icono -->
						</select>
					</div>

					<div class="col-md-6">
						<label class="control-label">Etiqueta <span class="text-danger">*</span></label>
						<input type="text" class="form-control" autocomplete="off" required name="EtiquetaTipo" id="EtiquetaTipo" value="<?php if ($edit == 1) {echo $row['EtiquetaTipo'];}?>">
					</div>
				</div>

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-12">
						<label class="control-label">Tipo de campo <span class="text-danger">*</span></label>
						<select class="form-control" name="TipoCampo" id="TipoCampo" required>
							<option value="Texto" <?php if (($edit == 1) && ($row['TipoCampo'] == 'Texto')) {echo "selected=\"selected\"";}?>>Texto</option>
							<option value="Comentario" <?php if (($edit == 1) && ($row['TipoCampo'] == 'Comentario')) {echo "selected=\"selected\"";}?>>Comentario</option>
							<option value="Fecha" <?php if (($edit == 1) && ($row['TipoCampo'] == 'Fecha')) {echo "selected=\"selected\"";}?>>Fecha</option>
							<option value="Cliente" <?php if (($edit == 1) && ($row['TipoCampo'] == 'Cliente')) {echo "selected=\"selected\"";}?>>Cliente (Lista)</option>
							<option value="Sucursal" <?php if (($edit == 1) && ($row['TipoCampo'] == 'Sucursal')) {echo "selected=\"selected\"";}?>>Sucursal (Dependiendo del cliente)</option>
							<option value="Seleccion" <?php if (($edit == 1) && ($row['TipoCampo'] == 'Seleccion')) {echo "selected=\"selected\"";}?>>Selección (SI/NO)</option>
							<option value="Lista" <?php if (($edit == 1) && ($row['TipoCampo'] == 'Lista')) {echo "selected=\"selected\"";}?>>Lista (Personalizada)</option>
							<option value="Usuario" <?php if (($edit == 1) && ($row['TipoCampo'] == 'Usuario')) {echo "selected=\"selected\"";}?>>Usuario</option>
						</select>
					</div>
				</div>

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">Obligatorio <span class="text-danger">*</span></label>
						<select class="form-control" id="Obligatorio" name="Obligatorio" required>
							<option value="Y" <?php if (($edit == 1) && ($row['Obligatorio'] == "Y")) {echo "selected=\"selected\"";}?>>SI</option>
							<option value="N" <?php if (($edit == 1) && ($row['Obligatorio'] == "N")) {echo "selected=\"selected\"";}?>>NO</option>
						</select>
					</div>

					<div class="col-md-6">
						<label class="control-label">Multiple <span class="text-danger">*</span></label>
						<select class="form-control" id="Multiple" name="Multiple" disabled>
							<option value="N" <?php if (($edit == 1) && ($row['Multiple'] == "N")) {echo "selected=\"selected\"";}?>>NO</option>
							<option value="Y" <?php if (($edit == 1) && ($row['Multiple'] == "Y")) {echo "selected=\"selected\"";}?>>SI</option>
						</select>
					</div>
				</div>

				<div id="CamposVista" style="display: none;">
					<br><br><br><br>
					<div class="form-group">
						<div class="col-md-12">
							<label class="control-label">Vista <span class="text-danger">*</span></label>
							<select name="VistaLista" class="form-control select2" id="VistaLista" required>
								<option value="" disabled selected>Seleccione...</option>
								<?php while ($row_Lista = sqlsrv_fetch_array($SQL_Lista)) {?>
									<option value="<?php echo $row_Lista['TABLE_NAME']; ?>" <?php if ((isset($row['VistaLista'])) && (strcmp($row_Lista['TABLE_NAME'], $row['VistaLista']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Lista['TABLE_NAME']; ?></option>
								<?php }?>
							</select>
						</div>
					</div>

					<br><br><br><br>
					<div class="form-group">
						<div class="col-md-4">
							<label class="control-label">Valor</label>
							<select name="ValorLista" class="form-control" id="ValorLista" required>
								<option value="">Seleccione...</option>
								<!-- Generado por JS -->
							</select>
						</div>

						<div class="col-md-4">
							<label class="control-label">Etiqueta</label>
							<select name="EtiquetaLista" class="form-control" id="EtiquetaLista" required>
								<option value="">Seleccione...</option>
								<!-- Generado por JS -->
							</select>
						</div>

						<div class="col-md-4">
							<label class="control-label">Permitir "Todos"</label>
							<select name="PermitirTodos" id="PermitirTodos" class="form-control">
								<option value="N" <?php if (($edit == 1) && ($row['PermitirTodos'] == "N")) {echo "selected";}?>>NO</option>
								<option value="Y" <?php if (($edit == 1) && ($row['PermitirTodos'] == "Y")) {echo "selected";}?>>SI</option>
							</select>
						</div>
					</div>
				</div>

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-12">
						<label class="control-label">Comentarios</label>
						<textarea name="Comentarios" rows="3" maxlength="3000" class="form-control" id="Comentarios" type="text"><?php if ($edit == 1) {echo $row['Comentarios'];}?></textarea>
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
