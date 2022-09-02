<?php require_once("includes/conexion.php");
PermitirAcceso(203);

$msg_error="";//Mensaje del error
$sw_error=0;

if(isset($_GET['id'])&&($_GET['id']!="")){
	$IdPerfil=base64_decode($_GET['id']);
}

if(isset($_GET['tl'])&&($_GET['tl']!="")){//0 Si se está creando. 1 Se se está editando.
	$edit=$_GET['tl'];
}elseif(isset($_POST['tl'])&&($_POST['tl']!="")){
	$edit=$_POST['tl'];
}else{
	$edit=0;
}

if($edit==0){
	$Title="Crear perfil";
}else{
	$Title="Editar perfil";
}

if(isset($_POST['P'])&&($_POST['P']!="")){
	try{
		
		$Type=1;
		$ID_Perfil="NULL";
		
		if($_POST['edit']==1){//Actualizando
			$Type=2;
			$ID_Perfil="'".$_POST['ID_PerfilUsuario']."'";
			
		}
		
		$ParamPerfil=array(
			$ID_Perfil,
			"'".$_POST['NombrePerfil']."'",
			$Type
		);
		$SQL_Perfil=EjecutarSP('sp_tbl_PerfilesUsuarios',$ParamPerfil,$_POST['P']);		
		
		if($SQL_Perfil){
			
			//Si se esta actualizando, se eliminan los permisos actuales
			if($_POST['edit']==1){
				$SQL_Delete=Eliminar('tbl_PermisosPerfiles',"ID_PerfilUsuario='".$_POST['ID_PerfilUsuario']."'");
				$SQL_Delete=Eliminar('tbl_PermisosPerfiles_ServiceOne',"ID_PerfilUsuario='".$_POST['ID_PerfilUsuario']."'");
				$SQL_Delete=Eliminar('tbl_PermisosPerfiles_SalesOne',"ID_PerfilUsuario='".$_POST['ID_PerfilUsuario']."'");
			}else{
				$row_Perfil=sqlsrv_fetch_array($SQL_Perfil);
				$ID_Perfil="'".$row_Perfil[0]."'";
			}
			
			//Insertar permisos del PortalOne
			$i=0;
			$Cuenta=count($_POST['PermisoPortalOne']);
			while($i<$Cuenta){
				$Param=array(
					$ID_Perfil,
					"'".$_POST['PermisoPortalOne'][$i]."'",
					"1"
				);
				$SQL=EjecutarSP('sp_tbl_PermisosPerfiles',$Param,$_POST['P']);	
				
				if($SQL){
					$i++;
				}	
			}
			
			//Insertar permisos del ServiceOne
			$i=0;
			$Cuenta=count($_POST['PermisoServiceOne']);
			while($i<$Cuenta){
				$Param=array(
					$ID_Perfil,
					"'".$_POST['PermisoServiceOne'][$i]."'",
					"2"
				);
				$SQL=EjecutarSP('sp_tbl_PermisosPerfiles',$Param,$_POST['P']);	
				
				if($SQL){
					$i++;
				}	
			}
			
			//Insertar permisos del SalesOne
			$i=0;
			$Cuenta=count($_POST['PermisoSalesOne']);
			while($i<$Cuenta){
				$Param=array(
					$ID_Perfil,
					"'".$_POST['PermisoSalesOne'][$i]."'",
					"3"
				);
				$SQL=EjecutarSP('sp_tbl_PermisosPerfiles',$Param,$_POST['P']);	
				
				if($SQL){
					$i++;
				}	
			}
			
			sqlsrv_close($conexion);
			
			if($_POST['edit']==1){
				header('Location:gestionar_perfiles.php?a='.base64_encode("OK_EditPerfil"));
			}else{
				header('Location:gestionar_perfiles.php?a='.base64_encode("OK_Perfil"));
			}
			
		}else{
			$msg_error="No se pudo insertar el perfil";
			$sw_error=1;
		}		
	}catch (Exception $e) {
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
	
}

if($edit==1){
	$Cons="Select * From uvw_tbl_PerfilesUsuarios Where ID_PerfilUsuario=".base64_decode($_GET['id']);
	$SQL=sqlsrv_query($conexion,$Cons);
	$row=sqlsrv_fetch_array($SQL);
	
	//Cargar permisos del perfil en PortalOne
	$SQL_RelPermiso=Seleccionar('uvw_tbl_PermisosPerfiles','*',"ID_PerfilUsuario='".base64_decode($_GET['id'])."'");
	$PermisosPerfil=array();
	$i=0;
	while($row_RelPermiso=sqlsrv_fetch_array($SQL_RelPermiso)){
		$PermisosPerfil[$i]=$row_RelPermiso['ID_Permiso'];
		$i++;
	}
	
	//Cargar permisos del perfil en ServiceOne
	$SQL_RelPermisoServiceOne=Seleccionar('uvw_tbl_PermisosPerfiles_ServiceOne','*',"ID_PerfilUsuario='".base64_decode($_GET['id'])."'");
	$PermisosPerfilServiceOne=array();
	$i=0;
	while($row_RelPermisoServiceOne=sqlsrv_fetch_array($SQL_RelPermisoServiceOne)){
		$PermisosPerfilServiceOne[$i]=$row_RelPermisoServiceOne['ID_Permiso'];
		$i++;
	}
	
	//Cargar permisos del perfil en SalesOne
	$SQL_RelPermisoSalesOne=Seleccionar('uvw_tbl_PermisosPerfiles_SalesOne','*',"ID_PerfilUsuario='".base64_decode($_GET['id'])."'");
	$PermisosPerfilSalesOne=array();
	$i=0;
	while($row_RelPermisoSalesOne=sqlsrv_fetch_array($SQL_RelPermisoSalesOne)){
		$PermisosPerfilSalesOne[$i]=$row_RelPermisoSalesOne['ID_Permiso'];
		$i++;
	}
}


//Lista de permisos PortalOne
$SQL_Permisos=Seleccionar('uvw_tbl_NombresPermisosPerfiles','*');

//Lista de permisos ServiceOne
$SQL_PermisosServiceOne=Seleccionar('uvw_tbl_NombresPermisosPerfiles_ServiceOne','*');

//Lista de permisos SalesOne
$SQL_PermisosSalesOne=Seleccionar('uvw_tbl_NombresPermisosPerfiles_SalesOne','*');

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $Title;?> | <?php echo NOMBRE_PORTAL;?></title>
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
                            <a href="gestionar_perfiles.php">Gestionar perfiles</a>
                        </li>
                        <li class="active">
                            <strong><?php echo $Title;?></strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
		 <form action="perfil.php" method="post" class="form-horizontal" id="AgregarPerfil">
			<div class="row">
				<div class="ibox-content">  
					<?php include("includes/spinner.php"); ?>   
						<div class="form-group">
							<label class="col-lg-1 control-label">Nombre perfil</label>
							<div class="col-lg-3">
								<input name="NombrePerfil" type="text" required="required" class="form-control" id="NombrePerfil" maxlength="100" value="<?php if($edit==1){ echo $row['PerfilUsuario'];}?>">
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-9">
								<?php if($edit==1){?>
									<button class="btn btn-warning" type="submit" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar perfil</button>  
								<?php }else{?>
									<button class="btn btn-primary" type="submit" id="Crear"><i class="fa fa-check"></i> Crear perfil</button>
								<?php }?>								
								<a href="gestionar_perfiles.php" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
								
								<input type="hidden" name="ID_PerfilUsuario" id="ID_PerfilUsuario" value="<?php if($edit==1){echo $row['ID_PerfilUsuario'];}?>">
								<input type="hidden" id="P" name="P" value="8" />
								<input type="hidden" id="edit" name="edit" value="<?php echo $edit;?>" />
							</div>
						</div>
				</div>
			</div>
			 <br>
				<div class="row">
					<div class="ibox-content"> 
						<?php include("includes/spinner.php"); ?>              
						<div class="tabs-container">
							<ul class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-desktop"></i> PortalOne</a></li>
								<li><a data-toggle="tab" href="#tab-2"><i class="fa fa-mobile"></i> ServiceOne</a></li>
								<li><a data-toggle="tab" href="#tab-3"><i class="fa fa-tablet"></i> SalesOne</a></li>
							</ul>
							<div class="tab-content">
								<div id="tab-1" class="tab-pane active">
									<div class="form-group">
										<div class="col-lg-10">
										<div class="table-responsive">
											<table class="table table-bordered">
												<thead>
												<tr>
													<th>Seleccionar</th>
													<th>Funci&oacute;n</th>
													<th>Descripci&oacute;n</th>
												</tr>
												</thead>
												<tbody>
											<?php while($row_Permisos=sqlsrv_fetch_array($SQL_Permisos)){
												if($row_Permisos['ID_Padre']==0){ ?>
													<tr class="warning">
														<td colspan="3"><strong><?php echo $row_Permisos['NombreFuncion'];?></strong></td>
													</tr>
											<?php
													$SQL_Padre=Seleccionar('uvw_tbl_NombresPermisosPerfiles','*',"ID_Padre='".$row_Permisos['ID_Permiso']."'");
													while($row_Padre=sqlsrv_fetch_array($SQL_Padre)){
														if(strlen($row_Padre['ID_Permiso'])==2){ ?>
															<tr class="info">
																<td colspan="3"><strong><?php echo $row_Padre['NombreFuncion'];?></strong></td>
															</tr>								
											<?php		
															$SQL_Hijo=Seleccionar('uvw_tbl_NombresPermisosPerfiles','*',"ID_Padre='".$row_Padre['ID_Permiso']."'");
															while($row_Hijo=sqlsrv_fetch_array($SQL_Hijo)){ ?>
																<tr>
																	<td>
																		<div class="switch">
																			<div class="onoffswitch">
																				<input name="PermisoPortalOne[]" type="checkbox" class="onoffswitch-checkbox" id="PermisoPortalOne<?php echo $row_Hijo['ID_Permiso'];?>" value="<?php echo $row_Hijo['ID_Permiso'];?>" <?php if($edit==1){if(in_array($row_Hijo['ID_Permiso'],$PermisosPerfil)){ echo "checked";}}?>>
																				<label class="onoffswitch-label" for="PermisoPortalOne<?php echo $row_Hijo['ID_Permiso'];?>">
																					<span class="onoffswitch-inner"></span>
																					<span class="onoffswitch-switch"></span>
																				</label>
																			</div>
																		</div>
																	</td>
																	<td><?php echo $row_Hijo['NombreFuncion'];?></td>
																	<td><?php echo $row_Hijo['Descripcion'];?></td>
																</tr>
																<?php
															}
														}else{
															?>
																<tr>
																	<td>
																		<div class="switch">
																			<div class="onoffswitch">
																				<input name="PermisoPortalOne[]" type="checkbox" class="onoffswitch-checkbox" id="PermisoPortalOne<?php echo $row_Padre['ID_Permiso'];?>" value="<?php echo $row_Padre['ID_Permiso'];?>" <?php if($edit==1){if(in_array($row_Padre['ID_Permiso'],$PermisosPerfil)){ echo "checked";}}?>>
																				<label class="onoffswitch-label" for="PermisoPortalOne<?php echo $row_Padre['ID_Permiso'];?>">
																					<span class="onoffswitch-inner"></span>
																					<span class="onoffswitch-switch"></span>
																				</label>
																			</div>
																		</div>
																	</td>
																	<td><?php echo $row_Padre['NombreFuncion'];?></td>
																	<td><?php echo $row_Padre['Descripcion'];?></td>
																</tr>
																<?php
														}
													}
												}
											}
										?>
												</tbody>
											</table>
											</div>
										</div>
									  </div>
								</div>
								<div id="tab-2" class="tab-pane">
									<div class="form-group">
										<div class="col-lg-10">
										<div class="table-responsive">
											<table class="table table-bordered">
												<thead>
												<tr>
													<th>Seleccionar</th>
													<th>Funci&oacute;n</th>
													<th>Descripci&oacute;n</th>
												</tr>
												</thead>
												<tbody>
											<?php while($row_PermisosServiceOne=sqlsrv_fetch_array($SQL_PermisosServiceOne)){
												if($row_PermisosServiceOne['ID_Padre']==0){ ?>
													<tr class="warning">
														<td colspan="3"><strong><?php echo $row_PermisosServiceOne['NombreFuncion'];?></strong></td>
													</tr>
											<?php
													$SQL_PadreServiceOne=Seleccionar('uvw_tbl_NombresPermisosPerfiles_ServiceOne','*',"ID_Padre='".$row_PermisosServiceOne['ID_Permiso']."'");
													while($row_PadreServiceOne=sqlsrv_fetch_array($SQL_PadreServiceOne)){
														if(strlen($row_PadreServiceOne['ID_Permiso'])==2){ ?>
															<tr class="info">
																<td colspan="3"><strong><?php echo $row_PadreServiceOne['NombreFuncion'];?></strong></td>
															</tr>								
											<?php		
															$SQL_HijoServiceOne=Seleccionar('uvw_tbl_NombresPermisosPerfiles_ServiceOne','*',"ID_Padre='".$row_PadreServiceOne['ID_Permiso']."'");
															while($row_HijoServiceOne=sqlsrv_fetch_array($SQL_HijoServiceOne)){ ?>
																<tr>
																	<td>
																		<div class="switch">
																			<div class="onoffswitch">
																				<input name="PermisoServiceOne[]" type="checkbox" class="onoffswitch-checkbox" id="PermisoServiceOne<?php echo $row_HijoServiceOne['ID_Permiso'];?>" value="<?php echo $row_HijoServiceOne['ID_Permiso'];?>" <?php if($edit==1){if(in_array($row_HijoServiceOne['ID_Permiso'],$PermisosPerfilServiceOne)){ echo "checked";}}?>>
																				<label class="onoffswitch-label" for="PermisoServiceOne<?php echo $row_HijoServiceOne['ID_Permiso'];?>">
																					<span class="onoffswitch-inner"></span>
																					<span class="onoffswitch-switch"></span>
																				</label>
																			</div>
																		</div>
																	</td>
																	<td><?php echo $row_HijoServiceOne['NombreFuncion'];?></td>
																	<td><?php echo $row_HijoServiceOne['Descripcion'];?></td>
																</tr>
																<?php
															}
														}else{
															?>
																<tr>
																	<td>
																		<div class="switch">
																			<div class="onoffswitch">
																				<input name="PermisoServiceOne[]" type="checkbox" class="onoffswitch-checkbox" id="PermisoServiceOne<?php echo $row_PadreServiceOne['ID_Permiso'];?>" value="<?php echo $row_PadreServiceOne['ID_Permiso'];?>" <?php if($edit==1){if(in_array($row_PadreServiceOne['ID_Permiso'],$PermisosPerfilServiceOne)){ echo "checked";}}?>>
																				<label class="onoffswitch-label" for="PermisoServiceOne<?php echo $row_PadreServiceOne['ID_Permiso'];?>">
																					<span class="onoffswitch-inner"></span>
																					<span class="onoffswitch-switch"></span>
																				</label>
																			</div>
																		</div>
																	</td>
																	<td><?php echo $row_PadreServiceOne['NombreFuncion'];?></td>
																	<td><?php echo $row_PadreServiceOne['Descripcion'];?></td>
																</tr>
																<?php
														}
													}
												}
											}
										?>
												</tbody>
											</table>
											</div>
										</div>
									  </div>
								</div>
								<div id="tab-3" class="tab-pane">
									<div class="form-group">
										<div class="col-lg-10">
										<div class="table-responsive">
											<table class="table table-bordered">
												<thead>
												<tr>
													<th>Seleccionar</th>
													<th>Funci&oacute;n</th>
													<th>Descripci&oacute;n</th>
												</tr>
												</thead>
												<tbody>
											<?php while($row_PermisosSalesOne=sqlsrv_fetch_array($SQL_PermisosSalesOne)){
												if($row_PermisosSalesOne['ID_Padre']==0){ ?>
													<tr class="warning">
														<td colspan="3"><strong><?php echo $row_PermisosSalesOne['NombreFuncion'];?></strong></td>
													</tr>
											<?php
													$SQL_PadreSalesOne=Seleccionar('uvw_tbl_NombresPermisosPerfiles_SalesOne','*',"ID_Padre='".$row_PermisosSalesOne['ID_Permiso']."'");
													while($row_PadreSalesOne=sqlsrv_fetch_array($SQL_PadreSalesOne)){
														if(strlen($row_PadreSalesOne['ID_Permiso'])==2){ ?>
															<tr class="info">
																<td colspan="3"><strong><?php echo $row_PadreSalesOne['NombreFuncion'];?></strong></td>
															</tr>								
											<?php		
															$SQL_HijoSalesOne=Seleccionar('uvw_tbl_NombresPermisosPerfiles_SalesOne','*',"ID_Padre='".$row_PadreSalesOne['ID_Permiso']."'");
															while($row_HijoSalesOne=sqlsrv_fetch_array($SQL_HijoSalesOne)){ ?>
																<tr>
																	<td>
																		<div class="switch">
																			<div class="onoffswitch">
																				<input name="PermisoSalesOne[]" type="checkbox" class="onoffswitch-checkbox" id="PermisoSalesOne<?php echo $row_HijoSalesOne['ID_Permiso'];?>" value="<?php echo $row_HijoSalesOne['ID_Permiso'];?>" <?php if($edit==1){if(in_array($row_HijoSalesOne['ID_Permiso'],$PermisosPerfilSalesOne)){ echo "checked";}}?>>
																				<label class="onoffswitch-label" for="PermisoSalesOne<?php echo $row_HijoSalesOne['ID_Permiso'];?>">
																					<span class="onoffswitch-inner"></span>
																					<span class="onoffswitch-switch"></span>
																				</label>
																			</div>
																		</div>
																	</td>
																	<td><?php echo $row_HijoSalesOne['NombreFuncion'];?></td>
																	<td><?php echo $row_HijoSalesOne['Descripcion'];?></td>
																</tr>
																<?php
															}
														}else{
															?>
																<tr>
																	<td>
																		<div class="switch">
																			<div class="onoffswitch">
																				<input name="PermisoSalesOne[]" type="checkbox" class="onoffswitch-checkbox" id="PermisoSalesOne<?php echo $row_PadreSalesOne['ID_Permiso'];?>" value="<?php echo $row_PadreSalesOne['ID_Permiso'];?>" <?php if($edit==1){if(in_array($row_PadreSalesOne['ID_Permiso'],$PermisosPerfilSalesOne)){ echo "checked";}}?>>
																				<label class="onoffswitch-label" for="PermisoSalesOne<?php echo $row_PadreSalesOne['ID_Permiso'];?>">
																					<span class="onoffswitch-inner"></span>
																					<span class="onoffswitch-switch"></span>
																				</label>
																			</div>
																		</div>
																	</td>
																	<td><?php echo $row_PadreSalesOne['NombreFuncion'];?></td>
																	<td><?php echo $row_PadreSalesOne['Descripcion'];?></td>
																</tr>
																<?php
														}
													}
												}
											}
										?>
												</tbody>
											</table>
											</div>
										</div>
									  </div>
								</div>
							</div>
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
		 $("#AgregarPerfil").validate({
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
	});
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>