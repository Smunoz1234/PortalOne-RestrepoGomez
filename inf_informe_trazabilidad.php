<?php require_once("includes/conexion.php");
PermitirAcceso(709);
$sw=0;
$EstadoSol="";
$EstadoOC="";
$EstadoEC="";
$Series="";
$Solicitante="";

//Estados
$SQL_Estado=Seleccionar('uvw_tbl_EstadoDocSAP','*');

$SQL_EstadoOC=Seleccionar('uvw_tbl_EstadoDocSAP','*');

$SQL_EstadoEC=Seleccionar('uvw_tbl_EstadoDocSAP','*');

//Empleados
$SQL_Solicitante=Seleccionar('uvw_Sap_tbl_Empleados','*',"UsuarioSAP <> ''",'NombreEmpleado');

//Series de documento
$ParamSerie=array(
	"'".$_SESSION['CodUser']."'",
	"'1470000113'"
);
$SQL_Series=EjecutarSP('sp_ConsultarSeriesDocumentos',$ParamSerie);

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
if(isset($_GET['EstadoSol'])&&$_GET['EstadoSol']!=""){
	$EstadoSol=$_GET['EstadoSol'];
	$sw=1;
}

if(isset($_GET['EstadoOC'])&&$_GET['EstadoOC']!=""){
	$EstadoOC=$_GET['EstadoOC'];
	$sw=1;
}

if(isset($_GET['EstadoEC'])&&$_GET['EstadoEC']!=""){
	$EstadoEC=$_GET['EstadoEC'];
	$sw=1;
}

if(isset($_GET['Series'])&&$_GET['Series']!=""){
	$Series=$_GET['Series'];
	$sw=1;
}

if(isset($_GET['Solicitante'])&&$_GET['Solicitante']!=""){
	$Solicitante=$_GET['Solicitante'];
	$sw=1;
}

