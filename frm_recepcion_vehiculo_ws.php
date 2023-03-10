<?php require_once "includes/conexion.php";
$Cabecera = $_POST;
$return = base64_decode($Cabecera["return"]);

unset($Cabecera["return"]);
unset($Cabecera["SucursalCliente"]);
unset($Cabecera["ContactoCliente"]);
unset($Cabecera["FechaCreacion"]);
unset($Cabecera["HoraCreacion"]);
unset($Cabecera["ResponsableCliente"]);

$Cabecera["app"] = "PortalOne"; // Por defecto
$Cabecera["estado"] = "O"; // Por defecto

$Cabecera["id_consecutivo_direccion"] = 0; // NumeroLinea (Sap_Clientes_Sucursales)
$Cabecera["id_direccion_destino"] = $_POST["SucursalCliente"];

$Cabecera["hora_ingreso"] = FormatoFechaToSAP($Cabecera['fecha_ingreso'], $Cabecera['hora_ingreso']);
$Cabecera["fecha_ingreso"] = FormatoFechaToSAP($Cabecera['fecha_ingreso']);

$Cabecera["hora_aprox_entrega"] = FormatoFechaToSAP($Cabecera['fecha_aprox_entrega'], $Cabecera['hora_aprox_entrega']);
$Cabecera["fecha_aprox_entrega"] = FormatoFechaToSAP($Cabecera['fecha_aprox_entrega']);

$Cabecera["km_actual"] = intval($Cabecera["km_actual"] ?? 0);

unset($Cabecera["hora_autoriza_campana"]); // SMM, 22/02/2022
unset($Cabecera["fecha_autoriza_campana"]); // SMM, 22/02/2022
if (isset($_POST['fecha_autoriza_campana']) && $_POST['fecha_autoriza_campana'] != "") {
    $Cabecera["fecha_hora_autoriza_campana"] = FormatoFechaToSAP($_POST['fecha_autoriza_campana'], $_POST['hora_autoriza_campana']);
}

// SMM, 22/10/2022
if ($Cabecera["profundidad_llantas_18"] == "") {unset($Cabecera["profundidad_llantas_18"]);}
if ($Cabecera["profundidad_llantas_19"] == "") {unset($Cabecera["profundidad_llantas_19"]);}
if ($Cabecera["profundidad_llantas_20"] == "") {unset($Cabecera["profundidad_llantas_20"]);}
if ($Cabecera["profundidad_llantas_21"] == "") {unset($Cabecera["profundidad_llantas_21"]);}
// Hasta aquí, 22/10/2022

for ($i = 1; $i <= 25; $i++) {
    if (isset($Cabecera["id_pregunta_$i"])) {
        $Cabecera["id_pregunta_$i"] = intval($Cabecera["id_pregunta_$i"]);
    }
}

$Cabecera["id_empleado_tecnico"] = intval($_SESSION['CodigoSAP']);
$Cabecera["empleado_tecnico"] = $_SESSION['NombreEmpleado'];

$Cabecera["id_usuario_creacion"] = $_SESSION['CodUser'];
$Cabecera["usuario_creacion"] = strtolower($_SESSION['User']); // 22/02/2022

if (isset($Cabecera["id_llamada_servicio"])) {
    $Cabecera["docentry_llamada_servicio"] = intval($Cabecera["id_llamada_servicio"]);

    $SQL_OT = Seleccionar("uvw_Sap_tbl_LlamadasServicios", "DocNum", "ID_LlamadaServicio=" . $Cabecera["docentry_llamada_servicio"]);
    $row_OT = sqlsrv_fetch_array($SQL_OT);
    $Cabecera["id_llamada_servicio"] = intval($row_OT['DocNum'] ?? 0);
} else {
    $Cabecera["id_llamada_servicio"] = 0;
    $Cabecera["docentry_llamada_servicio"] = 0;
}

$dir_name = "recepcion_vehiculos";
$dir_log = CrearObtenerDirRuta(ObtenerVariable("RutaAnexosPortalOne") . "/" . $_SESSION['User'] . "/" . $dir_name . "/");
$dir_main = CrearObtenerDirRuta(ObtenerVariable("RutaAnexosRecepcionVehiculo"));

