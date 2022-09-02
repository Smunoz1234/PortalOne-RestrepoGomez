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
		//Dividir el nombre en Nombre y Segundo nombre
		$Nombres = explode(" ", $_POST['PNNombres']);
		
		//Consultar nombres
		$SQL_Ciudad=Seleccionar('uvw_tbl_Municipios','Ciudad',"Codigo='".$_POST['City']."'");
		$row_Ciudad=sqlsrv_fetch_array($SQL_Ciudad);
		
		$SQL_Barrio=Seleccionar('uvw_Sap_tbl_Barrios','DeBarrio',"IdBarrio='".$_POST['Block']."'");
		$row_Barrio=sqlsrv_fetch_array($SQL_Barrio);		
		
		$Cabecera=array(
			"id_series" => null,
			"id_socio_negocio" => "LD-".$_POST['LicTradNum'],
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
			"id_proyecto" => ObtenerValorDefecto(2,"IdProyecto"),
			"id_regimen_tributario" => ObtenerValorDefecto(2,"IdRegimenTributario"),
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
					"id_socio_negocio" => "LD-".$_POST['LicTradNum'],
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
					"id_direccion" => ObtenerVariable("DirFacturacion"),
					"direccion" => $_POST['Street'],
					"id_socio_negocio" => "LD-".$_POST['LicTradNum'],
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
					"id_direccion" => ObtenerVariable("DirDestino"),
					"direccion" => $_POST['Street'],
					"id_socio_negocio" => "LD-".$_POST['LicTradNum'],
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
			"metodo" => 1
		);
		
		//$Cabecera_json=json_encode($Cabecera);
		//echo $Cabecera_json;
		
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

//Tipos de articulos
$SQL_TipoArticulo=Seleccionar('uvw_tbl_TipoArticulo','*');

$SQL_UndNeg=Seleccionar('tbl_TDU_UnidadNegocio','*');

//Grupos de articulos
$SQL_GruposArticulos=Seleccionar('uvw_Sap_tbl_GruposArticulos','*','','ItmsGrpNam');

?>
<!doctype html>
<html>
<head>
<?php include_once("includes/cabecera.php"); ?>
<title>Crear nuevo artículo | <?php echo NOMBRE_PORTAL;?></title>
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_ArtAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El artículo ha sido creado exitosamente.',
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
<script>
	$(document).ready(function() {
		$("#UndNegocio").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var UndNegocio=document.getElementById('UndNegocio').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=34&id="+UndNegocio,
				success: function(response){
					$('#Marca').html(response).fadeIn();
					$('#Marca').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});
		$("#Marca").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Marca=document.getElementById('Marca').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=35&id="+Marca,
				success: function(response){
					$('#Linea').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});
	});
</script>
</head>

