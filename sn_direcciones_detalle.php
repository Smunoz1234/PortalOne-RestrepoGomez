<?php
require_once("includes/conexion.php");
PermitirAcceso(502);
//require_once("includes/conexion_hn.php");

if(isset($_GET['id'])&&$_GET['id']!=""){
	$id=base64_decode($_GET['id']);
	$edit=0;//base64_decode($_GET['edit']);
//	$tipodir=$_GET['tdir'];
}else{
	$id="";
	$edit=0;
	$linea_direccion=0;
//	$tipodir="";
}

$Cont=0;

$linea_direccion=isset($_GET['cod']) ? base64_decode($_GET['cod']) : 0;
$tipodir=isset($_GET['tdir']) ? $_GET['tdir'] : "";

if($edit==0){//Creando
	
}else{//Actualizando
	$SQL=Seleccionar('uvw_Sap_tbl_SociosNegociosSucursales','*',"[CodigoCliente]='".$id."' and [NumeroLinea]='".$linea_direccion."'");
	$row=sqlsrv_fetch_array($SQL);
}

//Departamentos
$SQL_Dptos=Seleccionar('uvw_Sap_tbl_SN_Municipio','Distinct DeDepartamento','','DeDepartamento');

//Estrato
$SQL_Estrato=Seleccionar('tbl_EstratosSN','*','','Estrato');

?>

<div class="form-group">
	<label class="control-label">Tipo dirección <span class="text-danger">*</span></label>
	 <select name="AdresType" id="AdresType" class="form-control" required onChange="GuardarDatos();">
		<option value="B">DIRECCIÓN DE FACTURACIÓN</option>
		<option value="S">DIRECCIÓN DE ENVÍO</option>
	</select>
</div>	
<div class="form-group">
	<label class="control-label">Nombre dirección <span class="text-danger">*</span></label>
	<input name="Address" type="text" required class="form-control" id="Address" maxlength="50" onChange="GuardarDatos();">
</div>
<div class="form-group">
	<label class="control-label">Dirección <span class="text-danger">*</span></label>
	<input name="Street" type="text" required class="form-control" id="Street" maxlength="100" onChange="GuardarDatos();">
</div>
<div class="form-group">
	<label class="control-label">Departamento <span class="text-danger">*</span></label>
	<select name="County" id="County" class="form-control" required onChange="BuscarCiudad();BuscarCodigoPostal();GuardarDatos();">
		<option value="">Seleccione...</option>
	<?php
		while($row_Dptos=sqlsrv_fetch_array($SQL_Dptos)){?>
			<option value="<?php echo $row_Dptos['DeDepartamento'];?>"><?php echo $row_Dptos['DeDepartamento'];?></option>
	<?php }?>
	</select>
</div>
<div class="form-group">
	<label class="control-label">Ciudad <span class="text-danger">*</span></label>
	<select name="City" id="City" onChange="BuscarBarrio();GuardarDatos();" class="form-control" required>
		<option value="">Seleccione...</option>
	<?php
		if($edit==1){
			$SQL_City=Seleccionar('uvw_Sap_tbl_SN_Municipio','Distinct ID_Municipio, DE_Municipio',"DeDepartamento='".$row['Departamento']."'",'DE_Municipio');
		while($row_City=sqlsrv_fetch_array($SQL_City)){?>
			<option value="<?php echo $row_City['ID_Municipio'];?>"><?php echo $row_City['DE_Municipio'];?></option>
	<?php }
		}?>
	</select>
</div>
<div class="form-group">
	<label class="control-label">Barrio <span class="text-danger">*</span></label>
	<select name="Block" id="Block" class="form-control" required onChange="GuardarDatos();">
		<option value="">Seleccione...</option>
	<?php
		if($edit==1){
			$SQL_Barrio=Seleccionar('uvw_Sap_tbl_Barrios','*',"IdMunicipio='".$row['IdMunicipio']."'",'DeBarrio');
		while($row_Barrio=sqlsrv_fetch_array($SQL_Barrio)){?>
			<option value="<?php echo $row_Barrio['IdBarrio'];?>"><?php echo $row_Barrio['DeBarrio'];?></option>
	<?php }
		}?>
	</select>
</div>
<div class="form-group">
	<label class="control-label">Estrato</label>
	<select name="Estrato" id="Estrato" class="form-control" required onChange="GuardarDatos();">
	<?php
	while($row_Estrato=sqlsrv_fetch_array($SQL_Estrato)){?>
		<option value="<?php echo $row_Estrato['ID_Estrato'];?>"><?php echo $row_Estrato['Estrato'];?></option>
	<?php }?>
	</select>
