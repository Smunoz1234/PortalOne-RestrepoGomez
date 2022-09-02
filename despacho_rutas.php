<?php require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
PermitirAcceso(316);

$sw=0;
$NombreEmpleado="";
$Sede="";
$Almacen="";
$AlmacenDestino="";
$TipoLlamada="";
$EstadoActividad="";
$SeriesOT="";

//Sucursales
$ParamSucursal=array(
	"'".$_SESSION['CodUser']."'"
);
$SQL_Suc=EjecutarSP('sp_ConsultarSucursalesUsuario',$ParamSucursal);

//Tipo de llamada
$SQL_TipoLlamadas=Seleccionar('uvw_Sap_tbl_TipoLlamadas','*','','DeTipoLlamada');

//Estado actividad
$SQL_EstadoActividad=Seleccionar('uvw_tbl_TipoEstadoServicio','*');

//Tecnicos
if(isset($_GET['Sede'])){
	if($_GET['Sede']!=""){
		$WhereRec="CentroCosto3='".$_GET['Sede']."'";
		$SQL_Recursos=Seleccionar("uvw_Sap_tbl_Recursos","*",$WhereRec,"NombreEmpleado");
		$Sede=$_GET['Sede'];
		
		$ParamSerieOT=array(
			"'".$_GET['Sede']."'",
			"'191'",
			"'".$_SESSION['CodUser']."'",
			"2"
		);
		$SQL_SeriesOT=EjecutarSP('sp_ConsultarSeriesSucursales',$ParamSerieOT);
	}else{
		$WhereRec="CentroCosto3 IN (SELECT IdSucursal
		FROM uvw_tbl_SeriesSucursalesAlmacenes 
		WHERE IdSeries IN (SELECT IdSeries FROM uvw_tbl_UsuariosSeries WHERE ID_Usuario='".$_SESSION['CodUser']."' and IdTipoDocumento=191)
		GROUP BY IdSucursal, DeSucursal)";
		$SQL_Recursos=Seleccionar("uvw_Sap_tbl_Recursos","*",$WhereRec,"NombreEmpleado");
	}
	$sw=1;
}

//Fechas
if(isset($_GET['FechaInicial'])&&$_GET['FechaInicial']!=""){
	$FechaInicial=$_GET['FechaInicial'];
	$sw=1;
}else{
	$FechaInicial=date('Y-m-d');
}

if(isset($_GET['FechaFinal'])&&$_GET['FechaFinal']!=""){
	$FechaFinal=$_GET['FechaFinal'];
	$sw=1;
}else{
	$FechaFinal=date('Y-m-d');
}

if(isset($_GET['Recursos'])&&$_GET['Recursos']!=""){
	$NombreEmpleado=implode(",",$_GET['Recursos']);
	$sw=1;
}

if(isset($_GET['EstadoActividad'])&&$_GET['EstadoActividad']!=""){
	$EstadoActividad=implode(",",$_GET['EstadoActividad']);
	$sw=1;
}

if(isset($_GET['TipoLlamada'])&&$_GET['TipoLlamada']!=""){
	$TipoLlamada=$_GET['TipoLlamada'];
	$sw=1;
}

if(isset($_GET['Almacen'])&&$_GET['Almacen']!=""){
	$Almacen=$_GET['Almacen'];
	$sw=1;
}

if(isset($_GET['AlmacenDestino'])&&$_GET['AlmacenDestino']!=""){
	$AlmacenDestino=$_GET['AlmacenDestino'];
	$sw=1;
}

if(isset($_GET['SeriesOT'])&&$_GET['SeriesOT']!=""){
	$SeriesOT=$_GET['SeriesOT'];
	$sw=1;
}

$Cliente= isset($_GET['Cliente']) ? $_GET['Cliente'] : "";

$NomSP=(isset($_GET['TipoDespacho'])&&($_GET['TipoDespacho']=="2")) ? "sp_ConsultarDespachoRutasOT" : "sp_ConsultarDespachoRutas";

if($sw==1){
	$Param=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Cliente."'",
		"'".$Sede."'",
		"'".$Almacen."'",
		"'".$TipoLlamada."'",
		"'".$NombreEmpleado."'",
		"'".$EstadoActividad."'",
		"'".$SeriesOT."'",
		"'".$_SESSION['CodUser']."'"
	);
	$SQL=EjecutarSP($NomSP,$Param);
