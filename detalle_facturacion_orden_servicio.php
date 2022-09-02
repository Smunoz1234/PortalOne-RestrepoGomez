<?php 
require_once("includes/conexion.php");
PermitirAcceso(408);
$sw=0;
//$Proyecto="";
//$Almacen="";
$CardCode="";
$type=1;
$Estado=1;//Abierto

//Dimensiones de reparto
$SQL_DimReparto=Seleccionar('uvw_Sap_tbl_NombresDimensionesReparto','*','',"CodDim");

if(isset($_GET['id'])&&($_GET['id']!="")){
	if($_GET['type']==1){
		$type=1;
	}else{
		$type=$_GET['type'];
	}
	if($type==1){//Creando Orden de Venta
		$SQL=Seleccionar("uvw_tbl_FacturaOTDetalleCarrito","*","Usuario='".$_GET['usr']."' and CardCode='".$_GET['cardcode']."'");
		if($SQL){
			$sw=1;
			$CardCode=$_GET['cardcode'];
			//$Proyecto=$_GET['prjcode'];
			//$Almacen=$_GET['whscode'];			
		}else{
			$CardCode="";
			//$Proyecto="";
			//$Almacen="";
		}
		
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

function Totalizar(num){
	//alert(num);
	var SubTotal=0;
	var Descuentos=0;
	var Iva=0;
	var Total=0;
	var i=1;
	for(i=1;i<=num;i++){
		var TotalLinea=document.getElementById('LineTotal'+i);
		var PrecioLinea=document.getElementById('Price'+i);
		var PrecioIVALinea=document.getElementById('PriceTax'+i);
		var TarifaIVALinea=document.getElementById('TarifaIVA'+i);
		var ValorIVALinea=document.getElementById('VatSum'+i);
		var PrcDescuentoLinea=document.getElementById('DiscPrcnt'+i);
		var CantLinea=document.getElementById('Quantity'+i);
		
		var Precio=parseFloat(PrecioLinea.value.replace(/,/g, ''));
		var PrecioIVA=parseFloat(PrecioIVALinea.value.replace(/,/g, ''));
		var TarifaIVA=TarifaIVALinea.value.replace(/,/g, '');
		var ValorIVA=ValorIVALinea.value.replace(/,/g, '');
		var Cant=parseFloat(CantLinea.value.replace(/,/g, ''));
		//var TotIVA=((parseFloat(Precio)*parseFloat(TarifaIVA)/100)+parseFloat(Precio));
		//ValorIVALinea.value=number_format((parseFloat(Precio)*parseFloat(TarifaIVA)/100),2);
		//PrecioIVALinea.value=number_format(parseFloat(TotIVA),2);
		var SubTotalLinea=Precio*Cant;
		var PrcDesc=parseFloat(PrcDescuentoLinea.value.replace(/,/g, ''));
		var TotalDesc=(PrcDesc*SubTotalLinea)/100;
		//TotalLinea.value=number_format(SubTotalLinea-TotalDesc,2);

		SubTotal=parseFloat(SubTotal)+parseFloat(SubTotalLinea);
		Descuentos=parseFloat(Descuentos)+parseFloat(TotalDesc);
		Iva=parseFloat(Iva)+parseFloat(ValorIVA);
		//var Linea=document.getElementById('LineTotal'+i).value.replace(/,/g, '');
	}
	Total=parseFloat(Total)+parseFloat((SubTotal-Descuentos)+Iva);
	//return Total;
	//alert(Total);
	//window.parent.document.getElementById('SubTotal').value=number_format(parseFloat(SubTotal),2);
	//window.parent.document.getElementById('Descuentos').value=number_format(parseFloat(Descuentos),2);
	//window.parent.document.getElementById('Impuestos').value=number_format(parseFloat(Iva),2);
	//window.parent.document.getElementById('TotalOrden').value=number_format(parseFloat(Total),2);
	window.parent.document.getElementById('TotalItems').value=num;
}

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
				<th><button type="button" title="Borrar todos" class="btn btn-danger btn-xs" onClick="BorrarLinea(0,1);"><i class="fa fa-trash"></i></button></th>
				<th>Código artículo</th>
				<th>Nombre artículo</th>
				<th>Sucursal cliente</th>
				<th>OT relacionadas</th>
				<th>Áreas controladas</th>	
				<th>Unidad</th>
				<th>Cantidad</th>		
				<th>Precio</th>
				<th>Precio con IVA</th>
				<th>% Desc.</th>
				<th>Total</th>
				<?php $row_DimReparto=sqlsrv_fetch_array($SQL_DimReparto);?>
				<th><?php echo $row_DimReparto['NombreDim']; //Dimension 1 ?></th>
				<?php $row_DimReparto=sqlsrv_fetch_array($SQL_DimReparto);?>
				<th><?php echo $row_DimReparto['NombreDim']; //Dimension 2 ?></th>
				<?php $row_DimReparto=sqlsrv_fetch_array($SQL_DimReparto);?>
				<th><?php echo $row_DimReparto['NombreDim']; //Dimension 3 ?></th>
				<th>Proyecto</th>
				<th>Texto libre</th>
				<th>Almacén</th>				
				<th><i class="fa fa-refresh"></i></th>
			</tr>
		</thead>
		<tbody>
		<?php 
		if($sw==1){
			$i=1;
			while($row=sqlsrv_fetch_array($SQL)){
				
				//$Almacen=$row['WhsCode'];
		?>
		<tr>
			<td><?php if(($row['TreeType']!="T")&&($row['LineStatus']=="O")){?><button type="button" title="Borrar linea" class="btn btn-danger btn-xs" onClick="BorrarLinea(<?php echo $row['LineNum'];?>);"><i class="fa fa-trash"></i></button><?php }?></td>
			<td><input size="20" type="text" id="ItemCode<?php echo $i;?>" name="ItemCode[]" class="form-control" readonly value="<?php echo $row['ItemCode'];?>"><input type="hidden" name="LineNum[]" id="LineNum<?php echo $i;?>" value="<?php echo $row['LineNum'];?>"></td>
			<td><input size="50" type="text" id="ItemName<?php echo $i;?>" name="ItemName[]" class="form-control" value="<?php echo $row['ItemName'];?>" maxlength="100" onChange="ActualizarDatos('ItemName',<?php echo $i;?>,<?php echo $row['LineNum'];?>);" <?php if($row['LineStatus']=='C'||(!PermitirFuncion(402))){echo "readonly";}?>></td>
			<td><input size="50" type="text" id="SucursalCliente<?php echo $i;?>" name="SucursalCliente[]" class="form-control" value="<?php echo $row['SucursalCliente'];?>" maxlength="100" readonly></td>
			<td><input size="30" type="text" id="NumOT<?php echo $i;?>" name="NumOT[]" class="form-control" readonly value="<?php echo $row['CDU_OrdenServicio'];?>"></td>
			<td><?php if(($row['TreeType']!="T")||(($row['TreeType']=="T")&&($row['LineNum']!=0))){?>
				<input size="50" type="text" id="CDU_AreasControladas<?php echo $i;?>" name="CDU_AreasControladas[]" class="form-control" value="<?php echo $row['CDU_AreasControladas'];?>" onChange="ActualizarDatos('CDU_AreasControladas',<?php echo $i;?>,<?php echo $row['LineNum'];?>);" <?php if($row['LineStatus']=='C'||(!PermitirFuncion(402))){echo "readonly";}?>>
				<?php }?>
			</td>
			<td><input size="15" type="text" id="UnitMsr<?php echo $i;?>" name="UnitMsr[]" class="form-control" readonly value="<?php echo $row['UnitMsr'];?>"></td>
			<td><input size="15" type="text" id="Quantity<?php echo $i;?>" name="Quantity[]" class="form-control" value="<?php echo number_format($row['Quantity'],2);?>" onChange="ActualizarDatos('Quantity',<?php echo $i;?>,<?php echo $row['LineNum'];?>);" onBlur="CalcularTotal(<?php echo $i;?>);" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" <?php if($row['LineStatus']=='C'||(!PermitirFuncion(402))){echo "readonly";}?>></td>
			<td><input size="15" type="text" id="Price<?php echo $i;?>" name="Price[]" class="form-control" value="<?php echo number_format($row['Price'],2);?>" onChange="ActualizarDatos('Price',<?php echo $i;?>,<?php echo $row['LineNum'];?>);" onBlur="CalcularTotal(<?php echo $i;?>);" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" <?php if($row['LineStatus']=='C'||(!PermitirFuncion(402))){echo "readonly";}?>></td>
			<td><input size="15" type="text" id="PriceTax<?php echo $i;?>" name="PriceTax[]" class="form-control" value="<?php echo number_format($row['PriceTax'],2);?>" onBlur="CalcularTotal(<?php echo $i;?>);" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" readonly><input type="hidden" id="TarifaIVA<?php echo $i;?>" name="TarifaIVA[]" value="<?php echo number_format($row['TarifaIVA'],0);?>"><input type="hidden" id="VatSum<?php echo $i;?>" name="VatSum[]" value="<?php echo number_format($row['VatSum'],2);?>"></td>
			<td><input size="15" type="text" id="DiscPrcnt<?php echo $i;?>" name="DiscPrcnt[]" class="form-control" value="<?php echo number_format($row['DiscPrcnt'],2);?>" onChange="ActualizarDatos('DiscPrcnt',<?php echo $i;?>,<?php echo $row['LineNum'];?>);" onBlur="CalcularTotal(<?php echo $i;?>);" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" <?php if($row['LineStatus']=='C'||(!PermitirFuncion(402))){echo "readonly";}?>></td>
			<td><input size="15" type="text" id="LineTotal<?php echo $i;?>" name="LineTotal[]" class="form-control" readonly value="<?php echo number_format($row['LineTotal'],2);?>"></td>
			<td><input size="15" type="text" id="OcrCode<?php echo $i;?>" name="OcrCode[]" class="form-control" readonly value="<?php echo $row['OcrName'];?>"></td>
			<td><input size="15" type="text" id="OcrCode2<?php echo $i;?>" name="OcrCode2[]" class="form-control" readonly value="<?php echo $row['OcrName2'];?>"></td>
			<td><input size="15" type="text" id="OcrCode3<?php echo $i;?>" name="OcrCode3[]" class="form-control" readonly value="<?php echo $row['OcrName3'];?>"></td>	
			<td><input size="25" type="text" id="Proyecto<?php echo $i;?>" name="Proyecto[]" class="form-control" readonly value="<?php echo $row['DeProyecto'];?>"></td>	
			<td><input size="50" type="text" id="FreeTxt<?php echo $i;?>" name="FreeTxt[]" class="form-control" value="<?php echo $row['FreeTxt'];?>" onChange="ActualizarDatos('FreeTxt',<?php echo $i;?>,<?php echo $row['LineNum'];?>);" maxlength="100" <?php if($row['LineStatus']=='C'||(!PermitirFuncion(402))){echo "readonly";}?>></td>
			<td><input size="15" type="text" id="WhsCode<?php echo $i;?>" name="WhsCode[]" class="form-control" readonly value="<?php echo $row['WhsName'];?>"></td>
			<td><?php if($row['Metodo']==0){?><i class="fa fa-check-circle text-info" title="Sincronizado con SAP"></i><?php }else{?><i class="fa fa-times-circle text-danger" title="Aún no enviado a SAP"></i><?php }?></td>
		</tr>	
		<?php 
			$i++;}
			echo "<script>
			Totalizar(".($i-1).");
			</script>";
		}
		?>
		<?php if($Estado==1){?>
		<tr>
			<td>&nbsp;</td>
			<td><input size="20" type="text" id="ItemCodeNew" name="ItemCodeNew" class="form-control"></td>
			<td><input size="50" type="text" id="ItemNameNew" name="ItemNameNew" class="form-control"></td>
			<td><input size="50" type="text" id="SucursalClienteNew" name="SucursalClienteNew" class="form-control"></td>
			<td><input size="30" type="text" id="NumOTNew" name="NumOTNew" class="form-control"></td>
			<td><input size="50" type="text" id="CDU_AreasControladasNew" name="CDU_AreasControladasNew" class="form-control"></td>	
			<td><input size="15" type="text" id="UnitMsrNew" name="UnitMsrNew" class="form-control"></td>
			<td><input size="15" type="text" id="QuantityNew" name="QuantityNew" class="form-control"></td>
			<td><input size="15" type="text" id="PriceNew" name="PriceNew" class="form-control"></td>
			<td><input size="15" type="text" id="PriceTaxNew" name="PriceTaxNew" class="form-control"></td>
			<td><input size="15" type="text" id="DiscPrcntNew" name="DiscPrcntNew" class="form-control"></td>
			<td><input size="15" type="text" id="LineTotalNew" name="LineTotalNew" class="form-control"></td>			
			<td><input size="15" type="text" id="OcrCodeNew" name="OcrCodeNew" class="form-control"></td>
			<td><input size="15" type="text" id="OcrCode2New" name="OcrCode2New" class="form-control"></td>
			<td><input size="15" type="text" id="OcrCode3New" name="OcrCode3New" class="form-control"></td>
			<td><input size="25" type="text" id="ProyectoNew" name="ProyectoNew" class="form-control"></td>
			<td><input size="50" type="text" id="FreeTxtNew" name="FreeTxtNew" class="form-control"></td>
			<td><input size="15" type="text" id="WhsCodeNew" name="WhsCodeNew" class="form-control"></td>
			<td>&nbsp;</td>
		</tr>
		<?php }?>
		</tbody>
	</table>
	</div>
</form>
<script>
function CalcularTotal(line){
	var TotalLinea=document.getElementById('LineTotal'+line);
	var PrecioLinea=document.getElementById('Price'+line);
	var PrecioIVALinea=document.getElementById('PriceTax'+line);
	var TarifaIVALinea=document.getElementById('TarifaIVA'+line);
	var ValorIVALinea=document.getElementById('VatSum'+line);
	var PrcDescuentoLinea=document.getElementById('DiscPrcnt'+line);
	var CantLinea=document.getElementById('Quantity'+line);
	var Linea=document.getElementById('LineNum'+line);
	
	if(CantLinea.value>0){
		//if(parseFloat(PrecioLinea.value)>0){
			//alert('Info');
			var Precio=PrecioLinea.value.replace(/,/g, '');
			var TarifaIVA=TarifaIVALinea.value.replace(/,/g, '');
			var ValorIVA=ValorIVALinea.value.replace(/,/g, '');
			var Cant=CantLinea.value.replace(/,/g, '');
			var TotIVA=((parseFloat(Precio)*parseFloat(TarifaIVA)/100)+parseFloat(Precio));
			ValorIVALinea.value=number_format((parseFloat(Precio)*parseFloat(TarifaIVA)/100),2);
			PrecioIVALinea.value=number_format(parseFloat(TotIVA),2);
			var PrecioIVA=PrecioIVALinea.value.replace(/,/g, '');
			var SubTotalLinea=PrecioIVA*Cant;
			var PrcDesc=parseFloat(PrcDescuentoLinea.value.replace(/,/g, ''));
			var TotalDesc=(PrcDesc*SubTotalLinea)/100;
			
			TotalLinea.value=number_format(SubTotalLinea-TotalDesc,2);
		//}else{
			//alert('Ult');
			//var Ult=UltPrecioLinea.value.replace(/,/g, '');
			//var Cant=CantLinea.value.replace(/,/g, '');
			//TotalLinea.value=parseFloat(number_format(Ult*Cant,2));
		//}
		//ActualizarDatos(name,id,line);
		Totalizar(<?php if(isset($i)){echo $i-1;}else{echo 0;}?>);
		//window.parent.document.getElementById('TotalSolicitud').value='500';	
	}else{
		alert("No puede solicitar cantidad en 0. Si ya no va a solicitar este articulo, borre la linea.");
		CantLinea.value="1.00";
		//ActualizarDatos(1,line,Linea.value);
	}
	
}
</script>
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