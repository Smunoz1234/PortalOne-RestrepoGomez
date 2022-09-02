<?php
require_once( "includes/conexion.php" );
//require_once("includes/conexion_hn.php");
if(isset($_GET['id'])&&$_GET['id']!=""){
  $IdUsuario = base64_decode($_GET['id']);
}else{
  $IdUsuario = "";
}
$SQL_TipoDoc=Seleccionar("uvw_tbl_ObjetosSAP","*",'','CategoriaObjeto, DeTipoDocumento');

?>
<div class="ibox">
  <div class="ibox-title bg-success">
    <h5><i class="fa fa-plus-circle"></i> Ingresar valores predeterminados en los campos de documentos</h5>
    <a class="collapse-link pull-right"> <i class="fa fa-chevron-up"></i> </a> </div>
  <div class="ibox-content">
	   <div class="row">
	  <div class="form-group">
		<label class="col-lg-1 control-label">Tipo de documento</label>
		<div class="col-lg-3">
			<select name="TipoDocumento" class="form-control" id="TipoDocumento" onChange="CargarDetalle();">
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
		<div class="col-lg-8">
			<button id="btnNewParam" type="button" class="btn btn-primary pull-right" onClick="CrearParametro();"><i class="fa fa-plus-circle"></i> Crear nuevo parámetro</button>
		</div>
	</div>	
	  </div>
	  <br>
	  <div class="row"> 
	 	<div id="dv_detalle"></div> 
	  </div>
    
  </div>
</div>
<script>
	function CargarDetalle(ID, DocNum){
		var TipoDocumento = document.getElementById("TipoDocumento");
		$('.ibox-content').toggleClass('sk-loading',true);
		$.ajax({
			type: "POST",
			async: false,
			url: "us_valores_defecto_detalle.php?user=<?php echo base64_encode($IdUsuario);?>&tipodoc="+Base64.encode(TipoDocumento.value),
			success: function(response){
				$('.ibox-content').toggleClass('sk-loading',false);
				$('#dv_detalle').html(response);
			}
		});
	}
</script>