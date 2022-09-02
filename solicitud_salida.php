<?php require_once("includes/conexion.php");
PermitirAcceso(1202);
$msg_error="";//Mensaje del error
$IdSolSalida=0;
$IdPortal=0;//Id del portal para las solicitudes que fueron creadas en el portal, para eliminar el registro antes de cargar al editar

if(isset($_GET['id'])&&($_GET['id']!="")){//ID de la Solicitud de salida (DocEntry)
	$IdSolSalida=base64_decode($_GET['id']);
}

if(isset($_GET['id_portal'])&&($_GET['id_portal']!="")){//Id del portal de venta (ID interno)
	$IdPortal=base64_decode($_GET['id_portal']);
}

if(isset($_POST['IdSolSalida'])&&($_POST['IdSolSalida']!="")){//Tambien el Id interno, pero lo envío cuando mando el formulario
	$IdSolSalida=base64_decode($_POST['IdSolSalida']);
	$IdEvento=base64_decode($_POST['IdEvento']);
}

if(isset($_POST['swError'])&&($_POST['swError']!="")){//Para saber si ha ocurrido un error.
	$sw_error=$_POST['swError'];
}else{
	$sw_error=0;
}

if(isset($_REQUEST['tl'])&&($_REQUEST['tl']!="")){//0 Si se está creando. 1 Se se está editando.
	$edit=$_REQUEST['tl'];
}else{
	$edit=0;
}

if(isset($_POST['P'])&&($_POST['P']!="")){//Grabar Solicitud de salida
	//*** Carpeta temporal ***
		$i=0;//Archivos
		$temp=ObtenerVariable("CarpetaTmp");
		$carp_archivos=ObtenerVariable("RutaArchivos");
		$carp_anexos="solicitudsalida";
		$NuevoNombre="";
		$RutaAttachSAP=ObtenerDirAttach();
		$dir=$temp."/".$_SESSION['CodUser']."/";		   
		$route= opendir($dir);
		//$directorio = opendir("."); //ruta actual
		$DocFiles=array();
		while ($archivo = readdir($route)){ //obtenemos un archivo y luego otro sucesivamente
			if(($archivo == ".")||($archivo == "..")) continue;

			if (!is_dir($archivo)){//verificamos si es o no un directorio
				$DocFiles[$i]=$archivo;
				$i++;
				}
		}
		closedir($route);
		$CantFiles=count($DocFiles);
	
	try{
		if($_POST['tl']==1){//Actualizar
			$IdSolSalida=base64_decode($_POST['IdSolSalida']);
			$IdEvento=base64_decode($_POST['IdEvento']);
			$Type=2;
			if(!PermitirFuncion(403)){//Permiso para autorizar Solicitud de salida
				$_POST['Autorizacion']='P';//Si no tengo el permiso, la Solicitud queda pendiente
			}
		}else{//Crear
			$IdSolSalida="NULL";
			$IdEvento="0";
			$Type=1;
		}
		
		if(isset($_POST['AnioEntrega'])&&($_POST['AnioEntrega']!="")){
			$AnioEntrega="'".$_POST['AnioEntrega']."'";
		}else{
			$AnioEntrega="NULL";
		}
		
		if(isset($_POST['EntregaDescont'])&&($_POST['EntregaDescont']!="")){
			$EntregaDescont="'".$_POST['EntregaDescont']."'";
		}else{
			$EntregaDescont="NULL";
		}
		
		if(isset($_POST['ValorCuotaDesc'])&&($_POST['ValorCuotaDesc']!="")){
			$ValorCuotaDesc="'".$_POST['ValorCuotaDesc']."'";
		}else{
			$ValorCuotaDesc="NULL";
		}
		
		$ParametrosCabSolSalida=array(
			$IdSolSalida,
			$IdEvento,
			"NULL",
			"NULL",
			"'".$_POST['Serie']."'",
			"'".$_POST['EstadoDoc']."'",
			"'".FormatoFecha($_POST['DocDate'])."'",
			"'".FormatoFecha($_POST['DocDueDate'])."'",
			"'".FormatoFecha($_POST['TaxDate'])."'",
			"'".$_POST['CardCode']."'",
			"'".$_POST['ContactoCliente']."'",
			"'".$_POST['OrdenServicioCliente']."'",
			"'".$_POST['Referencia']."'",
			"'".$_SESSION['CodigoEmpVentas']."'",
			"'".LSiqmlObs($_POST['Comentarios'])."'",
			"'".str_replace(',','',$_POST['SubTotal'])."'",
			"'".str_replace(',','',$_POST['Descuentos'])."'",
			"NULL",
			"'".str_replace(',','',$_POST['Impuestos'])."'",
			"'".str_replace(',','',$_POST['TotalSolicitud'])."'",
			"'".$_POST['SucursalFacturacion']."'",
			"'".$_POST['DireccionFacturacion']."'",
			"'".$_POST['SucursalDestino']."'",
			"'".$_POST['DireccionDestino']."'",
			"'".$_POST['CondicionPago']."'",
			"'".$_POST['CentroCosto']."'",
			"'".$_POST['UnidadNegocio']."'",
			"'".$_POST['Sucursal']."'",
			"NULL",
			"'N'",
			"'".$_POST['TipoEntrega']."'",
			$AnioEntrega,
			$EntregaDescont,
			$ValorCuotaDesc,
			"'".$_POST['Almacen']."'",
			"'".$_POST['Empleado']."'",
			"'".$_SESSION['CodUser']."'",
			"'".$_SESSION['CodUser']."'",
			"$Type"
		);
		$SQL_CabeceraSolSalida=EjecutarSP('sp_tbl_SolicitudSalida',$ParametrosCabSolSalida,$_POST['P']);
		if($SQL_CabeceraSolSalida){
			if($Type==1){
				$row_CabeceraSolSalida=sqlsrv_fetch_array($SQL_CabeceraSolSalida);
				$IdSolSalida=$row_CabeceraSolSalida[0];
				$IdEvento=$row_CabeceraSolSalida[1];
			}else{
				$IdSolSalida=base64_decode($_POST['IdSolSalida']);//Lo coloco otra vez solo para saber que tiene ese valor
				$IdEvento=base64_decode($_POST['IdEvento']);
			}
			
			try{
				//Mover los anexos a la carpeta de archivos de SAP
				$j=0;
				while($j<$CantFiles){
					//Sacar la extension del archivo
					$FileActual=$DocFiles[$j];
					$exp=explode('.',$FileActual);
					$Ext = end($exp);
					//Sacar el nombre sin la extension
					$OnlyName = substr($DocFiles[$j],0,strlen($DocFiles[$j])-(strlen($Ext)+1));
					//Reemplazar espacios
					$OnlyName=str_replace(" ","_",$OnlyName);
					$Prefijo = substr(uniqid(rand()),0,3);
					$OnlyName=LSiqmlObs($OnlyName)."_".date('Ymd').$Prefijo;
					$NuevoNombre=$OnlyName.".".$Ext;

					$dir_new=$_SESSION['BD']."/".$carp_archivos."/".$carp_anexos."/";
					if(!file_exists($dir_new)){
						mkdir($dir_new,0777, true);
					}
					if(file_exists($dir_new)){
						copy($dir.$DocFiles[$j],$dir_new.$NuevoNombre);
						//move_uploaded_file($_FILES['FileArchivo']['tmp_name'],$dir_new.$NuevoNombre);
						copy($dir_new.$NuevoNombre,$RutaAttachSAP[0].$NuevoNombre);

						//Registrar archivo en la BD
						$ParamInsAnex=array(
							"'1250000001'",
							"'".$IdSolSalida."'",
							"'".$OnlyName."'",
							"'".$Ext."'",
							"1",
							"'".$_SESSION['CodUser']."'",
							"1"
						);
						$SQL_InsAnex=EjecutarSP('sp_tbl_DocumentosSAP_Anexos',$ParamInsAnex,$_POST['P']);
						if(!$SQL_InsAnex){
							$sw_error=1;
							$msg_error="Error al insertar los anexos.";
						}
					}
					$j++;
				}
			}catch (Exception $e) {
				echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
			}
						
			//Enviar datos al WebServices
			try{
				require_once("includes/conect_ws.php");
				$Parametros=array(
					'pIdSolSalida' => $IdSolSalida,
					'pIdEvento' => $IdEvento,
					'pLogin'=>$_SESSION['User']
				);
				$Client->AppPortal_InsertarSolicitudSalida($Parametros);
				//$Client->AppPortal_InsertarSolSalida($Parametros);
				$Respuesta=$Client->__getLastResponse();
				$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);
				$espaciosDeNombres = $Contenido->getNamespaces(true);
				$Nodos = $Contenido->children($espaciosDeNombres['s']);
				$Nodo=	$Nodos->children($espaciosDeNombres['']);
				$Nodo2=	$Nodo->children($espaciosDeNombres['']);

				$Archivo=json_decode($Nodo2,true);
				if($Archivo['ID_Respuesta']=="0"){
					//InsertarLog(1, 0, 'Error al generar el informe');
					//throw new Exception('Error al generar el informe. Error de WebServices');		
					$sw_error=1;
					$msg_error=$Archivo['DE_Respuesta'];
				}else{
					sqlsrv_close($conexion);
					if($_POST['tl']==0){//Creando solicitud
						header('Location:'.base64_decode($_POST['return']).'&a='.base64_encode("OK_SolSalAdd"));
					}else{//Actualizando solicitud
						header('Location:'.base64_decode($_POST['return']).'&a='.base64_encode("OK_SolSalUpd"));					
					}
				}
			}catch (Exception $e) {
				echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
			}
			
		}else{
			$sw_error=1;
			$msg_error="Ha ocurrido un error al crear la Solicitud de salida";
		}
	}catch (Exception $e){
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
	
}

