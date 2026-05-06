<?php
require_once 'preinclude.php';

if ($mysqli->connect_errno) {

    echo "Error: Fallo al conectarse a MySQL debido a: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    die( "Error: " . $mysqli->connect_error . "\n");

}

// Realizar una consulta SQL
$sql = "select 'Todas' as descripcion_categoria_premios, '' as url_foto, 'No' as anulado, 0 as 'id_categoria_abm_premios'
UNION
(select descripcion_categoria_premios, url_foto,anulado, id_categoria_abm_premios from sp_categoria_abm_premios
where anulado = 'No'
order by descripcion_categoria_premios DESC)";

if (!$resultado = $mysqli->query($sql)) {

    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die( "Error: " . $mysqli->error . "\n");
    
}

/*
*/
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