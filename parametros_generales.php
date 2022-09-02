<?php require_once("includes/conexion.php");
PermitirAcceso(204);

$sw_error=0;
$msg_error="";

if(isset($_POST['MM_Insert'])&&($_POST['MM_Insert']!="")){
	$Param=array(
		"NULL",
		"NULL",
		"'".$_SESSION['CodUser']."'",
		2,
		"'".$_POST['Plataforma']."'",
		"'".$_POST['NombreVariable']."'",
		"'".$_POST['NombreMostrar']."'",
		"'".$_POST['TipoCampo']."'"
	);
	$SQL=EjecutarSP('sp_tbl_VariablesGlobales',$Param);
	if($SQL){
		header('Location:parametros_generales.php?&a='.base64_encode("OK_NewParam"));
	}else{
		$sw_error=1;
		$msg_error="No se pudo insertar el nuevo parámetro";
	}
}

if(isset($_GET['t'])&&$_GET['t']!=""){//Mostrar Tab actual
	$Tab=$_GET['t'];
}else{
	$Tab=1;
}

//Variables globales
$SQL_Var=Seleccionar('uvw_tbl_VariablesGlobales','*',"Plataforma=1");

//Variables globales ServiceOneX
$SQL_SOX=Seleccionar('uvw_tbl_VariablesGlobales','*',"Plataforma=2");

//Variables globales SalesOne
$SQL_SO=Seleccionar('uvw_tbl_VariablesGlobales','*',"Plataforma=3");

//Email
$SQL_Mail=Seleccionar('tbl_EmailNotificaciones','*');
$row_Mail=sqlsrv_fetch_array($SQL_Mail);

//Plantillas Email
$SQL_Noti=Seleccionar('uvw_tbl_PlantillaEmail','*',"Estado=1");

//Datos del portal
$SQL_Datos=Seleccionar('tbl_DatosPortal','*');
$row_Datos=sqlsrv_fetch_array($SQL_Datos);

//Tipo notificacion
$SQL_TipoNoti=Seleccionar('tbl_TipoNotificacion','*');
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Par&aacute;metros generales | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_NewParam"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El nuevo parámetro ha sido agregado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if($sw_error==1){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Advertencia!',
                text: '".LSiqmlObs($msg_error)."',
                icon: 'warning'
            });
		});		
		</script>";
}
?>
<style>
	.swal2-container {
	  	z-index: 9000;
	}
