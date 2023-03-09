<?php require("includes/conexion.php");

//Actividades recibidas
//Fechas
if(isset($_GET['FechaInicial_MisAct'])&&$_GET['FechaInicial_MisAct']!=""){
	$FechaInicial_MisAct=$_GET['FechaInicial_MisAct'];
}else{
	//Restar dias a la fecha actual
	$fecha = date('Y-m-d');
	$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasDashboard").' day');
	$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
	$FechaInicial_MisAct=$nuevafecha;
}
if(isset($_GET['FechaFinal_MisAct'])&&$_GET['FechaFinal_MisAct']!=""){
	$FechaFinal_MisAct=$_GET['FechaFinal_MisAct'];
}else{
	$FechaFinal_MisAct=date('Y-m-d');
}

$Cons_MisAct="Select * From uvw_Sap_tbl_Actividades Where ID_EmpleadoActividad='".$_SESSION['CodigoSAP']."' And IdEstadoActividad='N'";
//echo $Cons_MisAct;
//echo "<br>";
$SQL_MisAct=sqlsrv_query($conexion,$Cons_MisAct,array(),array( "Scrollable" => 'Static' ));
$Num_MisAct=sqlsrv_num_rows($SQL_MisAct);

//Actividades enviadas
//Fechas
if(isset($_GET['FechaInicial_ActAsig'])&&$_GET['FechaInicial_ActAsig']!=""){
	$FechaInicial_ActAsig=$_GET['FechaInicial_ActAsig'];
}else{
	//Restar dias a la fecha actual
	$fecha = date('Y-m-d');
	$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasDashboard").' day');
	$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
	$FechaInicial_ActAsig=$nuevafecha;
}
if(isset($_GET['FechaFinal_ActAsig'])&&$_GET['FechaFinal_ActAsig']!=""){
	$FechaFinal_ActAsig=$_GET['FechaFinal_ActAsig'];
}else{
	$FechaFinal_ActAsig=date('Y-m-d');
}
$Cons_ActAsig="Select * From uvw_Sap_tbl_Actividades Where UsuarioCreacion='".$_SESSION['User']."' And IdEstadoActividad='N'";
//echo $Cons_ActAsig;
$SQL_ActAsig=sqlsrv_query($conexion,$Cons_ActAsig,array(),array( "Scrollable" => 'Buffered' ));
$Num_ActAsig=sqlsrv_num_rows($SQL_ActAsig);

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo NOMBRE_PORTAL;?> | Inicio</title>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_UpdAdd"))){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Listo!',
                text: 'La actividad ha sido actualizada exitosamente.',
                type: 'success'
            });
		});		
		</script>";
}
?>
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
	.WarningColor{
		border-left: 4px solid #f8ac59 !important;
	}
	.DangerColor{
		border-left: 4px solid #ed5565 !important;
	}
	.InfoColor{
		border-left: 4px solid #23c6c8 !important;
	}
