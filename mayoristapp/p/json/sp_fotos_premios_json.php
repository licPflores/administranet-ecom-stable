<?php
ini_set("display_errors",1);
require_once 'preinclude.php';
$where = " f.anulado='No'  ";
if(isset($_REQUEST['id_abm_premios']))
$where .= "\n AND f.id_abm_premios='".$_REQUEST['id_abm_premios']."'";

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


if(isset($_REQUEST['categoria'])){
$categoria =	$_REQUEST['categoria'];
	if(is_numeric($categoria))
	if($categoria>0)
$where .= " AND p.id_categoria_abm_premios ='".$categoria."'";	
}

if ($mysqli->connect_errno) {

    echo "Error: Fallo al conectarse a MySQL debido a: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    die( "Error: " . $mysqli->connect_error . "\n");

}
$mysqli->query("SET lc_time_names = 'es_AR'");
// Realizar una consulta SQL
$sql = "SELECT f.id_fotos_premios,
                f.id_abm_premios, 
                p.nombre_premios as dpremio,
                c.descripcion_categoria_premios as nCategoria,
                f.url_foto, 
                f.url_foto AS url_foto_link, 
                f.foto_principal, 
                f.descripcion,
                date_format(f.fecha_creacion,' %a %d/%b/%y') as fecha_creacion,
                f.anulado
                
        FROM sp_fotos_premios AS f 
        LEFT JOIN  sp_abm_premios AS p ON p.id_abm_premios=f.id_abm_premios 
        LEFT JOIN  sp_categoria_abm_premios AS c ON p.id_categoria_abm_premios=c.id_categoria_abm_premios
        WHERE
                $where";
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
    $columna[] = $property->name;
    //echo "Nombre: $nombreCampo <br>";
}

$x = 0;
while ($Registro = mysqli_fetch_object($resultado)) {
    // echo htmlentities( " id_abm_premios ---> ".$Registro->descripcion_premios);
    for ($y = 0; $y < sizeof($columna); $y++) {
        $n = $columna[$y];
        if ($n == 'url_foto_link') {
            // formato de fotito
            $rows[$x][$n] = '<img src="foto.php?catp=1&mini=2&url='.$Registro->url_foto_link.'" alt="'.$Registro->dpremio.'">';
        } else {
            $rows[$x][$n] = $Registro->$n;
        }
    }

    $x = $x + 1;
}

//var_dump($rows);
echo json_encode(array("total" => sizeof($rows), "rows" => $rows));
?>