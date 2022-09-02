<?php require_once("includes/conexion.php");
PermitirAcceso(303);
$IdActividad="";
$msg_error="";//Mensaje del error
$dt_LS=0;//sw para saber si vienen datos de la llamada de servicio. 0 no vienen. 1 si vienen.
$dt_DM=0;//sw para saber si vienen datos de un Documento de Marketing. 0 no vienen. 1 si vienen.
$TituloAct="INSPECCION DE CONDICIONES SANITARIAS";//Titulo por defecto cuando se está creando la actividad
$BloqEdit=0;//Saber si solo el usuario creador puede editar la actividad. 1 Bloqueado porque no es el creador. 0 Se puede editar por todos.

if(isset($_GET['id'])&&($_GET['id']!="")){
	$IdActividad=base64_decode($_GET['id']);
}

if(isset($_GET['tl'])&&($_GET['tl']!="")){//0 Creando una actividad. 1 Editando actividad.
	$type_act=$_GET['tl'];
}elseif(isset($_POST['tl'])&&($_POST['tl']!="")){
	$type_act=$_POST['tl'];
}else{
	$type_act=0;
}

if(isset($_REQUEST['dt_LS'])&&($_REQUEST['dt_LS'])==1){//Verificar que viene de una Llamada de servicio
	$dt_LS=1;
	
	//Clientes
	$SQL_Cliente=Seleccionar('uvw_Sap_tbl_Clientes','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreCliente');
	$row_Cliente=sqlsrv_fetch_array($SQL_Cliente);
	
	//Contacto cliente
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreContacto');
		
	//Sucursal cliente
	$SQL_SucursalCliente=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."' and TipoDireccion='S'",'NombreSucursal');
	
	//Orden de servicio
	$SQL_OrdenServicioCliente=Seleccionar('uvw_Sap_tbl_LlamadasServicios','*',"ID_LlamadaServicio='".base64_decode($_GET['LS'])."'");
}

if(isset($_REQUEST['dt_DM'])&&($_REQUEST['dt_DM'])==1){//Verificar que viene de un documento de marketing
	$dt_DM=1;
	
	//Clientes
	$SQL_Cliente=Seleccionar('uvw_Sap_tbl_Clientes','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreCliente');
	$row_Cliente=sqlsrv_fetch_array($SQL_Cliente);
	
	//Contacto cliente
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreContacto');
		
	//Sucursal cliente
	$SQL_SucursalCliente=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."' and TipoDireccion='S'",'NombreSucursal');
	
	//Documentos asociados
	$ParametrosDoc=array(
		"'".base64_decode($_GET['DM_type'])."'",
		"'".base64_decode($_GET['Cardcode'])."'",
		"'".base64_decode($_GET['DM'])."'"
	);
	$SQL_DocMarketing=EjecutarSP('sp_ConsultarDocMarketing',$ParametrosDoc);
	
	//Tipos de documentos de marketing
	$SQL_TipoDocMark=Seleccionar('tbl_ObjetosSAP','*',"ID_Objeto='".base64_decode($_GET['DM_type'])."'");
	
	//Orden de servicio
	//$SQL_OrdenServicioCliente=Seleccionar('uvw_Sap_tbl_LlamadasServicios','*',"ID_CodigoCliente='".base64_decode($_GET['Cardcode'])."' and NombreSucursal='".base64_decode($_GET['Sucursal'])."'",'AsuntoLlamada');
}

if(isset($_POST['swError'])&&($_POST['swError']!="")){//Para saber si ha ocurrido un error.
	$sw_error=$_POST['swError'];
}else{
	$sw_error=0;
}

if($type_act==0){
	$Title="Crear actividad";
}else{
	$Title="Editar actividad";
}

if(isset($_POST['P'])&&($_POST['P']==27)){//Insertar nueva actividad
	try{
		//*** Carpeta temporal ***
		$i=0;//Archivos
		$RutaAttachSAP=ObtenerDirAttach();
		$dir=CrearObtenerDirTemp();
		$dir_new=CrearObtenerDirAnx("actividades");		   
		$route= opendir($dir);
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
			
		if(isset($_POST['chkTodoDia'])&&($_POST['chkTodoDia']==1)){
			$HoraInicio="00:00";
			$HoraFin="00:00";
			$chkTodoDia=1;
		}else{
			$HoraInicio=$_POST['HoraInicio'];
			$HoraFin=$_POST['HoraFin'];
			$chkTodoDia=0;
		}
		
		$DocMark=array();
		
		if($_POST['DocMarketing']!=""){
			$DocMark=explode("__",$_POST['DocMarketing']);
		}else{
			$DocMark[0]="";
			$DocMark[1]="";
		}
		
		/*if($_POST['TipoAsignado']==3){//Tipo lista
			$IdLista="'".$_POST['EmpleadoActividad']."'";
			$IdEmpleado="NULL";
		}else{
			$IdLista="NULL";
			$IdEmpleado="'".$_POST['EmpleadoActividad']."'";
		}*/
		
		$ParamInsActividad=array(
			"NULL",
			"'".$_POST['OrdenServicioActividad']."'",
			"'".$_POST['TipoTarea']."'",
			"'".$_POST['TipoActividad']."'",
			"'".$_POST['AsuntoActividad']."'",
			"'".LSiqmlObs($_POST['TituloActividad'])."'",
			"'".$_POST['TipoAsignado']."'",
			"'".$_POST['EmpleadoActividad']."'",
			"'".$_POST['EnRuta']."'",
			"'".$_POST['MotivoCierre']."'",
			"'".$_POST['ClienteActividad']."'",
			"'".$_POST['ContactoCliente']."'",
			"'".$_POST['TelefonoActividad']."'",
			"'".$_POST['CorreoActividad']."'",
			"'".$_POST['SucursalCliente']."'",
			"'".$_POST['DireccionActividad']."'",
			"'".$_POST['NombreCiudad']."'",
			"'".$_POST['BarrioDireccionActividad']."'",
			"'".FormatoFecha($_POST['FechaInicio'],$HoraInicio)."'",
			"'".FormatoFecha($_POST['FechaFin'],$HoraFin)."'",
			"'".$chkTodoDia."'",
			"'".LSiqmlObs($_POST['Comentarios'])."'",
			"NULL",
			"'".$_POST['EstadoActividad']."'",
			"'".$_POST['TipoEstadoActividad']."'",
			"'".$_POST['OrdenServicioActividad']."'",
			"'".$_POST['IdAnexos']."'",
			"1",
			"'".$_SESSION['CodUser']."'",
			"'".$_POST['TipoDocMarketing']."'",
			"'".$DocMark[1]."'",
			"'".$DocMark[0]."'",
			"NULL",
			"NULL",
			"NULL",
			"NULL",
			"'".$_POST['CDU_TurnoTecnico']."'",
			"1"
		);
		
		$SQL_InsActividad=EjecutarSP('sp_tbl_Actividades',$ParamInsActividad,27);		
		if($SQL_InsActividad){
			$row_NewIdActividad=sqlsrv_fetch_array($SQL_InsActividad);
			$IdActividad=$row_NewIdActividad[0];
			
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
							"'66'",
							"'".$IdActividad."'",
							"'".$OnlyName."'",
							"'".$Ext."'",
							"1",
							"'".$_SESSION['CodUser']."'",
							"1"					
						);
						$SQL_InsAnex=EjecutarSP('sp_tbl_DocumentosSAP_Anexos',$ParamInsAnex,27);
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
				$Parametros=array(
					'id_documento' => intval($IdActividad),
					'id_evento' => 0
				);

				$Metodo="Actividades";
				$Resultado=EnviarWebServiceSAP($Metodo,$Parametros,true,true);
			
				if($Resultado->Success==0){
					$sw_error=1;
					$msg_error=$Resultado->Mensaje;
					if($_POST['EstadoActividad']=='Y'){
						$UpdEstado="Update tbl_Actividades Set Cod_Estado='N' Where ID_Actividad='".$IdActividad."'";
						$SQL_UpdEstado=sqlsrv_query($conexion,$UpdEstado);
					}
				}else{
					//Enviar correo
					$ParamEnviaMail=array(
						"'".$IdActividad."'",
						"'66'",
						"'3'"			
					);
					$SQL_EnviaMail=EjecutarSP('usp_CorreoEnvio',$ParamEnviaMail,27);
					if(!$SQL_EnviaMail){
						$sw_error=1;
						$msg_error="Error al enviar el correo al usuario.";
					}
					sqlsrv_close($conexion);
					if($_POST['dt_LS']==1||$_POST['dt_DM']==1){
						header('Location:'.base64_decode($_POST['return']).'&a='.base64_encode("OK_ActAdd"));
					}else{
						header('Location:gestionar_actividades.php?a='.base64_encode("OK_ActAdd"));	
					}
				}	
			}catch (Exception $e) {
				echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
			}			
			
		}else{
			$sw_error=1;
			$msg_error="Error al crear la actividad";
		}						
	}catch (Exception $e) {
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
	
}

