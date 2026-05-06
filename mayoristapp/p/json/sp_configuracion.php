<?php

require_once 'preinclude.php';
$msg = "sin novedad";
if ($mysqli->connect_errno) {

    echo "Error: Fallo al conectarse a MySQL debido a: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    die("Error: " . $mysqli->connect_error . "\n");
}

$sql = "select * from sp_configuracion";
$resultado = $mysqli->query($sql);
if ($mysqli->affected_rows < 1) {
    $sql = "INSERT INTO sp_configuracion SET valor_calculo_puntaje=0, valor_cada_puntaje=0, vencimiento_puntaje=0 ";
    $resultado = $mysqli->query($sql);
}



if (isset($_REQUEST['valor_calculo_puntaje']))
    if (is_numeric($_REQUEST['valor_calculo_puntaje'])) {

        $sql = "UPDATE  sp_configuracion "
                . "SET "
                . " valor_calculo_puntaje='" . $_REQUEST['valor_calculo_puntaje'] . "',"
                . " valor_cada_puntaje='".$_REQUEST['monto_puntaje']."'";
        if (!$resultado = $mysqli->query($sql)) {

            echo "Error: La ejecución de la consulta falló debido a: \n";
            echo "Query: " . $sql . "\n";
            echo "Errno: " . $mysqli->errno . "\n";
            die("Error: " . $mysqli->error . "\n");
        } else {
            $msg = "Actualizacion Correcta, Monto: $". $_REQUEST['monto_puntaje']." Puntaje: " . $_REQUEST['valor_calculo_puntaje'];
        }
    }




/// Vencimiento

if (isset($_REQUEST['vencimiento_puntaje']))
    if (is_numeric($_REQUEST['vencimiento_puntaje'])) {

        $fecha = $_REQUEST['vencimiento_puntaje'];


        $sql = "UPDATE  sp_configuracion SET vencimiento_puntaje='" . $fecha . "'";
        if (!$resultado = $mysqli->query($sql)) {

            echo "Error: La ejecución de la consulta falló debido a: \n";
            echo "Query: " . $sql . "\n";
            echo "Errno: " . $mysqli->errno . "\n";
            die("Error: " . $mysqli->error . "\n");
        } else {
            $msg = "Actualizacion Correcta, Vencimiento: " . $fecha;
        }
    }









$sql = "select valor_calculo_puntaje,valor_cada_puntaje,vencimiento_puntaje from sp_configuracion limit 1";
if (!$resultado = $mysqli->query($sql)) {

    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die("Error: " . $mysqli->error . "\n");
}

if ($mysqli->affected_rows < 1) {
    $sql = "select 0 as valor_calculo_puntaje, 12 as vencimiento_puntaje;";
    $resultado = $mysqli->query($sql);
}


$rows = Array();
$columna = Array();
$columnaCompleta = Array();
while ($property = mysqli_fetch_field($resultado))
    $columna[] = $property->name;
//$columnaCompleta = mysqli_fetch_field($resultado);

while ($Registro = mysqli_fetch_object($resultado))
    $rows[] = $Registro;

if (!isset($msg)) {
    echo json_encode(
            array('columnas' => $columna, 'datos' => $rows)
    );
} else {
    echo json_encode(
            array('columnas' => $columna, 'datos' => $rows, 'Mensaje' => $msg)
    );
}


