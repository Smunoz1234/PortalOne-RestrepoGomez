<?php require_once "includes/conexion.php";
PermitirAcceso(406);

$dt_LS = 0; //sw para saber si vienen datos de la llamada de servicio. 0 no vienen. 1 si vienen.
$dt_OV = 0; //sw para saber si vienen datos de una Orden de venta.

// Dimensiones, SMM 23/08/2022
$DimSeries = intval(ObtenerVariable("DimensionSeries"));
$SQL_Dimensiones = Seleccionar('uvw_Sap_tbl_Dimensiones', '*', "DimActive='Y'");

// Pruebas, SMM 23/08/2022
// $SQL_Dimensiones = Seleccionar('uvw_Sap_tbl_Dimensiones', '*', 'DimCode IN (1,2)');

$array_Dimensiones = [];
while ($row_Dimension = sqlsrv_fetch_array($SQL_Dimensiones)) {
    array_push($array_Dimensiones, $row_Dimension);
}

$encode_Dimensiones = json_encode($array_Dimensiones);
$cadena_Dimensiones = "JSON.parse('$encode_Dimensiones'.replace(/\\n|\\r/g, ''))";
// echo "<script> console.log('cadena_Dimensiones'); </script>";
// echo "<script> console.log($cadena_Dimensiones); </script>";
// Hasta aquí, SMM 23/08/2022

// SMM, 08/08/2022
$IdLlamada = "'" . base64_decode($_GET['LS'] ?? ($_GET['IdLlamada'] ?? "")) . "'";

$msg_error = ""; //Mensaje del error
$IdFactura = 0;
$IdPortal = 0; //Id del portal para las factura que fueron creadas en el portal, para eliminar el registro antes de cargar al editar

if (isset($_GET['id']) && ($_GET['id'] != "")) { //ID de la Orden de venta (DocEntry)
    $IdFactura = base64_decode($_GET['id']);
}

if (isset($_GET['id_portal']) && ($_GET['id_portal'] != "")) { //Id del portal de venta (ID interno)
    $IdPortal = base64_decode($_GET['id_portal']);
}

if (isset($_POST['IdFacturaVenta']) && ($_POST['IdFacturaVenta'] != "")) { //Tambien el Id interno, pero lo envío cuando mando el formulario
    $IdFacturaVenta = base64_decode($_POST['IdFacturaVenta']);
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

if (isset($_POST['P']) && ($_POST['P'] != "")) { //Grabar Factura de venta
    //*** Carpeta temporal ***
    $i = 0; //Archivos
    $RutaAttachSAP = ObtenerDirAttach();
    $dir = CrearObtenerDirTemp();
    $dir_new = CrearObtenerDirAnx("facturaventa");
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
            $IdFacturaVenta = base64_decode($_POST['IdFacturaVenta']);
            $IdEvento = base64_decode($_POST['IdEvento']);
            $Type = 2;
            if (!PermitirFuncion(403)) { //Permiso para autorizar factura de venta
                $_POST['Autorizacion'] = 'P'; //Si no tengo el permiso, la factura queda pendiente
            }
        } else { //Crear
            $IdFacturaVenta = "NULL";
            $IdEvento = "0";
            $Type = 1;
        }
        $ParametrosCabFacturaVenta = array(
            $IdFacturaVenta,
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
            "'" . $_POST['EmpleadoVentas'] . "'",
            "'" . LSiqmlObs($_POST['Comentarios']) . "'",
            "'" . str_replace(',', '', $_POST['SubTotal']) . "'",
            "'" . str_replace(',', '', $_POST['Descuentos']) . "'",
            "NULL",
            "'" . str_replace(',', '', $_POST['Impuestos']) . "'",
            "'" . str_replace(',', '', $_POST['TotalFactura']) . "'",
            "'" . $_POST['SucursalFacturacion'] . "'",
            "'" . $_POST['DireccionFacturacion'] . "'",
            "'" . $_POST['SucursalDestino'] . "'",
            "'" . $_POST['DireccionDestino'] . "'",
            "'" . $_POST['CondicionPago'] . "'",
            "'" . $_POST['Dim1'] . "'",
            "'" . $_POST['Dim2'] . "'",
            "'" . $_POST['Dim3'] . "'",
            "'" . $_POST['PrjCode'] . "'",
            "'" . $_POST['Autorizacion'] . "'",
            "'" . $_POST['Almacen'] . "'",
            "'" . $_SESSION['CodUser'] . "'",
            "'" . $_SESSION['CodUser'] . "'",
            "$Type",
        );
        $SQL_CabeceraFacturaVenta = EjecutarSP('sp_tbl_FacturaVenta', $ParametrosCabFacturaVenta, $_POST['P']);
        if ($SQL_CabeceraFacturaVenta) {
            if ($Type == 1) {
                $row_CabeceraFacturaVenta = sqlsrv_fetch_array($SQL_CabeceraFacturaVenta);
                $IdFacturaVenta = $row_CabeceraFacturaVenta[0];
                $IdEvento = $row_CabeceraFacturaVenta[1];
            } else {
                $IdFacturaVenta = base64_decode($_POST['IdFacturaVenta']); //Lo coloco otra vez solo para saber que tiene ese valor
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
                            "'13'",
                            "'" . $IdFacturaVenta . "'",
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
                $Parametros = array(
                    'id_documento' => intval($IdFacturaVenta),
                    'id_evento' => intval($IdEvento),
                );
                $Metodo = "FacturasVentas";
                $Resultado = EnviarWebServiceSAP($Metodo, $Parametros, true, true);

                if ($Resultado->Success == 0) {
                    $sw_error = 1;
                    $msg_error = $Resultado->Mensaje;
                } else {
                    sqlsrv_close($conexion);
                    if ($_POST['tl'] == 0) { //Creando factura
                        header('Location:' . base64_decode($_POST['return']) . '&a=' . base64_encode("OK_FactVentAdd"));
                    } else { //Actualizando factura
                        header('Location:' . base64_decode($_POST['return']) . '&a=' . base64_encode("OK_FactVentUpd"));
                    }
                }
            } catch (Exception $e) {
                echo 'Excepcion capturada: ', $e->getMessage(), "\n";
            }

        } else {
            $sw_error = 1;
            $msg_error = "Ha ocurrido un error al crear la factura de venta";
        }
    } catch (Exception $e) {
        echo 'Excepcion capturada: ', $e->getMessage(), "\n";
    }

}

if (isset($_GET['dt_OV']) && ($_GET['dt_OV']) == 1) { //Verificar que viene de una Orden de ventas
    $dt_OV = 1;

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreCliente');
    $row_Cliente = sqlsrv_fetch_array($SQL_Cliente);

    //Contacto cliente
    //$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreContacto');

    $ParametrosCopiarOrdenToFactura = array(
        "'" . base64_decode($_GET['OV']) . "'",
        "'" . base64_decode($_GET['Evento']) . "'",
        "'" . base64_decode($_GET['Almacen']) . "'",
        "'" . base64_decode($_GET['Cardcode']) . "'",
        "'" . $_GET['adt'] . "'",
        "'" . $_SESSION['CodUser'] . "'",
    );
    $SQL_CopiarOrdenToFactura = EjecutarSP('sp_tbl_OrdenVentaDet_To_FacturaVentaDet', $ParametrosCopiarOrdenToFactura);
    if (!$SQL_CopiarOrdenToFactura) {
        echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Ha ocurrido un error!',
				text: 'No se pudo copiar la Orden en Factura de venta.',
				icon: 'error'
			});
		});
		</script>";
    }

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreCliente');
    $row_Cliente = sqlsrv_fetch_array($SQL_Cliente);

    //Contacto cliente
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreContacto');

    //Sucursales, SMM 06/05/2022
    $SQL_SucursalDestino = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "' AND TipoDireccion='S'", 'NombreSucursal');
    $SQL_SucursalFacturacion = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "' AND TipoDireccion='B'", 'NombreSucursal');

    //Orden de servicio, SMM 05/08/2022
    $SQL_OrdenServicioCliente = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_LlamadaServicio=$IdLlamada");
    $row_OrdenServicioCliente = sqlsrv_fetch_array($SQL_OrdenServicioCliente);
}

