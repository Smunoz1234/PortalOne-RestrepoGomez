<?php 
if(isset($_GET['id'])&$_GET['id']!=""){
	include_once("includes/conexion.php");
	
	$typeFact = isset($_GET['typefact']) ? $_GET['typefact'] : 1;
	
	//Tipo de factura
	//1 -> (default) Factura de venta
	//2 -> Factura de proveedores
	if($typeFact==1){
		$SQLRet=Seleccionar('uvw_Sap_tbl_FacturasVentasRetenciones','*',"ID_FacturaVenta='".base64_decode($_GET['id'])."'");
	}elseif($typeFact==2){
		$SQLRet=Seleccionar('uvw_Sap_tbl_FacturasComprasRetenciones','*',"ID_FacturaCompra='".base64_decode($_GET['id'])."'");
	}
	
?>
<!doctype html>
<html>
<head>
<?php include_once("includes/cabecera.php"); ?>
<title>Retenciones de factura | <?php echo NOMBRE_PORTAL;?></title>
</head>

<body style="background-color: #fff; !important">
    <div class="row">
           <div class="col-lg-12">
			   <div class="ibox-content">
			<div class="table-responsive">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>#</th>
				<th>Código</th>
				<th>Nombre</th>
				<th>Tarifa</th>
				<th>Retención</th>
				<th>Base retención</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$i=1;
			$Total=0;
			while($rowRet=sqlsrv_fetch_array($SQLRet)){
				$Total=$Total+$rowRet['ImporteRetencion']; 
		?>
			<tr>
				<td><?php echo $i;?></td>
				<td><?php echo $rowRet['IdRetencion'];?></td>
				<td><?php echo utf8_encode($rowRet['DeRetencion']);?></td>
				<td><?php echo number_format($rowRet['TasaRetencion'],2);?></td>
				<td><?php echo number_format($rowRet['ImporteRetencion'],2);?></td>
				<td><?php echo number_format($rowRet['BaseRetencion'],2);?></td>
			</tr>
		<?php $i++;}?>
			<tr>
				<td colspan="4" class="font-bold"><span class="pull-right">Total</span></td>
				<td class="font-bold"><?php echo number_format($Total,2);?></td>
				<td></td>
			</tr>
		</tbody>
	</table>
</div>
		   		</div>
		  </div>
		  </div>
</body>
</html>
<?php sqlsrv_close($conexion);}?>