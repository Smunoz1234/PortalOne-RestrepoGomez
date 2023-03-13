<?php require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
PermitirAcceso(408);
$sw=0;
$Serie="";
$Cliente="";
$Sucursal="";
$EstadoLlamada="";
$Facturado="";
//$Zona="";
$ServicioLlamada="";
$MetodoAplicacion="";
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
$Cliente= isset($_GET['Cliente']) ? $_GET['Cliente'] : "";
$Sucursal= isset($_GET['Sucursal']) ? $_GET['Sucursal'] : "";
$Serie= isset($_GET['Series']) ? $_GET['Series'] : "";
$EstadoLlamada= isset($_GET['EstadoLlamada']) ? $_GET['EstadoLlamada'] : "";
$Facturado= isset($_GET['Facturado']) ? $_GET['Facturado'] : "";
//$Zona= isset($_GET['Zona']) ? $_GET['Zona'] : "";
$ServicioLlamada= isset($_GET['Servicio']) ? $_GET['Servicio'] : "";
$MetodoAplicacion= isset($_GET['MetodoAplicacion']) ? $_GET['MetodoAplicacion'] : "";
$TipoLlamada= isset($_GET['TipoLlamada']) ? implode(",",$_GET['TipoLlamada']) : "";
$EstadoServicio= isset($_GET['EstadoServicio']) ? implode(",",$_GET['EstadoServicio']) : "";
$FechaInicialActividad= isset($_GET['FechaInicialActividad']) ? $_GET['FechaInicialActividad'] : "";
$FechaFinalActividad= isset($_GET['FechaFinalActividad']) ? $_GET['FechaFinalActividad'] : "";

if($sw==1){
	$Param=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".FormatoFecha($FechaInicialActividad)."'",
		"'".FormatoFecha($FechaFinalActividad)."'",
		"'".$Cliente."'",
		"'".$Sucursal."'",
		"'".$EstadoLlamada."'",
		"'".$Serie."'",
		"'".$ServicioLlamada."'",
		"'".$TipoLlamada."'",
		"'".$EstadoServicio."'",
		"'".$Facturado."'",
		"'".$MetodoAplicacion."'",
		"'".$_SESSION['CodUser']."'"
	);
	$SQL=EjecutarSP('sp_ConsultarFacturacionOT',$Param);
	
	//VTA para seleccionar
	$SQL_VTAOpcional=Seleccionar('uvw_Sap_tbl_ArticulosVenta','ItemCode, ItemName',"IdTipoListaArticulo=2",'ItemName');
	
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Facturación de OT | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.ibox-title a{
		color: inherit !important;
	}
	.collapse-link:hover{
		cursor: pointer;
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
		$("#NombreClienteFactura").change(function(){
			var NomCliente=document.getElementById("NombreClienteFactura");
			var Cliente=document.getElementById("CodClienteFactura");
			if(NomCliente.value==""){
				Cliente.value="";
				$("#CodClienteFactura").trigger("change");
			}	
		});
	});
</script>
<script>
	var json=[];
	var cant=0;
