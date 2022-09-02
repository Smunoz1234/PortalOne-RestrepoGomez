<?php require_once("includes/conexion.php"); 
PermitirAcceso(201);

//Tipos de categorias
$SQL_TipoCategoria=Seleccionar("uvw_tbl_TipoCategoria","ID_TipoCategoria, TipoCategoria","");

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Agregar categor&iacute;a | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#TipoCategoria").change(function(){
			var TipoCat=document.getElementById('TipoCategoria').value;
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:13,TipoCat:TipoCat},
				dataType:'json',
				success: function(data){
					document.getElementById('URL').value=data.URL;
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
                    <h2>Agregar categor&iacute;a</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Administraci&oacute;n</a>
                        </li>
                        <li>
                            <a href="gestionar_categorias.php">Gestionar categor&iacute;as</a>
                        </li>
                        <li class="active">
                            <strong>Agregar categor&iacute;a</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			<div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
          		<div class="row">
					<div class="ibox">
						<div class="ibox-title bg-success">
							<h5><i class="fa fa-tags"></i> Categorias</h5>
							 <a class="collapse-link pull-right">
								<i class="fa fa-chevron-up"></i>
							</a>	
						</div>		
						<div class="ibox-content">
							 <div class="col-lg-6">
							  <form action="registro.php" method="post" class="form-horizontal" id="AgregarCategoria">
								<div class="form-group">
									<label class="col-sm-3 control-label">Nombre categor&iacute;a</label>
									<div class="col-sm-9"><input name="NombreCategoria" type="text" required="required" class="form-control" id="NombreCategoria"></div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Men&uacute; padre</label>
									<div class="col-sm-9">
									 <?php 
										$Cons_Menu="Select * From uvw_tbl_Categorias Where ID_Padre=0 and EstadoCategoria=1";
										$SQL_Menu=sqlsrv_query($conexion,$Cons_Menu,array(),array( "Scrollable" => 'static' ));
										$Num_Menu=sqlsrv_num_rows($SQL_Menu);
									 ?>
										<select name="IDPadre" class="form-control m-b" id="IDPadre">
										   <option value="0" selected="selected">(Ninguno)</option>
										   <?php 
											while($row_Menu=sqlsrv_fetch_array($SQL_Menu)){
												echo "<option value='".$row_Menu['ID_Categoria']."'>".$row_Menu['NombreCategoria']."</option>";

												$Cons_MenuLvl2="Select * From uvw_tbl_Categorias Where ID_Padre=".$row_Menu['ID_Categoria']." and EstadoCategoria=1";
												$SQL_MenuLvl2=sqlsrv_query($conexion,$Cons_MenuLvl2,array(),array( "Scrollable" => 'static' ));
												$Num_MenuLvl2=sqlsrv_num_rows($SQL_MenuLvl2);

												if($Num_MenuLvl2>=1){			
													while($row_MenuLvl2=sqlsrv_fetch_array($SQL_MenuLvl2)){
														$Cons_MenuLvl3="Select * From uvw_tbl_Categorias Where ID_Padre=".$row_MenuLvl2['ID_Categoria']." and EstadoCategoria=1";
														$SQL_MenuLvl3=sqlsrv_query($conexion,$Cons_MenuLvl3,array(),array( "Scrollable" => 'static' ));
														$Num_MenuLvl3=sqlsrv_num_rows($SQL_MenuLvl3);

														echo "<option value='".$row_MenuLvl2['ID_Categoria']."'>&nbsp;&nbsp;&nbsp;".$row_MenuLvl2['NombreCategoria']."</option>";

														if($Num_MenuLvl3>=1){
															while($row_MenuLvl3=sqlsrv_fetch_array($SQL_MenuLvl3)){
																echo "<option value='".$row_MenuLvl3['ID_Categoria']."'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$row_MenuLvl3['NombreCategoria']."</option>";
															}
														}
													}
												}	
											 }?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Estado</label>
									<div class="col-sm-9">
										<select name="EstadoCategoria" class="form-control m-b" id="EstadoCategoria">
										   <option value="1" selected="selected">Activo</option>
										   <option value="2">Inactivo</option>
										</select>
								  </div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Tipo categor&iacute;a</label>
									<div class="col-sm-9">
										<select name="TipoCategoria" class="form-control m-b" id="TipoCategoria">
										   <?php while($row_TipoCategoria=sqlsrv_fetch_array($SQL_TipoCategoria)){?>
												<option value="<?php echo $row_TipoCategoria['ID_TipoCategoria'];?>"><?php echo $row_TipoCategoria['TipoCategoria'];?></option>
										  <?php }?>
										</select>
								  </div>
								</div>
								<div class="form-group">
									<label class="col-lg-1 control-label">Proyectos</label>
									<div class="col-lg-4">
										<select data-placeholder="Digite para buscar..." name="Proyecto[]" class="form-control select2" id="Proyecto" multiple>
											<?php while($row_Proyectos=sqlsrv_fetch_array($SQL_Proyectos)){?>
												<option value="<?php echo $row_Proyectos['IdProyecto'];?>" <?php if($edit==1){ if((strcmp($row_Proyectos['IdProyecto'],$row_ProyectosUsuario['IdProyecto'])==0)){ echo "selected=\"selected\"";$row_ProyectosUsuario=sqlsrv_fetch_array($SQL_ProyectosUsuario);}}?>><?php echo $row_Proyectos['DeProyecto'];?></option>
											<?php }?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Mostrar en Dashboard</label>
									<div class="col-sm-9">
										<select name="MostrarDashboard" class="form-control m-b" id="MostrarDashboard">
										   <option value="1" selected="selected">SI</option>
										   <option value="0">NO</option>
										</select>
								  </div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">URL</label>
									<div class="col-sm-9">
										<input name="URL" type="text" class="form-control" id="URL">
										<span class="form-text m-b-none text-muted">Si es un menú padre, ingrese solo el signo <span class="font-bold">#</span></span>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Parámetros adicionales</label>
									<div class="col-sm-9">
										<input name="ParamAdicionales" type="text" class="form-control" id="ParamAdicionales" value="">
									</div>
								</div>
								<div class="form-group">
									<div class="col-sm-9">
										<button class="alkin btn btn-primary " type="submit"><i class="fa fa-check"></i>&nbsp;Agregar</button> <a href="gestionar_categorias.php" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
									</div>
								</div>
								<input type="hidden" id="P" name="P" value="1" />
							  </form>
		   </div>
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
		 $("#AgregarCategoria").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
		  $(".alkin").on('click', function(){
				 $('.ibox-content').toggleClass('sk-loading');
			});	
		 $('#TipoCategoria').trigger('change');
	});
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>