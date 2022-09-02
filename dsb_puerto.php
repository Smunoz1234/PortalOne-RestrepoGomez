<?php require_once("includes/conexion.php"); 

setlocale(LC_TIME, "spanish");

if(PermitirFuncion(1705)){
	$SQL_ContPuerto=EjecutarSP('sp_DashboardContadoresPuerto');
	$row_ContPuerto=sql_fetch_array($SQL_ContPuerto);
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Dashboard puerto | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	
</style>

<!-- InstanceEndEditable -->
</head>

<body class="mini-navbar">

<div id="wrapper">

    <?php include_once("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row page-wrapper wrapper-content animated fadeInRight">
		  <div class="row m-b-md">
				<div class="col-lg-12">
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<h2>Bienvenido</h2>
					</div>
				</div>
			</div>
			<?php if(PermitirFuncion(1705)){?>
		  <div class="row">
            <div class="col-lg-4">
              <div class="ibox ">
                <div class="ibox-title"> <span class="label label-info pull-right"><?php echo ucwords(strftime("%B"));?></span>
                  <h5>Monitoreo de temperaturas</h5>
                </div>
                <div class="ibox-content">
                  <h1 class="no-margins"><?php echo number_format($row_ContPuerto['CantTemp'],0);?></h1>
				</div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="ibox ">
                <div class="ibox-title"> <span class="label label-info pull-right"><?php echo ucwords(strftime("%B"));?></span>
                  <h5>Estado fitosanitario</h5>
                </div>
                <div class="ibox-content">
                  <h1 class="no-margins"><?php echo number_format($row_ContPuerto['CantEstadoFit'],0);?></h1>
				</div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="ibox ">
                <div class="ibox-title"> <span class="label label-info pull-right"><?php echo ucwords(strftime("%B"));?></span>
                  <h5>Análisis de laboratorio</h5>
                </div>
                <div class="ibox-content">
                  <h1 class="no-margins"><?php echo number_format($row_ContPuerto['CantAnalisisLab'],0);?></h1>
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

<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>