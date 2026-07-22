<?php 


require_once '../sesion.inc.php';
require_once '../conexion.inc.php';

$conexionVendedor = isset($connV) ? $connV : null;


$codViajante = $_SESSION['vendedor']->CodViajante;
//codigo para probar
//$año_actual = '2023-01';//date('Y-m');
$año_actual = date('Y-m');
$mes_actual = date('m');

$codigoArticulo = 'IDArt';

if($_SESSION['usa_id_manual'] == 'Si'){
    $codigoArticulo = 'id_manual';
}


$fechaFinal = new DateTime();
// $fechaFinal->modify('last day of this month');
// poner fecha hoy
$fechaFinal->modify('today');

// Calcular la fecha de inicio restando 2 meses a la fecha final ahora cambia solo el mes actual
$fechaInicio = clone $fechaFinal;
//$fechaInicio->modify('-2 months');
$fechaInicio->modify('first day of this month');

// Formatear las fechas a 'Y-m-d' para que sean cadenas de texto
$fechaInicio = $fechaInicio->format('Y-m-d');
$fechaFinal = $fechaFinal->format('Y-m-d');


//Funciones SQL


function traerCompPed($codViajante,$connV,$fechaInicio,$fechaFinal){

    $sqlInformacion = " SELECT
                                pd.TipoComprobante,
                                pd.Codigo,
                                pd.ImporteVenta,
                                pd.Anulado,
                                pd.Estado, 
                                pd.CodigoMovimiento,     
                                c.nombre_cliente as nombre
                        FROM comp_ped as pd 
                        LEFT JOIN cliente as c ON pd.Codigo = c.Codigo 
                        WHERE 
                            c.CodViajante=$codViajante 
                            AND  pd.Fecha BETWEEN '$fechaInicio' and '$fechaFinal'
                        ORDER BY pd.Fecha ASC;";


    $hacer = mysqli_query($connV,$sqlInformacion) or die('No puedo consultar la informacion del usuario '.$sqlInformacion);

    if (!$hacer) {
        return false;
    }

    $datosPedidos = array();

    while ($p = mysqli_fetch_object($hacer)) {
        $datosPedidos[] = $p;
    }

    if (empty($datosPedidos)) {
        return false;
    }

    return $datosPedidos;


}

        


function traerCliente($clientes,$connV){

    $datosCliente = array();

    foreach($clientes as $cli){

        

    //     $codigo = $cli['codigo'];

    //     $sqlInformacion = " SELECT
    //     cc.nombre_cliente as nombre
    //     FROM cliente as cc  
    //     WHERE 
    //     cc.Codigo=$codigo ; ";


    //     $hacer = mysqli_query($connV,$sqlInformacion) or die('No puedo consultar la informacion de cuentacliente '.$sqlInformacion);

    //     if($hacer){
            
    //         $p=  mysqli_fetch_object($hacer);

    //         $datos = array(
    //             'codigo' => $cli['codigo'],
    //             'nombre' => $p -> nombre,
    //             'saldo' => intval($cli['saldo']),
    //         );



    //         $datosCliente[] = $datos;
    //     }

    // }

        // la fucion tiene que ir acumulando el saldo del para cada cliente y armar un array de datos clientes con codigo, nombre saldo, el nombra ya viene en el array 
        // con esta estructura
        // [25] => stdClass Object
        // (
        //     [TipoComprobante] => PED
        //     [Codigo] => 2206
        //     [ImporteVenta] => 85756.72
        //     [Anulado] => No
        //     [Estado] => Facturado
        //     [CodigoMovimiento] => 331939
        //     [nombre] => RUIZ CECILIA 
        // )
        // importante: solo sumar los pedidos que no esten cancelados
        $codigo = $cli['Codigo'];
        $nombre = $cli['nombre'];
        $saldo = $cli['ImporteVenta'];

        // agregar al array datos cliente, de tal forma que si el cliente ya existe se sume el saldo
        $existe = false;
        foreach($datosCliente as &$dato){
            if($dato['codigo'] == $codigo){
                $dato['saldo'] += $saldo;
                $existe = true;
                break;
            }
        }
        if (!$existe) {
            $datosCliente[] = array(
                'codigo' => $codigo,
                'nombre' => $nombre,
                // el dato maneja dinero puese ser con decimales?
                'saldo' => floatval($saldo)
                

            );
        }


    }
        // echo '<pre>';
        // print_r($datosCliente);
        // echo '</pre>';
        return $datosCliente;    

     
}

