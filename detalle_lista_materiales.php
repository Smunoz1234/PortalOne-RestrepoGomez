<?php 
require_once("includes/conexion.php");
PermitirAcceso(1208);
$sw=0;
//$Proyecto="";
$Almacen="";
$CardCode="";
$Id="";
$Evento="";
$type=1;
$Estado=1;//Abierto
if(isset($_GET['id'])&&($_GET['id']!="")){
//	if($_GET['type']==1){
//		$type=1;
//	}else{
//		$type=$_GET['type'];
//	}
	$SQL=Seleccionar("tbl_ListaMaterialesDetalle","*","Usuario='".$_SESSION['CodUser']."' and Father='".base64_decode($_GET['id'])."' and IdEvento='".base64_decode($_GET['evento'])."' and Metodo <> 3",'VisOrder');
	if($SQL){
		$sw=1;
		$Id=base64_decode($_GET['id']);
		$Evento=base64_decode($_GET['evento']);
	}
}

			
//Servicios
$SQL_Servicios=Seleccionar("uvw_Sap_tbl_OrdenesVentasDetalleServicios","*","","DeServicio");

//Metodo de apliacion
$SQL_MetodoAplicacion=Seleccionar("uvw_Sap_tbl_OrdenesVentasDetalleMetodoAplicacion","*","","DeMetodoAplicacion");

//Tipo de plagas
$SQL_TipoPlaga=Seleccionar("uvw_Sap_tbl_OrdenesVentasDetalleTipoPlagas","*","","DeTipoPlagas");

//Almacenes
$SQL_Almacen=Seleccionar("uvw_Sap_tbl_Almacenes","*");

//Lista de precios
$SQL_ListaPrecios=Seleccionar('uvw_Sap_tbl_ListaPrecios','*');

//Normas de reparto (Centros de costos)
$SQL_Dim1=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=1');

//Normas de reparto (Unidad negocio)
$SQL_Dim2=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=2');

//Normas de reparto (Sede cliente)
$SQL_Dim3=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=3');

//Proyectos
$SQL_Proyecto=Seleccionar('uvw_Sap_tbl_Proyectos','*','','DeProyecto');
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
var json=[];
var cant=0;
	
function BorrarLinea(){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea eliminar este item? Este proceso no se puede revertir.')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=44&linenum="+json+"&id=<?php echo base64_decode($_GET['id']);?>&evento=<?php echo base64_decode($_GET['evento']);?>",
			success: function(response){
				window.location.href="detalle_lista_materiales.php?<?php echo $_SERVER['QUERY_STRING'];?>";
			}
		});
	}	
}
	
function DuplicarLinea(){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea duplicar estos registros?')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=45&linenum="+json+"&id=<?php echo base64_decode($_GET['id']);?>&evento=<?php echo base64_decode($_GET['evento']);?>",
			success: function(response){
				window.location.href="detalle_lista_materiales.php?<?php echo $_SERVER['QUERY_STRING'];?>";
			}
		});
	}	
}

function ActualizarDatos(name,id,line){//Actualizar datos asincronicamente
	$.ajax({
		type: "GET",
		url: "registro.php?P=36&doctype=15&name="+name+"&value="+btoa(document.getElementById(name+id).value)+"&line="+line+"&id=<?php echo base64_decode($_GET['id']);?>&evento=<?php echo base64_decode($_GET['evento']);?>",
		success: function(response){
			if(response!="Error"){
				window.parent.document.getElementById('TimeAct').innerHTML="<strong>Actualizado:</strong> "+response;
			}
		}
	});
}
	
function Seleccionar(ID){
	var btnBorrarLineas=document.getElementById('btnBorrarLineas');
	var btnDuplicarLineas=document.getElementById('btnDuplicarLineas');
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
		$("#btnBorrarLineas").prop('disabled', false);
		$("#btnDuplicarLineas").prop('disabled', false);
	}else{
		$("#btnBorrarLineas").prop('disabled', true);
		$("#btnDuplicarLineas").prop('disabled', true);
	}
	
	//console.log(json);
}

function SeleccionarTodos(){
	var Check = document.getElementById('chkAll').checked;
	if(Check==false){
		json=[];
		cant=0;
		$("#btnBorrarLineas").prop('disabled', true);
		$("#btnDuplicarLineas").prop('disabled', true);
	}
	$(".chkSel").prop("checked", Check);
	if(Check){
		$(".chkSel").trigger('change');
	}		
}