</div>
<div class="form-group">
	<label class="control-label">Código postal <span class="text-danger">*</span></label>
	<select name="CodigoPostal" id="CodigoPostal" class="form-control" required onChange="GuardarDatos();">
		<option value="">Seleccione...</option>
	<?php
		if($edit==1){
			$SQL_CodigoPostal=Seleccionar('uvw_Sap_tbl_CodigosPostales','*',"DeDepartamento='".$row['Departamento']."'",'ID_CodigoPostal');
		while($row_CodigoPostal=sqlsrv_fetch_array($SQL_CodigoPostal)){?>
			<option value="<?php echo $row_CodigoPostal['ID_CodigoPostal'];?>"><?php echo $row_CodigoPostal['DeCodigoPostal'];?></option>
	<?php }
		}?>
	</select>
</div>
<div class="form-group">
	<label class="control-label">Nombre contacto <span class="text-danger">*</span></label>
	<input name="DirNombreContacto" type="text" required class="form-control" id="DirNombreContacto" maxlength="100" onChange="GuardarDatos();">
</div>
<div class="form-group">
	<label class="control-label">Cargo contacto <span class="text-danger">*</span></label>
	<input name="DirCargoContacto" type="text" required class="form-control" id="DirCargoContacto" maxlength="100" onChange="GuardarDatos();">
</div>
<div class="form-group">
	<label class="control-label">Teléfono contacto <span class="text-danger">*</span></label>
	<input name="DirTelefonoContacto" type="text" required class="form-control" id="DirTelefonoContacto" maxlength="100" onChange="GuardarDatos();">
</div>
<div class="form-group">
	<label class="control-label">Correo contacto <span class="text-danger">*</span></label>
	<input name="DirCorreoContacto" type="text" required class="form-control" id="DirCorreoContacto" maxlength="254" onChange="GuardarDatos();">
</div>
<div class="form-group">
	<label class="control-label">Dirección del contrato <span class="text-danger">*</span></label>
	<select name="DirContrato" id="DirContrato" class="form-control" required onChange="GuardarDatos();">
		<option value="0">NO</option>
		<option value="1">SI</option>
	</select>
</div>
<input id="LineNum" name="LineNum" type="hidden" value="<?php echo $linea_direccion;?>" />
<input id="TipoDir" name="TipoDir" type="hidden" value="<?php echo $tipodir;?>" />
<script>
	$(document).ready(function(){
		CargarDatos();
	});
	
function CargarDatos(){
	let datosCliente = window.sessionStorage.getItem('<?php echo $id;?>')
	let LineNum = document.getElementById('LineNum').value;
	let TipoDir = document.getElementById('TipoDir').value;
	let json=[]
	let sw=-1;
	
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
	
	//Buscar si existe la direccion en la cadena JSON
	json[0].direcciones.forEach(function(element,index){
		if(json[0].direcciones[index].numero_linea==LineNum){
			//Si la encontre, marco el sw con el indice del arreglo
			sw=index;
		}
	});
	
	if(sw>=0){
		document.getElementById('AdresType').value = json[0].direcciones[sw].tipo_direccion;
		document.getElementById('Address').value = json[0].direcciones[sw].nombre_direccion;
		document.getElementById('Street').value = json[0].direcciones[sw].direccion;
		document.getElementById('County').value = json[0].direcciones[sw].departamento;
		BuscarCiudad(false);
		BuscarCodigoPostal();
		document.getElementById('City').value = json[0].direcciones[sw].ciudad;
		BuscarBarrio();
		document.getElementById('Block').value = json[0].direcciones[sw].barrio;
		document.getElementById('Estrato').value = json[0].direcciones[sw].estrato;
		document.getElementById('CodigoPostal').value = json[0].direcciones[sw].codigo_postal;
		document.getElementById('DirNombreContacto').value = json[0].direcciones[sw].nombre_contacto;
		document.getElementById('DirCargoContacto').value = json[0].direcciones[sw].cargo_contacto;
		document.getElementById('DirTelefonoContacto').value = json[0].direcciones[sw].telefono_contacto;
		document.getElementById('DirCorreoContacto').value = json[0].direcciones[sw].correo_contacto;
		document.getElementById('DirContrato').value = json[0].direcciones[sw].direccion_contrato;
	}else{
		document.getElementById('AdresType').value = '<?php if($edit==1){ echo $row['TipoDireccion'];}?>';
		document.getElementById('Address').value = '<?php if($edit==1){ echo $row['NombreSucursal'];}?>';
		document.getElementById('Street').value = '<?php if($edit==1){ echo $row['Direccion'];}?>';
		document.getElementById('County').value = '<?php if($edit==1){ echo $row['Departamento'];}?>';
		document.getElementById('City').value = '<?php if($edit==1){ echo $row['IdMunicipio'];}?>';
		document.getElementById('Block').value = '<?php if($edit==1){ echo $row['IdBarrio'];}?>';
		document.getElementById('Estrato').value = '<?php if($edit==1){ echo $row['Estrato'];}?>';
		document.getElementById('CodigoPostal').value = '<?php if($edit==1){ echo $row['CodigoPostal'];}?>';
		document.getElementById('DirNombreContacto').value = '<?php if($edit==1){ echo $row['NombreContacto'];}?>';
		document.getElementById('DirCargoContacto').value = '<?php if($edit==1){ echo $row['CargoContacto'];}?>';
		document.getElementById('DirTelefonoContacto').value = '<?php if($edit==1){ echo $row['TelefonoContacto'];}?>';
		document.getElementById('DirCorreoContacto').value = '<?php if($edit==1){ echo $row['CorreoContacto'];}?>';
		document.getElementById('DirContrato').value = '<?php if($edit==1){ echo $row['DirContrato'];}?>';
	}	
}
	