if($edit==1&&$sw_error==0){
	
	$ParametrosLimpiar=array(
		"'".$IdSolSalida."'",
		"'".$IdPortal."'",
		"'".$_SESSION['CodUser']."'"		
	);
	$LimpiarSolSalida=EjecutarSP('sp_EliminarDatosSolicitudSalida',$ParametrosLimpiar);
	
	$SQL_IdEvento=sqlsrv_fetch_array($LimpiarSolSalida);
	$IdEvento=$SQL_IdEvento[0];
	
	//Solicitud de salida
	$Cons="Select * From uvw_tbl_SolicitudSalida Where DocEntry='".$IdSolSalida."' AND IdEvento='".$IdEvento."'";
	$SQL=sqlsrv_query($conexion,$Cons);
	$row=sqlsrv_fetch_array($SQL);
	
	//Clientes
	$SQL_Cliente=Seleccionar('uvw_Sap_tbl_Clientes','*',"CodigoCliente='".$row['CardCode']."'",'NombreCliente');
	
	//Sucursales
	$SQL_SucursalFacturacion=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".$row['CardCode']."' and TipoDireccion='B'",'NombreSucursal');
	
	$SQL_SucursalDestino=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".$row['CardCode']."' and TipoDireccion='S'",'NombreSucursal');
	
	//Contacto cliente
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".$row['CardCode']."'",'NombreContacto');

	//Orden de servicio
	$SQL_OrdenServicioCliente=Seleccionar('uvw_Sap_tbl_LlamadasServicios','*',"ID_CodigoCliente='".$row['CardCode']."' And IdEstadoLlamada='-3'");
	
	//Sucursal
	
	$SQL_Sucursal=SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes','IdSucursal, DeSucursal',"IdSeries='".$row['IdSeries']."'","IdSucursal, DeSucursal");
	
	//Almacenes
	$SQL_Almacen=SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes','WhsCode, WhsName',"IdSeries='".$row['IdSeries']."'","WhsCode, WhsName",'WhsName');
	
	//Anexos
	$SQL_Anexo=Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos','*',"AbsEntry='".$row['IdAnexo']."'");
	
	//Validar si tiene doc de destino, no se pueda editar
	if($row['DocDestinoDocEntry']!=""){
		$EstadoReal="C";
//		$EstadoReal=$row['Cod_Estado'];
		//$row['Cod_Estado']="C";
	}else{
		$EstadoReal=$row['Cod_Estado'];
	}

}

if($sw_error==1){
	
	//Solicitud salida
	$Cons="Select * From uvw_tbl_SolicitudSalida Where ID_SolSalida='".$IdSolSalida."' AND IdEvento='".$IdEvento."'";
	$SQL=sqlsrv_query($conexion,$Cons);
	$row=sqlsrv_fetch_array($SQL);
	
	//Clientes
	$SQL_Cliente=Seleccionar('uvw_Sap_tbl_Clientes','*',"CodigoCliente='".$row['CardCode']."'",'NombreCliente');
	
	//Sucursales
	$SQL_SucursalFacturacion=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".$row['CardCode']."' and TipoDireccion='B'",'NombreSucursal');
	
	$SQL_SucursalDestino=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".$row['CardCode']."' and TipoDireccion='S'",'NombreSucursal');
	
	//Contacto cliente
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".$row['CardCode']."'",'NombreContacto');

	//Orden de servicio
	$SQL_OrdenServicioCliente=Seleccionar('uvw_Sap_tbl_LlamadasServicios','*',"ID_CodigoCliente='".$row['CardCode']."' And IdEstadoLlamada='-3'");
	
	//Sucursal
	$SQL_Sucursal=SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes','IdSucursal, DeSucursal',"IdSeries='".$row['IdSeries']."'","IdSucursal, DeSucursal");
	
	//Almacenes
	$SQL_Almacen=SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes','WhsCode, WhsName',"IdSeries='".$row['IdSeries']."'","WhsCode, WhsName",'WhsName');
	
	//Anexos
	$SQL_Anexo=Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos','*',"AbsEntry='".$row['IdAnexo']."'");

}

