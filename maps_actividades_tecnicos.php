<?php require_once("includes/conexion.php");
//PermitirAcceso(308);
$Cons="";
$sw=0;//Saber si se filtro por algun tecnico
//Empleados
$SQL_Tec=Seleccionar('uvw_Sap_tbl_Empleados','*',"IdUsuarioSAP=0",'NombreEmpleado');

if(isset($_POST['Tecnico'])&&$_POST['Tecnico']!=""){
	$Param=array(
		"'".$_POST['Tecnico']."'",
		"'".FormatoFecha($_POST['Fecha'])."'"
	);
	$SQL=EjecutarSP('sp_ConsultarDatosMapasTecnico',$Param);
	$Num=sqlsrv_num_rows($SQL);
	$sw=1;
}
//echo $Num;
//echo $Cons;
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Mapa de t&eacute;cnicos | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script async defer
  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDu57ZFRd7A4C9gnE8TTm0-sqRV67pY1WE">
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
                    <h2>Mapa de t&eacute;cnicos</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Servicios</a>
                        </li>
						<li>
                            <a href="#">Mapas</a>
                        </li>
                        <li class="active">
                            <strong>Mapa de t&eacute;cnicos</strong>
                        </li>
                    </ol>
                </div>
               <?php  //echo $Cons;?>
            </div>
         <div class="wrapper wrapper-content">
			<div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="maps_actividades_tecnicos.php" method="post" id="formFiltro" class="form-horizontal">
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Empleado <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="Tecnico" class="form-control m-b select2" id="Tecnico" required>
									<option value="">Seleccione...</option>
								  <?php while($row_Tec=sqlsrv_fetch_array($SQL_Tec)){?>
										<option value="<?php echo $row_Tec['ID_Empleado'];?>" <?php if((isset($_POST['Tecnico']))&&(strcmp($row_Tec['ID_Empleado'],$_POST['Tecnico'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Tec['NombreEmpleado'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Fecha <span class="text-danger">*</span></label>
							<div class="col-lg-2 input-group date">
								 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="Fecha" type="text" class="form-control" id="Fecha" value="<?php if(isset($_POST['Fecha'])){echo $_POST['Fecha'];}else{echo date('Y-m-d');}?>" readonly="readonly" required placeholder="YYYY-MM-DD">
							</div>
							<div class="col-lg-2">
								<button type="submit" class="btn btn-outline btn-success"><i class="fa fa-map-marker"></i> Localizar</button>
							</div>
						</div>
				 </form>
			</div>
			</div>
		  </div>
			<br>
			<div class="row">
				<div class="col-lg-12">
					<div class="ibox ">
						<div class="ibox-content">
							<?php include("includes/spinner.php"); ?>
							<div class="google-map" id="map"><?php if($sw==1&&$Num==0){echo "No hay datos para mostrar.";}?></div>										
						</div>
					</div>
				</div> 
			</div>
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>
    $(document).ready(function() {
		$("#formFiltro").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
		$(".select2").select2();
		$('#Fecha').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			todayHighlight: true,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			format: 'yyyy-mm-dd',
			endDate: '<?php echo date('Y-m-d');?>'
		});
		<?php if($sw==0){?>
			get_my_location();
		<?php }else{?>
			initMap();
		<?php }?>
		
    });
</script>
<script>
var map;
	function start_map(srt_lat,srt_lng){
		var pos = {
			lat: srt_lat, 
			lng: srt_lng
		};
		map = new google.maps.Map(document.getElementById('map'), {
			center: pos,
			zoom: 15
		});
	}
	<?php if($sw==1&&$Num>0){?>
	function initMap() {
		var directionsService = new google.maps.DirectionsService();  
		var directionsDisplay = new google.maps.DirectionsRenderer();
		var waypts = [];
		var stop="";
		<?php 
		$i=1;
		while($row=sqlsrv_fetch_array($SQL)){
			if(($row['LatitudGPS']!="")&&($row['LongitudGPS']!="")){
				if($i==1){
					echo "var start = new google.maps.LatLng(".$row['LatitudGPS'].",".$row['LongitudGPS'].");
					";
					echo "start_map(".$row['LatitudGPS'].",".$row['LongitudGPS'].");";
					if($i==$Num){
						echo "var end = new google.maps.LatLng(".$row['LatitudGPS'].",".$row['LongitudGPS'].");
						";
					}
				}elseif(($i>1)&&($i<$Num)){
					echo "stop = new google.maps.LatLng(".$row['LatitudGPS'].",".$row['LongitudGPS'].");
					";
					echo "waypts.push({
							location: stop
						});
						";
				}else{
					echo "var end = new google.maps.LatLng(".$row['LatitudGPS'].",".$row['LongitudGPS'].");
					";
				}
			}
			$i++;		
		}?>				 

		var request={
			origin:start,
			destination:end,
			waypoints: waypts,
			travelMode:google.maps.TravelMode.DRIVING
		};

		directionsService.route(request,function(response, status){
			if(status==google.maps.DirectionsStatus.OK){
				directionsDisplay.setDirections(response);
				directionsDisplay.setMap(map);
				directionsDisplay.setOptions({suppressMarkers:false});
			}
		});
	}
	<?php }?>
	function get_my_location(){
		if(navigator.geolocation){
			navigator.geolocation.getCurrentPosition(function(position){
				var mypos={
						lat:position.coords.latitude,
						lng:position.coords.longitude
					};
				
				start_map(position.coords.latitude,position.coords.longitude);
				
				var marker = new google.maps.Marker({
					map: map,
					draggable: false,
					animation: google.maps.Animation.DROP,
					position: mypos
				});
			});
		}
	}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>