<?php require_once("includes/conexion.php");
PermitirAcceso(317);

$sw=0;


//Sucursal
$ParamSucursal=array(
	"'".$_SESSION['CodUser']."'"
);
$SQL_Sucursal=EjecutarSP('sp_ConsultarSucursalesUsuario',$ParamSucursal);

//Metodo de apliacion
$SQL_MetodoAplicacion=Seleccionar("uvw_Sap_tbl_OrdenesVentasDetalleMetodoAplicacion","*","","DeMetodoAplicacion");

if(isset($_GET['Sucursal'])&&$_GET['Sucursal']!=""){
	$sw=1;
	//Serie de llamada
	$ParamSerie=array(
		"'".$_SESSION['CodigoSAP']."'",
		"'191'"
	);
	$SQL_Series=EjecutarSP('sp_ConsultarSeriesDocumentos',$ParamSerie);
}

//Fechas
if(isset($_GET['FechaInicial'])&&$_GET['FechaInicial']!=""){
	$FechaInicial=$_GET['FechaInicial'];
	$sw=1;
}else{
	//Restar 7 dias a la fecha actual
	$fecha = date('Y-m-d');
	$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasDocSAP").' day');
	$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
	$FechaInicial=$nuevafecha;
}
if(isset($_GET['FechaFinal'])&&$_GET['FechaFinal']!=""){
	$FechaFinal=$_GET['FechaFinal'];
	$sw=1;
}else{
	$FechaFinal=date('Y-m-d');
}

if($sw==1){
	$Param=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$_GET['Articulo']."'",
		"'".$_GET['ArticuloCambiar']."'",
		"'".$_GET['TipoTransaccion']."'",
		"'".$_GET['SeriesOT']."'",
		"'".$_GET['MetodoAplicacion']."'",
		"'".strtolower($_SESSION['CodUser'])."'"
	);
	$SQL=EjecutarSP('usp_tbl_CambioProductoOrdenVenta_Sel',$Param);
	$row=sqlsrv_fetch_array($SQL);
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Cambio de producto | <?php echo NOMBRE_PORTAL;?></title>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<style>
.select2-container{ width: 100% !important; }
</style>
<script type="text/javascript">
	$(document).ready(function() {
		$("#NombreClienteActividad").change(function(){
			var NomCliente=document.getElementById("NombreClienteActividad");
			var Cliente=document.getElementById("ClienteActividad");
			if(NomCliente.value==""){
				Cliente.value="";
			}	
		});
		
		$("#Sucursal").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Sucursal=document.getElementById('Sucursal').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=26&id="+Sucursal+"&tdoc=191",
				success: function(response){
					$('#SeriesOT').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});	
		});
		
	});	
</script>
<script>	
function Validar(Tipo){
	Swal.fire({
		title: "¿Está seguro que desea ejecutar el proceso?",
		text: "Se realizará el cambio de forma masiva",
		icon: "info",
		showCancelButton: true,
		confirmButtonText: "Si, confirmo",
		cancelButtonText: "No"
	}).then((result) => {
		if (result.isConfirmed) {
			EjecutarProceso(Tipo);
		}
	});	
}

function EjecutarProceso(Tipo){
	$('.ibox-content').toggleClass('sk-loading',true);
	var Evento = document.getElementById("IdEvento").value;
	var FechaInicial = document.getElementById("FechaInicial").value;
	var FechaFinal = document.getElementById("FechaFinal").value;
	var Sucursal = document.getElementById("Sucursal").value;
	var SeriesOT = document.getElementById("SeriesOT").value;
	var DGDetalle = document.getElementById("DGDetalle");
	
	$.ajax({
		url:"ajx_ejecutar_json.php",
		data:{
			type:4,
			Evento:Evento,
			FechaInicial:FechaInicial,
			FechaFinal:FechaFinal,
			Sucursal:Sucursal,
			SeriesOT:SeriesOT
		},
		dataType:'json',
		success: function(data){
			if(data.Estado==1){
				$("#UltEjecucion").html(MostrarFechaHora());				
				DGDetalle.src="detalle_cambio_producto_ot.php";
				ConsultarCant();
				$('.ibox-content').toggleClass('sk-loading',false);	
			}
			Swal.fire({
				title: data.Title,
				text: data.Mensaje,
				icon: data.Icon,
			});
			$('.ibox-content').toggleClass('sk-loading',false);	
		}
	});	
}
	
