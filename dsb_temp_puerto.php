<?php require_once("includes/conexion.php"); 

$sw=0;

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

$Cliente = isset($_GET['Cliente']) ? $_GET['Cliente'] : "";
$Sucursal = isset($_GET['Sucursal']) ? $_GET['Sucursal'] : "";
$Bodega = isset($_GET['Bodega']) ? $_GET['Bodega'] : "";
$Producto = isset($_GET['Producto']) ? $_GET['Producto'] : "";
$Motonave = isset($_GET['Motonave']) ? $_GET['Motonave'] : "";
$Estado = isset($_GET['Estado']) ? $_GET['Estado'] : "";
$Tabla = isset($_GET['Tabla']) ? $_GET['Tabla'] : 1;

if($sw==1){
	$Param=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Cliente."'",
		"'".$Sucursal."'",
		"'".$Bodega."'",
		"'".$Producto."'",
		"'".$Motonave."'",
		"'".$Estado."'"
	);

	$SQL_Datos=EjecutarSP('sp_DashboardPuerto',$Param);
//	$row_Datos=sql_fetch_array($SQL_Datos);

	$datos=array();
	$dias=array();

	while($row_Datos=sql_fetch_array($SQL_Datos)){
		if(!in_array($row_Datos['fecha']->format('Y-m-d'),$dias)){
			array_push($dias, $row_Datos['fecha']->format('Y-m-d'));
		}

		array_push($datos, [
			'temperatura_carga'=>$row_Datos['temperatura_carga'],
			'humedad_carga'=>$row_Datos['humedad_carga'],
			'fecha'=>$row_Datos['fecha']->format('Y-m-d'),
		]);
	}



//	echo '<pre>';
//	print_r($dias);
//	echo '</pre>';

//	echo '<pre>';
//	print_r($datos);
//	echo '</pre>';
//	exit();
	
}


//Bodegas
$SQL_Bodega=Seleccionar('tbl_BodegasPuerto','*',"codigo_cliente='".$Cliente."' and linea_sucursal='".$Sucursal."'",'bodega_puerto');

//Productos
$SQL_Producto=Seleccionar('tbl_ProductosPuerto','*','','producto_puerto');

//Motonave
$SQL_Motonave=Seleccionar('tbl_TransportesPuerto','*','','transporte_puerto');

//Estado
$SQL_EstadoFrm=Seleccionar('tbl_EstadoFormulario','*');

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Dashboard Monitoreo de temperaturas | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	
</style>

