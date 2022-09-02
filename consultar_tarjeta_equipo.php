<?php require_once("includes/conexion.php");
PermitirAcceso(1601);

$sw=0;

//Clientes
/*if(PermitirFuncion(205)){
	$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes","CodigoCliente, NombreCliente","",'NombreCliente');	
}else{
	$Where="ID_Usuario = ".$_SESSION['CodUser'];
	$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);	
}*/

//Empleados
$SQL_EmpleadoActividad=Seleccionar('uvw_Sap_tbl_Empleados','*','','NombreEmpleado');

//Usuarios
$SQL_UsuariosActividad=Seleccionar('uvw_Sap_tbl_Actividades','DISTINCT IdAsignadoPor, DeAsignadoPor','','DeAsignadoPor');

//Estado actividad
$SQL_EstadoActividad=Seleccionar('uvw_tbl_EstadoActividad','*');

//Tipos de actividad
$SQL_TipoActividad=Seleccionar('uvw_Sap_tbl_TiposActividad','*','','DE_TipoActividad');

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
	$sw=1;
}else{
	$FechaFinal=date('Y-m-d');
}

//Filtros
$Filtro="TipoEquipo <> ''";
if(isset($_GET['TipoEquipo'])&&$_GET['TipoEquipo']!=""){
	$Filtro.=" and TipoEquipo='".$_GET['TipoEquipo']."'";
	$sw=1;
}
if (isset($_GET['SerialEquipo'])&&$_GET['SerialEquipo']!=""){
	$Filtro.=" and (SerialFabricante LIKE '%".$_GET['SerialEquipo']."%' OR SerialInterno LIKE '%".$_GET['SerialEquipo']."%')";
	$sw=1;
}
if(isset($_GET['EstadoEquipo'])&&$_GET['EstadoEquipo']!=""){
	$Filtro.=" and CodEstado='".$_GET['EstadoEquipo']."'";
	$sw=1;
}
if(isset($_GET['ClienteEquipo'])){
	if($_GET['ClienteEquipo']!=""){//Si se selecciono el cliente
		$Filtro.=" and CardCode='".$_GET['ClienteEquipo']."'";
		$sw=1;	
	}else{
		if(!PermitirFuncion(205)){
			$Where="ID_Usuario = ".$_SESSION['CodUser'];
			$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);
			$k=0;
			$FiltroCliente="";
			while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){
				//Clientes
				$WhereCliente[$k]="ID_CodigoCliente='".$row_Cliente['CodigoCliente']."'";
				$FiltroCliente=implode(" OR ",$WhereCliente);
				
				$k++;
			}
			if($FiltroCliente!=""){
				$Filtro.=" and (".$FiltroCliente.")";
			}/*else{
				$Filtro.=" and (ID_CodigoCliente='".$FiltroCliente."')";
			}*/
			
			$Where="ID_Usuario = ".$_SESSION['CodUser'];
			$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);
		}
	}
}else{//Si no se selecciono el cliente
	if(!PermitirFuncion(205)){
		$Where="ID_Usuario = ".$_SESSION['CodUser'];
		$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);
		$k=0;
		$FiltroCliente="";
		while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){
			//Clientes
			$WhereCliente[$k]="ID_CodigoCliente='".$row_Cliente['CodigoCliente']."'";
			$FiltroCliente=implode(" OR ",$WhereCliente);
			//$FiltroSuc=implode(" OR ",$WhereSuc);		
			$k++;
		}
		if($FiltroCliente!=""){
			$Filtro.=" and (".$FiltroCliente.")";
		}/*else{
			$Filtro.=" and (ID_CodigoCliente='".$FiltroCliente."')";
		}*/
		
		//Recargar consultas para los combos
		$Where="ID_Usuario = ".$_SESSION['CodUser'];
		$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);
		
	}
}

if(isset($_GET['BuscarDato'])&&$_GET['BuscarDato']!=""){
	$Filtro.=" and (Calle LIKE '%".$_GET['BuscarDato']."%' OR CodigoPostal LIKE '%".$_GET['BuscarDato']."%' OR Barrio LIKE '%".$_GET['BuscarDato']."%' OR Ciudad LIKE '%".$_GET['BuscarDato']."%' OR Distrito LIKE '%".$_GET['BuscarDato']."%')";
	$sw=1;
}

if($sw==1){
	$Cons="Select * From uvw_Sap_tbl_TarjetasEquipos Where $Filtro ORDER BY IdTarjetaEquipo DESC";
	$SQL=sqlsrv_query($conexion,$Cons);
}else{
	$Cons="Select TOP 100 * From uvw_Sap_tbl_TarjetasEquipos Where $Filtro ORDER BY IdTarjetaEquipo DESC";
	$SQL=sqlsrv_query($conexion,$Cons);
}
//echo $Cons;

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Tarjetas de equipos | <?php echo NOMBRE_PORTAL;?></title>
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
</head>

<body>

