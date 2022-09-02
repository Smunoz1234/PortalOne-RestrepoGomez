<?php 
require_once("includes/conexion.php");
PermitirAcceso(215);

//Serie de llamada
$ParamSerie=array(
	"'".$_SESSION['CodUser']."'",
	"'191'"
);
$SQL_Series=EjecutarSP('sp_ConsultarSeriesDocumentos',$ParamSerie);

$sw_error=0;
$msg_error="";

//Insertar datos
if(isset($_POST['P'])&&($_POST['P']!="")){
	try{	
		$SQL_Campos=Seleccionar("tbl_Parametros_Asistentes","*");
		while($row_Campos=sqlsrv_fetch_array($SQL_Campos)){
			if(isset($_POST[$row_Campos['ID_Campo']])){
				if($row_Campos['TipoObjeto']=="66"){
					$Serie=1;
				}else{
					$Serie=$_POST['Series'];
				}
				
				$Param=array(
					"'".$row_Campos['ID_Campo']."'",
					"'".$row_Campos['TipoObjeto']."'",
					"'".$Serie."'",
					"'".$_POST[$row_Campos['NombreCampo']]."'",
					"'".$_SESSION['CodUser']."'"
				);

				$SQL_Param=EjecutarSP("sp_tbl_Parametros_Asistentes_Detalle",$Param);

				if(!$SQL_Param){
					$sw_error=1;
					$msg="Error al actualizar la información";
				}
			}			
		}
		
		if($sw_error==0){
			header('Location:parametros_asistentes.php?a='.base64_encode("OK_PRUpd"));
		}
	}catch (Exception $e) {
		$sw_error=1;
		$msg=$e->getMessage();
	}	
	
}

//Crear nuevo parametro
if(isset($_POST['MM_Insert'])&&($_POST['MM_Insert']!="")){
	$Param=array(
		"'".$_POST['TipoObjeto']."'",
		"'".$_POST['NombreVariable']."'",
		"'".$_POST['NombreMostrar']."'"
	);
	$SQL=EjecutarSP('sp_tbl_Parametros_Asistentes',$Param);
	if($SQL){
		header('Location:parametros_asistentes.php?&a='.base64_encode("OK_NewParam"));
	}else{
		$sw_error=1;
		$msg_error="No se pudo insertar el nuevo parámetro";
	}
}

//Rutas
$SQL_Rutas=Seleccionar("tbl_Parametros_Asistentes","*","TipoObjeto=66");

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Parámetros asistentes | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.swal2-container {
	  	z-index: 9000;
	}
</style>
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_NewParam"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El nuevo valor ha sido agregado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_PRUpd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'Datos actualizados exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Ha ocurrido un error!',
                text: '".LSiqmlObs($msg_error)."',
                icon: 'warning'
            });
		});		
		</script>";
}
?>
<script type="text/javascript">
	var opcionActual=0;
	
	$(document).ready(function() {
		$("#Series").data("prev",$("#Series").val());		
		
		$("#Series").change(function(event){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Series=document.getElementById('Series').value;
			var edit=0;
			
			if(document.getElementById("edit_"+$("#Series").data("prev"))){
				edit=document.getElementById("edit_"+$("#Series").data("prev")).value;
			}
			
			$("#Series").data("prev",$("#Series").val());
			
			if(edit==1){
				Swal.fire({
					title: "No ha guardado los cambios",
					text: "¿Desea continuar?",
					icon: "info",
					showCancelButton: true,
					confirmButtonText: "Si",
					cancelButtonText: "No"
				}).then((result) => {
					if (result.isConfirmed) {
						traerDatos(Series);
					}else{
						$("#Series").val(opcionActual);
						$("#Series").data("prev",$("#Series").val());
						$('.ibox-content').toggleClass('sk-loading',false);
					}
				});
			}else{
				traerDatos(Series);
			}
		});
		
	});	
</script>
<script>
function ActualizarDatos(campo){
	$("#edit_"+campo).val(1)
}

