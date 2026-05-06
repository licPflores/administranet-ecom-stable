<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once '../sesion.inc.php';



$datos = array();
if(isset($_GET['cajas']) && $_GET['cajas']==true){

    $informe = $_GET['informe'];

    if($informe == "cajaEstadistica"){
        echo json_encode( cajaEstadistica($connV,$_GET));
    }
    if($informe == 'cajaDetallada'){
        echo json_encode( listaDetallada($connV,$_GET));
    }


}




//CAJA LISTA DETALLADA

function listaDetallada($connV,$datos){

        // Definición de variables con los valores correspondientes
        
        $fechaInicio = $datos['fechaDesde'];
        $fechaFin = $datos['fechaHasta'];

        // Construcción de la consulta SQL como una cadena de texto en PHP
        $sqlQuery = "SELECT 
                        date_format(caja.fecha,'%d-%m-%Y') as fecha,
                        caja.tipo_comprobante as comp, 
                        caja.nro_comprobante as 'nroComp', 
                        caja.tipo as tipo, 
                        IF(caja.tipo_cp='Cliente', cliente.nombre_cliente, proveedor.Nombre) as 'cliente-proveedor', 
                        FORMAT(IF(caja.ingreso IS NOT NULL, caja.ingreso, 0), 2, 'es_AR') as ingreso,
                        FORMAT(IF(caja.egreso IS NOT NULL, caja.egreso, 0), 2, 'es_AR') as egreso,
                        FORMAT(IF(caja.tipo_comprobante='MCAJ', @T:=0, @T:=@T+(caja.ingreso-caja.egreso)), 2, 'es_AR') as saldo,
                        caja.detalle as detalle
                    FROM   
                        (cliente cliente LEFT OUTER JOIN caja caja ON cliente.Codigo=caja.codigo_cliente)
                        LEFT OUTER JOIN proveedor proveedor ON caja.codigo_prov=proveedor.Codigo
                    WHERE 
                        caja.fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND 1=1  
                    ORDER BY 
                        caja.Fecha ASC, caja.id_caja ASC";

        $hacer = mysqli_query($connV,$sqlQuery) or die('No puedo consultar la informacion de la caja '.$sqlQuery);


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




	  
	  
	  
	  




//CAJA ESTADISTICA

function cajaEstadistica($connV,$datos){

// Definición de variables con los valores correspondientes

$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];

// Construcción de la consulta SQL como una cadena de texto en PHP
$sqlQuery = "SELECT 
                caja.id_caja AS codigo,
                ROUND(SUM(
                    IF(caja.ingreso IS NULL OR caja.ingreso = 0, -1*caja.egreso,
                        IF(caja.egreso IS NULL OR caja.egreso = 0, caja.ingreso, 'error'))
                    ), 2) as saldo,
                CONCAT(caja_abm.nombre_caja,' (',caja_abm.id_caja,')') as ncliente,
                DATE_FORMAT(caja.fecha,'%d-%m-%Y') as fecha
            FROM 
                caja_abm caja_abm 
                INNER JOIN caja caja ON caja_abm.id_caja=caja.id_caja_abm_origen
            WHERE 
                caja.fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND 1=1 AND 
                caja.tipo_comprobante NOT IN ('MCAJ') 
            GROUP BY 
                caja_abm.id_caja, fecha
            ORDER BY 
            fecha, caja_abm.id_caja";

$hacer = mysqli_query($connV,$sqlQuery) or die('No puedo consultar la informacion del banco '.$sqlQuery);


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




?>
