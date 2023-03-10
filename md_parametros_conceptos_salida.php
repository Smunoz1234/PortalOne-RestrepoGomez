<?php
require_once "includes/conexion.php";

$Title = "Crear nuevo registro";
$Metodo = 1;

$edit = isset($_POST['edit']) ? $_POST['edit'] : 0;
$doc = isset($_POST['doc']) ? $_POST['doc'] : "";
$id = isset($_POST['id']) ? $_POST['id'] : "";

// Subconsultas, SMM 07/08/2022
$SQL_Entidad = Seleccionar('tbl_TipoEntidadSN', '*');
$SQL_CuentasContables = Seleccionar('uvw_Sap_tbl_PUC_Conceptos_Salidas', '*');

if ($edit == 1 && $id != "") {
    $Title = "Editar registro";
    $Metodo = 2;

    if ($doc == "Concepto") {
        $SQL = Seleccionar('tbl_SalidaInventario_Conceptos', '*', "id_concepto_salida='" . $id . "'");
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

<form id="frm_NewParam" method="post" action="parametros_conceptos_salida.php" enctype="multipart/form-data">
<div class="modal-header">
	<h4 class="modal-title">
		<?php echo $Title; ?>
	</h4>
</div>
<div class="modal-body">
	<div class="form-group">
		<div class="ibox-content">
			<?php include "includes/spinner.php";?>
			<?php if ($doc == "Concepto") {?>
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">ID Concepto Salida <span class="text-danger">*</span></label>
						<input type="text" class="form-control" name="id_concepto_salida" id="id_concepto_salida" required autocomplete="off" value="<?php if ($edit == 1) {echo $row['id_concepto_salida'];}?>" <?php if ($edit == 1) {echo "readonly";}?>>
					</div>

					<div class="col-md-6">
						<label class="control-label">Concepto Salida <span class="text-danger">*</span></label>
						<input type="text" class="form-control" name="concepto_salida" id="concepto_salida" required autocomplete="off" value="<?php if ($edit == 1) {echo $row['concepto_salida'];}?>">
					</div>
				</div>

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">Cuenta Contable <span class="text-danger">*</span></label>
						<select name="cuenta_contable" class="form-control select2" id="cuenta_contable" required>
							<option value="">Seleccione...</option>
							<?php while ($row_CuentaContable = sqlsrv_fetch_array($SQL_CuentasContables)) {?>
								<option value="<?php echo $row_CuentaContable['IdCuenta'] . "-" . $row_CuentaContable['Cuenta']; ?>" <?php if ((isset($row['cuenta_contable'])) && (strcmp($row_CuentaContable['IdCuenta'], $row['id_cuenta_contable']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_CuentaContable['Cuenta']." (".$row_CuentaContable['IdCuenta'].")"; ?></option>
							<?php }?>
						</select>
					</div>

					<div class="col-md-6">
						<label class="control-label">Estado <span class="text-danger">*</span></label>
						<select class="form-control" id="estado" name="estado">
							<option value="Y" <?php if (($edit == 1) && ($row['estado'] == "Y")) {echo "selected";}?>>ACTIVO</option>
							<option value="N" <?php if (($edit == 1) && ($row['estado'] == "N")) {echo "selected";}?>>INACTIVO</option>
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

	<!-- SMM, 12/01/2023 -->
	<input type="hidden" name="id_cc" id="id_cc" value="<?php if ($edit == 1) {echo $row['id_cuenta_contable'];}?>">
	<input type="hidden" name="cc" id="cc" value="<?php if ($edit == 1) {echo $row['cuenta_contable'];}?>">
</form>

<script>
$(document).ready(function(){
	// SMM, 22/12/2022
	$("#cuenta_contable").on("change", function() {
		let cuenta_contable = $(this).val().split("-");
		$("#id_cc").val(cuenta_contable[0]);
		$("#cc").val(cuenta_contable[1]);
	});
	// Hasta aquí, 22/12/2022

	$('.panel-collapse').on('show.bs.collapse', function () {
    	$(this).siblings('.panel-heading').addClass('active');
  	});
  	$('.panel-collapse').on('hide.bs.collapse', function () {
    	$(this).siblings('.panel-heading').removeClass('active');
  	});

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
