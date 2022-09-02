<?php 
require_once("includes/conexion.php");
PermitirAcceso(318);
$sw=0;
//$Proyecto="";
//$Almacen="";
$CardCode="";
$type=1;
$Estado=1;//Abierto

if(isset($_GET['idsucursal'])&&($_GET['idsucursal']!="")){
	$Sucursal="and IdLineaSucursal='".base64_decode($_GET['idsucursal'])."'";
}else{
	$Sucursal="";
}

$SQL=Seleccionar("uvw_tbl_ProgramacionOrdenesServicio","*","IdCliente='".base64_decode($_GET['cardcode'])."' $Sucursal and Periodo='".base64_decode($_GET['periodo'])."'","IdSucursalCliente");
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
	.table > tbody > tr > td{
		padding: 1px !important;
		vertical-align: middle;
	}
	.select2-container{ width: 100% !important; }
	.bg-success[readonly]{
		background-color: #1c84c6 !important;
		color: #ffffff !important;
	}
</style>
<script>
var json=[];
var cant=0;

function BorrarLinea(){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea eliminar este item? Este proceso no se puede revertir.')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=21&linenum="+json,		
			success: function(response){
				window.location.href="detalle_cronograma_servicios.php?<?php echo $_SERVER['QUERY_STRING'];?>";
			}
		});
	}	
}
	
function DuplicarLinea(LineNum){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea duplicar este item? El nuevo registro se pondr'+String.fromCharCode(225)+' al final de la tabla.')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=27&linenum="+LineNum,		
			success: function(response){
				window.location.href="detalle_cronograma_servicios.php?<?php echo $_SERVER['QUERY_STRING'];?>";
			}
		});
	}	
}
	
function CorregirSuc(LineNum, Val='', Clt=''){
//	$('.ibox-content').toggleClass('sk-loading',true);
	if(Val=='Sucursal no existe'){
		let select=document.createElement("select");
		let td=document.getElementById("SucCliente_"+LineNum);
		
		select.className='form-control';
		select.id="SelSucCliente_"+LineNum;
				
		td.innerHTML='';
		td.appendChild(select);
		
		$.ajax({
			type: "POST",
			url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Clt+"&sucline=1&tdir=S&selec=1",
			success: function(response){
				$('#SelSucCliente_'+LineNum).html(response);
				select.addEventListener("change", function(){
					let value=document.getElementById("SelSucCliente_"+LineNum).value
					CambiarSuc(LineNum, value);
				});
//				$('#SelSucCliente_'+LineNum).trigger("change");
//				$('.ibox-content').toggleClass('sk-loading',false);
			}
		});
	}else{
		if(confirm('Se cambiar'+String.fromCharCode(225)+' el nombre de la sucursal en el cronograma seg'+String.fromCharCode(250)+'n el que est'+String.fromCharCode(225)+' en el dato maestro.')){
			$.ajax({
				type: "GET",
				url: "includes/procedimientos.php?type=48&linenum="+LineNum,		
				success: function(response){
					window.location.href="detalle_cronograma_servicios.php?<?php echo $_SERVER['QUERY_STRING'];?>";
				}
			});
		}	
	}
	
}
	
function CambiarSuc(LineNum, IdSuc){
//	console.log("LineNum", LineNum)
//	console.log("IdSuc", IdSuc)
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "GET",
		url: "includes/procedimientos.php?type=48&linenum="+LineNum+"&idsuc="+IdSuc,		
		success: function(response){
			window.location.href="detalle_cronograma_servicios.php?<?php echo $_SERVER['QUERY_STRING'];?>";
		}
	});
}
	
