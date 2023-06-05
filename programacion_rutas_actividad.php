<?php
require_once "includes/conexion.php";
PermitirAcceso(312);
//require_once("includes/conexion_hn.php");
if (isset($_GET['id']) && $_GET['id'] != "") {
    $id = base64_decode($_GET['id']);
    $idEvento = base64_decode($_GET['idEvento']);
} else {
    $id = "";
    $idEvento = "";
}

$type_act = isset($_GET['tl']) ? $_GET['tl'] : 1;

if ($type_act == 1) {
    $Where = "DocEntry='" . $id . "' and IdEvento='" . $idEvento . "'";
} else {
    $Where = "ID_Actividad='" . $id . "' and IdEvento='" . $idEvento . "'";
}

//Actividades
$SQL_Actividades = Seleccionar('uvw_tbl_Actividades_Rutas', '*', $Where);
$row = sql_fetch_array($SQL_Actividades);

//Asunto actividad
$SQL_AsuntoActividad = Seleccionar('uvw_Sap_tbl_AsuntosActividad', '*', "Id_TipoActividad=2", 'DE_AsuntoActividad');

//Empleados
$SQL_EmpleadoActividad = Seleccionar('uvw_Sap_tbl_Empleados', '*', "IdUsuarioSAP=0", 'NombreEmpleado');

//Turno técnico
$SQL_TurnoTecnicos = Seleccionar('uvw_Sap_tbl_TurnoTecnicos', '*');

//Tipos de Estado actividad
$SQL_TiposEstadoActividad = Seleccionar('uvw_tbl_TipoEstadoServicio', '*');

//Estado actividad
$SQL_EstadoActividad = Seleccionar('uvw_tbl_EstadoActividad', '*');

//Materiales
$ParamMateriales = array(
    "'" . $row['ID_LlamadaServicio'] . "'",
);

$SQL_Materiales = EjecutarSP("sp_ConsultarDatosCalendarioRutasMateriales", $ParamMateriales);

//Historico actividades
$ParamHistAct = array(
    "'" . $row['ID_CodigoCliente'] . "'",
    "'" . $row['NombreSucursal'] . "'",
    "'" . FormatoFecha($row['FechaFinActividad']) . "'",
);

$SQL_HistAct = EjecutarSP("sp_ConsultarDatosCalendarioRutasHistAct", $ParamHistAct);

if ($type_act == 1) {
    //Anexos
    $SQL_AnexoActividad = Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos', '*', "AbsEntry='" . $row['IdAnexoActividad'] . "'");
}

// Grupos de Empleados, SMM 19/05/2022
$SQL_GruposUsuario = Seleccionar("uvw_tbl_UsuariosGruposEmpleados", "*", "[ID_Usuario]='" . $_SESSION['CodUser'] . "'", 'DeCargo');

$ids_grupos = array();
while ($row_GruposUsuario = sqlsrv_fetch_array($SQL_GruposUsuario)) {
    $ids_grupos[] = $row_GruposUsuario['IdCargo'];
}

$disabled = "";
if (isset($row['ID_EmpleadoActividad']) && (count($ids_grupos) > 0)) {
    $ID_Empleado = "'" . $row['ID_EmpleadoActividad'] . "'";
    $SQL_Empleado = Seleccionar('uvw_Sap_tbl_Empleados', '*', "ID_Empleado = $ID_Empleado");
    $row_Empleado = sql_fetch_array($SQL_Empleado);

    if (isset($row_Empleado['IdCargo']) && (!in_array($row_Empleado['IdCargo'], $ids_grupos))) {
        $disabled = "disabled";
    }
}

// SMM, 07/03/2023
/*
if (isset($row['IdTipoEstadoActividad']) && (strcmp("05", $row['IdTipoEstadoActividad']) == 0)) {
$disabled = "disabled";
}
 */
?>

