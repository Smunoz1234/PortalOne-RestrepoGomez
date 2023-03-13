<?php require_once "includes/conexion.php";
//require_once("includes/conexion_hn.php");
if (PermitirAcceso(1002) || PermitirAcceso(1003)) {
    $sw_ext = 0;
}
//Sw que permite saber si la ventana esta abierta en modo pop-up. Si es así, no cargo el menú ni el menú superior.
$sw_tech = 0; //Sw para saber si el articulo tiene algun tipo de tecnologia. (DIALNET)
$sw_error = 0; //Sw para saber si ha ocurrido un error al crear o actualizar un articulo.

//Posicion y OLT para controlar los datos que se envian a SAP cuando sea tecnologia AMS.
$Posicion = "";
$OLT = "";

if (isset($_GET['id']) && ($_GET['id'] != "")) {
    $IdItemCode = base64_decode($_GET['id']);
}

if (isset($_GET['ext']) && ($_GET['ext'] == 1)) {
    $sw_ext = 1; //Se está abriendo como pop-up
} elseif (isset($_POST['ext']) && ($_POST['ext'] == 1)) {
    $sw_ext = 1; //Se está abriendo como pop-up
} else {
    $sw_ext = 0;
}

if (isset($_GET['tl']) && ($_GET['tl'] != "")) { //0 Si se está creando. 1 Se se está editando.
    $edit = $_GET['tl'];
} elseif (isset($_POST['tl']) && ($_POST['tl'] != "")) {
    $edit = $_POST['tl'];
} else {
    $edit = 0;
}

if ($edit == 0) {
    $Title = "Crear artículo";
} else {
    $Title = "Editar artículo";
}

if (isset($_POST['P']) && ($_POST['P'] == "MM_Art")) { //Insertar o actualizar articulo
    try {
        $Metodo = 2; //Actualizar en el web services
        $Type = 2; //Ejecutar actualizar en el SP
        $IdArticuloPortal = "NULL";
        if (base64_decode($_POST['IdArticuloPortal']) == "") {
            $Metodo = 2;
            $Type = 1;
        } else {
            $IdArticuloPortal = "'" . base64_decode($_POST['IdArticuloPortal']) . "'";
        }

        $Grupo = explode("__", $_POST['GroupCode']);

        $ParamArticulos = array(
            "$IdArticuloPortal",
            "'" . $_POST['ItemCode'] . "'",
            "'" . $_POST['ItemName'] . "'",
            "'" . $_POST['FrgnName'] . "'",
            "'" . $Grupo[0] . "'",
            "'" . $_POST['ItemType'] . "'",
            "'" . $_POST['EstadoArticulo'] . "'",
            "$Metodo",
            "'" . $_SESSION['CodUser'] . "'",
            "'" . $_SESSION['CodUser'] . "'",
            "$Type",
        );
        $SQL_Articulos = EjecutarSP('sp_tbl_Articulos', $ParamArticulos, 48);
        if ($SQL_Articulos) {
            if (base64_decode($_POST['IdArticuloPortal']) == "") {
                $row_NewIdArticulo = sqlsrv_fetch_array($SQL_Articulos);
                $IdArticulo = $row_NewIdArticulo[0];
            } else {
                $IdArticulo = base64_decode($_POST['IdArticuloPortal']);
            }
            $IdItemCode = $_POST['ItemCode'];
            //sqlsrv_close($conexion);
            //header('Location:'.base64_decode($_POST['pag'])."?".base64_decode($_POST['return']).'&a='.base64_encode("OK_ArtUpd"));

            //Enviar datos al WebServices
            try {
                require_once "includes/conect_ws.php";
                $Parametros = array(
                    'pIdArticulo' => $IdArticulo,
                    'pLogin' => $_SESSION['User'],
                );
                $Client->AppPortal_InsertarArticulos($Parametros);
                $Respuesta = $Client->__getLastResponse();
                $Contenido = new SimpleXMLElement($Respuesta, 0, false, "s", true);
                $espaciosDeNombres = $Contenido->getNamespaces(true);
                $Nodos = $Contenido->children($espaciosDeNombres['s']);
                $Nodo = $Nodos->children($espaciosDeNombres['']);
                $Nodo2 = $Nodo->children($espaciosDeNombres['']);

                $Archivo = json_decode($Nodo2, true);
                if ($Archivo['ID_Respuesta'] == "0") {
                    //InsertarLog(1, 0, 'Error al generar el informe');
                    //throw new Exception('Error al generar el informe. Error de WebServices');
                    $sw_error = 1;
                    $msg_error = $Archivo['DE_Respuesta'];
                    //throw new Exception($Archivo['DE_Respuesta']);
                    /*if($_POST['EstadoActividad']=='Y'){
                $UpdEstado="Update tbl_Actividades Set Cod_Estado='N' Where ID_Actividad='".$IdActividad."'";
                $SQL_UpdEstado=sqlsrv_query($conexion,$UpdEstado);
                }*/
                } else {

                    sqlsrv_close($conexion);
                    header('Location:' . base64_decode($_POST['pag']) . "?" . base64_decode($_POST['return']) . '&a=' . base64_encode("OK_ArtUpd"));
                }
            } catch (Exception $e) {
                $sw_error = 1;
                //echo 'Excepcion capturada 1: ',  $e->getMessage(), "\n";
            }
        } else {
            $sw_error = 1;
            $msg_error = "Error al actualizar el articulo";
        }
    } catch (Exception $e) {
        $sw_error = 1;
        //echo 'Excepcion capturada 2: ',  $e->getMessage(), "\n";
    }
}

