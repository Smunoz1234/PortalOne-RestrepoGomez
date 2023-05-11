<?php require_once("includes/conexion.php");
// PermitirAcceso(208);
if (isset($_POST['step']) && $_POST['step'] != "") {
	$Step = $_POST['step'];
} else {
	$Step = 1;
}

//Clientes
if (PermitirFuncion(205)) {
	$SQL_Cliente = Seleccionar("uvw_Sap_tbl_Clientes", "CodigoCliente, NombreCliente", "");
} else {
	$Where = "ID_Usuario = " . $_SESSION['CodUser'];
	$SQL_Cliente = Seleccionar("uvw_tbl_ClienteUsuario", "CodigoCliente, NombreCliente", $Where);
}

if ($Step == 3) {
	//Sucursales
	if (PermitirFuncion(205)) {
		$Where = "CodigoCliente='" . $_POST['CodigoCliente'] . "'";
		$SQL_Sucursal = Seleccionar("uvw_Sap_tbl_Clientes_Sucursales", "NombreSucursal", $Where);
	} else {
		$Where = "CodigoCliente='" . $_POST['CodigoCliente'] . "' and ID_Usuario = " . $_SESSION['CodUser'];
		$SQL_Sucursal = Seleccionar("uvw_tbl_SucursalesClienteUsuario", "NombreSucursal", $Where);
	}

	$ListSucursales = array();
	$j = 0;
	while ($row_Sucursal = sqlsrv_fetch_array($SQL_Sucursal)) {
		$ListSucursales[$j] = $row_Sucursal['NombreSucursal'];
		$j++;
	}

	$NombreCliente = "";
	if (isset($_POST['CodigoCliente']) && ($_POST['CodigoCliente'] != "")) {
		while ($row_Cliente = sqlsrv_fetch_array($SQL_Cliente)) {
			if (strcmp($row_Cliente['CodigoCliente'], $_POST['CodigoCliente']) == 0) {
				$NombreCliente = $row_Cliente['NombreCliente'];
				break;
			}
		}
	}
}

// SMM, 05/10/2023
$SQL_Categorias = Seleccionar('uvw_tbl_PortalClientes_Categorias', '*');
$indicadorJerarquia = "&nbsp;&nbsp;&nbsp;";
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
	<?php include_once("includes/cabecera.php"); ?>
	<!-- InstanceBeginEditable name="doctitle" -->
	<title>
		<?php echo NOMBRE_PORTAL; ?> | Cargar informes
	</title>
	<!-- InstanceEndEditable -->
	<!-- InstanceBeginEditable name="head" -->
	<script type="text/javascript">
		$(document).ready(function () {//Cargar sucursales
			$("#Cliente").change(function () {
				$.ajax({
					type: "POST",
					url: "ajx_cboSucursal.php?id=" + document.getElementById('Cliente').value,
					success: function (response) {
						$('#Sucursal').html(response).fadeIn();
					}
				});
			});
		});
	</script>
	<!-- InstanceEndEditable -->
</head>

