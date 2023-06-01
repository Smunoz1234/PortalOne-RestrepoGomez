<?php
require_once "includes/conexion.php";

$edit = isset($_POST['edit']) ? $_POST['edit'] : 0;
$id = isset($_POST['id']) ? $_POST['id'] : "";

$Title = "Crear Nueva Serie";
$Type = 1;

$SQL_TipoDoc = Seleccionar("uvw_tbl_ObjetosSAP", "*", '', 'CategoriaObjeto, DeTipoDocumento');

if ($edit == 1) {
    $SQL_Data = Seleccionar("uvw_tbl_SeriesSucursalesAlmacenes", "*", "ID='$id'");
    $row_Data = sqlsrv_fetch_array($SQL_Data);

    $IdTipoDocumento = $row_Data['IdTipoDocumento'] ?? "";
    $IdSeries = $row_Data['IdSeries'] ?? "";
    $IdSucursal = $row_Data['IdSucursal'] ?? "";
    $WhsCode = $row_Data['WhsCode'] ?? "";
    $ToWhsCode = $row_Data['ToWhsCode'] ?? "";
    $IdBodegaDefecto = $row_Data['IdBodegaDefecto'] ?? "";

    $Title = "Editar Serie";
    $Type = 2;

    // Series
    $SQL_Series = Seleccionar("uvw_Sap_tbl_SeriesDocumentos", "IdSeries, DeSeries", "IdTipoDocumento='$IdTipoDocumento'");
}

// Sucursal
$DimSeries = intval(ObtenerVariable("DimensionSeries"));
$SQL_Sucursal = Seleccionar("uvw_Sap_tbl_DimensionesReparto", "OcrCode, OcrName", "DimCode='$DimSeries'");

// Almacen origen
$SQL_AlmOrigen = Seleccionar("uvw_Sap_tbl_Almacenes", "WhsCode, WhsName");

// Almacen Destino
$SQL_AlmDestino = Seleccionar("uvw_Sap_tbl_Almacenes", "WhsCode, WhsName");

// Almacen Defecto
$SQL_AlmDefecto = Seleccionar("uvw_Sap_tbl_Almacenes", "WhsCode, WhsName");
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

