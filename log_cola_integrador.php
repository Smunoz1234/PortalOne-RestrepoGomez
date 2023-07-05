<?php require_once "includes/conexion.php";
// Se cambio el GET por POST, 24/11/2021

PermitirAcceso(221);
$sw = 0;

//Fechas
$FechaFinal = "";
$FechaInicial = "";
if (isset($_POST['FechaInicial']) && $_POST['FechaInicial'] != "") {
	$FechaInicial = $_POST['FechaInicial'];
	$sw = 1;
} else {
	$FechaInicial = date('Y-m-d');
}
if (isset($_POST['FechaFinal']) && $_POST['FechaFinal'] != "") {
	$FechaFinal = $_POST['FechaFinal'];
} else {
	$FechaFinal = date('Y-m-d');
}

// Filtros, 26/11/2021
$Estado = isset($_POST['Estado']) ? $_POST['Estado'] : "";

// Top
$top = "";
if (isset($_POST['Cantidad_Visualizar']) && $_POST['Cantidad_Visualizar'] != "") {
	$top = "TOP (" . $_POST['Cantidad_Visualizar'] . ")";
} else {
	$top = "TOP (5)";
}

// Agregado por Stiven
$initDate = str_replace('-', '', $FechaInicial);
$finalDate = str_replace('-', '', $FechaFinal);

if (isset($_POST['ids']) && $_POST['ids'] != "") {
	foreach ($_POST['ids'] as &$id) {
		EjecutarSP("sp_tbl_ColaIntegrador", [$id, 0]);
	}
}

// Selecciona(tabla, campos, where, order), 27/11/2021
$estadoIntegracion = ($Estado == 2) ? "" : "Integracion = '$Estado' AND ";
$Cons = Seleccionar("tbl_ColaIntegrador", "$top *", $estadoIntegracion . "FechaEntrada BETWEEN '$initDate 00:00:00' AND '$finalDate 23:59:59'", "ID_Cola DESC");

//$SQL = sqlsrv_query($conexion, $Cons);
$SQL = $Cons;
// Fin, 24/11/2021
?>
<!DOCTYPE html>
<html>
<!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
	<?php include_once "includes/cabecera.php"; ?>
	<!-- InstanceBeginEditable name="doctitle" -->
	<title>Log cola integración WS |
		<?php echo NOMBRE_PORTAL; ?>
	</title>
	<!-- InstanceEndEditable -->
	<!-- InstanceBeginEditable name="head" -->


	<script>
		// Agregado por Stiven, 24/11/2021
		var json = [];
		var cant = 0;
		function SeleccionarOT(DocNum) {
			var btnCambiarLote = document.getElementById('btnCambiarLote');
			var Check = document.getElementById('chkSelOT' + DocNum).checked;
			var sw = -1;

			json.forEach(function (element, index) {
				if (json[index] == DocNum) {
					sw = index;
				}
				//console.log(element,index);
			});

			if (sw >= 0) {
				json.splice(sw, 1);
				cant--;
			} else if (Check) {
				json.push(DocNum);
				cant++;
			}

			if (cant > 0) {
				$("#btnCambiarLote").removeAttr("disabled");
			} else {
				$("#chkAll").prop("checked", false);
				$("#btnCambiarLote").attr("disabled", "disabled");
			}

			//console.log(json);
		}

		function SeleccionarTodos() {
			var Check = document.getElementById('chkAll').checked;
			if (Check == false) {
				json = [];
				cant = 0;
				$("#btnCambiarLote").attr("disabled", "disabled");
			}
			$(".chkSelOT").prop("checked", Check);
			if (Check) {
				$(".chkSelOT").trigger('change');
			}
		}
	</script>


	<?php
	if (isset($_POST['a']) && ($_POST['a'] == base64_encode("OK_OFertAdd"))) {
		echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La Oferta de venta ha sido agregada exitosamente.',
                icon: 'success'
            });
		});
		</script>";
	}
	if (isset($_POST['a']) && ($_POST['a'] == base64_encode("OK_OFertUpd"))) {
		echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La Oferta de venta ha sido actualizada exitosamente.',
                icon: 'success'
            });
		});
		</script>";
	}
	?>
	<!-- InstanceEndEditable -->
