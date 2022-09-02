<?php 
	require_once("includes/conexion.php");
	PermitirAcceso(1704);
//require_once("includes/conexion_hn.php");

$sw=0;	
//Fechas
if(isset($_GET['FechaInicial'])&&$_GET['FechaInicial']!=""){
	$FechaInicial=$_GET['FechaInicial'];
	$sw=1;
}else{
	//Restar 7 dias a la fecha actual
	$fecha = date('Y-m-d');
	$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasGestionar").' day');
	$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
	$FechaInicial=$nuevafecha;
}
if(isset($_GET['FechaFinal'])&&$_GET['FechaFinal']!=""){
	$FechaFinal=$_GET['FechaFinal'];
	$sw=1;
}else{
	$FechaFinal=date('Y-m-d');
}

//Filtros
$Cliente=isset($_GET['Cliente']) ? $_GET['Cliente'] : "";
$Sucursal=isset($_GET['Sucursal']) ? $_GET['Sucursal'] : "";
$Estado=isset($_GET['Estado']) ? $_GET['Estado'] : "";
$Empleado= isset($_GET['Empleado']) ? implode(",",$_GET['Empleado']) : "";
$Supervidor=isset($_GET['Supervidor']) ? $_GET['Supervidor'] : "";

	
if($sw==1){
	$Param=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Cliente."'",
		"'".$Sucursal."'",
		"'".$Estado."'",
		"'".$Empleado."'",
		"'".$Supervidor."'"
	);
	$SQL=EjecutarSP('sp_ConsultarEvalTecnicos',$Param);
}
	
//Estado
$SQL_EstadoFrm=Seleccionar('tbl_EstadoFormulario','*');
	
//Empleados
$SQL_Empleados=Seleccionar('uvw_Sap_tbl_Empleados','ID_Empleado, NombreEmpleado','','NombreEmpleado');
	
//Supervisor
$SQL_Supervisor=Seleccionar('uvw_tbl_EvaluacionTecnicos','DISTINCT id_empleado_supervisor, empleado_supervisor','','empleado_supervisor');

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Evaluación de técnicos | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
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
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Cliente.value+"&sucline=1",
				success: function(response){
					$('#Sucursal').html(response).fadeIn();
					$('#Sucursal').trigger('change');
				}
			});	
		});
		$("#Sucursal").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Sucursal=document.getElementById('Sucursal').value;
			var Cliente=document.getElementById("Cliente").value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=36&id="+Sucursal+"&clt="+Cliente,
				success: function(response){
					$('#Bodega').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					$('#Bodega').trigger('change');
				}
			});		
		});
	});
</script>
<script>
	var json=[];
	var cant=0;
function SeleccionarOT(DocNum){
	var btnCambiarLote=document.getElementById('btnCambiarLote');
	var Check = document.getElementById('chkSelOT'+DocNum).checked;
	var sw=-1;
	
	json.forEach(function(element,index){
		if(json[index]==DocNum){
			sw=index;
		}
		//console.log(element,index);
	});
	
	if(sw>=0){
		json.splice(sw, 1);
		cant--;
	}else if(Check){
		json.push(DocNum);
		cant++;
	}
	
	if(cant>0){
		$("#btnCambiarLote").removeAttr("disabled");
	}else{
		$("#chkAll").prop("checked", false);
		$("#btnCambiarLote").attr("disabled","disabled");
	}
	
	//console.log(json);
}
	
function SeleccionarTodos(){
	var Check = document.getElementById('chkAll').checked;
	if(Check==false){
		json=[];
		cant=0;
		$("#btnCambiarLote").attr("disabled","disabled");
	}
	$(".chkSelOT").prop("checked", Check);
	if(Check){
		$(".chkSelOT").trigger('change');
	}		
}	
</script>
<style>
	.swal2-container {
	  	z-index: 9000;
	}
