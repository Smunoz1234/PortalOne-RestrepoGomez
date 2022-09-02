<?php 
if(isset($_GET['id'])&&$_GET['id']!=""){
	require_once("includes/conexion.php");
	PermitirAcceso(101);
if(!is_numeric(base64_decode($_GET['id']))){
	$_GET['id']=base64_encode(1);
}
//Categoria
$Where="ID_Categoria = '".base64_decode($_GET['id'])."'";
$SQL_Cat=Seleccionar("uvw_tbl_Categorias","ID_Categoria, NombreCategoria",$Where);
$row_Cat=sqlsrv_fetch_array($SQL_Cat);

//Fechas
if(isset($_GET['FechaInicial'])&&$_GET['FechaInicial']!=""){
	$FechaInicial=$_GET['FechaInicial'];
}else{
	//Restar 7 dias a la fecha actual
	$fecha = date('Y-m-d');
	$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasDoc").' day');
	$nuevafecha = date ( 'Y-m-d' , $nuevafecha );
	$FechaInicial=$nuevafecha;
}
if(isset($_GET['FechaFinal'])&&$_GET['FechaFinal']!=""){
	$FechaFinal=$_GET['FechaFinal'];
}else{
	$FechaFinal=date('Y-m-d');
}

if(isset($_GET['_nw'])&&$_GET['_nw']==base64_encode("NeW")){//Mostrar solo los archivos que no se han descargado
	$Cons="Select * From uvw_tbl_archivos Where ID_Categoria='".base64_decode($_GET['id'])."' and ID_Archivo NOT IN (Select ID_Archivo From uvw_tbl_DescargaArchivos Where ID_Usuario=".$_SESSION['CodUser'].") Order by Fecha DESC";
}else{
	$Cons="Select * From uvw_tbl_archivos Where (Fecha Between '".FormatoFecha($FechaInicial)."' and '".FormatoFecha($FechaFinal)."') and ID_Categoria='".base64_decode($_GET['id'])."' Order by Fecha DESC";
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
				  <form action="documentos_generales.php" method="get" id="formBuscar" class="form-horizontal">
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
                        <th>Acciones</th>
                    </tr>
                    </thead>
                     <tbody>
                    <?php while($row=sqlsrv_fetch_array($SQL)){ ?>
						 <tr class="gradeX">
							<td><?php echo FormatoNombreArchivo($row['Archivo']);?></td>
							<td><?php echo $row['Comentarios'];?></td>
							<td><?php if($row['Fecha']!=""){ echo $row['Fecha']->format('Y-m-d');}else{?><p class="text-muted">--</p><?php }?></td>
							<td><?php echo $row['NombreUsuario'];?></td>
							<td><?php if($row['Archivo']!=""){?><a href="filedownload.php?file=<?php echo base64_encode($row['ID_Archivo']);?>&dtype=<?php echo base64_encode("1");?>" target="_blank" class="btn btn-success btn-xs"><i class="fa fa-download"></i> Descargar</a><?php }else{?><p class="text-muted">Ninguno</p><?php }?></td>
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