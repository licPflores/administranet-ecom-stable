<?php 
require_once 'sesion.inc.php';

$hayTacc = false;
$consultaTacc = "SHOW COLUMNS FROM articulo LIKE 'sin_tacc';";
$hacerTacc = mysqli_query($connV,$consultaTacc);
if(!$hacerTacc){
    echo '<pre>', mysqli_error($connV),'</pre>';
    //return false;
}
if($hacerTacc){
    $registros = mysqli_num_rows($hacerTacc);
    if($registros>0){
        $hayTacc = true;
    }
}
$vuelta= array();
$mensaje='sinTacc';
if($hayTacc){
    $mensaje='ok';
    $tacc = array(
        array('id'=>'', 'name'=>'- tacc -'),

        array('id'=>'Si', 'name'=>'Si'),
        array('id'=>'No','name'=>'No'));
}
$vuelta = array('mensaje'=>$mensaje,'valores'=>$tacc);
echo $json= json_encode($vuelta);
