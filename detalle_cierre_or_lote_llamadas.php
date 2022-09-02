<?php 
require_once("includes/conexion.php");
PermitirAcceso(311);
$sw=0;
//$Proyecto="";
//$Almacen="";
$CardCode="";
$type=1;
$Estado=1;//Abierto

$SQL=Seleccionar("uvw_tbl_CierreOTLlamadasCarrito","*","Usuario='".strtolower($_SESSION['User'])."'");
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
</style>
<script>
function BorrarLinea(LineNum, Todos=0){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea eliminar este item? Este proceso no se puede revertir.')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=15&edit=<?php echo $type;?>&linenum="+LineNum+"&cardcode=<?php echo $CardCode;?>&todos="+Todos,		
			success: function(response){
				window.location.href="detalle_facturacion_orden_servicio.php?<?php echo $_SERVER['QUERY_STRING'];?>";
			}
		});
	}	
}
</script>
<script>
function ActualizarDatos(name,id,line){//Actualizar datos asincronicamente
	$.ajax({
		type: "GET",
		url: "registro.php?P=36&doctype=8&type=1&name="+name+"&value="+Base64.encode(document.getElementById(name+id).value)+"&line="+line+"&cardcode=<?php echo $CardCode;?>",
		success: function(response){
			if(response!="Error"){
				window.parent.document.getElementById('TimeAct').innerHTML="<strong>Actualizado:</strong> "+response;
			}
		}
	});
}
</script>
</head>

<body>
<form id="from" name="form">
	<div class="">
	<table width="100%" class="table table-bordered">
		<thead>
			<tr>
				<th><button type="button" title="Borrar todos" class="btn btn-default btn-xs" onClick="BorrarLinea(0,1);"><i class="fa fa-trash"></i></button></th>
				<th>Orden servicio</th>
				<th>Serie</th>
				<th>Codigo cliente</th>
				<th>Nombre cliente</th>
				<th>Sucursal cliente</th>	
				<th>Estado Orden servicio</th>
				<th>Estado servicio</th>		
				<th>Cancelado por</th>
				<th>Anexo</th>
				<th>Fecha validación</th>
				<th>Validación</th>
				<th>Fecha ejecución</th>
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
			<td class="text-center"><button type="button" title="Borrar linea" class="btn btn-default btn-xs" onClick="BorrarLinea(<?php echo $row['ID'];?>);"><i class="fa fa-trash"></i></button></td>
			<td><input size="20" type="text" id="OrdenServicio<?php echo $i;?>" name="OrdenServicio[]" class="form-control" readonly value="<?php echo $row['ID_OrdenServicio'];?>"><input type="hidden" name="ID[]" id="ID<?php echo $i;?>" value="<?php echo $row['ID'];?>"></td>
			<td><input size="15" type="text" id="Serie<?php echo $i;?>" name="Serie[]" class="form-control" readonly value="<?php echo $row['SerieOT'];?>"></td>
			<td><input size="20" type="text" id="CodigoCliente<?php echo $i;?>" name="CodigoCliente[]" class="form-control" readonly value="<?php echo $row['IdCliente'];?>"></td>
			<td><input size="50" type="text" id="NombreCliente<?php echo $i;?>" name="NombreCliente[]" class="form-control" readonly value="<?php echo $row['NombreCliente'];?>"></td>
			<td><input size="50" type="text" id="SucursalCliente<?php echo $i;?>" name="SucursalCliente[]" class="form-control" readonly value="<?php echo $row['IdSucursalCliente'];?>"></td>
			<td><input size="15" type="text" id="EstadoOrdenServicio<?php echo $i;?>" name="EstadoOrdenServicio[]" class="form-control" readonly value="<?php echo $row['EstadoOrdenServicio'];?>"></td>
			<td>
				<select id="EstadoServicio<?php echo $i;?>" name="EstadoServicio[]" class="form-control m-b select2" onChange="ActualizarDatos('EstadoServicio',<?php echo $i;?>,<?php echo $row['ID'];?>);">
				  <?php while($row_EstServLlamada=sqlsrv_fetch_array($SQL_EstServLlamada)){?>
						<option value="<?php echo $row_EstServLlamada['IdEstadoServicio'];?>" <?php if((isset($row['EstadoServicio']))&&(strcmp($row_EstServLlamada['IdEstadoServicio'],$row['EstadoServicio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstServLlamada['DeEstadoServicio'];?></option>
				  <?php }?>
				</select>
			</td>
			<td>
				<select id="CanceladoPor<?php echo $i;?>" name="CanceladoPor[]" class="form-control m-b select2" onChange="ActualizarDatos('CanceladoPor',<?php echo $i;?>,<?php echo $row['ID'];?>);">
				  <?php while($row_CanceladoPorLlamada=sqlsrv_fetch_array($SQL_CanceladoPorLlamada)){?>
						<option value="<?php echo $row_CanceladoPorLlamada['IdCanceladoPor'];?>" <?php if((isset($row['CanceladoPor']))&&(strcmp($row_CanceladoPorLlamada['IdCanceladoPor'],$row['CanceladoPor'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_CanceladoPorLlamada['DeCanceladoPor'];?></option>
				  <?php }?>
				</select>
			</td>
			<td><input size="15" type="text" id="Anexo<?php echo $i;?>" name="Anexo[]" class="form-control" readonly value="<?php echo $row['AnexoOrdenServicio'];?>"></td>
			<td><input size="15" type="text" id="FechaValidacion<?php echo $i;?>" name="FechaValidacion[]" class="form-control" readonly value="<?php echo $row['FechaValidacion']->format('Y-m-d H:i:s');?>"></td>
			<td><input size="15" type="text" id="Validacion<?php echo $i;?>" name="Validacion[]" class="form-control" readonly value="<?php echo $row['Validacion'];?>"></td>
			<td><input size="15" type="text" id="FechaEjecucion<?php echo $i;?>" name="FechaEjecucion[]" class="form-control" readonly value="<?php echo $row['FechaEjecucion']->format('Y-m-d H:i:s');?>"></td>
			<td><input size="30" type="text" id="Ejecucion<?php echo $i;?>" name="Ejecucion[]" class="form-control" readonly value="<?php echo $row['Ejecucion'];?>"></td>
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