<?php
require_once "includes/conexion.php";
PermitirAcceso(318);
$sw = 0;
//$Proyecto="";
//$Almacen="";
$CardCode = "";
$type = 1;
$Estado = 1; //Abierto

if (isset($_GET['idsucursal']) && ($_GET['idsucursal'] != "")) {
    $Sucursal = "and IdLineaSucursal='" . base64_decode($_GET['idsucursal']) . "'";
} else {
    $Sucursal = "";
}

$SQL = Seleccionar("uvw_tbl_ProgramacionOrdenesServicio", "*", "IdCliente='" . base64_decode($_GET['cardcode']) . "' $Sucursal and Periodo='" . base64_decode($_GET['periodo']) . "'", "IdSucursalCliente");
if ($SQL) {
    $sw = 1;
}

if (isset($_GET['id']) && ($_GET['id'] != "")) {
    if ($_GET['type'] == 1) {
        $type = 1;
    } else {
        $type = $_GET['type'];
    }
    if ($type == 1) { //Creando Orden de Venta

    }
}

// SMM, 11/01/2022
$SQL_Sucursal = "";
$SQL_Frecuencia = "";
if (isset($_GET['cardcode']) && ($_GET['cardcode'] != "")) {
    $CardCode = base64_decode($_GET['cardcode']);
    $Sucursal = (isset($_GET['idsucursal']) && ($_GET['idsucursal'] != "")) ? base64_decode($_GET['idsucursal']) : "";

    // Sucursales
    if (PermitirFuncion(205)) {
        $Where = "CodigoCliente='$CardCode' AND TipoDireccion='S'";
        $SQL_Sucursal = Seleccionar("uvw_Sap_tbl_Clientes_Sucursales", "NombreSucursal, NumeroLinea", $Where);
    } else {
        $Where = "CodigoCliente='$CardCode' AND TipoDireccion='S' AND ID_Usuario = " . $_SESSION['CodUser'];
        $SQL_Sucursal = Seleccionar("uvw_tbl_SucursalesClienteUsuario", "NombreSucursal, NumeroLinea", $Where);
    }

    // Frecuencias
    $SQL_Frecuencia = Seleccionar("tbl_ProgramacionOrdenesServicioFrecuencia", "*");

    // Articulos
    $Where = "(CodigoCliente='$CardCode' AND LineaSucursal='$Sucursal' AND Estado='Y') OR IdTipoListaArticulo='2'";
    $SQL_LMT = ($Sucursal != "") ? Seleccionar("uvw_Sap_tbl_ArticulosLlamadas", "*", $Where, "IdTipoListaArticulo, ItemCode") : "";
}
?>

<!doctype html>
<html>
<head>
<?php include_once "includes/cabecera.php";?>

<style>
	body{
		background-color: #ffffff;
		overflow-x: auto;
	}

	#from .ibox-content{
		padding: 0px !important;
	}
	#from .form-control{
		width: auto;
		height: 28px;
	}
	#from .table > tbody > tr > td{
		padding: 1px !important;
		vertical-align: middle;
	}
	#from .select2-container{ width: 100% !important; }
	#from .bg-success[readonly]{
		background-color: #1c84c6 !important;
		color: #ffffff !important;
	}

	.select2-container, .swal2-container {
		z-index: 10000;
	}
	.select2-search--inline {
    display: contents;
	}
	.select2-search__field:placeholder-shown {
		width: 100% !important;
	}
</style>

<script>
var json=[];
var cant=0;

function BorrarLinea(){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea eliminar este item? Este proceso no se puede revertir.')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=21&linenum="+json,
			success: function(response){
				window.location.href="detalle_cronograma_servicios.php?<?php echo $_SERVER['QUERY_STRING']; ?>";
			}
		});
	}
}

function DuplicarLinea(LineNum){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea duplicar este item? El nuevo registro se pondr'+String.fromCharCode(225)+' al final de la tabla.')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=27&linenum="+LineNum,
			success: function(response){
				window.location.href="detalle_cronograma_servicios.php?<?php echo $_SERVER['QUERY_STRING']; ?>";
			}
		});
	}
}