if ($edit == 1) { //Editar articulo

    //Articulo
    $SQL = Seleccionar('uvw_Sap_tbl_ArticulosTodos', '*', "ItemCode='" . $IdItemCode . "'");
    $row = sqlsrv_fetch_array($SQL);
    //$sw_tech=$row['CDU_IdTipoTecnologia'];
    //$Posicion=$row['Posicion'];
    //$OLT=$row['IdOLT'];

    //Datos de inventario
    $SQL_DtInvent = Seleccionar('uvw_Sap_tbl_Articulos', '*', "ItemCode='" . $IdItemCode . "'");

    //Lista de precios
    $SQL_ListaPrecio = Seleccionar('uvw_Sap_tbl_ListaPrecioArticulos', '*', "ItemCode='" . $IdItemCode . "'");

    //Anexos
    $SQL_AnexoArticulos = Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos', '*', "AbsEntry='" . $row['IdAnexoArticulo'] . "'");

}
if ($sw_error == 1) { //Si ocurre un error

    //Articulo
    $SQL = Seleccionar('uvw_tbl_Articulos', '*', "ItemCode='" . $IdItemCode . "'");
    $row = sqlsrv_fetch_array($SQL);
    //$sw_tech=$row['CDU_IdTipoTecnologia'];
    //$Posicion=$row['Posicion'];
    //$OLT=$row['IdOLT'];

    //Datos de inventario
    $SQL_DtInvent = Seleccionar('uvw_Sap_tbl_Articulos', '*', "ItemCode='" . $IdItemCode . "'");

    //Lista de precios
    $SQL_ListaPrecio = Seleccionar('uvw_Sap_tbl_ListaPrecioArticulos', '*', "ItemCode='" . $IdItemCode . "'");

}

//Estado articulo
$SQL_EstadoArticulo = Seleccionar('uvw_tbl_EstadoArticulo', '*');

//Tipos de articulos
$SQL_TipoArticulo = Seleccionar('uvw_tbl_TipoArticulo', '*');

//Grupos de articulos
$SQL_GruposArticulos = Seleccionar('uvw_Sap_tbl_GruposArticulos', '*', '', 'ItmsGrpNam');