if($sw==1){
	$ParamCons=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$EstadoSol."'",
		"'".$EstadoOC."'",
		"'".$EstadoEC."'",
		"'".$Series."'",
		"'".$Solicitante."'"
	);
	$SQL=EjecutarSP('usp_InformeTrazabilidad',$ParamCons);
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Informe de trazabilidad de compras | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {
		
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
                    <h2>Informe trazabilidad de compras</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Compras</a>
                        </li>
                        <li>
                            <a href="#">Informes</a>
                        </li>
                        <li class="active">
                            <strong>Informe trazabilidad de compras</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="inf_informe_trazabilidad.php" method="get" id="formBuscar" class="form-horizontal">
					   <div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Fechas</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>"/>
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" />
								</div>
							</div>
							<label class="col-lg-1 control-label">Serie solicitud</label>
							<div class="col-lg-3">
								<select name="Series" class="form-control" id="Series">
										<option value="">(Todos)</option>
								  <?php while($row_Series=sqlsrv_fetch_array($SQL_Series)){?>
										<option value="<?php echo $row_Series['IdSeries'];?>" <?php if((isset($_GET['Series']))&&(strcmp($row_Series['IdSeries'],$_GET['Series'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Solicitante</label>
							<div class="col-lg-3">
								<select name="Solicitante" class="form-control select2" id="Solicitante">
										<option value="">(Todos)</option>
								  <?php while($row_Solicitante=sqlsrv_fetch_array($SQL_Solicitante)){?>
										<option value="<?php echo $row_Solicitante['UsuarioSAP'];?>" <?php if((isset($_GET['Solicitante']))&&(strcmp($row_Solicitante['UsuarioSAP'],$_GET['Solicitante'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Solicitante['NombreEmpleado'];?></option>
								  <?php }?>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Estado Sol Compra</label>
							<div class="col-lg-3">
								<select name="EstadoSol" class="form-control" id="EstadoSol">
										<option value="">(Todos)</option>
								  <?php while($row_Estado=sqlsrv_fetch_array($SQL_Estado)){?>
										<option value="<?php echo $row_Estado['Cod_Estado'];?>" <?php if((isset($_GET['EstadoSol']))&&(strcmp($row_Estado['Cod_Estado'],$_GET['EstadoSol'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Estado['NombreEstado'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Estado Orden Compra</label>
							<div class="col-lg-3">
								<select name="EstadoOC" class="form-control" id="EstadoOC">
										<option value="">(Todos)</option>
								  <?php while($row_EstadoOC=sqlsrv_fetch_array($SQL_EstadoOC)){?>
										<option value="<?php echo $row_EstadoOC['Cod_Estado'];?>" <?php if((isset($_GET['EstadoOC']))&&(strcmp($row_EstadoOC['Cod_Estado'],$_GET['EstadoOC'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoOC['NombreEstado'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Estado Entrada Compra</label>
							<div class="col-lg-3">
								<select name="EstadoEC" class="form-control" id="EstadoEC">
										<option value="">(Todos)</option>
								  <?php while($row_EstadoEC=sqlsrv_fetch_array($SQL_EstadoEC)){?>
										<option value="<?php echo $row_EstadoEC['Cod_Estado'];?>" <?php if((isset($_GET['EstadoEC']))&&(strcmp($row_EstadoEC['Cod_Estado'],$_GET['EstadoEC'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoEC['NombreEstado'];?></option>
								  <?php }?>
								</select>
							</div>							
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Referencia de colores</label>
							<div class="col-lg-9 font-bold">
								<i class="fa fa-square" style="color: #c4e3f3; font-size: 20px;"></i> Solicitud de compra <br>
								<i class="fa fa-square" style="color: #faf2cc; font-size: 20px;"></i> Orden de compra <br>
								<i class="fa fa-square" style="color: #ebcccc; font-size: 20px;"></i> Entrada de compra
							</div>							
							<div class="col-lg-2">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
					  	<?php if($sw==1){?>
					  	<div class="form-group">
							<div class="col-lg-10 col-md-10">
								<a href="exportar_excel.php?exp=10&Cons=<?php echo base64_encode(implode(",",$ParamCons));?>&sp=<?php echo base64_encode('usp_InformeTrazabilidad');?>">
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
			 <?php //echo $Cons;?>
          <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
			<div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example" >
                    <thead>
						<tr>
							<th class="info">DocNum</th>
							<th class="info">Serie</th>
							<th class="info">Centro de Costos</th>
							<th class="info">Estado Doc</th>
							<th class="info">FechaSolicitud</th>
							<th class="info">ComentariosSolicitud</th>
							<th class="info">NombreSolicitante</th>
							<th class="info">EstadoItem</th>
							<th class="info">Codigo</th>
							<th class="info">Descripcion</th>
							<th class="info">Texto libre</th>
							<th class="info">Cantidad</th>
							<th class="info">Cod Projecto</th>
							<th class="info">Nombre Projecto</th>
							<th class="info">Fecha de autorización SC</th>
							<th class="info">Resultado de autorización SC</th>
							<th class="info">Comentarios de autorización SC</th>
							<th class="warning">Numero de Orden Compra</th>
							<th class="warning">Estado Orden Compra</th>
							<th class="warning">Comentarios Orden de Compra</th>
							<th class="warning">Fecha de Orden Compra</th>
							<th class="warning">Cod Proveedor</th>
							<th class="warning">Nombre Proveedor</th>
							<th class="warning">Cantidad Item OC</th>
							<th class="warning">Precio Item OC</th>
							<th class="warning">Fecha de autorización OC</th>
							<th class="warning">Resultado de autorización OC</th>
							<th class="warning">Comentarios de autorización OC</th>
							<th class="danger">Num Entrada</th>
							<th class="danger">Estado Entrada</th>
							<th class="danger">Fecha Entrada</th>
							<th class="danger">Comentarios Entrada</th>
							<th class="danger">Usuario Entrada</th>
						</tr>
                    </thead>
                    <tbody>
                    <?php
						if($sw==1){
						while($row=sqlsrv_fetch_array($SQL)){ ?>
							<tr class="gradeX">
								<td class="info"><?php if($row['DocNum']!=""){?><a href="solicitud_compra.php?id=<?php echo base64_encode($row['ID_SolicitudCompra']);?>&id_portal=&tl=1" target="_blank"><?php echo $row['DocNum'];?></a><?php }?></td>
								<td class="info"><?php echo $row['DeSeries'];?></td>
								<td class="info"><?php echo utf8_encode($row['Centro de Costos']);?></td>
								<td class="info"><span <?php if($row['Cod_EstadoSol']=='O'){echo "class='label label-info'";}else{echo "class='label label-danger'";}?>><?php echo $row['NombreEstadoSol'];?></span></td>
								<td class="info"><?php if($row['FechaSolicitud']!=""){ echo $row['FechaSolicitud']->format('Y-m-d');}?></td>
								<td class="info"><?php echo utf8_encode($row['ComentariosSolicitud']);?></td>
								<td class="info"><?php echo utf8_encode($row['NombreSolicitante']);?></td>
								<td class="info"><?php echo $row['EstadoItem'];?></td>
								<td class="info"><?php echo $row['Codigo'];?></td>
								<td class="info"><?php echo utf8_encode($row['Descripcion']);?></td>
								<td class="info"><?php echo utf8_encode($row['Texto libre']);?></td>
								<td class="info"><?php echo number_format($row['Cantidad'],2);?></td>
								<td class="info"><?php echo $row['Cod Projecto'];?></td>
								<td class="info"><?php echo utf8_encode($row['Nombre Projecto']);?></td>
								<td class="info"><?php if($row['Fecha de autorizacion SC']!=""){ echo $row['Fecha de autorizacion SC']->format('Y-m-d');}?></td>
								<td class="info"><?php echo utf8_encode($row['Resultado de autorizacion SC']);?></td>
								<td class="info"><?php echo utf8_encode($row['Comentarios de autorizacion SC']);?></td>
								<td class="warning"><?php if($row['ID_OrdenCompra']!=""){?><a href="orden_compra.php?id=<?php echo base64_encode($row['ID_OrdenCompra']);?>&id_portal=&tl=1" target="_blank"><?php echo $row['Numero de Orden Compra'];?></a><?php }?></td>
								<td class="warning"><span <?php if($row['Cod_EstadoOC']=='O'){echo "class='label label-info'";}else{echo "class='label label-danger'";}?>><?php echo $row['NombreEstadoOC'];?></span></td>
								<td class="warning"><?php echo utf8_encode($row['Comentarios Orden de Compra']);?></td>
								<td class="warning"><?php if($row['Fecha de Orden Compra']!=""){ echo $row['Fecha de Orden Compra']->format('Y-m-d');}?></td>
								<td class="warning"><?php echo utf8_encode($row['Cod Proveedor']);?></td>
								<td class="warning"><?php echo utf8_encode($row['Nombre Proveedor']);?></td>
								<td class="warning"><?php echo number_format($row['Cantidad Item OC'],2);?></td>
								<td class="warning"><?php echo number_format($row['Precio Item OC'],2);?></td>
								<td class="warning"><?php if($row['Fecha de autorizacion OC']!=""){ echo $row['Fecha de autorizacion OC']->format('Y-m-d');}?></td>
								<td class="warning"><?php echo utf8_encode($row['Resultado de autorizacion OC']);?></td>
								<td class="warning"><?php echo utf8_encode($row['Comentarios de autorizacion OC']);?></td>
								<td class="danger"><?php if($row['ID_EntradaCompra']!=""){?><a href="entrada_compra.php?id=<?php echo base64_encode($row['ID_EntradaCompra']);?>&id_portal=&tl=1" target="_blank"><?php echo $row['Num Entrada'];?></a><?php }?></td>
								<td class="danger"><span <?php if($row['Cod_EstadoEC']=='O'){echo "class='label label-info'";}else{echo "class='label label-danger'";}?>><?php echo $row['NombreEstadoEC'];?></span></td>
								<td class="danger"><?php if($row['Fecha Entrada']!=""){ echo $row['Fecha Entrada']->format('Y-m-d');}?></td>
								<td class="danger"><?php echo utf8_encode($row['Comentarios Entrada']);?></td>
								<td class="danger"><?php echo utf8_encode($row['Usuario Entrada']);?></td>
							</tr>
					<?php }
						}?>
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
			
			$('.chosen-select').chosen({width: "100%"});
			
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
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>