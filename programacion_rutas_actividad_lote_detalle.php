<?php
require_once( "includes/conexion.php" );
PermitirAcceso(312);

$Cant=0;

$IdEvento= isset($_POST['idEvento']) ? base64_decode($_POST['idEvento']) : 0;

$Sede = isset($_POST['Sede']) ? $_POST['Sede'] : "";
$Hora = isset($_POST['HoraInicio']) ? $_POST['HoraInicio'] : "";
$Recurso = isset($_POST['Recursos']) ? $_POST['Recursos'] : "";
$Cliente = isset($_POST['Cliente']) ? $_POST['Cliente'] : "";
$NomSucursal = isset($_POST['SucursalCliente']) ? base64_decode($_POST['SucursalCliente']) : "";
$FechaInicial = isset($_POST['FechaInicio']) ? $_POST['FechaInicio'] : "";
$FechaFinal = isset($_POST['FechaFinal']) ? $_POST['FechaFinal'] : "";
$Type = isset($_POST['type']) ? $_POST['type'] : "";

//Consultamos la lista de OT pendientes
$ParamOT=array(
	$Type,
	"'".$_SESSION['CodUser']."'",
	"'".$IdEvento."'",
	"'".$Sede."'",
	"'".$Cliente."'",
	"'".$NomSucursal."'",
	"'".FormatoFecha($FechaInicial)."'",
	"'".FormatoFecha($FechaFinal)."'",
	"''",
	"''",
	"''",
	"''",
	"''",
	"''",
	"''",
	"'".$Hora."'",
	"'".$Recurso."'",
);

$SQL=EjecutarSP("sp_ConsultarDatosCalendarioRutasOT",$ParamOT);
$Cant=sqlsrv_num_rows($SQL);

?>
<script>
var json=[];
var cant=0;

function BorrarLinea(){
	if(confirm(String.fromCharCode(191)+'Est'+String.fromCharCode(225)+' seguro que desea eliminar este item? Este proceso no se puede revertir.')){
		$.ajax({
			type: "GET",
			url: "includes/procedimientos.php?type=40&linenum="+json,		
			success: function(response){
				FiltrarDatos(5)
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
	if(name=="FechaActividad"){
		if((campo.value=="")||(campo.value.length<10)||(!esFecha(campo.value))){
			 Swal.fire({
				title: '¡Advertencia!',
				text: 'Fecha invalida. Por favor verifique.',
				icon: 'warning',
			});
			campo.value=document.getElementById("FechaLlamada"+id).value;
		}
		ActualizarDatos(name,id,line)	
	}else{//HoraActividad
		if((campo.value=="")||(campo.value.length<5)||(!esHora(campo.value))){
			 Swal.fire({
				title: '¡Advertencia!',
				text: 'Hora invalida. Por favor verifique.',
				icon: 'warning',
			});
			campo.value='<?php echo $Hora;?>';
		}
		ActualizarDatos(name,id,line)
	}
	
}

function ActualizarDatos(name,id,line){//Actualizar datos asincronicamente
	$.ajax({
		type: "GET",
		url: "registro.php?P=36&doctype=14&name="+name+"&value="+btoa(document.getElementById(name+id).value)+"&line="+line,
		success: function(response){
			if(response!="Error"){
				mostrarNotify('Actualizado: '+response)
			}
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
					<button type="button" id="btnDuplicarLineas" title="Duplicar lineas" class="btn btn-primary btn-xs ml-1" disabled onClick="DuplicarLinea();"><i class="far fa-clone"></i></button>
				</div>				
			</th>
			<th>#</th>
			<th>Llamada de servicio</th>
			<th>Cliente</th>
			<th>Sucursal</th>
			<th>Fecha llamada</th>
			<th>Fecha actividad <span class="text-danger">*</span></th>
			<th>Hora actividad <span class="text-danger">*</span></th>
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
				<td><?php echo $row['DocNum'];?></td>
				<td><?php echo $row['NombreClienteLlamada'];?></td>						
				<td><?php echo $row['NombreSucursal'];?></td>
				<td><?php echo $row['FechaLlamada']->format('Y-m-d');?><input type="hidden" id="FechaLlamada<?php echo $i;?>" value="<?php echo $row['FechaLlamada']->format('Y-m-d');?>" /></td>
				 
				<td><input type="text" id="FechaActividad<?php echo $i;?>" name="FechaActividad" class="form-control FechaActividad" value="<?php if($row['FechaActividad']!=""){echo $row['FechaActividad']->format('Y-m-d');}?>" placeholder="YYYY-MM-DD" onChange="ValidarDatosDetalle('FechaActividad',<?php echo $i;?>,<?php echo $row['ID'];?>);" autocomplete="off"></td>
				
				 <td><input name="HoraActividad" type="text" class="form-control HoraInicio" id="HoraActividad<?php echo $i;?>" placeholder="HH:MM" onChange="ValidarDatosDetalle('HoraActividad',<?php echo $i;?>,<?php echo $row['ID'];?>);" value="<?php if($row['HoraActividad']!=""){echo $row['HoraActividad']->format('H:i');}?>" autocomplete="off"></td>
			   <td>
				 <select name="IdTecnico" id="IdTecnico<?php echo $i;?>" class="select2 form-control" style="width: 100%" onChange="ActualizarDatos('IdTecnico',<?php echo $i;?>,<?php echo $row['ID'];?>);">
				   <?php
					   while($row_Recursos=sqlsrv_fetch_array($SQL_Recursos)){?>
							<option value="<?php echo $row_Recursos['ID_Empleado'];?>" <?php if((isset($row['IdTecnico'])&&($row['IdTecnico']!=""))&&(strcmp($row_Recursos['ID_Empleado'],$row['IdTecnico'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Recursos['NombreEmpleado'];?></option>
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
	
	var horaActividad = document.getElementsByName("HoraActividad");
	var fechaActividad = document.getElementsByName("FechaActividad");
	
	fechaActividad.forEach(function(currentValue){
		vanillaTextMask.maskInput({
			inputElement: currentValue,
			mask: [/\d/,/\d/,/\d/,/\d/,'-',/\d/,/\d/,'-',/\d/,/\d/],
			guide: false
		})
	})
	
	horaActividad.forEach(function(currentValue){
		vanillaTextMask.maskInput({
			inputElement: currentValue,
			mask: [/\d/,/\d/,':',/\d/,/\d/],
			guide: false
		})
	})
	
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
</script>