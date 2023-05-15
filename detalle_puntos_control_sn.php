<?php
require_once "includes/conexion.php";
// PermitirAcceso(318);
$CardCodeID = "";
$SucursalSN = "";

// SMM, 24/02/2022
$SQL_ZonaSN = "";
if (isset($_GET['cardcode']) && ($_GET['cardcode'] != "")) {
	$CardCodeID = base64_decode($_GET['cardcode']);
	$SucursalSN = (isset($_GET['idsucursal']) && ($_GET['idsucursal'] != "")) ? base64_decode($_GET['idsucursal']) : "";

	// Sucursales
	$Where_ZonaSN = "[id_socio_negocio]='$CardCodeID'";
	if ($SucursalSN != "") {
		$Where_ZonaSN = "[id_socio_negocio]='$CardCodeID' AND [id_consecutivo_direccion] = '$CardCodeID'";
	}
	$SQL_ZonaSN = Seleccionar("uvw_tbl_SociosNegocios_Zonas", "*", $Where_ZonaSN);
}

// SMM, 24/02/2023
$WhereSucursalSN = "";
if (isset($_GET['idsucursal']) && ($_GET['idsucursal'] != "")) {
	$WhereSucursalSN = "AND [id_consecutivo_direccion]='" . base64_decode($_GET['idsucursal']) . "'";
}

// SMM, 05/15/2023
$SQL_TipoPC = Seleccionar("uvw_tbl_PuntoControl_Tipos", "*");
$SQL_NivelInfestacionPC = Seleccionar("tbl_PuntoControl_Nivel_Infestacion", "*", "[estado] = 'Y'");
$SQL = Seleccionar("uvw_tbl_PuntoControl", "*", "[id_socio_negocio]='$CardCodeID' $WhereSucursalSN");

// SMM, 25/02/2023
$msg_error = "";
$parametros = array();

$coduser = $_SESSION['CodUser'];
$datetime = FormatoFecha(date('Y-m-d'), date('H:i:s'));

