<?php 
if(isset($_POST['id'])&$_POST['id']!=""){
	include_once("includes/conexion.php");
	
	$typeFact = isset($_POST['typefact']) ? $_POST['typefact'] : 1;
	
	//Tipo de factura
	//1 -> (default) Factura de venta
	//2 -> Factura de proveedores
	if($typeFact==1){
		$SQLRet=Seleccionar('uvw_Sap_tbl_FacturasVentasRetenciones','*',"ID_FacturaVenta='".base64_decode($_POST['id'])."'");
	}elseif($typeFact==2){
		$SQLRet=Seleccionar('uvw_Sap_tbl_FacturasComprasRetenciones','*',"ID_FacturaCompra='".base64_decode($_POST['id'])."'");
	}
	
?>
<div class="modal-header">
	<h4 class="modal-title">
		Retenciones de factura
	</h4>
</div>
<div class="modal-body">
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
<div class="modal-footer">
	<button type="button" class="btn btn-danger m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
</div>
		
<?php }?>