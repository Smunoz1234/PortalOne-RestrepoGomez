<?php require_once "includes/conexion.php";
PermitirAcceso(311);

$sw = 0;

//Estado llamada
$SQL_EstadoLlamada = Seleccionar('uvw_tbl_EstadoLlamada', '*');

//Sucursal
$ParamSucursal = array(
    "'" . $_SESSION['CodUser'] . "'",
);
$SQL_Sucursal = EjecutarSP('sp_ConsultarSucursalesUsuario', $ParamSucursal);

if (isset($_GET['Sucursal']) && $_GET['Sucursal'] != "") {
    //Serie de llamada
    $ParamSerie = array(
        "'" . $_SESSION['CodUser'] . "'",
        "'191'",
    );
    $SQL_Series = EjecutarSP('sp_ConsultarSeriesDocumentos', $ParamSerie);
}

//Fechas
if (isset($_GET['FechaInicial']) && $_GET['FechaInicial'] != "") {
    $FechaInicial = $_GET['FechaInicial'];
    $sw = 1;
} else {
    //Restar 7 dias a la fecha actual
    $fecha = date('Y-m-d');
    $nuevafecha = strtotime('-' . ObtenerVariable("DiasRangoFechasDocSAP") . ' day');
    $nuevafecha = date('Y-m-d', $nuevafecha);
    $FechaInicial = $nuevafecha;
}
if (isset($_GET['FechaFinal']) && $_GET['FechaFinal'] != "") {
    $FechaFinal = $_GET['FechaFinal'];
    $sw = 1;
} else {
    $FechaFinal = date('Y-m-d');
}

if ($sw == 1) {
    // SMM, 09/08/2022
    $estados = isset($_GET['EstadosLlamada']) ? implode(";", $_GET['EstadosLlamada']) : "";

    $Param = array(
        "'" . FormatoFecha($FechaInicial) . "'",
        "'" . FormatoFecha($FechaFinal) . "'",
        "'" . $_GET['Series'] . "'",
        "'" . strtolower($_SESSION['User']) . "'",
        "'" . $estados . "'", // SMM, 09/08/2022
        "'" . $_GET['BasadoEscaneados'] . "'", // SMM, 09/08/2022
        $_GET['reload'] ?? 0,
    );
    $SQL = EjecutarSP('usp_tbl_CierreOrdenesServicio_Sel', $Param);
    $row = sqlsrv_fetch_array($SQL);
}

// Estado servicio llamada. SMM, 09/12/2022
$SQL_EstadoServicioLlamada = Seleccionar('uvw_Sap_tbl_LlamadasServiciosEstadoServicios', '*', '', 'DeEstadoServicio');

// Cancelado por llamada. SMM, 09/12/2022
$SQL_CanceladoPorLlamada = Seleccionar('uvw_Sap_tbl_LlamadasServiciosCanceladoPor', '*', '', 'DeCanceladoPor', 'DESC');
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Cierre de OT en lote | <?php echo NOMBRE_PORTAL; ?></title>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.select2-container{ width: 100% !important; }
	.panel-resizable {
		resize: vertical;
		overflow: auto
	}

	/** SMM, 19/09/2022 */
	.swal2-container, .clockpicker-popover {
		z-index: 9999999 !important;
	}

	.tooltip-inner {
    	min-width: 200px !important;
	}

	/**
	* Iconos del panel de información.
	* SMM, 15/09/2022
	*/
	.panel-heading  a:before {
		font-family: 'Glyphicons Halflings';
		content: "\e114";
		float: right;
		transition: all 0.5s;
	}
	.panel-heading.active a:before {
		-webkit-transform: rotate(180deg);
		-moz-transform: rotate(180deg);
		transform: rotate(180deg);
	}
</style>

<script type="text/javascript">
	$(document).ready(function() {
		$("#NombreClienteActividad").change(function(){
			var NomCliente=document.getElementById("NombreClienteActividad");
			var Cliente=document.getElementById("ClienteActividad");
			if(NomCliente.value==""){
				Cliente.value="";
			}
		});

		$("#Sucursal").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Sucursal=document.getElementById('Sucursal').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=26&id="+Sucursal+"&tdoc=191&taccion=3",
				success: function(response){
					$('#Series').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});

	});
