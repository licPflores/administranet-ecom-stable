<?php
# conexion con la base de datos
include("conexion.inc.php");
# clave de popup para las fotos
$clave=$_GET["clave"];
// Array indexes are 0-based, jCarousel positions are 1-based.
$first = max(0, intval($_GET['first']) - 1);
$last  = max($first + 1, intval($_GET['last']) - 1);

$length = $last - $first + 1;

// ---

# consulto la base de datos para traer las fotos que hayan
$sql="SELECT id_adjunto as id,titulo_adjunto as titulo FROM adjuntos WHERE id_popup=$clave";
$hacer=mysql_query($sql) or die("No puedo recuperar las fotos.".mysql_error());
$adjuntos="";

while($aj=mysql_fetch_array($hacer))
{
	$images[]="sistema/foto.php?origen=fotoc|".$aj["id"]."||"."sistema/foto.php?origen=foto|".$aj["id"]."||".$aj["titulo"]."||";
}

/*$images = array(
    /*'http://static.flickr.com/66/199481236_dc98b5abb3_s.jpg',
    'http://static.flickr.com/75/199481072_b4a0d09597_s.jpg',
    'http://static.flickr.com/57/199481087_33ae73a8de_s.jpg',
    'http://static.flickr.com/77/199481108_4359e6b971_s.jpg',
    'http://static.flickr.com/58/199481143_3c148d9dd3_s.jpg',
    'http://static.flickr.com/72/199481203_ad4cdcf109_s.jpg',
    'http://static.flickr.com/58/199481218_264ce20da0_s.jpg',
    'http://static.flickr.com/69/199481255_fdfe885f87_s.jpg',
    'http://static.flickr.com/60/199480111_87d4cb3e38_s.jpg',
    'http://static.flickr.com/70/229228324_08223b70fa_s.jpg',
	'sistema/foto.php?origen=fotoc|19',
	'sistema/foto.php?origen=fotoc|20',
	'sistema/foto.php?origen=fotoc|21',
	'sistema/foto.php?origen=fotoc|22',
);*/


//$images=array($adjuntos);

$total = count($images);
$selected = array_slice($images, $first, $length);

// ---

header('Content-Type: text/xml');

echo '<data>';

// Return total number of images so the callback
// can set the size of the carousel.
echo '  <total>' . $total . '</total>';
# fotos
	foreach ($selected as $img) {
		echo '  <image>' . $img . '</image>';
	}

echo '</data>';

?>