//	sqlsrv_next_result($SQL);
//	print_r($row);
	
	$SQL_Almacen=SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes','WhsCode, WhsName',"IdSucursal='".$Sede."' and IdTipoDocumento='17'","WhsCode, WhsName",'WhsName');
	$SQL_AlmacenDestino=SeleccionarGroupBy('uvw_tbl_SeriesSucursalesAlmacenes','ToWhsCode, ToWhsName',"IdSucursal='".$Sede."' and IdTipoDocumento='67'","ToWhsCode, ToWhsName",'ToWhsName');
	
	$ParamRes=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Cliente."'",
		"'".$Sede."'",
		"'".$Almacen."'",
		"'".$AlmacenDestino."'",
		"'".$TipoLlamada."'",
		"'".$NombreEmpleado."'",
		"'".$EstadoActividad."'",
		"'".$SeriesOT."'",
		"'".$_SESSION['CodUser']."'"
	);
	$SQL_Res=EjecutarSP('sp_ConsultarDespachoRutas_Resumen',$ParamRes);
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Despacho de servicios | <?php echo NOMBRE_PORTAL;?></title>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.ibox-title a{
		color: inherit !important;
	}
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
		
		$("#Sede").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=26&id="+document.getElementById('Sede').value+"&tdoc=191",
				success: function(response){
					$('#SeriesOT').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});	
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=27&id="+document.getElementById('Sede').value+"&todos=1",
				success: function(response){
					$('#Recursos').html(response).trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=33&id="+document.getElementById('Sede').value+"&tdoc=17&select=1",
				success: function(response){
					$('#Almacen').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					$('#Almacen').trigger('change');
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=33&id="+document.getElementById('Sede').value+"&tdoc=67&select=1&twhs=2",
				success: function(response){
					$('#AlmacenDestino').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					$('#AlmacenDestino').trigger('change');
				}
			});
		});		
	});
</script>
<script>
	var json=[];
//	var strJSON={};
	var cant=0;
function SeleccionarOT(DocNum){
	//var add=new Array(DocNum,AbsEntry,LineNum);
	var btnImprimir=document.getElementById('btnImprimir');
	var btnEntregas=document.getElementById('btnEntregas');
	var Check = document.getElementById('chkSelOT'+DocNum).checked;
	var sw=-1;
	
//	var JSONFile=document.getElementById('file');
	
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
//	strJSON=JSON.stringify(json);
	
	if(cant>0){
//		JSONFile.value=Base64.encode(strJSON);
		$("#btnImprimir").removeClass("disabled");
		$("#btnEntregas").removeClass("disabled");
	}else{
		$("#chkAll").prop("checked", false);
		$("#btnImprimir").addClass("disabled");
		$("#btnEntregas").addClass("disabled");
	}
	
	//console.log(json);
}
	
function SeleccionarTodos(){
	var Check = document.getElementById('chkAll').checked;
	if(Check==false){
		json=[];
		cant=0;
		$("#btnImprimir").addClass("disabled");
		$("#btnEntregas").addClass("disabled");
//		$(".chkSelOT").parents('tr').removeClass('bg-info');
	}
	$(".chkSelOT").prop("checked", Check);
	if(Check){
		$(".chkSelOT").trigger('change');
	}		
}

function EnviarDatos(){
	if(cant > 0){
		DescargarSAPDownload("sapdownload.php", "id="+btoa('17')+"&type="+btoa('2')+"&ObType="+btoa('191')+"&IdFrm="+btoa('1')+"&DocKey="+btoa(json), true)
	}
}
	
