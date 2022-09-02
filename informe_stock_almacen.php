<?php 
	require_once("includes/conexion.php");
	PermitirAcceso(1207);
//require_once("includes/conexion_hn.php");

$sw=0;

//Filtros
$Filtro="";//Filtro
if(isset($_GET['Almacen'])&&$_GET['Almacen']!=""){
	$Filtro.="Where WhsCode='".$_GET['Almacen']."'";
	$sw=1;
}

if(isset($_GET['Stock'])&&$_GET['Stock']=="SI"){
	$Filtro.=" and OnHand > 0";
	$sw=1;
}

if($sw==1){
	$Cons="Select * From uvw_Sap_tbl_Articulos $Filtro Order by ItemName";
	$SQL=sqlsrv_query($conexion,$Cons);
}


//Almacenes
$SQL_Almacen=Seleccionar('uvw_Sap_tbl_Almacenes','*');

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Informe de stock de almacén | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {
		
	});
</script>
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include_once("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Informe de stock de almacén</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Inventario</a>
                        </li>						
                        <li>
                            <a href="#">Informes</a>
                        </li>
                        <li class="active">
                            <strong>Informe de stock de almacén</strong>
                        </li>
                    </ol>
                </div>			
            </div>
         <div class="wrapper wrapper-content">
			 <div class="modal inmodal fade" id="myModal" tabindex="1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content" id="ContenidoModal">
						
					</div>
				</div>
			</div>
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="informe_stock_almacen.php" method="get" id="formBuscar" class="form-horizontal">
					  	<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Almacén <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select id="Almacen" name="Almacen" class="form-control select2">
									<option value="">Seleccione...</option>
									<?php while($row_Almacen=sqlsrv_fetch_array($SQL_Almacen)){?>
										<option value="<?php echo $row_Almacen['WhsCode'];?>" <?php if((isset($_GET['Almacen']))&&(strcmp($row_Almacen['WhsCode'],$_GET['Almacen'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Almacen['WhsName'];?></option>
								  	<?php }?>
								</select>
							</div>
							<div class="col-lg-3">
								<label class="checkbox-inline i-checks"><input name="Stock" id="Stock" type="checkbox" value="SI" <?php if(isset($_GET['Stock'])&&($_GET['Stock']=="SI")){ echo "checked";}?>> Ocultar artículos sin cantidad en stock</label>
							</div>
							<div class="col-lg-3">
								<button type="submit" class="btn btn-outline btn-success"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
					  	<?php if($sw==1){?>
					  	<div class="form-group">
							<div class="col-lg-10">
								<a href="exportar_excel.php?exp=13&Cons=<?php echo base64_encode($Cons);?>">
									<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
								</a>
							</div>
						</div>
					   <?php }?>
				 </form>
			</div>
			</div>
		  </div>
         <br>
        
		<div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
					<div class="table-responsive">
							<table class="table table-striped table-bordered table-hover dataTables-example" >
							<thead>
							<tr>
								<th>Código artículo</th>
								<th>Nombre artículo</th>
								<th>Unidad</th>
								<th>En stock</th>
								<th>Comprometido</th>
								<th>Solicitado</th>
								<th>Disponible</th>
								<th>Costo artículo</th>
								<th>Total</th>
								<th>Almacén</th>
							</tr>
							</thead>
							<tbody>
							<?php if($sw==1){
								while($row=sqlsrv_fetch_array($SQL)){ ?>
								<tr>
									<td><a href="articulos.php?id=<?php echo base64_encode($row['ItemCode']);?>&tl=1" target="_blank"><?php echo $row['ItemCode'];?></a></td>
									<td><?php echo $row['ItemName'];?></td>
									<td><?php echo $row['InvntryUom'];?></td>
									<td><?php echo number_format($row['OnHand'],2);?></td>
									<td><?php echo ($row['Comprometido']>0) ? number_format($row['Comprometido'],2) : "";?></td>
									<td><?php echo ($row['Pedido']>0) ? number_format($row['Pedido'],2) : "";?></td>
									<td><?php echo number_format($row['Disponible'],2);?></td>
									<td><?php echo "$".number_format($row['CostoArticulo'],2);?></td>
									<td><?php echo "$".number_format($row['CostoTotal'],2);?></td>
									<td><?php echo $row['WhsName'];?></td>
								</tr>
							<?php }
							}?>
							</tbody>
							</table>
					  </div>
				</div>
			 </div> 
          </div>
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
 <script>
        $(document).ready(function(){
			$("#formBuscar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
			 $(".alkin").on('click', function(){
					$('.ibox-content').toggleClass('sk-loading');
				});	
			
			$(".select2").select2();
			 $('.i-checks').iCheck({
				 checkboxClass: 'icheckbox_square-green',
				 radioClass: 'iradio_square-green',
			  });
			
            $('.dataTables-example').DataTable({
                pageLength: 25,
				rowGroup: {
					dataSrc: 9
				},
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

</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>