<body>
	<div class="row wrapper border-bottom white-bg page-heading">
		<div class="col-sm-8">
			<h2>Crear nuevo artículo</h2>
			<ol class="breadcrumb">
				<li>
					Datos maestros de artículo
				</li>
				<li class="active">
					<strong>Crear nuevo artículo</strong>
				</li>
			</ol>
		</div>
	</div>
	<div class="ibox-content">
		<?php include("includes/spinner.php"); ?>
		<div class="row"> 
			<div class="col-lg-12">
				<form action="popup_crear_lead.php" method="post" class="form-horizontal" id="FrmCrear">
					<div class="form-group">
						<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Información general</h3></label>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Tipo de artículo <span class="text-danger">*</span></label>
						<div class="col-lg-3">
							<select name="TipoArticulo" class="form-control" id="TipoArticulo" required>
								<option value="1">Artículo</option>
								<option value="2">Servicio</option>
							</select>
						</div>
						<label class="col-lg-1 control-label">Clase de artículo <span class="text-danger">*</span></label>
						<div class="col-lg-3">
							<select name="ItemType" class="form-control" id="ItemType" required>
								<option value="">Seleccione...</option>
							<?php
								while($row_TipoArticulo=sqlsrv_fetch_array($SQL_TipoArticulo)){?>
									<option value="<?php echo $row_TipoArticulo['ItemType'];?>" <?php if((isset($row['ItemType']))&&(strcmp($row_TipoArticulo['ItemType'],$row['ItemType'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoArticulo['DE_ItemType'];?></option>
							<?php }?>
							</select>
						</div>
						<label class="col-lg-1 control-label">Artículo de venta</label>
						<div class="col-lg-1">
							<label class="checkbox-inline i-checks"><input name="ArticuloVenta" id="ArticuloVenta" type="checkbox" value="1"></label>
						</div>
						<label class="col-lg-1 control-label">Artículo de compra</label>
						<div class="col-lg-1">
							<label class="checkbox-inline i-checks"><input name="ArticuloCompra" id="ArticuloCompra" type="checkbox" value="1"></label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Unidad de negocio <span class="text-danger">*</span></label>
						<div class="col-lg-3">
							<select name="UndNegocio" class="form-control" id="UndNegocio" required>
								<option value="">Seleccione...</option>
							<?php
								while($row_UndNeg=sqlsrv_fetch_array($SQL_UndNeg)){?>
									<option value="<?php echo $row_UndNeg['Id_UndNegocio'];?>"><?php echo $row_UndNeg['De_UndNegocio'];?></option>
							<?php }?>
							</select>
						</div>	
						<label class="col-lg-1 control-label">Marca <span class="text-danger">*</span></label>
						<div class="col-lg-3">
							<select name="Marca" class="form-control" id="Marca" required>
								<option value="">Seleccione...</option>
							</select>
						</div>
						<label class="col-lg-1 control-label">Linea <span class="text-danger">*</span></label>
						<div class="col-lg-3">
							<select name="Linea" class="form-control" id="Linea" required>
								<option value="">Seleccione...</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Datos del artículo</h3></label>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Código de artículo <span class="text-danger">*</span></label>
						<div class="col-lg-3">
							<input type="text" class="form-control" name="CardName" id="CardName" required value="<?php if($sw_error==1){ echo $_POST['CardName'];}?>" autocomplete="off" <?php if($sw_error==1&&$_POST['TipoEntidad']==1){ echo "readonly='readonly'";}?>>
						</div>
						<label class="col-lg-1 control-label">Descripción <span class="text-danger">*</span></label>
						<div class="col-lg-6">
							<input type="text" class="form-control" name="CorreoCliente" id="CorreoCliente" required value="<?php if($sw_error==1){ echo $_POST['CorreoCliente'];}?>" autocomplete="off">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Grupo <span class="text-danger">*</span></label>
						<div class="col-lg-3">
							<select name="GroupCode" class="form-control select2" id="GroupCode" required>
								<option value="">Seleccione...</option>
							<?php
								while($row_GruposArticulos=sqlsrv_fetch_array($SQL_GruposArticulos)){?>
									<option value="<?php echo $row_GruposArticulos['ItmsGrpCod'];?>"><?php echo $row_GruposArticulos['ItmsGrpNam'];?></option>
							<?php }?>
							</select>
						</div>
						<label class="col-lg-1 control-label">SubGrupo</label>
						<div class="col-lg-3">
							<select name="GroupCode" class="form-control select2" id="GroupCode" required>
								<option value="0">(Ninguno)</option>
							</select>
						</div>
					</div>				
					<div class="form-group">
						<div class="col-lg-9">
							<button class="btn btn-primary" type="submit" id="Crear"><i class="fa fa-check"></i> Crear artículo</button>
							<input type="hidden" id="P" name="P" value="38" />
							<input type="hidden" id="swError" name="swError" value="<?php echo $sw_error;?>" />
						</div>
					</div>					
				</form>
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

</script>
</body>
</html>
<?php 
	sqlsrv_close( $conexion );?>