function traerArticulosMasVendidos($datos,$connV){

    global $codigoArticulo;

    $codigos = codMovimiento($datos);

    $sql = " SELECT 
                SUM(
                    CASE 
                        -- Si el peso por unidad es mayor a 0, convierte kilos a unidades
                        WHEN s.unidad_art_peso IS NOT NULL AND s.unidad_art_peso > 0 
                            THEN s.Cantidad / s.unidad_art_peso
                        -- Si no tiene peso definido o es 0, toma la cantidad directa
                        ELSE s.Cantidad 
                    END
                ) AS Cantidad,
                s.$codigoArticulo, 
                s.Descripcion 
            FROM stockp as s
            WHERE s.CodigoMovimiento IN $codigos
            GROUP BY 
                s.$codigoArticulo
            ORDER BY Cantidad desc
            LIMIT 5; 
    ";
    // echo '<pre>';
    // print_r($sql);
    // echo '</pre>';
    $hacer = mysqli_query($connV,$sql) or die('No puedo consultar la informacion de los articulos '.$sql);

    if($hacer){
        $articulos = array();
        while($p=  mysqli_fetch_object($hacer)){
            $articulos[] = $p;
        }

        // echo '<pre>';
        // print_r($articulos);
        // echo '</pre>';

        return $articulos;

    }

}







//Funciones para mostrar informacion

function codMovimiento($datos){
    $codigos = '(';
    foreach($datos as $d){
        $cod = $d -> CodigoMovimiento;
        $codigos.="'".$cod."',";
    }
    $codigos = rtrim($codigos,',');
    $codigos.=')';
    return $codigos;
}




function cantidadPedidos($datos){
    $totalPedidos = 0;
    foreach($datos as $dato){
        if($dato -> TipoComprobante == 'PED'){
            $totalPedidos++;
        }
    }
    //echo 'total de pedidos = '.$totalPedidos;
    return $totalPedidos;
}


function cantidadEfectivoPedidos($datos){
    $totalEfectivo = 0;
    foreach($datos as $dato){
        $aux = $dato -> ImporteVenta;
        $totalEfectivo +=$aux;
        
    }
    //echo 'total de efectivo = '.$totalEfectivo;
    return $totalEfectivo;
}

function cantidadPedidosCancelados($datos){
    $totalCancelados = 0;
    foreach($datos as $dato){
        if($dato -> Anulado == 'Si'){
            $totalCancelados++;
        }
    }
    //echo 'total de pedidos anulados = '.$totalCancelados;
    return $totalCancelados;
}
function cantidadPedidosFacturados($datos){
    $totalFacturados = 0;
    foreach($datos as $dato){
        if($dato -> Estado == 'Facturado'){
            $totalFacturados+=1;
        }
    }
    //echo 'total de pedidos facturados = '.$totalFacturados;
    return $totalFacturados;
}

function cincoPrimerosClientes($arr){
    
    usort($arr, function($a, $b) {
        return $b['saldo'] - $a['saldo'];
    });

    $primerosCincoElementos = array_slice($arr, 0, 5);

    //print('Clientes que mas compraron');
    // return traerCliente($primerosCincoElementos);
    return $primerosCincoElementos;

}


function cincoUltimosClientes($arr){   

    usort($arr, function($a, $b) {
        return $b['saldo'] - $a['saldo'];
    });
    $ultimosCincoElementos = array_slice($arr, -5);

    //print('clientes que menos compraron');
    // return traerCliente($ultimosCincoElementos);
    return $ultimosCincoElementos;
}


  







//Esta funcion va a agrupando clientes por codigo y suma sus respectivos saldos

