<?php 
require("includes/conexion.php"); 
PermitirAcceso(204);
if(isset($_POST['step'])&&$_POST['step']!=""){
	$Step=$_POST['step'];
}else{
	$Step=2;
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo NOMBRE_PORTAL;?> | Cargar archivo .csv</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Cargar archivo .csv</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Administraci&oacute;n</a>
                        </li>
                        <li>
                           <a href="cargue_masivo_productos.php">Cargar productos masivos</a>
                        </li>
                        <li class="active">
                            <strong>Cargar archivo .csv</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
          <div class="row">
           <div class="col-lg-12">
             <div class="ibox-content">
             <form action="registro.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="AgregarArchivos" onSubmit="return ComprobarExt();">
				<div class="row">
					<div class="col-lg-8">
					<h3>Seleccione el archivo para cargar (extensión .csv)</h3><br>
						<div class="fileinput fileinput-new input-group" data-provides="fileinput">
							<div class="form-control" data-trigger="fileinput">
								<i class="glyphicon glyphicon-file fileinput-exists"></i>
							<span class="fileinput-filename"></span>
							</div>
							<span class="input-group-addon btn btn-default btn-file">
								<span class="fileinput-new">Seleccionar</span>
								<span class="fileinput-exists">Cambiar</span>
								<input name="FileArchivo" type="file" required="required" id="FileArchivo" />
							</span>
							<a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Quitar</a>
						</div> 
					</div>					
				</div>
	   			<div class="row">
					<label class="col-lg-1 control-label">Delimitador</label>
					<div class="col-lg-1">
						<input name="Delimiter" type="text" required="required" class="form-control" id="Delimiter" value=";" maxlength="1">
					</div>				
			   </div><br>
		   		<div class="row">
					<div class="col-lg-12">
						<a href="Templates/PlantillaCargueArchivos.xlsx" class="btn btn-default btn-xs"><i class="fa fa-download"></i> Descargar plantilla</a>
					</div>
				</div>
				<br><br>
			   <div class="row">
				   <div class="col-lg-12">
						<div class="col-sm-9">
							<button class="btn btn-primary" type="submit">Continuar <i class="fa fa-arrow-circle-right"></i></button> <a href="cargue_masivo_productos.php" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
						</div>
						<input type="hidden" id="P" name="P" value="23" />
				   </div>
			   </div>
			   </form>
			   </div>
		   </div>
          </div>
        </div>
        <!-- InstanceEndEditable -->
        <?php include("includes/footer.php"); ?>

    </div>
</div>
<?php include("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>
$(document).ready(function(){
	$("#AgregarArchivos").validate();
});
</script>
<script>	
	function ComprobarExt(){
		var form=document.getElementById("AgregarArchivos");
		var archivo=document.getElementById("FileArchivo").value;
		var ext=".csv";
		var permitido=false;
		if(archivo!=""){
			var ext_archivo=(archivo.substring(archivo.lastIndexOf("."))).toLowerCase();
			if(ext_archivo==ext){
				permitido=true;
			}
			if(!permitido){
				$(document).ready(function(){
					swal({
						title: '¡Error!',
						text: 'El archivo que intenta cargar no es extensión .csv',
						type: 'error'
					});
				});
				return false;
			}else{
				return true;
			}
		}else{
			return true;
		}
	}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>