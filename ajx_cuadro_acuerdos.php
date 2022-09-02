<?php 
if(isset($_GET['type'])&&($_GET['type']!="")){
	require_once("includes/conexion.php");
	
	if($_GET['type']==1){
		$Fecha=$_GET['fecha'];
		$Cuotas=$_GET['cuotas'];
		$Valor=$_GET['valor'];

		if($Fecha==""||$Cuotas==""||$Valor==""){
			exit();
		}

		$Array=CalcularCuotasAcuerdo($Fecha,$Cuotas,$Valor);

		//echo count($Array);
		//exit();

		//Armar tabla
		echo "
		<table class='table table-striped table-bordered'>
			<thead>
				<tr>
					<th>Cuota</th>
					<th>Fecha pago</th>
					<th>%</th>
					<th>Valor</th>
				</tr>
			</thead>
			<tbody>";

		$j=1;
		for($i=0;$i<count($Array);$i++){
			echo "<tr>";
			echo "<td>".$Array[$j][0]."</td>";
			echo "<td>".$Array[$j][1]."</td>";
			echo "<td>".number_format($Array[$j][2],0)."</td>";
			echo "<td>"."$".number_format($Array[$j][3],2)."</td>";
			echo "</tr>";
			$j++;
		}	
		echo "</tbody>
		</table>";
	}
	elseif($_GET['type']==2){
		$Param=array("'".base64_decode($_GET['clt'])."'",$_GET['int'],$_GET['factvenc']);
		$SQL_FactPend=EjecutarSP('sp_CalcularIntMoraFactVencida',$Param);
		
		echo "<table class='table table-striped table-bordered'>
				<thead>
				<tr>
					<th>#</th>
					<th>Factura</th>
					<th>Fecha venc.</th>
					<th>Dias vencidos</th>
					<th>Saldo total</th>															
					<th>Valor intereses mora</th>
					<th>Total a pagar</th>
				</tr>
				</thead>
				<tbody>";
		$i=1;
		while($row_FactPend=sqlsrv_fetch_array($SQL_FactPend)){
			echo "<tr>";
			echo "<td>".$i."</td>";
			echo "<td>".$row_FactPend['NoDocumento']."</td>";
			echo "<td>".$row_FactPend['FechaVencimiento']->format('Y-m-d')."</td>";
			echo "<td>".number_format($row_FactPend['DiasVencidos'],0)."</td>";
			echo "<td>"."$".number_format($row_FactPend['SaldoDocumento'],0)."</td>";
			echo "<td>"."$".number_format($row_FactPend['InteresesMora'],0)."</td>";
			echo "<td>"."$".number_format($row_FactPend['TotalPagar'],0)."</td>";
			echo "</tr>";
			$i++;
		}
		echo "</tbody>
		</table>";
		
	}else{
		exit();
	}
	
	
} ?>