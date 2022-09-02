<!DOCTYPE html>
<?php
	include 'config.php';

	 $result = mysqli_query($link, "SELECT * FROM tb_clientes where id = $_GET[id] ");
   if ( $row = mysqli_fetch_array($result)){  
                
                 $id           = $row['id'];                
                 $t_documento           = $row['t_documento'];
                 $n_completo           = $row['n_completo'];    
    }
?>
<html>
    <head></head>
    <body>
    	<script>
	    	function obtenerMuni(val){
				$.ajax({
		    		type: "POST",
		    		url: "get_muni.php",
		    		data:'id_depto='+val,
		    		success: function(data){
						$("#lista_muni").html(data);
		    		}
		    	});	
			}
			function ocultarInput(val){
				if (val=="n"){
					document.getElementById('rs').disabled = true;
					document.getElementById('nt').disabled = true;
					document.getElementById('nm').disabled = false;
					document.getElementById('pa').disabled = false;
					document.getElementById('sa').disabled = false;
				}
				else if (val=="j"){
					document.getElementById('rs').disabled = false;
					document.getElementById('nt').disabled = false;
					document.getElementById('nm').disabled = false;
					document.getElementById('pa').disabled = false;
					document.getElementById('sa').disabled = false;
				}
			}
			function nCompleto(val){
				document.getElementById("nc").value=document.getElementById("nm").value+" "+document.getElementById("pa").value+" "+document.getElementById("sa").value;
			}
		</script>
		<div align="center">
	    	<form enctype="multipart/form-data" action="guardar.php" method="POST">
	    		<table><tr><td>Tipo de cliente:</td>
	    			<td><select name="t_cliente" required onChange="ocultarInput(this.value)" style="width:177px">
		   						<option value="0">Seleccione...</option> 
						 		<option value="n">Persona Natural</option> 
								<option value="j">Persona Juridica</option>
							</select></td></tr>
					<tr><td>Razon social</td>
						<td><input type="text" name="r_social" id="rs" disabled required></td></tr>
					<tr><td>Nit</td>
						<td><input type="text" name="nit" id="nt"  disabled required></td></tr>
					<tr><td>Tipo de documeto</td>
						<td><select name="t_documento" style="width:177px">
		   						<option value="0">Seleccione...</option> 
						 		<option value="cc">Cedula de Ciudadania</option> 
								<option value="nit">Nit</option>
							</select></td></tr>
					<tr><td>Documento</td>
						<td><input type="text" name="documento" id="doc" required></td></tr>
					<tr><td>Nombres</td>
						<td><input type="text" name="nombres" id="nm" disabled required onChange="nCompleto(this.value)"></td></tr>
					<tr><td>Primer apellido</td>
						<td><input type="text" name="p_apellido" id="pa" disabled required onChange="nCompleto(this.value)"></td></tr>
					<tr><td>Segunto apellido</td>
						<td><input type="text" name="s_apellido" id="sa" disabled required onChange="nCompleto(this.value)"></td></tr>
					<tr><td>Nombre Completo</td>
						<td><input type="text" name="n_completo" id="nc" value=" <?php echo $n_completo; ?>" required onChange="nCompleto(this.value)"></td></tr>
					<tr><td>Telefono fijo</td>
						<td><input type="text" name="t_fijo" required></td></tr>
						<tr><td>Celular</td>
						<td><input type="text" name="t_celular" required></td></tr>
					<?php
				    	$consulta_economica = $link->query("select descripcion as 'descripcion' from tb_economica order by descripcion");
					?>
					<tr><td>Actividad economica</td>
						<td><select name="a_economica" style="width:177px">
								<option value=''>Seleccionar actividad economica...</option>
									<?php while($row= $consulta_economica->fetch_object()){
											echo "<option value='".$row->valor."'>".$row->descripcion."</option>";}?></select></td></tr>
					<tr><td>Correo electronico</td>
						<td><input type="text" name="c_electronico" required><br></td></tr>
					<?php
				    	$consulta_depto = $link->query("select id as 'valor', descripcion as 'descripcion' from depto order by descripcion");
				    	$consulta_muni = $link->query("select id as 'valor', descripcion as 'descripcion' from tb_muni order by descripcion");
					?>
					<tr><td>Departamentos</td>
						<td><select name="depto" id="lista_depto" required onChange="obtenerMuni(this.value);" style="width:177px">
								<option value=''>Seleccionar Departamento</option> 
									<?php while($row= $consulta_depto->fetch_object()){
											echo "<option value='".$row->valor."'>".$row->descripcion."</option>";}?></select></td></tr>
					<tr><td>Municipios</td>
						<td><select name="muni" id="lista_muni" required style="width:177px">
								<option value=''>Seleccionar Municipio</option>
									<?php while($row= $consulta_muni->fetch_object()){
										   echo "<option value='".$row->valor."'>".$row->descripcion."</option>";}?></select></td></tr>
					<tr><td>Direccion</td>
						<td><input type="text" name="direccion" required></td></tr>
					<tr><td>Barrio</td>
						<td><input type="text" name="barrio" required></td></tr>
					<tr><td>Estrato</td>
						<td><select name="estrato" required>
				   				<option value="0">0</option>
				   				<option value="1">1</option> 
								<option value="2">2</option> 
								<option value="3">3</option>
								<option value="4">4</option> 
								<option value="5">5</option> 
								<option value="6">6</option>
								<option value="7">7</option> 
								<option value="8">8</option> 
								<option value="9">9</option> 
								<option value="10">10</option></select></td></tr>
					<tr><td>Certificado</td>
						<td><input type="file" name="r_anexo" accept=".pdf"></td></tr>
					<tr><td align="right"><input type="submit" value="Guardar"></td>
						<td><input type="reset" value="Borrar"></td></tr>
					<tr><td align="center">
							<a href="clientes.php">Listado de Clientes</a>
						</td></tr>
		    		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		    	</table>
			</form>
		</div>
    </body>
</html>