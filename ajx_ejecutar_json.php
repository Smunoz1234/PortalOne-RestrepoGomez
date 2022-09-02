<?php 
if((isset($_GET['type'])&&($_GET['type']!=""))||(isset($_POST['type'])&&($_POST['type']!=""))){
	require_once("includes/conexion.php");
	header('Content-Type: application/json');
	if(isset($_GET['type'])&&($_GET['type']!="")){
		$type=$_GET['type'];
	}else{
		$type=$_POST['type'];
	}
	if($type==1){//Regenerar procesos de creacion de clientes
		$records=array();
		$Parametros=array(
			'pID' => $_GET['id'],
			'pMetodo' => $_GET['metodo'],
			'pLogin'=> $_SESSION['User']
		);
		$Resultado=EnviarWebServiceSAP('AppPortal_RegenerarInsertarClientePortal_JSON',$Parametros,true);
		$records=array(
			'Estado' => $Resultado->Success,
			'Mensaje' => $Resultado->Mensaje,
			'Objeto' => $Resultado->Objeto,
			'Title' => ($Resultado->Success==1) ? "¡Listo!" : "¡Advertencia!",
			'Icon' => ($Resultado->Success==1) ? "success" : "error",
		);
		echo json_encode($records);
	}
	if($type==2){//Ejecutar WebServices en JSON para cierre de OT
		$records=array();
//		$ParametrosOld=array(
//			'pIdEvento' => $_GET['Evento'],
//			'pFechaInicial' => $_GET['FechaInicial'],
//			'pFechaFinal'=> $_GET['FechaFinal'],
//			'pSucursal' => $_GET['Sucursal'],
//			'pLogin' => $_SESSION['User'],
//			'pIdSerieOT' => $_GET['Serie']
//		);
		$Parametros=array(
			'id_evento' => intval($_GET['Evento']),
			'fecha_inicial' => $_GET['FechaInicial'],
			'fecha_final'=> $_GET['FechaFinal'],
			'sucursal' => $_GET['Sucursal'],
			'usuario' => $_SESSION['User'],
			'id_serie_OT' => intval($_GET['Serie']),
			'docentry_llamada' => 0,
			'mensaje' => "",
			'metodo' => 0
		);
		if($_GET['Tipo']=='1'){//Actividades
			$Param=array(
				"'".strtolower($_SESSION['User'])."'");
			$SQL=EjecutarSP('sp_tbl_CierreOTDetalleCarritoValidar',$Param);
			$row=sqlsrv_fetch_array($SQL);
			if($row['CantError']==0){
//				$Metodo="AppPortal_CrearCierreActividadesOrdenServicio";
				$Metodo="Actividades/CierreLote";
			}else{
				$records=array(
					'Estado' => 0,
					'Mensaje' => $row['MsjError'],
					'Title' => "¡Advertencia!",
					'Icon' => "error",
				);
				echo json_encode($records);
				exit();
			}			
		}else{//Llamadas de servicio
			$Param=array(
				"'".strtolower($_SESSION['User'])."'",
				2
			);
			$SQL=EjecutarSP('sp_tbl_CierreOTDetalleCarritoValidar',$Param);
			$row=sqlsrv_fetch_array($SQL);
			if($row['CantError']==0){
				$RutaAttachSAP=ObtenerDirAttach();
				$Param=array(
					"'".ObtenerVariable("RutaAnexosOT").strtolower($_SESSION['User'])."\'",
					"'".$RutaAttachSAP[0]."'");
				$SQL=EjecutarSP('usp_CopiarArchivosToSAP',$Param);
				$row=sqlsrv_fetch_array($SQL);
				if($row['CantError']==0){
					$Metodo="LlamadasServicios/CierreLote";
				}else{
					$records=array(
						'Estado' => 0,
						'Mensaje' => "No se pudieron copiar los anexos a SAP",
						'Title' => "¡Advertencia!",
						'Icon' => "error",
					);
					echo json_encode($records);
					exit();
				}
			}else{
				$records=array(
					'Estado' => 0,
					'Mensaje' => $row['MsjError'],
					'Title' => "¡Advertencia!",
					'Icon' => "error",
				);
				echo json_encode($records);
				exit();
			}
					
		}
		$Resultado=EnviarWebServiceSAP($Metodo,$Parametros,true,true);
		$records=array(
			'Estado' => $Resultado->Success,
			'Mensaje' => $Resultado->Mensaje,
			'Title' => ($Resultado->Success==1) ? "¡Listo!" : "¡Advertencia!",
			'Icon' => ($Resultado->Success==1) ? "success" : "error",
		);
		echo json_encode($records);
	}
	if($type==3){//Ejecutar WebServices en JSON para creacion de OT
		$records=array();
		if($_GET['Tipo']=='1'){
			$Parametros=array(
				'id_evento' => intval($_GET['Evento']),
				'id_periodo' => intval($_GET['Anno']),
				'fecha_inicial' => $_GET['FechaInicial'],
				'fecha_final'=> $_GET['FechaFinal'],
				'id_sede' => $_GET['Sucursal'],
				'id_socio_negocio' => $_GET['Cliente'],
				'usuario' => $_SESSION['User'],
				'id_serie_OT' => intval($_GET['SeriesOT']),
				'id_serie_OV' => intval($_GET['SeriesOV'])
			);
			$Metodo="LlamadasServicios/CreacionLote";
			$Resultado=EnviarWebServiceSAP($Metodo,$Parametros,true,true);
		}else{
			$Parametros=array(
				'pIdEvento' => $_GET['Evento'],
				'pPeriodo' => $_GET['Anno'],
				'pFechaInicial' => $_GET['FechaInicial'],
				'pFechaFinal'=> $_GET['FechaFinal'],
				'pSucursal' => $_GET['Sucursal'],
				'pIdCliente' => $_GET['Cliente'],
				'pLogin' => $_SESSION['User'],
				'pIdSerieOT' => $_GET['SeriesOT'],
				'pIdSerieOV' => $_GET['SeriesOV']
			);
			$Metodo="AppPortal_CrearProgramaOrdenesVentas";
			$Resultado=EnviarWebServiceSAP($Metodo,$Parametros,true);			
		}
		
		$records=array(
			'Estado' => $Resultado->Success,
			'Mensaje' => $Resultado->Mensaje,
			'Title' => ($Resultado->Success==1) ? "¡Listo!" : "¡Advertencia!",
			'Icon' => ($Resultado->Success==1) ? "success" : "error",
		);
		echo json_encode($records);
	}
	if($type==4){//Ejecutar WebServices en JSON para cambio de producto
		$records=array();
		$Parametros=array(
			'id_evento' => intval($_GET['Evento']),
			'fecha_inicial' => $_GET['FechaInicial'],
			'fecha_final'=> $_GET['FechaFinal'],
			'id_sede' => $_GET['Sucursal'],
			'id_usuario' => intval($_SESSION['CodUser']),
			'id_serie_OT' => intval($_GET['SeriesOT'])
		);
		$Metodo="OrdenesVentas/CambiosProductos";
		$Resultado=EnviarWebServiceSAP($Metodo,$Parametros,true,true);
		$records=array(
			'Estado' => $Resultado->Success,
			'Mensaje' => $Resultado->Mensaje,
			'Title' => ($Resultado->Success==1) ? "¡Listo!" : "¡Advertencia!",
			'Icon' => ($Resultado->Success==1) ? "success" : "error",
		);
		echo json_encode($records);
	}
	if($type==5){//Ejecutar WebServices en JSON para integracion de actividades en rutero
		$records=array();
		$Parametros=array(
			'id_usuario' => intval($_SESSION['CodUser']),
			'id_evento' => intval($_GET['IdEvento']),
			'metodo' => 0
		);
		$Metodo="Actividades/Rutas";
		$Resultado=EnviarWebServiceSAP($Metodo,$Parametros,true,true);
		$records=array(
			'Estado' => $Resultado->Success,
			'Mensaje' => $Resultado->Mensaje,
			'Title' => ($Resultado->Success==1) ? "¡Listo!" : "¡Advertencia!",
			'Icon' => ($Resultado->Success==1) ? "success" : "error",
		);
		echo json_encode($records);
	}
	if($type==6){//Ejecutar WebServices en API JSON NET CORE para la facturacion de proyectos
		$records=array();
		$Parametros=array(
			'id_evento' => intval($_GET['Evento']),
			'id_metodo' => 1,
			'usuario' => "".strtolower($_SESSION['User']).""
		);
		$Metodo="FacturasVentas";
		$Resultado=EnviarWebServiceSAP($Metodo,$Parametros,true,true);
		$records=array(
			'Estado' => $Resultado->Success,
			'Mensaje' => $Resultado->Mensaje,
			'Title' => ($Resultado->Success==1) ? "¡Listo!" : "¡Advertencia!",
			'Icon' => ($Resultado->Success==1) ? "success" : "error",
		);
		echo json_encode($records);
	}
	if($type==7){//Cambiar estado en formulario de temperatura
		$records=array();
		
		if($_GET['esArray']==true){
			$array=explode(",",$_GET['id']);
			$arrayID=array();
			
			foreach($array as $id){
				array_push($arrayID,array(
					$_GET['nomID'] => intval($id),
					'estado' => $_GET['estado'],
					'comentarios_cierre' => $_GET['comentarios'],
					'id_usuario_cierre' => intval($_SESSION['CodUser'])
				));
			}
			
			$Parametros=array(
				'Lote' => $arrayID,
				'id_usuario_cierre' => intval($_SESSION['CodUser'])
			);
			$Metodo=$_GET['frm']."/CierreLote";
			$Prot="POST";
		}else{
			$Parametros=array(
				'estado' => $_GET['estado'],
				'comentarios_cierre' => $_GET['comentarios'],
				'id_usuario_cierre' => intval($_SESSION['CodUser'])
			);
			$Metodo=$_GET['frm']."/".$_GET['id'];
			$Prot="PATCH";
		}
		
		$Resultado=EnviarWebServiceSAP($Metodo,$Parametros,true,true,$Prot);
		$records=array(
			'Estado' => $Resultado->Success,
			'Mensaje' => $Resultado->Mensaje,
			'Title' => ($Resultado->Success==1) ? "¡Listo!" : "¡Advertencia!",
			'Icon' => ($Resultado->Success==1) ? "success" : "error",
		);
		echo json_encode($records);
	}
	
	sqlsrv_close($conexion);
}
?>