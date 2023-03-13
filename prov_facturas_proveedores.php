<?php require_once "includes/conexion.php";
PermitirAcceso(601);

$sw = 0;
$FilEstado = 0;
$Filtro = "";
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
    $WhereFecha = "and Canceled = 'N' and (DocDate Between '$FechaInicial' and '$FechaFinal')";
} else {
    $FechaFinal = date('Y-m-d');
    $WhereFecha = "and Canceled = 'N' and (DocDate Between '$FechaInicial' and '$FechaFinal')";
//    $FechaFinal="";
}

if (isset($_GET['Estado'])) {
    $FilEstado = $_GET['Estado'];
} else {
    $FilEstado = 0;
}

$Where = "CardCode='" . $_SESSION['CodigoSAPProv'] . "' $WhereFecha $Filtro";
// echo "SELECT * FROM uvw_Sap_tbl_FacturasCompras WHERE $Where";
$SQL = Seleccionar('uvw_Sap_tbl_FacturasCompras', '*', $Where);
$SQLCons = ReturnCons('uvw_Sap_tbl_FacturasCompras', '*', $Where);
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Facturas de proveedor | <?php echo NOMBRE_PORTAL; ?></title>
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
                    <h2>Facturas de proveedor</h2>
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
                            <strong>Facturas de proveedor</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
			 <div class="row">
				<div class="col-lg-12">
					<div class="ibox-content">
						 <?php include "includes/spinner.php";?>
					  <form action="prov_facturas_proveedores.php" method="get" id="formBuscar" class="form-horizontal">
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
								<div class="col-lg-3">
									<select name="Estado" class="form-control m-b chosen-select" id="Estado">
										<option value="0" <?php if ($FilEstado == 0) {echo "selected=\"selected\"";}?>>(Todos)</option>
										<option value="1" <?php if ($FilEstado == 1) {echo "selected=\"selected\"";}?>>Pagada</option>
										<option value="2" <?php if ($FilEstado == 2) {echo "selected=\"selected\"";}?>>Abonada</option>
										<option value="3" <?php if ($FilEstado == 3) {echo "selected=\"selected\"";}?>>Pendiente de pago</option>
									</select>
								</div>
								<div class="col-lg-4">
									<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
								</div>
							</div>
						  	<div class="form-group">
								<div class="col-lg-12">
									<a href="exportar_excel.php?exp=14&Cons=<?php echo base64_encode($SQLCons); ?>"><img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/></a>
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
						<th>Núm. Interno</th>
						<th>Proyecto</th>
						<th>Fecha de factura</th>
						<th>Fecha de registro</th>
						<th>Núm. Factura Proveedor</th>
						<th>Valor factura</th>
						<th>Estado</th>
						<th>Fecha pago</th>
						<th>Valor pagado</th>
						<th>Saldo pendiente</th>
						<th>Acción</th>
					</tr>
                    </thead>
                    <tbody>
						   <?php
