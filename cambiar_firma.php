<?php require_once("includes/conexion.php");
PermitirAcceso(222);

$sw_error=0;
$msg_error="";

$dir_firma=CrearObtenerDirTempFirma();
$dir_new=CrearObtenerDirAnx("usuarios");

$SQL=Seleccionar('uvw_tbl_Usuarios','AnxFirma',"ID_Usuario=".$_SESSION['CodUser']);
$row=sqlsrv_fetch_array($SQL);

if(isset($_POST['Cambio'])&&($_POST['Cambio']==1)){
	$NombreFileFirma="";
		
	//Firma usuario		
	if((isset($_POST['SigUser']))&&($_POST['SigUser']!="")){
		$NombreFileFirma=base64_decode($_POST['SigUser']);
		if(!copy($dir_firma.$NombreFileFirma,$dir_new.$NombreFileFirma)){
			$sw_error=1;
			$msg_error="No se pudo mover la firma";
		}
	}
	
	if($sw_error==0){
		$Param=array(
			"'".$_SESSION['CodUser']."'",
			"'".$NombreFileFirma."'"
		);
		$SQLUpd=EjecutarSP('sp_tbl_Usuarios_ActualizarFirma',$Param);
		if($SQLUpd){
			header('Location:cambiar_firma.php?a='.base64_encode("OK_UpdFirm"));
		}else{//Sino se actualiza la clave
			$sw_error=1;
			$msg_error="Ha ocurrido un error al actualizar la firma";
		}
	}
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Actualizar mi firma | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_UpdFirm"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La firma ha sido actualizada exitosamente',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Lo sentimos!',
                text: '".LSiqmlObs($msg_error)."',
                icon: 'warning'
            });
		});		
		</script>";
}
?>
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include_once("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-4">
                    <h2>Actualizar mi firma</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Administraci&oacute;n</a>
                        </li>
                        <li class="active">
                            <strong>Actualizar mi firma</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
          <div class="row">
           <div class="col-lg-12">
			   <div class="ibox-content">
				   <?php include("includes/spinner.php"); ?>
              <form action="cambiar_firma.php" method="post" class="form-horizontal" id="CambiarFirma" enctype="multipart/form-data">
			   <div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-pencil-square"></i> Realice su firma</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-2">Mi firma actual</label>		
				</div>
				<div class="form-group">
					<div class="col-lg-3">
						<?php if($row['AnxFirma']!=""){?>
							<img id="SigActual" style="max-width: 100%; height: auto;" src="<?php echo $dir_new.$row['AnxFirma'];?>" alt="" />
						<?php }else{?>
							<div class="alert alert-info text-center h-150 w-150 p-xl border-size-sm">
                                <h4><i class="fa fa-info-circle"></i> Aún no tiene ninguna firma realizada</h4>
                            </div>
						<?php }?>
					</div>			
				</div>
				<div class="form-group">
					<div class="col-lg-3">
						<?php LimpiarDirTempFirma(); ?>
						<button class="btn btn-success m-t-n-lg" type="button" id="FirmaUsuario" onClick="AbrirFirma('SigUser');"><i class="fa fa-pencil-square-o"></i> Realizar nueva firma</button>
						<input type="hidden" id="SigUser" name="SigUser" value="" />
						<img id="ImgSigUser" style="display: none; max-width: 100%; height: auto;" src="" alt="" />
					</div>
				</div>
				<div class="form-group m-t-xl">
					<div class="col-lg-3">
						<button class="btn btn-primary" type="submit" id="Crear"><i class="fa fa-check"></i> Guardar datos</button>
					</div>
				</div>
				<input name="Cambio" type="hidden" id="Cambio" value="1">
			  </form>
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
	 $(document).ready(function(){
		 $("#CambiarFirma").validate({
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
	});
</script>

<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>