<?php

require_once 'sesion.inc.php';




//Funciones SQL
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
                'rubro' => $rubroElegido,
                'totalCantidad' => $result->SumaTotalCantidad,
                'porcentajeContribucion' => $porcentaje,
                'porcentajeAcumulado' => $sumaAcumulativa
            );
            $datos[] = $r;
        }



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
                    LIMIT 25";

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
    echo json_encode($informePareto);
}




//Controlador


if(isset($_GET['estadisticas']) && $_GET['estadisticas']==1){

    $periodoTiempoSQL = '';
    $periodoTiempo = $_GET['periodoTiempo'];

    switch($_GET['opcionTiempo']){
        case 'anual':
            $periodoTiempoSQL = datosAnuales($periodoTiempo);
            break;
        case 'semestral':
            $periodoTiempoSQL = datosSemestrales($periodoTiempo);
            break;
        case 'trimestral':
            $periodoTiempoSQL = datosTrimestrales($periodoTiempo);
            break;
        case 'mensual':
            $periodoTiempoSQL = datosMensuales($periodoTiempo);
            break;
    
    }



    if($_GET['tipoEstadisticas'] == 'rubros'){
        rubros($connV,$periodoTiempoSQL);
    }
    if($_GET['tipoEstadisticas'] == 'clientes'){

        clientes($connV,$periodoTiempoSQL);

    }

}


function datosAnuales($datos){

    $where = ' AND YEAR(s.Fecha)='.$datos[0];
    
    return $where;
    
}


function datosSemestrales($datos){

    if($datos[1] == 1){
        $where = ' AND YEAR(s.Fecha)='.$datos[0].' AND MONTH(s.Fecha) BETWEEN 1 AND 6';
    }else{
        $where = ' AND YEAR(s.Fecha)='.$datos[0].' AND MONTH(s.Fecha) BETWEEN 7 AND 12';
    }

    return $where;
}


function datosTrimestrales($datos){

    $trimestre = $datos[1];

    switch ($trimestre) {
        case 1:
            $where = ' AND YEAR(s.Fecha)='.$datos[0].' AND MONTH(s.Fecha) BETWEEN 1 AND 3';
            break;
        case 2:
            $where = ' AND YEAR(s.Fecha)='.$datos[0].' AND MONTH(s.Fecha) BETWEEN 4 AND 6';
            break;
        case 3:
            $where = ' AND YEAR(s.Fecha)='.$datos[0].' AND MONTH(s.Fecha) BETWEEN 7 AND 9';
            break;
        case 4:
            $where = ' AND YEAR(s.Fecha)='.$datos[0].' AND MONTH(s.Fecha) BETWEEN 10 AND 12';
            break;
        default:
            return "Trimestre inválido";
    }
    return $where;
}
    


function datosMensuales($datos){

    $where = ' AND YEAR(s.Fecha)='.$datos[0].' AND MONTH(s.Fecha)='.$datos['1'];
    
    return $where;
}





?>