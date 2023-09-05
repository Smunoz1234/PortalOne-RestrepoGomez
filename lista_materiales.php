<?php require_once "includes/conexion.php";
PermitirAcceso(1208);

// Dimensiones. SMM, 21/07/2023
$DimSeries = intval(ObtenerVariable("DimensionSeries"));
$SQL_Dimensiones = Seleccionar('uvw_Sap_tbl_Dimensiones', '*', "DimActive='Y'");

$array_Dimensiones = [];
while ($row_Dimension = sqlsrv_fetch_array($SQL_Dimensiones)) {
	array_push($array_Dimensiones, $row_Dimension);
}

$encode_Dimensiones = json_encode($array_Dimensiones);
$cadena_Dimensiones = "JSON.parse('$encode_Dimensiones'.replace(/\\n|\\r/g, ''))";
// echo "<script> console.log('cadena_Dimensiones'); </script>";
// echo "<script> console.log($cadena_Dimensiones); </script>";
// Hasta aquí. SMM, 21/07/2023

$dt_LS = 0; //sw para saber si vienen datos de la llamada de servicio. 0 no vienen. 1 si vienen.
$dt_OF = 0; //sw para saber si vienen datos de una Oferta de venta.
$msg_error = ""; //Mensaje del error
$ItemCode = 0;
$IdPortal = 0; //Id del portal para las ordenes que fueron creadas en el portal, para eliminar el registro antes de cargar al editar

if (isset($_GET['id']) && ($_GET['id'] != "")) { //ID de la Lista de material (ItemCode)
	$ItemCode = base64_decode($_GET['id']);
}

if (isset($_POST['ItemCode']) && ($_POST['ItemCode'] != "")) { //Tambien el Id interno, pero lo envío cuando mando el formulario
	$ItemCode = base64_decode($_POST['ItemCode']);
	$IdEvento = base64_decode($_POST['IdEvento'] ?? "");
}

// SMM, 18/07/2023
if (isset($_GET['ItemCode']) && ($_GET['ItemCode'] != "")) { // También el ID Interno, pero lo envío desde Articulos (Crear LMT).
	$ItemCode = base64_decode($_GET['ItemCode']);
}

if (isset($_POST['swError']) && ($_POST['swError'] != "")) { //Para saber si ha ocurrido un error.
	$sw_error = $_POST['swError'];
} else {
	$sw_error = 0;
}

if (isset($_REQUEST['tl']) && ($_REQUEST['tl'] != "")) { //0 Si se está creando. 1 Se se está editando.
	$edit = $_REQUEST['tl'];
} else {
	$edit = 0;
}

// Stiven Muñoz Murillo
if (isset($_GET['ext']) && ($_GET['ext'] == 1)) {
	$sw_ext = 1; //Se está abriendo como pop-up
} elseif (isset($_POST['ext']) && ($_POST['ext'] == 1)) {
	$sw_ext = 1; //Se está abriendo como pop-up
} else {
	$sw_ext = 0;
}
// 12/01/2022

