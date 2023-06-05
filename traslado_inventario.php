<?php require_once "includes/conexion.php";
PermitirAcceso(1204);
$msg_error = ""; //Mensaje del error
$dt_SS = 0; //sw para saber si vienen datos de una Solicitud de salida.
$dt_DR = 0; //sw Para saber que viene de un despachode rutas
$IdTrasladoInv = 0;
$IdPortal = 0; //Id del portal para las solicitudes que fueron creadas en el portal, para eliminar el registro antes de cargar al editar
$NameFirma = "";
$PuedeFirmar = 0;

// Dimensiones, SMM 29/08/2022
$DimSeries = intval(ObtenerVariable("DimensionSeries"));
$SQL_Dimensiones = Seleccionar('uvw_Sap_tbl_Dimensiones', '*', "DimActive='Y'");

// Pruebas, SMM 29/08/2022
// $SQL_Dimensiones = Seleccionar('uvw_Sap_tbl_Dimensiones', '*', 'DimCode IN (1,2)');

$array_Dimensiones = [];
while ($row_Dimension = sqlsrv_fetch_array($SQL_Dimensiones)) {
    array_push($array_Dimensiones, $row_Dimension);
}

$encode_Dimensiones = json_encode($array_Dimensiones);
$cadena_Dimensiones = "JSON.parse('$encode_Dimensiones'.replace(/\\n|\\r/g, ''))";
// echo "<script> console.log('cadena_Dimensiones'); </script>";
// echo "<script> console.log($cadena_Dimensiones); </script>";
// Hasta aquí, SMM 29/08/2022

// SMM, 30/11/2022
$IdMotivo = "";
$motivoAutorizacion = "";

$debug_Condiciones = true; // Ocultar o mostrar modal y otras opciones de debug.
$IdTipoDocumento = 67; // Cambiar por el ID respectivo.
$success = 1; // Confirmación de autorización (1 - Autorizado / 0 - NO Autorizado)
$mensajeProceso = ""; // Mensaje proceso, mensaje de salida del procedimiento almacenado.

// Procesos de autorización, SMM 30/11/2022
$SQL_Procesos = Seleccionar("uvw_tbl_Autorizaciones_Procesos", "*", "Estado = 'Y' AND IdTipoDocumento = $IdTipoDocumento");

if (isset($_GET['id']) && ($_GET['id'] != "")) { //ID de la Salida de inventario (DocEntry)
    $IdTrasladoInv = base64_decode($_GET['id']);
}

if (isset($_GET['id_portal']) && ($_GET['id_portal'] != "")) { //Id del portal de venta (ID interno)
    $IdPortal = base64_decode($_GET['id_portal']);
}

