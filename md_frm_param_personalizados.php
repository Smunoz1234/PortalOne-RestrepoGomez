<?php  
require_once("includes/conexion.php");

$Title="Crear nuevo registro";
$Metodo=1;

$edit=isset($_POST['edit']) ? $_POST['edit'] : 0;
$doc=isset($_POST['doc']) ? $_POST['doc'] : "";
$id=isset($_POST['id']) ? $_POST['id'] : "";

$dir_new=CrearObtenerDirAnx("formularios/monitoreos_temperaturas/planos");

if($edit==1&&$id!=""){
	$Title="Editar registro";
	$Metodo=2;
	if($doc=="Bodegas"){
		$SQL=Seleccionar('tbl_BodegasPuerto','*',"id_bodega_puerto='".$id."'");
		$row=sqlsrv_fetch_array($SQL);
		
		$SQL_SucursalCliente=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','NombreSucursal, NumeroLinea',"CodigoCliente='".$row['codigo_cliente']."'",'NombreSucursal');
		
	}elseif($doc=="Productos"){
		$SQL=Seleccionar('tbl_ProductosPuerto','*',"id_producto_puerto='".$id."'");
		$row=sqlsrv_fetch_array($SQL);
	}elseif($doc=="Transportes"){
		$SQL=Seleccionar('tbl_TransportesPuerto','*',"id_transporte_puerto='".$id."'");
		$row=sqlsrv_fetch_array($SQL);
	}elseif($doc=="TipoInfectacion"){
		$SQL=Seleccionar('tbl_TipoInfectacionProductos','*',"id_tipo_infectacion_producto='".$id."'");
		$row=sqlsrv_fetch_array($SQL);
	}elseif($doc=="GradoInfectacion"){
		$SQL=Seleccionar('tbl_GradoInfectacion','*',"id_grado_infectacion='".$id."'");
		$row=sqlsrv_fetch_array($SQL);
	}elseif($doc=="Muelles"){
		$SQL=Seleccionar('tbl_MuellesPuerto','*',"id_muelle_puerto='".$id."'");
		$row=sqlsrv_fetch_array($SQL);
	}	
}



?>
<form id="frm_NewParam" method="post" action="parametros_frm_personalizados.php" enctype="multipart/form-data">
<div class="modal-header">
	<h4 class="modal-title">
		<?php echo $Title; ?>
	</h4>
