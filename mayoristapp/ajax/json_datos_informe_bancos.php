<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once '../sesion.inc.php';



$datos = array();
if(isset($_GET['bancos']) && $_GET['bancos']==true){

    $informe = $_GET['informe'];
    if($informe == 'libroBanco'){
        echo json_encode(libroBanco($connV,$_GET));
    }
    if($informe == 'saldoBancos'){
        echo json_encode(saldoBanco($connV,$_GET));
    }
    if($informe == 'chequeTerceros'){
        echo json_encode(chequeTerceros($connV,$_GET));
    }
    
    

}


//SQL LIBRO BANCO


function libroBanco($connV,$datos){

$sqlQuery = "SELECT 
                '-' as nombre,
                DATE_FORMAT(librobanco.Fecha,'%d-%m-%Y') AS fecha,
                librobanco.TipoComp as 'tipoComp', 
                librobanco.detalle as 'detalle',
                librobanco.Comprobante as comprobante, 
                FORMAT(IF(librobanco.Debito IS NOT NULL, librobanco.Debito, 0), 2, 'es_AR') AS debito,
                FORMAT(IF(librobanco.Credito IS NOT NULL, librobanco.Credito, 0), 2, 'es_AR') AS credito,
                FORMAT(librobanco.Saldo, 2, 'es_AR') AS saldo,
                IF(librobanco.conciliado = '1', 'Si', 'No') as conciliado
                FROM   
                librobanco librobanco ";



$filtroSql = '';
if($datos['filtro'] !== '' && isset($datos['filtroCodigos'])){

    $codigosFiltro = $datos['filtroCodigos'];
    $valoresCodigos = array_values($codigosFiltro);
    $listaCodigos = implode(',', $valoresCodigos);


    $filtro = $datos['filtro'];
    if($filtro == 'cliente'){
        $sqlQuery= "SELECT 
                        cli.nombre_cliente as nombre,
                        DATE_FORMAT(librobanco.Fecha,'%d-%m-%Y') AS fecha,
                        librobanco.TipoComp as 'tipoComp', 
                        librobanco.detalle as 'detalle',
                        librobanco.Comprobante as comprobante, 
                        FORMAT(IF(librobanco.Debito IS NOT NULL, librobanco.Debito, 0), 2, 'es_AR') AS debito,
                        FORMAT(IF(librobanco.Credito IS NOT NULL, librobanco.Credito, 0), 2, 'es_AR') AS credito,
                        FORMAT(librobanco.Saldo, 2, 'es_AR') AS saldo,
                        IF(librobanco.conciliado = '1', 'Si', 'No') as conciliado
                    FROM   
                        librobanco librobanco     
                    LEFT JOIN cuentacliente AS cc ON cc.CodigoMovimiento=librobanco.CodigoMovimiento 
                    LEFT JOIN cliente  as cli ON cli.Codigo=cc.Codigo ";


        $filtroSql = " AND cc.Codigo IN ($listaCodigos)";   
        }

    if($filtro == 'proveedor'){

        $sqlQuery= "SELECT 
                        pro.Nombre as nombre,
                        DATE_FORMAT(librobanco.Fecha,'%d-%m-%Y') AS fecha,
                        librobanco.TipoComp as 'tipoComp', 
                        librobanco.detalle as 'detalle',
                        librobanco.Comprobante as comprobante, 
                        FORMAT(IF(librobanco.Debito IS NOT NULL, librobanco.Debito, 0), 2, 'es_AR') AS debito,
                        FORMAT(IF(librobanco.Credito IS NOT NULL, librobanco.Credito, 0), 2, 'es_AR') AS credito,
                        FORMAT(librobanco.Saldo, 2, 'es_AR') AS saldo,
                        IF(librobanco.conciliado = '1', 'Si', 'No') as conciliado
                    FROM   
                        librobanco librobanco     
                    LEFT JOIN cuentaproveedor AS cc ON cc.CodigoMovimiento=librobanco.CodigoMovimiento 
                    LEFT JOIN proveedor  as pro ON pro.Codigo=cc.Codigo ";

        
        $filtroSql = " AND cc.Codigo IN ($listaCodigos)";

    }

}


$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];
$fechaSql = "";
$codCuentaSql = '';

if($datos['fechaDesdeDos'] !== '' && $datos['fechaHastaDos'] !== ''){
    $fechaInicio2 =$datos['fechaDesdeDos'];
    $fechaFin2 =$datos['fechaHastaDos'];

    $fechaSql = " WHERE (librobanco.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'   OR librobanco.Fecha BETWEEN '$fechaInicio2' AND '$fechaFin2' )";
 
}else{
    $fechaSql = " WHERE librobanco.Fecha BETWEEN '$fechaInicio' AND '$fechaFin' ";
}


if($datos['cuentaBancaria'] !== '' ){
    $codigoCuenta =  $datos['cuentaBancaria'];
    $codCuentaSql = " AND librobanco.CodBanco =".$codigoCuenta;
}

$sqlQuery .=  $fechaSql;
$sqlQuery .=  $filtroSql;
$sqlQuery .=  $codCuentaSql;
$sqlQuery .= ' ORDER BY fecha ';
$sqlQuery .= ';';


//print($sqlQuery);
$hacer = mysqli_query($connV,$sqlQuery) or die('No puedo consultar la informacion del banco '.$sqlQuery);


if($hacer){
    $datosPedidos = array();
    while($p=  mysqli_fetch_object($hacer)){
        $datosPedidos[] = $p;
    }
}


    return $datosPedidos;

}





	
	
//SQL SALDO DE BANCO

