<?php 
require_once("includes/conexion.php");
PermitirAcceso(311);
$sw=0;
//$Proyecto="";
//$Almacen="";
$CardCode="";
$type=1;
$Estado=1;//Abierto

if(isset($_GET['ot'])&&($_GET['ot']!="")){
	$FiltroOT="AND ID_Llamada='".$_GET['ot']."'";
}else{
	$FiltroOT="";
}

$SQL=Seleccionar("uvw_tbl_CierreOTActividadesCarrito","*","Usuario='".strtolower($_SESSION['User'])."' $FiltroOT",'ID_OrdenServicio');
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
</style>
<script>
var json=[];
var cant=0;
	
function BorrarLinea(LineNum){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea eliminar este item? Este proceso no se puede revertir.')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=25&tdoc=1&linenum="+json,		
			success: function(response){
				window.location.href="detalle_cierre_ot_lote_actividades.php?<?php echo $_SERVER['QUERY_STRING'];?>";
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
				window.parent.document.getElementById('TimeAct2').innerHTML="<strong>Actualizado:</strong> "+response;
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

function ValidarFechas(id,t){
	var FechaIni = document.getElementById("FechaHoraInicioActividad"+id);
	var FechaFin = document.getElementById("FechaHoraFinEjecucion"+id);
	
	var FechaInicial = new Date(FechaIni.value)
	var FechaFinal = new Date(FechaFin.value)
	
	var Tiempo = FechaFinal - FechaInicial
	
	if(Tiempo < 0){
		swal({
			title: '¡Error!',
			text: 'La fecha final no debe ser mayor a la fecha inicial',
			type: 'error'
		});
		if(t===1){
			FechaIni.value='';
			FechaIni.focus()
		}else{
			FechaFin.value='';
			FechaIni.focus()
		}
		window.parent.document.getElementById('CierreAct').disabled=true;
	}else{
		window.parent.document.getElementById('CierreAct').disabled=false;
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
				<th class="text-center form-inline w-80"><div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div> <button type="button" id="btnBorrarLineas" title="Borrar lineas" class="btn btn-danger btn-xs disabled" onClick="BorrarLinea();"><i class="fa fa-trash"></i></button></th>
				<th>Número de OT</th>
				<th>ID Actividad</th>				
				<th>Nombre empleado</th>
				<th>Fecha y hora inicio Actividad</th>	
				<th>Fecha y hora inicio Ejecución</th>		
				<th>Fecha y hora fin Ejecución</th>
				<th>Estado actividad</th>
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
			<td><input size="15" type="text" id="ID_OrdenServicio<?php echo $i;?>" name="ID_OrdenServicio[]" class="form-control" readonly value="<?php echo $row['ID_OrdenServicio'];?>"></td>
			<td><input size="15" type="text" id="ID_Actividad<?php echo $i;?>" name="ID_Actividad[]" class="form-control" readonly value="<?php echo $row['ID_Actividad'];?>"><input type="hidden" name="ID[]" id="ID<?php echo $i;?>" value="<?php echo $row['ID'];?>"></td>			
			<td><input size="30" type="text" id="NombreEmpleado<?php echo $i;?>" name="NombreEmpleado[]" class="form-control" readonly value="<?php echo $row['NombreEmpleadoActividad'];?>"></td>
			<td><input size="25" type="text" id="FechaHoraInicioActividad<?php echo $i;?>" name="FechaHoraInicioActividad[]" class="form-control" readonly value="<?php echo $row['FechaHoraInicioActividad']->format('Y-m-d H:i');?>"></td>
			
			<td><input size="25" type="text" id="FechaHoraInicioEjecucion<?php echo $i;?>" name="FechaHoraInicioEjecucion[]" class="form-control" onChange="ActualizarDatos('FechaHoraInicioEjecucion',<?php echo $i;?>,<?php echo $row['ID'];?>);" value="<?php if($row['FechaHoraInicioEjecucion']!=""){echo $row['FechaHoraInicioEjecucion']->format('Y-m-d H:i');}?>" data-mask="9999-99-99 99:99"></td>
			<td><input size="25" type="text" id="FechaHoraFinEjecucion<?php echo $i;?>" name="FechaHoraFinEjecucion[]" class="form-control" onChange="ActualizarDatos('FechaHoraFinEjecucion',<?php echo $i;?>,<?php echo $row['ID'];?>);" value="<?php if($row['FechaHoraFinEjecucion']!=""){echo $row['FechaHoraFinEjecucion']->format('Y-m-d H:i');}?>" data-mask="9999-99-99 99:99"></td>
			<td><input size="15" type="text" id="EstadoActividad<?php echo $i;?>" name="EstadoActividad[]" class="form-control <?php if($row['EstadoActividad']=="Abierto"){echo "bg-danger";}else{echo "bg-primary";}?>" readonly value="<?php echo $row['EstadoActividad'];?>"></td>
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