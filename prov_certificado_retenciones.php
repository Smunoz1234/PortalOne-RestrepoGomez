<?php require_once "includes/conexion.php";
PermitirAcceso(601);

function PrimerDiaMesRetenciones()
{
    $month = date('m');
    $year = date('Y');
    return date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Certificado de retenciones | <?php echo NOMBRE_PORTAL; ?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include_once "includes/menu.php";?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once "includes/menu_superior.php";?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Certificado de retenciones</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Proveedores</a>
                        </li>
						<li>
                            <a href="#">Certificados</a>
                        </li>
                        <li class="active">
                            <strong>Certificado de retenciones</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			     <div class="ibox-content">
				  <form action="export_certificado.php" method="post" id="frmCertificado" class="form-horizontal" target="_blank">
					  <div class="form-group">
						<label class="col-lg-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-check-square-o"></i> Críterios de selección</h3></label>
					  </div>
					  <div class="form-group">
						<div class="form-group">
						  	<label class="col-lg-1 control-label">Período</label>
							<div class="col-lg-3" id="data_5">
                                <div class="input-daterange input-group" id="datepicker">
                                    <input type="text" class="form-control-sm form-control" name="FechaInicial" id="FechaInicial" value="<?php echo PrimerDiaMesRetenciones(); ?>" autocomplete="off" />
                                    <span class="input-group-addon">hasta</span>
                                    <input type="text" class="form-control-sm form-control" name="FechaFinal" id="FechaFinal" value="<?php echo date('Y-m-d'); ?>" autocomplete="off" />
                                </div>
                            </div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Año gravable</label>
								<div class="col-lg-2">
									<select name="AGravable" class="form-control" id="AGravable" required>
										<option value="2016">2016</option>
										<option value="2017">2017</option>
										<option value="2018">2018</option>
										<option value="2019">2019</option>
										<option value="2020">2020</option>
										<option value="2021" selected>2021</option>
										<option value="2022">2022</option>
                                        <option value="2023">2023</option>
									</select>
								</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Comentarios</label>
							<div class="col-lg-4">
								<textarea name="Comentarios" rows="3" maxlength="150" class="form-control" id="Comentarios" placeholder="(Opcional)"></textarea>
							</div>
						</div>
					   <div class="col-lg-1">
							<button type="submit" name="submit" id="submit" class="btn btn-primary"><i class="fa fa-external-link"></i> Generar certificado</button>
					   </div>
					   <input type="hidden" name="MM_Cert" id="MM_Cert" value="CertRet">
					</div>
				 </form>
				</div>
			</div>
		  </div>
		</div>
        <!-- InstanceEndEditable -->
        <?php include_once "includes/footer.php";?>

    </div>
</div>
<?php include_once "includes/pie.php";?>
<!-- InstanceBeginEditable name="EditRegion4" -->
 <script>
        $(document).ready(function(){

			$('#FechaInicio').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				todayHighlight: true,
				format: 'yyyy-mm-dd'
            });
			 $('#FechaFin').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				todayHighlight: true,
				format: 'yyyy-mm-dd'
            });

			$('#data_5 .input-daterange').datepicker({
                keyboardNavigation: false,
                forceParse: false,
                autoclose: true,
				format: 'yyyy-mm-dd'
            });
        });

    </script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>