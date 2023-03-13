<?php
require_once "includes/conexion.php";

$preCostos = (isset($_POST['pre'])) ? $_POST['pre'] : 0;

//validar el uso

if ($preCostos == 0) { //para informe de analisis de ventas

    $ParamCons = array(
        "'" . $_POST['DocType'] . "'",
        "'" . $_POST['DocEntry'] . "'",
        $_POST['Todos'],
    );
    $SQL = EjecutarSP('usp_InformeVentas_DetalleArticulos', $ParamCons);

} elseif ($preCostos == 1) { //Previsualizar costos en asistente de facturacion

    $ParamCons = array(
        "'" . $_POST['CardCode'] . "'",
        "'" . $_SESSION['CodUser'] . "'",
    );
    $SQL = EjecutarSP('sp_ConsultarFacturacionOT_Costos', $ParamCons);

} elseif ($preCostos == 2) { //Previsualizar costos de una llamada de servicio

    $ParamCons = array(
        "'" . $_POST['DocEntry'] . "'",
    );
    $SQL = EjecutarSP('sp_ConsultarCostosOT', $ParamCons);
} elseif ($preCostos == 3) { //Previsualizar los precios de ventas de una llamada de servicio

    $ParamCons = array(
        "'" . $_POST['DocEntry'] . "'",
    );
    $SQL = EjecutarSP('sp_ConsultarEntregasVentasOT', $ParamCons);
}

// Previsualizar los precios de los documentos de marketing por tipo.
elseif ($preCostos == 4) {
    $ParamCons = array(
        "'" . $_POST['DocNum'] . "'",
        "'" . $_POST['IdObjeto'] . "'",
    );
    $SQL = EjecutarSP('sp_ConsultarDocumentosPreciosOT', $ParamCons);
}

?>
<div class="form-group">
	<div class="ibox-content">
		<div class="table-responsive">
		<table class="table table-bordered table-hover" >
			<thead>
			<tr>
				<th>#</th>
				<?php if ($preCostos == 3) {?>
					<th>Código cliente</th>
					<th>Nombre de cliente</th>
				<?php }?>
				<th>Código artículo</th>
				<th>Nombre de artículo</th>
				<th>Unidad de medida</th>
				<th>Cantidad</th>
				<th>Precio</th>
				<th>Total</th>
				<th>Clase de artículo</th>
				<th>Grupo de artículo</th>
			</tr>
			</thead>
			<tbody>
			<?php
$i = 1;
$SubGrupo = "";
$SubTotal = 0;
$Total = 0;
$sw_Cambio = 0;

while ($row = sqlsrv_fetch_array($SQL)) {
    if ($i == 1) {
        $SubGrupo = $row['DE_ItemType'];
    }
    ?>

				<?php
if ((($SubGrupo != $row['DE_ItemType']) && $i > 1) || ($i == 1)) {
        if ($i > 1) {
            ?>

				<tr>
					<td colspan="<?php echo ($preCostos == 3) ? '8' : '6'; ?>" class="text-success font-bold"><span class="pull-right">SubTotal <?php echo $SubGrupo; ?></span></td>
					<td class="text-success font-bold"><?php echo "$" . number_format($SubTotal, 2); ?></td>
					<td colspan="2" class="text-success font-bold">&nbsp;</td>
				</tr>

				<?php
}
        $SubGrupo = $row['DE_ItemType'];
        $SubTotal = 0;

        ?>
				<tr>
					<td colspan="<?php echo ($preCostos == 3) ? '11' : '9'; ?>" class="bg-muted text-success font-bold"><?php echo $row['DE_ItemType']; ?></td>
				</tr>
				<?php }?>

				 <tr>
					<td><?php echo $i; ?></td>
					<?php if ($preCostos == 3) {?>
						<td><a href="socios_negocios.php?id=<?php echo base64_encode($row['IdCliente']); ?>&tl=1" target="_blank"><?php echo $row['IdCliente']; ?></a></td>
						<td><?php echo $row['NombreCliente']; ?></td>
					<?php }?>
					<td><a href="articulos.php?id=<?php echo base64_encode($row['ItemCode']); ?>&tl=1" target="_blank"><?php echo $row['ItemCode']; ?></a></td>
					<td><?php echo $row['ItemName']; ?></td>
					<td><?php echo $row['Unidad']; ?></td>
					<td><?php echo number_format($row['Cantidad'], 2); ?></td>
					<td><?php echo "$" . number_format($row['Precio'], 2); ?></td>
					<td class="<?php if ($row['LineTotal'] < 0) {echo "text-danger";} else {echo "text-navy";}?>"><?php echo "$" . number_format($row['LineTotal'], 2); ?></td>
					<td><?php echo $row['DE_ItemType']; ?></td>
					<td><?php echo $row['ItmsGrpNam']; ?></td>
				</tr>
			<?php $i++;
    $SubTotal += $row['LineTotal'];
    $Total += $row['LineTotal'];
}
?>
				<tr>
					<td colspan="<?php echo ($preCostos == 3) ? '8' : '6'; ?>" class="text-success font-bold"><span class="pull-right">SubTotal <?php echo $SubGrupo; ?></span></td>
					<td class="text-success font-bold"><?php echo "$" . number_format($SubTotal, 2); ?></td>
					<td colspan="2" class="text-success font-bold">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="<?php echo ($preCostos == 3) ? '8' : '6'; ?>" class="text-danger font-bold"><span class="pull-right">TOTAL</span></td>
					<td class="text-danger font-bold"><?php echo "$" . number_format($Total, 2); ?></td>
					<td colspan="2" class="text-danger font-bold">&nbsp;</td>
				</tr>
			</tbody>
		</table>
  		</div>
	</div>
</div>