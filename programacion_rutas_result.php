<?php
require_once( "includes/conexion.php" );
PermitirAcceso(312);
//require_once("includes/conexion_hn.php");

$Type=5; //5 -> Consultar despues de integrar en el SP. 7 -> Consultar antes de enviar lo que esta pendiente

if(isset($_POST['idEvento'])&&$_POST['idEvento']!=""){
	$idEvento=base64_decode($_POST['idEvento']);
	if(isset($_POST['msg'])){
		$Mensaje=$_POST['msg'];
	}else{
		$Mensaje="Pendiente por integrar";
		$Type=7;
	}
	$Estado=isset($_POST['estado']) ? $_POST['estado'] : 0;
}else{
	$idEvento="";
	$Mensaje="";
	$Estado=0;
}

$Param=array(
	$Type,
	"'".$_SESSION['CodUser']."'",
	"'".$idEvento."'",
);

$SQL=EjecutarSP("usp_InsertarActividadesRutasToSAP_Core",$Param);

?>
<form id="frmActividad" method="post">
<div class="modal-content">
  <div class="modal-header">
    <h5 class="modal-title">Resultado de la operación: <p class="<?php if($Estado==1){echo "text-primary";}else{echo "text-danger";}?>"> <?php echo $Mensaje;?></p></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
  </div>
  <div class="modal-body">	
	<div class="table-responsive">
		<table class="datatables-demo table table-striped table-bordered table-hover table-sm">
			<thead>
			<tr>
				<th>#</th>
				<th>Tipo transacción</th>
				<th>Integración a SAP</th>
				<th>Respuesta de integración</th>
				<th>Llamada de servicio</th>
				<th>Actividad</th>
				<th>Cliente</th>
				<th>Sucursal cliente</th>
				<th>Técnico</th>
			</tr>
			</thead>
			<tbody>
			<?php $i=1;
				$Int=0;
				while($row=sqlsrv_fetch_array($SQL)){
				if($row['Integracion']==2){$Int=2;}?>
				 <tr>
					 <td><?php echo $i;?></td>
					 <td><?php echo $row['TipoTransaccion'];?></td>
					 <td><span class="<?php if($row['IntegracionSAP']=="NO INTEGRADO"){echo "badge badge-warning";}elseif($row['IntegracionSAP']=="EXITOSA"){echo "badge badge-success";}else{echo "badge badge-danger";}?>"><?php echo $row['IntegracionSAP'];?></span></td>
					 <td><?php echo $row['RespuestaIntegracion'];?></td>
					 <td><?php echo $row['IdLlamadaServicio'];?></td>
					 <td><?php echo $row['IdActividad'];?></td>
					 <td><?php echo $row['NombreCliente'];?></td>
					 <td><?php echo $row['SucursalCliente'];?></td>
					 <td><?php echo $row['EmpleadoActividad'];?></td>
				</tr>
			<?php $i++;}?>
			</tbody>
		</table>
	</div>	
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-secondary md-btn-flat" data-dismiss="modal" <?php if($Estado==1){?>onClick="Reload();"<?php }?>>Cerrar</button>
	<?php if(($Type==5&&$Int==2)||($Type==5&&$Estado!=1)){?><button type="button" class="btn btn-primary md-btn-flat" onClick="EjecutarProceso();"><i class="fas fa-sync"></i> Volver a enviar</button><?php }?>
  </div>
</div>
</form>
<script>
$(document).ready(function() {
	 $('.datatables-demo').DataTable({
		pageLength: 10,
		info: false,
		language: {
			"decimal":        "",
			"emptyTable":     "No se encontraron resultados.",
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
function Reload(){
	blockUI();
	let RecursosList=window.sessionStorage.getItem('ResourceList')
	let Param='';
//	console.log('RecursosList',RecursosList)
	if(RecursosList){
//		console.log(Recursos)
		RecursosArray = RecursosList.split(",");
		n = RecursosArray.map((a)=>{return `Recursos[]=${a}`});
		Param='&'+n.join('&');		
	}
//	console.log('Param',Param)
	window.location = window.location.href+'&reload=true'+Param;
//	location.reload();
}
</script>