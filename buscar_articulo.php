<?php 
if(!isset($_GET['dato'])||($_GET['dato']=="")){
	exit();
}else{
	require_once("includes/conexion.php");
	$Dato=$_GET['dato'];
	$CardCode=isset($_GET['cardcode']) ? $_GET['cardcode'] : "";
	$Almacen=isset($_GET['whscode']) ? $_GET['whscode'] : "";
	$AlmacenDestino=isset($_GET['towhscode']) ? $_GET['towhscode'] : "";
	$TodosArticulos=isset($_GET['todosart']) ? $_GET['todosart'] : 0;
	$TipoDoc=isset($_GET['tipodoc']) ? $_GET['tipodoc'] : 1;
	$SoloStock=isset($_GET['solostock']) ? $_GET['solostock'] : 1;
	$ID_OrdenVenta=0;
	$ID_OfertaVenta=0;
	$ID_EntregaVenta=0;
	$ID_SolTras=0;
	$ID_TrasladoInv=0;
	$ID_SalidaInv=0;
	$ID_SalidaInv=0;
	$ID_DevolucionVenta=0;
	$ID_FacturaVenta=0;
	$ID_ListaMaterial=0;
	$ID_SolicitudCompra=0;
	$ID_OrdenCompra=0;
	$ID_EntradaCompra=0;
	$ID_DevolucionCompra=0;
	$ID_FacturaCompra=0;
	$ID_Evento=0;
	$dim1=isset($_GET['dim1']) ? $_GET['dim1'] : "";
	$dim2=isset($_GET['dim2']) ? $_GET['dim2'] : "";
	$dim3=isset($_GET['dim3']) ? $_GET['dim3'] : "";
	
	if(isset($_GET['idordenventa'])&&$_GET['idordenventa']!=""){
		$ID_OrdenVenta=base64_decode($_GET['idordenventa']);
		$ID_Evento=base64_decode($_GET['evento']);
	}
	elseif(isset($_GET['idofertaventa'])&&$_GET['idofertaventa']!=""){
		$ID_OfertaVenta=base64_decode($_GET['idofertaventa']);
		$ID_Evento=base64_decode($_GET['evento']);
	}
	elseif(isset($_GET['identregaventa'])&&$_GET['identregaventa']!=""){
		$ID_EntregaVenta=base64_decode($_GET['identregaventa']);
		$ID_Evento=base64_decode($_GET['evento']);
	}
	elseif(isset($_GET['idsolsalida'])&&$_GET['idsolsalida']!=""){
		$ID_SolTras=base64_decode($_GET['idsolsalida']);
		$ID_Evento=base64_decode($_GET['evento']);
	}
	elseif(isset($_GET['idtrasladoinv'])&&$_GET['idtrasladoinv']!=""){
		$ID_TrasladoInv=base64_decode($_GET['idtrasladoinv']);
		$ID_Evento=base64_decode($_GET['evento']);
	}
	elseif(isset($_GET['idsalidainv'])&&$_GET['idsalidainv']!=""){
		$ID_SalidaInv=base64_decode($_GET['idsalidainv']);
		$ID_Evento=base64_decode($_GET['evento']);
	}
	elseif(isset($_GET['iddevolucionventa'])&&$_GET['iddevolucionventa']!=""){
		$ID_DevolucionVenta=base64_decode($_GET['iddevolucionventa']);
		$ID_Evento=base64_decode($_GET['evento']);
	}
	elseif(isset($_GET['idfacturaventa'])&&$_GET['idfacturaventa']!=""){
		$ID_FacturaVenta=base64_decode($_GET['idfacturaventa']);
		$ID_Evento=base64_decode($_GET['evento']);
	}
	elseif(isset($_GET['idlistamaterial'])&&$_GET['idlistamaterial']!=""){
		$ID_ListaMaterial=base64_decode($_GET['idlistamaterial']);
		$ID_Evento=base64_decode($_GET['evento']);
	}
	elseif(isset($_GET['idsolicitudcompra'])&&$_GET['idsolicitudcompra']!=""){
		$ID_SolicitudCompra=base64_decode($_GET['idsolicitudcompra']);
		$ID_Evento=base64_decode($_GET['evento']);
	}
	elseif(isset($_GET['idordencompra'])&&$_GET['idordencompra']!=""){
		$ID_OrdenCompra=base64_decode($_GET['idordencompra']);
		$ID_Evento=base64_decode($_GET['evento']);
	}
	elseif(isset($_GET['identradacompras'])&&$_GET['identradacompras']!=""){
		$ID_EntradaCompra=base64_decode($_GET['identradacompras']);
		$ID_Evento=base64_decode($_GET['evento']);
	}
	elseif(isset($_GET['iddevolucioncompra'])&&$_GET['iddevolucioncompra']!=""){
		$ID_DevolucionCompra=base64_decode($_GET['iddevolucioncompra']);
		$ID_Evento=base64_decode($_GET['evento']);
	}
	elseif(isset($_GET['idfacturacompra'])&&$_GET['idfacturacompra']!=""){
		$ID_FacturaCompra=base64_decode($_GET['idfacturacompra']);
		$ID_Evento=base64_decode($_GET['evento']);
	}
	$Param=array(
		"'".$Dato."'",
		"'".$Almacen."'",
		"'".$TipoDoc."'",
		"'".$SoloStock."'",
		"'".$TodosArticulos."'"
	);
	$SQL=EjecutarSP('sp_ConsultarArticulos',$Param);
	$Num=sqlsrv_has_rows($SQL);
	/*if($Num==1){
		if($DocType==1){
			
		}
	}*/
	/* if($Num===true){
		echo "<script>alert('Entro');</script>";
	}else{
		echo "<script>alert('NO Entro');</script>";
	} */	
	//$row=sqlsrv_fetch_array($SQL);
	//echo $Consulta;
?>
<!doctype html>
<html>
<head>
<?php include_once("includes/cabecera.php"); ?>
<title>Buscar artículo | <?php echo NOMBRE_PORTAL;?></title>
<style>
	body{
		background-color: #ffffff;
	}
</style>
<script type="text/javascript"> 
function showHint(str,whscode,doctype=<?php echo $_GET['doctype'];?>)
	{
if(doctype==1){//Orden de venta crear
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_orden_venta.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode=<?php echo $CardCode;?>&whscode=<?php echo $Almacen;?>';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=1&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==2){//Orden de venta editar
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_orden_venta.php?id=<?php echo base64_encode($ID_OrdenVenta);?>&evento=<?php echo base64_encode($ID_Evento);?>&type=2';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=2&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&id=<?php echo $ID_OrdenVenta;?>&evento=<?php echo $ID_Evento;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==3){//Oferta de venta crear
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_oferta_venta.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode=<?php echo $CardCode;?>&whscode=<?php echo $Almacen;?>';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=3&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==4){//Oferta de venta editar
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_oferta_venta.php?id=<?php echo base64_encode($ID_OfertaVenta);?>&evento=<?php echo base64_encode($ID_Evento);?>&type=2';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=4&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&id=<?php echo $ID_OfertaVenta;?>&evento=<?php echo $ID_Evento;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==5){//Entrega de venta crear
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_entrega_venta.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode=<?php echo $CardCode;?>&whscode=<?php echo $Almacen;?>';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=5&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==6){//Entrega de venta editar
	  var xhttp;
	  if (str.length == 0) {
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_entrega_venta.php?id=<?php echo base64_encode($ID_EntregaVenta);?>&evento=<?php echo base64_encode($ID_Evento);?>&type=2';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=6&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&id=<?php echo $ID_EntregaVenta;?>&evento=<?php echo $ID_Evento;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==7){//Solicitud de traslado crear
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_solicitud_traslado.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode=<?php echo $CardCode;?>&whscode=<?php echo $Almacen;?>';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=7&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>");
  }
else if(doctype==8){//Solicitud de traslado editar
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_solicitud_traslado.php?id=<?php echo base64_encode($ID_SolTras);?>&evento=<?php echo base64_encode($ID_Evento);?>&type=2';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=8&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&id=<?php echo $ID_SolTras;?>&evento=<?php echo $ID_Evento;?>");
  }
else if(doctype==9){//Salida de inventario crear
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_salida_inventario.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode=<?php echo $CardCode;?>&whscode=<?php echo $Almacen;?>';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=9&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>");
  }
else if(doctype==10){//Salida de inventario editar
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_salida_inventario.php?id=<?php echo base64_encode($ID_SalidaInv);?>&evento=<?php echo base64_encode($ID_Evento);?>&type=2';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=10&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&id=<?php echo $ID_SalidaInv;?>&evento=<?php echo $ID_Evento;?>");
  }
else if(doctype==11){//Traslado de inventario crear
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_traslado_inventario.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode=<?php echo $CardCode;?>&whscode=<?php echo $Almacen;?>&towhscode=<?php echo $AlmacenDestino;?>';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=11&item="+str+"&whscode="+whscode+"&towhscode=<?php echo $AlmacenDestino;?>&cardcode=<?php echo $CardCode;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==12){//Traslado de inventario editar
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_traslado_inventario.php?id=<?php echo base64_encode($ID_TrasladoInv);?>&evento=<?php echo base64_encode($ID_Evento);?>&type=2';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=12&item="+str+"&whscode="+whscode+"&towhscode=<?php echo $AlmacenDestino;?>&cardcode=<?php echo $CardCode;?>&id=<?php echo $ID_TrasladoInv;?>&evento=<?php echo $ID_Evento;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==13){//Devolucion de venta crear
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_devolucion_venta.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode=<?php echo $CardCode;?>&whscode=<?php echo $Almacen;?>';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=13&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==14){//Devolucion de venta editar
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_devolucion_venta.php?id=<?php echo base64_encode($ID_DevolucionVenta);?>&evento=<?php echo base64_encode($ID_Evento);?>&type=2';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=14&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&id=<?php echo $ID_DevolucionVenta;?>&evento=<?php echo $ID_Evento;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==15){//Factura de venta crear
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_factura_venta.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode=<?php echo $CardCode;?>&whscode=<?php echo $Almacen;?>';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=15&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==16){//Factura de venta editar
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_factura_venta.php?id=<?php echo base64_encode($ID_FacturaVenta);?>&evento=<?php echo base64_encode($ID_Evento);?>&type=2';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=16&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&id=<?php echo $ID_FacturaVenta;?>&evento=<?php echo $ID_Evento;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==17){//Lista de materiales
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
		}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_lista_materiales.php?id=<?php echo base64_encode($ID_ListaMaterial);?>&evento=<?php echo base64_encode($ID_Evento);?>';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
	  };
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=17&item="+str+"&whscode="+whscode+"&cardcode=&id=<?php echo $ID_ListaMaterial;?>&evento=<?php echo $ID_Evento;?>&lista_precio=<?php echo isset($_GET['lista_precio']) ? $_GET['lista_precio'] : "";?>&proyecto=<?php echo isset($_GET['proyecto']) ? $_GET['proyecto'] : "";?>&ocrcode=<?php echo isset($_GET['ocrcode']) ? $_GET['ocrcode'] : "";?>&ocrcode2=<?php echo isset($_GET['ocrcode2']) ? $_GET['ocrcode2'] : "";?>&ocrcode3=<?php echo isset($_GET['ocrcode3']) ? $_GET['ocrcode3'] : "";?>");
	}
else if(doctype==18){//Orden de compra crear
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	  }
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_orden_compra.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode=<?php echo $CardCode;?>&whscode=<?php echo $Almacen;?>';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=18&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==19){//Orden de compra editar
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_orden_compra.php?id=<?php echo base64_encode($ID_OrdenCompra);?>&evento=<?php echo base64_encode($ID_Evento);?>&type=2';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=19&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&id=<?php echo $ID_OrdenCompra;?>&evento=<?php echo $ID_Evento;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==20){//Entrada de compra crear
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	  }
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_entrada_compra.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode=<?php echo $CardCode;?>&whscode=<?php echo $Almacen;?>';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=20&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==21){//Entrada de compra editar
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_entrada_compra.php?id=<?php echo base64_encode($ID_EntradaCompra);?>&evento=<?php echo base64_encode($ID_Evento);?>&type=2';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=21&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&id=<?php echo $ID_EntradaCompra;?>&evento=<?php echo $ID_Evento;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==22){//Solicitud de compra crear
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	  }
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_solicitud_compra.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode=<?php echo $CardCode;?>&whscode=<?php echo $Almacen;?>';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=22&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==23){//Solicitud de compra editar
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_solicitud_compra.php?id=<?php echo base64_encode($ID_SolicitudCompra);?>&evento=<?php echo base64_encode($ID_Evento);?>&type=2';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=23&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&id=<?php echo $ID_SolicitudCompra;?>&evento=<?php echo $ID_Evento;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==24){//factura de compra crear
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	  }
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_factura_compra.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode=<?php echo $CardCode;?>&whscode=<?php echo $Almacen;?>';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=24&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==25){//factura de compra editar
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_factura_compra.php?id=<?php echo base64_encode($ID_FacturaCompra);?>&evento=<?php echo base64_encode($ID_Evento);?>&type=2';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=25&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&id=<?php echo $ID_FacturaCompra;?>&evento=<?php echo $ID_Evento;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==26){//Devolucion de compra crear
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	  }
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_devolucion_compra.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode=<?php echo $CardCode;?>&whscode=<?php echo $Almacen;?>';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=26&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
else if(doctype==27){//Devolucion de compra editar
	  var xhttp;
	  if (str.length == 0) { 
		  //document.getElementById("txtHint").innerHTML = "";
		  //alert('Largo 0');
		  return;
	}
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		  if (this.readyState == 4 && this.status == 200) {
			  window.opener.document.getElementById('DataGrid').src='detalle_devolucion_compra.php?id=<?php echo base64_encode($ID_DevolucionCompra);?>&evento=<?php echo base64_encode($ID_Evento);?>&type=2';
			  window.opener.document.getElementById('TotalItems').value=this.responseText;
			  window.opener.document.getElementById('BuscarItem').value="";
			  window.close();
		}
  	};
	  xhttp.open("POST", "registro.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("P=35&doctype=27&item="+str+"&whscode="+whscode+"&cardcode=<?php echo $CardCode;?>&id=<?php echo $ID_DevolucionCompra;?>&evento=<?php echo $ID_Evento;?>&dim1=<?php echo $dim1;?>&dim2=<?php echo $dim2;?>&dim3=<?php echo $dim3;?>");
  }
}
</script>
</head>