if ($FilEstado == 1) { //Filtro de estado - Pagada
    while ($row = sqlsrv_fetch_array($SQL)) {
        $dPago = ConsultarPago($row['ID_FacturaCompra'], $row['CardCode']);
        if ($dPago['DocNum'] != "") {
            ?>
									<tr>
										<td><?php echo $row['DocNum']; ?></td>
										<td><?php //echo $row['PrjName'];?></td>
										<td><?php echo $row['DocDate']; ?></td>
										<td><?php echo $row['TaxDate']; ?></td>
										<td><?php echo $row['NumAtCard']; ?></td>
										<td align="right"><?php echo number_format($row['DocTotal'], 2); ?></td>
										<td><span <?php if (($dPago['DocNum'] != "") && ($row['SaldoPendiente'] > 0)) {echo "class='label label-warning'";} elseif (($dPago['DocNum'] != "") && ($row['SaldoPendiente'] <= 0)) {echo "class='label label-primary'";} else {echo "class='label label-danger'";}?>><?php if (($dPago['DocNum'] != "") && ($row['SaldoPendiente'] > 0)) {echo "Abonada";} elseif (($dPago['DocNum'] != "") && ($row['SaldoPendiente'] <= 0)) {echo "Pagada";} else {echo "Pendiente de pago";}?></span></td>
										<td><?php if ($dPago['DocNum'] != "") {echo $dPago['FechaPago']->format('Y-m-d');} else {echo "--";}?></td>
										<td align="right"><?php if ($dPago['DocNum'] != "") {echo number_format($row['ValorPago'], 2);} else {echo "--";}?></td>
										<td align="right"><?php echo number_format($row['SaldoPendiente'], 2); ?></td>
										<td><a href="prov_detalle_facturas_proveedores.php?id=<?php echo base64_encode($row['ID_FacturaCompra']); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('prov_facturas_proveedores.php'); ?>" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a> <?php if (PermitirFuncion(604)) {?><a href="sapdownload.php?id=<?php echo base64_encode('15'); ?>&type=<?php echo base64_encode('2'); ?>&DocKey=<?php echo base64_encode($row['ID_FacturaCompra']); ?>&ObType=<?php echo base64_encode('18'); ?>&IdFrm=<?php echo base64_encode($row['IdSeries']); ?>" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-download"></i> Descargar</a><?php }?></td>
									</tr>
						   <?php }
    }
} elseif ($FilEstado == 2) { //Filtro de estado - Abonada
    while ($row = sqlsrv_fetch_array($SQL)) {
        $dPago = ConsultarPago($row['ID_FacturaCompra'], $row['CardCode']);
        if (($dPago['DocNum'] != "") && ($row['SaldoPendiente'] > 0)) {
            ?>
									<tr>
										<td><?php echo $row['DocNum']; ?></td>
										<td><?php //echo $row['PrjName'];?></td>
										<td><?php echo $row['DocDate']; ?></td>
										<td><?php echo $row['TaxDate']; ?></td>
										<td><?php echo $row['NumAtCard']; ?></td>
										<td align="right"><?php echo number_format($row['DocTotal'], 2); ?></td>
										<td><span <?php if (($dPago['DocNum'] != "") && ($row['SaldoPendiente'] > 0)) {echo "class='label label-warning'";} elseif (($dPago['DocNum'] != "") && ($row['SaldoPendiente'] <= 0)) {echo "class='label label-primary'";} else {echo "class='label label-danger'";}?>><?php if (($dPago['DocNum'] != "") && ($row['SaldoPendiente'] > 0)) {echo "Abonada";} elseif (($dPago['DocNum'] != "") && ($row['SaldoPendiente'] <= 0)) {echo "Pagada";} else {echo "Pendiente de pago";}?></span></td>
										<td><?php if ($dPago['DocNum'] != "") {echo $dPago['FechaPago']->format('Y-m-d');} else {echo "--";}?></td>
										<td align="right"><?php if ($dPago['DocNum'] != "") {echo number_format($row['ValorPago'], 2);} else {echo "--";}?></td>
										<td align="right"><?php echo number_format($row['SaldoPendiente'], 2); ?></td>
										<td><a href="prov_detalle_facturas_proveedores.php?id=<?php echo base64_encode($row['ID_FacturaCompra']); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('prov_facturas_proveedores.php'); ?>" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a> <?php if (PermitirFuncion(604)) {?><a href="sapdownload.php?id=<?php echo base64_encode('15'); ?>&type=<?php echo base64_encode('2'); ?>&DocKey=<?php echo base64_encode($row['ID_FacturaCompra']); ?>&ObType=<?php echo base64_encode('18'); ?>&IdFrm=<?php echo base64_encode($row['IdSeries']); ?>" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-download"></i> Descargar</a><?php }?></td>
									</tr>
						   <?php }
    }
} elseif ($FilEstado == 3) { // Pendiente de pago
    while ($row = sqlsrv_fetch_array($SQL)) {
        $dPago = ConsultarPago($row['ID_FacturaCompra'], $row['CardCode']);
        if ($dPago['DocNum'] == "") {
            ?>
									<tr>
										<td><?php echo $row['DocNum']; ?></td>
										<td><?php //echo $row['PrjName'];?></td>
										<td><?php echo $row['DocDate']; ?></td>
										<td><?php echo $row['TaxDate']; ?></td>
										<td><?php echo $row['NumAtCard']; ?></td>
										<td align="right"><?php echo number_format($row['DocTotal'], 2); ?></td>
										<td><span <?php if (($dPago['DocNum'] != "") && ($row['SaldoPendiente'] > 0)) {echo "class='label label-warning'";} elseif (($dPago['DocNum'] != "") && ($row['SaldoPendiente'] <= 0)) {echo "class='label label-primary'";} else {echo "class='label label-danger'";}?>><?php if (($dPago['DocNum'] != "") && ($row['SaldoPendiente'] > 0)) {echo "Abonada";} elseif (($dPago['DocNum'] != "") && ($row['SaldoPendiente'] <= 0)) {echo "Pagada";} else {echo "Pendiente de pago";}?></span></td>
										<td><?php if ($dPago['DocNum'] != "") {echo $dPago['FechaPago']->format('Y-m-d');} else {echo "--";}?></td>
										<td align="right"><?php if ($dPago['DocNum'] != "") {echo number_format($row['ValorPago'], 2);} else {echo "--";}?></td>
										<td align="right"><?php echo number_format($row['SaldoPendiente'], 2); ?></td>
										<td><a href="prov_detalle_facturas_proveedores.php?id=<?php echo base64_encode($row['ID_FacturaCompra']); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('prov_facturas_proveedores.php'); ?>" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a> <?php if (PermitirFuncion(604)) {?><a href="sapdownload.php?id=<?php echo base64_encode('15'); ?>&type=<?php echo base64_encode('2'); ?>&DocKey=<?php echo base64_encode($row['ID_FacturaCompra']); ?>&ObType=<?php echo base64_encode('18'); ?>&IdFrm=<?php echo base64_encode($row['IdSeries']); ?>" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-download"></i> Descargar</a><?php }?></td>
									</tr>
						   <?php }
    }
} else { //Todos
    while ($row = sqlsrv_fetch_array($SQL)) {
        $dPago = ConsultarPago($row['ID_FacturaCompra'], $row['CardCode']);
        ?>
									<tr>
										<td><?php echo $row['DocNum']; ?></td>
										<td><?php //echo $row['PrjName'];?></td>
										<td><?php echo $row['DocDate']; ?></td>
										<td><?php echo $row['TaxDate']; ?></td>
										<td><?php echo $row['NumAtCard']; ?></td>
										<td align="right"><?php echo number_format($row['DocTotal'], 2); ?></td>

										<td>
											<span <?php if (isset($dPago['DocNum']) && ($dPago['DocNum'] != "")) {if ($row['SaldoPendiente'] > 0) {echo "class='label label-warning'";} else {echo "class='label label-primary'";}} else {echo "class='label label-danger'";}?>>
											<?php if (isset($dPago['DocNum']) && ($dPago['DocNum'] != "")) {if ($row['SaldoPendiente'] > 0) {echo "Abonada";} else {echo "Pagada";}} else {echo "Pendiente de pago";}?>
											</span>
										</td>

										<td><?php if (isset($dPago['DocNum']) && ($dPago['DocNum'] != "")) {echo $dPago['FechaPago']->format('Y-m-d');} else {echo "--";}?></td>

										<td align="right"><?php if (isset($dPago['DocNum']) && ($dPago['DocNum'] != "")) {echo number_format($row['ValorPago'], 2);} else {echo "--";}?></td>

										<td align="right"><?php echo number_format($row['SaldoPendiente'], 2); ?></td>
										<td><a href="prov_detalle_facturas_proveedores.php?id=<?php echo base64_encode($row['ID_FacturaCompra']); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('prov_facturas_proveedores.php'); ?>" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a> <?php if (PermitirFuncion(604)) {?><a href="sapdownload.php?id=<?php echo base64_encode('15'); ?>&type=<?php echo base64_encode('2'); ?>&DocKey=<?php echo base64_encode($row['ID_FacturaCompra']); ?>&ObType=<?php echo base64_encode('18'); ?>&IdFrm=<?php echo base64_encode($row['IdSeries']); ?>" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-download"></i> Descargar</a><?php }?></td>
									</tr>
						   <?php
}
}
?>
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