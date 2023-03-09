<?php require("includes/conexion.php"); ?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo NOMBRE_PORTAL;?> | Agregar SubCategor&iacute;a</title>
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
                <div class="col-sm-8">
                    <h2>Agregar SubCategor&iacute;a</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Configuraci&oacute;n</a>
                        </li>
                        <li>
                            <a href="gestionar_categorias.php">Gestionar categor&iacute;as</a>
                        </li>
                        <li class="active">
                            <strong>Agregar SubCategor&iacute;a</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
          <div class="row">
           <div class="col-lg-12">
              <a href="gestionar_categorias.php" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
		   </div>
          </div>
          <br>
          <div class="row">
           <div class="col-lg-6">
              <form action="registro.php" method="post" class="form-horizontal" id="AgregarCategoria">
				<div class="form-group">
					<label class="col-sm-3 control-label">Nombre categor&iacute;a</label>
					<div class="col-sm-9"><input name="NombreCategoria" type="text" required="required" class="form-control" id="NombreCategoria"></div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Estado</label>
					<div class="col-sm-9">
                    	<select name="EstadoCategoria" class="form-control m-b" id="EstadoCategoria">
                           <option value="1" selected="selected">Activo</option>
                           <option value="2">Inactivo</option>
                        </select>
               	  </div>
				</div>
				<div class="form-group">
					<div class="col-sm-9">
						<button class="btn btn-primary " type="submit"><i class="fa fa-check"></i>&nbsp;Agregar</button>
					</div>
				</div>
				<input type="hidden" id="P" name="P" value="1" />
			  </form>
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
		 $("#AgregarCategoria").validate();
	});
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php //sqlsrv_close($conexion);?>