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
$log=1;
if(isset($_POST['User'])||isset($_POST['Password'])){
	if(($_POST['User']=="")||($_POST['Password'])==""){
			//header('Location:index1.php');
			$log=0;
		}else{
			//require("includes/conect_srv.php");
			require("includes/LSiqml.php");		
			require("includes/funciones.php");			
			$User=LSiqmlLogin($_POST['User']);
			$Pass=LSiqmlLogin($_POST['Password']);
					
			$Param=array(
				"'".$User."'",
				"'".md5($Pass)."'"
			);
			$SQL=EjecutarSP('sp_ValidarUsuario',$Param);
			
			if($SQL){
				$Num=sqlsrv_num_rows($SQL);
				if($Num>0){
					$row=sqlsrv_fetch_array($SQL);
					session_start();
					$_SESSION['BD']=$database;//Del archivo conect
					$_SESSION['User']=strtoupper($row['Usuario']);
					$_SESSION['CodUser']=$row['ID_Usuario'];
					$_SESSION['NomUser']=$row['NombreUsuario'];
					$_SESSION['EmailUser']=$row['Email'];
					$_SESSION['Perfil']=$row['ID_PerfilUsuario'];
					$_SESSION['NomPerfil']=$row['PerfilUsuario'];
					$_SESSION['CambioClave']=$row['CambioClave'];
					$_SESSION['TimeOut']=$row['TimeOut'];
					$_SESSION['CodigoSAP']=$row['CodigoSAP'];
					$_SESSION['IdCardCode']=$row['IdCardCode'];
					$_SESSION['CodigoEmpVentas']=$row['IdEmpVentas'];
					$_SESSION['SetCookie']=$row['SetCookie'];
					$_SESSION['CodigoSAPProv']=$row['CodigoSAPProv'];
					$_SESSION['NITProv']=$row['NITProv'];
					//$_SESSION['Sucursal']=$row['BranchName'];
					//$_SESSION['CodSucursal']=$row['Branch'];
					//$_SESSION['Dpto']=$row['DeptName'];
					//$_SESSION['CodDpto']=$row['Dept'];
					//$_SESSION['Ext']=$row['Extension'];
					
					$SQL_Pag=EjecutarSP('sp_ObtenerDashboard',$_SESSION['CodUser']);
					$row_Pag=sql_fetch_array($SQL_Pag);
					$_SESSION['Index']=$row_Pag['URL'];
					
					$jwt=AuthJWT($User,$Pass);
					if($jwt['Success']==1){
						$_SESSION['JWT']=$jwt['Token'];
					}else{
						$_SESSION['JWT']="";
					}
					setcookie("JWT",$jwt['Token'],array('secure'=>true,'httponly'=>true));
					
					if($row['CambioClave']==1){
						//echo "Ingreso al cambio";
						header('Location:login_cambio_clave.php');
					}else{
						$ConsUpdUltIng="Update tbl_Usuarios set FechaUltIngreso=GETDATE() Where ID_Usuario='".$_SESSION['CodUser']."'";
						if(sqlsrv_query($conexion,$ConsUpdUltIng)){
							sqlsrv_close($conexion);
							if(isset($_POST['return_url'])&&$_POST['return_url']!=""){
								header('Location:'.base64_decode($_POST['return_url']));
							}else{
								header('Location:'.$_SESSION['Index']);
							}
						}else{
							sqlsrv_close($conexion);
							echo "Error de ingreso. Fecha invalida.";
							}
					}					
				}else{
					$log=0;
					sqlsrv_close($conexion);
				}
			}else{
				$log=0;
				sqlsrv_close($conexion);
			}
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

          <!-- <h3 class="text-center text-muted font-weight-normal mb-4">Iniciar sesión</h3>-->

          <!-- Form -->
          <form name="frmLogin" id="frmLogin" class="mt-5" role="form" action="login.php" method="post" enctype="application/x-www-form-urlencoded">
            <div class="form-group">
				<label class="form-label">Base de datos</label>
				<select name="BaseDatos" id="BaseDatos" class="form-control">
					<option value="<?php echo BDPRO; ?>">Pruebas</option>
					<?php if(BDPRUEBAS!=""){?>
					<option value="<?php echo BDPRUEBAS; ?>"><?php echo BDPRUEBAS; ?></option>
					<?php }?>
				</select>
			</div>
			<div class="form-group">
              <label class="form-label">Usuario</label>
              <input name="User" type="text" autofocus required class="form-control" id="User" maxlength="50">
            </div>
			<div class="form-group">
				<label class="form-label d-flex justify-content-between align-items-end">
				  <div>Contrase&ntilde;a</div>
				  <a href="recordar_clave.php" class="d-block small">&iquest;Olvidaste tu contrase&ntilde;a?</a>
				</label>
				<input name="Password" type="password" required="" class="form-control" id="Password" maxlength="50" autocomplete="off">
			</div>
			<div class="d-flex justify-content-between align-items-center m-0">
				<label class="custom-control custom-checkbox m-0">
				  <input type="checkbox" class="custom-control-input">
				  <span class="custom-control-label">Recuerdame en este equipo</span>
				</label>				
		    </div>
			<div class="d-flex justify-content-between align-items-center m-0 mt-4">
				 <button type="submit" class="btn btn-primary btn-block">Ingresar</button>			
		    </div>			  
			  <input type="hidden" id="return_url" name="return_url" value="<?php if(isset($_GET['return_url'])){echo $_GET['return_url'];}?>" />
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
<?php if($log==0){?>
<script>
	$(document).ready(function(){
		toastr.error('Por favor compruebe su Usuario y Contraseña.','Error de ingreso');
	});
</script>
<?php }?>
<script>	
	 $(document).ready(function(){		
		  $("#frmLogin").validate();
	});
</script>
<?php include("includes/pie.php"); ?>

</body>

</html>