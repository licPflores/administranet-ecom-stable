<?php

require_once '../sesion.inc.php';



//Funciones SQL

//Funcion para rubros
function rubros($connV, $periodo){

    $sqlInformacion = " SELECT 
                    Subquery.CodigoRubro, 
                    SUM(Subquery.TotalCantidad) AS SumaTotalCantidad
                    FROM
                    (SELECT 
                        s.IDArt AS IDArt,
                        s.Descripcion,
                        a.CodigoRubro AS CodigoRubro,
                        SUM(
                            CASE 
                                WHEN s.cantidad_dividir > 1 THEN s.Cantidad / s.cantidad_dividir
                                ELSE s.Cantidad
                            END
                        ) AS TotalCantidad
                    FROM 
                        stock AS s
                    INNER JOIN 
                        articulo AS a ON a.IDArt = s.IDArt
                    WHERE 
                        s.Comprobante IN ('FA', 'FB') 
                        $periodo
                        AND s.Salida > 0
                    GROUP BY 
                        s.IDArt,
                        s.Descripcion,
                        a.CodigoRubro
                    ORDER BY 
                        TotalCantidad DESC) AS Subquery
                    GROUP BY 
                    Subquery.CodigoRubro;
                        ";
       


            $hacer = mysqli_query($connV,$sqlInformacion) or die('No puedo consultar la informacion de ventas'.$sqlInformacion);


            if($hacer){
            $total = array();
            while($p=  mysqli_fetch_object($hacer)){
                $total[] = $p;
            }      
        }

            // Ordenar datos por porcentaje
            usort($total, function ($a, $b) {
                return $b->SumaTotalCantidad - $a->SumaTotalCantidad;
            });
            

        $totalVentas = 0;
        foreach ($total as $result) {
            $totalVentas += $result->SumaTotalCantidad;
        }
        $rubros = traerNombresRubros($connV);


        $datos = array();
        $sumaAcumulativa = 0;
        foreach ($total as $result) {
            $porcentaje = ($result->SumaTotalCantidad / $totalVentas) * 100;
            $rubroElegido = '';
            foreach($rubros as $rub){
                if($rub -> CodigoRubro == $result -> CodigoRubro){
                    $rubroElegido = $rub -> NombreRubro;
                    break;
                }
            }
            $sumaAcumulativa += $porcentaje;

            $r = array(
                'codigo' => $result -> CodigoRubro,
                'rubro' => $rubroElegido,
                'totalCantidad' => $result->SumaTotalCantidad,
                'porcentajeContribucion' => $porcentaje,
                'porcentajeAcumulado' => $sumaAcumulativa
            );
            $datos[] = $r;
        }


        header('Content-Type: application/json');
        echo json_encode($datos);


}



function traerNombresRubros($connV){
            
        $sql= "SELECT 
                    CodigoRubro, NombreRubro
                FROM 
                    Rubro";



        $hacer = mysqli_query($connV,$sql) or die('No puedo consultar la informacion de ventas'.$sql);


        if($hacer){
            $total = array();
            while($p=  mysqli_fetch_object($hacer)){
            $total[] = $p;
            }      
        }

    return $total;

}

// funcion clientes!!

function clientes($connV, $periodo){
    //Obtener los datos de los primeros 20 resultados
    $sql20primeros = "SELECT 
                        s.Codigo AS codigo,
                        c.nombre_cliente AS nombre,
                        SUM(s.Importe) AS TotalCantidad
                    FROM 
                        recibo_factura AS s
                    LEFT JOIN 
                        cliente AS c ON s.Codigo=c.Codigo
                    WHERE 
                        s.TipoComprobante IN ('FA', 'FB') 
                        $periodo 
                    GROUP BY 
                        s.Codigo
                    ORDER BY 
                        TotalCantidad DESC
                    LIMIT 25;";

       

    $hacer1 = mysqli_query($connV, $sql20primeros) or die('No puedo consultar la informacion de ventas' . $sql20primeros);

    if ($hacer1) {
        $primeros20 = array();
        while ($p = mysqli_fetch_object($hacer1)) {
            $primeros20[] = $p;
        }
    }

    // Calcular el total de ventas
    $totalVentas = 0;
    foreach ($primeros20 as $venta) {
        $totalVentas += $venta->TotalCantidad;
    }

    // Calcular el porcentaje acumulado y el porcentaje acumulado normalizado (dividido por el total de ventas)
    $porcentajeAcumulado = 0;
    $informePareto = array();
    foreach ($primeros20 as $venta) {
        $porcentaje = ($venta->TotalCantidad / $totalVentas) * 100;
        $porcentajeAcumulado += $porcentaje;
        $informePareto[] = array(
            'codigo' => $venta->codigo,
            'nombre' => $venta->nombre,
            'totalCantidad' => $venta->TotalCantidad,
            'porcentajeContribucion' => $porcentaje,
            'porcentajeAcumulado' => $porcentajeAcumulado
        );
    }

    // Ordenar el informe por porcentaje descendente
    usort($informePareto, function ($a, $b) {
        return $b['totalCantidad'] - $a['totalCantidad'];
    });

    // Imprimir el informe Pareto
    header('Content-Type: application/json');
    echo json_encode($informePareto);
}


