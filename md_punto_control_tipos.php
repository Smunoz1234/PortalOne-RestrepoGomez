<?php
require_once "includes/conexion.php";

$Title = "Crear Nuevo Registro";
$Metodo = 1;

$edit = isset($_POST['edit']) ? $_POST['edit'] : 0;
$doc = isset($_POST['doc']) ? $_POST['doc'] : "";
$id = isset($_POST['id']) ? $_POST['id'] : "";

$SQL_FamiliasModal = Seleccionar('tbl_Plagas_Familias', '*');
$SQL_IconosModal = Seleccionar('tbl_PuntoControl_Iconos', '*');
$SQL_ClasesModal = Seleccionar('tbl_PuntoControl_Clases', '*');
$SQL_SNZonasModal = Seleccionar('tbl_SociosNegocios_Zonas', '*');

if ($edit == 1 && $id != "") {
    $Metodo = 2;

    if ($doc == "Familia") {
        $SQL = Seleccionar('tbl_Plagas_Familias', '*', "id_familia_plaga='$id'");
        $row = sqlsrv_fetch_array($SQL);

        $Title = "Editar Familia de Plagas";
    } elseif ($doc == "Icono") {
        $SQL = Seleccionar('tbl_PuntoControl_Iconos', '*', "id_icono='$id'");
        $row = sqlsrv_fetch_array($SQL);

        $Title = "Editar Icono de Punto de Control";
    } elseif ($doc == "Tipo") {
        $SQL = Seleccionar('tbl_PuntoControl_Tipos', '*', "id_tipo_punto_control='$id'");
        $row = sqlsrv_fetch_array($SQL);

        $Title = "Editar Tipo de Punto de Control";
    }
}

// SMM, 03/03/2023
$ruta_Iconos = ObtenerVariable("CarpetaTmp") . "/pc_iconos/" . $_SESSION['CodUser'] . "/";
?>

<style>
	.select2-container {
		z-index: 10000;
	}
	.select2-search--inline {
    display: contents;
	}
	.select2-search__field:placeholder-shown {
		width: 100% !important;
	}

	.dd-container img {
		max-height: 30px;
	}
</style>

<form id="frm_NewParam" method="post" action="punto_control_tipos.php" enctype="multipart/form-data">

<div class="modal-header">
	<h4 class="modal-title">
		<?php echo $Title; ?>
	</h4>
</div>

