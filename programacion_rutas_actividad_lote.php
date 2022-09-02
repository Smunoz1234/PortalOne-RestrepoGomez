<?php
require_once("includes/conexion.php");
PermitirAcceso(312);

$sw=0;

$IdEvento = isset($_POST['idEvento']) ? base64_decode($_POST['idEvento']) : 0;
$SedeLote = isset($_GET['SedeLote']) ? $_GET['SedeLote'] : "";

$ParamSucursal=array(
	"'".$_SESSION['CodUser']."'"
);
$SQL_Suc=EjecutarSP('sp_ConsultarSucursalesUsuario',$ParamSucursal);

//Lista de recursos (Tecnicos)
$ParamRec=array(
	"'".$_SESSION['CodUser']."'",
	"'".$SedeLote."'"	
);

$SQL_Recursos=EjecutarSP("sp_ConsultarTecnicos",$ParamRec);

$fecha = date('Y-m-d');
$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasDocSAP").' day');
$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
$FechaInicial=$nuevafecha;

$fecha = date('Y-m-d');
$nuevafecha = strtotime ('+'.ObtenerVariable("DiasRangoFechasDocSAP").' day');
$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
$FechaFinal=$nuevafecha;

?>
<script type="text/javascript">
	$(document).ready(function() {//Cargar los almacenes dependiendo del proyecto
		$("#NombreClienteLote").change(function(){
			var NomCliente=document.getElementById("NombreClienteLote");
			var Cliente=document.getElementById("ClienteLote");
			if(NomCliente.value==""){
				Cliente.value="";
				$("#ClienteLote").trigger("change");
			}
		});
		$("#ClienteLote").change(function(){
			var Cliente=document.getElementById("ClienteLote");
			$.ajax({
				type: "POST",
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Cliente.value,
				success: function(response){
					$('#Sucursal').html(response).fadeIn();
				}
			});
		});
		$("#SedeLote").change(function(){
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=27&id="+document.getElementById('SedeLote').value,
				success: function(response){
					$('#RecursosLote').html(response);
					$("#RecursosLote").trigger("change");
				}
			});
		});
	});
