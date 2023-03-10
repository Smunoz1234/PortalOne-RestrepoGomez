<?php require_once "includes/conexion.php";
PermitirAcceso(1605);

$sw = 0;
if (isset($_GET['Marca']) && $_GET['Marca'] != "") {
    $sw = 1;
}

// Clientes
if (PermitirFuncion(205)) {
    $SQL_Cliente = Seleccionar("uvw_Sap_tbl_Clientes", "CodigoCliente, NombreCliente", "", 'NombreCliente');
} else {
    $Where = "ID_Usuario = " . $_SESSION['CodUser'];
    $SQL_Cliente = Seleccionar("uvw_tbl_ClienteUsuario", "CodigoCliente, NombreCliente", $Where);
}

// Marcas de vehiculo
$SQL_Marca = Seleccionar('uvw_Sap_tbl_TarjetasEquipos_MarcaVehiculo', '*');

// Concesionarios en la tarjeta de equipo
$SQL_Concesionario = Seleccionar('uvw_Sap_tbl_TarjetasEquipos_Concesionario', '*');

//Fechas, SMM 13/07/2022
$FI_FechaMatricula = "";
$FF_FechaMatricula = "";
if (isset($_GET['FI_FechaMatricula']) && $_GET['FI_FechaMatricula'] != "") {
    $FI_FechaMatricula = $_GET['FI_FechaMatricula'];
    $sw = 1;
} else {
    //Restar 7 dias a la fecha actual
    $fecha = date('Y-m-d');
    $nuevafecha = strtotime('-' . ObtenerVariable("DiasRangoFechasGestionar") . ' day');
    $nuevafecha = date('Y-m-d', $nuevafecha);
    // $FI_FechaMatricula = $nuevafecha;
}
if (isset($_GET['FF_FechaMatricula']) && $_GET['FF_FechaMatricula'] != "") {
    $FF_FechaMatricula = $_GET['FF_FechaMatricula'];
    $sw = 1;
} else {
    // $FF_FechaMatricula = date('Y-m-d');
}

$FI_Fecha_SOAT = "";
$FF_Fecha_SOAT = "";
if (isset($_GET['FI_Fecha_SOAT']) && $_GET['FI_Fecha_SOAT'] != "") {
    $FI_Fecha_SOAT = $_GET['FI_Fecha_SOAT'];
    $sw = 1;
} else {
    //Restar 7 dias a la fecha actual
    $fecha = date('Y-m-d');
    $nuevafecha = strtotime('-' . ObtenerVariable("DiasRangoFechasGestionar") . ' day');
    $nuevafecha = date('Y-m-d', $nuevafecha);
    // $FI_Fecha_SOAT = $nuevafecha;
}
if (isset($_GET['FF_Fecha_SOAT']) && $_GET['FF_Fecha_SOAT'] != "") {
    $FF_Fecha_SOAT = $_GET['FF_Fecha_SOAT'];
    $sw = 1;
} else {
    // $FF_Fecha_SOAT = date('Y-m-d');
}

$FI_Fecha_Tecno = "";
$FF_Fecha_Tecno = "";
if (isset($_GET['FI_Fecha_Tecno']) && $_GET['FI_Fecha_Tecno'] != "") {
    $FI_Fecha_Tecno = $_GET['FI_Fecha_Tecno'];
    $sw = 1;
} else {
    //Restar 7 dias a la fecha actual
    $fecha = date('Y-m-d');
    $nuevafecha = strtotime('-' . ObtenerVariable("DiasRangoFechasGestionar") . ' day');
    $nuevafecha = date('Y-m-d', $nuevafecha);
    // $FI_Fecha_Tecno = $nuevafecha;
}
if (isset($_GET['FF_Fecha_Tecno']) && $_GET['FF_Fecha_Tecno'] != "") {
    $FF_Fecha_Tecno = $_GET['FF_Fecha_Tecno'];
    $sw = 1;
} else {
    // $FF_Fecha_Tecno = date('Y-m-d');
}

