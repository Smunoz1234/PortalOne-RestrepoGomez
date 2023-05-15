<?php
require_once "includes/conexion.php";

$Title = "Crear nuevo registro";
$Metodo = 1;

$edit = isset($_POST['edit']) ? $_POST['edit'] : 0;
$doc = isset($_POST['doc']) ? $_POST['doc'] : "";
$id = isset($_POST['id']) ? $_POST['id'] : "";

$SQL_CategoriasModal = Seleccionar('uvw_tbl_PortalClientes_Categorias', '*');
$indicadorJerarquia = "&nbsp;&nbsp;&nbsp;";

$SQL_FuncionesModal = Seleccionar('tbl_PortalClientes_Funciones', '*');

$ids_perfiles = array();
$SQL_PerfilesUsuarios = Seleccionar('uvw_tbl_PerfilesUsuarios', '*');

if ($edit == 1 && $id != "") {
    $Title = "Editar registro";
    $Metodo = 2;

    if ($doc == "Categoria") {
        $SQL = Seleccionar('tbl_PortalClientes_Categorias', '*', "[id]='" . $id . "'");
        $row = sqlsrv_fetch_array($SQL);
    } elseif ($doc == "Funcion") {
        $SQL = Seleccionar('tbl_PortalClientes_Funciones', '*', "[id]='" . $id . "'");
        $row = sqlsrv_fetch_array($SQL);
    }

    $ids_perfiles = isset($row['perfiles']) ? explode(";", $row['perfiles']) : [];
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

<form id="frm_NewParam" method="post" action="parametros_portal_Clientes.php" enctype="multipart/form-data">

<div class="modal-header">
	<h4 class="modal-title">
		<?php echo "Crear Nueva $doc"; ?>
	</h4>
</div>

<div class="modal-body">
	<div class="form-group">
		<div class="ibox-content">
			<?php include "includes/spinner.php";?>

			<?php if ($doc == "Categoria") {?>

				<!-- Inicio Categoria -->
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">Nombre Categoria <span class="text-danger">*</span></label>
						<input type="text" class="form-control" autocomplete="off" required name="NombreCategoria" id="NombreCategoria" value="<?php if ($edit == 1) {echo $row['nombre_categoria'];}?>">
					</div>
					<div class="col-md-6">
						<label class="control-label">Estado <span class="text-danger">*</span></label>
						<select class="form-control" id="Estado" name="Estado">
							<option value="Y" <?php if (($edit == 1) && ($row['estado'] == "Y")) {echo "selected";}?>>ACTIVO</option>
							<option value="N" <?php if (($edit == 1) && ($row['estado'] == "N")) {echo "selected";}?>>INACTIVO</option>
						</select>
					</div>
				</div>

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-12">
						<label class="control-label">Tipo <span class="text-danger">*</span></label>
						<select class="form-control" id="Tipo" name="Tipo">
							<option value="A" <?php if (($edit == 1) && ($row['tipo'] == "A")) {echo "selected";}?>>Archivo</option>
							<option value="R" <?php if (($edit == 1) && ($row['tipo'] == "R")) {echo "selected";}?>>Ruta</option>
						</select>
					</div>
				</div>

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-12">
						<label class="control-label">Categoria Padre</label>
						<select name="ID_CategoriaPadre" class="form-control select2" id="ID_CategoriaPadre">
							<option value="">[Raíz]</option>
							<?php while ($row_CategoriaModal = sqlsrv_fetch_array($SQL_CategoriasModal)) {?>
								<option value="<?php echo $row_CategoriaModal['id']; ?>" <?php if ((isset($row['id_categoria_padre'])) && (strcmp($row_CategoriaModal['id'], $row['id_categoria_padre']) == 0)) {echo "selected";}?>><?php echo $row_CategoriaModal['nombre_categoria']; ?></option>
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
						<textarea name="Comentarios" rows="3" maxlength="3000" class="form-control" id="Comentarios" type="text"><?php if ($edit == 1) {echo $row['comentarios'];}?></textarea>
					</div>
				</div>
				<br><br>
				<!-- Fin Categoria -->

			<?php } elseif ($doc == "Funcion") {?>

			<!-- Inicio Consulta -->
			<div class="form-group">
				<div class="col-md-6">
					<label class="control-label">Categoria <span class="text-danger">*</span></label>
					<select name="ID_Categoria" class="form-control select2" id="ID_Categoria" required>
						<option value="" disabled selected>Seleccione...</option>
						<?php while ($row_CategoriaModal = sqlsrv_fetch_array($SQL_CategoriasModal)) {?>
							<option value="<?php echo $row_CategoriaModal['id']; ?>" <?php if ((isset($row['id_categoria'])) && (strcmp($row_CategoriaModal['id'], $row['id_categoria']) == 0)) {echo "selected";}?>>
								<?php echo str_repeat($indicadorJerarquia, ($row_CategoriaModal['nivel'])) . ' ' . $row_CategoriaModal['nombre_categoria']; ?>
							</option>
						<?php }?>
					</select>
				</div>

				<div class="col-md-6">
					<label class="control-label">Estado <span class="text-danger">*</span></label>
					<select class="form-control" id="Estado" name="Estado">
						<option value="Y" <?php if (($edit == 1) && ($row['estado'] == "Y")) {echo "selected";}?>>ACTIVO</option>
						<option value="N" <?php if (($edit == 1) && ($row['estado'] == "N")) {echo "selected";}?>>INACTIVO</option>
					</select>
				</div>
			</div>

			<br><br><br><br>
			<div class="form-group">
				<div class="col-md-6">
					<label class="control-label">Ruta <span class="text-danger">*</span></label>
					<input type="text" class="form-control" autocomplete="off" required name="Ruta" id="Ruta" value="<?php if ($edit == 1) {echo $row['ruta'];}?>">
				</div>

				<div class="col-md-6">
					<label class="control-label">Etiqueta <span class="text-danger">*</span></label>
					<input type="text" class="form-control" autocomplete="off" name="EtiquetaConsulta" id="EtiquetaConsulta" value="<?php if ($edit == 1) {echo $row['etiqueta_consulta'];}?>">
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
					<textarea name="Comentarios" rows="3" maxlength="3000" class="form-control" id="Comentarios" type="text"><?php if ($edit == 1) {echo $row['comentarios'];}?></textarea>
				</div>
			</div>
			<br><br>
			<!-- Fin Consulta -->

			<?php } ?>
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

	<?php if (($edit == 1) && ($id != "")) {?>
		$('#VistaLista').trigger('change');
		$('#TipoCampo').trigger('change');
	<?php }?>
 });
</script>
