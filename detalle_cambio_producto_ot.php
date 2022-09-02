<?php 
require_once("includes/conexion.php");
PermitirAcceso(317);
$sw=0;
//$Proyecto="";
//$Almacen="";
$CardCode="";
$type=1;
$Estado=1;//Abierto
$Num=0;

$SQL=Seleccionar("tbl_CambioProductoOrdenVenta","*","Usuario='".strtolower($_SESSION['CodUser'])."'");
if($SQL){
	$Num=sqlsrv_num_rows($SQL);
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
	
function BorrarLinea(LineNum){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea eliminar este item? Este proceso no se puede revertir.')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=28&linenum="+json,		
			success: function(response){
				window.location.href="detalle_cambio_producto_ot.php?<?php echo $_SERVER['QUERY_STRING'];?>";
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
	<table width="100%" class="table table-bordered dataTables-example">
		<thead>
			<tr>
				<th>#</th>
				<th class="text-center form-inline w-80"><div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div> <button type="button" id="btnBorrarLineas" title="Borrar lineas" class="btn btn-danger btn-xs disabled" onClick="BorrarLinea();"><i class="fa fa-trash"></i></button></th>
				<th>Serie OT</th>
				<th>Llamada servicio</th>
				<th>Estado OT</th>
				<th>Tipo llamada</th>
				<th>Nombre cliente</th>
				<th>Sucursal cliente</th>	
				<th>ID Orden venta</th>
				<th>Fecha servicio OV</th>		
				<th>Estado OV</th>
				<th>Linea OV</th>
				<th>Articulo origen</th>
				<th>Nombre articulo origen</th>
				<th>Cant origen</th>
				<th>Und medida origen</th>
				<th>Articulo destino</th>
				<th>Nombre articulo destino</th>
				<th>Cant destino</th>
				<th>Und medida destino</th>
				<th>Cant litros</th>
				<th>Dosificación</th>				
				<th>Servicio llamada</th>
				<th>Metodo aplicación</th>
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
			<td><?php echo $row['SerieOT'];?></td>
			<td><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_Llamada']);?>&tl=1" target="_blank"><?php echo $row['ID_OrdenServicio'];?></a></td>
			<td><span <?php if($row['EstadoOrdenServicio']=='Abierto'){echo "class='label label-info'";}else{echo "class='label label-danger'";}?>><?php echo $row['EstadoOrdenServicio'];?></span></td>
			<td><?php echo $row['TipoLlamada'];?></td>
			<td><?php echo $row['DeCliente'];?></td>
			<td><?php echo $row['IdSucursalCliente'];?></td>
			<td><a href="orden_venta.php?id=<?php echo base64_encode($row['DocEntryOV']);?>&tl=1" target="_blank"><?php echo $row['ID_OV'];?></a></td>
			<td><?php if(is_object($row['FechaServicioOV'])&&($row['FechaServicioOV']!="")){echo $row['FechaServicioOV']->format('Y-m-d');}else{echo $row['FechaServicioOV'];}?></td>
			<td><span <?php if($row['EstadoOV']=='Abierto'){echo "class='label label-info'";}else{echo "class='label label-danger'";}?>><?php echo $row['EstadoOV'];?></span></td>			
			<td><?php echo $row['NoLineaOV'];?></td>
			<td><?php echo $row['IdArticuloOV'];?></td>
			<td><?php echo $row['DeArticuloOV'];?></td>
			<td><span class="badge badge-success"><?php echo number_format($row['CantArticuloOV'],2);?></span></td>
			<td><?php echo $row['UndMedArtOV'];?></td>
			<td><?php echo $row['IdArticuloAcambiar'];?></td>
			<td><?php echo $row['DeArticuloAcambiar'];?></td>
			<td><span class="badge badge-success"><?php echo number_format($row['FormulaArticuloOV'],2);?></span></td>
			<td><?php echo $row['UndMedArtACambiar'];?></td>			
			<td><?php echo number_format($row['CantLitrosArticuloOV'],2);?></td>
			<td><?php echo number_format($row['DosifiArticuloOV'],2);?></td>			
			<td><?php echo $row['ServicioLlamada'];?></td>
			<td><?php echo $row['MetodoAplica'];?></td>
			<td><span class="<?php if($row['Validacion']=="OK"){echo "badge badge-primary";}else{echo "badge badge-danger";}?>"><?php echo $row['Validacion'];?></span></td>
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
				dataSrc: 6
			}
		});
		<?php if($Num>0){?>
		 	window.parent.document.getElementById('Ejecutar').disabled=false;
	 	<?php }?>
	});
</script>
</body>
</html>
<?php 
	sqlsrv_close($conexion);
?>