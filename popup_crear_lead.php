<?php 
require_once("includes/conexion.php");

$msg_error="";//Mensaje del error

if(isset($_POST['swError'])&&($_POST['swError']!="")){//Para saber si ha ocurrido un error.
	$sw_error=$_POST['swError'];
}else{
	$sw_error=0;
}

if(isset($_POST['P'])&&($_POST['P']!="")){
	try{
		//*** Carpeta temporal de anexos ***
		$i=0;//Archivos
		$RutaAttachSAP=ObtenerDirAttach();
		$dir=CrearObtenerDirTemp();
		$dir_new=CrearObtenerDirAnx("socios_negocios");
	
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
		
		$Anexos=array();
		
		//Mover los anexos a la carpeta de archivos de SAP
		$j=0;
		while($j<$CantFiles){
			$Archivo=FormatoNombreAnexo($DocFiles[$j]);
			$NuevoNombre=$Archivo[0];
			$OnlyName=$Archivo[1];
			$Ext=$Archivo[2];

			if(file_exists($dir_new)){
				copy($dir.$DocFiles[$j],$dir_new.$NuevoNombre);
				copy($dir_new.$NuevoNombre,$RutaAttachSAP[0].$NuevoNombre);

				//Anexos
				array_push($Anexos,array(
					"id_anexo" => $j,
					"tipo_documento" => 2,
					"id_documento" => null,
					"archivo" => $OnlyName,
					"ext_archivo" => $Ext,
					"metodo" => 1,
					"fecha" => FormatoFechaToSAP(date('Y-m-d')),
					"id_usuario" => intval($_SESSION['CodUser']),
					"comentarios" => ""
				));
			}
			$j++;
		}
		
		//Dividir el nombre en Nombre y Segundo nombre
		$Nombres = explode(" ", $_POST['PNNombres']);
		
		//Consultar nombres
		$SQL_Ciudad=Seleccionar('uvw_tbl_Municipios','Ciudad',"Codigo='".$_POST['City']."'");
		$row_Ciudad=sqlsrv_fetch_array($SQL_Ciudad);
		
		$SQL_Barrio=Seleccionar('uvw_Sap_tbl_Barrios','DeBarrio',"IdBarrio='".$_POST['Block']."'");
		$row_Barrio=sqlsrv_fetch_array($SQL_Barrio);
		
		$exp=explode('-',$_POST['LicTradNum']);
		$IdNum = reset($exp);//Obtengo la primera parte del array
		
		if($_POST['CardType']=="L"){//Si es Lead
			$IdSN="LD-".$IdNum;
		}else{
			$IdSN="CN-".$IdNum;
		}
		
		$Cabecera=array(
			"id_series" => null,
			"id_socio_negocio" => $IdSN,
			"socio_negocio" => $_POST['CardName'],
			"numero_documento" => $_POST['LicTradNum'],
			"id_tipo_socio" => $_POST['CardType'],
			"id_tipo_entidad" => $_POST['TipoEntidad'],
			"nombres" => $_POST['PNNombres'],
			"primer_apellido" => $_POST['PNApellido1'],
			"segundo_pellido" => $_POST['PNApellido2'],
			"nombre_comercial" => $_POST['CardName'],
			"telefono1" => $_POST['TelefonoCliente'],
			"telefono2" => null,
			"celular" => $_POST['CelularCliente'],
			"correo_electronico" => $_POST['CorreoCliente'],
			"id_grupo_sn" => intval($_POST['GroupCode']),
			"id_condicion_pago" => intval($_POST['GroupNum']),
			"id_industria" => intval($_POST['Industria']),
			"id_territorio" => intval(ObtenerValorDefecto(2,"IdTerritorio")),
			"id_proyecto" => (isset($_POST['CodProyecto'])) ? $_POST['CodProyecto'] : "",
  			"proyecto" => (isset($_POST['NomProyecto'])) ? $_POST['NomProyecto'] : "",
			"id_regimen_tributario" => ($_POST['TipoEntidad']==1) ? ObtenerValorDefecto(2,"IdRegimenTributario") : ObtenerValorDefecto(2,"IdRegimenTributarioJuridica"),
			"id_tipo_documento" => $_POST['TipoDocumento'],
			"id_nacionalidad" => ObtenerValorDefecto(2,"IdNacionalidad"),
			"id_tipo_extranjero" => ObtenerValorDefecto(2,"IdTipoExtranjero"),
			"id_regimen_fiscal" => ObtenerValorDefecto(2,"IdRegimenFiscal"),
			"id_responsabilidad_fiscal" => ObtenerValorDefecto(2,"IdResponsabilidadFiscal"),
			"id_medio_pago" => $_POST['MedioPago'],
			"id_municipio" => ObtenerValorDefecto(2,"IdMunicipio"),
			"id_empleado_ventas" => intval($_SESSION['CodigoEmpVentas']),
			"id_doc_portal" => "",
			"usuario_creacion" => $_SESSION['User'],
			"usuario_actualizacion" => $_SESSION['User'],
			"contactos" => array(
				array(
					"id_consecutivo" => null,
					"id_contacto" => null,
					"contacto" => $_POST['CardName'],
					"id_socio_negocio" => $IdSN,
					"primer_nombre" => isset($Nombres[0])&&($Nombres[0]!="") ? $Nombres[0] : $_POST['CardName'],
					"segundo_nombre" => isset($Nombres[1])&&($Nombres[1]!="") ? $Nombres[1] : "",
					"apellidos" => $_POST['PNApellido1']." ".$_POST['PNApellido2'],
					"telefono" => $_POST['TelefonoCliente'],
					"celular" => $_POST['CelularCliente'],
					"id_actividad_economica" => ObtenerValorDefecto(2,"ActividadEconomica"),
					"id_representante_legal" => ObtenerValorDefecto(2,"RepLegal"),
					"id_identificacion" => $_POST['TipoDocumento'],
					"identificacion" => $_POST['LicTradNum'],
					"email" => $_POST['CorreoCliente'],
					"cargo" => "NO APLICA",
					"estado" => "Y",
					"metodo" => 1
				)				
			),
			"direcciones" => array(
				array(
					"id_consecutivo" => null,
					"id_direccion" => PermitirFuncion(509) ? LSiqmlObs($row_Ciudad['Ciudad']) : ObtenerVariable("DirFacturacion"),
					"direccion" => $_POST['Street'],
					"id_socio_negocio" => $IdSN,
					"id_tipo_direccion" => "B",
					"id_departamento" => substr($_POST['City'],0,2),
					"departamento" => $_POST['County'],
					"id_ciudad" => $_POST['City'],
					"ciudad" => $row_Ciudad['Ciudad'],
					"id_barrio" => $_POST['Block'],
					"barrio" => $row_Barrio['DeBarrio'],
					"id_estrato" => "",
					"id_codigo_postal" => ObtenerValorDefecto(2,"CodigoPostal"),
					"CDU_nombre_contacto" => isset($Nombres[0])&&($Nombres[0]!="") ? $Nombres[0] : $_POST['CardName'],
					"CDU_cargo_contacto" => "NO APLICA",
					"CDU_telefono_contacto" => $_POST['TelefonoCliente'],
					"CDU_correo_contacto" => $_POST['CorreoCliente'],
					"dir_mm" => "Y",
					"metodo" => 1
				),
				array(
					"id_consecutivo" => null,
					"id_direccion" => PermitirFuncion(509) ? LSiqmlObs($row_Ciudad['Ciudad']) : ObtenerVariable("DirDestino"),
					"direccion" => $_POST['Street'],
					"id_socio_negocio" => $IdSN,
					"id_tipo_direccion" => "S",
					"id_departamento" => substr($_POST['City'],0,2),
					"departamento" => $_POST['County'],
					"id_ciudad" => $_POST['City'],
					"ciudad" => $row_Ciudad['Ciudad'],
					"id_barrio" => $_POST['Block'],
					"barrio" => $row_Barrio['DeBarrio'],
					"id_estrato" => "",
					"id_codigo_postal" => ObtenerValorDefecto(2,"CodigoPostal"),
					"CDU_nombre_contacto" => isset($Nombres[0])&&($Nombres[0]!="") ? $Nombres[0] : $_POST['CardName'],
					"CDU_cargo_contacto" => "NO APLICA",
					"CDU_telefono_contacto" => $_POST['TelefonoCliente'],
					"CDU_correo_contacto" => $_POST['CorreoCliente'],
					"dir_mm" => "N",
					"metodo" => 1
				)				
			),
			"documentos_Anexos" => $Anexos,
			"metodo" => 1,
			"prop1" => (isset($_POST['Prop1'])&&($_POST['Prop1']=="Y")) ? $_POST['Prop1'] : "N",
			"prop2" => (isset($_POST['Prop2'])&&($_POST['Prop2']=="Y")) ? $_POST['Prop2'] : "N",
			"prop3" => (isset($_POST['Prop3'])&&($_POST['Prop3']=="Y")) ? $_POST['Prop3'] : "N",
			"prop4" => (isset($_POST['Prop4'])&&($_POST['Prop4']=="Y")) ? $_POST['Prop4'] : "N",
			"prop5" => (isset($_POST['Prop5'])&&($_POST['Prop5']=="Y")) ? $_POST['Prop5'] : "N",
			"prop6" => (isset($_POST['Prop6'])&&($_POST['Prop6']=="Y")) ? $_POST['Prop6'] : "N",
			"prop7" => (isset($_POST['Prop7'])&&($_POST['Prop7']=="Y")) ? $_POST['Prop7'] : "N",
			"prop8" => (isset($_POST['Prop8'])&&($_POST['Prop8']=="Y")) ? $_POST['Prop8'] : "N",
			"prop9" => (isset($_POST['Prop9'])&&($_POST['Prop9']=="Y")) ? $_POST['Prop9'] : "N",
			"crear_proyecto" => (isset($_POST['CrearProyecto'])&&($_POST['CrearProyecto']=="Y")) ? $_POST['CrearProyecto'] : "N",
		);
		
//		$Cabecera_json=json_encode($Cabecera);
//		echo $Cabecera_json;
		
		$Metodo="SociosNegocios/Asistente";
		$Resultado=EnviarWebServiceSAP($Metodo,$Cabecera,true,true);

		if($Resultado->Success==0){
			$sw_error=1;
			$msg_error=$Resultado->Mensaje;
		}else{
			header('Location:popup_crear_lead.php?a='.base64_encode("OK_SNAdd"));
		}
	}catch (Exception $e){
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
	
	
}

if($sw_error==1){
	
	//Ciudad
	$SQL_Ciudad=Seleccionar('uvw_Sap_tbl_SN_Municipio','*',"DeDepartamento='".$_POST['County']."'","DE_Municipio");
	
	//Barrio
	$SQL_Barrio=Seleccionar('uvw_Sap_tbl_Barrios','*',"IdMunicipio='".$_POST['City']."'","DeBarrio");

}

//Tipos de SN
$SQL_TipoSN=Seleccionar('uvw_tbl_TiposSN','*',(ObtenerValorDefecto(2,"IdTipoSN")!="") ? "CardType IN ('".str_replace(",","','",ObtenerValorDefecto(2,"IdTipoSN"))."')" : "");

//Tipo entidad
$SQL_TipoEntidad=Seleccionar('tbl_TipoEntidadSN','*','','NombreEntidad');

//Tipo documento
$SQL_TipoDoc=Seleccionar('tbl_TipoDocumentoSN','*','','TipoDocumento');

//Grupos de Clientes
$SQL_GruposClientes=Seleccionar('uvw_Sap_tbl_GruposClientes','*','','GroupName');

//Condiciones de pago
$SQL_CondicionPago=Seleccionar('uvw_Sap_tbl_CondicionPago','*','','NombreCondicion');

//Industrias
$SQL_Industria=Seleccionar('uvw_Sap_tbl_Clientes_Industrias','*','','DeIndustria');

//Medios de pago
$SQL_MedioPago=Seleccionar('tbl_MedioPagoSN','*','','DeMedioPago');

//Departamentos
$SQL_Dptos=Seleccionar('uvw_Sap_tbl_SN_Municipio','Distinct DeDepartamento','','DeDepartamento');

//Propiedades
$SQL_Prop=Seleccionar('uvw_Sap_tbl_SN_ListaPropiedades','*');
?>
<!doctype html>
<html>
<head>
<?php include_once("includes/cabecera.php"); ?>
<title>Crear nuevo cliente/prospecto | <?php echo NOMBRE_PORTAL;?></title>
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_SNAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El cliente ha sido creado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Lo sentimos!',
                text: '".LSiqmlObs($msg_error)."',
                icon: 'warning'
            });
		});		
		</script>";
}
?>
<style>
	/*.ibox-content{
		padding: 0px !important;	
	}*/
	body{
		background-color: #ffffff;
	}
	/*.table > tbody > tr > td{
		padding: 1px !important;
		vertical-align: middle;
	}*/
