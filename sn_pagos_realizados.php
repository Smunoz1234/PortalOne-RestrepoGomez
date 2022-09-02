<?php  
require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
if(isset($_GET['id'])&&$_GET['id']!=""){
	$CodCliente=base64_decode($_GET['id']);
}else{
	$CodCliente="";
}
//Pagos realizados
$SQL_PagosRealizados=Seleccionar('uvw_Sap_tbl_Pagos_Recibidos','*',"[CardCode]='".$CodCliente."'",'[DocDate]');
?>
<div class="form-group">
	<div class="col-lg-12">
		<div class="table-responsive">
			<table width="100%" class="table table-striped table-bordered table-hover dataTables5" >
			<thead>
			<tr>
				<th>Factura</th>
				<th>Fecha factura</th>
				<th>Fecha vencimiento</th>
				<th>Fecha pago</th>
				<th>Valor pagado</th>
				<th>Comentarios</th>
				<th>Dias transcurridos</th>
				<th>Saldo pendiente</th>
				<th>Acciones</th>
			</tr>
			</thead>
			<tbody>
			<?php while($row_PagosRealizados=sql_fetch_array($SQL_PagosRealizados)){ 
				$DVenc=DiasTranscurridos($row_PagosRealizados['DocDate']->format('Y-m-d'),$row_PagosRealizados['DocDueDateFactura']->format('Y-m-d'));
				?>
				 <tr>
					<td><?php echo $row_PagosRealizados['DocNumFactura'];?></td>
					<td><?php echo $row_PagosRealizados['DocDateFactura']->format('Y-m-d');?></td>
					<td><?php echo $row_PagosRealizados['DocDueDateFactura']->format('Y-m-d');?></td>
					<td><?php echo $row_PagosRealizados['DocDate']->format('Y-m-d');?></td>
					<td><?php echo "$".number_format($row_PagosRealizados['TotalFactura'],2);?></td>
					<td><?php echo utf8_encode($row_PagosRealizados['Notas']);?></td>
					<td><?php echo $DVenc[1];?></td>
					<td><?php echo "$".number_format($row_PagosRealizados['Pendiente'],2);?></td>
					<td>
						<a href="factura_venta.php?id=<?php echo base64_encode($row_PagosRealizados['DocEntryFactura']);?>&id_portal=<?php echo base64_encode($row_PagosRealizados['IdDocPortal']);?>&tl=1" class="btn btn-success btn-xs" target="_blank"><i class="fa fa-folder-open-o"></i> Abrir</a>
						<a href="sapdownload.php?id=<?php echo base64_encode('15');?>&type=<?php echo base64_encode('2');?>&DocKey=<?php echo base64_encode($row_PagosRealizados['DocEntryFactura']);?>&ObType=<?php echo base64_encode('13');?>&IdFrm=<?php echo base64_encode($row_PagosRealizados['IdSeries']);?>" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-download"></i> Descargar</a>
						<?php if($row_PagosRealizados['URLVisorPublico']!=""){?><a href="<?php echo $row_PagosRealizados['URLVisorPublico'];?>" target="_blank" class="btn btn-primary btn-xs" title="Ver factura eléctronica"><i class="fa fa-external-link"></i> Fact. Elect</a><?php }?>
					</td>
				</tr>
			<?php }?>
			</tbody>
			</table>
		</div>
	</div>
</div>
<script>
 $(document).ready(function(){
	$('.dataTables5').DataTable({
                pageLength: 10,
                dom: '<"html5buttons"B>lTfgitp',
				order: [[ 0, "desc" ]],
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