<?php 
if(isset($_GET['id'])&&$_GET['id']!=""){
	require_once("includes/conexion.php");
	PermitirAcceso(105);
if(!is_numeric(base64_decode($_GET['id']))){
	$_GET['id']=base64_encode(1);
}
//Categoria
$Where="ID_Categoria = '".base64_decode($_GET['id'])."'";
$SQL_Cat=Seleccionar("uvw_tbl_Categorias","ID_Categoria, NombreCategoria",$Where);
$row_Cat=sqlsrv_fetch_array($SQL_Cat);

//Campos
$Cons_Campos="Select * From uvw_tbl_ParamInfSAP_Campos Where ID_Categoria='".$row_Cat['ID_Categoria']."'";
$SQL_Campos=sqlsrv_query($conexion,$Cons_Campos,array(),array( "Scrollable" => 'static' ));
$Num_Campos=sqlsrv_num_rows($SQL_Campos);
	
//$SQL_Territorios=Seleccionar("uvw_Sap_tbl_Territorios","*","",'DeTerritorio');	

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
				  <form action="sapdownload.php" method="post" id="formInforme" class="form-horizontal">
					  <div class="form-group">
						<label class="col-lg-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-check-square-o"></i> Críterios de selección</h3></label>
					  </div>
					  <?php
					if($Num_Campos>0){
					  while($row_Campos=sqlsrv_fetch_array($SQL_Campos)){
					  	if($row_Campos['TipoCampo']=="Texto"){?>
							<div class="form-group">
								<label class="col-lg-2 control-label"><?php echo $row_Campos['LabelCampo'];?> <?php if($row_Campos['CampoObligatorio']==1){?><span class="text-danger">*</span><?php }?></label>
								<div class="col-lg-3">
									<input name="<?php echo $row_Campos['NombreCampo'];?>" type="text" class="form-control" <?php if($row_Campos['CampoObligatorio']==1){?>required="required"<?php }?> id="<?php echo $row_Campos['NombreCampo'];?>" maxlength="100">
								</div>
							</div>
					  <?php }elseif($row_Campos['TipoCampo']=="Comentario"){?>
					 		<div class="form-group">
								<label class="col-lg-2 control-label"><?php echo $row_Campos['LabelCampo'];?> <?php if($row_Campos['CampoObligatorio']==1){?><span class="text-danger">*</span><?php }?></label>
								<div class="col-lg-4">
									<textarea name="<?php echo $row_Campos['NombreCampo'];?>" maxlength="1000" rows="5" <?php if($row_Campos['CampoObligatorio']==1){?>required="required"<?php }?> class="form-control" id="<?php echo $row_Campos['NombreCampo'];?>" type="text"></textarea>
								</div>
							</div>						
					  <?php }elseif($row_Campos['TipoCampo']=="Fecha"){?>
							<div class="form-group">
								<label class="col-lg-2 control-label"><?php echo $row_Campos['LabelCampo'];?> <?php if($row_Campos['CampoObligatorio']==1){?><span class="text-danger">*</span><?php }?></label>
								<div class="col-lg-2 input-group date">
									 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="<?php echo $row_Campos['NombreCampo'];?>" type="text" <?php if($row_Campos['CampoObligatorio']==1){?>required="required"<?php }?> class="form-control" id="<?php echo $row_Campos['NombreCampo'];?>" value="<?php echo date('Y-m-d');?>">
								</div>
							</div>						   
					  <?php }elseif($row_Campos['TipoCampo']=="Cliente"){?>
							<div class="form-group">
								<label class="col-lg-2 control-label"><?php echo $row_Campos['LabelCampo'];?> <?php if($row_Campos['CampoObligatorio']==1){?><span class="text-danger">*</span><?php }?></label>
								<div class="col-lg-5">
									<input name="<?php echo $row_Campos['NombreCampo'];?>" type="hidden" id="<?php echo $row_Campos['NombreCampo'];?>" value="">
									<input name="srcNombreCliente" type="text" class="form-control" id="srcNombreCliente" placeholder="<?php if($row_Campos['CampoObligatorio']==1){?>Digite para buscar...<?php }else{?>Digite para buscar... (Para TODOS, dejar vacio)<?php }?>" <?php if($row_Campos['CampoObligatorio']==1){?>required="required"<?php }?>>
								</div>
							</div>						
					  <?php }elseif($row_Campos['TipoCampo']=="Sucursal"){?>
							<div class="form-group">
								<label class="col-lg-2 control-label"><?php echo $row_Campos['LabelCampo'];?> <?php if($row_Campos['CampoObligatorio']==1){?><span class="text-danger">*</span><?php }?></label>
								<div class="col-lg-4">
									 <select <?php if($row_Campos['Multiple']==1){?>data-placeholder="Seleccione..."<?php }?> id="<?php echo $row_Campos['NombreCampo'];?>" name="<?php if($row_Campos['Multiple']==1){echo $row_Campos['NombreCampo']."[]";}else{echo $row_Campos['NombreCampo'];}?>" class="form-control select2" <?php if($row_Campos['CampoObligatorio']==1){?>required="required"<?php }?> <?php if($row_Campos['Multiple']==1){?>multiple="multiple"<?php }?>>
										<?php if($row_Campos['Multiple']==0){?><option value="">(Todos)</option><?php }?>
									</select>
								</div>
							</div>
					  <?php }elseif($row_Campos['TipoCampo']=="Seleccion"){?>
							<div class="form-group">
								<label class="col-lg-2 control-label"><?php echo $row_Campos['LabelCampo'];?> <?php if($row_Campos['CampoObligatorio']==1){?><span class="text-danger">*</span><?php }?></label>
								<div class="col-lg-4">
									<label class="checkbox-inline i-checks"><input name="<?php echo $row_Campos['NombreCampo'];?>" id="<?php echo $row_Campos['NombreCampo'];?>" type="checkbox" value="1" <?php if($row_Campos['CampoObligatorio']==1){?>required="required"<?php }?>> <?php echo $row_Campos['NombreCheckbox'];?></label>
								</div>
							</div>						   
					 <?php }elseif($row_Campos['TipoCampo']=="Lista"){
							$Cmp_List=$row_Campos['EtiquetaList'].", ".$row_Campos['ValorList'];
						    $SQL_List=Seleccionar($row_Campos['VistaList'],$Cmp_List,'');?>
					 		<div class="form-group">
								<label class="col-lg-2 control-label"><?php echo $row_Campos['LabelCampo'];?> <?php if($row_Campos['CampoObligatorio']==1){?><span class="text-danger">*</span><?php }?></label>
								<div class="col-lg-4">
									<select <?php if($row_Campos['Multiple']==1){?>data-placeholder="Seleccione..."<?php }?> id="<?php echo $row_Campos['NombreCampo'];?>" name="<?php if($row_Campos['Multiple']==1){echo $row_Campos['NombreCampo']."[]";}else{echo $row_Campos['NombreCampo'];}?>" class="form-control select2" <?php if($row_Campos['CampoObligatorio']==1){?>required="required"<?php }?> <?php if($row_Campos['Multiple']==1){?>multiple="multiple"<?php }?>>
										<?php if($row_Campos['TodosList']==1&&$row_Campos['Multiple']==0){?><option value="Todos">(Todos)</option><?php }?>
										 <?php while($row_List=sqlsrv_fetch_array($SQL_List)){?>
												<option value="<?php echo $row_List[$row_Campos['ValorList']];?>"><?php echo $row_List[$row_Campos['EtiquetaList']];?></option>
										<?php  }?>
									</select>
								</div>
							</div>						   
					 <?php }
					  }
					}?>
					  <?php if($Num_Campos>0){?>
						<div class="form-group">
							<div class="col-lg-2">
								<button type="submit" name="submit" id="submit" class="btn btn-primary"><i class="fa fa-download"></i> Descargar</button>
							</div>
							<div class="col-lg-2">
								<div id="spinner1" style="display: none;" class="sk-spinner sk-spinner-wave pull-left">
									<div class="sk-rect1"></div>
									<div class="sk-rect2"></div>
									<div class="sk-rect3"></div>
									<div class="sk-rect4"></div>
									<div class="sk-rect5"></div>
								</div>
							</div>
						</div>
					  <?php }?>
				   		<input type="hidden" name="id" id="id" value="<?php echo $_GET['id'];?>">
					  	<input type="hidden" name="type" id="type" value="<?php echo base64_encode('1');?>">
				 </form>
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
			 $("#formInforme").validate({
				submitHandler: function(form){
					simpleLoad(true);
					setTimeout(simpleLoad,15000,false);
					form.submit();
			    }
			 });
			<?php $SQL_Campos=sqlsrv_query($conexion,$Cons_Campos,array(),array( "Scrollable" => 'static' ));
				if($Num_Campos>0){
					  while($row_Campos=sqlsrv_fetch_array($SQL_Campos)){
						if($row_Campos['TipoCampo']=="Fecha"){?>
							$('#<?php echo $row_Campos['NombreCampo'];?>').datepicker({
								todayBtn: "linked",
								keyboardNavigation: false,
								forceParse: false,
								calendarWeeks: true,
								autoclose: true,
								todayHighlight: true,
								format: 'yyyy-mm-dd'
							});			
			<?php 		}elseif($row_Campos['TipoCampo']=="Cliente"){?>
							$("#srcNombreCliente").change(function(){
								var NomCliente=document.getElementById("srcNombreCliente");
								var Cliente=document.getElementById("<?php echo $row_Campos['NombreCampo'];?>");
								if(NomCliente.value==""){
									Cliente.value="";
									CargarSucursales('<?php echo $row_Campos['NombreCampo'];?>');
								}				
							});
							$("#<?php echo $row_Campos['NombreCampo'];?>").change(function(){
								CargarSucursales('<?php echo $row_Campos['NombreCampo'];?>');								
							});

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
										var value = $("#srcNombreCliente").getSelectedItemData().CodigoCliente;
										$("#<?php echo $row_Campos['NombreCampo'];?>").val(value).trigger("change");
									}
								}
							};

							$("#srcNombreCliente").easyAutocomplete(options);
			
			<?php 
					  }
				  }
				}?>
			$(".select2").select2();
			$('.i-checks').iCheck({
				 checkboxClass: 'icheckbox_square-green',
				 radioClass: 'iradio_square-green',
			  });			
        });
	 <?php $SQL_Campos=sqlsrv_query($conexion,$Cons_Campos,array(),array( "Scrollable" => 'static' ));
				if($Num_Campos>0){
					  while($row_Campos=sqlsrv_fetch_array($SQL_Campos)){
						if($row_Campos['TipoCampo']=="Sucursal"){?>
							 function CargarSucursales(cmpCliente){
									var Clt=document.getElementById(''+cmpCliente);
									$.ajax({
										type: "POST",
										url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Clt.value+"<?php if($row_Campos['Multiple']==1){echo "&todos=0";}?>",
										success: function(response){
											$('#<?php echo $row_Campos['NombreCampo'];?>').html(response).fadeIn();
											$('#<?php echo $row_Campos['NombreCampo'];?>').val(null).trigger('change');
										}
									});									
								}
	 
	 <?php 				}
					  }
				}?>
	 	
		function simpleLoad(state){
			var spinner=document.getElementById('spinner1');
			var boton=document.getElementById("submit");
			if(state){
				boton.disabled = true;
				spinner.style.display='block';				
			}else{
				boton.disabled = false;
				spinner.style.display='none';
			}
		}
    </script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);
}?>