function SeleccionarOT(Num,id_tr, vta){
	var OTSel=document.getElementById("OTSel");
	var VTAOpt=document.getElementById("VTAOpcional");
	var DVtr=document.getElementById("DVtr");
	var OT=OTSel.value.indexOf(Num);
	var TR=DVtr.value.indexOf(id_tr);
	var Check = document.getElementById('chkSelOT'+Num).checked;
	
	if(vta==""){
		if(VTAOpt.value==""){
			Swal.fire({
				title: '¡Lo sentimos!',
				text: 'Debe seleccionar un VTA opcional para poder agregar esta OT',
				icon: 'error'
			});
			document.getElementById('chkSelOT'+Num).checked=false;
			PonerQuitarClase('chkSelOT'+Num,2);
			return;
		}
	}
	    
	if(Check){
		PonerQuitarClase("chkSelOT"+Num);
    }else{
		PonerQuitarClase("chkSelOT"+Num,2);
	}
	
	if(OT<0){
		OTSel.value=OTSel.value + Num + "[*]";
	}else{
		var tmp=OTSel.value.replace(Num+"[*]","");
		OTSel.value=tmp;
	}
	
	if(TR<0){
		DVtr.value=DVtr.value + id_tr + "[*]";
	}else{
		var tmp=DVtr.value.replace(id_tr+"[*]","");
		DVtr.value=tmp;
	}
	
	if(OTSel.value==""){
		document.getElementById('btnInd').disabled=true;
		document.getElementById('btnGrp').disabled=true;
	}else{
		document.getElementById('btnInd').disabled=false;
		document.getElementById('btnGrp').disabled=false;
	}
	
	if ($(".chkSelOT").length == $(".chkSelOT:checked").length){  
		$("#chkAll").prop("checked", true);  
	}else{  
		$("#chkAll").prop("checked", false); 
	}  
}

function EliminarFilas(){
	var DVtr=document.getElementById("DVtr");
	var arrayID=DVtr.value.split('[*]');
	for(var i=0; i < arrayID.length; i++){
		if(arrayID[i]!=""){
			$("#tr_"+arrayID[i]).remove();
		}
	}	
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
	
function SeleccionarZIP(DocNum, AbsEntry, LineNum){
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
	}else if(Check){
		json.push({DocNum,AbsEntry,LineNum});
		cant++;
	}
	
	strJSON=JSON.stringify(json);
	
	if(cant>0){
		JSONFile.value=Base64.encode(strJSON);
		//btnZIP.setAttribute('href',"attachdownload.php?file="+Base64.encode(strJSON)+"&line=&zip=<?php //echo base64_encode('1');?>");
		$("#btnZIP").removeClass("disabled");
	}else{
		$("#chkAll").prop("checked", false);
		$("#btnZIP").addClass("disabled");
	}
	
	//console.log(json);
}
	