//Funciones de categoria


function traerVentas($connV,$where){



    $sqlInformacion = "SELECT 
                            YEAR(s.Fecha) as Año, MONTH(s.Fecha) as Mes, s.TipoComprobante, SUM(s.SubtotalDesc) as Ventas
                        FROM 
                            cuentacliente AS s 
                        WHERE 
                        s.Anulado='No' AND s.TipoComprobante IN('FA','NC') $where
                        GROUP BY 
                            YEAR(s.Fecha), MONTH(s.Fecha), s.TipoComprobante;
                        ";



    $hacer = mysqli_query($connV,$sqlInformacion) or die('No puedo consultar la informacion de ventas'.$sqlInformacion);


    if($hacer){
        $total = 0;
        while($p=  mysqli_fetch_object($hacer)){
            $comp = $p->TipoComprobante;
            if($comp == 'NC'){
                $total -= $p -> Ventas;
            }else{
                $total += $p -> Ventas;
            }
        }      
    }

    return $total;

}

function traerCompras($connV,$where){

    $sqlInformacion = "SELECT 
                            YEAR(s.Fecha) as Año, MONTH(s.Fecha) as Mes, s.TipoComprobante, SUM(s.SubtotalDesc) as Compras
                        FROM
                            cuentaproveedor AS s LEFT JOIN otro_egreso ON s.CodigoMovimiento=otro_egreso.codigo_movimiento_op
                        WHERE 
                            s.Anulado='No' AND s.TipoComprobante IN('FA','NC') AND ISNULL(otro_egreso.codigo_movimiento_op) $where
                        GROUP BY  
                            YEAR(s.Fecha), MONTH(s.Fecha),s.TipoComprobante;
                        ";

    $hacer = mysqli_query($connV,$sqlInformacion) or die('No puedo consultar la informacion de compras'.$sqlInformacion);


    if($hacer){
        $total = 0;
        while($p=  mysqli_fetch_object($hacer)){
            $comp = $p->TipoComprobante;
            if($comp == 'NC'){
                $total -= $p -> Compras;
            }else{
                $total += $p -> Compras;
            }
        }      
    }
    return $total;


}

function traerGastos($connV,$where){

    $sqlInformacion = "SELECT 
                                YEAR(s.Fecha) as Año, MONTH(s.Fecha) as Mes, s.TipoComprobante, SUM(s.SubtotalDesc) as Gastos
                        FROM
                                cuentaproveedor AS s LEFT JOIN otro_egreso ON s.CodigoMovimiento=otro_egreso.codigo_movimiento_op
                        WHERE 
                                s.Anulado = 'No' AND s.TipoComprobante <> 'EB' AND NOT ISNULL(otro_egreso.codigo_movimiento_op) $where
                        GROUP BY  
                                YEAR(s.Fecha), MONTH(s.Fecha), s.TipoComprobante;	
                        ";

        $hacer = mysqli_query($connV,$sqlInformacion) or die('No puedo consultar la informacion de gastos'.$sqlInformacion);


        if($hacer){
            $total = 0;
            while($p=  mysqli_fetch_object($hacer)){
            
                $comp = $p->TipoComprobante;
                if($comp == 'FA' || $comp == 'FB' || $comp == 'FC' ){
                    $total += $p -> Gastos;
                }else{
                    $total -= $p -> Gastos;
                }
            }      
        }
    return $total;

}

function traerCobranzas($connV,$where){

    $sqlInformacion = "SELECT 
                            YEAR(s.Fecha) as Año, MONTH(s.Fecha) as Mes, s.TipoComprobante, SUM(s.ImporteCobro) as Cobros
                        FROM	
                            cuentacliente as s
                        WHERE
                        s.Anulado='No' AND s.TipoComprobante='REC'  $where
                        GROUP BY
                            YEAR(s.Fecha), MONTH(s.Fecha), s.TipoComprobante;
                        ";

        $hacer = mysqli_query($connV,$sqlInformacion) or die('No puedo consultar la informacion de cobranzas'.$sqlInformacion);


        if($hacer){
            $total = 0;
            while($p=  mysqli_fetch_object($hacer)){
                $total += $p -> Cobros;
            }      
        }
    return $total;

}

