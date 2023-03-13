<?php require_once "includes/conexion.php";
PermitirAcceso(601);

$Filtro = "";
$FiltroFecha = "";
$WhereFecha = "";

if (isset($_GET['FiltroFecha']) && $_GET['FiltroFecha'] != "") {
    $FiltroFecha = $_GET['FiltroFecha'];
} else {
    $FiltroFecha = "FechaPago";
}

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
    $WhereFecha = "and Canceled <> 'Y' and ($FiltroFecha Between '" . FormatoFecha($FechaInicial) . "' and '" . FormatoFecha($FechaFinal) . "')";
} else {
    $FechaFinal = date('Y-m-d');
    $WhereFecha = "and Canceled <> 'Y' and ($FiltroFecha Between '" . FormatoFecha($FechaInicial) . "' and '" . FormatoFecha($FechaFinal) . "')";
//    $FechaFinal="";
}

if (isset($_GET['BuscarDato']) && $_GET['BuscarDato'] != "") {
    $Filtro .= "and (ValorPago LIKE '%" . $_GET['BuscarDato'] . "%' OR FacturaProveedor LIKE '%" . $_GET['BuscarDato'] . "%' OR NumIntFactura LIKE '%" . $_GET['BuscarDato'] . "%')";
}

$Where = "CardCode='" . $_SESSION['CodigoSAPProv'] . "' $WhereFecha $Filtro ";
// echo "SELECT * FROM uvw_Sap_tbl_Pagos_Efectuados WHERE $Where";
$SQL = Seleccionar('uvw_Sap_tbl_Pagos_Efectuados', '*', $Where);
$SQLCons = ReturnCons('uvw_Sap_tbl_Pagos_Efectuados', '*', $Where);
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Pagos efectuados | <?php echo NOMBRE_PORTAL; ?></title>
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
                    <h2>Pagos efectuados</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Proveedores</a>
                        </li>
						<li>
                            <a href="#">Estados de cuenta</a>
                        </li>
                        <li class="active">
                            <strong>Pagos efectuados</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
		  <div class="row">
				<div class="col-lg-12">
					<div class="ibox-content">
						 <?php include "includes/spinner.php";?>
					  <form action="prov_pagos_efectuados.php" method="get" id="formBuscar" class="form-horizontal">
							<div class="form-group">
								<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
							</div>
							<div class="form-group">
								<label class="col-lg-1 control-label">Filtro fecha</label>
								<div class="col-lg-2">
									<select name="FiltroFecha" class="form-control m-b" id="FiltroFecha">
										<option value="FechaContFactura" <?php if ($FiltroFecha == "FechaContFactura") {echo "selected=\"selected\"";}?>>Fecha factura</option>
										<option value="FechaVencFactura" <?php if ($FiltroFecha == "FechaVencFactura") {echo "selected=\"selected\"";}?>>Fecha vencimiento</option>
										<option value="FechaPago" <?php if ($FiltroFecha == "FechaPago") {echo "selected=\"selected\"";}?>>Fecha pago</option>
									</select>
								</div>
								<label class="col-lg-1 control-label">Fechas</label>
								<div class="col-lg-3">
									<div class="input-daterange input-group" id="datepicker">
										<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial; ?>" autocomplete="off"/>
										<span class="input-group-addon">hasta</span>
										<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal; ?>" autocomplete="off" />
									</div>
								</div>
								<label class="col-lg-1 control-label">Buscar dato</label>
								<div class="col-lg-3">
									<input name="BuscarDato" type="text" class="form-control" id="BuscarDato" maxlength="100" value="<?php if (isset($_GET['BuscarDato']) && ($_GET['BuscarDato'] != "")) {echo $_GET['BuscarDato'];}?>">
								</div>
								<div class="col-lg-1">
									<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
								</div>
							</div>
						  	<div class="form-group">
								<div class="col-lg-12">
									<a href="exportar_excel.php?exp=15&Cons=<?php echo base64_encode($SQLCons); ?>"><img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/></a>
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
						<th>Factura proveedor</th>
						<th>Núm. Interno de factura</th>
						<th>Fecha factura</th>
						<th>Fecha vencimiento</th>
						<th>Núm. de pago</th>
						<th>Valor factura</th>
						<th>Valor pagado</th>
						<th>Fecha pago</th>
						<th>Efectivo</th>
						<th>Transferencia</th>
						<th>Cheque</th>
						<th>Núm. Cheque</th>
					</tr>
                    </thead>
                    <tbody>
						   <?php while ($row = sqlsrv_fetch_array($SQL)) {?>
							<tr class="odd gradeX">
								<td><?php if ($row['FacturaProveedor'] != "") {?><a href="prov_detalle_facturas_proveedores.php?id=<?php echo base64_encode($row['DocEntryFactura']); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('prov_pagos_efectuados.php'); ?>"><?php echo $row['FacturaProveedor']; ?></a><?php } else {echo "--";}?></td>
								<td><?php echo $row['DocNumFactura']; ?></td>
								<td><?php if ($row['FechaContFactura'] != "") {echo $row['FechaContFactura']->format('Y-m-d');} else {echo "--";}?></td>
								<td><?php if ($row['FechaVencFactura'] != "") {echo $row['FechaVencFactura']->format('Y-m-d');} else {echo "--";}?></td>
								<td><?php echo $row['NumPagoEfectuado']; ?></td>
								<td align="right"><?php echo number_format($row['DocTotal'], 2); ?></td>
								<td align="right"><?php echo number_format($row['ValorPago'], 2); ?></td>
								<td><?php if ($row['FechaPago'] != "") {echo $row['FechaPago']->format('Y-m-d');} else {echo "--";}?></td>
								<td align="right"><?php echo number_format($row['CashSum'], 2); ?></td>
								<td align="right"><?php echo number_format($row['TrsfrSum'], 2); ?></td>
								<td align="right"><?php echo number_format($row['CheckSum'], 2); ?></td>
								<td align="right"><?php echo $row['CheckNum']; ?></td>
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