if (isset($_POST['IdTrasladoInv']) && ($_POST['IdTrasladoInv'] != "")) { //Tambien el Id interno, pero lo envío cuando mando el formulario
    $IdTrasladoInv = base64_decode($_POST['IdTrasladoInv']);
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
if ($edit == 1) {
    $DocEntry = "'" . $IdTrasladoInv . "'"; // Cambiar por el ID respectivo del documento.

    $EsBorrador = (false) ? "DocumentoBorrador" : "Documento";
    $SQL_Autorizaciones = Seleccionar("uvw_Sap_tbl_Autorizaciones", "*", "IdTipoDocumento = $IdTipoDocumento AND DocEntry$EsBorrador = $DocEntry");
    $row_Autorizaciones = sqlsrv_fetch_array($SQL_Autorizaciones);

    // SMM, 30/11/2022
    $SQL_Procesos = Seleccionar("uvw_tbl_Autorizaciones_Procesos", "*", "IdTipoDocumento = $IdTipoDocumento");
}
// Hasta aquí, 30/11/2022

if (isset($_POST['P']) && ($_POST['P'] != "")) { //Grabar Salida de inventario
    //*** Carpeta temporal ***
    $i = 0; //Archivos
    $RutaAttachSAP = ObtenerDirAttach();
    $dir = CrearObtenerDirTemp();
    $dir_firma = CrearObtenerDirTempFirma();
    $dir_new = CrearObtenerDirAnx("trasladoinventario");

    // La firma se copia al directorio temporal, pero luego es que se copia a SAP.
    if ((isset($_POST['SigRecibe'])) && ($_POST['SigRecibe'] != "")) {
        $NombreFileFirma = base64_decode($_POST['SigRecibe']);
        $Nombre_Archivo = $NombreFileFirma;
        if (!copy($dir_firma . $NombreFileFirma, $dir . $Nombre_Archivo)) {
            $sw_error = 1;
            $msg_error = "No se pudo mover la firma";
        }
    }

    // Luego el directorio temporal se lee y se copia al directorio de SAP (RutaAttachSAP).
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
            $IdTrasladoInv = base64_decode($_POST['IdTrasladoInv']);
            $IdEvento = base64_decode($_POST['IdEvento']);
            $Type = 2;

            /*
            if (!PermitirFuncion(403)) { //Permiso para autorizar Solicitud de salida
            $_POST['Autorizacion'] = 'P'; //Si no tengo el permiso, la Solicitud queda pendiente
            }
             */

            if ((isset($_POST['SigRecibe'])) && ($_POST['SigRecibe'] != "")) { //Si solo estoy actualizando la firma
                $Type = 4;
            }
        } else { //Crear
            $IdTrasladoInv = "NULL";
            $IdEvento = "0";
            $Type = 1;
        }

        if (isset($_POST['AnioEntrega']) && ($_POST['AnioEntrega'] != "")) {
            $AnioEntrega = "'" . $_POST['AnioEntrega'] . "'";
        } else {
            $AnioEntrega = "NULL";
        }

        if ($Type == 4) {
            $ParametrosCabTrasladoInv = array(
                $IdTrasladoInv,
                $IdEvento,
                "'" . $_SESSION['CodUser'] . "'",
                "'" . $_SESSION['CodUser'] . "'",
                $Type,
                // SMM, 17/02/2023
                "'" . ($_POST['NombreRecibeFirma'] ?? "") . "'",
                "'" . ($_POST['CedulaRecibeFirma'] ?? "") . "'",
                // Estos campos deben ir arriba para poder firmar.
            );
        } else {
            $ParametrosCabTrasladoInv = array(
                $IdTrasladoInv,
                $IdEvento,
                "'" . $_SESSION['CodUser'] . "'",
                "'" . $_SESSION['CodUser'] . "'",
                $Type,
                // SMM, 16/02/2023
                "'" . ($_POST['NombreRecibeFirma'] ?? "") . "'",
                "'" . ($_POST['CedulaRecibeFirma'] ?? "") . "'",
                // Estos campos deben ir arriba para poder firmar.
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
                "'" . str_replace(',', '', $_POST['TotalTraslado']) . "'",
                "'" . $_POST['SucursalFacturacion'] . "'",
                "'" . $_POST['DireccionFacturacion'] . "'",
                "'" . $_POST['SucursalDestino'] . "'",
                "'" . $_POST['DireccionDestino'] . "'",
                "'" . $_POST['CondicionPago'] . "'",
                "'" . $_POST['Almacen'] . "'",
                "'" . $_POST['AlmacenDestino'] . "'",

                // Se eliminaron las dimensiones, SMM 23/11/2022

                "'" . $_POST['PrjCode'] . "'", // SMM, 29/11/2022
                "'" . $_POST['Autorizacion'] . "'",
                "NULL",
                "NULL",
                "NULL",
                "NULL",
                "NULL",
                // SMM, 30/11/2022
                "'" . ($_POST['IdMotivoAutorizacion'] ?? "") . "'",
                "'" . ($_POST['ComentariosAutor'] ?? "") . "'",
                "'" . ($_POST['MensajeProceso'] ?? "") . "'",
                // SMM, 23/12/2022
                "'" . $_POST['ConceptoSalida'] . "'",
            );
        }

        // Enviar el valor de la dimensiones dinámicamente al SP.
        foreach ($array_Dimensiones as &$dim) {
            $Dim_PostValue = $_POST[strval($dim['IdPortalOne'])] ?? ""; // SMM, 01/12/2022

            // El nombre de los parámetros es diferente en cada documento.
            array_push($ParametrosCabTrasladoInv, "'$Dim_PostValue'");
        } // SMM, 23/11/2022

        $SQL_CabeceraTrasladoInv = EjecutarSP('sp_tbl_TrasladoInventario', $ParametrosCabTrasladoInv, $_POST['P']);
        if ($SQL_CabeceraTrasladoInv) {
            if ($Type == 1) {
                $row_CabeceraTrasladoInv = sqlsrv_fetch_array($SQL_CabeceraTrasladoInv);
                $IdTrasladoInv = $row_CabeceraTrasladoInv[0];
                $IdEvento = $row_CabeceraTrasladoInv[1];

                // Comprobar procesos de autorización en la creación, SMM 30/11/2022
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

                // Consultar el motivo de autorización según el ID.
                $SQL_Motivos = Seleccionar("uvw_tbl_Autorizaciones_Motivos", "*", "IdMotivoAutorizacion = '$IdMotivo'");
                $row_MotivoAutorizacion = sqlsrv_fetch_array($SQL_Motivos);
                $motivoAutorizacion = $row_MotivoAutorizacion['MotivoAutorizacion'] ?? "";

                // Hasta aquí, 30/11/2022
            } else {
                $IdTrasladoInv = base64_decode($_POST['IdTrasladoInv']); //Lo coloco otra vez solo para saber que tiene ese valor
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
                            "'67'",
                            "'" . $IdTrasladoInv . "'",
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

            //Consultar cabecera
            $SQL_Cab = Seleccionar("uvw_tbl_TrasladoInventario", '*', "ID_TrasladoInv='" . $IdTrasladoInv . "' and IdEvento='" . $IdEvento . "'");
            $row_Cab = sqlsrv_fetch_array($SQL_Cab);

            //Consultar detalle
            $SQL_Det = Seleccionar("uvw_tbl_TrasladoInventarioDetalle", '*', "ID_TrasladoInv='" . $IdTrasladoInv . "' and IdEvento='" . $IdEvento . "'");

            //Consultar anexos
            $SQL_Anx = Seleccionar("uvw_tbl_DocumentosSAP_Anexos", '*', "ID_Documento='" . $IdTrasladoInv . "' and TipoDocumento='67' and Metodo=1");

            // SMM, 23/02/2023
            $ID_Documento = $row_Cab['DocNum'] ?? 0;

            //Consultar Lotes. SMM, 22/02/2023
            if ($edit == 1) {
                $SQL_Lotes = Seleccionar("uvw_Sap_tbl_LotesDocSAP", '*', "DocEntry='$ID_Documento' AND ObjType='67' AND Cantidad > 0 AND Sentido = 'IN'");
                // echo "SELECT * FROM uvw_Sap_tbl_LotesDocSAP WHERE DocEntry='$ID_Documento' AND ObjType='67' AND Cantidad > 0 AND Sentido = 'IN'";
                // exit();
            } else {
                $SQL_Lotes = Seleccionar("uvw_tbl_LotesDocSAP", '*', "DocEntry='$IdTrasladoInv' and IdEvento='$IdEvento' and ObjType='67' and Cantidad > 0");
                // echo "SELECT * FROM uvw_tbl_LotesDocSAP WHERE DocEntry='$IdTrasladoInv' AND IdEvento='$IdEvento' AND ObjType='67' AND Cantidad > 0";
                // exit();
            }

            // Consultar Seriales, 24/11/2022
            if ($edit == 1) {
                $SQL_Seriales = Seleccionar("uvw_Sap_tbl_SerialesDocSAP", '*', "DocEntry='$ID_Documento' AND ObjType='67' AND Cantidad > 0 AND Sentido = 'IN'");
                // echo "SELECT * FROM uvw_Sap_tbl_SerialesDocSAP WHERE DocEntry='$ID_Documento' AND ObjType='67' AND Cantidad > 0 AND Sentido = 'IN'";
                // exit();
            } else {
                $SQL_Seriales = Seleccionar("uvw_tbl_SerialesDocSAP", '*', "DocEntry='$IdTrasladoInv' and IdEvento='$IdEvento' and ObjType='67' and Cantidad > 0");
                // echo "SELECT * FROM uvw_tbl_SerialesDocSAP WHERE DocEntry='$IdTrasladoInv' AND IdEvento='$IdEvento' AND ObjType='67' AND Cantidad > 0";
                // exit();
            }

            $Detalle = array();
            $Anexos = array();
            $Lotes = array();
            $Seriales = array();

            //Detalle
            while ($row_Det = sqlsrv_fetch_array($SQL_Det)) {

                array_push($Detalle, array(
                    "base_type" => ($row_Det['BaseType'] === "") ? null : intval($row_Det['BaseType']),
                    "base_entry" => ($row_Det['BaseEntry'] === "") ? null : intval($row_Det['BaseEntry']),
                    "base_line" => ($row_Det['BaseLine'] === "") ? null : intval($row_Det['BaseLine']),
                    "line_num" => intval($row_Det['LineNum']),
                    "id_tipo_articulo" => "",
                    "tipo_articulo" => 0,
                    "id_articulo" => $row_Det['ItemCode'],
                    "articulo" => $row_Det['ItemName'],
                    "unidad_medida" => $row_Det['UnitMsr'],
                    "texto_libre" => $row_Det['FreeTxt'],
                    "id_bodega" => $row_Det['WhsCode'],
                    "id_bodega_destino" => $row_Det['ToWhsCode'],
                    "cant_articulo" => doubleval($row_Det['Quantity']),
                    "precio_articulo" => doubleval($row_Det['Price']),
                    "dim1" => $row_Det['OcrCode'],
                    "dim2" => $row_Det['OcrCode2'],
                    "dim3" => $row_Det['OcrCode3'],
                    "dim4" => $row_Det['OcrCode4'],
                    "dim5" => $row_Det['OcrCode5'],
                    "id_proyecto" => $row_Det['PrjCode'],
                    "metodo_linea" => intval($row_Det['Metodo']),
                    "maneja_serial" => $row_Det['ManSerNum'],
                    "maneja_lote" => $row_Det['ManBtchNum'],
                    "CDU_id_servicio" => $row_Det['CDU_IdServicio'],
                    "CDU_id_metodo_aplicacion" => $row_Det['CDU_IdMetodoAplicacion'],
                    "CDU_id_tipo_plagas" => $row_Det['CDU_IdTipoPlagas'],
                    "CDU_areas_controladas" => $row_Det['CDU_AreasControladas'],
                    "CDU_cant_litros" => doubleval($row_Det['CDU_CantLitros']),
                    "CDU_dosificacion" => 0,
                    "CDU_codigo_empleado" => "",
                    "CDU_nombre_empleado" => "",
                    "CDU_texto_libre" => "",
                    "CDU_numero_ots" => "",
                    "CDU_id_direccion_destino" => "",
                    "estado_linea" => $row_Det['LineStatus'],
                ));
            }

            //Anexos
            $i = 0;
            while ($row_Anx = sqlsrv_fetch_array($SQL_Anx)) {

                array_push($Anexos, array(
                    "id_anexo" => $i,
                    "tipo_documento" => intval($row_Anx['TipoDocumento']),
                    "id_documento" => intval($row_Anx['ID_Documento']),
                    "archivo" => $row_Anx['FileName'],
                    "ext_archivo" => $row_Anx['FileExt'],
                    "metodo" => intval($row_Anx['Metodo']),
                    "fecha" => FormatoFechaToSAP($row_Anx['Fecha']->format('Y-m-d')),
                    "id_usuario" => intval($row_Anx['ID_Usuario']),
                ));
                $i++;
            }

            //Lotes
            while ($row_Lotes = sqlsrv_fetch_array($SQL_Lotes)) {

                array_push($Lotes, array(
                    "id_documento" => intval($row_Lotes['DocEntry']),
                    "id_linea" => intval($row_Lotes['DocLinea']),
                    "id_articulo" => $row_Lotes['ItemCode'],
                    "articulo" => ($row_Lotes['ItemName'] ?? ""),
                    "cantidad" => intval($row_Lotes['Cantidad']),
                    "serial_lote" => $row_Lotes['DistNumber'],
                    "id_systema_articulo" => intval($row_Lotes['SysNumber']),
                ));
            }

            // Seriales, 24/11/2022
            while ($row_Seriales = sqlsrv_fetch_array($SQL_Seriales)) {

                array_push($Seriales, array(
                    "id_documento" => intval($row_Seriales['DocEntry']),
                    "id_linea" => intval($row_Seriales['DocLinea']),
                    "id_articulo" => $row_Seriales['ItemCode'],
                    "articulo" => $row_Seriales['ItemName'],
                    "cantidad" => intval($row_Seriales['Cantidad']),
                    "serial_lote" => $row_Seriales['DistNumber'],
                    "id_systema_articulo" => intval($row_Seriales['SysNumber']),
                ));
            }

            $Cabecera = array(
                "crear_salida_inventario" => PermitirFuncion(1214), // SMM, 22/02/2023
                "id_serie_salida_inventario" => (PermitirFuncion(1214) ? intval(ObtenerVariable("IdSerieSalidaInvPorDefecto")) : null), // SMM, 28/02/2023
                "CDU_nombre_firma_recibe" => ($row_Cab['NombreRecibeFirma'] ?? ""), // SMM, 20/02/2023
                "CDU_CC_firma_recibe" => ($row_Cab['CedulaRecibeFirma'] ?? ""), // SMM, 20/02/2023
                "id_documento" => $ID_Documento, // SMM, 01/12/2022
                "id_tipo_documento" => "67",
                "tipo_documento" => "Traslado de inventario",
                "moneda_documento" => "$",
                "estado" => $row_Cab['Cod_Estado'],
                "id_doc_portal" => "" . $row_Cab['ID_TrasladoInv'] . "",
                "id_series" => intval($row_Cab['IdSeries']),
                "id_cliente" => $row_Cab['CardCode'],
                "cliente" => $row_Cab['NombreCliente'],
                "id_contacto_cliente" => intval($row_Cab['CodigoContacto']),
                "contacto_cliente" => $row_Cab['NombreContacto'],
                "referencia" => $row_Cab['NumAtCard'],
                "id_condicion_pago" => intval($row_Cab['IdCondicionPago']),
                "id_direccion_facturacion" => $row_Cab['SucursalFacturacion'],
                "id_direccion_destino" => $row_Cab['SucursalDestino'],
                "fecha_contabilizacion" => FormatoFechaToSAP($row_Cab['DocDate']),
                "fecha_vencimiento" => FormatoFechaToSAP($row_Cab['DocDueDate']),
                "fecha_documento" => FormatoFechaToSAP($row_Cab['TaxDate']),
                "comentarios" => $row_Cab['Comentarios'],
                "usuario" => $row_Cab['Usuario'],
                "fecha_creacion" => FormatoFechaToSAP($row_Cab['FechaRegistro']->format('Y-m-d'), $row_Cab['FechaRegistro']->format('H:i:s')),
                "hora_creacion" => FormatoFechaToSAP($row_Cab['FechaRegistro']->format('Y-m-d'), $row_Cab['FechaRegistro']->format('H:i:s')),
                "id_anexo" => 0,
                "docentry_llamada_servicio" => 0,
                "docentry_documento" => $row_Cab['DocEntry'] ?? 0, // SMM, 01/12/2022
                "id_llamada_servicio" => 0,
                "id_vendedor" => intval($row_Cab['SlpCode']),
                "metodo" => intval($row_Cab['Metodo']),
                "id_bodega_origen" => $row_Cab['WhsCode'],
                "id_bodega_destino" => $row_Cab['ToWhsCode'],
                "documentos_Lineas" => $Detalle,
                "documentos_Anexos" => $Anexos,
                "documentos_Lotes" => $Lotes,
                "documentos_Seriales" => $Seriales,
            );

            $Cabecera_json = json_encode($Cabecera);
            // echo $Cabecera_json;
            // exit();

            // Verificar que el documento cumpla las Condiciones o este Pendiente de Autorización.
            if (($success == 1) || ($_POST['Autorizacion'] == "P")) {
                $success = 1;

                // Inicio, Enviar datos al WebServices.
                try {
                    if ($_POST['tl'] == 0) { //Creando
                        $Metodo = "TrasladosInventarios";
                        $Resultado = EnviarWebServiceSAP($Metodo, $Cabecera, true, true);
                    } else { //Editando
                        $Metodo = "TrasladosInventarios/" . $IdTrasladoInv;
                        $Resultado = EnviarWebServiceSAP($Metodo, $Cabecera, true, true, "PUT");
                    }

                    if ($Resultado->Success == 0) {
                        $sw_error = 1;
                        $msg_error = $Resultado->Mensaje;
                    } else {
                        // SMM, 30/11/2022
                        if (isset($_POST['Autorizacion']) && ($_POST['Autorizacion'] == "P")) {
                            $nombreArchivo = "traslado_inventario"; // Ajustar según sea el caso.
                            header("Location:$nombreArchivo.php?a=" . base64_encode("OK_BorradorAdd"));
                        } else {
                            // Inicio, redirección documento autorizado.
                            if ($_POST['tl'] == 0) { //Creando traslado
                                //Consultar ID creado para cargar el documento
                                $SQL_ConsID = Seleccionar('uvw_Sap_tbl_TrasladosInventarios', 'ID_TrasladoInv', "IdDocPortal='" . $IdTrasladoInv . "'");
                                $row_ConsID = sqlsrv_fetch_array($SQL_ConsID);
                                sqlsrv_close($conexion);
                                header('Location:traslado_inventario.php?id=' . base64_encode($row_ConsID['ID_TrasladoInv']) . '&id_portal=' . base64_encode($IdTrasladoInv) . '&tl=1&a=' . base64_encode("OK_TrasInvAdd"));
                            } else { //Actualizando traslado

                                // SMM, 17/02/2023
                                $SQL_ConsID = Seleccionar('uvw_Sap_tbl_TrasladosInventarios', 'ID_TrasladoInv', "IdDocPortal='" . $IdTrasladoInv . "'");
                                $row_ConsID = sqlsrv_fetch_array($SQL_ConsID);
                                sqlsrv_close($conexion);

                                /*
                                echo "SELECT ID_TrasladoInv FROM uvw_Sap_tbl_TrasladosInventarios WHERE IdDocPortal='$IdTrasladoInv'";
                                echo '<br>traslado_inventario.php?id=' . $row_ConsID['ID_TrasladoInv'] . "&id_portal=$IdTrasladoInv&tl=1&a=OK_TrasInvAdd";
                                echo '<br>traslado_inventario.php?id=' . base64_encode($row_ConsID['ID_TrasladoInv']) . '&id_portal=' . base64_encode($IdTrasladoInv) . '&tl=1&a=' . base64_encode("OK_TrasInvAdd");
                                exit();
                                 */

                                header('Location:traslado_inventario.php?id=' . base64_encode($row_ConsID['ID_TrasladoInv']) . '&id_portal=' . base64_encode($IdTrasladoInv) . '&tl=1&a=' . base64_encode("OK_TrasInvUpd"));
                                // header('Location:' . base64_decode($_POST['return']) . '&a=' . base64_encode("OK_TrasInvUpd"));
                            }
                            // Fin, redirección documento autorizado.
                        }
                    }
                } catch (Exception $e) {
                    echo 'Excepcion capturada: ', $e->getMessage(), "\n";
                }
                // Fin, Enviar datos al WebServices.
            } else {
                $sw_error = 1;
                $msg_error = "Este documento necesita autorización.";
            }
            // Hasta aquí, 30/11/2022

//            sqlsrv_close($conexion);
            //            if($_POST['tl']==0){//Creando Entrada
            //                header('Location:'.base64_decode($_POST['return']).'&a='.base64_encode("OK_TrasInvAdd"));
            //            }else{//Actualizando Entrada
            //                header('Location:'.base64_decode($_POST['return']).'&a='.base64_encode("OK_TrasInvUpd"));
            //            }

        } else {
            $sw_error = 1;
            $msg_error = "Ha ocurrido un error al crear el traslado de inventario";
        }
    } catch (Exception $e) {
        echo 'Excepcion capturada: ', $e->getMessage(), "\n";
    }

}

