<?php 
if(isset($_GET['id'])&&$_GET['id']!=""){
	require_once("includes/conexion.php");
	PermitirAcceso(106);
//require_once("includes/conexion_hn.php");

//Nombre del formulario
$SQL_Cat=Seleccionar("uvw_tbl_Categorias","ID_Categoria, NombreCategoria, NombreCategoriaPadre","ID_Categoria = '".base64_decode($_GET['id'])."'");
$row_Cat=sqlsrv_fetch_array($SQL_Cat);
$sw=0;
	
//Estado actividad
$SQL_EstadoLlamada=Seleccionar('uvw_tbl_EstadoLlamada','*');
	
//Estado criticidad
$SQL_EstadoCriticidad=Seleccionar('uvw_tbl_EstadoCriticidad','*');
	
//Zona
$SQL_Zonas=Seleccionar('uvw_Sap_tbl_Clientes_Zonas','*');

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
$Cliente="";
$Sucursal="";
$Estado="";
$EstadoCriticidad="";
$Revisado="";
$Zona="";
	
//Revisdo
if(isset($_GET['Cliente'])&&$_GET['Cliente']!=""){
	$Cliente=$_GET['Cliente'];
	$sw=1;
}
	
if(isset($_GET['Sucursal'])&&$_GET['Sucursal']!=""){
	$Sucursal=$_GET['Sucursal'];
	$sw=1;
}

//Estado
if(isset($_GET['Estado'])&&$_GET['Estado']!=""){
	$Estado=$_GET['Estado'];
	$sw=1;
}
	
//Estado criticidad
if(isset($_GET['EstadoCriticidad'])&&$_GET['EstadoCriticidad']!=""){
	$EstadoCriticidad=$_GET['EstadoCriticidad'];
	$sw=1;
}
	
//Revisdo
if(isset($_GET['Revisado'])&&$_GET['Revisado']!=""){
	$Revisado=$_GET['Revisado'];
	$sw=1;
}
	
//Zona
if(isset($_GET['Zona'])&&$_GET['Zona']!=""){
	$Zona=$_GET['Zona'];
	$sw=1;
}

if($sw==1){
	$Param=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Cliente."'",
		"'".$Sucursal."'",
		"'".$Estado."'",
		"'".$Revisado."'",
		"'".$EstadoCriticidad."'",
		"'".$Zona."'",
		"'0'",
		"'".$_SESSION['CodUser']."'"
	);
	$SQL=EjecutarSP('sp_ConsultarHallazgos',$Param);
}
//echo $Cons;

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $row_Cat['NombreCategoria'];?> | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_FrmAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El hallazgo ha sido registrado exitosamente. ID: ".base64_decode($_GET['new'])."',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_FrmUpd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El hallazgo ha sido actualizado exitosamente. ID: ".base64_decode($_GET['new'])."',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_FrmDel"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El hallazgo ha sido eliminado exitosamente.',
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
		$("#Cliente").change(function(){
			var Cliente=document.getElementById("Cliente");
			$.ajax({
				type: "POST",
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Cliente.value,
				success: function(response){
					$('#Sucursal').html(response).fadeIn();
				}
			});	
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
                    <h2><?php echo $row_Cat['NombreCategoria'];?></h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#"><?php echo $row_Cat['NombreCategoriaPadre'];?></a>
                        </li>
                        <li class="active">
                            <strong><?php echo $row_Cat['NombreCategoria'];?></strong>
                        </li>
                    </ol>
                </div>
			<?php if(PermitirFuncion(107)){?>
                <div class="col-sm-4">
                    <div class="title-action">
                        <a href="frm_hallazgos.php?frm=<?php echo $_GET['id'];?>" class="alkin btn btn-primary"><i class="fa fa-plus-circle"></i> Crear nuevo panorama</a>
                    </div>
                </div>
			<?php }?>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="gestionar_hallazgos.php" method="get" id="formBuscar" class="form-horizontal">
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
								<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Para TODOS, dejar vacio..." value="<?php if(isset($_GET['NombreCliente'])&&($_GET['NombreCliente']!="")){ echo $_GET['NombreCliente'];}?>">
							</div>
							<label class="col-lg-1 control-label">Sucursal</label>
							<div class="col-lg-3">
							 <select id="Sucursal" name="Sucursal" class="form-control">
								<option value="">(Todos)</option>
								<?php 
								 if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){//Cuando se ha seleccionado una opción
									 if(PermitirFuncion(205)){
										$Where="CodigoCliente='".$_GET['Cliente']."'";
										$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","NombreSucursal",$Where);
									 }else{
										$Where="CodigoCliente='".$_GET['Cliente']."' and ID_Usuario = ".$_SESSION['CodUser'];
										$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal",$Where);	
									 }
									 while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
										<option value="<?php echo $row_Sucursal['NombreSucursal'];?>" <?php if(strcmp($row_Sucursal['NombreSucursal'],$_GET['Sucursal'])==0){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['NombreSucursal'];?></option>
								<?php }
								 }?>
							</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Estado</label>
							<div class="col-lg-3">
								<select name="Estado" class="form-control" id="Estado">
										<option value="">(Todos)</option>
								  <?php while($row_EstadoLlamada=sqlsrv_fetch_array($SQL_EstadoLlamada)){?>
										<option value="<?php echo $row_EstadoLlamada['Cod_Estado'];?>" <?php if((isset($_GET['Estado']))&&(strcmp($row_EstadoLlamada['Cod_Estado'],$_GET['Estado'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoLlamada['NombreEstado'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Revisado</label>
							<div class="col-lg-3">
								<select name="Revisado" class="form-control" id="Revisado">
									<option value="">(Todos)</option>
								 	<option value="No revisado" <?php if(isset($_GET['Revisado'])&&($_GET['Revisado']=="No revisado")){ echo "selected=\"selected\"";}?>>No revisado</option>
									<option value="Revisado" <?php if(isset($_GET['Revisado'])&&($_GET['Revisado']=="Revisado")){ echo "selected=\"selected\"";}?>>Revisado</option>
								</select>
							</div>
							<label class="col-lg-1 control-label">Estado criticidad</label>
							<div class="col-lg-3">
								<select name="EstadoCriticidad" class="form-control" id="EstadoCriticidad">
									<option value="">(Todos)</option>
								  <?php while($row_EstadoCriticidad=sqlsrv_fetch_array($SQL_EstadoCriticidad)){?>
										<option value="<?php echo $row_EstadoCriticidad['IdEstadoCriticidad'];?>" <?php if((isset($_GET['EstadoCriticidad']))&&(strcmp($row_EstadoCriticidad['IdEstadoCriticidad'],$_GET['EstadoCriticidad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoCriticidad['DeEstadoCriticidad'];?></option>
								  <?php }?>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Zona</label>
							<div class="col-lg-3">
								<select name="Zona" class="form-control" id="Zona">
										<option value="">(Todos)</option>
									 <?php while($row_Zonas=sqlsrv_fetch_array($SQL_Zonas)){?>
										<option value="<?php echo $row_Zonas['ID_Zona'];?>" <?php if((isset($_GET['Zona']))&&(strcmp($row_Zonas['ID_Zona'],$_GET['Zona'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Zonas['DE_Zona'];?></option>
								  <?php }?>
								</select>
							</div>	
							<div class="col-lg-8 pull-right">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
					  <?php if($sw==1){?> 
					  	<div class="form-group">
							<div class="col-lg-12">
								<a href="exportar_excel.php?exp=2&Cons=<?php echo base64_encode(implode(",",$Param));?>">
									<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
								</a>
							</div>							
					  	</div>	
					  <?php }?>
					  <input type="hidden" name="id" id="id" value="<?php echo $_GET['id'];?>" />
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
                        <th>ID</th>
						<th>Técnico</th>
                        <th>Tipo visita</th>
						<th>Cliente</th>
						<th>Sucursal</th>
						<th>Área</th>
						<th>Hallazgo</th>
						<th>Recomendación</th>
                        <th>Fecha creación</th>
						<th>Fecha actualización</th>
						<th>Estado criticidad</th>
						<th>Estado</th>						
						<th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while($row=sqlsrv_fetch_array($SQL)){ ?>
						 <tr class="gradeX">
							<td><?php echo $row['ID_Frm'];?></td>
							<td><?php if($row['NombreEmpleado']!=""){echo $row['NombreEmpleado'];}else{echo "(Sin asignar)";}?></td>
							<td><?php echo $row['DeTipoVisita'];?></td>
							<td><?php echo $row['NombreCliente'];?></td>
							<td><?php echo $row['NombreSucursal'];?></td>
							<td><?php echo $row['DeArea'];?></td>
							<td><?php if(strlen($row['Hallazgo'])>140){echo substr($row['Hallazgo'],0,140)."...";}else{echo $row['Hallazgo'];}?></td>
							<td><?php if(strlen($row['Recomendaciones'])>140){echo substr($row['Recomendaciones'],0,140)."...";}else{echo $row['Recomendaciones'];}?></td>
							<td><?php echo $row['FechaCreacion']->format('Y-m-d H:i');?></td>
							<td><?php if($row['FechaAct']!=""){echo $row['FechaAct']->format('Y-m-d');}?></td>
							<td <?php if($row['IdEstadoCriticidad']=='1'){echo "class='bg-danger'";}elseif($row['IdEstadoCriticidad']=='2'){echo "class='bg-warning'";}else{echo "class='bg-primary'";}?>><?php echo $row['DeEstadoCriticidad'];?></td>
							<td <?php if($row['Cod_Estado']=='-3'){echo "class='bg-danger'";}elseif($row['Cod_Estado']=='-2'){echo "class='bg-warning'";}else{echo "class='bg-primary'";}?>><?php echo $row['NombreEstado'];?></td>
							<td><?php if(PermitirFuncion(107)){?><a href="frm_hallazgos.php?id=<?php echo base64_encode($row['ID_Frm']);?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('gestionar_hallazgos.php');?>&frm=<?php echo $_GET['id'];?>" class="alkin btn btn-link btn-xs"><i class="fa fa-folder-open-o"></i> Editar</a> - <?php }?><a href="sapdownload.php?id=<?php echo base64_encode('13');?>&type=<?php echo base64_encode('2');?>&DocKey=<?php echo base64_encode($row['ID_Frm']);?>" target="_blank" class="btn btn-link btn-xs"><i class="fa fa-download"></i> Descargar</a></td>
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
						var value = $("#NombreCliente").getSelectedItemData().CodigoCliente;
						$("#Cliente").val(value).trigger("change");
					}
				}
			};

			$("#NombreCliente").easyAutocomplete(options);
			
            $('.dataTables-example').DataTable({
                pageLength: 25,
				order: [[ 0, "desc" ]],
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
<?php sqlsrv_close($conexion);}?>