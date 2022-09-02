<?php  
require_once("includes/conexion.php");

$TipoObjeto = isset($_GET['obj']) ? $_GET['obj'] : "";
$Num=0;

$SQL_Campos=Seleccionar("uvw_tbl_CamposAdicionalesDoc","*","TipoObjeto='".$TipoObjeto."'");
$Num=sqlsrv_num_rows($SQL_Campos);


?>
 <div class="modal inmodal fade" id="myModalFrmAdi" tabindex="1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">
						Campos adicionales del documento
					</h4>
				</div>
				<div class="modal-body">
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						 <?php 
						if($Num>0){
						while($row_Campos=sqlsrv_fetch_array($SQL_Campos)){?>
						<div class="form-group" style="margin-left: 5px !important; margin-right: 5px !important;">
							<label class="control-label"><?php echo $row_Campos['LabelCampo'];?></label>
							<?php if($row_Campos['TipoCampo']=="Texto"){?>
								<input type="text" class="form-control" name="<?php echo $row_Campos['NombreCampo'];?>" id="<?php echo $row_Campos['NombreCampo'];?>" autocomplete="off">
							<?php }elseif($row_Campos['TipoCampo']=="Comentario"){?>
								<textarea name="<?php echo $row_Campos['NombreCampo'];?>" rows="3" maxlength="3000" class="form-control" id="<?php echo $row_Campos['NombreCampo'];?>" type="text"></textarea>
							<?php }elseif($row_Campos['TipoCampo']=="Fecha"){?>
								<div class="input-group date">
									 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="<?php echo $row_Campos['NombreCampo'];?>" type="text" class="form-control" id="<?php echo $row_Campos['NombreCampo'];?>" value="" readonly="readonly">
								</div>
							<?php }?>
						</div>
						<?php }
						}else{
							echo "<i class='fa fa-info-circle'></i> No existen campos adicionales para este documento.";
						}?>							
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
				</div>
		</div>
	</div>
 </div>
<script>
 $(document).ready(function(){
	<?php  
	 	$SQL_Fechas=Seleccionar("uvw_tbl_CamposAdicionalesDoc","*","TipoObjeto='".$TipoObjeto."' and TipoCampo='Fecha'");
	  	while($row_Campos=sqlsrv_fetch_array($SQL_Fechas)){
	 ?>
		 $('#<?php echo $row_Campos['NombreCampo'];?>').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			todayHighlight: true,
			format: 'yyyy-mm-dd'
		});	
 	<?php }?>
	 
 });
</script>