if(isset($_POST['P'])&&($_POST['P']==29)){//Actualizar actividad
	try{
		//*** Carpeta temporal ***
		$i=0;//Archivos
		$RutaAttachSAP=ObtenerDirAttach();
		$dir=CrearObtenerDirTemp();
		$dir_new=CrearObtenerDirAnx("actividades");		   
		$route= opendir($dir);
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
				
		if(isset($_POST['chkTodoDia'])&&($_POST['chkTodoDia']==1)){
			$HoraInicio="00:00";
			$HoraFin="00:00";
			$TodoDia=1;
		}else{
			$HoraInicio=$_POST['HoraInicio'];
			$HoraFin=$_POST['HoraFin'];
			$TodoDia=0;
		}
		
		$Metodo=2;//Actualizar en el web services
		$Type=2;//Ejecutar actualizar en el SP
		if(base64_decode($_POST['IdActividadPortal'])==""){
			$Metodo=2;
			$Type=1;
		}
		
		$DocMark=array();
		
		if($_POST['DocMarketing']!=""){
			$DocMark=explode("__",$_POST['DocMarketing']);
		}else{
			$DocMark[0]="";
			$DocMark[1]="";
		}
		
		//$dateInicial = date_create($_POST['FechaInicio']);
		//$FInicio=date_format($dateInicial, 'd/m/Y');
		
		//$dateFInal = date_create($_POST['FechaFin']);
		//$FFin=date_format($dateFInal, 'd/m/Y');
		
		/*if($_POST['TipoAsignado']==3){//Tipo lista
			$IdLista="'".$_POST['EmpleadoActividad']."'";
			$IdEmpleado="NULL";
		}else{
			$IdLista="NULL";
			$IdEmpleado="'".$_POST['EmpleadoActividad']."'";
		}*/
		
		$ParamUpdActividad=array(
			"'".base64_decode($_POST['IdActividadPortal'])."'",
			"'".base64_decode($_POST['ID'])."'",
			"'".$_POST['TipoTarea']."'",
			"'".$_POST['TipoActividad']."'",
			"'".$_POST['AsuntoActividad']."'",
			"'".LSiqmlObs($_POST['TituloActividad'])."'",
			"'".$_POST['TipoAsignado']."'",
			"'".$_POST['EmpleadoActividad']."'",
			"'".$_POST['EnRuta']."'",
			"'".$_POST['MotivoCierre']."'",
			"'".$_POST['ClienteActividad']."'",
			"'".$_POST['ContactoCliente']."'",
			"'".$_POST['TelefonoActividad']."'",
			"'".$_POST['CorreoActividad']."'",
			"'".$_POST['SucursalCliente']."'",
			"'".$_POST['DireccionActividad']."'",
			"'".$_POST['NombreCiudad']."'",
			"'".$_POST['BarrioDireccionActividad']."'",
			"'".FormatoFecha($_POST['FechaInicio'],$HoraInicio)."'",
			"'".FormatoFecha($_POST['FechaFin'],$HoraFin)."'",
			"'".$TodoDia."'",
			"'".LSiqmlObs($_POST['Comentarios'])."'",
			"'".LSiqmlObs($_POST['NotasActividad'])."'",
			"'".$_POST['EstadoActividad']."'",
			"'".$_POST['TipoEstadoActividad']."'",
			"'".$_POST['OrdenServicioActividad']."'",
			"'".$_POST['IdAnexos']."'",
			"$Metodo",
			"'".$_SESSION['CodUser']."'",
			"'".$_POST['TipoDocMarketing']."'",
			"'".$DocMark[1]."'",
			"'".$DocMark[0]."'",
			"'".$_POST['FechaInicioEjecucion']."'",
			"'".$_POST['HoraInicioEjecucion']."'",
			"'".$_POST['FechaFinEjecucion']."'",
			"'".$_POST['HoraFinEjecucion']."'",
			"'".$_POST['CDU_TurnoTecnico']."'",
			"$Type"		
		);
		$SQL_UpdActividad=EjecutarSP('sp_tbl_Actividades',$ParamUpdActividad,29);
		if($SQL_UpdActividad){
			if(base64_decode($_POST['IdActividadPortal'])==""){
				$row_NewIdActividad=sqlsrv_fetch_array($SQL_UpdActividad);
				$IdActividad=$row_NewIdActividad[0];
			}else{
				$IdActividad=base64_decode($_POST['IdActividadPortal']);
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
							"'66'",
							"'".$IdActividad."'",
							"'".$OnlyName."'",
							"'".$Ext."'",
							"1",
							"'".$_SESSION['CodUser']."'",
							"1"					
						);
						$SQL_InsAnex=EjecutarSP('sp_tbl_DocumentosSAP_Anexos',$ParamInsAnex,29);
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
				$Parametros=array(
					'id_documento' => intval($IdActividad),
					'id_evento' => 0
				);

				$Metodo="Actividades";
				$Resultado=EnviarWebServiceSAP($Metodo,$Parametros,true,true);
			
				if($Resultado->Success==0){
					$sw_error=1;
					$msg_error=$Resultado->Mensaje;
					if($_POST['EstadoActividad']=='Y'){
						$UpdEstado="Update tbl_Actividades Set Cod_Estado='N' Where ID_Actividad='".$IdActividad."'";
						$SQL_UpdEstado=sqlsrv_query($conexion,$UpdEstado);
					}
				}else{
					sqlsrv_close($conexion);
					header('Location:'.base64_decode($_POST['return']).'&a='.base64_encode("OK_UpdActAdd"));
				}	
			}catch (Exception $e) {
				echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
			}			
		}else{
			$sw_error=1;
			$msg_error="Error al actualizar la actividad";
		}						
	}catch (Exception $e) {
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}	
}

if(isset($_POST['P'])&&($_POST['P']==41)){//Eliminar la actividad
	try{
		$Parametros="";
		$Metodo="Actividades/Eliminar/".base64_decode($_POST['ID']);
		$Resultado=EnviarWebServiceSAP($Metodo,$Parametros,true,true);
		
		if($Resultado->Success==0){
			$sw_error=1;
			$msg_error=$Resultado->Mensaje;
			if($_POST['EstadoActividad']=='Y'){
				$UpdEstado="Update tbl_Actividades Set Cod_Estado='N' Where ID_Actividad='".base64_decode($_POST['IdActividadPortal'])."'";
				$SQL_UpdEstado=sqlsrv_query($conexion,$UpdEstado);
			}
		}else{
			sqlsrv_close($conexion);
			header('Location:'.base64_decode($_POST['return']).'&a='.base64_encode("OK_DelAct"));
		}	
	}catch (Exception $e) {
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}	
}

