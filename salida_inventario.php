<?php require_once "includes/conexion.php";
PermitirAcceso(1206);

$debug_mode = false; // Impide el llamado al Web Service, fuerza el $sw_error = 1

$msg_error = ""; //Mensaje del error
$dt_TI = 0; //sw para saber si vienen datos de una Solicitud de salida.
$IdSalidaInv = 0;
$IdPortal = 0; //Id del portal para las solicitudes que fueron creadas en el portal, para eliminar el registro antes de cargar al editar

// Dimensiones, SMM 31/08/2022
$DimSeries = intval(ObtenerVariable("DimensionSeries"));
$SQL_Dimensiones = Seleccionar('uvw_Sap_tbl_Dimensiones', '*', "DimActive='Y'");

// Pruebas, SMM 31/08/2022
// $SQL_Dimensiones = Seleccionar('uvw_Sap_tbl_Dimensiones', '*', 'DimCode IN (1,2)');

$array_Dimensiones = [];
while ($row_Dimension = sqlsrv_fetch_array($SQL_Dimensiones)) {
    array_push($array_Dimensiones, $row_Dimension);
}

$encode_Dimensiones = json_encode($array_Dimensiones);
$cadena_Dimensiones = "JSON.parse('$encode_Dimensiones'.replace(/\\n|\\r/g, ''))";
// echo "<script> console.log('cadena_Dimensiones'); </script>";
// echo "<script> console.log($cadena_Dimensiones); </script>";
// Hasta aquí, SMM 31/08/2022

if (isset($_GET['id']) && ($_GET['id'] != "")) { //ID de la Salida de inventario (DocEntry)
    $IdSalidaInv = base64_decode($_GET['id']);
}

if (isset($_GET['id_portal']) && ($_GET['id_portal'] != "")) { //Id del portal de venta (ID interno)
    $IdPortal = base64_decode($_GET['id_portal']);
}

if (isset($_POST['IdSalidaInv']) && ($_POST['IdSalidaInv'] != "")) { //Tambien el Id interno, pero lo envío cuando mando el formulario
    $IdSalidaInv = base64_decode($_POST['IdSalidaInv']);
    $IdEvento = base64_decode($_POST['IdEvento']);
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

if (isset($_POST['P']) && ($_POST['P'] != "")) { //Grabar Salida de inventario
    //*** Carpeta temporal ***
    $i = 0; //Archivos
    $RutaAttachSAP = ObtenerDirAttach();
    $dir = CrearObtenerDirTemp();
    $dir_firma = CrearObtenerDirTempFirma();
    $dir_new = CrearObtenerDirAnx("salidainventario");

    if ((isset($_POST['SigRecibe'])) && ($_POST['SigRecibe'] != "")) {
        $NombreFileFirma = base64_decode($_POST['SigRecibe']);
        $Nombre_Archivo = "Sig_" . $NombreFileFirma;
        if (!copy($dir_firma . $NombreFileFirma, $dir . $Nombre_Archivo)) {
            $sw_error = 1;
            $msg_error = "No se pudo mover la firma";
        }
    }

    $route = opendir($dir);
    $DocFiles = array();
    while ($archivo = readdir($route)) { //obtenemos un archivo y luego otro sucesivamente
        if (($archivo == ".") || ($archivo == "..")) {
            continue;
        }

        if (!is_dir($archivo)) { //verificamos si es o no un directorio
            $DocFiles[$i] = $archivo;
            $i++;
        }
    }
    closedir($route);
    $CantFiles = count($DocFiles);

    try {
        if ($_POST['tl'] == 1) { //Actualizar
            $IdSalidaInv = base64_decode($_POST['IdSalidaInv']);
            $IdEvento = base64_decode($_POST['IdEvento']);
            $Type = 2;
            if (!PermitirFuncion(403)) { //Permiso para autorizar Solicitud de salida
                $_POST['Autorizacion'] = 'P'; //Si no tengo el permiso, la Solicitud queda pendiente
            }
        } else { //Crear
            $IdSalidaInv = "NULL";
            $IdEvento = "0";
            $Type = 1;
        }

        if (isset($_POST['AnioEntrega']) && ($_POST['AnioEntrega'] != "")) {
            $AnioEntrega = "'" . $_POST['AnioEntrega'] . "'";
        } else {
            $AnioEntrega = "NULL";
        }

        if (isset($_POST['EntregaDescont']) && ($_POST['EntregaDescont'] != "")) {
            $EntregaDescont = "'" . $_POST['EntregaDescont'] . "'";
        } else {
            $EntregaDescont = "NULL";
        }

        if (isset($_POST['ValorCuotaDesc']) && ($_POST['ValorCuotaDesc'] != "")) {
            $ValorCuotaDesc = "'" . $_POST['ValorCuotaDesc'] . "'";
        } else {
            $ValorCuotaDesc = "NULL";
        }

        $ParametrosCabSalidaInv = array(
            $IdSalidaInv,
            $IdEvento,
            "NULL",
            "NULL",
            "'" . $_POST['Serie'] . "'",
            "'" . $_POST['EstadoDoc'] . "'",
            "'" . FormatoFecha($_POST['DocDate']) . "'",
            "'" . FormatoFecha($_POST['DocDueDate']) . "'",
            "'" . FormatoFecha($_POST['TaxDate']) . "'",
            "'" . $_POST['CardCode'] . "'",
            "'" . $_POST['ContactoCliente'] . "'",
            "'" . $_POST['OrdenServicioCliente'] . "'",
            "'" . $_POST['Referencia'] . "'",
            "'" . $_SESSION['CodigoEmpVentas'] . "'",
            "'" . LSiqmlObs($_POST['Comentarios']) . "'",
            "'" . str_replace(',', '', $_POST['SubTotal']) . "'",
            "'" . str_replace(',', '', $_POST['Descuentos']) . "'",
            "NULL",
            "'" . str_replace(',', '', $_POST['Impuestos']) . "'",
            "'" . str_replace(',', '', $_POST['TotalSalida']) . "'",
            "'" . $_POST['SucursalFacturacion'] . "'",
            "'" . $_POST['DireccionFacturacion'] . "'",
            "'" . $_POST['SucursalDestino'] . "'",
            "'" . $_POST['DireccionDestino'] . "'",
            "'" . $_POST['CondicionPago'] . "'",

            // Se eliminaron las dimensiones, SMM 23/11/2022

            "NULL",
            "'N'",
            "'" . $_POST['TipoEntrega'] . "'",
            $AnioEntrega,
            $EntregaDescont,
            $ValorCuotaDesc,
            "'" . $_POST['Almacen'] . "'",
            "'" . $_POST['Empleado'] . "'",
            "'" . $_SESSION['CodUser'] . "'",
            "'" . $_SESSION['CodUser'] . "'",
            "$Type",

            // SMM, 23/12/2022
            "'" . $_POST['ConceptoSalida'] . "'",
        );

        // Enviar el valor de la dimensiones dinámicamente al SP.
        foreach ($array_Dimensiones as &$dim) {
            $Dim_PostValue = $_POST[strval($dim['IdPortalOne'])];

            // El nombre de los parámetros es diferente en cada documento.
            array_push($ParametrosCabSalidaInv, "'$Dim_PostValue'");
        } // SMM, 23/11/2022

        $SQL_CabeceraSalidaInv = EjecutarSP('sp_tbl_SalidaInventario', $ParametrosCabSalidaInv, $_POST['P']);
        if ($SQL_CabeceraSalidaInv) {
            if ($Type == 1) {
                $row_CabeceraSalidaInv = sqlsrv_fetch_array($SQL_CabeceraSalidaInv);
                $IdSalidaInv = $row_CabeceraSalidaInv[0];
                $IdEvento = $row_CabeceraSalidaInv[1];
            } else {
                $IdSalidaInv = base64_decode($_POST['IdSalidaInv']); //Lo coloco otra vez solo para saber que tiene ese valor
                $IdEvento = base64_decode($_POST['IdEvento']);
            }

            try {
                //Mover los anexos a la carpeta de archivos de SAP
                $j = 0;
                while ($j < $CantFiles) {
                    $Archivo = FormatoNombreAnexo($DocFiles[$j]);
                    $NuevoNombre = $Archivo[0];
                    $OnlyName = $Archivo[1];
                    $Ext = $Archivo[2];

                    if (file_exists($dir_new)) {
                        copy($dir . $DocFiles[$j], $dir_new . $NuevoNombre);
                        //move_uploaded_file($_FILES['FileArchivo']['tmp_name'],$dir_new.$NuevoNombre);
                        copy($dir_new . $NuevoNombre, $RutaAttachSAP[0] . $NuevoNombre);

                        //Registrar archivo en la BD
                        $ParamInsAnex = array(
                            "'60'",
                            "'" . $IdSalidaInv . "'",
                            "'" . $OnlyName . "'",
                            "'" . $Ext . "'",
                            "1",
                            "'" . $_SESSION['CodUser'] . "'",
                            "1",
                        );
                        $SQL_InsAnex = EjecutarSP('sp_tbl_DocumentosSAP_Anexos', $ParamInsAnex, $_POST['P']);
                        if (!$SQL_InsAnex) {
                            $sw_error = 1;
                            $msg_error = "Error al insertar los anexos.";
                        }
                    }
                    $j++;
                }
            } catch (Exception $e) {
                echo 'Excepcion capturada: ', $e->getMessage(), "\n";
            }

            //Enviar datos al WebServices
            try {
                if (!$debug_mode) {
                    $Parametros = array(
                        'id_documento' => intval($IdSalidaInv),
                        'id_evento' => intval($IdEvento),
                    );
                    $Metodo = "SalidasInventarios";
                    $Resultado = EnviarWebServiceSAP($Metodo, $Parametros, true, true);
                }

                if ($debug_mode || ($Resultado->Success == 0)) {
                    $sw_error = 1;
                    $msg_error = ($debug_mode) ? "Modo de pruebas" : $Resultado->Mensaje;
                } else {
                    sqlsrv_close($conexion);
                    if ($_POST['tl'] == 0) { //Creando salida
                        header('Location:' . base64_decode($_POST['return']) . '&a=' . base64_encode("OK_SalInvAdd"));
                    } else { //Actualizando salida
                        header('Location:' . base64_decode($_POST['return']) . '&a=' . base64_encode("OK_SalInvUpd"));
                    }
                }
            } catch (Exception $e) {
                echo 'Excepcion capturada: ', $e->getMessage(), "\n";
            }

        } else {
            $sw_error = 1;
            $msg_error = "Ha ocurrido un error al crear la Salida de inventario";
        }
    } catch (Exception $e) {
        echo 'Excepcion capturada: ', $e->getMessage(), "\n";
    }

}

if (isset($_GET['dt_TI']) && ($_GET['dt_TI']) == 1) { //Verificar que viene de un traslado de inventario
    $dt_TI = 1;

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreCliente');
    $row_Cliente = sqlsrv_fetch_array($SQL_Cliente);

    // Sucursales. SMM, 01/12/2022
    $SQL_SucursalDestino = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "' AND NombreSucursal='" . base64_decode($_GET['Sucursal']) . "'");

    if (isset($_GET['SucursalFact'])) {
        $SQL_SucursalFacturacion = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "' AND NombreSucursal='" . base64_decode($_GET['SucursalFact']) . "' AND TipoDireccion='B'", 'NombreSucursal');
    }

    //Contacto cliente
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreContacto');

    $ParametrosCopiarTrasladoInvToSalidaInv = array(
        "'" . base64_decode($_GET['TI']) . "'",
        "'" . base64_decode($_GET['Evento']) . "'",
        "'" . base64_decode($_GET['Almacen']) . "'",
        "'" . base64_decode($_GET['Cardcode']) . "'",
        "'" . $_SESSION['CodUser'] . "'",
        "'" . base64_decode($_GET['DocEntry']) . "'", // SMM, 24/01/2023
    );
    $SQL_CopiarTrasladoInvToSalidaInv = EjecutarSP('sp_tbl_TrasladoInvDet_To_SalidaInvDet', $ParametrosCopiarTrasladoInvToSalidaInv);
    if (!$SQL_CopiarTrasladoInvToSalidaInv) {
        echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Ha ocurrido un error!',
				text: 'No se pudo copiar el Traslado en la Salida de inventario.',
				icon: 'error'
			});
		});
		</script>";
    }

}

