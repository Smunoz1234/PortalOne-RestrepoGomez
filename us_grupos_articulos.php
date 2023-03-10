<?php
require_once "includes/conexion.php";

if (isset($_GET['id']) && $_GET['id'] != "") {
    $IdUsuario = base64_decode($_GET['id']);
} else {
    $IdUsuario = "";
}

$SQL_GA = Seleccionar("uvw_Sap_tbl_GruposArticulos", "*", "Locked = 'N'", "ItmsGrpNam");
$SQL_GruposArticulos = Seleccionar("uvw_tbl_UsuariosGruposArticulos", "*", "ID_Usuario='$IdUsuario'");

// Insertar o eliminar un grupo de artículos
if (isset($_POST['Metodo'])) {
    $ID = ($_POST['Metodo'] == 3) ? $_POST['ID_Interno'] : "NULL"; // 3 - Eliminar

    $Usuario = "'" . $_SESSION['CodUser'] . "'";
    $FechaHora = "'" . FormatoFecha(date('Y-m-d'), date('H:i:s')) . "'";

    try {
        $Param = array(
            $ID,
            $_POST['Metodo'],
        );

        if ($_POST['Metodo'] != 3) {
            $Param = array_merge($Param, array(
                "'" . $_POST['ID_Usuario'] . "'",
                "'" . $_POST['IdGrupoArticulo'] . "'",
                "'" . $_POST['GrupoArticulo'] . "'",
                "'" . $_POST['Comentarios'] . "'",
                $Usuario, // @id_usuario_creacion
                $FechaHora, // @fecha_creacion
                $FechaHora, // @hora_creacion
            ));
        }

        $SQL = EjecutarSP('sp_tbl_UsuariosGruposArticulos', $Param);
        if ($SQL) {
            exit("OK");

        } else {
            die(print_r(sqlsrv_errors(), true));
        }
    } catch (Exception $e) {
        die(print_r($e->getMessage(), true));
    }
}
?>

<style>
.select2-dropdown{
    z-index: 9999;
}
</style>

<div class="wrapper wrapper-content">
	<div class="modal inmodal fade" id="modalGruposArticulos" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"> Adicionar grupo de articulos</h4>
				</div>

				<form id="formGruposArticulos">

					<div class="modal-body">
						<div class="form-group">
							<div class="ibox-content">
								<?php include "includes/spinner.php";?>

								<div class="form-group">
									<div class="col-md-12">
										<label class="control-label">Grupo de Articulo SAP <span class="text-danger">*</span></label>
										<select name="ItmsGrpCod" id="ItmsGrpCod" class="form-control select2" required>
											<option value="" disabled selected>Seleccione...</option>
											<?php while ($row_GA = sqlsrv_fetch_array($SQL_GA)) {?>
												<option value="<?php echo $row_GA['ItmsGrpCod']; ?>" data-name="<?php echo $row_GA['ItmsGrpNam']; ?>">
													<?php echo $row_GA['ItmsGrpNam']; ?>
												</option>
											<?php }?>
										</select>
									</div>
								</div>

								<div class="form-group">
									<div class="col-md-12">
										<label class="control-label">Comentarios</label>
										<textarea name="Comentarios" rows="3" maxlength="3000" class="form-control" id="Comentarios" type="text"></textarea>
									</div>
								</div>

							</div>
						</div> <!-- form-group ibox-content -->
					</div> <!-- modal-body -->

					<div class="modal-footer">
						<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i> Aceptar</button>
						<button type="button" class="btn btn-warning m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
					</div>

				</form>
			</div> <!-- modal-content -->
		</div> <!-- modal-dialog -->
	</div> <!-- inmodal -->
</div> <!-- wrapper-content -->

