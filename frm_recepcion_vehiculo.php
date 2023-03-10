<?php require_once "includes/conexion.php";
PermitirAcceso(1706);

$IdFrm = "";
$msg_error = ""; //Mensaje del error
$dt_LS = 0; //sw para saber si vienen datos del SN. 0 no vienen. 1 si vienen.

//Nombre del formulario
if (isset($_REQUEST['frm']) && ($_REQUEST['frm'] != "")) {
    $frm = $_REQUEST['frm'];

    // Stiven Muñoz Murillo, 10/01/2022
    $SQL_Cat = Seleccionar("uvw_tbl_Categorias", "ID_Categoria, NombreCategoria, NombreCategoriaPadre, URL", "ID_Categoria = '" . base64_decode($frm) . "'");
} else {
    // Stiven Muñoz Murillo, 09/02/2022
    $frm = "";
}

// Stiven Muñoz Murillo, 10/01/2022
$row_Cat = isset($SQL_Cat) ? sqlsrv_fetch_array($SQL_Cat) : [];

if (isset($_GET['id']) && ($_GET['id'] != "")) {
    $IdFrm = base64_decode($_GET['id']);
}

if (isset($_GET['tl']) && ($_GET['tl'] != "")) { //0 Creando el formulario. 1 Editando el formulario.
    $type_frm = $_GET['tl'];
} elseif (isset($_POST['tl']) && ($_POST['tl'] != "")) {
    $type_frm = $_POST['tl'];
} else {
    $type_frm = 0;
}

if (isset($_GET['dt_LS']) && ($_GET['dt_LS']) == 1) { //Verificar que viene de una Llamada de servicio
    $dt_LS = 1;

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreCliente');
    $row_Cliente = sqlsrv_fetch_array($SQL_Cliente);

    //Contacto cliente
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreContacto');

    //Sucursal cliente, (Se agrego "TipoDireccion='S' AND ...")
    $SQL_SucursalCliente = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "TipoDireccion='S' AND CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreSucursal');

    //Orden de servicio
    $SQL_OrdenServicioCliente = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_LlamadaServicio='" . base64_decode($_GET['LS']) . "'");
}

if (isset($_POST['swError']) && ($_POST['swError'] != "")) { //Para saber si ha ocurrido un error.
    $sw_error = $_POST['swError'];
} else {
    $sw_error = 0;
}

if ($type_frm == 0) {
    $Title = "Crear nueva Recepción de vehículo";
} else {
    $Title = "Editar Recepción de vehículo";
}

$dir = CrearObtenerDirTemp();
$dir_firma = CrearObtenerDirTempFirma();

// @author Stiven Muñoz Murillo
// @version 10/01/2022

// Marcas de vehiculo en la tarjeta de equipo
$SQL_MarcaVehiculo = Seleccionar('uvw_Sap_tbl_TarjetasEquipos_MarcaVehiculo', '*');

// Lineas de vehiculo en la tarjeta de equipo
$SQL_LineaVehiculo = Seleccionar('uvw_Sap_tbl_TarjetasEquipos_LineaVehiculo', '*');

// Modelo o año de fabricación de vehiculo en la tarjeta de equipo
$SQL_ModeloVehiculo = Seleccionar('uvw_Sap_tbl_TarjetasEquipos_AñoModeloVehiculo', '*');

// Colores de vehiculo en la tarjeta de equipo
$SQL_ColorVehiculo = Seleccionar('uvw_Sap_tbl_TarjetasEquipos_ColorVehiculo', '*');

