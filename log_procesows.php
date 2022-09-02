<?php require_once("includes/conexion.php");
PermitirAcceso(401);
$sw=0;

//Fechas
$FechaFinal="";
$FechaInicial="";
if(isset($_GET['FechaInicial'])&&$_GET['FechaInicial']!=""){
	$FechaInicial=$_GET['FechaInicial'];
	$sw=1;
}else{
	//Restar 1 dias a la fecha actual
	$fecha = date('Y-m-d');
	$nuevafecha = date ( 'Y-m-d' , strtotime('-1 day'));
	$FechaInicial=$nuevafecha;
}
if(isset($_GET['FechaFinal'])&&$_GET['FechaFinal']!=""){
	$FechaFinal=$_GET['FechaFinal'];
}else{
	$FechaFinal=date('Y-m-d');
}

// Top
$top= "";
if(isset($_GET['Cantidad_Visualizar'])&&$_GET['Cantidad_Visualizar']!=""){
	$top = "TOP (".$_GET['Cantidad_Visualizar'].")";
} else {
    $top = "TOP (5)";
}

$Cons="Select $top * From uvw_tbl_LogProcesosWs Where (Fecha Between '$FechaInicial 00:00:00' and '$FechaFinal 23:59:59') Order by ID DESC";
$SQL=sqlsrv_query($conexion,$Cons);
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Log proceso WS | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_OFertAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La Oferta de venta ha sido agregada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_OFertUpd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La Oferta de venta ha sido actualizada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
?>
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
                    <h2>Log proceso WS</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="#">Administración</a>
                        </li>
                        <li>
                            <a href="#">Logs del sistema</a>
                        </li>
                        <li class="active">
                            <strong>Log proceso WS</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="log_procesows.php" method="get" id="formBuscar" class="form-horizontal">
					  	<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Fechas</label>
							<div class="col-lg-4">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>"/>
									<span class="input-group-addon">-</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" />
								</div>
							</div>
                            <label class="col-lg-1 control-label">Cantidad a visualizar</label>
							<div class="col-lg-4">
                                <select name="Cantidad_Visualizar" class="form-control" id="Cantidad_Visualizar">
                                    <option value="5" <?php if((isset($_GET['Cantidad_Visualizar']))&&(strcmp("5",$_GET['Cantidad_Visualizar'])==0)){ echo "selected=\"selected\"";}?>>5</option>
                                    <option value="10" <?php if((isset($_GET['Cantidad_Visualizar']))&&(strcmp("10",$_GET['Cantidad_Visualizar'])==0)){ echo "selected=\"selected\"";}?>>10</option>
                                    <option value="25" <?php if((isset($_GET['Cantidad_Visualizar']))&&(strcmp("25",$_GET['Cantidad_Visualizar'])==0)){ echo "selected=\"selected\"";}?>>25</option>
                                    <option value="50" <?php if((isset($_GET['Cantidad_Visualizar']))&&(strcmp("50",$_GET['Cantidad_Visualizar'])==0)){ echo "selected=\"selected\"";}?>>50</option>
                                    <option value="75" <?php if((isset($_GET['Cantidad_Visualizar']))&&(strcmp("75",$_GET['Cantidad_Visualizar'])==0)){ echo "selected=\"selected\"";}?>>75</option>
                                    <option value="100" <?php if((isset($_GET['Cantidad_Visualizar']))&&(strcmp("100",$_GET['Cantidad_Visualizar'])==0)){ echo "selected=\"selected\"";}?>>100</option>
                                </select>
							</div>
                            <div class="col-lg-2">
                                <button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
                            </div>
						</div>
				 </form>
				</div>
				</div>
		  	 </div>
          <br>
			 <?php //echo $Cons;?>
          <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover dataTables-example" >
						<thead>
						<tr>
							<th>Id log</th>
							<th>Fecha</th>
							<th>Id usuario</th>
                            <th>Tipo transacción</th>
                            <th>Proceso</th>
							<th>Mensaje excepción</th>
                            <th>Mensaje SAP B1</th>
						</tr>
						</thead>
						<tbody>
						<?php
							if($sw==1){
							while($row=sqlsrv_fetch_array($SQL)){ ?>
							 <tr class="gradeX">
								<td><?php echo $row['ID'];?></td>
								<td><?php echo $row['Fecha']->format('Y-m-d h:m:i');?></td>
								<td><?php echo $row['IdUsuario'];?></td>
                                <td><?php echo $row['TipoTransaccion'];?></td>
								<td><?php echo $row['Proceso'];?></td>
								<td><?php echo $row['MensajeExcepcion'];?></td>
                                <td><?php echo $row['MensajeSAPB1'];?></td>
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
			 $('#FechaInicial').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true,
            });
			 $('#FechaFinal').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true,
            }); 
			
			$('.chosen-select').chosen({width: "100%"});
			
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
						var value = $("#NombreCliente").getSelectedItemData().CodigoCliente;
						$("#Cliente").val(value);
					}
				}
			};

			$("#NombreCliente").easyAutocomplete(options);

			var cantidadVisualizar = <?php echo (isset($_GET['Cantidad_Visualizar'])) ? $_GET['Cantidad_Visualizar'] : 5; ?>
			
            $('.dataTables-example').DataTable({
                pageLength: cantidadVisualizar,
                lengthMenu: getLengthMenu(cantidadVisualizar),
                responsive: false,
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

			function getLengthMenu(maximumQuantity) {
				var lengthMenu = []
				switch(maximumQuantity) {
					case 5:
						lengthMenu = [5]
						break
                    case 10:
						lengthMenu = [5,10]
						break
					case 25:
						lengthMenu = [5,10,25]
						break
					case 50:
						lengthMenu = [5,10,25,50]
						break
					case 75:
						lengthMenu = [5,10,25,50,75]
						break
					case 100:
						lengthMenu = [5,10,25,50,75,100]
						break
                    default:
                        lengthMenu = [5]
                        break
				}
				return lengthMenu
			}

        });

    </script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>