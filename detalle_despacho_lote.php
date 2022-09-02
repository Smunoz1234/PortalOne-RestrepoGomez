<?php 
require_once("includes/conexion.php");
PermitirAcceso(319);
$sw=0;
//$Proyecto="";
//$Almacen="";
$CardCode="";
$type=1;
$Estado=1;//Abierto

$SQL=Seleccionar("tbl_DespachoLoteDetalleCarrito","*","Usuario='".$_SESSION['CodUser']."'");
if($SQL){
	$sw=1;
}

if(isset($_GET['id'])&&($_GET['id']!="")){
	if($_GET['type']==1){
		$type=1;
	}else{
		$type=$_GET['type'];
	}
	if($type==1){//Creando Orden de Venta
		
	}
}
?>
<!doctype html>
<html>
<head>
<?php include_once("includes/cabecera.php"); ?>
<style>
	.ibox-content{
		padding: 0px !important;	
	}
	body{
		background-color: #ffffff;
		overflow-x: auto;
	}
	.form-control{
		width: auto;
		height: 28px;
	}	
</style>
<script>
var json=[];
var cant=0;

function BorrarLinea(){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea eliminar este item? Este proceso no se puede revertir.')){
		$('.ibox-content', window.parent.document).toggleClass('sk-loading',true);
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=33&linenum="+json,
			success: function(response){
				MostrarResumen();
				$('.ibox-content', window.parent.document).toggleClass('sk-loading',false);
			}
		});
	}	
}

function MostrarResumen(){
	$('.ibox-content', window.parent.document).toggleClass('sk-loading',true);
	$.ajax({
		type: "GET",
		url: "despacho_lote_art.php?user=<?php echo $_SESSION['CodUser'];?>",
		success: function(response){
			$('#dv_Articulos', window.parent.document).html(response);
			window.location.href="detalle_despacho_lote.php?<?php echo $_SERVER['QUERY_STRING'];?>";
			$('.ibox-content', window.parent.document).toggleClass('sk-loading',false);			
		}
	});
}
	
function ActualizarDatos(name,id,line){//Actualizar datos asincronicamente
	$.ajax({
		type: "GET",
		url: "registro.php?P=36&doctype=10&type=1&name="+name+"&value="+Base64.encode(document.getElementById(name+id).value)+"&line="+line,
		success: function(response){
			if(response!="Error"){
				window.parent.document.getElementById('TimeAct').innerHTML="<strong>Actualizado:</strong> "+response;
			}
		}
	});
}

function Seleccionar(ID){
	var btnBorrarLineas=document.getElementById('btnBorrarLineas');
	var Check = document.getElementById('chkSel'+ID).checked;
	var sw=-1;
	json.forEach(function(element,index){
//		console.log(element,index);
//		console.log(json[index])deta
		if(json[index]==ID){
			sw=index;
		}
		
	});
	
	if(sw>=0){
		json.splice(sw, 1);
		cant--;
	}else if(Check){
		json.push(ID);
		cant++;
	}
	if(cant>0){
		$("#btnBorrarLineas").removeClass("disabled");
	}else{
		$("#btnBorrarLineas").addClass("disabled");
	}
	
	//console.log(json);
}

function SeleccionarTodos(){
	var Check = document.getElementById('chkAll').checked;
	if(Check==false){
		json=[];
		cant=0;
		$("#btnBorrarLineas").addClass("disabled");
	}
	$(".chkSel").prop("checked", Check);
	if(Check){
		$(".chkSel").trigger('change');
	}		
}

</script>
</head>

<body>
<form id="from" name="form">
	<div class="">
	<table width="100%" class="table table-bordered  dataTables-example">
		<thead>
			<tr>
				<th>#</th>
				<th class="text-center"><div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div> <button type="button" id="btnBorrarLineas" title="Borrar lineas" class="btn btn-danger btn-xs disabled" onClick="BorrarLinea();"><i class="fa fa-trash"></i></button></th>
				<th>Llamada servicio</th>
				<th>Serie</th>
				<th>Tipo llamada</th>
				<th>Cliente</th>
				<th>Sucursal</th>
				<th>Fecha llamada</th>	
				<th>Fecha actividad</th>
				<th>Estado actividad</th>
				<th>Técnico</th>
				<th>Almacén</th>
				<th>Orden de venta</th>		
				<th>Entrega de venta</th>
				<th>Validación</th>
				<th>Ejecución</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		if($sw==1){
			$i=1;
			while($row=sqlsrv_fetch_array($SQL)){
		?>
		<tr>
			<td class="text-center"><?php echo $i;?></td>
			<td class="text-center">
				<div class="checkbox checkbox-success no-margins">
					<input type="checkbox" class="chkSel" id="chkSel<?php echo $row['ID'];?>" value="" onChange="Seleccionar('<?php echo $row['ID'];?>');" aria-label="Single checkbox One"><label></label>
				</div>
			</td>
			<td><a href="llamada_servicio.php?id=<?php echo base64_encode($row['DocEntryLlamada']);?>&tl=1" target="_blank"><?php echo $row['DocNumLlamada'];?></a></td>
			<td><?php echo $row['DeSerie'];?></td>
			<td><?php echo $row['DeTipoLlamada'];?></td>
			<td><?php echo $row['DeCliente'];?></td>
			<td><?php echo $row['SucursalCliente'];?></td>
			<td><?php echo ($row['FechaLlamada']!="") ? $row['FechaLlamada']->format('Y-m-d') : "";?></td>
			<td><?php echo ($row['FechaActividad']!="") ? $row['FechaActividad']->format('Y-m-d') : "";?></td>
			<td><?php echo $row['DeEstadoActividad'];?></td>
			<td><?php echo $row['DeTecnico'];?></td>
			<td><?php echo $row['Almacen'];?></td>
			<td><a href="orden_venta.php?id=<?php echo base64_encode($row['DocEntryOrdenVenta']);?>&tl=1" target="_blank"><?php echo $row['DocNumOrdenVenta'];?></a></td>
			<td><a href="entrega_venta.php?id=<?php echo base64_encode($row['DocEntryEntregaVenta']);?>&tl=1" target="_blank"><?php echo $row['DocNumEntregaVenta'];?></a></td>
			<td><span class="<?php if(strstr($row['Validacion'],"OK")){echo "badge badge-primary";}else{echo "badge badge-danger";}?>"><?php echo $row['Validacion'];?></span></td>
			<td class="<?php if($row['Integracion']==0){ echo "bg-warning";}elseif($row['Integracion']==2){echo "bg-danger";}else{echo "bg-primary";}?>"><?php echo $row['Ejecucion'];?></td>
		</tr>
		<?php 
			$i++;}
		}
		?>
		</tbody>
	</table>
	</div>
</form>
<script>
	 $(document).ready(function(){
		 $(".alkin").on('click', function(){
				 $('.ibox-content').toggleClass('sk-loading');
			}); 
		  $(".select2").select2();
		 
		 <?php $j=1;
		 while($j<$i){?>
		 $('#FechaHoraInicioEjecucion<?php echo $j;?>').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			format: 'yyyy-mm-dd 00:00',
			todayHighlight: true
		});
		 
		 $('#FechaHoraFinEjecucion<?php echo $j;?>').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			format: 'yyyy-mm-dd 00:00',
			todayHighlight: true
		});
			 
		 <?php $j++;}?>
		 
		$('.dataTables-example').DataTable({
			searching: false,
			paging: false,
			fixedHeader: true,
			rowGroup: {
				dataSrc: 10
			}

		});
	});
</script>
</body>
</html>
<?php 
	sqlsrv_close($conexion);
?>