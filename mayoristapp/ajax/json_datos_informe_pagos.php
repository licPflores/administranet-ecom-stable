<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once '../sesion.inc.php';

$filtroSql = '';

$datos = array();



if(isset($_GET['pagos']) && $_GET['pagos']==true){




    if($_GET['filtro'] == 'proveedor' && isset($_GET['filtroCodigos'])){


        $codigosFiltro = $_GET['filtroCodigos'];
        $valoresCodigos = array_values($codigosFiltro);
        $listaCodigos = implode(',', $valoresCodigos);

        $filtroSql = "AND proveedor.Codigo IN  ($listaCodigos)";   
       }

        

    $informe = $_GET['informe'];
    if($informe == 'pagosProveedor'){
        echo json_encode(pagosProveedor($connV,$_GET,$filtroSql));
    }
    if($informe == 'comprobantesPagarProveedor'){
        echo json_encode(comprobantesCobrarProveedor($connV,$_GET,$filtroSql));
    }
    if($informe == 'gastosPeriodo'){
        echo json_encode(gastosPeriodo($connV,$_GET,$filtroSql));
    }

}



//PAGOS POR PROVEEDOR

function pagosProveedor($connV,$datos,$filtroSql){

// Definición de variables con los valores correspondientes
$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];
$fechaDosSql = '';


if($datos['fechaDesdeDos'] !== '' && $datos['fechaHastaDos'] !== ''){
    $fechaInicio2 =$datos['fechaDesdeDos'];
    $fechaFin2 =$datos['fechaHastaDos'];
    $fechaDosSql = "OR   `cuentaproveedor`.`Fecha` BETWEEN '$fechaInicio2' AND '$fechaFin2' ";
}

// Construcción de la consulta SQL como una cadena de texto en PHP
$sqlQuery = "SELECT 
                `proveedor`.`Codigo` as codigo,
                DATE_FORMAT(`cuentaproveedor`.`Fecha`,'%m-%Y') AS fecha,
                ROUND(SUM(cuentaproveedor.ImportePago),2) AS saldo, 
                `proveedor`.`Nombre` AS ncliente
            FROM   
                `cuentaproveedor` `cuentaproveedor` 
                INNER JOIN `proveedor` `proveedor` ON `cuentaproveedor`.`Codigo`=`proveedor`.`Codigo`
            WHERE  
                (`cuentaproveedor`.`Fecha` BETWEEN '$fechaInicio' AND '$fechaFin' $fechaDosSql)
                AND cuentaproveedor.TipoComprobante = 'OP' AND 
                cuentaproveedor.CodigoMovimiento <> 0 AND 
                cuentaproveedor.Anulado = 'No' 
                $filtroSql 
            GROUP BY 
                codigo, DATE_FORMAT(`cuentaproveedor`.`Fecha`,'%m-%Y')
            ORDER BY 
                codigo, fecha";


$hacer = mysqli_query($connV,$sqlQuery) or die('No puedo consultar la informacion los pagos '.$sqlQuery);


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










//COMPROBANTES A COBRAR POR PROVEEDOR

function comprobantesCobrarProveedor($connV,$datos,$filtroSql){

// Definición de variables con los valores correspondientes
$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];
$fechaDosSql = '';


if($datos['fechaDesdeDos'] !== '' && $datos['fechaHastaDos'] !== ''){
    $fechaInicio2 =$datos['fechaDesdeDos'];
    $fechaFin2 =$datos['fechaHastaDos'];
    $fechaDosSql = "OR `op_factura`.`Fecha` BETWEEN '$fechaInicio2' AND '$fechaFin2' ";
}

// Construcción de la consulta SQL como una cadena de texto en PHP
$sqlQuery = "SELECT 
                `proveedor`.`Codigo` AS codigo,
                `proveedor`.`Nombre` AS ncliente,
                DATE_FORMAT(`op_factura`.`Fecha`,'%m-%Y') AS fecha, 
                ROUND(SUM(
                    IF(
                        ISNULL(cuentaproveedor.ImportePago),
                        IF(
                            ISNULL(cuentaproveedor.ImporteCompra),
                            0,
                            op_factura.Saldo
                        ),
                        op_factura.Saldo * (-1)
                    )
                ),2) AS saldo
            FROM   
                (((`op_factura` `op_factura` 
                INNER JOIN `cuentaproveedor` `cuentaproveedor` ON `op_factura`.`CodigoMovimiento` = `cuentaproveedor`.`CodigoMovimiento`) 
                INNER JOIN `proveedor` `proveedor` ON `cuentaproveedor`.`Codigo` = `proveedor`.`Codigo`) 
                INNER JOIN `provincia` `provincia` ON `proveedor`.`CodProvincia` = `provincia`.`CodProvincia`) 
                INNER JOIN `departamento` `departamento` ON `proveedor`.`IDDepartamento` = `departamento`.`IDDepartamento`
            WHERE  
                (`op_factura`.`Fecha` BETWEEN '$fechaInicio' AND '$fechaFin' $fechaDosSql )
                AND `op_factura`.`TipoComprobante` <> 'INIC' AND 
                op_factura.Saldo <> 0 AND 
                op_factura.Anulado = 'No' AND 
                op_factura.Estado = 'N/Canc' 
                $filtroSql
            GROUP BY 
                codigo, DATE_FORMAT(`op_factura`.`Fecha`,'%m-%Y')
            ORDER BY 
                codigo, fecha";


$hacer = mysqli_query($connV,$sqlQuery) or die('No puedo consultar la informacion los pagos '.$sqlQuery);


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


















//GASTOS POR PERIODO

function gastosPeriodo($connV,$datos,$filtroSql){
// Definición de variables con los valores correspondientes
$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];
$fechaDosSql = '';


if($datos['fechaDesdeDos'] !== '' && $datos['fechaHastaDos'] !== ''){
    $fechaInicio2 =$datos['fechaDesdeDos'];
    $fechaFin2 =$datos['fechaHastaDos'];
    $fechaDosSql = "OR `otro_egreso`.`fecha_oe` BETWEEN '$fechaInicio2' AND '$fechaFin2' ";
}

// Construcción de la consulta SQL como una cadena de texto en PHP
$sqlQuery = "SELECT 
                otro_egreso.id_oe AS codigo,
                DATE_FORMAT(`otro_egreso`.`fecha_oe`, '%d-%m-%Y') AS fecha,
                `otro_egreso`.`nombre_oe` AS ncliente, 
                ROUND(SUM(`otro_egreso`.`importe_oe`), 2) AS saldo
            FROM   
                `otro_egreso` `otro_egreso`
            WHERE   
                (`otro_egreso`.`fecha_oe` BETWEEN '$fechaInicio' AND '$fechaFin' $fechaDosSql)
                AND otro_egreso.tipo_oe ='Otros Egresos' AND 
                otro_egreso.Anulado ='No' 
             
            GROUP BY 
                ncliente,fecha
            ORDER BY 
                ncliente,fecha";


$hacer = mysqli_query($connV,$sqlQuery) or die('No puedo consultar la informacion los gastos '.$sqlQuery);


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


