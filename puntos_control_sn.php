<?php require_once "includes/conexion.php";
PermitirAcceso(318);
$sw = 0; //Para saber si ya se selecciono un cliente y mostrar las sucursales
$Filtro = "";
$sw_suc = 0; // SMM, 14/02/2023

//Normas de reparto (Sucursal)
$SQL_DRSucursal = Seleccionar('uvw_Sap_tbl_DimensionesReparto', '*', 'DimCode=3');

if (isset($_GET['Anno']) && ($_GET['Anno'] != "")) {
	$Anno = $_GET['Anno'];
	$sw = 1;
} else {
	$Anno = date('Y');
}

//Cliente
if (isset($_GET['Cliente'])) {
	if ($_GET['Cliente'] != "") { //Si se selecciono el cliente
		$Filtro .= " and ID_CodigoCliente='" . $_GET['Cliente'] . "'";
		$sw_suc = 1; //Cuando se ha seleccionado una sucursal
		$sw = 1;
		if (isset($_GET['Sucursal'])) {
			if ($_GET['Sucursal'] == "") {
				//Sucursales
				if (PermitirFuncion(205)) {
					$Where = "CodigoCliente='" . $_GET['Cliente'] . "' and TipoDireccion='S'";
					$SQL_Sucursal = Seleccionar("uvw_Sap_tbl_Clientes_Sucursales", "NombreSucursal", $Where);
				} else {
					$Where = "CodigoCliente='" . $_GET['Cliente'] . "' and TipoDireccion='S' and ID_Usuario = " . $_SESSION['CodUser'];
					$SQL_Sucursal = Seleccionar("uvw_tbl_SucursalesClienteUsuario", "NombreSucursal", $Where);
				}
				$j = 0;
				unset($WhereSuc);
				$WhereSuc = array();
				while ($row_Sucursal = sqlsrv_fetch_array($SQL_Sucursal)) {
					$WhereSuc[$j] = "NombreSucursal='" . $row_Sucursal['NombreSucursal'] . "'";
					$j++;
				}
				$FiltroSuc = implode(" OR ", $WhereSuc);
				$Filtro .= " and (" . $FiltroSuc . ")";
			} else {
				$Filtro .= " and NombreSucursal='" . $_GET['Sucursal'] . "'";
			}
		}

	} else {
		if (!PermitirFuncion(205)) {
			$Where = "ID_Usuario = " . $_SESSION['CodUser'];
			$SQL_Cliente = Seleccionar("uvw_tbl_ClienteUsuario", "CodigoCliente, NombreCliente", $Where);
			$k = 0;
			while ($row_Cliente = sqlsrv_fetch_array($SQL_Cliente)) {

				//Sucursales
				$Where = "CodigoCliente='" . $row_Cliente['CodigoCliente'] . "' and TipoDireccion='S' and ID_Usuario = " . $_SESSION['CodUser'];
				$SQL_Sucursal = Seleccionar("uvw_tbl_SucursalesClienteUsuario", "NombreSucursal", $Where);

				$j = 0;
				unset($WhereSuc);
				$WhereSuc = array();
				while ($row_Sucursal = sqlsrv_fetch_array($SQL_Sucursal)) {
					$WhereSuc[$j] = "NombreSucursal='" . $row_Sucursal['NombreSucursal'] . "'";
					$j++;
				}

				$FiltroSuc = implode(" OR ", $WhereSuc);

				if ($k == 0) {
					$Filtro .= " AND (ID_CodigoCliente='" . $row_Cliente['CodigoCliente'] . "' AND (" . $FiltroSuc . "))";
				} else {
					$Filtro .= " OR (ID_CodigoCliente='" . $row_Cliente['CodigoCliente'] . "' AND (" . $FiltroSuc . "))";
				}

				$k++;
			}
		}
	}
}
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
	<?php include_once "includes/cabecera.php"; ?>
	<!-- InstanceBeginEditable name="doctitle" -->
	<title>Puntos de control de socios de negocios |
		<?php echo NOMBRE_PORTAL; ?>
	</title>
	<!-- InstanceEndEditable -->
	<!-- InstanceBeginEditable name="head" -->
	<script type="text/javascript">
		$(document).ready(function () {
			$("#NombreCliente").change(function () {
				var NomCliente = document.getElementById("NombreCliente");
				var Cliente = document.getElementById("Cliente");
				if (NomCliente.value == "") {
					Cliente.value = "";
					$("#Cliente").trigger("change");
				}
			});
			$("#Cliente").change(function () {
				var Cliente = document.getElementById("Cliente");
				$('.ibox-content').toggleClass('sk-loading', true);
				$.ajax({
					type: "POST",
					url: "ajx_cbo_sucursales_clientes_simple.php?CardCode=" + Cliente.value + "&sucline=1&tdir=S",
					success: function (response) {
						$('#Sucursal').html(response).fadeIn();
						$("#Sucursal").trigger("change");
						$('.ibox-content').toggleClass('sk-loading', false);
					}
				});
			});
		});
	</script>

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
					<h2>Puntos de control de socios de negocios</h2>
					<ol class="breadcrumb">
						<li>
							<a href="index1.php">Inicio</a>
						</li>
						<li>
							<a href="#">Datos maestros</a>
						</li>
						<li>
							<a href="#">Puntos de control</a>
						</li>
						<li class="active">
							<strong>Puntos de control de socios de negocios</strong>
						</li>
					</ol>
				</div>
			</div>
			<div class="wrapper wrapper-content">
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox-content">
							<?php include "includes/spinner.php"; ?>
							<form action="puntos_control_sn.php" method="get" class="form-horizontal" id="frmBuscar"
								name="frmBuscar">
								<div class="form-group">
									<label class="col-xs-12">
										<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para
											filtrar</h3>
									</label>
								</div>
								<div class="form-group">
									<!-- Inicio, Cliente -->
									<label class="col-lg-1 control-label">Socio Negocio <span
											class="text-danger">*</span></label>
									<div class="col-lg-3">
										<input name="Cliente" type="hidden" id="Cliente" value="<?php if (isset($_GET['Cliente']) && ($_GET['Cliente'] != "")) {
											echo $_GET['Cliente'];
										} ?>">
										<input name="NombreCliente" type="text" class="form-control" id="NombreCliente"
											placeholder="Ingrese para buscar..." value="<?php if (isset($_GET['NombreCliente']) && ($_GET['NombreCliente'] != "")) {
												echo $_GET['NombreCliente'];
											} ?>" required>
									</div>
									<!-- Fin, Cliente -->

									<!-- Inicio, Sucursal que depende del Cliente -->
									<label class="col-lg-1 control-label">Sucursal Socio Negocio</label>
									<div class="col-lg-3">
										<select id="Sucursal" name="Sucursal" class="form-control select2">
											<option value="">(Todos)</option>
											<?php if ($sw_suc == 1) { ?>
												<?php if (PermitirFuncion(205)) { ?>
													<?php $Where = "CodigoCliente='" . $_GET['Cliente'] . "' and TipoDireccion='S'"; ?>
													<?php $SQL_Sucursal = Seleccionar("uvw_Sap_tbl_Clientes_Sucursales", "NombreSucursal, NumeroLinea", $Where); ?>
												<?php } else { ?>
													<?php $Where = "CodigoCliente='" . $_GET['Cliente'] . "' and TipoDireccion='S' and ID_Usuario = " . $_SESSION['CodUser']; ?>
													<?php $SQL_Sucursal = Seleccionar("uvw_tbl_SucursalesClienteUsuario", "NombreSucursal, NumeroLinea", $Where); ?>
												<?php } ?>
												<?php while ($row_Sucursal = sqlsrv_fetch_array($SQL_Sucursal)) { ?>
													<option value="<?php echo $row_Sucursal['NumeroLinea']; ?>" <?php if (strcmp($row_Sucursal['NumeroLinea'], $_GET['Sucursal']) == 0) {
														   echo "selected=\"selected\"";
													   } ?>><?php echo $row_Sucursal['NombreSucursal']; ?></option>
												<?php } ?>
											<?php } ?>
										</select>
									</div>
									<!-- Fin, Sucursal que depende del Cliente -->

									<!-- Inicio, Submit BTN -->
									<div class="col-lg-4">
										<button type="submit" class="btn btn-outline btn-info pull-right"><i
												class="fa fa-search"></i> Buscar</button>
									</div>
									<!-- Fin, Submit BTN -->
								</div>

								<?php if ($sw == 1) { ?>
									<br>
									<div class="form-group">
										<label class="col-xs-12">
											<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-ellipsis-h"></i> Acciones
											</h3>
										</label>
									</div>

									<div class="form-group">
										<iframe id="DataGrid" name="DataGrid" style="border: 0;" width="100%"
											height="700"></iframe>
									</div>

									<script>
										document.addEventListener('DOMContentLoaded', function () {
											let iframe = document.getElementById('DataGrid');
											iframe.src = 'detalle_puntos_control_sn.php?cardcode=<?php echo base64_encode($_GET['Cliente']); ?>&idsucursal=<?php echo base64_encode($_GET['Sucursal']); ?>&periodo=<?php echo base64_encode($Anno); ?>&namesucursal=' + encodeURIComponent($('#Sucursal option:selected').text());
										});
									</script>
								<?php } ?>
							</form>
						</div>
					</div>
				</div>
			</div> <!-- row -->

			<!-- InstanceEndEditable -->
			<br>
			<?php include_once "includes/footer.php"; ?>
		</div>
	</div>

	<?php include_once "includes/pie.php"; ?>

	<!-- InstanceBeginEditable name="EditRegion4" -->
	<script>
		$(document).ready(function () {
			// SMM, 18/01/2023
			$('[data-toggle="tooltip"]').tooltip();

			$("#frmBuscar").validate({
				submitHandler: function (form) {
					$('.ibox-content').toggleClass('sk-loading');
					form.submit();
				}
			});

			$(".btn_del").each(function (el) {
				$(this).bind("click", delRow);
			});

			//$(".btn_plus").bind("click",addField);

			$('#FechaCorte').datepicker({
				todayBtn: "linked",
				keyboardNavigation: false,
				forceParse: false,
				calendarWeeks: true,
				autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true
			});

			$("#frmAlertas").validate();

			$(".truncate").dotdotdot({
				watch: 'window'
			});

			$(".select2").select2();

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
						$("#Cliente").val(value).trigger("change");
					}
				}
			};

			$("#NombreCliente").easyAutocomplete(options);
		});
	</script>

	<script>
		function delRow() {//Eliminar div
			$(this).parent('div').remove();
		}
		function delRow2(btn) {//Eliminar div
			$(btn).parent('div').remove();
		}
	</script>
	<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd -->

</html>
<?php sqlsrv_close($conexion); ?>