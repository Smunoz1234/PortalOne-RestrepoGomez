<?php
require_once( "includes/conexion.php" );
PermitirAcceso(312);

$Cant=0;

$IdEvento= isset($_POST['idEvento']) ? base64_decode($_POST['idEvento']) : 0;

$Sede = isset($_POST['Sede']) ? $_POST['Sede'] : "";
$LlamadaServicio = isset($_POST['LlamadaServicio']) ? $_POST['LlamadaServicio'] : "";
$Recurso = isset($_POST['Recursos']) ? $_POST['Recursos'] : "";
$Cliente = isset($_POST['Cliente']) ? $_POST['Cliente'] : "";
$NomSucursal = isset($_POST['SucursalCliente']) ? base64_decode($_POST['SucursalCliente']) : "";
$FechaInicial = isset($_POST['FechaInicio']) ? $_POST['FechaInicio'] : "";
$FechaFinal = isset($_POST['FechaFinal']) ? $_POST['FechaFinal'] : "";
$Type = isset($_POST['type']) ? $_POST['type'] : "";

//Consultamos la lista de OT pendientes
$Param=array(
	$Type,
	"'".$_SESSION['CodUser']."'",
	"'".$IdEvento."'",
	"'".$Sede."'",
	"'".$Cliente."'",
	"'".$NomSucursal."'",
	"'".FormatoFecha($FechaInicial)."'",
	"'".FormatoFecha($FechaFinal)."'",
	"'".$Recurso."'",
	"'".$LlamadaServicio."'",
);

$SQL=EjecutarSP("sp_ConsultarDatosCalendarioMoverActLote",$Param);
$Cant=sqlsrv_num_rows($SQL);

//Lista de recursos (Tecnicos)
$ParamRec=array(
	"'".$_SESSION['CodUser']."'",
	"'".$Sede."'"	
);

$SQL_Recursos=EjecutarSP("sp_ConsultarTecnicos",$ParamRec);

?>
<script>
var json=[];
var cant=0;

function BorrarLinea(){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea eliminar este item? Este proceso no se puede revertir.')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=50&linenum="+json,		
			success: function(response){
				FiltrarDatos(3)
			}
		});
	}	
}

function DuplicarLinea(){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea duplicar estos registros?')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=42&linenum="+json,
			success: function(response){
				FiltrarDatos(5)
			}
		});
	}	
}

function ValidarDatosDetalle(name,id,line){//Actualizar datos asincronicamente
	var campo=document.getElementById(name+id);
	if(name=="FechaInicioActividad" || name=="FechaFinActividad"){
		if((campo.value=="")||(campo.value.length<10)||(!esFecha(campo.value))){
			 Swal.fire({
				title: '¡Advertencia!',
				text: 'Fecha invalida. Por favor verifique.',
				icon: 'warning',
			});
			campo.value='<?php echo date('Y-m-d');?>';
		}
		ActualizarDatos(name,id,line)	
	}else{//HoraActividad
		if((campo.value=="")||(campo.value.length<5)||(!esHora(campo.value))){
			 Swal.fire({
				title: '¡Advertencia!',
				text: 'Hora invalida. Por favor verifique.',
				icon: 'warning',
			});
			campo.value='';
		}
		ActualizarDatos(name,id,line)
	}
	
}

function ActualizarDatos(name,id,line){//Actualizar datos asincronicamente
	$.ajax({
		type: "GET",
		url: "registro.php",
		data: {
			P:'36',
			doctype:'20',
			name:name,
			value:btoa(document.getElementById(name+id).value),
			line:line,
			actodos:'0',
			tipoactlote:'',
			fechainicio:'',
			horainicio:'',
			fechafin:'',
			horafin:'',
			empleado:''
		},
		success: function(response){
			if(response!="Error"){
				mostrarNotify('Actualizado: '+response)
			}
		}
	});
}
	
function ActualizarDatosLote(name){//Actualizar datos asincronicamente
	Swal.fire({
		title: "¿Está seguro que desea actualizar todos los registros?",
		icon: "warning",
		showCancelButton: true,
		confirmButtonText: "Si, confirmo",
		cancelButtonText: "No"
	}).then((result) => {
		if (result.isConfirmed) {
			$.ajax({
				type: "GET",
				url: "registro.php",
				data: {
					P:'36',
					doctype:'20',
					name:'',
					value:'',
					line:'',
					actodos:'1',
					tipoactlote:name,
					fechainicio:document.getElementById('FechaInicialAsig').value,
					horainicio:document.getElementById('HoraInicioAsig').value,
					fechafin:document.getElementById('FechaFinAsig').value,
					horafin:document.getElementById('HoraFinAsig').value,
					empleado:document.getElementById('TecnicoAsig').value
				},
				success: function(response){
					if(response!="Error"){
						FiltrarDatos(3)
					}else{
						mostrarNotify(response)
					}
				}
			});
		}
	});
}

