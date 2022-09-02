<?php 
require_once("includes/conexion.php");

$Cons_Menu="Select * From uvw_tbl_Categorias Where ID_Padre=0 and EstadoCategoria=1 and ID_Permiso IN (Select ID_Permiso From uvw_tbl_PermisosPerfiles Where ID_PerfilUsuario='".$_SESSION['Perfil']."')";
$SQL_Menu=sqlsrv_query($conexion,$Cons_Menu,array(),array( "Scrollable" => 'Buffered' ));
$Num_Menu=sqlsrv_num_rows($SQL_Menu);

?>
      <nav class="navbar-default navbar-static-side" role="navigation">
        <div class="sidebar-collapse">
            <ul class="nav metismenu" id="side-menu">
                <li class="nav-header">
                    <div class="dropdown profile-element">
	                    <img src="img/img_logo_menu.png" width="150" height="45" alt=""/>
	                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
							<span class="clear">
								<br>
								<span class="block m-t-xs"><strong class="font-bold"><?php echo $_SESSION['NomUser'];?></strong></span> 
								<span class="text-muted text-xs block"><?php echo $_SESSION['NomPerfil'];?></span>
							</span>
						</a>
	                </div>
                    <div class="logo-element">
                    	<img src="img/img_logo_slim.png" class="img-circle" alt="" width="30" height="30"/> 
                    </div>
                </li>
                <li class="active">
                    <a class="alnk" href="<?php echo isset($_SESSION['Index']) ? $_SESSION['Index'] : "index1.php"; ?>"><i class="fa fa-home"></i> <span class="nav-label">Inicio</span></a>
                </li>
			<?php while($row_Menu=sqlsrv_fetch_array($SQL_Menu)){
					$arrow="";
					$lnk="class='alnk'";
					
					$Cons_MenuLvl2="Select * From uvw_tbl_Categorias Where ID_Padre=".$row_Menu['ID_Categoria']." and EstadoCategoria=1";
					$SQL_MenuLvl2=sqlsrv_query($conexion,$Cons_MenuLvl2,array(),array( "Scrollable" => 'static' ));
					$Num_MenuLvl2=sqlsrv_num_rows($SQL_MenuLvl2);
					
					if($Num_MenuLvl2>=1){
						$arrow=" <span class='fa arrow'></span>";
						$lnk="";
					}
	
					if($row_Menu['URL']!="#"){
						$URL=$row_Menu['URL']."?id=".base64_encode($row_Menu['ID_Categoria']);
						if($row_Menu['ParamAdicionales']!=""){
							$URL=$URL."&".$row_Menu['ParamAdicionales'];
						}
					}else{
						$URL=$row_Menu['URL'];
					}
					echo "<li>
							<a ".$lnk." href='".$URL."'><i class='fa fa-sitemap'></i> <span class='nav-label'>".$row_Menu['NombreCategoria']." </span>".$arrow."</a>
							";//li2
					
					if($Num_MenuLvl2>=1){
							$lnk="class='alnk'";
							echo "<ul class='nav nav-second-level collapse'>";//ul2
							while($row_MenuLvl2=sqlsrv_fetch_array($SQL_MenuLvl2)){
								$Cons_MenuLvl3="Select * From uvw_tbl_Categorias Where ID_Padre=".$row_MenuLvl2['ID_Categoria']." and EstadoCategoria=1";
								$SQL_MenuLvl3=sqlsrv_query($conexion,$Cons_MenuLvl3,array(),array( "Scrollable" => 'static' ));
								$Num_MenuLvl3=sqlsrv_num_rows($SQL_MenuLvl3);
								$arrow_Lvl3="";
								if($Num_MenuLvl3>=1){
									$arrow_Lvl3=" <span class='fa arrow'></span>";
									$lnk="";
								}
								if($row_MenuLvl2['URL']!="#"){
									$URL2=$row_MenuLvl2['URL']."?id=".base64_encode($row_MenuLvl2['ID_Categoria']);
									if($row_MenuLvl2['ParamAdicionales']!=""){
										$URL2=$URL2."&".$row_MenuLvl2['ParamAdicionales'];
									}
								}else{
									$URL2=$row_MenuLvl2['URL'];
								}
								echo "
									<li>
										<a ".$lnk." href='".$URL2."'>".$row_MenuLvl2['NombreCategoria'].$arrow_Lvl3."</a>
									";//li1
								if($Num_MenuLvl3>=1){
									$lnk="class='alnk'";
									echo "<ul class='nav nav-third-level'>";//ul1
									while($row_MenuLvl3=sqlsrv_fetch_array($SQL_MenuLvl3)){
										if($row_MenuLvl3['URL']!="#"){
											$URL3=$row_MenuLvl3['URL']."?id=".base64_encode($row_MenuLvl3['ID_Categoria']);
											if($row_MenuLvl3['ParamAdicionales']!=""){
												$URL3=$URL3."&".$row_MenuLvl3['ParamAdicionales'];
											}
										}else{
											$URL3=$row_MenuLvl3['URL'];
											$lnk="";
										}
										echo "<li>
												<a ".$lnk." href='".$URL3."'>".$row_MenuLvl3['NombreCategoria']."</a>
											  </li>";
									}
									echo "
										  </ul>";//ul1
								}
								echo "
								   </li>
								";//li1
							}
							echo "
								</ul>";//ul2
						}
					echo "
					</li>
					";//li2					
				 }?>
				<?php if(PermitirFuncion([1701,1702,1703,1704])){?>
            	<li>
                    <a href="#"><i class="fa fa-file-text"></i> <span class="nav-label">Formularios</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level">
						<?php if(PermitirFuncion(1702)){?><li><a class="alnk" href="consultar_frm_temperatura.php">Monitoreo de temperatura</a></li><?php }?>
                  		<?php if(PermitirFuncion(1703)){?><li><a class="alnk" href="consultar_frm_fitosanitario.php">Monitoreo estado fitosanitario</a></li><?php }?>
                   		<?php if(PermitirFuncion(1701)){?><li><a class="alnk" href="consultar_frm_analisis_lab.php">Monitoreo análisis de laboratorio</a></li><?php }?>
						<?php if(PermitirFuncion(1704)){?><li><a class="alnk" href="consultar_frm_eval_tecnico.php">Evaluación de técnicos</a></li><?php }?>
						<?php if(PermitirFuncion(1702)){?>
						<li>
                   			<a href="#">Informes <span class="fa arrow"></span></a>
							<ul class='nav nav-third-level'>
								<?php if(PermitirFuncion(1702)){?><li><a class="alnk" href="dsb_temp_puerto.php">Dashboard Monitoreo de temperatura</a></li><?php }?>
							</ul>
						</li>
						<?php }?>
                    </ul>
                </li>
                <?php }?>
           		<?php if(PermitirFuncion(207)||PermitirFuncion(208)||PermitirFuncion(209)||PermitirFuncion(210)){?>
            	<li>
                    <a href="#"><i class="fa fa-file-text"></i> <span class="nav-label">Gesti&oacute;n de archivos</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level">
						<?php if(PermitirFuncion(207)){?><li><a class="alnk" href="gestionar_documentos.php">Gestionar documentos</a></li><?php }?>
                  		<?php if(PermitirFuncion(208)){?><li><a class="alnk" href="gestionar_informes.php">Gestionar informes</a></li><?php }?>
                   		<?php if(PermitirFuncion(209)){?><li><a class="alnk" href="gestionar_productos.php">Gestionar productos</a></li><?php }?>
						<?php if(PermitirFuncion(210)){?><li><a class="alnk" href="gestionar_calidad.php">Gestionar calidad</a></li><?php }?>
                    </ul>
                </li>
                <?php }?>
				<?php if(PermitirFuncion(501)||PermitirFuncion(502)||PermitirFuncion(505)||PermitirFuncion(506)||PermitirFuncion(1001)||PermitirFuncion(1002)){?>
				<li>
                    <a href="#"><i class="fa fa-users"></i> <span class="nav-label">Datos maestros</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level">
						<?php if(PermitirFuncion(501)){?><li><a class="alnk" href="socios_negocios.php">Crear socios de negocios</a></li><?php }?>						
						<?php if(PermitirFuncion(1001)){?><li><a class="alnk" href="articulos.php">Crear artículos</a></li><?php }?>
						<?php if(PermitirFuncion(1209)){?><li><a class="alnk" href="lista_materiales.php">Crear lista de materiales</a></li><?php }?>		
						<?php if(PermitirFuncion(220)){?><li><a class="alnk" href="consultar_plantilla_actividades.php">Plantilla de actividades</a></li><?php }?>						
						<li>
                   			<a href="#">Consultas <span class="fa arrow"></span></a>
							<ul class='nav nav-third-level'>
								<?php if(PermitirFuncion(502)){?><li><a class="alnk" href="consultar_socios_negocios.php">Consultar socios de negocios</a></li><?php }?>
								<?php if(PermitirFuncion(1002)){?><li><a class="alnk" href="consultar_articulos.php">Consultar artículos</a></li><?php }?>
								<?php if(PermitirFuncion(1208)){?><li><a class="alnk" href="consultar_lista_materiales.php">Consultar lista de materiales</a></li><?php }?>
							</ul>
						</li>						
                    </ul>
                </li>
				<?php }?>
				<?php if(PermitirFuncion(1101)||PermitirFuncion(1102)){?>
				<li>
                    <a href="#"><i class="fa fa-suitcase"></i> <span class="nav-label">CRM</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level">
						<?php if(PermitirFuncion(401)){?><li><a class="alnk" href="index1.php">Cotizador</a></li><?php }?>
						<?php if(PermitirFuncion(1101)){?><li><a class="alnk" href="oportunidad.php">Oportunidad de venta</a></li><?php }?>
						<?php if(PermitirFuncion(303)){?><li><a class="alnk" href="gestionar_actividades.php">Actividades</a></li><?php }?>
						<?php if(PermitirFuncion(401)){?><li><a class="alnk" href="oferta_venta.php">Oferta de venta</a></li><?php }?>
						<?php if(PermitirFuncion(402)){?><li><a class="alnk" href="orden_venta.php">Orden de venta</a></li><?php }?>	
						<?php if(PermitirFuncion(506)){?><li><a class="alnk" href="consultar_contratos.php">Contratos</a></li><?php }?>
						<li>
                   			<a href="#">Consultas <span class="fa arrow"></span></a>
							<ul class='nav nav-third-level'>
								<?php if(PermitirFuncion(1102)){?><li><a class="alnk" href="consultar_oportunidad.php">Consultar oportunidades</a></li><?php }?>	
								<?php if(PermitirFuncion(405)){?><li><a class="alnk" href="consultar_oferta_venta.php">Consultar oferta de venta</a></li><?php }?>
								<?php if(PermitirFuncion(406)){?><li><a class="alnk" href="consultar_orden_venta.php">Consultar orden de venta</a></li><?php }?>
							</ul>
						</li>							
                    </ul>
                </li>
				<?php }?>
				<?php if(PermitirFuncion(301)||PermitirFuncion(303)||PermitirFuncion(305)||PermitirFuncion(306)||PermitirFuncion(307)||PermitirFuncion(308)||PermitirFuncion(310)||PermitirFuncion(311)||PermitirFuncion(312)||PermitirFuncion(316)){?>
				<li>
                    <a href="#"><i class="fa fa-tasks"></i> <span class="nav-label">Servicios</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
						<?php if(PermitirFuncion(312)){?><li><a href="programacion_rutas.php" target="_blank">Programación de servicios</a></li><?php }?>
						<?php if(PermitirFuncion(316)){?><li><a href="despacho_rutas.php">Despacho de servicios</a></li><?php }?>
						<?php if(PermitirFuncion(301)){?><li><a class="alnk" href="gestionar_llamadas_servicios.php">Llamadas de servicio</a></li><?php }?>
						<?php if(PermitirFuncion(303)){?><li><a class="alnk" href="gestionar_actividades.php">Actividades</a></li><?php }?>
						<?php if(PermitirFuncion(305)||PermitirFuncion(306)||PermitirFuncion(307)){?>
							<li>
								<a href="#">Calendarios <span class="fa arrow"></span></a>
								<ul class='nav nav-third-level'>
									<?php if(PermitirFuncion(305)){?><li><a class="alnk" href="calendario_actividades.php">Calendario de clientes</a></li><?php }?>
									<?php if(PermitirFuncion(306)){?><li><a class="alnk" href="calendario_actividades_tecnico.php">Calendario de técnicos</a></li><?php }?>
									<?php if(PermitirFuncion(307)){?><li><a class="alnk" target="_blank" href="calendario_actividades_tecnico_ajx.php">Calendario dashboard</a></li><?php }?>
									<?php if(PermitirFuncion(305)){?><li><a class="alnk" href="calendario_cronograma.php">Calendario de cronograma de servicios</a></li><?php }?>
								</ul>
							</li>
						<?php }?>
						<?php if(PermitirFuncion(308)){?>
							<li>
								<a href="#">Mapas <span class="fa arrow"></span></a>
								<ul class='nav nav-third-level'>
									<?php if(PermitirFuncion(308)){?><li><a class="alnk" href="maps_actividades_tecnicos.php">Mapa de técnicos</a></li><?php }?>
								</ul>
							</li>
						<?php }?>
						<?php if(PermitirFuncion(310)||PermitirFuncion(311)||PermitirFuncion(317)||PermitirFuncion(318)||PermitirFuncion(319)){?>
							<li>
								<a href="#">Asistentes <span class="fa arrow"></span></a>
								<ul class='nav nav-third-level'>
									<?php if(PermitirFuncion(310)){?><li><a class="alnk" href="creacion_ot_lote.php">Creación de OT en lote</a></li><?php }?>
									<?php if(PermitirFuncion(311)){?><li><a class="alnk" href="cierre_ot_lote.php">Cierre de OT en lote</a></li><?php }?>
									<?php if(PermitirFuncion(317)){?><li><a class="alnk" href="cambio_producto_ot.php">Cambio de productos en lote</a></li><?php }?>
									<?php if(PermitirFuncion(319)){?><li><a class="alnk" href="consultar_despacho_lote.php">Despachos en lote</a></li><?php }?>
									<?php if(PermitirFuncion(318)){?><li><a class="alnk" href="cronograma_servicios.php">Cronograma de servicios</a></li><?php }?>
								</ul>
							</li>
						<?php }?>
						<?php if(PermitirFuncion(314)||PermitirFuncion(315)||PermitirFuncion(408)){?>
							<li>
								<a href="#">Informes <span class="fa arrow"></span></a>
								<ul class='nav nav-third-level'>
									<li><a class="alnk" href="dsb_servicios.php?id=<?php echo base64_encode('1');?>">Dashboard de servicios</a></li>
									<?php if(PermitirFuncion(314)){?><li><a class="alnk" href="consultar_operaciones.php">Gestión de operaciones</a></li><?php }?>
									<?php if(PermitirFuncion(315)){?><li><a class="alnk" href="programacion_clientes.php">Programación de clientes</a></li><?php }?>
									<?php if(PermitirFuncion(414)){?><li><a class="alnk" href="impresion_orden_servicio.php">Impresión de OT</a></li><?php }?>
								</ul>
							</li>
						<?php }?>
                    </ul>
                </li>
				<?php }?>				
				<?php if(PermitirFuncion([401,402,404,405,406,407,408,409,410,411,412,413,414,415])){?>
				<li>
                    <a href="#"><i class="fa fa-tags"></i> <span class="nav-label">Ventas</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
						<?php if(PermitirFuncion(401)){?><li><a class="alnk" href="oferta_venta.php">Oferta de venta</a></li><?php }?>
						<?php if(PermitirFuncion(402)){?><li><a class="alnk" href="orden_venta.php">Orden de venta</a></li><?php }?>
						<?php if(PermitirFuncion(404)){?><li><a class="alnk" href="entrega_venta.php">Entrega de venta</a></li><?php }?>
						<?php if(PermitirFuncion(409)){?><li><a class="alnk" href="devolucion_venta.php">Devolución de venta</a></li><?php }?>
						<?php if(PermitirFuncion(411)){?><li><a class="alnk" href="factura_venta.php">Factura de venta</a></li><?php }?>						
						<li>
                   			<a href="#">Consultas <span class="fa arrow"></span></a>
							<ul class='nav nav-third-level'>
								<?php if(PermitirFuncion(405)){?><li><a class="alnk" href="consultar_oferta_venta.php">Consultar oferta de venta</a></li><?php }?>
								<?php if(PermitirFuncion(406)){?><li><a class="alnk" href="consultar_orden_venta.php">Consultar orden de venta</a></li><?php }?>
								<?php if(PermitirFuncion(407)){?><li><a class="alnk" href="consultar_entrega_venta.php">Consultar entrega de venta</a></li><?php }?>
								<?php if(PermitirFuncion(410)){?><li><a class="alnk" href="consultar_devolucion_venta.php">Consultar devolución de venta</a></li><?php }?>
								<?php if(PermitirFuncion(412)){?><li><a class="alnk" href="consultar_factura_venta.php">Consultar factura de venta</a></li><?php }?>
							</ul>
						</li>	
						<?php if(PermitirFuncion([408,414])){?>
						<li>
							<a href="#">Asistentes <span class="fa arrow"></span></a>
							<ul class='nav nav-third-level'>								
								<?php if(PermitirFuncion(408)){?><li><a class="alnk" href="facturacion_orden_servicio.php">Facturación de OT</a></li><?php }?>
								<?php if(PermitirFuncion(414)){?><li><a class="alnk" href="impresion_orden_servicio.php">Impresión de OT</a></li><?php }?>
							</ul>
						</li>		
						<?php }?>
						<?php if(PermitirFuncion([413,415])){?>
						<li>
							<a href="#">Informes <span class="fa arrow"></span></a>
							<ul class='nav nav-third-level'>
								<?php if(PermitirFuncion(413)){?><li><a class="alnk" href="informe_analisis_venta.php">Análisis de ventas</a></li><?php }?>
								<?php if(PermitirFuncion(415)){?><li><a class="alnk" href="dsb_facturacion.php">Dashboard facturación</a></li><?php }?>
							</ul>
						</li>		
						<?php }?>
                    </ul>
                </li>
				<?php }?>
				<?php if(PermitirFuncion(1502)){?>
				<li>
                    <a href="#"><i class="fa fa-newspaper-o"></i> <span class="nav-label">Facturación electrónica</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
						<?php if(PermitirFuncion(1502)){?><li><a class="alnk" href="comprobantes_fe.php">Comprobantes electrónicos</a></li><?php }?>				
                    </ul>
                </li>
				<?php }?>
				<?php if(PermitirFuncion([1201,1202,1203,1204,1205,1206,1207])){?>
				<li>
                    <a href="#"><i class="fa fa-cubes"></i> <span class="nav-label">Inventario</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
						<?php if(PermitirFuncion(1201)){?><li><a class="alnk" href="solicitud_salida.php">Solicitud de traslado</a></li><?php }?>
						<?php if(PermitirFuncion(1203)){?><li><a class="alnk" href="traslado_inventario.php">Traslado de inventario</a></li><?php }?>
						<?php if(PermitirFuncion(1205)){?><li><a class="alnk" href="salida_inventario.php">Salida de traslado</a></li><?php }?>
						 <li>
                   			<a href="#">Consultas <span class="fa arrow"></span></a>
							<ul class='nav nav-third-level'>
								<?php if(PermitirFuncion(1202)){?><li><a class="alnk" href="consultar_solicitud_salida.php">Consultar solicitud de traslado</a></li><?php }?>
								<?php if(PermitirFuncion(1204)){?><li><a class="alnk" href="consultar_traslado_inventario.php">Consultar traslado de inventario</a></li><?php }?>
								<?php if(PermitirFuncion(1206)){?><li><a class="alnk" href="consultar_salida_inventario.php">Consultar salida de traslado</a></li><?php }?>
							</ul>
						</li>
						<?php if(PermitirFuncion(1207)){?>
						<li>
                   			<a href="#">Informes <span class="fa arrow"></span></a>
							<ul class='nav nav-third-level'>
								<?php if(PermitirFuncion(1207)){?><li><a class="alnk" href="informe_stock_almacen.php">Informe de stock de almacén</a></li><?php }?>
							</ul>
						</li>
						<?php }?>
                    </ul>
                </li>
				<?php }?>
				<?php if(PermitirFuncion([701,702,703,704,705,706,707,708,709,714,715])){?>
				<li>
                    <a href="#"><i class="fa fa-shopping-cart"></i> <span class="nav-label">Compras</span><span class="fa arrow"></span></a>
					 <ul class="nav nav-second-level collapse">
						 <?php if(PermitirFuncion(701)){?><li><a class="alnk" href="solicitud_compra.php">Solicitud de compra</a></li><?php }?>
						 <?php if(PermitirFuncion(702)){?><li><a class="alnk" href="orden_compra.php">Orden de compra</a></li><?php }?>
						 <?php if(PermitirFuncion(703)){?><li><a class="alnk" href="entrada_compra.php">Entrada de compras</a></li><?php }?>
						 <?php if(PermitirFuncion(714)){?><li><a class="alnk" href="devolucion_compra.php">Devolución de compras</a></li><?php }?>						 
						 <?php if(PermitirFuncion(704)){?><li><a class="alnk" href="factura_compra.php">Factura de compras</a></li><?php }?>
						 <?php if(PermitirFuncion([705,706,707,708])){?>
						 <li>
                   			<a href="#">Consultas <span class="fa arrow"></span></a>
							<ul class='nav nav-third-level'>
								<?php if(PermitirFuncion(705)){?><li><a href="consultar_solicitud_compra.php">Consultar solicitud de compra</a></li><?php }?>
								<?php if(PermitirFuncion(706)){?><li><a href="consultar_orden_compra.php">Consultar orden de compra</a></li><?php }?>
								<?php if(PermitirFuncion(707)){?><li><a href="consultar_entrada_compra.php">Consultar entrada de compras</a></li><?php }?>
								<?php if(PermitirFuncion(715)){?><li><a href="consultar_devolucion_compra.php">Consultar devolución de compras</a></li><?php }?>								
								<?php if(PermitirFuncion(708)){?><li><a href="consultar_factura_compra.php">Consultar factura de compras</a></li><?php }?>
							</ul>
						</li>
						<?php }?>
						<?php if(PermitirFuncion(709)){?>
						<li>
                   			<a href="#">Informes <span class="fa arrow"></span></a>
							<ul class='nav nav-third-level'>
								<?php if(PermitirFuncion(709)){?><li><a href="inf_informe_trazabilidad.php">Informe de trazabilidad</a></li><?php }?>
							</ul>
						</li>
						<?php }?>
					</ul>
                </li>
				<?php }?>		
				<?php if(PermitirFuncion(601)){?>
				<li>
                    <a href="#"><i class="fa fa-users"></i> <span class="nav-label">Proveedores</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
						<li>
							<a href="#">Documentos <span class="fa arrow"></span></a>
							<ul class='nav nav-third-level'>
								<li><a class="alnk" href="prov_ordenes_compra.php">Ordenes de compra</a></li>
								<li><a class="alnk" href="prov_entradas_compra.php">Entradas de mercancías/servicios</a></li>
								<li><a class="alnk" href="prov_facturas_proveedores.php">Facturas de proveedor</a></li>
							</ul>
						</li>
						<li>
							<a href="#">Estados de cuenta <span class="fa arrow"></span></a>
							<ul class='nav nav-third-level'>
								<li><a class="alnk" href="prov_pagos_efectuados.php">Pagos efectuados</a></li>
							</ul>
						</li>
						<li>
							<a href="#">Certificados <span class="fa arrow"></span></a>
							<ul class='nav nav-third-level'>
								<li><a class="alnk" href="prov_certificado_retenciones.php">Certificado de retenciones</a></li>
							</ul>
						</li>
                    </ul>
                </li>
				<?php }?>
				<?php if(PermitirFuncion(801)){?>
				<li>
                    <a href="#"><i class="fa fa-suitcase"></i> <span class="nav-label">Cartera</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level">
						<?php if(PermitirFuncion(801)){?><li><a class="alnk" href="consultar_cliente_cartera.php">Consultar cliente</a></li><?php }?>
						<?php if(PermitirFuncion(801)){?><li><a class="alnk" href="consultar_gestiones.php">Consultar gestiones</a></li><?php }?>						
						<?php if(PermitirFuncion(802)){?><li><a class="alnk" href="reporte_gestiones_cartera.php">Reporte de gestiones</a></li><?php }?>
                    </ul>
                </li>
				<?php }?>
				<?php if(PermitirFuncion(1601)){?>
				<li>
                    <a href="#"><i class="fa fa-wrench"></i> <span class="nav-label">Mantenimiento</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level">
						<?php if(PermitirFuncion(1601)){?><li><a class="alnk" href="consultar_tarjeta_equipo.php">Tarjetas de equipos</a></li><?php }?>
						<?php if(PermitirFuncion(1601)){?><li><a class="alnk" href="#">Base de datos de soluciones</a></li><?php }?>
                    </ul>
                </li>
				<?php }?>
	            <li>
                    <a href="#"><i class="fa fa-gears"></i> <span class="nav-label">Administraci&oacute;n</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level">
						<li><a href="cambiar_clave.php">Cambiar contrase&ntilde;a</a></li>
						<?php if(PermitirFuncion(222)){?><li><a class="alnk" href="cambiar_firma.php">Actualizar mi firma</a></li><?php }?>
						<?php if(PermitirFuncion(201)){?><li><a class="alnk" href="gestionar_categorias.php">Gestionar categor&iacute;as</a></li><?php }?>
						<?php if(PermitirFuncion(202)){?><li><a class="alnk" href="gestionar_usuarios.php">Gestionar usuarios</a></li><?php }?>
                  		<?php if(PermitirFuncion(203)){?><li><a class="alnk" href="gestionar_perfiles.php">Gestionar perfiles</a></li><?php }?>
						<?php if(PermitirFuncion(214)){?><li><a class="alnk" href="gestionar_series.php">Gestionar series</a></li><?php }?>
						<?php if(PermitirFuncion(204)||PermitirFuncion(211)||PermitirFuncion(1501)||PermitirFuncion(215)||PermitirFuncion(803)||PermitirFuncion(216)||PermitirFuncion(217)||PermitirFuncion(218)||PermitirFuncion(219)){?>
						<li>
                   			<a href="#">Parámetros del sistema<span class="fa arrow"></span></a>
							<ul class='nav nav-third-level'>
								<?php if(PermitirFuncion(204)){?><li><a class="alnk" href="parametros_generales.php">Parámetros generales</a></li><?php }?>
								<?php if(PermitirFuncion(211)){?><li><a class="alnk" href="informes_sap_parametrizar.php">Parametrizar Informes SAP B1</a></li><?php }?>
								<?php if(PermitirFuncion(1501)){?><li><a class="alnk" href="parametros_fe.php">Parámetros Facturación Electrónica</a></li><?php }?>
								<?php if(PermitirFuncion(215)){?><li><a class="alnk" href="parametros_asistentes.php">Parámetros asistentes</a></li><?php }?>
								<?php if(PermitirFuncion(803)){?><li><a class="alnk" href="parametros_gestion_cartera.php">Parámetros de Gestión cartera</a></li><?php }?>
								<?php if(PermitirFuncion(216)){?><li><a class="alnk" href="parametros_frm_personalizados.php">Parámetros de formularios personalizados</a></li><?php }?>
								<?php if(PermitirFuncion(217)){?><li><a class="alnk" href="parametros_campos_adicionales.php">Campos adicionales en documentos</a></li><?php }?>
								<?php if(PermitirFuncion(218)){?><li><a class="alnk" href="parametros_dosificaciones.php">Parámetros dosificaciones</a></li><?php }?>
								<?php if(PermitirFuncion(219)){?><li><a class="alnk" href="parametros_formatos_impresion.php">Parámetros formatos de impresión</a></li><?php }?>
							</ul>
						</li>
						<?php }?>
						<?php if(PermitirFuncion(221)){?>
						<li>
                   			<a href="#">Logs del sistema<span class="fa arrow"></span></a>
							<ul class='nav nav-third-level'>
								<li><a class="alnk" href="log_sistema.php">Log del sistema</a></li>
								<li><a class="alnk" href="log_procesows.php">Log proceso WS</a></li>
								<li><a class="alnk" href="log_cola_integrador.php">Log cola integración</a></li>
							</ul>
						</li>
						<?php }?>
                  		<?php if(PermitirFuncion(206)){?><li><a class="alnk" href="gestionar_alertas.php">Gestionar alertas</a></li><?php }?>
                  		<?php if(PermitirFuncion(204)){?><li><a class="alnk" href="cargue_masivo_archivos.php">Cargar archivos masivos</a></li><?php }?>
						<?php if(PermitirFuncion(204)){?><li><a class="alnk" href="cargue_masivo_productos.php">Cargar productos masivos</a></li><?php }?>
                  		<li><a class="alnk" href="contrato_confidencialidad.php">Acuerdo de confidencialidad</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>