function GuardarDatos(){
	let datosCliente = window.sessionStorage.getItem('<?php echo $id;?>')
	let LineNum = document.getElementById('LineNum').value;
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

	json[0].direcciones.forEach(function(element,index){
		if(json[0].direcciones[index].numero_linea==LineNum){
			sw=index;
			metodo=json[0].direcciones[index].metodo;
			metodo= (metodo==1) ? 1: 2;
		}
		//console.log(elemente.log(element,index);
	});
	
	if(sw>=0){
		json[0].direcciones.splice(sw, 1);
	}
	
	json[0].direcciones.push({
		numero_linea: LineNum,
		tipo_direccion: document.getElementById('AdresType').value,
		nombre_direccion: document.getElementById('Address').value,
		direccion: document.getElementById('Street').value,
		departamento: document.getElementById('County').value,
		ciudad: document.getElementById('City').value, 
		barrio: document.getElementById('Block').value, 
		estrato: document.getElementById('Estrato').value, 
		codigo_postal: document.getElementById('CodigoPostal').value, 
		nombre_contacto: document.getElementById('DirNombreContacto').value, 
		cargo_contacto: document.getElementById('DirCargoContacto').value, 
		telefono_contacto: document.getElementById('DirTelefonoContacto').value, 
		correo_contacto: document.getElementById('DirCorreoContacto').value, 
		direccion_contrato: document.getElementById('DirContrato').value,
		metodo: metodo
	})
	
	let badge=' <span class="badge badge-info">Nuevo</span>';
	
	if(metodo==2){//Si estoy actualizando la direccion
		badge=' <span class="badge badge-warning">Editado</span>';		
	}
	
	let lbl_Dir=window.frames['frameDir'].document.getElementById('Dir<?php echo $tipodir;?>_'+LineNum);
	let lbl_Direccion=window.frames['frameDir'].document.getElementById('Dir<?php echo $tipodir;?>Direccion_'+LineNum);
	let lbl_Ciudad=window.frames['frameDir'].document.getElementById('Dir<?php echo $tipodir;?>Ciudad_'+LineNum);
	let lbl_Contacto=window.frames['frameDir'].document.getElementById('Dir<?php echo $tipodir;?>Contacto_'+LineNum);

	//Nombre direccion
	lbl_Dir.innerHTML='<i class="fa fa-home"></i> '+document.getElementById('Address').value + badge;

	//Direccion
	if(document.getElementById('Street').value!=""){
		lbl_Direccion.innerHTML='<i class="fa fa-map-marker"></i> '+document.getElementById('Street').value;
	}else{
		lbl_Direccion.innerHTML="";
	}

	//Ciudad
	if(document.getElementById('City').value!=""){
		let city=document.getElementById('City')
		lbl_Ciudad.innerHTML='<i class="fa fa-university"></i> '+city.options[city.selectedIndex].text;
	}else{
		lbl_Ciudad.innerHTML="";
	}
	
	//Nombre contacto
	if(document.getElementById('DirNombreContacto').value!=""){
		lbl_Contacto.innerHTML='<i class="fa fa-user"></i> '+document.getElementById('DirNombreContacto').value;
	}else{
		lbl_Contacto.innerHTML="";
	}
	
	window.sessionStorage.setItem('<?php echo $id;?>',JSON.stringify(json))
	
}
	
function BuscarCiudad(save=true){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		async: false,
		url: "ajx_cbo_select.php?type=8&id="+document.getElementById('County').value,
		success: function(response){
			$('#City').html(response).fadeIn();
			if(save){
				$('#City').trigger('change');
			}else{
				//BuscarBarrio()
			}			
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
}
	
function BuscarCodigoPostal(){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		async: false,
		url: "ajx_cbo_select.php?type=24&id="+document.getElementById('County').value,
		success: function(response){
			$('#CodigoPostal').html(response).fadeIn();
			//$('#CodigoPostal'+id).trigger('change');
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
}

function BuscarBarrio(){
	$('.ibox-content').toggleClass('sk-loading',true);
	$.ajax({
		type: "POST",
		async: false,
		url: "ajx_cbo_select.php?type=13&id="+document.getElementById('City').value,
		success: function(response){
			$('#Block').html(response).fadeIn();
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
}
</script>
