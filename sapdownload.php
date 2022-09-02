<?php 
if((isset($_POST['id'])&&$_POST['id']!="")||(isset($_GET['id'])&&$_GET['id']!="")){
	require_once("includes/conexion.php");
	require_once("includes/conect_ws_rep.php");
	
	if(isset($_POST['id'])&&$_POST['id']!=""){
		$ID=base64_decode($_POST['id']);
		$Type=base64_decode($_POST['type']);
	}else{
		$ID=base64_decode($_GET['id']);
		$Type=base64_decode($_GET['type']);
	}
	
	$NombreArchivo="";
	$size=0;
	$SrvRuta="";
	$ZipMode=0;
	
	if(isset($_REQUEST['zip'])&&($_REQUEST['zip'])==base64_encode('1')){
		$ZipMode=1;
	}
	
	#Ruta local de archivos de SAP	
	$carp_archivos=ObtenerVariable("RutaArchivos");
	$RutaLocal=$_SESSION['BD']."/".$carp_archivos."/InformesSAP/";
	
	#OBTENER RUTA DE DESCARGAR DEL ARCHIVO DEL SERVIDOR
	if(SO=="Linux"){
		
		/******* LINUX *******/
		//Credenciales
		$Dominio=DOMINIO_WIN;
		$User=USER_WIN;
		$Pass=PASS_WIN;
		$Path=PATH_WIN;

		$SrvRuta = "smb://".$Dominio.$User.$Pass.$Path;

	}else{

		/******* WINDOWS *******/				
		$SrvRuta = $RutaLocal;

	}
	
	if($Type==1){//Informes SAP B1
		//Campos
		$SQL_Campos=Seleccionar('uvw_tbl_ParamInfSAP_Campos','*',"ID_Categoria='".$ID."'");
		$Num_Campos=sqlsrv_num_rows($SQL_Campos);

		$SQL_WS=Seleccionar('uvw_tbl_ParamInfSAP_WebServices','*',"ID_Categoria=".$ID);
		$row_WS=sqlsrv_fetch_array($SQL_WS);
	}	
		
	try{
		if($Type==1){//Informes SAP B1
			$Parametros=array();
			if($row_WS['NombreReporte']!=""){
				$Parametros["pNombreReporte"]=$row_WS['NombreReporte'];
			}
			while($row_Campos=sqlsrv_fetch_array($SQL_Campos)){
				if($row_Campos['TipoCampo']=="Usuario"){
					$Parametros[$row_Campos['NombreParam']] = $_SESSION['User'];
				}elseif($row_Campos['TipoCampo']=="Seleccion"){
					if(isset($_POST[$row_Campos['NombreCampo']])&&($_POST[$row_Campos['NombreCampo']]==1)){
						$_POST[$row_Campos['NombreCampo']]="1";
					}else{
						$_POST[$row_Campos['NombreCampo']]="0";
					}
					$Parametros[$row_Campos['NombreParam']] = $_POST[$row_Campos['NombreCampo']];
				}elseif($row_Campos['TipoCampo']=="Cliente"){
					if($_POST[$row_Campos['NombreCampo']]==""){
						$_POST[$row_Campos['NombreCampo']]="Todos";
					}
					$Parametros[$row_Campos['NombreParam']] = $_POST[$row_Campos['NombreCampo']];
				}elseif(($row_Campos['TipoCampo']=="Sucursal") || ($row_Campos['TipoCampo']=="Lista")){
					if($row_Campos['Multiple']==1){//Si es un campo de lista multiple
						if($_POST[$row_Campos['NombreCampo']]==""){
							$_POST[$row_Campos['NombreCampo']]="Todos";
						}
						$Parametros[$row_Campos['NombreParam']] = implode(",",$_POST[$row_Campos['NombreCampo']]);						
					}else{//Si es un campo de lista normal, de un solo valor
						if($_POST[$row_Campos['NombreCampo']]==""){
							$_POST[$row_Campos['NombreCampo']]="Todos";
						}
						$Parametros[$row_Campos['NombreParam']] = $_POST[$row_Campos['NombreCampo']];
					}					
				}else{
					$Parametros[$row_Campos['NombreParam']] = $_POST[$row_Campos['NombreCampo']];
				}			
			}
			
//			print_r($Parametros);
//			exit();
			/*$Parametros2=array(
				'pFechaIni' => $_POST['Fechainicio'],
				'pFechaFin' => $_POST['FechaFin'],
				'pIdCliente' => $_POST['Cliente'],
				'pIdTerritorio' => $_POST['Territorio'],
				'pUsuario' => $_SESSION['User']
			);*/
			$Metodo=$row_WS['NombreWS'];
			
			//$Client->$Metodo($Parametros);
			
			$result = $Client->$Metodo($Parametros);
			
			if(is_soap_fault($result)){
				trigger_error("Fallo IntSAPB1: (Codigo: {$result->faultcode}, Mensaje: {$result->faultstring})", E_USER_ERROR);
			}
			
			$Respuesta=$Client->__getLastResponse();

			$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);

			$espaciosDeNombres = $Contenido->getNamespaces(true);
			$Nodos = $Contenido->children($espaciosDeNombres['s']);
			$Nodo=	$Nodos->children($espaciosDeNombres['']);
			$Nodo2=	$Nodo->children($espaciosDeNombres['']);
			//echo $Nodo2[0];
			try{
				$Archivo=json_decode($Nodo2[0],true);
				//$Archivo=explode("#",$Nodo2[0]);
				if($Archivo['ID_Respuesta']=="0"){
					//InsertarLog(1, 0, 'Error al generar el informe');
					throw new Exception('Error al generar el informe. Error de WebServices');		
				}
			}catch (Exception $e){
				echo 'Excepción capturada: ',  $e->getMessage(), "\n";
				InsertarLog(1, 501, 'Excepción capturada: '.$e->getMessage());//501, cod de SAP Download
			}		
		
		}elseif($Type==2){//Layouts de SAP B1
			if($ID==10){//Factura de servicio - DIALNET
				if($ZipMode==1){//Comprimir los archivos descargados
					
					$Facts=explode("[*]",$_GET['DocKey']);//Lista de los números de las facturas.
					$Files=array();//Donde se almacenan los archivos generados, los cuales se van a comprimir.
					
					$Count=count($Facts);
					$i=0;
					while($i<($Count-1)){
						$Parametros=array(
							'pDockey' => base64_decode($Facts[$i]),
							'pUsuario' => $_SESSION['User']
						);
						//print_r($Parametros);

						$result = $Client->CrearFacturaServicio($Parametros);
						if(is_soap_fault($result)){
							trigger_error("Fallo IntSAPB1: (Codigo: {$result->faultcode}, Mensaje: {$result->faultstring})", E_USER_ERROR);
						}

						$Respuesta=$Client->__getLastResponse();

						$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);

						$espaciosDeNombres = $Contenido->getNamespaces(true);
						$Nodos = $Contenido->children($espaciosDeNombres['s']);
						$Nodo=	$Nodos->children($espaciosDeNombres['']);
						$Nodo2=	$Nodo->children($espaciosDeNombres['']);
						//echo $Nodo2[0];
						try{
							$Archivo=json_decode($Nodo2[0],true);
							//$Archivo=explode("#",$Nodo2[0]);
							if($Archivo['ID_Respuesta']=="0"){
								//InsertarLog(1, 0, 'Error al generar el informe');
								throw new Exception('Error al generar el informe. Error de WebServices');		
							}
						}catch (Exception $e){
							echo 'Excepción capturada: ',  $e->getMessage(), "\n";
							InsertarLog(1, 501, 'Excepción capturada: '.$e->getMessage());//501, cod de SAP Download
						}
						$Files[$i]=$Archivo['DE_Respuesta'];
						
						if(SO=="Linux"){//Copiar los archivos a la ruta local para comprimirlos localmente
							if(!copy($SrvRuta.$Archivo['DE_Respuesta'],$RutaLocal.$Archivo['DE_Respuesta'])){
								exit('No se pudo copiar el archivo '.$Archivo['DE_Respuesta']);			
							}
						}
						$i++;
					}
					
					//Crear archivo ZIP e insertar los archivos
					$zip = new ZipArchive();
					$zipName=date('YmdHi')."_".$_SESSION['CodUser'].".zip";
					$filezip = $RutaLocal.$zipName;
					//echo $filezip;
					//exit();

					if($zip->open($filezip, ZIPARCHIVE::CREATE)===TRUE){
						$Count=count($Files);
						$i=0;
						//$zip->close();
						while($i<$Count){
							$zip->addFile($RutaLocal.$Files[$i],$Files[$i]);
							//echo "Se agregó: ".$SrvRuta.$Files[$i]."\n";
							$i++;
						}
						//exit();
						$zip->close();
						$Archivo['DE_Respuesta']=$zipName;
					}else{
						exit("No se puede abrir el archivo $filezip\n");
					}				
					
				}else{//Descargar el archivo individualmente
					$Parametros=array(
						'pDockey' => base64_decode($_GET['DocKey']),
						'pUsuario' => $_SESSION['User']
					);

					$result = $Client->CrearFacturaServicio($Parametros);
					if(is_soap_fault($result)){
						trigger_error("Fallo IntSAPB1: (Codigo: {$result->faultcode}, Mensaje: {$result->faultstring})", E_USER_ERROR);
					}

					$Respuesta=$Client->__getLastResponse();

					$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);

					$espaciosDeNombres = $Contenido->getNamespaces(true);
					$Nodos = $Contenido->children($espaciosDeNombres['s']);
					$Nodo=	$Nodos->children($espaciosDeNombres['']);
					$Nodo2=	$Nodo->children($espaciosDeNombres['']);
					//echo $Nodo2[0];
					try{
						$Archivo=json_decode($Nodo2[0],true);
						//$Archivo=explode("#",$Nodo2[0]);
						if($Archivo['ID_Respuesta']=="0"){
							//InsertarLog(1, 0, 'Error al generar el informe');
							throw new Exception('Error al generar el informe. Error de WebServices');		
						}
					}catch (Exception $e){
						echo 'Excepción capturada: ',  $e->getMessage(), "\n";
						InsertarLog(1, 501, 'Excepción capturada: '.$e->getMessage());//501, cod de SAP Download
					}
					
				}
				
			}
			if($ID==11){//Formato de convenio de pago - DIALNET
				$Parametros=array(
					'pDockey' => base64_decode($_GET['DocKey']),
					'pUsuario' => $_SESSION['User']
				);

				$result = $Client->CrearAcuerdoPago($Parametros);
				if(is_soap_fault($result)){
					trigger_error("Fallo IntSAPB1: (Codigo: {$result->faultcode}, Mensaje: {$result->faultstring})", E_USER_ERROR);
				}

				$Respuesta=$Client->__getLastResponse();

				$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);

				$espaciosDeNombres = $Contenido->getNamespaces(true);
				$Nodos = $Contenido->children($espaciosDeNombres['s']);
				$Nodo=	$Nodos->children($espaciosDeNombres['']);
				$Nodo2=	$Nodo->children($espaciosDeNombres['']);
				//echo $Nodo2[0];
				try{
					$Archivo=json_decode($Nodo2[0],true);
					//$Archivo=explode("#",$Nodo2[0]);
					if($Archivo['ID_Respuesta']=="0"){
						//InsertarLog(1, 0, 'Error al generar el informe');
						throw new Exception('Error al generar el informe. Error de WebServices');		
					}
				}catch (Exception $e){
					echo 'Excepción capturada: ',  $e->getMessage(), "\n";
					InsertarLog(1, 501, 'Excepción capturada: '.$e->getMessage());//501, cod de SAP Download
				}				
				
			}
			if($ID==12){//Formato de liquidación de intereses - DIALNET
				$Parametros=array(
					'pDockey' => base64_decode($_GET['DocKey']),
					'pUsuario' => $_SESSION['User']
				);

				$result = $Client->CrearLiquidaIntereses($Parametros);
				if(is_soap_fault($result)){
					trigger_error("Fallo IntSAPB1: (Codigo: {$result->faultcode}, Mensaje: {$result->faultstring})", E_USER_ERROR);
				}

				$Respuesta=$Client->__getLastResponse();

				$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);

				$espaciosDeNombres = $Contenido->getNamespaces(true);
				$Nodos = $Contenido->children($espaciosDeNombres['s']);
				$Nodo=	$Nodos->children($espaciosDeNombres['']);
				$Nodo2=	$Nodo->children($espaciosDeNombres['']);
				//echo $Nodo2[0];
				try{
					$Archivo=json_decode($Nodo2[0],true);
					//$Archivo=explode("#",$Nodo2[0]);
					if($Archivo['ID_Respuesta']=="0"){
						//InsertarLog(1, 0, 'Error al generar el informe');
						throw new Exception('Error al generar el informe. Error de WebServices');		
					}
				}catch (Exception $e){
					echo 'Excepción capturada: ',  $e->getMessage(), "\n";
					InsertarLog(1, 501, 'Excepción capturada: '.$e->getMessage());//501, cod de SAP Download
				}				
				
			}
			if($ID==13){//Formato de panorama de riesgo - COPLA
				$Parametros=array(
					'pDocKey' => base64_decode($_GET['DocKey']),
					'pUsuario' => $_SESSION['User']
				);

				$result = $Client->CrearPanoramaRiesgo($Parametros);
				if(is_soap_fault($result)){
					trigger_error("Fallo IntSAPB1: (Codigo: {$result->faultcode}, Mensaje: {$result->faultstring})", E_USER_ERROR);
				}

				$Respuesta=$Client->__getLastResponse();

				$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);

				$espaciosDeNombres = $Contenido->getNamespaces(true);
				$Nodos = $Contenido->children($espaciosDeNombres['s']);
				$Nodo=	$Nodos->children($espaciosDeNombres['']);
				$Nodo2=	$Nodo->children($espaciosDeNombres['']);
				//echo $Nodo2[0];
				try{
					$Archivo=json_decode($Nodo2[0],true);
					//$Archivo=explode("#",$Nodo2[0]);
					if($Archivo['ID_Respuesta']=="0"){
						//InsertarLog(1, 0, 'Error al generar el informe');
						throw new Exception('Error al generar el informe. Error de WebServices');		
					}
				}catch (Exception $e){
					echo 'Excepción capturada: ',  $e->getMessage(), "\n";
					InsertarLog(1, 501, 'Excepción capturada: '.$e->getMessage());//501, cod de SAP Download
				}				
				
			}
			if($ID==14){//Formato de OT - COPLA
				$Parametros=array(
					'pDocKey' => base64_decode($_GET['DocKey']),
					'pUsuario' => $_SESSION['User']
				);

				$result = $Client->CrearFormatoOT($Parametros);
				if(is_soap_fault($result)){
					trigger_error("Fallo IntSAPB1: (Codigo: {$result->faultcode}, Mensaje: {$result->faultstring})", E_USER_ERROR);
				}

				$Respuesta=$Client->__getLastResponse();

				$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);

				$espaciosDeNombres = $Contenido->getNamespaces(true);
				$Nodos = $Contenido->children($espaciosDeNombres['s']);
				$Nodo=	$Nodos->children($espaciosDeNombres['']);
				$Nodo2=	$Nodo->children($espaciosDeNombres['']);
				//echo $Nodo2[0];
				try{
					$Archivo=json_decode($Nodo2[0],true);
					//$Archivo=explode("#",$Nodo2[0]);
					if($Archivo['ID_Respuesta']=="0"){
						//InsertarLog(1, 0, 'Error al generar el informe');
						throw new Exception('Error al generar el informe. Error de WebServices');		
					}
				}catch (Exception $e){
					echo 'Excepción capturada: ',  $e->getMessage(), "\n";
					InsertarLog(1, 501, 'Excepción capturada: '.$e->getMessage());//501, cod de SAP Download
				}				
				
			}
			if($ID==15){//Formatos SAP B1
				if($ZipMode==1){//Comprimir los archivos descargados
					
//					$Facts=explode("[*]",$_GET['DocKey']);//Lista de los números de las facturas.
//					$IdSeries=explode("[*]",$_GET['IdFrm']);//Lista de las series de las facturas
//					$Files=array();//Donde se almacenan los archivos generados, los cuales se van a comprimir.
					
					//Leer los archivos, los cuales se pasan por JSON
					$json=($_REQUEST['file']);
					$archivos=json_decode($json, true);
					$Files=array();//Donde se almacenan los archivos generados, los cuales se van a comprimir.
					
					$Count=count($archivos);					
					$i=0;
					while($i<$Count){
						$Parametros=array(
							'pIdObjeto' => $archivos[$i]['Obj'],//Código del objeto
							'pIdFormato' => $archivos[$i]['Serie'],//Id del formato o la serie, en la misma poscicion que la de la factura
							'pDockey' => $archivos[$i]['Num'],
							'pID' => (isset($_REQUEST['idreg'])) ? base64_decode($_REQUEST['idreg']) : '',
							'pUsuario' => $_SESSION['User']
						);
						
//						print_r($Parametros);
//						exit();

						$result = $Client->CrearFormatoSAP($Parametros);
						if(is_soap_fault($result)){
							trigger_error("Fallo IntSAPB1: (Codigo: {$result->faultcode}, Mensaje: {$result->faultstring})", E_USER_ERROR);
						}

						$Respuesta=$Client->__getLastResponse();

						$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);

						$espaciosDeNombres = $Contenido->getNamespaces(true);
						$Nodos = $Contenido->children($espaciosDeNombres['s']);
						$Nodo=	$Nodos->children($espaciosDeNombres['']);
						$Nodo2=	$Nodo->children($espaciosDeNombres['']);
						//echo $Nodo2[0];
						try{
							$Archivo=json_decode($Nodo2[0],true);
							//$Archivo=explode("#",$Nodo2[0]);
							if($Archivo['ID_Respuesta']=="0"){
								//InsertarLog(1, 0, 'Error al generar el informe');
								throw new Exception('Error al generar el informe. Error de WebServices');		
							}
						}catch (Exception $e){
							echo 'Excepción capturada: ',  $e->getMessage(), "\n";
							InsertarLog(1, 501, 'Excepción capturada: '.$e->getMessage());//501, cod de SAP Download
						}
						$Files[$i]=$Archivo['DE_Respuesta'];
						
						if(SO=="Linux"){//Copiar los archivos a la ruta local para comprimirlos localmente
							if(!copy($SrvRuta.$Archivo['DE_Respuesta'],$RutaLocal.$Archivo['DE_Respuesta'])){
								exit('No se pudo copiar el archivo '.$Archivo['DE_Respuesta']);			
							}
						}
						$i++;
					}
					
					//Crear archivo ZIP e insertar los archivos
					$zip = new ZipArchive();
					$zipName=date('YmdHi')."_".$_SESSION['CodUser'].".zip";
					$filezip = $RutaLocal.$zipName;
					//echo $filezip;
					//exit();

					if($zip->open($filezip, ZIPARCHIVE::CREATE)===TRUE){
						$Count=count($Files);
						$i=0;
						//$zip->close();
						while($i<$Count){
							$zip->addFile($RutaLocal.$Files[$i],$Files[$i]);
							//echo "Se agregó: ".$SrvRuta.$Files[$i]."\n";
							$i++;
						}
						//exit();
						$zip->close();
						$Archivo['DE_Respuesta']=$zipName;
					}else{
						exit("No se puede abrir el archivo $filezip\n");
					}
					
				}else{					
					$Parametros=array(
						'pIdObjeto' => base64_decode($_REQUEST['ObType']),//Codigo del objeto
						'pIdFormato' => base64_decode($_REQUEST['IdFrm']),//Id del formato (Serie)
						'pDockey' => base64_decode($_REQUEST['DocKey']),//DocEntry del documento
						'pID' => (isset($_REQUEST['IdReg'])) ? base64_decode($_REQUEST['IdReg']) : '',//Id de la tabla de formatos (para cuando hay varios formatos de la misma serie)
						'pUsuario' => $_SESSION['User']
					);

					$result = $Client->CrearFormatoSAP($Parametros);
					if(is_soap_fault($result)){
						trigger_error("Fallo IntSAPB1: (Codigo: {$result->faultcode}, Mensaje: {$result->faultstring})", E_USER_ERROR);
					}

					$Respuesta=$Client->__getLastResponse();

					$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);

					$espaciosDeNombres = $Contenido->getNamespaces(true);
					$Nodos = $Contenido->children($espaciosDeNombres['s']);
					$Nodo=	$Nodos->children($espaciosDeNombres['']);
					$Nodo2=	$Nodo->children($espaciosDeNombres['']);
					//echo $Nodo2[0];
					try{
						$Archivo=json_decode($Nodo2[0],true);
						//$Archivo=explode("#",$Nodo2[0]);
						if($Archivo['ID_Respuesta']=="0"){
							//InsertarLog(1, 0, 'Error al generar el informe');
							throw new Exception('Error al generar el informe. Error de WebServices');		
						}
					}catch (Exception $e){
						echo 'Excepción capturada: ',  $e->getMessage(), "\n";
						InsertarLog(1, 501, 'Excepción capturada: '.$e->getMessage());//501, cod de SAP Download
					}	
				}
				
			}
			if($ID==16){//Formatos de factura de proyecto SAP B1 (Dialnet)
				if($ZipMode==1){//Comprimir los archivos descargados
					
					//Leer los archivos, los cuales se pasan por JSON
					$json=($_REQUEST['file']);
					$archivos=json_decode($json, true);
					$Files=array();//Donde se almacenan los archivos generados, los cuales se van a comprimir.
					
					$Count=count($archivos);
					$i=0;
					while($i<$Count){
						$Parametros=array(
							'pIdObjeto' => $archivos[$i]['Obj'],//Código del objeto
							'pIdFormato' => $archivos[$i]['Serie'],//Id del formato o la serie, en la misma poscicion que la de la factura
							'pDockey' => $archivos[$i]['Num'],
							'pCedula' => $archivos[$i]['Cedula'],
							'pFechaFactura' => $archivos[$i]['Fecha'],
							'pUsuario' => $_SESSION['User']
						);

						$result = $Client->CrearFormatoProyectoSAP($Parametros);
						if(is_soap_fault($result)){
							trigger_error("Fallo IntSAPB1: (Codigo: {$result->faultcode}, Mensaje: {$result->faultstring})", E_USER_ERROR);
						}

						$Respuesta=$Client->__getLastResponse();

						$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);

						$espaciosDeNombres = $Contenido->getNamespaces(true);
						$Nodos = $Contenido->children($espaciosDeNombres['s']);
						$Nodo=	$Nodos->children($espaciosDeNombres['']);
						$Nodo2=	$Nodo->children($espaciosDeNombres['']);
						//echo $Nodo2[0];
						try{
							$Archivo=json_decode($Nodo2[0],true);
							//$Archivo=explode("#",$Nodo2[0]);
							if($Archivo['ID_Respuesta']=="0"){
								//InsertarLog(1, 0, 'Error al generar el informe');
								throw new Exception('Error al generar el informe. Error de WebServices');		
							}
						}catch (Exception $e){
							echo 'Excepción capturada: ',  $e->getMessage(), "\n";
							InsertarLog(1, 501, 'Excepción capturada: '.$e->getMessage());//501, cod de SAP Download
						}
						$Files[$i]=$Archivo['DE_Respuesta'];
						
						if(SO=="Linux"){//Copiar los archivos a la ruta local para comprimirlos localmente
							if(!copy($SrvRuta.$Archivo['DE_Respuesta'],$RutaLocal.$Archivo['DE_Respuesta'])){
								exit('No se pudo copiar el archivo '.$Archivo['DE_Respuesta']);			
							}
						}
						$i++;
					}
					
					//Crear archivo ZIP e insertar los archivos
					$zip = new ZipArchive();
					$zipName=date('YmdHi')."_".$_SESSION['CodUser'].".zip";
					$filezip = $RutaLocal.$zipName;
					//echo $filezip;
					//exit();

					if($zip->open($filezip, ZIPARCHIVE::CREATE)===TRUE){
						$Count=count($Files);
						$i=0;
						//$zip->close();
						while($i<$Count){
							$zip->addFile($RutaLocal.$Files[$i],$Files[$i]);
							//echo "Se agregó: ".$SrvRuta.$Files[$i]."\n";
							$i++;
						}
						//exit();
						$zip->close();
						$Archivo['DE_Respuesta']=$zipName;
					}else{
						exit("No se puede abrir el archivo $filezip\n");
					}				
					
				}else{
					$Parametros=array(
						'pIdObjeto' => base64_decode($_GET['ObType']),//Codigo del objeto
						'pIdFormato' => base64_decode($_GET['IdFrm']),//Id del formato
						'pDockey' => base64_decode($_GET['DocKey']),
						'pCedula' => base64_decode($_GET['Cedula']),
						'pFechaFactura' => base64_decode($_GET['Fecha']),
						'pUsuario' => $_SESSION['User']
					);

					$result = $Client->CrearFormatoProyectoSAP($Parametros);
					if(is_soap_fault($result)){
						trigger_error("Fallo IntSAPB1: (Codigo: {$result->faultcode}, Mensaje: {$result->faultstring})", E_USER_ERROR);
					}

					$Respuesta=$Client->__getLastResponse();

					$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);

					$espaciosDeNombres = $Contenido->getNamespaces(true);
					$Nodos = $Contenido->children($espaciosDeNombres['s']);
					$Nodo =	$Nodos->children($espaciosDeNombres['']);
					$Nodo2 = $Nodo->children($espaciosDeNombres['']);
					//echo $Nodo2[0];
					try{
						$Archivo=json_decode($Nodo2[0],true);
						//$Archivo=explode("#",$Nodo2[0]);
						if($Archivo['ID_Respuesta']=="0"){
							//InsertarLog(1, 0, 'Error al generar el informe');
							throw new Exception('Error al generar el informe. Error de WebServices');		
						}
					}catch (Exception $e){
						echo 'Excepción capturada: ',  $e->getMessage(), "\n";
						InsertarLog(1, 501, 'Excepción capturada: '.$e->getMessage());//501, cod de SAP Download
					}	
				}
				
			}
			if($ID==17){//Formatos SAP B1 en Lote
				$Parametros=array(
					'pIdObjeto' => base64_decode($_REQUEST['ObType']),//Codigo del objeto
					'pIdFormato' => base64_decode($_REQUEST['IdFrm']),//Id del formato
					'pDockey' => base64_decode($_REQUEST['DocKey']),
					'pUsuario' => $_SESSION['User']
				);

				$result = $Client->CrearFormatoSAP_Lote($Parametros);
				if(is_soap_fault($result)){
					trigger_error("Fallo IntSAPB1: (Codigo: {$result->faultcode}, Mensaje: {$result->faultstring})", E_USER_ERROR);
				}

				$Respuesta=$Client->__getLastResponse();

				$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);

				$espaciosDeNombres = $Contenido->getNamespaces(true);
				$Nodos = $Contenido->children($espaciosDeNombres['s']);
				$Nodo=	$Nodos->children($espaciosDeNombres['']);
				$Nodo2=	$Nodo->children($espaciosDeNombres['']);
				//echo $Nodo2[0];
				try{
					$Archivo=json_decode($Nodo2[0],true);
					//$Archivo=explode("#",$Nodo2[0]);
					if($Archivo['ID_Respuesta']=="0"){
						//InsertarLog(1, 0, 'Error al generar el informe');
						throw new Exception('Error al generar el informe. Error de WebServices');		
					}
				}catch (Exception $e){
					echo 'Excepción capturada: ',  $e->getMessage(), "\n";
					InsertarLog(1, 501, 'Excepción capturada: '.$e->getMessage());//501, cod de SAP Download
				}
				
			}
			if($ID==18){//Informe de rutas del programador
				$Parametros=array(
					'pNombreReporte' => 'InformeRutasOperaciones',
					'pFechaIni' => base64_decode($_REQUEST['FechaInicial']),//Id del formato
					'pFechaFin' => base64_decode($_REQUEST['FechaFinal']),
					'pSede' => base64_decode($_REQUEST['Sede']),
					'pAlmacen' => base64_decode($_REQUEST['Almacen']),
					'pTipoLlamada' => base64_decode($_REQUEST['TipoLlamada']),
					'pTecnicos' => base64_decode($_REQUEST['Tecnicos']),
					'pLogin' => $_SESSION['User'],
					'pUsuario' => $_SESSION['CodUser']
				);

				$result = $Client->CrearInformeRutaOperaciones($Parametros);
				if(is_soap_fault($result)){
					trigger_error("Fallo IntSAPB1: (Codigo: {$result->faultcode}, Mensaje: {$result->faultstring})", E_USER_ERROR);
				}

				$Respuesta=$Client->__getLastResponse();

				$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);

				$espaciosDeNombres = $Contenido->getNamespaces(true);
				$Nodos = $Contenido->children($espaciosDeNombres['s']);
				$Nodo=	$Nodos->children($espaciosDeNombres['']);
				$Nodo2=	$Nodo->children($espaciosDeNombres['']);
				//echo $Nodo2[0];
				try{
					$Archivo=json_decode($Nodo2[0],true);
					//$Archivo=explode("#",$Nodo2[0]);
					if($Archivo['ID_Respuesta']=="0"){
						//InsertarLog(1, 0, 'Error al generar el informe');
						throw new Exception('Error al generar el informe. Error de WebServices');		
					}
				}catch (Exception $e){
					echo 'Excepción capturada: ',  $e->getMessage(), "\n";
					InsertarLog(1, 501, 'Excepción capturada: '.$e->getMessage());//501, cod de SAP Download
				}				
			}
		}
		
	}catch(SoapFault $ex){
		echo "Fault code: {$ex->faultcode}"."<br>";
		echo "Fault string: {$ex->faultstring}"."<br>";
		if ($Client != null){
			$Client = null;
		}
		exit();
	}
	try{
		
		//BUSCAR ARCHIVO PARA DESCARGAR
		if($ZipMode==1){
			$filename = $filezip;
		}else{
			$filename = $SrvRuta.$Archivo['DE_Respuesta'];
			//echo $filename;
			//exit();
		}
		
		
		$NombreArchivo=$Archivo['DE_Respuesta'];
		$size = filesize($filename);
		//echo $filename;
		//exit();

		header("Content-Transfer-Encoding: binary");
		//header('Content-type: application/octet-stream', true);
		header('Content-type: application/pdf', true);
		header("Content-Type: application/force-download"); 
		header('Content-Disposition: attachment; filename="'.$NombreArchivo.'"');
		header("Content-Length: $size");
		readfile($filename);

		//echo $filename;
	}catch (Exception $e) {
		echo 'Excepción capturada: ',  $e->getMessage(), "\n";
		InsertarLog(1, 501, 'Excepción capturada: '.error_get_last());
	}
	
}
?>