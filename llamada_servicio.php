<?php require_once "includes/conexion.php";
PermitirAcceso(303);
$IdLlamada = "";

$msg = ""; // Mensaje OK, 14/09/2022
$msg_error = ""; //Mensaje del error

if (isset($_GET['msg']) && ($_GET['msg'] != "")) {
    $msg = base64_decode($_GET['msg']);
}

$dt_LS = 0; //sw para saber si vienen datos del SN. 0 no vienen. 1 si vienen.
$sw_valDir = 0; //Validar si el nombre de la direccion cambio
$TituloLlamada = ""; //Titulo por defecto cuando se está creando la llamada de servicio

$testMode = false; // SMM, 04/03/2022

// Inicio, copiar firma a la ruta log y main. SMM, 17/09/2022
$FirmaContactoResponsable = "";
if (isset($_POST['FirmaContactoResponsable']) && ($_POST['FirmaContactoResponsable'] != "") && isset($_POST['DocNum']) && ($_POST['DocNum'] != "")) {
    $dir_name = "llamadas_servicios";
    $FirmaContactoResponsable = base64_decode($_POST['FirmaContactoResponsable']);

    $dir_log = CrearObtenerDirRuta(ObtenerVariable("RutaAnexosPortalOne") . "/" . $_SESSION['User'] . "/" . $dir_name . "/");
    $dir_main = CrearObtenerDirRuta(ObtenerVariable("RutaAnexosLlamadaServicio"));
    $source = CrearObtenerDirTempFirma() . $FirmaContactoResponsable;

    if ($testMode) {
        echo "<script> console.log('dir_log, " . str_replace("\\", "/", $dir_log) . "'); </script>";
        echo "<script> console.log('dir_main, " . str_replace("\\", "/", $dir_main) . "'); </script>";
        echo "<script> console.log('source, " . str_replace("\\", "/", $source) . "'); </script>";
    }

    // NuevoNombreArchivoFirma
    $FirmaContactoResponsable = ObtenerVariable("PrefijoFormatoLlamadaServicioPortalOne") . base64_decode($_POST['DocNum']) . "Firma.jpg";

    $dest = $dir_log . $FirmaContactoResponsable;
    copy($source, $dest);

    $dest = $dir_main . $FirmaContactoResponsable;
    copy($source, $dest);
}
// Fin, copiar firma a la ruta log y main. SMM, 17/09/2022

if (isset($_GET['id']) && ($_GET['id'] != "")) {
    $IdLlamada = base64_decode($_GET['id']);
}

