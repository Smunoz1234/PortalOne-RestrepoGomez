<?php require("includes/conexion.php");
PermitirAcceso(204);

$Cons="Select * From uvw_tbl_Productos_Cargue";
$SQL=sqlsrv_query($conexion,$Cons,array(),array( "Scrollable" => 'static' ));
$Num=sqlsrv_num_rows($SQL);
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo NOMBRE_PORTAL;?> | Cargar productos masivos</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Listo!',
                text: 'Se movieron: ".base64_decode($_GET['a'])." archivo(s).',
                type: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['b'])){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Listo!',
                text: 'Se cargaron ".base64_decode($_GET['b'])." registros.',
                type: 'success'
            });
		});		
		</script>";
}
?>
<script>
		function EliminarRegistro(id){
			if(id!=""){
				swal({
					title: "¿Estás seguro?",
					text: "Se eliminará la información de este registro. Este proceso no tiene reversión.",
					type: "warning",
					showCancelButton: true,
					confirmButtonText: "Si, estoy seguro",
					cancelButtonText: "Cancelar",
					closeOnConfirm: false
				},
				function(){
					location.href='registro.php?P=25&type=1&id='+id;
				});
			}
		}
		function EliminarDatos(){
				swal({
					title: "¿Estás seguro?",
					text: "Se eliminará toda la información para cargar. Este proceso no tiene reversión.",
					type: "warning",
					showCancelButton: true,
					confirmButtonText: "Si, estoy seguro",
					cancelButtonText: "Cancelar",
					closeOnConfirm: false
				},
				function(){
					location.href='registro.php?P=25&type=2';
				});			
		}
</script>
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Cargar productos masivos</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Administraci&oacute;n</a>
                        </li>
                        <li class="active">
                            <strong>Cargar productos masivos</strong>
                        </li>
                    </ol>
                </div>
                <div class="col-sm-4">
                    <div class="title-action">
                        <a href="cargar_archivo_csv_prod.php" class="btn btn-primary"><i class="fa fa-upload"></i> Cargar archivo .csv</a>
                    </div>
                </div>
               <?php  //echo $Cons;?>
            </div>
         <div class="wrapper wrapper-content">
         <?php if($Num>=1){?>
			<div class="row">
				<div class="col-lg-12">
					<div class="ibox-content">
						<form action="registro.php" method="post" id="frmMover" class="form-horizontal">
							<div class="form-group">
								<div class="col-lg-2">
									<button type="submit" name="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> Mover archivos</button>
								</div>
								<div class="col-lg-8">
									<a href="cargue_masivo_productos.php" class="btn btn-warning pull-right"><i class="fa fa-refresh"></i> Actualizar</a>
								</div>
								<div class="col-lg-2">
									<a href="#" onClick="EliminarDatos();" class="btn btn-danger pull-right"><i class="fa fa-trash-o"></i> Borrar datos</a>
								</div>
								<input type="hidden" name="P" id="P" value="24">
							</div>
						</form>
					</div>
				</div>
			</div>
          <br>
          <?php }?>
          <div class="row">
           <div class="col-lg-12">  
			    <div class="ibox-content">
			<div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example" >
                    <thead>
                    <tr>
                    	<th>Código</th>
						<th>Nombre</th>
						<th>Categoría</th>
						<th>Fecha archivo</th>						
						<th>Comentarios</th>
						<th>Nombre del archivo</th>
						<th>Estado</th>
						<th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while($row=sqlsrv_fetch_array($SQL)){ 
			$Msg=ValidarEstadoProductosCargue($row['ItemName'],$row['NombreCategoriaProductos'],utf8_decode($row['Archivo']));
						?>
						 <tr class="gradeX">
						 	<td><?php echo $row['ItemCode'];?></td>
						 	<td><?php echo $row['ItemName'];?></td>
						 	<td><?php echo $row['NombreCategoriaProductos'];?></td>
						 	<td><?php if($row['Fecha']!=""){ echo $row['Fecha']->format('Y-m-d');}else{?><p class="text-muted">--</p><?php }?></td>
						 	<td><?php echo $row['Comentarios'];?></td>
							<td><?php echo $row['Archivo'];?></td>
							<td><?php if($Msg[0][0]>=1){echo "<span class='badge badge-danger' title='".$Msg[0][1]."'>Error <i class='fa fa-eye'></i></span>";}else{echo "<span class='badge badge-primary'>Correcto</span>";}?></td>
							<td><?php if($row['Archivo']!=""){?><a href="#" onClick="EliminarRegistro(<?php echo $row['ID_Producto'];?>);" class="btn btn-link btn-xs"><i class="fa fa-eraser"></i> Eliminar</a><?php }else{?><p class="text-muted">Ninguno</p><?php }?></td>
						</tr>
					<?php }?>
                    </tbody>
                    </table>
              </div>
			   </div> 
			</div>
          </div>
        </div>
        <!-- InstanceEndEditable -->
        <?php include("includes/footer.php"); ?>

    </div>
</div>
<?php include("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
 <script>
        $(document).ready(function(){
			 $('#FechaInicial').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'dd/mm/yyyy'
            });
			 $('#FechaFinal').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'dd/mm/yyyy'
            }); 
			
			$('.chosen-select').chosen({width: "100%"});
			
            $('.dataTables-example').DataTable({
                pageLength: 25,
                responsive: true,
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