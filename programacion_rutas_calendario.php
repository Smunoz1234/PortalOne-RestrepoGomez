
<?php
require_once("includes/conexion.php");

$type=0;

if(isset($_GET['type'])){
	$type=$_GET['type'];
}

if(isset($_GET['sw'])){
	$sw=$_GET['sw'];
}

if(isset($_GET['fchinicial'])){
	$FechaInicial=$_GET['fchinicial'];
}

if(isset($_GET['pTecnicos'])){
	$Recurso=$_GET['pTecnicos'];
}

if(isset($_GET['pIdEvento'])){
	$IdEvento=$_GET['pIdEvento'];
}

if(isset($_GET['pSede'])){
	$Sede=$_GET['pSede'];
}

if(isset($_GET['pGrupo'])){
	$Grupo=$_GET['pGrupo'];
}

if($type==1){//Si estoy refrescando datos ya cargados
	
	//Tecnicos para seleccionar
	$ParamRec=array(
		"'".$_SESSION['CodUser']."'",
		"'".$Sede."'",
		"'".$Grupo."'",
		"'".$Recurso."'"
	);

	$SQL_Recursos=EjecutarSP("sp_ConsultarTecnicos",$ParamRec);
	
	//Datos de las actividades para mostrar
	$ParamCons=array(
		"'".$Recurso."'",
		"'".$Grupo."'",
		"'".$IdEvento."'",
		"'".$_SESSION['CodUser']."'"
	);

	$SQL_Actividad=EjecutarSP("sp_ConsultarDatosCalendarioRutasRecargar",$ParamCons);	
}elseif($type==0&&$sw==1){
	array_push($ParamRec, "'".$FilRec."'");
	$SQL_Recursos=EjecutarSP("sp_ConsultarTecnicos",$ParamRec);
}

