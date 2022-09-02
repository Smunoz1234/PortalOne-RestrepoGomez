<?php require_once("includes/conexion.php");
PermitirAcceso(202);
$sw=0;//Verificar que hay datos
$And=0;//Agregar mas filtros a la busqueda
$Filtro="";
if(isset($_GET['Buscar'])){
	$sw=1;
}
if(isset($_GET['Buscar'])&&$_GET['Buscar']!=""){
	$Filtro="Where (Usuario LIKE '%".$_GET['Buscar']."%' Or NombreUsuario LIKE '%".$_GET['Buscar']."%' OR CodigoSAP LIKE '%".$_GET['Buscar']."%')";
	$And=1;
	$sw=1;
}
if(isset($_GET['BuscarPerfil'])&&$_GET['BuscarPerfil']!=""){
	if($And==0){
		$Filtro=" Where ID_PerfilUsuario='".$_GET['BuscarPerfil']."'";
		$And=1;
	}else{
		$Filtro.=" And ID_PerfilUsuario='".$_GET['BuscarPerfil']."'";
	}
	$sw=1;
}
if($sw==1){
	$Cons="Select * From uvw_tbl_Usuarios $Filtro";
	$SQL=sqlsrv_query($conexion,$Cons);
}

$SQL_Perfiles=Seleccionar('uvw_tbl_PerfilesUsuarios','*','','PerfilUsuario');
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Gestionar usuarios | <?php echo NOMBRE_PORTAL;?></title>
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
?>
<script>
function Activar_Inactivar(ID){
	$.ajax({
		type: "GET",
		url: "includes/procedimientos.php?type=2&ID_Usuario="+ID,
		success: function(response){
			if(response==1){//Lo activo
				document.getElementById('LinkActive'+ID).setAttribute('title','Inactivar');
				document.getElementById('btnActive'+ID).setAttribute('class','btn btn-danger btn-circle');
				document.getElementById('spanActive'+ID).setAttribute('class','glyphicon glyphicon-ban-circle');
				document.getElementById('rowAct'+ID).innerHTML="<span class='badge badge-primary'>Activo</span>";
			}
			if(response==2){//Lo desactivo
				document.getElementById('LinkActive'+ID).setAttribute('title','Activar');
				document.getElementById('btnActive'+ID).setAttribute('class','btn btn-success btn-circle');
				document.getElementById('spanActive'+ID).setAttribute('class','glyphicon glyphicon-ok-circle');
				document.getElementById('rowAct'+ID).innerHTML="<span class='badge badge-danger'>Inactivo</span>";
			}
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
                    <h2>Gestionar usuarios</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Administraci&oacute;n</a>
                        </li>
                        <li class="active">
                            <strong>Gestionar usuarios</strong>
                        </li>
                    </ol>
                </div>
                 <div class="col-sm-4">
                    <div class="title-action">
                        <a href="usuarios.php" class="btn btn-primary"><i class="fa fa-plus-circle"></i> Agregar usuario</a>
                    </div>
                </div>
            </div>           
        <div class="wrapper wrapper-content">
		<div class="row">
			<div class="col-lg-12">
				<div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
					<form action="gestionar_usuarios.php" method="get" id="formBuscar" class="form-horizontal">
						 <div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						  </div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Perfil</label>
							<div class="col-lg-2">
								<select name="BuscarPerfil" class="form-control m-b" id="BuscarPerfil">
									<option value="" selected="selected">(Todos)</option>
									<?php while($row_Perfiles=sqlsrv_fetch_array($SQL_Perfiles)){?>
										<option value="<?php echo $row_Perfiles['ID_PerfilUsuario'];?>" <?php if((isset($_GET['BuscarPerfil']))&&(strcmp($row_Perfiles['ID_PerfilUsuario'],$_GET['BuscarPerfil'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Perfiles['PerfilUsuario'];?></option>
									<?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Buscar dato</label>
							<div class="col-lg-4">
								<div class="input-group form-group has-success">
									<input name="Buscar" type="text" class="form-control" id="Buscar" placeholder="Digite para buscar..." value="<?php if(isset($_GET['Buscar'])&&($_GET['Buscar']!="")){ echo $_GET['Buscar'];}?>"><span class="input-group-btn"><button type="submit" class="btn btn-primary">Buscar</button></span>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
         <br>
		<?php if($sw==1){?>
          <div class="row">
           <div class="col-lg-12">
			<div class="ibox-content">
				 <?php include("includes/spinner.php"); ?>
           <div class="table-responsive">
			 <table class="table table-striped table-bordered table-hover dataTables-example" >
				<thead>
				<tr>
					<th>ID</th>
					<th>Usuario</th>
					<th>Nombre usuario</th>
					<th>Perfil</th>
					<th>Código SAP</th>
					<th>Email</th>
					<th>Clientes</th>
					<th>Fecha &Uacute;lt. Ingreso</th>
					<th>Estado</th>
					<th>Acciones</th>
				</tr>
				</thead>
				<tbody>
				<?php while($row=sqlsrv_fetch_array($SQL)){?>
				<tr>
					<td><?php echo $row['ID_Usuario'];?></td>
					<td><?php echo $row['Usuario'];?></td>
					<td><?php echo $row['NombreUsuario'];?></td>
					<td><?php echo $row['PerfilUsuario'];?></td>
					<td><?php echo $row['CodigoSAP'];?></td>
					<td><?php if($row['Email']!=""){echo $row['Email'];}else{?><p class="text-muted">--</p><?php };?></td>
					<td><?php $Cant=ContarClienteUsuario($row['ID_Usuario']); if($Cant>0){echo $Cant;}else{?><p class="text-muted">--</p><?php }?></td>
					<td><?php if($row['FechaUltIngreso']!=""){echo $row['FechaUltIngreso']->format('Y-m-d H:i:s');}else{ echo "Nunca ha ingresado";};?></td>
					<td id="rowAct<?php echo $row['ID_Usuario'];?>"><?php if($row['Estado']==1){?><span class="badge badge-primary"><?php echo $row['NombreEstado'];?></span><?php }else{?><span class="badge badge-danger"><?php echo $row['NombreEstado'];?></span><?php }?></td>			
                    <td>
                        <a href="usuarios.php?id=<?php echo base64_encode($row['ID_Usuario']);?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('gestionar_usuarios.php');?>" class="btn btn-info btn-circle" title="Editar"><i class="fa fa-edit"></i></a>
                        <?php if($row['Estado']==1){?>
							<a href="#" id="LinkActive<?php echo $row['ID_Usuario'];?>" onClick="Activar_Inactivar(<?php echo $row['ID_Usuario'];?>);" style="text-decoration:none" title="Inactivar"><button id="btnActive<?php echo $row['ID_Usuario'];?>" type="button" class="btn btn-danger btn-circle" ><span id="spanActive<?php echo $row['ID_Usuario'];?>" class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span></button></a>
						<?php }else{ ?>
							<a href="#" id="LinkActive<?php echo $row['ID_Usuario'];?>" onClick="Activar_Inactivar(<?php echo $row['ID_Usuario'];?>);" style="text-decoration:none" title="Activar"><button id="btnActive<?php echo $row['ID_Usuario'];?>" type="button" class="btn btn-success btn-circle" ><span id="spanActive<?php echo $row['ID_Usuario'];?>" class="glyphicon glyphicon-ok-circle" aria-hidden="true"></span></button></a>
						<?php }?>
                    </td>
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
			 $(".alkin").on('click', function(){
					$('.ibox-content').toggleClass('sk-loading');
				});			
				
            $('.dataTables-example').DataTable({
                pageLength: 25,
				order: [[ 0, "desc" ]],
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