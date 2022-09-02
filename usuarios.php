<?php require_once("includes/conexion.php");
PermitirAcceso(202);

$msg_error="";//Mensaje del error
$sw_error=0;
$IdUsuario=0;

if(isset($_GET['id'])&&($_GET['id']!="")){
	$IdUsuario=base64_decode($_GET['id']);
}
if(isset($_GET['tl'])&&($_GET['tl']!="")){//0 Si se está creando. 1 Se se está editando.
	$edit=$_GET['tl'];
}elseif(isset($_POST['tl'])&&($_POST['tl']!="")){
	$edit=$_POST['tl'];
}else{
	$edit=0;
}

if($edit==0){
	$Title="Crear usuario";
}else{
	$Title="Editar usuario";
}

$dir_firma=CrearObtenerDirTempFirma();
$dir_new=CrearObtenerDirAnx("usuarios");

if(isset($_POST['P'])&&($_POST['P']!="")){
	try{

		$NombreFileFirma="";
		
		//Firma usuario		
		if($_FILES['FirmaCargadaUsuario']['tmp_name']!=""){
			if(is_uploaded_file($_FILES['FirmaCargadaUsuario']['tmp_name'])){
				$Nombre_Archivo=$_FILES['FirmaCargadaUsuario']['name'];
				$NuevoNombre=FormatoNombreAnexo($Nombre_Archivo);
				$NombreFileFirma="Sig_".$NuevoNombre[0];
				if(!move_uploaded_file($_FILES['FirmaCargadaUsuario']['tmp_name'],$dir_new.$NombreFileFirma)){
					$sw_error=1;
					$msg_error="No se pudo mover la firma a la carpeta de anexos local";
				}
			}else{
				$sw_error=1;
				$msg_error="No se pudo cargar la firma";
			}
		}else{
			if((isset($_POST['SigUser']))&&($_POST['SigUser']!="")){
				$NombreFileFirma=base64_decode($_POST['SigUser']);
				if(!copy($dir_firma.$NombreFileFirma,$dir_new.$NombreFileFirma)){
					$sw_error=1;
					$msg_error="No se pudo mover la firma";
				}
			}
		}
		
		$IdUsuario="NULL";
		$Usuario="'".$_POST['Usuario']."'";
		$Password="'".md5($_POST['Password'])."'";
		$Type=1;
		
		if($_POST['P']==5){//Actualizando
			if($_POST['Password']!=""){//Cambiar clave
				$ParamUpdClave=array(
					"'".$_POST['ID_Usuario']."'",
					"'".md5($_POST['Password'])."'",
					"'".$_POST['CambioPass']."'"
				);
				$SQL_Clave=EjecutarSP('sp_tbl_Usuarios_CambiarClave',$ParamUpdClave,$_POST['P']);
			}	
			
			$IdUsuario="'".$_POST['ID_Usuario']."'";
			$Usuario="NULL";
			$Password="NULL";
			$Type=2;
		}
		
		isset($_POST['Dimension1']) ? $CentroCosto1="'".$_POST['Dimension1']."'" : $CentroCosto1="NULL";
		isset($_POST['Dimension2']) ? $CentroCosto2="'".$_POST['Dimension2']."'" : $CentroCosto2="NULL";
		isset($_POST['Dimension3']) ? $CentroCosto3="'".$_POST['Dimension3']."'" : $CentroCosto3="NULL";
		isset($_POST['Dimension4']) ? $CentroCosto4="'".$_POST['Dimension4']."'" : $CentroCosto4="NULL";
		isset($_POST['Dimension5']) ? $CentroCosto5="'".$_POST['Dimension5']."'" : $CentroCosto5="NULL";
		
		if(isset($_POST['chkServiceOne'])&&($_POST['chkServiceOne']==1)){
			$chkServiceOne=1;
		}else{
			$chkServiceOne=0;
		}
		
		if(isset($_POST['chkSalesOne'])&&($_POST['chkSalesOne']==1)){
			$chkSalesOne=1;
		}else{
			$chkSalesOne=0;
		}
		
		$ParamUser=array(
			$IdUsuario,
			$Usuario,
			$Password,
			"'".LSiqmlObs($_POST['Nombre'])."'",
			"'".LSiqmlObs($_POST['SegundoNombre'])."'",
			"'".LSiqmlObs($_POST['Apellido'])."'",
			"'".LSiqmlObs($_POST['SegundoApellido'])."'",
			"'".$_POST['Email']."'",
			"'".$_POST['Cedula']."'",
			"'".$_POST['Telefono']."'",
			"'".$_POST['PerfilUsuario']."'",
			"NULL",
			"'".$_POST['TimeOut']."'",
			"'".$_POST['CodigoSAP']."'",
			"'".$_POST['Estado']."'",
			"'".$_POST['Proveedor']."'",
			$chkServiceOne,
			$chkSalesOne,
			$CentroCosto1,
			$CentroCosto2,
			$CentroCosto3,
			$CentroCosto4,
			$CentroCosto5,
			"'".$NombreFileFirma."'",
			"'".$_POST['AlmacenOrigen']."'",
			"'".$_POST['AlmacenDestino']."'",
			"'".$_POST['SerialEquipo']."'",
			"'".$_POST['Dashboard']."'",
			$Type
		);
		$SQL_User=EjecutarSP('sp_tbl_Usuarios',$ParamUser,$_POST['P']);
		
		if($SQL_User){
			if($_POST['P']==5){
				$ID=$_POST['ID_Usuario'];
				
				if(isset($_POST['Cliente'])){
					$ParamDelete=array(
						"'".$ID."'",
						"'".$_SESSION['CodUser']."'",
						"1"
					);
					$SQL_Delete=EjecutarSP('sp_EliminarRelSucursalesClientesUsuario',$ParamDelete,$_POST['P']);
				}
				
			}else{
				$row_InsUser=sqlsrv_fetch_array($SQL_User);	
				$ID=$row_InsUser[0];
			}
			
			//Series
			#Primero limpiamos las series
			$ParamDeleteSerie=array(
				"'".$ID."'",
				"NULL",
				"NULL",
				"NULL",
				"2"
			);
			$SQL_DeleteSerie=EjecutarSP('sp_InsertarUsuariosSeries',$ParamDeleteSerie,$_POST['P']);
			
			#Insertamos las series
			if($SQL_DeleteSerie){
				$i=0;
				$CuentaSerie=count($_POST['TipoDocumento']);
				while($i<$CuentaSerie){
					if($_POST['TipoDocumento'][$i]!=""&&$_POST['Series'][$i]!=""){
						$ParamSeries=array(
							"'".$ID."'",
							"'".$_POST['Series'][$i]."'",
							"'".$_POST['TipoDocumento'][$i]."'",
							"'".$_POST['PermisoSerie'][$i]."'",
							"1"
						);
						$SQL_Series=EjecutarSP('sp_InsertarUsuariosSeries',$ParamSeries,$_POST['P']);
						if(!$SQL_Series){
							$sw_error=1;
							$msg_error="No se pudo insertar las series";
						}
					}
					$i++;
				}
			}
			
			//Proyectos
			#Primero limpiamos los proyectos
			$ParamDeleteProy=array(
				"'".$ID."'",
				"NULL",
				"2"
			);
			$SQL_DeleteProy=EjecutarSP('sp_InsertarUsuariosProyecto',$ParamDeleteProy,$_POST['P']);
			
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
						$SQL_Proy=EjecutarSP('sp_InsertarUsuariosProyecto',$ParamProy,$_POST['P']);
						if(!$SQL_Proy){
							$sw_error=1;
							$msg_error="No se pudo insertar el proyecto";
						}
					}
					$i++;
				}
			}
			
			//Clientes
			if(isset($_POST['Cliente'])){
				$i=0;
				$CuentaCliente=count($_POST['Cliente']);
				//echo $Cuenta;
				while($i<$CuentaCliente){
					if($_POST['Cliente'][$i]!=""){

						//Consultar si ya existe el cliente
						$SQL_ConsCliente=Seleccionar('uvw_tbl_ClienteUsuario','CodigoCliente',"ID_Usuario='".$ID."' and CodigoCliente='".$_POST['Cliente'][$i]."'");
						$row_ConsCliente=sqlsrv_fetch_array($SQL_ConsCliente);

						//Insertar el cliente
						if($row_ConsCliente['CodigoCliente']==""){
							$ParamInsertCliente=array(
								"'".$ID."'",
								"'".$_POST['Cliente'][$i]."'",
								"1"
							);
							$SQL_InsertCliente=EjecutarSP('sp_InsertarClienteUsuario',$ParamInsertCliente,$_POST['P']);
							if(!$SQL_InsertCliente){
								$sw_error=1;
								$msg_error="Ha ocurrido un error al insertar el cliente";
							}
						}

						//Insertar la sucursal
						$ParamInsertSucursal=array(
							"'".$ID."'",
							"'".$_POST['Cliente'][$i]."'",
							"'".$_POST['Sucursal'][$i]."'",
							"'".$_SESSION['CodUser']."'",
							"1"
						);
						$SQL_InsertSucursal=EjecutarSP('sp_InsertarClienteSucursalUsuario',$ParamInsertSucursal,$_POST['P']);

						if(!$SQL_InsertSucursal){
							$sw_error=1;
							$msg_error="Ha ocurrido un error al insertar la sucursal";
						}
					}
					$i++;
				}

				if($_POST['P']==5){
					$ParamDelete=array(
						"'".$ID."'",
						"'".$_SESSION['CodUser']."'",
						"2"
					);
					$SQL_Delete=EjecutarSP('sp_EliminarRelSucursalesClientesUsuario',$ParamDelete,$_POST['P']);
				}				
			}
			
			//Valores por defecto
			if(isset($_POST['TipoDocumento'])&&($_POST['TipoDocumento']!="")){
				$SQL=Seleccionar("uvw_tbl_CamposValoresDefecto","*","TipoObjeto='".$_POST['TipoDocumento']."'");
				
				$SQL_Delete=Eliminar("tbl_CamposValoresDefecto_Detalle","ID_Usuario='".$ID."' and TipoObjeto='".$_POST['TipoDocumento']."'");
				
				while($row=sqlsrv_fetch_array($SQL)){
					if($_POST['ValorCampo'.$row['ID_Campo']]!=""){
						$ParamInsertVal=array(
							"'".$row['ID_Campo']."'",
							"'".$row['TipoObjeto']."'",
							"'".$ID."'",
							"'".$_POST['ValorCampo'.$row['ID_Campo']]."'",
							"1"
						);
						$SQL_InsertVal=EjecutarSP('sp_tbl_CamposValoresDefecto_Detalle',$ParamInsertVal,$_POST['P']);
						if(!$SQL_InsertVal){
							$sw_error=1;
							$msg_error="Ha ocurrido un error al insertar los valores predeterminados";
						}
					}
					$i++;
				}		
			}

			
			($_POST['P']==5) ? $MsgReturn="OK_EditUser" : $MsgReturn="OK_User";
			
			sqlsrv_close($conexion);
			header('Location:usuarios.php?id='.base64_encode($ID).'&tl=1&a='.base64_encode($MsgReturn));
		}else{
			$sw_error=1;
			$msg_error="Ha ocurrido un error al insertar el usuario";
		}
	}catch (Exception $e) {
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
}

