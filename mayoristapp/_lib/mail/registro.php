<?php

if(isset($_REQUEST))
{

$ip_origen = getenv("REMOTE_ADDR");
//nombre=leopoldo&telefono=falcon&correo=leo@gmail.com
if(isset($_REQUEST['nombre']) && isset($_REQUEST['telefono']) && isset($_REQUEST['correo'])   )

$nombre = $_REQUEST['nombre'];
$nombre =  ucwords(strtolower($nombre));
$telefono = $_REQUEST['telefono'];
$correo = $_REQUEST['correo'];
$mysqli = new mysqli("127.0.0.1", "admn_free", "NahuelHuapi1", "administranet_free");
if ($mysqli->connect_errno) {
    echo "Falló la conexión con MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$sql="INSERT INTO instalacion_cliente (id_instalacion_cliente, nombre_cliente, telefono_cliente, email_cliente, fecha_hora_instalacion, ip_instalacion) VALUES (NULL, '".$nombre."', '".$telefono."', '".$correo."', CURRENT_TIMESTAMP, '".$ip_origen."');";
if (!$mysqli->query($sql) ) {
 //   echo "Falló la creación de la tabla: (" . $mysqli->errno . ") " . $mysqli->error;
print 0;

}else{


print $mysqli->insert_id;
}


}else
{
print 0;
}
