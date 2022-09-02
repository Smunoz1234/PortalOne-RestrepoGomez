<?php require_once("includes/conexion.php");
PermitirAcceso(211);
$sw=0;
$msg_error="";//Mensaje del error
$sw_error=0;

if(isset($_POST['P'])&&($_POST['P']!="")){
	try{
		//Insertar datos WS
		$ParamInsWS=array(
			"'".base64_decode($_POST['id'])."'",
			"'".$_POST['NombreWS']."'",
			"'".$_POST['NombreReporte']."'",
			"'".$_SESSION['CodUser']."'",
			"1"
		);
		$SQL_InsWS=EjecutarSP('sp_tbl_ParamInfSAP_WebServices',$ParamInsWS,$_POST['P']);
		if($SQL_InsWS){
			//Insertar campos
			$Count=count($_POST['NombreParam']);
			$i=0;

			$SQL_Delete=Eliminar('tbl_ParamInfSAP_Campos',"ID_Categoria='".base64_decode($_POST['id'])."'");
			if($SQL_Delete){
				while($i<$Count){
					if($_POST['NombreParam'][$i]!=""){
						//Insertar el registro en la BD
						$ParamInsDir=array(
							"'".base64_decode($_POST['id'])."'",
							"'".$_POST['NombreParam'][$i]."'",
							"'".$_POST['LabelCampo'][$i]."'",
							"'".$_POST['NombreCampo'][$i]."'",
							"'".$_POST['TipoCampo'][$i]."'",
							"'".$_POST['CampoOblig'][$i]."'",
							"'".$_POST['NombreCheckbox'][$i]."'",
							"'".$_POST['VistaList'][$i]."'",
							"'".$_POST['EtiqList'][$i]."'",
							"'".$_POST['ValorList'][$i]."'",
							"'".$_POST['TodosList'][$i]."'",
							"'".$_POST['Multiple'][$i]."'",
							"'".$_SESSION['CodUser']."'",
							"1"
						);
						$SQL_InsDir=EjecutarSP('sp_tbl_ParamInfSAP_Campos',$ParamInsDir,$_POST['P']);
						if(!$SQL_InsDir){
							$msg_error="Ha ocurrido un error al insertar los parametros de SAP B1.";
							$sw_error=1;
						}
					}
					$i=$i+1;
				}
				sqlsrv_close($conexion);
				header('Location:informes_sap_parametrizar.php?a='.base64_encode("OK_ParamInfSAP").'&id='.$_POST['id']);
			}else{
				$msg_error="Ocurrio un error al eliminar el registro";
				$sw_error=1;
			}				
		}		
	}catch (Exception $e) {
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}

}

//Listar informes
$SQL_Informe=Seleccionar('uvw_tbl_Categorias','*',"ID_TipoCategoria=5 AND ID_Padre <> 0");

if((isset($_POST['InformeSAP'])&&$_POST['InformeSAP']!="")||(isset($_GET['id'])&&$_GET['id']!="")){
	$sw=1;
	if(isset($_POST['InformeSAP'])){
		$ID=$_POST['InformeSAP'];
	}else{
		$ID=base64_decode($_GET['id']);
	}
	$SQL_WS=Seleccionar('uvw_tbl_ParamInfSAP_WebServices','*',"ID_Categoria=".$ID);
	$row_WS=sqlsrv_fetch_array($SQL_WS);
	
	$Cons_Campos="Select * From uvw_tbl_ParamInfSAP_Campos Where ID_Categoria='".$ID."'";
	//echo $Cons_Campos;
	$SQL_Campos=sqlsrv_query($conexion,$Cons_Campos,array(),array( "Scrollable" => 'static' ));
	$Num_Campos=sqlsrv_num_rows($SQL_Campos);
}


