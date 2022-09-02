<?php require("includes/conexion.php"); 
$Nombre_archivo="contrato_confidencialidad.txt";
$Archivo=fopen($Nombre_archivo,"r");
$Contenido = fread($Archivo, filesize($Nombre_archivo));

/*if ($Archivo) {
    while(($bufer = fgets($gestor, 4096)) !== false) {
        echo $bufer;
    }
    if (!feof($gestor)) {
        echo "Error: fallo inesperado de fgets()\n";
    }
    fclose($gestor);
}*/

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo NOMBRE_PORTAL;?> | Contrato de confidencialidad</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-12">
                    <h2>Contrato de confidencialidad</h2>
                </div>
            </div>
        <div class="row wrapper wrapper-content animated fadeInRight">
			<div class="col-xs-12 col-sm-12 col-md-6 col-lg-8">
				<?php echo utf8_encode($Contenido);?>
			</div>
		</div>
        <!-- InstanceEndEditable -->
        <?php include("includes/footer.php"); ?>

    </div>
</div>
<?php include("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->

<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php 
fclose($Archivo);
sqlsrv_close($conexion);
?>