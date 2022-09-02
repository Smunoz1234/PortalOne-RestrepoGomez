<?php require_once("includes/conexion.php");
PermitirAcceso(1102);

$msg_error="";//Mensaje del error
$sw_ext=0;//Sw que permite saber si la ventana esta abierta en modo pop-up. Si es así, no cargo el menú ni el menú superior.

if(isset($_GET['id'])&&($_GET['id']!="")){
	$IdOportunidad=base64_decode($_GET['id']);
}
	
if(isset($_GET['ext'])&&($_GET['ext']==1)){
	$sw_ext=1;//Se está abriendo como pop-up
}

if(isset($_POST['swError'])&&($_POST['swError']!="")){//Para saber si ha ocurrido un error.
	$sw_error=$_POST['swError'];
}else{
	$sw_error=0;
}

if(isset($_GET['tl'])&&($_GET['tl']!="")){//0 Si se está creando. 1 Se se está editando.
	$edit=$_GET['tl'];
}elseif(isset($_POST['tl'])&&($_POST['tl']!="")){
	$edit=$_POST['tl'];
}else{
	$edit=0;
}

if(isset($_POST['P'])&&($_POST['P']!="")){
	try{
		
		#Comprobar si el cliente ya esta guardado en la tabla de SN. Si no está guardado se ejecuta el INSERT con el Metodo de actualizar
		//$SQL_Dir=Seleccionar('tbl_SociosNegocios','CardCode',"CardCode='".$_POST['CardCode']."'");
		//$row_Dir=sqlsrv_fetch_array($SQL_Dir);
		
		$Metodo=2;//Actualizar en el web services
		$Type=2;//Ejecutar actualizar en el SP
		
		if($_POST['tl']==0){//Creando SN
			$Metodo=1;
		}
		
		if($_POST['ID_SN']==""){//Insertando en la tabla
			$Type=1;
		}
		
		$Param=array(
			"'".$_POST['NumeroOportunidad']."'",
			"'".$_POST['NombreOportunidad']."'",
			"'".$_POST['TipoOpr']."'",
			"'".$_POST['Estado']."'",
			"'".$_POST['CierreTipo']."'",
			"'".$_POST['FechaInicio']."'",
			"'".$_POST['FechaCierrePrev']."'",
			"'".$_POST['FechaCierre']."'",
			"'".$_POST['CardCode']."'",
			"'".$_POST['ContactoCliente']."'",
			"'".$_POST['Propietario']."'",
			"'".$_POST['Territorio']."'",
			"'".$_POST['ImporteFact']."'",
			"'".$_POST['EmpleadoVentas']."'",
			"'".$_POST['NivelInteres']."'",
			"'".$_POST['Proyecto']."'",
			"'".$_POST['FuenteInfo']."'",
			"'".$_POST['RamoInd']."'",
			"'".$_POST['PrctOpor']."'",
			"'".LSiqmlValorDecimal($_POST['MontoPotencial'])."'",
			"'".LSiqmlValorDecimal($_POST['MontoPonderado'])."'",
			"'".LSiqmlValorDecimal($_POST['PrcGananciaBruta'])."'",
			"'".LSiqmlValorDecimal($_POST['GananciaBruta'])."'",
			"'".$_POST['CanalSN']."'",
			"'".$_POST['ContactoCanalSN']."'",
			"'".$_POST['TipoDocResumen']."'",
			"'".$_POST['DocNumResumen']."'",
			"'".$_POST['DocEntryResumen']."'",
			"'".$_POST['Comentarios']."'",
			"'".$_POST['IdAnexo']."'",
			$Metodo,
			"'".$_SESSION['CodUser']."'",
			$Type
		);
		$SQL_Opr=EjecutarSP('sp_tbl_Oportunidades',$Param,$_POST['P']);
		if($SQL_Opr){
			$row_NewID=sqlsrv_fetch_array($SQL_Opr);
			$ID=$row_NewID[0];
			
			$json=json_decode($_POST['dataJSON']);
			
			foreach ($json as $Detalle){
				if($Detalle->id_linea!=""){
					//Insertar el registro en la BD
					$ParamDet=array(
						"'".$ID."'",
						"'".$Detalle->id_oportunidad."'",
						"'".$Detalle->id_linea."'",
						"'".$Detalle->fecha_inicio."'",
						"'".$Detalle->fecha_cierre."'",
						"'".$Detalle->id_etapa."'",
						"'".$Detalle->prct_etapa."'",
						"'".LSiqmlValorDecimal($Detalle->monto_potencial)."'",
						"'".LSiqmlValorDecimal($Detalle->importe_ponderado)."'",
						"'".$Detalle->id_empleado."'",
						"'".$Detalle->comentarios."'",
						"'".$Detalle->actividad."'",
						"'".$Detalle->tipo_documento."'",
						"'".$Detalle->docentry_documento."'",
						"'".$Detalle->docnum_documento."'",
						"'".$Detalle->id_propietario."'",
						"'".$Detalle->id_estado."'",
						"'".$Detalle->metodo."'",
						"'".$_SESSION['CodUser']."'",
						"1"
					);

					$SQL_Det=EjecutarSP('sp_tbl_OportunidadesDetalle',$ParamDet,$_POST['P']);

					if(!$SQL_Det){
						$sw_error=1;
						$msg_error="Ha ocurrido un error al insertar el detalle de las etapas";
					}
				}
			}
			
			if($sw_error==0){
//				$Msg=($_POST['tl']==1) ? "OK_OPEdit" : "OK_OPAdd";
				if($_POST['tl']==1){
					$Msg="OK_OPEdit";
					header('Location:oportunidad.php?id='.base64_encode($_POST['NumeroOportunidad']).'&tl=1&a='.base64_encode($Msg));
				}else{
					$Msg="OK_OPAdd";
					header('Location:oportunidad.php?a='.base64_encode($Msg));
				}
				
			}
			
			
			
			
		}else{
			$sw_error=1;
			$msg_error="Ha ocurrido un error al crear la oportunidad";
		}
	}catch (Exception $e){
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
	
}

if($edit==1){

	//Oportunidad
	$SQL=Seleccionar("uvw_Sap_tbl_Oportunidades","*","ID_Oportunidad='".$IdOportunidad."'");
	$row=sql_fetch_array($SQL);
	
	//Contactos
	$SQL_ContactoCliente=Seleccionar("uvw_Sap_tbl_ClienteContactos","*","CodigoCliente='".$row['IdClienteOportunidad']."'");
	
	//Detalle de etapas
	$SQL_Detalle=Seleccionar("uvw_Sap_tbl_OportunidadesDetalle","*","ID_Oportunidad='".$IdOportunidad."'");
	
	//Anexos
	$SQL_Anexo=Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos','*',"AbsEntry='".$row['IdAnexo']."'");
	
	if($row['CodigoCanalSN']!=""){
		//Contactos
		$SQL_ContactoCanalSN=Seleccionar("uvw_Sap_tbl_ClienteContactos","*","CodigoCliente='".$row['CodigoCanalSN']."'");
	}
	
	
}

if($sw_error==1){	

	//Cliente
	$SQL=Seleccionar("uvw_tbl_SociosNegocios","*","[CardCode]='".$IdOportunidad."'");
	$row=sql_fetch_array($SQL);
	
	//Direcciones
	$SQL_Dir=Seleccionar("uvw_tbl_SociosNegocios_Direcciones","*","[CodigoCliente]='".$row['CodigoCliente']."'");
	$Num_Dir=sql_num_rows($SQL_Dir);
	
	//Contactos
	$SQL_Cont=Seleccionar("uvw_tbl_SociosNegocios_Contactos","*","[CodigoCliente]='".$row['CodigoCliente']."'");
	$Num_Cont=sql_num_rows($SQL_Cont);
	
	//Municipio MM
	$SQL_MunMM=Seleccionar('uvw_tbl_Municipios','*',"Codigo='".$row['U_HBT_MunMed']."'");
	$row_MunMM=sql_fetch_array($SQL_MunMM);
	
	//Facturas pendientes
	$SQL_FactPend=Seleccionar('uvw_Sap_tbl_FacturasPendientes','TOP 10 *',"ID_CodigoCliente='".$row['CodigoCliente']."'","FechaContabilizacion","DESC");
		
	//ID de servicios
	$SQL_IDServicio=Seleccionar('uvw_Sap_tbl_Articulos','*',"[CodigoCliente]='".$row['CodigoCliente']."'",'[ItemCode]');
		
	//Historico de gestiones
	$SQL_HistGestion=Seleccionar('uvw_tbl_Cartera_Gestion','TOP 10 *',"CardCode='".$row['CodigoCliente']."'",'FechaRegistro');
}

//Estado documento
$SQL_EstadoOpr=Seleccionar('uvw_tbl_EstadoOportunidad','*');

//Industrias
$SQL_Industria=Seleccionar('uvw_Sap_tbl_Clientes_Industrias','*','','DeIndustria');

//Territorio
$SQL_Territorio=Seleccionar('uvw_Sap_tbl_Territorios','*','','DeTerritorio');

//Nivel de interes
$SQL_NivelInteres=Seleccionar('uvw_Sap_tbl_OportunidadesNivelTipoInteres','*');

//Proyectos
$SQL_Proyecto=Seleccionar('uvw_Sap_tbl_Proyectos','*','','DeProyecto');

//Fuente de información
$SQL_FuenteInfo=Seleccionar('uvw_Sap_tbl_OportunidadesFuenteInformacion','*','','DE_FuenteInfo');

//Ramo industria
$SQL_RamoInd=Seleccionar('uvw_Sap_tbl_OportunidadesRamoIndustria','*','','DescripcionRamoIndustria');

//Etapa
$SQL_Etapa=Seleccionar('uvw_Sap_tbl_OportunidadesEtapas','*','','ID_Etapa');

//Tipos de documentos de marketing
$SQL_TipoDocMark=Seleccionar('tbl_ObjetosSAP','*');

//Empleados de ventas
$SQL_EmpVentas=Seleccionar('uvw_Sap_tbl_EmpleadosVentas','*');

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Oportunidad de venta | <?php echo NOMBRE_PORTAL;?></title>
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_OPAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La oportunidad ha sido creada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_OPEdit"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La oportunidad ha sido actualizada exitosamente.',
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
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.select2-container{ width: 100% !important; }
	.swal2-container {
	  	z-index: 9000;
	}
</style>
<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otro		
		$("#CardCode").change(function(){
			var carcode=document.getElementById('CardCode').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=7&id="+carcode,
				success: function(response){
					$('#CondicionPago').html(response).fadeIn();
				}
			});
		});
		
		
	});
