<?php require_once("includes/conexion.php"); 

setlocale(LC_TIME, "spanish");
//require_once("includes/conexion_hn.php");
$sw=0;
$Filtro="";//Filtro
$sw_Informe=isset($_GET['id']) ? 1 : 0;//Indica si la pagina se esta cargando inicialmente o como informe. 1: Como Informe. 0: Inicialmente.
$FechaInicial='';
$FechaFinal='';

if(PermitirFuncion(301)){//Solo si tiene permisos de ver las llamadas de servicio

if($sw_Informe==1){
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
}


$Series=isset($_GET['Series']) ? $_GET['Series'] : '';

//if(PermitirFuncion(901)){//Llamadas de servicio
if(($sw_Informe==0) || ($sw_Informe==1&&$sw==1)){
	$Param1=array(
		"1",
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Series."'"
	);
	$Param2=array(
		"2",
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Series."'"
	);
	$Param3=array(
		"3",
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Series."'"
	);
	$Param4=array(
		"4",
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Series."'"
	);
	$Param6=array(
		"6",
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Series."'"
	);
	$Param7=array(
		"7",
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Series."'"
	);
	
	$SQL_Llamadas=EjecutarSP('sp_DashboardLlamadas',$Param1);
	$row_Llamadas=sql_fetch_array($SQL_Llamadas);

	$SQL_EstServicio=EjecutarSP('sp_DashboardLlamadas',$Param2);
	$SQL_ActEstadoServ=EjecutarSP('sp_DashboardLlamadas',$Param3);
	$SQL_OrdenServLlamada=EjecutarSP('sp_DashboardLlamadas',$Param4);
//	$SQL_Panoramas=EjecutarSP('sp_DashboardLlamadas','5');
	$SQL_ServiciosMes=EjecutarSP('sp_DashboardLlamadas',$Param6);
	$SQL_OrigenLlamada=EjecutarSP('sp_DashboardLlamadas',$Param7);
//	$row_EstServicio=sql_fetch_array($SQL_EstServicio);
//}

	$meses=array();
	$datos=array();
	$dias=array();
	while($row_ServiciosMes=sql_fetch_array($SQL_ServiciosMes)){
		if(!in_array($row_ServiciosMes['Mes'],$meses)){
			array_push($meses, $row_ServiciosMes['Mes']);
		}
		
		if(!in_array($row_ServiciosMes['Dia'],$dias)){
			array_push($dias, $row_ServiciosMes['Dia']);
		}

		array_push($datos, [
			'Mes'=>$row_ServiciosMes['Mes'],
			'Dia'=>$row_ServiciosMes['Dia'],
			'Cant'=>$row_ServiciosMes['Cant'],
		]);
	}
	sort($dias);

//	echo '<pre>';
//	print_r($meses);
//	echo '</pre>';
//	
//	echo '<pre>';
//	print_r($datos);
//	echo '</pre>';
//	exit();
	
	if(PermitirFuncion(902) && $sw_Informe==0){//Dashboard de actividades enviadas y recibidas

		$SQL_MisAct=Seleccionar('uvw_Sap_tbl_Actividades','TOP 10 FechaFinActividad,ID_Actividad,DE_AsuntoActividad,DE_TipoActividad,FechaHoraInicioActividad,FechaHoraFinActividad,DeAsignadoPor,NombreEmpleado',"ID_EmpleadoActividad='".$_SESSION['CodigoSAP']."' And IdEstadoActividad='N'");
		$Num_MisAct=sqlsrv_num_rows($SQL_MisAct);

		$SQL_ActAsig=Seleccionar('uvw_Sap_tbl_Actividades','TOP 10 FechaFinActividad,ID_Actividad,DE_AsuntoActividad,DE_TipoActividad,FechaHoraInicioActividad,FechaHoraFinActividad,DeAsignadoPor,NombreEmpleado',"UsuarioCreacion='".$_SESSION['User']."' And IdEstadoActividad='N'");
		$Num_ActAsig=sqlsrv_num_rows($SQL_ActAsig);
	}

}

}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Inicio | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	#animar{
		animation-duration: 1.5s;
  		animation-name: tada;
  		animation-iteration-count: infinite;
	}
	#animar2{
		animation-duration: 1s;
  		animation-name: swing;
  		animation-iteration-count: infinite;
	}
	#animar3{
		animation-duration: 3s;
  		animation-name: pulse;
  		animation-iteration-count: infinite;
	}
	.edit1 {/*Widget editado por aordonez*/
		border-radius: 0px !important; 
		padding: 15px 20px;
		margin-bottom: 10px;
		margin-top: 10px;
		height: 120px !important;
	}
	.modal-lg {
		width: 50% !important;
	}
