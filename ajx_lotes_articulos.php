<?php 
if(isset($_GET['id'])&&($_GET['id']!="")){
	require_once("includes/conexion.php");
	
	$edit=$_GET['edit'];
	$objtype=$_GET['objtype'];
	$basetype=isset($_GET['basetype']) ? $_GET['basetype'] : "";
	$sentido=isset($_GET['sentido']) ? $_GET['sentido'] : "out";
	
	if($edit==1){//Creando
		
		//Consultar los lotes que se sacaron
		if($sentido=="in"){
			if($objtype==16){//Devolucion de venta
				$Parametros=array(
					"'".$basetype."'",
					"'".$_GET['base_entry']."'",
					"'".$_GET['baseline']."'",
					"'".$_GET['id']."'"
				);
				$SQL=EjecutarSP('sp_ConsultarLotesDocSAP',$Parametros);
				$TotalLotEnt=SumarTotalLotesEntregar($_GET['id'], $_GET['linenum'], $_GET['whscode'], $_GET['cardcode'], $objtype, $sentido, $_GET['usuario']);
			}
			if($objtype==20){//Entrada de compras
				$Parametros=array(
					"'".$_GET['id']."'",
					"'".$_GET['linenum']."'",
					"'".$_GET['whscode']."'",
					"''",
					"'".$_GET['cardcode']."'",
					"'".$objtype."'",
					"'".$sentido."'",
					"'".$_SESSION['CodUser']."'"
				);
				$SQL=EjecutarSP('sp_ConsultarLotesDatos',$Parametros);
				$TotalLotEnt=SumarTotalLotesEntregar($_GET['id'], $_GET['linenum'], $_GET['whscode'], $_GET['cardcode'], $objtype, $sentido, $_GET['usuario']);
			}
//			if($objtype==21){//Devolucion de compra
//				$Parametros=array(
//					"'".$basetype."'",
//					"'".$_GET['base_entry']."'",
//					"'".$_GET['baseline']."'",
//					"'".$_GET['id']."'"
//				);
//				$SQL=EjecutarSP('sp_ConsultarLotesDocSAP',$Parametros);
//				$TotalLotEnt=SumarTotalLotesEntregar($_GET['id'], $_GET['linenum'], $_GET['whscode'], $_GET['cardcode'], $objtype, $sentido, $_GET['usuario']);
//			}
		}else{//Consultar los lotes del articulo
			$Parametros=array(
				"'".$_GET['id']."'",
				"'".$_GET['whscode']."'"
			);
			$SQL=EjecutarSP('sp_ConsultarInventarioLotes',$Parametros);
			$TotalLotEnt=SumarTotalLotesEntregar($_GET['id'], $_GET['linenum'], $_GET['whscode'], $_GET['cardcode'], $objtype, $sentido, $_GET['usuario']);
		}		
	}else{//Consultando
		$Parametros=array(
			"'".$objtype."'",
			"'".$_GET['docentry']."'",
			"'".$_GET['linenum']."'",
			"'".$_GET['id']."'"
		);
		$SQL=EjecutarSP('sp_ConsultarLotesDocSAP',$Parametros);
	}
	
?>
<!doctype html>
<html>
<head>
<style>
	.iboxedit{
		padding: 10px !important;	
	}
	body{
		background-color: #ffffff;
	}
	.form-control{
		width: auto;
		height: 28px;
	}
	.txtYellow{
		background-color: #FAF9C3;
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
function ActualizarDatos(idlote, sysnumber, fechavenc, cantidad, idfila=''){//Actualizar datos asincronicamente
	$.ajax({
		type: "GET",
		url: "includes/procedimientos.php?type=11&edit=<?php echo $edit;?>&objtype=<?php echo $objtype;?>&linenum=<?php echo $_GET['linenum'];?>&itemcode=<?php echo $_GET['id'];?>&itemname=<?php echo $_GET['itemname'];?>&und=<?php echo $_GET['und'];?>&whscode=<?php echo $_GET['whscode'];?>&distnumber="+idlote+"&sysnumber="+sysnumber+"&fechavenc="+fechavenc+"&cant="+cantidad+"&cardcode=<?php echo $_GET['cardcode'];?>&usuario=<?php echo $_GET['usuario'];?>&sentido=<?php echo $sentido;?>",
		success: function(response){
			if(response!="Error"){
				document.getElementById('TimeAct').innerHTML="<strong>Actualizado:</strong> "+response;
				CalcularTotal(idlote,sysnumber,fechavenc,idfila);
			}
		}
	});
}
function CalcularTotal(idlote, sysnumber, fechavenc, idfila=''){
	$.ajax({
		type: "GET",
		url: "includes/procedimientos.php?type=12&edit=<?php echo $edit;?>&objtype=<?php echo $objtype;?>&linenum=<?php echo $_GET['linenum'];?>&itemcode=<?php echo $_GET['id'];?>&whscode=<?php echo $_GET['whscode'];?>&cardcode=<?php echo $_GET['cardcode'];?>&usuario=<?php echo $_GET['usuario'];?>&sentido=<?php echo $sentido;?>",
		success: function(response){
			if(response!="Error"){			
				var TotalLote=response.replace(/,/g, '');
				var CantTotalArticulo='<?php echo $_GET['cant'];?>';
				
				if(parseFloat(TotalLote)>parseFloat(CantTotalArticulo)){
					Swal.fire({
						title: '¡Advertencia!',
						text: 'La cantidad total es mayor a la cantidad a entregar.',
						icon: 'warning'
					});
					<?php if($sentido=='in'){?>
						document.getElementById("Cantidad"+idfila).value='0';
						let cantidad = document.getElementById("Cantidad"+idfila).value;
					<?php }else{?>
						document.getElementById("ItemCode"+idlote).value='0';
						let cantidad = document.getElementById("ItemCode"+idlote).value;
					<?php }?>
					
					ActualizarDatos(idlote, sysnumber, fechavenc, cantidad, idfila);
				}
				
				document.getElementById('TotalLotEnt').innerHTML=response;
			}
		}
	});
}
</script>
<?php }?>
</head>

<body>
	<div class="ibox-content iboxedit">
		<?php include("includes/spinner.php"); ?>
		<div class="row"> 
			<div class="col-lg-12">
				<form action="" method="post" class="form-horizontal" id="FrmLotes">
					<?php if($edit==1){//Creando documento ?>
					<div class="form-group">
						<label class="col-xs-12">
							<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-tasks"></i> Lotes disponibles: <?php echo base64_decode($_GET['itemname'])." (".$_GET['id'].")";?></h3>
						</label>
					</div>
					<?php if($sentido=="in"&&$basetype==""){?>
<!--
					<div class="form-group">
						<label class="col-lg-1 control-label">Lotes actuales</label>
						<div class="col-lg-4">
							<label class="checkbox-inline i-checks"><input name="chkOcultarLotes" id="chkOcultarLotes" type="checkbox" value="1"> Ocultar lotes del sistema</label>
						</div>
					</div>
-->
					<?php }?>
					<div class="form-group">
						<div class="col-xs-12">
							<div id="TimeAct"  class="pull-right"></div>
						</div>
					</div>
					<?php if($sentido=="out"){?>
					<table width="100%" class="table table-bordered tableedit">
						<thead>
							<tr>
								<th>Lote</th>
								<th>Cantidad disponible</th>
								<th>Fecha de vencimiento</th>
								<th>Número de sistema</th>
								<th>Cantidad asignada</th>
							</tr>
						</thead>
						<tbody>
						<?php 
						while($row=sqlsrv_fetch_array($SQL)){
							//Consultar si hay datos ingresados en los lotes
							$Parametros=array(
								"'".$_GET['id']."'",
								"'".$_GET['linenum']."'",
								"'".$_GET['whscode']."'",
								"'".$row['IdLote']."'",
								"'".$_GET['cardcode']."'",
								"'".$objtype."'",
								"'".$sentido."'",
								"'".$_SESSION['CodUser']."'"
							);
							$SQL_DtAct=EjecutarSP('sp_ConsultarLotesDatos',$Parametros);
							$row_DtAct=sqlsrv_fetch_array($SQL_DtAct);
						?>
						<tr>
							<td><?php echo $row['IdLote'];?></td>
							<td><?php echo number_format($row['Cantidad'],0);?></td>
							<td><?php echo $row['FechaVenciLote'];?></td>
							<td><?php echo $row['IdSysNumber'];?></td>
							<td><input type="text" id="ItemCode<?php echo $row['IdLote'];?>" name="ItemCode[]" class="form-control txtYellow" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" onChange="VerificarCant('<?php echo $row['IdLote'];?>','<?php echo $row['IdSysNumber'];?>','<?php echo number_format($row['Cantidad'],0);?>','<?php echo $row['FechaVenciLote'];?>');" value="<?php echo number_format($row_DtAct['Cantidad'],0);?>"></td>
						</tr>
						<?php }?>
						</tbody>
					</table>
					<?php }else{//sentido=in ?>
					<table width="100%" class="table table-bordered tableedit">
						<thead>
							<tr>
								<th>Lote</th>
								<th>Cantidad a ingresar</th>
								<th>Fecha de vencimiento</th>
								<th>Acciones</th>
							</tr>
						</thead>
						<tbody>
						<?php 
						$Cont=1;
						while($row=sqlsrv_fetch_array($SQL)){
						?>
						<tr id="trLote<?php echo $Cont; ?>">
							<td><input type="text" id="IdLote<?php echo $Cont; ?>" name="IdLote[]" class="form-control txtYellow" value="<?php echo isset($row['DistNumber']) ? $row['DistNumber'] : $row['IdLote'];?>" onChange="VerificarCantIngresar('<?php echo $Cont; ?>');"></td>
							<td><input type="text" id="Cantidad<?php echo $Cont; ?>" name="Cantidad[]" class="form-control txtYellow" value="<?php echo $row['Cantidad'];?>" onChange="VerificarCantIngresar('<?php echo $Cont; ?>');"></td>
							<td><input type="text" id="FechaVenciLote<?php echo $Cont; ?>" name="FechaVenciLote[]" class="form-control txtYellow" value="<?php echo $row['FechaVenc'];?>" onChange="VerificarCantIngresar('<?php echo $Cont; ?>');"></td>
							<td>&nbsp;</td>
						</tr>
						<?php 
							$Cont++;
						}?>
						<tr id="trLote<?php echo $Cont; ?>">
							<td><input type="text" id="IdLote<?php echo $Cont; ?>" name="IdLote[]" class="form-control txtYellow" value="" onChange="VerificarCantIngresar('<?php echo $Cont; ?>');"></td>
							
							<td><input type="text" id="Cantidad<?php echo $Cont; ?>" name="Cantidad[]" class="form-control txtYellow" value="" onChange="VerificarCantIngresar('<?php echo $Cont; ?>');"></td>
							
							<td><input type="text" id="FechaVenciLote<?php echo $Cont; ?>" name="FechaVenciLote[]" class="form-control txtYellow" value="" onChange="VerificarCantIngresar('<?php echo $Cont; ?>');"></td>
							<td><button type="button" id="btnAdd<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addField(this);" title="Añadir fila"><i class="fa fa-plus"></i></button></td>
						</tr>
						</tbody>
					</table>
					<?php }?>
					<div class="col-xs-11">
						<h3 class="text-success pull-right"><strong><?php echo ($sentido=="in") ? "Total a ingresar:" : "Total a entregar:";?> </strong></h3>						
					</div>
					<div class="col-xs-1">
						<h3 class="text-danger"><strong id="TotalLotEnt"><?php echo $TotalLotEnt;?></strong></h3>
					</div>
					<?php }else{//Consultando documento ?>
						<div class="form-group">
							<label class="col-xs-12">
								<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-tasks"></i> Lotes: <?php echo base64_decode($_GET['itemname'])." (".$_GET['id'].")";?></h3>
							</label>
						</div>
						<table width="100%" class="table table-bordered tableedit">
						<thead>
							<tr>
								<th>Lote</th>
								<th>Unidad</th>
								<th>Fecha de vencimiento</th>
								<th>Número de sistema</th>
								<th>Cantidad entregada</th>
							</tr>
						</thead>
						<tbody>
						<?php 
						while($row=sqlsrv_fetch_array($SQL)){
						?>
						<tr>
							<td><?php echo $row['IdLote'];?></td>
							<td><?php echo $row['UndMedida'];?></td>
							<td><?php echo $row['FechaVenc'];?></td>
							<td><?php echo $row['IdSysNumber'];?></td>
							<td><?php echo number_format($row['Cantidad']);?></td>
						</tr>
						<?php }?>
						</tbody>
					</table>
					<?php }?>
					<input type="hidden" name="cantItem" id="cantItem" value="<?php echo ($edit==1) ? $_GET['cant'] : ""; ?>" />
				</form>
			</div>
		</div>
	</div>
<?php if($edit==1){?>
<script>
function VerificarCant(id, sysnumber, cant_actual, fechavenc){
	var CantIngresada=document.getElementById('ItemCode'+id);	
	if(parseFloat(CantIngresada.value)>0){
		var CantLote=cant_actual.replace(/,/g, '');
		if(parseFloat(CantIngresada.value) > parseFloat(CantLote)){
			CantIngresada.value='0';
			Swal.fire({
				title: '¡Advertencia!',
				text: 'No puede ingresar una cantidad mayor a la cantidad del lote.',
				icon: 'warning'
			});
		}
	}else if(parseFloat(CantIngresada.value)<0){
		CantIngresada.value='0';
		Swal.fire({
			title: '¡Advertencia!',
			text: 'No puede ingresar una cantidad negativa.',
			icon: 'warning'
		});
	}
		ActualizarDatos(id, sysnumber, fechavenc, CantIngresada.value);
}
	
function VerificarCantIngresar(id){
	let idLote=document.getElementById("IdLote"+id);
	let cantIngresada=document.getElementById('Cantidad'+id);
	let fechaVenc=document.getElementById('FechaVenciLote'+id);
	let cantItem=document.getElementById('cantItem').value;
	
	if(idLote.value!=""&&cantIngresada.value!=""&&fechaVenc.value!=""){
		if(parseFloat(cantIngresada.value)>0){
			var cantArticulo=cantItem.replace(/,/g, '');

			if(parseFloat(cantIngresada.value) > parseFloat(cantArticulo)){
				cantIngresada.value='0';
				Swal.fire({
					title: '¡Advertencia!',
					text: 'No puede ingresar una cantidad mayor a la cantidad del artículo.',
					icon: 'warning'
				});			
			}
		}else if(parseFloat(cantIngresada.value)<0){
			cantIngresada.value='0';
			Swal.fire({
				title: '¡Advertencia!',
				text: 'No puede ingresar una cantidad negativa.',
				icon: 'warning'
			});
		}
		ActualizarDatos(idLote.value, '', fechaVenc.value, cantIngresada.value, id);
	}	
}
</script>
<?php }?>
<script>
	 $(document).ready(function(){
		  $('.i-checks').iCheck({
			 checkboxClass: 'icheckbox_square-green',
             radioClass: 'iradio_square-green',
          });
		 /*$("#FrmLotes").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});*/
		 
		<?php if($edit==1&&$sentido=="in"){?> 
		 
		 let fechaVencimiento = document.getElementsByName("FechaVenciLote[]");
		 
		 fechaVencimiento.forEach(function(currentValue){
			vanillaTextMask.maskInput({
				inputElement: currentValue,
				mask: [/\d/,/\d/,/\d/,/\d/,'-',/\d/,/\d/,'-',/\d/,/\d/],
				guide: false
			})
		 })
		 
		 <?php }?>
	 });
	
function addField(btn){//Clonar div
	var clickID = parseInt($(btn).parent('td').parent('tr').attr('id').replace('trLote',''));
	//alert($(btn).parent('div').attr('id'));
	//alert(clickID);
	var newID = (clickID+1);
	
	//var $example = $(".select2").select2();
	//$example.select2("destroy");

	$newClone = $('#trLote'+clickID).clone(true);

	//div
	$newClone.attr("id",'trLote'+newID);
	
	

	//inputs
	$newClone.children("td").eq(0).children("input").eq(0).attr('id','IdLote'+newID);
	$newClone.children("td").eq(0).children("input").eq(0).attr('onChange',"VerificarCantIngresar('"+newID+"');");
	$newClone.children("td").eq(1).children("input").eq(0).attr('id','Cantidad'+newID);
	$newClone.children("td").eq(1).children("input").eq(0).attr('onChange',"VerificarCantIngresar('"+newID+"');");
	$newClone.children("td").eq(2).children("input").eq(0).attr('id','FechaVenciLote'+newID);
	$newClone.children("td").eq(2).children("input").eq(0).attr('onChange',"VerificarCantIngresar('"+newID+"');");
	
	//button
	$newClone.children("td").eq(3).children("button").eq(0).attr('id','btnAdd'+newID);

	$newClone.insertAfter($('#trLote'+clickID));

	document.getElementById('btnAdd'+clickID).innerHTML="<i class='fa fa-minus'></i>";
	document.getElementById('btnAdd'+clickID).setAttribute('class','btn btn-warning btn-xs');
	document.getElementById('btnAdd'+clickID).setAttribute('onClick','delRow2(this);');
	
	//Limpiar campos
	document.getElementById('IdLote'+newID).value='';
	document.getElementById('Cantidad'+newID).value='';
	document.getElementById('FechaVenciLote'+newID).value='';
	
	vanillaTextMask.maskInput({
		inputElement: document.getElementById('FechaVenciLote'+newID),
		mask: [/\d/,/\d/,/\d/,/\d/,'-',/\d/,/\d/,'-',/\d/,/\d/],
		guide: false
	})
	
}
	
function delRow2(btn){//Eliminar div
	$(btn).parent('td').parent('tr').remove();
}
</script>
</body>
</html>
<?php 
	sqlsrv_close($conexion);
}?>