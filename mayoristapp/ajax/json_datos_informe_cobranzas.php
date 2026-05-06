<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once '../sesion.inc.php';



$listaCodigos = '';
$filtroDatos = true;
if(isset($_GET['cobranzas']) && $_GET['cobranzas']==true){

    
            $filtroSql = '';
            if($_GET['filtro'] !== '' && isset($_GET['filtroCodigos'])){

                $codigosFiltro = $_GET['filtroCodigos'];
                $valoresCodigos = array_values($codigosFiltro);
                $listaCodigos = implode(',', $valoresCodigos);

                $filtroDatos = false;


}

    $filtro = $_GET['filtro'];
    


    $informe = $_GET['informe'];
    if($informe == 'cobranzasCliente'){


        if($filtro == 'cliente' && !$filtroDatos){

            $filtroSql = "AND cuentacliente.Codigo IN  ($listaCodigos)";   
        }

        echo json_encode( cobranzasCliente($connV,$_GET,$filtroSql));
    }
    if($informe == 'cobranzasVendedor'){

        if($filtro == 'vendedor' && !$filtroDatos){
            $filtroSql = "AND cuentacliente.CodViajante IN ($listaCodigos)";
        }
        echo json_encode( cobranzasVendedor($connV,$_GET,$filtroSql));
    }
    if($informe == 'comprobantesCobrarCliente'){

        if($filtro == 'cliente' && !$filtroDatos){

            $filtroSql = "AND cuentacliente.Codigo IN  ($listaCodigos)";      
        }

        echo json_encode( comprobantesCobrarCliente($connV,$_GET,$filtroSql));
    }
    if($informe == 'comprobantesCobrarVendedor'){

        if($filtro == 'vendedor' && !$filtroDatos){
            $filtroSql =  "AND cuentacliente.CodViajante IN ($listaCodigos)";
        }
        echo json_encode( comprobantesCobrarVendedor($connV,$_GET,$filtroSql));
    }

}


//COBRANZAS POR CLIENTE 



function cobranzasCliente($connV,$datos,$filtroSql){


$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];
$fechaDosSql = '';


if($datos['fechaDesdeDos'] !== '' && $datos['fechaHastaDos'] !== ''){
    $fechaInicio2 =$datos['fechaDesdeDos'];
    $fechaFin2 =$datos['fechaHastaDos'];
    $fechaDosSql = "OR cuentacliente.Fecha BETWEEN '$fechaInicio2' AND '$fechaFin2' ";
}

// Construcción de la consulta SQL como una cadena de texto en PHP
$sqlQuery = "SELECT 
                cliente.codigo as codigo,
                cliente.nombre_cliente AS ncliente,
                ROUND(SUM(
                    IF(cuentacliente.TipoComprobante='REC', cuentacliente.ImporteCobro, cuentacliente.ImporteVenta)
                    ), 2) AS saldo,
                date_format(cuentacliente.Fecha,'%m-%Y') AS fecha
            FROM   
                cliente cliente 
                INNER JOIN cuentacliente cuentacliente ON cliente.Codigo=cuentacliente.Codigo
            WHERE 
                (cuentacliente.Fecha BETWEEN '$fechaInicio' AND '$fechaFin' $fechaDosSql)
                AND cuentacliente.TipoComprobante IN ('REC','FA','FB','FM','FE','FC') AND 
                (cuentacliente.CondVenta ='Contado' OR cuentacliente.CondVenta ='-') AND 
                cuentacliente.CodigoMovimiento <> 0 AND cuentacliente.Anulado ='No'   
                $filtroSql

            GROUP BY 
                DATE_FORMAT(cuentacliente.Fecha,'%m-%Y'),cliente.codigo
            ORDER BY 
            codigo,fecha";

$hacer = mysqli_query($connV,$sqlQuery) or die('No puedo consultar la informacion de las cobranzas '.$sqlQuery);


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













//COBRANZAS POR VENDEDOR

function cobranzasVendedor($connV,$datos,$filtroSql){


// Definición de variables con los valores correspondientes
$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];
$fechaDosSql = '';


if($datos['fechaDesdeDos'] !== '' && $datos['fechaHastaDos'] !== ''){
    $fechaInicio2 =$datos['fechaDesdeDos'];
    $fechaFin2 =$datos['fechaHastaDos'];
    $fechaDosSql = "OR cuentacliente.Fecha BETWEEN '$fechaInicio2' AND '$fechaFin2' ";
}

// Construcción de la consulta SQL como una cadena de texto en PHP
$sqlQuery = "SELECT 
                viajantes.CodViajante AS codigo,
                DATE_FORMAT(cuentacliente.Fecha,'%m-%Y') AS fecha,
                viajantes.Nombre AS ncliente,  
                ROUND(SUM(
                    IF(cuentacliente.TipoComprobante='REC', cuentacliente.ImporteCobro, cuentacliente.ImporteVenta)
                ), 2) AS saldo
            FROM   
                (cuentacliente cuentacliente 
                INNER JOIN viajantes viajantes ON cuentacliente.CodViajante=viajantes.CodViajante) 
                INNER JOIN cliente cliente ON cuentacliente.Codigo=cliente.Codigo
            WHERE  
                (cuentacliente.Fecha BETWEEN '$fechaInicio' AND '$fechaFin' $fechaDosSql)
                AND cuentacliente.TipoComprobante IN ('REC','FA','FB','FM','FE','FC') AND
                cuentacliente.CondVenta IN ('Contado','-') AND
                cuentacliente.Anulado ='No' 
                $filtroSql
            GROUP BY 
                DATE_FORMAT(cuentacliente.Fecha,'%m-%Y'),viajantes.CodViajante
            ORDER BY 
            codigo, fecha";