<div class="modal-body">
	<div class="form-group">
		<div class="ibox-content">
			<?php include "includes/spinner.php";?>

			<?php if ($doc == "Familia") {?>

				<!-- Inicio Familia -->
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">ID Familia Plaga <span class="text-danger">*</span></label>
						<input <?php if ($edit == 1) {echo "readonly";}?> type="text" class="form-control" autocomplete="off" required id="id_familia_plaga" name="id_familia_plaga" value="<?php if ($edit == 1) {echo $row['id_familia_plaga'];}?>">
					</div>

					<div class="col-md-6">
						<label class="control-label">Familia Plaga <span class="text-danger">*</span></label>
						<input type="text" class="form-control" autocomplete="off" required id="familia_plaga" name="familia_plaga" value="<?php if ($edit == 1) {echo $row['familia_plaga'];}?>">
					</div>
				</div> <!-- form-group -->

				<br><br>
				<!-- Fin Familia -->

			<?php } elseif ($doc == "Icono") {?>

				<!-- Inicio Icono -->
				<div class="form-horizontal">
					<div class="form-group">
						<label class="control-label">ID Icono <span class="text-danger">*</span></label>
						<br>
						<div class="col-md-6 form-group">
							<input <?php if ($edit == 1) {echo "readonly";}?> type="text" class="form-control" autocomplete="off" required name="id_icono" id="id_icono" value="<?php if ($edit == 1) {echo $row['id_icono'];}?>">
						</div>
					</div>

					<div class="form-group">
						<label class="control-label">Icono <span class="text-danger">*</span></label>
						<br>
						<div class="col-md-9">
							<div class="form-group">
								<div class="fileinput fileinput-new input-group" data-provides="fileinput">
									<div class="form-control" data-trigger="fileinput">
										<i class="glyphicon glyphicon-file fileinput-exists"></i>
										<span class="fileinput-filename"></span>
									</div>
									<span class="input-group-addon btn btn-default btn-file">
										<span class="fileinput-new">Seleccionar</span>
										<span class="fileinput-exists">Cambiar</span>
										<input name="Img" type="file" id="Img" onchange="uploadImage('Img'); document.getElementById('icono').value = document.getElementById('Img').value;" required="required"/>
									</span>
									<a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Quitar</a>
								</div>
								<div class="row">
									<div id="msgImg" style="display:none" class="alert alert-info">
										<i class="fa fa-info-circle"></i> <span>Imagen cargada éxitosamente.<span>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-3">
							<img id="viewImg" style="max-width: 100%; max-height: 50px;" src="">
							<input type="hidden" name="icono" id="icono">
						</div>
					</div>
				</div>
				<!-- Fin Icono -->

			<?php } elseif ($doc == "Tipo") {?>

				<!-- Inicio Tipo -->
				<div class="form-group">
					<div class="col-md-9">
						<label class="control-label">ID Icono</label>
						<select id="iconList" name="iconList">
							<?php while ($row_Icono = sqlsrv_fetch_array($SQL_IconosModal)) {?>
								<option value="<?php echo $row_Icono['id_icono']; ?>" data-imagesrc="<?php echo $ruta_Iconos . $row_Icono['icono']; ?>" data-description="<?php echo $row_Icono['icono']; ?>"><?php echo $row_Icono['id_icono']; ?></option>
							<?php }?>
						</select>
					</div>

					<div class="col-md-3">
						<input type="hidden" name="id_icono" id="id_icono" class="form-control">
					</div>
				</div> <!-- form-group -->

				<br><br><br><br><br><br>
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">ID Tipo Punto Control <span class="text-danger">*</span></label>
						<input <?php if ($edit == 1) {echo "readonly";}?> required type="text" class="form-control" autocomplete="off" id="id_tipo_punto_control" name="id_tipo_punto_control" value="<?php if ($edit == 1) {echo $row['id_tipo_punto_control'];}?>">
					</div>

					<div class="col-md-6">
						<label class="control-label">Tipo Punto Control <span class="text-danger">*</span></label>
						<input type="text" class="form-control" required autocomplete="off" id="tipo_punto_control" name="tipo_punto_control" value="<?php if ($edit == 1) {echo $row['tipo_punto_control'];}?>">
					</div>
				</div> <!-- form-group -->

				<br><br><br><br>
				<div class="form-group">
					<div class="col-md-6">
						<label class="control-label">ID Familia Plaga</label>
						<select id="id_familia_plaga" name="id_familia_plaga" class="form-control">
							<option value="" <?php if ($edit == 0) {echo "disabled selected";}?>>Seleccione...</option>

							<?php while ($row_Familia = sqlsrv_fetch_array($SQL_FamiliasModal)) {?>
								<option value="<?php echo $row_Familia['id_familia_plaga']; ?>" <?php if (isset($row['id_familia_plaga']) && ($row['id_familia_plaga'] == $row_Familia['id_familia_plaga'])) {echo "selected";}?>><?php echo $row_Familia['id_familia_plaga'] . " - " . $row_Familia['familia_plaga']; ?></option>
							<?php }?>
						</select>
					</div>

					<div class="col-md-6">
						<label class="control-label">ID Clase Control</label>
						<select id="id_clase_control" name="id_clase_control" class="form-control">
							<option value="" <?php if ($edit == 0) {echo "disabled selected";}?>>Seleccione...</option>

							<?php while ($row_Clase = sqlsrv_fetch_array($SQL_ClasesModal)) {?>
								<option value="<?php echo $row_Clase['id_clase_control']; ?>" <?php if (isset($row['id_clase_control']) && ($row['id_clase_control'] == $row_Clase['id_clase_control'])) {echo "selected";}?>><?php echo $row_Clase['id_clase_control'] . " - " . $row_Clase['clase_control']; ?></option>
							<?php }?>
						</select>
					</div>
				</div> <!-- form-group -->

				<br><br><br><br>
				<div class="form-group">

					<div class="col-md-6">
						<label class="control-label">Código Prefijo</label>
						<input type="text" class="form-control" autocomplete="off" id="codigo_prefijo" name="codigo_prefijo" value="<?php if ($edit == 1) {echo $row['codigo_prefijo'];}?>">
					</div>
				</div> <!-- form-group -->

				<br><br><br><br>
				<div class="form-group">
					<div class="row">
						<div class="col-md-6">
							<label class="control-label">ID Color</label>
						</div>
						<div class="col-md-6">
							<label class="control-label">Estado <span class="text-danger">*</span></label>
						</div>
					</div>

					<div class="row">
						<div class="col-md-2">
							<input type="color" class="form-control" autocomplete="off" id="id_color" name="id_color" value="<?php if ($edit == 1) {echo $row['id_color'];}?>" oninput="$('#color').val(this.value);">
						</div>
						<div class="col-md-4">
							<input type="text" class="form-control" id="color" value="<?php if ($edit == 1) {echo $row['id_color'];}?>" readonly>
						</div>

						<div class="col-md-6">
							<div class="form-group">
								<select class="form-control" id="estado" name="estado" required>
									<option value="Y" <?php if (($edit == 1) && ($row['estado'] == "Y")) {echo "selected";}?>>ACTIVO</option>
									<option value="N" <?php if (($edit == 1) && ($row['estado'] == "N")) {echo "selected";}?>>INACTIVO</option>
								</select>
							</div>
						</div>
					</div>
				</div> <!-- form-group -->

				<div class="form-group">
					<div class="col-md-12">
						<label class="control-label">Descripción</label>
						<textarea name="descripcion" rows="3" maxlength="250" class="form-control" id="descripcion" type="text"><?php if ($edit == 1) {echo $row['descripcion'];}?></textarea>
					</div>
				</div>

				<br><br>
				<!-- Fin Tipo -->

			<?php }?>
		</div> <!-- ibox-content -->
	</div> <!-- form-group -->