if (isset($_GET['tl']) && ($_GET['tl'] != "")) { //0 Creando una llamada de servicio. 1 Editando llamada de servicio.
    $type_llmd = $_GET['tl'];
} elseif (isset($_POST['tl']) && ($_POST['tl'] != "")) {
    $type_llmd = $_POST['tl'];
} else {
    $type_llmd = 0;
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

if (isset($_POST['swError']) && ($_POST['swError'] != "")) { //Para saber si ha ocurrido un error.
    $sw_error = $_POST['swError'];
} else {
    $sw_error = 0;
}

if ($type_llmd == 0) {
    $Title = "Crear llamada de servicio";

    //Origen de llamada
    $SQL_OrigenLlamada = Seleccionar('uvw_Sap_tbl_LlamadasServiciosOrigen', '*', "Activo = 'Y'", 'DeOrigenLlamada');

    //Tipo de llamada
    $SQL_TipoLlamadas = Seleccionar('uvw_Sap_tbl_TipoLlamadas', '*', "Activo = 'Y'", 'DeTipoLlamada');

    //Tipo problema llamadas
    $SQL_TipoProblema = Seleccionar('uvw_Sap_tbl_TipoProblemasLlamadas', '*', "Activo = 'Y'", 'DeTipoProblemaLlamada');

    //SubTipo problema llamadas
    $SQL_SubTipoProblema = Seleccionar('uvw_Sap_tbl_SubTipoProblemasLlamadas', '*', "Activo = 'Y'", 'DeSubTipoProblemaLlamada');

    // SMM, 12/02/2022
} else {
    $Title = "Editar llamada de servicio";

    //Origen de llamada
    $SQL_OrigenLlamada = Seleccionar('uvw_Sap_tbl_LlamadasServiciosOrigen', '*', '', 'DeOrigenLlamada');

    //Tipo de llamada
    $SQL_TipoLlamadas = Seleccionar('uvw_Sap_tbl_TipoLlamadas', '*', '', 'DeTipoLlamada');

    //Tipo problema llamadas
    $SQL_TipoProblema = Seleccionar('uvw_Sap_tbl_TipoProblemasLlamadas', '*', '', 'DeTipoProblemaLlamada');

    //SubTipo problema llamadas
    $SQL_SubTipoProblema = Seleccionar('uvw_Sap_tbl_SubTipoProblemasLlamadas', '*', '', 'DeSubTipoProblemaLlamada');

    // SMM, 12/02/2022
}

if (isset($_POST['P']) && ($_POST['P'] == 32)) { //Crear llamada de servicio
    //Insertar llamada de servicio
    try {
        //*** Carpeta temporal ***
        $i = 0; //Archivos
        $RutaAttachSAP = ObtenerDirAttach();
        $dir = CrearObtenerDirTemp();
        $dir_new = CrearObtenerDirAnx("llamadas");
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

        $ParamInsLlamada = array(
            "NULL",
            "NULL",
            "NULL",
            "'Externa'",
            "'" . $_POST['AsuntoLlamada'] . "'",
            "'" . $_POST['Series'] . "'",
            "'" . $_POST['EstadoLlamada'] . "'",
            "'" . $_POST['OrigenLlamada'] . "'",
            "'" . $_POST['TipoLlamada'] . "'",
            "'" . $_POST['TipoProblema'] . "'",
            "'" . $_POST['SubTipoProblema'] . "'",
            "'" . $_POST['ContratoServicio'] . "'",
            "'" . $_POST['Tecnico'] . "'",
            "'" . $_POST['ClienteLlamada'] . "'",
            "'" . $_POST['ContactoCliente'] . "'",
            "'" . $_POST['TelefonoLlamada'] . "'",
            "'" . $_POST['CorreoLlamada'] . "'",
            "'" . $_POST['IdArticuloLlamada'] . "'", // SMM, 24/01/2022
            "'" . $_POST['NumeroSerie'] . "'",
            "'" . $_POST['SucursalCliente'] . "'",
            "'" . $_POST['IdSucursalCliente'] . "'",
            "'" . $_POST['DireccionLlamada'] . "'",
            "'" . $_POST['CiudadLlamada'] . "'",
            "'" . $_POST['BarrioDireccionLlamada'] . "'",
            "'" . $_POST['EmpleadoLlamada'] . "'",
            "'" . $_POST['Proyecto'] . "'",
            "'" . LSiqmlObs($_POST['ComentarioLlamada']) . "'",
            "'" . LSiqmlObs($_POST['ResolucionLlamada']) . "'",
            "'" . FormatoFecha($_POST['FechaCreacion'], $_POST['HoraCreacion']) . "'",
            "'" . FormatoFecha($_POST['FechaCierre'], $_POST['HoraCierre']) . "'",
            "'" . $_POST['IdAnexos'] . "'",
            "1",
            "'" . $_SESSION['CodUser'] . "'",
            "'" . $_SESSION['CodUser'] . "'",
            "'" . $_POST['CDU_EstadoServicio'] . "'",
            "'" . LSiqmlObs($_POST['CDU_Servicios']) . "'",
            "'" . LSiqmlObs($_POST['CDU_Areas']) . "'",
            "'" . LSiqmlObs($_POST['CDU_NombreContacto']) . "'",
            "'" . LSiqmlObs($_POST['CDU_TelefonoContacto']) . "'",
            "'" . LSiqmlObs($_POST['CDU_CargoContacto']) . "'",
            "'" . LSiqmlObs($_POST['CDU_CorreoContacto']) . "'",
            "NULL",
            "NULL",
            "NULL",
            "NULL",
            "NULL",
            "NULL",
            "'" . $_POST['CDU_CanceladoPor'] . "'",
            ($_POST['CantArticulo'] != "") ? LSiqmlValorDecimal($_POST['CantArticulo']) : 0,
            ($_POST['PrecioArticulo'] != "") ? LSiqmlValorDecimal($_POST['PrecioArticulo']) : 0,
            "1", // Tipo de SP
            // Campos nuevos
            "'" . $_POST['CDU_Marca'] . "'",
            "'" . $_POST['CDU_Linea'] . "'",
            "'" . $_POST['CDU_Ano'] . "'",
            "'" . $_POST['CDU_Concesionario'] . "'",
            "'" . $_POST['CDU_Aseguradora'] . "'",
            "'" . $_POST['CDU_TipoPreventivo'] . "'", // SMM, 14/09/2022
            "'" . $_POST['CDU_TipoServicio'] . "'",
            isset($_POST['CDU_Kilometros']) ? $_POST['CDU_Kilometros'] : 0, // int
            "'" . $_POST['CDU_Contrato'] . "'",
            "NULL", // CDU_Asesor
            "'" . $_POST['CDU_ListaMateriales'] . "'",
            isset($_POST['CDU_TiempoTarea']) ? $_POST['CDU_TiempoTarea'] : 0, // int
            "'" . $_POST['CDU_IdTecnicoAdicional'] . "'", // SMM, 25/05/2022
            "'" . FormatoFecha($_POST['FechaAgenda'], $_POST['HoraAgenda']) . "'", // SMM 01/06/2022
            "'" . FormatoFecha($_POST['FechaAgenda'], $_POST['HoraAgenda']) . "'", // SMM 01/06/2022
            (PermitirFuncion(323) && PermitirFuncion(304)) ? "1" : "0", // CreacionActividad, SMM 28/07/2022
            "0", // EnvioCorreo, SMM 28/07/2022
            "'" . ($_POST['NombreContactoFirma'] ?? "") . "'", // SMM, 18/10/2022
            "'" . ($_POST['CedulaContactoFirma'] ?? "") . "'", // SMM, 18/10/2022
            "'" . ($_POST['TelefonosContactosFirma'] ?? "") . "'", // SMM, 18/10/2022
            "'" . ($_POST['CorreosContactosFirma'] ?? "") . "'", // SMM, 18/10/2022
            "'" . $FirmaContactoResponsable . "'", // SMM, 16/09/2022
            "0", // FormatoCierreLlamada, SMM 14/10/2022
        );
        $SQL_InsLlamada = EjecutarSP('sp_tbl_LlamadaServicios', $ParamInsLlamada, 32);
        if ($SQL_InsLlamada) {
            $row_NewIdLlamada = sqlsrv_fetch_array($SQL_InsLlamada);
            $IdLlamada = $row_NewIdLlamada[0];

            try {
                //Mover los anexos a la carpeta de archivos de SAP
                $j = 0;
                while ($j < $CantFiles) {
                    $Archivo = FormatoNombreAnexo($DocFiles[$j], false);
                    $NuevoNombre = $Archivo[0];
                    $OnlyName = $Archivo[1];
                    $Ext = $Archivo[2];

                    if (file_exists($dir_new)) {
                        copy($dir . $DocFiles[$j], $dir_new . $NuevoNombre);
                        //move_uploaded_file($_FILES['FileArchivo']['tmp_name'],$dir_new.$NuevoNombre);
                        copy($dir_new . $NuevoNombre, $RutaAttachSAP[0] . $NuevoNombre);

                        //Registrar archivo en la BD
                        $ParamInsAnex = array(
                            "'191'",
                            "'" . $row_NewIdLlamada[0] . "'",
                            "'" . $OnlyName . "'",
                            "'" . $Ext . "'",
                            "1",
                            "'" . $_SESSION['CodUser'] . "'",
                            "1",
                        );
                        $SQL_InsAnex = EjecutarSP('sp_tbl_DocumentosSAP_Anexos', $ParamInsAnex, 32);
                        if (!$SQL_InsAnex) {
                            $sw_error = 1;
                            $msg_error = "Error al crear la llamada de servicio";
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
                    'id_documento' => intval($row_NewIdLlamada[0]),
                    'id_evento' => 0,
                );

                $Metodo = "LlamadasServicios";
                $Resultado = EnviarWebServiceSAP($Metodo, $Parametros, true, true); // Crear llamada en SAP

                if ($Resultado->Success == 0 || $testMode) {
                    $sw_error = 1;
                    $msg_error = $Resultado->Mensaje;
                    if ($_POST['EstadoLlamada'] == '-1') {
                        $UpdEstado = "Update tbl_LlamadasServicios Set Cod_Estado='-3' Where ID_LlamadaServicio='" . $IdLlamada . "'";
                        $SQL_UpdEstado = sqlsrv_query($conexion, $UpdEstado);
                    }
                } else {
                    $msg = base64_encode($Resultado->Mensaje); // SMM, 14/09/2022

                    //Consultar la llamada para recargarla nuevamente y poder mantenerla
                    $SQL_Llamada = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '[ID_LlamadaServicio]', "[IdLlamadaPortal]='" . $IdLlamada . "'");
                    $row_Llamada = sqlsrv_fetch_array($SQL_Llamada);
                    sqlsrv_close($conexion);
                    header("Location:llamada_servicio.php?msg=$msg&id=" . base64_encode($row_Llamada['ID_LlamadaServicio']) . '&tl=1&a=' . base64_encode("OK_LlamAdd"));
                }
            } catch (Exception $e) {
                echo 'Excepcion capturada: ', $e->getMessage(), "\n";
            }

        } else {
            $sw_error = 1;
            $msg_error = "Error al crear la llamada de servicio";
        }
    } catch (Exception $e) {
        echo 'Excepcion capturada: ', $e->getMessage(), "\n";
    }
}

if (isset($_POST['P']) && ($_POST['P'] == 33)) { //Actualizar llamada de servicio
    try {
        ///*** Carpeta temporal ***
        $i = 0; //Archivos
        $RutaAttachSAP = ObtenerDirAttach();
        $dir = CrearObtenerDirTemp();
        $dir_new = CrearObtenerDirAnx("llamadas");
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

        $Metodo = 2; //Actualizar en el web services
        $Type = 2; //Ejecutar actualizar en el SP

        if (($sw_error == 0) && (base64_decode($_POST['IdLlamadaPortal']) == "")) {
            $Metodo = 2; //Actualizar en el web services
            $Type = 1; //Ejecutar insertar en el SP
        } elseif (($type_llmd == 0) && ($sw_error == 1) && (base64_decode($_POST['IdLlamadaPortal']) != "")) {
            $Metodo = 1; //Insertar en el web services
            $Type = 2; //Ejecutar Actualizar en el SP
        }

        $ParamUpdLlamada = array(
            "'" . base64_decode($_POST['IdLlamadaPortal']) . "'",
            "'" . base64_decode($_POST['DocEntry']) . "'",
            "'" . base64_decode($_POST['DocNum']) . "'",
            "'Externa'",
            "'" . $_POST['AsuntoLlamada'] . "'",
            "'" . $_POST['Series'] . "'",
            "'" . $_POST['EstadoLlamada'] . "'",
            "'" . $_POST['OrigenLlamada'] . "'",
            "'" . $_POST['TipoLlamada'] . "'",
            "'" . $_POST['TipoProblema'] . "'",
            "'" . $_POST['SubTipoProblema'] . "'",
            "'" . $_POST['ContratoServicio'] . "'",
            "'" . $_POST['Tecnico'] . "'",
            "'" . $_POST['ClienteLlamada'] . "'",
            "'" . $_POST['ContactoCliente'] . "'",
            "'" . $_POST['TelefonoLlamada'] . "'",
            "'" . $_POST['CorreoLlamada'] . "'",
            "'" . $_POST['IdArticuloLlamada'] . "'", // SMM, 24/01/2022
            "'" . $_POST['NumeroSerie'] . "'",
            "'" . $_POST['SucursalCliente'] . "'",
            "'" . $_POST['IdSucursalCliente'] . "'",
            "'" . $_POST['DireccionLlamada'] . "'",
            "'" . $_POST['CiudadLlamada'] . "'",
            "'" . $_POST['BarrioDireccionLlamada'] . "'",
            "'" . $_POST['EmpleadoLlamada'] . "'",
            "'" . $_POST['Proyecto'] . "'",
            "'" . LSiqmlObs($_POST['ComentarioLlamada']) . "'",
            "'" . LSiqmlObs($_POST['ResolucionLlamada']) . "'",
            "'" . FormatoFecha($_POST['FechaCreacion'], $_POST['HoraCreacion']) . "'",
            "'" . FormatoFecha($_POST['FechaCierre'], $_POST['HoraCierre']) . "'",
            "'" . $_POST['IdAnexos'] . "'",
            "$Metodo",
            "'" . $_SESSION['CodUser'] . "'",
            "'" . $_SESSION['CodUser'] . "'",
            "'" . $_POST['CDU_EstadoServicio'] . "'",
            "'" . LSiqmlObs($_POST['CDU_Servicios']) . "'",
            "'" . LSiqmlObs($_POST['CDU_Areas']) . "'",
            "'" . LSiqmlObs($_POST['CDU_NombreContacto']) . "'",
            "'" . LSiqmlObs($_POST['CDU_TelefonoContacto']) . "'",
            "'" . LSiqmlObs($_POST['CDU_CargoContacto']) . "'",
            "'" . LSiqmlObs($_POST['CDU_CorreoContacto']) . "'",
            "NULL",
            "NULL",
            "NULL",
            "NULL",
            "NULL",
            "NULL",
            "'" . $_POST['CDU_CanceladoPor'] . "'",
            ($_POST['CantArticulo'] != "") ? LSiqmlValorDecimal($_POST['CantArticulo']) : 0,
            ($_POST['PrecioArticulo'] != "") ? LSiqmlValorDecimal($_POST['PrecioArticulo']) : 0,
            "$Type",
            // Campos nuevos
            "'" . $_POST['CDU_Marca'] . "'",
            "'" . $_POST['CDU_Linea'] . "'",
            "'" . $_POST['CDU_Ano'] . "'",
            "'" . $_POST['CDU_Concesionario'] . "'",
            "'" . $_POST['CDU_Aseguradora'] . "'",
            "NULL",
            "'" . $_POST['CDU_TipoServicio'] . "'",
            isset($_POST['CDU_Kilometros']) ? $_POST['CDU_Kilometros'] : 0, // int
            "'" . $_POST['CDU_Contrato'] . "'",
            "NULL",
            "'" . $_POST['CDU_ListaMateriales'] . "'",
            isset($_POST['CDU_TiempoTarea']) ? $_POST['CDU_TiempoTarea'] : 0, // int
            "'" . $_POST['CDU_IdTecnicoAdicional'] . "'", // SMM, 25/05/2022
            "'" . FormatoFecha($_POST['FechaAgenda'], $_POST['HoraAgenda']) . "'", // SMM 01/06/2022
            "'" . FormatoFecha($_POST['FechaAgenda'], $_POST['HoraAgenda']) . "'", // SMM 01/06/2022
            "0", // CreacionActividad, SMM 28/07/2022
            (PermitirFuncion(324) && ($_POST['EstadoLlamada'] == -1)) ? "1" : "0", // EnvioCorreo, SMM 28/07/2022
            "'" . ($_POST['NombreContactoFirma'] ?? "") . "'", // SMM, 18/10/2022
            "'" . ($_POST['CedulaContactoFirma'] ?? "") . "'", // SMM, 18/10/2022
            "'" . ($_POST['TelefonosContactosFirma'] ?? "") . "'", // SMM, 18/10/2022
            "'" . ($_POST['CorreosContactosFirma'] ?? "") . "'", // SMM, 18/10/2022
            "'" . $FirmaContactoResponsable . "'", // SMM, 16/09/2022
            (PermitirFuncion(325) && ($_POST['EstadoLlamada'] == -1)) ? "1" : "0", // FormatoCierreLlamada, SMM 14/10/2022
        );

        // Actualizar la llamada de servicio.
        $SQL_UpdLlamada = EjecutarSP('sp_tbl_LlamadaServicios', $ParamUpdLlamada, 33);
        if ($SQL_UpdLlamada) {
            if (base64_decode($_POST['IdLlamadaPortal']) == "") {
                $row_NewIdLlamada = sqlsrv_fetch_array($SQL_UpdLlamada);
                $IdLlamada = $row_NewIdLlamada[0];
            } else {
                $IdLlamada = base64_decode($_POST['IdLlamadaPortal']);
            }

            try {
                //Mover los anexos a la carpeta de archivos de SAP
                $j = 0;
                if ($sw_error == 1) { //Si hay un error, limpiar los anexos ya cargados, para volverlos a cargar a la tabla
                    //Registrar archivo en la BD
                    $ParamDelAnex = array(
                        "'191'",
                        "'" . $IdLlamada . "'",
                        "NULL",
                        "NULL",
                        "NULL",
                        "'" . $_SESSION['CodUser'] . "'",
                        "2",
                    );
                    $SQL_DelAnex = EjecutarSP('sp_tbl_DocumentosSAP_Anexos', $ParamDelAnex, 33);
                }
                while ($j < $CantFiles) {
                    $Archivo = FormatoNombreAnexo($DocFiles[$j], false);
                    $NuevoNombre = $Archivo[0];
                    $OnlyName = $Archivo[1];
                    $Ext = $Archivo[2];

                    if (file_exists($dir_new)) {
                        copy($dir . $DocFiles[$j], $dir_new . $NuevoNombre);
                        //move_uploaded_file($_FILES['FileArchivo']['tmp_name'],$dir_new.$NuevoNombre);
                        copy($dir_new . $NuevoNombre, $RutaAttachSAP[0] . $NuevoNombre);

                        //Registrar archivo en la BD
                        $ParamInsAnex = array(
                            "'191'",
                            "'" . $IdLlamada . "'",
                            "'" . $OnlyName . "'",
                            "'" . $Ext . "'",
                            "1",
                            "'" . $_SESSION['CodUser'] . "'",
                            "1",
                        );
                        $SQL_InsAnex = EjecutarSP('sp_tbl_DocumentosSAP_Anexos', $ParamInsAnex, 33);
                        if (!$SQL_InsAnex) {
                            $sw_error = 1;
                            $msg_error = "Error al actualizar la llamada de servicio";
                            //throw new Exception('Error al insertar los anexos.');
                            //sqlsrv_close($conexion);
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
                    'id_documento' => intval($IdLlamada),
                    'id_evento' => 0,
                );

                $Metodo = "LlamadasServicios";
                $Resultado = EnviarWebServiceSAP($Metodo, $Parametros, true, true); // Actualizar llamada en SAP

                if ($Resultado->Success == 0 || $testMode) {
                    $sw_error = 1;
                    $msg_error = $Resultado->Mensaje;

                    if ($_POST['EstadoLlamada'] == '-1') {
                        $UpdEstado = "UPDATE tbl_LlamadasServicios SET Cod_Estado='-3' WHERE ID_LlamadaServicio='" . $IdLlamada . "'";
                        $SQL_UpdEstado = sqlsrv_query($conexion, $UpdEstado);
                    }
                } else {
                    $msg = base64_encode($Resultado->Mensaje); // SMM, 14/09/2022

                    sqlsrv_close($conexion);
                    header("Location:llamada_servicio.php?msg=$msg&id=" . $_POST['DocEntry'] . '&tl=1&a=' . base64_encode("OK_UpdAdd"));
                }
            } catch (Exception $e) {
                echo 'Excepcion capturada: ', $e->getMessage(), "\n";
            }

        } else {
            $sw_error = 1;
            $msg_error = "Error al actualizar la llamada de servicio";
            //throw new Exception('Error al actualizar la llamada de servicio');
            //sqlsrv_close($conexion);
            //exit();
        }
    } catch (Exception $e) {
        echo 'Excepcion capturada: ', $e->getMessage(), "\n";
    }

}

if (isset($_POST['P']) && ($_POST['P'] == 40)) { //Reabrir llamada de servicio
    try {
        //$Parametros=array(
        //            'id_documento' => intval(base64_decode($_POST['DocEntry'])),
        //            'id_evento' => 0
        //        );

        $Parametros = "";

        $Metodo = "LlamadasServicios/Reabrir/" . base64_decode($_POST['DocEntry']);
        $Resultado = EnviarWebServiceSAP($Metodo, $Parametros, true, true);

        if ($Resultado->Success == 0) {
            $sw_error = 1;
            $msg_error = $Resultado->Mensaje;
        } else {
            sqlsrv_close($conexion);
            header('Location:llamada_servicio.php?id=' . $_POST['DocEntry'] . '&tl=1&a=' . base64_encode("OK_OpenLlam"));
        }

    } catch (Exception $e) {
        //InsertarLog(1, 40, $Cons_UpdCierreLlamada);
        echo 'Excepcion capturada: ', $e->getMessage(), "\n";
    }
}

if (isset($_GET['dt_LS']) && ($_GET['dt_LS']) == 1) { //Verificar que viene de una Llamada de servicio (Datos Llamada servicio)
    $dt_LS = 1;

    //Clientes
    $SQL_Cliente = Seleccionar('uvw_Sap_tbl_Clientes', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreCliente');
    $row_Cliente = sqlsrv_fetch_array($SQL_Cliente);

    //Contacto cliente
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreContacto');

    //Sucursal cliente
    $SQL_SucursalCliente = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', '*', "CodigoCliente='" . base64_decode($_GET['Cardcode']) . "'", 'NombreSucursal');
}

if ($type_llmd == 1 && $sw_error == 0) {
    //Llamada
    $SQL = Seleccionar('uvw_Sap_tbl_LlamadasServicios', '*', "ID_LlamadaServicio='" . $IdLlamada . "'");
    $row = sqlsrv_fetch_array($SQL);

    //Clientes
    $SQL_Cliente = Seleccionar("uvw_Sap_tbl_Clientes", "CodigoCliente, NombreCliente", "CodigoCliente='" . $row['ID_CodigoCliente'] . "'", 'NombreCliente');

    //Contactos clientes
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', 'CodigoContacto, ID_Contacto', "CodigoCliente='" . $row['ID_CodigoCliente'] . "'", 'NombreContacto');

    //Sucursales
    $SQL_SucursalCliente = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', 'NombreSucursal, NumeroLinea, TipoDireccion', "CodigoCliente='" . $row['ID_CodigoCliente'] . "' and TipoDireccion='S'", 'TipoDireccion, NombreSucursal');

    //Anexos
    $SQL_AnexoLlamada = Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos', '*', "AbsEntry='" . $row['IdAnexoLlamada'] . "'");

    //Articulos del cliente (ID servicio)
    $ParamArt = array(
        "'" . $row['ID_CodigoCliente'] . "'",
        "'" . $row['NombreSucursal'] . "'",
        "'0'",
    );
    $SQL_Articulos = EjecutarSP('sp_ConsultarArticulosLlamadas', $ParamArt);

    //Numero de series -> Tarjeta de equipo
    $SQL_NumeroSerie = Seleccionar('uvw_Sap_tbl_TarjetasEquipos', '*', "ItemCode='" . $row['IdArticuloLlamada'] . "' AND CardCode='" . $row['ID_CodigoCliente'] . "'", 'SerialFabricante');

    // SMM, 01/03/2022
    $CDU_IdMarca_TarjetaEquipo = $row['CDU_IdMarca_TarjetaEquipo'] ?? '';
    $CDU_IdLinea_TarjetaEquipo = $row['CDU_IdLinea_TarjetaEquipo'] ?? '';

    //Lista de materiales
    $SQL_ListaMateriales = Seleccionar('uvw_Sap_tbl_ListaMateriales', '*', "CDU_IdMarca='" . $CDU_IdMarca_TarjetaEquipo . "' AND CDU_IdLinea='" . $CDU_IdLinea_TarjetaEquipo . "'");

    //Activides relacionadas
    $SQL_Actividad = Seleccionar('uvw_Sap_tbl_Actividades', '*', "ID_LlamadaServicio='" . $IdLlamada . "'", 'ID_Actividad');

    //Documentos relacionados
    $SQL_DocRel = Seleccionar('uvw_Sap_tbl_LlamadasServiciosDocRelacionados', '*', "ID_LlamadaServicio='" . $IdLlamada . "'");

    //Formularios de llamadas de servicios
    $SQL_Formularios = Seleccionar('uvw_tbl_LlamadasServicios_Formularios', '*', "docentry_llamada_servicio='" . $IdLlamada . "'");

    //Contratos de servicio
    $SQL_Contrato = Seleccionar('uvw_Sap_tbl_Contratos', '*', "CodigoCliente='" . $row['ID_CodigoCliente'] . "'", 'ID_Contrato');

    // Stiven Muñoz Murillo, 24/01/2022
    $SQL_Articulo = Seleccionar('uvw_Sap_tbl_ArticulosLlamadas', '*', "ItemCode='" . $row['IdArticuloLlamada'] . "'");
    $row_Articulo = sqlsrv_fetch_array($SQL_Articulo);
}

if ($sw_error == 1) {
    //Si ocurre un error, vuelvo a consultar los datos insertados desde la base de datos.
    $SQL = Seleccionar('uvw_tbl_LlamadasServicios', '*', "ID_LlamadaServicio='" . $IdLlamada . "'");
    $row = sqlsrv_fetch_array($SQL);

    //Clientes
    $SQL_Cliente = Seleccionar("uvw_Sap_tbl_Clientes", "CodigoCliente, NombreCliente", "CodigoCliente='" . $row['ID_CodigoCliente'] . "'", 'NombreCliente');

    //Contactos clientes
    $SQL_ContactoCliente = Seleccionar('uvw_Sap_tbl_ClienteContactos', 'CodigoContacto, ID_Contacto', "CodigoCliente='" . $row['ID_CodigoCliente'] . "'", 'NombreContacto');

    //Sucursales
    $SQL_SucursalCliente = Seleccionar('uvw_Sap_tbl_Clientes_Sucursales', 'NombreSucursal, NumeroLinea, TipoDireccion', "CodigoCliente='" . $row['ID_CodigoCliente'] . "' and TipoDireccion='S'", 'TipoDireccion, NombreSucursal');

    //Articulos del cliente (ID servicio)
    $ParamArt = array(
        "'" . $row['ID_CodigoCliente'] . "'",
        "'" . $row['NombreSucursal'] . "'",
        "'0'",
    );
    $SQL_Articulos = EjecutarSP('sp_ConsultarArticulosLlamadas', $ParamArt);

    //Numero de series -> Tarjeta de equipo
    $SQL_NumeroSerie = Seleccionar('uvw_Sap_tbl_TarjetasEquipos', '*', "ItemCode='" . $row['IdArticuloLlamada'] . "'", 'SerialFabricante');

    //Activides relacionadas
    $SQL_Actividad = Seleccionar('uvw_Sap_tbl_Actividades', '*', "ID_LlamadaServicio='" . $row['DocEntry'] . "'", 'ID_Actividad');

    //Documentos relacionados
    $SQL_DocRel = Seleccionar('uvw_Sap_tbl_LlamadasServiciosDocRelacionados', '*', "ID_LlamadaServicio='" . $row['DocEntry'] . "'");

    //Formularios de llamadas de servicios
    $SQL_Formularios = Seleccionar('uvw_tbl_LlamadasServicios_Formularios', '*', "docentry_llamada_servicio='" . $row['DocEntry'] . "'");

    //Contratos de servicio
    $SQL_Contrato = Seleccionar('uvw_Sap_tbl_Contratos', '*', "CodigoCliente='" . $row['ID_CodigoCliente'] . "'", 'ID_Contrato');

    // SMM, 01/03/2022
    $CDU_IdMarca_TarjetaEquipo = $row['CDU_IdMarca_TarjetaEquipo'] ?? '';
    $CDU_IdLinea_TarjetaEquipo = $row['CDU_IdLinea_TarjetaEquipo'] ?? '';

    // Lista de materiales
    $SQL_ListaMateriales = Seleccionar('uvw_Sap_tbl_ListaMateriales', '*', "CDU_IdMarca='" . $CDU_IdMarca_TarjetaEquipo . "' AND CDU_IdLinea='" . $CDU_IdLinea_TarjetaEquipo . "'");

    // Stiven Muñoz Murillo, 02/06/2022
    $SQL_Articulo = Seleccionar('uvw_Sap_tbl_ArticulosLlamadas', '*', "ItemCode='" . $row['IdArticuloLlamada'] . "'");
    $row_Articulo = sqlsrv_fetch_array($SQL_Articulo);
}

//Serie de llamada
$ParamSerie = array(
    "'" . $_SESSION['CodUser'] . "'",
    "'191'",
    ($type_llmd == 0) ? 2 : 1,
);
$SQL_Series = EjecutarSP('sp_ConsultarSeriesDocumentos', $ParamSerie);

//Estado servicio llamada
$SQL_EstServLlamada = Seleccionar('uvw_Sap_tbl_LlamadasServiciosEstadoServicios', '*', '', 'DeEstadoServicio');

//Cancelado por llamada
$SQL_CanceladoPorLlamada = Seleccionar('uvw_Sap_tbl_LlamadasServiciosCanceladoPor', '*', '', 'DeCanceladoPor');

//Causa reprogramacion llamada
$SQL_CausaReprog = Seleccionar('uvw_Sap_tbl_LlamadasServiciosReprogramacion', '*', '', 'DeReprogramacion');

//Cola llamada
//$SQL_ColaLlamada=Seleccionar('uvw_Sap_tbl_ColaLlamadas','*','','DeColaLlamada');

//Empleados
$SQL_EmpleadoLlamada = Seleccionar('uvw_Sap_tbl_Empleados', '*', "UsuarioSAP <> ''", 'NombreEmpleado');

//Proyectos
$SQL_Proyecto = Seleccionar('uvw_Sap_tbl_Proyectos', '*', '', 'DeProyecto');

//Tecnicos
$SQL_Tecnicos = Seleccionar('uvw_Sap_tbl_Recursos', '*', '', 'NombreEmpleado');

// Tecnicos Adicionales, SMM 25/05/2022
$SQL_TecnicosAdicionales = Seleccionar('uvw_Sap_tbl_Recursos', '*', '', 'NombreEmpleado');

//Estado llamada
$SQL_EstadoLlamada = Seleccionar('uvw_tbl_EstadoLlamada', '*');

// @author Stiven Muñoz Murillo
// @version 02/12/2021

// Marcas de vehiculo en la llamada de servicio
$SQL_MarcaVehiculo = Seleccionar('uvw_Sap_tbl_LlamadasServicios_MarcaVehiculo', '*');

// Lineas de vehiculo en la llamada de servicio
$SQL_LineaVehiculo = Seleccionar('uvw_Sap_tbl_LlamadasServicios_LineaVehiculo', '*');

// Modelo o año de fabricación de vehiculo en la llamada de servicio
$SQL_ModeloVehiculo = Seleccionar('uvw_Sap_tbl_LlamadasServicios_AñoModeloVehiculo', '*');

// Concesionarios en la llamada de servicio
$SQL_Concesionario = Seleccionar('uvw_Sap_tbl_LlamadasServicios_Concesionario', '*');

// Aseguradoras en la llamada de servicio
$SQL_Aseguradora = Seleccionar('uvw_Sap_tbl_LlamadasServicios_Aseguradoras', '*');

// Tipos preventivos en la llamada de servicio
$SQL_TipoPreventivo = Seleccionar('uvw_Sap_tbl_LlamadasServicios_TipoPreventivo', '*');

// Tipos de servicio en la llamada de servicio
$SQL_TipoServicio = Seleccionar('uvw_Sap_tbl_LlamadasServicios_TipoServicio', '*');

// Contratos en la llamada de servicio
$SQL_ContratosLlamada = Seleccionar('uvw_Sap_tbl_LlamadasServicios_Contratos_TBUsuario', '*');

// Asesores (Empleados de venta) en la llamada de servicio
// $SQL_EmpleadosVentas = Seleccionar('uvw_Sap_tbl_EmpleadosVentas', '*');

// Stiven Muñoz Murillo, 04/03/2022
if ($testMode) {
    $row_encode = isset($row) ? json_encode($row) : "";
    $cadena = isset($row) ? "JSON.parse('$row_encode'.replace(/\\n|\\r/g, ''))" : "'Not Found'";
    echo "<script> console.log($cadena); </script>";
}
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $Title; ?> | <?php echo NOMBRE_PORTAL; ?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_LlamAdd"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: '" . LSiqmlObs($msg) . "',
                icon: 'success'
            });
		});
		</script>";
}
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_UpdAdd"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: '" . LSiqmlObs($msg) . "',
                icon: 'success'
            });
		});
		</script>";
}
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_ActAdd"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La actividad ha sido agregada exitosamente.',
                icon: 'success'
            });
		});
		</script>";
}
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_UpdActAdd"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La actividad ha sido actualizada exitosamente.',
                icon: 'success'
            });
		});
		</script>";
}
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_OVenAdd"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La Orden de venta ha sido agregada exitosamente.',
                icon: 'success'
            });
		});
		</script>";
}
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_OVenUpd"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La Orden de venta ha sido actualizada exitosamente.',
                icon: 'success'
            });
		});
		</script>";
}
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_EVenAdd"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La Entrega de venta ha sido agregada exitosamente.',
                icon: 'success'
            });
		});
		</script>";
}
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_EVenUpd"))) {
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
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_OpenLlam"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La llamada de servicio ha sido abierta nuevamente.',
                icon: 'success'
            });
		});
		</script>";
}
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_DelAct"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La actividad ha sido eliminado exitosamente.',
                icon: 'success'
            });
		});
		</script>";
}
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_OpenAct"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La actividad ha sido abierta nuevamente.',
                icon: 'success'
            });
		});
		</script>";
}
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_FrmAdd"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El hallazgo ha sido registrado exitosamente.',
                icon: 'success'
            });
		});
		</script>";
}
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_FrmUpd"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El hallazgo ha sido actualizado exitosamente.',
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
                text: '" . LSiqmlObs($msg_error) . "',
                icon: 'warning'
            });
		});
		</script>";
}
?>