</style>
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
                    <h2>Par&aacute;metros generales</h2>
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
                            <strong>Par&aacute;metros generales</strong>
                        </li>
                    </ol>
                </div>
			 	<div class="col-sm-4">
                    <div class="title-action">
						<button id="btnNewParam" class="btn btn-primary" onClick="CrearParametro();"><i class="fa fa-plus-circle"></i> Crear nuevo parámetro</button>
                    </div>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
		 	<div class="modal inmodal fade" id="myModal" tabindex="1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content" id="ContenidoModal">
						
					</div>
				</div>
			</div>
          <div class="row">
           <div class="col-lg-12">
			   <div class="ibox-content">
				    <?php include("includes/spinner.php"); ?>
				<div class="tabs-container">
					<ul class="nav nav-tabs">
						<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-desktop"></i> PortalOne</a></li>
						<li><a data-toggle="tab" href="#tab-2"><i class="fa fa-tablet"></i> ServiceOne X</a></li>
						<li><a data-toggle="tab" href="#tab-3"><i class="fa fa-tablet"></i> SalesOne</a></li>
						<li><a data-toggle="tab" href="#tab-4"><i class="fa fa-gear"></i> Configuraciones</a></li>
						<li><a data-toggle="tab" href="#tab-5"><i class="fa fa-envelope"></i> Servidor SMTP</a></li>
						<li><a data-toggle="tab" href="#tab-6"><i class="fa fa-paper-plane"></i> Notificaciones</a></li>
						<li><a data-toggle="tab" href="#tab-7"><i class="fa fa-book"></i> Acuerdo de confidencialidad</a></li>
					</ul>
					<div class="tab-content">
						<div id="tab-1" class="tab-pane active">
							<div class="panel-body">
                              	<?php while($row_Var=sqlsrv_fetch_array($SQL_Var)){?>
										<div class="row">
											<div class="form-group">
												<label class="col-sm-3 control-label"><?php echo $row_Var['NombreMostrar'];?><br><span class="text-muted"><?php echo $row_Var['NombreVariable'];?></span></label>
												<div class="col-sm-5">
													<input name="VarGlobal_<?php echo $row_Var['ID_Variable'];?>" type="<?php echo $row_Var['Type'];?>" required="required" class="<?php echo $row_Var['Class'];?>" id="VarGlobal_<?php echo $row_Var['ID_Variable'];?>" value="<?php echo $row_Var['Valor'];?>">
												</div>
												<div class="col-sm-2">
													<button type="button" id="btn_VarGlobal" onClick="ActualizarVariable(<?php echo $row_Var['ID_Variable'];?>);" class="ladda-button btn btn-info btn-sm" data-style="slide-right"><i class="fa fa-refresh"></i> Actualizar</button>
												</div>
												<div class="col-sm-2">
													<div id="Validar_Var_<?php echo $row_Var['ID_Variable'];?>"></div>
												</div>
											</div>
										</div><br>
								<?php }?>	
							</div>
						</div>
						<div id="tab-2" class="tab-pane">
							<div class="panel-body">
                              	<?php while($row_SOX=sqlsrv_fetch_array($SQL_SOX)){?>
										<div class="row">
											<div class="form-group">
												<label class="col-sm-3 control-label"><?php echo $row_SOX['NombreMostrar'];?><br><span class="text-muted"><?php echo $row_SOX['NombreVariable'];?></span></label>
												<div class="col-sm-5">
													<input name="VarGlobal_<?php echo $row_SOX['ID_Variable'];?>" type="<?php echo $row_SOX['Type'];?>" required="required" class="<?php echo $row_SOX['Class'];?>" id="VarGlobal_<?php echo $row_SOX['ID_Variable'];?>" value="<?php echo $row_SOX['Valor'];?>">
												</div>
												<div class="col-sm-2">
													<button type="button" id="btn_VarGlobal" onClick="ActualizarVariable(<?php echo $row_SOX['ID_Variable'];?>);" class="ladda-button btn btn-info btn-sm" data-style="slide-right"><i class="fa fa-refresh"></i> Actualizar</button>
												</div>
												<div class="col-sm-2">
													<div id="Validar_Var_<?php echo $row_SOX['ID_Variable'];?>"></div>
												</div>
											</div>
										</div><br>
								<?php }?>	
							</div>
						</div>
						<div id="tab-3" class="tab-pane">
							<div class="panel-body">
                              	<?php while($row_SO=sqlsrv_fetch_array($SQL_SO)){?>
										<div class="row">
											<div class="form-group">
												<label class="col-sm-3 control-label"><?php echo $row_SO['NombreMostrar'];?><br><span class="text-muted"><?php echo $row_SO['NombreVariable'];?></span></label>
												<div class="col-sm-5">
													<input name="VarGlobal_<?php echo $row_SO['ID_Variable'];?>" type="<?php echo $row_SO['Type'];?>" required="required" class="<?php echo $row_SO['Class'];?>" id="VarGlobal_<?php echo $row_SO['ID_Variable'];?>" value="<?php echo $row_SO['Valor'];?>">
												</div>
												<div class="col-sm-2">
													<button type="button" id="btn_VarGlobal" onClick="ActualizarVariable(<?php echo $row_SO['ID_Variable'];?>);" class="ladda-button btn btn-info btn-sm" data-style="slide-right"><i class="fa fa-refresh"></i> Actualizar</button>
												</div>
												<div class="col-sm-2">
													<div id="Validar_Var_<?php echo $row_SO['ID_Variable'];?>"></div>
												</div>
											</div>
										</div><br>
								<?php }?>	
							</div>
						</div>
						<div id="tab-4" class="tab-pane">
							<div class="panel-body">
								<div class="row">
									<div class="form-group">
										<label class="col-sm-3 control-label">Base de datos</label>
										<div class="col-sm-5">
											<input name="BaseDatos" type="text" class="form-control" id="BaseDatos" value="<?php echo $_SESSION['BD'];?>" readonly="readonly">
										</div>
									</div>
								</div>
								<br>
								<div class="row">
									<div class="form-group">
										<label class="col-sm-3 control-label">Sistema operativo</label>
										<div class="col-sm-5">
											<input name="SO" type="text" class="form-control" id="SO" value="<?php echo SO;?>" readonly="readonly">
										</div>
									</div>
								</div>
								<br>
								<div class="row">
									<div class="form-group">
										<label class="col-sm-3 control-label">Nombre del portal</label>
										<div class="col-sm-5">
											<input name="Dato_1" type="text" required="required" class="form-control" id="Dato_1" value="<?php echo $row_Datos['NombrePortal'];?>">
										</div>
										<div class="col-sm-2">
											<button onClick="ActualizarDatosPortal(1);" type="button" class="ladda-button btn btn-info btn-sm" data-style="slide-right"><i class="fa fa-refresh"></i> Actualizar</button>
										</div>
										<div class="col-sm-2">
											<div id="Result_1"></div>
										</div>
									</div>
								</div>
								<br>
								<div class="row">
									<div class="form-group">
										<label class="col-sm-3 control-label">Nombre de la empresa</label>
										<div class="col-sm-5">
											<input name="Dato_2" type="text" required="required" class="form-control" id="Dato_2" value="<?php echo $row_Datos['NombreEmpresa'];?>">
										</div>
										<div class="col-sm-2">
											<button onClick="ActualizarDatosPortal(2);" type="button" class="ladda-button btn btn-info btn-sm" data-style="slide-right"><i class="fa fa-refresh"></i> Actualizar</button>
										</div>
										<div class="col-sm-2">
											<div id="Result_2"></div>
										</div>
									</div>
								</div>
								<br>
								<div class="row">
									<div class="form-group">
										<label class="col-sm-3 control-label">Código SN de la empresa</label>
										<div class="col-sm-5">
											<input name="Dato_6" type="text" required="required" class="form-control" id="Dato_6" value="<?php echo $row_Datos['NIT'];?>">
										</div>
										<div class="col-sm-2">
											<button onClick="ActualizarDatosPortal(6);" type="button" class="ladda-button btn btn-info btn-sm" data-style="slide-right"><i class="fa fa-refresh"></i> Actualizar</button>
										</div>
										<div class="col-sm-2">
											<div id="Result_6"></div>
										</div>
									</div>
								</div>
								<br>
								<div class="row">
									<div class="form-group">
										<label class="col-sm-3 control-label">Sucursal de la empresa</label>
										<div class="col-sm-5">
											<input name="Dato_7" type="text" required="required" class="form-control" id="Dato_7" value="<?php echo $row_Datos['SucursalEmpresa'];?>">
										</div>
										<div class="col-sm-2">
											<button onClick="ActualizarDatosPortal(7);" type="button" class="ladda-button btn btn-info btn-sm" data-style="slide-right"><i class="fa fa-refresh"></i> Actualizar</button>
										</div>
										<div class="col-sm-2">
											<div id="Result_7"></div>
										</div>
									</div>
								</div>
								<br>
								<div class="row">
									<div class="form-group">
										<label class="col-sm-3 control-label">Logo de la empresa (300 x 95, <em>.png</em>)</label>
										<div class="col-sm-5">
											<button onClick="CargarLogoEmpresa();" type="button" class="btn btn-w-m btn-default">Cargar imagen</button>
										</div>
										<div class="col-sm-2">
											<button onClick="ActualizarLogo(3);" type="button" class="ladda-button btn btn-info btn-sm" data-style="slide-right"><i class="fa fa-refresh"></i> Actualizar</button>
										</div>
										<div class="col-sm-2">
											<div id="Result_3"></div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="form-group">
										<div class="col-sm-5">
											<img id="ImgLogoEmpresa" src="img/img_logo.png" width="150" height="45" alt=""/> 
										</div>
									</div>
								</div>
								<br>
								<div class="row">
									<div class="form-group">
										<label class="col-sm-3 control-label">Logo slim de la empresa (30 x 30, <em>.png</em>)</label>
										<div class="col-sm-5">
											<button onClick="CargarLogoSlimEmpresa();" type="button" class="btn btn-w-m btn-default">Cargar imagen</button>
										</div>
										<div class="col-sm-2">
											<button onClick="ActualizarLogo(4);" type="button" class="ladda-button btn btn-info btn-sm" data-style="slide-right"><i class="fa fa-refresh"></i> Actualizar</button>
										</div>
										<div class="col-sm-2">
											<div id="Result_4"></div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="form-group">
										<div class="col-sm-5">
											<img id="ImgLogoSlimEmpresa" src="img/img_logo_slim.png" alt="" width="30" height="30"/> 
										</div>
									</div>
								</div>
								<br>
								<div class="row">
									<div class="form-group">
										<label class="col-sm-3 control-label">Favicon</label>
										<div class="col-sm-5">
											<button onClick="CargarFavicon();" type="button" class="btn btn-w-m btn-default">Cargar imagen</button>
										</div>
										<div class="col-sm-2">
											<button onClick="ActualizarLogo(5);" type="button" class="ladda-button btn btn-info btn-sm" data-style="slide-right"><i class="fa fa-refresh"></i> Actualizar</button>
										</div>
										<div class="col-sm-2">
											<div id="Result_5"></div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="form-group">
										<div class="col-sm-5">
											<img id="ImgFavicon" src="css/favicon.png" alt=""/> 
										</div>
									</div>
								</div>
								<br>
								<div class="row">
									<div class="form-group">
										<label class="col-sm-3 control-label">Fondo pantalla de inicio (4000 x 2250, <em>.jpg</em>)</label>
										<div class="col-sm-5">
											<button onClick="CargarFondo();" type="button" class="btn btn-w-m btn-default">Cargar imagen</button>
										</div>
										<div class="col-sm-2">
											<button onClick="ActualizarLogo(8);" type="button" class="ladda-button btn btn-info btn-sm" data-style="slide-right"><i class="fa fa-refresh"></i> Actualizar</button>
										</div>
										<div class="col-sm-2">
											<div id="Result_8"></div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="form-group">
										<div class="col-sm-5">
											<img id="ImgFondo" src="img/img_background.jpg" alt="" width="100" height="70"/> 
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id="tab-5" class="tab-pane">
							<div class="panel-body">
								<form action="#" method="post" class="form-horizontal" id="ActualizarSMTP">
									<div class="form-group">
										<label class="col-sm-2 control-label">Correo electr&oacute;nico</label>
										<div class="col-sm-3"><input name="Usuario" type="text" required="required" class="form-control" id="Usuario" value="<?php echo isset($row_Mail['Usuario']) ? $row_Mail['Usuario'] : "";?>"></div>
										<label class="col-sm-2 control-label">Contrase&ntilde;a</label>
										<div class="col-sm-3"><input name="Password" type="password" required="required" class="form-control" id="Password" value="<?php echo isset($row_Mail['Clave']) ? base64_decode($row_Mail['Clave']) : "";?>"></div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">Servidor SMTP</label>
										<div class="col-sm-3"><input name="Servidor" type="text" required="required" class="form-control" id="Servidor" value="<?php echo isset($row_Mail['ServidorSMTP']) ? $row_Mail['ServidorSMTP'] : "";?>"></div>
										<label class="col-sm-2 control-label">Puerto SMTP</label>
										<div class="col-sm-3"><input name="Puerto" type="text" required="required" class="form-control" id="Puerto" value="<?php echo isset($row_Mail['PuertoSMTP']) ? $row_Mail['PuertoSMTP'] : "";?>"></div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">Requiere autenticaci&oacute;n</label>
										<div class="col-sm-3">
											<div class="switch">
												<div class="onoffswitch">
													<input name="ReqAut" type="checkbox" class="onoffswitch-checkbox" id="ReqAut" value="1" <?php if(isset($row_Mail['AutenticacionSMTP'])&&$row_Mail['AutenticacionSMTP']==1){ echo "checked='checked'";} ?>>
													<label class="onoffswitch-label" for="ReqAut">
														<span class="onoffswitch-inner"></span>
														<span class="onoffswitch-switch"></span>
													</label>
												</div>
											</div>
										</div>
										<label class="col-sm-2 control-label">Tipo de conexión cifrada</label>
										<div class="col-sm-3">
											<select name="TypeCon" id="TypeCon" class="form-control">
												<option value="" <?php if(isset($row_Mail['TipoConexion'])&&$row_Mail['TipoConexion']==""){ echo "selected='selected'";} ?>>Ninguno</option>
												<option value="ssl" <?php if(isset($row_Mail['TipoConexion'])&&$row_Mail['TipoConexion']=="ssl"){ echo "selected='selected'";} ?>>SSL</option>
												<option value="tls" <?php if(isset($row_Mail['TipoConexion'])&&$row_Mail['TipoConexion']=="tls"){ echo "selected='selected'";} ?>>TLS</option>
											</select>
										</div>
									</div>
									<br>
									<div class="form-group">
										<div class="col-sm-2">
											<button class="btn btn-warning btn-sm" id="Probar" type="button"><i class="fa fa-spinner"></i> Probar configuración</button>
										</div>
										<div class="col-sm-2">
											<div id="Validar"></div>
											<div id="spinner1" style="display: none;" class="sk-spinner sk-spinner-wave pull-left">
												<div class="sk-rect1"></div>
												<div class="sk-rect2"></div>
												<div class="sk-rect3"></div>
												<div class="sk-rect4"></div>
												<div class="sk-rect5"></div>
											</div>
										</div>
									</div>
									<br>
									<div class="form-group">
										<div class="col-sm-3">
											<button class="btn btn-info" type="button" id="btnActualizarMail"><i class="fa fa-refresh"></i> Actualizar</button>
										</div>
									</div>
									<input type="hidden" id="P" form="ActualizarSMTP" name="P" value="" />
									<input type="hidden" id="t" form="ActualizarSMTP" name="t" value="3" />
								  </form>
								  <br>
								  <div id="MsgOk" style="display: none;" class="col-lg-6">
									<div class="alert alert-success" role="alert">
										<i class="fa fa-check-circle"></i> Datos actualizados correctamente.
									</div>
								  </div>
								  <div id="MsgError" style="display: none;" class="col-lg-6">
									<div class="alert alert-danger" role="alert">
										<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> No se pudo actualizar la información. Pongase en contacto con el administrador.
									</div>
								</div>
							</div>
						</div>
						<div id="tab-6" class="tab-pane">			
							<div class="panel-body">
								<div class="row">
									<div class="col-lg-6">
										<div class="panel panel-info">
											<div class="panel-heading">
												<i class="fa fa-info-circle"></i> Información de variables
											</div>
											<div class="panel-body">
												<p>Puede utilizar las siguientes variables en el cuerpo del mensaje para referirse a los datos de los archivos:</p>
												<ul>
													<li><strong>[Nombre_Cliente]</strong> Muestra el nombre del cliente al que hace referencia el archivo.</li>
													<li><strong>[Nombre_Sucursal]</strong> Muestra el nombre de la sucursal a la que hace referencia el archivo.</li>
													<li><strong>[Nombre_Categoria]</strong> Muestra la categoría a la que pertenece el archivo.</li>
													<li><strong>[Nombre_Archivo]</strong> Muestra el nombre del archivo.</li>
													<li><strong>[Comentarios]</strong> Muestra los comentarios asociados al archivo.</li>
													<li><strong>[Fecha]</strong> Fecha en la que se está ejecutando la acción del archivo.</li>
													<li><strong>[Hora]</strong> Hora en la que se está ejecutando la acción del archivo.</li>
													<li><strong>[Nombre_Usuario]</strong> Usuario que está ejecutando la acción del archivo.</li>
												</ul>
											</div>
										</div>
									</div>		
								</div>
								<?php while($row_Noti=sqlsrv_fetch_array($SQL_Noti)){?>
								<div class="well">
									<div class="mail-box">
										<div class="mail-body">
											<form class="form-horizontal" method="get">
												<div class="form-group">
												<div class="col-sm-1">
													<p class="text-primary">Activo</p>
												</div>	
												<div class="col-sm-1">
													<div class="switch pull-right">
														<div class="onoffswitch">
															<input name="Estado_<?php echo $row_Noti['ID_Plantilla'];?>" type="checkbox" class="onoffswitch-checkbox" id="Estado_<?php echo $row_Noti['ID_Plantilla'];?>" value="1" <?php if($row_Noti['Estado']==1){ echo "checked='checked'";} ?>>
															<label class="onoffswitch-label" for="Estado_<?php echo $row_Noti['ID_Plantilla'];?>">
																<span class="onoffswitch-inner"></span>
																<span class="onoffswitch-switch"></span>
															</label>
														</div>
													</div>
												</div>
													<label class="col-sm-1 control-label">Tipo:</label>
													<div class="col-sm-2">
														<select name="TipoNotificacion" id="TipoNotificacion_<?php echo $row_Noti['ID_Plantilla'];?>" class="form-control">
														<?php 
															while($row_TipoNoti=sqlsrv_fetch_array($SQL_TipoNoti)){?>
															<option value="<?php echo $row_TipoNoti['ID_TipoNotificacion'];?>" <?php if(strcmp($row_Noti['ID_TipoNotificacion'],$row_TipoNoti['ID_TipoNotificacion'])==0){ echo "selected=\"selected\"";}?>><?php echo $row_TipoNoti['TipoNotificacion'];?></option>
															<?php }?>
														</select>
													</div>
													<label class="col-sm-2 control-label">Asunto:</label>
													<div class="col-sm-5"><input type="text" id="Asunto_<?php echo $row_Noti['ID_Plantilla'];?>" class="form-control" value="<?php echo $row_Noti['Asunto'];?>"></div>
												</div>
											</form>
										</div>
										<div class="mail-text h-200">
											<textarea name="Mensaje_<?php echo $row_Noti['ID_Plantilla'];?>" id="Mensaje_<?php echo $row_Noti['ID_Plantilla'];?>" class="summernote"><?php echo $row_Noti['Mensaje'];?></textarea>
											<div class="clearfix"></div>
										</div>
										<div class="mail-body text-right tooltip-demo">
											<button type="button" onClick="ActualizarPlantillaEmail(<?php echo $row_Noti['ID_Plantilla'];?>);" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> Guardar</button>
										</div>
										<div class="clearfix"></div>
									</div>
                           			<div id="MsgOkEmail_<?php echo $row_Noti['ID_Plantilla'];?>" style="display: none;" class="col-lg-6">
										<div class="alert alert-success" role="alert">
											<i class="fa fa-check-circle"></i> Datos actualizados correctamente.
										</div>
								  </div>
                            	</div>                            	
                            	<br>
                            	<?php }?>
							</div>
						</div>
						<div id="tab-7" class="tab-pane">	
						 <?php 
							$Nombre_archivo="contrato_confidencialidad.txt";
							$Archivo=fopen($Nombre_archivo,"r+");
							$Contenido = fread($Archivo, filesize($Nombre_archivo));
						 ?>		
							<div class="panel-body">
								<form method="post" action="registro.php" id="frm_Acuerdo">
								<div class="well">
									<div class="mail-box">
										<div class="mail-body">
											
										</div>
										<div class="mail-text h-200">
											<textarea name="TextAcuerdo" id="TextAcuerdo" class="summernote"><?php echo $Contenido;?></textarea>
											<div class="clearfix"></div>
										</div>
									<?php if(isset($_GET['result'])&&($_GET['result']==base64_encode("MsgOkAcuerdoOK"))){?>
											<div id="MsgOkAcuerdoOK" class="col-lg-6">
												<div class="alert alert-success" role="alert">
													<i class="fa fa-check-circle"></i> Datos actualizados correctamente.
												</div>
											</div>
									<?php }?>
									<?php if(isset($_GET['result'])&&($_GET['result']==base64_encode("MsgOkAcuerdoER"))){?>
											<div id="MsgOkAcuerdoER" class="col-lg-6">
												<div class="alert alert-danger" role="alert">
													<i class="fa fa-times-circle"></i> Error al actualizar los datos.
												</div>
											</div>
									<?php }?>
										<div class="mail-body text-right tooltip-demo">
											<button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> Guardar</button>
										</div>
										
									</div>
									
                            	</div>
									<input type="hidden" id="P" form="frm_Acuerdo" name="P" value="31" />
									<input type="hidden" id="t" form="frm_Acuerdo" name="t" value="5" />
								</form>
                            	<br>
							</div>
							<?php fclose($Archivo);?>
						</div>
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
		$("#ActualizarSMTP").validate();
		
		$('.summernote').summernote();
		
		Ladda.bind( '.ladda-button',{ timeout: 1200 });	
		
		$('.tagsinput').tagsinput({
                tagClass: 'label label-primary'
            });
	});
</script>
<script src="js/js_mail.js"></script>
<script src="js/js_variables_globales.js"></script>
<script src="js/js_plantillas_email.js"></script>
<script src="js/js_configuracion.js"></script>
<script>
function CrearParametro(){
	$('.ibox-content').toggleClass('sk-loading',true);
	
	$.ajax({
		type: "POST",
		url: "md_crear_parametro.php",
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