// Stiven Muñoz Murillo, 23/12/2021
$SQL_MarcaVehiculo = Seleccionar('uvw_Sap_tbl_Articulos_MarcaVehiculo', '*');
$SQL_LineaVehiculo = Seleccionar('uvw_Sap_tbl_Articulos_LineaVehiculo', '*');
$SQL_TipoVehiculo = Seleccionar('uvw_Sap_tbl_Articulos_TipoVehiculo', '*');
$SQL_ServicioVehiculo = Seleccionar('uvw_Sap_tbl_Articulos_ServicioVehiculo', '*');
$SQL_TipoCarroceria = Seleccionar('uvw_Sap_tbl_Articulos_TipoCarroceriaVehiculo', '*');
$SQL_NumPuertas = Seleccionar('uvw_Sap_tbl_Articulos_NumPuertasVehiculo', '*');
$SQL_CapaPasajeros = Seleccionar('uvw_Sap_tbl_Articulos_CapaPasajerosVehiculo', '*');
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $Title; ?> | <?php echo NOMBRE_PORTAL; ?></title>
<?php
if (isset($_GET['a']) && ($_GET['a'] == base64_encode("OK_ArtUpd"))) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El ID de servicio ha sido actualizado exitosamente.',
                icon: 'success'
            });
		});
		</script>";
}
if (isset($sw_error) && ($sw_error == 1)) {
    echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Ha ocurrido un error!',
                text: '" . LSiqmlObs($msg_error) . "',
                icon: 'warning'
            });
		});
		</script>";
}
?>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.select2-container{
		width: 100% !important;
	}
	.ibox-title a{
		color: inherit !important;
	}
	.collapse-link:hover{
		cursor: pointer;
	}
</style>
<!-- InstanceEndEditable -->
</head>

<body <?php if ($sw_ext == 1) {echo "class='mini-navbar'";}?>>

<div id="wrapper">

    <?php if ($sw_ext != 1) {include "includes/menu.php";}?>

    <div id="page-wrapper" class="gray-bg">
        <?php if ($sw_ext != 1) {include "includes/menu_superior.php";}?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2><?php echo $Title; ?></h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Gestión de artículos</a>
                        </li>
                        <li class="active">
                            <strong><?php echo $Title; ?></strong>
                        </li>
                    </ol>
                </div>
            </div>

         <div class="wrapper wrapper-content">
			 <form action="articulos.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="FrmArticulo">
			 <div class="ibox-content">
				<?php include "includes/spinner.php";?>
			 	<div class="row">
					<div class="col-lg-12 form-horizontal">
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-plus-square"></i> Acciones</h3></label>
						</div>
						<div class="form-group">
							<div class="col-lg-12">
								<?php
$EliminaMsg = array("a=" . base64_encode("OK_ArtUpd"), "a=" . base64_encode("OK_ArtAdd"), "&&"); //Eliminar mensajes

if (isset($_REQUEST['return'])) {
    $_REQUEST['return'] = str_replace($EliminaMsg, "", base64_decode($_REQUEST['return']));
}
if (isset($_REQUEST['return'])) {
    $return = base64_decode($_REQUEST['pag']) . "?" . $_REQUEST['return'];
} else {
    $return = "consultar_articulos.php";
}
?>
								<?php