if ($edit == 1 && $sw_error == 0) {

    $ParametrosLimpiar = array(
        "'" . $IdSalidaInv . "'",
        "'" . $IdPortal . "'",
        "'" . $_SESSION['CodUser'] . "'",
    );
    $LimpiarSolSalida = EjecutarSP('sp_EliminarDatosSalidaInventario', $ParametrosLimpiar);

    $SQL_IdEvento = sqlsrv_fetch_array($LimpiarSolSalida);
    $IdEvento = $SQL_IdEvento[0];

    //Salida inventario
    $Cons = "Select * From uvw_tbl_SalidaInventario Where DocEntry='" . $IdSalidaInv . "' AND IdEvento='" . $IdEvento . "'";
    $SQL = sqlsrv_query($conexion, $Cons);
    $row = sqlsrv_fetch_array($SQL);

    // SMM, 05/09/2022
    // echo $Cons;

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreCliente');

    //Sucursales
    $SQL_SucursalFacturacion = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' and TipoDireccion='B'", 'NombreSucursal');
    $SQL_SucursalDestino = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' and TipoDireccion='S'", 'NombreSucursal');

    //Contacto cliente
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreContacto');

    // Orden de servicio, SMM, 31/08/2022
    $SQL_OrdenServicioCliente = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_LlamadaServicio='" . $row['ID_LlamadaServicio'] . "'");
    $row_OrdenServicioCliente = sqlsrv_fetch_array($SQL_OrdenServicioCliente);

    //Sucursal
    $SQL_Sucursal = SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes', 'IdSucursal, DeSucursal', "IdSeries='" . $row['IdSeries'] . "'", "IdSucursal, DeSucursal");

    //Almacenes
    $SQL_Almacen = SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes', 'WhsCode, WhsName', "IdSeries='" . $row['IdSeries'] . "'", "WhsCode, WhsName", 'WhsName');

    //Anexos
    $SQL_Anexo = Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos', '*', "AbsEntry='" . $row['IdAnexo'] . "'");

}

if ($sw_error == 1) {

    //Salida de inventario
    $Cons = "Select * From uvw_tbl_SalidaInventario Where ID_SalidaInv='" . $IdSalidaInv . "' AND IdEvento='" . $IdEvento . "'";
    $SQL = sqlsrv_query($conexion, $Cons);
    $row = sqlsrv_fetch_array($SQL);

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreCliente');

    //Sucursales
    $SQL_SucursalFacturacion = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' and TipoDireccion='B'", 'NombreSucursal');

    $SQL_SucursalDestino = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' and TipoDireccion='S'", 'NombreSucursal');

    //Contacto cliente
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreContacto');

    //Orden de servicio, SMM, 31/08/2022
    $SQL_OrdenServicioCliente = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_LlamadaServicio='" . $row['ID_LlamadaServicio'] . "'");
    $row_OrdenServicioCliente = sqlsrv_fetch_array($SQL_OrdenServicioCliente);

    //Sucursal
    $SQL_Sucursal = SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes', 'IdSucursal, DeSucursal', "IdSeries='" . $row['IdSeries'] . "'", "IdSucursal, DeSucursal");

    //Almacenes
    $SQL_Almacen = SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes', 'WhsCode, WhsName', "IdSeries='" . $row['IdSeries'] . "'", "WhsCode, WhsName", 'WhsName');

    //Anexos
    $SQL_Anexo = Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos', '*', "AbsEntry='" . $row['IdAnexo'] . "'");

}

// Se eliminaron las dimensiones en esta parte, SMM 31/08/2022

//Condiciones de pago
$SQL_CondicionPago = Seleccionar('uvw_Sap_tbl_CondicionPago', '*', '', 'IdCondicionPago');

//Datos de dimensiones del usuario actual
$SQL_DatosEmpleados = Seleccionar('uvw_tbl_Usuarios', 'CentroCosto1,CentroCosto2', "ID_Usuario='" . $_SESSION['CodUser'] . "'");
$row_DatosEmpleados = sqlsrv_fetch_array($SQL_DatosEmpleados);

//Empleados
$SQL_Empleado = Seleccionar('uvw_Sap_tbl_EmpleadosSN', '*', '', 'NombreEmpleado');

//Tipo entrega
$SQL_TipoEntrega = Seleccionar('uvw_Sap_tbl_TipoEntrega', '*', '', 'DeTipoEntrega');

//Año entrega
$SQL_AnioEntrega = Seleccionar('uvw_Sap_tbl_TipoEntregaAnio', '*', '', 'DeAnioEntrega');

//Estado documento
$SQL_EstadoDoc = Seleccionar('uvw_tbl_EstadoDocSAP', '*');

//Estado autorizacion
$SQL_EstadoAuth = Seleccionar('uvw_Sap_tbl_EstadosAuth', '*');

//Series de documento
$ParamSerie = array(
    "'" . $_SESSION['CodUser'] . "'",
    "'60'",
);
$SQL_Series = EjecutarSP('sp_ConsultarSeriesDocumentos', $ParamSerie);

// Filtrar conceptos de salida. SMM, 20/01/2023
$Where_Conceptos = "ID_Usuario='" . $_SESSION['CodUser'] . "'";
$SQL_Conceptos = Seleccionar('uvw_tbl_UsuariosConceptos', '*', $Where_Conceptos);

$Conceptos = array();
while ($Concepto = sqlsrv_fetch_array($SQL_Conceptos)) {
    $Conceptos[] = ("'" . $Concepto['IdConcepto'] . "'");
}

$Filtro_Conceptos = "Estado = 'Y'";
if (count($Conceptos) > 0 && ($edit == 0)) {
    $Filtro_Conceptos .= " AND id_concepto_salida IN (";
    $Filtro_Conceptos .= implode(",", $Conceptos);
    $Filtro_Conceptos .= ")";
}

