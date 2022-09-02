<?php require("includes/conexion.php"); 
PermitirAcceso(209);
if(isset($_POST['step'])&&$_POST['step']!=""){
	$Step=$_POST['step'];
}else{
	$Step=1;
}

//Lista de productos
$SQL_Productos=Seleccionar("uvw_Sap_tbl_ListaProductos","ItemCode, ItemName","");

if($Step==3){
	
	while($row_Productos=sqlsrv_fetch_array($SQL_Productos)){
		if((isset($_POST['ItemCode']))&&(strcmp($row_Productos['ItemCode'],$_POST['ItemCode'])==0)){
			$ItemName=$row_Productos['ItemName'];
			break;
		}
	}	
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo NOMBRE_PORTAL;?> | Cargar productos</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {//Cargar sucursales
		$("#Cliente").change(function(){
			$.ajax({
				type: "POST",
				url: "ajx_cboSucursal.php?id="+document.getElementById('Cliente').value,
				success: function(response){
					$('#Sucursal').html(response).fadeIn();
				}
			});
		});
	});
</script>
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
                    <h2>Cargar productos</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Gesti&oacute;n de archivos</a>
                        </li>
                         <li>
                            <a href="gestionar_productos.php">Gestionar productos</a>
                        </li>
                        <li class="active">
                            <strong>Cargar productos</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
          <div class="row">
           <div class="col-lg-12">
             <div class="ibox-content">
				 <div class="sk-spinner sk-spinner-wave">
					<div class="sk-rect1"></div>
					<div class="sk-rect2"></div>
					<div class="sk-rect3"></div>
					<div class="sk-rect4"></div>
					<div class="sk-rect5"></div>
				</div>
              <?php if($Step==1){?>
              <h2>Seleccione el producto para cargar la información</h2>
              <br>
               <form action="productos_add.php" method="post" class="form-horizontal" id="SeleccionarProducto">
               	<div class="form-group">
				  <label class="col-sm-1 control-label">Producto</label>
					<div class="col-sm-5">
						<select name="Producto" required class="form-control m-b chosen-select" id="Producto">
						  <option value="">Seleccione...</option>
						  <?php while($row_Productos=sqlsrv_fetch_array($SQL_Productos)){?>
						   <option value="<?php echo $row_Productos['ItemCode'];?>"><?php echo $row_Productos['ItemName'];?></option>
						   <?php }?>
						</select>
					</div>
				</div><br>
		   		<div class="form-group">
					<div class="col-sm-9">
						<button class="btn btn-primary" type="submit">Continuar <i class="fa fa-arrow-circle-right"></i></button> <a href="gestionar_productos.php" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
					</div>
				</div>
				<input type="hidden" id="step" name="step" value="2" />
			   </form>
              <?php }elseif($Step==2){
			    $temp=ObtenerVariable("CarpetaTmp");
				$route= $temp."/".$_SESSION['CodUser']."/";
				if(file_exists($route)){
					EliminarTemporal($route);
				}
			   ?>
               <form action="upload.php" class="dropzone" id="dropzoneForm">
					<div class="fallback">
						<input name="File" id="File" type="file" />
					</div>
			   </form>
			   <br><br>
			   <form action="productos_add.php" method="post" class="form-horizontal" id="AgregarArchivos">
			    	<div class="col-sm-9">
						<button class="btn btn-primary" type="submit">Continuar <i class="fa fa-arrow-circle-right"></i></button> <a href="gestionar_productos.php" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
					</div>
					<input type="hidden" id="step" name="step" value="3" />
			   		<input type="hidden" id="ItemCode" name="ItemCode" value="<?php echo $_POST['Producto'];?>" />
			   </form>
			   <?php }elseif($Step==3){?>
			   <form action="registro.php" method="post" class="form-horizontal" id="AgregarDatos">
              	 <h2>Ingresar información de los archivos</h2>
              	<br>
               	<div class="form-group">
				  <p class="text-primary">Producto:<br><strong><?php echo $ItemName;?></strong></p>
				</div>
	   			<br>
		   		<?php 
				$temp=ObtenerVariable("CarpetaTmp");
				$dir=$temp."/".$_SESSION['CodUser']."/";		   
				$route= opendir($dir);
			    //$directorio = opendir("."); //ruta actual
				$DocFiles=array();
				$i=0;
				while ($archivo = readdir($route)){ //obtenemos un archivo y luego otro sucesivamente
					if(($archivo == ".")||($archivo == "..")) continue;

					if (!is_dir($archivo)){//verificamos si es o no un directorio
						$peso = FormatUnitBytes(filesize($dir.$archivo));
						$DocFiles[$i]=$archivo;
						?>
					   <div class="col-lg-12">
					   		<div class="col-lg-2">
							   <div class="form-group">
									<div class="file-box">
										<div class="file">
											<a href="#">
												<span class="corner"></span>
												<div class="icon">
													<i class="fa fa-file"></i>
												</div>
												<div class="file-name truncate"><?php echo $archivo;?><br/><small><?php echo $peso;?></small></div>
											</a>
										</div>
									</div>
							   </div>
						   </div>
						   <?php 
						   //Categorias de productos
							$SQL_CatProductos=Seleccionar("uvw_tbl_CategoriasProductos","ID_CategoriaProductos, NombreCategoriaProductos","");	
						   ?>
						   <div class="col-lg-6">
							   <div class="form-group">
									<label class="col-sm-4 control-label">Categoria</label>
									<div class="col-sm-8">
									<select required name="Categoria<?php echo $i;?>" class="form-control m-b" id="Categoria<?php echo $i;?>">
									   		<option value="" selected="selected">Seleccione...</option>
									   <?php while($row_CatProductos=sqlsrv_fetch_array($SQL_CatProductos)){?>
											<option value="<?php echo $row_CatProductos['ID_CategoriaProductos'];?>"><?php echo $row_CatProductos['NombreCategoriaProductos'];?></option>
									  <?php }?>
									</select>
									</div>
							   </div>
							   <div class="form-group">
							   		<label class="col-sm-4 control-label">Fecha</label>
								 	<div class="col-sm-8">
									 	<div class="input-group date">
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="Fecha<?php echo $i;?>" type="text" required="required" class="form-control" id="Fecha<?php echo $i;?>" value="<?php echo date('d/m/Y');?>" readonly>
									 </div>
								 	</div>
							   </div>
							   <div class="form-group">
							   		<label class="col-sm-4 control-label">Comentarios</label>
									<div class="col-sm-8"><textarea name="Comentarios<?php echo $i;?>" rows="6" maxlength="1000" class="form-control" id="Comentarios<?php echo $i;?>" placeholder="Descripción del documento..."></textarea></div>
					   		   </div>
						   </div>
						</div><br><br>
						<?php 
						//echo $archivo." (".$peso.")"."<br />";
						$i++;
					}					
				}
				closedir($route);									   
			   ?>
			   
			   <br>
		   		<div class="form-group">
					<div class="col-sm-9">
						<button class="btn btn-primary" id="toggleSpinners" type="submit">Continuar <i class="fa fa-arrow-circle-right"></i></button> <a href="gestionar_productos.php" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
					</div>
				</div>
				<input type="hidden" id="P" name="P" value="17" />
	   			<input type="hidden" id="CantFiles" name="CantFiles" value="<?php echo $i;?>" />
	   			<input type="hidden" id="ItemCode" name="ItemCode" value="<?php echo $_POST['ItemCode'];?>" />
			   </form>
			   <?php }?>
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
 Dropzone.options.dropzoneForm = {
		paramName: "File", // The name that will be used to transfer the file
		maxFilesize: "<?php echo ObtenerVariable("MaxSizeFile");?>", // MB
	 	maxFiles: "<?php echo ObtenerVariable("CantidadArchivos");?>",
		uploadMultiple: true,
		addRemoveLinks: true,
		dictRemoveFile: "Quitar",
	 	acceptedFiles: "<?php echo ObtenerVariable("TiposArchivos");?>",
		dictDefaultMessage: "<strong>Haga clic aqui para cargar archivos</strong><br>Tambien puede arrastrarlos hasta aqui<br><h4><small>(máximo <?php echo ObtenerVariable("CantidadArchivos");?> archivos a la vez)<small></h4>",
		dictFallbackMessage: "Tu navegador no soporta cargue de archivos mediante arrastrar y soltar",
	 	removedfile: function(file) {
		  $.get( "includes/procedimientos.php", {
			type: "3",
		  	nombre: file.name
		  }).done(function( data ) {
		 	var _ref;
		  	return (_ref = file.previewElement) !== null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
		 	});
		 }
	};
</script>
<script>	
	 $(document).ready(function(){		
		  $("#AgregarDatos").validate();
		 
		  $(".truncate").dotdotdot({
            watch: 'window'
		  });
		  		 
		 $('.chosen-select').chosen({width: "100%"});
		<?php 
		 /*if($Step==3){
			$k=0;
		 	while($k<$i){?>  
			 $('#Fecha<?php echo $k;?>').datepicker({
					todayBtn: "linked",
					keyboardNavigation: false,
					forceParse: false,
					calendarWeeks: true,
					autoclose: true,
					format: 'dd/mm/yyyy'
				});
	 <?php $k++;}
		}*/?>
		 
		  $('.file-box').each(function() {
                animationHover(this, 'pulse');
            });
	});
	
	$(function(){
		$('#toggleSpinners').on('click', function(){
			$('.ibox-content').toggleClass('sk-loading');
		})
	});
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>