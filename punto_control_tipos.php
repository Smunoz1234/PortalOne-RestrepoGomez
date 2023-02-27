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

        if ($_POST['TipoDoc'] == "Familia") {
            $SQL = EjecutarSP('sp_tbl_IconosSAPB1_Familias', $Param);
            if (!$SQL) {
                $sw_error = 1;
                $msg_error = "No se pudo eliminar la Categoría.";
            }
        } elseif ($_POST['TipoDoc'] == "Icono") {
            $SQL = EjecutarSP('sp_tbl_IconosSAPB1_Iconos', $Param);
            if (!$SQL) {
                $sw_error = 1;
                $msg_error = "No se pudo eliminar la Icono SAP B1.";
            }
        } elseif ($_POST['TipoDoc'] == "Tipo") {
            $SQL = EjecutarSP('sp_tbl_IconosSAPB1_Tipos', $Param);
            if (!$SQL) {
                $sw_error = 1;
                $msg_error = "No se pudo eliminar la Tipo.";
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

        if ($_POST['TipoDoc'] == "Familia") {
            $FechaHora = "'" . FormatoFecha(date('Y-m-d'), date('H:i:s')) . "'";
            $Usuario = "'" . $_SESSION['CodUser'] . "'";

            $Perfiles = implode(";", $_POST['Perfiles']);
            $Perfiles = count($_POST['Perfiles']) > 0 ? "'$Perfiles'" : "''";

            $ID = (isset($_POST['ID_Actual']) && ($_POST['ID_Actual'] != "")) ? $_POST['ID_Actual'] : "NULL";

            $Param = array(
                $_POST['Metodo'] ?? 1, // 1 - Crear, 2 - Actualizar
                $ID,
                "'" . $_POST['ID_FamiliaPadre'] . "'",
                "'" . $_POST['NombreFamilia'] . "'",
                $Perfiles,
                "'" . $_POST['Comentarios'] . "'",
                "'" . $_POST['Estado'] . "'",
                $Usuario, // @id_usuario_actualizacion
                $FechaHora, // @fecha_actualizacion
                $FechaHora, // @hora_actualizacion
                ($_POST['Metodo'] == 1) ? $Usuario : "NULL",
                ($_POST['Metodo'] == 1) ? $FechaHora : "NULL",
                ($_POST['Metodo'] == 1) ? $FechaHora : "NULL",
            );

            $SQL = EjecutarSP('sp_tbl_IconosSAPB1_Familias', $Param);
            if (!$SQL) {
                $sw_error = 1;
                $msg_error = "No se pudo insertar la nueva Categoría";
            }
        } elseif ($_POST['TipoDoc'] == "Icono") {
            $FechaHora = "'" . FormatoFecha(date('Y-m-d'), date('H:i:s')) . "'";
            $Usuario = "'" . $_SESSION['CodUser'] . "'";

            $Perfiles = implode(";", $_POST['Perfiles']);
            $Perfiles = count($_POST['Perfiles']) > 0 ? "'$Perfiles'" : "''";

            $ID = (isset($_POST['ID_Actual']) && ($_POST['ID_Actual'] != "")) ? $_POST['ID_Actual'] : "NULL";

            $Param = array(
                $_POST['Metodo'] ?? 1, // 1 - Crear, 2 - Actualizar
                $ID,
                "'" . $_POST['ID_Familia'] . "'",
                "'" . $_POST['ProcedimientoIcono'] . "'",
                "'" . $_POST['EtiquetaIcono'] . "'",
                "'" . $_POST['ParametrosTipo'] . "'",
                $Perfiles,
                "'" . $_POST['Comentarios'] . "'",
                "'" . $_POST['Estado'] . "'",
                $Usuario, // @id_usuario_actualizacion
                $FechaHora, // @fecha_actualizacion
                $FechaHora, // @hora_actualizacion
                ($_POST['Metodo'] == 1) ? $Usuario : "NULL",
                ($_POST['Metodo'] == 1) ? $FechaHora : "NULL",
                ($_POST['Metodo'] == 1) ? $FechaHora : "NULL",
            );

            $SQL = EjecutarSP('sp_tbl_IconosSAPB1_Iconos', $Param);
            if (!$SQL) {
                $sw_error = 1;
                $msg_error = "No se pudo insertar la nueva Icono SAP B1";
            }
        } elseif ($_POST['TipoDoc'] == "Tipo") {
            $FechaHora = "'" . FormatoFecha(date('Y-m-d'), date('H:i:s')) . "'";
            $Usuario = "'" . $_SESSION['CodUser'] . "'";

            $ID = (isset($_POST['ID_Actual']) && ($_POST['ID_Actual'] != "")) ? $_POST['ID_Actual'] : "NULL";

            $Param = array(
                $_POST['Metodo'] ?? 1, // 1 - Crear, 2 - Actualizar
                $ID,
                "'" . $_POST['ID_Icono'] . "'",
                "'" . $_POST['ParametroTipo'] . "'",
                "'" . $_POST['EtiquetaTipo'] . "'",
                "'" . $_POST['Obligatorio'] . "'",
                "'" . $_POST['Estado'] . "'",
                "'" . $_POST['TipoCampo'] . "'",
                "'" . $_POST['Multiple'] . "'",
                "'" . $_POST['PermitirTodos'] . "'",
                "'" . $_POST['VistaLista'] . "'",
                "'" . $_POST['EtiquetaLista'] . "'",
                "'" . $_POST['ValorLista'] . "'",
                "'" . $_POST['Comentarios'] . "'",
                $Usuario, // @id_usuario_actualizacion
                $FechaHora, // @fecha_actualizacion
                $FechaHora, // @hora_actualizacion
                ($_POST['Metodo'] == 1) ? $Usuario : "NULL",
                ($_POST['Metodo'] == 1) ? $FechaHora : "NULL",
                ($_POST['Metodo'] == 1) ? $FechaHora : "NULL",
            );

            $SQL = EjecutarSP('sp_tbl_IconosSAPB1_Tipos', $Param);
            $row = sqlsrv_fetch_array($SQL);

            if (!$SQL) {
                $sw_error = 1;
                $msg_error = "No se pudo insertar la nueva Tipo";
            } elseif (isset($row['Error'])) {
                $sw_error = 1;
                $msg_error = $row['Error'];
            }
        }

        // OK
        if ($sw_error == 0) {
            $TipoDoc = $_POST['TipoDoc'];
            header("Location:parametros_Iconos_sap.php?doc=$TipoDoc&a=" . base64_encode("OK_PRUpd") . "#$TipoDoc");
        }

    } catch (Exception $e) {
        $sw_error = 1;
        $msg_error = $e->getMessage();
    }

}

