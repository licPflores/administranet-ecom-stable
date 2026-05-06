<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once '../sesion.inc.php';



$datos = array();
if(isset($_GET['compras']) && $_GET['compras']==true){



        
    $filtroSql = '';
    $filtroDatos = true;
    if($_GET['filtro'] !== '' && isset($_GET['filtroCodigos'])){

        $codigosFiltro = $_GET['filtroCodigos'];
        $valoresCodigos = array_values($codigosFiltro);
        $listaCodigos = implode(',', $valoresCodigos);

        $filtroDatos = false;
        }
        $filtro = $_GET['filtro'];





    $informe = $_GET['informe'];
    if($informe == 'comprasProveedorComprobante'){
        if($filtro == 'proveedor' && !$filtroDatos){

            $filtroSql = "AND proveedor.Codigo IN  ($listaCodigos)";   
        }
        echo json_encode( proveedorComprobante($connV,$_GET,$filtroSql));
    }
    if($informe == 'comprasProveedorRegistro'){
        if($filtro == 'proveedor' && !$filtroDatos){

            $filtroSql = "AND proveedor.Codigo IN  ($listaCodigos)";   
        }
        echo json_encode( proveedorRegistro($connV,$_GET,$filtroSql));
    }
    if($informe == 'comprasArticuloComprobante'){
        if($filtro == 'articulo' && !$filtroDatos){

            $filtroSql = "AND articulo.IDArt IN  ($listaCodigos)";   
        }
        echo json_encode( articuloComprobante($connV,$_GET,$filtroSql));
    }
    if($informe == 'comprasArticuloRegistro'){
        if($filtro == 'articulo' && !$filtroDatos){

            $filtroSql = "AND articulo.IDArt IN  ($listaCodigos)";   
        }
        echo json_encode( articuloRegistro($connV,$_GET,$filtroSql));
    }

}



//COMPRAS-PROVEEDOR-POR COMPROBANTE

function proveedorComprobante($connV,$datos,$filtroSql){


// Definición de variables con los valores correspondientes
$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];
$fechaDosSql = '';


if($datos['fechaDesdeDos'] !== '' && $datos['fechaHastaDos'] !== ''){
    $fechaInicio2 =$datos['fechaDesdeDos'];
    $fechaFin2 =$datos['fechaHastaDos'];
    $fechaDosSql = "OR cuentaproveedor.Fecha BETWEEN '$fechaInicio2' AND '$fechaFin2' ";
}


