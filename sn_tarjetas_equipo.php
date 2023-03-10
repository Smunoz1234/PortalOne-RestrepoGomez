<?php  
require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
if(isset($_GET['id'])&&$_GET['id']!=""){
	$id=base64_decode($_GET['id']);
}else{
	$id="";
}

if(isset($_GET['objtype'])){
	$objtype=$_GET['objtype'];
}else{
	$objtype=2;
}

if($objtype==190){
	//Contratos
	$Where="[IdContratoServicio]='".$id."'";
}else{
	//Socios de negocios
	$Where="[CardCode]='".$id."'";
}

$SQL_Tarjetas=Seleccionar('uvw_Sap_tbl_TarjetasEquipos','*',$Where);

?>
<div class="form-group">
	<div class="col-lg-12">
		<div class="table-responsive">
		<table class="table table-striped table-bordered table-hover dataTables7" >
		<thead>
		<tr>
			<th>Núm.</th>
			<th>Serial fabricante</th>                                         
			<th>Serial interno</th>
			<th>Código de artículo</th>
			<th>Artículo</th>
			<th>Tipo de equipo</th>
			<th>Estado</th>
			<th>Acciones</th>
		</tr>
		</thead>
		<tbody>
		<?php while($row_Tarjeta=sqlsrv_fetch_array($SQL_Tarjetas)){?>
						 <tr class="gradeX tooltip-demo">
							<td><?php echo $row_Tarjeta['IdTarjetaEquipo'];?></td>
							<td><?php echo $row_Tarjeta['SerialFabricante'];?></td>
							<td><?php echo $row_Tarjeta['SerialInterno'];?></td>
							<td><?php echo $row_Tarjeta['ItemCode'];?></td>
							<td><?php echo $row_Tarjeta['ItemName'];?></td>
							<td>
								<?php if($row_Tarjeta['TipoEquipo'] === 'P') { echo 'Compras'; } elseif($row_Tarjeta['TipoEquipo'] === 'R') { echo 'Ventas'; } ?>
							</td>
							<td>
								<?php if($row_Tarjeta['CodEstado']=='A') { ?>
									<span  class='label label-info'>Activo</span>
								<?php } elseif ($row_Tarjeta['CodEstado']=='R') { ?>
									<span  class='label label-danger'>Devuelto</span>
								<?php } elseif ($row_Tarjeta['CodEstado']=='T') { ?>
									<span  class='label label-success'>Finalizado</span>
								<?php } elseif ($row_Tarjeta['CodEstado']=='L') { ?>
									<span  class='label label-secondary'>Concedido en préstamo</span>
								<?php } elseif ($row_Tarjeta['CodEstado']=='I') { ?>
									<span  class='label label-warning'>En laboratorio de reparación</span>
								<?php } ?>
							</td>
							<td><a href="tarjeta_equipo.php?id=<?php echo base64_encode($row_Tarjeta['IdTarjetaEquipo']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('consultar_tarjeta_equipo.php');?>&tl=1" class="alkin btn btn-success btn-xs" target="_blank"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
						</tr>
					<?php }?>
		</tbody>
		</table>
		</div>
	</div>
</div>	
<script>
 $(document).ready(function(){
	$('.dataTables7').DataTable({
                pageLength: 25,
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