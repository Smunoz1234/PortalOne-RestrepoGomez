<?php
include "config.php" 

?>
<!DOCTYPE html>
<!-- saved from url=(0038)http://localhost/clientes/clientes.php -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>
    <body>
    	<table border="1" align="center"><tbody><tr><td colspan="8" align="center">LISTADO DE CLIENTES</td></tr>
    		<tr>
    			<td>Documento</td>
    			<td>Nombre</td>
    			<td>Celular</td>
    			<td>Telefono fijo</td>
    			<td>Correo electronico</td>
    			<td>Direccion</td>
    			<td colspan="2">Modificar</td>
    		</tr>
            <?php 
                $result = mysqli_query($link, "SELECT * FROM tb_clientes ");
                while ( $row = mysqli_fetch_array($result)){
                    $id = $row['id'];                
                    $t_documento = $row['t_documento'];
                    $n_completo = $row['n_completo'];?>
            <tr>
                <td><?php echo $t_documento ?></td>
                <td><?php echo $n_completo ?></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td><a href="index.php?id=<?php echo $id ?>">Editar</a></td>
                <td><a href="#">Borrar</a></td>
            </tr>   
            
            <?php } ?> 
</tbody></table></body></html>