<?php
require_once 'preinclude.php';
if ($mysqli->connect_errno) {

    echo "Error: Fallo al conectarse a MySQL debido a: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    die( "Error: " . $mysqli->connect_error . "\n");

}

// Realizar una consulta SQL
$sql = "select * from sp_abm_premios";
if (!$resultado = $mysqli->query($sql)) {

    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die( "Error: " . $mysqli->error . "\n");
    
}


$msg = "";

$id='XX';
if(isset($_REQUEST['id']))$id=$_REQUEST['id'];
switch ($_REQUEST['CUAL']) {
    case "":
        $msg =  "No hay datos";
        break;
    case "sp_ab_premios":
        $sql="DELETE * FROM  sp_abm_premios 
				WHERE id_abm_premios = '".$id."' limit 1;
				";
		$sql="UPDATE  sp_abm_premios SET
		anulado = 'Si' 
		WHERE id_abm_premios = '".$id."' limit 1;
		";
        break;

}


if(isset($sql)){


 if (!$mysqli->query($sql))
    $msg= "Error: ". $mysqli->error;
  else {
    $msg= "Registro Eliminado";
  }
  
  
}else
{
	$msg="No hay sql";
}
  if(isset($msg))
	  if(!empty($msg))
		  echo json_encode(array("resultado" =>  $msg));
	 

?>