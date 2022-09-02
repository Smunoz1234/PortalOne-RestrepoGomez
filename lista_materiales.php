<?php require_once("includes/conexion.php");
PermitirAcceso(1208);
$dt_LS=0;//sw para saber si vienen datos de la llamada de servicio. 0 no vienen. 1 si vienen.
$dt_OF=0;//sw para saber si vienen datos de una Oferta de venta.
$msg_error="";//Mensaje del error
$ItemCode=0;
$IdPortal=0;//Id del portal para las ordenes que fueron creadas en el portal, para eliminar el registro antes de cargar al editar

if(isset($_GET['id'])&&($_GET['id']!="")){//ID de la Lista de material (ItemCode)
	$ItemCode=base64_decode($_GET['id']);
}

if(isset($_POST['ItemCode'])&&($_POST['ItemCode']!="")){//Tambien el Id interno, pero lo envío cuando mando el formulario
	$ItemCode=base64_decode($_POST['ItemCode']);
	$IdEvento=base64_decode($_POST['IdEvento']);
}

if(isset($_POST['swError'])&&($_POST['swError']!="")){//Para saber si ha ocurrido un error.
	$sw_error=$_POST['swError'];
}else{
	$sw_error=0;
}

if(isset($_REQUEST['tl'])&&($_REQUEST['tl']!="")){//0 Si se está creando. 1 Se se está editando.
	$edit=$_REQUEST['tl'];
}else{
	$edit=0;
}