if (isset($_GET['dt_FC']) && ($_GET['dt_FC']) == 1) { //Verificar que viene de una Facturacion de OTs
    $dt_OV = 1;

    $ParametrosCopiarFactOTToFactura = array(
        "'" . base64_decode($_GET['Cardcode']) . "'",
        "'" . $_SESSION['CodUser'] . "'",
        "'" . base64_decode($_GET['adt']) . "'",
        "'" . base64_decode($_GET['CodFactura']) . "'",
        //"'".base64_decode($_GET['add30'])."'"
    );
    $SQL_CopiarFactOTToFactura = EjecutarSP('sp_tbl_FacturaOTDet_To_FacturaVentaDet', $ParametrosCopiarFactOTToFactura);

    //Verificar si se va a facturar a nombre de otro cliente
    if ($_GET['CodFactura'] != "") {
        $_GET['Cardcode'] = $_GET['CodFactura'];
    }

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreCliente');
    $row_Cliente = sqlsrv_fetch_array($SQL_Cliente);

    if (!$SQL_CopiarFactOTToFactura) {
        echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Ha ocurrido un error!',
				text: 'No se pudo copiar el detale de las ordenes de servicio en Factura de venta.',
				icon: 'error'
			});
		});
		</script>";
    }

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreCliente');
    $row_Cliente = sqlsrv_fetch_array($SQL_Cliente);

    //Contacto cliente
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreContacto');

    //Sucursales, SMM 06/05/2022
    $SQL_SucursalDestino = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "' AND TipoDireccion='S'", 'NombreSucursal');
    $SQL_SucursalFacturacion = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "' AND TipoDireccion='B'", 'NombreSucursal');

    //Orden de servicio, SMM 05/08/2022
    $SQL_OrdenServicioCliente = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_LlamadaServicio=$IdLlamada");
    $row_OrdenServicioCliente = sqlsrv_fetch_array($SQL_OrdenServicioCliente);
}

if (isset($_GET['dt_FC']) && ($_GET['dt_FC']) == 2) { //Verificar que viene de una llamada de servicio
    $dt_OV = 1;

    $ParametrosCopiarFactOTToFactura = array(
        "'" . base64_decode($_GET['Cardcode']) . "'",
        $IdLlamada,
        "'" . base64_decode($_GET['DocNum']) . "'",
        "'" . $_SESSION['CodUser'] . "'",
        "'" . base64_decode($_GET['adt']) . "'",
        "'" . base64_decode($_GET['CodFactura']) . "'",
        //"'".base64_decode($_GET['add30'])."'"
    );
    $SQL_CopiarFactOTToFactura = EjecutarSP('sp_tbl_LlamadaServicio_To_FacturaVentaDet', $ParametrosCopiarFactOTToFactura);

    //Verificar si se va a facturar a nombre de otro cliente
    if ($_GET['CodFactura'] != "") {
        $_GET['Cardcode'] = $_GET['CodFactura'];
    }

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreCliente');
    $row_Cliente = sqlsrv_fetch_array($SQL_Cliente);

    if (!$SQL_CopiarFactOTToFactura) {
        echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Ha ocurrido un error!',
				text: 'No se pudo copiar el detale de las ordenes de servicio en Factura de venta.',
				icon: 'error'
			});
		});
		</script>";
    }

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreCliente');
    $row_Cliente = sqlsrv_fetch_array($SQL_Cliente);

    //Contacto cliente
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreContacto');

    //Sucursales, SMM 06/05/2022
    $SQL_SucursalDestino = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "' AND TipoDireccion='S'", 'NombreSucursal');
    $SQL_SucursalFacturacion = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "' AND TipoDireccion='B'", 'NombreSucursal');

    //Orden de servicio, SMM 05/08/2022
    $SQL_OrdenServicioCliente = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_LlamadaServicio=$IdLlamada");
    $row_OrdenServicioCliente = sqlsrv_fetch_array($SQL_OrdenServicioCliente);
}

if ($edit == 1 && $sw_error == 0) {

    $ParametrosLimpiar = array(
        "'" . $IdFactura . "'",
        "'" . $IdPortal . "'",
        "'" . $_SESSION['CodUser'] . "'",
    );
    $LimpiarFactura = EjecutarSP('sp_EliminarDatosFacturaVenta', $ParametrosLimpiar);

    $SQL_IdEvento = sqlsrv_fetch_array($LimpiarFactura);
    $IdEvento = $SQL_IdEvento[0];

    //Factura de venta
    $Cons = "Select * From uvw_tbl_FacturaVenta Where DocEntry='" . $IdFactura . "' AND IdEvento='" . $IdEvento . "'";
    $SQL = sqlsrv_query($conexion, $Cons);
    $row = sqlsrv_fetch_array($SQL);

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreCliente');

    //Sucursales, SMM 06/05/2022
    $SQL_SucursalDestino = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' AND TipoDireccion='S'", 'NombreSucursal');
    $SQL_SucursalFacturacion = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' AND TipoDireccion='B'", 'NombreSucursal');

    //Contacto cliente
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreContacto');

    //Orden de servicio, SMM 05/08/2022
    $SQL_OrdenServicioCliente = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_LlamadaServicio='" . $row['ID_LlamadaServicio'] . "'");
    $row_OrdenServicioCliente = sqlsrv_fetch_array($SQL_OrdenServicioCliente);

    //Sucursal
    $SQL_Sucursal = Seleccionar('uvw_tbl_SeriesSucursalesAlmacenes', 'IdSucursal, DeSucursal', "IdSeries='" . $row['IdSeries'] . "'");

    //Almacenes
    $SQL_Almacen = Seleccionar('uvw_tbl_SeriesSucursalesAlmacenes', 'WhsCode, WhsName', "IdSeries='" . $row['IdSeries'] . "'", 'WhsName');

    //Anexos
    $SQL_Anexo = Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos', '*', "AbsEntry='" . $row['IdAnexo'] . "'");

}

if ($sw_error == 1) {

    //Factura de venta
    $Cons = "Select * From uvw_tbl_FacturaVenta Where ID_FacturaVenta='" . $IdFacturaVenta . "' AND IdEvento='" . $IdEvento . "'";
    $SQL = sqlsrv_query($conexion, $Cons);
    $row = sqlsrv_fetch_array($SQL);

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreCliente');

    //Sucursales, SMM 06/05/2022
    $SQL_SucursalDestino = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' AND TipoDireccion='S'", 'NombreSucursal');
    $SQL_SucursalFacturacion = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' AND TipoDireccion='B'", 'NombreSucursal');

    //Contacto cliente
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreContacto');

    //Orden de servicio, SMM 05/08/2022
    $SQL_OrdenServicioCliente = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_LlamadaServicio='" . $row['ID_LlamadaServicio'] . "'");
    $row_OrdenServicioCliente = sqlsrv_fetch_array($SQL_OrdenServicioCliente);

    //Sucursal
    $SQL_Sucursal = Seleccionar('uvw_tbl_SeriesSucursalesAlmacenes', 'IdSucursal, DeSucursal', "IdSeries='" . $row['IdSeries'] . "'");

    //Almacenes
    $SQL_Almacen = Seleccionar('uvw_tbl_SeriesSucursalesAlmacenes', 'WhsCode, WhsName', "IdSeries='" . $row['IdSeries'] . "'", 'WhsName');

    //Anexos
    $SQL_Anexo = Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos', '*', "AbsEntry='" . $row['IdAnexo'] . "'");

}

// Se eliminaron las dimensiones en esta parte, SMM 23/08/2022