<style>
	.ibox-title a{
		color: inherit !important;
	}
	.collapse-link:hover{
		cursor: pointer;
	}
	.select2-container{
		width: 100% !important;
	}
	.swal2-container {
		z-index: 9000;
	}

	/** SMM, 16/09/2022 */
	.cierre-span {
		display: none;
	}

	.badge-secondary {
		margin: 10px;
		cursor: pointer;
	}
</style>

<script type="text/javascript">
	$(document).ready(function() {
		var borrarNumeroSerie = true;
		var borrarLineaModeloVehiculo = true;

		//Cargar los combos dependiendo de otros
		$("#ClienteLlamada").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Cliente=document.getElementById('ClienteLlamada').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=2&id="+Cliente,
				success: function(response){
					$('#ContactoCliente').html(response).fadeIn();
					$('#ContactoCliente').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=3&id="+Cliente,
				success: function(response){
					$('#SucursalCliente').html(response).fadeIn();
					$('#SucursalCliente').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=29&id="+Cliente,
				success: function(response){
					$('#ContratoServicio').html(response).fadeIn();
					$('#ContratoServicio').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=30&id="+Cliente,
				success: function(response){
					$('#Proyecto').html(response).fadeIn();
					$('#Proyecto').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
			$.ajax({
					type: "POST",
					url: "ajx_cbo_select.php?type=28&id=&clt="+Cliente+"&<?php echo isset($_GET['Serial']) ? ("Serial=" . base64_decode($_GET['Serial'])) : ""; ?>&<?php echo isset($_GET['IdTE']) ? ("IdTE=" . base64_decode($_GET['IdTE'])) : ""; ?>",
					success: function(response){
						// console.log(response);

						$('#NumeroSerie').html(response).fadeIn();
						$('#NumeroSerie').trigger('change');

						$('.ibox-content').toggleClass('sk-loading',false);
					}
				});
		});
		$("#SucursalCliente").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);

			var Cliente=document.getElementById('ClienteLlamada').value;
			var Sucursal=document.getElementById('SucursalCliente').value;

			if(Sucursal != -1 && Sucursal != '') {
				$.ajax({
					url:"ajx_buscar_datos_json.php",
					data:{type:1,CardCode:Cliente,Sucursal:Sucursal},
					dataType:'json',
					success: function(data){
						document.getElementById('DireccionLlamada').value=data.Direccion;
						document.getElementById('BarrioDireccionLlamada').value=data.Barrio;
						document.getElementById('CiudadLlamada').value=data.Ciudad;
						document.getElementById('CDU_NombreContacto').value=data.NombreContacto;
						document.getElementById('CDU_TelefonoContacto').value=data.TelefonoContacto;
						document.getElementById('CDU_CargoContacto').value=data.CargoContacto;
						document.getElementById('CDU_CorreoContacto').value=data.CorreoContacto;

						// Stiven Muñoz Murillo, 22/01/2022
						document.getElementById('TelefonoLlamada').value=data.TelefonoContacto;
					},
					error: function(error){
						$('.ibox-content').toggleClass('sk-loading', false);
						console.error("SucursalCliente", error.responseText);
					}
				});
			} else {
				$('.ibox-content').toggleClass('sk-loading', false);
			}
			// TODO, filtrar los resultados por cliente y sucursal
			/*
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=11&id="+Cliente+"&suc="+Sucursal,
				success: function(response){
					$('#ArticuloLlamada').html(response).fadeIn();
					$('#ArticuloLlamada').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
			*/
			// TODO, abajo en el buscar
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{
					type:39,
					clt:Cliente,
					suc:Sucursal},
				dataType:'json',
				success: function(data){
					document.getElementById('IdSucursalCliente').value=data.IdSucursal;
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});
		$("#ContactoCliente").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Contacto=document.getElementById('ContactoCliente').value;
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:5,Contacto:Contacto},
				dataType:'json',
				success: function(data){
					document.getElementById('TelefonoLlamada').value=data.Telefono;
					document.getElementById('CorreoLlamada').value=data.Correo;
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});

		// Stiven Muñoz Murillo, 24/01/2022
		$("#IdArticuloLlamada").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var ID=document.getElementById('IdArticuloLlamada').value;
			var Cliente=document.getElementById('ClienteLlamada').value;
			if(ID!=""){
				$.ajax({
					url:"ajx_buscar_datos_json.php",
					data:{type:6,id:ID},
					dataType:'json',
					success: function(data){
						document.getElementById('CDU_Servicios').value=data.Servicios;
						document.getElementById('CDU_Areas').value=data.Areas;
						$('.ibox-content').toggleClass('sk-loading',false);
					}
				});
				$.ajax({
					type: "POST",
					url: "ajx_cbo_select.php?type=28&id="+ID+"&clt="+Cliente,
					success: function(response){
						// console.log(response);

						if(borrarNumeroSerie) {
							$('#NumeroSerie').html(response).fadeIn();
							$('#NumeroSerie').trigger('change');
						} else {
							borrarNumeroSerie = true;
						}

						$('.ibox-content').toggleClass('sk-loading',false);
					}
				});
			}else{
				document.getElementById('CDU_Servicios').value='';
				document.getElementById('CDU_Areas').value='';
				/*document.getElementById('CDU_NombreContacto').value='';
				document.getElementById('CDU_TelefonoContacto').value='';
				document.getElementById('CDU_CargoContacto').value='';
				document.getElementById('CDU_CorreoContacto').value='';*/
				$('.ibox-content').toggleClass('sk-loading',false);
			}
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$("#TipoTarea").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var TipoTarea=document.getElementById('TipoTarea').value;
			if(TipoTarea=="Interna"){
				document.getElementById('ClienteLlamada').value='<?php echo NIT_EMPRESA; ?>';
				document.getElementById('NombreClienteLlamada').value='<?php echo NOMBRE_EMPRESA; ?>';
				document.getElementById('NombreClienteLlamada').readOnly=true;
				$('#ClienteLlamada').trigger('change');
				$('.ibox-content').toggleClass('sk-loading',false);
				//HabilitarCampos(0);
			}else{
				document.getElementById('ClienteLlamada').value='';
				document.getElementById('NombreClienteLlamada').value='';
				document.getElementById('NombreClienteLlamada').readOnly=false;
				$('#ClienteLlamada').trigger('change');
				$('.ibox-content').toggleClass('sk-loading',false);
				//HabilitarCampos(1);
			}
		});
		<?php if ($type_llmd == 0 && $sw_error == 0) {?>
		$("#Series").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Series=document.getElementById('Series').value;
			if(Series!=""){
				$.ajax({
					url:"ajx_buscar_datos_json.php",
					data:{type:30,id:Series},
					dataType:'json',
					success: function(data){
						if (data.OrigenLlamada){
							document.getElementById('OrigenLlamada').value=data.OrigenLlamada;
							document.getElementById('TipoLlamada').value=data.TipoLlamada;
							document.getElementById('TipoProblema').value=data.TipoProblemaLlamada;
							// let subtipo = document.getElementById('SubTipoProblema').value=data.SubTipoProblemaLlamada;
							document.getElementById('AsuntoLlamada').value=data.AsuntoLlamada;
						} else {
							document.getElementById('OrigenLlamada').value="";
							document.getElementById('TipoLlamada').value="";
							document.getElementById('TipoProblema').value="";
							// document.getElementById('SubTipoProblema').value="";
							document.getElementById('AsuntoLlamada').value="";
						}
						$('.ibox-content').toggleClass('sk-loading',false);
					}
				});
			} else{
				$('.ibox-content').toggleClass('sk-loading',false);
			}
		});

		// Stiven Muñoz Murillo, 07/02/2022
		$("#CDU_ListaMateriales").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			let listaMaterial=document.getElementById('CDU_ListaMateriales').value;

			if(listaMaterial != "") {
				$.ajax({
					url: "ajx_buscar_datos_json.php",
					data: {
						type: 47,
						id: listaMaterial
					},
					dataType: 'json',
					success: function(data){
						// console.log(data);

						document.getElementById('CDU_TiempoTarea').value = data.tiempoTarea;
						$('.ibox-content').toggleClass('sk-loading',false);
					},
					error: function(error) {
						console.error(error.responseText);
						$('.ibox-content').toggleClass('sk-loading',false);
					}
				});
			}
		});

		// Stiven Muñoz Murillo, 20/12/2021
		$("#CDU_Marca").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var marcaVehiculo=document.getElementById('CDU_Marca').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=39&id="+marcaVehiculo,
				success: function(response){
					// console.log(response);

					if(borrarLineaModeloVehiculo) {
						$('#CDU_Linea').html(response).fadeIn();
						$('#CDU_Linea').trigger('change');
					} else {
						borrarLineaModeloVehiculo = true;
					}

					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});

		// Stiven Muñoz Murillo, 22/12/2021
		$("#NumeroSerie").change(function(){
			$('.ibox-content').toggleClass('sk-loading', true);

			var ID=document.getElementById('NumeroSerie').value;
			var Cliente=document.getElementById('ClienteLlamada').value;

			// SMM, 19/05/2022
			let IdTarjetaEquipo = $("#NumeroSerie").find(':selected').data('id');

			if(ID != "") {
				$.ajax({
					url:"ajx_buscar_datos_json.php",
					data:{
						type:44,
						id:IdTarjetaEquipo, // Antes, ID.
						clt:Cliente,
						si:0 // SMM, 19/05/2022
					},
					dataType:'json',
					success: function(data){
						// console.log(data);

						borrarNumeroSerie = false;
						document.getElementById('IdArticuloLlamada').value = data.IdArticuloLlamada;
						document.getElementById('DeArticuloLlamada').value = data.DeArticuloLlamada;
						// $('#IdArticuloLlamada').trigger('change');

						document.getElementById('CDU_Marca').value = data.CDU_IdMarca;
						$('#CDU_Marca').trigger('change');

						borrarLineaModeloVehiculo = false;
						document.getElementById('CDU_Linea').value = data.CDU_IdLinea;
						$('#CDU_Linea').trigger('change');

            			document.getElementById('CDU_Ano').value = data.CDU_Ano;
						$('#CDU_Ano').trigger('change');

            			document.getElementById('CDU_Concesionario').value = data.CDU_Concesionario;
						$('#CDU_Concesionario').trigger('change');

            			document.getElementById('CDU_TipoServicio').value = (data.CDU_TipoServicio != null) ? data.CDU_TipoServicio : "";
						$('#CDU_TipoServicio').trigger('change');

						$('.ibox-content').toggleClass('sk-loading',false);
					},
					error: function(data) {
						console.error("Line 1133", data.responseText);
					}
				});
				$.ajax({
					type: "POST",
					url: "ajx_cbo_select.php?type=40&id="+ID,
					success: function(response){
						// console.log(response);

						$('#CDU_ListaMateriales').html(response).fadeIn();
						$('#CDU_ListaMateriales').trigger('change');

						$('.ibox-content').toggleClass('sk-loading',false);
					}
				});
			}
			$('.ibox-content').toggleClass('sk-loading',false);
		});

		$('#Series').trigger('change');
		<?php }?>

		$("#EstadoLlamada").on("change", function() {
			let estado = $(this).val();

			if(estado == "-1") {
				console.log("el estado de la llamada cambio a cerrado.");

				$(".cierre-span").css("display", "initial");
				$(".cierre-input").prop("readonly", false);
				$(".cierre-input").prop("disabled", false);

				// SMM, 14/10/2022
				<?php if ($sw_error == 0) {?>
					$("#NombreContactoFirma").val($("#CDU_NombreContacto").val());
					$("#CorreosDestinatarios").html("");
					$("#TelefonosDestinatarios").html("");
					AgregarEsto("CorreosDestinatarios", $("#CDU_CorreoContacto").val());
					AgregarEsto("TelefonosDestinatarios", $("#CDU_TelefonoContacto").val());
				<?php }?>
			} else {
				console.log("cambio el estado de la llamada, diferente a cerrado.");

				$(".cierre-span").css("display", "none");
				$(".cierre-input").prop("readonly", true);
				$(".cierre-input").prop("disabled", true);
			}
		});
	});