</script>
<script>
function Validar(Tipo){
	Swal.fire({
		title: "¿Está seguro que desea ejecutar el proceso?",
		text: "Se cerrarán los documentos listados",
		icon: "info",
		showCancelButton: true,
		confirmButtonText: "Si, confirmo",
		cancelButtonText: "No"
	}).then((result) => {
		if (result.isConfirmed) {
			EjecutarProceso(Tipo);
		}
	});
}

function EjecutarProceso(Tipo){
	$('.ibox-content').toggleClass('sk-loading',true);
		var Evento = document.getElementById("IdEvento").value;
		var FechaInicial = document.getElementById("FechaInicial").value;
		var FechaFinal = document.getElementById("FechaFinal").value;
		var Sucursal = document.getElementById("Sucursal").value;
		var Series = document.getElementById("Series").value;
		var DG_Actividades=document.getElementById("DG_Actividades");
		var DG_Llamadas=document.getElementById("DG_Llamadas");

		$.ajax({
			url:"ajx_ejecutar_json.php",
			data:{
				type:2,Evento:Evento,FechaInicial:FechaInicial,FechaFinal:FechaFinal,Sucursal:Sucursal,Serie:Series,Tipo:Tipo,
				<?php if (isset($_GET['BasadoEscaneados'])) {echo "BasadoEscaneados: " . $_GET['BasadoEscaneados'];}?>
			},
			dataType:'json',
			success: function(data){
				// SMM, 19/09/2022
				console.log("Respuesta ajx_ejecutar_json(2): ", data.Parametros);

				if(data.Estado==1){
					$("#UltEjecucion").html(MostrarFechaHora());
					DG_Actividades.src="detalle_cierre_ot_lote_actividades.php";
					DG_Llamadas.src="detalle_cierre_ot_lote_llamadas.php";
					ConsultarCant();
					$('.ibox-content').toggleClass('sk-loading',false);
				}
				$('.ibox-content').toggleClass('sk-loading',false);
				Swal.fire({
					title: data.Title,
					text: data.Mensaje,
					icon: data.Icon
				});
			},
			error: function(data){
				console.log('Error:', data)
				$('.ibox-content').toggleClass('sk-loading',false);
			}
		});

}

