<?php require_once("includes/conexion.php");
PermitirAcceso(413);

$sw=0;

$TotalVentas=0;
$TotalCostos=0;
$TotalGanancia=0;
$PrcntGanancia=0;

//Empleado de ventas
$SQL_EmpleadosVentas=Seleccionar('uvw_Sap_tbl_EmpleadosVentas','*','','DE_EmpVentas');

//Clientes
$SQL_Clientes=Seleccionar('uvw_Sap_tbl_Clientes','CodigoCliente, NombreCliente','','NombreCliente');

//Fechas
if(isset($_GET['FechaInicial'])&&$_GET['FechaInicial']!=""){
	$FechaInicial=$_GET['FechaInicial'];
	$sw=1;
}else{
	//Restar 7 dias a la fecha actual
	$fecha = date('Y-m-d');
	$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasDocSAP").' day');
	$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
	$FechaInicial=$nuevafecha;
}
if(isset($_GET['FechaFinal'])&&$_GET['FechaFinal']!=""){
	$FechaFinal=$_GET['FechaFinal'];
}else{
	$FechaFinal=date('Y-m-d');
}

//Filtros

$Cliente= isset($_GET['Cliente']) ? implode(",",$_GET['Cliente']) : "";
$Empleado= isset($_GET['EmpleadoVentas']) ? implode(",",$_GET['EmpleadoVentas']) : "";
$Articulo= isset($_GET['Articulo']) ? $_GET['Articulo'] : "";
$TipoInforme= isset($_GET['TipoInforme']) ? $_GET['TipoInforme'] : 0;

