<?php require_once("includes/conexion.php");
PermitirAcceso(201);
$Cons="Select * From uvw_tbl_Categorias Where ID_Categoria=".base64_decode($_GET['id']);
$SQL=sqlsrv_query($conexion,$Cons);
$row=sqlsrv_fetch_array($SQL);

//Tipos de categorias
$SQL_TipoCategoria=Seleccionar("uvw_tbl_TipoCategoria","ID_TipoCategoria, TipoCategoria","");
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo NOMBRE_PORTAL;?> | Editar categor&iacute;a</title>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#TipoCategoria").change(function(){
			var TipoCat=document.getElementById('TipoCategoria').value;
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:13,TipoCat:TipoCat},
				dataType:'json',
				success: function(data){
					document.getElementById('URL').value=data.URL;
				}
			});
		});
	});
</script>
<script>
function Eliminar(){
	swal({
		title: "Eliminar",
		text: "¿Está seguro que desea eliminar esta categoría?",
		type: "warning",
		showCancelButton: true,
		confirmButtonText: "Si, estoy seguro",
		cancelButtonText: "Cancelar",
		closeOnConfirm: false
	},
	function(){
		$('.ibox-content').toggleClass('sk-loading');
		location.href='registro.php?P=3&id=<?php echo $row['ID_Categoria'];?>';
	});
}
</script>
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
                    <h2>Editar categor&iacute;a</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Administraci&oacute;n</a>
                        </li>
                        <li>
                            <a href="gestionar_categorias.php">Gestionar categor&iacute;as</a>
                        </li>
                        <li class="active">
                            <strong>Editar categor&iacute;a</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			 <div class="ibox-content">
				 <?php include("includes/spinner.php"); ?>
          		<div class="row">
           <div class="col-lg-6">
              <form action="registro.php" method="post" class="form-horizontal" id="AgregarCategoria">
				<div class="form-group">
					<label class="col-sm-3 control-label">Nombre categor&iacute;a</label>
					<div class="col-sm-9"><input name="NombreCategoria" type="text" required="required" class="form-control" id="NombreCategoria" value="<?php echo $row['NombreCategoria'];?>"></div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Men&uacute; padre</label>
					<div class="col-sm-9">
					 <?php 
						$Cons_Menu="Select * From uvw_tbl_Categorias Where ID_Padre=0 and EstadoCategoria=1";
						$SQL_Menu=sqlsrv_query($conexion,$Cons_Menu,array(),array( "Scrollable" => 'static' ));
						$Num_Menu=sqlsrv_num_rows($SQL_Menu);
					 ?>
						<select name="IDPadre" class="form-control m-b" id="IDPadre">
                           <option value="0">(Ninguno)</option>
                           <?php 
							while($row_Menu=sqlsrv_fetch_array($SQL_Menu)){
								if((strcmp($row_Menu['ID_Categoria'],$row['ID_Padre'])==0)){
									echo "<option value='".$row_Menu['ID_Categoria']."' selected='selected'>".$row_Menu['NombreCategoria']."</option>";
								}else{
									echo "<option value='".$row_Menu['ID_Categoria']."'>".$row_Menu['NombreCategoria']."</option>";
								}
								
								$Cons_MenuLvl2="Select * From uvw_tbl_Categorias Where ID_Padre=".$row_Menu['ID_Categoria']." and EstadoCategoria=1";
								$SQL_MenuLvl2=sqlsrv_query($conexion,$Cons_MenuLvl2,array(),array( "Scrollable" => 'static' ));
								$Num_MenuLvl2=sqlsrv_num_rows($SQL_MenuLvl2);

								if($Num_MenuLvl2>=1){
									while($row_MenuLvl2=sqlsrv_fetch_array($SQL_MenuLvl2)){
										$Cons_MenuLvl3="Select * From uvw_tbl_Categorias Where ID_Padre=".$row_MenuLvl2['ID_Categoria']." and EstadoCategoria=1";
										$SQL_MenuLvl3=sqlsrv_query($conexion,$Cons_MenuLvl3,array(),array( "Scrollable" => 'static' ));
										$Num_MenuLvl3=sqlsrv_num_rows($SQL_MenuLvl3);
										
										if((strcmp($row_MenuLvl2['ID_Categoria'],$row['ID_Padre'])==0)){
											echo "<option value='".$row_MenuLvl2['ID_Categoria']."' selected='selected'>&nbsp;&nbsp;&nbsp;".$row_MenuLvl2['NombreCategoria']."</option>";
										}else{
											echo "<option value='".$row_MenuLvl2['ID_Categoria']."'>&nbsp;&nbsp;&nbsp;".$row_MenuLvl2['NombreCategoria']."</option>";
										}

										if($Num_MenuLvl3>=1){
											while($row_MenuLvl3=sqlsrv_fetch_array($SQL_MenuLvl3)){
												if((strcmp($row_MenuLvl3['ID_Categoria'],$row['ID_Padre'])==0)){
													echo "<option value='".$row_MenuLvl3['ID_Categoria']."' selected='selected'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$row_MenuLvl3['NombreCategoria']."</option>";
												}else{
													echo "<option value='".$row_MenuLvl3['ID_Categoria']."'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$row_MenuLvl3['NombreCategoria']."</option>";
												}
												
											}
										}
									}
								}	
							 }?>
                        </select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Estado</label>
					<div class="col-sm-9">
                    	<select name="EstadoCategoria" class="form-control m-b" id="EstadoCategoria">
                           <option value="1" <?php if($row['EstadoCategoria']==1){echo "selected='selected'";}?>>Activo</option>
                           <option value="2" <?php if($row['EstadoCategoria']==2){echo "selected='selected'";}?>>Inactivo</option>
                        </select>
               	  </div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Tipo categor&iacute;a</label>
					<div class="col-sm-9">
                    	<select name="TipoCategoria" class="form-control m-b" id="TipoCategoria">
                           <?php while($row_TipoCategoria=sqlsrv_fetch_array($SQL_TipoCategoria)){?>
								<option value="<?php echo $row_TipoCategoria['ID_TipoCategoria'];?>" <?php if((strcmp($row_TipoCategoria['ID_TipoCategoria'],$row['ID_TipoCategoria'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoCategoria['TipoCategoria'];?></option>
						  <?php }?>
                        </select>
               	  </div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Mostrar en Dashboard</label>
					<div class="col-sm-9">
                    	<select name="MostrarDashboard" class="form-control m-b" id="MostrarDashboard">
                            <option value="1" <?php if($row['MostrarDashboard']==1){echo "selected='selected'";}?>>SI</option>
                           <option value="0" <?php if($row['MostrarDashboard']==0){echo "selected='selected'";}?>>NO</option>
                        </select>
               	  </div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">URL</label>
					<div class="col-sm-9">
						<input name="URL" type="text" class="form-control" id="URL" value="<?php echo $row['URL'];?>">
						<span class="form-text m-b-none text-muted">Si es un menú padre, ingrese solo el signo <span class="font-bold">#</span>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-9">
						<button class="alkin btn btn-warning" type="submit"><i class="fa fa-refresh"></i>&nbsp;Actualizar</button> <a href="gestionar_categorias.php" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
					</div>
					<div class="col-sm-3">
						<button class="btn btn-danger pull-right" type="button" onClick="Eliminar();"><i class="fa fa-times-circle"></i>&nbsp;Eliminar</button>		
					</div>
				</div>
				<input type="hidden" id="P" name="P" value="2" />
				<input type="hidden" id="ID" name="ID" value="<?php echo $row['ID_Categoria'];?>" />
			  </form>
		   </div>
          </div>
			</div>
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>
	 $(document).ready(function(){
		 $("#AgregarCategoria").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
		 $(".alkin").on('click', function(){
				 $('.ibox-content').toggleClass('sk-loading');
			});	
	});
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>