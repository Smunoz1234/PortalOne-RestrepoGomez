<?php
if (isset($_GET['docentry']) && ($_GET['docentry'] != "")) {
    require_once "includes/conexion.php";

    $edit = $_GET['edit'];
    $objtype = $_GET['objtype'];
    $tipotrans = 1; //1: Sacando seriales. 2: Ingresando seriales
    if (isset($_GET['tipotrans'])) {
        $tipotrans = $_GET['tipotrans'];
    }

    if ($edit == 1) { //Creando documento
        //Consultar los articulos que tienen seriales en este documento
        if ($objtype == 15) { //Entrega de ventas
            $SQL_Items = Seleccionar("uvw_tbl_EntregaVentaDetalleCarrito", "*", "Usuario='" . $_GET['usuario'] . "' and CardCode='" . $_GET['cardcode'] . "' and WhsCode='" . $_GET['whscode'] . "' and ManSerNum='Y'");
        } elseif ($objtype == 16) { //Devolucion de ventas
            $SQL_Items = Seleccionar("uvw_tbl_DevolucionVentaDetalleCarrito", "*", "Usuario='" . $_GET['usuario'] . "' and CardCode='" . $_GET['cardcode'] . "' and WhsCode='" . $_GET['whscode'] . "' and ManSerNum='Y'");
        } elseif ($objtype == 67) { //Traslado de inventario
            $SQL_Items = Seleccionar("uvw_tbl_TrasladoInventarioDetalleCarrito", "*", "Usuario='" . $_GET['usuario'] . "' and CardCode='" . $_GET['cardcode'] . "' and WhsCode='" . $_GET['whscode'] . "' and ManSerNum='Y'");
        }

    } else { //Consultando documento
        $IdDocEntry = base64_decode($_GET['docentry']);
        $IdEvento = base64_decode($_GET['evento']);

        //Consultar los articulos que tienen lotes en este documento
        if ($objtype == 15) { //Entrega de ventas
            $SQL_Items = Seleccionar("uvw_tbl_EntregaVentaDetalle", "*", "ID_EntregaVenta='" . base64_decode($_GET['id']) . "' and IdEvento='" . $IdEvento . "' and ManSerNum='Y'");
        } elseif ($objtype == 16) { //Devolucion de ventas
            $SQL_Items = Seleccionar("uvw_tbl_DevolucionVentaDetalle", "*", "ID_DevolucionVenta='" . base64_decode($_GET['id']) . "' and IdEvento='" . $IdEvento . "' and ManSerNum='Y'");
        } elseif ($objtype == 67) { //Traslado de inventario
            $SQL_Items = Seleccionar("uvw_tbl_TrasladoInventarioDetalle", "*", "ID_TrasladoInv='" . base64_decode($_GET['id']) . "' and IdEvento='" . $IdEvento . "' and ManSerNum='Y'");

        }

    }

    ?>
<!doctype html>
<html>
<head>
<?php include_once "includes/cabecera.php";?>
<title>Número de serie | <?php echo NOMBRE_PORTAL; ?></title>
<style>
	/*.ibox-content{
		padding: 0px !important;
	}*/
	body{
		background-color: #ffffff;
	}
	.form-control{
		width: auto;
		height: 28px;
	}
	/*.table > tbody > tr > td{
		padding: 1px !important;
		vertical-align: middle;
	}*/
</style>
<script>
function BuscarSerial(item,almacen,numlinea,itemname,und, cant){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		<?php if ($edit == 1) {?>
		url: "ajx_seriales_articulos.php?id="+item+"&cardcode=<?php echo $_GET['cardcode']; ?>&whscode=<?php echo $_GET['whscode']; ?>&tipotrans=<?php echo $tipotrans; ?>&usuario=<?php echo $_GET['usuario']; ?>&edit=<?php echo $edit; ?>&objtype=<?php echo $objtype; ?>&linenum="+numlinea+"&itemname="+itemname+"&und="+und+"&cant="+cant,
		<?php } else {?>
		url: "ajx_seriales_articulos.php?id="+item+"&linenum="+numlinea+"&itemname="+itemname+"&docentry=<?php echo $IdDocEntry; ?>&idevento=<?php echo $IdEvento; ?>&edit=<?php echo $edit; ?>&objtype=<?php echo $objtype; ?>",
		<?php }?>
		success: function(response){
			if(response!=""){
				$('#SeriesItem').html(response).fadeIn();
				$('.ibox-content').toggleClass('sk-loading',false);
			}
		}
	});
}
</script>
</head>

<body>
	<div class="ibox-content">
		<?php include "includes/spinner.php";?>
		<div class="row">
			<div class="col-lg-12">
				<form action="popup_agregar_area.php" method="post" class="form-horizontal" id="FrmAgregar">
					<div class="form-group">
						<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-list"></i> Articulos del documento</h3></label>
					</div>
					<table width="100%" class="table table-bordered table-hover">
						<thead>
							<tr>
								<th>#</th>
								<th>Código artículo</th>
								<th>Nombre artículo</th>
								<th>Unidad</th>
								<th>Cantidad</th>
								<th>Almacén</th>
							</tr>
						</thead>
						<tbody>
						<?php
$i = 1;
    while ($row = sqlsrv_fetch_array($SQL_Items)) {
        ?>
						<tr style="cursor: pointer;" onClick="BuscarSerial('<?php echo $row['ItemCode']; ?>','<?php echo $row['WhsCode']; ?>','<?php echo $row['LineNum']; ?>','<?php echo base64_encode($row['ItemName']); ?>','<?php echo $row['UnitMsr']; ?>','<?php echo number_format($row['Quantity'], 0); ?>');">
							<td><?php echo ($row['LineNum'] + 1); ?></td>
							<td><?php echo $row['ItemCode']; ?></td>
							<td><?php echo $row['ItemName']; ?></td>
							<td><?php echo $row['UnitMsr']; ?></td>
							<td><?php echo number_format($row['Quantity'], 0); ?></td>
							<td><?php echo $row['WhsName']; ?></td>
						</tr>
						<?php
$i++;}
    ?>
						</tbody>
					</table>
					<div id="SeriesItem"></div>
					<input type="hidden" id="MMInsert" name="MMInsert" value="1" />
					<div class="form-group">
					<div class="col-lg-9">
						<button class="btn btn-primary" type="button" form="FrmAgregar" onClick="javascript:window.close();" id="Crear"><i class="fa fa-check"></i> Aceptar</button>
					</div>
				</div>
				</form>
			</div>
		</div>
	</div>

<script>
	 $(document).ready(function(){
		 $("#FrmAgregar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
	 });
</script>
</body>
</html>
<?php
sqlsrv_close($conexion);
}?>