function ExportarEntregas(){
	if(cant > 0){
		DescargarSAPDownload("sapdownload.php", "id="+btoa('17')+"&type="+btoa('2')+"&ObType="+btoa('191')+"&IdFrm="+btoa('2')+"&DocKey="+btoa(json), true)
	}
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
                    <h2>Despacho de servicios</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                      	<li>
                            <a href="#">Servicios</a>
                        </li>
                        <li class="active">
                            <strong>Despacho de servicios</strong>
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
				  <form action="despacho_rutas.php" method="get" id="formBuscar" class="form-horizontal">
					<div class="form-group">
						<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Fechas <span class="text-danger">*</span></label>
						<div class="col-lg-3">
							<div class="input-daterange input-group" id="datepicker">
								<input name="FechaInicial" autocomplete="off" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>"/>
								<span class="input-group-addon">hasta</span>
								<input name="FechaFinal" autocomplete="off" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" />
							</div>
						</div>
						<label class="col-lg-1 control-label">Sede <span class="text-danger">*</span></label>
						<div class="col-lg-3">
							<select name="Sede" class="form-control select2" id="Sede" required>
								<option value="">(Todos)</option>
								 <?php while($row_Suc=sqlsrv_fetch_array($SQL_Suc)){?>
										<option value="<?php echo $row_Suc['IdSucursal'];?>" <?php if((isset($_GET['Sede'])&&($_GET['Sede']!=""))&&(strcmp($row_Suc['IdSucursal'],$_GET['Sede'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Suc['DeSucursal'];?></option>
								 <?php }?>
							</select>
						</div>
						<label class="col-lg-1 control-label">Serie OT</label>
						<div class="col-lg-3">
							<select name="SeriesOT" class="form-control" id="SeriesOT">
									<option value="">(Todos)</option>
							  <?php if(isset($_GET['Sede'])&&($_GET['Sede']!="")){
										while($row_SeriesOT=sqlsrv_fetch_array($SQL_SeriesOT)){?>
										<option value="<?php echo $row_SeriesOT['IdSeries'];?>" <?php if((isset($_GET['SeriesOT']))&&(strcmp($row_SeriesOT['IdSeries'],$_GET['SeriesOT'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_SeriesOT['DeSeries'];?></option>
							  <?php 	}
									}?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Cliente</label>
						<div class="col-lg-3">
							<input name="Cliente" type="hidden" id="Cliente" value="<?php if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){ echo $_GET['Cliente'];}?>">
							<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Ingrese para buscar..." value="<?php if(isset($_GET['NombreCliente'])&&($_GET['NombreCliente']!="")){ echo $_GET['NombreCliente'];}?>">
						</div>
						<label class="col-lg-1 control-label">Almacén origen <span class="text-danger">*</span></label>
						<div class="col-lg-3">
							<select name="Almacen" class="form-control" id="Almacen" required>
								<option value="">Seleccione...</option>
								<?php
								  if($sw==1){
								  while($row_Almacen=sqlsrv_fetch_array($SQL_Almacen)){?>							
										<option value="<?php echo $row_Almacen['WhsCode'];?>" <?php if((isset($_GET['Almacen'])&&($_GET['Almacen'])!="")&&(strcmp($row_Almacen['WhsCode'],$_GET['Almacen'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Almacen['WhsName'];?></option>
								  <?php }}?>
							</select>
						</div>
						<label class="col-lg-1 control-label">Técnico</label>
						<div class="col-lg-3">
							<select name="Recursos[]" class="form-control select2" multiple id="Recursos" data-placeholder="(Todos)">
								 <?php
								  if(isset($_GET['Sede'])){ $j=0; 
								  while($row_Recursos=sqlsrv_fetch_array($SQL_Recursos)){?>							
										<option value="<?php echo $row_Recursos['ID_Empleado'];?>" <?php if((isset($_GET['Recursos'][$j])&&($_GET['Recursos'][$j])!="")&&(strcmp($row_Recursos['ID_Empleado'],$_GET['Recursos'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_Recursos['NombreEmpleado'];?></option>
								  <?php }}?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Estado actividad</label>
						<div class="col-lg-3">
							<select name="EstadoActividad[]" class="form-control select2" multiple id="EstadoActividad" data-placeholder="(Todos)">
							  <?php $j=0; 
								while($row_EstadoActividad=sqlsrv_fetch_array($SQL_EstadoActividad)){?>
									<option value="<?php echo $row_EstadoActividad['ID_TipoEstadoServicio'];?>" <?php if((isset($_GET['EstadoActividad'][$j]))&&(strcmp($row_EstadoActividad['ID_TipoEstadoServicio'],$_GET['EstadoActividad'][$j])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoActividad['DE_TipoEstadoServicio'];?></option>
							  <?php }?>
							</select>
						</div>
						<label class="col-lg-1 control-label">Almacén destino <span class="text-danger">*</span></label>
						<div class="col-lg-3">
							<select name="AlmacenDestino" class="form-control" id="AlmacenDestino" required>
								<option value="">Seleccione...</option>
								<?php
								  if($sw==1){
								  while($row_AlmacenDestino=sqlsrv_fetch_array($SQL_AlmacenDestino)){?>							
										<option value="<?php echo $row_AlmacenDestino['ToWhsCode'];?>" <?php if((isset($_GET['AlmacenDestino'])&&($_GET['AlmacenDestino'])!="")&&(strcmp($row_AlmacenDestino['ToWhsCode'],$_GET['AlmacenDestino'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_AlmacenDestino['ToWhsName'];?></option>
								  <?php }}?>
							</select>
						</div>
						<label class="col-lg-1 control-label">Basado en <span class="text-danger">*</span></label>
						<div class="col-lg-3">
							<select name="TipoDespacho" class="form-control" id="TipoDespacho" required>
								<option value="1" <?php if(isset($_GET['TipoDespacho'])&&($_GET['TipoDespacho']=="1")){ echo "selected=\"selected\"";}?>>Actividades</option>
								<option value="2" <?php if(isset($_GET['TipoDespacho'])&&($_GET['TipoDespacho']=="2")){ echo "selected=\"selected\"";}?>>Llamadas de servicios</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Tipo llamada</label>
						<div class="col-lg-3">
							<select name="TipoLlamada" class="form-control" id="TipoLlamada">
								<option value="">(Todos)</option>
							  <?php
								while($row_TipoLlamadas=sqlsrv_fetch_array($SQL_TipoLlamadas)){?>										
									<option value="<?php echo $row_TipoLlamadas['IdTipoLlamada'];?>" <?php if((isset($_GET['TipoLlamada']))&&(strcmp($row_TipoLlamadas['IdTipoLlamada'],$_GET['TipoLlamada'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoLlamadas['DeTipoLlamada'];?></option>
							  <?php }?>
							</select>
						</div>
						<div class="col-lg-8 pull-right">
							<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
						</div>	
					</div>		
					<div class="form-group">
						<div class="col-lg-4">
							 <?php if($sw==1){?>
							<a href="exportar_excel.php?exp=12&Cons=<?php echo base64_encode(implode(",",$Param));?>&sp=<?php echo base64_encode('sp_ConsultarDespachoRutas');?>">
								<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
							</a>
							<?php }?>
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
					<div class="ibox">
						<div class="ibox-title bg-success">
							<h5><i class="fa fa-check-square-o"></i> Resultados</h5>
							 <a class="collapse-link pull-right">
								<i class="fa fa-chevron-up"></i>
							</a>	
						</div>		
						<div class="ibox-content">	
							<div class="row m-b-md">
								<div class="col-lg-12">
									<a href="sapdownload.php?id=<?php echo base64_encode('18');?>&type=<?php echo base64_encode('2');?>&FechaInicial=<?php echo base64_encode(FormatoFecha($FechaInicial));?>&FechaFinal=<?php echo base64_encode(FormatoFecha($FechaFinal));?>&Sede=<?php echo base64_encode($Sede);?>&Almacen=<?php echo base64_encode($Almacen);?>&TipoLlamada=<?php echo base64_encode($TipoLlamada);?>&Tecnicos=<?php echo base64_encode($NombreEmpleado);?>" target="_blank" class="btn btn-warning disabled"><i class="fa fa-download"></i> Descargar rutas</a>
									<button class="pull-right btn btn-danger disabled" id="btnImprimir" name="btnImprimir" onClick="EnviarDatos();"><i class="fa fa-file-pdf-o"></i> Exportar rutas</button>
									<button class="pull-right btn btn-success m-r-xs disabled" id="btnEntregas" name="btnEntregas" onClick="ExportarEntregas();"><i class="fa fa-file-pdf-o"></i> Exportar entregas</button>			
								</div>
							</div>
							<div class="table-responsive">
								<table class="table table-bordered table-hover table-striped dataTables-example">
								<thead>
								<tr>
									<th>Llamada de servicio</th>
									<th>Serie</th>
									<th>Tipo llamada</th>
									<th>Cliente</th>
									<th>Sucursal cliente</th> 
									<th>Fecha llamada</th>
									<th>Fecha actividad</th>	
									<th>Estado actividad</th>
									<th>Técnico</th>
									<th>Almacen</th>
									<th>Orden de venta</th>
									<th>Entregas</th>
									<th>Devolución</th>
									<th>Seleccionar <div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div></th>
								</tr>
								</thead>
								<tbody>
								<?php $i=0;
									while($row=sql_fetch_array($SQL)){?>
									 <tr id="tr_<?php echo $i;?>">
										<td><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_LlamadaServicio']);?>&tl=1" target="_blank"><?php echo $row['DocNum'];?></a></td>
										<td><?php echo $row['NombreSerie'];?></td>
										<td><?php echo $row['TipoOrdenServicio'];?></td>
										<td><?php echo $row['NombreCliente'];?></td>
										<td><?php echo $row['NombreSucursalCliente'];?></td>
										<td><?php echo $row['FechaCreacionLLamada'];?></td>
										<td><?php echo is_object($row['FechaActividad']) ? $row['FechaActividad']->format('Y-m-d H:i') : $row['FechaActividad'];?></td>
										<td><?php echo $row['EstadoActividad'];?></td>
										<td><?php echo $row['NombreEmpleadoActividad'];?></td>
										<td><?php echo $row['Almacen'];?></td>
										<td><?php echo $row['OrdenVenta'];?></td>
										<td><?php echo $row['Entregas'];?></td>
										<td><?php echo $row['Devolucion'];?></td>
										<td>
											<div class="checkbox checkbox-success">
												<input type="checkbox" class="chkSelOT" id="chkSelOT<?php echo $row['ID_LlamadaServicio'];?>" value="" onChange="SeleccionarOT('<?php echo $row['ID_LlamadaServicio'];?>');" aria-label="Single checkbox One"><label></label>
											</div>
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
          </div>
		<br>
		<div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>	
					<div class="row">
						<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-cubes"></i> Resumen de artículos a utilizar</h3></label>
					</div>
					<div class="row m-b-md">
						<div class="col-lg-6">
							<div class="alert alert-danger h5">
                                <i class="fa fa-info-circle"></i> El stock de los almacenes muestra las cantidades a <strong>fecha de hoy.</strong>
                            </div>
						</div>
						<div class="col-lg-6">
							<div class="btn-group pull-right">
								<button data-toggle="dropdown" class="btn btn-success dropdown-toggle"><i class="fa fa-mail-forward"></i> Copiar a <i class="fa fa-caret-down"></i></button>
								<ul class="dropdown-menu">
									<li><a class="dropdown-item" target="_blank" href="traslado_inventario.php?dt_DR=1&Cardcode=<?php echo base64_encode(ObtenerVariable("NITClienteDefault"));?>&AlmacenDestino=<?php echo base64_encode($AlmacenDestino);?>">Traslado de inventario</a></li>
								</ul>
							</div>
						</div>
					</div>
					<div class="table-responsive">
						<table class="table table-bordered table-hover table-striped dataTables-Resumen">
						<thead>
						<tr>
							<th>#</th>
							<th>Código</th>
							<th>Nombre artículo</th>
							<th>Unidad</th>
							<th>Cantidad inicial</th>
							<th>Cantidad pendiente</th>
							<th>Stock almacén origen</th>
							<th>Stock almacén destino</th>
							<th>Faltante de stock destino</th>
							<th>Tipo artículo</th>
						</tr>
						</thead>
						<tbody>
						<?php $i=1;
						 	while($row_Res=sql_fetch_array($SQL_Res)){?>
							<tr>
								<td><?php echo $i;?></td>
								<td><?php echo $row_Res['ItemCode'];?></td>
								<td><?php echo $row_Res['ItemName'];?></td>
								<td><?php echo $row_Res['Unidad'];?></td>
								<td><?php echo number_format($row_Res['Cantidad'],2);?></td>
								<td><?php echo number_format($row_Res['CantPendiente'],2);?></td>
								<td><?php echo number_format($row_Res['StockAlmacenOrigen'],2);?></td>
								<td><?php echo number_format($row_Res['StockAlmacenDestino'],2);?></td>
								<td><?php echo number_format($row_Res['Pendiente'],2);?></td>
								<td><?php echo $row_Res['DE_ItemType'];?></td>
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
				todayHighlight: true,
				format: 'yyyy-mm-dd'
            });
			 $('#FechaFinal').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				todayHighlight: true,
				format: 'yyyy-mm-dd'
            }); 
			$(".select2").select2();
			
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
				pageLength: 50,
				lengthMenu: [[10, 25, 50, 100, 150, 200, -1], [10, 25, 50, 100, 150, 200, "Todos"]],
				order: [[ 8, "asc" ]],
				rowGroup: {
					dataSrc: 8
				},
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
			
			$('.dataTables-Resumen').DataTable({
                dom: '<"html5buttons"B>lTfgitp',
				pageLength: 25,
				//order: [[ 1, "asc" ]],
				rowGroup: {
					dataSrc: 9
				},
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