$FI_FechaUlt_CambAceite = "";
$FF_FechaUlt_CambAceite = "";
if (isset($_GET['FI_FechaUlt_CambAceite']) && $_GET['FI_FechaUlt_CambAceite'] != "") {
    $FI_FechaUlt_CambAceite = $_GET['FI_FechaUlt_CambAceite'];
    $sw = 1;
} else {
    //Restar 7 dias a la fecha actual
    $fecha = date('Y-m-d');
    $nuevafecha = strtotime('-' . ObtenerVariable("DiasRangoFechasGestionar") . ' day');
    $nuevafecha = date('Y-m-d', $nuevafecha);
    // $FI_FechaUlt_CambAceite = $nuevafecha;
}
if (isset($_GET['FF_FechaUlt_CambAceite']) && $_GET['FF_FechaUlt_CambAceite'] != "") {
    $FF_FechaUlt_CambAceite = $_GET['FF_FechaUlt_CambAceite'];
    $sw = 1;
} else {
    // $FF_FechaUlt_CambAceite = date('Y-m-d');
}

$FI_FechaProx_CambAceite = "";
$FF_FechaProx_CambAceite = "";
if (isset($_GET['FI_FechaProx_CambAceite']) && $_GET['FI_FechaProx_CambAceite'] != "") {
    $FI_FechaProx_CambAceite = $_GET['FI_FechaProx_CambAceite'];
    $sw = 1;
} else {
    //Restar 7 dias a la fecha actual
    $fecha = date('Y-m-d');
    $nuevafecha = strtotime('-' . ObtenerVariable("DiasRangoFechasGestionar") . ' day');
    $nuevafecha = date('Y-m-d', $nuevafecha);
    // $FI_FechaProx_CambAceite = $nuevafecha;
}
if (isset($_GET['FF_FechaProx_CambAceite']) && $_GET['FF_FechaProx_CambAceite'] != "") {
    $FF_FechaProx_CambAceite = $_GET['FF_FechaProx_CambAceite'];
    $sw = 1;
} else {
    // $FF_FechaProx_CambAceite = date('Y-m-d');
}

$FI_FechaUlt_Mant = "";
$FF_FechaUlt_Mant = "";
if (isset($_GET['FI_FechaUlt_Mant']) && $_GET['FI_FechaUlt_Mant'] != "") {
    $FI_FechaUlt_Mant = $_GET['FI_FechaUlt_Mant'];
    $sw = 1;
} else {
    //Restar 7 dias a la fecha actual
    $fecha = date('Y-m-d');
    $nuevafecha = strtotime('-' . ObtenerVariable("DiasRangoFechasGestionar") . ' day');
    $nuevafecha = date('Y-m-d', $nuevafecha);
    // $FI_FechaUlt_Mant = $nuevafecha;
}
if (isset($_GET['FF_FechaUlt_Mant']) && $_GET['FF_FechaUlt_Mant'] != "") {
    $FF_FechaUlt_Mant = $_GET['FF_FechaUlt_Mant'];
    $sw = 1;
} else {
    // $FF_FechaUlt_Mant = date('Y-m-d');
}

$FI_FechaProx_Mant = "";
$FF_FechaProx_Mant = "";
if (isset($_GET['FI_FechaProx_Mant']) && $_GET['FI_FechaProx_Mant'] != "") {
    $FI_FechaProx_Mant = $_GET['FI_FechaProx_Mant'];
    $sw = 1;
} else {
    //Restar 7 dias a la fecha actual
    $fecha = date('Y-m-d');
    $nuevafecha = strtotime('-' . ObtenerVariable("DiasRangoFechasGestionar") . ' day');
    $nuevafecha = date('Y-m-d', $nuevafecha);
    // $FI_FechaProx_Mant = $nuevafecha;
}
if (isset($_GET['FF_FechaProx_Mant']) && $_GET['FF_FechaProx_Mant'] != "") {
    $FF_FechaProx_Mant = $_GET['FF_FechaProx_Mant'];
    $sw = 1;
} else {
    // $FF_FechaProx_Mant = date('Y-m-d');
}

// Filtros
$Cliente = $_GET['ClienteEquipo'] ?? "";
$IdMarca = $_GET['Marca'] ?? "";
$CiudadSede = $_GET['CiudadSede'] ?? "";
$IdConcesionario = $_GET['Concesionario'] ?? "";

