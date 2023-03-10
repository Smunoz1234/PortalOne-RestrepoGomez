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
			<div class="col-lg-4">
			 <ul class="folder-list" style="padding: 0">
			<?php while($row_Anexo=sqlsrv_fetch_array($SQL_Anexo)){
					$Icon=IconAttach($row_Anexo['FileExt']);
				 ?>
				<li><a href="attachdownload.php?file=<?php echo base64_encode($row_Anexo['AbsEntry']);?>&line=<?php echo base64_encode($row_Anexo['Line']);?>" target="_blank" class="btn-link btn-xs"><i class="<?php echo $Icon;?>"></i> <?php echo $row_Anexo['NombreArchivo'];?></a></li>
			<?php }?>
			 </ul>
			</div>
		</div>
<?php }else{ echo "<p>Sin anexos.</p>"; }
}
if($PedirAnexo=="SI"){LimpiarDirTemp();?>
<h3>Documento de identidad (Frontal)</h3>
<div class="form-group">
	<div class="col-lg-4">
		<input id="FileCC1" name="FileCC1[]" type="file" multiple>
		<input id="FileCC1Val" name="FileCC1Val" type="hidden" value="" />
		<div id="msg_errorCC1"></div>
	</div>
</div>
<br>
<h3>Documento de identidad (Posterior)</h3>
<div class="form-group">
	<div class="col-lg-4">
		<input id="FileCC2" name="FileCC2[]" type="file" multiple>
		<input id="FileCC2Val" name="FileCC2Val" type="hidden" value="" />
		<div id="msg_errorCC2"></div>
	</div>
</div>
<br>
<h3>Fotocopia del documento de servicios públicos</h3>
<div class="form-group">
	<div class="col-lg-4">
		<input id="FileSP" name="FileSP[]" type="file" multiple>
		<input id="FileSPVal" name="FileSPVal" type="hidden" value="" />
		<div id="msg_errorSP"></div>
	</div>
</div>
<br>
<h3>Foto predio del lugar</h3>
<div class="form-group">
	<div class="col-lg-4">
		<input id="FilePR" name="FilePR[]" type="file" multiple>
		<input id="FilePRVal" name="FilePRVal" type="hidden" value="" />
		<div id="msg_errorPR"></div>
	</div>
</div>
<br>
<h3>Firma</h3>
<div class="form-group">
	<div class="col-lg-4">
		<button class="btn btn-primary" type="button" id="Firmar" onClick="AbrirFirma();"><i class="fa fa-pencil-square-o"></i> Realizar firma</button> 
		<input type="hidden" id="SigCliente" name="SigCliente" form="EditarSN" value="" />
		<div id="msgInfoSig" style="display: none;" class="alert alert-info"><i class="fa fa-info-circle"></i> El documento ya ha sido firmado.</div>
	</div>
</div>
<div class="form-group">
	<div class="col-lg-4">
		<img id="ImgSigPrev" style="display: none; max-width: 100%; height: auto;" src="" alt="" />
	</div>
</div>
<script>
function AbrirFirma(){
	var posicion_x;
	var posicion_y;
	posicion_x=(screen.width/2)-(1200/2);  
	posicion_y=(screen.height/2)-(500/2);
	self.name='opener';
	remote=open('popup_firma.php','remote',"width=1200,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
	remote.focus();	
}	
</script>
<script>
$(document).ready(function(){
	//console.log('Inicio de consola');
	var CC=document.getElementById("LicTradNum");
	var Lat=document.getElementById("Latitud");
	var Long=document.getElementById("Longitud");
	
	$("#FileCC1").fileinput({
		language: 'es',
		uploadUrl: "upload_sn.php",
		uploadExtraData:{id:'1',doc:CC.value},
		uploadAsync: true,
		showPreview: false,
		showUpload: false, // hide upload button
		showRemove: true, // hide remove button
		minFileCount: 1,
		maxFileCount: 1,
		elErrorContainer: '#msg_errorCC1',
		allowedFileExtensions: ['jpg','png']
	}).on('fileselect', function(event, numFiles, label) {
		$("#FileCC1Val").val(label);
	}).on('fileclear', function(event) {
		$("#FileCC1Val").val("");
	}).on("filebatchselected", function(event, files) {
		$(this).fileinput("upload");
	});
	
	$("#FileCC2").fileinput({
		language: 'es',
		uploadUrl: "upload_sn.php",
		uploadExtraData:{id:'2',doc:CC.value},
		uploadAsync: true,
		showPreview: false,
		showUpload: false, // hide upload button
		showRemove: true, // hide remove button
		minFileCount: 1,
		maxFileCount: 1,
		elErrorContainer: '#msg_errorCC2',
		allowedFileExtensions: ['jpg','png']
	}).on('fileselect', function(event, numFiles, label) {
		$("#FileCC2Val").val(label);
	}).on('fileclear', function(event) {
		$("#FileCC2Val").val("");
	}).on("filebatchselected", function(event, files) {
		$(this).fileinput("upload");
	});
	
	$("#FileSP").fileinput({
		language: 'es',
		uploadUrl: "upload_sn.php",
		uploadExtraData:{id:'3',doc:CC.value},
		uploadAsync: true,
		showPreview: false,
		showUpload: false, // hide upload button
		showRemove: true, // hide remove button
		minFileCount: 1,
		maxFileCount: 1,
		elErrorContainer: '#msg_errorSP',
		allowedFileExtensions: ['jpg','png']
	}).on('fileselect', function(event, numFiles, label) {
		$("#FileSPVal").val(label);
	}).on('fileclear', function(event) {
		$("#FileSPVal").val("");
	}).on("filebatchselected", function(event, files) {
		$(this).fileinput("upload");
	});
	
	$("#FilePR").fileinput({
		language: 'es',
		uploadUrl: "upload_sn.php",
		uploadExtraData:{id:'4',doc:CC.value,Lat:Lat.value,Long:Long.value},
		uploadAsync: true,
		showPreview: false,
		showUpload: false, // hide upload button
		showRemove: true, // hide remove button
		minFileCount: 1,
		maxFileCount: 1,
		elErrorContainer: '#msg_errorPR',
		allowedFileExtensions: ['jpg','png']
	}).on('fileselect', function(event, numFiles, label) {
		$("#FilePRVal").val(label);
	}).on('fileclear', function(event) {
		$("#FilePRVal").val("");
	}).on("filebatchselected", function(event, files) {
		$(this).fileinput("upload");
	});
});
</script>
<?php }?>