if (isset($_GET['dt_SS']) && ($_GET['dt_SS']) == 1) { //Verificar que viene de una Solicitud de salida
    $dt_SS = 1;

    // Limpiar lotes y seriales. SMM, 23/01/2022
    $ConsLote = "Delete From tbl_LotesDocSAP Where CardCode='" . base64_decode($_GET['Cardcode']) . "' And Usuario='" . $_SESSION['CodUser'] . "'";
    $ConsSerial = "Delete From tbl_SerialesDocSAP Where CardCode='" . base64_decode($_GET['Cardcode']) . "' And Usuario='" . $_SESSION['CodUser'] . "'";
    $SQL_ConsLote = sqlsrv_query($conexion, $ConsLote);
    $SQL_ConsSerial = sqlsrv_query($conexion, $ConsSerial);

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

    $ParametrosCopiarSolSalidaToTrasladoInv = array(
        "'" . base64_decode($_GET['SS']) . "'",
        "'" . base64_decode($_GET['Evento']) . "'",
        "'" . base64_decode($_GET['Almacen']) . "'",
        "'" . base64_decode($_GET['Cardcode']) . "'",
        "'" . $_SESSION['CodUser'] . "'",
    );
    $SQL_CopiarSolSalidaToTrasladoInv = EjecutarSP('sp_tbl_SolSalidaDet_To_TrasladoInvDet', $ParametrosCopiarSolSalidaToTrasladoInv);
    if (!$SQL_CopiarSolSalidaToTrasladoInv) {
        echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Ha ocurrido un error!',
				text: 'No se pudo copiar la Solicitud en la Salida de inventario.',
				icon: 'error'
			});
		});
		</script>";
    }

}

if (isset($_GET['dt_DR']) && ($_GET['dt_DR']) == 1) { //Verificar que viene del despacho de rutas
    $dt_SS = 1;
    $dt_DR = 1;

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreCliente');
    $row_Cliente = sqlsrv_fetch_array($SQL_Cliente);

    //Sucursal destino
    $SQL_SucursalDestino = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "' AND TipoDireccion='S' and NumeroLinea=0");
    $row_SucursalDestino = sqlsrv_fetch_array($SQL_SucursalDestino);
    $_GET['Direccion'] = base64_encode($row_SucursalDestino['Direccion']);

    sqlsrv_fetch($SQL_SucursalDestino, SQLSRV_SCROLL_ABSOLUTE, -1);

    //Contacto cliente
    //$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreContacto');

    $ParamCopiarDespachosToTraslados = array(
        "'" . base64_decode($_GET['AlmacenDestino']) . "'",
        "'" . base64_decode($_GET['Cardcode']) . "'",
        "'" . $_SESSION['CodUser'] . "'",
    );
    $SQL_CopiarDespachosToTraslados = EjecutarSP('sp_tbl_DespachoRutas_To_TrasladoInvDet', $ParamCopiarDespachosToTraslados);
    if (!$SQL_CopiarDespachosToTraslados) {
        echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Ha ocurrido un error!',
				text: 'No se pudo copiar el despacho en el traslado de inventario.',
				icon: 'error'
			});
		});
		</script>";
    }

}

if ($edit == 1 && $sw_error == 0) {

    $ParametrosLimpiar = array(
        "'" . $IdTrasladoInv . "'",
        "'" . $IdPortal . "'",
        "'" . $_SESSION['CodUser'] . "'",
    );
    $LimpiarSolSalida = EjecutarSP('sp_EliminarDatosTrasladoInventario', $ParametrosLimpiar);

    $SQL_IdEvento = sqlsrv_fetch_array($LimpiarSolSalida);
    $IdEvento = $SQL_IdEvento[0];

    //Salida inventario
    $Cons = "Select * From uvw_tbl_TrasladoInventario Where DocEntry='" . $IdTrasladoInv . "' AND IdEvento='" . $IdEvento . "'";
    // echo $Cons;
    // exit();

    $SQL = sqlsrv_query($conexion, $Cons);
    $row = sqlsrv_fetch_array($SQL);

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreCliente');

    //Sucursales
    $SQL_SucursalFacturacion = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' and TipoDireccion='B'", 'NombreSucursal');
    $SQL_SucursalDestino = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' and TipoDireccion='S'", 'NombreSucursal');

    //Contacto cliente
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreContacto');

    //Orden de servicio, SMM, 29/08/2022
    $SQL_OrdenServicioCliente = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_LlamadaServicio='" . $row['ID_LlamadaServicio'] . "'");
    $row_OrdenServicioCliente = sqlsrv_fetch_array($SQL_OrdenServicioCliente);

    //Sucursal
    $SQL_Sucursal = SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes', 'IdSucursal, DeSucursal', "IdSeries='" . $row['IdSeries'] . "'", "IdSucursal, DeSucursal");

    //Almacenes origen
    $SQL_Almacen = SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes', 'WhsCode, WhsName', "IdSeries='" . $row['IdSeries'] . "'", "WhsCode, WhsName", 'WhsName');

    //Almacenes destino
    $SQL_AlmacenDestino = SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes', 'ToWhsCode, ToWhsName', "IdSeries='" . $row['IdSeries'] . "'", "ToWhsCode, ToWhsName", 'ToWhsName');

    //Anexos
    $SQL_Anexo = Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos', '*', "AbsEntry='" . $row['IdAnexo'] . "'");

    if (($row['CodEmpleado'] == $_SESSION['IdCardCode']) || PermitirFuncion(1213)) {
        $PuedeFirmar = 1;
    } else {
        $PuedeFirmar = 0;
    }

}

