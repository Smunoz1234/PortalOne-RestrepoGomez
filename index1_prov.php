<?php require_once("includes/conexion.php"); 
//setlocale(LC_TIME, "spanish");
//require_once("includes/conexion_hn.php");

$fecha = date('Y-m-d');
$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasDocSAP").' day');
$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
$FechaInicial=$nuevafecha;

$FechaFinal=date('Y-m-d');

//Proveedor
$SQL_Prov=Seleccionar('uvw_Sap_tbl_Proveedores','CodigoCliente, NombreCliente, LicTradNum',"CodigoCliente='".$_SESSION['CodigoSAPProv']."'");
$row_Prov=sqlsrv_fetch_array($SQL_Prov);

//Ordenes de compra abiertas
$SQL_OC=Seleccionar('uvw_Sap_tbl_OrdenesCompras','Count(ID_OrdenCompra) as Count',"CardCode='".$_SESSION['CodigoSAPProv']."' And Cod_Estado='O'");
$row_OC=sqlsrv_fetch_array($SQL_OC);

//Entradas de compra
$SQL_Ent=Seleccionar('uvw_Sap_tbl_EntradasCompras','Count(ID_EntradaCompra) as Count',"CardCode='".$_SESSION['CodigoSAPProv']."' And (DocDate Between '$FechaInicial' and '$FechaFinal')");
$row_Ent=sqlsrv_fetch_array($SQL_Ent);

//Facturas de compra
$SQL_Fact=Seleccionar('uvw_Sap_tbl_FacturasCompras','Count(ID_FacturaCompra) as Count',"CardCode='".$_SESSION['CodigoSAPProv']."' And (DocDate Between '$FechaInicial' and '$FechaFinal')");
$row_Fact=sqlsrv_fetch_array($SQL_Fact);

//Pagos efectuados
$SQL_Pago=Seleccionar('uvw_Sap_tbl_Pagos_Efectuados','Count(CardCode) as Count',"CardCode='".$_SESSION['CodigoSAPProv']."' And (FechaPago Between '".FormatoFecha($FechaInicial)."' and '".FormatoFecha($FechaFinal)."')");
$row_Pago=sqlsrv_fetch_array($SQL_Pago);


?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Inicio | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	#animar{
		animation-duration: 1.5s;
  		animation-name: tada;
  		animation-iteration-count: infinite;
	}
	#animar2{
		animation-duration: 1s;
  		animation-name: swing;
  		animation-iteration-count: infinite;
	}
	#animar3{
		animation-duration: 3s;
  		animation-name: pulse;
  		animation-iteration-count: infinite;
	}
	.edit1 {/*Widget editado por aordonez*/
		border-radius: 0px !important; 
		padding: 15px 20px;
		margin-bottom: 10px;
		margin-top: 10px;
		height: 120px !important;
	}
	.modal-lg {
		width: 50% !important;
	}
	h1{
		font-size: 36px !important;
	}
</style>

<script>
$(document).ready(function(){
	<?php if(!isset($_SESSION['SetCookie'])||($_SESSION['SetCookie']=="")){?>
		$('#myModal').modal("show");
	<?php }?>
	
});
</script>
<!-- InstanceEndEditable -->
</head>

<body class="mini-navbar">

<div id="wrapper">

    <?php include_once("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
      
        <?php 
		$Nombre_archivo="contrato_confidencialidad.txt";
		$Archivo=fopen($Nombre_archivo,"r");
		$Contenido = fread($Archivo, filesize($Nombre_archivo));
		?>
        <div class="modal inmodal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" data-show="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Acuerdo de confidencialidad</h4>
						<small>Por favor lea atentamente este contrato que contiene los T&eacute;rminos y Condiciones de uso de este sitio. Si continua usando este portal, consideramos que usted est&aacute; de acuerdo con ellos.</small>
					</div>
					<div class="modal-body">
						<?php echo $Contenido;?>
					</div>

					<div class="modal-footer">
						<button type="button" onClick="AceptarAcuerdo();" class="btn btn-primary" data-dismiss="modal">Acepto los t&eacute;rminos</button>
					</div>
				</div>
			</div>
		</div>
        <div class="row page-wrapper wrapper-content animated fadeInRight">
		  <div class="row m-b-md">
			<div class="col-lg-12">
				<div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <h2>Bienvenido <strong><?php echo $row_Prov['NombreCliente'];?></strong></h2>
				</div>
			</div>
		  </div>
          <div class="row">
            <div class="col-lg-3">
              <div class="ibox ">
                <div class="ibox-title">
                  <h5>Ordenes de compra abiertas</h5>
                </div>
                <div class="ibox-content">
					<i class="fa fa-shopping-cart fa-5x"></i>
                  	<h1 class="no-margins pull-right"><?php echo number_format($row_OC['Count'],0);?></h1>
				</div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="ibox ">
                <div class="ibox-title"> <span class="label label-success pull-right">Última semana</span>
					<h5>Entradas de mercancías/servicio</h5>
                </div>
                <div class="ibox-content">
					<i class="fa fa-truck fa-5x"></i>
					<h1 class="no-margins pull-right"><?php echo number_format($row_Ent['Count'],0);?></h1>
				</div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="ibox ">
                <div class="ibox-title"> <span class="label label-success pull-right">Última semana</span>
                  <h5>Nuevas facturas recibidas</h5>
                </div>
                <div class="ibox-content">
					<i class="fa fa-briefcase fa-5x"></i>
					<h1 class="no-margins pull-right"><?php echo number_format($row_Fact['Count'],0);?></h1>
				</div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="ibox ">
                <div class="ibox-title"> <span class="label label-success pull-right">Última semana</span>
                  <h5>Nuevos pagos realizados</h5>
                </div>
                <div class="ibox-content">
					<i class="fa fa-money fa-5x"></i>
					<h1 class="no-margins pull-right"><?php echo number_format($row_Pago['Count'],0);?></h1>
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
<script>	
	 $(document).ready(function(){
		 $('.navy-bg').each(function() {
                animationHover(this, 'pulse');
            });
		  $('.yellow-bg').each(function() {
                animationHover(this, 'pulse');
            });
		 $('.lazur-bg').each(function() {
                animationHover(this, 'pulse');
            });
		 $(".truncate").dotdotdot({
            watch: 'window'
		  });
	});
</script>

<?php if(isset($_GET['dt'])&&$_GET['dt']==base64_encode("result")){?>
<script>
	$(document).ready(function(){
		toastr.options = {
			closeButton: true,
			progressBar: true,
			showMethod: 'slideDown',
			timeOut: 6000
		};
		toastr.success('¡Su contraseña ha sido modificada!', 'Felicidades');
	});
</script>
<?php }?>
<script src="js/js_setcookie.js"></script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>