<?php require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
PermitirAcceso(801);
$sw=0;//Para saber si ya se selecciono un cliente y mostrar la información

if(isset($_POST['Cliente'])&&($_POST['Cliente'])!=""){
	$_POST['Cliente']=strtoupper($_POST['Cliente']);
	
	$Where="[CodigoCliente] LIKE '%".$_POST['Cliente']."%' 
	OR [LicTradNum] LIKE '%".$_POST['Cliente']."%' 
	OR [NombreCliente] LIKE '%".$_POST['Cliente']."%'";
	$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes",'"CodigoCliente","NombreCliente","LicTradNum","PersonaContacto","GrupoNombre","City"',$Where);	

	$sw=1;	
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Consultar cliente cartera | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_GtnCtr"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'Gestión guardada exitosamente.',
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
                    <h2>Consultar cliente</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Gesti&oacute;n de cartera</a>
                        </li>
                        <li class="active">
                            <strong>Consultar cliente</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			<div class="row">
				<div class="col-lg-12">   
					<div class="ibox-content"> 
						<?php include("includes/spinner.php"); ?>
						<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Ingrese la información para consultar el cliente</h3>
						<br>
						<form action="consultar_cliente_cartera.php" method="post" class="form-horizontal" id="Consultar">
							<div class="form-group">
								<label class="col-lg-1 control-label">Cliente</label>
								<div class="col-lg-4">
									<input autocomplete="off" name="Cliente" type="text" required="required" class="form-control" id="Cliente" maxlength="100" placeholder="Consulte el ID o el nombre del cliente" value="<?php if(isset($_POST['Cliente'])&&$_POST['Cliente']!=""){echo $_POST['Cliente'];}?>">
								</div>
								<div class="col-lg-2">
									<button type="submit" class="btn btn-outline btn-info"><i class="fa fa-search"></i> Buscar</button>
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
							<table class="table table-bordered" >
							<thead>
							<tr>
								<th>&nbsp;</th>
								<th>Código cliente</th>
								<th>Nombre cliente</th>
								<th>NIT o Cédula</th>
								<th>Grupo</th>
								<th>Contacto</th>
								<th>Ciudad</th>
								<th>Valor vencido</th>
								<th>Acciones</th>
							</tr>
							</thead>
							<tbody>
							<?php $i=1; while($row_Cliente=sql_fetch_array($SQL_Cliente)){ 
								$Total=SumarFacturasPendientes($row_Cliente['CodigoCliente']);?>
								 <tr>
									<td><?php echo $i;?></td>
									<td><?php echo $row_Cliente['CodigoCliente'];?></td>
									<td><?php echo $row_Cliente['NombreCliente'];?></td>
									<td><?php echo $row_Cliente['LicTradNum'];?></td>
									<td><?php echo $row_Cliente['GrupoNombre'];?></td>
									<td><?php echo $row_Cliente['PersonaContacto'];?></td>
									<td><?php echo $row_Cliente['City'];?></td>
									<td><?php echo "$".number_format($Total,0);?></td>
									<td><a href="gestionar_cartera.php?Clt=<?php echo base64_encode($row_Cliente['CodigoCliente']);?>" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
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
		 $("#Consultar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
		  $(".btn-link").on('click', function(){
				$('.ibox-content').toggleClass('sk-loading');
			});		 
	});
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>