<?php require_once("includes/conexion.php");
PermitirAcceso(220);
$sw_ext=0;
$msg_error="";//Mensaje del error
$CodigoPlantilla=0;

if(isset($_REQUEST['ext'])&&($_REQUEST['ext']==1)){
	$sw_ext=1;//Se está abriendo como pop-up
}

if(isset($_GET['id'])&&($_GET['id']!="")){//ID de la plantilla (CodigoPlantilla)
	$CodigoPlantilla=base64_decode($_GET['id']);
}

if(isset($_POST['codPlantilla'])&&($_POST['codPlantilla']!="")){//Cuando el cdigo de la plantilla viene del modal
	$CodigoPlantilla=$_POST['codPlantilla'];
}

if(isset($_POST['swError'])&&($_POST['swError']!="")){//Para saber si ha ocurrido un error.
	$sw_error=$_POST['swError'];
}else{
	$sw_error=0;
}

if(isset($_REQUEST['tl'])&&($_REQUEST['tl']!="")){//0 Si se está creando. 1 Se se está editando.
	$edit=$_REQUEST['tl'];
}else{
	$edit=0;
}

if(isset($_POST['P'])&&($_POST['P']!="")){//Grabar cabecera
	
	try{
		$Type=($_POST['tl']==1) ? 2 : 1;
		
		$ParamCab=array(
			"'".base64_decode($_POST['ID'])."'",
			"'".$_POST['CodigoPlantilla']."'",
			"'".$_POST['Descripcion']."'",
			"'".$_SESSION['CodUser']."'",
			$Type
		);
		$SQL_Cab=EjecutarSP('sp_tbl_PlantillaActividades',$ParamCab,$_POST['P']);
		if($SQL_Cab){
			
			$Msg=($_POST['tl']==1) ? "OK_PlUpd" : "OK_PlAdd";
			
			header('Location:plantilla_actividades.php?id='.base64_encode($_POST['CodigoPlantilla']).'&tl=1&a='.base64_encode($Msg).(($sw_ext==1) ? "&ext=1" : ""));			
		}else{
			$sw_error=1;
			$msg_error="Ha ocurrido un error al insertar la plantilla";
		}
	}catch (Exception $e){
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
	
}

if(isset($_POST['MM_Insert'])&&($_POST['MM_Insert']!="")){//Grabar detalle
	
	try{
		
		if($_POST['type']=='1'){//Creando nuevo registro
			$ParamDet=array(
				"''",
				"'".$_POST['codPlantilla']."'",
				"'".$_SESSION['CodUser']."'",
				"'".$_POST['type']."'"
			);
			$SQL_Det=EjecutarSP('sp_tbl_PlantillaActividades_Detalle',$ParamDet);
			if($SQL_Det){
				$row_Cab=sqlsrv_fetch_array($SQL_Det);
				$idDetalle=$row_Cab[0];
			}
		}else{
			$idDetalle=$_POST['idDetalle'];
		}
		
		$SQL_Campos=Seleccionar("tbl_Parametros_Asistentes","*","TipoObjeto=66","LabelCampo");
		while($row_Campos=sqlsrv_fetch_array($SQL_Campos)){
			if(isset($_POST[$row_Campos['ID_Campo']])){
				$ParamData=array(
					"'".$_POST['codPlantilla']."'",
					"'".$idDetalle."'",
					"'".$row_Campos['ID_Campo']."'",
					"'".$_POST[$row_Campos['ID_Campo']]."'",
					"'".$_SESSION['CodUser']."'",
					"'".$_POST['type']."'"
				);
				$SQL_Data=EjecutarSP('sp_tbl_PlantillaActividades_Datos',$ParamData);
				if(!$SQL_Data){	
					$sw_error=1;
					$msg_error="Ha ocurrido un error al insertar los datos del detalle";		
				}
			}
		}		
		
		if($sw_error==0){
			
			$Msg=($_POST['tl']==1) ? "OK_PlUpd" : "OK_PlAdd";
			header('Location:plantilla_actividades.php?id='.base64_encode($_POST['codPlantilla']).'&tl=1&a='.base64_encode($Msg).(($sw_ext==1) ? "&ext=1" : ""));
			
		}
	}catch (Exception $e){
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
	
}

if($edit==1&&$sw_error==0){
	
	//Cabecera
	$SQL=Seleccionar("uvw_tbl_PlantillaActividades",'*',"CodigoPlantilla='".$CodigoPlantilla."'");
	$row=sqlsrv_fetch_array($SQL);
	
	//Detalle
	$SQL_Detalle=Seleccionar("uvw_tbl_PlantillaActividades_Detalle",'*',"CodigoPlantilla='".$CodigoPlantilla."'");
	

}

if($sw_error==1){
	
	//Cabecera
	$SQL=Seleccionar("uvw_tbl_PlantillaActividades",'*',"CodigoPlantilla='".$CodigoPlantilla."'");
	$row=sqlsrv_fetch_array($SQL);
	
	//Detalle
	$SQL_Detalle=Seleccionar("uvw_tbl_PlantillaActividades_Detalle",'*',"CodigoPlantilla='".$CodigoPlantilla."'");

}

$SQL_Campos=Seleccionar("tbl_Parametros_Asistentes","*","TipoObjeto=66","LabelCampo");

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Plantilla de actividades | <?php echo NOMBRE_PORTAL;?></title>
<?php 
if(isset($_GET['a'])&&$_GET['a']==base64_encode("OK_PlAdd")){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'La Plantilla de actividades ha sido creada exitosamente.',
				icon: 'success'
			});
		});	
		</script>";
}
if(isset($_GET['a'])&&$_GET['a']==base64_encode("OK_PlUpd")){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'La Plantilla de actividades ha sido actualizada exitosamente.',
				icon: 'success'
			});
		});
		</script>";
}
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Advertencia!',
                text: `".LSiqmlObs($msg_error)."`,
                icon: 'warning'
            });
		});
		</script>";
}
?>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.swal2-container {
	  	z-index: 9000;
	}
	.easy-autocomplete {
		 width: 100% !important
	}
