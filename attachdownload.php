<?php
if (isset($_REQUEST['file']) && $_REQUEST['file'] != "") {
    require_once "includes/conexion.php";

    $file = base64_decode($_REQUEST['file']);

    if (!isset($_GET['line']) || $_GET['line'] == "") {
        $line = 1;
    } else {
        $line = base64_decode($_GET['line']);
    }

    $NombreArchivo = "";
    $size = 0;
    $ZipMode = 0;
    $type = 1;

    if (isset($_GET['type'])) {
        $type = $_GET['type'];
    }

    if (isset($_REQUEST['zip']) && ($_REQUEST['zip']) == base64_encode('1')) {
        $ZipMode = 1;
    }

    #Ruta local de archivos de SAP
    $carp_archivos = ObtenerVariable("RutaArchivos");
    $RutaLocal = $_SESSION['BD'] . "/" . $carp_archivos . "/InformesSAP/";

    //Validar si el anexo es de SAP o interno de PortalOne.
    //1 > (default) SAP.
    //2 > PortalOne
    //3 > Archivos de contratos DIALNET

    #OBTENER RUTA DE DESCARGAR DEL ARCHIVO DEL SERVIDOR
    if (SO == "Linux") {

        /******* LINUX *******/
        //Credenciales
        $Dominio = DOMINIO_WIN;
        $User = USER_WIN;
        $Pass = PASS_WIN;
        $Path = PATH_WIN;

        $SrvRuta = "smb://" . $Dominio . $User . $Pass . $Path;

    } else {

        /******* WINDOWS *******/
        $SrvRuta = $RutaLocal;

    }

    if ($type == 1) {
        $RutaAttachSAP = ObtenerDirAttach();

        if ($ZipMode == 1) { //Comprimir los archivos descargados

            //Leer los archivos, los cuales se pasan por JSON
            $json = base64_decode($_REQUEST['file']);
            $archivos = json_decode($json, true);
            /*print_r($archivos[0]);
            exit();
            foreach ($archivos as $dato) {
            echo '<pre>';
            print_r($dato);
            echo '</pre>';
            }
            exit();*/
            //$Facts=explode("[*]",$_GET['DocKey']);//Lista de los números de las facturas.
            $Files = array(); //Donde se almacenan los archivos generados, los cuales se van a comprimir.

            $Count = count($archivos);
            $i = 0;
            $j = 0; //indice interno del array Files, por si algun registro viene sin nombre no lo inserte
            while ($i < $Count) {

                $SQL = Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos', 'NombreArchivo', "AbsEntry='" . $archivos[$i]['AbsEntry'] . "' AND Line='" . $archivos[$i]['LineNum'] . "'");
                $row = sqlsrv_fetch_array($SQL);

                if (isset($row['NombreArchivo']) && ($row['NombreArchivo'] != "")) {
                    $Files[$j] = $row['NombreArchivo'];
                    //Mover los archivos a la ruta local
                    copy($RutaAttachSAP[0] . $Files[$j], $RutaLocal . $Files[$j]);
                    $j++;
                }

                $i++;
            }

            //Crear archivo ZIP e insertar los archivos
            $zip = new ZipArchive();
            $zipName = date('YmdHi') . "_" . $_SESSION['CodUser'] . ".zip";
            $filezip = $RutaLocal . $zipName;
            //echo $filezip;
            //exit();

            if ($zip->open($filezip, ZIPARCHIVE::CREATE) === true) {
                $Count = count($Files);
                $i = 0;
                //$zip->close();
                while ($i < $Count) {
                    $zip->addFile($RutaLocal . $Files[$i], $Files[$i]);
                    //$zip->addFile($RutaAttachSAP[0].$Files[$i],$Files[$i]);
                    //echo "Se agregó: ".$SrvRuta.$Files[$i]."\n";
                    $i++;
                }
                //exit();
                $zip->close();
                //$filename=$zipName;
            } else {
                exit("No se puede abrir el archivo $filezip\n");
            }

        } else {

            $SQL = Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos', 'NombreArchivo', "AbsEntry='" . $file . "' AND Line='" . $line . "'");
            $row = sqlsrv_fetch_array($SQL);
        }

        //BUSCAR ARCHIVO PARA DESCARGAR
        if ($ZipMode == 1) {
            $filename = $filezip;
            $NombreArchivo = $zipName;
        } else {
            $filename = $RutaAttachSAP[0] . $row['NombreArchivo'];
            $NombreArchivo = $row['NombreArchivo'];
        }

        $size = filesize($filename);

    } elseif ($type == 2) { //PortalOne

        $carp_archivos = ObtenerVariable("RutaArchivos");
        $carp_anexos = "formularios";
        $dir_attach = $_SESSION['BD'] . "/" . $carp_archivos . "/" . $carp_anexos . "/";

        $SQL = Seleccionar('uvw_tbl_DocumentosSAP_Anexos', 'NombreArchivo', "ID_Anexo='" . $file . "'");
        $row = sqlsrv_fetch_array($SQL);

        $filename = $dir_attach . $row['NombreArchivo'];

        $NombreArchivo = $row['NombreArchivo'];
        $size = filesize($filename);

    } elseif ($type == 3) { //Contratos de DIALNET

        $RutaAttachSAP = ObtenerDirAttach();

        if ($ZipMode == 1) { //Comprimir los archivos descargados

            //Leer los archivos, los cuales se pasan por JSON
            $json = base64_decode($_GET['file']);
            $archivos = json_decode($json, true);
            /*print_r($archivos[0]);
            exit();
            foreach ($archivos as $dato) {
            echo '<pre>';
            print_r($dato);
            echo '</pre>';
            }
            exit();*/
            //$Facts=explode("[*]",$_GET['DocKey']);//Lista de los números de las facturas.
            $Files = array(); //Donde se almacenan los archivos generados, los cuales se van a comprimir.

            $Count = count($archivos);
            $i = 0;

            //Crear archivo ZIP e insertar los archivos
            $zip = new ZipArchive();
            $zipName = date('YmdHi') . "_" . $_SESSION['CodUser'] . ".zip";
            $filezip = $RutaLocal . $zipName;

            if ($zip->open($filezip, ZIPARCHIVE::CREATE) === true) {

                while ($i < $Count) {
                    $ParamAnx = array(
                        "'" . $archivos[$i]['DocCont'] . "'",
                    );
                    $SQL = EjecutarSP('sp_InformeSNProyecto_DescargaAnexos', $ParamAnx, 0, 2);

                    //$SQL=Seleccionar('uvw_Sap_tbl_Contratos','NombreArchivo',"AbsEntry='".$archivos[$i]['AbsEntry']."' AND Line='".$archivos[$i]['LineNum']."'");

                    while ($row = sql_fetch_array($SQL, 2)) {

                        //Copiar los archivos a la ruta local para comprimirlos localmente
                        if (!copy($RutaAttachSAP[0] . $row['NombreArchivo'], $RutaLocal . $row['NombreArchivo'])) {
                            exit('No se pudo copiar el archivo ' . $row['NombreArchivo']);
                        }

                        //echo $RutaAttachSAP[0].$row['NombreArchivo']." => ".$archivos[$i]['LicTradNum']."/".$row['DeCategoria']."/".$row['NombreArchivo']."<br>";
                        $zip->addFile($RutaLocal . $row['NombreArchivo'], $archivos[$i]['LicTradNum'] . "/" . utf8_encode($row['DeCategoria']) . "/" . $row['NombreArchivo']);

                    }
                    $i++;
                }

                $zip->close();

            } else {
                exit("No se puede abrir el archivo $filezip\n");
            }
            //exit("Fin del proceso");

        }

        //BUSCAR ARCHIVO PARA DESCARGAR
        if ($ZipMode == 1) {
            $filename = $filezip;
            $NombreArchivo = $zipName;
        } else {
            $filename = $RutaAttachSAP[0] . $row['NombreArchivo'];
            $NombreArchivo = $row['NombreArchivo'];
        }

        $size = filesize($filename);

    }

    header("Content-Transfer-Encoding: binary");
    header('Content-type: application/pdf', true);
    header("Content-Type: application/force-download");
    header('Content-Disposition: attachment; filename="' . $NombreArchivo . '"');
    header("Content-Length: $size");
    readfile($filename);

    //echo $filename;
}
