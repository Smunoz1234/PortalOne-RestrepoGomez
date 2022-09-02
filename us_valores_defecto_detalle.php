<?php
require_once( "includes/conexion.php" );
//require_once("includes/conexion_hn.php");
if(isset($_GET['user'])&&$_GET['user']!=""){
  	$IdUsuario = base64_decode($_GET['user']);
	$TipoDoc = base64_decode($_GET['tipodoc']);
}else{
  	$IdUsuario = "";
	$TipoDoc = "";
}

$SQL=Seleccionar("uvw_tbl_CamposValoresDefecto","*","TipoObjeto='".$TipoDoc."'");
?>
<div class="form-group">
	<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-pencil-square"></i> Ingrese los valores de cada campo</h3></label>
</div>
 <?php while($row=sqlsrv_fetch_array($SQL)){
		$SQL_Detalle=Seleccionar("uvw_tbl_CamposValoresDefecto_Detalle","*","TipoObjeto='".$TipoDoc."' and ID_Usuario='".$IdUsuario."' and ID_Campo='".$row['ID_Campo']."'",'NombreUsuario');
		$row_Detalle=sqlsrv_fetch_array($SQL_Detalle);
?>
	<div class="row m-b-xs">
		<div class="form-group">
			<label class="col-lg-1 control-label"><?php echo $row['LabelCampo'];?><br><span class="text-muted"><?php echo $row['NombreCampo'];?></span></label>
			<div class="col-lg-3">
				<input name="ValorCampo<?php echo $row['ID_Campo'];?>" type="text" class="form-control" id="ValorCampo<?php echo $row['ID_Campo'];?>" value="<?php echo $row_Detalle['ValorCampo'];?>">
			</div>
		</div>
	</div>
<?php }?>	