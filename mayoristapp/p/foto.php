<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);



/*
 * funcion para crear miniaturas
 * =============================================================================
 * @return: texto
 */

function creaMiniatura($imagen, $mini,$nombreFoto=null,$rutaMini=null) {
    //echo "en la miniatura con mini::{".$mini."} y idart::{".$idArt."} y ruta::{".$rutaMini."}";
    $calidad = 0;
    if ($mini == 0) {
        // tamaño grande
        $tam = null;
        $tamh = null;
        //$nombreFoto = "imagen-grande";
        $calidad = 100;
    }
    if ($mini == 1) {
        //tamaño medio
        $tam = 257;
        $tamh= 350;
       // $nombreFoto = "imagen-media";
        $calidad = 85;
    }
    if ($mini == 2) {
        // tamaño chico.
        $tam = 50;
        //$nombreFoto = "imagen-chica";
        $calidad = 75;
    }

    if ($mini == 3) {
        // tamaño chico fotos del slider. 
    }
    //CREAMOS UNA IMAGEN CON LA FUNCION DE GD imagecreatefromstring
    //YA QUE ESTA PUEDE LEER EL CAMPO BLOB QUE ESTAMOS OBTENIENDO
    $im = imagecreatefromstring($imagen);

    //OBTENEMOS EL TIPO MIME DEL ARCHIVO ASI EL NAVEGADOR SABRA DE QUE SE TRATA
//          header("Content-type:".$fila["Tipo"]);
    //OBTENEMOS LAS MEDIDAS ACTUALES DE LA IMAGEN
    $width = imagesx($im);
    $height = imagesy($im);
    
    // imagen al natural
    if ($mini == 0) {
        // ESTABLECEMOS EL TAMA�O DEL THUMBNAIL
        $imgw = $width;

        //CALCULAMOS EL ALTO DE LA IMAGEN PARA MANTER EL ASPECTO
        $imgh = $height;
        // CREAMOS UNA NUEVA IMAGEN UTILIZANDO LAS NUEVAS MEDIDAS
        $thumb = imagecreatetruecolor($imgw, $imgh);

      
        header('Content-type: image/pjpeg');
        //header('Content-Disposition: Attachment;filename='.$nombreFoto.'.png');
        header("Pragma: no-cache");
        header("Expires: 0");
        // COPIAMOS LA IMAGEN ORIGINA AL THUMBNAIL
        imagecopyresized($thumb, $im, 0, 0, 0, 0, $imgw, $imgh, imagesx($im), imagesy($im));
        $back = imagecolorallocate($thumb, 255, 255, 255);
        imagefill($thumb, 0, 0, $back);
        if($idArt!="0"&&$idArt!=NULL){
            $out = imagejpeg($thumb, "images/premios/".$rutaMini.$nombreFoto, 100);
            readfile("images/premios/".$rutaMini.$nombreFoto);
        }else{
            $out = imagejpeg($thumb, NULL, $calidad);
        }
       
    }
    
    // imagen miniatura de catalogo.
    //echo var_dump($mini==1);
    if($mini==1){
        //echo "dentro del mini=1<br>";
        $imgw = $width / $tam;
        $imgh = $height / $tamh;

        if ($imgw > $imgh) {
            $new_width = $tam;
            $new_height = ($tam / $width) * $height;
        } else {
            $new_height = $tamh;
            $new_width = ($tamh / $height) * $width;
        }


        $x_mid = $new_width / 2;
        $y_mid = $new_height / 2;

        $x = ($x_mid - ($tam / 2));
        $y = ($y_mid - ($tamh / 2));
        
        $newImage = imagecreatetruecolor($new_width, $new_height);
       
        imagecopyresampled($newImage, $im, 0, 0, 0, 0, $new_width, $new_height, $width, $height);


        $final = imagecreatetruecolor($tam, $tamh);
        imagecopyresampled($final, $newImage, 0, 0, $x, $y, $tam, $tamh, $tam, $tamh);
        $bg_color = imagecolorallocate($final, 255, 255, 255);
        imagefill($final, 0, 0, $bg_color);
        header('Content-type: image/pjpeg');
        //header('Content-Disposition: Attachment;filename='.$nombreFoto.'.png');
        header("Pragma: no-cache");
        header("Expires: 0");
        //echo "imprimo foto::";
        //$out = imagejpeg($final, NULL, $calidad);
        if($idArt!="0"&&$idArt!=NULL){
            //echo "creo foto en disco <br>";
            $out = imagejpeg($final, "images/premios/".$rutaMini.$nombreFoto, 100);
            //echo "echo obtengo la foto de la ruta{images/productos/".$rutaMini.$idArt.".jpg}";
            readfile("images/premios/".$rutaMini.$nombreFoto);
        }else{
            $out = imagejpeg($final, NULL, $calidad);
        }
    }
    
    
    // imagen xs para resumen de catalogo.
    if($mini==2){
        $imgw = $tam;
        //CALCULAMOS EL ALTO DE LA IMAGEN PARA MANTER EL ASPECTO
        $imgh = $height / $width * $imgw;
        // CREAMOS UNA NUEVA IMAGEN UTILIZANDO LAS NUEVAS MEDIDAS
        $thumb = imagecreatetruecolor($imgw, $imgh);

                // RELLENAMOS EL LA IMAGEN CON EL COLOR QUE CREAMOS EN EL PASO ANTERIOR
        //imagefill($thumb, 0, 0, $back);
        header('Content-type: image/pjpeg');
        //header('Content-Disposition: Attachment;filename='.$nombreFoto.'.png');
        header("Pragma: no-cache");
        header("Expires: 0");
        // COPIAMOS LA IMAGEN ORIGINA AL THUMBNAIL
        imagecopyresized($thumb,$im,0,0,0,0,$imgw,$imgh,imagesx($im),imagesy($im));
        $back = imagecolorallocate($thumb, 255, 255, 255);
        imagefill($thumb, 0, 0, $back);
        if($idArt!="0"&&$idArt!=NULL){
            $out = imagejpeg($thumb, "images/premios/".$rutaMini.$nombreFoto, 100);
            readfile("images/premios/".$rutaMini.$nombreFoto);
        }else{
            $out = imagejpeg($thumb, NULL, $calidad);
        }
        //$out = imagejpeg($thumb, NULL, $calidad);
    }
    
    // LIMPIAMOS LA MEMORIA
    imagedestroy($im);
    imagedestroy($thumb);

   
}

