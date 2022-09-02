<?php require_once("includes/conexion.php");
PermitirAcceso(801);

$sw=0;

//Usuarios
$SQL_UsuariosGestion=Seleccionar('uvw_tbl_Cartera_Gestion','DISTINCT ID_Usuario, NombreUsuario','','NombreUsuario');

//Tipos de gestion
$SQL_TipoGestion=Seleccionar('uvw_tbl_Cartera_TipoGestion','ID_TipoGestion, TipoGestion','','TipoGestion');

//No pago
$SQL_NoPago=Seleccionar('uvw_tbl_Cartera_CausaNoPago','ID_CausaNoPago, CausaNoPago','','CausaNoPago');

if(isset($_GET['TipoGestion'])&&($_GET['TipoGestion']!="")){
	//Tipos de evento
	$SQL_TipoEvento=Seleccionar('uvw_tbl_Cartera_Evento','ID_Evento, NombreEvento',"ID_TipoGestion='".$_GET['TipoGestion']."'",'NombreEvento');
}


if(isset($_GET['Evento'])&&($_GET['Evento']!="")){
	//Tipos de resultado
	$SQL_TipoResultado=Seleccionar('uvw_tbl_Cartera_ResultadoGestion','ID_ResultadoGestion, ResultadoGestion',"ID_Evento='".$_GET['Evento']."'",'ResultadoGestion');
}


//Fechas
if(isset($_GET['FechaInicial'])&&$_GET['FechaInicial']!=""){
	$FechaInicial=$_GET['FechaInicial'];
	$sw=1;
}else{
	//Restar 7 dias a la fecha actual
	$fecha = date('Y-m-d');
	$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasGestionar").' day');
	$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
	$FechaInicial=$nuevafecha;
}
if(isset($_GET['FechaFinal'])&&$_GET['FechaFinal']!=""){
	$FechaFinal=$_GET['FechaFinal'];
	$sw=1;
}else{
	$FechaFinal=date('Y-m-d');
}

//Filtros
$Filtro="";//Filtro
if(isset($_GET['TipoGestion'])&&$_GET['TipoGestion']!=""){
	$Filtro.=" and ID_TipoGestion='".$_GET['TipoGestion']."'";
	$sw=1;
}

if(isset($_GET['Evento'])&&$_GET['Evento']!=""){
	$Filtro.=" and ID_Evento='".$_GET['Evento']."'";
	$sw=1;
}

if(isset($_GET['Resultado'])&&$_GET['Resultado']!=""){
	$Filtro.=" and ID_ResultadoGestion='".$_GET['Resultado']."'";
	$sw=1;
}

if(isset($_GET['NoPago'])&&$_GET['NoPago']!=""){
	$Filtro.=" and ID_CausaNoPago='".$_GET['NoPago']."'";
	$sw=1;
}

if(isset($_GET['AcuerdoPago'])&&$_GET['AcuerdoPago']!=""){
	$Filtro.=" and AcuerdoPago='".$_GET['AcuerdoPago']."'";
	$sw=1;
}

if(isset($_GET['FechaCompPago'])&&$_GET['FechaCompPago']!=""){
	$Filtro.=" and FechaCompromiso='".$_GET['FechaCompPago']."'";
	$sw=1;
}

if(isset($_GET['ClienteGestion'])&&$_GET['ClienteGestion']!=""){
	$Filtro.=" and CardCode='".$_GET['ClienteGestion']."'";
	$sw=1;
}

if(isset($_GET['UsuarioGestion'])&&($_GET['UsuarioGestion']!="")){
	$FilUsuario="";
	for($i=0;$i<count($_GET['UsuarioGestion']);$i++){
		if($i==0){
			$FilUsuario.="'".$_GET['UsuarioGestion'][$i]."'";
		}else{
			$FilUsuario.=",'".$_GET['UsuarioGestion'][$i]."'";
		}		
	}
	$Filtro.=" and ID_Usuario IN (".$FilUsuario.")";	
	$sw=1;
}

if($sw==1){
	$Where="(CAST(FechaRegistro AS DATE) Between '".FormatoFecha($FechaInicial)."' and '".FormatoFecha($FechaFinal)."') $Filtro";	
	$SQL=Seleccionar('uvw_tbl_Cartera_Gestion','*',$Where);
	$SQLCons=ReturnCons('uvw_tbl_Cartera_Gestion','*',$Where);
}
//echo $Cons;
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Consultar gestiones | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {
		$("#NombreClienteGestion").change(function(){
			var NomCliente=document.getElementById("NombreClienteGestion");
			var Cliente=document.getElementById("ClienteGestion");
			if(NomCliente.value==""){
				Cliente.value="";
			}	
		});
		$("#TipoGestion").change(function(){
			var TG=document.getElementById('TipoGestion').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=9&id="+TG,
				success: function(response){
					$('#Evento').html(response).fadeIn();
					//$('#Evento').trigger('change');
				}
			});
		});
		$("#Evento").change(function(){
			var EV=document.getElementById('Evento').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=10&id="+EV,
				success: function(response){
					$('#Resultado').html(response).fadeIn();
					//$('#Resultado').trigger('change');
				}
			});
		});
	});
