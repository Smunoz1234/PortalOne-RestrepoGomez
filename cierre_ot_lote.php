<?php require_once("includes/conexion.php");
PermitirAcceso(311);

$sw=0;

//Estado llamada
$SQL_EstadoLlamada=Seleccionar('uvw_tbl_EstadoLlamada','*');

//Sucursal
$ParamSucursal=array(
	"'".$_SESSION['CodUser']."'"
);
$SQL_Sucursal=EjecutarSP('sp_ConsultarSucursalesUsuario',$ParamSucursal);

if(isset($_GET['Sucursal'])&&$_GET['Sucursal']!=""){
	//Serie de llamada
	$ParamSerie=array(
		"'".$_SESSION['CodUser']."'",
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

$Recargar=isset($_GET['reload']) ? $_GET['reload'] : 0;

if($sw==1){
	$Param=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$_GET['Series']."'",
		"'".strtolower($_SESSION['User'])."'",
		"'".$_GET['EstadoLlamada']."'",
		$Recargar
	);
	$SQL=EjecutarSP('usp_tbl_CierreOrdenesServicio_Sel',$Param);
	$row=sqlsrv_fetch_array($SQL);
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Cierre de OT en lote | <?php echo NOMBRE_PORTAL;?></title>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.select2-container{ width: 100% !important; }
	.panel-resizable {
		resize: vertical;
		overflow: auto
	}
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
				url: "ajx_cbo_select.php?type=26&id="+Sucursal+"&tdoc=191&taccion=3",
				success: function(response){
					$('#Series').html(response).fadeIn();
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
		text: "Se cerrarán los documentos listados",
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
		var Series = document.getElementById("Series").value;
		var DG_Actividades=document.getElementById("DG_Actividades");
		var DG_Llamadas=document.getElementById("DG_Llamadas");
		
		$.ajax({
			url:"ajx_ejecutar_json.php",
			data:{type:2,Evento:Evento,FechaInicial:FechaInicial,FechaFinal:FechaFinal,Sucursal:Sucursal,Serie:Series,Tipo:Tipo},
			dataType:'json',
			success: function(data){
				if(data.Estado==1){
					$("#UltEjecucion").html(MostrarFechaHora());				
					DG_Actividades.src="detalle_cierre_ot_lote_actividades.php";
					DG_Llamadas.src="detalle_cierre_ot_lote_llamadas.php";	
					ConsultarCant();
					$('.ibox-content').toggleClass('sk-loading',false);	
				}
				$('.ibox-content').toggleClass('sk-loading',false);
				Swal.fire({
					title: data.Title,
					text: data.Mensaje,
					icon: data.Icon
				});
			},
			error: function(data){
				console.log('Error:', data)
				$('.ibox-content').toggleClass('sk-loading',false);
			}
		});
		
}

function ConsultarCant(){
	$.ajax({
		url:"ajx_buscar_datos_json.php",
		data:{
			type:38,
			doctype:1
		},
		dataType:'json',
		success: function(data){
			if(data){
				$("#Tot_ValOK").html(data.ValOK);
				$("#Tot_ValNov").html(data.ValNov);
				$("#Tot_Pend").html(data.Pend);
				$("#Tot_Cerradas").html(data.Cerradas);
				$("#Tot_NoCerradas").html(data.NoCerradas);
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
                    <h2>Cierre de OT en lote</h2>
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
                            <strong>Cierre de OT en lote</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="cierre_ot_lote.php" method="get" id="formBuscar" class="form-horizontal">
					  <div class="form-group">
						<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
					  </div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Fechas</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>"/>
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" />
								</div>
							</div>
							<label class="col-lg-1 control-label">Sucursal <span class="text-danger">*</span></label>
							<div class="col-lg-2">
								<select name="Sucursal" class="form-control" id="Sucursal" required>
									<option value="">Seleccione...</option>
								  <?php	while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
											<option value="<?php echo $row_Sucursal['IdSucursal'];?>" <?php if(isset($_GET['Sucursal'])&&(strcmp($row_Sucursal['IdSucursal'],$_GET['Sucursal'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['DeSucursal'];?></option>
									<?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Serie <span class="text-danger">*</span></label>
							<div class="col-lg-2">
								<select name="Series" class="form-control" id="Series" required>
										<option value="">Seleccione...</option>
								  <?php if($sw==1){ 
											while($row_Series=sqlsrv_fetch_array($SQL_Series)){?>
											<option value="<?php echo $row_Series['IdSeries'];?>" <?php if((isset($_GET['Series']))&&(strcmp($row_Series['IdSeries'],$_GET['Series'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries'];?></option>
								  <?php 	}
										}?>
								</select>
							</div>							
						</div>
					 	<div class="form-group">
							<label class="col-lg-1 control-label">Estado OT <span class="text-danger">*</span></label>
							<div class="col-lg-2">
								<select name="EstadoLlamada" class="form-control" id="EstadoLlamada" required>
										<option value="">Seleccione...</option>
								  <?php while($row_EstadoLlamada=sqlsrv_fetch_array($SQL_EstadoLlamada)){?>
										<option value="<?php echo $row_EstadoLlamada['Cod_Estado'];?>" <?php if((isset($_GET['EstadoLlamada']))&&(strcmp($row_EstadoLlamada['Cod_Estado'],$_GET['EstadoLlamada'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoLlamada['NombreEstado'];?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-7"></div>
							<div class="col-lg-2">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
					  <input type="hidden" id="reload" name="reload" value="0" />
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
							<h5><span class="font-normal">Cant. OT validación OK</span></h5>
						</div>
						<div class="ibox-content">
							<h2 class="no-margins font-bold text-success" id="Tot_ValOK">0</h2>
						</div>
					</div>
				</div>
				<div class="col-lg-2">
					<div class="ibox">
						<div class="ibox-title">
							<h5><span class="font-normal">Cant. OT con novedad</span></h5>
						</div>
						<div class="ibox-content">
							<h2 class="no-margins font-bold text-danger" id="Tot_ValNov">0</h2>
						</div>
					</div>
				</div>
				<div class="col-lg-2">
					<div class="ibox">
						<div class="ibox-title">
							<h5><span class="font-normal">Cant. OT pendiente por ejecutar</span></h5>
						</div>
						<div class="ibox-content">
							<h2 class="no-margins font-bold text-warning" id="Tot_Pend">0</h2>
						</div>
					</div>
				</div>
				<div class="col-lg-2">
					<div class="ibox">
						<div class="ibox-title">
							<h5><span class="font-normal">Cant. OT cerradas</span></h5>
						</div>
						<div class="ibox-content">
							<h2 class="no-margins font-bold text-navy" id="Tot_Cerradas">0</h2>
						</div>
					</div>
				</div>
				<div class="col-lg-2">
					<div class="ibox">
						<div class="ibox-title">
							<h5><span class="font-normal">Cant. OT no cerradas</span></h5>
						</div>
						<div class="ibox-content">
							<h2 class="no-margins font-bold text-danger" id="Tot_NoCerradas">0</h2>
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
							<button class="btn btn-primary btn-lg btn-outline m-b-md" type="button" id="CierreLlamadas" onClick="Validar('2');"><i class="fa fa-play-circle"></i> Cerrar llamadas de servicio</button>
							<input type="hidden" id="IdEvento" value="<?php if(isset($row['IdEvento'])){echo $row['IdEvento'];}?>" />
						</div>
						<div class="col-lg-2">
							<div class="form-group border">
								<div class="p-xs">
									<label class="text-muted">Última consulta</label>
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
					<div class="tabs-container m-b-md">  
						<ul class="nav nav-tabs">
							<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Llamadas de servicio</a></li>							
							<li><span class="TimeAct"><div id="TimeAct">&nbsp;</div></span></li>
						</ul>
						<div class="tab-content">
							<div id="tab-1" class="tab-pane active">
								<iframe id="DG_Llamadas" name="DG_Llamadas" style="border: 0;" width="100%" height="400" src="detalle_cierre_ot_lote_llamadas.php"></iframe>
							</div>
						</div>					
					</div>
					<button class="btn btn-success btn-lg btn-outline m-b-md" type="button" id="CierreAct" onClick="Validar('1');"><i class="fa fa-play-circle"></i> Cerrar actividades</button>
					<div class="tabs-container">  
						<ul class="nav nav-tabs">
							<li class="active"><a data-toggle="tab" href="#tab-2"><i class="fa fa-tasks"></i> Actividades</a></li>								
							<li><span class="TimeAct"><div id="TimeAct2">&nbsp;</div></span></li>
						</ul>
						<div class="tab-content">
							<div id="tab-2" class="tab-pane active">
								<iframe id="DG_Actividades" name="DG_Actividades" style="border: 0;" width="100%" height="400" src="detalle_cierre_ot_lote_actividades.php"></iframe>
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
				var reload = document.getElementById("reload");
				 <?php if($sw==1){?>
				 	Swal.fire({
						title: "Cierre de OT",
						text: "¿Deseas volver a consultar este cierre o generar uno nuevo?",
						icon: "info",
						showCancelButton: true,
						confirmButtonText: "Generar uno nuevo",
						cancelButtonText: "Continuar con el mismo"
					}).then((result) => {
						if (result.isConfirmed){
							reload.value = "0"
						}else{
							reload.value = "1"
						}
						$('.ibox-content').toggleClass('sk-loading');
				 		form.submit();
					});
			 	 <?php }else{?>
				 	$('.ibox-content').toggleClass('sk-loading');
				 	form.submit();
			 	 <?php }?>
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
				todayHighlight: true
			});
			 $('#FechaFinal').datepicker({
				todayBtn: "linked",
				keyboardNavigation: false,
				forceParse: false,
				calendarWeeks: true,
				autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true
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
						var value = $("#NombreClienteActividad").getSelectedItemData().CodigoCliente;
						$("#ClienteActividad").val(value).trigger("change");
					}
				}
			};

			$("#NombreClienteActividad").easyAutocomplete(options);
			
			<?php if($sw==1){?>
			ConsultarCant();
			<?php }?>
			
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