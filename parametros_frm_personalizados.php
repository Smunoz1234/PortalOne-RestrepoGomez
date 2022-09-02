<?php 
require_once("includes/conexion.php");
PermitirAcceso(216);

$sw_error=0;
$dir_new=CrearObtenerDirAnx("formularios/monitoreos_temperaturas/planos");
//Insertar datos
if(isset($_POST['frmType'])&&($_POST['frmType']!="")){
	try{
		
		if($_POST['TipoDoc']=="Bodegas"){
			if($_FILES['AnexoBodega']['tmp_name']!=""){
				if(is_uploaded_file($_FILES['AnexoBodega']['tmp_name'])){
					$Nombre_Archivo=$_FILES['AnexoBodega']['name'];
					$NuevoNombre=FormatoNombreAnexo($Nombre_Archivo);
					if(!move_uploaded_file($_FILES['AnexoBodega']['tmp_name'],$dir_new.$NuevoNombre[0])){
						$sw_error=1;
						$msg_error="No se pudo mover el anexo a la carpeta de anexos local";
					}
				}else{
					$sw_error=1;
					$msg_error="No se pudo cargar el anexo";
				}
			}else{
				if($_POST['Metodo']==1){
					$Ruta=ObtenerVariable("PlanoGenericoMonitoreos");
					if($Ruta!=""){
						$ar=explode("\\",$Ruta);
						$Plano=end($ar);
						$NuevoNombre=FormatoNombreAnexo($Plano);
						if(!copy($Ruta,$dir_new.$NuevoNombre[0])){
							$sw_error=1;
							$msg_error="No se pudo mover el anexo por defecto a la carpeta de anexos local";
						}
					}else{
						$NuevoNombre[0]="";
					}
				}else{
					$NuevoNombre[0]="";
				}
				
			}

			$Param=array(
				"'".$_POST['TipoDoc']."'",
				"'".$_POST['CodigoBodega']."'",
				"'".$_POST['ID_Actual']."'",
				"'".$_POST['NombreBodega']."'",
				"'".$_POST['ComentariosBodega']."'",
				"'".$_POST['EstadoBodega']."'",
				"'".$_POST['Metodo']."'",
				"'".$_SESSION['CodUser']."'",
				"'".$_POST['ClienteBodega']."'",
				"'".$_POST['SucursalBodega']."'",
				"'".$NuevoNombre[0]."'"
			);
			$SQL=EjecutarSP('sp_tbl_FrmPuerto',$Param);
			if(!$SQL){
				$sw_error=1;
				$msg_error="No se pudo insertar los datos";
			}
		}
		elseif($_POST['TipoDoc']=="Productos"){
			$Param=array(
				"'".$_POST['TipoDoc']."'",
				"'".$_POST['CodigoProducto']."'",
				"'".$_POST['ID_Actual']."'",
				"'".$_POST['NombreProducto']."'",
				"'".$_POST['ComentariosProducto']."'",
				"'".$_POST['EstadoProducto']."'",
				"'".$_POST['Metodo']."'",
				"'".$_SESSION['CodUser']."'"
			);
			$SQL=EjecutarSP('sp_tbl_FrmPuerto',$Param);
			if(!$SQL){
				$sw_error=1;
				$msg_error="No se pudo insertar los datos";
			}			
		}
		elseif($_POST['TipoDoc']=="Transportes"){
			//Transportes
			$Param=array(
				"'".$_POST['TipoDoc']."'",
				"'".$_POST['CodigoTransporte']."'",
				"'".$_POST['ID_Actual']."'",
				"'".$_POST['NombreTransporte']."'",
				"'".$_POST['ComentariosTransporte']."'",
				"'".$_POST['EstadoTransporte']."'",
				"'".$_POST['Metodo']."'",
				"'".$_SESSION['CodUser']."'",
				"'".$_POST['RegistroCap']."'",
			);
			$SQL=EjecutarSP('sp_tbl_FrmPuerto',$Param);
			if(!$SQL){
				$sw_error=1;
				$msg_error="No se pudo insertar los datos";
			}	
		}
		else{
			$Param=array(
				"'".$_POST['TipoDoc']."'",
				"'".$_POST['Codigo']."'",
				"'".$_POST['ID_Actual']."'",
				"'".$_POST['Nombre']."'",
				"'".$_POST['Comentarios']."'",
				"'".$_POST['Estado']."'",
				"'".$_POST['Metodo']."'",
				"'".$_SESSION['CodUser']."'"
			);
			$SQL=EjecutarSP('sp_tbl_FrmPuerto',$Param);
			if(!$SQL){
				$sw_error=1;
				$msg_error="No se pudo insertar los datos";
			}			
		}
		
		if($sw_error==0){
			header('Location:parametros_frm_personalizados.php?a='.base64_encode("OK_PRUpd").'#'.$_POST['TipoDoc']);
		}
	}catch (Exception $e) {
		$sw_error=1;
		$msg_error=$e->getMessage();
	}	
	
}

