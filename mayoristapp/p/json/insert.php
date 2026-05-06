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
if(preg_match('/\d\/\d\/\d/',$_REQUEST['vigencia_premios']))
{
	
	
$f = explode('/',$_REQUEST['vigencia_premios']);
//$fecha = $f[1]."-".printf("%0.2d",$f[0]).'-'.$f[2];
$ano = $f[2];
$dia = intval( $f[0]);
$mes = intval($f[1]);


if($dia<10)$dia = "0".$dia;
if($mes<10)$mes = "0".$mes;

$fecha = $ano.'-'.$mes.'-'.$dia;

	
}else{
	$fecha = date('Y-m-d');
}

switch ($_REQUEST['CUAL']) {
    case "":
        $msg =  "No hay datos";
        break;
    case "sp_ab_premios":
	$saldopremios = 0;
	if(isset($_REQUEST['saldo_premios']))$saldopremios= $_REQUEST['saldo_premios'];
	

        $sql="INSERT INTO sp_abm_premios SET
                                nombre_premios='".$_REQUEST['nombre_premios']."',
				descripcion_premios='".$_REQUEST['descripcion_premios']."',
				puntos_premios='".$_REQUEST['puntos_premios']."',
				saldo_premios=ROUND(".$saldopremios.",0),
				vigencia_premios='".$fecha."',
				id_categoria_abm_premios='".$_REQUEST['id_categoria_abm_premios']."',
				anulado='No'
				";
				
        break;
	
	case "sp_categoria_abm_premios":
	$sql="INSERT INTO sp_categoria_abm_premios SET
			descripcion_categoria_premios = '".$_REQUEST['descripcion_categoria_premios']."',
			url_foto = '".$_REQUEST['url_foto']."',
			anulado='No'";

	break;
	case "sp_fotos_premios":
//            echo "<pre>";
//            print_r($_REQUEST);
            
	$sql="";
        $fotoPrincipal="No";
	if(isset($_REQUEST['foto_principal'])&&$_REQUEST['foto_principal']=="Si"){
            $sqlanterior= "UPDATE sp_fotos_premios SET foto_principal='No' where id_abm_premios='".$_REQUEST['id_abm_premios']."';";
            $fotoPrincipal="Si";
	}
	$descripcionFoto='';
	if(isset($_REQUEST['descripcion']))$descripcionFoto=$_REQUEST['descripcion'];
	$sql= "insert into sp_fotos_premios SET
			id_abm_premios='".$_REQUEST['id_abm_premios']."',
			url_foto='".$_REQUEST['url_foto']."',
			descripcion='".$descripcionFoto."',
			fecha_creacion = NOW(),
			foto_principal='".$fotoPrincipal."',
			anulado='No';";
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
	  $insersion=$mysqli->insert_id;
    $msg= "Insercion:. ". $insersion;
			if(isset($_FILES)){
			if($_REQUEST['CUAL']=='sp_ab_premios'){
				
define("cliente_id","7b166eac1783b6c");
define("secret",'3cc3289561bf034c13aa9f594ebe8ee7aa2ef102');

$archivo = $_FILES['archivo']['tmp_name'];
if(!is_file($archivo))die(json_encode( array('msg' =>   "No existe". $archivo)));


$image = file_get_contents($archivo);


$nombre = md5_file($archivo);
$ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);



$ch = curl_init();
$headers = array("Authorization: Client-ID ".cliente_id);
$poster = array( 'image' => base64_encode($image),
               'name' =>  basename($archivo),
			   'title' => md5_file($archivo)
			   );

curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $poster);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$reply = curl_exec($ch);

curl_close($ch);
$reply = json_decode($reply);
if($reply->success == "true"){

if(isset( $reply->data->link))
	if(preg_match('/^http/',$reply->data->link)){





	$sqlFOTO= "insert into sp_fotos_premios SET
			id_abm_premios='".$insersion."',
			url_foto='".$reply->data->link."',
			descripcion='',
			fecha_creacion = NOW(),
			foto_principal='Si',
			anulado='No';";
			
		$mysqli->query($sqlFOTO);	
			}
				
			}
			
		}
  }
 ///////////
  } 
  
}else
{
	$msg="No hay sql";
}
  if(isset($msg))
	  if(!empty($msg))
		  echo json_encode(array("resultado" =>  $msg));
	 

?>