//Normas de reparto (Area)
$SQL_ControCosto=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=1');

//Normas de reparto (centros de costos)
$SQL_UnidadNegocio=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=2');

//Condiciones de pago
$SQL_CondicionPago=Seleccionar('uvw_Sap_tbl_CondicionPago','*','','IdCondicionPago');

//Datos de dimensiones del usuario actual
$SQL_DatosEmpleados=Seleccionar('uvw_tbl_Usuarios','CentroCosto1,CentroCosto2',"ID_Usuario='".$_SESSION['CodUser']."'");
$row_DatosEmpleados=sqlsrv_fetch_array($SQL_DatosEmpleados);

//Empleados
$SQL_Empleado=Seleccionar('uvw_Sap_tbl_EmpleadosSN','*','','NombreEmpleado');

//Tipo entrega
$SQL_TipoEntrega=Seleccionar('uvw_Sap_tbl_TipoEntrega','*','','DeTipoEntrega');

//Año entrega
$SQL_AnioEntrega=Seleccionar('uvw_Sap_tbl_TipoEntregaAnio','*','','DeAnioEntrega');

//Estado documento
$SQL_EstadoDoc=Seleccionar('uvw_tbl_EstadoDocSAP','*');

//Estado autorizacion
$SQL_EstadoAuth=Seleccionar('uvw_Sap_tbl_EstadosAuth','*');

//Series de documento
$ParamSerie=array(
	"'".$_SESSION['CodUser']."'",
	"'1250000001'"
);
$SQL_Series=EjecutarSP('sp_ConsultarSeriesDocumentos',$ParamSerie);

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Solicitud de salida | <?php echo NOMBRE_PORTAL;?></title>
<?php 
if(isset($_GET['a'])&&$_GET['a']==base64_encode("OK_SolSalAdd")){
	echo "<script>
		$(document).ready(function() {
			swal({
				title: '¡Listo!',
				text: 'La Solicitud de salida ha sido creada exitosamente.',
				type: 'success'
			});
		});		
		</script>";
}
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Ha ocurrido un error!',
                text: '".str_replace("'","",$msg_error)."',
                type: 'error'
            });
		});		
		</script>";
}
?>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.panel-body{
		padding: 0px !important;
	}
	.tabs-container .panel-body{
		padding: 0px !important;
	}
	.nav-tabs > li > a{
		padding: 14px 20px 14px 25px !important;
	}
</style>
<script>
function BuscarArticulo(dato){
	var almacen= document.getElementById("Almacen").value;
	var cardcode= document.getElementById("CardCode").value;
	var posicion_x; 
	var posicion_y;  
	posicion_x=(screen.width/2)-(1200/2);  
	posicion_y=(screen.height/2)-(500/2);
	if(dato!=""){
		if((cardcode!="")&&(almacen!="")){
			remote=open('buscar_articulo.php?dato='+dato+'&cardcode='+cardcode+'&whscode='+almacen+'&doctype=<?php if($edit==0){echo "7";}else{echo "8";}?>&idsolsalida=<?php if($edit==1){echo base64_encode($row['ID_SolSalida']);}else{echo "0";}?>&evento=<?php if($edit==1){echo base64_encode($row['IdEvento']);}else{echo "0";}?>&tipodoc=3','remote',"width=1200,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
			remote.focus();
		}else{
			swal({
				title: "¡Error!",
				text: "Debe seleccionar un cliente y un almacén",
				type: "error",
				confirmButtonText: "OK"
			});
		}	
	}			
}
function ConsultarDatosCliente(){
	var Cliente=document.getElementById('CardCode');
	if(Cliente.value!=""){
		self.name='opener';
		remote=open('socios_negocios.php?id='+Base64.encode(Cliente.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}
</script>
<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#CardCode").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var frame=document.getElementById('DataGrid');
			var carcode=document.getElementById('CardCode').value;
			var almacen=document.getElementById('Almacen').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=2&id="+carcode,
				success: function(response){
					$('#ContactoCliente').html(response).fadeIn();
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=3&tdir=S&id="+carcode,
				success: function(response){
					$('#SucursalDestino').html(response).fadeIn();
					$('#SucursalDestino').trigger('change');
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=6&id="+carcode,
				success: function(response){
					$('#OrdenServicioCliente').html(response).fadeIn();
					$('#OrdenServicioCliente').val(null).trigger('change');
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=3&tdir=B&id="+carcode,
				success: function(response){
					$('#SucursalFacturacion').html(response).fadeIn();
					$('#SucursalFacturacion').trigger('change');
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=7&id="+carcode,
				success: function(response){
					$('#CondicionPago').html(response).fadeIn();
				}
			});
			<?php if($edit==0){?>
				if(carcode!="" && almacen!=""){
					frame.src="detalle_solicitud_salida.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode="+carcode+"&whscode="+almacen;
				}else{
					frame.src="detalle_solicitud_salida.php";
				}
			<?php }else{?>
				if(carcode!="" && almacen!=""){
					frame.src="detalle_solicitud_salida.php?id=<?php echo base64_encode($row['ID_SolSalida']);?>&evento=<?php echo base64_encode($row['IdEvento']);?>&type=2";
				}else{
					frame.src="detalle_solicitud_salida.php";
				}
			<?php }?>		
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$("#SucursalDestino").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Cliente=document.getElementById('CardCode').value;
			var Sucursal=document.getElementById('SucursalDestino').value;
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:3,CardCode:Cliente,Sucursal:Sucursal},
				dataType:'json',
				success: function(data){
					document.getElementById('DireccionDestino').value=data.Direccion;
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});
		$("#SucursalFacturacion").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Cliente=document.getElementById('CardCode').value;
			var Sucursal=document.getElementById('SucursalFacturacion').value;
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:3,CardCode:Cliente,Sucursal:Sucursal},
				dataType:'json',
				success: function(data){
					document.getElementById('DireccionFacturacion').value=data.Direccion;
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});
		$("#Serie").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Serie=document.getElementById('Serie').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=19&id="+Serie,
				success: function(response){
					$('#Sucursal').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					$('#Sucursal').trigger('change');
				}
			});		
		});
		$("#Sucursal").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Sucursal=document.getElementById('Sucursal').value;
			var Serie=document.getElementById('Serie').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=20&id="+Sucursal+"&serie="+Serie+"&tdoc=1250000001",
				success: function(response){
					$('#Almacen').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					$('#Almacen').trigger('change');
				}
			});		
		});
		$("#Almacen").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var carcode=document.getElementById('CardCode').value;
			var almacen=document.getElementById('Almacen').value;
			var frame=document.getElementById('DataGrid');
			if(carcode!="" && almacen!=""){
				frame.src="detalle_solicitud_salida.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode="+carcode+"&whscode="+almacen;
			}else{
				frame.src="detalle_solicitud_salida.php";
			}	
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$("#TipoEntrega").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var TipoEnt=document.getElementById('TipoEntrega').value;	
			var EntDesc=document.getElementById('EntregaDescont');
			var VlrCuota=document.getElementById('ValorCuotaDesc');
			if(TipoEnt==2||TipoEnt==3||TipoEnt==4){//Periodicas
				document.getElementById('dv_AnioEnt').style.display='block';
				document.getElementById('dv_Descont').style.display='none';
				document.getElementById('dv_VlrCuota').style.display='none';
				VlrCuota.value="";
				$("#ValorCuotaDesc").removeAttr("required");
			}else if(TipoEnt==6){//Descontable
				document.getElementById('dv_AnioEnt').style.display='none';
				document.getElementById('dv_Descont').style.display='block';
				$('#EntregaDescont').trigger('change');
			}else{
				document.getElementById('dv_AnioEnt').style.display='none';
				document.getElementById('dv_Descont').style.display='none';
				document.getElementById('dv_VlrCuota').style.display='none';
				VlrCuota.value="";
				$("#ValorCuotaDesc").removeAttr("required");
			}	
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		
		$("#EntregaDescont").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var EntDesc=document.getElementById('EntregaDescont');
			var VlrCuota=document.getElementById('ValorCuotaDesc');
			if(EntDesc.value=="SI"){
				document.getElementById('dv_VlrCuota').style.display='block';
				$("#ValorCuotaDesc").attr("required","required");
			}else{
				$("#ValorCuotaDesc").removeAttr("required");
				VlrCuota.value="";
				document.getElementById('dv_VlrCuota').style.display='none';
			}
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		
	});