//Crear nuevo valor por defecto
if(isset($_POST['MM_Insert'])&&($_POST['MM_Insert']!="")){
	$Param=array(
		"'".$_POST['TipoDocumento']."'",
		"'".$_POST['NombreVariable']."'",
		"'".$_POST['NombreMostrar']."'"
	);
	$SQL=EjecutarSP('sp_tbl_CamposValoresDefecto',$Param);
	if($SQL){
		header('Location:usuarios.php?'.base64_decode($_POST['return']).'&a='.base64_encode("OK_NewParam"));
	}else{
		$sw_error=1;
		$msg_error="No se pudo insertar el nuevo parámetro";
	}
}

if($edit==1){//Editar usuario

	//Articulo	
	$SQL=Seleccionar('uvw_tbl_Usuarios','*',"ID_Usuario='".$IdUsuario."'");
	$row=sqlsrv_fetch_array($SQL);
	
	//Obtener clientes relacionados
	/* $Cons_RelClientes="Select * From uvw_tbl_ClienteUsuario Where ID_Usuario='".$row['ID_Usuario']."'";
	$SQL_RelClientes=sqlsrv_query($conexion,$Cons_RelClientes);
	//$Num_Sucursal=sqlsrv_num_rows($SQL_Sucursal);
	$ClientesUsuario=array();
	$i=0;
	while($row_RelClientes=sqlsrv_fetch_array($SQL_RelClientes)){
		$ClientesUsuario[$i]=$row_RelClientes['CodigoCliente'];
		$i++;
	}	 */
		
	//Series asociadas
	$SQL_SeriesUsuario=Seleccionar("uvw_tbl_UsuariosSeries","*","[ID_Usuario]='".$IdUsuario."'",'DeTipoDocumento');
	$Num_SeriesUsuario=sql_num_rows($SQL_SeriesUsuario);
	
	//Proyetos asociados
	$SQL_ProyectosUsuario=Seleccionar("uvw_tbl_UsuariosProyectos","*","[ID_Usuario]='".$IdUsuario."'",'DeProyecto');
	$row_ProyectosUsuario=sqlsrv_fetch_array($SQL_ProyectosUsuario);
}
	
