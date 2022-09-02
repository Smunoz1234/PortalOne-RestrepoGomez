<?php require_once("includes/conexion.php");
PermitirAcceso(715);
$dt_LS=0;//sw para saber si vienen datos de la llamada de servicio. 0 no vienen. 1 si vienen.
$dt_EC=0;//sw para saber si vienen datos de una Entrada de compra.
$msg_error="";//Mensaje del error
$IdDevolucion=0;
$IdPortal=0;//Id del portal para las entregas que fueron creadas en el portal, para eliminar el registro antes de cargar al editar
$NameFirma="";

if(isset($_GET['id'])&&($_GET['id']!="")){//ID de la Devolucion de compras (DocEntry)
	$IdDevolucion=base64_decode($_GET['id']);
}

if(isset($_GET['id_portal'])&&($_GET['id_portal']!="")){//Id del portal de venta (ID interno)
	$IdPortal=base64_decode($_GET['id_portal']);
}

if(isset($_POST['IdDevolucionCompra'])&&($_POST['IdDevolucionCompra']!="")){//Tambien el Id interno, pero lo envío cuando mando el formulario
	$IdDevolucionCompra=base64_decode($_POST['IdDevolucionCompra']);
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

if(isset($_POST['P'])&&($_POST['P']!="")){//Grabar Devolucion de compras
	//*** Carpeta temporal ***
		$i=0;//Archivos
		$RutaAttachSAP=ObtenerDirAttach();
		$dir=CrearObtenerDirTemp();
		$dir_firma=CrearObtenerDirTempFirma();
		$dir_new=CrearObtenerDirAnx("devolucioncompra");
	
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
			$IdDevolucionCompra=base64_decode($_POST['IdDevolucionCompra']);
			$IdEvento=base64_decode($_POST['IdEvento']);
			$Type=2;
			if(!PermitirFuncion(403)){//Permiso para autorizar Devolucion de compras
				$_POST['Autorizacion']='P';//Si no tengo el permiso, la Devolucion queda pendiente
			}
		}else{//Crear
			$IdDevolucionCompra="NULL";
			$IdEvento="0";
			$Type=1;
		}		
		$ParametrosCabDevolucionCompra=array(
			$IdDevolucionCompra,
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
			"'".$_POST['EmpleadoVentas']."'",
			"'".LSiqmlObs($_POST['Comentarios'])."'",
			"'".str_replace(',','',$_POST['SubTotal'])."'",
			"'".str_replace(',','',$_POST['Descuentos'])."'",
			"NULL",
			"'".str_replace(',','',$_POST['Impuestos'])."'",
			"'".str_replace(',','',$_POST['TotalDevolucion'])."'",
			"'".$_POST['SucursalFacturacion']."'",
			"'".$_POST['DireccionFacturacion']."'",
			"'".$_POST['SucursalDestino']."'",
			"'".$_POST['DireccionDestino']."'",
			"'".$_POST['CondicionPago']."'",
			"'".$_POST['Dim1']."'",
			"'".$_POST['Dim2']."'",
			"'".$_POST['Sucursal']."'",
			"NULL",
			"'".$_POST['Autorizacion']."'",
			"'".$_POST['Almacen']."'",
			"'".$_SESSION['CodUser']."'",
			"'".$_SESSION['CodUser']."'",
			"$Type"
		);
		$SQL_CabeceraDevolucionCompra=EjecutarSP('sp_tbl_DevolucionCompra',$ParametrosCabDevolucionCompra,$_POST['P']);
		if($SQL_CabeceraDevolucionCompra){
			if($Type==1){
				$row_CabeceraDevolucionCompra=sqlsrv_fetch_array($SQL_CabeceraDevolucionCompra);
				$IdDevolucionCompra=$row_CabeceraDevolucionCompra[0];
				$IdEvento=$row_CabeceraDevolucionCompra[1];
			}else{
				$IdDevolucionCompra=base64_decode($_POST['IdDevolucionCompra']);//Lo coloco otra vez solo para saber que tiene ese valor
				$IdEvento=base64_decode($_POST['IdEvento']);
			}
			
			try{
				//Mover los anexos a la carpeta de archivos de SAP
				$j=0;
				while($j<$CantFiles){
					$Archivo=FormatoNombreAnexo($DocFiles[$j]);
					$NuevoNombre=$Archivo[0];
					$OnlyName=$Archivo[1];
					$Ext=$Archivo[2];
					
					if(file_exists($dir_new)){
						copy($dir.$DocFiles[$j],$dir_new.$NuevoNombre);
						//move_uploaded_file($_FILES['FileArchivo']['tmp_name'],$dir_new.$NuevoNombre);
						copy($dir_new.$NuevoNombre,$RutaAttachSAP[0].$NuevoNombre);

						//Registrar archivo en la BD
						$ParamInsAnex=array(
							"'21'",
							"'".$IdDevolucionCompra."'",
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
			
			if($_POST['tl']==0){//Creando			
			
				//Consultar cabecera
				$SQL_Cab=Seleccionar("uvw_tbl_DevolucionCompra",'*',"ID_DevolucionCompra='".$IdDevolucionCompra."' and IdEvento='".$IdEvento."'");
				$row_Cab=sqlsrv_fetch_array($SQL_Cab);

				//Consultar detalle
				$SQL_Det=Seleccionar("uvw_tbl_DevolucionCompraDetalle",'*',"ID_DevolucionCompra='".$IdDevolucionCompra."' and IdEvento='".$IdEvento."'");

				//Consultar anexos
				$SQL_Anx=Seleccionar("uvw_tbl_DocumentosSAP_Anexos",'*',"ID_Documento='".$IdDevolucionCompra."' and TipoDocumento='21' and Metodo=1");

				//Consultar Lotes
				$SQL_Lotes=Seleccionar("uvw_tbl_LotesDocSAP",'*',"DocEntry='".$IdDevolucionCompra."' and IdEvento='".$IdEvento."' and ObjType='21'");

				$Detalle=array();
				$Anexos=array();
				$Lotes=array();
				$Seriales=array();

				//Detalle
				while($row_Det=sqlsrv_fetch_array($SQL_Det)){

					array_push($Detalle,array(
						"base_type" => ($row_Det['BaseType']==="") ? null : intval($row_Det['BaseType']),
						"base_entry" => ($row_Det['BaseEntry']==="") ? null : intval($row_Det['BaseEntry']),
						"base_line" => ($row_Det['BaseLine']==="") ? null : intval($row_Det['BaseLine']),
						"line_num" => intval($row_Det['LineNum']),
						"id_tipo_articulo" => "",
						"tipo_articulo" => 0,
						"id_articulo" => $row_Det['ItemCode'],
						"articulo" => $row_Det['ItemName'],
						"unidad_medida" => $row_Det['UnitMsr'],
						"texto_libre" => $row_Det['FreeTxt'],
						"id_bodega" => $row_Det['WhsCode'],
						"cant_articulo" => intval($row_Det['Quantity']),
						"precio_articulo" => intval($row_Det['Price']),
						"dim1" => $row_Det['OcrCode'],
						"dim2" => $row_Det['OcrCode2'],
						"dim3" => $row_Det['OcrCode3'],
						"dim4" => $row_Det['OcrCode4'],
						"dim5" => $row_Det['OcrCode5'],
						"id_proyecto" => $row_Det['PrjCode'],
						"metodo_linea" => intval($row_Det['Metodo']),
						"maneja_serial" => $row_Det['ManSerNum'],
						"maneja_lote" => $row_Det['ManBtchNum'],
						"CDU_id_servicio" => $row_Det['CDU_IdServicio'],
						"CDU_id_metodo_aplicacion" => $row_Det['CDU_IdMetodoAplicacion'],
						"CDU_id_tipo_plagas" => $row_Det['CDU_IdTipoPlagas'],
						"CDU_areas_controladas" => $row_Det['CDU_AreasControladas'],
						"CDU_cant_litros" => intval($row_Det['CDU_CantLitros']),
						"CDU_dosificacion" => intval($row_Det['CDU_Dosificacion']),
						"CDU_cant_visita" => 0,
						"CDU_codigo_empleado" => "",
						"CDU_nombre_empleado" => "",
						"CDU_texto_libre" => "",
						"CDU_numero_ots" => "",
						"CDU_id_direccion_destino" => "",
						"estado_linea" => $row_Det['LineStatus'],
						"docentry_documento" => intval($row_Det['DocEntry'])
					));
				}

				//Anexos
				$i=0;
				while($row_Anx=sqlsrv_fetch_array($SQL_Anx)){

					array_push($Anexos,array(
						"id_anexo" => $i,
						"tipo_documento" => intval($row_Anx['TipoDocumento']),
						"id_documento" => intval($row_Anx['ID_Documento']),
						"archivo" => $row_Anx['FileName'],
						"ext_archivo" => $row_Anx['FileExt'],
						"metodo" => intval($row_Anx['Metodo']),
						"fecha" => FormatoFechaToSAP($row_Anx['Fecha']->format('Y-m-d')),
						"id_usuario" => intval($row_Anx['ID_Usuario']),
						"comentarios" => ""
					));
					$i++;
				}

				//Lotes
				while($row_Lotes=sqlsrv_fetch_array($SQL_Lotes)){

					array_push($Lotes,array(
						"id_documento" => intval($row_Lotes['DocEntry']),
						"id_linea" => intval($row_Lotes['DocLinea']),
						"id_articulo" => $row_Lotes['ItemCode'],
						"articulo" => $row_Lotes['ItemName'],
						"cantidad" => intval($row_Lotes['Cantidad']),
						"serial_lote" => $row_Lotes['DistNumber'],
						"id_systema_articulo" => 0
					));
				}

				$Cabecera=array(
					"id_documento" => 0,
					"id_tipo_documento" => "21",
					"tipo_documento" => "Devolucion de compras",
					"moneda_documento" => "$",
					"estado" => $row_Cab['Cod_Estado'],
					"id_doc_portal" => "".$row_Cab['ID_DevolucionCompra']."",
					"id_series" => intval($row_Cab['IdSeries']),
					"id_cliente" => $row_Cab['CardCode'],
					"cliente" => $row_Cab['NombreCliente'],
					"id_contacto_cliente" => intval($row_Cab['CodigoContacto']),
					"contacto_cliente" => $row_Cab['NombreContacto'],
					"referencia" => $row_Cab['NumAtCard'],
					"id_condicion_pago" => intval($row_Cab['IdCondicionPago']),
					"id_direccion_facturacion" => $row_Cab['SucursalFacturacion'],
					"id_direccion_destino" => $row_Cab['SucursalDestino'],
					"fecha_contabilizacion" => FormatoFechaToSAP($row_Cab['DocDate']),
					"fecha_vencimiento" => FormatoFechaToSAP($row_Cab['DocDueDate']),
					"fecha_documento" => FormatoFechaToSAP($row_Cab['TaxDate']),
					"comentarios" => $row_Cab['Comentarios'],
					"usuario" => $row_Cab['Usuario'],
					"fecha_creacion" => FormatoFechaToSAP($row_Cab['FechaRegistro']->format('Y-m-d'),$row_Cab['FechaRegistro']->format('H:i:s')),
					"hora_creacion" => FormatoFechaToSAP($row_Cab['FechaRegistro']->format('Y-m-d'),$row_Cab['FechaRegistro']->format('H:i:s')),
					"id_anexo" => 0,
					"docentry_llamada_servicio" => 0,
					"docentry_documento" => 0,
					"id_llamada_servicio" => 0,
					"id_vendedor" => intval($row_Cab['SlpCode']),
					"metodo" => intval($row_Cab['Metodo']),
					"documento_destino" => "18",
					"documento_destino_borrador" => true,
					"documentos_Lineas" => $Detalle,
					"documentos_Anexos" => $Anexos,
					"documentos_Lotes" => $Lotes,
					"documentos_Seriales" => $Seriales
				);
				
			}else{//Actualizando
				
				//Consultar cabecera
				$SQL_Cab=Seleccionar("uvw_tbl_DevolucionCompra",'*',"ID_DevolucionCompra='".$IdDevolucionCompra."' and IdEvento='".$IdEvento."'");
				$row_Cab=sqlsrv_fetch_array($SQL_Cab);

				//Consultar detalle
				$SQL_Det=Seleccionar("uvw_tbl_DevolucionCompraDetalle",'*',"ID_DevolucionCompra='".$IdDevolucionCompra."' and IdEvento='".$IdEvento."'");

				//Consultar anexos
				$SQL_Anx=Seleccionar("uvw_tbl_DocumentosSAP_Anexos",'*',"ID_Documento='".$IdDevolucionCompra."' and TipoDocumento='21' and Metodo=1");

				$Detalle=array();
				$Anexos=array();
				$Lotes=array();
				$Seriales=array();

				//Detalle
				while($row_Det=sqlsrv_fetch_array($SQL_Det)){

					array_push($Detalle,array(
						"base_type" => ($row_Det['BaseType']==="") ? null : intval($row_Det['BaseType']),
						"base_entry" => ($row_Det['BaseEntry']==="") ? null : intval($row_Det['BaseEntry']),
						"base_line" => ($row_Det['BaseLine']==="") ? null : intval($row_Det['BaseLine']),
						"line_num" => intval($row_Det['LineNum']),
						"id_tipo_articulo" => "",
						"tipo_articulo" => 0,
						"id_articulo" => $row_Det['ItemCode'],
						"articulo" => $row_Det['ItemName'],
						"unidad_medida" => $row_Det['UnitMsr'],
						"texto_libre" => $row_Det['FreeTxt'],
						"id_bodega" => $row_Det['WhsCode'],
						"cant_articulo" => intval($row_Det['Quantity']),
						"precio_articulo" => intval($row_Det['Price']),
						"dim1" => $row_Det['OcrCode'],
						"dim2" => $row_Det['OcrCode2'],
						"dim3" => $row_Det['OcrCode3'],
						"dim4" => $row_Det['OcrCode4'],
						"dim5" => $row_Det['OcrCode5'],
						"id_proyecto" => $row_Det['PrjCode'],
						"metodo_linea" => intval($row_Det['Metodo']),
						"maneja_serial" => $row_Det['ManSerNum'],
						"maneja_lote" => $row_Det['ManBtchNum'],
						"CDU_id_servicio" => $row_Det['CDU_IdServicio'],
						"CDU_id_metodo_aplicacion" => $row_Det['CDU_IdMetodoAplicacion'],
						"CDU_id_tipo_plagas" => $row_Det['CDU_IdTipoPlagas'],
						"CDU_areas_controladas" => $row_Det['CDU_AreasControladas'],
						"CDU_cant_litros" => intval($row_Det['CDU_CantLitros']),
						"CDU_dosificacion" => intval($row_Det['CDU_Dosificacion']),						
						"CDU_cant_visita" => 0,
						"CDU_codigo_empleado" => "",
						"CDU_nombre_empleado" => "",
						"CDU_texto_libre" => "",
						"CDU_numero_ots" => "",
						"CDU_id_direccion_destino" => "",
						"estado_linea" => $row_Det['LineStatus'],
						"docentry_documento" => intval($row_Det['DocEntry'])
					));
				}

				//Anexos
				$i=0;
				while($row_Anx=sqlsrv_fetch_array($SQL_Anx)){

					array_push($Anexos,array(
						"id_anexo" => $i,
						"tipo_documento" => intval($row_Anx['TipoDocumento']),
						"id_documento" => intval($row_Anx['ID_Documento']),
						"archivo" => $row_Anx['FileName'],
						"ext_archivo" => $row_Anx['FileExt'],
						"metodo" => intval($row_Anx['Metodo']),
						"fecha" => FormatoFechaToSAP($row_Anx['Fecha']->format('Y-m-d')),
						"id_usuario" => intval($row_Anx['ID_Usuario']),
						"comentarios" => ""
					));
					$i++;
				}

				$Cabecera=array(
					"id_documento" => intval($row_Cab['DocNum']),
					"id_tipo_documento" => "21",
					"tipo_documento" => "Devolucion de compras",
					"moneda_documento" => "$",
					"estado" => $row_Cab['Cod_Estado'],
					"id_doc_portal" => "".$row_Cab['ID_DevolucionCompra']."",
					"id_series" => intval($row_Cab['IdSeries']),
					"id_cliente" => $row_Cab['CardCode'],
					"cliente" => $row_Cab['NombreCliente'],
					"id_contacto_cliente" => intval($row_Cab['CodigoContacto']),
					"contacto_cliente" => $row_Cab['NombreContacto'],
					"referencia" => $row_Cab['NumAtCard'],
					"id_condicion_pago" => intval($row_Cab['IdCondicionPago']),
					"id_direccion_facturacion" => $row_Cab['SucursalFacturacion'],
					"id_direccion_destino" => $row_Cab['SucursalDestino'],
					"fecha_contabilizacion" => FormatoFechaToSAP($row_Cab['DocDate']),
					"fecha_vencimiento" => FormatoFechaToSAP($row_Cab['DocDueDate']),
					"fecha_documento" => FormatoFechaToSAP($row_Cab['TaxDate']),
					"comentarios" => $row_Cab['Comentarios'],
					"usuario" => $row_Cab['Usuario'],
					"usuario_autorizacion" => $row_Cab['Usuario'],
					"fecha_actualizacion" => FormatoFechaToSAP($row_Cab['FechaRegistro']->format('Y-m-d'),$row_Cab['FechaRegistro']->format('H:i:s')),
					"hora_actualizacion" => FormatoFechaToSAP($row_Cab['FechaRegistro']->format('Y-m-d'),$row_Cab['FechaRegistro']->format('H:i:s')),
					"seg_actualizacion" => 0,
					"id_anexo" => intval($row_Cab['IdAnexo']),
					"metodo" => intval($row_Cab['Metodo']),
					"id_llamada_servicio" => 0,
					"docentry_llamada_servicio" => 0,
					"docentry_documento" => intval($row_Cab['DocEntry']),
					"id_vendedor" => intval($row_Cab['SlpCode']),
					"id_bodega_origen" => "",
  					"id_bodega_destino" => "",					
					"documento_destino" => "18",
					"documento_destino_borrador" => true,
					"documento_destino" => "",
					"documento_destino_borrador" => true,
					"CDU_tipo_venta" => "",
					"CDU_id_tipo_entrega" => "",
					"CDU_id_annio_entrega" => "",
					"CDU_entrega_descontable" => "",
					"CDU_valor_cuota_descontable" => 0,
					"CDU_MedioPago" => "",
					"CDU_DocTributario" => "",
					"CDU_TipoOperacion" => "",
					"CDU_numero_ot" => "",
					"CDU_id_creacion_OT_lote" => "",
					"documentos_Lineas" => $Detalle,
					"documentos_Anexos" => $Anexos,
					"documentos_Lotes" => $Lotes,
					"documentos_Seriales" => $Seriales
				);

			}
			
//			$Cabecera_json=json_encode($Cabecera);
//			echo $Cabecera_json;
//			exit();
			
			//Enviar datos al WebServices
			try{	
				if($_POST['tl']==0){//Creando
					$Metodo="DevolucionesCompras";
					$Resultado=EnviarWebServiceSAP($Metodo,$Cabecera,true,true);
				}else{//Editando
					$Metodo="DevolucionesCompras";
					$Resultado=EnviarWebServiceSAP($Metodo,$Cabecera,true,true,"PUT");
				}
				

				if($Resultado->Success==0){
					$sw_error=1;
					$msg_error=$Resultado->Mensaje;
					$Cabecera_json=json_encode($Cabecera);
				}else{
					if($_POST['tl']==0){//Creando Devolucion						
						//Consultar ID creado para cargar el documento
						$SQL_ConsID=Seleccionar('uvw_Sap_tbl_DevolucionesCompras','ID_DevolucionCompra',"IdDocPortal='".$IdDevolucionCompra."'");
						$row_ConsID=sqlsrv_fetch_array($SQL_ConsID);
						sqlsrv_close($conexion);
						header('Location:devolucion_compra.php?id='.base64_encode($row_ConsID['ID_DevolucionCompra']).'&id_portal='.base64_encode($IdDevolucionCompra).'&tl=1&a='.base64_encode("OK_DCompAdd"));
					}else{//Actualizando Entrega
						sqlsrv_close($conexion);
						header('Location:'.base64_decode($_POST['return']).'&a='.base64_encode("OK_DCompUpd"));					
					}
				}
			}catch (Exception $e) {
				echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
			}	
			
			
//			sqlsrv_close($conexion);
//			if($_POST['tl']==0){//Creando Devolucion	
//				header('Location:'.base64_decode($_POST['return']).'&a='.base64_encode("OK_DCompAdd")."&json=".base64_encode(json_encode($Cabecera)));
//			}else{//Actualizando Devolucion
//				header('Location:'.base64_decode($_POST['return']).'&a='.base64_encode("OK_DCompUpd"));					
//			}
			
		}else{
			$sw_error=1;
			$msg_error="Ha ocurrido un error al crear la Devolucion de compras";
		}
	}catch (Exception $e){
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
	
}

if(isset($_GET['dt_LS'])&&($_GET['dt_LS'])==1){//Verificar que viene de una Llamada de servicio (Datos Llamada servicio).
	$dt_LS=1;
	
	//Consultar datos de la LMT
	/*$SQL_LMT=Seleccionar('uvw_Sap_tbl_ArticulosLlamadas','*',"ItemCode='".base64_decode($_GET['ItemCode'])."'");
	$row_LMT=sqlsrv_fetch_array($SQL_LMT);
	
	//Cargar la LMT
	$ParametrosAddLMT=array(
		"'".base64_decode($_GET['ItemCode'])."'",
		"'".$row_LMT['WhsCode']."'",
		"'".base64_decode($_GET['Cardcode'])."'",
		"'".$_SESSION['CodUser']."'"
	);
	$SQL_AddLMT=EjecutarSP('sp_CargarLMT_DevolucionCompraDetalleCarrito',$ParametrosAddLMT);
	
	//Proveedores
	$SQL_Cliente=Seleccionar('uvw_Sap_tbl_Proveedores','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreCliente');
	$row_Cliente=sqlsrv_fetch_array($SQL_Cliente);
	
	//Contacto cliente
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreContacto');
	
	//Sucursal destino
	$SQL_SucursalDestino=Seleccionar('uvw_Sap_tbl_Proveedores_Sucursales','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."' AND NombreSucursal='".base64_decode($_GET['Sucursal'])."'");*/
	
	//Orden de servicio
	$SQL_OrdenServicioCliente=Seleccionar('uvw_Sap_tbl_LlamadasServicios','*',"ID_LlamadaServicio='".base64_decode($_GET['LS'])."'");
}

if(isset($_GET['dt_EC'])&&($_GET['dt_EC'])==1){//Verificar que viene de una Orden de compras
	$dt_EC=1;
	
	//Proveedores
	$SQL_Cliente=Seleccionar('uvw_Sap_tbl_Proveedores','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreCliente');
	$row_Cliente=sqlsrv_fetch_array($SQL_Cliente);
	
	//Sucursal destino
	$SQL_SucursalDestino=Seleccionar('uvw_Sap_tbl_Proveedores_Sucursales','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."' AND TipoDireccion='S'");
	
	//Contacto cliente
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ProveedorContactos','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreContacto');
	
	$ParametrosCopiarEntradaToDevolucion=array(
		"'".base64_decode($_GET['EC'])."'",
		"'".base64_decode($_GET['Evento'])."'",
		"'".base64_decode($_GET['Almacen'])."'",
		"'".base64_decode($_GET['Cardcode'])."'",
		"'".$_SESSION['CodUser']."'"
	);
	$SQL_CopiarEntradaToDevolucion=EjecutarSP('sp_tbl_EntradaCompraDet_To_DevolucionCompraDet',$ParametrosCopiarEntradaToDevolucion);
	if(!$SQL_CopiarEntradaToDevolucion){
		echo "<script>
		$(document).ready(function() {
			swal({
				title: '¡Ha ocurrido un error!',
				text: 'No se pudo copiar la Entrada en Devolucion de compras.',
				type: 'error'
			});
		});		
		</script>";
	}
		
}

if($edit==1&&$sw_error==0){
	
	$ParametrosLimpiar=array(
		"'".$IdDevolucion."'",
		"'".$IdPortal."'",
		"'".$_SESSION['CodUser']."'"	
	);
	$LimpiarDevolucion=EjecutarSP('sp_EliminarDatosDevolucionCompra',$ParametrosLimpiar);
	
	$SQL_IdEvento=sqlsrv_fetch_array($LimpiarDevolucion);
	$IdEvento=$SQL_IdEvento[0];
	
	//Devolucion de compras
	$Cons="Select * From uvw_tbl_DevolucionCompra Where DocEntry='".$IdDevolucion."' AND IdEvento='".$IdEvento."'";
	$SQL=sqlsrv_query($conexion,$Cons);
	$row=sqlsrv_fetch_array($SQL);
	
	//Proveedores
	$SQL_Cliente=Seleccionar('uvw_Sap_tbl_Proveedores','*',"CodigoCliente='".$row['CardCode']."'",'NombreCliente');
	
	//Sucursales
	$SQL_SucursalFacturacion=Seleccionar('uvw_Sap_tbl_Proveedores_Sucursales','*',"CodigoCliente='".$row['CardCode']."' and TipoDireccion='B'",'NombreSucursal');
	
	$SQL_SucursalDestino=Seleccionar('uvw_Sap_tbl_Proveedores_Sucursales','*',"CodigoCliente='".$row['CardCode']."' and TipoDireccion='S'",'NombreSucursal');
	
	//Contacto cliente
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ProveedorContactos','*',"CodigoCliente='".$row['CardCode']."'",'NombreContacto');

	//Orden de servicio
	$SQL_OrdenServicioCliente=Seleccionar('uvw_Sap_tbl_LlamadasServicios','*',"ID_CodigoCliente='".$row['CardCode']."' And IdEstadoLlamada='-3'");
	
	//Sucursal
	$SQL_Sucursal=SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes','IdSucursal, DeSucursal',"IdSeries='".$row['IdSeries']."'","IdSucursal, DeSucursal");
	
	//Almacenes
	$SQL_Almacen=SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes','WhsCode, WhsName',"IdSeries='".$row['IdSeries']."'","WhsCode, WhsName",'WhsName');
	
	//Anexos
	$SQL_Anexo=Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos','*',"AbsEntry='".$row['IdAnexo']."'");

}

if($sw_error==1){
	
	//Devolucion de compras
	$Cons="Select * From uvw_tbl_DevolucionCompra Where ID_DevolucionCompra='".$IdDevolucionCompra."' AND IdEvento='".$IdEvento."'";
	$SQL=sqlsrv_query($conexion,$Cons);
	$row=sqlsrv_fetch_array($SQL);
	
	//Proveedores
	$SQL_Cliente=Seleccionar('uvw_Sap_tbl_Proveedores','*',"CodigoCliente='".$row['CardCode']."'",'NombreCliente');
	
	//Sucursales
	$SQL_SucursalFacturacion=Seleccionar('uvw_Sap_tbl_Proveedores_Sucursales','*',"CodigoCliente='".$row['CardCode']."' and TipoDireccion='B'",'NombreSucursal');
	
	$SQL_SucursalDestino=Seleccionar('uvw_Sap_tbl_Proveedores_Sucursales','*',"CodigoCliente='".$row['CardCode']."' and TipoDireccion='S'",'NombreSucursal');
	
	//Contacto cliente
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ProveedorContactos','*',"CodigoCliente='".$row['CardCode']."'",'NombreContacto');

	//Orden de servicio
	$SQL_OrdenServicioCliente=Seleccionar('uvw_Sap_tbl_LlamadasServicios','*',"ID_CodigoCliente='".$row['CardCode']."' And IdEstadoLlamada='-3'");
	
	//Sucursal
	$SQL_Sucursal=SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes','IdSucursal, DeSucursal',"IdSeries='".$row['IdSeries']."'","IdSucursal, DeSucursal");
	
	//Almacenes
	$SQL_Almacen=SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes','WhsCode, WhsName',"IdSeries='".$row['IdSeries']."'","WhsCode, WhsName",'WhsName');
	
	//Anexos
	$SQL_Anexo=Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos','*',"AbsEntry='".$row['IdAnexo']."'");

}

//Dimensiones de reparto
$SQL_DimReparto=Seleccionar('uvw_Sap_tbl_NombresDimensionesReparto','*',"CodDim IN (1,2,3)","CodDim");

//Normas de reparte (centros de costos)
$SQL_Dim1=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=1');

//Normas de reparte (Unidad negocio)
$SQL_Dim2=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=2');

//Condiciones de pago
$SQL_CondicionPago=Seleccionar('uvw_Sap_tbl_CondicionPago','*','','IdCondicionPago');

//Datos de dimensiones del usuario actual
$SQL_DatosEmpleados=Seleccionar('uvw_tbl_Usuarios','CentroCosto1,CentroCosto2,CentroCosto3',"ID_Usuario='".$_SESSION['CodUser']."'");
$row_DatosEmpleados=sqlsrv_fetch_array($SQL_DatosEmpleados);

//Estado documento
$SQL_EstadoDoc=Seleccionar('uvw_tbl_EstadoDocSAP','*');

//Estado autorizacion
$SQL_EstadoAuth=Seleccionar('uvw_Sap_tbl_EstadosAuth','*');

//Empleado de ventas
$SQL_EmpleadosVentas=Seleccionar('uvw_Sap_tbl_EmpleadosVentas','*','','DE_EmpVentas');

//Series de documento
$ParamSerie=array(
	"'".$_SESSION['CodUser']."'",
	"'21'"
);
$SQL_Series=EjecutarSP('sp_ConsultarSeriesDocumentos',$ParamSerie);

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Devolucion de compras | <?php echo NOMBRE_PORTAL;?></title>
<?php 
if(isset($_GET['a'])&&$_GET['a']==base64_encode("OK_DCompAdd")){
	//$jsonUpd= isset($_GET['json']) ? base64_decode($_GET['json']) : "";
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'La Devolucion de compras ha sido creada exitosamente.',
				icon: 'success'
			});
		});		
		</script>";
}
if(isset($_GET['a'])&&$_GET['a']==base64_encode("OK_DCompUpd")){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'La Devolucion de compras ha sido actualizada exitosamente.',
				icon: 'success'
			});
		});		
		</script>";
}
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Advertencia!',
                text: `".LSiqmlObs($msg_error)."`,
                icon: 'warning'
            });
			console.log('json:','$Cabecera_json');
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
	.ui-datepicker{
		z-index: 9999999 !important;
	}
