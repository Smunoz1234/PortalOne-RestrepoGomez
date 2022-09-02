<?php  
require_once("includes/conexion.php");

//Estado
$SQL_EstadoFrm=Seleccionar('tbl_EstadoFormulario','*',"Cod_Estado <> 'O'");

$id=0;
$esArray=false;
$count=0;
$frm=isset($_POST['frm']) ? $_POST['frm'] : "";
$nomID=isset($_POST['nomID']) ? $_POST['nomID'] : "";

if(isset($_POST['id'])){
	if(is_array($_POST['id'])){
		$esArray=true;
		$count=count($_POST['id']);
		$id=implode(',',$_POST['id']);
	}else{
		$id=$_POST['id'];
	}	
}

?>
<div class="modal-header">
	<h4 class="modal-title">
		Cambiar estado <?php if($esArray){echo "en lote";}?><br>
		<?php if($esArray){?>
			<small>Cantidad: <?php echo $count;?></small>
		<?php }else{?>
			<small>ID: <?php echo $id;?></small>
		<?php }?>		
	</h4>
</div>
<div class="modal-body">
	<div class="form-group">
		<div class="ibox-content">
			<?php include("includes/spinner.php"); ?>
			<div class="form-group">
				<label class="control-label">Estado <span class="text-danger">*</span></label>
				<select name="Estado" class="form-control" id="Estado" required>
					<option value="">Seleccione...</option>
					 <?php while($row_EstadoFrm=sqlsrv_fetch_array($SQL_EstadoFrm)){?>
							<option value="<?php echo $row_EstadoFrm['Cod_Estado'];?>"><?php echo $row_EstadoFrm['NombreEstado'];?></option>
					  <?php }?>
				</select>
			</div>
			<div class="form-group">
				<label class="control-label">Comentarios <span class="text-danger">*</span></label>
				<textarea name="Comentarios" rows="5" class="form-control" id="Comentarios" placeholder="Ingrese sus comentarios..." required></textarea>
			</div>
		</div>
	</div>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-success m-t-md" onClick="GuardarDatos('<?php echo $id;?>');"><i class="fa fa-check"></i> Aceptar</button>
	<button type="button" class="btn btn-danger m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
</div>
	
<script>
	
function GuardarDatos(id){
	Swal.fire({
		title: "¿Está seguro que desea ejecutar el proceso?",
		text: "Se modificarán los estados de los registros",
		icon: "info",
		showCancelButton: true,
		confirmButtonText: "Si, confirmo",
		cancelButtonText: "No"
	}).then((result) => {
		if (result.isConfirmed) {
			EjecutarProceso(id);
		}
	});	
}

function EjecutarProceso(id){	
	$('.ibox-content').toggleClass('sk-loading',true);
	var estado = document.getElementById("Estado").value;
	var comentarios = document.getElementById("Comentarios").value;
	
	var esArray= <?php echo ($esArray) ? 'true' : 'false';?>;
	
	if(estado==""||comentarios==""){
		$('.ibox-content').toggleClass('sk-loading',false);
		Swal.fire({
			title: '¡Advertencia!',
			text: 'Debe llenar todos los campos',
			icon: 'warning'
		});
	}else{
		$.ajax({
			url:"ajx_ejecutar_json.php",
			data:{
				type:7,
				id:id,
				estado:estado,
				comentarios:comentarios,
				esArray:esArray,
				frm:'<?php echo $frm;?>',
				nomID:'<?php echo $nomID;?>'
			},
			dataType:'json',
			success: function(data){
				$('.ibox-content').toggleClass('sk-loading',false);
				Swal.fire({
					title: data.Title,
					text: data.Mensaje,
					icon: data.Icon
				});
				if(data.Estado==1){
					$('#myModal').modal("hide");
					if(esArray){
						var arrayID = id.split(",");
	//					console.log(arrayID);
						arrayID.forEach(function(value){
							$('#btnEstado'+value).hide();
							$('#dvChkSel'+value).remove();
							$('#comentCierre'+value).html(comentarios);
							if(estado=="C"){
								$('#lblEstado'+value).removeClass()
								$('#lblEstado'+value).addClass("label label-primary");
								$('#lblEstado'+value).html("Cerrado");
							}else if(estado=="A"){
								$('#lblEstado'+value).removeClass()
								$('#lblEstado'+value).addClass("label label-danger");
								$('#lblEstado'+value).html("Anulado");
							}						
						});
						$(".chkSelOT").prop("checked", false);
						$("#chkAll").prop("checked", false);
						$("#btnCambiarLote").attr("disabled","disabled");
						json=[];
						cant=0;
					}else{
						$('#btnEstado'+id).hide();
						$('#dvChkSel'+id).remove();
						$('#comentCierre'+id).html(comentarios);
						if(estado=="C"){
							$('#lblEstado'+id).removeClass()
							$('#lblEstado'+id).addClass("label label-primary");
							$('#lblEstado'+id).html("Cerrado");
						}else if(estado=="A"){
							$('#lblEstado'+id).removeClass()
							$('#lblEstado'+id).addClass("label label-danger");
							$('#lblEstado'+id).html("Anulado");
						}
					}
				}				
			},
			error: function(data){
				console.log('Error:', data)
				$('.ibox-content').toggleClass('sk-loading',false);
			}
		});	
	}
	
	
}
</script>