</div> <!-- modal-body -->

<div class="modal-footer">
	<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i> Aceptar</button>
	<button type="button" class="btn btn-warning m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
</div>

	<input type="hidden" id="TipoDoc" name="TipoDoc" value="<?php echo $doc; ?>" />
	<input type="hidden" id="ID_Actual" name="ID_Actual" value="<?php echo $id; ?>" />
	<input type="hidden" id="Metodo" name="Metodo" value="<?php echo $Metodo; ?>" />
	<input type="hidden" id="frmType" name="frmType" value="1" />

</form>

<script>
$(document).ready(function() {
	// SMM, 06/03/2023
	$('#iconList').ddslick({
		width: "100%",
		background: "#FFFFFF",
		selectText: "Seleccione...",
		onSelected: function (data) {
			console.log(data);
			$("#id_icono").val(data.selectedData.text);
		}
	});

	<?php if ($edit == 1) {?>
		// Basado en, https://github.com/prashantchaudhary/ddslick/blob/master/jquery.ddslick.js
		let iconIndex = $('#iconList').find(".dd-option-value[value= '<?php echo $row["id_icono"] ?? ""; ?>']").parents("li").prevAll().length;
		$('#iconList').ddslick('select', {index: iconIndex});
	<?php }?>

	// Activación del componente "tagsinput"
	$('input[data-role=tagsinput]').tagsinput({
		confirmKeys: [32, 44] // Espacio y coma.
	});

	// Ajusto el ancho del componente "tagsinput"
	$('.bootstrap-tagsinput').css("display", "block");
	$('.bootstrap-tagsinput > input').css("width", "100%");

	$("#frm_NewParam").validate({
		submitHandler: function(form){
			let Metodo = document.getElementById("Metodo").value;
			if(Metodo!="3"){
				Swal.fire({
				title: "¿Está seguro que desea guardar los datos?",
				icon: "question",
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
			$('.ibox-content').toggleClass('sk-loading',true);
			form.submit();
			}
	}
	 });

	$('.chosen-select').chosen({width: "100%"});
	$(".select2").select2();
 });
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
				url: 'upload_image.php?persistent=pc_iconos',
				type: 'post',
				data: formData,
				contentType: false,
				processData: false,
				success: function(response) {
					console.log(response);
					json_response = JSON.parse(response);

					photo_name = json_response.nombre;
					photo_route = json_response.directorio + photo_name;

					testImage(photo_route).then(success => {
						console.log(success);
						console.log("Line 1100, testImage", photo_route);

						$("#icono").val(photo_name); // guarda el nombre del archivo en el input "icono"
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