</style>
<script>
function BuscarArticulo(dato){
	var almacen= document.getElementById("Almacen").value;
	var cardcode= document.getElementById("CardCode").value;
	var dim1= document.getElementById("Dim1").value;
	var dim2= document.getElementById("Dim2").value;
	var dim3= document.getElementById("Sucursal").value;
	var posicion_x; 
	var posicion_y;  
	posicion_x=(screen.width/2)-(1200/2);  
	posicion_y=(screen.height/2)-(500/2);
	if(dato!=""){
		if((cardcode!="")&&(almacen!="")){
			remote=open('buscar_articulo.php?dato='+dato+'&cardcode='+cardcode+'&whscode='+almacen+'&doctype=<?php if($edit==0){echo "26";}else{echo "27";}?>&iddevolucioncompra=<?php if($edit==1){echo base64_encode($row['ID_DevolucionCompra']);}else{echo "0";}?>&evento=<?php if($edit==1){echo base64_encode($row['IdEvento']);}else{echo "0";}?>&tipodoc=1&dim1='+dim1+'&dim2='+dim2+'&dim3='+dim3,'remote',"width=1210,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
			remote.focus();
		}else{
			Swal.fire({
				title: "¡Advertencia!",
				text: "Debe seleccionar un proveedor y un almacén",
				type: "warning",
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
			<?php if($edit==0&&$dt_EC==0){?>
			$.ajax({
				type: "POST",
				url: "includes/procedimientos.php?type=7&objtype=21&cardcode="+carcode
			});
			<?php }?>
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=2&id="+carcode+"&pv=1",
				success: function(response){
					$('#ContactoCliente').html(response).fadeIn();
				}
			});
			<?php if($dt_LS==0&&$dt_EC==0){//Para que no recargue las listas cuando vienen de una llamada de servicio u Orden compra.?>
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=3&tdir=S&id="+carcode+"&pv=1",
				success: function(response){
					$('#SucursalDestino').html(response).fadeIn();
					$('#SucursalDestino').trigger('change');
				}
			});
			<?php }?>
			<?php if($dt_LS==0){//Para que no recargue las listas cuando vienen de una llamada de servicio?>
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=6&id="+carcode,
				success: function(response){
					$('#OrdenServicioCliente').html(response).fadeIn();
					$('#OrdenServicioCliente').val(null).trigger('change');
				}
			});
			<?php }?>
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=3&tdir=B&id="+carcode+"&pv=1",
				success: function(response){
					$('#SucursalFacturacion').html(response).fadeIn();
					$('#SucursalFacturacion').trigger('change');
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=7&id="+carcode+"&pv=1",
				success: function(response){
					$('#CondicionPago').html(response).fadeIn();
				}
			});
			<?php if($edit==0){?>
				if(carcode!=""){
					frame.src="detalle_devolucion_compra.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode="+carcode;
				}else{
					frame.src="detalle_devolucion_compra.php";
				}
			<?php }else{?>
				if(carcode!=""){
					frame.src="detalle_devolucion_compra.php?id=<?php echo base64_encode($row['ID_DevolucionCompra']);?>&evento=<?php echo base64_encode($row['IdEvento']);?>&docentry=<?php echo base64_encode($row['DocEntry']);?>&type=2";
				}else{
					frame.src="detalle_devolucion_compra.php";
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
				data:{type:3,CardCode:Cliente,Sucursal:Sucursal,pv:1},
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
				data:{type:3,CardCode:Cliente,Sucursal:Sucursal,pv:1},
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
			var frame=document.getElementById('DataGrid');
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=20&id="+Sucursal+"&serie="+Serie+"&tdoc=21",
				success: function(response){
					$('#Almacen').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					//$('#Almacen').trigger('change');
				}
			});
			
			if(Sucursal!=""&&document.getElementById('CardCode').value!=""&&document.getElementById('TotalItems').value!="0"){
				Swal.fire({
					title: "¿Desea actualizar las lineas?",
					icon: "question",
					showCancelButton: true,
					confirmButtonText: "Si, confirmo",
					cancelButtonText: "No"
				}).then((result) => {
					if (result.isConfirmed) {
						$('.ibox-content').toggleClass('sk-loading',true);
							<?php if($edit==0){?>
						$.ajax({
							type: "GET",					
							url: "registro.php?P=36&doctype=21&type=1&name=OcrCode3&value="+Base64.encode(Sucursal)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_devolucion_compra.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }else{?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=21&type=2&name=OcrCode3&value="+Base64.encode(Sucursal)+"&line=0&id=<?php echo $row['ID_DevolucionCompra'];?>&evento=<?php echo $IdEvento;?>&actodos=1",
							success: function(response){
								frame.src="detalle_devolucion_compra.php?id=<?php echo base64_encode($row['ID_DevolucionCompra']);?>&evento=<?php echo base64_encode($IdEvento);?>&type=2";
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }?>
					}
				});
			}
		});
		
		$("#Almacen").change(function(){
			var frame=document.getElementById('DataGrid');
			if(document.getElementById('Almacen').value!=""&&document.getElementById('CardCode').value!=""&&document.getElementById('TotalItems').value!="0"){
				Swal.fire({
					title: "¿Desea actualizar las lineas?",
					icon: "question",
					showCancelButton: true,
					confirmButtonText: "Si, confirmo",
					cancelButtonText: "No"
				}).then((result) => {
					if (result.isConfirmed) {
						$('.ibox-content').toggleClass('sk-loading',true);
							<?php if($edit==0){?>
						$.ajax({
							type: "GET",					
							url: "registro.php?P=36&doctype=21&type=1&name=WhsCode&value="+Base64.encode(document.getElementById('Almacen').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_devolucion_compra.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }else{?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=21&type=2&name=WhsCode&value="+Base64.encode(document.getElementById('Almacen').value)+"&line=0&id=<?php echo $row['ID_DevolucionCompra'];?>&evento=<?php echo $IdEvento;?>&actodos=1",
							success: function(response){
								frame.src="detalle_devolucion_compra.php?id=<?php echo base64_encode($row['ID_DevolucionCompra']);?>&evento=<?php echo base64_encode($IdEvento);?>&type=2";
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }?>
					}
				});
			}
			
//			$('.ibox-content').toggleClass('sk-loading',true);
//			var carcode=document.getElementById('CardCode').value;
//			var almacen=document.getElementById('Almacen').value;
//			var frame=document.getElementById('DataGrid');
//			if(carcode!="" && almacen!=""){
//				frame.src="detalle_devolucion_compra.php?id=0&type=1&usr=<?php //echo $_SESSION['CodUser'];?>&cardcode="+carcode+"&whscode="+almacen;
//			}else{
//				frame.src="detalle_devolucion_compra.php";
//			}	
//			$('.ibox-content').toggleClass('sk-loading',false);
		});
		
		$("#Dim1").change(function(){
			var frame=document.getElementById('DataGrid');
			if(document.getElementById('Dim1').value!=""&&document.getElementById('CardCode').value!=""&&document.getElementById('TotalItems').value!="0"){
				Swal.fire({
					title: "¿Desea actualizar las lineas?",
					icon: "question",
					showCancelButton: true,
					confirmButtonText: "Si, confirmo",
					cancelButtonText: "No"
				}).then((result) => {
					if (result.isConfirmed) {
						$('.ibox-content').toggleClass('sk-loading',true);
							<?php if($edit==0){?>
						$.ajax({
							type: "GET",					
							url: "registro.php?P=36&doctype=21&type=1&name=OcrCode&value="+Base64.encode(document.getElementById('Dim1').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_devolucion_compra.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }else{?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=21&type=2&name=OcrCode&value="+Base64.encode(document.getElementById('Dim1').value)+"&line=0&id=<?php echo $row['ID_DevolucionCompra'];?>&evento=<?php echo $IdEvento;?>&actodos=1",
							success: function(response){
								frame.src="detalle_devolucion_compra.php?id=<?php echo base64_encode($row['ID_DevolucionCompra']);?>&evento=<?php echo base64_encode($IdEvento);?>&type=2";
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }?>
					}
				});
			}
		});
		
		$("#Dim2").change(function(){
			var frame=document.getElementById('DataGrid');
			if(document.getElementById('Dim2').value!=""&&document.getElementById('CardCode').value!=""&&document.getElementById('TotalItems').value!="0"){
				Swal.fire({
					title: "¿Desea actualizar las lineas?",
					icon: "question",
					showCancelButton: true,
					confirmButtonText: "Si, confirmo",
					cancelButtonText: "No"
				}).then((result) => {
					if (result.isConfirmed) {
						$('.ibox-content').toggleClass('sk-loading',true);
							<?php if($edit==0){?>
						$.ajax({
							type: "GET",					
							url: "registro.php?P=36&doctype=21&type=1&name=OcrCode2&value="+Base64.encode(document.getElementById('Dim2').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_devolucion_compra.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }else{?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=21&type=2&name=OcrCode2&value="+Base64.encode(document.getElementById('Dim2').value)+"&line=0&id=<?php echo $row['ID_DevolucionCompra'];?>&evento=<?php echo $IdEvento;?>&actodos=1",
							success: function(response){
								frame.src="detalle_devolucion_compra.php?id=<?php echo base64_encode($row['ID_DevolucionCompra']);?>&evento=<?php echo base64_encode($IdEvento);?>&type=2";
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }?>
					}
				});
			}
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
                    <h2>Devolución de compras</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Compras</a>
                        </li>
                        <li class="active">
                            <strong>Devolución de compras</strong>
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
							<a href="sapdownload.php?id=<?php echo base64_encode('15');?>&type=<?php echo base64_encode('2');?>&DocKey=<?php echo base64_encode($row['DocEntry']);?>&ObType=<?php echo base64_encode('21');?>&IdFrm=<?php echo base64_encode($row['IdSeries']);?>" target="_blank" class="btn btn-outline btn-success"><i class="fa fa-download"></i> Descargar formato</a>
							<a href="#" class="btn btn-outline btn-info" onClick="VerMapaRel('<?php echo base64_encode($row['DocEntry']);?>','<?php echo base64_encode('21');?>');"><i class="fa fa-sitemap"></i> Mapa de relaciones</a>
						</div>
						<div class="col-lg-6">
							<?php if($row['DocBaseDocEntry']!=""){?>
								<a href="entrada_compra.php?id=<?php echo base64_encode($row['DocBaseDocEntry']);?>&id_portal=<?php echo base64_encode($row['DocBaseIdPortal']);?>&tl=1" target="_blank" class="btn btn-outline btn-success pull-right m-l-sm"><i class="fa fa-mail-reply"></i> Ir a documento base</i></a>
							<?php }?>
							<button type="button" onClick="javascript:location.href='actividad.php?dt_DM=1&Cardcode=<?php echo base64_encode($row['CardCode']);?>&Contacto=<?php echo base64_encode($row['CodigoContacto']);?>&Sucursal=<?php echo base64_encode($row['SucursalDestino']);?>&Direccion=<?php echo base64_encode($row['DireccionDestino']);?>&DM_type=<?php echo base64_encode('21');?>&DM=<?php echo base64_encode($row['DocEntry']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('devolucion_compra.php');?>'" class="alkin btn btn-outline btn-primary pull-right"><i class="fa fa-plus-circle"></i> Agregar actividad</button>
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
              <form action="devolucion_compra.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="CrearDevolucionCompra">
				  <?php 
				  	$_GET['obj']="21";
				  	include_once('md_frm_campos_adicionales.php');
				  ?>
				<div class="form-group">
					<label class="col-md-8 col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-user"></i> Información de proveedor</h3></label>
					<label class="col-md-4 col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-calendar"></i> Fechas de documento</h3></label>
				</div>
				<div class="col-lg-8">
					<div class="form-group">
						<label class="col-lg-1 control-label"><i onClick="ConsultarDatosCliente();" title="Consultar cliente" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Proveedor <span class="text-danger">*</span></label>
						<div class="col-lg-9">
							<input name="CardCode" type="hidden" id="CardCode" value="<?php if(($edit==1)||($sw_error==1)){echo $row['CardCode'];}elseif($dt_LS==1||$dt_EC==1){echo $row_Cliente['CodigoCliente'];}?>">
							
							<input name="CardName" type="text" required="required" class="form-control" id="CardName" placeholder="Digite para buscar..." value="<?php if(($edit==1)||($sw_error==1)){echo $row['NombreCliente'];}elseif($dt_LS==1||$dt_EC==1){echo $row_Cliente['NombreCliente'];}?>" <?php if((($edit==1)&&($row['Cod_Estado']=='C'))||($dt_LS==1||$dt_EC==1)||($edit==1)){echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Contacto</label>
						<div class="col-lg-5">
							<select name="ContactoCliente" class="form-control" id="ContactoCliente" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
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
							  <?php if(($edit==1)||($sw_error==1)||($dt_LS==1)||($dt_EC==1)){while($row_SucursalDestino=sqlsrv_fetch_array($SQL_SucursalDestino)){?>
									<option value="<?php echo $row_SucursalDestino['NombreSucursal'];?>" <?php if((isset($row['SucursalDestino']))&&(strcmp($row_SucursalDestino['NombreSucursal'],$row['SucursalDestino'])==0)){ echo "selected=\"selected\"";}elseif(isset($_GET['Sucursal'])&&($_GET['Sucursal']!="")&&(strcmp($row_SucursalDestino['NombreSucursal'],base64_decode($_GET['Sucursal']))==0)){ echo "selected=\"selected\"";}?>><?php echo $row_SucursalDestino['NombreSucursal'];?></option>
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
							<input type="text" class="form-control" name="DireccionDestino" id="DireccionDestino" value="<?php if($edit==1||$sw_error==1){echo $row['DireccionDestino'];}elseif($dt_LS==1||$dt_EC==1){echo base64_decode($_GET['Direccion']);}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
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
								if($edit==1||$dt_LS==1||$sw_error==1){
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
						<label class="col-lg-5">Número</label>
						<div class="col-lg-7">
							<input type="text" name="DocNum" id="DocNum" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['DocNum'];}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-5">Fecha de contabilización <span class="text-danger">*</span></label>
						<div class="col-lg-7 input-group date">
							 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="DocDate" type="text" required="required" class="form-control" id="DocDate" value="<?php if($edit==1||$sw_error==1){echo $row['DocDate'];}else{echo date('Y-m-d');}?>" readonly="readonly" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-5">Fecha de entrega/servicio <span class="text-danger">*</span></label>
						<div class="col-lg-7 input-group date">
							 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="DocDueDate" type="text" required="required" class="form-control" id="DocDueDate" value="<?php if($edit==1||$sw_error==1){echo $row['DocDueDate'];}else{echo date('Y-m-d');}?>" readonly="readonly" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-5">Fecha del documento <span class="text-danger">*</span></label>
						<div class="col-lg-7 input-group date">
							 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="TaxDate" type="text" required="required" class="form-control" id="TaxDate" value="<?php if($edit==1||$sw_error==1){echo $row['TaxDate'];}else{echo date('Y-m-d');}?>" readonly="readonly" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-5">Estado <span class="text-danger">*</span></label>
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
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Datos de la Devolucion</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Serie <span class="text-danger">*</span></label>
					<div class="col-lg-3">
                    	<select name="Serie" class="form-control" id="Serie" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
                          <?php while($row_Series=sqlsrv_fetch_array($SQL_Series)){?>
								<option value="<?php echo $row_Series['IdSeries'];?>" <?php if(($edit==1||$sw_error==1)&&(isset($row['IdSeries']))&&(strcmp($row_Series['IdSeries'],$row['IdSeries'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries'];?></option>
						  <?php }?>
						</select>
               	  	</div>
					<label class="col-lg-1 control-label">Referencia</label>
					<div class="col-lg-3">
                    	<input type="text" name="Referencia" id="Referencia" class="form-control" value="<?php if($edit==1){echo $row['NumAtCard'];}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
               	  	</div>
					<label class="col-lg-1 control-label">Condición de pago <span class="text-danger">*</span></label>
					<div class="col-lg-3">
						<select name="CondicionPago" class="form-control" id="CondicionPago" required="required" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
						  <?php while($row_CondicionPago=sqlsrv_fetch_array($SQL_CondicionPago)){?>
								<option value="<?php echo $row_CondicionPago['IdCondicionPago'];?>" <?php if($edit==1||$sw_error==1){if(($row['IdCondicionPago']!="")&&(strcmp($row_CondicionPago['IdCondicionPago'],$row['IdCondicionPago'])==0)){ echo "selected=\"selected\"";}}?>><?php echo $row_CondicionPago['NombreCondicion'];?></option>
						  <?php }?>
						</select>
				  	</div>
				</div>
				<div class="form-group">					
					<?php $row_DimReparto=sqlsrv_fetch_array($SQL_DimReparto);?>
					<label class="col-lg-1 control-label"><?php echo $row_DimReparto['NombreDim']; ?> <span class="text-danger">*</span></label>
					<div class="col-lg-3">
						<select name="Dim1" class="form-control" id="Dim1" required="required" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
						  <?php while($row_Dim1=sqlsrv_fetch_array($SQL_Dim1)){?>
								<option value="<?php echo $row_Dim1['OcrCode'];?>" <?php if((isset($row['OcrCode'])&&($row['OcrCode']!=""))&&(strcmp($row_Dim1['OcrCode'],$row['OcrCode'])==0)){echo "selected=\"selected\"";}elseif(($edit==0)&&(!isset($_GET['Dim1']))&&($row_DatosEmpleados['CentroCosto1']!="")&&(strcmp($row_DatosEmpleados['CentroCosto1'],$row_Dim1['OcrCode'])==0)){echo "selected=\"selected\"";}elseif(isset($_GET['Dim1'])&&(strcmp($row_Dim1['OcrCode'],base64_decode($_GET['Dim1']))==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Dim1['OcrName'];?></option>
						  <?php }?>
						</select>
					</div>
					<?php $row_DimReparto=sqlsrv_fetch_array($SQL_DimReparto);?>
					<label class="col-lg-1 control-label"><?php echo $row_DimReparto['NombreDim']; ?> <span class="text-danger">*</span></label>
					<div class="col-lg-3">
                    	<select name="Dim2" class="form-control" id="Dim2" required="required" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
                          <?php while($row_Dim2=sqlsrv_fetch_array($SQL_Dim2)){?>
									<option value="<?php echo $row_Dim2['OcrCode'];?>" <?php if((isset($row['OcrCode2'])&&($row['OcrCode2']!=""))&&(strcmp($row_Dim2['OcrCode'],$row['OcrCode2'])==0)){echo "selected=\"selected\"";}elseif(($edit==0)&&(!isset($_GET['Dim2']))&&($row_DatosEmpleados['CentroCosto2']!="")&&(strcmp($row_DatosEmpleados['CentroCosto2'],$row_Dim2['OcrCode'])==0)){echo "selected=\"selected\"";}elseif(isset($_GET['Dim2'])&&(strcmp($row_Dim2['OcrCode'],base64_decode($_GET['Dim2']))==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Dim2['OcrName'];?></option>
							<?php 	}?>
						</select>
               	  	</div>
					<?php $row_DimReparto=sqlsrv_fetch_array($SQL_DimReparto);?>
					<label class="col-lg-1 control-label"><?php echo $row_DimReparto['NombreDim']; ?> <span class="text-danger">*</span></label>
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
					<label class="col-lg-1 control-label">Almacén <span class="text-danger">*</span></label>
					<div class="col-lg-3">
						<select name="Almacen" class="form-control" id="Almacen" required="required" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
						  <?php if($edit==1){
									while($row_Almacen=sqlsrv_fetch_array($SQL_Almacen)){?>
									<option value="<?php echo $row_Almacen['WhsCode'];?>" <?php if($dt_LS==1){if(strcmp($row_Almacen['WhsCode'],$row_LMT['WhsCode'])==0){ echo "selected=\"selected\"";}}elseif(($edit==1)&&(isset($row['WhsCode']))&&(strcmp($row_Almacen['WhsCode'],$row['WhsCode'])==0)){ echo "selected=\"selected\"";}elseif(($edit==0)&&(isset($_GET['Almacen']))&&(strcmp($row_Almacen['WhsCode'],base64_decode($_GET['Almacen']))==0)){echo "selected=\"selected\"";}?>><?php echo $row_Almacen['WhsName'];?></option>
						  <?php 	}
								}?>
						</select>
					</div>
					<label class="col-lg-1 control-label">Autorización</label>
					<div class="col-lg-3">
                    	<select name="Autorizacion" class="form-control" id="Autorizacion" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
                          <?php while($row_EstadoAuth=sqlsrv_fetch_array($SQL_EstadoAuth)){?>
								<option value="<?php echo $row_EstadoAuth['IdAuth'];?>" <?php if(($edit==1)&&(isset($row['AuthPortal']))&&(strcmp($row_EstadoAuth['IdAuth'],$row['AuthPortal'])==0)){ echo "selected=\"selected\"";}elseif(($edit==0)&&($row_EstadoAuth['IdAuth']=='N')){echo "selected=\"selected\"";}?>><?php echo $row_EstadoAuth['DeAuth'];?></option>
						  <?php }?>
						</select>
               	  	</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Contenido de la Devolución</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Buscar articulo</label>
					<div class="col-lg-4">
                    	<input name="BuscarItem" id="BuscarItem" type="text" class="form-control" placeholder="Escriba para buscar..." <?php if($edit==0){?>onBlur="javascript:BuscarArticulo(this.value);"<?php }?> <?php if($edit==1){echo "readonly";}?>>
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
							<iframe id="DataGrid" name="DataGrid" style="border: 0;" width="100%" height="300" src="<?php if($edit==0&&$sw_error==0){echo "detalle_devolucion_compra.php";}elseif($edit==0&&$sw_error==1){echo "detalle_devolucion_compra.php?id=0&type=1&usr=".$_SESSION['CodUser']."&cardcode=".$row['CardCode'];}else{echo "detalle_devolucion_compra.php?id=".base64_encode($row['ID_DevolucionCompra'])."&evento=".base64_encode($row['IdEvento'])."&docentry=".base64_encode($row['DocEntry'])."&type=2&status=".base64_encode($row['Cod_Estado']);}?>"></iframe>
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
											<div class="col-xs-12">
												<?php  while($row_Anexo=sqlsrv_fetch_array($SQL_Anexo)){
															$Icon=IconAttach($row_Anexo['FileExt']);
												?>
													<div class="file-box">
														<div class="file">
															<a href="attachdownload.php?file=<?php echo base64_encode($row_Anexo['AbsEntry']);?>&line=<?php echo base64_encode($row_Anexo['Line']);?>" target="_blank">
																<div class="icon">
																	<i class="<?php echo $Icon;?>"></i>
																</div>
																<div class="file-name">
																	<?php echo $row_Anexo['NombreArchivo'];?>
																	<br/>
																	<small><?php echo $row_Anexo['Fecha'];?></small>
																</div>
															</a>
														</div>
													</div>
												<?php }?>
											</div>
										</div>								
							<?php }else{ echo "<p>Sin anexos.</p>"; }
								}?>
								<?php if(($edit==0)||(($edit==1)&&($row['Cod_Estado']=='O'))){?>
								<div class="row">
									<form action="upload.php" class="dropzone" id="dropzoneForm" name="dropzoneForm">
										<?php if($sw_error==0){LimpiarDirTemp();}?>
										<div class="fallback">
											<input name="File" id="File" type="file" form="dropzoneForm" />
										</div>
									 </form>
								</div>
								<?php }?>
							</div>										   
				   		</div>
					</div>					
				</div> 
			   <form id="frm" action="" class="form-horizontal">
				<div class="form-group">&nbsp;</div>				  
				<div class="col-lg-8">
					<div class="form-group">
						<label class="col-lg-2">Encargado de compras <span class="text-danger">*</span></label>
						<div class="col-lg-5">
							<select name="EmpleadoVentas" class="form-control" id="EmpleadoVentas" form="CrearDevolucionCompra" required="required" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
							  <?php while($row_EmpleadosVentas=sqlsrv_fetch_array($SQL_EmpleadosVentas)){?>
									<option value="<?php echo $row_EmpleadosVentas['ID_EmpVentas'];?>" <?php if($edit==0&&$sw_error==0){if(($_SESSION['CodigoEmpVentas']!="")&&(strcmp($row_EmpleadosVentas['ID_EmpVentas'],$_SESSION['CodigoEmpVentas'])==0)){ echo "selected=\"selected\"";}}elseif($edit==1||$sw_error==1){if(($row['SlpCode']!="")&&(strcmp($row_EmpleadosVentas['ID_EmpVentas'],$row['SlpCode'])==0)){ echo "selected=\"selected\"";}}?>><?php echo $row_EmpleadosVentas['DE_EmpVentas'];?></option>
							  <?php }?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-2">Comentarios</label>
						<div class="col-lg-10">
							<textarea name="Comentarios" form="CrearDevolucionCompra" rows="4" class="form-control" id="Comentarios" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>><?php if($edit==1||$sw_error==1){echo $row['Comentarios'];}?></textarea>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-2">Información adicional</label>
						<div class="col-lg-10">
							<button class="btn btn-success" type="button" id="DatoAdicionales" onClick="VerCamposAdi();"><i class="fa fa-list"></i> Ver campos adicionales</button> 
						</div>						
					</div>
				</div>
				<div class="col-lg-4">
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Subtotal</strong></label>
						<div class="col-lg-5">
							<input type="text" name="SubTotal" form="CrearDevolucionCompra" id="SubTotal" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if($edit==1){echo number_format($row['SubTotal'],0);}else{echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Descuentos</strong></label>
						<div class="col-lg-5">
							<input type="text" name="Descuentos" form="CrearDevolucionCompra" id="Descuentos" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if($edit==1){echo number_format($row['DiscSum'],0);}else{echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">IVA</strong></label>
						<div class="col-lg-5">
							<input type="text" name="Impuestos" form="CrearDevolucionCompra" id="Impuestos" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if($edit==1){echo number_format($row['VatSum'],0);}else{echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Total</strong></label>
						<div class="col-lg-5">
							<input type="text" name="TotalDevolucion" form="CrearDevolucionCompra" id="TotalDevolucion" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if($edit==1){echo number_format($row['DocTotal'],0);}else{echo "0.00";}?>" readonly>
						</div>
					</div>
				</div>		
				<div class="form-group">
					<div class="col-lg-9">
						<?php if($edit==0&&PermitirFuncion(714)){?>
							<button class="btn btn-primary" type="submit" form="CrearDevolucionCompra" id="Crear"><i class="fa fa-check"></i> Crear Devolución de compras</button>
						<?php }elseif($row['Cod_Estado']=="O"&&PermitirFuncion(714)){?>
							<button class="btn btn-warning" type="submit" form="CrearDevolucionCompra" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar Devolución de compras</button>
						<?php }?>
						<?php 
							
							if(isset($_GET['return'])){
								$return=base64_decode($_GET['pag'])."?".base64_decode($_GET['return']);
							}elseif(isset($_POST['return'])){
								$return=base64_decode($_POST['return']);
							}else{
								$return="devolucion_compra.php?".$_SERVER['QUERY_STRING'];
							}
							$return=QuitarParametrosURL($return,array("a"));
						?>
						<a href="<?php echo $return;?>" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
					</div>
					<?php if(($edit==1)&&($row['Cod_Estado']!='C')){?>
					<div class="col-lg-3">
						<div class="btn-group pull-right">
                            <button data-toggle="dropdown" class="btn btn-success dropdown-toggle"><i class="fa fa-mail-forward"></i> Copiar a <i class="fa fa-caret-down"></i></button>
                            <ul class="dropdown-menu">
                                <li><a class="alkin dropdown-item" href="factura_compra.php?dt_EC=1&Cardcode=<?php echo base64_encode($row['CardCode']);?>&Dim1=<?php echo base64_encode($row['OcrCode']);?>&Dim2=<?php echo base64_encode($row['OcrCode2']);?>&Sucursal=<?php echo base64_encode($row['SucursalDestino']);?>&Direccion=<?php echo base64_encode($row['DireccionDestino']);?>&Almacen=<?php echo base64_encode($row['WhsCode']);?>&Contacto=<?php echo base64_encode($row['CodigoContacto']);?>&EC=<?php echo base64_encode($row['ID_DevolucionCompra']);?>&Evento=<?php echo base64_encode($row['IdEvento']);?>&dt_LS=1&LS=<?php echo base64_encode($row['ID_LlamadaServicio']);?>">Factura de compra</a></li>
								
								<li><a class="alkin dropdown-item" href="devolucion_compra.php?dt_EC=1&Cardcode=<?php echo base64_encode($row['CardCode']);?>&CCosto=<?php echo base64_encode($row['OcrCode2']);?>&Area=<?php echo base64_encode($row['OcrCode']);?>&Sucursal=<?php echo base64_encode($row['SucursalDestino']);?>&Direccion=<?php echo base64_encode($row['DireccionDestino']);?>&Almacen=<?php echo base64_encode($row['WhsCode']);?>&Contacto=<?php echo base64_encode($row['CodigoContacto']);?>&ET=<?php echo base64_encode($row['ID_DevolucionCompra']);?>&Evento=<?php echo base64_encode($row['IdEvento']);?>&dt_LS=1&LS=<?php echo base64_encode($row['ID_LlamadaServicio']);?>">Devolución de compra</a></li>
                            </ul>
                        </div>
					</div>
					<?php }?>
				</div>
				<input type="hidden" form="CrearDevolucionCompra" id="P" name="P" value="58" />
				<input type="hidden" form="CrearDevolucionCompra" id="IdDevolucionCompra" name="IdDevolucionCompra" value="<?php if($edit==1){echo base64_encode($row['ID_DevolucionCompra']);}?>" />
				<input type="hidden" form="CrearDevolucionCompra" id="IdEvento" name="IdEvento" value="<?php if($edit==1){echo base64_encode($IdEvento);}?>" />
				<input type="hidden" form="CrearDevolucionCompra" id="d_LS" name="d_LS" value="<?php echo $dt_LS;?>" />
				<input type="hidden" form="CrearDevolucionCompra" id="tl" name="tl" value="<?php echo $edit;?>" />
				<input type="hidden" form="CrearDevolucionCompra" id="swError" name="swError" value="<?php echo $sw_error;?>" />
				<input type="hidden" form="CrearDevolucionCompra" id="return" name="return" value="<?php echo base64_encode($return);?>" />
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
		 $("#CrearDevolucionCompra").validate({
			 submitHandler: function(form){
				if(Validar()){
					Swal.fire({
						title: "¿Está seguro que desea guardar los datos?",
						icon: "question",
						showCancelButton: true,
						confirmButtonText: "Si, confirmo",
						cancelButtonText: "No"
					}).then((result) => {
						if (result.isConfirmed) {
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
		 
		 <?php 
		 if($edit==1){?>
		 $('#Serie option:not(:selected)').attr('disabled',true);
		 //$('#Sucursal option:not(:selected)').attr('disabled',true);
		 $('#Almacen option:not(:selected)').attr('disabled',true);
		 $('#Dim1 option:not(:selected)').attr('disabled',true);
		 $('#Dim2 option:not(:selected)').attr('disabled',true);
	 	 <?php }?>
		 
		 <?php 
		 if(!PermitirFuncion(403)){?>
		 $('#Autorizacion option:not(:selected)').attr('disabled',true);
	 	 <?php }?>
		 
		 var options = {
			  url: function(phrase) {
				  return "ajx_buscar_datos_json.php?type=7&id="+phrase+"&pv=1";
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
		<?php if($dt_LS==1||$dt_EC==1){?>
		 $('#CardCode').trigger('change');		 
		 //$('#Almacen').trigger('change');
		<?php }?>
		<?php if($edit==0){?>
		 $('#Serie').trigger('change');
	 	<?php }?>
		 
	});
</script>
<script>
function Validar(){
	var result=true;

	var TotalItems = document.getElementById("TotalItems");

	//Validar si fue actualizado por otro usuario
//	$.ajax({
//		url:"ajx_buscar_datos_json.php",
//		data:{type:15,
//			  docentry:'<?php //if($edit==1){echo base64_encode($row['DocEntry']);}?>',
//			  objtype:21,
//			  date:'<?php //echo FormatoFecha(date('Y-m-d'),date('H:i:s'));?>'},
//		dataType:'json',
//		async: false,
//		success: function(data){
//			if(data.Result==1){
//				result=true;
//			}else{
//				result=false;
//				Swal.fire({
//					title: '¡Lo sentimos!',
//					text: 'Este documento ya fue actualizado por otro usuario. Debe recargar la página para volver a cargar los datos.',
//					icon: 'error'
//				});
//			}
//		}
//	 });

	if(TotalItems.value=="0"){
		result=false;
		Swal.fire({
			title: '¡Advertencia!',
			text: 'No puede guardar el documento sin contenido. Por favor verifique.',
			icon: 'warning'
		});
	}

	<?php if($edit==0){?>
	//Validar que los items con lote ya fueron seleccionados
	var Cliente=document.getElementById('CardCode').value;
	var almacen=document.getElementById('Almacen').value;
	$.ajax({
		url:"ajx_buscar_datos_json.php",
		data:{type:17,
			  cardcode:Cliente,
			  objtype:21,
			  whscode:almacen},
		dataType:'json',
		async: false,
		success: function(data){
			if(data.Result!='1'){
				result=false;
				Swal.fire({
					title: '¡Advertencia!',
					text: 'Algunos articulos faltan por seleccionar lotes. Por favor verifique.',
					icon: 'warning'
				});
			}
		}
	});

//	$.ajax({
//		url:"ajx_buscar_datos_json.php",
//		data:{type:19,
//			  cardcode:Cliente,
//			  objtype:21,
//			  whscode:almacen},
//		dataType:'json',
//		async: false,
//		success: function(data){
//			if(data.Result=='1'){
//				result=true;
//			}else{
//				result=false;
//				Swal.fire({
//					title: '¡Advertencia!',
//					text: 'Algunos articulos faltan por seleccionar seriales. Por favor verifique.',
//					icon: 'warning'
//				});
//			}
//		}
//	});

//	$.ajax({
//		url:"ajx_buscar_datos_json.php",
//		data:{type:27,
//			  cardcode:Cliente,
//			  objtype:21,
//			  whscode:almacen},
//		dataType:'json',
//		async: false,
//		success: function(data){
//			if(data.Estado=='0'){
//				result=false;
//				Swal.fire({
//					title: data.Title,
//					text: data.Mensaje,
//					icon: data.Icon,
//				});	
//			}else{
//				result=true;
//			}
//		}
//	});
	<?php }?>

	return result;
}	
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
				url: "dm_actividades.php?id=<?php if($edit==1){echo base64_encode($row['DocEntry']);}?>&objtype=21",
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