?>
<div id="calendario"></div>
<script>

    $(document).ready(function() {
				
        /* initialize the calendar
         -----------------------------------------------------------------*/
			var CalendarJS = FullCalendar.Calendar;
			var Draggable = FullCalendar.Draggable;

			var containerEl = document.getElementById('dvOT');
			var calendarEl = document.getElementById('calendario');
		
			var fechaActual='<?php if($sw==1){echo $FechaInicial;}else{echo date('Y-m-d');}?>'
			
			var vistaActual=window.sessionStorage.getItem('CurrentViewCalendar')
			
			if(!vistaActual){
				vistaActual='resourceTimeGridWeek'
			}
		
			var visualizarFechasActual=true
		
			if(window.sessionStorage.getItem('DateAboveResources')==="false"){
				visualizarFechasActual=false
			}
			
			<?php if(isset($_GET['reload'])||$type==1){?>
				fechaActual = window.sessionStorage.getItem('CurrentDateCalendar')
				if(!fechaActual){
					fechaActual='<?php echo $FechaInicial;?>'
				}
			<?php }?>

			// initialize the external events
			// -----------------------------------------------------------------

			new Draggable(containerEl, {
				itemSelector: '.item-drag',
				eventData: function(eventEl) {
//					console.log(eventEl)
					var new_id=0;
					$.ajax({
						url:"ajx_buscar_datos_json.php",
						data:{type:26},
						dataType:'json',
						async:false,
						success: function(data){
							new_id=data.NewID;
						}
					});
					return {
						id: new_id,
						title: eventEl.dataset.title,
						duration: '02:00'
					};
				}
			});
		
			//Identificar si un evento fue copiado
			var copiado=false;

			calendar = new CalendarJS(calendarEl, {
				locale: 'es',
				themeSystem: 'bootstrap',
				headerToolbar: {
				  left: 'prev,next today',
				  center: 'title',
				  right: 'dayGridMonth,resourceTimeGridWeek,resourceTimeGridDay,resourceTimeGridFourDay,listWeek'
				},
				buttonText:{
				  today: 'Hoy',
				  month: 'Mes',
				  week:  'Semana',
				  day:	 'Día',
				  list:  'Agenda'
				},
				initialView: vistaActual,
				initialDate: fechaActual,
				datesSet: function(dateInfo){
//					console.log(dateInfo)
					window.sessionStorage.setItem('CurrentViewCalendar',dateInfo.view.type)
					window.sessionStorage.setItem('CurrentDateCalendar',dateInfo.startStr.substring(0,10)) 				
				},
				datesAboveResources: visualizarFechasActual,
				editable: true,
//				selectable: true,
				droppable: true,
//				drop: function(info){
//					//console.log("Drop",info.resource.id)
//					//console.log(info)
//					//console.log(info.draggedEl.parentNode)
//					debugger;
//					if(info.draggedEl.parentNode){
//						info.draggedEl.parentNode.removeChild(info.draggedEl)
//					}else{
//						$(info.draggedEl).remove()
//					}
//					
//					debugger;
//				},
//				eventDragStop:function(info) {
//					console.log(info)
//				},
				views: {
				  resourceTimeGridFourDay: {
					type: 'resourceTimeGrid',
					duration: { days: 4 },
					buttonText: '4 dias'
				  }
				},
			    resources: [
					<?php 
					if($sw==1){					
						while($row_Recursos=sqlsrv_fetch_array($SQL_Recursos)){
							//$newColor=GenerarColor();
					?>
				 			{
								id: '<?php echo $row_Recursos['ID_Empleado'];?>', 
								title: '<?php echo $row_Recursos['NombreEmpleado'];?>'
							},
					<?php }
					}?>
			    ],
				resourceOrder: 'title',
				eventDidMount: function(info){
//					console.log('Se ejecuto eventDidMount')
//					$(info.el).tooltip({ title: info.event.extendedProps.informacionAdicional })
//					console.log(info.event)
					if(info.view.type!='dayGridMonth' && info.view.type!='listWeek'){
						let cont = info.el.getElementsByClassName('fc-event-time')//fc-event-title-container
						
						//-3 Abierto, -2 Pendiente, -1 Cerrado
						if(info.event.extendedProps.estadoLlamadaServ=='-3'){					
							cont[0].insertAdjacentHTML('beforeend','<i class="fas fa-door-open pull-right" title="Llamada de servicio abierta"></i>')
						}else if(info.event.extendedProps.estadoLlamadaServ=='-2'){
							cont[0].insertAdjacentHTML('beforeend','<i class="fas fa-clock pull-right" title="Llamada de servicio pendiente"></i>')
						}else if(info.event.extendedProps.estadoLlamadaServ=='-1'){
							cont[0].insertAdjacentHTML('beforeend','<i class="fas fa-door-closed pull-right" title="Llamada de servicio cerrada"></i>')
						}else if(info.event.extendedProps.estadoLlamadaServ==''){//No tiene llamada de servicio
							cont[0].insertAdjacentHTML('beforeend','<i class="fas fa-unlink pull-right" title="Actividad sin llamada de servicio asociada"></i>')
						}else if(info.event.extendedProps.estadoLlamadaServ===undefined){//Cuando se agrega por primera vez haciendo drop
							cont[0].insertAdjacentHTML('beforeend','<i class="fas fa-door-open pull-right" title="Llamada de servicio abierta"></i>')
						}
					
						//Si tiene llamada de servicio
						if(info.event.extendedProps.llamadaServicio===undefined || info.event.extendedProps.llamadaServicio!=='0'){//Cuando se agrega por primera vez haciendo drop
							cont[0].insertAdjacentHTML('beforeend','<i class="fas fa-phone-square-alt mr-1 pull-right" title="Tiene asociada una llamada de servicio"></i>')
						}					
					}					
				},
				events:[
					<?php 
					if($sw==1){
						while($row_Actividad=sqlsrv_fetch_array($SQL_Actividad)){
							$classAdd="";
							if($row_Actividad['IdEstadoActividad']=='Y'){
								$classAdd="'event-striped'";
							}
							if($row_Actividad['IdEstadoLlamada']=='-2'){//Llamada pendiente
								$classAdd.=",'event-pend'";
							}
					?>
					{
						id: '<?php echo $row_Actividad['ID_Actividad'];?>',
						title: '<?php echo $row_Actividad['EtiquetaActividad'];?>',
						start: '<?php echo $row_Actividad['FechaHoraInicioActividad']->format('Y-m-d H:i');?>',
						end: '<?php echo $row_Actividad['FechaHoraFinActividad']->format('Y-m-d H:i');?>',
						resourceId: '<?php echo $row_Actividad['ID_EmpleadoActividad'];?>',
						textColor: '#fff',
						backgroundColor: '<?php echo $row_Actividad['ColorEstadoServicio'];?>',
						borderColor: '<?php echo $row_Actividad['ColorEstadoServicio'];?>',
						classNames: [<?php echo $classAdd;?>],
						tl:'<?php echo ($row_Actividad['IdActividadPortal']==0) ? 1 : 0;?>',
						estado:'<?php echo $row_Actividad['IdEstadoActividad'];?>',
						llamadaServicio: '<?php echo $row_Actividad['ID_LlamadaServicio'];?>',
						estadoLlamadaServ: '<?php echo $row_Actividad['IdEstadoLlamada'];?>',
						informacionAdicional: '<?php echo $row_Actividad['InformacionAdicional'];?>',
						manualChange:'0'
					},
					<?php }
					}?>
				],
				eventDrop:function(info){
//					console.log(info)
					//Cuando se va a duplicar con la tecla CTRL
					if(info.jsEvent.ctrlKey){
						copiado=true;
						var new_id=0;
						$.ajax({
							url:"ajx_buscar_datos_json.php",
							data:{type:26},
							dataType:'json',
							async:false,
							success: function(data){
								new_id=data.NewID;
							}
						});
						var data = {
							id: new_id,
							title: info.event.title,
							start: info.event.start,
							end: info.event.end,
							resourceId: info.event.getResources()[0].id,
							textColor: '#fff',
							backgroundColor: info.event.backgroundColor,
							borderColor: info.event.borderColor,
							extendedProps: {}
						}
						$.ajax({
							type: "GET",
							url: "includes/procedimientos.php?type=31&id_actividad="+new_id+"&id_evento="+$("#IdEvento").val()+"&llamada_servicio="+info.event.extendedProps.llamadaServicio+"&id_empleadoactividad="+info.event.getResources()[0].id+"&fechainicio="+info.event.startStr.substring(0,10)+"&horainicio="+info.event.startStr.substring(11,16)+"&fechafin="+info.event.endStr.substring(0,10)+"&horafin="+info.event.endStr.substring(11,16)+"&sptype=1&metodo=1&docentry=&comentarios_actividad=&estado=&id_tipoestadoact=&fechainicio_ejecucion=&horainicio_ejecucion=&fechafin_ejecucion=&horafin_ejecucion=&turno_tecnico=&id_asuntoactividad=&titulo_actividad=",
							async: false,
							success: function(response){
								if(isNaN(response)){
									Swal.fire({
										title: '¡Advertencia!',
										text: 'No se pudo insertar la actividad en la ruta',
										icon: 'warning',
									});
								}else{
									$("#btnGuardar").prop('disabled', false);
									$("#btnPendientes").prop('disabled', false);
//									data.extendedProps.id = response;
									data.estado = 'N';
									data.llamadaServicio = info.event.extendedProps.llamadaServicio;
									data.manualChange = '0'
									calendar.addEvent(data);
//									var dev = calendar.addEvent(data);
//									console.log("Dev: ",dev)
									info.revert()
//									console.log("newEvent: ",info.event)
									console.log("Se ejecuto eventDrop duplicando.")
									mostrarNotify('Se ha duplicado una actividad')
								}	
								copiado=false;
	//							console.log(response)
							}						
						});						
					}else{//Cuando se mueve el evento a otro lado sin duplicarlo
						copiado=false;
						var ID;
						var docentry;
						var metodo;
						var estado='Y'; //Cerrado
						var manual;
	//					console.log(info.event)
						if((!info.event.extendedProps.tl)||(info.event.extendedProps.tl==0)){
							ID=info.event.id //info.event.extendedProps.id
							estado=info.event.extendedProps.estado
							docentry=0
							metodo=1
							manual=info.event.extendedProps.manualChange
						}else{
							ID=info.event.id
							estado=info.event.extendedProps.estado
							docentry=ID
							metodo=2	
							manual=info.event.extendedProps.manualChange
						}
	//					console.log(estado)
						if(estado==='Y'&&copiado===false){
							info.revert()
							Swal.fire({
								title: '¡Advertencia!',
								text: 'La actividad se encuentra cerrada. No puede ejecutar esta acción.',
								icon: 'warning',
							});
						}else if(copiado===true){
							//info.revert()
						}else{
							//Validar si la información se está cambiando en la ventana modal de la actividad. Si es así no modifico nada por este callback.
							//0-> Se está modificando en el calendario
							//1-> Se está modificando en el modal
							if(manual=='0'){
								$.ajax({
									type: "GET",
									url: "includes/procedimientos.php?type=31&id_actividad="+ID+"&id_evento="+$("#IdEvento").val()+"&llamada_servicio=&id_empleadoactividad="+info.event.getResources()[0].id+"&fechainicio="+info.event.startStr.substring(0,10)+"&horainicio="+info.event.startStr.substring(11,16)+"&fechafin="+info.event.endStr.substring(0,10)+"&horafin="+info.event.endStr.substring(11,16)+"&sptype=2&metodo="+metodo+"&docentry="+docentry+"&comentarios_actividad=&estado=&id_tipoestadoact=&fechainicio_ejecucion=&horainicio_ejecucion=&fechafin_ejecucion=&horafin_ejecucion=&turno_tecnico=&id_asuntoactividad=&titulo_actividad=",		
									success: function(response){
			//							console.log(response)
										if(response!="OK"){
											info.revert()
											Swal.fire({
												title: '¡Advertencia!',
												text: 'No se pudo actualizar la actividad en la ruta',
												icon: 'warning',
											});									
										}else{
											$("#btnGuardar").prop('disabled', false);
											$("#btnPendientes").prop('disabled', false);
										}
										console.log("Se ejecuto eventDrop.")
										mostrarNotify('Se ha editado una actividad')
									}						
								});
							}
						}
					}
				},
				eventResize:function(info){
					var ID;
					var docentry;
					var metodo;
					var estado='Y'; //Cerrado
					var manual;
//					console.log(info.event)
//					console.log("Copiado",copiado)
//					console.log("tl",info.event.extendedProps.tl)
					if((!info.event.extendedProps.tl)||(info.event.extendedProps.tl==0)){
						ID=info.event.id //info.event.extendedProps.id
						estado=info.event.extendedProps.estado
						docentry=0
						metodo=1
						manual=info.event.extendedProps.manualChange
//						console.log("Entro en 1")
//						console.log("ID",ID)
					}else{
						ID=info.event.id
						estado=info.event.extendedProps.estado
						docentry=ID
						metodo=2	
						manual=info.event.extendedProps.manualChange
//						console.log("Entro en 2")
//						console.log("ID",ID)
					}
//					console.log(estado)
					if(estado==='Y'&&copiado===false){
						info.revert()
						Swal.fire({
							title: '¡Advertencia!',
							text: 'La actividad se encuentra cerrada. No puede ejecutar esta acción.',
							icon: 'warning',
						});
					}else{
						//Validar si la información se está cambiando en la ventana modal de la actividad. Si es así no modifico nada por este callback.
						//0-> Se está modificando en el calendario
						//1-> Se está modificando en el modal
//						console.log("Manual",manual)
						if(manual=='0'){
							$.ajax({
								type: "GET",
								url: "includes/procedimientos.php?type=31&id_actividad="+ID+"&id_evento="+$("#IdEvento").val()+"&llamada_servicio=&id_empleadoactividad="+info.event.getResources()[0].id+"&fechainicio="+info.event.startStr.substring(0,10)+"&horainicio="+info.event.startStr.substring(11,16)+"&fechafin="+info.event.endStr.substring(0,10)+"&horafin="+info.event.endStr.substring(11,16)+"&sptype=2&metodo="+metodo+"&docentry="+docentry+"&comentarios_actividad=&estado=&id_tipoestadoact=&fechainicio_ejecucion=&horainicio_ejecucion=&fechafin_ejecucion=&horafin_ejecucion=&turno_tecnico=&id_asuntoactividad=&titulo_actividad=",		
								success: function(response){
		//							console.log(response)
									if(response!="OK"){
										info.revert()
										Swal.fire({
											title: '¡Advertencia!',
											text: 'No se pudo actualizar la actividad en la ruta',
											icon: 'warning',
										});									
									}else{
										$("#btnGuardar").prop('disabled', false);
										$("#btnPendientes").prop('disabled', false);
										mostrarNotify('Se ha editado una actividad')
									}
									console.log("Se ejecuto eventResize.")
								}						
							});
						}
					}
				},
				//eventChange: function(info){},
				eventReceive:function(info){
//					console.log('Se ejecuto eventReceive')
//					console.log(info)
//					console.log(info.event)
//					console.log(info.event.id)
//					console.log(info.event.startStr)
//					console.log(info.event.endStr)
//					console.log(info.draggedEl.dataset.docnum)
//					console.log($("#IdEvento").val())
//					console.log(info.event.getResources()[0].id)					
					if(info.draggedEl.parentNode){
						info.draggedEl.parentNode.removeChild(info.draggedEl)
						$.ajax({
							type: "GET",
							url: "includes/procedimientos.php?type=31&id_actividad="+info.event.id+"&id_evento="+$("#IdEvento").val()+"&llamada_servicio="+info.draggedEl.dataset.docnum+"&id_empleadoactividad="+info.event.getResources()[0].id+"&fechainicio="+info.event.startStr.substring(0,10)+"&horainicio="+info.event.startStr.substring(11,16)+"&fechafin="+info.event.endStr.substring(0,10)+"&horafin="+info.event.endStr.substring(11,16)+"&sptype=1&metodo=1&docentry=&comentarios_actividad=&estado=&id_tipoestadoact=&fechainicio_ejecucion=&horainicio_ejecucion=&fechafin_ejecucion=&horafin_ejecucion=&turno_tecnico=&id_asuntoactividad=&titulo_actividad=",		
							success: function(response){
								if(isNaN(response)){
									Swal.fire({
										title: '¡Advertencia!',
										text: 'No se pudo insertar la actividad en la ruta. Respuesta: '+response,
										icon: 'warning',
									});
								}else{
									$("#btnGuardar").prop('disabled', false);
									$("#btnPendientes").prop('disabled', false);
	//								info.event.setExtendedProp('id',response)
									info.event.setExtendedProp('estado','N')
									info.event.setExtendedProp('llamadaServicio',info.draggedEl.dataset.docnum)
									info.event.setExtendedProp('estadoLlamadaServ',info.draggedEl.dataset.estado)
									info.event.setExtendedProp('informacionAdicional',info.draggedEl.dataset.info)
									info.event.setExtendedProp('manualChange','0')	
									mostrarNotify('Se ha agregado una nueva actividad')
								}
	//							console.log(response)
							}						
						});	
					}else{
//						console.log(info)
						info.event.remove()						
					}					
				},
				eventClick: function(info){
//					console.log(info.event.title)
//					var ID;
					var tl;
					if((!info.event.extendedProps.tl)||(info.event.extendedProps.tl==0)){
//						ID=info.event.extendedProps.id
						tl=0 //Es nuevo
					}else{
//						ID=info.event.id
						tl=info.event.extendedProps.tl //Ya existe
					}
//					console.log('ID',ID)
//					console.log('tl',tl)
//					console.log('tl:',info.event.extendedProps.tl)
					blockUI();
					$.ajax({
						type: "POST",
						async: false,
						url: "programacion_rutas_actividad.php?id="+btoa(info.event.id)+"&idEvento="+btoa($("#IdEvento").val())+"&tl="+tl,
						success: function(response){
							$('#ContenidoModal').html(response);
							$('#ModalAct').modal("show");
							blockUI(false);
						}
					});
				},
				height: 'auto', // will activate stickyHeaderDates automatically!
				contentHeight: 'auto',
				dayMinWidth: 150, // will cause horizontal scrollbars
			});
//			console.log(calendar)
        	calendar.render();
    });

</script>