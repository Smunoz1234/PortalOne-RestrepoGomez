<?php  
require_once("includes/conexion.php");

$edit = isset($_POST['edit']) ? $_POST['edit'] : 0;
$id = isset($_POST['id']) ? $_POST['id'] : "";
$estado = isset($_POST['estado']) ? $_POST['estado'] : "O";
$linea = isset($_POST['linea']) ? $_POST['linea'] : random_int(100, 999);

$Title="Añadir etapa";
$Type=1;

//Tipos de documentos
$SQL_TipoDoc=Seleccionar("uvw_tbl_ObjetosSAP","*",'','CategoriaObjeto, DeTipoDocumento');

//Empleados de ventas
$SQL_EmpVentas=Seleccionar('uvw_Sap_tbl_EmpleadosVentas','*');

//Etapas
$SQL_Etapas=Seleccionar('uvw_Sap_tbl_OportunidadesEtapas','*');

//Empleados
$SQL_Empleados=Seleccionar('uvw_Sap_tbl_Empleados','ID_Empleado, NombreEmpleado','','NombreEmpleado');

if($edit==1){
	$SQL_Data=Seleccionar("uvw_Sap_tbl_OportunidadesDetalle","*","ID_Oportunidad='".$id."' and IdLinea='".$linea."'");
	$row_Data=sqlsrv_fetch_array($SQL_Data);
	$Title="Editar etapa";
	$Type=2;
}

?>
<form id="frm_NewParam" method="post" action="">
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
					<label class="control-label">Fecha de inicio <span class="text-danger">*</span></label>
					<div class="input-group date">
						 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaInicio" type="text" class="form-control" id="FechaInicio" value="" readonly="readonly" required>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label">Fecha de cierre</label>
					<div class="input-group date">
						 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaCierre" type="text" class="form-control" id="FechaCierre" value="" readonly="readonly">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label">Empleado de ventas <span class="text-danger">*</span></label>
					<select name="EmpVentas" class="form-control" id="EmpVentas" required <?php if($estado=="C"){echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
					  <?php while($row_EmpVentas=sqlsrv_fetch_array($SQL_EmpVentas)){?>
							<option value="<?php echo $row_EmpVentas['ID_EmpVentas'];?>"><?php echo $row_EmpVentas['DE_EmpVentas'];?></option>
					  <?php }?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Etapa <span class="text-danger">*</span></label>
					<select name="Etapa" class="form-control" id="Etapa" required <?php if($estado=="C"){echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
					  <?php while($row_Etapas=sqlsrv_fetch_array($SQL_Etapas)){?>
							<option value="<?php echo $row_Etapas['ID_Etapa'];?>"><?php echo $row_Etapas['DE_Etapa'];?></option>
					  <?php }?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">% Etapa</label>
					<input type="text" class="form-control" name="PrctEtapa" id="PrctEtapa" autocomplete="off" value="" readonly>
				</div>
				<div class="form-group">
					<label class="control-label">Monto potencial <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="MontoPotencial" id="MontoPotencial" required autocomplete="off" value="" <?php if($estado=="C"){echo "readonly";}?>>
				</div>
				<div class="form-group">
					<label class="control-label">Importe ponderado <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="ImportePonderado" id="ImportePonderado" required autocomplete="off" value="" <?php if($estado=="C"){echo "readonly";}?>>
				</div>
				<div class="form-group">
					<label class="control-label">Comentarios</label>
					<textarea name="Comentarios" rows="2" maxlength="3000" class="form-control" id="Comentarios" type="text" <?php if($estado=="C"){echo "readonly";}?>></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Tipo de documento relacionado</label>
					<select name="TipoDoc" class="form-control" id="TipoDoc" <?php if($estado=="C"){echo "disabled='disabled'";}?>>
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
				<div class="form-group">
					<label class="control-label">Documento relacionado</label>
					<input type="text" class="form-control" name="DocRelacionado" id="DocRelacionado" autocomplete="off" value="" <?php if($estado=="C"){echo "readonly";}?>>
				</div>
				<div class="form-group">
					<label class="control-label">Propietario <span class="text-danger">*</span></label>
					<select name="Propietario" class="form-control" id="Propietario" <?php if($estado=="C"){echo "disabled='disabled'";}?>>
							<option value="">Seleccione...</option>
					  <?php while($row_Empleados=sqlsrv_fetch_array($SQL_Empleados)){?>
							<option value="<?php echo $row_Empleados['ID_Empleado'];?>"><?php echo $row_Empleados['NombreEmpleado'];?></option>
					  <?php }?>
					</select>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<?php if($estado=="O"){?><button type="button" class="btn btn-success m-t-md" onClick="ValidarDatos();"><i class="fa fa-check"></i> Aceptar</button><?php }?>
		<button type="button" class="btn btn-danger m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
	</div>
	<input type="hidden" id="MM_Insert" name="MM_Insert" value="1" />
	<input type="hidden" id="id" name="id" value="<?php echo $id;?>" />
	<input type="hidden" id="type" name="type" value="<?php echo $Type;?>" />
	<input type="hidden" id="linea" name="linea" value="<?php echo $linea;?>" />
</form>
<script>
 $(document).ready(function(){
	 CargarDatos();
	 
	 <?php if($estado=="O"){?>
	 $('#FechaInicio').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			todayHighlight: true,
			format: 'yyyy-mm-dd'
		});	
	 
	  $('#FechaCierre').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			todayHighlight: true,
			format: 'yyyy-mm-dd'
		});	
 	<?php }?>
	 
	$("#Etapa").change(function(){
		var Etapa=document.getElementById('Etapa').value;
		$.ajax({
			url:"ajx_buscar_datos_json.php",
			data:{type:41,id:Etapa},
			dataType:'json',
			success: function(data){
				document.getElementById('PrctEtapa').value=data.PorcentajeEtapa;
			}
		});
	});
 });
	
