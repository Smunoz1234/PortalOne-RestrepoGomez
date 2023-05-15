<?php
require_once "includes/conexion.php";

// PermitirAcceso(216);
$sw_error = 0;

if (isset($_POST['Metodo']) && ($_POST['Metodo'] == 3)) {
	try {
		$Param = array(
			$_POST['Metodo'], // 3 - Eliminar
			isset($_POST['ID']) ? $_POST['ID'] : "NULL",
		);

		if ($_POST['TipoDoc'] == "Categoria") {
			$SQL = EjecutarSP('sp_tbl_PortalClientes_Categorias', $Param);
			if (!$SQL) {
				$sw_error = 1;
				$msg_error = "No se pudo eliminar la Categoría.";
			}
		} elseif ($_POST['TipoDoc'] == "Funcion") {
			$SQL = EjecutarSP('sp_tbl_PortalClientes_Funciones', $Param);
			if (!$SQL) {
				$sw_error = 1;
				$msg_error = "No se pudo eliminar la Funcion.";
			}
		}

	} catch (Exception $e) {
		$sw_error = 1;
		$msg_error = $e->getMessage();
	}
}

//Insertar datos o actualizar datos
if ((isset($_POST['frmType']) && ($_POST['frmType'] != "")) || (isset($_POST['Metodo']) && ($_POST['Metodo'] == 2))) {
	try {

		if ($_POST['TipoDoc'] == "Categoria") {
			$FechaHora = "'" . FormatoFecha(date('Y-m-d'), date('H:i:s')) . "'";
			$Usuario = "'" . $_SESSION['CodUser'] . "'";

			$Perfiles = implode(";", $_POST['Perfiles']);
			$Perfiles = count($_POST['Perfiles']) > 0 ? "'$Perfiles'" : "''";

			$ID = (isset($_POST['ID_Actual']) && ($_POST['ID_Actual'] != "")) ? $_POST['ID_Actual'] : "NULL";

			$Param = array(
				$_POST['Metodo'] ?? 1,
				// 1 - Crear, 2 - Actualizar
				$ID,
				"'" . $_POST['ID_CategoriaPadre'] . "'",
				"'" . $_POST['NombreCategoria'] . "'",
				$Perfiles,
				"'" . $_POST['Comentarios'] . "'",
				"'" . $_POST['Estado'] . "'",
				"'" . $_POST['Tipo'] . "'",
				$Usuario,
				// @id_usuario_actualizacion
				$FechaHora,
				// @fecha_actualizacion
				$FechaHora, // @hora_actualizacion
				($_POST['Metodo'] == 1) ? $Usuario : "NULL",
				($_POST['Metodo'] == 1) ? $FechaHora : "NULL",
				($_POST['Metodo'] == 1) ? $FechaHora : "NULL",
			);

			$SQL = EjecutarSP('sp_tbl_PortalClientes_Categorias', $Param);
			if (!$SQL) {
				$sw_error = 1;
				$msg_error = "No se pudo insertar la nueva Categoría";
			}
		} elseif ($_POST['TipoDoc'] == "Funcion") {
			$FechaHora = "'" . FormatoFecha(date('Y-m-d'), date('H:i:s')) . "'";
			$Usuario = "'" . $_SESSION['CodUser'] . "'";

			$Perfiles = implode(";", $_POST['Perfiles']);
			$Perfiles = count($_POST['Perfiles']) > 0 ? "'$Perfiles'" : "''";

			$ID = (isset($_POST['ID_Actual']) && ($_POST['ID_Actual'] != "")) ? $_POST['ID_Actual'] : "NULL";

			$Param = array(
				$_POST['Metodo'] ?? 1,
				// 1 - Crear, 2 - Actualizar
				$ID,
				"'" . $_POST['ID_Categoria'] . "'",
				"'" . $_POST['Ruta'] . "'",
				"'" . $_POST['EtiquetaConsulta'] . "'",
				$Perfiles,
				"'" . $_POST['Comentarios'] . "'",
				"'" . $_POST['Estado'] . "'",
				$Usuario,
				// @id_usuario_actualizacion
				$FechaHora,
				// @fecha_actualizacion
				$FechaHora, // @hora_actualizacion
				($_POST['Metodo'] == 1) ? $Usuario : "NULL",
				($_POST['Metodo'] == 1) ? $FechaHora : "NULL",
				($_POST['Metodo'] == 1) ? $FechaHora : "NULL",
			);

			$SQL = EjecutarSP('sp_tbl_PortalClientes_Funciones', $Param);
			if (!$SQL) {
				$sw_error = 1;
				$msg_error = "No se pudo insertar la nueva Consulta";
			}
		}

		// OK
		if ($sw_error == 0) {
			$TipoDoc = $_POST['TipoDoc'];
			header("Location:parametros_portal_clientes.php?doc=$TipoDoc&a=" . base64_encode("OK_PRUpd") . "#$TipoDoc");
		}

	} catch (Exception $e) {
		$sw_error = 1;
		$msg_error = $e->getMessage();
	}

}

