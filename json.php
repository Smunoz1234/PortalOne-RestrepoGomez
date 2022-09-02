<?php 
$data=array(
'Nombre1'=>1,
'Nombre2'=>2,
'Nombre3'=>3
);

function mostrar($row){
	foreach($row as $clave => $valor){
		return("{$clave} => {$valor}");
	}
}

$c = array_map("mostrar", $data);
print_r($c);
?>
