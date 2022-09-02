<?php require_once("includes/conexion.php");
PermitirAcceso(303);

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
$Filtro="";//Filtro
if(isset($_GET['TipoActividad'])&&$_GET['TipoActividad']!=""){
	$Filtro.=" and ID_TipoActividad='".$_GET['TipoActividad']."'";
	$sw=1;
}
if(isset($_GET['EstadoActividad'])&&$_GET['EstadoActividad']!=""){
	$Filtro.=" and IdEstadoActividad='".$_GET['EstadoActividad']."'";
	$sw=1;
}
if(isset($_GET['TipoTarea'])&&$_GET['TipoTarea']!=""){
	$Filtro.=" and TipoTarea='".$_GET['TipoTarea']."'";
	$sw=1;
}
if(isset($_GET['ClienteActividad'])){
	if($_GET['ClienteActividad']!=""){//Si se selecciono el cliente
		$Filtro.=" and ID_CodigoCliente='".$_GET['ClienteActividad']."'";
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
if(isset($_GET['EmpleadoActividad'])&&$_GET['EmpleadoActividad']!=""){
	$FilEmpleado="";
	for($i=0;$i<count($_GET['EmpleadoActividad']);$i++){
		if($i==0){
			$FilEmpleado.="'".$_GET['EmpleadoActividad'][$i]."'";
		}else{
			$FilEmpleado.=",'".$_GET['EmpleadoActividad'][$i]."'";
		}		
	}
	$Filtro.=" and ID_Empleado IN (".$FilEmpleado.")";	
	$sw=1;
}

if(isset($_GET['UsuarioActividad'])&&($_GET['UsuarioActividad']!="")){
	$FilUsuario="";
	if($_GET['UsuarioActividad']==""){
		$_GET['UsuarioActividad']=$_SESSION['CodUser'];		
	}
	for($i=0;$i<count($_GET['UsuarioActividad']);$i++){
		if($i==0){
			$FilUsuario.="'".$_GET['UsuarioActividad'][$i]."'";
		}else{
			$FilUsuario.=",'".$_GET['UsuarioActividad'][$i]."'";
		}		
	}
	$Filtro.=" and IdAsignadoPor IN (".$FilUsuario.")";	
	$sw=1;
}elseif(!isset($_GET['UsuarioActividad'])&&(!isset($_GET['FechaInicial']))){
	$_GET['UsuarioActividad'][0]=$_SESSION['CodUser'];
	$FilUsuario="";
	$FilUsuario.="'".$_GET['UsuarioActividad'][0]."'";
	$Filtro.=" and IdAsignadoPor IN (".$FilUsuario.")";
	$sw=1;	
}

if(isset($_GET['BuscarDato'])&&$_GET['BuscarDato']!=""){
	$Filtro.=" and (NombreContacto LIKE '%".$_GET['BuscarDato']."%' OR NombreSucursal LIKE '%".$_GET['BuscarDato']."%' OR ComentariosActividad LIKE '%".$_GET['BuscarDato']."%' OR NotasActividad LIKE '%".$_GET['BuscarDato']."%' OR DE_AsuntoActividad LIKE '%".$_GET['BuscarDato']."%' OR TituloActividad LIKE '%".$_GET['BuscarDato']."%' OR ID_Actividad LIKE '%".$_GET['BuscarDato']."%' OR NombreCliente LIKE '%".$_GET['BuscarDato']."%')";
	$sw=1;
}

if($sw==1){
	$Cons="Select * From uvw_Sap_tbl_Actividades Where (FechaHoraInicioActividad Between '".FormatoFecha($FechaInicial,"00:00:00")."' and '".FormatoFecha($FechaFinal,"23:59:59")."') $Filtro ORDER BY ID_Actividad DESC";
	$SQL=sqlsrv_query($conexion,$Cons);
}else{
	$Cons="Select TOP 100 * From uvw_Sap_tbl_Actividades Where (FechaHoraInicioActividad Between '".FormatoFecha($FechaInicial,"00:00:00")."' and '".FormatoFecha($FechaFinal,"23:59:59")."') $Filtro ORDER BY ID_Actividad DESC";
	$SQL=sqlsrv_query($conexion,$Cons);
}
//echo $Cons;

if(isset($_GET['IDActividad'])&&$_GET['IDActividad']!=""){
	$Where="ID_Actividad LIKE '%".$_GET['IDActividad']."%'";
	$SQL=Seleccionar('uvw_Sap_tbl_Actividades','*',$Where);
}else{
	
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Gestionar actividades | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_ActAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La actividad ha sido creada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_UpdActAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La actividad ha sido actualizada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_File_delete"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El registro ha sido eliminado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_DelAct"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La actividad ha sido eliminado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_OpenAct"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La actividad ha sido abierta nuevamente.',
                icon: 'success'
            });
		});		
		</script>";
}
?>
<script type="text/javascript">
	$(document).ready(function() {
		$("#NombreClienteActividad").change(function(){
			var NomCliente=document.getElementById("NombreClienteActividad");
			var Cliente=document.getElementById("ClienteActividad");
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
                    <h2>Gestionar actividades</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Servicios</a>
                        </li>
                        <li class="active">
                            <strong>Gestionar actividades</strong>
                        </li>
                    </ol>
                </div>
			<?php if(PermitirFuncion(304)){?>
                <div class="col-sm-4">
                    <div class="title-action">
                        <a href="actividad.php" class="alkin btn btn-primary"><i class="fa fa-plus-circle"></i> Crear nueva actividad</a>
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
				  <form action="gestionar_actividades.php" method="get" id="formBuscar" class="form-horizontal">
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
							<label class="col-lg-1 control-label">Tipo</label>
							<div class="col-lg-3">
								<select name="TipoActividad" class="form-control" id="TipoActividad">
										<option value="">(Todos)</option>
								  <?php while($row_TipoActividad=sqlsrv_fetch_array($SQL_TipoActividad)){?>
										<option value="<?php echo $row_TipoActividad['ID_TipoActividad'];?>" <?php if((isset($_GET['TipoActividad']))&&(strcmp($row_TipoActividad['ID_TipoActividad'],$_GET['TipoActividad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoActividad['DE_TipoActividad'];?></option>
								  <?php }?>
								</select>
               	  			</div>
							<label class="col-lg-1 control-label">Tipo tarea</label>
							<div class="col-lg-2">
								<select name="TipoTarea" class="form-control " id="TipoTarea">
									<option value="" selected="selected">(Todos)</option>
									<option value="Externa" <?php if((isset($_GET['TipoTarea']))&&($_GET['TipoTarea']=='Externa')){ echo "selected=\"selected\"";}?>>Externa</option>
							<option value="Interna" <?php if((isset($_GET['TipoTarea']))&&($_GET['TipoTarea']=='Interna')){ echo "selected=\"selected\"";}?>>Interna</option>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Cliente</label>
							<div class="col-lg-3">
								<input name="ClienteActividad" type="hidden" id="ClienteActividad" value="<?php if(isset($_GET['ClienteActividad'])&&($_GET['ClienteActividad']!="")){ echo $_GET['ClienteActividad'];}?>">
								<input name="NombreClienteActividad" type="text" class="form-control" id="NombreClienteActividad" placeholder="Para TODOS, dejar vacio..." value="<?php if(isset($_GET['NombreClienteActividad'])&&($_GET['NombreClienteActividad']!="")){ echo $_GET['NombreClienteActividad'];}?>">
							</div>
							<label class="col-lg-1 control-label">Estado</label>
							<div class="col-lg-3">
								<select name="EstadoActividad" class="form-control" id="EstadoActividad">
										<option value="">(Todos)</option>
								  <?php while($row_EstadoActividad=sqlsrv_fetch_array($SQL_EstadoActividad)){?>
										<option value="<?php echo $row_EstadoActividad['Cod_Estado'];?>" <?php if((isset($_GET['EstadoActividad']))&&(strcmp($row_EstadoActividad['Cod_Estado'],$_GET['EstadoActividad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoActividad['NombreEstado'];?></option>
								  <?php }?>
								</select>
							</div>							
							<label class="col-lg-1 control-label">Buscar dato</label>
							<div class="col-lg-2">
								<input name="BuscarDato" type="text" class="form-control" id="BuscarDato" maxlength="100" value="<?php if(isset($_GET['BuscarDato'])&&($_GET['BuscarDato']!="")){ echo $_GET['BuscarDato'];}?>">
							</div>
						</div>
					 	<div class="form-group">
							<label class="col-lg-1 control-label">Asignado por</label>
							<div class="col-lg-3">
								<select data-placeholder="(Todos)" name="UsuarioActividad[]" class="form-control  chosen-select" multiple id="UsuarioActividad">
								  <?php $j=0; 
									while($row_UsuariosActividad=sqlsrv_fetch_array($SQL_UsuariosActividad)){?>
										<option value="<?php echo $row_UsuariosActividad['IdAsignadoPor'];?>" <?php if((isset($_GET['UsuarioActividad'][$j])&&($_GET['UsuarioActividad'][$j])!="")&&(strcmp($row_UsuariosActividad['IdAsignadoPor'],$_GET['UsuarioActividad'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_UsuariosActividad['DeAsignadoPor'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Asignado a</label>
							<div class="col-lg-3">
								<select data-placeholder="(Todos)" name="EmpleadoActividad[]" class="form-control chosen-select" multiple id="EmpleadoActividad">
								  <?php $j=0; 
									while($row_EmpleadoActividad=sqlsrv_fetch_array($SQL_EmpleadoActividad)){?>
										<option value="<?php echo $row_EmpleadoActividad['ID_Empleado'];?>" <?php if((isset($_GET['EmpleadoActividad'][$j])&&($_GET['EmpleadoActividad'][$j])!="")&&(strcmp($row_EmpleadoActividad['ID_Empleado'],$_GET['EmpleadoActividad'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_EmpleadoActividad['NombreEmpleado'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Id actividad</label>
							<div class="col-lg-2">
								<input name="IDActividad" type="text" class="form-control" id="IDActividad" maxlength="100" value="<?php if(isset($_GET['IDActividad'])&&($_GET['IDActividad']!="")){ echo $_GET['IDActividad'];}?>">
							</div>
							<div class="col-lg-1">
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
						<th>Asignado por</th>
						<th>Asignado a</th>
						<th>Titulo</th>                                         
                        <th>Cliente</th>
                        <th>Sucursal</th>
						<th>Fecha inicio</th>
						<th>Fecha limite</th>
						<th>Dias venc.</th>
						<th>Orden servicio</th>
						<th>Estado</th>
						<th>Acciones</th>
						<th><i class="fa fa-refresh"></i></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while($row=sqlsrv_fetch_array($SQL)){ 
							$DVenc=DiasTranscurridos(date('Y-m-d'),$row['FechaFinActividad']);
						?>
						 <tr class="gradeX tooltip-demo">
							<td><?php echo $row['ID_Actividad'];?></td>
							<td><?php echo $row['DeAsignadoPor'];?></td>
							<td><?php if($row['NombreEmpleado']!=""){echo $row['NombreEmpleado'];}else{echo "(Sin asignar)";}?></td>
							<td><?php echo $row['TituloActividad'];?></td>						
							<td><?php echo $row['NombreCliente'];?></td>
							<td><?php echo $row['NombreSucursal'];?></td>
							<td><?php if($row['FechaHoraInicioActividad']!=""){ echo $row['FechaHoraInicioActividad']->format('Y-m-d H:s');}else{?><p class="text-muted">--</p><?php }?></td>
							<td><?php if($row['FechaHoraFinActividad']!=""){ echo $row['FechaHoraFinActividad']->format('Y-m-d H:s');}else{?><p class="text-muted">--</p><?php }?></td>
							<td><p class='<?php echo $DVenc[0];?>'><?php echo $DVenc[1];?></p></td>
							<td><?php if($row['ID_OrdenServicioActividad']!=0){?><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_LlamadaServicio']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('gestionar_actividades.php');?>&tl=1"><?php echo $row['ID_OrdenServicioActividad'];?></a><?php }else{echo "--";}?></td>							 
							<td><span <?php if($row['IdEstadoActividad']=='N'){echo "class='label label-info'";}else{echo "class='label label-danger'";}?>><?php echo $row['DeEstadoActividad'];?></span></td>							 
							<td><a href="actividad.php?id=<?php echo base64_encode($row['ID_Actividad']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('gestionar_actividades.php');?>&tl=1" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
							<td><?php if($row['Metodo']==0){?><i class="fa fa-check-circle text-info" title="Sincronizado con SAP"></i><?php }else{?><i class="fa fa-times-circle text-danger" title="Error de sincronización con SAP"></i><?php }?></td>
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
				format: 'yyyy-mm-dd'
            });
			 $('#FechaFinal').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
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
						var value = $("#NombreClienteActividad").getSelectedItemData().CodigoCliente;
						$("#ClienteActividad").val(value).trigger("change");
					}
				}
			};

			$("#NombreClienteActividad").easyAutocomplete(options);
			
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