<?php require_once("includes/conexion.php");
$sw_error=0;
$msg_error="";

if(isset($_POST['Cambio'])&&($_POST['Cambio']==1)){
	$SQLClave=Seleccionar('uvw_tbl_Usuarios','Password',"ID_Usuario=".$_SESSION['CodUser']);
	$rowClave=sqlsrv_fetch_array($SQLClave);
	
	if(md5($_POST['PasswordActual'])==$rowClave['Password']){
		if(md5($_POST['PasswordNueva'])===md5($_POST['PasswordConfirmacion'])){
			try{
				$Upd_Clave="EXEC sp_tbl_Usuarios_CambiarClave '".$_SESSION['CodUser']."', '".md5($_POST['PasswordNueva'])."', '0'";
				
				$ParamUPD=array(
					"'".$_SESSION['CodUser']."'",
					"'".md5($_POST['PasswordNueva'])."'",
					"0"
				);
				$Upd_Clave=EjecutarSP('sp_tbl_Usuarios_CambiarClave',$ParamUPD);
				
				if($Upd_Clave){
					header('Location:cambiar_clave.php?a='.base64_encode("OK_UpdPass"));
				}else{//Sino se actualiza la clave
					$sw_error=1;
					$msg_error="Ha ocurrido un error al actualizar la contraseña";
				}
			}catch (Exception $e) {
				$sw_error=1;
				$msg_error="Ha ocurrido un error al actualizar la contraseña";
			}
		}else{//Si la nueva clave y la confirmación no son iguales
			$sw_error=1;
			$msg_error="Las contraseñas nuevas no coinciden. Por favor verifique";
		}
	}else{//Si la clave actual no es correcta
		$sw_error=1;
		$msg_error="Su contraseña actual no es correcta. Por favor verifique";	
	}
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Cambiar contrase&ntilde;a | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_UpdPass"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La constraseña ha sido cambiada exitosamente',
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
                    <h2>Cambiar contrase&ntilde;a</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Administraci&oacute;n</a>
                        </li>
                        <li class="active">
                            <strong>Cambiar contrase&ntilde;a</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
          <div class="row">
           <div class="col-lg-12">
			   <div class="ibox-content">
				   <?php include("includes/spinner.php"); ?>
              <form action="cambiar_clave.php" method="post" class="form-horizontal" id="CambiarClave" enctype="application/x-www-form-urlencoded">
			   <div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Ingrese la información</h3></label>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">Contrase&ntilde;a actual</label>
					<div class="col-sm-3"><input name="PasswordActual" type="password" autofocus required="required" class="form-control" id="PasswordActual" maxlength="50"></div>
				</div>
				<div class="form-group" id="pwd-container1">
					<label class="col-sm-2 control-label">Nueva contrase&ntilde;a</label>
					<div class="col-sm-3"><input name="PasswordNueva" type="password" required="required" class="form-control example1" id="PasswordNueva" maxlength="50"></div>
					<div class="col-sm-5">
						<div class="pwstrength_viewport_progress"></div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">Reescriba la contrase&ntilde;a</label>
					<div class="col-sm-3"><input name="PasswordConfirmacion" type="password" required="required" class="form-control" id="PasswordConfirmacion" maxlength="50"></div>
				</div>
				<div class="form-group">
					<div class="col-sm-9">
						<button class="btn btn-success" type="submit"><i class="fa fa-lock"></i>&nbsp;Cambiar</button> <a href="index1.php" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
					</div>
				</div>
				<input name="Cambio" type="hidden" id="Cambio" value="1">
			  </form>
			   <div class="alert alert-info">
				   <h3>Se recomienda que las contraseñas cumplan con los siguientes requisitos:</h3>
					<ul style="font-size: 1.2em;">					
						<li>Deben tener por lo menos 8 caracteres.</li>
						<li>Deben constar únicamente de caracteres del alfabeto latino que se encuentren en un teclado en inglés (no deben tener acentos ni otros diacríticos).</li>
						<li>Deben ser una combinación de por lo menos tres de los siguientes tipos de carácteres: mayúsculas, minúsculas, números y signos de puntuación.</li>
						<li>No deben basarse en una palabra que pueda encontrarse en un diccionario.</li>
						<li>No pueden estar basadas en su nombre ni en su nombre de usuario.</li>
						<li>No deben contener caracteres repetidos o secuencias de caracteres tales como 1234, 2222, ABCD o letras adyacentes del teclado</li>
					</ul>
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
	 $(document).ready(function(){
		 $("#CambiarClave").validate({
			 submitHandler: function(form){
				 Swal.fire({
						title: "¿Está seguro que desea cambiar su contraseña?",
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
		 // Example 1
            var options1 = {};
            options1.ui = {
                container: "#pwd-container1",
                showVerdictsInsideProgressBar: true,
                viewports: {
                    progress: ".pwstrength_viewport_progress"
                }
            };
            options1.common = {
                debug: false,
            };
            $('.example1').pwstrength(options1);
	});
</script>

<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>