if ($sw == 1) {
    $Param = array(
        "'" . $Cliente . "'",
        "'" . $IdMarca . "'",
        "'" . $CiudadSede . "'",
        "'" . $IdConcesionario . "'",
        "'" . FormatoFecha($FI_FechaMatricula) . "'",
        "'" . FormatoFecha($FF_FechaMatricula) . "'",
        "'" . FormatoFecha($FI_Fecha_SOAT) . "'",
        "'" . FormatoFecha($FF_Fecha_SOAT) . "'",
        "'" . FormatoFecha($FI_Fecha_Tecno) . "'",
        "'" . FormatoFecha($FF_Fecha_Tecno) . "'",
        "'" . FormatoFecha($FI_FechaUlt_CambAceite) . "'",
        "'" . FormatoFecha($FF_FechaUlt_CambAceite) . "'",
        "'" . FormatoFecha($FI_FechaProx_CambAceite) . "'",
        "'" . FormatoFecha($FF_FechaProx_CambAceite) . "'",
        "'" . FormatoFecha($FI_FechaUlt_Mant) . "'",
        "'" . FormatoFecha($FF_FechaUlt_Mant) . "'",
        "'" . FormatoFecha($FI_FechaProx_Mant) . "'",
        "'" . FormatoFecha($FF_FechaProx_Mant) . "'",
    );

    $SQL = EjecutarSP('usp_inf_GestionTarjetaEquipos', $Param);
}
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Gestión de tarjetas de equipo | <?php echo NOMBRE_PORTAL; ?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {
		$("#NombreClienteEquipo").change(function(){
			var NomCliente=document.getElementById("NombreClienteEquipo");
			var Cliente=document.getElementById("ClienteEquipo");
			if(NomCliente.value==""){
				Cliente.value="";
			}
		});
	});
</script>
<!-- InstanceEndEditable -->

<style>
	table.dataTable tbody tr.selected {
		background-color: gray !important;
	}
</style>
</head>

<body>

