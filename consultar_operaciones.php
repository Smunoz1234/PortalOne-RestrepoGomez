<?php require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
PermitirAcceso(314);

$sw=0;
$Cliente="";
$Sucursal="";
$Serie="";
$TipoLlamada="";
$EstadoLlamada="";
$EstadoActividad="";
$EstadoServicioLlamada="";
$NombreEmpleado="";
$sw_suc=0;

//Estado llamada
$SQL_EstadoLlamada=Seleccionar('uvw_tbl_EstadoLlamada','*');

//Estado actividad
$SQL_EstadoActividad=Seleccionar('uvw_tbl_TipoEstadoServicio','*');

//Tipo de llamada
$SQL_TipoLlamadas=Seleccionar('uvw_Sap_tbl_TipoLlamadas','*','','DeTipoLlamada');

//Estado servicio llamada
$SQL_EstServLlamada=Seleccionar('uvw_Sap_tbl_LlamadasServiciosEstadoServicios','*','','DeEstadoServicio');

//Empleados
$SQL_EmpleadoActividad=Seleccionar('uvw_Sap_tbl_Empleados','*',"IdUsuarioSAP=0",'NombreEmpleado');

//Serie de llamada
$ParamSerie=array(
	"'".$_SESSION['CodUser']."'",
	"'191'"
);
$SQL_Series=EjecutarSP('sp_ConsultarSeriesDocumentos',$ParamSerie);

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

if(isset($_GET['Cliente'])&&$_GET['Cliente']!=""){
	$Cliente=$_GET['Cliente'];
	$sw_suc=1;
	$sw=1;
}

if(isset($_GET['Sucursal'])&&$_GET['Sucursal']!=""){
	$Sucursal=$_GET['Sucursal'];
	$sw=1;
}

if(isset($_GET['Series'])&&$_GET['Series']!=""){
	$Serie=$_GET['Series'];
	$sw=1;
}

if(isset($_GET['EstadoLlamada'])&&$_GET['EstadoLlamada']!=""){
	$EstadoLlamada=$_GET['EstadoLlamada'];
	$sw=1;
}

if(isset($_GET['EstadoActividad'])&&$_GET['EstadoActividad']!=""){
	$EstadoActividad=$_GET['EstadoActividad'];
	$sw=1;
}

if(isset($_GET['NombreEmpleado'])&&$_GET['NombreEmpleado']!=""){
	$NombreEmpleado=$_GET['NombreEmpleado'];
	$sw=1;
}

$Facturado= isset($_GET['Facturado']) ? $_GET['Facturado'] : "";
$TipoLlamada= isset($_GET['TipoLlamada']) ? implode(",",$_GET['TipoLlamada']) : "";
$EstadoServicioLlamada= isset($_GET['EstadoServicio']) ? implode(",",$_GET['EstadoServicio']) : "";

if($sw==1){
	$Param=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Cliente."'",
		"'".$Sucursal."'",
		"'".$Serie."'",
		"'".$TipoLlamada."'",
		"'".$EstadoLlamada."'",
		"'".$EstadoActividad."'",
		"'".$EstadoServicioLlamada."'",
		"'".$NombreEmpleado."'",
		"'".$Facturado."'",
		"'".strtolower($_SESSION['User'])."'"
	);
	$SQL=EjecutarSP('usp_TraerConsultaOperaciones',$Param);
//	sqlsrv_next_result($SQL);
//	print_r($row);
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Gestión de operaciones | <?php echo NOMBRE_PORTAL;?></title>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.modal-dialog{
		width: 70% !important;
	}
	.modal-footer{
		border: 0px !important;
	}
</style>
<script type="text/javascript">
	$(document).ready(function() {
		$("#NombreCliente").change(function(){
			var NomCliente=document.getElementById("NombreCliente");
			var Cliente=document.getElementById("Cliente");
			if(NomCliente.value==""){
				Cliente.value="";
				$("#Cliente").trigger("change");
			}	
		});
		$("#Cliente").change(function(){
			var Cliente=document.getElementById("Cliente");
			$.ajax({
				type: "POST",
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Cliente.value,
				success: function(response){
					$('#Sucursal').html(response).fadeIn().change();
				}
			});
		});
		
	});
