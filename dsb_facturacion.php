<?php require_once("includes/conexion.php"); 

$sw=0;

//Tipo de llamada
$SQL_TipoLlamadas=Seleccionar('uvw_Sap_tbl_TipoLlamadas','*','','DeTipoLlamada');

//Fechas
if(isset($_GET['FechaInicial'])&&$_GET['FechaInicial']!=""){
	$FechaInicial=$_GET['FechaInicial'];
	$sw=1;
}else{
	$FechaInicial=PrimerDiaMes(date('m'));
}
if(isset($_GET['FechaFinal'])&&$_GET['FechaFinal']!=""){
	$FechaFinal=$_GET['FechaFinal'];
	$sw=1;
}else{
	$FechaFinal=UltimoDiaMes(date('m'));
}

$Cliente = isset($_GET['Cliente']) ? $_GET['Cliente'] : "";
$Sucursal = isset($_GET['Sucursal']) ? $_GET['Sucursal'] : "";
$Series = isset($_GET['Series']) ? $_GET['Series'] : "";
$TipoLlamada= isset($_GET['TipoLlamada']) ? implode(",",$_GET['TipoLlamada']) : "";

//Serie de llamada
$ParamSerie=array(
	"'".$_SESSION['CodUser']."'",
	"'191'"
);
$SQL_Series=EjecutarSP('sp_ConsultarSeriesDocumentos',$ParamSerie);


$ParamCont=array(
	"'1'",
	"'".FormatoFecha($FechaInicial)."'",
	"'".FormatoFecha($FechaFinal)."'",
	"'".$Cliente."'",
	"'".$Sucursal."'",
	"'".$Series."'",
	"'".$TipoLlamada."'"
);

$SQL_Cont=EjecutarSP('sp_DashboardFacturacion',$ParamCont);
$row_Cont=sqlsrv_fetch_array($SQL_Cont);

$Param=array(
	"'2'",
	"'".FormatoFecha($FechaInicial)."'",
	"'".FormatoFecha($FechaFinal)."'",
	"'".$Cliente."'",
	"'".$Sucursal."'",
	"'".$Series."'",
	"'".$TipoLlamada."'"
);

$SQL=EjecutarSP('sp_DashboardFacturacion',$Param);



?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Dashboard facturación | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.modal-dialog{
		width: 70% !important;
	}
	.modal-footer{
		border: 0px !important;
	}
</style>

