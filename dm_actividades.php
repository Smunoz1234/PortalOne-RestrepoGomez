<?php  
require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
if(isset($_GET['id'])&&$_GET['id']!=""){
	$IdDocMarketing=base64_decode($_GET['id']);
	$IdDocType=$_GET['objtype'];
}else{
	$IdDocMarketing="";
	$IdDocType="";
}
//Actividades
$SQL_Actividades=Seleccionar('uvw_Sap_tbl_Actividades','*',"[DocMarkDocEntry]='".$IdDocMarketing."' and [DocMarkDocType]='".$IdDocType."'","[ID_Actividad]","DESC");

?>
<div class="form-group">
	<div class="col-lg-12">
		<div class="table-responsive">
		<table class="table table-striped table-bordered table-hover dataTables3" >
			<thead>
			<tr>
				<th>Núm.</th>
				<th>Asignado por</th>
				<th>Asignado a</th>
				<th>Asunto</th>
				<th>Sucursal</th>
				<th>Fecha creación</th>
				<th>Fecha actividad</th>
				<th>Fecha limite</th>
				<th>Dias venc.</th>
				<th>Orden servicio</th>
				<th>Estado</th>
				<th>Acciones</th>
			</tr>
			</thead>
			<tbody>
			<?php while($row_Actividades=sql_fetch_array($SQL_Actividades)){
					$DVenc=DiasTranscurridos(date('Y-m-d'),$row_Actividades['FechaFinActividad']);
				?>
				 <tr>
					<td><?php echo $row_Actividades['ID_Actividad'];?></td>
					<td><?php echo $row_Actividades['DeAsignadoPor'];?></td>
					<td><?php if($row_Actividades['NombreEmpleado']!=""){echo $row_Actividades['NombreEmpleado'];}else{echo "(Sin asignar)";}?></td>
					<td><?php echo $row_Actividades['DE_AsuntoActividad'];?></td>
					<td><?php echo $row_Actividades['NombreSucursal'];?></td>
					<td><?php if($row_Actividades['FechaCreacion']!=""){ echo $row_Actividades['FechaCreacion'];}else{?><p class="text-muted">--</p><?php }?></td>
					<td><?php if($row_Actividades['FechaHoraInicioActividad']!=""){ echo $row_Actividades['FechaHoraInicioActividad']->format('Y-m-d');}else{?><p class="text-muted">--</p><?php }?></td>
					<td><?php if($row_Actividades['FechaHoraFinActividad']!=""){ echo $row_Actividades['FechaHoraFinActividad']->format('Y-m-d');}else{?><p class="text-muted">--</p><?php }?></td>
					<td><p class='<?php echo $DVenc[0];?>'><?php echo $DVenc[1];?></p></td>
					<td><?php if($row_Actividades['ID_OrdenServicioActividad']!=0){?><a href="llamada_servicio.php?id=<?php echo base64_encode($row_Actividades['ID_LlamadaServicio']);?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode($row_Actividades['DocMarkPage'].".php");?>"><?php echo $row_Actividades['ID_OrdenServicioActividad'];?></a><?php }else{echo "--";}?></td>
					<td><span <?php if($row_Actividades['IdEstadoActividad']=='N'){echo "class='label label-info'";}else{echo "class='label label-danger'";}?>><?php echo $row_Actividades['DeEstadoActividad'];?></span></td>	
					<td><a href="actividad.php?id=<?php echo base64_encode($row_Actividades['ID_Actividad']);?>&tl=1" class="btn btn-success btn-xs" target="_blank"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
				</tr>
			<?php }?>
			</tbody>
		</table>
  		</div>
	</div>
</div>	
<script>
 $(document).ready(function(){
	$('.dataTables3').DataTable({
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