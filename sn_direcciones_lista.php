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
}

if($edit==0){//Creando
	
}else{//Actualizando
	$SQL=Seleccionar('uvw_Sap_tbl_SociosNegociosSucursales','*',"[CodigoCliente]='".$id."'");
}

?>
<html>
<head>
<?php include_once("includes/cabecera.php"); ?>
<style>	
	body{
		background-color: #ffffff !important;
		overflow-x: auto;
	}
	.table{
		font-size: 10px;
	}
</style>
<script>
function ConsultarDireccion(ID,tdir){
	$('.ibox-content', window.parent.document).toggleClass('sk-loading',true);
	//var frame=window.parent.document.getElementById('frameCtcDetalle');
	$.ajax({
		type: "POST",
		url: "sn_direcciones_detalle.php?edit=<?php echo base64_encode($edit);?>&id=<?php if($edit==1){echo base64_encode($id);}?>&cod="+btoa(ID)+"&tdir="+tdir,
		success: function(response){
			$('#frameDirDetalle', window.parent.document).html(response);
			//$('#CodigoPostal'+id).trigger('change');
			$('.ibox-content', window.parent.document).toggleClass('sk-loading',false);
		}
	});
	//frame.src="sn_contactos_detalle.php?edit=<?php echo base64_encode($edit);?>&id=<?php if($edit==1){echo base64_encode($id);}?>&cod="+btoa(ID);
	
//	$('.ibox-content').toggleClass('sk-loading',false);
}
	
function CrearDireccion(tdir){
	let datosCliente = window.sessionStorage.getItem('<?php echo $id;?>')
	let newCod = window.sessionStorage.getItem('newCod')
	let json=[]
	let sw=-1;
	
	if(!newCod){
		window.sessionStorage.setItem('newCod',0)
		newCod = window.sessionStorage.getItem('newCod')
	}
	
	newCod++;
	window.sessionStorage.setItem('newCod',newCod)
	
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
	
//	json[0].contactos.forEach(function(element,index){
//		if(json[0].contactos[index].cod_contacto==codContacto){
//			sw=index;
//		}
//	});
	
	newCod=(newCod*1000)
	
	json[0].direcciones.push({
		numero_linea: newCod,
		tipo_direccion: tdir,
		nombre_direccion: "",
		direccion: "",
		departamento: "",
		ciudad: "",
		barrio: "",
		estrato: "1",
		codigo_postal: "",
		nombre_contacto: "",
		cargo_contacto: "",
		telefono_contacto: "",
		correo_contacto: "",
		direccion_contrato: "0",
		metodo: 1
	})
	window.sessionStorage.setItem('<?php echo $id;?>',JSON.stringify(json))
	
	let html = document.implementation.createHTMLDocument()
	let tr = html.createElement("tr");
	tr.innerHTML=`<td><a href="#" onClick="ConsultarDireccion('${newCod}','${tdir}');" id="Dir${tdir}_${newCod}"><i class="fa fa-home"></i> Nueva dirección</a></td>
				<td id="Dir${tdir}Direccion_${newCod}"></td>
				<td id="Dir${tdir}Ciudad_${newCod}"></td>
				<td id="Dir${tdir}Contacto_${newCod}"></td>`;
	let tbody=document.getElementById("listaDatosDir"+tdir)
	tbody.appendChild(tr);

}
	
function CargarDireccion(numero_linea,tipo_direccion,nombre_direccion,direccion,departamento,ciudad,barrio,estrato,codigo_postal,nombre_contacto,cargo_contacto,telefono_contacto,correo_contacto,direccion_contrato){
	let datosCliente = window.sessionStorage.getItem('<?php echo $id;?>')
	let json=[]
		
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

	json[0].direcciones.push({
		numero_linea: numero_linea,
		tipo_direccion: tipo_direccion,
		nombre_direccion: atob(nombre_direccion),
		direccion: direccion,
		departamento: departamento,
		ciudad: ciudad,
		barrio: barrio,
		estrato: estrato,
		codigo_postal: codigo_postal,
		nombre_contacto: nombre_contacto,
		cargo_contacto: cargo_contacto,
		telefono_contacto: telefono_contacto,
		correo_contacto: correo_contacto,
		direccion_contrato: direccion_contrato,
		metodo: 0
	})
	window.sessionStorage.setItem('<?php echo $id;?>',JSON.stringify(json))

}

</script>
</head>