<script>
$(document).ready(function(){
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
				$('#Sucursal').html(response);
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
			url: "ajx_cbo_select.php?type=36&id="+Sucursal+"&clt="+Cliente+"&selec=1",
			success: function(response){
				$('#Bodega').html(response).fadeIn();
				$('.ibox-content').toggleClass('sk-loading',false);
				$('#Bodega').trigger('change');
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
        <div class="row page-wrapper wrapper-content animated fadeInRight">
		  <div class="row m-b-md">
			<div class="col-lg-12">
				<div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="dsb_temp_puerto.php" method="get" id="formBuscar" class="form-horizontal">
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Fechas <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" autocomplete="off" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>"/>
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" autocomplete="off" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" />
								</div>
							</div>
							<label class="col-lg-1 control-label">Cliente <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<input name="Cliente" type="hidden" id="Cliente" value="<?php if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){ echo $_GET['Cliente'];}?>">
								<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Para TODOS, dejar vacio..." value="<?php if(isset($_GET['NombreCliente'])&&($_GET['NombreCliente']!="")){ echo $_GET['NombreCliente'];}?>" required>
							</div>
							<label class="col-lg-1 control-label">Sucursal <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								 <select id="Sucursal" name="Sucursal" class="form-control select2" required>
									<option value="">Seleccione...</option>
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
							<label class="col-lg-1 control-label">Bodega <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="Bodega" class="form-control select2" id="Bodega" required>
									<option value="">Seleccione...</option>
								  <?php
									if($sw==1){
										while($row_Bodega=sqlsrv_fetch_array($SQL_Bodega)){?>
											<option value="<?php echo $row_Bodega['id_bodega_puerto'];?>" <?php if((isset($_GET['Bodega']))&&(strcmp($row_Bodega['id_bodega_puerto'],$_GET['Bodega'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Bodega['bodega_puerto'];?></option>
								  <?php }
									}?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Producto <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="Producto" class="form-control select2" id="Producto" required>
									<option value="">Seleccione...</option>
								  <?php while($row_Producto=sqlsrv_fetch_array($SQL_Producto)){?>
										<option value="<?php echo $row_Producto['id_producto_puerto'];?>" <?php if((isset($_GET['Producto']))&&(strcmp($row_Producto['id_producto_puerto'],$_GET['Producto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Producto['producto_puerto'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Tabla de resultados <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="Tabla" class="form-control select2" id="Tabla" required>
									<option value="1" <?php if(isset($_GET['Tabla'])&&($_GET['Tabla']=="1")){echo "selected=\"selected\"";}?>>NO mostrar la tabla</option>
								 	<option value="2" <?php if(isset($_GET['Tabla'])&&($_GET['Tabla']=="2")){echo "selected=\"selected\"";}?>>SI mostrar la tabla</option>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Motonave</label>
							<div class="col-lg-3">
								<select name="Motonave" class="form-control select2" id="Motonave">
									<option value="">(Todos)</option>
								  <?php while($row_Motonave=sqlsrv_fetch_array($SQL_Motonave)){?>
										<option value="<?php echo $row_Motonave['id_transporte_puerto'];?>" <?php if((isset($_GET['Motonave']))&&(strcmp($row_Motonave['id_transporte_puerto'],$_GET['Motonave'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Motonave['transporte_puerto'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Estado</label>
							<div class="col-lg-3">
								<select name="Estado" class="form-control" id="Estado">
										<option value="">(Todos)</option>
								  <?php while($row_EstadoFrm=sqlsrv_fetch_array($SQL_EstadoFrm)){?>
										<option value="<?php echo $row_EstadoFrm['Cod_Estado'];?>" <?php if((isset($_GET['Estado']))&&(strcmp($row_EstadoFrm['Cod_Estado'],$_GET['Estado'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoFrm['NombreEstado'];?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-4">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
				 </form>
				</div>
			</div>
		  </div>
		  <?php if($Tabla==2){?>
          <div class="row m-b-md">
           <div class="col-lg-12">
			    <div class="ibox-content form-horizontal">
					<?php include("includes/spinner.php"); ?>
					<div class="form-group">
						<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Datos de la gráfica</h3></label>
					</div>
					<div class="table-responsive">
							<table class="table table-bordered table-hover" >
							<thead>
							<tr>
								<th>Fecha</th>
								<th>Bodega</th>
								<th>Producto</th>
								<th>Motonave</th>
								<th>Estado</th>
								<th>Temperatura de la carga (°C)</th>
								<th>Humedad de la carga (%)</th>
							</tr>
							</thead>
							<tbody>
							<?php $SQL=EjecutarSP('sp_DashboardPuerto',$Param);
							  while($row=sqlsrv_fetch_array($SQL)){ ?>
								<tr class="gradeX">
									<td><?php echo $row['fecha']->format('Y-m-d');?></td>
									<td><?php echo $row['bodega_puerto'];?></td>						
									<td><?php echo $row['producto_puerto'];?></td>
									<td><?php echo $row['transporte_puerto'];?></td>
									<td><span <?php if($row['estado']=='O'){echo "class='label label-info'";}elseif($row['estado']=='A'){echo "class='label label-danger'";}else{echo "class='label label-primary'";}?>><?php echo $row['nombre_estado'];?></span></td>
									<td><?php echo number_format($row['temperatura_carga'],2);?></td>
									<td><?php echo number_format($row['humedad_carga'],2);?></td>
								</tr>
							<?php }?>
							</tbody>
							</table>
							<div class="col-lg-12">
								<a href="exportar_excel.php?exp=10&Cons=<?php echo base64_encode(implode(",",$Param));?>&sp=<?php echo base64_encode("sp_DashboardPuerto");?>">
									<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
								</a>
							</div>
					  </div>
				</div>
			 </div> 
          </div>
		<?php }?>
		  <?php if($sw==1){?>
		  <div class="row">
            <div class="col-lg-12">
				<div class="ibox">
					<div class="ibox-content row">
						<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-line-chart"></i> Gráfica de tendencia</h3></label>
					</div>
					<div id="graph_1"></div>
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
<?php if($sw==1){?>
<script>
Highcharts.chart('graph_1', {
    chart: {
        type: 'line'
    },
    title: {
        text: 'Monitoreo de temperaturas'
    },
	xAxis: {
		type: 'category',
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
		{
			name:'Temperatura de la carga (°C)',
			color: "#0c1af3",
			data:[
				<?php
					for($j=0;$j<count($datos);$j++){?>
						{
							name:"<?php echo $datos[$j]['fecha']; ?>",
							y:<?php echo $datos[$j]['temperatura_carga']; ?>
						},
				<?php }	?>
			]
		},
		{
			name:'Humedad de la carga (%)',
			color: "#f36b0c",
			data:[
				<?php
					for($j=0;$j<count($datos);$j++){?>
						{
							name:"<?php echo $datos[$j]['fecha']; ?>",
							y:<?php echo $datos[$j]['humedad_carga']; ?>
						},
				<?php }	?>
			]
		}
    ]
});
</script>
<?php }?>
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
		 
		 $(".select2").select2();
		 
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
	});
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>