function ConsultarCant(){
	$.ajax({
		url:"ajx_buscar_datos_json.php",
		data:{
			type:33
		},
		dataType:'json',
		success: function(data){
			if(data){
				$("#Tot_ValOK").html(data.ValOK);
				$("#Tot_ValNov").html(data.ValNov);
				$("#Tot_Pend").html(data.Pend);
				$("#Tot_Creadas").html(data.Creadas);
				$("#Tot_NoCreadas").html(data.NoCreadas);
			}
		}
	});
}
</script>
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Cambio de producto</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Servicios</a>
                        </li>
						 <li>
                            <a href="#">Asistentes</a>
                        </li>
                        <li class="active">
                            <strong>Cambio de producto</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="cambio_producto_ot.php" method="get" id="formBuscar" class="form-horizontal">
						   <div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						  </div>
						<div class="form-group">							
							<label class="col-lg-1 control-label">Fechas</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>" autocomplete="off"/>
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" autocomplete="off" />
								</div>
							</div>
							<label class="col-lg-1 control-label">Sucursal</label>
							<div class="col-lg-3">
								<select name="Sucursal" class="form-control" id="Sucursal" required>
									<option value="">Seleccione...</option>
								  <?php	while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
											<option value="<?php echo $row_Sucursal['IdSucursal'];?>" <?php if(isset($_GET['Sucursal'])&&(strcmp($row_Sucursal['IdSucursal'],$_GET['Sucursal'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['DeSucursal'];?></option>
									<?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Serie OT</label>
							<div class="col-lg-2">
								<select name="SeriesOT" class="form-control" id="SeriesOT" required>
										<option value="">Seleccione...</option>
								  <?php if($sw==1){ 
											while($row_Series=sqlsrv_fetch_array($SQL_Series)){?>
											<option value="<?php echo $row_Series['IdSeries'];?>" <?php if((isset($_GET['SeriesOT']))&&(strcmp($row_Series['IdSeries'],$_GET['SeriesOT'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries'];?></option>
								  <?php 	}
										}?>
								</select>
							</div>							
						</div>
					 	<div class="form-group">
							<label class="col-lg-1 control-label">Articulo</label>
							<div class="col-lg-3">
								<input name="Articulo" type="hidden" id="Articulo" value="<?php if(isset($_GET['Articulo'])&&($_GET['Articulo']!="")){ echo $_GET['Articulo'];}?>">
								<input name="NombreArticulo" type="text" class="form-control" id="NombreArticulo" placeholder="Ingrese para buscar..." value="<?php if(isset($_GET['NombreArticulo'])&&($_GET['NombreArticulo']!="")){ echo $_GET['NombreArticulo'];}?>" required>
							</div>
							<label class="col-lg-1 control-label">Cambiarlo por</label>
							<div class="col-lg-3">
								<input name="ArticuloCambiar" type="hidden" id="ArticuloCambiar" value="<?php if(isset($_GET['ArticuloCambiar'])&&($_GET['ArticuloCambiar']!="")){ echo $_GET['ArticuloCambiar'];}?>">
								<input name="NombreArticuloCambiar" type="text" class="form-control" id="NombreArticuloCambiar" placeholder="Ingrese para buscar..." value="<?php if(isset($_GET['NombreArticuloCambiar'])&&($_GET['NombreArticuloCambiar']!="")){ echo $_GET['NombreArticuloCambiar'];}?>" required>
							</div>
							<label class="col-lg-1 control-label">Tipo transacción</label>
							<div class="col-lg-2">
								<select name="TipoTransaccion" class="form-control" id="TipoTransaccion">
									<option value="0" <?php if((isset($_GET['TipoTransaccion']))&&($_GET['TipoTransaccion']==0)){ echo "selected=\"selected\"";}?>>Cambio de producto</option>
									<option value="1" <?php if((isset($_GET['TipoTransaccion']))&&($_GET['TipoTransaccion']==1)){ echo "selected=\"selected\"";}?>>Eliminar producto</option>
									<option value="2" <?php if((isset($_GET['TipoTransaccion']))&&($_GET['TipoTransaccion']==2)){ echo "selected=\"selected\"";}?>>Equivalencia producto</option>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Método de aplicación</label>
							<div class="col-lg-3">
								<select name="MetodoAplicacion" class="form-control select2" id="MetodoAplicacion">
									<option value="">(NINGUNO)</option>
								  <?php	while($row_MetodoAplicacion=sqlsrv_fetch_array($SQL_MetodoAplicacion)){?>
											<option value="<?php echo $row_MetodoAplicacion['IdMetodoAplicacion'];?>" <?php if(isset($_GET['MetodoAplicacion'])&&(strcmp($row_MetodoAplicacion['IdMetodoAplicacion'],$_GET['MetodoAplicacion'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_MetodoAplicacion['DeMetodoAplicacion'];?></option>
									<?php }?>
								</select>
							</div>
							<div class="col-lg-8">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
				 </form>
			</div>
			</div>
		  </div>
         <br>
		<?php if($sw==1){?>	 
		<div class="row">
			<div class="col-lg-2">
				<div class="ibox">
					<div class="ibox-title">
						<h5><span class="font-normal">Cant. Validación OK</span></h5>
					</div>
					<div class="ibox-content">
						<h2 class="no-margins font-bold text-success" id="Tot_ValOK">0</h2>
					</div>
				</div>
			</div>
			<div class="col-lg-2">
				<div class="ibox">
					<div class="ibox-title">
						<h5><span class="font-normal">Cant. Con novedad</span></h5>
					</div>
					<div class="ibox-content">
						<h2 class="no-margins font-bold text-danger" id="Tot_ValNov">0</h2>
					</div>
				</div>
			</div>
			<div class="col-lg-2">
				<div class="ibox">
					<div class="ibox-title">
						<h5><span class="font-normal">Cant. Pendiente por ejecutar</span></h5>
					</div>
					<div class="ibox-content">
						<h2 class="no-margins font-bold text-warning" id="Tot_Pend">0</h2>
					</div>
				</div>
			</div>
			<div class="col-lg-2">
				<div class="ibox">
					<div class="ibox-title">
						<h5><span class="font-normal">Cant. Cambiadas</span></h5>
					</div>
					<div class="ibox-content">
						<h2 class="no-margins font-bold text-navy" id="Tot_Creadas">0</h2>
					</div>
				</div>
			</div>
			<div class="col-lg-2">
				<div class="ibox">
					<div class="ibox-title">
						<h5><span class="font-normal">Cant. No cambiadas</span></h5>
					</div>
					<div class="ibox-content">
						<h2 class="no-margins font-bold text-danger" id="Tot_NoCreadas">0</h2>
					</div>
				</div>
			</div>
		</div>
		<br>
		 <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
					<div class="row">
						<div class="col-lg-8">
							<button class="btn btn-success btn-lg" disabled type="button" id="Ejecutar" onClick="Validar('1');"><i class="fa fa-play-circle"></i> Ejecutar transacción</button>
							<input type="hidden" id="IdEvento" value="<?php if(isset($row['IdEvento'])){echo $row['IdEvento'];}?>" />
						</div>
						<div class="col-lg-2">
							<div class="form-group border">
								<div class="p-xs">
									<label class="text-muted">Última validación</label>
									<div class="font-bold"><?php echo date('Y-m-d H:i');?></div>
								</div>
							</div>
						</div>
						<div class="col-lg-2">
							<div class="form-group border">
								<div class="p-xs">
									<label class="text-muted">Última ejecución</label>
									<div id="UltEjecucion" class="font-bold">&nbsp;</div>
								</div>
							</div>
						</div>
					</div>
					<div class="tabs-container">  
						<ul class="nav nav-tabs">
							<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Contenido</a></li>
							<li><span class="TimeAct"><div id="TimeAct">&nbsp;</div></span></li>
						</ul>
						<div class="tab-content">
							<div id="tab-1" class="tab-pane active">
								<iframe id="DGDetalle" name="DGDetalle" style="border: 0;" width="100%" height="500" src="detalle_cambio_producto_ot.php"></iframe>
							</div>
						</div>					
					</div>
				</div>
			</div>			
          </div>	
		 <?php }?>
        </div>
        <!-- InstanceEndEditable -->
        <?php include("includes/footer.php"); ?>

    </div>
</div>
<?php include("includes/pie.php"); ?>
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
				format: 'yyyy-mm-dd'
            });
			 $('#FechaFinal').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd'
            }); 
			
			$(".select2").select2();
			
			<?php if($sw==1){?>
			ConsultarCant();
			<?php }?>
			
			var options = {
				url: function(phrase) {
					return "ajx_buscar_datos_json.php?type=24&id="+phrase;
				},

				getValue: "NombreBuscarArticulo",
				requestDelay: 400,
				list: {
					match: {
						enabled: true
					},
					onClickEvent: function() {
						var value = $("#NombreArticulo").getSelectedItemData().IdArticulo;
						$("#Articulo").val(value).trigger("change");
					}
				}
			};
			
			var options2 = {
				url: function(phrase) {
					return "ajx_buscar_datos_json.php?type=24&id="+phrase;
				},

				getValue: "NombreBuscarArticulo",
				requestDelay: 400,
				list: {
					match: {
						enabled: true
					},
					onClickEvent: function() {
						var value = $("#NombreArticuloCambiar").getSelectedItemData().IdArticulo;
						$("#ArticuloCambiar").val(value).trigger("change");
					}
				}
			};

			$("#NombreArticulo").easyAutocomplete(options);
			$("#NombreArticuloCambiar").easyAutocomplete(options2);
			
            $('.dataTables-example').DataTable({
                pageLength: 25,
                dom: '<"html5buttons"B>lTfgitp',
				order: [[ 0, "desc" ]],
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