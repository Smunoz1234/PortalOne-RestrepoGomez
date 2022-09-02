<?php 
require_once("includes/conexion.php");
PermitirAcceso(311);
$sw=0;
//$Proyecto="";
//$Almacen="";
$CardCode="";
$type=1;
$Estado=1;//Abierto

$SQL=Seleccionar("uvw_tbl_CierreOTLlamadasCarrito","*","Usuario='".strtolower($_SESSION['User'])."'",'ID_OrdenServicio');
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
	.bg-primary{
		background-color: #1ab394 !important;
		color: #ffffff !important;
	}
	.bg-info{
		background-color: #23c6c8 !important;
		color: #ffffff !important;
	}
	.bg-danger{
		background-color: #ed5565 !important;
		color: #ffffff !important;
	}
	.table > tbody > tr > td{
		padding: 1px !important;
		vertical-align: middle;
	}
	.select2-container{ width: 100% !important; }
	textarea.form-control{
		height: 28px;
	}
</style>
<script>
var dataJson=[];
var cant=0;

function BorrarLinea(){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea eliminar este item? Este proceso no se puede revertir.')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=25&tdoc=2&linenum="+dataJson,			
			success: function(response){
				window.location.href="detalle_cierre_ot_lote_llamadas.php?<?php echo $_SERVER['QUERY_STRING'];?>";
				window.parent.document.getElementById("DG_Actividades").src="detalle_cierre_ot_lote_actividades.php";
				window.parent.ConsultarCant();
			}
		});
	}	
}

function ActualizarDatos(name,id,line){//Actualizar datos asincronicamente
	$.ajax({
		type: "GET",
		url: "registro.php?P=36&doctype=10&type=2&name="+name+"&value="+Base64.encode(document.getElementById(name+id).value)+"&line="+line,
		success: function(response){
			if(response!="Error"){
				window.parent.document.getElementById('TimeAct').innerHTML="<strong>Actualizado:</strong> "+response;
			}
		}
	});
}
	
function CargarAct(ID){//Cargar las actividades de esta llamada
	if(ID!=""){
		window.parent.document.getElementById("DG_Actividades").src="detalle_cierre_ot_lote_actividades.php?ot="+ID;	
	}else{
		window.parent.document.getElementById("DG_Actividades").src="detalle_cierre_ot_lote_actividades.php";	
	}	
}

function Seleccionar(ID){
	var btnBorrarLineas=document.getElementById('btnBorrarLineas');
	var Check = document.getElementById('chkSel'+ID).checked;
	var sw=-1;
	dataJson.forEach(function(element,index){
//		console.log(element,index);
//		console.log(dataJson[index])deta
		if(dataJson[index]==ID){
			sw=index;
		}
		
	});
	
	if(sw>=0){
		dataJson.splice(sw, 1);
		cant--;
	}else if(Check){
		dataJson.push(ID);
		cant++;
	}
	if(cant>0){
		$("#btnBorrarLineas").removeClass("disabled");
	}else{
		$("#btnBorrarLineas").addClass("disabled");
	}
	
	//console.log(dataJson);
}

