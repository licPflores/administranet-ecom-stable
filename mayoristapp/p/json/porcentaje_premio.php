<?php
require_once 'preinclude.php';
$sql="";
$mensaje="Sin Novedad";
if(!isset($_REQUEST['Porcentaje']))$mensaje = "No tengo Porcentaje";

if ($mysqli->connect_errno) {

    echo "Error: Fallo al conectarse a MySQL debido a: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    die( "Error: " . $mysqli->connect_error . "\n");

}


if(isset($_REQUEST['Categorias'])){
	
$cadena = $_REQUEST['Categorias'];
$porcentaje = $_REQUEST['Porcentaje'];
$cadena = trim($cadena);

$cadena = str_replace(' ', "','", $cadena);
$cadena = "('".$cadena."')";


// Realizar una consulta SQL
$sql = "update sp_abm_premios SET 
puntos_premios = puntos_premios+(puntos_premios*(".$porcentaje.")/100)
WHERE id_categoria_abm_premios IN ".$cadena.";
 ";	
	

}
	
	if(isset($_REQUEST['Productos'])){
	
$cadena = $_REQUEST['Productos'];
$porcentaje = $_REQUEST['Porcentaje']/100;
$cadena = trim($cadena);

$cadena = str_replace(' ', "','", $cadena);
$cadena = "('".$cadena."')";


// Realizar una consulta SQL
$sql = "update sp_abm_premios SET 
puntos_premios = puntos_premios+(puntos_premios*(".$porcentaje."))
WHERE id_abm_premios IN ".$cadena.";
 ";	
//$mensaje .= PHP_EOL.$sql;	

}
	
	
	
	
	



if($sql!="")
if (!$resultado = $mysqli->query($sql)) {

    $mensaje =  "Error: La ejecución de la consulta falló debido a: \n".
     "Query: " . $sql . "\n".
     "Errno: " . $mysqli->errno . "\n".
     "Error: " . $mysqli->error . "\n";
    
}else{

/*
*/
$mensaje =   mysqli_affected_rows($mysqli)." Registros Afectados";
	}
	//var_dump($rows);
//echo json_encode(array("total" => sizeof($rows),"rows" => $rows));
echo json_encode(array("mensaje" => $mensaje, "sql" => $sql));


?>