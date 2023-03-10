<?php  
require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
if(isset($_GET['id'])&&$_GET['id']!=""){
	$CodCliente=base64_decode($_GET['id']);
}else{
	$CodCliente="";
}

//anticipos realizados
        $ParamSN = array(
            "'".$CodCliente."'"
        );

$SQL_SN = EjecutarSP('usp_Anticipos_Clientes', $ParamSN);
?>
<div class="form-group">
	<div class="col-lg-12">
		<div class="table-responsive">
			<table width="100%" class="table table-striped table-bordered table-hover dataTables6" >
			<thead>
			<tr>
				<th>No Pago</th>
				<th>Fecha pago</th>
				<th>Valor pagado</th>
				<th>Comentarios</th>
				<th>Saldo pendiente</th>
			</tr>
			</thead>
			<tbody>
			<?php while($row_AnticiposRealizados=sqlsrv_fetch_array($SQL_SN)){ ?>
				 <tr>
					<td><?php echo $row_AnticiposRealizados['NoPago'];?></td>
					<td><?php echo $row_AnticiposRealizados['FechaPago']->format('Y-m-d');?></td>
					<td><?php echo "$".number_format($row_AnticiposRealizados['ValorPago'],2);?></td>
					<td><?php echo utf8_encode($row_AnticiposRealizados['Comentarios']);?></td>
					<td><?php echo "$".number_format($row_AnticiposRealizados['SaldoPendiente'],2);?></td>
				</tr>
			<?php }?>
			</tbody>
			</table>
		</div>
	</div>
</div>
<script>
 $(document).ready(function(){
	$('.dataTables6').DataTable({
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