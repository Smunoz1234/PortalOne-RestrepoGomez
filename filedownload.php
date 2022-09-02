<?php 
if(isset($_GET['file'])&&$_GET['file']!=""){
	require_once("includes/conexion.php");
	
	$file=base64_decode($_GET['file']);
	
	//Selecciono los datos del archivo
	if((!isset($_GET['dir']))&&(!isset($_GET['api']))){
		$Cons="Select * From uvw_tbl_archivos Where ID_Archivo='".$file."'";
		$SQL=sqlsrv_query($conexion,$Cons);
		$row=sqlsrv_fetch_array($SQL);
	}
	
	if(isset($_GET['dtype'])&&$_GET['dtype']!=""){//Indicar si lo está descargando desde documentos o informes (públicos)
		if($row['Usuario']!=$_SESSION['CodUser']){//Validar que el usuario que lo esta descargando no es el mismo que lo cargo
			//Busco los datos del usuario que cargo el archivo (para la copia)
			$ConUser="Select NombreUsuario, Email From uvw_tbl_Usuarios Where ID_Usuario='".$row['Usuario']."'";
			$SQLUser=sqlsrv_query($conexion,$ConUser);
			$rowUser=sqlsrv_fetch_array($SQLUser);

			//Verificar si el usuario ya ha descargado el archivo
			$ConsDown="EXEC sp_tbl_DescargaArchivos '".$_SESSION['CodUser']."','".$file."',1";
			$SQLDown=sqlsrv_query($conexion,$ConsDown);
			$rowDown=sqlsrv_fetch_array($SQLDown);

			if($rowDown['Result']==0){//Si nunca lo ha descargado, enviar el mail
				EnviarMail($_SESSION['EmailUser'],$_SESSION['NomUser'],2,"","",$rowUser['Email'],$rowUser['NombreUsuario'],$row['NombreCliente'],$row['ID_Sucursal'],$row['ID_Categoria'],$row['Comentarios'],$row['Archivo']);

			}
		}
		//Insertar el archivo descargado en la tabla de registros de descargas
		$ConsInst="EXEC sp_tbl_DescargaArchivos '".$_SESSION['CodUser']."','".$file."',2";
		sqlsrv_query($conexion,$ConsInst);
	}
	
	if(isset($_GET['dir'])&&$_GET['dir']!=""){//Saber si le paso la ruta del archivo por parámetro o si tomo la estandar
		$filename=base64_decode($_GET['dir']).$file;
		$NombreArchivo=$file;
	}elseif(isset($_GET['api'])&&$_GET['api']!=""){//Descargar un archivo proveniente de una API
		$Result=DescargarFileAPI($file);
		$dir_temp=CrearObtenerDirTemp();
		$filename=$dir_temp.$_SESSION['User'].'.pdf';
		file_put_contents($filename, $Result);
		$NombreArchivo=$_SESSION['User']."_".date('YmdHi').'.pdf';
	}else{
		$carp_archivos=ObtenerVariable("RutaArchivos");
		$filename = $_SESSION['BD']."/".$carp_archivos."/".$row['CardCode']."/".$row['ID_Categoria']."/".$row['Archivo'];
		$NombreArchivo=$row['Archivo'];
	}
		
	$size = filesize($filename);
	
	header("Content-Transfer-Encoding: binary"); 
	//header("Content-type: application/octet-stream");
	header('Content-type: application/pdf', true);
	header("Content-Type: application/force-download");
	header('Content-Disposition: attachment; filename="'.$NombreArchivo.'"');
	header("Content-Length: $size"); 
	readfile($filename);
	
	//echo $filename;
}




?>