</script>
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include_once("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Solicitud de salida</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Inventario</a>
                        </li>
                        <li class="active">
                            <strong>Solicitud de salida</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
		 <?php if($edit==1){?>
		 <div class="row">
			<div class="col-lg-12">   		
				<div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
					<div class="form-group">
						<div class="col-lg-6">
							<a href="sapdownload.php?id=<?php echo base64_encode('15');?>&type=<?php echo base64_encode('2');?>&DocKey=<?php echo base64_encode($row['DocEntry']);?>&ObType=<?php echo base64_encode('1250000001');?>&IdFrm=<?php echo base64_encode($row['IdSeries']);?>" target="_blank" class="btn btn-outline btn-success"><i class="fa fa-download"></i> Descargar formato</a>
							<a href="#" class="btn btn-info btn-outline" onClick="VerMapaRel('<?php echo base64_encode($row['DocEntry']);?>','<?php echo base64_encode('1250000001');?>');"><i class="fa fa-sitemap"></i> Mapa de relaciones</a>
						</div>
						<div class="col-lg-6">
							<?php if($row['DocDestinoDocEntry']!=""){?>
								<a href="traslado_inventario.php?id=<?php echo base64_encode($row['DocDestinoDocEntry']);?>&id_portal=<?php echo base64_encode($row['DocDestinoIdPortal']);?>&tl=1" target="_blank" class="btn btn-outline btn-success pull-right m-l-sm">Ir a documento destino <i class="fa fa-external-link"></i></a>
							<?php }?>
							<button type="button" onClick="javascript:location.href='actividad.php?dt_DM=1&Cardcode=<?php echo base64_encode($row['CardCode']);?>&Contacto=<?php echo base64_encode($row['CodigoContacto']);?>&Sucursal=<?php echo base64_encode($row['SucursalDestino']);?>&Direccion=<?php echo base64_encode($row['DireccionDestino']);?>&DM_type=<?php echo base64_encode('1250000001');?>&DM=<?php echo base64_encode($row['DocEntry']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('solicitud_salida.php');?>'" class="alkin btn btn-outline btn-primary pull-right"><i class="fa fa-plus-circle"></i> Agregar actividad</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<br>
		<?php }?>
			 <div class="ibox-content">
				 <?php include("includes/spinner.php"); ?>
          <div class="row"> 
           <div class="col-lg-12">
              <form action="solicitud_salida.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="CrearSolicitudSalida">
				<div class="form-group">
					<label class="col-md-8 col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-user"></i> Información de cliente</h3></label>
					<label class="col-md-4 col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-calendar"></i> Fechas y estado de documento</h3></label>
				</div>
				<div class="col-lg-8">
					<div class="form-group">
						<label class="col-lg-1 control-label"><i onClick="ConsultarDatosCliente();" title="Consultar cliente" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Cliente</label>
						<div class="col-lg-9">
							<input name="CardCode" type="hidden" id="CardCode" value="<?php if(($edit==1)||($sw_error==1)){echo $row['CardCode'];}?>">
							
							<input name="CardName" type="text" required="required" class="form-control" id="CardName" placeholder="Digite para buscar..." value="<?php if(($edit==1)||($sw_error==1)){echo $row['NombreCliente'];}?>" <?php if((($edit==1)&&($row['Cod_Estado']=='C'))||($edit==1)){echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Contacto</label>
						<div class="col-lg-5">
							<select name="ContactoCliente" class="form-control" id="ContactoCliente" required <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
									<option value="">Seleccione...</option>
							<?php
								if($edit==1||$sw_error==1){
									while($row_ContactoCliente=sqlsrv_fetch_array($SQL_ContactoCliente)){?>
										<option value="<?php echo $row_ContactoCliente['CodigoContacto'];?>" <?php if((isset($row['CodigoContacto']))&&(strcmp($row_ContactoCliente['CodigoContacto'],$row['CodigoContacto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ContactoCliente['ID_Contacto'];?></option>
						  	<?php 	}
								}?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Sucursal destino</label>
						<div class="col-lg-5">
							<select name="SucursalDestino" class="form-control select2" id="SucursalDestino" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
							  <?php if($edit==0){?><option value="">Seleccione...</option><?php }?>
							  <?php if(($edit==1)||($sw_error==1)){while($row_SucursalDestino=sqlsrv_fetch_array($SQL_SucursalDestino)){?>
									<option value="<?php echo $row_SucursalDestino['NombreSucursal'];?>" <?php if((isset($row['SucursalDestino']))&&(strcmp($row_SucursalDestino['NombreSucursal'],$row['SucursalDestino'])==0)){ echo "selected=\"selected\"";}elseif(isset($_GET['Sucursal'])&&(strcmp($row_SucursalDestino['NombreSucursal'],base64_decode($_GET['Sucursal']))==0)){ echo "selected=\"selected\"";}?>><?php echo $row_SucursalDestino['NombreSucursal'];?></option>
							  <?php }}?>
							</select>
						</div>
						<label class="col-lg-1 control-label">Sucursal facturación</label>
						<div class="col-lg-5">
							<select name="SucursalFacturacion" class="form-control select2" id="SucursalFacturacion" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
							  <option value="">Seleccione...</option>
							  <?php if($edit==1||$sw_error==1){while($row_SucursalFacturacion=sqlsrv_fetch_array($SQL_SucursalFacturacion)){?>
									<option value="<?php echo $row_SucursalFacturacion['NombreSucursal'];?>" <?php if((isset($row['SucursalFacturacion']))&&(strcmp($row_SucursalFacturacion['NombreSucursal'],$row['SucursalFacturacion'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_SucursalFacturacion['NombreSucursal'];?></option>
							  <?php }}?>
							</select>
						</div>						
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Dirección destino</label>
						<div class="col-lg-5">
							<input type="text" class="form-control" name="DireccionDestino" id="DireccionDestino" value="<?php if($edit==1||$sw_error==1){echo $row['DireccionDestino'];}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
						</div>
						<label class="col-lg-1 control-label">Dirección facturación</label>
						<div class="col-lg-5">
							<input type="text" class="form-control" name="DireccionFacturacion" id="DireccionFacturacion" value="<?php if($edit==1||$sw_error==1){echo $row['DireccionFacturacion'];}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
						</div>						
					</div>
					<div class="form-group">
					<label class="col-lg-1 control-label"><?php if(($edit==1)&&($row['ID_LlamadaServicio']!=0)){?><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_LlamadaServicio']);?>&tl=1" target="_blank" title="Consultar Llamada de servicio" class="btn-xs btn-success fa fa-search"></a> <?php }?>Orden servicio</label>
				  	<div class="col-lg-11">
                    	<select name="OrdenServicioCliente" class="form-control select2" id="OrdenServicioCliente" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
                         	<option value="">(Ninguna)</option>
							<?php
								if($edit==1||$sw_error==1){
									while($row_OrdenServicioCliente=sqlsrv_fetch_array($SQL_OrdenServicioCliente)){?>
										<option value="<?php echo $row_OrdenServicioCliente['ID_LlamadaServicio'];?>" <?php if((isset($row['ID_LlamadaServicio']))&&(strcmp($row_OrdenServicioCliente['ID_LlamadaServicio'],$row['ID_LlamadaServicio'])==0)){ echo "selected=\"selected\"";}elseif((isset($_GET['LS']))&&(strcmp($row_OrdenServicioCliente['ID_LlamadaServicio'],base64_decode($_GET['LS']))==0)){ echo "selected=\"selected\"";}?>><?php echo $row_OrdenServicioCliente['DocNum']." - ".$row_OrdenServicioCliente['AsuntoLlamada']." (".$row_OrdenServicioCliente['DeTipoLlamada'].")";?></option>
							  <?php }
								}?>
						</select>
               	  	</div>
				</div>	
				</div>
				<div class="col-lg-4">
					<div class="form-group">
						<label class="col-lg-5">Fecha de contabilización</label>
						<div class="col-lg-7 input-group date">
							 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="DocDate" id="DocDate" type="text" required="required" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['DocDate'];}else{echo date('Y-m-d');}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-5">Fecha de requerida salida</label>
						<div class="col-lg-7 input-group date">
							 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="DocDueDate" id="DocDueDate" type="text" required="required" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['DocDueDate'];}else{echo date('Y-m-d');}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-5">Fecha del documento</label>
						<div class="col-lg-7 input-group date">
							 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="TaxDate" id="TaxDate" type="text" required="required" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['TaxDate'];}else{echo date('Y-m-d');}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-5">Estado</label>
						<div class="col-lg-7">
							<select name="EstadoDoc" class="form-control" id="EstadoDoc" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
							  <?php while($row_EstadoDoc=sqlsrv_fetch_array($SQL_EstadoDoc)){?>
									<option value="<?php echo $row_EstadoDoc['Cod_Estado'];?>" <?php if(($edit==1)&&(isset($row['Cod_Estado']))&&(strcmp($row_EstadoDoc['Cod_Estado'],$row['Cod_Estado'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoDoc['NombreEstado'];?></option>
							  <?php }?>
							</select>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Datos de la Solicitud</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Serie</label>
					<div class="col-lg-3">
                    	<select name="Serie" class="form-control" id="Serie" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
                          <?php while($row_Series=sqlsrv_fetch_array($SQL_Series)){?>
								<option value="<?php echo $row_Series['IdSeries'];?>" <?php if(($edit==1||$sw_error==1)&&(isset($row['IdSeries']))&&(strcmp($row_Series['IdSeries'],$row['IdSeries'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries'];?></option>
						  <?php }?>
						</select>
               	  	</div>
					<label class="col-lg-1 control-label">Número</label>
					<div class="col-lg-3">
                    	<input type="text" name="DocNum" id="DocNum" class="form-control" value="<?php if($edit==1){echo $row['DocNum'];}?>" readonly>
               	  	</div>
					<label class="col-lg-1 control-label">Referencia</label>
					<div class="col-lg-3">
                    	<input type="text" name="Referencia" id="Referencia" class="form-control" value="<?php if($edit==1){echo $row['NumAtCard'];}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
               	  	</div>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Área</label>
					<div class="col-lg-3">
                    	<select name="UnidadNegocio" class="form-control" id="UnidadNegocio" required="required" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
                          <?php while($row_UnidadNegocio=sqlsrv_fetch_array($SQL_UnidadNegocio)){?>
									<option value="<?php echo $row_UnidadNegocio['OcrCode'];?>" <?php if((isset($row['OcrCode2'])&&($row['OcrCode2']!=""))&&(strcmp($row_UnidadNegocio['OcrCode'],$row['OcrCode2'])==0)){echo "selected=\"selected\"";}elseif(($edit==0)&&($row_DatosEmpleados['CentroCosto2']!="")&&(strcmp($row_DatosEmpleados['CentroCosto2'],$row_UnidadNegocio['OcrCode'])==0)){echo "selected=\"selected\"";}?>><?php echo $row_UnidadNegocio['OcrName'];?></option>
							<?php 	}?>
						</select>
               	  	</div>
					<label class="col-lg-1 control-label">Contro de costos</label>
					<div class="col-lg-3">
						<select name="CentroCosto" class="form-control" id="CentroCosto" required="required" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
						  <?php while($row_ControCosto=sqlsrv_fetch_array($SQL_ControCosto)){?>
								<option value="<?php echo $row_ControCosto['OcrCode'];?>" <?php if((isset($row['OcrCode'])&&($row['OcrCode']!=""))&&(strcmp($row_ControCosto['OcrCode'],$row['OcrCode'])==0)){echo "selected=\"selected\"";}elseif(($edit==0)&&($row_DatosEmpleados['CentroCosto1']!="")&&(strcmp($row_DatosEmpleados['CentroCosto1'],$row_ControCosto['OcrCode'])==0)){echo "selected=\"selected\"";}?>><?php echo $row_ControCosto['OcrName'];?></option>
						  <?php }?>
						</select>
					</div>
					<label class="col-lg-1 control-label">Sucursal</label>
					<div class="col-lg-3">
                    	<select name="Sucursal" class="form-control" id="Sucursal" required="required" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
                          <?php if($edit==1){
									while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
									<option value="<?php echo $row_Sucursal['IdSucursal'];?>" <?php if(($edit==1)&&(isset($row['OcrCode3']))&&(strcmp($row_Sucursal['IdSucursal'],$row['OcrCode3'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['DeSucursal'];?></option>
							<?php 	}
								}?>
						</select>
               	  	</div>
				</div>
				<div class="form-group">					
					<label class="col-lg-1 control-label">Almacén</label>
					<div class="col-lg-3">
						<select name="Almacen" class="form-control" id="Almacen" required="required" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
						  <?php if($edit==1){
									while($row_Almacen=sqlsrv_fetch_array($SQL_Almacen)){?>
									<option value="<?php echo $row_Almacen['WhsCode'];?>" <?php if(($edit==1)&&(isset($row['WhsCode']))&&(strcmp($row_Almacen['WhsCode'],$row['WhsCode'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Almacen['WhsName'];?></option>
						  <?php 	}
								}?>
						</select>
					</div>
					<label class="col-lg-1 control-label">Condición de pago</label>
					<div class="col-lg-3">
						<select name="CondicionPago" class="form-control" id="CondicionPago" required="required" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
						  <?php while($row_CondicionPago=sqlsrv_fetch_array($SQL_CondicionPago)){?>
								<option value="<?php echo $row_CondicionPago['IdCondicionPago'];?>" <?php if($edit==1){if(($row['IdCondicionPago']!="")&&(strcmp($row_CondicionPago['IdCondicionPago'],$row['IdCondicionPago'])==0)){ echo "selected=\"selected\"";}}?>><?php echo $row_CondicionPago['NombreCondicion'];?></option>
						  <?php }?>
						</select>
				  	</div>
					<label class="col-lg-1 control-label">Solicitado para</label>
					<div class="col-lg-3">
                    	<select name="Empleado" class="form-control select2" required id="Empleado" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
								<option value="">Seleccione...</option>
                          <?php while($row_Empleado=sqlsrv_fetch_array($SQL_Empleado)){?>
								<option value="<?php echo $row_Empleado['ID_Empleado'];?>" <?php if((isset($row['CodEmpleado']))&&(strcmp($row_Empleado['ID_Empleado'],$row['CodEmpleado'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Empleado['NombreEmpleado'];?></option>
						  <?php }?>
						</select>
               	  	</div>					
				</div>
				<div class="form-group">					
					<label class="col-lg-1 control-label">Tipo entrega</label>
					<div class="col-lg-3">
                    	<select name="TipoEntrega" class="form-control" id="TipoEntrega" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?> required>
								<option value="">Seleccione...</option>
                          <?php while($row_TipoEntrega=sqlsrv_fetch_array($SQL_TipoEntrega)){?>
								<option value="<?php echo $row_TipoEntrega['IdTipoEntrega'];?>" <?php if((isset($row['IdTipoEntrega']))&&(strcmp($row_TipoEntrega['IdTipoEntrega'],$row['IdTipoEntrega'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoEntrega['DeTipoEntrega'];?></option>
						  <?php }?>
						</select>
               	  	</div>
					<div id="dv_AnioEnt" style="display: none;">
						<label class="col-lg-1 control-label">Año entrega</label>
						<div class="col-lg-2">
							<select name="AnioEntrega" class="form-control" id="AnioEntrega" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
							  <?php while($row_AnioEntrega=sqlsrv_fetch_array($SQL_AnioEntrega)){?>
									<option value="<?php echo $row_AnioEntrega['IdAnioEntrega'];?>" <?php if((isset($row['IdAnioEntrega']))&&(strcmp($row_AnioEntrega['IdAnioEntrega'],$row['IdAnioEntrega'])==0)){ echo "selected=\"selected\"";}elseif(date('Y')==$row_AnioEntrega['DeAnioEntrega']){echo "selected=\"selected\"";}?>><?php echo $row_AnioEntrega['DeAnioEntrega'];?></option>
							  <?php }?>
							</select>
						</div>
					</div>
					<div id="dv_Descont" style="display: none;">
						<label class="col-lg-1 control-label">Entrega descontable</label>
						<div class="col-lg-2">
							<select name="EntregaDescont" class="form-control" id="EntregaDescont" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
								<option value="NO" <?php if(($edit==1)&&($row['Descontable']=="NO")){ echo "selected=\"selected\"";}?>>NO</option>
								<option value="SI" <?php if(($edit==1)&&($row['Descontable']=="SI")){ echo "selected=\"selected\"";}?>>SI</option>
							</select>
						</div>
					</div>
					<div id="dv_VlrCuota" style="display: none;">
						<label class="col-lg-1 control-label">Cant cuota</label>
						<div class="col-lg-2">
							<input type="text" class="form-control" name="ValorCuotaDesc" id="ValorCuotaDesc" onKeyPress="return justNumbers(event,this.value);" value="<?php if($edit==1||$sw_error==1){echo $row['ValorCuotaDesc'];}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Contenido de la Solicitud</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Buscar articulo</label>
					<div class="col-lg-4">
                    	<input name="BuscarItem" id="BuscarItem" type="text" class="form-control" placeholder="Escriba para buscar..." onBlur="javascript:BuscarArticulo(this.value);" <?php if((($edit==1)&&($row['Cod_Estado']=='C'))||(!PermitirFuncion(1201))){echo "readonly";}?>>
               	  	</div>
				</div>
				<div class="tabs-container">  
					<ul class="nav nav-tabs">
						<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Contenido</a></li>
						<?php if($edit==1){?><li><a data-toggle="tab" href="#tab-2" onClick="ConsultarTab('2');"><i class="fa fa-calendar"></i> Actividades</a></li><?php }?>
						<li><a data-toggle="tab" href="#tab-3"><i class="fa fa-paperclip"></i> Anexos</a></li>						
						<li><span class="TimeAct"><div id="TimeAct">&nbsp;</div></span></li>
						<span class="TotalItems"><strong>Total Items:</strong>&nbsp;<input type="text" name="TotalItems" id="TotalItems" class="txtLimpio" value="0" size="1" readonly></span>
					</ul>
					<div class="tab-content">
						<div id="tab-1" class="tab-pane active">
							<iframe id="DataGrid" name="DataGrid" style="border: 0;" width="100%" height="300" src="<?php if($edit==0){echo "detalle_solicitud_salida.php";}else{echo "detalle_solicitud_salida.php?id=".base64_encode($row['ID_SolSalida'])."&evento=".base64_encode($row['IdEvento'])."&type=2&status=".base64_encode($EstadoReal);}?>"></iframe>
						</div>
						<?php if($edit==1){?>
						<div id="tab-2" class="tab-pane">
							<div id="dv_actividades" class="panel-body">
							
							</div>
						</div>
						<?php }?>
						 </form>
						<div id="tab-3" class="tab-pane">
							<div class="panel-body">
								<?php if($edit==1){
									if($row['IdAnexo']!=0){?>
										<div class="form-group">
											<div class="col-lg-4">
											 <ul class="folder-list" style="padding: 0">
											<?php while($row_Anexo=sqlsrv_fetch_array($SQL_Anexo)){
													$Icon=IconAttach($row_Anexo['FileExt']);
												 ?>
												<li><a href="attachdownload.php?file=<?php echo base64_encode($row_Anexo['AbsEntry']);?>&line=<?php echo base64_encode($row_Anexo['Line']);?>" target="_blank" class="btn-link btn-xs"><i class="<?php echo $Icon;?>"></i> <?php echo $row_Anexo['NombreArchivo'];?></a></li>
											<?php }?>
											 </ul>
											</div>
										</div>
							<?php }else{ echo "<p>Sin anexos.</p>"; }
								}?>
								<div class="row">
									<form action="upload.php" class="dropzone" id="dropzoneForm" name="dropzoneForm">
										<?php if($sw_error==0){LimpiarDirTemp();}?>
										<div class="fallback">
											<input name="File" id="File" type="file" form="dropzoneForm" />
										</div>
									 </form>
								</div>
							</div>										   
				   		</div>
					</div>					
				</div> 
			   <form id="frm" action="" class="form-horizontal">
				<div class="form-group">&nbsp;</div>				  
				<div class="col-lg-8">
					<div class="form-group">
						<label class="col-lg-2">Empleado de ventas</label>
						<div class="col-lg-5">
							<input type="text" name="EmpleadoVentas" form="CrearSolicitudSalida" class="form-control" id="EmpleadoVentas" value="<?php if($edit==1){echo $row['NombreEmpleado'];}else{echo $_SESSION['NomUser'];} ?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-2">Comentarios</label>
						<div class="col-lg-10">
							<textarea name="Comentarios" form="CrearSolicitudSalida" rows="4" class="form-control" id="Comentarios" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>><?php if($edit==1){echo $row['Comentarios'];}?></textarea>
						</div>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Subtotal</strong></label>
						<div class="col-lg-5">
							<input type="text" name="SubTotal" form="CrearSolicitudSalida" id="SubTotal" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if($edit==1){echo number_format($row['SubTotal'],0);}else{echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Descuentos</strong></label>
						<div class="col-lg-5">
							<input type="text" name="Descuentos" form="CrearSolicitudSalida" id="Descuentos" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if($edit==1){echo number_format($row['DiscSum'],0);}else{echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">IVA</strong></label>
						<div class="col-lg-5">
							<input type="text" name="Impuestos" form="CrearSolicitudSalida" id="Impuestos" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if($edit==1){echo number_format($row['VatSum'],0);}else{echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Total</strong></label>
						<div class="col-lg-5">
							<input type="text" name="TotalSolicitud" form="CrearSolicitudSalida" id="TotalSolicitud" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if($edit==1){echo number_format($row['DocTotal'],0);}else{echo "0.00";}?>" readonly>
						</div>
					</div>
				</div>		
				<div class="form-group">
					<div class="col-lg-9">
						<?php if($edit==0&&PermitirFuncion(1201)){?>
							<button class="btn btn-primary" type="submit" form="CrearSolicitudSalida" id="Crear"><i class="fa fa-check"></i> Crear Solicitud de salida</button>
						<?php }elseif($row['Cod_Estado']=="O"&&PermitirFuncion(1201)){?>
							<button class="btn btn-warning" type="submit" form="CrearSolicitudSalida" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar Solicitud de salida</button>
						<?php }?>
						<?php 
							$EliminaMsg=array("&a=".base64_encode("OK_SolSalAdd"),"&a=".base64_encode("OK_SolSalUpd"));//Eliminar mensajes
							if(isset($_GET['return'])){
								$_GET['return']=str_replace($EliminaMsg,"",base64_decode($_GET['return']));
							}
							if(isset($_GET['return'])){
								$return=base64_decode($_GET['pag'])."?".$_GET['return'];
							}elseif(isset($_POST['return'])){
								$return=base64_decode($_POST['return']);
							}else{
								$return="solicitud_salida.php?";
							}
						?>
						<a href="<?php echo $return;?>" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
					</div>
					<?php if(($edit==1)&&($row['Cod_Estado']!='C')){?>
					<div class="col-lg-3">
						<div class="btn-group pull-right">
                            <button data-toggle="dropdown" class="btn btn-success dropdown-toggle"><i class="fa fa-mail-forward"></i> Copiar a <i class="fa fa-caret-down"></i></button>
                            <ul class="dropdown-menu">
                                <li><a class="alkin dropdown-item" href="traslado_inventario.php?dt_SS=1&Cardcode=<?php echo base64_encode($row['CardCode']);?>&CCosto=<?php echo base64_encode($row['OcrCode2']);?>&Area=<?php echo base64_encode($row['OcrCode']);?>&Sucursal=<?php echo base64_encode($row['SucursalDestino']);?>&Direccion=<?php echo base64_encode($row['DireccionDestino']);?>&Almacen=<?php echo base64_encode($row['WhsCode']);?>&Contacto=<?php echo base64_encode($row['CodigoContacto']);?>&Empleado=<?php echo base64_encode($row['CodEmpleado']);?>&TipoEntrega=<?php echo base64_encode($row['IdTipoEntrega']);?>&AnioEntrega=<?php echo base64_encode($row['IdAnioEntrega']);?>&EntregaDescont=<?php echo base64_encode($row['Descontable']);?>&ValorCuotaDesc=<?php echo base64_encode($row['ValorCuotaDesc']);?>&SS=<?php echo base64_encode($row['ID_SolSalida']);?>&Evento=<?php echo base64_encode($row['IdEvento']);?>">Traslado de salida</a></li>
                            </ul>
                        </div>
					</div>
					<?php }?>
				</div>
				<input type="hidden" form="CrearSolicitudSalida" id="P" name="P" value="50" />
				<input type="hidden" form="CrearSolicitudSalida" id="IdSolSalida" name="IdSolSalida" value="<?php if($edit==1){echo base64_encode($row['ID_SolSalida']);}?>" />
				<input type="hidden" form="CrearSolicitudSalida" id="IdEvento" name="IdEvento" value="<?php if($edit==1){echo base64_encode($IdEvento);}?>" />
				<input type="hidden" form="CrearSolicitudSalida" id="tl" name="tl" value="<?php echo $edit;?>" />
				<input type="hidden" form="CrearSolicitudSalida" id="swError" name="swError" value="<?php echo $sw_error;?>" />
				<input type="hidden" form="CrearSolicitudSalida" id="return" name="return" value="<?php echo base64_encode($return);?>" />
			 </form>
		   </div>
			</div>
          </div>
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>
	 $(document).ready(function(){
		 $("#CrearSolicitudSalida").validate({
			 submitHandler: function(form){
				if(Validar()){
					swal({
						title: "¿Está seguro que desea guardar los datos?",
						type: "info",
						showCancelButton: true,
						closeOnConfirm: true,
						confirmButtonText: "Si, confirmo",
						cancelButtonText: "No"
					},
					function(isConfirm){
						if(isConfirm){
							$('.ibox-content').toggleClass('sk-loading',true);
							form.submit();
						}
					});
				}else{
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			}
		 });
		 
		 $(".alkin").on('click', function(){
				 $('.ibox-content').toggleClass('sk-loading');
			});
		 <?php if((($edit==1)&&($row['Cod_Estado']=='O')||($edit==0))){?>
		 $('#DocDate').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                autoclose: true,
				format: 'yyyy-mm-dd',
			 	todayHighlight: true,
			 	startDate: '<?php echo date('Y-m-d');?>'
            });
		 $('#DocDueDate').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                autoclose: true,
				format: 'yyyy-mm-dd',
			 	todayHighlight: true,
			 	startDate: '<?php echo date('Y-m-d');?>'
            });
		 $('#TaxDate').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                autoclose: true,
				format: 'yyyy-mm-dd',
			 	todayHighlight: true,
			 	startDate: '<?php echo date('Y-m-d');?>'
            });
	 	 <?php }?>
		 //$('.chosen-select').chosen({width: "100%"});
		 $(".select2").select2();
		 $('.i-checks').iCheck({
			 checkboxClass: 'icheckbox_square-green',
             radioClass: 'iradio_square-green',
          });
		 <?php 
		 if($edit==1){?>
		 $('#Serie option:not(:selected)').attr('disabled',true);
		 $('#Sucursal option:not(:selected)').attr('disabled',true);
		 $('#Almacen option:not(:selected)').attr('disabled',true);
		 $('#Empleado option:not(:selected)').attr('disabled',true);
		 
		 $('#TipoEntrega').trigger('change');
	 	 <?php }?>
		 
		 <?php 
		 if(!PermitirFuncion(403)){?>
		 $('#Autorizacion option:not(:selected)').attr('disabled',true);
	 	 <?php }?>
		 
		 var options = {
			  url: function(phrase) {
				  return "ajx_buscar_datos_json.php?type=7&id="+phrase;
			  },
			  getValue: "NombreBuscarCliente",
			  requestDelay: 400,
			  list: {
				  match: {
					  enabled: true
				  },
				  onClickEvent: function() {
					  var value = $("#CardName").getSelectedItemData().CodigoCliente;
					  $("#CardCode").val(value).trigger("change");
				  }
			  }
		 };
		  <?php if($edit==0){?>
		 $("#CardName").easyAutocomplete(options);
	 	 <?php }?>
		<?php if($edit==0){?>
		 $('#Serie').trigger('change');
	 	<?php }?>
	});
</script>
<script>
//Variables de tab
 var tab_2=0;

function ConsultarTab(type){
	if(type==2){//Actividades
		if(tab_2==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "dm_actividades.php?id=<?php if($edit==1){echo base64_encode($row['DocEntry']);}?>&objtype=15",
				success: function(response){
					$('#dv_actividades').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_2=1;
				}
			});
		}
	}
}
</script>
<script>
function Validar(){
	var result=true;
	
	var TotalItems = document.getElementById("TotalItems");
	
	//Validar si fue actualizado por otro usuario
	$.ajax({
		url:"ajx_buscar_datos_json.php",
		data:{type:15,
			  docentry:'<?php if($edit==1){echo base64_encode($row['DocEntry']);}?>',
			  objtype:'1250000001',
			  date:'<?php echo FormatoFecha(date('Y-m-d'),date('H:i:s'));?>'},
		dataType:'json',
		success: function(data){
			if(data.Result!=1){
				result=false;
				swal({
					title: '¡Lo sentimos!',
					text: 'Este documento ya fue actualizado por otro usuario. Debe recargar la página para volver a cargar los datos.',
					type: 'error'
				});
			}
		}
	 });
	
	if(TotalItems.value=="0"){
		result=false;
		swal({
			title: '¡Lo sentimos!',
			text: 'No puede guardar el documento sin contenido. Por favor verifique.',
			type: 'error'
		});
	}
	
	return result;
}
</script>
<script>
 Dropzone.options.dropzoneForm = {
		paramName: "File", // The name that will be used to transfer the file
		maxFilesize: "<?php echo ObtenerVariable("MaxSizeFile");?>", // MB
	 	maxFiles: "<?php echo ObtenerVariable("CantidadArchivos");?>",
		uploadMultiple: true,
		addRemoveLinks: true,
		dictRemoveFile: "Quitar",
	 	acceptedFiles: "<?php echo ObtenerVariable("TiposArchivos");?>",
		dictDefaultMessage: "<strong>Haga clic aqui para cargar anexos</strong><br>Tambien puede arrastrarlos hasta aqui<br><h4><small>(máximo <?php echo ObtenerVariable("CantidadArchivos");?> archivos a la vez)<small></h4>",
		dictFallbackMessage: "Tu navegador no soporta cargue de archivos mediante arrastrar y soltar",
	 	removedfile: function(file) {
		  $.get( "includes/procedimientos.php", {
			type: "3",
		  	nombre: file.name
		  }).done(function( data ) {
		 	var _ref;
		  	return (_ref = file.previewElement) !== null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
		 	});
		 }
	};
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>