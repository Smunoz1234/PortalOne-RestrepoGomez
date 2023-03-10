<?php
require_once "includes/conexion.php";
PermitirAcceso(407);

// Dimensiones, SMM 22/08/2022
$DimSeries = intval(ObtenerVariable("DimensionSeries"));
$SQL_Dimensiones = Seleccionar('uvw_Sap_tbl_Dimensiones', '*', "DimActive='Y'");

// Pruebas, SMM 22/08/2022
// $SQL_Dimensiones = Seleccionar('uvw_Sap_tbl_Dimensiones', '*', 'DimCode IN (1,2)');

$array_Dimensiones = [];
while ($row_Dimension = sqlsrv_fetch_array($SQL_Dimensiones)) {
    array_push($array_Dimensiones, $row_Dimension);
}
// Hasta aquí, SMM 22/08/2022

$sw = 0;
//$Proyecto="";
$Almacen = "";
$CardCode = "";
$Usuario = "";
$type = 1;
$Id = "";
$Evento = "";
$Estado = 1; //Abierto
$Lotes = 0; //Cantidad de articulos con lotes
$Seriales = 0; //Cantidad de articulos con seriales

// Cambiar bandera de autorización, 16/07/2022
$bandera_autorizacion = false;
if (isset($_GET['autoriza']) && ($_GET['autoriza'] == "1")) {
    $bandera_autorizacion = false;
}

// Se eliminaron las dimensiones, 31/08/2022

if (isset($_GET['id']) && ($_GET['id'] != "")) {
    if ($_GET['type'] == 1) {
        $type = 1;
    } else {
        $type = $_GET['type'];
    }
    if ($type == 1) { //Creando Entrega de Venta
        $where = "Usuario='" . $_GET['usr'] . "' and CardCode='" . $_GET['cardcode'] . "'";
        $SQL = Seleccionar("uvw_tbl_EntregaVentaDetalleCarrito_Borrador", "*", $where);
        // echo $where;

        //Contar si hay articulos con lote
        $SQL_Lotes = Seleccionar("uvw_tbl_EntregaVentaDetalleCarrito_Borrador", "Count(ID_EntregaVentaDetalleCarrito) AS Cant", "Usuario='" . $_GET['usr'] . "' and CardCode='" . $_GET['cardcode'] . "' and ManBtchNum='Y'");
        $row_Lotes = sqlsrv_fetch_array($SQL_Lotes);
        $Lotes = $row_Lotes['Cant'];

        //Contar si hay articulos con seriales
        $SQL_Seriales = Seleccionar("uvw_tbl_EntregaVentaDetalleCarrito_Borrador", "Count(ID_EntregaVentaDetalleCarrito) AS Cant", "Usuario='" . $_GET['usr'] . "' and CardCode='" . $_GET['cardcode'] . "' and ManSerNum='Y'");
        $row_Seriales = sqlsrv_fetch_array($SQL_Seriales);
        $Seriales = $row_Seriales['Cant'];

        if ($SQL) {
            $sw = 1;
            $CardCode = $_GET['cardcode'];
            //$Proyecto=$_GET['prjcode'];
            //$Almacen=$_GET['whscode'];
            $Usuario = $_GET['usr'];
        } else {
            $CardCode = "";
            //$Proyecto="";
            $Almacen = "";
            $Usuario = "";
        }

    } else { //Editando Entrega de venta
        if (isset($_GET['status']) && (base64_decode($_GET['status']) == "C")) {
            $Estado = 2;
        } else {
            $Estado = 1;
        }

        $Id = base64_decode($_GET['id']);
        $Evento = base64_decode($_GET['evento']);

        $SQL = Seleccionar("uvw_tbl_EntregaVentaDetalle_Borrador", "*", "ID_EntregaVenta='" . base64_decode($_GET['id']) . "' and IdEvento='" . base64_decode($_GET['evento']) . "' and Metodo <> 3");

        //Contar si hay articulos con lote
        $SQL_Lotes = Seleccionar("uvw_tbl_EntregaVentaDetalle_Borrador", "Count(ID_EntregaVenta) AS Cant", "ID_EntregaVenta='" . base64_decode($_GET['id']) . "' and IdEvento='" . base64_decode($_GET['evento']) . "' and Metodo <> 3 and ManBtchNum='Y'");
        $row_Lotes = sqlsrv_fetch_array($SQL_Lotes);
        $Lotes = $row_Lotes['Cant'];

        //Contar si hay articulos con seriales
        $SQL_Seriales = Seleccionar("uvw_tbl_EntregaVentaDetalle_Borrador", "Count(ID_EntregaVenta) AS Cant", "ID_EntregaVenta='" . base64_decode($_GET['id']) . "' and IdEvento='" . base64_decode($_GET['evento']) . "' and Metodo <> 3 and ManSerNum='Y'");
        $row_Seriales = sqlsrv_fetch_array($SQL_Seriales);
        $Seriales = $row_Seriales['Cant'];

        if ($SQL) {
            $sw = 1;
        }
    }
}

//Empleado de ventas, SMM 22/02/2022
$SQL_EmpleadosVentas = Seleccionar('uvw_Sap_tbl_EmpleadosVentas', '*', '', 'DE_EmpVentas');

//Servicios
$SQL_Servicios = Seleccionar("uvw_Sap_tbl_OrdenesVentasDetalleServicios", "*", "", "DeServicio");

//Metodo de aplicacion
$SQL_MetodoAplicacion = Seleccionar("uvw_Sap_tbl_OrdenesVentasDetalleMetodoAplicacion", "*", "", "DeMetodoAplicacion");

//Tipo de plagas
$SQL_TipoPlaga = Seleccionar("uvw_Sap_tbl_OrdenesVentasDetalleTipoPlagas", "*", "", "DeTipoPlagas");

//$Almacen=$row['WhsCode'];
$ParamSerie = array(
    "'" . $_SESSION['CodUser'] . "'",
    "'15'",
);
$SQL_Almacen = EjecutarSP('sp_ConsultarAlmacenesUsuario', $ParamSerie);

// Se eliminaron las dimensiones, 31/08/2022

//Proyectos
$SQL_Proyecto = Seleccionar('uvw_Sap_tbl_Proyectos', '*', '', 'DeProyecto');