$SQL_ConceptoSalida = Seleccionar('tbl_SalidaInventario_Conceptos', '*', $Filtro_Conceptos, 'id_concepto_salida');
// Hasta aquí, 16/02/2023

// Filtrar proyectos asignados. SMM, 16/02/2023
$Where_Proyectos = "ID_Usuario='" . $_SESSION['CodUser'] . "'";
$SQL_Proyectos = Seleccionar('uvw_tbl_UsuariosProyectos', '*', $Where_Proyectos);

$Proyectos = array();
while ($Concepto = sqlsrv_fetch_array($SQL_Proyectos)) {
    $Proyectos[] = ("'" . $Concepto['IdProyecto'] . "'");
}

$Filtro_Proyectos = "";
if (count($Proyectos) > 0 && ($edit == 0)) {
    $Filtro_Proyectos .= "IdProyecto IN (";
    $Filtro_Proyectos .= implode(",", $Proyectos);
    $Filtro_Proyectos .= ")";
}

$SQL_Proyecto = Seleccionar('uvw_Sap_tbl_Proyectos', '*', $Filtro_Proyectos, 'DeProyecto');
// Hasta aquí, 16/02/2023

// SMM, 20/01/2023
if ($edit == 0) {
    $ClienteDefault = "";
    $NombreClienteDefault = "";
    $SucursalDestinoDefault = "";
    $SucursalFacturacionDefault = "";

    if (ObtenerVariable("NITClienteDefault") != "") {
        $ClienteDefault = ObtenerVariable("NITClienteDefault");

        $SQL_ClienteDefault = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='$ClienteDefault'");
        $row_ClienteDefault = sqlsrv_fetch_array($SQL_ClienteDefault);

        $NombreClienteDefault = $row_ClienteDefault["NombreBuscarCliente"]; // NombreCliente
        $SucursalDestinoDefault = "DITAR S.A";
        $SucursalFacturacionDefault = "DITAR S.A.";
    }
}

// Stiven Muñoz Murillo, 31/08/2022
$row_encode = isset($row) ? json_encode($row) : "";
$cadena = isset($row) ? "JSON.parse('$row_encode'.replace(/\\n|\\r/g, ''))" : "'Not Found'";
// echo "<script> console.log('consulta principal'); </script>";
// echo "<script> console.log($cadena); </script>";
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Salida de traslado | <?php echo NOMBRE_PORTAL; ?></title>
<?php
if (isset($_GET['a']) && $_GET['a'] == base64_encode("OK_SalInvAdd")) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'La Salida de inventario ha sido creada exitosamente.',
				icon: 'success'
			});
		});
		</script>";
}
if (isset($sw_error) && ($sw_error == 1)) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Ha ocurrido un error!',
                text: '" . preg_replace('/\s+/', ' ', LSiqmlObs($msg_error)) . "',
                icon: 'error'
            });
		});
		</script>";
}
?>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.panel-body{
		padding: 0px !important;
	}
	.tabs-container .panel-body{
		padding: 0px !important;
	}
	.nav-tabs > li > a{
		padding: 14px 20px 14px 25px !important;
	}
</style>
<script>
function BuscarArticulo(dato){
	var almacen= document.getElementById("Almacen").value;
	var cardcode= document.getElementById("CardCode").value;

	// SMM, 31/08/2022
	var dim1= ((document.getElementById("Dim1") || {}).value) || "";
	var dim2= ((document.getElementById("Dim2") || {}).value) || "";
	var dim3= ((document.getElementById("Dim3") || {}).value) || "";
	var dim4= ((document.getElementById("Dim4") || {}).value) || "";
	var dim5= ((document.getElementById("Dim5") || {}).value) || "";
	// Hasta aquí, 31/08/2022

	var posicion_x;
	var posicion_y;
	posicion_x=(screen.width/2)-(1200/2);
	posicion_y=(screen.height/2)-(500/2);

	// SMM, 05/12/2022
	let proyecto = document.getElementById("PrjCode").value;

	// SMM, 23/01/2023
	let conceptoSalida = document.getElementById("ConceptoSalida").value;

	if(dato!=""){
		if((cardcode!="")&&(almacen!="")){
			remote=open(`buscar_articulo.php?concepto=${conceptoSalida}&dim1=${dim1}&dim2=${dim2}&dim3=${dim3}&prjcode=${proyecto}&dato=`+dato+'&cardcode='+cardcode+'&whscode='+almacen+'&doctype=<?php if ($edit == 0) {echo "9";} else {echo "10";}?>&idsalidainv=<?php if ($edit == 1) {echo base64_encode($row['ID_SalidaInv']);} else {echo "0";}?>&evento=<?php if ($edit == 1) {echo base64_encode($row['IdEvento']);} else {echo "0";}?>&tipodoc=3','remote',"width=1200,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
			remote.focus();
		}else{
			Swal.fire({
				title: "¡Error!",
				text: "Debe seleccionar un cliente y un almacén",
				icon: "error",
				confirmButtonText: "OK"
			});
		}
	}
}
function ConsultarDatosCliente(){
	var Cliente=document.getElementById('CardCode');
	if(Cliente.value!=""){
		self.name='opener';
		remote=open('socios_negocios.php?id='+Base64.encode(Cliente.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}
</script>

<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#CardCode").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);

			var frame=document.getElementById('DataGrid');
			var carcode=document.getElementById('CardCode').value;
			var almacen=document.getElementById('Almacen').value;

			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=2&id="+carcode,
				success: function(response){
					$('#ContactoCliente').html(response).fadeIn();
				},
				error: function(error) {
					console.log(`ajx_cbo_select.php?type=2&id=${carcode}`);
					console.log("Line 500", error.responseText);

					$('.ibox-content').toggleClass('sk-loading', false);
				}
			});

			<?php if ($edit == 0 && $sw_error == 0 && ($dt_TI == 0)) { // Limpiar carrito detalle. ?>
			$.ajax({
				type: "POST",
				url: "includes/procedimientos.php?type=7&objtype=60&cardcode="+carcode
			});
			<?php }?>

			<?php if ($dt_TI == 0) { //Para que no recargue las listas cuando vienen de una solicitud de salida.?>
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=3&tdir=S&id="+carcode,
				success: function(response){
					$('#SucursalDestino').html(response).fadeIn();

					<?php if (($edit == 0) && ($ClienteDefault != "")) {?>
						$("#SucursalDestino").val("<?php echo $SucursalDestinoDefault; ?>");
					<?php }?>

					$('#SucursalDestino').trigger('change');
				},
				error: function(error) {
					console.log("Line 515", error.responseText);

					$('.ibox-content').toggleClass('sk-loading', false);
				}
			});

			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=3&tdir=B&id="+carcode,
				success: function(response){
					$('#SucursalFacturacion').html(response).fadeIn();

					<?php if (($edit == 0) && ($ClienteDefault != "")) {?>
						$("#SucursalFacturacion").val("<?php echo $SucursalFacturacionDefault; ?>");
					<?php }?>

					$('#SucursalFacturacion').trigger('change');
				},
				error: function(error) {
					console.log("Line 525", error.responseText);

					$('.ibox-content').toggleClass('sk-loading', false);
				}
			});
			<?php }?>

			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=7&id="+carcode,
				success: function(response){
					$('#CondicionPago').html(response).fadeIn();
				},
				error: function(error) {
					console.log("Line 543", error.responseText);

					$('.ibox-content').toggleClass('sk-loading', false);
				}
			});

			// SMM, 23/01/2023
			<?php if (isset($_GET['a'])) {?>
				frame.src="detalle_salida_inventario.php";
			<?php } else {?>
				// Antiguo fragmento de código
				<?php if ($edit == 0) {?>
					if(carcode!="") {
						frame.src="detalle_salida_inventario.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+carcode+'&dt_TI=<?php echo $dt_TI; ?>';
					}else{
						frame.src="detalle_salida_inventario.php";
					}
				<?php } else {?>
					if(carcode!="") {
						frame.src="detalle_salida_inventario.php?id=<?php echo base64_encode($row['ID_SalidaInv']); ?>&evento=<?php echo base64_encode($row['IdEvento']); ?>&type=2&docentry=<?php echo base64_encode($row['DocEntry']); ?>&dt_TI=<?php echo $dt_TI; ?>";
					}else{
						frame.src="detalle_salida_inventario.php";
					}
				<?php }?>
				// Hasta aquí
			<?php }?>

			$('.ibox-content').toggleClass('sk-loading',false);
		});

		$("#SucursalDestino").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);

			var Cliente=document.getElementById('CardCode').value;
			var Sucursal=document.getElementById('SucursalDestino').value;

			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:3,CardCode:Cliente,Sucursal:Sucursal},
				dataType:'json',
				success: function(data){
					document.getElementById('DireccionDestino').value=data.Direccion;

					$('.ibox-content').toggleClass('sk-loading',false);
				},
				error: function(error) {
					// console.log("Line 637", error.responseText);
					console.log("El cliente no tiene una dirección destino");

					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});

		$("#SucursalFacturacion").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);

			var Cliente=document.getElementById('CardCode').value;
			var Sucursal=document.getElementById('SucursalFacturacion').value;

			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:3,CardCode:Cliente,Sucursal:Sucursal},
				dataType:'json',
				success: function(data){
					document.getElementById('DireccionFacturacion').value=data.Direccion;

					$('.ibox-content').toggleClass('sk-loading',false);
				},
				error: function(error) {
					// console.log("Line 658", error.responseText);
					console.log("El cliente no tiene una dirección de facturación");

					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});

