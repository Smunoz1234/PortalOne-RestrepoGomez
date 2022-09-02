<?php
require_once( "includes/conexion.php" );
//require_once("includes/conexion_hn.php");
if(isset($_POST['obj'])&&$_POST['obj']!=""){
	$TipoDoc = base64_decode($_POST['obj']);
}else{
	$TipoDoc = "";
}

$SQL=Seleccionar("uvw_tbl_CamposAdicionalesDoc","*","TipoObjeto='".$TipoDoc."'");
?>
	<div class="row m-b-xs">
		<div class="table-responsive">
			<table width="100%" class="table table-bordered">
				<thead>
					<tr>
						<th>Tipo documento</th>
						<th>Nombre interno del campo</th>
						<th>Nombre a mostrar</th>						
						<th>Tipo campo</th>
						<th>Valor por defecto</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>
					 <?php while($row=sqlsrv_fetch_array($SQL)){?>
					<tr>
						<td><?php echo $row['DE_Objeto'];?></td>
						<td><?php echo $row['NombreCampo'];?></td>
						<td><?php echo $row['LabelCampo'];?></td>
						<td><?php echo $row['TipoCampo'];?></td>
						<td></td>
						<td>
							<button type="button" id="btnEdit<?php echo $row['ID'];?>" class="btn btn-success btn-xs" onClick="EditarCampo('<?php echo $row['ID'];?>');"><i class="fa fa-pencil"></i> Editar</button>
							<button type="button" id="btnDel<?php echo $row['ID'];?>" class="btn btn-danger btn-xs" onClick="BorrarLinea('<?php echo $row['ID'];?>');"><i class="fa fa-trash"></i> Eliminar</button></td>
					</tr>	
					<?php }?>
				</tbody>
			</table>
		</div>
	</div>
<script>
function BorrarLinea(id){
	Swal.fire({
		title: "¿Está seguro que desea eliminar este campo?",
		text: "Este proceso no se puede revertir",
		icon: "warning",
		showCancelButton: true,
		confirmButtonText: "Si, confirmo",
		cancelButtonText: "No"
	}).then((result) => {
		if (result.isConfirmed) {
			$.ajax({
				type: "GET",
				url: "includes/procedimientos.php?type=41&linenum="+id,		
				success: function(response){
					$("#TipoDocumento").trigger('change');
				}
			});
		}
	});
}
</script>