</script>
<div class="modal-content">
  <div class="modal-header">
    <h5 class="modal-title">Crear actividades en lote</h5>
    <button type="button" class="close" aria-label="Close" onClick="CerrarModal();">×</button>
  </div>
  <div class="modal-body">	
	  <div class="pt-3 pr-3 pl-3 pb-1 mb-2 bg-primary text-white"><h5><i class="fas fa-filter"></i> Filtros de busqueda</h5></div>
	  <form method="post" class="form-horizontal" id="frmActividadesLote">
		<div class="form-row">
			<div class="form-group col-lg-2">
				<label class="form-label">Fecha inicial <span class="text-danger">*</span></label>
				<div class="input-group">
					<input name="FechaInicial" type="text" class="form-control" id="FechaInicial" value="<?php echo $FechaInicial;?>" placeholder="YYYY-MM-DD" autocomplete="off" required>
				</div>
			</div>
			<div class="form-group col-lg-2">
				<label class="form-label">Fecha final <span class="text-danger">*</span></label>
				<div class="input-group">
					<input name="FechaFinal" type="text" class="form-control" id="FechaFinal" value="<?php echo $FechaFinal;?>" placeholder="YYYY-MM-DD" autocomplete="off" required>
				</div>
			</div>  
			<div class="form-group col-lg-4">
				<label class="form-label">Cliente</label>
				<input name="ClienteLote" type="hidden" id="ClienteLote" value="<?php if(isset($_GET['ClienteLote'])&&($_GET['ClienteLote']!="")){ echo $_GET['ClienteLote'];}?>">
				<input name="NombreClienteLote" type="text" class="form-control" id="NombreClienteLote" placeholder="Ingrese para buscar..." value="<?php if(isset($_GET['NombreClienteLote'])&&($_GET['NombreClienteLote']!="")){ echo $_GET['NombreClienteLote'];}?>">
			</div>
			<div class="form-group col-lg-4">
				<label class="form-label">Sucursal</label>
				<div class="select2-success">
				  <select name="Sucursal" id="Sucursal" class="select2OTLote form-control" style="width: 100%">
					 <option value="">(TODOS)</option>
					 <?php 
					 if(isset($_GET['Sucursal'])){//Cuando se ha seleccionado una opción
						 if(PermitirFuncion(205)){
							$Where="CodigoCliente='".$_GET['Cliente']."'";
							$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","NombreSucursal",$Where);
						 }else{
							$Where="CodigoCliente='".$_GET['Cliente']."' and ID_Usuario = ".$_SESSION['CodUser'];
							$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal",$Where);	
						 }
						 while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
							<option value="<?php echo $row_Sucursal['NombreSucursal'];?>" <?php if(strcmp($row_Sucursal['NombreSucursal'],$_GET['Sucursal'])==0){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['NombreSucursal'];?></option>
					 <?php }
					 }?>
				  </select>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-lg-4">
				<label class="form-label">Sede</label>
				 <div class="select2-success">
				  <select name="SedeLote" id="SedeLote" class="select2OTLote form-control" style="width: 100%">
					  <option value="">Seleccione...</option>
					   <?php
					   while($row_Suc=sqlsrv_fetch_array($SQL_Suc)){?>
							<option value="<?php echo $row_Suc['IdSucursal'];?>" <?php if((isset($_GET['SedeLote'])&&($_GET['SedeLote']!=""))&&(strcmp($row_Suc['IdSucursal'],$_GET['SedeLote'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Suc['DeSucursal'];?></option>
					  <?php }?>
				  </select>
				</div>
			</div>
			<div class="form-group col-lg-4">
				<label class="form-label">Técnico por defecto <span class="text-danger">*</span></label>
				 <div class="select2-success">
				  <select name="RecursosLote" id="RecursosLote" class="select2OTLote form-control" style="width: 100%" required>
					   <option value="">Seleccione...</option>
					   <?php
					   while($row_Recursos=sqlsrv_fetch_array($SQL_Recursos)){?>
							<option value="<?php echo $row_Recursos['ID_Empleado'];?>" <?php if((isset($_GET['RecursosLote'])&&($_GET['RecursosLote']!=""))&&(strcmp($row_Recursos['ID_Empleado'],$_GET['RecursosLote'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Recursos['NombreEmpleado'];?></option>
					  <?php }?>
				  </select>
				</div>
			</div>
			<div class="form-group col-lg-2">
				<label class="form-label">Hora inicial <span class="text-danger">*</span></label>
				<div class="input-group">
					<input name="HoraInicio" id="HoraInicio" type="text" class="form-control" value="08:00" required="required" placeholder="HH:MM" autocomplete="off">
				</div>
			</div>
			<div class="form-group col-lg-2">
				<button id="btnFiltrar" type="submit" class="btn btn-success load mt-4"><i class="fas fa-filter"></i> Filtrar datos</button>
			</div>
		</div>
	  </form>
	  <div id="dvResultados" class="card mt-md-3"></div>
  </div>
  <div class="modal-footer">
	  <button type="button" id="btnGenerarAct" class="btn btn-primary md-btn-flat" disabled onClick="GenerarActividades();"><i class="fas fa-check-circle"></i> Generar actividades</button>
	  <button type="button" class="btn btn-secondary md-btn-flat" onClick="CerrarModal();"><i class="fas fa-window-close"></i> Cerrar</button>	
  </div>
</div>

<script>
var filter=false;

$(document).ready(function() {
	$("#frmActividadesLote").validate({
		submitHandler: function(form,event){
			event.preventDefault()
			preFiltrarDatos();
		}
	});
	
	$(".select2OTLote").select2({
        dropdownParent: $('#ModalAct')
    });

	 $('#FechaInicial').flatpickr({
		 dateFormat: "Y-m-d",
		 static : true,
		 allowInput: true
	 });

	 $('#FechaFinal').flatpickr({
		 dateFormat: "Y-m-d",
		 static : true,
		 allowInput: true
	 });
	
	$('#HoraInicio').flatpickr({
		 enableTime: true,
		 noCalendar: true,
		 dateFormat: "H:i",
		 time_24hr: true,
		 static : true,
		 allowInput: true
	 });

	var options = {
		url: function(phrase) {
			return "ajx_buscar_datos_json.php?type=7&id="+phrase;
		},

		getValue: "NombreBuscarCliente",
		requestDelay: 400,
		list: {
			match: {
				enabled: true
			},
			onClickEvent: function() {
				var value = $("#NombreClienteLote").getSelectedItemData().CodigoCliente;
				$("#ClienteLote").val(value).trigger("change");
			}
		}
	};

	$("#NombreClienteLote").easyAutocomplete(options);
	$(".easy-autocomplete").removeAttr("style");

});
	
function preFiltrarDatos(){
	if(filter==true){
		Swal.fire({
			title: "¿Desea volver a filtrar?",
			text: "Se perderán los cambios que ha realizado",
			icon: "warning",
			showCancelButton: true,
			confirmButtonText: "Si, confirmo",
			cancelButtonText: "No"
		}).then((result) => {
			if (result.isConfirmed) {
				FiltrarDatos()
				filter=true
			}
		});
	}else{
		FiltrarDatos()
		filter=true
	}	
}

function FiltrarDatos(type=3){
	blockUI();
	var Evento=document.getElementById("IdEvento").value;
	var Cliente=$("#ClienteLote").val();
	var SucursalCliente=$("#Sucursal").val();
	var FechaInicioOT=$("#FechaInicial").val();
	var FechaFinalOT=$("#FechaFinal").val();
	var RecursosLote=$("#RecursosLote").val();
	var HoraInicio=$("#HoraInicio").val();
	var Sede=$("#Sede").val();
	
	$.ajax({
		type: "POST",
		data:{
			idEvento:'<?php echo $_POST['idEvento'];?>',
			SucursalCliente:btoa(SucursalCliente),
			FechaInicio:FechaInicioOT,
			FechaFinal:FechaFinalOT,
			Cliente:Cliente,
			Recursos:RecursosLote,
			HoraInicio:HoraInicio,
			Sede:Sede,
			type:type
		},
		url: "programacion_rutas_actividad_lote_detalle.php",
		success: function(response){
			$('#dvResultados').html(response);
			$("#btnGenerarAct").prop('disabled', false);
			blockUI(false);
		}
	});
}
	
function GenerarActividades(){	
	Swal.fire({
		title: "¿Está seguro que desea continuar?",
		text: "Se crearán las actividades en el calendario",
		icon: "warning",
		showCancelButton: true,
		confirmButtonText: "Si, confirmo",
		cancelButtonText: "No"
	}).then((result) => {
		if (result.isConfirmed) {
			blockUI();
			$.ajax({
				type: "GET",
				url: "includes/procedimientos.php?type=35&&id_evento=<?php echo base64_decode($_POST['idEvento']);?>",		
				success: function(response){
					if(response=="OK"){
						blockUI(false);
						$("#btnGuardar").prop('disabled', false);
						$("#btnPendientes").prop('disabled', false);
						Swal.fire({
							title: "Se han insertado las actividades en el calendario",
							text: "Recuerde guardar los datos en el botón Guardar todo",
							icon: 'success',
						});
						$('#ModalAct').modal("hide");
						RefresarCalendario()
						FiltrarOT()
					}
				}						
			});
		}else{
			blockUI(false);
		}
	});	
}
	
function CerrarModal(){	
	Swal.fire({
		title: "¿Está seguro que desea cerrar?",
		text: "Se perderán los cambios que ha realizado",
		icon: "warning",
		showCancelButton: true,
		confirmButtonText: "Si, confirmo",
		cancelButtonText: "No"
	}).then((result) => {
		if (result.isConfirmed) {
			$('#ModalAct').modal("hide");
		}
	});	
}
</script>