// SMM, 27/02/2023
$SQL_Familias = Seleccionar("tbl_PuntoControl", "*");
$SQL_Iconos = Seleccionar("tbl_PuntoControl_Iconos", "*");
$SQL_Tipos = Seleccionar("tbl_PuntoControl_Tipos", "*");
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Tipos de puntos de control | <?php echo NOMBRE_PORTAL; ?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<style>
	.ibox-title a{
		color: inherit !important;
	}
	.collapse-link:hover{
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

    <?php include_once "includes/menu.php";?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once "includes/menu_superior.php";?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Tipos de puntos de control</h2>
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
                            <strong>Tipos de puntos de control</strong>
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
						<?php include "includes/spinner.php";?>

						<div class="tabs-container">

						 	<ul class="nav nav-tabs">
								<li class="<?php echo (isset($_GET['doc']) && ($_GET['doc'] == "Familia") || !isset($_GET['doc'])) ? "active" : ""; ?>">
									<a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Familia de plagas</a>
								</li>
								<li class="<?php echo (isset($_GET['doc']) && ($_GET['doc'] == "Icono")) ? "active" : ""; ?>">
									<a data-toggle="tab" href="#tab-2"><i class="fa fa-list"></i> Iconos de puntos de control</a>
								</li>
								<li class="<?php echo (isset($_GET['doc']) && ($_GET['doc'] == "Tipo")) ? "active" : ""; ?>">
									<a data-toggle="tab" href="#tab-3"><i class="fa fa-list"></i> Tipos de puntos de control</a>
								</li>
							</ul>

							<div class="tab-content">

								<!-- Inicio, lista Familias -->
								<div id="tab-1" class="tab-pane <?php echo (isset($_GET['doc']) && ($_GET['doc'] == "Familia") || !isset($_GET['doc'])) ? "active" : ""; ?>">
									<form class="form-horizontal">
										<div class="ibox" id="Familia">
											<div class="ibox-title bg-success">
												<h5 class="collapse-link"><i class="fa fa-list"></i> Lista de Familias</h5>
												 <a class="collapse-link pull-right">
													<i class="fa fa-chevron-up"></i>
												</a>
											</div>
											<div class="ibox-content">
												<div class="row m-b-md">
													<div class="col-lg-12">
														<button class="btn btn-primary pull-right" type="button" onClick="CrearCampo('Familia');"><i class="fa fa-plus-circle"></i> Agregar nueva</button>
													</div>
												</div>
												<div class="table-responsive">
													<table class="table table-striped table-bordered table-hover dataTables-example">
														<thead>
															<tr>
																<th>ID Punto Control</th>
																<th>Punto Control</th>
																<th>Descripción Punto Control</th>
																<th>ID Tipo Punto Control</th>
																<th>Socio Negocio</th>
																<th>ID Dirección Destino</th>
																<th>Dirección Destino</th>
																<th>ID Zona</th>
																<th>Zona</th>
																<th>Estado</th>
																<th>Fecha Actualización</th>
																<th>Usuario Actualización</th>
																<th>Acciones</th>
															</tr>
														</thead>
														<tbody>
															 <?php while ($row_Familia = sqlsrv_fetch_array($SQL_Familias)) {?>
															<tr>
																<td><?php echo $row['id_punto_control']; ?></td>
																<td><?php echo $row['punto_control']; ?></td>
																<td><?php echo $row['descripcion_punto_control']; ?></td>
																<td><?php echo $row['id_tipo_punto_control']; ?></td>
																<td><?php echo $row['socio_negocio']; ?></td>
																<td><?php echo $row['id_direccion_destino']; ?></td>
																<td><?php echo $row['direccion_destino']; ?></td>
																<td><?php echo $row['id_zona_sn']; ?></td>
																<td><?php echo $row['zona_sn']; ?></td>

																<td>
																	<span class="badge <?php echo ($row['estado'] == "Y") ? "badge-primary" : "badge-danger"; ?>">
																		<?php echo ($row['estado'] == "Y") ? "Activo" : "Inactivo"; ?>
																	</span>
																</td>

																<td><?php echo isset($row['fecha_actualizacion']) ? date_format($row['fecha_actualizacion'], 'Y-m-d H:i:s') : ""; ?></td>
																<td><?php echo $row['usuario_actualizacion']; ?></td>

																<td>
																	<button type="button" id="btnEdit<?php echo $row_Familia['ID']; ?>" class="btn btn-success btn-xs" onClick="EditarCampo('<?php echo $row_Familia['ID']; ?>','Familia');"><i class="fa fa-pencil"></i> Editar</button>
																	<button type="button" id="btnDelete<?php echo $row_Familia['ID']; ?>" class="btn btn-danger btn-xs" onClick="EliminarCampo('<?php echo $row_Familia['ID']; ?>','Familia');"><i class="fa fa-trash"></i> Eliminar</button>
																</td>
															</tr>
															 <?php }?>
														</tbody>
													</table>
												</div>
											</div> <!-- ibox-content -->
										</div> <!-- ibox -->
									</form>
								</div>
								<!-- Fin, lista Familias -->

								<!-- Inicio, lista Iconos -->
								<div id="tab-2" class="tab-pane <?php echo (isset($_GET['doc']) && ($_GET['doc'] == "Icono")) ? "active" : ""; ?>">
									<form class="form-horizontal">
										<div class="ibox" id="Icono">
											<div class="ibox-title bg-success">
												<h5 class="collapse-link"><i class="fa fa-list"></i> Lista de Iconos</h5>
												 <a class="collapse-link pull-right">
													<i class="fa fa-chevron-up"></i>
												</a>
											</div>
											<div class="ibox-content">
												<div class="row m-b-md">
													<div class="col-lg-12">
														<button class="btn btn-primary pull-right" type="button" onClick="CrearCampo('Icono');"><i class="fa fa-plus-circle"></i> Agregar nueva</button>
													</div>
												</div>
												<div class="table-responsive">
													<table class="table table-striped table-bordered table-hover dataTables-example">
														<thead>
															<tr>
																<th>ID Icono</th>
																<th>Icono</th>
																<th>Fecha Actualizacion</th>
																<th>Usuario Actualizacion</th>
																<th>Acciones</th>
															</tr>
														</thead>
														<tbody>
															 <?php while ($row_Icono = sqlsrv_fetch_array($SQL_Iconos)) {?>
															<tr>
																<td><?php echo $row_Icono['ID Icono']; ?></td>
																<td><?php echo $row_Icono['Icono']; ?></td>

																<td><?php echo isset($row['fecha_actualizacion']) ? date_format($row['fecha_actualizacion'], 'Y-m-d H:i:s') : ""; ?></td>
																<td><?php echo $row['usuario_actualizacion']; ?></td>

																<td>
																	<button type="button" id="btnEdit<?php echo $row_Icono['ID']; ?>" class="btn btn-success btn-xs" onClick="EditarCampo('<?php echo $row_Icono['ID']; ?>','Icono');"><i class="fa fa-pencil"></i> Editar</button>
																	<button type="button" id="btnDelete<?php echo $row_Icono['ID']; ?>" class="btn btn-danger btn-xs" onClick="EliminarCampo('<?php echo $row_Icono['ID']; ?>','Icono');"><i class="fa fa-trash"></i> Eliminar</button>
																</td>
															</tr>
															 <?php }?>
														</tbody>
													</table>
												</div>
											</div> <!-- ibox-content -->
										</div> <!-- ibox -->
									</form>
								</div>
								<!-- Fin, lista Iconos -->

								<!-- Inicio, lista Tipos -->
								<div id="tab-3" class="tab-pane <?php echo (isset($_GET['doc']) && ($_GET['doc'] == "Tipo")) ? "active" : ""; ?>">
									<form class="form-horizontal">
										<div class="ibox" id="Tipo">
											<div class="ibox-title bg-success">
												<h5 class="collapse-link"><i class="fa fa-list"></i> Lista de Tipos</h5>
												 <a class="collapse-link pull-right">
													<i class="fa fa-chevron-up"></i>
												</a>
											</div>
											<div class="ibox-content">
												<div class="row m-b-md">
													<div class="col-lg-12">
														<button class="btn btn-primary pull-right" type="button" onClick="CrearCampo('Tipo');"><i class="fa fa-plus-circle"></i> Agregar nueva</button>
													</div>
												</div>
												<div class="table-responsive">
													<table class="table table-striped table-bordered table-hover dataTables-example">
														<thead>
															<tr>
																<th>Icono SAP B1</th>
																<th>Parámetro de Tipo</th>
																<th>Etiqueta</th>
																<th>Tipo de Campo</th>
																<th>Vista de referencia</th>
																<th>Obligatorio</th>
																<th>Multiple</th>
																<th>Estado</th>
																<th>Fecha Actualizacion</th>
																<th>Usuario Actualizacion</th>
																<th>Acciones</th>
															</tr>
														</thead>
														<tbody>
															 <?php while ($row_Tipo = sqlsrv_fetch_array($SQL_Tipos)) {?>
															<tr>
																<td><?php echo $row['id_tipo_punto_control']; ?></td>
																<td><?php echo $row['tipo_punto_control']; ?></td>
																<td><?php echo $row['id_familia_plaga']; ?></td>
																<td><?php echo $row['familia_plaga']; ?></td>
																<td><?php echo $row['descripcion']; ?></td>

																<td>
																	<span class="badge <?php echo ($row['estado'] == "Y") ? "badge-primary" : "badge-danger"; ?>">
																		<?php echo ($row['estado'] == "Y") ? "Activo" : "Inactivo"; ?>
																	</span>
																</td>

																<td><?php echo $row['id_icono']; ?></td>
																<td><?php echo $row['id_color']; ?></td>
																<td><?php echo $row['id_clase_control']; ?></td>
																<td><?php echo $row['clase_control']; ?></td>
																<td><?php echo $row['codigo_prefijo']; ?></td>

																<td><?php echo isset($row['fecha_actualizacion']) ? date_format($row['fecha_actualizacion'], 'Y-m-d H:i:s') : ""; ?></td>
																<td><?php echo $row['usuario_actualizacion']; ?></td>

																<td>
																	<button type="button" id="btnEdit<?php echo $row_Tipo['ID']; ?>" class="btn btn-success btn-xs" onClick="EditarCampo('<?php echo $row_Tipo['ID']; ?>','Tipo');"><i class="fa fa-pencil"></i> Editar</button>
																	<button type="button" id="btnDelete<?php echo $row_Tipo['ID']; ?>" class="btn btn-danger btn-xs" onClick="EliminarCampo('<?php echo $row_Tipo['ID']; ?>','Tipo');"><i class="fa fa-trash"></i> Eliminar</button>
																</td>
															</tr>
															 <?php }?>
														</tbody>
													</table>
												</div>
											</div> <!-- ibox-content -->
										</div> <!-- ibox -->
									</form>
								</div>
								<!-- Fin, lista Tipos -->

							</div> <!-- tab-content -->
						</div> <!-- tabs-container -->
					</div>
          		</div>
			 </div>

        </div>
        <!-- InstanceEndEditable -->
        <?php include_once "includes/footer.php";?>

    </div>
</div>
<?php include_once "includes/pie.php";?>
<!-- InstanceBeginEditable name="EditRegion4" -->

<script>
	$(document).ready(function(){
		$(".select2").select2();
		$('.i-checks').iCheck({
				checkboxClass: 'icheckbox_square-green',
				radioClass: 'iradio_square-green',
			});

		$('.dataTables-example').DataTable({
			pageLength: 10,
			dom: '<"html5buttons"B>lTfgitp',
			language: {
				"decimal":        "",
				"emptyTable":     "No se encontraron resultados.",
				"info":           "Mostrando _START_ - _END_ de _TOTAL_ registros",
				"infoEmpty":      "Mostrando 0 - 0 de 0 registros",
				"infoFiltered":   "(filtrando de _MAX_ registros)",
				"infoPostFix":    "",
				"thousands":      ",",
				"lengthMenu":     "Mostrar _MENU_ registros",
				"loadingRecords": "Cargando...",
				"processing":     "Procesando...",
				"search":         "Filtrar:",
				"zeroRecords":    "Ningún registro encontrado",
				"paginate": {
					"first":      "Primero",
					"last":       "Último",
					"next":       "Siguiente",
					"previous":   "Anterior"
				},
				"aria": {
					"sortAscending":  ": Activar para ordenar la columna ascendente",
					"sortDescending": ": Activar para ordenar la columna descendente"
				}
			},
			buttons: []
		});
	});
</script>

<script>
function CrearCampo(doc){
	$('.ibox-content').toggleClass('sk-loading',true);

	$.ajax({
		type: "POST",
		url: "md_punto_control_tipos.php",
		data:{
			doc:doc
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		}
	});
}

function EditarCampo(id, doc){
	$('.ibox-content').toggleClass('sk-loading',true);

	$.ajax({
		type: "POST",
		url: "md_punto_control_tipos.php",
		data:{
			doc:doc,
			id:id,
			edit:1
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		}
	});
}

function EliminarCampo(id, doc){
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
				url: "parametros_Iconos_sap.php",
				data: { TipoDoc: doc, ID: id, Metodo: 3 },
				async: false,
				success: function(data){
					// console.log(data);
					location.href = `parametros_Iconos_sap.php?doc=${doc}&a=<?php echo base64_encode("OK_PRDel"); ?>`;
				},
				error: function(error) {
					console.error("Icono erronea");
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
<?php sqlsrv_close($conexion);?>
