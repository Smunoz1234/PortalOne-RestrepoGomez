<?php
require_once( "includes/conexion.php" );

$Param=array(
	"'3'",
	"'".$_GET['finicial']."'",
	"'".$_GET['ffinal']."'",
	"'".$_GET['id']."'",
	"'".$_GET['suc']."'",
	"'".$_GET['serie']."'",
	"'".$_GET['tllamada']."'"
);

$SQL=EjecutarSP('sp_DashboardFacturacion',$Param);
?>
<div class="table-responsive">
	<table width="100%" class="table table-bordered table-hover dataTables-Details-lvl1">
		<thead>
			<tr>
				<th>#</th>
				<th>Código de artículo</th>
				<th>Descripción de artículo</th>
				<th>Sucursal</th>
				<th>Cantidad</th>
				<th>Valor del artículo</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		  $i=1;
		  while($row=sqlsrv_fetch_array($SQL)){
		?>
			<tr>
				<td><?php echo $i;?></td>
				<td class="drill-lvl1" data-vta='<?php echo $row['IdVTAFactura'];?>' data-suc='<?php echo $row['NombreSucursal'];?>'><a href='javascript:void(0);'><i class='fa fa-plus-square-o'></i> <?php echo $row['IdVTAFactura'];?></a></td>
				<td><?php echo $row['DeVTAFactura'];?></td>
				<td><?php echo $row['NombreSucursal'];?></td>
				<td><?php echo number_format($row['Cant'],2);?></td>
				<td><?php echo number_format($row['Total'],2);?></td>
			</tr>
		<?php  $i++;}?>
		</tbody>
	</table>
</div>
<script>
 $(document).ready(function(){
	 
	let table_details_lvl1 = $('.dataTables-Details-lvl1').DataTable({
		retrieve: true,
		pageLength: 10,
		orderCellsTop: true,
		searching: false,
		responsive: true,
		info: false,
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
		}
	});
	 
	$('.dataTables-Details-lvl1 tbody').on('click', 'td.drill-lvl1', function (ev) {

        let tbid = $(this).closest('table').attr('id');
        let tr = $(this).closest('tr');
        let row = table_details_lvl1.row( tr );
        
        let vta = $(this).data('vta');
		let suc = $(this).data('suc');
 
		//Busco el div col-* mas arriba para obtener la clase de bootstrap que tiene
        //var column = $(this).closest('table').parent().parent().parent();

        if ( row.child.isShown() ) {
            // Si el row esta abierto, cierralo
            row.child.hide();
            tr.removeClass('shown');

            // contract column on drill down hide
//            var restoreclass=tr.attr('class').replace('odd ','').replace('even ','');
//            tr.removeClass(restoreclass);
//            column.removeClass('col-md-12');
//            column.addClass('col-'+restoreclass);
        }
        else {
            // Open this row
			$('.ibox-content').toggleClass('sk-loading',true);
            $.get( 
				'dsb_facturacion_detalle_nivel2.php',
				{
					id:'<?php echo $_GET['id'];?>',
					finicial:'<?php echo $_GET['finicial'];?>',
					ffinal: '<?php echo $_GET['ffinal'];?>',
					suc: suc,
					serie: '<?php echo $_GET['serie'];?>',
					tllamada: '<?php echo $_GET['tllamada'];?>',
					vta: vta
				}
			).done( function( data ) {
				//Mostrar los datos
				row.child(data).show(); 

				// expand column on drill down, retract on hide
//				var columnclass = $(column).attr('class').substring(4); //col-[md-12]
//				column.removeClass('col-'+columnclass);
//				column.addClass('col-md-12'); 
				tr.addClass('shown');
//				tr.addClass(columnclass);
				$('.ibox-content').toggleClass('sk-loading',false);
            });
            
        }
	});
 });
</script>