if (isset($_POST['P']) && ($_POST['P'] != "")) { //Grabar lista de materiales

	try {
		$Type = ($_POST['tl'] == 1) ? 2 : 1;

		$ParametrosCabListaMateriales = array(
			"'" . $_POST['ItemCode'] . "'",
			"'" . base64_decode($_POST['IdEvento']) . "'",
			"'" . $_POST['ItemName'] . "'",
			"'" . $_POST['Cantidad'] . "'",
			"'" . $_POST['ListaPrecio'] . "'",
			"'" . $_POST['TipoListaMat'] . "'",
			"'" . $_POST['Proyecto'] . "'",

			"'" . ($_POST['Dim1'] ?? "") . "'", // SMM, 21/07/2023
			"'" . ($_POST['Dim2'] ?? "") . "'", // SMM, 21/07/2023
			"'" . ($_POST['Dim3'] ?? "") . "'", // SMM, 21/07/2023

			"'" . $_POST['Cliente'] . "'",
			"'" . $_POST['Sucursal'] . "'",
			"'" . $_POST['CDU_Areas'] . "'",
			"'" . $_POST['CDU_Servicios'] . "'",
			"'" . $_POST['CodigoPlantilla'] . "'",
			"'" . $_SESSION['CodUser'] . "'",
			"'" . $_SESSION['CodUser'] . "'",
			"'" . ($_POST['CDU_IdMarca'] ?? "") . "'",
			"'" . ($_POST['CDU_IdLinea'] ?? "") . "'",
			"'" . $_POST['CDU_MetodoAplicacion'] . "'",
			$_POST['CDU_TiempoTarea'] ?? 0,
			// Campos nuevos
			$_POST['CDU_Frecuencia'] ?? 0,
			"'" . ($_POST['CDU_IdLineaNegocio'] ?? "") . "'",
			"'" . ($_POST['CDU_IdArticuloVTAFactura'] ?? "") . "'",
			"'" . ($_POST['IdBodega'] ?? "") . "'", //SMM, 05/09/2023
			// Tipo de método
			$Type
		);

		// Insertar a la tabla de PortalOne
		$SQL_CabeceraListaMateriales = EjecutarSP('sp_tbl_ListaMateriales', $ParametrosCabListaMateriales, $_POST['P']);
		if ($SQL_CabeceraListaMateriales) {
			$ItemCode = $_POST['ItemCode'] ?? 0;
			$IdEvento = base64_decode($_POST['IdEvento']);

			// Consultar cabecera
			$Where_json = ($_POST['tl'] == 0) ? "IdEvento='$IdEvento'" : "ItemCode='$ItemCode' AND IdEvento='$IdEvento'";
			$SQL_json = Seleccionar("tbl_ListaMateriales", '*', $Where_json);
			$row_json = sqlsrv_fetch_array($SQL_json);

			// Consultar detalle
			$Where_det = ($_POST['tl'] == 0) ? "IdEvento='$IdEvento'" : "Father='$ItemCode' AND IdEvento='$IdEvento'";
			$SQL_det = Seleccionar("tbl_ListaMaterialesDetalle", '*', $Where_det, 'ChildNum');

			$Detalle = array();
			while ($row_det = sqlsrv_fetch_array($SQL_det)) {

				array_push(
					$Detalle,
					array(
						"id_lista_material" => $row_det['Father'],
						"id_linea" => intval($row_det['ChildNum']),
						"id_linea_visual_order" => intval($row_det['VisOrder']),
						"id_articulo" => $row_det['ItemCode'],
						"tipo_linea" => "" . $row_det['Type'] . "",
						"cant_articulo" => number_format($row_det['Cantidad'], 2),
						"id_bodega" => $row_det['WhsCode'],
						"precio_articulo" => intval($row_det['Precio']),
						"und_medida" => $row_det['UndMedida'],
						"metodo_emision" => $row_det['MetodoEmision'],
						"comentarios" => null,
						"id_lista_precio" => intval($row_det['IdListaPrecio']),
						"dim1" => $row_det['OcrCode'],
						"dim2" => $row_det['OcrCode2'],
						"dim3" => $row_det['OcrCode3'],
						"dim4" => null,
						"dim5" => null,
						"id_proyecto" => $row_det['IdProyecto'],
						"CDU_id_servicio" => $row_det['CDU_IdServicio'],
						"CDU_id_metodo_aplicacion" => $row_det['CDU_IdMetodoAplicacion'],
						"CDU_id_tipo_plagas" => $row_det['CDU_IdTipoPlagas'],
						"CDU_areas_controladas" => $row_det['CDU_AreasControladas'],
						"CDU_cant_litros" => intval($row_det['CDU_CantLitros']),
						"metodo_linea" => intval($row_det['Metodo']),
						"metodo" => intval($row_json['Metodo']),
					)
				);
			}

			$Cabecera = array(
				"id_lista_material" => strtoupper($row_json['ItemCode']),
				"lista_material" => strtoupper($row_json['ItemName']),
				"tipo_lista_material" => $row_json['TipoListaMat'],
				"cantidad" => number_format($row_json['Cantidad'], 2),
				"cantidad_prom_produccion" => intval($row_json['TamProduccion']),
				"id_lista_precio" => intval($row_json['IdListaPrecio']),
				"dim1" => $row_json['OcrCode'],
				"dim2" => $row_json['OcrCode2'],
				"dim3" => $row_json['OcrCode3'],
				"dim4" => "",
				"dim5" => "",
				"id_proyecto" => $row_json['IdProyecto'],
				"id_bodega" => $row_json['ToWH'],
				"CDU_id_socio_negocio" => $row_json['CDU_CodigoCliente'],
				"CDU_socio_negocio" => $row_json['CDU_NombreCliente'],
				"CDU_id_consecutivo_direccion" => intval($row_json['CDU_IdSucursalCliente']),
				"CDU_id_direccion_destino" => $row_json['CDU_SucursalCliente'],
				"CDU_servicios" => $row_json['CDU_Servicios'],
				"CDU_areas" => $row_json['CDU_Areas'],
				"id_documento" => "",
				"id_oportunidad_venta" => "",
				"id_plantilla_actividad" => $row_json['CDU_CodPlantilla'],
				"metodo" => intval($row_json['Metodo']),
				"CDU_tiempo_tarea" => intval($row_json['CDU_TiempoTarea']),
				"CDU_metodo_aplicacion" => $row_json['CDU_MetodoAplicacion'],
				"frecuencia" => intval($row_json['CDU_Frecuencia']),
				"id_linea_negocio" => $row_json['CDU_IdLineaNegocio'],
				"CDU_id_articulo_vta_factura" => ($row_json['CDU_IdArticuloVTAFactura'] ?? ""),
				"lista_material_lineas" => $Detalle,
			);

			$Cabecera_json = json_encode($Cabecera);
			// echo $Cabecera_json;
			// exit();

			// SMM, 05/07/2023
			$usuario = $_SESSION['User'];
			$fechaCrea = ($row_json['FechaRegistro']->format('Y-m-d') . "T" . $row_json['FechaRegistro']->format('H:i:s'));
			$horaCrea = ($row_json['FechaRegistro']->format('Y-m-d') . "T" . $row_json['FechaRegistro']->format('H:i:s'));
			$fechaAct = ($row_json['FechaActualizacion']->format('Y-m-d') . "T" . $row_json['FechaActualizacion']->format('H:i:s'));
			$horaAct = ($row_json['FechaActualizacion']->format('Y-m-d') . "T" . $row_json['FechaActualizacion']->format('H:i:s'));
			$segundoAct = intval($row_json['FechaActualizacion']->format('s'));

			//Enviar datos al WebServices
			try {
				if ($_POST['tl'] == 0) { //Creando
					$Cabecera["usuario_creacion"] = $usuario;
					$Cabecera["fecha_creacion"] = $fechaCrea;
					$Cabecera["hora_creacion"] = $horaCrea;
					$Cabecera["crear_articulo"] = PermitirFuncion(1005);

					$Metodo = "ListasMateriales";
					$Resultado = EnviarWebServiceSAP($Metodo, $Cabecera, true, true);
				} else { //Editando
					$Cabecera["usuario_actualizacion"] = $usuario;
					$Cabecera["fecha_actualizacion"] = $fechaAct;
					$Cabecera["hora_actualizacion"] = $horaAct;
					$Cabecera["seg_actualizacion"] = ($segundoAct + 1); // Porque no se puede enviar como Cero

					$Metodo = "ListasMateriales/$ItemCode";
					$Resultado = EnviarWebServiceSAP($Metodo, $Cabecera, true, true, "PUT");
				}

				if ($Resultado->Success == 0) {
					$sw_error = 1;
					$msg_error = $Resultado->Mensaje;
					$Cabecera_json = json_encode($Cabecera);
				} else {
					$sw_error = 0; // SMM, 06/07/2023
					$Msg = ($_POST['tl'] == 1) ? "OK_LMTUpd" : "OK_LMTAdd";

					// SMM, 05/09/2023
					if ($_POST['tl'] == 0) {
						$Cliente = $row_json['CDU_CodigoCliente'];
						$NombreCliente = $row_json['CDU_NombreCliente'];

						sqlsrv_close($conexion);
						header("Location:consultar_lista_materiales.php?Cliente=$Cliente&NombreCliente=$NombreCliente&a=" . base64_encode($Msg));
					} elseif ($_POST['tl'] == 1) {
						sqlsrv_close($conexion);
						header('Location:lista_materiales.php?id=' . base64_encode($ItemCode) . '&tl=1&a=' . base64_encode($Msg));
					}

					// En caso de error, no redirige.
					$edit = 1;
					$_GET['a'] = base64_encode($Msg);
				}
			} catch (Exception $e) {
				echo 'Excepcion capturada: ', $e->getMessage(), "\n";
			}

		} else {
			$sw_error = 1;
			$msg_error = "Ha ocurrido un error al insertar la lista de materiales";
		}
	} catch (Exception $e) {
		echo 'Excepcion capturada: ', $e->getMessage(), "\n";
	}

}

