<?php  
require_once("includes/conexion.php");

$edit = isset($_POST['edit']) ? $_POST['edit'] : 0;
$id = isset($_POST['id']) ? $_POST['id'] : "";

$Title="Crear nuevo formato";
$Type=1;
$swOtro=1;
$swOtroSerie=1;


$SQL_TipoDoc=Seleccionar("uvw_tbl_ObjetosSAP","*",'','CategoriaObjeto, DeTipoDocumento');

if($edit==1){
	$SQL_Data=Seleccionar("uvw_tbl_FormatosSAP","*","ID='".$id."'");
	$row_Data=sqlsrv_fetch_array($SQL_Data);
	$Title="Editar formato";
	$Type=2;
	
	$SQL_Series=Seleccionar('uvw_Sap_tbl_SeriesDocumentos','IdSeries, DeSeries',"IdTipoDocumento='".$row_Data['ID_Objeto']."'",'DeSeries');
}

?>
<form id="frm_NewParam" method="post" action="parametros_formatos_impresion.php" enctype="multipart/form-data">
	<div class="modal-header">
		<h4 class="modal-title">
			<?php echo $Title; ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="form-group">
			<div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
				<div class="form-group">
					<label class="control-label">Tipo de documento <span class="text-danger">*</span></label>
					<select name="TipoDoc" class="form-control" id="TipoDoc" required>
							<option value="">Seleccione...</option>
					  <?php $CatActual="";
						while($row_TipoDoc=sqlsrv_fetch_array($SQL_TipoDoc)){
							if($CatActual!=$row_TipoDoc['CategoriaObjeto']){
								echo "<optgroup label='".$row_TipoDoc['CategoriaObjeto']."'></optgroup>";
								$CatActual=$row_TipoDoc['CategoriaObjeto'];
							}
						?>
							<option value="<?php echo $row_TipoDoc['IdTipoDocumento']."__".$row_TipoDoc['DeTipoDocumento'];?>" <?php if((($edit==1)&&(isset($row_Data['ID_Objeto']))&&(strcmp($row_TipoDoc['IdTipoDocumento'],$row_Data['ID_Objeto'])==0))){ echo "selected=\"selected\""; $swOtro=0;}?>><?php echo $row_TipoDoc['DeTipoDocumento'];?></option>
					  <?php }?>
						<optgroup label='Otros'></optgroup>
						<option value="OTRO" <?php if(($edit==1)&&($swOtro==1&&$row_Data['ID_Objeto']!="")){echo "selected=\"selected\"";}?>>OTRO</option>
					</select>
				</div>
				<div id="dvIdDoc" class="form-group" <?php if((($edit==1)&&($swOtro==0))||($edit==0)){echo 'style="display: none;"';}?>>
					<label class="control-label">Id del documento <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="IDDocumento" id="IDDocumento" required autocomplete="off" value="<?php if($edit==1){echo $row_Data['ID_Objeto'];}?>">
				</div>
				<div id="dvNomDoc" class="form-group" <?php if((($edit==1)&&($swOtro==0))||($edit==0)){echo 'style="display: none;"';}?>>
					<label class="control-label">Nombre del documento <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="NombreDocumento" id="NombreDocumento" required autocomplete="off" value="<?php if($edit==1){echo $row_Data['DE_Objeto'];}?>">
				</div>
				<div id="dvSerieFormato" class="form-group" <?php if((($edit==1)&&($swOtro==1))){echo 'style="display: none;"';}?>>
					<label class="control-label">Serie del formato <span class="text-danger">*</span></label>
					<select name="SerieDoc" class="form-control" id="SerieDoc" required="required">
						<option value="">Seleccione...</option>
					  <?php if($edit==1){
								while($row_Series=sqlsrv_fetch_array($SQL_Series)){?>
								<option value="<?php echo $row_Series['IdSeries'];?>" <?php if(($edit==1)&&(isset($row_Data['IdFormato']))&&(strcmp($row_Series['IdSeries'],$row_Data['IdFormato'])==0)){ echo "selected=\"selected\""; $swOtroSerie=0;}?>><?php echo $row_Series['DeSeries'];?></option>
					  <?php 	}
							}?>
						<option value="OTRO" <?php if(($edit==1)&&($swOtroSerie==1&&$row_Data['IdFormato']!=="")){echo "selected=\"selected\"";}?>>OTRO</option>
					</select>
				</div>
				<div id="dvIDFormato" class="form-group" <?php if((($edit==1)&&($swOtro==0)&&($swOtroSerie==0))||($edit==0)){echo 'style="display: none;"';}?>>
					<label class="control-label">Id del formato <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="IDFormato" id="IDFormato" required autocomplete="off" value="<?php if($edit==1){echo $row_Data['IdFormato'];}?>">
				</div>
				<div class="form-group">
					<label class="control-label">Nombre a mostrar <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="NombreVisualizar" id="NombreVisualizar" required autocomplete="off" value="<?php if($edit==1){echo $row_Data['NombreVisualizar'];}?>">
				</div>
				<div class="form-group">
					<label class="control-label">Ver en documento <span class="text-danger">*</span></label>
					<select name="VerDocumento" class="form-control" id="VerDocumento" required>
						<option value="Y" <?php if(($edit==1)&&($row_Data['VerEnDocumento']=="Y")){ echo "selected=\"selected\"";}?>>SI</option>
						<option value="N" <?php if(($edit==1)&&($row_Data['VerEnDocumento']=="N")){ echo "selected=\"selected\"";}?>>NO</option>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Comentarios</label>
					<textarea name="Comentarios" rows="2" class="form-control" id="Comentarios"><?php if($edit==1){echo $row_Data['Comentarios'];}?></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Nombre del archivo <span class="text-danger">*</span></label>
					<?php if($edit==1){?><input type="text" class="form-control" name="NombreArchivo" id="NombreArchivo" required autocomplete="off" value="<?php if($edit==1){echo $row_Data['DeFormato'];}?>"><?php }?>
					<input name="FileNombreArchivo" type="file" id="FileNombreArchivo" class="m-t-md" <?php if($edit==0){echo "required";}?> />
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i> Aceptar</button>
		<button type="button" class="btn btn-danger m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
	</div>
	<input type="hidden" id="MM_Insert" name="MM_Insert" value="1" />
	<input type="hidden" id="id" name="id" value="<?php echo $id;?>" />
	<input type="hidden" id="type" name="type" value="<?php echo $Type;?>" />
