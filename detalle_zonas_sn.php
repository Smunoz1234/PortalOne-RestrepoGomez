<?php
require_once "includes/conexion.php";
// PermitirAcceso(318);
$CardCodeID = "";
$SucursalSN = "";

// SMM, 24/02/2023
if (isset($_GET['idsucursal']) && ($_GET['idsucursal'] != "")) {
    $SucursalSN = "AND [id_consecutivo_direccion]='" . base64_decode($_GET['idsucursal']) . "'";
}

// SMM, 24/02/2022
$SQL_SucursalSN = "";
if (isset($_GET['cardcode']) && ($_GET['cardcode'] != "")) {
    $CardCodeID = base64_decode($_GET['cardcode']);
    $SucursalSN = (isset($_GET['idsucursal']) && ($_GET['idsucursal'] != "")) ? base64_decode($_GET['idsucursal']) : "";

    // Sucursales
    if (PermitirFuncion(205)) {
        $Where = "CodigoCliente='$CardCodeID' AND TipoDireccion='S'";
        $SQL_SucursalSN = Seleccionar("uvw_Sap_tbl_Clientes_Sucursales", "NombreSucursal, NumeroLinea", $Where);
    } else {
        $Where = "CodigoCliente='$CardCodeID' AND TipoDireccion='S' AND ID_Usuario = " . $_SESSION['CodUser'];
        $SQL_SucursalSN = Seleccionar("uvw_tbl_SucursalesClienteUsuario", "NombreSucursal, NumeroLinea", $Where);
    }
}

// SMM, 24/02/2023
$SQL = Seleccionar("tbl_SociosNegocios_Zonas", "*", "[id_socio_negocio]='$CardCodeID'", "[id_zona_sn]");

// SMM, 25/02/2023
$msg_error = "";
$parametros = array();

$coduser = $_SESSION['CodUser'];
$datetime = FormatoFecha(date('Y-m-d'), date('H:i:s'));

$type = $_POST['type'] ?? 0;
$id_zona_sn = $_POST['id_zona_sn'] ?? "";
$zona_sn = $_POST['zona_sn'] ?? "";
$id_socio_negocio = $_POST['id_socio_negocio'] ?? "";
$socio_negocio = $_POST['socio_negocio'] ?? "";
$id_consecutivo_direccion = $_POST['id_consecutivo_direccion'] ?? "NULL";
$id_direccion_destino = $_POST['id_direccion_destino'] ?? "";
$direccion_destino = $_POST['direccion_destino'] ?? "";
$estado = $_POST['estado'] ?? "";
$observaciones = $_POST['observaciones'] ?? "";

$id_usuario_creacion = "'$coduser'";
$fecha_creacion = "'$datetime'";
$hora_creacion = "'$datetime'";
$id_usuario_actualizacion = "'$coduser'";
$fecha_actualizacion = "'$datetime'";
$hora_actualizacion = "'$datetime'";

if ($type == 1) {
    $msg_error = "No se pudo crear el registro.";

    $parametros = array(
        $type,
        "'$id_zona_sn'",
        "'$zona_sn'",
        "'$id_socio_negocio'",
        "'$socio_negocio'",
        $id_consecutivo_direccion,
        "'$id_direccion_destino'",
        "'$direccion_destino'",
        "'$estado'",
        "'$observaciones'",
        $id_usuario_actualizacion,
        $fecha_actualizacion,
        $hora_actualizacion,
        $id_usuario_creacion,
        $fecha_creacion,
        $hora_creacion,
    );

} elseif ($type == 2) {
    $msg_error = "No se pudo actualizar el registro.";

    $parametros = array(
        $type,
        "'$id_zona_sn'",
        "'$zona_sn'",
        "'$id_socio_negocio'",
        "'$socio_negocio'",
        $id_consecutivo_direccion,
        "'$id_direccion_destino'",
        "'$direccion_destino'",
        "'$estado'",
        "'$observaciones'",
        $id_usuario_actualizacion,
        $fecha_actualizacion,
        $hora_actualizacion,
    );

} elseif ($type == 3) {
    $msg_error = "No se pudo eliminar el registro.";

    $parametros = array(
        $type, // 3 - Eliminar
        "'$id_zona_sn'",
    );
}