$hacer = mysqli_query($connV,$sqlQuery) or die('No puedo consultar la informacion las cobranzas '.$sqlQuery);


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

 
 
 
 
 
 
 
 //COMPROBANTES A COBRAR POR VENDEDOR

function comprobantesCobrarVendedor($connV,$datos,$filtroSql){//cuentacliente.Codigo IN ('32873') 

// Definición de variables con los valores correspondientes
$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];
$fechaDosSql = '';


if($datos['fechaDesdeDos'] !== '' && $datos['fechaHastaDos'] !== ''){
    $fechaInicio2 =$datos['fechaDesdeDos'];
    $fechaFin2 =$datos['fechaHastaDos'];
    $fechaDosSql = "OR recibo_factura.Fecha BETWEEN '$fechaInicio2' AND '$fechaFin2' ";
}

// Construcción de la consulta SQL como una cadena de texto en PHP
$sqlQuery = "SELECT 
                viajantes.CodViajante as codigo,
                viajantes.Nombre AS ncliente,
                DATE_FORMAT(recibo_factura.Fecha,'%m-%Y') AS fecha,
                ROUND(SUM(
                    IF(cuentacliente.ImporteCobro IS NOT NULL, recibo_factura.Saldo*(-1),
                        IF(cuentacliente.ImporteVenta IS NOT NULL, recibo_factura.Saldo, 999999999999))
                ), 2) AS saldo
            FROM   
                ((cuentacliente cuentacliente 
                INNER JOIN cliente cliente ON cuentacliente.Codigo=cliente.Codigo) 
                INNER JOIN recibo_factura recibo_factura ON cuentacliente.CodigoMovimiento=recibo_factura.CodigoMovimiento) 
                INNER JOIN viajantes viajantes ON cliente.CodViajante=viajantes.CodViajante
            WHERE 
                (recibo_factura.Fecha BETWEEN '$fechaInicio' AND '$fechaFin' $fechaDosSql )
                AND recibo_factura.TipoComprobante <> 'INIC' AND 
                recibo_factura.Saldo <> 0 AND 
                recibo_factura.Anulado = 'No' AND 
                recibo_factura.Estado = 'N/Canc' 
                $filtroSql
            GROUP BY 
                fecha, codigo
            ORDER BY 
                codigo,fecha";


$hacer = mysqli_query($connV,$sqlQuery) or die('No puedo consultar la informacion las cobranzas '.$sqlQuery);


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
 
 
 
 
 
 
 //comprobantes a cobrar por cliente
 
function comprobantesCobrarCliente($connV,$datos,$filtroSql){

// Definición de variables con los valores correspondientes
$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];
$fechaDosSql = '';


if($datos['fechaDesdeDos'] !== '' && $datos['fechaHastaDos'] !== ''){
    $fechaInicio2 =$datos['fechaDesdeDos'];
    $fechaFin2 =$datos['fechaHastaDos'];
    $fechaDosSql = "OR recibo_factura.Fecha BETWEEN '$fechaInicio2' AND '$fechaFin2' ";
}

// Construcción de la consulta SQL como una cadena de texto en PHP
$sqlQuery = "SELECT 
                cliente.Codigo as codigo,
                SUM(ROUND(
                    IF(cuentacliente.ImporteCobro IS NOT NULL, recibo_factura.Saldo*(-1),
                        IF(cuentacliente.ImporteVenta IS NOT NULL, recibo_factura.Saldo, 99999999999999)), 2)
                ) AS saldo,
                DATE_FORMAT(Recibo_factura.Fecha, '%m-%Y') as fecha,
                cliente.nombre_cliente as ncliente 
            FROM
                (((cuentacliente cuentacliente 
                INNER JOIN cliente cliente ON cuentacliente.Codigo=cliente.Codigo)
                INNER JOIN recibo_factura recibo_factura ON cuentacliente.CodigoMovimiento=recibo_factura.CodigoMovimiento)
                INNER JOIN departamento departamento ON cliente.IDDepartamento=departamento.IDDepartamento)
                INNER JOIN provincia provincia ON cliente.CodProvincia=provincia.CodProvincia 
            WHERE 
                (recibo_factura.Fecha BETWEEN '$fechaInicio' AND '$fechaFin' $fechaDosSql)
                AND recibo_factura.Saldo <> 0 AND 
                recibo_factura.Estado = 'N/Canc' AND 
                recibo_factura.Anulado = 'No' 
                $filtroSql
            GROUP BY 
                fecha, cliente.Codigo
            ORDER BY 
            cliente.Codigo, fecha";


$hacer = mysqli_query($connV,$sqlQuery) or die('No puedo consultar la informacion las cobranzas '.$sqlQuery);


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