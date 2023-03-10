<?php require_once "includes/conexion.php";
PermitirAcceso(422);

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

$IdMotivo = "";
$motivoAutorizacion = "";
$debug_Condiciones = false;

$success = 1; // Confirmación de autorización (1 - Autorizado / 0 - NO Autorizado), SMM 16/08/2022
$mensajeProceso = ""; // Mensaje proceso, mensaje de salida del procedimiento almacenado.

$msg_error = ""; //Mensaje del error
$IdEntrega = 0;
$IdPortal = 0; //Id del portal para las entregas que fueron creadas en el portal, para eliminar el registro antes de cargar al editar
$NameFirma = "";

// Procesos de autorización, SMM 19/08/2022
$SQL_Procesos = Seleccionar("uvw_tbl_Autorizaciones_Procesos", "*", "Estado = 'Y' AND IdTipoDocumento = 15");

if (isset($_GET['id']) && ($_GET['id'] != "")) { //ID de la Entrega de venta (DocEntry)
    $IdEntrega = base64_decode($_GET['id']);
}

if (isset($_GET['id_portal']) && ($_GET['id_portal'] != "")) { //Id del portal de venta (ID interno)
    $IdPortal = base64_decode($_GET['id_portal']);
}

if (isset($_POST['IdEntregaVenta']) && ($_POST['IdEntregaVenta'] != "")) { //Tambien el Id interno, pero lo envío cuando mando el formulario
    $IdEntregaVenta = base64_decode($_POST['IdEntregaVenta']);
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

// Consulta decisión de autorización en la edición de documentos.
// Stiven Muñoz Murillo, 16/08/2022
if ($edit == 1) {
    $DocEntry = "'" . $IdEntrega . "'";
    $EsBorrador = (true) ? "DocumentoBorrador" : "Documento";
    $SQL_Autorizaciones = Seleccionar("uvw_Sap_tbl_Autorizaciones", "*", "IdTipoDocumento = 15 AND DocEntry$EsBorrador = $DocEntry");
    $row_Autorizaciones = sqlsrv_fetch_array($SQL_Autorizaciones);

    // SMM, 19/08/2022
    $SQL_Procesos = Seleccionar("uvw_tbl_Autorizaciones_Procesos", "*", "IdTipoDocumento = 15");
}
// Hasta aquí, 16/08/2022

// Inicio, Grabar Entrega de Venta
if (isset($_POST['P']) && ($_POST['P'] != "")) {
    //*** Carpeta temporal ***
    $i = 0; //Archivos
    $RutaAttachSAP = ObtenerDirAttach();
    $dir = CrearObtenerDirTemp();
    $dir_firma = CrearObtenerDirTempFirma();
    $dir_new = CrearObtenerDirAnx("entregaventa");

    if ((isset($_POST['SigRecibe'])) && ($_POST['SigRecibe'] != "")) {
        $NombreFileFirma = base64_decode($_POST['SigRecibe']);
        $Nombre_Archivo = "Sig_" . $NombreFileFirma;
        if (!copy($dir_firma . $NombreFileFirma, $dir . $Nombre_Archivo)) {
            $sw_error = 1;
            $msg_error = "No se pudo mover la firma";
        }
    }

    $route = opendir($dir);
    //$directorio = opendir("."); //ruta actual
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
            $IdEntregaVenta = base64_decode($_POST['IdEntregaVenta']);
            $IdEvento = base64_decode($_POST['IdEvento']);
            $Type = 2;
            if (!PermitirFuncion(403)) { //Permiso para autorizar Entrega de venta
                $_POST['Autorizacion'] = 'P'; //Si no tengo el permiso, la Entrega queda pendiente
            }
        } else { //Crear
            $IdEntregaVenta = "NULL";
            $IdEvento = "0";
            $Type = 1;
        }
        $ParametrosCabEntregaVenta = array(
            $IdEntregaVenta,
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
            "'" . str_replace(',', '', $_POST['TotalEntrega']) . "'",
            "'" . $_POST['SucursalFacturacion'] . "'",
            "'" . LSiqmlObs($_POST['DireccionFacturacion']) . "'",
            "'" . $_POST['SucursalDestino'] . "'",
            "'" . LSiqmlObs($_POST['DireccionDestino']) . "'",
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
            // SMM, 16/08/2022
            "'" . ($_POST['IdMotivoAutorizacion'] ?? "") . "'",
            "'" . ($_POST['ComentariosAutor'] ?? "") . "'",
            "'" . ($_POST['MensajeProceso'] ?? "") . "'",
        );

        $SQL_CabeceraEntregaVenta = EjecutarSP('sp_tbl_EntregaVenta_Borrador', $ParametrosCabEntregaVenta, $_POST['P']);
        if ($SQL_CabeceraEntregaVenta) {
            if ($Type == 1) {
                $row_CabeceraEntregaVenta = sqlsrv_fetch_array($SQL_CabeceraEntregaVenta);
                $IdEntregaVenta = $row_CabeceraEntregaVenta[0];
                $IdEvento = $row_CabeceraEntregaVenta[1];

                // Comprobar procesos de autorización en la creación, SMM 16/08/2022
                while ($row_Proceso = sqlsrv_fetch_array($SQL_Procesos)) {
                    $ids_perfiles = ($row_Proceso['Perfiles'] != "") ? explode(";", $row_Proceso['Perfiles']) : [];

                    if (in_array($_SESSION['Perfil'], $ids_perfiles) || (count($ids_perfiles) == 0)) {
                        $sql = $row_Proceso['Condiciones'] ?? '';

                        $sql = str_replace("[IdDocumento]", $IdEntregaVenta, $sql);
                        $sql = str_replace("[IdEvento]", $IdEvento, $sql);

                        $stmt = sqlsrv_query($conexion, $sql);

                        $data = "";
                        if ($stmt === false) {
                            $data = json_encode(sqlsrv_errors(), JSON_PRETTY_PRINT);
                        } else {
                            $records = array();
                            while ($obj = sqlsrv_fetch_object($stmt)) {
                                if (isset($obj->success) && ($obj->success == 0)) {
                                    $success = 0;
                                    $IdMotivo = $obj->IdMotivo;
                                    $mensajeProceso = $obj->mensaje;
                                }

                                array_push($records, $obj);
                            }
                            $data = json_encode($records, JSON_PRETTY_PRINT);
                        }

                        if ($debug_Condiciones) {
                            $dataString = "JSON.stringify($data, null, '\t')";
                            echo "<script> console.log($dataString); </script>";
                        }
                    }
                }

                // Consultar el motivo de autorización según el id, SMM 20/08/2022
                $SQL_Motivos = Seleccionar("uvw_tbl_Autorizaciones_Motivos", "*", "IdMotivoAutorizacion = '$IdMotivo'");
                $row_MotivoAutorizacion = sqlsrv_fetch_array($SQL_Motivos);
                $motivoAutorizacion = $row_MotivoAutorizacion['MotivoAutorizacion'];
                // Hasta aquí, 16/08/2022

            } else {
                $IdEntregaVenta = base64_decode($_POST['IdEntregaVenta']); //Lo coloco otra vez solo para saber que tiene ese valor
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
                            "'15'",
                            "'" . $IdEntregaVenta . "'",
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

            /*sqlsrv_close($conexion);
            if($_POST['tl']==0){//Creando Entrega
            header('Location:'.base64_decode($_POST['return']).'&a='.base64_encode("OK_EVenAdd"));
            }else{//Actualizando Entrega
            header('Location:'.base64_decode($_POST['return']).'&a='.base64_encode("OK_EVenUpd"));
            }*/

            // Verificar que el documento cumpla las Condiciones o este Pendiente de Autorización.
            // Stiven Muñoz Murillo, 16/08/2022
            if (($success == 1) || ($_POST['Autorizacion'] == "P")) {
                $success = 1;

                //Enviar datos al WebServices
                try {
                    $Parametros = array(
                        'id_documento' => intval($IdEntregaVenta),
                        'id_evento' => intval($IdEvento),
                    );
                    // Esta línea cambia en el borrador.
                    $Metodo = ($_POST["Definitivo"] == "1") ? "EntregasVentas/CrearBorrador_A_Definitivo" : "EntregasVentas/Borrador";
                    $Resultado = EnviarWebServiceSAP($Metodo, $Parametros, true, true);

                    if ($Resultado->Success == 0) {
                        //InsertarLog(1, 0, 'Error al generar el informe');
                        //throw new Exception('Error al generar el informe. Error de WebServices');
                        $sw_error = 1;
                        $msg_error = $Resultado->Mensaje;
                    } else {

                        // No se necesita el mensaje de confirmación de creación del borrador.

                        if ($_POST['tl'] == 0) { //Creando Entrega
                            //Consultar ID creado para cargar el documento
                            $SQL_ConsID = Seleccionar('uvw_Sap_tbl_EntregasVentas', 'ID_EntregaVenta', "IdDocPortal='" . $IdEntregaVenta . "'");
                            $row_ConsID = sqlsrv_fetch_array($SQL_ConsID);
                            sqlsrv_close($conexion);
                            header('Location:entrega_venta_borrador.php?id=' . base64_encode($row_ConsID['ID_EntregaVenta']) . '&id_portal=' . base64_encode($IdEntregaVenta) . '&tl=1&a=' . base64_encode("OK_EVenAdd"));
                        } else { //Actualizando Entrega
                            sqlsrv_close($conexion);

                            header('Location:entrega_venta_borrador.php?a=' . base64_encode("OK_BorradorAdd"));
                            // header('Location:entrega_venta_borrador.php?id=' . base64_encode($row_ConsID['ID_EntregaVenta']) . '&id_portal=' . base64_encode($row_ConsID['ID_EntregaVenta']) . '&tl=1&a=' . base64_encode("OK_EVenUpd"));
                            // header('Location:' . base64_decode($_POST['return']) . '&a=' . base64_encode("OK_EVenUpd"));
                        }

                    }
                } catch (Exception $e) {
                    echo 'Excepcion capturada: ', $e->getMessage(), "\n";
                }
            } else {
                $sw_error = 1;
                $msg_error = "Este documento necesita autorización.";
            }
            // Hasta aquí 16/08/2022

        } else {
            $sw_error = 1;
            $msg_error = "Ha ocurrido un error al crear la Entrega de venta";
        }
    } catch (Exception $e) {
        echo 'Excepcion capturada: ', $e->getMessage(), "\n";
    }
}
// Fin, Grabar Entrega de Venta