</style>
</head>

<body>
	<div class="row wrapper border-bottom white-bg page-heading">
		<div class="col-sm-8">
			<h2>Crear nuevo cliente/prospecto</h2>
			<ol class="breadcrumb">
				<li>
					Socios de negocios
				</li>
				<li class="active">
					<strong>Crear nuevo cliente/prospecto</strong>
				</li>
			</ol>
		</div>
	</div>
	<div class="ibox-content">
		<?php include("includes/spinner.php"); ?>
		<div class="row"> 
			<div class="col-lg-12">
				<form action="popup_crear_lead.php" method="post" class="form-horizontal" id="FrmCrear">
					<div class="tabs-container">
						<ul class="nav nav-tabs">
							<li class="active"><a data-toggle="tab" href="#tabcl-1"><i class="fa fa-info-circle"></i> Información general</a></li>
							<li><a data-toggle="tab" href="#tabcl-2"><i class="fa fa-list"></i> Propiedades</a></li>
							<li><a data-toggle="tab" href="#tabcl-3"><i class="fa fa-paperclip"></i> Anexos</a></li>
						</ul>
						<div class="tab-content">
							<div id="tabcl-1" class="tab-pane active">
								<div class="form-group">
									<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Información general</h3></label>
								</div>
								<div class="form-group">
									<label class="col-lg-1 control-label">Tipo socio de negocio</label>
									<div class="col-lg-3">
										<select name="CardType" class="form-control" id="CardType" required>
										<?php
											while($row_TipoSN=sqlsrv_fetch_array($SQL_TipoSN)){?>
												<option value="<?php echo $row_TipoSN['CardType'];?>" <?php if(($sw_error==1)&&(strcmp($row_TipoSN['CardType'],$_POST['CardType'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoSN['DE_CardType'];?></option>
										<?php }?>
										</select>
									</div>
									<div class="col-lg-4">
										<div id="spinner1" style="visibility: hidden;" class="sk-spinner sk-spinner-wave">
											<div class="sk-rect1"></div>
											<div class="sk-rect2"></div>
											<div class="sk-rect3"></div>
											<div class="sk-rect4"></div>
											<div class="sk-rect5"></div>
										</div>
									</div>
									<div id="Validar" class="col-lg-4"></div>								
								</div>
								<div class="form-group">
									<label class="col-lg-1 control-label">Tipo entidad <span class="text-danger">*</span></label>
									<div class="col-lg-3">
										<select name="TipoEntidad" class="form-control" id="TipoEntidad" required>
											<option value="">Seleccione...</option>
										<?php
											while($row_TipoEntidad=sqlsrv_fetch_array($SQL_TipoEntidad)){?>
												<option value="<?php echo $row_TipoEntidad['ID_TipoEntidad'];?>" <?php if(($sw_error==1)&&(strcmp($row_TipoEntidad['ID_TipoEntidad'],$_POST['TipoEntidad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoEntidad['NombreEntidad'];?></option>
										<?php }?>
										</select>
									</div>
									<label class="col-lg-1 control-label">Tipo documento <span class="text-danger">*</span></label>
									<div class="col-lg-3">
										<select name="TipoDocumento" class="form-control" id="TipoDocumento" required>
											<option value="">Seleccione...</option>
										<?php
											while($row_TipoDoc=sqlsrv_fetch_array($SQL_TipoDoc)){?>
												<option value="<?php echo $row_TipoDoc['ID_TipoDocumento'];?>" <?php if(($sw_error==1)&&(strcmp($row_TipoDoc['ID_TipoDocumento'],$_POST['TipoDocumento'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoDoc['TipoDocumento'];?></option>
										<?php }?>
										</select>
									</div>
									<label class="col-lg-1 control-label">Número documento <span class="text-danger">*</span></label>
									<div class="col-lg-3">
										<input name="LicTradNum" type="text" required class="form-control" id="LicTradNum" value="<?php if($sw_error==1){ echo $_POST['LicTradNum'];}?>" maxlength="15" onKeyPress="return justNumbers(event,this.value);" onChange="ValidarSN(this.value);" autocomplete="off">
									</div>
								</div>
								<div class="form-group">
									<label class="col-lg-1 control-label">Nombres</label>
									<div class="col-lg-3">
										<input name="PNNombres" type="text" class="form-control" id="PNNombres" <?php if(($sw_error==0)||($sw_error==1&&$_POST['TipoEntidad']==2)){ echo "readonly='readonly'";}?> value="<?php if($sw_error==1){ echo $_POST['PNNombres'];}?>" onChange="CrearNombre();" autocomplete="off">
									</div>	
									<label class="col-lg-1 control-label">Primer apellido</label>
									<div class="col-lg-3">
										<input name="PNApellido1" type="text" class="form-control" id="PNApellido1" <?php if(($sw_error==0)||($sw_error==1&&$_POST['TipoEntidad']==2)){ echo "readonly='readonly'";}?> value="<?php if($sw_error==1){ echo $_POST['PNApellido1'];}?>" onChange="CrearNombre();" autocomplete="off">
									</div>
									<label class="col-lg-1 control-label">Segundo apellido</label>
									<div class="col-lg-3">
										<input name="PNApellido2" type="text" class="form-control" id="PNApellido2" <?php if(($sw_error==0)||($sw_error==1&&$_POST['TipoEntidad']==2)){ echo "readonly='readonly'";}?> value="<?php if($sw_error==1){ echo $_POST['PNApellido2'];}?>" onChange="CrearNombre();" autocomplete="off">
									</div>
								</div>
								<div class="form-group">
									<label class="col-lg-1 control-label">Nombre cliente/Razón social <span class="text-danger">*</span></label>
									<div class="col-lg-3">
										<input type="text" class="form-control" name="CardName" id="CardName" required value="<?php if($sw_error==1){ echo $_POST['CardName'];}?>" autocomplete="off" <?php if($sw_error==1&&$_POST['TipoEntidad']==1){ echo "readonly='readonly'";}?>>
									</div>
									<label class="col-lg-1 control-label">Correo eléctronico <span class="text-danger">*</span></label>
									<div class="col-lg-3">
										<input type="text" class="form-control" name="CorreoCliente" id="CorreoCliente" required value="<?php if($sw_error==1){ echo $_POST['CorreoCliente'];}?>" autocomplete="off">
									</div>
									<label class="col-lg-1 control-label">Grupo <span class="text-danger">*</span></label>
									<div class="col-lg-3">
										<select name="GroupCode" class="form-control select2" id="GroupCode" required>
											<option value="">Seleccione...</option>
										<?php
											while($row_GruposClientes=sqlsrv_fetch_array($SQL_GruposClientes)){?>
												<option value="<?php echo $row_GruposClientes['GroupCode'];?>" <?php if(($sw_error==1)&&(strcmp($row_GruposClientes['GroupCode'],$_POST['GroupCode'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_GruposClientes['GroupName'];?></option>
										<?php }?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-lg-1 control-label">Teléfono <span class="text-danger">*</span></label>
									<div class="col-lg-3">
										<input type="text" class="form-control" name="TelefonoCliente" id="TelefonoCliente" required value="<?php if($sw_error==1){ echo $_POST['TelefonoCliente'];}?>" autocomplete="off">
									</div>
									<label class="col-lg-1 control-label">Celular <span class="text-danger">*</span></label>
									<div class="col-lg-3">
										<input type="text" class="form-control" name="CelularCliente" id="CelularCliente" required value="<?php if($sw_error==1){ echo $_POST['CelularCliente'];}?>" autocomplete="off">
									</div>	
									<label class="col-lg-1 control-label">Dirección <span class="text-danger">*</span></label>
									<div class="col-lg-3">
										<input name="Street" type="text" required class="form-control" id="Street" maxlength="100" autocomplete="off" value="<?php if($sw_error==1){ echo $_POST['Street'];}?>">
									</div>
								</div>
								<div class="form-group">						
									<label class="col-lg-1 control-label">Departamento <span class="text-danger">*</span></label>
									<div class="col-lg-3">
										<select name="County" id="County" class="form-control" required onChange="BuscarCiudad();">
											<option value="">Seleccione...</option>
										<?php
											while($row_Dptos=sqlsrv_fetch_array($SQL_Dptos)){?>
												<option value="<?php echo $row_Dptos['DeDepartamento'];?>" <?php if(($sw_error==1)&&(strcmp($row_Dptos['DeDepartamento'],$_POST['County'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Dptos['DeDepartamento'];?></option>
										<?php }?>
										</select>
									</div>
									<label class="col-lg-1 control-label">Ciudad <span class="text-danger">*</span></label>
									<div class="col-lg-3">
										<select name="City" id="City" onChange="BuscarBarrio();" class="form-control" required>
											<option value="">Seleccione...</option>
											<?php
											if($sw_error==1){
												while($row_Ciudad=sqlsrv_fetch_array($SQL_Ciudad)){?>
												<option value="<?php echo $row_Ciudad['ID_Municipio'];?>" <?php if(($sw_error==1)&&(strcmp($row_Ciudad['ID_Municipio'],$_POST['City'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Ciudad['DE_Municipio'];?></option>								
										<?php }
											}?>
										</select>
									</div>
									<label class="col-lg-1 control-label">Barrio <span class="text-danger">*</span></label>
									<div class="col-lg-3">
										<select name="Block" id="Block" class="form-control select2" required>
											<option value="">Seleccione...</option>
											<?php
											if($sw_error==1){
												while($row_Barrio=sqlsrv_fetch_array($SQL_Barrio)){?>
												<option value="<?php echo $row_Barrio['IdBarrio'];?>" <?php if(($sw_error==1)&&(strcmp($row_Barrio['IdBarrio'],$_POST['Block'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Barrio['DeBarrio'];?></option>								
										<?php }
											}?>
										</select>
									</div>
								</div>
								<div class="form-group">						
									<label class="col-lg-1 control-label">Proyecto</label>
									<div class="col-lg-3">
										<label class="checkbox-inline i-checks"><input name="CrearProyecto" id="CrearProyecto" type="checkbox" value="Y"> Crear el proyecto para este cliente</label>
									</div>
									<div id="dvPrj" style="display: none;">
										<label class="col-lg-1 control-label">Código proyecto <span class="text-danger">*</span></label>
										<div class="col-lg-3">
											<input type="text" class="form-control" name="CodProyecto" id="CodProyecto" onChange="ValidarPrj();" required value="<?php if($sw_error==1){ echo $_POST['CodProyecto'];}?>" autocomplete="off">
										</div>	
										<label class="col-lg-1 control-label">Nombre proyecto <span class="text-danger">*</span></label>
										<div class="col-lg-3">
											<input name="NomProyecto" type="text" required class="form-control" id="NomProyecto" maxlength="100" autocomplete="off" value="<?php if($sw_error==1){ echo $_POST['NomProyecto'];}?>">
										</div>
									</div>									
								</div>
								<div class="form-group">
									<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-briefcase"></i> Información comercial</h3></label>
								</div>
								<div class="form-group">
									<label class="col-lg-1 control-label">Industria <span class="text-danger">*</span></label>
									<div class="col-lg-3">
										<select name="Industria" class="form-control" id="Industria" required>
										<?php
											while($row_Industria=sqlsrv_fetch_array($SQL_Industria)){?>
												<option value="<?php echo $row_Industria['IdIndustria'];?>" <?php if(($sw_error==1)&&(strcmp($row_Industria['IdIndustria'],$_POST['Industria'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Industria['DeIndustria'];?></option>
										<?php }?>
										</select>
									</div>
									<label class="col-lg-1 control-label">Condición de pago <span class="text-danger">*</span></label>
									<div class="col-lg-3">
										<select name="GroupNum" class="form-control" id="GroupNum" required>
											<option value="">Seleccione...</option>
										<?php
											while($row_CondicionPago=sqlsrv_fetch_array($SQL_CondicionPago)){?>
												<option value="<?php echo $row_CondicionPago['IdCondicionPago'];?>" <?php if(($sw_error==1)&&(strcmp($row_CondicionPago['IdCondicionPago'],$_POST['GroupNum'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_CondicionPago['NombreCondicion'];?></option>
										<?php }?>
										</select>
									</div>
									<label class="col-lg-1 control-label">Medio de pago <span class="text-danger">*</span></label>
									<div class="col-lg-3">
										<select name="MedioPago" class="form-control select2" id="MedioPago" required>
										<?php
											while($row_MedioPago=sqlsrv_fetch_array($SQL_MedioPago)){?>
												<option value="<?php echo $row_MedioPago['IdMedioPago'];?>" <?php if(($sw_error==1)&&(strcmp($row_MedioPago['IdMedioPago'],$_POST['MedioPago'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_MedioPago['DeMedioPago'];?></option>
										<?php }?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<div class="col-lg-9">
										<button class="btn btn-primary" type="submit" id="Crear"><i class="fa fa-check"></i> Crear Prospecto</button>
										<input type="hidden" id="P" name="P" value="38" />
										<input type="hidden" id="swError" name="swError" value="<?php echo $sw_error;?>" />
									</div>
								</div>	
							</div>
							<div id="tabcl-2" class="tab-pane">
								<div class="form-group">
									<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Propiedades del cliente</h3></label>
								</div>
								<div class="form-group">
									<div class="col-lg-6">
										<div class="table-responsive">
											<table class="table table-striped table-bordered table-hover" >
											<thead>
											<tr>
												<th>#</th>
												<th>Nombre de la propiedad</th>
												<th>Seleccionar</th>
											</tr>
											</thead>
											<tbody>
											<?php 
												while($row=sqlsrv_fetch_array($SQL_Prop)){ ?>
													<tr>
														<td><?php echo $row['CodigoGrupo'];?></td>
														<td><?php echo $row['NombreGrupo'];?></td>
														<td>
															<div class="checkbox checkbox-success">
																<input type="checkbox" class="chkProp" id="Prop<?php echo $row['CodigoGrupo'];?>" name="Prop<?php echo $row['CodigoGrupo'];?>" value="Y" aria-label="Single checkbox One"><label></label>
															</div>
														</td>
													</tr>
											<?php }?>
											</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
							</form>
							<div id="tabcl-3" class="tab-pane">
								<div class="panel-body">
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
			</div>
		</div>
	</div>

<script>
 $(document).ready(function(){
	 $("#FrmCrear").validate({
		submitHandler: function(form){
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
		}
	});
	
	$(".select2").select2();
	$('.i-checks').iCheck({
		 checkboxClass: 'icheckbox_square-green',
		 radioClass: 'iradio_square-green',
	  });
	 
	 $("#TipoEntidad").change(function(){
			var TipoEntidad=document.getElementById('TipoEntidad').value;
			var Nombres=document.getElementById('PNNombres');
			var Apellido1=document.getElementById('PNApellido1');
			var Apellido2=document.getElementById('PNApellido2');
			var CardName=document.getElementById('CardName');
			
			if(TipoEntidad==1){//Natural
				//Quitar
				Nombres.removeAttribute("readonly");
				Apellido1.removeAttribute("readonly");
				Apellido2.removeAttribute("readonly");
				
				//Poner
				Nombres.setAttribute("required","required");
				Apellido1.setAttribute("required","required");
				CardName.setAttribute("readonly","readonly");
				<?php if($sw_error==0){?>
				CardName.value="";
				<?php }?>
			}else{//Juridica
				//Quitar
				CardName.removeAttribute("readonly");
				Nombres.removeAttribute("required");
				Apellido1.removeAttribute("required");
				
				//Poner
				CardName.value="";
				Nombres.value="";
				Apellido1.value="";
				Apellido2.value="";
				Nombres.setAttribute("readonly","readonly");
				Apellido1.setAttribute("readonly","readonly");
				Apellido2.setAttribute("readonly","readonly");
			}
		});
	 <?php if($sw_error==0){?>
		$('#TipoEntidad').trigger('change');
	 <?php }?>
	 
	 
		$('#CrearProyecto').on('ifChecked', function(event){
			document.getElementById('dvPrj').style.display='block';
			CrearProyecto();
		});
		$('#CrearProyecto').on('ifUnchecked', function(event){
			document.getElementById('dvPrj').style.display='none';
		});	
 });
</script>
<script>
function ValidarSN(ID){
	if(isNaN(ID)){
		document.getElementById('Crear').disabled=true;
		Swal.fire({
			title: '¡Advertencia!',
			text: 'La cedula del cliente no es un valor numerico. Por favor valide.',
			icon: 'warning'
		});
	}else{
		var spinner=document.getElementById('spinner1');
		spinner.style.visibility='visible';
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=16&id="+ID,
			success: function(response){
				document.getElementById('Validar').innerHTML=response;
				spinner.style.visibility='hidden';
				if(response!=""){
					document.getElementById('Crear').disabled=true;
				}else{
					document.getElementById('Crear').disabled=false;
				}
			}
		});
	}	
}

function ValidarPrj(){
	let codigo=document.getElementById("CodProyecto").value
	
	$.ajax({
		url:"ajx_buscar_datos_json.php",
		data:{type:42,codigo:codigo},
		dataType:'json',
		success: function(data){
			if((data.IdProyecto!="")&&(data.IdProyecto!==null)){
				Swal.fire({
					title: '¡Advertencia!',
					text: 'El código del proyecto ya existe. Por favor verifique.',
					icon: 'warning'
				});
			}
		}
	});
}

function CrearNombre(){
	var TipoEntidad=document.getElementById("TipoEntidad");
	var Nombre=document.getElementById("PNNombres");
	var PrimerApellido=document.getElementById("PNApellido1");
	var SegundoApellido=document.getElementById("PNApellido2");
	var CardName=document.getElementById("CardName");
	
	if(TipoEntidad.value==1){//Natural
		if(Nombre.value!=""&&PrimerApellido.value!=""){
			CardName.value=PrimerApellido.value + ' ' + SegundoApellido.value + ' ' + Nombre.value;
		}else{
			CardName.value="";
		}
	}
}

function BuscarCiudad(){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		url: "ajx_cbo_select.php?type=8&id="+document.getElementById('County').value,
		success: function(response){
			$('#City').html(response).fadeIn();
			$('#City').trigger('change');
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
}

function BuscarBarrio(){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		url: "ajx_cbo_select.php?type=13&id="+document.getElementById('City').value,
		success: function(response){
			$('#Block').html(response).fadeIn();
			$('#Block').trigger('change');
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
}
	
function CrearProyecto(){
	let lictrad=document.getElementById("LicTradNum").value.split('-')
	
	if(document.getElementById("CardType").value=="L"){
		document.getElementById("CodProyecto").value='LD-'+lictrad[0]
	}else{
		document.getElementById("CodProyecto").value='CN-'+lictrad[0]
	}
	
	ValidarPrj()
	
	document.getElementById("NomProyecto").value=document.getElementById("CardName").value
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
</body>
</html>
<?php 
	sqlsrv_close( $conexion );?>