<div id="wrapper">

    <?php include "includes/menu.php";?>

    <div id="page-wrapper" class="gray-bg">
        <?php include "includes/menu_superior.php";?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Gestión de tarjetas de equipo</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="#">Mantenimiento</a>
                        </li>
						<li>
                            <a href="#">Informes</a>
                        </li>
                        <li class="active">
                            <strong>Gestión de tarjetas de equipo</strong>
                        </li>
                    </ol>
                </div>
			<?php if (PermitirFuncion(1602)) {?>
                <div class="col-sm-4">
                    <div class="title-action">
                        <a href="tarjeta_equipo.php" class="alkin btn btn-primary"><i class="fa fa-plus-circle"></i> Crear nueva tarjeta de equipo</a>
                    </div>
                </div>
			<?php }?>
               <?php //echo $Cons;?>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include "includes/spinner.php";?>
				  <form action="informe_tarjeta_equipo.php" method="get" id="formBuscar" class="form-horizontal">
					    <div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Marca <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<select name="Marca" class="form-control" id="Marca" required>
										<option value="" disabled selected>Seleccione...</option>
								  <?php while ($row_Marca = sqlsrv_fetch_array($SQL_Marca)) {?>
										<option value="<?php echo $row_Marca['IdMarcaVehiculo']; ?>" <?php if ((isset($_GET['Marca'])) && (strcmp($row_Marca['IdMarcaVehiculo'], $_GET['Marca']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Marca['DeMarcaVehiculo']; ?></option>
								  <?php }?>
								</select>
							</div>

							<label class="col-lg-1 control-label">Concesionario</label>
							<div class="col-lg-3">
								<select name="Concesionario" class="form-control" id="Concesionario">
										<option value="">(Todos)</option>
								  <?php while ($row_Concesionario = sqlsrv_fetch_array($SQL_Concesionario)) {?>
										<option value="<?php echo $row_Concesionario['CodigoConcesionario']; ?>" <?php if ((isset($_GET['Concesionario'])) && (strcmp($row_Concesionario['CodigoConcesionario'], $_GET['Concesionario']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_Concesionario['NombreConcesionario']; ?></option>
								  <?php }?>
								</select>
							</div>

							<label class="col-lg-1 control-label">Ciudad Sede</label>
							<div class="col-lg-3">
								<input name="CiudadSede" type="text" class="form-control" id="CiudadSede" maxlength="100" value="<?php if (isset($_GET['CiudadSede']) && ($_GET['CiudadSede'] != "")) {echo $_GET['CiudadSede'];}?>">
							</div>
						</div>

					  	<div class="form-group">
						  	<label class="col-lg-1 control-label">Cliente</label>
							<div class="col-lg-3">
								<input name="ClienteEquipo" type="hidden" id="ClienteEquipo" value="<?php if (isset($_GET['ClienteEquipo']) && ($_GET['ClienteEquipo'] != "")) {echo $_GET['ClienteEquipo'];}?>">
								<input name="NombreClienteEquipo" type="text" class="form-control" id="NombreClienteEquipo" placeholder="Para TODOS, dejar vacio..." value="<?php if (isset($_GET['NombreClienteEquipo']) && ($_GET['NombreClienteEquipo'] != "")) {echo $_GET['NombreClienteEquipo'];}?>">
							</div>

							<label class="col-lg-1 control-label">Fecha Matricula</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group">
									<input name="FI_FechaMatricula" type="text" class="input-sm form-control fecha" id="FI_FechaMatricula" placeholder="Fecha inicial" value="<?php echo $FI_FechaMatricula; ?>" autocomplete="off" />
									<span class="input-group-addon">hasta</span>
									<input name="FF_FechaMatricula" type="text" class="input-sm form-control fecha" id="FF_FechaMatricula" placeholder="Fecha final" value="<?php echo $FF_FechaMatricula; ?>" autocomplete="off" />
								</div>
							</div>

							<label class="col-lg-1 control-label">Fecha SOAT</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group">
									<input name="FI_Fecha_SOAT" type="text" class="input-sm form-control fecha" id="FI_Fecha_SOAT" placeholder="Fecha inicial" value="<?php echo $FI_Fecha_SOAT; ?>" autocomplete="off" />
									<span class="input-group-addon">hasta</span>
									<input name="FF_Fecha_SOAT" type="text" class="input-sm form-control fecha" id="FF_Fecha_SOAT" placeholder="Fecha final" value="<?php echo $FF_Fecha_SOAT; ?>" autocomplete="off" />
								</div>
							</div>
						</div>

						<div class="form-group">
							<label class="col-lg-1 control-label">Fecha Ult. Mantenimiento</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group">
									<input name="FI_FechaUlt_Mant" type="text" class="input-sm form-control fecha" id="FI_FechaUlt_Mant" placeholder="Fecha inicial" value="<?php echo $FI_FechaUlt_Mant; ?>" autocomplete="off" />
									<span class="input-group-addon">hasta</span>
									<input name="FF_FechaUlt_Mant" type="text" class="input-sm form-control fecha" id="FF_FechaUlt_Mant" placeholder="Fecha final" value="<?php echo $FF_FechaUlt_Mant; ?>" autocomplete="off" />
								</div>
							</div>

							<label class="col-lg-1 control-label">Fecha Ult. Camb. Aceite</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group">
									<input name="FI_FechaUlt_CambAceite" type="text" class="input-sm form-control fecha" id="FI_FechaUlt_CambAceite" placeholder="Fecha inicial" value="<?php echo $FI_FechaUlt_CambAceite; ?>" autocomplete="off" />
									<span class="input-group-addon">hasta</span>
									<input name="FF_FechaUlt_CambAceite" type="text" class="input-sm form-control fecha" id="FF_FechaUlt_CambAceite" placeholder="Fecha final" value="<?php echo $FF_FechaUlt_CambAceite; ?>" autocomplete="off" />
								</div>
							</div>

							<label class="col-lg-1 control-label">Fecha Tecno</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group">
									<input name="FI_Fecha_Tecno" type="text" class="input-sm form-control fecha" id="FI_Fecha_Tecno" placeholder="Fecha inicial" value="<?php echo $FI_Fecha_Tecno; ?>" autocomplete="off" />
									<span class="input-group-addon">hasta</span>
									<input name="FF_Fecha_Tecno" type="text" class="input-sm form-control fecha" id="FF_Fecha_Tecno" placeholder="Fecha final" value="<?php echo $FF_Fecha_Tecno; ?>" autocomplete="off" />
								</div>
							</div>
						</div>

						<div class="form-group">
							<label class="col-lg-1 control-label">Fecha Prox. Mantenimiento</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group">
									<input name="FI_FechaProx_Mant" type="text" class="input-sm form-control fecha" id="FI_FechaProx_Mant" placeholder="Fecha inicial" value="<?php echo $FI_FechaProx_Mant; ?>" autocomplete="off" />
									<span class="input-group-addon">hasta</span>
									<input name="FF_FechaProx_Mant" type="text" class="input-sm form-control fecha" id="FF_FechaProx_Mant" placeholder="Fecha final" value="<?php echo $FF_FechaProx_Mant; ?>" autocomplete="off" />
								</div>
							</div>

							<label class="col-lg-1 control-label">Fecha Prox. Camb. Aceite</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group">
									<input name="FI_FechaProx_CambAceite" type="text" class="input-sm form-control fecha" id="FI_FechaProx_CambAceite" placeholder="Fecha inicial" value="<?php echo $FI_FechaProx_CambAceite; ?>" autocomplete="off" />
									<span class="input-group-addon">hasta</span>
									<input name="FF_FechaProx_CambAceite" type="text" class="input-sm form-control fecha" id="FF_FechaProx_CambAceite" placeholder="Fecha final" value="<?php echo $FF_FechaProx_CambAceite; ?>" autocomplete="off" />
								</div>
							</div>

							<div class="col-lg-4">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>

						<?php if (($sw == 1) && sqlsrv_has_rows($SQL)) {?>
					  	<div class="form-group">
							<div class="col-lg-10">
								<a href="exportar_excel.php?exp=10&Cons=<?php echo base64_encode(implode(",", $Param)); ?>&sp=<?php echo base64_encode("usp_inf_GestionTarjetaEquipos"); ?>">
									<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
								</a>
							</div>
						</div>
					   <?php }?>
				 </form>
			</div>
			</div>
		  </div>
         <br>

		<?php if ($sw == 1) {?>
          <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include "includes/spinner.php";?>
			<div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example" id="example">
                    <thead>
                    <tr>
						<th>Núm.</th>
						<th>Código cliente</th>
						<th>Nombre cliente</th>
						<th>Serial interno</th>
                        <th>Marca vehículo</th>
						<th>Ciudad Sede</th>
						<th>Concesionario</th>
						<th>Fecha Matricula</th>
						<th>Fecha SOAT</th>
						<th>Fecha Tecno.</th>
						<th>Fecha Ult. Camb. Aceite</th>
						<th>Fecha Prox. Camb. Aceite</th>
						<th>Novedad</th>
						<th>Fecha Agenda</th>
						<th>Fecha Ult. Mant.</th>
						<th>Fecha Prox. Mant.</th>
						<th>Estado</th>
						<th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = sqlsrv_fetch_array($SQL)) {?>
						 <tr class="gradeX tooltip-demo">
							<td><?php echo $row['IdTarjetaEquipo']; ?></td>
							<td><?php echo $row['CardCode']; ?></td>
							<td><?php echo $row['CardName']; ?></td>
							<td><?php echo $row['SerialInterno']; ?></td>
							<td><?php echo $row['CDU_Marca']; ?></td>
							<td><?php echo $row['CDU_SedeVenta']; ?></td>
							<td><?php echo $row['CDU_Concesionario']; ?></td>
							<td><?php echo ($row['CDU_FechaMatricula'] != "") ? $row['CDU_FechaMatricula']->format('Y-m-d') : ""; ?></td>
							<td><?php echo ($row['CDU_Fecha_SOAT'] != "") ? $row['CDU_Fecha_SOAT']->format('Y-m-d') : ""; ?></td>
							<td><?php echo ($row['CDU_Fecha_Tecno'] != "") ? $row['CDU_Fecha_Tecno']->format('Y-m-d') : ""; ?></td>
							<td><?php echo ($row['CDU_FechaUlt_CambAceite'] != "") ? $row['CDU_FechaUlt_CambAceite']->format('Y-m-d') : ""; ?></td>
							<td><?php echo ($row['CDU_FechaProx_CambAceite'] != "") ? $row['CDU_FechaProx_CambAceite']->format('Y-m-d') : ""; ?></td>
							
							<td><?php echo $row['CDU_Novedad']; ?></td>
							<td><?php echo ($row['CDU_FechaAgenda'] != "") ? $row['CDU_FechaAgenda']->format('Y-m-d') : ""; ?></td>
							
							<td><?php echo ($row['CDU_FechaUlt_Mant'] != "") ? $row['CDU_FechaUlt_Mant']->format('Y-m-d') : ""; ?></td>
							<td><?php echo ($row['CDU_FechaProx_Mant'] != "") ? $row['CDU_FechaProx_Mant']->format('Y-m-d') : ""; ?></td>
							<td>
								<?php if ($row['CodEstado'] == 'A') {?>
									<span  class='label label-info'>Activo</span>
								<?php } elseif ($row['CodEstado'] == 'R') {?>
									<span  class='label label-danger'>Devuelto</span>
								<?php } elseif ($row['CodEstado'] == 'T') {?>
									<span  class='label label-success'>Finalizado</span>
								<?php } elseif ($row['CodEstado'] == 'L') {?>
									<span  class='label label-secondary'>Concedido en préstamo</span>
								<?php } elseif ($row['CodEstado'] == 'I') {?>
									<span  class='label label-warning'>En laboratorio de reparación</span>
								<?php }?>
							</td>
							<td>
								<div>
									<a href="tarjeta_equipo.php?id=<?php echo base64_encode($row['IdTarjetaEquipo']); ?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&pag=<?php echo base64_encode('informe_tarjeta_equipo.php'); ?>&tl=1" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a>
									<a target="_blank" href="gestionar_cartera.php?Clt=<?php echo base64_encode($row['CardCode']); ?>&TE=<?php echo base64_encode($row['IdTarjetaEquipo']); ?>" class="btn btn-info btn-xs"><i class="fa fa-plus"></i> Crear Gestión CRM</a>
								</div>
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
        <?php include "includes/footer.php";?>

    </div>
</div>
<?php include "includes/pie.php";?>
<!-- InstanceBeginEditable name="EditRegion4" -->
 <script>
        $(document).ready(function(){
			$('#example tbody').on('click', 'tr', function () {
				if ($(this).hasClass('selected')) {
					$(this).removeClass('selected');
				} else {
					$('#example tr.selected').removeClass('selected');
					$(this).addClass('selected');
				}
			});

			$("#formBuscar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
			 $(".alkin").on('click', function(){
					$('.ibox-content').toggleClass('sk-loading');
				});

			// SMM, 25/08/2022
			$('.fecha').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				todayHighlight: true,
				format: 'yyyy-mm-dd'
            });

			$('.chosen-select').chosen({width: "100%"});

			var options = {
				url: function(phrase) {
					return "ajx_buscar_datos_json.php?type=7&id="+phrase;
				},

				getValue: "NombreBuscarCliente",
				requestDelay: 400,
				list: {
					match: {
						enabled: true
					},
					onClickEvent: function() {
						var value = $("#NombreClienteEquipo").getSelectedItemData().CodigoCliente;
						$("#ClienteEquipo").val(value).trigger("change");
					},
					onKeyEnterEvent: function() {
						var value = $("#NombreClienteEquipo").getSelectedItemData().CodigoCliente;
						$("#ClienteEquipo").val(value).trigger("change");
					}
				}
			};

			$("#NombreClienteEquipo").easyAutocomplete(options);

            $('.dataTables-example').DataTable({
                pageLength: 25,
                dom: '<"html5buttons"B>lTfgitp',
				order: [[ 0, "desc" ]],
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
