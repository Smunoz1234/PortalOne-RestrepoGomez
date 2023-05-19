<?php
require_once "includes/conexion.php";
// PermitirAcceso(318);

$Where = "";
$namesucursal = $_GET['namesucursal'] ?? "";

// SMM, 05/16/2023
$SQL_ID = Seleccionar("uvw_tbl_PuntoControl", "MAX(id_interno) + 1 AS max_id_interno");
$row_ID = sqlsrv_fetch_array($SQL_ID);
$ID = $row_ID["max_id_interno"] ?? "1";

// SMM, 05/16/2023
$CardCodeID = "";
$ConsecutivoPC = "";
if (isset($_GET['cardcode']) && ($_GET['cardcode'] != "")) {
	$CardCodeID = base64_decode($_GET['cardcode']);
	$Where = "[id_socio_negocio]='$CardCodeID'";

	$SQL_Consecutivo = sqlsrv_query($conexion, "SELECT dbo.ObtenerConsecutivoPC('$CardCodeID') AS Consecutivo");
	$row_Consecutivo = sqlsrv_fetch_array($SQL_Consecutivo);
	$ConsecutivoPC = $row_Consecutivo["Consecutivo"];
}

// SMM, 05/16/2023
$SucursalSN = "";
if (isset($_GET['idsucursal']) && ($_GET['idsucursal'] != "")) {
	$SucursalSN = base64_decode($_GET['idsucursal']);
	$Where = "[id_socio_negocio]='$CardCodeID' AND [id_consecutivo_direccion] = '$SucursalSN'";
}

// SMM, 05/16/2022
// echo $Where;
$SQL_ZonaSN = Seleccionar("uvw_tbl_SociosNegocios_Zonas", "*", "$Where AND [estado] = 'Y'", "id_zona_sn");
$SQL = Seleccionar("uvw_tbl_PuntoControl", "*", $Where);

// SMM, 05/15/2023
$SQL_TipoPC = Seleccionar("uvw_tbl_PuntoControl_Tipos", "*");
$SQL_NivelInfestacionPC = Seleccionar("tbl_PuntoControl_Nivel_Infestacion", "*", "[estado] = 'Y'");

// SMM, 25/02/2023
$msg_error = "";
$parametros = array();

$coduser = $_SESSION['CodUser'];
$datetime = FormatoFecha(date('Y-m-d'), date('H:i:s'));

$type = $_POST['type'] ?? 0;
$id_interno = $_POST['id_interno'] ?? "NULL";
$punto_control = $_POST['punto_control'] ?? "";
$descripcion_punto_control = $_POST['descripcion_punto_control'] ?? "";
$id_tipo_punto_control = $_POST['id_tipo_punto_control'] ?? "";
$prefijo = $_POST['prefijo'] ?? "";
$id_socio_negocio = $_POST['id_socio_negocio'] ?? "";
$id_zona_sn = $_POST['id_zona_sn'] ?? "";
$id_nivel_infestacion = $_POST['id_nivel_infestacion'] ?? "";
$instala_tecnico = $_POST['instala_tecnico'] ?? "";
$estado = $_POST['estado'] ?? "";
$fecha_instalacion = isset($_POST['fecha_instalacion']) ? ("'" . $_POST['fecha_instalacion'] . "'") : "NULL";
$umbral_seguridad = $_POST['umbral_seguridad'] ?? "NULL";
$umbral_critico = $_POST['umbral_critico'] ?? "NULL";
$id_usuario_creacion = "'$coduser'";
$fecha_creacion = "'$datetime'";
$hora_creacion = "'$datetime'";
$id_usuario_actualizacion = "'$coduser'";
$fecha_actualizacion = "'$datetime'";
$hora_actualizacion = "'$datetime'";

