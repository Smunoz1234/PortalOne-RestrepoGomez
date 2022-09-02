<?php  
require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
if(isset($_GET['edit'])&&$_GET['edit']==1){
	$CodCliente=base64_decode($_GET['id']);
	$edit=$_GET['edit'];
	$metod=$_GET['metod'];
	$EsProyecto=$_GET['esproyecto'];
	$PedirAnexo=$_GET['pediranexos'];
	$Anexo=base64_decode($_GET['anx']);
	//$Latitud="";
	//$Longitud="";
}else{
	$CodCliente="";
	$edit=$_GET['edit'];
	$metod="";
	$EsProyecto=$_GET['esproyecto'];
	$PedirAnexo=$_GET['pediranexos'];
	$Anexo=0;
	//$Latitud=base64_decode($_GET['Lat']);
	//$Longitud=base64_decode($_GET['Long']);
}


?>
<?php 
if($edit==1){
	if($Anexo!=0){
	$SQL_Anexo=Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos','*',"AbsEntry='".$Anexo."'");
?>
	<div class="form-group">
		<div class="col-xs-12">
			<?php while($row_Anexo=sqlsrv_fetch_array($SQL_Anexo)){
						$Icon=IconAttach($row_Anexo['FileExt']);?>
				<div class="file-box">
					<div class="file">
						<a href="attachdownload.php?file=<?php echo base64_encode($row_Anexo['AbsEntry']);?>&line=<?php echo base64_encode($row_Anexo['Line']);?>" target="_blank">
							<div class="icon">
								<i class="<?php echo $Icon;?>"></i>
							</div>
							<div class="file-name">
								<?php echo $row_Anexo['NombreArchivo'];?>
								<br/>
								<small><?php echo $row_Anexo['Fecha'];?></small>
							</div>
						</a>
					</div>
				</div>
			<?php }?>
		</div>
	</div>
<?php }else{ echo "<p>Sin anexos.</p>"; }
}
LimpiarDirTemp();?>
<div class="row">
	<form action="upload.php" class="dropzone" id="dropzoneForm" name="dropzoneForm">
		<div class="fallback">
			<input name="File" id="File" type="file" form="dropzoneForm" />
		</div>
	 </form>
</div>
<script>
 Dropzone.options.dropzoneForm = {
	paramName: "File", // The name that will be used to transfer the file
	maxFilesize: "<?php echo ObtenerVariable("MaxSizeFile");?>", // MB
	maxFiles: "<?php echo ObtenerVariable("CantidadArchivos");?>",
	uploadMultiple: true,
	addRemoveLinks: true,
	dictRemoveFile: "Quitar",
	acceptedFiles: "<?php echo ObtenerVariable("TiposArchivos");?>",
	dictDefaultMessage: "<strong>Haga clic aqui para cargar anexos</strong><br>Tambien puede arrastrarlos hasta aqui<br><h4><small>(máximo <?php echo ObtenerVariable("CantidadArchivos");?> archivos a la vez)<small></h4>",
	dictFallbackMessage: "Tu navegador no soporta cargue de archivos mediante arrastrar y soltar",
	removedfile: function(file) {
	  $.get( "includes/procedimientos.php", {
		type: "3",
		nombre: file.name
	  }).done(function( data ) {
		var _ref;
		return (_ref = file.previewElement) !== null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
		});
	 }
};
</script>