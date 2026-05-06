<?php

$puntos = 0;


if(isset($_REQUEST['id'])){
require_once 'preinclude.php';

if ($mysqli->connect_errno) {

    echo "Error: Fallo al conectarse a MySQL debido a: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    die( "Error: " . $mysqli->connect_error . "\n");

}


$sql = "select puntos_premios from sp_abm_premios where id_abm_premios=".$_REQUEST['id']. " limit 1";

if (!$resultado = $mysqli->query($sql)) {

    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die( "Error: " . $mysqli->error . "\n");
    
}else{
	if($mysqli->affected_rows<1){
		$sql="select 0 as puntos_premios";
		$resultado = $mysqli->query($sql);
	}
	
	
	
	

		$Registro = mysqli_fetch_object($resultado);

		$puntos = $Registro->puntos_premios;
}

}
echo $puntos;