// Preguntas en la recepción de vehículo
$SQL_Preguntas = Seleccionar('tbl_RecepcionVehiculos_Preguntas', '*', "estado = 'Y'");
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $Title; ?> | <?php echo NOMBRE_PORTAL; ?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		var borrarLineaModeloVehiculo = true;

		$("#id_socio_negocio").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Cliente=document.getElementById('id_socio_negocio').value;

			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=2&id="+Cliente,
				success: function(response){
					$('#ContactoCliente').html(response).fadeIn();
					$('#ContactoCliente').trigger('change');
					//$('.ibox-content').toggleClass('sk-loading',false);
				}
			});

			<?php if ($dt_LS == 0) { //Para que no recargue las listas cuando vienen de una llamada de servicio. ?>
				$.ajax({
					type: "POST",
					url: "ajx_cbo_select.php?type=3&id="+Cliente,
					success: function(response){
						$('#SucursalCliente').html(response).fadeIn();
						$('#SucursalCliente').trigger('change');
					}
				});

				// Stiven Muñoz Murillo, 10/01/2022
				$.ajax({
					type: "POST",
					url: "ajx_cbo_select.php?type=6&id="+Cliente,
					success: function(response){
						$('#id_llamada_servicio').html(response).fadeIn();
						$('#id_llamada_servicio').trigger('change');
					}
				});
			<?php }?>

			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=16&id="+Cliente,
				success: function(response){
					$('#Area1').html(response).fadeIn();
				}
			});

			// Stiven Muñoz Murillo, 20/01/2022
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data: {
					type: 45,
					id: Cliente
				},
				dataType:'json',
				success: function(data){
					console.log("Line 151, ajx_buscar_datos_json.php 45", data);

					document.getElementById('direccion_destino').value=data.Direccion;
					document.getElementById('celular').value=data.Celular;
					document.getElementById('ciudad').value=data.Ciudad;
					document.getElementById('telefono').value=data.Telefono;
					document.getElementById('correo').value=data.Correo;
				},
				error: function(error) {
					console.error(error.responseText);
				}
			});

			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$("#SucursalCliente").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);

			var Cliente=document.getElementById('id_socio_negocio').value;
			var Sucursal=document.getElementById('SucursalCliente').value;

			if(Sucursal !== "" && Sucursal !== null && Sucursal*1 !== -1) {
				$.ajax({
					url:"ajx_buscar_datos_json.php",
					data:{type:1,CardCode:Cliente,Sucursal:Sucursal},
					dataType:'json',
					success: function(data){
						document.getElementById('direccion_destino').value=data.Direccion;
						document.getElementById('barrio').value=data.Barrio;
						document.getElementById('ciudad').value=data.Ciudad;
						document.getElementById('telefono').value=data.TelefonoContacto;
						document.getElementById('correo').value=data.CorreoContacto;
					},
					error: function(error) {
						console.error("#SucursalCliente", error.responseText);
					}
				});
			}

			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$("#ContactoCliente").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Contacto=document.getElementById('ContactoCliente').value;

			if(Contacto !== "" && Contacto !== null) {
				$.ajax({
					url:"ajx_buscar_datos_json.php",
					data:{type:5,Contacto:Contacto},
					dataType:'json',
					success: function(data){
						document.getElementById('telefono').value=data.Telefono;
						document.getElementById('correo').value=data.Correo;
					},
					error: function(error) {
						console.error("#ContactoCliente", error.responseText);
					}
				});
			}

			$('.ibox-content').toggleClass('sk-loading',false);
		});

		// Stiven Muñoz Murillo, 10/01/2021
		$("#CDU_Marca").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var marcaVehiculo=document.getElementById('CDU_Marca').value;

			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=39&id="+marcaVehiculo,
				success: function(response){
					// console.log(response);

					if(borrarLineaModeloVehiculo) {
						$('#CDU_Linea').html(response).fadeIn();
						$('#CDU_Linea').trigger('change');
					} else {
						borrarLineaModeloVehiculo = true;
					}

					$('.ibox-content').toggleClass('sk-loading',false);
				},
				error: function(error) {
					console.error("#CDU_Marca", error.responseText);
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});

		// Stiven Muñoz Murillo, 19/01/2021
		$("#id_llamada_servicio").change(function() {
			$('.ibox-content').toggleClass('sk-loading',true);

			$.ajax({
				url: "ajx_buscar_datos_json.php",
				data: {
					type: 44,
					id: '',
					ot: document.getElementById('id_llamada_servicio').value
				},
				dataType: 'json',
				success: function(data){
					console.log("Line 254, ajx_buscar_datos_json.php 44", data);

					document.getElementById('placa').value = data.SerialInterno;
					document.getElementById('VIN').value = data.SerialFabricante;
					document.getElementById('no_motor').value = data.No_Motor;

					document.getElementById('km_actual').value = data.CDU_Kilometros; // SMM, 02/03/2022

					<?php if (PermitirFuncion(1708)) {?> // SMM, 14/06/2022
						document.getElementById('responsable_cliente').value = data.CDU_NombreContacto; // SMM, 15/02/2022
						document.getElementById('telefono_responsable_cliente').value = data.CDU_TelefonoContacto; // SMM, 22/02/2022
						document.getElementById('correo_responsable_cliente').value = data.CDU_CorreoContacto; // SMM, 22/02/2022
					<?php }?> // Se deben llenar sólo con el permiso.

					if(data.CDU_IdMarca !== null) {
						document.getElementById('id_marca').value = data.CDU_IdMarca;
						$('#id_marca').trigger('change');

						borrarLineaModeloVehiculo = false;
						document.getElementById('id_linea').value = data.CDU_IdLinea;
						$('#id_linea').trigger('change');

						document.getElementById('id_annio').value = data.CDU_Ano;
						$('#id_annio').trigger('change');

						document.getElementById('id_color').value = data.CDU_Color;
						$('#id_color').trigger('change');
					}

					$('.ibox-content').toggleClass('sk-loading',false);
				},
				error: function(error) {
					console.error("#id_llamada_servicio", error.responseText);
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});
	});

// Stiven Muñoz Murillo, 12/01/2022
function ConsultarServicio(){
	var llamada=document.getElementById('id_llamada_servicio');
	if(llamada.value!=""){
		self.name='opener';
		remote=open('llamada_servicio.php?id='+Base64.encode(llamada.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}

function ConsultarDatosCliente(){
	var Cliente=document.getElementById('id_socio_negocio');
	if(Cliente.value!=""){
		self.name='opener';
		remote=open('socios_negocios.php?id='+Base64.encode(Cliente.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}

function AbrirFirma(IDCampo){
	var posicion_x;
	var posicion_y;
	posicion_x=(screen.width/2)-(1200/2);
	posicion_y=(screen.height/2)-(500/2);
	self.name='opener';
	remote=open('popup_firma.php?id='+Base64.encode(IDCampo),'remote',"width=1200,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
	remote.focus();
}

// SMM, 19/04/2022
function ConsultarEquipo(){
	var numSerie=document.getElementById('placa');
	if(numSerie.value!=""){
		self.name='opener';
		remote=open('tarjeta_equipo.php?id='+Base64.encode(numSerie.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}
</script>
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include "includes/menu.php";?>

    <div id="page-wrapper" class="gray-bg">
        <?php include "includes/menu_superior.php";?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2><?php echo $Title; ?></h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#"><?php echo isset($row_Cat['NombreCategoriaPadre']) ? $row_Cat['NombreCategoriaPadre'] : " Formularios"; ?></a>
                        </li>
                        <li class="active">
                            <a href="<?php echo isset($row_Cat['URL']) ? $row_Cat['URL'] . "?id=" . $frm : "consultar_frm_recepcion_vehiculo.php" ?>"><?php echo isset($row_Cat['NombreCategoria']) ? $row_Cat['NombreCategoria'] : "Recepción de vehículos"; ?></a>
                        </li>
						<li class="active">
                            <strong><?php echo $Title; ?></strong>
                        </li>
                    </ol>
                </div>
            </div>

         <div class="wrapper wrapper-content">
			 <div class="ibox-content">
				  <?php include "includes/spinner.php";?>
          <div class="row">
           <div class="col-lg-12">
              <form action="frm_recepcion_vehiculo.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="recepcionForm">
				<!-- IBOX, Inicio -->
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-user"></i> Datos del propietario</h5>
						 <a class="collapse-link pull-right" style="color: white;">
							<i class="fa fa-chevron-up"></i>
						</a>
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label"><i onClick="ConsultarDatosCliente();" title="Consultar cliente" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Cliente <span class="text-danger">*</span></label>

								<input name="id_socio_negocio" type="hidden" id="id_socio_negocio" value="<?php if (($type_frm == 1) || ($sw_error == 1)) {echo $row['ID_CodigoCliente'];} elseif ($dt_LS == 1) {echo $row_Cliente['CodigoCliente'];}?>">
								<input name="socio_negocio" type="text" required="required" class="form-control" id="socio_negocio" placeholder="Digite para buscar..." <?php if ((($type_frm == 1) && ($row['Cod_Estado'] == '-1')) || ($dt_LS == 1)) {echo "readonly='readonly'";}?> value="<?php if (($type_frm == 1) || ($sw_error == 1)) {echo $row['NombreCliente'];} elseif ($dt_LS == 1) {echo $row_Cliente['NombreCliente'];}?>">
							</div>
							<div class="col-lg-4">
								<label class="control-label">Contacto</label>

								<select name="ContactoCliente" class="form-control" id="ContactoCliente" <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "disabled='disabled'";}?>>
								<?php if ((($type_frm == 0) || ($sw_error == 1)) && ($dt_LS != 1)) {?><option value="">Seleccione...</option><?php }?>
								<?php if (($type_frm == 1) || ($sw_error == 1) || ($dt_LS == 1)) {while ($row_ContactoCliente = sqlsrv_fetch_array($SQL_ContactoCliente)) {?>
										<option value="<?php echo $row_ContactoCliente['CodigoContacto']; ?>" <?php if ((isset($row['ID_Contacto'])) && (strcmp($row_ContactoCliente['CodigoContacto'], $row['ID_Contacto']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_ContactoCliente['ID_Contacto']; ?></option>
								<?php }}?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Sucursal</label>

								<select name="SucursalCliente" class="form-control select2" id="SucursalCliente" <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "disabled='disabled'";}?>>
								<?php if ((($type_frm == 0) || ($sw_error == 1)) && ($dt_LS != 1)) {?><option value="">Seleccione...</option><?php }?>
								<?php if (($type_frm == 1) || ($sw_error == 1) || ($dt_LS == 1)) {while ($row_SucursalCliente = sqlsrv_fetch_array($SQL_SucursalCliente)) {?>
										<option value="<?php echo $row_SucursalCliente['NombreSucursal']; ?>" <?php if ((isset($row['NombreSucursal'])) && (strcmp($row_SucursalCliente['NombreSucursal'], $row['NombreSucursal']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_SucursalCliente['NombreSucursal']; ?></option>
								<?php }}?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Teléfono <span class="text-danger">*</span></label>

								<input name="telefono" type="text" class="form-control" id="telefono" required="required" maxlength="50" <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "readonly='readonly'";}?> value="<?php if (($type_frm == 1) || ($sw_error == 1)) {echo $row['TelefonoContacto'];} elseif ($dt_LS == 1) {echo isset($_GET['Telefono']) ? base64_decode($_GET['Telefono']) : "";}?>">
							</div>
							<div class="col-lg-4">
								<label class="control-label">Celular</label>

								<input name="celular" type="text" class="form-control" id="celular" maxlength="50" <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "readonly='readonly'";}?> value="<?php if (($type_frm == 1) || ($sw_error == 1)) {echo $row['CelularContacto'];} elseif ($dt_LS == 1) {echo isset($_GET['Celular']) ? base64_decode($_GET['Celular']) : "";}?>">
							</div>
							<div class="col-lg-4">
								<label class="control-label">Correo <span class="text-danger">*</span></label>

								<input name="correo" type="email" class="form-control" id="correo" required="required" maxlength="100" <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "readonly='readonly'";}?> value="<?php if (($type_frm == 1) || ($sw_error == 1)) {echo $row['CorreoContacto'];} elseif ($dt_LS == 1) {echo isset($_GET['Correo']) ? base64_decode($_GET['Correo']) : "";}?>">
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Dirección</label>

								<input name="direccion_destino" type="text" class="form-control" id="direccion_destino" maxlength="100" <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "readonly='readonly'";}?> value="<?php if (($type_frm == 1) || ($sw_error == 1)) {echo $row['Direccion'];} elseif ($dt_LS == 1) {echo isset($_GET['Direccion']) ? base64_decode($_GET['Direccion']) : "";}?>">
							</div>
							<div class="col-lg-4">
								<label class="control-label">Barrio</label>

								<input name="barrio" type="text" class="form-control" id="barrio" maxlength="50" <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "readonly='readonly'";}?> value="<?php if (($type_frm == 1) || ($sw_error == 1)) {echo $row['barrio'];} elseif ($dt_LS == 1) {echo isset($_GET['Barrio']) ? base64_decode($_GET['Barrio']) : "";}?>">
							</div>
							<div class="col-lg-4">
								<label class="control-label">Ciudad</label>

								<input name="ciudad" type="text" class="form-control" id="ciudad" maxlength="100" value="<?php if (($type_frm == 1) || ($sw_error == 1)) {echo $row['ciudad'];} elseif ($dt_LS == 1) {echo base64_decode($_GET['Ciudad']);}?>" <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "readonly='readonly'";}?>>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-8 border-bottom">
								<label class="control-label text-danger">Información del servicio</label>
							</div>
						</div>
						<!-- Orden de servicio, Inicio -->
						<div class="form-group">
							<div class="col-lg-8">
								<label class="control-label"><i onClick="ConsultarServicio();" title="Consultar llamada de servicio" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Orden servicio <span class="text-danger">*</span></label>

								<select name="id_llamada_servicio" class="form-control select2" required="required" id="id_llamada_servicio" <?php if ($dt_LS == 1) {echo "disabled='disabled'";}?>>
									<?php if ($dt_LS != 1) {?><option value="">(Ninguna)</option><?php }?>
									<?php if ($sw_error == 1 || $dt_LS == 1 || $type_llmd == 1) {while ($row_OrdenServicioCliente = sqlsrv_fetch_array($SQL_OrdenServicioCliente)) {?>
										<option value="<?php echo $row_OrdenServicioCliente['ID_LlamadaServicio']; ?>" <?php if ((isset($row['ID_OrdenServicioActividad'])) && (strcmp($row_OrdenServicioCliente['ID_LlamadaServicio'], $row['ID_LlamadaServicio']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['LS']) && (strcmp($row_OrdenServicioCliente['ID_LlamadaServicio'], base64_decode($_GET['LS'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_OrdenServicioCliente['DocNum'] . " - " . $row_OrdenServicioCliente['AsuntoLlamada']; ?></option>
								  <?php }}?>
								</select>
							</div>
						</div>
						<!-- Orden de servicio, Fin -->
					</div>
				</div>
				<!-- IBOX, Fin -->
				<!-- IBOX, Inicio -->
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-car"></i> Datos del vehículo</h5>
						 <a class="collapse-link pull-right" style="color: white;">
							<i class="fa fa-chevron-up"></i>
						</a>
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label"><i onClick="ConsultarEquipo();" title="Consultar Placa" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Serial Interno (Placa) <span class="text-danger">*</span></label>
								<input <?php if ($dt_LS == 1) {echo "readonly='readonly'";}?> autocomplete="off" name="placa" type="text" required="required" class="form-control" id="placa" maxlength="150" value="<?php if (isset($row['SerialInterno'])) {echo $row['SerialInterno'];}?>">
							</div>
							<div class="col-lg-4">
								<label class="control-label">Serial Fabricante (VIN) <span class="text-danger">*</span></label>
								<input <?php if ($dt_LS == 1) {echo "readonly='readonly'";}?> autocomplete="off" name="VIN" type="text" required="required" class="form-control" id="VIN" maxlength="150" value="<?php if (isset($row['SerialFabricante'])) {echo $row['SerialFabricante'];}?>">
							</div>
							<div class="col-lg-4">
								<label class="control-label">No_Motor <span class="text-danger">*</span></label>
								<input autocomplete="off" name="no_motor" type="text" required="required" class="form-control" id="no_motor" maxlength="100"
								value="<?php if (isset($row['No_Motor'])) {echo $row['No_Motor'];}?>">
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Marca del vehículo <span class="text-danger">*</span></label>
								<select name="id_marca" class="form-control select2" required="required" id="id_marca"
								<?php if ($dt_LS == 1) {echo "disabled='disabled'";}?>>
									<option value="" disabled selected>Seleccione...</option>
								  <?php while ($row_MarcaVehiculo = sqlsrv_fetch_array($SQL_MarcaVehiculo)) {?>
									<option value="<?php echo $row_MarcaVehiculo['IdMarcaVehiculo']; ?>"
									<?php if ((isset($row['CDU_IdMarca'])) && (strcmp($row_MarcaVehiculo['IdMarcaVehiculo'], $row['CDU_IdMarca']) == 0)) {echo "selected=\"selected\"";}?>>
										<?php echo $row_MarcaVehiculo['DeMarcaVehiculo']; ?>
									</option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Línea del vehículo <span class="text-danger">*</span></label>
								<select name="id_linea" class="form-control select2" required="required" id="id_linea"
								<?php if ($dt_LS == 1) {echo "disabled='disabled'";}?>>
										<option value="" disabled selected>Seleccione...</option>
								  <?php while ($row_LineaVehiculo = sqlsrv_fetch_array($SQL_LineaVehiculo)) {?>
										<option value="<?php echo $row_LineaVehiculo['IdLineaModeloVehiculo']; ?>"
										<?php if ((isset($row['CDU_IdLinea'])) && (strcmp($row_LineaVehiculo['IdLineaModeloVehiculo'], $row['CDU_IdLinea']) == 0)) {echo "selected=\"selected\"";}?>>
											<?php echo $row_LineaVehiculo['DeLineaModeloVehiculo']; ?>
										</option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Modelo del vehículo <span class="text-danger">*</span></label>
								<select name="id_annio" class="form-control select2" required="required" id="id_annio"
								<?php if ($dt_LS == 1) {echo "disabled='disabled'";}?>>
										<option value="" disabled selected>Seleccione...</option>
								  <?php while ($row_ModeloVehiculo = sqlsrv_fetch_array($SQL_ModeloVehiculo)) {?>
										<option value="<?php echo $row_ModeloVehiculo['CodigoModeloVehiculo']; ?>"
										<?php if ((isset($row['CDU_Ano'])) && ((strcmp($row_ModeloVehiculo['CodigoModeloVehiculo'], $row['CDU_Ano']) == 0) || (strcmp($row_ModeloVehiculo['AñoModeloVehiculo'], $row['CDU_Ano']) == 0))) {echo "selected=\"selected\"";}?>>
											<?php echo $row_ModeloVehiculo['AñoModeloVehiculo']; ?>
										</option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Color <span class="text-danger">*</span></label>
								<select name="id_color" class="form-control select2" required="required" id="id_color"
								<?php if ($dt_LS == 1) {echo "disabled='disabled'";}?>>
										<option value="" disabled selected>Seleccione...</option>
								  <?php while ($row_ColorVehiculo = sqlsrv_fetch_array($SQL_ColorVehiculo)) {?>
										<option value="<?php echo $row_ColorVehiculo['CodigoColorVehiculo']; ?>"
										<?php if ((isset($row['CDU_Color'])) && ((strcmp($row_ColorVehiculo['CodigoColorVehiculo'], $row['CDU_Color']) == 0) || (strcmp($row_ColorVehiculo['NombreColorVehiculo'], $row['CDU_Color']) == 0))) {echo "selected=\"selected\"";}?>>
											<?php echo $row_ColorVehiculo['NombreColorVehiculo']; ?>
										</option>
								  <?php }?>
								</select>
							</div>
						</div>
					</div>
				</div>
				<!-- IBOX, Fin -->
				<!-- IBOX, Inicio -->
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-info-circle"></i> Datos de recepción</h5>
						 <a class="collapse-link pull-right" style="color: white;">
							<i class="fa fa-chevron-up"></i>
						</a>
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Servicio de movilidad ofrecido</label>
								<select name="servicio_movil_ofrecido" class="form-control" id="servicio_movil_ofrecido" <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "disabled='disabled'";}?>>
										<option value="SI">SI</option>
										<option value="NO">NO</option>
								<?php //while ($row_EstadoLlamada = sqlsrv_fetch_array($SQL_EstadoLlamada)) {?>
										<!--option value="<?php echo $row_EstadoLlamada['Cod_Estado']; ?>" <?php if ((isset($row['Cod_Estado'])) && (strcmp($row_EstadoLlamada['Cod_Estado'], $row['Cod_Estado']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_EstadoLlamada['NombreEstado']; ?></option -->
								<?php //}?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Se hizo prueba de ruta</label>
								<select name="hizo_prueba_ruta" class="form-control" id="hizo_prueba_ruta" <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "disabled='disabled'";}?>>
										<option value="SI">SI</option>
										<option value="NO">NO</option>
								<?php //while ($row_EstadoLlamada = sqlsrv_fetch_array($SQL_EstadoLlamada)) {?>
										<!--option value="<?php echo $row_EstadoLlamada['Cod_Estado']; ?>" <?php if ((isset($row['Cod_Estado'])) && (strcmp($row_EstadoLlamada['Cod_Estado'], $row['Cod_Estado']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_EstadoLlamada['NombreEstado']; ?></option -->
								<?php //}?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Campaña autorizada por cliente</label>
								<select name="campana_autorizada_cliente" class="form-control" id="campana_autorizada_cliente" <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "disabled='disabled'";}?>>
									<option value="SI">SI</option>
									<option value="NO">NO</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Nivel de combustible</label>
								<select name="nivel_combustible" class="form-control" id="nivel_combustible" <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "disabled='disabled'";}?>>
									<option value="1/4">1/4</option>
									<option value="1/2">1/2</option>
									<option value="3/4">3/4</option>
									<option value="Full">Full</option>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Medio por el cual se informo campaña</label>
								<select name="medio_informa_campana" class="form-control" id="medio_informa_campana" <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "disabled='disabled'";}?>>
										<option value="N/A">N/A</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">KM actual <span class="text-danger">*</span></label>
								<input autocomplete="off" name="km_actual" required="required" type="text" class="form-control" id="km_actual" maxlength="100">
							</div>
							<div class="col-lg-4">
								<label class="control-label">No. Campaña <span class="text-danger">*</span></label>
								<input autocomplete="off" name="no_campana" required="required" type="text" class="form-control" id="no_campana" maxlength="100">
							</div>
						</div>
						<!-- Inicio, crono-info -->
						<div class="form-group">
							<div class="col-lg-4 border-bottom ">
								<label class="control-label text-danger">Información cronológica de la recepción</label>
							</div>
						</div>
						<div class="form-group">
							<!-- Inicio, Componente Fecha y Hora -->
							<div class="col-lg-6">
								<div class="row">
									<label class="col-lg-6 control-label" style="text-align: left !important;">Fecha y hora de creación <span class="text-danger">*</span></label>
								</div>
								<div class="row">
									<div class="col-lg-6 input-group date">
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaCreacion" type="text" autocomplete="off" class="form-control" id="FechaCreacion" value="<?php if (($type_frm == 1) && ($row['FechaCreacion']->format('Y-m-d')) != "1900-01-01") {echo $row['FechaCreacion']->format('Y-m-d');} else {echo date('Y-m-d');}?>" readonly='readonly' placeholder="YYYY-MM-DD" required>
									</div>
									<div class="col-lg-6 input-group clockpicker" data-autoclose="true">
										<input name="HoraCreacion" id="HoraCreacion" type="text" autocomplete="off" class="form-control" value="<?php if (($type_frm == 1) && ($row['FechaCreacion']->format('Y-m-d')) != "1900-01-01") {echo $row['FechaCreacion']->format('H:i');} else {echo date('H:i');}?>" readonly='readonly' placeholder="hh:mm" required>
										<span class="input-group-addon">
											<span class="fa fa-clock-o"></span>
										</span>
									</div>
								</div>
							</div>
							<!-- Fin, Componente Fecha y Hora -->
							<!-- Inicio, Componente Fecha y Hora -->
							<div class="col-lg-6">
								<div class="row">
									<label class="col-lg-6 control-label" style="text-align: left !important;">Fecha y hora de ingreso <span class="text-danger">*</span></label>
								</div>
								<div class="row">
									<div class="col-lg-6 input-group date">
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="fecha_ingreso" type="text" autocomplete="off" class="form-control" id="fecha_ingreso" value="<?php if (($type_frm == 1) && ($row['fecha_ingreso']->format('Y-m-d')) != "1900-01-01") {echo $row['fecha_ingreso']->format('Y-m-d');} else {echo date('Y-m-d');}?>" placeholder="YYYY-MM-DD" required>
									</div>
									<div class="col-lg-6 input-group clockpicker" data-autoclose="true">
										<input name="hora_ingreso" id="hora_ingreso" type="text" autocomplete="off" class="form-control" value="<?php if (($type_frm == 1) && ($row['fecha_ingreso']->format('Y-m-d')) != "1900-01-01") {echo $row['fecha_ingreso']->format('H:i');} else {echo date('H:i');}?>" placeholder="hh:mm" required>
										<span class="input-group-addon">
											<span class="fa fa-clock-o"></span>
										</span>
									</div>
								</div>
							</div>
							<!-- Fin, Componente Fecha y Hora -->
						</div>
						<div class="form-group">
							<!-- Inicio, Componente Fecha y Hora -->
							<div class="col-lg-6">
								<div class="row">
									<label class="col-lg-6 control-label" style="text-align: left !important;">Fecha y hora Aprox. Entrega <span class="text-danger">*</span></label>
								</div>
								<div class="row">
									<div class="col-lg-6 input-group date">
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="fecha_aprox_entrega" type="text" autocomplete="off" class="form-control" id="fecha_aprox_entrega" value="<?php if (($type_frm == 1) && ($row['fecha_aprox_entrega']->format('Y-m-d')) != "1900-01-01") {echo $row['fecha_aprox_entrega']->format('Y-m-d');} //else {echo date('Y-m-d');}?>" placeholder="YYYY-MM-DD" required>
									</div>
									<div class="col-lg-6 input-group clockpicker" data-autoclose="true">
										<input name="hora_aprox_entrega" id="hora_aprox_entrega" type="text" autocomplete="off" class="form-control" value="<?php if (($type_frm == 1) && ($row['fecha_aprox_entrega']->format('Y-m-d')) != "1900-01-01") {echo $row['fecha_aprox_entrega']->format('H:i');} //else {echo date('H:i');}?>" placeholder="hh:mm" required>
										<span class="input-group-addon">
											<span class="fa fa-clock-o"></span>
										</span>
									</div>
								</div>
							</div>
							<!-- Fin, Componente Fecha y Hora -->
							<!-- Inicio, Componente Fecha y Hora -->
							<div class="col-lg-6">
								<div class="row">
									<label class="col-lg-6 control-label" style="text-align: left !important;">Fecha hora propietario autoriza campaña</label>
								</div>
								<div class="row">
									<div class="col-lg-6 input-group date">
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="fecha_autoriza_campana" type="text" autocomplete="off" class="form-control" id="fecha_autoriza_campana" value="<?php if (($type_frm == 1) && ($row['fecha_autoriza_campana']->format('Y-m-d')) != "1900-01-01") {echo $row['fecha_autoriza_campana']->format('Y-m-d');} //else {echo date('Y-m-d');}?>" placeholder="YYYY-MM-DD">
									</div>
									<div class="col-lg-6 input-group clockpicker" data-autoclose="true">
										<input name="hora_autoriza_campana" id="hora_autoriza_campana" type="text" autocomplete="off" class="form-control" value="<?php if (($type_frm == 1) && ($row['fecha_autoriza_campana']->format('Y-m-d')) != "1900-01-01") {echo $row['fecha_autoriza_campana']->format('H:i');} //else {echo date('H:i');}?>" placeholder="hh:mm">
										<span class="input-group-addon">
											<span class="fa fa-clock-o"></span>
										</span>
									</div>
								</div>
							</div>
							<!-- Fin, Componente Fecha y Hora -->
						</div>
						<!-- Fin, crono-info -->
					</div>
				</div>
				<!-- IBOX, Fin -->
				<!-- IBOX, Inicio -->
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-list"></i> Datos piezas de vehículo</h5>
						 <a class="collapse-link pull-right" style="color: white;">
							<i class="fa fa-chevron-up"></i>
						</a>
					</div>
					<div class="ibox-content">
						<?php $count_rp = 0;?>

						<?php while ($row_Pregunta = sqlsrv_fetch_array($SQL_Preguntas)) {?>
							<?php $count_rp++;?>

							<input type="hidden" name="<?php echo "id_pregunta_$count_rp"; ?>" id="<?php echo "id_pregunta_$count_rp"; ?>" value="<?php echo $row_Pregunta['id_recepcion_pregunta']; ?>">
							<input type="hidden" name="<?php echo "pregunta_$count_rp"; ?>" id="<?php echo "pregunta_$count_rp"; ?>" value="<?php echo $row_Pregunta['recepcion_pregunta']; ?>">

							<div class="form-group">
								<div class="col-lg-4 border-bottom ">
									<label class="control-label text-danger"><?php echo $row_Pregunta['recepcion_pregunta']; ?></label>
								</div>
							</div>
							<div class="form-group">
								<label class="col-lg-1 control-label">Disponibilidad</label>
								<div class="col-lg-2">
									<select class="form-control" name="<?php echo "p_disponible_$count_rp"; ?>" id="<?php echo "p_disponible_$count_rp"; ?>" <?php if (false) {echo "disabled='disabled'";}?>>
										<option value="SI" <?php if ((isset($row["p_disponible_$count_rp"])) && (strcmp("si", $row["p_disponible_$count_rp"]) == 0)) {echo "selected=\"selected\"";}?>>
											Si
										</option>
										<option value="NO" <?php if ((isset($row["p_disponible_$count_rp"])) && (strcmp("no", $row["p_disponible_$count_rp"]) == 0)) {echo "selected=\"selected\"";}?>>
											No
										</option>
									</select>
								</div>
								<label class="col-lg-1 control-label">Estado</label>
								<div class="col-lg-2">
									<select class="form-control" name="<?php echo "p_estado_$count_rp"; ?>" id="<?php echo "p_estado_$count_rp"; ?>" <?php if (false) {echo "disabled='disabled'";}?>>
										<option value="BUENO" <?php if ((isset($row["p_estado_$count_rp"])) && (strcmp("BUENO", $row["p_estado_$count_rp"]) == 0)) {echo "selected=\"selected\"";}?>>
											Bueno
										</option>
										<option value="MALO" <?php if ((isset($row["p_estado_$count_rp"])) && (strcmp("MALO", $row["p_estado_$count_rp"]) == 0)) {echo "selected=\"selected\"";}?>>
											Malo
										</option>
									</select>
								</div>
								<?php if ($row_Pregunta["profundidad_llantas"] == 'Y') {?>
									<!-- Se excluyeron algunas validaciones innecesarias de los otros campos -->
									<label class="col-lg-1 control-label">Profundidad Llantas (Máximo 24mm)</label>
									<div class="col-lg-2">
										<input autocomplete="off" name="<?php echo "profundidad_llantas_$count_rp"; ?>" id="<?php echo "profundidad_llantas_$count_rp"; ?>" type="number" class="form-control" min="0" max="24">
									</div>
								<?php }?>
							</div>
						<?php }?>
					</div>
				</div>
				<!-- IBOX, Fin -->
				<!-- IBOX, Inicio -->
				<?php if (PermitirFuncion(1708)) {?>
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-image"></i> Registros fotográficos</h5>
						 <a class="collapse-link pull-right" style="color: white;">
							<i class="fa fa-chevron-up"></i>
						</a>
					</div>
					<div class="ibox-content">
						<!-- Inicio, Foto 1 -->
						<div class="form-group">
							<label class="col-lg-1 control-label">Frente <span class="text-danger">*</span></label>
							<div class="col-lg-5">
								<div class="fileinput fileinput-new input-group" data-provides="fileinput">
									<div class="form-control" data-trigger="fileinput">
										<i class="glyphicon glyphicon-file fileinput-exists"></i>
									<span class="fileinput-filename"></span>
									</div>
									<span class="input-group-addon btn btn-default btn-file">
										<span class="fileinput-new">Seleccionar</span>
										<span class="fileinput-exists">Cambiar</span>
										<input name="Img1" type="file" id="Img1" onchange="uploadImage('Img1')" required="required"/>
									</span>
									<a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Quitar</a>
								</div>
								<div class="row">
									<div id="msgImg1" style="display:none" class="alert alert-info">
										<i class="fa fa-info-circle"></i> <span>Imagen cargada éxitosamente.<span>
									</div>
								</div>
							</div>
							<div class="col-lg-5">
								<img id="viewImg1" style="max-width: 100%; height: 100px;" src="">
							</div>
						</div>
						<!-- Inicio, Foto 1 -->
						<!-- Inicio, Foto 2 -->
						<div class="form-group">
							<label class="col-lg-1 control-label">Lateral Izquierdo <span class="text-danger">*</span></label>
							<div class="col-lg-5">
								<div class="fileinput fileinput-new input-group" data-provides="fileinput">
									<div class="form-control" data-trigger="fileinput">
										<i class="glyphicon glyphicon-file fileinput-exists"></i>
									<span class="fileinput-filename"></span>
									</div>
									<span class="input-group-addon btn btn-default btn-file">
										<span class="fileinput-new">Seleccionar</span>
										<span class="fileinput-exists">Cambiar</span>
										<input name="Img2" type="file" id="Img2" onchange="uploadImage('Img2')" required="required"/>
									</span>
									<a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Quitar</a>
								</div>
								<div class="row">
									<div id="msgImg2" style="display:none" class="alert alert-info">
										<i class="fa fa-info-circle"></i> <span>Imagen cargada éxitosamente.<span>
									</div>
								</div>
							</div>
							<div class="col-lg-5">
								<img id="viewImg2" style="max-width: 100%; height: 100px;" src="">
							</div>
						</div>
						<!-- Fin, Foto 2 -->
						<!-- Inicio, Foto 3 -->
						<div class="form-group">
							<label class="col-lg-1 control-label">Lateral Derecho <span class="text-danger">*</span></label>
							<div class="col-lg-5">
								<div class="fileinput fileinput-new input-group" data-provides="fileinput">
									<div class="form-control" data-trigger="fileinput">
										<i class="glyphicon glyphicon-file fileinput-exists"></i>
									<span class="fileinput-filename"></span>
									</div>
									<span class="input-group-addon btn btn-default btn-file">
										<span class="fileinput-new">Seleccionar</span>
										<span class="fileinput-exists">Cambiar</span>
										<input name="Img3" type="file" id="Img3" onchange="uploadImage('Img3')" required="required"/>
									</span>
									<a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Quitar</a>
								</div>
								<div class="row">
									<div id="msgImg3" style="display:none" class="alert alert-info">
										<i class="fa fa-info-circle"></i> <span>Imagen cargada éxitosamente.<span>
									</div>
								</div>
							</div>
							<div class="col-lg-5">
								<img id="viewImg3" style="max-width: 100%; height: 100px;" src="">
							</div>
						</div>
						<!-- Fin, Foto 3 -->
						<!-- Inicio, Foto 4 -->
						<div class="form-group">
							<label class="col-lg-1 control-label">Trasero <span class="text-danger">*</span></label>
							<div class="col-lg-5">
								<div class="fileinput fileinput-new input-group" data-provides="fileinput">
									<div class="form-control" data-trigger="fileinput">
										<i class="glyphicon glyphicon-file fileinput-exists"></i>
									<span class="fileinput-filename"></span>
									</div>
									<span class="input-group-addon btn btn-default btn-file">
										<span class="fileinput-new">Seleccionar</span>
										<span class="fileinput-exists">Cambiar</span>
										<input name="Img4" type="file" id="Img4" onchange="uploadImage('Img4')" required="required"/>
									</span>
									<a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Quitar</a>
								</div>
								<div class="row">
									<div id="msgImg4" style="display:none" class="alert alert-info">
										<i class="fa fa-info-circle"></i> <span>Imagen cargada éxitosamente.<span>
									</div>
								</div>
							</div>
							<div class="col-lg-5">
								<img id="viewImg4" style="max-width: 100%; height: 100px;" src="">
							</div>
						</div>
						<!-- Fin, Foto 4 -->
						<!-- Inicio, Foto 5 -->
						<div class="form-group">
							<label class="col-lg-1 control-label">Capot <span class="text-danger">*</span></label>
							<div class="col-lg-5">
								<div class="fileinput fileinput-new input-group" data-provides="fileinput">
									<div class="form-control" data-trigger="fileinput">
										<i class="glyphicon glyphicon-file fileinput-exists"></i>
									<span class="fileinput-filename"></span>
									</div>
									<span class="input-group-addon btn btn-default btn-file">
										<span class="fileinput-new">Seleccionar</span>
										<span class="fileinput-exists">Cambiar</span>
										<input name="Img5" type="file" id="Img5" onchange="uploadImage('Img5')" required="required"/>
									</span>
									<a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Quitar</a>
								</div>
								<div class="row">
									<div id="msgImg5" style="display:none" class="alert alert-info">
										<i class="fa fa-info-circle"></i> <span>Imagen cargada éxitosamente.<span>
									</div>
								</div>
							</div>
							<div class="col-lg-5">
								<img id="viewImg5" style="max-width: 100%; height: 100px;" src="">
							</div>
						</div>
						<!-- Fin, Foto 5 -->
						<div class="form-group">
							<label class="col-lg-1 control-label">Observaciones <span class="text-danger">*</span></label>
							<div class="col-lg-8">
								<textarea name="observaciones" id="observaciones" rows="5" type="text" maxlength="3000" class="form-control" required="required" <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "readonly='readonly'";}?>><?php if (($type_frm == 1) || ($sw_error == 1)) {echo utf8_decode($row['ComentariosCierre']);}?></textarea>
							</div>
						</div>
					</div>
				</div>
				<?php }?>
				<!-- IBOX, Fin -->
				<!-- IBOX, Inicio -->
				<?php if (PermitirFuncion(1708)) {?>
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-pencil-square-o"></i> Firmas</h5>
						 <a class="collapse-link pull-right" style="color: white;">
							<i class="fa fa-chevron-up"></i>
						</a>
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<label class="col-lg-1 control-label">Responsable del cliente <span class="text-danger">*</span></label>
							<div class="col-lg-4">
								<input autocomplete="off" name="responsable_cliente" type="text" class="form-control" required="required" id="responsable_cliente"  <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "readonly='readonly'";}?> value="<?php if (($type_frm == 1) || ($sw_error == 1)) {echo $row['ResponsableCliente'];}?>">
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Cédula de contacto <span class="text-danger">*</span></label>
							<div class="col-lg-4">
								<input autocomplete="off" name="cedula_responsable_cliente" type="text" class="form-control" required="required" id="cedula_responsable_cliente"  <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "readonly='readonly'";}?>>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Teléfono de contacto <span class="text-danger">*</span></label>
							<div class="col-lg-4">
								<input autocomplete="off" name="telefono_responsable_cliente" type="text" class="form-control" required="required" id="telefono_responsable_cliente"  <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "readonly='readonly'";}?>>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Correo de contacto <span class="text-danger">*</span></label>
							<div class="col-lg-4">
								<input autocomplete="off" name="correo_responsable_cliente" type="email" class="form-control" required="required" id="correo_responsable_cliente"  <?php if (($type_frm == 1) && ($row['Cod_Estado'] == '-1')) {echo "readonly='readonly'";}?>>
							</div>
						</div>
						<br><br>
						<div class="form-group">
							<label class="col-lg-1">Firma del cliente <span class="text-danger">*</span></label>
							<?php if ($type_frm == 1 && $row['FirmaCliente'] != "") {?>
							<div class="col-lg-10">
								<span class="badge badge-primary">Firmado</span>
							</div>
							<?php } else { //LimpiarDirTempFirma();?>
							<div class="col-lg-5">
								<button class="btn btn-primary" type="button" id="FirmaCliente" onClick="AbrirFirma('SigCliente');"><i class="fa fa-pencil-square-o"></i> Realizar firma</button>
								<br>
								<input type="text" id="SigCliente" name="SigCliente" value="" form="recepcionForm" required="required" readonly="readonly" style="width: 0; margin-left: -7px; visibility: hidden;"/>
								<div id="msgInfoSigCliente" style="display: none;" class="alert alert-info"><i class="fa fa-info-circle"></i> El documento ya ha sido firmado.</div>
							</div>
							<div class="col-lg-5">
								<img id="ImgSigCliente" style="display: none; max-width: 100%; height: auto;" src="" alt="" />
							</div>
							<?php }?>
						</div>
					</div>
				</div>
				<?php }?>
				<!-- IBOX, Fin -->

<!-- Inicio, relacionado al $return -->
<?php
$EliminaMsg = array("&a=" . base64_encode("OK_FrmAdd"), "&a=" . base64_encode("OK_FrmUpd"), "&a=" . base64_encode("OK_FrmDel")); //Eliminar mensajes

if (isset($_GET['return'])) {
    $_GET['return'] = str_replace($EliminaMsg, "", base64_decode($_GET['return']));
}
if (isset($_GET['return'])) {
    $return = base64_decode($_GET['pag']) . "?" . $_GET['return'];
} else {
    // Stiven Muñoz Murillo, 10/01/2022
    $return = "consultar_frm_recepcion_vehiculo.php?id=" . $frm;
}
?>
<!-- Fin, relacionado al $return -->

				<!-- Campos ocultos -->
				<input type="hidden" id="return" name="return" value="<?php echo base64_encode($return); ?>" />
			</form>


			<!-- Stiven Muñoz Murillo, 10/01/2022 -->
			<div class="ibox">
				<div class="ibox-title bg-success">
					<h5 class="collapse-link"><i class="fa fa-paperclip"></i> Fotos adicionales</h5>
					<a class="collapse-link pull-right" style="color: white;">
						<i class="fa fa-chevron-up"></i>
					</a>
				</div>
				<div class="ibox-content">
					<?php if ( /*$row['IdAnexoLlamada'] != 0*/false) {?>
						<div class="form-group">
							<div class="col-xs-12">
								<?php while ($row_AnexoLlamada = sqlsrv_fetch_array($SQL_AnexoLlamada)) {?>
									<?php $Icon = IconAttach($row_AnexoLlamada['FileExt']);?>
									<div class="file-box">
										<div class="file">
											<a href="attachdownload.php?file=<?php echo base64_encode($row_AnexoLlamada['AbsEntry']); ?>&line=<?php echo base64_encode($row_AnexoLlamada['Line']); ?>" target="_blank">
												<div class="icon">
													<i class="<?php echo $Icon; ?>"></i>
												</div>
												<div class="file-name">
													<?php echo $row_AnexoLlamada['NombreArchivo']; ?>
													<br/>
													<small><?php echo $row_AnexoLlamada['Fecha']; ?></small>
												</div>
											</a>
										</div>
									</div>
								<?php }?>
							</div>
						</div>
					<?php } else {echo "<!--p>Sin anexos.</p-->";}?>
					<div class="row">
						<form action="upload.php?persistent=recepcion_vehiculos" class="dropzone" id="dropzoneForm" name="dropzoneForm">
							<?php //if ($sw_error == 0) {LimpiarDirTemp();}?>
							<div class="fallback">
								<input name="File" id="File" type="file" form="dropzoneForm" />
							</div>
						</form>
					</div>
				</div>
			</div>
			<!-- Fin Anexos -->

			<!-- Botones de acción al final del formulario, SMM -->
			   <div class="form-group">
					<div class="col-lg-9">
						<?php if ($type_frm == 0) {?>
							<button class="btn btn-primary" form="recepcionForm" type="submit" id="Crear"><i class="fa fa-check"></i> Registrar formulario</button>
						<?php }?>
						<a href="<?php echo $return; ?>" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
					</div>
				</div>
			<!-- Pendiente a agregar al formulario, SMM -->
		   </div>
			</div>
          </div>
        </div>
        <!-- InstanceEndEditable -->
        <?php include "includes/footer.php";?>

    </div>
</div>
<?php include "includes/pie.php";?>
<!-- InstanceBeginEditable name="EditRegion4" -->

<script>
var anexos = []; // SMM, 16/02/2022

// Stiven Muñoz Murillo, 11/01/2022
Dropzone.options.dropzoneForm = {
	paramName: "File", // The name that will be used to transfer the file
	maxFilesize: "<?php echo ObtenerVariable("MaxSizeFile"); ?>", // MB
	maxFiles: "<?php echo ObtenerVariable("CantidadArchivos"); ?>",
	uploadMultiple: true,
	addRemoveLinks: true,
	dictRemoveFile: "Quitar",
	acceptedFiles: "<?php echo ObtenerVariable("TiposArchivos"); ?>",
	dictDefaultMessage: "<strong>Haga clic aqui para cargar anexos</strong><br>Tambien puede arrastrarlos hasta aqui<br><h4><small>(máximo <?php echo ObtenerVariable("CantidadArchivos"); ?> archivos a la vez)<small></h4>",
	dictFallbackMessage: "Tu navegador no soporta cargue de archivos mediante arrastrar y soltar",
	removedfile: function(file) {
			var indice = anexos.indexOf(file.name);
			if (indice !== -1) {
				anexos.splice(indice, 1);
			}

			$.get( "includes/procedimientos.php", {
				type: "3",
				nombre: file.name
			}).done(function( data ) {
				var _ref;
				return (_ref = file.previewElement) !== null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
			});
		},
	init: function(file) {
		this.on("addedfile", file => {
			anexos.push(file.name); // SMM, 16/02/2022
			console.log("Line 1057, Dropzone(addedfile)", file.name);

			// SMM, 28/09/2022
			$("#Crear").prop("disabled", true);
    	});
	},
	queuecomplete: function() {
		console.log("Line 1087, Dropzone(queuecomplete)");

		// SMM, 28/09/2022
		$("#Crear").prop("disabled", false);
	}
};
</script>

<script>
var photos = []; // SMM, 11/02/2022

// Stiven Muñoz Murillo, 11/01/2022
function uploadImage(refImage) {
	$('.ibox-content').toggleClass('sk-loading', true); // Carga iniciada.

	var formData = new FormData();
	var file = $(`#${refImage}`)[0].files[0];

	console.log("Line 1073, uploadImage", file);
	formData.append('image', file);

	if(typeof file !== 'undefined'){
		fileSize = returnFileSize(file.size)

		if(fileSize.heavy) {
			console.error("Heavy");

			mostrarAlerta(`msg${refImage}`, 'danger', `La imagen no puede superar los 2MB, actualmente pesa ${fileSize.size}`);
			$('.ibox-content').toggleClass('sk-loading', false); // Carga terminada.
		} else {
			// Inicio, AJAX
			$.ajax({
				url: 'upload_image.php?persistent=recepcion_vehiculos',
				type: 'post',
				data: formData,
				contentType: false,
				processData: false,
				success: function(response) {
					json_response = JSON.parse(response);

					photo_name = json_response.nombre;
					photo_route = json_response.directorio + photo_name;

					testImage(photo_route).then(success => {
						console.log(success);
						console.log("Line 1100, testImage", photo_route);

						photos[refImage] = photo_name; // SMM, 11/02/2022

						$(`#view${refImage}`).attr("src", photo_route);
						mostrarAlerta(`msg${refImage}`, 'info', `Imagen cargada éxitosamente con un peso de ${fileSize.size}`);
					})
					.catch(error => {
						console.error(error);
						console.error(response);

						mostrarAlerta(`msg${refImage}`, 'danger', 'Error al cargar la imagen.');
					});

					$('.ibox-content').toggleClass('sk-loading', false); // Carga terminada.
				},
				error: function(response) {
					console.error("server error")
					console.error(response);

					mostrarAlerta(`msg${refImage}`, 'danger', 'Error al cargar la imagen en el servidor.');
					$('.ibox-content').toggleClass('sk-loading', false); // Carga terminada.
				}
			});
			// Fin, AJAX
		}
	} else {
		console.log("Ninguna imagen seleccionada");

		$(`#msg${refImage}`).css("display", "none");
		$(`#view${refImage}`).attr("src", "");

		$('.ibox-content').toggleClass('sk-loading', false); // Carga terminada.
	}
	return false;
}

// Stiven Muñoz Murillo, 13/01/2022
function mostrarAlerta(id, tipo, mensaje) {
	$(`#${id}`).attr("class", `alert alert-${tipo}`);
	$(`#${id} span`).text(mensaje);
	$(`#${id}`).css("display", "inherit");
}

function returnFileSize(number) {
	if (number < 1024) {
        return { heavy: false, size: (number + 'bytes') };
    } else if (number >= 1024 && number < 1048576) {
		number = (number / 1024).toFixed(1);
        return { heavy: false, size: (number + 'KB') };
    } else if (number >= 1048576) {
		number = (number / 1048576).toFixed(1);
		if(number > 2) {
			return { heavy: true, size: (number + 'MB') };
		} else {
			return { heavy: false, size: (number + 'MB') };
		}
    } else {
		return { heavy: true, size: Infinity }
	}
}

// Reference, https://stackoverflow.com/questions/9714525/javascript-image-url-verify
function testImage(url, timeoutT) {
    return new Promise(function (resolve, reject) {
        var timeout = timeoutT || 5000;
        var timer, img = new Image();
        img.onerror = img.onabort = function () {
            clearTimeout(timer);
            reject("error loading image");
        };
        img.onload = function () {
            clearTimeout(timer);
            resolve("image loaded successfully");
        };
        timer = setTimeout(function () {
            // reset .src to invalid URL so it stops previous
            // loading, but doesn't trigger new load
            img.src = "//!!!!/test.jpg";
            reject("timeout");
        }, timeout);
        img.src = url;
    });
}
</script>

<script>
$(document).ready(function(){
	maxLength('observaciones'); // SMM, 02/03/2022

	var bandera_fechas = false; // SMM, 25/02/2022
	$('#recepcionForm').on('submit', function (event) {
		// Stiven Muñoz Murillo, 08/02/2022
		event.preventDefault();

		// Stiven Muñoz Murillo, 25/02/2022
		let d1 = new Date(`${$('#fecha_ingreso').val()} ${$('#hora_ingreso').val()}`);
		let d2 = new Date(`${$('#fecha_aprox_entrega').val()} ${$('#hora_aprox_entrega').val()}`);

		console.log(d1);
		console.log(d2);

		// Stiven Muñoz Murillo, 25/02/2022
		bandera_fechas = (d1 > d2) ? true:false;
	});

	$("#recepcionForm").validate({
		submitHandler: function(form){
			if(bandera_fechas) {
				Swal.fire({
					"title": "¡Ha ocurrido un error!",
					"text": "La fecha de ingreso no puede superar a la fecha de entrega.",
					"icon": "warning"
				});
			} else {
				Swal.fire({
					title: "¿Desea continuar con el registro?",
					icon: "question",
					showCancelButton: true,
					confirmButtonText: "Si, confirmo",
					cancelButtonText: "No"
				}).then((result) => {
					if (result.isConfirmed) {
						$('.ibox-content').toggleClass('sk-loading', true); // Carga iniciada.

						let formData = new FormData(form);
						Object.entries(photos).forEach(([key, value]) => formData.append(key, value));
						Object.entries(anexos).forEach(([key, value]) => formData.append(`Anexo${key}`, value));

						// Agregar valores de las listas
						formData.append("id_llamada_servicio", $("#id_llamada_servicio").val());
						formData.append("id_marca", $("#id_marca").val());
						formData.append("id_linea", $("#id_linea").val());
						formData.append("id_annio", $("#id_annio").val());
						formData.append("id_color", $("#id_color").val());

						let json = Object.fromEntries(formData);
						localStorage.recepcionForm = JSON.stringify(json);

						console.log("Line 1790", json);

						// Inicio, AJAX
						$.ajax({
							url: 'frm_recepcion_vehiculo_ws.php',
							type: 'POST',
							data: formData,
							processData: false,  // tell jQuery not to process the data
							contentType: false,   // tell jQuery not to set contentType
							success: function(response) {
								console.log("Line 1273", response);

								try {
									let json_response = JSON.parse(response);
									Swal.fire(json_response).then(() => {
										if (json_response.hasOwnProperty('return')) {
											window.location = json_response.return;
										}
									});
								} catch (error) {
									console.log("Line 1283", error);
								}

								$('.ibox-content').toggleClass('sk-loading', false); // Carga terminada.
							},
							error: function(response) {
								console.error("server error")
								console.error(response);

								$('.ibox-content').toggleClass('sk-loading', false); // Carga terminada.
							}
						});
						// Fin, AJAX
					} else {
						console.log("Registro NO confirmado.")
					}
				}); // SMM, 14/06/2022
			}
		}
	});

	$(".alkin").on('click', function(){
			$('.ibox-content').toggleClass('sk-loading');
	});

	// Inicio, sección de fechas y horas.
	if(!$('#fecha_ingreso').prop('readonly')){
		$('#fecha_ingreso').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			format: 'yyyy-mm-dd',
			todayHighlight: true,
			endDate: '<?php echo date('Y-m-d'); ?>'
		});

		$('#hora_ingreso').clockpicker({
			donetext: 'Done'
		});
	}
	if(!$('#fecha_autoriza_campana').prop('readonly')){
		$('#fecha_autoriza_campana').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			format: 'yyyy-mm-dd',
			todayHighlight: true
		});

		$('#hora_autoriza_campana').clockpicker({
			donetext: 'Done'
		});
	}
	if(!$('#fecha_aprox_entrega').prop('readonly')){
		$('#fecha_aprox_entrega').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			format: 'yyyy-mm-dd',
			todayHighlight: true
		});

		$('#hora_aprox_entrega').clockpicker({
			donetext: 'Done'
		});
	}
	// Fin, sección de fechas y horas.


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
				var value = $("#socio_negocio").getSelectedItemData().CodigoCliente;
				$("#id_socio_negocio").val(value).trigger("change");
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

	$("#socio_negocio").easyAutocomplete(options);
	$("#ciudad").easyAutocomplete(options2);

	<?php if ($dt_LS == 1) {?>
		$('#SucursalCliente option:not(:selected)').attr('disabled',true);
		$('#id_llamada_servicio option:not(:selected)').attr('disabled',true);

		// Stiven Muñoz Murillo, 20/01/2022
		$('#id_llamada_servicio').trigger('change');
		$('#id_socio_negocio').trigger('change');
	<?php }?>
});
</script>

<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>