// Inicio, Verificar que viene de una Orden de Ventas
if (isset($_GET['dt_OV']) && ($_GET['dt_OV']) == 1) {
    $dt_OV = 1;

    $ParametrosCopiarOrdenToEntrega = array(
        "'" . base64_decode($_GET['OV']) . "'",
        "'" . base64_decode($_GET['Evento']) . "'",
        "'" . base64_decode($_GET['Almacen']) . "'",
        "'" . base64_decode($_GET['Cardcode']) . "'",
        "'" . $_SESSION['CodUser'] . "'",
    );

    $SQL_CopiarOrdenToEntrega = EjecutarSP('sp_tbl_OrdenVentaDet_To_EntregaVentaDet', $ParametrosCopiarOrdenToEntrega);
    if (!$SQL_CopiarOrdenToEntrega) {
        echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Ha ocurrido un error!',
				text: 'No se pudo copiar la Orden en Entrega de venta.',
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

    //Orden de servicio
    $SQL_OrdenServicioCliente = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_LlamadaServicio='" . base64_decode($_GET['LS']) . "'");
}
// Fin, Verificar que viene de una Orden de Ventas

// SMM, 07/03/2022
if (isset($_GET['dt_ET']) && ($_GET['dt_ET']) == 1) { // Verificar que viene de una Entrega de ventas (Duplicar)
    $dt_OV = 1;

    $ParametrosCopiarEntregaToEntrega = array(
        "'" . base64_decode($_GET['ET']) . "'",
        "'" . base64_decode($_GET['Evento']) . "'",
        "'" . base64_decode($_GET['Almacen']) . "'",
        "'" . base64_decode($_GET['Cardcode']) . "'",
        "'" . $_SESSION['CodUser'] . "'",
    );

    $SQL_CopiarEntregaToEntrega = EjecutarSP('sp_tbl_EntregaVentaDet_To_EntregaVentaDet', $ParametrosCopiarEntregaToEntrega);
    if (!$SQL_CopiarEntregaToEntrega) {
        echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Ha ocurrido un error!',
				text: 'No se pudo duplicar la Entrega de venta.',
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

    //Orden de servicio
    $SQL_OrdenServicioCliente = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_LlamadaServicio='" . base64_decode($_GET['LS']) . "'");
}

// Inicio, Verificar que viene de una Llamada de servicio (Datos Llamada servicio).
if (isset($_GET['dt_LS']) && ($_GET['dt_LS']) == 1) {
    $dt_LS = 1;

    if ($dt_OV == 0) {
        if (!isset($_GET['LMT']) && $_GET['ItemCode'] != "" && isset($_GET['ItemCode'])) {
            //Consultar datos de la LMT
            $SQL_LMT = Seleccionar('uvw_Sap_tbl_ArticulosLlamadas', '*', "ItemCode='" . base64_decode($_GET['ItemCode']) . "'");
            $row_LMT = sqlsrv_fetch_array($SQL_LMT);

            //Cargar la LMT
            $ParametrosAddLMT = array(
                "'" . base64_decode($_GET['ItemCode']) . "'",
                "'" . $row_LMT['WhsCode'] . "'",
                "'" . base64_decode($_GET['Cardcode']) . "'",
                "'" . $_SESSION['CodUser'] . "'",
            );
            $SQL_AddLMT = EjecutarSP('sp_CargarLMT_EntregaVentaDetalleCarrito', $ParametrosAddLMT);
        } else {
            // echo "Hola Mundo";
            Eliminar('tbl_EntregaVentaDetalleCarrito', "Usuario='" . $_SESSION['CodUser'] . "' AND CardCode='" . base64_decode($_GET['Cardcode']) . "'");
        }
    }

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreCliente');
    $row_Cliente = sqlsrv_fetch_array($SQL_Cliente);

    //Contacto cliente
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreContacto');

    //Sucursales, SMM 06/05/2022
    $SQL_SucursalDestino = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "' AND TipoDireccion='S'", 'NombreSucursal');
    $SQL_SucursalFacturacion = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "' AND TipoDireccion='B'", 'NombreSucursal');

    //Orden de servicio
    $SQL_OrdenServicioCliente = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_LlamadaServicio='" . base64_decode($_GET['LS']) . "'");
}
// Fin, Verificar que viene de una LS

