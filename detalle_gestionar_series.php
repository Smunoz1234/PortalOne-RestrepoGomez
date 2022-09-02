<?php 
require_once("includes/conexion.php");
PermitirAcceso(214);
$sw=0;
//$Proyecto="";
//$Almacen="";
$CardCode="";
$type=1;
$Estado=1;//Abierto

$SQL=Seleccionar("uvw_tbl_SeriesSucursalesAlmacenes","*");
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
function BorrarLinea(LineNum){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea eliminar este item? Este proceso no se puede revertir.')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=29&linenum="+LineNum,		
			success: function(response){
				window.location.href="detalle_gestionar_series.php?<?php echo $_SERVER['QUERY_STRING'];?>";
			}
		});
	}	
}

function ActualizarDatos(name,id,line){//Actualizar datos asincronicamente
	$.ajax({
		type: "GET",
		url: "registro.php?P=36&doctype=13&type=1&name="+name+"&value="+Base64.encode(document.getElementById(name+id).value)+"&line="+line,
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
	<div class="table-responsive">
	<table width="100%" class="table table-hover table-striped dataTables-example">
		<thead>
			<tr>
				<th>&nbsp;</th>
				<th>#</th>
				<th>Nombre documento</th>
				<th>Nombre Serie</th>
				<th>Sucursal</th>		
				<th>Almacén origen</th>
				<th>Almacén destino</th>	
				<th>Almacén defecto</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		if($sw==1){
			$i=1;
			while($row=sqlsrv_fetch_array($SQL)){
				
				//Series
				$SQL_Series=Seleccionar("uvw_Sap_tbl_SeriesDocumentos","IdSeries, DeSeries","IdTipoDocumento='".$row['IdTipoDocumento']."'");
				
				//Sucursal
				$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_DimensionesReparto","OcrCode, OcrName","DimCode='3'");
				
				//Almacen origen
				$SQL_AlmOrigen=Seleccionar("uvw_Sap_tbl_Almacenes","WhsCode, WhsName");
				
				//Almacen Destino
				$SQL_AlmDestino=Seleccionar("uvw_Sap_tbl_Almacenes","WhsCode, WhsName");
				
				//Almacen Defecto
				$SQL_AlmDefecto=Seleccionar("uvw_Sap_tbl_Almacenes","WhsCode, WhsName");
		?>
		<tr>
			<td class="text-center">
				<button type="button" title="Borrar linea" class="btn btn-danger btn-xs" onClick="BorrarLinea(<?php echo $row['ID'];?>);"><i class="fa fa-trash"></i></button>
			</td>
			<td class="text-center"><?php echo $i;?></td>		
			<td><?php echo $row['DeTipoDocumento'];?></td>
			<td>
				<select id="IdSeries<?php echo $i;?>" name="IdSeries[]" class="form-control" onChange="ActualizarDatos('IdSeries',<?php echo $i;?>,<?php echo $row['ID'];?>);">
					<option value="">Seleccione...</option>
				  <?php while($row_Series=sqlsrv_fetch_array($SQL_Series)){?>
						<option value="<?php echo $row_Series['IdSeries'];?>" <?php if((isset($row['IdSeries']))&&(strcmp($row_Series['IdSeries'],$row['IdSeries'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries'];?></option>
				  <?php }?>
				</select>
			</td>
			<td>
				<select id="IdSucursal<?php echo $i;?>" name="IdSucursal[]" class="form-control" onChange="ActualizarDatos('IdSucursal',<?php echo $i;?>,<?php echo $row['ID'];?>);">
					<option value="">Seleccione...</option>
				  <?php while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
						<option value="<?php echo $row_Sucursal['OcrCode'];?>" <?php if((isset($row['IdSucursal']))&&(strcmp($row_Sucursal['OcrCode'],$row['IdSucursal'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['OcrName'];?></option>
				  <?php }?>
				</select>
			</td>
			<td>
				<select id="WhsCode<?php echo $i;?>" name="WhsCode[]" class="form-control" onChange="ActualizarDatos('WhsCode',<?php echo $i;?>,<?php echo $row['ID'];?>);">
					<option value="">Seleccione...</option>
				  <?php while($row_AlmOrigen=sqlsrv_fetch_array($SQL_AlmOrigen)){?>
						<option value="<?php echo $row_AlmOrigen['WhsCode'];?>" <?php if((isset($row['WhsCode']))&&(strcmp($row_AlmOrigen['WhsCode'],$row['WhsCode'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_AlmOrigen['WhsName'];?></option>
				  <?php }?>
				</select>
			</td>
			<td>
				<select id="ToWhsCode<?php echo $i;?>" name="ToWhsCode[]" class="form-control" onChange="ActualizarDatos('ToWhsCode',<?php echo $i;?>,<?php echo $row['ID'];?>);">
				 		<option value="">(Ninguno)</option>
					<?php while($row_AlmDestino=sqlsrv_fetch_array($SQL_AlmDestino)){?>
						<option value="<?php echo $row_AlmDestino['WhsCode'];?>" <?php if((isset($row['ToWhsCode']))&&(strcmp($row_AlmDestino['WhsCode'],$row['ToWhsCode'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_AlmDestino['WhsName'];?></option>
				  <?php }?>
				</select>
			</td>
			<td>
				<select id="IdBodegaDefecto<?php echo $i;?>" name="IdBodegaDefecto[]" class="form-control" onChange="ActualizarDatos('IdBodegaDefecto',<?php echo $i;?>,<?php echo $row['ID'];?>);">
					<option value="">(Ninguno)</option>
				  <?php while($row_AlmDefecto=sqlsrv_fetch_array($SQL_AlmDefecto)){?>						
						<option value="<?php echo $row_AlmDefecto['WhsCode'];?>" <?php if((isset($row['IdBodegaDefecto']))&&(strcmp($row_AlmDefecto['WhsCode'],$row['IdBodegaDefecto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_AlmDefecto['WhsName'];?></option>
				  <?php }?>
				</select>
			</td>	
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
			info: false,
			paging: false,
			fixedHeader: true,
			rowGroup: {
				dataSrc: 2
			}

		});
	});
</script>
</body>
</html>
<?php 
	sqlsrv_close($conexion);
?>