<?php
ini_set("display_errors",1);
setlocale(LC_MONETARY, 'es_AR');
//header('Content-Type: application/json');
require_once("sesion.inc.php");

$nCampos = Array();


$todas=2;
if(isset($_REQUEST['CajasTodas'])===true)$todas=1;


$sql="SELECT caja_abm.* ,sucursales.id_sucursal ,sucursales.nombre_sucursal as nomb_sucursal ,
concat(caja_abm.nombre_caja,' (',caja_abm.tipo_caja,')') as nombreCaja
FROM caja_abm, sucursales 
WHERE (tipo_caja = 'Acumulativa' OR tipo_caja = 'Punto de Venta' OR tipo_caja = 'Cheque' OR tipo_caja = 'Tarjeta' OR tipo_caja = 'Acumulativa Cheque' OR tipo_caja = 'Acumulativa Otro Medio de Cobro' OR tipo_caja = 'Otro Medio de Cobro' OR tipo_caja = 'Acumulativa Tarjeta') 
And caja_abm.id_sucursal = sucursales.id_sucursal 
AND caja_abm.anulado='No'
ORDER BY nombre_caja";



// function connectDB(){
//     global $servidor;
//     global $baseConecto;
    
//         $server = $servidor;
//         $user = "administranet";
//         $pass = "a7v8xx0805";
//         $bd = $baseConecto;

//     $conexion = mysqli_connect($server, $user, $pass,$bd);

//         if($conexion){
//     //        json_encode( 'La conexion de la base de datos se ha hecho satisfactoriamente');
//         }else{
//             json_encode( 'Ha sucedido un error inexperado en la conexion de la base de datos');
// 			return false;
//         }

//     return $conexion;
// }

// function disconnectDB($conexion){

//     $close = mysqli_close($conexion);

//         if($close){
//          //   echo 'La desconexion de la base de datos se ha hecho satisfactoriamente';
//         }else{
//        //     echo 'Ha sucedido un error inexperado en la desconexion de la base de datos';
//         }   

//     return $close;
// }


//Creamos la conexión con la función anterior

    $conexion = $connV;//connectDB();
	
	if(!isset($sql))die("No tengo SQL");

    //generamos la consulta

        mysqli_set_charset($conexion, "utf8"); //formato de datos utf8

    if(!$result = mysqli_query($conexion, $sql)) die(mysqli_error($conexion).PHP_EOL.$sql); //si la conexión cancelar programa

		$contar = mysqli_num_rows($result);
    $contar--;
	//print_r(mysqli_fetch_field($result));/*
	while ($property = mysqli_fetch_field($result)) {
    $nombre	= $property->name;
    $tipo	= $property->type;
	//print "Nombre: ".$nombre." Tipo: ".$tipo."<br>".PHP_EOL;
	$Campos[] =  $property->name;
	}



    echo '<div class="panelesBloqueInforme"> ';
    echo '<div class="control">';
    echo '<label for="idCaja" class="parametros">Cajas:</label>';
    echo '<select name="idCaja" id="idCaja">';
    echo '<option value="0">Todas las cajas</option>';
    while ($row = mysqli_fetch_object($result)) {
        echo '<option value="' . $row->id_caja . '" >' . htmlentities($row->nombre_caja).'</option>'; 
    }
    echo '</select>';
    
    
    
    echo '</div>';
    echo '</div>';
        
include 'cmdTipo.php';
