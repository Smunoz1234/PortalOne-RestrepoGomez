<?php require_once "includes/conexion.php";
PermitirAcceso(601);
$sw = 0;
//Estado actividad
$SQL_Estado = Seleccionar('uvw_tbl_EstadoDocSAP', '*');

$Filtro = ""; //Filtro
$WhereFecha = "";
//Fechas
if (isset($_GET['FechaInicial']) && $_GET['FechaInicial'] != "") {
    $FechaInicial = $_GET['FechaInicial'];
    $sw = 1;
} else {
    //Restar 7 dias a la fecha actual
    $fecha = date('Y-m-d');
    $nuevafecha = strtotime('-' . ObtenerVariable("DiasRangoFechasDocSAP") . ' day');
    $nuevafecha = date('Y-m-d', $nuevafecha);
    $FechaInicial = $nuevafecha;
//    $FechaInicial="";
}
if (isset($_GET['FechaFinal']) && $_GET['FechaFinal'] != "") {
    $FechaFinal = $_GET['FechaFinal'];
    $WhereFecha = "and (DocDate Between '$FechaInicial' and '$FechaFinal')";
} else {
    $FechaFinal = date('Y-m-d');
    $WhereFecha = "and (DocDate Between '$FechaInicial' and '$FechaFinal')";
//    $FechaFinal="";
}

//Filtros
if (isset($_GET['Estado']) && $_GET['Estado'] != "") {
    $Filtro .= " and Cod_Estado='" . $_GET['Estado'] . "'";
}

if (isset($_GET['BuscarDato']) && $_GET['BuscarDato'] != "") {
    $Filtro .= " and (DocNum LIKE '%" . $_GET['BuscarDato'] . "%' OR NombreContacto LIKE '%" . $_GET['BuscarDato'] . "%' OR DocNumLlamadaServicio LIKE '%" . $_GET['BuscarDato'] . "%' OR ID_LlamadaServicio LIKE '%" . $_GET['BuscarDato'] . "%' OR IdDocPortal LIKE '%" . $_GET['BuscarDato'] . "%' OR NombreEmpleadoVentas LIKE '%" . $_GET['BuscarDato'] . "%' OR Comentarios LIKE '%" . $_GET['BuscarDato'] . "%')";
}

$SQL = Seleccionar('uvw_Sap_tbl_OrdenesCompras', '*', "CardCode='" . $_SESSION['CodigoSAPProv'] . "' $WhereFecha $Filtro");

?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Ordenes de compra | <?php echo NOMBRE_PORTAL; ?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
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
                    <h2>Ordenes de compra</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Proveedores</a>
                        </li>
						<li>
                            <a href="#">Documentos</a>
                        </li>
                        <li class="active">
                            <strong>Ordenes de compra</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
			<div class="row">
				<div class="col-lg-12">
					<div class="ibox-content">
						 <?php include "includes/spinner.php";?>
					  <form action="prov_ordenes_compra.php" method="get" id="formBuscar" class="form-horizontal">
							<div class="form-group">
								<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
							</div>
							<div class="form-group">
								<label class="col-lg-1 control-label">Fechas</label>
								<div class="col-lg-3">
									<div class="input-daterange input-group" id="datepicker">
										<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial; ?>" autocomplete="off"/>
										<span class="input-group-addon">hasta</span>
										<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal; ?>" autocomplete="off" />
									</div>
								</div>
								<label class="col-lg-1 control-label">Estado</label>
								<div class="col-lg-2">
									<select name="Estado" class="form-control" id="Estado">
											<option value="">(Todos)</option>
									  <?php while ($row_Estado = sqlsrv_fetch_array($SQL_Estado)) {?>
											<option value="<?php echo $row_Estado['Cod_Estado']; ?>" <?php if ((isset($_GET['Estado'])) && (strcmp($row_Estado['Cod_Estado'], $_GET['Estado']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Estado['NombreEstado']; ?></option>
									  <?php }?>
									</select>
								</div>
								<label class="col-lg-1 control-label">Buscar dato</label>
								<div class="col-lg-3">
									<input name="BuscarDato" type="text" class="form-control" id="BuscarDato" maxlength="100" value="<?php if (isset($_GET['BuscarDato']) && ($_GET['BuscarDato'] != "")) {echo $_GET['BuscarDato'];}?>">
								</div>
								<div class="col-lg-1">
									<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
								</div>
							</div>
					 </form>
				</div>
			</div>
		  </div>
		  <br>
          <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
			<div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example" >
                    <thead>
                    <tr>
                        <th>Orden de compra</th>
						<th>Fecha orden</th>
						<th>Proyecto</th>
                        <th>Valor</th>
						<th>Estado</th>
						<th>Acción</th>
                    </tr>
                    </thead>
                    <tbody>
					   <?php while ($row = sqlsrv_fetch_array($SQL)) {?>
						<tr class="odd gradeX">
							<td><?php echo $row['DocNum']; ?></td>
							<td><?php echo $row['DocDate']; ?></td>
							<td><?php //echo $row['PrjName'];?></td>
							<td align="right"><?php echo "$ " . number_format($row['DocTotal'], 2); ?></td>
							<td><span <?php if ($row['Cod_Estado'] == 'O') {echo "class='label label-info'";} else {echo "class='label label-danger'";}?>><?php echo $row['NombreEstado']; ?></span></td>
							<td><a href="prov_detalle_orden_compra.php?id=<?php echo base64_encode($row['ID_OrdenCompra']); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('prov_ordenes_compra.php'); ?>" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a> <?php if (PermitirFuncion(602)) {?><a href="sapdownload.php?id=<?php echo base64_encode('15'); ?>&type=<?php echo base64_encode('2'); ?>&DocKey=<?php echo base64_encode($row['ID_OrdenCompra']); ?>&ObType=<?php echo base64_encode('22'); ?>&IdFrm=<?php echo base64_encode($row['IdSeries']); ?>" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-download"></i> Descargar</a><?php }?></td>
						</tr>
						<?php }?>
					</tbody>
                    </table>
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
			$("#formBuscar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
			 $(".alkin").on('click', function(){
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
            $('.dataTables-example').DataTable({
                pageLength: 10,
                responsive: true,
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
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>