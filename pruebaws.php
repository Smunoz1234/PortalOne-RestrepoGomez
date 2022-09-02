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

$IdEntradaCompra='97';
$IdEvento='5404';


$Metodo="EntradasCompras";

$result=EnviarWebServiceSAP($Metodo,$Cabecera,true,true,"PUT");

//$result=AuthJWT("jgeronimog","1234");
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