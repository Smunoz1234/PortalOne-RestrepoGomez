<?php 
require_once("includes/conexion.php");
PermitirAcceso(219);

$sw_error=0;
$msg_error="";

$dirRuta=CrearObtenerDirRuta(ObtenerVariable("RutaFormatosImpresion"));
//Crear nuevo parametro
if(isset($_POST['MM_Insert'])&&($_POST['MM_Insert']!="")){
	
	if($_FILES['FileNombreArchivo']['tmp_name']!=""){
		if(is_uploaded_file($_FILES['FileNombreArchivo']['tmp_name'])){
			$Nombre_Archivo=$_FILES['FileNombreArchivo']['name'];
			$NuevoNombre=FormatoNombreAnexo($Nombre_Archivo,false);
			if(!move_uploaded_file($_FILES['FileNombreArchivo']['tmp_name'],$dirRuta.$NuevoNombre[0])){
				$sw_error=1;
				$msg_error="No se pudo mover el anexo a la carpeta de anexos local";
			}
		}else{
			$sw_error=1;
			$msg_error="No se pudo cargar el anexo";
		}
	}else{
		$NuevoNombre[1]=$_POST['NombreArchivo'];
	}
	
	if($_POST['TipoDoc']=="OTRO"){
		$IdObjeto=$_POST['IDDocumento'];
		$DeObjeto=$_POST['NombreDocumento'];
		$IdFormato=$_POST['IDFormato'];
	}else{
		$Obj=explode("__",$_POST['TipoDoc']);
		$IdObjeto=$Obj[0];
		$DeObjeto=$Obj[1];
		$IdFormato=($_POST['SerieDoc']=="OTRO") ? $_POST['IDFormato'] : $_POST['SerieDoc'];
	}
	
	$Param=array(
		"'".$_POST['id']."'",
		"'".$IdObjeto."'",
		"'".$DeObjeto."'",
		"'".$IdFormato."'",
		"'".$NuevoNombre[1]."'",
		"'".$_POST['NombreVisualizar']."'",
		"'".$_POST['VerDocumento']."'",
		"'".$_POST['Comentarios']."'",
		"'".$_SESSION['CodUser']."'",
		"'".$_POST['type']."'"
	);
	$SQL=EjecutarSP('sp_tbl_FormatosSAP',$Param);
	if($SQL){
		$a=($_POST['type']==1) ? "OK_NewParam" : "OK_UpdParam";
		header('Location:parametros_formatos_impresion.php?a='.base64_encode($a));
	}else{
		$sw_error=1;
		$msg_error="No se pudo insertar el nuevo registro";
	}
}

$SQL=Seleccionar("uvw_tbl_FormatosSAP","*","","DE_Objeto");

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Parámetros formatos de impresión | <?php echo NOMBRE_PORTAL;?></title>
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
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_NewParam"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El nuevo registro ha sido agregado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_UpdParam"))){
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
                    <h2>Parámetros formatos de impresión</h2>
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
                            <strong>Parámetros formatos de impresión</strong>
                        </li>
                    </ol>
                </div>
            </div>
            <?php  //echo $Cons;?>
         <div class="wrapper wrapper-content">
			 <div class="modal inmodal fade" id="myModal" tabindex="1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content" id="ContenidoModal">
						
					</div>
				</div>
			 </div>
			 <form action="" method="post" id="frmParam" class="form-horizontal">			 
			 <div class="row">
				<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-plus-square"></i> Acciones</h3></label>
						</div>
						<div class="form-group">
							<div class="col-lg-6">
								<button class="btn btn-primary" type="button" id="NewParam" onClick="CrearCampo();"><i class="fa fa-plus-circle"></i> Crear nuevo registro</button>  
							</div>
						</div>
					  	<input type="hidden" id="P" name="P" value="frmParam" />
					</div>
				</div>
			 </div>
			 <br>
			 <div class="row">
			 	<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-check-square-o"></i> Lista de formatos</h3></label>
						</div>
						<div class="table-responsive">
							<table class="table table-bordered table-hover dataTables-example" >
							<thead>
							<tr>
								<th>#</th>
								<th>ID tipo documento</th>
								<th>Tipo documento</th>
								<th>ID formato</th>
								<th>Serie documento</th>
								<th>Nombre archivo</th>
								<th>Nombre a mostrar</th>
								<th>Ver en documento</th>
								<th>Comentarios</th>
								<th>Fecha actualización</th>
								<th>Usuario actualización</th>
								<th>Acciones</th>
							</tr>
							</thead>
							<tbody>
							<?php $i=1;
								while($row=sqlsrv_fetch_array($SQL)){ ?>
									<tr class="gradeX">
										<td><?php echo $i;?></td>
										<td><?php echo $row['ID_Objeto'];?></td>						
										<td><?php echo $row['DE_Objeto'];?></td>
										<td><?php echo $row['IdFormato'];?></td>										
										<td><?php echo $row['DeSeries'];?></td>						
										<td><?php echo $row['DeFormato'];?></td>
										<td><?php echo $row['NombreVisualizar'];?></td>
										<td><?php echo ($row['VerEnDocumento']=='N') ? "NO" : "SI";?></td>
										<td><?php echo $row['Comentarios'];?></td>
										<td><?php echo ($row['FechaActualizacion']!="") ? $row['FechaActualizacion']->format('Y-m-d H:i') : "";?></td>
										<td><?php echo $row['NombreUsuarioActualizacion'];?></td>
										<td>
											<button type="button" id="btnEdit<?php echo $row['ID'];?>" class="btn btn-success btn-xs" onClick="EditarCampo('<?php echo $row['ID'];?>');"><i class="fa fa-pencil"></i> Editar</button>
											<button type="button" id="btnDel<?php echo $row['ID'];?>" class="btn btn-danger btn-xs" onClick="BorrarLinea('<?php echo $row['ID'];?>');"><i class="fa fa-trash"></i> Eliminar</button>
										</td>
									</tr>
							<?php $i++;}?>
							</tbody>
							</table>
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
		$("#frmParam").validate({
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

		$(".select2").select2();
		$('.i-checks').iCheck({
			 checkboxClass: 'icheckbox_square-green',
			 radioClass: 'iradio_square-green',
		  });
		
		<?php if(isset($_GET['doc'])){?>
		$("#TipoDocumento").trigger('change');
		<?php }?>
		
		 $('.dataTables-example').DataTable({
			pageLength: 25,
			dom: '<"html5buttons"B>lTfgitp',
			rowGroup: {
				dataSrc: [2]
			},
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
function CrearCampo(){
	$('.ibox-content').toggleClass('sk-loading',true);
	
	$.ajax({
		type: "POST",
		url: "md_crear_formatos_impresion.php",
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		}
	});
}	
function EditarCampo(id){
	$('.ibox-content').toggleClass('sk-loading',true);
	
	$.ajax({
		type: "POST",
		url: "md_crear_formatos_impresion.php",
		data:{
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
function BorrarLinea(id){
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
				url: "includes/procedimientos.php?type=46&linenum="+id,		
				success: function(response){
					$("#btnDel"+id).parents("tr").remove();
				}
			});
		}
	});
}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>