if ($type == 1) {
	$msg_error = "No se pudo crear el registro.";

	$parametros = array(
		$type,
		"NULL",
		"'$punto_control'",
		"'$descripcion_punto_control'",
		"'$id_tipo_punto_control'",
		"'$prefijo'",
		"'$id_socio_negocio'",
		"'$id_zona_sn'",
		"'$id_nivel_infestacion'",
		"'$instala_tecnico'",
		"'$estado'",
		$fecha_instalacion,
		$umbral_seguridad,
		$umbral_critico,
		$id_usuario_actualizacion,
		$fecha_actualizacion,
		$hora_actualizacion,
		$id_usuario_creacion,
		$fecha_creacion,
		$hora_creacion,
	);

} elseif ($type == 2) {
	$msg_error = "No se pudo actualizar el registro.";

	$parametros = array(
		$type,
		"$id_interno",
		"'$punto_control'",
		"'$descripcion_punto_control'",
		"'$id_tipo_punto_control'",
		"'$prefijo'",
		"'$id_socio_negocio'",
		"'$id_zona_sn'",
		"'$id_nivel_infestacion'",
		"'$instala_tecnico'",
		"'$estado'",
		$fecha_instalacion,
		$umbral_seguridad,
		$umbral_critico,
		$id_usuario_actualizacion,
		$fecha_actualizacion,
		$hora_actualizacion,
	);

} elseif ($type == 3) {
	$msg_error = "No se pudo eliminar el registro.";

	$parametros = array(
		$type,
		"'$id_interno'",
	);
}

if ($type != 0) {
	$SQL_Operacion = EjecutarSP('sp_tbl_PuntoControl', $parametros);

	if (!$SQL_Operacion) {
		echo $msg_error;
	} else {
		$row = sqlsrv_fetch_array($SQL_Operacion);

		if (isset($row['Error']) && ($row['Error'] != "")) {
			echo "$msg_error ";
			echo "(" . $row['Error'] . ")";
		} else {
			echo "OK";
		}
	}

	// Mostrar mensajes AJAX.
	exit();
}
?>

<!doctype html>
<html>

<head>

	<?php include_once "includes/cabecera.php"; ?>

	<style>
		body {
			background-color: #ffffff;
			overflow-x: auto;
		}

		#from .ibox-content {
			padding: 0px !important;
		}

		#from .form-control {
			width: auto;
			height: 28px;
		}

		#from .table>tbody>tr>td {
			padding: 1px !important;
			vertical-align: middle;
		}

		#from .select2-container {
			width: 100% !important;
		}

		#from .bg-success[readonly] {
			background-color: #1c84c6 !important;
			color: #ffffff !important;
		}

		.select2-container,
		.swal2-container {
			z-index: 10000;
		}

		.select2-search--inline {
			display: contents;
		}

		.select2-search__field:placeholder-shown {
			width: 100% !important;
		}

		.ui-datepicker {
			z-index: 9999999 !important;
		}
	</style>

	<script>
		var json = [];
		var cant = 0;

		// SMM, 25/02/2023
		function Seleccionar(ID) {
			let check = document.getElementById('chkSel' + ID).checked;

			let index = json.findIndex(function (element) {
				return element == ID;
			});

			if (index >= 0) {
				json.splice(index, 1);
				cant--;
			} else {
				check ? json.push(ID) : null;
				cant += check ? 1 : 0;
			}

			$("#btnBorrarLineas").prop('disabled', cant <= 0);
		}

		// SMM, 25/02/2023
		function SeleccionarTodos() {
			let checkAll = document.getElementById('chkAll');
			let isChecked = checkAll.checked;
			let chkSel = $(".chkSel:not(:disabled)");

			if (!isChecked) {
				json = [];
				cant = 0;

				$("#btnBorrarLineas").prop('disabled', true);
			}

			chkSel.prop("checked", isChecked);

			if (isChecked) {
				chkSel.trigger('change');
			}
		}

		// SMM, 25/02/2023
		function BorrarLineas() {
			Swal.fire({
				title: '¿Está seguro que desea eliminar los registros seleccionados?',
				text: "Esta acción no se puede deshacer.",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Sí, eliminar'
			}).then((result) => {
				if (result.isConfirmed) {
					json.forEach(function (id) {
						OperacionModal(id);
					});
				}
			});
		}
	</script>

