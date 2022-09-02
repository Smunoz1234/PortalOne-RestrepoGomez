<?php require_once("includes/conexion.php");

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Inicio | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
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
</style>

<script>
$(document).ready(function(){
	<?php if(!isset($_SESSION['SetCookie'])||($_SESSION['SetCookie']=="")){?>
		$('#myModal').modal("show");
	<?php }?>
	
	$("#Sucursal").change(function(){
		$('.ibox-content').toggleClass('sk-loading',true);
		var Sucursal=document.getElementById('Sucursal').value;
		$.ajax({
			type: "POST",
			url: "ajx_cbo_select.php?type=26&id="+Sucursal+"&tdoc=191",
			success: function(response){
				$('#Series').html(response).fadeIn();
				$('.ibox-content').toggleClass('sk-loading',false);
			}
		});	
	});
});
</script>
<!-- InstanceEndEditable -->
</head>

<body class="mini-navbar">

<div id="wrapper">

    <?php include_once("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
      
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
						<?php echo $Contenido;?>
					</div>

					<div class="modal-footer">
						<button type="button" onClick="AceptarAcuerdo();" class="btn btn-primary" data-dismiss="modal">Acepto los t&eacute;rminos</button>
					</div>
				</div>
			</div>
		</div>
        <div class="row page-wrapper wrapper-content animated fadeInRight">
			<div class="row m-b-md">
				<div class="col-lg-12">
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<h2>Bienvenido <?php echo $_SESSION['NomUser'];?></h2>
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
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>