function CorregirSuc(LineNum, Val='', Clt=''){
//	$('.ibox-content').toggleClass('sk-loading',true);
	if(Val=='Sucursal no existe'){
		let select=document.createElement("select");
		let td=document.getElementById("SucCliente_"+LineNum);

		select.className='form-control';
		select.id="SelSucCliente_"+LineNum;

		td.innerHTML='';
		td.appendChild(select);

		$.ajax({
			type: "POST",
			url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Clt+"&sucline=1&tdir=S&selec=1",
			success: function(response){
				$('#SelSucCliente_'+LineNum).html(response);
				select.addEventListener("change", function(){
					let value=document.getElementById("SelSucCliente_"+LineNum).value
					CambiarSuc(LineNum, value);
				});
//				$('#SelSucCliente_'+LineNum).trigger("change");
//				$('.ibox-content').toggleClass('sk-loading',false);
			}
		});
	}else{
		if(confirm('Se cambiar'+String.fromCharCode(225)+' el nombre de la sucursal en el cronograma seg'+String.fromCharCode(250)+'n el que est'+String.fromCharCode(225)+' en el dato maestro.')){
			$.ajax({
				type: "GET",
				url: "includes/procedimientos.php?type=48&linenum="+LineNum,
				success: function(response){
					window.location.href="detalle_cronograma_servicios.php?<?php echo $_SERVER['QUERY_STRING']; ?>";
				}
			});
		}
	}

}

function CambiarSuc(LineNum, IdSuc){
//	console.log("LineNum", LineNum)
//	console.log("IdSuc", IdSuc)
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "GET",
		url: "includes/procedimientos.php?type=48&linenum="+LineNum+"&idsuc="+IdSuc,
		success: function(response){
			window.location.href="detalle_cronograma_servicios.php?<?php echo $_SERVER['QUERY_STRING']; ?>";
		}
	});
}

function ActualizarDatos(name,id,line){//Actualizar datos asincronicamente
	$.ajax({
		type: "GET",
		url: "registro.php?P=36&doctype=11&type=2&name="+name+"&value="+Base64.encode(document.getElementById(name+id).value)+"&line="+line,
		success: function(response){
			if(response!="Error"){
				window.parent.document.getElementById('TimeAct').innerHTML="<strong>Actualizado:</strong> "+response;
			}
		}
	});
}

function ActualizarDatosModal(datos, linea){
	console.log(datos);

    let Actualizado = true;

    for (const clave in datos) {
        if (datos.hasOwnProperty(clave)) {
            let valor = Base64.encode(datos[clave]);
            $.ajax({
                type: "GET",
                url: `registro.php?P=36&doctype=11&type=2&name=${clave}&value=${valor}&line=${linea}&new=1`,
                success: function(response) {
                    if(response != "Error") {
                        Actualizado = false;
                    }
                },
                error: function(error) {
                    console.error(`Error en Línea 230 con ${key}`);
                    Actualizado = false;
                }
            });
        }
    }

	if(Actualizado) {
		Swal.fire("Actualizado", "Se han actualizado todos los campos correctamente.", "success");
	} else {
		Swal.fire("Error", "Ocurrió un error durante la actualización.", "error");
	}
}


function Seleccionar(ID){
	var btnBorrarLineas=document.getElementById('btnBorrarLineas');
	var Check = document.getElementById('chkSel'+ID).checked;
	var sw=-1;
	json.forEach(function(element,index){
//		console.log(element,index);
//		console.log(json[index])deta
		if(json[index]==ID){
			sw=index;
		}

	});

	if(sw>=0){
		json.splice(sw, 1);
		cant--;
	}else if(Check){
		json.push(ID);
		cant++;
	}
	if(cant>0){
		$("#btnBorrarLineas").removeClass("disabled");
	}else{
		$("#btnBorrarLineas").addClass("disabled");
	}

	//console.log(json);
}

function SeleccionarTodos(){
	var Check = document.getElementById('chkAll').checked;
	if(Check==false){
		json=[];
		cant=0;
		$("#btnBorrarLineas").addClass("disabled");
	}
	$(".chkSel").prop("checked", Check);
	if(Check){
		$(".chkSel").trigger('change');
	}
}