<body>

	<div id="wrapper">

		<?php include_once("includes/menu.php"); ?>

		<div id="page-wrapper" class="gray-bg">
			<?php include_once("includes/menu_superior.php"); ?>
			<!-- InstanceBeginEditable name="Contenido" -->
			<div class="row wrapper border-bottom white-bg page-heading">
				<div class="col-sm-8">
					<h2>Cargar Archivos - Portal Clientes</h2>
					<ol class="breadcrumb">
						<li>
							<a href="index1.php">Inicio</a>
						</li>
						<li>
							<a href="#">Portal Clientes</a>
						</li>
						<li>
							<a href="gestionar_archivos_clientes.php">Gestionar Archivos</a>
						</li>
						<li class="active">
							<strong>Cargar Archivos</strong>
						</li>
					</ol>
				</div>
			</div>
			<div class="wrapper wrapper-content">
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox-content">
							<?php include("includes/spinner.php"); ?>
							<?php if ($Step == 1) { ?>
								<form action="gestionar_archivos_clientes_add.php" method="post" class="form-horizontal"
									id="SeleccionarCliente">
									<div class="form-group">
										<label class="col-lg-12">
											<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-cloud-upload"></i> Seleccione
												el cliente para cargar la información</h3>
										</label>
									</div>
									<div class="form-group">
										<label class="col-sm-1 control-label">Cliente</label>
										<div class="col-sm-6">
											<select name="Cliente" class="form-control m-b chosen-select" id="Cliente">
												<option value="">(Todos)</option>
												<?php while ($row_Cliente = sqlsrv_fetch_array($SQL_Cliente)) { ?>
													<option value="<?php echo $row_Cliente['CodigoCliente']; ?>"><?php echo $row_Cliente['NombreCliente']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div><br>
									<div class="form-group">
										<div class="col-sm-9">
											<button class="btn btn-primary" type="submit">Continuar <i
													class="fa fa-arrow-circle-right"></i></button> <a
												href="gestionar_archivos_clientes.php"
												class="btn btn-outline btn-default"><i
													class="fa fa-arrow-circle-o-left"></i> Regresar</a>
										</div>
									</div>
									<input type="hidden" id="step" name="step" value="2" />
								</form>
							<?php } elseif ($Step == 2) {
								LimpiarDirTemp();
								?>
								<div class="row">
									<div class="col-lg-12">
										<form action="upload.php" class="dropzone" id="dropzoneForm">
											<div class="fallback">
												<input name="File" id="File" type="file" />
											</div>
										</form>
									</div>
								</div>
								<br><br>
								<div class="row">
									<div class="col-lg-12">
										<form action="gestionar_archivos_clientes_add.php" method="post"
											class="form-horizontal" id="AgregarArchivos">
											<div class="col-sm-9">
												<button class="btn btn-primary" type="submit">Continuar <i
														class="fa fa-arrow-circle-right"></i></button> <a
													href="gestionar_archivos_clientes.php"
													class="btn btn-outline btn-default"><i
														class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
											</div>
											<input type="hidden" id="step" name="step" value="3" />
											<input type="hidden" id="CodigoCliente" name="CodigoCliente"
												value="<?php echo $_POST['Cliente']; ?>" />
										</form>
									</div>
								</div>
							<?php } elseif ($Step == 3) { ?>
								<form action="registro.php" method="post" class="form-horizontal" id="AgregarDatos">
									<div class="form-group">
										<label class="col-lg-12">
											<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Ingresar
												información de los archivos</h3>
										</label>
									</div>
									<div class="form-group">
										<h3 class="col-lg-12">Cliente:<br>
											<?php echo ($NombreCliente == "") ? "(Todos)" : $NombreCliente; ?>
										</h3>
									</div>
									<br>
									<?php
									$temp = ObtenerVariable("CarpetaTmp");
									$dir = $temp . "/" . $_SESSION['CodUser'] . "/";
									$ruta = opendir($dir);
									$DocFiles = array();
									$i = 0;
									while ($archivo = readdir($ruta)) { //obtenemos un archivo y luego otro sucesivamente
										// SMM, 05/10/2023
										sqlsrv_fetch($SQL_Categorias, SQLSRV_SCROLL_ABSOLUTE, -1);

										if (($archivo == ".") || ($archivo == ".."))
											continue;

										if (!is_dir($archivo)) { //verificamos si es o no un directorio
											$peso = FormatUnitBytes(filesize($dir . $archivo));
											$DocFiles[$i] = $archivo;
											$exp = explode('.', $archivo);
											$Ext = end($exp);
											$Icon = IconAttach($Ext);
											?>
											<div class="col-lg-12">
												<div class="col-lg-2">
													<div class="form-group">
														<div class="file-box">
															<div class="file">
																<a href="#">
																	<span class="corner"></span>
																	<div class="icon">
																		<i class="<?php echo $Icon; ?>"></i>
																	</div>
																	<div class="file-name truncate">
																		<?php echo $archivo; ?>
																		<br />
																		<small>
																			<?php echo $peso; ?>
																		</small>
																	</div>
																</a>
															</div>
														</div>
													</div>
												</div>
												<div class="col-lg-6">
													<div class="form-group" <?php if($NombreCliente == "") { echo "style='visibility: hidden;'"; }?>>
														<label class="col-sm-4 control-label">Sucursal</label>
														<div class="col-sm-8">
															<select id="Sucursal<?php echo $i; ?>"
																name="Sucursal<?php echo $i; ?>[]" data-placeholder="(Todos)"
																class="chosen-select" multiple>
																<?php
																foreach ($ListSucursales as $NombreSuc) { ?>
																	<option value="<?php echo $NombreSuc; ?>"><?php echo $NombreSuc; ?>
																	</option>
																<?php } ?>
															</select>
														</div>
													</div>
													<div class="form-group">
														<label class="col-sm-4 control-label">Categoria <span
																class="text-danger">*</span></label>
														<div class="col-sm-8">
															<select name="Categoria<?php echo $i; ?>" required
																class="form-control m-b select2" id="Categoria<?php echo $i; ?>">
																<option value="" disabled selected>Seleccione...</option>
																<?php while ($row_Categoria = sqlsrv_fetch_array($SQL_Categorias)) { ?>
																	<option value="<?php echo $row_Categoria['id']; ?>" <?php if ((isset($row['id_categoria'])) && (strcmp($row_Categoria['id'], $row['id_categoria']) == 0)) {
																		   echo "selected";
																	   } ?> 				
																	   <?php if ($row_Categoria['es_hoja'] == 0) {
																			echo "style='color: white; background-color: darkgray; font-weight: bold;' disabled";
																		} ?>>
																		<?php echo str_repeat($indicadorJerarquia, ($row_Categoria['nivel'])) . ' ' . $row_Categoria['nombre_categoria']; ?>
																	</option>
																<?php } ?>
															</select>
														</div>
													</div>
													<div class="form-group">
														<label class="col-sm-4 control-label">Fecha</label>
														<div class="col-sm-8">
															<div class="input-group date">
																<span class="input-group-addon"><i
																		class="fa fa-calendar"></i></span><input
																	name="Fecha<?php echo $i; ?>" type="text" required="required"
																	class="form-control" id="Fecha<?php echo $i; ?>"
																	value="<?php echo date('d/m/Y'); ?>" readonly>
															</div>
														</div>
													</div>
													<div class="form-group">
														<label class="col-sm-4 control-label">Comentarios</label>
														<div class="col-sm-8"><textarea name="Comentarios<?php echo $i; ?>" rows="6"
																maxlength="1000" class="form-control"
																id="Comentarios<?php echo $i; ?>"
																placeholder="Descripción del documento..."></textarea></div>
													</div>
												</div>
											</div><br><br>
											<?php
											//echo $archivo." (".$peso.")"."<br />";
											$i++;
										}
									}
									closedir($ruta);
									?>

									<br>
									<div class="form-group">
										<div class="col-sm-9">
											<button class="btn btn-primary" id="toggleSpinners" type="submit">Continuar <i
													class="fa fa-arrow-circle-right"></i></button> <a
												href="gestionar_archivos_clientes.php"
												class="btn btn-outline btn-default"><i
													class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
										</div>
									</div>

									<!-- SMM, 05/11/2023 -->
									<input type="hidden" id="type" name="type" value="1">

									<input type="hidden" id="P" name="P" value="59" />
									<input type="hidden" id="CantFiles" name="CantFiles" value="<?php echo $i; ?>" />
									<input type="hidden" id="CodigoCliente" name="CodigoCliente"
										value="<?php echo $_POST['CodigoCliente']; ?>" />
								</form>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<!-- InstanceEndEditable -->
			<?php include_once("includes/footer.php"); ?>

		</div>
	</div>
	<?php include_once("includes/pie.php"); ?>
	<!-- InstanceBeginEditable name="EditRegion4" -->
	<script>
		Dropzone.options.dropzoneForm = {
			paramName: "File", // The name that will be used to transfer the file
			maxFilesize: "<?php echo ObtenerVariable("MaxSizeFile"); ?>", // MB
			maxFiles: "<?php echo ObtenerVariable("CantidadArchivos"); ?>",
			uploadMultiple: true,
			addRemoveLinks: true,
			dictRemoveFile: "Quitar",
			acceptedFiles: "<?php echo ObtenerVariable("TiposArchivos"); ?>",
			dictDefaultMessage: "<strong>Haga clic aqui para cargar archivos</strong><br>Tambien puede arrastrarlos hasta aqui<br><h4><small>(máximo <?php echo ObtenerVariable("CantidadArchivos"); ?> archivos a la vez)<small></h4>",
			dictFallbackMessage: "Tu navegador no soporta cargue de archivos mediante arrastrar y soltar",
			removedfile: function (file) {
				$.get("includes/procedimientos.php", {
					type: "3",
					nombre: file.name
				}).done(function (data) {
					var _ref;
					return (_ref = file.previewElement) !== null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
				});
			}
		};
	</script>
	<script>
		$(document).ready(function () {
			$("#AgregarDatos").validate({
				submitHandler: function(form) {
					$('.ibox-content').addClass('sk-loading'); // activa el mensaje "cargando"
					form.submit(); // envía el formulario
				}
			});
			
			/*
			$(".truncate").dotdotdot({
				watch: 'window'
			});
			*/

			$('.chosen-select').chosen({ width: "100%" });
			<?php
			/*if($Step==3){
			$k=0;
			while($k<$i){?>  
			$('#Fecha<?php echo $k;?>').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			format: 'dd/mm/yyyy'
			});
			<?php $k++;}
			}*/?>

			$('.file-box').each(function () {
				animationHover(this, 'pulse');
			});
		});

		/*
		$(function () {
			$('#toggleSpinners').on('click', function () {
				$('.ibox-content').toggleClass('sk-loading');
			})
		});
		*/
	</script>
	<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd -->

</html>
<?php sqlsrv_close($conexion); ?>