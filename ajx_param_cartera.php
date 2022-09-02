<?php 
if(isset($_POST['type'])&&($_POST['type']!="")){
	require_once("includes/conexion.php");
	
	if($_POST['type']==1){//Tipo de gestion, debe mostrar los tipos de eventos
		$SQL_TipoGestion=Seleccionar("tbl_Cartera_TipoGestion","*","ID_TipoGestion='".$_POST['id']."'");
		$row_TipoGestion=sqlsrv_fetch_array($SQL_TipoGestion);
		
		$SQL_Evento=Seleccionar("tbl_Cartera_Evento","*","ID_TipoGestion='".$_POST['id']."'");
	?>
		<h3>Tipos de eventos de <strong><?php echo $row_TipoGestion['TipoGestion']; ?></strong></h3>
		<div class="table-responsive">
			<table class="table table-bordered">
				<thead>
				<tr>
					<th>Nombre Evento</th>
					<th>Acciones</th>
				</tr>
				</thead>
				<tbody>
			   <?php
					while($row_Evento=sqlsrv_fetch_array($SQL_Evento)){?>
					<tr>
						<td><input type="text" class="form-control" id="TipoEvento<?php echo $row_Evento['ID_Evento'];?>" name="TipoEvento<?php echo $row_Evento['ID_Evento'];?>" value="<?php echo $row_Evento['NombreEvento'];?>" /></td>
						<td><button type="button" class="btn btn-success btn-xs" title="Ver conceptos relacionados" onClick="traerDatos('<?php echo $row_Evento['ID_Evento'];?>',2);"><i class="fa fa-eye"></i></button></td>
					</tr>
				<?php }?>
					<tr>
						<td><input type="text" class="form-control" id="TipoEventoNew" name="TipoEventoNew" value="" placeholder="Ingrese el nuevo valor" /></td>
						<td><button type="button" class="btn btn-primary btn-xs" title="Añadir concepto"><i class="fa fa-plus-square"></i></button></td>
					</tr>
				</tbody>
			</table>
		</div>
	<?php 
	}elseif($_POST['type']==2){//Tipo de evento, debe mostrar los resultados de gestión
		$SQL_Evento=Seleccionar("tbl_Cartera_Evento","*","ID_Evento='".$_POST['id']."'");
		$row_Evento=sqlsrv_fetch_array($SQL_Evento);
		
		$SQL_ResultadoGestion=Seleccionar("tbl_Cartera_ResultadoGestion","*","ID_Evento='".$_POST['id']."'");
	?>
		<h3>Resultados de gestión de <strong><?php echo $row_Evento['NombreEvento']; ?></strong></h3>
		<div class="table-responsive">
			<table class="table table-bordered">
				<thead>
				<tr>
					<th>Resultado de gestión</th>
					<th>Comentarios sugeridos</th>
					<th>Acciones</th>
				</tr>
				</thead>
				<tbody>
			   <?php
					while($row_ResultadoGestion=sqlsrv_fetch_array($SQL_ResultadoGestion)){?>
					<tr>
						<td><input type="text" class="form-control" id="ResultadoGestion<?php echo $row_ResultadoGestion['ID_ResultadoGestion'];?>" name="ResultadoGestion<?php echo $row_ResultadoGestion['ID_ResultadoGestion'];?>" value="<?php echo $row_ResultadoGestion['ResultadoGestion'];?>" /></td>
						<td>
							<textarea class="form-control" id="ComentariosGestion<?php echo $row_ResultadoGestion['ID_ResultadoGestion'];?>" name="ComentariosGestion<?php echo $row_ResultadoGestion['ID_ResultadoGestion'];?>"><?php echo $row_ResultadoGestion['ComentariosSugeridos'];?></textarea>
						</td>
						<td><button type="button" class="btn btn-success btn-xs" title="Ver conceptos relacionados" onClick="traerDatos('<?php echo $row_Evento['ID_Evento'];?>',2);"><i class="fa fa-eye"></i></button></td>
					</tr>
				<?php }?>
					<tr>
						<td><input type="text" class="form-control" id="ResultadoGestionNew" name="ResultadoGestionNew" value="" placeholder="Ingrese el nuevo valor" /></td>
						<td>
							<textarea class="form-control" id="ComentariosGestionNew" name="ComentariosGestionNew" placeholder="Ingrese los comentarios sugeridos"></textarea>
						</td>
						<td><button type="button" class="btn btn-primary btn-xs" title="Añadir concepto"><i class="fa fa-plus-square"></i></button></td>
					</tr>
				</tbody>
			</table>
		</div>
	<?php 
		
	}else{
		exit();
	}
	
	
} ?>