function Seleccionar(ID){
	var btnBorrarLineas=document.getElementById('btnBorrarLineas');
	var btnDuplicarLineas=document.getElementById('btnDuplicarLineas');
	var Check = document.getElementById('chkSel'+ID).checked;
	var sw=-1;
	json.forEach(function(element,index){
//		console.log(element,index);
//		console.log(json[index])deta
		if(json[index]==ID){
			sw=index;
		}
		
	});
	
	if(sw>=0){
		json.splice(sw, 1);
		cant--;
	}else if(Check){
		json.push(ID);
		cant++;
	}
	if(cant>0){
		$("#btnBorrarLineas").prop('disabled', false);
		$("#btnDuplicarLineas").prop('disabled', false);
	}else{
		$("#btnBorrarLineas").prop('disabled', true);
		$("#btnDuplicarLineas").prop('disabled', true);
	}
	
	//console.log(json);
}

function SeleccionarTodos(){
	var Check = document.getElementById('chkAll').checked;
	if(Check==false){
		json=[];
		cant=0;
		$("#btnBorrarLineas").prop('disabled', true);
		$("#btnDuplicarLineas").prop('disabled', true);
	}
	$(".chkSel").prop("checked", Check);
	if(Check){
		$(".chkSel").trigger('change');
	}		
}
</script>
<div class="card">
  <h6 class="card-header bg-primary text-white">
	Resultados: <?php echo $Cant;?>
  </h6>
	<div id="accordionActLote" class="card col-lg-12 p-md-4">
		<div class="pt-2 pr-2 pl-2 pb-0 mb-2 bg-success text-white">
			<a class="d-flex justify-content-between text-white" data-toggle="collapse" aria-expanded="true" href="#accordionActLote-1">
				<h5 class="pr-2"><i class="fas fa-info-circle"></i> Campos para actualizar en lote</h5>
				<div class="collapse-icon"></div>
			</a>
		</div>
		<div id="accordionActLote-1" class="collapse" data-parent="#accordionActLote">
			<div class="form-group row">
				<div class="col-lg-5 border-bottom ml-4">
					<label class="col-form-label text-danger"><i class="fas fa-calendar-alt"></i> Actualizar fechas</label>
				</div>
			</div>
			<div class="form-row mb-2">
				<label class="col-lg-1 col-form-label">Fecha inicio</label>
				<div class="col-lg-2 input-group">
					<input name="FechaInicialAsig" type="text" class="form-control" id="FechaInicialAsig" value="<?php echo date('Y-m-d');?>" placeholder="YYYY-MM-DD">
				</div>
				<div class="col-lg-2 input-group">
					<input name="HoraInicioAsig" id="HoraInicioAsig" type="text" class="form-control" value="<?php echo date('H').":00";?>" onChange="ValidarHoras();">
				</div>
				<label class="col-lg-1 col-form-label">Fecha fin</label>
				<div class="col-lg-2 input-group">
					<input name="FechaFinAsig" type="text" class="form-control" id="FechaFinAsig" value="<?php echo date('Y-m-d');?>" placeholder="YYYY-MM-DD">
				</div>
				<div class="col-lg-2 input-group">
					<input name="HoraFinAsig" id="HoraFinAsig" type="text" class="form-control" value="<?php echo (date('H')+2).":00";?>" onChange="ValidarHoras();">
				</div>
				<div class="col-lg-2">
					<button id="btnActFechaLote" type="button" class="btn btn-primary btn-sm" onClick="ActualizarDatosLote('Fechas');"><i class="fas fa-list"></i> Actualizar fechas</button>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-lg-5 border-bottom ml-4">
					<label class="col-form-label text-danger"><i class="fas fa-user-alt"></i> Actualizar técnico</label>
				</div>
			</div>
			<div class="form-row">
				<label class="col-lg-1 col-form-label">Técnico</label>
				<div class="col-lg-4">
					 <div class="select2-success">
					  <select name="TecnicoAsig" id="TecnicoAsig" class="select2OTLote form-control" style="width: 100%">
						   <option value="">Seleccione...</option>
						   <?php
						   while($row_Recursos=sqlsrv_fetch_array($SQL_Recursos)){?>
								<option value="<?php echo $row_Recursos['ID_Empleado'];?>"><?php echo $row_Recursos['NombreEmpleado'];?></option>
						  <?php }?>
					  </select>
					</div>
				</div>
				<div class="col-lg-2">
					<button id="btnActTecnicoLote" type="button" class="btn btn-primary btn-sm" onClick="ActualizarDatosLote('Empleado');"><i class="fas fa-list"></i> Actualizar técnico</button>
				</div>
			</div>
		</div>
	</div>  
  <div class="card-datatable table-responsive">
	<table class="datatables-demo table table-striped table-bordered small">
	  <thead>
		<tr>
			<th class="text-center">
				<div class="d-inline-flex">
					<label class="custom-control custom-checkbox checkbox-lg">
					  <input type="checkbox" class="custom-control-input" id="chkAll" onChange="SeleccionarTodos();" title="Seleccionar todos">
					  <span class="custom-control-label"></span>
					</label>
					<button type="button" id="btnBorrarLineas" title="Borrar lineas" class="btn btn-danger btn-xs" disabled onClick="BorrarLinea();"><i class="fas fa-trash"></i></button>
					<button type="button" id="btnDuplicarLineas" title="Duplicar lineas" class="btn btn-primary btn-xs ml-1" disabled onClick="DuplicarLinea();" style="display: none;"><i class="far fa-clone"></i></button>
				</div>				
			</th>
			<th>#</th>
			<th>Llamada de servicio</th>
			<th>Actividad</th>
			<th>Cliente</th>
			<th>Sucursal</th>
			<th>Fecha inicio <span class="text-danger">*</span></th>
			<th>Hora inicio <span class="text-danger">*</span></th>
			<th>Fecha fin <span class="text-danger">*</span></th>
			<th>Hora fin <span class="text-danger">*</span></th>
			<th>Técnico asignado <span class="text-danger">*</span></th>
		</tr>
	  </thead>
	  <tbody>
	   <?php
		  $i=1;
		  while($row=sqlsrv_fetch_array($SQL)){
			  
			$ParamRec=array(
				"'".$_SESSION['CodUser']."'",
				"'".$Sede."'"	
			);

			$SQL_Recursos=EjecutarSP("sp_ConsultarTecnicos",$ParamRec,-1);
				
			?>
			 <tr class="gradeX odd" id="tr_<?php echo $row['ID'];?>">
				<td class="text-center">
					<label class="custom-control custom-checkbox checkbox-lg">
					  <input type="checkbox" class="custom-control-input chkSel" id="chkSel<?php echo $row['ID'];?>" value="" onChange="Seleccionar('<?php echo $row['ID'];?>');">
					  <span class="custom-control-label"></span>
					</label>
				</td>
				<td><?php echo $i;?></td>
				<td><?php echo $row['ID_LlamadaServicio'];?></td>
				<td><?php echo $row['ID_Actividad'];?></td>
				<td><?php echo $row['NombreClienteLlamada'];?></td>						
				<td><?php echo $row['NombreSucursal'];?></td>
				 
				<td><input type="text" id="FechaInicioActividad<?php echo $i;?>" name="FechaInicioActividad" class="form-control" value="<?php if($row['FechaInicioActividad']!=""){echo $row['FechaInicioActividad']->format('Y-m-d');}?>" placeholder="YYYY-MM-DD" onChange="ValidarDatosDetalle('FechaInicioActividad',<?php echo $i;?>,<?php echo $row['ID'];?>);" autocomplete="off"></td>
				
				<td><input name="HoraInicioActividad" type="text" class="form-control" id="HoraInicioActividad<?php echo $i;?>" placeholder="HH:MM" onChange="ValidarDatosDetalle('HoraInicioActividad',<?php echo $i;?>,<?php echo $row['ID'];?>);" value="<?php if($row['HoraInicioActividad']!=""){echo $row['HoraInicioActividad']->format('H:i');}?>" autocomplete="off"></td>
				 
				<td><input type="text" id="FechaFinActividad<?php echo $i;?>" name="FechaFinActividad" class="form-control" value="<?php if($row['FechaFinActividad']!=""){echo $row['FechaFinActividad']->format('Y-m-d');}?>" placeholder="YYYY-MM-DD" onChange="ValidarDatosDetalle('FechaFinActividad',<?php echo $i;?>,<?php echo $row['ID'];?>);" autocomplete="off"></td>
				
				<td><input name="HoraFinActividad" type="text" class="form-control" id="HoraFinActividad<?php echo $i;?>" placeholder="HH:MM" onChange="ValidarDatosDetalle('HoraFinActividad',<?php echo $i;?>,<?php echo $row['ID'];?>);" value="<?php if($row['HoraFinActividad']!=""){echo $row['HoraFinActividad']->format('H:i');}?>" autocomplete="off"></td>
				 
			   	<td>
				 <select name="ID_EmpleadoActividad" id="ID_EmpleadoActividad<?php echo $i;?>" class="select2 form-control" style="width: 100%" onChange="ActualizarDatos('ID_EmpleadoActividad',<?php echo $i;?>,<?php echo $row['ID'];?>);">
				   <?php
					   while($row_Recursos=sqlsrv_fetch_array($SQL_Recursos)){?>
							<option value="<?php echo $row_Recursos['ID_Empleado'];?>" <?php if((isset($row['ID_EmpleadoActividad'])&&($row['ID_EmpleadoActividad']!=""))&&(strcmp($row_Recursos['ID_Empleado'],$row['ID_EmpleadoActividad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Recursos['NombreEmpleado'];?></option>
					  <?php }?>
				    </select>
			   </td>
			</tr>
		<?php $i++;}?>
	  </tbody>
	</table>
  </div>
</div>

<script>
$(document).ready(function() {
	
	var horaInicioActividad = document.getElementsByName("HoraInicioActividad");
	var horaFinActividad = document.getElementsByName("HoraFinActividad");
	var fechaInicioActividad = document.getElementsByName("FechaInicioActividad");
	var fechaFinActividad = document.getElementsByName("FechaFinActividad");
	
	fechaInicioActividad.forEach(function(currentValue){
		vanillaTextMask.maskInput({
			inputElement: currentValue,
			mask: [/\d/,/\d/,/\d/,/\d/,'-',/\d/,/\d/,'-',/\d/,/\d/],
			guide: false
		})
	})
	
	fechaFinActividad.forEach(function(currentValue){
		vanillaTextMask.maskInput({
			inputElement: currentValue,
			mask: [/\d/,/\d/,/\d/,/\d/,'-',/\d/,/\d/,'-',/\d/,/\d/],
			guide: false
		})
	})
	
	horaInicioActividad.forEach(function(currentValue){
		vanillaTextMask.maskInput({
			inputElement: currentValue,
			mask: [/\d/,/\d/,':',/\d/,/\d/],
			guide: false
		})
	})
	
	horaFinActividad.forEach(function(currentValue){
		vanillaTextMask.maskInput({
			inputElement: currentValue,
			mask: [/\d/,/\d/,':',/\d/,/\d/],
			guide: false
		})
	})
	
	$('#FechaInicialAsig').flatpickr({
		 dateFormat: "Y-m-d",
		 static : true,
		 allowInput: true
	 });
	
	$('#FechaFinAsig').flatpickr({
		 dateFormat: "Y-m-d",
		 static : true,
		 allowInput: true
	 });
	
	$('#HoraInicioAsig').flatpickr({
		 enableTime: true,
		 noCalendar: true,
		 dateFormat: "H:i",
		 time_24hr: true,
		 static : true,
		 allowInput: true
	 });
	
	$('#HoraFinAsig').flatpickr({
		 enableTime: true,
		 noCalendar: true,
		 dateFormat: "H:i",
		 time_24hr: true,
		 static : true,
		 allowInput: true
	 });
	
	$(".select2OTLote").select2({
        dropdownParent: $('#ModalAct')
    });
	
	$('.datatables-demo').DataTable({
		pageLength: 10,
		info: false,
		language: {
			"decimal":        "",
			"emptyTable":     "No se encontraron resultados.",
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
		}

	});

});
	
function ValidarHoras(){
	var HInicio = document.getElementById("HoraInicioAsig").value;
	var HFin = document.getElementById("HoraFinAsig").value;
	
	if(!validarRangoHoras(HInicio,HFin)){
		 Swal.fire({
			title: '¡Advertencia!',
			text: 'Tiempo no válido. Ingrese una duración positiva.',
			icon: 'warning',
		});
		return false;
	}
}

</script>