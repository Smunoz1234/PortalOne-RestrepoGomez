<?php
require_once "includes/conexion.php";
PermitirAcceso(312);
//require_once("includes/conexion_hn.php");

$DocNum = (isset($_GET['DocNum']) && $_GET['DocNum'] != "") ? $_GET['DocNum'] : "";
$Placa = (isset($_GET['Placa']) && $_GET['Placa'] != "") ? $_GET['Placa'] : ""; // SMM, 09/03/2022
$Series = (isset($_GET['Series']) && $_GET['Series'] != "") ? $_GET['Series'] : "";
$SucursalCliente = (isset($_GET['SucursalCliente']) && $_GET['SucursalCliente'] != "") ? $_GET['SucursalCliente'] : "";
$Servicios = (isset($_GET['Servicios']) && $_GET['Servicios'] != "") ? $_GET['Servicios'] : "";
$Areas = (isset($_GET['Areas']) && $_GET['Areas'] != "") ? $_GET['Areas'] : "";
$Articulo = (isset($_GET['Articulo']) && $_GET['Articulo'] != "") ? $_GET['Articulo'] : "";
$TipoLlamada = (isset($_GET['TipoLlamada']) && $_GET['TipoLlamada'] != "") ? $_GET['TipoLlamada'] : "";
$Ciudad = (isset($_GET['Ciudad']) && $_GET['Ciudad'] != "") ? $_GET['Ciudad'] : "";
$FechaInicio = isset($_GET['FechaInicio']) ? $_GET['FechaInicio'] : "";
$FechaFinal = isset($_GET['FechaFinal']) ? $_GET['FechaFinal'] : "";
$Cliente = (isset($_GET['Cliente']) && $_GET['Cliente'] != "") ? $_GET['Cliente'] : "";

// SMM, 02/09/2022
$FiltrarActividades = "NULL";
if (getCookiePHP("FiltrarActividades") == "true") {
    $FiltrarActividades = "1";
}

$ParamOT = array(
    "2",
    "'" . $_SESSION['CodUser'] . "'",
    "'" . $_GET['idEvento'] . "'",
    "''", //Sede
    "'" . $Cliente . "'",
    "'" . utf8_encode(base64_decode($SucursalCliente)) . "'",
    "'" . FormatoFecha($FechaInicio) . "'",
    "'" . FormatoFecha($FechaFinal) . "'",
    $FiltrarActividades, // SMM, 02/09/2022
    "'" . $DocNum . "'",
    "'" . $Series . "'",
    "'" . $Servicios . "'",
    "'" . $Areas . "'",
    "'" . $Articulo . "'",
    "'" . $TipoLlamada . "'",
    "'" . $Ciudad . "'",
    "''",
    "''",
    "'" . $Placa . "'", // SMM, 09/03/2022
);

$SQL_OT = EjecutarSP("sp_ConsultarDatosCalendarioRutasOT", $ParamOT);
$Num_OT = sqlsrv_num_rows($SQL_OT);

// Consultar DuracionActividad desde los Parámetros Asistentes. SMM, 17/06/2023
$Cons_DuracionActividad = "SELECT dbo.FN_NDG_PARAMETRO_ASISTENTE('DuracionActividad', 1) AS DuracionActividad";
$SQL_DuracionActividad = sqlsrv_query($conexion, $Cons_DuracionActividad);
$row_DuracionActividad = sqlsrv_fetch_array($SQL_DuracionActividad);
$DuracionActividad = $row_DuracionActividad["DuracionActividad"] ?? 120;
?>

