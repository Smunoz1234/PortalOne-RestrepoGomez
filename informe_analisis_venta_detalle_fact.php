<?php  
require_once("includes/conexion.php");
PermitirAcceso(413);

$ParamCons=array(
	"'13'",
	"'".$_POST['DocEntry']."'"
);
$SQL=EjecutarSP('usp_InformeVentas_DetalleLlamadas',$ParamCons);

$ParamCount=array(
	"'13'",
	"'".$_POST['DocEntry']."'",
	1
);
$SQL_Count=EjecutarSP('usp_InformeVentas_DetalleLlamadas',$ParamCount);
$row_Count=sqlsrv_fetch_array($SQL_Count);
?>
<br>
 	<div class="row">
	   <div class="col-lg-12">
			<div class="ibox-content">
				 <?php include("includes/spinner.php"); ?>
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-list"></i> Detalle de factura - Documentos relacionados</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>	
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<div class="row m-b-md">
								<div class="col-lg-3">
									<div class="ibox border-left-right border-top-bottom">
										<div class="ibox-title">
											<h2 class="font-bold">Factura No</h2>
										</div>
										<div class="ibox-content">
											<h2 class="no-margins"><span class="font-bold text-info"><?php echo $_POST['DocNum'];?></span></h2>
										</div>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="ibox border-left-right border-top-bottom">
										<div class="ibox-title">
											<h2 class="font-bold">Cant. OT relacionadas</h2>
										</div>
										<div class="ibox-content">
											<h2 class="no-margins"><span class="font-bold text-info"><?php echo $row_Count['Cant'];?></span></h2>
										</div>
									</div>
								</div>
							</div>
							<div class="row m-b-md">
								<div class="col-lg-3">
									<div class="ibox border-left-right border-top-bottom">
										<div class="ibox-title">
											<h2 class="font-bold">Total ventas</h2>
										</div>
										<div class="ibox-content">
											<h2 class="no-margins"><span class="font-bold text-success"><?php echo "$".number_format($_POST['Ventas'],0);?></span></h2>
										</div>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="ibox border-left-right border-top-bottom">
										<div class="ibox-title">
											<h2 class="font-bold">Total costos 
												<?php if($_POST['Costos']>0){?><button type="button" title="Mostrar detalle de costos" class="btn btn-primary pull-right" onClick="CargarArticulos('<?php echo ($row_Count['Cant']>0) ? "15" : "13"; ?>','<?php echo $_POST['DocEntry'];?>','<?php echo $_POST['DocNum'];?>','Costos de factura',1);"><i class="fa fa-list"></i></button><?php }?>
											</h2>
										</div>
										<div class="ibox-content">
											<h2 class="no-margins"><span class="font-bold text-danger"><?php echo "$".number_format($_POST['Costos'],0);?></span></h2>
										</div>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="ibox border-left-right border-top-bottom">
										<div class="ibox-title">
											<h2 class="font-bold">Total ganancias</h2>
										</div>
										<div class="ibox-content">
											<h2 class="no-margins"><span class="font-bold <?php if($_POST['Ganancia']<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo "$".number_format($_POST['Ganancia'],0);?></span></h2>
										</div>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="ibox border-left-right border-top-bottom">
										<div class="ibox-title">
											<h2 class="font-bold">Ganancias (%)</h2>
										</div>
										<div class="ibox-content">
											<h2 class="no-margins"><span class="font-bold <?php if($_POST['PrctGanancia']<0 || $_POST['Ganancia']<0){echo "text-danger";}else{echo "text-navy";}?>"><?php echo ($_POST['Ganancia']<0) ? number_format($_POST['PrctGanancia']*-1,2)."%" : number_format($_POST['PrctGanancia'],2)."%";?></span></h2>
										</div>
									</div>
								</div>
							</div>
							<?php if($row_Count['Cant']>0){?>
							<div class="table-responsive">
								<table class="table table-bordered table-hover dataTables-DetailsFact" >
								<thead>
								<tr>
									<th>Llamada de servicio</th>
									<th>Fecha programación</th>
									<th>Fecha cierre</th>
									<th>Lista de materiales</th>
									<th>Tipo llamada</th>
									<th>Sucursal cliente</th>
									<th>Técnico asignado</th>
									<th>Estado llamada</th>
									<th>Tipo documento</th>
									<th>Número de documento</th>
									<th>Ingresos/Costos del documento</th>
									<th>Fecha de documento</th>
									<th>Estado documento</th>
									<th>Acciones</th>
								</tr>
								</thead>
								<tbody>
								<?php
									while($row=sqlsrv_fetch_array($SQL)){ ?>
										<tr class="gradeX">
											<td><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_LlamadaServicio']);?>&tl=1" target="_blank"><?php echo $row['DocNumLlamada'];?></a></td>
											<td><?php echo $row['FechaCreacionLlamada'];?></td>
											<td><?php echo $row['FechaCierreLlamada'];?></td>
											<td><a href="articulos.php?id=<?php echo base64_encode($row['IdArticuloLlamada']);?>&tl=1" target="_blank" title="<?php echo $row['DeArticuloLlamada'];?>"><?php echo $row['IdArticuloLlamada'];?></a></td>
											<td><?php echo $row['DeTipoLlamada'];?></td>
											<td><?php echo $row['NombreSucursal'];?></td>
											<td><?php echo $row['NombreEmpleadoActividad'];?></td>
											<td><span <?php if($row['IdEstadoLlamada']=='-3'){echo "class='label label-info'";}elseif($row['IdEstadoLlamada']=='-2'){echo "class='label label-warning'";}else{echo "class='label label-danger'";}?>><?php echo $row['DeEstadoLlamada'];?></span></td>
											<td><?php echo $row['DeObjeto'];?></td>											
											<td><a href="<?php echo $row['Link'];?>.php?id=<?php echo base64_encode($row['DocEntry']);?>&tl=1" target="_blank"><?php echo $row['DocNum'];?></a></td>
											<td><span class="badge <?php if($row['IdObjeto']==13 || $row['IdObjeto']==16){echo "badge-success";}elseif($row['IdObjeto']==15){echo "badge-danger";}else{echo "badge-muted";}?>"><?php if($row['IdObjeto']==13 || $row['IdObjeto']==16){echo "(+) ";}elseif($row['IdObjeto']==15){echo "(-) ";} echo number_format($row['Costos'],2);?></span></td>
											<td><?php echo $row['DocDate'];?></td>
											<td><span <?php if($row['Cod_Estado']=='O'){echo "class='label label-info'";}else{echo "class='label label-danger'";}?>><?php echo $row['NombreEstado'];?></span></td>
											<td class="text-center form-inline w-80">
												<button type="button" title="Mostrar detalle de artículos" class="btn btn-primary btn-xs" onClick="CargarArticulos('<?php echo $row['IdObjeto'];?>','<?php echo $row['DocEntry'];?>','<?php echo $row['DocNum'];?>','<?php echo $row['DeObjeto'];?>');"><i class="fa fa-list"></i></button>
												<button type="button" title="Mostrar actividades" class="btn btn-success btn-xs" onClick="CargarAct('<?php echo $row['ID_LlamadaServicio'];?>','<?php echo $row['DocNumLlamada'];?>');"><i class="fa fa-tags"></i></button>								
											</td>
										</tr>
								<?php }?>
								</tbody>
								</table>
								<div class="col-lg-12">
									<a href="exportar_excel.php?exp=10&Cons=<?php echo base64_encode(implode(",",$ParamCons));?>&sp=<?php echo base64_encode("usp_InformeVentas_DetalleLlamadas");?>">
										<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
									</a>
								</div>
							</div>
							<?php }?>
						</div>
					</div>
				</div>
			</div>
		</div> 
	</div>
<script>
 $(document).ready(function(){
	 
	var table = $('.dataTables-DetailsFact').DataTable({
		ordering: false,
		info: false,
		paging: false,
		searching: false,
		rowGroup: {
			dataSrc: 0
		},
		language: {
			"decimal":        "",
			"emptyTable":     "No se encontraron resultados.",
			"info":           "Mostrando _START_ - _END_ de _TOTAL_ registros",
			"infoEmpty":      "Mostrando 0 - 0 de 0 registros",
			"infoFiltered":   "(filtrando de _MAX_ registros)",
			"infoPostFix":    "",
			"thousands":      ",",
			"lengthMenu":     "Mostrar _MENU_ registros",
			"loadingRecords": "Cargando...",
			"processing":     "Procesando...",
			"search":         "Filtrar:",
			"zeroRecords":    "Ningún registro encontrado",
			"paginate": {
				"first":      "Primero",
				"last":       "Último",
				"next":       "Siguiente",
				"previous":   "Anterior"
			},
			"aria": {
				"sortAscending":  ": Activar para ordenar la columna ascendente",
				"sortDescending": ": Activar para ordenar la columna descendente"
			}
		},
		buttons: []

	});
 });
</script>