function crearArrayClientes($datosCuentaCliente){

    $arrClienteS = array();

    
    foreach($datosCuentaCliente as $dato){


        $arrCliente = array();
        $arrCliente['codigo'] = $dato->Codigo;
        $arrCliente['nombre'] = $dato->nombre;
        $arrCliente['saldo'] = $dato->ImporteVenta;


        if ($arrClienteS) {
            $comprobar = true;
            foreach ($arrClienteS as &$arr) {
                if ($arr['codigo'] == $arrCliente['codigo']) {
                    // Actualizar el saldo en el array original (&$arr)
                    $arr['saldo'] += $arrCliente['saldo'];
                    $comprobar = false;
                    break;
                }
            }
            unset($arr); // Liberar la referencia al último elemento del array
            if ($comprobar) {
                $arrClienteS[] = $arrCliente;
            }
        } else {
            $arrClienteS[] = $arrCliente;
        }
    }
    return $arrClienteS;

}

// Esta función va a traer el importe de ventas netas, el total de descuentos otorgados y el porcentaje de descuentos sobre el total de ventas netas.
function traerVentaNeta($codViajante,$connV,$fechaInicio,$fechaFinal){
$porcentajeDescuento =0;
    $sql = "SELECT
            vend.Nombre,
            SUM(
              
              IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV'                 
                #stock.TipoComp ='ND Anul NC'
				OR stock.TipoComp ='ND Anul NC'
            	,stock.PrecioNetoxR,
            	stock.PrecioNetoxR * -1) 
            
           ) AS totalVentaNeta ,
           
           SUM(
             IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV'                 
                #stock.TipoComp ='ND Anul NC'
				OR stock.TipoComp ='ND Anul NC'
            	,stock.ImpDesc,
            	stock.ImpDesc * -1) 
           ) AS descuentoOtorgado
            
            FROM stock
                LEFT JOIN cuentacliente AS cc ON (cc.CodigoMovimiento= stock.CodigoMovimiento) 
                LEFT JOIN articulo AS arti ON arti.IDArt = stock.IDArt               
                LEFT JOIN cliente AS cli ON (cli.Codigo= stock.CodigoCP)
                LEFT JOIN viajantes AS vend ON (vend.CodViajante= cli.CodViajante)
                #LEFT JOIN viajantes AS vend ON (vend.CodViajante= cc.CodViajante)
                LEFT JOIN usuarios AS usu ON (usu.id_usuario=stock.IdUsuario)
                
           WHERE
                ( (stock.Fecha BETWEEN '$fechaInicio' AND '$fechaFinal') )
               
               AND stock.anulado='No'
               AND stock.visualiza_ensamble='No'
               
                AND (stock.TipoComp = 'Venta' 
                    OR stock.TipoComp = 'Venta TPV' 
                    OR stock.TipoComp = 'Devol - Cliente'                                
					OR stock.TipoComp = 'ND Anul NC'                   
                    )
                  AND vend.CodViajante IN ($codViajante)

            GROUP BY vend.CodViajante 
            ORDER BY cli.nombre_cliente ASC,  stock.Fecha ASC;";

    $hacer = mysqli_query($connV, $sql) or die('No puedo consultar la información de ventas netas ' . $sql);

    if ($hacer) {
        $datos = mysqli_fetch_assoc($hacer);
        if (!$datos) {
            return array(
                'totalVentaNeta' => 0,
                'descuentoOtorgado' => 0,
            );
        }

        return $datos;
    }

    return array(
        'totalVentaNeta' => 0,
        'descuentoOtorgado' => 0,
    );
}

function normalizarMonto($valor)
{
    if ($valor === null || $valor === '') {
        return 0;
    }

    return round((float)$valor, 2);
}

function calcularPorcentajeDescuento($ventaNeta, $descuento)
{
    $ventaNeta = (float)$ventaNeta;
    $descuento = (float)$descuento;

    if ($ventaNeta <= 0) {
        return 0;
    }

    return round(($descuento / $ventaNeta) * 100, 2);
}

function mesActualEnEspanol()
{
    $meses = array(
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    );

    $mes = (int)date('n');
    return $meses[$mes] . ' ' . date('Y');
}