function traerPagos($connV,$where){

    $sqlInformacion = "SELECT
                            YEAR(s.Fecha) as Año, MONTH(s.Fecha) as Mes, s.TipoComprobante, SUM(s.ImportePago) as Pagos
                        FROM 
                            cuentaproveedor AS s
                        WHERE
                            s.Anulado = 'No' AND s.TipoComprobante = 'OP' $where
                        GROUP BY 
                            YEAR(s.Fecha), MONTH(s.Fecha), s.TipoComprobante;
                        ";

        $hacer = mysqli_query($connV,$sqlInformacion) or die('No puedo consultar la informacion de pagos'.$sqlInformacion);


        if($hacer){
            $total = 0;
            while($p=  mysqli_fetch_object($hacer)){
                $total += $p ->Pagos;
            }      
        }
    return $total;

}






//Funciones para crear where en sql con distintas fechas


function crearWhereAnual($datos){

    $where = ' AND YEAR(s.Fecha)='.$datos[1];

    return $where;

}


function crearWhereSemestral($datos){

    if($datos[2] == 1){
        $where = ' AND YEAR(s.Fecha)='.$datos[1].' AND MONTH(s.Fecha) BETWEEN 1 AND 6';
    }else{
        $where = ' AND YEAR(s.Fecha)='.$datos[1].' AND MONTH(s.Fecha) BETWEEN 7 AND 12';
    }

    return $where;
}


function crearWhereTrimestral($datos){

    $trimestre = $datos[2];

    switch ($trimestre) {
        case 1:
            $where = ' AND YEAR(s.Fecha)='.$datos[1].' AND MONTH(s.Fecha) BETWEEN 1 AND 3';
            break;
        case 2:
            $where = ' AND YEAR(s.Fecha)='.$datos[1].' AND MONTH(s.Fecha) BETWEEN 4 AND 6';
            break;
        case 3:
            $where = ' AND YEAR(s.Fecha)='.$datos[1].' AND MONTH(s.Fecha) BETWEEN 7 AND 9';
            break;
        case 4:
            $where = ' AND YEAR(s.Fecha)='.$datos[1].' AND MONTH(s.Fecha) BETWEEN 10 AND 12';
            break;
        default:
            return "Trimestre inválido";
    }
    return $where;
}



function crearWhereMensual($datos){

    $where = ' AND YEAR(s.Fecha)='.$datos[1].' AND MONTH(s.Fecha)='.$datos[2];

    return $where;
}




$datos = array();
if(isset($_GET['estadisticasParetto']) && $_GET['estadisticasParetto']==1){


    $cadena = '';
    if(isset($_GET['valoresFiltro'])){

        $cadena = implode(', ', array_map(function($dato) {
            return "'" . $dato . "'";
        }, $_GET['valoresFiltro']));
        
    }

    $periodoTiempoSQL = '';
    $periodoTiempo = $_GET['opcionesPeriodo'];

    switch($periodoTiempo[0]){
        case 'anual':
            $periodoTiempoSQL = crearWhereAnual($periodoTiempo);
            break;
        case 'semestral':
            $periodoTiempoSQL = crearWhereSemestral($periodoTiempo);
            break;
        case 'trimestral':
            $periodoTiempoSQL = crearWhereTrimestral($periodoTiempo);
            break;
        case 'mensual':
            $periodoTiempoSQL = crearWhereMensual($periodoTiempo);
            break;

        }






        if($_GET['tipoListado'] == 'rubros'){

            if($_GET['elegirFiltro'] == 'elegir' && !empty($_GET['valoresFiltro'])){
                
                $periodoTiempoSQL.= ' AND a.CodigoRubro IN ';
                $periodoTiempoSQL.= "(".$cadena.")";
            }

            rubros($connV,$periodoTiempoSQL);

        }
        if($_GET['tipoListado'] == 'clientes'){

            
            if($_GET['elegirFiltro'] == 'elegir'  && !empty($_GET['valoresFiltro'])){
                
                $periodoTiempoSQL.= ' AND s.Codigo IN ';
                $periodoTiempoSQL.= "(".$cadena.")";
            }

            clientes($connV,$periodoTiempoSQL);

        }
        if($_GET['tipoListado'] == 'categoria'){

            $estadisticas = array();

            $estadisticas['ventas'] = traerVentas($connV,$periodoTiempoSQL);
            $estadisticas['compras'] = traerCompras($connV,$periodoTiempoSQL);
            $estadisticas['gastos'] = traerGastos($connV,$periodoTiempoSQL);
            $estadisticas['cobranzas'] = traerCobranzas($connV,$periodoTiempoSQL);
            $estadisticas['pagos'] = traerPagos($connV,$periodoTiempoSQL);
        
            header('Content-Type: application/json');
        
            echo json_encode($estadisticas);
        
        }

}

