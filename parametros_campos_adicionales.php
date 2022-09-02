<?php 
require_once("includes/conexion.php");
PermitirAcceso(217);

$sw_error=0;
$msg_error="";

//Crear nuevo parametro
if(isset($_POST['MM_Insert'])&&($_POST['MM_Insert']!="")){
	$Param=array(
		"'".$_POST['id']."'",
		"'".$_POST['TipoDoc']."'",
		"'".$_POST['NombreCampo']."'",
		"'".$_POST['LabelCampo']."'",
		"'".$_POST['TipoCampo']."'",
		"'".$_POST['type']."'"
	);
	$SQL=EjecutarSP('sp_tbl_CamposAdicionalesDoc',$Param);
	if($SQL){
		$a=($_POST['type']==1) ? "OK_NewParam" : "OK_UpdParam";
		header('Location:parametros_campos_adicionales.php?doc='.base64_encode($_POST['doc']).'&a='.base64_encode($a));
	}else{
		$sw_error=1;
		$msg_error="No se pudo insertar el nuevo parámetro";
	}
}

$SQL_TipoDoc=Seleccionar("uvw_tbl_ObjetosSAP","*",'','CategoriaObjeto, DeTipoDocumento');

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Parámetros campos adicionales | <?php echo NOMBRE_PORTAL;?></title>
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
                text: 'El nuevo campo ha sido agregado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_UpdParam"))){
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

<script>
$(document).ready(function() {
	$("#TipoDocumento").change(function(event){
		$('.ibox-content').toggleClass('sk-loading',true);
		var TDoc = document.getElementById("TipoDocumento").value;
		$.ajax({
			type: "POST",
			url: "parametros_campos_adicionales_detalle.php",
			data:{
				obj:btoa(TDoc)
			},
			success: function(response){
				$('#Result').html(response);
				$('.ibox-content').toggleClass('sk-loading',false);
			}
		});
	});

});
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
                    <h2>Parámetros campos adicionales</h2>
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
                            <strong>Parámetros campos adicionales</strong>
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
			 <form action="" method="post" id="frmParam" class="form-horizontal">			 
			 <div class="row">
				<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-plus-square"></i> Acciones</h3></label>
						</div>
						<div class="form-group">
							<div class="col-lg-6">
								<button class="btn btn-warning" type="button" id="NewParam" onClick="CrearCampo();"><i class="fa fa-plus-circle"></i> Crear nuevo campo</button>  
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
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-check-square-o"></i> Lista de campos adicionales por documento</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Tipo de documento</label>
							<div class="col-lg-3">
								<select name="TipoDocumento" class="form-control" id="TipoDocumento">
										<option value="">Seleccione...</option>
								  <?php $CatActual="";
									while($row_TipoDoc=sqlsrv_fetch_array($SQL_TipoDoc)){
										if($CatActual!=$row_TipoDoc['CategoriaObjeto']){
											echo "<optgroup label='".$row_TipoDoc['CategoriaObjeto']."'></optgroup>";
											$CatActual=$row_TipoDoc['CategoriaObjeto'];
										}
									?>
										<option value="<?php echo $row_TipoDoc['IdTipoDocumento'];?>" <?php if((isset($_GET['doc']))&&(strcmp($row_TipoDoc['IdTipoDocumento'],base64_decode($_GET['doc']))==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoDoc['DeTipoDocumento'];?></option>
								  <?php }?>
								</select>
							</div>
						</div>	
						<div id="Result"></div>
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
		
		<?php if(isset($_GET['doc'])){?>
		$("#TipoDocumento").trigger('change');
		<?php }?>
	});
</script>
<script>
function CrearCampo(){
	$('.ibox-content').toggleClass('sk-loading',true);
	
	$.ajax({
		type: "POST",
		url: "md_crear_campos_adicionales.php",
		data:{
			doc:document.getElementById("TipoDocumento").value
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		}
	});
}	
function EditarCampo(id){
	$('.ibox-content').toggleClass('sk-loading',true);
	
	$.ajax({
		type: "POST",
		url: "md_crear_campos_adicionales.php",
		data:{
			doc:document.getElementById("TipoDocumento").value,
			id:id,
			edit:1
		},
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