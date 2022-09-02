<?php
include("includes/definicion.php");
if (!isset($_SESSION)) {
  session_start();
}
if (isset($_SESSION['User'])&&$_SESSION['User']!="") {
	header('Location:index1.php');
	exit();
}
session_destroy();
$error=0;
$success=0;
if(isset($_POST['User'])||isset($_POST['Email'])){
	require("includes/conect_srv.php");
	require("includes/funciones.php");
	$Con="Select * From uvw_tbl_Usuarios Where Usuario='".strtolower($_POST['User'])."' and Email='".$_POST['Email']."'";
	$SQL=sqlsrv_query($conexion,$Con,array(),array( "Scrollable" => 'static' ));
	$Num=sqlsrv_num_rows($SQL);
	$row=sqlsrv_fetch_array($SQL);
	//echo $Num;
	//exit();
	if($Num>=1){
		$random=rand(1548, 10548);
		$hash=md5($row['Usuario'].$random.date('Ymd'));
		$ConUpd="Update tbl_Usuarios Set ForgotPassword='".$hash."' Where ID_Usuario='".$row['ID_Usuario']."'";
		$bdhash=base64_encode($database);
		//echo $ConUpd;
		//exit();
		if(sqlsrv_query($conexion,$ConUpd)){
			EnviarMail($row['Email'],$row['NombreUsuario'],3,"Restablecer clave","<!doctype html><html><head><meta charset='utf-8'><style>* { margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px;}img { max-width: 100%;}body { -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6;}table td { vertical-align: top;}body { background-color: #f6f6f6;}.body-wrap { background-color: #f6f6f6; width: 100%;}.container { display: block !important; max-width: 600px !important; margin: 0 auto !important; clear: both !important;}.content { max-width: 600px; margin: 0 auto; display: block; padding: 20px;}.main { background: #fff; border: 1px solid #e9e9e9; border-radius: 3px;}.content-wrap { padding: 20px;}.content-block { padding: 0 0 20px;}.header { width: 100%; margin-bottom: 20px;}.footer { width: 100%; clear: both; color: #999; padding: 20px;}.footer a { color: #999;}.footer p, .footer a, .footer unsubscribe, .footer td { font-size: 12px;}h1, h2, h3 { font-family: 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif; color: #000; margin: 40px 0 0; line-height: 1.2; font-weight: 400;}h1 { font-size: 32px; font-weight: 500;}h2 { font-size: 24px;}h3 { font-size: 18px;}h4 { font-size: 14px; font-weight: 600;}p, ul, ol { margin-bottom: 10px; font-weight: normal;}p li, ul li, ol li { margin-left: 5px; list-style-position: inside;}a { color: #1ab394; text-decoration: underline;}.btn-primary { text-decoration: none; color: #FFF; background-color: #1ab394; border: solid #1ab394; border-width: 5px 10px; line-height: 2; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize;}.last { margin-bottom: 0;}.first { margin-top: 0;}.aligncenter { text-align: center;}.alignright { text-align: right;}.alignleft { text-align: left;}.clear { clear: both;}@media only screen and (max-width: 640px) { h1, h2, h3, h4 { font-weight: 600 !important; margin: 20px 0 5px !important; } h1 { font-size: 22px !important; } h2 { font-size: 18px !important; } h3 { font-size: 16px !important; } .container { width: 100% !important; } .content, .content-wrap { padding: 10px !important; } .invoice { width: 100% !important; }}</style></head><body><table class='body-wrap'> <tr> <td></td> <td class='container' width='600'> <div class='content'> <table class='main' width='100%' cellpadding='0' cellspacing='0'> <tr> <td class='content-wrap'> <table cellpadding='0' cellspacing='0'> <tr valign='middle' align='center'> <td> <img class='img-responsive' src='http://190.144.36.138:89/img/img_logo.png'/> </td> </tr> <tr> <td align='center' class='content-block'> <h3>Restablecer contrase&ntilde;a</h3> </td> </tr> <tr> <td class='content-block'><p>Hemos recibido una solicitud para restablecer tu contraseña en el PortalCopla. Para continuar con el proceso haz clic en el boton <strong>Cambiar contrase&ntilde;a</strong> para cambiar tu contraseña.</p></td> </tr> <tr> <td class='content-block'>Esta solicitud solo está disponible durante 12 horas. Luego tendrás que volver a solicitar restabler tu contrase&ntilde;a.<br>No responda este mensaje, pues no recibirá respuesta.</td> </tr> <tr> <td class='content-block aligncenter'> <a href='http://190.144.36.138:89/forgotpassword.php?id=".base64_encode($row['ID_Usuario'])."&code=".$hash."&bdcode=".$bdhash."' class='btn-primary'>Cambiar contrase&ntilde;a</a> </td> </tr> </table> </td> </tr> </table> <div class='footer'> <table width='92%'> <tr> <td class='aligncenter content-block'>Todos los derechos reservados &copy; 2017 COPLA GROUP S.A.S</td> </tr> </table> </div></div> </td> <td></td> </tr></table></body></html>");
				$success=1;
		}
	}else{
		$error=1;
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
			<h4 class="text-center text-muted font-weight-normal mb-4">Recordar contraseña</h4>

            <!-- Form -->
			<form name="frmForgot" id="frmForgot" class="my-5" role="form" action="recordar_clave.php" method="post" enctype="application/x-www-form-urlencoded">
					<div class="form-group">
						<select name="BaseDatos" id="BaseDatos" class="form-control">
							<option value="<?php echo BDPRO; ?>"><?php echo BDPRO; ?></option>
							<?php if(BDPRUEBAS!=""){?>
							<option value="<?php echo BDPRUEBAS; ?>"><?php echo BDPRUEBAS; ?></option>
							<?php }?>
						</select>
					</div>
					<div class="form-group">
						<label class="form-label">Usuario</label>
						<input name="User" type="text" autofocus required="" class="form-control" id="User" maxlength="50">
					</div>
					<div class="form-group">
						<label class="form-label">Correo electr&oacute;nico</label>
						<input name="Email" type="email" autofocus required="" class="form-control" id="Email" maxlength="50">
					</div>
					<div class="d-flex justify-content-between align-items-center m-0">
						<button type="submit" class="btn btn-primary">Enviar solicitud</button>
						<button onClick="javascript:location.href='login.php'" type="button" class="btn btn-secondary">Regresar</button>
				  </div><br>
					<?php if($success==1){?>
					<div class="alert alert-info">
						<i class="fa fa-info-circle"></i> Hemos enviado a su correo electrónico instrucciones para restablecer su contrase&ntilde;a.
					</div>
					<?php }?>
					<?php if($error==1){?>
					<div class="alert alert-danger">
						<i class="fa fa-times-circle"></i> No hemos encontrado estos registros en nuestras bases de datos. Por favor intenta nuevamente.
					</div>
					<?php }?>
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
<?php if(isset($_POST['data'])&&$_POST['data']=="OK"){?>
<script>
	$(document).ready(function(){
		toastr.success('¡Su contraseña ha sido modificada!','Felicidades');
	});
</script>
<?php }?>
<script>	
	 $(document).ready(function(){		
		  $("#frmForgot").validate();
	});
</script>
<?php include("includes/pie.php"); ?>
</body>

</html>