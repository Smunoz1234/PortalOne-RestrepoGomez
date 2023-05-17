<?php
require_once "includes/conexion.php";
// PermitirAcceso(318);
$CardCodeID = "";
$SucursalSN = "";

// SMM, 24/02/2022
$SQL_SucursalSN = "";
if (isset($_GET['cardcode']) && ($_GET['cardcode'] != "")) {
    $CardCodeID = base64_decode($_GET['cardcode']);
    $SucursalSN = (isset($_GET['idsucursal']) && ($_GET['idsucursal'] != "")) ? base64_decode($_GET['idsucursal']) : "";

    // Sucursales
    if (PermitirFuncion(205)) {
        $Where = "CodigoCliente='$CardCodeID' AND TipoDireccion='S'";
        $SQL_SucursalSN = Seleccionar("uvw_Sap_tbl_Clientes_Sucursales", "NombreSucursal, NumeroLinea", $Where);
    } else {
        $Where = "CodigoCliente='$CardCodeID' AND TipoDireccion='S' AND ID_Usuario = " . $_SESSION['CodUser'];
        $SQL_SucursalSN = Seleccionar("uvw_tbl_SucursalesClienteUsuario", "NombreSucursal, NumeroLinea", $Where);
    }
}

// SMM, 24/02/2023
$WhereSucursalSN = "";
if (isset($_GET['idsucursal']) && ($_GET['idsucursal'] != "")) {
    $WhereSucursalSN = "AND [id_consecutivo_direccion]='" . base64_decode($_GET['idsucursal']) . "'";
}

// SMM, 24/02/2023
$SQL = Seleccionar("uvw_tbl_SociosNegocios_Zonas", "*", "[id_socio_negocio]='$CardCodeID' $WhereSucursalSN", "[id_zona_sn]");

// SMM, 25/02/2023
$msg_error = "";
$parametros = array();

$coduser = $_SESSION['CodUser'];
$datetime = FormatoFecha(date('Y-m-d'), date('H:i:s'));

$type = $_POST['type'] ?? 0;
$id_zona_sn = $_POST['id_zona_sn'] ?? "";
$zona_sn = $_POST['zona_sn'] ?? "";
$id_socio_negocio = $_POST['id_socio_negocio'] ?? "";
$socio_negocio = $_POST['socio_negocio'] ?? "";
$id_consecutivo_direccion = $_POST['id_consecutivo_direccion'] ?? "NULL";
$id_direccion_destino = $_POST['id_direccion_destino'] ?? "";
$direccion_destino = $_POST['direccion_destino'] ?? "";
$estado = $_POST['estado'] ?? "";
$observaciones = $_POST['observaciones'] ?? "";

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
        "'$id_zona_sn'",
        "'$zona_sn'",
        "'$id_socio_negocio'",
        "'$socio_negocio'",
        $id_consecutivo_direccion,
        "'$id_direccion_destino'",
        "'$direccion_destino'",
        "'$estado'",
        "'$observaciones'",
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
        "'$id_zona_sn'",
        "'$zona_sn'",
        "'$id_socio_negocio'",
        "'$socio_negocio'",
        $id_consecutivo_direccion,
        "'$id_direccion_destino'",
        "'$direccion_destino'",
        "'$estado'",
        "'$observaciones'",
        $id_usuario_actualizacion,
        $fecha_actualizacion,
        $hora_actualizacion,
    );

} elseif ($type == 3) {
    $msg_error = "No se pudo eliminar el registro.";

    $parametros = array(
        $type, // 3 - Eliminar
        "'$id_zona_sn'",
    );
}

if ($type != 0) {
    $SQL_Operacion = EjecutarSP('sp_tbl_SociosNegocios_Zonas', $parametros);

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

// SMM, 02/03/2023
$SQL_Validacion = Seleccionar("uvw_tbl_SociosNegocios_Zonas", "COUNT(*) AS num_errors", "[id_socio_negocio]='$CardCodeID' AND [error] = 'Y'");
$row_Validacion = sqlsrv_fetch_array($SQL_Validacion);

$error_validacion = false;
if ($row_Validacion["num_errors"] > 0) {
    $error_validacion = true;
}
?>

<!doctype html>
<html>

<head>

<?php include_once "includes/cabecera.php";?>

<style>
	body{
		background-color: #ffffff;
		overflow-x: auto;
	}

	#from .ibox-content{
		padding: 0px !important;
	}
	#from .form-control{
		width: auto;
		height: 28px;
	}
	#from .table > tbody > tr > td{
		padding: 1px !important;
		vertical-align: middle;
	}
	#from .select2-container{ width: 100% !important; }
	#from .bg-success[readonly]{
		background-color: #1c84c6 !important;
		color: #ffffff !important;
	}

	.select2-container, .swal2-container {
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

		let index = json.findIndex(function(element) {
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

		if(!isChecked) {
			json = [];
			cant = 0;

			$("#btnBorrarLineas").prop('disabled', true);
		}

		chkSel.prop("checked", isChecked);

		if(isChecked) {
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
				json.forEach(function(id) {
					OperacionModal(id);
				});
			}
		});
	}