<form id="frmActividad" method="post">
<div class="modal-content">
  <div class="modal-header">
    <h5 class="modal-title"><?php echo $row['EtiquetaActividad']; ?></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
  </div>
  <div class="modal-body">
	<div class="pt-3 pr-3 pl-3 pb-1 mb-2 bg-primary text-white"><h5><i class="fas fa-calendar-alt"></i> Datos de programación</h5></div>
   	<div class="form-group row">
		<label class="col-lg-2 col-form-label">Fecha inicio <span class="text-danger">*</span></label>
		<div class="col-lg-2 input-group">
			<?php /*?><div class="input-group-prepend">
<span class="input-group-text" id="basic-addon1"><i class="fas fa-calendar-alt d-block"></i></span>
</div><?php */?>
			<input <?php echo $disabled ?> name="FechaInicio" type="text" required="required" class="form-control" id="FechaInicio" value="<?php echo $row['FechaInicioActividad']; ?>" <?php if (($type_act == 1) && ($row['IdEstadoActividad'] == 'Y')) {echo "readonly='readonly'";}?>>
		</div>
		<div class="col-lg-2 input-group">
			<input <?php echo $disabled ?> name="HoraInicio" id="HoraInicio" type="text" class="form-control" value="<?php echo $row['HoraInicioActividad']; ?>" required="required" onChange="ValidarHoras();" <?php if (($type_act == 1) && ($row['IdEstadoActividad'] == 'Y')) {echo "readonly='readonly'";}?>>
			<?php /*?><div class="input-group-prepend">
<span class="input-group-text" id="basic-addon1"><i class="fas fa-clock d-block"></i></span>
</div><?php */?>
		</div>
		<label class="col-lg-2 col-form-label">Fecha inicio ejecución</label>
		<div class="col-lg-2 input-group">
			<input <?php echo $disabled ?> name="FechaInicioEjecucion" type="text" class="form-control" id="FechaInicioEjecucion" value="<?php echo $row['CDU_FechaInicioEjecucionActividad']; ?>" readonly="readonly">
		</div>
		<div class="col-lg-2">
			<input <?php echo $disabled ?> name="HoraInicioEjecucion" type="text" class="form-control" id="HoraInicioEjecucion" value="<?php echo $row['CDU_HoraInicioEjecucionActividad']; ?>" readonly="readonly">
		</div>
	</div>
	<div class="form-group row">
		<label class="col-lg-2 col-form-label">Fecha fin <span class="text-danger">*</span></label>
		<div class="col-lg-2 input-group">
			<?php /*?><div class="input-group-prepend">
<span class="input-group-text" id="basic-addon1"><i class="fas fa-calendar-alt d-block"></i></span>
</div><?php */?>
			<input <?php echo $disabled ?> name="FechaFin" type="text" required="required" class="form-control" id="FechaFin" value="<?php echo $row['FechaFinActividad']; ?>" <?php if (($type_act == 1) && ($row['IdEstadoActividad'] == 'Y')) {echo "readonly='readonly'";}?>>
		</div>
		<div class="col-lg-2 input-group">
			<input <?php echo $disabled ?> name="HoraFin" id="HoraFin" type="text" class="form-control" value="<?php echo $row['HoraFinActividad']; ?>" required="required" onChange="ValidarHoras();" <?php if (($type_act == 1) && ($row['IdEstadoActividad'] == 'Y')) {echo "readonly='readonly'";}?>>
			<?php /*?><div class="input-group-prepend">
<span class="input-group-text" id="basic-addon1"><i class="fas fa-clock d-block"></i></span>
</div><?php */?>
		</div>
		<label class="col-lg-2 col-form-label">Fecha fin ejecución</label>
		<div class="col-lg-2 input-group">
			<input <?php echo $disabled ?> name="FechaFinEjecucion" type="text" class="form-control" id="FechaFinEjecucion" value="<?php echo $row['CDU_FechaFinEjecucionActividad']; ?>" readonly="readonly">
		</div>
		<div class="col-lg-2">
			<input <?php echo $disabled ?> name="HoraFinEjecucion" type="text" class="form-control" id="HoraFinEjecucion" value="<?php echo $row['CDU_HoraFinEjecucionActividad']; ?>" readonly="readonly">
		</div>
	</div>
	<div class="form-group row">
		<label class="col-lg-2 col-form-label">Tipo estado actividad <span class="text-danger">*</span></label>
		<div class="col-lg-4">
			<select <?php echo $disabled ?> name="TipoEstadoActividad" class="form-control" id="TipoEstadoActividad" required="required" <?php if (($type_act == 1) && ($row['IdEstadoActividad'] == 'Y')) {echo "disabled='disabled'";}?>>
				<option value="">Seleccione...</option>
			  <?php while ($row_TiposEstadoActividad = sqlsrv_fetch_array($SQL_TiposEstadoActividad)) {?>
					<option value="<?php echo $row_TiposEstadoActividad['ID_TipoEstadoServicio']; ?>" data-color="<?php echo $row_TiposEstadoActividad['ColorEstadoServicio']; ?>" style="color: <?php echo $row_TiposEstadoActividad['ColorEstadoServicio']; ?>;font-weight: bold;" <?php if ((isset($row['IdTipoEstadoActividad'])) && (strcmp($row_TiposEstadoActividad['ID_TipoEstadoServicio'], $row['IdTipoEstadoActividad']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_TiposEstadoActividad['DE_TipoEstadoServicio']; ?></option>
			  <?php }?>
			</select>
		</div>
		<label class="col-lg-2 col-form-label">Asignado a <span class="text-danger">*</span></label>
		<div class="col-lg-4">
			<select <?php echo $disabled ?> name="EmpleadoActividad" class="form-control select2" style="width: 100%" required id="EmpleadoActividad" <?php if (($type_act == 1) && ($row['IdEstadoActividad'] == 'Y')) {echo "disabled='disabled'";}?>>
					<option value="">(Sin asignar)</option>
			  <?php while ($row_EmpleadoActividad = sqlsrv_fetch_array($SQL_EmpleadoActividad)) {?>
					<option value="<?php echo $row_EmpleadoActividad['ID_Empleado']; ?>" <?php if ((isset($row['ID_EmpleadoActividad'])) && (strcmp($row_EmpleadoActividad['ID_Empleado'], $row['ID_EmpleadoActividad']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_EmpleadoActividad['NombreEmpleado']; ?></option>
			  <?php }?>
			</select>
		</div>
	</div>
	<div class="form-group row">
		<label class="col-lg-2 col-form-label">Estado actividad <span class="text-danger">*</span></label>
		<div class="col-lg-4">
			<select <?php echo $disabled ?> name="EstadoActividad" class="form-control" id="EstadoActividad" <?php if (($type_act == 1) && ($row['IdEstadoActividad'] == 'Y')) {echo "disabled='disabled'";}?>>
			  <?php while ($row_EstadoActividad = sqlsrv_fetch_array($SQL_EstadoActividad)) {?>
					<option value="<?php echo $row_EstadoActividad['Cod_Estado']; ?>" <?php if ((isset($row['IdEstadoActividad'])) && (strcmp($row_EstadoActividad['Cod_Estado'], $row['IdEstadoActividad']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_EstadoActividad['NombreEstado']; ?></option>
			  <?php }?>
			</select>
		</div>
		<label class="col-lg-2 col-form-label">Turno técnico</label>
		<div class="col-lg-4">
			<select <?php echo $disabled ?> name="TurnoTecnico" class="form-control" id="TurnoTecnico" <?php if (($type_act == 1) && ($row['IdEstadoActividad'] == 'Y')) {echo "disabled='disabled'";}?>>
					<option value="">Seleccione...</option>
			  <?php while ($row_TurnoTecnicos = sqlsrv_fetch_array($SQL_TurnoTecnicos)) {?>
					<option value="<?php echo $row_TurnoTecnicos['CodigoTurno']; ?>" <?php if ((isset($row['CDU_IdTurnoTecnico'])) && (strcmp($row_TurnoTecnicos['CodigoTurno'], $row['CDU_IdTurnoTecnico']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_TurnoTecnicos['NombreTurno']; ?></option>
			  <?php }?>
			</select>
		</div>
	</div>
	<div class="pt-3 pr-3 pl-3 pb-1 mb-2 bg-primary text-white"><h5><i class="fas fa-info-circle"></i> Información de la actividad</h5></div>
	<ul class="nav nav-tabs" id="myTab" role="tablist">
		<li class="nav-item">
			<a class="nav-link active" data-toggle="tab" href="#tab-1"><i class="fas fa-tasks"></i> Programación</a>
	  	</li>
			<li class="nav-item">
			<a class="nav-link" data-toggle="tab" href="#tab-2"><i class="fas fa-user-friends"></i> Cliente</a>
	  	</li>
	  	<li class="nav-item">
			<a class="nav-link" data-toggle="tab" href="#tab-3"><i class="fas fa-tools"></i> Materiales</a>
	  	</li>
	  	<li class="nav-item">
			<a class="nav-link" data-toggle="tab" href="#tab-4"><i class="fas fa-history"></i> Historico de actividades</a>
	  	</li>
		<?php if ($type_act == 1) {?>
		<li class="nav-item">
			<a class="nav-link" data-toggle="tab" href="#tab-5"><i class="fas fa-paperclip"></i> Anexos</a>
	  	</li>
		<li class="nav-item">
			<a class="nav-link" data-toggle="tab" href="#tab-6" <?php if ($row['LatitudGPS'] != "" && $row['LongitudGPS'] != "") {?>onClick="initMap();"<?php }?>><i class="fas fa-map-marker-alt"></i> Ubicación GPS</a>
	  	</li>
		<?php }?>
	</ul>
	<div class="tab-content" id="myTabContent">
		<div class="tab-pane fade show active" id="tab-1">
			<br>
			<div class="form-group row mb-n2">
				<div class="col-lg-3">
					<label class="col-form-label"><?php if (($type_act == 1) && ($row['DocEntry'] != 0)) {?><a href="actividad.php?id=<?php echo base64_encode($row['DocEntry']); ?>&tl=1" target="_blank" title="Consultar actividad" class="btn-xs btn-success fas fa-search"></a> <?php }?>ID Actividad</label>
					<p><?php echo $row['DocEntry']; ?></p>
				</div>
				<div class="col-lg-3">
					<label class="col-form-label"><?php if (($type_act == 1) && ($row['ID_LlamadaServicio'] != 0)) {?><a href="llamada_servicio.php?id=<?php echo base64_encode($row['DocEntryLlamadaServicio']); ?>&tl=1" target="_blank" title="Consultar Llamada de servicio" class="btn-xs btn-success fas fa-search"></a> <?php }?>Llamada de servicio</label>
					<p><?php echo $row['ID_LlamadaServicio']; ?></p>
				</div>
				<div class="col-lg-3">
					<label class="col-form-label">Estado de servicio</label>
					<p class="text-white"><span class="badge <?php if ($row['IdEstadoServicio'] == '0') {echo "bg-warning";} elseif ($row['IdEstadoServicio'] == '1') {echo "bg-primary";} else {echo "bg-danger";}?>"><?php echo $row['DeEstadoServicio']; ?></span></p>
				</div>
				<div class="col-lg-3">
					<label class="col-form-label">Estado llamada</label>
					<p class="text-white"><span class="badge <?php if ($row['IdEstadoLlamada'] == '-3') {echo "bg-primary";} elseif ($row['IdEstadoLlamada'] == '-2') {echo "bg-warning";} else {echo "bg-danger";}?>"><?php echo $row['DeEstadoLlamada']; ?></span></p>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-lg-8">
					<label class="col-form-label">Titulo de actividad <span class="text-danger">*</span></label>
					<input <?php echo $disabled ?> name="TituloActividad" type="text" class="form-control" id="TituloActividad" required value="<?php echo $row['TituloActividad']; ?>" <?php if (($type_act == 1) && ($row['IdEstadoActividad'] == 'Y')) {echo "readonly='readonly'";}?>>
				</div>
				<div class="col-lg-4">
					<label class="col-form-label">Asunto <span class="text-danger">*</span></label>
					<select <?php echo $disabled ?> name="AsuntoActividad" class="form-control" id="AsuntoActividad" required="required" <?php if (($type_act == 1) && ($row['IdEstadoActividad'] == 'Y')) {echo "disabled='disabled'";}?>>
						<?php if ($type_act == 0) {?><option value="">Seleccione...</option><?php }?>
						<?php while ($row_AsuntoActividad = sqlsrv_fetch_array($SQL_AsuntoActividad)) {?>
							<option value="<?php echo $row_AsuntoActividad['ID_AsuntoActividad']; ?>" <?php if ((isset($row['ID_AsuntoActividad'])) && (strcmp($row_AsuntoActividad['ID_AsuntoActividad'], $row['ID_AsuntoActividad']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_AsuntoActividad['DE_AsuntoActividad']; ?></option>
					  <?php }?>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-lg-4">
					<label class="col-form-label">Comentarios para el técnico</label>
					<textarea <?php echo $disabled ?> name="Comentarios" rows="2" maxlength="1000" class="form-control" id="Comentarios" type="text" <?php if (($type_act == 1) && ($row['IdEstadoActividad'] == 'Y')) {echo "readonly='readonly'";}?>><?php echo $row['ComentariosActividad']; ?></textarea>
				</div>
				<div class="col-lg-4">
					<label class="col-form-label">Comentarios de la llamada de servicio</label>
					<p><?php echo $row['ComentarioLlamada']; ?></p>
				</div>
				<div class="col-lg-4">
					<label class="col-form-label">Servicios</label>
					<p><?php echo $row['CDU_Servicios']; ?></p>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-lg-4">
					<label class="col-form-label">Notas del técnico</label>
					<p><?php echo $row['NotasActividad']; ?></p>
				</div>
				<div class="col-lg-4">
					<label class="col-form-label">Resolución de la llamada</label>
					<p><?php echo $row['ResolucionLlamada']; ?></p>
				</div>
				<div class="col-lg-4">
					<label class="col-form-label">Áreas</label>
					<p><?php echo $row['CDU_Areas']; ?></p>
				</div>
			</div>
		</div>
		<div class="tab-pane fade" id="tab-2">
			<br>
			<div class="form-group row">
				<div class="col-lg-5 border-bottom mr-4">
					<label class="col-form-label text-danger"><i class="fas fa-info-circle"></i> Información del contacto en sitio</label>
				</div>
				<div class="col-lg-6 border-bottom ">
					<label class="col-form-label text-danger"><i class="fas fa-map-marker-alt"></i> Ubicación del sitio</label>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-lg-5">
					<div class="col-lg-12">
						<label class="col-form-label"><?php if (($type_act == 1) && ($row['ID_LlamadaServicio'] != 0)) {?><a href="socios_negocios.php?id=<?php echo base64_encode($row['ID_CodigoCliente']); ?>&tl=1" target="_blank" title="Consultar cliente" class="btn-xs btn-success fas fa-search"></a> <?php }?>Nombre cliente</label>
						<p><?php echo $row['NombreCliente']; ?></p>
					</div>
					<div class="col-lg-12">
						<label class="col-form-label">Nombre sucursal</label>
						<p><?php echo $row['NombreSucursal']; ?></p>
					</div>
					<div class="col-lg-12">
						<label class="col-form-label">Dirección</label>
						<p><?php echo $row['DireccionActividad']; ?></p>
					</div>
					<div class="col-lg-12">
						<label class="col-form-label">Ciudad</label>
						<p><?php echo $row['NombreCiudad']; ?></p>
					</div>
					<div class="col-lg-12">
						<label class="col-form-label">Contacto</label>
						<p><?php echo $row['CDU_NombreContacto']; ?></p>
					</div>
					<div class="col-lg-12">
						<label class="col-form-label">Teléfono</label>
						<p><?php echo $row['CDU_TelefonoContacto']; ?></p>
					</div>
					<div class="col-lg-12">
						<label class="col-form-label">Email</label>
						<p><?php echo $row['CDU_CorreoContacto']; ?></p>
					</div>
				</div>
				<div class="col-lg-7">
					<div class="google-map mapGoogle" id="mapCliente"></div>
				</div>
			</div>
		</div>
		<div class="tab-pane fade" id="tab-3">
			<br>
			<div class="table-responsive">
				<table class="table table-striped table-bordered table-hover table-sm" >
					<thead>
					<tr>
						<th>Código artículo</th>
						<th>Nombre artículo</th>
						<th>Cantidad</th>
						<th>Metodo de aplicación</th>
					</tr>
					</thead>
					<tbody>
					<?php while ($row_Materiales = sqlsrv_fetch_array($SQL_Materiales)) {?>
						 <tr>
							 <td><?php echo $row_Materiales['ItemCode']; ?></td>
							 <td><?php echo $row_Materiales['ItemName']; ?></td>
							 <td><?php echo number_format($row_Materiales['Cantidad'], 2); ?></td>
							 <td><?php echo $row_Materiales['MetodoAplicacion']; ?></td>
						</tr>
					<?php }?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="tab-pane fade" id="tab-4">
			<br>
			<?php while ($row_HistAct = sqlsrv_fetch_array($SQL_HistAct)) {?>
			<div class="media mb-3">
				<div class="text-center">
					<i class="far fa-clock fa-2x text-primary"></i>
					<div class="text-muted small text-nowrap mt-2"><?php echo $row_HistAct['FechaActividad']; ?></div>
				</div>
				<div class="media-body bg-lighter rounded py-2 px-3 ml-3">
					<div class="font-weight-semibold mb-1"><?php echo $row_HistAct['NombreEmpleado']; ?></div>
					<?php echo $row_HistAct['DetallesActividad']; ?><br>
					<?php echo $row_HistAct['ComentariosActividad']; ?>
				</div>
			</div>
			<?php }?>
		</div>
		<?php if ($type_act == 1) {?>
		<div class="tab-pane fade" id="tab-5">
			<?php
if ($row['IdAnexoActividad'] != 0) {
    while ($row_AnexoActividad = sqlsrv_fetch_array($SQL_AnexoActividad)) {
        $Icon = IconAttach($row_AnexoActividad['FileExt'], 2);
        ?>
					<div class="col-md-6 col-lg-4 col-xl-4 p-1">
						<div class="project-attachment ui-bordered p-2">
							<div class="project-attachment-file display-4">
								<i class="<?php echo $Icon; ?>"></i>
							</div>
							<div class="media-body ml-3">
								<strong class="project-attachment-filename"><?php echo $row_AnexoActividad['NombreArchivo']; ?></strong>
								<div class="text-muted small"><?php echo $row_AnexoActividad['Fecha']; ?></div>
								<div>
									<a href="attachdownload.php?file=<?php echo base64_encode($row_AnexoActividad['AbsEntry']); ?>&line=<?php echo base64_encode($row_AnexoActividad['Line']); ?>" target="_blank">Descargar</a>
								</div>
							</div>
						</div>
					</div>
			<?php }?>
			<?php } else {echo "<br><p>Sin anexos.</p>";}?>
		</div>
		<div class="tab-pane fade" id="tab-6">
			<div class="card card-body">
				<div class="google-map mapGoogle" id="map"><?php if ($row['LatitudGPS'] == "" || $row['LongitudGPS'] == "") {echo "<br><p>No hay datos para mostrar.</p>";}?></div>
			</div>
		</div>
		<?php }?>
	</div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-secondary md-btn-flat" data-dismiss="modal">Cerrar</button>
    <?php if ($row['IdEstadoActividad'] != 'Y') {?><button type="submit" class="btn btn-primary md-btn-flat"><i class="fas fa-save"></i> Guardar</button><?php }?>
  </div>
</div>
</form>
<script>
	 $(document).ready(function(){
		  $("#frmActividad").validate({
			 submitHandler: function(form, event){
				event.preventDefault()
				blockUI();
				$.ajax({
					type: "GET",
					url: "includes/procedimientos.php?type=31&id_actividad=<?php echo $row['ID_Actividad']; ?>&id_evento=<?php echo $row['IdEvento']; ?>&docentry=<?php echo $row['DocEntry']; ?>&id_asuntoactividad="+$("#AsuntoActividad").val()+"&titulo_actividad="+$("#TituloActividad").val()+"&id_empleadoactividad="+$("#EmpleadoActividad").val()+"&fechainicio="+$("#FechaInicio").val()+"&horainicio="+$("#HoraInicio").val()+"&fechafin="+$("#FechaFin").val()+"&horafin="+$("#HoraFin").val()+"&comentarios_actividad="+$("#Comentarios").val()+"&estado="+$("#EstadoActividad").val()+"&id_tipoestadoact="+$("#TipoEstadoActividad").val()+"&llamada_servicio=<?php echo $row['ID_LlamadaServicio']; ?>&metodo=2&fechainicio_ejecucion="+$("#FechaInicioEjecucion").val()+"&horainicio_ejecucion="+$("#HoraInicioEjecucion").val()+"&fechafin_ejecucion="+$("#FechaFinEjecucion").val()+"&horafin_ejecucion="+$("#HoraFinEjecucion").val()+"&turno_tecnico="+$("#TurnoTecnico").val()+"&sptype=2",
					success: function(response){
						if(response=="OK"){
							$("#btnGuardar").prop('disabled', false);
							$("#btnPendientes").prop('disabled', false);
							var event = calendar.getEventById('<?php echo $id; ?>')
							event.setExtendedProp('manualChange', '1')
							event.setProp('backgroundColor', $("#TipoEstadoActividad").find(':selected').data('color'))
							event.setProp('borderColor', $("#TipoEstadoActividad").find(':selected').data('color'))
							event.setDates($("#FechaInicio").val()+' '+$("#HoraInicio").val(), $("#FechaFin").val()+' '+$("#HoraFin").val())
							event.setResources([$("#EmpleadoActividad").val()])
							if($("#EstadoActividad").val()=='Y'){
								event.setProp('classNames', ['event-striped'])
							}
							$('#ModalAct').modal("hide");
							event.setExtendedProp('manualChange', '0')
							blockUI(false);
							mostrarNotify('Se ha editado una actividad')
						}else{
							 Swal.fire({
								title: '¡Advertencia!',
								text: 'No se pudo insertar la actividad en la ruta',
								icon: 'warning',
							});
							console.log("Error:",response)
						}
					}
				});
			}
		});

 <?php if ($row['IdEstadoActividad'] != 'Y') {?>
		 $('#FechaInicio').flatpickr({
			 dateFormat: "Y-m-d",
			 static : true,
			 allowInput: true
		 });
		 $('#HoraInicio').flatpickr({
			 enableTime: true,
			 noCalendar: true,
			 dateFormat: "H:i",
			 time_24hr: true,
			 static : true,
			 allowInput: true
		 });

		 $('#FechaFin').flatpickr({
			 dateFormat: "Y-m-d",
			 static : true,
			 allowInput: true
		 });
		 $('#HoraFin').flatpickr({
			 enableTime: true,
			 noCalendar: true,
			 dateFormat: "H:i",
			 time_24hr: true,
			 static : true,
			 allowInput: true
		 });

 <?php }?>
 		$('#EmpleadoActividad').select2({
			dropdownParent: $('#ModalAct')
		 });

		 initMapCliente();

	 });

function ValidarHoras(){
	var HInicio = document.getElementById("HoraInicio").value;
	var HFin = document.getElementById("HoraFin").value;

	if(!validarRangoHoras(HInicio,HFin)){
		 Swal.fire({
			title: '¡Advertencia!',
			text: 'Tiempo no válido. Ingrese una duración positiva.',
			icon: 'warning',
		});
		return false;
	}
}
</script>
<script>
function initMapCliente(){
	let pos = {
		lat: 41.850033,
		lng: -87.6500523
	};

	let mapCliente = new google.maps.Map(document.getElementById('mapCliente'), {
		center: pos,
		zoom: 16
	});

//	const iconBase = "https://maps.google.com/mapfiles/kml/paddle/";

	let start = new google.maps.LatLng(41.850033, -87.6500523);

	let markerStart = new google.maps.Marker({
		map: mapCliente,
		draggable: false,
		animation: google.maps.Animation.DROP,
		position: start
	});
}
</script>
<?php if ($type_act == 1) {
    if ($row['LatitudGPS'] != "" && $row['LongitudGPS'] != "") {?>
<script>
function initMap(){
	let pos = {
		lat: <?php echo $row['LatitudGPS']; ?>,
		lng: <?php echo $row['LongitudGPS']; ?>
	};

	let map = new google.maps.Map(document.getElementById('map'), {
		center: pos,
		zoom: 16
	});

	const iconBase = "https://maps.google.com/mapfiles/kml/paddle/";

	let start = new google.maps.LatLng(<?php echo $row['LatitudGPS']; ?>,<?php echo $row['LongitudGPS']; ?>);

	let markerStart = new google.maps.Marker({
		map: map,
		draggable: false,
		animation: google.maps.Animation.DROP,
		position: start,
		icon: iconBase + 'go.png'
	});

	<?php if ($row['LatitudGPSFin'] != "" && $row['LongitudGPSFin'] != "") {?>
		let end = new google.maps.LatLng(<?php echo $row['LatitudGPSFin']; ?>,<?php echo $row['LongitudGPSFin']; ?>);

		let markerEnd = new google.maps.Marker({
			map: map,
			draggable: false,
			animation: google.maps.Animation.DROP,
			position: end,
			icon: iconBase + 'red-square.png'
		});
	<?php }?>
}
</script>
<?php }
}?>