if(isset($_POST['P'])&&($_POST['P']!="")){//Grabar lista de materiales
	
	try{
		$Type=($_POST['tl']==1) ? 2 : 1;
		
		$ParametrosCabListaMateriales=array(
			"'".$_POST['ItemCode']."'",
			"'".base64_decode($_POST['IdEvento'])."'",
			"'".$_POST['ItemName']."'",
			"'".$_POST['Cantidad']."'",
			"'".$_POST['ListaPrecio']."'",
			"'".$_POST['TipoListaMat']."'",
			"'".$_POST['Proyecto']."'",
			"'".$_POST['OcrCode']."'",
			"'".$_POST['OcrCode2']."'",
			"'".$_POST['OcrCode3']."'",
			"'".$_POST['Cliente']."'",
			"'".$_POST['Sucursal']."'",
			"'".$_POST['CDU_Areas']."'",
			"'".$_POST['CDU_Servicios']."'",
			"'".$_POST['CodigoPlantilla']."'",
			"'".$_SESSION['CodUser']."'",
			"'".$_SESSION['CodUser']."'",
			$Type
		);
		$SQL_CabeceraListaMateriales=EjecutarSP('sp_tbl_ListaMateriales',$ParametrosCabListaMateriales,$_POST['P']);
		if($SQL_CabeceraListaMateriales){
			$ItemCode=$_POST['ItemCode'];
			$IdEvento=base64_decode($_POST['IdEvento']);
			
			//Consultar cabecera
			$SQL_json=Seleccionar("tbl_ListaMateriales",'*',"ItemCode='".$ItemCode."' and IdEvento='".$IdEvento."'");
			$row_json=sqlsrv_fetch_array($SQL_json);
			
			//Consultar detalle
			$SQL_det=Seleccionar("tbl_ListaMaterialesDetalle",'*',"Father='".$ItemCode."' and IdEvento='".$IdEvento."'",'ChildNum');
			
			$Detalle=array();
			
			while($row_det=sqlsrv_fetch_array($SQL_det)){
				
				array_push($Detalle,array(
					"id_lista_material" => $row_det['Father'],
					"id_linea" => intval($row_det['ChildNum']),
					"id_linea_visual_order" => intval($row_det['VisOrder']),
					"id_articulo" => $row_det['ItemCode'],
					"tipo_linea" => "".$row_det['Type']."",
					"cant_articulo" => number_format($row_det['Cantidad'],2),
					"id_bodega" => $row_det['WhsCode'],
					"precio_articulo" => intval($row_det['Precio']),
					"und_medida" => $row_det['UndMedida'],
					"metodo_emision" => $row_det['MetodoEmision'],
					"comentarios" => null,            
					"id_lista_precio" => intval($row_det['IdListaPrecio']),
					"dim1" => $row_det['OcrCode'],
					"dim2" => $row_det['OcrCode2'],
					"dim3" => $row_det['OcrCode3'],
					"dim4" => null,
					"dim5" => null,
					"id_proyecto" => $row_det['IdProyecto'],
					"CDU_id_servicio" => $row_det['CDU_IdServicio'],
					"CDU_id_metodo_aplicacion" => $row_det['CDU_IdMetodoAplicacion'],
					"CDU_id_tipo_plagas" => $row_det['CDU_IdTipoPlagas'],
					"CDU_areas_controladas" => $row_det['CDU_AreasControladas'],
					"CDU_cant_litros" => intval($row_det['CDU_CantLitros']),
					"metodo_linea" => intval($row_det['Metodo']),
					"metodo" => intval($row_json['Metodo'])
				));
			}
						
			$Cabecera=array(
				"id_lista_material" => strtoupper($row_json['ItemCode']),
				"lista_material" => strtoupper($row_json['ItemName']),
				"tipo_lista_material" => $row_json['TipoListaMat'],
				"cantidad" => number_format($row_json['Cantidad'],2),
				"dim1" => $row_json['OcrCode'],
				"dim2" => $row_json['OcrCode2'],
				"dim3" => $row_json['OcrCode3'],
				"dim4" => "",
				"dim5" => "",  
				"id_proyecto" => $row_json['IdProyecto'],
				"id_lista_precio" => intval($row_json['IdListaPrecio']),
				"id_bodega" => $row_json['ToWH'],
				"cantidad_prom_produccion" => intval($row_json['TamProduccion']),
				"CDU_id_socio_negocio" => $row_json['CDU_CodigoCliente'],
				"CDU_socio_negocio" => $row_json['CDU_NombreCliente'],
				"CDU_id_consecutivo_direccion" => intval($row_json['CDU_IdSucursalCliente']),
				"CDU_id_direccion_destino" => $row_json['CDU_SucursalCliente'],
				"CDU_servicios" => $row_json['CDU_Servicios'],
				"CDU_areas" => $row_json['CDU_Areas'],
				"id_plantilla_actividad" => $row_json['CDU_CodPlantilla'],
				"id_oportunidad_venta" => "",
				"id_documento" => "",
				"usuario_actualizacion" => $_SESSION['User'],
				"fecha_actualizacion" => ($row_json['FechaActualizacion']->format('Y-m-d')."T".$row_json['FechaActualizacion']->format('H:i:s')),
				"hora_actualizacion" => ($row_json['FechaActualizacion']->format('Y-m-d')."T".$row_json['FechaActualizacion']->format('H:i:s')),
				"seg_actualizacion" => intval($row_json['FechaActualizacion']->format('s')),
				"metodo" => intval($row_json['Metodo']),
				"lista_material_lineas" => $Detalle
			);
			
//			$Cabecera_json=json_encode($Cabecera);
//			echo $Cabecera_json;
//			exit();
			
			//Enviar datos al WebServices
			try{	
				if($_POST['tl']==0){//Creando
					$Metodo="ListasMateriales";
					$Resultado=EnviarWebServiceSAP($Metodo,$Cabecera,true,true);
				}else{//Editando
					$Metodo="ListasMateriales/".$ItemCode;
					$Resultado=EnviarWebServiceSAP($Metodo,$Cabecera,true,true,"PUT");
				}
				

				if($Resultado->Success==0){
					$sw_error=1;
					$msg_error=$Resultado->Mensaje;
					$Cabecera_json=json_encode($Cabecera);
				}else{
					$Msg=($_POST['tl']==1) ? "OK_LMTUpd" : "OK_LMTAdd";
					//sqlsrv_close($conexion);
					//header('Location:lista_materiales.php?id='.base64_encode($ItemCode).'&tl=1&a='.base64_encode($Msg));
					$edit=1;
					$_GET['a']=base64_encode($Msg);
				}
			}catch (Exception $e) {
				echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
			}		
			
			
		}else{
			$sw_error=1;
			$msg_error="Ha ocurrido un error al insertar la lista de materiales";
		}
	}catch (Exception $e){
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
	
}

if($edit==0&&$sw_error==0){
	$SQL_NewIdEvento=EjecutarSP('sp_ObtenerIdEvento');
	$row_NewIdEvento=sqlsrv_fetch_array($SQL_NewIdEvento);
	$IdEvento=$row_NewIdEvento[0];
}

if($edit==1&&$sw_error==0){
	
	$ParametrosLimpiar=array(
		"'".$ItemCode."'",
		"'".$_SESSION['CodUser']."'"
	);
	$LimpiarLista=EjecutarSP('sp_EliminarListaMateriales',$ParametrosLimpiar);
	
	$SQL_IdEvento=sqlsrv_fetch_array($LimpiarLista);
	$IdEvento=$SQL_IdEvento[0];
	
	//Lista de material
	$SQL=Seleccionar("tbl_ListaMateriales",'*',"ItemCode='".$ItemCode."' and IdEvento='".$IdEvento."'");
	$row=sqlsrv_fetch_array($SQL);
	
	$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","NombreSucursal, NumeroLinea","CodigoCliente='".$row['CDU_CodigoCliente']."'");

}

if($sw_error==1){
	
	//Lista de material
	$SQL=Seleccionar("tbl_ListaMateriales",'*',"ItemCode='".$ItemCode."' and IdEvento='".$IdEvento."'");
	$row=sqlsrv_fetch_array($SQL);
	
	$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","NombreSucursal, NumeroLinea","CodigoCliente='".$row['CDU_CodigoCliente']."'");

}

//Normas de reparto (centros de costos)
$SQL_CentroCosto=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=1');

//Normas de reparto (Unidad negocio)
$SQL_UnidadNegocio=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=2');

//Normas de reparto (Sede cliente)
$SQL_Sede=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=3');

//Tipo lista
$SQL_TipoLista=Seleccionar('tbl_TipoListaMateriales','*');

//Datos de dimensiones del usuario actual
$SQL_DatosEmpleados=Seleccionar('uvw_tbl_Usuarios','CentroCosto1, CentroCosto2, CentroCosto3',"ID_Usuario='".$_SESSION['CodUser']."'");
$row_DatosEmpleados=sqlsrv_fetch_array($SQL_DatosEmpleados);

//Lista de precios
$SQL_ListaPrecios=Seleccionar('uvw_Sap_tbl_ListaPrecios','*');

//Proyectos
$SQL_Proyecto=Seleccionar('uvw_Sap_tbl_Proyectos','*','','DeProyecto');

//Plantillas
$SQL_Plantilla=Seleccionar('uvw_tbl_PlantillaActividades','*');

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Lista de materiales | <?php echo NOMBRE_PORTAL;?></title>
<?php 
if(isset($_GET['a'])&&$_GET['a']==base64_encode("OK_LMTAdd")){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'La Lista de materiales ha sido creada exitosamente.',
				icon: 'success'
			});
		});	
		</script>";
}
if(isset($_GET['a'])&&$_GET['a']==base64_encode("OK_LMTUpd")){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
				title: '¡Listo!',
				text: 'La Lista de materiales ha sido actualizada exitosamente.',
				icon: 'success'
			});
		});
		</script>";
}
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Advertencia!',
                text: `".LSiqmlObs($msg_error)."`,
                icon: 'warning'
            });
		});
		console.log('json:','$Cabecera_json');
		</script>";
}
?>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.panel-body{
		padding: 0px !important;
	}
	.tabs-container .panel-body{
		padding: 0px !important;
	}
	.nav-tabs > li > a{
		padding: 14px 20px 14px 25px !important;
	}