</head>

<body>

	<div id="wrapper">

		<?php include_once "includes/menu.php"; ?>

		<div id="page-wrapper" class="gray-bg">
			<?php include_once "includes/menu_superior.php"; ?>
			<!-- InstanceBeginEditable name="Contenido" -->
			<div class="row wrapper border-bottom white-bg page-heading">
				<div class="col-sm-8">
					<h2>Log cola integración</h2>
					<ol class="breadcrumb">
						<li>
							<a href="#">Administración</a>
						</li>
						<li>
							<a href="#">Logs del sistema</a>
						</li>
						<li class="active">
							<strong>Log cola integración</strong>
						</li>
					</ol>
				</div>
			</div>
			<div class="wrapper wrapper-content">
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox-content">
							<?php include "includes/spinner.php"; ?>
							<form action="log_cola_integrador.php" method="post" id="formBuscar"
								class="form-horizontal">
								<div class="form-group">
									<label class="col-xs-12">
										<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para
											filtrar</h3>
									</label>
								</div>
								<div class="form-group">
									<label class="col-lg-1 control-label">Fechas</label>
									<div class="col-lg-3">
										<div class="input-daterange input-group" id="datepicker">
											<input name="FechaInicial" type="text" class="input-sm form-control"
												id="FechaInicial" placeholder="Fecha inicial"
												value="<?php echo $FechaInicial; ?>" />
											<span class="input-group-addon">-</span>
											<input name="FechaFinal" type="text" class="input-sm form-control"
												id="FechaFinal" placeholder="Fecha final"
												value="<?php echo $FechaFinal; ?>" />
										</div>
									</div>
									<label class="col-lg-1 control-label">Estado Integración</label>
									<div class="col-lg-2">
										<select name="Estado" class="form-control" id="Estado">
											<option <?php if (isset($_POST['Estado']) && $_POST['Estado'] == 2) {
												echo "selected=\"selected\"";
											} ?> value="2">(Todos)</option>
											<option <?php if (isset($_POST['Estado']) && $_POST['Estado'] == 1) {
												echo "selected=\"selected\"";
											} ?> value="1">Integrado</option>
											<option <?php if (isset($_POST['Estado']) && $_POST['Estado'] == 0) {
												echo "selected=\"selected\"";
											} ?> value="0">Pendiente</option>
											<option <?php if (isset($_POST['Estado']) && $_POST['Estado'] == -1) {
												echo "selected=\"selected\"";
											} ?> value="-1">Error</option>
										</select>
									</div>
									<label class="col-lg-1 control-label">Cantidad a visualizar</label>
									<div class="col-lg-2">
										<select name="Cantidad_Visualizar" class="form-control"
											id="Cantidad_Visualizar">
											<option value="5" <?php if ((isset($_POST['Cantidad_Visualizar'])) && (strcmp("5", $_POST['Cantidad_Visualizar']) == 0)) {
												echo "selected=\"selected\"";
											} ?>>5</option>
											<option value="10" <?php if ((isset($_POST['Cantidad_Visualizar'])) && (strcmp("10", $_POST['Cantidad_Visualizar']) == 0)) {
												echo "selected=\"selected\"";
											} ?>>10</option>
											<option value="25" <?php if ((isset($_POST['Cantidad_Visualizar'])) && (strcmp("25", $_POST['Cantidad_Visualizar']) == 0)) {
												echo "selected=\"selected\"";
											} ?>>25</option>
											<option value="50" <?php if ((isset($_POST['Cantidad_Visualizar'])) && (strcmp("50", $_POST['Cantidad_Visualizar']) == 0)) {
												echo "selected=\"selected\"";
											} ?>>50</option>
											<option value="75" <?php if ((isset($_POST['Cantidad_Visualizar'])) && (strcmp("75", $_POST['Cantidad_Visualizar']) == 0)) {
												echo "selected=\"selected\"";
											} ?>>75</option>
											<option value="100" <?php if ((isset($_POST['Cantidad_Visualizar'])) && (strcmp("100", $_POST['Cantidad_Visualizar']) == 0)) {
												echo "selected=\"selected\"";
											} ?>>100</option>
											<option value="200" <?php if ((isset($_POST['Cantidad_Visualizar'])) && (strcmp("200", $_POST['Cantidad_Visualizar']) == 0)) {
												echo "selected=\"selected\"";
											} ?>>200</option>
											<option value="300" <?php if ((isset($_POST['Cantidad_Visualizar'])) && (strcmp("300", $_POST['Cantidad_Visualizar']) == 0)) {
												echo "selected=\"selected\"";
											} ?>>300</option>
										</select>
									</div>
									<div class="col-lg-2">
										<button type="submit" class="btn btn-outline btn-success pull-right"><i
												class="fa fa-search"></i> Buscar</button>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
				<br>
				<?php //echo $Cons;
				?>
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox-content">
							<?php include "includes/spinner.php"; ?>
							<div class="row m-b-md">
								<div class="col-lg-12">
									<button class="pull-right btn btn-success" id="btnCambiarLote" name="btnCambiarLote"
										onClick="CambiarEstadoEnLote();" disabled><i class="fa fa-pencil"></i>
										Integración Pendiente</button>
								</div>
							</div>
							<div class="table-responsive">
								<table class="table table-striped table-bordered table-hover dataTables-example">
									<thead>
										<tr>
											<th>Id cola</th>
											<th>Fecha</th>
											<th>Descripción</th>
											<th>Id de documento</th>
											<th>Tipo de documento</th>
											<th>Mensaje del proceso</th>
											<th>Fecha Ejecucion</th>
											<th>Reintentos</th>
											<th>Integración</th>
											<th class="text-center">
												<div class="checkbox checkbox-success"><input type="checkbox"
														id="chkAll" value="" onChange="SeleccionarTodos();"
														title="Seleccionar todos"><label></label></div>
											</th>
										</tr>
									</thead>
									<tbody>
										<?php
										if ($sw == 1) {
											while ($row = sqlsrv_fetch_array($SQL)) { ?>
												<tr class="gradeX">
													<td>
														<?php echo $row['ID_Cola']; ?>
													</td>
													<td>
														<?php echo $row['FechaEntrada']->format('Y-m-d h:m:i'); ?>
													</td>
													<td>
														<?php echo $row['DE_Cola']; ?>
													</td>
													<td>
														<?php echo $row['ID_Documento']; ?>
													</td>
													<td>
														<?php echo $row['TipoDocumento']; ?>
													</td>
													<td>
														<?php echo $row['MensajeDelProceso']; ?>
													</td>
													<td>
														<?php echo (isset($row['FechaEjecucion']) && ($row['FechaEjecucion'] != '')) ? ($row['FechaEjecucion']->format('Y-m-d h:m:i')) : ""; ?>
													</td>
													<td>
														<?php echo $row['CantidadReintento']; ?>
													</td>
													<td>
														<?php echo "<span id='lblEstado" . $row['ID_Cola'] . "'";
														if ($row['Integracion'] == '1') {
															echo "class='label label-info'> Integrado";
														} elseif ($row['Integracion'] == '-1') {
															echo "class='label label-danger'> Error";
														} else {
															echo "class='label label-primary'> Pendiente";
														}
														echo "</span>"; ?>
													</td>
													<td class="text-center">
														<div class="checkbox checkbox-success"
															id="dvChkSel<?php echo $row['ID_Cola']; ?>">
															<input type="checkbox" class="chkSelOT"
																id="chkSelOT<?php echo $row['ID_Cola']; ?>" value=""
																onChange="SeleccionarOT('<?php echo $row['ID_Cola']; ?>');"
																aria-label="Single checkbox One"><label></label>
														</div>
													</td>
												</tr>
											<?php }
										} ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- InstanceEndEditable -->
			<?php include_once "includes/footer.php"; ?>

		</div>
	</div>
	<?php include_once "includes/pie.php"; ?>
	<!-- InstanceBeginEditable name="EditRegion4" -->
	<script>
		$(document).ready(function () {
			$("#formBuscar").validate({
				submitHandler: function (form) {
					$('.ibox-content').toggleClass('sk-loading');
					form.submit();
				}
			});

			$(".alkin").on('click', function () {
				$('.ibox-content').toggleClass('sk-loading');
			});
			$('#FechaInicial').datepicker({
				todayBtn: "linked",
				keyboardNavigation: false,
				forceParse: false,
				calendarWeeks: true,
				autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true,
			});
			$('#FechaFinal').datepicker({
				todayBtn: "linked",
				keyboardNavigation: false,
				forceParse: false,
				calendarWeeks: true,
				autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true,
			});

			$('.chosen-select').chosen({
				width: "100%"
			});

			var options = {
				url: function (phrase) {
					return "ajx_buscar_datos_json.php?type=7&id=" + phrase;
				},

				getValue: "NombreBuscarCliente",
				requestDelay: 400,
				list: {
					match: {
						enabled: true
					},
					onClickEvent: function () {
						var value = $("#NombreCliente").getSelectedItemData().CodigoCliente;
						$("#Cliente").val(value);
					}
				}
			};

			$("#NombreCliente").easyAutocomplete(options);

			var cantidadVisualizar = <?php echo (isset($_POST['Cantidad_Visualizar'])) ? $_POST['Cantidad_Visualizar'] : 5; ?>

			$('.dataTables-example').DataTable({
				pageLength: cantidadVisualizar,
				lengthMenu: getLengthMenu(cantidadVisualizar),
				responsive: false,
				dom: '<"html5buttons"B>lTfgitp',
				language: {
					"decimal": "",
					"emptyTable": "No se encontraron resultados.",
					"info": "Mostrando _START_ - _END_ de _TOTAL_ registros",
					"infoEmpty": "Mostrando 0 - 0 de 0 registros",
					"infoFiltered": "(filtrando de _MAX_ registros)",
					"infoPostFix": "",
					"thousands": ",",
					"lengthMenu": "Mostrar _MENU_ registros",
					"loadingRecords": "Cargando...",
					"processing": "Procesando...",
					"search": "Filtrar:",
					"zeroRecords": "Ningún registro encontrado",
					"paginate": {
						"first": "Primero",
						"last": "Último",
						"next": "Siguiente",
						"previous": "Anterior"
					},
					"aria": {
						"sortAscending": ": Activar para ordenar la columna ascendente",
						"sortDescending": ": Activar para ordenar la columna descendente"
					}
				},
				buttons: []

			});

			function getLengthMenu(maximumQuantity) {
				var lengthMenu = []
				switch (maximumQuantity) {
					case 5:
						lengthMenu = [5]
						break
					case 10:
						lengthMenu = [5, 10]
						break
					case 25:
						lengthMenu = [5, 10, 25]
						break
					case 50:
						lengthMenu = [5, 10, 25, 50]
						break
					case 75:
						lengthMenu = [5, 10, 25, 50, 75]
						break
					case 100:
						lengthMenu = [5, 10, 25, 50, 75, 100]
						break
					default:
						lengthMenu = [5]
						break
				}
				return lengthMenu
			}
		});


		function CambiarEstadoEnLote() {
			Swal.fire({
				title: "¿Está seguro que desea ejecutar el proceso?",
				text: "Se modificarán los estados de los registros",
				icon: "info",
				showCancelButton: true,
				confirmButtonText: "Si, confirmo",
				cancelButtonText: "No"
			}).then((result) => {
				// Cargando...
				// $('.ibox-content').toggleClass('sk-loading',true);
				if (result.isConfirmed) {
					$.ajax({
						type: "POST",
						url: "log_cola_integrador.php",
						async: false,
						data: {
							"ids": json
						},
						success: function (response, status) {
							// Cargando...
							// $('.ibox-content').toggleClass('sk-loading',false);
							Swal.fire({
								title: "Proceso ejecutado",
								text: "Registros actualizados exitosamente",
								icon: "success"
							});
							// Actualizar vista
							json.forEach(id => {
								// $('#btnEstado'+id).hide();
								// $('#dvChkSel'+id).remove();
								$('#lblEstado' + id).removeClass()
								$('#lblEstado' + id).addClass("label label-primary");
								$('#lblEstado' + id).html("Pendiente");
							});
							$(".chkSelOT").prop("checked", false);
							$("#chkAll").prop("checked", false);
							$("#btnCambiarLote").attr("disabled", "disabled");
							json = [];
							cant = 0;
						}
					});
				}
			});
		}
	</script>
	<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd -->

</html>
<?php sqlsrv_close($conexion); ?>