if($sw==1){
	$ParamCons=array(
		"'".$TipoInforme."'",
		1, //TipoResumen: Resumido
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Empleado."'",
		"'".$Cliente."'",
		"'".$Articulo."'"
	);
	$SQL=EjecutarSP('usp_InformeVentas',$ParamCons);
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Informe Análisis de ventas | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.ibox-title a{
		color: inherit !important;
	}
	.collapse-link:hover{
		cursor: pointer;
	}
	.modal-dialog{
		width: 70% !important;
	}
	.modal-footer{
		border: 0px !important;
	}
</style>
<script type="text/javascript">
	$(document).ready(function() {
		$("#NombreCliente").change(function(){
			var NomCliente=document.getElementById("NombreCliente");
			var Cliente=document.getElementById("Cliente");
			if(NomCliente.value==""){
				Cliente.value="";
			}	
		});
		$("#NombreArticulo").change(function(){
			var NomArticulo=document.getElementById("NombreArticulo");
			var Articulo=document.getElementById("Articulo");
			if(NomArticulo.value==""){
				Articulo.value="";
			}	
		});
	});
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
                    <h2>Informe Análisis de ventas</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Ventas</a>
                        </li>
                        <li>
                            <a href="#">Informes</a>
                        </li>
                        <li class="active">
                            <strong>Informe Análisis de ventas</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
			<div class="modal inmodal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="TituloModal"></h4>
						</div>
						<div class="modal-body" id="ContenidoModal"></div>
						<div class="modal-footer">
							<button type="button" class="btn btn-success m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
						</div>
					</div>
				</div>
			</div>			 
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="informe_analisis_venta.php" method="get" id="formBuscar" class="form-horizontal">
					   <div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Fechas</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>" autocomplete="off" />
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" autocomplete="off" />
								</div>
							</div>
							<label class="col-lg-1 control-label">Tipo de informe</label>
							<div class="col-lg-3">
								<select name="TipoInforme" class="form-control" id="TipoInforme">
									<option value="1" <?php if(isset($_GET['TipoInforme'])&&($_GET['TipoInforme']==1)){echo "selected=\"selected\"";}?>>Por clientes</option>
									<option value="2" <?php if(isset($_GET['TipoInforme'])&&($_GET['TipoInforme']==2)){echo "selected=\"selected\"";}?>>Por empleados de venta</option>
									<option value="3" <?php if(isset($_GET['TipoInforme'])&&($_GET['TipoInforme']==3)){echo "selected=\"selected\"";}?>>Por artículos</option>
								</select>
							</div>
						</div>
					  	<div class="form-group">	
							<label class="col-lg-1 control-label">Cliente</label>
							<div class="col-lg-3">
								<select data-placeholder="(Todos)" name="Cliente[]" class="form-control select2" id="Cliente" multiple>
								  <?php $j=0;
									while($row_Clientes=sqlsrv_fetch_array($SQL_Clientes)){?>
										<option value="<?php echo $row_Clientes['CodigoCliente'];?>" <?php if((isset($_GET['Cliente'][$j]))&&(strcmp($row_Clientes['CodigoCliente'],$_GET['Cliente'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_Clientes['NombreCliente'];?></option>
								  <?php }?>
								</select>
							</div>
<!--
							<div class="col-lg-3">
								<input name="Cliente" type="hidden" id="Cliente" value="<?php //if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){ echo $_GET['Cliente'];}?>">
								<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Ingrese para buscar..." value="<?php //if(isset($_GET['NombreCliente'])&&($_GET['NombreCliente']!="")){ echo $_GET['NombreCliente'];}?>">
							</div>
-->
							<label class="col-lg-1 control-label">Empleado de ventas</label>
							<div class="col-lg-3">
								<select data-placeholder="(Todos)" name="EmpleadoVentas[]" class="form-control select2" id="EmpleadoVentas" multiple>
								  <?php $j=0;
									while($row_EmpleadosVentas=sqlsrv_fetch_array($SQL_EmpleadosVentas)){?>
										<option value="<?php echo $row_EmpleadosVentas['ID_EmpVentas'];?>" <?php if((isset($_GET['EmpleadoVentas'][$j]))&&(strcmp($row_EmpleadosVentas['ID_EmpVentas'],$_GET['EmpleadoVentas'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_EmpleadosVentas['DE_EmpVentas'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Artículo</label>
							<div class="col-lg-3">
								<input name="Articulo" type="hidden" id="Articulo" value="<?php if(isset($_GET['Articulo'])&&($_GET['Articulo']!="")){ echo $_GET['Articulo'];}?>">
								<input name="NombreArticulo" type="text" class="form-control" id="NombreArticulo" placeholder="Ingrese para buscar..." value="<?php if(isset($_GET['NombreArticulo'])&&($_GET['NombreArticulo']!="")){ echo $_GET['NombreArticulo'];}?>">
							</div>
						</div>
					  	<div class="form-group">	
							<div class="col-lg-12">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
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
					<div class="ibox">
						<div class="ibox-title bg-success">
							<h5 class="collapse-link"><i class="fa fa-list"></i> Análisis resumido <?php if($TipoInforme==1){echo "por cliente";}elseif($TipoInforme==2){echo "por empleado de venta";}else{echo "por artículo";}?></h5>
							 <a class="collapse-link pull-right">
								<i class="fa fa-chevron-up"></i>
							</a>	
						</div>
						<div class="ibox-content">
							<div class="form-group">
								<?php if($TipoInforme==1){?>
								<div class="table-responsive">
									<table class="table table-striped table-bordered table-hover dataTables-example" >
									<thead>
									<tr>
										<th>Código cliente</th>
										<th>Nombre cliente</th>
										<th>Cant. Facturas</th>
										<th>Ventas</th>						
										<th>Costos</th>
										<th>Ganancia</th>
										<th>% de ganancia</th>
										<th>Acciones</th>
									</tr>
									</thead>
									<tbody>
									<?php
										if($sw==1){
										while($row=sqlsrv_fetch_array($SQL)){ ?>
											<tr id="tr_Resum<?php echo $row['CardCode'];?>" class="trResum">
												<td><?php echo $row['CardCode'];?></td>
												<td><?php echo $row['CardName'];?></td>
												<td><?php echo $row['TotalDocs'];?></td>
												<td><?php echo number_format($row['TotalVentas'],2);?></td>
												<td><?php echo number_format($row['Costos'],2);?></td>
												<td class="<?php if($row['GananciaBruta']<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo number_format($row['GananciaBruta'],2);?></td>
												<td class="<?php if($row['PrctGanancia']<0 || $row['GananciaBruta']<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo number_format($row['PrctGanancia'],2);?></td>
												<td><a href="#" onClick="VerDetalle('','<?php echo $row['CardCode'];?>','',1);" class="btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Ver detalles</a></td>
											</tr>
									<?php 
										$TotalVentas+=$row['TotalVentas'];
										$TotalCostos+=$row['Costos'];
										$TotalGanancia+=$row['GananciaBruta'];
										}
									}?>
									</tbody>
									</table>
								</div>
								<?php }elseif($TipoInforme==2){?>
								<div class="table-responsive">
									<table class="table table-striped table-bordered table-hover dataTables-example" >
									<thead>
									<tr>
										<th>Nombre empleado</th>
										<th>Cant. Facturas</th>
										<th>Ventas</th>
										<th>Costos</th>
										<th>Ganancia</th>
										<th>% de ganancia</th>
										<th>Acciones</th>
									</tr>
									</thead>
									<tbody>
									<?php
										if($sw==1){
										while($row=sqlsrv_fetch_array($SQL)){ ?>
											<tr id="tr_Resum<?php echo $row['SlpCode'];?>" class="trResum">
												<td><?php echo $row['SlpName'];?></td>
												<td><?php echo $row['TotalDocs'];?></td>
												<td><?php echo number_format($row['TotalVentas'],2);?></td>
												<td><?php echo number_format($row['Costos'],2);?></td>
												<td class="<?php if($row['GananciaBruta']<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo number_format($row['GananciaBruta'],2);?></td>
												<td class="<?php if($row['PrctGanancia']<0 || $row['GananciaBruta']<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo number_format($row['PrctGanancia'],2);?></td>
												<td><a href="#" onClick="VerDetalle('<?php echo $row['SlpCode'];?>','','',2);" class="btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Ver detalles</a></td>
											</tr>
									<?php 
										$TotalVentas+=$row['TotalVentas'];
										$TotalCostos+=$row['Costos'];
										$TotalGanancia+=$row['GananciaBruta'];
										}
									}?>
									</tbody>
									</table>
								</div>
								<?php }elseif($TipoInforme==3){?>
								<div class="table-responsive">
									<table class="table table-striped table-bordered table-hover dataTables-example" >
									<thead>
									<tr>
										<th>Código artículo</th>
										<th>Nombre artículo</th>
										<th>Cantidad</th>
										<th>Ventas</th>
										<th>Costos</th>
										<th>Ganancia</th>
										<th>% de ganancia</th>
										<th>Acciones</th>
									</tr>
									</thead>
									<tbody>
									<?php
										if($sw==1){
										while($row=sqlsrv_fetch_array($SQL)){ ?>
											<tr id="tr_Resum<?php echo $row['ItemCode'];?>" class="trResum">
												<td><?php echo $row['ItemCode'];?></td>
												<td><?php echo $row['ItemName'];?></td>
												<td><?php echo number_format($row['Cantidad'],2);?></td>
												<td><?php echo number_format($row['TotalVentas'],2);?></td>
												<td><?php echo number_format($row['Costos'],2);?></td>
												<td class="<?php if($row['GananciaBruta']<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo number_format($row['GananciaBruta'],2);?></td>
												<td class="<?php if($row['PrctGanancia']<0 || $row['GananciaBruta']<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo number_format($row['PrctGanancia'],2);?></td>
												<td><a href="#" onClick="VerDetalle('','','<?php echo $row['ItemCode'];?>',3);" class="btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Ver detalles</a></td>
											</tr>
									<?php 
										$TotalVentas+=$row['TotalVentas'];
										$TotalCostos+=$row['Costos'];
										$TotalGanancia+=$row['GananciaBruta'];
										}
									}?>
									</tbody>
									</table>
								</div>
								<?php }?>
								<div class="col-lg-12">
									<a href="exportar_excel.php?exp=10&Cons=<?php echo base64_encode(implode(",",$ParamCons));?>&sp=<?php echo base64_encode("usp_InformeVentas");?>">
										<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
									</a>
								</div>
							</div> 
							<div class="row m-t-md">
								<div class="col-lg-10 pull-right">
									<div class="col-lg-3">
										<div class="ibox border-left-right border-top-bottom">
											<div class="ibox-title">
												<h2 class="font-bold">Total ventas</h2>
											</div>
											<div class="ibox-content">
												<h1 class="no-margins"><span class="font-bold text-success"><?php echo "$".number_format($TotalVentas,0);?></span></h1>
											</div>
										</div>
									</div>
									<div class="col-lg-3">
										<div class="ibox border-left-right border-top-bottom">
											<div class="ibox-title">
												<h2 class="font-bold">Total costos</h2>
											</div>
											<div class="ibox-content">
												<h1 class="no-margins"><span class="font-bold text-danger"><?php echo "$".number_format($TotalCostos,0);?></span></h1>
											</div>
										</div>
									</div>
									<div class="col-lg-3">
										<div class="ibox border-left-right border-top-bottom">
											<div class="ibox-title">
												<h2 class="font-bold">Total ganancias</h2>
											</div>
											<div class="ibox-content">
												<h1 class="no-margins"><span class="font-bold <?php if($TotalGanancia<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo "$".number_format($TotalGanancia,0);?></span></h1>
											</div>
										</div>
									</div>
									<div class="col-lg-3">
										<div class="ibox border-left-right border-top-bottom">
											<div class="ibox-title">
												<h2 class="font-bold">Ganancias (%)</h2>
											</div>
											<?php 
//						  						if($TipoAnalisis==1){
//													$PrcntGanancia=(($TotalVentas-$TotalCostos)!=0) ? (($TotalVentas-$TotalCostos)*100)/$TotalVentas : 0; 
//												}else{
//													$PrcntGanancia=($TotalVentas!=0) ? ($TotalGanancia*100)/$TotalVentas : 0; 
//												}	
						  						$PrcntGanancia=($TotalVentas!=0) ? ($TotalGanancia*100)/$TotalVentas : 0; 
											?>
											<div class="ibox-content">
												<h2 class="no-margins"><span class="font-bold <?php if($PrcntGanancia<0 || $TotalGanancia<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo number_format($PrcntGanancia,2)."%";?></span></h2>
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
		 <?php }?>
		<div id="dv_DetalleFact"></div>
		<div id="dv_DetalleLlamada"></div>
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
 <script>
    $(document).ready(function(){
			$("#formBuscar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
			$(".alkin").on('click', function(){
				$('.ibox-content').toggleClass('sk-loading');
			});	
			
			$(".select2").select2();
			
			$('#FechaInicial').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true,
            });
			 $('#FechaFinal').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true,
            }); 
			
//			var options = {
//				url: function(phrase) {
//					return "ajx_buscar_datos_json.php?type=7&id="+phrase;
//				},
//
//				getValue: "NombreBuscarCliente",
//				requestDelay: 400,
//				list: {
//					match: {
//						enabled: true
//					},
//					onClickEvent: function() {
//						var value = $("#NombreCliente").getSelectedItemData().CodigoCliente;
//						$("#Cliente").val(value);
//					}
//				}
//			};
		
			var options_Art = {
				url: function(phrase) {
					return "ajx_buscar_datos_json.php?type=24&id="+phrase;
				},

				getValue: "NombreBuscarArticulo",
				requestDelay: 400,
				list: {
					match: {
						enabled: true
					},
					onClickEvent: function() {
						var value = $("#NombreArticulo").getSelectedItemData().IdArticulo;
						$("#Articulo").val(value).trigger("change");
					}
				}
			};

			//$("#NombreCliente").easyAutocomplete(options);
			$("#NombreArticulo").easyAutocomplete(options_Art);
			
            $('.dataTables-example').DataTable({
                pageLength: 10,
                responsive: false,
                dom: '<"html5buttons"B>lTfgitp',
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
function VerDetalle(cod_emp, cod_cliente, cod_articulo, type){
	var trLine;
	
	if(cod_emp!=""){
		trLine=cod_emp
	}else if(cod_cliente!=""){
		trLine=cod_cliente
	}else if(cod_articulo!=""){
		trLine=cod_articulo
	}

	PonerQuitarClase(trLine,2);
	
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		url: "informe_analisis_venta_detalle.php",
		data:{
			SlpCode:(cod_emp!="") ? cod_emp : '<?php echo $Empleado;?>',
			FInicial:'<?php echo FormatoFecha($FechaInicial);?>',
			FFinal:'<?php echo FormatoFecha($FechaFinal);?>',
			Cliente:(cod_cliente!="") ? cod_cliente : '<?php echo $Cliente;?>',
			Articulo:(cod_articulo!="") ? cod_articulo : '<?php echo $Articulo;?>',
			TipoInforme: type
		},
		success: function(response){
			$('#dv_DetalleFact').html(response).fadeIn();
			$('#dv_DetalleLlamada').html('');
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
}

function VerDetalleFactura(docentry, docnum, ventas, costos, ganancia, prctganancia){
	PonerQuitarClase(docentry);
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		url: "informe_analisis_venta_detalle_fact.php",
		data:{
			DocEntry:docentry,
			DocNum: docnum,
			Ventas: ventas,
			Costos: costos,
			Ganancia: ganancia,
			PrctGanancia: prctganancia
		},
		success: function(response){
			$('#dv_DetalleLlamada').html(response).fadeIn();
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
}
	
function CargarAct(ID, DocNum){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		async: false,
		url: "sn_actividades.php?id="+Base64.encode(ID)+"&objtype=191",
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#TituloModal').html('Actividades relacionadas - OT: '+ DocNum);
			$('#myModal').modal("show");
		}
	});
}
	
function CargarArticulos(doctype, docentry, docnum, nombre_doc, todos_art=0){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		async: false,
		url: "md_articulos_documentos.php",
		data:{
			DocType:doctype,
			DocEntry:docentry,
			Todos:todos_art
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#TituloModal').html(nombre_doc+': '+ docnum);
			$('#myModal').modal("show");
		}
	});
}
	
function PonerQuitarClase(ID,detalle=1){
	if(detalle==1){
		$(".trDetalle").removeClass('bg-light');
		$("#tr_Det"+ID).addClass('bg-light');
	}else{
		$(".trResum").removeClass('bg-light');
		$("#tr_Resum"+ID).addClass('bg-light');
	}
	
}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>