// Base del Redondeo
$SQL_DatosBase = Seleccionar('uvw_Sap_ConfiguracionSAPB1_DatosBase', '*');

if ($SQL_DatosBase) {
    $row_DatosBase = sqlsrv_fetch_array($SQL_DatosBase);
}

$row_encode = isset($row_DatosBase) ? json_encode($row_DatosBase) : "";
$json_DatosBase = isset($row_DatosBase) ? "JSON.parse(JSON.stringify($row_encode))" : "";

$dImportes = $row_DatosBase["DecimalImportes"] ?? 0; // Useless
$dTasas = $row_DatosBase["DecimalTasas"] ?? 4; // Useless
$dUnidades = $row_DatosBase["DecimalUnidades"] ?? 2; // Useless
$dConsultas = $row_DatosBase["DecimalConsultas"] ?? 4; // Useless
$dPrecios = $row_DatosBase["DecimalPrecios"] ?? 2;
$dCantidades = $row_DatosBase["DecimalCantidades"] ?? 2;
$dPorcentajes = $row_DatosBase["DecimalPorcentajes"] ?? 4;

$sDecimal = $row_DatosBase["CaracterSeparadorDecimal"] ?? ".";
$sMillares = $row_DatosBase["CaracterSeparadorMillares"] ?? ",";
?>

<!doctype html>
<html>
<head>
<?php include_once "includes/cabecera.php";?>
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
	// SMM, 01/06/2022
	<?php if ($json_DatosBase != "") {?>
		var json_DatosBase = <?php echo $json_DatosBase; ?>;

		var dPrecios = json_DatosBase.DecimalPrecios;
		var dCantidades = json_DatosBase.DecimalCantidades;
		var dPorcentajes = json_DatosBase.DecimalPorcentajes;

		var sDecimal = json_DatosBase.CaracterSeparadorDecimal;
		var sMillares = json_DatosBase.CaracterSeparadorMillares;
	<?php } else {?>
		console.log("DatosBase, not found.");

		var dPrecios = 2;
		var dCantidades = 2;
		var dPorcentajes = 4;

		var sDecimal = ".";
		var sMillares = ",";
	<?php }?>
</script>