// Construcción de la consulta SQL como una cadena de texto en PHP
$sqlQuery = "SELECT 
                proveedor.codigo as codigo,
                DATE_FORMAT(cuentaproveedor.Fecha,'%m-%Y') as fecha, 
                proveedor.Nombre as ncliente,
                ROUND(
                    SUM( 
                        IF(
                            cuentaproveedor.Anulado ='Si' OR (cuentaproveedor.TipoComprobante='ND' AND cuentaproveedor.motivo_nd='Cheque rechazado'), 
                            0,
                            IF(
                                cuentaproveedor.TipoComprobante='NC',
                                cuentaproveedor.ImportePago*(-1),
                                cuentaproveedor.ImporteCompra
                            )
                        )
                    ), 2
                ) AS saldo
            FROM   
                `cuentaproveedor` `cuentaproveedor` 
                INNER JOIN `proveedor` `proveedor` ON `cuentaproveedor`.`Codigo`=`proveedor`.`Codigo`
            WHERE 
                (cuentaproveedor.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'  $fechaDosSql)
                AND cuentaproveedor.TipoComprobante IN('FA','FB','FC','FE','FM','NC','EB','ND') AND 
                cuentaproveedor.CodigoMovimiento <> 0 AND 
                cuentaproveedor.anulado = 'No' AND 
                proveedor.Codigo <> 0 
                $filtroSql

        GROUP BY 
        codigo, DATE_FORMAT(cuentaproveedor.Fecha,'%m-%Y')
        ORDER BY 
        codigo, fecha";


$hacer = mysqli_query($connV,$sqlQuery) or die('No puedo consultar la informacion las compras '.$sqlQuery);


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










//COMPRAS PROVEEDOR POR REGISTRO


function proveedorRegistro($connV,$datos,$filtroSql){

// Definición de variables con los valores correspondientes
$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];
$fechaDosSql = '';


if($datos['fechaDesdeDos'] !== '' && $datos['fechaHastaDos'] !== ''){
    $fechaInicio2 =$datos['fechaDesdeDos'];
    $fechaFin2 =$datos['fechaHastaDos'];
    $fechaDosSql = "OR cuentaproveedor.FechaRegistro BETWEEN '$fechaInicio2' AND '$fechaFin2' ";
}
// Construcción de la consulta SQL como una cadena de texto en PHP
$sqlQuery = "SELECT 
                proveedor.codigo as codigo,
                DATE_FORMAT(cuentaproveedor.FechaRegistro,'%m-%Y') as fecha, 
                proveedor.Nombre as ncliente,
                ROUND(
                    SUM( 
                        IF(
                            cuentaproveedor.Anulado ='Si' OR (cuentaproveedor.TipoComprobante='ND' AND cuentaproveedor.motivo_nd='Cheque rechazado'), 
                            0,
                            IF(
                                cuentaproveedor.TipoComprobante='NC',
                                cuentaproveedor.ImportePago*(-1),
                                cuentaproveedor.ImporteCompra
                            )
                        )
                    ), 2
                ) AS saldo
            FROM   
                `cuentaproveedor` `cuentaproveedor` 
                INNER JOIN `proveedor` `proveedor` ON `cuentaproveedor`.`Codigo`=`proveedor`.`Codigo`
            WHERE 
                (cuentaproveedor.FechaRegistro BETWEEN '$fechaInicio' AND '$fechaFin' $fechaDosSql)
                AND cuentaproveedor.TipoComprobante IN('FA','FB','FC','FE','FM','NC','EB','ND') AND 
                cuentaproveedor.CodigoMovimiento <> 0 AND 
                cuentaproveedor.anulado = 'No' AND 
                proveedor.Codigo <> 0 
                $filtroSql
            GROUP BY 
                codigo, DATE_FORMAT(cuentaproveedor.FechaRegistro,'%m-%Y')
            ORDER BY 
                codigo, fecha";


$hacer = mysqli_query($connV,$sqlQuery) or die('No puedo consultar la informacion las compras '.$sqlQuery);


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



















//COMPRA ARTICULO POR COMPROBANTE

function articuloComprobante($connV,$datos,$filtroSql){


// Definición de variables con los valores correspondientes
$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];
$fechaDosSql = '';


if($datos['fechaDesdeDos'] !== '' && $datos['fechaHastaDos'] !== ''){
    $fechaInicio2 =$datos['fechaDesdeDos'];
    $fechaFin2 =$datos['fechaHastaDos'];
    $fechaDosSql = "OR stock.Fecha BETWEEN '$fechaInicio2' AND '$fechaFin2' ";
}
// Construcción de la consulta SQL como una cadena de texto en PHP
$sqlQuery = "SELECT 
                articulo.IDArt as codigo,
                DATE_FORMAT(stock.Fecha,'%m-%Y') AS fecha,  
                articulo.NombreArticulo AS ncliente, 
                ROUND(SUM(
                    IF(
                        stock.TipoComp IN('Compra','ND Anul NC', 'Anul NC Devol'),
                        stock.PrecioNetoxR,
                        IF(
                            stock.TipoComp IN('Devol - Proveedor','Anul Compra'),
                            stock.PrecioNetoxR * -1,
                            0
                        )
                    )
                ),2) AS saldo
            FROM   
                stock stock 
                INNER JOIN articulo articulo ON stock.IDArt=articulo.IDArt
            WHERE  
                (stock.Fecha BETWEEN '$fechaInicio' AND '$fechaFin' $fechaDosSql)
                AND (ISNULL(stock.Codigogasto) OR stock.Codigogasto = 0) AND
                stock.TipoComp IN ('Compra','Devol - Proveedor','ND Anul NC','Anul NC Devol','Anul Compra') AND 
                stock.anulado = 'No' 
                $filtroSql 
                GROUP BY 
                    codigo, DATE_FORMAT(stock.Fecha,'%m-%Y')
                ORDER BY 
                    codigo, fecha";


$hacer = mysqli_query($connV,$sqlQuery) or die('No puedo consultar la informacion las compras '.$sqlQuery);


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







  
 
 
 
 //COMPRA ARTICULOS POR REGISTRO
 
 function articuloRegistro($connV,$datos,$filtroSql){

// Definición de variables con los valores correspondientes
$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];
$fechaDosSql = '';


if($datos['fechaDesdeDos'] !== '' && $datos['fechaHastaDos'] !== ''){
    $fechaInicio2 =$datos['fechaDesdeDos'];
    $fechaFin2 =$datos['fechaHastaDos'];
    $fechaDosSql = "OR cuentaproveedor.FechaRegistro BETWEEN '$fechaInicio2' AND '$fechaFin2' ";
}
// Construcción de la consulta SQL como una cadena de texto en PHP
$sqlQuery = "SELECT 
                articulo.IDArt as codigo,
                DATE_FORMAT(cuentaproveedor.FechaRegistro,'%m-%Y') AS fecha,  
                articulo.NombreArticulo AS ncliente, 
                stock.Cantidad,
                ROUND(SUM(
                    IF(
                        stock.TipoComp IN('Compra','ND Anul NC', 'Anul NC Devol'),
                        stock.PrecioNetoxR,
                        stock.PrecioNetoxR * -1
                    )
                ),2) AS saldo,
                ROUND(stock.PrecioNetoxR,2) AS PrecioNetoxR
            FROM   
                stock stock 
                INNER JOIN articulo articulo ON stock.IDArt=articulo.IDArt  
                LEFT JOIN cuentaproveedor cuentaproveedor ON cuentaproveedor.CodigoMovimiento = stock.CodigoMovimiento
            WHERE  
                (cuentaproveedor.FechaRegistro BETWEEN '$fechaInicio' AND '$fechaFin' $fechaDosSql)
                AND cuentaproveedor.CodSucursal <> 0 AND
                stock.anulado = 'No' AND 
                stock.TipoComp IN ('Compra','Devol - Proveedor','ND Anul NC' , 'Anul NC Devol', 'Anul Compra') AND
                (ISNULL(stock.Codigogasto) OR stock.Codigogasto = 0) 
                $filtroSql   
                GROUP BY 
                    codigo, DATE_FORMAT(cuentaproveedor.FechaRegistro,'%m-%Y')
                ORDER BY 
                    codigo, fecha";



$hacer = mysqli_query($connV,$sqlQuery) or die('No puedo consultar la informacion las compras '.$sqlQuery);


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