</style>
<?php if(!isset($_SESSION['SetCookie'])||($_SESSION['SetCookie']=="")){?>
<script>
$(document).ready(function(){
	$('#myModal').modal("show");
});
</script>
<?php }?>
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-6">
                    <h2>Bienvenido a <?php echo NOMBRE_PORTAL;?></h2>
                </div>
        </div>
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
						<?php echo utf8_encode($Contenido);?>
					</div>

					<div class="modal-footer">
						<button type="button" onClick="AceptarAcuerdo();" class="btn btn-primary" data-dismiss="modal">Acepto los t&eacute;rminos</button>
					</div>
				</div>
			</div>
		</div>
        <div class="row page-wrapper wrapper-content animated fadeInRight">
          	<div class="row">
				<div class="col-lg-12">
					<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-tasks"></i> Tareas recibidas - <?php echo $Num_MisAct;?></h3>
					<div class="ibox-content">
					<?php include("includes/spinner.php"); ?>
						<div class="row">
							<div class="col-lg-12 col-md-12">
								<div class="ibox-content">
									<form action="index1.php" method="get" id="formBuscar_MisAct" class="form-horizontal">
										<?php /*?><div class="form-group">
											<label class="col-lg-1 control-label">Fechas</label>
											<div class="col-lg-3">
												<div class="input-daterange input-group" id="datepicker">
													<input name="FechaInicial_MisAct" type="text" class="input-sm form-control" id="FechaInicial_MisAct" placeholder="Fecha inicial" value="<?php echo $FechaInicial_MisAct;?>"/>
													<span class="input-group-addon">hasta</span>
													<input name="FechaFinal_MisAct" type="text" class="input-sm form-control" id="FechaFinal_MisAct" placeholder="Fecha final" value="<?php echo $FechaFinal_MisAct;?>" />
												</div>
											</div>
											<div class="col-lg-7">
												<button type="submit" class="btn btn-outline btn-success"><i class="fa fa-search"></i> Buscar</button>
											</div>
										 </div><?php */?>
									<div class="table-responsive">
										<table class="table table-striped table-bordered table-hover dataTables-example" >
											<thead>
												<tr>
													<th>Núm.</th>
													<th>Titulo</th>
													<th>Tipo</th>
													<th>Asignado por</th>
													<th>Fecha creación</th>
													<th>Fecha actividad</th>
													<th>Fecha limite</th>
													<th>Dias venc.</th>
													<th>Respuesta</th>
													<th>Acciones</th>
												</tr>
											</thead>
											<tbody>
											<?php while($row_MisAct=sqlsrv_fetch_array($SQL_MisAct)){
													$DVenc_MisAct=DiasTranscurridos(date('Y-m-d'),$row_MisAct['FechaFinActividad']);
													if(($DVenc_MisAct[1]>=-2)&&($DVenc_MisAct[1]<0)){
														$Clase="class='WarningColor'";
													}elseif($DVenc_MisAct[1]>0){
														$Clase="class='DangerColor'";
													}else{
														$Clase="class='InfoColor'";
													}
												?>
												<tr class="gradeX">
													<td <?php echo $Clase;?>><?php echo $row_MisAct['ID_Actividad'];?></td>
													<td><?php echo $row_MisAct['TituloActividad'];?></td>
													<td><?php echo $row_MisAct['TipoTarea'];?></td>
													<td><?php echo $row_MisAct['DeAsignadoPor'];?></td>
													<td><?php echo $row_MisAct['FechaCreacion'];?></td>
													<td><?php echo $row_MisAct['FechaInicioActividad'];?></td>
													<td><?php echo $row_MisAct['FechaFinActividad'];?></td>
													<td><p class='<?php echo $DVenc_MisAct[0];?>'><?php echo $DVenc_MisAct[1];?></p></td>
													<td><?php echo ConsultarNotasActividad($row_MisAct['ID_Actividad']);?></td>
													<td><a href="actividad.php?id=<?php echo base64_encode($row_MisAct['ID_Actividad']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('index1.php');?>&tl=1" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
												</tr>
											<?php }?>
											</tbody>
										</table>
									</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div> 
			</div>
			<?php if(PermitirFuncion(301)){?>
			<br>
			<div class="row">
				<div class="col-lg-12">
					<h3 class="bg-primary p-xs b-r-sm"><i class="fa fa-pencil-square-o"></i> Tareas enviadas - <?php echo $Num_ActAsig;?></h3>
					<div class="ibox-content">
					<?php include("includes/spinner.php"); ?>
						<div class="row">
							<div class="col-lg-12 col-md-12">
								<div class="ibox-content">
									<form action="index1.php" method="get" id="formBuscar_ActAsig" class="form-horizontal">
										<?php /*?><div class="form-group">
											<label class="col-lg-1 control-label">Fechas</label>
											<div class="col-lg-3">
												<div class="input-daterange input-group" id="datepicker">
													<input name="FechaInicial_ActAsig" type="text" class="input-sm form-control" id="FechaInicial_ActAsig" placeholder="Fecha inicial" value="<?php echo $FechaInicial_ActAsig;?>"/>
													<span class="input-group-addon">hasta</span>
													<input name="FechaFinal_ActAsig" type="text" class="input-sm form-control" id="FechaFinal_ActAsig" placeholder="Fecha final" value="<?php echo $FechaFinal_ActAsig;?>" />
												</div>
											</div>
											<div class="col-lg-7">
												<button type="submit" class="btn btn-outline btn-success"><i class="fa fa-search"></i> Buscar</button>
											</div>
										 </div><?php */?>
									<div class="table-responsive">
										<table class="table table-striped table-bordered table-hover dataTables-example" >
											<thead>
												<tr>
													<th>Núm.</th>
													<th>Titulo</th>
													<th>Tipo</th>
													<th>Asignado a</th>
													<th>Fecha creación</th>
													<th>Fecha actividad</th>
													<th>Fecha limite</th>													
													<th>Dias venc.</th>
													<th>Respuesta</th>
													<th>Acciones</th>
												</tr>
											</thead>
											<tbody>
											<?php while($row_ActAsig=sqlsrv_fetch_array($SQL_ActAsig)){
													$DVenc_ActAsi=DiasTranscurridos(date('Y-m-d'),$row_ActAsig['FechaFinActividad']);
													if(($DVenc_ActAsi[1]>=-2)&&($DVenc_ActAsi[1]<0)){
														$Clase="class='WarningColor'";
													}elseif($DVenc_ActAsi[1]>0){
														$Clase="class='DangerColor'";
													}else{
														$Clase="class='InfoColor'";
													}
												?>
												<tr class="gradeX">
													<td <?php echo $Clase;?>><?php echo $row_ActAsig['ID_Actividad'];?></td>
													<td><?php echo $row_ActAsig['TituloActividad'];?></td>
													<td><?php echo $row_ActAsig['TipoTarea'];?></td>
													<td><?php if($row_ActAsig['NombreEmpleado']!=""){echo $row_ActAsig['NombreEmpleado'];}else{echo "(Sin asignar)";}?></td>
													<td><?php echo $row_ActAsig['FechaCreacion'];?></td>
													<td><?php echo $row_ActAsig['FechaInicioActividad'];?></td>
													<td><?php echo $row_ActAsig['FechaFinActividad'];?></td>													
													<td><p class='<?php echo $DVenc_ActAsi[0];?>'><?php echo $DVenc_ActAsi[1];?></p></td>
													<td><?php echo ConsultarNotasActividad($row_ActAsig['ID_Actividad']);?></td>
													<td><a href="actividad.php?id=<?php echo base64_encode($row_ActAsig['ID_Actividad']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('index1.php');?>&tl=1" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
												</tr>
											<?php }?>
											</tbody>
										</table>
									</div>
									</form>
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
		 $('.navy-bg').each(function() {
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
<script>
        $(document).ready(function(){
			$('#FechaInicial_MisAct').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'Y-m-d'
            });
			 $('#FechaFinal_MisAct').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'Y-m-d'
            }); 
			<?php if(PermitirFuncion(301)){?>
			$('#FechaInicial_ActAsig').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'Y-m-d'
            });
			 $('#FechaFinal_ActAsig').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'Y-m-d'
            }); 
			<?php }?>
            $('.dataTables-example').DataTable({
                pageLength: 10,
                responsive: true,
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