if(isset($_POST['P'])&&($_POST['P']==42)){//Reabrir actividad
	try{
		$Parametros="";
		$Metodo="Actividades/Reabrir/".base64_decode($_POST['ID']);
		$Resultado=EnviarWebServiceSAP($Metodo,$Parametros,true,true);
		
		if($Resultado->Success==0){
			$sw_error=1;
			$msg_error=$Resultado->Mensaje;
			if($_POST['EstadoActividad']=='Y'){
				$UpdEstado="Update tbl_Actividades Set Cod_Estado='N' Where ID_Actividad='".base64_decode($_POST['IdActividadPortal'])."'";
				$SQL_UpdEstado=sqlsrv_query($conexion,$UpdEstado);
			}
		}else{
			sqlsrv_close($conexion);
			header('Location:actividad.php?id='.$_POST['ID'].'&a='.base64_encode("OK_OpenAct").'&return='.$_POST['return_param'].'&pag='.$_POST['pag_param'].'&tl=1');
		}	
	}catch (Exception $e) {
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
}

if($type_act==1&&$sw_error==0){//Editando la actividad
	$SQL=Seleccionar('uvw_Sap_tbl_Actividades','*',"ID_Actividad='".$IdActividad."'");
	$row=sqlsrv_fetch_array($SQL);
	
	//Validar si es el creador de la actividad
	if(!PermitirFuncion(309)){
		if($row['IdAsignadoPor']!=$_SESSION['CodUser']){
			$BloqEdit=1;
		}	
	}
	
	//Clientes	
	$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes","CodigoCliente, NombreCliente","CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreCliente');	
	
	//Contactos clientes
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreContacto');

	//Sucursales
	$SQL_SucursalCliente=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreSucursal');

	//Anexos
	$SQL_AnexoActividad=Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos','*',"AbsEntry='".$row['IdAnexoActividad']."'");

	//Asunto actividad
	$SQL_AsuntoActividad=Seleccionar('uvw_Sap_tbl_AsuntosActividad','*',"Id_TipoActividad='".$row['ID_TipoActividad']."'",'DE_AsuntoActividad');
	
	//Documentos asociados
	$ParametrosDoc=array(
		"'".$row['DocMarkDocType']."'",
		"'".$row['ID_CodigoCliente']."'"
	);
	$SQL_DocMarketing=EjecutarSP('sp_ConsultarDocMarketing',$ParametrosDoc);
	
	//Orden de servicio
	$SQL_OrdenServicioCliente=Seleccionar('uvw_Sap_tbl_LlamadasServicios','*',"ID_CodigoCliente='".$row['ID_CodigoCliente']."' and NombreSucursal='".$row['NombreSucursal']."' And IdEstadoLlamada='-3'",'AsuntoLlamada');
}

if($sw_error==1){
	//Si ocurre un error, vuelvo a consultar los datos insertados desde la base de datos.
	$SQL=Seleccionar('uvw_tbl_Actividades','*',"ID_Actividad='".$IdActividad."'");
	$row=sqlsrv_fetch_array($SQL);
	
	//Validar si es el creador de la actividad
	if(!PermitirFuncion(309)){
		if($row['ID_Usuario']!=$_SESSION['CodUser']){
			$BloqEdit=1;
		}	
	}
	
	//Clientes
	$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes","CodigoCliente, NombreCliente","CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreCliente');
	
	//Contactos clientes
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreContacto');

	//Sucursales
	$SQL_SucursalCliente=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreSucursal');

	//Asunto actividad
	$SQL_AsuntoActividad=Seleccionar('uvw_Sap_tbl_AsuntosActividad','*',"Id_TipoActividad='".$row['ID_TipoActividad']."'",'DE_AsuntoActividad');
	
	//Documentos asociados
	$ParametrosDoc=array(
		"'".$row['DocMarkDocType']."'",
		"'".$row['ID_CodigoCliente']."'"
	);
	$SQL_DocMarketing=EjecutarSP('sp_ConsultarDocMarketing',$ParametrosDoc);
	
	//Orden de servicio
	$SQL_OrdenServicioCliente=Seleccionar('uvw_Sap_tbl_LlamadasServicios','*',"ID_CodigoCliente='".$row['ID_CodigoCliente']."' and NombreSucursal='".$row['NombreSucursal']."' And IdEstadoLlamada='-3'",'AsuntoLlamada');
}

//Tipos de actividad
$SQL_TipoActividad=Seleccionar('uvw_Sap_tbl_TiposActividad','*','','DE_TipoActividad');

//Empleados
$SQL_EmpleadoActividad=Seleccionar('uvw_Sap_tbl_Empleados','*',"IdUsuarioSAP=0",'NombreEmpleado');

//Lista de destinatarios
$SQL_ListaDest=Seleccionar('uvw_Sap_tbl_ListaDestinatarios','*','','DeListaAsignado');

//Usuarios SAP
$SQL_UsuariosSAP=Seleccionar('uvw_Sap_tbl_Empleados','*',"IdUsuarioSAP <> 0",'NombreEmpleado');

//Estado actividad
$SQL_EstadoActividad=Seleccionar('uvw_tbl_EstadoActividad','*');

//Tipos de Estado actividad
$SQL_TiposEstadoActividad=Seleccionar('uvw_Sap_tbl_TiposEstadosActividad','*');

//Motivo de cierre
$SQL_MotivoCierre=Seleccionar('uvw_Sap_tbl_MotivosCierreActividad','*');

//Turno técnico
$SQL_TurnoTecnicos=Seleccionar('uvw_Sap_tbl_TurnoTecnicos','*');
//$SQL_TurnoTecnicos=EjecutarSP('sp_ConsultarTurnoTecnico',$row['ID_EmpleadoActividad']);

if($dt_DM==0){
	//Tipos de documentos de marketing
	$SQL_TipoDocMark=Seleccionar('tbl_ObjetosSAP','*');
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $Title;?> | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_InsNotAct"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'Las notas fueron agregadas exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_OpenAct"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La actividad ha sido abierta nuevamente.',
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
                text: '".LSiqmlObs($msg_error)."',
                icon: 'warning'
            });
		});		
		</script>";
}
?>
<style>
	.ibox-title a{
		color: inherit !important;
	}
	.collapse-link:hover{
		cursor: pointer;
	}
</style>
<script>
function ValidarHoras(){
	var HInicio = document.getElementById("HoraInicio").value;
	var HFin = document.getElementById("HoraFin").value;
	
	HInicioMinutos = parseInt(HInicio.substr(3,2));
	HInicioHoras = parseInt(HInicio.substr(0,2));

	HFinMinutos = parseInt(HFin.substr(3,2));
	HFinHoras = parseInt(HFin.substr(0,2));

	TranscurridoMinutos = HFinMinutos - HInicioMinutos;
	TranscurridoHoras = HFinHoras - HInicioHoras;

	if (TranscurridoMinutos < 0) {
		TranscurridoHoras--;
		TranscurridoMinutos = 60 + TranscurridoMinutos;
	}
	
	if(TranscurridoHoras < 0){
		Swal.fire({
                title: '¡Error!',
                text: 'Tiempo no válido. Ingrese una duración positiva.',
                icon: 'error'
            });
		return false;
	}else{
		return true;
	}
}
function ValidarHorasEjec(){
	var HInicio = document.getElementById("HoraInicioEjecucion").value;
	var HFin = document.getElementById("HoraFinEjecucion").value;
	
	HInicioMinutos = parseInt(HInicio.substr(3,2));
	HInicioHoras = parseInt(HInicio.substr(0,2));

	HFinMinutos = parseInt(HFin.substr(3,2));
	HFinHoras = parseInt(HFin.substr(0,2));

	TranscurridoMinutos = HFinMinutos - HInicioMinutos;
	TranscurridoHoras = HFinHoras - HInicioHoras;

	if (TranscurridoMinutos < 0) {
		TranscurridoHoras--;
		TranscurridoMinutos = 60 + TranscurridoMinutos;
	}
	
	if(TranscurridoHoras < 0){
		swal({
                title: '¡Error!',
                text: 'Tiempo no válido. Ingrese una duración positiva.',
                icon: 'error'
            });
		return false;
	}else{
		return true;
	}
}
</script>
<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#TipoActividad").change(function(){
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=1&id="+document.getElementById('TipoActividad').value,
				success: function(response){
					$('#AsuntoActividad').html(response).fadeIn();
				}
			});
		});
		$("#ClienteActividad").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Cliente=document.getElementById('ClienteActividad').value;
			var Sucursal=document.getElementById('SucursalCliente').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=2&id="+Cliente,
				success: function(response){
					$('#ContactoCliente').html(response).fadeIn();
					$('#ContactoCliente').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);	
				}
			});
			<?php if($dt_LS!=1){?>
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=3&id="+Cliente,
				success: function(response){
					$('#SucursalCliente').html(response).fadeIn();
					$('#SucursalCliente').trigger('change');
				}
			});
			<?php }?>
			/*$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=4&id="+Cliente+"&suc="+btoa(Sucursal),
				success: function(response){
					$('#OrdenServicioActividad').html(response).fadeIn();
					$('#OrdenServicioActividad').val(null).trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});*/
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$("#SucursalCliente").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Cliente=document.getElementById('ClienteActividad').value;
			var Sucursal=document.getElementById('SucursalCliente').value;
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:1,CardCode:Cliente,Sucursal:Sucursal},
				dataType:'json',
				success: function(data){
					document.getElementById('DireccionActividad').value=data.Direccion;
					document.getElementById('BarrioDireccionActividad').value=data.Barrio;
					document.getElementById('NombreCiudad').value=data.Ciudad;
				}
			});
			<?php if($dt_LS!=1){?>
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=4&id="+Cliente+"&suc="+btoa(Sucursal),
				success: function(response){
					$('#OrdenServicioActividad').html(response).fadeIn();
					$('#OrdenServicioActividad').val(null).trigger('change');
				}
			});	
			<?php }?>
			$('.ibox-content').toggleClass('sk-loading',false);			
		});
		$("#ContactoCliente").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Contacto=document.getElementById('ContactoCliente').value;
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:5,Contacto:Contacto},
				dataType:'json',
				success: function(data){
					document.getElementById('TelefonoActividad').value=data.Telefono;
					document.getElementById('CorreoActividad').value=data.Correo;
				}
			});
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$("#TipoTarea").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var TipoTarea=document.getElementById('TipoTarea').value;
			if(TipoTarea=="Interna"){
				$.ajax({
					type: "POST",
					url: "ajx_cbo_select.php?type=5",
					success: function(response){
						$('#OrdenServicioActividad').html(response).fadeIn();
						$('#OrdenServicioActividad').val(null).trigger('change');						
					}
				});
				document.getElementById('ClienteActividad').value='<?php echo NIT_EMPRESA;?>';
				document.getElementById('NombreClienteActividad').value='<?php echo NOMBRE_EMPRESA;?>';
				document.getElementById('NombreClienteActividad').readOnly=true;
				$('#ClienteActividad').trigger('change');
				$('.ibox-content').toggleClass('sk-loading',false);
				//HabilitarCampos(0);
			}else{
				//HabilitarCampos(1);
				document.getElementById('ClienteActividad').value='';
				document.getElementById('NombreClienteActividad').value='';
				document.getElementById('NombreClienteActividad').readOnly=false;
				$('#ClienteActividad').trigger('change');
				$('.ibox-content').toggleClass('sk-loading',false);				
			}		
		});
		$("#TipoDocMarketing").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var TipoDoc=document.getElementById('TipoDocMarketing').value;
			var Cliente=document.getElementById('ClienteActividad').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=21&id="+Cliente+"&doctype="+TipoDoc,
				success: function(response){
					$('#DocMarketing').html(response).fadeIn();
					$('#DocMarketing').val(null).trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});	
		});
		$("#TipoAsignado").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var TipoAsig=document.getElementById('TipoAsignado').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=22&id="+TipoAsig,
				success: function(response){
					$('#EmpleadoActividad').html(response).fadeIn();
					$('#EmpleadoActividad').val(null).trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});	
		});
		/*$("#EmpleadoActividad").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Tecnico=document.getElementById('EmpleadoActividad').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=17&id="+Tecnico,
				success: function(response){
					$('#CDU_TurnoTecnico').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});			
		});*/
		$('#chkTodoDia').on('ifChecked', function(event){
			document.getElementById('dv_HoraInicio').style.display='none';
			document.getElementById('dv_HoraFin').style.display='none';
		});
		$('#chkTodoDia').on('ifUnchecked', function(event){
			document.getElementById('dv_HoraInicio').style.display='table';
			document.getElementById('dv_HoraFin').style.display='table';
		});
		<?php if($dt_DM==1){?>
		$('#SucursalCliente').trigger('change');
		<?php }?>
	});
