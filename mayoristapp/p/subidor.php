<?php
ini_set("display_errors",1);
ini_set("error_reporting",E_ALL);


if(!$_FILES){
	echo json_encode(array('msg' => 'No tengo archivo para subir'));
	exit;
	
}

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


echo $reply;
$reply = json_decode($reply);




