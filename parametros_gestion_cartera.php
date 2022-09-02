<?php 
require_once("includes/conexion.php");
PermitirAcceso(803);

$SQL_TipoGestion=Seleccionar("tbl_Cartera_TipoGestion","*");

$SQL_Evento=Seleccionar("tbl_Cartera_Evento","*");

$SQL_ResGestion=Seleccionar("tbl_Cartera_ResultadoGestion","*");

$SQL_Dirigido=Seleccionar("tbl_Cartera_Dirigido","*");

$SQL_NoPago=Seleccionar("tbl_Cartera_CausaNoPago","*");

$SQL_RelConceptos=Seleccionar("uvw_tbl_Cartera_RelacionConceptos","*");

//Creacion de OT
//$SQL_CreacionOT=Seleccionar("tbl_Parametros_Asistentes","*","TipoAsistente=2");

//OT de mantenimiento
//$SQL_MantOT=Seleccionar("tbl_Parametros_Asistentes","*","TipoAsistente=3");

$sw_error=0;

//Insertar datos
if(isset($_POST['frmType'])&&($_POST['frmType']!="")){
	try{
		if($_POST['frmType']==1){//Guardar o actualizar conceptos
			
			//Tipos de gestion
			$i=0;
			$Cuenta=count($_POST['TipoGestion']);
			while($i<$Cuenta){
				if($_POST['TipoGestion'][$i]!=""&&$_POST['TipoDestino'][$i]!=""&&$_POST['MetodoTG'][$i]!="0"){
					$Param=array(
						"1",
						"'".$_POST['ID_TipoGestion'][$i]."'",
						"'TipoGestion'",
						"'".$_POST['TipoGestion'][$i]."'",
						"'".$_POST['TipoDestino'][$i]."'",
						"'".$_POST['MetodoTG'][$i]."'",
						"'".$_SESSION['CodUser']."'"
					);
					$SQL=EjecutarSP('sp_tbl_Cartera_RelacionConceptos',$Param);
					if(!$SQL){
						$sw_error=1;
						$msg_error="No se pudo insertar los conceptos";
					}
				}
				$i++;
			}
			
			//Eventos
			$i=0;
			$Cuenta=count($_POST['NombreEvento']);
			while($i<$Cuenta){
				if($_POST['NombreEvento'][$i]!=""&&$_POST['MetodoEvento'][$i]!="0"){
					$Param=array(
						"1",
						"'".$_POST['ID_Evento'][$i]."'",
						"'Evento'",
						"'".$_POST['NombreEvento'][$i]."'",
						"NULL",
						"'".$_POST['MetodoEvento'][$i]."'",
						"'".$_SESSION['CodUser']."'"
					);
					$SQL=EjecutarSP('sp_tbl_Cartera_RelacionConceptos',$Param);
					if(!$SQL){
						$sw_error=1;
						$msg_error="No se pudo insertar los conceptos";
					}
				}
				$i++;
			}
			
			//Dirigido
			$i=0;
			$Cuenta=count($_POST['NombreDirigido']);
			while($i<$Cuenta){
				if($_POST['NombreDirigido'][$i]!=""&&$_POST['MetodoDirigido'][$i]!="0"){
					$Param=array(
						"1",
						"'".$_POST['ID_Dirigido'][$i]."'",
						"'Dirigido'",
						"'".$_POST['NombreDirigido'][$i]."'",
						"NULL",
						"'".$_POST['MetodoDirigido'][$i]."'",
						"'".$_SESSION['CodUser']."'"
					);
					$SQL=EjecutarSP('sp_tbl_Cartera_RelacionConceptos',$Param);
					if(!$SQL){
						$sw_error=1;
						$msg_error="No se pudo insertar los conceptos";
					}
				}
				$i++;
			}
			
			//CausaNoPago
			$i=0;
			$Cuenta=count($_POST['CausaNoPago']);
			while($i<$Cuenta){
				if($_POST['CausaNoPago'][$i]!=""&&$_POST['MetodoNoPago'][$i]!="0"){
					$Param=array(
						"1",
						"'".$_POST['ID_CausaNoPago'][$i]."'",
						"'CausaNoPago'",
						"'".$_POST['CausaNoPago'][$i]."'",
						"NULL",
						"'".$_POST['MetodoNoPago'][$i]."'",
						"'".$_SESSION['CodUser']."'"
					);
					$SQL=EjecutarSP('sp_tbl_Cartera_RelacionConceptos',$Param);
					if(!$SQL){
						$sw_error=1;
						$msg_error="No se pudo insertar los conceptos";
					}
				}
				$i++;
			}
			
			//Resultado gestión
			$i=0;
			$Cuenta=count($_POST['NombreResGestion']);
			while($i<$Cuenta){
				if($_POST['NombreResGestion'][$i]!=""&&$_POST['MetodoResGestion'][$i]!="0"){
					$Param=array(
						"1",
						"'".$_POST['ID_ResultadoGestion'][$i]."'",
						"'ResultadoGestion'",
						"'".$_POST['NombreResGestion'][$i]."'",
						"'".$_POST['ComentariosResGestion'][$i]."'",
						"'".$_POST['MetodoResGestion'][$i]."'",
						"'".$_SESSION['CodUser']."'"
					);
					$SQL=EjecutarSP('sp_tbl_Cartera_RelacionConceptos',$Param);
					if(!$SQL){
						$sw_error=1;
						$msg_error="No se pudo insertar los conceptos";
					}
				}
				$i++;
			}
		
		}else{//Guardar la relacion de los conceptos
			$i=0;
			$Cuenta=count($_POST['TipoGestion']);
			$Del=Eliminar('tbl_Cartera_RelacionConceptos');
			if($Del){
				while($i<$Cuenta){
					if($_POST['TipoGestion'][$i]!=""&&$_POST['NombreEvento'][$i]!=""&&$_POST['ResultadoGestion'][$i]!=""){
						$Param=array(
							"2",
							"NULL",
							"'".$_POST['TipoGestion'][$i]."'",
							"'".$_POST['NombreEvento'][$i]."'",
							"'".$_POST['ResultadoGestion'][$i]."'",
							"NULL",
							"'".$_SESSION['CodUser']."'"
						);
						$SQL=EjecutarSP('sp_tbl_Cartera_RelacionConceptos',$Param);
						if(!$SQL){
							$sw_error=1;
							$msg_error="No se pudo insertar la relacion";
						}
					}
					$i++;
				}
			}else{
				$sw_error=1;
				$msg_error="No se pudo eliminar la relacion";
			}
		}

		
		if($sw_error==0){
			header('Location:parametros_gestion_cartera.php?a='.base64_encode("OK_PRUpd"));
		}
	}catch (Exception $e) {
		$sw_error=1;
		$msg_error=$e->getMessage();
	}	
	
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Parámetros gestión cartera | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_PRUpd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'Datos actualizados exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Ha ocurrido un error!',
                text: '".LSiqmlObs($msg_error)."',
                icon: 'warning'
            });
		});		
		</script>";
}
?>
<script>
function ActualizarDatos(campo){
	$("#edit_"+campo).val(1)
}