</script>

</head>
<body>

<div class="modal inmodal fade" id="modalZonasSN" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Adicionar Zonas a Socios de negocios</h4>
			</div> <!-- modal-header -->

			<form id="modalForm">
				<div class="modal-body">
					<div class="form-group">
						<div class="ibox-content">
							<input type="hidden" id="Type">

							<div class="form-group">
								<div class="col-md-6">
									<label class="control-label">Socio Negocio <span class="text-danger">*</span></label>
									<input required type="text" class="form-control" autocomplete="off" id="IDSocioNegocio" value="<?php echo $CardCodeID; ?>" readonly>
								</div>

								<div class="col-md-6">
									<label class="control-label">Estado</label>
									<select class="form-control" id="Estado">
										<option value="Y">ACTIVO</option>
										<option value="N">INACTIVO</option>
									</select>
								</div>
							</div> <!-- form-group -->

							<br><br><br><br>
							<div class="form-group">
								<div class="col-md-6">
									<label class="control-label">ID Zona <span class="text-danger">*</span></label>
									<input required type="text" class="form-control" autocomplete="off" id="IDZonaSN">
								</div>

								<div class="col-md-6">
									<label class="control-label">Nombre Zona <span class="text-danger">*</span></label>
									<input required type="text" class="form-control" autocomplete="off" id="ZonaSN">
								</div>
							</div> <!-- form-group -->

							<br><br><br><br>
							<div class="form-group">
								<div class="col-md-12">
									<label class="control-label">Sucursal Socio Negocio <span class="text-danger">*</span></label>
									<select id="SucursalSN" class="form-control" <?php if ($SucursalSN != "") {echo "readonly";}?> required>
										<option value="" <?php if ($SucursalSN == "") {echo "disabled selected";}?>>Seleccione...</option>

										<?php while ($row_SucursalSN = sqlsrv_fetch_array($SQL_SucursalSN)) {?>
											<option value="<?php echo $row_SucursalSN['NumeroLinea']; ?>" <?php if ($SucursalSN == $row_SucursalSN['NumeroLinea']) {echo "selected";}?>><?php echo $row_SucursalSN['NombreSucursal']; ?></option>
										<?php }?>
									</select>
								</div>
							</div> <!-- form-group -->

							<br><br><br><br>
							<div class="form-group">
								<div class="col-md-12">
									<label class="control-label">Observaciones (250 caracteres)</label>
									<textarea type="text" class="form-control" name="Observaciones" id="Observaciones" rows="3" maxlength="250"></textarea>
								</div>
							</div> <!-- form-group -->

							<br><br>
						</div> <!-- ibox-content -->
					</div> <!-- form-group -->
				</div> <!-- modal-body -->

				<div class="modal-footer">
					<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i> Aceptar</button>
					<button type="button" class="btn btn-warning m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
				</div> <!-- modal-footer -->
			</form>
		</div> <!-- modal-content -->
	</div> <!-- modal-dialog -->
</div> <!-- modal -->