</script>
<script>
function CargarEtapa(
	id_oportunidad,
	id_linea,
	fecha_inicio,
	fecha_cierre,
	id_etapa,
	nombre_etapa,
	prct_etapa,
	monto_potencial,
	importe_ponderado,
	id_empleado,
	nombre_empleado,
	comentarios,
	actividad,
	tipo_documento,
	nombre_documento,
	docentry_documento,
	docnum_documento,
	id_propietario,
	nombre_propietario,
	id_estado,
	nombre_estado)
{
		
	let datosEtapas = window.sessionStorage.getItem('OPR<?php echo ($edit==1) ? $row['ID_Oportunidad'] : "";?>')
	let json=[]
		
	if(datosEtapas){
		json = JSON.parse(datosEtapas)
	}else{
		window.sessionStorage.setItem('OPR<?php echo ($edit==1) ? $row['ID_Oportunidad'] : "";?>','')
	}

	json.push({
		id_oportunidad: id_oportunidad,
		id_linea: id_linea,
		fecha_inicio: fecha_inicio,
		fecha_cierre: fecha_cierre,
		id_etapa: id_etapa,
		nombre_etapa: nombre_etapa,
		prct_etapa: prct_etapa,
		monto_potencial: monto_potencial,
		importe_ponderado: importe_ponderado,
		id_empleado: id_empleado,
		nombre_empleado: nombre_empleado,
		comentarios: comentarios,
		actividad: actividad,
		tipo_documento: tipo_documento,
		nombre_documento: nombre_documento,
		docentry_documento: docentry_documento,
		docnum_documento: docnum_documento,
		id_propietario: id_propietario,
		nombre_propietario: nombre_propietario,
		id_estado: id_estado,
		nombre_estado: nombre_estado,
		metodo: 0
	})
	window.sessionStorage.setItem('OPR<?php echo ($edit==1) ? $row['ID_Oportunidad'] : "";?>',JSON.stringify(json))

}