function traerDatos(Series){
	$.ajax({
		type: "POST",
		url: "ajx_cbo_select.php?type=31&id="+Series+"&obj=191",
		success: function(response){
			$('#Result').html(response);
			opcionActual=Series;
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
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
                    <h2>Parámetros asistentes</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
						<li>
                            <a href="#">Administración</a>
                        </li>
						<li>
                            <a href="#">Parámetros del sistema</a>
                        </li>
                        <li class="active">
                            <strong>Parámetros asistentes</strong>
                        </li>
                    </ol>
                </div>
            </div>
            <?php  //echo $Cons;?>
         <div class="wrapper wrapper-content">
			 <div class="modal inmodal fade" id="myModal" tabindex="1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content" id="ContenidoModal">
						
					</div>
				</div>
			 </div>
			 <form action="parametros_asistentes.php" method="post" id="frmParam" class="form-horizontal">			 
			 <div class="row">
				<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-plus-square"></i> Acciones</h3></label>
						</div>
						<div class="form-group">
							<div class="col-lg-6">
								<button class="btn btn-primary" type="submit" id="Guardar"><i class="fa fa-check"></i> Guardar datos</button>  
								<button class="btn btn-warning" type="button" id="NewParam" onClick="CrearParametro();"><i class="fa fa-plus-circle"></i> Crear nuevo valor</button>  
							</div>
						</div>
					  	<input type="hidden" id="P" name="P" value="frmParam" />
					</div>
				</div>
			 </div>
			 <br>
			 <div class="row">
			 	<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						 <div class="tabs-container">
							<ul class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-calendar"></i> Rutero</a></li>
								<li><a data-toggle="tab" href="#tab-2"><i class="fa fa-tasks"></i> Creación de OT</a></li>
							</ul>
							<div class="tab-content">
								<div id="tab-1" class="tab-pane active">
									<br>
									<div class="form-group">
										<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-check-square-o"></i> Lista de parámetros de rutero</h3></label>
									</div>
									<input name="<?php echo $row_Rutas['ID_Campo'];?>" type="hidden" id="<?php echo $row_Rutas['ID_Campo'];?>" value="<?php echo $row_Rutas['ID_Campo'];?>">
									<?php
										while($row_Rutas=sqlsrv_fetch_array($SQL_Rutas)){
											$SQL_Data=Seleccionar('tbl_Parametros_Asistentes_Detalle','*',"ID_Campo='".$row_Rutas['ID_Campo']."' and TipoObjeto='".$row_Rutas['TipoObjeto']."'");
											$row_Data=sqlsrv_fetch_array($SQL_Data);
									
									?>
										<div class="form-group">
											<label class="col-lg-2 control-label"><?php echo $row_Rutas['LabelCampo'];?><br><span class="text-muted"><?php echo $row_Rutas['NombreCampo'];?></span></label>
											<div class="col-lg-3">
												<input name="<?php echo $row_Rutas['NombreCampo'];?>" type="text" class="form-control" id="<?php echo $row_Rutas['NombreCampo'];?>" maxlength="100" value="<?php echo $row_Data['Valor'];?>" autocomplete="off">
												<input name="<?php echo $row_Rutas['ID_Campo'];?>" type="hidden" id="<?php echo $row_Rutas['ID_Campo'];?>" value="<?php echo $row_Rutas['ID_Campo'];?>">
											</div>
										</div>
									<?php }?>
								</div>
								<div id="tab-2" class="tab-pane">
									<br>
									<div class="form-group">
										<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-check-square-o"></i> Lista de parámetros de creación de OT</h3></label>
									</div>
									<div class="form-group">
										<label class="col-lg-2 control-label">Serie OT</label>
										<div class="col-lg-3">
											<select name="Series" class="form-control" id="Series">
													<option value="">Seleccione...</option>
											  <?php while($row_Series=sqlsrv_fetch_array($SQL_Series)){?>
													<option value="<?php echo $row_Series['IdSeries'];?>" <?php if((isset($_GET['Series']))&&(strcmp($row_Series['IdSeries'],$_GET['Series'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries']." ({$row_Series['IdSeries']})";?></option>
											  <?php }?>
											</select>
										</div>
									</div>	
									<div id="Result"></div>
								</div>
							</div>
						 </div>
					</div>
          		</div>
			 </div>
		</form>	 
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
 <script>
	$(document).ready(function(){
		$("#frmParam").validate({
		 submitHandler: function(form){
			 Swal.fire({
					title: "¿Está seguro que desea guardar los datos?",
					icon: "question",
					showCancelButton: true,
					confirmButtonText: "Si, confirmo",
					cancelButtonText: "No"
				}).then((result) => {
					if (result.isConfirmed) {
						$('.ibox-content').toggleClass('sk-loading',true);
						form.submit();
					}
				});
			}
		});

		$(".select2").select2();
		$('.i-checks').iCheck({
			 checkboxClass: 'icheckbox_square-green',
			 radioClass: 'iradio_square-green',
		  });			
	});
</script>
<script>
function CrearParametro(){
	$('.ibox-content').toggleClass('sk-loading',true);
	
	$.ajax({
		type: "POST",
		url: "md_crear_parametro_asistente.php",
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		}
	});
}	
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>