<?php 
require("includes/conexion.php"); 

if(isset($_POST['frmLoad'])&&$_POST['frmLoad']=="frmCargarImagen"){
	$ext=end(explode(".",$_FILES['FileLoad']['name']));
	if(($ext!="png")&&($ext!="jpg")){
		echo "Solo archivos con extensión: <b>.png, .jpg</b>";
	}else{
		if($_POST['id']==1){//Cargar Logo de la empresa
			move_uploaded_file($_FILES['FileLoad']['tmp_name'],"img/img_tmp/img_logo.png");
		?>
		<script>
			opener.document.getElementById("ImgLogoEmpresa").src="img/img_tmp/img_logo.png#" + new Date();
			self.close();
		</script>
		<?php
		}elseif($_POST['id']==2){//Cargar Logo Slim de la empresa
			move_uploaded_file($_FILES['FileLoad']['tmp_name'],"img/img_tmp/img_logo_slim.png");
		?>
		<script>
			opener.document.getElementById("ImgLogoSlimEmpresa").src="img/img_tmp/img_logo_slim.png#" + new Date();
			self.close();
		</script>
		<?php
		}elseif($_POST['id']==3){//Cargar Favicon
			move_uploaded_file($_FILES['FileLoad']['tmp_name'],"img/img_tmp/favicon.png");
		?>
		<script>
			opener.document.getElementById("ImgFavicon").src="img/img_tmp/favicon.png#" + new Date();
			self.close();
		</script>
		<?php
		}elseif($_POST['id']==4){//Cargar fondo pantalla inicio
			move_uploaded_file($_FILES['FileLoad']['tmp_name'],"img/img_tmp/img_background.jpg");
		?>
		<script>
			opener.document.getElementById("ImgFondo").src="img/img_tmp/img_background.jpg#" + new Date();
			self.close();
		</script>
		<?php
		}
	}
}else{

?>
<!doctype html>
<html>
<head>
<?php include("includes/cabecera.php"); ?>
<title><?php echo NOMBRE_PORTAL;?> | Subir imagen</title>
</head>

<body>
  <div class="wrapper wrapper-content">
          <div class="row">
           <div class="col-lg-12">
				<form action="cargar_imagen.php" name="frmCargarImagen" id="frmCargarImagen" method="post" enctype="multipart/form-data">
					<div class="fileinput fileinput-new input-group" data-provides="fileinput">
						<div class="form-control" data-trigger="fileinput">
							<i class="glyphicon glyphicon-file fileinput-exists"></i>
						<span class="fileinput-filename"></span>
						</div>
						<span class="input-group-addon btn btn-default btn-file">
							<span class="fileinput-new">Seleccionar</span>
							<span class="fileinput-exists">Cambiar</span>
							<input type="file" name="FileLoad" id="FileLoad" required />
						</span>
						<a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Quitar</a>
					</div>
					<input type="hidden" name="frmLoad" id="frmLoad" value="frmCargarImagen" />
					<input type="hidden" name="id" id="id" value="<?php echo $_GET['id'];?>" />
					<button type="submit" class="btn btn-w-m btn-success"><i class="fa fa-file-photo-o"></i> Cargar imagen</button>
				</form>
			</div>
	  </div>
	</div>
<script>	
	 $(document).ready(function(){		
		  $("#frmCargarImagen").validate();
	});
</script>
</body>
</html>
<?php }
	sqlsrv_close($conexion); 
?>