$row_Articulo = array();
if ($edit == 0 && $sw_error == 0) {
	$SQL_NewIdEvento = EjecutarSP('sp_ObtenerIdEvento');
	$row_NewIdEvento = sqlsrv_fetch_array($SQL_NewIdEvento);
	$IdEvento = $row_NewIdEvento[0];

	// SMM, 18/07/2023
	if ($ItemCode !== 0) {
		$SQL_Articulos = Seleccionar("uvw_Sap_tbl_ArticulosTodos", '*', "ItemCode='$ItemCode'");
		$row_Articulo = sqlsrv_fetch_array($SQL_Articulos);

		// Mostrar la salida de print_r con formato legible
		// echo '<pre>' . print_r($row_Articulo, true) . '</pre>';
	}
}

if ($edit == 1 && $sw_error == 0) {

	$ParametrosLimpiar = array(
		"'" . $ItemCode . "'",
		"'" . $_SESSION['CodUser'] . "'",
	);
	$LimpiarLista = EjecutarSP('sp_EliminarListaMateriales', $ParametrosLimpiar);

	$SQL_IdEvento = sqlsrv_fetch_array($LimpiarLista);
	$IdEvento = $SQL_IdEvento[0];

	//echo $IdEvento;
	//exit();

	//Lista de material
	$SQL = Seleccionar("tbl_ListaMateriales", '*', "ItemCode='$ItemCode' AND IdEvento='$IdEvento'");
	$row = sqlsrv_fetch_array($SQL);

	$codigoCliente = isset($row['CDU_CodigoCliente']) ? $row['CDU_CodigoCliente'] : "";
	$SQL_Sucursal = Seleccionar("uvw_Sap_tbl_Clientes_Sucursales", "NombreSucursal, NumeroLinea", "CodigoCliente='$codigoCliente'");

	// Stiven Muñoz Murillo, 07/02/2022
	// Lista de materiales (SAP)
	$SQL_Sap = Seleccionar("uvw_Sap_tbl_ListaMateriales", '*', "ItemCode='$ItemCode'");
	$row_Sap = sqlsrv_fetch_array($SQL_Sap);
	$row_Sap_encode = isset($row_Sap) ? json_encode($row_Sap) : "";
	$cadena_sap = isset($row_Sap) ? "JSON.parse('$row_Sap_encode'.replace(/\\n|\\r/g, ''))" : "'Not Found'";
	// echo "<script> console.log('SAP', $cadena_sap); </script>";
}

if ($sw_error == 1) {
	$Where = "ItemCode = '$ItemCode' AND IdEvento = '$IdEvento'";

	// Evento con mayor fecha de actualización. SMM, 05/09/2023 
	$Sub_Cons = "SELECT MAX(FechaActualizacion) FROM [tbl_ListaMateriales] WHERE $Where";

	// Eliminar Eventos Repetidos. SMM, 05/09/2023
	$Cons_Delete = "DELETE FROM [tbl_ListaMateriales] WHERE ($Where) AND FechaActualizacion < ($Sub_Cons);";
	sqlsrv_query($conexion, $Cons_Delete);
	
	// Lista de materiales
	$SQL = Seleccionar("tbl_ListaMateriales", '*', $Where);
	$row = sqlsrv_fetch_array($SQL);

	$codigoCliente = isset($row['CDU_CodigoCliente']) ? $row['CDU_CodigoCliente'] : "";
	$SQL_Sucursal = Seleccionar("uvw_Sap_tbl_Clientes_Sucursales", "NombreSucursal, NumeroLinea", "CodigoCliente='$codigoCliente'");

	$ItemCode = (isset($_POST['ItemCode']) && ($_POST['ItemCode'] != "")) ? $_POST['ItemCode'] : 0;
	$IdEvento = base64_decode($_POST['IdEvento']);

	// SMM, 27/07/2023
	if ($edit == 1) {
		// Lista de materiales (SAP)
		$SQL_Sap = Seleccionar("uvw_Sap_tbl_ListaMateriales", '*', "ItemCode='$ItemCode'");
		$row_Sap = sqlsrv_fetch_array($SQL_Sap);
		$row_Sap_encode = isset($row_Sap) ? json_encode($row_Sap) : "";
		$cadena_sap = isset($row_Sap) ? "JSON.parse('$row_Sap_encode'.replace(/\\n|\\r/g, ''))" : "'Not Found'";
		// echo "<script> console.log('SAP', $cadena_sap); </script>";
	}
}

// Se eliminaron las dimensiones, 27/07/2023

//Tipo lista
$SQL_TipoLista = Seleccionar('tbl_TipoListaMateriales', '*');

//Datos de dimensiones del usuario actual
$SQL_DatosEmpleados = Seleccionar('uvw_tbl_Usuarios', 'CentroCosto1, CentroCosto2, CentroCosto3', "ID_Usuario='" . $_SESSION['CodUser'] . "'");
$row_DatosEmpleados = sqlsrv_fetch_array($SQL_DatosEmpleados);

//Lista de precios
$SQL_ListaPrecios = Seleccionar('uvw_Sap_tbl_ListaPrecios', '*');

//Proyectos
$SQL_Proyecto = Seleccionar('uvw_Sap_tbl_Proyectos', '*', '', 'DeProyecto');

//Plantillas
$SQL_Plantilla = Seleccionar('uvw_tbl_PlantillaActividades', '*');

// @author Stiven Muñoz Murillo
// @version 05/12/2021

// Marcas de vehiculo en la tarjeta de equipo
$SQL_MarcaVehiculo = Seleccionar('uvw_Sap_tbl_ListaMateriales_MarcaVehiculo', '*');

// Lineas de vehiculo en la tarjeta de equipo
$SQL_LineaVehiculo = Seleccionar('uvw_Sap_tbl_ListaMateriales_LineaVehiculo', '*');

// SMM, 14/07/2023
$SQL_LineaNegocio = Seleccionar('uvw_Sap_tbl_ListaMateriales_LineaNegocio', '*');

// SMM, 18/07/2023
$SQL_ArticulosVTA = Seleccionar('uvw_Sap_tbl_Articulos_VTA_Factura', '*');

// SMM, 25/07/2023
$SQL_Bodega = SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes', 'WhsCode, WhsName', "IdTipoDocumento='17'", "WhsCode, WhsName", 'WhsName');

// Stiven Muñoz Murillo, 07/02/2022
$row_encode = isset($row) ? json_encode($row) : "";
$cadena = isset($row) ? "JSON.parse('$row_encode'.replace(/\\n|\\r/g, ''))" : "'Not Found'";
// echo "<script> console.log($cadena); </script>";