if ($sw_error == 1) {

    //Salida de inventario
    $Cons = "Select * From uvw_tbl_TrasladoInventario Where ID_TrasladoInv='" . $IdTrasladoInv . "' AND IdEvento='" . $IdEvento . "'";
    $SQL = sqlsrv_query($conexion, $Cons);
    $row = sqlsrv_fetch_array($SQL);

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreCliente');

    //Sucursales
    $SQL_SucursalFacturacion = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' and TipoDireccion='B'", 'NombreSucursal');
    $SQL_SucursalDestino = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . $row['CardCode'] . "' and TipoDireccion='S'", 'NombreSucursal');

    //Contacto cliente
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', '*', "CodigoCliente='" . $row['CardCode'] . "'", 'NombreContacto');

    //Orden de servicio, SMM, 29/08/2022
    $SQL_OrdenServicioCliente = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_LlamadaServicio='" . $row['ID_LlamadaServicio'] . "'");
    $row_OrdenServicioCliente = sqlsrv_fetch_array($SQL_OrdenServicioCliente);

    //Sucursal
    $SQL_Sucursal = SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes', 'IdSucursal, DeSucursal', "IdSeries='" . $row['IdSeries'] . "'", "IdSucursal, DeSucursal");

    //Almacenes origen
    $SQL_Almacen = SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes', 'WhsCode, WhsName', "IdSeries='" . $row['IdSeries'] . "'", "WhsCode, WhsName", 'WhsName');

    //Almacenes destino
    $SQL_AlmacenDestino = SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes', 'ToWhsCode, ToWhsName', "IdSeries='" . $row['IdSeries'] . "'", "ToWhsCode, ToWhsName", 'ToWhsName');

    //Anexos
    $SQL_Anexo = Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos', '*', "AbsEntry='" . $row['IdAnexo'] . "'");

    if (($row['CodEmpleado'] == $_SESSION['IdCardCode']) || PermitirFuncion(1213)) {
        $PuedeFirmar = 1;
    } else {
        $PuedeFirmar = 0;
    }

}

// Se eliminaron las dimensiones en esta parte, SMM 29/08/2022

//Condiciones de pago
$SQL_CondicionPago = Seleccionar('uvw_Sap_tbl_CondicionPago', '*', '', 'IdCondicionPago');

//Datos de dimensiones del usuario actual
$SQL_DatosEmpleados = Seleccionar('uvw_tbl_Usuarios', 'CentroCosto1,CentroCosto2,CentroCosto3', "ID_Usuario='" . $_SESSION['CodUser'] . "'");
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

//Empleado de ventas
$SQL_EmpleadosVentas = Seleccionar('uvw_Sap_tbl_EmpleadosVentas', '*', '', 'DE_EmpVentas');

