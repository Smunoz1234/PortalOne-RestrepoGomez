<?php require_once("includes/conexion.php");
PermitirAcceso(214);
$sw=0;//Para saber si ya se selecciono un cliente y mostrar las sucursales
$Filtro="";
$SQL_TipoDoc=Seleccionar("uvw_tbl_ObjetosSAP","*",'','CategoriaObjeto, DeTipoDocumento');
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Gestionar series | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_Alert"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'Alertas agregadas exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_EditAlert"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El usuario ha sido editado exitosamente.',
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
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Cliente.value+"&sucline=1",
				success: function(response){
					$('#Sucursal').html(response).fadeIn();
				}
			});
		});
	});
</script>
<script>
function AgregarDoc(){
	var TipoDoc=document.getElementById("TipoDocumento");
	var frame=document.getElementById('DataGrid');
	
	if(TipoDoc.value!=""){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=30&tipodoc="+TipoDoc.value,		
			success: function(response){
				frame.src="detalle_gestionar_series.php";
				$('#TipoDocumento').val(null).trigger('change');
				Swal.fire({
					title: '¡Listo!',
					text: 'Se ha agregado el item exitosamente',
					icon: 'success'
				});
			}
		});
	}else{
		Swal.fire({
			title: '¡Lo sentimos!',
			text: 'Debe seleccionar un documento para agregar',
			icon: 'error'
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
                    <h2>Gestionar series</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Administraci&oacute;n</a>
                        </li>
                        <li class="active">
                            <strong>Gestionar series</strong>
                        </li>
                    </ol>
                </div>
            </div>           
         <div class="wrapper wrapper-content">
          <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
					<div class="row p-md">
						<div class="form-group">
							<label class="col-lg-1 control-label">Tipo de documento</label>
							<div class="col-lg-3">
								<select name="TipoDocumento" class="form-control" id="TipoDocumento">
										<option value="">Seleccione...</option>
								  <?php $CatActual="";
									while($row_TipoDoc=sqlsrv_fetch_array($SQL_TipoDoc)){
										if($CatActual!=$row_TipoDoc['CategoriaObjeto']){
											echo "<optgroup label='".$row_TipoDoc['CategoriaObjeto']."'></optgroup>";
											$CatActual=$row_TipoDoc['CategoriaObjeto'];
										}
									?>
										<option value="<?php echo $row_TipoDoc['IdTipoDocumento'];?>"><?php echo $row_TipoDoc['DeTipoDocumento'];?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-1">
								<button type="button" id="btnNuevo" class="btn btn-success" onClick="AgregarDoc();"><i class="fa fa-plus-circle"></i> Añadir</button>
							</div>
						</div>
						<div class="form-group">
							<span class="TimeAct"><div id="TimeAct">&nbsp;</div></span>
						</div>						
					</div>
					<div class="row table-responsive">
						<iframe id="DataGrid" name="DataGrid" style="border: 0;" width="100%" height="800" src="detalle_gestionar_series.php"></iframe>
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
		 $("#frmBuscar").validate();
		 
		 $(".btn_del").each(function (el){
			 $(this).bind("click",delRow);
		 });
		 
		 //$(".btn_plus").bind("click",addField);
		 
		 $('#FechaCorte').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
			 	todayHighlight: true
            });
		 
		 $("#frmAlertas").validate();
		 
		 $(".truncate").dotdotdot({
            watch: 'window'
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
	});	
</script>

<script>
function delRow(){//Eliminar div
	$(this).parent('div').remove();
}
function delRow2(btn){//Eliminar div
	$(btn).parent('div').remove();
}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>