$type = $_POST['type'] ?? 0;
$id_interno = $_POST['id_interno'] ?? "NULL";
$id_punto_control = $_POST['id_punto_control'] ?? "";
$punto_control = $_POST['punto_control'] ?? "";
$descripcion_punto_control = $_POST['descripcion_punto_control'] ?? "";
$id_tipo_punto_control = $_POST['id_tipo_punto_control'] ?? "";
$id_socio_negocio = $_POST['id_socio_negocio'] ?? "";
$id_zona_sn = $_POST['id_zona_sn'] ?? "";
$id_nivel_infestacion = $_POST['id_nivel_infestacion'] ?? "";
$instala_tecnico = $_POST['instala_tecnico'] ?? "";
$estado = $_POST['estado'] ?? "";
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
		"'$id_punto_control'",
		"'$punto_control'",
		"'$descripcion_punto_control'",
		"'$id_tipo_punto_control'",
		"'$id_socio_negocio'",
		"'$id_zona_sn'",
		"'$id_nivel_infestacion'",
		"'$instala_tecnico'",
		"'$estado'",
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
		"'$id_punto_control'",
		"'$punto_control'",
		"'$descripcion_punto_control'",
		"'$id_tipo_punto_control'",
		"'$id_socio_negocio'",
		"'$id_zona_sn'",
		"'$id_nivel_infestacion'",
		"'$instala_tecnico'",
		"'$estado'",
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

	<div class="modal inmodal fade" id="modalZonasSN" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" style="width: 70% !important;">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Adicionar punto de control</h4>
				</div> <!-- modal-header -->

				<form id="modalForm">
					<div class="modal-body">
						<div class="form-group">
							<div class="ibox-content">
								<input type="hidden" id="type">

								<div class="form-group">
									<div class="col-md-4">
										<label class="control-label">Socio Negocio <span
												class="text-danger">*</span></label>
										<input required type="text" class="form-control" autocomplete="off"
											id="id_socio_negocio" value="<?php echo $CardCodeID; ?>" readonly>
									</div>

									<div class="col-md-4">
										<label class="control-label">ID Punto Control <span
												class="text-danger">*</span></label>
										<input required type="text" class="form-control" autocomplete="off"
											id="id_punto_control">
									</div>

									<div class="col-md-4">
										<label class="control-label">Nombre Punto Control <span
												class="text-danger">*</span></label>
										<input required type="text" class="form-control" autocomplete="off"
											id="punto_control">
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
												<option value="<?php echo $row_TipoPC['id_tipo_punto_control']; ?>"><?php echo $row_TipoPC['tipo_punto_control']; ?></option>
											<?php } ?>
										</select>
									</div>

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
									<div class="col-md-4">
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

									<div class="col-md-4">
										<label class="control-label">Instalado por técnico <span
												class="text-danger">*</span></label>
										<select class="form-control" id="instala_tecnico" required>
											<option value="Y">Si, el técnico realiza la instalación</option>
											<option value="N">No, el técnico NO realiza la instalación</option>
										</select>
									</div>

									<div class="col-md-4">
										<label class="control-label">Estado</label>
										<select class="form-control" id="estado">
											<option value="Y">ACTIVO</option>
											<option value="N">INACTIVO</option>
										</select>
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
										<th>Socio de Negocio</th>
										<th>Zona de Socio de Negocio</th>
										<th>Nivel de Infestación</th>
										<th>Instalado por Técnico</th>
										<th>Estado</th>
										<th>Fecha Actualización</th>
										<th>Usuario Actualización</th>
									</tr>
								</thead>

								<tbody>
									<?php while ($row = sqlsrv_fetch_array($SQL)) { ?>
										<tr>
											<td class="text-center">
												<div class="checkbox checkbox-success no-margins">
													<input type="checkbox" class="chkSel"
														id="chkSel<?php echo $row['id_zona_sn']; ?>" value=""
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
												<?php echo $row['id_punto_control'] . " - " . $row['punto_control']; ?>
											</td>
											<td>
												<?php echo $row['id_tipo_punto_control'] . " - " . $row['tipo_punto_control']; ?>
											</td>
											<td>
												<?php echo $row['descripcion_punto_control']; ?>
											</td>
											<td>
												<?php echo $row['socio_negocio']; ?>
											</td>

											<td>
												<b>Zona:</b>
												<?php echo $row['id_zona_sn'] . " - " . $row['zona_sn']; ?>

												<br><br><b>ID Dirección Destino:</b>
												<?php echo $row['id_consecutivo_direccion'] . " - " . $row['id_direccion_destino']; ?>

												<br><br><b>Dirección Destino:</b>
												<?php echo $row['direccion_destino']; ?>
											</td>

											<td>
												<?php echo $row['nivel_infestacion']; ?>
											</td>

											<td>
												<?php echo ($row['instala_tecnico'] == "Y") ? "Si, el técnico realiza la instalación" : "No, el técnico no realiza la instalación"; ?>
											</td>

											<td>
												<span
													class="badge <?php echo ($row['estado'] == "Y") ? "badge-primary" : "badge-danger"; ?>">
													<?php echo ($row['estado'] == "Y") ? "Activo" : "Inactivo"; ?>
												</span>
											</td>

											<td>
												<?php echo isset($row['fecha_actualizacion']) ? date_format($row['fecha_actualizacion'], 'Y-m-d H:i:s') : ""; ?>
											</td>
											<td>
												<?php echo $row['usuario_actualizacion']; ?>
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
			$.ajax({
				type: "POST",
				url: "detalle_puntos_control_sn.php",
				data: {
					type: (ID == "") ? $("#type").val() : 3,
					id_punto_control: $("#id_punto_control").val(),
					punto_control: $("#punto_control").val(),
					descripcion_punto_control: $("#descripcion_punto_control").val(),
					id_tipo_punto_control: $("#id_tipo_punto_control").val(),
					id_socio_negocio: $("#id_socio_negocio").val(),
					id_zona_sn: $("#id_zona_sn").val(),
					id_nivel_infestacion: $("#id_nivel_infestacion").val(),
					instala_tecnico: $("#instala_tecnico").val(),
					estado: $("#estado").val(),
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

		// SMM, 25/02/2023
		function MostrarModal(ID = "") {
			if (ID != "") {
				$.ajax({
					url: "ajx_buscar_datos_json.php",
					data: {
						type: 45,
						id: ID
					},
					dataType: 'json',
					success: function (linea) {
						console.log(linea);

						$("#IDZonaSN").val(linea.id_zona_sn);
						$("#ZonaSN").val(linea.zona_sn);
						$("#IDSocioNegocio").val(linea.id_socio_negocio);
						$("#SucursalSN").val(linea.id_consecutivo_direccion);
						$("#Estado").val(linea.estado);
						$("#Observaciones").val(linea.observaciones);

						$("#type").val(2);
						$('#modalZonasSN').modal("show");
					},
					error: function (error) {
						console.error("470->", error.responseText);
					}
				});
			} else {
				$("#type").val(1);
				$('#modalZonasSN').modal("show");
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
					$('#modalZonasSN').modal("hide");
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
			});
		});
	</script>

</body>

</html>
<?php sqlsrv_close($conexion); ?>