<?php 
require("includes/conexion.php");
$result=0;
if(isset($_POST['Cambio'])&&($_POST['Cambio']==1)){
	if(isset($_POST['Password'])&&(md5($_POST['Password'])==md5($_POST['ConfPassword']))){
		try{
			$Upd_Clave="EXEC sp_tbl_Usuarios_CambiarClave '".$_SESSION['CodUser']."', '".md5($_POST['Password'])."', '0'";
			$SQL_Clave=sqlsrv_query($conexion,$Upd_Clave);
			if(sqlsrv_query($conexion,$Upd_Clave)){
				header('Location:logout.php?data='.base64_encode("result"));
			}else{//Sino se actualiza la clave
				sqlsrv_close($conexion);
				throw new Exception('Ha ocurrido un error cambiar la clave.');
				echo $Upd_Clave;
			}
		}catch (Exception $e) {
			//InsertarLog(1, 5, $Upd_Clave);
			echo 'Excepción capturada: ',  $e->getMessage(), "\n";
			exit();
		}
	}else{//Si la nueva clave y la confirmación no son iguales
		$result=2;			
	}
}
?>
<!DOCTYPE html>
<html lang="es">
<html lang="en" class="light-style">

<head>
  	<title>Iniciar sesi&oacute;n | <?php echo NOMBRE_PORTAL;?></title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="x-ua-compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
	<link rel="shortcut icon" href="css/favicon.png" />
	<link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900" rel="stylesheet">

	<link rel="stylesheet" href="css/bootstrap.css" class="theme-settings-bootstrap-css">
	<link rel="stylesheet" href="css/appwork.css" class="theme-settings-appwork-css">
	<link rel="stylesheet" href="css/theme-corporate.css" class="theme-settings-theme-css">
	<link rel="stylesheet" href="css/uikit.css">
	<link rel="stylesheet" href="css/authentication.css">
	<link rel="stylesheet" href="css/toastr.css">
	<script src="js/jquery-3.1.1.min.js"></script>
	<script src="js/popper.min.js"></script>
	<script src="js/bootstrap.js"></script>
	<script src="js/toastr.js"></script>
	<script src="js/plugins/validate/jquery.validate.min.js"></script>
	<script src="js/funciones.js"></script>
</head>

<body>
  <div class="page-loader">
    <div class="bg-primary"></div>
  </div>

  <!-- Content -->

  <div class="authentication-wrapper authentication-2 ui-bg-cover ui-bg-overlay-container px-4" style="background-image: url('img/background.jpg');">
    <div class="ui-bg-overlay bg-dark opacity-25"></div>

    <div class="authentication-inner py-5">

      <div class="card">
        <div class="p-4 px-sm-5 pt-sm-5 pb-0">
          <!-- Logo -->			
          <div class="d-flex justify-content-center align-items-center pb-2 mb-4">
           <img src="img/img_logo.png" alt="Logo" width="300" height="95" />
          </div>
          <!-- / Logo -->
			<h4 class="text-center text-muted font-weight-normal mb-4">Cambiar contrase&ntilde;a</h4>

          <!-- Form -->
          <form name="frmLogin" id="frmLogin" class="mt-5" role="form" action="login_cambio_clave.php" method="post" enctype="application/x-www-form-urlencoded">
            <div class="form-group">
				<label class="form-label">Nueva contrase&ntilde;a</label>
				<input name="Password" type="password" autofocus required="required" class="form-control" id="Password" maxlength="50">
			</div>
			<div class="form-group">
				<label class="form-label">Confirmar</label>
				<input name="ConfPassword" type="password" required="required" class="form-control" id="ConfPassword" maxlength="50">
			</div>
			<div class="d-flex justify-content-between align-items-center m-0">
				<button type="submit" class="btn btn-primary">Cambiar contrase&ntilde;a</button>
				<button onClick="javascript:location.href='logout.php'" type="button" class="btn btn-secondary">Salir</button>
		    </div>
			<br>
			<?php if($result==2){?>
				<div class="alert alert-danger">
					<i class="fa fa-times-circle-o"></i> <strong>Error.</strong> Las contrase&ntilde;as no coinciden. <br>Por favor verifique.
				</div>
			<?php }?>
			<input name="Cambio" type="hidden" id="Cambio" value="1">
          </form>
          <!-- / Form -->

        </div>
        <div class="card-footer py-3 px-4 px-sm-5">
          <div class="text-center text-body">
            <?php include("includes/copyright.php"); ?>
          </div>
        </div>
      </div>

    </div>
  </div>
<script>	
	 $(document).ready(function(){		
		  $("#frmLogin").validate();
	});
</script>
<?php include("includes/pie.php"); ?>

</body>

</html>