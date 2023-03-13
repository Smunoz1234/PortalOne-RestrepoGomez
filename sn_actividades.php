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

if($objtype==191){
	//Llamadas de servicios
	$Where="[ID_LlamadaServicio]='".$id."'";
}else{
	//Socios de negocios
	$Where="[ID_CodigoCliente]='".$id."'";
}

//Actividades
$SQL_Actividades=Seleccionar('uvw_Sap_tbl_Actividades','
	   [ID_Actividad]
      ,[ID_OrdenServicioActividad]
      ,[ID_LlamadaServicio]
      ,[IdClaseActividad]
      ,[DeClaseActividad]
      ,[ID_TipoActividad]
      ,[DE_TipoActividad]
      ,[ID_AsuntoActividad]
      ,[DE_AsuntoActividad]
      ,[ID_EmpleadoActividad]
      ,[NombreEmpleado]
      ,[ID_CodigoCliente]
      ,[NombreCliente]
      ,[ID_ContactoCliente]
      ,[AsuntoLlamada]
	  ,[FechaCreacion]
	  ,[FechaInicioActividad]
	  ,[FechaHoraInicioActividad]
	  ,[FechaFinActividad]
	  ,[FechaHoraFinActividad]
	  ,[NombreSucursal]
	  ,[NombreCiudad]
	  ,[IdTipoEstadoActividad]
	  ,[IdEstadoActividad]
      ,[DeEstadoActividad]
	  ,[IdAsignadoPor]
      ,[DeAsignadoPor]
	  ,[DeTipoEstadoActividad]'
	  ,$Where,"[ID_Actividad]","DESC");

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
				<th>Fecha inicio actividad</th>
				<th>Fecha fin actividad</th>
				<th>Dias venc.</th>
				<th>Orden servicio</th>
				<th>Estado servicio Actividad</th>
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
					<td><?php if($row_Actividades['ID_OrdenServicioActividad']!=0){?><a href="llamada_servicio.php?id=<?php echo base64_encode($row_Actividades['ID_LlamadaServicio']);?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('socios_negocios.php');?>"><?php echo $row_Actividades['ID_OrdenServicioActividad'];?></a><?php }else{echo "--";}?></td>							
					<td><?php echo $row_Actividades['DeTipoEstadoActividad'];?></td>
					<td><span <?php if($row_Actividades['IdEstadoActividad']=='N'){echo "class='label label-info'";}else{echo "class='label label-danger'";}?>><?php echo $row_Actividades['DeEstadoActividad'];?></span></td>	
					<td><a href="actividad.php?id=<?php echo base64_encode($row_Actividades['ID_Actividad']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('socios_negocios.php');?>&tl=1" class="btn btn-success btn-xs" target="_blank"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
				</tr>
			<?php }?>
			</tbody>
		</table>
  		</div>
	</div>
</div>	
<script>
 $(document).ready(function(){
	 
	// Setup - add a text input to each footer cell
//    $('.dataTables3 thead tr').clone(true).appendTo( '.dataTables3 thead' );
//    $('.dataTables3 thead tr:eq(1) th').each( function (i) {
//        var title = $(this).text(); //Agregar en el placeholder si quiere que salga el nombre
//        $(this).html( '<input type="text" class="form-control" placeholder="Buscar" />' );
// 
//        $( 'input', this ).on( 'keyup change', function () {
//            if ( table.column(i).search() !== this.value ) {
//                table
//                    .column(i)
//                    .search( this.value )
//                    .draw();
//            }
//        } );
//    });
	 
	var table = $('.dataTables3').DataTable({
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