//Estados
$SQL_Estados=Seleccionar('uvw_tbl_Estados','*');

//Perfiles
$SQL_Perfiles=Seleccionar('uvw_tbl_PerfilesUsuarios','*','','PerfilUsuario');

//Proveedores
$SQL_Proveedores=Seleccionar('uvw_Sap_tbl_Proveedores','CodigoCliente, NombreCliente','','NombreCliente');

//Empleados SAP
$SQL_Empleados=Seleccionar('uvw_Sap_tbl_Empleados','ID_Empleado, NombreEmpleado','','NombreEmpleado');

//Tipos de documentos SAP
$SQL_TiposDocumentos=Seleccionar("uvw_tbl_ObjetosSAP","*",'','CategoriaObjeto, DeTipoDocumento');

//Dimensiones de reparto
$SQL_DimReparto=Seleccionar('uvw_Sap_tbl_NombresDimensionesReparto','*');

//Proyectos
$SQL_Proyectos=Seleccionar('uvw_Sap_tbl_Proyectos','*','','DeProyecto');

//Dashboard
$SQL_Dashboard=Seleccionar('tbl_Dashboard','*');

//Almacenes
$SQL_AlmacenOrigen=Seleccionar('uvw_Sap_tbl_Almacenes','*');
$SQL_AlmacenDestino=Seleccionar('uvw_Sap_tbl_Almacenes','*');


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
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_User"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El usuario ha sido agregado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_EditUser"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El usuario ha sido editado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_CopyUser"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'Los datos del usuario se han copiado exitosamente',
                icon: 'success'
            });
		});		
		</script>";
}
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
	.swal2-container {
	  	z-index: 9000;
	}
</style>
<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#CodigoSAP").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var CodigoSAP=document.getElementById('CodigoSAP').value;
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:25,id:CodigoSAP},
				dataType:'json',
				async: false,
				success: function(data){
					document.getElementById('Email').value=data.Email;
					document.getElementById('Cedula').value=data.Cedula;
					document.getElementById('Telefono').value=data.Telefono;
				}
			});
			$('.ibox-content').toggleClass('sk-loading',false);
		});
	});
</script>
<script>
function ValidarUsuario(User){
	var spinner=document.getElementById('spinner1');
	spinner.style.visibility='visible';
	$.ajax({
		type: "GET",
		url: "includes/procedimientos.php?type=1&Usuario="+User,
		success: function(response){
			spinner.style.visibility='hidden';
			document.getElementById('Validar').innerHTML=response;			
			if(response=="<p class='text-danger'><i class='fa fa-times-circle-o'></i> No disponible</p>"){
				document.getElementById('Crear').disabled=true;
			}else{
				document.getElementById('Crear').disabled=false;
			}
		}
	});
}

function Mostrar(){
	var x = document.getElementById("Password").getAttribute("type");
	if(x=="password"){
		document.getElementById('Password').setAttribute('type','text');
		document.getElementById('VerPass').setAttribute('class','glyphicon glyphicon-eye-close');
		document.getElementById('aVerPass').setAttribute('title','Ocultar contrase'+String.fromCharCode(241)+'a');
	}else{
		document.getElementById('Password').setAttribute('type','password');
		document.getElementById('VerPass').setAttribute('class','glyphicon glyphicon-eye-open');
		document.getElementById('aVerPass').setAttribute('title','Mostrar contrase'+String.fromCharCode(241)+'a');
	}	
}
    /*$.expr[':'].icontains = function(obj, index, meta, stack){
    return (obj.textContent || obj.innerText || jQuery(obj).text() || '').toLowerCase().indexOf(meta[3].toLowerCase()) >= 0;
    };
    $(document).ready(function(){
        $('#buscador').keyup(function(){
			 buscar = $(this).val();
			 $('#lista #lista2').removeClass('panel-success');
				if(jQuery.trim(buscar) != ''){
				   $("#lista #lista2:icontains('" + buscar + "')").addClass('panel-success');
				}
		});
    });*/  