function traer_foto_externo($arrUrl, $idArt=null, $rutaMini, $mini) {
//traigo la fogo que me piden y cuando la guardo la recupero fisicamente.
    
//echo '<img src="'.$url.'">';
	switch($mini){
		case 0:
		// foto grande
		$url=$arrUrl['urlGrande'];
		break;
		case 1:
		// foto mediana
		$url=$arrUrl['urlMediana'];
		break;
		case 2:
		// foto chica
		$url=$arrUrl['urlChica'];
		break;
	}
	$nombreFoto=$arrUrl['nombreFoto'];
	
    $ch = curl_init($url);
    
    $my_save_dir = 'images/premios/'.$rutaMini;
    
//$filename = basename($url);
    $filename =$nombreFoto;
    $complete_save_loc = $my_save_dir . $filename;
    //echo $complete_save_loc;
    $fp = fopen($complete_save_loc, 'wb');

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_exec($ch);
    if ($errno = curl_errno($ch)) {
        $error_message = curl_strerror($errno);
        echo "cURL error ({$errno}):\n {$error_message}";
    }
    curl_close($ch);
    fclose($fp);
    
    
        //$imagen = file_get_contents('images/productos/' . $idArt . '.jpg');
		//$imagen = file_get_contents('images/productos/' . $rutaMini .$nombreFoto);
        //creaMiniatura($imagen, $mini, $nombreFoto, $rutaMini);
		trae_foto_disco($complete_save_loc);
    
}

// traer la foto del disco directamente.
function trae_foto_disco($rutaFoto){
    //echo $rutaFoto;
    $filename = $rutaFoto;
    $file_extension = strtolower(substr(strrchr($filename,"."),1));
    //echo $file_extension;
    switch( $file_extension ) {
    case "gif": $ctype="image/gif"; break;
    case "png": $ctype="image/png"; break;
    case "jpeg":
    case "jpg": $ctype="image/pjpeg"; break;
    default:
}

header('Content-type: ' . $ctype);
        
    header("Pragma: no-cache");
    header("Expires: 0"); 
	
    readfile($rutaFoto);
	// file_get_contents($fn);
	
	/*
	$fn=rutaFoto;
	 $headers = apache_request_headers(); 

    // Checking if the client is validating his cache and if it is current.
    if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($fn))) {
        // Client's cache IS current, so we just respond '304 Not Modified'.
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($fn)).' GMT', true, 304);
    } else {
        // Image not cached or cache outdated, we respond '200 OK' and output the image.
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($fn)).' GMT', true, 200);
        header('Content-Length: '.filesize($fn));
        header("Content-type:image/pjpeg");
        readfile($rutaFoto);
    }
	*/
}

