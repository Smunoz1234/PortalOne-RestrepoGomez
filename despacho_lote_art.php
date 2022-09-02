<?php  
require_once("includes/conexion.php");

$Param=array(
	"'".$_SESSION['CodUser']."'"
);
$SQL=EjecutarSP('sp_ConsultarDespachoLote_Resumen',$Param);

?>
<div class="row">
 <div class="col-lg-12">
	<div class="ibox-content">
		 <?php include("includes/spinner.php"); ?>
		<div class="ibox">
			<div class="ibox-title bg-success">
				<h5 class="collapse-link"><i class="fa fa-cubes"></i> Resumen de artículos a despachar</h5>
				<a class="collapse-link pull-right" style="color: inherit;">
					<i class="fa fa-chevron-up"></i>
				</a>	
			</div>
			<div class="ibox-content">
				<div class="table-responsive">
					<table class="table table-bordered table-hover table-striped dataTables-Resumen">
					<thead>
					<tr>
						<th>#</th>
						<th>Código</th>
						<th>Nombre artículo</th>
						<th>Unidad</th>
						<th>Cantidad</th>
						<th>Validación</th>
					</tr>
					</thead>
					<tbody>
					<?php $i=1;
						while($row=sql_fetch_array($SQL)){?>
						<tr>
							<td><?php echo $i;?></td>
							<td><?php echo $row['ItemCode'];?></td>
							<td><?php echo $row['ItemName'];?></td>
							<td><?php echo $row['Unidad'];?></td>
							<td><?php echo number_format($row['Cantidad'],2);?></td>
							<td><span class="badge badge-primary">OK</span></td>
						</tr>
					<?php $i++;}?>
					</tbody>
					</table>
				</div>
			</div>
		</div>		
	</div>
 </div> 
</div>
<script>
 $(document).ready(function(){
	var table = $('.dataTables-Resumen').DataTable({
		pageLength: 10,
		dom: '<"html5buttons"B>lTfgitp',
		orderCellsTop: true,
		fixedHeader: true,
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