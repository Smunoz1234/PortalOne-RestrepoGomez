<?php  
require_once("includes/conexion.php");
PermitirAcceso(413);

$TotalVentas=0;
$TotalCostos=0;
$TotalGanancia=0;
$PrcntGanancia=0;

$ParamCons=array(
	$_POST['TipoInforme'],
	2,
	"'".$_POST['FInicial']."'",
	"'".$_POST['FFinal']."'",
	"'".$_POST['SlpCode']."'",
	"'".$_POST['Cliente']."'",
	"'".$_POST['Articulo']."'"
);
$SQL=EjecutarSP('usp_InformeVentas',$ParamCons);

?>
<br>
 	<div class="row">
	   <div class="col-lg-12">
			<div class="ibox-content">
				 <?php include("includes/spinner.php"); ?>
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-list"></i> Análisis detallado</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>	
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<?php if($_POST['TipoInforme']!=3){?>
							<div class="table-responsive">
								<table class="table table-striped table-bordered table-hover dataTables-DetailsVenta" >
								<thead>
								<tr>
									<th>Número de documento</th>
									<th>Fecha documento</th>
									<th>Cliente</th>
									<th>Sucursal</th>
									<th>Empleado de ventas</th>
									<th>Ventas</th>
									<th>Costos</th>
									<th>Ganancia</th>
									<th>% de ganancia</th>
									<th>Análisis de costos</th>
									<th>Acciones</th>
								</tr>
								</thead>
								<tbody>
								<?php 
									while($row=sqlsrv_fetch_array($SQL)){ ?>
										<tr id="tr_Det<?php echo $row['DocEntry'];?>" class="trDetalle">
											<td><a href="<?php echo ($row['DocType']==13) ? 'factura_venta' : 'nota_credito'; ?>.php?id=<?php echo base64_encode($row['DocEntry']);?>&tl=1" target="_blank"><?php echo ($row['DocType']==13) ? ($row['DocSubType']=='DN') ? 'ND '.$row['DocNum'] : 'FV '.$row['DocNum'] : 'NC '.$row['DocNum'];?></a></td>
											<td><?php echo $row['DocDate']->format('Y-m-d');?></td>
											<td><?php echo $row['CardName'];?></td>
											<td><?php echo $row['SucursalCliente'];?></td>
											<td><?php echo $row['SlpName'];?></td>
											<td><?php echo number_format($row['Sales'],2);?></td>
											<td><?php echo number_format($row['Costos'],2);?></td>
											<td class="<?php if($row['GrossProfit']<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo number_format($row['GrossProfit'],2);?></td>
											<td class="<?php if($row['GrossPrcnt']<0 || $row['GrossProfit']<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo number_format($row['GrossPrcnt'],2);?></td>
											<td><?php echo $row['TipoCostos'];?></td>
											<td><a href="#" onClick="VerDetalleFactura('<?php echo $row['DocEntry'];?>','<?php echo $row['DocNum'];?>','<?php echo $row['Sales'];?>','<?php echo $row['Costos'];?>','<?php echo $row['GrossProfit'];?>','<?php echo $row['GrossPrcnt'];?>');" class="btn btn-warning btn-xs"><i class="fa fa-folder-open-o"></i> Ver detalles</a></td>
										</tr>
								<?php 
									$TotalVentas+=$row['Sales'];
									$TotalCostos+=$row['Costos'];
									$TotalGanancia+=$row['GrossProfit'];
								}?>
								</tbody>
								</table>
								<div class="col-lg-12">
									<a href="exportar_excel.php?exp=10&Cons=<?php echo base64_encode(implode(",",$ParamCons));?>&sp=<?php echo base64_encode("usp_InformeVentas");?>">
										<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
									</a>
								</div>
							</div>
							<?php }else{?>
							<div class="table-responsive">
								<table class="table table-striped table-bordered table-hover dataTables-DetailsVenta" >
								<thead>
								<tr>
									<th>Número de documento</th>
									<th>Fecha documento</th>
									<th>Cliente</th>
									<th>Sucursal</th>
									<th>Empleado ventas</th>
									<th>Código artículo</th>
									<th>Nombre artículo</th>
									<th>Cantidad</th>
									<th>Precio artículo</th>
									<th>Precio venta</th>
									<th>Ventas</th>
									<th>Costos</th>
									<th>Ganancia</th>
									<th>% de ganancia</th>
								</tr>
								</thead>
								<tbody>
								<?php 
									while($row=sqlsrv_fetch_array($SQL)){ ?>
										<tr>
											<td><a href="<?php echo ($row['DocType']==13) ? 'factura_venta' : 'nota_credito'; ?>.php?id=<?php echo base64_encode($row['DocEntry']);?>&tl=1" target="_blank"><?php echo ($row['DocType']==13) ? 'FV '.$row['DocNum'] : 'NC '.$row['DocNum'];?></a></td>
											<td><?php echo $row['DocDate']->format('Y-m-d');?></td>
											<td><?php echo $row['CardName'];?></td>
											<td><?php echo $row['SucursalCliente'];?></td>
											<td><?php echo $row['SlpName'];?></td>
											<td><a href="articulos.php?id=<?php echo base64_encode($row['ItemCode']);?>&tl=1" target="_blank"><?php echo $row['ItemCode'];?></a></td>
											<td><?php echo $row['ItemName'];?></td>
											<td><?php echo number_format($row['Qty'],2);?></td>
											<td><?php echo number_format($row['StockPrice'],2);?></td>
											<td><?php echo number_format($row['Price'],2);?></td>
											<td><?php echo number_format($row['Sales'],2);?></td>
											<td><?php echo number_format($row['Costos'],2);?></td>
											<td class="<?php if($row['GrossProfit']<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo number_format($row['GrossProfit'],2);?></td>
											<td class="<?php if($row['GrossPrcnt']<0 || $row['GrossProfit']<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo number_format($row['GrossPrcnt'],2);?></td>
										</tr>
								<?php 
									$TotalVentas+=$row['Sales'];
									$TotalCostos+=$row['Costos'];
									$TotalGanancia+=$row['GrossProfit'];
								}?>
								</tbody>
								</table>
								<div class="col-lg-12">
									<a href="exportar_excel.php?exp=10&Cons=<?php echo base64_encode(implode(",",$ParamCons));?>&sp=<?php echo base64_encode("usp_InformeVentas");?>">
										<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
									</a>
								</div>
							</div>
							<?php }?>
						</div>
						<div class="row m-t-md">
							<div class="col-lg-10 pull-right">
								<div class="col-lg-3">
									<div class="ibox border-left-right border-top-bottom">
										<div class="ibox-title">
											<h2 class="font-bold">Total ventas</h2>
										</div>
										<div class="ibox-content">
											<h1 class="no-margins"><span class="font-bold text-success"><?php echo "$".number_format($TotalVentas,0);?></span></h1>
										</div>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="ibox border-left-right border-top-bottom">
										<div class="ibox-title">
											<h2 class="font-bold">Total costos</h2>
										</div>
										<div class="ibox-content">
											<h1 class="no-margins"><span class="font-bold text-danger"><?php echo "$".number_format($TotalCostos,0);?></span></h1>
										</div>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="ibox border-left-right border-top-bottom">
										<div class="ibox-title">
											<h2 class="font-bold">Total ganancias</h2>
										</div>
										<div class="ibox-content">
											<h1 class="no-margins"><span class="font-bold <?php if($TotalGanancia<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo "$".number_format($TotalGanancia,0);?></span></h1>
										</div>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="ibox border-left-right border-top-bottom">
										<div class="ibox-title">
											<h2 class="font-bold">Ganancias (%)</h2>
										</div>
										<?php $PrcntGanancia=(($TotalVentas-$TotalCostos)!=0 && $TotalVentas!=0) ? (($TotalVentas-$TotalCostos)*100)/$TotalVentas : 0; ?>
										<div class="ibox-content">
											<h2 class="no-margins"><span class="font-bold <?php if($PrcntGanancia<0 || $TotalGanancia<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo number_format($PrcntGanancia,2)."%";?></span></h2>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div> 
	</div>
<script>
 $(document).ready(function(){
	 
	var table = $('.dataTables-DetailsVenta').DataTable({
		pageLength: 10,
		dom: '<"html5buttons"B>lTfgitp',
		orderCellsTop: true,
		order: [2,"asc"],
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