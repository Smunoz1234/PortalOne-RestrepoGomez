<?php
require_once("includes/conexion.php");
PermitirAcceso(502);
//require_once("includes/conexion_hn.php");

if(isset($_GET['id'])&&$_GET['id']!=""){
	$id=base64_decode($_GET['id']);
	$edit=base64_decode($_GET['edit']);
}else{
	$id="";
	$edit=0;
	$codigo_contacto=0;
}

$codigo_contacto=isset($_GET['cod']) ? base64_decode($_GET['cod']) : $codigo_contacto=0;

if($edit==0){//Creando
	
}else{//Actualizando
	$SQL=Seleccionar('uvw_Sap_tbl_SociosNegociosContactos','*',"[CodigoCliente]='".$id."' and [CodigoContacto]='".$codigo_contacto."'");
	$row=sqlsrv_fetch_array($SQL);
}

//Grupo de correos
$SQL_GrupoCorreo=Seleccionar('uvw_Sap_tbl_GrupoCorreo','*');

?>

<div class="form-group">
	<label class="control-label">Nombre <span class="text-danger">*</span></label>
	<input type="text" class="form-control" name="NombreContacto" id="NombreContacto" value="" required onChange="GuardarDatos();">
</div>	
<div class="form-group">
	<label class="control-label">Segundo nombre</label>
	<input type="text" class="form-control" name="SegundoNombre" id="SegundoNombre" value="" onChange="GuardarDatos();">
</div>
<div class="form-group">
	<label class="control-label">Apellidos <span class="text-danger">*</span></label>
	<input type="text" class="form-control" name="Apellidos" id="Apellidos" value="" required onChange="GuardarDatos();">
</div>
<div class="form-group">
	<label class="control-label">Cédula <span class="text-danger">*</span></label>
	<input type="text" class="form-control" maxlength="15" onKeyPress="return justNumbers(event,this.value);" name="CedulaContacto" id="CedulaContacto" value="" required onChange="GuardarDatos();">
</div>
<div class="form-group">
	<label class="control-label">Teléfono <span class="text-danger">*</span></label>
	<input type="text" maxlength="20" class="form-control" name="Telefono" id="Telefono" value="" required onChange="GuardarDatos();">
</div>
<div class="form-group">
	<label class="control-label">Celular <span class="text-danger">*</span></label>
	<input type="text" maxlength="50" class="form-control" name="TelefonoCelular" id="TelefonoCelular" value="" onChange="GuardarDatos();">
</div>
<div class="form-group">
	<label class="control-label">Actividad económica <span class="text-danger">*</span></label>
	<select name="ActEconomica" class="form-control" id="ActEconomica" required onChange="GuardarDatos();">
		<option value="">Seleccione...</option>
		<option value="EMPLEADO">EMPLEADO</option>
		<option value="INDEPENDIENTE">INDEPENDIENTE</option>
		<option value="OTRO">OTRO</option>
	</select>
</div>
<div class="form-group">
	<label class="control-label">Rep. Legal <span class="text-danger">*</span></label>
	<select name="RepLegal" class="form-control" id="RepLegal" required onChange="GuardarDatos();">
		<option value="NO">NO</option>
		<option value="SI">SI</option>
	</select>
</div>
<div class="form-group">
	<label class="control-label">Email <span class="text-danger">*</span></label>
	<input type="email" class="form-control" name="Email" id="Email" value="" onChange="ValidarEmail();GuardarDatos();">
	<div id="spinEmail" style="display: none;" class="sk-spinner sk-spinner-wave">
		<div class="sk-rect1"></div>
		<div class="sk-rect2"></div>
		<div class="sk-rect3"></div>
		<div class="sk-rect4"></div>
		<div class="sk-rect5"></div>
	</div>
	<div id="ValEmail"></div>