function ConsultarCant(){
	$.ajax({
		url:"ajx_buscar_datos_json.php",
		data:{
			type:38,
			doctype:1
		},
		dataType:'json',
		success: function(data){
			if(data){
				$("#Tot_ValOK").html(data.ValOK);
				$("#Tot_ValNov").html(data.ValNov);
				$("#Tot_Pend").html(data.Pend);
				$("#Tot_Cerradas").html(data.Cerradas);
				$("#Tot_NoCerradas").html(data.NoCerradas);
			}
		}
	});
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
                    <h2>Cierre de OT en lote</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Servicios</a>
                        </li>
						 <li>
                            <a href="#">Asistentes</a>
                        </li>
                        <li class="active">
                            <strong>Cierre de OT en lote</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
			<!-- Inicio, modalCambiarLlamadas. SMM, 12/09/2022 -->
			<div class="modal inmodal fade" id="modalCambiarLlamadas" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title">Cambiar campos en lote - Llamadas</h4>
						</div>

						<!-- form id="formCambiarLlamadas" -->
							<div class="modal-body">
								<div class="ibox-content">

									<div class="panel panel-info"> <!-- SMM, 15/09/2022 -->
										<div class="panel-heading active" role="tab" id="headingOne">
											<h4 class="panel-title">
												<a role="button" data-toggle="collapse" href="#collapseOne" aria-controls="collapseOne">
													<i class="fa fa-info-circle"></i> Instrucciones de uso
												</a>
											</h4>
										</div>
										<div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
											<div class="panel-body">
												<b>
													Esta ventana permite cambiar rápidamente el valor de los campos en la lista de llamadas de servicio, ninguno
													de los campos es obligatorio y solo se veran afectados los campos para los cuales "seleccione" o digite un valor.
												</b>
											</div> <!-- panel-body-->
										</div> <!-- panel-collapse -->
									</div> <!-- panel-info -->

									<div class="form-group">
										<label class="col-lg-2">Estado servicio</label>
										<div class="col-lg-10">
											<select name="IdEstadoServicio" class="form-control" id="IdEstadoServicio" required>
												<option value="" disabled selected>Seleccione...</option>
											<?php while ($row_EstadoServicio = sqlsrv_fetch_array($SQL_EstadoServicioLlamada)) {?>
												<option value="<?php echo $row_EstadoServicio['IdEstadoServicio']; ?>">
													<?php echo $row_EstadoServicio['DeEstadoServicio']; ?>
												</option>
											<?php }?>
											</select>
										</div>
									</div>
									<br><br><br>
									<div class="form-group">
										<label class="col-lg-2">Cancelado por</label>
										<div class="col-lg-10">
											<select name="IdCanceladoPor" class="form-control" id="IdCanceladoPor" required>
												<option value="" disabled selected>Seleccione...</option>
											<?php while ($row_CanceladoPor = sqlsrv_fetch_array($SQL_CanceladoPorLlamada)) {?>
												<option value="<?php echo $row_CanceladoPor['IdCanceladoPor']; ?>">
													<?php echo $row_CanceladoPor['DeCanceladoPor']; ?>
												</option>
											<?php }?>
											</select>
										</div>
									</div>
									<br><br><br>
									<div class="form-group">
										<label class="col-lg-2">Comentarios de cierre</label>
										<div class="col-lg-10">
											<textarea type="text" maxlength="2000" rows="4" class="form-control" name="ComentariosCierre" id="ComentariosCierre"></textarea>
										</div>
									</div>
									<br><br><br>
								</div>
							</div>

							<div class="modal-footer">
								<button type="button" class="btn btn-success m-t-md" id="formCambiarLlamadas"><i class="fa fa-check"></i> Aceptar</button>
								<button type="button" class="btn btn-warning m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
							</div>
						<!-- /formCambiarLlamadas -->
					</div>
				</div>
			</div>
			<!-- Fin, modalCambiarLlamadas. SMM, 12/09/2022 -->

			<!-- Inicio, modalCambiarActividades. SMM, 13/09/2022 -->
			<div class="modal inmodal fade" id="modalCambiarActividades" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title">Cambiar campos en lote - Actividades</h4>
						</div>

						<!-- form id="formCambiarActividades" -->
							<div class="modal-body">
								<div class="ibox-content">
								<div class="panel panel-info"> <!-- SMM, 15/09/2022 -->
										<div class="panel-heading active" role="tab" id="headingTwo">
											<h4 class="panel-title">
												<a role="button" data-toggle="collapse" href="#collapseTwo" aria-controls="collapseTwo">
													<i class="fa fa-info-circle"></i> Instrucciones de uso
												</a>
											</h4>
										</div>
										<div id="collapseTwo" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingTwo">
											<div class="panel-body">
												<p>
													Esta ventana permite cambiar rápidamente el valor de los campos en la lista de actividades, ninguno
													de los campos es obligatorio y solo se veran afectados los campos para los cuales seleccione o digite un valor.
												</p>
											</div> <!-- panel-body-->
										</div> <!-- panel-collapse -->
									</div> <!-- panel-info -->
									<br>
									<!-- Inicio, Componente de Fecha y Hora -->
									<div class="form-group">
										<div class="row">
											<label class="col-lg-6 control-label" style="text-align: left !important;">Fecha y hora inicio Ejecución</label>
										</div>
										<div class="row">
											<div class="col-lg-6 input-group date">
												<span class="input-group-addon">
													<i class="fa fa-calendar"></i>
												</span>
												<input name="FechaInicioEjecucion" id="FechaInicioEjecucion" type="text" autocomplete="off" class="form-control" placeholder="YYYY-MM-DD">
											</div>
											<div class="col-lg-6 input-group clockpicker" data-autoclose="true">
												<input name="HoraInicioEjecucion" id="HoraInicioEjecucion" type="text" autocomplete="off" class="form-control" placeholder="hh:mm">
												<span class="input-group-addon">
													<i class="fa fa-clock-o"></i>
												</span>
											</div>
										</div>
									</div>
									<!-- Fin, Componente de Fecha y Hora -->

									<br><br>
									<!-- Inicio, Componente de Fecha y Hora -->
									<div class="form-group">
										<div class="row">
											<label class="col-lg-6 control-label" style="text-align: left !important;">Fecha y hora fin Ejecución</label>
										</div>
										<div class="row">
											<div class="col-lg-6 input-group date">
												<span class="input-group-addon">
													<i class="fa fa-calendar"></i>
												</span>
												<input name="FechaFinEjecucion" id="FechaFinEjecucion" type="text" autocomplete="off" class="form-control" placeholder="YYYY-MM-DD">
											</div>
											<div class="col-lg-6 input-group clockpicker" data-autoclose="true">
												<input name="HoraFinEjecucion" id="HoraFinEjecucion" type="text" autocomplete="off" class="form-control" placeholder="hh:mm">
												<span class="input-group-addon">
													<i class="fa fa-clock-o"></i>
												</span>
											</div>
										</div>
									</div>
									<!-- Fin, Componente de Fecha y Hora -->
									<br><br><br>
								</div>
							</div>

							<div class="modal-footer">
								<button type="button" class="btn btn-success m-t-md" id="formCambiarActividades"><i class="fa fa-check"></i> Aceptar</button>
								<button type="button" class="btn btn-warning m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
							</div>
						<!-- /formCambiarActividades -->
					</div>
				</div>
			</div>
			<!-- Fin, modalCambiarActividades. SMM, 13/09/2022 -->

             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include "includes/spinner.php";?>
				  <form action="cierre_ot_lote.php" method="get" id="formBuscar" class="form-horizontal">
					  <div class="form-group">
						<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
					  </div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Fechas</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial; ?>"/>
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal; ?>" />
								</div>
							</div>
							<label class="col-lg-1 control-label">Sucursal <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="Sucursal" class="form-control" id="Sucursal" required>
									<option value="">Seleccione...</option>
								  <?php	while ($row_Sucursal = sqlsrv_fetch_array($SQL_Sucursal)) {?>
											<option value="<?php echo $row_Sucursal['IdSucursal']; ?>" <?php if (isset($_GET['Sucursal']) && (strcmp($row_Sucursal['IdSucursal'], $_GET['Sucursal']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['DeSucursal']; ?></option>
									<?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Serie <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="Series" class="form-control" id="Series" required>
										<option value="">Seleccione...</option>
								  <?php if ($sw == 1) {
    while ($row_Series = sqlsrv_fetch_array($SQL_Series)) {?>
											<option value="<?php echo $row_Series['IdSeries']; ?>" <?php if ((isset($_GET['Series'])) && (strcmp($row_Series['IdSeries'], $_GET['Series']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries']; ?></option>
								  <?php }
}?>
								</select>
							</div>
						</div>

						<!-- SMM, 09/08/2022 -->
					 	<div class="form-group">
							<label class="col-lg-1 control-label">Estado OT <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select data-placeholder="Digite para buscar..." name="EstadosLlamada[]" class="form-control select2" id="EstadosLlamada" multiple>
									<?php while ($row_EstadoLlamada = sqlsrv_fetch_array($SQL_EstadoLlamada)) {?>
										<option value="<?php echo $row_EstadoLlamada['Cod_Estado']; ?>"
										<?php if (isset($_GET['EstadosLlamada']) && in_array($row_EstadoLlamada['Cod_Estado'], $_GET['EstadosLlamada'])) {echo "selected";}?>>
											<?php echo $row_EstadoLlamada['NombreEstado']; ?>
										</option>
									<?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Basado en archivos escaneados <span class="text-danger">*</span></label>
							<div class="col-lg-2">
								<select name="BasadoEscaneados" class="form-control" id="BasadoEscaneados" required>
									<option value="1" <?php if (isset($_GET['BasadoEscaneados']) && ($_GET['BasadoEscaneados'] == "1")) {echo "selected";}?>>SI</option>
									<option value="0" <?php if (isset($_GET['BasadoEscaneados']) && ($_GET['BasadoEscaneados'] == "0")) {echo "selected";}?>>NO</option>
								</select>
							</div>
							<div class="col-lg-1">
								<button type="button" class="btn btn-sm btn-secondary btn-circle" data-toggle="tooltip" data-html="true"
								title="<b>SI</b> - Búsqueda basada en los archivos que se encuentran en la ruta compartida de directorios.
								<br><b>NO</b> - Búsqueda basada en el registro de la llamada de servicio."><i class="fa fa-info"></i></button>
							</div>

							<div class="col-lg-4">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
						<!-- Hasta aquí, 09/08/2022 -->

					  <input type="hidden" id="reload" name="reload" value="0" />
				 </form>
			</div>
			</div>
		  </div>
         <br>
		<?php if ($sw == 1) {?>
		<div class="row">
				<div class="col-lg-2">
					<div class="ibox">
						<div class="ibox-title">
							<h5><span class="font-normal">Cant. OT validación OK</span></h5>
						</div>
						<div class="ibox-content">
							<h2 class="no-margins font-bold text-success" id="Tot_ValOK">0</h2>
						</div>
					</div>
				</div>
				<div class="col-lg-2">
					<div class="ibox">
						<div class="ibox-title">
							<h5><span class="font-normal">Cant. OT con novedad</span></h5>
						</div>
						<div class="ibox-content">
							<h2 class="no-margins font-bold text-danger" id="Tot_ValNov">0</h2>
						</div>
					</div>
				</div>
				<div class="col-lg-2">
					<div class="ibox">
						<div class="ibox-title">
							<h5><span class="font-normal">Cant. OT pendiente por ejecutar</span></h5>
						</div>
						<div class="ibox-content">
							<h2 class="no-margins font-bold text-warning" id="Tot_Pend">0</h2>
						</div>
					</div>
				</div>
				<div class="col-lg-2">
					<div class="ibox">
						<div class="ibox-title">
							<h5><span class="font-normal">Cant. OT cerradas</span></h5>
						</div>
						<div class="ibox-content">
							<h2 class="no-margins font-bold text-navy" id="Tot_Cerradas">0</h2>
						</div>
					</div>
				</div>
				<div class="col-lg-2">
					<div class="ibox">
						<div class="ibox-title">
							<h5><span class="font-normal">Cant. OT no cerradas</span></h5>
						</div>
						<div class="ibox-content">
							<h2 class="no-margins font-bold text-danger" id="Tot_NoCerradas">0</h2>
						</div>
					</div>
				</div>
			</div>
		<br>
		 <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include "includes/spinner.php";?>
					<div class="row">
						<div class="col-lg-8">
							<button class="btn btn-primary btn-lg btn-outline m-b-md" type="button" id="CierreLlamadas" onClick="Validar('2');"><i class="fa fa-play-circle"></i> Cerrar llamadas de servicio</button>
							<input type="hidden" id="IdEvento" value="<?php if (isset($row['IdEvento'])) {echo $row['IdEvento'];}?>" />

							<!-- SMM, 13/09/2022 -->
							<button class="btn btn-success btn-lg btn-outline m-b-md" type="button" onClick="cambiarCampos(1)"><i class="fa fa-refresh"></i> Cambiar campos en lote</button>
						</div>
						<div class="col-lg-2">
							<div class="form-group border">
								<div class="p-xs">
									<label class="text-muted">Última consulta</label>
									<div class="font-bold"><?php echo date('Y-m-d H:i'); ?></div>
								</div>
							</div>
						</div>
						<div class="col-lg-2">
							<div class="form-group border">
								<div class="p-xs">
									<label class="text-muted">Última ejecución</label>
									<div id="UltEjecucion" class="font-bold">&nbsp;</div>
								</div>
							</div>
						</div>
					</div>
					<div class="tabs-container m-b-md">
						<ul class="nav nav-tabs">
							<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Llamadas de servicio</a></li>
							<li><span class="TimeAct"><div id="TimeAct">&nbsp;</div></span></li>
						</ul>
						<div class="tab-content">
							<div id="tab-1" class="tab-pane active">
								<iframe id="DG_Llamadas" name="DG_Llamadas" style="border: 0;" width="100%" height="400" src="detalle_cierre_ot_lote_llamadas.php"></iframe>
							</div>
						</div>
					</div>
					<button class="btn btn-primary btn-lg btn-outline m-b-md" type="button" id="CierreAct" onClick="Validar('1');"><i class="fa fa-play-circle"></i> Cerrar actividades</button>

					<!-- SMM, 13/09/2022 -->
					<button class="btn btn-success btn-lg btn-outline m-b-md" type="button" onClick="cambiarCampos(2)"><i class="fa fa-refresh"></i> Cambiar campos en lote</button>

					<div class="tabs-container">
						<ul class="nav nav-tabs">
							<li class="active"><a data-toggle="tab" href="#tab-2"><i class="fa fa-tasks"></i> Actividades</a></li>
							<li><span class="TimeAct"><div id="TimeAct2">&nbsp;</div></span></li>
						</ul>
						<div class="tab-content">
							<div id="tab-2" class="tab-pane active">
								<iframe id="DG_Actividades" name="DG_Actividades" style="border: 0;" width="100%" height="400" src="detalle_cierre_ot_lote_actividades.php"></iframe>
							</div>
						</div>
					</div>
				</div>
			</div>
          </div>
		 <?php }?>
        </div>
        <!-- InstanceEndEditable -->
        <?php include "includes/footer.php";?>

    </div>
</div>
<?php include "includes/pie.php";?>
<!-- InstanceBeginEditable name="EditRegion4" -->

<script>
// SMM, 12/09/2022
function cambiarCampos(tipo) {
	if(tipo === 1) {
		document.getElementById("IdEstadoServicio").value = "";
		document.getElementById("IdCanceladoPor").value = "";
		document.getElementById("ComentariosCierre").value = "";

		$('#modalCambiarLlamadas').modal('show');
	} else {
		document.getElementById("FechaInicioEjecucion").value = "";
		document.getElementById("HoraInicioEjecucion").value = "";
		document.getElementById("FechaFinEjecucion").value = "";
		document.getElementById("HoraFinEjecucion").value = "";

		$('#modalCambiarActividades').modal('show');
	}
}
// Hasta aquí, 12/09/2022

	$(document).ready(function(){
		// SMM, 09/19/2022
		$('[data-toggle="tooltip"]').tooltip();

		// SMM, 15/09/2022
		$('.panel-collapse').on('show.bs.collapse', function () {
			$(this).siblings('.panel-heading').addClass('active');
		});

		$('.panel-collapse').on('hide.bs.collapse', function () {
			$(this).siblings('.panel-heading').removeClass('active');
		});
		// Hasta aquí, 15/09/2022

		// SMM, 12/09/2022
		$("#formCambiarLlamadas").on("click", function(event) {
			// event.preventDefault(); // Evitar redirección del formulario

			Swal.fire({
				title: "¿Desea cambiar los campos en la lista de llamadas de servicio?",
				icon: "question",
				showCancelButton: true,
				confirmButtonText: "Si, confirmo",
				cancelButtonText: "No"
			}).then((result) => {
				if (result.isConfirmed) {
					let DG_Llamadas = document.getElementById("DG_Llamadas");

					// Filtros, 09/12/2022
					let f1= `IdEstadoServicio=${document.getElementById("IdEstadoServicio").value}`;
					let f2= `IdCanceladoPor=${document.getElementById("IdCanceladoPor").value}`;
					let f3= `ComentariosCierre=${document.getElementById("ComentariosCierre").value}`;

					DG_Llamadas.src = `detalle_cierre_ot_lote_llamadas.php?${f1}&${f2}&${f3}`;
					$('#modalCambiarLlamadas').modal('hide');
				} else {
					console.log("Acción cancelada por el usuario");
				}
			});
		});
		// Hasta aquí, 12/09/2022

		// SMM, 13/09/2022
		$('.date').datepicker();
		$('.clockpicker').clockpicker();

		$("#formCambiarActividades").on("click", function(event) {
			// event.preventDefault(); // Evitar redirección del formulario

			Swal.fire({
				title: "¿Desea cambiar los campos en la lista de actividades?",
				icon: "question",
				showCancelButton: true,
				confirmButtonText: "Si, confirmo",
				cancelButtonText: "No"
			}).then((result) => {
				if (result.isConfirmed) {
					let DG_Actividades = document.getElementById("DG_Actividades");

					// Filtros, 13/09/2022
					let f1= `FechaInicioEjecucion=${document.getElementById("FechaInicioEjecucion").value}`;
					let f2= `HoraInicioEjecucion=${document.getElementById("HoraInicioEjecucion").value}`;
					let f3= `FechaFinEjecucion=${document.getElementById("FechaFinEjecucion").value}`;
					let f4= `HoraFinEjecucion=${document.getElementById("HoraFinEjecucion").value}`;

					DG_Actividades.src = `detalle_cierre_ot_lote_actividades.php?${f1}&${f2}&${f3}&${f4}`;
					$('#modalCambiarActividades').modal('hide');
				} else {
					console.log("Acción cancelada por el usuario");
				}
			});
		});
		// Hasta aquí, 13/09/2022

			$("#formBuscar").validate({
			 submitHandler: function(form){
				var reload = document.getElementById("reload");
				 <?php if ($sw == 1) {?>
				 	Swal.fire({
						title: "Cierre de OT",
						text: "¿Deseas volver a consultar este cierre o generar uno nuevo?",
						icon: "info",
						showCancelButton: true,
						confirmButtonText: "Generar uno nuevo",
						cancelButtonText: "Continuar con el mismo"
					}).then((result) => {
						if (result.isConfirmed){
							reload.value = "0"
						}else{
							reload.value = "1"
						}
						$('.ibox-content').toggleClass('sk-loading');
				 		form.submit();
					});
			 	 <?php } else {?>
				 	$('.ibox-content').toggleClass('sk-loading');
				 	form.submit();
			 	 <?php }?>
				}
			});
			 $(".alkin").on('click', function(){
					$('.ibox-content').toggleClass('sk-loading');
				});
			 $('#FechaInicial').datepicker({
				todayBtn: "linked",
				keyboardNavigation: false,
				forceParse: false,
				calendarWeeks: true,
				autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true
			});
			 $('#FechaFinal').datepicker({
				todayBtn: "linked",
				keyboardNavigation: false,
				forceParse: false,
				calendarWeeks: true,
				autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true
			});

			$('.chosen-select').chosen({width: "100%"});

			// SMM, 09/08/2022
			$(".select2").select2();

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

			$("#NombreClienteActividad").easyAutocomplete(options);

			<?php if ($sw == 1) {?>
			ConsultarCant();
			<?php }?>

            $('.dataTables-example').DataTable({
                pageLength: 25,
                dom: '<"html5buttons"B>lTfgitp',
				order: [[ 0, "desc" ]],
				language: {
					"decimal":        "",
					"emptyTable":     "No se encontraron resultados.",
					"info":           "Mostrando _START_ - _END_ de _TOTAL_ registros",
					"infoEmpty":      "Mostrando 0 - 0 de 0 registros",
					"infoFiltered":   "(filtrando de _MAX_ registros)",
					"infoPostFix":    "",
					"thousands":      ",",
					"lengthMenu":     "Mostrar _MENU_ registros",
					"loadingRecords": "Cargando...",
					"processing":     "Procesando...",
					"search":         "Filtrar:",
					"zeroRecords":    "Ningún registro encontrado",
					"paginate": {
						"first":      "Primero",
						"last":       "Último",
						"next":       "Siguiente",
						"previous":   "Anterior"
					},
					"aria": {
						"sortAscending":  ": Activar para ordenar la columna ascendente",
						"sortDescending": ": Activar para ordenar la columna descendente"
					}
				},
                buttons: []

            });

        });

    </script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>