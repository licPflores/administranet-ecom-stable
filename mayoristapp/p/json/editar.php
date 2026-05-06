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
if(isset($_REQUEST['vigencia_premios']))
{
$f = explode('/',$_REQUEST['vigencia_premios']);
//$fecha = $f[1]."-".printf("%0.2d",$f[0]).'-'.$f[2];
$ano = $f[2];
$dia = intval( $f[0]);
$mes = intval($f[1]);


if($dia<10)$dia = "0".$dia;
if($mes<10)$mes = "0".$mes;

$fecha = $ano.'-'.$mes.'-'.$dia;

	
}
$id='XX';
//echo "<pre>";
//print_r($_REQUEST);

if(isset($_REQUEST['id']))$id=$_REQUEST['id'];
switch ($_REQUEST['CUAL']) {
    case "":
        $msg =  "No hay datos";
        break;
    case "sp_ab_premios":
		$saldopremios = 0;
	if(isset($_REQUEST['saldo_premios']))$saldopremios= $_REQUEST['saldo_premios'];
        $sql="UPDATE  sp_abm_premios SET
                                nombre_premios='".$_REQUEST['nombre_premios']."',
				descripcion_premios='".$_REQUEST['descripcion_premios']."',
				puntos_premios='".$_REQUEST['puntos_premios']."',
				vigencia_premios='".$fecha."',
				saldo_premios=ROUND(".$saldopremios.",0),
				id_categoria_abm_premios='".$_REQUEST['id_categoria_abm_premios']."',
				anulado='".$_REQUEST['anulado']."'
				WHERE id_abm_premios = '".$id."' limit 1;
				";
        break;
		
	case "sp_categoria_abm_premios":
	$sql="UPDATE sp_categoria_abm_premios SET
			descripcion_categoria_premios = '".$_REQUEST['descripcion_categoria_premios']."',
			url_foto = '".$_REQUEST['url_foto']."',
			anulado = '".$_REQUEST['anulado']."'
			WHERE id_categoria_abm_premios = '".$id."' limit 1";

	break;
        case "sp_fotos_premios":
            
            if(isset($_REQUEST["foto_principal"])&&$_REQUEST["foto_principal"]=="Si"){
                $sqlanterior="UPDATE sp_fotos_premios SET foto_principal='No' WHERE id_abm_premios=".$_REQUEST['id_abm_premios'].";";
            }
            $sql= "UPDATE sp_fotos_premios SET
			id_abm_premios='".$_REQUEST['id_abm_premios']."',
			url_foto='".$_REQUEST['url_foto']."',
			descripcion='".$_REQUEST['descripcion']."',
			fecha_creacion = NOW(),
			foto_principal='".$_REQUEST['foto_principal']."',
			anulado='".$_REQUEST['anulado']."'
			WHERE
			id_fotos_premios = '".$id."'";
	break;

}

if(isset($sqlanterior)){


 if (!$mysqli->query($sqlanterior))
    $msg= "Error: ". $mysqli->error;
  else {
    $msg= "Actualizado";

  }
  
   if(isset($msg))
    if(!strstr($msg,'Actualizado')) {
		  echo json_encode(array("resultado" =>  $msg)); 
	  exit;
	  }
}


if(isset($sql)){


 if (!$mysqli->query($sql))
    $msg= "Error: ". $mysqli->error;
  else {
    $msg= "Edicion Completada";
  }
  
  
}else
{
	$msg="No hay sql";
}
  if(isset($msg))
	  if(!empty($msg))
		  echo json_encode(array("resultado" =>  $msg));
	 
/*CREATE TABLE sp_abm_premios (
  id_abm_premios bigint(20) DEFAULT NULL,
  descripcion_premios varchar(500) DEFAULT NULL,
  puntos_premios double(15,0) DEFAULT NULL,
  vigencia_premios date DEFAULT NULL,
  id_categoria_abm_premios bigint(20) DEFAULT NULL,
  anulado varchar(2) DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8*/

//echo json_encode(array("total" => sizeof($rows),"rows" => $rows));
?>