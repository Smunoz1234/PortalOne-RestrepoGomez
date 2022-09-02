<?php
require_once( "includes/conexion.php" );

if(isset($_POST['id'])&&$_POST['id']!=""){
	$id = $_POST['id'];
}else{
	$id = "";
}

$SQL=Seleccionar("tbl_AnalisisLaboratorioDetalle","*","id_analisis_laboratorio='".$id."'");
$dir_anx=CrearObtenerDirAnx("formularios/analisis_laboratorio/anexos");
?>
<div class="row m-t-md form-horizontal">
	 <div class="col-lg-12">
		<div class="ibox-content">
			 <?php include("includes/spinner.php"); ?>
			<div class="form-group">
				<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Detalle de análisis de laboratorio: <?php echo $id;?></h3></label>
			</div>
			<div class="table-responsive">
				<table width="100%" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>#</th>
							<th>Motonave</th>
							<th>Producto</th>
							<th>Humedad (%)</th>
							<th>Densidad (Kg)</th>
							<th>Granos partidos (%)</th>
							<th>Impurezas (%)</th>
							<th>Granos quemados (%)</th>
							<th>Otros productos</th>
							<th>Otros productos (%)</th>
							<th>Granos perforados (%)</th>
						</tr>
					</thead>
					<tbody>
						 <?php $i=1;
							while($row=sqlsrv_fetch_array($SQL)){?>
						<tr>
							<td><?php echo $i;?></td>
							<td><?php echo $row['transporte_puerto'];?></td>
							<td><?php echo $row['producto_puerto'];?></td>
							<td><?php echo number_format($row['porcen_humedad'],1);?> <a href="filedownload.php?file=<?php echo base64_encode($row['anexo_humedad_densidad']);?>&dir=<?php echo base64_encode($dir_anx);?>" target="_blank" title="Descargar anexo" class="btn-link btn-xs text-danger"><i class="fa fa-download"></i></a></td>
							<td><?php echo number_format($row['kg_hl_densidad'],1);?></td>
							<td><?php echo number_format($row['porcen_granos_partidos'],1);?> <a href="filedownload.php?file=<?php echo base64_encode($row['anexo_granos_partidos']);?>&dir=<?php echo base64_encode($dir_anx);?>" target="_blank" title="Descargar anexo" class="btn-link btn-xs text-danger"><i class="fa fa-download"></i></a></td>
							<td><?php echo number_format($row['porcen_impurezas'],1);?> <a href="filedownload.php?file=<?php echo base64_encode($row['anexo_porcen_impurezas']);?>&dir=<?php echo base64_encode($dir_anx);?>" target="_blank" title="Descargar anexo" class="btn-link btn-xs text-danger"><i class="fa fa-download"></i></a></td>
							<td><?php echo number_format($row['porcen_granos_quemados'],1);?> <a href="filedownload.php?file=<?php echo base64_encode($row['anexo_granos_quemados']);?>&dir=<?php echo base64_encode($dir_anx);?>" target="_blank" title="Descargar anexo" class="btn-link btn-xs text-danger"><i class="fa fa-download"></i></a></td>
							<td><?php echo $row['producto_otros'];?></td>
							<td><?php echo number_format($row['porcen_otros_granos'],1);?> <a href="filedownload.php?file=<?php echo base64_encode($row['anexo_otros_granos']);?>&dir=<?php echo base64_encode($dir_anx);?>" target="_blank" title="Descargar anexo" class="btn-link btn-xs text-danger"><i class="fa fa-download"></i></a></td>
							<td><?php echo number_format($row['porcen_granos_perforados'],1);?> <a href="filedownload.php?file=<?php echo base64_encode($row['anexo_granos_perforados']);?>&dir=<?php echo base64_encode($dir_anx);?>" target="_blank" title="Descargar anexo" class="btn-link btn-xs text-danger"><i class="fa fa-download"></i></a></td>
						</tr>	
						<?php $i++;}?>
					</tbody>
				</table>
			</div>
		</div>
	 </div> 
</div>