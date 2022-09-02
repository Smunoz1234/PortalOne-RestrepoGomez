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
	$SQL=Seleccionar('uvw_Sap_tbl_SociosNegociosContactos','*',"[CodigoCliente]='".$id."'");
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
function ConsultarContacto(ID){
	$('.ibox-content', window.parent.document).toggleClass('sk-loading',true);
	//var frame=window.parent.document.getElementById('frameCtcDetalle');
	$.ajax({
		type: "POST",
		url: "sn_contactos_detalle.php?edit=<?php echo base64_encode($edit);?>&id=<?php if($edit==1){echo base64_encode($id);}?>&cod="+btoa(ID),
		success: function(response){
			$('#frameCtcDetalle', window.parent.document).html(response);
			//$('#CodigoPostal'+id).trigger('change');
			$('.ibox-content', window.parent.document).toggleClass('sk-loading',false);
		}
	});
	//frame.src="sn_contactos_detalle.php?edit=<?php echo base64_encode($edit);?>&id=<?php if($edit==1){echo base64_encode($id);}?>&cod="+btoa(ID);
	
//	$('.ibox-content').toggleClass('sk-loading',false);
}
	
function CrearContacto(){
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
	
	json[0].contactos.push({
		cod_contacto: newCod,
		primer_nombre: "",
		segundo_nombre: "",
		apellidos: "",
		cedula: "",
		telefono: "",
		celular: "",
		act_economica: "",
		rep_legal: "NO",
		email: "",
		posicion: "",
		grupo_correo: "",
		estado: "Y",
		metodo: 1
	})
	window.sessionStorage.setItem('<?php echo $id;?>',JSON.stringify(json))
	
	let html = document.implementation.createHTMLDocument()
	let tr = html.createElement("tr");
	tr.innerHTML=`<td><a href="#" onClick="ConsultarContacto('${newCod}');" id="Ctc_${newCod}"><i class="fa fa-user"></i> Nuevo contacto</a></td>
				<td id="CtcTel_${newCod}"></td>
				<td id="CtcCargo_${newCod}"></td>
				<td><span class="label label-primary">Activo</span></td>`;
	let tbody=document.getElementById("listaDatos")
	tbody.appendChild(tr);

}
</script>
</head>

<body>
<div class="table-responsive">
	<button type="button" onClick="CrearContacto();" class="btn btn-primary m-sm"><i class="fa fa-plus-circle"></i> Agregar contacto</button>
	<table class="table table-striped table-hover">
		<tbody id="listaDatos">
		<?php
			if($edit==1){
		while($row=sqlsrv_fetch_array($SQL)){?>
			<tr>
				<td><a href="#" onClick="ConsultarContacto('<?php echo $row['CodigoContacto'];?>');" id="Ctc_<?php echo $row['CodigoContacto'];?>"><i class="fa fa-user"></i> <?php echo $row['ID_Contacto'];?></a></td>
				<td id="CtcTel_<?php echo $row['CodigoContacto'];?>"><?php if($row['Telefono1']!=""){?><i class="fa fa-phone"></i> <?php echo $row['Telefono1'];?><?php }?></td>
				<td id="CtcCargo_<?php echo $row['CodigoContacto'];?>"><?php if($row['Posicion']!=""){?><i class="fa fa-tag"></i> <?php echo $row['Posicion'];?><?php }?></td>
				<td><span class="label <?php if($row['Estado']=='Y'){echo "label-primary";}else{echo "label-danger";}?>"><?php if($row['Estado']=='Y'){echo "Activo";}else{echo "Inactivo";}?></span></td>
			</tr>
		<?php }
			}?>
		</tbody>
	</table>
</div>
</body>
</html>
<?php 
	sqlsrv_close($conexion);
?>