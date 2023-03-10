<?php
require_once "includes/conexion.php";
PermitirAcceso(216);

$sw_error = 0;

if (isset($_POST['Metodo']) && ($_POST['Metodo'] == 3)) {
    try {

        if ($_POST['TipoDoc'] == "Concepto") {
            $Param = array(
                $_POST['Metodo'], // 3 - Eliminar
                isset($_POST['id']) ? $_POST['id'] : "NULL",
            );
            $SQL = EjecutarSP('sp_tbl_SalidaInventario_Conceptos', $Param);
            if (!$SQL) {
                $sw_error = 1;
                $msg_error = "No se pudo eliminar el registro";
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

        if ($_POST['TipoDoc'] == "Concepto") {
            $Param = array(
                $_POST['Metodo'] ?? 1, // 1 - Crear, 2 - Actualizar
                "'" . $_POST['id_concepto_salida'] . "'",
                "'" . $_POST['concepto_salida'] . "'",
                "'" . $_POST['id_cc'] . "'", // id_cuenta_contable
                "'" . $_POST['cc'] . "'", // cuenta_contable
                "'" . $_POST['estado'] . "'",
            );
            $SQL = EjecutarSP('sp_tbl_SalidaInventario_Conceptos', $Param);
            if (!$SQL) {
                $sw_error = 1;
                $msg_error = "No se pudo insertar los datos";
            }
        }

        if ($sw_error == 0) {
            $TipoDoc = $_POST['TipoDoc'];
            header("Location:parametros_conceptos_salida.php?doc=$TipoDoc&a=" . base64_encode("OK_PRUpd") . "#$TipoDoc");
        }

    } catch (Exception $e) {
        $sw_error = 1;
        $msg_error = $e->getMessage();
    }

}

$SQL_ConceptosSalida = Seleccionar("tbl_SalidaInventario_Conceptos", "*");
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Parámetros asistente de socios de negocio | <?php echo NOMBRE_PORTAL; ?></title>
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
                    <h2>Parámetros de Concepto de salida de inventario</h2>
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
                            <strong>Parámetros de Concepto de salida de inventario</strong>
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
								<li class="<?php echo (isset($_GET['doc']) && ($_GET['doc'] == "Concepto") || !isset($_GET['doc'])) ? "active" : ""; ?>">
									<a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Lista de Conceptos de Salida</a>
								</li>
							</ul>
							<div class="tab-content">
								<!-- Inicio, lista Conceptos de Salida -->
								<div id="tab-1" class="tab-pane <?php echo (isset($_GET['doc']) && ($_GET['doc'] == "Concepto") || !isset($_GET['doc'])) ? "active" : ""; ?>">
									<form class="form-horizontal">
										<!-- Inicio, ibox Conceptos -->
										<div class="ibox" id="Concepto">
											<div class="ibox-title bg-success">
												<h5 class="collapse-link"><i class="fa fa-list"></i> Lista de Conceptos de Salida</h5>
												 <a class="collapse-link pull-right">
													<i class="fa fa-chevron-up"></i>
												</a>
											</div>
											<div class="ibox-content">
												<div class="row m-b-md">
													<div class="col-lg-12">
														<button class="btn btn-primary pull-right" type="button" id="NewConcepto" onClick="CrearCampo('Concepto');"><i class="fa fa-plus-circle"></i> Agregar nuevo</button>
													</div>
												</div>
												<div class="table-responsive">
													<table class="table table-striped table-bordered table-hover dataTables-example">
														<thead>
															<tr>
																<th>ID Concepto Salida</th>
																<th>Concepto Salida</th>
																<th>ID Cuenta Contable</th>
																<th>Cuenta Contable</th>
																<th>Estado</th>
																<th>Acciones</th>
															</tr>
														</thead>
														<tbody>
															 <?php while ($row_ConceptosSalida = sqlsrv_fetch_array($SQL_ConceptosSalida)) {?>
															<tr>
																<td><?php echo $row_ConceptosSalida['id_concepto_salida']; ?></td>
																<td><?php echo $row_ConceptosSalida['concepto_salida']; ?></td>
																<td><?php echo $row_ConceptosSalida['id_cuenta_contable']; ?></td>
																<td><?php echo $row_ConceptosSalida['cuenta_contable']; ?></td>

																<td>
																	<span class="label <?php echo ($row_ConceptosSalida['estado'] == "Y") ? "label-info" : "label-danger"; ?>">
																		<?php echo ($row_ConceptosSalida['estado'] == "Y") ? "Activo" : "Inactivo"; ?>
																	</span>
																</td>
																<td>
																	<button type="button" id="btnEdit<?php echo $row_ConceptosSalida['id']; ?>" class="btn btn-success btn-xs" onClick="EditarCampo('<?php echo $row_ConceptosSalida['id_concepto_salida']; ?>','Concepto');"><i class="fa fa-pencil"></i> Editar</button>
																	<button type="button" id="btnDelete<?php echo $row_ConceptosSalida['id']; ?>" class="btn btn-danger btn-xs" onClick="EliminarCampo('<?php echo $row_ConceptosSalida['id_concepto_salida']; ?>','Concepto');"><i class="fa fa-trash"></i> Eliminar</button>
																</td>
															</tr>
															 <?php }?>
														</tbody>
													</table>
												</div>
											</div>
										</div>
										<!-- Fin, ibox Conceptos -->
									</form>
								</div>
								<!-- Fin, lista Conceptos de Salida -->
							</div>
						 </div>
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
		url: "md_parametros_conceptos_salida.php",
		data:{
			doc:doc
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		},
		error: function(error) {
			console.error("consulta erronea, crear");
		}
	});
}
function EditarCampo(id, doc){
	$('.ibox-content').toggleClass('sk-loading',true);

	$.ajax({
		type: "POST",
		url: "md_parametros_conceptos_salida.php",
		data:{
			doc:doc,
			id:id,
			edit:1
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		},
		error: function(error) {
			console.error("consulta erronea, editar");
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
			//$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "post",
				url: "parametros_conceptos_salida.php",
				data: {
					TipoDoc: doc,
					id: id,
					Metodo: 3
					 },
				async: false,
				success: function(data){
					console.log(data);
					location.href = "parametros_conceptos_salida.php?a=<?php echo base64_encode("OK_PRDel"); ?>";
				},
				error: function(error) {
					console.error("consulta erronea, eliminar");
				}
			});
		}
	});
}
</script>

<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>