<?php require_once("includes/conexion.php");
PermitirAcceso(1002);
$sw=0;//Para saber si ya se selecciono un cliente y mostrar la información


//Empleados de venta
$SQL_EmpVentas=Seleccionar('uvw_Sap_tbl_EmpleadosVentas','*');

//Estado documento
$SQL_Estado=Seleccionar('uvw_tbl_EstadoOportunidad','*');

//Filtros
$Filtro="";//Filtro

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

if(isset($_GET['Cliente'])&&$_GET['Cliente']!=""){
	$Filtro.=" and IdClienteOportunidad='".$_GET['Cliente']."'";
	$sw=1;
}

if(isset($_GET['Estado'])&&$_GET['Estado']!=""){
	$Filtro.=" and IdEstadoOportunidad='".$_GET['Estado']."'";
	$sw=1;
}

if(isset($_GET['EmpVentas'])&&$_GET['EmpVentas']!=""){
	$Filtro.=" and IdEmpleadoOportunidad='".$_GET['EmpVentas']."'";
	$sw=1;
}


if($sw==1){	
	$Cons="Select * From uvw_Sap_tbl_Oportunidades WHERE (FechaCreacion Between '$FechaInicial' and '$FechaFinal') $Filtro";
	$SQL=sqlsrv_query($conexion,$Cons);
}

//echo $Cons;
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Consultar oportunidad | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&$_GET['a']==base64_encode("OK_LMTAdd")){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'La Lista de materiales ha sido creada exitosamente.',
				icon: 'success'
			});
		});		
		</script>";
}
if(isset($_GET['a'])&&$_GET['a']==base64_encode("OK_LMTUpd")){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'La Lista de materiales ha sido actualizada exitosamente.',
				icon: 'success'
			});
		});		
		</script>";
}
?>
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
                    <h2>Consultar oportunidad</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">CRM</a>
                        </li>
						<li>
                            <a href="#">Consultas</a>
                        </li>
                        <li class="active">
                            <strong>Consultar oportunidad</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					<?php include("includes/spinner.php"); ?>
				  <form action="consultar_oportunidad.php" method="get" id="formBuscar" class="form-horizontal">
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
						<label class="col-lg-1 control-label">Cliente</label>
						<div class="col-lg-3">
							<input name="Cliente" type="hidden" id="Cliente" value="<?php if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){ echo $_GET['Cliente'];}?>">
							<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Ingrese para buscar..." value="<?php if(isset($_GET['NombreCliente'])&&($_GET['NombreCliente']!="")){ echo $_GET['NombreCliente'];}?>">
						</div>
						<label class="col-lg-1 control-label">Estado</label>
						<div class="col-lg-3">
							<select name="Estado" class="form-control" id="Estado">
									<option value="">(Todos)</option>
							  <?php while($row_Estado=sqlsrv_fetch_array($SQL_Estado)){?>
									<option value="<?php echo $row_Estado['Cod_Estado'];?>" <?php if((isset($_GET['Estado']))&&(strcmp($row_Estado['Cod_Estado'],$_GET['Estado'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Estado['NombreEstado'];?></option>
							  <?php }?>
							</select>
						</div>
					  </div>
					  <div class="form-group">
						<label class="col-lg-1 control-label">Empleado de ventas</label>
						<div class="col-lg-3">
							<select name="EmpVentas" class="form-control" id="EmpVentas">
									<option value="">(Todos)</option>
							  <?php while($row_EmpVentas=sqlsrv_fetch_array($SQL_EmpVentas)){?>
									<option value="<?php echo $row_EmpVentas['ID_EmpVentas'];?>" <?php if((isset($_GET['EmpVentas']))&&(strcmp($row_EmpVentas['ID_EmpVentas'],$_GET['EmpVentas'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EmpVentas['DE_EmpVentas'];?></option>
							  <?php }?>
							</select>
						</div>
						<div class="col-lg-8">
							<button type="submit" class="btn btn-outline btn-info pull-right"><i class="fa fa-search"></i> Buscar</button>
						</div>
					</div>
				 </form>
			</div>
			</div>
		  </div>
         <br>
			 <?php //echo $Cons;?>
		<?php if($sw==1){?>
          <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					<?php include("includes/spinner.php"); ?>
				<div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example" >
                    <thead>
                    <tr>
						<th>ID Oportunidad</th>
						<th>Nombre Oportunidad</th>
						<th>Nombre cliente</th>
						<th>Tipo</th>
						<th>Fecha creación</th>
						<th>Fecha inicio</th>
						<th>Fecha cierre prevista</th>
						<th>Fecha cierre</th>
						<th>Empleado de ventas</th>
						<th>Propietario</th>
						<th>% de cierre</th>
						<th>Estado</th>
						<th>Acciones</th>
					</tr>
                    </thead>
                    <tbody>
                    <?php while($row=sqlsrv_fetch_array($SQL)){ ?>
						<tr class="gradeX">
							<td><?php echo $row['ID_Oportunidad'];?></td>
							<td><?php echo $row['NombreOportunidad'];?></td>
							<td><?php echo $row['DeClienteOportunidad'];?></td>
							<td><?php echo $row['DeTipoOportunidad'];?></td>
							<td><?php echo $row['FechaCreacion']->format('Y-m-d');?></td>
							<td><?php echo ($row['FechaInicio']!="") ? $row['FechaInicio']->format('Y-m-d') : "";?></td>
							<td><?php echo ($row['FechaCierrePrevista']!="") ? $row['FechaCierrePrevista']->format('Y-m-d') : "";?></td>
							<td><?php echo ($row['FechaCierre']!="") ? $row['FechaCierre']->format('Y-m-d') : "";?></td>
							<td><?php echo $row['DeEmpleadoOportunidad'];?></td>
							<td><?php echo $row['NombrePropietario'];?></td>
							<td><?php echo number_format($row['PorcentajeOportunidad'],0)."%";?></td>
							<td><span <?php if($row['IdEstadoOportunidad']=='O'){echo "class='label label-warning'";}elseif($row['IdEstadoOportunidad']=='L'){echo "class='label label-danger'";}else{echo "class='label label-primary'";}?>><?php echo $row['DeEstadoOportunidad'];?></span></td>
							<td><a href="oportunidad.php?id=<?php echo base64_encode($row['ID_Oportunidad']);?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('consultar_oportunidad.php');?>" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
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
			$(".btn-link").on('click', function(){
				$('.ibox-content').toggleClass('sk-loading');
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
			
            $('.dataTables-example').DataTable({
                pageLength: 25,
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