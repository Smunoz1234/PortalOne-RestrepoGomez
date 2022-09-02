<?php 
if(isset($_GET['id'])&&$_GET['id']!=""){
	require_once("includes/conexion.php");
	$dir=CrearObtenerDirTempFirma();
	$Campo=base64_decode($_GET['id']);
?>
<!doctype html>
<html>
<head>
<?php include_once("includes/cabecera.php"); ?>
<title>Firmar | <?php echo NOMBRE_PORTAL;?></title>
<style>
*,
*::before,
*::after {
  box-sizing: border-box;
}

body {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-pack: center;
      -ms-flex-pack: center;
          justify-content: center;
  -webkit-box-align: center;
      -ms-flex-align: center;
          align-items: center;
  height: 100vh;
  width: 100%;
  -webkit-user-select: none;
     -moz-user-select: none;
      -ms-user-select: none;
          user-select: none;
  margin: 0;
  padding: 32px 16px;
  font-family: Helvetica, Sans-Serif;
  background-color: #ffffff;
}

.signature-pad {
  position: relative;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
      -ms-flex-direction: column;
          flex-direction: column;
  font-size: 10px;
  width: 100%;
  height: 100%;
  max-width: 1200px;
  max-height: 500px;
  border: 1px solid #e8e8e8;
  background-color: #fff;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.27), 0 0 40px rgba(0, 0, 0, 0.08) inset;
  border-radius: 4px;
  padding: 16px;
}

.signature-pad::before,
.signature-pad::after {
  position: absolute;
  z-index: -1;
  content: "";
  width: 40%;
  height: 10px;
  bottom: 10px;
  background: transparent;
  box-shadow: 0 8px 12px rgba(0, 0, 0, 0.4);
}

.signature-pad::before {
  left: 20px;
  -webkit-transform: skew(-3deg) rotate(-3deg);
          transform: skew(-3deg) rotate(-3deg);
}

.signature-pad::after {
  right: 20px;
  -webkit-transform: skew(3deg) rotate(3deg);
          transform: skew(3deg) rotate(3deg);
}

.signature-pad--body {
  position: relative;
  -webkit-box-flex: 1;
      -ms-flex: 1;
          flex: 1;
  border: 1px solid #f4f4f4;
}

.signature-pad--body canvas {
  position: absolute;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  border-radius: 4px;
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.02) inset;
}

.signature-pad--footer {
  color: #C3C3C3;
  text-align: center;
  font-size: 1.2em;
  margin-top: 8px;
}

.signature-pad--actions {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-pack: justify;
      -ms-flex-pack: justify;
  justify-content: space-between;
  margin-top: 8px;
}

</style>
</head>

<body>

   <div id="signature-pad" class="signature-pad">
    <div class="signature-pad--body">
      <canvas></canvas>
    </div>
    <div class="signature-pad--footer">
      <div class="description">Realice su firma arriba
		 <div id="msgsig"></div>
		</div>		

      <div class="signature-pad--actions">
        <div>         
		<button class="btn btn-danger" type="button" id="clear"><i class="fa fa-eraser"></i> Limpiar</button> 
        </div>
        <div>
         <button class="btn btn-success" type="button" id="GuardarFirma"><i class="fa fa-pencil-square-o"></i> Guardar firma</button>
		 <button class="btn btn-warning" type="button" id="CerrarVentana"><i class="fa fa-window-close" aria-hidden="true"></i> Cerrar ventana</button>
        </div>
      </div>
    </div>
  </div>
<div class="form-group">
	<div class="col-lg-12">
		
	</div>	
</div>
<script>
var wrapper = document.getElementById("signature-pad");
var canvas = wrapper.querySelector("canvas");
var signaturePad = new SignaturePad(canvas, {
  backgroundColor: 'rgb(255, 255, 255)'
});
	
function resizeCanvas() {
  var ratio =  Math.max(window.devicePixelRatio || 1, 1);
  canvas.width = canvas.offsetWidth * ratio;
  canvas.height = canvas.offsetHeight * ratio;
  canvas.getContext("2d").scale(ratio, ratio);
  signaturePad.clear();
}
	
window.onresize = resizeCanvas;
resizeCanvas();

function download(dataURL, filename) {
  if (navigator.userAgent.indexOf("Safari") > -1 && navigator.userAgent.indexOf("Chrome") === -1) {
    window.open(dataURL);
  } else {
    var blob = dataURLToBlob(dataURL);
    var url = window.URL.createObjectURL(blob);

    var a = document.createElement("a");
    a.style = "display: none";
    a.href = url;
    a.download = filename;

    document.body.appendChild(a);
    a.click();

    window.URL.revokeObjectURL(url);
  }
}

function dataURLToBlob(dataURL) {
  // Code taken from https://github.com/ebidel/filer.js
  var parts = dataURL.split(';base64,');
  var contentType = parts[0].split(":")[1];
  var raw = window.atob(parts[1]);
  var rawLength = raw.length;
  var uInt8Array = new Uint8Array(rawLength);

  for (var i = 0; i < rawLength; ++i) {
    uInt8Array[i] = raw.charCodeAt(i);
  }

  return new Blob([uInt8Array], { type: contentType });
}

$("#CerrarVentana").click(function(){
	self.close();
});

$("#clear").click(function(){
	$("#msgsig").hide("fast");
	opener.document.getElementById("<?php echo $Campo;?>").value='';
  	signaturePad.clear();
});

$("#GuardarFirma").click(function(){
	$("#msgsig").hide("fast");
  if (signaturePad.isEmpty()) {
    alert("Debe realizar la firma antes de guardarla");
  } else {
    var dataURL = signaturePad.toDataURL("image/jpeg");
    $.ajax({
			url:"ajx_generar_firma.php",
			// Enviar un parámetro post con el nombre base64 y con la imagen en el
			data:{
				base64: dataURL
			},
			// Método POST
			type:"post",
			success: function(response){
				var signame=Base64.decode(response);
				opener.document.getElementById("<?php echo $Campo;?>").value=response;
				if(opener.document.getElementById("msgInfo<?php echo $Campo;?>")){
					opener.document.getElementById("msgInfo<?php echo $Campo;?>").style.display='block';
				}
				$("#msgsig").html('<div class="alert alert-success"><i class="fa fa-thumbs-up"></i> La firma ha sido cargada exitosamente.</div>').show('slow');
				if(opener.document.getElementById("Img<?php echo $Campo;?>")){
					opener.document.getElementById("Img<?php echo $Campo;?>").src="<?php echo $dir;?>" + signame + "#" + new Date();
					opener.document.getElementById("Img<?php echo $Campo;?>").style.display='block';
				}
				
			},
			error: function(jqXHR, textStatus, errorThrown){
				$("#msgsig").html('<div class="alert alert-danger"><i class="fa fa-times-circle-o"></i> Ocurrio un error al intentar guardar la firma.</div>').show('slow');
			}
		});
  }
});	
</script>
</body>
</html>
<?php sqlsrv_close( $conexion );}?>