<?php require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
PermitirAcceso(414);

$sw=0;
$Serie="";
$Cliente="";
$Sucursal="";
$EstadoLlamada="";
$Facturado="";
//$Zona="";
$ServicioLlamada="";
$TipoLlamada="";
$EstadoServicio="";

//Estado actividad
$SQL_EstadoLlamada=Seleccionar('uvw_tbl_EstadoLlamada','*');

//Asignado por
$SQL_AsignadoPor=Seleccionar('uvw_Sap_tbl_LlamadasServicios','DISTINCT IdAsignadoPor, DeAsignadoPor','','DeAsignadoPor');

//Estado servicio llamada
$SQL_EstServLlamada=Seleccionar('uvw_Sap_tbl_LlamadasServiciosEstadoServicios','*','','DeEstadoServicio');

//Tipo de problema llamada
$SQL_TipoProblema=Seleccionar('uvw_Sap_tbl_TipoProblemasLlamadas','*','','DeTipoProblemaLlamada');

//Tipo de llamada
$SQL_TipoLlamadas=Seleccionar('uvw_Sap_tbl_TipoLlamadas','*','','DeTipoLlamada');

//Zona
//$SQL_Zonas=Seleccionar('uvw_Sap_tbl_Clientes_Zonas','*');

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

//Filtros
$TipoFecha= isset($_GET['TipoFecha']) ? $_GET['TipoFecha'] : "";
$Cliente= isset($_GET['Cliente']) ? $_GET['Cliente'] : "";
$Sucursal= isset($_GET['Sucursal']) ? $_GET['Sucursal'] : "";
$Serie= isset($_GET['Series']) ? $_GET['Series'] : "";
$EstadoLlamada= isset($_GET['EstadoLlamada']) ? $_GET['EstadoLlamada'] : "";
$Facturado= isset($_GET['Facturado']) ? $_GET['Facturado'] : "";
//$Zona= isset($_GET['Zona']) ? $_GET['Zona'] : "";
$ServicioLlamada= isset($_GET['Servicio']) ? $_GET['Servicio'] : "";
$TipoLlamada= isset($_GET['TipoLlamada']) ? implode(",",$_GET['TipoLlamada']) : "";
$EstadoServicio= isset($_GET['EstadoServicio']) ? implode(",",$_GET['EstadoServicio']) : "";

if($sw==1){
	$Param=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$TipoFecha."'",
		"'".$Cliente."'",
		"'".$Sucursal."'",
		"'".$EstadoLlamada."'",
		"'".$Serie."'",
		"'".$ServicioLlamada."'",
		"'".$TipoLlamada."'",
		"'".$EstadoServicio."'",
		"'".$Facturado."'",
		"'".$_SESSION['CodUser']."'"
	);
	$SQL=EjecutarSP('sp_ConsultarImpresionOT',$Param);
	
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Impresión de OT | <?php echo NOMBRE_PORTAL;?></title>
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
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Cliente.value,
				success: function(response){
					$('#Sucursal').html(response).fadeIn();
				}
			});
		});
		
		/*$("#btnZIP").click(function(){
			$.ajax({
				type: "POST",
				url: "attachdownload.php",
				data: {
					file: Base64.encode(strJSON),
					zip: Base64.encode('1')					
				},
				success: function(response){
					console.log(response);
				}
			});
		});*/
		
	});
</script>
<script>
	var json=[];
	var cant=0;
function SeleccionarOT(DocNum, AbsEntry, LineNum, DocEntry, Serie){
	//var add=new Array(DocNum,AbsEntry,LineNum);
	var btnZIP=document.getElementById('btnZIP');
	var Check = document.getElementById('chkSelOT'+DocNum).checked;
	var sw=-1;
	var strJSON;
	var JSONFile=document.getElementById('file');
	
	json.forEach(function(element,index){
		if(json[index].DocNum==DocNum){
			sw=index;
		}
		//console.log(element,index);
	});
	
	if(sw>=0){
		json.splice(sw, 1);
		cant--;
	}else{
		json.push({Obj:'191',DocNum,AbsEntry,LineNum,Num:DocEntry,Serie});
		cant++;
	}
	
	if(Check){
		PonerQuitarClase("chkSelOT"+DocNum);
    }else{
		PonerQuitarClase("chkSelOT"+DocNum,2);
	}
	
	strJSON=JSON.stringify(json);
	
	if(cant>0){
		JSONFile.value=Base64.encode(strJSON);
		//btnZIP.setAttribute('href',"attachdownload.php?file="+Base64.encode(strJSON)+"&line=&zip=<?php //echo base64_encode('1');?>");
		$("#btnZIP").removeClass("disabled");
	}else{
		$("#btnZIP").addClass("disabled");
	}
	
	//console.log(json);
}