function BuscarSucursal(id){
	$('.ibox-content').toggleClass('sk-loading',true);
	var Cliente=document.getElementById('Cliente'+id).value;
	$.ajax({
		type: "POST",
		async: false,
		url: "ajx_cbo_select.php?type=3&id="+Cliente,
		success: function(response){
			$('#Sucursal'+id).html(response).fadeIn();
			$('#Sucursal'+id).trigger('change');
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
	$('.ibox-content').toggleClass('sk-loading',false);
}

function BuscarSerieDoc(id){
	$('.ibox-content').toggleClass('sk-loading',true);
	var TipoDocumento=document.getElementById('TipoDocumento'+id).value;
	$.ajax({
		type: "POST",
		url: "ajx_cbo_select.php?type=25&id="+TipoDocumento,
		success: function(response){
			$('#Series'+id).html(response).fadeIn();
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
	$('.ibox-content').toggleClass('sk-loading',false);
}

function CopiarParam(){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		async: false,
		url: "us_copiar_usuario.php?id=<?php echo base64_encode($IdUsuario);?>",
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
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
                            <a href="gestionar_usuarios.php">Gestionar usuarios</a>
                        </li>
                        <li class="active">
                            <strong><?php echo $Title;?></strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			 <div class="modal inmodal fade" id="myModal" tabindex="1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content" id="ContenidoModal">
						
					</div>
				</div>
			</div>
			 <form action="usuarios.php" method="post" class="form-horizontal" id="AgregarUsuario" enctype="multipart/form-data">				 
			<div class="row">
				<div class="ibox-content">
					<?php include("includes/spinner.php"); ?>
					<div class="form-group">
						<div class="col-lg-12">
							<?php 			
								if(isset($_GET['return'])){
									$return=base64_decode($_GET['pag'])."?".base64_decode($_GET['return']);						
								}else{
									$return="gestionar_usuarios.php?";
								}
								$return=QuitarParametrosURL($return,array("a"));
							?>
							<input type="hidden" id="ID_Usuario" name="ID_Usuario" value="<?php if($edit==1){echo $row['ID_Usuario'];}?>" />
							<input type="hidden" id="return" name="return" value="<?php echo base64_encode($return);?>" />
							<input type="hidden" id="P" name="P" value="<?php if($edit==1){ echo "5";}else{echo "4";}?>" />

							<?php if($edit==1){?>
							<button class="btn btn-warning" type="submit" id="Crear"><i class="fa fa-refresh"></i> Actualizar usuario</button> 
							<?php }else{?>
							<button class="btn btn-primary" type="submit" id="Crear"><i class="fa fa-check"></i> Crear usuario</button>
							<?php }?>
							<a href="<?php echo $return;?>" class="btn btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
							<?php if($edit==1){?>
							<button type="button" class="btn btn-success pull-right" onClick="CopiarParam();"><i class="fa fa-copy"></i> Copiar parámetros desde otro usuario</button>
							<?php }?>
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
						<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-user-circle"></i> Información de usuario</a></li>
						<li><a data-toggle="tab" href="#tab-2" onClick="ConsultarTab('2');"><i class="fa fa-group"></i> Clientes asociados</a></li>
						<li><a data-toggle="tab" href="#tab-3" onClick="ConsultarTab('3');"><i class="fa fa-edit"></i> Valores por defecto</a></li>
					</ul>
				    <div class="tab-content">
						<div id="tab-1" class="tab-pane active">
							<div class="ibox">
								<div class="ibox-title bg-success">
									<h5><i class="fa fa-user-circle"></i> Datos de usuario</h5>
									 <a class="collapse-link pull-right">
										<i class="fa fa-chevron-up"></i>
									</a>	
								</div>		
								<div class="ibox-content">		
									<div class="form-group">
										<label class="col-lg-1 control-label">Nombre</label>
										<div class="col-lg-3"><input name="Nombre" type="text" required="required" class="form-control" id="Nombre" value="<?php if($edit==1){echo $row['Nombre'];}?>"></div>
										<label class="col-lg-1 control-label">Segundo nombre</label>
										<div class="col-lg-3"><input name="SegundoNombre" type="text" class="form-control" id="SegundoNombre" value="<?php if($edit==1){echo $row['SegundoNombre'];}?>"></div>
										<label class="col-lg-1 control-label">Apellido</label>
										<div class="col-lg-3"><input name="Apellido" type="text" required="required" class="form-control" id="Apellido" value="<?php if($edit==1){echo $row['Apellido'];}?>"></div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Segundo apellido</label>
										<div class="col-lg-3"><input name="SegundoApellido" type="text" class="form-control" id="SegundoApellido" value="<?php if($edit==1){echo $row['SegundoApellido'];}?>"></div>
										<label class="col-lg-1 control-label">Cédula</label>
										<div class="col-lg-3"><input name="Cedula" type="text" class="form-control" id="Cedula" value="<?php if($edit==1){echo $row['Cedula'];}?>"></div>
										<label class="col-lg-1 control-label">Télefono</label>
										<div class="col-lg-3"><input name="Telefono" type="text" class="form-control" id="Telefono" value="<?php if($edit==1){echo $row['Telefono'];}?>"></div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Usuario</label>
										<div class="col-lg-2"><input name="Usuario" type="text" required="required" class="form-control" id="Usuario" onChange="ValidarUsuario(this.value);" value="<?php if($edit==1){echo $row['Usuario'];}?>" <?php if($edit==1){ echo "readonly";}?>></div>
										<div id="Validar" class="col-lg-1">
											<div id="spinner1" style="visibility: hidden;" class="sk-spinner sk-spinner-wave">
												<div class="sk-rect1"></div>
												<div class="sk-rect2"></div>
												<div class="sk-rect3"></div>
												<div class="sk-rect4"></div>
												<div class="sk-rect5"></div>
											</div>
										</div>
										<label class="col-lg-1 control-label">Contrase&ntilde;a</label>
										<div class="col-lg-3"><input name="Password" type="password" class="form-control example1" id="Password" value=""><a href="#" id="aVerPass" onClick="javascript:Mostrar();" title="Mostrar contrase&ntilde;a" class="btn btn-default btn-xs"><span id="VerPass" class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a></div>
										<label class="col-lg-1 control-label">&nbsp;</label>
										<div class="col-lg-3"><div id="lvlPass" class="pwstrength_viewport_progress"></div></div>
									</div>
									<div class="form-group" id="pwd-container1">
										<label class="col-lg-1 control-label">Email</label>
										<div class="col-lg-3"><input name="Email" type="email" class="form-control" id="Email" value="<?php if($edit==1){echo $row['Email'];}?>"></div>
										<div class="col-lg-1">
											<div class="switch pull-right">
												<div class="onoffswitch">
													<input name="CambioPass" type="checkbox" class="onoffswitch-checkbox" id="CambioPass" value="1">
													<label class="onoffswitch-label" for="CambioPass">
														<span class="onoffswitch-inner"></span>
														<span class="onoffswitch-switch"></span>
													</label>
												</div>
											</div>
										</div>
										<div class="col-lg-3">
											<p class="text-primary">Solicitar cambio de contrase&ntilde;a al primer inicio de sesi&oacute;n.</p>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Time Out</label>
										<div class="col-lg-3"><input name="TimeOut" type="text" required="required" class="form-control" id="TimeOut" value="900" maxlength="4" value="<?php if($edit==1){echo $row['TimeOut'];}?>"></div>
										<label class="col-lg-1 control-label">Código SAP</label>
										<div class="col-lg-3">
											<select name="CodigoSAP" class="form-control select2" id="CodigoSAP">
												<option value="">Seleccione...</option>
											  <?php while($row_Empleados=sqlsrv_fetch_array($SQL_Empleados)){?>
													<option value="<?php echo $row_Empleados['ID_Empleado'];?>" <?php if(isset($row['CodigoSAP'])&&(strcmp($row_Empleados['ID_Empleado'],$row['CodigoSAP'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Empleados['ID_Empleado']." - ".$row_Empleados['NombreEmpleado'];?></option>
											  <?php }?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Perfil</label>
										<div class="col-lg-3">
											<select name="PerfilUsuario" class="form-control" id="PerfilUsuario">
											  <?php while($row_Perfiles=sqlsrv_fetch_array($SQL_Perfiles)){?>
													<option value="<?php echo $row_Perfiles['ID_PerfilUsuario'];?>" <?php if(($edit==1)&&(strcmp($row_Perfiles['ID_PerfilUsuario'],$row['ID_PerfilUsuario'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Perfiles['PerfilUsuario'];?></option>
											  <?php }?>
											</select>
									  	</div>
										<label class="col-lg-1 control-label">Estado</label>
										<div class="col-lg-3">
											<select name="Estado" class="form-control" id="Estado">
											  <?php while($row_Estado=sqlsrv_fetch_array($SQL_Estados)){?>
													<option value="<?php echo $row_Estado['Cod_Estado'];?>" <?php if(($edit==1)&&(strcmp($row_Estado['Cod_Estado'],$row['Estado'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Estado['NombreEstado'];?></option>
											  <?php }?>
											</select>
									  	</div>
										<label class="col-lg-1 control-label">Proveedor</label>
										<div class="col-lg-3">
											<select name="Proveedor" class="form-control select2" id="Proveedor">
													<option value="">Seleccione...</option>
											  <?php while($row_Proveedores=sqlsrv_fetch_array($SQL_Proveedores)){?>
													<option value="<?php echo $row_Proveedores['CodigoCliente'];?>" <?php if(($edit==1)&&(strcmp($row_Proveedores['CodigoCliente'],$row['CodigoSAPProv'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Proveedores['NombreCliente'];?></option>
											  <?php }?>
											</select>
									  	</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Service One</label>
										<div class="col-lg-3">
											<label class="checkbox-inline i-checks"><input name="chkServiceOne" id="chkServiceOne" type="checkbox" value="1" <?php if($edit==1){if($row['UserServiceOne']==1){echo "checked=\"checked\"";}}?>> Este usuario tiene acceso a Service One</label>
										</div>
										<label class="col-lg-1 control-label">Serial equipo</label>
										<div class="col-lg-3"><input name="SerialEquipo" type="text" class="form-control" id="SerialEquipo" value="<?php if($edit==1){echo $row['SerialEquipo'];}?>"></div>
										<label class="col-lg-1 control-label">Dashboard inicial</label>
										<div class="col-lg-3">
											<select name="Dashboard" class="form-control" id="Dashboard">
												<?php while($row_Dashboard=sqlsrv_fetch_array($SQL_Dashboard)){?>
													<option value="<?php echo $row_Dashboard['IdDashboard'];?>" <?php if(($edit==1)&&(strcmp($row_Dashboard['IdDashboard'],$row['Dashboard'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Dashboard['NombreDashboard'];?></option>
											  <?php }?>
											</select>
									  	</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Sales One</label>
										<div class="col-lg-3">
											<label class="checkbox-inline i-checks"><input name="chkSalesOne" id="chkSalesOne" type="checkbox" value="1" <?php if($edit==1){if($row['UserSalesOne']==1){echo "checked=\"checked\"";}}?>> Este usuario tiene acceso a Sales One</label>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Firma usuario</label>										
										<div class="col-lg-3">
											<?php LimpiarDirTempFirma();
											if(($edit==0)||(($edit==1)&&($row['AnxFirma']==""))){?>
												<button class="btn btn-primary" type="button" id="FirmaUsuario" onClick="AbrirFirma('SigUser');"><i class="fa fa-pencil-square-o"></i> Realizar firma</button> 
											<?php }else{?>
												<button class="btn btn-warning" type="button" id="FirmaUsuario" onClick="AbrirFirma('SigUser');"><i class="fa fa-pencil-square-o"></i> Actualizar firma</button> 
											<?php }?>
											<input type="hidden" id="SigUser" name="SigUser" value="" />
											<span class="text-primary font-bold">O cargue un archivo con la imagen de la firma</span>
											<input name="FirmaCargadaUsuario" type="file" id="FirmaCargadaUsuario" class="m-t-sm" />
										</div>							
									</div>
									<div class="form-group">
										<div class="col-lg-1">&nbsp;</div>
										<div class="col-lg-3">
											<?php if(($edit==1)&&($row['AnxFirma']!="")){?>
												<img id="ImgSigUser" style="max-width: 100%; height: auto;" src="<?php echo $dir_new.$row['AnxFirma'];?>" alt="" />
											<?php }else{?>
												<img id="ImgSigUser" style="display: none; max-width: 100%; height: auto;" src="" alt="" />
											<?php }?>
										</div>										
									</div>	
								</div>
							</div>
							<div class="ibox">
								<div class="ibox-title bg-success">
									<h5><i class="fa fa-list"></i> Series asociadas</h5>
									 <a class="collapse-link pull-right">
										<i class="fa fa-chevron-up"></i>
									</a>	
								</div>		
								<div class="ibox-content">
									  <?php  $Cont=1;
								  if($edit==1&&$Num_SeriesUsuario>0){
									  $row_SeriesUsuario=sqlsrv_fetch_array($SQL_SeriesUsuario);
									do{ 
										$SQL_SeriesDocumentos=Seleccionar('uvw_Sap_tbl_SeriesDocumentos','IdSeries, DeSeries',"IdTipoDocumento='".$row_SeriesUsuario['IdTipoDocumento']."'",'DeSeries');
									?>
								  <div id="divSerie_<?php echo $Cont;?>" class="form-group">					 
									<?php /*?> <input name="Cliente[]" type="hidden" id="Cliente<?php echo $Cont;?>" value="" onChange="BuscarSucursal('<?php echo $Cont;?>');"><?php */?>
									 <label class="col-lg-1 control-label">Tipo de documento</label>
									 <div class="col-lg-3">
										 <select name="TipoDocumento[]" class="form-control" id="TipoDocumento<?php echo $Cont;?>" onChange="BuscarSerieDoc('<?php echo $Cont;?>');">
												<option value="">Seleccione...</option>
										  <?php $CatActual="";
												while($row_TiposDocumentos=sqlsrv_fetch_array($SQL_TiposDocumentos)){
											 		if($CatActual!=$row_TiposDocumentos['CategoriaObjeto']){
														echo "<optgroup label='".$row_TiposDocumentos['CategoriaObjeto']."'></optgroup>";
														$CatActual=$row_TiposDocumentos['CategoriaObjeto'];
													}?>
													<option value="<?php echo $row_TiposDocumentos['IdTipoDocumento'];?>" <?php if((strcmp($row_TiposDocumentos['IdTipoDocumento'],$row_SeriesUsuario['IdTipoDocumento'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TiposDocumentos['DeTipoDocumento'];?></option>
										  <?php }?>
										</select>
									 </div>
									 <label class="col-lg-1 control-label">Serie</label>
									 <div class="col-lg-2">
										<select name="Series[]" class="form-control" id="Series<?php echo $Cont;?>">
											<option value="">Seleccione...</option>
										  <?php while($row_SeriesDocumentos=sqlsrv_fetch_array($SQL_SeriesDocumentos)){?>
												<option value="<?php echo $row_SeriesDocumentos['IdSeries'];?>" <?php if((strcmp($row_SeriesDocumentos['IdSeries'],$row_SeriesUsuario['IdSeries'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_SeriesDocumentos['DeSeries'];?></option>
										  <?php }?>
										</select>
									 </div>
									 <label class="col-lg-1 control-label">Permiso</label>
									 <div class="col-lg-2">
										<select name="PermisoSerie[]" class="form-control" id="PermisoSerie<?php echo $Cont;?>">
											<option value="1" <?php if($row_SeriesUsuario['Permiso']==1){ echo "selected=\"selected\"";}?>>Solo consulta</option>
											<option value="2" <?php if($row_SeriesUsuario['Permiso']==2){ echo "selected=\"selected\"";}?>>Solo creación</option>
											<option value="3" <?php if($row_SeriesUsuario['Permiso']==3){ echo "selected=\"selected\"";}?>>Creación y consulta</option>
										</select>
									 </div>
									 <div class="col-lg-2">
										<button type="button" id="btnSeries<?php echo $Cont;?>" class="btn btn-warning btn-xs btn_del"><i class="fa fa-minus"></i> Remover</button>
									 </div>
								 </div>
								 <?php 
										$Cont++;
										$SQL_TiposDocumentos=Seleccionar("uvw_tbl_ObjetosSAP","*",'','CategoriaObjeto, DeTipoDocumento');
									    
									} while($row_SeriesUsuario=sqlsrv_fetch_array($SQL_SeriesUsuario));
								} ?>
								 <div id="divSerie_<?php echo $Cont;?>" class="form-group">					 
									<?php /*?> <input name="Cliente[]" type="hidden" id="Cliente<?php echo $Cont;?>" value="" onChange="BuscarSucursal('<?php echo $Cont;?>');"><?php */?>
									 <label class="col-lg-1 control-label">Tipo de documento</label>
									 <div class="col-lg-3">
										 <select name="TipoDocumento[]" class="form-control" id="TipoDocumento<?php echo $Cont;?>" onChange="BuscarSerieDoc('<?php echo $Cont;?>');">
												<option value="">Seleccione...</option>
										  <?php $CatActual="";
											 while($row_TiposDocumentos=sqlsrv_fetch_array($SQL_TiposDocumentos)){
											 	if($CatActual!=$row_TiposDocumentos['CategoriaObjeto']){
													echo "<optgroup label='".$row_TiposDocumentos['CategoriaObjeto']."'></optgroup>";
													$CatActual=$row_TiposDocumentos['CategoriaObjeto'];
												}?>
												<option value="<?php echo $row_TiposDocumentos['IdTipoDocumento'];?>"><?php echo $row_TiposDocumentos['DeTipoDocumento'];?></option>
										  <?php }?>
										</select>
									 </div>
									 <label class="col-lg-1 control-label">Serie</label>
									 <div class="col-lg-2">
										<select name="Series[]" class="form-control" id="Series<?php echo $Cont;?>">
											<option value="">Seleccione...</option>
										</select>
									 </div>
									 <label class="col-lg-1 control-label">Permiso</label>
									 <div class="col-lg-2">
										<select name="PermisoSerie[]" class="form-control" id="PermisoSerie<?php echo $Cont;?>">
											<option value="1">Solo consulta</option>
											<option value="2">Solo creación</option>
											<option value="3">Creación y consulta</option>
										</select>
									 </div>
									 <div class="col-lg-2">
										<button type="button" id="btnSeries<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addFieldTDoc(this);"><i class="fa fa-plus"></i> Añadir otro</button>	
									 </div>
								</div>
							  </div>
							</div>
							<div class="ibox">
								<div class="ibox-title bg-success">
									<h5><i class="fa fa-briefcase"></i> Proyectos asociados</h5>
									 <a class="collapse-link pull-right">
										<i class="fa fa-chevron-up"></i>
									</a>	
								</div>		
								<div class="ibox-content">
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
								</div>
							</div>
							<div class="ibox">
								<div class="ibox-title bg-success">
									<h5><i class="fa fa-tags"></i> Dimensiones de reparto asociadas</h5>
									 <a class="collapse-link pull-right">
										<i class="fa fa-chevron-up"></i>
									</a>	
								</div>		
								<div class="ibox-content">
									 <?php  while($row_DimReparto=sqlsrv_fetch_array($SQL_DimReparto)){
											//Cargar centros de costos por cada dimension
											$SQL_CentroCostos=Seleccionar('uvw_Sap_tbl_CentrosCostos','*',"DimCode='".$row_DimReparto['CodDim']."'");?>
									<div class="form-group">
										<label class="col-lg-1 control-label"><?php echo $row_DimReparto['NombreDim'];?><br><span class="text-muted"><?php echo $row_DimReparto['TipoDim'];?></span></label>
										<div class="col-lg-3">
											<select name="Dimension<?php echo $row_DimReparto['CodDim'];?>" class="form-control" id="Dimension<?php echo $row_DimReparto['CodDim'];?>">
												<option value="">(Ninguno)</option>
											  <?php while($row_CentroCostos=sqlsrv_fetch_array($SQL_CentroCostos)){?>
													<option value="<?php echo $row_CentroCostos['PrcCode'];?>" <?php if(($edit==1)&&(strcmp($row_CentroCostos['PrcCode'],$row['CentroCosto'.$row_DimReparto['CodDim']])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_CentroCostos['PrcName'];?></option>
											  <?php }?>
											</select>
										</div>
									</div>
									<?php }?>
								</div>
							</div>
							<div class="ibox">
								<div class="ibox-title bg-success">
									<h5><i class="fa fa-cubes"></i> Almacenes</h5>
									 <a class="collapse-link pull-right">
										<i class="fa fa-chevron-up"></i>
									</a>	
								</div>		
								<div class="ibox-content">
								  <div class="form-group">
									 <label class="col-lg-1 control-label">Almacén de origen</label>
									 <div class="col-lg-3">
										 <select name="AlmacenOrigen" class="form-control select2" id="AlmacenOrigen">
											 	<option value="">(Ninguno)</option>
										  <?php while($row_AlmacenOrigen=sqlsrv_fetch_array($SQL_AlmacenOrigen)){?>
												<option value="<?php echo $row_AlmacenOrigen['WhsCode'];?>" <?php if(($edit==1)&&(strcmp($row_AlmacenOrigen['WhsCode'],$row['AlmacenOrigen'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_AlmacenOrigen['WhsName'];?></option>
										  <?php }?>
										</select>
									 </div>
									  <label class="col-lg-1 control-label">Almacén de destino</label>
									 <div class="col-lg-3">
										 <select name="AlmacenDestino" class="form-control select2" id="AlmacenDestino">
											 	<option value="">(Ninguno)</option>
										  <?php while($row_AlmacenDestino=sqlsrv_fetch_array($SQL_AlmacenDestino)){?>
												<option value="<?php echo $row_AlmacenDestino['WhsCode'];?>" <?php if(($edit==1)&&(strcmp($row_AlmacenDestino['WhsCode'],$row['AlmacenDestino'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_AlmacenDestino['WhsName'];?></option>
										  <?php }?>
										</select>
									 </div>
								 </div>
								</div>
							</div>
						</div>
						<div id="tab-2" class="tab-pane">
							<div id="dv_clientes" class="panel-body">
								
							</div>			
						</div>
						<div id="tab-3" class="tab-pane">
							<div id="dv_valores_defecto" class="panel-body">
								
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
		 $("#AgregarUsuario").validate({
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
		 
		 $('.chosen-select').chosen({width: "100%"});
		 $(".select2").select2();
		  $('.i-checks').iCheck({
			 checkboxClass: 'icheckbox_square-green',
             radioClass: 'iradio_square-green',
          });
		 
		 $(".btn_del").each(function (el){
			$(this).bind("click",delRow);
		});
		 
		document.getElementById('Nombre').focus();
		 
	  	// Example 1
		var options1 = {};
		options1.ui = {
			container: "#pwd-container1",
			showVerdictsInsideProgressBar: true,
			viewports: {
				progress: ".pwstrength_viewport_progress"
			}
		};
		options1.common = {
			debug: false,
		};
		$('.example1').pwstrength(options1);
	
	});
</script>
<script>
function addField(btn){//Clonar divDir
	var clickID = parseInt($(btn).parent('div').parent('div').attr('id').replace('divCliente_',''));
	//alert($(btn).parent('div').attr('id'));
	//alert(clickID);
	$("#Cliente"+clickID).select2("destroy");
	$("#Sucursal"+clickID).select2("destroy");
	var newID = (clickID+1);
	
	//var $example = $(".select2").select2();
	//$example.select2("destroy");

	$newClone = $('#divCliente_'+clickID).clone(true);

	//div
	$newClone.attr("id",'divCliente_'+newID);

	//select
	$newClone.children("div").eq(0).children("select").eq(0).attr('id','Cliente'+newID);
	$newClone.children("div").eq(1).children("select").eq(0).attr('id','Sucursal'+newID);
	
	$newClone.children("div").eq(0).children("select").eq(0).attr('onChange','BuscarSucursal('+newID+');');	

	//inputs
	//$newClone.children("input").eq(0).attr('id','Cliente'+newID);
	//$newClone.children("div").eq(0).children("div").eq(0).children("input").eq(0).attr('id','NombreCliente'+newID);
	
	//$newClone.children("input").eq(0).attr('onChange','BuscarSucursal('+newID+');');	

	//button
	$newClone.children("div").eq(2).children("button").eq(0).attr('id','btnCliente'+newID);

	$newClone.insertAfter($('#divCliente_'+clickID));

	document.getElementById('btnCliente'+clickID).innerHTML="<i class='fa fa-minus'></i> Remover";
	document.getElementById('btnCliente'+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById('btnCliente'+clickID).setAttribute('onClick','delRow2(this);');
	
	//Limpiar campos
	document.getElementById('Sucursal'+newID).value='';
	$("#Sucursal"+newID).empty();
	$("#Sucursal"+newID).append($('<option>',{ value: '', text : 'Seleccione...' }));
	
	$("#Cliente"+clickID).select2();
	$("#Cliente"+newID).select2();
	
	$("#Sucursal"+clickID).select2();
	$("#Sucursal"+newID).select2();
	//document.getElementById('Cliente'+newID).value='';
	//document.getElementById('NombreCliente'+newID).value='';

	//$(".select2").select2();
	//EasyComplete(newID);
	//$("#"+clickID).addEventListener("click",delRow);

	//$("#"+clickID).bind("click",delRow);
}
	
function addFieldTDoc(btn){//Clonar divDir
	var clickID = parseInt($(btn).parent('div').parent('div').attr('id').replace('divSerie_',''));
	//alert($(btn).parent('div').attr('id'));
	//alert(clickID);
	var newID = (clickID+1);
	
	//var $example = $(".select2").select2();
	//$example.select2("destroy");

	$newClone = $('#divSerie_'+clickID).clone(true);

	//div
	$newClone.attr("id",'divSerie_'+newID);

	//select
	$newClone.children("div").eq(0).children("select").eq(0).attr('id','TipoDocumento'+newID);
	$newClone.children("div").eq(1).children("select").eq(0).attr('id','Series'+newID);
	$newClone.children("div").eq(2).children("select").eq(0).attr('id','PermisoSerie'+newID);
	
	$newClone.children("div").eq(0).children("select").eq(0).attr('onChange','BuscarSerieDoc('+newID+');');	

	//inputs
	//$newClone.children("input").eq(0).attr('id','Cliente'+newID);
	//$newClone.children("div").eq(0).children("div").eq(0).children("input").eq(0).attr('id','NombreCliente'+newID);
	
	//$newClone.children("input").eq(0).attr('onChange','BuscarSucursal('+newID+');');	

	//button
	$newClone.children("div").eq(3).children("button").eq(0).attr('id','btnSeries'+newID);

	$newClone.insertAfter($('#divSerie_'+clickID));

	document.getElementById('btnSeries'+clickID).innerHTML="<i class='fa fa-minus'></i> Remover";
	document.getElementById('btnSeries'+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById('btnSeries'+clickID).setAttribute('onClick','delRow2(this);');
	
	//Limpiar campos
	document.getElementById('Series'+newID).value='';
	document.getElementById('PermisoSerie'+newID).value='1';
	$("#Series"+newID).empty();
	$("#Series"+newID).append($('<option>',{ value: '', text : 'Seleccione...' }));
}
	
function addFieldProy(btn){//Clonar divDir
	var clickID = parseInt($(btn).parent('div').parent('div').attr('id').replace('divProy_',''));
	//alert($(btn).parent('div').attr('id'));
	//alert(clickID);
	$("#Proyecto"+clickID).select2("destroy");
	var newID = (clickID+1);
	
	//var $example = $(".select2").select2();
	//$example.select2("destroy");

	$newClone = $('#divProy_'+clickID).clone(true);

	//div
	$newClone.attr("id",'divProy_'+newID);

	//select
	$newClone.children("div").eq(0).children("select").eq(0).attr('id','Proyecto'+newID);
	$newClone.children("label").eq(0).html('Proyecto '+newID);
	
//	$newClone.children("div").eq(0).children("select").eq(0).select2('destroy');
//	$newClone.children("div").eq(0).children("select").eq(0).select2();


	//button
	$newClone.children("div").eq(1).children("button").eq(0).attr('id','btnProy'+newID);

	$newClone.insertAfter($('#divProy_'+clickID));

	document.getElementById('btnProy'+clickID).innerHTML="<i class='fa fa-minus'></i> Remover";
	document.getElementById('btnProy'+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById('btnProy'+clickID).setAttribute('onClick','delRow2(this);');
	
	//Limpiar campos
	document.getElementById('Proyecto'+newID).value='';
	$("#Proyecto"+clickID).select2();
	$("#Proyecto"+newID).select2();
}
</script>
<script>
function delRow(){//Eliminar div
	$(this).parent('div').parent('div').remove();
}
function delRow2(btn){//Eliminar div
	$(btn).parent('div').parent('div').remove();
}
</script>
<script>
var tab_2=0;
var tab_3=0;
	
function ConsultarTab(type){
	if(type==2){//Clientes
		if(tab_2==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "us_clientes.php?id=<?php if($edit==1){echo base64_encode($row['ID_Usuario']);}?>",
				success: function(response){
					$('#dv_clientes').html(response).fadeIn();
					$("#Cliente1").select2();
					$("#Sucursal1").select2();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_2=1;
				}
			});
		}
	}else if(type==3){//Valores predeterminados
		if(tab_3==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "us_valores_defecto.php?id=<?php if($edit==1){echo base64_encode($row['ID_Usuario']);}?>",
				success: function(response){
					$('#dv_valores_defecto').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_3=1;
				}
			});
		}
	}
}

function CrearParametro(){
	$('.ibox-content').toggleClass('sk-loading',true);
	
	$.ajax({
		type: "POST",
		url: "md_crear_parametro_valdefecto.php",
		data:{
			return:'<?php echo base64_encode($_SERVER['QUERY_STRING']);?>'
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