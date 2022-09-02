<?php require_once("includes/conexion.php"); 
PermitirAcceso(208);
if(isset($_POST['step'])&&$_POST['step']!=""){
	$Step=$_POST['step'];
}else{
	$Step=1;
}

//Clientes
if(PermitirFuncion(205)){
	$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes","CodigoCliente, NombreCliente","");	
}else{
	$Where="ID_Usuario = ".$_SESSION['CodUser'];
	$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);	
}

if($Step==3){
	//Sucursales
	if(PermitirFuncion(205)){
		$Where="CodigoCliente='".$_POST['CodigoCliente']."'";
		$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","NombreSucursal",$Where);
	}else{
		$Where="CodigoCliente='".$_POST['CodigoCliente']."' and ID_Usuario = ".$_SESSION['CodUser'];
		$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal",$Where);	
	}
	
	$ListSucursales=array();
	$j=0;
	while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){
		$ListSucursales[$j]=$row_Sucursal['NombreSucursal'];
		$j++;
	}
	
	while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){
		if((isset($_POST['CodigoCliente']))&&(strcmp($row_Cliente['CodigoCliente'],$_POST['CodigoCliente'])==0)){
			$NombreCliente=$row_Cliente['NombreCliente'];
			break;
		}
	}	
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Cargar informes | <?php echo NOMBRE_PORTAL;?></title>
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

    <?php include_once("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Cargar informes</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Gesti&oacute;n de archivos</a>
                        </li>
                         <li>
                            <a href="gestionar_informes.php">Gestionar informes</a>
                        </li>
                        <li class="active">
                            <strong>Cargar informes</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
          <div class="row">
           <div class="col-lg-12">
             <div class="ibox-content">
				 <?php include("includes/spinner.php"); ?>
              <?php if($Step==1){?>
               <form action="informes_add.php" method="post" class="form-horizontal" id="SeleccionarCliente">
				<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-cloud-upload"></i> Seleccione el cliente para cargar la información</h3></label>
				</div>
               	<div class="form-group">
				  <label class="col-sm-1 control-label">Cliente</label>
					<div class="col-sm-6">
						<select name="Cliente" required class="form-control select2" id="Cliente">
						  <option value="">Seleccione...</option>
						  <?php while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){?>
						   <option value="<?php echo $row_Cliente['CodigoCliente'];?>"><?php echo $row_Cliente['NombreCliente'];?></option>
						   <?php }?>
						</select>
					</div>
				</div><br>
		   		<div class="form-group">
					<div class="col-sm-9">
						<button class="btn btn-primary" type="submit">Continuar <i class="fa fa-arrow-circle-right"></i></button> <a href="gestionar_informes.php" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
					</div>
				</div>
				<input type="hidden" id="step" name="step" value="2" />
			   </form>
              <?php }elseif($Step==2){
			    LimpiarDirTemp();
			   ?>
              	<div class="row">
					<div class="col-lg-12">
					   <form action="upload.php" class="dropzone" id="dropzoneForm">
							<div class="fallback">
								<input name="File" id="File" type="file" />
							</div>
					   </form>
			   		</div>
				</div>
			   <br><br>
			   <div class="row">
				   <div class="col-lg-12">
					   <form action="informes_add.php" method="post" class="form-horizontal" id="AgregarArchivos">
							<div class="col-sm-9">
								<button class="btn btn-primary" type="submit">Continuar <i class="fa fa-arrow-circle-right"></i></button> <a href="gestionar_informes.php" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
							</div>
							<input type="hidden" id="step" name="step" value="3" />
							<input type="hidden" id="CodigoCliente" name="CodigoCliente" value="<?php echo $_POST['Cliente'];?>" />
					   </form>
					</div>
			   </div>
			   <?php }elseif($Step==3){?>
			   <form action="registro.php" method="post" class="form-horizontal" id="AgregarDatos">
              	<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Ingresar información de los archivos</h3></label>
				</div>
               	<div class="form-group">
				  <p class="col-lg-12 text-primary">Cliente:<br><strong><?php echo $NombreCliente;?></strong></p>
				</div>
	   			<br>
		   		<?php 
				$temp=ObtenerVariable("CarpetaTmp");
				$dir=$temp."/".$_SESSION['CodUser']."/";		   
				$ruta= opendir($dir);
				$DocFiles=array();
				$i=0;
				while ($archivo = readdir($ruta)){ //obtenemos un archivo y luego otro sucesivamente
					if(($archivo == ".")||($archivo == "..")) continue;

					if (!is_dir($archivo)){//verificamos si es o no un directorio
						$peso = FormatUnitBytes(filesize($dir.$archivo));
						$DocFiles[$i]=$archivo;
						$exp=explode('.',$archivo);
						$Ext = end($exp);
						$Icon=IconAttach($Ext);
						?>
					   <div class="col-lg-12">
					   		<div class="col-lg-2">
							   <div class="form-group">
									<div class="file-box">
										<div class="file">
											<a href="#">
												<div class="icon">
													<i class="<?php echo $Icon;?>"></i>
												</div>
												<div class="file-name">
													<?php echo $archivo;?>
													<br/>
													<small><?php echo $peso;?></small>
												</div>
											</a>
										</div>
									</div>
							   </div>
						   </div>
						   <div class="col-lg-6">
							   <div class="form-group">
									<label class="col-sm-4 control-label">Sucursal</label>
									<div class="col-sm-8">
										<select id="Sucursal<?php echo $i;?>" name="Sucursal<?php echo $i;?>[]" data-placeholder="(Todos)" class="form-control select2" multiple>
										<?php 
											foreach ($ListSucursales as $NombreSuc) {?>		
												<option value="<?php echo $NombreSuc;?>"><?php echo $NombreSuc;?></option>
										<?php }?>
										</select>
									</div>
							   </div>
							   <div class="form-group">
									<label class="col-sm-4 control-label">Categoria</label>
									<div class="col-sm-8">
								 	<?php 
									$Cons_Menu="Select * From uvw_tbl_Categorias Where ID_Padre=0 and EstadoCategoria=1 and ID_TipoCategoria=2";
									$SQL_Menu=sqlsrv_query($conexion,$Cons_Menu,array(),array( "Scrollable" => 'static' ));
									$Num_Menu=sqlsrv_num_rows($SQL_Menu);
									?>
									<select required name="Categoria<?php echo $i;?>" class="form-control" id="Categoria<?php echo $i;?>">
									   <option value="" selected="selected">Seleccione...</option>
									   <?php 
										while($row_Menu=sqlsrv_fetch_array($SQL_Menu)){
											echo "<optgroup label='".$row_Menu['NombreCategoria']."'>";

											$Cons_MenuLvl2="Select * From uvw_tbl_Categorias Where ID_Padre=".$row_Menu['ID_Categoria']." and EstadoCategoria=1";
											$SQL_MenuLvl2=sqlsrv_query($conexion,$Cons_MenuLvl2,array(),array( "Scrollable" => 'static' ));
											$Num_MenuLvl2=sqlsrv_num_rows($SQL_MenuLvl2);

											if($Num_MenuLvl2>=1){
												while($row_MenuLvl2=sqlsrv_fetch_array($SQL_MenuLvl2)){
													$Cons_MenuLvl3="Select * From uvw_tbl_Categorias Where ID_Padre=".$row_MenuLvl2['ID_Categoria']." and EstadoCategoria=1";
													$SQL_MenuLvl3=sqlsrv_query($conexion,$Cons_MenuLvl3,array(),array( "Scrollable" => 'static' ));
													$Num_MenuLvl3=sqlsrv_num_rows($SQL_MenuLvl3);

													if($Num_MenuLvl3>=1){
														echo "<optgroup label='".$row_MenuLvl2['NombreCategoria']."'>";
														while($row_MenuLvl3=sqlsrv_fetch_array($SQL_MenuLvl3)){
															echo "<option value='".$row_MenuLvl3['ID_Categoria']."'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$row_MenuLvl3['NombreCategoria']."</option>";
														}
														echo "</optgroup>";
													}else{
														echo "<option value='".$row_MenuLvl2['ID_Categoria']."'>&nbsp;&nbsp;&nbsp;".$row_MenuLvl2['NombreCategoria']."</option>";
													}
												}
											}
											echo "</optgroup>";
										 }?>
									</select>
									</div>
							   </div>
							   <div class="form-group">
							   		<label class="col-sm-4 control-label">Fecha</label>
								 	<div class="col-sm-8">
									 	<div class="input-group date">
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="Fecha<?php echo $i;?>" type="text" required="required" class="form-control" id="Fecha<?php echo $i;?>" value="<?php echo date('Y-m-d');?>" readonly>
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
				closedir($ruta);									   
			   ?>
			   
			   <br>
		   		<div class="form-group">
					<div class="col-sm-9">
						<button class="btn btn-primary" id="toggleSpinners" type="submit">Continuar <i class="fa fa-arrow-circle-right"></i></button> <a href="gestionar_informes.php" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
					</div>
				</div>
				<input type="hidden" id="P" name="P" value="9" />
	   			<input type="hidden" id="CantFiles" name="CantFiles" value="<?php echo $i;?>" />
		   		<input type="hidden" id="CodigoCliente" name="CodigoCliente" value="<?php echo $_POST['CodigoCliente'];?>" />
			   </form>
			   <?php }?>
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
		  $("#AgregarDatos").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
		 
		  $(".truncate").dotdotdot({
            watch: 'window'
		  });
		  		 
		 $(".select2").select2();
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
					format: 'yyyy-mm-dd',
					todayHighlight: true
				});
	 <?php $k++;}
		}*/?>
		 
		  $('.file-box').each(function() {
                animationHover(this, 'pulse');
            });
	});
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>