<?php require_once("includes/conexion.php");
PermitirAcceso(1002);
$sw=0;//Para saber si ya se selecciono un cliente y mostrar la información

//Filtros
$Filtro="";//Filtro
if(isset($_GET['BuscarDato'])&&$_GET['BuscarDato']!=""){
	$Filtro="Where (CodigoPlantilla LIKE '%".$_GET['BuscarDato']."%' OR Descripcion LIKE '%".$_GET['BuscarDato']."%')";
	
}
$Cons="Select * From uvw_tbl_PlantillaActividades $Filtro";
$SQL=sqlsrv_query($conexion,$Cons);
//echo $Cons;
$sw=1;

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Consultar plantilla actividades | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_ArtAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El articulo ha sido creado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_ArtUpd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El ID de servicio ha sido actualizado exitosamente.',
                icon: 'success'
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
                    <h2>Consultar plantilla actividades</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Datos maestros</a>
                        </li>
                        <li class="active">
                            <strong>Consultar plantilla actividades</strong>
                        </li>
                    </ol>
                </div>
				<div class="col-sm-4">
                    <div class="title-action">
                        <a href="plantilla_actividades.php" class="alkin btn btn-primary"><i class="fa fa-plus-circle"></i> Crear nueva plantilla</a>
                    </div>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					<?php include("includes/spinner.php"); ?>
				  <form action="consultar_plantilla_actividades.php" method="get" id="formBuscar" class="form-horizontal">
					  	<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Buscar</label>
							<div class="col-lg-4">
								<input name="BuscarDato" type="text" class="form-control" id="BuscarDato" maxlength="100" placeholder="Consulte por el código o la descripción..." value="<?php if(isset($_GET['BuscarDato'])&&($_GET['BuscarDato']!="")){ echo $_GET['BuscarDato'];}?>">
							</div>
							<div class="col-lg-1">
								<button type="submit" class="btn btn-outline btn-info"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
				 </form>
			</div>
			</div>
		  </div>
         <br>
			 <?php //echo $Cons;?>
		<?php if($sw==1){?>
          <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					<?php include("includes/spinner.php"); ?>
			<div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example" >
                    <thead>
                    <tr>
						<th>#</th>
						<th>Código de plantilla</th>
						<th>Descripción</th>
						<th>Cantidad detalles</th>
						<th>Fecha creación</th>
						<th>Usuario creación</th>
						<th>Fecha últ. actualización</th>
						<th>Usuario actualización</th>
						<th>Acciones</th>
					</tr>
                    </thead>
                    <tbody>
                    <?php $i=1; 
						 while($row=sqlsrv_fetch_array($SQL)){ ?>
						 <tr class="gradeX">
								<td><?php echo $i;?></td>
								<td><?php echo $row['CodigoPlantilla'];?></td>						
								<td><?php echo $row['Descripcion'];?></td>
								<td><?php echo $row['CantDetalles'];?></td>
								<td><?php echo ($row['FechaCreacion']!="") ? $row['FechaCreacion']->format('Y-m-d H:i') : "";?></td>
								<td><?php echo $row['NombreUsuarioCreacion'];?></td>
								<td><?php echo ($row['FechaActualizacion']!="") ? $row['FechaActualizacion']->format('Y-m-d H:i') : "";?></td>
								<td><?php echo $row['NombreUsuarioActualizacion'];?></td>
								<td><a href="plantilla_actividades.php?id=<?php echo base64_encode($row['CodigoPlantilla']);?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('consultar_plantilla_actividades.php');?>" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
							</tr>
					<?php $i++;}?>
                    </tbody>
                    </table>
              </div>
			</div>
			 </div> 
          </div>
		<?php }?>
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
 <script>
        $(document).ready(function(){
			 $("#formBuscar").validate({
				 submitHandler: function(form){
					 $('.ibox-content').toggleClass('sk-loading');
					 form.submit();
				}
			});
			$(".btn-link").on('click', function(){
				$('.ibox-content').toggleClass('sk-loading');
			});
            $('.dataTables-example').DataTable({
                pageLength: 25,
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
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>