function CargarDatos(){
	let datosCliente = window.sessionStorage.getItem('OPR<?php echo $id;?>')
	let LineNum = document.getElementById('linea').value;
	let json=[]
	let sw=-1;
	
	if(datosCliente){
		json = JSON.parse(datosCliente)
	}else{
		window.sessionStorage.setItem('OPR<?php echo $id;?>','')
	}
	
	//Buscar si existe la direccion en la cadena JSON
	json.forEach(function(element,index){
		if(json[index].id_linea==LineNum){
			//Si la encontre, marco el sw con el indice del arreglo
			sw=index;
		}
	});
	
	if(sw>=0){
		document.getElementById('FechaInicio').value = json[sw].fecha_inicio;
		document.getElementById('FechaCierre').value = json[sw].fecha_cierre;
		document.getElementById('EmpVentas').value = json[sw].id_empleado;
		document.getElementById('Etapa').value = json[sw].id_etapa;
		document.getElementById('PrctEtapa').value = json[sw].prct_etapa;
		document.getElementById('MontoPotencial').value = json[sw].monto_potencial;
		document.getElementById('ImportePonderado').value = json[sw].importe_ponderado;
		document.getElementById('Comentarios').value = json[sw].comentarios;
		document.getElementById('TipoDoc').value = json[sw].tipo_documento;
		document.getElementById('DocRelacionado').value = json[sw].docnum_documento;
		document.getElementById('Propietario').value = json[sw].id_propietario;
	}
}
	
function ValidarDatos(){
	Swal.fire({
		title: "¿Está seguro que desea guardar los datos?",
		icon: "question",
		showCancelButton: true,
		confirmButtonText: "Si, confirmo",
		cancelButtonText: "No"
	}).then((result) => {
		if (result.isConfirmed) {
			GuardarDatos()
		}
	});
}
	