</style>
<script>
function BuscarArticulo(dato){
	var itemcode = document.getElementById("ItemCode").value;
	var lista_precio = document.getElementById("ListaPrecio").value;
	var proyecto = document.getElementById("Proyecto").value;
	var ocrcode = document.getElementById("OcrCode").value;
	var ocrcode2 = document.getElementById("OcrCode2").value;
	var ocrcode3 = document.getElementById("OcrCode3").value;
	var posicion_x; 
	var posicion_y;  
	posicion_x=(screen.width/2)-(1200/2);  
	posicion_y=(screen.height/2)-(500/2);
	if(dato!=""){
		if(itemcode!=""){
			remote=open('buscar_articulo.php?dato='+dato+'&idlistamaterial='+btoa(itemcode)+'&evento=<?php if($edit==1){echo base64_encode($row['IdEvento']);}else{echo base64_encode($IdEvento);}?>&lista_precio='+btoa(lista_precio)+'&proyecto='+btoa(proyecto)+'&ocrcode='+btoa(ocrcode)+'&ocrcode2='+btoa(ocrcode2)+'&ocrcode3='+btoa(ocrcode3)+'&tipodoc=3&doctype=17&todosart=1','remote',"width=1200,height=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=no,fullscreen=no,directories=no,status=yes,left="+posicion_x+",top="+posicion_y+"");
			remote.focus();
		}else{
			Swal.fire({
				title: "¡Advertencia!",
				text: "Debe seleccionar un artículo",
				icon: "warning",
				confirmButtonText: "OK"
			});
		}	
	}			
}
function ConsultarDatosCliente(){
	var Cliente=document.getElementById('CardCode');
	if(Cliente.value!=""){
		self.name='opener';
		remote=open('socios_negocios.php?id='+Base64.encode(Cliente.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}
function ConsultarPlantilla(){
	var CodigoPlantilla=document.getElementById('CodigoPlantilla');
	if(CodigoPlantilla.value!=""){
		self.name='opener';
		remote=open('plantilla_actividades.php?id='+btoa(CodigoPlantilla.value)+'&tl=1&ext=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}
</script>
<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#NombreCliente").change(function(){
			var NomCliente=document.getElementById("NombreCliente");
			var Cliente=document.getElementById("Cliente");
			if(NomCliente.value==""){
				Cliente.value="";
				$("#Cliente").trigger("change");
			}	
		});
		$("#Cliente").change(function(){
			var Cliente=document.getElementById("Cliente");
			$.ajax({
				type: "POST",
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Cliente.value+"&sucline=1&selec=1&todos=0",
				success: function(response){
					$('#Sucursal').html(response);
					$("#Sucursal").trigger("change");
				}
			});	
		});
		
	});
</script>
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include_once("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Lista de materiales</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Datos maestros</a>
                        </li>
                        <li class="active">
                            <strong>Lista de materiales</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
		 <div class="ibox-content">
			 <?php include("includes/spinner.php"); ?>
          <div class="row"> 
           <div class="col-lg-12">
              <form action="lista_materiales.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="frmListaMateriales">
				<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-info-circle"></i> Información de la lista de materiales</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Código <span class="text-danger">*</span></label>
					<div class="col-lg-3">
                    	<input type="text" name="ItemCode" id="ItemCode" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['ItemCode'];}?>" <?php if($edit==1){echo "readonly";}?> required>
               	  	</div>
					<label class="col-lg-1 control-label">Descripción <span class="text-danger">*</span></label>
					<div class="col-lg-3">
                    	<input type="text" name="ItemName" id="ItemName" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['ItemName'];}?>" required>
               	  	</div>
					<label class="col-lg-1 control-label">Cantidad <span class="text-danger">*</span></label>
					<div class="col-lg-3">
                    	<input type="text" name="Cantidad" id="Cantidad" class="form-control" value="<?php if($edit==1||$sw_error==1){echo number_format($row['Cantidad'],2);}?>" required>
               	  	</div>					
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Tipo lista de material <span class="text-danger">*</span></label>
					<div class="col-lg-3">
                    	<select name="TipoListaMat" class="form-control" id="TipoListaMat" required>
                          <?php while($row_TipoLista=sqlsrv_fetch_array($SQL_TipoLista)){?>
								<option value="<?php echo $row_TipoLista['TipoListaMat'];?>" <?php if(($edit==1||$sw_error==1)&&(isset($row['TipoListaMat']))&&(strcmp($row_TipoLista['TipoListaMat'],$row['TipoListaMat'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoLista['DeTipoListaMat'];?></option>
						  <?php }?>
						</select>
               	  	</div>
					<label class="col-lg-1 control-label">Lista de precios <span class="text-danger">*</span></label>
					<div class="col-lg-3">
                    	<select name="ListaPrecio" class="form-control" id="ListaPrecio" required>
                          <?php while($row_ListaPrecios=sqlsrv_fetch_array($SQL_ListaPrecios)){?>
								<option value="<?php echo $row_ListaPrecios['IdListaPrecio'];?>" <?php if(($edit==1||$sw_error==1)&&(isset($row['IdListaPrecio']))&&(strcmp($row_ListaPrecios['IdListaPrecio'],$row['IdListaPrecio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ListaPrecios['DeListaPrecio'];?></option>
						  <?php }?>
						</select>
               	  	</div>
					<label class="col-lg-1 control-label">Proyecto <span class="text-danger">*</span></label>
					<div class="col-lg-3">
						<select name="Proyecto" class="form-control select2" id="Proyecto" required>
								<option value="">Seleccione...</option>
						  <?php while($row_Proyecto=sqlsrv_fetch_array($SQL_Proyecto)){?>
								<option value="<?php echo $row_Proyecto['IdProyecto'];?>" <?php if(($edit==1||$sw_error==1)&&(isset($row['IdProyecto']))&&(strcmp($row_Proyecto['IdProyecto'],$row['IdProyecto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Proyecto['DeProyecto'];?></option>
						  <?php }?>
						</select>
					</div>			
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Centro de costo <span class="text-danger">*</span></label>
					<div class="col-lg-3">
                    	<select name="OcrCode2" class="form-control" id="OcrCode2" required="required">
							<option value="">Seleccione...</option>
                          <?php while($row_UnidadNegocio=sqlsrv_fetch_array($SQL_UnidadNegocio)){?>
									<option value="<?php echo $row_UnidadNegocio['OcrCode'];?>" <?php if(($edit==1||$sw_error==1)&&(isset($row['OcrCode2']))&&(strcmp($row_UnidadNegocio['OcrCode'],$row['OcrCode2'])==0)){echo "selected=\"selected\"";}elseif(($edit==0)&&($row_DatosEmpleados['CentroCosto2']!="")&&(strcmp($row_DatosEmpleados['CentroCosto2'],$row_UnidadNegocio['OcrCode'])==0)){echo "selected=\"selected\"";}?>><?php echo $row_UnidadNegocio['OcrName'];?></option>
							<?php 	}?>
						</select>
               	  	</div>
					<label class="col-lg-1 control-label">Área <span class="text-danger">*</span></label>
					<div class="col-lg-3">
						<select name="OcrCode" class="form-control" id="OcrCode" required="required">
							<option value="">Seleccione...</option>
						  <?php while($row_CentroCosto=sqlsrv_fetch_array($SQL_CentroCosto)){?>
								<option value="<?php echo $row_CentroCosto['OcrCode'];?>" <?php if(($edit==1||$sw_error==1)&&(isset($row['OcrCode']))&&(strcmp($row_CentroCosto['OcrCode'],$row['OcrCode'])==0)){echo "selected=\"selected\"";}elseif(($edit==0)&&($row_DatosEmpleados['CentroCosto1']!="")&&(strcmp($row_DatosEmpleados['CentroCosto1'],$row_CentroCosto['OcrCode'])==0)){echo "selected=\"selected\"";}?>><?php echo $row_CentroCosto['OcrName'];?></option>
						  <?php }?>
						</select>
					</div>
					<label class="col-lg-1 control-label">Sede <span class="text-danger">*</span></label>
					<div class="col-lg-3">
                    	<select name="OcrCode3" class="form-control" id="OcrCode3" required="required">
							<option value="">Seleccione...</option>
						  <?php while($row_Sede=sqlsrv_fetch_array($SQL_Sede)){?>
								<option value="<?php echo $row_Sede['OcrCode'];?>" <?php if(($edit==1||$sw_error==1)&&(isset($row['OcrCode3']))&&(strcmp($row_Sede['OcrCode'],$row['OcrCode3'])==0)){echo "selected=\"selected\"";}elseif(($edit==0)&&($row_DatosEmpleados['CentroCosto3']!="")&&(strcmp($row_DatosEmpleados['CentroCosto3'],$row_Sede['OcrCode'])==0)){echo "selected=\"selected\"";}?>><?php echo $row_Sede['OcrName'];?></option>
						  <?php }?>
						</select>
               	  	</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-tags"></i> Información adicional</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Cliente <span class="text-danger">*</span></label>
					<div class="col-lg-3">
						<input name="Cliente" type="hidden" id="Cliente" value="<?php if(($edit==1)||($sw_error==1)){ echo $row['CDU_CodigoCliente'];}?>">
						<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Escribar para buscar..." value="<?php if(($edit==1)||($sw_error==1)){ echo $row['CDU_NombreCliente'];}?>" required>
					</div>
					<label class="col-lg-1 control-label">Sucursal cliente <span class="text-danger">*</span></label>
					<div class="col-lg-3">
					 <select id="Sucursal" name="Sucursal" class="form-control select2" required>
						<option value="">Seleccione...</option>
						<?php 
						 if(($edit==1)||($sw_error==1)){
							 while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
								<option value="<?php echo $row_Sucursal['NumeroLinea'];?>" <?php if(strcmp($row_Sucursal['NumeroLinea'],$row['CDU_IdSucursalCliente'])==0){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['NombreSucursal'];?></option>
						<?php }
						 }?>
					</select>
					</div>
					<label class="col-lg-1 control-label"><i onClick="ConsultarPlantilla();" title="Consultar plantilla" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Plantilla de actividades</label>
					<div class="col-lg-3">
					 <select id="CodigoPlantilla" name="CodigoPlantilla" class="form-control select2">
						<option value="">Seleccione...</option>
						<?php 
							 while($row_Plantilla=sqlsrv_fetch_array($SQL_Plantilla)){?>
								<option value="<?php echo $row_Plantilla['CodigoPlantilla'];?>" <?php if(strcmp($row_Plantilla['CodigoPlantilla'],$row['CDU_CodPlantilla'])==0){ echo "selected=\"selected\"";}?>><?php echo $row_Plantilla['CodigoPlantilla']." - ".$row_Plantilla['Descripcion'];?></option>
						<?php }?>
					</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Servicios</label>
					<div class="col-lg-3">
						<textarea name="CDU_Servicios" rows="5" class="form-control" id="CDU_Servicios" type="text"><?php if(($edit==1)||($sw_error==1)){echo $row['CDU_Servicios'];}?></textarea>
					</div>
					<label class="col-lg-1 control-label">Áreas</label>
					<div class="col-lg-3">
						<textarea name="CDU_Areas" rows="5" class="form-control" id="CDU_Areas" type="text"><?php if(($edit==1)||($sw_error==1)){echo $row['CDU_Areas'];}?></textarea>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Contenido de la lista</h3></label>
				</div>
				<div class="form-group">
					<label class="col-lg-1 control-label">Buscar articulo</label>
					<div class="col-lg-4">
                    	<input name="BuscarItem" id="BuscarItem" type="text" class="form-control" placeholder="Escriba para buscar..." onBlur="javascript:BuscarArticulo(this.value);">
               	  	</div>
				</div>
				<div class="tabs-container">  
					<ul class="nav nav-tabs">
						<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Contenido</a></li>					
						<li><span class="TimeAct"><div id="TimeAct">&nbsp;</div></span></li>
						<span class="TotalItems"><strong>Total Items:</strong>&nbsp;<input type="text" name="TotalItems" id="TotalItems" class="txtLimpio" value="0" size="1" readonly></span>
					</ul>
					<div class="tab-content">
						<div id="tab-1" class="tab-pane active">
							<iframe id="DataGrid" name="DataGrid" style="border: 0;" width="100%" height="300" src="<?php if($edit==0&&$sw_error==0){echo "detalle_lista_materiales.php";}else{echo "detalle_lista_materiales.php?id=".base64_encode($row['ItemCode'])."&evento=".base64_encode($row['IdEvento'])."&type=2";}?>"></iframe>
						</div>
					</div>
				</div>				
				<div class="form-group m-t-xl">
					<div class="col-lg-8">
						<?php if($edit==0&&PermitirFuncion(1209)){?>
							<button class="btn btn-primary" type="submit" form="frmListaMateriales" id="Crear"><i class="fa fa-check"></i> Crear lista de materiales</button>
						<?php }else{?>
							<button class="btn btn-warning" type="submit" form="frmListaMateriales" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar lista de materiales</button>
						<?php }?>
						<?php 
//							
							if(isset($_GET['return'])){
								$return=base64_decode($_GET['pag'])."?".base64_decode($_GET['return']);
							}elseif(isset($_POST['return'])){
								$return=base64_decode($_POST['return']);
							}else{
								$return="consultar_lista_materiales.php";
							}
							$return=QuitarParametrosURL($return,array("a"));
						?>
						<a href="<?php echo $return;?>" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
					</div>
					<div class="col-lg-4">
						<div class="form-group">
							<label class="col-lg-7"><strong class="pull-right">Total</strong></label>
							<div class="col-lg-5">
								<input type="text" name="Total" form="frmListaMateriales" id="Total" class="form-control" style="text-align: right; font-weight: bold;" value="0.00" readonly>
							</div>
						</div>
					</div>
				</div>
				<input type="hidden" id="P" name="P" value="66" />
				<input type="hidden" id="IdEvento" name="IdEvento" value="<?php echo base64_encode($IdEvento);?>" />
				<input type="hidden" id="tl" name="tl" value="<?php echo $edit;?>" />
				<input type="hidden" id="swError" name="swError" value="<?php echo $sw_error;?>" />
				<input type="hidden" id="return" name="return" value="<?php echo base64_encode($return);?>" />
			 </form>
		   </div>
			</div>
          </div>
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>
	 $(document).ready(function(){
		 $("#frmListaMateriales").validate({
			 submitHandler: function(form){
				 Swal.fire({
					title: "¿Está seguro que desea guardar los datos?",
					icon: "question",
					showCancelButton: true,
					confirmButtonText: "Si, confirmo",
					cancelButtonText: "No"
				}).then((result) => {
					if (result.isConfirmed) {
						$('.ibox-content').toggleClass('sk-loading',true);
						form.submit();
					}
				});
			}
		 });
		 
		 $(".alkin").on('click', function(){
				 $('.ibox-content').toggleClass('sk-loading');
			});


		 $(".select2").select2();
		 
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
					  var value = $("#NombreCliente").getSelectedItemData().CodigoCliente;
					  $("#Cliente").val(value).trigger("change");
				  }
			  }
		 };
		$("#NombreCliente").easyAutocomplete(options);
		
	});
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd -->
</html>
<?php sqlsrv_close($conexion);?>