</div>
<div class="form-group">
	<label class="control-label">Cargo/Vínculo <span class="text-danger">*</span></label>
	<input type="text" class="form-control" name="Posicion" id="Posicion" value="" required onChange="GuardarDatos();">
</div>
<div class="form-group">
	<label class="control-label">Grupo correo</label>
	<select name="GrupoCorreo" id="GrupoCorreo" class="form-control" onChange="GuardarDatos();">
		<option value="">(Ninguno)</option>
	<?php 
		$SQL_GrupoCorreo=Seleccionar('uvw_Sap_tbl_GrupoCorreo','*');
		while($row_GrupoCorreo=sqlsrv_fetch_array($SQL_GrupoCorreo)){?>
			<option value="<?php echo $row_GrupoCorreo['ID_GrupoCorreo'];?>"><?php echo $row_GrupoCorreo['DE_GrupoCorreo'];?></option>
	<?php }?>
	</select>
</div>
<div class="form-group">
	<label class="control-label">Estado</label>
	<select name="EstadoContacto" id="EstadoContacto" class="form-control" onChange="GuardarDatos();">
		<option value="Y">Activo</option>
		<option value="N">Inactivo</option>
	</select>
</div>

<input id="CodigoContacto" name="CodigoContacto" type="hidden" value="<?php echo $codigo_contacto;?>" />
<script>
	$(document).ready(function(){
		CargarDatos();
	});
	
function CargarDatos(){
	let datosCliente = window.sessionStorage.getItem('<?php echo $id;?>')
	let codContacto = document.getElementById('CodigoContacto').value;
	let json=[]
	let sw=-1;
	
//	let primer_nombre= "";
//	let segundo_nombre= "";
//	let apellidos= "";
//	let cedula="";
//	let telefono= "";
//	let celular= "";
//	let act_economica= "";
//	let rep_legal= "";
//	let email= "";
//	let posicion= "";
//	let grupo_correo= "";
//	let estado= "";

	if(datosCliente){
		json = JSON.parse(datosCliente)
	}else{
		json.push({
			cod_cliente: '<?php echo $id;?>',
			contactos:[],
			direcciones:[]
		})
		window.sessionStorage.setItem('<?php echo $id;?>','')
	}

	json[0].contactos.forEach(function(element,index){
		if(json[0].contactos[index].cod_contacto==codContacto){
			sw=index;
		}
	});
	
	if(sw>=0){
		document.getElementById('NombreContacto').value = json[0].contactos[sw].primer_nombre;
		document.getElementById('SegundoNombre').value = json[0].contactos[sw].segundo_nombre;
		document.getElementById('Apellidos').value = json[0].contactos[sw].apellidos;
		document.getElementById('CedulaContacto').value = json[0].contactos[sw].cedula;
		document.getElementById('Telefono').value = json[0].contactos[sw].telefono;
		document.getElementById('TelefonoCelular').value = json[0].contactos[sw].celular;
		document.getElementById('ActEconomica').value = json[0].contactos[sw].act_economica;
		document.getElementById('RepLegal').value = json[0].contactos[sw].rep_legal;
		document.getElementById('Email').value = json[0].contactos[sw].email;
		document.getElementById('Posicion').value = json[0].contactos[sw].posicion;
		document.getElementById('GrupoCorreo').value = json[0].contactos[sw].grupo_correo;
		document.getElementById('EstadoContacto').value = json[0].contactos[sw].estado;
	}else{
		document.getElementById('NombreContacto').value = '<?php if($edit==1){if($row['NombreContacto']!=""){ echo $row['NombreContacto'];}else{echo $row['ID_Contacto'];}}?>';
		document.getElementById('SegundoNombre').value = '<?php if($edit==1){ echo $row['SegundoNombre'];}?>';
		document.getElementById('Apellidos').value = '<?php if($edit==1){ echo $row['Apellidos'];}?>';
		document.getElementById('CedulaContacto').value = '<?php if($edit==1){ echo $row['CedulaContacto'];}?>';
		document.getElementById('Telefono').value = '<?php if($edit==1){ echo $row['Telefono1'];}?>';
		document.getElementById('TelefonoCelular').value = '<?php if($edit==1){ echo $row['TelefonoCelular'];}?>';
		document.getElementById('ActEconomica').value = '<?php if($edit==1){ echo $row['ActEconomica'];}?>';
		document.getElementById('RepLegal').value = '<?php if($edit==1){ echo $row['RepLegal'];}?>';
		document.getElementById('Email').value = '<?php if($edit==1){ echo $row['CorreoElectronico'];}?>';
		document.getElementById('Posicion').value = '<?php if($edit==1){ echo $row['Posicion'];}?>';
		document.getElementById('GrupoCorreo').value = '';
		document.getElementById('EstadoContacto').value = '<?php if($edit==1){ echo $row['Estado'];}?>';
	}	
}
	