</style>

<script>
$(document).ready(function(){
	<?php if(!isset($_SESSION['SetCookie'])||($_SESSION['SetCookie']=="")){?>
		$('#myModal').modal("show");
	<?php }?>
	
	$("#Sucursal").change(function(){
		$('.ibox-content').toggleClass('sk-loading',true);
		var Sucursal=document.getElementById('Sucursal').value;
		$.ajax({
			type: "POST",
			url: "ajx_cbo_select.php?type=26&id="+Sucursal+"&tdoc=191",
			success: function(response){
				$('#Series').html(response).fadeIn();
				$('.ibox-content').toggleClass('sk-loading',false);
			}
		});	
	});
});
</script>
<!-- InstanceEndEditable -->
</head>

<body class="mini-navbar">

<div id="wrapper">

    <?php include_once("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
      
        <?php 
		$Nombre_archivo="contrato_confidencialidad.txt";
		$Archivo=fopen($Nombre_archivo,"r");
		$Contenido = fread($Archivo, filesize($Nombre_archivo));
		?>
        <div class="modal inmodal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" data-show="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Acuerdo de confidencialidad</h4>
						<small>Por favor lea atentamente este contrato que contiene los T&eacute;rminos y Condiciones de uso de este sitio. Si continua usando este portal, consideramos que usted est&aacute; de acuerdo con ellos.</small>
					</div>
					<div class="modal-body">
						<?php echo $Contenido;?>
					</div>

					<div class="modal-footer">
						<button type="button" onClick="AceptarAcuerdo();" class="btn btn-primary" data-dismiss="modal">Acepto los t&eacute;rminos</button>
					</div>
				</div>
			</div>
		</div>
        <div class="row page-wrapper wrapper-content animated fadeInRight">
			<?php if($sw_Informe==0){?>
			<div class="row m-b-md">
				<div class="col-lg-12">
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<h2>Bienvenido <?php echo $_SESSION['NomUser'];?></h2>
					</div>
				</div>
			</div>
			<?php }?>
		  <?php if($sw_Informe==1 && PermitirFuncion(301)){?>
		  <div class="row m-b-md">
			<div class="col-lg-12">
				<div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="dsb_servicios.php" method="get" id="formBuscar" class="form-horizontal">
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
							<label class="col-lg-1 control-label">Sucursal</label>
							<div class="col-lg-2">
								<select name="Sucursal" class="form-control" id="Sucursal">
									<option value="">(Todos)</option>
								  <?php	while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
											<option value="<?php echo $row_Sucursal['IdSucursal'];?>" <?php if(isset($_GET['Sucursal'])&&(strcmp($row_Sucursal['IdSucursal'],$_GET['Sucursal'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['DeSucursal'];?></option>
									<?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Serie</label>
							<div class="col-lg-2">
								<select name="Series" class="form-control" id="Series">
										<option value="">(Todos)</option>
								  <?php if($sw==1){ 
											while($row_Series=sqlsrv_fetch_array($SQL_Series)){?>
											<option value="<?php echo $row_Series['IdSeries'];?>" <?php if((isset($_GET['Series']))&&(strcmp($row_Series['IdSeries'],$_GET['Series'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries'];?></option>
								  <?php 	}
										}?>
								</select>
							</div>
							<div class="col-lg-1">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
					  <input type="hidden" name="id" id="id" value="<?php echo base64_encode($sw_Informe);?>" />
				 </form>
				</div>
			</div>
		  </div>
		  <?php }?>
		  <?php if((PermitirFuncion(301))&&(($sw_Informe==0) || ($sw_Informe==1&&$sw==1))){?>
          <div class="row">
            <div class="col-lg-3">
              <div class="ibox ">
                <div class="ibox-title"> <span class="label label-info pull-right"><?php if($sw_Informe==0){echo ucwords(strftime("%B"));}?></span>
                  <h5>Llamadas abiertas</h5>
                </div>
                <div class="ibox-content">
                  <h1 class="no-margins"><?php echo number_format($row_Llamadas['LlamadasAbiertas'],0);?></h1>
				</div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="ibox ">
                <div class="ibox-title"> <span class="label label-warning pull-right"><?php if($sw_Informe==0){echo ucwords(strftime("%B"));}?></span>
                  <h5>Llamadas pendientes</h5>
                </div>
                <div class="ibox-content">
                  <h1 class="no-margins"><?php echo number_format($row_Llamadas['LlamadasPendientes'],0);?></h1>
				</div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="ibox ">
                <div class="ibox-title"> <span class="label label-danger pull-right"><?php if($sw_Informe==0){echo ucwords(strftime("%B"));}?></span>
                  <h5>Llamadas cerradas</h5>
                </div>
                <div class="ibox-content">
                  <h1 class="no-margins"><?php echo number_format($row_Llamadas['LlamadasCerradas'],0);?></h1>
				</div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="ibox ">
                <div class="ibox-title"> <span class="label label-success pull-right"><?php if($sw_Informe==0){echo ucwords(strftime("%B"));}?></span>
                  <h5>Total llamadas</h5>
                </div>
                <div class="ibox-content">
                  <h1 class="no-margins"><?php echo number_format($row_Llamadas['TotalLlamadas'],0);?></h1>
				</div>
              </div>
            </div>
          </div>
		
		  <div class="row">
            <div class="col-lg-4">
				<div class="ibox">
					<div class="row">
						<div class="col-lg-12 col-md-12">
							<div class="ibox">
								<div id="graph_1" class="table-responsive"></div>
							</div>
						</div>
					</div>
				</div>
            </div>
			<div class="col-lg-8">
				<div class="ibox">
					<div class="row">
						<div class="col-lg-12 col-md-12">
							<div class="ibox">
								<div id="graph_4" class="table-responsive"></div>
							</div>
						</div>
					</div>
				</div>
            </div>
          </div>
		  <div class="row">
			  <div class="col-lg-4">
				  <div class="ibox">
					<div class="row">
					  <div class="col-lg-12 col-md-12">
						<div class="ibox">
						  <div id="graph_2" class="table-responsive"></div>
						</div>
					  </div>
					</div>
				  </div>
				</div>
			  <div class="col-lg-4">
				  <div class="ibox">
					<div class="row">
					  <div class="col-lg-12 col-md-12">
						<div class="ibox">
						  <div id="graph_3" class="table-responsive"></div>
						</div>
					  </div>
					</div>
				  </div>
				</div>
			  <?php if(PermitirFuncion(902) && $sw_Informe==0){//Dashboard de actividades enviadas y recibidas?>
			  <div class="col-lg-4">
				  <div class="ibox ">
					<div class="ibox-title">
					  <h5>Actividades</h5>
					  <div class="ibox-tools"> <a class="collapse-link"> <i class="fa fa-chevron-up"></i> </a> <a class="close-link"> <i class="fa fa-times"></i> </a> </div>
					</div>
					<div class="ibox-content">
					  <div class="tabs-container">
						<ul class="nav nav-tabs">
						  <li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-calendar"></i> Recibidas <span class="label label-success float-right m-l-sm"><?php echo $Num_MisAct;?></span></a></li>
						  <li><a data-toggle="tab" href="#tab-2"><i class="fa fa-calendar"></i> Enviadas <span class="label label-success float-right m-l-sm"><?php echo $Num_ActAsig;?></span></a></li>
						</ul>
					  </div>
					  <div class="tab-content">
						<div id="tab-1" class="tab-pane active"> <br>
						  <div class="feed-activity-list">
							<?php
							if ( $Num_MisAct > 0 ) {
							  while ( $row_MisAct = sqlsrv_fetch_array( $SQL_MisAct ) ) {
								$DVenc = DiasTranscurridos( $row_MisAct[ 'FechaFinActividad' ], date( 'Y-m-d' ) );
								?>
							<div class="feed-element">
							  <div>
								<div class="col-xs"> <a class="col-xs-6" href="actividad.php?id=<?php echo base64_encode($row_MisAct['ID_Actividad']);?>&tl=1" target="_blank"> <span class="badge badge-primary"><?php echo $row_MisAct['ID_Actividad'];?></span> </a> <span class="pull-right badge badge-success font-bold"><i class="fa fa-user"></i> <?php echo $row_MisAct['DeAsignadoPor'];?></span> </div>
								<div class="col-xs">
								  <h4 class="col-xs-12 font-bold no-margins"><?php echo $row_MisAct['DE_AsuntoActividad'];?></h4>
								</div>
								<div class="col-xs">
								  <h4 class="col-xs-6 font-bold no-margins"><?php echo $row_MisAct['DE_TipoActividad'];?></h4>
								  <h4 class="pull-right no-margins <?php echo $DVenc[0];?>"><strong>Vencimiendo: </strong>
									<?php if($DVenc[1]==0){echo "Hoy";}else{echo $DVenc[1]." dias";}?>
								  </h4>
								</div>
								<div class="col-xs">
								  <h4 class="col-xs-6 text-navy no-margins">Inicia: <?php echo $row_MisAct['FechaHoraInicioActividad']->format('Y-m-d H:s');?></h4>
								  <h4 class="pull-right text-danger no-margins">Finaliza: <?php echo $row_MisAct['FechaHoraFinActividad']->format('Y-m-d H:s');?></h4>
								</div>
							  </div>
							</div>
							<?php }
											}else{?>
							<div class="feed-element">
							  <div>
								<div>No hay actividades nuevas</div>
							  </div>
							</div>
							<?php }?>
						  </div>
						</div>
						<div id="tab-2" class="tab-pane"> <br>
						  <div class="feed-activity-list">
							<?php if($Num_ActAsig>0){
											while($row_ActAsig=sqlsrv_fetch_array($SQL_ActAsig)){
												$DVenc=DiasTranscurridos($row_ActAsig['FechaFinActividad'],date('Y-m-d'));
											?>
							<div class="feed-element">
							  <div>
								<div class="col-xs"> <a class="col-xs-6" href="actividad.php?id=<?php echo base64_encode($row_ActAsig['ID_Actividad']);?>&tl=1" target="_blank"> <span class="badge badge-primary"><?php echo $row_ActAsig['ID_Actividad'];?></span> </a> <span class="pull-right badge badge-success font-bold"><i class="fa fa-user"></i> <?php echo $row_ActAsig['NombreEmpleado'];?></span> </div>
								<div class="col-xs">
								  <h4 class="col-xs-12 font-bold no-margins"><?php echo $row_ActAsig['DE_AsuntoActividad'];?></h4>
								</div>
								<div class="col-xs">
								  <h4 class="col-xs-6 font-bold no-margins"><?php echo $row_ActAsig['DE_TipoActividad'];?></h4>
								  <h4 class="pull-right no-margins <?php echo $DVenc[0];?>"><strong>Vencimiendo: </strong>
									<?php if($DVenc[1]==0){echo "Hoy";}else{echo $DVenc[1]." dias";}?>
								  </h4>
								</div>
								<div class="col-xs">
								  <h4 class="col-xs-6 text-navy no-margins">Inicia: <?php echo $row_ActAsig['FechaHoraInicioActividad']->format('Y-m-d H:s');?></h4>
								  <h4 class="pull-right text-danger no-margins">Finaliza: <?php echo $row_ActAsig['FechaHoraFinActividad']->format('Y-m-d H:s');?></h4>
								</div>
							  </div>
							</div>
							<?php }
											}else{?>
							<div class="feed-element">
							  <div>
								<div>No hay actividades nuevas</div>
							  </div>
							</div>
							<?php }?>
						  </div>
						</div>
					  </div>
					</div>
				  </div>
				</div>
			  <?php }else{?>
			  <div class="col-lg-4">
				<div class="ibox">
					<div class="row">
						<div class="col-lg-12 col-md-12">
							<div class="ibox">
								<div id="graph_5" class="table-responsive"></div>
							</div>
						</div>
					</div>
				</div>
              </div>
			  <?php }?>
		  </div>
		  <?php }?>
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<?php if((PermitirFuncion(301))&&(($sw_Informe==0) || ($sw_Informe==1&&$sw==1))){?>
<script>
Highcharts.chart('graph_1', {
    chart: {
        type: 'variablepie'
    },
    title: {
        text: 'Estados de servicio'
    },
    subtitle: {
        text: 'Llamadas de servicio'
    },
    tooltip: {
        headerFormat: '',
        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b><br/>'
    },

    series: [
        {
			minPointSize: '50%',
			innerSize: '40%',
			zMin: 0,
			name: "Estados de servicio",
//            colorByPoint: true,
			data: [
			<?php while($row_EstServicio=sql_fetch_array($SQL_EstServicio)){?>
				  {
					name: "<?php echo $row_EstServicio['DeEstadoServicio'];?>",
					y: <?php echo $row_EstServicio['Cant']; ?>
				  },
			<?php }?>
            ]
        }
    ]
});
</script>
<script>
Highcharts.chart('graph_2', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie'
    },
     title: {
        text: 'Estados de tareas'
    },
    subtitle: {
        text: 'Actividades'
    },
    tooltip: {
        headerFormat: '',
        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b><br/>'
    },
	accessibility: {
        point: {
            valueSuffix: '%'
        }
    },
	plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '{point.percentage:.1f}%'
            },
            showInLegend: true
        }
    },
    series: [
        {
			name: "Cantidad",
			data: [
			<?php while($row_ActEstadoServ=sql_fetch_array($SQL_ActEstadoServ)){?>
				 {
					name: "<?php echo $row_ActEstadoServ['DeTipoEstadoActividad'];?>",
					color: "<?php echo $row_ActEstadoServ['Color'];?>",
					y: <?php echo $row_ActEstadoServ['Cant'];?>
				  },
			<?php }?>
            ]
        }
    ]
});
</script>
<script>
Highcharts.chart('graph_3', {
    chart: {
        type: 'bar'
    },
     title: {
        text: 'Tipos de llamadas'
    },
    accessibility: {
        announceNewData: {
            enabled: true
        }
    },
    xAxis: {
        type: 'category'
    },
    yAxis: {
        title: {
            text: 'Cantidad'
        }

    },
    legend: {
        enabled: false
    },
    plotOptions: {
        series: {
            borderWidth: 0,
            dataLabels: {
                enabled: true,
                format: '{point.y}'
            }
        }
    },
	tooltip: {
        //headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b>'
    },
    series: [
        {
			name: "Tipos de llamada",
			colorByPoint: true,
			data: [
			<?php
				while($row_OrdenServLlamada=sql_fetch_array($SQL_OrdenServLlamada)){?>
				  {
					name: '<?php echo $row_OrdenServLlamada['DeTipoLlamada'];?>',
					y: <?php echo $row_OrdenServLlamada['Cant'];?>
				  },
			<?php }?>
            ]
        }
    ]
});
</script>
<script>
Highcharts.chart('graph_4', {
    chart: {
        type: 'line'
    },
    title: {
        text: '<?php echo $sw_Informe==1 ? 'Servicios agregados en el periodo' : 'Servicios agregados este mes'?>'
    },
	xAxis: {
		type: 'category',
		title: {
            text: 'Dias del mes'
        },
		categories:[<?php for($i=0;$i<count($dias);$i++){echo "'".$dias[$i]."',";}?>]
    },
    yAxis: {
        title: {
            text: 'Cantidad'
        }
    },
	plotOptions: {
        line: {
            dataLabels: {
                enabled: true
            },
            enableMouseTracking: false
        }
    },
    series: [
		<?php
		for($i=0;$i<count($meses);$i++){?>
			{
				name: '<?php echo $meses[$i];?>',
				data: [
					<?php
					for($j=0;$j<count($datos);$j++){
						if($datos[$j]['Mes']==$meses[$i]){?>
							{
								name:"<?php echo $datos[$j]['Dia']; ?>",
								y:<?php echo $datos[$j]['Cant']; ?>
							},
					<?php		
						}		
					 }	?>
				]
			},			
		<?php }	?>
    ]
});
</script>
<?php if($sw_Informe==1&&$sw==1){?>
<script>
Highcharts.chart('graph_5', {
    chart: {
        type: 'column'
    },
     title: {
        text: 'Top Origen de llamadas'
    },
	subtitle: {
        text: 'Llamadas de servicio'
    },
    accessibility: {
        announceNewData: {
            enabled: true
        }
    },
    xAxis: {
        type: 'category'
    },
    yAxis: {
        title: {
            text: 'Cantidad'
        }

    },
    legend: {
        enabled: false
    },
    plotOptions: {
        series: {
            borderWidth: 0,
            dataLabels: {
                enabled: true,
                format: '{point.y}'
            }
        }
    },
	tooltip: {
        //headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b>'
    },
    series: [
        {
			name: "Origen de llamada",
			colorByPoint: true,
			data: [
			<?php
				while($row_OrigenLlamada=sql_fetch_array($SQL_OrigenLlamada)){?>
				  {
					name: '<?php echo $row_OrigenLlamada['DeOrigenLlamada'];?>',
					y: <?php echo $row_OrigenLlamada['Cant'];?>
				  },
			<?php }?>
            ]
        }
    ]
});
</script>
<?php }?>
<?php }?>

<script>	
	 $(document).ready(function(){
		 $("#formBuscar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
			 }
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
		 $('.navy-bg').each(function() {
                animationHover(this, 'pulse');
            });
		  $('.yellow-bg').each(function() {
                animationHover(this, 'pulse');
            });
		 $('.lazur-bg').each(function() {
                animationHover(this, 'pulse');
            });
		 $(".truncate").dotdotdot({
            watch: 'window'
		  });
	});
</script>
<?php if(isset($_GET['dt'])&&$_GET['dt']==base64_encode("result")){?>
<script>
	$(document).ready(function(){
		toastr.options = {
			closeButton: true,
			progressBar: true,
			showMethod: 'slideDown',
			timeOut: 6000
		};
		toastr.success('¡Su contraseña ha sido modificada!', 'Felicidades');
	});
</script>
<?php }?>
<script src="js/js_setcookie.js"></script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>