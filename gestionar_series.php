<?php
require_once "includes/conexion.php";
PermitirAcceso(219);
error_reporting(E_ALL ^ E_WARNING);

$sw_error = 0;
$msg_error = "";

// Crear nuevo parametro
if (isset($_POST['MM_Insert']) && ($_POST['MM_Insert'] != "")) {

    $Param = array(
        "'" . $_POST['type'] . "'",
        "'" . $_POST['ID'] . "'",
        "'" . $_POST['SerieDoc'] . "'",
        "'" . $_POST['TipoDoc'] . "'",
        "'" . $_POST['IdSucursal'] . "'",
        "'" . $_POST['WhsCode'] . "'",
        "'" . $_POST['ToWhsCode'] . "'",
        "'" . $_POST['IdBodegaDefecto'] . "'",
    );
    $SQL = EjecutarSP('sp_tbl_SeriesSucursalesAlmacenes', $Param);
    if ($SQL) {
        $a = ($_POST['type'] == 1) ? "OK_NewParam" : "OK_UpdParam";
        header('Location:gestionar_series.php?a=' . base64_encode($a));
    } else {
        $sw_error = 1;
        $msg_error = "No se pudo insertar el nuevo registro";
    }
}

$SQL = Seleccionar("uvw_tbl_SeriesSucursalesAlmacenes", "*");
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Gestionar series | <?php echo NOMBRE_PORTAL; ?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.swal2-container {
	  	z-index: 9000;
	}
	.easy-autocomplete {
		 width: 100% !important
	}
</style>
<?php
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_NewParam"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El nuevo registro ha sido agregado exitosamente.',
                icon: 'success'
            });
		});
		</script>";
}
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_UpdParam"))) {
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
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_DelReg"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El registro ha sido eliminado exitosamente.',
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
                    <h2>Gestionar series</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
						<li>
                            <a href="#">Administración</a>
                        </li>
                        <li class="active">
                            <strong>Gestionar series</strong>
                        </li>
                    </ol>
                </div>
            </div>
            <?php //echo $Cons;?>
         <div class="wrapper wrapper-content">
			 <div class="modal inmodal fade" id="myModal" tabindex="1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content" id="ContenidoModal">

					</div>
				</div>
			 </div>
			 <form action="" method="post" id="frmParam" class="form-horizontal">
			 <div class="row">
				<div class="col-lg-12">
					<div class="ibox-content">
						<?php include "includes/spinner.php";?>
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-plus-square"></i> Acciones</h3></label>
						</div>
						<div class="form-group">
							<div class="col-lg-6">
								<button class="btn btn-primary" type="button" id="NewParam" onClick="CrearCampo();"><i class="fa fa-plus-circle"></i> Crear nuevo registro</button>
							</div>
						</div>
					  	<input type="hidden" id="P" name="P" value="frmParam" />
					</div>
				</div>
			 </div>
			 <br>
			 <div class="row">
			 	<div class="col-lg-12">
					<div class="ibox-content">
						<?php include "includes/spinner.php";?>
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-check-square-o"></i> Lista de formatos</h3></label>
						</div>
						<div class="table-responsive">
							<table class="table table-bordered table-hover dataTables-example" >
							<thead>
							<tr>
								<th>#</th>
								<th>ID tipo documento</th>
								<th>Tipo documento</th>
								<th>ID Serie (Formato)</th>
								<th>Nombre Serie</th>
								<th>Dimensión 1</th>
								<th>Almacén origen</th>
								<th>Almacén destino</th>
								<th>Almacén defecto</th>
								<th>Acciones</th>
							</tr>
							</thead>

							<tbody>
								<?php while ($row = sqlsrv_fetch_array($SQL)) {?>
									<tr class="gradeX">
										<td><?php echo $row['ID']; ?></td>
										<td><?php echo $row['IdTipoDocumento']; ?></td>
										<td><?php echo $row['DeTipoDocumento']; ?></td>
										<td><?php echo $row['IdSeries']; ?></td>
										<td><?php echo $row['DeSeries']; ?></td>
										<td><?php echo $row['IdSucursal'] . " - " . $row['DeSucursal']; ?></td>
										<td><?php echo $row['WhsCode'] . " - " . $row['WhsName']; ?></td>
										<td><?php echo $row['ToWhsCode'] . " - " . $row['ToWhsName']; ?></td>
										<td><?php echo $row['IdBodegaDefecto'] . " - " . $row['DeBodegaDefecto']; ?></td>

										<td>
											<button type="button" id="btnEdit<?php echo $row['ID']; ?>" class="btn btn-success btn-xs" onClick="EditarCampo('<?php echo $row['ID']; ?>');"><i class="fa fa-pencil"></i> Editar</button>
											<button type="button" id="btnDel<?php echo $row['ID']; ?>" class="btn btn-danger btn-xs" onClick="BorrarLinea('<?php echo $row['ID']; ?>');"><i class="fa fa-trash"></i> Eliminar</button>
										</td>
									</tr>
								<?php }?>
							</tbody>
							</table>
					  </div>
					</div>
          		</div>
			 </div>
		</form>
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once "includes/footer.php";?>

    </div>
</div>
<?php include_once "includes/pie.php";?>
<!-- InstanceBeginEditable name="EditRegion4" -->
 <script>
	$(document).ready(function(){
		$("#frmParam").validate({
		 submitHandler: function(form){
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
			}
		});

		$(".select2").select2();
		$('.i-checks').iCheck({
			 checkboxClass: 'icheckbox_square-green',
			 radioClass: 'iradio_square-green',
		  });

		<?php if (isset($_GET['doc'])) {?>
		$("#TipoDocumento").trigger('change');
		<?php }?>

		 $('.dataTables-example').DataTable({
			pageLength: 25,
			dom: '<"html5buttons"B>lTfgitp',
			rowGroup: {
				dataSrc: [2]
			},
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
			, order: [[ 1, "asc" ]]
			, ordering: false
		});
	});
</script>
<script>
function CrearCampo(){
	$('.ibox-content').toggleClass('sk-loading',true);

	$.ajax({
		type: "POST",
		url: "md_gestionar_series.php",
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		}
	});
}

function EditarCampo(id){
	$('.ibox-content').toggleClass('sk-loading',true);

	$.ajax({
		type: "POST",
		url: "md_gestionar_series.php",
		data:{
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

function BorrarLinea(id){
	Swal.fire({
		title: "¿Está seguro que desea eliminar este registro?",
		text: "Este proceso no se puede revertir",
		icon: "warning",
		showCancelButton: true,
		confirmButtonText: "Si, confirmo",
		cancelButtonText: "No"
	}).then((result) => {
		if (result.isConfirmed) {
			$.ajax({
				type: "GET",
				url: "includes/procedimientos.php?type=66&linenum="+id,
				success: function(response){
					location.href = "gestionar_series.php?a=<?php echo base64_encode("OK_DelReg"); ?>";
				},
				error: function(error) {
					console.error("consulta erronea");
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