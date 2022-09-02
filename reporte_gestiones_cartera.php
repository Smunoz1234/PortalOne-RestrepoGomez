<?php require_once("includes/conexion.php");
PermitirAcceso(802);
$sw=0;
$Anio=date('Y');
$Mes=date('n');
$Usuario="";

//Usuarios
$SQL_UsuariosGestion=Seleccionar('uvw_tbl_Cartera_Gestion','DISTINCT ID_Usuario, NombreUsuario','','NombreUsuario');

if(isset($_POST['Anio'])&&$_POST['Anio']!=""){
	$Anio=$_POST['Anio'];
	$Mes=$_POST['Mes'];
	
	//$SQL_Dia=Seleccionar("uvw_tbl_Cartera_Gestion","DISTINCT Dia","Anio='".$Anio."' AND Mes='".$Mes."'","Dia");
	//$SQL_Asesor=Seleccionar("uvw_tbl_Cartera_Gestion","DISTINCT NombreUsuario","Anio='".$Anio."' AND Mes='".$Mes."'","NombreUsuario");
	
	$sw=1;
}

if(isset($_POST['UsuarioGestion'])&&$_POST['UsuarioGestion']!=""){
	$Usuario=$_POST['UsuarioGestion'];
	$sw=1;
}


?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Reporte de gestiones | <?php echo NOMBRE_PORTAL;?></title>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include_once("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Reporte de gestiones</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Gestión de cartera</a>
                        </li>
                        <li class="active">
                            <strong>Reporte de gestiones</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			 <div class="ibox-content">
				 <?php include("includes/spinner.php"); ?>
          <div class="row"> 
           <div class="col-lg-12">
              	<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Aplique los filtros necesarios</h3>
				<br>
				<form action="reporte_gestiones_cartera.php" method="post" class="form-horizontal" id="Consultar">
					<div class="form-group">
						<label class="col-lg-1 control-label">Año</label>
						<div class="col-lg-2">
							<select name="Anio" class="form-control m-b" id="Anio" required>
								<option value="2019" <?php if($Anio=='2019'){echo "selected";} ?>>2019</option>
								<option value="2020" <?php if($Anio=='2020'){echo "selected";} ?>>2020</option>
								<option value="2021" <?php if($Anio=='2021'){echo "selected";} ?>>2021</option>
								<option value="2022" <?php if($Anio=='2022'){echo "selected";} ?>>2022</option>
							</select>
						</div>
						<label class="col-lg-1 control-label">Mes</label>
						<div class="col-lg-2">
							<select name="Mes" class="form-control m-b" id="Mes" required>
								<option value="1" <?php if($Mes==1){echo "selected";} ?>>1 - Enero</option>
								<option value="2" <?php if($Mes==2){echo "selected";} ?>>2 - Febrero</option>
								<option value="3" <?php if($Mes==3){echo "selected";} ?>>3 - Marzo</option>
								<option value="4" <?php if($Mes==4){echo "selected";} ?>>4 - Abril</option>
								<option value="5" <?php if($Mes==5){echo "selected";} ?>>5 - Mayo</option>
								<option value="6" <?php if($Mes==6){echo "selected";} ?>>6 - Junio</option>
								<option value="7" <?php if($Mes==7){echo "selected";} ?>>7 - Julio</option>
								<option value="8" <?php if($Mes==8){echo "selected";} ?>>8 - Agosto</option>
								<option value="9" <?php if($Mes==9){echo "selected";} ?>>9 - Septiembre</option>
								<option value="10" <?php if($Mes==10){echo "selected";} ?>>10 - Octubre</option>
								<option value="11" <?php if($Mes==11){echo "selected";} ?>>11 - Noviembre</option>
								<option value="12" <?php if($Mes==12){echo "selected";} ?>>12 - Diciembre</option>
							</select>
						</div>
						<label class="col-lg-1 control-label">Asesor</label>
							<div class="col-lg-2">
								<select name="UsuarioGestion" class="form-control m-b" id="UsuarioGestion">
									<option value="">(TODOS)</option>
								  <?php while($row_UsuariosGestion=sqlsrv_fetch_array($SQL_UsuariosGestion)){?>
										<option value="<?php echo $row_UsuariosGestion['NombreUsuario'];?>" <?php if((isset($_POST['UsuarioGestion']))&&(strcmp($row_UsuariosGestion['NombreUsuario'],$_POST['UsuarioGestion'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_UsuariosGestion['NombreUsuario'];?></option>
								  <?php }?>
								</select>
							</div>
						<div class="col-lg-2">
							<button type="submit" class="btn btn-outline btn-info"><i class="fa fa-filter"></i> Filtrar</button>
						</div>
					</div>
				</form>
		   </div>
			</div>
          </div>
		<?php if($sw==1){?>
			<br>
			 <div class="row">
				<div class="col-lg-12">
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="table-responsive">
						 <table class="table table-hover">
							<thead>
								<tr class="font-bold bg-muted">
									<th>Asesor</th>
									<th>1</th>
									<th>2</th>
									<th>3</th>
									<th>4</th>
									<th>5</th>
									<th>6</th>
									<th>7</th>
									<th>8</th>
									<th>9</th>
									<th>10</th>
									<th>11</th>
									<th>12</th>
									<th>13</th>
									<th>14</th>
									<th>15</th>
									<th>16</th>
									<th>17</th>
									<th>18</th>
									<th>19</th>
									<th>20</th>
									<th>21</th>
									<th>22</th>
									<th>23</th>
									<th>24</th>
									<th>25</th>
									<th>26</th>
									<th>27</th>
									<th>28</th>
									<th>29</th>
									<th>30</th>
									<th>31</th>
									<th>Total</th>
								</tr>
							</thead>
							<tbody>
								<?php 
						 		$Parametros=array(
									"'".$Anio."'",
									"'".$Mes."'",
									"'".$Usuario."'"
								);
								$SQL_Cant=EjecutarSP('sp_InformeGestionCartera',$Parametros);
						 		while($row_Cant=sqlsrv_fetch_array($SQL_Cant)){
								?>
								<tr>
									<td><?php echo $row_Cant['NombreAsesor'];?></td>
									<td><?php echo $row_Cant['1'];?></td>
									<td><?php echo $row_Cant['2'];?></td>
									<td><?php echo $row_Cant['3'];?></td>
									<td><?php echo $row_Cant['4'];?></td>
									<td><?php echo $row_Cant['5'];?></td>
									<td><?php echo $row_Cant['6'];?></td>
									<td><?php echo $row_Cant['7'];?></td>
									<td><?php echo $row_Cant['8'];?></td>
									<td><?php echo $row_Cant['9'];?></td>
									<td><?php echo $row_Cant['10'];?></td>
									<td><?php echo $row_Cant['11'];?></td>
									<td><?php echo $row_Cant['12'];?></td>
									<td><?php echo $row_Cant['13'];?></td>
									<td><?php echo $row_Cant['14'];?></td>
									<td><?php echo $row_Cant['15'];?></td>
									<td><?php echo $row_Cant['16'];?></td>
									<td><?php echo $row_Cant['17'];?></td>
									<td><?php echo $row_Cant['18'];?></td>
									<td><?php echo $row_Cant['19'];?></td>
									<td><?php echo $row_Cant['20'];?></td>
									<td><?php echo $row_Cant['21'];?></td>
									<td><?php echo $row_Cant['22'];?></td>
									<td><?php echo $row_Cant['23'];?></td>
									<td><?php echo $row_Cant['24'];?></td>
									<td><?php echo $row_Cant['25'];?></td>
									<td><?php echo $row_Cant['26'];?></td>
									<td><?php echo $row_Cant['27'];?></td>
									<td><?php echo $row_Cant['28'];?></td>
									<td><?php echo $row_Cant['29'];?></td>
									<td><?php echo $row_Cant['30'];?></td>
									<td><?php echo $row_Cant['31'];?></td>
									<td><?php echo $row_Cant['Total'];?></td>
								</tr>				
								<?php }?>								
							</tbody>
                         </table>
						</div>						
					</div>
				</div>
			 </div>
			 <br>
			 <div class="row">
				 <div class="col-lg-6">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5>TOP 3 - Resultados de gestión</h5>
                        </div>
                        <div class="ibox-content">
							<?php include("includes/spinner.php"); ?>
                            <div>
                                <div id="pie"></div>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="col-lg-6">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5>TOP 5 - Cantidad de gestiones</h5>
                        </div>
                        <div class="ibox-content">
							<?php include("includes/spinner.php"); ?>
                            <div>
                                <div id="pie2"></div>
                            </div>
                        </div>
                    </div>
                </div>
			 </div>
		<?php }?>
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>
	 $(document).ready(function(){
		 $("#Consultar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
		 
		 c3.generate({
                bindto: '#pie',
                data: {
					columns: [
						<?php 
						$ParametrosTop=array(
							"'".$Anio."'",
							"'".$Mes."'",
							"'".$Usuario."'"
						);
						$SQL_TopRes=EjecutarSP('sp_InformeGestionCartera_TOPResultados',$ParametrosTop);

						while($row_TopRes=sql_fetch_array($SQL_TopRes)){
							echo '["'.$row_TopRes['ResultadoGestion'].'",'.$row_TopRes['Cant'].'],';
						}						
						?>
					],
					type : 'donut'
				},
				tooltip: {
					show: true,
					format: {
						title: function (d) { return 'Cantidad';},
						value: function (value, ratio, id) {
							var format = d3.format(',');
							return format(value);
						}
					}
				}
            });
		 
		c3.generate({
                bindto: '#pie2',
                data: {
					columns: [
						<?php 
						array_push($ParametrosTop, 2);
						$SQL_TopRes=EjecutarSP('sp_InformeGestionCartera_TOPResultados',$ParametrosTop);

						while($row_TopRes=sql_fetch_array($SQL_TopRes)){
							echo '["'.$row_TopRes['NombreUsuario'].'",'.$row_TopRes['Cant'].'],';
						}						
						?>
					],
					type : 'bar'
				},
				bar: {
					width: {
						ratio: 0.5
					}
				},
				tooltip: {
					show: true,
					format: {
						title: function (d) { return 'Cantidad';},
						value: function (value, ratio, id) {
							var format = d3.format(',');
							return format(value);
						}
					}
				}
            });
		 
	});
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>