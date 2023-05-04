<?php require_once("includes/conexion.php");
PermitirAcceso(201);
//$Cons_Menu="Select * From uvw_tbl_Categorias";
//$SQL_Menu=sqlsrv_query($conexion,$Cons_Menu);
$SQL_Menu=Seleccionar('uvw_tbl_Categorias','*');
$json="";
while($row_Menu=sqlsrv_fetch_array($SQL_Menu)){
	if($row_Menu['ID_Padre']==0){
		$row_Menu['ID_Padre']="#";
	}
	$json=$json."{'id':'".$row_Menu['ID_Categoria']."', 'parent': '".$row_Menu['ID_Padre']."', 'text': '".$row_Menu['NombreCategoria']."', 'state': { 'opened': false}},";
}
//echo $json;
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Gestionar categor&iacute;as | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_Cat"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La categoría ha sido agregada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_Cat_edit"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La categoría ha sido actualizada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_Cat_delete"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La categoría ha sido eliminada exitosamente.',
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

    <?php include_once("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Gestionar categor&iacute;as</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Administraci&oacute;n</a>
                        </li>
                        <li class="active">
                            <strong>Gestionar categor&iacute;as</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
         <div class="row">
			<div class="col-lg-12">
				<div class="ibox-content">
					<?php include("includes/spinner.php"); ?>
					<a href="categorias.php" class="alkin btn btn-primary"><i class="fa fa-plus-circle"></i> Agregar categor&iacute;a</a>
					<a style="visibility: hidden;" id="a_Edit" href="#" class="alkin btn btn-success"><i class="fa fa-pencil"></i> Editar</a>
				</div>
			</div>
		  </div>
         <br>
          <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content"> 
					<?php include("includes/spinner.php"); ?>
           <?php 
			//$Cons_Menu="Select * From uvw_tbl_Categorias Where ID_Padre=0";
			//$SQL_Menu=sqlsrv_query($conexion,$Cons_Menu,array(),array( "Scrollable" => 'static' ));
			//$Num_Menu=sqlsrv_num_rows($SQL_Menu);
			?>
           	   <div id="jstree1">
                      
			   </div> 
			   <div id="event_result"></div>
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
	 $(".alkin").on('click', function(){
			$('.ibox-content').toggleClass('sk-loading');
		});		

        $('#jstree1')
		/*.on('changed.jstree', function (e, data) {
			var i, j, r = [];
			for(i = 0, j = data.selected.length; i < j; i++) {
			  r.push(data.instance.get_node(data.selected[i]).text);
			}
			//$('#event_result').html('Selected: ' + r.join(', '));
		})*/
		.jstree({
            'core' : {
                'check_callback' : function(){
					//alert('Prueba');
				},
				'strings': {
                	'Loading ...': 'Cargando...'
            	},
				'multiple' : false,
				'data': [<?php echo $json;?>]
            },
			'get_selected' : true
        })
		.bind('select_node.jstree', function (e, data) {
			Editar(data.node.id);
		});	
});
</script>
<script>
function Editar(id){
	//alert("Has seleccionado el nodo "+id);
	//var div_edit=document.getElementById('div_Edit');
	var a_Edit=document.getElementById('a_Edit');
	a_Edit.style.visibility='visible';
	a_Edit.setAttribute('href','categorias.php?id='+Base64.encode(id)+'&tl=1');
}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>