</form>
<script>
 $(document).ready(function(){
	 $("#frm_NewParam").validate({
		 submitHandler: function(form){
			if(Validar()){
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
			 }
		}
	 });
	 
	 $("#TipoDoc").change(function(){
		$('.ibox-content').toggleClass('sk-loading',true);
		 var ar=document.getElementById('TipoDoc').value.split("__");
		 var TipoDoc=ar[0];
		 if(TipoDoc!="OTRO"){
			 $.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=25&id="+TipoDoc,
				success: function(response){
					$('#SerieDoc').html(response);
					$('#SerieDoc').append($("<option>",{value:"OTRO",text: "OTRO"}));
					$('.ibox-content').toggleClass('sk-loading',false);
					toggleOtrosFormatos(false);
				}
			});	
		 }else{
			toggleOtrosFormatos(true);
			$('.ibox-content').toggleClass('sk-loading',false);
		 }			
	});
	 
	 $("#SerieDoc").change(function(){
		$('.ibox-content').toggleClass('sk-loading',true);
		 var SerieDoc=document.getElementById('SerieDoc').value;
		 if(SerieDoc=="OTRO"){
			document.getElementById('dvIDFormato').style.display='block';
			 $('.ibox-content').toggleClass('sk-loading',false);
		 }else{
			document.getElementById('dvIDFormato').style.display='none';
			$('.ibox-content').toggleClass('sk-loading',false);
		 }			
	});
	 
 });
</script>
<script>
function toggleOtrosFormatos(state=false){
	if(state){//Mostrar los campos de otros formatos
		document.getElementById('dvIdDoc').style.display='block';		
		document.getElementById('dvNomDoc').style.display='block';
		document.getElementById('dvIDFormato').style.display='block';
		document.getElementById('dvSerieFormato').style.display='none';
	}else{//Ocultar los campos de otros formatos
		document.getElementById('dvIdDoc').style.display='none';	
		document.getElementById('dvNomDoc').style.display='none';
		document.getElementById('dvIDFormato').style.display='none';
		document.getElementById('dvSerieFormato').style.display='block';
	}
}
	
function Validar(){
	let result=true;
	
	$('.ibox-content').toggleClass('sk-loading',true);
	
	let archivo=document.getElementById("FileNombreArchivo").value;
	let ext=".rpt";
	let idObj="";
	let idFormato="";
	
	let ar=document.getElementById('TipoDoc').value.split("__");
	let TipoDoc=ar[0];
	
	let id=document.getElementById('id').value;
	
	if(TipoDoc!="OTRO"){
		idObj=TipoDoc;
		idFormato=document.getElementById('SerieDoc').value;
	}else{
		idObj=document.getElementById('IDDocumento').value;
		idFormato=document.getElementById('IDFormato').value;
	}
	
//	$.ajax({
//		url:"ajx_buscar_datos_json.php",
//		data:{type:36,
//			  id:id,
//			  idObj:idObj,
//			  idFormato:idFormato
//			 },
//		dataType:'json',
//		async: false,
//		success: function(data){
//			if(data.Result=='1'){
//				result=false;
//				Swal.fire({
//					title: '¡Advertencia!',
//					text: 'Ya existe un formato relacionado a este documento con este ID del formato. Por favor verifique.',
//					icon: 'warning',
//				});	
//				$('.ibox-content').toggleClass('sk-loading',false);
//			}else{
//				$('.ibox-content').toggleClass('sk-loading',false);
//			}
//		}
//	});	
	
	if(archivo!=""){
		let ext_archivo=(archivo.substring(archivo.lastIndexOf("."))).toLowerCase();
		if(ext_archivo!=ext){
			result=false;
			Swal.fire({
				title: '¡Advertencia!',
				text: 'El archivo debe ser extensión .rpt, por favor verifique.',
				icon: 'warning',
			});	
		}
	}
	
	
	return result;
}

</script>