if ($edit == 1) {
    if (PermitirFuncion(1003)) {?>
										<button class="btn btn-warning" type="submit" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar</button>
								<?php }
} else {
    if (PermitirFuncion(1001)) {?>
										<button class="btn btn-primary" type="submit" id="Crear"><i class="fa fa-check"></i> Crear articulo</button>
								<?php }
}?>
								<?php if ($sw_ext == 0) {?>
									<a href="<?php echo $return; ?>" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
								<?php }?>
							</div>
						</div>
						<input type="hidden" id="P" name="P" value="MM_Art" />
						<input type="hidden" id="IdArticuloPortal" name="IdArticuloPortal" value="<?php if (isset($row['IdArticuloPortal'])) {echo base64_encode($row['IdArticuloPortal']);}?>" />
						<input type="hidden" id="ext" name="ext" value="<?php echo $sw_ext; ?>" />
						<input type="hidden" id="tl" name="tl" value="<?php echo $edit; ?>" />
						<input type="hidden" id="error" name="error" value="<?php echo $sw_error; ?>" />
						<input type="hidden" id="pag" name="pag" value="<?php if (isset($_REQUEST['pag'])) {echo $_REQUEST['pag'];} else {echo base64_encode("articulos.php");} //viene de afuera ?>" />
						<input type="hidden" id="return" name="return" value="<?php if (isset($_REQUEST['return'])) {echo base64_encode($_REQUEST['return']);} else {echo base64_encode($_SERVER['QUERY_STRING']);} //viene de afuera ?>" />
					</div>
				</div>
			 </div>
			 <br>
			 <div class="row">
					<div class="ibox-content">
						<?php include "includes/spinner.php";?>
						 <div class="tabs-container">
								<ul class="nav nav-tabs">
									<li class="active"><a data-toggle="tab" href="#tabSN-1"><i class="fa fa-info-circle"></i> Información general</a></li>
									<?php if ($edit == 1) {?><li><a data-toggle="tab" href="#tabSN-2"><i class="fa fa-database"></i> Datos de inventario</a></li><?php }?>
									<?php if ($edit == 1) {?><li><a data-toggle="tab" href="#tabSN-3" onClick="ConsultarTab('3');"><i class="fa fa-list-alt"></i> Lista de materiales</a></li><?php }?>
									<li><a data-toggle="tab" href="#tabSN-5"><i class="fa fa-paperclip"></i> Anexos</a></li>
								</ul>
							   <div class="tab-content">
								   <div id="tabSN-1" class="tab-pane active">
									   <div class="ibox">
											<div class="ibox-title bg-success">
												<h5 class="collapse-link"><i class="fa fa-info-circle"></i> Información principal</h5>
												 <a class="collapse-link pull-right">
													<i class="fa fa-chevron-up"></i>
												</a>
											</div>
											<div class="ibox-content">
												<div class="form-group">
													<label class="col-lg-1 control-label">Código</label>
													<div class="col-lg-3">
														<input name="ItemCode" autofocus="autofocus" type="text" required class="form-control" id="ItemCode" value="<?php if ($edit == 1) {echo $row['ItemCode'];}?>" <?php if ($edit == 1) {echo "readonly='readonly'";}?>>
													</div>
													<label class="col-lg-1 control-label">Artículo de inventario</label>
													<div class="col-lg-1">
														<label class="checkbox-inline i-checks"><input name="ArtInventario" id="ArtInventario" type="checkbox" value="Y" <?php if ($edit == 1) {if ($row['InvntItem'] == 'Y') {echo "checked=\"checked\"";}}?>></label>
													</div>
													<label class="col-lg-1 control-label">Artículo de venta</label>
													<div class="col-lg-1">
														<label class="checkbox-inline i-checks"><input name="ArtVenta" id="ArtVenta" type="checkbox" value="Y" <?php if ($edit == 1) {if ($row['SellItem'] == 'Y') {echo "checked=\"checked\"";}}?>></label>
													</div>
													<label class="col-lg-1 control-label">Artículo de compra</label>
													<div class="col-lg-1">
														<label class="checkbox-inline i-checks"><input name="ArtCompra" id="ArtCompra" type="checkbox" value="Y" <?php if ($edit == 1) {if ($row['PrchseItem'] == 'Y') {echo "checked=\"checked\"";}}?>></label>
													</div>
												</div>
												<div class="form-group">
													<label class="col-lg-1 control-label">Descripción</label>
													<div class="col-lg-3">
														<input type="text" class="form-control" name="ItemName" id="ItemName" required value="<?php if ($edit == 1) {echo $row['ItemName'];}?>">
													</div>
													<label class="col-lg-1 control-label">Referencia</label>
													<div class="col-lg-3">
														<input type="text" class="form-control" name="FrgnName" id="FrgnName" value="<?php if ($edit == 1) {echo $row['FrgnName'];}?>">
													</div>
													<label class="col-lg-1 control-label">Tipo artículo</label>
													<div class="col-lg-3">
														<select name="ItemType" class="form-control" id="ItemType" required>
														<?php
