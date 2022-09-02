<?php require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
PermitirAcceso(801);
$sw=0;//Para saber si ya se selecciono un cliente y mostrar la información
$msg_error="";//Mensaje del error

if(isset($_POST['swError'])&&($_POST['swError']!="")){//Para saber si ha ocurrido un error.
	$sw_error=$_POST['swError'];
}else{
	$sw_error=0;
}

//Clientes
/*if(PermitirFuncion(205)){
	$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes","CodigoCliente, NombreCliente","",'NombreCliente',"ASC");	
}else{
	$Where="ID_Usuario = ".$_SESSION['CodUser'];
	$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where,"NombreCliente");	
}*/

if(isset($_POST['P'])&&($_POST['P']!="")){//Grabar gestion	
	try{
		if($_POST['FechaCompPago']!=""){
			$_POST['FechaCompPago']="'".$_POST['FechaCompPago']."'";
		}else{
			$_POST['FechaCompPago']="NULL";
		}
		
		//Si hay acuerdo de pago
		if(isset($_POST['chkRegAcuerdo'])&&($_POST['chkRegAcuerdo']==1)){
			$chkRegAcuerdo=1;
		}else{
			$chkRegAcuerdo=0;
		}
		
		//Si hay liquidacion de intereses
		if(isset($_POST['chkLiqIntereses'])&&($_POST['chkLiqIntereses']==1)){
			$chkLiqIntereses=1;
		}else{
			$chkLiqIntereses=0;
		}
		
		$ParametrosInsGestion=array(
			"NULL",
			"'".base64_decode($_POST['CardCode'])."'",
			"'".$_POST['TipoGestion']."'",
			"'".$_POST['Destino']."'",
			"'".$_POST['Evento']."'",
			"'".$_POST['Dirigido']."'",
			"'".$_POST['ResultadoGestion']."'",
			$_POST['FechaCompPago'],
			"'".LSiqmlObs($_POST['Comentarios'])."'",
			"'".$_POST['CausaNoPago']."'",
			"'".$chkLiqIntereses."'",
			"'".$chkRegAcuerdo."'",
			"'".base64_decode($_POST['cllName'])."'",
			"1",
			"'".$_SESSION['CodUser']."'",
			"1"			
		);
		$SQL_InsGestion=EjecutarSP('sp_tbl_Cartera_Gestion',$ParametrosInsGestion,43);
		if($SQL_InsGestion){
			$row_NewIdGestion=sqlsrv_fetch_array($SQL_InsGestion);
			
			//Si hay liquidacion de intereses
			if($chkLiqIntereses==1){
				$ParametrosInsLiq=array(
					"NULL",
					"'".$row_NewIdGestion[0]."'",
					"'".base64_decode($_POST['CardCode'])."'",
					"'".$_POST['FechaLiquidacion']."'",
					"'".LSiqmlValorDecimal($_POST['TotalSaldoLiqInt'])."'",
					"'".LSiqmlValorDecimal($_POST['InteresesMoraLiqInt'])."'",
					"'".LSiqmlValorDecimal($_POST['RetiroAnticipadoLiqInt'])."'",
					"'".LSiqmlValorDecimal($_POST['GastosCobranzaLiqInt'])."'",
					"'".LSiqmlValorDecimal($_POST['CobroPrejuridicoLiqInt'])."'",
					"'".LSiqmlValorDecimal($_POST['TotalLiquidadoLiqInt'])."'",
					"'".LSiqmlValorDecimal($_POST['DescuentoLiqInt'])."'",
					"'".LSiqmlValorDecimal($_POST['TotalPagarLiqInt'])."'",
					"1",
					"'".$_SESSION['CodUser']."'",
					"1"
				);
				$SQL_InsLiqInt=EjecutarSP('sp_tbl_Cartera_LiquidacionIntereses',$ParametrosInsLiq,43);
				if($SQL_InsLiqInt){
					$row_NewIdLiqInt=sqlsrv_fetch_array($SQL_InsLiqInt);
					
					//Enviar datos al WebServices - Liquidacion de intereses
					/*try{
						require_once("includes/conect_ws.php");
						$Parametros=array(
							'pIdLiqInteres' => $row_NewIdLiqInt[0],
							'pLogin'=>$_SESSION['User']
						);
						$Client->InsertarLiquidaInteresPortal($Parametros);
					}catch (Exception $e) {
						echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
					}*/
					
					//Insertar tabla de intereses de facturas
					if(isset($_POST['chkCobIntLiqInt'])&&($_POST['chkCobIntLiqInt']==1)){//Traer la tabla de facturas vencidas con o sin intereses
						if(isset($_POST['chkVerFactNoVencLiqInt'])&&($_POST['chkVerFactNoVencLiqInt']==1)){
							$Param=array("'".base64_decode($_POST['CardCode'])."'",1,1);
						}
						else{
							$Param=array("'".base64_decode($_POST['CardCode'])."'",1,0);
						}						
					}else{
						if(isset($_POST['chkVerFactNoVencLiqInt'])&&($_POST['chkVerFactNoVencLiqInt']==1)){
							$Param=array("'".base64_decode($_POST['CardCode'])."'",0,1);
						}else{
							$Param=array("'".base64_decode($_POST['CardCode'])."'",0,0);
						}
					}
					$SQL_FactPend=EjecutarSP('sp_CalcularIntMoraFactVencida',$Param);
					while($row_FactPend=sqlsrv_fetch_array($SQL_FactPend)){
						$ParametrosInsFacVenc=array(
							"NULL",
							"'".$row_NewIdLiqInt[0]."'",
							"'".$row_NewIdGestion[0]."'",
							"'".base64_decode($_POST['CardCode'])."'",
							"'".$row_FactPend['NoDocumento']."'",
							"'".$row_FactPend['FechaVencimiento']->format('Y-m-d')."'",
							"'".$row_FactPend['DiasVencidos']."'",
							"'".$row_FactPend['SaldoDocumento']."'",
							"'".$row_FactPend['InteresesMora']."'",
							"'".$row_FactPend['TotalPagar']."'",							
							"'".$_SESSION['CodUser']."'",
							"1"
						);
						$SQL_InsFacVenc=EjecutarSP('sp_tbl_Cartera_FactVencLiqIntereses',$ParametrosInsFacVenc,43);						
					}			
				}else{
					throw new Exception('Ha ocurrido un error al insertar la liquidacion de intereses');
					sqlsrv_close($conexion);
					exit();
				}
			}
			
			//Si hay acuerdo de pago
			if($chkRegAcuerdo==1){
				$ParametrosInsAcu=array(
					"NULL",
					"'".$row_NewIdGestion[0]."'",
					"'".base64_decode($_POST['CardCode'])."'",
					"'".$_POST['TipoConvenio']."'",
					"'".$_POST['FechaAcuerdo']."'",
					"'".LSiqmlValorDecimal($_POST['TotalSaldo'])."'",
					"'".LSiqmlValorDecimal($_POST['InteresesMora'])."'",
					"'".LSiqmlValorDecimal($_POST['RetiroAnticipado'])."'",					
					"'".LSiqmlValorDecimal($_POST['GastosCobranza'])."'",
					"'".LSiqmlValorDecimal($_POST['CobroPrejuridico'])."'",
					"'".LSiqmlValorDecimal($_POST['TotalLiquidado'])."'",
					"'".LSiqmlValorDecimal($_POST['Descuento'])."'",
					"'".LSiqmlValorDecimal($_POST['TotalPagar'])."'",
					"'".LSiqmlValorDecimal($_POST['AbonoInicial'])."'",
					"'".LSiqmlValorDecimal($_POST['SaldoDiferir'])."'",
					"'".$_POST['Cuotas']."'",
					"1",
					"'".$_SESSION['CodUser']."'",
					"1"
				);				
				$SQL_InsAcuerdo=EjecutarSP('sp_tbl_Cartera_AcuerdosDePago',$ParametrosInsAcu,43);
				if($SQL_InsAcuerdo){
					$row_NewIdAcuerdo=sqlsrv_fetch_array($SQL_InsAcuerdo);
					
					//Enviar datos al WebServices - Acuerdo de pago
					/*try{
						require_once("includes/conect_ws.php");
						$Parametros=array(
							'pIdAcpago' => $row_NewIdAcuerdo[0],
							'pLogin'=>$_SESSION['User']
						);
						$Client->InsertarAcuerdoPagoPortal($Parametros);
					}catch (Exception $e) {
						echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
					}*/
					
					//Insertar tabla de intereses de facturas
					if(isset($_POST['chkCobInt'])&&($_POST['chkCobInt']==1)){//Traer la tabla de facturas vencidas con o sin intereses
						if(isset($_POST['chkVerFactNoVenc'])&&($_POST['chkVerFactNoVenc']==1)){
							$Param=array("'".base64_decode($_POST['CardCode'])."'",1,1);
						}else{
							$Param=array("'".base64_decode($_POST['CardCode'])."'",1,0);
						}
					}else{
						if(isset($_POST['chkVerFactNoVenc'])&&($_POST['chkVerFactNoVenc']==1)){
							$Param=array("'".base64_decode($_POST['CardCode'])."'",0,1);
						}else{
							$Param=array("'".base64_decode($_POST['CardCode'])."'",0,0);
						}
					}
					$SQL_FactPend=EjecutarSP('sp_CalcularIntMoraFactVencida',$Param);
					while($row_FactPend=sqlsrv_fetch_array($SQL_FactPend)){
						$ParametrosInsFacVenc=array(
							"NULL",
							"'".$row_NewIdAcuerdo[0]."'",
							"'".$row_NewIdGestion[0]."'",
							"'".base64_decode($_POST['CardCode'])."'",
							"'".$row_FactPend['NoDocumento']."'",
							"'".$row_FactPend['FechaVencimiento']->format('Y-m-d')."'",
							"'".$row_FactPend['DiasVencidos']."'",
							"'".$row_FactPend['SaldoDocumento']."'",
							"'".$row_FactPend['InteresesMora']."'",
							"'".$row_FactPend['TotalPagar']."'",							
							"'".$_SESSION['CodUser']."'",
							"1"
						);
						$SQL_InsFacVenc=EjecutarSP('sp_tbl_Cartera_FactVencAcuerdos',$ParametrosInsFacVenc,43);						
					}
					
					//Enviar datos al WebServices - Intereses facturas
					/*try{
						require_once("includes/conect_ws.php");
						$Parametros=array(
							'pIdAcpago' => $row_NewIdAcuerdo[0],
							'pLogin'=>$_SESSION['User']
						);
						$Client->InsertarAcuerdoPagoFvaPortal($Parametros);
					}catch (Exception $e) {
						echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
					}*/
					
					//Si hay mas de una cuota
					if($_POST['Cuotas']>=1){
						$Array=CalcularCuotasAcuerdo($_POST['FechaAcuerdo'],$_POST['Cuotas'],LSiqmlValorDecimal($_POST['SaldoDiferir']));
						$j=1;
						for($i=0;$i<$_POST['Cuotas'];$i++){
							$ParametrosInsCuota=array(
								"NULL",
								"'".$row_NewIdAcuerdo[0]."'",
								"'".$row_NewIdGestion[0]."'",
								"'".base64_decode($_POST['CardCode'])."'",
								"'".$Array[$j][1]."'",
								"'".$Array[$j][2]."'",
								"'".$Array[$j][3]."'",
								"'".$_SESSION['CodUser']."'",
								"1"
							);	
							$SQL_InsCuota=EjecutarSP('sp_tbl_Cartera_CuotasAcuerdos',$ParametrosInsCuota,43);
							$j++;
						}
						//Enviar datos al WebServices - Cuotas acuerdos
						/*try{
							require_once("includes/conect_ws.php");
							$Parametros=array(
								'pIdAcpago' => $row_NewIdAcuerdo[0],
								'pLogin'=>$_SESSION['User']
							);
							$Client->InsertarAcuerdoPagoCuotasPortal($Parametros);
						}catch (Exception $e) {
							echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
						}*/
					}				
				}else{
					$sw_error=1;
					$msg_error="Ha ocurrido un error al crear la orden de venta";
				}
			}
			
			//Enviar datos al WebServices
			/*try{
				require_once("includes/conect_ws.php");
				$Parametros=array(
					'pIdGestion' => $row_NewIdGestion[0],
					'pLogin'=>$_SESSION['User']
				);
				$Client->InsertarGescarPortal($Parametros);
			}catch (Exception $e) {
				echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
			}*/
			sqlsrv_close($conexion);
			header('Location:gestionar_cartera.php?Clt='.$_POST['CardCode'].'&a='.base64_encode("OK_GtnCtr"));
		}else{
			$sw_error=1;
			$msg_error="Ha ocurrido un error al crear la orden de venta";
		}
	}catch (Exception $e){
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
	
}

if((isset($_POST['Cliente'])&&($_POST['Cliente'])!="")||(isset($_GET['Clt'])&&($_GET['Clt'])!="")){
	if(isset($_POST['Cliente'])){
		$Cliente=$_POST['Cliente'];
	}else{
		$Cliente=base64_decode($_GET['Clt']);
	}
	$sw=1;
	
	//Cliente
	$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes",'"CodigoCliente", "NombreCliente", "LicTradNum", "DeEstadoServicioCliente"',"[CodigoCliente]='".$Cliente."'");	
	$row_Cliente=sql_fetch_array($SQL_Cliente);
	
	//Archivo de grabacion (parte final)
	$FileCall=$_SESSION['Ext']."_".$Cliente."_".date('Ymd')."_".date("His").".WAV";
	
	//Tipo de gestion
	$SQL_TipoGestion=Seleccionar('uvw_tbl_Cartera_TipoGestion','*');
	
	//Dirigido a
	$SQL_Dirigido=Seleccionar('uvw_tbl_Cartera_Dirigido','*');
	
	//Causa de no pago
	$SQL_CausaNoPago=Seleccionar('uvw_tbl_Cartera_CausaNoPago','*','','CausaNoPago');
	
	//Pagos realizados
	//$SQL_PagosRealizados=Seleccionar('uvw_Sap_tbl_Pagos_Recibidos','*',"CardCode='".$Cliente."'",'DocDate');
	
	//Facturas pendientes
	$SQL_FacturasPendientes=Seleccionar('uvw_Sap_tbl_FacturasPendientes','*',"ID_CodigoCliente='".$Cliente."'",'FechaContabilizacion');
	
	//Historico de gestiones
	$SQL_HistGestion=Seleccionar('uvw_tbl_Cartera_Gestion','*',"CardCode='".$Cliente."'",'FechaRegistro','Desc');
	
	//Historico de acuerdos
	$SQL_HistAcuerdos=Seleccionar('uvw_tbl_Cartera_AcuerdosDePago','*',"CardCode='".$Cliente."'",'FechaRegistro','Desc');
	
	//Historico de liquidaciones
	$SQL_LiqIntereses=Seleccionar('uvw_tbl_Cartera_LiquidacionIntereses','*',"CardCode='".$Cliente."'",'FechaRegistro','Desc');
	
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Gestionar cartera | <?php echo NOMBRE_PORTAL;?></title>
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
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Ha ocurrido un error!',
                text: '".LSiqmlObs($msg_error)."',
                icon: 'warning'
            });
		});		
		</script>";
}
?>
<style>
	.ibox-title a{
		color: inherit !important;
	}
	.collapse-link:hover{
		cursor: pointer;
	}
