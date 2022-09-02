<?php 
if(isset($_GET['id'])&&($_GET['id']!="")){
	require_once("includes/conexion.php");
	
	$edit=$_GET['edit'];
	$objtype=$_GET['objtype'];
	
	if($edit==1){//Creando
		//Consultar los seriales del articulo
		//Si se cambia este procedimiento, cambiar tambien en ajx_buscar_datos_json.php (id: 20)
		$Parametros=array(
			"'".$_GET['id']."'",
			"'".$_GET['whscode']."'",
			"'".$_GET['tipotrans']."'",
			"'".$_GET['cardcode']."'"
		);
		$SQL=EjecutarSP('sp_ConsultarInventarioSeriales',$Parametros,0,2);
		
		$TotalSerEnt=SumarTotalSerialesEntregar($_GET['id'], $_GET['linenum'], $_GET['whscode'], $_GET['cardcode'], $objtype, $_GET['usuario']);
		
	}else{//Consultando
		$Parametros=array(
			"'".$objtype."'",
			"'".$_GET['docentry']."'",
			"'".$_GET['linenum']."'",
			"'".$_GET['id']."'"
		);
		$SQL=EjecutarSP('sp_ConsultarSerialDocSAP',$Parametros,0,2);
	}
	
?>
<!doctype html>
<html>
<head>
<?php include_once("includes/cabecera.php"); ?>
<style>
	.iboxedit{
		padding: 10px !important;	
	}
	body{
		background-color: #ffffff;
	}
	<?php if($edit==1){?>
	.tableedit > tbody > tr > td{
		padding-left: 8px !important;
		vertical-align: middle;
		padding-top: 1px !important;
		padding-bottom: 1px !important;
	}
	<?php }else{?>
	.tableedit > tbody > tr > td{
		padding-left: 8px !important;
		vertical-align: middle;
	}
	<?php }?>
</style>
<?php if($edit==1){?>
<script>
var DespVal=0;
function ActualizarDatos(idserial,sysnumber, fechavenc, fechaadmin){//Actualizar datos asincronicamente
	$.ajax({
		type: "GET",
		url: "includes/procedimientos.php?type=18&edit=<?php echo $edit;?>&objtype=<?php echo $objtype;?>&linenum=<?php echo $_GET['linenum'];?>&itemcode=<?php echo $_GET['id'];?>&itemname=<?php echo $_GET['itemname'];?>&und=<?php echo $_GET['und'];?>&whscode=<?php echo $_GET['whscode'];?>&distnumber="+idserial+"&sysnumber="+sysnumber+"&fechavenc="+fechavenc+"&fechaadmin="+fechaadmin+"&cant="+document.getElementById("ItemCode"+idserial).value+"&cardcode=<?php echo $_GET['cardcode'];?>&usuario=<?php echo $_GET['usuario'];?>",
		success: function(response){
			if(response!="Error"){
				document.getElementById('TimeAct').innerHTML="<strong>Actualizado:</strong> "+response;
				CalcularTotal(idserial,sysnumber,fechavenc);
			}
		}
	});
}
function CalcularTotal(idserial,sysnumber, fechavenc, fechaadmin){
	$.ajax({
		type: "GET",
		url: "includes/procedimientos.php?type=19&edit=<?php echo $edit;?>&objtype=<?php echo $objtype;?>&linenum=<?php echo $_GET['linenum'];?>&itemcode=<?php echo $_GET['id'];?>&whscode=<?php echo $_GET['whscode'];?>&cardcode=<?php echo $_GET['cardcode'];?>&usuario=<?php echo $_GET['usuario'];?>",
		success: function(response){
			if(response!="Error"){
				var TotalEnt=response.replace(/,/g, '');
				var CantSalida='<?php echo $_GET['cant'];?>';
				if(parseFloat(TotalEnt)>parseFloat(CantSalida)){
					swal({
						title: "¡Lo sentimos!",
						text: "La cantidad total es mayor a la cantidad a entregar.",
						type: "error",
						confirmButtonText: "OK"
					});
					document.getElementById("ItemCode"+idserial).value='0';
					PonerQuitarClase("Select"+idserial,2);
					$("#Select"+idserial).prop("checked", false);
					//document.getElementById('Select'+idserial).checked=false;
					ActualizarDatos(idserial, sysnumber, fechavenc, fechaadmin);
				}
				document.getElementById('TotalSerEnt').innerHTML=response;
			}
		}
	});
}
function SeleccionarSerial(){
	var Buscar = document.getElementById("BuscarSerialSelect");
	if(Buscar.value!=""){
		$('.ibox-content').toggleClass('sk-loading',true);
		$.ajax({
			url:"ajx_buscar_datos_json.php",
			data:{
				type:20,
				id:'<?php echo $_GET['id'];?>',
				whscode:'<?php echo $_GET['whscode'];?>',
				cardcode:'<?php echo $_GET['cardcode'];?>',
				tipotrans:'<?php echo $_GET['tipotrans'];?>',
				buscar:Buscar.value
			},
			dataType:'json',
			success: function(data){
				var id_result=data.IdSerial;
				var dv_msg = document.getElementById("MsgBuscar");
				dv_msg.innerHTML = '';
				
				if(id_result!=null){
					if($("#Select"+id_result).prop('checked')){
						$("#Select"+id_result).prop("checked", false);
					}else{
						$("#Select"+id_result).prop("checked", true);
					}
					
					if(DespVal==1){
						goToId("Select"+id_result);
					}
					
					$("#Select"+id_result).trigger('change');
					dv_msg.innerHTML = '<p class="text-info"><i class="fa fa-thumbs-up"></i> Serial encontrado</p>';
					Buscar.value='';
					Buscar.focus();
				}else{
					dv_msg.innerHTML = '<p class="text-danger"><i class="fa fa-times-circle-o"></i> Serial no encontrado</p>';
					Buscar.value='';
					Buscar.focus();
				}
			}
		});
		$('.ibox-content').toggleClass('sk-loading',false);
	}	
}
function goToId(idName){
	if($("#"+idName).length){
		var target_offset = $("#"+idName).offset();
		var target_top = target_offset.top;
		$('html,body').animate({scrollTop:target_top},{duration:"slow"});
	}
}
function PonerQuitarClase(idName,evento=1){
	if($("#"+idName).length){
		if(evento==1){
			$("#"+idName).parents('tr').addClass('bg-info');
		}else{
			$("#"+idName).parents('tr').removeClass('bg-info');
		}		
	}
}
function SetDesp(value){
	DespVal=value;
}
</script>
<?php }?>
</head>

<body>
	<div class="ibox-content iboxedit">
		<?php include("includes/spinner.php"); ?>
		<div class="row"> 
			<div class="col-lg-12">
				<form action="" method="post" class="form-horizontal" id="FrmSeriales">
					<?php if($edit==1){?>
					<div class="form-group">
						<label class="col-xs-12">
							<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-barcode"></i> Seriales disponibles: <?php echo base64_decode($_GET['itemname'])." (".$_GET['id'].")";?></h3>
						</label>
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Desplazamiento</label>
						<div class="col-lg-4">
							<label class="checkbox-inline i-checks"><input name="chkDesp" id="chkDesp" type="checkbox" value="1"> Activar desplazamiento</label>
						</div>					
					</div>
					<div class="form-group">
						<label class="col-lg-1 control-label">Buscar serial</label>
						<div class="col-lg-3">
							<input name="BuscarSerialSelect" type="text" autofocus class="form-control" id="BuscarSerialSelect" placeholder="Ingrese para buscar..." onChange="SeleccionarSerial();" value="">
						</div>
						<div class="col-lg-2">
							<div id="MsgBuscar"></div>
						</div>
						<div class="col-lg-6">
							<div class="col-xs-11">
								<h2 class="text-success pull-right"><strong>Total a entregar: </strong></h2>
							</div>
							<div class="col-xs-1">
								<h2 class="text-danger"><strong id="TotalSerEnt"><?php echo $TotalSerEnt;?></strong></h2>
							</div>
						</div>						
					</div>
					<div class="form-group">
						<div class="col-xs-12">
							<div id="TimeAct"  class="pull-right"></div>
						</div>
					</div>
					<table id="tabla_datos" width="100%" class="table table-bordered tableedit">
						<thead>
							<tr>
								<th>Serial</th>
								<th>Cantidad disponible</th>
								<th>Fecha de vencimiento</th>
								<th>Fecha de admisión</th>
								<th>Número de sistema</th>
								<th>Seleccionar serial</th>
							</tr>
						</thead>
						<tbody>
						<?php 
						while($row=sql_fetch_array($SQL,2)){
							//Consultar si hay datos ingresados en los lotes
							$Parametros=array(
								"'".$_GET['id']."'",
								"'".$_GET['linenum']."'",
								"'".$_GET['whscode']."'",
								"'".$row['IdSerial']."'",
								"'".$_GET['cardcode']."'",
								"'".$objtype."'",
								"'".$_SESSION['CodUser']."'"
							);
							$SQL_DtAct=EjecutarSP('sp_ConsultarSerialesDatos',$Parametros);
							$row_DtAct=sqlsrv_fetch_array($SQL_DtAct);
						?>
						<tr <?php if($row_DtAct['Cantidad']>0){?>class="bg-info"<?php }?>>
							<td><?php echo $row['IdSerial'];?></td>
							<td><?php echo number_format($row['Cantidad'],0);?></td>
							<td><?php echo $row['FechaVenciSerial'];?></td>
							<td><?php echo $row['FechaAdminSerial'];?></td>
							<td><?php echo $row['IdSysNumber'];?></td>
						  <td>
								<div class="checkbox checkbox-success">
									<input type="checkbox" id="Select<?php echo $row['IdSerial'];?>" onChange="VerificarCant('<?php echo $row['IdSerial'];?>','<?php echo $row['IdSysNumber'];?>','<?php echo number_format($row['Cantidad'],0);?>','<?php echo $row['FechaVenciSerial'];?>','<?php echo $row['FechaAdminSerial'];?>');" <?php if($row_DtAct['Cantidad']>0){?>checked="checked"<?php }?>><label></label>
								</div>
							<input type="hidden" id="ItemCode<?php echo $row['IdSerial'];?>" name="ItemCode[]" value="<?php echo number_format($row_DtAct['Cantidad'],0);?>" />
						  </td>
						</tr>
						<?php }?>
						</tbody>
					</table>
					<?php }else{?>
						<div class="form-group">
							<label class="col-xs-12">
								<h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-barcode"></i> Seriales: <?php echo base64_decode($_GET['itemname'])." (".$_GET['id'].")";?></h3>
							</label>
						</div>
						<table width="100%" class="table table-bordered tableedit dataTables-example">
						<thead>
							<tr>
								<th>Serial</th>
								<th>Unidad</th>
								<th>Fecha de vencimiento</th>
								<th>Fecha de admisión</th>
								<th>Número de sistema</th>
								<th>Cantidad entregada</th>
							</tr>
						</thead>
						<tbody>
						<?php 
						while($row=sql_fetch_array($SQL,2)){
						?>
						<tr>
							<td><?php echo $row['IdSerial'];?></td>
							<td><?php echo $row['UndMedida'];?></td>
							<td><?php echo $row['FechaVenciSerial'];?></td>
							<td><?php echo $row['FechaAdminSerial'];?></td>
							<td><?php echo $row['IdSysNumber'];?></td>
							<td><?php echo number_format($row['Cantidad']);?></td>
						</tr>
						<?php }?>
						</tbody>
					</table>
					<?php }?>
				</form>
			</div>
		</div>
	</div>
<?php if($edit==1){?>
<script>
function VerificarCant(id, sysnumber, cant_actual, fechavenc, fechaadmin){
	var CantIngresada=document.getElementById('ItemCode'+id);
	var Check = document.getElementById('Select'+id).checked;
    
	if(Check){
		CantIngresada.value='1';
		PonerQuitarClase("Select"+id);
    }else{
		CantIngresada.value='0';
		PonerQuitarClase("Select"+id,2);
	}
	
	ActualizarDatos(id, sysnumber, fechavenc, fechaadmin);
}
</script>
<?php }?>
<script>
	 $(document).ready(function(){
		 
		 $('.i-checks').iCheck({
			 checkboxClass: 'icheckbox_square-green',
             radioClass: 'iradio_square-green',
          });
		 
		<?php if($edit==1){?>
		 $("#BuscarSerialSelect").focus();
		 
		 $('#chkDesp').on('ifChecked', function(event){
			SetDesp(1);
		});
		$('#chkDesp').on('ifUnchecked', function(event){
			SetDesp(0);
		});
	 	<?php }?>
		 
		
		 
		var tabla = $('#tabla_datos').DataTable({
                pageLength: 100,
                dom: '<"html5buttons"B>lTfgitp',
				language: {
					"decimal":        "",
					"emptyTable":     "No se encontraron resultados.",
					"info":           "Mostrando _START_ - _END_ de _TOTAL_ registros",
					"infoEmpty":      "Mostrando 0 - 0 de 0 registros",
					"infoFiltered":   "(filtrando de _MAX_ registros)",
					"infoPostFix":    "",
					"thousands":      ",",
					"lengthMenu":     "Mostrar _MENU_ registros",
					"loadingRecords": "Cargando...",
					"processing":     "Procesando...",
					"search":         "Filtrar:",
					"zeroRecords":    "Ningún registro encontrado",
					"paginate": {
						"first":      "Primero",
						"last":       "Último",
						"next":       "Siguiente",
						"previous":   "Anterior"
					},
					"aria": {
						"sortAscending":  ": Activar para ordenar la columna ascendente",
						"sortDescending": ": Activar para ordenar la columna descendente"
					}
				},
				buttons: []
            });
	 });
</script>
</body>
</html>
<?php 
	sqlsrv_close($conexion);
}?>