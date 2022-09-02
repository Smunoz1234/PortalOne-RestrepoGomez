<?php require_once("includes/conexion.php");
PermitirAcceso(208);
$WhereSuc=array();//Para ir agregando los filtros normalmente
$WhereCliente=array();//Para ir agregando los filtros normalmente
$i=0;//Controlar el indice del array
$Filtro="";//Filtro
$sw_suc=0;//Mostrar las sucursales del cliente seleccionado
$sw=0;//Mostrar datos

//Clientes
if(PermitirFuncion(205)){
	$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes","CodigoCliente, NombreCliente","");	
}else{
	$Where="ID_Usuario = ".$_SESSION['CodUser'];
	$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);	
}

//Fechas
if(isset($_GET['FechaInicial'])&&$_GET['FechaInicial']!=""){
	$sw=1;
	$FechaInicial=$_GET['FechaInicial'];
}else{
	//Restar dias a la fecha actual
	$fecha = date('Y-m-d');
	$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasGestionar").' day');
	$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
	$FechaInicial=$nuevafecha;
}
if(isset($_GET['FechaFinal'])&&$_GET['FechaFinal']!=""){
	$sw=1;
	$FechaFinal=$_GET['FechaFinal'];
}else{
	$FechaFinal=date('Y-m-d');
}

if(isset($_GET['Cliente'])){
	$sw=1;
	if($_GET['Cliente']!=""){//Si se selecciono el cliente
		$Filtro.=" and CardCode='".$_GET['Cliente']."'";
		$sw_suc=1;
		if(isset($_GET['Sucursal'])){
			if($_GET['Sucursal']==""){
				//Sucursales
				if(PermitirFuncion(205)){
					$Where="CodigoCliente='".$_GET['Cliente']."'";
					$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","NombreSucursal",$Where);
				}else{
					$Where="CodigoCliente='".$_GET['Cliente']."' and ID_Usuario = ".$_SESSION['CodUser'];
					$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal",$Where);	
				}
				$j=0;
				unset($WhereSuc);
				$WhereSuc = array(); 
				while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){
					$WhereSuc[$j]="ID_Sucursal='".$row_Sucursal['NombreSucursal']."'";
					$j++;
				}
				$FiltroSuc=implode(" OR ",$WhereSuc);
				$Filtro.=" and (".$FiltroSuc.")";
			}else{
				$Filtro.=" and ID_Sucursal='".$_GET['Sucursal']."'";
			}
		}
		
	}else{
		if(!PermitirFuncion(205)){
			$Where="ID_Usuario = ".$_SESSION['CodUser'];
			$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);
			$k=0;
			while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){
				
				//Sucursales
				$Where="CodigoCliente='".$row_Cliente['CodigoCliente']."' and ID_Usuario = ".$_SESSION['CodUser'];
				$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal",$Where);	

				$j=0;
				unset($WhereSuc);
				$WhereSuc = array();  
				while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){
					$WhereSuc[$j]="ID_Sucursal='".$row_Sucursal['NombreSucursal']."'";
					$j++;
				}
				
				$FiltroSuc=implode(" OR ",$WhereSuc);
				
				if($k==0){
					$Filtro.=" AND (CardCode='".$row_Cliente['CodigoCliente']."' AND (".$FiltroSuc."))";
				}else{
					$Filtro.=" OR (CardCode='".$row_Cliente['CodigoCliente']."' AND (".$FiltroSuc."))";
				}				
				
				$k++;
			}
			//Recargar consultas para los combos
			$Where="ID_Usuario = ".$_SESSION['CodUser'];
			$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);
		}
	}
}else{//Si no se selecciono el cliente
		if(!PermitirFuncion(205)){
			$Where="ID_Usuario = ".$_SESSION['CodUser'];
			$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);
			$k=0;
			while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){
				
				//Sucursales
				$Where="CodigoCliente='".$row_Cliente['CodigoCliente']."' and ID_Usuario = ".$_SESSION['CodUser'];
				$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal",$Where);	

				$j=0;
				unset($WhereSuc);
				$WhereSuc = array();  
				while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){
					$WhereSuc[$j]="ID_Sucursal='".$row_Sucursal['NombreSucursal']."'";
					$j++;
				}
				
				$FiltroSuc=implode(" OR ",$WhereSuc);
				
				if($k==0){
					$Filtro.=" AND (CardCode='".$row_Cliente['CodigoCliente']."' AND (".$FiltroSuc."))";
				}else{
					$Filtro.=" OR (CardCode='".$row_Cliente['CodigoCliente']."' AND (".$FiltroSuc."))";
				}				
				
				$k++;
			}
			//Recargar consultas para los combos
			$Where="ID_Usuario = ".$_SESSION['CodUser'];
			$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);
		}
	}
if(isset($_GET['Categoria'])){
	if(($_GET['Categoria'])!=""){
		$Filtro.=" and ID_Categoria='".$_GET['Categoria']."'";
	}else{
		$Filtro.=" and ID_TipoCategoria=2";
	}	
}else{
	$Filtro.=" and ID_TipoCategoria=2";
}
if($sw==1){
	$Cons="Select * From uvw_tbl_archivos Where (Fecha Between '".FormatoFecha($FechaInicial)."' and '".FormatoFecha($FechaFinal)."') $Filtro Order by Fecha DESC";
}else{
	$Cons="";
}