// traer la foto de la tabla articulo.
function trae_foto_base_articulo($imagen,$idArt,$rutaMini,$mini){
    if($rutaMini!=""){
        // tamaño normal
        creaMiniatura($imagen, $mini, $idArt, "");
    }
    // miniatura
    creaMiniatura($imagen, $mini, $idArt, $rutaMini);
}

// trae la imagen sin foto.
function trae_sin_foto(){
    header("Content-type:image/pjpeg");
    header("Pragma: no-cache");
    header("Expires: 0");
	readfile('images/sinfoto.jpg');
	// $fn='images/sinfoto.jpg');
     // cabeceras nuevas
	 /*
	 $fn='images/sinfoto.jpg';
	 $headers = apache_request_headers(); 

    // Checking if the client is validating his cache and if it is current.
    if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($fn))) {
        // Client's cache IS current, so we just respond '304 Not Modified'.
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($fn)).' GMT', true, 304);
    } else {
        // Image not cached or cache outdated, we respond '200 OK' and output the image.
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($fn)).' GMT', true, 200);
        header('Content-Length: '.filesize($fn));
        header('Content-Type: image/png');
        print file_get_contents($fn);
    }
	*/ 
    
}    

// function para controlar si hay que pisar la miniatura
function buscar_foto_externa($idArt) {
require_once '../../conexion-general.inc.php';
	//echo "buscando la foto<br>";
    $query = "SELECT url_externo AS url,nombre_archivo AS extension "
            . "FROM articulo_foto "
            . "WHERE idArt=" . $idArt . " "
            . "ORDER BY id_articulo_foto ASC "
            . "LIMIT 1";
    $sal = mysqli_query($conexionT, $query)or die("no anduvo externo" . mysqli_error($conexionT));
    $resul = mysqli_num_rows($sal);
	//echo var_dump($resul);
    if ($resul == 0) {

        return false;
    } else {
        $fila = mysqli_fetch_assoc($sal);
        $a = explode(".", $fila["url"]);
        //$url = $fila["url"];
        //https://i.imgur.com/fHtzRLg.jpg
        /*
         * Thumbnail Suffix	Thumbnail Name	Thumbnail Size	Keeps Image Proportions
          s	Small Square	90x90	No
          b	Big Square	160x160	No
          t	Small Thumbnail	160x160	Yes
          m	Medium Thumbnail	320x320	Yes
          l	Large Thumbnail	640x640	Yes
          h	Huge Thumbnail	1024x1024	Yes
         */
	$arrUrl = explode("/",$fila["url"]);
        $urlGrande = $a[0] . "." . $a[1] . "." . $a[2] . "h" . "." . $a[3];
	$urlMediana = $a[0] . "." . $a[1] . "." . $a[2] . "m" . "." . $a[3];
	$urlChica = $a[0] . "." . $a[1] . "." . $a[2] . "s" . "." . $a[3];
        $arrExterno=array('nombreFoto'=>$arrUrl[3],'urlGrande'=>$urlGrande,'urlMediana'=>$urlMediana,'urlChica'=>$urlChica);
		
	return $arrExterno;
    }
}


function buscar_foto_externa_sin_db($urlFoto){
    $a = explode(".", $urlFoto);
        //$url = $fila["url"];
        //https://i.imgur.com/fHtzRLg.jpg
        /*
         * Thumbnail Suffix	Thumbnail Name	Thumbnail Size	Keeps Image Proportions
          s	Small Square        90x90	No
          b	Big Square          160x160	No
          t	Small Thumbnail     160x160	Yes
          m	Medium Thumbnail    320x320	Yes
          l	Large Thumbnail     640x640	Yes
          h	Huge Thumbnail      1024x1024	Yes
         */
        $arrUrl = explode("/",$urlFoto);
        $urlGrande = $a[0] . "." . $a[1] . "." . $a[2] . "h" . "." . $a[3];
	$urlMediana = $a[0] . "." . $a[1] . "." . $a[2] . "m" . "." . $a[3];
	$urlChica = $a[0] . "." . $a[1] . "." . $a[2] . "s" . "." . $a[3];
        $arrExterno=array('nombreFoto'=>$arrUrl[3],'urlGrande'=>$urlGrande,'urlMediana'=>$urlMediana,'urlChica'=>$urlChica);
		
	return $arrExterno;
}




