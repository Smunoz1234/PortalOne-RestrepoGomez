<?php 
if(!isset($_GET['MM_Mail'])||$_GET['MM_Mail']==""){exit();}else{
	require("includes/conexion.php");
	require_once('mailer/PHPMailerAutoload.php');
	
	//instancio un objeto de la clase PHPMailer
	$mail = new PHPMailer(); // defaults to using php "mail()"
	$mail->CharSet = "UTF-8";
	$mail->Encoding = "quoted-printable"; 
	//indico a la clase que use SMTP
	$mail->isSMTP();
	//permite modo debug para ver mensajes de las cosas que van ocurriendo
	//$mail->SMTPDebug = 2;
	//Debo de hacer autenticación SMTP
	if($_GET['ReqAut']==1){
		$mail->SMTPAuth = true;
	}else{
		$mail->SMTPAuth = false;
	}	
	$mail->SMTPSecure = $_GET['TypeCon'];
	//indico el servidor de Gmail para SMTP
	$mail->Host = $_GET['Servidor'];
	//indico el puerto que usa Gmail
	$mail->Port = $_GET['Puerto'];
	//indico un usuario / clave de un usuario de gmail
	$mail->Username = $_GET['Usuario'];
	$mail->Password = base64_decode($_GET['Password']);
	$mail->SetFrom($_GET['Usuario'], NOMBRE_PORTAL);
	$mail->AddReplyTo($_GET['Usuario'], NOMBRE_PORTAL);
	
	$mail->Subject = "Mensaje de prueba de ".NOMBRE_PORTAL;
	$mail->MsgHTML("Mensaje de correo electrónico enviado automáticamente por ".NOMBRE_PORTAL." al comprobar la configuración de su cuenta.");
	//indico destinatario
	$address = $_GET['Usuario'];
	$mail->AddAddress($_GET['Usuario'], NOMBRE_PORTAL);
	//Añadir con copia
	//$mail->AddCC($email_1);
	if(!$mail->Send()){
		$InsertLog="Insert Into tbl_Log Values ('".date('Y-m-d H:i:s')."','".$_SESSION['CodUser']."','Error',50,'".$mail->ErrorInfo."')";
		sqlsrv_query($conexion,$InsertLog);
		echo $mail->ErrorInfo;
	}else{
		echo "MOK";
	}
	
	
}




?>