// SMM, 19/07/2023
$dt_LMT = 0;
if (isset($_GET['dt_LMT']) && ($_GET['dt_LMT']) == 1) { // Verificar que viene de una Lista de materiales (Duplicar)
	$dt_LMT = 1;
	$ID_Documento = "'" . base64_decode($_GET['LMT']) . "'";

	$Where = "ItemCode=$ID_Documento AND IdEvento='" . base64_decode($_GET['Evento']) . "'";
	// echo "SELECT * FROM tbl_ListaMateriales WHERE $Where";

	$SQL = Seleccionar("tbl_ListaMateriales", '*', $Where);
	$row = sqlsrv_fetch_array($SQL);

	// Lista de materiales (SAP)
	$SQL_Sap = Seleccionar("uvw_Sap_tbl_ListaMateriales", '*', "ItemCode=$ID_Documento");
	$row_Sap = sqlsrv_fetch_array($SQL_Sap);

	$ParametrosCopiar = array(
		$ID_Documento,
		$IdEvento, // Nuevo Evento desde "sp_ObtenerIdEvento"
		"'" . base64_decode($_GET['Evento']) . "'",
		"'" . $_SESSION['CodUser'] . "'",
	);

	$SQL_Copiar = EjecutarSP('sp_tbl_ListaMaterialesDet_To_ListaMaterialesDet', $ParametrosCopiar);
	if (!$SQL_Copiar) {
		echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Ha ocurrido un error!',
				text: 'No se pudo duplicar la Lista de materiales.',
				icon: 'error'
			});
		});
		</script>";
	}

	$codigoCliente = $row['CDU_CodigoCliente'] ?? "";
	$SQL_Sucursal = Seleccionar("uvw_Sap_tbl_Clientes_Sucursales", "NombreSucursal, NumeroLinea", "CodigoCliente='$codigoCliente'");
}