</script>
<script>
function HabilitarCampos(type=1){
	if(type==0){//Deshabilitar
		document.getElementById('DatosActividad').style.display='none';
		document.getElementById('DatosCliente').style.display='none';
	}else{//Habilitar
		document.getElementById('DatosActividad').style.display='block';
		document.getElementById('DatosCliente').style.display='block';
	}
}
function ConsultarDatosCliente(){
	var Cliente=document.getElementById('ClienteActividad');
	if(Cliente.value!=""){
		self.name='opener';
		remote=open('socios_negocios.php?id='+Base64.encode(Cliente.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}
</script>
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2><?php echo $Title;?></h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Gesti&oacute;n de tareas</a>
                        </li>
                        <li>
                            <a href="gestionar_actividades.php">Gestionar actividades</a>
                        </li>
                        <li class="active">
                            <strong><?php echo $Title;?></strong>
                        </li>
                    </ol>
                </div>
            </div>
           
      <div class="wrapper wrapper-content">
	  <?php if($type_act==1){?>
			<div class="row">
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">N&uacute;mero de actividad</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php echo $row['ID_Actividad'];?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-2">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Creada por</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php if($sw_error==0&&$row['UsuarioCreacion']!=""){echo $row['UsuarioCreacion'];}else{echo "&nbsp;";}?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-2">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Fecha creación</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php if($sw_error==0&&$row['FechaCreacion']!=""){echo $row['FechaCreacion']." ".$row['HoraCreacion'];}else{echo "&nbsp;";}?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-2">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Actualizado por</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php if($sw_error==0&&$row['UsuarioActualizacion']!=""){echo $row['UsuarioActualizacion'];}else{echo "&nbsp;";}?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Fecha actualización</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php if($sw_error==0&&$row['FechaActualizacion']!=""){echo $row['FechaActualizacion']." ".$row['HoraActualizacion'];}else{echo "&nbsp;";}?></h3>
						</div>
					</div>
				</div>
			</div>
			<?php }?>
			<?php if($type_act==1){?>
				<div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
					<div class="row">
						<div class="col-lg-12"> 
							<div class="ibox">
								<div class="ibox-title bg-success">
									<h5 class="collapse-link"><i class="fa fa-play-circle"></i> Acciones</h5>
									 <a class="collapse-link pull-right">
										<i class="fa fa-chevron-up"></i>
									</a>	
								</div>
								<div class="ibox-content">
									<div class="form-group">
										<div class="col-lg-6">
											<div class="btn-group">
												<button data-toggle="dropdown" class="btn btn-outline btn-success dropdown-toggle"><i class="fa fa-download"></i> Descargar formato <i class="fa fa-caret-down"></i></button>
												<ul class="dropdown-menu">
													<?php 
													$SQL_Formato=Seleccionar('uvw_tbl_FormatosSAP','*',"ID_Objeto=66 and VerEnDocumento='Y'");
													while($row_Formato=sqlsrv_fetch_array($SQL_Formato)){ ?>
														<li>
															<a class="dropdown-item" target="_blank" href="sapdownload.php?id=<?php echo base64_encode('15');?>&type=<?php echo base64_encode('2');?>&DocKey=<?php echo base64_encode($row['ID_Actividad']);?>&ObType=<?php echo base64_encode('66');?>&IdFrm=<?php echo base64_encode($row_Formato['IdFormato']);?>&IdReg=<?php echo base64_encode($row_Formato['ID']);?>"><?php echo $row_Formato['NombreVisualizar']; ?></a>
														</li>
													<?php }?>
												</ul>
											</div>
										</div>
									</div>
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
			   <form action="actividad.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="CrearActividad">
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-info-circle"></i> Información de tarea</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>	
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<label class="col-lg-1 control-label">Titulo actividad <span class="text-danger">*</span></label>
							<div class="col-lg-7">
								<input autocomplete="off" name="TituloActividad" type="text" required="required" class="form-control" id="TituloActividad" maxlength="150" value="<?php if($type_act==1||$sw_error==1){echo $row['TituloActividad'];}else{echo $TituloAct;}?>" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y')||($BloqEdit==1))){ echo "readonly='readonly'";}?>>
							</div>
							<label class="col-lg-1 control-label">Tipo tarea</label>
							<div class="col-lg-3">
								<select name="TipoTarea" class="form-control" id="TipoTarea" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "disabled='disabled'";}?>>
									<?php 
									if($dt_LS==1){
										if(base64_decode($_GET['TTarea'])=='Externa'){?>
											<option value="Externa">Externa</option>
									<?php }elseif(base64_decode($_GET['TTarea'])=='Interna'){?>
											<option value="Interna">Interna</option>
									<?php }
									}elseif($dt_DM==1){?>
										<option value="Externa">Externa</option>
									<?php								
									}else{?>
										<option value="Externa" <?php if(($type_act==1||$sw_error==1)&&(isset($row['TipoTarea']))&&($row['TipoTarea']=='Externa')){ echo "selected=\"selected\"";}?>>Externa</option>
										<option value="Interna" <?php if(($type_act==1||$sw_error==1)&&(isset($row['TipoTarea']))&&($row['TipoTarea']=='Interna')){ echo "selected=\"selected\"";}?>>Interna</option>
									<?php }?>
								</select>
							</div>
						</div>
						<div id="DatosActividad" class="form-group">
							<label class="col-lg-1 control-label">Tipo asunto <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="TipoActividad" class="form-control" id="TipoActividad" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "disabled='disabled'";}?> required="required">
										<option value="">Seleccione...</option>
								  <?php while($row_TipoActividad=sqlsrv_fetch_array($SQL_TipoActividad)){?>
										<option value="<?php echo $row_TipoActividad['ID_TipoActividad'];?>" <?php if((isset($row['ID_TipoActividad']))&&(strcmp($row_TipoActividad['ID_TipoActividad'],$row['ID_TipoActividad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoActividad['DE_TipoActividad'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Asunto <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="AsuntoActividad" class="form-control" id="AsuntoActividad" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "disabled='disabled'";}?> required="required">
									<?php if(($type_act==0)||($sw_error==1)){?><option value="">Seleccione...</option><?php }?>
									<?php if(($type_act==1)||($sw_error==1)){while($row_AsuntoActividad=sqlsrv_fetch_array($SQL_AsuntoActividad)){?>
										<option value="<?php echo $row_AsuntoActividad['ID_AsuntoActividad'];?>" <?php if((isset($row['ID_AsuntoActividad']))&&(strcmp($row_AsuntoActividad['ID_AsuntoActividad'],$row['ID_AsuntoActividad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_AsuntoActividad['DE_AsuntoActividad'];?></option>
								  <?php }}?>
								</select>
							</div>
							<label class="col-lg-1 control-label">En ruta <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="EnRuta" class="form-control" id="EnRuta" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "disabled='disabled'";}?> required>
									<option value="NO" <?php if((isset($row['EnRuta']))&&($row['EnRuta']=='NO')){ echo "selected=\"selected\"";}?>>NO</option>
									<option value="SI" <?php if((isset($row['EnRuta']))&&($row['EnRuta']=='SI')){ echo "selected=\"selected\"";}?>>SI</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Tipo asignado <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="TipoAsignado" class="form-control" required id="TipoAsignado" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "disabled='disabled'";}?>>
									<option value="2" <?php if((isset($row['IdTipoAsignado']))&&($row['IdTipoAsignado']=='2')){ echo "selected=\"selected\"";}?>>Empleado</option>
									<option value="1" <?php if((isset($row['IdTipoAsignado']))&&($row['IdTipoAsignado']=='1')){ echo "selected=\"selected\"";}?>>Usuario SAP</option>
									<option value="3" <?php if((isset($row['IdTipoAsignado']))&&($row['IdTipoAsignado']=='3')){ echo "selected=\"selected\"";}?>>Lista destinatarios</option>
								</select>
							</div>
							<label class="col-lg-1 control-label">Asignado a <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="EmpleadoActividad" class="form-control select2" required id="EmpleadoActividad" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "disabled='disabled'";}?>>
									<?php if(($type_act==0)||(($type_act==1)&&($row['IdTipoAsignado']==2))){?>	
										<option value="">(Sin asignar)</option>
								  <?php while($row_EmpleadoActividad=sqlsrv_fetch_array($SQL_EmpleadoActividad)){?>
										<option value="<?php echo $row_EmpleadoActividad['ID_Empleado'];?>" <?php if((isset($row['ID_EmpleadoActividad']))&&(strcmp($row_EmpleadoActividad['ID_Empleado'],$row['ID_EmpleadoActividad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EmpleadoActividad['NombreEmpleado'];?></option>
								  <?php }
									}elseif(($type_act==1)&&($row['IdTipoAsignado']==3)){?>
										<option value="">(Sin asignar)</option>
								  <?php while($row_ListaDest=sqlsrv_fetch_array($SQL_ListaDest)){?>
										<option value="<?php echo $row_ListaDest['IdListaAsignado'];?>" <?php if((isset($row['IdListaAsignado']))&&(strcmp($row_ListaDest['IdListaAsignado'],$row['IdListaAsignado'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ListaDest['DeListaAsignado'];?></option>
								  <?php }
									}elseif(($type_act==1)&&($row['IdTipoAsignado']==1)){?>
										<option value="">(Sin asignar)</option>
								  <?php while($row_UsuariosSAP=sqlsrv_fetch_array($SQL_UsuariosSAP)){?>
										<option value="<?php echo $row_UsuariosSAP['IdUsuarioSAP'];?>" <?php if((isset($row['ID_EmpleadoActividad']))&&(strcmp($row_UsuariosSAP['IdUsuarioSAP'],$row['ID_EmpleadoActividad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_UsuariosSAP['NombreEmpleado'];?></option>
								  <?php }
									}?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Turno técnico</label>
							<div class="col-lg-3">
								<select name="CDU_TurnoTecnico" class="form-control" id="CDU_TurnoTecnico" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								  <?php while($row_TurnoTecnicos=sqlsrv_fetch_array($SQL_TurnoTecnicos)){?>
										<option value="<?php echo $row_TurnoTecnicos['CodigoTurno'];?>" <?php if((isset($row['CDU_IdTurnoTecnico']))&&(strcmp($row_TurnoTecnicos['CodigoTurno'],$row['CDU_IdTurnoTecnico'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TurnoTecnicos['NombreTurno'];?></option>
								  <?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
				   <label class="col-lg-1 control-label">Motivo de cierre</label>
					<div class="col-lg-3">
                    	<select name="MotivoCierre" class="form-control select2" id="MotivoCierre" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "disabled='disabled'";}?>>
                          <?php while($row_MotivoCierre=sqlsrv_fetch_array($SQL_MotivoCierre)){?>
								<option value="<?php echo $row_MotivoCierre['IdMotivoCierre'];?>" <?php if((isset($row['IdMotivoCierre']))&&(strcmp($row_MotivoCierre['IdMotivoCierre'],$row['IdMotivoCierre'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_MotivoCierre['DeMotivoCierre'];?></option>
						  <?php }?>
						</select>
               	  	</div>
					<label class="col-lg-1 control-label">Estado <span class="text-danger">*</span></label>
					<div class="col-lg-3">
                    	<select name="EstadoActividad" class="form-control" id="EstadoActividad" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "disabled='disabled'";}?>>
                          <?php while($row_EstadoActividad=sqlsrv_fetch_array($SQL_EstadoActividad)){?>
								<option value="<?php echo $row_EstadoActividad['Cod_Estado'];?>" <?php if((isset($row['IdEstadoActividad']))&&(strcmp($row_EstadoActividad['Cod_Estado'],$row['IdEstadoActividad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoActividad['NombreEstado'];?></option>
						  <?php }?>
						</select>
               	  	</div>
					<label class="col-lg-1 control-label">Tipo estado actividad <span class="text-danger">*</span></label>
					<div class="col-lg-3">
                    	<select name="TipoEstadoActividad" class="form-control" id="TipoEstadoActividad" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "disabled='disabled'";}?> required="required">
							<option value="">Seleccione...</option>
                          <?php while($row_TiposEstadoActividad=sqlsrv_fetch_array($SQL_TiposEstadoActividad)){?>
								<option value="<?php echo $row_TiposEstadoActividad['ID_TipoEstadoActividad'];?>" <?php if((isset($row['IdTipoEstadoActividad']))&&(strcmp($row_TiposEstadoActividad['ID_TipoEstadoActividad'],$row['IdTipoEstadoActividad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TiposEstadoActividad['DE_TipoEstadoActividad'];?></option>
						  <?php }?>
						</select>
               	  	</div>
				</div>
					</div>
				</div>
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-group"></i> Información de cliente</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>	
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<label class="col-lg-1 control-label"><i onClick="ConsultarDatosCliente();" title="Consultar cliente" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Cliente <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<input name="ClienteActividad" type="hidden" id="ClienteActividad" value="<?php if(($type_act==1)||($sw_error==1)){echo $row['ID_CodigoCliente'];}elseif($dt_LS==1||$dt_DM==1){echo $row_Cliente['CodigoCliente'];}?>">
								<input name="NombreClienteActividad" type="text" required="required" class="form-control" id="NombreClienteActividad" placeholder="Digite para buscar..." value="<?php if(($type_act==1)||($sw_error==1)){echo $row['NombreCliente'];}elseif($dt_LS==1||$dt_DM==1){echo $row_Cliente['NombreCliente'];}?>" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y')||($BloqEdit==1)||($row['TipoTarea']=='Interna'))||(($sw_error==1)&&($row['TipoTarea']=='Interna'))||($dt_LS==1)||($dt_DM==1)) {echo "readonly='readonly'";}?>>
							</div>
							<label class="col-lg-1 control-label">Contacto <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="ContactoCliente" class="form-control" id="ContactoCliente" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "disabled='disabled'";}?> required>
									<?php if((($type_act==0)||($sw_error==1))&&($dt_LS!=1)&&($dt_DM!=1)){?><option value="">Seleccione...</option><?php }?>
									<?php if(($type_act==1)||($sw_error==1)||($dt_LS==1)||($dt_DM==1)){while($row_ContactoCliente=sqlsrv_fetch_array($SQL_ContactoCliente)){?>
										<option value="<?php echo $row_ContactoCliente['CodigoContacto'];?>" <?php if((isset($row['ID_ContactoCliente']))&&(strcmp($row_ContactoCliente['CodigoContacto'],$row['ID_ContactoCliente'])==0)){ echo "selected=\"selected\"";}elseif(isset($_GET['Contacto'])&&(strcmp($row_ContactoCliente['CodigoContacto'],base64_decode($_GET['Contacto']))==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ContactoCliente['ID_Contacto'];?></option>
								  <?php }}?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Sucursal <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="SucursalCliente" class="form-control select2" id="SucursalCliente" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "disabled='disabled'";}?> required>
									<?php if(($type_act==0)||($sw_error==1)){?><option value="">Seleccione...</option><?php }?>
									<?php if(($type_act==1)||($sw_error==1)||($dt_LS==1)||($dt_DM==1)){while($row_SucursalCliente=sqlsrv_fetch_array($SQL_SucursalCliente)){?>
										<option value="<?php echo $row_SucursalCliente['NombreSucursal'];?>" <?php if((isset($row['NombreSucursal']))&&(strcmp($row_SucursalCliente['NombreSucursal'],$row['NombreSucursal'])==0)){ echo "selected=\"selected\"";}elseif(isset($_GET['Sucursal'])&&(strcmp($row_SucursalCliente['NombreSucursal'],base64_decode($_GET['Sucursal']))==0)){ echo "selected=\"selected\"";}?>><?php echo $row_SucursalCliente['NombreSucursal'];?></option>
								  <?php }}?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Dirección <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<input name="DireccionActividad" type="text" required="required" class="form-control" id="DireccionActividad" maxlength="100" value="<?php if(($type_act==1)||($sw_error==1)){echo $row['DireccionActividad'];}elseif($dt_LS==1||$dt_DM==1){echo base64_decode($_GET['Direccion']);}?>" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y')||($BloqEdit==1))){ echo "readonly='readonly'";}?>>
							</div>
							<label class="col-lg-1 control-label">Barrio</label>
							<div class="col-lg-3">
								<input name="BarrioDireccionActividad" type="text" class="form-control" id="BarrioDireccionActividad" maxlength="50" value="<?php if(($type_act==1)||($sw_error==1)){echo $row['BarrioDireccionActividad'];}elseif($dt_LS==1){echo base64_decode($_GET['Barrio']);}?>" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y')||($BloqEdit==1))){ echo "readonly='readonly'";}?>>
							</div>
							<label class="col-lg-1 control-label">Teléfono <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<input name="TelefonoActividad" autocomplete="off" type="text" class="form-control" required="required" id="TelefonoActividad" maxlength="50" value="<?php if(($type_act==1)||($sw_error==1)){echo $row['TelefonoContacto'];}elseif($dt_LS==1){echo base64_decode($_GET['Telefono']);}?>" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y')||($BloqEdit==1))){ echo "readonly='readonly'";}?>>
							</div>
						</div>
						<div class="form-group">
					<label class="col-lg-1 control-label">Ciudad</label>
					<div class="col-lg-3">
                    	<input name="NombreCiudad" type="text" class="form-control" id="NombreCiudad" maxlength="100" value="<?php if(($type_act==1)||($sw_error==1)){echo $row['NombreCiudad'];}elseif($dt_LS==1){echo base64_decode($_GET['Ciudad']);}?>" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y')||($BloqEdit==1))){ echo "readonly='readonly'";}?>>
               	  	</div>
					<label class="col-lg-1 control-label">Correo</label>
					<div class="col-lg-3">
                    	<input name="CorreoActividad" autocomplete="off" type="text" class="form-control" id="CorreoActividad" maxlength="100" value="<?php if(($type_act==1)||($sw_error==1)){echo $row['CorreoContacto'];}elseif($dt_LS==1){echo base64_decode($_GET['Correo']);}?>" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y')||($BloqEdit==1))){ echo "readonly='readonly'";}?>>
               	  	</div>
				</div>
					</div>
				</div>
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-calendar"></i> Programación</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>	
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<label class="col-lg-1 control-label">Comentarios</label>
							<div class="col-lg-6">
								<textarea name="Comentarios" rows="7" maxlength="3000" class="form-control" id="Comentarios" type="text" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y')||($BloqEdit==1))){ echo "readonly='readonly'";}?>><?php if(($type_act==1)||($sw_error==1)){echo $row['ComentariosActividad'];}?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Fecha inicio <span class="text-danger">*</span></label>
							<div class="col-lg-2 input-group date">
								 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaInicio" type="text" required="required" class="form-control" id="FechaInicio" value="<?php if((($type_act==1)||($sw_error==1))&&($row['FechaInicioActividad'])!="1900-01-01"){echo $row['FechaInicioActividad'];}else{ echo date('Y-m-d');}?>" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y')||($BloqEdit==1))){ echo "readonly='readonly'";}?>>
							</div>
							<div id="dv_HoraInicio" class="col-lg-2 input-group clockpicker" data-autoclose="true" <?php if(($type_act==1)&&($row['TodoDia']==1)){?>style="display: none;"<?php }?>>
								<input name="HoraInicio" id="HoraInicio" type="text" class="form-control" value="<?php if((($type_act==1)||($sw_error==1))&&($row['HoraInicioActividad']!="00:00")){echo $row['HoraInicioActividad'];}else{echo date('H:i');}?>" required="required" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y')||($BloqEdit==1))){ echo "readonly='readonly'";}?> onChange="ValidarHoras();">
								<span class="input-group-addon">
									<span class="fa fa-clock-o"></span>
								</span>
							</div>
						  <div class="col-lg-2">
								<label class="checkbox-inline i-checks"><input name="chkTodoDia" type="checkbox" id="chkTodoDia" value="<?php if(($type_act==1)||($sw_error==1)){echo $row['TodoDia'];}?>" <?php if((($type_act==1)||($sw_error==1))&&($row['TodoDia']==1)){echo "checked='checked'";}?> <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y')||($BloqEdit==1))){ echo "disabled='disabled'";}?>> Todo el día</label>
						  </div>
						</div>
						<div class="form-group">
					<label class="col-lg-1 control-label">Fecha fin <span class="text-danger">*</span></label>
				  	<div class="col-lg-2 input-group date">
                    	 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaFin" id="FechaFin" type="text" required="required" class="form-control" value="<?php if((($type_act==1)||($sw_error==1))&&($row['FechaFinActividad'])!="1900-01-01"){echo $row['FechaFinActividad'];}else{ echo date('Y-m-d');}?>" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y')||($BloqEdit==1))){ echo "readonly='readonly'";}?>>
               	  	</div>
					<div id="dv_HoraFin" class="col-lg-2 input-group clockpicker" data-autoclose="true" <?php if(($type_act==1)&&($row['TodoDia']==1)){?>style="display: none;"<?php }?>>
						<?php 
							$nuevahora = strtotime ( '+'.ObtenerVariable("TimeMinAct").' minute' , time() ) ;
							$nuevahora = date ('H:i' , $nuevahora);
						?>
                    	<input name="HoraFin" id="HoraFin" type="text" class="form-control" value="<?php if((($type_act==1)||($sw_error==1))&&($row['HoraFinActividad']!="00:00")){echo $row['HoraFinActividad'];}else{echo $nuevahora;}?>" required="required" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y')||($BloqEdit==1))){ echo "readonly='readonly'";}?> onChange="ValidarHoras();">
						<span class="input-group-addon">
							<span class="fa fa-clock-o"></span>
						</span>
               	  	</div>
				</div>
					</div>
				</div>
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-edit"></i> Información adicional</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>	
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<label class="col-lg-1 control-label"><?php if(($type_act==1)&&($row['DocMarkDocType']!="-1")){?><a href="<?php echo $row['DocMarkPage'];?>.php?id=<?php echo base64_encode($row['DocMarkDocEntry']);?>&id_portal=<?php echo base64_encode($row['DocMarkIdPortal']);?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('actividad.php');?>" target="_blank" title="Consultar documento" class="btn-xs btn-success fa fa-search"></a> <?php }?>Documento asociado</label>
							<div class="col-lg-2">
								<select name="TipoDocMarketing" class="form-control" id="TipoDocMarketing" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "disabled='disabled'";}?>>
									<?php if($dt_DM!=1){?><option value="">(Ninguno)</option><?php }?>
									<?php while($row_TipoDocMark=sqlsrv_fetch_array($SQL_TipoDocMark)){?>
										<option value="<?php echo $row_TipoDocMark['ID_Objeto'];?>" <?php if((isset($row['DocMarkDocType']))&&(strcmp($row_TipoDocMark['ID_Objeto'],$row['DocMarkDocType'])==0)){ echo "selected=\"selected\"";}elseif(isset($_GET['DM_type'])&&(strcmp($row_TipoDocMark['ID_Objeto'],base64_decode($_GET['DM_type']))==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoDocMark['DE_Objeto'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Número de documento</label>
							<div class="col-lg-2">
								<select name="DocMarketing" class="form-control select2" id="DocMarketing" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "disabled='disabled'";}?>>
									<?php if($dt_DM!=1){?><option value="">(Ninguna)</option><?php }?>
									<?php if(($type_act==1)||($sw_error==1)||($dt_DM==1)){while($row_DocMarketing=sqlsrv_fetch_array($SQL_DocMarketing)){?>
										<option value="<?php echo $row_DocMarketing['DocEntry']."__".$row_DocMarketing['DocNum'];?>" <?php if((isset($row['DocMarkDocEntry']))&&(strcmp($row_DocMarketing['DocEntry'],$row['DocMarkDocEntry'])==0)){ echo "selected=\"selected\"";}elseif(isset($_GET['DM'])&&(strcmp($row_DocMarketing['DocEntry'],base64_decode($_GET['DM']))==0)){ echo "selected=\"selected\"";}?>><?php echo $row_DocMarketing['DocNum'];?></option>
								  <?php }}?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label"><?php if(($type_act==1)&&($row['ID_LlamadaServicio']!=0)){?><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_LlamadaServicio']);?>&tl=1" target="_blank" title="Consultar Llamada de servicio" class="btn-xs btn-success fa fa-search"></a> <?php }?>Orden servicio</label>
							<div class="col-lg-8">
								<select name="OrdenServicioActividad" class="form-control select2" id="OrdenServicioActividad" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "disabled='disabled'";}?>>
									<?php if($dt_LS!=1){?><option value="">(Ninguna)</option><?php }?>
									<?php if(($type_act==1)||($sw_error==1)||($dt_LS==1)||($dt_DM==1)){while($row_OrdenServicioCliente=sqlsrv_fetch_array($SQL_OrdenServicioCliente)){?>
										<option value="<?php echo $row_OrdenServicioCliente['ID_LlamadaServicio'];?>" <?php if((isset($row['ID_OrdenServicioActividad']))&&(strcmp($row_OrdenServicioCliente['ID_LlamadaServicio'],$row['ID_LlamadaServicio'])==0)){ echo "selected=\"selected\"";}elseif(isset($_GET['LS'])&&(strcmp($row_OrdenServicioCliente['ID_LlamadaServicio'],base64_decode($_GET['LS']))==0)){ echo "selected=\"selected\"";}?>><?php echo $row_OrdenServicioCliente['DocNum']." - ".$row_OrdenServicioCliente['AsuntoLlamada'];?></option>
								  <?php }}?>
								</select>
							</div>
						</div>
					</div>
				</div>
				<?php if($type_act==1){?>
			   	<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-pencil"></i> Notas</h5>
						<a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>	
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<div class="col-lg-11">
								<textarea name="NotasActividad" rows="10" class="form-control" id="NotasActividad" maxlength="3000" placeholder="Escriba los comentarios de los trabajos realizados..." type="text" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "readonly='readonly'";}?>><?php if(($type_act==1)||($sw_error==1)){echo $row['NotasActividad'];}?></textarea>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-9">
								<?php if(($type_act==1)&&(PermitirFuncion(304)&&($row['IdEstadoActividad']!='Y'))){?> 
									<button class="btn btn-success" type="submit" id="Insertar"><i class="fa fa-check-square-o"></i>&nbsp;Insertar notas</button>
								<?php }?>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Fecha inicio ejecución</label>
							<div class="col-lg-2 input-group date">
								 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaInicioEjecucion" type="text" class="form-control" id="FechaInicioEjecucion" value="<?php if((($type_act==1)||($sw_error==1))&&($row['CDU_FechaInicioEjecucionActividad'])!=""){echo $row['CDU_FechaInicioEjecucionActividad'];}?>" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "readonly='readonly'";}?>>
							</div>
							<div class="col-lg-2 input-group clockpicker" data-autoclose="true">
								<input name="HoraInicioEjecucion" id="HoraInicioEjecucion" type="text" class="form-control" value="<?php if((($type_act==1)||($sw_error==1))&&($row['CDU_HoraInicioEjecucionActividad']!="")){echo $row['CDU_HoraInicioEjecucionActividad'];}?>" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "readonly='readonly'";}?> onChange="ValidarHorasEjec();">
								<span class="input-group-addon">
									<span class="fa fa-clock-o"></span>
								</span>
							</div>
						</div>
						<div class="form-group">
					<label class="col-lg-1 control-label">Fecha fin ejecución</label>
				  	<div class="col-lg-2 input-group date">
                    	 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaFinEjecucion" id="FechaFinEjecucion" type="text" class="form-control" value="<?php if((($type_act==1)||($sw_error==1))&&($row['CDU_FechaFinEjecucionActividad'])!=""){echo $row['CDU_FechaFinEjecucionActividad'];}?>" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "readonly='readonly'";}?>>
               	  	</div>
					<div class="col-lg-2 input-group clockpicker" data-autoclose="true">
						<?php 
							//$nuevahora = strtotime ( '+'.ObtenerVariable("TimeMinAct").' minute' , time() ) ;
							//$nuevahora = date ('H:i' , $nuevahora);
						?>
                    	<input name="HoraFinEjecucion" id="HoraFinEjecucion" type="text" class="form-control" value="<?php if((($type_act==1)||($sw_error==1))&&($row['CDU_HoraFinEjecucionActividad']!="")){echo $row['CDU_HoraFinEjecucionActividad'];}?>" <?php if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y'))){ echo "readonly='readonly'";}?> onChange="ValidarHorasEjec();">
						<span class="input-group-addon">
							<span class="fa fa-clock-o"></span>
						</span>
               	  	</div>
				</div>
					</div>
				</div>
				<?php }?>
			   	<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-paperclip"></i> Anexos</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>	
					</div>
					<div class="ibox-content">
						<?php if($type_act==1){
								if($row['IdAnexoActividad']!=0){?>
									<div class="form-group">
										<div class="col-xs-12">
											<?php while($row_AnexoActividad=sqlsrv_fetch_array($SQL_AnexoActividad)){
														$Icon=IconAttach($row_AnexoActividad['FileExt']);?>
												<div class="file-box">
													<div class="file">
														<a href="attachdownload.php?file=<?php echo base64_encode($row_AnexoActividad['AbsEntry']);?>&line=<?php echo base64_encode($row_AnexoActividad['Line']);?>" target="_blank">
															<div class="icon">
																<i class="<?php echo $Icon;?>"></i>
															</div>
															<div class="file-name">
																<?php echo $row_AnexoActividad['NombreArchivo'];?>
																<br/>
																<small><?php echo $row_AnexoActividad['Fecha'];?></small>
															</div>
														</a>
													</div>
												</div>
											<?php }?>
										</div>
									</div>
						<?php }else{ echo "<p>Sin anexos.</p>"; }
								}?>
						 <?php 			
							$EliminaMsg=array("&a=".base64_encode("OK_ActAdd"),"&a=".base64_encode("OK_UpdAdd"),"&a=".base64_encode("OK_OVenAdd"),"&a=".base64_encode("OK_DelAct"),"&a=".base64_encode("OK_OpenAct"));//Eliminar mensajes
			
							if(isset($_GET['return'])){
								$_GET['return']=str_replace($EliminaMsg,"",base64_decode($_GET['return']));
								$return=base64_decode($_GET['pag'])."?".$_GET['return'];						
							}else{
								$return="gestionar_actividades.php?";
							}?>
						   
							<input type="hidden" id="P" name="P" value="<?php if($type_act==0){echo "27";}else{echo "29";}?>" />
							<input type="hidden" id="swTipo" name="swTipo" value="0" />
							<input type="hidden" id="swError" name="swError" value="<?php echo $sw_error;?>" />
							<input type="hidden" id="tl" name="tl" value="<?php echo $type_act;?>" />
							<input type="hidden" id="dt_LS" name="dt_LS" value="<?php echo $dt_LS;?>" />
							<input type="hidden" id="dt_DM" name="dt_DM" value="<?php echo $dt_DM;?>" />
							<input type="hidden" id="pag_param" name="pag_param" value="<?php if(isset($_GET['pag'])){echo $_GET['pag'];}?>" />
							<input type="hidden" id="return_param" name="return_param" value="<?php if(isset($_GET['return'])){echo base64_encode($_GET['return']);}?>" />
							<input type="hidden" id="return" name="return" value="<?php echo base64_encode($return);?>" />			
							<input type="hidden" id="ID" name="ID" value="<?php if($type_act==1){ echo base64_encode($row['ID_Actividad']);}?>" />
							<input type="hidden" id="IdAnexos" name="IdAnexos" value="<?php if($type_act==1){echo $row['IdAnexoActividad'];}?>" />							
							<input type="hidden" id="IdActividadPortal" name="IdActividadPortal" value="<?php if(($type_act==1)&&($sw_error==0)){echo base64_encode($row['IdActividadPortal']);}elseif($sw_error==1){echo base64_encode($row['ID_Actividad']);}?>" />
						</form>
						<?php if(($type_act==0)||(($type_act==1)&&($row['IdEstadoActividad']!='Y'))){?> 
						<div class="row">
							<form action="upload.php" class="dropzone" id="dropzoneForm" name="dropzoneForm">
								<?php LimpiarDirTemp();	?>		 
								<div class="fallback">
									<input name="File" id="File" type="file" form="dropzoneForm" />
								</div>
							 </form>
						</div>
					</div>
				</div>
				<br><br>
				<?php }?>
				<div class="form-group">
					<div class="col-lg-9">
						<?php if(($type_act==1)&&(PermitirFuncion(304)&&($row['IdEstadoActividad']!='Y')&&($BloqEdit==0))){?> 
							<button class="btn btn-warning" form="CrearActividad" type="submit" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar actividad</button> 
						<?php }?>
						<?php if(($type_act==1)&&(PermitirFuncion(304)&&($row['IdEstadoActividad']=='Y')&&($BloqEdit==0))){?>
							<button class="btn btn-success" form="CrearActividad" type="submit" onClick="EnviarFrm('42');" id="Reabrir"><i class="fa fa-reply"></i> Reabrir</button>
						<?php }?>
						<?php if(($type_act==1)&&(PermitirFuncion(313)&&($BloqEdit==0))){?>
							<button class="btn btn-danger" form="CrearActividad" type="submit" onClick="EnviarFrm('41');" id="Eliminar"><i class="fa fa-trash"></i> Eliminar</button>
						<?php }?>
						<?php if($type_act==0){?> 
							<button class="btn btn-primary" form="CrearActividad" type="submit" id="Crear"><i class="fa fa-check"></i> Crear actividad</button>  
							<?php }?>
						<a href="<?php echo $return;?>" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
					</div>
				</div>
				<br><br>
		   </div>
			</div>
          </div>
	</div>
        <!-- InstanceEndEditable -->
        <?php include("includes/footer.php"); ?>

    </div>
</div>
<?php include("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>
	 $(document).ready(function(){
		$("#CrearActividad").validate({
			 submitHandler: function(form){
				 var vP=document.getElementById('P');
				 if(vP.value==41){
					 Swal.fire({
						title: "¿Estás seguro?",
						text: "¿Realmente desea eliminar esta actividad? Esta acción no se puede reversar.",
						icon: "warning",
						showCancelButton: true,
						confirmButtonText: "Si, estoy seguro",
						cancelButtonText: "Cancelar"
					}).then((result) => {
						if (result.isConfirmed) {
							$('.ibox-content').toggleClass('sk-loading');
							form.submit();	
						}
					});
				 }else{
					if(Validar()){
						 Swal.fire({
							title: "¿Está seguro que desea guardar los datos?",
							icon: "info",
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
			}
		});
		 $(".alkin").on('click', function(){
				 $('.ibox-content').toggleClass('sk-loading');
			});
			
		 maxLength('Comentarios');
		 maxLength('NotasActividad');
  
		 <?php if(PermitirFuncion(304)){?> 
		 $('#FechaInicio').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
			 	todayHighlight: true,
				format: 'yyyy-mm-dd'
            });
		 $('#FechaFin').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
			 	todayHighlight: true,
				format: 'yyyy-mm-dd'
            });
		 $('#FechaInicioEjecucion').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
			 	todayHighlight: true,
				format: 'yyyy-mm-dd'
            });
		 $('#FechaFinEjecucion').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
			 	todayHighlight: true,
				format: 'yyyy-mm-dd'
            });
		 $('.clockpicker').clockpicker();
	 	<?php }?>
		 $(".select2").select2();
		 $('.i-checks').iCheck({
			 checkboxClass: 'icheckbox_square-green',
             radioClass: 'iradio_square-green',
          });
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
					var value = $("#NombreClienteActividad").getSelectedItemData().CodigoCliente;
					$("#ClienteActividad").val(value).trigger("change");
				}
			}
		};
		var options2 = {
			url: function(phrase) {
				return "ajx_buscar_datos_json.php?type=8&id="+phrase;
			},

			getValue: "Ciudad",
			requestDelay: 400,
			template: {
				type: "description",
				fields: {
					description: "Codigo"
				}
			},
			list: {
				match: {
					enabled: true
				}
			}
		};
		<?php if($dt_LS==0&&$dt_DM==0){?>
		$("#NombreClienteActividad").easyAutocomplete(options);
	 	<?php }?>
		$("#NombreCiudad").easyAutocomplete(options2);
		 
		 <?php 
			if(($type_act==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y')||($BloqEdit==1))){?>
				//$('#ClienteActividad option:not(:selected)').attr('disabled',true);
		 		$('#TipoTarea option:not(:selected)').attr('disabled',true);
		 		$('#TipoActividad option:not(:selected)').attr('disabled',true);
		 		$('#AsuntoActividad option:not(:selected)').attr('disabled',true);
		 		$('#EnRuta option:not(:selected)').attr('disabled',true);
				$('#TipoAsignado option:not(:selected)').attr('disabled',true);
		 		$('#EmpleadoActividad option:not(:selected)').attr('disabled',true);
		 		//$('#EstadoActividad option:not(:selected)').attr('disabled',true);
		 		$('#CDU_TurnoTecnico option:not(:selected)').attr('disabled',true);
		 		$('#TipoEstadoActividad option:not(:selected)').attr('disabled',true);
		 		$('#ContactoCliente option:not(:selected)').attr('disabled',true);
		 		$('#SucursalCliente option:not(:selected)').attr('disabled',true);
		 		$('#TipoDocMarketing option:not(:selected)').attr('disabled',true);	
		 		$('#DocMarketing option:not(:selected)').attr('disabled',true);	
		 		$('#OrdenServicioActividad option:not(:selected)').attr('disabled',true);	
		<?php } ?>
		
		 <?php if($dt_LS==1||$dt_DM==1){?>
		 $('#ClienteActividad').trigger('change');
		<?php }?>
		
	});

function EnviarFrm(P=29){
	var vP=document.getElementById('P');
	var txtNotas=document.getElementById('NotasActividad');
	if(P==29){
		vP.value=P;
		txtNotas.setAttribute("required","required");
	}else{
		vP.value=P;
		txtNotas.removeAttribute("required");		
	}	
}

function Validar(){
	let result=true;
	
	let vP=document.getElementById('P');
	
	if(vP.value!=42&&vP.value!=41){
		if(!ValidarHoras()){
			result=false;
		}

		<?php if($type_act==1){?>

		if(!ValidarHorasEjec()){
			result=false;
		}

		let FechaInicio = document.getElementById("FechaInicio").value + ' ' + document.getElementById("HoraInicio").value;
		let FechaInicioEjecucion = document.getElementById("FechaInicioEjecucion").value + ' ' + document.getElementById("HoraInicioEjecucion").value;

		let FechaFin = document.getElementById("FechaFin").value + ' ' + document.getElementById("HoraFin").value;
		let FechaFinEjecucion = document.getElementById("FechaFinEjecucion").value + ' ' + document.getElementById("HoraFinEjecucion").value;

		if((Date.parse(FechaInicioEjecucion) < Date.parse(FechaInicio)) || (Date.parse(FechaFinEjecucion) < Date.parse(FechaFin))){
			result=false;
			Swal.fire({
				title: '¡Advertencia!',
				text: 'La fecha de ejecución no puede ser menor a la fecha de programación',
				icon: 'warning'
			});
		}
		<?php }?>
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