function SeleccionarTodos(){
	var Check = document.getElementById('chkAll').checked;
	if(Check==false){
		document.getElementById("OTSel").value='';
		DVtr=document.getElementById("DVtr").value='';
		json=[];
		cant=0;
		$("#btnZIP").addClass("disabled");
		document.getElementById('btnInd').disabled=true;
		document.getElementById('btnGrp').disabled=true;
		$(".chkSelOT").parents('tr').removeClass('bg-success');
	}
	$(".chkSelOT").prop("checked", Check);
	if(Check){
		$(".chkSelOT").trigger('change');
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
                    <h2>Facturación de OT</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Ventas - Clientes</a>
                        </li>
                        <li class="active">
                            <strong>Facturación de OT</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
			 <!-- Inicio, myModal -->
			<div class="modal inmodal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="TituloModal"></h4>
						</div>
						<div class="modal-body" id="ContenidoModal"></div>
						<div class="modal-footer">
							<button type="button" class="btn btn-success m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
						</div>
					</div>
				</div>
			</div>
			<!-- Fin, myModal -->
			
             <div class="row">
				<div class="col-lg-12">
			     <div class="ibox-content">
				   <?php include("includes/spinner.php"); ?>
				   <form action="facturacion_orden_servicio.php" method="get" id="formBuscar" class="form-horizontal">
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
					    </div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Cliente <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<input name="Cliente" type="hidden" id="Cliente" value="<?php if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){ echo $_GET['Cliente'];}?>">
								<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Ingrese para buscar..." value="<?php if(isset($_GET['NombreCliente'])&&($_GET['NombreCliente']!="")){ echo $_GET['NombreCliente'];}?>" required>
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
							<label class="col-lg-1 control-label">Fechas de la OT</label>
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
							<label class="col-lg-1 control-label">Método de aplicación</label>
							<div class="col-lg-3">
								<input name="MetodoAplicacion" type="text" class="form-control" id="MetodoAplicacion" maxlength="50" value="<?php if(isset($_GET['MetodoAplicacion'])&&($_GET['MetodoAplicacion']!="")){ echo $_GET['MetodoAplicacion'];}?>">
							</div>							
							<label class="col-lg-1 control-label">Fechas de la actividad</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicialActividad" type="text" class="input-sm form-control" id="FechaInicialActividad" placeholder="Fecha inicial" value="<?php echo $FechaInicialActividad;?>"/>
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinalActividad" type="text" class="input-sm form-control" id="FechaFinalActividad" placeholder="Fecha final" value="<?php echo $FechaFinalActividad;?>" />
								</div>
							</div>
						</div>
						<div class="form-group">
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
							<label class="col-lg-1 control-label">Tipo llamada</label>
							<div class="col-lg-3">
								<select data-placeholder="(Todos)" name="TipoLlamada[]" class="form-control chosen-select" id="TipoLlamada" multiple>
								  <?php $j=0;
									while($row_TipoLlamadas=sqlsrv_fetch_array($SQL_TipoLlamadas)){?>
										<option value="<?php echo $row_TipoLlamadas['IdTipoLlamada'];?>" <?php if((isset($_GET['TipoLlamada'][$j])&&($_GET['TipoLlamada'][$j]!=""))&&(strcmp($row_TipoLlamadas['IdTipoLlamada'],$_GET['TipoLlamada'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_TipoLlamadas['DeTipoLlamada'];?></option>
								  <?php }?>
								</select>
							</div>							
						</div>
						<div class="form-group">		
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
					<div class="row p-md">
						<div class="form-group">
							<div class="col-lg-3">
								<label class="control-label">VTA opcional</label>
								<select name="VTAOpcional" class="form-control select2" id="VTAOpcional">
										<option value="">Seleccione...</option>
								<?php while($row_VTAOpcional=sqlsrv_fetch_array($SQL_VTAOpcional)){?>
										<option value="<?php echo $row_VTAOpcional['ItemCode'];?>"><?php echo $row_VTAOpcional['ItemCode'].": ".$row_VTAOpcional['ItemName'];?></option>
								<?php }?>
								</select>
							</div>		
							<div class="col-lg-7">
								<label class="control-label">Facturar a nombre de</label>
								<input name="CodClienteFactura" type="hidden" id="CodClienteFactura" value="">							
								<input name="NombreClienteFactura" type="text" class="form-control" id="NombreClienteFactura" placeholder="Digite para buscar..." value="">
							</div>
						</div>
					</div>
					<div class="row p-sm">
						<div class="table-responsive">
							<table class="table table-bordered dataTables-example">
							<thead>
							<tr>
								<th>Llamada servicio</th>
								<th>Serie</th>
								<th>Lista de materiales</th>
								<th>VTA para factura</th>
								<th>Tipo llamada</th>
								<th>Sucursal</th>   
								<th>Servicio</th>   
								<th>Cant Valor Art.</th>
								<th>Fecha creación</th>
								<th>Fecha cierre</th>
								<th>Fecha Últ. Act</th>
								<th>Estado</th>
								<th>Estado de servicio</th>
								<th>Facturado</th>
								<th>Anexo OT</th>
								<th>Seleccionar <div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div></th>
							</tr>
							</thead>
							<tbody>
							<?php $i=0;
								while($row=sql_fetch_array($SQL)){ 
									$Icon=IconAttach($row['ExtAnexoLlamada']);?>
								<tr id="tr_<?php echo $i;?>">
									<td><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_LlamadaServicio']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('facturacion_orden_servicio.php');?>&tl=1" target="_blank"><?php echo $row['DocNum'];?></a></td>	
									<td><?php echo $row['SeriesName'];?></td>
									<td><a href="articulos.php?id=<?php echo base64_encode($row['IdArticuloLlamada']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('facturacion_orden_servicio.php');?>&tl=1" target="_blank" title="<?php echo $row['DeArticuloLlamada'];?>"><?php echo $row['IdArticuloLlamada'];?></a></td>
									<td><a href="articulos.php?id=<?php echo base64_encode($row['IdVTAFactura']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('facturacion_orden_servicio.php');?>&tl=1" target="_blank" title="<?php echo $row['DeVTAFactura'];?>"><?php echo $row['IdVTAFactura'];?></a></td>
									<td><?php echo $row['DeTipoLlamada'];?></td>
									<td><?php echo $row['NombreSucursal'];?></td>
									<td><?php echo $row['CDU_Servicios'];?></td>
									<td><?php echo 'Cant:'.number_format($row['CDU_CantArticulo'],0).' - Valor:'.number_format($row['CDU_PrecioArticulo'],0);?></td>
									<td><?php echo $row['FechaCreacionLLamada'];?></td>
									<td><?php echo $row['FechaCierreLLamada'];?></td>
									<td><?php echo $row['FechaInicioActividad'];?></td>
									<td><span <?php if($row['IdEstadoLlamada']=='-3'){echo "class='label label-info'";}elseif($row['IdEstadoLlamada']=='-2'){echo "class='label label-warning'";}else{echo "class='label label-danger'";}?>><?php echo $row['DeEstadoLlamada'];?></span></td>	
									<td><span <?php if($row['CDU_EstadoServicio']=='0'){echo "class='label label-warning'";}elseif($row['CDU_EstadoServicio']=='1'){echo "class='label label-primary'";}else{echo "class='label label-danger'";}?>><?php echo $row['DeEstadoServicio'];?></span></td>
									<td><?php if($row['Facturado']=="SI"){?><a href="factura_venta.php?id=<?php echo base64_encode($row['DocEntryFactura']);?>&id_portal=<?php echo base64_encode($row['DocPortalFactura']);?>&tl=1" target="_blank"><?php echo $row['DocNumFactura'];?></a><?php }else{ echo "NO";}?></td>
									<td><?php if($row['DeAnexoLlamada']!=""){?><a href="attachdownload.php?file=<?php echo base64_encode($row['IdAnexoLlamada']);?>&line=<?php echo base64_encode($row['LineNumAnexoLlamada']);?>" target="_blank" title="Descargar archivo" class="btn-link btn-xs"><i class="<?php echo $Icon;?>"></i> <?php echo $row['DeAnexoLlamada'];?></a><?php }?></td>
									<td>
										<?php if(($row['TipoLlamadaFacturable']=="SI")&&($row['EstadoServicioFacturable']=="SI")&&($row['Facturado']=="NO")){if(($row['IdTipoLlamada']=="1")&&($row['IdVTAFactura']=="")){}else{?>
											<div class="checkbox checkbox-success">
												<input type="checkbox" class="chkSelOT" id="chkSelOT<?php echo $row['DocNum'];?>" value="" onChange="SeleccionarOT('<?php echo $row['DocNum'];?>','<?php echo $i;?>','<?php echo $row['IdVTAFactura'];?>');SeleccionarZIP('<?php echo $row['DocNum'];?>','<?php echo $row['IdAnexoLlamada'];?>','<?php echo $row['LineNumAnexoLlamada'];?>');" aria-label="Single checkbox One"><label></label>
											</div>
										<?php }}?>
									</td>
								</tr>
							<?php $i++;}?>
							</tbody>
							</table>
						</div>
					</div>
					<div class="row p-md">
						<div class="col-lg-4">
							<input type="hidden" id="OTSel" name="OTSel" value="" />
							<input type="hidden" id="DVtr" name="DVtr" value="" />
						</div>
						<div class="col-lg-4">
							<button type="submit" class="btn btn-primary disabled" id="btnZIP" name="btnZIP" form="frmDownload"><i class="fa fa-file-zip-o"></i> Exportar en ZIP</button>
							<div class="btn-group">
								<button data-toggle="dropdown" type="button" disabled="" class="btn btn-success dropdown-toggle" id="btnInd" name="btnInd"><i class="fa fa-angle-down"></i> Individual</button>
								<ul class="dropdown-menu">
									<li><a class="dropdown-item" href="#" id="btnIndGrpSuc" title="Agrupar las sucursales del cliente y suma la cantidad de OT y valores del VTA en la misma fila">Agrupar por sucursal</a></li>
									<li><a class="dropdown-item" href="#" id="btnIndBySuc" title="No agrupa las sucursales, sino que agrupa por los VTA, si estos se repiten, y suma la cantidad de OT y valores del VTA en la misma fila"><strong>NO</strong> Agrupar por sucursal</a></li>
								</ul>
							</div>	
							<div class="btn-group">
								<button data-toggle="dropdown" type="button" disabled="" class="btn btn-success dropdown-toggle" id="btnGrp" name="btnGrp"><i class="fa fa-angle-double-down"></i> Agrupado</button>
								<ul class="dropdown-menu">
									<li><a class="dropdown-item" href="#" id="btnGrpGrpSuc" title="Agrupar las sucursales del cliente en un mismo VTA y pone en la cantidad 1 y el valor único del VTA en la misma fila">Agrupar por sucursal</a></li>
									<li><a class="dropdown-item" href="#" id="btnGrpBySuc" title="No agrupa las sucursales, sino que coloca todos los OT agrupadas, con la cantidad 1 y el valor único del VTA en la misma fila"><strong>NO</strong> Agrupar por sucursal</a></li>
								</ul>
							</div>						
						</div>
						<div class="col-lg-4">
						<form id="frmDownload" name="frmDownload" action="attachdownload.php" method="post" target="_blank">
							<input type="hidden" id="file" name="file" value="" />
							<input type="hidden" id="zip" name="zip" value="<?php echo base64_encode('1'); ?>" />
						</form>		
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
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Detalle de factura</h3></label>
						</div>
						<div class="row m-b-md">
							<div class="col-lg-12">
								<button class="pull-right btn btn-primary" id="btnPreCostos" name="btnPreCostos" onClick="MostrarCostos('<?php echo $Cliente; ?>');"><i class="fa fa-money"></i> Previsualizar costos</button>			
							</div>
						</div>
						<div class="tabs-container">  
							<ul class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Contenido</a></li>		
								<li><span class="TimeAct"><div id="TimeAct">&nbsp;</div></span></li>
								<span class="TotalItems"><strong>Total Items:</strong>&nbsp;<input type="text" name="TotalItems" id="TotalItems" class="txtLimpio" value="0" size="1" readonly></span>
							</ul>
							<div class="tab-content">
								<div id="tab-1" class="tab-pane active">
									<iframe id="DataGrid" name="DataGrid" style="border: 0;" width="100%" height="300" src="detalle_facturacion_orden_servicio.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode=<?php echo $_GET['Cliente'];?>"></iframe>
								</div>						
							</div>					
						</div>
						<div class="row p-md">
							<div class="col-lg-12">
								<div class="btn-group pull-right">
									<button data-toggle="dropdown" class="btn btn-success dropdown-toggle"><i class="fa fa-mail-forward"></i> Copiar a <i class="fa fa-caret-down"></i></button>
									<ul class="dropdown-menu">
										<li><a class="alkin dropdown-item" href="#" onClick="CopiarToFactura(1);">Factura de venta (copiar adjuntos)</a></li>
										<li><a class="alkin dropdown-item" href="#" onClick="CopiarToFactura(0);">Factura de venta (<strong>NO</strong> copiar adjuntos)</a></li>
										<li class="dropdown-divider"></li>
										<li><a class="alkin dropdown-item" href="#" onClick="CopiarToFactura(1,2);">Orden de venta (copiar adjuntos)</a></li>
										<li><a class="alkin dropdown-item" href="#" onClick="CopiarToFactura(0,2);">Orden de venta (<strong>NO</strong> copiar adjuntos)</a></li>
									</ul>
								</div>
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
			
			 var options2 = {
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
						  var value = $("#NombreClienteFactura").getSelectedItemData().CodigoCliente;
						  $("#CodClienteFactura").val(value).trigger("change");
					  }
				  }
			 };
		  $("#NombreClienteFactura").easyAutocomplete(options2);
			
			$("#btnIndGrpSuc").click(function(){
				EnviarDatos(1,1);
			});
			
			$("#btnIndBySuc").click(function(){
				EnviarDatos(1);
			});
			
			$("#btnGrpGrpSuc").click(function(){
				EnviarDatos(2,1);
			});

			$("#btnGrpBySuc").click(function(){
				EnviarDatos(2);
			});
			
            $('.dataTables-example').DataTable({
                pageLength: 50,
				lengthMenu: [[10, 25, 50, 100, 150, 200, -1], [10, 25, 50, 100, 150, 200, "Todos"]],
				order: [[ 0, "desc" ]],
				ordering:  false,
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
function EnviarDatos(metodo, grpsuc=0){
	$('.ibox-content').toggleClass('sk-loading',true);
	var Cliente=document.getElementById("Cliente");
	var VTAOpt=document.getElementById("VTAOpcional");
	var DataGrid=document.getElementById("DataGrid");
	var OTSel=document.getElementById("OTSel");
	var DVtr=document.getElementById("DVtr");	
	if(Cliente.value!=""&&OTSel.value!=""){
		/*if(metodo==2){
			if(VTAOpt.value==""){
				swal({
					title: '¡Lo sentimos!',
					text: 'Debe seleccionar un VTA opcional en el cual se agruparán las OT',
					type: 'error'
				});
				$('.ibox-content').toggleClass('sk-loading',false);
				return;
			}
		}*/
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=14&cardcode="+Cliente.value+"&metodo="+metodo+"&otsel="+OTSel.value+"&vtaopt="+VTAOpt.value+"&grpsuc="+grpsuc,
			success: function(response){
				if(response!="Error"){
					window.parent.document.getElementById('TimeAct').innerHTML="<strong>Actualizado:</strong> "+response;
					EliminarFilas();
					OTSel.value="";
					DVtr.value="";
					DataGrid.src="detalle_facturacion_orden_servicio.php?id=0&type=1&usr=<?php echo $_SESSION['CodUser'];?>&cardcode="+Cliente.value;
					document.getElementById('btnInd').disabled=true;
					document.getElementById('btnGrp').disabled=true;
					$("#btnZIP").addClass("disabled");
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			}
		});
	}else{
		Swal.fire({
			title: '¡Lo sentimos!',
			text: 'Debe seleccionar un cliente y las OT para agregar al detalle',
			icon: 'error'
		});
		$('.ibox-content').toggleClass('sk-loading',false);
	}
}

function CopiarToFactura(adj=1,dest=1){
	let CodClienteFactura = document.getElementById('CodClienteFactura');
	let docDest="factura_venta.php";
	if(dest==2){
		docDest="orden_venta.php";
	}
	
	if(CodClienteFactura.value!=""){
		window.location = docDest+"?dt_FC=1&Cardcode=<?php echo base64_encode($_GET['Cliente']);?>&Sucursal=<?php echo base64_encode($_GET['Sucursal']);?>&adt="+btoa(adj)+"&CodFactura="+btoa(CodClienteFactura.value);
	}else{
		window.location = docDest+"?dt_FC=1&Cardcode=<?php echo base64_encode($_GET['Cliente']);?>&Sucursal=<?php echo base64_encode($_GET['Sucursal']);?>&adt="+btoa(adj)+"&CodFactura=";
	}	
}

function MostrarCostos(cardcode){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		async: false,
		url: "md_articulos_documentos.php",
		data:{
			pre:1,
			CardCode:cardcode
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#TituloModal').html('Costos asociados');
			$('#myModal').modal("show");
		}
	});
}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>