<!-- Inicio, ibox GruposArticulos -->
<div class="ibox" id="GruposArticulos">
	<div class="ibox-title bg-success">
		<h5 class="collapse-link"><i class="fa fa-list"></i> Lista Grupos Articulos</h5>
			<a class="collapse-link pull-right">
			<i class="fa fa-chevron-up"></i>
		</a>
	</div>
	<div class="ibox-content">
		<div class="row m-b-md">
			<div class="col-lg-12">
				<button class="btn btn-primary pull-right" type="button" id="NewMotivo" onClick="$('#modalGruposArticulos').modal('show');"><i class="fa fa-plus-circle"></i> Agregar nuevo</button>
			</div>
		</div>
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-hover dataTables-example">
				<thead>
					<tr>
						<th>Código Grupo Artículo</th>
						<th>Descripción Grupo Artículo</th>
						<th>Comentarios</th>
						<th>Fecha Creación</th>
						<th>Usuario Creación</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>
					<?php while ($row_GrupoArticulo = sqlsrv_fetch_array($SQL_GruposArticulos)) {?>
						<tr>
							<td><?php echo $row_GrupoArticulo['IdGrupoArticulo']; ?></td>
							<td><?php echo $row_GrupoArticulo['GrupoArticulo']; ?></td>
							<td><?php echo $row_GrupoArticulo['Comentarios']; ?></td>
							<td><?php echo isset($row_GrupoArticulo['fecha_creacion']) ? date_format($row_GrupoArticulo['fecha_creacion'], 'Y-m-d H:i:s') : ""; ?></td>
							<td><?php echo $row_GrupoArticulo['usuario_creacion']; ?></td>
							<td>
								<button type="button" class="btn btn-danger btn-xs" onClick="EliminarFila('<?php echo $row_GrupoArticulo['ID']; ?>');"><i class="fa fa-trash"></i> Eliminar</button>
							</td>
						</tr>
					<?php }?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<!-- Fin, ibox GruposArticulos -->

<script>
	function EliminarFila(ID) {
		Swal.fire({
			title: "¿Esta seguro que desea eliminar el grupo de artículos?",
			icon: "question",
			showCancelButton: true,
			confirmButtonText: "Si, confirmo",
			cancelButtonText: "No"
		}).then((result) => {
			if (result.isConfirmed) {
				$('.ibox-content').toggleClass('sk-loading',true);

				$.ajax({
					type: "post",
					url: "us_grupos_articulos.php",
					data: {
						Metodo: 3
						, ID_Interno: ID
						},
					async: false,
					success: function(data){
						if(data == "OK") {
							$(document).ready(function() {
								Swal.fire({
									title: '¡Listo!',
									text: 'Registro eliminado correctamente.',
									icon: 'success'
								});
							});
						} else {
							console.log(data);

							$(document).ready(function() {
								Swal.fire({
									title: '¡Error!',
									text: 'Ocurrio un error al momento de eliminar el registro.',
									icon: 'warning'
								});
							});
						}

						ConsultarTab('4');
						$('.ibox-content').toggleClass('sk-loading',false);
					},
					error: function(error) {
						$(document).ready(function() {
							Swal.fire({
								title: '¡Error!',
								text: 'Ocurrio un error al momento de consultar al servidor.',
								icon: 'warning'
							});
						});

						console.log(error);
						$('.ibox-content').toggleClass('sk-loading',false);
					}
				});
			} // if
		});
	}

	$(document).ready(function(){
		$(".select2").select2();

		$("#formGruposArticulos").on("submit", function(event) {
			event.preventDefault(); // Evitar redirección del formulario

			$('.ibox-content').toggleClass('sk-loading',true);

			let IdGrupoArticulo = document.getElementById('ItmsGrpCod').value;
			let GrupoArticulo = $("#ItmsGrpCod").find(':selected').data('name');
			let Comentarios = document.getElementById('Comentarios').value;

			$.ajax({
				type: "post",
				url: "us_grupos_articulos.php",
				data: {
					  Metodo: 1
					, ID_Usuario: <?php echo $IdUsuario; ?>
					, IdGrupoArticulo: IdGrupoArticulo
					, GrupoArticulo: GrupoArticulo
					, Comentarios: Comentarios
					},
				async: false,
				success: function(data){
					if(data == "OK") {
						$(document).ready(function() {
							Swal.fire({
								title: '¡Listo!',
								text: 'Registro creado correctamente.',
								icon: 'success'
							});
						});
					} else {
						console.log(data);

						$(document).ready(function() {
							Swal.fire({
								title: '¡Error!',
								text: 'Ocurrio un error al momento de eliminar el registro.',
								icon: 'warning'
							});
						});
					}

					ConsultarTab('4');
					$('.ibox-content').toggleClass('sk-loading',false);
				},
				error: function(error) {
					$(document).ready(function() {
						Swal.fire({
							title: '¡Error!',
							text: 'Ocurrio un error al momento de consultar al servidor.',
							icon: 'warning'
						});
					});

					console.log(error);
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			}); // ajax

			// Cerrar el modal sin fallos
			$("#modalGruposArticulos").modal("hide");
			$('body').removeClass('modal-open');
			$('.modal-backdrop').remove();
			return false;
		});

		$('.dataTables-example').DataTable({
			pageLength: 10,
			dom: '<"html5buttons"B>lTfgitp',
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