function SeleccionarTodos(){
	var Check = document.getElementById('chkAll').checked;
	if(Check==false){
		dataJson=[];
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
	<table width="100%" class="table table-bordered">
		<thead>
			<tr>
				<th>#</th>
				<th class="text-center form-inline w-80">
					<div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div><button type="button" id="btnBorrarLineas" title="Borrar lineas" class="btn btn-danger btn-xs disabled" onClick="BorrarLinea();"><i class="fa fa-trash"></i></button>
				</th>
				<th>Abrir <button type="button" title="Mostrar todas las actividades" class="btn btn-success btn-xs" onClick="CargarAct('');"><i class="fa fa-list"></i></button></th>
				<th>Número de OT</th>
				<th>Nombre cliente</th>
				<th>Sucursal cliente</th>	
				<th>Estado servicio</th>		
				<th>Cancelado por</th>
				<th>Anexo</th>
				<th>Comentarios de cierre</th>
				<th>Estado OT</th>
				<th>Validación</th>
				<th>Ejecución</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		if($sw==1){
			$i=1;
			while($row=sqlsrv_fetch_array($SQL)){
				
				//Estado servicio llamada
				$SQL_EstServLlamada=Seleccionar('uvw_Sap_tbl_LlamadasServiciosEstadoServicios','*','','DeEstadoServicio');
				
				//Cancelado por llamada
				$SQL_CanceladoPorLlamada=Seleccionar('uvw_Sap_tbl_LlamadasServiciosCanceladoPor','*','','DeCanceladoPor','DESC');
		?>
		<tr>
			<td class="text-center"><?php echo $i;?></td>
			<td class="text-center">
				<div class="checkbox checkbox-success no-margins">
					<input type="checkbox" class="chkSel" id="chkSel<?php echo $row['ID'];?>" value="" onChange="Seleccionar('<?php echo $row['ID'];?>');" aria-label="Single checkbox One"><label></label>
				</div>
			</td>
			<td class="text-center"><button type="button" title="Mostrar actividades" class="btn btn-success btn-xs" onClick="CargarAct('<?php echo $row['ID_Llamada'];?>');"><i class="fa fa-eye"></i></button></td>
			<td><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_Llamada']);?>&tl=1" target="_blank" id="OrdenServicio<?php echo $i;?>"><?php echo $row['ID_OrdenServicio'];?></a></td>
			<td><input size="50" type="text" id="NombreCliente<?php echo $i;?>" name="NombreCliente[]" class="form-control" readonly value="<?php echo $row['NombreCliente'];?>"></td>
			<td><input size="50" type="text" id="SucursalCliente<?php echo $i;?>" name="SucursalCliente[]" class="form-control" readonly value="<?php echo $row['IdSucursalCliente'];?>"></td>
			<td>
				<select id="EstadoServicio<?php echo $i;?>" name="EstadoServicio[]" class="form-control" onChange="ActualizarDatos('EstadoServicio',<?php echo $i;?>,<?php echo $row['ID'];?>);">
				  <?php while($row_EstServLlamada=sqlsrv_fetch_array($SQL_EstServLlamada)){?>
						<option value="<?php echo $row_EstServLlamada['IdEstadoServicio'];?>" <?php if((isset($row['EstadoServicio']))&&(strcmp($row_EstServLlamada['IdEstadoServicio'],$row['EstadoServicio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstServLlamada['DeEstadoServicio'];?></option>
				  <?php }?>
				</select>
			</td>
			<td>
				<select id="CanceladoPor<?php echo $i;?>" name="CanceladoPor[]" class="form-control" onChange="ActualizarDatos('CanceladoPor',<?php echo $i;?>,<?php echo $row['ID'];?>);">
				  <?php while($row_CanceladoPorLlamada=sqlsrv_fetch_array($SQL_CanceladoPorLlamada)){?>
						<option value="<?php echo $row_CanceladoPorLlamada['IdCanceladoPor'];?>" <?php if((isset($row['CanceladoPor']))&&(strcmp($row_CanceladoPorLlamada['IdCanceladoPor'],$row['CanceladoPor'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_CanceladoPorLlamada['DeCanceladoPor'];?></option>
				  <?php }?>
				</select>
			</td>
			<td><input size="15" type="text" id="Anexo<?php echo $i;?>" name="Anexo[]" class="form-control" readonly value="<?php echo $row['AnexoOrdenServicio'];?>"></td>
			<td>
				<textarea cols="70" maxlength="2000" id="ComentariosCierre<?php echo $i;?>" name="ComentariosCierre[]" class="form-control" onChange="ActualizarDatos('ComentariosCierre',<?php echo $i;?>,<?php echo $row['ID'];?>);"><?php echo $row['ComentariosCierre'];?></textarea>
			</td>
			<td><input size="15" type="text" id="EstadoOrdenServicio<?php echo $i;?>" name="EstadoOrdenServicio[]" class="form-control <?php if($row['EstadoOrdenServicio']=="Abierto"){echo "bg-danger";}else{echo "bg-primary";}?>" readonly value="<?php echo $row['EstadoOrdenServicio'];?>"></td>
			<td><span class="<?php if(strstr($row['Validacion'],"OK")){echo "badge badge-primary";}else{echo "badge badge-danger";}?>"><?php echo $row['Validacion'];?></span></td>
			<td><span class="<?php if($row['Integracion']==0){echo "badge badge-warning";}elseif($row['Integracion']==2){"badge badge-danger";}else{echo "badge badge-primary";}?>"><?php echo $row['Ejecucion'];?></span></td>
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
	});
</script>
</body>
</html>
<?php 
	sqlsrv_close($conexion);
?>