function GuardarDatos(){
	
	let datosCliente = window.sessionStorage.getItem('OPR<?php echo $id;?>')
	let LineNum = document.getElementById('linea').value;
	let json=[]
	let metodo=2;
	let sw=-1;
	
	if(datosCliente){
		json = JSON.parse(datosCliente)
	}else{
		window.sessionStorage.setItem('OPR<?php echo $id;?>','')
	}
	
	//Buscar si existe la direccion en la cadena JSON
	json.forEach(function(element,index){
		if(json[index].id_linea==LineNum){
			//Si la encontre, marco el sw con el indice del arreglo
			sw=index;
			metodo=json[sw].metodo;
			metodo= (metodo==1) ? 1: 2;
		}
	});
	
	if(sw>=0){
//		json.splice(sw, 1);	
		json[sw].id_oportunidad= '<?php echo $id;?>';
		json[sw].id_linea= LineNum;
		json[sw].fecha_inicio= document.getElementById('FechaInicio').value;
		json[sw].fecha_cierre= document.getElementById('FechaCierre').value;
		json[sw].id_etapa= document.getElementById('Etapa').value;
		json[sw].nombre_etapa= document.getElementById('Etapa').options[document.getElementById('Etapa').selectedIndex].text;
		json[sw].prct_etapa= number_format(document.getElementById('PrctEtapa').value,2);
		json[sw].monto_potencial= number_format(document.getElementById('MontoPotencial').value,2);
		json[sw].importe_ponderado= number_format(document.getElementById('ImportePonderado').value,2);
		json[sw].id_empleado= document.getElementById('EmpVentas').value;
		json[sw].nombre_empleado= document.getElementById('EmpVentas').options[document.getElementById('EmpVentas').selectedIndex].text;
		json[sw].comentarios= document.getElementById('Comentarios').value;
		json[sw].actividad= 'N';
		json[sw].tipo_documento= document.getElementById('TipoDoc').value;
		json[sw].nombre_documento= (document.getElementById('TipoDoc').value!="") ? document.getElementById('TipoDoc').options[document.getElementById('TipoDoc').selectedIndex].text : "";
		json[sw].docentry_documento= '';
		json[sw].docnum_documento= document.getElementById('DocRelacionado').value;
		json[sw].id_propietario= document.getElementById('Propietario').value;
		json[sw].nombre_propietario= document.getElementById('Propietario').options[document.getElementById('Propietario').selectedIndex].text;
		json[sw].id_estado= 'O';
		json[sw].nombre_estado= 'Abierto';
		json[sw].metodo= metodo;
		
		document.getElementById('FIni'+LineNum).innerHTML = json[sw].fecha_inicio
		document.getElementById('FCie'+LineNum).innerHTML = json[sw].fecha_cierre
		document.getElementById('NomEmp'+LineNum).innerHTML = json[sw].nombre_empleado
		document.getElementById('Eta'+LineNum).innerHTML = json[sw].nombre_etapa
		document.getElementById('PrEtap'+LineNum).innerHTML = json[sw].prct_etapa
		document.getElementById('MPot'+LineNum).innerHTML = json[sw].monto_potencial
		document.getElementById('IPon'+LineNum).innerHTML = json[sw].importe_ponderado
		document.getElementById('Com'+LineNum).innerHTML = json[sw].comentarios
		document.getElementById('NomObj'+LineNum).innerHTML = json[sw].nombre_documento
		document.getElementById('DocNum'+LineNum).innerHTML = json[sw].docnum_documento
		document.getElementById('NomProp'+LineNum).innerHTML = json[sw].nombre_propietario
		
	}else{
		json.push({
			id_oportunidad: '<?php echo $id;?>',
			id_linea: LineNum,
			fecha_inicio: document.getElementById('FechaInicio').value,
			fecha_cierre: document.getElementById('FechaCierre').value,
			id_etapa: document.getElementById('Etapa').value,
			nombre_etapa: document.getElementById('Etapa').options[document.getElementById('Etapa').selectedIndex].text,
			prct_etapa: number_format(document.getElementById('PrctEtapa').value,2),
			monto_potencial: number_format(document.getElementById('MontoPotencial').value,2),
			importe_ponderado: number_format(document.getElementById('ImportePonderado').value,2),
			id_empleado: document.getElementById('EmpVentas').value,
			nombre_empleado: document.getElementById('EmpVentas').options[document.getElementById('EmpVentas').selectedIndex].text,
			comentarios: document.getElementById('Comentarios').value,
			actividad: 'N',
			tipo_documento: document.getElementById('TipoDoc').value,
			nombre_documento: (document.getElementById('TipoDoc').value!="") ? document.getElementById('TipoDoc').options[document.getElementById('TipoDoc').selectedIndex].text : "",
			docentry_documento: '',
			docnum_documento: document.getElementById('DocRelacionado').value,
			id_propietario: document.getElementById('Propietario').value,
			nombre_propietario: document.getElementById('Propietario').options[document.getElementById('Propietario').selectedIndex].text,
			id_estado: 'O',
			nombre_estado: 'Abierto',
			metodo: 1
		})
		
		let html = document.implementation.createHTMLDocument()
		let tr = html.createElement("tr");
		let count = document.getElementById("listaEtapas").childElementCount;

		json.forEach(function(element,index){
			if(json[index].id_linea==LineNum){
				//Si la encontre, marco el sw con el indice del arreglo
				sw=index;
			}
		});

		tr.innerHTML=`
			<td id="idLin${json[sw].id_linea}">${count+1}</td>
			<td id="FIni${json[sw].id_linea}">${json[sw].fecha_inicio}</td>
			<td id="FCie${json[sw].id_linea}">${json[sw].fecha_cierre}</td>
			<td id="NomEmp${json[sw].id_linea}">${json[sw].nombre_empleado}</td>
			<td id="Eta${json[sw].id_linea}">${json[sw].nombre_etapa}</td>
			<td id="PrEtap${json[sw].id_linea}">${json[sw].prct_etapa}</td>
			<td id="MPot${json[sw].id_linea}">${json[sw].monto_potencial}</td>
			<td id="IPon${json[sw].id_linea}">${json[sw].importe_ponderado}</td>
			<td id="Com${json[sw].id_linea}">${json[sw].comentarios}</td>
			<td id="NomObj${json[sw].id_linea}">${json[sw].nombre_documento}</td>
			<td id="DocNum${json[sw].id_linea}">${json[sw].docnum_documento}</td>
			<td id="NomProp${json[sw].id_linea}">${json[sw].nombre_propietario}</td>
			<td id="Est${json[sw].id_linea}"><span class='label label-warning'>${json[sw].nombre_estado}</span></td>
			<td id="Acc${json[sw].id_linea}">
				<button type="button" id="btnEdit${json[sw].id_linea}" class="btn btn-success btn-xs" onClick="EditarEtapa('${json[sw].id_oportunidad}','${json[sw].id_linea}');"><i class="fa fa-pencil"></i> Editar</button>
				<button type="button" id="btnDel${json[sw].id_linea}" class="btn btn-danger btn-xs" onClick="BorrarLinea('${json[sw].id_oportunidad}');"><i class="fa fa-trash"></i> Eliminar</button>
			</td>`;
		let tbody=document.getElementById("listaEtapas")
		tbody.appendChild(tr);
	}
	
	window.sessionStorage.setItem('OPR<?php echo $id;?>',JSON.stringify(json))
		
	$('#myModal').modal("hide");
}
</script>