</style>
<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#NombreCliente").change(function(){
			var NomCliente=document.getElementById("NombreCliente");
			var Cliente=document.getElementById("Cliente");
			if(NomCliente.value==""){
				Cliente.value="";
				$("#Cliente").trigger("change");
			}	
		});
		$("#Cliente").change(function(){
			var Cliente=document.getElementById("Cliente");
			$.ajax({
				type: "POST",
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Cliente.value+"&sucline=1&selec=1&todos=0",
				success: function(response){
					$('#Sucursal').html(response);
					$("#Sucursal").trigger("change");
				}
			});	
		});
		
	});
</script>
<!-- InstanceEndEditable -->
</head>

<body <?php if($sw_ext==1){echo "class='mini-navbar'"; }?>>

<div id="wrapper">

	<?php if($sw_ext!=1){include("includes/menu.php"); }?>

    <div id="page-wrapper" class="gray-bg">
		<?php if($sw_ext!=1){include("includes/menu_superior.php"); }?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Plantilla de actividades</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Datos maestros</a>
                        </li>
                        <li class="active">
                            <strong>Plantilla de actividades</strong>
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
		 <div class="ibox-content">
			 <?php include("includes/spinner.php"); ?>
          <div class="row"> 
           <div class="col-lg-12">
              <form action="plantilla_actividades.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="frmPlantilla">
				<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Información de la plantilla</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Código <span class="text-danger">*</span></label>
					<div class="col-lg-3">
                    	<input type="text" name="CodigoPlantilla" id="CodigoPlantilla" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['CodigoPlantilla'];}?>" <?php if($edit==1){echo "readonly";}?> required>
               	  	</div>
					<label class="col-lg-1 control-label">Descripción <span class="text-danger">*</span></label>
					<div class="col-lg-3">
                    	<input type="text" name="Descripcion" id="Descripcion" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['Descripcion'];}?>" required>
               	  	</div>	
					<div class="col-lg-3">
						<?php 
