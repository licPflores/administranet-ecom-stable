<?php
require_once 'preinclude.php';
define('PGN',1);
define('RES',5);

$pagina=intval(PGN);
$resultado=RES;
$primeraVez=0;
$cuantos=0;
/*	"pageNumber": pageNumber,
		'pageSize':pageSize*/

if(isset($_REQUEST['pageNumber'])&&isset($_REQUEST['pageNumber'])){    
    
    $primeraVez++;
    
}
// cargos los limites
if(isset($_REQUEST['pageNumber']))$pagina=$_REQUEST['pageNumber'];
if(isset($_REQUEST['pageSize']))$resultado=$_REQUEST['pageSize'];
    $offset = ($pagina-1)*$resultado;


if ($mysqli->connect_errno) {

    echo "Error: Fallo al conectarse a MySQL debido a: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    die( "Error: " . $mysqli->connect_error . "\n");

}

if(!isset($_REQUEST['puntos'])){
	$where="AND p.puntos_premios<=10000";
}else
	$where="AND  p.puntos_premios<='".$_REQUEST['puntos']."' ";

if(isset($_REQUEST['articulo'])){
    if($_REQUEST['articulo']!=""){
        $where.=" AND p.id_abm_premios = '".$_REQUEST['articulo']."'";
    }
}




if(isset($_REQUEST['categoria'])){
    if($_REQUEST['categoria']!=="0" && $_REQUEST['categoria']!==""){
        $where.=" AND p.id_categoria_abm_premios = '".$_REQUEST['categoria']."'";
    }
}

if($primeraVez==0){
    // recupero cuantos articulos hay por primera vez no al paginar.
    $sqlC="SELECT 
                COUNT(*) AS cuantos 
            FROM sp_abm_premios p
            LEFT OUter JOIN sp_categoria_abm_premios c ON p.id_categoria_abm_premios= c.id_categoria_abm_premios
            LEFT OUTER JOIN sp_fotos_premios f  ON f.id_abm_premios= p.id_abm_premios AND f.foto_principal='Si'
            WHERE
                p.anulado='No'
            ".$where."; ";
    $res = $mysqli->query($sqlC);
    $total= mysqli_fetch_object($res);
    $cuantos=$total->cuantos;
}


// RESULTADO PAGINADO
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
        LEFT OUTER JOIN sp_fotos_premios f  ON f.id_abm_premios= p.id_abm_premios AND f.foto_principal='Si'
        WHERE
         p.anulado='No'
        ".$where." 
        ORDER BY p.puntos_premios DESC,nombre_premios DESC
        LIMIT ".$offset.",".$resultado.";";


    $listaHtml='';
    if (!$resultado = $mysqli->query($sql)) {

        echo "Error: La ejecución de la consulta falló debido a: \n";
        echo "Query: " . $sql . "\n";
        echo "Errno: " . $mysqli->errno . "\n";
        die( "Error: " . $mysqli->error . "\n");

    }


 $listaHtml .='<ul id="list" class="m-list">';
    while ($Registro = mysqli_fetch_object($resultado)) {
       
        
    //  echo htmlentities( " id_abm_premios ---> ".$Registro->descripcion_premios);
	$listaHtml.="\n\t<li data-options=\"animation:'pop',direction:''\" onclick=\"javascript:location.href='mostrame.php?id_abm_premios=".$Registro->id_abm_premios."'\">\n";
	
	//echo "<small>ID: ".$Registro->id_abm_premios."</small>\n";
        if($Registro->url_foto!=""){
            $listaHtml.= '<div class="list-image">'.PHP_EOL;
            $descr='';
            if(!empty($Registro->fdesc))$descr= 'alt="'.$Registro->fdesc.'"';
//            $listaHtml.= '<img  src="'.$Registro->url_foto.'" '.$descr.' /></div>'.PHP_EOL;
            $listaHtml .='<img src="foto.php?catp=1&mini=1&url='.$Registro->url_foto.'" alt="'.$Registro->nombre_premios.'"></div>'.PHP_EOL;
        }
        $listaHtml.= '<div class="cuerpo-grilla">'.PHP_EOL;
        $listaHtml.= '<div class="list-header">' .htmlentities($Registro->nombre_premios).'</div>'.PHP_EOL;
	
	$listaHtml.= '<div class="list-content">'.htmlentities($Registro->descripcion_premios).'</div>'.PHP_EOL;
        $listaHtml.= '<div class="puntos">'.htmlentities(number_format($Registro->puntos_premios,0,',','.')).' <span>PUNTOS</span></div>'.PHP_EOL;
        
	//echo '<a href="mostrame.php?id_abm_premios='.$Registro->id_abm_premios.'" class="easyui-linkbutton" style="width:100%">Button1</a>'.PHP_EOL;
	$listaHtml.= '</div>'.PHP_EOL;
	$listaHtml.= '</li>'.PHP_EOL;

			
    }
	
    $listaHtml.='</ul>';

 print json_encode($vector=array('listaHtml'=>$listaHtml,'cuantos'=>$cuantos));	

?>