</script>
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
                    <h2>Consultar gestiones</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Gesti&oacute;n de cartera</a>
                        </li>
                        <li class="active">
                            <strong>Consultar gestiones</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="consultar_gestiones.php" method="get" id="formBuscar" class="form-horizontal">
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
							<label class="col-lg-1 control-label">Cliente</label>
							<div class="col-lg-3">
								<input name="ClienteGestion" type="hidden" id="ClienteGestion" value="<?php if(isset($_GET['ClienteGestion'])&&($_GET['ClienteGestion']!="")){ echo $_GET['ClienteGestion'];}?>">
								<input name="NombreClienteGestion" type="text" class="form-control" id="NombreClienteGestion" placeholder="Para TODOS, dejar vacio..." value="<?php if(isset($_GET['NombreClienteGestion'])&&($_GET['NombreClienteGestion']!="")){ echo $_GET['NombreClienteGestion'];}?>">
							</div>
							<label class="col-lg-1 control-label">Usuario</label>
							<div class="col-lg-3">
								<select data-placeholder="(Todos)" name="UsuarioGestion[]" class="form-control chosen-select" multiple id="UsuarioGestion">
								  <?php $j=0; 
									while($row_UsuariosGestion=sqlsrv_fetch_array($SQL_UsuariosGestion)){?>
										<option value="<?php echo $row_UsuariosGestion['ID_Usuario'];?>" <?php if((isset($_GET['UsuarioGestion'][$j])&&($_GET['UsuarioGestion'][$j])!="")&&(strcmp($row_UsuariosGestion['ID_Usuario'],$_GET['UsuarioGestion'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_UsuariosGestion['NombreUsuario'];?></option>
								  <?php }?>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Tipo gesti&oacute;n</label>
							<div class="col-lg-3">
								<select name="TipoGestion" class="form-control" id="TipoGestion">
										<option value="">(Todos)</option>
								  <?php while($row_TipoGestion=sqlsrv_fetch_array($SQL_TipoGestion)){?>
										<option value="<?php echo $row_TipoGestion['ID_TipoGestion'];?>" <?php if((isset($_GET['TipoGestion']))&&(strcmp($row_TipoGestion['ID_TipoGestion'],$_GET['TipoGestion'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoGestion['TipoGestion'];?></option>
								  <?php }?>
								</select>
               	  			</div>
							<label class="col-lg-1 control-label">Evento</label>
							<div class="col-lg-3">
								<select name="Evento" class="form-control" id="Evento">
										<option value="">(Todos)</option>
								  <?php if(isset($_GET['TipoGestion'])&&($_GET['TipoGestion']!="")){
											while($row_TipoEvento=sqlsrv_fetch_array($SQL_TipoEvento)){?>
												<option value="<?php echo $row_TipoEvento['ID_Evento'];?>" <?php if((isset($_GET['Evento']))&&(strcmp($row_TipoEvento['ID_Evento'],$_GET['Evento'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoEvento['NombreEvento'];?></option>
								  <?php 	}
										}?>
								</select>
               	  			</div>
							<label class="col-lg-1 control-label">Resultado</label>
							<div class="col-lg-3">
								<select name="Resultado" class="form-control" id="Resultado">
										<option value="">(Todos)</option>
								  <?php if(isset($_GET['Evento'])&&($_GET['Evento']!="")){
											while($row_TipoResultado=sqlsrv_fetch_array($SQL_TipoResultado)){?>
												<option value="<?php echo $row_TipoResultado['ID_ResultadoGestion'];?>" <?php if((isset($_GET['Resultado']))&&(strcmp($row_TipoResultado['ID_ResultadoGestion'],$_GET['Resultado'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoResultado['ResultadoGestion'];?></option>
								  <?php 	}
										}?>
								</select>
               	  			</div>							
						</div>
					 	<div class="form-group">
							<label class="col-lg-1 control-label">Causa no pago</label>
							<div class="col-lg-3">
								<select name="NoPago" class="form-control" id="NoPago">
										<option value="">(Todos)</option>
										<option value="1" <?php if((isset($_GET['NoPago']))&&(strcmp(1,$_GET['NoPago'])==0)){ echo "selected=\"selected\"";}?>>(NINGUNA)</option>
								  <?php while($row_NoPago=sqlsrv_fetch_array($SQL_NoPago)){?>
										<option value="<?php echo $row_NoPago['ID_CausaNoPago'];?>" <?php if((isset($_GET['NoPago']))&&(strcmp($row_NoPago['ID_CausaNoPago'],$_GET['NoPago'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_NoPago['CausaNoPago'];?></option>
								  <?php }?>
								</select>
               	  			</div>
							<label class="col-lg-1 control-label">Acuerdo de pago</label>
							<div class="col-lg-3">
								<select name="AcuerdoPago" class="form-control" id="AcuerdoPago">
									<option value="">(Todos)</option>
									<option value="1" <?php if((isset($_GET['AcuerdoPago']))&&(strcmp(1,$_GET['AcuerdoPago'])==0)){ echo "selected=\"selected\"";}?>>SI</option>
									<option value="0" <?php if((isset($_GET['AcuerdoPago']))&&(strcmp(0,$_GET['AcuerdoPago'])==0)){ echo "selected=\"selected\"";}?>>NO</option>
								</select>
               	  			</div>
							<div class="form-group">
								<label class="col-lg-1 control-label">Fecha comp.</label>
								<div class="col-lg-3 input-group date">
									 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaCompPago" type="text" class="form-control" id="FechaCompPago" value="<?php if(isset($_GET['FechaCompPago'])&&($_GET['FechaCompPago']!="")){ echo $_GET['FechaCompPago'];}?>" readonly="readonly" placeholder="YYYY-MM-DD">
								</div>
							</div>
					  	</div>
					  	<div class="form-group">
							<div class="col-lg-12 pull-right">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
					  	</div>
					  <?php if($sw==1){?>
					  	<div class="form-group">
							<div class="col-lg-12">
								<a href="exportar_excel.php?exp=1&Cons=<?php echo base64_encode($SQLCons);?>">
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
		<?php if($sw==1){?> 
          <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
			<div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example" >
                    <thead>
                    <tr>
						<th>Tipo gestión</th>
						<th>Cliente</th>
						<th>Destino</th>
						<th>Evento</th>
						<th>Resultado</th>
						<th>Fecha C. Pago</th>
						<th>Fecha Utl Pago</th>
                        <th>Causa de no pago</th>
						<th>Acuerdo de pago</th>
                        <th>Fecha registro</th>
						<th>Usuario</th>
						<th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while($row=sqlsrv_fetch_array($SQL)){ ?>
						 <tr class="gradeX tooltip-demo">
							<td><?php echo $row['TipoGestion'];?><?php if($row['CallFile']!=""){?><a href="recorddownload.php?file=<?php echo base64_encode($row['ID_Gestion']);?>" target="_blank" class="btn btn-link btn-xs" title="Descargar grabación"><i class="fa fa-phone"></i></a><?php }?></td>
							<td><?php echo $row['NombreCliente'];?></td>
							<td><?php echo $row['Destino'];?></td>
							<td><?php echo $row['NombreEvento'];?></td>
							<td><?php echo $row['ResultadoGestion'];?></td>
							<td><?php if($row['FechaCompromiso']!=""){echo $row['FechaCompromiso']->format('Y-m-d');}?></td>
							<td><?php if($row['FechaUltPago']!=""){echo $row['FechaUltPago']->format('Y-m-d');}?></td>
							<td><?php echo $row['CausaNoPago'];?></td>
							<td><?php if($row['AcuerdoPago']==1){echo "SI <a href='sapdownload.php?id=".base64_encode('11')."&type=".base64_encode('2')."&DocKey=".base64_encode($row['ID_Acuerdo'])."' target='_blank' class='btn btn-link btn-xs' title='Descargar acuerdo'><i class='fa fa-download'></i>";}else{echo "NO";}?></td>
							<td><?php echo $row['FechaRegistro']->format('Y-m-d H:i');?></td>
							<td><?php echo $row['NombreUsuario'];?></td>
							<td><a href="gestionar_cartera.php?Clt=<?php echo base64_encode($row['CardCode']);?>" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
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
        <?php include("includes/footer.php"); ?>

    </div>
</div>
<?php include("includes/pie.php"); ?>
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
			$('#FechaCompPago').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true
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
						var value = $("#NombreClienteGestion").getSelectedItemData().CodigoCliente;
						$("#ClienteGestion").val(value).trigger("change");
					}
				}
			};

			$("#NombreClienteGestion").easyAutocomplete(options);
			
            $('.dataTables-example').DataTable({
                pageLength: 25,
                dom: '<"html5buttons"B>lTfgitp',
				order: [[ 5, "desc" ]],
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