function HabilitarCampos(type=1){
	if(type==0){//Deshabilitar
		document.getElementById('DatosCliente').style.display='none';
		document.getElementById('swTipo').value="1";
	}else{//Habilitar
		document.getElementById('DatosCliente').style.display='block';
		document.getElementById('swTipo').value="0";
	}
}
function ConsultarDatosCliente(){
	var Cliente=document.getElementById('ClienteLlamada');
	if(Cliente.value!=""){
		self.name='opener';
	remote=open('socios_negocios.php?id='+Base64.encode(Cliente.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
	remote.focus();
	}
}
function ConsultarArticulo(){
	var Articulo=document.getElementById('IdArticuloLlamada');
	console.log(Articulo.value);
	if(Articulo.value!=""){
		self.name='opener';
		remote=open('articulos.php?id='+Base64.encode(Articulo.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}
function ConsultarEquipo(){
	var numSerie=document.getElementById('NumeroSerie');

	if(numSerie.value!=""){
		self.name='opener';

		let parametros = "";
		let IdTarjetaEquipo = $("#NumeroSerie").find(':selected').data('id');
		if(((typeof IdTarjetaEquipo) !== 'undefined') && (IdTarjetaEquipo != null && IdTarjetaEquipo != "")) {
			parametros = `id='${Base64.encode(IdTarjetaEquipo + "")}'&ext=1&tl=1`;
		} else {
			parametros = `id='${Base64.encode(numSerie.value)}'&ext=1&tl=1&te=1`;
		}

		remote=open('tarjeta_equipo.php?'+parametros,'remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}
function ConsultarContrato(){
	var Contrato=document.getElementById('ContratoServicio');
	if(Contrato.value!=""){
		self.name='opener';
		remote=open('contratos.php?id='+btoa(Contrato.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}

// Stiven Muñoz Murillo, 30/12/2021
function ConsultarMateriales(){
	var Materiales=document.getElementById('CDU_ListaMateriales');
	if(Materiales.value!=""){
		self.name='opener';
		remote=open('lista_materiales.php?id='+Base64.encode(Materiales.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}
function CrearLead(){
	self.name='opener';
	var altura=720;
	var anchura=1240;
	var posicion_y=parseInt((window.screen.height/2)-(altura/2));
	var posicion_x=parseInt((window.screen.width/2)-(anchura/2));
	remote=open('popup_crear_lead.php','remote','width='+anchura+',height='+altura+',location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=no,status=yes,left='+posicion_x+',top='+posicion_y);
	remote.focus();
}

// SMM, 16/09/2022
function ValidarCorreo(evento, entrada, contenedorID = "CorreosDestinatarios") {
	if (event.code === 'Space') {
		let re = /\S+@\S+\.\S+/;
		let correo = entrada.value.trim();

		entrada.value = "";
		if(re.test(correo)) {
			AgregarEsto(contenedorID, correo);
		} else {
			alert("El correo no paso la validación.");
		}
	}
}

function LlenarCorreos() {
	let badges = document.getElementById("CorreosContactosFirma");
	badges.value = "";

	$("#CorreosDestinatarios .badge").each(function() {
		let badge = $(this).text().trim();
		console.log(`|${badge}|`);

		badges.value += `${badge};`;
	});
}

function ValidarTelefono(evento, entrada) {
	if (event.code === 'Space') {
		let re = /\d{5,}/;
		let telefono = entrada.value.trim();

		entrada.value = "";
		if(re.test(telefono)) {
			AgregarEsto("TelefonosDestinatarios", telefono);
		} else {
			alert("El télefono no paso la validación.");
		}
	}
}

function LlenarTelefonos() {
	let badges = document.getElementById("TelefonosContactosFirma");
	badges.value = "";

	$("#TelefonosDestinatarios .badge").each(function() {
		let badge = $(this).text().trim();
		console.log(`|${badge}|`);

		badges.value += `${badge};`;
	});
}

function EliminarEsto(elemento) {
	elemento.remove();

	LlenarCorreos();
	LlenarTelefonos();
}

function AgregarEsto(contenedorID, valorElemento) {
	let contenedorElementos = document.getElementById(contenedorID);
	contenedorElementos.innerHTML += `<span onclick="EliminarEsto(this)" class="badge badge-secondary"><i class="fa fa-trash"></i> ${valorElemento}</span>`;

	LlenarCorreos();
	LlenarTelefonos();
}
</script>

<!-- InstanceEndEditable -->
</head>

<!-- Stiven Muñoz Murillo -->
<body <?php if ($sw_ext == 1) {echo "class='mini-navbar'";}?>>
<div id="wrapper">
	<?php if ($sw_ext != 1) {include "includes/menu.php";}?>
    <div id="page-wrapper" class="gray-bg">
		<?php if ($sw_ext != 1) {include "includes/menu_superior.php";}?>
<!-- 12/01/2022 -->

		<!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2><?php echo $Title; ?></h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Gesti&oacute;n de tareas</a>
                        </li>
                        <li>
                            <a href="gestionar_llamadas_servicios.php">Gestionar llamadas de servicios</a>
                        </li>
                        <li class="active">
                            <strong><?php echo $Title; ?></strong>
                        </li>
                    </ol>
                </div>
            </div>

        <div class="wrapper wrapper-content">
			<!-- Inicio, myModal -->
			<div class="modal inmodal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg" style="width: 70% !important;">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="TituloModal"></h4>
						</div>
						<div class="modal-body" id="ContenidoModal"></div>
						<div class="modal-footer">
							<button type="button" class="btn btn-success m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
						</div>
					</div>
				</div>
			</div>
			<!-- Fin, myModal -->
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
			<!-- Inicio, modalFactSN -->
			<div class="modal inmodal fade" id="modalFactSN" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg" style="width: 70% !important;">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title">Cambiar Socio de Negocio en el Nuevo Documento</h4>
						</div>

						<form id="formCambiarFactSN">
							<div class="modal-body">
								<div class="row">
									<div class="col-lg-1"></div>
									<div class="col-lg-5">
										<label class="control-label">
											<i onClick="ConsultarDatosFactSN();" title="Consultar cliente" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Cliente <span class="text-danger">*</span>
										</label>
										<select class="form-control" id="ClienteFactSN" name="ClienteFactSN" required>
											<option value="">Seleccione...</option>
										</select>
										<small class="form-text text-muted">Sólo se listan los clientes con entregas abiertas.</small>
									</div>
									<div class="col-lg-5">
										<label class="control-label">Contacto</label>
										<select class="form-control" id="ContactoFactSN" name="ContactoFactSN">
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
										<select class="form-control" id="SucursalFactSN" name="SucursalFactSN">
											<option value="">Seleccione...</option>
										</select>
									</div>
									<div class="col-lg-5">
										<label class="control-label">Dirección</label>
										<input type="text" class="form-control" id="DireccionFactSN" name="DireccionFactSN" maxlength="100">
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
			<!-- Fin, modalFactSN -->

			<!-- Inicio, modalCorreo. SMM, 13/10/2022 -->
			<?php if (isset($row['IdEstadoLlamada']) && ($row['IdEstadoLlamada'] == '-1')) {?>
				<div class="modal inmodal fade" id="modalCorreo" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-lg" style="width: 70% !important;">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title">Envío de llamada de servicio No.<?php echo $row['DocNum']; ?></h4>
							</div>

							<!-- form id="formCambiarSN" -->
								<div class="modal-body">
									<div class="row">
										<div class="col-lg-1"></div>
										<div class="col-lg-10">
											<div class="form-group">
												<label class="control-label">Para</label>
												<input placeholder="Ingrese un nuevo correo y utilice la tecla [ESP] para agregar" onKeyUp="ValidarCorreo(event, this, 'CorreosPara')" autocomplete="off" name="EmailPara" type="text" class="form-control" id="EmailPara" maxlength="50" value="">
												<input type="hidden" id="CorreosContactosFirma" name="CorreosContactosFirma">

												<div id="CorreosPara"></div>
											</div>
										</div>
										<div class="col-lg-1"></div>
									</div>
									<div class="row">
										<div class="col-lg-1"></div>
										<div class="col-lg-10">
											<div class="form-group">
												<label class="control-label">Con copia a</label>
												<input placeholder="Ingrese un nuevo correo y utilice la tecla [ESP] para agregar" onKeyUp="ValidarCorreo(event, this, 'CorreosCC')" autocomplete="off" name="EmailCC" type="text" class="form-control" id="CorreoContactoFirma" maxlength="50" value="">
												<input type="hidden" id="CorreosContactosFirma" name="CorreosContactosFirma">

												<div id="CorreosCC"></div>
											</div>
										</div>
										<div class="col-lg-1"></div>
									</div>
								</div>

								<div class="modal-footer">
									<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i> Aceptar</button>
									<button type="button" class="btn btn-secondary m-t-md CancelarSN" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
								</div>
							<!-- /form -->
						</div>
					</div>
				</div>
			<?php }?>
			<!-- Fin, modalCorreo -->

			<?php if ($type_llmd == 1) {?>
			<div class="row">
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Llamada de servicio</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php echo $row['DocNum']; ?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Creada por</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php if ($row['UsuarioCreacion'] != "") {echo $row['UsuarioCreacion'];} else {echo "&nbsp;";}?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Actualizado por</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php if ($row['UsuarioActualizacion'] != "") {echo $row['UsuarioActualizacion'];} else {echo "&nbsp;";}?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Fecha actualización</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php if ($row['FechaActualizacion'] != "") {echo $row['FechaActualizacion'] . " " . $row['HoraActualizacion'];} else {echo "&nbsp;";}?></h3>
						</div>
					</div>
				</div>
			</div>
			<?php }?>
				<div class="ibox-content">
				<?php include "includes/spinner.php";?>
					<div class="row">
						<div class="col-lg-12">
							<div class="ibox">
								<div class="ibox-title bg-success">
									<h5 class="collapse-link"><i class="fa fa-play-circle"></i> Acciones</h5>
									 <a class="collapse-link pull-right">
										<i class="fa fa-chevron-up"></i>
									</a>
								</div>
								<div class="ibox-content">
									<div class="form-group">
										<?php if ($type_llmd == 1) {?>
										<div class="col-lg-6">
											<div class="btn-group">
												<button data-toggle="dropdown" class="btn btn-outline btn-success dropdown-toggle"><i class="fa fa-download"></i> Descargar formato <i class="fa fa-caret-down"></i></button>
												<ul class="dropdown-menu">
													<?php
$SQL_Formato = Seleccionar('uvw_tbl_FormatosSAP', '*', "ID_Objeto=191 and (IdFormato='" . $row['Series'] . "' OR DeSeries is null) and VerEnDocumento='Y'");
    while ($row_Formato = sqlsrv_fetch_array($SQL_Formato)) {?>
														<li>
															<a class="dropdown-item" target="_blank" href="sapdownload.php?id=<?php echo base64_encode('15'); ?>&type=<?php echo base64_encode('2'); ?>&DocKey=<?php echo base64_encode($row['ID_LlamadaServicio']); ?>&ObType=<?php echo base64_encode('191'); ?>&IdFrm=<?php echo base64_encode($row_Formato['IdFormato']); ?>&IdReg=<?php echo base64_encode($row_Formato['ID']); ?>"><?php echo $row_Formato['NombreVisualizar']; ?></a>
														</li>
													<?php }?>
												</ul>
											</div>

											<?php if (isset($row['IdEstadoLlamada']) && ($row['IdEstadoLlamada'] == '-1') && false) {?>
												<a href="#" class="btn btn-outline btn-primary" onClick="$('#modalCorreo').modal('show');"><i class="fa fa-envelope"></i> Enviar correo</a>
											<?php }?>
											<a href="#" class="btn btn-outline btn-info" onClick="VerMapaRel('<?php echo base64_encode($row['ID_LlamadaServicio']); ?>','<?php echo base64_encode('191'); ?>');"><i class="fa fa-sitemap"></i> Mapa de relaciones</a>
										</div>
										<?php } else if (PermitirFuncion(508)) {?>
											<button onClick="CrearLead();" class="btn btn-outline btn-primary"><i class="fa fa-user-circle"></i> Crear Prospecto</button>
											<a href="tarjeta_equipo.php" class="btn btn-outline btn-info" target="_blank"><i class="fa fa-plus-circle"></i> Crear nueva tarjeta de equipo</a>
										<?php }?>
									</div>
								</div>
							</div>

						</div>
					</div>
				</div>
			<br>
			 <div class="ibox-content">
				  <?php include "includes/spinner.php";?>
          <div class="row">
           <div class="col-lg-12">
              <form action="llamada_servicio.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="CrearLlamada">
				<div id="DatosCliente" <?php //if($row['TipoTarea']=='Interna'){ echo 'style="display: none;"';}?>>
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-group"></i> Información de cliente</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label"><i onClick="ConsultarDatosCliente();" title="Consultar cliente" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Cliente <span class="text-danger">*</span></label>
								<input name="ClienteLlamada" type="hidden" id="ClienteLlamada" value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['ID_CodigoCliente'];} elseif ($dt_LS == 1) {echo $row_Cliente['CodigoCliente'];}?>">
								<input name="NombreClienteLlamada" type="text" required="required" class="form-control" id="NombreClienteLlamada" placeholder="Digite para buscar..." <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1') || ($row['TipoTarea'] == 'Interna')) || ($dt_LS == 1) || ($type_llmd == 1)) {echo "readonly='readonly'";}?> value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['NombreClienteLlamada'];} elseif ($dt_LS == 1) {echo $row_Cliente['NombreCliente'];}?>">
							</div>
							<div class="col-lg-4">
								<label class="control-label">Contacto</label>
								<select name="ContactoCliente" class="form-control" id="ContactoCliente" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
								  <?php if (($type_llmd == 0) || ($sw_error == 1)) {?><option value="">Seleccione...</option><?php }?>
								  <?php if (($type_llmd == 1) || ($sw_error == 1)) {while ($row_ContactoCliente = sqlsrv_fetch_array($SQL_ContactoCliente)) {?>
										<option value="<?php echo $row_ContactoCliente['CodigoContacto']; ?>" <?php if ((isset($row['IdContactoLLamada'])) && (strcmp($row_ContactoCliente['CodigoContacto'], $row['IdContactoLLamada']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_ContactoCliente['ID_Contacto']; ?></option>
								  <?php }}?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Sucursal <span class="text-danger">*</span></label>
								<select name="SucursalCliente" class="form-control select2" id="SucursalCliente" required="required" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
								  <?php if (($type_llmd == 0) || ($sw_error == 1)) {?><option value="">Seleccione...</option><?php }?>
								  <?php if (($type_llmd == 1) || ($sw_error == 1)) {
    while ($row_SucursalCliente = sqlsrv_fetch_array($SQL_SucursalCliente)) {?>
										<option value="<?php echo $row_SucursalCliente['NombreSucursal']; ?>" <?php if (isset($row['NombreSucursal']) && (strcmp($row_SucursalCliente['NombreSucursal'], $row['NombreSucursal']) == 0)) {echo "selected=\"selected\"";} elseif (isset($row['NombreSucursal']) && (strcmp($row_SucursalCliente['NumeroLinea'], $row['IdNombreSucursal']) == 0)) {echo "selected=\"selected\"";
        $sw_valDir = 1;}?>><?php echo $row_SucursalCliente['NombreSucursal']; ?></option>
								  <?php }
}?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Dirección <span class="text-danger">*</span></label>
								<input name="DireccionLlamada" type="text" required="required" class="form-control" id="DireccionLlamada" maxlength="100" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?> value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['DireccionLlamada'];}?>">
							</div>
							<div class="col-lg-4">
								<label class="control-label">Barrio</label>
								<input name="BarrioDireccionLlamada" type="text" class="form-control" id="BarrioDireccionLlamada" maxlength="50" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?> value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['BarrioDireccionLlamada'];}?>">
							</div>
							<div class="col-lg-4">
								<label class="control-label">Teléfono <span class="text-danger">*</span></label>
								<input name="TelefonoLlamada" type="text" class="form-control" required="required" id="TelefonoLlamada" maxlength="50" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?> value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['TelefonoContactoLlamada'];}?>">
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Ciudad</label>
								<input name="CiudadLlamada" type="text" class="form-control" id="CiudadLlamada" maxlength="100" value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['CiudadLlamada'];}?>" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?>>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Correo</label>
								<input name="CorreoLlamada" type="email" class="form-control" id="CorreoLlamada" maxlength="100" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?> value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['CorreoContactoLlamada'];}?>">
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-8 border-bottom">
								<label class="control-label text-danger">Información del servicio</label>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-8">
								<label class="control-label"><i onClick="ConsultarArticulo();" title="Consultar ID Servicio" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> ID servicio <span class="text-danger">*</span></label>
								<input name="IdArticuloLlamada" type="hidden" id="IdArticuloLlamada" value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['IdArticuloLlamada'];} elseif ($dt_LS == 1 && isset($row_Articulo['ItemCode'])) {echo $row_Articulo['ItemCode'];}?>">

								<!-- Descripción del Item -->
								<input name="DeArticuloLlamada" type="text" required="required" class="form-control" id="DeArticuloLlamada" placeholder="Digite para buscar..."
								<?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>
								value="<?php if (($type_llmd == 1 || $sw_error == 1 || $dt_LS == 1) && isset($row_Articulo['ItemCode'])) {echo $row_Articulo['ItemCode'] . " - " . $row_Articulo['ItemName'];}?>">
							</div>
							<div class="col-lg-4">
								<label class="control-label"><i onClick="ConsultarEquipo();" title="Consultar tarjeta de equipo" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Tarjeta de equipo</label>
								<select name="NumeroSerie" class="form-control select2" id="NumeroSerie" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
									<?php if (($type_llmd == 1) || ($sw_error == 1)) {while ($row_NumeroSerie = sqlsrv_fetch_array($SQL_NumeroSerie)) {?>
										<option value="<?php echo $row_NumeroSerie['SerialInterno']; ?>" data-id="<?php echo $row_NumeroSerie['IdTarjetaEquipo'] ?? ""; ?>" <?php if ((isset($row_NumeroSerie['SerialInterno'])) && (strcmp($row_NumeroSerie['SerialInterno'], $row['IdNumeroSerie']) == 0)) {echo "selected=\"selected\"";} elseif ((isset($_GET['Serial'])) && (strcmp(base64_decode($_GET['Serial']), $row_NumeroSerie['SerialInterno']) == 0)) {echo "selected=\"selected\"";}?>><?php echo "SN Fabricante: " . $row_NumeroSerie['SerialFabricante'] . " - Núm. Serie: " . $row_NumeroSerie['SerialInterno']; ?></option>
									<?php }}?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Cantidad artículo</label>
							<input name="CantArticulo" type="text" class="form-control" id="CantArticulo" maxlength="50" value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo number_format($row['CDU_CantArticulo'], 2);}?>" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?> onKeyPress="return justNumbers(event,this.value);" onKeyUp="revisaCadena(this);">
							</div>
							<div class="col-lg-4">
								<label class="control-label">Precio artículo</label>
							<input name="PrecioArticulo" type="text" class="form-control" id="PrecioArticulo" maxlength="50" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?> value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo number_format($row['CDU_PrecioArticulo'], 2);}?>" onKeyPress="return justNumbers(event,this.value);" onKeyUp="revisaCadena(this);">
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-8 border-bottom">
								<label class="control-label text-danger">Información de la lista de materiales</label>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-8">
								<label class="control-label"><i onClick="ConsultarMateriales();" title="Consultar Lista de Materiales" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> ID lista de materiales</label>
								<select name="CDU_ListaMateriales" class="form-control select2" id="CDU_ListaMateriales" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
									<?php if (($type_llmd == 1) || ($sw_error == 1)) {while ($row_ListaMateriales = sqlsrv_fetch_array($SQL_ListaMateriales)) {?>
										<option value="<?php echo $row_ListaMateriales['ItemCode']; ?>" <?php if ((isset($row['CDU_ListaMateriales'])) && (strcmp($row_ListaMateriales['ItemCode'], $row['CDU_ListaMateriales']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_ListaMateriales['ItemName']; ?></option>
									<?php }}?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Tiempo tarea (Minutos) <span class="text-danger">*</span></label>
								<input name="CDU_TiempoTarea" type="number" class="form-control" id="CDU_TiempoTarea" required="required" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?> value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['CDU_TiempoTarea'];}?>">
							</div>
						</div>
					</div>
				</div>
				</div>
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-info-circle"></i> Información de servicio</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<div class="col-lg-8 border-bottom m-r-sm">
								<label class="control-label text-danger">Información básica</label>
							</div>
							<div class="col-lg-3 border-bottom ">
								<label class="control-label text-danger">Programación</label>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Serie <span class="text-danger">*</span></label>
								<select name="Series" class="form-control" required="required" id="Series" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
									<?php while ($row_Series = sqlsrv_fetch_array($SQL_Series)) {?>
										<option value="<?php echo $row_Series['IdSeries']; ?>"
										<?php if ((isset($row['Series'])) && (strcmp($row_Series['IdSeries'], $row['Series']) == 0)) {echo "selected=\"selected\"";} elseif ((isset($row['IdSeries'])) && (strcmp($row_Series['IdSeries'], $row['IdSeries']) == 0)) {echo "selected=\"selected\"";}?>>
											<?php echo $row_Series['DeSeries']; ?>
										</option>
								  	<?php }?>
								</select>
							</div>
							<div class="col-lg-2">
								<label class="control-label">Número de llamada</label>
								<input autocomplete="off" name="Ticket" type="text" class="form-control" id="Ticket" maxlength="50" readonly="readonly" value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['DocNum'];}?>">
							</div>
							<div class="col-lg-2">
								<label class="control-label">ID de llamada</label>
								<input autocomplete="off" name="CallID" type="text" class="form-control" id="CallID" maxlength="50" readonly="readonly" value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['ID_LlamadaServicio'];}?>">
							</div>
							<div class="col-lg-4">
								<label class="control-label">Fecha de creación <span class="text-danger">*</span></label>
								<div class="input-group date">
									 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaCreacion" type="text" required="required" class="form-control" id="FechaCreacion" value="<?php if (($type_llmd == 1) && ($row['FechaCreacionLLamada']) != "") {echo $row['FechaCreacionLLamada'];} else {echo date('Y-m-d');}?>" readonly='readonly'>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-8">
								<label class="control-label">Asunto de llamada <span class="text-danger">*</span></label>
								<input autocomplete="off" name="AsuntoLlamada" type="text" required="required" class="form-control" id="AsuntoLlamada" maxlength="150" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?> value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['AsuntoLlamada'];} else {echo $TituloLlamada;}?>">
							</div>
							<div class="col-lg-4">
								<label class="control-label">Hora de creación <span class="text-danger">*</span></label>
								<div class="input-group clockpicker" data-autoclose="true">
									<span class="input-group-addon">
										<span class="fa fa-clock-o"></span>
									</span>
									<input name="HoraCreacion" id="HoraCreacion" type="text" class="form-control" value="<?php if ($type_llmd == 1) {echo $row['FechaHoraCreacionLLamada']->format('H:i');} else {echo date('H:i');}?>" required="required" readonly='readonly'>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Origen <span class="text-danger">*</span></label>
								<select name="OrigenLlamada" class="form-control" required="required" id="OrigenLlamada" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								  <?php while ($row_OrigenLlamada = sqlsrv_fetch_array($SQL_OrigenLlamada)) {?>
										<option value="<?php echo $row_OrigenLlamada['IdOrigenLlamada']; ?>" <?php if ((isset($row['IdOrigenLlamada'])) && (strcmp($row_OrigenLlamada['IdOrigenLlamada'], $row['IdOrigenLlamada']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_OrigenLlamada['DeOrigenLlamada']; ?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Tipo llamada (Tipo Cliente) <span class="text-danger">*</span></label>
								<select name="TipoLlamada" class="form-control" required="required" id="TipoLlamada" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								  <?php while ($row_TipoLlamadas = sqlsrv_fetch_array($SQL_TipoLlamadas)) {?>
										<option value="<?php echo $row_TipoLlamadas['IdTipoLlamada']; ?>" <?php if ((isset($row['IdTipoLlamada'])) && (strcmp($row_TipoLlamadas['IdTipoLlamada'], $row['IdTipoLlamada']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_TipoLlamadas['DeTipoLlamada']; ?></option>
								  <?php }?>
								</select>
							</div>
							<!-- SMM -->
							<div class="col-lg-4">
								<label class="control-label">Fecha Agenda <?php if (PermitirFuncion(323) && PermitirFuncion(304)) {?><span class="text-danger">*</span><?php }?></label>
								<div class="input-group date">
									 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input <?php if ($type_llmd != 0) {?> readonly <?php }?> <?php if (PermitirFuncion(323) && PermitirFuncion(304)) {?> required <?php }?> name="FechaAgenda" type="text" class="form-control" id="FechaAgenda" value="<?php if (($type_llmd == 1) && ($row['FechaAgenda'] != "")) {echo is_string($row['FechaAgenda']) ? date("Y-m-d", strtotime($row['FechaAgenda'])) : $row['FechaAgenda']->format("Y-m-d");} else {echo date('Y-m-d');}?>">
								</div>
							</div>
							<!-- 01/06/2022 -->
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Tipo problema (Tipo Servicio) <span class="text-danger">*</span></label>
								<select name="TipoProblema" class="form-control" id="TipoProblema" required <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								  <?php while ($row_TipoProblema = sqlsrv_fetch_array($SQL_TipoProblema)) {?>
										<option value="<?php echo $row_TipoProblema['IdTipoProblemaLlamada']; ?>" <?php if ((isset($row['IdTipoProblemaLlamada'])) && (strcmp($row_TipoProblema['IdTipoProblemaLlamada'], $row['IdTipoProblemaLlamada']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_TipoProblema['DeTipoProblemaLlamada']; ?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">SubTipo problema (Subtipo Servicio) <span class="text-danger">*</span></label>
								<select name="SubTipoProblema" class="form-control" required id="SubTipoProblema" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								  <?php while ($row_SubTipoProblema = sqlsrv_fetch_array($SQL_SubTipoProblema)) {?>
										<option value="<?php echo $row_SubTipoProblema['IdSubTipoProblemaLlamada']; ?>" <?php if ((isset($row['IdSubTipoProblemaLlamada'])) && (strcmp($row_SubTipoProblema['IdSubTipoProblemaLlamada'], $row['IdSubTipoProblemaLlamada']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_SubTipoProblema['DeSubTipoProblemaLlamada']; ?></option>
								  <?php }?>
								</select>
							</div>
							<!-- SMM -->
							<div class="col-lg-4">
								<label class="control-label">Hora Agenda <?php if (PermitirFuncion(323) && PermitirFuncion(304)) {?><span class="text-danger">*</span><?php }?></label>
								<div class="input-group clockpicker" data-autoclose="true">
									<span class="input-group-addon">
										<span class="fa fa-clock-o"></span>
									</span>
									<input <?php if ($type_llmd != 0) {?> readonly <?php }?> <?php if (PermitirFuncion(323) && PermitirFuncion(304)) {?> required <?php }?> name="HoraAgenda" id="HoraAgenda" type="text" class="form-control" value="<?php if (($type_llmd == 1) && ($row['HoraAgenda'] != "")) {echo is_string($row['FechaAgenda']) ? date("H:i", strtotime($row['HoraAgenda'])) : $row['HoraAgenda']->format("H:i");} else {echo date('H:i');}?>" required="required">
								</div>
							</div>
							<!-- 01/06/2022 -->
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label"><i onClick="ConsultarContrato();" title="Consultar Contrato servicio" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Contrato servicio</label>
								<select name="ContratoServicio" class="form-control" id="ContratoServicio" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
									<?php if (($type_llmd == 1) || ($sw_error == 1)) {while ($row_Contrato = sqlsrv_fetch_array($SQL_Contrato)) {?>
										<option value="<?php echo $row_Contrato['ID_Contrato']; ?>" <?php if ((isset($row_Contrato['ID_Contrato'])) && (strcmp($row_Contrato['ID_Contrato'], $row['IdContratoServicio']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Contrato['ID_Contrato'] . " - " . $row_Contrato['DE_Contrato']; ?></option>
									<?php }}?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Cola</label>
								<select name="ColaLlamada" class="form-control" id="ColaLlamada" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								  <?php /*while($row_ColaLlamada=sqlsrv_fetch_array($SQL_ColaLlamada)){?>
<option value="<?php echo $row_ColaLlamada['IdColaLlamada'];?>" <?php if((isset($row['IdColaLlamada']))&&(strcmp($row_ColaLlamada['IdColaLlamada'],$row['IdColaLlamada'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ColaLlamada['DeColaLlamada'];?></option>
<?php }*/?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Aseguradora <span class="text-danger">*</span></label>
								<select name="CDU_Aseguradora" class="form-control select2" required="required" id="CDU_Aseguradora"
								<?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="" disabled selected>Seleccione...</option>
								  <?php while ($row_Aseguradora = sqlsrv_fetch_array($SQL_Aseguradora)) {?>
										<option value="<?php echo $row_Aseguradora['NombreAseguradora']; ?>"
										<?php if ((isset($row['CDU_Aseguradora'])) && (strcmp($row_Aseguradora['NombreAseguradora'], $row['CDU_Aseguradora']) == 0)) {echo "selected=\"selected\"";}?>>
											<?php echo $row_Aseguradora['NombreAseguradora']; ?>
										</option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Contrato/Campaña <span class="text-danger">*</span></label>
								<select name="CDU_Contrato" class="form-control select2" required="required" id="CDU_Contrato"
								<?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="" disabled selected>Seleccione...</option>
								  <?php while ($row_Contrato = sqlsrv_fetch_array($SQL_ContratosLlamada)) {?>
										<option value="<?php echo $row_Contrato['NombreContrato']; ?>"
										<?php if ((isset($row['CDU_Contrato'])) && (strcmp($row_Contrato['NombreContrato'], $row['CDU_Contrato']) == 0)) {echo "selected=\"selected\"";}?>>
											<?php echo $row_Contrato['NombreContrato']; ?>
										</option>
								  <?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-8 border-bottom">
								<label class="control-label text-danger">Información de responsables</label>
							</div>
							<div class="col-lg-4 border-bottom">
								<label class="control-label text-danger">Estados de servicio</label>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Asignado a</label>
								<select name="EmpleadoLlamada" class="form-control select2" id="EmpleadoLlamada" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="">(Sin asignar)</option>
								  <?php while ($row_EmpleadoLlamada = sqlsrv_fetch_array($SQL_EmpleadoLlamada)) {?>
										<option value="<?php echo $row_EmpleadoLlamada['ID_Empleado']; ?>" <?php if ((isset($row['IdAsignadoA'])) && (strcmp($row_EmpleadoLlamada['ID_Empleado'], $row['IdAsignadoA']) == 0)) {echo "selected=\"selected\"";} elseif (($type_llmd == 0) && (isset($_SESSION['CodigoSAP'])) && (strcmp($row_EmpleadoLlamada['ID_Empleado'], $_SESSION['CodigoSAP']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_EmpleadoLlamada['NombreEmpleado']; ?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Técnico/Asesor <?php if (PermitirFuncion(323) && PermitirFuncion(304)) {?><span class="text-danger">*</span><?php }?></label>
								<select <?php if (PermitirFuncion(323) && PermitirFuncion(304)) {?> required <?php }?> name="Tecnico" class="form-control select2" id="Tecnico" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								  <?php while ($row_Tecnicos = sqlsrv_fetch_array($SQL_Tecnicos)) {?>
										<option value="<?php echo $row_Tecnicos['ID_Empleado']; ?>" <?php if ((isset($row['IdTecnico'])) && (strcmp($row_Tecnicos['ID_Empleado'], $row['IdTecnico']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Tecnicos['NombreEmpleado']; ?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Estado <span class="text-danger">*</span></label>
								<select name="EstadoLlamada" class="form-control" id="EstadoLlamada" required="required" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
								  <?php while ($row_EstadoLlamada = sqlsrv_fetch_array($SQL_EstadoLlamada)) {?>
										<option value="<?php echo $row_EstadoLlamada['Cod_Estado']; ?>" <?php if ((isset($row['IdEstadoLlamada'])) && (strcmp($row_EstadoLlamada['Cod_Estado'], $row['IdEstadoLlamada']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_EstadoLlamada['NombreEstado']; ?></option>
								  <?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Proyecto</label>
								<select name="Proyecto" class="form-control select2" id="Proyecto" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								  <?php while ($row_Proyecto = sqlsrv_fetch_array($SQL_Proyecto)) {?>
										<option value="<?php echo $row_Proyecto['IdProyecto']; ?>" <?php if ((isset($row['IdProyecto'])) && (strcmp($row_Proyecto['IdProyecto'], $row['IdProyecto']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Proyecto['DeProyecto']; ?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Técnico/Asesor Adicional</label>
								<select name="CDU_IdTecnicoAdicional" class="form-control select2" id="CDU_IdTecnicoAdicional" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								  <?php while ($row_Tecnicos = sqlsrv_fetch_array($SQL_TecnicosAdicionales)) {?>
										<option value="<?php echo $row_Tecnicos['ID_Empleado']; ?>" <?php if ((isset($row['CDU_IdTecnicoAdicional'])) && (strcmp($row_Tecnicos['ID_Empleado'], $row['CDU_IdTecnicoAdicional']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Tecnicos['NombreEmpleado']; ?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Estado de servicio <span class="text-danger">*</span></label>
								<select name="CDU_EstadoServicio" class="form-control" id="CDU_EstadoServicio" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?> required>
								  <?php while ($row_EstServLlamada = sqlsrv_fetch_array($SQL_EstServLlamada)) {?>
										<option value="<?php echo $row_EstServLlamada['IdEstadoServicio']; ?>" <?php if ((($type_llmd == 0) && ($row_EstServLlamada['IdEstadoServicio'] == 0)) || ((isset($row['CDU_EstadoServicio'])) && (strcmp($row_EstServLlamada['IdEstadoServicio'], $row['CDU_EstadoServicio']) == 0))) {echo "selected=\"selected\"";}?>><?php echo $row_EstServLlamada['DeEstadoServicio']; ?></option>
								  <?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-8">
								<label class="control-label">Comentario <span class="text-danger">*</span></label>
								<textarea name="ComentarioLlamada" rows="7" maxlength="3000" required="required" class="form-control" id="ComentarioLlamada" type="text" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?>><?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['ComentarioLlamada'];}?></textarea>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Cancelado por <span class="text-danger">*</span></label>
								<select name="CDU_CanceladoPor" class="form-control" id="CDU_CanceladoPor" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?> required>
								  <?php while ($row_CanceladoPorLlamada = sqlsrv_fetch_array($SQL_CanceladoPorLlamada)) {?>
										<option value="<?php echo $row_CanceladoPorLlamada['IdCanceladoPor']; ?>" <?php if ((isset($row['CDU_CanceladoPor'])) && (strcmp($row_CanceladoPorLlamada['IdCanceladoPor'], $row['CDU_CanceladoPor']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_CanceladoPorLlamada['DeCanceladoPor']; ?></option>
								  <?php }?>
								</select>
							</div>
						</div>
					</div>
				</div>

				<!-- INICIO, información del vehículo y de la cita -->
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-info-circle"></i> Información del vehículo y de la cita</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>
					</div>
					<div class="ibox-content">

						<!-- Agregado por Stiven Muñoz Murillo -->
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Kilometros <span class="text-danger">*</span></label>
								<input autocomplete="off" name="CDU_Kilometros" type="number" class="form-control" id="CDU_Kilometros" maxlength="100"
								value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['CDU_Kilometros'];}?>" required="required"
								<?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?>>
							</div>

							<!-- SMM, 14/09/2022 -->
							<div class="col-lg-4">
								<label class="control-label">Tipo preventivo <span class="text-danger">*</span></label>
								<select name="CDU_TipoPreventivo" class="form-control select2" required="required" id="CDU_TipoPreventivo"
								<?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="" disabled selected>Seleccione...</option>
								  <?php while ($row_TipoPreventivo = sqlsrv_fetch_array($SQL_TipoPreventivo)) {?>
										<option value="<?php echo $row_TipoPreventivo['CodigoTipoPreventivo']; ?>"
										<?php if ((isset($row['CDU_TipoPreventivo'])) && (strcmp($row_TipoPreventivo['CodigoTipoPreventivo'], $row['CDU_TipoPreventivo']) == 0)) {echo "selected=\"selected\"";}?>>
											<?php echo $row_TipoPreventivo['TipoPreventivo']; ?>
										</option>
								  <?php }?>
								</select>
							</div>
							<!-- Hasta aquí, 14/09/2022 -->
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Marca del vehículo <span class="text-danger">*</span></label>
								<select name="CDU_Marca" class="form-control select2" required="required" id="CDU_Marca"
								<?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
									<option value="" disabled selected>Seleccione...</option>
								  <?php while ($row_MarcaVehiculo = sqlsrv_fetch_array($SQL_MarcaVehiculo)) {?>
									<option value="<?php echo $row_MarcaVehiculo['IdMarcaVehiculo']; ?>"
									<?php if ((isset($row['CDU_IdMarca'])) && (strcmp($row_MarcaVehiculo['IdMarcaVehiculo'], $row['CDU_IdMarca']) == 0)) {echo "selected=\"selected\"";} elseif ((isset($row['CDU_Marca'])) && (strcmp($row_MarcaVehiculo['IdMarcaVehiculo'], $row['CDU_Marca']) == 0)) {echo "selected=\"selected\"";}?>>
										<?php echo $row_MarcaVehiculo['DeMarcaVehiculo']; ?>
									</option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Línea del vehículo <span class="text-danger">*</span></label>
								<select name="CDU_Linea" class="form-control select2" required="required" id="CDU_Linea"
								<?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="" disabled selected>Seleccione...</option>
								  <?php while ($row_LineaVehiculo = sqlsrv_fetch_array($SQL_LineaVehiculo)) {?>
										<option value="<?php echo $row_LineaVehiculo['IdLineaModeloVehiculo']; ?>"
										<?php if ((isset($row['CDU_IdLinea'])) && (strcmp($row_LineaVehiculo['IdLineaModeloVehiculo'], $row['CDU_IdLinea']) == 0)) {echo "selected=\"selected\"";} elseif ((isset($row['CDU_Linea'])) && (strcmp($row_LineaVehiculo['IdLineaModeloVehiculo'], $row['CDU_Linea']) == 0)) {echo "selected=\"selected\"";}?>>
											<?php echo $row_LineaVehiculo['DeLineaModeloVehiculo']; ?>
										</option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Modelo del vehículo <span class="text-danger">*</span></label>
								<select name="CDU_Ano" class="form-control select2" required="required" id="CDU_Ano"
								<?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="" disabled selected>Seleccione...</option>
								  <?php while ($row_ModeloVehiculo = sqlsrv_fetch_array($SQL_ModeloVehiculo)) {?>
										<option value="<?php echo $row_ModeloVehiculo['CodigoModeloVehiculo']; ?>"
										<?php if ((isset($row['CDU_Ano'])) && ((strcmp($row_ModeloVehiculo['CodigoModeloVehiculo'], $row['CDU_Ano']) == 0) || (strcmp($row_ModeloVehiculo['AñoModeloVehiculo'], $row['CDU_Ano']) == 0))) {echo "selected=\"selected\"";}?>>
											<?php echo $row_ModeloVehiculo['AñoModeloVehiculo']; ?>
										</option>
								  <?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Concesionario <span class="text-danger">*</span></label>
								<select name="CDU_Concesionario" class="form-control select2" required="required" id="CDU_Concesionario"
								<?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="" disabled selected>Seleccione...</option>
								  <?php while ($row_Concesionario = sqlsrv_fetch_array($SQL_Concesionario)) {?>
										<option value="<?php echo $row_Concesionario['NombreConcesionario']; ?>"
										<?php if ((isset($row['CDU_Concesionario'])) && (strcmp($row_Concesionario['NombreConcesionario'], $row['CDU_Concesionario']) == 0)) {echo "selected=\"selected\"";}?>>
											<?php echo $row_Concesionario['NombreConcesionario']; ?>
										</option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Tipo servicio <span class="text-danger">*</span></label>
								<select name="CDU_TipoServicio" class="form-control select2" required="required" id="CDU_TipoServicio"
								<?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "disabled='disabled'";}?>>
										<option value="" disabled selected>Seleccione...</option>
								  <?php while ($row_TipoServicio = sqlsrv_fetch_array($SQL_TipoServicio)) {?>
										<option value="<?php echo $row_TipoServicio['NombreTipoServicio']; ?>"
										<?php if ((isset($row['CDU_TipoServicio'])) && (strcmp($row_TipoServicio['NombreTipoServicio'], $row['CDU_TipoServicio']) == 0)) {echo "selected=\"selected\"";}?>>
											<?php echo $row_TipoServicio['NombreTipoServicio']; ?>
										</option>
								  <?php }?>
								</select>
							</div>
						</div>
						<!-- Agregado, hasta aquí -->
					</div>
				</div>
				<!-- FIN, información del vehículo y de la cita -->

				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-edit"></i> Información adicional</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<div class="col-lg-5 border-bottom m-r-sm">
								<label class="control-label text-danger">Información del contacto del cliente</label>
							</div>
							<div class="col-lg-6 border-bottom ">
								<label class="control-label text-danger">Información del servicio</label>
							</div>
						</div>
						<div class="col-lg-5 m-r-md">
							<div class="form-group">
								<label class="control-label">Nombre de contacto <?php if (PermitirFuncion(324)) {?><span class="text-danger">*</span><?php }?></label>
								<input <?php if (PermitirFuncion(324)) {?> required <?php }?> autocomplete="off" name="CDU_NombreContacto" type="text" class="form-control" id="CDU_NombreContacto" maxlength="100" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?> value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['CDU_NombreContacto'];}?>">
							</div>
							<div class="form-group">
								<label class="control-label">Cargo de contacto <?php if (PermitirFuncion(324)) {?><span class="text-danger">*</span><?php }?></label>
								<input <?php if (PermitirFuncion(324)) {?> required <?php }?> autocomplete="off" name="CDU_CargoContacto" type="text" class="form-control" id="CDU_CargoContacto" maxlength="100" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?> value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['CDU_CargoContacto'];}?>">
							</div>
							<div class="form-group">
								<label class="control-label">Teléfono de contacto <?php if (PermitirFuncion(324)) {?><span class="text-danger">*</span><?php }?></label>
								<input <?php if (PermitirFuncion(324)) {?> required <?php }?> autocomplete="off" name="CDU_TelefonoContacto" type="text" class="form-control" id="CDU_TelefonoContacto" maxlength="100" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?> value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['CDU_TelefonoContacto'];}?>">
							</div>
							<div class="form-group">
								<label class="control-label">Correo de contacto <?php if (PermitirFuncion(324)) {?><span class="text-danger">*</span><?php }?></label>
								<input <?php if (PermitirFuncion(324)) {?> required <?php }?> autocomplete="off" name="CDU_CorreoContacto" type="email" class="form-control" id="CDU_CorreoContacto" maxlength="100" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?> value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['CDU_CorreoContacto'];}?>">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label class="control-label">Servicios</label>
								<textarea name="CDU_Servicios" rows="5" maxlength="2000" class="form-control" id="CDU_Servicios" type="text" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?>><?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['CDU_Servicios'];}?></textarea>
							</div>
							<div class="form-group">
								<label class="control-label">Áreas</label>
								<textarea name="CDU_Areas" rows="5" maxlength="2000" class="form-control" id="CDU_Areas" type="text" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?>><?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['CDU_Areas'];}?></textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-check-circle"></i> Cierre de llamada de servicio</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<div class="col-lg-8">
								<label class="control-label">Resolución de llamada</label>
								<textarea name="ResolucionLlamada" rows="5" maxlength="3000" type="text" class="form-control" id="ResolucionLlamada" <?php if (($type_llmd == 1) && (!PermitirFuncion(302) || ($row['IdEstadoLlamada'] == '-1'))) {echo "readonly='readonly'";}?>><?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['ResolucionLlamada'];}?></textarea>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-4">
								<label class="control-label">Fecha de cierre <span class="text-danger">*</span></label>
								<div class="input-group date">
									 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaCierre" type="text" required="required" class="form-control" id="FechaCierre" value="<?php if (($type_llmd == 1) && ($row['FechaCierreLLamada']) != "") {echo $row['FechaCierreLLamada'];} else {echo date('Y-m-d');}?>" readonly='readonly'>
								</div>
							</div>
							<div class="col-lg-4">
								<label class="control-label">Hora de cierre <span class="text-danger">*</span></label>
								<div class="input-group clockpicker" data-autoclose="true">
									<input name="HoraCierre" id="HoraCierre" type="text" class="form-control" value="<?php if (($type_llmd == 1) && ($row['FechaCierreLLamada']) != "") {echo $row['FechaHoraCierreLLamada']->format('H:i');} else {echo date('H:i');}?>" required="required" readonly='readonly'>
									<span class="input-group-addon">
										<span class="fa fa-clock-o"></span>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- SMM, 16/09/2022 -->
				<?php if ($type_llmd == 1) {?>
					<div class="ibox">
						<div class="ibox-title bg-success">
							<h5 class="collapse-link"><i class="fa fa-check-circle"></i> Contacto cierre de llamada de servicio</h5>
							<a class="collapse-link pull-right">
								<i class="fa fa-chevron-up"></i>
							</a>
						</div> <!-- ibox-title -->
						<div class="ibox-content">
							<div class="form-group">
								<div class="col-lg-5 border-bottom m-r-sm">
									<label class="control-label text-danger">Información de destinatarios</label>
								</div>
								<div class="col-lg-6 border-bottom ">
									<label class="control-label text-danger">Firma del cliente</label>
								</div>
							</div>
							<div class="col-lg-5 m-r-md">

								<div class="form-group">
									<label class="control-label">Correos Destinatarios (Máximo 4) <span class="text-danger cierre-span">*</span></label>
									<input placeholder="Ingrese un nuevo correo y utilice la tecla [ESP] para agregar" onKeyUp="ValidarCorreo(event, this)" <?php if (!$testMode) {echo "readonly";}?> autocomplete="off" name="CorreoContactoFirma" type="text" class="form-control cierre-input" id="CorreoContactoFirma" maxlength="50" value="">
									<input type="hidden" id="CorreosContactosFirma" name="CorreosContactosFirma">

									<div id="CorreosDestinatarios">
										<?php if (($type_llmd == 1) || ($sw_error == 1)) {?>
											<?php $CorreosContactosFirma = explode(";", $row['CorreoContactoFirma']);?>
											<?php foreach ($CorreosContactosFirma as &$Correo) {?>
												<?php if ($Correo != "") {?>
													<span class="badge badge-secondary" style="cursor: not-allowed;"><i class="fa fa-trash"></i> <?php echo $Correo; ?></span>
												<?php }?>
											<?php }?>
										<?php }?>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label">Teléfonos Destinatarios (Máximo 4) <span class="text-danger cierre-span">*</span></label>
									<input placeholder="Ingrese un nuevo teléfono y utilice la tecla [ESP] para agregar" onKeyUp="ValidarTelefono(event, this)" <?php if (!$testMode) {echo "readonly";}?> autocomplete="off" name="TelefonoContactoFirma" type="text" class="form-control cierre-input" id="TelefonoContactoFirma" maxlength="10" value="">
									<input type="hidden" id="TelefonosContactosFirma" name="TelefonosContactosFirma">

									<div id="TelefonosDestinatarios">
										<?php if (($type_llmd == 1) || ($sw_error == 1)) {?>
											<?php $TelefonosContactosFirma = explode(";", $row['TelefonoContactoFirma']);?>
											<?php foreach ($TelefonosContactosFirma as &$Telefono) {?>
												<?php if ($Telefono != "") {?>
													<span class="badge badge-secondary" style="cursor: not-allowed;"><i class="fa fa-trash"></i> <?php echo $Telefono; ?></span>
												<?php }?>
											<?php }?>
										<?php }?>
									</div>
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group">
									<label class="control-label">Nombre del cliente <!-- span class="text-danger cierre-span">*</span --></label>
									<input <?php if (!$testMode) {echo "readonly";}?> autocomplete="off" name="NombreContactoFirma" type="text" class="form-control cierre-input" id="NombreContactoFirma" maxlength="100" value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['NombreContactoFirma'] ?? "";}?>">
								</div>
								<div class="form-group">
									<label class="control-label">Cédula del cliente <!-- span class="text-danger cierre-span">*</span --></label>
									<input <?php if (!$testMode) {echo "readonly";}?> autocomplete="off" name="CedulaContactoFirma" type="number" class="form-control cierre-input" id="CedulaContactoFirma" maxlength="15" value="<?php if (($type_llmd == 1) || ($sw_error == 1)) {echo $row['CedulaContactoFirma'] ?? "";}?>">
								</div>
								<!-- Componente "firma"-->
								<br><br>
								<div class="form-group">
									<label class="col-lg-2">Firma del cliente <!-- span class="text-danger cierre-span">*</span --></label>
									<?php if (($sw_error == 1) || (($type_llmd == 1) && ($row['IdEstadoLlamada'] == '-1'))) {?>
										<?php if (isset($row['FirmaContactoResponsable']) && ($row['FirmaContactoResponsable'] != "")) {?>
											<div class="col-lg-10">
												<span class="badge badge-primary">Firmado</span>
											</div>
										<?php } else {?>
											<div class="col-lg-10">
												<span class="badge badge-danger">NO Firmado</span>
											</div>
										<?php }?>
									<?php } else { //LimpiarDirTempFirma();?>
									<div class="col-lg-5">
										<button <?php if (!$testMode) {echo "disabled";}?> class="btn btn-primary cierre-input" type="button" id="FirmaCliente" onClick="AbrirFirma('FirmaContactoResponsable');"><i class="fa fa-pencil-square-o"></i> Realizar firma</button>
										<br>
										<input readonly type="text" id="FirmaContactoResponsable" name="FirmaContactoResponsable" style="width: 100px; margin-left: -7px; visibility: hidden;" value="">
										<div id="msgInfoFirmaContactoResponsable" style="display: none;" class="alert alert-info"><i class="fa fa-info-circle"></i> El documento ya ha sido firmado.</div>
									</div>
									<div class="col-lg-5">
										<img id="ImgFirmaContactoResponsable" style="display: none; max-width: 100%; height: auto;" src="" alt="" />
									</div>
									<?php }?>
								</div>
								<!-- Hasta aquí -->
							</div>
						</div> <!-- ibox-content -->
					</div> <!-- ibox -->
				<?php }?>
				<!-- Hasta aquí, 16/09/2022 -->

				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-paperclip"></i> Anexos</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>
					</div>
					<div class="ibox-content">
						<?php if ($type_llmd == 1) {
    if ($row['IdAnexoLlamada'] != 0) {?>
								<div class="form-group">
									<div class="col-xs-12">
										<?php while ($row_AnexoLlamada = sqlsrv_fetch_array($SQL_AnexoLlamada)) {
        $Icon = IconAttach($row_AnexoLlamada['FileExt']);?>
											<div class="file-box">
												<div class="file">
													<a href="attachdownload.php?file=<?php echo base64_encode($row_AnexoLlamada['AbsEntry']); ?>&line=<?php echo base64_encode($row_AnexoLlamada['Line']); ?>" target="_blank">
														<div class="icon">
															<i class="<?php echo $Icon; ?>"></i>
														</div>
														<div class="file-name">
															<?php echo $row_AnexoLlamada['NombreArchivo']; ?>
															<br/>
															<small><?php echo $row_AnexoLlamada['Fecha']; ?></small>
														</div>
													</a>
												</div>
											</div>
										<?php }?>
									</div>
								</div>
						<?php } else {echo "<p>Sin anexos.</p>";}
}?>
						<?php
if (isset($_GET['return'])) {
    $return = base64_decode($_GET['pag']) . "?" . $_GET['return'];
} else {
    $return = "gestionar_llamadas_servicios.php";
}
$return = QuitarParametrosURL($return, array("a"));?>
						<input type="hidden" id="P" name="P" value="<?php if (($type_llmd == 0) && ($sw_error == 0)) {echo "32";} else {echo "33";}?>" />
						<input type="hidden" id="swTipo" name="swTipo" value="0" />
						<input type="hidden" id="swError" name="swError" value="<?php echo $sw_error; ?>" />
						<input type="hidden" id="tl" name="tl" value="<?php echo $type_llmd; ?>" />
						<input type="hidden" id="IdLlamadaPortal" name="IdLlamadaPortal" value="<?php if (($type_llmd == 1) && ($sw_error == 0)) {echo base64_encode($row['IdLlamadaPortal']);} elseif (($type_llmd == 1) && ($sw_error == 1)) {echo base64_encode($row['ID_LlamadaServicio']);} elseif (($type_llmd == 0) && ($sw_error == 1)) {echo base64_encode($row['ID_LlamadaServicio']);}?>" />
						<input type="hidden" id="DocEntry" name="DocEntry" value="<?php if ($type_llmd == 1) {echo base64_encode($row['ID_LlamadaServicio']);}?>" />
						<input type="hidden" id="DocNum" name="DocNum" value="<?php if ($type_llmd == 1) {echo base64_encode($row['DocNum']);}?>" />
					    <input type="hidden" id="IdAnexos" name="IdAnexos" value="<?php if ($type_llmd == 1) {echo $row['IdAnexoLlamada'];}?>" />
						<input type="hidden" id="IdSucursalCliente" name="IdSucursalCliente" value="<?php if ($type_llmd == 1) {echo $row['IdNombreSucursal'];}?>" />
					   </form>
						<?php if (($type_llmd == 0) || (($type_llmd == 1) && ($row['IdEstadoLlamada'] != '-1'))) {?>
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
				   <div class="form-group">
						<br>
						<?php if (($type_llmd == 1) && (PermitirFuncion(302) && (($row['IdEstadoLlamada'] == '-3') || ($row['IdEstadoLlamada'] == '-2')))) {?>
							<div class="col-lg-2">
								<button class="btn btn-warning" type="submit" form="CrearLlamada" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar llamada</button>
							</div>
						<?php }?>
						<?php if (($type_llmd == 1) && (PermitirFuncion(302) && ($row['IdEstadoLlamada'] == '-1'))) {?>
							<?php if (PermitirFuncion(322)) {?>
								<div class="col-lg-2">
									<button class="btn btn-success" type="submit" form="CrearLlamada" onClick="EnviarFrm('40');" id="Reabrir"><i class="fa fa-reply"></i> Reabrir</button>
								</div>
							<?php }?>
						<?php }?>
						<?php if ($type_llmd == 0) {?>
							<div class="col-lg-2">
								<button class="btn btn-primary" form="CrearLlamada" type="submit" id="Crear"><i class="fa fa-check"></i> Crear llamada <?php if (PermitirFuncion(323) && PermitirFuncion(304)) {?>y actividad<?php }?></button>
							</div>
						<?php }?>
							<div class="col-lg-2">
								<a href="<?php echo $return; ?>" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
							</div>
					</div>
			  		<br><br>
			   <?php if ($type_llmd == 1) {?>
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-pencil-square-o"></i> Seguimiento de llamada</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>
					</div>
					<div class="ibox-content">
						<div class="tabs-container">
							<ul class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-calendar"></i> Actividades</a></li>
								<li><a data-toggle="tab" href="#tab-2"><i class="fa fa-tags"></i> Documentos relacionados</a></li>
								<li><a data-toggle="tab" href="#tab-3"><i class="fa fa-clipboard"></i> Formatos adicionales</a></li>
							</ul>
							<div class="tab-content">
							<div id="tab-1" class="tab-pane active">
								<div class="panel-body">
									<div class="row">
									<?php if (PermitirFuncion(304) && (($row['IdEstadoLlamada'] == '-3') || ($row['IdEstadoLlamada'] == '-2'))) {?>
									<button type="button" onClick="javascript:location.href='actividad.php?dt_LS=1&TTarea=<?php echo base64_encode($row['TipoTarea']); ?>&Cardcode=<?php echo base64_encode($row['ID_CodigoCliente']); ?>&Contacto=<?php echo base64_encode($row['IdContactoLLamada']); ?>&Sucursal=<?php echo base64_encode($row['NombreSucursal']); ?>&Direccion=<?php echo base64_encode($row['DireccionLlamada']); ?>&Ciudad=<?php echo base64_encode($row['CiudadLlamada']); ?>&Barrio=<?php echo base64_encode($row['BarrioDireccionLlamada']); ?>&Telefono=<?php echo base64_encode($row['TelefonoContactoLlamada']); ?>&Correo=<?php echo base64_encode($row['CorreoContactoLlamada']); ?>&LS=<?php echo base64_encode($IdLlamada); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('llamada_servicio.php'); ?>'" class="alkin btn btn-primary btn-xs"><i class="fa fa-plus-circle"></i> Agregar actividad</button>
									<?php }?>
									</div>
									<br>
									<div class="table-responsive">
										<table class="table table-striped table-bordered table-hover dataTables-example" >
											<thead>
											<tr>
												<th>Número</th>
												<th>Asignado por</th>
												<th>Asignado a</th>

												<th>Perfil</th> <!-- SMM, 14/09/2022 -->

												<th>Titulo</th>
												<th>Fecha creación</th>
												<th>Fecha limite</th>
												<th>Dias venc.</th>
												<th>Estado</th>
												<th>Estado Servicio</th>
												<th>Acciones</th>
											</tr>
											</thead>
											<tbody>
											<?php while ($row_Actividad = sqlsrv_fetch_array($SQL_Actividad)) {
    if ($row_Actividad['IdEstadoActividad'] == 'N') {
        $DVenc = DiasTranscurridos(date('Y-m-d'), $row_Actividad['FechaFinActividad']);
    } else {
        $DVenc = array('text-primary', 0);
    }
    ?>
												 <tr class="gradeX">
													<td><?php echo $row_Actividad['ID_Actividad']; ?></td>
													<td><?php echo $row_Actividad['DeAsignadoPor']; ?></td>
													<td><?php if ($row_Actividad['NombreEmpleado'] != "") {echo $row_Actividad['NombreEmpleado'];} else {echo "(Sin asignar)";}?></td>

													<td><?php echo $row_Actividad['CargoEmpleado']; ?></td>

													<td><?php echo $row_Actividad['TituloActividad']; ?></td>
													<td><?php if ($row_Actividad['FechaHoraInicioActividad'] != "") {echo $row_Actividad['FechaHoraInicioActividad']->format('Y-m-d H:s');} else {?><p class="text-muted">--</p><?php }?></td>
													<td><?php if ($row_Actividad['FechaHoraFinActividad'] != "") {echo $row_Actividad['FechaHoraFinActividad']->format('Y-m-d H:s');} else {?><p class="text-muted">--</p><?php }?></td>
													<td><p class='<?php echo $DVenc[0]; ?>'><?php echo $DVenc[1]; ?></p></td>
													<td><span <?php if ($row_Actividad['IdEstadoActividad'] == 'N') {echo "class='label label-info'";} else {echo "class='label label-danger'";}?>><?php echo $row_Actividad['DeEstadoActividad']; ?></span></td>

													<td>
														<?php $SQL_TiposEstadoServ = Seleccionar("uvw_tbl_TipoEstadoServicio", "*");?>
														<?php while ($row_TipoEstadoServ = sqlsrv_fetch_array($SQL_TiposEstadoServ)) {?>
															<?php if ($row_Actividad['IdTipoEstadoActividad'] == $row_TipoEstadoServ['ID_TipoEstadoServicio']) {?>
																<span class='label text-white' style="background-color: <?php echo $row_TipoEstadoServ['ColorEstadoServicio']; ?>;"><?php echo $row_Actividad['DeTipoEstadoActividad']; ?></span>
															<?php }?>
														<?php }?>
													</td>

													<td>
														<a href="actividad.php?tl=1&id=<?php echo base64_encode($row_Actividad['ID_Actividad']); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('llamada_servicio.php'); ?>" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a>

														<!-- Botón de descarga -->
														<div class="btn-group">
															<button data-toggle="dropdown" class="btn btn-xs btn-warning dropdown-toggle"><i class="fa fa-download"></i> Descargar formato <i class="fa fa-caret-down"></i></button>
															<ul class="dropdown-menu">
																<?php $SQL_Formato = Seleccionar('uvw_tbl_FormatosSAP', '*', "ID_Objeto=66 and VerEnDocumento='Y'");?>
    															<?php while ($row_Formato = sqlsrv_fetch_array($SQL_Formato)) {?>
																	<li>
																		<a class="dropdown-item" target="_blank" href="sapdownload.php?id=<?php echo base64_encode('15'); ?>&type=<?php echo base64_encode('2'); ?>&DocKey=<?php echo base64_encode($row_Actividad['ID_Actividad']); ?>&ObType=<?php echo base64_encode('66'); ?>&IdFrm=<?php echo base64_encode($row_Formato['IdFormato']); ?>&IdReg=<?php echo base64_encode($row_Formato['ID']); ?>"><?php echo $row_Formato['NombreVisualizar']; ?></a>
																	</li>
																<?php }?>
															</ul>
														</div>
														<!-- SMM, 25/07/2022 -->
													</td>
												</tr>
											<?php }?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<div id="tab-2" class="tab-pane">
								<div class="panel-body">
									<!-- Agregar documento, Inicio -->
									<div class="row">
										<div class="col-lg-9">
											<?php if (PermitirFuncion(302) && ($row['IdEstadoLlamada'] != '-1')) {?>
												<?php if (PermitirFuncion(402)) {?>
													<div class="btn-group">
														<button data-toggle="dropdown" class="btn btn-outline btn-success dropdown-toggle"><i class="fa fa-plus-circle"></i> Agregar documento <i class="fa fa-caret-down"></i></button>
														<ul class="dropdown-menu">
															<li>
																<a class="dropdown-item alkin d-venta" href="oferta_venta.php?dt_LS=1&Cardcode=<?php echo base64_encode($row['ID_CodigoCliente']); ?>&Contacto=<?php echo base64_encode($row['IdContactoLLamada']); ?>&Sucursal=<?php echo base64_encode($row['NombreSucursal']); ?>&Direccion=<?php echo base64_encode($row['DireccionLlamada']); ?>&TipoLlamada=<?php echo base64_encode($row['IdTipoLlamada']); ?>&ItemCode=<?php echo base64_encode($row['CDU_ListaMateriales']); ?>&LS=<?php echo base64_encode($IdLlamada); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('llamada_servicio.php'); ?>">Oferta de venta con LMT</a>
															</li>
															<li>
																<a class="dropdown-item alkin d-venta" href="oferta_venta.php?dt_LS=1&Cardcode=<?php echo base64_encode($row['ID_CodigoCliente']); ?>&Contacto=<?php echo base64_encode($row['IdContactoLLamada']); ?>&Sucursal=<?php echo base64_encode($row['NombreSucursal']); ?>&Direccion=<?php echo base64_encode($row['DireccionLlamada']); ?>&TipoLlamada=<?php echo base64_encode($row['IdTipoLlamada']); ?>&ItemCode=<?php echo base64_encode($row['CDU_ListaMateriales']); ?>&LS=<?php echo base64_encode($IdLlamada); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('llamada_servicio.php'); ?>&LMT=false">Oferta de venta sin LMT</a>
															</li>
															<li>
																<a class="dropdown-item alkin d-venta" href="orden_venta.php?dt_LS=1&Cardcode=<?php echo base64_encode($row['ID_CodigoCliente']); ?>&Contacto=<?php echo base64_encode($row['IdContactoLLamada']); ?>&Sucursal=<?php echo base64_encode($row['NombreSucursal']); ?>&Direccion=<?php echo base64_encode($row['DireccionLlamada']); ?>&TipoLlamada=<?php echo base64_encode($row['IdTipoLlamada']); ?>&ItemCode=<?php echo base64_encode($row['CDU_ListaMateriales']); ?>&LS=<?php echo base64_encode($IdLlamada); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('llamada_servicio.php'); ?>">Orden de venta con LMT</a>
															</li>
															<li>
																<a class="dropdown-item alkin d-venta" href="orden_venta.php?dt_LS=1&Cardcode=<?php echo base64_encode($row['ID_CodigoCliente']); ?>&Contacto=<?php echo base64_encode($row['IdContactoLLamada']); ?>&Sucursal=<?php echo base64_encode($row['NombreSucursal']); ?>&Direccion=<?php echo base64_encode($row['DireccionLlamada']); ?>&TipoLlamada=<?php echo base64_encode($row['IdTipoLlamada']); ?>&ItemCode=<?php echo base64_encode($row['CDU_ListaMateriales']); ?>&LS=<?php echo base64_encode($IdLlamada); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('llamada_servicio.php'); ?>&LMT=false">Orden de venta sin LMT</a>
															</li>
															<li>
																<a class="dropdown-item alkin d-venta" href="entrega_venta.php?dt_LS=1&Cardcode=<?php echo base64_encode($row['ID_CodigoCliente']); ?>&Contacto=<?php echo base64_encode($row['IdContactoLLamada']); ?>&Sucursal=<?php echo base64_encode($row['NombreSucursal']); ?>&Direccion=<?php echo base64_encode($row['DireccionLlamada']); ?>&TipoLlamada=<?php echo base64_encode($row['IdTipoLlamada']); ?>&ItemCode=<?php echo base64_encode($row['CDU_ListaMateriales']); ?>&LS=<?php echo base64_encode($IdLlamada); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('llamada_servicio.php'); ?>">Entrega de venta con LMT</a>
															</li>
															<li>
																<a class="dropdown-item alkin d-venta" href="entrega_venta.php?dt_LS=1&Cardcode=<?php echo base64_encode($row['ID_CodigoCliente']); ?>&Contacto=<?php echo base64_encode($row['IdContactoLLamada']); ?>&Sucursal=<?php echo base64_encode($row['NombreSucursal']); ?>&Direccion=<?php echo base64_encode($row['DireccionLlamada']); ?>&TipoLlamada=<?php echo base64_encode($row['IdTipoLlamada']); ?>&ItemCode=<?php echo base64_encode($row['CDU_ListaMateriales']); ?>&LS=<?php echo base64_encode($IdLlamada); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('llamada_servicio.php'); ?>&LMT=false">Entrega de venta sin LMT</a>
															</li>
															<li>
																<a class="dropdown-item alkin d-venta" href="devolucion_venta.php?dt_LS=1&Cardcode=<?php echo base64_encode($row['ID_CodigoCliente']); ?>&Contacto=<?php echo base64_encode($row['IdContactoLLamada']); ?>&Sucursal=<?php echo base64_encode($row['NombreSucursal']); ?>&Direccion=<?php echo base64_encode($row['DireccionLlamada']); ?>&TipoLlamada=<?php echo base64_encode($row['IdTipoLlamada']); ?>&ItemCode=<?php echo base64_encode($row['CDU_ListaMateriales']); ?>&LS=<?php echo base64_encode($IdLlamada); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('llamada_servicio.php'); ?>">Devolución de venta con LMT</a>
															</li>
															<li>
																<a class="dropdown-item alkin d-venta" href="devolucion_venta.php?dt_LS=1&Cardcode=<?php echo base64_encode($row['ID_CodigoCliente']); ?>&Contacto=<?php echo base64_encode($row['IdContactoLLamada']); ?>&Sucursal=<?php echo base64_encode($row['NombreSucursal']); ?>&Direccion=<?php echo base64_encode($row['DireccionLlamada']); ?>&TipoLlamada=<?php echo base64_encode($row['IdTipoLlamada']); ?>&ItemCode=<?php echo base64_encode($row['CDU_ListaMateriales']); ?>&LS=<?php echo base64_encode($IdLlamada); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('llamada_servicio.php'); ?>&LMT=false">Devolución de venta sin LMT</a>
															</li>
														</ul>
													</div>
											<?php }}?>
										</div>
										<div class="col-lg-3">
											<div class="row">
												<div class="col-lg-6">
													<button class="pull-right btn btn-primary" id="btnPreCostos" name="btnPreCostos" onClick="MostrarCostos('<?php echo $IdLlamada; ?>');"><i class="fa fa-money"></i> Previsualizar Precios</button>
												</div>
												<div class="col-lg-6">
													<div class="btn-group pull-right">
														<button data-toggle="dropdown" class="btn btn-success dropdown-toggle"><i class="fa fa-mail-forward"></i> Liquidación <i class="fa fa-caret-down"></i></button>
														<ul class="dropdown-menu">
															<li><a class="alkin dropdown-item" href="#" onClick="CopiarToFactura(1);">Prefactura de venta</a></li>
															<!-- li><a class="alkin dropdown-item" href="#" onClick="CopiarToFactura(0);">Factura de venta (<strong>NO</strong> copiar adjuntos)</a></li -->
															<!--li class="dropdown-divider"></li>
															<li><a class="alkin dropdown-item" href="#" onClick="CopiarToFactura(1,2);">Orden de venta (copiar adjuntos)</a></li>
															<li><a class="alkin dropdown-item" href="#" onClick="CopiarToFactura(0,2);">Orden de venta (<strong>NO</strong> copiar adjuntos)</a></li-->
														</ul>
													</div>
												</div>
											</div>
										</div>
									</div>
									<br>
									<!-- Agregar documento, Fin -->
									<div class="table-responsive">
										<table class="table table-striped table-bordered table-hover dataTables-example" >
											<thead>
											<tr>
												<th>Nombre cliente</th>
												<th>Tipo de documento</th>
												<th>Número de documento</th>
												<th>Fecha de documento</th>
												<th>Autorización</th>
												<th>Estado de documento</th>
												<th>Creado por</th>
												<th>Artículos/Costos</th>
												<th>Acciones</th>
											</tr>
											</thead>
											<tbody>
											<?php while ($row_DocRel = sqlsrv_fetch_array($SQL_DocRel)) {?>
												 <tr class="gradeX">
												 	<td><?php echo $row_DocRel['NombreCliente']; ?></td>
												 	<td><?php echo $row_DocRel['DeObjeto']; ?></td>
													<td><?php echo $row_DocRel['DocNum']; ?></td>
													<td><?php echo $row_DocRel['DocDate']; ?></td>
													<td><?php echo $row_DocRel['DeAuthPortal']; ?></td>
													<td><span <?php if ($row_DocRel['Cod_Estado'] == 'O') {echo "class='label label-info'";} else {echo "class='label label-danger'";}?>><?php echo $row_DocRel['NombreEstado']; ?></span></td>
													<td><?php echo $row_DocRel['Usuario']; ?></td>
													<td>
														<a class="btn btn-primary btn-xs" id="btnPreCostos" name="btnPreCostos" onClick="MostrarCostos_Documentos('<?php echo $row_DocRel['DocNum']; ?>', '<?php echo $row_DocRel['IdObjeto']; ?>', '<?php echo $row_DocRel['DeObjeto']; ?>');"><i class="fa fa-money"></i> Previsualizar Precios</a>
													</td>
													<td>
													<?php if ($row_DocRel['Link'] != "") {?>
														<a href="<?php echo $row_DocRel['Link']; ?>.php?id=<?php echo base64_encode($row_DocRel['DocEntry']); ?>&id_portal=<?php echo base64_encode($row_DocRel['IdPortal']); ?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('llamada_servicio.php'); ?>" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a>
													<?php }?>
													<?php if ($row_DocRel['Descargar'] != "") {?>
														<a href="sapdownload.php?id=<?php echo base64_encode('15'); ?>&type=<?php echo base64_encode('2'); ?>&DocKey=<?php echo base64_encode($row_DocRel['DocEntry']); ?>&ObType=<?php echo base64_encode($row_DocRel['IdObjeto']); ?>&IdFrm=<?php echo base64_encode($row_DocRel['IdSeries']); ?>" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-download"></i> Descargar</a>
													<?php }?>
													</td>
												</tr>
											<?php }?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<div id="tab-3" class="tab-pane">
								<div class="panel-body">
									<!-- Agregar formato, Inicio -->
									<div class="row">
										<?php if (PermitirFuncion(302) && ($row['IdEstadoLlamada'] != '-1')) {
    if (PermitirFuncion(1706)) {?>
												<div class="btn-group">
													<button data-toggle="dropdown" class="btn btn-outline btn-success dropdown-toggle"><i class="fa fa-plus-circle"></i> Agregar formato <i class="fa fa-caret-down"></i></button>
													<ul class="dropdown-menu">
														<li>
															<a class="dropdown-item alkin" href="frm_recepcion_vehiculo.php?dt_LS=1&Cardcode=<?php echo base64_encode($row['ID_CodigoCliente']); ?>&Contacto=<?php echo base64_encode($row['IdContactoLLamada']); ?>&Sucursal=<?php echo base64_encode($row['NombreSucursal']); ?>&Direccion=<?php echo base64_encode($row['DireccionLlamada']); ?>&TipoLlamada=<?php echo base64_encode($row['IdTipoLlamada']); ?>&LS=<?php echo base64_encode($IdLlamada); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('llamada_servicio.php'); ?>">Recepción vehículo</a>
														</li>
													</ul>
												</div>
											<?php }
}?>
									</div>
									<br>
									<!-- Agregar formato, Fin -->
									<div class="table-responsive">
											<table class="table table-striped table-bordered table-hover dataTables-example" >
												<thead>
												<tr>
													<th>Tipo de documento</th>
													<th>Número de documento</th>
													<th>Fecha de documento</th>
													<th>Observaciones</th>
													<th>Comentarios de cierre</th>
													<th>Fecha cierre</th>
													<th>Creado por</th>
													<th>Estado de documento</th>
													<th>Acciones</th>
												</tr>
												</thead>
												<tbody>
												<?php while ($row_Formularios = sqlsrv_fetch_array($SQL_Formularios)) {?>
													<tr class="gradeX">
														<td><?php echo $row_Formularios['tipo_objeto']; ?></td>
														<td><?php echo $row_Formularios['id_formulario']; ?></td>
														<td><?php echo isset($row_Formularios['hora_actualizacion']) ? $row_Formularios['hora_actualizacion']->format('Y-m-d H:i') : ""; ?></td>
														<td><?php echo SubComent($row_Formularios['observaciones'], 140); ?></td>
														<td id="comentCierre<?php echo $row_Formularios['id_formulario']; ?>"><?php echo SubComent($row_Formularios['comentarios_cierre'], 140); ?></td>
														<td><?php echo ($row_Formularios['fecha_cierre'] != "") ? $row_Formularios['fecha_cierre']->format('Y-m-d H:i') : ""; ?></td>
														<td><?php echo $row_Formularios['nombre_usuario_creacion']; ?></td>
														<td><span id="lblEstado<?php echo $row_Formularios['id_formulario']; ?>" <?php if ($row_Formularios['estado'] == 'O') {echo "class='label label-info'";} elseif ($row_Formularios['estado'] == 'A') {echo "class='label label-danger'";} else {echo "class='label label-primary'";}?>><?php echo $row_Formularios['nombre_estado']; ?></span></td>
														<td class="text-center form-inline w-80">
															<?php if ($row_Formularios['estado'] == 'O') {?>
																<button id="btnEstado<?php echo $row_Formularios['id_formulario']; ?>" class="btn btn-success btn-xs" onClick="CambiarEstado('<?php echo $row_Formularios['id_formulario']; ?>','<?php echo $row_Formularios['nombre_servicio']; ?>','<?php echo $row_Formularios['columna_id']; ?>');" title="Cambiar estado"><i class="fa fa-pencil"></i></button>
															<?php }?>
															<a href="filedownload.php?file=<?php echo base64_encode($row_Formularios['nombre_servicio'] . "/DescargarFormatos/" . $row_Formularios['id_formulario'] . "/" . $_SESSION['User']); ?>&api=1" target="_blank" class="btn btn-warning btn-xs" title="Descargar"><i class="fa fa-download"></i></a>

															<!-- SMM, 05/10/2022 -->
															<?php if (isset($row_Formularios['nombre_servicio']) && ($row_Formularios['nombre_servicio'] == "RecepcionVehiculos")) {?>
																<a href="descargar_frm_recepcion_vehiculo.php?id=<?php echo $row_Formularios['id_formulario']; ?>" target="_blank" class="btn btn-danger btn-xs" title="Descargar Fotos"><i class="fa fa-file-image-o"></i></a>
															<?php }?>
														</td>
													</tr>
												<?php }?>
												</tbody>
											</table>
										</div>
									</div>
							</div>
						</div>
						</div>
					</div>
				</div>
			   <?php }?>
		   </div>
			</div>
          </div>
        </div>
        <!-- InstanceEndEditable -->
        <?php include "includes/footer.php";?>

    </div>
</div>
<?php include "includes/pie.php";?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>
	 $(document).ready(function(){
		// SMM, 11/05/2022
		<?php if (isset($_GET['Serial'])) {?>
			// $('#NumeroSerie').trigger('change');
	 	<?php }?>

		$("#CrearLlamada").validate({
			 submitHandler: function(form){
				 if(Validar()){
					 let vP=document.getElementById('P');
					 let msg= (vP.value=='40') ? "¿Está seguro que desea reabrir la llamada?" : "¿Está seguro que desea guardar los datos?";
					 let sw_ValDir=<?php echo $sw_valDir; ?>;

					if(sw_ValDir==1){
						let dirAnterior='<?php echo isset($row['NombreSucursal']) ? $row['NombreSucursal'] : ""; ?>';
						let combo = document.getElementById("SucursalCliente");
						let dirActual = combo.options[combo.selectedIndex].text;

						Swal.fire({
							title: '¡Advertencia!',
							html: 'La sucursal <strong>'+dirAnterior+'</strong> ha cambiado de nombre por <strong>'+dirActual+'</strong>. Se actualizará en la llamada de servicio.',
							icon: 'warning',
							showCancelButton: true,
							confirmButtonText: "Entendido",
							cancelButtonText: "Cancelar"
						}).then((des) => {
							if (des.isConfirmed) {
								Swal.fire({
									title: msg,
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
							}
						});
					}else{
						Swal.fire({
							title: msg,
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
					}

				}else{
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			}
		});

		maxLength('ComentarioLlamada');
		maxLength('ResolucionLlamada');

		maxLength('CDU_Servicios'); // SMM, 02/03/2022
		maxLength('CDU_Areas'); // SMM, 02/03/2022

		 <?php if (($type_llmd == 0) || (($type_llmd == 1) && (PermitirFuncion(302) && ($row['IdEstadoLlamada'] != '-1')))) {?>
		 $('#FechaCreacion, #FechaAgenda').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
			 	todayHighlight: true,
			 	//startDate: '<?php if ($type_llmd == 1) {echo $row['FechaCreacionLLamada'];} else {echo date('Y-m-d');}?>'
            });
		 $('.clockpicker').clockpicker();
		  <?php }?>
		 <?php if (($type_llmd == 1) && (PermitirFuncion(302) && ($row['IdEstadoLlamada'] != '-1'))) {?>
		 $('#FechaCierre').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
			 	todayHighlight: true,
			 	startDate: '<?php echo $row['FechaCreacionLLamada']; ?>',
			 	endDate: '<?php echo date('Y-m-d'); ?>'
            });
		<?php }?>
		 $(".select2").select2();
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
					var value = $("#NombreClienteLlamada").getSelectedItemData().CodigoCliente;
					$("#ClienteLlamada").val(value).trigger("change");
				}
			}
		};
		 var options2 = {
			url: function(phrase) {
				return "ajx_buscar_datos_json.php?type=8&id="+phrase;
			},

			getValue: "Ciudad",
			requestDelay: 400,
			template: {
				type: "description",
				fields: {
					description: "Codigo"
				}
			},
			list: {
				match: {
					enabled: true
				}
			}
		};

		// Stiven Muñoz Murillo, 24/01/2022
		var options3 = {
			url: function(phrase) {
				return "ajx_buscar_datos_json.php?type=46&id="+phrase;
			},
			getValue: "DeArticuloLlamada",
			requestDelay: 400,
			list: {
				match: {
					enabled: true
				},
				onClickEvent: function() {
					var value = $("#DeArticuloLlamada").getSelectedItemData().IdArticuloLlamada;
					$("#IdArticuloLlamada").val(value).trigger("change");
				}
			}
		};

		<?php
if ($type_llmd == 0) {?>
		$("#NombreClienteLlamada").easyAutocomplete(options);
		<?php }?>
		$("#CiudadLlamada").easyAutocomplete(options2);

		// Stiven Muñoz Murillo, 24/01/2022
		$("#DeArticuloLlamada").easyAutocomplete(options3);

		<?php if ($dt_LS == 1) {?>
		$('#ClienteLlamada').trigger('change');

		// Stiven Muñoz Murillo, 24/01/2022
		$('#IdArticuloLlamada').trigger('change');
	 	<?php }?>

		<?php
if ($type_llmd == 1) {?>
			$('#Series option:not(:selected)').attr('disabled',true);
		<?php }?>

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

// Validación de la llamada de servicio, se ejecuta al momento de crear o actualizar.
function Validar(){
	let res=true;

	let vP=document.getElementById('P');
	let EstLlamada=document.getElementById('EstadoLlamada');
	let txtResol=document.getElementById('ResolucionLlamada');
	let EstadoServicio = document.getElementById("CDU_EstadoServicio");
	let CanceladoPor = document.getElementById("CDU_CanceladoPor");

	// ($_POST['P'] == 40), Reabrir llamada de servicio
	if(vP.value!=40) {
		if(EstLlamada.value=='-1'){
			if(txtResol.value==''){
				res=false;
				Swal.fire({
					title: '¡Advertencia!',
					text: 'Debe ingresar la Resolución de la llamada',
					icon: 'warning'
				});
			}

			if(EstadoServicio.value=='0'){
				res=false;
				Swal.fire({
					title: '¡Advertencia!',
					text: 'Cuando está cerrando la llamada, el Estado de servicio debe ser diferente a NO EJECUTADO',
					icon: 'warning'
				});
			}
		}

		if(EstadoServicio.value=='2'){
			if (CanceladoPor.value=='' || CanceladoPor.value=='1.N/A'){
				res=false;
				Swal.fire({
					title: '¡Advertencia!',
					text: 'Debe seleccionar un valor en el campo Cancelado Por.',
					icon: 'warning'
				});
			}
		}
	}
	return res;
}

function EnviarFrm(P=33){
	var vP=document.getElementById('P');
//	vP.value=P;
	var txtComentario=document.getElementById('ComentarioLlamada');
	if(P==40){
		vP.value=P;
		txtComentario.removeAttribute("required");
		document.getElementById('DireccionLlamada').removeAttribute("required");
	}else{
		vP.value=P;
		txtComentario.setAttribute("required","required");
	}
}

function CambiarEstado(id,form,columID){
	$('.ibox-content').toggleClass('sk-loading',true);

	$.ajax({
		type: "POST",
		url: "md_frm_cambiar_estados.php",
		data:{
			id:id,
			frm: form,
			nomID: columID
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		}
	});
}

// SMM, 18/02/2022
function MostrarCostos(id_llamada){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		async: false,
		url: "md_articulos_documentos.php",
		data:{
			pre:3,
			DocEntry:id_llamada
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#TituloModal').html('Precios IVA Incluido (Entregas (+) / Devoluciones (-))');
			$('#myModal').modal("show");
		}
	});
}

// SMM, 26/07/2022
function MostrarCostos_Documentos(docnum, id_objeto, de_objeto){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		async: false,
		url: "md_articulos_documentos.php",
		data:{
			pre: 4,
			DocNum: docnum,
			IdObjeto: id_objeto
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#TituloModal').html(`Precios IVA Incluido (${de_objeto}: ${docnum})`);
			$('#myModal').modal("show");
		}
	});
}

// SMM 22/03/2022
function CopiarToFactura(adj=1,dest=1){
	var docDest="factura_venta.php";
	if(dest==2)	docDest="orden_venta.php";

	<?php if (PermitirFuncion(419)) {?>
		Swal.fire({
			title: "¿Desea cambiar de socio de negocio?",
			icon: "question",
			showCancelButton: true,
			confirmButtonText: "Si, confirmo",
			cancelButtonText: "No"
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					type: "POST",
					url: "ajx_cbo_select.php?type=42&id=" + $('#CallID').val(),
					success: function(response){
						$('#ClienteFactSN').html(response).fadeIn();
						$('#ClienteFactSN').trigger('change');
					}
				});
				$('#modalFactSN').modal("show");
			} else {
				CopiarFactura(adj, docDest);
			}
		});
	<?php } else {?>
		CopiarFactura(adj, docDest);
	<?php }?>
}

// neduga, 18/02/2022
function CopiarFactura(adj, docDest) {
	let CodClienteFactura = document.getElementById('ClienteLlamada');

	if(CodClienteFactura.value!="") {
		window.location = docDest+`?dt_FC=2&Cardcode=${btoa(CodClienteFactura.value)}&adt=${btoa(adj)}&CodFactura=${btoa(CodClienteFactura.value)}&IdLlamada=${btoa($('#CallID').val())}&DocNum=${btoa($('#Ticket').val())}`;
	} else {
		Swal.fire({
			title: '¡Advertencia!',
			text: 'Debe seleccionar un valor en el campo Cliente.',
			icon: 'warning'
		});
	}
}

// SMM, 22/06/2022
function CopiarFacturaSN(Cliente, Contacto, Sucursal, Direccion) {
	if(Cliente != "") {
		adicionales = `Cardcode=${btoa(Cliente)}&CodFactura=${btoa(Cliente)}&Contacto=${btoa(Contacto)}&Sucursal=${btoa(Sucursal)}&Direccion=${btoa(Direccion)}`;
		window.location = `factura_venta.php?dt_FC=2&${adicionales}&adt=${btoa(1)}&IdLlamada=${btoa($('#CallID').val())}&DocNum=${btoa($('#Ticket').val())}`;
	} else {
		console.log('Debe seleccionar un valor en el campo Cliente.');
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

<script>
	$(function () {
		var url = "";
		var params = [];

		$(".alkin").on("click", function(event){
			$('.ibox-content').toggleClass('sk-loading'); // Cargando...
		});

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

		let options = {
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

		$("#NombreClienteSN").easyAutocomplete(options);

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

		$("#formCambiarFactSN").on("submit", function(event) {
			event.preventDefault(); // Evitar redirección del formulario

			let Cliente = document.getElementById('ClienteFactSN').value;
			let Contacto = document.getElementById('ContactoFactSN').value;
			let Sucursal = document.getElementById('SucursalFactSN').value;
			let Direccion = document.getElementById('DireccionFactSN').value;

			CopiarFacturaSN(Cliente, Contacto, Sucursal, Direccion);
		});

		$("#ClienteFactSN").change(function() {
			let ClienteFactSN = document.getElementById('ClienteFactSN').value;

			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=2&id="+ClienteFactSN,
				success: function(response) {
					$('#ContactoFactSN').html(response).fadeIn();
					$('#ContactoFactSN').trigger('change');
				},
				error: function(error) {
					console.error("ContactoFactSN", error.responseText);
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=3&id="+ClienteFactSN,
				success: function(response) {
					console.log(response);

					$('#SucursalFactSN').html(response).fadeIn();
					$('#SucursalFactSN').trigger('change');
				},
				error: function(error) {
					console.error("SucursalFactSN", error.responseText);
				}
			});
		});

		$("#SucursalFactSN").change(function() {
			let ClienteFactSN = document.getElementById('ClienteFactSN').value;
			let SucursalFactSN = document.getElementById('SucursalFactSN').value;

			if (SucursalFactSN != -1 && SucursalFactSN != '') {
				$.ajax({
					url:"ajx_buscar_datos_json.php",
					data: {
						type: 1,
						CardCode: ClienteFactSN,
						Sucursal: SucursalFactSN
					},
					dataType:'json',
					success: function(data) {
						document.getElementById('DireccionFactSN').value=data.Direccion;
					},
					error: function(error) {
						console.error("SucursalFactSN", error.responseText);
					}
				});
			}
		});
	});

	function ConsultarDatosClienteSN(){
		let ClienteSN=document.getElementById('ClienteSN');

		if(ClienteSN.value!=""){
			self.name='opener';
			remote=open('socios_negocios.php?id='+Base64.encode(ClienteSN.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
			remote.focus();
		}
	}

	function ConsultarDatosFactSN(){
		let ClienteFactSN=document.getElementById('ClienteFactSN');

		if(ClienteFactSN.value!=""){
			self.name='opener';
			remote=open('socios_negocios.php?id='+Base64.encode(ClienteFactSN.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
			remote.focus();
		}
	}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>