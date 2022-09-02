<?php 
require_once("includes/conexion.php");
PermitirAcceso(311);
$sw=0;
//$Proyecto="";
//$Almacen="";
$CardCode="";
$type=1;
$Estado=1;//Abierto

$SQL=Seleccionar("tbl_CreacionProgramaOrdenesServicio","*","Usuario='".strtolower($_SESSION['User'])."'","DeCliente, SucursalCliente");
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
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=26&linenum="+json,		
			success: function(response){
				window.location.href="detalle_creacion_ot_lote.php?<?php echo $_SERVER['QUERY_STRING'];?>";
				window.parent.ConsultarCant();
			}
		});
	}	
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
				<th>Nombre cliente</th>
				<th>Sucursal cliente</th>
				<th>Lista de materiales</th>
				<th>Nombre de lista de materiales</th>
				<th>Periodo</th>
				<th>Sucursal</th>	
				<th>Núm de OT</th>
				<th>Núm de OV</th>		
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
			<td><?php echo $row['DeCliente'];?></td>
			<td><?php echo $row['SucursalCliente'];?></td>
			<td><a href="articulos.php?id=<?php echo base64_encode($row['IdArticuloLMT']);?>&tl=1" target="_blank"><?php echo $row['IdArticuloLMT'];?></a></td>
			<td><?php echo $row['NombreArticuloLMT'];?></td>			
			<td><?php if(is_object($row['Periodo'])&&($row['Periodo']!="")){echo $row['Periodo']->format('Y-m-d');}else{echo $row['Periodo'];}?></td>			
			<td><?php echo $row['Sucursal'];?></td>
			<td><?php if($row['ID_LLamadaServicio']!=0){?><a href="llamada_servicio.php?id=<?php echo base64_encode($row['DocEntryLlamada']);?>&tl=1" target="_blank"><?php echo $row['ID_LLamadaServicio'];?></a><?php }else{echo $row['ID_LLamadaServicio'];}?></td>
			<td><?php if($row['ID_OrdenVenta']!=0){?><a href="orden_venta.php?id=<?php echo base64_encode($row['DocEntryOV']);?>&tl=1" target="_blank"><?php echo $row['ID_OrdenVenta'];?></a><?php }else{echo $row['ID_OrdenVenta'];}?></td>
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
		 
		$('.dataTables-example').DataTable({
			searching: false,
			paging: false,
			fixedHeader: true,
			rowGroup: {
				dataSrc: [2,3]
			}

		});
	});
</script>
</body>
</html>
<?php 
	sqlsrv_close($conexion);
?>