<?php while ($row_OT = sqlsrv_fetch_array($SQL_OT)) {?>
    <div class="card card-body mt-lg-3 bg-light border-primary <?php if ($row_OT['Validacion'] == "OK") {echo "item-drag";}?>" style="min-height: 14rem;" data-title="<?php if(PermitirFuncion(330)) { echo $row_OT['Etiqueta_Automotriz'] ?? ""; } else { echo $row_OT['Etiqueta'] ?? ""; } ?>" data-docnum="<?php echo $row_OT['DocNum']; ?>" data-estado="<?php echo $row_OT['IdEstadoLlamada']; ?>" data-info="<?php echo $row_OT['DeTipoLlamada'] ?? ""; ?>" data-validacion="<?php echo $row_OT['Validacion']; ?>"
	data-tiempo="<?php echo (isset($row_OT['CDU_TiempoTarea']) && ($row_OT['CDU_TiempoTarea'] != 0)) ? $row_OT['CDU_TiempoTarea'] : $DuracionActividad; ?>" data-comentario="<?php echo $row_OT['ComentarioLlamada'] ?? ""; ?>">

		<h5 class="card-title"><a href="llamada_servicio.php?id=<?php echo base64_encode($row_OT['ID_LlamadaServicio']); ?>&tl=1" target="_blank" title="Consultar Llamada de servicio" class="btn-xs btn-success fas fa-search"></a> <?php echo $row_OT['DocNum']; ?></h5>
		<h6 class="card-subtitle mb-2 text-muted"><?php echo $row_OT['DeTipoLlamada']; ?></h6>
		<p class="card-text mb-0 small text-primary"><?php echo $row_OT['DeArticuloLlamada']; ?></p>
		<p class="card-text mb-0 small"><strong><?php echo $row_OT['NombreClienteLlamada']; ?></strong></p>

        <?php if(isset($row_OT["SerialArticuloLlamada"]) && ($row_OT["SerialArticuloLlamada"] != "")) { ?>
            <p class="card-text mb-0 small"><span class="font-weight-bold">Serial Interno:</span> <?php echo $row_OT['SerialArticuloLlamada']; ?></p>
        <?php } ?>

        <?php if(isset($row_OT["CDU_Marca"]) && ($row_OT["CDU_Marca"] != "")) { ?>
            <p class="card-text mb-0 small"><span class="font-weight-bold">Marca:</span> <?php echo $row_OT['CDU_Marca']; ?></p>
        <?php } ?>

        <?php if(isset($row_OT["CDU_Linea"]) && ($row_OT["CDU_Linea"] != "")) { ?>
            <p class="card-text mb-0 small"><span class="font-weight-bold">Linea:</span> <?php echo $row_OT['CDU_Linea']; ?></p>
        <?php } ?>

        <?php if(isset($row_OT["NombreSucursal"]) && ($row_OT["NombreSucursal"] != "")) { ?>
            <p class="card-text mb-0 small"><span class="font-weight-bold">Sucursal:</span> <?php echo $row_OT['NombreSucursal']; ?></p>
        <?php } ?>

        <?php if(isset($row_OT["CiudadLlamada"]) && ($row_OT["CiudadLlamada"] != "")) { ?>
            <p class="card-text mb-0 small"><span class="font-weight-bold">Ciudad:</span> <?php echo $row_OT['CiudadLlamada']; ?></p>
        <?php } ?>

        <?php if(isset($row_OT["FechaLlamada"]) && ($row_OT["FechaLlamada"] != "")) { ?>
            <p class="card-text mb-0 small"><span class="font-weight-bold">Fecha:</span> <?php echo $row_OT['FechaLlamada']->format('Y-m-d'); ?></p>
        <?php } ?>

        <?php if(isset($row_OT["Servicios"]) && ($row_OT["Servicios"] != "")) { ?>
            <p class="card-text mb-0 small"><span class="font-weight-bold">Servicios:</span> <?php echo $row_OT['Servicios']; ?></p>
        <?php } ?>

        <?php if(isset($row_OT["Areas"]) && ($row_OT["Areas"] != "")) { ?>
            <p class="card-text mb-0 small"><span class="font-weight-bold">Áreas:</span> <?php echo substr($row_OT['Areas'], 0, 150); ?></p>
        <?php } ?>

        <?php if(isset($row_OT["MetodoAplicaLlamadas"]) && ($row_OT["MetodoAplicaLlamadas"] != "")) { ?>
            <p class="card-text mb-0 small"><span class="font-weight-bold">Método Aplicación:</span> <?php echo substr($row_OT['MetodoAplicaLlamadas'], 0, 150); ?></p>
        <?php } ?>

        <?php if(isset($row_OT["Validacion"]) && ($row_OT["Validacion"] != "")) { ?>
            <p class="card-text mb-0 small"><span class="font-weight-bold">Validación:</span> <span class="<?php if ($row_OT['Validacion'] != "OK") {echo "text-danger";} else {echo "text-success";}?>"><?php echo $row_OT['Validacion']; ?></span></p>
        <?php } ?>
	</div>
<?php }?>

<script>
function valor(){
	$("#CantOT").html("<?php echo $Num_OT; ?>")
}

valor();
</script>