<div id="wrapper">

    <?php include("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Tarjetas de equipos</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="#">Mantenimiento</a>
                        </li>
                        <li class="active">
                            <strong>Tarjetas de equipos</strong>
                        </li>
                    </ol>
                </div>
			<?php if(PermitirFuncion(304)){?>
                <div class="col-sm-4">
                    <div class="title-action">
                        <a href="tarjeta_equipo.php" class="alkin btn btn-primary"><i class="fa fa-plus-circle"></i> Crear nueva tarjeta de equipo</a>
                    </div>
                </div>
			<?php }?>
               <?php  //echo $Cons;?>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="consulta_tarjeta_equipo.php" method="get" id="formBuscar" class="form-horizontal">
					   <div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Tipo de equipo</label>
							<div class="col-lg-3">
								<select name="TipoEquipo" class="form-control" id="TipoEquipo">
									<option value="">(Todos)</option>
									<option value="P" <?php if((isset($_GET['TipoEquipo']))&&(strcmp("P",$_GET['TipoEquipo'])==0)){ echo "selected=\"selected\"";}?>>Compras</option>
									<option value="R" <?php if((isset($_GET['TipoEquipo']))&&(strcmp("R",$_GET['TipoEquipo'])==0)){ echo "selected=\"selected\"";}?>>Ventas</option>
								</select>
               	  			</div>
							<label class="col-lg-1 control-label">Serial</label>
							<div class="col-lg-3">
								<input name="SerialEquipo" type="text" class="form-control" id="SerialEquipo" maxlength="100" value="<?php if(isset($_GET['SerialEquipo'])&&($_GET['SerialEquipo']!="")){ echo $_GET['SerialEquipo'];}?>" placeholder="Serial fabricante o interno">
							</div>
							<label class="col-lg-1 control-label">Estado de equipo</label>
							<div class="col-lg-3">
								<select name="EstadoEquipo" class="form-control" id="EstadoEquipo">
									<option value="">(Todos)</option>
									<option value="A" <?php if((isset($_GET['EstadoEquipo']))&&(strcmp("A",$_GET['EstadoEquipo'])==0)){ echo "selected=\"selected\"";}?>>Activo</option>
									<option value="R" <?php if((isset($_GET['EstadoEquipo']))&&(strcmp("R",$_GET['EstadoEquipo'])==0)){ echo "selected=\"selected\"";}?>>Devuelto</option>
									<option value="T" <?php if((isset($_GET['EstadoEquipo']))&&(strcmp("T",$_GET['EstadoEquipo'])==0)){ echo "selected=\"selected\"";}?>>Finalizado</option>
									<option value="L" <?php if((isset($_GET['EstadoEquipo']))&&(strcmp("L",$_GET['EstadoEquipo'])==0)){ echo "selected=\"selected\"";}?>>Concedido en prestamo</option>
									<option value="I" <?php if((isset($_GET['EstadoEquipo']))&&(strcmp("I",$_GET['EstadoEquipo'])==0)){ echo "selected=\"selected\"";}?>>En laboratorio de reparación</option>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Cliente</label>
							<div class="col-lg-3">
								<input name="ClienteEquipo" type="hidden" id="ClienteEquipo" value="<?php if(isset($_GET['ClienteEquipo'])&&($_GET['ClienteEquipo']!="")){ echo $_GET['ClienteEquipo'];}?>">
								<input name="NombreClienteEquipo" type="text" class="form-control" id="NombreClienteEquipo" placeholder="Para TODOS, dejar vacio..." value="<?php if(isset($_GET['NombreClienteEquipo'])&&($_GET['NombreClienteEquipo']!="")){ echo $_GET['NombreClienteEquipo'];}?>">
							</div>
							<label class="col-lg-1 control-label">Buscar dato</label>
							<div class="col-lg-3">
								<input name="BuscarDato" type="text" class="form-control" id="BuscarDato" maxlength="100" value="<?php if(isset($_GET['BuscarDato'])&&($_GET['BuscarDato']!="")){ echo $_GET['BuscarDato'];}?>">
							</div>
							<div class="col-lg-4">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
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
						<th>Núm.</th>
						<th>Código cliente</th>
						<th>Cliente</th>
						<th>Serial fabricante</th>                                         
                        <th>Serial interno</th>
                        <th>Código de artículo</th>
						<th>Artículo</th>
						<th>Tipo de equipo</th>
						<th>Estado</th>
						<th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while($row=sqlsrv_fetch_array($SQL)){?>
						 <tr class="gradeX tooltip-demo">
							<td><?php echo $row['IdTarjetaEquipo'];?></td>
							<td><?php echo $row['CardCode'];?></td>
							<td><?php echo $row['CardName'];?></td>
							<td><?php echo $row['SerialFabricante'];?></td>
							<td><?php echo $row['SerialInterno'];?></td>
							<td><?php echo $row['ItemCode'];?></td>
							<td><?php echo $row['ItemName'];?></td>
							<td>
								<?php if($row['TipoEquipo'] === 'P') { echo 'Compras'; } elseif($row['TipoEquipo'] === 'R') { echo 'Ventas'; } ?>
							</td>
							<td>
								<?php if($row['CodEstado']=='A') { ?>
									<span  class='label label-info'>Activo</span>
								<?php } elseif ($row['CodEstado']=='R') { ?>
									<span  class='label label-danger'>Devuelto</span>
								<?php } elseif ($row['CodEstado']=='T') { ?>
									<span  class='label label-success'>Finalizado</span>
								<?php } elseif ($row['CodEstado']=='L') { ?>
									<span  class='label label-secondary'>Concedido en préstamo</span>
								<?php } elseif ($row['CodEstado']=='I') { ?>
									<span  class='label label-warning'>En laboratorio de reparación</span>
								<?php } ?>
							</td>
							<td><a href="tarjeta_equipo.php?id=<?php echo base64_encode($row['IdTarjetaEquipo']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('consulta_tarjeta_equipo.php');?>&tl=1" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
						</tr>
					<?php }?>
                    </tbody>
                    </table>
              </div>
			</div>
			 </div> 
          </div>
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
				todayHighlight: true,
				format: 'yyyy-mm-dd'
            });
			 $('#FechaFinal').datepicker({
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