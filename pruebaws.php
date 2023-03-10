<?php 
require_once("includes/conexion.php");
//header('Content-Type: application/json');

//$Metodo='OrdenesVentas';

//$IdOrdenVenta="578";
//$IdEvento="1380";

//$Parametros=array(
//	'id_documento' => intval($IdOrdenVenta),
//	'id_evento' => intval($IdEvento)
//);
//$result=EnviarWebServiceSAP($Metodo,$Parametros,true,true);

/*$Parametros=array(
	'pIdEvento' => '139',
	'pPeriodo' => '2020',
	'pFechaInicial' => '2020-08-29',
	'pFechaFinal'=> '2020-09-13',
	'pSucursal' => '301',
	'pIdCliente' => 'CN-890107487',
	'pLogin' => 'aordonez',
	'pIdSerieOT' => '132',
	'pIdSerieOV' => '133'
);

$Metodo="AppPortal_CrearProgramaOrdenServicio";*/

$Parametros=array(
	'usuario' => "jgeronimog",
	'password' => "1234",
	'app' => "ServiceOne",
	'version_app' => "2.0"
);
$Metodo="Login";

//$result=EnviarWebServiceSAP($Metodo,$Parametros,true,true);

$result=AuthJWT("jgeronimog","1234");
//$result=AuthJWT("aordonez","123");

echo "<pre>";		
print_r($result);
//$result=json_decode($result);
//print_r(json_encode($result));
//print_r(json_decode($result));
//echo "<br>";
//echo "Success: ".$result->Success;
//echo "<br>";
//echo "Mensaje: ".$result->Mensaje;
//echo "<br>";
//foreach($result->Objeto as $Objeto){
//	echo "NoObjeto: ".$Objeto->NoObjeto;
//	echo "<br>";
//	echo "TipoObjeto: ".$Objeto->TipoObjeto;
//	echo "<br>";
//	echo "<br>";
//}
//echo "JWT_Token:".$result->Objeto->sesion->token;
echo "</pre>";
/*
$datos='{"rates": {"AED": 3.673014,"AFN": 68.343295,"ALL": 115.9367,"AMD": 479.122298}}';
print_r($datos);

    #No le pasamos el parámetro TRUE porque podemos trabajarlo como JSON  
    $jsonObject = json_decode($datos);
    echo "------- Sólo valores -------\n\n";
    foreach ($jsonObject->rates as $v){
        echo "$v\n";
    }

    echo "\n\n------- Valores y claves -------\n\n";
    foreach ($jsonObject->rates as $k=>$v){
        echo "$k : $v\n";
    }
*/
?>