</script>
<script>
	function CargarAct(ID, DocNum){
		$('.ibox-content').toggleClass('sk-loading',true);
		$.ajax({
			type: "POST",
			async: false,
			url: "sn_actividades.php?id="+Base64.encode(ID)+"&objtype=191",
			success: function(response){
				$('.ibox-content').toggleClass('sk-loading',false);
				$('#ContenidoModal').html(response);
				$('#TituloModal').html('Actividades relacionadas - OT: '+ DocNum);
				$('#myModal').modal("show");
			}
		});
	}
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
                    <h2>Gestión de operaciones</h2>
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
                            <strong>Gestión de operaciones</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
			 <div class="modal inmodal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="TituloModal"></h4>
						</div>
						<div class="modal-body" id="ContenidoModal">							
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-success m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
						</div>
					</div>
				</div>
			</div>
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="consultar_operaciones.php" method="get" id="formBuscar" class="form-horizontal">
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
							<label class="col-lg-1 control-label">Cliente</label>
							<div class="col-lg-3">
								<input name="Cliente" type="hidden" id="Cliente" value="<?php if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){ echo $_GET['Cliente'];}?>">
								<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Ingrese para buscar..." value="<?php if(isset($_GET['NombreCliente'])&&($_GET['NombreCliente']!="")){ echo $_GET['NombreCliente'];}?>">
							</div>
							<label class="col-lg-1 control-label">Sucursal cliente</label>
							<div class="col-lg-3">
							 <select id="Sucursal" name="Sucursal" class="form-control select2">
								<option value="">(Todos)</option>
								<?php 
								 if($sw_suc==1){//Cuando se ha seleccionado una opción
									 if(PermitirFuncion(205)){
										$Where="CodigoCliente='".$_GET['Cliente']."'";
										$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","NombreSucursal",$Where);
									 }else{
										$Where="CodigoCliente='".$_GET['Cliente']."' and ID_Usuario = ".$_SESSION['CodUser'];
										$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal",$Where);	
									 }
									 while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
										<option value="<?php echo $row_Sucursal['NombreSucursal'];?>" <?php if(strcmp($row_Sucursal['NombreSucursal'],$_GET['Sucursal'])==0){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['NombreSucursal'];?></option>
								<?php }
								 }?>
							</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Serie</label>
							<div class="col-lg-3">
								<select name="Series" class="form-control" id="Series">
										<option value="">(Todos)</option>
								  <?php while($row_Series=sqlsrv_fetch_array($SQL_Series)){?>
										<option value="<?php echo $row_Series['IdSeries'];?>" <?php if((isset($_GET['Series']))&&(strcmp($row_Series['IdSeries'],$_GET['Series'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries'];?></option>
								  <?php }?>
								</select>
							</div>		
							<label class="col-lg-1 control-label">Tipo llamada</label>
							<div class="col-lg-3">
								<select data-placeholder="(Todos)" name="TipoLlamada[]" class="form-control chosen-select" id="TipoLlamada" multiple>
								  <?php $j=0;
									while($row_TipoLlamadas=sqlsrv_fetch_array($SQL_TipoLlamadas)){?>
										<option value="<?php echo $row_TipoLlamadas['IdTipoLlamada'];?>" <?php if((isset($_GET['TipoLlamada'][$j])&&($_GET['TipoLlamada'][$j]!=""))&&(strcmp($row_TipoLlamadas['IdTipoLlamada'],$_GET['TipoLlamada'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_TipoLlamadas['DeTipoLlamada'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Estado llamada</label>
							<div class="col-lg-3">
								<select name="EstadoLlamada" class="form-control" id="EstadoLlamada">
										<option value="">(Todos)</option>
								  <?php while($row_EstadoLlamada=sqlsrv_fetch_array($SQL_EstadoLlamada)){?>
										<option value="<?php echo $row_EstadoLlamada['Cod_Estado'];?>" <?php if((isset($_GET['EstadoLlamada']))&&(strcmp($row_EstadoLlamada['Cod_Estado'],$_GET['EstadoLlamada'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoLlamada['NombreEstado'];?></option>
								  <?php }?>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Estado actividad</label>
							<div class="col-lg-3">
								<select name="EstadoActividad" class="form-control" id="EstadoActividad">
										<option value="">(Todos)</option>
								  <?php while($row_EstadoActividad=sqlsrv_fetch_array($SQL_EstadoActividad)){?>
										<option value="<?php echo $row_EstadoActividad['DE_TipoEstadoServicio'];?>" <?php if((isset($_GET['EstadoActividad']))&&(strcmp($row_EstadoActividad['DE_TipoEstadoServicio'],$_GET['EstadoActividad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoActividad['DE_TipoEstadoServicio'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Estado servicio llamada</label>
							<div class="col-lg-3">
								<select data-placeholder="(Todos)" name="EstadoServicio[]" class="form-control chosen-select" id="EstadoServicio" multiple>
										<?php $j=0; 
									while($row_EstServLlamada=sqlsrv_fetch_array($SQL_EstServLlamada)){?>
										<option value="<?php echo $row_EstServLlamada['IdEstadoServicio'];?>" <?php if((isset($_GET['EstadoServicio'][$j])&&($_GET['EstadoServicio'][$j]!=""))&&(strcmp($row_EstServLlamada['IdEstadoServicio'],$_GET['EstadoServicio'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_EstServLlamada['DeEstadoServicio'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Técnico</label>
							<div class="col-lg-3">
								<select name="NombreEmpleado" class="form-control select2" id="NombreEmpleado">
									<option value="">(Todos)</option>
										<?php while($row_EmpleadoActividad=sqlsrv_fetch_array($SQL_EmpleadoActividad)){?>
										<option value="<?php echo $row_EmpleadoActividad['NombreEmpleado'];?>" <?php if((isset($_GET['NombreEmpleado']))&&(strcmp($row_EmpleadoActividad['NombreEmpleado'],$_GET['NombreEmpleado'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EmpleadoActividad['NombreEmpleado'];?></option>
								  <?php }?>
								</select>
							</div>
						</div>
					    <div class="form-group">
							<label class="col-lg-1 control-label">Facturado</label>
							<div class="col-lg-3">
								<select name="Facturado" class="form-control" id="Facturado">
									<option value="">(Todos)</option>
									<option value="SI" <?php if(isset($_GET['Facturado'])&&($_GET['Facturado']=="SI")){ echo "selected=\"selected\"";}?>>SI</option>
									<option value="NO" <?php if(isset($_GET['Facturado'])&&($_GET['Facturado']=="NO")){ echo "selected=\"selected\"";}?>>NO</option>
								</select>
							</div>							
							<div class="col-lg-4 pull-right">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>	
					  	</div>
					   <?php if($sw==1){?>
					  	<div class="form-group">
							<div class="col-lg-8">
								<a href="exportar_excel.php?exp=18&Cons=<?php echo base64_encode(implode(",",$Param));?>&sp=<?php echo base64_encode('usp_TraerConsultaOperaciones');?>">
									<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
								</a>								
							</div>	
					  	</div>	
					  <?php }?>
				 </form>
			</div>
			</div>
		  </div>
		<?php if($sw==1){?>
        <br>
        <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
					<div class="table-responsive">
						<table class="table table-bordered table-hover table-striped dataTables-example">
						<thead>
						<tr>
							<th>#</th>
							<th>Actividades</th>
							<th>Id Llamada servicio</th>
							<th>Serie</th>
							<th>Tipo llamada</th>
							<th>Cliente</th>
							<th>Sucursal</th> 
							<th>Facturado</th>
							<th>Servicio</th>  
							<th>Metodo aplicación</th>
							<th>Áreas</th>  
							<th>Fecha creación</th>
							<th>Fecha cierre</th>
							<th>Estado llamada</th>
							<th>Estado servicio llamada</th>
							<th>Fecha actividad</th>
							<th>Fecha de cronograma</th>
							<th>Técnico</th>
							<th>Estado actividad</th>
							<th>Comentarios llamada</th>
							<th>Comentarios de cierre</th>
							<th>Orden de venta</th>
							<th>Entregas</th>
							<th>Devolución</th>
						</tr>
						</thead>
						<tbody>
						<?php $i=1;
							 while($row=sql_fetch_array($SQL)){?>
							<tr id="tr_<?php echo $i;?>" class="gradeX">
								<td><?php echo $i;?></td>
								<td class="text-center"><button type="button" title="Mostrar actividades" class="btn btn-success btn-xs" onClick="CargarAct('<?php echo $row['ID_LlamadaServicio'];?>','<?php echo $row['ID_Llamada'];?>');"><i class="fa fa-plus"></i></button></td>
								<td><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_LlamadaServicio']);?>&tl=1" target="_blank"><?php echo $row['ID_Llamada'];?></a></td>
								<td><?php echo $row['NombreSerie'];?></td>
								<td><?php echo $row['DeTipoLlamada'];?></td>
								<td><?php echo $row['NombreCliente'];?></td>
								<td><?php echo $row['NombreSucursalCliente'];?></td>
								<td><?php echo $row['Facturado'];?></td>
								<td><?php echo $row['ServiciosLlamadas'];?></td>
								<td><?php echo $row['MetodoAplicaLlamadas'];?></td>
								<td><?php echo SubComent($row['AreasCtrlLlamadas'],50);?></td>
								<td><?php echo $row['FechaCreacionLLamada'];?></td>
								<td><?php echo $row['FechaCierreLLamada'];?></td>
								<td><span <?php if($row['IdEstadoLlamada']=='-3'){echo "class='label label-info'";}elseif($row['IdEstadoLlamada']=='-2'){echo "class='label label-warning'";}else{echo "class='label label-danger'";}?>><?php echo $row['DeEstadoLlamada'];?></span></td>	
								<td><span <?php if($row['IdEstadoServicioLlamada']=='0'){echo "class='label label-warning'";}elseif($row['IdEstadoServicioLlamada']=='1'){echo "class='label label-primary'";}else{echo "class='label label-danger'";}?>><?php echo $row['EstadoServicioLlamada'];?></span></td>								
								<td><?php echo ($row['FechaActividad']!="") ? $row['FechaActividad']->format('Y-m-d H:i') : "";?></td>
								<td><?php echo ($row['FechaCronograma']!="") ? $row['FechaCronograma']->format('Y-m-d') : "";?></td>
								<td><?php echo $row['NombreEmpleadoActividad'];?></td>
								<td><?php echo $row['NombreEstadoActividad'];?></td>
								<td><?php echo SubComent($row['ComentarioLlamada']);?></td>
								<td><?php echo SubComent($row['ResolucionLlamada']);?></td>
								<td><?php echo $row['OrdenVenta'];?></td>
								<td><?php echo $row['Entregas'];?></td>
								<td><?php echo $row['Devolucion'];?></td>
							</tr>
						<?php $i++;}?>
						</tbody>
						</table>
              </div>
			</div>
			 </div> 
          </div>
		<?php }?>
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
			$(".select2").select2();
			$('.chosen-select').chosen({width: "100%"});
			$('.i-checks').iCheck({
				 checkboxClass: 'icheckbox_square-green',
				 radioClass: 'iradio_square-green',
			  });
			
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
						$("#Cliente").val(value).trigger("change");
					}
				}
			};

			$("#NombreCliente").easyAutocomplete(options);
			
            $('.dataTables-example').DataTable({
                dom: '<"html5buttons"B>lTfgitp',
				lengthMenu: [[10, 25, 50, 100, 150, 200, -1], [10, 25, 50, 100, 150, 200, "Todos"]],
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