function CalcularCierre(){
	
	let cantDias=document.getElementById("CierrePlan");
	let fechaInicio=document.getElementById("FechaInicio");
	let fechaPrevCierre=document.getElementById("FechaCierrePrev");
	
	fechaPrevCierre.value=sumarDiasFecha(fechaInicio.value,cantDias.value)
	
}
	
function CalcularDiasCierre() {
	
	let cantDias=document.getElementById("CierrePlan");
	let fechaInicio=document.getElementById("FechaInicio");
	let fechaPrevCierre=document.getElementById("FechaCierrePrev");
	
	cantDias.value=dateDaysDiff(fechaInicio.value,fechaPrevCierre.value)
	
}
	
function sumarDiasFecha(pFecha, pDias) {
	//la fecha
	let TuFecha = new Date(pFecha);

	//dias a sumar
	let dias = parseInt(pDias);

	//nueva fecha sumada
	TuFecha.setDate(TuFecha.getDate() + (dias+1));
	
	let year = TuFecha.getFullYear();
	let mes = String((TuFecha.getMonth() + 1))
	let dia = String(TuFecha.getDate())
	
	//formato de salida para la fecha
	let res = year + '-' + (((mes.length)===1) ? "0"+mes : mes) + '-' + (((dia.length)===1) ? "0"+dia : dia);
	
	return res;	
}
	
function dateDaysDiff(date1, date2) {
	dt1 = new Date(date1);
	dt2 = new Date(date2);
	return Math.floor((Date.UTC(dt2.getFullYear(), dt2.getMonth(), dt2.getDate()) - Date.UTC(dt1.getFullYear(), dt1.getMonth(), dt1.getDate()) ) /(1000 * 60 * 60 * 24));
}
</script>
<!-- InstanceEndEditable -->
</head>

<body <?php if($sw_ext==1){echo "class='mini-navbar'"; }?>>

