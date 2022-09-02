<?php 
if(isset($_GET['id'])&&$_GET['id']!=""){
	require_once("includes/conexion.php");
	PermitirAcceso(102);
if(!is_numeric(base64_decode($_GET['id']))){
	$_GET['id']=base64_encode(1);
}
//Categoria
$SQL_Cat=Seleccionar("uvw_tbl_Categorias","ID_Categoria, NombreCategoria","ID_Categoria = '".base64_decode($_GET['id'])."'");
$row_Cat=sqlsrv_fetch_array($SQL_Cat);

$WhereSuc=array();//Para ir agregando los filtros normalmente
$WhereCliente=array();//Para ir agregando los filtros normalmente
$i=0;//Controlar el indice del array
$Filtro="";//Filtro
$sw_suc=0;//Mostrar las sucursales del cliente seleccionado
$sw_todos=0;

if(PermitirFuncion(205)){
	$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes","CodigoCliente, NombreCliente","");	
	$sw_todos=1;
}else{
	$Where="ID_Usuario = ".$_SESSION['CodUser'];
	$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);	
}

//Fechas
if(isset($_GET['FechaInicial'])&&$_GET['FechaInicial']!=""){
	$FechaInicial=$_GET['FechaInicial'];
}else{
	//Restar 7 dias a la fecha actual
	$fecha = date('Y-m-d');
	$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasInformes").' day');
	$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
	$FechaInicial=$nuevafecha;
}
if(isset($_GET['FechaFinal'])&&$_GET['FechaFinal']!=""){
	$FechaFinal=$_GET['FechaFinal'];
}else{
	$FechaFinal=date('Y-m-d');
}

if(isset($_GET['Cliente'])){
	if($_GET['Cliente']!=""){//Si se selecciono el cliente
		$Filtro.=" and CardCode='".$_GET['Cliente']."'";
		$sw_suc=1;//Cuando se ha seleccionado una sucursal
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
			//Recargar consultas para combos
			$Where="ID_Usuario = ".$_SESSION['CodUser'];
			$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);
		}
}

if(isset($_GET['_nw'])&&$_GET['_nw']==base64_encode("NeW")){//Mostrar solo los archivos que no se han descargado
	$Cons="Select * From uvw_tbl_archivos Where ID_Categoria='".base64_decode($_GET['id'])."' $Filtro and ID_Archivo NOT IN (Select ID_Archivo From uvw_tbl_DescargaArchivos T2 Where T2.ID_Usuario=".$_SESSION['CodUser'].") Order by Fecha DESC";
}else{
	$Cons="Select * From uvw_tbl_archivos Where (Fecha Between '".FormatoFecha($FechaInicial)."' and '".FormatoFecha($FechaFinal)."') $Filtro  and ID_Categoria='".base64_decode($_GET['id'])."' Order by Fecha DESC";
}
//echo $Cons;
$SQL=sqlsrv_query($conexion,$Cons);
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $row_Cat['NombreCategoria'];?> | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
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
                        <li class="active">
                            <strong><?php echo $row_Cat['NombreCategoria'];?></strong>
                        </li>
                    </ol>
                </div>
            </div>
            <?php  //echo $Cons;?>
         <div class="wrapper wrapper-content">
         <div class="row">
			  <div class="col-lg-12">
			    <div class="ibox-content">
					<?php include("includes/spinner.php"); ?>
				  <form action="informes.php" method="get" id="formBuscar" class="form-horizontal">
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
							<select name="Cliente" class="form-control select2" id="Cliente">
								<?php if($sw_todos==1){?><option value="" selected="selected">(Todos)</option><?php }?>
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
									 if($sw_suc==1){//Cuando se ha seleccionado una opción
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
									 }elseif($sw_suc==0){//Cuando no se ha seleccionado todavia, al entrar a la pagina
										  while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
											<option value="<?php echo $row_Sucursal['NombreSucursal'];?>"><?php echo $row_Sucursal['NombreSucursal'];?></option>
									<?php }
									 }?>
								</select>
								</div>			  
					   <div class="col-lg-1">
							<button type="submit" name="submit" class="btn btn-outline btn-primary"><i class="fa fa-search"></i> Buscar</button>
					   </div>
					   <input type="hidden" name="id" id="id" value="<?php echo $_GET['id'];?>">
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
                        <th>Fecha</th>
                        <th>Usuario cargue</th>
                        <th>Cliente</th>
                        <th>Sucursal</th>
                        <th>Acciones</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                     <tbody>
                    <?php while($row=sqlsrv_fetch_array($SQL)){ ?>
						 <tr class="gradeX">
							<td><?php echo FormatoNombreArchivo($row['Archivo']);?></td>
							<td><?php echo $row['Comentarios'];?></td>
							<td><?php if($row['Fecha']!=""){ echo $row['Fecha']->format('Y-m-d');}else{?><p class="text-muted">--</p><?php }?></td>
							<td><?php echo $row['NombreUsuario'];?></td>
							<td><?php echo $row['NombreCliente'];?></td>
							<td><?php echo $row['ID_Sucursal'];?></td>
							<td><?php if($row['Archivo']!=""){?><a href="filedownload.php?file=<?php echo base64_encode($row['ID_Archivo']);?>&dtype=<?php echo base64_encode("2");?>" target="_blank" class="btn btn-success btn-xs"><i class="fa fa-download"></i> Descargar</a><?php }else{?><p class="text-muted">Ninguno</p><?php }?></td>
							<td align="center"><?php if(ConsultarDescargaArchivo($row['ID_Archivo'])==0){echo "<i title='No se ha descargado' class='fa fa-eye-slash'></i>";}else{echo "<i title='Ult. Fecha descarga: ".ConsultarFechaDescarga($row['ID_Archivo'])."' class='fa fa-eye'></i>";}?></td>
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
<?php sqlsrv_close($conexion);
}?>