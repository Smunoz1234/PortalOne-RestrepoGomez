<?php require_once("includes/conexion.php");
PermitirAcceso(318);
$sw=0;//Para saber si ya se selecciono un cliente y mostrar las sucursales
$Filtro="";
$sw_Clt=0;//Tipo cliente
$sw_Std=0;//Tipo Estandar

//Normas de reparto (Sucursal)
$SQL_DRSucursal=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=3');


if(isset($_GET['Anno'])&&($_GET['Anno']!="")){
	$Anno=$_GET['Anno'];
	$sw=1;
}else{
	$Anno=date('Y');
}


//Cliente
if(isset($_GET['Cliente'])){
	if($_GET['Cliente']!=""){//Si se selecciono el cliente
		$Filtro.=" and ID_CodigoCliente='".$_GET['Cliente']."'";
		$sw_suc=1;//Cuando se ha seleccionado una sucursal
		$sw=1;
		if(isset($_GET['Sucursal'])){
			if($_GET['Sucursal']==""){
				//Sucursales
				if(PermitirFuncion(205)){
					$Where="CodigoCliente='".$_GET['Cliente']."' and TipoDireccion='S'";
					$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","NombreSucursal",$Where);
				}else{
					$Where="CodigoCliente='".$_GET['Cliente']."' and TipoDireccion='S' and ID_Usuario = ".$_SESSION['CodUser'];
					$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal",$Where);	
				}
				$j=0;
				unset($WhereSuc);
				$WhereSuc = array(); 
				while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){
					$WhereSuc[$j]="NombreSucursal='".$row_Sucursal['NombreSucursal']."'";
					$j++;
				}
				$FiltroSuc=implode(" OR ",$WhereSuc);
				$Filtro.=" and (".$FiltroSuc.")";
			}else{
				$Filtro.=" and NombreSucursal='".$_GET['Sucursal']."'";
			}
		}
		
	}else{
		if(!PermitirFuncion(205)){
			$Where="ID_Usuario = ".$_SESSION['CodUser'];
			$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);
			$k=0;
			while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){
				
				//Sucursales
				$Where="CodigoCliente='".$row_Cliente['CodigoCliente']."' and TipoDireccion='S' and ID_Usuario = ".$_SESSION['CodUser'];
				$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal",$Where);	

				$j=0;
				unset($WhereSuc);
				$WhereSuc = array();  
				while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){
					$WhereSuc[$j]="NombreSucursal='".$row_Sucursal['NombreSucursal']."'";
					$j++;
				}
				
				$FiltroSuc=implode(" OR ",$WhereSuc);
				
				if($k==0){
					$Filtro.=" AND (ID_CodigoCliente='".$row_Cliente['CodigoCliente']."' AND (".$FiltroSuc."))";
				}else{
					$Filtro.=" OR (ID_CodigoCliente='".$row_Cliente['CodigoCliente']."' AND (".$FiltroSuc."))";
				}				
				
				$k++;
			}
		}
	}
}

