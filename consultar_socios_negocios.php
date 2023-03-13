<?php require_once("includes/conexion.php");
PermitirAcceso(502);
$sw=0;//Para saber si ya se selecciono un cliente y mostrar la información

//Filtros
$Filtro="";//Filtro
if(isset($_GET['BuscarDato'])&&$_GET['BuscarDato']!=""){
	$Filtro="Where (CodigoCliente LIKE '%".$_GET['BuscarDato']."%' OR LicTradNum LIKE '%".$_GET['BuscarDato']."%' OR NombreCliente LIKE '%".$_GET['BuscarDato']."%' OR AliasCliente LIKE '%".$_GET['BuscarDato']."%' OR PersonaContacto LIKE '%".$_GET['BuscarDato']."%' OR Telefono LIKE '%".$_GET['BuscarDato']."%' OR Celular LIKE '%".$_GET['BuscarDato']."%' OR Email LIKE '%".$_GET['BuscarDato']."%')";
	
	$Cons="Select * From uvw_Sap_tbl_SociosNegocios $Filtro";
	$SQL=sqlsrv_query($conexion,$Cons);
	$sw=1;
}


?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo NOMBRE_PORTAL;?> | Consultar socios de negocios</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_LlamAdd"))){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Listo!',
                text: 'La llamada de servicio ha sido creada exitosamente.',
                type: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_UpdAdd"))){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Listo!',
                text: 'La llamada de servicio ha sido actualizada exitosamente.',
                type: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_ClosLlam"))){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Listo!',
                text: 'La llamada de servicio ha sido actualizada exitosamente.',
                type: 'success'
            });
		});		
		</script>";
}
?>
<script>
function CrearLead(){
	self.name='opener';
	var altura=720;
	var anchura=1240;
	var posicion_y=parseInt((window.screen.height/2)-(altura/2));
	var posicion_x=parseInt((window.screen.width/2)-(anchura/2));
	remote=open('popup_crear_lead.php','remote','width='+anchura+',height='+altura+',location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=no,status=yes,left='+posicion_x+',top='+posicion_y);
	remote.focus();
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
                    <h2>Consultar socios de negocios</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Socios de negocios</a>
                        </li>
                        <li class="active">
                            <strong>Consultar socios de negocios</strong>
                        </li>
                    </ol>
                </div>
				<div class="col-sm-4">
                    <div class="title-action">
					<?php if(PermitirFuncion(508)) {?>
                       <button type="button" onClick="CrearLead();" class="btn btn-primary"><i class="fa fa-user-circle"></i> Crear Prospecto</button>
					<?php }?>
                    </div>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
					  <form action="consultar_socios_negocios.php" method="get" id="formBuscar" class="form-horizontal">
							<div class="form-group">
								<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
							</div>
							<div class="form-group">
								<label class="col-lg-1 control-label">Buscar</label>
								<div class="col-lg-4">
									<input name="BuscarDato" type="text" class="form-control" id="BuscarDato" maxlength="100" placeholder="Consulte el ID o el nombre del cliente" value="<?php if(isset($_GET['BuscarDato'])&&($_GET['BuscarDato']!="")){ echo $_GET['BuscarDato'];}?>">
								</div>
								<div class="col-lg-1">
									<button type="submit" class="btn btn-outline btn-success"><i class="fa fa-search"></i> Buscar</button>
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
                        <th>Código</th>
						<th>Nombre cliente</th>
						<th>NIT o Cédula</th>
						<th>Tipo SN</th>
						<th>Grupo cliente</th>
						<th>Contacto</th>
                        <th>Teléfono</th>
						<th>Celular</th>
						<th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while($row=sqlsrv_fetch_array($SQL)){ ?>
						 <tr class="gradeX">
							<td><?php echo $row['CodigoCliente'];?></td>
							<td><?php echo $row['NombreCliente'];?></td>
							<td><?php echo $row['LicTradNum'];?></td>
							<td><?php echo $row['TipoSN'];?></td>
							<td><?php echo $row['GrupoNombre'];?></td>
							<td><?php echo $row['PersonaContacto'];?></td>
							<td><?php echo $row['Telefono'];?></td>
							<td><?php echo $row['Celular'];?></td>
							<td><a href="socios_negocios.php?id=<?php echo base64_encode($row['CodigoCliente']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('consultar_socios_negocios.php');?>&tl=1" class="btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
						</tr>
					<?php }?>
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