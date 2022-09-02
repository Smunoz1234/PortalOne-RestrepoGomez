<?php require_once("includes/conexion.php");
PermitirAcceso(203);
$sw=0;//Verificar que hay datos
$Cons="Select * From uvw_tbl_PerfilesUsuarios Order by PerfilUsuario";
$SQL=sqlsrv_query($conexion,$Cons);

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Gestionar perfiles | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_Perfil"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El perfil ha sido agregado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_EditPerfil"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El perfil ha sido editado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_Perfil_delete"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El perfil ha sido eliminado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
?>
<script>
function Eliminar(id){
	Swal.fire({
		title: "Eliminar",
		text: "¿Está seguro que desea eliminar este perfil?",
		icon: "warning",
		showCancelButton: true,
		confirmButtonText: "Si, estoy seguro",
		cancelButtonText: "Cancelar"
	}).then((result) => {
		if (result.isConfirmed) {
			location.href='registro.php?P=7&id='+id;
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
                    <h2>Gestionar perfiles</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Administraci&oacute;n</a>
                        </li>
                        <li class="active">
                            <strong>Gestionar perfiles</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			 <div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
				<div class="row">
					<div class="col-lg-12"> 
						<div class="ibox">
							<div class="ibox-title bg-success">
								<h5 class="collapse-link"><i class="fa fa-play-circle"></i> Acciones</h5>
								 <a class="collapse-link pull-right">
									<i class="fa fa-chevron-up"></i>
								</a>	
							</div>
							<div class="ibox-content">
								<div class="form-group">
									<div class="col-lg-6">
										<a href="perfil.php" class="btn btn-outline btn-primary"><i class="fa fa-plus-circle"></i> Agregar perfil</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>			
         <br>
          <div class="ibox-content">
           <div class="row">
			   <div class="col-lg-12">
           <div class="table-responsive">       
			<table class="table table-striped">
				<thead>
				<tr>
					<th>Nombre del perfil</th>
					<th>Cant. Usuarios</th>
					<th>Acciones</th>
				</tr>
				</thead>
				<tbody>
				<?php while($row=sqlsrv_fetch_array($SQL)){?>
				<tr>
					<td><?php echo $row['PerfilUsuario'];?></td>
					<td><?php echo $row['CantUsuarios'];?></td>
                    <td>
                        <a href="perfil.php?id=<?php echo base64_encode($row['ID_PerfilUsuario']);?>&tl=1" class="btn btn-info btn-circle" title="Editar"><i class="fa fa-edit"></i></a>
                        <?php if($row['CantUsuarios']==0){?><a href="#" onClick="Eliminar(<?php echo $row['ID_PerfilUsuario'];?>);" class="btn btn-danger btn-circle" title="Eliminar"><i class="fa fa-eraser"></i></a><?php }?>
                    </td>
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
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->

<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>