//Condiciones de pago
$SQL_CondicionPago = Seleccionar('uvw_Sap_tbl_CondicionPago', '*', '', 'IdCondicionPago');

//Datos de dimensiones del usuario actual
$SQL_DatosEmpleados = Seleccionar('uvw_tbl_Usuarios', 'CentroCosto1,CentroCosto2,CentroCosto3', "ID_Usuario='" . $_SESSION['CodUser'] . "'");
$row_DatosEmpleados = sqlsrv_fetch_array($SQL_DatosEmpleados);

//Estado documento
$SQL_EstadoDoc = Seleccionar('uvw_tbl_EstadoDocSAP', '*');

//Estado autorizacion
$SQL_EstadoAuth = Seleccionar('uvw_Sap_tbl_EstadosAuth', '*');

//Empleado de ventas
$SQL_EmpleadosVentas = Seleccionar('uvw_Sap_tbl_EmpleadosVentas', '*', '', 'DE_EmpVentas');

//Series de documento
$ParamSerie = array(
    "'" . $_SESSION['CodUser'] . "'",
    "'13'",
);
$SQL_Series = EjecutarSP('sp_ConsultarSeriesDocumentos', $ParamSerie);

// Lista de precios, 25/02/2022
$SQL_ListaPrecios = Seleccionar('uvw_Sap_tbl_ListaPrecios', '*');

// Proyectos, SMM 04/03/2022
$SQL_Proyecto = Seleccionar('uvw_Sap_tbl_Proyectos', '*', '', 'DeProyecto');