// Inicio, agregar anexos al JSON.
if (isset($Cabecera["Anexo0"])) {
    $a = 0;
    $Cabecera["anexos"] = array();
    while (isset($Cabecera["Anexo$a"])) {
        $ext = substr($Cabecera["Anexo$a"], -3);
        $hash = substr(uniqid(rand()), 0, 8);
        $nombre_anexo = "Anexo_$hash.$ext";

        array_push($Cabecera["anexos"], [
            "anexo" => $nombre_anexo,
        ]);

        // Inicio, copiar anexos a la ruta log y main.
        $source = CrearObtenerDirRuta(ObtenerVariable("CarpetaTmp") . "/recepcion_vehiculos/" . $_SESSION['CodUser'] . "/");
        $source .= NormalizarNombreArchivo(str_replace(" ", "_", $Cabecera["Anexo$a"])); // SMM, 28/09/2022

        $dest = $dir_log . $nombre_anexo;
        copy($source, $dest);
        $dest = $dir_main . $nombre_anexo;
        copy($source, $dest);
        // Fin, copiar anexos a la ruta log y main.

        $a++;
    }
}
// Fin, agregar anexos al JSON.

// Inicio, copiar firma a la ruta log y main, y agregarlas al JSON.
if (isset($Cabecera["SigCliente"])) {
    $Cabecera["firma_responsable_cliente"] = base64_decode($Cabecera["SigCliente"]) ?? "";
    unset($Cabecera["SigCliente"]);

    if ($Cabecera["firma_responsable_cliente"] != "") {
        $source = CrearObtenerDirTempFirma() . $Cabecera["firma_responsable_cliente"];

        $dest = $dir_log . $Cabecera["firma_responsable_cliente"];
        copy($source, $dest);

        $dest = $dir_main . $Cabecera["firma_responsable_cliente"];
        copy($source, $dest);
    }
}
// Fin, copiar firma a la ruta log y main, y agregarlas al JSON.

// Inicio, copiar fotografias a la ruta log y main, y agregarlas al JSON.
if (PermitirFuncion(1708)) {
    $Cabecera["fotografias"] = [
        array(
            "id_recepcion_vehiculo" => 0, // Por defecto
            "id_recepcion_fotografia" => 0, // Por defecto
            "anexo_frente" => $Cabecera["Img1"] ?? "",
            "anexo_lateral_izquierdo" => $Cabecera["Img2"] ?? "",
            "anexo_lateral_derecho" => $Cabecera["Img3"] ?? "",
            "anexo_trasero" => $Cabecera["Img4"] ?? "",
            "anexo_capot" => $Cabecera["Img5"] ?? "",
        ),
    ];
} // SMM, 14/06/2022

$route = CrearObtenerDirRuta(ObtenerVariable("CarpetaTmp") . "/recepcion_vehiculos/" . $_SESSION['CodUser'] . "/");
if (isset($Cabecera["Img1"])) {
    $source = $route . $Cabecera["Img1"];

    $dest = $dir_log . $Cabecera["Img1"];
    copy($source, $dest);

    $dest = $dir_main . $Cabecera["Img1"];
    copy($source, $dest);

    unset($Cabecera["Img1"]);
}

if (isset($Cabecera["Img2"])) {
    $source = $route . $Cabecera["Img2"];

    $dest = $dir_log . $Cabecera["Img2"];
    copy($source, $dest);

    $dest = $dir_main . $Cabecera["Img2"];
    copy($source, $dest);

    unset($Cabecera["Img2"]);
}

if (isset($Cabecera["Img3"])) {
    $source = $route . $Cabecera["Img3"];

    $dest = $dir_log . $Cabecera["Img3"];
    copy($source, $dest);

    $dest = $dir_main . $Cabecera["Img3"];
    copy($source, $dest);

    unset($Cabecera["Img3"]);
}

if (isset($Cabecera["Img4"])) {
    $source = $route . $Cabecera["Img4"];

    $dest = $dir_log . $Cabecera["Img4"];
    copy($source, $dest);

    $dest = $dir_main . $Cabecera["Img4"];
    copy($source, $dest);

    unset($Cabecera["Img4"]);
}

if (isset($Cabecera["Img5"])) {
    $source = $route . $Cabecera["Img5"];

    $dest = $dir_log . $Cabecera["Img5"];
    copy($source, $dest);

    $dest = $dir_main . $Cabecera["Img5"];
    copy($source, $dest);

    unset($Cabecera["Img5"]);
}
// Fin, Copiar fotografias a la ruta log y main.

try {
    $Metodo = "RecepcionVehiculos";
    $Resultado = EnviarWebServiceSAP($Metodo, $Cabecera, true, true);

    if ($Resultado->Success == 0) {
        // $sw_error = 1;
        $msg_error = $Resultado->Mensaje;
        $msg = array(
            "title" => "¡Ha ocurrido un error!",
            "text" => "$msg_error",
            "icon" => "warning",
        );

        echo json_encode($msg);
    } else {
        $msg = array(
            "title" => "¡Listo!",
            "text" => "La recepción de vehículo ha sido creada exitosamente.",
            "icon" => "success",
            "return" => $return,
        );

        echo json_encode($msg);
    }
} catch (Exception $e) {
    echo 'Excepcion capturada: ', $e->getMessage(), "\n";
}

sqlsrv_close($conexion);