<body>
<div class="p-sm col-lg-4">
	<label class="checkbox-inline i-checks"><input name="chkStock" type="checkbox" id="chkStock" value="1" <?php if($SoloStock==1){?>checked="checked"<?php }?>> Mostrar solo los art&iacute;culos con stock</label>
</div>
<div class="ibox-content">
	<?php include("includes/spinner.php"); ?>
<?php 
 $rawdata = array();
   //guardamos en un array multidimensional todos los datos de la consulta
   $i=0;
	if($Num===false){
		echo "No se encontraron registros que coincidieran con la busqueda.";
		//exit();
	}else{
	   while($row = sqlsrv_fetch_array($SQL)){
		   $rawdata[$i] = $row;
		   $i++;
	   }

	   //$close = mysqli_close($conexion);

	   //DIBUJAMOS LA TABLA

	   echo '<table width="100%" class="table table-striped">';
	   $columnas = count($rawdata[0])/2;
	   //echo $columnas;
	   $filas = count($rawdata);
	   //echo "<br>".$filas."<br>";

	   //Añadimos los titulos
	   echo '<thead>';
	   for($i=1;$i<count($rawdata[0]);$i=$i+2){
		  next($rawdata[0]);
		  echo "<th><b>".key($rawdata[0])."</b></th>";
		  next($rawdata[0]);
	   }
	   echo '</thead>';

		//Añadimos los datos
	   echo '<tbody>';
		for($i=0;$i<$filas;$i++){

		  echo "<tr>";
		  for($j=0;$j<$columnas;$j++){
			  if($j==0){
				  echo "<td><a href=\"#\" onClick=\"showHint('".$rawdata[$i][$j]."','".$rawdata[$i][6]."');\">".utf8_encode($rawdata[$i][$j])."</a></td>";
			  }else{
				  if(is_object($rawdata[$i][$j])){
					  echo "<td>".$rawdata[$i][$j]->format('Y-m-d')."</td>";
				  }elseif(is_numeric($rawdata[$i][$j])){
					  echo "<td>".number_format($rawdata[$i][$j],2)."</td>";
				  }else{
					  echo "<td>".utf8_encode($rawdata[$i][$j])."</td>";
				  }
			  }
		  }
		  echo "</tr>";
	   }
	   echo '</tbody>';
	   echo '</table>';
	}?>
</div>
<?php 
	//URL para recargar con stock o sin stock	
	$URL=QuitarParametrosURL('buscar_articulo.php?'.$_SERVER['QUERY_STRING'],array("solostock"));
?>
<script>
	 $(document).ready(function(){
		 $('.i-checks').iCheck({
			 checkboxClass: 'icheckbox_square-green',
             radioClass: 'iradio_square-green',
          });
		 
		 //Check para mostrar u ocultar los articulos con stock
		$('#chkStock').on('ifChecked', function(event){
			$('.ibox-content').toggleClass('sk-loading',true);
			$(location).attr('href','<?php echo $URL;?>&solostock=1');
		});
		$('#chkStock').on('ifUnchecked', function(event){
			$('.ibox-content').toggleClass('sk-loading',true);
			$(location).attr('href','<?php echo $URL;?>&solostock=2');
		});
	});
</script>
</body>
</html>
<?php 
	sqlsrv_close( $conexion );
}?>