if($sw==1){	
	//$SQL_Datos=Seleccionar("tbl_ProgramacionOrdenesServicio","*","IdCliente='".$_GET['Cliente']."' and IdLineaSucursal='".$_GET['Sucursal']."' and Periodo='".$_GET['Anno']."'");
	//$Num_Datos=sql_num_rows($SQL_Datos);
	$SQL_LMT="";
	if($_GET['Sucursal']!=""){
		$SQL_LMT=Seleccionar("uvw_Sap_tbl_ArticulosLlamadas","*","(CodigoCliente='".$_GET['Cliente']."' and LineaSucursal='".$_GET['Sucursal']."' and Estado='Y') OR IdTipoListaArticulo='2'","IdTipoListaArticulo, ItemCode");
	}	
	
	$SQL_Frecuencia=Seleccionar("tbl_ProgramacionOrdenesServicioFrecuencia","*");
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Cronograma de servicios | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {
		$("#NombreCliente").change(function(){
			var NomCliente=document.getElementById("NombreCliente");
			var Cliente=document.getElementById("Cliente");
			if(NomCliente.value==""){
				Cliente.value="";
				$("#Cliente").trigger("change");
			}
		});
		$("#Cliente").change(function(){
			var Cliente=document.getElementById("Cliente");
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Cliente.value+"&sucline=1&tdir=S",
				success: function(response){
					$('#Sucursal').html(response).fadeIn();
					$("#Sucursal").trigger("change");
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});
	});
</script>
<script>
<?php if($sw==1){ ?>
function AgregarLMT(){
	var LMT=document.getElementById("ListaLMT");
	var Frecuencia=document.getElementById("Frecuencia");
	var FechaCorte=document.getElementById("FechaCorte");
	var frame=document.getElementById('DataGrid');
	
	if(LMT.value!=""){
		if(Frecuencia.value!=""){
			if(FechaCorte.value==""){
				Swal.fire({
					title: '¡Lo sentimos!',
					text: 'Debe seleccionar la fecha de corte',
					icon: 'error'
				});
				return
			}
		}
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=22&itemcode="+LMT.value+"&cardcode=<?php echo $_GET['Cliente'];?>&idsucursal=<?php echo $_GET['Sucursal'];?>&periodo=<?php echo $_GET['Anno'];?>&frecuencia="+Frecuencia.value+"&fechacorte="+FechaCorte.value,		
			success: function(response){
				frame.src="detalle_cronograma_servicios.php?cardcode=<?php echo base64_encode($_GET['Cliente']);?>&idsucursal=<?php echo base64_encode($_GET['Sucursal']);?>&periodo=<?php echo base64_encode($Anno);?>";
				$('#ListaLMT').val(null).trigger('change');
				Swal.fire({
					title: '¡Listo!',
					text: 'Se ha agregado el item exitosamente',
					icon: 'success'
				});
			}
		});
	}else{
		Swal.fire({
			title: '¡Lo sentimos!',
			text: 'Debe seleccionar la lista de materiales para agregar',
			icon: 'error'
		});
	}
}
<?php }?>
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
                    <h2>Cronograma de servicios</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Servicios</a>
                        </li>
						 <li>
                            <a href="#">Asistentes</a>
                        </li>
                        <li class="active">
                            <strong>Cronograma de servicios</strong>
                        </li>
                    </ol>
                </div>
            </div>           
         <div class="wrapper wrapper-content">
			<div class="row">
				<div class="col-lg-12">   
					<div class="ibox-content"> 
						 <?php include("includes/spinner.php"); ?>
						<form action="cronograma_servicios.php" method="get" class="form-horizontal" id="frmBuscar" name="frmBuscar">
							<div class="form-group">
								<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
							  </div>
							<div class="form-group">
								<label class="col-lg-1 control-label">Cliente <span class="text-danger">*</span></label>
								<div class="col-lg-3">
									<input name="Cliente" type="hidden" id="Cliente" value="<?php if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){ echo $_GET['Cliente'];}?>">
									<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Ingrese para buscar..." value="<?php if(isset($_GET['NombreCliente'])&&($_GET['NombreCliente']!="")){ echo $_GET['NombreCliente'];}?>" required>
								</div>
								<label class="col-lg-1 control-label">Sucursal cliente</label>
								<div class="col-lg-3">
									 <select id="Sucursal" name="Sucursal" class="form-control select2">
										<option value="">(Todos)</option>
										<?php 
										 if($sw_suc==1){//Cuando se ha seleccionado una opción
											 if(PermitirFuncion(205)){
												$Where="CodigoCliente='".$_GET['Cliente']."' and TipoDireccion='S'";
												$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","NombreSucursal, NumeroLinea",$Where);
											 }else{
												$Where="CodigoCliente='".$_GET['Cliente']."' and TipoDireccion='S' and ID_Usuario = ".$_SESSION['CodUser'];
												$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal, NumeroLinea",$Where);	
											 }
											 while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
												<option value="<?php echo $row_Sucursal['NumeroLinea'];?>" <?php if(strcmp($row_Sucursal['NumeroLinea'],$_GET['Sucursal'])==0){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['NombreSucursal'];?></option>
										<?php }
										 }?>
									</select>
								</div>
								<label class="col-lg-1 control-label">Año <span class="text-danger">*</span></label>
								<div class="col-lg-2">
									<select name="Anno" required class="form-control" id="Anno">
										<option value="2019" <?php if((isset($Anno))&&(strcmp(2019,$Anno)==0)){ echo "selected=\"selected\"";}?>>2019</option>
										<option value="2020" <?php if((isset($Anno))&&(strcmp(2020,$Anno)==0)){ echo "selected=\"selected\"";}?>>2020</option>
										<option value="2021" <?php if((isset($Anno))&&(strcmp(2021,$Anno)==0)){ echo "selected=\"selected\"";}?>>2021</option>
										<option value="2022" <?php if((isset($Anno))&&(strcmp(2022,$Anno)==0)){ echo "selected=\"selected\"";}?>>2022</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-lg-1 control-label">Sede <span class="text-danger">*</span></label>
								<div class="col-lg-2">
									<select name="DRSucursal" class="form-control" id="DRSucursal" required>
										<option value="">Seleccione...</option>
									  <?php while($row_DRSucursal=sqlsrv_fetch_array($SQL_DRSucursal)){?>
												<option value="<?php echo $row_DRSucursal['OcrCode'];?>" <?php if((isset($_GET['DRSucursal'])&&($_GET['DRSucursal']!=""))&&(strcmp($row_DRSucursal['OcrCode'],$_GET['DRSucursal'])==0)){echo "selected=\"selected\"";}?>><?php echo $row_DRSucursal['OcrName'];?></option>
										<?php 	}?>
									</select>
								</div>								
								<div class="col-lg-9">
									<button type="submit" class="btn btn-outline btn-info pull-right"><i class="fa fa-search"></i> Buscar</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
         <br>
		<?php if($sw==1){ ?>	  
          <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
					<div class="row p-md">
						<div class="form-group">
							<div class="col-lg-6">
								<label class="control-label">Lista materiales / artículos</label>
								<select name="ListaLMT" class="form-control select2" id="ListaLMT">
										<option value="">Seleccione...</option>
								  <?php while($row_LMT=sqlsrv_fetch_array($SQL_LMT)){
											if(($row_LMT['IdTipoListaArticulo']==1)&&($sw_Clt==0)){
												echo "<optgroup label='Cliente'></optgroup>";
												$sw_Clt=1;
											}elseif(($row_LMT['IdTipoListaArticulo']==2)&&($sw_Std==0)){
												echo "<optgroup label='Genericas'></optgroup>";
												$sw_Std=1;
											}?>
										<option value="<?php echo $row_LMT['ItemCode'];?>"><?php echo $row_LMT['ItemCode']." - ".$row_LMT['ItemName']." (SERV: ".substr($row_LMT['Servicios'],0,20)." - ÁREA: ".substr($row_LMT['Areas'],0,20).")";?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-2">
								<label class="control-label">Frecuencia</label>
								<select name="Frecuencia" class="form-control" id="Frecuencia">
									<option value="">Ninguna</option>
									 <?php while($row_Frecuencia=sqlsrv_fetch_array($SQL_Frecuencia)){?>
										<option value="<?php echo $row_Frecuencia['IdFrecuencia'];?>"><?php echo $row_Frecuencia['DeFrecuencia'];?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-2">
								<label class="control-label">Fecha de corte</label>
								<div class="input-group date">
									 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaCorte" type="text" class="form-control" id="FechaCorte" value="<?php echo date('Y-m-d');?>" readonly="readonly" placeholder="YYYY-MM-DD">
								</div>
							</div>
							<div class="col-lg-1">
								<button type="button" id="btnNuevo" class="btn btn-success m-t-md" onClick="AgregarLMT();"><i class="fa fa-plus-circle"></i> Añadir</button>
							</div>
						</div>
					</div>
					<div class="tabs-container">  
						<ul class="nav nav-tabs">
							<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Contenido</a></li>		
							<li><span class="TimeAct"><div id="TimeAct">&nbsp;</div></span></li>
						</ul>
						<div class="tab-content">
							<div id="tab-1" class="tab-pane active">
								<iframe id="DataGrid" name="DataGrid" style="border: 0;" width="100%" height="700" src="detalle_cronograma_servicios.php?cardcode=<?php echo base64_encode($_GET['Cliente']);?>&idsucursal=<?php echo base64_encode($_GET['Sucursal']);?>&periodo=<?php echo base64_encode($Anno);?>"></iframe>
							</div>						
						</div>					
					</div>
				</div>
			</div>			
          </div>	
		 <?php }
			 ?>
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>	
	 $(document).ready(function(){
		$("#frmBuscar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
		 
		 $(".btn_del").each(function (el){
			 $(this).bind("click",delRow);
		 });
		 
		 //$(".btn_plus").bind("click",addField);
		 
		 $('#FechaCorte').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
			 	todayHighlight: true
            });
		 
		 $("#frmAlertas").validate();
		 
		 $(".truncate").dotdotdot({
            watch: 'window'
		 });
		  		 
		  $(".select2").select2();
		 
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
					var value = $("#NombreCliente").getSelectedItemData().CodigoCliente;
					$("#Cliente").val(value).trigger("change");
				}
			}
		};

		$("#NombreCliente").easyAutocomplete(options);
	});	
</script>

<script>
function delRow(){//Eliminar div
	$(this).parent('div').remove();
}
function delRow2(btn){//Eliminar div
	$(btn).parent('div').remove();
}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>