if ($type != 0) {
    $SQL_Operacion = EjecutarSP('sp_tbl_SociosNegocios_Zonas', $parametros);

    if (!$SQL_Operacion) {
        echo $msg_error;
    } else {
        $row = sqlsrv_fetch_array($SQL_Operacion);

        if (isset($row['Error']) && ($row['Error'] != "")) {
            echo "$msg_error ";
            echo "(" . $row['Error'] . ")";
        } else {
            echo "OK";
        }
    }

    // Mostrar mensajes AJAX.
    exit();
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

<div class="modal inmodal fade" id="modalZonasSN" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Adicionar Zonas a Socios de negocios</h4>
			</div> <!-- modal-header -->

			<form id="modalForm">
				<div class="modal-body">
					<div class="form-group">
						<div class="ibox-content">
							<input type="hidden" id="Type">

							<div class="form-group">
								<div class="col-md-6">
									<label class="control-label">Socio Negocio <span class="text-danger">*</span></label>
									<input required type="text" class="form-control" autocomplete="off" id="IDSocioNegocio" value="<?php echo $CardCodeID; ?>" readonly>
								</div>

								<div class="col-md-6">
									<label class="control-label">Estado</label>
									<select class="form-control" id="Estado">
										<option value="Y">ACTIVO</option>
										<option value="N">INACTIVO</option>
									</select>
								</div>
							</div> <!-- form-group -->

							<br><br><br><br>
							<div class="form-group">
								<div class="col-md-6">
									<label class="control-label">ID Zona <span class="text-danger">*</span></label>
									<input required type="text" class="form-control" autocomplete="off" id="IDZonaSN">
								</div>

								<div class="col-md-6">
									<label class="control-label">Nombre Zona <span class="text-danger">*</span></label>
									<input required type="text" class="form-control" autocomplete="off" id="ZonaSN">
								</div>
							</div> <!-- form-group -->

							<br><br><br><br>
							<div class="form-group">
								<div class="col-md-12">
									<label class="control-label">Sucursal Socio Negocio <span class="text-danger">*</span></label>
									<select id="SucursalSN" class="form-control" <?php if ($SucursalSN != "") {echo "readonly";}?> required>
										<option value="" <?php if ($SucursalSN == "") {echo "disabled selected";}?>>Seleccione...</option>

										<?php while ($row_SucursalSN = sqlsrv_fetch_array($SQL_SucursalSN)) {?>
											<option value="<?php echo $row_SucursalSN['NumeroLinea']; ?>" <?php if ($SucursalSN == $row_SucursalSN['NumeroLinea']) {echo "selected";}?>><?php echo $row_SucursalSN['NombreSucursal']; ?></option>
										<?php }?>
									</select>
								</div>
							</div> <!-- form-group -->

							<br><br><br><br>
							<div class="form-group">
								<div class="col-md-12">
									<label class="control-label">Observaciones (250 caracteres)</label>
									<textarea type="text" class="form-control" name="Observaciones" id="Observaciones" rows="3" maxlength="250"></textarea>
								</div>
							</div> <!-- form-group -->

							<br><br>
						</div> <!-- ibox-content -->
					</div> <!-- form-group -->
				</div> <!-- modal-body -->

				<div class="modal-footer">
					<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i> Aceptar</button>
					<button type="button" class="btn btn-warning m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
				</div> <!-- modal-footer -->
			</form>
		</div> <!-- modal-content -->
	</div> <!-- modal-dialog -->
</div> <!-- modal -->

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
			<button type="button" id="btnNuevo" class="btn btn-success" onClick="MostrarModal();"><i class="fa fa-plus-circle"></i> Adicionar zonas</button>
		</div>
	</div> <!-- form-group -->

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

					<div class="ibox-content">
						<?php include "includes/spinner.php";?>

						<table width="100%" class="table table-bordered dataTables-example">
							<thead>
								<tr>
									<th class="text-center form-inline w-80"><div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div> <button type="button" id="btnBorrarLineas" title="Borrar lineas" class="btn btn-danger btn-xs disabled" onClick="BorrarLinea();"><i class="fa fa-trash"></i></button></th>
									<th>&nbsp;</th>
									<th>ID Zona</th>
									<th>Zona</th>
									<th>ID Socio Negocio</th>
									<th>Socio Negocio</th>
									<th>ID Consecutivo Dirección</th>
									<th>ID Dirección Destino</th>
									<th>Dirección Destino</th>
									<th>Estado</th>
									<th>Observaciones</th>
									<th>Fecha Actualización</th>
									<th>ID Usuario Actualización</th>
								</tr>
							</thead>

							<tbody>
								<?php while ($row = sqlsrv_fetch_array($SQL)) {?>
								<tr>
									<td class="text-center">
										<div class="checkbox checkbox-success no-margins">
											<input type="checkbox" class="chkSel" id="chkSel<?php echo $row['ID']; ?>" value="" onChange="Seleccionar('<?php echo $row['ID']; ?>');" aria-label="Single checkbox One"><label></label>
										</div>
									</td>

									<td class="text-center form-inline w-80">
										<button type="button" title="Actualizar información de la LMT" class="btn btn-warning btn-xs" onClick="ActualizarLinea(<?php echo $row['ID']; ?>);"><i class="fa fa-refresh"></i></button>
										<button type="button" title="Más información" class="btn btn-info btn-xs" onClick="InfoLinea(<?php echo $row['ID']; ?>);"><i class="fa fa-info"></i></button>
										<button type="button" title="Duplicar linea" class="btn btn-success btn-xs" onClick="DuplicarLinea(<?php echo $row['ID']; ?>);"><i class="fa fa-copy"></i></button>
									</td>

									<td><?php echo $row['id_zona_sn']; ?></td>
									<td><?php echo $row['zona_sn']; ?></td>
									<td><?php echo $row['id_socio_negocio']; ?></td>
									<td><?php echo $row['socio_negocio']; ?></td>
									<td><?php echo $row['id_consecutivo_direccion']; ?></td>
									<td><?php echo $row['id_direccion_destino']; ?></td>
									<td><?php echo $row['direccion_destino']; ?></td>

									<td>
										<span class="badge <?php echo ($row['estado'] == "Y") ? "badge-primary" : "badge-danger"; ?>">
											<?php echo ($row['estado'] == "Y") ? "Activo" : "Inactivo"; ?>
										</span>
									</td>

									<td><?php echo $row['observaciones']; ?></td>

									<td><?php echo isset($row['fecha_actualizacion']) ? date_format($row['fecha_actualizacion'], 'Y-m-d H:i:s') : ""; ?></td>
									<td><?php echo $row['id_usuario_actualizacion']; ?></td>
								</tr>
								<?php }?>
							</tbody>
						</table>
					</div> <!-- ibox-content -->
				</div> <!-- tab-1 -->
			</div> <!-- tab-content -->
		</div> <!-- tabs-container -->
	</div> <!-- col-lg-12 -->
</div> <!-- row m-t-md -->

<script>
	// SMM, 24/02/2023
	function OperacionModal() {
		$.ajax({
			type: "POST",
			url: "detalle_zonas_sn.php",
			data: {
				type: $("#Type").val(),
				id_zona_sn: $("#IDZonaSN").val(),
				zona_sn: $("#ZonaSN").val(),
				id_socio_negocio: $("#IDSocioNegocio").val(),
				socio_negocio: "",
				id_consecutivo_direccion: $("#SucursalSN").val(),
				id_direccion_destino: "",
				direccion_destino: "",
				estado: $("#Estado").val(),
				observaciones: $("#Observaciones").val(),
			},
			success: function(response) {
				Swal.fire({
					icon: (response == "OK") ? "success" : "warning'",
					title: (response == "OK") ? "Operación exitosa" : "Ocurrió un error",
					text: (response == "OK") ? "La consulta se ha ejecutado correctamente." : response
				}).then((result) => {
					if (result.isConfirmed) {
						location.reload();
					}
				});
			},
			error: function(error) {
				console.error("560->", error.responseText);
			}
		});
	}

	// SMM, 24/02/2023
	function MostrarModal(ID = "") {
		if(ID != "") {
			$("#Type").val(2);

			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{
					type: 44,
					id: ID
				},
				dataType:'json',
				success: function(linea){
					$("#IdLinea").val(ID);

					$("#Estado").val(linea.Estado);
					$("#Estado").trigger("change");

					$("#Areas").val(linea.Areas);
					$("#Servicios").val(linea.Servicios);
					$("#MetodoAplicacion").val(linea.MetodoAplicacion);
					$("#Observaciones").val(linea.Observaciones);
				},
				error: function(error) {
					console.error("520->", error.responseText);
				}
			});
		} else {
			$("#Type").val(1);
		}

		// Siempre se muestra el Modal.
		$('#modalZonasSN').modal("show");
	}

	$("#modalForm").on("submit", function(event) {
		event.preventDefault(); // Evitar redirección del formulario

		Swal.fire({
			title: "¿Está seguro que desea continuar con la operación?",
			icon: "question",
			showCancelButton: true,
			confirmButtonText: "Si, confirmo",
			cancelButtonText: "No"
		}).then((result) => {
			if (result.isConfirmed) {
				OperacionModal();

				// Ocultar modal.
				$('#modalZonasSN').modal("hide");
			}
		}); // Swal.fire
	});

	$(document).ready(function(){
		$(".select2").select2();

		$(".alkin").on('click', function(){
			$('.ibox-content').toggleClass('sk-loading');
		});

		$('.dataTables-example').DataTable({
			searching: false,
			info: false,
			paging: false,
			language: {
				"decimal":        "",
				"thousands":      ",",
				"emptyTable":     "No se encontraron resultados."
			}
		});
	});
</script>

</body>

</html>
<?php sqlsrv_close($conexion);?>