<script>
function BuscarLote(){
	var posicion_x;
	var posicion_y;
	posicion_x=(screen.width/2)-(1200/2);
	posicion_y=(screen.height/2)-(500/2);
	<?php if ($type == 1) { //Creando Entrega de venta ?>
		var Almacen='<?php echo $Almacen; ?>';
		var CardCode='<?php echo $CardCode; ?>';
		var Lotes='<?php echo $Lotes; ?>'
		if(CardCode!=""&&Lotes>0){
			remote=open('popup_lotes_sap.php?docentry=0&evento=0&edit=<?php echo $type; ?>&usuario=<?php echo $Usuario; ?>&cardcode=<?php echo $CardCode; ?>&objtype=15','remote',"width=1200,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
			remote.focus();
		}
	<?php } else { //Editando Entrega de venta ?>
		remote=open('popup_lotes_sap.php?id=<?php if ($type == 2) {echo $_GET['id'];} else {echo "0";}?>&evento=<?php if ($type == 2) {echo $_GET['evento'];} else {echo "0";}?>&docentry=<?php if ($type == 2) {echo $_GET['docentry'];} else {echo "0";}?>&edit=<?php echo $type; ?>&objtype=15','remote',"width=1200,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
		remote.focus();
	<?php }?>
}

function BuscarSerial(){
	var posicion_x;
	var posicion_y;
	posicion_x=(screen.width/2)-(1200/2);
	posicion_y=(screen.height/2)-(500/2);
	<?php if ($type == 1) { //Creando Entrega de venta ?>
		var Almacen='<?php echo $Almacen; ?>';
		var CardCode='<?php echo $CardCode; ?>';
		var Seriales='<?php echo $Seriales; ?>'
		if(CardCode!=""&&Seriales>0){
			remote=open('popup_seriales_sap.php?docentry=0&evento=0&edit=<?php echo $type; ?>&usuario=<?php echo $Usuario; ?>&cardcode=<?php echo $CardCode; ?>&objtype=15','remote',"width=1200,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
			remote.focus();
		}
	<?php } else { //Editando Entrega de venta ?>
		remote=open('popup_seriales_sap.php?id=<?php if ($type == 2) {echo $_GET['id'];} else {echo "0";}?>&evento=<?php if ($type == 2) {echo $_GET['evento'];} else {echo "0";}?>&docentry=<?php if ($type == 2) {echo $_GET['docentry'];} else {echo "0";}?>&edit=<?php echo $type; ?>&objtype=15','remote',"width=1200,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
		remote.focus();
	<?php }?>
}

function CalcularLinea(line, totalizar=true) {
	let Linea = document.getElementById(`LineNum${line}`); // SMM, 04/04/2022
	let CantLinea = document.getElementById(`Quantity${line}`);
	let PrecioLinea = document.getElementById(`Price${line}`);
	let PrecioIVALinea = document.getElementById(`PriceTax${line}`);
	let TarifaIVALinea = document.getElementById(`TarifaIVA${line}`);
	let ValorIVALinea = document.getElementById(`VatSum${line}`);
	let PrecioDescLinea = document.getElementById(`PriceDisc${line}`); // SMM, 04/04/2022
	let PrcDescLinea = document.getElementById(`DiscPrcnt${line}`);
	let TotalLinea = document.getElementById(`LineTotal${line}`);

	let CantDecimal = parseFloat(CantLinea.value.replace(/,/g, ''));
	let PrecioDecimal = parseFloat(PrecioLinea.value.replace(/,/g, ''));
	let PrecioIVADecimal = parseFloat(PrecioIVALinea.value.replace(/,/g, '')); // Useless
	let TarifaIVADecimal = parseFloat(TarifaIVALinea.value.replace(/,/g, ''));
	let ValorIVADecimal = parseFloat(ValorIVALinea.value.replace(/,/g, '')); // Useless
	let PrecioDescDecimal = parseFloat(PrecioDescLinea.value.replace(/,/g, ''));
	let PrcDescDecimal = parseFloat(PrcDescLinea.value.replace(/,/g, ''));
	let TotalDecimal = parseFloat(TotalLinea.value.replace(/,/g, ''));

	let SubTotalLinea = PrecioDecimal * CantDecimal; // SMM, 12/04/2022
	let TotalDescLinea = (PrcDescDecimal * SubTotalLinea) / 100;
	let SubTotalDesc = SubTotalLinea - TotalDescLinea; // Para, Totalizar()

	let ControlDesc = document.getElementById(`ControlDesc${line}`).checked;
	if(totalizar && ControlDesc == false) {
		TotalLinea.value = number_format(SubTotalDesc, dPrecios, sDecimal, sMillares);
		ActualizarDatos('LineTotal', line, Linea.value, dPrecios);
	}

	// SMM, 04/04/2022
	let SubTotalDescLinea = (PrcDescDecimal * PrecioDecimal) / 100;
	let NuevoPrecioDesc = PrecioDecimal - SubTotalDescLinea;

	PrecioDescLinea.value = number_format(NuevoPrecioDesc, dPrecios, sDecimal, sMillares);

	// SMM, 12/04/2022
	let NuevoValorIVA = NuevoPrecioDesc * (TarifaIVADecimal / 100);
	ValorIVALinea.value = number_format(NuevoValorIVA, dPrecios, sDecimal, sMillares);
	let NuevoPrecioIVA = NuevoPrecioDesc + NuevoValorIVA;
	PrecioIVALinea.value = number_format(NuevoPrecioIVA, dPrecios, sDecimal, sMillares);

	let IvaLinea = NuevoValorIVA * CantDecimal; // Para, Totalizar()

	<?php if ($type != 1) {?> // SMM, 05/05/2022
		if(!totalizar) {
			if(number_format(SubTotalDesc, dPrecios, sDecimal, sMillares) != number_format(TotalDecimal, dPrecios, sDecimal, sMillares)) {
				console.log(`${number_format(SubTotalDesc, dPrecios, sDecimal, sMillares)} != ${number_format(TotalDecimal, dPrecios, sDecimal, sMillares)}`);
				$(`#ControlDesc${line}`).prop("checked", true);

			} else { // SMM, 15/04/2022
				console.log(`${number_format(SubTotalDesc, dPrecios, sDecimal, sMillares)} == ${number_format(TotalDecimal, dPrecios, sDecimal, sMillares)}`);
				// $(`#ControlDesc${line}`).prop("checked", false);
			}
		}
	<?php }?>

	ActualizarDatos('ControlDesc', line, Linea.value);

	let NuevoSubTotal = SubTotalDesc;
	let NuevoIVA = IvaLinea;

	ControlDesc = document.getElementById(`ControlDesc${line}`).checked;
	if(ControlDesc == true) { // 14/04/2022
		NuevoSubTotal = TotalDecimal;
		NuevoIVA = TotalDecimal * (TarifaIVADecimal / 100);
	}

	return [NuevoSubTotal, NuevoIVA, Linea, SubTotalLinea, CantLinea, PrcDescLinea, TotalLinea, TotalDecimal, PrecioDescDecimal];
}

function Totalizar(num, totalizar=true) {
	var SubTotal = 0;
	var Descuentos = 0;
	var Iva = 0;
	var Total = 0;

	console.log(`Totalizar(${num}, ${totalizar})`);
	for(let i=1; i <= num; i++) {
		let ValoresLinea = CalcularLinea(i, totalizar);

		let NuevoSubTotal = ValoresLinea[0];
		let IvaLinea = ValoresLinea[1];

		SubTotal = parseFloat(SubTotal) + parseFloat(NuevoSubTotal);

		let Exento = document.getElementById(`SujetoImpuesto${i}`);
		if(!Exento.checked) {
			Iva = parseFloat(Iva) + parseFloat(IvaLinea);
		}
	}

	// Total = parseFloat(Total) + parseFloat((SubTotal - Descuentos) + Iva);
	Total = parseFloat(parseFloat(SubTotal).toFixed(dPrecios)) + parseFloat(parseFloat(Iva).toFixed(dPrecios)); // SMM 18/04/2022

	window.parent.document.getElementById('SubTotal').value = number_format(parseFloat(SubTotal), dPrecios);
	window.parent.document.getElementById('Impuestos').value = number_format(parseFloat(Iva), dPrecios);

	window.parent.document.getElementById('Redondeo').value = number_format(Math.floor(Math.round(Total)) - parseFloat(parseFloat(Total).toFixed(dPrecios)), dPrecios);
	window.parent.document.getElementById('TotalEntrega').value = number_format(Math.floor(Math.round(Total)), dPrecios);

	window.parent.document.getElementById('TotalItems').value = num;
}

function ActualizarDatos(name, id, line, round=0) { // Actualizar datos asincronicamente
	console.log(`ActualizarDatos(${name}, ${id}, ${line}, ${round})`);

	let elemento = document.getElementById(name+id);
	let valor = elemento.value;

	if(round > 0) {
		elemento.value = number_format(valor, round, sDecimal, sMillares);
	}

	if(name == "ControlDesc"){ // SMM, 11/04/2022
		if(elemento.checked) {
			valor = 'T';
		} else {
			valor = 'X';
		}
	} else if(name == "LineTotal") { // SMM, 18/04/2022
		valor = valor.replace(/,/g, '');
	} else if(name == "SujetoImpuesto"){ // SMM, 23/04/2022
		if(elemento.checked) {
			valor = 'N';
		} else {
			valor = 'Y';
		}
	}

	$.ajax({
		type: "GET",
		<?php if ($type == 1) {?>
		url: "registro.php?P=36&borrador=1&doctype=3&type=1&name="+name+"&value="+Base64.encode(valor)+"&line="+line+"&cardcode=<?php echo $CardCode; ?>&whscode=<?php echo $Almacen; ?>&actodos=0",
		<?php } else {?>
		url: "registro.php?P=36&borrador=1&doctype=3&type=2&name="+name+"&value="+Base64.encode(valor)+"&line="+line+"&id=<?php echo base64_decode($_GET['id']); ?>&evento=<?php echo base64_decode($_GET['evento']); ?>&actodos=0",
		<?php }?>
		success: function(response){
			if(response!="Error"){
				window.parent.document.getElementById('TimeAct').innerHTML="<strong>Actualizado:</strong> "+response;
			}
		},
		error: function(error) {
			console.log(error);
		}
	});
}

// ¿ SujetoImpuesto, Exento de IVA ?
function SujetoImpuesto() {
		let exento = window.parent.document.getElementById('Exento').value;

		if(exento == 'N') {
			console.log("Cliente extranjero, exento de IVA");

			$(".chkExento").each(function() {
				// console.log($(this).data("exento"));

				if($(this).data("exento") != 'Y') {
					$(this).prop("checked", true);
					$(this).trigger('change');
				}
			});
		}
}
// SMM, 23/04/2022

function ActStockAlmacen(name,id,line){//Actualizar el stock al cambiar el almacen
	if(false) { // Se desactivo esta función en los documentos en borrador. SMM, 03/10/2022
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=34&edit=<?php echo $type; ?>&whscode="+document.getElementById(name+id).value+"&linenum="+line+"&cardcode=<?php echo $CardCode; ?>&id=<?php echo $Id; ?>&evento=<?php echo $Evento; ?>&tdoc=15",
			success: function(response){
				if(response!="Error"){
					document.getElementById("OnHand"+id).value=number_format(response, dCantidades, sDecimal, sMillares);
				}
			},
			error: function(error){
				console.error(error.responseText);
			}
		});
	}
}

// Stiven Muñoz Murillo, 27/01/2022
var json=[];
var cant=0;

function BorrarLinea(){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea eliminar este item? Este proceso no se puede revertir.')){
		$.ajax({
			type: "GET",
			<?php if ($type == 1) {?>
			url: "includes/procedimientos.php?type=8&borrador=1&edit=<?php echo $type; ?>&linenum="+json+"&cardcode=<?php echo $CardCode; ?>",
			<?php } else {?>
			url: "includes/procedimientos.php?type=8&borrador=1&edit=<?php echo $type; ?>&linenum="+json+"&id=<?php echo base64_decode($_GET['id']); ?>&evento=<?php echo base64_decode($_GET['evento']); ?>",
			<?php }?>
			success: function(response){
				window.location.href="detalle_entrega_venta_borrador.php?<?php echo $_SERVER['QUERY_STRING']; ?>";
				console.log(response);
			},
			error: function(error){
				console.error(error.responseText);
			}
		});
	}
}

// SMM, 27/04/2022
function DuplicarLinea(){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea duplicar estos registros?')){
		$.ajax({
			type: "GET",
			<?php if ($type == 1) {?>
			url: "includes/procedimientos.php?type=55&borrador=1&edit=<?php echo $type; ?>&linenum="+json+"&cardcode=<?php echo $CardCode; ?>",
			<?php } else {?>
			url: "includes/procedimientos.php?type=55&borrador=1&edit=<?php echo $type; ?>&linenum="+json+"&id=<?php echo base64_decode($_GET['id']); ?>&evento=<?php echo base64_decode($_GET['evento']); ?>",
			<?php }?>
			success: function(response){
				window.location.href="detalle_entrega_venta_borrador.php?<?php echo $_SERVER['QUERY_STRING']; ?>";
			},
			error: function(error) {
				console.log(error.responseText);
			}
		});
	}
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
	$(".chkSel:not(:disabled)").prop("checked", Check);

	if(Check){
		$(".chkSel:not(:disabled)").trigger('change');
	}
}

