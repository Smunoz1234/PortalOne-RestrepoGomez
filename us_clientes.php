<?php
require_once( "includes/conexion.php" );
//require_once("includes/conexion_hn.php");
if ( isset( $_GET[ 'id' ] ) && $_GET[ 'id' ] != "" ) {
  $IdUsuario = base64_decode( $_GET[ 'id' ] );
} else {
  $IdUsuario = "";
}
//Clientes
$cons="Select * From uvw_Sap_tbl_Clientes order by NombreCliente";
$SQL_Cliente=sqlsrv_query($conexion,$cons);
//$SQL_Cliente=Seleccionar('uvw_Sap_tbl_Clientes','*','','NombreCliente');

$SQL_ClienteUsuario = Seleccionar( 'uvw_tbl_ClienteUsuario', 'CodigoCliente, NombreCliente', "ID_Usuario='" . $IdUsuario . "'", 'NombreCliente' );

$Cont = 1;
?>
<div class="ibox">
  <div class="ibox-title bg-success">
    <h5><i class="fa fa-plus-circle"></i> Agregar nuevas sucursales</h5>
    <a class="collapse-link pull-right"> <i class="fa fa-chevron-up"></i> </a> </div>
  <div class="ibox-content">
    <div id="divCliente_<?php echo $Cont;?>" class="form-group">
      <label class="col-lg-1 control-label">Cliente</label>
      <div class="col-lg-4">
        <select name="Cliente[]" class="form-control" id="Cliente<?php echo $Cont;?>" onChange="BuscarSucursal('<?php echo $Cont;?>');">
          <option value="">Seleccione...</option>
          <?php while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){?>
          <option value="<?php echo $row_Cliente['CodigoCliente'];?>"><?php echo $row_Cliente['NombreCliente'];?></option>
          <?php }?>
        </select>
      </div>
      <label class="col-lg-1 control-label">Sucursal</label>
      <div class="col-lg-3">
        <select name="Sucursal[]" class="form-control" id="Sucursal<?php echo $Cont;?>">
          <option value="">Seleccione...</option>
        </select>
      </div>
      <div class="col-lg-3">
        <button type="button" id="btnCliente<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addField(this);"><i class="fa fa-plus"></i> Añadir otro</button>
      </div>
    </div>
  </div>
</div>

<div class="ibox">
  <div class="ibox-title bg-success">
    <h5><i class="fa fa-group"></i> Clientes asociados</h5>
    <a class="collapse-link pull-right"> <i class="fa fa-chevron-up"></i> </a> </div>
  <div class="ibox-content">
    <?php
    while ($row_ClienteUsuario=sqlsrv_fetch_array($SQL_ClienteUsuario)){
      ?>
    <div class="form-group">
      <div class="col-xs-12 font-bold">
        <div class="p-xs border-bottom bg-muted"><?php echo $row_ClienteUsuario['NombreCliente'];?></div>
      </div>
    </div>
    <div class="form-group">
      <div class="col-lg-3"> <span class="text-primary font-bold">Sucursal</span> </div>
      <div class="col-lg-3"> <span class="text-primary font-bold">Acción</span> </div>
      <div class="col-lg-3"> <span class="text-primary font-bold">Asignado por</span> </div>
      <div class="col-lg-3"> <span class="text-primary font-bold">Fecha asignación</span> </div>
    </div>
    <?php  
			  $SQL_SucursalCliente=Seleccionar('uvw_tbl_SucursalesClienteUsuario','NombreSucursal, NombreUsuarioAct, FechaAct',"ID_Usuario='".$IdUsuario."' and CodigoCliente='".$row_ClienteUsuario['CodigoCliente']."'",'NombreSucursal'); 
			  $row_SucursalCliente=sqlsrv_fetch_array($SQL_SucursalCliente);
		  do{
		 ?>
    <div id="div_<?php echo $Cont;?>" class="form-group">
      <input type="hidden" name="Cliente[]" id="Cliente<?php echo $Cont;?>" value="<?php echo $row_ClienteUsuario['CodigoCliente'];?>" />
      <input type="hidden" name="Sucursal[]" id="Sucursal<?php echo $Cont;?>" value="<?php echo $row_SucursalCliente['NombreSucursal'];?>" />
      <div class="col-lg-3"> <span class="text-primary"><?php echo $row_SucursalCliente['NombreSucursal'];?></span> </div>
      <div class="col-lg-3">
        <button type="button" id="<?php echo $Cont;?>" class="btn btn-warning btn-xs" onClick="delRow2(this);"><i class="fa fa-minus"></i> Remover</button>
      </div>
      <div class="col-lg-3"> <span class="text-primary"><?php echo $row_SucursalCliente['NombreUsuarioAct'];?></span> </div>
      <div class="col-lg-3"> <span class="text-primary">
        <?php if($row_SucursalCliente['FechaAct']!=""){echo $row_SucursalCliente['FechaAct']->format('Y-m-d H:i');}?>
        </span> </div>
    </div>
    <?php $Cont++;
			}while($row_SucursalCliente=sqlsrv_fetch_array($SQL_SucursalCliente));
		  }?>
  </div>
</div>