//							
							if(isset($_GET['return'])){
								$return=base64_decode($_GET['pag'])."?".base64_decode($_GET['return']);
							}elseif(isset($_POST['return'])){
								$return=base64_decode($_POST['return']);
							}else{
								$return="consultar_plantilla_actividades.php";
							}
							$return=QuitarParametrosURL($return,array("a"));
						?>
                    	<?php if($edit==0){?>
							<button class="btn btn-primary" type="submit" form="frmPlantilla" id="Crear"><i class="fa fa-check"></i> Crear plantilla</button>
						<?php }else{?>
							<button class="btn btn-warning" type="submit" form="frmPlantilla" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar información</button>
						<?php }?>
						<?php if($sw_ext==0){?>
							<a href="<?php echo $return;?>" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
						<?php }?>
               	  	</div>	
				</div>
				<?php if($edit==1){?>
				<div class="form-group">
					<label class="col-lg-1 control-label">Fecha últ. actualización</label>
					<div class="col-lg-3">
                    	<input type="text" name="FechaAct" id="FechaAct" class="form-control" value="<?php if($edit==1||$sw_error==1){echo ($row['FechaActualizacion']!="") ? $row['FechaActualizacion']->format('Y-m-d H:i') : "&nbsp;";}?>" readonly>
               	  	</div>
					<label class="col-lg-1 control-label">Usuario actualización</label>
					<div class="col-lg-3">
                    	<input type="text" name="UsuarioAct" id="UsuarioAct" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['NombreUsuarioActualizacion'];}?>" readonly>
               	  	</div>					
				</div>
				<?php }?>
				<?php if($edit==1){?>
				<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Contenido de la plantilla</h3></label>
				</div>				
				<div class="form-group">
					<div class="row m-b-md">
						<div class="col-lg-12">
							<button class="btn btn-primary pull-right" type="button" id="NewBodega" onClick="CrearCampo('<?php echo $row['CodigoPlantilla'];?>');"><i class="fa fa-plus-circle"></i> Crear nuevo registro</button> 
						</div>
					</div>
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover dataTables-example" >
							<thead>
							<tr>
								<th>#</th>
							<?php while($row_Campos=sqlsrv_fetch_array($SQL_Campos)){ ?>
								<th><?php echo $row_Campos['LabelCampo'];?></th>
							<?php }?>
								<th>Fecha actualización</th>
								<th>Usuario actualización</th>
								<th>Acciones</th>
							</tr>
							</thead>
							<tbody>
							<?php $i=1;
								while($row_Detalle=sqlsrv_fetch_array($SQL_Detalle)){
									$Param=array(
										"'".$CodigoPlantilla."'",
										"'".$row_Detalle['IdDetalle']."'",
										"''",
										"''",
										"''",
										"'4'"
									);
									$SQL_Data=EjecutarSP('sp_tbl_PlantillaActividades_Datos',$Param,-1);
								?>
									<tr class="gradeX">
										<td><?php echo $i;?></td>
									<?php while($row_Data=sqlsrv_fetch_array($SQL_Data)){ ?>
										<td><?php echo $row_Data['Valor'];?></td>
									<?php }?>
										<td><?php echo ($row_Detalle['FechaActualizacion']!="") ? $row_Detalle['FechaActualizacion']->format('Y-m-d H:i') : "";?></td>
										<td><?php echo $row_Detalle['NombreUsuarioActualizacion'];?></td>
										<td>
											<button type="button" id="btnEdit<?php echo $row_Detalle['IdDetalle'];?>" class="btn btn-success btn-xs" onClick="EditarCampo('<?php echo $row_Detalle['IdDetalle'];?>','<?php echo $row_Detalle['CodigoPlantilla'];?>');"><i class="fa fa-pencil"></i> Editar</button>
											<button type="button" id="btnDel<?php echo $row_Detalle['IdDetalle'];?>" class="btn btn-danger btn-xs" onClick="BorrarLinea('<?php echo $row_Detalle['IdDetalle'];?>','<?php echo $row_Detalle['CodigoPlantilla'];?>');"><i class="fa fa-trash"></i> Eliminar</button>
										</td>
									</tr>
							<?php $i++;}?>
							</tbody>
						</table>
				  </div>
				</div>
				<?php }?>
				<input type="hidden" id="P" name="P" value="66" />
				<input type="hidden" id="ID" name="ID" value="<?php echo ($edit==1) ? base64_encode($row['ID']) : "";?>" />
				<input type="hidden" id="tl" name="tl" value="<?php echo $edit;?>" />
				<input type="hidden" id="swError" name="swError" value="<?php echo $sw_error;?>" />
				<input type="hidden" id="ext" name="ext" value="<?php echo $sw_ext;?>" />
			 </form>
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
		 $("#frmPlantilla").validate({
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
		 
		 $(".alkin").on('click', function(){
				 $('.ibox-content').toggleClass('sk-loading');
			});


		 $(".select2").select2();
		 
		 var options = {
			  url: function(phrase) {
				  return "ajx_buscar_datos_json.php?type=7&id="+phrase;
			  },
			  getValue: "NombreBuscarCliente",
			  requestDelay: 400,
			  list: {
				  match: {
					  enabled: true
				  },
				  onClickEvent: function() {
					  var value = $("#NombreCliente").getSelectedItemData().CodigoCliente;
					  $("#Cliente").val(value).trigger("change");
				  }
			  }
		 };
		$("#NombreCliente").easyAutocomplete(options);
		 
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
function CrearCampo(codPlantilla){
	$('.ibox-content').toggleClass('sk-loading',true);
	
	$.ajax({
		type: "POST",
		url: "md_crear_plantilla_actividades_detalle.php",
		data:{
			codPlant:codPlantilla,
			ext: '<?php echo $sw_ext;?>'
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		}
	});
}	
function EditarCampo(id, codPlantilla){
	$('.ibox-content').toggleClass('sk-loading',true);
	
	$.ajax({
		type: "POST",
		url: "md_crear_plantilla_actividades_detalle.php",
		data:{
			idDetalle:id,
			edit:1,
			codPlant:codPlantilla,
			ext: '<?php echo $sw_ext;?>'
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		}
	});
}	
function BorrarLinea(id, codPlantilla){
	Swal.fire({
		title: "¿Está seguro que desea eliminar este registro?",
		text: "Este proceso no se puede revertir",
		icon: "warning",
		showCancelButton: true,
		confirmButtonText: "Si, confirmo",
		cancelButtonText: "No"
	}).then((result) => {
		if (result.isConfirmed) {
			$.ajax({
				type: "GET",
				url: "includes/procedimientos.php?type=47&idDetalle="+id+"&codPlant="+codPlantilla,		
				success: function(response){
					$("#btnDel"+id).parents("tr").remove();
					Swal.fire({
						title: '¡Listo!',
						text: 'Se ha eliminado exitosamente.',
						icon: 'success'
					});
				}
			});
		}
	});
}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd -->
</html>
<?php sqlsrv_close($conexion);?>