<body>
<div class="table-responsive">
	<div class="btn-group">
		<button data-toggle="dropdown" type="button" class="btn btn-primary dropdown-toggle m-sm"><i class="fa fa-plus-circle"></i> Agregar dirección</button>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item" href="#" onClick="CrearDireccion('B');">Dirección de facturación</a></li>
			<li><a class="dropdown-item" href="#" onClick="CrearDireccion('S');">Dirección de envío</a></li>
		</ul>
	</div>	
	<table class="table table-striped table-hover">
		<tbody id="listaDatosDirB">
			<tr><td colspan="4" class="bg-success"><i class="fa fa-list"></i> Direcciones de facturación</td></tr>
		<?php
			$SQL=Seleccionar('uvw_Sap_tbl_SociosNegociosSucursales','*',"[CodigoCliente]='".$id."' and TipoDireccion='B'");
		while($row=sqlsrv_fetch_array($SQL)){?>
			<tr>
				<td><a href="#" onClick="ConsultarDireccion('<?php echo $row['NumeroLinea'];?>','B');" id="DirB_<?php echo $row['NumeroLinea'];?>"><i class="fa fa-home"></i> <?php echo $row['NombreSucursal'];?></a></td>
				<td id="DirBDireccion_<?php echo $row['NumeroLinea'];?>"><?php if($row['Direccion']!=""){?><i class="fa fa-map-marker"></i> <?php echo $row['Direccion'];?><?php }?></td>
				<td id="DirBCiudad_<?php echo $row['NumeroLinea'];?>"><?php if($row['Ciudad']!=""){?><i class="fa fa-university"></i> <?php echo $row['Ciudad'];?><?php }?></td>
				<td id="DirBContacto_<?php echo $row['NumeroLinea'];?>"><?php if($row['NombreContacto']!=""){?><i class="fa fa-user"></i> <?php echo $row['NombreContacto'];?><?php }?></td>
			</tr>
		<?php echo "<script>
			CargarDireccion(
			'".$row['NumeroLinea']."',
			'".$row['TipoDireccion']."',
			'".base64_encode($row['NombreSucursal'])."',
			'".$row['Direccion']."',
			'".$row['Departamento']."',
			'".$row['IdMunicipio']."',
			'".$row['IdBarrio']."',
			'".$row['Estrato']."',
			'".$row['CodigoPostal']."',
			'".$row['NombreContacto']."',
			'".$row['CargoContacto']."',
			'".$row['TelefonoContacto']."',
			'".$row['CorreoContacto']."',
			'".$row['DirContrato']."');
			</script>";}?>
		</tbody>
		<tbody id="listaDatosDirS">
			<tr><td colspan="4" class="bg-success"><i class="fa fa-list"></i> Direcciones de envío</td></tr>
		<?php
			$SQL=Seleccionar('uvw_Sap_tbl_SociosNegociosSucursales','*',"[CodigoCliente]='".$id."' and TipoDireccion='S'");
		while($row=sqlsrv_fetch_array($SQL)){?>
			<tr>
				<td><a href="#" onClick="ConsultarDireccion('<?php echo $row['NumeroLinea'];?>','S');" id="DirS_<?php echo $row['NumeroLinea'];?>"><i class="fa fa-home"></i> <?php echo $row['NombreSucursal'];?></a></td>
				<td id="DirSDireccion_<?php echo $row['NumeroLinea'];?>"><?php if($row['Direccion']!=""){?><i class="fa fa-map-marker"></i> <?php echo $row['Direccion'];?><?php }?></td>
				<td id="DirSCiudad_<?php echo $row['NumeroLinea'];?>"><?php if($row['Ciudad']!=""){?><i class="fa fa-university"></i> <?php echo $row['Ciudad'];?><?php }?></td>
				<td id="DirSContacto_<?php echo $row['NumeroLinea'];?>"><?php if($row['NombreContacto']!=""){?><i class="fa fa-user"></i> <?php echo $row['NombreContacto'];?><?php }?></td>
			</tr>
		<?php echo "<script>
			CargarDireccion(
			'".$row['NumeroLinea']."',
			'".$row['TipoDireccion']."',
			'".base64_encode($row['NombreSucursal'])."',
			'".$row['Direccion']."',
			'".$row['Departamento']."',
			'".$row['IdMunicipio']."',
			'".$row['IdBarrio']."',
			'".$row['Estrato']."',
			'".$row['CodigoPostal']."',
			'".$row['NombreContacto']."',
			'".$row['CargoContacto']."',
			'".$row['TelefonoContacto']."',
			'".$row['CorreoContacto']."',
			'".$row['DirContrato']."');
			</script>";}?>
		</tbody>
	</table>
</div>
</body>
</html>
<?php 
	sqlsrv_close($conexion);
?>