//Series de documento
$ParamSerie = array(
    "'" . $_SESSION['CodUser'] . "'",
    "'67'",
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

// Consultar el motivo de autorización según el ID. SMM, 30/11/2022
if (isset($row['IdMotivoAutorizacion']) && ($row['IdMotivoAutorizacion'] != "") && ($IdMotivo == "")) {
    $IdMotivo = $row['IdMotivoAutorizacion'];
    $SQL_Motivos = Seleccionar("uvw_tbl_Autorizaciones_Motivos", "*", "IdMotivoAutorizacion = '$IdMotivo'");
    $row_MotivoAutorizacion = sqlsrv_fetch_array($SQL_Motivos);
    $motivoAutorizacion = $row_MotivoAutorizacion['MotivoAutorizacion'] ?? "";
}

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

// Stiven Muñoz Murillo, 29/08/2022
$row_encode = isset($row) ? json_encode($row) : "";
$cadena = isset($row) ? "JSON.parse('$row_encode'.replace(/\\n|\\r/g, ''))" : "'Not Found'";
// echo "<script> console.log('consulta principal'); </script>";
// echo "<script> console.log($cadena); </script>";

// SMM, 17/02/2023
// echo base64_decode($_GET["id"]) . "<br>";
// echo base64_decode($_GET["id_portal"]);
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Traslado de inventario | <?php echo NOMBRE_PORTAL; ?></title>
<?php
if (isset($_GET['a']) && $_GET['a'] == base64_encode("OK_TrasInvAdd")) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'El traslado de inventario ha sido creado exitosamente.',
				icon: 'success'
			});
		});
		</script>";
}
if (isset($_GET['a']) && $_GET['a'] == base64_encode("OK_TrasInvUpd")) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'El traslado de inventario ha sido actualizado exitosamente.',
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
                icon: 'warning'
            });
			console.log('json:','" . ($Cabecera_json ?? "") . "');
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
	var almacendestino= document.getElementById("AlmacenDestino").value;
	var cardcode= document.getElementById("CardCode").value;

	// SMM, 29/08/2022
	var dim1= ((document.getElementById("Dim1") || {}).value) || "";
	var dim2= ((document.getElementById("Dim2") || {}).value) || "";
	var dim3= ((document.getElementById("Dim3") || {}).value) || "";
	var dim4= ((document.getElementById("Dim4") || {}).value) || "";
	var dim5= ((document.getElementById("Dim5") || {}).value) || "";
	// Hasta aquí, 29/08/2022

	// SMM, 29/11/2022
	let proyecto = document.getElementById("PrjCode").value;

	// SMM, 23/01/2023
	let conceptoSalida = document.getElementById("ConceptoSalida").value;

	var posicion_x;
	var posicion_y;
	posicion_x=(screen.width/2)-(1200/2);
	posicion_y=(screen.height/2)-(500/2);

	if(dato!=""){
		if((cardcode!="")&&(almacen!="")&&(almacendestino!="")){
			remote=open('buscar_articulo.php?concepto=${conceptoSalida}&dato='+dato+'&cardcode='+cardcode+'&prjcode='+proyecto+'&whscode='+almacen+'&towhscode='+almacendestino+'&doctype=<?php if ($edit == 0) {echo "11";} else {echo "12";}?>&idtrasladoinv=<?php if ($edit == 1) {echo base64_encode($row['ID_TrasladoInv']);} else {echo "0";}?>&evento=<?php if ($edit == 1) {echo base64_encode($row['IdEvento']);} else {echo "0";}?>&tipodoc=3&dim1='+dim1+'&dim2='+dim2+'&dim3='+dim3,'remote',"width=1200,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
			remote.focus();
		}else{
			Swal.fire({
				title: "¡Advertencia!",
				text: "Debe seleccionar un cliente, un almacén de origen y uno de destino",
				icon: "warning",
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
function AbrirFirma(IDCampo){
	var posicion_x;
	var posicion_y;
	posicion_x=(screen.width/2)-(1200/2);
	posicion_y=(screen.height/2)-(500/2);
	self.name='opener';
	remote=open('popup_firma.php?id='+Base64.encode(IDCampo),'remote',"width=1200,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
	remote.focus();
}

// SMM, 30/11/2022
function verAutorizacion() {
	$('#modalAUT').modal('show');
}
</script>

<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#CardCode").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);

			var frame=document.getElementById('DataGrid');
			var carcode=document.getElementById('CardCode').value;
			var almacen=document.getElementById('Almacen').value;
			var almacendestino=document.getElementById('AlmacenDestino').value;

			<?php if ($edit == 0 && $dt_DR == 0 && $dt_SS == 0 && $sw_error == 0) {?>
			$.ajax({
				type: "POST",
				url: "includes/procedimientos.php?type=7&objtype=67&cardcode="+carcode
			});
			<?php }?>

			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=2&id="+carcode,
				success: function(response){
					$('#ContactoCliente').html(response).fadeIn();
				}
				, error: function(error){
					console.log("724->", error.responseText);
				}
			});

			<?php if ($dt_SS == 0) { //Para que no recargue las listas cuando vienen de una solicitud de salida.?>
				$.ajax({
					type: "POST",
					url: "ajx_cbo_select.php?type=3&tdir=S&id="+carcode,
					success: function(response){
						$('#SucursalDestino').html(response).fadeIn();

						<?php if (($edit == 0) && ($ClienteDefault != "")) {?>
							$("#SucursalDestino").val("<?php echo $SucursalDestinoDefault; ?>");
						<?php }?>

						$('#SucursalDestino').trigger('change');
					}
					, error: function(error){
						console.log("736->", error.responseText);
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
					}
					, error: function(error){
						console.log("749->", error.responseText);
					}
				});

				// Recargar condición de pago.
				$.ajax({
					type: "POST",
					url: "ajx_cbo_select.php?type=7&id="+carcode,
					success: function(response){
						$('#CondicionPago').html(response).fadeIn();
					}
					, error: function(error){
						console.log("760->", error.responseText);
					}
				});
			<?php }?>

			// SMM, 23/01/2023
			<?php if (isset($_GET['a'])) {?>
				frame.src="detalle_traslado_inventario.php";
			<?php } else {?>
				// Antiguo fragmento de código
				<?php if ($edit == 0) {?>
					if(carcode!="") {
						frame.src="detalle_traslado_inventario.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+carcode;
					}else{
						frame.src="detalle_traslado_inventario.php";
					}
				<?php } else {?>
					if(carcode!="") {
						frame.src="detalle_traslado_inventario.php?id=<?php echo base64_encode($row['ID_TrasladoInv']); ?>&evento=<?php echo base64_encode($row['IdEvento']); ?>&docentry=<?php echo base64_encode($row['DocEntry']); ?>&type=2";
					}else{
						frame.src="detalle_traslado_inventario.php";
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
				}
				, error: function(error){
					console.log("798->", error.responseText);

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
				}
				, error: function(error){
					console.log("820->", error.responseText);

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
}?> // SMM, 29/08/2022

		$("#Serie").change(function() {
			$('.ibox-content').toggleClass('sk-loading',true);

			console.log("SDim Message,\n<?php echo $console_Msg; ?>"); // SMM, 29/08/2022

			var Serie=document.getElementById('Serie').value;
			var SDim = document.getElementById('<?php echo $SDimPO; ?>').value; // SMM, 29/08/2022

			$.ajax({
				type: "POST",
				url: `ajx_cbo_select.php?type=19&id=${Serie}&SDim=${SDim}`, // SMM, 29/08/2022
				success: function(response){
					$('#<?php echo $SDimPO; ?>').html(response).fadeIn(); // SMM, 29/08/2022
					$('#<?php echo $SDimPO; ?>').trigger('change'); // SMM, 29/08/2022

					$('.ibox-content').toggleClass('sk-loading',false);
				},
				error: function(error) {
					console.log("Line 903", error.responseText);

					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});

// Actualización de las dimensiones dinámicamente, SMM 10/10/2022
<?php foreach ($array_Dimensiones as &$dim) {?>

<?php $Name_IdDoc = "ID_TrasladoInv";?>
<?php $DimCode = intval($dim['DimCode']);?>
<?php $OcrId = ($DimCode == 1) ? "" : $DimCode;?>

$("#<?php echo $dim['IdPortalOne']; ?>").change(function() {

	var docType = 6;
	var detalleDoc = "detalle_traslado_inventario.php";

	var frame = document.getElementById('DataGrid');
	var DimIdPO = document.getElementById('<?php echo $dim['IdPortalOne']; ?>').value;

	<?php if ($DimCode == $DimSeries) {?>
		$('.ibox-content').toggleClass('sk-loading',true);

		let tDoc = 67;
		let Serie = document.getElementById('Serie').value;

		var url20 = `ajx_cbo_select.php?type=20&id=${DimIdPO}&serie=${Serie}&tdoc=${tDoc}&WhsCode=<?php echo isset($_GET['Almacen']) ? base64_decode($_GET['Almacen']) : ($row['WhsCode'] ?? ""); ?>&ToWhsCode=<?php echo isset($_GET['AlmacenDestino']) ? base64_decode($_GET['AlmacenDestino']) : ($row['ToWhsCode'] ?? ""); ?>`;

		$.ajax({
			type: "POST",
			url: url20,
			success: function(response){
				// console.log(url20);
				// console.log("ajx_cbo_select.php?type=20");

				console.log("Cargando almacenes origen...");

				$('#Almacen').html(response).fadeIn();
				// $('#Almacen').trigger('change');

				$('.ibox-content').toggleClass('sk-loading',false);
			},
			error: function(error) {
				// Mensaje de error
				console.log("Line 869", error.responseText);

				$('.ibox-content').toggleClass('sk-loading', false);
			}
		});

		$.ajax({
				type: "POST",
				url: `${url20}&twhs=2`,
				success: function(response){
					console.log("Cargando almacenes destino...");

					$('#AlmacenDestino').html(response).fadeIn();
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

		$("#Almacen").change(function(){
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
							url: "registro.php?P=36&doctype=6&type=1&name=WhsCode&value="+Base64.encode(document.getElementById('Almacen').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_traslado_inventario.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=6&type=2&name=WhsCode&value="+Base64.encode(document.getElementById('Almacen').value)+"&line=0&id=<?php echo $row['ID_TrasladoInv']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_traslado_inventario.php?id=<?php echo base64_encode($row['ID_TrasladoInv']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }?>
					}
				});
			}

//			$('.ibox-content').toggleClass('sk-loading',true);
//			var carcode=document.getElementById('CardCode').value;
//			var almacen=document.getElementById('Almacen').value;
//			var almacendestino=document.getElementById('AlmacenDestino').value;
//			var frame=document.getElementById('DataGrid');
//			if(carcode!="" && almacen!="" && almacendestino!=""){
//				frame.src="detalle_traslado_inventario.php?id=0&type=1&usr=<?php //echo $_SESSION['CodUser'];?>&cardcode="+carcode+"&whscode="+almacen+"&towhscode="+almacendestino;
//			}else{
//				frame.src="detalle_traslado_inventario.php";
//			}
//			$('.ibox-content').toggleClass('sk-loading',false);
		});

		$("#AlmacenDestino").change(function(){
			var frame=document.getElementById('DataGrid');
			if(document.getElementById('AlmacenDestino').value!=""&&document.getElementById('CardCode').value!=""&&document.getElementById('TotalItems').value!="0"){
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
							url: "registro.php?P=36&doctype=6&type=1&name=ToWhsCode&value="+Base64.encode(document.getElementById('AlmacenDestino').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_traslado_inventario.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=6&type=2&name=ToWhsCode&value="+Base64.encode(document.getElementById('AlmacenDestino').value)+"&line=0&id=<?php echo $row['ID_TrasladoInv']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_traslado_inventario.php?id=<?php echo base64_encode($row['ID_TrasladoInv']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php }?>
					}
				});
			}
		});

//		$("#TipoEntrega").change(function(){
//			$('.ibox-content').toggleClass('sk-loading',true);
//			var TipoEnt=document.getElementById('TipoEntrega').value;
//			if(TipoEnt==2||TipoEnt==3||TipoEnt==4){
//				document.getElementById('dv_AnioEnt').style.display='block';
//			}else{
//				document.getElementById('dv_AnioEnt').style.display='none';
//			}
//			$('.ibox-content').toggleClass('sk-loading',false);
//		});

		// Actualización del proyecto en las líneas, SMM 29/11/2022
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
							url: "registro.php?P=36&doctype=6&type=1&name=PrjCode&value="+Base64.encode(document.getElementById('PrjCode').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_traslado_inventario.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=6&type=2&name=PrjCode&value="+Base64.encode(document.getElementById('PrjCode').value)+"&line=0&id=<?php echo $row['ID_TrasladoInv']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_traslado_inventario.php?id=<?php echo base64_encode($row['ID_TrasladoInv']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
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
							url: "registro.php?P=36&doctype=6&type=1&name=ConceptoSalida&value="+Base64.encode(document.getElementById('ConceptoSalida').value)+"&line=0&cardcode="+document.getElementById('CardCode').value+"&whscode=0&actodos=1",
							success: function(response){
								frame.src="detalle_traslado_inventario.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser']; ?>&cardcode="+document.getElementById('CardCode').value;
								$('.ibox-content').toggleClass('sk-loading',false);
							}
						});
						<?php } else {?>
						$.ajax({
							type: "GET",
							url: "registro.php?P=36&doctype=6&type=2&name=ConceptoSalida&value="+Base64.encode(document.getElementById('ConceptoSalida').value)+"&line=0&id=<?php echo $row['ID_TrasladoInv']; ?>&evento=<?php echo $IdEvento; ?>&actodos=1",
							success: function(response){
								frame.src="detalle_traslado_inventario.php?id=<?php echo base64_encode($row['ID_TrasladoInv']); ?>&evento=<?php echo base64_encode($IdEvento); ?>&type=2";
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
                    <h2>Traslado de inventario</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Inventario</a>
                        </li>
                        <li class="active">
                            <strong>Traslado de inventario</strong>
                        </li>
                    </ol>
                </div>
            </div>

         <div class="wrapper wrapper-content">
			<!-- SMM, 29/08/2022 -->
			<?php include_once 'md_consultar_llamadas_servicios.php';?>

			<!-- Inicio, modalAUT. SMM, 30/11/2022 -->
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
												<textarea <?php if ($edit == 1) {echo "readonly";}?> form="CrearEntregaVenta" class="form-control required" name="ComentariosAutor" id="ComentariosAutor" type="text" maxlength="250" rows="4"><?php if ($edit == 1 || $sw_error == 1) {echo $row['ComentariosAutor'];} elseif (isset($_GET['ComentariosAutor'])) {echo base64_decode($_GET['ComentariosAutor']);}?></textarea>
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
									<?php if ($edit == 0) {?>
										<button type="button" class="btn btn-success m-t-md" id="formAUT_button"><i class="fa fa-check"></i> Enviar</button>
									<?php }?>
									<button type="button" class="btn btn-warning m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
								</div>
							<!-- /form -->
						</div>
					</div>
				</div>
			<?php }?>
			<!-- Fin, modalAUT. SMM, 30/11/2022 -->

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
									<?php $SQL_Formato = Seleccionar('uvw_tbl_FormatosSAP', '*', "ID_Objeto=67 AND (IdFormato='" . $row['IdSeries'] . "' OR DeSeries IS NULL) AND VerEnDocumento='Y' AND (EsBorrador='N' OR EsBorrador IS NULL)");?>
									<?php while ($row_Formato = sqlsrv_fetch_array($SQL_Formato)) {?>
										<li>
											<a class="dropdown-item" target="_blank" href="sapdownload.php?type=<?php echo base64_encode('2'); ?>&id=<?php echo base64_encode('15'); ?>&ObType=<?php echo base64_encode($row_Formato['ID_Objeto']); ?>&IdFrm=<?php echo base64_encode($row_Formato['IdFormato']); ?>&DocKey=<?php echo base64_encode($row['DocEntry']); ?>&IdReg=<?php echo base64_encode($row_Formato['ID']); ?>"><?php echo $row_Formato['NombreVisualizar']; ?></a>
										</li>
									<?php }?>
								</ul>
							</div>
							<!-- Hasta aquí, 22/02/2023 -->

							<a href="#" class="btn btn-info btn-outline" onClick="VerMapaRel('<?php echo base64_encode($row['DocEntry']); ?>','<?php echo base64_encode('67'); ?>');"><i class="fa fa-sitemap"></i> Mapa de relaciones</a>
						</div>
						<div class="col-lg-6">
							<?php if ($row['DocDestinoDocEntry'] != "") {?>
								<a href="salida_inventario.php?id=<?php echo base64_encode($row['DocDestinoDocEntry']); ?>&id_portal=<?php echo base64_encode($row['DocDestinoIdPortal']); ?>&tl=1" target="_blank" class="btn btn-outline btn-success pull-right m-l-sm">Ir a documento destino <i class="fa fa-mail-forward"></i></a>
							<?php }?>
							<?php if ($row['DocBaseDocEntry'] != "") {?>
								<a href="solicitud_traslado.php?id=<?php echo base64_encode($row['DocBaseDocEntry']); ?>&id_portal=<?php echo base64_encode($row['DocBaseIdPortal']); ?>&tl=1" target="_blank" class="btn btn-outline btn-success pull-right m-l-sm"><i class="fa fa-mail-reply"></i> Ir a documento base</i></a>
							<?php }?>
							<button type="button" onClick="javascript:location.href='actividad.php?dt_DM=1&Cardcode=<?php echo base64_encode($row['CardCode']); ?>&Contacto=<?php echo base64_encode($row['CodigoContacto']); ?>&Sucursal=<?php echo base64_encode($row['SucursalDestino']); ?>&Direccion=<?php echo base64_encode($row['DireccionDestino']); ?>&DM_type=<?php echo base64_encode('67'); ?>&DM=<?php echo base64_encode($row['DocEntry']); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('traslado_inventario.php'); ?>'" class="alkin btn btn-outline btn-primary pull-right"><i class="fa fa-plus-circle"></i> Agregar actividad</button>
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
              <form action="traslado_inventario.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="CrearTrasladoInventario">
				    <?php
$_GET['obj'] = "67";
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
							<input name="CardCode" type="hidden" id="CardCode" value="<?php if (($edit == 1) || ($sw_error == 1)) {echo $row['CardCode'];} elseif ($dt_SS == 1) {echo $row_Cliente['CodigoCliente'];} elseif (($edit == 0) && ($ClienteDefault != "")) {echo $ClienteDefault;}?>">

							<input autocomplete="off" name="CardName" type="text" required="required" class="form-control" id="CardName" placeholder="Digite para buscar..." value="<?php if (($edit == 1) || ($sw_error == 1)) {echo $row['NombreCliente'];} elseif ($dt_SS == 1) {echo $row_Cliente['NombreCliente'];} elseif (($edit == 0) && ($ClienteDefault != "")) {echo $NombreClienteDefault;}?>" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || ($dt_SS == 1) || ($edit == 1)) {echo "readonly";}?>>
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
					</div>

					<div class="form-group">
						<label class="col-lg-1 control-label">Sucursal destino</label>
						<div class="col-lg-5">
							<select name="SucursalDestino" class="form-control select2" id="SucursalDestino" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							  <?php if (($edit == 0) && ($dt_SS == 0)) {?><option value="">Seleccione...</option><?php }?>
							  <?php if (($edit == 1) || ($sw_error == 1) || ($dt_SS == 1)) {while ($row_SucursalDestino = sqlsrv_fetch_array($SQL_SucursalDestino)) {?>
									<option value="<?php echo $row_SucursalDestino['NombreSucursal']; ?>" <?php if ((isset($row['SucursalDestino'])) && (strcmp($row_SucursalDestino['NombreSucursal'], $row['SucursalDestino']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['Sucursal']) && (strcmp($row_SucursalDestino['NombreSucursal'], base64_decode($_GET['Sucursal'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_SucursalDestino['NombreSucursal']; ?></option>
							  <?php }}?>
							</select>
						</div>
						<label class="col-lg-1 control-label">Sucursal facturación</label>
						<div class="col-lg-5">
							<select name="SucursalFacturacion" class="form-control select2" id="SucursalFacturacion" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							  <option value="">Seleccione...</option>
							  <?php if (($edit == 1) || ($sw_error == 1) || ($dt_SS == 1)) {while ($row_SucursalFacturacion = sqlsrv_fetch_array($SQL_SucursalFacturacion)) {?>
									<option value="<?php echo $row_SucursalFacturacion['NombreSucursal']; ?>" <?php if ((isset($row['SucursalFacturacion'])) && (strcmp($row_SucursalFacturacion['NombreSucursal'], $row['SucursalFacturacion']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['SucursalFact']) && (strcmp($row_SucursalFacturacion['NombreSucursal'], base64_decode($_GET['SucursalFact'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_SucursalFacturacion['NombreSucursal']; ?></option>
							  <?php }}?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Dirección destino</label>
						<div class="col-lg-5">
							<input type="text" class="form-control" name="DireccionDestino" id="DireccionDestino" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['DireccionDestino'];} elseif ($dt_SS == 1 && isset($_GET['Direccion'])) {echo base64_decode($_GET['Direccion']);}?>" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "readonly";}?>>
						</div>
						<label class="col-lg-1 control-label">Dirección facturación</label>
						<div class="col-lg-5">
							<input type="text" class="form-control" name="DireccionFacturacion" id="DireccionFacturacion" value="<?php if ($edit == 1 || $sw_error == 1) {echo $row['DireccionFacturacion'];}?>" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "readonly";}?>>
						</div>
					</div>

					<!-- SMM, 29/08/2022 -->
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
						<label class="col-lg-5">Fecha de requerida salida <span class="text-danger">*</span></label>
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
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Datos del traslado</h3></label>
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

					<!-- SMM, 31/03/2023 -->
					<label class="col-lg-1 control-label">Condición de pago</label>
					<div class="col-lg-3">
						<select name="CondicionPago" class="form-control" id="CondicionPago" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>

							<?php while ($row_CondicionPago = sqlsrv_fetch_array($SQL_CondicionPago)) {?>
								<option value="<?php echo $row_CondicionPago['IdCondicionPago']; ?>" <?php if (isset($row['IdCondicionPago']) && (strcmp($row_CondicionPago['IdCondicionPago'], $row['IdCondicionPago']) == 0)) {echo "selected";} elseif ((isset($_GET['CondicionPago'])) && (strcmp($row_CondicionPago['IdCondicionPago'], base64_decode($_GET['CondicionPago'])) == 0)) {echo "selected";}?>><?php echo $row_CondicionPago['NombreCondicion']; ?></option>
						  	<?php }?>
						</select>
				  	</div>
					<!-- Hasta aquí -->
				</div>

				<!-- Dimensiones dinámicas, SMM 29/08/2022 -->
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
									<?php echo $row_Dim['OcrCode'] . "-" . $row_Dim['OcrName']; ?>
								</option>
							<?php }?>
							</select>
						</div>
					<?php }?>
				</div>
				<!-- Dimensiones dinámicas, hasta aquí -->

				<div class="form-group">
					<label class="col-lg-1 control-label">Almacén origen <span class="text-danger">*</span></label>
					<div class="col-lg-3">
						<select name="Almacen" class="form-control" id="Almacen" required="required" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
						  <?php if ($edit == 1) {
    while ($row_Almacen = sqlsrv_fetch_array($SQL_Almacen)) {?>
									<option value="<?php echo $row_Almacen['WhsCode']; ?>" <?php if (($edit == 1) && (isset($row['WhsCode'])) && (strcmp($row_Almacen['WhsCode'], $row['WhsCode']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Almacen['WhsName']; ?></option>
						  <?php }
}?>
						</select>
					</div>

					<label class="col-lg-1 control-label">Almacén destino <span class="text-danger">*</span></label>
					<div class="col-lg-3">
						<select name="AlmacenDestino" class="form-control" id="AlmacenDestino" required="required" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
						  <?php if ($edit == 1) {?>
							<?php while ($row_AlmacenDestino = sqlsrv_fetch_array($SQL_AlmacenDestino)) {?>
								<option value="<?php echo $row_AlmacenDestino['ToWhsCode']; ?>" <?php if (($edit == 1) && (isset($row['ToWhsCode'])) && (strcmp($row_AlmacenDestino['ToWhsCode'], $row['ToWhsCode']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_AlmacenDestino['ToWhsName']; ?></option>
						  	<?php }?>
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
									<?php echo $row_Proyecto['IdProyecto'] . "-" . $row_Proyecto['DeProyecto']; ?>
								</option>
							<?php }?>
						</select>
					</div>
					<!-- Fin, Proyecto -->
				</div>

				<div class="form-group">
					<!-- Inicio, Empleado -->
					<label class="col-lg-1 control-label">Solicitado para</label>
					<div class="col-lg-3">
						<select name="Empleado" class="form-control" id="Empleado" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
							<?php while ($row_Empleado = sqlsrv_fetch_array($SQL_Empleado)) {?>
								<option value="<?php echo $row_Empleado['ID_Empleado']; ?>" <?php if ((isset($row['CodEmpleado'])) && (strcmp($row_Empleado['ID_Empleado'], $row['CodEmpleado']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['Empleado']) && (strcmp($row_Empleado['ID_Empleado'], base64_decode($_GET['Empleado'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Empleado['NombreEmpleado']; ?></option>
							<?php }?>
						</select>
					</div>
					<!-- Hasta aquí. SMM, 25/01/2023 -->

					<!-- Inicio, TipoEntrega -->
					<label class="col-lg-1 control-label">Tipo entrega</label>
					<div class="col-lg-3">
						<select name="TipoEntrega" class="form-control" id="TipoEntrega" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
							<?php while ($row_TipoEntrega = sqlsrv_fetch_array($SQL_TipoEntrega)) {?>
								<option value="<?php echo $row_TipoEntrega['IdTipoEntrega']; ?>" <?php if ((isset($row['IdTipoEntrega'])) && (strcmp($row_TipoEntrega['IdTipoEntrega'], $row['IdTipoEntrega']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['TipoEntrega']) && (strcmp($row_TipoEntrega['IdTipoEntrega'], base64_decode($_GET['TipoEntrega'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_TipoEntrega['DeTipoEntrega']; ?></option>
							<?php }?>
						</select>
					</div>
					<div id="dv_AnioEnt" style="display: none;">
						<label class="col-lg-1 control-label">Año entrega</label>
						<div class="col-lg-2">
							<select name="AnioEntrega" class="form-control" id="AnioEntrega" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							<?php while ($row_AnioEntrega = sqlsrv_fetch_array($SQL_AnioEntrega)) {?>
								<option value="<?php echo $row_AnioEntrega['IdAnioEntrega']; ?>" <?php if ((isset($row['IdAnioEntrega'])) && (strcmp($row_AnioEntrega['IdAnioEntrega'], $row['IdAnioEntrega']) == 0)) {echo "selected=\"selected\"";} elseif (isset($_GET['AnioEntrega']) && (strcmp($row_AnioEntrega['IdAnioEntrega'], base64_decode($_GET['AnioEntrega'])) == 0)) {echo "selected=\"selected\"";} elseif (date('Y') == $row_AnioEntrega['DeAnioEntrega']) {echo "selected=\"selected\"";}?>><?php echo $row_AnioEntrega['DeAnioEntrega']; ?></option>
								<?php }?>
							</select>
						</div>
					</div>
					<!-- Hasta aquí. SMM, 25/01/2023 -->

					<!-- SMM, 30/11/2022 -->
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
					<!-- Hasta aquí, 30/11/2022 -->

				</div> <!-- form-group -->

				<div class="form-group">

					<!-- SMM, 23/12/2022 -->
					<label class="col-lg-1 control-label">Concepto Salida</label>
					<div class="col-lg-3">
						<select name="ConceptoSalida" class="form-control select2" id="ConceptoSalida" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
								<option value="">Seleccione...</option>
								<?php while ($row_ConceptoSalida = sqlsrv_fetch_array($SQL_ConceptoSalida)) {?>
									<option value="<?php echo $row_ConceptoSalida['id_concepto_salida']; ?>" <?php if ((isset($row['ConceptoSalida'])) && (strcmp($row_ConceptoSalida['id_concepto_salida'], $row['ConceptoSalida']) == 0)) {echo "selected";} elseif ((isset($_GET['ConceptoSalida'])) && (strcmp($row_ConceptoSalida['id_concepto_salida'], base64_decode($_GET['ConceptoSalida'])) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_ConceptoSalida['id_concepto_salida'] . "-" . $row_ConceptoSalida['concepto_salida']; ?></option>
								<?php }?>
						</select>
					</div>
					<!-- Hasta aquí, 23/12/2022 -->

				</div> <!-- form-group -->

				<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Contenido del traslado</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Buscar articulo</label>
					<div class="col-lg-4">
                    	<input name="BuscarItem" id="BuscarItem" type="text" class="form-control" placeholder="Escriba para buscar..." onBlur="javascript:BuscarArticulo(this.value);" <?php if ((($edit == 1) && ($row['Cod_Estado'] == 'C')) || (!PermitirFuncion(1203))) {echo "readonly";}?>>
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
							<iframe id="DataGrid" name="DataGrid" style="border: 0;" width="100%" height="300" src="<?php if ($edit == 0 && $sw_error == 0) {echo "detalle_traslado_inventario.php";} elseif ($edit == 0 && $sw_error == 1) {echo "detalle_traslado_inventario.php?id=0&type=1&usr=" . $_SESSION['CodUser'] . "&cardcode=" . $row['CardCode'];} else {echo "detalle_traslado_inventario.php?id=" . base64_encode($row['ID_TrasladoInv']) . "&evento=" . base64_encode($row['IdEvento']) . "&docentry=" . base64_encode($row['DocEntry']) . "&type=2&status=" . base64_encode($row['Cod_Estado']);}?>"></iframe>
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
    LimpiarDirTemp();
    if ($row['IdAnexo'] != 0) {?>
										<div class="form-group">
											<div class="col-xs-12">
												<?php while ($row_Anexo = sqlsrv_fetch_array($SQL_Anexo)) {
        $Icon = IconAttach($row_Anexo['FileExt']);
        $tmp = substr($row_Anexo['NombreArchivo'], 0, 4);
        if ($tmp == "Sig_") {
            $NameFirma = $row_Anexo['NombreArchivo'];
        }?>
													<div class="file-box">
														<div class="file">
															<a href="attachdownload.php?file=<?php echo base64_encode($row_Anexo['AbsEntry']); ?>&line=<?php echo base64_encode($row_Anexo['Line']); ?>" target="_blank">
																<div class="icon">
																	<i class="<?php echo $Icon; ?>"></i>
																</div>
																<div class="file-name">
																	<?php echo $row_Anexo['NombreArchivo']; ?>
																	<br/>
																	<small><?php echo $row_Anexo['Fecha']; ?></small>
																</div>
															</a>
														</div>
													</div>
												<?php }?>
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
						<label class="col-lg-2">Encargado del departamento <span class="text-danger">*</span></label>
						<div class="col-lg-5">
							<select name="EmpleadoVentas" class="form-control" id="EmpleadoVentas" form="CrearTrasladoInventario" required="required" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "disabled='disabled'";}?>>
							  <?php while ($row_EmpleadosVentas = sqlsrv_fetch_array($SQL_EmpleadosVentas)) {?>
									<option value="<?php echo $row_EmpleadosVentas['ID_EmpVentas']; ?>" <?php if ($edit == 0) {if (($_SESSION['CodigoEmpVentas'] != "") && (strcmp($row_EmpleadosVentas['ID_EmpVentas'], $_SESSION['CodigoEmpVentas']) == 0)) {echo "selected=\"selected\"";}} elseif ($edit == 1) {if (($row['SlpCode'] != "") && (strcmp($row_EmpleadosVentas['ID_EmpVentas'], $row['SlpCode']) == 0)) {echo "selected=\"selected\"";}}?>><?php echo $row_EmpleadosVentas['DE_EmpVentas']; ?></option>
							  <?php }?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-2">Comentarios</label>
						<div class="col-lg-10">
							<textarea type="text" maxlength="2000" name="Comentarios" form="CrearTrasladoInventario" rows="4" id="Comentarios" class="form-control" <?php if (($edit == 1) && ($row['Cod_Estado'] == 'C')) {echo "readonly";}?>><?php if ($edit == 1 || $sw_error == 1) {echo $row['Comentarios'];} elseif (isset($_GET['Comentarios'])) {echo base64_decode($_GET['Comentarios']);}?></textarea>
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
							<input type="text" name="SubTotal" form="CrearTrasladoInventario" id="SubTotal" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['SubTotal'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Descuentos</strong></label>
						<div class="col-lg-5">
							<input type="text" name="Descuentos" form="CrearTrasladoInventario" id="Descuentos" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['DiscSum'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">IVA</strong></label>
						<div class="col-lg-5">
							<input type="text" name="Impuestos" form="CrearTrasladoInventario" id="Impuestos" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['VatSum'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-7"><strong class="pull-right">Total</strong></label>
						<div class="col-lg-5">
							<input type="text" name="TotalTraslado" form="CrearTrasladoInventario" id="TotalTraslado" class="form-control" style="text-align: right; font-weight: bold;" value="<?php if ($edit == 1) {echo number_format($row['DocTotal'], 0);} else {echo "0.00";}?>" readonly>
						</div>
					</div>
				</div>

				<!-- SMM, 16/07/2023 -->
				<?php if ($edit == 1) {?>
					<div class="col-lg-12">
						<div class="form-group">
							<div class="col-lg-6 border-bottom ">
								<label class="control-label text-danger">Información de quien recibe</label>
							</div>
						</div>

						<?php // if (PermitirFuncion(1213)) {?>
							<div class="form-group">
								<div class="col-lg-5">
									<label class="control-label">Nombre de quien recibe <span class="text-danger cierre-span">*</span></label>
									<input form="CrearTrasladoInventario" autocomplete="off" name="NombreRecibeFirma" type="text" class="form-control cierre-input" id="NombreRecibeFirma" maxlength="200" value="<?php echo $row['NombreRecibeFirma'] ?? ""; ?>" required <?php if ($NameFirma != "") {echo "readonly";}?>>
								</div>
								<div class="col-lg-5">
									<label class="control-label">Cédula de quien recibe</label>
									<input form="CrearTrasladoInventario" autocomplete="off" name="CedulaRecibeFirma" type="number" class="form-control cierre-input" id="CedulaRecibeFirma" maxlength="20" value="<?php echo $row['CedulaRecibeFirma'] ?? ""; ?>" <?php if ($NameFirma != "") {echo "readonly";}?>>
								</div>
							</div>
						<?php // }?>

						<!-- Componente "firma"-->
						<div class="form-group">
							<label class="col-lg-2">Firma de quien recibe</label>
							<?php if ($NameFirma != "") {?>
							<div class="col-lg-10">
								<span class="badge badge-primary">Firmado</span>
							</div>
							<?php } elseif ($PuedeFirmar == 1) {LimpiarDirTempFirma();?>
							<div class="col-lg-5">
								<button class="btn btn-primary" type="button" id="FirmaCliente" onClick="AbrirFirma('SigRecibe');"><i class="fa fa-pencil-square-o"></i> Realizar firma</button>
								<input type="hidden" id="SigRecibe" name="SigRecibe" value="" form="CrearTrasladoInventario" />
								<div id="msgInfoSigRecibe" style="display: none;" class="alert alert-info"><i class="fa fa-info-circle"></i> El documento ya ha sido firmado.</div>
							</div>
							<div class="col-lg-5">
								<img id="ImgSigRecibe" style="display: none; max-width: 100%; height: auto;" src="" alt="" />
							</div>
							<?php } else {?>
								<div class="col-lg-10">
									<span class="badge badge-warning">Usuario no autorizado para firmar</span>
								</div>
							<?php }?>
						</div>
						<!-- Hasta aquí -->
						<br><br>
					</div>
				<?php }?>
				<!-- Hasta aquí, 16/07/2023 -->

				<div class="form-group">
					<div class="col-lg-9">
						<?php if ($edit == 0 && PermitirFuncion(1203)) {?>
							<button class="btn btn-primary" type="submit" form="CrearTrasladoInventario" id="Crear"><i class="fa fa-check"></i> Crear Traslado</button>
						<?php } elseif (($edit == 1) && (($row['Cod_Estado'] == "O" && PermitirFuncion(1203)) || (($NameFirma == "") && ($PuedeFirmar == 1)))) {?>
							<button class="btn btn-warning" type="submit" form="CrearTrasladoInventario" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar Traslado</button>
						<?php }?>
						<?php
if (isset($_GET['return'])) {
    $return = base64_decode($_GET['pag']) . "?" . base64_decode($_GET['return']);
} elseif (isset($_POST['return'])) {
    $return = base64_decode($_POST['return']);
} else {
    $return = "traslado_inventario.php?";
}
$return = QuitarParametrosURL($return, array("a"));
?>
						<a href="<?php echo $return; ?>" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
					</div>

<!-- Dimensiones dinámicas, SMM 29/08/2022 -->
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
<!-- Hasta aquí, 29/08/2022 -->

					<?php if (($edit == 1) && ($row['DocDestinoDocEntry'] == "") && ($NameFirma != "")) {?>
					<div class="col-lg-3">
						<div class="btn-group pull-right">
                            <button data-toggle="dropdown" class="btn btn-success dropdown-toggle"><i class="fa fa-mail-forward"></i> Copiar a <i class="fa fa-caret-down"></i></button>
                            <ul class="dropdown-menu">
                                <li><a class="alkin dropdown-item" href="salida_inventario.php?dt_TI=1&DocEntry=<?php echo base64_encode($row['DocEntry']); ?>&Cardcode=<?php echo base64_encode($row['CardCode']); ?>&Dim1=<?php echo base64_encode($row['OcrCode']); ?>&Dim2=<?php echo base64_encode($row['OcrCode2']); ?>&Dim3=<?php echo base64_encode($row['OcrCode3']); ?>&SucursalFact=<?php echo base64_encode($row['SucursalFacturacion']); ?>&Sucursal=<?php echo base64_encode($row['SucursalDestino']); ?>&Direccion=<?php echo base64_encode($row['DireccionDestino']); ?>&Almacen=<?php echo base64_encode($row['ToWhsCode']); ?>&AlmacenDestino=<?php echo base64_encode($row['ToWhsCode']); ?>&Contacto=<?php echo base64_encode($row['CodigoContacto']); ?>&Empleado=<?php echo base64_encode($row['CodEmpleado']); ?>&TipoEntrega=<?php echo base64_encode($row['IdTipoEntrega']); ?>&AnioEntrega=<?php echo base64_encode($row['IdAnioEntrega']); ?>&TI=<?php echo base64_encode($row['ID_TrasladoInv']); ?>&Evento=<?php echo base64_encode($row['IdEvento']); ?>&Proyecto=<?php echo base64_encode($row['PrjCode']); ?>&ConceptoSalida=<?php echo base64_encode($row['ConceptoSalida']); ?>&CondicionPago=<?php echo base64_encode($row['IdCondicionPago']); ?>">Salida de inventario</a></li>
                            </ul>
                        </div>
					</div>
					<?php }?>
				</div>
				<input type="hidden" form="CrearTrasladoInventario" id="P" name="P" value="52" />
				<input type="hidden" form="CrearTrasladoInventario" id="IdTrasladoInv" name="IdTrasladoInv" value="<?php if ($edit == 1) {echo base64_encode($row['ID_TrasladoInv']);}?>" />
				<input type="hidden" form="CrearTrasladoInventario" id="IdEvento" name="IdEvento" value="<?php if ($edit == 1) {echo base64_encode($IdEvento);}?>" />
				<input type="hidden" form="CrearTrasladoInventario" id="tl" name="tl" value="<?php echo $edit; ?>" />
				<input type="hidden" form="CrearTrasladoInventario" id="dt_SS" name="dt_SS" value="<?php echo $dt_SS; ?>" />
				<input type="hidden" form="CrearTrasladoInventario" id="swError" name="swError" value="<?php echo $sw_error; ?>" />
				<input type="hidden" form="CrearTrasladoInventario" id="return" name="return" value="<?php echo base64_encode($return); ?>" />
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
		maxLength('Comentarios'); // SMM, 17/02/2023

		// SMM, 20/01/2023
		<?php if (($edit == 0) && ($ClienteDefault != "")) {?>
			$("#CardCode").change();
		<?php }?>

		$("#CrearTrasladoInventario").validate({
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

		// Mostrar modal NO se cumplen las condiciones. SMM, 30/11/2022
		<?php if ($success == 0) {?>
			$('#modalAUT').modal('show');
		<?php }?>
		// Hasta aquí, 30/11/2022

		// Almacenar campos de autorización. SMM, 30/11/2022
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
				if($("#Autorizacion").val() == "N") {
					$("#Autorizacion").val("P").change();

					// Corregir valores nulos en el combo de autorización.
					$('#Autorizacion option:selected').attr('disabled', false);
					$('#Autorizacion option:not(:selected)').attr('disabled', true);
				}
				$('#modalAUT').modal('hide');
			}
		});
		// Almacenar campos autorización, hasta aquí.

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
	 	<?php }?>

		<?php if ($dt_SS == 1) {?>
			// $('#Empleado option:not(:selected)').attr('disabled',true);
		<?php }?>

		// $('#Autorizacion option:not(:selected)').attr('disabled',true);

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

		 <?php if ($dt_SS == 1) {?>
			$('#CardCode').trigger('change');

			// SMM, 01/12/2022
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
				url: "dm_actividades.php?id=<?php if ($edit == 1) {echo base64_encode($row['DocEntry']);}?>&objtype=67",
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
	var almacen= document.getElementById("Almacen").value;
	var almacendestino= document.getElementById("AlmacenDestino").value;

	if(almacen==almacendestino){
		result=false;
		Swal.fire({
			title: '¡Lo sentimos!',
			text: 'No puede realizar transferencia entre los mismos almacenes.',
			icon: 'error'
		});
	}


	//Validar si fue actualizado por otro usuario
//	$.ajax({
//		url:"ajx_buscar_datos_json.php",
//		data:{type:15,
//			  docentry:'<?php //if($edit==1){echo base64_encode($row['DocEntry']);}?>',
//			  objtype:'67',
//			  date:'<?php //echo FormatoFecha(date('Y-m-d'),date('H:i:s'));?>'},
//		dataType:'json',
//		async: false,
//		success: function(data){
//			if(data.Result!=1){
//				result=false;
//				Swal.fire({
//					title: '¡Lo sentimos!',
//					text: 'Este documento ya fue actualizado por otro usuario. Debe recargar la página para volver a cargar los datos.',
//					icon: 'error'
//				});
//			}
//		}
//	 });

	<?php if ($edit == 1) {?>
	//Firma
//	if(document.getElementById("SigRecibe")){
//		var AnxFirma = document.getElementById("SigRecibe");
//		if(AnxFirma.value==""){
//			result=false;
//			Swal.fire({
//				title: '¡Advertencia!',
//				text: 'No se ha firmado el documento. Por favor verifique.',
//				icon: 'warning'
//			});
//		}
//	}
	<?php }?>

	<?php if ($edit == 0) {?>
		//Validar que los items con lote ya fueron seleccionados
		var Cliente=document.getElementById('CardCode').value;
		var almacen=document.getElementById('Almacen').value;
		$.ajax({
			url:"ajx_buscar_datos_json.php",
			data:{type:17,
				  cardcode:Cliente,
				  objtype:67,
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
				  objtype:67,
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

					console.log("Cantidad solicitada diferente a cantidad total de salida");
					console.log(`ERROR, ${data.CantSolicitada} != ${data.CantTotalSalida}`);
				}
			}
		});

		$.ajax({
			url:"ajx_buscar_datos_json.php",
			data:{type:27,
				  cardcode:Cliente,
				  objtype:67,
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

		$.ajax({
			url:"ajx_buscar_datos_json.php",
			data:{
				type:43,
				cardcode:Cliente
			},
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