<div class="row">
	<div class="form-group">
		<div class="col-lg-2">
			<button type="button" id="btnNuevo" class="btn btn-success" onclick="MostrarModal();"><i class="fa fa-plus-circle"></i> Adicionar zonas</button>
		</div>
		<div class="col-lg-4" style="<?php if (!$error_validacion) {echo "display: none;";}?>">
			<button type="button" id="btnCorregir" class="btn btn-warning" onclick="CorregirDatos();"><i class="fa fa-gavel"></i> Corregir datos</button>

			<button style="margin-left: 5px;" type="button" class="btn btn-sm btn-circle" data-toggle="tooltip" data-placement="bottom" data-html="true"
			title="Se encontraron inconsistencias en algunos de los nombres de los socios de negocios y sucursales asociadas, pulse el botón para corregir."><i class="fa fa-info"></i></button>
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
						<?php include "includes/spinner.php";?>

						<table width="100%" class="table table-bordered dataTables-example">
							<thead>
								<tr>
									<th class="text-center form-inline w-80">
										<div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onchange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div>
										<button type="button" id="btnBorrarLineas" title="Borrar lineas" class="btn btn-danger btn-xs" disabled onclick="BorrarLineas();"><i class="fa fa-trash"></i></button>
									</th>

									<th>Acciones</th>
									<th>ID Zona</th>
									<th>Zona</th>
									<th>Socio Negocio</th>
									<th>ID Dirección Destino</th>
									<th>Dirección Destino</th>
									<th>Estado</th>
									<th>Observaciones</th>
									<th>Fecha Actualización</th>
									<th>Usuario Actualización</th>
								</tr>
							</thead>

							<tbody>
								<?php while ($row = sqlsrv_fetch_array($SQL)) {?>
								<tr>
									<td class="text-center">
										<div class="checkbox checkbox-success no-margins">
											<input type="checkbox" class="chkSel" id="chkSel<?php echo $row['id_zona_sn']; ?>" value="" onchange="Seleccionar('<?php echo $row['id_zona_sn']; ?>');" aria-label="Single checkbox One"><label></label>
										</div>
									</td>

									<td class="text-center form-inline w-80">
										<button type="button" title="Editar información" class="btn btn-warning btn-xs" onclick="MostrarModal('<?php echo $row['id_zona_sn']; ?>');"><i class="fa fa-pencil"></i></button>
									</td>

									<td><?php echo $row['id_zona_sn']; ?></td>
									<td><?php echo $row['zona_sn']; ?></td>
									<td><?php echo $row['socio_negocio']; ?></td>
									<td><?php echo $row['id_direccion_destino']; ?></td>
									<td><?php echo $row['direccion_destino']; ?></td>

									<td>
										<span class="badge <?php echo ($row['estado'] == "Y") ? "badge-primary" : "badge-danger"; ?>">
											<?php echo ($row['estado'] == "Y") ? "Activo" : "Inactivo"; ?>
										</span>
									</td>

									<td><?php echo $row['observaciones']; ?></td>

									<td><?php echo isset($row['fecha_actualizacion']) ? date_format($row['fecha_actualizacion'], 'Y-m-d H:i:s') : ""; ?></td>
									<td><?php echo $row['usuario_actualizacion']; ?></td>
								</tr>
								<?php }?>
							</tbody>
						</table>
					</div> <!-- ibox-content -->
				</div> <!-- tab-1 -->
			</div> <!-- tab-content -->
		</div> <!-- tabs-container -->
	</div> <!-- col-lg-12 -->
</div> <!-- row m-t-md -->

<script>
	// SMM, 02/03/2023
	function CorregirDatos() {
		Swal.fire({
			title: 'Corregir Datos de la Tabla',
			text: "¿Está seguro que desea continuar?",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Si, continuar',
			cancelButtonText: 'No'
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					type: 'GET',
					url: 'includes/procedimientos.php?type=67&id_socio_negocio=<?php echo $CardCodeID; ?>',
					success: function(response) {
						location.reload();
					},
					error: function(error) {
						console.error('430->', error.responseText);
					}
				});
			}
		});
	}

	// SMM, 24/02/2023
	function OperacionModal(ID = "") {
		$.ajax({
			type: "POST",
			url: "detalle_zonas_sn.php",
			data: {
				type: (ID == "") ? $("#Type").val() : 3,
				id_zona_sn: (ID == "") ? $("#IDZonaSN").val() : ID,
				zona_sn: $("#ZonaSN").val(),
				id_socio_negocio: $("#IDSocioNegocio").val(),
				socio_negocio: "",
				id_consecutivo_direccion: $("#SucursalSN").val(),
				id_direccion_destino: "",
				direccion_destino: "",
				estado: $("#Estado").val(),
				observaciones: $("#Observaciones").val(),
			},
			success: function(response) {
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
			error: function(error) {
				console.error("445->", error.responseText);
			}
		});
	}

	// SMM, 25/02/2023
	function MostrarModal(ID = "") {
		if(ID != "") {
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{
					type: 49,
					id: ID
				},
				dataType:'json',
				success: function(linea) {
					console.log(linea);

					$("#IDZonaSN").val(linea.id_zona_sn);
					$("#ZonaSN").val(linea.zona_sn);
					$("#IDSocioNegocio").val(linea.id_socio_negocio);
					$("#SucursalSN").val(linea.id_consecutivo_direccion);
					$("#Estado").val(linea.estado);
					$("#Observaciones").val(linea.observaciones);

					$("#Type").val(2);
					$('#modalZonasSN').modal("show");
				},
				error: function(error) {
					console.error("470->", error.responseText);
				}
			});
		} else {
			$("#Type").val(1);
			$('#modalZonasSN').modal("show");
		}
	}

	$("#modalForm").on("submit", function(event) {
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

	$(document).ready(function() {
		$('[data-toggle="tooltip"]').tooltip();

		$(".select2").select2();

		$(".alkin").on('click', function(){
			$('.ibox-content').toggleClass('sk-loading');
		});

		$('.dataTables-example').DataTable({
			searching: false,
			info: false,
			paging: false,
			language: {
				"decimal":        "",
				"thousands":      ",",
				"emptyTable":     "No se encontraron resultados."
			}
		});
	});
</script>

</body>

</html>
<?php sqlsrv_close($conexion);?>