function saldoBanco($connV,$datos){

// Definición de variables con los valores correspondientes


$id = ' ';

$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];
$fechaSql = "";
$codCuentaSql = '';

if($datos['fechaDesdeDos'] !== '' && $datos['fechaHastaDos'] !== ''){
    $fechaInicio2 =$datos['fechaDesdeDos'];
    $fechaFin2 =$datos['fechaHastaDos'];

    $fechaSql = " (librobanco.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'   OR librobanco.Fecha BETWEEN '$fechaInicio2' AND '$fechaFin2' )";
 
}else{
    $fechaSql = "librobanco.Fecha BETWEEN '$fechaInicio' AND '$fechaFin' ";
}

if($datos['cuentaBancaria'] !== '' ){
    $codigoCuenta =  $datos['cuentaBancaria'];
    $codCuentaSql = "AND librobanco.CodBanco =".$codigoCuenta;
}


// Construcción de la consulta SQL como una cadena de texto en PHP
$sqlQuery = "SELECT
                banco.CodBanco as codigo,
                banco.Nombre AS ncliente,
                DATE_FORMAT(librobanco.Fecha,'%m-%Y') AS fecha,
                FORMAT(SUM(librobanco.Saldo), 2, 'es_AR') AS saldo
            FROM 
                librobanco librobanco 
            INNER JOIN banco banco ON librobanco.CodBanco=banco.CodBanco
            WHERE 
               $fechaSql
               $codCuentaSql
            GROUP BY codigo, ncliente
            ORDER BY fecha, ncliente";


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




 
 
 
 
 
 
 
 
 
 
 
//SQL LISTA DE CHEQUES DE TERCEROS
function chequeTerceros($connV,$datos){

$filtroSql = '';
if($datos['filtro'] !== '' && isset($datos['filtroCodigos'])){

    $codigosFiltro = $datos['filtroCodigos'];
    $valoresCodigos = array_values($codigosFiltro);
    $listaCodigos = implode(',', $valoresCodigos);


    $filtro = $datos['filtro'];
    if($filtro == 'cliente'){

        $filtroSql = " AND CodCliente IN ($listaCodigos)";   
        }

    if($filtro == 'proveedor'){
    
        $filtroSql = " AND CodProveedor IN ($listaCodigos)";

    }

}




// Definición de variables con los valores correspondientes
$fechaInicio = $datos['fechaDesde'];
$fechaFin = $datos['fechaHasta'];
$fechaSql = '';
$fechaCheque =  'FechaVto';

if($datos['fechaCheque']== 'emision' ){
    $fechaCheque = 'FechaEmision';
}
if($datos['fechaCheque']== 'cobro' ){
    $fechaCheque = 'FechaCobro';
}


if($datos['fechaDesdeDos'] !== '' && $datos['fechaHastaDos'] !== ''){
    $fechaInicio2 =$datos['fechaDesdeDos'];
    $fechaFin2 =$datos['fechaHastaDos'];

    $fechaSql = " (chequetercero.$fechaCheque BETWEEN '$fechaInicio' AND '$fechaFin'   OR chequetercero.$fechaCheque BETWEEN '$fechaInicio2' AND '$fechaFin2' )";
 
}else{
    $fechaSql = "chequetercero.$fechaCheque BETWEEN '$fechaInicio' AND '$fechaFin' ";
}

$estadoChequeSql = "";
if($datos['estadoCheque'] !== 'Todos'){
    $estado = $datos['estadoCheque'];
    $estadoChequeSql = "AND chequetercero.$estado = 'Si'";
}
$codCuentaSql = "";
if($datos['cuentaBancaria'] !== '' ){
    $codigoCuenta =  $datos['cuentaBancaria'];
    $codCuentaSql = " AND chequetercero.CodBanco =".$codigoCuenta;
}



// Construcción de la consulta SQL como una cadena de texto en PHP
$sqlQuery = "SELECT '-',
                chequetercero.NroCheque, 
                IF(banco.Nombre IS NULL,' - ',banco.Nombre) as banco,
                IF(chequetercero.Librador IS NULL,' - ',chequetercero.Librador) as librador,
                IF(chequetercero.CodCliente IS NULL,' - ',cliente.nombre_cliente) as cliente,
                DATE_FORMAT(chequetercero.FechaEmision,'%d-%m-%Y') as 'fechaEmision',
                DATE_FORMAT(chequetercero.FechaCobro,'%d-%m-%Y') as 'fechaCobro',
                DATE_FORMAT(chequetercero.FechaVto,'%d-%m-%Y') as 'fechaVto',
                FORMAT(chequetercero.Importe, 2, 'es_AR') as importe,
                IF(proveedor.Nombre IS NULL,' - ',proveedor.Nombre) as proveedor, 
                IF(chequetercero.Encartera='Si','En cartera',
                    IF(chequetercero.Rechazado='Si','Rechazado',
                        IF(chequetercero.Depositado='Si','Depositado',
                            IF(chequetercero.Entregado='Si','Entregado a proveedor','Ninguno')))) as estado
            FROM chequetercero 
            LEFT JOIN banco banco ON chequetercero.CodBanco=banco.CodBanco  
            LEFT JOIN proveedor proveedor ON chequetercero.CodProveedor=proveedor.Codigo
            LEFT JOIN cliente cliente ON cliente.Codigo=chequetercero.CodCliente

            WHERE 
            $fechaSql $filtroSql
                AND chequetercero.anulado = 'No' $estadoChequeSql $codCuentaSql 
            ORDER BY chequetercero.FechaVto, chequetercero.CodProveedor, chequetercero.CodBanco;";

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