?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Parametrizar Informes SAP B1 | <?php echo NOMBRE_PORTAL;?></title>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_ParamInfSAP"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'Campos guardados exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Lo sentimos!',
                text: '".LSiqmlObs($msg_error)."',
                icon: 'warning'
            });
		});		
		</script>";
}
?>
<script>
function ChangeTipoCampo(id){
	var TipoCampo=document.getElementById('TipoCampo_'+id).value;
	if(TipoCampo=="Seleccion"){
		HabilitarCampos(0,id);
		document.getElementById('dv_ConfCheck_'+id).style.display='block';
		document.getElementById('dv_CampoOblig_'+id).style.display='block';	
	}else if(TipoCampo=="Lista"){
		HabilitarCampos(0,id);
		document.getElementById('dv_ConfList_'+id).style.display='block';
		document.getElementById('dv_Multiple_'+id).style.display='block';
		document.getElementById('dv_CampoOblig_'+id).style.display='block';	
	}else if(TipoCampo=="Sucursal"){
		HabilitarCampos(0,id);
		document.getElementById('dv_Multiple_'+id).style.display='block';
		document.getElementById('dv_CampoOblig_'+id).style.display='block';	
	}else if(TipoCampo=="Usuario"){
		HabilitarCampos(0,id);
		document.getElementById('LabelCampo_'+id).value="";
		document.getElementById('NombreCampo_'+id).value="";
		document.getElementById('LabelCampo_'+id).readOnly=true;
		document.getElementById('NombreCampo_'+id).readOnly=true;
		document.getElementById('dv_Multiple_'+id).style.display='none';
		document.getElementById('dv_CampoOblig_'+id).style.display='none';	
	}else{
		HabilitarCampos(0,id);
		document.getElementById('dv_CampoOblig_'+id).style.display='block';	
		document.getElementById('dv_Multiple_'+id).style.display='none';
	}
}
function ConsultarCamposList(id){
	var VistaList=document.getElementById('VistaList_'+id).value;
	if(VistaList!=""){
		$.ajax({
			type: "POST",
			url: "ajx_cbo_select.php?type=12&id="+document.getElementById('VistaList_'+id).value,
			success: function(response){
				$('#EtiqList_'+id).html(response).fadeIn();
				$('#ValorList_'+id).html(response).fadeIn();
			}
		});
	}	
}
function HabilitarCampos(type=0,id){
	if(type==0){//Deshabilitar
		document.getElementById('dv_ConfCheck_'+id).style.display='none';
		document.getElementById('dv_ConfList_'+id).style.display='none';
		document.getElementById('LabelCampo_'+id).readOnly=false;
		document.getElementById('NombreCampo_'+id).readOnly=false;
	}else{//Habilitar
		document.getElementById('LabelCampo_'+id).readOnly=false;
		document.getElementById('NombreCampo_'+id).readOnly=false;
		document.getElementById('dv_ConfCheck_'+id).style.display='block';
		document.getElementById('dv_ConfList_'+id).style.display='block';
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
                    <h2>Parametrizar Informes SAP B1</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Administraci&oacute;n</a>
                        </li>
                        <li class="active">
                            <strong>Parametrizar Informes SAP B1</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			 <div class="ibox-content">
				 <?php include("includes/spinner.php"); ?>
          <div class="row"> 
           <div class="col-lg-12">
              	<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Seleccione el informe a parametrizar</h3>
				<br>
				<form action="informes_sap_parametrizar.php" method="post" class="form-horizontal" id="Consultar">
					<div class="form-group">
						<label class="col-lg-1 control-label">Informe</label>
						<div class="col-lg-4">
							<select name="InformeSAP" class="form-control m-b" id="InformeSAP" required>
								<option value="">Seleccione...</option>
                          <?php while($row_Informe=sqlsrv_fetch_array($SQL_Informe)){?>
								<option value="<?php echo $row_Informe['ID_Categoria'];?>" <?php if((isset($ID))&&(strcmp($row_Informe['ID_Categoria'],$ID)==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Informe['NombreCategoria'];?></option>
						  <?php }?>
						</select>
						</div>
						<div class="col-lg-2">
							<button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Consultar</button>
						</div>
					</div>
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
						<h3 class="bg-primary p-xs b-r-sm"><i class="fa fa-check-circle-o"></i> Configure los campos del informe</h3><br>
						 <form action="informes_sap_parametrizar.php" method="post" id="frmCampos" class="form-horizontal">
							 <div class="form-group">
								<label class="col-lg-2 control-label text-success">Nombre del método WS</label>
								<div class="col-lg-3">
									<input name="NombreWS" type="text" required="required" class="form-control" id="NombreWS" maxlength="50" value="<?php if($row_WS['NombreWS']!=""){echo $row_WS['NombreWS'];}?>" autocomplete="off">
								</div>
								<label class="col-lg-2 control-label text-success">Nombre del archivo</label>
								<div class="col-lg-3">
									<input name="NombreReporte" type="text" required="required" class="form-control" id="NombreReporte" maxlength="50" value="<?php if($row_WS['NombreReporte']!=""){echo $row_WS['NombreReporte'];}?>" autocomplete="off" placeholder="Incluyendo la extensión del archivo...">
								</div>
							</div>
				 <?php $Cont=1;
					if($Num_Campos>0){
						$row_Campos=sqlsrv_fetch_array($SQL_Campos);
						do{ ?>
							 <div id="div_<?php echo $Cont;?>" class="bg-muted p-sm m-t-md">
								 <div class="form-group">
									<label class="col-lg-2 control-label">Nombre del parámetro</label>
									<div class="col-lg-3">
										<input name="NombreParam[]" type="text" required="required" class="form-control" id="NombreParam_<?php echo $Cont;?>" maxlength="50" value="<?php echo $row_Campos['NombreParam'];?>" autocomplete="off">
									</div>
									<label class="col-lg-2 control-label">Label del campo</label>
									<div class="col-lg-3">
										<input name="LabelCampo[]" type="text" required="required" class="form-control" id="LabelCampo_<?php echo $Cont;?>" maxlength="50" value="<?php echo $row_Campos['LabelCampo'];?>" <?php if($row_Campos['TipoCampo']=='Usuario'){ echo "readonly";}?> autocomplete="off">
									</div>
								</div>
								<div class="form-group">
									<label class="col-lg-2 control-label">Nombre del campo</label>
									<div class="col-lg-3">
										<input name="NombreCampo[]" type="text" required="required" class="form-control" id="NombreCampo_<?php echo $Cont;?>" maxlength="50" value="<?php echo $row_Campos['NombreCampo'];?>" <?php if($row_Campos['TipoCampo']=='Usuario'){ echo "readonly";}?> autocomplete="off">
									</div>
									<label class="col-lg-2 control-label">Tipo de campo</label>
									<div class="col-lg-3">
										<select name="TipoCampo[]" class="form-control m-b" id="TipoCampo_<?php echo $Cont;?>" required onChange="ChangeTipoCampo('<?php echo $Cont;?>');">
											<option value="Texto" <?php if($row_Campos['TipoCampo']=='Texto'){echo "selected=\"selected\"";} ?>>Texto</option>
											<option value="Comentario" <?php if($row_Campos['TipoCampo']=='Comentario'){echo "selected=\"selected\"";} ?>>Comentario</option>
											<option value="Fecha" <?php if($row_Campos['TipoCampo']=='Fecha'){echo "selected=\"selected\"";} ?>>Fecha</option>
											<option value="Cliente" <?php if($row_Campos['TipoCampo']=='Cliente'){echo "selected=\"selected\"";} ?>>Cliente (Lista)</option>
											<option value="Sucursal" <?php if($row_Campos['TipoCampo']=='Sucursal'){echo "selected=\"selected\"";} ?>>Sucursal (Dependiendo del cliente)</option>
											<option value="Seleccion" <?php if($row_Campos['TipoCampo']=='Seleccion'){echo "selected=\"selected\"";} ?>>Selección (Checkbox)</option>
											<option value="Lista" <?php if($row_Campos['TipoCampo']=='Lista'){echo "selected=\"selected\"";} ?>>Lista (Personalizada)</option>
											<option value="Usuario" <?php if($row_Campos['TipoCampo']=='Usuario'){echo "selected=\"selected\"";} ?>>Usuario</option>
										</select>
									</div>
								</div>
								<div id="dv_ConfCheck_<?php echo $Cont;?>" <?php if($row_Campos['TipoCampo']!='Seleccion'){?>style="display: none;"<?php }?> class="form-group">
									<label class="col-lg-2 control-label">Nombre checkbox</label>
									<div class="col-lg-3">
										<input name="NombreCheckbox[]" type="text" required="required" class="form-control" id="NombreCheckbox_<?php echo $Cont;?>" maxlength="50" value="<?php echo $row_Campos['NombreCheckbox'];?>" autocomplete="off">
									</div>
								</div>
								<?php 
						   				if($row_Campos['TipoCampo']=='Lista'){
											$Cons_List="EXEC sp_columns '".$row_Campos['VistaList']."'";
											$SQL_List=sqlsrv_query($conexion,$Cons_List);							
										}
								 ?>
								<div id="dv_ConfList_<?php echo $Cont;?>" <?php if($row_Campos['TipoCampo']!='Lista'){?>style="display: none;"<?php }?>>
									<div class="form-group">
										<label class="col-lg-2 control-label">Vista</label>
										<div class="col-lg-3">
											<input name="VistaList[]" type="text" required="required" class="form-control" id="VistaList_<?php echo $Cont;?>" maxlength="100" value="<?php echo $row_Campos['VistaList'];?>" autocomplete="off">
										</div>
										<div class="col-lg-1">
											<button type="button" onClick="ConsultarCamposList('<?php echo $Cont;?>');" class="btn btn-success btn-xs" title="Consultar"><i class="fa fa-search"></i></button>
										</div>
										<label class="col-lg-1 control-label">Etiqueta</label>
										<div class="col-lg-2">
											<select name="EtiqList[]" class="form-control m-b" id="EtiqList_<?php echo $Cont;?>" required>
												<option value="">Seleccione...</option>
												<?php if($row_Campos['TipoCampo']=='Lista'){
									 					while($row_List=sqlsrv_fetch_array($SQL_List)){?>
															<option value="<?php echo $row_List['COLUMN_NAME'];?>" <?php if((isset($row_Campos['EtiquetaList']))&&(strcmp($row_List['COLUMN_NAME'],$row_Campos['EtiquetaList'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_List['COLUMN_NAME'];?></option>
												<?php 	}
									 				  }?>
											</select>
										</div>
										<label class="col-lg-1 control-label">Valor</label>
										<div class="col-lg-2">
											<select name="ValorList[]" class="form-control m-b" id="ValorList_<?php echo $Cont;?>" required>
												<option value="">Seleccione...</option>
												<?php if($row_Campos['TipoCampo']=='Lista'){
									 					$SQL_List=sqlsrv_query($conexion,$Cons_List);
									 					while($row_List=sqlsrv_fetch_array($SQL_List)){?>
															<option value="<?php echo $row_List['COLUMN_NAME'];?>" <?php if((isset($row_Campos['ValorList']))&&(strcmp($row_List['COLUMN_NAME'],$row_Campos['ValorList'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_List['COLUMN_NAME'];?></option>
												<?php 	}
								 					}?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-2 control-label">Permitir "Todos"</label>
										<div class="col-lg-2">
											<select name="TodosList[]" class="form-control m-b" id="TodosList_<?php echo $Cont;?>">
												<option value="0" <?php if($row_Campos['TodosList']==0){echo "selected";}?>>NO</option>
												<option value="1" <?php if($row_Campos['TodosList']==1){echo "selected";}?>>SI</option>
											</select>
										</div>
									</div>
								</div>
								<div class="form-group">
									<div id="dv_CampoOblig_<?php echo $Cont;?>" <?php if($row_Campos['TipoCampo']=='Usuario'){?>style="display: none;"<?php }?>>
										<label class="col-lg-2 control-label">Campo obligatorio</label>
										<div class="col-lg-2">
											<select name="CampoOblig[]" class="form-control m-b" id="CampoOblig_<?php echo $Cont;?>">
												<option value="0" <?php if($row_Campos['CampoObligatorio']==0){echo "selected";}?>>NO</option>
												<option value="1" <?php if($row_Campos['CampoObligatorio']==1){echo "selected";}?>>SI</option>
											</select>
										</div>
									</div>	
									<div class="col-lg-1"></div>
									<div id="dv_Multiple_<?php echo $Cont;?>" <?php if(($row_Campos['TipoCampo']!='Sucursal')&&($row_Campos['TipoCampo']!='Lista')){?>style="display: none;"<?php }?>>
										<label class="col-lg-2 control-label">Permitir varios valores</label>
										<div class="col-lg-2">
											<select name="Multiple[]" class="form-control m-b" id="Multiple_<?php echo $Cont;?>">
												<option value="0" <?php if($row_Campos['Multiple']==0){echo "selected";}?>>NO</option>
												<option value="1" <?php if($row_Campos['Multiple']==1){echo "selected";}?>>SI</option>
											</select>
										</div>
									</div>									
								</div>
								<button type="button" id="<?php echo $Cont;?>" class="btn btn-warning btn-xs btn_del"><i class="fa fa-minus"></i> Remover</button><br>
							</div>
						<?php 
								$Cont++;
							} while($row_Campos=sqlsrv_fetch_array($SQL_Campos));
						} ?>
							 <div id="div_<?php echo $Cont;?>" class="bg-muted p-sm m-t-md">
								 <div class="form-group">
									<label class="col-lg-2 control-label">Nombre del parámetro</label>
									<div class="col-lg-3">
										<input name="NombreParam[]" type="text" class="form-control" id="NombreParam_<?php echo $Cont;?>" maxlength="50" value="" autocomplete="off">
									</div>
									<label class="col-lg-2 control-label">Label del campo</label>
									<div class="col-lg-3">
										<input name="LabelCampo[]" type="text" class="form-control" id="LabelCampo_<?php echo $Cont;?>" maxlength="50" value="" autocomplete="off">
									</div>
								</div>
								<div class="form-group">
									<label class="col-lg-2 control-label">Nombre del campo</label>
									<div class="col-lg-3">
										<input name="NombreCampo[]" type="text" class="form-control" id="NombreCampo_<?php echo $Cont;?>" maxlength="50" value="" autocomplete="off">
									</div>
									<label class="col-lg-2 control-label">Tipo de campo</label>
									<div class="col-lg-3">
										<select name="TipoCampo[]" class="form-control m-b" id="TipoCampo_<?php echo $Cont;?>" onChange="ChangeTipoCampo('<?php echo $Cont;?>');">
											<option value="Texto">Texto</option>
											<option value="Comentario">Comentario</option>
											<option value="Fecha">Fecha</option>
											<option value="Cliente">Cliente (Lista)</option>
											<option value="Sucursal">Sucursal (Dependiendo del cliente)</option>
											<option value="Seleccion">Selección (Checkbox)</option>
											<option value="Lista">Lista (Personalizada)</option>
											<option value="Usuario">Usuario</option>
										</select>
									</div>
								</div>
								<div id="dv_ConfCheck_<?php echo $Cont;?>" style="display: none;" class="form-group">
									<label class="col-lg-2 control-label">Nombre checkbox</label>
									<div class="col-lg-3">
										<input name="NombreCheckbox[]" type="text" class="form-control" id="NombreCheckbox_<?php echo $Cont;?>" maxlength="50" value="" autocomplete="off">
									</div>
								</div>
								<div id="dv_ConfList_<?php echo $Cont;?>" style="display: none;" class="form-group">
									<div class="form-group">
										<label class="col-lg-2 control-label">Vista</label>
										<div class="col-lg-3">
											<input name="VistaList[]" type="text" class="form-control" id="VistaList_<?php echo $Cont;?>" maxlength="100" value="">
										</div>
										<div class="col-lg-1">
											<button type="button" onClick="ConsultarCamposList('<?php echo $Cont;?>');" class="btn btn-success btn-xs" title="Consultar"><i class="fa fa-search"></i></button>
										</div>
										<label class="col-lg-1 control-label">Etiqueta</label>
										<div class="col-lg-2">
											<select name="EtiqList[]" class="form-control m-b" id="EtiqList_<?php echo $Cont;?>">
												<option value="">Seleccione...</option>
											</select>
										</div>
										<label class="col-lg-1 control-label">Valor</label>
										<div class="col-lg-2">
											<select name="ValorList[]" class="form-control m-b" id="ValorList_<?php echo $Cont;?>">
												<option value="">Seleccione...</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-2 control-label">Permitir "Todos"</label>
										<div class="col-lg-2">
											<select name="TodosList[]" class="form-control m-b" id="TodosList_<?php echo $Cont;?>">
												<option value="0">NO</option>
												<option value="1">SI</option>
											</select>
										</div>
									</div>
								</div>
								<div class="form-group">
									<div id="dv_CampoOblig_<?php echo $Cont;?>">
										<label class="col-lg-2 control-label">Campo obligatorio</label>
										<div class="col-lg-2">
											<select name="CampoOblig[]" class="form-control m-b" id="CampoOblig_<?php echo $Cont;?>">
												<option value="0">NO</option>
												<option value="1">SI</option>
											</select>
										</div>
									</div>
									<div class="col-lg-1"></div>
									<div id="dv_Multiple_<?php echo $Cont;?>" style="display: none;">
										<label class="col-lg-2 control-label">Permitir varios valores</label>
										<div class="col-lg-2">
											<select name="Multiple[]" class="form-control m-b" id="Multiple_<?php echo $Cont;?>">
												<option value="0">NO</option>
												<option value="1">SI</option>
											</select>
										</div>
									</div>
								</div>
							<button type="button" id="<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addField(this);"><i class="fa fa-plus"></i> Añadir otro</button><br>
							</div>
							 <br>
							 <div class="form-group">
								 <div class="col-lg-2">
									<button class="btn btn-primary" type="submit" id="Crear"><i class="fa fa-check"></i> Guardar</button>
							   </div>							 
							 </div>			
							 <input type="hidden" id="P" name="P" value="44" />
							 <input type="hidden" name="id" id="id" value="<?php echo base64_encode($ID);?>">		
				 		</form>
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
		 $("#Consultar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
		 $("#frmCampos").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
		 /*$('.i-checks').iCheck({
			 checkboxClass: 'icheckbox_square-green',
             radioClass: 'iradio_square-green',
          });*/
		 $(".btn_del").each(function (el){
			 $(this).bind("click",delRow);
		 });
	});
</script>
<script>
function addField(btn){//Clonar div
	var clickID = parseInt($(btn).parent('div').attr('id').replace('div_',''));
	//alert($(btn).parent('div').attr('id'));
	//alert(clickID);
	var newID = (clickID+1);

	$newClone = $('#div_'+clickID).clone(true);

	//div
	$newClone.attr("id",'div_'+newID);
	$newClone.children("div").eq(2).attr('id','dv_ConfCheck_'+newID);
	$newClone.children("div").eq(3).attr('id','dv_ConfList_'+newID);
	$newClone.children("div").eq(4).children("div").eq(2).attr('id','dv_Multiple_'+newID);

	//select
	$newClone.children("div").eq(1).children("div").eq(1).children("select").eq(0).attr('id','TipoCampo_'+newID);
	$newClone.children("div").eq(1).children("div").eq(1).children("select").eq(0).attr('onChange',"ChangeTipoCampo('"+newID+"');");	
	$newClone.children("div").eq(3).children("div").eq(0).children("div").eq(2).children("select").eq(0).attr('id','EtiqList_'+newID);
	$newClone.children("div").eq(3).children("div").eq(0).children("div").eq(3).children("select").eq(0).attr('id','ValorList_'+newID);	
	$newClone.children("div").eq(3).children("div").eq(1).children("div").eq(0).children("select").eq(0).attr('id','TodosList_'+newID);	
	$newClone.children("div").eq(4).children("div").eq(0).children("select").eq(0).attr('id','CampoOblig_'+newID);
	$newClone.children("div").eq(4).children("div").eq(2).children("select").eq(0).attr('id','Multiple'+newID);

	//inputs
	$newClone.children("div").eq(0).children("div").eq(0).children("input").eq(0).attr('id','NombreParam_'+newID);
	$newClone.children("div").eq(0).children("div").eq(1).children("input").eq(0).attr('id','LabelCampo_'+newID);
	$newClone.children("div").eq(1).children("div").eq(0).children("input").eq(0).attr('id','NombreCampo_'+newID);
	$newClone.children("div").eq(2).children("div").eq(0).children("input").eq(0).attr('id','NombreCheckbox_'+newID);
	
	$newClone.children("div").eq(3).children("div").eq(0).children("input").eq(0).attr('id','VistaList_'+newID);

	//button
	$newClone.children("button").eq(0).attr('id',''+newID);
	$newClone.children("div").eq(3).children("div").eq(1).children("button").eq(0).attr('onClick',"ConsultarCamposList('"+newID+"');");

	$newClone.insertAfter($('#div_'+clickID));
	
	//$("#"+clickID).val('Remover');
	document.getElementById(''+clickID).innerHTML="<i class='fa fa-minus'></i> Remover";
	document.getElementById(''+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById(''+clickID).setAttribute('onClick','delRow2(this);');

	//$("#"+clickID).addEventListener("click",delRow);

	//$("#"+clickID).bind("click",delRow);
}
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