$SQL_Categorias = Seleccionar("uvw_tbl_PortalClientes_Categorias", "*");
$SQL_Funciones = Seleccionar("uvw_tbl_PortalClientes_Funciones", "*");
$SQL_Perfiles = Seleccionar('uvw_tbl_PerfilesUsuarios', '*');
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
	<?php include_once "includes/cabecera.php"; ?>
	<!-- InstanceBeginEditable name="doctitle" -->
	<title>Parámetros Menú Portal Clientes |
		<?php echo NOMBRE_PORTAL; ?>
	</title>
	<!-- InstanceEndEditable -->
	<!-- InstanceBeginEditable name="head" -->

	<style>
		.ibox-title a {
			color: inherit !important;
		}

		.collapse-link:hover {
			cursor: pointer;
		}

		.swal2-container {
			z-index: 9000;
		}

		.easy-autocomplete {
			width: 100% !important
		}
	</style>

	<?php
	if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_PRUpd"))) {
		echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'Datos actualizados exitosamente.',
                icon: 'success'
            });
		});
		</script>";
	}
	if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_PRDel"))) {
		echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'Datos eliminados exitosamente.',
                icon: 'success'
            });
		});
		</script>";
	}
	if (isset($sw_error) && ($sw_error == 1)) {
		echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Ha ocurrido un error!',
                text: '" . LSiqmlObs($msg_error) . "',
                icon: 'warning'
            });
		});
		</script>";
	}
	?>
	<script>

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
					<h2>Parámetros Menú Portal Clientes</h2>
					<ol class="breadcrumb">
						<li>
							<a href="index1.php">Inicio</a>
						</li>
						<li>
							<a href="#">Administración</a>
						</li>
						<li>
							<a href="#">Parámetros del sistema</a>
						</li>
						<li class="active">
							<strong>Parámetros Menú Portal Clientes</strong>
						</li>
					</ol>
				</div>
			</div>
			<?php //echo $Cons;?>
			<div class="wrapper wrapper-content">
				<div class="modal inmodal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-lg">
						<div class="modal-content" id="ContenidoModal">

						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox-content">
							<?php include "includes/spinner.php"; ?>

							<div class="tabs-container">

								<ul class="nav nav-tabs">
									<li
										class="<?php echo (isset($_GET['doc']) && ($_GET['doc'] == "Categoria") || !isset($_GET['doc'])) ? "active" : ""; ?>">
										<a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Categorías</a>
									</li>
									<li
										class="<?php echo (isset($_GET['doc']) && ($_GET['doc'] == "Consulta")) ? "active" : ""; ?>">
										<a data-toggle="tab" href="#tab-2"><i class="fa fa-list"></i> Función de
											Menú</a>
									</li>
									<li
										class="<?php echo (isset($_GET['doc']) && ($_GET['doc'] == "Consulta")) ? "active" : ""; ?>">
										<a data-toggle="tab" href="#tab-3"><i class="fa fa-list"></i> Jerarquias
											Equipos</a>
									</li>
								</ul>

								<div class="tab-content">

									<!-- Inicio, lista Categorias -->
									<div id="tab-1"
										class="tab-pane <?php echo (isset($_GET['doc']) && ($_GET['doc'] == "Categoria") || !isset($_GET['doc'])) ? "active" : ""; ?>">
										<form class="form-horizontal">
											<div class="ibox" id="Categoria">
												<div class="ibox-title bg-success">
													<h5 class="collapse-link"><i class="fa fa-list"></i> Lista de
														Categorías</h5>
													<a class="collapse-link pull-right">
														<i class="fa fa-chevron-up"></i>
													</a>
												</div>
												<div class="ibox-content">
													<div class="row m-b-md">
														<div class="col-lg-12">
															<button class="btn btn-primary pull-right" type="button"
																onClick="CrearCampo('Categoria');"><i
																	class="fa fa-plus-circle"></i> Agregar
																nueva</button>
														</div>
													</div>
													<div class="table-responsive">
														<table
															class="table table-striped table-bordered table-hover dataTables-example">
															<thead>
																<tr>
																	<th>Nivel</th>
																	<th>Nombre Categoría</th>
																	<th>Categoría Padre</th>
																	<th>Perfiles</th>
																	<th>Comentarios</th>
																	<th>Fecha Actualizacion</th>
																	<th>Usuario Actualizacion</th>
																	<th>Estado</th>
																	<th>Tipo</th>
																	<th>Acciones</th>
																</tr>
															</thead>
															<tbody>
																<?php while ($row_Categoria = sqlsrv_fetch_array($SQL_Categorias)) { ?>
																	<tr>
																		<td>
																			<?php echo $row_Categoria['nivel']; ?>
																		</td>
																		<td>
																			<?php echo $row_Categoria['nombre_categoria']; ?>
																		</td>

																		<td>
																			<?php echo ($row_Categoria['categoria_padre'] == "") ? "[Raíz]" : $row_Categoria['categoria_padre']; ?>
																		</td>

																		<td>
																			<?php sqlsrv_fetch($SQL_Perfiles, SQLSRV_SCROLL_ABSOLUTE, -1); ?>
																			<?php $ids_perfiles = explode(";", $row_Categoria['perfiles']); ?>

																			<?php echo ($row_Categoria['perfiles'] == "") ? "(Todos)" : ""; ?>

																			<?php while ($row_Perfil = sqlsrv_fetch_array($SQL_Perfiles)) { ?>
																				<?php if (in_array($row_Perfil['ID_PerfilUsuario'], $ids_perfiles)) { ?>
																					<div style="margin: 10px !important;">
																						<p class="label label-secondary">
																							<?php echo $row_Perfil['PerfilUsuario']; ?>
																						</p>
																					</div>
																				<?php } ?>
																			<?php } ?>
																		</td>

																		<td>
																			<?php echo $row_Categoria['comentarios']; ?>
																		</td>

																		<td>
																			<?php echo isset($row_Categoria['fecha_actualizacion']) ? date_format($row_Categoria['fecha_actualizacion'], 'Y-m-d H:i:s') : ""; ?>
																		</td>
																		<td>
																			<?php echo $row_Categoria['usuario_actualizacion']; ?>
																		</td>
																		<td>
																			<span
																				class="label <?php echo ($row_Categoria['estado'] == "Y") ? "label-info" : "label-danger"; ?>">
																				<?php echo ($row_Categoria['estado'] == "Y") ? "Activo" : "Inactivo"; ?>
																			</span>
																		</td>
																		<td>
																			<span
																				class="label <?php echo ($row_Categoria['tipo'] == "A") ? "label-primary" : "label-warning"; ?>">
																				<?php echo ($row_Categoria['tipo'] == "A") ? "Archivo" : "Ruta"; ?>
																			</span>
																		</td>
																		<td>
																			<button type="button"
																				id="btnEdit<?php echo $row_Categoria['id']; ?>"
																				class="btn btn-success btn-xs"
																				onClick="EditarCampo('<?php echo $row_Categoria['id']; ?>','Categoria');"><i
																					class="fa fa-pencil"></i>
																				Editar</button>
																			<button type="button"
																				id="btnDelete<?php echo $row_Categoria['id']; ?>"
																				class="btn btn-danger btn-xs"
																				onClick="EliminarCampo('<?php echo $row_Categoria['id']; ?>','Categoria');"><i
																					class="fa fa-trash"></i>
																				Eliminar</button>
																		</td>
																	</tr>
																<?php } ?>
															</tbody>
														</table>
													</div>
												</div> <!-- ibox-content -->
											</div> <!-- ibox -->
										</form>
									</div>
									<!-- Fin, lista Categorias -->

									<!-- Inicio, lista Funciones -->
									<div id="tab-2"
										class="tab-pane <?php echo (isset($_GET['doc']) && ($_GET['doc'] == "Funcion")) ? "active" : ""; ?>">
										<form class="form-horizontal">
											<div class="ibox" id="Funcion">
												<div class="ibox-title bg-success">
													<h5 class="collapse-link"><i class="fa fa-list"></i> Lista de
														Funciones</h5>
													<a class="collapse-link pull-right">
														<i class="fa fa-chevron-up"></i>
													</a>
												</div>
												<div class="ibox-content">
													<div class="row m-b-md">
														<div class="col-lg-12">
															<button class="btn btn-primary pull-right" type="button"
																onClick="CrearCampo('Funcion');"><i
																	class="fa fa-plus-circle"></i> Agregar
																nueva</button>
														</div>
													</div>
													<div class="table-responsive">
														<table
															class="table table-striped table-bordered table-hover dataTables-example">
															<thead>
																<tr>
																	<th>Categoría</th>
																	<th>Ruta</th>
																	<th>Etiqueta</th>
																	<th>Perfiles</th>
																	<th>Comentarios</th>
																	<th>Fecha Actualizacion</th>
																	<th>Usuario Actualizacion</th>
																	<th>Estado</th>
																	<th>Acciones</th>
																</tr>
															</thead>
															<tbody>
																<?php while ($row_Funcion = sqlsrv_fetch_array($SQL_Funciones)) { ?>
																	<tr>
																		<td>
																			<?php echo $row_Funcion['categoria']; ?>
																		</td>
																		<td>
																			<?php echo $row_Funcion['ruta']; ?>
																		</td>
																		<td>
																			<?php echo $row_Funcion['etiqueta_consulta']; ?>
																		</td>

																		<td>
																			<?php sqlsrv_fetch($SQL_Perfiles, SQLSRV_SCROLL_ABSOLUTE, -1); ?>
																			<?php $ids_perfiles = explode(";", $row_Funcion['perfiles']); ?>

																			<?php echo ($row_Funcion['perfiles'] == "") ? "(Todos)" : ""; ?>

																			<?php while ($row_Perfil = sqlsrv_fetch_array($SQL_Perfiles)) { ?>
																				<?php if (in_array($row_Perfil['ID_PerfilUsuario'], $ids_perfiles)) { ?>
																					<div style="margin: 10px !important;">
																						<p class="label label-secondary">
																							<?php echo $row_Perfil['PerfilUsuario']; ?>
																						</p>
																					</div>
																				<?php } ?>
																			<?php } ?>
																		</td>

																		<td>
																			<?php echo $row_Funcion['comentarios']; ?>
																		</td>

																		<td>
																			<?php echo isset($row_Funcion['fecha_actualizacion']) ? date_format($row_Funcion['fecha_actualizacion'], 'Y-m-d H:i:s') : ""; ?>
																		</td>
																		<td>
																			<?php echo $row_Funcion['usuario_actualizacion']; ?>
																		</td>
																		<td>
																			<span
																				class="label <?php echo ($row_Funcion['estado'] == "Y") ? "label-info" : "label-danger"; ?>">
																				<?php echo ($row_Funcion['estado'] == "Y") ? "Activo" : "Inactivo"; ?>
																			</span>
																		</td>
																		<td>
																			<button type="button"
																				id="btnEdit<?php echo $row_Funcion['id']; ?>"
																				class="btn btn-success btn-xs"
																				onClick="EditarCampo('<?php echo $row_Funcion['id']; ?>','Funcion');"><i
																					class="fa fa-pencil"></i>
																				Editar</button>
																			<button type="button"
																				id="btnDelete<?php echo $row_Funcion['id']; ?>"
																				class="btn btn-danger btn-xs"
																				onClick="EliminarCampo('<?php echo $row_Funcion['id']; ?>','Funcion');"><i
																					class="fa fa-trash"></i>
																				Eliminar</button>
																		</td>
																	</tr>
																<?php } ?>
															</tbody>
														</table>
													</div>
												</div> <!-- ibox-content -->
											</div> <!-- ibox -->
										</form>
									</div>
									<!-- Fin, lista Funciones -->

									<!-- Inicio, lista Consultas -->
									<div id="tab-3"
										class="tab-pane <?php echo (isset($_GET['doc']) && ($_GET['doc'] == "Consulta")) ? "active" : ""; ?>">
										<?php include_once "jerarquias_equipos.php"; ?>
									</div>
									<!-- Fin, lista Consultas -->

								</div> <!-- tab-content -->
							</div> <!-- tabs-container -->
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
			$(".select2").select2();
			$('.i-checks').iCheck({
				checkboxClass: 'icheckbox_square-green',
				radioClass: 'iradio_square-green',
			});

			$('.dataTables-example').DataTable({
				pageLength: 10,
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
		});
	</script>

	<script>
		function CrearCampo(doc) {
			$('.ibox-content').toggleClass('sk-loading', true);

			$.ajax({
				type: "POST",
				url: "md_portal_Clientes.php",
				data: {
					doc: doc
				},
				success: function (response) {
					$('.ibox-content').toggleClass('sk-loading', false);
					$('#ContenidoModal').html(response);
					$('#myModal').modal("show");
				}
			});
		}

		function EditarCampo(id, doc) {
			$('.ibox-content').toggleClass('sk-loading', true);

			$.ajax({
				type: "POST",
				url: "md_portal_Clientes.php",
				data: {
					doc: doc,
					id: id,
					edit: 1
				},
				success: function (response) {
					$('.ibox-content').toggleClass('sk-loading', false);
					$('#ContenidoModal').html(response);
					$('#myModal').modal("show");
				}
			});
		}

		function EliminarCampo(id, doc) {
			Swal.fire({
				title: "¿Está seguro que desea eliminar este registro?",
				icon: "question",
				showCancelButton: true,
				confirmButtonText: "Si, confirmo",
				cancelButtonText: "No"
			}).then((result) => {
				if (result.isConfirmed) {
					// $('.ibox-content').toggleClass('sk-loading',true);

					$.ajax({
						type: "post",
						url: "parametros_portal_Clientes.php",
						data: { TipoDoc: doc, ID: id, Metodo: 3 },
						async: false,
						success: function (data) {
							// console.log(data);
							location.href = `parametros_portal_Clientes.php?doc=${doc}&a=<?php echo base64_encode("OK_PRDel"); ?>`;
						},
						error: function (error) {
							console.error("consulta erronea");
						}
					});
				}
			});

			return result;
		}
	</script>
	<!-- InstanceEndEditable -->

</body>

<!-- InstanceEnd -->

</html>
<?php sqlsrv_close($conexion); ?>