//echo $Cons;
$SQL=sqlsrv_query($conexion,$Cons);


?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Gestionar informes | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_UpdFile"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'Los archivos han sido cargados exitosamente.',
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
?>
<script type="text/javascript">
	$(document).ready(function() {//Cargar los almacenes dependiendo del proyecto
		$("#Cliente").change(function(){
			$.ajax({
				type: "POST",
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+document.getElementById('Cliente').value,
				success: function(response){
					$('#Sucursal').html(response);
					$('#Sucursal').trigger('change');
				}
			});
		});
	});
</script>
<script>
		function EliminarRegistro(id){
			if(id!=""){
				Swal.fire({
					title: "¿Estás seguro?",
					text: "Se eliminará la información de este registro. Este proceso no tiene reversión.",
					icon: "warning",
					showCancelButton: true,
					confirmButtonText: "Si, estoy seguro",
					cancelButtonText: "Cancelar"
				}).then((result) => {
					if (result.isConfirmed) {
						$('.ibox-content').toggleClass('sk-loading',true);
						location.href='registro.php?P=13&type=2&id='+id;
					}
				});
			}
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
                    <h2>Gestionar informes</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Gesti&oacute;n de archivos</a>
                        </li>
                        <li class="active">
                            <strong>Gestionar informes</strong>
                        </li>
                    </ol>
                </div>
                <div class="col-sm-4">
                    <div class="title-action">
                        <a href="informes_add.php" class="btn btn-primary"><i class="fa fa-upload"></i> Cargar informes</a>
                    </div>
                </div>
               <?php  //echo $Cons;?>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				 <div class="col-lg-12">
			    	<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
			  			<form action="gestionar_informes.php" method="get" id="formBuscar" class="form-horizontal">
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
						  </div>
						  <div class="form-group">
								<label class="col-lg-1 control-label">Cliente</label>
								<div class="col-lg-3">
									<select name="Cliente" class="form-control select2" id="Cliente">
										<option value="" selected="selected">(Todos)</option>
									<?php while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){?>
										<option value="<?php echo $row_Cliente['CodigoCliente'];?>" <?php if((isset($_GET['Cliente']))&&(strcmp($row_Cliente['CodigoCliente'],$_GET['Cliente'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Cliente['NombreCliente'];?></option>
									<?php }?>
									</select>
								</div>
								<label class="col-lg-1 control-label">Sucursal</label>
								<div class="col-lg-2">
								 <select id="Sucursal" name="Sucursal" class="form-control select2">
									<option value="">(Todos)</option>
									<?php 
									 if($sw_suc==1){//Mostrar el cliente seleccionado
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
									 }elseif($sw_suc==2){//Si no se ha seleccionado ningun cliente
										  while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
											<option value="<?php echo $row_Sucursal['NombreSucursal'];?>"><?php echo $row_Sucursal['NombreSucursal'];?></option>
									<?php }								 
									 }?>
								</select>
								</div>
								<label class="col-lg-1 control-label">Categoría</label>
								<div class="col-lg-2">
								 <?php include_once("includes/select_categorias_informes.php"); ?>
								</div>
								<div class="col-lg-1 pull-right">
									<button type="submit" class="btn btn-outline btn-success"><i class="fa fa-search"></i> Buscar</button>
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
			<div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example" >
                    <thead>
                    <tr>
                        <th>Nombre del archivo</th>
                        <th>Descripci&oacute;n</th>
                        <th>Fecha archivo</th>
                        <th>Cliente</th>
                        <th>Sucursal</th>
                        <th>Categoría</th>
                        <th>Fecha y Hora cargue</th>
                        <th>Usuario cargue</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while($row=sqlsrv_fetch_array($SQL)){ ?>
						 <tr class="gradeX">
							<td><?php echo FormatoNombreArchivo($row['Archivo']);?></td>
							<td><?php echo $row['Comentarios'];?></td>
							<td><?php if($row['Fecha']!=""){ echo $row['Fecha']->format('Y-m-d');}else{?><p class="text-muted">--</p><?php }?></td>
							<td><?php echo $row['NombreCliente'];?></td>
							<td><?php echo $row['ID_Sucursal'];?></td>
							<td><?php echo $row['NombreCategoria'];?></td>
							<td><?php echo $row['FechaRegistro']->format('Y-m-d H:i:s');?></td>
							<td><?php echo $row['NombreUsuario'];?></td>
							<td><?php if($row['Archivo']!=""){?><a href="filedownload.php?file=<?php echo base64_encode($row['ID_Archivo']);?>" target="_blank" class="btn btn-success btn-xs"><i class="fa fa-download"></i> Descargar</a><?php if(PermitirFuncion(205)||ConsultarUsuarioCargue($row['ID_Archivo'])){?> <a href="#" onClick="EliminarRegistro(<?php echo $row['ID_Archivo'];?>);" class="btn btn-danger btn-xs"><i class="fa fa-eraser"></i> Eliminar</a><?php }}else{?><p class="text-muted">Ninguno</p><?php }?></td>
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
			
			$(".select2").select2();
			
            $('.dataTables-example').DataTable({
                pageLength: 25,
                responsive: true,
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