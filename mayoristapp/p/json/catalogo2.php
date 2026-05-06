<?php
require_once 'preinclude.php';
define('PGN',1);
define('RES',10);
$pagina= intval(PGN);
$resultado= intval(RES);
if(isset($_REQUEST['page']))$pagina=$_REQUEST['page'];
if(isset($_REQUEST['rows']))$resultado=$_REQUEST['rows'];

$offset = ($pagina-1)*$resultado;
if ($mysqli->connect_errno) {

    echo "Error: Fallo al conectarse a MySQL debido a: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    die( "Error: " . $mysqli->connect_error . "\n");

}

if(!isset($_REQUEST['puntos'])){
	$where="AND p.puntos_premios<=10000";
}else
	$where="AND  p.puntos_premios<='".$_REQUEST['puntos']."'";

if(isset($_REQUEST['articulo']))
	if($_REQUEST['articulo']!="")
$where.=" AND p.id_abm_premios = '".$_REQUEST['articulo']."'";

if(isset($_REQUEST['categoria']))
if($_REQUEST['categoria']!="")
	$where.=" AND c.descripcion_categoria_premios like '%".$_REQUEST['categoria']."%'";

// Realizar una consulta SQL
$sql = "SELECT p.id_abm_premios,
	p.nombre_premios,
    p.descripcion_premios,
    ROUND(p.puntos_premios,0) AS puntos_premios,
    date_format(p.vigencia_premios,'%d/%m/%Y') as vigencia_premios,
    p.id_categoria_abm_premios,
    c.descripcion_categoria_premios,
    p.anulado,
	 date_format(p.vigencia_premios,'%d') as vpd,
	 date_format(p.vigencia_premios,'%m') as vpm,
	 date_format(p.vigencia_premios,'%Y') as vpy,
     f.url_foto,
     f.foto_principal,
     f.descripcion as fdesc
	
FROM sp_abm_premios p
LEFT OUter JOIN sp_categoria_abm_premios c ON p.id_categoria_abm_premios= c.id_categoria_abm_premios
LEFT OUTER JOIN sp_fotos_premios f  ON f.id_abm_premios= p.id_abm_premios
where
 p.anulado='No'
".$where." 
ORDER BY RAND()
limit $offset,$resultado
";
$csql = "SELECT count(*) as CONTADOR 
	
FROM sp_abm_premios p
LEFT OUter JOIN sp_categoria_abm_premios c ON p.id_categoria_abm_premios= c.id_categoria_abm_premios
LEFT OUTER JOIN sp_fotos_premios f  ON f.id_abm_premios= p.id_abm_premios
where
 p.anulado='No'
".$where."
";

$resultado = $mysqli->query($csql);
$Registro = mysqli_fetch_object($resultado);
$contador = $Registro->CONTADOR;
$paginas = round($Registro->CONTADOR/MAXIMO,0);

echo json_encode(array('total'=>$contador,'paginas'=>$paginas));
/*
if (!$resultado = $mysqli->query($sql)) {

    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die( "Error: " . $mysqli->error . "\n");
    
}



 ?><ul id="list" class="m-list"><?php
    while ($Registro = mysqli_fetch_object($resultado)) {
    //  echo htmlentities( " id_abm_premios ---> ".$Registro->descripcion_premios);
	echo "<li data-options=\"animation:'pop',direction:''\" onclick=\"javascript:location.href='mostrame.php?id_abm_premios=".$Registro->id_abm_premios."'\">\n";
	echo "<div class=\"list-header\"> ".$Registro->nombre_premios."</div>\n";
	//echo "<small>ID: ".$Registro->id_abm_premios."</small>\n";
	echo '<img class="list-image" src="'.$Registro->url_foto.'" alt="'.$Registro->fdesc.'"/>'.PHP_EOL;
	echo '<h3>'.$Registro->puntos_premios.' puntos</h3>'.PHP_EOL;
	echo '<div class="list-content">'.$Registro->descripcion_premios.'</div>'.PHP_EOL;
	//echo '<a href="mostrame.php?id_abm_premios='.$Registro->id_abm_premios.'" class="easyui-linkbutton" style="width:100%">Button1</a>'.PHP_EOL;
	
	echo "</li>\n";

			
    }
	
	?></ul><?php
	
	
*/	

?>