function general($codViajante,$connV,$fechaInicio,$fechaFinal){

    $estadisticas = array();


    $datos = traerCompPed($codViajante,$connV,$fechaInicio,$fechaFinal);
    // funcion traer venta Neta trae el importe de ventas netas , y el total de descuentos otorgados y el porcentaje de descuentos sobre el total de ventas netas.
    $datosVentaNeta=traerVentaNeta($codViajante,$connV,$fechaInicio,$fechaFinal);

    $estadisticas['articulos'] = array();
    $estadisticas['cincoPrimerosClientes'] = array();
    $estadisticas['cincoUltimosClientes'] = array();

    if($datos){

        $estadisticas['articulos'] = traerArticulosMasVendidos($datos,$connV);

        $estadisticas['cantidadPedidos'] = cantidadPedidos($datos);

        $estadisticas['cantidadEfectivo'] = cantidadEfectivoPedidos($datos);

        $estadisticas['pedidosCancelados'] = cantidadPedidosCancelados($datos);

        $estadisticas['pedidosFacturados'] = cantidadPedidosFacturados($datos);

        
        $ventasNetas = normalizarMonto($datosVentaNeta['totalVentaNeta']);
        $descuentosOtorgados = normalizarMonto($datosVentaNeta['descuentoOtorgado']);
        $porcentajeDescuento = calcularPorcentajeDescuento($ventasNetas, $descuentosOtorgados);

        $estadisticas['rendimiento'] = array(
            'ventasNetas' => $ventasNetas,
            'descuentosOtorgados' => $descuentosOtorgados,
            'porcentajeDescuento' => $porcentajeDescuento,
            'periodo' => mesActualEnEspanol(),
        );

        $estadisticas['importeVentasNetas'] = $ventasNetas;
        $estadisticas['importeDescuentosOtorgados'] = $descuentosOtorgados;
        $estadisticas['porcentajeDescuentosOtorgados'] = $porcentajeDescuento;



        //$datosCuentaCliente = traerCuentaCliente($codViajante,$connV,$fechaInicio,$fechaFinal);


        $arr =  crearArrayClientes($datos);

        // echo '<pre>';
        // print_r($datos);
        // echo '</pre>';

        $estadisticas['cincoPrimerosClientes'] = cincoPrimerosClientes($arr,$connV);

        $estadisticas['cincoUltimosClientes'] = cincoUltimosClientes($arr,$connV);

    }else{

        $estadisticas['articulos'][] = array(
            "Cantidad" => "0",
            "Descripcion" => "NO HAY ARTICULOS PARA MOSTRAR",
            "IDArt" => 0
        );

        $estadisticas['cantidadPedidos'] = 0;

        $estadisticas['cantidadEfectivo'] = 0;

        $estadisticas['pedidosCancelados'] = 0;

        $estadisticas['pedidosFacturados'] = 0;
        $estadisticas['rendimiento'] = array(
            'ventasNetas' => 0,
            'descuentosOtorgados' => 0,
            'porcentajeDescuento' => 0,
            'periodo' => mesActualEnEspanol(),
        );
        $estadisticas['importeVentasNetas'] = 0;
        $estadisticas['importeDescuentosOtorgados'] = 0;
        $estadisticas['porcentajeDescuentosOtorgados'] = 0;



        $estadisticas['cincoPrimerosClientes'][] = array(
            "codigo" => "0",
            "nombre" => "NO HAY CLIENTES PARA MOSTRAR",
            "saldo" => 0
        );

        $estadisticas['cincoUltimosClientes'][] = array(
            "codigo" => "0",
            "nombre" => "NO HAY CLIENTES PARA MOSTRAR",
            "saldo" => 0
        );
    }
    


    print_r(json_encode($estadisticas));



    }


// echo '<pre>';
// print_r($estadisticas);
// echo '</pre>';

if (isset($_GET['estadisticas']) && $_GET['estadisticas'] == true){

    general($codViajante,$conexionVendedor,$fechaInicio,$fechaFinal);

}

?>