$SQL_BodegasPuerto=Seleccionar("tbl_BodegasPuerto","*");

$SQL_Productos=Seleccionar("tbl_ProductosPuerto","*");

$SQL_Transporte=Seleccionar("tbl_TransportesPuerto","*");

$SQL_TipoInfectacion=Seleccionar("tbl_TipoInfectacionProductos","*");

$SQL_GradoInfectacion=Seleccionar("tbl_GradoInfectacion","*");

$SQL_Muelles=Seleccionar("tbl_MuellesPuerto","*");

$SQL_Cliente=Seleccionar('uvw_Sap_tbl_Clientes','CodigoCliente, NombreCliente','','NombreCliente');

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Parámetros de formularios personalizados | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.ibox-title a{
		color: inherit !important;
	}
	.collapse-link:hover{
		cursor: pointer;
	}
	.swal2-container {
	  	z-index: 9000;
	}
	.easy-autocomplete {
		 width: 100% !important
	}
</style>
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_PRUpd"))){
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
                    <h2>Parámetros de formularios personalizados</h2>
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
                            <strong>Parámetros de formularios personalizados</strong>
                        </li>
                    </ol>
                </div>
            </div>
            <?php  //echo $Cons;?>
         <div class="wrapper wrapper-content">
			 <div class="modal inmodal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
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
								<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Monitoreo de temperatura - Puerto</a></li>
							</ul>
							<div class="tab-content">
								<div id="tab-1" class="tab-pane active">
									<form class="form-horizontal">
										<div class="ibox" id="Bodegas">
											<div class="ibox-title bg-success">
												<h5 class="collapse-link"><i class="fa fa-list"></i> Bodegas</h5>
												 <a class="collapse-link pull-right">
													<i class="fa fa-chevron-up"></i>
												</a>	
											</div>
											<div class="ibox-content">
												<div class="row m-b-md">
													<div class="col-lg-12">
														<button class="btn btn-primary pull-right" type="button" id="NewBodega" onClick="CrearCampo('Bodegas');"><i class="fa fa-plus-circle"></i> Agregar nuevo</button> 
													</div>
												</div>
												<div class="table-responsive">
													<table class="table table-striped table-bordered table-hover dataTables-example">
														<thead>
															<tr>
																<th>Código bodega</th>
																<th>Nombre bodega</th>
																<th>Comentarios</th>
																<th>Nombre cliente</th>
																<th>Sucursal cliente</th>
																<th>Estado</th>
																<th>Anexo</th>
																<th>Acciones</th>
															</tr>
														</thead>
														<tbody>
															 <?php  
															while($row_BodegasPuerto=sqlsrv_fetch_array($SQL_BodegasPuerto)){
															?>
															<tr>
																<td><?php echo $row_BodegasPuerto['id_bodega_puerto'];?></td>
																
																<td><?php echo $row_BodegasPuerto['bodega_puerto'];?></td>
																
																<td><?php echo $row_BodegasPuerto['comentarios'];?></td>
																
																<td><?php echo $row_BodegasPuerto['nombre_cliente'];?></td>
																<td><?php echo $row_BodegasPuerto['sucursal_cliente'];?></td>
																<td><?php if($row_BodegasPuerto['estado']=='Y'){echo "ACTIVO";}else{echo "INACTIVO";}?></td>
																
																<td>
																	<?php if($row_BodegasPuerto['anexo']!=""){?>
																		<a href="filedownload.php?file=<?php echo base64_encode($row_BodegasPuerto['anexo']);?>&dir=<?php echo base64_encode($dir_new);?>" target="_blank" title="Descargar archivo" class="btn-link btn-xs"><i class="fa fa-download"></i> <?php echo $row_BodegasPuerto['anexo'];?></a>														
																	<?php }?>
																</td>
																<td>
																	<button type="button" id="btnEdit<?php echo $row_BodegasPuerto['id_bodega_puerto'];?>" class="btn btn-success btn-xs" onClick="EditarCampo('<?php echo $row_BodegasPuerto['id_bodega_puerto'];?>','Bodegas');"><i class="fa fa-pencil"></i> Editar</button>
																</td>
															</tr>
															 <?php }?>
														</tbody>
													</table>
												</div>	
											</div>
										</div>
										<div class="ibox" id="Productos">
											<div class="ibox-title bg-success">
												<h5 class="collapse-link"><i class="fa fa-list"></i> Productos</h5>
												 <a class="collapse-link pull-right">
													<i class="fa fa-chevron-up"></i>
												</a>	
											</div>
											<div class="ibox-content">
												<div class="row m-b-md">
													<div class="col-lg-12">
														<button class="btn btn-primary pull-right" type="button" id="NewBodega" onClick="CrearCampo('Productos');"><i class="fa fa-plus-circle"></i> Agregar nuevo</button> 
													</div>
												</div>
												<div class="table-responsive">
													<table class="table table-striped table-bordered table-hover dataTables-example">
														<thead>
															<tr>
																<th>Código producto</th>
																<th>Nombre producto</th>
																<th>Comentarios</th>
																<th>Estado</th>
																<th>Acciones</th>
															</tr>
														</thead>
														<tbody>
														  <?php  
															while($row_Productos=sqlsrv_fetch_array($SQL_Productos)){
															?>
															<tr>
																 <td><?php echo $row_Productos['id_producto_puerto'];?></td>
																 <td><?php echo $row_Productos['producto_puerto'];?></td>
																 <td><?php echo $row_Productos['comentarios'];?></td>
																 <td><?php if($row_Productos['estado']=='Y'){echo "ACTIVO";}else{echo "INACTIVO";}?></td>
																 <td>
																	<button type="button" id="btnEditProd<?php echo $row_Productos['id_producto_puerto'];?>" class="btn btn-success btn-xs" onClick="EditarCampo('<?php echo $row_Productos['id_producto_puerto'];?>','Productos');"><i class="fa fa-pencil"></i> Editar</button>
																 </td>
															</tr>
														 <?php }?>					
														</tbody>
													</table>
												</div>	
											</div>
										</div>
										<div class="ibox" id="Transportes">
											<div class="ibox-title bg-success">
												<h5 class="collapse-link"><i class="fa fa-list"></i> Motonave</h5>
												 <a class="collapse-link pull-right">
													<i class="fa fa-chevron-up"></i>
												</a>	
											</div>
											<div class="ibox-content">
												<div class="row m-b-md">
													<div class="col-lg-12">
														<button class="btn btn-primary pull-right" type="button" id="NewBodega" onClick="CrearCampo('Transportes');"><i class="fa fa-plus-circle"></i> Agregar nuevo</button> 
													</div>
												</div>
												<div class="table-responsive">
													<table width="100%" class="table table-striped table-bordered table-hover dataTables-example">
														<thead>
															<tr>
																<th>Código motonave</th>
																<th>Nombre motonave</th>
																<th>REG (Registro capitanía)</th>
																<th>Comentarios</th>
																<th>Estado</th>
																<th>Acciones</th>
															</tr>
														</thead>
														<tbody>
														  <?php  
															while($row_Transporte=sqlsrv_fetch_array($SQL_Transporte)){?>
															<tr>
																 <td><?php echo $row_Transporte['id_transporte_puerto'];?></td>
																 <td><?php echo $row_Transporte['transporte_puerto'];?></td>
																 <td><?php echo $row_Transporte['registro_capitania'];?></td>
																 <td><?php echo $row_Transporte['comentarios'];?></td>
																 <td><?php if($row_Transporte['estado']=='Y'){echo "ACTIVO";}else{echo "INACTIVO";}?></td>
																 <td>
																	<button type="button" id="btnEditTrans<?php echo $row_Transporte['id_transporte_puerto'];?>" class="btn btn-success btn-xs" onClick="EditarCampo('<?php echo $row_Transporte['id_transporte_puerto'];?>','Transportes');"><i class="fa fa-pencil"></i> Editar</button>
																 </td>
															</tr>
														 <?php } ?>		
														</tbody>
													</table>
												</div>
											</div>
										</div>
										<div class="ibox" id="TipoInfectacion">
											<div class="ibox-title bg-success">
												<h5 class="collapse-link"><i class="fa fa-list"></i> Tipo infestación productos</h5>
												 <a class="collapse-link pull-right">
													<i class="fa fa-chevron-up"></i>
												</a>	
											</div>
											<div class="ibox-content">
												<div class="row m-b-md">
													<div class="col-lg-12">
														<button class="btn btn-primary pull-right" type="button" id="NewBodega" onClick="CrearCampo('TipoInfectacion');"><i class="fa fa-plus-circle"></i> Agregar nuevo</button> 
													</div>
												</div>
												<div class="table-responsive">
													<table class="table table-striped table-bordered table-hover dataTables-example">
														<thead>
															<tr>
																<th>Código infestación</th>
																<th>Nombre infestación</th>
																<th>Comentarios</th>
																<th>Estado</th>
																<th>Acciones</th>
															</tr>
														</thead>
														<tbody>
														  <?php  
															while($row_TipoInfectacion=sqlsrv_fetch_array($SQL_TipoInfectacion)){
															?>
															<tr>
																 <td><?php echo $row_TipoInfectacion['id_tipo_infectacion_producto'];?></td>
																 <td><?php echo $row_TipoInfectacion['tipo_infectacion_producto'];?></td>
																 <td><?php echo $row_TipoInfectacion['comentarios'];?></td>
																 <td><?php if($row_TipoInfectacion['estado']=='Y'){echo "ACTIVO";}else{echo "INACTIVO";}?></td>
																 <td>
																	<button type="button" class="btn btn-success btn-xs" onClick="EditarCampo('<?php echo $row_TipoInfectacion['id_tipo_infectacion_producto'];?>','TipoInfectacion');"><i class="fa fa-pencil"></i> Editar</button>
																 </td>
															</tr>
														 <?php }?>					
														</tbody>
													</table>
												</div>	
											</div>
										</div>
										<div class="ibox" id="GradoInfectacion">
											<div class="ibox-title bg-success">
												<h5 class="collapse-link"><i class="fa fa-list"></i> Grado infestación productos</h5>
												 <a class="collapse-link pull-right">
													<i class="fa fa-chevron-up"></i>
												</a>	
											</div>
											<div class="ibox-content">
												<div class="row m-b-md">
													<div class="col-lg-12">
														<button class="btn btn-primary pull-right" type="button" id="NewBodega" onClick="CrearCampo('GradoInfectacion');"><i class="fa fa-plus-circle"></i> Agregar nuevo</button> 
													</div>
												</div>
												<div class="table-responsive">
													<table class="table table-striped table-bordered table-hover dataTables-example">
														<thead>
															<tr>
																<th>Código grado infestación</th>
																<th>Nombre grado infestación</th>
																<th>Comentarios</th>
																<th>Estado</th>
																<th>Acciones</th>
															</tr>
														</thead>
														<tbody>
														  <?php  
															while($row_GradoInfectacion=sqlsrv_fetch_array($SQL_GradoInfectacion)){
															?>
															<tr>
																 <td><?php echo $row_GradoInfectacion['id_grado_infectacion'];?></td>
																 <td><?php echo $row_GradoInfectacion['grado_infectacion'];?></td>
																 <td><?php echo $row_GradoInfectacion['comentarios'];?></td>
																 <td><?php if($row_GradoInfectacion['estado']=='Y'){echo "ACTIVO";}else{echo "INACTIVO";}?></td>
																 <td>
																	<button type="button" class="btn btn-success btn-xs" onClick="EditarCampo('<?php echo $row_GradoInfectacion['id_grado_infectacion'];?>','GradoInfectacion');"><i class="fa fa-pencil"></i> Editar</button>
																 </td>
															</tr>
														 <?php }?>					
														</tbody>
													</table>
												</div>	
											</div>
										</div>
										<div class="ibox" id="Muelles">
											<div class="ibox-title bg-success">
												<h5 class="collapse-link"><i class="fa fa-list"></i> Muelles</h5>
												 <a class="collapse-link pull-right">
													<i class="fa fa-chevron-up"></i>
												</a>	
											</div>
											<div class="ibox-content">
												<div class="row m-b-md">
													<div class="col-lg-12">
														<button class="btn btn-primary pull-right" type="button" onClick="CrearCampo('Muelles');"><i class="fa fa-plus-circle"></i> Agregar nuevo</button> 
													</div>
												</div>
												<div class="table-responsive">
													<table class="table table-striped table-bordered table-hover dataTables-example">
														<thead>
															<tr>
																<th>Código muelle</th>
																<th>Nombre muelle</th>
																<th>Comentarios</th>
																<th>Estado</th>
																<th>Acciones</th>
															</tr>
														</thead>
														<tbody>
														  <?php  
															while($row_Muelles=sqlsrv_fetch_array($SQL_Muelles)){
															?>
															<tr>
																 <td><?php echo $row_Muelles['id_muelle_puerto'];?></td>
																 <td><?php echo $row_Muelles['muelle_puerto'];?></td>
																 <td><?php echo $row_Muelles['comentarios'];?></td>
																 <td><?php if($row_Muelles['estado']=='Y'){echo "ACTIVO";}else{echo "INACTIVO";}?></td>
																 <td>
																	<button type="button" class="btn btn-success btn-xs" onClick="EditarCampo('<?php echo $row_Muelles['id_muelle_puerto'];?>','Muelles');"><i class="fa fa-pencil"></i> Editar</button>
																 </td>
															</tr>
														 <?php }?>					
														</tbody>
													</table>
												</div>	
											</div>
										</div>
									</form>	 
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
			$(".select2").select2();
			$('.i-checks').iCheck({
				 checkboxClass: 'icheckbox_square-green',
				 radioClass: 'iradio_square-green',
			  });
			
			$('.dataTables-example').DataTable({
				pageLength: 10,
				dom: '<"html5buttons"B>lTfgitp',
				language: {
					"decimal":        "",
					"emptyTable":     "No se encontraron resultados.",
					"info":           "Mostrando _START_ - _END_ de _TOTAL_ registros",
					"infoEmpty":      "Mostrando 0 - 0 de 0 registros",
					"infoFiltered":   "(filtrando de _MAX_ registros)",
					"infoPostFix":    "",
					"thousands":      ",",
					"lengthMenu":     "Mostrar _MENU_ registros",
					"loadingRecords": "Cargando...",
					"processing":     "Procesando...",
					"search":         "Filtrar:",
					"zeroRecords":    "Ningún registro encontrado",
					"paginate": {
						"first":      "Primero",
						"last":       "Último",
						"next":       "Siguiente",
						"previous":   "Anterior"
					},
					"aria": {
						"sortAscending":  ": Activar para ordenar la columna ascendente",
						"sortDescending": ": Activar para ordenar la columna descendente"
					}
				},
				buttons: []

			});
        });
    </script>
<script>
function CrearCampo(doc){
	$('.ibox-content').toggleClass('sk-loading',true);
	
	$.ajax({
		type: "POST",
		url: "md_frm_param_personalizados.php",
		data:{
			doc:doc
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		}
	});
}	
function EditarCampo(id, doc){
	$('.ibox-content').toggleClass('sk-loading',true);
	
	$.ajax({
		type: "POST",
		url: "md_frm_param_personalizados.php",
		data:{
			doc:doc,
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