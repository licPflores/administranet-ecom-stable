<?php
require_once 'preinclude.php';

if ($mysqli->connect_errno) {

    echo "Error: Fallo al conectarse a MySQL debido a: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    die( "Error: " . $mysqli->connect_error . "\n");

}

$idCliente= $_SESSION['cliente'][0]->Codigo;
$id_abm_premios = 2;
if(isset($_REQUEST['id']))$id_abm_premios=$_REQUEST['id'];
$sql = " INSERT INTO sp_comprobante_canje SET
fecha = CURDATE(),
id_cliente = ".$idCliente.",
puntaje_consumido_total=(select puntos_premios from sp_abm_premios where id_abm_premios=".$id_abm_premios."),
estado='solicitado',
fecha_entrega = CURDATE(),
anulado='No'";
if (!$resultado = $mysqli->query($sql)) {

    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die( "Error: " . $mysqli->error . "\n");
    
}

/*CREATE TABLE sp_abm_premios (
  id_abm_premios bigint(20) DEFAULT NULL,
  descripcion_premios varchar(500) DEFAULT NULL,
  puntos_premios double(15,0) DEFAULT NULL,
  vigencia_premios date DEFAULT NULL,
  id_categoria_abm_premios bigint(20) DEFAULT NULL,
  anulado varchar(2) DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8*/
$rows = Array();
$columna = Array();	
 while ($property = mysqli_fetch_field($resultado)) {
			$columna[]=$property->name;
			//echo "Nombre: $nombreCampo <br>";
			
			}

$x=0;
    while ($Registro = mysqli_fetch_object($resultado)) {
     // echo htmlentities( " id_abm_premios ---> ".$Registro->descripcion_premios);
	 for($y=0;$y<sizeof($columna);$y++){
		 $n=$columna[$y];
		$rows[$x][$n]=$Registro->$n; 
	 }

			$x=$x+1;
			
    }
	
	//var_dump($rows);
echo json_encode(array("total" => sizeof($rows),"rows" => $rows));
?>