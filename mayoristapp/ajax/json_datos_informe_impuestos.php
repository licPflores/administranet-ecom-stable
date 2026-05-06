<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once '../sesion.inc.php';



$datos = array();
if(isset($_GET['impuestos']) && $_GET['impuestos']==true){

    
    
    echo json_encode(impuestos($connV,$_GET));
    

}

//SQL IMPUESTOS

function impuestos($connV,$datos){
// Definición de variables con los valores correspondientes
$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];
$fechaDosSql = '';


if($datos['fechaDesdeDos'] !== '' && $datos['fechaHastaDos'] !== ''){
    $fechaInicio2 =$datos['fechaDesdeDos'];
    $fechaFin2 =$datos['fechaHastaDos'];
    $fechaDosSql = "OR (`otro_egreso`.`fecha_oe` BETWEEN '$fechaInicio2' AND '$fechaFin2' OR `otro_egreso`.`fecha_oe` BETWEEN '$fechaInicio2' AND DATE_SUB('$fechaFin2', INTERVAL 1 DAY))  ";
}

// Construcción de la consulta SQL como una cadena de texto en PHP
$sqlQuery = "SELECT 
                `impuesto`.`id_impuesto` as codigo,
                DATE_FORMAT(`otro_egreso`.`fecha_oe`,'%m-%Y') AS fecha,
                ROUND(SUM(`otro_egreso`.`importe_oe`),2) AS saldo,
                `impuesto`.`nombre_impuesto` AS ncliente
            FROM   
                `otro_egreso` `otro_egreso` 
                INNER JOIN `impuesto` `impuesto` ON `otro_egreso`.`id_impuesto`=`impuesto`.`id_impuesto`
            WHERE 
                (`otro_egreso`.`fecha_oe` BETWEEN '$fechaInicio' AND '$fechaFin'  
                OR `otro_egreso`.`fecha_oe` BETWEEN '$fechaInicio' AND DATE_SUB('$fechaFin', INTERVAL 1 DAY))  $fechaDosSql
                AND otro_egreso.anulado = 'No' AND otro_egreso.tipo_oe = 'Impuestos'  
            GROUP BY 
                codigo, fecha
            ORDER BY 
                fecha, codigo";


$hacer = mysqli_query($connV,$sqlQuery) or die('No puedo consultar la informacion los impuestos '.$sqlQuery);


if($hacer){
    $datosPedidos = array();
    while($p=  mysqli_fetch_object($hacer)){
        $datosPedidos[] = $p;
    }
}
    // echo '<pre>';
    // print_r($datosPedidos);
    // echo '</pre>';

    return $datosPedidos;

}


