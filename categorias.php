<?php require_once("includes/conexion.php");
PermitirAcceso(201);

$msg_error="";//Mensaje del error
$sw_error=0;
$IdCategoria=0;


if(isset($_GET['id'])&&($_GET['id']!="")){
	$IdCategoria=base64_decode($_GET['id']);
}
if(isset($_GET['tl'])&&($_GET['tl']!="")){//0 Si se está creando. 1 Se se está editando.
	$edit=$_GET['tl'];
}elseif(isset($_POST['tl'])&&($_POST['tl']!="")){
	$edit=$_POST['tl'];
}else{
	$edit=0;
}

if($edit==0){
	$Title="Crear categoria";
}else{
	$Title="Editar categoria";
}

if(isset($_POST['P'])&&($_POST['P']!="")){
	try{
		
		$IdCategoria="NULL";
		$Proyecto="";
		$Type=1;
		
		if($_POST['edit']==1){//Actualizando			
			$IdCategoria="'".$_POST['ID']."'";
			$Type=2;
		}
		
		$ParamInsCat=array(
			$IdCategoria,
			"'".$_POST['NombreCategoria']."'",
			"'".$_POST['EstadoCategoria']."'",
			"'".$_POST['IDPadre']."'",
			"'".$_POST['URL']."'",
			"'".$_POST['ParamAdicionales']."'",
			"'".$_POST['MostrarDashboard']."'",
			"'".$_POST['TipoCategoria']."'",
			"'".$_SESSION['CodUser']."'",
			$Type
		);
		$SQL_InsCat=EjecutarSP('sp_tbl_Categorias',$ParamInsCat,$_POST['P']);
		
		if($SQL_InsCat){
			if($_POST['edit']==1){
				$ID=$_POST['ID'];
			}else{
				$row_InsCat=sqlsrv_fetch_array($SQL_InsCat);	
				$ID=$row_InsCat[0];
			}
			
			//Proyectos
			#Primero limpiamos los proyectos
			$ParamDeleteProy=array(
				"'".$ID."'",
				"NULL",
				"2"
			);
			$SQL_DeleteProy=EjecutarSP('sp_tbl_Categorias_Proyectos',$ParamDeleteProy,$_POST['P']);

			#Insertamos los proyectos
			if($SQL_DeleteProy){
				$i=0;
				$CuentaProy=count($_POST['Proyecto']);
				while($i<$CuentaProy){
					if($_POST['Proyecto'][$i]!=""){
						$ParamProy=array(
							"'".$ID."'",
							"'".$_POST['Proyecto'][$i]."'",
							"1"
						);
						$SQL_Proy=EjecutarSP('sp_tbl_Categorias_Proyectos',$ParamProy,$_POST['P']);
						if(!$SQL_Proy){
							$sw_error=1;
							$msg_error="No se pudo insertar el proyecto";
						}
					}
					$i++;
				}
			}
		
			
			($_POST['edit']==1) ? $MsgReturn="OK_Cat_edit" : $MsgReturn="OK_Cat";
			
			sqlsrv_close($conexion);
			header('Location:gestionar_categorias.php?a='.base64_encode($MsgReturn));
		}else{
			$sw_error=1;
			$msg_error="Ha ocurrido un error al insertar la categoria";
		}
	}catch (Exception $e) {
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
}

if($edit==1){//Editar categoria
	$SQL=Seleccionar('uvw_tbl_Categorias','*',"ID_Categoria='".$IdCategoria."'");
	$row=sqlsrv_fetch_array($SQL);
	
	//Proyetos asociados
	$ProyectosCat=array();
	$SQL_ProyectosCat=Seleccionar("tbl_Categorias_Proyectos","IdProyecto","[ID_Categoria]='".$IdCategoria."'");
	while($row_ProyectosCat=sqlsrv_fetch_array($SQL_ProyectosCat)){
		array_push($ProyectosCat,$row_ProyectosCat['IdProyecto']);
	}	
}

//Tipos de categorias
$SQL_TipoCategoria=Seleccionar("uvw_tbl_TipoCategoria","ID_TipoCategoria, TipoCategoria","");

//Proyectos
$SQL_Proyectos=Seleccionar('uvw_Sap_tbl_Proyectos','*','','DeProyecto');

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $Title;?> | <?php echo NOMBRE_PORTAL;?></title>
<style>
	.ibox-title a{
		color: inherit !important;
	}
	.modal-dialog{
		width: 50% !important;
	}
	.modal-footer{
		border: 0px !important;
	}
</style>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Lo sentimos!',
                text: '".LSiqmlObs($msg_error)."',
                icon: 'warning'
            });
		});		
		</script>";
}
?>
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
<script>
function Eliminar(){
	Swal.fire({
		title: "Eliminar",
		text: "¿Está seguro que desea eliminar esta categoría?",
		icon: "warning",
		showCancelButton: true,
		confirmButtonText: "Si, estoy seguro",
		cancelButtonText: "Cancelar",
	}).then((result) => {
		if (result.isConfirmed) {
			$('.ibox-content').toggleClass('sk-loading');
			location.href='registro.php?P=3&id=<?php echo $row['ID_Categoria'];?>';
		}
	});
}
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
                    <h2><?php echo $Title;?></h2>
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
                            <strong><?php echo $Title;?></strong>
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
							  <form action="categorias.php" method="post" class="form-horizontal" id="AgregarCategoria">
								<div class="form-group">
									<label class="col-sm-3 control-label">Nombre categor&iacute;a</label>
									<div class="col-sm-9"><input name="NombreCategoria" type="text" required="required" class="form-control" id="NombreCategoria" value="<?php if($edit==1){echo $row['NombreCategoria'];}?>" autocomplete="off"></div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Men&uacute; padre</label>
									<div class="col-sm-9">
									 <?php 
										$SQL_Menu=Seleccionar("uvw_tbl_Categorias","*","ID_Padre=0 and EstadoCategoria=1");
										$Num_Menu=sqlsrv_num_rows($SQL_Menu);
									 ?>
										<select name="IDPadre" class="form-control" id="IDPadre">
										   <option value="0">(Ninguno)</option>
										   <?php 
											while($row_Menu=sqlsrv_fetch_array($SQL_Menu)){
												if((isset($row['ID_Padre']))&&(strcmp($row_Menu['ID_Categoria'],$row['ID_Padre'])==0)){
													echo "<option value='".$row_Menu['ID_Categoria']."' selected='selected'>".$row_Menu['NombreCategoria']."</option>";
												}else{
													echo "<option value='".$row_Menu['ID_Categoria']."'>".$row_Menu['NombreCategoria']."</option>";
												}
												$SQL_MenuLvl2=Seleccionar("uvw_tbl_Categorias","*","ID_Padre=".$row_Menu['ID_Categoria']." and EstadoCategoria=1");
												$Num_MenuLvl2=sqlsrv_num_rows($SQL_MenuLvl2);

												if($Num_MenuLvl2>=1){
													while($row_MenuLvl2=sqlsrv_fetch_array($SQL_MenuLvl2)){
														$SQL_MenuLvl3=Seleccionar("uvw_tbl_Categorias","*","ID_Padre=".$row_MenuLvl2['ID_Categoria']." and EstadoCategoria=1");
														$Num_MenuLvl3=sqlsrv_num_rows($SQL_MenuLvl3);

														if((isset($row['ID_Padre']))&&(strcmp($row_MenuLvl2['ID_Categoria'],$row['ID_Padre'])==0)){
															echo "<option value='".$row_MenuLvl2['ID_Categoria']."' selected='selected'>&nbsp;&nbsp;&nbsp;".$row_MenuLvl2['NombreCategoria']."</option>";
														}else{
															echo "<option value='".$row_MenuLvl2['ID_Categoria']."'>&nbsp;&nbsp;&nbsp;".$row_MenuLvl2['NombreCategoria']."</option>";
														}

														if($Num_MenuLvl3>=1){
															while($row_MenuLvl3=sqlsrv_fetch_array($SQL_MenuLvl3)){
																if((isset($row['ID_Padre']))&&(strcmp($row_MenuLvl3['ID_Categoria'],$row['ID_Padre'])==0)){
																	echo "<option value='".$row_MenuLvl3['ID_Categoria']."' selected='selected'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$row_MenuLvl3['NombreCategoria']."</option>";
																}else{
																	echo "<option value='".$row_MenuLvl3['ID_Categoria']."'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$row_MenuLvl3['NombreCategoria']."</option>";
																}

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
										<select name="EstadoCategoria" class="form-control" id="EstadoCategoria">
										   <option value="1" <?php if($edit==1){if($row['EstadoCategoria']==1){echo "selected='selected'";}}?>>Activo</option>
										   <option value="2" <?php if($edit==1){if($row['EstadoCategoria']==2){echo "selected='selected'";}}?>>Inactivo</option>
										</select>
								  </div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Tipo categor&iacute;a</label>
									<div class="col-sm-9">
										<select name="TipoCategoria" class="form-control" id="TipoCategoria">
										   <?php while($row_TipoCategoria=sqlsrv_fetch_array($SQL_TipoCategoria)){?>
												<option value="<?php echo $row_TipoCategoria['ID_TipoCategoria'];?>" <?php if(($edit==1)&&(strcmp($row_TipoCategoria['ID_TipoCategoria'],$row['ID_TipoCategoria'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoCategoria['TipoCategoria'];?></option>
										  <?php }?>
										</select>
								  </div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Proyectos</label>
									<div class="col-sm-9">
										<select data-placeholder="Digite para buscar..." name="Proyecto[]" class="form-control select2" id="Proyecto" multiple>
											<?php while($row_Proyectos=sqlsrv_fetch_array($SQL_Proyectos)){?>
												<option value="<?php echo $row_Proyectos['IdProyecto'];?>" <?php if(($edit==1)&&(in_array($row_Proyectos['IdProyecto'],$ProyectosCat))){ echo "selected=\"selected\"";}?>><?php echo $row_Proyectos['DeProyecto'];?></option>
											<?php }?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Mostrar en Dashboard</label>
									<div class="col-sm-9">
										<select name="MostrarDashboard" class="form-control" id="MostrarDashboard">
											<option value="1" <?php if($edit==1){if($row['MostrarDashboard']==1){echo "selected='selected'";}}?>>SI</option>
										   <option value="0" <?php if($edit==1){if($row['MostrarDashboard']==0){echo "selected='selected'";}}?>>NO</option>
										</select>
								  </div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">URL</label>
									<div class="col-sm-9">
										<input name="URL" type="text" class="form-control" id="URL" value="<?php if($edit==1){echo $row['URL'];}?>">
										<span class="form-text m-b-none text-muted">Si es un menú padre, ingrese solo el signo <span class="font-bold">#</span>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Parámetros adicionales</label>
									<div class="col-sm-9">
										<input name="ParamAdicionales" type="text" class="form-control" id="ParamAdicionales" value="<?php if($edit==1){echo $row['ParamAdicionales'];}?>">
									</div>
								</div>
								<div class="form-group">
									<div class="col-sm-9">
										<?php if($edit==1){?>
											<button class="btn btn-warning" type="submit" id="Crear"><i class="fa fa-refresh"></i> Actualizar categoria</button> 
										<?php }else{?>
											<button class="btn btn-primary" type="submit" id="Crear"><i class="fa fa-check"></i> Crear categoria</button>
										<?php }?>
										<a href="gestionar_categorias.php" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
									</div>
									<?php if($edit==1){?>
									<div class="col-sm-3">
										<button class="btn btn-danger pull-right" type="button" onClick="Eliminar();"><i class="fa fa-times-circle"></i>&nbsp;Eliminar</button>		
									</div>
									<?php }?>
								</div>
								<input type="hidden" id="P" name="P" value="2" />
								<input type="hidden" id="ID" name="ID" value="<?php if($edit==1){echo $row['ID_Categoria'];}?>" />
								<input type="hidden" id="edit" name="edit" value="<?php echo $edit;?>" />

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
		$(".select2").select2();
		$(".alkin").on('click', function(){
			$('.ibox-content').toggleClass('sk-loading');
		});
		<?php if($edit==1&&($row['URL']=="")){?>
		$('#TipoCategoria').trigger('change');
		<?php }?>
	});
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>