<?php require_once("includes/conexion.php");
PermitirAcceso(319);

$sw=0;
$NombreEmpleado="";
$Sede="";
$NameFirma="";

//Sucursales
$ParamSucursal=array(
	"'".$_SESSION['CodUser']."'"
);
$SQL_Suc=EjecutarSP('sp_ConsultarSucursalesUsuario',$ParamSucursal);

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

//Filtros
if(isset($_GET['Sede'])&&$_GET['Sede']!=""){
	//Tecnicos
	$ParamTecnicos=array(
		"'".$_SESSION['CodUser']."'",
		"'".$_GET['Sede']."'"
	);
	$SQL_Recursos=EjecutarSP('sp_ConsultarTecnicos',$ParamTecnicos);

	$Sede=$_GET['Sede'];
	$sw=1;
}

if(isset($_GET['Recursos'])&&$_GET['Recursos']!=""){
	$NombreEmpleado=implode(",",$_GET['Recursos']);
	$sw=1;
}

if($sw==1){
	$Param=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Sede."'",
		"'".$NombreEmpleado."'",
		"'".$_SESSION['CodUser']."'"
	);
	$SQL=EjecutarSP('sp_ConsultarDespachoLote',$Param);
//	sqlsrv_next_result($SQL);
//	print_r($row);
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Despachos en lote | <?php echo NOMBRE_PORTAL;?></title>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
.select2-container{ width: 100% !important; }
.ibox-title a{
		color: inherit !important;
}
.collapse-link:hover{
	cursor: pointer;
}
</style>
<script type="text/javascript">
	$(document).ready(function() {
		$("#Sede").change(function(){
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=27&id="+document.getElementById('Sede').value+"&todos=1",
				success: function(response){
					$('#Recursos').html(response).trigger('change');;
				}
			});
		});
		
	});
</script>
<script>
function EjecutarProceso(Tipo){
$('.ibox-content').toggleClass('sk-loading',true);
	var Evento = document.getElementById("IdEvento").value;
	var FechaInicial = document.getElementById("FechaInicial").value;
	var FechaFinal = document.getElementById("FechaFinal").value;
	var Anno = document.getElementById("Anno").value;
	var Cliente = document.getElementById("Cliente").value;
	var Sucursal = document.getElementById("Sucursal").value;
	var SeriesOT = document.getElementById("SeriesOT").value;
	var SeriesOV = document.getElementById("SeriesOV").value;
	var DGDetalle = document.getElementById("DGDetalle");
	
	$.ajax({
		url:"ajx_ejecutar_json.php",
		data:{
			type:3,
			Evento:Evento,
			FechaInicial:FechaInicial,
			FechaFinal:FechaFinal,
			Anno:Anno,
			Cliente:Cliente,
			Sucursal:Sucursal,
			SeriesOT:SeriesOT,
			SeriesOV:SeriesOV,
			Tipo:Tipo
		},
		dataType:'json',
		success: function(data){
			if(data.Estado==1){
				$("#UltEjecucion").html(MostrarFechaHora());				
				DGDetalle.src="detalle_creacion_ot_lote.php";				
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
	
function AbrirFirma(IDCampo){
	var posicion_x;
	var posicion_y;
	posicion_x=(screen.width/2)-(1200/2);  
	posicion_y=(screen.height/2)-(500/2);
	self.name='opener';
	remote=open('popup_firma.php?id='+Base64.encode(IDCampo),'remote',"width=1200,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
	remote.focus();
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
                    <h2>Despachos en lote</h2>
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
                            <strong>Despachos en lote</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    	<div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
					<div class="ibox">
						<div class="ibox-title bg-success">
							<h5 class="collapse-link"><i class="fa fa-filter"></i> Datos para filtrar</h5>
							<a class="collapse-link pull-right">
								<i class="fa fa-chevron-up"></i>
							</a>	
						</div>
						<div class="ibox-content">
							  <form action="despacho_lote.php" method="get" id="formBuscar" class="form-horizontal">
								  <div class="form-group">
									<label class="col-lg-1 control-label">Fechas</label>
									<div class="col-lg-3">
										<div class="input-daterange input-group" id="datepicker">
											<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>"/>
											<span class="input-group-addon">hasta</span>
											<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" />
										</div>
									</div>
									<label class="col-lg-1 control-label">Sede</label>
									<div class="col-lg-2">
										<select name="Sede" class="form-control select2" id="Sede">
											<option value="">(Todos)</option>
											 <?php while($row_Suc=sqlsrv_fetch_array($SQL_Suc)){?>
													<option value="<?php echo $row_Suc['IdSucursal'];?>" <?php if((isset($_GET['Sede'])&&($_GET['Sede']!=""))&&(strcmp($row_Suc['IdSucursal'],$_GET['Sede'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Suc['DeSucursal'];?></option>
											 <?php }?>
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
									<div class="col-lg-2">
										<a href="consultar_despacho_lote.php" class="btn btn-warning"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
									</div>	
									<div class="col-lg-10 pull-right">
										<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
									</div>	
								</div>
							 </form>
						</div>
					</div>				
			 	</div>
			</div>
		  </div>
         <br>
		<?php if($sw==1){?>	 
		 <div id="dv_Articulos"><?php include('despacho_lote_art.php');?></div>
		 <br>
		 <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
					<div class="row">
						<div class="col-lg-3">
							<button class="btn btn-success btn-lg" type="button" id="CrearOrdenes" onClick="EjecutarProceso('2');"><i class="fa fa-play-circle"></i> Crear entregas</button>
							<button class="btn btn-primary btn-lg" type="button" id="FirmaCliente" onClick="AbrirFirma('SigDespacho');"><i class="fa fa-pencil-square-o"></i> Realizar firma</button>
							<input type="hidden" id="IdEvento" value="<?php if(isset($row['IdEvento'])){echo $row['IdEvento'];}?>" />
							<input type="hidden" id="SigDespacho" name="SigDespacho" value="" />
						</div>	
						<div class="col-lg-5"><?php LimpiarDirTempFirma();?>
							<div id="msgInfoSigDespacho" style="display: none;" class="alert alert-info"><i class="fa fa-info-circle"></i> El documento ya ha sido firmado.</div>
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
								<iframe id="DGDetalle" name="DGDetalle" style="border: 0;" width="100%" height="700" src="detalle_despacho_lote.php"></iframe>
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

        });

    </script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>