// Dimensión de serie dinámica.
<?php foreach ($array_Dimensiones as &$dim) {
    $DimCode = intval($dim['DimCode']);
    $OcrId = ($DimCode == 1) ? "" : $DimCode;

    if ($DimCode == $DimSeries) {
        $decode_SDim = base64_decode($_GET[strval($dim['IdPortalOne'])] ?? "");
        $rowValue_SDim = ($row["OcrCode$OcrId"] ?? "");

        $console_Msg = $dim['DimDesc'] . " (GET): $decode_SDim";
        $console_Msg .= "& " . $dim['DimDesc'] . " (ROW): $rowValue_SDim";

        $SDimPO = $dim['IdPortalOne'];
    }
}?> // SMM, 31/08/2022

		$("#Serie").change(function() {
			$('.ibox-content').toggleClass('sk-loading',true);

			console.log("SDim Message,\n<?php echo $console_Msg; ?>"); // SMM, 31/08/2022

			var Serie=document.getElementById('Serie').value;
			var SDim = document.getElementById('<?php echo $SDimPO; ?>').value; // SMM, 31/08/2022

			$.ajax({
				type: "POST",
				url: `ajx_cbo_select.php?type=19&id=${Serie}&SDim=${SDim}`, // SMM, 31/08/2022
				success: function(response){
					$('#<?php echo $SDimPO; ?>').html(response).fadeIn(); // SMM, 31/08/2022
					$('#<?php echo $SDimPO; ?>').trigger('change'); // SMM, 31/08/2022

					$('.ibox-content').toggleClass('sk-loading',false);
				},
				error: function(error) {
					console.log("Line 903", error.responseText);

					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});

// Actualización de las dimensiones dinámicamente, SMM 05/12/2022
<?php foreach ($array_Dimensiones as &$dim) {?>

<?php $Name_IdDoc = "ID_SalidaInv";?>
<?php $DimCode = intval($dim['DimCode']);?>
<?php $OcrId = ($DimCode == 1) ? "" : $DimCode;?>

$("#<?php echo $dim['IdPortalOne']; ?>").change(function() {

	var docType = 5;
	var detalleDoc = "detalle_salida_inventario.php";

	var frame = document.getElementById('DataGrid');
	var DimIdPO = document.getElementById('<?php echo $dim['IdPortalOne']; ?>').value;

	<?php if ($DimCode == $DimSeries) {?>
		$('.ibox-content').toggleClass('sk-loading',true);

		let tDoc = 60;
		let Serie = document.getElementById('Serie').value;

		var url20 = `ajx_cbo_select.php?type=20&id=${DimIdPO}&serie=${Serie}&tdoc=${tDoc}&WhsCode=<?php echo isset($_GET['Almacen']) ? base64_decode($_GET['Almacen']) : ($row['WhsCode'] ?? ""); ?>&ToWhsCode=<?php echo isset($_GET['AlmacenDestino']) ? base64_decode($_GET['AlmacenDestino']) : ($row['ToWhsCode'] ?? ""); ?>`;

		$.ajax({
				type: "POST",
				url: `${url20}<?php if ($dt_TI == 1) {echo "&twhs=2";}?>`,
				success: function(response){
					console.log("Cargando almacenes destino...");

					$('#Almacen').html(response).fadeIn();
					//$('#AlmacenDestino').trigger('change');

					$('.ibox-content').toggleClass('sk-loading',false);
				},
				error: function(error) {
					// Mensaje de error
					console.log("Line 923", error.responseText);

					$('.ibox-content').toggleClass('sk-loading', false);
				}
			});
	<?php }?>

	var CardCode = document.getElementById('CardCode').value;
	var TotalItems = document.getElementById('TotalItems').value;

	if(DimIdPO!="" && CardCode!="" && TotalItems!="0") {
		Swal.fire({
			title: "¿Desea actualizar las lineas de la <?php echo $dim['DescPortalOne']; ?>?",
			icon: "question",
			showCancelButton: true,
			confirmButtonText: "Si, confirmo",
			cancelButtonText: "No"
		}).then((result) => {
			if (result.isConfirmed) {
				$('.ibox-content').toggleClass('sk-loading',true);

				<?php if ($edit == 0) {?>
					$.ajax({
						type: "GET",
						url: `registro.php?P=36&type=1&doctype=${docType}&name=OcrCode<?php echo $OcrId; ?>&value=${Base64.encode(DimIdPO)}&cardcode=${CardCode}&actodos=1&whscode=0&line=0`,
						success: function(response){
							frame.src=`${detalleDoc}?type=1&id=0&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode=${CardCode}`;

							$('.ibox-content').toggleClass('sk-loading',false);
						}
					});
				<?php } else {?>
					$.ajax({
						type: "GET",
						url: `registro.php?P=36&type=2&doctype=${docType}&name=OcrCode<?php echo $OcrId; ?>&value=${Base64.encode(DimIdPO)}&id=<?php echo $row[strval($Name_IdDoc)]; ?>&evento=<?php echo $IdEvento; ?>&actodos=1&line=0`,
						success: function(response){
							frame.src=`${detalleDoc}?type=2&id=<?php echo base64_encode($row[strval($Name_IdDoc)]); ?>&evento=<?php echo base64_encode($IdEvento); ?>`;

							$('.ibox-content').toggleClass('sk-loading',false);
						}
					});
				<?php }?>
			}
		});
	} else  {
		if(false) {
			console.log("No se cumple la siguiente condición en la <?php echo $dim['DimName']; ?>");

			console.log(`DimIdPO == ${DimIdPO}`);
			console.log(`CardCode == ${CardCode}`);
			console.log(`TotalItems == ${TotalItems}`);

			$('.ibox-content').toggleClass('sk-loading',false);
		}
	}
});

<?php }?>
// Actualización dinámica, llega hasta aquí.

		$("#TipoEntrega").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var TipoEnt=document.getElementById('TipoEntrega').value;
			var EntDesc=document.getElementById('EntregaDescont');
			var VlrCuota=document.getElementById('ValorCuotaDesc');
			if(TipoEnt==2||TipoEnt==3||TipoEnt==4){//Periodicas
				document.getElementById('dv_AnioEnt').style.display='block';
				document.getElementById('dv_Descont').style.display='none';
				document.getElementById('dv_VlrCuota').style.display='none';
				VlrCuota.value="";
				$("#ValorCuotaDesc").removeAttr("required");
			}else if(TipoEnt==6){//Descontable
				document.getElementById('dv_AnioEnt').style.display='none';
				document.getElementById('dv_Descont').style.display='block';
				$('#EntregaDescont').trigger('change');
			}else{
				document.getElementById('dv_AnioEnt').style.display='none';
				document.getElementById('dv_Descont').style.display='none';
				document.getElementById('dv_VlrCuota').style.display='none';
				VlrCuota.value="";
				$("#ValorCuotaDesc").removeAttr("required");
			}
			$('.ibox-content').toggleClass('sk-loading',false);
		});

		$("#EntregaDescont").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var EntDesc=document.getElementById('EntregaDescont');
			var VlrCuota=document.getElementById('ValorCuotaDesc');
			if(EntDesc.value=="SI"){
				document.getElementById('dv_VlrCuota').style.display='block';
				$("#ValorCuotaDesc").attr("required","required");
			}else{
				$("#ValorCuotaDesc").removeAttr("required");
				VlrCuota.value="";
				document.getElementById('dv_VlrCuota').style.display='none';
			}
			$('.ibox-content').toggleClass('sk-loading',false);
		});

		// Actualización del proyecto en las líneas, SMM 05/12/2022
		$("#PrjCode").change(function() {
			var frame=document.getElementById('DataGrid');

			if(document.getElementById('PrjCode').value!=""&&document.getElementById('CardCode').value!=""&&document.getElementById('TotalItems').value!="0"){
				Swal.fire({
					title: "¿Desea actualizar las lineas?",
					icon: "question",
					showCancelButton: true,
					confirmButtonText: "Si, confirmo",
					cancelButtonText: "No"
				}).then((result) => {
					if (result.isConfirmed) {
						$('.ibox-content').toggleClass('sk-loading',true);
							<?php if ($edit == 0) {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=5&type=1&name=PrjCode&value="+Base64.encode(document.getElementById('PrjCode').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_salida_inventario.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=5&type=2&name=PrjCode&value="+Base64.encode(document.getElementById('PrjCode').value)+"&line=0&id=<?php echo $row['ID_SolSalida']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_salida_inventario.php?id=<?php echo base64_encode($row['ID_SolSalida']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }?>
					}
				});
			}
		});
		// Actualizar proyecto, llega hasta aquí.

		// Actualización del concepto de salida en las líneas, SMM 21/01/2023
		$("#ConceptoSalida").change(function() {
			var frame=document.getElementById('DataGrid');

			if(document.getElementById('ConceptoSalida').value!=""&&document.getElementById('CardCode').value!=""&&document.getElementById('TotalItems').value!="0"){
				Swal.fire({
					title: "¿Desea actualizar las lineas?",
					icon: "question",
					showCancelButton: true,
					confirmButtonText: "Si, confirmo",
					cancelButtonText: "No"
				}).then((result) => {
					if (result.isConfirmed) {
						$('.ibox-content').toggleClass('sk-loading',true);
							<?php if ($edit == 0) {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=5&type=1&name=ConceptoSalida&value="+Base64.encode(document.getElementById('ConceptoSalida').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_salida_inventario.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=5&type=2&name=ConceptoSalida&value="+Base64.encode(document.getElementById('ConceptoSalida').value)+"&line=0&id=<?php echo $row['ID_SolSalida']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_salida_inventario.php?id=<?php echo base64_encode($row['ID_SolSalida']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }?>
					}
				});
			}
		});
		// Actualización del concepto de salida, llega hasta aquí.
	});
