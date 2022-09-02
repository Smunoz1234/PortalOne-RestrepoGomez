<?php require("includes/conexion.php");
PermitirAcceso(206);
$sw=0;//Para saber si ya se selecciono un cliente y mostrar las sucursales
//Clientes
$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes","CodigoCliente, NombreCliente","");

$Cons_Cat="Select * From uvw_tbl_Categorias Where ID_Padre=0 and EstadoCategoria=1 and ID_TipoCategoria=2";
//$SQL_Cat=sqlsrv_query($conexion,$Cons_Cat,array(),array( "Scrollable" => 'static' ));
//$Num_Menu=sqlsrv_num_rows($SQL_Menu);

if((isset($_POST['Cliente'])&&($_POST['Cliente'])!="")||(isset($_GET['Cliente'])&&($_GET['Cliente'])!="")){
	if(isset($_POST['Cliente'])){
		$Cliente=$_POST['Cliente'];
	}else{
		$Cliente=base64_decode($_GET['Cliente']);
	}
	$Where="Where CodigoCliente=''".$Cliente."''";
	$SQL_Sucursales=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","CodigoCliente, NombreCliente, NombreSucursal",$Where);
	$ConsDatos="Select * From uvw_tbl_AlertasInformes Where CardCode='".$Cliente."'";
	$SQL_Datos=sqlsrv_query($conexion,$ConsDatos,array(),array( "Scrollable" => 'static' ));
	$Num_Datos=sqlsrv_num_rows($SQL_Datos);
	$sw=1;
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo NOMBRE_PORTAL;?> | Gestionar alertas</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_Alert"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'Alertas agregadas exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_EditAlert"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El usuario ha sido editado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
?>

<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Gestionar alertas</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Administraci&oacute;n</a>
                        </li>
                        <li class="active">
                            <strong>Gestionar alertas</strong>
                        </li>
                    </ol>
                </div>
            </div>           
         <div class="wrapper wrapper-content">
			<div class="row">
				<div class="col-lg-12">   
					<div class="ibox-content"> 
						<h2>Seleccione el cliente para cargar la información</h2>
						<br>
						<form action="gestionar_alertas.php" method="post" class="form-horizontal" id="SeleccionarCliente">
							<div class="form-group">
								<label class="col-sm-1 control-label">Cliente</label>
								<div class="col-sm-4">
									<select name="Cliente" required class="form-control m-b chosen-select" id="Cliente">
										<option value="">Seleccione...</option>
										<?php while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){?>
											<option value="<?php echo $row_Cliente['CodigoCliente'];?>" <?php if((isset($Cliente))&&(strcmp($row_Cliente['CodigoCliente'],$Cliente)==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Cliente['NombreCliente'];?></option>
										<?php }?>
									</select>
								</div>
								<div class="col-sm-2">
									<button type="submit" class="btn btn-outline btn-info"><i class="fa fa-search"></i> Buscar</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
         <br>
		<?php if($sw==1){ ?>	  
          <div class="row">
			  <div class="col-lg-12">
				  <div class="ibox-content">
					  <form name="frmAlertas" id="frmAlertas" action="registro.php" method="post">
					  <div class="row">
						   <div class="col-lg-6">
							<h2>Configure las alertas</h2>
								</div>
						  <div class="col-lg-6">
							  <button class="btn btn-primary pull-right" type="submit"><i class="fa fa-check"></i> Guardar todo</button>
							</div>
					  </div>
					  <br>
						<?php $Cont=1;
						if($Num_Datos>0){
							$row_Datos=sqlsrv_fetch_array($SQL_Datos);
							do{ ?>
						<div id="div_<?php echo $Cont;?>">
							<div class="row">
								<div class="col-lg-1">Categoria: </div>
								<div class="col-lg-4">
								<select name="Categoria[]" class="form-control m-b" id="Categoria<?php echo $Cont;?>">
									<option value="" selected="selected">Seleccione...</option>
									<?php 
									$SQL_Cat=sqlsrv_query($conexion,$Cons_Cat,array(),array( "Scrollable" => 'static' ));
									while($row_Cat=sqlsrv_fetch_array($SQL_Cat)){
										$Sel="";//Si se ha seleccionado esta categoria
										echo "<optgroup label='".$row_Cat['NombreCategoria']."'>";

										$Cons_MenuLvl2="Select * From uvw_tbl_Categorias Where ID_Padre=".$row_Cat['ID_Categoria']." and EstadoCategoria=1";
										$SQL_MenuLvl2=sqlsrv_query($conexion,$Cons_MenuLvl2,array(),array( "Scrollable" => 'static' ));
										$Num_MenuLvl2=sqlsrv_num_rows($SQL_MenuLvl2);

										if($Num_MenuLvl2>=1){
											while($row_MenuLvl2=sqlsrv_fetch_array($SQL_MenuLvl2)){
												$Cons_MenuLvl3="Select * From uvw_tbl_Categorias Where ID_Padre=".$row_MenuLvl2['ID_Categoria']." and EstadoCategoria=1";
												$SQL_MenuLvl3=sqlsrv_query($conexion,$Cons_MenuLvl3,array(),array( "Scrollable" => 'static' ));
												$Num_MenuLvl3=sqlsrv_num_rows($SQL_MenuLvl3);

												if($Num_MenuLvl3>=1){
													echo "<optgroup label='".$row_MenuLvl2['NombreCategoria']."'>";
													while($row_MenuLvl3=sqlsrv_fetch_array($SQL_MenuLvl3)){
														if(strcmp($row_MenuLvl3['ID_Categoria'],$row_Datos['ID_Categoria'])==0){ $Sel="selected=\"selected\"";}else{$Sel="";}

														echo "<option value='".$row_MenuLvl3['ID_Categoria']."' ".$Sel.">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$row_MenuLvl3['NombreCategoria']."</option>";
													}
													echo "</optgroup>";
												}else{
													if(strcmp($row_MenuLvl2['ID_Categoria'],$row_Datos['ID_Categoria'])==0){ $Sel="selected=\"selected\"";}else{$Sel="";}

													echo "<option value='".$row_MenuLvl2['ID_Categoria']."' ".$Sel.">&nbsp;&nbsp;&nbsp;".$row_MenuLvl2['NombreCategoria']."</option>";
												}
											}
										}
										echo "</optgroup>";
									 }?>
								</select>
								</div>
								<div class="col-lg-1">Sucursal: </div>
								<div class="col-lg-4">
									<select name="Sucursal[]" class="form-control m-b" id="Sucursal<?php echo $Cont;?>">
										<option value="" selected="selected">Seleccione...</option>
										<?php 
										$Where="Where CodigoCliente=''".$Cliente."''";
										$SQL_Sucursales=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","CodigoCliente, NombreCliente, NombreSucursal",$Where);
										while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursales)){?>
											<option value="<?php echo $row_Sucursal['NombreSucursal'];?>" <?php if(strcmp($row_Sucursal['NombreSucursal'],$row_Datos['Sucursal'])==0){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['NombreSucursal'];?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="row">
							<div class="col-lg-12">
								<div class="table-responsive"> 
								<table class="table table-bordered">
									<thead>
									<tr>
										<td>Enero</td>
										<td>Febrero</td>
										<td>Marzo</td>
										<td>Abril</td>
										<td>Mayo</td>
										<td>Junio</td>
										<td>Julio</td>
										<td>Agosto</td>
										<td>Septiembre</td>
										<td>Octubre</td>
										<td>Noviembre</td>
										<td>Diciembre</td>
									</tr>
									</thead>
									<tbody>
<tr>
<td><input name="Enero[]" type="text" class="form-control" id="Enero<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="<?php if($row_Datos['Enero']>0){echo $row_Datos['Enero'];}?>" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Febrero[]" id="Febrero<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="<?php if($row_Datos['Febrero']>0){echo $row_Datos['Febrero'];}?>" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Marzo[]" id="Marzo<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="<?php if($row_Datos['Marzo']>0){echo $row_Datos['Marzo'];}?>" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Abril[]" id="Abril<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="<?php if($row_Datos['Abril']>0){echo $row_Datos['Abril'];}?>" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Mayo[]" id="Mayo<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="<?php if($row_Datos['Mayo']>0){echo $row_Datos['Mayo'];}?>" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Junio[]" id="Junio<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="<?php if($row_Datos['Junio']>0){echo $row_Datos['Junio'];}?>" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Julio[]" id="Julio<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="<?php if($row_Datos['Julio']>0){echo $row_Datos['Julio'];}?>" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Agosto[]" id="Agosto<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="<?php if($row_Datos['Agosto']>0){echo $row_Datos['Agosto'];}?>" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Septiembre[]" id="Septiembre<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="<?php if($row_Datos['Septiembre']>0){echo $row_Datos['Septiembre'];}?>" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Octubre[]" id="Octubre<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="<?php if($row_Datos['Octubre']>0){echo $row_Datos['Octubre'];}?>" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Noviembre[]" id="Noviembre<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="<?php if($row_Datos['Noviembre']>0){echo $row_Datos['Noviembre'];}?>" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Diciembre[]" id="Diciembre<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="<?php if($row_Datos['Diciembre']>0){echo $row_Datos['Diciembre'];}?>" onKeyPress="return justNumbersOnly(event);"></td>
</tr>
									</tbody>
								</table>
								</div>	
							</div>
							</div>
							<button type="button" id="<?php echo $Cont;?>" class="btn btn-warning btn-xs btn_del"><i class="fa fa-minus"></i> Remover</button>
							<br><br>
						</div>
						<?php $Cont++;} while($row_Datos=sqlsrv_fetch_array($SQL_Datos));
						} ?>

						<div id="div_<?php echo $Cont;?>">
							<div class="row">
								<div class="col-lg-1">Categoria: </div>
								<div class="col-lg-4">
								<select name="Categoria[]" class="form-control m-b" id="Categoria<?php echo $Cont;?>">
									<option value="" selected="selected">Seleccione...</option>
									<?php 
									$SQL_Cat=sqlsrv_query($conexion,$Cons_Cat,array(),array( "Scrollable" => 'static' ));
									while($row_Cat=sqlsrv_fetch_array($SQL_Cat)){
										echo "<optgroup label='".$row_Cat['NombreCategoria']."'>";

										$Cons_MenuLvl2="Select * From uvw_tbl_Categorias Where ID_Padre=".$row_Cat['ID_Categoria']." and EstadoCategoria=1";
										$SQL_MenuLvl2=sqlsrv_query($conexion,$Cons_MenuLvl2,array(),array( "Scrollable" => 'static' ));
										$Num_MenuLvl2=sqlsrv_num_rows($SQL_MenuLvl2);

										if($Num_MenuLvl2>=1){
											while($row_MenuLvl2=sqlsrv_fetch_array($SQL_MenuLvl2)){
												$Cons_MenuLvl3="Select * From uvw_tbl_Categorias Where ID_Padre=".$row_MenuLvl2['ID_Categoria']." and EstadoCategoria=1";
												$SQL_MenuLvl3=sqlsrv_query($conexion,$Cons_MenuLvl3,array(),array( "Scrollable" => 'static' ));
												$Num_MenuLvl3=sqlsrv_num_rows($SQL_MenuLvl3);

												if($Num_MenuLvl3>=1){
													echo "<optgroup label='".$row_MenuLvl2['NombreCategoria']."'>";
													while($row_MenuLvl3=sqlsrv_fetch_array($SQL_MenuLvl3)){

														echo "<option value='".$row_MenuLvl3['ID_Categoria']."' ".$Sel.">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$row_MenuLvl3['NombreCategoria']."</option>";
													}
													echo "</optgroup>";
												}else{

													echo "<option value='".$row_MenuLvl2['ID_Categoria']."' ".$Sel.">&nbsp;&nbsp;&nbsp;".$row_MenuLvl2['NombreCategoria']."</option>";
												}
											}
										}
										echo "</optgroup>";
									 }?>
								</select>
								</div>
								<div class="col-lg-1">Sucursal: </div>
								<div class="col-lg-4">
									<select name="Sucursal[]" class="form-control m-b" id="Sucursal<?php echo $Cont;?>">
										<option value="" selected="selected">Seleccione...</option>
										<?php 
										$Where="Where CodigoCliente=''".$Cliente."''";
										$SQL_Sucursales=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","CodigoCliente, NombreCliente, NombreSucursal",$Where);
										while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursales)){?>
											<option value="<?php echo $row_Sucursal['NombreSucursal'];?>"><?php echo $row_Sucursal['NombreSucursal'];?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="row">
							<div class="col-lg-12">
								<div class="table-responsive"> 
								<table class="table table-bordered">
									<thead>
									<tr>
										<td>Enero</td>
										<td>Febrero</td>
										<td>Marzo</td>
										<td>Abril</td>
										<td>Mayo</td>
										<td>Junio</td>
										<td>Julio</td>
										<td>Agosto</td>
										<td>Septiembre</td>
										<td>Octubre</td>
										<td>Noviembre</td>
										<td>Diciembre</td>
									</tr>
									</thead>
									<tbody>
<tr>
<td><input name="Enero[]" type="text" class="form-control" id="Enero<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Febrero[]" id="Febrero<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Marzo[]" id="Marzo<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Abril[]" id="Abril<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Mayo[]" id="Mayo<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Junio[]" id="Junio<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Julio[]" id="Julio<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Agosto[]" id="Agosto<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Septiembre[]" id="Septiembre<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Octubre[]" id="Octubre<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Noviembre[]" id="Noviembre<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="" onKeyPress="return justNumbersOnly(event);"></td>
<td><input class="form-control" type="text" name="Diciembre[]" id="Diciembre<?php echo $Cont;?>" maxlength="2" placeholder="Día" value="" onKeyPress="return justNumbersOnly(event);"></td>
</tr>
									</tbody>
								</table>
								</div>	
							</div>
							</div>
							<button type="button" id="<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addField(this);"><i class="fa fa-plus"></i> Añadir</button>
						<br><br>
						</div>

						
					  	<script>
						function addField(btn){//Clonar div
							var clickID = parseInt($(btn).parent('div').attr('id').replace('div_',''));
							//alert($(btn).parent('div').attr('id'));
							//alert(clickID);
							var newID = (clickID+1);

							$newClone = $('#div_'+clickID).clone(true);

							//div
							$newClone.attr("id",'div_'+newID);

							//select
							$newClone.children("div").eq(0).children("div").eq(1).children("select").eq(0).attr('id','Categoria'+newID);
							$newClone.children("div").eq(0).children("div").eq(3).children("select").eq(0).attr('id','Sucursal'+newID);

							//inputs
							$newClone.children("div").eq(1).children("div").eq(0).children("div").eq(0).children("table").eq(0).children("tbody").eq(0).children("tr").eq(0).children("td").eq(0).children("input").eq(0).attr('id','Enero'+newID);
							$newClone.children("div").eq(1).children("div").eq(0).children("div").eq(0).children("table").eq(0).children("tbody").eq(0).children("tr").eq(0).children("td").eq(1).children("input").eq(0).attr('id','Febrero'+newID);
							$newClone.children("div").eq(1).children("div").eq(0).children("div").eq(0).children("table").eq(0).children("tbody").eq(0).children("tr").eq(0).children("td").eq(2).children("input").eq(0).attr('id','Marzo'+newID);
							$newClone.children("div").eq(1).children("div").eq(0).children("div").eq(0).children("table").eq(0).children("tbody").eq(0).children("tr").eq(0).children("td").eq(3).children("input").eq(0).attr('id','Abril'+newID);
							$newClone.children("div").eq(1).children("div").eq(0).children("div").eq(0).children("table").eq(0).children("tbody").eq(0).children("tr").eq(0).children("td").eq(4).children("input").eq(0).attr('id','Mayo'+newID);
							$newClone.children("div").eq(1).children("div").eq(0).children("div").eq(0).children("table").eq(0).children("tbody").eq(0).children("tr").eq(0).children("td").eq(5).children("input").eq(0).attr('id','Junio'+newID);
							$newClone.children("div").eq(1).children("div").eq(0).children("div").eq(0).children("table").eq(0).children("tbody").eq(0).children("tr").eq(0).children("td").eq(6).children("input").eq(0).attr('id','Julio'+newID);
							$newClone.children("div").eq(1).children("div").eq(0).children("div").eq(0).children("table").eq(0).children("tbody").eq(0).children("tr").eq(0).children("td").eq(7).children("input").eq(0).attr('id','Agosto'+newID);
							$newClone.children("div").eq(1).children("div").eq(0).children("div").eq(0).children("table").eq(0).children("tbody").eq(0).children("tr").eq(0).children("td").eq(8).children("input").eq(0).attr('id','Septiembre'+newID);
							$newClone.children("div").eq(1).children("div").eq(0).children("div").eq(0).children("table").eq(0).children("tbody").eq(0).children("tr").eq(0).children("td").eq(9).children("input").eq(0).attr('id','Octubre'+newID);
							$newClone.children("div").eq(1).children("div").eq(0).children("div").eq(0).children("table").eq(0).children("tbody").eq(0).children("tr").eq(0).children("td").eq(10).children("input").eq(0).attr('id','Noviembre'+newID);
							$newClone.children("div").eq(1).children("div").eq(0).children("div").eq(0).children("table").eq(0).children("tbody").eq(0).children("tr").eq(0).children("td").eq(11).children("input").eq(0).attr('id','Diciembre'+newID);

							//button								
							$newClone.children("button").eq(0).attr('id',''+newID);

							$newClone.insertAfter($('#div_'+clickID));

							//$("#"+clickID).val('Remover');
							document.getElementById(''+clickID).innerHTML="<i class='fa fa-minus'></i> Remover";
							document.getElementById(''+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
							document.getElementById(''+clickID).setAttribute('onClick','delRow2(this);');

							//$("#"+clickID).addEventListener("click",delRow);

							//$("#"+clickID).bind("click",delRow);
						}
					  </script>
					<input type="hidden" name="P" id="P" value="26" />
					<input type="hidden" name="CardCode" id="CardCode" value="<?php echo base64_encode($Cliente);?>" />
				</form>
				  </div>	   
			  </div>
          </div>
		 <?php }
			 ?>
        </div>
        <!-- InstanceEndEditable -->
        <?php include("includes/footer.php"); ?>

    </div>
</div>
<?php include("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>	
	 $(document).ready(function(){
		 $(".btn_del").each(function (el){
			 $(this).bind("click",delRow);
		 });
		 
		 //$(".btn_plus").bind("click",addField);
		 
		 $("#frmAlertas").validate();
		 
		 $(".truncate").dotdotdot({
            watch: 'window'
		 });
		  		 
		 $('.chosen-select').chosen({width: "100%"});
	});
	
	$(function(){
		$('#toggleSpinners').on('click', function(){
			$('.ibox-content').toggleClass('sk-loading');
		})
	});
</script>

<script>
function delRow(){//Eliminar div
	$(this).parent('div').remove();
}
function delRow2(btn){//Eliminar div
	$(btn).parent('div').remove();
}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>