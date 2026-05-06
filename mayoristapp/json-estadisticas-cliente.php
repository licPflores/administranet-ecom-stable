<?php 


require_once 'sesion.inc.php';

$codigoArticulo = 'IDArt';

if($_SESSION['usa_id_manual'] == 'Si'){
    $codigoArticulo = 'id_manual';
}



// Calcular la fecha final como el último día del mes actual
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


function traerCompPed($codCliente,$connV,$fechaInicio,$fechaFinal){

    $sqlInformacion = "     SELECT *
                            FROM comp_ped AS cp
                            WHERE cp.Codigo= $codCliente   AND  cp.Fecha BETWEEN '$fechaInicio' and '$fechaFinal'; ";


    $hacer = mysqli_query($connV,$sqlInformacion) or die('No puedo consultar la informacion del cliente '.$sqlInformacion);

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


function traerArticulos($datos,$connV){
    global $codigoArticulo;

    $listaArt = "(";
    foreach($datos as $d){
        $cod = $d -> CodigoMovimiento;
        $listaArt.="'".$cod."',";
    }
    $listaArt = rtrim($listaArt,',');
    $listaArt.=")";

   

    $sql = " SELECT SUM(s.Cantidad) as cantidad, s.$codigoArticulo, s.Descripcion 
                FROM stockp as s
                WHERE s.CodigoMovimiento IN $listaArt
                GROUP BY s.$codigoArticulo
                ORDER BY cantidad ASC; 
                ";
    $hacer = mysqli_query($connV,$sql) or die('No puedo consultar la informacion de los articulos '.$sql);

    if($hacer){
        $articulos = array();
        while($p=  mysqli_fetch_object($hacer)){
            $articulos[] = $p;
        }

        //  echo '<pre>';
        //  print_r($articulos);
        //  echo '</pre>';

        return $articulos;

    }

}

function traerArticulosTipoCliente($tipoCliente,$connV,$fechaInicio,$fechaFinal){

    global $codigoArticulo;

        $sql = "SELECT SUM(s.Cantidad) as cantidad, s.$codigoArticulo, s.Descripcion 
        FROM stock AS s 
        LEFT JOIN cliente AS c ON c.Codigo = s.CodigoCP
        WHERE s.Fecha BETWEEN '$fechaInicio' and '$fechaFinal'
        AND c.TipoCliente= $tipoCliente
        GROUP BY s.$codigoArticulo
        ORDER BY cantidad DESC
        LIMIT 10;";

        $hacer = mysqli_query($connV,$sql) or die('No puedo consultar la informacion de los articulos '.$sql);

        if($hacer){
            $articulos = array();
            while($p=  mysqli_fetch_object($hacer)){
                $articulos[] = $p;
            }

            //echo '<pre>';
            //print_r($articulos);
            //echo '</pre>';

            return $articulos;

        }
}







function pedidosRealizados($datos){

    $contadorPedidos = 0;
    foreach($datos as $d){
        if($d->TipoComprobante == 'PED'){
            $contadorPedidos+=1;
        }
    }
    return $contadorPedidos;
}


function pedidosFacturados($datos){

    $contadorPedidos = 0;
    foreach($datos as $d){
        if($d->TipoComprobante == 'PED' && $d->Estado=='Facturado'){
            $contadorPedidos+=1;
        }
    }
    
    return $contadorPedidos;
}


function articulosMasVendidos($art){
    
    $art = array_reverse($art);
    $primerosCincoElementos = array_slice($art, 0,20);
    
    
    return $primerosCincoElementos;
}

function articulosMenosVendidos($art){
    $primerosCincoElementos = array_slice($art, 0, 5);
    
    
    return $primerosCincoElementos;
}










$vuelta = array();
if(isset($_GET['estadisticas-cliente']) && $_GET['estadisticas-cliente']==true){
    $codCliente = $_GET['cliente'];
    $tipoCliente = $_GET['tipoCliente'];

    $datos = traerCompPed($codCliente,$connV,$fechaInicio,$fechaFinal);

    if($datos){
            $vuelta['totalPedidosRealizados'] = pedidosRealizados($datos);
            $vuelta['totalPedidosFacturados'] = pedidosFacturados($datos);
            $articulos = traerArticulos($datos,$connV);
            $vuelta['articulosMasVendidos'] = articulosMasVendidos($articulos);
            $vuelta['articulosMenosVendidos'] = articulosMenosVendidos($articulos);

            $vuelta['articulosTipoCliente'] = traerArticulosTipoCliente($tipoCliente,$connV,$fechaInicio,$fechaFinal);
    }else{

        $vuelta['totalPedidosRealizados'] = 0;
            $vuelta['totalPedidosFacturados'] = 0;

            $vuelta['articulosMasVendidos'][] = array(
                "cantidad" => "0",
                "Descripcion" => "NO HAY ARTICULOS PARA MOSTRAR",
                "IDArt" => 0
            );


            $vuelta['articulosMenosVendidos'][] = array(
            "cantidad" => "0",
            "Descripcion" => "NO HAY ARTICULOS PARA MOSTRAR",
            "IDArt" => 0
        );

            $vuelta['articulosTipoCliente'][] = array(
            "cantidad" => "0",
            "Descripcion" => "NO HAY ARTICULOS PARA MOSTRAR",
            "IDArt" => 0
        );

    }




    header('Content-Type: application/json');

    echo json_encode($vuelta);
}





?>