</script>
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include_once "includes/menu.php";?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once "includes/menu_superior.php";?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Salida de traslado</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Inventario</a>
                        </li>
                        <li class="active">
                            <strong>Salida de traslado</strong>
                        </li>
                    </ol>
                </div>
            </div>

         <div class="wrapper wrapper-content">
			<!-- SMM, 31/08/2022 -->
			<?php include_once 'md_consultar_llamadas_servicios.php';?>

		<!-- Campos de auditoria de documento. SMM, 23/12/2022 -->
		<?php if ($edit == 1) {?>
			<div class="row">
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Creada por</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php if (isset($row['CDU_UsuarioCreacion']) && ($row['CDU_UsuarioCreacion'] != "")) {echo $row['CDU_UsuarioCreacion'];} else {echo "&nbsp;";}?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Fecha creación</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php echo (isset($row['CDU_FechaHoraCreacion']) && ($row['CDU_FechaHoraCreacion'] != "")) ? $row['CDU_FechaHoraCreacion']->format('Y-m-d H:i') : "&nbsp;"; ?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Actualizado por</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php if (isset($row['CDU_UsuarioActualizacion']) && ($row['CDU_UsuarioActualizacion'] != "")) {echo $row['CDU_UsuarioActualizacion'];} else {echo "&nbsp;";}?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Fecha actualización</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php echo (isset($row['CDU_FechaHoraActualizacion']) && ($row['CDU_FechaHoraActualizacion'] != "")) ? $row['CDU_FechaHoraActualizacion']->format('Y-m-d H:i') : "&nbsp;"; ?></h3>
						</div>
					</div>
				</div>
			</div>
		<?php }?>
		<!-- Hasta aquí. SMM, 23/12/2022 -->

		 <?php if ($edit == 1) {?>
		 <div class="row">
			<div class="col-lg-12">
				<div class="ibox-content">
				<?php include "includes/spinner.php";?>
					<div class="form-group">
						<div class="col-lg-6">
							<!-- SMM, 22/02/2023 -->
							<div class="btn-group">
								<button data-toggle="dropdown" class="btn btn-outline btn-success dropdown-toggle"><i class="fa fa-download"></i> Descargar formato <i class="fa fa-caret-down"></i></button>
								<ul class="dropdown-menu">
									<?php $SQL_Formato = Seleccionar('uvw_tbl_FormatosSAP', '*', "ID_Objeto=60 AND (IdFormato='" . $row['IdSeries'] . "' OR DeSeries IS NULL) AND VerEnDocumento='Y' AND (EsBorrador='N' OR EsBorrador IS NULL)");?>
									<?php while ($row_Formato = sqlsrv_fetch_array($SQL_Formato)) {?>
										<li>
											<a class="dropdown-item" target="_blank" href="sapdownload.php?type=<?php echo base64_encode('2'); ?>&id=<?php echo base64_encode('15'); ?>&ObType=<?php echo base64_encode($row_Formato['ID_Objeto']); ?>&IdFrm=<?php echo base64_encode($row_Formato['IdFormato']); ?>&DocKey=<?php echo base64_encode($row['DocEntry']); ?>&IdReg=<?php echo base64_encode($row_Formato['ID']); ?>"><?php echo $row_Formato['NombreVisualizar']; ?></a>
										</li>
									<?php }?>
								</ul>
							</div>
							<!-- Hasta aquí, 22/02/2023 -->

							<a href="#" class="btn btn-info btn-outline" onClick="VerMapaRel('<?php echo base64_encode($row['DocEntry']); ?>','<?php echo base64_encode('60'); ?>');"><i class="fa fa-sitemap"></i> Mapa de relaciones</a>
						</div>
						<div class="col-lg-6">
							<?php if ($row['DocBaseDocEntry'] != "") {?>
								<a href="traslado_inventario.php?id=<?php echo base64_encode($row['DocBaseDocEntry']); ?>&id_portal=<?php echo base64_encode($row['DocBaseIdPortal']); ?>&tl=1" target="_blank" class="btn btn-outline btn-success pull-right m-l-sm"><i class="fa fa-mail-reply"></i> Ir a documento base</i></a>
							<?php }?>
							<button type="button" onClick="javascript:location.href='actividad.php?dt_DM=1&Cardcode=<?php echo base64_encode($row['CardCode']); ?>&Contacto=<?php echo base64_encode($row['CodigoContacto']); ?>&Sucursal=<?php echo base64_encode($row['SucursalDestino']); ?>&Direccion=<?php echo base64_encode($row['DireccionDestino']); ?>&DM_type=<?php echo base64_encode('60'); ?>&DM=<?php echo base64_encode($row['DocEntry']); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('salida_inventario.php'); ?>'" class="alkin btn btn-primary pull-right"><i class="fa fa-plus-circle"></i> Agregar actividad</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<br>
		<?php }?>
			 <div class="ibox-content">
				 <?php include "includes/spinner.php";?>
          <div class="row">
           <div class="col-lg-12">
              <form action="salida_inventario.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="CrearSalidaInventario">
				<div class="form-group">
					<label class="col-md-8 col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-user"></i> Información de cliente</h3></label>
					<label class="col-md-4 col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-calendar"></i> Fechas y estado de documento</h3></label>
				</div>
				<div class="col-lg-8">
					<div class="form-group">
						<label class="col-lg-1 control-label"><i onClick="ConsultarDatosCliente();" title="Consultar cliente" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Cliente</label>
						<div class="col-lg-9">
							<input name="CardCode" type="hidden" id="CardCode" value="<?php if (($edit == 1) || ($sw_error == 1)) {echo $row['CardCode'];} elseif ($dt_TI == 1) {echo $row_Cliente['CodigoCliente'];} elseif (($edit == 0) && ($ClienteDefault != "")) {echo $ClienteDefault;}?>">

							<input autocomplete="off" name="CardName" type="text" required="required" class="form-control" id="CardName" placeholder="Digite para buscar..." value="<?php if (($edit == 1) || ($sw_error == 1)) {echo $row['NombreCliente'];} elseif ($dt_TI == 1) {echo $row_Cliente['NombreCliente'];} elseif (($edit == 0) && ($ClienteDefault != "")) {echo $NombreClienteDefault;}?>" <?php if (((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) || ($dt_TI == 1) || ($edit == 1)) {echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Contacto</label>
						<div class="col-lg-5">
							<select name="ContactoCliente" class="form-control" id="ContactoCliente" required <?php if (((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) || ($dt_TI == 1)) {echo "readonly";}?>>
									<option value="">Seleccione...</option>
							<?php
if ($edit == 1 || $sw_error == 1) {
    while ($row_ContactoCliente = sqlsrv_fetch_array($SQL_ContactoCliente)) {?>
										<option value="<?php echo $row_ContactoCliente['CodigoContacto']; ?>" <?php if ((isset($row['CodigoContacto'])) && (strcmp($row_ContactoCliente['CodigoContacto'], $row['CodigoContacto']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_ContactoCliente['ID_Contacto']; ?></option>
						  	<?php }
}?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Sucursal destino</label>
						<div class="col-lg-5">
							<select name="SucursalDestino" class="form-control" id="SucursalDestino" <?php if (((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) || ($dt_TI == 1)) {echo "readonly";}?>>
							  <?php if (($edit == 0) && ($dt_TI == 0)) {?><option value="">Seleccione...</option><?php }?>
							  <?php if (($edit == 1) || ($sw_error == 1) || ($dt_TI == 1)) {while ($row_SucursalDestino = sqlsrv_fetch_array($SQL_SucursalDestino)) {?>
									<option value="<?php echo $row_SucursalDestino['NombreSucursal']; ?>" <?php if ((isset($row['SucursalDestino'])) && (strcmp($row_SucursalDestino['NombreSucursal'], $row['SucursalDestino']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['Sucursal']) && (strcmp($row_SucursalDestino['NombreSucursal'], base64_decode($_GET['Sucursal'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_SucursalDestino['NombreSucursal']; ?></option>
							  <?php }}?>
							</select>
						</div>

						<label class="col-lg-1 control-label">Sucursal facturación</label>
						<div class="col-lg-5">
							<select name="SucursalFacturacion" class="form-control" id="SucursalFacturacion" <?php if (((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) || ($dt_TI == 1)) {echo "readonly";}?>>
							  <option value="">Seleccione...</option>
							  <?php if (($edit == 1) || ($sw_error == 1) || ($dt_TI == 1)) {while ($row_SucursalFacturacion = sqlsrv_fetch_array($SQL_SucursalFacturacion)) {?>
									<option value="<?php echo $row_SucursalFacturacion['NombreSucursal']; ?>" <?php if ((isset($row['SucursalFacturacion'])) && (strcmp($row_SucursalFacturacion['NombreSucursal'], $row['SucursalFacturacion']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['SucursalFact']) && (strcmp($row_SucursalFacturacion['NombreSucursal'], base64_decode($_GET['SucursalFact'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_SucursalFacturacion['NombreSucursal']; ?></option>
							  <?php }}?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Dirección destino</label>
						<div class="col-lg-5">
							<input type="text" class="form-control" name="DireccionDestino" id="DireccionDestino" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['DireccionDestino'];} elseif ($dt_TI == 1) {echo base64_decode($_GET['Direccion']);}?>" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
						</div>
						<label class="col-lg-1 control-label">Dirección facturación</label>
						<div class="col-lg-5">
							<input type="text" class="form-control" name="DireccionFacturacion" id="DireccionFacturacion" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['DireccionFacturacion'];}?>" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
						</div>
					</div>

					<!-- SMM, 31/08/2022 -->
					<div class="form-group">
						<label class="col-lg-1 control-label"><?php if (($edit == 1) && ($row['ID_LlamadaServicio'] != 0)) {?><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_LlamadaServicio']); ?>&tl=1" target="_blank" title="Consultar Llamada de servicio" class="btn-xs btn-success fa fa-search"></a> <?php }?>Orden servicio</label>
						<div class="col-lg-7">
							<input type="hidden" class="form-control" name="OrdenServicioCliente" id="OrdenServicioCliente" value="<?php if (isset($row_OrdenServicioCliente['ID_LlamadaServicio']) && ($row_OrdenServicioCliente['ID_LlamadaServicio'] != 0)) {echo $row_OrdenServicioCliente['ID_LlamadaServicio'];}?>">
							<input readonly type="text" class="form-control" name="Desc_OrdenServicioCliente" id="Desc_OrdenServicioCliente" placeholder="Haga clic en el botón"
							value="<?php if (isset($row_OrdenServicioCliente['ID_LlamadaServicio']) && ($row_OrdenServicioCliente['ID_LlamadaServicio'] != 0)) {echo $row_OrdenServicioCliente['DocNum'] . " - " . $row_OrdenServicioCliente['AsuntoLlamada'] . " (" . $row_OrdenServicioCliente['DeTipoLlamada'] . ")";}?>">
						</div>
						<div class="col-lg-4">
							<button class="btn btn-success" type="button" onClick="$('#mdOT').modal('show');"><i class="fa fa-refresh"></i> Cambiar orden servicio</button>
						</div>
					</div>
					<!-- Hasta aquí -->
				</div>
				<div class="col-lg-4">
					<div class="form-group">
						<label class="col-lg-5">Número</label>
						<div class="col-lg-7">
							<input type="text" name="DocNum" id="DocNum" class="form-control" value="<?php if ($edit == 1) {echo $row['DocNum'];}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-5">Fecha de contabilización</label>
						<div class="col-lg-7 input-group date">
							 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="DocDate" id="DocDate" type="text" required="required" class="form-control" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['DocDate'];} else {echo date('Y-m-d');}?>" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-5">Fecha de requerida salida</label>
						<div class="col-lg-7 input-group date">
							 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="DocDueDate" id="DocDueDate" type="text" required="required" class="form-control" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['DocDueDate'];} else {echo date('Y-m-d');}?>" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-5">Fecha del documento</label>
						<div class="col-lg-7 input-group date">
							 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="TaxDate" id="TaxDate" type="text" required="required" class="form-control" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['TaxDate'];} else {echo date('Y-m-d');}?>" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-5">Estado</label>
						<div class="col-lg-7">
							<select name="EstadoDoc" class="form-control" id="EstadoDoc" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
							  <?php while ($row_EstadoDoc = sqlsrv_fetch_array($SQL_EstadoDoc)) {?>
									<option value="<?php echo $row_EstadoDoc['Cod_Estado']; ?>" <?php if (($edit == 1) && (isset($row['Cod_Estado'])) && (strcmp($row_EstadoDoc['Cod_Estado'], $row['Cod_Estado']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_EstadoDoc['NombreEstado']; ?></option>
							  <?php }?>
							</select>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Datos de la Salida</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Serie</label>
					<div class="col-lg-3">
                    	<select name="Serie" class="form-control" id="Serie" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
                          <?php while ($row_Series = sqlsrv_fetch_array($SQL_Series)) {?>
								<option value="<?php echo $row_Series['IdSeries']; ?>" <?php if (($edit == 1 || $sw_error == 1) && (isset($row['IdSeries'])) && (strcmp($row_Series['IdSeries'], $row['IdSeries']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries']; ?></option>
						  <?php }?>
						</select>
               	  	</div>

					<label class="col-lg-1 control-label">Referencia</label>
					<div class="col-lg-3">
                    	<input type="text" name="Referencia" id="Referencia" class="form-control" value="<?php if ($edit == 1) {echo $row['NumAtCard'];}?>" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
               	  	</div>
				</div>

				<!-- Dimensiones dinámicas, SMM 31/08/2022 -->
				<div class="form-group">
					<?php foreach ($array_Dimensiones as &$dim) {?>
						<label class="col-lg-1 control-label"><?php echo $dim['DescPortalOne']; ?> <span class="text-danger">*</span></label>
						<div class="col-lg-3">
							<select name="<?php echo $dim['IdPortalOne'] ?>" id="<?php echo $dim['IdPortalOne'] ?>" class="form-control" required="required" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
								<option value="">Seleccione...</option>

							<?php $SQL_Dim = Seleccionar('uvw_Sap_tbl_DimensionesReparto', '*', 'DimCode=' . $dim['DimCode']);?>
							<?php while ($row_Dim = sqlsrv_fetch_array($SQL_Dim)) {?>
								<?php $DimCode = intval($dim['DimCode']);?>
								<?php $OcrId = ($DimCode == 1) ? "" : $DimCode;?>

								<option value="<?php echo $row_Dim['OcrCode']; ?>"
								<?php if ((isset($row["OcrCode$OcrId"]) && ($row["OcrCode$OcrId"] != "")) && (strcmp($row_Dim['OcrCode'], $row["OcrCode$OcrId"]) == 0)) {echo "selected=\"selected\"";} elseif (($edit == 0) && (isset($_GET['LMT']) && !isset($_GET[strval($dim['IdPortalOne'])])) && ($row_DatosEmpleados["CentroCosto$DimCode"] != "") && (strcmp($row_DatosEmpleados["CentroCosto$DimCode"], $row_Dim['OcrCode']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET[strval($dim['IdPortalOne'])]) && (strcmp($row_Dim['OcrCode'], base64_decode($_GET[strval($dim['IdPortalOne'])])) == 0)) {echo "selected=\"selected\"";}?>>
									<?php echo $row_Dim['OcrCode'] . "-" . $row_Dim['OcrName']; ?>
								</option>
							<?php }?>
							</select>
						</div>
					<?php }?>
				</div>
				<!-- Dimensiones dinámicas, hasta aquí -->

				<div class="form-group">
					<label class="col-lg-1 control-label">Almacén</label>
					<div class="col-lg-3">
						<select name="Almacen" class="form-control" id="Almacen" required="required" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
							<option value="">Seleccione...</option>
						  <?php if ($edit == 1) {
    while ($row_Almacen = sqlsrv_fetch_array($SQL_Almacen)) {?>
									<option value="<?php echo $row_Almacen['WhsCode']; ?>" <?php if (($edit == 1) && (isset($row['WhsCode'])) && (strcmp($row_Almacen['WhsCode'], $row['WhsCode']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Almacen['WhsName']; ?></option>
						  <?php }
}?>
						</select>
					</div>
					<label class="col-lg-1 control-label">Condición de pago</label>
					<div class="col-lg-3">
						<select name="CondicionPago" class="form-control" id="CondicionPago" required="required" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
							<option value="">Seleccione...</option>
						  <?php while ($row_CondicionPago = sqlsrv_fetch_array($SQL_CondicionPago)) {?>
								<option value="<?php echo $row_CondicionPago['IdCondicionPago']; ?>" <?php if ($edit == 1) {if (($row['IdCondicionPago'] != "") && (strcmp($row_CondicionPago['IdCondicionPago'], $row['IdCondicionPago']) == 0)) {echo "selected=\"selected\"";}}?>><?php echo $row_CondicionPago['NombreCondicion']; ?></option>
						  <?php }?>
						</select>
				  	</div>
					<label class="col-lg-1 control-label">Solicitado para</label>
					<div class="col-lg-3">
                    	<select name="Empleado" class="form-control" required id="Empleado" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
								<?php if (($edit == 0) && ($dt_TI == 0)) {?><option value="">Seleccione...</option><?php }?>
                          <?php while ($row_Empleado = sqlsrv_fetch_array($SQL_Empleado)) {?>
								<option value="<?php echo $row_Empleado['ID_Empleado']; ?>" <?php if ((isset($row['CodEmpleado'])) && (strcmp($row_Empleado['ID_Empleado'], $row['CodEmpleado']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['Empleado']) && (strcmp($row_Empleado['ID_Empleado'], base64_decode($_GET['Empleado'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Empleado['NombreEmpleado']; ?></option>
						  <?php }?>
						</select>
               	  	</div>
				</div>
				<div class="form-group">
					<!-- Inicio, Proyecto -->
					<label class="col-lg-1 control-label">Proyecto <span class="text-danger">*</span></label>
					<div class="col-lg-3">
						<select id="PrjCode" name="PrjCode" class="form-control" required="required" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
								<option value="">(NINGUNO)</option>
							<?php while ($row_Proyecto = sqlsrv_fetch_array($SQL_Proyecto)) {?>
								<option value="<?php echo $row_Proyecto['IdProyecto']; ?>" <?php if ((isset($row['PrjCode'])) && (strcmp($row_Proyecto['IdProyecto'], $row['PrjCode']) == 0)) {echo "selected=\"selected\"";} elseif ((isset($_GET['Proyecto'])) && (strcmp($row_Proyecto['IdProyecto'], base64_decode($_GET['Proyecto'])) == 0)) {echo "selected=\"selected\"";}?>>
									<?php echo $row_Proyecto['IdProyecto'] . "-" . $row_Proyecto['DeProyecto']; ?>
								</option>
							<?php }?>
						</select>
					</div>
					<!-- Fin, Proyecto -->

					<!-- SMM, 23/12/2022 -->
					<label class="col-lg-1 control-label">Concepto Salida</label>
					<div class="col-lg-3">
						<select name="ConceptoSalida" class="form-control" id="ConceptoSalida" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C') || ($dt_TI == 1)) {echo "readonly";}?>>
								<option value="">Seleccione...</option>
								<?php while ($row_ConceptoSalida = sqlsrv_fetch_array($SQL_ConceptoSalida)) {?>
									<option value="<?php echo $row_ConceptoSalida['id_concepto_salida']; ?>" <?php if ((isset($row['ConceptoSalida'])) && (strcmp($row_ConceptoSalida['id_concepto_salida'], $row['ConceptoSalida']) == 0)) {echo "selected";} elseif ((isset($_GET['ConceptoSalida'])) && (strcmp($row_ConceptoSalida['id_concepto_salida'], base64_decode($_GET['ConceptoSalida'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_ConceptoSalida['id_concepto_salida'] . "-" . $row_ConceptoSalida['concepto_salida']; ?></option>
								<?php }?>
						</select>
					</div>
					<!-- Hasta aquí, 23/12/2022 -->

					<label class="col-lg-1 control-label">Tipo entrega</label>
					<div class="col-lg-3">
                    	<select name="TipoEntrega" class="form-control" id="TipoEntrega" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
								<option value="">Seleccione...</option>
                          <?php while ($row_TipoEntrega = sqlsrv_fetch_array($SQL_TipoEntrega)) {?>
								<option value="<?php echo $row_TipoEntrega['IdTipoEntrega']; ?>" <?php if ((isset($row['IdTipoEntrega'])) && (strcmp($row_TipoEntrega['IdTipoEntrega'], $row['IdTipoEntrega']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['TipoEntrega']) && (strcmp($row_TipoEntrega['IdTipoEntrega'], base64_decode($_GET['TipoEntrega'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_TipoEntrega['DeTipoEntrega']; ?></option>
						  <?php }?>
						</select>
               	  	</div>
					<div id="dv_AnioEnt" style="display: none;">
						<label class="col-lg-1 control-label">Año entrega</label>
						<div class="col-lg-2">
							<select name="AnioEntrega" class="form-control" id="AnioEntrega" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
							  <?php while ($row_AnioEntrega = sqlsrv_fetch_array($SQL_AnioEntrega)) {?>
									<option value="<?php echo $row_AnioEntrega['IdAnioEntrega']; ?>" <?php if ((isset($row['IdAnioEntrega'])) && (strcmp($row_AnioEntrega['IdAnioEntrega'], $row['IdAnioEntrega']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['AnioEntrega']) && (strcmp($row_AnioEntrega['IdAnioEntrega'], base64_decode($_GET['AnioEntrega'])) == 0)) {echo "selected=\"selected\"";} elseif (date('Y') == $row_AnioEntrega['DeAnioEntrega']) {echo "selected=\"selected\"";}?>><?php echo $row_AnioEntrega['DeAnioEntrega']; ?></option>
							  <?php }?>
							</select>
						</div>
					</div>
					<div id="dv_Descont" style="display: none;">
						<label class="col-lg-1 control-label">Entrega descontable</label>
						<div class="col-lg-2">
							<select name="EntregaDescont" class="form-control" id="EntregaDescont" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
								<option value="NO" <?php if (($edit == 1) && ($row['Descontable'] == "NO")) {echo "selected=\"selected\"";} elseif (isset($_GET['EntregaDescont']) && (base64_decode($_GET['EntregaDescont']) == "NO")) {echo "selected=\"selected\"";}?>>NO</option>
								<option value="SI" <?php if (($edit == 1) && ($row['Descontable'] == "SI")) {echo "selected=\"selected\"";} elseif (isset($_GET['EntregaDescont']) && (base64_decode($_GET['EntregaDescont']) == "SI")) {echo "selected=\"selected\"";}?>>SI</option>
							</select>
						</div>
					</div>
					<div id="dv_VlrCuota" style="display: none;">
						<label class="col-lg-1 control-label">Cant cuota</label>
						<div class="col-lg-2">
							<input type="text" class="form-control" name="ValorCuotaDesc" id="ValorCuotaDesc" onKeyPress="return justNumbers(event,this.value);" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['ValorCuotaDesc'];} elseif (isset($_GET['ValorCuotaDesc'])) {echo base64_decode($_GET['ValorCuotaDesc']);}?>" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Contenido de la Salida</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Buscar articulo</label>
					<div class="col-lg-4">
                    	<input name="BuscarItem" id="BuscarItem" type="text" class="form-control" placeholder="Escriba para buscar..." onBlur="javascript:BuscarArticulo(this.value);" <?php if (((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) || (!PermitirFuncion(1205)) || ($dt_TI == 1)) {echo "readonly";}?>>
               	  	</div>
				</div>
				<div class="tabs-container">
					<ul class="nav nav-tabs">
						<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Contenido</a></li>
						<?php if ($edit == 1) {?><li><a data-toggle="tab" href="#tab-2" onClick="ConsultarTab('2');"><i class="fa fa-calendar"></i> Actividades</a></li><?php }?>
						<li><a data-toggle="tab" href="#tab-3"><i class="fa fa-paperclip"></i> Anexos</a></li>
						<li><span class="TimeAct"><div id="TimeAct">&nbsp;</div></span></li>
						<span class="TotalItems"><strong>Total Items:</strong>&nbsp;<input type="text" name="TotalItems" id="TotalItems" class="txtLimpio" value="0" size="1" readonly></span>
					</ul>
					<div class="tab-content">
						<div id="tab-1" class="tab-pane active">
							<iframe id="DataGrid" name="DataGrid" style="border: 0;" width="100%" height="300" src="<?php if ($edit == 0) {echo "detalle_salida_inventario.php";} else {echo "detalle_salida_inventario.php?id=" . base64_encode($row['ID_SalidaInv']) . "&evento=" . base64_encode($row['IdEvento']) . "&type=2&status=" . base64_encode($row['Cod_Estado']) . "&dt_TI=" . $dt_TI;}?>"></iframe>
						</div>
						<?php if ($edit == 1) {?>
						<div id="tab-2" class="tab-pane">
							<div id="dv_actividades" class="panel-body">

							</div>
						</div>
						<?php }?>
						 </form>
						<div id="tab-3" class="tab-pane">
							<div class="panel-body">
								<?php if ($edit == 1) {
    if ($row['IdAnexo'] != 0) {?>
										<div class="form-group">
											<div class="col-lg-4">
											 <ul class="folder-list" style="padding: 0">
											<?php while ($row_Anexo = sqlsrv_fetch_array($SQL_Anexo)) {
        $Icon = IconAttach($row_Anexo['FileExt']);
        ?>
												<li><a href="attachdownload.php?file=<?php echo base64_encode($row_Anexo['AbsEntry']); ?>&line=<?php echo base64_encode($row_Anexo['Line']); ?>" target="_blank" class="btn-link btn-xs"><i class="<?php echo $Icon; ?>"></i> <?php echo $row_Anexo['NombreArchivo']; ?></a></li>
											<?php }?>
											 </ul>
											</div>
										</div>
							<?php } else {echo "<p>Sin anexos.</p>";}
} elseif ($edit == 0) {
    LimpiarDirTemp();?>
								<div class="row">
									<form action="upload.php" class="dropzone" id="dropzoneForm" name="dropzoneForm">
										<div class="fallback">
											<input name="File" id="File" type="file" form="dropzoneForm" />
										</div>
									 </form>
								</div>
								<?php }?>
							</div>
				   		</div>
					</div>
				</div>
			   <form id="frm" action="" class="form-horizontal">
				<div class="form-group">&nbsp;</div>
				<div class="col-lg-8">
					<div class="form-group">
						<label class="col-lg-2">Empleado de ventas</label>
						<div class="col-lg-5">
							<input type="text" name="EmpleadoVentas" form="CrearSalidaInventario" class="form-control" id="EmpleadoVentas" value="<?php if ($edit == 1) {echo $row['NombreEmpleado'];} else {echo $_SESSION['NomUser'];}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-2">Comentarios</label>
						<div class="col-lg-10">
							<textarea name="Comentarios" form="CrearSalidaInventario" rows="4" class="form-control" id="Comentarios" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_TI == 1)) {echo "readonly";}?>><?php if ($edit == 1) {echo $row['Comentarios'];}?></textarea>
						</div>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Subtotal</strong></label>
						<div class="col-lg-5">
							<input type="text" name="SubTotal" form="CrearSalidaInventario" id="SubTotal" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['SubTotal'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Descuentos</strong></label>
						<div class="col-lg-5">
							<input type="text" name="Descuentos" form="CrearSalidaInventario" id="Descuentos" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['DiscSum'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">IVA</strong></label>
						<div class="col-lg-5">
							<input type="text" name="Impuestos" form="CrearSalidaInventario" id="Impuestos" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['VatSum'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Total</strong></label>
						<div class="col-lg-5">
							<input type="text" name="TotalSalida" form="CrearSalidaInventario" id="TotalSalida" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['DocTotal'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-9">
						<?php if ($edit == 0 && PermitirFuncion(1205)) {?>
							<button class="btn btn-primary" type="submit" form="CrearSalidaInventario" id="Crear"><i class="fa fa-check"></i> Crear Salida de traslado</button>
						<?php } elseif (isset($row['Cod_Estado']) && $row['Cod_Estado'] == "O" && PermitirFuncion(1205)) {?>
							<button class="btn btn-warning" type="submit" form="CrearSalidaInventario" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar Salida de traslado</button>
						<?php } elseif (!PermitirFuncion(1205)) {?>
							<!-- p>No tiene permiso, 1205</p -->
						<?php }?>
						<?php
$EliminaMsg = array("&a=" . base64_encode("OK_SalInvAdd"), "&a=" . base64_encode("OK_SalInvUpd")); //Eliminar mensajes
if (isset($_GET['return'])) {
    $_GET['return'] = str_replace($EliminaMsg, "", base64_decode($_GET['return']));
}
if (isset($_GET['return'])) {
    $return = base64_decode($_GET['pag']) . "?" . $_GET['return'];
} elseif (isset($_POST['return'])) {
    $return = base64_decode($_POST['return']);
} else {
    $return = "salida_inventario.php?";
}
?>
						<a href="<?php echo $return; ?>" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
					</div>

<!-- Dimensiones dinámicas, SMM 31/08/2022 -->
<?php if ($edit == 1) {
    $CopyDim = "";
    foreach ($array_Dimensiones as &$dim) {
        $DimCode = intval($dim['DimCode']);
        $OcrId = ($DimCode == 1) ? "" : $DimCode;

        $DimIdPO = $dim['IdPortalOne'];
        $encode_OcrCode = base64_encode($row["OcrCode$OcrId"]);
        $CopyDim .= "$DimIdPO=$encode_OcrCode&";
    }
}?>
<!-- Hasta aquí, 31/08/2022 -->
				</div>
				<input type="hidden" form="CrearSalidaInventario" id="P" name="P" value="51" />
				<input type="hidden" form="CrearSalidaInventario" id="IdSalidaInv" name="IdSalidaInv" value="<?php if ($edit == 1) {echo base64_encode($row['ID_SalidaInv']);}?>" />
				<input type="hidden" form="CrearSalidaInventario" id="IdEvento" name="IdEvento" value="<?php if ($edit == 1) {echo base64_encode($IdEvento);}?>" />
				<input type="hidden" form="CrearSalidaInventario" id="tl" name="tl" value="<?php echo $edit; ?>" />
				<input type="hidden" form="CrearSalidaInventario" id="dt_TI" name="dt_TI" value="<?php echo $dt_TI; ?>" />
				<input type="hidden" form="CrearSalidaInventario" id="swError" name="swError" value="<?php echo $sw_error; ?>" />
				<input type="hidden" form="CrearSalidaInventario" id="return" name="return" value="<?php echo base64_encode($return); ?>" />
			 </form>
		   </div>
			</div>
          </div>
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once "includes/footer.php";?>

    </div>
</div>
<?php include_once "includes/pie.php";?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>
	$(document).ready(function(){
		// SMM, 20/01/2023
		<?php if (($edit == 0) && ($ClienteDefault != "")) {?>
			$("#CardCode").change();
		<?php }?>

		$("#CrearSalidaInventario").validate({
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
		 <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'O') || ($edit == 0))) {?>
		 $('#DocDate').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                autoclose: true,
				format: 'yyyy-mm-dd',
			 	todayHighlight: true,
			 	startDate: '<?php echo date('Y-m-d'); ?>'
            });
		 $('#DocDueDate').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                autoclose: true,
				format: 'yyyy-mm-dd',
			 	todayHighlight: true,
			 	startDate: '<?php echo date('Y-m-d'); ?>'
            });
		 $('#TaxDate').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                autoclose: true,
				format: 'yyyy-mm-dd',
			 	todayHighlight: true,
			 	startDate: '<?php echo date('Y-m-d'); ?>'
            });
	 	 <?php }?>
		 //$('.chosen-select').chosen({width: "100%"});
		 $(".select2").select2();

		 <?php if ($edit == 1) {?>
		 $('#Serie option:not(:selected)').attr('disabled',true);
		 $('#Sucursal option:not(:selected)').attr('disabled',true);
		 $('#Almacen option:not(:selected)').attr('disabled',true);

		 $('#TipoEntrega').trigger('change');
	 	 <?php }?>

		 <?php if ($dt_TI == 1) {?>
		 $('#TipoEntrega').trigger('change');
		 $('#Empleado option:not(:selected)').attr('disabled',true);
		 $('#CentroCosto option:not(:selected)').attr('disabled',true);
		 $('#UnidadNegocio option:not(:selected)').attr('disabled',true);
		 $('#CondicionPago option:not(:selected)').attr('disabled',true);
		 $('#TipoEntrega option:not(:selected)').attr('disabled',true);
		 <?php }?>

		 <?php
if (!PermitirFuncion(403)) {?>
		 $('#Autorizacion option:not(:selected)').attr('disabled',true);
	 	 <?php }?>

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
		 <?php if ($edit == 0) {?>
		 $("#CardName").easyAutocomplete(options);
	 	 <?php }?>

		 <?php if ($dt_TI == 1) {?>
			$('#CardCode').trigger('change');

			// SMM, 06/12/2022
			$('#SucursalFacturacion').trigger('change');
		<?php }?>

		<?php if ($edit == 0) {?>
		 $('#Serie').trigger('change');
	 	<?php }?>
	});
</script>
<script>
//Variables de tab
 var tab_2=0;

function ConsultarTab(type){
	if(type==2){//Actividades
		if(tab_2==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "dm_actividades.php?id=<?php if ($edit == 1) {echo base64_encode($row['DocEntry']);}?>&objtype=60",
				success: function(response){
					$('#dv_actividades').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_2=1;
				}
			});
		}
	}
}
</script>
<script>
function Validar(){
	var result=true;

	<?php if ($edit == 0) {?>
		//Validar que los items con lote ya fueron seleccionados
		var Cliente=document.getElementById('CardCode').value;
		var almacen=document.getElementById('Almacen').value;

		$.ajax({
			url:"ajx_buscar_datos_json.php",
			data:{type:27,
				  cardcode:Cliente,
				  objtype:60,
				  whscode:almacen},
			dataType:'json',
			async: false,
			success: function(data){
				if(data.Estado=='0'){
					result=false;
					Swal.fire({
						title: data.Title,
						text: data.Mensaje,
						icon: data.Icon,
					});
				}
			}
		});
	<?php }?>

	var TotalItems = document.getElementById("TotalItems");

	if(TotalItems.value=="0"){
		result=false;
		Swal.fire({
			title: '¡Lo sentimos!',
			text: 'No puede guardar el documento sin contenido. Por favor verifique.',
			icon: 'error'
		});
	}

	return result;
}
</script>
<script>
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
