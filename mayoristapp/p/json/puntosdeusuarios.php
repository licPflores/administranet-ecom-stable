<?php

require_once 'preinclude.php';

if ($mysqli->connect_errno) {

    echo "Error: Fallo al conectarse a MySQL debido a: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    die("Error: " . $mysqli->connect_error . "\n");
}
$idCliente = 0;


if(isset($_SESSION['cliente'])) {
    if (is_object($_SESSION['cliente'])) {
        $clienteObj = $_SESSION['cliente'];
    } else {
        $clienteObj = $_SESSION['cliente'][0];
    }
    $idCliente = $clienteObj->Codigo;
}



$sql = "SELECT 
            id_cliente,
            ROUND(saldo_premios,0) AS saldo_premios,
            ROUND(saldo_premios,0) AS puntos_premios,
            date_format(vencimiento,'%d/%m/%Y') AS vencimiento
        FROM sp_saldo_cliente_premios 
        WHERE id_cliente = " . $idCliente . " LIMIT 1;";
//die($sql);
if (!$resultado = $mysqli->query($sql)) {

    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die("Error: " . $mysqli->error . "\n");
}



if ($resultado->num_rows < 1) {
    $sql = "SELECT '" . $idCliente . "' AS id_cliente,
            0 AS saldo_premios,
            date_format(date_add(curdate(), interval 1 year),'%d/%m/%Y') AS vencimiento";

    if (!$resultado = $mysqli->query($sql)) {

        echo "Error: La ejecución de la consulta falló debido a: \n";
        echo "Query: " . $sql . "\n";
        echo "Errno: " . $mysqli->errno . "\n";
        die("Error: " . $mysqli->error . "\n");
    }
}

$rows = Array();
$columna = Array();
while ($property = mysqli_fetch_field($resultado)) {
    $columna[] = $property->name;
    //echo "Nombre: $nombreCampo <br>";
}

$x = 0;
while ($Registro = mysqli_fetch_object($resultado)) {
    // echo htmlentities( " id_abm_premios ---> ".$Registro->descripcion_premios);
    for ($y = 0; $y < sizeof($columna); $y++) {
        $n = $columna[$y];
        $rows[$x][$n] = $Registro->$n;
    }

    $x = $x + 1;
}
// si tengo carrito traigo los puntos disponibles.
if(isset($_SESSION["carrito"])){
    $enCarrito=$_SESSION["carrito"];
    // recalcular los puntos de usuario 
    //print_r($rows);
    foreach($enCarrito as $premio){
    //  echo print_r($premio);
        $rows[0]["saldo_premios"]-=$premio;
    }
}
//var_dump($enCarrito);
//print_r($rows);
echo json_encode(array("total" => sizeof($rows), "rows" => $rows));
