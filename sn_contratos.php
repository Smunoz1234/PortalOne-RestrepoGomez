<?php  
require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
if(isset($_GET['id'])&&$_GET['id']!=""){
	$CodCliente=base64_decode($_GET['id']);
}else{
	$CodCliente="";
}
//Contrato
$SQL_Contrato=Seleccionar('uvw_Sap_tbl_Contratos','*',"[CodigoCliente]='".$CodCliente."'");

?>
<div class="form-group">
	<div class="col-lg-12">
		<div class="table-responsive">
		<table class="table table-striped table-bordered table-hover dataTables501" >
			<thead>
			<tr>
				<th>ID Contrato</th>
				<th>Descripción</th>
				<th>Nombre sucursal</th>
				<th>Dirección sucursal</th>
				<th>Fecha inicio</th>
				<th>Fecha final</th>
				<th>Vigencia</th>
				<th>Modelo contrato</th>
				<th>Núm. impreso</th>
				<th>Estado</th>
				<th>Acciones</th>
			</tr>
			</thead>
			<tbody>
			<?php while($row_Contrato=sql_fetch_array($SQL_Contrato)){
				?>
				 <tr>
					<td><?php echo $row_Contrato['ID_Contrato'];?></td>
					<td><?php echo $row_Contrato['DE_Contrato'];?></td>
					<td><?php echo $row_Contrato['CDU_NombreSucursal'];?></td>
					<td><?php echo $row_Contrato['CDU_DirSucursal'];?></td>
					<td><?php echo $row_Contrato['FechaInicioContrato']->format('Y-m-d');?></td>
					<td><?php echo $row_Contrato['FechaFinContrato']->format('Y-m-d');?></td>
					<td><?php echo $row_Contrato['CDU_VigServicio'];?></td>
					<td><?php echo $row_Contrato['IdModeloContrato'];?></td>
					<td><?php echo $row_Contrato['CDU_NoContratoImp'];?></td>
					<td><?php echo $row_Contrato['DeEstadoContrato'];?></td>
					<td><a href="contratos.php?id=<?php echo base64_encode($row_Contrato['ID_Contrato']);?>&tl=1" class="btn btn-link btn-xs" target="_blank"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
				</tr>
			<?php }?>
			</tbody>
		</table>
  		</div>
	</div>
</div>	
<script>
 $(document).ready(function(){
	$('.dataTables501').DataTable({
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