if ($edit == 1 && $sw_error == 0) {

    $ParametrosLimpiar = array(
        "'" . $IdEntrega . "'",
        "'" . $IdPortal . "'",
        "'" . $_SESSION['CodUser'] . "'",
    );
    $LimpiarEntrega = EjecutarSP('sp_EliminarDatosEntregaVenta_Borrador', $ParametrosLimpiar);

    $SQL_IdEvento = sqlsrv_fetch_array($LimpiarEntrega);
    $IdEvento = $SQL_IdEvento[0];

    //Entrega de venta
    $Cons = "Select * From uvw_tbl_EntregaVenta_Borrador Where DocEntry='" . $IdEntrega . "' AND IdEvento='" . $IdEvento . "'";
    $SQL = sqlsrv_query($conexion, $Cons);
    $row = sqlsrv_fetch_array($SQL);

    // echo "<br>" . $Cons . "<br>";

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreCliente');

    //Sucursales, SMM 06/05/2022
    $SQL_SucursalDestino = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' AND TipoDireccion='S'", 'NombreSucursal');
    $SQL_SucursalFacturacion = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' AND TipoDireccion='B'", 'NombreSucursal');

    //Contacto cliente
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreContacto');

    //Orden de servicio
    $SQL_OrdenServicioCliente = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_CodigoCliente='" . $row['CardCode'] . "' OR ID_LlamadaServicio='" . $row['ID_LlamadaServicio'] . "' AND IdEstadoLlamada<>'-1'");

    //Sucursal
    $SQL_Sucursal = SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes', 'IdSucursal, DeSucursal', "IdSeries='" . $row['IdSeries'] . "'", "IdSucursal, DeSucursal");

    //Almacenes
    $SQL_Almacen = SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes', 'WhsCode, WhsName', "IdSeries='" . $row['IdSeries'] . "'", "WhsCode, WhsName", 'WhsName');

    //Anexos
    $SQL_Anexo = Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos', '*', "AbsEntry='" . $row['IdAnexo'] . "'");

}

if ($sw_error == 1) {

    //Entrega de venta
    $Cons = "Select * From uvw_tbl_EntregaVenta_Borrador Where ID_EntregaVenta='" . $IdEntregaVenta . "' AND IdEvento='" . $IdEvento . "'";
    $SQL = sqlsrv_query($conexion, $Cons);
    $row = sqlsrv_fetch_array($SQL);

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreCliente');

    //Sucursales, SMM 06/05/2022
    $SQL_SucursalDestino = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' AND TipoDireccion='S'", 'NombreSucursal');
    $SQL_SucursalFacturacion = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' AND TipoDireccion='B'", 'NombreSucursal');

    //Contacto cliente
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreContacto');

    //Orden de servicio
    $SQL_OrdenServicioCliente = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_CodigoCliente='" . $row['CardCode'] . "' OR ID_LlamadaServicio='" . $row['ID_LlamadaServicio'] . "' AND IdEstadoLlamada<>'-1'");

    //Sucursal
    $SQL_Sucursal = SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes', 'IdSucursal, DeSucursal', "IdSeries='" . $row['IdSeries'] . "'", "IdSucursal, DeSucursal");

    //Almacenes
    $SQL_Almacen = SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes', 'WhsCode, WhsName', "IdSeries='" . $row['IdSeries'] . "'", "WhsCode, WhsName", 'WhsName');

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
    "'15'",
);
$SQL_Series = EjecutarSP('sp_ConsultarSeriesDocumentos', $ParamSerie);

// Lista de precios, 24/02/2022
$SQL_ListaPrecios = Seleccionar('uvw_Sap_tbl_ListaPrecios', '*');

// Proyectos, SMM 04/03/2022
$SQL_Proyecto = Seleccionar('uvw_Sap_tbl_Proyectos', '*', '', 'DeProyecto');

// Consultar el motivo de autorización según el id, SMM 20/08/2022
if (isset($row['IdMotivoAutorizacion']) && ($row['IdMotivoAutorizacion'] != "") && ($IdMotivo == "")) {
    $IdMotivo = $row['IdMotivoAutorizacion'];
    $SQL_Motivos = Seleccionar("uvw_tbl_Autorizaciones_Motivos", "*", "IdMotivoAutorizacion = '$IdMotivo'");
    $row_MotivoAutorizacion = sqlsrv_fetch_array($SQL_Motivos);
    $motivoAutorizacion = $row_MotivoAutorizacion['MotivoAutorizacion'] ?? "";
}

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
<title>Entrega de venta borrador | <?php echo NOMBRE_PORTAL; ?></title>
<?php
if (isset($_GET['a']) && $_GET['a'] == base64_encode("OK_EVenAdd")) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'La Entrega de venta ha sido creada exitosamente.',
				icon: 'success'
			});
		});
		</script>";
}
if (isset($_GET['a']) && $_GET['a'] == base64_encode("OK_EVenUpd")) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'La Entrega de venta ha sido actualizada exitosamente.',
				icon: 'success'
			});
		});
		</script>";
}

// SMM, 16/08/2022
if (isset($sw_error) && ($sw_error == 1)) {
    $error_title = ($success == 0) ? "Advertencia" : "Ha ocurrido un error";

    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡$error_title!',
                text: `" . LSiqmlObs($msg_error) . "`,
                icon: 'warning'
            });
		});
		</script>";
}