function Totalizar(num){
	//alert(num);
	var SubTotal=0;
	var Total=0;
	var i=1;
	for(i=1;i<=num;i++){
		var TotalLinea=document.getElementById('LineTotal'+i);
		var PrecioLinea=document.getElementById('Precio'+i);
		var CantLinea=document.getElementById('Cantidad'+i);
		
		var Precio=parseFloat(PrecioLinea.value.replace(/,/g, ''));
		var Cant=parseFloat(CantLinea.value.replace(/,/g, ''));
		
		var SubTotalLinea=Precio*Cant;

		SubTotal=parseFloat(SubTotal)+parseFloat(SubTotalLinea);
	}
	Total=parseFloat(Total)+parseFloat(SubTotal);
	//return Total;
	//alert(Total);
	window.parent.document.getElementById('Total').value=number_format(parseFloat(Total),2);
	window.parent.document.getElementById('TotalItems').value=num;
}
</script>
</head>

<body>
<form id="from" name="form">
	<div class="">
	<table width="100%" class="table table-bordered">
		<thead>
			<tr>
				<th class="text-center form-inline w-150">
					<div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div> 
					<button type="button" id="btnBorrarLineas" title="Borrar lineas" class="btn btn-danger btn-xs" disabled onClick="BorrarLinea();"><i class="fa fa-trash"></i></button>
					<button type="button" id="btnDuplicarLineas" title="Duplicar lineas" class="btn btn-success btn-xs" disabled onClick="DuplicarLinea();"><i class="fa fa-copy"></i></button>
				</th>
				<th>Código artículo</th>
				<th>Nombre artículo</th>
				<th>Unidad</th>
				<th>Cantidad</th>
				<th>Almacén</th>
				<th>Proyecto</th>
				<th>Dimensión 1</th>
				<th>Dimensión 2</th>
				<th>Dimensión 3</th>
				<th>Método emisión</th>
				<th>Servicio</th>
				<th>Método aplicación</th>
				<th>Tipo plaga</th>
				<th>Áreas controladas</th>				
				<th>Precio</th>
				<th>Total</th>
				<th>Lista de precios</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		if($sw==1){
			$i=1;
			
			while($row=sqlsrv_fetch_array($SQL)){
				sqlsrv_fetch($SQL_Almacen, SQLSRV_SCROLL_ABSOLUTE, -1);
				sqlsrv_fetch($SQL_Proyecto, SQLSRV_SCROLL_ABSOLUTE, -1);
				sqlsrv_fetch($SQL_Dim1, SQLSRV_SCROLL_ABSOLUTE, -1);
				sqlsrv_fetch($SQL_Dim2, SQLSRV_SCROLL_ABSOLUTE, -1);
				sqlsrv_fetch($SQL_Dim3, SQLSRV_SCROLL_ABSOLUTE, -1);
				sqlsrv_fetch($SQL_Servicios, SQLSRV_SCROLL_ABSOLUTE, -1);
				sqlsrv_fetch($SQL_MetodoAplicacion, SQLSRV_SCROLL_ABSOLUTE, -1);
				sqlsrv_fetch($SQL_TipoPlaga, SQLSRV_SCROLL_ABSOLUTE, -1);
				sqlsrv_fetch($SQL_ListaPrecios, SQLSRV_SCROLL_ABSOLUTE, -1);
				
		?>
		<tr>
			<td class="text-center">
				<div class="checkbox checkbox-success no-margins">
					<input type="checkbox" class="chkSel" id="chkSel<?php echo $row['ChildNum'];?>" value="" onChange="Seleccionar('<?php echo $row['ChildNum'];?>');" aria-label="Single checkbox One"><label></label>
				</div>
			</td>
			
			<td><input size="20" type="text" id="ItemCode<?php echo $i;?>" name="ItemCode[]" class="form-control" readonly value="<?php echo $row['ItemCode'];?>"><input type="hidden" name="LineNum[]" id="LineNum<?php echo $i;?>" value="<?php echo $row['ChildNum'];?>"></td>
			
			<td><input size="50" type="text" autocomplete="off" id="ItemName<?php echo $i;?>" name="ItemName[]" class="form-control" value="<?php echo $row['ItemName'];?>" maxlength="100" readonly></td>
			
			<td><input size="15" type="text" id="UndMedida<?php echo $i;?>" name="UndMedida[]" class="form-control" readonly value="<?php echo $row['UndMedida'];?>"></td>
			
			<td><input size="15" type="text" autocomplete="off" id="Cantidad<?php echo $i;?>" name="Cantidad[]" class="form-control" value="<?php echo number_format($row['Cantidad'],2);?>" onChange="ActualizarDatos('Cantidad',<?php echo $i;?>,<?php echo $row['ChildNum'];?>);" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" onBlur="CalcularTotal(<?php echo $i;?>);"></td>
			
			<td>
				<select id="WhsCode<?php echo $i;?>" name="WhsCode[]" class="form-control select2" onChange="ActualizarDatos('WhsCode',<?php echo $i;?>,<?php echo $row['ChildNum'];?>);">
				  <?php while($row_Almacen=sqlsrv_fetch_array($SQL_Almacen)){?>
						<option value="<?php echo $row_Almacen['WhsCode'];?>" <?php if((isset($row['WhsCode']))&&(strcmp($row_Almacen['WhsCode'],$row['WhsCode'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Almacen['WhsName'];?></option>
				  <?php }?>
				</select>
			</td>
			
			<td>
				<select id="IdProyecto<?php echo $i;?>" name="IdProyecto[]" class="form-control select2" onChange="ActualizarDatos('IdProyecto',<?php echo $i;?>,<?php echo $row['ChildNum'];?>);">
					<option value="">(NINGUNO)</option>
				  <?php while($row_Proyecto=sqlsrv_fetch_array($SQL_Proyecto)){?>
						<option value="<?php echo $row_Proyecto['IdProyecto'];?>" <?php if((isset($row['IdProyecto']))&&(strcmp($row_Proyecto['IdProyecto'],$row['IdProyecto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Proyecto['DeProyecto'];?></option>
				  <?php }?>
				</select>
			</td>
			
			<td>
				<select id="OcrCode<?php echo $i;?>" name="OcrCode[]" class="form-control select2" onChange="ActualizarDatos('OcrCode',<?php echo $i;?>,<?php echo $row['ChildNum'];?>);">
				  <?php while($row_Dim1=sqlsrv_fetch_array($SQL_Dim1)){?>
						<option value="<?php echo $row_Dim1['OcrCode'];?>" <?php if((isset($row['OcrCode']))&&(strcmp($row_Dim1['OcrCode'],$row['OcrCode'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Dim1['OcrName'];?></option>
				  <?php }?>
				</select>
			</td>
			
			<td>
				<select id="OcrCode2<?php echo $i;?>" name="OcrCode2[]" class="form-control select2" onChange="ActualizarDatos('OcrCode2',<?php echo $i;?>,<?php echo $row['ChildNum'];?>);">
				  <?php while($row_Dim2=sqlsrv_fetch_array($SQL_Dim2)){?>
						<option value="<?php echo $row_Dim2['OcrCode'];?>" <?php if((isset($row['OcrCode2']))&&(strcmp($row_Dim2['OcrCode'],$row['OcrCode2'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Dim2['OcrName'];?></option>
				  <?php }?>
				</select>
			</td>
			
			<td>
				<select id="OcrCode3<?php echo $i;?>" name="OcrCode3[]" class="form-control select2" onChange="ActualizarDatos('OcrCode3',<?php echo $i;?>,<?php echo $row['ChildNum'];?>);">
				  <?php while($row_Dim3=sqlsrv_fetch_array($SQL_Dim3)){?>
						<option value="<?php echo $row_Dim3['OcrCode'];?>" <?php if((isset($row['OcrCode3']))&&(strcmp($row_Dim3['OcrCode'],$row['OcrCode3'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Dim3['OcrName'];?></option>
				  <?php }?>
				</select>
			</td>
			
			<td>
				<select id="MetodoEmision<?php echo $i;?>" name="MetodoEmision[]" class="form-control select2" onChange="ActualizarDatos('MetodoEmision',<?php echo $i;?>,<?php echo $row['ChildNum'];?>);">
				  	<option value="M" <?php if((isset($row['MetodoEmision']))&&($row['MetodoEmision']=="M")){ echo "selected=\"selected\"";}?>>Manual</option>
					<option value="B" <?php if((isset($row['MetodoEmision']))&&($row['MetodoEmision']=="B")){ echo "selected=\"selected\"";}?>>Notificación</option>
				</select>
			</td>

			<td>
				<select id="CDU_IdServicio<?php echo $i;?>" name="CDU_IdServicio[]" class="form-control select2" onChange="ActualizarDatos('CDU_IdServicio',<?php echo $i;?>,<?php echo $row['ChildNum'];?>);">
				  <option value="">(NINGUNO)</option>
				  <?php while($row_Servicios=sqlsrv_fetch_array($SQL_Servicios)){?>
						<option value="<?php echo $row_Servicios['IdServicio'];?>" <?php if((isset($row['CDU_IdServicio']))&&(strcmp($row_Servicios['IdServicio'],$row['CDU_IdServicio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Servicios['DeServicio'];?></option>
				  <?php }?>
				</select>
			</td>
			<td>
				<select id="CDU_IdMetodoAplicacion<?php echo $i;?>" name="CDU_IdMetodoAplicacion[]" class="form-control select2" onChange="ActualizarDatos('CDU_IdMetodoAplicacion',<?php echo $i;?>,<?php echo $row['ChildNum'];?>);">
				  <option value="">(NINGUNO)</option>
				  <?php while($row_MetodoAplicacion=sqlsrv_fetch_array($SQL_MetodoAplicacion)){?>
						<option value="<?php echo $row_MetodoAplicacion['IdMetodoAplicacion'];?>" <?php if((isset($row['CDU_IdMetodoAplicacion']))&&(strcmp($row_MetodoAplicacion['IdMetodoAplicacion'],$row['CDU_IdMetodoAplicacion'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_MetodoAplicacion['DeMetodoAplicacion'];?></option>
				  <?php }?>
				</select>
			</td>
			<td>
				<select id="CDU_IdTipoPlagas<?php echo $i;?>" name="CDU_IdTipoPlagas[]" class="form-control select2" onChange="ActualizarDatos('CDU_IdTipoPlagas',<?php echo $i;?>,<?php echo $row['ChildNum'];?>);">
				  <option value="">(NINGUNO)</option>
				  <?php while($row_TipoPlaga=sqlsrv_fetch_array($SQL_TipoPlaga)){?>
						<option value="<?php echo $row_TipoPlaga['IdTipoPlagas'];?>" <?php if((isset($row['CDU_IdTipoPlagas']))&&(strcmp($row_TipoPlaga['IdTipoPlagas'],$row['CDU_IdTipoPlagas'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoPlaga['DeTipoPlagas'];?></option>
				  <?php }?>
				</select>
			</td>
			<td>
				<input size="50" type="text" id="CDU_AreasControladas<?php echo $i;?>" name="CDU_AreasControladas[]" class="form-control" value="<?php echo $row['CDU_AreasControladas'];?>" onChange="ActualizarDatos('CDU_AreasControladas',<?php echo $i;?>,<?php echo $row['ChildNum'];?>);">
			</td>
			
			<td><input size="15" type="text" id="Precio<?php echo $i;?>" name="Precio[]" class="form-control" value="<?php echo number_format($row['Precio'],2);?>" onChange="ActualizarDatos('Precio',<?php echo $i;?>,<?php echo $row['ChildNum'];?>);" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" onBlur="CalcularTotal(<?php echo $i;?>);"></td>
			
			<td><input size="15" type="text" id="LineTotal<?php echo $i;?>" name="LineTotal[]" class="form-control" readonly value="<?php echo number_format($row['Total'],2);?>"></td>
			
			<td>
				<select id="ListaPrecio<?php echo $i;?>" name="ListaPrecio[]" class="form-control select2" onChange="ActualizarDatos('IdListaPrecio',<?php echo $i;?>,<?php echo $row['ChildNum'];?>);">
				  <?php while($row_ListaPrecios=sqlsrv_fetch_array($SQL_ListaPrecios)){?>
						<option value="<?php echo $row_ListaPrecios['IdListaPrecio'];?>" <?php if((isset($row['IdListaPrecio']))&&(strcmp($row_ListaPrecios['IdListaPrecio'],$row['IdListaPrecio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ListaPrecios['DeListaPrecio'];?></option>
				  <?php }?>
				</select>
			</td>
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
			<td><input size="15" type="text" id="UndMedidaNew" name="UndMedidaNew" class="form-control"></td>
			<td><input size="15" type="text" id="QuantityNew" name="QuantityNew" class="form-control"></td>
			<td><input size="32" type="text" id="WhsCodeNew" name="WhsCodeNew" class="form-control"></td>
			<td><input size="70" type="text" id="ProyectoNew" name="ProyectoNew" class="form-control"></td>
			<td><input size="18" type="text" id="OcrCodeNew" name="OcrCodeNew" class="form-control"></td>
			<td><input size="18" type="text" id="OcrCode2New" name="OcrCode2New" class="form-control"></td>
			<td><input size="18" type="text" id="OcrCode3New" name="OcrCode3New" class="form-control"></td>			
			<td><input size="12" type="text" id="MetodoEmisionNew" name="MetodoEmisionNew" class="form-control"></td>
			<td><input size="43" type="text" id="CDU_IdServicioNew" name="CDU_IdServicioNew" class="form-control"></td>
			<td><input size="30" type="text" id="CDU_IdMetodoAplicacionNew" name="CDU_IdMetodoAplicacionNew" class="form-control"></td>
			<td><input size="25" type="text" id="CDU_IdTipoPlagasNew" name="CDU_IdTipoPlagasNew" class="form-control"></td>
			<td><input size="50" type="text" id="CDU_AreasControladasNew" name="CDU_AreasControladasNew" class="form-control"></td>
			<td><input size="15" type="text" id="PriceNew" name="PriceNew" class="form-control"></td>
			<td><input size="15" type="text" id="LineTotalNew" name="LineTotalNew" class="form-control"></td>
			<td><input size="24" type="text" id="ListaPrecioNew" name="ListaPrecioNew" class="form-control"></td>
		</tr>
		<?php }?>
		</tbody>
	</table>
	</div>
</form>
<script>
function CalcularTotal(line){
	var TotalLinea=document.getElementById('LineTotal'+line);
	var PrecioLinea=document.getElementById('Precio'+line);
	var CantLinea=document.getElementById('Cantidad'+line);
	var Linea=document.getElementById('LineNum'+line);
	
	if(CantLinea.value>0){
		var Precio=PrecioLinea.value.replace(/,/g, '');
		var Cant=CantLinea.value.replace(/,/g, '');
			
		TotalLinea.value=number_format(Precio*Cant,2);
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
		 
		 var options = {
			url: function(phrase) {
				return "ajx_buscar_datos_json.php?type=12&data="+phrase+"&tipodoc=3&todosart=1";
			},
			getValue: "IdArticulo",
			requestDelay: 400,
			template: {
				type: "description",
				fields: {
					description: "DescripcionArticulo"
				}
			},
			list: {
				maxNumberOfElements: 8,
				match: {
					enabled: true
				},
				onClickEvent: function() {
					var IdArticulo = $("#ItemCodeNew").getSelectedItemData().IdArticulo;
					var DescripcionArticulo = $("#ItemCodeNew").getSelectedItemData().DescripcionArticulo;
					var UndMedida = $("#ItemCodeNew").getSelectedItemData().UndMedida;
					var PrecioSinIVA = $("#ItemCodeNew").getSelectedItemData().PrecioSinIVA;
					var PrecioConIVA = $("#ItemCodeNew").getSelectedItemData().PrecioConIVA;
					var CodAlmacen = $("#ItemCodeNew").getSelectedItemData().CodAlmacen;
					var Almacen = $("#ItemCodeNew").getSelectedItemData().Almacen;
					var StockAlmacen = $("#ItemCodeNew").getSelectedItemData().StockAlmacen;
					var StockGeneral = $("#ItemCodeNew").getSelectedItemData().StockGeneral;
					var Proyecto = window.parent.document.getElementById('Proyecto').value;
					var ListaPrecio = window.parent.document.getElementById('ListaPrecio').value;
					var OcrCode = window.parent.document.getElementById('OcrCode').value;
					var OcrCode2 = window.parent.document.getElementById('OcrCode2').value;
					var OcrCode3 = window.parent.document.getElementById('OcrCode3').value;
					$("#ItemNameNew").val(DescripcionArticulo);
					$("#UnitMsrNew").val(UndMedida);
					$("#QuantityNew").val('1.00');
					$("#PriceNew").val(PrecioSinIVA);
					$("#LineTotalNew").val('0.00');
					$("#WhsCodeNew").val(Almacen);
					$.ajax({
						type: "POST",
						url: "registro.php",
						data:{
							P: '35',
							doctype: '17',
							item: IdArticulo,
							whscode: CodAlmacen,
							id: '<?php echo base64_decode($_GET['id']);?>',
							evento: '<?php echo base64_decode($_GET['evento']);?>',
							lista_precio: btoa(ListaPrecio),
							proyecto: btoa(Proyecto),
							ocrcode: btoa(OcrCode),
							ocrcode2: btoa(OcrCode2),
							ocrcode3: btoa(OcrCode3)
						},
						success: function(response){
							window.location.href="detalle_lista_materiales.php?<?php echo $_SERVER['QUERY_STRING'];?>";
						}
					});
				}
			}
		};
		<?php if($sw==1&&$Estado==1){?> 
		$("#ItemCodeNew").easyAutocomplete(options);
	 	<?php }?>
	});
</script>
</body>
</html>
<?php 
	sqlsrv_close($conexion);
?>