function traerDatos(id,tipoConcepto){
	$('.ibox-content').toggleClass('sk-loading',true);
	
	if(tipoConcepto==1){//TipoGestion
		$.ajax({
			type: "POST",
			data:{
				id:id,
				type:tipoConcepto
			},
			url: "ajx_param_cartera.php",
			success: function(response){
				$('#dvEvento').html(response);
				$('#dvResultadoGestion').html('');
				$('.ibox-content').toggleClass('sk-loading',false);
			}
		});
	}
	if(tipoConcepto==2){//Evento
		$.ajax({
			type: "POST",
			data:{
				id:id,
				type:tipoConcepto
			},
			url: "ajx_param_cartera.php",
			success: function(response){
				$('#dvResultadoGestion').html(response);
				$('.ibox-content').toggleClass('sk-loading',false);
			}
		});
	}
	
	
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
                    <h2>Parámetros gestión cartera</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
						<li>
                            <a href="#">Administración</a>
                        </li>
                        <li class="active">
                            <strong>Parámetros gestión cartera</strong>
                        </li>
                    </ol>
                </div>
            </div>
            <?php  //echo $Cons;?>
         <div class="wrapper wrapper-content">
			 		 
			 <div class="row">
			 	<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						 <div class="tabs-container">
							<ul class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Conceptos de cartera</a></li>
								<li><a data-toggle="tab" href="#tab-2"><i class="fa fa-sitemap"></i> Relaciones</a></li>
							</ul>
							<div class="tab-content">
								<div id="tab-1" class="tab-pane active">
									<form action="parametros_gestion_cartera.php" method="post" id="frmParam" class="form-horizontal">	
									<br>
										<div class="form-group">
											<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-check-square-o"></i> Conceptos de gestión de cartera</h3></label>
										</div>
										<div class="form-group">
											<div class="col-lg-2">
												<button class="btn btn-primary" type="submit" id="Guardar"><i class="fa fa-check"></i> Guardar datos</button>  
											</div>
										</div>										
										<div class="form-group">
											<label class="col-xs-12"><h3 class="bg-info p-xs b-r-sm">Tipos de gestión</h3></label>
										</div>
										<div class="form-group">
											<div class="col-lg-4">
												  <?php  
													$Cont=1;
													$row_TipoGestion=sqlsrv_fetch_array($SQL_TipoGestion);
													do{
													?>
													<div id="divTipoGestion_<?php echo $Cont;?>" class="form-group">
														 <div class="col-lg-6">
															 <input type="text" class="form-control" id="TipoGestion<?php echo $Cont;?>" name="TipoGestion[]" value="<?php echo $row_TipoGestion['TipoGestion'];?>" onChange="CambiarMetodo('MetodoTG<?php echo $Cont;?>');" />
														 </div>
														 <div class="col-lg-4">
															 <select class="form-control" id="TipoDestino<?php echo $Cont;?>" name="TipoDestino[]" onChange="CambiarMetodo('MetodoTG<?php echo $Cont;?>');">
																 <option value="1" <?php if($row_TipoGestion['TipoDestino']==1){echo "selected='selected'";}?>>LLAMADA</option>
																 <option value="2" <?php if($row_TipoGestion['TipoDestino']==2){echo "selected='selected'";}?>>VISITA</option>
															 </select>
														 </div>
														 <div class="col-lg-2">
															<input type="hidden" id="ID_TipoGestion<?php echo $Cont;?>" name="ID_TipoGestion[]" value="<?php echo $row_TipoGestion['ID_TipoGestion'];?>" />
															<input type="hidden" id="MetodoTG<?php echo $Cont;?>" name="MetodoTG[]" value="0" />
														 </div>
													</div>
												 <?php 
														$Cont++;
													} while($row_TipoGestion=sqlsrv_fetch_array($SQL_TipoGestion));
												 ?>
												 <div id="divTipoGestion_<?php echo $Cont;?>" class="form-group">
													 <div class="col-lg-6">
														 <input type="text" class="form-control" id="TipoGestion<?php echo $Cont;?>" name="TipoGestion[]" value="" placeholder="Ingrese el nuevo valor" />
													 </div>
													 <div class="col-lg-4">
														 <select class="form-control" id="TipoDestino<?php echo $Cont;?>" name="TipoDestino[]">
															 <option value="1">LLAMADA</option>
															 <option value="2">VISITA</option>
														 </select>
													 </div>
													 <div class="col-lg-2">
														<input type="hidden" id="MetodoTG<?php echo $Cont;?>" name="MetodoTG[]" value="1" />
														<button type="button" id="btnTipoGestion<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addFieldTG(this);"><i class="fa fa-plus"></i></button>	
													 </div>
												 </div>						
											</div>
										</div>	
										<div class="form-group">
											<label class="col-xs-12"><h3 class="bg-info p-xs b-r-sm">Eventos</h3></label>
										</div>
										<div class="form-group">
											<div class="col-lg-4">
												  <?php  
													$Cont=1;
													$row_Evento=sqlsrv_fetch_array($SQL_Evento);
													do{
													?>
													<div id="divEvento_<?php echo $Cont;?>" class="form-group">
														 <div class="col-lg-10">
															 <input type="text" class="form-control" id="NombreEvento<?php echo $Cont;?>" name="NombreEvento[]" value="<?php echo $row_Evento['NombreEvento'];?>" onChange="CambiarMetodo('MetodoEvento<?php echo $Cont;?>');" />
														 </div>
														 <div class="col-lg-2">
															<input type="hidden" id="ID_Evento<?php echo $Cont;?>" name="ID_Evento[]" value="<?php echo $row_Evento['ID_Evento'];?>" />
															<input type="hidden" id="MetodoEvento<?php echo $Cont;?>" name="MetodoEvento[]" value="0" />
														 </div>
													</div>
												 <?php 
														$Cont++;
													} while($row_Evento=sqlsrv_fetch_array($SQL_Evento));
												 ?>
												 <div id="divEvento_<?php echo $Cont;?>" class="form-group">
													 <div class="col-lg-10">
														 <input type="text" class="form-control" id="NombreEvento<?php echo $Cont;?>" name="NombreEvento[]" value="" placeholder="Ingrese el nuevo valor" />
													 </div>
													 <div class="col-lg-2">
														<input type="hidden" id="MetodoEvento<?php echo $Cont;?>" name="MetodoEvento[]" value="1" />
														<button type="button" id="btnNombreEvento<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addFieldEvento(this);"><i class="fa fa-plus"></i></button>	
													 </div>
												 </div>						
											</div>
										</div>
										<div class="form-group">
											<label class="col-xs-12"><h3 class="bg-info p-xs b-r-sm">Dirigido a</h3></label>
										</div>
										<div class="form-group">
											<div class="col-lg-4">
												  <?php  
													$Cont=1;
													$row_Dirigido=sqlsrv_fetch_array($SQL_Dirigido);
													do{
													?>
													<div id="divDirigido_<?php echo $Cont;?>" class="form-group">
														 <div class="col-lg-10">
															 <input type="text" class="form-control" id="NombreDirigido<?php echo $Cont;?>" name="NombreDirigido[]" value="<?php echo $row_Dirigido['NombreDirigido'];?>" onChange="CambiarMetodo('MetodoDirigido<?php echo $Cont;?>');" />
														 </div>
														 <div class="col-lg-2">
															<input type="hidden" id="ID_Dirigido<?php echo $Cont;?>" name="ID_Dirigido[]" value="<?php echo $row_Dirigido['ID_Dirigido'];?>" />
															<input type="hidden" id="MetodoDirigido<?php echo $Cont;?>" name="MetodoDirigido[]" value="0" />
														 </div>
													</div>
												 <?php 
														$Cont++;
													} while($row_Dirigido=sqlsrv_fetch_array($SQL_Dirigido));
												 ?>
												 <div id="divDirigido_<?php echo $Cont;?>" class="form-group">
													 <div class="col-lg-10">
														 <input type="text" class="form-control" id="NombreDirigido<?php echo $Cont;?>" name="NombreDirigido[]" value="" placeholder="Ingrese el nuevo valor" />
													 </div>
													 <div class="col-lg-2">
														<input type="hidden" id="MetodoDirigido<?php echo $Cont;?>" name="MetodoDirigido[]" value="1" />
														<button type="button" id="btnNombreDirigido<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addFieldDirigido(this);"><i class="fa fa-plus"></i></button>	
													 </div>
												 </div>						
											</div>
										</div>
										<div class="form-group">
											<label class="col-xs-12"><h3 class="bg-info p-xs b-r-sm">Causas de No Pago</h3></label>
										</div>
										<div class="form-group">
											<div class="col-lg-4">											
												  <?php  
													$Cont=1;
													$row_NoPago=sqlsrv_fetch_array($SQL_NoPago);
													do{
													?>
													<div id="divNoPago_<?php echo $Cont;?>" class="form-group">
														 <div class="col-lg-10">
															 <input type="text" class="form-control" id="CausaNoPago<?php echo $Cont;?>" name="CausaNoPago[]" value="<?php echo $row_NoPago['CausaNoPago'];?>" onChange="CambiarMetodo('MetodoNoPago<?php echo $Cont;?>');" />
														 </div>
														 <div class="col-lg-2">
															<input type="hidden" id="ID_CausaNoPago<?php echo $Cont;?>" name="ID_CausaNoPago[]" value="<?php echo $row_NoPago['ID_CausaNoPago'];?>" />
															<input type="hidden" id="MetodoNoPago<?php echo $Cont;?>" name="MetodoNoPago[]" value="0" />
														 </div>
													</div>
												 <?php 
														$Cont++;
													} while($row_NoPago=sqlsrv_fetch_array($SQL_NoPago));
												 ?>
												 <div id="divNoPago_<?php echo $Cont;?>" class="form-group">
													 <div class="col-lg-10">
														 <input type="text" class="form-control" id="CausaNoPago<?php echo $Cont;?>" name="CausaNoPago[]" value="" placeholder="Ingrese el nuevo valor" />
													 </div>
													 <div class="col-lg-2">
														<input type="hidden" id="MetodoNoPago<?php echo $Cont;?>" name="MetodoNoPago[]" value="1" />
														<button type="button" id="btnCausaNoPago<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addFieldNoPago(this);"><i class="fa fa-plus"></i></button>	
													 </div>
												 </div>						
											</div>
										</div>
										<div class="form-group">
											<label class="col-xs-12"><h3 class="bg-info p-xs b-r-sm">Resultados de gestión</h3></label>
										</div>
										<div class="form-group">
											<div class="col-lg-7">
												  <?php  
													$Cont=1;
													$row_ResGestion=sqlsrv_fetch_array($SQL_ResGestion);
													do{
													?>
													<div id="divResGestion_<?php echo $Cont;?>" class="form-group">
														 <div class="col-lg-5">
															 <input type="text" class="form-control" id="NombreResGestion<?php echo $Cont;?>" name="NombreResGestion[]" value="<?php echo $row_ResGestion['ResultadoGestion'];?>" onChange="CambiarMetodo('MetodoResGestion<?php echo $Cont;?>');" />
														 </div>
														 <div class="col-lg-5">
															 <textarea type="text" class="form-control" id="ComentariosResGestion<?php echo $Cont;?>" name="ComentariosResGestion[]" onChange="CambiarMetodo('MetodoResGestion<?php echo $Cont;?>');"><?php echo $row_ResGestion['ComentariosSugeridos'];?></textarea>
														 </div>
														 <div class="col-lg-2">
															<input type="hidden" id="ID_ResultadoGestion<?php echo $Cont;?>" name="ID_ResultadoGestion[]" value="<?php echo $row_ResGestion['ID_ResultadoGestion'];?>" />
															<input type="hidden" id="MetodoResGestion<?php echo $Cont;?>" name="MetodoResGestion[]" value="0" />
														 </div>
													</div>
												 <?php 
														$Cont++;
													} while($row_ResGestion=sqlsrv_fetch_array($SQL_ResGestion));
												 ?>
												 <div id="divResGestion_<?php echo $Cont;?>" class="form-group">
													 <div class="col-lg-5">
														 <input type="text" class="form-control" id="NombreResGestion<?php echo $Cont;?>" name="NombreResGestion[]" value="" placeholder="Ingrese el nuevo valor" />
													 </div>
													 <div class="col-lg-5">
															 <textarea type="text" class="form-control" id="ComentariosResGestion<?php echo $Cont;?>" name="ComentariosResGestion[]" placeholder="Ingrese el comentario sugerido (opcional)"></textarea>
														 </div>
													 <div class="col-lg-2">
														<input type="hidden" id="MetodoResGestion<?php echo $Cont;?>" name="MetodoResGestion[]" value="1" />
														<button type="button" id="btnResGestion<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addFieldResGestion(this);"><i class="fa fa-plus"></i></button>	
													 </div>
												 </div>						
											</div>
										</div>
										<input type="hidden" id="frmType" name="frmType" value="1" />
									</form>	 
								</div>
								<div id="tab-2" class="tab-pane">
									<form action="parametros_gestion_cartera.php" method="post" id="frmParam2" class="form-horizontal">
										<br>
										<div class="form-group">
											<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-check-square-o"></i> Relacione los conceptos de cartera</h3></label>
										</div>
										<div class="form-group">
											<div class="col-lg-2">
												<button class="btn btn-primary" type="submit" id="Guardar"><i class="fa fa-check"></i> Guardar datos</button>  
											</div>
										</div>
										<div class="form-group">
											<div class="col-lg-8">
												  <?php  
													$Cont=1;
													$row_RelConceptos=sqlsrv_fetch_array($SQL_RelConceptos);
													do{
														$SQL_TipoGestion=Seleccionar("tbl_Cartera_TipoGestion","*");
														$SQL_Evento=Seleccionar("tbl_Cartera_Evento","*");
														$SQL_ResGestion=Seleccionar("tbl_Cartera_ResultadoGestion","*");
													?>
													<div id="divRelConceptos_<?php echo $Cont;?>" class="form-group">
														<div class="col-lg-3">
															 <select class="form-control" id="TipoGestion<?php echo $Cont;?>" name="TipoGestion[]" onChange="CambiarMetodo('MetodoRelConceptos<?php echo $Cont;?>');">
																 <?php while($row_TipoGestion=sqlsrv_fetch_array($SQL_TipoGestion)){?>
																	<option value="<?php echo $row_TipoGestion['ID_TipoGestion'];?>" <?php if((isset($row_RelConceptos['ID_TipoGestion']))&&(strcmp($row_TipoGestion['ID_TipoGestion'],$row_RelConceptos['ID_TipoGestion'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoGestion['TipoGestion'];?></option>
															  <?php }?>
															 </select>
														 </div>
														 <div class="col-lg-3">
															 <select class="form-control" id="NombreEvento<?php echo $Cont;?>" name="NombreEvento[]" onChange="CambiarMetodo('MetodoRelConceptos<?php echo $Cont;?>');">
																 <?php while($row_Evento=sqlsrv_fetch_array($SQL_Evento)){?>
																	<option value="<?php echo $row_Evento['ID_Evento'];?>" <?php if((isset($row_RelConceptos['ID_Evento']))&&(strcmp($row_Evento['ID_Evento'],$row_RelConceptos['ID_Evento'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Evento['NombreEvento'];?></option>
															  <?php }?>
															 </select>
														 </div>
														 <div class="col-lg-4">
															 <select class="form-control" id="ResultadoGestion<?php echo $Cont;?>" name="ResultadoGestion[]" onChange="CambiarMetodo('MetodoRelConceptos<?php echo $Cont;?>');">
																 <?php while($row_ResGestion=sqlsrv_fetch_array($SQL_ResGestion)){?>
																	<option value="<?php echo $row_ResGestion['ID_ResultadoGestion'];?>" <?php if((isset($row_RelConceptos['ID_ResultadoGestion']))&&(strcmp($row_ResGestion['ID_ResultadoGestion'],$row_RelConceptos['ID_ResultadoGestion'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ResGestion['ResultadoGestion'];?></option>
															  <?php }?>
															 </select>
														 </div>
														 <div class="col-lg-2">
															<input type="hidden" id="MetodoRelConceptos<?php echo $Cont;?>" name="MetodoRelConceptos[]" value="0" />
															<button type="button" id="btnSeries<?php echo $Cont;?>" class="btn btn-warning btn-xs btn_del" onClick="delRow2(this);"><i class="fa fa-minus"></i> Remover</button>
														 </div>
													</div>
												 <?php 
														$Cont++;
													} while($row_RelConceptos=sqlsrv_fetch_array($SQL_RelConceptos));
												 ?>
												<?php 
													$SQL_TipoGestion=Seleccionar("tbl_Cartera_TipoGestion","*");
													$SQL_Evento=Seleccionar("tbl_Cartera_Evento","*");
													$SQL_ResGestion=Seleccionar("tbl_Cartera_ResultadoGestion","*");
												?>
												 <div id="divRelConceptos_<?php echo $Cont;?>" class="form-group">
													 <div class="col-lg-3">
														 <select class="form-control" id="TipoGestion<?php echo $Cont;?>" name="TipoGestion[]">
															 <option value="">Seleccione...</option>
															 <?php while($row_TipoGestion=sqlsrv_fetch_array($SQL_TipoGestion)){?>
																<option value="<?php echo $row_TipoGestion['ID_TipoGestion'];?>"><?php echo $row_TipoGestion['TipoGestion'];?></option>
														  <?php }?>
														 </select>
													 </div>
													 <div class="col-lg-3">
														 <select class="form-control" id="NombreEvento<?php echo $Cont;?>" name="NombreEvento[]">
															 <option value="">Seleccione...</option>
															 <?php while($row_Evento=sqlsrv_fetch_array($SQL_Evento)){?>
																<option value="<?php echo $row_Evento['ID_Evento'];?>"><?php echo $row_Evento['NombreEvento'];?></option>
														  <?php }?>
														 </select>
													 </div>
													 <div class="col-lg-4">
														 <select class="form-control" id="ResultadoGestion<?php echo $Cont;?>" name="ResultadoGestion[]">
															 <option value="">Seleccione...</option>
															 <?php while($row_ResGestion=sqlsrv_fetch_array($SQL_ResGestion)){?>
																<option value="<?php echo $row_ResGestion['ID_ResultadoGestion'];?>"><?php echo $row_ResGestion['ResultadoGestion'];?></option>
														  <?php }?>
														 </select>
													 </div>
													 <div class="col-lg-2">
														<input type="hidden" id="MetodoRelConceptos<?php echo $Cont;?>" name="MetodoRelConceptos[]" value="1" />
														<button type="button" id="btnRelConceptos<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addFieldRelConceptos(this);"><i class="fa fa-plus"></i> Añadir otro</button>	
													 </div>
												</div>			
											</div>
										</div>
										<input type="hidden" id="frmType" name="frmType" value="2" />
									</form>	 
								</div>
							</div>
						 </div>
						
												
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
			$("#frmParam").validate({
				 submitHandler: function(form){
					Swal.fire({
						title: "¿Está seguro que desea guardar los datos?",
						icon: "info",
						showCancelButton: true,
						confirmButtonText: "Si, confirmo",
						cancelButtonText: "No"
					}).then((result) => {
						if (result.isConfirmed) {
							$('.ibox-content').toggleClass('sk-loading',true);
							form.submit();
						}
					});	
				}
			});
			$(".select2").select2();
			$('.i-checks').iCheck({
				 checkboxClass: 'icheckbox_square-green',
				 radioClass: 'iradio_square-green',
			  });			
        });
    </script>
<script>
function addFieldTG(btn){//Clonar div
	var clickID = parseInt($(btn).parent('div').parent('div').attr('id').replace('divTipoGestion_',''));
	//alert($(btn).parent('div').attr('id'));
	//alert(clickID);
	var newID = (clickID+1);
	
	//var $example = $(".select2").select2();
	//$example.select2("destroy");

	$newClone = $('#divTipoGestion_'+clickID).clone(true);

	//div
	$newClone.attr("id",'divTipoGestion_'+newID);

	//select
	$newClone.children("div").eq(1).children("select").eq(0).attr('id','TipoDestino'+newID);

	//inputs
	$newClone.children("div").eq(0).children("input").eq(0).attr('id','TipoGestion'+newID);
	$newClone.children("div").eq(2).children("input").eq(0).attr('id','MetodoTG'+newID);

	//button
	$newClone.children("div").eq(2).children("button").eq(0).attr('id','btnTipoGestion'+newID);

	$newClone.insertAfter($('#divTipoGestion_'+clickID));

	document.getElementById('btnTipoGestion'+clickID).innerHTML="<i class='fa fa-minus'></i>";
	document.getElementById('btnTipoGestion'+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById('btnTipoGestion'+clickID).setAttribute('onClick','delRow2(this);');
	
	//Limpiar campos
	document.getElementById('TipoGestion'+newID).value='';
	document.getElementById('TipoDestino'+newID).value='1';
}
	
function addFieldEvento(btn){//Clonar div
	var clickID = parseInt($(btn).parent('div').parent('div').attr('id').replace('divEvento_',''));
//	alert($(btn).parent('div').parent('div').attr('id'));
//	alert(clickID);
	var newID = (clickID+1);

	$newClone = $('#divEvento_'+clickID).clone(true);

	//div
	$newClone.attr("id",'divEvento_'+newID);

	//inputs
	$newClone.children("div").eq(0).children("input").eq(0).attr('id','NombreEvento'+newID);
	$newClone.children("div").eq(1).children("input").eq(0).attr('id','MetodoEvento'+newID);

	//button
	$newClone.children("div").eq(1).children("button").eq(0).attr('id','btnNombreEvento'+newID);

	$newClone.insertAfter($('#divEvento_'+clickID));

	document.getElementById('btnNombreEvento'+clickID).innerHTML="<i class='fa fa-minus'></i>";
	document.getElementById('btnNombreEvento'+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById('btnNombreEvento'+clickID).setAttribute('onClick','delRow2(this);');
	
	//Limpiar campos
	document.getElementById('NombreEvento'+newID).value='';	
}

function addFieldResGestion(btn){//Clonar div
	var clickID = parseInt($(btn).parent('div').parent('div').attr('id').replace('divResGestion_',''));
//	alert($(btn).parent('div').parent('div').attr('id'));
//	alert(clickID);
	var newID = (clickID+1);

	$newClone = $('#divResGestion_'+clickID).clone(true);

	//div
	$newClone.attr("id",'divResGestion_'+newID);

	//inputs
	$newClone.children("div").eq(0).children("input").eq(0).attr('id','NombreResGestion'+newID);
	$newClone.children("div").eq(1).children("textarea").eq(0).attr('id','ComentariosResGestion'+newID);
	$newClone.children("div").eq(2).children("input").eq(0).attr('id','MetodoResGestion'+newID);

	//button
	$newClone.children("div").eq(2).children("button").eq(0).attr('id','btnResGestion'+newID);

	$newClone.insertAfter($('#divResGestion_'+clickID));

	document.getElementById('btnResGestion'+clickID).innerHTML="<i class='fa fa-minus'></i>";
	document.getElementById('btnResGestion'+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById('btnResGestion'+clickID).setAttribute('onClick','delRow2(this);');
	
	//Limpiar campos
	document.getElementById('NombreResGestion'+newID).value='';	
	document.getElementById('ComentariosResGestion'+newID).value='';
}
	
function addFieldDirigido(btn){//Clonar div
	var clickID = parseInt($(btn).parent('div').parent('div').attr('id').replace('divDirigido_',''));
//	alert($(btn).parent('div').parent('div').attr('id'));
//	alert(clickID);
	var newID = (clickID+1);

	$newClone = $('#divDirigido_'+clickID).clone(true);

	//div
	$newClone.attr("id",'divDirigido_'+newID);

	//inputs
	$newClone.children("div").eq(0).children("input").eq(0).attr('id','NombreDirigido'+newID);
	$newClone.children("div").eq(1).children("input").eq(0).attr('id','MetodoDirigido'+newID);

	//button
	$newClone.children("div").eq(1).children("button").eq(0).attr('id','btnNombreDirigido'+newID);

	$newClone.insertAfter($('#divDirigido_'+clickID));

	document.getElementById('btnNombreDirigido'+clickID).innerHTML="<i class='fa fa-minus'></i>";
	document.getElementById('btnNombreDirigido'+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById('btnNombreDirigido'+clickID).setAttribute('onClick','delRow2(this);');
	
	//Limpiar campos
	document.getElementById('NombreDirigido'+newID).value='';	
}
	
function addFieldNoPago(btn){//Clonar div
	var clickID = parseInt($(btn).parent('div').parent('div').attr('id').replace('divNoPago_',''));
//	alert($(btn).parent('div').parent('div').attr('id'));
//	alert(clickID);
	var newID = (clickID+1);

	$newClone = $('#divNoPago_'+clickID).clone(true);

	//div
	$newClone.attr("id",'divNoPago_'+newID);

	//inputs
	$newClone.children("div").eq(0).children("input").eq(0).attr('id','CausaNoPago'+newID);
	$newClone.children("div").eq(1).children("input").eq(0).attr('id','MetodoNoPago'+newID);

	//button
	$newClone.children("div").eq(1).children("button").eq(0).attr('id','btnCausaNoPago'+newID);

	$newClone.insertAfter($('#divNoPago_'+clickID));

	document.getElementById('btnCausaNoPago'+clickID).innerHTML="<i class='fa fa-minus'></i>";
	document.getElementById('btnCausaNoPago'+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById('btnCausaNoPago'+clickID).setAttribute('onClick','delRow2(this);');
	
	//Limpiar campos
	document.getElementById('CausaNoPago'+newID).value='';	
}
	
function addFieldRelConceptos(btn){//Clonar div
	var clickID = parseInt($(btn).parent('div').parent('div').attr('id').replace('divRelConceptos_',''));
//	alert($(btn).parent('div').parent('div').attr('id'));
//	alert(clickID);
	var newID = (clickID+1);

	$newClone = $('#divRelConceptos_'+clickID).clone(true);

	//div
	$newClone.attr("id",'divRelConceptos_'+newID);
	
	//select
	$newClone.children("div").eq(0).children("select").eq(0).attr('id','TipoGestion'+newID);
	$newClone.children("div").eq(1).children("select").eq(0).attr('id','NombreEvento'+newID);
	$newClone.children("div").eq(2).children("select").eq(0).attr('id','ResultadoGestion'+newID);

	//inputs
	$newClone.children("div").eq(3).children("input").eq(0).attr('id','MetodoRelConceptos'+newID);

	//button
	$newClone.children("div").eq(3).children("button").eq(0).attr('id','btnRelConceptos'+newID);

	$newClone.insertAfter($('#divRelConceptos_'+clickID));

	document.getElementById('btnRelConceptos'+clickID).innerHTML="<i class='fa fa-minus'></i> Remover";
	document.getElementById('btnRelConceptos'+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById('btnRelConceptos'+clickID).setAttribute('onClick','delRow2(this);');
	
	//Limpiar campos
	document.getElementById('TipoGestion'+newID).value='';
	document.getElementById('NombreEvento'+newID).value='';
	document.getElementById('ResultadoGestion'+newID).value='';
}
	
function delRow(){//Eliminar div
	$(this).parent('div').parent('div').remove();
}
	
function delRow2(btn){//Eliminar div
	$(btn).parent('div').parent('div').remove();
}
	
function CambiarMetodo(id){
	var inpMetodo=document.getElementById(id);
	inpMetodo.value=2;
}	
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>