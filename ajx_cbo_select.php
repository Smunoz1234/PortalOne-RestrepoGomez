<?php 
if(!isset($_GET['type'])||($_GET['type']=="")){//Saber que combo voy a consultar
	exit();
}else{
	require_once("includes/conexion.php");
	
	if($_GET['type']==1){//Asunto actividad, dependiendo del tipo de actividad
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$Cons="Select * From uvw_Sap_tbl_AsuntosActividad Where Id_TipoActividad='".$_GET['id']."' Order by DE_AsuntoActividad";
			$SQL=sqlsrv_query($conexion,$Cons);
			if($SQL){
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['ID_AsuntoActividad']."\">".$row['DE_AsuntoActividad']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==2){//Contacto cliente, dependiendo del cliente
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$Vista="uvw_Sap_tbl_ClienteContactos";
			if(isset($_GET['pv'])&&($_GET['pv']==1)){//Proveedor
				$Vista="uvw_Sap_tbl_ProveedorContactos";
			}
			$FactElect=0;
			if(isset($_GET['fe'])&&$_GET['fe']!=""){
				$FactElect=$_GET['fe'];
			}
			if($FactElect==1){
				$Cons="Select * From $Vista Where CodigoCliente='".$_GET['id']."' And Estado='Y' Order by ID_Contacto";
			}else{
				$Cons="Select * From $Vista Where CodigoCliente='".$_GET['id']."' And Estado='Y' And ID_Contacto NOT LIKE '%ELECTRONICA%' Order by ID_Contacto";
			}
			$SQL=sqlsrv_query($conexion,$Cons);
			if($SQL){
				while($row=sqlsrv_fetch_array($SQL)){
					if($row['IdContactoPorDefecto']=="Y"){
						echo "<option value=\"".$row['CodigoContacto']."\" selected=\"selected\">".$row['ID_Contacto']."</option>";
					}else{
						echo "<option value=\"".$row['CodigoContacto']."\">".$row['ID_Contacto']."</option>";
					}					
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==3){//Sucursal cliente, dependiendo del cliente
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$type_dir='S';
			$sw_dirS=0;//Destino (Envio)
			$sw_dirB=0;//Factura
			$prov=0;//Si es proveedor
			if(isset($_GET['tdir'])&&$_GET['tdir']!=""){
				$type_dir=$_GET['tdir'];
			}
			if(isset($_GET['pv'])&&($_GET['pv']==1)){
				$prov=1;
			}
			$Parametros=array(
				"'".$_GET['id']."'",
				"'".$type_dir."'",
				"'".$_SESSION['CodUser']."'",
				$prov
			);
			$SQL=EjecutarSP('sp_ConsultarSucursalesClientes',$Parametros);
			
			//$Cons="Select * From uvw_Sap_tbl_Clientes_Sucursales Where CodigoCliente='".$_GET['id']."' and TipoDireccion='".$type_dir."' Order by TipoDireccion, NombreSucursal";
			//$SQL=sqlsrv_query($conexion,$Cons);
			if($SQL){
				while($row=sqlsrv_fetch_array($SQL)){
					if(($row['TipoDireccion']=="B")&&($sw_dirB==0)){
						echo "<optgroup label='Dirección de facturas'></optgroup>";
						$sw_dirB=1;
					}elseif(($row['TipoDireccion']=="S")&&($sw_dirS==0)){
						echo "<optgroup label='Dirección de destino'></optgroup>";
						$sw_dirS=1;
					}
					echo "<option value=\"".$row['NombreSucursal']."\">".$row['NombreSucursal']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==4){//Orden de servicio, dependiendo del cliente y la sucursal
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>(Ninguna)</option>";
		}else{		
			$SQL=Seleccionar('uvw_Sap_tbl_LlamadasServicios','*',"ID_CodigoCliente='".$_GET['id']."' And NombreSucursal='".base64_decode($_GET['suc'])."' And IdEstadoLlamada <> '-1'",'AsuntoLlamada');
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				echo "<option value=''>(Ninguna)</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['ID_LlamadaServicio']."\">".$row['DocNum']." - ".$row['AsuntoLlamada']."</option>";
				}
			}else{
				echo "<option value=''>(Ninguna)</option>";

			}
		}
	}
	elseif($_GET['type']==5){//Orden de servicio internas
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>(Ninguna)</option>";
		}else{
			$Cons="Select * From uvw_Sap_tbl_LlamadasServicios Where CodigoClienteLlamada='".NIT_EMPRESA."' And EstadoLlamada='Abierto' Order by AsuntoLlamada";
			$SQL=sqlsrv_query($conexion,$Cons,array(),array( "Scrollable" => 'Buffered' ));
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				echo "<option value=''>(Ninguna)</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['IdTicket']."\">".$row['IdTicket']." - ".$row['AsuntoLlamada']."</option>";
				}
			}else{
				echo "<option value=''>(Ninguna)</option>";
			}
		}
	}
	elseif($_GET['type']==6){//Orden de servicio, traer todas las de un cliente en particular
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>(Ninguna)</option>";
		}else{
			$SQL=Seleccionar('uvw_Sap_tbl_LlamadasServicios','ID_LlamadaServicio, DocNum, AsuntoLlamada, DeTipoLlamada',"ID_CodigoCliente='".$_GET['id']."'",'AsuntoLlamada');//Colocar estado Abierto
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				echo "<option value=''>(Ninguna)</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['ID_LlamadaServicio']."\">".$row['DocNum']." - ".$row['AsuntoLlamada']." (".$row['DeTipoLlamada'].")</option>";
				}
			}else{
				echo "<option value=''>(Ninguna)</option>";
			}
		}
	}
	elseif($_GET['type']==7){//Condiciones de pago dependiendo del cliente
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$Cons="Select * From uvw_Sap_tbl_CondicionPago Order by Dias";
			$SQL=sqlsrv_query($conexion,$Cons);
			if($SQL){
				$Vista="uvw_Sap_tbl_Clientes";
				if(isset($_GET['pv'])&&($_GET['pv']==1)){//Proveedor
					$Vista="uvw_Sap_tbl_Proveedores";
				}
				$SQL_Cliente=Seleccionar($Vista,'GroupNum',"CodigoCliente='".$_GET['id']."'");
				$row_Cliente=sqlsrv_fetch_array($SQL_Cliente);
				while($row=sqlsrv_fetch_array($SQL)){
					if(strcmp($row['IdCondicionPago'],$row_Cliente['GroupNum'])==0){
						echo "<option value=\"".$row['IdCondicionPago']."\" selected=\"selected\">".$row['NombreCondicion']."</option>";
					}else{
						echo "<option value=\"".$row['IdCondicionPago']."\">".$row['NombreCondicion']."</option>";
					}					
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==8){//Ciudad dependiendo del departamento
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$Cons="Select * From uvw_Sap_tbl_SN_Municipio Where DeDepartamento='".$_GET['id']."' Order by DE_Municipio";
			$SQL=sqlsrv_query($conexion,$Cons);
			if($SQL){
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['ID_Municipio']."\">".$row['DE_Municipio']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==9){//Evento de cartera, dependiendo del Tipo de Gestion
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			echo "<option value=''>Seleccione...</option>";
			$Cons="Select DISTINCT ID_Evento, NombreEvento From uvw_tbl_Cartera_RelacionConceptos Where ID_TipoGestion='".$_GET['id']."' Order by NombreEvento";
			$SQL=sqlsrv_query($conexion,$Cons);
			if($SQL){
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['ID_Evento']."\">".$row['NombreEvento']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==10){//Resultado de gestion, dependiendo del evento
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$Cons="Select ID_ResultadoGestion, ResultadoGestion From uvw_tbl_Cartera_RelacionConceptos Where ID_Evento='".$_GET['id']."' Order by ResultadoGestion";
			$SQL=sqlsrv_query($conexion,$Cons);
			if($SQL){
				echo "<option value=''>Seleccione...</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['ID_ResultadoGestion']."\">".$row['ResultadoGestion']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==11){//Id de servicio (ItemCode), dependiendo del cliente y la sucursal
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			if(isset($_GET['suc'])){
				$Suc="'".$_GET['suc']."'";
			}else{
				$Suc="NULL";
			}
			
			$sw_Clt=0;//Tipo cliente
			$sw_Std=0;//Tipo Estandar
			
			$ParamCons=array(
				"'".$_GET['id']."'",
				$Suc,
				"'1'"
			);
			$SQL=EjecutarSP('sp_ConsultarArticulosLlamadas',$ParamCons);
			if($SQL){
				echo "<option value=''>Seleccione...</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					if(($row['IdTipoListaArticulo']==1)&&($sw_Clt==0)){
						echo "<optgroup label='Cliente'></optgroup>";
						$sw_Clt=1;
					}elseif(($row['IdTipoListaArticulo']==2)&&($sw_Std==0)){
						echo "<optgroup label='Genericas'></optgroup>";
						$sw_Std=1;
					}
					echo "<option value=\"".$row['ItemCode']."\">".$row['ItemCode']." - ".$row['ItemName']." (".$row['DireccionSucursal'].")</option>";
					//echo "<option value=\"".$row['ItemCode']."\">".$row['ItemCode']." - ".$row['ItemName']." (SERV: ".substr($row['Servicios'],0,20)." - ÁREA: ".substr($row['Areas'],0,20).")</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==12){//Un select dinamico, se le pasa el nombre de la vista a consultar y muestra la lista de los campos
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$Cons="EXEC sp_columns '".$_GET['id']."'";
			$SQL=sqlsrv_query($conexion,$Cons);
			if($SQL){
				echo "<option value=''>Seleccione...</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['COLUMN_NAME']."\">".$row['COLUMN_NAME']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==13){//Barrio dependiendo de la ciudad
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$Cons="Select * From uvw_Sap_tbl_Barrios Where IdMunicipio='".$_GET['id']."' Order by DeBarrio";
			$SQL=sqlsrv_query($conexion,$Cons);
			if($SQL){
				echo "<option value=''>Seleccione...</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['IdBarrio']."\">".$row['DeBarrio']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==14){//Destino, dependiendo del Tipo de Gestion
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$SQL_TipoDest=Seleccionar('uvw_tbl_Cartera_TipoGestion','TipoDestino',"ID_TipoGestion='".$_GET['id']."'");
			$row_TipoDest=sqlsrv_fetch_array($SQL_TipoDest);
			if($row_TipoDest['TipoDestino']==1){
				$SQL=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".base64_decode($_GET['clt'])."'");
				if($SQL){
					echo "<option value=''>Seleccione...</option>";
					while($row=sqlsrv_fetch_array($SQL)){
						if($row['Posicion']!=""){
							$Posicion=" (".$row['Posicion'].")";
						}else{
							$Posicion="";
						}
						echo "<option value=\"".$row['Telefono1']."\">".$row['ID_Contacto'].$Posicion." - ".$row['Telefono1']."</option>";
					}
				}else{
					echo "<option value=''>Seleccione...</option>";
				}
			}else{
				$SQL=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".base64_decode($_GET['clt'])."'");
				if($SQL){
					echo "<option value=''>Seleccione...</option>";
					while($row=sqlsrv_fetch_array($SQL)){
						echo "<option value=\"".LSiqmlObs($row['Direccion'])."\">".$row['NombreSucursal']." (".LSiqmlObs($row['Direccion']).")</option>";
					}
				}else{
					echo "<option value=''>Seleccione...</option>";
				}
			}			
		}
	}
	elseif($_GET['type']==15){//Tipo de problema llamada, dependiendo del tipo de llamada
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$Cons="Select * From uvw_Sap_tbl_TipoProblemasLlamadas Where IdTipoProblemaLlamada IN (Select IdTipoProblemaLlamada From tbl_Rel_TipoLL_TipoProblemaLL Where IdTipoLlamada='".$_GET['id']."') Order by DeTipoProblemaLlamada";
			$SQL=sqlsrv_query($conexion,$Cons);
			if($SQL){
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['IdTipoProblemaLlamada']."\">".$row['DeTipoProblemaLlamada']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==16){//Areas de clientes, dependiendo del cliente. COPLA
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>(Ninguna)</option>";
		}else{
			$SQL=Seleccionar('uvw_tbl_Areas_Clientes','*',"IdCodigoCliente='".$_GET['id']."'",'DeArea');
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				echo "<option value=''>(Ninguna)</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['IdArea']."\">".$row['DeArea']."</option>";
				}
			}else{
				echo "<option value=''>(Ninguna)</option>";

			}
		}
	}
	elseif($_GET['type']==17){//Turnos del técnico. Dependiendo del técnico
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$SQL=EjecutarSP('sp_ConsultarTurnoTecnico',$_GET['id']);
			$Num=sqlsrv_num_rows($SQL);
			if($Num>0){
				//echo "<option value=''>Seleccione...</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['CodigoTurno']."\">".$row['NombreTurno']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==18){//Atributo dependiendo del fabricante. DIALNET (MySQL)
		require_once("includes/conexion_mysql.php");
		
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$SQL=Seleccionar('dictionary','DISTINCT Attribute',"Vendor='".$_GET['id']."'",'Attribute','',3);
			$Num=mysqli_num_rows($SQL);
			if($Num>0){
				echo "<option value=''>Seleccione...</option>";
				while($row=mysqli_fetch_array($SQL)){
					echo "<option value=\"".$row['Attribute']."\">".$row['Attribute']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
		mysqli_close($conexion_mysql);
	}
	elseif($_GET['type']==19){//Sucursal dependiendo de la serie del documento
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$Cons="Select IdSucursal, DeSucursal From uvw_tbl_SeriesSucursalesAlmacenes Where IdSeries='".$_GET['id']."' Group by IdSucursal, DeSucursal Order by DeSucursal";
			$SQL=sqlsrv_query($conexion,$Cons,array(),array( "Scrollable" => 'Buffered' ));
			$Num=sqlsrv_num_rows($SQL);
			if(isset($_GET['todos'])&&($_GET['todos']==1)){
				$Todos=1;
			}else{
				$Todos=0;
			}
			if($Num){
				if($Todos==1){
					echo "<option value=''>(Todos)</option>";
				}
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['IdSucursal']."\">".$row['DeSucursal']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";

			}
		}
	}
	elseif($_GET['type']==20){//Almacen dependiendo de la sucursal, la serie y el tipo de documento
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$twhs=1;//Identificar si es Almacen de origen o destino. 1: Origen (default). 2: Destino
			if(isset($_GET['twhs'])){
				$twhs=$_GET['twhs'];
			}
			//$SQL=Seleccionar('uvw_Sap_tbl_SeriesSucursalesAlmacenes','*',"IdSucursal='".$_GET['id']."' and IdTipo='".$_GET['tdoc']."'");
			//$Num=sqlsrv_num_rows($SQL);			
			
			if($twhs==1){
				$SQL=SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes','WhsCode, WhsName',"IdSeries='".$_GET['serie']."' and IdSucursal='".$_GET['id']."' and IdTipoDocumento='".$_GET['tdoc']."'","WhsCode, WhsName",'WhsName');
				$Num=sqlsrv_num_rows($SQL);
				if($Num){
					while($row=sqlsrv_fetch_array($SQL)){
						echo "<option value=\"".$row['WhsCode']."\">".$row['WhsName']."</option>";
					}
				}else{
					echo "<option value=''>Seleccione...</option>";
				}
			}else{
				$selected = "";
				$towhscode = $_GET['towhscode'] ?? "";

				$SQL=SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes','ToWhsCode, ToWhsName',"IdSeries='".$_GET['serie']."' and IdSucursal='".$_GET['id']."' and IdTipoDocumento='".$_GET['tdoc']."' and ToWhsCode <> ''","ToWhsCode, ToWhsName",'ToWhsName');
				$Num=sqlsrv_num_rows($SQL);
				if($Num){
					while($row=sqlsrv_fetch_array($SQL)){
						$selected = ($towhscode == $row['ToWhsCode']) ? "selected":"";
						echo "<option value=\"".$row['ToWhsCode']."\" $selected>".$row['ToWhsName']."</option>";
					}
				}else{
					echo "<option value=''>Seleccione...</option>";
				}
			}
		}
	}
	elseif($_GET['type']==21){//Lista de documentos de marketing, dependiendo del DocType
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$Parametros=array(
				"'".$_GET['doctype']."'",
				"'".$_GET['id']."'"
			);
			/*if(isset($_GET['entry'])&&$_GET['entry']!=""){
				array_push($Parametros, 2);
			}*/
			$SQL=EjecutarSP('sp_ConsultarDocMarketing',$Parametros);
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				echo "<option value=''>Seleccione...</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['DocEntry']."__".$row['DocNum']."\">".$row['DocNum']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";

			}
		}
	}
	elseif($_GET['type']==22){//Cargar empleados o lista de destinatarios en actividades (Asignado a)
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>(Sin asignar)</option>";
		}else{
			if($_GET['id']==2 || $_GET['id']==171){//Empleado
				$SQL=Seleccionar('uvw_Sap_tbl_Empleados','*',"IdUsuarioSAP=0",'NombreEmpleado');
				$Num=sqlsrv_num_rows($SQL);
				if($Num){
					echo "<option value=''>(Sin asignar)</option>";
					while($row=sqlsrv_fetch_array($SQL)){
						echo "<option value=\"".$row['ID_Empleado']."\">".$row['NombreEmpleado']."</option>";
					}
				}else{
					echo "<option value=''>(Sin asignar)</option>";

				}
			}elseif($_GET['id']==3){//Lista de destinatarios
				$SQL=Seleccionar('uvw_Sap_tbl_ListaDestinatarios','*',"Activa='Y'",'DeListaAsignado');
				$Num=sqlsrv_num_rows($SQL);
				if($Num){
					echo "<option value=''>(Sin asignar)</option>";
					while($row=sqlsrv_fetch_array($SQL)){
						echo "<option value=\"".$row['IdListaAsignado']."\">".$row['DeListaAsignado']."</option>";
					}
				}else{
					echo "<option value=''>(Sin asignar)</option>";

				}
			}elseif($_GET['id']==1 || $_GET['id']==12){//Usuarios de SAP
				$SQL=Seleccionar('uvw_Sap_tbl_Empleados','*',"IdUsuarioSAP <> 0",'NombreEmpleado');
				$Num=sqlsrv_num_rows($SQL);
				if($Num){
					echo "<option value=''>(Sin asignar)</option>";
					while($row=sqlsrv_fetch_array($SQL)){
						echo "<option value=\"".$row['IdUsuarioSAP']."\">".$row['NombreEmpleado']."</option>";
					}
				}else{
					echo "<option value=''>(Sin asignar)</option>";

				}
			}
			
		}
	}
	elseif($_GET['type']==23){//Municipio dependiendo del departamento (cuando valor y el label son el mismo)
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>(TODOS)</option>";
		}else{		
			$SQL=Seleccionar('uvw_Sap_tbl_Clientes','DISTINCT Municipio',"Departamento='".$_GET['id']."'",'Municipio');
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				echo "<option value=''>(TODOS)</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['Municipio']."\">".$row['Municipio']."</option>";
				}
			}else{
				echo "<option value=''>(TODOS)</option>";
			}
		}
	}
	elseif($_GET['type']==24){//Codigo postal dependiendo del departamento
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$Cons="Select * From uvw_Sap_tbl_CodigosPostales Where DeDepartamento='".$_GET['id']."' Order by ID_CodigoPostal";
			$SQL=sqlsrv_query($conexion,$Cons);
			if($SQL){
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['ID_CodigoPostal']."\">".$row['DeCodigoPostal']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==25){//Serie de documento dependiendo del tipo de documento
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{		
			$SQL=Seleccionar('uvw_Sap_tbl_SeriesDocumentos','IdSeries, DeSeries',"IdTipoDocumento='".$_GET['id']."'",'DeSeries');
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				echo "<option value=''>Seleccione...</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['IdSeries']."\">".$row['DeSeries']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==26){//Serie dependiendo de la sucursal
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$tAccion='2';
			if(isset($_GET['taccion'])&&$_GET['taccion']!=""){
				$tAccion=$_GET['taccion'];
			}
			$Parametros=array(
				"'".$_GET['id']."'",
				"'".$_GET['tdoc']."'",
				"'".$_SESSION['CodUser']."'",
				"'".$tAccion."'"
				
			);
			$SQL=EjecutarSP('sp_ConsultarSeriesSucursales',$Parametros);
			$Num=sqlsrv_num_rows($SQL);
			if(isset($_GET['todos'])&&($_GET['todos']==1)){
				$Todos=1;
			}else{
				$Todos=0;
			}
			if($Num){
				if($Todos==1){
					echo "<option value=''>(Todos)</option>";
				}
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['IdSeries']."\">".$row['DeSeries']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";

			}
		}
	}
	elseif($_GET['type']==27){//Empleados (recursos) dependiendo de la sucursal (CentroCostos)
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>(Todos)</option>";
		}else{		
			$SQL=Seleccionar('uvw_Sap_tbl_Recursos','DISTINCT IdCargo, DeCargo',"CentroCosto3='".$_GET['id']."'");
			$Num=sqlsrv_num_rows($SQL);
			if(isset($_GET['todos'])&&($_GET['todos']==1)){
				$Todos=1;
			}else{
				$Todos=0;
			}
			if($Num){
				if($Todos==1){
					echo "<option value=''>(Todos)</option>";
				}
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<optgroup label='".$row['DeCargo']."'></optgroup>";
					$SQL_Rec=Seleccionar('uvw_Sap_tbl_Recursos','ID_Empleado, NombreEmpleado',"CentroCosto3='".$_GET['id']."' and IdCargo='".$row['IdCargo']."'",'NombreEmpleado');
					while($row_Rec=sqlsrv_fetch_array($SQL_Rec)){
						echo "<option value=\"".$row_Rec['ID_Empleado']."\">".$row_Rec['NombreEmpleado']."</option>";
					}			
				}
			}else{
				echo "<option value=''>(Todos)</option>";
			}
		}
	}
	elseif($_GET['type']==28){//Numeros de series dependiendo del Articulo de la llamada
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$SQL=Seleccionar('uvw_Sap_tbl_TarjetasEquipos','*',"ItemCode='".$_GET['id']."' and ItemInventario='Y'",'SerialFabricante');
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				echo "<option value=''>Seleccione...</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['SerialInterno']."\">SN Fabricante: ".$row['SerialFabricante']." - Núm. Serie: ".$row['SerialInterno']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";

			}
		}
	}
	elseif($_GET['type']==29){//Contratos de servicio dependiendo del cliente
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$SQL=Seleccionar('uvw_Sap_tbl_Contratos','*',"CodigoCliente='".$_GET['id']."' and IdEstadoContrato='A'",'ID_Contrato');
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				echo "<option value=''>Seleccione...</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['ID_Contrato']."\">".$row['ID_Contrato']." - ".$row['DE_Contrato']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";

			}
		}
	}
	elseif($_GET['type']==30){//Proyecto dependiendo del cliente (COPLA Y RG)
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$SQL=Seleccionar('uvw_Sap_tbl_Proyectos','*',"IdProyecto='".$_GET['id']."'",'DeProyecto');
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['IdProyecto']."\">".$row['DeProyecto']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";

			}
		}
	}
	elseif($_GET['type']==31){//Mostrar la lista de parametros de asistente, dependiendo de la serie de la OT
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "No hay resultados";
		}else{
			$SQL=Seleccionar('tbl_Parametros_Asistentes','*',"TipoObjeto='".$_GET['obj']."'");
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				while($row=sqlsrv_fetch_array($SQL)){
					$SQL_Data=Seleccionar('tbl_Parametros_Asistentes_Detalle','*',"ID_Campo='".$row['ID_Campo']."' and TipoObjeto='".$_GET['obj']."' and IdSerie='".$_GET['id']."'");
					$row_Data=sqlsrv_fetch_array($SQL_Data);
					echo "<div class='form-group'>
						<label class='col-lg-2 control-label'>".$row['LabelCampo']."<br><span class='text-muted'>".$row['NombreCampo']."</span></label>
						<div class='col-lg-3'>
							<input name='".$row['NombreCampo']."' type='text' class='form-control' id='".$row['NombreCampo']."' maxlength='100' autocomplete='off' value='".$row_Data['Valor']."' onChange='ActualizarDatos(\"".$_GET['id']."\");'>
							<input name='".$row['ID_Campo']."' type='hidden' id='".$row['ID_Campo']."' value='".$row['ID_Campo']."'>
						</div>
					</div>";
				}
				echo "<input type='hidden' name='edit_".$_GET['id']."' id='edit_".$_GET['id']."' value='0' />";
			}else{
				echo "No hay resultados";

			}
		}
	}
	elseif($_GET['type']==32){//Lista de materiales del cronograma dependiendo del cliente y el año
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>(Ninguno)</option>";
		}else{
			//$SQL=SeleccionarGroupBy('uvw_tbl_ProgramacionOrdenesServicio','IdArticuloLMT, DeArticuloLMT',"IdCliente='".$_GET['id']."' and Periodo='".$_GET['periodo']."'","IdArticuloLMT, DeArticuloLMT",'IdArticuloLMT');
			$sw_Clt=0;//Tipo cliente
			$sw_Std=0;//Tipo Estandar
			
			$SQL=Seleccionar("uvw_Sap_tbl_ArticulosLlamadas","*","(CodigoCliente='".$_GET['id']."' and Estado='Y') OR IdTipoListaArticulo='2'","IdTipoListaArticulo, ItemCode");
			
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				echo "<option value=''>(Ninguno)</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					if(($row['IdTipoListaArticulo']==1)&&($sw_Clt==0)){
						echo "<optgroup label='Cliente'></optgroup>";
						$sw_Clt=1;
					}elseif(($row['IdTipoListaArticulo']==2)&&($sw_Std==0)){
						echo "<optgroup label='Genericas'></optgroup>";
						$sw_Std=1;
					}
					echo "<option value=\"".$row['ItemCode']."\">".$row['ItemCode']." - ".$row['ItemName']."</option>";
				}
			}else{
				echo "<option value=''>(Ninguno)</option>";

			}
		}
	}
	elseif($_GET['type']==33){//Almacen dependiendo de la sucursal y el tipo de documento
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>(Todos)</option>";
		}else{
			$twhs=1;//Identificar si es Almacen de origen o destino. 1: Origen (default). 2: Destino
			if(isset($_GET['twhs'])){
				$twhs=$_GET['twhs'];
			}
			
			if(isset($_GET['todos'])&&($_GET['todos']==1)){
				$Todos=1;
			}else{
				$Todos=0;
			}
			
			if(isset($_GET['select'])&&($_GET['select']==1)){
				$Select=1;
			}else{
				$Select=0;
			}
			
			if($twhs==1){
				$SQL=SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes','WhsCode, WhsName',"IdSucursal='".$_GET['id']."' and IdTipoDocumento='".$_GET['tdoc']."'","WhsCode, WhsName",'WhsName');
				$Num=sqlsrv_num_rows($SQL);				
				if($Num){
					if($Todos==1){
						echo "<option value=''>(Todos)</option>";
					}
					if($Select==1){
						echo "<option value=''>Seleccione...</option>";
					}
					while($row=sqlsrv_fetch_array($SQL)){
						echo "<option value=\"".$row['WhsCode']."\">".$row['WhsName']."</option>";
					}
				}else{
					if($Todos==1){
						echo "<option value=''>(Todos)</option>";
					}
					if($Select==1){
						echo "<option value=''>Seleccione...</option>";
					}

				}
			}else{
				$SQL=SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes','ToWhsCode, ToWhsName',"IdSucursal='".$_GET['id']."' and IdTipoDocumento='".$_GET['tdoc']."'","ToWhsCode, ToWhsName",'ToWhsName');
				$Num=sqlsrv_num_rows($SQL);
				if($Num){
					if($Todos==1){
						echo "<option value=''>(Todos)</option>";
					}
					if($Select==1){
						echo "<option value=''>Seleccione...</option>";
					}
					while($row=sqlsrv_fetch_array($SQL)){
						echo "<option value=\"".$row['ToWhsCode']."\">".$row['ToWhsName']."</option>";
					}
				}else{
					if($Todos==1){
						echo "<option value=''>(Todos)</option>";
					}
					if($Select==1){
						echo "<option value=''>Seleccione...</option>";
					}

				}
			}			
		}
	}
	elseif($_GET['type']==34){//Marca dependiendo de Und Negocio (Reindustria)
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$SQL=Seleccionar('tbl_TDU_Marca','*',"Id_UndNegocio='".$_GET['id']."'");//Colocar estado Abierto
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				echo "<option value=''>Seleccione...</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['Id_Marca']."\">".$row['De_Marca']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==35){//Linea dependiendo de marca (Reindustria)
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>Seleccione...</option>";
		}else{
			$SQL=Seleccionar('tbl_TDU_Linea','*',"Id_Marca='".$_GET['id']."'");//Colocar estado Abierto
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				echo "<option value=''>Seleccione...</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['Id_Linea']."\">".$row['De_Linea']."</option>";
				}
			}else{
				echo "<option value=''>Seleccione...</option>";
			}
		}
	}
	elseif($_GET['type']==36){//Bodega dependiendo de la sucursal (Puerto)
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>(Todos)</option>";
		}else{
			$Todos=(isset($_GET['todos'])) ? $_GET['todos'] : 1;
			$Selec=(isset($_GET['selec'])) ? $_GET['selec'] : 0;
			
			$SQL=Seleccionar('tbl_BodegasPuerto','*',"codigo_cliente='".$_GET['clt']."' and linea_sucursal='".$_GET['id']."'");
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				if($Todos==1&&$Selec==0){
					echo "<option value=''>(Todos)</option>";
				}elseif($Selec==1){
					echo "<option value=''>Seleccione...</option>";
				}
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['id_bodega_puerto']."\">".$row['bodega_puerto']."</option>";
				}
			}else{
				echo "<option value=''>(Todos)</option>";
			}
		}
	}
	elseif($_GET['type']==37){//Lista de cargos de recursos (empleados) dependiendo de la sucursal (centro de costos)
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>(TODOS)</option>";
		}else{		
			$SQL=Seleccionar('uvw_Sap_tbl_Recursos','DISTINCT IdCargo, DeCargo',"CentroCosto3='".$_GET['id']."'");
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				echo "<option value=''>(TODOS)</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['IdCargo']."\">".$row['DeCargo']."</option>";
				}
			}else{
				echo "<option value=''>(TODOS)</option>";
			}
		}
	}
	elseif($_GET['type']==38){//Empleados (recursos) dependiendo de la sucursal (CentroCostos) y el cargo (grupo)
		if(!isset($_GET['id'])||($_GET['id']=="")){
			echo "<option value=''>(NINGUNO)</option>";
		}else{		
			$SQL=Seleccionar('uvw_Sap_tbl_Recursos','ID_Empleado, NombreEmpleado',"CentroCosto3='".$_GET['id']."' and IdCargo='".$_GET['grupo']."'",'NombreEmpleado');
			$Num=sqlsrv_num_rows($SQL);
			if($Num){
				echo "<option value=''>(TODOS)</option>";
				while($row=sqlsrv_fetch_array($SQL)){
					echo "<option value=\"".$row['ID_Empleado']."\">".$row['NombreEmpleado']."</option>";
				}
			}else{
				echo "<option value=''>(NINGUNO)</option>";
			}
		}
	}
	sqlsrv_close($conexion);
}
?>