// Stiven Muñoz Murillo, 02/03/2022
$row_encode = isset($row) ? json_encode($row) : "";
$cadena = isset($row) ? "JSON.parse('$row_encode'.replace(/\\n|\\r/g, ''))" : "'Not Found'";
// echo "<script> console.log($cadena); </script>";
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Factura de venta | <?php echo NOMBRE_PORTAL; ?></title>
<?php
if (isset($_GET['a']) && $_GET['a'] == base64_encode("OK_FactVentAdd")) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'La Factura de venta ha sido creada exitosamente.',
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
                text: `" . LSiqmlObs($msg_error) . "`,
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

	var dim1= document.getElementById("Dim1").value;
	var dim2= document.getElementById("Dim2").value;
	var dim3= document.getElementById("Dim3").value;

	var posicion_x;
	var posicion_y;
	posicion_x=(screen.width/2)-(1200/2);
	posicion_y=(screen.height/2)-(500/2);

	let idlistaprecio = document.getElementById("IdListaPrecio").value; // SMM, 25/02/2022

	let proyecto = document.getElementById("PrjCode").value; // SMM, 04/05/2022
	let empleado = document.getElementById("EmpleadoVentas").value; // SMM, 04/05/2022

	if(dato!=""){
		if((cardcode!="")&&(almacen!="")&&(idlistaprecio!="")){
			remote=open('buscar_articulo.php?dato='+dato+'&prjcode='+proyecto+'&empventas='+empleado+'&cardcode='+cardcode+'&whscode='+almacen+'&idlistaprecio='+idlistaprecio+'&doctype=<?php if ($edit == 0) {echo "15";} else {echo "16";}?>&idfacturaventa=<?php if ($edit == 1) {echo base64_encode($row['ID_FacturaVenta']);} else {echo "0";}?>&evento=<?php if ($edit == 1) {echo base64_encode($row['IdEvento']);} else {echo "0";}?>&tipodoc=2&dim1='+dim1+'&dim2='+dim2+'&dim3='+dim3,'remote',"width=1200,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
			remote.focus();
		}else{
			Swal.fire({
				title: "¡Advertencia!",
				text: "Debe seleccionar un cliente, un almacén y una lista de precios",
				type: "warning",
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
<?php if ($edit == 1) {?>
function MostrarRet(){
	var posicion_x;
	var posicion_y;
	posicion_x=(screen.width/2)-(1200/2);
	posicion_y=(screen.height/2)-(500/2);
	remote=open('ajx_retenciones_factura.php?id=<?php echo base64_encode($IdFactura); ?>','remote',"width=1200,height=300,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
	remote.focus();
}
<?php }?>
</script>
<script type="text/javascript">
	$(document).ready(function() { // Cargar los combos dependiendo de otros
		$("#CardCode").change(function() {
			$('.ibox-content').toggleClass('sk-loading', true);

			var frame=document.getElementById('DataGrid');
			var carcode=document.getElementById('CardCode').value;
			var almacen=document.getElementById('Almacen').value;

			// Cargar contactos del cliente.
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=2&id="+carcode,
				success: function(response){
					$('#ContactoCliente').html(response).fadeIn();
				},
				error: function(error) {
					console.error(error.responseText);
					$('.ibox-content').toggleClass('sk-loading', false);
				}
			});

			// Lista de precio en el SN, SMM 20/01/2022
			let cardcode = carcode;

			// SMM, 05/05/2022
			document.cookie = `cardcode=${cardcode}`;

			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data: {
					type: 45,
					id: cardcode
				},
				dataType:'json',
				success: function(data){
					console.log("Line 534", data);

					document.getElementById('IdListaPrecio').value=data.IdListaPrecio;
					$('#IdListaPrecio').trigger('change');

					document.getElementById('Exento').value=data.SujetoImpuesto; // SMM, 23/04/2022
				},
				error: function(error) {
					// console.error("Linea 592", error.responseText);
					console.log("El cliente no tiene IdListaPrecio");

					$('.ibox-content').toggleClass('sk-loading', false);
				}
			});

			<?php if ($edit == 0 && $sw_error == 0 && $dt_LS == 0 && $dt_OV == 0) { // Limpiar carrito detalle. ?>
			$.ajax({
				type: "POST",
				url: "includes/procedimientos.php?type=7&objtype=13&cardcode="+carcode
			});

			// Recargar sucursales.
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=3&tdir=S&id="+carcode,
				success: function(response){
					$('#SucursalDestino').html(response).fadeIn();
					$('#SucursalDestino').trigger('change');
				},
				error: function(error) {
					console.error(error.responseText);
					$('.ibox-content').toggleClass('sk-loading', false);
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=3&tdir=B&id="+carcode,
				success: function(response){
					$('#SucursalFacturacion').html(response).fadeIn();
					$('#SucursalFacturacion').trigger('change');
				},
				error: function(error) {
					console.error(error.responseText);
					$('.ibox-content').toggleClass('sk-loading', false);
				}
			});
			<?php }?>

			<?php if ($edit == 0 && $sw_error == 0 && $dt_OV == 0) { // Recargar condición de pago. ?>
				$.ajax({
					type: "POST",
					url: "ajx_cbo_select.php?type=7&id="+carcode,
					success: function(response){
						$('#CondicionPago').html(response).fadeIn();
					},
					error: function(error) {
						console.error(error.responseText);
						$('.ibox-content').toggleClass('sk-loading', false);
					}
				});
				// En la llamada no hay condición de pago, por lo que se carga desde el cliente.
			<?php }?>

			<?php if ($edit == 0) {?>
				if(carcode!=""){
					frame.src="detalle_factura_venta.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+carcode;
				}else{
					frame.src="detalle_factura_venta.php";
				}
			<?php } else {?>
				if(carcode!=""){
					frame.src="detalle_factura_venta.php?id=<?php echo base64_encode($row['ID_FacturaVenta']); ?>&evento=<?php echo base64_encode($row['IdEvento']); ?>&docentry=<?php echo base64_encode($row['DocEntry']); ?>&type=2";
				}else{
					frame.src="detalle_factura_venta.php";
				}
			<?php }?>

			$('.ibox-content').toggleClass('sk-loading', false);
		});

		$("#SucursalDestino").change(function() {
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
					// console.error("Line 637", error.responseText);
					console.log("El cliente no tiene Dirección Destino");

					$('.ibox-content').toggleClass('sk-loading', false);
				}
			});
		});

		$("#SucursalFacturacion").change(function() {
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
					// console.error("Line 657", error.responseText);
					console.log("El cliente no tiene Dirección de Facturación");

					$('.ibox-content').toggleClass('sk-loading', false);
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
}?> // SMM, 23/08/2022

		$("#Serie").change(function() {
			$('.ibox-content').toggleClass('sk-loading',true);

			console.log("SDim Message,\n<?php echo $console_Msg; ?>"); // SMM, 23/08/2022

			var Serie=document.getElementById('Serie').value;
			var SDim = document.getElementById('<?php echo $SDimPO; ?>').value; // SMM, 23/08/2022

			$.ajax({
				type: "POST",
				url: `ajx_cbo_select.php?type=19&id=${Serie}&SDim=${SDim}`, // SMM, 23/08/2022
				success: function(response){
					$('#<?php echo $SDimPO; ?>').html(response).fadeIn(); // SMM, 23/08/2022
					$('#<?php echo $SDimPO; ?>').trigger('change'); // SMM, 23/08/2022

					$('.ibox-content').toggleClass('sk-loading',false);
				},
				error: function(error) {
					console.log("Line 903", error.responseText);

					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});

		// Actualización del almacen en las líneas.
		$("#Almacen").change(function() {
			var frame=document.getElementById('DataGrid');
			if(document.getElementById('Almacen').value!=""&&document.getElementById('CardCode').value!=""&&document.getElementById('TotalItems').value!="0"){
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
							url: "registro.php?P=36&doctype=9&type=1&name=WhsCode&value="+Base64.encode(document.getElementById('Almacen').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_factura_venta.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=9&type=2&name=WhsCode&value="+Base64.encode(document.getElementById('Almacen').value)+"&line=0&id=<?php echo $row['ID_FacturaVenta']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_factura_venta.php?id=<?php echo base64_encode($row['ID_FacturaVenta']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }?>
					}
				});
			}
		});
		// Actualizar almacen, llega hasta aquí.

		// Actualización de la dimensión 1 (Marca) en las líneas.
		$("#Dim1").change(function() {
			var frame=document.getElementById('DataGrid');
			if(document.getElementById('Dim1').value!=""&&document.getElementById('CardCode').value!=""&&document.getElementById('TotalItems').value!="0"){
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
							url: "registro.php?P=36&doctype=9&type=1&name=OcrCode&value="+Base64.encode(document.getElementById('Dim1').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_factura_venta.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=9&type=2&name=OcrCode&value="+Base64.encode(document.getElementById('Dim1').value)+"&line=0&id=<?php echo $row['ID_FacturaVenta']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_factura_venta.php?id=<?php echo base64_encode($row['ID_FacturaVenta']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }?>
					}
				});
			}
		});
		// Actualizar dimensión 1, llega hasta aquí.

		// Actualización de la dimensión 2 (Ciudad) en las líneas.
		$("#Dim2").change(function() {
			$('.ibox-content').toggleClass('sk-loading',true);
			var Dim2=document.getElementById('Dim2').value;
			var Serie=document.getElementById('Serie').value;
			var frame=document.getElementById('DataGrid');

			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=20&id="+Dim2+"&serie="+Serie+"&tdoc=13",
				success: function(response){
					$('#Almacen').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					//$('#Almacen').trigger('change');
				}
			});

			if(document.getElementById('Dim2').value!=""&&document.getElementById('CardCode').value!=""&&document.getElementById('TotalItems').value!="0"){
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
							url: "registro.php?P=36&doctype=9&type=1&name=OcrCode2&value="+Base64.encode(document.getElementById('Dim2').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_factura_venta.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=9&type=2&name=OcrCode2&value="+Base64.encode(document.getElementById('Dim2').value)+"&line=0&id=<?php echo $row['ID_FacturaVenta']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_factura_venta.php?id=<?php echo base64_encode($row['ID_FacturaVenta']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }?>
					}
				});
			}
		});
		// Actualizar dimensión 2, llega hasta aquí.

		// Actualización de la dimensión 3 (Placa) en las líneas.
		$("#Dim3").change(function() {
			// $('.ibox-content').toggleClass('sk-loading',true);

			var Dim3=document.getElementById('Dim3').value;
			var Serie=document.getElementById('Serie').value;
			var frame=document.getElementById('DataGrid');


			if(Dim3!=""&&document.getElementById('CardCode').value!=""&&document.getElementById('TotalItems').value!="0"){
				console.log("Line 819");
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
							url: "registro.php?P=36&doctype=9&type=1&name=OcrCode3&value="+Base64.encode(Dim3)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_factura_venta.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							},
							error: function(error) {
								console.error("Line 838", error.responseText);
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
							console.log("Line 838");
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=9&type=2&name=OcrCode3&value="+Base64.encode(Dim3)+"&line=0&id=<?php echo $row['ID_FacturaVenta']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_factura_venta.php?id=<?php echo base64_encode($row['ID_FacturaVenta']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
								$('.ibox-content').toggleClass('sk-loading',false);
							},
							error: function(error) {
								console.error("Line 846", error.responseText);
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }?>
					}
				});
			}
		});
		// Actualizar dimensión 3, llega hasta aquí.

		// Actualización del vendedor en las líneas, SMM 23/02/2022
		$("#EmpleadoVentas").change(function() {
			var frame=document.getElementById('DataGrid');
			if(document.getElementById('EmpleadoVentas').value!=""&&document.getElementById('CardCode').value!=""&&document.getElementById('TotalItems').value!="0"){
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
							type: "GET", // "EmpVentas" es el nombre que tiene el registro en el detalle.
							url: "registro.php?P=36&doctype=9&type=1&name=EmpVentas&value="+Base64.encode(document.getElementById('EmpleadoVentas').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_factura_venta.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET", // "EmpVentas" es el nombre que tiene el registro en el detalle.
							url: "registro.php?P=36&doctype=9&type=2&name=EmpVentas&value="+Base64.encode(document.getElementById('EmpleadoVentas').value)+"&line=0&id=<?php echo $row['ID_FacturaVenta']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_factura_venta.php?id=<?php echo base64_encode($row['ID_FacturaVenta']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }?>
					}
				});
			}
		});
		// Actualizar vendedor, llega hasta aquí.

		// Actualización del proyecto en las líneas, SMM 23/02/2022
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
							url: "registro.php?P=36&doctype=9&type=1&name=PrjCode&value="+Base64.encode(document.getElementById('PrjCode').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_factura_venta.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=9&type=2&name=PrjCode&value="+Base64.encode(document.getElementById('PrjCode').value)+"&line=0&id=<?php echo $row['ID_FacturaVenta']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_factura_venta.php?id=<?php echo base64_encode($row['ID_FacturaVenta']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }?>
					}
				});
			}
		});
		// Actualizar proyecto, llega hasta aquí.
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
                    <h2>Factura de venta</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Ventas - Clientes</a>
                        </li>
                        <li class="active">
                            <strong>Factura de venta</strong>
                        </li>
                    </ol>
                </div>
            </div>

         <div class="wrapper wrapper-content">
			<!-- SMM, 02/08/2022 -->
			<?php include_once 'md_consultar_llamadas_servicios.php';?>

		 <?php if ($edit == 1) {?>
		 <div class="ibox-content">
			<?php include "includes/spinner.php";?>
			 <div class="row">
				<div class="col-lg-12 form-horizontal">
					<div class="form-group">
						<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-plus-square"></i> Acciones</h3></label>
					</div>
					<div class="form-group">
						<div class="col-lg-6">
							<!-- SMM, 06/10/2022 -->
							<div class="btn-group">
								<button data-toggle="dropdown" class="btn btn-outline btn-success dropdown-toggle"><i class="fa fa-download"></i> Descargar formato <i class="fa fa-caret-down"></i></button>
								<ul class="dropdown-menu">
									<?php $SQL_Formato = Seleccionar('uvw_tbl_FormatosSAP', '*', "ID_Objeto=13 AND (IdFormato='" . $row['IdSeries'] . "' OR DeSeries IS NULL) AND VerEnDocumento='Y' AND (EsBorrador='N' OR EsBorrador IS NULL)");?>
									<?php while ($row_Formato = sqlsrv_fetch_array($SQL_Formato)) {?>
										<li>
											<a class="dropdown-item" target="_blank" href="sapdownload.php?id=<?php echo base64_encode('15'); ?>&type=<?php echo base64_encode('2'); ?>&DocKey=<?php echo base64_encode($row['DocEntry']); ?>&ObType=<?php echo base64_encode($row_Formato['ID_Objeto']); ?>&IdFrm=<?php echo base64_encode($row_Formato['IdFormato']); ?>"><?php echo $row_Formato['NombreVisualizar']; ?></a>
										</li>
									<?php }?>
								</ul>
							</div>
							<!-- Hasta aquí, 06/10/2022 -->
						
							<a href="#" class="btn btn-outline btn-info" onClick="VerMapaRel('<?php echo base64_encode($row['DocEntry']); ?>','<?php echo base64_encode('13'); ?>');"><i class="fa fa-sitemap"></i> Mapa de relaciones</a>
							
							<?php if ($row['URLVisorPublico'] != "") {?>
								<a href="<?php echo $row['URLVisorPublico']; ?>" target="_blank" class="btn btn-outline btn-primary"><i class="fa fa-external-link"></i> Ver Fact. Eléctronica</a>
							<?php }?>
						</div>
						<div class="col-lg-6">
							<?php if ($row['DocDestinoDocEntry'] != "") {?>
								<a href="nota_credito.php?id=<?php echo base64_encode($row['DocDestinoDocEntry']); ?>&id_portal=<?php echo base64_encode($row['DocDestinoIdPortal']); ?>&tl=1" target="_blank" class="btn btn-outline btn-primary pull-right">Ir a documento destino <i class="fa fa-mail-forward"></i></a>
							<?php }?>
							<?php if ($row['DocBaseDocEntry'] != "") {?>
								<a href="entrega_venta.php?id=<?php echo base64_encode($row['DocBaseDocEntry']); ?>&id_portal=<?php echo base64_encode($row['DocBaseIdPortal']); ?>&tl=1" target="_blank" class="btn btn-outline btn-primary pull-right"><i class="fa fa-mail-reply"></i> Ir a documento base</a>
							<?php }?>
							<?php if ($row['Cod_Estado'] == 'O') {?>
								<button type="button" onClick="javascript:location.href='actividad.php?dt_DM=1&Cardcode=<?php echo base64_encode($row['CardCode']); ?>&Contacto=<?php echo base64_encode($row['CodigoContacto']); ?>&Sucursal=<?php echo base64_encode($row['SucursalDestino']); ?>&Direccion=<?php echo base64_encode($row['DireccionDestino']); ?>&DM_type=<?php echo base64_encode('13'); ?>&DM=<?php echo base64_encode($row['DocEntry']); ?>&dt_LS=1&LS=<?php echo base64_encode($row['ID_LlamadaServicio']); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('factura_venta.php'); ?>'" class="alkin btn btn-outline btn-primary pull-right"><i class="fa fa-plus-circle"></i> Agregar actividad</button>
						<?php }?>
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
              <form action="factura_venta.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="CrearFacturaVenta">
				  <?php
$_GET['obj'] = "13";
include_once 'md_frm_campos_adicionales.php';
?>
				<div class="form-group">
					<label class="col-md-8 col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-user"></i> Información de cliente</h3></label>
					<label class="col-md-4 col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-calendar"></i> Fechas de documento</h3></label>
				</div>
				<div class="col-lg-8">
					<div class="form-group">
						<label class="col-lg-1 control-label"><i onClick="ConsultarDatosCliente();" title="Consultar cliente" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Cliente <span class="text-danger">*</span></label>
						<div class="col-lg-9">
							<input name="CardCode" type="hidden" id="CardCode" value="<?php if (($edit == 1) || ($sw_error == 1)) {echo $row['CardCode'];} elseif ($dt_LS == 1 || $dt_OV == 1) {echo $row_Cliente['CodigoCliente'];}?>">

							<input name="CardName" type="text" required="required" class="form-control" id="CardName" placeholder="Digite para buscar..." value="<?php if (($edit == 1) || ($sw_error == 1)) {echo $row['NombreCliente'];} elseif ($dt_LS == 1 || $dt_OV == 1) {echo $row_Cliente['NombreCliente'];}?>"
							<?php if ($dt_LS == 1 || $dt_OV == 1 || $edit == 1) {echo "readonly";}?>>
						</div>

						<div class="col-lg-2">
							<input type="hidden" id="Exento" name="Exento" class="form-control" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Contacto <span class="text-danger">*</span></label>
						<div class="col-lg-5">
							<select name="ContactoCliente" class="form-control" id="ContactoCliente" required <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
									<option value="">Seleccione...</option>
							<?php
if ($edit == 1 || $sw_error == 1) {
    while ($row_ContactoCliente = sqlsrv_fetch_array($SQL_ContactoCliente)) {?>
										<option value="<?php echo $row_ContactoCliente['CodigoContacto']; ?>" <?php if ((isset($row['CodigoContacto'])) && (strcmp($row_ContactoCliente['CodigoContacto'], $row['CodigoContacto']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_ContactoCliente['ID_Contacto']; ?></option>
						  	<?php }
}?>
							</select>
						</div>

						<!-- Inicio, Lista Precios SN -->
						<label class="col-lg-1 control-label">Lista Precios <!--span class="text-danger">*</span--></label>
						<div class="col-lg-5">
							<select name="IdListaPrecio" class="form-control" id="IdListaPrecio" <?php if (!PermitirFuncion(418)) {echo "disabled='disabled'";}?>>
								<?php while ($row_ListaPrecio = sqlsrv_fetch_array($SQL_ListaPrecios)) {?>
								<option value="<?php echo $row_ListaPrecio['IdListaPrecio']; ?>"
								<?php if (isset($row['IdListaPrecio']) && (strcmp($row_ListaPrecio['IdListaPrecio'], $row['IdListaPrecio']) == 0)) {echo "selected=\"selected\"";}?>>
									<?php echo $row_ListaPrecio['DeListaPrecio']; ?>
								</option>
								<?php }?>
							</select>
						</div>
						<!-- Fin, Lista Precios SN -->
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Sucursal destino <span class="text-danger">*</span></label>
						<div class="col-lg-5">
							<select name="SucursalDestino" class="form-control select2" id="SucursalDestino" required="required" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							      <option value="">Seleccione...</option>
							  <?php if ($edit == 1 || $sw_error == 1 || $dt_LS == 1 || $dt_OV == 1) {?>
								  <optgroup label='Dirección de destino'></optgroup>
								  <?php while ($row_SucursalDestino = sqlsrv_fetch_array($SQL_SucursalDestino)) {?>
									<option value="<?php echo $row_SucursalDestino['NombreSucursal']; ?>" <?php if ((isset($row['SucursalDestino'])) && (strcmp($row_SucursalDestino['NombreSucursal'], $row['SucursalDestino']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['Sucursal']) && (strcmp($row_SucursalDestino['NombreSucursal'], base64_decode($_GET['Sucursal'])) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['Sucursal']) && (strcmp(LSiqmlObs($row_SucursalDestino['NombreSucursal']), base64_decode($_GET['Sucursal'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_SucursalDestino['NombreSucursal']; ?></option>
							  	<?php }?>
							  <?php }?>
							</select>
						</div>
						<label class="col-lg-1 control-label">Sucursal facturación <span class="text-danger">*</span></label>
						<div class="col-lg-5">
							<select name="SucursalFacturacion" class="form-control select2" id="SucursalFacturacion" required="required" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							      <option value="">Seleccione...</option>
							  <?php if ($edit == 1 || $sw_error == 1 || $dt_LS == 1 || $dt_OV == 1) {?>
								  <optgroup label='Dirección de facturas'></optgroup>
								  <?php while ($row_SucursalFacturacion = sqlsrv_fetch_array($SQL_SucursalFacturacion)) {?>
									<option value="<?php echo $row_SucursalFacturacion['NombreSucursal']; ?>" <?php if ((isset($row['SucursalFacturacion'])) && (strcmp($row_SucursalFacturacion['NombreSucursal'], $row['SucursalFacturacion']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['SucursalFact']) && (strcmp($row_SucursalFacturacion['NombreSucursal'], base64_decode($_GET['SucursalFact'])) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['SucursalFact']) && (strcmp(LSiqmlObs($row_SucursalFacturacion['NombreSucursal']), base64_decode($_GET['SucursalFact'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_SucursalFacturacion['NombreSucursal']; ?></option>
							      <?php }?>
							  <?php }?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Dirección destino</label>
						<div class="col-lg-5">
							<input type="text" class="form-control" name="DireccionDestino" id="DireccionDestino" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['DireccionDestino'];} elseif ($dt_LS == 1 || isset($_GET['Direccion'])) {echo base64_decode($_GET['Direccion']);}?>" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "readonly";}?>>
						</div>
						<label class="col-lg-1 control-label">Dirección facturación</label>
						<div class="col-lg-5">
							<input type="text" class="form-control" name="DireccionFacturacion" id="DireccionFacturacion" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['DireccionFacturacion'];}?>" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "readonly";}?>>
						</div>
					</div>
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
				</div>
				<div class="col-lg-4">
					<div class="form-group">
						<label class="col-lg-5">Número</label>
						<div class="col-lg-7">
							<input type="text" name="DocNum" id="DocNum" class="form-control" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['DocNum'];}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-5">Fecha de contabilización <span class="text-danger">*</span></label>
						<div class="col-lg-7 input-group date">
							 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="DocDate" type="text" required="required" class="form-control" id="DocDate" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['DocDate'];} else {echo date('Y-m-d');}?>" readonly="readonly" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-5">Fecha de vencimiento <span class="text-danger">*</span></label>
						<div class="col-lg-7 input-group date">
							 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="DocDueDate" type="text" required="required" class="form-control" id="DocDueDate" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['DocDueDate'];} else {echo date('Y-m-d');}?>" readonly="readonly" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-5">Fecha del documento <span class="text-danger">*</span></label>
						<div class="col-lg-7 input-group date">
							 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="TaxDate" type="text" required="required" class="form-control" id="TaxDate" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['TaxDate'];} else {echo date('Y-m-d');}?>" readonly="readonly" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-5">Estado <span class="text-danger">*</span></label>
						<div class="col-lg-7">
							<select name="EstadoDoc" class="form-control" id="EstadoDoc" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							  <?php while ($row_EstadoDoc = sqlsrv_fetch_array($SQL_EstadoDoc)) {?>
									<option value="<?php echo $row_EstadoDoc['Cod_Estado']; ?>" <?php if (($edit == 1) && (isset($row['Cod_Estado'])) && (strcmp($row_EstadoDoc['Cod_Estado'], $row['Cod_Estado']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_EstadoDoc['NombreEstado']; ?></option>
							  <?php }?>
							</select>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Datos de la factura</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Serie <span class="text-danger">*</span></label>
					<div class="col-lg-3">
                    	<select name="Serie" class="form-control" id="Serie" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
                          <?php while ($row_Series = sqlsrv_fetch_array($SQL_Series)) {?>
								<option value="<?php echo $row_Series['IdSeries']; ?>" <?php if (($edit == 1 || $sw_error == 1) && (isset($row['IdSeries'])) && (strcmp($row_Series['IdSeries'], $row['IdSeries']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries']; ?></option>
						  <?php }?>
						</select>
               	  	</div>
					<label class="col-lg-1 control-label">Referencia</label>
					<div class="col-lg-3">
                    	<input type="text" name="Referencia" id="Referencia" class="form-control" value="<?php if ($edit == 1) {echo $row['NumAtCard'];}?>" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "readonly";}?>>
               	  	</div>
					<label class="col-lg-1 control-label">Condición de pago</label>
					<div class="col-lg-3">
						<select name="CondicionPago" class="form-control" id="CondicionPago" required="required" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
						  <?php while ($row_CondicionPago = sqlsrv_fetch_array($SQL_CondicionPago)) {?>
								<option value="<?php echo $row_CondicionPago['IdCondicionPago']; ?>" <?php if ($edit == 1 || $sw_error == 1) {if (($row['IdCondicionPago'] != "") && (strcmp($row_CondicionPago['IdCondicionPago'], $row['IdCondicionPago']) == 0)) {echo "selected=\"selected\"";}}?>><?php echo $row_CondicionPago['NombreCondicion']; ?></option>
						  <?php }?>
						</select>
				  	</div>
				</div>

				<!-- Dimensiones dinámicas, SMM 23/08/2022 -->
				<div class="form-group">
					<?php foreach ($array_Dimensiones as &$dim) {?>
						<label class="col-lg-1 control-label"><?php echo $dim['DescPortalOne']; ?> <span class="text-danger">*</span></label>
						<div class="col-lg-3">
							<select name="<?php echo $dim['IdPortalOne'] ?>" id="<?php echo $dim['IdPortalOne'] ?>" class="form-control select2" required="required" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
								<option value="">Seleccione...</option>

							<?php $SQL_Dim = Seleccionar('uvw_Sap_tbl_DimensionesReparto', '*', 'DimCode=' . $dim['DimCode']);?>
							<?php while ($row_Dim = sqlsrv_fetch_array($SQL_Dim)) {?>
								<?php $DimCode = intval($dim['DimCode']);?>
								<?php $OcrId = ($DimCode == 1) ? "" : $DimCode;?>

								<option value="<?php echo $row_Dim['OcrCode']; ?>"
								<?php if ((isset($row["OcrCode$OcrId"]) && ($row["OcrCode$OcrId"] != "")) && (strcmp($row_Dim['OcrCode'], $row["OcrCode$OcrId"]) == 0)) {echo "selected=\"selected\"";} elseif (($edit == 0) && (isset($_GET['LMT']) && !isset($_GET[strval($dim['IdPortalOne'])])) && ($row_DatosEmpleados["CentroCosto$DimCode"] != "") && (strcmp($row_DatosEmpleados["CentroCosto$DimCode"], $row_Dim['OcrCode']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET[strval($dim['IdPortalOne'])]) && (strcmp($row_Dim['OcrCode'], base64_decode($_GET[strval($dim['IdPortalOne'])])) == 0)) {echo "selected=\"selected\"";}?>>
									<?php echo $row_Dim['OcrName']; ?>
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
						<select name="Almacen" class="form-control" id="Almacen" required="required" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
						  <?php if ($edit == 1) {
    while ($row_Almacen = sqlsrv_fetch_array($SQL_Almacen)) {?>
									<option value="<?php echo $row_Almacen['WhsCode']; ?>" <?php if ($dt_LS == 1) {if (strcmp($row_Almacen['WhsCode'], $row_LMT['WhsCode']) == 0) {echo "selected=\"selected\"";}} elseif (($edit == 1) && (isset($row['WhsCode'])) && (strcmp($row_Almacen['WhsCode'], $row['WhsCode']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Almacen['WhsName']; ?></option>
						  <?php }
}?>
						</select>
					</div>
					<label class="col-lg-1 control-label">Autorización</label>
					<div class="col-lg-3">
                    	<select name="Autorizacion" class="form-control" id="Autorizacion" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
                          <?php while ($row_EstadoAuth = sqlsrv_fetch_array($SQL_EstadoAuth)) {?>
								<option value="<?php echo $row_EstadoAuth['IdAuth']; ?>" <?php if (($edit == 1) && (isset($row['AuthPortal'])) && (strcmp($row_EstadoAuth['IdAuth'], $row['AuthPortal']) == 0)) {echo "selected=\"selected\"";} elseif (($edit == 0) && ($row_EstadoAuth['IdAuth'] == 'N')) {echo "selected=\"selected\"";}?>><?php echo $row_EstadoAuth['DeAuth']; ?></option>
						  <?php }?>
						</select>
               	  	</div>

					<!-- Inicio, Proyecto -->
					<label class="col-lg-1 control-label">Proyecto <span class="text-danger">*</span></label>
					<div class="col-lg-3">
						<select id="PrjCode" name="PrjCode" class="form-control select2" required="required" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
								<option value="">(NINGUNO)</option>
							<?php while ($row_Proyecto = sqlsrv_fetch_array($SQL_Proyecto)) {?>
								<option value="<?php echo $row_Proyecto['IdProyecto']; ?>" <?php if ((isset($row['PrjCode'])) && (strcmp($row_Proyecto['IdProyecto'], $row['PrjCode']) == 0)) {echo "selected=\"selected\"";} elseif ((isset($_GET['Proyecto'])) && (strcmp($row_Proyecto['IdProyecto'], base64_decode($_GET['Proyecto'])) == 0)) {echo "selected=\"selected\"";}?>>
									<?php echo $row_Proyecto['DeProyecto']; ?>
								</option>
							<?php }?>
						</select>
					</div>
					<!-- Fin, Proyecto -->
				</div>
				<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Contenido de la factura</h3></label>
				</div>
				<!-- Inicio, descuento aseguradora -->
				<?php if ($edit == 0) {?>
					<div class="form-group">
						<label class="col-lg-1 control-label">Valor descuento</label>
						<div class="col-lg-4">
							<input type="text" id="ValorDescuento" name="ValorDescuento" class="form-control" placeholder="Digite el valor del descuento de aseguradora..." onBlur="this.value=number_format(this.value,2);" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" autocomplete="off" <?php if ($edit == 1) {echo "readonly";}?>>
						</div>
						<label class="col-lg-1 control-label">% descuento</label>
						<div class="col-lg-1">
							<input type="text" id="PorcentajeDescuento" name="PorcentajeDescuento" value="0.0000" class="form-control" readonly>
						</div>
						<div class="col-lg-2">
							<button class="btn btn-success" type="button" id="AplicarDescuento">Aplicar descuento de aseguradora</button>
						</div>
					</div>
				<?php }?>
				<!-- Fin, descuento aseguradora -->
				<div class="form-group">
					<label class="col-lg-1 control-label">Buscar articulo</label>
					<div class="col-lg-4">
                    	<input name="BuscarItem" id="BuscarItem" type="text" class="form-control" placeholder="Escriba para buscar..." <?php if ($edit == 0) {?>onBlur="javascript:BuscarArticulo(this.value);"<?php }?> <?php if ($edit == 1) {echo "readonly";}?>>
               	  	</div>

					<!-- SMM, 04/05/2022 -->
					<?php $filtro_consulta = "LineNum NoLinea, ItemCode IdArticulo, ItemName DeArticulo, Quantity Cantidad,
					UnitMsr UnidadMedida, WhsCode IdAlmacen, WhsName DeAlmacen, OnHand Stock, Price Precio, PriceTax PrecioConIva,
					TarifaIVA, VatSum IVATotalLinea, DiscPrcnt PorcenDescuento, LineTotal TotalLinea, CDU_AreasControladas AreasControladas,
					OcrCode IdDimension1, OcrCode2 IdDimension2, OcrCode3 IdDimension3, OcrCode4 IdDimension4, OcrCode5 IdDimension5, PrjCode IdProyecto";?>

					<?php $cookie_cardcode = 0;?>
					<?php if ($edit == 1) {?>
						<?php $ID_FacturaVenta = $row['ID_FacturaVenta'];?>
						<?php $Evento = $row['IdEvento'];?>
						<?php $consulta_detalle = "SELECT $filtro_consulta FROM uvw_tbl_FacturaVentaDetalle WHERE ID_FacturaVenta='$ID_FacturaVenta' AND IdEvento='$Evento' AND Metodo <> 3";?>
					<?php } else {?>
						<?php $Usuario = $_SESSION['CodUser'];?>
						<?php $cookie_cardcode = 1;?>
						<?php $consulta_detalle = "SELECT $filtro_consulta FROM uvw_tbl_FacturaVentaDetalleCarrito WHERE Usuario='$Usuario'";?>
					<?php }?>

					<div class="col-lg-1 pull-right">
						<a href="exportar_excel.php?exp=20&cookie_cardcode=<?php echo $cookie_cardcode; ?>&Cons=<?php echo base64_encode($consulta_detalle); ?>">
							<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
						</a>
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
							<iframe id="DataGrid" name="DataGrid" style="border: 0;" width="100%" height="300" src="<?php if ($edit == 0 && $sw_error == 0) {echo "detalle_factura_venta.php";} elseif ($edit == 0 && $sw_error == 1) {echo "detalle_factura_venta.php?id=0&type=1&usr=" . $_SESSION['CodUser'] . "&cardcode=" . $row['CardCode'];} else {echo "detalle_factura_venta.php?id=" . base64_encode($row['ID_FacturaVenta']) . "&evento=" . base64_encode($row['IdEvento']) . "&docentry=" . base64_encode($row['DocEntry']) . "&type=2&status=" . base64_encode($row['Cod_Estado']);}?>"></iframe>
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
}?>
								<div class="row">
									<form action="upload.php" class="dropzone" id="dropzoneForm" name="dropzoneForm">
										<?php if ($sw_error == 0) {LimpiarDirTemp();}?>
										<div class="fallback">
											<input name="File" id="File" type="file" form="dropzoneForm" />
										</div>
									 </form>
								</div>
							</div>
				   		</div>
					</div>
				</div>
			   <form id="frm" action="" class="form-horizontal">
				<div class="form-group">&nbsp;</div>
				<div class="col-lg-8">
					<div class="form-group">
						<label class="col-lg-2">Empleado de ventas <span class="text-danger">*</span></label>
						<div class="col-lg-5">
							<select name="EmpleadoVentas" class="form-control" id="EmpleadoVentas" form="CrearFacturaVenta" required="required" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							  <?php while ($row_EmpleadosVentas = sqlsrv_fetch_array($SQL_EmpleadosVentas)) {?>
									<option value="<?php echo $row_EmpleadosVentas['ID_EmpVentas']; ?>" <?php if ($edit == 0 && $sw_error == 0) {if (isset($_GET['Empleado']) && (strcmp($row_EmpleadosVentas['ID_EmpVentas'], base64_decode($_GET['Empleado'])) == 0)) {echo "selected=\"selected\"";} elseif (($_SESSION['CodigoEmpVentas'] != "") && (!isset($_GET['Empleado'])) && (strcmp($row_EmpleadosVentas['ID_EmpVentas'], $_SESSION['CodigoEmpVentas']) == 0)) {echo "selected=\"selected\"";}} elseif ($edit == 1 || $sw_error == 1) {if (($row['SlpCode'] != "") && (strcmp($row_EmpleadosVentas['ID_EmpVentas'], $row['SlpCode']) == 0)) {echo "selected=\"selected\"";}}?>><?php echo $row_EmpleadosVentas['DE_EmpVentas']; ?></option>
							  <?php }?>
							</select>
						</div>


					</div>

					<div class="form-group">
						<label class="col-lg-2">Comentarios</label>
						<div class="col-lg-10">
							<textarea name="Comentarios" form="CrearFacturaVenta" rows="4" class="form-control" id="Comentarios" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "readonly";}?>><?php if ($edit == 1) {echo $row['Comentarios'];} elseif (isset($_GET['Comentarios'])) {echo base64_decode($_GET['Comentarios']);}?></textarea>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-2">Información adicional</label>
						<div class="col-lg-10">
							<button class="btn btn-success" type="button" id="DatoAdicionales" onClick="VerCamposAdi();"><i class="fa fa-list"></i> Ver campos adicionales</button>
						</div>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Subtotal</strong></label>
						<div class="col-lg-5">
							<input type="text" name="SubTotal" form="CrearFacturaVenta" id="SubTotal" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['SubTotal'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Descuentos</strong></label>
						<div class="col-lg-5">
							<input type="text" name="Descuentos" form="CrearFacturaVenta" id="Descuentos" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['DiscSum'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">IVA</strong></label>
						<div class="col-lg-5">
							<input type="text" name="Impuestos" form="CrearFacturaVenta" id="Impuestos" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['VatSum'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Redondeo</strong></label>
						<div class="col-lg-5">
							<input type="text" name="Redondeo" form="CrearOrdenVenta" id="Redondeo" class="form-control" style="text-align: right; font-weight: bold;" value="0.00" readonly>
						</div>
					</div>
					<?php if ($edit == 1) {?>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right"><?php if ($row['WTSum'] > 0) {?><a href="#" onClick="MostrarRet();">Retenciones <i class="fa fa-external-link"></i></a><?php } else {?>Retenciones<?php }?></strong></label>
						<div class="col-lg-5">
							<input type="text" name="Retenciones" form="CrearFacturaVenta" id="Retenciones" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['WTSum'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
					<?php }?>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Total</strong></label>
						<div class="col-lg-5">
							<input type="text" name="TotalFactura" form="CrearFacturaVenta" id="TotalFactura" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['DocTotal'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-9">
						<?php if ($edit == 0 && PermitirFuncion(411)) {?>
							<button class="btn btn-primary" type="submit" form="CrearFacturaVenta" id="Crear"><i class="fa fa-check"></i> Crear Factura de venta</button>
						<?php } elseif (($edit == 1) && ($row['Cod_Estado'] == "O" && PermitirFuncion(411))) {?>
							<button class="btn btn-warning" type="submit" form="CrearFacturaVenta" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar Factura de venta</button>
						<?php }?>
						<?php
//$EliminaMsg=array("&a=".base64_encode("OK_OVenAdd"),"&a=".base64_encode("OK_OVenUpd"),"&a=".base64_encode("OK_ActAdd"),"&a=".base64_encode("OK_UpdAdd"));//Eliminar mensajes

/*if(isset($_GET['return'])){
$_GET['return']=str_replace($EliminaMsg,"",base64_decode($_GET['return']));
}*/
if (isset($_GET['return'])) {
    $return = base64_decode($_GET['pag']) . "?" . base64_decode($_GET['return']);
} elseif (isset($_POST['return'])) {
    $return = base64_decode($_POST['return']);
} else {
    $return = "factura_venta.php?";
}
$return = QuitarParametrosURL($return, array("a"));
?>
						<a href="<?php echo $return; ?>" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
					</div>

<!-- Dimensiones dinámicas, SMM 23/08/2022 -->
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
<!-- Hasta aquí, 23/08/2022 -->

<!-- Aquí va el copiar a otros documentos, 23/08/2022 -->

				</div>
				<input type="hidden" form="CrearFacturaVenta" id="P" name="P" value="55" />
				<input type="hidden" form="CrearFacturaVenta" id="IdFacturaVenta" name="IdFacturaVenta" value="<?php if ($edit == 1) {echo base64_encode($row['ID_FacturaVenta']);}?>" />
				<input type="hidden" form="CrearFacturaVenta" id="IdEvento" name="IdEvento" value="<?php if ($edit == 1) {echo base64_encode($IdEvento);}?>" />
				<input type="hidden" form="CrearFacturaVenta" id="d_LS" name="d_LS" value="<?php echo $dt_LS; ?>" />
				<input type="hidden" form="CrearFacturaVenta" id="tl" name="tl" value="<?php echo $edit; ?>" />
				<input type="hidden" form="CrearFacturaVenta" id="swError" name="swError" value="<?php echo $sw_error; ?>" />
				<input type="hidden" form="CrearFacturaVenta" id="return" name="return" value="<?php echo base64_encode($return); ?>" />
				<!-- input type="hidden" form="CrearFacturaVenta" id="PrjCode" name="PrjCode" value="<?php // if ($edit == 1) {echo $row['PrjCode'];}?>" / -->
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
		// Inicio, calcular descuento.
		$("#ValorDescuento").on("change", function() {
			let SubTotal = parseFloat($("#SubTotal").val().replace(/,/g, ''));
			let ValorDescuento = parseFloat($("#ValorDescuento").val().replace(/,/g, ''));

			$("#PorcentajeDescuento").val((100*(ValorDescuento/SubTotal)).toFixed(4));
		});
		// Fin, calcular descuento.

		// Inicio, aplicar descuento.
		$("#AplicarDescuento").on("click", function() {
			let frame=document.getElementById('DataGrid');

			let DiscPrcnt=document.getElementById('PorcentajeDescuento').value;
			let CardCode=document.getElementById('CardCode').value;
			let TotalItems=document.getElementById('TotalItems').value;

			if(DiscPrcnt!="" && CardCode!="" && TotalItems!="0"){
				Swal.fire({
					title: "¿Desea actualizar las líneas?",
					icon: "question",
					showCancelButton: true,
					confirmButtonText: "Si, confirmo",
					cancelButtonText: "No"
				}).then((result) => {
					if (result.isConfirmed) {
						$.ajax({
							type: "GET", // custom=1&
							url: "registro.php?P=36&doctype=9&type=1&name=DiscPrcnt&value="+Base64.encode(DiscPrcnt)+"&line=0&cardcode="+CardCode+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_factura_venta.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+CardCode;
							}
						});
					}
				});
			} else {
				Swal.fire({
					title: "Debe ingresar un cliente y al menos un artículo.",
					icon: "warning"
				});
			}
		});
		// Fin, aplicar descuento.

		 $("#CrearFacturaVenta").validate({
			 submitHandler: function(form){
				if(Validar()){
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

		 <?php
if ($edit == 1) {?>
		 $('#Serie option:not(:selected)').attr('disabled',true);
		 $('#Sucursal option:not(:selected)').attr('disabled',true);
		 $('#Almacen option:not(:selected)').attr('disabled',true);
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
		 <?php if (PermitirFuncion(419) || ($edit == 0)) {?>
		 $("#CardName").easyAutocomplete(options);
	 	 <?php }?>
		<?php if ($dt_LS == 1 || $dt_OV == 1) {?>
		 $('#CardCode').trigger('change');
		 //$('#Almacen').trigger('change');
		<?php }?>
		<?php if ($edit == 0) {?>
		 $('#Serie').trigger('change');
	 	<?php }?>

		$('#CardCode').trigger('change'); // SMM, 24/02/2022

		// SMM, 11/05/2022
		<?php if (isset($_GET['SucursalFact'])) {?>
			$('#SucursalFacturacion').trigger('change');
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
				url: "dm_actividades.php?id=<?php if ($edit == 1) {echo base64_encode($row['DocEntry']);}?>&objtype=13",
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

	var TotalItems = document.getElementById("TotalItems");

	//Validar si fue actualizado por otro usuario
	/*$.ajax({
		url:"ajx_buscar_datos_json.php",
		data:{type:15,
			  docentry:'<?php if ($edit == 1) {echo base64_encode($row['DocEntry']);}?>',
			  objtype:13,
			  date:'<?php echo FormatoFecha(date('Y-m-d'), date('H:i:s')); ?>'},
		dataType:'json',
		success: function(data){
			if(data.Result==1){
				result=true;
			}else{
				result=false;
				swal({
					title: '¡Lo sentimos!',
					text: 'Este documento ya fue actualizado por otro usuario. Debe recargar la página para volver a cargar los datos.',
					type: 'error'
				});
			}
		}
	 });*/

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