// SMM, 10/03/2022
function ConsultarArticulo(articulo){
	if(articulo!=""){
		self.name='opener';
		remote=open('articulos.php?id='+articulo+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
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
				<th class="text-center form-inline w-150">
					<div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div>
					<button type="button" id="btnBorrarLineas" title="Borrar lineas" class="btn btn-danger btn-xs" disabled onClick="BorrarLinea();"><i class="fa fa-trash"></i></button>
					<button type="button" id="btnDuplicarLineas" title="Duplicar lineas" class="btn btn-success btn-xs" disabled onClick="DuplicarLinea();"><i class="fa fa-copy"></i></button>
				</th>
				<th>Código artículo</th>
				<th>Nombre artículo</th>
				<th>Unidad</th>
				<th>Cantidad<?php if ($Lotes > 0) {?><span class="badge badge-info pull-right" title="Ver lotes (Alt+Q)" style="cursor: pointer;" onClick="BuscarLote();"><i class="fa fa-tasks"></i></span><?php }?><?php if ($Seriales > 0) {?><span class="badge badge-success pull-right" title="Ver seriales (Alt+Y)" style="cursor: pointer;" onClick="BuscarSerial();"><i class="fa fa-barcode"></i></span><?php }?></th>
				<th>Cant. Pendiente</th>
				<th>Cant. Litros</th>
				<th>Almacén</th>
				<th>Dosificación</th>
				<th>Stock almacén</th>

				<!-- Dimensiones dinámicas, SMM 22/08/2022 -->
				<?php foreach ($array_Dimensiones as &$dim) {?>
					<th><?php echo $dim["DimDesc"]; ?></th>
				<?php }?>
				<!-- Dimensiones dinámicas, hasta aquí -->

				<th>Proyecto</th>
				<th>Empleado de ventas</th>
				<th>Servicio</th>
				<th>Método aplicación</th>
				<th>Tipo plaga</th>
				<th>Áreas controladas</th>
				<th>Texto libre</th>
				<th>Precio</th>
				<th>Precio con IVA</th>
				<th>Precio con Desc.</th><!-- SMM, 05/04/2022 -->
				<th>% Desc.</th>
				<th>Total</th>
				<th>CtrlDesc</th><!-- SMM, 10/05/2022 -->
				<th>Exento</th><!-- SMM, 23/04/2022 -->
				<th><i class="fa fa-refresh"></i></th>
			</tr>
		</thead>
		<tbody>
		<?php
if ($sw == 1) {
    $i = 1;
    while ($row = sqlsrv_fetch_array($SQL)) {
        /**** Campos definidos por el usuario ****/
        sqlsrv_fetch($SQL_Servicios, SQLSRV_SCROLL_ABSOLUTE, -1);
        sqlsrv_fetch($SQL_MetodoAplicacion, SQLSRV_SCROLL_ABSOLUTE, -1);
        sqlsrv_fetch($SQL_TipoPlaga, SQLSRV_SCROLL_ABSOLUTE, -1);
        sqlsrv_fetch($SQL_Almacen, SQLSRV_SCROLL_ABSOLUTE, -1);

        // Se eliminaron las dimensiones, 31/08/2022

        sqlsrv_fetch($SQL_Proyecto, SQLSRV_SCROLL_ABSOLUTE, -1);
        sqlsrv_fetch($SQL_EmpleadosVentas, SQLSRV_SCROLL_ABSOLUTE, -1); // SMM, 22/02/2022
        ?>
		<tr>
			<td class="text-center form-inline w-150">
				<div class="checkbox checkbox-success"><input type="checkbox" class="chkSel" id="chkSel<?php echo $row['LineNum']; ?>" value="" onChange="Seleccionar('<?php echo $row['LineNum']; ?>');" aria-label="Single checkbox One" <?php if (($row['LineStatus'] == "C") && ($type == 1) || $bandera_autorizacion) {echo "disabled='disabled'";}?>><label></label></div>
				<button type="button" class="btn btn-success btn-xs" onClick="ConsultarArticulo('<?php echo base64_encode($row['ItemCode']); ?>');" title="Consultar Articulo"><i class="fa fa-search"></i></button> <!-- SMM, 10/03/2022 -->
			</td>

			<td>
				<input size="20" type="text" id="ItemCode<?php echo $i; ?>" name="ItemCode[]" class="form-control" readonly value="<?php echo $row['ItemCode']; ?>">
				<input type="hidden" name="LineNum[]" id="LineNum<?php echo $i; ?>" value="<?php echo $row['LineNum']; ?>">
			</td>

			<td><input size="50" type="text" autocomplete="off" id="ItemName<?php echo $i; ?>" name="ItemName[]" class="form-control" value="<?php echo str_replace('"', '', $row['ItemName']); ?>" maxlength="100" onChange="ActualizarDatos('ItemName',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>);" <?php if (($row['LineStatus'] == 'C') || ($type == 2)) {echo "readonly";}?>></td>

			<td><input size="15" type="text" id="UnitMsr<?php echo $i; ?>" name="UnitMsr[]" class="form-control" readonly value="<?php echo $row['UnitMsr']; ?>"></td>

			<td>
				<input size="15" type="text" autocomplete="off" id="Quantity<?php echo $i; ?>" name="Quantity[]" class="form-control" value="<?php echo number_format($row['Quantity'], $dCantidades, $sDecimal, $sMillares); ?>" onChange="ActualizarDatos('Quantity',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>, <?php echo $dCantidades; ?>);" onBlur="CalcularTotal(<?php echo $i; ?>);" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" <?php if (($row['LineStatus'] == 'C') || ($type == 2)) {echo "readonly";}?> onFocus="focalizarValores(this)">
			</td>

			<td><input size="15" type="text" id="CantInicial<?php echo $i; ?>" name="CantInicial[]" class="form-control" value="<?php echo number_format($row['CantInicial'], $dCantidades, $sDecimal, $sMillares); ?>" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" readonly></td>

			<td><input size="15" type="text" autocomplete="off" id="CDU_CantLitros<?php echo $i; ?>" name="CDU_CantLitros[]" class="form-control" value="<?php echo number_format($row['CDU_CantLitros'], $dCantidades, $sDecimal, $sMillares); ?>" onChange="ActualizarDatos('CDU_CantLitros',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>);" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" <?php if ($row['LineStatus'] == 'C' || (!PermitirFuncion(402)) || $bandera_autorizacion) {echo "readonly";}?>></td>

			<td>
				<select id="WhsCode<?php echo $i; ?>" name="WhsCode[]" class="form-control select2" onChange="ActualizarDatos('WhsCode',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>);ActStockAlmacen('WhsCode',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>);" <?php if ($row['LineStatus'] == 'C' || (!PermitirFuncion(402)) || $bandera_autorizacion) {echo "disabled='disabled'";}?>>
				  <option value="">(NINGUNO)</option>
				  <?php while ($row_Almacen = sqlsrv_fetch_array($SQL_Almacen)) {?>
						<option value="<?php echo $row_Almacen['WhsCode']; ?>" <?php if ((isset($row['WhsCode'])) && (strcmp($row_Almacen['WhsCode'], $row['WhsCode']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Almacen['WhsName']; ?></option>
				  <?php }?>
				</select>
			</td>
			<td><input size="15" type="text" id="CDU_Dosificacion<?php echo $i; ?>" name="CDU_Dosificacion[]" class="form-control" value="<?php echo number_format($row['CDU_Dosificacion'], $dCantidades, $sDecimal, $sMillares); ?>" onChange="ActualizarDatos('CDU_Dosificacion',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>);" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" <?php if ($row['LineStatus'] == 'C') {echo "readonly";}?>></td>
			<td><input size="15" type="text" id="OnHand<?php echo $i; ?>" name="OnHand[]" class="form-control" value="<?php echo number_format($row['OnHand'], $dCantidades, $sDecimal, $sMillares); ?>" readonly></td>

			<!-- Dimensiones dinámicas, SMM 22/08/2022 -->
			<?php foreach ($array_Dimensiones as &$dim) {?>
				<?php $DimCode = intval($dim['DimCode']);?>
				<?php $OcrId = ($DimCode == 1) ? "" : $DimCode;?>

				<td>
					<select id="OcrCode<?php echo $OcrId . $i; ?>" name="OcrCode<?php echo $OcrId; ?>[]" class="form-control select2" onChange="ActualizarDatos('OcrCode<?php echo $OcrId; ?>',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>);" <?php if ($row['LineStatus'] == 'C' || (!PermitirFuncion(402))) {echo "disabled='disabled'";}?>>
						<option value="">(NINGUNO)</option>

						<?php $SQL_Dim = Seleccionar('uvw_Sap_tbl_DimensionesReparto', '*', "DimCode=$DimCode");?>
						<?php while ($row_Dim = sqlsrv_fetch_array($SQL_Dim)) {?>
							<option value="<?php echo $row_Dim['OcrCode']; ?>" <?php if ((isset($row["OcrCode$OcrId"])) && (strcmp($row_Dim['OcrCode'], $row["OcrCode$OcrId"]) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Dim['OcrName']; ?></option>
						<?php }?>
					</select>
				</td>
			<?php }?>
			<!-- Dimensiones dinámicas, hasta aquí -->

			<td>
				<select id="PrjCode<?php echo $i; ?>" name="PrjCode[]" class="form-control select2" onChange="ActualizarDatos('PrjCode',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>);" <?php if ($row['LineStatus'] == 'C' || (!PermitirFuncion(402)) || $bandera_autorizacion) {echo "disabled='disabled'";}?>>
					<option value="">(NINGUNO)</option>
				  <?php while ($row_Proyecto = sqlsrv_fetch_array($SQL_Proyecto)) {?>
						<option value="<?php echo $row_Proyecto['IdProyecto']; ?>" <?php if ((isset($row['PrjCode'])) && (strcmp($row_Proyecto['IdProyecto'], $row['PrjCode']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Proyecto['DeProyecto']; ?></option>
				  <?php }?>
				</select>
			</td>

			<td> <!-- SMM, 22/02/2022 -->
				<select id="EmpVentas<?php echo $i; ?>" name="EmpVentas[]" class="form-control select2" onChange="ActualizarDatos('EmpVentas',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>);" <?php if ($row['LineStatus'] == 'C' || (!PermitirFuncion(402)) || $bandera_autorizacion) {echo "disabled='disabled'";}?>>
						<option value="">(NINGUNO)</option>
					<?php while ($row_EmpleadosVentas = sqlsrv_fetch_array($SQL_EmpleadosVentas)) {?>
						<option value="<?php echo $row_EmpleadosVentas['ID_EmpVentas']; ?>" <?php if ((isset($row['EmpVentas'])) && (strcmp($row_EmpleadosVentas['ID_EmpVentas'], $row['EmpVentas']) == 0)) {echo "selected=\"selected\"";}?>>
							<?php echo $row_EmpleadosVentas['DE_EmpVentas']; ?>
						</option>
					<?php }?>
				</select>
			</td>

			<td><?php if (($row['TreeType'] != "T") || (($row['TreeType'] == "T") && ($row['LineNum'] != 0))) {?>
				<select id="CDU_IdServicio<?php echo $i; ?>" name="CDU_IdServicio[]" class="form-control select2" onChange="ActualizarDatos('CDU_IdServicio',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>);" <?php if ($row['LineStatus'] == 'C' || (!PermitirFuncion(402)) || $bandera_autorizacion) {echo "disabled='disabled'";}?>>
				  <option value="">(NINGUNO)</option>
				  <?php while ($row_Servicios = sqlsrv_fetch_array($SQL_Servicios)) {?>
						<option value="<?php echo $row_Servicios['IdServicio']; ?>" <?php if ((isset($row['CDU_IdServicio'])) && (strcmp($row_Servicios['IdServicio'], $row['CDU_IdServicio']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Servicios['DeServicio']; ?></option>
				  <?php }?>
				</select>
				<?php }?>
			</td>
			<td><?php if (($row['TreeType'] != "T") || (($row['TreeType'] == "T") && ($row['LineNum'] != 0))) {?>
				<select id="CDU_IdMetodoAplicacion<?php echo $i; ?>" name="CDU_IdMetodoAplicacion[]" class="form-control select2" onChange="ActualizarDatos('CDU_IdMetodoAplicacion',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>);ActDosificacion(<?php echo $i; ?>);" <?php if ($row['LineStatus'] == 'C' || (!PermitirFuncion(402)) || $bandera_autorizacion) {echo "disabled='disabled'";}?>>
				  <option value="">(NINGUNO)</option>
				  <?php while ($row_MetodoAplicacion = sqlsrv_fetch_array($SQL_MetodoAplicacion)) {?>
						<option value="<?php echo $row_MetodoAplicacion['IdMetodoAplicacion']; ?>" <?php if ((isset($row['CDU_IdMetodoAplicacion'])) && (strcmp($row_MetodoAplicacion['IdMetodoAplicacion'], $row['CDU_IdMetodoAplicacion']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_MetodoAplicacion['DeMetodoAplicacion']; ?></option>
				  <?php }?>
				</select>
				<?php }?>
			</td>
			<td><?php if (($row['TreeType'] != "T") || (($row['TreeType'] == "T") && ($row['LineNum'] != 0))) {?>
				<select id="CDU_IdTipoPlagas<?php echo $i; ?>" name="CDU_IdTipoPlagas[]" class="form-control select2" onChange="ActualizarDatos('CDU_IdTipoPlagas',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>);" <?php if ($row['LineStatus'] == 'C' || (!PermitirFuncion(402)) || $bandera_autorizacion) {echo "disabled='disabled'";}?>>
				  <option value="">(NINGUNO)</option>
				  <?php while ($row_TipoPlaga = sqlsrv_fetch_array($SQL_TipoPlaga)) {?>
						<option value="<?php echo $row_TipoPlaga['IdTipoPlagas']; ?>" <?php if ((isset($row['CDU_IdTipoPlagas'])) && (strcmp($row_TipoPlaga['IdTipoPlagas'], $row['CDU_IdTipoPlagas']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_TipoPlaga['DeTipoPlagas']; ?></option>
				  <?php }?>
				</select>
				<?php }?>
			</td>
			<td><?php if (($row['TreeType'] != "T") || (($row['TreeType'] == "T") && ($row['LineNum'] != 0))) {?>
				<input size="50" type="text" id="CDU_AreasControladas<?php echo $i; ?>" name="CDU_AreasControladas[]" class="form-control" value="<?php echo $row['CDU_AreasControladas']; ?>" onChange="ActualizarDatos('CDU_AreasControladas',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>);" <?php if ($row['LineStatus'] == 'C' || (!PermitirFuncion(402)) || $bandera_autorizacion) {echo "readonly";}?>>
				<?php }?>
			</td>

			<td><input size="50" type="text" id="FreeTxt<?php echo $i; ?>" name="FreeTxt[]" class="form-control" value="<?php echo $row['FreeTxt']; ?>" onChange="ActualizarDatos('FreeTxt',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>);" maxlength="100" <?php if (($row['LineStatus'] == 'C') || ($type == 2)) {echo "readonly";}?>></td>

			<td>
				<input size="15" type="text" id="Price<?php echo $i; ?>" name="Price[]" class="form-control" value="<?php echo number_format($row['Price'], $dPrecios, $sDecimal, $sMillares); ?>" onChange="ActualizarDatos('Price',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>, <?php echo $dPrecios; ?>);" onBlur="CalcularTotal(<?php echo $i; ?>);" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" <?php if (($row['LineStatus'] == 'C') || ($type == 2) || !PermitirFuncion(420)) {echo "readonly";}?> onFocus="focalizarValores(this)">
			</td>

			<td>
				<input size="15" type="text" id="PriceTax<?php echo $i; ?>" name="PriceTax[]" class="form-control" value="<?php echo number_format($row['PriceTax'], $dPrecios, $sDecimal, $sMillares); ?>" onBlur="CalcularTotal(<?php echo $i; ?>);" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" readonly onFocus="focalizarValores(this)">
				<input type="hidden" id="TarifaIVA<?php echo $i; ?>" name="TarifaIVA[]" value="<?php echo number_format($row['TarifaIVA'], 0, $sDecimal, $sMillares); ?>">
				<input type="hidden" id="VatSum<?php echo $i; ?>" name="VatSum[]" value="<?php echo number_format($row['VatSum'], 2, $sDecimal, $sMillares); ?>">
			</td>

			<td>
				<input size="15" type="text" id="PriceDisc<?php echo $i; ?>" name="PriceDisc[]" class="form-control" value="0" onBlur="CalcularTotal(<?php echo $i; ?>);" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" readonly onFocus="focalizarValores(this)">
			</td>

			<td>
				<input size="15" type="text" id="DiscPrcnt<?php echo $i; ?>" name="DiscPrcnt[]" class="form-control" value="<?php echo number_format($row['DiscPrcnt'], $dPorcentajes, $sDecimal, $sMillares); ?>" onChange="ActualizarDatos('DiscPrcnt',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>, <?php echo $dPorcentajes; ?>);" onBlur="CalcularTotal(<?php echo $i; ?>);" onKeyUp="revisaCadena(this, <?php echo $dPorcentajes; ?>);" onKeyPress="return justNumbers(event,this.value);" <?php if (($row['LineStatus'] == 'C') || ($type == 2)) {echo "readonly";}?> onFocus="focalizarValores(this)">
			</td>

			<td>
				<input size="15" type="text" id="LineTotal<?php echo $i; ?>" name="LineTotal[]" class="form-control" value="<?php echo number_format($row['LineTotal'], $dPrecios, $sDecimal, $sMillares); ?>" onChange="ActualizarDatos('LineTotal',<?php echo $i; ?>,<?php echo $row['LineNum']; ?>, <?php echo $dPrecios; ?>);" onBlur="CalcularTotal(<?php echo $i; ?>, false);" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" <?php if ($row['LineStatus'] == 'C' || ($type == 2)) {echo "readonly";}?> autocomplete="off" onFocus="focalizarValores(this)">
			</td>

			<td>
				<input type="checkbox" id="ControlDesc<?php echo $i; ?>" name="ControlDesc[]" class="form-control" onChange="ActualizarDatos('ControlDesc',<?php echo $i; ?>, <?php echo $row['LineNum']; ?>);" <?php if (isset($row['ControlDesc']) && ($row['ControlDesc'] == "T")) {echo "checked";}?> disabled>
			</td>

			<td>
				<input type="checkbox" id="SujetoImpuesto<?php echo $i; ?>" name="SujetoImpuesto[]" class="form-control chkExento" onChange="ActualizarDatos('SujetoImpuesto',<?php echo $i; ?>, <?php echo $row['LineNum']; ?>);" <?php if (isset($row['SujetoImpuesto']) && ($row['SujetoImpuesto'] == "N")) {echo "checked";}?> data-exento="<?php echo $row['SujetoImpuesto']; ?>" disabled>
			</td>

			<td><?php if ($row['Metodo'] == 0) {?><i class="fa fa-check-circle text-info" title="Sincronizado con SAP"></i><?php } else {?><i class="fa fa-times-circle text-danger" title="Aún no enviado a SAP"></i><?php }?></td>
		</tr>
		<?php

        // Cambio de Stock en Lote
        $LineNum = $row['LineNum'];
        echo "<script> ActStockAlmacen('WhsCode', $i, $LineNum); </script>";
        // SMM, 30/03/2022

        $i++;}

    echo "<script>SujetoImpuesto();</script>";
    echo "<script> Totalizar(" . ($i - 1) . ", false); </script>";
}
?>
		<?php if ($Estado == 1) {?>
		<tr>
			<td>&nbsp;</td>
			<td><input size="20" type="text" id="ItemCodeNew" name="ItemCodeNew" class="form-control"></td>
			<td><input size="50" type="text" id="ItemNameNew" name="ItemNameNew" class="form-control"></td>
			<td><input size="15" type="text" id="UnitMsrNew" name="UnitMsrNew" class="form-control"></td>
			<td><input size="15" type="text" id="QuantityNew" name="QuantityNew" class="form-control"></td>
			<td><input size="15" type="text" id="CantInicialNew" name="CantInicialNew" class="form-control"></td>
			<td><input size="15" type="text" id="CDU_CantLitrosNew" name="CDU_CantLitrosNew" class="form-control"></td>
			<td><input size="20" type="text" id="WhsCodeNew" name="WhsCodeNew" class="form-control"></td>
			<td><input size="15" type="text" id="CDU_DosificacionNew" name="CDU_DosificacionNew" class="form-control"></td>
			<td><input size="15" type="text" id="OnHandNew" name="OnHandNew" class="form-control"></td>

			<td><input size="20" type="text" id="OcrCodeNew" name="OcrCodeNew" class="form-control"></td>
			<td><input size="20" type="text" id="OcrCode2New" name="OcrCode2New" class="form-control"></td>
			<td><input size="20" type="text" id="OcrCode3New" name="OcrCode3New" class="form-control"></td>
			<td><input size="70" type="text" id="ProyectoNew" name="ProyectoNew" class="form-control"></td>

			<td><input size="43" type="text" id="CDU_IdServicioNew" name="CDU_IdServicioNew" class="form-control"></td>
			<td><input size="30" type="text" id="CDU_IdMetodoAplicacionNew" name="CDU_IdMetodoAplicacionNew" class="form-control"></td>
			<td><input size="30" type="text" id="CDU_IdTipoPlagasNew" name="CDU_IdTipoPlagasNew" class="form-control"></td>
			<td><input size="50" type="text" id="CDU_AreasControladasNew" name="CDU_AreasControladasNew" class="form-control"></td>

			<td><input size="50" type="text" id="FreeTxtNew" name="FreeTxtNew" class="form-control"></td>
			<td><input size="15" type="text" id="PriceNew" name="PriceNew" class="form-control"></td>
			<td><input size="15" type="text" id="PriceTaxNew" name="PriceTaxNew" class="form-control"></td>
			<td><input size="15" type="text" id="DiscPrcntNew" name="DiscPrcntNew" class="form-control"></td>
			<td><input size="15" type="text" id="LineTotalNew" name="LineTotalNew" class="form-control"></td>
			<td>&nbsp;</td>
		</tr>
		<?php }?>
		</tbody>
	</table>
	</div>
</form>

<script>
function focalizarValores(elemento) {
	elemento.value = parseFloat(elemento.value.replace(/,/g, ''));
}

function CalcularTotal(line, totalizar=true) {
	console.log(`CalcularTotal(${line}, ${totalizar})`);

	let ValoresLinea = CalcularLinea(line, totalizar);

	let Linea = ValoresLinea[2];
	let SubTotalLinea = ValoresLinea[3];
	let CantLinea = ValoresLinea[4];
	let PrcDescLinea = ValoresLinea[5];
	let TotalLinea = ValoresLinea[6];
	let TotalDecimal = ValoresLinea[7];
	let PrecioDescDecimal = ValoresLinea[8];

	// console.log("TotalDecimal", TotalDecimal);
	// console.log("PrecioDescDecimal", PrecioDescDecimal);

	if(CantLinea.value > 0) {
		if(totalizar) {
			$(`#ControlDesc${line}`).prop("checked", false);

			console.log("TOTALIZAR");
			Totalizar(<?php if (isset($i)) {echo $i - 1;} else {echo 0;}?>);
		} else { // SMM 28/03/2022
			// console.log("SubTotalLinea", SubTotalLinea);

			if(TotalDecimal < SubTotalLinea) {
				TotalDescLinea = SubTotalLinea - TotalDecimal;
				console.log("TotalDescLinea", TotalDescLinea);

				PrcDesc = 100 / (SubTotalLinea / TotalDescLinea);
				console.log("PrcDesc", PrcDesc);

				PrcDescLinea.value = number_format(PrcDesc, dPorcentajes, sDecimal, sMillares);
				TotalLinea.value = number_format(TotalDecimal, dPrecios, sDecimal, sMillares);

				// SMM, 10/05/2022
				if(TotalDecimal != PrecioDescDecimal) {
					$(`#ControlDesc${line}`).prop("checked", true);
				} // Para que no se afecte con la tecla [TAB]

				// SMM, 11/04/2022
				ActualizarDatos('DiscPrcnt', line, Linea.value, dPorcentajes);
				Totalizar(<?php if (isset($i)) {echo $i - 1;} else {echo 0;}?>, false);
			} else {
				console.log(`${TotalDecimal} >= ${SubTotalLinea}`);
				alert("El nuevo total de línea debe ser menor al total de línea sin descuento (SubTotalLinea).");
			}
		}
	} else {
		alert("No puede solicitar cantidad en 0. Si ya no va a solicitar este articulo, borre la linea.");

		CantLinea.value = "1.00";
		ActualizarDatos('Quantity', line, Linea.value, dCantidades);
	}
}
</script>

<script>
	 $(document).ready(function(){
		 $(".alkin").on('click', function(){
				 $('.ibox-content').toggleClass('sk-loading');
			});
		  $(".select2").select2();

		shortcut.add("Alt+Q",function() {
               BuscarLote();
        });

		shortcut.add("Alt+Y",function() {
               BuscarSerial();
        });

		 var options = {
			url: function(phrase) {
				return "ajx_buscar_datos_json.php?type=12&data="+phrase+"&whscode=<?php echo $Almacen; ?>&tipodoc=2";
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
					$("#ItemNameNew").val(DescripcionArticulo);
					$("#UnitMsrNew").val(UndMedida);
					$("#QuantityNew").val('1.00');
					$("#CantInicialNew").val('1.00');
					$("#CDU_CantLitrosNew").val('1.00');
					$("#PriceNew").val(PrecioSinIVA);
					$("#PriceTaxNew").val(PrecioConIVA);
					$("#DiscPrcntNew").val('0.00');
					$("#LineTotalNew").val('0.00');
					$("#OnHandNew").val(StockAlmacen);
					$("#WhsCodeNew").val(Almacen);
					$.ajax({
						type: "GET",
						<?php if ($type == 1) {?>
						url: "registro.php?P=35&borrador=1&doctype=5&item="+IdArticulo+"&whscode="+CodAlmacen+"&cardcode=<?php echo $CardCode; ?>",
						<?php } else {?>
						url: "registro.php?P=35&borrador=1&doctype=6&item="+IdArticulo+"&whscode="+CodAlmacen+"&cardcode=0&id=<?php echo base64_decode($_GET['id']); ?>&evento=<?php echo base64_decode($_GET['evento']); ?>",
						<?php }?>
						success: function(response){
							window.location.href="detalle_entrega_venta_borrador.php?<?php echo $_SERVER['QUERY_STRING']; ?>";
						}
					});
				}
			}
		};
		<?php if ($sw == 1 && $Estado == 1 && $type == 1 && PermitirFuncion(404)) {?>
		$("#ItemCodeNew").easyAutocomplete(options);
	 	<?php }?>
	});
</script>
</body>
</html>
<?php
sqlsrv_close($conexion);
?>