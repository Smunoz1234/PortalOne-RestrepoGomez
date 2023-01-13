<?php
if ((isset($_GET['type']) && ($_GET['type'] != "")) || (isset($_POST['type']) && ($_POST['type'] != ""))) {
    require_once "includes/conexion.php";
    header('Content-Type: application/json');

    if (isset($_GET['type']) && ($_GET['type'] != "")) {
        $type = $_GET['type'];
    } else {
        $type = $_POST['type'];
    }

    if ($type == 1) { // Ejecutar consulta.
        $sql = $_GET['query'] ?? '';
        $stmt = sqlsrv_query($conexion, $sql);

        if ($stmt === false) {
            echo json_encode(sqlsrv_errors(), JSON_PRETTY_PRINT);
        } else {
            $records = array();

            // Devolver cada fila como un objeto.
            while ($obj = sqlsrv_fetch_object($stmt)) {
                array_push($records, $obj);
            }

            echo json_encode($records, JSON_PRETTY_PRINT);
        }
    }

    sqlsrv_close($conexion);
}
