<?php
if (isset($_SESSION)) {
    session_destroy();
}

$parametros_cookies = session_get_cookie_params();
setcookie(session_name(), 0, 1, $parametros_cookies["path"]);
setcookie("JWT", "", time() - 3600);
setcookie("banderaMenu", "", time() - 3600); // SMM, 18/11/2022

if (isset($_GET['data']) && $_GET['data'] != "") {?>

	<!DOCTYPE html>
	<html lang="es">
		<head></head>

		<body onload="Enviar();">
			<form name="form" id="form" method="post" action="login.php">
				<input type="hidden" name="data" value="OK">
			</form>

			<script language="javascript">
			function Enviar(){
				alert('Se ha cerrado la sesión, debe ingresar nuevamente.');
				document.getElementById('form').submit();
			}
			</script>
		</body>
	</html>

<?php } elseif (isset($_GET['msg']) && $_GET['msg'] != "") {?>
	<?php // SMM, 16/11/2022 ?>
	<?php echo base64_decode($_GET['msg']); ?>
	<?php isset($_GET['return_url']) ? header('Location:login.php?return_url=' . $_GET['return_url']) : header('Location:login.php');?>
<?php } else {?>
	<!DOCTYPE html>
	<html lang="es">
		<head>
			<?php include "includes/cabecera.php";?>

			<title>Logout</title>
		</head>

		<body>
			<script>
			$(document).ready(function() {
				// Mensaje de cierre. SMM, 12/11/2022

				Swal.fire({
					title: '¡Advertencia!',
					text: 'La sesión ha expirado y debe ingresar nuevamente.',
					icon: 'warning'
				}).then((result) => {
					console.log(result);
					return_url = '<?php echo $_GET['return_url'] ?? ""; ?>'

					if(return_url === "") {
						location.href = "login.php";
					} else {
						location.href = `login.php?return_url=${return_url}`;
					}
				});
			});
			</script>
		</body>
	</html>
<?php }?>