function GuardarDatos(){
	let datosCliente = window.sessionStorage.getItem('<?php echo $id;?>')
	let codContacto = document.getElementById('CodigoContacto').value;
	let json=[]
	let sw=-1;
	let metodo=2;
	if(datosCliente){
		json = JSON.parse(datosCliente)
	}else{
		json.push({
			cod_cliente: '<?php echo $id;?>',
			contactos:[],
			direcciones:[]
		})
		window.sessionStorage.setItem('<?php echo $id;?>','')
	}

	json[0].contactos.forEach(function(element,index){
		if(json[0].contactos[index].cod_contacto==codContacto){
			sw=index;
			metodo=json[0].contactos[index].metodo;
		}
		//console.log(elemente.log(element,index);
	});
	
	if(sw>=0){
		json[0].contactos.splice(sw, 1);
	}
	
	json[0].contactos.push({
		cod_contacto: codContacto,
		primer_nombre: document.getElementById('NombreContacto').value,
		segundo_nombre: document.getElementById('SegundoNombre').value,
		apellidos: document.getElementById('Apellidos').value,
		cedula: document.getElementById('CedulaContacto').value,
		telefono: document.getElementById('Telefono').value,
		celular: document.getElementById('TelefonoCelular').value,
		act_economica: document.getElementById('ActEconomica').value,
		rep_legal: document.getElementById('RepLegal').value,
		email: document.getElementById('Email').value,
		posicion: document.getElementById('Posicion').value,
		grupo_correo: document.getElementById('GrupoCorreo').value,
		estado: document.getElementById('EstadoContacto').value,
		metodo: metodo
	})
	
	let badge=' <span class="badge badge-info">Nuevo</span>';
	
	if(metodo==2){//Si estoy actualizando el contacto
		badge=' <span class="badge badge-warning">Editado</span>';		
	}
	
	let lbl_Ctc=window.frames['frameCtc'].document.getElementById('Ctc_'+codContacto);
	let lbl_CtcTel=window.frames['frameCtc'].document.getElementById('CtcTel_'+codContacto);
	let lbl_CtcCargo=window.frames['frameCtc'].document.getElementById('CtcCargo_'+codContacto);		

	//Nombre contacto
	lbl_Ctc.innerHTML='<i class="fa fa-user"></i> '+document.getElementById('NombreContacto').value + ' ' +document.getElementById('Apellidos').value + badge;

	//Telefono
	if(document.getElementById('Telefono').value!=""){
		lbl_CtcTel.innerHTML='<i class="fa fa-phone"></i> '+document.getElementById('Telefono').value;
	}else{
		lbl_CtcTel.innerHTML="";
	}

	//Cargo
	if(document.getElementById('Posicion').value!=""){
		lbl_CtcCargo.innerHTML='<i class="fa fa-tag"></i> '+document.getElementById('Posicion').value;
	}else{
		lbl_CtcCargo.innerHTML="";
	}	
	
	window.sessionStorage.setItem('<?php echo $id;?>',JSON.stringify(json))
	
}
</script>
