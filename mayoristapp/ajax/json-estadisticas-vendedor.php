<?php 


require_once '../sesion.inc.php';


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
$fechaFinal->modify('last day of this month');

// Calcular la fecha de inicio restando 2 meses a la fecha final
$fechaInicio = clone $fechaFinal;
$fechaInicio->modify('-2 months');
$fechaInicio->modify('first day of this month');

// Formatear las fechas a 'Y-m-d' para que sean cadenas de texto
$fechaInicio = $fechaInicio->format('Y-m-d');
$fechaFinal = $fechaFinal->format('Y-m-d');


//Funciones SQL


function traerCompPed($codViajante,$connV,$fechaInicio,$fechaFinal){

    $sqlInformacion = " SELECT
                                TipoComprobante,Codigo,ImporteVenta,Anulado,Estado, CodigoMovimiento
                        FROM comp_ped as pd  
                        WHERE 
                            pd.CodViajante=$codViajante AND  pd.Fecha BETWEEN '$fechaInicio' and '$fechaFinal'; ";


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

        

        $codigo = $cli['codigo'];

        $sqlInformacion = " SELECT
        cc.nombre_cliente as nombre
        FROM cliente as cc  
        WHERE 
        cc.Codigo=$codigo ; ";


        $hacer = mysqli_query($connV,$sqlInformacion) or die('No puedo consultar la informacion de cuentacliente '.$sqlInformacion);

        if($hacer){
            
            $p=  mysqli_fetch_object($hacer);

            $datos = array(
                'codigo' => $cli['codigo'],
                'nombre' => $p -> nombre,
                'saldo' => intval($cli['saldo']),
            );



            $datosCliente[] = $datos;
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

    $sql = " SELECT s.Cantidad, s.$codigoArticulo, s.Descripcion 
            FROM stockp as s
            WHERE s.CodigoMovimiento IN $codigos
            GROUP BY s.$codigoArticulo
            ORDER BY s.Cantidad desc
            LIMIT 5; 
    ";
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

function cincoPrimerosClientes($arr,$connV){
    
    usort($arr, function($a, $b) {
        return $b['saldo'] - $a['saldo'];
    });

    $primerosCincoElementos = array_slice($arr, 0, 5);

    //print('Clientes que mas compraron');
    return traerCliente($primerosCincoElementos,$connV);

}


function cincoUltimosClientes($arr,$connV){   

    usort($arr, function($a, $b) {
        return $b['saldo'] - $a['saldo'];
    });
    $ultimosCincoElementos = array_slice($arr, -5);

    //print('clientes que menos compraron');
    return traerCliente($ultimosCincoElementos,$connV);
}


  







//Esta funcion va a agrupando clientes por codigo y suma sus respectivos saldos

function crearArrayClientes($datosCuentaCliente){

    $arrClienteS = array();

    
    foreach($datosCuentaCliente as $dato){


        $arrCliente = array();
        $arrCliente['codigo'] = $dato->Codigo;
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



function general($codViajante,$connV,$fechaInicio,$fechaFinal){

    $estadisticas = array();


    $datos = traerCompPed($codViajante,$connV,$fechaInicio,$fechaFinal);

    if($datos){

        $estadisticas['articulos'] = traerArticulosMasVendidos($datos,$connV);

        $estadisticas['cantidadPedidos'] = cantidadPedidos($datos);

        $estadisticas['cantidadEfectivo'] = cantidadEfectivoPedidos($datos);

        $estadisticas['pedidosCancelados'] = cantidadPedidosCancelados($datos);

        $estadisticas['pedidosFacturados'] = cantidadPedidosFacturados($datos);


        //$datosCuentaCliente = traerCuentaCliente($codViajante,$connV,$fechaInicio,$fechaFinal);


        $arr =  crearArrayClientes($datos);



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

    general($codViajante,$connV,$fechaInicio,$fechaFinal);

}



 
?>