function PonerQuitarClase(idName,evento=1){
	if($("#"+idName).length){
		if(evento==1){
			$("#"+idName).parents('tr').addClass('bg-success');
		}else{
			$("#"+idName).parents('tr').removeClass('bg-success');
		}		
	}
}

function SeleccionarTodos(){
	$(".chkSelOT").prop("checked", $("#chkAll").prop('checked'));
	$(".chkSelOT").trigger('change');	
}
	
function DescargarZIP(idFormato){
	DescargarSAPDownload("sapdownload.php", "id="+btoa('15')+"&type="+btoa('2')+"&zip="+btoa('1')+"&idreg="+btoa(idFormato)+"&file="+JSON.stringify(json), true)
}
	
function DescargarANX(){
	DescargarSAPDownload("attachdownload.php","zip="+btoa('1')+"&file="+btoa(JSON.stringify(json)), true)
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
                    <h2>Impresión de OT</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Ventas - Clientes</a>
                        </li>
                        <li class="active">
                            <strong>Impresión de OT</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="impresion_orden_servicio.php" method="get" id="formBuscar" class="form-horizontal">
					  <div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
					    </div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Cliente</label>
							<div class="col-lg-3">
								<input name="Cliente" type="hidden" id="Cliente" value="<?php if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){ echo $_GET['Cliente'];}?>">
								<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Ingrese para buscar..." value="<?php if(isset($_GET['NombreCliente'])&&($_GET['NombreCliente']!="")){ echo $_GET['NombreCliente'];}?>">
							</div>
							<label class="col-lg-1 control-label">Sucursal</label>
							<div class="col-lg-3">
							 <select id="Sucursal" name="Sucursal" class="form-control select2">
								<option value="">(Todos)</option>
								<?php 
								 if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){//Cuando se ha seleccionado una opción
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
							<label class="col-lg-1 control-label"><select id="TipoFecha" name="TipoFecha"><option value="FechaCreacionLLamada" <?php if(isset($_GET['TipoFecha'])&&($_GET['TipoFecha']=="FechaCreacionLLamada")){ echo "selected=\"selected\"";}?>>Fecha creación</option><option value="FechaCierreLLamada" <?php if(isset($_GET['TipoFecha'])&&($_GET['TipoFecha']=="FechaCierreLLamada")){ echo "selected=\"selected\"";}?>>Fecha cierre</option></select></label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>"/>
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" />
								</div>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Estado llamada</label>
							<div class="col-lg-3">
								<select name="EstadoLlamada" class="form-control" id="EstadoLlamada">
										<option value="">(Todos)</option>
								  <?php while($row_EstadoLlamada=sqlsrv_fetch_array($SQL_EstadoLlamada)){?>
										<option value="<?php echo $row_EstadoLlamada['Cod_Estado'];?>" <?php if((isset($_GET['EstadoLlamada']))&&(strcmp($row_EstadoLlamada['Cod_Estado'],$_GET['EstadoLlamada'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoLlamada['NombreEstado'];?></option>
								  <?php }?>
								</select>
							</div>							
							<label class="col-lg-1 control-label">Serie llamada</label>
							<div class="col-lg-3">
								<select name="Series" class="form-control" id="Series">
										<option value="">(Todos)</option>
								  <?php while($row_Series=sqlsrv_fetch_array($SQL_Series)){?>
										<option value="<?php echo $row_Series['IdSeries'];?>" <?php if((isset($_GET['Series']))&&(strcmp($row_Series['IdSeries'],$_GET['Series'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries'];?></option>
								  <?php }?>
								</select>
							</div>	
							<label class="col-lg-1 control-label">Servicio</label>
							<div class="col-lg-3">
								<input name="Servicio" type="text" class="form-control" id="Servicio" maxlength="50" value="<?php if(isset($_GET['Servicio'])&&($_GET['Servicio']!="")){ echo $_GET['Servicio'];}?>">
							</div>	
						</div>
						<div class="form-group">							
							<label class="col-lg-1 control-label">Tipo llamada</label>
							<div class="col-lg-3">
								<select data-placeholder="(Todos)" name="TipoLlamada[]" class="form-control chosen-select" id="TipoLlamada" multiple>
								  <?php $j=0;
									while($row_TipoLlamadas=sqlsrv_fetch_array($SQL_TipoLlamadas)){?>
										<option value="<?php echo $row_TipoLlamadas['IdTipoLlamada'];?>" <?php if((isset($_GET['TipoLlamada'][$j])&&($_GET['TipoLlamada'][$j]!=""))&&(strcmp($row_TipoLlamadas['IdTipoLlamada'],$_GET['TipoLlamada'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_TipoLlamadas['DeTipoLlamada'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Estado servicio</label>
							<div class="col-lg-3">
								<select data-placeholder="(Todos)" name="EstadoServicio[]" class="form-control chosen-select" id="EstadoServicio" multiple>
										<?php $j=0; 
									while($row_EstServLlamada=sqlsrv_fetch_array($SQL_EstServLlamada)){?>
										<option value="<?php echo $row_EstServLlamada['IdEstadoServicio'];?>" <?php if((isset($_GET['EstadoServicio'][$j])&&($_GET['EstadoServicio'][$j]!=""))&&(strcmp($row_EstServLlamada['IdEstadoServicio'],$_GET['EstadoServicio'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_EstServLlamada['DeEstadoServicio'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Facturado</label>
							<div class="col-lg-3">
								<select name="Facturado" class="form-control" id="Facturado">
									<option value="">(Todos)</option>
									<option value="SI" <?php if(isset($_GET['Facturado'])&&($_GET['Facturado']=="SI")){ echo "selected=\"selected\"";}?>>SI</option>
									<option value="NO" <?php if(isset($_GET['Facturado'])&&($_GET['Facturado']=="NO")){ echo "selected=\"selected\"";}?>>NO</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-8">
								 <?php if($sw==1){?>
								<a href="exportar_excel.php?exp=10&Cons=<?php echo base64_encode(implode(",",$Param));?>&sp=<?php echo base64_encode('sp_ConsultarImpresionOT');?>">
									<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
								</a>
								<?php }?>
							</div>	
							<div class="col-lg-4 pull-right">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>	
					  	</div>	
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
				<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-check-square"></i> Datos para seleccionar</h3></label>
				 </div>
				<div class="row m-b-md">
					<div class="col-lg-6">
						<form id="frmDownload" name="frmDownload" action="attachdownload.php" method="post" target="_blank">
							<input type="hidden" id="file" name="file" value="" />
							<input type="hidden" id="zip" name="zip" value="<?php echo base64_encode('1'); ?>" />
						</form>
					</div>
					<div class="col-lg-6">
						<div class="btn-group pull-right">
							<button data-toggle="dropdown" class="btn btn-primary disabled dropdown-toggle" id="btnZIP" name="btnZIP"><i class="fa fa-download"></i> Descargar <i class="fa fa-caret-down"></i></button>
							<ul class="dropdown-menu">
								<li>
									<a class="dropdown-item" href="javascript:void(0);" onClick="DescargarANX();">Anexos</a>
									<?php 
									$SQL_Formato=Seleccionar('uvw_tbl_FormatosSAP','*',"ID_Objeto=191 and VerEnDocumento='Y'");
									while($row_Formato=sqlsrv_fetch_array($SQL_Formato)){ 
										$Nombre=($row_Formato['DeSeries']=="") ? $row_Formato['NombreVisualizar'] : $row_Formato['NombreVisualizar']." (".$row_Formato['DeSeries'].")";
									?>
									
										<a class="dropdown-item" href="javascript:void(0);" onClick="DescargarZIP('<?php echo $row_Formato['ID']; ?>');"><?php echo $Nombre; ?></a>
								<?php }?>
								</li>
							</ul>
						</div>
					</div>					
				</div>
				<div class="row p-sm">
					<div class="table-responsive">
						<table class="table table-bordered dataTables-example">
						<thead>
						<tr>
							<th>Llamada de servicio</th>
							<th>Serie</th>
							<th>Tipo llamada</th>
							<th>Cliente</th>
							<th>Sucursal</th>
							<th>VTA</th>
							<th>Valor VTA</th>
							<th>Fecha creación</th>
							<th>Fecha cierre</th>
							<th>Estado</th>
							<th>Estado de servicio</th>
							<th>Factura</th>
							<th>Anexo OT</th>
							<th>Seleccionar <div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div></th>
						</tr>
						</thead>
						<tbody>
						<?php $i=0;
							 while($row=sql_fetch_array($SQL)){
								$Icon=IconAttach($row['ExtAnexoLlamada']);?>
							 <tr id="tr_<?php echo $i;?>" class="gradeX">
								<td><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_LlamadaServicio']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('impresion_orden_servicio.php');?>&tl=1" target="_blank"><?php echo $row['DocNum'];?></a></td>	
								<td><?php echo $row['SeriesName'];?></td>
								<td><?php echo $row['DeTipoLlamada'];?></td>
								<td><?php echo $row['NombreClienteLlamada'];?></td>
								<td><?php echo $row['NombreSucursal'];?></td>								 
								<td><?php echo $row['IdVTAFactura'];?></td>
								<td><?php echo number_format($row['ValorVTAFactura'],0);?></td>								 
								<td><?php echo $row['FechaCreacionLLamada'];?></td>
								<td><?php echo $row['FechaCierreLLamada'];?></td>
								<td><span <?php if($row['IdEstadoLlamada']=='-3'){echo "class='label label-info'";}elseif($row['IdEstadoLlamada']=='-2'){echo "class='label label-warning'";}else{echo "class='label label-danger'";}?>><?php echo $row['DeEstadoLlamada'];?></span></td>
								<td><span <?php if($row['CDU_EstadoServicio']=='0'){echo "class='label label-warning'";}elseif($row['CDU_EstadoServicio']=='1'){echo "class='label label-primary'";}else{echo "class='label label-danger'";}?>><?php echo $row['DeEstadoServicio'];?></span></td>
								<td><?php if($row['DocNumFactura']!=""){?><a href="factura_venta.php?id=<?php echo base64_encode($row['DocEntryFactura']);?>&id_portal=<?php echo base64_encode($row['DocPortalFactura']);?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('impresion_orden_servicio.php');?>" target="_blank"><?php echo $row['DocNumFactura'];?></a><?php }else{echo "NO";}?></td>
								<td><?php if($row['DeAnexoLlamada']!=""){?><a href="attachdownload.php?file=<?php echo base64_encode($row['IdAnexoLlamada']);?>&line=<?php echo base64_encode($row['LineNumAnexoLlamada']);?>" target="_blank" title="Descargar archivo" class="btn-link btn-xs"><i class="<?php echo $Icon;?>"></i> <?php echo $row['DeAnexoLlamada'];?></a><?php }?></td>
								<td>
									<?php //if($row['DeAnexoLlamada']!=""){?>
									<div class="checkbox checkbox-success">
										<input type="checkbox" class="chkSelOT" id="chkSelOT<?php echo $row['DocNum'];?>" value="" onChange="SeleccionarOT('<?php echo $row['DocNum'];?>','<?php echo $row['IdAnexoLlamada'];?>','<?php echo $row['LineNumAnexoLlamada'];?>','<?php echo $row['ID_LlamadaServicio'];?>','<?php echo $row['Series'];?>');" aria-label="Single checkbox One"><label></label>
									</div>
									<?php //}?>
								</td>
							</tr>
						<?php $i++;}?>
						</tbody>
						</table>				
              		</div>
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
                pageLength: 25,
				lengthMenu: [ [10, 25, 50, 100, 150, 200, -1], [10, 25, 50, 100, 150, 200, "Todos"] ],
				rowGroup: {
					dataSrc: [3]
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