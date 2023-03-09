<?php
require_once( "includes/conexion.php" );

$Param=array(
	"'4'",
	"'".$_GET['finicial']."'",
	"'".$_GET['ffinal']."'",
	"'".$_GET['id']."'",
	"'".$_GET['suc']."'",
	"'".$_GET['serie']."'",
	"'".$_GET['tllamada']."'",
	"'".$_GET['vta']."'"
);

$SQL=EjecutarSP('sp_DashboardFacturacion',$Param);
?>
<div class="table-responsive">
	<table width="100%" class="table table-bordered table-hover dataTables-Details-lvl2">
		<thead>
			<tr>
				<th>#</th>
				<th>Id llamada</th>
				<th>Serie</th>
				<th>Sucursal cliente</th>
				<th>Tipo llamada</th>
				<th>Visita</th>
				<th>Servicio</th>
				<?php /*?><th>VTA</th>
				<th>Valor VTA</th><?php */?>
				<th>Fecha creación</th>
				<th>Fecha cierre</th>
				<th>Fecha actividad</th>
				<th>Dias sin facturar</th>
				<th>Estado llamada</th>
				<th>Estado servicio llamada</th>
				<th>Acciones</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		  $i=1;
		  while($row=sqlsrv_fetch_array($SQL)){  
			$Icon=IconAttach($row['ExtAnexoLlamada']);
			$DVenc=DiasTranscurridos(date('Y-m-d'),$row['FechaInicioActividad']);
		?>
			<tr>
				<td><?php echo $i;?></td>
				<td><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_LlamadaServicio']);?>&tl=1" target="_blank"><?php echo $row['DocNum'];?></a></td>
				<td><?php echo $row['SeriesName'];?></td>
				<td><?php echo $row['NombreSucursal'];?></td>
				<td><?php echo $row['DeTipoLlamada'];?></td>
				<td><?php echo $row['DeArticuloLlamada'];?></td>
				<td><?php echo $row['CDU_Servicios'];?></td>
				<?php /*?><td><a href="articulos.php?id=<?php echo base64_encode($row['IdVTAFactura']);?>&tl=1" target="_blank" title="<?php echo $row['DeVTAFactura'];?>"><?php echo $row['IdVTAFactura'];?></a></td>
				<td><?php echo number_format($row['ValorVTAFactura'],2);?></td><?php */?>
				<td><?php echo $row['FechaCreacionLLamada'];?></td>
				<td><?php echo $row['FechaCierreLLamada'];?></td>
				<td><?php echo $row['FechaInicioActividad'];?></td>
				<td><p class='<?php echo $DVenc[0];?>'><?php echo $DVenc[1];?></p></td>
				<td><span <?php if($row['IdEstadoLlamada']=='-3'){echo "class='label label-info'";}elseif($row['IdEstadoLlamada']=='-2'){echo "class='label label-warning'";}else{echo "class='label label-danger'";}?>><?php echo $row['DeEstadoLlamada'];?></span></td>
				<td><span <?php if($row['IdEstadoServicio']=='0'){echo "class='label label-warning'";}elseif($row['IdEstadoServicio']=='1'){echo "class='label label-primary'";}else{echo "class='label label-danger'";}?>><?php echo $row['DeEstadoServicio'];?></span></td>
				
				<td class="text-center form-inline w-80">
					<button type="button" title="Mostrar costos asociados" class="btn btn-primary btn-xs" onClick="MostrarCostos('<?php echo $row['ID_LlamadaServicio'];?>','<?php echo $row['DocNum'];?>');"><i class="fa fa-money"></i></button>
					<button type="button" title="Mostrar actividades" class="btn btn-success btn-xs" onClick="CargarAct('<?php echo $row['ID_LlamadaServicio'];?>','<?php echo $row['DocNum'];?>');"><i class="fa fa-tags"></i></button>
				</td>
				
			</tr>
		<?php  $i++;}?>
		</tbody>
	</table>
</div>
<script>
 $(document).ready(function(){
	 
	let table_detail_lvl2 = $('.dataTables-Details-lvl2').DataTable({
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
 });
	
function MostrarCostos(docentry, docnum){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		async: false,
		url: "md_articulos_documentos.php",
		data:{
			pre:2,
			DocEntry:docentry
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#TituloModal').html('Costos asociados OT: ' + docnum);
			$('#myModal').modal("show");
		}
	});
}
</script>