<script>
$(document).ready(function(){
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
		$.ajax({
			type: "POST",
			url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Cliente.value+"&todos=1",
			success: function(response){
				$('#Sucursal').html(response);
				$('#Sucursal').trigger('change');
			}
		});	
	});
	
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
        <div class="row page-wrapper wrapper-content animated fadeInRight">
			<div class="modal inmodal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="TituloModal"></h4>
						</div>
						<div class="modal-body" id="ContenidoModal">							
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-success m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
						</div>
					</div>
				</div>
			</div>
			<div class="row m-b-md">
				<div class="col-lg-12">
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<h2>Bienvenido <?php echo $_SESSION['NomUser'];?></h2>
					</div>
				</div>
			</div>
		  <div class="row m-b-md">
			<div class="col-lg-12">
				<div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="dsb_facturacion.php" method="get" id="formBuscar" class="form-horizontal">
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Fechas <span class="text-danger">*</span></label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" autocomplete="off" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>"/>
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" autocomplete="off" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" />
								</div>
							</div>
							<label class="col-lg-1 control-label">Cliente</label>
							<div class="col-lg-3">
								<input name="Cliente" type="hidden" id="Cliente" value="<?php if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){ echo $_GET['Cliente'];}?>">
								<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Para TODOS, dejar vacio..." value="<?php if(isset($_GET['NombreCliente'])&&($_GET['NombreCliente']!="")){ echo $_GET['NombreCliente'];}?>">
							</div>
							<label class="col-lg-1 control-label">Sucursal</label>
							<div class="col-lg-3">
								 <select id="Sucursal" name="Sucursal" class="form-control select2">
									<option value="">(Todos)</option>
									<?php 
									 if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){//Cuando se ha seleccionado una opción
										 if(PermitirFuncion(205)){
											$Where="CodigoCliente='".$_GET['Cliente']."'";
											$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","NombreSucursal, NumeroLinea",$Where);
										 }else{
											$Where="CodigoCliente='".$_GET['Cliente']."' and ID_Usuario = ".$_SESSION['CodUser'];
											$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal, NumeroLinea",$Where);	
										 }
										 while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
											<option value="<?php echo $row_Sucursal['NombreSucursal'];?>" <?php if(strcmp($row_Sucursal['NombreSucursal'],$_GET['Sucursal'])==0){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['NombreSucursal'];?></option>
									<?php }
									 }?>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Serie</label>
							<div class="col-lg-3">
								<select name="Series" class="form-control" id="Series">
										<option value="">(Todos)</option>
								  <?php
											while($row_Series=sqlsrv_fetch_array($SQL_Series)){?>
											<option value="<?php echo $row_Series['IdSeries'];?>" <?php if((isset($_GET['Series']))&&(strcmp($row_Series['IdSeries'],$_GET['Series'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries'];?></option>
								  <?php 	}
										?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Tipo llamada</label>
							<div class="col-lg-3">
								<select data-placeholder="(Todos)" name="TipoLlamada[]" class="form-control select2" id="TipoLlamada" multiple>
								  <?php $j=0;
									while($row_TipoLlamadas=sqlsrv_fetch_array($SQL_TipoLlamadas)){?>
										<option value="<?php echo $row_TipoLlamadas['IdTipoLlamada'];?>" <?php if((isset($_GET['TipoLlamada'][$j])&&($_GET['TipoLlamada'][$j]!=""))&&(strcmp($row_TipoLlamadas['IdTipoLlamada'],$_GET['TipoLlamada'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_TipoLlamadas['DeTipoLlamada'];?></option>
								  <?php }?>
								</select>
							</div>	
							<div class="col-lg-4">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
				 </form>
				</div>
			</div>
		  </div>
		  <div class="row m-b-md">
            <div class="col-lg-3">
              <div class="ibox ">
                <div class="ibox-title text-danger"></span>
                  <h5>OT sin factura</h5>
                </div>
                <div class="ibox-content">
                  <h1 class="no-margins"><?php echo $row_Cont['CantServiciosSinFactura'];?></h1>
				</div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="ibox ">
                <div class="ibox-title text-danger"></span>
                  <h5>Total sin facturar ($)</h5>
                </div>
                <div class="ibox-content">
                  <h1 class="no-margins"><?php echo number_format($row_Cont['TotalServiciosSinFactura'],2);?></h1>
				</div>
              </div>
            </div>
          </div>			
		  <div class="row">
           <div class="col-md-12">
			    <div class="ibox-content form-horizontal">
					<?php include("includes/spinner.php"); ?>
					<div class="table-responsive">
							<table class="table table-bordered table-hover dataTables">
							<caption><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Resumen por cliente</h3></caption>
							<thead>
							<tr>
								<th>#</th>
								<th>Código cliente</th>
								<th>Nombre cliente</th>
								<th>Cant. artículos sin facturar</th>
								<th>Servicio más antiguo</th>
								<th>Total ($)</th>
							</tr>
							</thead>
							<tbody>
							<?php $i=1;
							  while($row=sqlsrv_fetch_array($SQL)){  ?>
								<tr>
									<td><?php echo $i;?></td>
									<td class="drill" data-cardcode='<?php echo $row['CodigoCliente'];?>'><a href='javascript:void(0);'><i class='fa fa-plus-square-o'></i> <?php echo $row['CodigoCliente'];?></a></td>
									<td><?php echo $row['NombreCliente'];?></td>
									<td><?php echo number_format($row['Cant'],2);?></td>
									<td><?php echo $row['FechaInicioActividad'];?></td>
									<td><?php echo number_format($row['Total'],2);?></td>
								</tr>
							<?php $i++;}?>
							</tbody>
							</table>
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
			todayHighlight: true
		});
		 $('#FechaFinal').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			format: 'yyyy-mm-dd',
			todayHighlight: true
		}); 
		 
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
		 
		var table= $('.dataTables').DataTable({
			pageLength: 10,
			responsive: true,
			searching: false,
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
		 
	$('.dataTables tbody').on('click', 'td.drill', function (ev) {

        let tbid = $(this).closest('table').attr('id');
        let tr = $(this).closest('tr');
        let row = table.row( tr );
        
        let cardcode = $(this).data('cardcode');
 
		//Busco el div col-* mas arriba para obtener la clase de bootstrap que tiene
        //var column = $(this).closest('table').parent().parent().parent();

        if ( row.child.isShown() ) {
            // Si el row esta abierto, cierralo
            row.child.hide();
            tr.removeClass('shown');

            // contract column on drill down hide
//            var restoreclass=tr.attr('class').replace('odd ','').replace('even ','');
//            tr.removeClass(restoreclass);
//            column.removeClass('col-md-12');
//            column.addClass('col-'+restoreclass);
        }
        else {
            // Open this row
			$('.ibox-content').toggleClass('sk-loading',true);
            $.get( 
				'dsb_facturacion_detalle_nivel1.php',
				{
					id:cardcode,
					finicial:'<?php echo FormatoFecha($FechaInicial);?>',
					ffinal: '<?php echo FormatoFecha($FechaFinal);?>',
					suc: '<?php echo $Sucursal;?>',
					serie: '<?php echo $Series;?>',
					tllamada: '<?php echo $TipoLlamada;?>'
				}
			).done( function( data ) {
				//Mostrar los datos
				row.child(data).show(); 

				// expand column on drill down, retract on hide
//				var columnclass = $(column).attr('class').substring(4); //col-[md-12]
//				column.removeClass('col-'+columnclass);
//				column.addClass('col-md-12'); 
				tr.addClass('shown');
//				tr.addClass(columnclass);
				$('.ibox-content').toggleClass('sk-loading',false);
            });
            
        }
	});
});
	
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
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>