/*
 * PREMIOS
 * =============================================================================
 */

// soy premio y Categoria
if(isset($_GET["catp"])){
    // tratamiento fotos de premios.
    
    $urlImagen=$_GET["url"];
    if(isset($_GET["mini"])){
        $mini=$_GET["mini"];
    }else{
        $mini=1;
    }
    
    if ($mini == 0) {
        // tamaño grande

        $rutaMini = "";
    }
    if ($mini == 1) {
        //tamaño medio

        $rutaMini = "miniatura/";
    }
    if ($mini == 2) {
        // tamaño chico.

        $rutaMini = "miniatura/xs/";
    }

    $hayFoto=false;
    
    $arrUrl = explode("/",$urlImagen);
    if($urlImagen!=='sinfoto.jpg'){
        $nombreImagen=$arrUrl[3];
        
    }else{
        trae_sin_foto();
        exit();
    }
        
    
    $urlFotoFisica="images/premios/".$rutaMini.$nombreImagen;
    // me fijo si la foto existe y si existe la traigo y salgo 
    //echo "<pre>";
    //echo "<pre>".print_r($urlFotoFisica)."</pre>";
    if(file_exists($urlFotoFisica)){
        
            //echo "el archivo existe";
        trae_foto_disco($urlFotoFisica);
    }else{
            // la foto no existe la tengo que generar y traer.
        //    echo "la foto no existe";
//        require_once 'includes/conex.inc.php';
        $arrUrl=buscar_foto_externa_sin_db($urlImagen,null, $rutaMini, $mini);
        traer_foto_externo($arrUrl,null,$rutaMini,$mini);
    }
    exit();
}



if(!isset($_GET["catp"])){
/*
 *  PRODUCTOS
 * =============================================================================
 */
/*
 * @Origen, es el tipo de imagen a procesar si es el logo o la foto del articulo
 * o la foto de la empresa.
 * @quien = el id del articulo necesario para localizar las fotos en 
 * administranet o en el disco.
 */


/*
 * PARAMETROS INICIO PROCESO
 * para hacer miniaturas.
 * mini = 0 => tamaño original Grande
 * mini = 1 => foto media
 * mini = 2 => foto chica
 */
$mini = 0;
$rutaMini ="";
if (isset($_GET["mini"])) {
    $mini = $_GET["mini"];
}

/*ruta de las imagenes*/
if ($mini == 0) {
    // tamaño grande

    $rutaMini = "";
}
if ($mini == 1) {
    //tamaño medio

    $rutaMini = "miniatura/";
}
if ($mini == 2) {
    // tamaño chico.

    $rutaMini = "miniatura/xs/";
}

// origen trae el idart
$g = explode("|", $_GET["origen"]);
$origen = $g[0];
$quien = $g[1];



$fotoDos="";

$hayFoto=false;

// primero debo recuperar si o si la foto en articulo foto , debido a que ahora es la unica manera.
// segundo cuando recupero el nombre de imageurl, la busco en el disco si esta la muestro.
// tercero no esta la foto en disco , la descargo 
// cuarto no hay foto en arti foto muestro sin foto.


// quien ese el id art






    $idArtExt=$quien;
    
    $arrUrl= buscar_foto_externa($idArtExt);

    if($arrUrl!==false){
		// hay foto cargada primero la busco fisicamente 
		
        $hayFoto=true;
		$urlFotoFisica="images/premios/".$rutaMini.$arrUrl['nombreFoto'];
		// me fijo si la foto existe y si existe la traigo y salgo 
		//echo "<pre>";
		//echo print_r($arrUrl);
		if(file_exists($urlFotoFisica)){
			//echo "el archivo existe";
			 trae_foto_disco($urlFotoFisica);
		}else{
			// la foto no existe la tengo que generar y traer.
			//echo "la foto no existe";
				traer_foto_externo($arrUrl, $quien, $rutaMini, $mini);
		}
		
		
        
		
    }else{
		// no hay foto
        $hayFoto=false;
    }
	
	
	// no hay foto asi que traigo sin foto
    if($hayFoto==false){
    trae_sin_foto();
    }
    exit();
}