function ActualizarDatos(name,id,line){//Actualizar datos asincronicamente
	$.ajax({
		type: "GET",
		url: "registro.php?P=36&doctype=11&type=2&name="+name+"&value="+Base64.encode(document.getElementById(name+id).value)+"&line="+line,
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

function ConsultarArticulo(Articulo){
	if(Articulo.value!=""){
		self.name='opener';
		remote=open('articulos.php?id='+Articulo+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}
	
function Resaltar(ID){
	$("input").removeClass('bg-success');
	$("#"+ID).find("input").addClass('bg-success');	
}
</script>
</head>

<body>
<form id="from" name="form">
	<div class="ibox-content">
		<?php include("includes/spinner.php"); ?>
	<table width="100%" class="table table-bordered dataTables-example">
		<thead>
			<tr>
				<th class="text-center form-inline w-80"><div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div> <button type="button" id="btnBorrarLineas" title="Borrar lineas" class="btn btn-danger btn-xs disabled" onClick="BorrarLinea();"><i class="fa fa-trash"></i></button></th>
				<th>&nbsp;</th>
				<th>#</th>
				<th>Validación</th>
				<th>Sucursal cliente</th>
				<th>Código artículo</th>
				<th>Nombre artículo</th>
				<th>Estado</th>
				<th>Frecuencia</th>
				<th>Enero</th>
				<th>Febrero</th>
				<th>Marzo</th>
				<th>Abril</th>	
				<th>Mayo</th>
				<th>Junio</th>		
				<th>Julio</th>
				<th>Agosto</th>
				<th>Septiembre</th>
				<th>Octubre</th>
				<th>Noviembre</th>
				<th>Diciembre</th>
				<th>Fecha Últ. Actualización</th>
				<th>Usuario Últ. Actualización</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		if($sw==1){
			$i=1;
			while($row=sqlsrv_fetch_array($SQL)){
		?>
		<tr id="<?php echo $i;?>" onClick="Resaltar('<?php echo $i;?>');">
			<td class="text-center">
				<div class="checkbox checkbox-success no-margins">
					<input type="checkbox" class="chkSel" id="chkSel<?php echo $row['ID'];?>" value="" onChange="Seleccionar('<?php echo $row['ID'];?>');" aria-label="Single checkbox One"><label></label>
				</div>
			</td>
			<td class="text-center form-inline w-80">
				<button type="button" title="Duplicar linea" class="btn btn-success btn-xs" onClick="DuplicarLinea(<?php echo $row['ID'];?>);"><i class="fa fa-copy"></i></button>
				<?php if(!strstr($row['Validacion'],"OK")){?>
					<button type="button" title="Corregir sucursal" class="btn btn-warning btn-xs" onClick="CorregirSuc(<?php echo $row['ID'];?>,'<?php echo $row['Validacion'];?>','<?php echo $row['IdCliente'];?>');"><i class="fa fa-gavel"></i></button>
				<?php }?>
			</td>
			<td class="text-center"><?php echo $i;?></td>
			<td><span class="<?php if(strstr($row['Validacion'],"OK")){echo "badge badge-primary";}else{echo "badge badge-danger";}?>"><?php echo $row['Validacion'];?></span></td>
			<td id="SucCliente_<?php echo $row['ID'];?>"><input size="50" type="text" id="SucursalCliente<?php echo $i;?>" name="SucursalCliente[]" class="form-control" readonly value="<?php echo $row['IdSucursalCliente'];?>"></td>
			<td><input size="20" type="text" id="CodListaMateriales<?php echo $i;?>" name="CodListaMateriales[]" class="form-control btn-link" readonly value="<?php echo $row['IdArticuloLMT'];?>" onClick="ConsultarArticulo('<?php echo base64_encode($row['IdArticuloLMT']);?>');" title="Consultar artículo" style="cursor: pointer"></td>
			<td><input size="80" type="text" id="ListaMateriales<?php echo $i;?>" name="ListaMateriales[]" class="form-control" readonly value="<?php echo $row['DeArticuloLMT'];?>"></td>
			<td><input size="15" type="text" id="Estado<?php echo $i;?>" name="Estado[]" class="form-control" readonly value="<?php echo $row['NombreEstado'];?>"></td>
			<td><input size="15" type="text" id="Frecuencia<?php echo $i;?>" name="Frecuencia[]" class="form-control" readonly value="<?php echo $row['Frecuencia'];?>"></td>
			<td><input size="15" type="text" id="Enero<?php echo $i;?>" name="Enero[]" class="form-control" value="<?php if($row['Enero']!=""){ echo $row['Enero']->format('Y-m-d');}?>" onChange="ActualizarDatos('Enero',<?php echo $i;?>,<?php echo $row['ID'];?>);" data-mask="9999-99-99" title="Enero"></td>
			<td><input size="15" type="text" id="Febrero<?php echo $i;?>" name="Febrero[]" class="form-control" value="<?php if($row['Febrero']!=""){ echo $row['Febrero']->format('Y-m-d');}?>" onChange="ActualizarDatos('Febrero',<?php echo $i;?>,<?php echo $row['ID'];?>);" data-mask="9999-99-99" title="Febrero"></td>
			<td><input size="15" type="text" id="Marzo<?php echo $i;?>" name="Marzo[]" class="form-control" value="<?php if($row['Marzo']!=""){ echo $row['Marzo']->format('Y-m-d');}?>" onChange="ActualizarDatos('Marzo',<?php echo $i;?>,<?php echo $row['ID'];?>);" data-mask="9999-99-99" title="Marzo"></td>
			<td><input size="15" type="text" id="Abril<?php echo $i;?>" name="Abril[]" class="form-control" value="<?php if($row['Abril']!=""){ echo $row['Abril']->format('Y-m-d');}?>" onChange="ActualizarDatos('Abril',<?php echo $i;?>,<?php echo $row['ID'];?>);" data-mask="9999-99-99" title="Abril"></td>
			<td><input size="15" type="text" id="Mayo<?php echo $i;?>" name="Mayo[]" class="form-control" value="<?php if($row['Mayo']!=""){ echo $row['Mayo']->format('Y-m-d');}?>" onChange="ActualizarDatos('Mayo',<?php echo $i;?>,<?php echo $row['ID'];?>);" data-mask="9999-99-99" title="Mayo"></td>
			<td><input size="15" type="text" id="Junio<?php echo $i;?>" name="Junio[]" class="form-control" value="<?php if($row['Junio']!=""){ echo $row['Junio']->format('Y-m-d');}?>" onChange="ActualizarDatos('Junio',<?php echo $i;?>,<?php echo $row['ID'];?>);" data-mask="9999-99-99" title="Junio"></td>
			<td><input size="15" type="text" id="Julio<?php echo $i;?>" name="Julio[]" class="form-control" value="<?php if($row['Julio']!=""){ echo $row['Julio']->format('Y-m-d');}?>" onChange="ActualizarDatos('Julio',<?php echo $i;?>,<?php echo $row['ID'];?>);" data-mask="9999-99-99" title="Julio"></td>
			<td><input size="15" type="text" id="Agosto<?php echo $i;?>" name="Agosto[]" class="form-control" value="<?php if($row['Agosto']!=""){ echo $row['Agosto']->format('Y-m-d');}?>" onChange="ActualizarDatos('Agosto',<?php echo $i;?>,<?php echo $row['ID'];?>);" data-mask="9999-99-99" title="Agosto"></td>
			<td><input size="15" type="text" id="Septiembre<?php echo $i;?>" name="Septiembre[]" class="form-control" value="<?php if($row['Septiembre']!=""){ echo $row['Septiembre']->format('Y-m-d');}?>" onChange="ActualizarDatos('Septiembre',<?php echo $i;?>,<?php echo $row['ID'];?>);" data-mask="9999-99-99" title="Septiembre"></td>
			<td><input size="15" type="text" id="Octubre<?php echo $i;?>" name="Octubre[]" class="form-control" value="<?php if($row['Octubre']!=""){ echo $row['Octubre']->format('Y-m-d');}?>" onChange="ActualizarDatos('Octubre',<?php echo $i;?>,<?php echo $row['ID'];?>);" data-mask="9999-99-99" title="Octubre"></td>
			<td><input size="15" type="text" id="Noviembre<?php echo $i;?>" name="Noviembre[]" class="form-control" value="<?php if($row['Noviembre']!=""){ echo $row['Noviembre']->format('Y-m-d');}?>" onChange="ActualizarDatos('Noviembre',<?php echo $i;?>,<?php echo $row['ID'];?>);" data-mask="9999-99-99" title="Noviembre"></td>
			<td><input size="15" type="text" id="Diciembre<?php echo $i;?>" name="Diciembre[]" class="form-control" value="<?php if($row['Diciembre']!=""){ echo $row['Diciembre']->format('Y-m-d');}?>" onChange="ActualizarDatos('Diciembre',<?php echo $i;?>,<?php echo $row['ID'];?>);" data-mask="9999-99-99" title="Diciembre"></td>
			<td><input size="15" type="text" id="FechaActualizacion<?php echo $i;?>" name="FechaActualizacion[]" class="form-control" value="<?php if($row['FechaActualizacion']!=""){echo $row['FechaActualizacion']->format('Y-m-d H:i');}?>" readonly></td>
			<td><input size="20" type="text" id="Usuario<?php echo $i;?>" name="Usuario[]" class="form-control" value="<?php echo $row['Usuario'];?>" readonly></td>
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
		 $('.dataTables-example').DataTable({
			searching: false,
			info: false,
			paging: false,
			//fixedHeader: true,
//			scrollX: true,
//			scrollY: true,
//			fixedColumns: {
//				leftColumns: 5
//			}
		});
	});
</script>
</body>
</html>
<?php 
	sqlsrv_close($conexion);
?>