while ($row_TipoArticulo = sqlsrv_fetch_array($SQL_TipoArticulo)) {?>
																<option value="<?php echo $row_TipoArticulo['ItemType']; ?>" <?php if ((isset($row['ItemType'])) && (strcmp($row_TipoArticulo['ItemType'], $row['ItemType']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_TipoArticulo['DE_ItemType']; ?></option>
														<?php }?>
														</select>
													</div>
												</div>
												<div class="form-group">
													<label class="col-lg-1 control-label">Estado</label>
													<div class="col-lg-3">
														<select name="EstadoArticulo" class="form-control" id="EstadoArticulo" required>
														<?php
while ($row_EstadoArticulo = sqlsrv_fetch_array($SQL_EstadoArticulo)) {?>
																<option value="<?php echo $row_EstadoArticulo['Cod_Estado']; ?>" <?php if ((isset($row['Estado'])) && (strcmp($row_EstadoArticulo['Cod_Estado'], $row['Estado']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_EstadoArticulo['NombreEstado']; ?></option>
														<?php }?>
														</select>
													</div>
													<label class="col-lg-1 control-label">Unidad de medida</label>
													<div class="col-lg-3">
														<input type="text" class="form-control" name="UnidadMedInv" id="UnidadMedInv" value="<?php if ($edit == 1) {echo $row['InvntryUom'];}?>">
													</div>
													<label class="col-lg-1 control-label">Grupo</label>
													<div class="col-lg-3">
														<select name="GroupCode" class="form-control select2" id="GroupCode" required>
															<option value="">Seleccione...</option>
														<?php
while ($row_GruposArticulos = sqlsrv_fetch_array($SQL_GruposArticulos)) {?>
																<option value="<?php echo $row_GruposArticulos['ItmsGrpCod'] . "__" . $row_GruposArticulos['ItmsGrpNam']; ?>" <?php if ((isset($row['ItmsGrpCod'])) && (strcmp($row_GruposArticulos['ItmsGrpCod'], $row['ItmsGrpCod']) == 0)) {echo "selected=\"selected\"";}?>><?php echo $row_GruposArticulos['ItmsGrpNam']; ?></option>
														<?php }?>
														</select>
													</div>
												</div>
												
												<div class="form-group">
													<!-- SMM -->
													<label class="col-lg-1 control-label">Ubicación Física</label>
													<div class="col-lg-3">
														<input type="text" class="form-control" name="CDU_UbicacionFisica" id="CDU_UbicacionFisica" value="<?php if ($edit == 1) {echo $row['CDU_UbicacionFisica'] ?? "";}?>" readonly>
													</div>
													<!-- 03/03/2022 -->
													<!-- SMM -->
													<label class="col-lg-1 control-label">Código de proveedor</label>
													<div class="col-lg-3">
														<input type="text" class="form-control" name="SuppCatNum" id="SuppCatNum" value="<?php if ($edit == 1) {echo $row['SuppCatNum'];}?>" readonly>
													</div>
													<!-- 08/03/2022 -->
												</div>
											</div>
									   </div>
									   <div class="ibox">
										<div class="ibox-title bg-success">
											<h5 class="collapse-link"><i class="fa fa-money"></i> Lista de precios</h5>
											 <a class="collapse-link pull-right">
												<i class="fa fa-chevron-up"></i>
											</a>
										</div>
										<div class="ibox-content">
											<div class="form-group">
												<div class="table-responsive">
													<table class="table table-bordered table-hover" >
													<thead>
													<tr>
														<th>Lista de precio</th>
														<th>Precio por unidad</th>
														<th>Tarifa de impuesto</th>
														<th>Valor del impuesto</th>
														<th>Precio con impuesto</th>
													</tr>
													</thead>
													<tbody>
													<?php
while ($row_ListaPrecio = sqlsrv_fetch_array($SQL_ListaPrecio)) {?>
															<tr>
																<td><?php echo $row_ListaPrecio['ListName']; ?></td>
																<td><?php echo number_format($row_ListaPrecio['Price'], 2); ?></td>
																<td><?php echo number_format($row_ListaPrecio['TarifaIVA'], 2); ?></td>
																<td><?php echo number_format($row_ListaPrecio['VatSum'], 2); ?></td>
																<td><?php echo number_format($row_ListaPrecio['PriceTax'], 2); ?></td>
															</tr>
													<?php }?>
													</tbody>
													</table>
												</div>
											</div>
										</div>
								   </div>
								   <!-- INICIO, información del vehículo -->
<div class="ibox">
	<div class="ibox-title bg-success">
		<h5 class="collapse-link"><i class="fa fa-info-circle"></i> Información base del vehículo</h5>
			<a class="collapse-link pull-right">
			<i class="fa fa-chevron-up"></i>
		</a>
	</div>
	<div class="ibox-content">
		<div class="form-group">
			<div class="col-lg-4">
				<label class="control-label">Marca del vehículo <span class="text-danger">*</span></label>
				<select name="CDU_IdMarca" class="form-control select2" required="required" id="CDU_IdMarca">
					<option value="" disabled selected disabled selected>Seleccione...</option>
					<?php while ($row_MarcaVehiculo = sqlsrv_fetch_array($SQL_MarcaVehiculo)) {?>
					<option value="<?php echo $row_MarcaVehiculo['IdMarcaVehiculo']; ?>"
					<?php if ((isset($row['CDU_IdMarca'])) && (strcmp($row_MarcaVehiculo['IdMarcaVehiculo'], $row['CDU_IdMarca']) == 0)) {echo "selected=\"selected\"";}?>>
						<?php echo $row_MarcaVehiculo['DeMarcaVehiculo']; ?>
					</option>
					<?php }?>
				</select>
			</div>
			<div class="col-lg-4">
				<label class="control-label">Línea del vehículo <span class="text-danger">*</span></label>
				<select name="CDU_Linea" class="form-control select2" required="required" id="CDU_Linea">
						<option value="" disabled selected>Seleccione...</option>
					<?php while ($row_LineaVehiculo = sqlsrv_fetch_array($SQL_LineaVehiculo)) {?>
						<option value="<?php echo $row_LineaVehiculo['LineaModeloVehiculo']; //['Codigo'];    ?>"
						<?php if ((isset($row['CDU_Linea'])) && (strcmp($row_LineaVehiculo['LineaModeloVehiculo'], $row['CDU_Linea']) == 0)) {echo "selected=\"selected\"";}?>>
							<?php echo $row_LineaVehiculo['LineaModeloVehiculo']; //. " - " . $row_LineaVehiculo['MarcaVehiculo'];    ?>
						</option>
					<?php }?>
				</select>
			</div>
			<div class="col-lg-4">
				<label class="control-label">Tipo de vehículo <span class="text-danger">*</span></label>
				<select name="CDU_TipoVehiculo" class="form-control select2" required="required" id="CDU_TipoVehiculo">
						<option value="" disabled selected>Seleccione...</option>
					<?php while ($row_TipoVehiculo = sqlsrv_fetch_array($SQL_TipoVehiculo)) {?>
						<option value="<?php echo $row_TipoVehiculo['CodigoTipoVehiculo']; ?>"
						<?php if ((isset($row['CDU_TipoVehiculo'])) && (strcmp($row_TipoVehiculo['CodigoTipoVehiculo'], $row['CDU_TipoVehiculo']) == 0)) {echo "selected=\"selected\"";}?>>
							<?php echo $row_TipoVehiculo['NombreTipoVehiculo']; ?>
						</option>
					<?php }?>
				</select>
			</div>
		</div>
		<div class="form-group">
		<div class="col-lg-4">
				<label class="control-label">Servicio vehículo <span class="text-danger">*</span></label>
				<select name="CDU_ServicioVehiculo" class="form-control select2" required="required" id="CDU_ServicioVehiculo">
						<option value="" disabled selected>Seleccione...</option>
					<?php while ($row_ServicioVehiculo = sqlsrv_fetch_array($SQL_ServicioVehiculo)) {?>
						<option value="<?php echo $row_ServicioVehiculo['CodigoServicioVehiculo']; ?>"
						<?php if (isset($row['CDU_ServicioVehiculo']) && (strcmp($row_ServicioVehiculo['CodigoServicioVehiculo'], $row['CDU_ServicioVehiculo']) == 0)) {echo "selected=\"selected\"";}?>>
							<?php echo $row_ServicioVehiculo['NombreServicioVehiculo']; ?>
						</option>
					<?php }?>
				</select>
			</div>
			<div class="col-lg-4">
				<label class="control-label">Tipo de carrocería <span class="text-danger">*</span></label>
				<select name="CDU_TipoCarroceria" class="form-control select2" required="required" id="CDU_TipoCarroceria">
						<option value="" disabled selected>Seleccione...</option>
					<?php while ($row_TipoCarroceriaVehiculo = sqlsrv_fetch_array($SQL_TipoCarroceria)) {?>
						<option value="<?php echo $row_TipoCarroceriaVehiculo['CodigoTipoCarroceriaVehiculo']; ?>"
						<?php if (isset($row['CDU_TipoCarroceria']) && (strcmp($row_TipoCarroceriaVehiculo['CodigoTipoCarroceriaVehiculo'], $row['CDU_TipoCarroceria']) == 0)) {echo "selected=\"selected\"";}?>>
							<?php echo $row_TipoCarroceriaVehiculo['NombreTipoCarroceriaVehiculo']; ?>
						</option>
					<?php }?>
				</select>
			</div>
			<div class="col-lg-4">
				<label class="control-label">Número de puertas <span class="text-danger">*</span></label>
				<select name="CDU_NumPuertas" class="form-control select2" required="required" id="CDU_NumPuertas">
						<option value="" disabled selected>Seleccione...</option>
					<?php while ($row_NumPuertas = sqlsrv_fetch_array($SQL_NumPuertas)) {?>
						<option value="<?php echo $row_NumPuertas['CodigoNumPuertasVehiculo']; ?>"
						<?php if (isset($row['CDU_NumPuertas']) && (strcmp($row_NumPuertas['CodigoNumPuertasVehiculo'], $row['CDU_NumPuertas']) == 0)) {echo "selected=\"selected\"";}?>>
							<?php echo $row_NumPuertas['NombreNumPuertasVehiculo']; ?>
						</option>
					<?php }?>
				</select>
			</div>
		</div>
		<div class="form-group">
			<div class="col-lg-4">
				<label class="control-label">Capacidad de pasajeros <span class="text-danger">*</span></label>
				<select name="CDU_CapaPasajeros" class="form-control select2" required="required" id="CDU_CapaPasajeros">
						<option value="" disabled selected>Seleccione...</option>
					<?php while ($row_CapaPasajeros = sqlsrv_fetch_array($SQL_CapaPasajeros)) {?>
						<option value="<?php echo $row_CapaPasajeros['CodigoCapPasajerosVehiculo']; ?>"
						<?php if (isset($row['CDU_CapaPasajeros']) && (strcmp($row_CapaPasajeros['CodigoCapPasajerosVehiculo'], $row['CDU_CapaPasajeros']) == 0)) {echo "selected=\"selected\"";}?>>
							<?php echo $row_CapaPasajeros['NombreCapPasajerosVehiculo']; ?>
						</option>
					<?php }?>
				</select>
			</div>
		</div>
	</div>
</div>
<!-- FIN, información del vehículo -->

</div>
<?php if ($edit == 1) {?>
<div id="tabSN-2" class="tab-pane">
<div class="panel-body">
<div class="form-group">
<div class="col-lg-12">
	<div class="table-responsive">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th>Código almacén</th>
			<th>Nombre almacén</th>
			<th>Stock</th>
			<th>Comprometido</th>
			<th>Pedido</th>
			<th>Disponible</th>
			<th>Costo del artículo</th>
		</tr>
		</thead>
		<tbody>
		<?php while ($row_DtInvent = sqlsrv_fetch_array($SQL_DtInvent)) {?>
				<tr style="<?php if ($row_DtInvent['OnHand'] > 0) {
echo "background-color: #1ab394; color:white;";
}
?>">
				<td><?php echo $row_DtInvent['WhsCode']; ?></td>
				<td><?php echo $row_DtInvent['WhsName']; ?></td>
				<td>
					<?php echo number_format($row_DtInvent['OnHand'], 2); ?>
				</td>
				<td><?php echo number_format($row_DtInvent['Comprometido'], 2); ?></td>
				<td><?php echo number_format($row_DtInvent['Pedido'], 2); ?></td>
				<td><?php echo number_format($row_DtInvent['Disponible'], 2); ?></td>
				<td>
<?php
if (PermitirFuncion(1004)) {
echo "$" . number_format($row_DtInvent['CostoArticulo'], 2);
} else {
echo "*****";
}
?>
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
<?php }?>
<?php if ($edit == 1) {?>
<div id="tabSN-3" class="tab-pane">
<div id="dv_ListaMateriales" class="panel-body">

</div>
</div>
<?php }?>
</form>
<div id="tabSN-5" class="tab-pane">
<div class="panel-body">
<?php if ($edit == 1) {
if ($row['IdAnexoArticulo'] != 0) {?>
	<div class="form-group">
		<div class="col-xs-12">
			<?php while ($row_AnexoArticulos = sqlsrv_fetch_array($SQL_AnexoArticulos)) {
$Icon = IconAttach($row_AnexoArticulos['FileExt']);?>
				<div class="file-box">
					<div class="file">
						<a href="attachdownload.php?file=<?php echo base64_encode($row_AnexoArticulos['AbsEntry']); ?>&line=<?php echo base64_encode($row_AnexoArticulos['Line']); ?>" target="_blank">
							<div class="icon">
								<i class="<?php echo $Icon; ?>"></i>
							</div>
							<div class="file-name">
								<?php echo $row_AnexoArticulos['NombreArchivo']; ?>
								<br/>
								<small><?php echo $row_AnexoArticulos['Fecha']; ?></small>
							</div>
						</a>
					</div>
				</div>
			<?php }?>
		</div>
	</div>
<?php } else {echo "<p>Sin anexos.</p>";}
}?>
											<?php if (($edit == 0) || (($edit == 1) && ($row['Estado'] != 'N') && (PermitirFuncion(1003)))) {?>
											<div class="row">
												<form action="upload.php" class="dropzone" id="dropzoneForm" name="dropzoneForm">
													<?php if ($sw_error == 0) {LimpiarDirTemp();}?>
													<div class="fallback">
														<input name="File" id="File" type="file" form="dropzoneForm" />
													</div>
												 </form>
											</div>
											<?php }?>
										</div>
								   </div>
							   </div>
						   </div>
					</div>
			 </div>

        </div>
        <!-- InstanceEndEditable -->
        <?php include "includes/footer.php";?>

    </div>
</div>

<?php include "includes/pie.php";?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>
 $(document).ready(function(){
	 $("#FrmArticulo").validate({
		 submitHandler: function(form){
			 $('.ibox-content').toggleClass('sk-loading');
			 form.submit();
		}
	});
   $(".alkin").on('click', function(){
	   $('.ibox-content').toggleClass('sk-loading');
	});

	 $('.i-checks').iCheck({
		 checkboxClass: 'icheckbox_square-green',
		 radioClass: 'iradio_square-green',
	  });

	$(".select2").select2();

	$('.footable').footable();

 });
</script>
<script>
//Variables de tab
 var tab_3=0;

 function ConsultarTab(type){
	if(type==3){//Lista de materiales
		if(tab_3==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "ar_lista_materiales.php?id=<?php if ($edit == 1) {echo base64_encode($IdItemCode);}?>",
				success: function(response){
					$('#dv_ListaMateriales').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_3=1;
				}
			});
		}
	}
}
</script>
<script>
 Dropzone.options.dropzoneForm = {
		paramName: "File", // The name that will be used to transfer the file
		maxFilesize: "<?php echo ObtenerVariable("MaxSizeFile"); ?>", // MB
	 	maxFiles: "<?php echo ObtenerVariable("CantidadArchivos"); ?>",
		uploadMultiple: true,
		addRemoveLinks: true,
		dictRemoveFile: "Quitar",
	 	acceptedFiles: "<?php echo ObtenerVariable("TiposArchivos"); ?>",
		dictDefaultMessage: "<strong>Haga clic aqui para cargar anexos</strong><br>Tambien puede arrastrarlos hasta aqui<br><h4><small>(máximo <?php echo ObtenerVariable("CantidadArchivos"); ?> archivos a la vez)<small></h4>",
		dictFallbackMessage: "Tu navegador no soporta cargue de archivos mediante arrastrar y soltar",
	 	removedfile: function(file) {
		  $.get( "includes/procedimientos.php", {
			type: "3",
		  	nombre: file.name
		  }).done(function( data ) {
		 	var _ref;
		  	return (_ref = file.previewElement) !== null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
		 	});
		 }
	};
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>