// SMM, 16/08/2022
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_BorradorAdd"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'Se ha actualizado el documento en borrador.',
                icon: 'success'
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


	/**
	* Stiven Muñoz Murillo
	* 16/08/2022
	 */
	.bootstrap-maxlength {
		background-color: black;
		z-index: 9999999;
	}
	.swal2-container {
		z-index: 9999999 !important;
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
			remote=open('buscar_articulo.php?dato='+dato+'&prjcode='+proyecto+'&empventas='+empleado+'&cardcode='+cardcode+'&whscode='+almacen+'&idlistaprecio='+idlistaprecio+'&doctype=<?php if ($edit == 0) {echo "5";} else {echo "6";}?>&identregaventa=<?php if ($edit == 1) {echo base64_encode($row['ID_EntregaVenta']);} else {echo "0";}?>&evento=<?php if ($edit == 1) {echo base64_encode($row['IdEvento']);} else {echo "0";}?>&tipodoc=2&dim1='+dim1+'&dim2='+dim2+'&dim3='+dim3,'remote',"width=1200,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
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

// SMM, 16/08/2022
function verAutorizacion() {
	$('#modalAUT').modal('show');
}
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

			// Lista de precio en el SN y Exento, SMM 20/01/2022
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
					console.error(error.responseText);
					$('.ibox-content').toggleClass('sk-loading', false);
				}
			});

			<?php if ($edit == 0 && $sw_error == 0 && $dt_LS == 0 && $dt_OV == 0) { // Limpiar carrito detalle. ?>
			$.ajax({
				type: "POST",
				url: "includes/procedimientos.php?type=7&objtype=15&cardcode="+carcode
			});

			// Recargar lista de llamadas de servicio.
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=6&id="+carcode,
				success: function(response){
					$('#OrdenServicioCliente').html(response).fadeIn();
					$('#OrdenServicioCliente').trigger('change');
				},
				error: function(error) {
					console.error(error.responseText);
					$('.ibox-content').toggleClass('sk-loading', false);
				}
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
					frame.src="detalle_entrega_venta_borrador.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+carcode;
				}else{
					frame.src="detalle_entrega_venta_borrador.php";
				}
			<?php } else {?>
				if(carcode!=""){
					// SMM, 16/08/2022
					frame.src="detalle_entrega_venta_borrador.php?autoriza=1&id=<?php echo base64_encode($row['ID_EntregaVenta']); ?>&evento=<?php echo base64_encode($row['IdEvento']); ?>&docentry=<?php echo base64_encode($row['DocEntry']); ?>&type=2";
				}else{
					frame.src="detalle_entrega_venta_borrador.php";
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
					console.error("Line 637", error.responseText);
					$('.ibox-content').toggleClass('sk-loading',false);
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
					console.error("Line 658", error.responseText);
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
							url: "registro.php?P=36&doctype=3&type=1&name=WhsCode&value="+Base64.encode(document.getElementById('Almacen').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_entrega_venta_borrador.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=3&type=2&name=WhsCode&value="+Base64.encode(document.getElementById('Almacen').value)+"&line=0&id=<?php echo $row['ID_EntregaVenta']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_entrega_venta_borrador.php?id=<?php echo base64_encode($row['ID_EntregaVenta']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
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
							url: "registro.php?P=36&doctype=3&type=1&name=OcrCode&value="+Base64.encode(document.getElementById('Dim1').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_entrega_venta_borrador.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=3&type=2&name=OcrCode&value="+Base64.encode(document.getElementById('Dim1').value)+"&line=0&id=<?php echo $row['ID_EntregaVenta']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_entrega_venta_borrador.php?id=<?php echo base64_encode($row['ID_EntregaVenta']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
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
			var frame=document.getElementById('DataGrid');

			var Dim2=document.getElementById('Dim2').value;
			var Serie=document.getElementById('Serie').value;

			// Stiven Muñoz Murillo, 04/02/2022
			// let WhsCode = document.getElementById('Almacen').value;

			$.ajax({
				type: "POST",
				url: `ajx_cbo_select.php?type=20&id=${Dim2}&serie=${Serie}&tdoc=15&WhsCode=<?php echo isset($_GET['Almacen']) ? base64_decode($_GET['Almacen']) : ($row['WhsCode'] ?? ""); ?>`, // Modificación almacen, SMM/01/05/2022
				success: function(response){
					$('#Almacen').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					// $('#Almacen').trigger('change');
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
							url: "registro.php?P=36&doctype=3&type=1&name=OcrCode2&value="+Base64.encode(document.getElementById('Dim2').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_entrega_venta_borrador.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=3&type=2&name=OcrCode2&value="+Base64.encode(document.getElementById('Dim2').value)+"&line=0&id=<?php echo $row['ID_EntregaVenta']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_entrega_venta_borrador.php?id=<?php echo base64_encode($row['ID_EntregaVenta']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
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
			var frame=document.getElementById('DataGrid');

			var Dim3=document.getElementById('Dim3').value;
			var Serie=document.getElementById('Serie').value;

			let CardCode = document.getElementById('CardCode').value;
			let TotalItems = document.getElementById('TotalItems').value;

			if(Dim3!=""&&CardCode!=""&&TotalItems!="0") {
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
							url: "registro.php?P=36&doctype=3&type=1&name=OcrCode3&value="+Base64.encode(Dim3)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_entrega_venta_borrador.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							},
							error: function(error) {
								console.error(error.responseText);
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=3&type=2&name=OcrCode3&value="+Base64.encode(Dim3)+"&line=0&id=<?php echo $row['ID_EntregaVenta']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_entrega_venta_borrador.php?id=<?php echo base64_encode($row['ID_EntregaVenta']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
								$('.ibox-content').toggleClass('sk-loading',false);
							},
							error: function(error) {
								console.error(error.responseText);
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }?>
					}
				});
			} else {
				console.log("No se cumple la siguiente condición en la dimensión 3");
				console.log(`Dim3==${Dim3}`);
				console.log(`CardCode==${CardCode}`);
				console.log(`TotalItems==${TotalItems}`);
				$('.ibox-content').toggleClass('sk-loading',false);
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
							url: "registro.php?P=36&doctype=3&type=1&name=EmpVentas&value="+Base64.encode(document.getElementById('EmpleadoVentas').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_entrega_venta_borrador.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET", // "EmpVentas" es el nombre que tiene el registro en el detalle.
							url: "registro.php?P=36&doctype=3&type=2&name=EmpVentas&value="+Base64.encode(document.getElementById('EmpleadoVentas').value)+"&line=0&id=<?php echo $row['ID_EntregaVenta']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_entrega_venta_borrador.php?id=<?php echo base64_encode($row['ID_EntregaVenta']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
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
							url: "registro.php?P=36&doctype=3&type=1&name=PrjCode&value="+Base64.encode(document.getElementById('PrjCode').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_entrega_venta_borrador.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=3&type=2&name=PrjCode&value="+Base64.encode(document.getElementById('PrjCode').value)+"&line=0&id=<?php echo $row['ID_EntregaVenta']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_entrega_venta_borrador.php?id=<?php echo base64_encode($row['ID_EntregaVenta']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
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
                    <h2>Entrega de venta borrador</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Ventas - Clientes</a>
                        </li>
                        <li class="active">
                            <strong>Entrega de venta borrador</strong>
                        </li>
                    </ol>
                </div>
            </div>

         <div class="wrapper wrapper-content">
			<!-- Inicio, modalSN -->
			<div class="modal inmodal fade" id="modalSN" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg" style="width: 70% !important;">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title">Cambiar Socio de Negocio en el Nuevo Documento</h4>
						</div>

						<form id="formCambiarSN">
							<div class="modal-body">
								<div class="row">
									<div class="col-lg-1"></div>
									<div class="col-lg-5">
										<label class="control-label">
											<i onClick="ConsultarDatosClienteSN();" title="Consultar cliente" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Cliente <span class="text-danger">*</span>
										</label>
										<input type="hidden" id="ClienteSN" name="ClienteSN" >
										<input type="text" class="form-control" id="NombreClienteSN" name="NombreClienteSN"  placeholder="Digite para buscar..." required="required">
									</div>
									<div class="col-lg-5">
										<label class="control-label">Contacto</label>
										<select class="form-control" id="ContactoSN" name="ContactoSN">
											<option value="">Seleccione...</option>
										</select>
									</div>
									<div class="col-lg-1"></div>
								</div>
								<br><br>
								<div class="row">
									<div class="col-lg-1"></div>
									<div class="col-lg-5">
										<label class="control-label">Sucursal</label>
										<select class="form-control" id="SucursalSN" name="SucursalSN">
											<option value="">Seleccione...</option>
										</select>
									</div>
									<div class="col-lg-5">
										<label class="control-label">Dirección</label>
										<input type="text" class="form-control" id="DireccionSN" name="DireccionSN" maxlength="100">
									</div>
									<div class="col-lg-1"></div>
								</div>
							</div>

							<div class="modal-footer">
								<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i> Aceptar</button>
								<button type="button" class="btn btn-secondary m-t-md CancelarSN" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!-- Fin, modalSN -->

			<!-- Inicio, modalAUT. SMM, 16/08/2022 -->
			<?php if (($edit == 1) || ($success == 0) || ($sw_error == 1) || $debug_Condiciones) {?>
				<div class="modal inmodal fade" id="modalAUT" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title">Autorización de documento</h4>
							</div>

							<!-- form id="formAUT" -->
								<div class="modal-body">
									<div class="ibox-content">
										<div class="form-group">
											<label class="col-lg-2">Motivo <span class="text-danger">*</span></label>
											<div class="col-lg-10">
												<input required type="hidden" form="CrearEntregaVenta" class="form-control" name="IdMotivoAutorizacion" id="IdMotivoAutorizacion" value="<?php echo $IdMotivo; ?>">
												<input readonly type="text" style="color: black; font-weight: bold;" class="form-control" id="MotivoAutorizacion" value="<?php echo $motivoAutorizacion; ?>">
											</div>
										</div>
										<br><br><br>
										<div class="form-group">
											<label class="col-lg-2">Mensaje proceso</label>
											<div class="col-lg-10">
												<textarea readonly form="CrearEntregaVenta" style="color: black; font-weight: bold;" class="form-control" name="MensajeProceso" id="MensajeProceso" type="text" maxlength="250" rows="4"><?php if ($mensajeProceso != "") {echo $mensajeProceso;} elseif ($edit == 1 || $sw_error == 1) {echo $row['ComentariosMotivo'];}?></textarea>
											</div>
										</div>
										<br><br><br>
										<br><br><br>
										<div class="form-group">
											<label class="col-lg-2">Comentarios autor <span class="text-danger">*</span></label>
											<div class="col-lg-10">
												<textarea form="CrearEntregaVenta" class="form-control required" name="ComentariosAutor" id="ComentariosAutor" type="text" maxlength="250" rows="4"><?php if ($edit == 1 || $sw_error == 1) {echo $row['ComentariosAutor'];} elseif (isset($_GET['ComentariosAutor'])) {echo base64_decode($_GET['ComentariosAutor']);}?></textarea>
											</div>
										</div>
										<br><br><br>

										<!-- Inicio, Componente Fecha y Hora -->
										<br><br><br>
										<div class="form-group">
											<div class="row">
												<label class="col-lg-6 control-label" style="text-align: left !important;">Fecha y hora decisión SAP B1</label>
											</div>
											<div class="row">
												<div class="col-lg-6 input-group date">
													<span class="input-group-addon"><i class="fa fa-calendar"></i></span><input readonly name="FechaAutorizacion" type="text" autocomplete="off" class="form-control" id="FechaAutorizacion" value="<?php if (isset($row_Autorizaciones['FechaAutorizacion_SAPB1']) && ($row_Autorizaciones['FechaAutorizacion_SAPB1']->format('Y-m-d') != "1900-01-01")) {echo $row_Autorizaciones['FechaAutorizacion_SAPB1']->format('Y-m-d');}?>" placeholder="YYYY-MM-DD">
												</div>
												<div class="col-lg-6 input-group clockpicker" data-autoclose="true">
													<input readonly name="HoraAutorizacion" id="HoraAutorizacion" type="text" autocomplete="off" class="form-control" value="<?php if (isset($row_Autorizaciones['HoraAutorizacion_SAPB1'])) {echo $row_Autorizaciones['HoraAutorizacion_SAPB1'];}?>" placeholder="hh:mm">
													<span class="input-group-addon">
														<span class="fa fa-clock-o"></span>
													</span>
												</div>
											</div>
										</div>
										<!-- Fin, Componente Fecha y Hora -->

										<br>
										<div class="form-group">
											<label class="col-lg-2">Decisión</label>
											<div class="col-lg-10">
												<?php if (isset($row_Autorizaciones['EstadoAutorizacion'])) {?>
													<input type="text" class="form-control" name="IdEstadoAutorizacion" id="IdEstadoAutorizacion" readonly
													value="<?php echo $row_Autorizaciones['EstadoAutorizacion']; ?>" style="font-weight: bold; color: white; background-color: <?php echo $row_Autorizaciones['ColorEstadoAutorizacion']; ?>;">
												<?php } else {?>
													<input type="text" class="form-control" name="IdEstadoAutorizacion" id="IdEstadoAutorizacion" readonly>
												<?php }?>
											</div>
										</div>
										<br><br><br>
										<div class="form-group">
											<label class="col-lg-2">Usuario autorizador</label>
											<div class="col-lg-10">
												<?php if (isset($row_Autorizaciones['IdUsuarioAutorizacion_SAPB1'])) {?>
													<input type="text" class="form-control" name="IdUsuarioAutorizacion" id="IdUsuarioAutorizacion" readonly
													value="<?php echo $row_Autorizaciones['NombreUsuarioAutorizacion_SAPB1']; ?>">
												<?php } else {?>
													<input type="text" class="form-control" name="IdUsuarioAutorizacion" id="IdUsuarioAutorizacion" readonly>
												<?php }?>
											</div>
										</div>
										<br><br><br>
										<div class="form-group">
											<label class="col-lg-2">Comentarios autorizador</label>
											<div class="col-lg-10">
												<textarea readonly type="text" maxlength="200" rows="4" class="form-control" name="ComentariosAutorizador" id="ComentariosAutorizador"><?php if (isset($row_Autorizaciones['ComentariosAutorizador_SAPB1'])) {echo $row_Autorizaciones['ComentariosAutorizador_SAPB1'];}?></textarea>
											</div>
										</div>
										<br><br><br><br>
									</div>
								</div>

								<div class="modal-footer">
									<button type="button" class="btn btn-success m-t-md" id="formAUT_button"><i class="fa fa-check"></i> Enviar</button>
									<button type="button" class="btn btn-warning m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
								</div>
							<!-- /form -->
						</div>
					</div>
				</div>
			<?php }?>
			<!-- Fin, modalAUT. SMM, 16/08/2022 -->


		 <?php if ($edit == 1) {?>
			<div class="row">
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Creada por</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php if ($row['CDU_UsuarioCreacion'] != "") {echo $row['CDU_UsuarioCreacion'];} else {echo "&nbsp;";}?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Fecha creación</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php echo ($row['CDU_FechaHoraCreacion'] != "") ? $row['CDU_FechaHoraCreacion']->format('Y-m-d H:i') : "&nbsp;"; ?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Actualizado por</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php if ($row['CDU_UsuarioActualizacion'] != "") {echo $row['CDU_UsuarioActualizacion'];} else {echo "&nbsp;";}?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Fecha actualización</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php echo ($row['CDU_FechaHoraActualizacion'] != "") ? $row['CDU_FechaHoraActualizacion']->format('Y-m-d H:i') : "&nbsp;"; ?></h3>
						</div>
					</div>
				</div>
			</div>
			<?php }?>
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
									<?php $SQL_Formato = Seleccionar('uvw_tbl_FormatosSAP', '*', "ID_Objeto=15 AND (IdFormato='" . $row['IdSeries'] . "' OR DeSeries IS NULL) AND VerEnDocumento='Y' AND EsBorrador='Y'");?>
									<?php while ($row_Formato = sqlsrv_fetch_array($SQL_Formato)) {?>
										<li>
											<a class="dropdown-item" target="_blank" href="sapdownload.php?id=<?php echo base64_encode('15'); ?>&type=<?php echo base64_encode('2'); ?>&DocKey=<?php echo base64_encode($row['DocEntry']); ?>&ObType=<?php echo base64_encode($row_Formato['ID_Objeto']); ?>&IdFrm=<?php echo base64_encode($row_Formato['IdFormato']); ?>"><?php echo $row_Formato['NombreVisualizar']; ?></a>
										</li>
									<?php }?>
								</ul>
							</div>
							<!-- Hasta aquí, 06/10/2022 -->
						</div>
						<div class="col-lg-6">
							<?php if ($row['DocDestinoDocEntry'] != "") {?>
								<a href="<?php if ($row['DocDestinoObjeto'] == 16) {echo "devolucion_venta";} else {echo "factura_venta";}?>.php?id=<?php echo base64_encode($row['DocDestinoDocEntry']); ?>&id_portal=<?php echo base64_encode($row['DocDestinoIdPortal']); ?>&tl=1" target="_blank" class="btn btn-outline btn-primary pull-right m-l-sm">Ir a documento destino <i class="fa fa-mail-forward"></i></a>
							<?php }?>
							<?php if ($row['DocBaseDocEntry'] != "") {?>
								<a href="orden_venta.php?id=<?php echo base64_encode($row['DocBaseDocEntry']); ?>&id_portal=<?php echo base64_encode($row['DocBaseIdPortal']); ?>&tl=1" target="_blank" class="btn btn-outline btn-primary pull-right"><i class="fa fa-mail-reply"></i> Ir a documento base</a>
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
              <form action="entrega_venta_borrador.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="CrearEntregaVenta">
				  <?php
$_GET['obj'] = "15";
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

							<!-- SMM, 16/08/2022 -->
							<input type="hidden" id="Definitivo" name="Definitivo" class="form-control" value="0" />
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
							<input type="text" class="form-control" name="DireccionDestino" id="DireccionDestino" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['DireccionDestino'];} elseif ($dt_LS == 1 || $dt_OV == 1) {echo base64_decode($_GET['Direccion']);}?>" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "readonly";}?>>
						</div>
						<label class="col-lg-1 control-label">Dirección facturación</label>
						<div class="col-lg-5">
							<input type="text" class="form-control" name="DireccionFacturacion" id="DireccionFacturacion" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['DireccionFacturacion'];}?>" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "readonly";}?>>
						</div>
					</div>
					<div class="form-group">
					<label class="col-lg-1 control-label"><?php if (($edit == 1) && ($row['ID_LlamadaServicio'] != 0)) {?><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_LlamadaServicio']); ?>&tl=1" target="_blank" title="Consultar Llamada de servicio" class="btn-xs btn-success fa fa-search"></a> <?php }?>Orden servicio</label>
				  	<div class="col-lg-11">
                    	<select name="OrdenServicioCliente" class="form-control select2" id="OrdenServicioCliente" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
                         	<option value="">(Ninguna)</option>
							<?php
if ($edit == 1 || $dt_LS == 1 || $sw_error == 1) {
    while ($row_OrdenServicioCliente = sqlsrv_fetch_array($SQL_OrdenServicioCliente)) {?>
										<option value="<?php echo $row_OrdenServicioCliente['ID_LlamadaServicio']; ?>" <?php if ((isset($row['ID_LlamadaServicio'])) && (strcmp($row_OrdenServicioCliente['ID_LlamadaServicio'], $row['ID_LlamadaServicio']) == 0)) {echo "selected=\"selected\"";} elseif ((isset($_GET['LS'])) && (strcmp($row_OrdenServicioCliente['ID_LlamadaServicio'], base64_decode($_GET['LS'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_OrdenServicioCliente['DocNum'] . " - " . $row_OrdenServicioCliente['AsuntoLlamada'] . " (" . $row_OrdenServicioCliente['DeTipoLlamada'] . ")"; ?></option>
							  <?php }
}?>
						</select>
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
						<label class="col-lg-5">Fecha de entrega/servicio <span class="text-danger">*</span></label>
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
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Datos de la Entrega</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Serie <span class="text-danger">*</span></label>
					<div class="col-lg-3">
                    	<select name="Serie" class="form-control" required="required" id="Serie" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
                        	<!-- SMM, 01/05/2022 -->
							<?php if (sqlsrv_num_rows($SQL_Series) > 1) {?>
								<option value=''>Seleccione...</option>
							<?php }?>

							<?php while ($row_Series = sqlsrv_fetch_array($SQL_Series)) {?>
								<option value="<?php echo $row_Series['IdSeries']; ?>" <?php if (($edit == 1 || $sw_error == 1) && (isset($row['IdSeries'])) && (strcmp($row_Series['IdSeries'], $row['IdSeries']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['Serie']) && (strcmp($row_Series['IdSeries'], base64_decode($_GET['Serie'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries']; ?></option>
							<?php }?>
						</select>
               	  	</div>
					<label class="col-lg-1 control-label">Referencia</label>
					<div class="col-lg-3">
                    	<input type="text" name="Referencia" id="Referencia" class="form-control" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['NumAtCard'];} elseif ($edit == 0 && isset($_GET['Referencia'])) {echo base64_decode($_GET['Referencia']);}?>" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "readonly";}?>>
               	  	</div>
					<label class="col-lg-1 control-label">Condición de pago</label>
					<div class="col-lg-3">
						<select name="CondicionPago" class="form-control" id="CondicionPago" required="required" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
						  <?php while ($row_CondicionPago = sqlsrv_fetch_array($SQL_CondicionPago)) {?>
								<option value="<?php echo $row_CondicionPago['IdCondicionPago']; ?>" <?php if ($edit == 1 || $sw_error == 1) {if (($row['IdCondicionPago'] != "") && (strcmp($row_CondicionPago['IdCondicionPago'], $row['IdCondicionPago']) == 0)) {echo "selected=\"selected\"";}} elseif ((isset($_GET['CondicionPago'])) && (strcmp($row_CondicionPago['IdCondicionPago'], base64_decode($_GET['CondicionPago'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_CondicionPago['NombreCondicion']; ?></option>
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
					<label class="col-lg-1 control-label">Almacén <span class="text-danger">*</span></label>
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

					<!-- SMM, 16/08/2022 -->
					<label class="col-lg-1 control-label">
						Autorización
						<?php if ((isset($row_Autorizaciones['IdEstadoAutorizacion']) && ($edit == 1)) || ($success == 0) || ($sw_error == 1) || $debug_Condiciones) {?>
							<i onClick="verAutorizacion();" title="Ver Autorización" style="cursor: pointer" class="btn-xs btn-success fa fa-eye"></i>
						<?php }?>
					</label>
					<div class="col-lg-3">
                    	<select name="Autorizacion" class="form-control" id="Autorizacion" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
                          <?php while ($row_EstadoAuth = sqlsrv_fetch_array($SQL_EstadoAuth)) {?>
								<option value="<?php echo $row_EstadoAuth['IdAuth']; ?>"
								<?php if (($edit == 1 || $sw_error == 1) && (isset($row['AuthPortal'])) && (strcmp($row_EstadoAuth['IdAuth'], $row['AuthPortal']) == 0)) {echo "selected=\"selected\"";} elseif (isset($row_Autorizaciones['IdEstadoAutorizacion']) && ($row_Autorizaciones['IdEstadoAutorizacion'] == 'Y') && ($row_EstadoAuth['IdAuth'] == 'Y')) {echo "selected=\"selected\"";} elseif (isset($row_Autorizaciones['IdEstadoAutorizacion']) && ($row_Autorizaciones['IdEstadoAutorizacion'] == 'W') && ($row_EstadoAuth['IdAuth'] == 'P')) {echo "selected=\"selected\"";} elseif (($edit == 0 && $sw_error == 0) && ($row_EstadoAuth['IdAuth'] == 'N')) {echo "selected=\"selected\"";}?>>
									<?php echo $row_EstadoAuth['DeAuth']; ?>
								</option>
						  <?php }?>
						</select>
               	  	</div>
					<!-- Hasta aquí, 16/08/2022 -->

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
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Contenido de la Entrega</h3></label>
				</div>
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
						<?php $ID_EntregaVenta = $row['ID_EntregaVenta'];?>
						<?php $Evento = $row['IdEvento'];?>
						<?php $consulta_detalle = "SELECT $filtro_consulta FROM uvw_tbl_EntregaVentaDetalle_Borrador WHERE ID_EntregaVenta='$ID_EntregaVenta' AND IdEvento='$Evento' AND Metodo <> 3";?>
					<?php } else {?>
						<?php $Usuario = $_SESSION['CodUser'];?>
						<?php $cookie_cardcode = 1;?>
						<?php $consulta_detalle = "SELECT $filtro_consulta FROM uvw_tbl_EntregaVentaDetalleCarrito_Borrador WHERE Usuario='$Usuario'";?>
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
							<iframe id="DataGrid" name="DataGrid" style="border: 0;" width="100%" height="300" src="<?php if ($edit == 0 && $sw_error == 0) {echo "detalle_entrega_venta_borrador.php";} elseif ($edit == 0 && $sw_error == 1) {echo "detalle_entrega_venta_borrador.php?id=0&type=1&usr=" . $_SESSION['CodUser'] . "&cardcode=" . $row['CardCode'];} else {echo "detalle_entrega_venta_borrador.php?autoriza=1&id=" . base64_encode($row['ID_EntregaVenta']) . "&evento=" . base64_encode($row['IdEvento']) . "&docentry=" . base64_encode($row['DocEntry']) . "&type=2&status=" . base64_encode($row['Cod_Estado']);}?>"></iframe>
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
        $tmp = substr($row_Anexo['NombreArchivo'], 0, 4);
        if ($tmp == "Sig_") {
            $NameFirma = $row_Anexo['NombreArchivo'];
        }
        ?>
												<li><a href="attachdownload.php?file=<?php echo base64_encode($row_Anexo['AbsEntry']); ?>&line=<?php echo base64_encode($row_Anexo['Line']); ?>" target="_blank" class="btn-link btn-xs"><i class="<?php echo $Icon; ?>"></i> <?php echo $row_Anexo['NombreArchivo']; ?></a></li>
											<?php }?>
											 </ul>
											</div>
										</div>
							<?php } else {echo "<p>Sin anexos.</p>";}
}?>
								<?php if (($edit == 0) || (($edit == 1) && ($row['Cod_Estado'] == 'O'))) {?>
								<div class="row">
									<form action="upload.php" class="dropzone" id="dropzoneForm" name="dropzoneForm">
										<?php if ($sw_error == 0) {LimpiarDirTemp();}?>
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
						<label class="col-lg-2">Empleado de ventas <span class="text-danger">*</span></label>
						<div class="col-lg-5">
							<select name="EmpleadoVentas" class="form-control" id="EmpleadoVentas" form="CrearEntregaVenta" required="required" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							   <?php while ($row_EmpleadosVentas = sqlsrv_fetch_array($SQL_EmpleadosVentas)) {?>
									<option value="<?php echo $row_EmpleadosVentas['ID_EmpVentas']; ?>" <?php if ($edit == 0 && $sw_error == 0) {if (isset($_GET['Empleado']) && (strcmp($row_EmpleadosVentas['ID_EmpVentas'], base64_decode($_GET['Empleado'])) == 0)) {echo "selected=\"selected\"";} elseif (($_SESSION['CodigoEmpVentas'] != "") && (!isset($_GET['Empleado'])) && (strcmp($row_EmpleadosVentas['ID_EmpVentas'], $_SESSION['CodigoEmpVentas']) == 0)) {echo "selected=\"selected\"";}} elseif ($edit == 1 || $sw_error == 1) {if (($row['SlpCode'] != "") && (strcmp($row_EmpleadosVentas['ID_EmpVentas'], $row['SlpCode']) == 0)) {echo "selected=\"selected\"";}}?>><?php echo $row_EmpleadosVentas['DE_EmpVentas']; ?></option>
							  <?php }?>
							</select>
						</div>


					</div>
					<div class="form-group">
						<label class="col-lg-2">Comentarios</label>
						<div class="col-lg-10">
							<textarea name="Comentarios" form="CrearEntregaVenta" rows="4" maxlength="2000" class="form-control" id="Comentarios" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "readonly";}?>><?php if ($edit == 1 || $sw_error == 1) {echo $row['Comentarios'];} elseif (isset($_GET['Comentarios'])) {echo base64_decode($_GET['Comentarios']);}?></textarea>
						</div>
					</div>
					<?php if (PermitirFuncion(417)) {?>
						<div class="form-group">
							<label class="col-lg-2">Firma quien recibe</label>
							<?php if ($edit == 1 && $NameFirma != "") {?>
							<div class="col-lg-10">
								<span class="badge badge-primary">Firmado</span>
							</div>
							<?php } else {LimpiarDirTempFirma();?>
							<div class="col-lg-5">
								<button class="btn btn-primary" type="button" id="FirmaCliente" onClick="AbrirFirma('SigRecibe');"><i class="fa fa-pencil-square-o"></i> Realizar firma</button>
								<input type="hidden" id="SigRecibe" name="SigRecibe" value="" form="CrearEntregaVenta" />
								<div id="msgInfoSigRecibe" style="display: none;" class="alert alert-info"><i class="fa fa-info-circle"></i> El documento ya ha sido firmado.</div>
							</div>
							<div class="col-lg-5">
								<img id="ImgSigRecibe" style="display: none; max-width: 100%; height: auto;" src="" alt="" />
							</div>
							<?php }?>
						</div>
					<?php }?>
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
							<input type="text" name="SubTotal" form="CrearEntregaVenta" id="SubTotal" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['SubTotal'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Descuentos</strong></label>
						<div class="col-lg-5">
							<input type="text" name="Descuentos" form="CrearEntregaVenta" id="Descuentos" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['DiscSum'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">IVA</strong></label>
						<div class="col-lg-5">
							<input type="text" name="Impuestos" form="CrearEntregaVenta" id="Impuestos" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['VatSum'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Redondeo</strong></label>
						<div class="col-lg-5">
							<input type="text" name="Redondeo" form="CrearEntregaVenta" id="Redondeo" class="form-control" style="text-align: right; font-weight: bold;" value="0.00" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Total</strong></label>
						<div class="col-lg-5">
							<input type="text" name="TotalEntrega" form="CrearEntregaVenta" id="TotalEntrega" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['DocTotal'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-9">
						<?php if ($edit == 0 && PermitirFuncion(404)) {?>
							<!-- button class="btn btn-primary" type="submit" form="CrearEntregaVenta" id="Crear"><i class="fa fa-check"></i> Crear Entrega de venta</button -->
						<?php } elseif ($edit == 1 && $row['Cod_Estado'] == "O" && PermitirFuncion(404)) {?>
							<?php if (isset($row_Autorizaciones['IdEstadoAutorizacion']) && ($row_Autorizaciones['IdEstadoAutorizacion'] == 'Y')) {?>
								<!-- SMM, 16/08/2022 -->
								<button class="btn btn-primary" type="submit" form="CrearEntregaVenta" id="Actualizar2" onclick="CambiarDefinitivo();"><i class="fa fa-check"></i> Crear Entrega de venta Definitiva</button>
							<?php }?>
							<button class="btn btn-warning" type="submit" form="CrearEntregaVenta" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar Entrega de venta Borrador</button>
						<?php }?>

						<?php
if (isset($_GET['return'])) {
    $return = base64_decode($_GET['pag']) . "?" . base64_decode($_GET['return']);
} elseif (isset($_POST['return'])) {
    $return = base64_decode($_POST['return']);
} else {
    $return = "entrega_venta_borrador.php?" . $_SERVER['QUERY_STRING'];
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
				<input type="hidden" form="CrearEntregaVenta" id="P" name="P" value="49" />
				<input type="hidden" form="CrearEntregaVenta" id="IdEntregaVenta" name="IdEntregaVenta" value="<?php if ($edit == 1) {echo base64_encode($row['ID_EntregaVenta']);}?>" />
				<input type="hidden" form="CrearEntregaVenta" id="IdEvento" name="IdEvento" value="<?php if ($edit == 1) {echo base64_encode($IdEvento);}?>" />
				<input type="hidden" form="CrearEntregaVenta" id="d_LS" name="d_LS" value="<?php echo $dt_LS; ?>" />
				<input type="hidden" form="CrearEntregaVenta" id="tl" name="tl" value="<?php echo $edit; ?>" />
				<input type="hidden" form="CrearEntregaVenta" id="swError" name="swError" value="<?php echo $sw_error; ?>" />
				<input type="hidden" form="CrearEntregaVenta" id="return" name="return" value="<?php echo base64_encode($return); ?>" />
				<!-- input type="hidden" form="CrearEntregaVenta" id="PrjCode" name="PrjCode" value="<?php // if ($edit == 1) {echo $row['PrjCode'];}?>" /-->
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
		 $("#CrearEntregaVenta").validate({
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


		// Mostrar modal NO se cumplen las condiciones, SMM 16/08/2022
		<?php if ($success == 0) {?>
			$('#modalAUT').modal('show');
		<?php }?>
		// Hasta aquí, 16/08/2022

		// Almacenar campos de autorización, SMM, 16/08/2022
		$("#formAUT_button").on("click", function(event) {
			// event.preventDefault(); // Evitar redirección del formulario

			let incompleto = false;
			$('.required').each(function() {
				if($(this).val() == null || $(this).val() == ""){
					incompleto = true;
				}
			});

			if(incompleto) {
				Swal.fire({
					"title": "¡Advertencia!",
					"text": "Aún tiene campos sin completar.",
					"icon": "warning"
				});
			} else {
				Swal.fire({
					"title": "¡Listo!",
					"text": "Puede continuar con la creación del documento.",
					"icon": "success"
				});

				// Cambiar estado de autorización a pendiente.
				// if($("#Autorizacion").val() == "N") {
					$("#Autorizacion").val("P").change();

				// Corregir valores nulos en el combo de autorización.
				$('#Autorizacion option:selected').attr('disabled', false);
				$('#Autorizacion option:not(:selected)').attr('disabled', true);
				// }

				// SMM, 12/08/2022
				$("#Actualizar").show();
				$("#Actualizar2").hide();

				$(".form-control").removeAttr('readonly');

				// Ocultar Modal
				$('#modalAUT').modal('hide');
			}
		});
		// Almacenar campos autorización, hasta aquí

		maxLength('Comentarios'); // SMM, 16/08/2022
		maxLength('ComentariosAutor'); // SMM, 16/08/2022


		$(".alkin").on('click', function(){
			$('.ibox-content').toggleClass('sk-loading');
		});


		// Cambiar SN en las copias
		$(".d-venta").on("click", function(event){
			<?php if (PermitirFuncion(419)) {?>
				event.preventDefault(); // Evitar redirección del ancla
				console.log(event);

				Swal.fire({
					title: "¿Desea cambiar de socio de negocio?",
					icon: "question",
					showCancelButton: true,
					confirmButtonText: "Si, confirmo",
					cancelButtonText: "No"
				}).then((result) => {
					if (result.isConfirmed) {
						let qs = "";
						[url, qs] = $(this).attr('href').split('?');
						params = Object.fromEntries(new URLSearchParams(qs));

						$('#modalSN').modal("show");
					} else {
						location.href = $(this).attr('href');
					}
				});
			<?php } else {?>
				console.log("Permiso 419, no esta activo");
			<?php }?>
		});

		let optionsSN = {
			url: function(phrase) {
				return "ajx_buscar_datos_json.php?type=7&id="+phrase;
			},
			adjustWidth: false,
			getValue: "NombreBuscarCliente",
			requestDelay: 400,
			list: {
				match: {
					enabled: true
				},
				onClickEvent: function() {
					var value = $("#NombreClienteSN").getSelectedItemData().CodigoCliente;
					$("#ClienteSN").val(value).trigger("change");
				}
			}
		};

		$("#NombreClienteSN").easyAutocomplete(optionsSN);

		$(".CancelarSN").on("click", function() {
			$('.ibox-content').toggleClass('sk-loading', false);
		});

		$("#formCambiarSN").on("submit", function(event) {
			event.preventDefault(); // Evitar redirección del formulario

			let ClienteSN = document.getElementById('ClienteSN').value;
			let ContactoSN = document.getElementById('ContactoSN').value;
			let SucursalSN = document.getElementById('SucursalSN').value;
			let DireccionSN = document.getElementById('DireccionSN').value;

			params.Cardcode = Base64.encode(ClienteSN);
			params.Contacto = Base64.encode(ContactoSN);
			params.Sucursal = Base64.encode(SucursalSN);
			params.Direccion = Base64.encode(DireccionSN);

			let qs = new URLSearchParams(params).toString();
			location.href = `${url}?${qs}`;
		});

		$("#ClienteSN").change(function() {
			let ClienteSN = document.getElementById('ClienteSN').value;

			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=2&id="+ClienteSN,
				success: function(response) {
					$('#ContactoSN').html(response).fadeIn();
					$('#ContactoSN').trigger('change');
				},
				error: function(error) {
					console.error("ContactoSN", error.responseText);
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=3&id="+ClienteSN,
				success: function(response) {
					console.log(response);

					$('#SucursalSN').html(response).fadeIn();
					$('#SucursalSN').trigger('change');
				},
				error: function(error) {
					console.error("SucursalSN", error.responseText);
				}
			});
		});

		$("#SucursalSN").change(function() {
			let ClienteSN = document.getElementById('ClienteSN').value;
			let SucursalSN = document.getElementById('SucursalSN').value;

			if (SucursalSN != -1 && SucursalSN != '') {
				$.ajax({
					url:"ajx_buscar_datos_json.php",
					data: {
						type: 1,
						CardCode: ClienteSN,
						Sucursal: SucursalSN
					},
					dataType:'json',
					success: function(data) {
						document.getElementById('DireccionSN').value=data.Direccion;
					},
					error: function(error) {
						console.error("SucursalSN", error.responseText);
					}
				});
			}
		});
		// SMM, 11/05/2022


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

		// $(".select2").select2();

		// SMM, 16/08/2022
		<?php if (isset($row_Autorizaciones['IdEstadoAutorizacion']) && ($row_Autorizaciones['IdEstadoAutorizacion'] == 'Y')) {?>
			$("#Actualizar").hide();

			$(".form-control").attr('readonly', 'readonly');

			<?php if (PermitirFuncion(424)) {?>
				$("#ComentariosAutor").removeAttr('readonly');
			<?php } else {?>
				$("#formAUT_button").hide();
			<?php }?>

			$(".select2").select2("readonly", true);
		<?php } else {?>
			$(".select2").select2();
		<?php }?>
		// Hasta aquí, 16/08/2022

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

		 $('#Serie').trigger('change'); // SMM, 01/05/2022
		 $('#CardCode').trigger('change'); // SMM, 24/02/2022

		// SMM, 11/05/2022
		<?php if (isset($_GET['SucursalFact'])) {?>
			$('#SucursalFacturacion').trigger('change');
	 	<?php }?>
	});
</script>
<script>
function Validar(){
	var result=true;

	var TotalItems = document.getElementById("TotalItems");

	//Validar si fue actualizado por otro usuario
	$.ajax({
		url:"ajx_buscar_datos_json.php",
		data:{type:15,
			  docentry:'<?php if ($edit == 1) {echo base64_encode($row['DocEntry']);}?>',
			  objtype:15,
			  date:'<?php echo FormatoFecha(date('Y-m-d'), date('H:i:s')); ?>'},
		dataType:'json',
		async: false,
		success: function(data){
			if(data.Result!=1){
				result=false;
				Swal.fire({
					title: '¡Lo sentimos!',
					text: 'Este documento ya fue actualizado por otro usuario. Debe recargar la página para volver a cargar los datos.',
					icon: 'error'
				});
			}
		}
	 });

	if(TotalItems.value=="0"){
		result=false;
		Swal.fire({
			title: '¡Advertencia!',
			text: 'No puede guardar el documento sin contenido. Por favor verifique.',
			icon: 'warning'
		});
	}

	<?php if ($edit == 0) {?>
	//Validar que los items con lote ya fueron seleccionados
	var Cliente=document.getElementById('CardCode').value;
	var almacen=document.getElementById('Almacen').value;
	$.ajax({
		url:"ajx_buscar_datos_json.php",
		data:{type:17,
			  cardcode:Cliente,
			  objtype:15,
			  whscode:almacen},
		dataType:'json',
		async: false,
		success: function(data){
			if(data.Result!='1'){
				result=false;
				Swal.fire({
					title: '¡Advertencia!',
					text: 'Algunos articulos faltan por seleccionar lotes. Por favor verifique.',
					icon: 'warning'
				});
			}
		}
	});

	$.ajax({
		url:"ajx_buscar_datos_json.php",
		data:{type:19,
			  cardcode:Cliente,
			  objtype:15,
			  whscode:almacen},
		dataType:'json',
		async: false,
		success: function(data){
			if(data.Result!='1'){
				result=false;
				Swal.fire({
					title: '¡Advertencia!',
					text: 'Algunos articulos faltan por seleccionar seriales. Por favor verifique.',
					icon: 'warning'
				});
			}
		}
	});

	$.ajax({
		url:"ajx_buscar_datos_json.php",
		data:{type:27,
			  cardcode:Cliente,
			  objtype:15,
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

	return result;
}

// SMM, 16/08/2022
function CambiarDefinitivo() {
	$("#Definitivo").val("1");
}
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
				url: "dm_actividades.php?id=<?php if ($edit == 1) {echo base64_encode($row['DocEntry']);}?>&objtype=15",
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