<div id="wrapper">

    <?php if($sw_ext!=1){include("includes/menu.php"); }?>

    <div id="page-wrapper" class="gray-bg">
        <?php if($sw_ext!=1){include("includes/menu_superior.php"); }?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Oportunidad de venta</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">CRM</a>
                        </li>
                        <li class="active">
                            <strong>Oportunidad de venta</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			 <div class="modal inmodal fade" id="myModal" tabindex="1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content" id="ContenidoModal">
						
					</div>
				</div>
			 </div>
			 <form action="oportunidad.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="FrmOp">
			  <?php 
				$_GET['obj']="97";
				include_once('md_frm_campos_adicionales.php');
			  ?>
			 <div class="row">
				<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-plus-square"></i> Acciones</h3></label>
						</div>
						<div class="form-group">
							<div class="col-lg-8">
								<?php 
								if($edit==1){
									if(PermitirFuncion(1102)){?>
										<button class="btn btn-warning" type="submit" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar oportunidad</button>
								<?php }
								}elseif(PermitirFuncion(1101)){?>
										<button class="btn btn-primary" type="submit" id="Crear"><i class="fa fa-check"></i> Crear oportunidad</button>
								<?php }?>
								<button class="btn btn-success" type="button" id="DatoAdicionales" onClick="VerCamposAdi();"><i class="fa fa-list"></i> Ver campos adicionales</button> 
								<?php 
									if(isset($_GET['return'])){
										$return=base64_decode($_GET['pag'])."?".$_GET['return'];
									}elseif(isset($_POST['return'])){
										$return=base64_decode($_POST['return']);
									}else{
										$return="oportunidad.php?".$_SERVER['QUERY_STRING'];
									}
									$return=QuitarParametrosURL($return,array("a"));
								?>
								<a href="<?php echo $return;?>" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
							</div>
						</div>
						<input type="hidden" id="P" name="P" value="97" />
						<input type="hidden" id="ID" name="ID" value="<?php if(isset($row['IdOportunidadPortal'])){echo base64_encode($row['IdOportunidadPortal']); }?>" />
						<input type="hidden" id="tl" name="tl" value="<?php echo $edit;?>" />
						<input type="hidden" id="IdAnexo" name="IdAnexo" value="<?php if(($edit==1)||($sw_error==1)){echo $row['IdAnexo'];}?>" />
						<input type="hidden" id="dataJSON" name="dataJSON" value="" />
					</div>
				</div>
			 </div>
			 <br>
			 <div class="row">
			 	<div class="col-lg-12">
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-user"></i> Información de cliente</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Cliente <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<input name="CardCode" type="hidden" id="CardCode" value="<?php if(($edit==1)||($sw_error==1)){echo $row['IdClienteOportunidad'];}?>">

								<input name="CardName" type="text" required="required" class="form-control" id="CardName" placeholder="Digite para buscar..." value="<?php if(($edit==1)||($sw_error==1)){echo $row['DeClienteOportunidad'];}?>" <?php if(($edit==1)&&($row['IdEstadoOportunidad']=='C')){echo "readonly";}?>>
							</div>
							<label class="col-lg-1 control-label">Contacto <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="ContactoCliente" class="form-control" id="ContactoCliente" required <?php if(($edit==1)&&($row['IdEstadoOportunidad']=='C')){echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								<?php
									if($edit==1||$sw_error==1){
										while($row_ContactoCliente=sqlsrv_fetch_array($SQL_ContactoCliente)){?>
											<option value="<?php echo $row_ContactoCliente['CodigoContacto'];?>" <?php if((isset($row['IdContactoOportunidad']))&&(strcmp($row_ContactoCliente['CodigoContacto'],$row['IdContactoOportunidad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ContactoCliente['ID_Contacto'];?></option>
								<?php 	}
									}?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Propietario <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<input name="Propietario" id="Propietario" type="hidden" value="<?php if($edit==1||$sw_error==1){echo $row['IdPropietario'];}?>" />
								<input name="NombrePropietario" type="text" class="form-control" id="NombrePropietario" value="<?php if($edit==1||$sw_error==1){echo $row['NombrePropietario'];}?>" readonly="readonly">
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Importe de factura total</label>
							<div class="col-lg-3">
								<input name="ImporteFact" type="text" class="form-control" id="ImporteFact" value="<?php if($edit==1||$sw_error==1){echo number_format($row['ImporteFacturaTotal'],2);}?>" readonly="readonly">
							</div>
							<label class="col-lg-1 control-label">Industria</label>
							<div class="col-lg-3">
								<select name="Industria" class="form-control" id="Industria">
									<option value="">(Ninguna)</option>
								<?php
									while($row_Industria=sqlsrv_fetch_array($SQL_Industria)){?>
										<option value="<?php echo $row_Industria['IdIndustria'];?>" <?php if((isset($row['IdIndustria']))&&(strcmp($row_Industria['IdIndustria'],$row['IdIndustria'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Industria['DeIndustria'];?></option>
								<?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Territorio</label>
							<div class="col-lg-3">
								<select name="Territorio" class="form-control" id="Territorio">
									<option value="">(Ninguno)</option>
								<?php
									while($row_Territorio=sqlsrv_fetch_array($SQL_Territorio)){?>
										<option value="<?php echo $row_Territorio['IdTerritorio'];?>" <?php if((isset($row['IdTerritorio']))&&(strcmp($row_Territorio['IdTerritorio'],$row['IdTerritorio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Territorio['DeTerritorio'];?></option>
								<?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-suitcase"></i> Información de la oportunidad</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Nombre de la oportunidad <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<input autocomplete="off" name="NombreOportunidad" type="text" required="required" class="form-control" id="NombreOportunidad" maxlength="150" value="<?php if($edit==1||$sw_error==1){echo $row['NombreOportunidad'];}?>">
							</div>
							<label class="col-lg-1 control-label">Número de oportunidad</label>
							<div class="col-lg-3">
								<input autocomplete="off" name="NumeroOportunidad" type="text" class="form-control" id="NumeroOportunidad" maxlength="10" value="<?php if($edit==1||$sw_error==1){echo $row['ID_Oportunidad'];}?>" readonly>
							</div>
							<label class="col-lg-1 control-label">Estado <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<input autocomplete="off" name="EstadoOportunidad" type="text" class="form-control" id="EstadoOportunidad" maxlength="10" value="<?php if($edit==1||$sw_error==1){echo $row['DeEstadoOportunidad'];}?>" readonly>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Tipo de oportunidad <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="TipoOpr" class="form-control" id="TipoOpr" <?php if(($edit==1)&&($row['IdEstadoOportunidad']=='C')){echo "disabled='disabled'";}?>>
									<option value="R" <?php if(($edit==1||$sw_error==1)&&($row['IdTipoOportunidad']=="R")){echo "selected=\"selected\"";}?>>Ventas</option>
									<option value="P" <?php if(($edit==1||$sw_error==1)&&($row['IdTipoOportunidad']=="P")){echo "selected=\"selected\"";}?>>Compras</option>
								</select>
							</div>
							<label class="col-lg-1 control-label">Fecha de inicio <span class="text-danger">*</span></label>
							<div class="col-lg-3 input-group date">
								 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaInicio" id="FechaInicio" type="text" onChange="CalcularDiasCierre();" required="required" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['FechaInicio']->format('Y-m-d');}?>" <?php if(($edit==1)&&($row['IdEstadoOportunidad']=='C')){echo "readonly";}?>>
							</div>
							<label class="col-lg-1 control-label">Fecha de cierre</label>
							<div class="col-lg-3 input-group date">
								 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaCierre" id="FechaCierre" type="text" class="form-control" value="<?php if($edit==1||$sw_error==1){echo ($row['FechaCierre']!="") ? $row['FechaCierre']->format('Y-m-d') : "";}?>" <?php if(($edit==1)&&($row['IdEstadoOportunidad']=='C')){echo "readonly";}?>>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Empleado de ventas <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="EmpleadoVentas" class="form-control select2" id="EmpleadoVentas" required>
										<option value="">Seleccione...</option>
								  <?php while($row_EmpVentas=sqlsrv_fetch_array($SQL_EmpVentas)){?>
										<option value="<?php echo $row_EmpVentas['ID_EmpVentas'];?>" <?php if(($edit==1)&&(isset($row['IdEmpleadoOportunidad']))&&(strcmp($row_EmpVentas['ID_EmpVentas'],$row['IdEmpleadoOportunidad'])==0)){ echo "selected=\"selected\"";}elseif(($edit==0)&&(isset($_SESSION['CodigoSAP']))&&(strcmp($row_EmpVentas['ID_EmpVentas'],$_SESSION['CodigoSAP'])==0)){echo "selected=\"selected\"";}?>><?php echo $row_EmpVentas['DE_EmpVentas'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">% de cierre <span class="text-danger">*</span></label>
							<div class="col-lg-7">
								<input type="hidden" name="PrctOpor" id="PrctOpor" value="<?php if($edit==1||$sw_error==1){echo $row['PorcentajeOportunidad'];}?>" />
								<div class="progress">
									<div class="progress-bar progress-bar-success" role="progressbar" style="width: <?php if(($edit==1)||($sw_error==1)){echo number_format($row['PorcentajeOportunidad'],0);}else{echo "0";}?>%;" aria-valuenow="<?php if(($edit==1)||($sw_error==1)){echo number_format($row['PorcentajeOportunidad'],0);}else{echo "0";}?>" aria-valuemin="0" aria-valuemax="100"><?php if(($edit==1)||($sw_error==1)){echo number_format($row['PorcentajeOportunidad'],0);}else{echo "0";}?>%</div>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Detalles de la oportunidad</h3></label>
						</div>
						 <div class="tabs-container">
								<ul class="nav nav-tabs">
									<li class="active"><a data-toggle="tab" href="#tabOpr-1"><i class="fa fa-shopping-cart"></i> Potencial</a></li>
									<li><a data-toggle="tab" href="#tabOpr-2"><i class="fa fa-book"></i> General</a></li>
									<li><a data-toggle="tab" href="#tabOpr-3"><i class="fa fa-flag-checkered"></i> Etapas</a></li>
									<?php /*?><li><a data-toggle="tab" href="#tabOpr-4"><i class="fa fa-group"></i> Socios de negocios</a></li><?php */?>
									<li><a data-toggle="tab" href="#tabOpr-5"><i class="fa fa-trophy"></i> Competidores</a></li>
									<li><a data-toggle="tab" href="#tabOpr-6"><i class="fa fa-tasks"></i> Resumen</a></li>
									<li><a data-toggle="tab" href="#tabOpr-7"><i class="fa fa-paperclip"></i> Anexos</a></li>
								</ul>
							   <div class="tab-content">
								   <div id="tabOpr-1" class="tab-pane active">
									   <br>
										<div class="form-group">
											<label class="col-lg-1 control-label">Cierre planificado en <span class="text-danger">*</span></label>
											<div class="col-lg-1">
												<input autocomplete="off" name="CierrePlan" type="text" required="required" class="form-control" id="CierrePlan" maxlength="10" value="<?php if($edit==1||$sw_error==1){echo $row['CierrePlanificado'];} ?>" onChange="CalcularCierre();">
											</div>
											<div class="col-lg-1">
												<select name="CierreTipo" class="form-control" id="CierreTipo" <?php if(($edit==1)&&($row['IdEstadoOportunidad']=='C')){echo "disabled='disabled'";}?>>
													<option value="M" <?php if(($edit==1||$sw_error==1)&&($row['DifType']=="M")){echo "selected=\"selected\"";}?>>Meses</option>
													<option value="W" <?php if(($edit==1||$sw_error==1)&&($row['DifType']=="W")){echo "selected=\"selected\"";}?>>Semanas</option>
													<option value="D" <?php if(($edit==1||$sw_error==1)&&($row['DifType']=="D")){echo "selected=\"selected\"";}?>>Días</option>
												</select>
											</div>
											<label class="col-lg-1 control-label">Fecha de cierre prevista <span class="text-danger">*</span></label>
											<div class="col-lg-2 input-group date">
												 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaCierrePrev" id="FechaCierrePrev" type="text" onChange="CalcularDiasCierre();" required="required" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['FechaCierrePrevista']->format('Y-m-d');}?>" <?php if(($edit==1)&&($row['IdEstadoOportunidad']=='C')){echo "readonly";}?>>
											</div>
											<label class="col-lg-1 control-label">Monto potencial ($)</label>
											<div class="col-lg-2">
												<input name="MontoPotencial" type="text" class="form-control" id="MontoPotencial" value="<?php if($edit==1||$sw_error==1){echo number_format($row['MontoPotencial'],2);}?>" <?php if(($edit==1)&&($row['IdEstadoOportunidad']!='O')){echo "readonly";}?>>
											</div>
											<label class="col-lg-1 control-label">Monto ponderado ($)</label>
											<div class="col-lg-2">
												<input name="MontoPonderado" type="text" class="form-control" id="MontoPonderado" value="<?php if($edit==1||$sw_error==1){echo number_format($row['MontoPonderado'],2);}?>" <?php if(($edit==1)&&($row['IdEstadoOportunidad']!='O')){echo "readonly";}?>>
											</div>	
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">% de ganancia bruta</label>
											<div class="col-lg-2">
												<input name="PrcGananciaBruta" type="text" class="form-control" id="PrcGananciaBruta" value="<?php if($edit==1||$sw_error==1){echo number_format($row['PrcnGananciaBruta'],0);}?>" <?php if(($edit==1)&&($row['IdEstadoOportunidad']!='O')){echo "readonly";}?>>
											</div>
											<label class="col-lg-1 control-label">Ganancia bruta total ($)</label>
											<div class="col-lg-2">
												<input name="GananciaBruta" type="text" class="form-control" id="GananciaBruta" value="<?php if($edit==1||$sw_error==1){echo number_format($row['GananciaBruta'],2);}?>" <?php if(($edit==1)&&($row['IdEstadoOportunidad']!='O')){echo "readonly";}?>>
											</div>
											<label class="col-lg-1 control-label">Nivel de interés <span class="text-danger">*</span></label>
											<div class="col-lg-2">
												<select name="NivelInteres" class="form-control" id="NivelInteres" <?php if(($edit==1)&&($row['IdEstadoOportunidad']!='O')){echo "disabled='disabled'";}?> required="required">
													<option value="">Seleccione...</option>
													<?php while($row_NivelInteres=sqlsrv_fetch_array($SQL_NivelInteres)){?>
														<option value="<?php echo $row_NivelInteres['ID_NivelInteres'];?>" <?php if(($edit==1)&&(isset($row['IdNivelInteres']))&&(strcmp($row_NivelInteres['ID_NivelInteres'],$row['IdNivelInteres'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_NivelInteres['DE_NivelInteres'];?></option>
											  		<?php }?>
												</select>												
											</div>
										</div>
								   </div>
								   <div id="tabOpr-2" class="tab-pane">
										<br>
										<div class="form-group">
											<label class="col-lg-1 control-label">Canal SN</label>
											<div class="col-lg-3">
												<input name="CanalSN" type="hidden" id="CanalSN" value="<?php if(($edit==1)||($sw_error==1)){echo $row['CodigoCanalSN'];}?>">

												<input name="NombreCanalSN" type="text" class="form-control" id="NombreCanalSN" placeholder="Digite para buscar..." value="<?php if(($edit==1)||($sw_error==1)){echo $row['NombreCanalSN'];}?>" <?php if(($edit==1)&&($row['IdEstadoOportunidad']!='O')){echo "readonly";}?>>
											</div>
											<label class="col-lg-1 control-label">Contacto</label>
											<div class="col-lg-3">
												<select name="ContactoCanalSN" class="form-control" id="ContactoCanalSN" <?php if(($edit==1)&&($row['IdEstadoOportunidad']=='C')){echo "disabled='disabled'";}?>>
														<option value="">Seleccione...</option>
												<?php
													if(($edit==1||$sw_error==1)&&($row['CodigoCanalSN']!="")){
														while($row_ContactoCanalSN=sqlsrv_fetch_array($SQL_ContactoCanalSN)){?>
															<option value="<?php echo $row_ContactoCanalSN['CodigoContacto'];?>" <?php if((isset($row['CodigoContactoCanalSN']))&&(strcmp($row_ContactoCanalSN['CodigoContacto'],$row['CodigoContactoCanalSN'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ContactoCanalSN['ID_Contacto'];?></option>
												<?php 	}
													}?>
												</select>
											</div>
											<label class="col-lg-1 control-label">Proyecto</label>
											<div class="col-lg-3">
												<select name="Proyecto" class="form-control select2" id="Proyecto">
													<option value="">Seleccione...</option>
												<?php
													while($row_Proyecto=sqlsrv_fetch_array($SQL_Proyecto)){?>
														<option value="<?php echo $row_Proyecto['IdProyecto'];?>" <?php if((isset($row['IdProyecto']))&&(strcmp($row_Proyecto['IdProyecto'],$row['IdProyecto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Proyecto['DeProyecto'];?></option>
												<?php }?>
												</select>
											</div>
										</div>
									   	<div class="form-group">											
											<label class="col-lg-1 control-label">Fuente de información</label>
											<div class="col-lg-3">
												<select name="FuenteInfo" class="form-control select2" id="FuenteInfo">
													<option value="">Seleccione...</option>
												<?php
													while($row_FuenteInfo=sqlsrv_fetch_array($SQL_FuenteInfo)){?>
														<option value="<?php echo $row_FuenteInfo['ID_FuenteInfo'];?>" <?php if((isset($row['IdFuenteInfo']))&&(strcmp($row_FuenteInfo['ID_FuenteInfo'],$row['IdFuenteInfo'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_FuenteInfo['DE_FuenteInfo'];?></option>
												<?php }?>
												</select>
											</div>
											<label class="col-lg-1 control-label">Ramo</label>
											<div class="col-lg-3">
												<select name="RamoInd" class="form-control select2" id="RamoInd">
													<option value="">Seleccione...</option>
												<?php
													while($row_RamoInd=sqlsrv_fetch_array($SQL_RamoInd)){?>
														<option value="<?php echo $row_RamoInd['ID_RamoIndustria'];?>" <?php if((isset($row['IdIndustria']))&&(strcmp($row_RamoInd['ID_RamoIndustria'],$row['IdIndustria'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_RamoInd['DescripcionRamoIndustria'];?></option>
												<?php }?>
												</select>
											</div>
										</div>
									   	<div class="form-group">
											<label class="col-lg-1 control-label">Comentarios</label>
											<div class="col-lg-7">
												<textarea name="Comentarios" rows="7" maxlength="1000" class="form-control" id="Comentarios" type="text"><?php if($edit==1||$sw_error==1){echo $row['ComentariosOportunidad'];} ?></textarea>
											</div>											
										</div>
								   </div>
								   <div id="tabOpr-3" class="tab-pane">
										<br>
									   	<div class="row m-b-md">
											<div class="col-lg-12">
												<button class="btn btn-primary" type="button" id="NewParam" onClick="CrearEtapa();"><i class="fa fa-plus-circle"></i> Añadir etapa</button>
											</div>
										</div>
										<div class="table-responsive">
											<script>
												window.sessionStorage.removeItem('OPR<?php echo ($edit==1) ? $row['ID_Oportunidad'] : "";?>');
											</script>
											<table class="table table-bordered table-hover dataTables-example" >
											<thead>
											<tr>
												<th>#</th>
												<th>Fecha de inicio</th>
												<th>Fecha de cierre</th>
												<th>Empleado de ventas</th>
												<th>Etapa</th>
												<th>%</th>
												<th>Monto potencial</th>
												<th>Importe ponderado</th>
												<th>Comentarios</th>
												<th>Clase de documento</th>
												<th>Número de documento</th>
												<th>Propietario</th>
												<th>Estado</th>
												<th>Acciones</th>
											</tr>
											</thead>
											<tbody id="listaEtapas">
											<?php $i=1;
												while($row_Detalle=sqlsrv_fetch_array($SQL_Detalle)){ ?>
													<tr class="gradeX">
														<td id="idLin<?php echo $row_Detalle['IdLinea'];?>"><?php echo $i;?></td>
														<td id="FIni<?php echo $row_Detalle['IdLinea'];?>"><?php echo $row_Detalle['FechaInicio'];?></td>						
														<td id="FCie<?php echo $row_Detalle['IdLinea'];?>"><?php echo $row_Detalle['FechaCierre'];?></td>
														<td id="NomEmp<?php echo $row_Detalle['IdLinea'];?>"><?php echo $row_Detalle['DeEmpleado'];?></td>
														<td id="Eta<?php echo $row_Detalle['IdLinea'];?>"><?php echo $row_Detalle['DeEtapa'];?></td>										
														<td id="PrEtap<?php echo $row_Detalle['IdLinea'];?>"><?php echo number_format($row_Detalle['PorcentajeEtapa'],0)."%";?></td>		
														<td id="MPot<?php echo $row_Detalle['IdLinea'];?>"><?php echo number_format($row_Detalle['MontoPotencial'],2);?></td>
														<td id="IPon<?php echo $row_Detalle['IdLinea'];?>"><?php echo number_format($row_Detalle['ImportePonderado'],2);?></td>
														<td id="Com<?php echo $row_Detalle['IdLinea'];?>"><?php echo $row_Detalle['Comentarios'];?></td>
														<td id="NomObj<?php echo $row_Detalle['IdLinea'];?>"><?php echo $row_Detalle['NombreObjeto'];?></td>
														<td id="DocNum<?php echo $row_Detalle['IdLinea'];?>"><?php echo $row_Detalle['DocNumDocumento'];?></td>
														<td id="NomProp<?php echo $row_Detalle['IdLinea'];?>"><?php echo $row_Detalle['NombrePropietario'];?></td>
														<td id="Est<?php echo $row_Detalle['IdLinea'];?>"><span <?php if($row_Detalle['IdEstado']=='C'){echo "class='label label-info'";}else{echo "class='label label-warning'";}?>><?php echo $row_Detalle['DeEstado'];?></span></td>
														<td id="Acc<?php echo $row_Detalle['IdLinea'];?>">
															<button type="button" id="btnEdit<?php echo $row_Detalle['IdLinea'];?>" class="btn btn-success btn-xs" onClick="EditarEtapa('<?php echo $row_Detalle['ID_Oportunidad'];?>','<?php echo $row_Detalle['IdLinea'];?>','<?php echo $row_Detalle['IdEstado'];?>');"><i class="fa fa-pencil"></i> Editar</button>
															<button type="button" id="btnDel<?php echo $row_Detalle['IdLinea'];?>" class="btn btn-danger btn-xs" onClick="BorrarLinea('<?php echo $row_Detalle['ID_Oportunidad'];?>');"><i class="fa fa-trash"></i> Eliminar</button>
														</td>
													</tr>
											<?php echo "<script>
													CargarEtapa(
													'".$row_Detalle['ID_Oportunidad']."',
													'".$row_Detalle['IdLinea']."',
													'".$row_Detalle['FechaInicio']."',
													'".$row_Detalle['FechaCierre']."',
													'".$row_Detalle['IdEtapa']."',
													'".$row_Detalle['DeEtapa']."',
													'".number_format($row_Detalle['PorcentajeEtapa'],2)."',
													'".number_format($row_Detalle['MontoPotencial'],2)."',
													'".number_format($row_Detalle['ImportePonderado'],2)."',
													'".$row_Detalle['IdEmpleado']."',
													'".$row_Detalle['DeEmpleado']."',
													'".$row_Detalle['Comentarios']."',
													'".$row_Detalle['Actividad']."',
													'".$row_Detalle['ObjetoDocumento']."',
													'".$row_Detalle['NombreObjeto']."',
													'".$row_Detalle['DocEntryDocumento']."',
													'".$row_Detalle['DocNumDocumento']."',
													'".$row_Detalle['IdPropietario']."',
													'".$row_Detalle['NombrePropietario']."',
													'".$row_Detalle['IdEstado']."',
													'".$row_Detalle['DeEstado']."');
													</script>";
												$i++;
												}?>
											</tbody>
											</table>
									  </div>
								   </div>
								   <?php /*?><div id="tabOpr-4" class="tab-pane">
										<br>
										
								   </div><?php */?>
								   <div id="tabOpr-5" class="tab-pane">
										<br>
										<div class="table-responsive">
											<table class="table table-bordered table-hover dataTables-example" >
											<thead>
											<tr>
												<th>#</th>
												<th>Nombre</th>
												<th>Nivel de amenaza</th>
												<th>Comentarios</th>
												<th>Ganada</th>
											</tr>
											</thead>
											<tbody>
											
											</tbody>
											</table>
									  </div>
								   </div>
								   <div id="tabOpr-6" class="tab-pane">
										<br>
									   	<div class="form-group">
											<label class="col-lg-1 control-label">Estado</label>
											<div class="col-lg-3">
												<select name="Estado" class="form-control" id="Estado">
												  <?php while($row_EstadoOpr=sqlsrv_fetch_array($SQL_EstadoOpr)){?>
														<option value="<?php echo $row_EstadoOpr['Cod_Estado'];?>" <?php if(($edit==1)&&(isset($row['IdEstadoOportunidad']))&&(strcmp($row_EstadoOpr['Cod_Estado'],$row['IdEstadoOportunidad'])==0)){ echo "selected=\"selected\"";}elseif(($edit==0)&&($row_EstadoOpr['Cod_Estado']=='O')){echo "selected=\"selected\"";}?>><?php echo $row_EstadoOpr['NombreEstado'];?></option>
												  <?php }?>
												</select>
											</div>
											<label class="col-lg-1 control-label">Clase de documento</label>
											<div class="col-lg-3">
												<input name="TipoDocResumen" type="hidden" class="form-control" id="TipoDocResumen" value="<?php if($edit==1||$sw_error==1){echo $row['IdTipoDocResumen'];}?>">
												
												<input autocomplete="off" name="NombreDocResumen" type="text" class="form-control" id="NombreDocResumen" maxlength="10" value="<?php if($edit==1||$sw_error==1){echo $row['DeTipoDocResumen'];}?>" readonly>
											</div>
											<label class="col-lg-1 control-label">Número de documento</label>
											<div class="col-lg-3">
												<input name="DocEntryResumen" type="hidden" class="form-control" id="DocEntryResumen"value="<?php if($edit==1||$sw_error==1){echo $row['DocEntryResumen'];}?>">
												
												<input autocomplete="off" name="DocNumResumen" type="text" class="form-control" id="DocNumResumen" maxlength="10" value="<?php if($edit==1||$sw_error==1){echo $row['DocNumResumen'];}?>" readonly>
											</div>
										</div>
								   </div>
								   </form>
								   <div id="tabOpr-7" class="tab-pane">
										<br>
										<?php 
										if($edit==1){
											if($row['IdAnexo']!=0){
										?>
											<div class="form-group">
												<div class="col-xs-12">
													<?php while($row_Anexo=sqlsrv_fetch_array($SQL_Anexo)){
																$Icon=IconAttach($row_Anexo['FileExt']);?>
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
										<div class="row">
											<form action="upload.php" class="dropzone" id="dropzoneForm" name="dropzoneForm">
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
        <!-- InstanceEndEditable -->
        <?php include("includes/footer.php"); ?>

    </div>
</div>
<?php include("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>
 $(document).ready(function(){
	 
	 $("#FrmOp").validate({
		submitHandler: function(form){
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
						let datosDetalle = window.sessionStorage.getItem('OPR<?php echo ($edit==1) ? $row['ID_Oportunidad'] : "";?>')
						let dataJSON = document.getElementById('dataJSON')
						dataJSON.value=datosDetalle
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
	 
	$(".select2").select2();
	 
	 maxLength('Comentarios');
	 
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
	$("#CardName").easyAutocomplete(options);
	 
	 $('#FechaInicio').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			todayHighlight: true,
			format: 'yyyy-mm-dd'
		});
	 $('#FechaCierre').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			todayHighlight: true,
			format: 'yyyy-mm-dd'
		});
	 $('#FechaCierrePrev').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			todayHighlight: true,
			format: 'yyyy-mm-dd'
		});
	 
	$('.dataTables-example').DataTable({
			searching: false,
			info: false,
			paging: false,
			fixedHeader: true,
		 	ordering: false
		});
 });
</script>
<script>
function CrearEtapa(){
	$('.ibox-content').toggleClass('sk-loading',true);
	
	$.ajax({
		type: "POST",
		url: "md_crear_etapa_oportunidad.php",
		data:{
			id:'<?php echo ($edit==1) ? $row['ID_Oportunidad']: "";?>'
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		}
	});
}

function EditarEtapa(id, linea, estado){
	$('.ibox-content').toggleClass('sk-loading',true);
	
	$.ajax({
		type: "POST",
		url: "md_crear_etapa_oportunidad.php",
		data:{
			id:id,
			linea:linea,
			estado:estado,
			edit:1
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		}
	});
}
	
function Validar(){
	var result=true;
	
	let datosDetalle = window.sessionStorage.getItem('OPR<?php echo ($edit==1) ? $row['ID_Oportunidad'] : "";?>')
	let json=[]
	if(datosDetalle){
		json = JSON.parse(datosDetalle)
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