</style>
<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#TipoGestion").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var TG=document.getElementById('TipoGestion').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=9&id="+TG,
				success: function(response){
					$('#Evento').html(response).fadeIn();
					$('#Evento').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=14&id="+TG+"&clt=<?php echo base64_encode($Cliente);?>",
				success: function(response){
					$('#Destino').html(response).fadeIn();
					$('#Destino').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
			//Cuando hay integración con Issabel
//			$.ajax({
//				url:"ajx_buscar_datos_json.php",
//				data:{type:10,tge:TG},
//				dataType:'json',
//				success: function(data){
//					document.getElementById('TipoDestino').value=data.TDest;
//					if(document.getElementById('TipoDestino').value==1){
//						document.getElementById('dv_btnLlamar').style.display='block';
//					}else{
//						ResetCall();
//						document.getElementById('dv_btnLlamar').style.display='none';
//					}
//					$('.ibox-content').toggleClass('sk-loading',false);
//				}
//			});
		});
		$("#Evento").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=10&id="+document.getElementById('Evento').value,
				success: function(response){
					$('#ResultadoGestion').html(response).fadeIn();
					$('#ResultadoGestion').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});
		$("#Cuotas").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			TablaAcuerdos();
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$("#RetiroAnticipado").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			CalculaAcuerdo();
			TablaAcuerdos();
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$("#Descuento").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			CalculaAcuerdo();
			TablaAcuerdos();
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$("#AbonoInicial").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			CalculaAcuerdo();
			TablaAcuerdos();
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$("#FechaAcuerdo").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			CalculaAcuerdo();
			TablaAcuerdos();
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		
		//Check cobrar intereses liquidacion
		$('#chkCobIntLiqInt').on('ifChecked', function(event){
			$('.ibox-content').toggleClass('sk-loading',true);
			CalculaLiqIntereses();
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$('#chkCobIntLiqInt').on('ifUnchecked', function(event){
			$('.ibox-content').toggleClass('sk-loading',true);
			CalculaLiqIntereses();
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		
		//Check mostrar facturas no vencidas liquidacion		
		$('#chkVerFactNoVencLiqInt').on('ifChecked', function(event){
			$('.ibox-content').toggleClass('sk-loading',true);
			CalculaLiqIntereses();
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$('#chkVerFactNoVencLiqInt').on('ifUnchecked', function(event){
			$('.ibox-content').toggleClass('sk-loading',true);
			CalculaLiqIntereses();
			$('.ibox-content').toggleClass('sk-loading',false);
		});	
		
		//Check cobrar intereses acuerdos
		$('#chkCobInt').on('ifChecked', function(event){
			$('.ibox-content').toggleClass('sk-loading',true);
			CalculaAcuerdo();
			TablaAcuerdos();
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$('#chkCobInt').on('ifUnchecked', function(event){
			$('.ibox-content').toggleClass('sk-loading',true);
			CalculaAcuerdo();
			TablaAcuerdos();
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		
		//Check mostrar facturas no vencidas acuerdos	
		$('#chkVerFactNoVenc').on('ifChecked', function(event){
			$('.ibox-content').toggleClass('sk-loading',true);
			CalculaAcuerdo();
			TablaAcuerdos();
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$('#chkVerFactNoVenc').on('ifUnchecked', function(event){
			$('.ibox-content').toggleClass('sk-loading',true);
			CalculaAcuerdo();
			TablaAcuerdos();
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		
		$("#Destino").change(function(){
			//ResetCall(); //Cuando hay integración con Issabel
		});
		
		$('#chkLiqIntereses').on('ifChecked', function(event){
			$('.ibox-content').toggleClass('sk-loading',true);
			document.getElementById('dvLiqIntereses').style.display='block';
			document.getElementById('dvAcuerdos').style.display='none';
			$('#chkRegAcuerdo').iCheck('uncheck');
			CalculaLiqIntereses();
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$('#chkLiqIntereses').on('ifUnchecked', function(event){
			document.getElementById('dvLiqIntereses').style.display='none';
		});	
		
		$('#chkRegAcuerdo').on('ifChecked', function(event){
			$('.ibox-content').toggleClass('sk-loading',true);
			document.getElementById('dvAcuerdos').style.display='block';
			document.getElementById('dvLiqIntereses').style.display='none';
			$('#chkLiqIntereses').iCheck('uncheck');
			CalculaAcuerdo();
			TablaAcuerdos();
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$('#chkRegAcuerdo').on('ifUnchecked', function(event){
			document.getElementById('dvAcuerdos').style.display='none';
		});
	});
</script>
<script>
var json=[];
var cant=0;

function ConsultarDatosCliente(){
	var Cliente='<?php echo $row_Cliente['CodigoCliente'];?>';
	if(Cliente!=""){
		self.name='opener';
	remote=open('socios_negocios.php?id='+Base64.encode(Cliente)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
	remote.focus();
	}
}

function SeleccionarFactura(Num, Obj, Serie){
	var btnZIP=document.getElementById('btnZIP');
	var sw=-1;
	var strJSON;
	
	json.forEach(function(element,index){
		if(json[index].Num==Num){
			sw=index;
		}
		//console.log(element,index);
	});
	
	if(sw>=0){
		json.splice(sw, 1);
		cant--;
	}else{
		json.push({Num,Obj,Serie});
		cant++;
	}
	
	strJSON=JSON.stringify(json);
	
	if(cant>0){
		$("#btnZIP").prop('disabled', false);
	}else{
		$("#btnZIP").prop('disabled', true);
	}
	
}
	
function DescargarZIP(){
	DescargarSAPDownload("sapdownload.php", "id="+Base64.encode('15')+"&type="+Base64.encode('2')+"&zip="+Base64.encode('1')+"&file="+JSON.stringify(json), true)
}

function SeleccionarTodos(){
	var Check = document.getElementById('chkAll').checked;
	if(Check==false){
		json=[];
		cant=0;
		$("#btnZIP").addClass("disabled");
		$("#btnZIP").attr("disabled");
	}
	$(".chkSelOT").prop("checked", Check);
	if(Check){
		json=[];
		cant=0;
		$(".chkSelOT").trigger('change');
	}		
}

function TablaAcuerdos(){
	var Cuotas=document.getElementById('Cuotas');
	var Valor=document.getElementById('SaldoDiferir');
	var Fecha=document.getElementById('FechaAcuerdo');
	if((Cuotas.value>=1)&&(Valor.value!="0")){
		$.ajax({
			type: "POST",
			url: "ajx_cuadro_acuerdos.php?type=1&cuotas="+Cuotas.value+"&valor="+Valor.value.replace(/,/g, '')+"&fecha="+Fecha.value,
			success: function(response){
				if(response!=""){
					document.getElementById('dv_grpCuotas').style.display='block';
					$('#dv_CuotasAcuerdo').html(response).fadeIn();
				}				
			}
		});
	}else{
		document.getElementById('dv_grpCuotas').style.display='none';
	}
}

function ObtenerComentario(){
	$('.ibox-content').toggleClass('sk-loading',true);
	var ResultadoGestion=document.getElementById('ResultadoGestion');
	if(ResultadoGestion.value!=""){
		$.ajax({
			url:"ajx_buscar_datos_json.php",
			data:{type:16,Res:ResultadoGestion.value},
			dataType:'json',
			success: function(data){
				document.getElementById('Comentarios').value=data.Comentarios;
			}
		});
	}
	$('.ibox-content').toggleClass('sk-loading',false);
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
                    <h2>Gestionar cartera</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Gesti&oacute;n de cartera</a>
                        </li>
                        <li class="active">
                            <strong>Gestionar cartera</strong>
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
									<input autocomplete="off" name="Cliente" type="text" required="required" class="form-control" id="Cliente" maxlength="100" placeholder="Consulte el ID o el nombre del cliente" value="<?php echo utf8_encode($row_Cliente['NombreCliente']);?>">
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
          <div class="row">
           <div class="col-lg-12">
			   <div class="ibox-content">
				    <?php include("includes/spinner.php"); ?>
				 <div class="form-group">
					<h3 class="col-lg-8 bg-primary p-xs b-r-sm"><i onClick="ConsultarDatosCliente();" title="Consultar cliente" style="cursor: pointer;display: initial;" class="btn-xs btn-success fa fa-search"></i> Cliente: <?php echo utf8_encode($row_Cliente['NombreCliente'])." [".$row_Cliente['LicTradNum']."]";?></h3>
					<h3 class="col-lg-1">&nbsp;</h3>
					<h3 class="col-lg-3 bg-warning p-xs b-r-sm"><span class="pull-right">Estado de servicio: <?php echo $row_Cliente['DeEstadoServicioCliente'];?></span></h3>
				 </div>
			   	<div class="tabs-container">
			  		<ul class="nav nav-tabs">
						<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-briefcase"></i> Registrar gestión</a></li>
						<li class=""><a data-toggle="tab" href="#tab-2" onClick="ConsultarTab('2');"><i class="fa fa-money"></i> Pagos realizados</a></li>
						<li class=""><a data-toggle="tab" href="#tab-3"><i class="fa fa-calendar"></i> Historico de gestión</a></li>
					</ul>
				  <div class="tab-content">
					  <div id="tab-1" class="tab-pane active">
					  		<div class="panel-body">
								<form action="gestionar_cartera.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="RegistrarGestion">
									<div class="ibox">
										<div class="ibox-title bg-success">
											<?php $Total=SumarFacturasPendientes($Cliente);?>
											<h5 class="collapse-link"><i class="fa fa-file-text"></i> Facturas pendientes</h5>
											 <a class="collapse-link pull-right">
												<i class="fa fa-chevron-up"></i>
											</a>	
										</div>
										<div class="ibox-content">
											<div class="form-group">
												<div class="row m-b-md">
													<div class="col-lg-10">
														<h2>Total pendiente: <span class="font-bold text-danger"><?php echo "$".number_format($Total,0);?></span></h2>
													</div>
													<div class="col-lg-2">
														<button type="button" class="btn btn-primary pull-right" id="btnZIP" name="btnZIP" onClick="DescargarZIP();" disabled><i class="fa fa-file-zip-o"></i> Descargar facturas</button>
													</div>
												</div>
												<div class="col-lg-12">
													<div class="table-responsive">
														<table class="table table-bordered table-striped table-hover dataTables-example">
														<thead>
														<tr>
															<th>#</th>
															<th>Factura</th>
															<th>Fecha contabilización</th>
															<th>Fecha vencimiento</th>
															<th>Valor factura</th>
															<th>Abono</th>
															<th>Días vencidos</th>
															<th>Saldo total</th>
															<th>Acciones</th>
															<th>Seleccionar <div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div></th>
														</tr>
														</thead>
														<tbody>
														<?php $i=1;
															while($row_FacturasPendientes=sqlsrv_fetch_array($SQL_FacturasPendientes)){ ?>
															 <tr>
																<td><?php echo $i;?></td>
																<td><?php echo $row_FacturasPendientes['NoDocumento'];?></td>
																<td><?php if($row_FacturasPendientes['FechaContabilizacion']->format('Y-m-d')){echo $row_FacturasPendientes['FechaContabilizacion']->format('Y-m-d');}else{echo $row_FacturasPendientes['FechaContabilizacion'];}?></td>
																<td><?php if($row_FacturasPendientes['FechaVencimiento']->format('Y-m-d')){echo $row_FacturasPendientes['FechaVencimiento']->format('Y-m-d');}else{echo $row_FacturasPendientes['FechaVencimiento'];}?></td>
																<td><?php echo "$".number_format($row_FacturasPendientes['TotalDocumento'],2);?></td>
																<td><?php echo "$".number_format($row_FacturasPendientes['ValorPagoDocumento'],2);?></td>
																<td><?php echo number_format($row_FacturasPendientes['DiasVencidos'],0);?></td>
																<td><?php echo "$".number_format($row_FacturasPendientes['SaldoDocumento'],2);?></td>
																<td><a href="sapdownload.php?id=<?php echo base64_encode('15');?>&type=<?php echo base64_encode('2');?>&DocKey=<?php echo base64_encode($row_FacturasPendientes['NoInterno']);?>&ObType=<?php echo base64_encode('13');?>&IdFrm=<?php echo base64_encode($row_FacturasPendientes['IdSeries']);?>" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-download"></i> Descargar</a></td>
																<td><div class="checkbox checkbox-success"><input type="checkbox" class="chkSelOT" id="singleCheckbox<?php echo $row_FacturasPendientes['NoDocumento'];?>" value="" onChange="SeleccionarFactura('<?php echo $row_FacturasPendientes['NoInterno'];?>','<?php echo ('13');?>','<?php echo ($row_FacturasPendientes['IdSeries']);?>');" aria-label="Single checkbox One"><label></label></div></td>
															</tr>
														<?php $i++; }?>
														</tbody>
														</table>												
													</div>
												</div>
											</div>
										</div>
									</div>									
									<div class="form-group">
										<label class="col-lg-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-briefcase"></i> Registrar gestión</h3></label>
									</div>
									<div class="form-group">
										<label class="col-lg-2 control-label">Tipo gestión</label>
										<div class="col-lg-3">
											<select name="TipoGestion" class="form-control select2" id="TipoGestion" required>
													<option value="">Seleccione...</option>
												<?php
													while($row_TipoGestion=sqlsrv_fetch_array($SQL_TipoGestion)){?>
													<option value="<?php echo $row_TipoGestion['ID_TipoGestion'];?>"><?php echo $row_TipoGestion['TipoGestion'];?></option>
											  <?php }?>
											</select>
											<input type="hidden" name="TipoDestino" id="TipoDestino" value="" />
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-2 control-label">Destino</label>
										<div class="col-lg-5">
											<select name="Destino" class="form-control select2" id="Destino" required>
													<option value="">Seleccione...</option>
											</select>
										</div>
										<div id="dv_btnLlamar" class="col-lg-2" style="display: none;">
											<button title="Llamar" type="button" class="btn btn-success" onClick="return EsTel('<?php echo base64_encode($FileCall);?>');"><i class="fa fa-phone"></i></button>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-2 control-label">Evento</label>
										<div class="col-lg-3">
											<select name="Evento" class="form-control select2" id="Evento" required>
													<option value="">Seleccione...</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-2 control-label">Dirigido a</label>
										<div class="col-lg-3">
											<select name="Dirigido" class="form-control select2" id="Dirigido" required>
													<option value="">Seleccione...</option>
												<?php
													while($row_Dirigido=sqlsrv_fetch_array($SQL_Dirigido)){?>
													<option value="<?php echo $row_Dirigido['ID_Dirigido'];?>"><?php echo $row_Dirigido['NombreDirigido'];?></option>
											  <?php }?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-2 control-label">Resultado</label>
										<div class="col-lg-4">
											<select name="ResultadoGestion" class="form-control select2" id="ResultadoGestion" required>
													<option value="">Seleccione...</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-2 control-label">Comentarios</label>
										<div class="col-lg-6">
											<textarea name="Comentarios" maxlength="1000" rows="7" required="required" class="form-control" id="Comentarios" type="text"></textarea>
										</div>
										<div id="dv_btnComents" class="col-lg-2">
											<button title="Agregar comentario sugerido" type="button" class="btn btn-warning" onClick="ObtenerComentario();"><i class="fa fa-comment"></i></button>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-2 control-label">Fecha de compromiso de pago</label>
										<div class="col-lg-2 input-group date">
											 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaCompPago" type="text" class="form-control" id="FechaCompPago" value="" readonly="readonly" placeholder="YYYY-MM-DD">
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-2 control-label">Causa de no pago</label>
										<div class="col-lg-4">
											<select name="CausaNoPago" class="form-control select2" id="CausaNoPago">
												<option value="1">(NINGUNA)</option>
												<?php
													while($row_CausaNoPago=sqlsrv_fetch_array($SQL_CausaNoPago)){?>
													<option value="<?php echo $row_CausaNoPago['ID_CausaNoPago'];?>"><?php echo $row_CausaNoPago['CausaNoPago'];?></option>
											  <?php }?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-2 control-label">Liquidar intereses</label>
										<div class="col-lg-4">
											<label class="checkbox-inline i-checks"><input name="chkLiqIntereses" id="chkLiqIntereses" type="checkbox" value="1"> Registrar liquidaci&oacute;n de intereses</label>
										</div>
									</div>
									<div id="dvLiqIntereses" style="display: none;">
										<div class="form-group">
											<label class="col-lg-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-money"></i> Liquidar intereses</h3></label>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Fecha liquidaci&oacute;n</label>
											<div class="col-lg-2 input-group date">
												 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaLiquidacion" type="text" required="required" class="form-control" id="FechaLiquidacion" value="<?php echo date('Y-m-d');?>" readonly="readonly">
											</div>	
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Intereses</label>
											<div class="col-lg-2">
												<label class="checkbox-inline i-checks"><input name="chkCobIntLiqInt" id="chkCobIntLiqInt" type="checkbox" value="1" checked="checked"> Cobrar intereses</label>
											</div>
											<?php if(PermitirFuncion(205)){?>
											<label class="col-lg-2 control-label">Facturas</label>
											<div class="col-lg-3">
												<label class="checkbox-inline i-checks"><input name="chkVerFactNoVencLiqInt" id="chkVerFactNoVencLiqInt" type="checkbox" value="1"> Mostrar facturas no vencidas</label>
											</div>
											<?php }?>
										</div>
										<div class="form-group">
											<div class="col-lg-12">
												<div id="dv_TblIntMoraLiqInt" class="table-responsive">			
												</div>												
											</div>										
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Total saldo ($)</label>
											<div class="col-lg-3">
												<input name="TotalSaldoLiqInt" type="text" class="form-control text-right" id="TotalSaldoLiqInt" maxlength="50" readonly="readonly" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Intereses en mora ($)</label>
											<div class="col-lg-3">
												<input name="InteresesMoraLiqInt" type="text" class="form-control text-right" readonly="readonly" id="InteresesMoraLiqInt" maxlength="50" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Retiro anticipado ($)</label>
											<div class="col-lg-3">
												<input name="RetiroAnticipadoLiqInt" type="text" class="form-control text-right" id="RetiroAnticipadoLiqInt" maxlength="50" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Gastos de cobranza ($)</label>
											<div class="col-lg-3">
												<input name="GastosCobranzaLiqInt" type="text" class="form-control text-right" readonly="readonly" id="GastosCobranzaLiqInt" maxlength="50" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Cobro prejurídico ($)</label>
											<div class="col-lg-3">
												<input name="CobroPrejuridicoLiqInt" type="text" class="form-control text-right" readonly="readonly" id="CobroPrejuridicoLiqInt" maxlength="50" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Total liquidado ($)</label>
											<div class="col-lg-3">
												<input name="TotalLiquidadoLiqInt" type="text" class="form-control text-right" readonly="readonly" id="TotalLiquidadoLiqInt" maxlength="50" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Descuento ($)</label>
											<div class="col-lg-3">
												<input name="DescuentoLiqInt" type="text" class="form-control text-right" id="DescuentoLiqInt" maxlength="50" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">TOTAL A PAGAR ($)</label>
											<div class="col-lg-3">
												<input name="TotalPagarLiqInt" type="text" class="form-control text-right font-bold" readonly="readonly" id="TotalPagarLiqInt" maxlength="50" value="0">
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-2 control-label">Acuerdo de pago</label>
										<div class="col-lg-4">
											<label class="checkbox-inline i-checks"><input name="chkRegAcuerdo" id="chkRegAcuerdo" type="checkbox" value="1"> Registrar acuerdo de pago</label>
										</div>
									</div>
									<div id="dvAcuerdos" style="display: none;">
										<div class="form-group">
											<label class="col-lg-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-handshake-o"></i> Acuerdos de pago</h3></label>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Tipo de convenio</label>
											<div class="col-lg-3">
												<select name="TipoConvenio" class="form-control" id="TipoConvenio">
													<option value="ESCRITO">ESCRITO</option>
													<option value="TELEFONICO">TELEFONICO</option>
													<option value="VERBAL">VERBAL</option>													
												</select>
											</div>
											<label class="col-lg-2 control-label">Fecha acuerdo</label>
											<div class="col-lg-3 input-group date">
												 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaAcuerdo" type="text" required="required" class="form-control" id="FechaAcuerdo" value="<?php echo date('Y-m-d');?>" readonly="readonly">
											</div>	
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Intereses</label>
											<div class="col-lg-2">
												<label class="checkbox-inline i-checks"><input name="chkCobInt" id="chkCobInt" type="checkbox" value="1" checked="checked"> Cobrar intereses</label>
											</div>
											<?php if(PermitirFuncion(205)){?>
											<label class="col-lg-2 control-label">Facturas</label>
											<div class="col-lg-3">
												<label class="checkbox-inline i-checks"><input name="chkVerFactNoVenc" id="chkVerFactNoVenc" type="checkbox" value="1"> Mostrar facturas no vencidas</label>
											</div>
											<?php }?>
										</div>
										<div class="form-group">
											<div class="col-lg-12">
												<div id="dv_TblIntMora" class="table-responsive">			
												</div>												
											</div>										
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Total saldo ($)</label>
											<div class="col-lg-3">
												<input name="TotalSaldo" type="text" class="form-control text-right" id="TotalSaldo" maxlength="50" readonly="readonly" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Intereses en mora ($)</label>
											<div class="col-lg-3">
												<input name="InteresesMora" type="text" class="form-control text-right" readonly="readonly" id="InteresesMora" maxlength="50" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Retiro anticipado ($)</label>
											<div class="col-lg-3">
												<input name="RetiroAnticipado" type="text" class="form-control text-right" id="RetiroAnticipado" maxlength="50" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Gastos de cobranza ($)</label>
											<div class="col-lg-3">
												<input name="GastosCobranza" type="text" class="form-control text-right" readonly="readonly" id="GastosCobranza" maxlength="50" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Cobro prejurídico ($)</label>
											<div class="col-lg-3">
												<input name="CobroPrejuridico" type="text" class="form-control text-right" readonly="readonly" id="CobroPrejuridico" maxlength="50" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Total liquidado ($)</label>
											<div class="col-lg-3">
												<input name="TotalLiquidado" type="text" class="form-control text-right" readonly="readonly" id="TotalLiquidado" maxlength="50" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Descuento ($)</label>
											<div class="col-lg-3">
												<input name="Descuento" type="text" class="form-control text-right" id="Descuento" maxlength="50" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">TOTAL A PAGAR ($)</label>
											<div class="col-lg-3">
												<input name="TotalPagar" type="text" class="form-control text-right font-bold" readonly="readonly" id="TotalPagar" maxlength="50" value="0">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Abono inicial ($)</label>
											<div class="col-lg-3">
												<input name="AbonoInicial" type="text" class="form-control text-right" id="AbonoInicial" maxlength="50" value="0">
											</div>										
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Saldo a diferir ($)</label>
											<div class="col-lg-3">
												<input name="SaldoDiferir" type="text" class="form-control text-right" id="SaldoDiferir" maxlength="50" readonly="readonly" value="0">
											</div>										
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Cuotas</label>
											<div class="col-lg-2">
												<input class="touchspin1" type="text" value="1" name="Cuotas" id="Cuotas">
											</div>											
										</div>
										<div id="dv_grpCuotas" class="form-group" style="display: none;">
											<div class="col-lg-2">&nbsp;</div>
											<div class="col-lg-8 col-md-6 col-sm-12">
												<div id="dv_CuotasAcuerdo" class="table-responsive"></div>
											</div>
										</div>
									</div>
									<div class="form-group">
										<div class="col-lg-4">
											<button class="btn btn-primary" type="submit" id="Guardar"><i class="fa fa-check"></i>&nbsp;Guardar gestión</button>
										</div>
									</div>
									<br>
									<?php /*?><div class="form-group">
										<div class="col-lg-6 col-md-12">
											<div class="panel-body" style="border: 1px solid #e7eaec;">
												<iframe scrolling="no" marginheight="0" frameborder="0" class="col-lg-12" id="dv_TagLlamada" src="frm1.php?type=1" style="border-style:solid; border-width:0; padding-left:0; padding-right:0; padding-top:0; padding-bottom:0" height="70"></iframe>
											</div>
										</div>
										<div class="col-lg-6 col-md-12">
											<div class="panel-body" style="border: 1px solid #e7eaec;">
												<iframe scrolling="no" marginheight="0" frameborder="0" class="col-lg-12" id="dv_ElastixLlamada" src="frm2.php?type=1" style="border-style:solid; border-width:0; padding-left:0; padding-right:0; padding-top:0; padding-bottom:0" height="70"></iframe>
											</div>
										</div>
									</div><?php */?>
									<input type="hidden" id="P" name="P" value="43" />
									<input type="hidden" id="CardCode" name="CardCode" value="<?php echo base64_encode($Cliente);?>" />
									<input type="hidden" id="cllName" name="cllName" value="" />
								</form>
							</div>
					  </div>
					  <div id="tab-2" class="tab-pane">
						  <div id="dv_pagosreal" class="panel-body">
						 
						  </div>
					  </div>
					  <div id="tab-3" class="tab-pane">
						<div class="panel-body">
							<label class="col-lg-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-calendar"></i> Historico de gestiones</h3></label>
							<div class="col-lg-12">
								<div class="table-responsive">
									<table style="width: 100%;" class="table table-striped table-bordered table-hover dataTables-example" >
									<thead>
									<tr>
										<th>#</th>
										<th>Tipo gestión</th>
										<th>Destino</th>
										<th>Evento</th>
										<th>Resultado</th>
										<th>Comentario</th>
										<th>Causa no pago</th>
										<th>Acuerdo de pago</th>
										<th>Fecha registro</th>
										<th>Usuario</th>
									</tr>
									</thead>
									<tbody>
									<?php $i=1;
										while($row_HistGestion=sqlsrv_fetch_array($SQL_HistGestion)){?>
										 <tr class="gradeX">
											<td><?php echo $i;?></td>
											<td><?php echo $row_HistGestion['TipoGestion'];?><?php if($row_HistGestion['CallFile']!=""){?><a href="recorddownload.php?file=<?php echo base64_encode($row_HistGestion['ID_Gestion']);?>" target="_blank" class="btn btn-link btn-xs" title="Descargar grabación"><i class="fa fa-phone"></i></a><?php }?></td>
											<td><?php echo $row_HistGestion['Destino'];?></td>
											<td><?php echo $row_HistGestion['NombreEvento'];?></td>
											<td><?php echo $row_HistGestion['ResultadoGestion'];?></td>
											<td><?php echo $row_HistGestion['Comentarios'];?></td>
											<td><?php echo $row_HistGestion['CausaNoPago'];?></td>
											<td><?php if($row_HistGestion['AcuerdoPago']==1){echo "SI";}else{echo "NO";}?></td>
											<td><?php echo $row_HistGestion['FechaRegistro']->format('Y-m-d H:i');?></td>
											<td><?php echo $row_HistGestion['Usuario'];?></td>
										</tr>
									<?php $i++; }?>
									</tbody>
									</table>
									</div>			  		
						 	</div>
							<label class="col-lg-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-handshake-o"></i> Historico de acuerdos de pago</h3></label>
							<div class="col-lg-12">  
						  		<div class="table-responsive">
								<table style="width: 100%;" class="table table-striped table-bordered table-hover dataTables-example">
								<thead>
								<tr>
									<th>Fecha acuerdo</th>
									<th>Total saldo</th>
									<th>Intereses en mora</th>
									<th>Gastos de cobranza</th>									
									<th>Cobro prejurídico</th>
									<th>Total liquidado</th>
									<th>Descuento</th>
									<th>Total a pagar</th>
									<th>Abono inicial</th>
									<th>Saldo diferido</th>
									<th>Cuotas</th>	
									<th>Valor cuota</th>	
									<th>Fecha registro</th>
									<th>Usuario</th>
									<th>Acciones</th>
								</tr>
								</thead>
								<tbody>
								<?php while($row_HistAcuerdos=sqlsrv_fetch_array($SQL_HistAcuerdos)){?>
									 <tr class="gradeX">
										 <td><?php if($row_HistAcuerdos['FechaAcuerdo']!=""){echo $row_HistAcuerdos['FechaAcuerdo']->format('Y-m-d');}else{echo "--";}?></td>
										 <td><?php echo "$".number_format($row_HistAcuerdos['TotalSaldo'],0);?></td>
										 <td><?php echo "$".number_format($row_HistAcuerdos['InteresesMora'],0);?></td>
										 <td><?php echo "$".number_format($row_HistAcuerdos['GastosCobranza'],0);?></td>					 
										 <td><?php echo "$".number_format($row_HistAcuerdos['CobroPrejuridico'],0);?></td>
										 <td><?php echo "$".number_format($row_HistAcuerdos['TotalLiquidado'],0);?></td>
										 <td><?php echo "$".number_format($row_HistAcuerdos['Descuento'],0);?></td>
										 <td><?php echo "$".number_format($row_HistAcuerdos['TotalPagar'],0);?></td>
										 <td><?php echo "$".number_format($row_HistAcuerdos['AbonoInicial'],0);?></td>
										 <td><?php echo "$".number_format($row_HistAcuerdos['SaldoDiferir'],0);?></td>
										 <td><?php echo number_format($row_HistAcuerdos['Cuotas'],0);?></td>	 
										 <td><?php echo "$".number_format($row_HistAcuerdos['ValorCuota'],0);?></td>
										 <td><?php echo $row_HistAcuerdos['FechaRegistro']->format('Y-m-d H:i');?></td>
										 <td><?php echo $row_HistAcuerdos['Usuario'];?></td>
										 <td><a href="sapdownload.php?id=<?php echo base64_encode('11');?>&type=<?php echo base64_encode('2');?>&DocKey=<?php echo base64_encode($row_HistAcuerdos['ID_Acuerdo']);?>" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-download"></i> Descargar</a></td>
									</tr>
								<?php }?>
								</tbody>							
								</table>
						  		</div>
						 	</div>
							<label class="col-lg-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-money"></i> Historico de liquidaci&oacute;n de intereses</h3></label>
							<div class="col-lg-12">  
						  		<div class="table-responsive">
								<table style="width: 100%;" class="table table-striped table-bordered table-hover dataTables-example">
								<thead>
								<tr>
									<th>Fecha liquidaci&oacute;n</th>
									<th>Total saldo</th>
									<th>Intereses en mora</th>
									<th>Gastos de cobranza</th>									
									<th>Cobro prejurídico</th>
									<th>Total liquidado</th>
									<th>Descuento</th>
									<th>Total a pagar</th>
									<th>Fecha registro</th>
									<th>Usuario</th>
									<th>Acciones</th>
								</tr>
								</thead>
								<tbody>
								<?php while($row_LiqIntereses=sqlsrv_fetch_array($SQL_LiqIntereses)){?>
									 <tr class="gradeX">
										 <td><?php if($row_LiqIntereses['FechaLiquidacion']!=""){echo $row_LiqIntereses['FechaLiquidacion']->format('Y-m-d');}else{echo "--";}?></td>
										 <td><?php echo "$".number_format($row_LiqIntereses['TotalSaldo'],0);?></td>
										 <td><?php echo "$".number_format($row_LiqIntereses['InteresesMora'],0);?></td>
										 <td><?php echo "$".number_format($row_LiqIntereses['GastosCobranza'],0);?></td>					 
										 <td><?php echo "$".number_format($row_LiqIntereses['CobroPrejuridico'],0);?></td>
										 <td><?php echo "$".number_format($row_LiqIntereses['TotalLiquidado'],0);?></td>
										 <td><?php echo "$".number_format($row_LiqIntereses['Descuento'],0);?></td>
										 <td><?php echo "$".number_format($row_LiqIntereses['TotalPagar'],0);?></td>
										 <td><?php echo $row_LiqIntereses['FechaRegistro']->format('Y-m-d H:i');?></td>
										 <td><?php echo $row_LiqIntereses['Usuario'];?></td>
										 <td><a href="sapdownload.php?id=<?php echo base64_encode('12');?>&type=<?php echo base64_encode('2');?>&DocKey=<?php echo base64_encode($row_LiqIntereses['ID_LiqInt']);?>" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-download"></i> Descargar</a></td>
									</tr>
								<?php }?>
								</tbody>
								</table>
						  		</div>
						 	</div>
						</div>
					  </div>
				  </div>
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
		 $("#Consultar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
		 $("#RegistrarGestion").validate({
			 submitHandler: function(form){
				Swal.fire({
					title: "¿Está seguro que desea guardar los datos?",
					icon: "info",
					showCancelButton: true,
					confirmButtonText: "Si, confirmo",
					cancelButtonText: "No"
				}).then((result) => {
					if (result.isConfirmed) {
						$('.ibox-content').toggleClass('sk-loading',true);
						form.submit();
					}
				});
			}
		});
		 
		 $('#FechaAcuerdo').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
			 	todayHighlight: true
            });
		 $('#FechaLiquidacion').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
			 	todayHighlight: true
            });
		$('#FechaMora').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true
            });
		 $('#FechaCompPago').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
			 	todayHighlight: true
            });
		 $(".select2").select2();
		 $('.i-checks').iCheck({
			 checkboxClass: 'icheckbox_square-green',
             radioClass: 'iradio_square-green',
          });
		 
		 $(".touchspin1").TouchSpin({
                buttondown_class: 'btn btn-white',
                buttonup_class: 'btn btn-white'
            });	
		 
		 $('.dataTables-example').DataTable({
                pageLength: 10,
			 	responsive: true,
                dom: '<"html5buttons"B>lTfgitp',
				ordering:  false,
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
<script>
//Variables de tab
var tab_2=0;

function ConsultarTab(type){
	if(type==2){//Pagos realizados
		if(tab_2==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "sn_pagos_realizados.php?id=<?php echo base64_encode($row_Cliente['CodigoCliente']);?>",
				success: function(response){
					$('#dv_pagosreal').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_2=1;
				}
			});
		}
	}
}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>