</style>
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
                    <h2>Evaluación de técnicos</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Formularios</a>
                        </li>
                        <li class="active">
                            <strong>Evaluación de técnicos</strong>
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
				  <form action="consultar_frm_eval_tecnico.php" method="get" id="formBuscar" class="form-horizontal">
					  	<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Fechas</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>" autocomplete="off" />
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" autocomplete="off" />
								</div>
							</div>
							<label class="col-lg-1 control-label">Cliente</label>
							<div class="col-lg-3">
								<input name="Cliente" type="hidden" id="Cliente" value="<?php if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){ echo $_GET['Cliente'];}?>">
								<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Para TODOS, dejar vacio..." value="<?php if(isset($_GET['NombreCliente'])&&($_GET['NombreCliente']!="")){ echo $_GET['NombreCliente'];}?>">
							</div>
							<label class="col-lg-1 control-label">Sucursal</label>
							<div class="col-lg-3">
							 <select id="Sucursal" name="Sucursal" class="form-control select2">
								<option value="">(Todos)</option>
								<?php 
								 if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){//Cuando se ha seleccionado una opción
									 if(PermitirFuncion(205)){
										$Where="CodigoCliente='".$_GET['Cliente']."'";
										$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","NombreSucursal, NumeroLinea",$Where);
									 }else{
										$Where="CodigoCliente='".$_GET['Cliente']."' and ID_Usuario = ".$_SESSION['CodUser'];
										$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal, NumeroLinea",$Where);	
									 }
									 while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
										<option value="<?php echo $row_Sucursal['NumeroLinea'];?>" <?php if(strcmp($row_Sucursal['NumeroLinea'],$_GET['Sucursal'])==0){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['NombreSucursal'];?></option>
								<?php }
								 }?>
							</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Estado</label>
							<div class="col-lg-3">
								<select name="Estado" class="form-control" id="Estado">
										<option value="">(Todos)</option>
								  <?php while($row_EstadoFrm=sqlsrv_fetch_array($SQL_EstadoFrm)){?>
										<option value="<?php echo $row_EstadoFrm['Cod_Estado'];?>" <?php if((isset($_GET['Estado']))&&(strcmp($row_EstadoFrm['Cod_Estado'],$_GET['Estado'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoFrm['NombreEstado'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Técnico</label>
							<div class="col-lg-3">
								<select data-placeholder="(Todos)" name="Empleado[]" class="form-control select2" id="Empleado" multiple>
								  <?php $j=0;
									while($row_Empleados=sqlsrv_fetch_array($SQL_Empleados)){?>
										<option value="<?php echo $row_Empleados['ID_Empleado'];?>" <?php if((isset($_GET['Empleado'][$j])&&($_GET['Empleado'][$j]!=""))&&(strcmp($row_Empleados['ID_Empleado'],$_GET['Empleado'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_Empleados['NombreEmpleado'];?></option>
								  <?php }?>
								</select>
							</div>	
							<label class="col-lg-1 control-label">Supervisor</label>
							<div class="col-lg-3">
								<select name="Supervisor" class="form-control select2" id="Supervisor">
									<option value="">(Todos)</option>
								  <?php while($row_Supervisor=sqlsrv_fetch_array($SQL_Supervisor)){?>
										<option value="<?php echo $row_Supervisor['id_empleado_supervisor'];?>" <?php if((isset($_GET['Supervisor']))&&(strcmp($row_Supervisor['id_empleado_supervisor'],$_GET['Supervisor'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Supervisor['empleado_supervisor'];?></option>
								  <?php }?>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<div class="col-lg-4 pull-right">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
					  	<?php if($sw==1){?>
					  	<div class="form-group">
							<div class="col-lg-10">
								<a href="exportar_excel.php?exp=10&Cons=<?php echo base64_encode(implode(",",$Param));?>&sp=<?php echo base64_encode("sp_ConsultarEvalTecnicos");?>">
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
        <?php if($sw==1){?>  
		<div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
					<div class="row m-b-md">
						<div class="col-lg-12">
							<button class="pull-right btn btn-success" id="btnCambiarLote" name="btnCambiarLote" onClick="CambiarEstado('',true);" disabled><i class="fa fa-pencil"></i> Cambiar estados en lote</button>
						</div>
					</div>
					<div class="table-responsive">
							<table class="table table-striped table-bordered table-hover dataTables-example" >
							<thead>
							<tr>
								<th>ID</th>
								<th>Supervisor</th>
								<th>Técnico</th>
								<th>Cliente</th>
								<th>Sucursal</th>
								<th>Observaciones</th>
								<th>Comentarios cierre</th>
								<th>Fecha creación</th>
								<th>Usuario creación</th>
								<th>Fecha cierre</th>
								<th>Usuario cierre</th>
								<th>Estado</th>						
								<th>Acciones</th>
								<th class="text-center"><div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div></th>
							</tr>
							</thead>
							<tbody>
							<?php while($row=sqlsrv_fetch_array($SQL)){ ?>
								<tr id="tr_Resum<?php echo $row['id_evaluacion_tecnico'];?>" class="trResum">
									<td><?php echo $row['id_evaluacion_tecnico'];?></td>
									<td><?php echo $row['empleado_supervisor'];?></td>
									<td><?php echo $row['empleado_tecnico'];?></td>
									<td><?php echo $row['socio_negocio'];?></td>
									<td><?php echo $row['id_direccion_destino'];?></td>
									<td><?php echo SubComent($row['observaciones'],140);?></td>
									<td id="comentCierre<?php echo $row['id_evaluacion_tecnico'];?>"><?php echo SubComent($row['comentarios_cierre'],140);?></td>
									<td><?php echo $row['fecha_hora']->format('Y-m-d H:i');?></td>
									<td><?php echo $row['nombre_usuario_creacion'];?></td>	
									<td><?php echo ($row['fecha_cierre']!="") ? $row['fecha_cierre']->format('Y-m-d H:i') : "";?></td>
									<td><?php echo $row['nombre_usuario_cierre'];?></td>	
									<td><span id="lblEstado<?php echo $row['id_evaluacion_tecnico'];?>" <?php if($row['estado']=='O'){echo "class='label label-info'";}elseif($row['estado']=='A'){echo "class='label label-danger'";}else{echo "class='label label-primary'";}?>><?php echo $row['nombre_estado'];?></span></td>
									<td class="text-center form-inline w-80">
										<?php if($row['estado']=='O'){?>
											<button id="btnEstado<?php echo $row['id_evaluacion_tecnico'];?>" class="btn btn-success btn-xs" onClick="CambiarEstado('<?php echo $row['id_evaluacion_tecnico'];?>');" title="Cambiar estado"><i class="fa fa-pencil"></i></button>
										<?php }?>
										
										<a href="filedownload.php?file=<?php echo base64_encode("EvaluacionTecnicos/DescargarFormatos/".$row['id_evaluacion_tecnico']."/".$_SESSION['User']);?>&api=1" target="_blank" class="btn btn-warning btn-xs" title="Descargar"><i class="fa fa-download"></i></a>
									</td>
									<td class="text-center">
										<?php if($row['estado']=='O'){?>
										<div class="checkbox checkbox-success" id="dvChkSel<?php echo $row['id_evaluacion_tecnico'];?>">											
											<input type="checkbox" class="chkSelOT" id="chkSelOT<?php echo $row['id_evaluacion_tecnico'];?>" value="" onChange="SeleccionarOT('<?php echo $row['id_evaluacion_tecnico'];?>');" aria-label="Single checkbox One"><label></label>
										</div>
										<?php }?>
									</td>
								</tr>
							<?php }?>
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
                pageLength: 25,
				order: [[ 0, "desc" ]],
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
<script>
function CambiarEstado(id,lote=false){
	$('.ibox-content').toggleClass('sk-loading',true);
	
	if(lote){
		id=json
	}
	
	$.ajax({
		type: "POST",
		url: "md_frm_cambiar_estados.php",
		data:{
			id:id,
			frm: 'EvaluacionTecnicos',
			nomID: 'id_evaluacion_tecnico'
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		}
	});
}

function PonerQuitarClase(ID){
	$(".trResum").removeClass('bg-light');
	$("#tr_Resum"+ID).addClass('bg-light');	
}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>