</head>

<body>

	<div class="modal inmodal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" style="width: 70% !important;">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Adicionar punto de control</h4>
					<h6 class="modal-title">
						<?php echo $namesucursal; ?>
					</h6>
				</div> <!-- modal-header -->

				<form id="modalForm">
					<div class="modal-body">
						<div class="form-group">
							<div class="ibox-content">
								<input type="hidden" id="type">
								<input type="hidden" id="prefijo">

								<div class="form-group">
									<div class="col-md-4">
										<label class="control-label">ID Interno <span
												class="text-danger">*</span></label>
										<input required readonly type="text" class="form-control" autocomplete="off"
											id="id_interno" value="<?php echo $ID; ?>">
									</div>

									<div class="col-md-4">
										<label class="control-label">ID Punto Control <span
												class="text-danger">*</span></label>
										<input required type="text" class="form-control" autocomplete="off"
											id="id_punto_control" readonly>
									</div>

									<div class="col-md-4">
										<label class="control-label">Estado</label>
										<select class="form-control" id="estado">
											<option value="Y">ACTIVO</option>
											<option value="N">INACTIVO</option>
										</select>
									</div>
								</div> <!-- form-group -->

								<br><br><br><br>
								<div class="form-group">
									<div class="col-md-6">
										<label class="control-label">Tipo Punto Control <span
												class="text-danger">*</span></label>
										<select id="id_tipo_punto_control" class="form-control" required>
											<option value="">Seleccione...</option>

											<?php while ($row_TipoPC = sqlsrv_fetch_array($SQL_TipoPC)) { ?>
												<option value="<?php echo $row_TipoPC['id_tipo_punto_control']; ?>"
													data-prefijo="<?php echo $row_TipoPC['codigo_prefijo']; ?>">
													<?php echo $row_TipoPC['tipo_punto_control']; ?>
												</option>
											<?php } ?>
										</select>
									</div>

									<div class="col-md-6">
										<label class="control-label">Nombre Punto Control <span
												class="text-danger">*</span></label>
										<input required type="text" class="form-control" autocomplete="off"
											id="punto_control">
									</div>
								</div> <!-- form-group -->

								<br><br><br><br>
								<div class="form-group">
									<div class="col-md-12">
										<label class="control-label">Descripción Punto Control (200 caracteres)</label>
										<textarea type="text" class="form-control" name="descripcion_punto_control"
											id="descripcion_punto_control" rows="3" maxlength="200"></textarea>
									</div>
								</div> <!-- form-group -->

								<br><br><br><br><br><br>
								<div class="form-group">
									<div class="col-md-6">
										<label class="control-label">Instalado por técnico <span
												class="text-danger">*</span></label>
										<select class="form-control" id="instala_tecnico" required>
											<option value="Y">Si, el técnico realiza la instalación</option>
											<option value="N">No, el técnico NO realiza la instalación</option>
										</select>
									</div>

									<div class="col-md-6">
										<label class="control-label">Fecha Instalación</label>
										<div class="input-group date">
											<span class="input-group-addon"><i class="fa fa-calendar"></i></span><input
												type="text" class="form-control" id="fecha_instalacion"
												value="<?php echo date('Y-m-d'); ?>">
										</div>
									</div>
								</div> <!-- form-group -->

								<br><br><br><br>
								<div class="form-group">
									<div class="col-md-6">
										<label class="control-label">Zona Socio Negocio <span
												class="text-danger">*</span></label>
										<select id="id_zona_sn" class="form-control" required>
											<option value="" <?php if ($SucursalSN == "") {
												echo "disabled selected";
											} ?>>
												Seleccione...</option>

											<?php while ($row_ZonaSN = sqlsrv_fetch_array($SQL_ZonaSN)) { ?>
												<option value="<?php echo $row_ZonaSN['id_zona_sn']; ?>"><?php echo $row_ZonaSN['zona_sn']; ?></option>
											<?php } ?>
										</select>
									</div>

									<div class="col-md-6">
										<label class="control-label">Nivel infestación <span
												class="text-danger">*</span></label>
										<select id="id_nivel_infestacion" class="form-control" required>
											<?php while ($row_NivelInfestacionPC = sqlsrv_fetch_array($SQL_NivelInfestacionPC)) { ?>
												<option
													value="<?php echo $row_NivelInfestacionPC['id_nivel_infestacion']; ?>">
													<?php echo $row_NivelInfestacionPC['nivel_infestacion']; ?></option>
											<?php } ?>
										</select>
									</div>
								</div> <!-- form-group -->

								<br><br><br><br>
								<div class="form-group">
									<div class="col-md-6">
										<label class="control-label">Umbral de Seguridad</label>
										<input type="number" class="form-control" autocomplete="off"
											id="umbral_seguridad">
									</div>

									<div class="col-md-6">
										<label class="control-label">Umbral Crítico</label>
										<input type="number" class="form-control" autocomplete="off"
											id="umbral_critico">
									</div>
								</div> <!-- form-group -->

								<br><br>
							</div> <!-- ibox-content -->
						</div> <!-- form-group -->
					</div> <!-- modal-body -->

					<div class="modal-footer">
						<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i>
							Aceptar</button>
						<button type="button" class="btn btn-warning m-t-md" data-dismiss="modal"><i
								class="fa fa-times"></i> Cerrar</button>
					</div> <!-- modal-footer -->
				</form>
			</div> <!-- modal-content -->
		</div> <!-- modal-dialog -->
	</div> <!-- modal -->

	<div class="row">
		<div class="form-group">
			<div class="col-lg-3">
				<button type="button" id="btnNuevo" class="btn btn-success" onclick="MostrarModal();"><i
						class="fa fa-plus-circle"></i> Adicionar punto de control</button>
			</div>
		</div> <!-- form-group -->

		<br><br>
	</div> <!-- row -->

	<div class="row m-t-md">
		<div class="col-lg-12">
			<div class="tabs-container">
				<ul class="nav nav-tabs">
					<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Contenido</a></li>
				</ul> <!-- nav-tabs -->

				<div class="tab-content">
					<div id="tab-1" class="tab-pane active">

						<div class="ibox-content">
							<?php include "includes/spinner.php"; ?>

							<table width="100%" class="table table-bordered dataTables-example">
								<thead>
									<tr>
										<th class="text-center form-inline w-80">
											<div class="checkbox checkbox-success"><input type="checkbox" id="chkAll"
													value="" onchange="SeleccionarTodos();"
													title="Seleccionar todos"><label></label></div>
											<button type="button" id="btnBorrarLineas" title="Borrar lineas"
												class="btn btn-danger btn-xs" disabled onclick="BorrarLineas();"><i
													class="fa fa-trash"></i></button>
										</th>

										<th>Acciones</th>
										<th>ID</th>
										<th>Punto Control</th>
										<th>Tipo Punto Control</th>
										<th>Descripción</th>
										<th>Zona de Socio de Negocio</th>
										<th>Nivel de Infestación</th>
										<th>Instalación</th>
										<th>Umbrales</th>
										<th>Estado</th>
										<th>Actualización</th>
									</tr>
								</thead>

								<tbody>
									<?php while ($row = sqlsrv_fetch_array($SQL)) { ?>
										<tr>
											<td class="text-center">
												<div class="checkbox checkbox-success no-margins">
													<input type="checkbox" class="chkSel"
														id="chkSel<?php echo $row['id_interno']; ?>" value=""
														onchange="Seleccionar('<?php echo $row['id_interno']; ?>');"
														aria-label="Single checkbox One"><label></label>
												</div>
											</td>

											<td class="text-center form-inline w-80">
												<button type="button" title="Editar información"
													class="btn btn-warning btn-xs"
													onclick="MostrarModal('<?php echo $row['id_interno']; ?>');"><i
														class="fa fa-pencil"></i></button>
											</td>

											<td>
												<?php echo $row['id_interno']; ?>
											</td>

											<td>
												<b>ID:</b>
												<?php echo $row['id_punto_control']; ?>

												<br><br><b>Nombre:</b>
												<?php echo $row['punto_control']; ?>
											</td>

											<td>
												<b>ID:</b>
												<?php echo $row['id_tipo_punto_control']; ?>

												<br><br><b>Nombre:</b>
												<?php echo $row['tipo_punto_control']; ?>
											</td>

											<td>
												<?php echo $row['descripcion_punto_control']; ?>
											</td>

											<td class="w-80">
												<b>
													<?php echo $row['id_zona_sn'] . " - " . $row['zona_sn']; ?>
												</b>

												<b>
													<br>
													<?php echo $row['socio_negocio']; ?>
												</b>

												<br><br><b>ID Dirección Destino:</b>
												<?php echo $row['id_consecutivo_direccion'] . " - " . $row['id_direccion_destino']; ?>

												<br><br><b>Dirección Destino:</b>
												<?php echo $row['direccion_destino']; ?>
											</td>

											<td>
												<?php echo $row['nivel_infestacion']; ?>
											</td>

											<td>
												<b>
													<?php echo ($row['instala_tecnico'] == "Y") ? "El técnico realiza la instalación" : "El técnico NO realiza la instalación"; ?>
												</b>

												<br><br><b>Fecha Instalación:</b>
												<?php echo isset($row['fecha_instalacion']) ? date_format($row['fecha_instalacion'], 'Y-m-d H:i:s') : ""; ?>
											</td>

											<td>
												<b>Umbral de Seguridad:</b>
												<?php echo $row['umbral_seguridad']; ?>

												<br><br><b>Umbral Crítico:</b>
												<?php echo $row['umbral_critico']; ?>
											</td>

											<td>
												<span
													class="badge <?php echo ($row['estado'] == "Y") ? "badge-primary" : "badge-danger"; ?>">
													<?php echo ($row['estado'] == "Y") ? "Activo" : "Inactivo"; ?>
												</span>
											</td>

											<td>
												<b>Usuario Actualización:</b>
												<?php echo $row['usuario_actualizacion']; ?>

												<br><br><b>Fecha Actualización:</b>
												<?php echo isset($row['fecha_actualizacion']) ? date_format($row['fecha_actualizacion'], 'Y-m-d H:i:s') : ""; ?>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div> <!-- ibox-content -->
					</div> <!-- tab-1 -->
				</div> <!-- tab-content -->
			</div> <!-- tabs-container -->
		</div> <!-- col-lg-12 -->
	</div> <!-- row m-t-md -->

	<script>
		// SMM, 24/02/2023
		function OperacionModal(ID = "") {
			if (ID != "") {
				$("#id_interno").val(ID);
			}

			$.ajax({
				type: "POST",
				url: "detalle_puntos_control_sn.php",
				data: {
					type: (ID == "") ? $("#type").val() : 3,
					id_interno: $("#id_interno").val(),
					punto_control: $("#punto_control").val(),
					descripcion_punto_control: $("#descripcion_punto_control").val(),
					id_tipo_punto_control: $("#id_tipo_punto_control").val(),
					prefijo: $("#prefijo").val(),
					id_socio_negocio: "<?php echo $CardCodeID; ?>",
					id_zona_sn: $("#id_zona_sn").val(),
					id_nivel_infestacion: $("#id_nivel_infestacion").val(),
					instala_tecnico: $("#instala_tecnico").val(),
					estado: $("#estado").val(),
					fecha_instalacion: $("#fecha_instalacion").val(),
					umbral_seguridad: $("#umbral_seguridad").val(),
					umbral_critico: $("#umbral_critico").val(),
				},
				success: function (response) {
					Swal.fire({
						icon: (response == "OK") ? "success" : "warning'",
						title: (response == "OK") ? "Operación exitosa" : "Ocurrió un error",
						text: (response == "OK") ? "La consulta se ha ejecutado correctamente." : response
					}).then((result) => {
						if (result.isConfirmed) {
							location.reload();
						}
					});
				},
				error: function (error) {
					console.error("445->", error.responseText);
				}
			});
		}

		// SMM, 17/05/2023
		function MostrarModal(ID = "") {
			if (ID != "") {
				// SMM, 17/05/2023
				$("#id_interno").val(ID);

				$.ajax({
					url: "ajx_buscar_datos_json.php",
					data: {
						type: 50,
						id: ID
					},
					dataType: 'json',
					success: function (linea) {
						console.log(linea);

						// SMM, 17/05/2023
						$("#id_punto_control").val(linea.id_punto_control);
						$("#punto_control").val(linea.punto_control);
						$("#id_tipo_punto_control").val(linea.id_tipo_punto_control);
						$("#descripcion_punto_control").val(linea.descripcion_punto_control);
						$("#id_zona_sn").val(linea.id_zona_sn);
						$("#id_nivel_infestacion").val(linea.id_nivel_infestacion);
						$("#instala_tecnico").val(linea.instala_tecnico);
						$("#fecha_instalacion").val(linea.fecha_instalacion);
						$("#estado").val(linea.estado);
						$("#umbral_seguridad").val(linea.umbral_seguridad);
						$("#umbral_critico").val(linea.umbral_critico);

						$("#type").val(2);
						$('#myModal').modal("show");
					},
					error: function (error) {
						console.error("660->", error.responseText);
					}
				});
			} else {
				$("#type").val(1);
				$('#myModal').modal("show");
			}
		}

		$("#modalForm").on("submit", function (event) {
			event.preventDefault(); // Evitar redirección del formulario

			Swal.fire({
				title: "¿Está seguro que desea continuar con la operación?",
				icon: "question",
				showCancelButton: true,
				confirmButtonText: "Si, confirmo",
				cancelButtonText: "No"
			}).then((result) => {
				if (result.isConfirmed) {
					OperacionModal();

					// Ocultar modal.
					$('#myModal').modal("hide");
				}
			}); // Swal.fire
		});

		$(document).ready(function () {
			$('[data-toggle="tooltip"]').tooltip();

			$(".select2").select2();

			$(".alkin").on('click', function () {
				$('.ibox-content').toggleClass('sk-loading');
			});

			$('.dataTables-example').DataTable({
				searching: false,
				info: false,
				paging: false,
				language: {
					"decimal": "",
					"thousands": ",",
					"emptyTable": "No se encontraron resultados."
				}
				, order: [[2, "desc"]] // SMM, 25/01/2023
			});

			// SMM, 05/16/2023
			$("#id_tipo_punto_control").on("change", function () {
				let prefijo = $(this).children("option:selected").data("prefijo");

				$("#prefijo").val(prefijo);
				$("#id_punto_control").val(`${prefijo}-<?php echo $ConsecutivoPC; ?>`);
				$("#punto_control").val($('#id_tipo_punto_control option:selected').text().trim());
			});

			$("#FechaInstalacion").datepicker({
				todayBtn: "linked",
				keyboardNavigation: false,
				forceParse: false,
				calendarWeeks: true,
				autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true
			});
		});
	</script>

</body>

</html>
<?php sqlsrv_close($conexion); ?>