// SMM, 09/05/2023
$SQL_Codigo = Seleccionar("uvw_Sap_tbl_SeriesLMT", "CodigoSiguiente");
$row_Codigo = sqlsrv_fetch_array($SQL_Codigo);
$CodigoSiguiente = $row_Codigo["CodigoSiguiente"] ?? "";
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
	<?php include_once "includes/cabecera.php"; ?>
	<!-- InstanceBeginEditable name="doctitle" -->
	<title>Lista de materiales |
		<?php echo NOMBRE_PORTAL; ?>
	</title>
	<?php
	if (isset($_GET['a']) && $_GET['a'] == base64_encode("OK_LMTAdd")) {
		echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'La Lista de materiales ha sido creada exitosamente.',
				icon: 'success'
			});
		});
		</script>";
	}
	if (isset($_GET['a']) && $_GET['a'] == base64_encode("OK_LMTUpd")) {
		echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'La Lista de materiales ha sido actualizada exitosamente.',
				icon: 'success'
			});
		});
		</script>";
	}
	if (isset($sw_error) && ($sw_error == 1)) {
		echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Advertencia!',
                text: `" . LSiqmlObs($msg_error) . "`,
                icon: 'warning'
            });
		});
		console.log('json:','$Cabecera_json');
		</script>";
	}
	?>
	<!-- InstanceEndEditable -->
	<!-- InstanceBeginEditable name="head" -->
	<style>
		.panel-body {
			padding: 0px !important;
		}

		.tabs-container .panel-body {
			padding: 0px !important;
		}

		.nav-tabs>li>a {
			padding: 14px 20px 14px 25px !important;
		}
	</style>
	<script>
		function BuscarArticulo(dato) {
			let itemcode = document.getElementById("ItemCode");

			var lista_precio = document.getElementById("ListaPrecio").value;
			var proyecto = document.getElementById("Proyecto").value;

			// SMM, 21/07/2023
			var dim1 = ((document.getElementById("Dim1") || {}).value) || "";
			var dim2 = ((document.getElementById("Dim2") || {}).value) || "";
			var dim3 = ((document.getElementById("Dim3") || {}).value) || "";
			var dim4 = ((document.getElementById("Dim4") || {}).value) || "";
			var dim5 = ((document.getElementById("Dim5") || {}).value) || "";
			// Hasta aquí, 21/07/2023

			var posicion_x;
			var posicion_y;
			posicion_x = (screen.width / 2) - (1200 / 2);
			posicion_y = (screen.height / 2) - (500 / 2);
			if (dato != "") {
				if (itemcode.value != "" || !itemcode.required) {
					remote = open('buscar_articulo.php?dato=' + dato + '&idlistamaterial=' + btoa(itemcode.value) + '&evento=<?php if ($edit == 1) {
						echo base64_encode($row['IdEvento']);
					} else {
						echo base64_encode($IdEvento);
					} ?>&lista_precio=' + btoa(lista_precio) + '&proyecto=' + btoa(proyecto) + '&ocrcode=' + btoa(dim1) + '&ocrcode2=' + btoa(dim2) + '&ocrcode3=' + btoa(dim3) + '&tipodoc=3&doctype=17&todosart=1', 'remote', "width=1200,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left=" + posicion_x + ",top=" + posicion_y + "");
					remote.focus();
				} else {
					Swal.fire({
						title: "¡Advertencia!",
						text: "Debe seleccionar un artículo",
						icon: "warning",
						confirmButtonText: "OK"
					});
				}
			}
		}
		function ConsultarDatosCliente() {
			var Cliente = document.getElementById('CardCode');
			if (Cliente.value != "") {
				self.name = 'opener';
				remote = open('socios_negocios.php?id=' + Base64.encode(Cliente.value) + '&ext=1&tl=1', 'remote', 'location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
				remote.focus();
			}
		}
		function ConsultarPlantilla() {
			var CodigoPlantilla = document.getElementById('CodigoPlantilla');
			if (CodigoPlantilla.value != "") {
				self.name = 'opener';
				remote = open('plantilla_actividades.php?id=' + btoa(CodigoPlantilla.value) + '&tl=1&ext=1', 'remote', 'location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
				remote.focus();
			}
		}
	</script>
	<script type="text/javascript">
		$(document).ready(function () {//Cargar los combos dependiendo de otros
			$("#NombreCliente").change(function () {
				var NomCliente = document.getElementById("NombreCliente");
				var Cliente = document.getElementById("Cliente");
				if (NomCliente.value == "") {
					Cliente.value = "";
					$("#Cliente").trigger("change");
				}
			});
			$("#Cliente").change(function () {
				var Cliente = document.getElementById("Cliente");
				$.ajax({
					type: "POST",
					url: "ajx_cbo_sucursales_clientes_simple.php?CardCode=" + Cliente.value + "&sucline=1&selec=1&todos=0",
					success: function (response) {
						$('#Sucursal').html(response);

						// SMM, 18/07/2023
						<?php if (isset($row_Articulo["IdSucCliente"])) { ?>
							$("#Sucursal").val("<?php echo $row_Articulo["IdSucCliente"]; ?>");
						<?php } ?>

						$("#Sucursal").trigger("change");
					}
				});
			});

			// Stiven Muñoz Murillo, 28/12/2021
			$("#CDU_IdMarca").change(function () {
				$('.ibox-content').toggleClass('sk-loading', true);
				var marcaVehiculo = document.getElementById('CDU_IdMarca').value;
				$.ajax({
					type: "POST",
					url: "ajx_cbo_select.php?type=41&id=" + marcaVehiculo,
					success: function (response) {
						$('#CDU_IdLinea').html(response).fadeIn();
						$('#CDU_IdLinea').trigger('change');
						$('.ibox-content').toggleClass('sk-loading', false);
					}
				});
			});

			// SMM, 18/07/2023
			<?php if (isset($row_Articulo["IdCliente"])) { ?>
				$("#Cliente").trigger("change");
			<?php } ?>
		});
	</script>
	<!-- InstanceEndEditable -->
</head>

<!-- Stiven Muñoz Murillo -->

<body <?php if ($sw_ext == 1) {
	echo "class='mini-navbar'";
} ?>>
	<div id="wrapper">
		<?php if ($sw_ext != 1) {
			include "includes/menu.php";
		} ?>
		<div id="page-wrapper" class="gray-bg">
			<?php if ($sw_ext != 1) {
				include "includes/menu_superior.php";
			} ?>
			<!-- 12/01/2022 -->

			<!-- InstanceBeginEditable name="Contenido" -->
			<div class="row wrapper border-bottom white-bg page-heading">
				<div class="col-sm-8">
					<h2>Lista de materiales</h2>
					<ol class="breadcrumb">
						<li>
							<a href="index1.php">Inicio</a>
						</li>
						<li>
							<a href="#">Datos maestros</a>
						</li>
						<li class="active">
							<strong>Lista de materiales</strong>
						</li>
					</ol>
				</div>
			</div>

			<div class="wrapper wrapper-content">
				<?php if ($edit == 1) { ?>
					<div class="row">
						<div class="col-lg-3">
							<div class="ibox ">
								<div class="ibox-title">
									<h5><span class="font-normal">Creada por</span></h5>
								</div>
								<div class="ibox-content">
									<h3 class="no-margins">
										<?php if (isset($row_Sap['UsuarioCreacion']) && ($row_Sap['UsuarioCreacion'] != "")) {
											echo $row_Sap['UsuarioCreacion'];
										} else {
											echo "&nbsp;";
										} ?>
									</h3>
								</div>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="ibox ">
								<div class="ibox-title">
									<h5><span class="font-normal">Fecha creación</span></h5>
								</div>
								<div class="ibox-content">
									<h3 class="no-margins">
										<?php if (isset($row_Sap['FechaHoraCreacion']) && ($row_Sap['FechaHoraCreacion'] != "")) {
											echo $row_Sap['FechaHoraCreacion']->format('Y-m-d H:i');
										} else {
											echo "&nbsp;";
										} ?>
									</h3>
								</div>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="ibox ">
								<div class="ibox-title">
									<h5><span class="font-normal">Actualizado por</span></h5>
								</div>
								<div class="ibox-content">
									<h3 class="no-margins">
										<?php if (isset($row_Sap['UsuarioActualizacion']) && ($row_Sap['UsuarioActualizacion'] != "")) {
											echo $row_Sap['UsuarioActualizacion'];
										} else {
											echo "&nbsp;";
										} ?>
									</h3>
								</div>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="ibox ">
								<div class="ibox-title">
									<h5><span class="font-normal">Fecha actualización</span></h5>
								</div>
								<div class="ibox-content">
									<h3 class="no-margins">
										<?php if (isset($row_Sap['FechaHoraActualizacion']) && ($row_Sap['FechaHoraActualizacion'] != "")) {
											echo $row_Sap['FechaHoraActualizacion']->format('Y-m-d H:i');
										} else {
											echo "&nbsp;";
										} ?>
									</h3>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>

				<div class="ibox-content">
					<?php include "includes/spinner.php"; ?>
					<div class="row">
						<div class="col-lg-12">
							<form action="lista_materiales.php" method="post" class="form-horizontal"
								enctype="multipart/form-data" id="frmListaMateriales">
								<div class="form-group">
									<label class="col-xs-12">
										<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Información
											de la lista de materiales</h3>
									</label>
								</div>

								<div class="form-group">
									<div class="col-lg-4">
										<label class="control-label"><i onclick="ConsultarArticulo('ItemCode');"
												title="Consultar Articulo" style="cursor: pointer"
												class="btn-xs btn-success fa fa-search"></i> Código
											<?php if (!PermitirFuncion(1006)) { ?> <span class="text-danger">*</span>
											<?php } ?>
										</label>

										<input type="text" name="ItemCode" id="ItemCode" class="form-control" value="<?php if ($edit == 1 || $sw_error == 1) {
											echo $row['ItemCode'];
										} ?>" <?php if (($edit == 1) || PermitirFuncion(1006)) {
											 echo "readonly";
										 } ?> <?php if (!PermitirFuncion(1006)) { ?> required <?php } ?>
											placeholder="<?php echo $CodigoSiguiente; ?>">
									</div>

									<div class="col-lg-4">
										<label class="control-label">Descripción <span
												class="text-danger">*</span></label>

										<input type="text" name="ItemName" id="ItemName" class="form-control" value="<?php if ($edit == 1 || $sw_error == 1 || $dt_LMT == 1) {
											echo $row['ItemName'];
										} elseif (isset($row_Articulo["ItemName"])) {
											echo $row_Articulo["ItemName"];
										} ?>" required>
									</div>

									<div class="col-lg-4">
										<label class="control-label">Cantidad <span class="text-danger">*</span></label>

										<input type="text" name="Cantidad" id="Cantidad" class="form-control" value="<?php if ($edit == 1 || $sw_error == 1 || $dt_LMT == 1) {
											echo number_format($row['Cantidad'], 2);
										} ?>" required>
									</div>

									<div class="col-lg-4">
										<label class="control-label">Tipo lista de material <span
												class="text-danger">*</span></label>

										<select name="TipoListaMat" class="form-control select2" id="TipoListaMat"
											required>
											<?php while ($row_TipoLista = sqlsrv_fetch_array($SQL_TipoLista)) { ?>
												<option value="<?php echo $row_TipoLista['TipoListaMat']; ?>" <?php if (($edit == 1 || $sw_error == 1 || $dt_LMT == 1) && (isset($row['TipoListaMat'])) && (strcmp($row_TipoLista['TipoListaMat'], $row['TipoListaMat']) == 0)) {
													   echo "selected";
												   } ?>><?php echo $row_TipoLista['DeTipoListaMat']; ?></option>
											<?php } ?>
										</select>
									</div>

									<div class="col-lg-4">
										<label class="control-label">Lista de precios <span
												class="text-danger">*</span></label>

										<select name="ListaPrecio" class="form-control select2" id="ListaPrecio"
											required>
											<?php while ($row_ListaPrecios = sqlsrv_fetch_array($SQL_ListaPrecios)) { ?>
												<option value="<?php echo $row_ListaPrecios['IdListaPrecio']; ?>" <?php if (($edit == 1 || $sw_error == 1 || $dt_LMT == 1) && (isset($row['IdListaPrecio'])) && (strcmp($row_ListaPrecios['IdListaPrecio'], $row['IdListaPrecio']) == 0)) {
													   echo "selected";
												   } ?>><?php echo $row_ListaPrecios['DeListaPrecio']; ?></option>
											<?php } ?>
										</select>
									</div>

									<div class="col-lg-4">
										<label class="control-label">Bodega <span class="text-danger">*</span></label>

										<select name="IdBodega" class="form-control select2" id="IdBodega" required>
											<option value="">Seleccione...</option>

											<?php while ($row_Bodega = sqlsrv_fetch_array($SQL_Bodega)) { ?>
												<option value="<?php echo $row_Bodega['WhsCode']; ?>" <?php if ((isset($row['ToWH'])) && (strcmp($row_Bodega['WhsCode'], $row['ToWH']) == 0)) {
													   echo "selected";
												   } ?>>
													<?php echo $row_Bodega['WhsName']; ?>
												</option>
											<?php } ?>
										</select>
									</div>
									<!-- /#IdBodega -->

									<div class="col-lg-4">
										<label class="control-label">Proyecto</label>

										<select name="Proyecto" class="form-control select2" id="Proyecto">
											<option value="">Seleccione...</option>
											<?php while ($row_Proyecto = sqlsrv_fetch_array($SQL_Proyecto)) { ?>
												<option value="<?php echo $row_Proyecto['IdProyecto']; ?>" <?php if (($edit == 1 || $sw_error == 1 || $dt_LMT == 1) && (isset($row['IdProyecto'])) && (strcmp($row_Proyecto['IdProyecto'], $row['IdProyecto']) == 0)) {
													   echo "selected";
												   } ?>><?php echo $row_Proyecto['DeProyecto'] . " (" . $row_Proyecto['IdProyecto'] . ")"; ?>
												</option>
											<?php } ?>
										</select>
									</div>
									<!-- /#Proyecto -->
								</div>
								<!-- /.form-group -->

								<!-- Dimensiones dinámicas, SMM 21/07/2023 -->
								<div class="form-group">
									<?php foreach ($array_Dimensiones as &$dim) { ?>
										<div class="col-lg-4">
											<label class="control-label">
												<?php echo $dim['DescPortalOne']; ?> <span class="text-danger">*</span>
											</label>

											<select name="<?php echo $dim['IdPortalOne'] ?>"
												id="<?php echo $dim['IdPortalOne'] ?>" class="form-control select2"
												required>
												<option value="">Seleccione...</option>

												<?php $SQL_Dim = Seleccionar('uvw_Sap_tbl_DimensionesReparto', '*', 'DimCode=' . $dim['DimCode']); ?>
												<?php while ($row_Dim = sqlsrv_fetch_array($SQL_Dim)) { ?>
													<?php $DimCode = intval($dim['DimCode']); ?>
													<?php $OcrId = ($DimCode == 1) ? "" : $DimCode; ?>

													<option value="<?php echo $row_Dim['OcrCode']; ?>" <?php if (isset($row["OcrCode$OcrId"]) && ($row_Dim['OcrCode'] == $row["OcrCode$OcrId"])) {
														   echo "selected";
													   } elseif (isset($_GET[strval($dim['IdPortalOne'])]) && (strcmp($row_Dim['OcrCode'], base64_decode($_GET[strval($dim['IdPortalOne'])])) == 0)) {
														   echo "selected";
													   } elseif (($edit == 0) && ($row_DatosEmpleados["CentroCosto$DimCode"] == $row_Dim['OcrCode'])) {
														   echo "selected";
													   } ?>>
														<?php echo $row_Dim['OcrName']; ?>
													</option>
												<?php } ?>
											</select>
										</div>
									<?php } ?>
								</div><br>
								<!-- Dimensiones dinámicas, hasta aquí -->

								<div class="form-group">
									<label class="col-xs-12">
										<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-tags"></i> Información
											adicional</h3>
									</label>
								</div>

								<div class="form-group">
									<label class="col-lg-1 control-label">
										<i onClick="ConsultarCliente();" title="Consultar cliente"
											style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i>
										Cliente <span class="text-danger">*</span>
									</label>
									<div class="col-lg-3">
										<input name="Cliente" type="hidden" id="Cliente" value="<?php if (($edit == 1) || ($sw_error == 1) || ($dt_LMT == 1)) {
											echo $row['CDU_CodigoCliente'];
										} elseif (isset($row_Articulo["IdCliente"])) {
											echo $row_Articulo["IdCliente"];
										} ?>">
										<input name="NombreCliente" type="text" class="form-control" id="NombreCliente"
											placeholder="Escribar para buscar..." value="<?php if (($edit == 1) || ($sw_error == 1) || ($dt_LMT == 1)) {
												echo $row['CDU_NombreCliente'];
											} elseif (isset($row_Articulo["DeCliente"])) {
												echo $row_Articulo["DeCliente"];
											} ?>" required>
									</div>
									<label class="col-lg-1 control-label">Sucursal cliente <span
											class="text-danger">*</span></label>
									<div class="col-lg-3">
										<select id="Sucursal" name="Sucursal" class="form-control select2" required>
											<option value="">Seleccione...</option>
											<?php
											if (($edit == 1) || ($sw_error == 1) || ($dt_LMT == 1)) {
												while ($row_Sucursal = sqlsrv_fetch_array($SQL_Sucursal)) { ?>
													<option value="<?php echo $row_Sucursal['NumeroLinea']; ?>" <?php if (strcmp($row_Sucursal['NumeroLinea'], $row['CDU_IdSucursalCliente']) == 0) {
														   echo "selected=\"selected\"";
													   } ?>><?php echo $row_Sucursal['NombreSucursal']; ?></option>
												<?php }
											} ?>
										</select>
									</div>
									<label class="col-lg-1 control-label"><i onClick="ConsultarPlantilla();"
											title="Consultar plantilla" style="cursor: pointer"
											class="btn-xs btn-success fa fa-search"></i> Plantilla de
										actividades</label>
									<div class="col-lg-3">
										<select id="CodigoPlantilla" name="CodigoPlantilla"
											class="form-control select2">
											<option value="">Seleccione...</option>
											<?php
											while ($row_Plantilla = sqlsrv_fetch_array($SQL_Plantilla)) { ?>
												<option value="<?php echo $row_Plantilla['CodigoPlantilla']; ?>" <?php if (isset($row['CDU_CodPlantilla']) && ($row_Plantilla['CodigoPlantilla'] == $row['CDU_CodPlantilla'])) {
													   echo "selected=\"selected\"";
												   } ?>><?php echo $row_Plantilla['CodigoPlantilla'] . " - " . $row_Plantilla['Descripcion']; ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-lg-1 control-label">Servicios</label>
									<div class="col-lg-3">
										<textarea name="CDU_Servicios" rows="5" class="form-control" id="CDU_Servicios"
											type="text"><?php if (($edit == 1) || ($sw_error == 1) || ($dt_LMT == 1)) {
												echo $row['CDU_Servicios'];
											} elseif (isset($row_Articulo["Servicios"])) {
												echo $row_Articulo["Servicios"];
											} ?></textarea>
									</div>

									<label class="col-lg-1 control-label">Áreas</label>
									<div class="col-lg-3">
										<textarea name="CDU_Areas" rows="5" class="form-control" id="CDU_Areas"
											type="text"><?php if (($edit == 1) || ($sw_error == 1) || ($dt_LMT == 1)) {
												echo $row['CDU_Areas'];
											} elseif (isset($row_Articulo["Areas"])) {
												echo $row_Articulo["Areas"];
											} ?></textarea>
									</div>

									<label class="col-lg-1 control-label">Método Aplicación</label>
									<div class="col-lg-3">
										<textarea name="CDU_MetodoAplicacion" rows="5" class="form-control"
											id="CDU_MetodoAplicacion" type="text"><?php if (($edit == 1) || ($sw_error == 1) || ($dt_LMT == 1)) {
												echo $row['CDU_MetodoAplicacion'] ?? "";
											} elseif (isset($row_Articulo["CDU_MetodoAplicacion"])) {
												echo $row_Articulo["CDU_MetodoAplicacion"];
											} ?></textarea>
									</div>
								</div>

								<div class="form-group">
									<label class="col-lg-1 control-label">Línea Negocio</label>
									<div class="col-lg-3">
										<select name="CDU_IdLineaNegocio" class="form-control select2"
											id="CDU_IdLineaNegocio">
											<option value="" disabled selected>Seleccione...</option>

											<?php while ($row_LineaNegocio = sqlsrv_fetch_array($SQL_LineaNegocio)) { ?>
												<option value="<?php echo $row_LineaNegocio['IdLineaNegocio']; ?>" <?php if ((isset($row['CDU_IdLineaNegocio'])) && (strcmp($row_LineaNegocio['IdLineaNegocio'], $row['CDU_IdLineaNegocio']) == 0)) {
													   echo "selected";
												   } ?>>
													<?php echo $row_LineaNegocio['DeLineaNegocio']; ?>
												</option>
											<?php } ?>
										</select>
									</div>

									<label class="col-lg-1 control-label">Frecuencia <span
											class="text-danger">*</span></label>
									<div class="col-lg-3">
										<input name="CDU_Frecuencia" type="number" class="form-control"
											id="CDU_Frecuencia" required value="<?php if (($edit == 1) || ($sw_error == 1) || ($dt_LMT == 1)) {
												echo $row['CDU_Frecuencia'] ?? "";
											} ?>">
									</div>

									<label class="col-lg-1 control-label">Tiempo tarea (Minutos) <span
											class="text-danger">*</span></label>
									<div class="col-lg-3">
										<input name="CDU_TiempoTarea" type="number" class="form-control"
											id="CDU_TiempoTarea" required="required" value="<?php if (($edit == 1) || ($sw_error == 1) || ($dt_LMT == 1)) {
												echo $row['CDU_TiempoTarea'] ?? '';
											} ?>">
									</div>
								</div>

								<div class="form-group">
									<label class="col-lg-1 control-label">Marca</label>
									<div class="col-lg-3">
										<select name="CDU_IdMarca" class="form-control select2" id="CDU_IdMarca">
											<option value="" disabled selected disabled selected>Seleccione...</option>
											<?php while ($row_MarcaVehiculo = sqlsrv_fetch_array($SQL_MarcaVehiculo)) { ?>
												<option value="<?php echo $row_MarcaVehiculo['IdMarcaVehiculo']; ?>" <?php if ((isset($row['CDU_IdMarca'])) && (strcmp($row_MarcaVehiculo['IdMarcaVehiculo'], $row['CDU_IdMarca']) == 0)) {
													   echo "selected";
												   } ?>>
													<?php echo $row_MarcaVehiculo['DeMarcaVehiculo']; ?>
												</option>
											<?php } ?>
										</select>
									</div>

									<label class="col-lg-1 control-label">Línea</label>
									<div class="col-lg-3">
										<select name="CDU_IdLinea" class="form-control select2" id="CDU_IdLinea">
											<option value="" disabled selected>Seleccione...</option>
											<?php while ($row_LineaVehiculo = sqlsrv_fetch_array($SQL_LineaVehiculo)) { ?>
												<option value="<?php echo $row_LineaVehiculo['IdLineaModeloVehiculo']; ?>"
													<?php if ((isset($row['CDU_IdLinea'])) && (strcmp($row_LineaVehiculo['IdLineaModeloVehiculo'], $row['CDU_IdLinea']) == 0)) {
														echo "selected";
													} ?>>
													<?php echo $row_LineaVehiculo['DeLineaModeloVehiculo']; ?>
												</option>
											<?php } ?>
										</select>
									</div>

									<label class="col-lg-1 control-label"><i
											onclick="ConsultarArticulo('CDU_IdArticuloVTAFactura');"
											title="Consultar Articulo" style="cursor: pointer"
											class="btn-xs btn-success fa fa-search"></i> Articulo VTA Factura</label>
									<div class="col-lg-3">
										<select name="CDU_IdArticuloVTAFactura" class="form-control select2"
											id="CDU_IdArticuloVTAFactura">
											<option value="" disabled selected>Seleccione...</option>

											<?php while ($row_ArticuloVTA = sqlsrv_fetch_array($SQL_ArticulosVTA)) { ?>
												<option value="<?php echo $row_ArticuloVTA['ItemCode']; ?>" <?php if ((isset($row['CDU_IdArticuloVTAFactura'])) && (strcmp($row_ArticuloVTA['ItemCode'], $row['CDU_IdArticuloVTAFactura']) == 0)) {
													   echo "selected";
												   } ?>>
													<?php echo $row_ArticuloVTA['ItemCode'] . " - " . $row_ArticuloVTA['ItemName']; ?>
												</option>
											<?php } ?>
										</select>
									</div>
								</div>

								<div class="form-group">
									<label class="col-xs-12">
										<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Contenido de la
											lista</h3>
									</label>
								</div>
								<div class="form-group">
									<label class="col-lg-1 control-label">Buscar articulo</label>
									<div class="col-lg-4">
										<input name="BuscarItem" id="BuscarItem" type="text" class="form-control"
											placeholder="Escriba para buscar..."
											onBlur="javascript:BuscarArticulo(this.value);">
									</div>
								</div>
								<div class="tabs-container">
									<ul class="nav nav-tabs">
										<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i>
												Contenido</a></li>
										<li><span class="TimeAct">
												<div id="TimeAct">&nbsp;</div>
											</span></li>
										<span class="TotalItems"><strong>Total Items:</strong>&nbsp;<input type="text"
												name="TotalItems" id="TotalItems" class="txtLimpio" value="0" size="1"
												readonly></span>
									</ul>
									<div class="tab-content">
										<div id="tab-1" class="tab-pane active">
											<iframe id="DataGrid" name="DataGrid" style="border: 0;" width="100%"
												height="300" src="<?php if ($edit == 0 && $sw_error == 0) {
													echo "detalle_lista_materiales.php?id=" . base64_encode(0) . "&evento=" . base64_encode($IdEvento) . "&type=1";
												} else {
													$id_detalle = (isset($row['ItemCode']) && ($row['ItemCode'] != "")) ? $row['ItemCode'] : $ItemCode;
													$evento_detalle = (isset($row['IdEvento']) && ($row['IdEvento'] != "")) ? $row['IdEvento'] : $IdEvento;

													$id_detalle = base64_encode($id_detalle);
													$evento_detalle = base64_encode($evento_detalle);

													echo "detalle_lista_materiales.php?id=$id_detalle&evento=$evento_detalle&type=2";
												} ?>"></iframe>
										</div>
									</div>
								</div>
								<div class="form-group m-t-xl">
									<div class="col-lg-8">
										<?php if ($edit == 0 && PermitirFuncion(1209)) { ?>
											<button class="btn btn-primary" type="submit" form="frmListaMateriales"
												id="Crear"><i class="fa fa-check"></i>
												Crear lista de materiales
												<?php if (PermitirFuncion(1005)) {
													echo "y articulo";
												} ?>
											</button>
										<?php } else { ?>
											<button class="btn btn-warning" type="submit" form="frmListaMateriales"
												id="Actualizar"><i class="fa fa-refresh"></i> Actualizar lista de
												materiales</button>
										<?php } ?>
										<?php
										if (isset($_GET['return'])) {
											$return = base64_decode($_GET['pag']) . "?" . base64_decode($_GET['return']);
										} elseif (isset($_POST['return'])) {
											$return = base64_decode($_POST['return']);
										} else {
											$return = "consultar_lista_materiales.php";
										}
										$return = QuitarParametrosURL($return, array("a"));
										?>
										<a href="<?php echo $return; ?>" class="btn btn-outline btn-default"><i
												class="fa fa-arrow-circle-o-left"></i> Regresar</a>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<label class="col-lg-7"><strong class="pull-right">Total</strong></label>
											<div class="col-lg-5">
												<input type="text" name="Total" form="frmListaMateriales" id="Total"
													class="form-control" style="text-align: right; font-weight: bold;"
													value="0.00" readonly>
											</div>
										</div>
									</div>

									<?php if ($edit == 1) { ?>
										<div class="col-lg-12">
											<div class="btn-group dropdown pull-right">
												<button data-toggle="dropdown" class="btn btn-success dropdown-toggle"><i
														class="fa fa-mail-forward"></i> Copiar a <i
														class="fa fa-caret-down"></i></button>
												<ul class="dropdown-menu">
													<li><a class="alkin dropdown-item"
															href="lista_materiales.php?dt_LMT=1&LMT=<?php echo base64_encode($ItemCode); ?>&Evento=<?php echo base64_encode($IdEvento); ?>">Lista
															de Materiales (Duplicar)</a></li>
												</ul>
											</div>
										</div>
									<?php } ?>
								</div>
								<input type="hidden" id="P" name="P" value="66" />
								<input type="hidden" id="IdEvento" name="IdEvento"
									value="<?php echo base64_encode($IdEvento); ?>" />
								<input type="hidden" id="tl" name="tl" value="<?php echo $edit; ?>" />
								<input type="hidden" id="swError" name="swError" value="<?php echo $sw_error; ?>" />
								<input type="hidden" id="return" name="return"
									value="<?php echo base64_encode($return); ?>" />
							</form>
						</div>
					</div>
				</div>
			</div>
			<!-- InstanceEndEditable -->
			<?php include_once "includes/footer.php"; ?>

		</div>
	</div>
	<?php include_once "includes/pie.php"; ?>
	<!-- InstanceBeginEditable name="EditRegion4" -->
	<script>
		// SMM, 18/07/2023
		function ConsultarCliente() {
			let Cliente = document.getElementById('Cliente');

			if (Cliente.value != "") {
				self.name = 'opener';

				remote = open('socios_negocios.php?id=' + Base64.encode(Cliente.value) + '&ext=1&tl=1', 'remote', 'location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
				remote.focus();
			}
		}

		// SMM, 18/07/2023
		function ConsultarArticulo(ID) {
			var Articulo = document.getElementById(ID);
			// console.log(Articulo.value);

			if (Articulo.value != "") {
				self.name = 'opener';
				remote = open('articulos.php?id=' + Base64.encode(Articulo.value) + '&ext=1&tl=1', 'remote', 'location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
				remote.focus();
			}
		}

		$(document).ready(function () {
			$("#frmListaMateriales").validate({
				submitHandler: function (form) {
					Swal.fire({
						title: "¿Está seguro que desea guardar los datos?",
						icon: "question",
						showCancelButton: true,
						confirmButtonText: "Si, confirmo",
						cancelButtonText: "No"
					}).then((result) => {
						if (result.isConfirmed) {
							$('.ibox-content').toggleClass('sk-loading', true);
							form.submit();
						}
					});
				}
			});

			$(".alkin").on('click', function () {
				$('.ibox-content').toggleClass('sk-loading');
			});

			$(".select2").select2();

			var options = {
				url: function (phrase) {
					return "ajx_buscar_datos_json.php?type=7&id=" + phrase;
				},
				getValue: "NombreBuscarCliente",
				requestDelay: 400,
				list: {
					match: {
						enabled: true
					},
					onClickEvent: function () {
						var value = $("#NombreCliente").getSelectedItemData().CodigoCliente;
						$("#Cliente").val(value).trigger("change");
					}
				}
			};
			$("#NombreCliente").easyAutocomplete(options);

		});
	</script>
	<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd -->

</html>
<?php sqlsrv_close($conexion); ?>