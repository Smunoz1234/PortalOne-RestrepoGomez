<?php require_once("includes/conexion.php");
PermitirAcceso(305);

$sw=0;
$Cliente = isset($_GET['Cliente']) ? $_GET['Cliente'] : "";
$Sucursal = isset($_GET['Sucursal']) ? $_GET['Sucursal'] : "";

if(isset($_GET['Anno'])&&($_GET['Anno']!="")){
	$Anno=$_GET['Anno'];
	$sw=1;
}else{
	$Anno=date('Y');
}

if($sw==1){
	$Param=array(
		"'".$Anno."'",
		"'".$Cliente."'",
		"'".$Sucursal."'"
	);
	$SQL=EjecutarSP('sp_ConsultarDatosCalendarioCronograma',$Param);
	$sw=1;
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Calendario de cronograma de servicios | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {//Cargar los almacenes dependiendo del proyecto
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
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Cliente.value+"&tdir=S",
				success: function(response){
					$('#Sucursal').html(response);
					$("#Sucursal").trigger("change");
				}
			});
		});
	});
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
                    <h2>Calendario de cronograma de servicios</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Servicios</a>
                        </li>
						<li>
                            <a href="#">Calendarios</a>
                        </li>
                        <li class="active">
                            <strong>Calendario de cronograma de servicios</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
			<div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					<?php include("includes/spinner.php"); ?>
				  <form action="calendario_cronograma.php" method="get" id="formFiltro" class="form-horizontal">
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
							  <select name="Sucursal" id="Sucursal" class="select2 form-control">
									 <option value="">(Todos)</option>
									 <?php 
									 if(isset($_GET['Sucursal'])){//Cuando se ha seleccionado una opción
										 if(PermitirFuncion(205)){
											$Where="CodigoCliente='".$_GET['Cliente']."' and TipoDireccion='S'";
											$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","NombreSucursal",$Where);
										 }else{
											$Where="CodigoCliente='".$_GET['Cliente']."' and TipoDireccion='S' and ID_Usuario = ".$_SESSION['CodUser'];
											$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal",$Where);	
										 }
										 while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
											<option value="<?php echo $row_Sucursal['NombreSucursal'];?>" <?php if(strcmp($row_Sucursal['NombreSucursal'],$_GET['Sucursal'])==0){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['NombreSucursal'];?></option>
									 <?php }
									 }?>
								  </select>
							</div>
							<label class="col-lg-1 control-label">Año <span class="text-danger">*</span></label>
							<div class="col-lg-2">
								<select name="Anno" required class="form-control" id="Anno">
									<option value="2019" <?php if((isset($Anno))&&(strcmp(2019,$Anno)==0)){ echo "selected=\"selected\"";}?>>2019</option>
									<option value="2020" <?php if((isset($Anno))&&(strcmp(2020,$Anno)==0)){ echo "selected=\"selected\"";}?>>2020</option>
									<option value="2021" <?php if((isset($Anno))&&(strcmp(2021,$Anno)==0)){ echo "selected=\"selected\"";}?>>2021</option>
									<option value="2022" <?php if((isset($Anno))&&(strcmp(2022,$Anno)==0)){ echo "selected=\"selected\"";}?>>2022</option>
								</select>
							</div>
							<div class="col-lg-1">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-filter"></i> Filtrar</button>
							</div>
						</div>
					  	<div class="form-group">
							<div class="col-lg-12">
								<label class="form-label">Referencia de colores</label>
								<br>
								<i class="fa fa-square" style="color: #3788D8;font-size: medium;"></i> <span class="m-r-sm">VISITA DE PROGRAMACIÓN</span>
								<i class="fa fa-square" style="color: #1ABC9C;font-size: medium;"></i> <span class="m-r-sm">VISITA DE SUPERVISIÓN</span>	
							</div>
						</div>
				 </form>
			</div>
			</div>
		  </div>
			<br>
			<div class="row">
				<div class="col-lg-12">
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="table-responsive">
							<div id="calendar"></div>
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
		
        /* initialize the calendar
         -----------------------------------------------------------------*/
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,listWeek'
            },
			defaultView: 'month',
            editable: false,
			timeFormat: 'hh:mm a',
			eventRender: function(event, element){
				element.qtip({
					content: {
						title: event.subtitle,
						text: event.description
					},
					position: {
						target: 'mouse',
						adjust: { x: 5, y: 5 }
					}
				});
			},
            events: [
			<?php 
				if($sw==1){	
					while($row=sqlsrv_fetch_array($SQL)){?>
						{
							id: '<?php echo $row['ID'];?>',
							title:'<?php echo LSiqmlObs($row['Etiqueta']);?>',
							subtitle:'<?php echo $row['TipoVisita'].' ('.$row['DeArticuloLMT'].')'; ?>',
							description:'<?php echo LSiqmlSaltos(LSiqmlObs($row['InformacionAdicional']))?>',
							start: '<?php echo $row['Fecha']->format('Y-m-d');?>',
							//end: '<?php //echo $row['Fecha']?>',
							allDay: true,
							textColor: '#ffffff',
							backgroundColor: '<?php echo $row['ColorEvento']?>',
							borderColor: '<?php echo $row['ColorEvento']?>'
						},
					<?php }
				}
			?>
            ]		
        });
    });
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>