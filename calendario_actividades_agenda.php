<?php require("includes/conexion.php");
PermitirAcceso(306);
//CALENDARIO DE COPLA GROUP SAS
$Cons="";
$Cons_Tec="EXEC sp_ConsultarTecnicos '".$_SESSION['CodUser']."'";
//echo $Cons;
$SQL_Tec=sqlsrv_query($conexion,$Cons_Tec);

if(isset($_GET['Tecnico'])&&$_GET['Tecnico']!=""){
	$Cons="EXEC sp_ConsultarDatosCalendarioTecnico '".$_GET['Tecnico']."'";
}elseif(isset($_GET['Tecnico'])&&$_GET['Tecnico']==""){
	$Cons="EXEC sp_ConsultarDatosCalendarioTecnico ''";
}
$SQL=sqlsrv_query($conexion,$Cons);
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo NOMBRE_PORTAL;?> | Calendario de empleados</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {//Cargar los almacenes dependiendo del proyecto
		$("#ClienteActividad").change(function(){
			$.ajax({
				type: "POST",
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+document.getElementById('ClienteActividad').value,
				success: function(response){
					$('#Sucursal').html(response).fadeIn();
				}
			});
		});
	});
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
                    <h2>Calendario de empleados</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Gesti&oacute;n de tareas</a>
                        </li>
						<li>
                            <a href="#">Calendarios</a>
                        </li>
                        <li class="active">
                            <strong>Calendario de empleados</strong>
                        </li>
                    </ol>
                </div>
               <?php  //echo $Cons;?>
            </div>
         <div class="wrapper wrapper-content">
			<div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
				  <form action="calendario_actividades_tecnico.php" method="get" id="formFiltro" class="form-horizontal">
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Empleado</label>
							<div class="col-lg-3">
								<select name="Tecnico" class="form-control m-b select2" id="Tecnico">
										<option value="">(TODOS)</option>
								  <?php while($row_Tec=sqlsrv_fetch_array($SQL_Tec)){?>
										<option value="<?php echo $row_Tec['ID_Tecnico'];?>" <?php if((isset($_GET['Tecnico']))&&(strcmp($row_Tec['ID_Tecnico'],$_GET['Tecnico'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Tec['NombreTecnico'];?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-1">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-filter"></i> Filtrar</button>
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
						<div class="table-responsive">
							<div id="calendar"></div>
						</div>
					</div>
				</div> 
			</div>
        </div>
        <!-- InstanceEndEditable -->
        <?php include("includes/footer.php"); ?>

    </div>
</div>
<?php include("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>

    $(document).ready(function() {
		$(".select2").select2();
		
        /* initialize the calendar
         -----------------------------------------------------------------*/
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay,listWeek'
            },
			defaultView: 'agendaWeek',
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
				while($row=sqlsrv_fetch_array($SQL)){
					if($row['TodoDia']==1){$AllDay="true";}else{$AllDay="false";}
					echo "{
						id: ".$row['ID_Actividad'].",
						title:'".$row['EtiquetaActividad']."',
						subtitle:'".$row['DE_AsuntoActividad']."',
						description:'".LSiqmlObs($row['ComentariosActividad'])."',
						start: '".$row['FechaHoraInicioActividad']."',
						end: '".$row['FechaHoraFinActividad']."',
						allDay: ".$AllDay.",
						textColor: '#ffffff',
						backgroundColor: '".$row['ColorPrioridadActividad']."',
						borderColor: '".$row['ColorPrioridadActividad']."',
						url: 'actividad_edit.php?id=".base64_encode($row['ID_Actividad'])."&return=".base64_encode($_SERVER['QUERY_STRING'])."&pag=".base64_encode('calendario_actividades_tecnico.php')."'
					},";
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