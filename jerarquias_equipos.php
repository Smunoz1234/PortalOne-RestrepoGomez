<?php 
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
        <!-- InstanceEndEditable -->

    </div>
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
				'data': [
					{
						"text" : "PLANTA VALORES",
						"icon" : "fa fa-map-marker",
						"children" : [
							{
								"text" : "UKITA 1",
								"icon" : "fa fa-car",
								"children" : [
									{
										"text" : "TORRE 1",
										"icon" : "fa fa-car"
									}
								]
							},
							{
								"text" : "UKITA 2",
								"icon" : "fa fa-wrench"
							}
						]
					}
				]
            },
			'get_selected' : true
			, "plugins" : [ "themes", "icons" ]
        })
		.bind('select_node.jstree', function (e, data) {
			Editar(data.node.id);
		});	
		
	console.log([<?php echo $json;?>]);
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
