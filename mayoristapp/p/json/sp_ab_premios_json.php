<?php
require_once 'preinclude.php';

if ($mysqli->connect_errno) {

    echo "Error: Fallo al conectarse a MySQL debido a: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    die( "Error: " . $mysqli->connect_error . "\n");

}
$cantidad  = "";
$where = "";
if(isset($_REQUEST['cantidadpremio']))$cantidad = " AND p.saldo_premios<='".$_REQUEST['cantidadpremio']."'";
if(isset($_REQUEST['categoria'])){
	
	if($_REQUEST['categoria']!=="0")
$where .= " AND c.id_categoria_abm_premios='".$_REQUEST['categoria']."'";	
}

$ptos = 0;
if(isset($_REQUEST['puntos'])){
$ptos =	$_REQUEST['puntos'];
	if(is_numeric($ptos))
	if($ptos>0)
$where .= " AND p.puntos_premios <='".$ptos."'";	
}


$articulo = 0;
if(isset($_REQUEST['articulo'])){
$articulo =	$_REQUEST['articulo'];
	if(is_numeric($articulo))
	if($articulo>0)
$where .= " AND p.id_abm_premios ='".$articulo."'";	
}
	


// Realizar una consulta SQL
$sql = "SELECT p.id_abm_premios,
            p.nombre_premios,
            p.descripcion_premios,
            ROUND(p.puntos_premios,0) AS puntos_premios,
            ROUND(p.saldo_premios,0) AS saldo_premios,
            date_format(p.vigencia_premios,'%d/%m/%Y') as vigencia_premios,
            p.id_categoria_abm_premios,
            c.descripcion_categoria_premios,
            p.anulado,
             date_format(p.vigencia_premios,'%d') as vpd,
             date_format(p.vigencia_premios,'%m') as vpm,
             date_format(p.vigencia_premios,'%Y') as vpy

        FROM sp_abm_premios p, sp_categoria_abm_premios c
        WHERE
        p.id_categoria_abm_premios= c.id_categoria_abm_premios
       # AND p.anulado = 'No' 
         ".$where." 
        ".$cantidad."
		
        Order by p.nombre_premios ASC 
";


if (!$resultado = $mysqli->query($sql)) {

    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die( "Error: " . $mysqli->error . "\n");
    
}

$t = md5($sql);

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
	
	
	echo json_encode(array("total" => sizeof($rows),"rows" => $rows,'Guardado' => 'No'));



	
	//var_dump($rows);


//print_r($_SESSION['variables']);
?>