<?php



require_once 'preinclude.php';

$msg='No tengo ID';
if (is_object($_SESSION['cliente'])) {
    $clienteObj = $_SESSION['cliente'];
} else {
    $clienteObj = $_SESSION['cliente'][0];
}


if(!isset($clienteObj)){
    $msg="No tengo cliente..";
    echo json_encode(array('msg' => $msg));
    exit;
    }

if(isset($_REQUEST['ide'])){
define('ID',$_REQUEST['ide']) ;

}else{
echo json_encode(array('msg' => $msg));
exit;
}

$sql="select * from sp_comprobante_canje 
WHERE id_sp_comprobante_canje=".ID." limit 1";

if (!$resultado = $mysqli->query($sql)) {

    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die("Error: " . $mysqli->error . "\n");
}

$Registro = mysqli_fetch_object($resultado);
$idcliente=$Registro->id_cliente;
$puntaje=$Registro->puntaje_consumido_total;
$puntaje=round($puntaje,0);
$estado=$Registro->estado;
$anulado = $Registro->anulado;
$idusuario=$Registro->id_usuario;
$tipocanje=$Registro->tipo_canje;
$idcanje=$Registro->id_sp_comprobante_canje;

if($anulado==='Si'){
    echo json_encode(array('msg' => 'Ya estaba anulado'));
exit;
}

//devolverpuntos($idusuario,$puntaje);
//DEVUELVO PUNTos

$sql = "UPDATE sp_saldo_cliente_premios SET saldo_premios=(saldo_premios+".$puntaje.
") WHERE id_cliente=".$idcliente." limit 1";



if (!$resultado = $mysqli->query($sql)) {

    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die("Error: " . $mysqli->error . "\n");
}
$msg = "Se agregaron ".$puntaje." puntos al cliente ".utf8_encode($clienteObj->cliente).PHP_EOL; 


//Modifico stock

$sql = "select * from sp_premios_canje WHERE id_sp_comprobante_canje =".$idcanje;
if (!$resultado = $mysqli->query($sql)) {

    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die("Error: " . $mysqli->error . "\n");
}


while ($Registro = mysqli_fetch_object($resultado)) {

    $vector[$Registro->id_abm_premios]=$Registro->cantidad;

}


foreach($vector as $clave => $cantidad){

//    echo "vector $clave cdad ".$cantidad.PHP_EOL;
$cantidad = round($cantidad,0);
$sql = "update sp_abm_premios SET saldo_premios=(saldo_premios+".$cantidad.") WHERE id_abm_premios=".$clave." limit 1";

if (!$resultado = $mysqli->query($sql)) {
    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die("Error: " . $mysqli->error . "\n");
}
$msg .= "Se agregaron ".$cantidad." a el stock del producto ".$clave.PHP_EOL; 
//echo "<pre>$sql</pre>".PHP_EOL;

};
$sql = "DELETE from sp_premios_canje WHERE id_sp_comprobante_canje =".$idcanje;

if (!$resultado = $mysqli->query($sql)) {
    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die("Error: " . $mysqli->error . "\n");
}



$sql = "UPDATE  sp_comprobante_canje SET anulado='Si' WHERE id_sp_comprobante_canje =".$idcanje;
$nroCanje=str_pad($idcanje,8,'0',STR_PAD_LEFT );
$msg .= "Se ha anulado el canje ".$nroCanje.PHP_EOL; 

if (!$resultado = $mysqli->query($sql)) {
    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die("Error: " . $mysqli->error . "\n");
}

echo json_encode(array('msg' => $msg));

require_once "mail.php";
$mensaje=" Nro canje: ".$nroCanje.". ";
$Manual = $clienteObj->id_manual_cli;
$txt = "El cliente " . utf8_encode($clienteObj->cliente) . " (ID: " . $Manual . ') Ha anulado un canje ' . PHP_EOL ;
                //$html = "<h1>El cliente " . htmlentities($clienteObj->cliente) . " (id:" . $Manual . ")</h1>\n";
                $html = '<h3>'.$msg.'</h3>';
                $html .= 	'<center><img width="100px" src="cid:check-png"/></center>';
//                $html .= '<h3>Ha Canjeado un total de  ' . $saldo . " puntos</h3>" . PHP_EOL;
//                $html .='<h3>Realizado por: '.$usuarioCanje.'</h3>'.PHP_EOL;
//                 $html .='<h5>generado por administraNET gestión.</h5>'.PHP_EOL;
 $cliente=utf8_encode($clienteObj->cliente) . " (ID: " . $Manual . ")";
 $fecha=date('d/m/y H:i');
	$mailOk=enviarMail('f.calderon@laibero.com.ar', $txt, null, $html, 'Se ha Anulado un canje de ' . utf8_encode($clienteObj->cliente) . ' ' . date('d/m/y H:i'), null);