<form id="frm_NewParam" method="post" action="gestionar_series.php" enctype="multipart/form-data">
	<div class="modal-header">
		<h4 class="modal-title">
			<?php echo $Title; ?>
		</h4>
	</div>

	<div class="modal-body">
		<div class="form-group">
			<div class="ibox-content">
				<?php include "includes/spinner.php";?>

				<div class="form-group">
					<label class="control-label">Tipo de documento <span class="text-danger">*</span></label>
					<select name="TipoDoc" class="form-control" id="TipoDoc" required>
						<option value="">Seleccione...</option>

						<?php $CatActual = "";?>
						<?php while ($row_TipoDoc = sqlsrv_fetch_array($SQL_TipoDoc)) {?>
							<?php if ($CatActual != $row_TipoDoc['CategoriaObjeto']) {?>
								<?php echo "<optgroup label='" . $row_TipoDoc['CategoriaObjeto'] . "'></optgroup>"; ?>
								<?php $CatActual = $row_TipoDoc['CategoriaObjeto'];?>
							<?php }?>

							<option value="<?php echo $row_TipoDoc['IdTipoDocumento']; ?>" <?php if (($edit == 1) && ($row_TipoDoc['IdTipoDocumento'] == $IdTipoDocumento)) {echo "selected";}?>><?php echo $row_TipoDoc['DeTipoDocumento']; ?></option>
						<?php }?>
					</select>
				</div>

				<div class="form-group">
					<label class="control-label">Serie del documento <span class="text-danger">*</span></label>

					<select name="SerieDoc" class="form-control select2" id="SerieDoc" required>
						<option value="">Seleccione...</option>

						<?php if ($edit == 1) {?>
							<?php while ($row_Series = sqlsrv_fetch_array($SQL_Series)) {?>
								<option value="<?php echo $row_Series['IdSeries']; ?>" <?php if (($edit == 1) && ($row_Series['IdSeries'] == $IdSeries)) {echo "selected";}?>>
									<?php echo $row_Series['IdSeries'] . " - " . $row_Series['DeSeries']; ?>
								</option>
							<?php }?>
						<?php }?>
					</select>
				</div>

				<div class="form-group">
					<label class="control-label">Dimensión 1 <span class="text-danger">*</span></label>

					<select name="IdSucursal" class="form-control select2" id="IdSucursal" required>
						<option value="">Seleccione...</option>

						<?php while ($row_Sucursal = sqlsrv_fetch_array($SQL_Sucursal)) {?>
							<option value="<?php echo $row_Sucursal['OcrCode']; ?>" <?php if (($edit == 1) && ($row_Sucursal['OcrCode'] == $IdSucursal)) {echo "selected";}?>>
								<?php echo $row_Sucursal['OcrCode'] . " - " . $row_Sucursal['OcrName']; ?>
							</option>
						<?php }?>
					</select>
				</div>

				<div class="form-group">
					<label class="control-label">Almacén origen  <span class="text-danger">*</span></label>

					<select name="WhsCode" class="form-control select2" id="WhsCode" required>
						<option value="">Seleccione...</option>

						<?php while ($row_AlmOrigen = sqlsrv_fetch_array($SQL_AlmOrigen)) {?>
							<option value="<?php echo $row_AlmOrigen['WhsCode']; ?>" <?php if (($edit == 1) && ($row_AlmOrigen['WhsCode'] == $WhsCode)) {echo "selected";}?>>
								<?php echo $row_AlmOrigen['WhsCode'] . " - " . $row_AlmOrigen['WhsName']; ?>
							</option>
						<?php }?>
					</select>
				</div>

				<div class="form-group">
					<label class="control-label">Almacén destino</label>

					<select name="ToWhsCode" class="form-control select2" id="ToWhsCode">
							<option value="">(Ninguno)</option>

							<?php while ($row_AlmDestino = sqlsrv_fetch_array($SQL_AlmDestino)) {?>
								<option value="<?php echo $row_AlmDestino['WhsCode']; ?>" <?php if (($edit == 1) && ($row_AlmDestino['WhsCode'] == $ToWhsCode)) {echo "selected";}?>>
									<?php echo $row_AlmDestino['WhsCode'] . " - " . $row_AlmDestino['WhsName']; ?>
								</option>
							<?php }?>
					</select>
				</div>

				<div class="form-group">
					<label class="control-label">Almacén defecto</label>

					<select name="IdBodegaDefecto" class="form-control select2" id="IdBodegaDefecto">
						<option value="">(Ninguno)</option>

						<?php while ($row_AlmDefecto = sqlsrv_fetch_array($SQL_AlmDefecto)) {?>
							<option value="<?php echo $row_AlmDefecto['WhsCode']; ?>" <?php if (($edit == 1) && ($row_AlmDefecto['WhsCode'] == $IdBodegaDefecto)) {echo "selected";}?>>
								<?php echo $row_AlmDefecto['WhsCode'] . "-" . $row_AlmDefecto['WhsName']; ?>
							</option>
						<?php }?>
					</select>
				</div>

			</div> <!-- ibox-content -->
		</div> <!-- form-group -->
	</div> <!-- modal-body -->

	<div class="modal-footer">
		<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i> Aceptar</button>
		<button type="button" class="btn btn-danger m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
	</div>

	<input type="hidden" id="MM_Insert" name="MM_Insert" value="1" />
	<input type="hidden" id="ID" name="ID" value="<?php echo $id; ?>" />
	<input type="hidden" id="type" name="type" value="<?php echo $Type; ?>" />
</form>

<script>
$(document).ready(function() {
	$(".select2").select2();

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

	$("#TipoDoc").change(function(){
		$('.ibox-content').toggleClass('sk-loading',true);

		var ar=document.getElementById('TipoDoc').value.split("__");
		var TipoDoc=ar[0];

		$.ajax({
			type: "POST",
			url: "ajx_cbo_select.php?type=25&id="+TipoDoc,
			success: function(response){
				$('#SerieDoc').html(response);

				$('.ibox-content').toggleClass('sk-loading',false);
			}
		});
	});
});
</script>