function ConsultarArticulo(Articulo){
	if(Articulo.value!=""){
		self.name='opener';
		remote=open('articulos.php?id='+Articulo+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}

function Resaltar(ID){
	$("input").removeClass('bg-success');
	$("#"+ID).find("input").addClass('bg-success');
}
</script>
</head>

<body>
	<div class="row">
		
			<div class="row">
				<div class="form-group">
					<div class="col-lg-2">
						<div class="btn-group">
							<button data-toggle="dropdown" class="btn btn-info dropdown-toggle"><i class="fa fa-download"></i> Descargar formato <i class="fa fa-caret-down"></i></button>
							<ul class="dropdown-menu">
								<li>
									<a class="dropdown-item alkin" href="sapdownload.php?type=<?php echo base64_encode('2'); ?>&id=<?php echo base64_encode('19'); ?>&IdCliente=<?php echo $_GET['Cliente']; ?>&IdPeriodo=<?php echo $Anno; ?>&IdSucursal=<?php echo $_GET['Sucursal'] ?? '-1'; ?>&TipoExp=1" target="_blank">PDF</a>
								</li>
								<li>
									<a class="dropdown-item alkin" href="sapdownload.php?type=<?php echo base64_encode('2'); ?>&id=<?php echo base64_encode('19'); ?>&IdCliente=<?php echo $_GET['Cliente']; ?>&IdPeriodo=<?php echo $Anno; ?>&IdSucursal=<?php echo $_GET['Sucursal'] ?? '-1'; ?>&TipoExp=2" target="_blank">Excel</a>
								</li>
							</ul>
						</div> <!-- btn-group-->
					</div>

					<div class="col-lg-4">
						<button class="btn btn-warning" id="ActualizarCronograma"><i class="fa fa-refresh"></i> Actualizar cronograma basado en LMT</button>

						<button style="margin-left: 5px;" type="button" class="btn btn-sm btn-circle" data-toggle="tooltip" data-html="true"
						title="Actualiza de manera masiva los campos de Áreas, Servicios, Método de aplicación de las LMT hacia el Cronograma de Servicios."><i class="fa fa-info"></i></button>
					</div>

					<div class="col-lg-1">
						<button type="button" id="btnNuevo" class="btn btn-success" onClick="AgregarLMT();"><i class="fa fa-plus-circle"></i> Adicionar zonas</button>
					</div>
				</div>
				<br><br>
			</div> <!-- row -->

			<div class="row m-t-md">
				<div class="col-lg-12">
					<div class="tabs-container">
						<ul class="nav nav-tabs">
							<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Contenido</a></li>
						</ul> <!-- nav-tabs -->

						<div class="tab-content">
							<div id="tab-1" class="tab-pane active">
								<!-- Inicio, modalInfo -->
								<div class="modal inmodal fade" id="modalInfo" tabindex="-1" role="dialog" aria-hidden="true">
									<div class="modal-dialog modal-lg" style="width: 70% !important;">
										<div class="modal-content">
											<div class="modal-header">
												<h4 class="modal-title">Más información</h4>
											</div>

											<form id="formActualizarInfo">
												<div class="modal-body">
													<div class="form-group">
														<div class="ibox-content">
															<div class="form-group">
																<input type="hidden" id="IdLinea" name="IdLinea">
															</div>
															<div class="form-group">
																<div class="col-md-12">
																	<label class="control-label">Sucursal Cliente</label>
																	<select id="SucursalCliente" name="SucursalCliente" class="form-control select2" disabled>
																		<option value="">Seleccione...</option>
																		<?php while ($row_Sucursal = sqlsrv_fetch_array($SQL_Sucursal)) {?>
																			<!-- El ID es NumeroLinea -->
																			<option value="<?php echo $row_Sucursal['NombreSucursal']; ?>"><?php echo $row_Sucursal['NombreSucursal']; ?></option>
																		<?php }?>
																	</select>
																</div>
															</div>

															<br><br><br><br>
															<div class="form-group">
																<div class="col-md-6">
																	<label class="control-label">Estado</label>
																	<select class="form-control" id="Estado" name="Estado" disabled>
																		<option value="Y">ACTIVO</option>
																		<option value="N">INACTIVO</option>
																	</select>
																</div>
																<div class="col-md-6">
																	<label class="control-label">Frecuencia</label>
																	<select name="Frecuencia" class="form-control" id="Frecuencia" disabled>
																		<option value="">Ninguna</option>
																		<?php while ($row_Frecuencia = sqlsrv_fetch_array($SQL_Frecuencia)) {?>
																			<option value="<?php echo $row_Frecuencia['DeFrecuencia']; ?>"><?php echo $row_Frecuencia['DeFrecuencia'] . " (" . $row_Frecuencia['CantidadVeces'] . ")"; ?></option>
																		<?php }?>
																	</select>
																</div>
															</div>

															<br><br><br><br>
															<div class="form-group">
																<div class="col-md-12">
																	<label class="control-label"><i id="BuscarArticulo" title="Consultar Articulo LMT" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Lista materiales / artículos</label>
																	<select name="ListaLMT" class="form-control select2" id="ListaLMT" disabled>
																		<option value="">Seleccione...</option>

																		<?php while ($row_LMT = sqlsrv_fetch_array($SQL_LMT)) {?>

																			<?php if (($row_LMT['IdTipoListaArticulo'] == 1) && ($sw_Clt == 0)) {?>
																				<?php echo "<optgroup label='Cliente'></optgroup>"; ?>
																				<?php $sw_Clt = 1;?>
																			<?php } elseif (($row_LMT['IdTipoListaArticulo'] == 2) && ($sw_Std == 0)) {?>
																				<?php echo "<optgroup label='Genericas'></optgroup>"; ?>
																				<?php $sw_Std = 1;?>
																			<?php }?>

																			<option value="<?php echo $row_LMT['ItemCode']; ?>"><?php echo $row_LMT['ItemCode'] . " - " . $row_LMT['ItemName'] . " (SERV: " . substr($row_LMT['Servicios'], 0, 20) . " - ÁREA: " . substr($row_LMT['Areas'], 0, 20) . ")"; ?></option>
																		<?php }?>
																	</select>
																</div>
															</div>

															<br><br><br><br>
															<div class="form-group">
																<div class="col-md-6">
																	<label class="control-label">Áreas (3000 caracteres)</label>
																	<textarea name="Areas" rows="3" maxlength="3000" class="form-control" id="Areas" type="text"></textarea>
																</div>

																<div class="col-md-6">
																	<label class="control-label">Servicios (3000 caracteres)</label>
																	<textarea name="Servicios" rows="3" maxlength="3000" class="form-control" id="Servicios" type="text"></textarea>
																</div>
															</div>

															<br><br><br><br><br><br>
															<div class="form-group">
																<div class="col-md-6">
																	<label class="control-label">Método de aplicación (3000 caracteres)</label>
																	<textarea name="MetodoAplicacion" rows="3" maxlength="3000" class="form-control" id="MetodoAplicacion" type="text"></textarea>
																</div>

																<div class="col-md-6">
																	<label class="control-label">Observaciones (3000 caracteres)</label>
																	<textarea name="Observaciones" rows="3" maxlength="3000" class="form-control" id="Observaciones" type="text"></textarea>
																</div>
															</div>
															<br><br>
														</div> <!-- ibox-content -->
													</div> <!-- form-group -->
												</div> <!-- modal-body -->

												<div class="modal-footer">
													<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i> Aceptar</button>
													<button type="button" class="btn btn-warning m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
												</div> <!-- modal-footer -->
											</form>
										</div>
									</div>
								</div>
								<!-- Fin, modalInfo -->

								<div id="from" name="form">
									<div class="ibox-content">
										<?php include "includes/spinner.php";?>
									<table width="100%" class="table table-bordered dataTables-example">
										<thead>
											<tr>
												<th class="text-center form-inline w-80"><div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div> <button type="button" id="btnBorrarLineas" title="Borrar lineas" class="btn btn-danger btn-xs disabled" onClick="BorrarLinea();"><i class="fa fa-trash"></i></button></th>
												<th>&nbsp;</th>
												<th>#</th>
												<th>Validación</th>
												<th>Sucursal cliente</th>
												<th>Código artículo</th>
												<th>Nombre artículo</th>
												<th>Estado</th>
												<th>Frecuencia</th>
												<th>Fecha Últ. Actualización</th>
												<th>Usuario Últ. Actualización</th>
											</tr>
										</thead>
										<tbody>
										<?php while ($row = sqlsrv_fetch_array($SQL)) { ?>
										<tr id="<?php echo $i; ?>" onClick="Resaltar('<?php echo $i; ?>');">
											<td class="text-center">
												<div class="checkbox checkbox-success no-margins">
													<input type="checkbox" class="chkSel" id="chkSel<?php echo $row['ID']; ?>" value="" onChange="Seleccionar('<?php echo $row['ID']; ?>');" aria-label="Single checkbox One"><label></label>
												</div>
											</td>

											<td class="text-center form-inline w-80">
												<!-- SMM, 10/01/2022 -->
												<button type="button" title="Actualizar información de la LMT" class="btn btn-warning btn-xs" onClick="ActualizarLinea(<?php echo $row['ID']; ?>);"><i class="fa fa-refresh"></i></button>

												<!-- SMM, 10/01/2022 -->
												<button type="button" title="Más información" class="btn btn-info btn-xs" onClick="InfoLinea(<?php echo $row['ID']; ?>);"><i class="fa fa-info"></i></button>

												<button type="button" title="Duplicar linea" class="btn btn-success btn-xs" onClick="DuplicarLinea(<?php echo $row['ID']; ?>);"><i class="fa fa-copy"></i></button>
												<?php if (!strstr($row['Validacion'], "OK")) {?>
													<button type="button" title="Corregir sucursal" class="btn btn-warning btn-xs" onClick="CorregirSuc(<?php echo $row['ID']; ?>,'<?php echo $row['Validacion']; ?>','<?php echo $row['IdCliente']; ?>');"><i class="fa fa-gavel"></i></button>
												<?php }?>
											</td>

											<td class="text-center"><?php echo $i ?? ""; ?></td>
											<td><span class="<?php if (strstr($row['Validacion'], "OK")) {echo "badge badge-primary";} else {echo "badge badge-danger";}?>"><?php echo $row['Validacion']; ?></span></td>
											<td id="SucCliente_<?php echo $row['ID']; ?>"><input size="50" type="text" id="SucursalCliente<?php echo $i; ?>" name="SucursalCliente[]" class="form-control" readonly value="<?php echo $row['IdSucursalCliente']; ?>"></td>
											<td><input size="20" type="text" id="CodListaMateriales<?php echo $i; ?>" name="CodListaMateriales[]" class="form-control btn-link" readonly value="<?php echo $row['IdArticuloLMT']; ?>" onClick="ConsultarArticulo('<?php echo base64_encode($row['IdArticuloLMT']); ?>');" title="Consultar artículo" style="cursor: pointer"></td>
											<td><input size="80" type="text" id="ListaMateriales<?php echo $i; ?>" name="ListaMateriales[]" class="form-control" readonly value="<?php echo $row['DeArticuloLMT']; ?>"></td>
											<td><input size="15" type="text" id="Estado<?php echo $i; ?>" name="Estado[]" class="form-control" readonly value="<?php echo $row['NombreEstado']; ?>"></td>
											<td><input size="15" type="text" id="Frecuencia<?php echo $i; ?>" name="Frecuencia[]" class="form-control" readonly value="<?php echo $row['Frecuencia']; ?>"></td>
											<td><input size="15" type="text" id="FechaActualizacion<?php echo $i; ?>" name="FechaActualizacion[]" class="form-control" value="<?php if ($row['FechaActualizacion'] != "") {echo $row['FechaActualizacion']->format('Y-m-d H:i');}?>" readonly></td>
											<td><input size="20" type="text" id="Usuario<?php echo $i; ?>" name="Usuario[]" class="form-control" value="<?php echo $row['Usuario']; ?>" readonly></td>
										</tr>
										<?php }?>
										</tbody>
									</table>
									</div>
								</div> <!-- form -->

								<script>
									function ActualizarLinea(ID) {
										Swal.fire({
											title: "¿Está seguro que desea actualizar la linea en base a la LMT?",
											icon: 'question',
											showCancelButton: true,
											confirmButtonText: 'Si, confirmo',
											cancelButtonText: 'No'
										}).then((result) => {
											if (result.isConfirmed) {

												// Cargando...
												$('.ibox-content').toggleClass('sk-loading', true);

												$.ajax({
													type: "GET",
													url: `includes/procedimientos.php?type=52&Metodo=2&Linea=${ID}&Cliente=<?php echo base64_decode($_GET['cardcode'] ?? ""); ?>&Sucursal=<?php echo base64_decode($_GET['idsucursal'] ?? ""); ?>&Periodo=<?php echo base64_decode($_GET['periodo'] ?? ""); ?>`,
													success: function(response) {
														Swal.fire({
															title: '¡Listo!',
															text: 'La linea se actualizo exitosamente',
															icon: 'success'
														}); // Swal
													}
												}); // ajax

												// Carga terminada.
												$('.ibox-content').toggleClass('sk-loading', false);
											}
										}); // Swal
									}

									// SMM, 10/01/2022
									function InfoLinea(ID) {
										$.ajax({
											url:"ajx_buscar_datos_json.php",
											data:{
												type: 44,
												id: ID
											},
											dataType:'json',
											success: function(linea){
												$("#IdLinea").val(ID);

												let sucursal = linea.SucursalCliente;
												$("#SucursalCliente").val(sucursal);
												$("#SucursalCliente").trigger("change");

												$("#Estado").val(linea.Estado);
												$("#Estado").trigger("change");

												let frecuencia = (linea.Frecuencia !== "Ninguna") ? linea.Frecuencia : "";
												$("#Frecuencia").val(frecuencia);
												$("#Frecuencia").trigger("change");

												let articuloLMT = linea.ArticuloLMT;
												$("#BuscarArticulo").on("click", function() {
													let base64 = btoa(articuloLMT);
													ConsultarArticulo(base64);
												});
												$.ajax({
													type: "POST",
													url: `ajx_cbo_select.php?type=11&id=<?php echo $CardCode; ?>&suc=${sucursal}`,
													success: function(response){
														$('#ListaLMT').html(response).fadeIn();
														$('#ListaLMT').trigger('change');

														$("#ListaLMT").val(articuloLMT);
														$("#ListaLMT").trigger("change");

														$('.ibox-content').toggleClass('sk-loading', false);
													},
													error: function(data) {
														console.error("510->", data.responseText);

														$('.ibox-content').toggleClass('sk-loading', false);
													}
												});

												$("#Areas").val(linea.Areas);
												$("#Servicios").val(linea.Servicios);
												$("#MetodoAplicacion").val(linea.MetodoAplicacion);
												$("#Observaciones").val(linea.Observaciones);
											},
											error: function(error) {
												console.error("520->", error.responseText);

												// $('.ibox-content').toggleClass('sk-loading', false);
											}
										});

										// Mostrar modal
										$('#modalInfo').modal("show");
									}

									$("#formActualizarInfo").on("submit", function(event) {
										event.preventDefault(); // Evitar redirección del formulario

										Swal.fire({
											title: "¿Está seguro que desea actualizar la linea?",
											icon: "question",
											showCancelButton: true,
											confirmButtonText: "Si, confirmo",
											cancelButtonText: "No"
										}).then((result) => {
											if (result.isConfirmed) {
												let ID = $("#IdLinea").val();

												// Cargando...
												$('.ibox-content').toggleClass('sk-loading', true);

												let datos = {};

												/*
												datos.IdSucursal = $("#SucursalCliente").val();
												datos.Estado = $("#Estado").val();
												datos.Frecuencia = $("#Frecuencia").val();
												datos.IdArticuloLMT = $("#ListaLMT").val();
												*/

												datos.Areas = $("#Areas").val();
												datos.Servicios = $("#Servicios").val();
												datos.MetodoAplicacion = $("#MetodoAplicacion").val();
												datos.Observaciones = $("#Observaciones").val();

												ActualizarDatosModal(datos, ID);

												// Mostrar modal
												$('#modalInfo').modal("hide");

												// Carga terminada.
												$('.ibox-content').toggleClass('sk-loading', false);
											}
										}); // Swal.fire
									});

									$(document).ready(function(){
										// SMM, 12/01/2022
										$(".select2").select2();

										$(".alkin").on('click', function(){
												$('.ibox-content').toggleClass('sk-loading');
											});
										$('.dataTables-example').DataTable({
											searching: false,
											info: false,
											paging: false,
											//fixedHeader: true,

										});
									});
								</script>
							</div>
						</div> <!-- tab-content -->
					</div> <!-- tabs-container -->
				</div> <!-- col-lg-12 -->
			</div> <!-- row m-t-md -->
		
	</div> <!-- row -->
</body>
</html>
<?php
sqlsrv_close($conexion);
?>