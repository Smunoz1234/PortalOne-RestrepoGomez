<?php
  include 'config.php';
    if(!empty($_POST["id_depto"])){
      $sql ="SELECT id, descripcion FROM tb_muni WHERE id_depto = '" . $_POST["id_depto"] . "'";
      $consulta_muni = $link->query($sql);?>
      <option value="">Seleccionar Municipio</option>
      <?php
      while($muni= $consulta_muni->fetch_object()){?>
        <option value="<?php echo $muni->id; ?>"><?php echo $muni->descripcion; ?></option>
        <?php
      }
    }
?>