<?php
	include 'config.php';
	if (empty($_POST['r_social'])){
		$r_social = "";
	}else{
		$r_social = $_POST["r_social"];
	}
	if (empty($_POST['nit'])){
		$nit = "";
	}else{
		$nit = $_POST["nit"];
	}
	// if (empty($_POST["t_documento"])) {
	    $sql ="INSERT INTO `tb_clientes`(`t_cliente`, `t_documento`, `documento`, `r_social`, `nit`, `nombres`, `p_apellido`, `s_apellido`, `n_completo`, `t_fijo`, `t_celular`, `a_economica`, `c_electronico`, `direccion`, `departamento`, `municipio`, `barrio`, `estrato`, `r_anexo`) VALUES ('" . $_POST["t_cliente"] . "','" . $_POST["t_documento"] . "','" . $_POST["documento"] . "','$r_social','$nit','" . $_POST["nombres"] . "','" . $_POST["p_apellido"] . "','" . $_POST["s_apellido"] . "','" . $_POST["n_completo"] . "','" . $_POST["t_fijo"] . "','" . $_POST["t_celular"] . "','" . $_POST["a_economica"] . "','" . $_POST["c_electronico"] . "','" . $_POST["direccion"] . "','" . $_POST["depto"] . "','" . $_POST["muni"] . "','" . $_POST["barrio"] . "','" . $_POST["estrato"] . "','" . $_FILES['r_anexo']['name'] . "')";
// 	}else{
// 	 	$sql ="UPDATE `tb_clientes` SET `t_cliente`='" . $_POST["t_cliente"] . "',`t_documento`='" . $_POST["t_documento"] . "',`documento`='" . $_POST["documento"] . "',`r_social`='$r_social',`nit`='$nit',`nombres`='" . $_POST["nombres"] . "',`p_apellido`='" . $_POST["p_apellido"] . "',`s_apellido`='" . $_POST["s_apellido"] . "',`n_completo`='" . $_POST["n_completo"] . "',`t_fijo`='" . $_POST["t_fijo"] . "',`t_celular`='" . $_POST["t_celular"] . "',`a_economica`='" . $_POST["a_economica"] . "',`c_electronico`='" . $_POST["c_electronico"] . "',`direccion`='" . $_POST["direccion"] . "',`departamento`='" . $_POST["depto"] . "',`municipio`='" . $_POST["muni"] . "',`barrio`='" . $_POST["barrio"] . "',`estrato`='" . $_POST["estrato"] . "',`r_anexo`='" . $_FILES['r_anexo']['name'] . "' WHERE `documento`= '" . $_POST["t_documento"] . "'";
// }
	
	
	$consulta = $link->query($sql);
	//move_uploaded_file( "avocado" , "/uploads" );
	//$update= $consulta->fetch_object();
	// if ($link->query($sql) === TRUE) {
	// 	echo "OK";}
	// else{
	//  	echo "ERROR";}
 	header('Location: index.php');

	 	


//$ext_type = array('pdf');

?>