</div>
<div class="modal-body">
	<div class="form-group">
		<div class="ibox-content">
			<?php include("includes/spinner.php"); ?>
			<?php if($doc=="Bodegas"){?>
			<div class="form-group">
				<label class="control-label">Código de bodega <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="CodigoBodega" id="CodigoBodega" required autocomplete="off" value="<?php if($edit==1){echo $row['id_bodega_puerto'];}?>">
			</div>
			<div class="form-group">
				<label class="control-label">Nombre de bodega <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="NombreBodega" id="NombreBodega" required autocomplete="off" value="<?php if($edit==1){echo $row['bodega_puerto'];}?>">
			</div>
			<div class="form-group">
				<label class="control-label">Comentarios</label>
				<textarea name="ComentariosBodega" rows="3" maxlength="3000" class="form-control" id="ComentariosBodega" type="text"><?php if($edit==1){echo $row['comentarios'];}?></textarea>
			</div>
			<div class="form-group">
				<label class="control-label">Cliente <span class="text-danger">*</span></label>
				<input name="ClienteBodega" type="hidden" id="ClienteBodega" value="<?php if($edit==1){echo $row['codigo_cliente'];}?>">
				<input name="NombreClienteBodega" type="text" class="form-control" id="NombreClienteBodega" placeholder="Ingrese para buscar..." value="<?php if($edit==1){echo $row['nombre_cliente'];}?>" required>
			</div>
			<div class="form-group">
				<label class="control-label">Sucursal <span class="text-danger">*</span></label>
				<select name="SucursalBodega" class="form-control chosen-select" id="SucursalBodega" required>
				  <option value="">Seleccione...</option>
					<?php 
					if($edit==1){
						while($row_SucursalCliente=sqlsrv_fetch_array($SQL_SucursalCliente)){?>
							<option value="<?php echo $row_SucursalCliente['NumeroLinea'];?>" <?php if((isset($row['linea_sucursal']))&&(strcmp($row_SucursalCliente['NumeroLinea'],$row['linea_sucursal'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_SucursalCliente['NombreSucursal'];?></option>
				  <?php }
					}?>
				</select>
			</div>
			<div class="form-group">
				<label class="control-label">Estado <span class="text-danger">*</span></label>
				<select class="form-control" id="EstadoBodega" name="EstadoBodega">
					 <option value="Y" <?php if(($edit==1)&&($row['estado']=="Y")){echo "selected=\"selected\"";}?>>ACTIVO</option>
					 <option value="N" <?php if(($edit==1)&&($row['estado']=="N")){echo "selected=\"selected\"";}?>>INACTIVO</option>
				 </select>
			</div>
			<div class="form-group">
				<label class="control-label">Anexo</label>
				<?php if(($edit==1)&&($row['anexo']!="")){?>
					<br><a href="filedownload.php?file=<?php echo base64_encode($row['anexo']);?>&dir=<?php echo base64_encode($dir_new);?>" target="_blank" title="Descargar archivo" class="btn-link btn-xs"><i class="fa fa-download"></i> <?php echo $row['anexo'];?></a>														
				<?php }?>
				<?php if($edit==0){
						$Ruta=ObtenerVariable("PlanoGenericoMonitoreos");
						if($Ruta!=""){
							$ar=explode("\\",$Ruta);
							$Plano=end($ar);
							echo "<br><strong>Por defecto: </strong>".$Plano;
						}
					}
				?>
				<input name="AnexoBodega" type="file" id="AnexoBodega" class="m-t-md" />
			</div>
			<?php }elseif($doc=="Productos"){?>
			<div class="form-group">
				<label class="control-label">Código de producto <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="CodigoProducto" id="CodigoProducto" required autocomplete="off" value="<?php if($edit==1){echo $row['id_producto_puerto'];}?>">
			</div>
			<div class="form-group">
				<label class="control-label">Nombre de producto <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="NombreProducto" id="NombreProducto" required autocomplete="off" value="<?php if($edit==1){echo $row['producto_puerto'];}?>">
			</div>
			<div class="form-group">
				<label class="control-label">Comentarios</label>
				<textarea name="ComentariosProducto" rows="3" maxlength="3000" class="form-control" id="ComentariosProducto" type="text"><?php if($edit==1){echo $row['comentarios'];}?></textarea>
			</div>
			<div class="form-group">
				<label class="control-label">Estado <span class="text-danger">*</span></label>
				<select class="form-control" id="EstadoProducto" name="EstadoProducto">
					 <option value="Y" <?php if(($edit==1)&&($row['estado']=="Y")){echo "selected=\"selected\"";}?>>ACTIVO</option>
					 <option value="N" <?php if(($edit==1)&&($row['estado']=="N")){echo "selected=\"selected\"";}?>>INACTIVO</option>
				 </select>
			</div>
			<?php }elseif($doc=="Transportes"){?>
			<div class="form-group">
				<label class="control-label">Código de motonave <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="CodigoTransporte" id="CodigoTransporte" required autocomplete="off" value="<?php if($edit==1){echo $row['id_transporte_puerto'];}?>">
			</div>
			<div class="form-group">
				<label class="control-label">Nombre de motonave <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="NombreTransporte" id="NombreTransporte" required autocomplete="off" value="<?php if($edit==1){echo $row['transporte_puerto'];}?>">
			</div>
			<div class="form-group">
				<label class="control-label">REG (Registro capitanía)</label>
				<input type="text" class="form-control" name="RegistroCap" id="RegistroCap" autocomplete="off" value="<?php if($edit==1){echo $row['registro_capitania'];}?>">
			</div>
			<div class="form-group">
				<label class="control-label">Comentarios</label>
				<textarea name="ComentariosTransporte" rows="3" maxlength="3000" class="form-control" id="ComentariosTransporte" type="text"><?php if($edit==1){echo $row['comentarios'];}?></textarea>
			</div>
			<div class="form-group">
				<label class="control-label">Estado <span class="text-danger">*</span></label>
				<select class="form-control" id="EstadoTransporte" name="EstadoTransporte">
					 <option value="Y" <?php if(($edit==1)&&($row['estado']=="Y")){echo "selected=\"selected\"";}?>>ACTIVO</option>
					 <option value="N" <?php if(($edit==1)&&($row['estado']=="N")){echo "selected=\"selected\"";}?>>INACTIVO</option>
				 </select>
			</div>
			<?php }elseif($doc=="TipoInfectacion"){?>
			<div class="form-group">
				<label class="control-label">Código de infestación <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="Codigo" id="Codigo" required autocomplete="off" value="<?php if($edit==1){echo $row['id_tipo_infectacion_producto'];}?>">
			</div>
			<div class="form-group">
				<label class="control-label">Nombre de infestación <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="Nombre" id="Nombre" required autocomplete="off" value="<?php if($edit==1){echo $row['tipo_infectacion_producto'];}?>">
			</div>
			<div class="form-group">
				<label class="control-label">Comentarios</label>
				<textarea name="Comentarios" rows="3" maxlength="3000" class="form-control" id="Comentarios" type="text"><?php if($edit==1){echo $row['comentarios'];}?></textarea>
			</div>
			<div class="form-group">
				<label class="control-label">Estado <span class="text-danger">*</span></label>
				<select class="form-control" id="Estado" name="Estado">
					 <option value="Y" <?php if(($edit==1)&&($row['estado']=="Y")){echo "selected=\"selected\"";}?>>ACTIVO</option>
					 <option value="N" <?php if(($edit==1)&&($row['estado']=="N")){echo "selected=\"selected\"";}?>>INACTIVO</option>
				 </select>
			</div>
			<?php }elseif($doc=="GradoInfectacion"){?>
			<div class="form-group">
				<label class="control-label">Código de grado infestación <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="Codigo" id="Codigo" required autocomplete="off" value="<?php if($edit==1){echo $row['id_grado_infectacion'];}?>">
			</div>
			<div class="form-group">
				<label class="control-label">Nombre de grado infestación <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="Nombre" id="Nombre" required autocomplete="off" value="<?php if($edit==1){echo $row['grado_infectacion'];}?>">
			</div>
			<div class="form-group">
				<label class="control-label">Comentarios</label>
				<textarea name="Comentarios" rows="3" maxlength="3000" class="form-control" id="Comentarios" type="text"><?php if($edit==1){echo $row['comentarios'];}?></textarea>
			</div>
			<div class="form-group">
				<label class="control-label">Estado <span class="text-danger">*</span></label>
				<select class="form-control" id="Estado" name="Estado">
					 <option value="Y" <?php if(($edit==1)&&($row['estado']=="Y")){echo "selected=\"selected\"";}?>>ACTIVO</option>
					 <option value="N" <?php if(($edit==1)&&($row['estado']=="N")){echo "selected=\"selected\"";}?>>INACTIVO</option>
				 </select>
			</div>
			<?php }elseif($doc=="Muelles"){?>
			<div class="form-group">
				<label class="control-label">Código de muelle <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="Codigo" id="Codigo" required autocomplete="off" value="<?php if($edit==1){echo $row['id_muelle_puerto'];}?>">
			</div>
			<div class="form-group">
				<label class="control-label">Nombre de muelle <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="Nombre" id="Nombre" required autocomplete="off" value="<?php if($edit==1){echo $row['muelle_puerto'];}?>">
			</div>
			<div class="form-group">
				<label class="control-label">Comentarios</label>
				<textarea name="Comentarios" rows="3" maxlength="3000" class="form-control" id="Comentarios" type="text"><?php if($edit==1){echo $row['comentarios'];}?></textarea>
			</div>
			<div class="form-group">
				<label class="control-label">Estado <span class="text-danger">*</span></label>
				<select class="form-control" id="Estado" name="Estado">
					 <option value="Y" <?php if(($edit==1)&&($row['estado']=="Y")){echo "selected=\"selected\"";}?>>ACTIVO</option>
					 <option value="N" <?php if(($edit==1)&&($row['estado']=="N")){echo "selected=\"selected\"";}?>>INACTIVO</option>
				 </select>
			</div>
			<?php }?>
		</div>
	</div>
</div>
<div class="modal-footer">
	<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i> Aceptar</button>
	<?php if($edit==1){?><button type="button" class="btn btn-danger m-t-md pull-left" onClick="Eliminar('<?php echo $doc;?>','<?php echo $id;?>');"><i class="fa fa-trash"></i> Eliminar</button><?php }?>
	<button type="button" class="btn btn-warning m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
</div>
	<input type="hidden" id="TipoDoc" name="TipoDoc" value="<?php echo $doc; ?>" />
	<input type="hidden" id="ID_Actual" name="ID_Actual" value="<?php echo $id; ?>" />
	<input type="hidden" id="Metodo" name="Metodo" value="<?php echo $Metodo; ?>" />
	<input type="hidden" id="frmType" name="frmType" value="1" />
</form>
<script>
 $(document).ready(function(){
	 $("#frm_NewParam").validate({
		 submitHandler: function(form){
			 let Metodo = document.getElementById("Metodo").value;
			 if(Metodo!="3"){
				 Swal.fire({
					title: "¿Está seguro que desea guardar los datos?",
					icon: "question",
					showCancelButton: true,
					confirmButtonText: "Si, confirmo",
					cancelButtonText: "No"
				}).then((result) => {
					if (result.isConfirmed) {
						$('.ibox-content').toggleClass('sk-loading',true);
						form.submit();
					}
				});	
			 }else{
				$('.ibox-content').toggleClass('sk-loading',true);
				form.submit();
			 }	 
		}
	 });
	$('.chosen-select').chosen({width: "100%"});
	 
	<?php if($doc=="Bodegas"){?>

		 $("#NombreClienteBodega").change(function(){
			var NomCliente=document.getElementById("NombreClienteBodega");
			var Cliente=document.getElementById("ClienteBodega");
			if(NomCliente.value==""){
				Cliente.value="";
				$("#ClienteBodega").trigger("change");
			}	
		});
		$("#ClienteBodega").change(function(){
			var Cliente=document.getElementById("ClienteBodega");
			$.ajax({
				type: "POST",
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Cliente.value+"&sucline=1&selec=1",
				success: function(response){
					$("#SucursalBodega").chosen("destroy");
					$('#SucursalBodega').html(response);
					$('#SucursalBodega').chosen({width: "100%"});
				}
			});	
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
					  var value = $("#NombreClienteBodega").getSelectedItemData().CodigoCliente;
					  $("#ClienteBodega").val(value).trigger("change");
				  }
			  }
		 };
		$("#NombreClienteBodega").easyAutocomplete(options);
 	<?php }?>
 });
</script>
<script>
function Eliminar(doc,id){
	var result=true;
	
	Swal.fire({
		title: "¿Está seguro que desea eliminar este registro?",
		icon: "question",
		showCancelButton: true,
		confirmButtonText: "Si, confirmo",
		cancelButtonText: "No"
	}).then((result) => {
		if (result.isConfirmed) {
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:34,
					  item:doc,
					  id:id
					 },
				dataType:'json',
				async: false,
				success: function(data){
					if(data.Estado=='0'){
						result=false;
						Swal.fire({
							title: data.Title,
							text: data.Mensaje,
							icon: data.Icon,
						});	
						$('.ibox-content').toggleClass('sk-loading',false);
					}else{
						document.getElementById("Metodo").value="3";
						$("#frm_NewParam").submit();
					}
				}
			});
		}
	});	
	
	return result;
}
</script>