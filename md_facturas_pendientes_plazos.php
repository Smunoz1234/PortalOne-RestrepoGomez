<?php
require_once "includes/conexion.php";

$SQL = Seleccionar('uvw_Sap_tbl_FacturasPendientes_Plazos', '*', "NoDocumento='" . $_POST['NoDocumento'] . "'");
?>

<div class="form-group">
	<div class="ibox-content">
		<div class="table-responsive">
		<table class="table table-bordered table-hover" >
			<thead>
			<tr>
				<th>#</th>
				<th>Código cliente</th>
				<th>Nombre cliente</th>
				<th>Fecha de vencimiento</th>
				<th>%</th>
				<th>Total</th>
			</tr>
			</thead>
			<tbody>
			<?php $i = 1;?>
			<?php $porcentaje = 0;?>
			<?php $total = 0;?>
			<?php while ($row = sqlsrv_fetch_array($SQL)) {?>
				<tr>
					<td><?php echo $i; ?></td>
					<td><?php echo $row['ID_CodigoCliente']; ?></td>
					<td><?php echo $row['NombreCliente']; ?></td>
					<td><?php echo $row['FechaVencimiento']->format('Y-m-d'); ?></td>
					<td><?php echo number_format($row['Porcentaje'], 2); ?></td>
					<td><?php echo "$" . number_format($row['Total'], 2); ?></td>

					<?php $porcentaje += $row['Porcentaje'];?>
					<?php $total += $row['Total'];?>
				</tr>
			<?php $i++;}?>

			<tr>
				<td colspan="4" class="text-danger font-bold"><span class="pull-right">TOTAL</span></td>
				<td class="text-danger font-bold"><?php echo number_format($porcentaje, 2); ?></td>
				<td class="text-danger font-bold"><?php echo "$" . number_format($total, 2); ?></td>
			</tr>
			</tbody>
		</table>
  		</div>
	</div>
</div>