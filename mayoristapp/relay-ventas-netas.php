<?php
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', '1');
//header('Content-type: text/html; charset=utf-8');
require_once 'sesion.inc.php';
/*
 * Ventas netas por periodo
 * Ventas por rubro
 * Ventas por subro proveedor.
 * 
 */




/*
 *function: ventas totales, sirve para traerme las ventas totales por periodo
 *@desde:= fecha desde
 *@hasta:= fecha de fin
 *@periodo:= dia, semana, mes
 *@salida: = si es tabla o json para graficos.  
 */
function ventas_totales(
    $usaIdManual,
    $connV,
    $listarPor = null,
    $filtrarPor = null,
    $vendedor = null,
    $desde = null,
    $hasta = null,
    $periodo = null,
    $salida = null
) {
    $arrResultado = array();
    $agrupar = "mes";
    $comoSumo = "";
    $primerAgrupo = "";
    $segundoAgrupo = "";
    $where = "";
    $orderby = "";

    $listaFiltro = explode("||", $filtrarPor);
    switch ($periodo) {
        case "dia":
            $agrupar = "dia,semana";
            break;
        case "semana":
            $agrupar = "semana,mes";
            break;
        case "mes":
            $agrupar = "mes,aa";
            break;
    }
    /*como sumo*/
    $leftJoin = "";
    switch ($listarPor) {
        case 'cliente':
            $comoSumo = " SUM(IF(cc.TipoComprobante<>'NCA' 
                        AND cc.TipoComprobante<>'NCB', 
                        cc.SubTotalDesc,0)) -
                        SUM(IF(cc.TipoComprobante='NCA' 
                        OR cc.TipoComprobante='NCB', 
                        cc.SubTotalDesc,0)) AS total ";
            break;
        case 'articulo':
            // tengo que multiplicar por 1000 si esta en gramos.
            $comoSumo = " SUM( IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV' OR 
                stock.TipoComp = 'Anul NC Devol' OR 
                stock.TipoComp ='ND Anul NC'
            ,stock.PrecioNetoxR,stock.PrecioNetoxR * -1)) AS total";
            $leftJoin = " LEFT JOIN stock ON (cc.CodigoMovimiento= stock.CodigoMovimiento) 
                LEFT JOIN articulo AS arti ON arti.IDArt = stock.IDArt";
            // medir por peso.
            break;
    }

    /*campos a listar*/
    switch ($listarPor) {
        case 'cliente':

            if ($usaIdManual == 'Si') {
                $primerAgrupo = "cli.id_manual_cli AS cod,concat(' (cod: ',cli.id_manual_cli,') ',cli.nombre_cliente)  As nom,";
                $agrupar .= ",cli.id_manual_cli";
                $orderby .= "cli.nombre_cliente ASC, ";
                $where .= " AND NOT ISNULL(arti.id_manual) ";
            } else {
                $primerAgrupo = "cli.Codigo AS cod,cli.nombre_cliente  As nom,";
                $agrupar .= ",cli.Codigo";
                $orderby .= "cli.nombre_cliente ASC, ";
            }
            break;
        case 'tipocliente':
            $primerAgrupo = "tpcli.IDTipoCliente AS cod,tpcli.NombreTipoCliente  As nom,";
            $agrupar .= ",tpcli.IDTipoCliente";
            $orderby .= "tpcli.NombreTipoCliente ASC, ";
            break;
        case 'vendedor':
            $primerAgrupo = "vend.CodViajante AS cod,vend.Nombre  As nom,";
            $agrupar .= ",vend.CodViajante";
            $orderby .= "vend.Nombre ASC, ";
            break;
        case 'articulo':
            //             $primerAgrupo = "arti.IDArt AS cod,arti.NombreArticulo  As nom,";
            //             $agrupar .=",arti.IDArt";
            //             $orderby .="arti.NombreArticulo ASC, ";
            if ($usaIdManual == 'Si') {
                $primerAgrupo = "arti.id_manual AS cod,concat(' (cod: ',arti.id_manual,') ',arti.NombreArticulo)  As nom,";
                $agrupar .= ",arti.id_manual";
                $orderby .= "arti.NombreArticulo ASC, ";
                $where .= " AND NOT ISNULL(arti.id_manual) ";
            } else {
                $primerAgrupo = "arti.IDArt AS cod,concat(' (cod: ',arti.IDArt,') ',arti.NombreArticulo)  As nom,";
                $agrupar .= ",arti.IDArt";
                $orderby .= "arti.NombreArticulo ASC, ";
            }
            break;
        case 'proveedor':
            if ($usaIdManual == 'Si') {
                $primerAgrupo = " prov.id_manual_prov AS cod,concat(' (cod: ',prov.id_manual_prov,') ',prov.Nombre) As nom,";
                $agrupar .= ",prov.id_manual_prov";
                $orderby .= " prov.Nombre ASC, ";
                $where .= " AND NOT ISNULL(prov.id_manual_prov) ";
            } else {
                $primerAgrupo = " prov.Codigo AS cod,prov.Nombre As nom,";
                $agrupar .= ",prov.Codigo";
                $orderby .= " prov.Nombre ASC, ";
            }
            break;
        case 'zona':
            $primerAgrupo = " zonas.id_zona AS cod,zonas.nombre_zona As nom,";
            $agrupar .= ",zonas.id_zona";
            $orderby .= " zonas.nombre_zona ASC, ";

            break;
        case 'rubro':
            $primerAgrupo = " cat.id_categoria AS cod, cat.nombre_categoria AS nom,";
            $agrupar .= ",cat.id_categoria";
            $orderby .= " cat.nombre_categoria ASC, ";
            break;
        case 'rubro':
            $primerAgrupo = " ru.CodigoRubro AS cod, ru.NombreRubro AS nom,";
            $agrupar .= ",ru.CodigoRubro";
            $orderby .= " ru.NombreRubro ASC, ";
            break;
        case 'subrubro':
            $primerAgrupo = "srub.IdSubRubro AS cod,srub.NombreSubRubro As nom"
                . ",ru.CodigoRubro AS cod3, ru.NombreRubro AS nom3,";
            $agrupar .= ",srub.IdSubRubro";
            $orderby .= " ru.NombreRubro ASC, srub.NombreSubRubro ASC, ";
            break;
    }

    $arrFiltros = array();


    foreach ($listaFiltro as $valorFiltro) {

        $datoFiltro = explode("|", $valorFiltro);

        $arrFiltros[$datoFiltro[0]][] = $datoFiltro[1];
    }

    foreach ($arrFiltros as $clave => $fi) {
        //var_dump($fi);
        //print_r($fi);
        switch ($clave) {
            case 'cliente':
                // no puedo volver a agrupar.
                if ($listarPor != 'cliente') {
                    $primerAgrupo .= 'cli.Codigo AS cod2,cli.nombre_cliente  As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.

                if (!in_array("todos", $fi)) {
                    $where  .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'tipocliente':
                // no puedo volver a agrupar.
                if ($listarPor != 'tipocliente') {
                    $primerAgrupo .= 'tpcli.IDTipoCliente AS cod2,tpcli.NombreTipoCliente As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.

                if (!in_array("todos", $fi)) {
                    $where  .= ' AND tpcli.IDTipoCliente IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'vendedor':
                // no puedo volver a agrupar.
                if (!in_array("todos", $fi)) {
                    $primerAgrupo .= 'vend.CodViajante AS cod2,vend.Nombre  As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND vend.CodViajante IN (' . implode(",", $fi) . ")";
                }
                break;
            case 'articulo':
                // no puedo volver a agrupar.
                if ($listarPor != 'articulo') {
                    $primerAgrupo .= 'arti.IDArt AS cod2,arti.NombreArticulo  As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND arti.IDArt IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'proveedor':
                // no puedo volver a agrupar.
                if ($listarPor != 'proveedor') {
                    $primerAgrupo .= 'prov.Codigo AS cod2,prov.Nombre As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND prov.Codigo IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'zona':
                // no puedo volver a agrupar.
                if ($listarPor != 'zona') {
                    $primerAgrupo .= ' zonas.id_zona AS cod2,zonas.nombre_zona As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND  zonas.id_zona IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'rubro':
                // no puedo volver a agrupar.
                if ($listarPor != 'rubro') {
                    $primerAgrupo .= 'ru.CodigoRubro AS cod2, ru.NombreRubro AS nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND ru.CodigoRubro IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'subrubro':
                // no puedo volver a agrupar.
                if ($listarPor != 'subrubro') {
                    $primerAgrupo .= 'srub.IdSubRubro AS cod,srub.NombreSubRubro As nom,';
                }
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND srub.IdSubRubro IN (' . implode(",", $fi) . ")";
                }

                break;

            case 'marca':
                // no puedo volver a agrupar.
                //                if($listarPor!='subrubro'){
                //                    $primerAgrupo .='srub.IdSubRubro AS cod,srub.NombreSubRubro As nom,';
                //
                //                }
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND marca.CodMarca IN (' . implode(",", $fi) . ")";
                }

                break;
        }
    }

    // armar el sql a partir 

    $sql = "SELECT 
                    {$primerAgrupo}
                    {$segundoAgrupo}
                    DAY(cc.Fecha) As dia,
                    WEEKOFYEAR(cc.Fecha) AS semana,
                    MONTH(cc.Fecha) AS mes,
                    YEAR(cc.Fecha) AS aa,
                    DATE_FORMAT(
                        STR_TO_DATE(CONCAT(YEARWEEK(cc.Fecha),
                        'Monday'),'%X%V %W'),'%d/%m') AS PrimerDiaSemana,  
                    DATE_FORMAT(
                    STR_TO_DATE(CONCAT(YEARWEEK(cc.Fecha),
                    'Saturday'),'%X%V %W'),'%d/%m') AS UltimoDiaSemana, 
                    {$comoSumo}       
            FROM cuentacliente AS cc
            {$leftJoin}
            LEFT JOIN cliente AS cli ON (cli.Codigo= cc.Codigo) 
            LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
            WHERE
            cc.CodViajante = {$vendedor}
            AND cc.Fecha BETWEEN '{$desde}' AND '{$hasta}'
            AND cc.`TipoComprobante`<>'NDA' 
            AND cc.`TipoComprobante`<>'NDB' 
            AND cc.`TipoComprobante`<>'REC' 
            AND cc.`Anulado` ='No'
             {$where}    

            GROUP BY {$agrupar} ORDER BY {$orderby} cc.Fecha ASC";
    //            GROUP BY {$agrupar}
    //            ORDER BY cuentacliente.Fecha ASC";

    // tengo que trabajar con arrays para devolver el resultado final, tanto
    // para json como para tabla html. con totales.
    $hacer = mysqli_query($connV, $sql) or die("no puedo hacer la consulta de las ventas" . mysqli_error($connV) . "<pre>" . $sql . "</pre>");

    while ($r = mysqli_fetch_assoc($hacer)) {
        $arrResultado[] = $r;
    }

    // SIN RESULTADO
    //print_r($arrResultado);

    if (empty($arrResultado)) {
        return "vacio";
    }

    /*
     * SALIDA TABLA HTML
     */
    if ($salida == "html") {
        //listar por lo coloco en el primer titulo
        switch ($listarPor) {
            case 'cliente':
                $titulo[0] = array("titulo" => "Cliente", "span" => 2, "rowspan" => 1);
                break;
            case 'vendedor':
                $titulo[0] = array("titulo" => "Vendedor", "span" => 2, "rowspan" => 1);
                break;
            case 'articulo':
                $titulo[0] = array("titulo" => "Articulo", "span" => 2, "rowspan" => 1);
                break;
            case 'proveedor':
                $titulo[0] = array("titulo" => "Proveedor", "span" => 2, "rowspan" => 1);
                break;
            case 'zona':
                $titulo[0] = array("titulo" => "Zona", "span" => 2, "rowspan" => 1);
                break;
            case 'rubro':
                $titulo[0] = array("titulo" => "Rubro", "span" => 2, "rowspan" => 1);
                break;
            case 'subrubro':
                $titulo[0] = array("titulo" => "Sub Rubro - Rubro", "span" => 2, "rowspan" => 1);
                break;
        }

        // filtrar por y su valor esto lo coloco en el segundo titulo.
        $arrFiltros = array();
        //      print_r($filtrarPor);
        foreach ($listaFiltro as $ff) {
            $datoFiltro = explode("|", $ff);
            $arrFiltros[$datoFiltro[0]][] = $datoFiltro[2];
        }
        // si el filtro es todos, no tengo que seguir buscando por si se eligio otra opcion
        foreach ($arrFiltros as $key => $fil) {
            switch ($key) {
                case 'cliente':
                    // no puedo volver a agrupar.
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    $tituloFil .= " - Cliente: " . implode(",", $fil);

                    break;
                case 'vendedor':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Vendedor: " . implode(",", $fil);

                    break;
                case 'articulo':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Art: " . implode(",", $fil);
                    break;
                case 'proveedor':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Prov: " . implode(",", $fil);
                    break;
                case 'zona':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Zona: " . implode(",", $fil);
                    break;
                case 'rubro':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Rubro: " . implode(",", $fil);
                    break;
                case 'subrubro':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - SubR: " . implode(",", $fil);


                    break;
            }
        }
        // colocar en el titulo si estoy haciendo listado por tipo, monto, peso, unidades
        $tituloTipo = "";
        $tipo = "monto";
        switch ($tipo) {
            case "un":
                $tituloTipo = "UNIDADES ";
                break;
            case "peso":
                $tituloTipo = "KILOGRAMOS";
                break;
            case "monto":
                $tituloTipo = "VENTAS NETAS";
                break;
        }

        if (empty($filtrarPor)) {
            $titulo[1] = array("titulo" => "{$tituloTipo} x {$periodo} ", "span" => 2, "rowspan" => 1);
        } else {
            $titulo[1] = array("titulo" => "{$tituloTipo} x {$periodo}  /  {$tituloFil}", "span" => 2, "rowspan" => 1);
        }

        /**
         * CABECERAS TH de las tablas.
         */
        foreach ($arrResultado as $campo) {

            switch ($periodo) {
                case "dia":
                    $cabecera[] = array("a" => $campo["aa"], "m" => $campo["mes"], "i" => $campo["dia"] . $campo["mes"], "th" => $campo["dia"]);
                    $cabeceraT[$campo["dia"] . $campo["mes"]] = $campo["dia"];
                    break;
                case "semana":
                    $cabecera[] = array("a" => $campo["aa"], "m" => $campo["mes"], "i" => $campo["semana"] . $campo["mes"], "th" => $campo["semana"]);
                    $cabeceraT[$campo["semana"] . $campo["mes"]] = $campo["semana"] . " - " . $campo["PrimerDiaSemana"] . " al " . $campo["UltimoDiaSemana"];
                    break;
                case "mes":
                    $cabecera[] = array("a" => $campo["aa"], "m" => $campo["mes"], "i" => $campo["aa"] . $campo["mes"], "th" => utf8_encode(strftime("%b", mktime(0, 0, 0, $campo["mes"], $campo["dia"], $campo["aa"]))));
                    $cabeceraT[$campo["aa"] . $campo["mes"]] = utf8_encode(strftime("%b", mktime(0, 0, 0, $campo["mes"], $campo["dia"], $campo["aa"])));
                    break;
            }

            $totalGral = $totalGral  + $campo["total"];
        }
        //print_r($totalGral);
        $cabeceraTT = $cabeceraT;
        // este indice ith, es para guardar las claves de combinacion de dia -mes, semana- mes, aa-mes
        $ith = array();
        switch ($periodo) {
            case "dia":
                $ith = array("dia", "mes");
                $cadTitulo = "%b del %Y";
                //$textoT = utf8_encode(strftime("%B del %Y", mktime(0, 0, 0, $aa, $campo["dia"], $campo["aa"])));                        
                break;
            case "semana":
                $ith = array("semana", "mes");
                $cadTitulo = "%b del %Y";
                ///$textoT = utf8_encode(strftime("%B del %Y", mktime(0, 0, 0, $aa, $campo["dia"], $campo["aa"])));                        
                break;
            case "mes":
                //                $ith = array("mes","aa");
                $ith = array("aa", "mes");
                $cadTitulo = "%Y";
                //$textoT = utf8_encode(strftime("%Y", mktime(0, 0, 0, $campo["mes"], $campo["dia"], $aa)));                        
                break;
        }

        /*
         * TITULOS
         */
        // recorro las cabeceras y tengo que obtener el mes o dia u año segun lo que tenga.
        // en el periodo para armar los titulos .
        $tit = 0;
        $aaa = 0;
        $mm = 0;
        $colspan = 0;
        $col = 0;
        $ii = array();
        //        print_r($titulo);
        foreach ($cabecera as $ca) {

            if ($tit == 0) {
                $mm = $ca["m"];
                $aaa = $ca["a"];
                $ii[] = $ca["i"];
                $tit++;
            } else {
                if ($mm != $ca["m"] || $aaa != $ca["a"]) {
                    $colspan = count($ii);
                    $textoT = utf8_encode(strftime($cadTitulo, mktime(0, 0, 0, $mm, 1, $aaa)));
                    //                    $titulo[$mm.$aaa]= array("titulo" => $textoT,"span" => $colspan,"rowspan"=>1);
                    $titulo[$aaa . $mm] = array("titulo" => $textoT, "span" => $colspan, "rowspan" => 1);
                    $mm = $ca["m"];
                    $aaa = $ca["a"];
                    $ii = array($ca["i"]);
                }
                if (!in_array($ca["i"], $ii)) {
                    $ii[] = $ca["i"];
                }
            }
        }
        $colspan = count($ii);
        $textoT = utf8_encode(strftime($cadTitulo, mktime(0, 0, 0, $mm, 1, $aaa)));
        $titulo[$aaa . $mm] = array("titulo" => $textoT, "span" => $colspan, "rowspan" => 1);
        $titulo[] = array("titulo" => "SubTotal", "span" => 1, "rowspan" => 2);

        /***Cambio de cabeceras*/

        /*
         * DATOS
         */
        $rng = 0;
        // rubro es el cod de cualquier campo.
        $rubro = "";
        $totaFila = 0;
        if ($grafico == 1) {
            // grafico de barras
            $arrayChart = array(
                "cols" => array(
                    array(
                        "id" => 0,
                        "label" => "Periodo {$periodo}",
                        "type" => "string"
                    ),

                ),
                "rows" => array()
            );
            // grafico de torta
            $arrayChartT = array(
                "cols" => array(
                    array(
                        "id" => 0,
                        "label" => "Rubro",
                        "type" => "string"
                    ),
                    array(
                        "id" => 1,
                        "label" => "Ventas",
                        "type" => "number"
                    ),
                    array(
                        "id" => null,
                        "label" => "",
                        "pattern" => "",
                        "type" => "string",
                        "p" => array("role" => "style"),
                    ),

                ),
                "rows" => array()
            );
        }

        // dato de tabla html   
        //echo "<pre>";
        foreach ($arrResultado as $campo) {
            $importeNc = 0;
            // buscar la notas de credito desde el array solo si correspondde.
            $icampo = intval($campo[$ith[0]] . $campo[$ith[1]]);
            if ($rubro === "") {
                $rubro = $campo["cod"];
                //tabla
                $renglon[$rubro][] = getSubString($campo["nom"]);

                //grafico
                $arrayChart["cols"][] = array(
                    "id" => $rubro,
                    "label" => $campo["nom"],
                    "type" => "number"
                );
                //recorro las cabeceras y coloco lo que hace falta
                foreach ($cabeceraT as $key => $ca) {
                    if ($icampo === $key) {
                        //controlo si hay nc para buscar datos

                        //                        if($traigoArrayNc==0){
                        //                            $arrValor = array("cod"=> $rubro, $ith[0]=> $campo[$ith[0]],$ith[1] => $campo[$ith[1]]);
                        //                            $keyNc = multidimensional_search($arrayNc, $arrValor);
                        //                            $importeNc = $arrayNc[$keyNc]["importe"];
                        //                            
                        //                        }

                        $renglon[$rubro][$icampo] = $campo["total"];

                        // localizar en el array nc las nc.
                    } else {

                        $renglon[$rubro][$key] = 0;
                    }
                }
                $totalFila += $campo["total"];
            } else {
                if ($rubro != $campo["cod"]) {
                    $renglon[$rubro]["subt"] = $totalFila;
                    $rubro = $campo["cod"];
                    $renglon[$rubro][] = $campo["nom"];
                    //                     $renglon[$rubro][]= getSubString($campo["nom"]);
                    $arrayChart["cols"][] = array(
                        "id" => $rubro,
                        "label" => $campo["nom"],
                        "type" => "number"
                    );
                    // cabeceras
                    foreach ($cabeceraT as $key => $ca) {
                        if ($icampo === $key) {

                            //                            if($traigoArrayNc==0){
                            //                                $arrValor = array("cod"=> $rubro, $ith[0]=> $campo[$ith[0]],$ith[1] => $campo[$ith[1]]);
                            //                                $keyNc = multidimensional_search($arrayNc, $arrValor);
                            //                                $importeNc = $arrayNc[$keyNc]["importe"];
                            //                                
                            //                            }

                            $renglon[$rubro][$icampo] = $campo["total"];
                        } else {
                            $renglon[$rubro][$key] = 0;
                        }
                    }

                    $totalFila = $campo["total"];
                } else {
                    // cabeceras
                    foreach ($cabeceraT as $key => $ca) {
                        if ($icampo === $key) {
                            $importeNc = 0;
                            //                            if($traigoArrayNc==0){
                            //                                $arrValor = array("cod"=> $rubro, $ith[0]=> $campo[$ith[0]],$ith[1] => $campo[$ith[1]]);
                            //                                $keyNc = multidimensional_search($arrayNc, $arrValor);
                            //                                $importeNc = $arrayNc[$keyNc]["importe"];
                            //                                
                            //                            }

                            $renglon[$rubro][$icampo] = $campo["total"];
                        }
                    }
                    $totalFila += $campo["total"];
                }
            }
        }

        //        $renglon[$rubro]["subt"]= number_format($totalFila,2,".","");
        $renglon[$rubro]["subt"] = $totalFila;

        if ($grafico == 1) {
            /*
             * Grafico
             * **/
            $arrColor = array(
                1 => array(
                    "rgba(0, 172, 212, 0.5)",
                    "rgba(0, 172, 212, 0.8)",
                    "rgba(0, 172, 212, 0.75)",
                    "rgba(0, 172, 212, 1)",
                    "#00A4CC"
                ),

                2 => array(
                    "rgba(248, 147, 31, 0.5)",
                    "rgba(248, 147, 31, 0.8)",
                    "rgba(248, 147, 31, 0.75)",
                    "rgba(248, 147, 31, 1)",
                    "#109618"
                ),
                3 => array(
                    "rgba(34, 181, 75, 0.5)",
                    "rgba(34, 181, 75, 0.8)",
                    "rgba(34, 181, 75, 0.75)",
                    "rgba(34, 181, 75, 1)",
                    //"lightskyblue"
                    "#FF9900"
                )
            );

            $optionChart = array(
                "title" => "Ventas netas {$listarPor} - {$periodo} / {$tituloFil}",
                "width" => 700,
                "height" => 400,
                "hAxis" => array("title" => "Período " . $periodo)
            );
            $optionChartT = array(
                "title" => "Ventas netas {$listarPor} - {$periodo} / {$tituloFil}",
                "is3D" => true,
                "width" => 700,
                "height" => 400
            );

            //$linea=array();
            foreach ($cabeceraTT as $key => $c) {
                $linea = array();
                // recorro todas las cabeceras y me tiene que hacer una linea nueva
                $linea[] = array("v" => $c);
                foreach ($renglon as $clave => $reng) {
                    // recorro los renglones y prgunto si hay cabecera
                    foreach ($reng as $keyR => $r) {
                        //print_r($keyR);
                        if ($key == $keyR) {
                            $valor = $r;
                            $valorF = "$" . number_format($r, 2, ",", ".");
                            $linea[] = array("v" => $valor, "f" => $valorF);
                        }
                    }
                    // coloco el color de la series
                    $optionChart["series"][$clave - 1] = array("color" => $arrColor[$clave][4]);
                    $optionChartT["slices"][$clave - 1] = array("color" => $arrColor[$clave][4]);
                }
                //$style= "opacity: 0.2";
                $arrayChart["rows"][] = array("c" => $linea);
            }


            //series:{0:{color:'green'},1:{color:'yellow'}}

            foreach ($renglon as $key => $reng) {
                $nombre = $reng[0];
                $valorT = $reng["subt"] + 0;
                unset($reng[0]);
                unset($reng[$cuantos]);

                $cabeza = $nombre;
                $valor = $valorT;
                $valorF = "$" . number_format($valorT, 2, ",", ".");
                $style = "color: " . $arrColor[$key][4];
                $arrayChartT["rows"][] = array(
                    "c" => array(
                        array("v" => $cabeza),
                        array("v" => $valor, "f" => $valorF),
                        array("v" => $style)
                    )
                );
            }
        } /* Fin de graficos*/


        //        print_r($arrayChartT);
        /**Envio Final*/
        $traigoArrayNc = 0;
        if ($traigoArrayNc == 0 || $tipo == "un" || $tipo == "peso") {
            if ($grafico == 1) {
                $arrayFinal = array(
                    "titulos" => $titulo,
                    "cabeceras" => $cabeceraTT,
                    "data" => $renglon,
                    "goption" => $optionChart,
                    "gdata" => $arrayChart,
                    "goptionT" => $optionChartT,
                    "gdataT" => $arrayChartT
                );
            } else {
                $arrayFinal = array(
                    "titulos" => $titulo,
                    "cabeceras" => $cabeceraTT,
                    "data" => $renglon
                );
            }
        } else {
            if ($grafico == 1) {
                $arrayFinal = array(
                    "titulos" => $titulo,
                    "cabeceras" => $cabeceraTT,
                    "data" => $renglon,
                    "goption" => $optionChart,
                    "gdata" => $arrayChart,
                    "goptionT" => $optionChartT,
                    "gdataT" => $arrayChartT,
                    "impNC" => $arrayNc
                );
            } else {
                $arrayFinal = array(
                    "titulos" => $titulo,
                    "cabeceras" => $cabeceraTT,
                    "data" => $renglon
                );
            }
        }
        return json_encode($arrayFinal);
    }
}
/**
 * 
 */
function ventas_totales_rubro(
    $connV,
    $vendedor = null,
    $desde = null,
    $hasta = null,
    $rangoDoble = null,
    $desdeDos = null,
    $hastaDos = null,
    $operacionRango = null,
    $periodo = null,
    $salida = null,
    $tipo = null,
    $filtrarPor = null
) {
    //$queDevuelvo=array($connV,$vendedor, $desde,$hasta,$rangoDoble,$desdeDos,$hastaDos,$operacionRango,$periodo,$salida,$tipo,$filtrarPor);
    //    echo "<pre>";
    //    print_r($queDevuelvo);
    //    echo "</pre>";
    $arrResultado = array();
    $agrupar = "mes";
    $where = "";
    $whereNc = "";
    $traigoArrayNc = 0;
    $tituloFil = "";
    $listaFiltro = array();
    $whereVendedores = '';
    $whereVendedoresNc = '';
    $usoAcargo = 0;
    if ($filtrarPor) {
        $listaFiltro = explode("||", $filtrarPor);
    }

    $vendedorCargo = $_SESSION['vendedor_a_cargo'];


    // no tengo acargo traigo solo lo mio. y no podria filtrar.
    if (empty($vendedorCargo)) {
        $whereVendedores = " AND stock.CodViajante={$vendedor}";
        $whereVendedoresNc = " AND cc.CodViajante={$vendedor}";
    }

    // si no tengo filtros tengo y soy supervisor debo traer los vendedores a mi cargo
    if (!empty($vendedorCargo)) {
        $whereVendedores = " AND  stock.CodViajante IN (" . implode(", ", $vendedorCargo) . ")";
        $whereVendedoresNc = " AND cc.CodViajante IN (" . implode(", ", $vendedorCargo) . ")";
    }



    //$listaFiltro = explode("||",$filtrarPor);

    switch ($periodo) {
        case "dia":
            $agrupar = "dia,semana";
            break;
        case "semana":
            $agrupar = "semana,mes";
            break;
        case "mes":
            $agrupar = "mes,aa";
            break;
    }
    $rangoFecha = " (stock.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
    if ($rangoDoble == 1) {
        $rangoFecha .= " OR (stock.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
    }
    /* tipo de sumatoria si por plata, kilos unidades.*/
    switch ($tipo) {
        case 'un':
            $comoSumo = " SUM( IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV' OR 
                stock.TipoComp = 'Anul NC Devol' OR 
                stock.TipoComp ='ND Anul NC'
            ,stock.Cantidad,stock.Cantidad * -1)) AS total";
            break;
        case 'peso':
            // tengo que multiplicar por 1000 si esta en gramos. CDOGIO p todos los clientes
            // reemplazar cuando sea kilos y prestar atencion cuando se haga por unidades
            //            $comoSumo ="  SUM( IF(
            //                stock.TipoComp ='Venta' OR
            //                stock.TipoComp ='Venta TPV' OR 
            //                stock.TipoComp = 'Anul NC Devol' OR 
            //                stock.TipoComp ='ND Anul NC'
            //            
            //            ,IF(arti.id_unimed=32,stock.cantidad_uni * stock.Cantidad / 1000 ,
            //                
            //                IF(arti.id_unimed=36,stock.cantidad_uni * stock.Cantidad / 10000,stock.cantidad_uni * stock.Cantidad)
            //                ),
            //                IF(arti.id_unimed=32 ,stock.cantidad_uni * stock.Cantidad / 1000 * -1,
            //                    
            //                    IF(arti.id_unimed=36,stock.cantidad_uni * stock.Cantidad / 10000 *-1 ,stock.cantidad_uni * stock.Cantidad * -1)
            //                )
            //            )) AS total";

            //            $comoSumo ="SUM( IF(
            //                stock.TipoComp ='Venta' OR
            //                stock.TipoComp ='Venta TPV' OR 
            //                stock.TipoComp = 'Anul NC Devol' OR 
            //                stock.TipoComp ='ND Anul NC'
            //            
            //            ,IF(arti.id_unimed=32, stock.Cantidad / 1000 * COALESCE(kg.valor1,1) ,
            //                
            //                IF(arti.id_unimed=36, stock.Cantidad / 10000 * COALESCE(kg.valor1,1),stock.Cantidad* COALESCE(kg.valor1,1) ) 
            //                ),
            //                IF(arti.id_unimed=32 , stock.Cantidad / 1000 * -1 * COALESCE(kg.valor1,1),
            //                    
            //                    IF(arti.id_unimed=36,stock.Cantidad / 10000 *-1* COALESCE(kg.valor1,1) ,stock.Cantidad * -1* COALESCE(kg.valor1,1) )
            //                )
            //            )) AS total";

            $comoSumo = " arti.cantidad_promedio_bulto AS kg,
                SUM( IF(
                                stock.TipoComp ='Venta' OR
                                stock.TipoComp ='Venta TPV' OR                                 
                                stock.TipoComp ='ND Anul NC'            
                                ,stock.Cantidad * arti.cantidad_promedio_bulto 
                                ,stock.Cantidad * -1 * arti.cantidad_promedio_bulto
		
                                )
                            ) AS total";
            // si esta opcion tomamos vamos a tener que filtrar articulos que no se pueden
            //$where .=" AND (arti.id_unimed=1 OR arti.id_unimed=32 OR arti.id_unimed=36) ";
            // medir por peso.
            break;
        case 'monto':
            //            $comoSumo =" SUM( IF(
            //                stock.TipoComp ='Venta' OR
            //                stock.TipoComp ='Venta TPV' OR 
            //                stock.TipoComp = 'Anul NC Devol' OR 
            //                stock.TipoComp ='ND Anul NC'
            //            ,stock.PrecioNetoxR,stock.PrecioNetoxR * -1)) AS total";

            $comoSumo = "SUM(
              
              IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV' OR                
                stock.TipoComp ='ND Anul NC'
            	,stock.PrecioNetoxR,
            	stock.PrecioNetoxR * -1) 
            
           ) AS total ";
            break;
    }
    /* filtros */
    $arrFiltros = array();
    if (!empty($listaFiltro)) {
        //     print_r($filtrarPor);
        foreach ($listaFiltro as $valorFiltro) {
            $datoFiltro = explode("|", $valorFiltro);
            if (isset($datoFiltro[1])) {
                $arrFiltros[$datoFiltro[0]][] = $datoFiltro[1];
            }
        }

        foreach ($arrFiltros as $clave => $fi) {
            //        var_dump(empty($fi));
            //        print_r($fi);
            switch ($clave) {
                case 'cliente':
                    // no puedo volver a agrupar.
                    //                    if($listarPor!='cliente'){
                    //                        $primerAgrupo .='cli.Codigo AS cod2,cli.nombre_cliente  As nom2,';
                    //
                    //                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                        $whereNc .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                    }

                    break;
                case 'tipocliente':
                    // no puedo volver a agrupar.
                    //                    if($listarPor!='tipocliente'){
                    //                        $primerAgrupo .='tpcli.IDTipoCliente AS cod2,tpcli.NombreTipoCliente  As nom2,';
                    //
                    //                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND tpcli.IDTipoCliente IN (' . implode(",", $fi) . ")";
                        $whereNc  .= ' AND tpcli.IDTipoCliente IN (' . implode(",", $fi) . ")";
                    }

                    break;
                case 'vendedor':
                    // no puedo volver a agrupar.
                    if (!in_array("todos", $fi)) {
                        $primerAgrupo = 'vend.CodViajante AS cod2,vend.Nombre  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where  = ' AND vend.CodViajante IN (' . implode(",", $fi) . ")";
                        $whereNc  = ' AND vend.CodViajante IN (' . implode(",", $fi) . ")";
                        $usoAcargo++;
                    }
                    break;
                case 'articulo':
                    // no puedo volver a agrupar.
                    //                    if($listarPor!='articulo'){
                    //                        $primerAgrupo .='arti.IDArt AS cod2,arti.NombreArticulo  As nom2,';
                    //
                    //                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND arti.IDArt IN (' . implode(",", $fi) . ")";
                        $whereNc  .= ' AND arti.IDArt IN (' . implode(",", $fi) . ")";
                    }

                    break;
                case 'proveedor':
                    // no puedo volver a agrupar.
                    //                    if($listarPor!='proveedor'){
                    //                        $primerAgrupo .='prov.Codigo AS cod2,prov.Nombre As nom2,';
                    //
                    //                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND prov.Codigo IN (' . implode(",", $fi) . ")";
                        $whereNc  .= ' AND prov.Codigo IN (' . implode(",", $fi) . ")";
                    }

                    break;
                case 'zona':
                    // no puedo volver a agrupar.
                    //                    if($listarPor!='zona'){
                    //                        $primerAgrupo .=' zonas.id_zona AS cod2,zonas.nombre_zona As nom2,';
                    //
                    //                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND  zonas.id_zona IN (' . implode(",", $fi) . ")";
                        $whereNc  .= ' AND  zonas.id_zona IN (' . implode(",", $fi) . ")";
                    }

                    break;

                case 'rubro':
                    // no puedo volver a agrupar.
                    //                    if($listarPor!='rubro'){
                    //                        $primerAgrupo .='ru.CodigoRubro AS cod2, ru.NombreRubro AS nom2,';
                    //
                    //                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND ru.CodigoRubro IN (' . implode(",", $fi) . ")";
                        $whereNc  .= ' AND ru.CodigoRubro IN (' . implode(",", $fi) . ")";
                    }

                    break;
                case 'subrubro':
                    // no puedo volver a agrupar.
                    //                    if($listarPor!='subrubro'){
                    //                        $primerAgrupo .='srub.IdSubRubro AS cod,srub.NombreSubRubro As nom,';
                    //
                    //                    }
                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND srub.IdSubRubro IN (' . implode(",", $fi) . ")";
                        $whereNc  .= ' AND srub.IdSubRubro IN (' . implode(",", $fi) . ")";
                    }

                    break;
                case 'marca':
                    // no puedo volver a agrupar.
                    //                    if($listarPor!='subrubro'){
                    //                        $primerAgrupo .='srub.IdSubRubro AS cod,srub.NombreSubRubro As nom,';
                    //
                    //                    }
                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND marca.CodMarca IN (' . implode(",", $fi) . ")";
                    }

                    break;
            }
        }
    }
    //    $sql = "SELECT
    //            DAY(stock.Fecha)as dia,
    //            WEEKOFYEAR(stock.Fecha) as semana,
    //            MONTH(stock.Fecha) as mes,
    //            YEAR(stock.Fecha) AS aa,
    //            ru.CodigoRubro AS codR,
    //            ru.NombreRubro AS nomR,
    //            DATE_FORMAT(STR_TO_DATE(CONCAT(YEARWEEK(stock.Fecha),'Monday'),'%X%V %W'),'%d/%m') as PrimerDiaSemana,  
    //            DATE_FORMAT(STR_TO_DATE(CONCAT(YEARWEEK(stock.Fecha),'Saturday'),'%X%V %W'),'%d/%m') as UltimoDiaSemana,  
    //            SUM( IF(
    //                stock.TipoComp ='Venta' OR
    //                stock.TipoComp ='Venta TPV' OR 
    //                stock.TipoComp = 'Anul NC Devol' OR 
    //                stock.TipoComp ='ND Anul NC'
    //            ,stock.PrecioNetoxR,stock.PrecioNetoxR * -1)) AS total 
    //            FROM stock 
    //                LEFT JOIN articulo AS arti ON arti.IDArt = stock.IDArt
    //                LEFT JOIN rubro AS ru ON ru.CodigoRubro = arti.CodigoRubro
    //            WHERE
    //                stock.CodViajante = {$vendedor}
    //                AND ({$rangoFecha})
    //                AND stock.Anulado ='No'
    //                
    //               AND (stock.TipoComp = 'Venta' 
    //                    OR stock.TipoComp = 'Venta TPV' 
    //                    OR stock.TipoComp = 'Devol - Cliente' 
    //                    OR stock.TipoComp = 'ND Anul NC'
    //                    )
    //            GROUP BY {$agrupar} ,ru.CodigoRubro ORDER BY ru.CodigoRubro ASC ,stock.Fecha ASC";
    // vendedores a cargo
    if ($usoAcargo == 0) {
        $where .= $whereVendedores;
        $whereNc .= $whereVendedoresNc;
    }

    $sql = "SELECT
            DAY(stock.Fecha)as dia,
            WEEKOFYEAR(stock.Fecha) as semana,
            MONTH(stock.Fecha) as mes,
            YEAR(stock.Fecha) AS aa,
            ru.id_categoria AS codCat,
            ru.CodigoRubro AS codR,
            CONCAT(cat.nombre_categoria,' ',ru.NombreRubro) AS nomR,
            DATE_FORMAT(STR_TO_DATE(CONCAT(YEARWEEK(stock.Fecha),'Monday'),'%X%V %W'),'%d/%m') as PrimerDiaSemana,  
            DATE_FORMAT(STR_TO_DATE(CONCAT(YEARWEEK(stock.Fecha),'Saturday'),'%X%V %W'),'%d/%m') as UltimoDiaSemana,  
            {$comoSumo}  
            FROM stock 
                LEFT JOIN cuentacliente AS cc ON (cc.CodigoMovimiento= stock.CodigoMovimiento) 
                LEFT JOIN articulo AS arti ON arti.IDArt = stock.IDArt
                LEFT JOIN articulo_val_ce AS kg ON (kg.id_articulo = arti.IDArt AND kg.id_articulo_ce=1)
                LEFT JOIN rubro AS ru ON ru.CodigoRubro = arti.CodigoRubro
                LEFT JOIN rubro_categoria AS cat ON cat.id_categoria=ru.id_categoria
                LEFT JOIN marca ON marca.CodMarca=arti.CodigoMarca
                LEFT JOIN cliente AS cli ON cli.Codigo=stock.CodigoCP
                LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
                LEFT JOIN proveedor AS prov ON prov.Codigo = arti.CodigoProveedor
                LEFT JOIN punto_venta AS ppv ON ( ppv.id_punto_venta=cc.id_pv)
                LEFT JOIN tipo_cliente AS tpcli ON tpcli.IDTipoCliente = cli.TipoCliente
            WHERE
                 
                ({$rangoFecha})
                {$where}  
                AND cc.Anulado='No' 
                AND stock.Anulado='No'
                AND ru.anulado='No'
               
                AND (stock.TipoComp = 'Venta' 
                    OR stock.TipoComp = 'Venta TPV' 
                    OR stock.TipoComp = 'Devol - Cliente' 
                    OR stock.TipoComp = 'ND Anul NC'
                    )
                 
            GROUP BY {$agrupar} ,ru.CodigoRubro ORDER BY ru.CodigoRubro ASC ,stock.Fecha ASC";
    //    echo "sql</pre>";
    //    print_r($sql); 
    //    echo "</pre>";

    // tengo que trabajar con arrays para devolver el resultado final, tanto
    // para json como para tabla html. con totales.
    $hacer = mysqli_query($connV, $sql) or die("no puedo hacer la consulta de las ventas" . mysqli_error($connV) . "<pre>" . $sql . "</pre>");
    while ($r = mysqli_fetch_assoc($hacer)) {
        $arrResultado[] = $r;
    }

    //    SIN RESULTADO
    if (empty($arrResultado)) {
        return "vacio";
    }

    $rangoFechaCc = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
    if ($rangoDoble == 1) {
        $rangoFechaCc .= " OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
    }
    // si calculo x unidades no traigo las notas de credito
    if ($tipo == "monto") {
        // notas de credito 
        $sqlNC = " SELECT cc.`Fecha`, 
                    cc.`TipoComprobante`, 
                    cc.`NroComprobante`, 
                    cc.`SubtotalDesc`, 
                    cc.`motivo_nd`, 
                    cc.`TipoNC`, 
                    cc.`ImpDesc1`, 
                    cc.`ImpDesc2`,
                    SUM(IF(cc.TipoNC = 'Devolucion',
                                IF(cc.ImpDesc1<>0 OR cc.ImpDesc2<>0,
                                    (cc.ImpDesc1+cc.ImpDesc2) ,0
                                    ),
                                IF(cc.TipoComprobante ='NDA' 
                                    OR cc.TipoComprobante ='NDB' 
                                    OR cc.TipoComprobante ='NDE' 
                                    OR cc.TipoComprobante ='NDC' 
                                    OR cc.TipoComprobante ='NDM',
                                    cc.SubtotalDesc,
                                        IF(cc.TipoComprobante ='NCA' 
                                            OR cc.TipoComprobante ='NCB' 
                                            OR cc.TipoComprobante ='NCE' 
                                            OR cc.TipoComprobante ='NCC' 
                                            OR cc.TipoComprobante ='NCM',
                                            cc.SubtotalDesc * -1,
                                                IF(cc.TipoComprobante ='FA' 
                                                    OR cc.TipoComprobante ='FB' 
                                                    OR cc.TipoComprobante ='FE' 
                                                    OR cc.TipoComprobante ='FC' 
                                                    OR cc.TipoComprobante ='FM',
                                                    (cc.ImpDesc1+cc.ImpDesc2) * -1,0
                                                ) 
                                        )
                                  )
                        )) AS importe
             FROM   cuentacliente AS cc
             LEFT JOIN cliente AS cli ON cli.Codigo=cc.Codigo
             LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
             LEFT JOIN tipo_cliente  AS tpcli ON tpcli.IDTipoCliente=cli.TipoCliente
             WHERE
          
            ({$rangoFechaCc})
            {$whereNc} 
            AND cc.Anulado ='No'
            AND cc.TipoComprobante IN ('NCA','NCB',
                                        'NCE','NCC','NCM','FA','FB','FE','FC','FM')                            
                                       
            GROUP BY cc.CodViajante
             ORDER BY cc.Fecha";
        $hacerNc = mysqli_query($connV, $sqlNC) or die("no puedo recuperar las notas de C rubro" . mysqli_error($connV) . $sqlNC);
        $arrayNc = mysqli_fetch_assoc($hacerNc);
    }
    if ($tipo == "un" || $tipo == "peso") {
        $traigoArrayNc++;
    }

    /* no se bien porque no tomo las notas de debito **/
    //        AND cc.TipoComprobante IN ('NDA','NDB','NDE','NDC','NDM','NCA','NCB',
    //                                    'NCE','NCC','NCM','FA','FB','FE','FC','FM')
    /*
     * SALIDA TABLA HTML
     */
    if ($salida == "html") {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $localidad = setlocale(LC_TIME, NULL); #Guarda localización regional actual
        setlocale(LC_TIME, 'es_AR'); # Localiza en español es_Cenezuela

        $cabecera = array();
        $cabeceraT = array();
        $totalFila = 0;
        $totalGral = 0;
        $arrFiltros = array();

        //     print_r($filtrarPor);
        foreach ($listaFiltro as $ff) {
            $datoFiltro = explode("|", $ff);

            if (isset($datoFiltro[2])) {
                $arrFiltros[$datoFiltro[0]][] = $datoFiltro[2];
            }
        }

        foreach ($arrFiltros as $key => $fil) {


            switch ($key) {
                case 'cliente':
                    // no puedo volver a agrupar.
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    $tituloFil .= " - Cliente: " . implode(",", $fil);

                    //                    $titulo[] = array("titulo" => "{$periodo} / {$tituloFil}","span" => 2,"rowspan"=>1);
                    break;

                case 'tipocliente':
                    // no puedo volver a agrupar.
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    $tituloFil .= " - Tipo Cliente: " . implode(",", $fil);

                    //                    $titulo[] = array("titulo" => "{$periodo} / {$tituloFil}","span" => 2,"rowspan"=>1);
                    break;
                case 'vendedor':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Vendedor: " . implode(",", $fil);

                    //                    $titulo[] = array("titulo" => "{$periodo} / {$tituloFil}","span" => 2,"rowspan"=>1);
                    break;
                case 'articulo':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Art: " . implode(",", $fil);
                    break;
                case 'proveedor':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Prov: " . implode(",", $fil);
                    break;
                case 'zona':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Zona: " . implode(",", $fil);
                    break;
                case 'rubro':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Rubro: " . implode(",", $fil);
                    break;
                case 'subrubro':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - SubR: " . implode(",", $fil);


                    break;
            }
        }
        // colocar en el titulo si estoy haciendo listado por tipo, monto, peso, unidades
        $tituloTipo = "";
        switch ($tipo) {
            case "un":
                $tituloTipo = "UNIDADES x categoria rubro";
                break;
            case "peso":
                $tituloTipo = "KILOGRAMOS x categoria rubro";
                break;
            case "monto":
                $tituloTipo = "VENTAS NETAS x categoria rubro";
                break;
        }

        if (empty($filtrarPor)) {
            $titulo[] = array("titulo" => "{$tituloTipo} x {$periodo} ", "span" => 1, "rowspan" => 2);
        } else {
            $titulo[] = array("titulo" => "{$tituloTipo} x {$periodo}  /  {$tituloFil}", "span" => 1, "rowspan" => 2);
        }

        $mes = 0;
        $aa = 0;
        //        $titulo[] = array("titulo" => "{$periodo} / <br>Ventas netas rubro","span" => 1,"rowspan"=>2);
        $rub = "";
        /**
         * CABECERAS TH de las tablas.
         */
        foreach ($arrResultado as $campo) {

            switch ($periodo) {
                case "dia":
                    $cabecera[] = array("a" => $campo["aa"], "m" => $campo["mes"], "i" => $campo["dia"] . $campo["mes"], "th" => $campo["dia"]);
                    $cabeceraT[$campo["dia"] . $campo["mes"]] = $campo["dia"];
                    break;
                case "semana":
                    $cabecera[] = array("a" => $campo["aa"], "m" => $campo["mes"], "i" => $campo["semana"] . $campo["mes"], "th" => $campo["semana"]);
                    $cabeceraT[$campo["semana"] . $campo["mes"]] = $campo["semana"] . " - " . $campo["PrimerDiaSemana"] . " al " . $campo["UltimoDiaSemana"];
                    break;
                case "mes":
                    $cabecera[] = array("a" => $campo["aa"], "m" => $campo["mes"], "i" => $campo["mes"] . $campo["aa"], "th" => utf8_encode(strftime("%B", mktime(0, 0, 0, $campo["mes"], $campo["dia"], $campo["aa"]))));
                    $cabeceraT[$campo["mes"] . $campo["aa"]] = utf8_encode(strftime("%B", mktime(0, 0, 0, $campo["mes"], $campo["dia"], $campo["aa"])));
                    break;
            }

            $totalGral = $totalGral  + $campo["total"];
        }
        $cabeceraTT = $cabeceraT;
        // este indice ith, es para guardar las claves de combinacion de dia -mes, semana- mes, aa-mes
        $ith = array();
        switch ($periodo) {
            case "dia":
                $ith = array("dia", "mes");
                $cadTitulo = "%B del %Y";
                //$textoT = utf8_encode(strftime("%B del %Y", mktime(0, 0, 0, $aa, $campo["dia"], $campo["aa"])));                        
                break;
            case "semana":
                $ith = array("semana", "mes");
                $cadTitulo = "%B del %Y";
                ///$textoT = utf8_encode(strftime("%B del %Y", mktime(0, 0, 0, $aa, $campo["dia"], $campo["aa"])));                        
                break;
            case "mes":
                $ith = array("mes", "aa");
                $cadTitulo = "%Y";
                //$textoT = utf8_encode(strftime("%Y", mktime(0, 0, 0, $campo["mes"], $campo["dia"], $aa)));                        
                break;
        }

        /*
         * TITULOS
         */
        // recorro las cabeceras y tengo que obtener el mes o dia u año segun lo que tenga.
        // en el periodo para armar los titulos .
        $tit = 0;
        $aaa = 0;
        $mm = 0;
        $colspan = 0;
        $col = 0;
        $ii = array();
        foreach ($cabecera as $ca) {

            if ($tit == 0) {
                $mm = $ca["m"];
                $aaa = $ca["a"];
                $ii[] = $ca["i"];
                $tit++;
            } else {
                if ($mm != $ca["m"] || $aaa != $ca["a"]) {
                    $colspan = count($ii);
                    $textoT = utf8_encode(strftime($cadTitulo, mktime(0, 0, 0, $mm, 1, $aaa)));
                    $titulo[$mm . $aaa] = array("titulo" => $textoT, "span" => $colspan, "rowspan" => 1);
                    $mm = $ca["m"];
                    $aaa = $ca["a"];
                    $ii = array($ca["i"]);
                }
                if (!in_array($ca["i"], $ii)) {
                    $ii[] = $ca["i"];
                }
            }
        }
        $colspan = count($ii);

        $textoT = utf8_encode(strftime($cadTitulo, mktime(0, 0, 0, $mm, 1, $aaa)));
        $titulo[$mm . $aaa] = array("titulo" => $textoT, "span" => $colspan, "rowspan" => 1);

        if ($operacionRango == "suma" or $operacionRango == "sumag") {
            $titulo[] = array("titulo" => "SubTotal", "span" => 1, "rowspan" => 2);
        } else {
            $titulo[] = array("titulo" => "diferencia", "span" => 1, "rowspan" => 2);
        }
        //$titulo[] = array("titulo" => "SubTotal","span" => 1,"rowspan"=>2);
        /***Cambio de cabeceras*/

        /*
         * DATOS
         */
        $rng = 0;
        $rubro = "";
        $totaFila = 0;
        $arrayChart = array(
            "cols" => array(
                array(
                    "id" => 0,
                    "label" => "Periodo {$periodo}",
                    "type" => "string"
                ),

            ),
            "rows" => array()
        );
        $arrayChartT = array(
            "cols" => array(
                array(
                    "id" => 0,
                    "label" => "Rubro",
                    "type" => "string"
                ),
                array(
                    "id" => 1,
                    "label" => "Ventas",
                    "type" => "number"
                ),
                array(
                    "id" => null,
                    "label" => "",
                    "pattern" => "",
                    "type" => "string",
                    "p" => array("role" => "style"),
                ),

            ),
            "rows" => array()
        );


        foreach ($arrResultado as $campo) {

            $icampo = intval($campo[$ith[0]] . $campo[$ith[1]]);

            if ($rubro === "") {
                $rubro = $campo["codR"];
                $renglon[$rubro][] = $campo["nomR"];
                $renglon[$rubro]["cat"] = $campo["codCat"];
                $arrayChart["cols"][] = array(
                    "id" => $rubro,
                    "label" => $campo["nomR"],
                    "type" => "number"
                );
                //recorro las cabeceras y coloco lo que hace falta
                foreach ($cabeceraT as $key => $ca) {

                    if ($icampo === $key) {
                        //                        $renglon[$rubro][$icampo]= number_format($campo["total"],2,".","");
                        $renglon[$rubro][$icampo] = $campo["total"];
                    } else {
                        //                        $renglon[$rubro][$key]= number_format(0,2,".","");
                        $renglon[$rubro][$key] = 0;
                    }
                }

                $totalFila += $campo["total"];
            } else {
                if ($rubro != $campo["codR"]) {
                    //                    $renglon[$rubro]["subt"]=number_format($totalFila,2,".",""); 
                    $renglon[$rubro]["subt"] = $totalFila;
                    $rubro = $campo["codR"];
                    $renglon[$rubro][] = $campo["nomR"];
                    $renglon[$rubro]["cat"] = $campo["codCat"];
                    $arrayChart["cols"][] = array(
                        "id" => $rubro,
                        "label" => $campo["nomR"],
                        "type" => "number"
                    );

                    // cabeceras
                    foreach ($cabeceraT as $key => $ca) {

                        if ($icampo === $key) {
                            //                            $renglon[$rubro][$icampo]= number_format($campo["total"],2,".","");
                            $renglon[$rubro][$icampo] = $campo["total"];
                        } else {
                            //                            $renglon[$rubro][$key]= number_format(0,2,".","");
                            $renglon[$rubro][$key] = 0;
                        }
                    }

                    $totalFila = $campo["total"];
                } else {
                    // cabeceras
                    foreach ($cabeceraT as $key => $ca) {

                        if ($icampo === $key) {
                            //                            $renglon[$rubro][$icampo]= number_format($campo["total"],2,".","");
                            $renglon[$rubro][$icampo] = $campo["total"];
                        }
                    }
                    $totalFila += $campo["total"];
                }
            }
        }
        //        $renglon[$rubro]["subt"]= number_format($totalFila,2,".","");
        $renglon[$rubro]["subt"] = $totalFila;

        /*
         * Grafico
         * **/
        $arrColor = array(
            1 => array(
                "rgba(0, 172, 212, 0.5)",
                "rgba(0, 172, 212, 0.8)",
                "rgba(0, 172, 212, 0.75)",
                "rgba(0, 172, 212, 1)",
                "#00A4CC"
            ),

            2 => array(
                "rgba(248, 147, 31, 0.5)",
                "rgba(248, 147, 31, 0.8)",
                "rgba(248, 147, 31, 0.75)",
                "rgba(248, 147, 31, 1)",
                "#109618"
            ),
            3 => array(
                "rgba(34, 181, 75, 0.5)",
                "rgba(34, 181, 75, 0.8)",
                "rgba(34, 181, 75, 0.75)",
                "rgba(34, 181, 75, 1)",
                //"lightskyblue"
                "#FF9900"
            )
        );
        $optionChart = array(
            "title" => "Ventas Netas por Categoria Rubro",
            "width" => 700,
            "height" => 400,
            "hAxis" => array("title" => "Período " . $periodo)
        );
        $optionChartT = array(
            "title" => "Ventas Netas por Rubro",
            "is3D" => true,
            "width" => 700,
            "height" => 400
        );

        //$linea=array();
        foreach ($cabeceraTT as $key => $c) {
            $linea = array();
            // recorro todas las cabeceras y me tiene que hacer una linea nueva
            $linea[] = array("v" => $c);
            foreach ($renglon as $clave => $reng) {
                // recorro los renglones y prgunto si hay cabecera
                //                echo "reng:<pre>";
                //                print_r($reng);
                //                echo "</pre>clave:<pre>";
                //                print_r($clave);
                //                echo "</pre>";

                foreach ($reng as $keyR => $r) {
                    //print_r($keyR);
                    if ($key == $keyR) {
                        $valor = $r;
                        $valorF = "$" . number_format($r, 2, ",", ".");
                        $linea[] = array("v" => $valor, "f" => $valorF);
                    }
                }
                // coloco el color de la series
                $optionChart["series"][$clave - 1] = array("color" => $arrColor[$reng["cat"]][4]);
                $optionChartT["slices"][$clave - 1] = array("color" => $arrColor[$reng["cat"]][4]);
            }
            //$style= "opacity: 0.2";
            $arrayChart["rows"][] = array("c" => $linea);
        }


        //series:{0:{color:'green'},1:{color:'yellow'}}
        /* rango doble*/
        //        var_dump($operacionRango);
        if ($operacionRango == "resta") {

            foreach ($renglon as $k => $r) {

                $suma = 0;
                $vuelta = 0;
                foreach ($r as $kk => $valor) {

                    if ($kk != 0 && $kk != "subt") {
                        if ($vuelta == 0) {
                            $suma = $suma - $valor;
                        } else {
                            $suma = $suma + $valor;
                        }
                        $vuelta++;
                    }
                }
                $renglon[$k]["subt"] = $suma;
            }
        }

        /* grafico de rubro */
        foreach ($renglon as $key => $reng) {
            $nombre = $reng[0];
            $valorT = $reng["subt"] + 0;
            //            unset($reng[0]);
            //            unset($reng[$cuantos]);

            $cabeza = $nombre;
            $valor = $valorT;
            $valorF = "$" . number_format($valorT, 2, ",", ".");
            $style = "color: " . $arrColor[$reng["cat"]][4];
            $arrayChartT["rows"][] = array(
                "c" => array(
                    array("v" => $cabeza),
                    array("v" => $valor, "f" => $valorF),
                    array("v" => $style)
                )
            );
        }

        //print_r($arrayChartT);
        /**Envio Final*/
        if ($traigoArrayNc == 0) {
            $arrayFinal = array(
                "titulos" => $titulo,
                "cabeceras" => $cabeceraTT,
                "data" => $renglon,
                "goption" => $optionChart,
                "gdata" => $arrayChart,
                "goptionT" => $optionChartT,
                "gdataT" => $arrayChartT,
                "impNC" => $arrayNc["importe"]
            );
        } else {
            $arrayFinal = array(
                "titulos" => $titulo,
                "cabeceras" => $cabeceraTT,
                "data" => $renglon,
                "goption" => $optionChart,
                "gdata" => $arrayChart,
                "goptionT" => $optionChartT,
                "gdataT" => $arrayChartT

            );
        }
        return json_encode($arrayFinal);
    }
}
function ventas_totales_rubro_proveedor(
    $usaIdManual,
    $connV,
    $vendedor = null,
    $desde = null,
    $hasta = null,
    $rangoDoble = null,
    $desdeDos = null,
    $hastaDos = null,
    $operacionRango = null,
    $periodo = null,
    $salida = null,
    $tipo = null,
    $filtrarPor = null
) {
    $arrResultado = array();
    $agrupar = "mes";
    $comoSumo = "";
    $where = "";
    $whereNc = "";
    $traigoArrayNc = 0;
    $tituloFil = "";
    $listarPor = "";
    $primerAgrupo = "";
    $usoAcargo = 0;
    $whereVendedoresNc = '';
    $whereVendedores = '';
    // FILTRO
    $listaFiltro = array();
    if ($filtrarPor) {
        $listaFiltro = explode("||", $filtrarPor);
    }

    $vendedorCargo = $_SESSION['vendedor_a_cargo'];


    // no tengo acargo traigo solo lo mio. y no podria filtrar.
    if (empty($vendedorCargo)) {
        $whereVendedores = " AND stock.CodViajante={$vendedor}";
        $whereVendedoresNc = " AND cc.CodViajante={$vendedor}";
    }
    // si no tengo filtros tengo y soy supervisor debo traer los vendedores a mi cargo
    if (!empty($vendedorCargo)) {

        $whereVendedoresNc = " AND  cc.CodViajante IN (" . implode(", ", $vendedorCargo) . ")";
        $whereVendedores = " AND stock.CodViajante IN (" . implode(", ", $vendedorCargo) . ")";
    }

    switch ($periodo) {
        case "dia":
            $agrupar = "dia,semana";
            break;
        case "semana":
            $agrupar = "semana,mes";
            break;
        case "mes":
            $agrupar = "mes,aa";
            break;
    }

    $rangoFecha = " (stock.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
    if ($rangoDoble == 1) {
        $rangoFecha .= " OR (stock.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
    }

    /* tipo de sumatoria si por plata, kilos unidades.*/
    switch ($tipo) {
        case 'un':
            $comoSumo = " SUM( IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV' OR 
                stock.TipoComp = 'Anul NC Devol' OR 
                stock.TipoComp ='ND Anul NC'
            ,stock.Cantidad,stock.Cantidad * -1)) AS total";
            break;
        case 'peso':
            // tengo que multiplicar por 1000 si esta en gramos. CDOGIO p todos los clientes
            // reemplazar cuando sea kilos y prestar atencion cuando se haga por unidades
            //            $comoSumo ="  SUM( IF(
            //                stock.TipoComp ='Venta' OR
            //                stock.TipoComp ='Venta TPV' OR 
            //                stock.TipoComp = 'Anul NC Devol' OR 
            //                stock.TipoComp ='ND Anul NC'
            //            
            //            ,IF(arti.id_unimed=32,stock.cantidad_uni * stock.Cantidad / 1000 ,
            //                
            //                IF(arti.id_unimed=36,stock.cantidad_uni * stock.Cantidad / 10000,stock.cantidad_uni * stock.Cantidad)
            //                ),
            //                IF(arti.id_unimed=32 ,stock.cantidad_uni * stock.Cantidad / 1000 * -1,
            //                    
            //                    IF(arti.id_unimed=36,stock.cantidad_uni * stock.Cantidad / 10000 *-1 ,stock.cantidad_uni * stock.Cantidad * -1)
            //                )
            //            )) AS total";

            //            $comoSumo ="SUM( IF(
            //                stock.TipoComp ='Venta' OR
            //                stock.TipoComp ='Venta TPV' OR 
            //                stock.TipoComp = 'Anul NC Devol' OR 
            //                stock.TipoComp ='ND Anul NC'
            //            
            //            ,IF(arti.id_unimed=32, stock.Cantidad / 1000 * COALESCE(kg.valor1,1) ,
            //                
            //                IF(arti.id_unimed=36, stock.Cantidad / 10000 * COALESCE(kg.valor1,1),stock.Cantidad* COALESCE(kg.valor1,1) ) 
            //                ),
            //                IF(arti.id_unimed=32 , stock.Cantidad / 1000 * -1 * COALESCE(kg.valor1,1),
            //                    
            //                    IF(arti.id_unimed=36,stock.Cantidad / 10000 *-1* COALESCE(kg.valor1,1) ,stock.Cantidad * -1* COALESCE(kg.valor1,1) )
            //                )
            //            )) AS total";

            $comoSumo = " arti.cantidad_promedio_bulto AS kg,
                SUM( IF(
                                stock.TipoComp ='Venta' OR
                                stock.TipoComp ='Venta TPV' OR                                 
                                stock.TipoComp ='ND Anul NC'            
                                ,stock.Cantidad * arti.cantidad_promedio_bulto 
                                ,stock.Cantidad * -1 * arti.cantidad_promedio_bulto
		
                                )
                            ) AS total";
            // si esta opcion tomamos vamos a tener que filtrar articulos que no se pueden
            //$where .=" AND (arti.id_unimed=1 OR arti.id_unimed=32 OR arti.id_unimed=36) ";
            // medir por peso.
            break;
        case 'monto':
            //            $comoSumo =" SUM( IF(
            //                stock.TipoComp ='Venta' OR
            //                stock.TipoComp ='Venta TPV' OR 
            //                stock.TipoComp = 'Anul NC Devol' OR 
            //                stock.TipoComp ='ND Anul NC'
            //            ,stock.PrecioNetoxR,stock.PrecioNetoxR * -1)) AS total";

            $comoSumo = "SUM(
              
              IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV' OR                
                stock.TipoComp ='ND Anul NC'
            	,stock.PrecioNetoxR,
            	stock.PrecioNetoxR * -1) 
            
           ) AS total ";
            break;
    }

    if ($usaIdManual == "Si") {
        $cabeza = "prov.id_manual_prov AS codP,
                CONCAT('(cod: ',prov.id_manual_prov,') ', prov.Nombre) As nomP,";
        $where .= " AND NOT ISNULL(prov.id_manual_prov) ";
    } else {
        $cabeza = "prov.Codigo AS codP,
                CONCAT('(cod: ',prov.Codigo,') ', prov.Nombre) As nomP,";
    }

    /* filtros */
    $arrFiltros = array();
    //     print_r($filtrarPor);
    if (!empty($listaFiltro)) {
        foreach ($listaFiltro as $ff) {
            $datoFiltro = explode("|", $ff);
            if (isset($datoFiltro[1])) {
                $arrFiltros[$datoFiltro[0]][] = $datoFiltro[1];
            }
        }

        foreach ($arrFiltros as $clave => $fi) {
            //        var_dump(empty($fi));
            //        print_r($fi);
            switch ($clave) {
                case 'cliente':
                    // no puedo volver a agrupar.
                    if ($listarPor != 'cliente') {
                        $primerAgrupo .= 'cli.Codigo AS cod2,cli.nombre_cliente  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                        $whereNc .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                    }

                    break;
                case 'tipocliente':
                    // no puedo volver a agrupar.
                    if ($listarPor != 'tipocliente') {
                        $primerAgrupo .= 'tpcli.IDTipoCliente AS cod2,tpcli.NombreTipoCliente  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where .= ' AND tpcli.IDTipoCliente IN (' . implode(",", $fi) . ")";
                        $whereNc .= ' AND tpcli.IDTipoCliente IN (' . implode(",", $fi) . ")";
                    }

                    break;
                case 'vendedor':
                    // no puedo volver a agrupar.
                    if (!in_array("todos", $fi)) {
                        $primerAgrupo .= 'vend.CodViajante AS cod2,vend.Nombre  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where = ' AND vend.CodViajante IN (' . implode(",", $fi) . ")";
                        $whereNc .= ' AND vend.CodViajante IN (' . implode(",", $fi) . ")";
                        $usoAcargo++;
                    }

                    break;
                case 'articulo':
                    // no puedo volver a agrupar.
                    if ($listarPor != 'articulo') {
                        $primerAgrupo .= 'arti.IDArt AS cod2,arti.NombreArticulo  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where .= ' AND arti.IDArt IN (' . implode(",", $fi) . ")";
                        $whereNc .= ' AND arti.IDArt IN (' . implode(",", $fi) . ")";
                    }

                    break;
                case 'proveedor':
                    // no puedo volver a agrupar.
                    if ($listarPor != 'proveedor') {
                        $primerAgrupo .= 'prov.Codigo AS cod2,prov.Nombre As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where .= ' AND prov.Codigo IN (' . implode(",", $fi) . ")";
                        $whereNc .= ' AND prov.Codigo IN (' . implode(",", $fi) . ")";
                    }

                    break;
                case 'zona':
                    // no puedo volver a agrupar.
                    if ($listarPor != 'zona') {
                        $primerAgrupo .= ' zonas.id_zona AS cod2,zonas.nombre_zona As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where .= ' AND  zonas.id_zona IN (' . implode(",", $fi) . ")";
                        $whereNc .= ' AND  zonas.id_zona IN (' . implode(",", $fi) . ")";
                    }

                    break;
                case 'rubro':
                    // no puedo volver a agrupar.
                    if ($listarPor != 'rubro') {
                        $primerAgrupo .= 'ru.CodigoRubro AS cod2, ru.NombreRubro AS nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where .= ' AND ru.CodigoRubro IN (' . implode(",", $fi) . ")";
                        $whereNc .= ' AND ru.CodigoRubro IN (' . implode(",", $fi) . ")";
                    }

                    break;
                case 'subrubro':
                    // no puedo volver a agrupar.
                    if ($listarPor != 'subrubro') {
                        $primerAgrupo .= 'srub.IdSubRubro AS cod,srub.NombreSubRubro As nom,';
                    }
                    if (!in_array("todos", $fi)) {
                        $where .= ' AND ru.CodigoRubro IN (' . implode(",", $fi) . ")";
                        $whereNc .= ' AND ru.CodigoRubro IN (' . implode(",", $fi) . ")";
                    }

                    break;
                case 'marca':
                    // no puedo volver a agrupar.
                    //                    if ($listarPor != 'subrubro') {
                    //                        $primerAgrupo .='srub.IdSubRubro AS cod,srub.NombreSubRubro As nom,';
                    //                    }
                    if (!in_array("todos", $fi)) {
                        $where .= ' AND marca.CodMarca IN (' . implode(",", $fi) . ")";
                    }

                    break;
            }
        }
    }

    // vendedores a cargo
    if ($usoAcargo == 0) {
        $where .= $whereVendedores;
        $whereNc .= $whereVendedoresNc;
    }

    $sql = "SELECT
            {$cabeza}
            DAY(stock.Fecha)as dia,
            WEEKOFYEAR(stock.Fecha) as semana,
            MONTH(stock.Fecha) as mes,
            YEAR(stock.Fecha) AS aa,
            ru.CodigoRubro AS codR,
            #ru.NombreRubro AS nomR,
            CONCAT(cat.nombre_categoria,' ',ru.NombreRubro) AS nomR,
            DATE_FORMAT(
            STR_TO_DATE(CONCAT(YEARWEEK(stock.Fecha),
            'Monday'),'%X%V %W'),'%d/%m') AS PrimerDiaSemana,  
            DATE_FORMAT(
            STR_TO_DATE(CONCAT(YEARWEEK(stock.Fecha),
            'Saturday'),'%X%V %W'),'%d/%m') AS UltimoDiaSemana,   
            {$comoSumo} 
            FROM stock
                LEFT JOIN cuentacliente AS cc ON (cc.CodigoMovimiento= stock.CodigoMovimiento) 
                LEFT JOIN articulo AS arti ON arti.IDArt = stock.IDArt
                LEFT JOIN marca ON marca.CodMarca=arti.CodigoMarca
                LEFT JOIN proveedor AS prov ON prov.Codigo = arti.CodigoProveedor
                LEFT JOIN rubro AS ru ON ru.CodigoRubro = arti.CodigoRubro
                LEFT JOIN rubro_categoria AS cat ON cat.id_categoria=ru.id_categoria
                LEFT JOIN articulo_val_ce AS kg ON (kg.id_articulo = arti.IDArt AND kg.id_articulo_ce=1)
                
               
                LEFT JOIN cliente AS cli ON cli.Codigo=stock.CodigoCP
                LEFT JOIN erp_zona AS zonas ON zonas.id_zona=cli.id_zona
                LEFT JOIN punto_venta AS ppv ON ( ppv.id_punto_venta=cc.id_pv)
                LEFT JOIN tipo_cliente AS tpcli ON tpcli.IDTipoCliente=cli.TipoCliente
            WHERE
               
               ({$rangoFecha})
               {$where}  
                AND stock.Anulado ='No'
               AND (stock.TipoComp = 'Venta' 
                    OR stock.TipoComp = 'Venta TPV' 
                    OR stock.TipoComp = 'Devol - Cliente' 
                    OR stock.TipoComp = 'ND Anul NC'
                    )

            GROUP BY {$agrupar} ,arti.CodigoRubro,arti.CodigoProveedor ORDER BY ru.CodigoRubro ASC,prov.Nombre ASC, stock.Fecha ASC";
    // tengo que trabajar con arrays para devolver el resultado final, tanto
    // para json como para tabla html. con totales.
    $hacer = mysqli_query($connV, $sql) or die("no puedo hacer la consulta de las ventas" . mysqli_error($connV) . "<pre>" . $sql . "</pre>");
    while ($r = mysqli_fetch_assoc($hacer)) {
        $arrResultado[] = $r;
    }
    // SIN RESULTADO

    if (empty($arrResultado)) {
        return "vacio";
    }

    $rangoFechaCc = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
    if ($rangoDoble == 1) {
        $rangoFechaCc .= " OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
    }
    if ($tipo == 'monto') {
        // notas de credito 
        $sqlNC = " SELECT cc.`Fecha`, 
                    cc.`TipoComprobante`, 
                    cc.`NroComprobante`, 
                    cc.`SubtotalDesc`, 
                    cc.`motivo_nd`, 
                    cc.`TipoNC`, 
                    cc.`ImpDesc1`, 
                    cc.`ImpDesc2`,
                    SUM(IF(cc.TipoNC = 'Devolucion',
                                IF(cc.ImpDesc1<>0 OR cc.ImpDesc2<>0,
                                    (cc.ImpDesc1+cc.ImpDesc2) ,0
                                    ),
                                IF(cc.TipoComprobante ='NDA' 
                                    OR cc.TipoComprobante ='NDB' 
                                    OR cc.TipoComprobante ='NDE' 
                                    OR cc.TipoComprobante ='NDC' 
                                    OR cc.TipoComprobante ='NDM',
                                    cc.SubtotalDesc,
                                        IF(cc.TipoComprobante ='NCA' 
                                            OR cc.TipoComprobante ='NCB' 
                                            OR cc.TipoComprobante ='NCE' 
                                            OR cc.TipoComprobante ='NCC' 
                                            OR cc.TipoComprobante ='NCM',
                                            cc.SubtotalDesc * -1,
                                                IF(cc.TipoComprobante ='FA' 
                                                    OR cc.TipoComprobante ='FB' 
                                                    OR cc.TipoComprobante ='FE' 
                                                    OR cc.TipoComprobante ='FC' 
                                                    OR cc.TipoComprobante ='FM',
                                                    (cc.ImpDesc1+cc.ImpDesc2) * -1,0
                                                ) 
                                        )
                                  )
                        )) AS importe
             FROM   cuentacliente AS cc
             LEFT JOIN cliente AS cli ON cli.Codigo=cc.Codigo
             LEFT JOIN erp_zona AS zonas ON zonas.id_zona=cli.id_zona
             LEFT JOIN tipo_cliente AS tpcli ON tpcli.IDTipoCliente= cli.TipoCliente
             WHERE
            ({$rangoFechaCc})
             {$whereNc}
            
            AND cc.Anulado ='No'
            AND cc.TipoComprobante IN ('NCA','NCB',
                                        'NCE','NCC','NCM','FA','FB','FE','FC','FM') 
            GROUP BY cc.CodViajante
             ORDER BY cc.`Fecha`";
        $hacerNc = mysqli_query($connV, $sqlNC) or die("no puedo recuperar las notas de C" . mysqli_error($connV) . $sqlNC);
        $arrayNc = mysqli_fetch_assoc($hacerNc);
    }
    if ($tipo == "un" || $tipo == "peso") {
        $traigoArrayNc++;
    }
    /*
     * SALIDA TABLA HTML
     */
    if ($salida == "html") {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $localidad = setlocale(LC_TIME, NULL); #Guarda localización regional actual
        setlocale(LC_TIME, 'es_AR'); # Localiza en español es_Cenezuela

        $cabecera = array();
        $cabeceraT = array();
        $totalFila = 0;
        $totalGral = 0;
        $arrFiltros = array();
        //     print_r($filtrarPor);
        foreach ($listaFiltro as $ff) {
            $datoFiltro = explode("|", $ff);
            if (isset($datoFiltro[1])) {
                $arrFiltros[$datoFiltro[0]][] = $datoFiltro[2];
            }
        }
        //$renglon[0][] = "Ventas";
        foreach ($arrFiltros as $key => $fil) {
            //            print_r($key);
            //            print_r($fil);
            switch ($key) {
                case 'cliente':
                    // no puedo volver a agrupar.
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    $tituloFil .= " - Cliente: " . implode(",", $fil);

                    //                    $titulo[] = array("titulo" => "{$periodo} / {$tituloFil}","span" => 2,"rowspan"=>1);
                    break;

                case 'tipocliente':
                    // no puedo volver a agrupar.
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    $tituloFil .= " - Tipo Cliente: " . implode(",", $fil);

                    //                    $titulo[] = array("titulo" => "{$periodo} / {$tituloFil}","span" => 2,"rowspan"=>1);
                    break;
                case 'vendedor':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Vendedor: " . implode(",", $fil);

                    //                    $titulo[] = array("titulo" => "{$periodo} / {$tituloFil}","span" => 2,"rowspan"=>1);
                    break;
                case 'articulo':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Art: " . implode(",", $fil);
                    break;
                case 'proveedor':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Prov: " . implode(",", $fil);
                    break;
                case 'zona':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Zona: " . implode(",", $fil);
                    break;
                case 'rubro':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Rubro: " . implode(",", $fil);
                    break;
                case 'subrubro':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - SubR: " . implode(",", $fil);


                    break;
            }
        }
        // colocar en el titulo si estoy haciendo listado por tipo, monto, peso, unidades
        $tituloTipo = "";
        switch ($tipo) {
            case "un":
                $tituloTipo = "UNIDADES ";
                break;
            case "peso":
                $tituloTipo = "KILOGRAMOS";
                break;
            case "monto":
                $tituloTipo = "VENTAS NETAS";
                break;
        }

        if (empty($filtrarPor)) {
            //            $titulo[1] = array("titulo" => "{$tituloTipo} x {$periodo} ","span" => 2,"rowspan"=>1);
            $titulo[] = array("titulo" => "Rubro", "span" => 1, "rowspan" => 2);
            $titulo[] = array("titulo" => "{$tituloTipo} x {$periodo} / Proveedor", "span" => 1, "rowspan" => 2);
        } else {
            $titulo[] = array("titulo" => "Rubro", "span" => 1, "rowspan" => 2);
            $titulo[] = array("titulo" => "{$tituloTipo} x {$periodo}  /  {$tituloFil} ", "span" => 1, "rowspan" => 2);
        }
        $mes = 0;
        $aa = 0;
        //        $titulo[] = array("titulo" => "Rubro","span" => 1,"rowspan"=>2);
        //        $titulo[] = array("titulo" => "{$periodo} / Proveedor","span" => 1,"rowspan"=>2);
        $rub = "";
        /**
         * CABECERAS TH de las tablas.
         */
        foreach ($arrResultado as $campo) {

            switch ($periodo) {
                case "dia":
                    $cabecera[] = array("a" => $campo["aa"], "m" => $campo["mes"], "i" => $campo["dia"] . $campo["mes"], "th" => $campo["dia"]);
                    $cabeceraT[$campo["dia"] . $campo["mes"]] = $campo["dia"];
                    break;
                case "semana":
                    $cabecera[] = array("a" => $campo["aa"], "m" => $campo["mes"], "i" => $campo["semana"] . $campo["mes"], "th" => $campo["semana"]);
                    $cabeceraT[$campo["semana"] . $campo["mes"]] = $campo["semana"] . " - " . $campo["PrimerDiaSemana"] . " al " . $campo["UltimoDiaSemana"];
                    break;
                case "mes":
                    $cabecera[] = array("a" => $campo["aa"], "m" => $campo["mes"], "i" => $campo["mes"] . $campo["aa"], "th" => utf8_encode(strftime("%B", mktime(0, 0, 0, $campo["mes"], $campo["dia"], $campo["aa"]))));
                    $cabeceraT[$campo["mes"] . $campo["aa"]] = utf8_encode(strftime("%B", mktime(0, 0, 0, $campo["mes"], $campo["dia"], $campo["aa"])));
                    break;
            }
            //$colSpan++;
            //$renglon[0][] = $campo["total"];
            $totalGral = $totalGral  + $campo["total"];
        }
        //print_r($totalGral);
        $cabeceraTT = $cabeceraT;
        // este indice ith, es para guardar las claves de combinacion de dia -mes, semana- mes, aa-mes
        $ith = array();
        switch ($periodo) {
            case "dia":
                $ith = array("dia", "mes");
                $cadTitulo = "%B del %Y";
                //$textoT = utf8_encode(strftime("%B del %Y", mktime(0, 0, 0, $aa, $campo["dia"], $campo["aa"])));                        
                break;
            case "semana":
                $ith = array("semana", "mes");
                $cadTitulo = "%B del %Y";
                ///$textoT = utf8_encode(strftime("%B del %Y", mktime(0, 0, 0, $aa, $campo["dia"], $campo["aa"])));                        
                break;
            case "mes":
                $ith = array("mes", "aa");
                $cadTitulo = "%Y";
                //$textoT = utf8_encode(strftime("%Y", mktime(0, 0, 0, $campo["mes"], $campo["dia"], $aa)));                        
                break;
        }

        /*
         * TITULOS
         */
        // recorro las cabeceras y tengo que obtener el mes o dia u año segun lo que tenga.
        // en el periodo para armar los titulos .
        $tit = 0;
        $aaa = 0;
        $mm = 0;
        $colspan = 0;
        $col = 0;
        $ii = array();
        // doble orden del abecera 
        foreach ($cabecera as $clave => $cc) {
            $m[$clave] = $cc["m"];
            $a[$clave] = $cc["a"];
        }
        array_multisort($a, SORT_ASC, $m, SORT_ASC, $cabecera);

        foreach ($cabecera as $ca) {

            if ($tit == 0) {
                $mm = $ca["m"];
                $aaa = $ca["a"];
                $ii[] = $ca["i"];
                $tit++;
            } else {
                if ($mm != $ca["m"] || $aaa != $ca["a"]) {
                    //                    echo "<pre>";
                    //                    print_r($ii);
                    //                    echo "</pre>";
                    $colspan = count($ii);
                    $textoT = utf8_encode(strftime($cadTitulo, mktime(0, 0, 0, $mm, 1, $aaa)));
                    $titulo[$mm . $aaa] = array("titulo" => $textoT, "span" => $colspan, "rowspan" => 1);
                    $mm = $ca["m"];
                    $aaa = $ca["a"];
                    $ii = array($ca["i"]);
                }
                if (!in_array($ca["i"], $ii)) {
                    $ii[] = $ca["i"];
                }
            }
        }


        $colspan = count($ii);

        $textoT = utf8_encode(strftime($cadTitulo, mktime(0, 0, 0, $mm, 1, $aaa)));
        $titulo[$mm . $aaa] = array("titulo" => $textoT, "span" => $colspan, "rowspan" => 1);
        //$titulo[] = array("titulo" => "SubTotal $","span" => 1,"rowspan"=>2);
        if ($operacionRango == "suma" || $operacionRango == "sumag") {
            $titulo[] = array("titulo" => "SubTotal $", "span" => 1, "rowspan" => 2);
        } else {
            $titulo[] = array("titulo" => "Dif $", "span" => 1, "rowspan" => 2);
        }

        $titulo[] = array("titulo" => "SubTotal %", "span" => 1, "rowspan" => 2);

        /***Cambio de cabeceras*/

        /*
         * DATOS
         */
        $rng = 0;
        $rubro = "";
        $prov = "";
        $totaFila = 0;

        foreach ($arrResultado as $campo) {

            $icampo = intval($campo[$ith[0]] . $campo[$ith[1]]);

            if ($rubro === "") {

                $rubro = $campo["codR"];
                $prov = $campo["codP"];
                //$renglon[$rubro][$prov][]= $rubro." - ".$campo["nomR"];
                $renglon[$rubro][$prov][] = $campo["nomR"];
                $renglon[$rubro][$prov][] = $campo["nomP"];

                //recorro las cabeceras y coloco lo que hace falta
                foreach ($cabeceraT as $key => $ca) {

                    if ($icampo === $key) {
                        $renglon[$rubro][$prov][$icampo] = $campo["total"];
                    } else {
                        $renglon[$rubro][$prov][$key] = 0;
                    }
                }

                $totalFila += $campo["total"];
            } else {
                if ($rubro != $campo["codR"]) {
                    $renglon[$rubro][$prov]["subt"] = $totalFila;
                    $renglon[$rubro][$prov]["port"] = $totalFila * 100 / $totalGral;
                    $rubro = $campo["codR"];
                    $prov = $campo["codP"];
                    //$renglon[$rubro][$prov][]= $rubro." - ".$campo["nomR"];
                    $renglon[$rubro][$prov][] = $campo["nomR"];
                    $renglon[$rubro][$prov][] = $campo["nomP"];


                    // cabeceras
                    foreach ($cabeceraT as $key => $ca) {

                        if ($icampo === $key) {
                            $renglon[$rubro][$prov][$icampo] = $campo["total"];
                        } else {
                            $renglon[$rubro][$prov][$key] = 0;
                        }
                    }

                    $totalFila = $campo["total"];
                } else {
                    /// si cambia el proveedor
                    if ($prov != $campo["codP"]) {
                        $renglon[$rubro][$prov]["subt"] = $totalFila;
                        $renglon[$rubro][$prov]["port"] = $totalFila * 100 / $totalGral;
                        $prov = $campo["codP"];
                        //$renglon[$rubro][$prov][]= $rubro." - ".$campo["nomR"];
                        $renglon[$rubro][$prov][] = $campo["nomR"];

                        $renglon[$rubro][$prov][] = $campo["nomP"];

                        //recorro las cabeceras y coloco lo que hace falta
                        foreach ($cabeceraT as $key => $ca) {

                            if ($icampo === $key) {
                                $renglon[$rubro][$prov][$icampo] = $campo["total"];
                            } else {
                                $renglon[$rubro][$prov][$key] = 0;
                            }
                        }
                        $totalFila = $campo["total"];
                    } else {

                        // cabeceras
                        foreach ($cabeceraT as $key => $ca) {

                            if ($icampo === $key) {
                                $renglon[$rubro][$prov][$icampo] = $campo["total"];
                            }
                        }
                        $totalFila += $campo["total"];
                    }
                }
            }
        }
        $renglon[$rubro][$prov]["subt"] = $totalFila;
        if ($totalGral == 0) {
            $renglon[$rubro][$prov]["port"] = 0;
        } else {
            $renglon[$rubro][$prov]["port"] = $totalFila * 100 / $totalGral;
        }

        /* Rango Doble*/
        /*doble rango con suma o diferencia.*/

        if ($operacionRango == "resta") {
            $totalGralP = 0;
            foreach ($renglon as $k => $r) {
                $suma = 0;
                $vuelta = 0;
                foreach ($r as $kk => $valor) {
                    $vuelta = 0;
                    $suma = 0;
                    foreach ($valor as $v => $vv) {
                        if ($v != 0 && $v != 1 && $v != "subt") {
                            if ($vuelta == 0) {
                                $suma = $suma - $vv;
                            } else {
                                $suma = $suma + $vv;
                            }
                            $vuelta++;
                        }
                    }

                    // recalcular el porcentaje total.    
                    $renglon[$k][$kk]["subt"] = $suma;
                    $totalGralP += $suma;
                }
            }
            /*Recalculo de porcentajes en rubro Proveedor.*/

            foreach ($renglon as $k => $r) {
                $suma = 0;
                $vuelta = 0;
                foreach ($r as $kk => $valor) {
                    $vuelta = 0;
                    $suma = $valor["subt"];


                    // recalcular el porcentaje total.    
                    //$renglon[$k][$kk]["subt"] =$suma;
                    if ($totalGralP == 0) {
                        $renglon[$k][$kk]["port"] = 0;
                    } else {
                        $renglon[$k][$kk]["port"] = $suma * 100 / $totalGralP;
                    }
                }
            }
        }

        /**Envio Final*/
        if ($traigoArrayNc == 0) {
            $arrayFinal = array(
                "titulos" => $titulo,
                "cabeceras" => $cabeceraTT,
                "data" => $renglon,
                "impNC" => $arrayNc["importe"]

            );
        } else {
            $arrayFinal = array(
                "titulos" => $titulo,
                "cabeceras" => $cabeceraTT,
                "data" => $renglon


            );
        }
        return json_encode($arrayFinal);
    }
}
/*
 * function: listado_seleccion
 * desc:    busca el total de la tabla pasado como parametro y devuelve un
 * listado de options para llenar una lista
 * @tabla: valor para saber de que tabla debo buscar los options.
 * @salida: es un texto con options.
 */
function listado_seleccion($tabla = null, $codViajante = null, $connV)
{
    $todosClientes = $_SESSION['todos_clientes'];

    $vendedorCargo = array();

    if (isset($_SESSION['vendedor_a_cargo']) && !empty($_SESSION['vendedor_a_cargo'])) {
        $vendedorCargo = $_SESSION['vendedor_a_cargo'];
    }

    $sql = "";
    $lista = "";
    $where = "";
    switch ($tabla) {
        case "cliente":
            // ver si todos los cliente
            if ($todosClientes == 'No') {
                if (!empty($vendedorCargo)) {
                    $where .= " AND cliente.CodViajante IN (" . implode(',', $vendedorCargo) . ") ";
                }

                if (empty($vendedorCargo)) {
                    $where .= " AND cliente.CodViajante =" . $codViajante;
                }
            }
            // pero solo clientes del vendedor....
            $sql = "SELECT cliente.Codigo AS valor,"
                . " CONCAT(cliente.nombre_cliente,' (cod:',cliente.Codigo,')') AS texto "
                . " FROM cliente"
                . " WHERE cliente.Estado='Activo'"
                . $where
                . " ORDER BY texto ASC";
            break;
        case "tipocliente":
            // ver si todos los cliente

            // pero solo clientes del vendedor....
            $sql = "SELECT tipo_cliente.IDTipoCliente AS valor,"
                . " CONCAT(tipo_cliente.NombreTipoCliente,' (cod:',tipo_cliente.NombreTipoCliente,')') AS texto "
                . " FROM tipo_cliente"
                . " WHERE tipo_cliente.Anulado='No'"
                . $where
                . " ORDER BY texto ASC";
            break;
        case "articulo":
            $sql = "SELECT articulo.IDArt AS valor,"
                . " CONCAT(articulo.NombreArticulo,' (cod:',articulo.IDArt,')') AS texto "
                . " FROM articulo"
                . " WHERE articulo.Discontinuo='No'"
                . " ORDER BY texto ASC";
            break;
        case "vendedor":
            if (!empty($vendedorCargo)) {
                $where .= " AND viajantes.CodViajante IN (" . implode(',', $vendedorCargo) . ") ";
            }
            $sql = "SELECT viajantes.CodViajante AS valor,"
                . " CONCAT(viajantes.Nombre,' (cod:',viajantes.CodViajante,')') AS texto "
                . " FROM viajantes"
                . " WHERE viajantes.Anulado='No'"
                . $where
                . " ORDER BY texto ASC";
            break;
        case "proveedor":
            $sql = "SELECT proveedor.Codigo AS valor,"
                . " CONCAT(proveedor.Nombre,' (cod:',proveedor.Codigo,')') AS texto "
                . " FROM proveedor"
                . " WHERE proveedor.Estado='Activo' AND proveedor.Tipo='Mercaderias'"
                . " ORDER BY texto ASC";
            break;

        case "zona":
            $sql = "SELECT erp_zona.id_zona AS valor,"
                . " CONCAT(erp_zona.nombre_zona,' (cod:',erp_zona.id_zona,')') AS texto "
                . " FROM erp_zona"
                . " WHERE erp_zona.anulado='No'"
                . " ORDER BY texto ASC";
            break;
        case "categoria":
            $sql = "SELECT rubro_categoria.id_categoria AS valor,"
                . " CONCAT(rubro_categoria.nombre_categoria,' (cod:',rubro_categoria.id_categoria,')') AS texto "
                . " FROM rubro_categoria"
                . " WHERE rubro_categoria.anulado='No'"
                . " ORDER BY texto ASC";
            break;
        case "rubro":
            $sql = "SELECT rubro.CodigoRubro AS valor,"
                . " CONCAT(rubro.NombreRubro,' (cat:',cat.nombre_categoria,' - ru:',rubro.CodigoRubro,')') AS texto "
                . " FROM rubro"
                . " LEFT JOIN rubro_categoria AS cat ON cat.id_categoria=rubro.id_categoria"
                . " WHERE rubro.anulado='No'"
                . " ORDER BY texto ASC";
            break;
        case "subrubro":
            $sql = "SELECT subrubro.IDSubRubro AS valor,"
                . " CONCAT(subrubro.NombreSubRubro,' (ru: ',rubro.NombreRubro,' - cod: ', subrubro.IDSubRubro ,')') AS texto "
                . " FROM subrubro "
                . " LEFT JOIN rubro ON rubro.CodigoRubro = subrubro.CodigoRubro "
                . " WHERE subrubro.anulado='No'"
                . " ORDER BY texto ASC";
            break;
        case "marca":
            $sql = "SELECT marca.CodMarca AS valor,"
                . " CONCAT(marca.NombreMarca,' (cod:',marca.CodMarca,')') AS texto "
                . " FROM marca"
                . " WHERE marca.anulado='No'"
                . " ORDER BY texto ASC";
            break;
    }
    $hacer = mysqli_query($connV, $sql) or die("no puedo ejecutar el listado " . mysqli_error($connV) . '<pre>' . $sql . '</pre>');
    //    echo "<pre>";
    //    print_r($sql);
    //    echo "</pre>";
    $arrLista[] = array("label" => " - Todos -", "value" => "todos|todos");
    while ($listado = mysqli_fetch_assoc($hacer)) {
        $lista .= '<option value="' . $listado["valor"] . '|' . $listado["texto"] . '"> ' . $listado["texto"] . ' </option>' . "\n";
        $arrLista[] = array(
            "label" => $listado["texto"],
            "value" => $listado["valor"] . '|' . $listado["texto"],
        );
    }
    //    return $lista;
    return json_encode($arrLista);
}
function multidimensional_search($parents, $searched)
{
    if (empty($searched) || empty($parents)) {
        return false;
    }

    foreach ($parents as $key => $value) {
        $exists = true;
        foreach ($searched as $skey => $svalue) {
            $exists = ($exists && isset($parents[$key][$skey]) && $parents[$key][$skey] == $svalue);
        }
        if ($exists) {
            return $key;
        }
    }

    return false;
}

/* funcion de acortar un texto especifico*/
function getSubString($string, $length = NULL)
{
    //Si no se especifica la longitud por defecto es 50
    if ($length == NULL)
        $length = 30;
    //Primero eliminamos las etiquetas html y luego cortamos el string
    $stringDisplay = substr(strip_tags($string), 0, $length);
    //Si el texto es mayor que la longitud se agrega puntos suspensivos
    if (strlen(strip_tags($string)) > $length)
        $stringDisplay .= ' ...';
    return $stringDisplay;
}
function armar_sql_periodo(
    $tipo = null,
    $puntoVenta = null,
    $periodo = null,
    $desde = null,
    $hasta = null,
    $rangoDoble = null,
    $desdeDos = null,
    $hastaDos = null,
    $operacionRango = null
) {
    $comoSumo = "";
    $primerAgrupo = "";
    $segundoAgrupo = "";
    $where = "";
    $orderby = "";
    $agrupar = "mes";

    $nombreRango = "";
    $numeroRango = "";

    $desdT =  implode("/", array_reverse(explode("-", $desde)));
    $hastaT = implode("/", array_reverse(explode("-", $hasta)));
    $desdeDosT = implode("/", array_reverse(explode("-", $desdeDos)));
    $hastaDosT = implode("/", array_reverse(explode("-", $hastaDos)));
    /* PERIODO DE AGRUPACION, RANGO Y AGRUPACION DE PERIODOS*/
    /* Periodo, segun el tipo de operacin si es suma rango o resta */

    /*PERIODO*/
    if ($operacionRango == "suma") {
        switch ($periodo) {
            case "dia":
                $agrupar = "dia,semana";
                break;
            case "semana":
                $agrupar = "semana,mes";
                break;
            case "mes":
                $agrupar = "mes,aa";
                break;
        }
        /* armar el rango de fechas.*/
        /* rango de fehcas.*/

        $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
        if ($rangoDoble == 1) {
            $rangoFecha .= " OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
        }
    } else {
        $agrupar = "rango";
        $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
        if ($rangoDoble == 1) {

            $rangoFecha .= " OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
            $nombreRango = " IF(cc.Fecha>='{$desde}' AND cc.Fecha<='{$hasta}','{$desdT} al {$hastaT}',
            	IF(cc.Fecha>='{$desdeDos}' AND cc.Fecha<='{$hastaDos}','{$desdeDosT} al {$hastaDosT}','nada')
             ) AS rangotxt,";
            $numeroRango = " IF(cc.Fecha>='{$desde}' AND cc.Fecha<='{$hasta}',1,
            	IF(cc.Fecha>='{$desdeDos}' AND cc.Fecha<='{$hastaDos}',2,'nada')
             ) AS rango,";
        } else {
            $nombreRango = "'{$desde} al {$hasta}' AS rangotxt,";
            $numeroRango = " 1 AS rango,";
        }
    }
    /*campos a listar*/


    /* punto de venta
     * reviso el punto de venta si elegi la opcion todos en algun
     * parametro borro toda seleccion, si no , con el explode me queda
     * todo guardado. 
     *      */
    //    print_r($puntoVenta);


    /*filtrar por y su valor
     * se agrego un multiple filtros.
     * creo array con las claves.      
     */

    /* rango de fechas doble.
     */
    //    $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
    //    if($rangoDoble==1){
    //        $rangoFecha .=" OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
    //    }

    $sql = "SELECT
               {$nombreRango}
               {$numeroRango}
                DAY(cc.Fecha)as dia,
                WEEKOFYEAR(cc.Fecha) as semana,
                MONTH(cc.Fecha) as mes2,
                DATE_FORMAT(cc.Fecha,'%m') as mes,
                YEAR(cc.Fecha) AS aa,  
                DATE_FORMAT(
                STR_TO_DATE(CONCAT(YEARWEEK(cc.Fecha),
                'Monday'),'%X%V %W'),'%d/%m') AS PrimerDiaSemana,  
                DATE_FORMAT(
                STR_TO_DATE(CONCAT(YEARWEEK(cc.Fecha),
                'Saturday'),'%X%V %W'),'%d/%m') AS UltimoDiaSemana,             
                COUNT(cc.CodigoMovimiento) AS cuantos
        FROM   cuentacliente AS cc
        
        WHERE
          ({$rangoFecha})       
        AND cc.Anulado ='No'
       
        AND cc.TipoComprobante IN ('NCA','NCB',
                                    'NCE','NCC','NCM','FA','FB','FE','FC','FM')
        {$where}
        GROUP BY    
        {$agrupar} ORDER BY {$orderby} cc.`Fecha`";
    return $sql;
}

function armar_sql(
    $usaIdManual,
    $codViajante = null,
    $tipo = null,
    $listarPor = null,
    $filtrarPor = null,
    $puntoVenta = null,
    $periodo = null,
    $desde = null,
    $hasta = null,
    $rangoDoble = null,
    $desdeDos = null,
    $hastaDos = null,
    $operacionRango = null
) {

    // echo '<pre>';
    // var_dump($usaIdManual,$codViajante,$tipo,$listarPor,$filtrarPor,$puntoVenta,
    // $periodo,$desde,$hasta,$rangoDoble,$desdeDos,$hastaDos,$operacionRango);
    //echo var_dump('filtarpor::',$filtrarPor);
    //echo '<pre>';

    $comoSumo = "";
    $primerAgrupo = "";
    $segundoAgrupo = "";
    $whereVendedores = "";
    $where = "";
    $usoAcargo = 0;
    // vendedor a cargo
    $vendedorCargo = $_SESSION['vendedor_a_cargo'];
    //echo 'hola '.var_dump($_SESSION['vendedor_a_cargo']);    
    // no tengo acargo traigo solo lo mio. y no podria filtrar.
    if (empty($vendedorCargo)) {
        //$where = " AND cc.CodViajante={$codViajante}";
        $whereVendedores = " AND cc.CodViajante={$codViajante}";
    }

    // si no tengo filtros tengo y soy supervisor debo traer los vendedores a mi cargo
    if (!empty($vendedorCargo)) {

        $whereVendedores = " AND cc.CodViajante IN (" . implode(", ", $vendedorCargo) . ")";
    }

    $orderby = "";
    $agrupar = "mes";
    $nombreRango = "";
    $numeroRango = "";
    $desdT =  implode("/", array_reverse(explode("-", $desde)));
    $hastaT = implode("/", array_reverse(explode("-", $hasta)));
    $desdeDosT = implode("/", array_reverse(explode("-", $desdeDos)));
    $hastaDosT = implode("/", array_reverse(explode("-", $hastaDos)));

    // evaluar el where para ver si puedo ver solo mis ventas

    if ($operacionRango == "suma") {
        /*PERIODO*/
        switch ($periodo) {
            case "dia":
                $agrupar = "dia,semana";
                break;
            case "semana":
                $agrupar = "semana,mes";
                break;
            case "mes":
                $agrupar = "mes,aa";
                break;
        }
        /* armar el rango de fechas.*/
        /* rango de fehcas.*/

        $rangoFecha = " (stock.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
        if ($rangoDoble == 1) {
            $rangoFecha .= " OR (stock.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
        }
    } else {
        // si la operacion es suma con agrupacion o diferencia no agrupo por nada.
        /* Agrupacion*/
        $agrupar = "rango";
        /*Campos para la agrupacion de varios meses en solo dos o un rango.*/

        $rangoFecha = " (stock.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";

        if ($rangoDoble == 1) {

            $rangoFecha .= " OR (stock.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
            $nombreRango = " IF(stock.Fecha>='{$desde}' AND stock.Fecha<='{$hasta}','{$desdT} al {$hastaT}',
            	IF(stock.Fecha>='{$desdeDos}' AND stock.Fecha<='{$hastaDos}','{$desdeDosT} al {$hastaDosT}','nada')
             ) AS rangotxt,";
            $numeroRango = " IF(stock.Fecha>='{$desde}' AND stock.Fecha<='{$hasta}',1,
            	IF(stock.Fecha>='{$desdeDos}' AND stock.Fecha<='{$hastaDos}',2,'nada')
             ) AS rango,";
        } else {
            $nombreRango = " '{$desdT} al {$hastaT}' AS rangotxt,";
            $numeroRango = " 1 AS rango,";
        }
    }

    // como sumo dinero, cantidades, kg
    switch ($tipo) {
        case 'un':
            $comoSumo = " SUM( IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV' OR 
                stock.TipoComp = 'Anul NC Devol' OR 
                stock.TipoComp ='ND Anul NC'
            ,stock.Cantidad,stock.Cantidad * -1)) AS total";
            break;
        case 'peso':
            // tengo que multiplicar por 1000 si esta en gramos. CDOGIO p todos los clientes
            // reemplazar cuando sea kilos y prestar atencion cuando se haga por unidades
            //            $comoSumo ="  SUM( IF(
            //                stock.TipoComp ='Venta' OR
            //                stock.TipoComp ='Venta TPV' OR 
            //                stock.TipoComp = 'Anul NC Devol' OR 
            //                stock.TipoComp ='ND Anul NC'
            //            
            //            ,IF(arti.id_unimed=32,stock.cantidad_uni * stock.Cantidad / 1000 ,
            //                
            //                IF(arti.id_unimed=36,stock.cantidad_uni * stock.Cantidad / 10000,stock.cantidad_uni * stock.Cantidad)
            //                ),
            //                IF(arti.id_unimed=32 ,stock.cantidad_uni * stock.Cantidad / 1000 * -1,
            //                    
            //                    IF(arti.id_unimed=36,stock.cantidad_uni * stock.Cantidad / 10000 *-1 ,stock.cantidad_uni * stock.Cantidad * -1)
            //                )
            //            )) AS total";

            //            $comoSumo ="SUM( IF(
            //                stock.TipoComp ='Venta' OR
            //                stock.TipoComp ='Venta TPV' OR 
            //                stock.TipoComp = 'Anul NC Devol' OR 
            //                stock.TipoComp ='ND Anul NC'
            //            
            //            ,IF(arti.id_unimed=32, stock.Cantidad / 1000 * COALESCE(kg.valor1,1) ,
            //                
            //                IF(arti.id_unimed=36, stock.Cantidad / 10000 * COALESCE(kg.valor1,1),stock.Cantidad* COALESCE(kg.valor1,1) ) 
            //                ),
            //                IF(arti.id_unimed=32 , stock.Cantidad / 1000 * -1 * COALESCE(kg.valor1,1),
            //                    
            //                    IF(arti.id_unimed=36,stock.Cantidad / 10000 *-1* COALESCE(kg.valor1,1) ,stock.Cantidad * -1* COALESCE(kg.valor1,1) )
            //                )
            //            )) AS total";

            $comoSumo = " arti.cantidad_promedio_bulto AS kg,
                SUM( IF(
                                stock.TipoComp ='Venta' OR
                                stock.TipoComp ='Venta TPV' OR                                 
                                stock.TipoComp ='ND Anul NC'            
                                ,stock.Cantidad * arti.cantidad_promedio_bulto 
                                ,stock.Cantidad * -1 * arti.cantidad_promedio_bulto
		
                                )
                            ) AS total";
            // si esta opcion tomamos vamos a tener que filtrar articulos que no se pueden
            //$where .=" AND (arti.id_unimed=1 OR arti.id_unimed=32 OR arti.id_unimed=36) ";
            // medir por peso.
            break;
        case 'monto':
            //            $comoSumo =" SUM( IF(
            //                stock.TipoComp ='Venta' OR
            //                stock.TipoComp ='Venta TPV' OR 
            //                stock.TipoComp = 'Anul NC Devol' OR 
            //                stock.TipoComp ='ND Anul NC'
            //            ,stock.PrecioNetoxR,stock.PrecioNetoxR * -1)) AS total";

            $comoSumo = "SUM(
              
              IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV' OR                
                stock.TipoComp ='ND Anul NC'
            	,stock.PrecioNetoxR,
            	stock.PrecioNetoxR * -1) 
            
           ) AS total ";
            break;
    }
    /*campos a listar*/
    switch ($listarPor) {
        case 'cliente':
            //            $primerAgrupo = "cli.Codigo AS cod,cli.nombre_cliente  As nom,";
            //            $agrupar .=",cli.Codigo";
            //            $orderby .="cli.nombre_cliente ASC, ";
            //            
            if ($usaIdManual == 'Si') {
                $primerAgrupo = "cli.id_manual_cli AS cod,concat('(cod: ',cli.id_manual_cli,') ',cli.nombre_cliente)  As nom,";
                $agrupar .= ",cli.id_manual_cli";
                $orderby .= "cli.nombre_cliente ASC, ";
                //$where .= " AND NOT ISNULL(arti.id_manual) ";
            } else {
                $primerAgrupo = "cli.Codigo AS cod,cli.nombre_cliente  As nom,";
                $agrupar .= ",cli.Codigo";
                $orderby .= "cli.nombre_cliente ASC, ";
            }
            break;
        case 'tipocliente':
            $primerAgrupo = "tpcli.IDTipoCliente AS cod,tpcli.NombreTipoCliente  As nom,";
            $agrupar .= ",tpcli.IDTipoCliente";
            $orderby .= "tpcli.NombreTipoCliente ASC, ";
            break;
        case 'vendedor':
            $primerAgrupo = "vend.CodViajante AS cod,vend.Nombre  As nom,";
            $agrupar .= ",vend.CodViajante";
            $orderby .= "vend.Nombre ASC, ";
            break;
        case 'articulo':
            //             $primerAgrupo = "arti.IDArt AS cod,arti.NombreArticulo  As nom,";
            //             $agrupar .=",arti.IDArt";
            //             $orderby .="arti.NombreArticulo ASC, ";
            if ($usaIdManual == 'Si') {
                $primerAgrupo = "arti.id_manual AS cod,concat('(cod: ',arti.id_manual,') ',arti.NombreArticulo)  As nom,";
                $agrupar .= ",arti.id_manual";
                $orderby .= "arti.NombreArticulo ASC, ";
                $where .= " AND NOT ISNULL(arti.id_manual) ";
            } else {
                $primerAgrupo = "arti.IDArt AS cod,concat(' (cod: ',arti.IDArt,') ',arti.NombreArticulo)  As nom,";
                $agrupar .= ",arti.IDArt";
                $orderby .= "arti.NombreArticulo ASC, ";
            }

            break;
        case 'proveedor':
            //            $primerAgrupo = " prov.Codigo AS cod,prov.Nombre As nom,";
            //             $agrupar .=",prov.Codigo";
            //             $orderby .=" prov.Nombre ASC, ";
            if ($usaIdManual == 'Si') {
                $primerAgrupo = " prov.id_manual_prov AS cod,concat('(cod: ',prov.id_manual_prov,') ',prov.Nombre) As nom,";
                $agrupar .= ",prov.id_manual_prov";
                $orderby .= " prov.Nombre ASC, ";
                $where .= " AND NOT ISNULL(prov.id_manual_prov) ";
            } else {
                $primerAgrupo = " prov.Codigo AS cod,prov.Nombre As nom,";
                $agrupar .= ",prov.Codigo";
                $orderby .= " prov.Nombre ASC, ";
            }
            break;
        case 'zona':
            $primerAgrupo = " zonas.id_zona AS cod,zonas.nombre_zona As nom,";
            $agrupar .= ",zonas.id_zona";
            $orderby .= " zonas.nombre_zona ASC, ";

            break;
        case 'rubro':
            $primerAgrupo = " ru.CodigoRubro AS cod, ru.NombreRubro AS nom,";
            $agrupar .= ",ru.CodigoRubro";
            $orderby .= " ru.NombreRubro ASC, ";
            break;
        case 'subrubro':
            $primerAgrupo = "srub.IdSubRubro AS cod,srub.NombreSubRubro As nom"
                . ",ru.CodigoRubro AS cod3, ru.NombreRubro AS nom3,";
            $agrupar .= ",srub.IdSubRubro";
            $orderby .= " ru.NombreRubro ASC, srub.NombreSubRubro ASC, ";
            break;
    }

    /* punto de venta
     * reviso el punto de venta si elegi la opcion todos en algun
     * parametro borro toda seleccion, si no , con el explode me queda
     * todo guardado. 
     *      */
    //    print_r($puntoVenta);
    //    var_dump(empty($puntoVenta));
    //    if(!empty($puntoVenta)){
    //        if(!in_array("todos",$puntoVenta)){
    //            $where .= " AND cc.id_pv IN (".implode(",",$puntoVenta).")";
    //        }
    //    }



    /*filtrar por y su valor
     * se agrego un multiple filtros.
     * creo array con las claves.      
     */
    $arrFiltros = array();
    //     print_r($filtrarPor);
    foreach ($filtrarPor as $ff) {
        $datoFiltro = explode("|", $ff);
        if (isset($datoFiltro[1])) {
            $arrFiltros[$datoFiltro[0]][] = $datoFiltro[1];
        }
    }


    foreach ($arrFiltros as $clave => $fi) {
        //        var_dump(empty($fi));
        //        print_r($fi);

        switch ($clave) {
            case 'cliente':
                // no puedo volver a agrupar.
                if ($listarPor != 'cliente') {
                    $primerAgrupo .= 'cli.Codigo AS cod2,cli.nombre_cliente  As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.

                if (!in_array("todos", $fi)) {
                    $where .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'tipocliente':
                // no puedo volver a agrupar.
                if ($listarPor != 'tipocliente') {
                    $primerAgrupo .= 'tpcli.IDTipoCliente AS cod2,tpcli.NombreTipoCliente  As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.

                if (!in_array("todos", $fi)) {
                    $where .= ' AND tpcli.IDTipoCliente IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'vendedor':
                // no puedo volver a agrupar.
                if (!in_array("todos", $fi)) {
                    $primerAgrupo .= 'vend.CodViajante AS cod2,vend.Nombre  As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND vend.CodViajante IN (' . implode(",", $fi) . ")";
                    $usoAcargo++;
                }

                break;
            case 'articulo':
                // no puedo volver a agrupar.
                if ($listarPor != 'articulo') {
                    $primerAgrupo .= 'arti.IDArt AS cod2,arti.NombreArticulo  As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND arti.IDArt IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'proveedor':
                // no puedo volver a agrupar.
                if ($listarPor != 'proveedor') {
                    $primerAgrupo .= 'prov.Codigo AS cod2,prov.Nombre As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND prov.Codigo IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'zona':
                // no puedo volver a agrupar.
                if ($listarPor != 'zona') {
                    $primerAgrupo .= ' zonas.id_zona AS cod2,zonas.nombre_zona As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND  zonas.id_zona IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'rubro':
                // no puedo volver a agrupar.
                if ($listarPor != 'rubro') {
                    $primerAgrupo .= 'ru.CodigoRubro AS cod2, ru.NombreRubro AS nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND ru.CodigoRubro IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'subrubro':
                // no puedo volver a agrupar.
                if ($listarPor != 'subrubro') {
                    $primerAgrupo .= 'srub.IdSubRubro AS cod,srub.NombreSubRubro As nom,';
                }
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND srub.IdSubRubro IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'marca':
                // no puedo volver a agrupar.
                if ($listarPor != 'marca') {
                    $primerAgrupo .= ' marca.CodMarca AS cod2,marca.NombreMarca As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND  marca.CodMarca IN (' . implode(",", $fi) . ")";
                }

                break;
        }
    }

    // uso el filtro de vendedor
    if ($usoAcargo == 0) {
        $where .= $whereVendedores;
    }

    /* armar el rango de fechas.*/
    /* */

    //    $rangoFecha =" (stock.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
    //    if($rangoDoble==1){
    //        $rangoFecha .=" OR (stock.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
    //    }

    /*armando de sql*/
    $sql = "SELECT
            {$primerAgrupo}
            {$segundoAgrupo}
            {$nombreRango}
            {$numeroRango}
            DAY(stock.Fecha)as dia,
            WEEKOFYEAR(stock.Fecha) as semana,
            MONTH(stock.Fecha) as mes2,
            DATE_FORMAT(stock.Fecha,'%m') as mes,
            YEAR(stock.Fecha) AS aa,
  
            DATE_FORMAT(
            STR_TO_DATE(CONCAT(YEARWEEK(stock.Fecha),
            'Monday'),'%X%V %W'),'%d/%m') AS PrimerDiaSemana,  
            DATE_FORMAT(
            STR_TO_DATE(CONCAT(YEARWEEK(stock.Fecha),
            'Saturday'),'%X%V %W'),'%d/%m') AS UltimoDiaSemana,   
            {$comoSumo} 
            FROM stock
                LEFT JOIN cuentacliente AS cc ON (cc.CodigoMovimiento= stock.CodigoMovimiento) 
                LEFT JOIN articulo AS arti ON arti.IDArt = stock.IDArt
                LEFT JOIN articulo_val_ce AS kg ON (kg.id_articulo = arti.IDArt AND kg.id_articulo_ce=1)
                LEFT JOIN rubro AS ru ON ru.CodigoRubro = arti.CodigoRubro
                LEFT JOIN subrubro AS srub ON srub.IDSubRubro = arti.IDSubRubro
                LEFT JOIN marca ON marca.CodMarca=arti.CodigoMarca
                LEFT JOIN proveedor AS prov ON prov.Codigo = arti.CodigoProveedor
                LEFT JOIN cliente AS cli ON (cli.Codigo= stock.CodigoCP)
                LEFT JOIN viajantes AS vend ON (vend.CodViajante= cc.CodViajante)
                LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
                LEFT JOIN punto_venta AS ppv ON ( ppv.id_punto_venta=cc.id_pv)
                LEFT JOIN tipo_cliente AS tpcli ON (tpcli.IDTipoCliente=cli.TipoCliente)
           WHERE
                ({$rangoFecha})
               AND cc.Anulado='No'
               AND stock.Anulado='No'
                AND (stock.TipoComp = 'Venta' 
                    OR stock.TipoComp = 'Venta TPV' 
                    OR stock.TipoComp = 'Devol - Cliente' 
                    OR stock.TipoComp = 'ND Anul NC'
                    )
                 {$where}    

            GROUP BY {$agrupar} ORDER BY {$orderby} stock.Fecha ASC";
    //echo '<pre>';
    //print_r($sql);
    //echo '</pre>';
    return $sql;
}

function armar_sql_nc(
    $codViajante = null,
    $tipo = null,
    $listarPor = null,
    $filtrarPor = null,
    $puntoVenta = null,
    $periodo = null,
    $desde = null,
    $hasta = null,
    $rangoDoble = null,
    $desdeDos = null,
    $hastaDos = null,
    $operacionRango = null
) {

    $comoSumo = "";
    $primerAgrupo = "";
    $segundoAgrupo = "";
    $where = "";
    $orderby = "";
    $agrupar = "mes";
    $nombreRango = "";
    $numeroRango = "";
    $whereVendedores = "";
    $usoAcargo = 0;
    // vendedor a cargo
    $vendedorCargo = $_SESSION['vendedor_a_cargo'];


    // no tengo acargo traigo solo lo mio. y no podria filtrar.
    if (empty($vendedorCargo)) {
        $whereVendedores = " AND cc.CodViajante={$codViajante}";
    }
    // si no tengo filtros tengo y soy supervisor debo traer los vendedores a mi cargo
    if (!empty($vendedorCargo)) {

        $whereVendedores = " AND cc.CodViajante IN (" . implode(", ", $vendedorCargo) . ")";
    }


    if ($operacionRango == "suma") {
        /*PERIODO*/
        switch ($periodo) {
            case "dia":
                $agrupar = "dia,semana";
                break;
            case "semana":
                $agrupar = "semana,mes";
                break;
            case "mes":
                $agrupar = "mes,aa";
                break;
        }
        /* armar el rango de fechas.*/
        /* rango de fehcas.*/

        $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
        if ($rangoDoble == 1) {
            $rangoFecha .= " OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
        }
    } else {
        // si la operacion es suma con agrupacion o diferencia no agrupo por nada.
        /* Agrupacion*/
        $agrupar = "rango";
        /*Campos para la agrupacion de varios meses en solo dos o un rango.*/

        $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
        if ($rangoDoble == 1) {

            $rangoFecha .= " OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
            $nombreRango = " IF(cc.Fecha>='{$desde}' AND cc.Fecha<='{$hasta}','{$desde} al {$hasta}',
            	IF(cc.Fecha>='{$desdeDos}' AND cc.Fecha<='{$hastaDos}','{$desdeDos} al {$hastaDos}','nada')
             ) AS rangotxt,";
            $numeroRango = " IF(cc.Fecha>='{$desde}' AND cc.Fecha<='{$hasta}',1,
            	IF(cc.Fecha>='{$desdeDos}' AND cc.Fecha<='{$hastaDos}',2,'nada')
             ) AS rango,";
        } else {
            $nombreRango = "'{$desde} al {$hasta}' AS rangotxt,";
            $numeroRango = " 1 AS rango,";
        }
    }

    /*campos a listar*/
    switch ($listarPor) {
        case 'cliente':
            $primerAgrupo = "cc.Codigo AS cod,cli.nombre_cliente  As nom,";
            $agrupar .= ",cc.Codigo";
            $orderby .= "cc.Codigo ASC, ";
            break;
        case 'tipocliente':
            $primerAgrupo = "tpcli.IDTipoCliente AS cod,tpcli.NombreTipoCliente AS nom";
            $agrupar .= ",tpcli.IDTipoCliente";
            $orderby .= "tpcli.IDTipoCliente ASC, ";
            break;
        case 'vendedor':
            $primerAgrupo = "cc.CodViajante AS cod,vend.Nombre  As nom,";
            $agrupar .= ",vend.CodViajante";
            $orderby .= "vend.Nombre ASC, ";
            break;

        case 'zona':
            $primerAgrupo = " zonas.id_zona AS cod,zonas.nombre_zona As nom,";
            $agrupar .= ",zonas.id_zona";
            $orderby .= " zonas.nombre_zona ASC, ";

            break;
    }

    /* punto de venta
     * reviso el punto de venta si elegi la opcion todos en algun
     * parametro borro toda seleccion, si no , con el explode me queda
     * todo guardado. 
     *      */
    //    print_r($puntoVenta);
    if (!empty($puntoVenta)) {
        if (!in_array("todos", $puntoVenta)) {
            $where .= " AND cc.id_pv IN (" . implode(",", $puntoVenta) . ")";
        }
    }


    /*filtrar por y su valor
     * se agrego un multiple filtros.
     * creo array con las claves.      
     */
    $arrFiltros = array();
    //     print_r($filtrarPor);
    if (!empty($filtrarPor)) {
        foreach ($filtrarPor as $ff) {

            $datoFiltro = explode("|", $ff);
            //         print_r($datoFiltro);
            if (isset($datoFiltro[1])) {
                $arrFiltros[$datoFiltro[0]][] = $datoFiltro[1];
            }
            //        print_r($arrFiltros);
        }

        foreach ($arrFiltros as $clave => $fi) {
            //        print_r($clave);
            //        print_r($fi);
            switch ($clave) {
                case 'cliente':
                    // no puedo volver a agrupar.
                    if ($listarPor != 'cliente') {
                        $primerAgrupo .= 'cli.Codigo AS cod2,cli.nombre_cliente  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                        //$where .= $whereVendedores;
                    }

                    break;
                case 'tipocliente':
                    // no puedo volver a agrupar.
                    if ($listarPor != 'tipocliente') {
                        $primerAgrupo .= 'tpcli.IDTipoCliente AS cod2,tpcli.NombreTipoCliente  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where .= ' AND tpcli.IDTipoCliente IN (' . implode(",", $fi) . ")";
                        //$where .= $whereVendedores;
                    }

                    break;
                case 'vendedor':
                    // no puedo volver a agrupar.
                    if (!in_array("todos", $fi)) {
                        $primerAgrupo .= 'vend.CodViajante AS cod2,vend.Nombre  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where .= ' AND vend.CodViajante IN (' . implode(",", $fi) . ")";
                        $usoAcargo++;
                    }


                    break;


                case 'zona':
                    // no puedo volver a agrupar.
                    if ($listarPor != 'zona') {
                        $primerAgrupo .= ' zonas.id_zona AS cod2,zonas.nombre_zona As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where .= ' AND  zonas.id_zona IN (' . implode(",", $fi) . ")";
                        //$where .= $whereVendedores;
                    }

                    break;
            }
        }
    }
    /* rango de fechas doble.
     */
    //    $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
    //    if($rangoDoble==1){
    //        $rangoFecha .=" OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
    //    }

    // a cargo  
    if ($usoAcargo == 0) {
        $where .= $whereVendedores;
    }

    $sql = "SELECT
                {$primerAgrupo}
                {$segundoAgrupo}
                {$nombreRango}
                {$numeroRango}    
                DAY(cc.Fecha)as dia,
                WEEKOFYEAR(cc.Fecha) as semana,
                MONTH(cc.Fecha) as mes2,
                DATE_FORMAT(cc.Fecha,'%m') as mes,
                YEAR(cc.Fecha) AS aa,  
                DATE_FORMAT(
                STR_TO_DATE(CONCAT(YEARWEEK(cc.Fecha),
                'Monday'),'%X%V %W'),'%d/%m') AS PrimerDiaSemana,  
                DATE_FORMAT(
                STR_TO_DATE(CONCAT(YEARWEEK(cc.Fecha),
                'Saturday'),'%X%V %W'),'%d/%m') AS UltimoDiaSemana,             
                    SUM(
                    IF(cc.TipoNC = 'Devolucion',
                        IF(cc.ImpDesc1<>0 OR cc.ImpDesc2<>0,
                            (cc.ImpDesc1+cc.ImpDesc2) ,0
                            ),
                        IF(cc.TipoComprobante ='NDA' 
                            OR cc.TipoComprobante ='NDB' 
                            OR cc.TipoComprobante ='NDE' 
                            OR cc.TipoComprobante ='NDC' 
                            OR cc.TipoComprobante ='NDM',
                            cc.SubtotalDesc,
                                IF(cc.TipoComprobante ='NCA' 
                                    OR cc.TipoComprobante ='NCB' 
                                    OR cc.TipoComprobante ='NCE' 
                                    OR cc.TipoComprobante ='NCC' 
                                    OR cc.TipoComprobante ='NCM',
                                    cc.SubtotalDesc * -1,
                                        IF(cc.TipoComprobante ='FA' 
                                            OR cc.TipoComprobante ='FB' 
                                            OR cc.TipoComprobante ='FE' 
                                            OR cc.TipoComprobante ='FC' 
                                            OR cc.TipoComprobante ='FM',
                                            (cc.ImpDesc1+cc.ImpDesc2) * -1,0
                                        ) 
                                )
                          )
                    )) AS total
        FROM   cuentacliente AS cc
        LEFT JOIN viajantes AS vend ON (vend.CodViajante= cc.CodViajante)
        LEFT JOIN cliente AS cli ON cli.Codigo = cc.Codigo
        LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
        LEFT JOIN tipo_cliente AS tpcli ON (tpcli.IDTipoCliente=cli.TipoCliente)
        WHERE
          ({$rangoFecha})       
        AND cc.Anulado ='No'
       
        AND cc.TipoComprobante IN ('NCA','NCB',
                                    'NCE','NCC','NCM','FA','FB','FE','FC','FM')
        {$where}
        GROUP BY    
        {$agrupar} ORDER BY {$orderby} cc.`Fecha`";
    return $sql;
}

function traer_valor_nc($connV, $codViajante = null, $puntoVentaId = null, $desde = null, $hasta = null, $periodo, $rangoDoble = null, $desdeDos = null, $hastaDos = null, $filtrarPor = null)
{
    switch ($periodo) {
        case "dia":
            $agrupar = "dia,semana";
            $ith = array("dia", "mes");
            break;
        case "semana":
            $agrupar = "semana,mes";
            $ith = array("semana", "mes");
            break;
        case "mes":
            $agrupar = "mes,aa";
            $ith = array("aa", "mes");
            break;
    }

    $whereVendedores = "";
    $usoAcargo = 0;
    $where = '';
    // vendedor a cargo
    $vendedorCargo = $_SESSION['vendedor_a_cargo'];


    // no tengo acargo traigo solo lo mio. y no podria filtrar.
    if (empty($vendedorCargo)) {
        $whereVendedores = " AND cc.CodViajante={$codViajante}";
    }

    // si no tengo filtros tengo y soy supervisor debo traer los vendedores a mi cargo
    if (!empty($vendedorCargo) && (empty($filtrarPor) || $filtrarPor == null)) {

        $whereVendedores = " AND cc.CodViajante IN (" . implode(", ", $vendedorCargo) . ")";
    }


    // $where = " AND cc.CodViajante={$codViajante}";
    $arrFiltros = array();
    //     print_r($filtrarPor);
    if (!empty($filtrarPor)) {
        foreach ($filtrarPor as $ff) {

            $datoFiltro = explode("|", $ff);
            //         print_r($datoFiltro);
            if (isset($datoFiltro[1])) {
                $arrFiltros[$datoFiltro[0]][] = $datoFiltro[1];
            }
            //        print_r($arrFiltros);
        }

        foreach ($arrFiltros as $clave => $fi) {
            //        print_r($clave);
            //        print_r($fi);
            switch ($clave) {
                case 'cliente':
                    // no puedo volver a agrupar.
                    /* if ($listarPor != 'cliente') {
                        $primerAgrupo .='cli.Codigo AS cod2,cli.nombre_cliente  As nom2,';
                    } */
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                        $where .= $whereVendedores;
                    }

                    break;
                case 'tipocliente':
                    // no puedo volver a agrupar.
                    /* if ($listarPor != 'tipocliente') {
                        $primerAgrupo .='tpcli.IDTipoCliente AS cod2,tpcli.NombreTipoCliente  As nom2,';
                    } */
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where .= ' AND tpcli.IDTipoCliente IN (' . implode(",", $fi) . ")";
                        $where .= $whereVendedores;
                    }

                    break;
                case 'vendedor':
                    // no puedo volver a agrupar.
                    /* if (!in_array("todos", $fi)) {
                        $primerAgrupo .='vend.CodViajante AS cod2,vend.Nombre  As nom2,';
                    } */
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where .= ' AND vend.CodViajante IN (' . implode(",", $fi) . ")";
                        $usoAcargo++;
                    }

                    break;


                case 'zona':
                    // no puedo volver a agrupar.
                    /* if ($listarPor != 'zona') {
                        $primerAgrupo .=' zonas.id_zona AS cod2,zonas.nombre_zona As nom2,';
                    } */
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where .= ' AND  zonas.id_zona IN (' . implode(",", $fi) . ")";
                        $where .= $whereVendedores;
                    }

                    break;
            }
        }
    }

    // uso vendedor a cargo
    if ($usoAcargo == 0) {
        $where .= $whereVendedores;
    }

    /*
     * Rango de fecha
     */
    //    var_dump($rangoDoble);
    $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta} ') ";
    if ($rangoDoble == 1) {
        $rangoFecha .= " OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}')";
    }
    $sqlNC = " SELECT 
                DAY(cc.Fecha)as dia,
                WEEKOFYEAR(cc.Fecha) as semana,
                MONTH(cc.Fecha) as mes2,
                DATE_FORMAT(cc.Fecha,'%m') as mes,
                YEAR(cc.Fecha) AS aa,  
                DATE_FORMAT(
                STR_TO_DATE(CONCAT(YEARWEEK(cc.Fecha),
                'Monday'),'%X%V %W'),'%d/%m') AS PrimerDiaSemana,  
                DATE_FORMAT(
                STR_TO_DATE(CONCAT(YEARWEEK(cc.Fecha),
                'Saturday'),'%X%V %W'),'%d/%m') AS UltimoDiaSemana,
            SUM(
            IF(cc.TipoNC = 'Devolucion',
                IF(cc.ImpDesc1<>0 OR cc.ImpDesc2<>0,
                    (cc.ImpDesc1+cc.ImpDesc2),0
                    ),
                IF(cc.TipoComprobante ='NDA' 
                    OR cc.TipoComprobante ='NDB' 
                    OR cc.TipoComprobante ='NDE' 
                    OR cc.TipoComprobante ='NDC' 
                    OR cc.TipoComprobante ='NDM',
                    cc.SubtotalDesc,
                        IF(cc.TipoComprobante ='NCA' 
                            OR cc.TipoComprobante ='NCB' 
                            OR cc.TipoComprobante ='NCE' 
                            OR cc.TipoComprobante ='NCC' 
                            OR cc.TipoComprobante ='NCM',
                            cc.SubtotalDesc * -1,
                                IF(cc.TipoComprobante ='FA' 
                                    OR cc.TipoComprobante ='FB' 
                                    OR cc.TipoComprobante ='FE' 
                                    OR cc.TipoComprobante ='FC' 
                                    OR cc.TipoComprobante ='FM',
                                    (cc.ImpDesc1+cc.ImpDesc2) * -1,0
                                ) 
                        )
                  )
            )) AS importe
     FROM   cuentacliente AS cc
     LEFT JOIN cliente AS cli ON cli.Codigo=cc.Codigo
     LEFT JOIN tipo_cliente AS tpcli ON tpcli.IDTipoCliente=cli.TipoCliente
     WHERE
      ({$rangoFecha})
      {$where}    
    AND cc.Anulado ='No'
    
    AND cc.TipoComprobante IN ('NCA','NCB',
                                'NCE','NCC','NCM','FA','FB','FE','FC','FM')
    GROUP BY {$agrupar} 
     ORDER BY cc.`Fecha` ASC";

    //print_r($sqlNC);
    /*
    AND cc.TipoComprobante IN ('NDA','NDB','NDE','NDC','NDM','NCA','NCB',
                                'NCE','NCC','NCM','FA','FB','FE','FC','FM')
     *      */

    $hacerNc = mysqli_query($connV, $sqlNC) or die("no puedo recuperar las notas de C" . mysqli_error($connV) . $sqlNC);
    $arrayNc = array();
    while ($nc = mysqli_fetch_assoc($hacerNc)) {
        $icampo = intval($nc[$ith[0]] . $nc[$ith[1]]);
        $arrayNc[$icampo] = $nc["importe"];
    }
    //print_r($arrayNc);
    return $arrayNc;
}
// funcion para ubicar las ventas netas por vendedor pero por lciente articulo
// y despues alguna otra fucion
function ventas_totales_todos(
    $usaIdManual,
    $connV,
    $codViajante = null,
    $tipo = null,
    $listarPor = null,
    $filtrarPor = null,
    $puntoVenta = null,
    $desde = null,
    $hasta = null,
    $periodo = null,
    $salida = null,
    $grafico = null,
    $rangoDoble = null,
    $desdeDos = null,
    $hastaDos = null,
    $operacionRango = null
) {

    // echo "Varibles que recibo:<pre>";
    //         var_dump($usaIdManual,$connV,$codViajante,$tipo,$listarPor,$filtrarPor,
    //         $puntoVenta, $desde,$hasta,$periodo,$salida,$grafico,$rangoDoble,$desdeDos,$hastaDos,$operacionRango);

    // echo '<br>filtrarPor::';
    // var_dump($filtrarPor);

    // FILTRO
    $listaFiltro = array();
    if ($filtrarPor) {
        $listaFiltro = explode("||", $filtrarPor);
    }

    // print_r($listaFiltro).PHP_EOL;
    // var_dump($listaFiltro).PHP_EOL;
    // echo "</pre>";    
    //    foreach($pvLista as $pv){
    //        $arrPuntoVenta = explode("|", $pv);
    //        if($arrPuntoVenta[1]=="todos"){
    //            $puntoVentaId[] = "todos";
    //        }else{
    //            if($arrPuntoVenta[0]!=""){
    //                $puntoVentaId[] = $arrPuntoVenta[0];
    //            }
    //            
    //        }
    //        
    //        $puntoVentaTxt[] = $arrPuntoVenta[1];
    //    }
    //    $tituloFil =" PV: ".implode(",", $puntoVentaTxt);
    $tituloFil = "";
    // filtro valores

    $arrFiltros = array();

    foreach ($listaFiltro as $valorFiltro) {
        $datoFiltro = explode("|", $valorFiltro);
        if (isset($datoFiltro[2])) {
            $arrFiltros[$datoFiltro[0]][] = $datoFiltro[2];
        }
    }

    // return $sqlTotal;
    $arrResultado = array();
    $arrResultadoNc = array();
    $agrupar = "mes";

    switch ($periodo) {
        case "dia":
            $agrupar = "dia,semana";
            $ithClave = array(0 => "semana", 1 => "dia");
            break;
        case "semana":
            $agrupar = "semana,mes";
            $ithClave = array(0 => "mes", 1 => "semana");
            break;
        case "mes":
            $agrupar = "mes,aa";
            $ithClave = array(0 => "aa", 1 => "mes");
            break;
    }


    //manejar las cabeceras 
    if ($rangoDoble == 1) {
        $sqlPeriodo = armar_sql_periodo($tipo, $puntoVenta, $periodo, $desde, $hasta, $rangoDoble, $desdeDos, $hastaDos, $operacionRango);
    } else {
        $sqlPeriodo = armar_sql_periodo($tipo, $puntoVenta, $periodo, $desde, $hasta, null, null, null, $operacionRango);
    }
    //        echo "<pre>";
    //                print_r($sqlPeriodo);
    //        echo "</pre><br>";
    /*
     * PERIODOS
     */
    $hacerP = mysqli_query($connV, $sqlPeriodo) or die("no puedo hacer la consulta de las ventas" . mysqli_error($connV) . "<pre>" . $sqlPeriodo . "</pre>");
    while ($p = mysqli_fetch_assoc($hacerP)) {
        $arrPer[] = $p;
    }

    /* no hay un solo movimiento en esos periodos*/
    if (empty($arrPer)) {
        return "vacio";
    }

    /* evaluar si es rango doble y pasar las dos variables o nada.*/
    /*Todo*/
    if ($rangoDoble == 1) {
        $sqlTotal = armar_sql($usaIdManual, $codViajante, $tipo, $listarPor, $listaFiltro, $puntoVenta, $periodo, $desde, $hasta, $rangoDoble, $desdeDos, $hastaDos, $operacionRango);
    } else {
        $sqlTotal = armar_sql($usaIdManual, $codViajante, $tipo, $listarPor, $listaFiltro, $puntoVenta, $periodo, $desde, $hasta, null, null, null, $operacionRango);
    }
    //    echo "<pre>";
    //    print_r($sqlTotal);
    //    echo "</pre>";
    $hacer = mysqli_query($connV, $sqlTotal) or die("no puedo hacer la consulta de las ventas" . mysqli_error($connV) . "<pre>" . $sqlTotal . "</pre>");
    while ($r = mysqli_fetch_assoc($hacer)) {
        $k = "";
        foreach ($ithClave as $c) {
            $k .= $r[$c];
        }
        $k .= $r["cod"];
        $arrResultado[$k] = $r;
        //$arrResultado[] = $r;
    }
    // SIN RESULTADO

    if (empty($arrResultado)) {
        return "vacio";
    }

    // validar si es con valor o con array las notas de credito.

    $valorNc = array('articulo', 'proveedor', 'rubro', 'subrubro', 'marca');
    $traigoArrayNc = 0;
    if (in_array($listarPor, $valorNc)) {
        $traigoArrayNc++;
    }
    foreach ($arrFiltros as $cc => $vv) {
        if (in_array($cc, $valorNc)) {
            $traigoArrayNc++;
        }
    }

    //evaluo si nc con array o valor
    //    var_dump($traigoArrayNc++);
    if ($traigoArrayNc == 0) {
        // busco array
        if ($rangoDoble == 1) {
            $sqlTotalNc = armar_sql_nc($codViajante, $tipo, $listarPor, $listaFiltro, null, $periodo, $desde, $hasta, $rangoDoble, $desdeDos, $hastaDos, $operacionRango);
        } else {
            $sqlTotalNc = armar_sql_nc($codViajante, $tipo, $listarPor, $listaFiltro, null, $periodo, $desde, $hasta, null, null, null, $operacionRango);
        }
        //            echo "<pre>";
        //    print_r($sqlTotalNc);
        //    echo "</pre>";
        $hacerNc = mysqli_query($connV, $sqlTotalNc) or die("no puedo hacer la consulta de las ventas" . mysqli_error($connV) . "<pre>" . $sqlTotalNc . "</pre>");
        while ($n = mysqli_fetch_assoc($hacerNc)) {

            $k = "";
            foreach ($ithClave as $c) {
                $k .= $n[$c];
            }
            $k .= $n["cod"];

            $arrayNc[$k] = $n;
            //$arrayNc[] = $n;
        }
    } else {
        //recupero el valor
        $arrayNc = traer_valor_nc($connV, $codViajante, null, $desde, $hasta, $periodo, $rangoDoble, $desdeDos, $hastaDos, $listaFiltro);
    }
    if ($tipo == "un" || $tipo == "peso") {
        $traigoArrayNc++;
    }



    /*
     * SALIDA TABLA HTML
     */
    if ($salida == "html") {
        //        echo "<pre>";
        //        print_r($arrayNc);
        //        echo "</pre>";
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $localidad = setlocale(LC_TIME, NULL); #Guarda localización regional actual
        setlocale(LC_TIME, 'es_AR'); # Localiza en español es_Cenezuela

        $cabecera = array();
        $cabeceraT = array();
        $totalFila = 0;
        $totalGral = 0;
        //$renglon[0][] = "Ventas";

        $mes = 0;
        $aa = 0;

        //listar por lo coloco en el primer titulo
        switch ($listarPor) {
            case 'cliente':
                $titulo[0] = array("titulo" => "Cliente", "span" => 2, "rowspan" => 1);
                break;
            case 'tipocliente':
                $titulo[0] = array("titulo" => "Tipo de Cliente", "span" => 2, "rowspan" => 1);
                break;
            case 'vendedor':
                $titulo[0] = array("titulo" => "Vendedor", "span" => 2, "rowspan" => 1);
                break;
            case 'articulo':
                $titulo[0] = array("titulo" => "Articulo", "span" => 2, "rowspan" => 1);
                break;
            case 'proveedor':
                $titulo[0] = array("titulo" => "Proveedor", "span" => 2, "rowspan" => 1);
                break;
            case 'zona':
                $titulo[0] = array("titulo" => "Zona", "span" => 2, "rowspan" => 1);
                break;
            case 'rubro':
                $titulo[0] = array("titulo" => "Rubro", "span" => 2, "rowspan" => 1);
                break;
            case 'subrubro':
                $titulo[0] = array("titulo" => "Sub Rubro - Rubro", "span" => 2, "rowspan" => 1);
                break;
        }

        // filtrar por y su valor esto lo coloco en el segundo titulo.
        // si el filtro es todos, no tengo que seguir buscando por si se eligio otra opcion
        foreach ($arrFiltros as $key => $fil) {
            //            print_r($key);
            //            print_r($fil);
            switch ($key) {
                case 'cliente':
                    // no puedo volver a agrupar.
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    $tituloFil .= " - Cliente: " . implode(",", $fil);

                    //                    $titulo[] = array("titulo" => "{$periodo} / {$tituloFil}","span" => 2,"rowspan"=>1);
                    break;

                case 'tipocliente':
                    // no puedo volver a agrupar.
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    $tituloFil .= " - Tipo Cliente: " . implode(",", $fil);

                    //                    $titulo[] = array("titulo" => "{$periodo} / {$tituloFil}","span" => 2,"rowspan"=>1);
                    break;
                case 'vendedor':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Vendedor: " . implode(",", $fil);

                    //                    $titulo[] = array("titulo" => "{$periodo} / {$tituloFil}","span" => 2,"rowspan"=>1);
                    break;
                case 'articulo':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Art: " . implode(",", $fil);
                    break;
                case 'proveedor':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Prov: " . implode(",", $fil);
                    break;
                case 'zona':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Zona: " . implode(",", $fil);
                    break;
                case 'rubro':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Rubro: " . implode(",", $fil);
                    break;
                case 'subrubro':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - SubR: " . implode(",", $fil);


                    break;
                case 'marca':
                    // no puedo volver a agrupar.
                    $tituloFil .= " - Marca: " . implode(",", $fil);


                    break;
            }
        }
        // colocar en el titulo si estoy haciendo listado por tipo, monto, peso, unidades
        $tituloTipo = "";
        switch ($tipo) {
            case "un":
                $tituloTipo = "UNIDADES ";
                break;
            case "peso":
                $tituloTipo = "KILOGRAMOS";
                break;
            case "monto":
                $tituloTipo = "VENTAS NETAS";
                break;
        }

        if (empty($filtrarPor)) {
            $titulo[1] = array("titulo" => "{$tituloTipo} x {$periodo} ", "span" => 2, "rowspan" => 1);
        } else {
            $titulo[1] = array("titulo" => "{$tituloTipo} x {$periodo}  /  {$tituloFil}", "span" => 2, "rowspan" => 1);
        }


        /**
         * CABECERAS TH de las tablas.
         */
        //        foreach($arrResultado as $campo){$arrPer
        foreach ($arrPer as $campo) {
            if ($operacionRango == "suma") {
                switch ($periodo) {
                    case "dia":
                        $cabecera[] = array("a" => $campo["aa"], "m" => $campo["mes"], "i" => $campo["dia"] . $campo["mes"], "th" => $campo["dia"]);
                        $cabeceraT[$campo["dia"] . $campo["mes"]] = $campo["dia"];
                        break;
                    case "semana":
                        $cabecera[] = array("a" => $campo["aa"], "m" => $campo["mes"], "i" => $campo["semana"] . $campo["mes"], "th" => $campo["semana"]);
                        $cabeceraT[$campo["semana"] . $campo["mes"]] = $campo["semana"] . " - " . $campo["PrimerDiaSemana"] . " al " . $campo["UltimoDiaSemana"];
                        break;
                    case "mes":
                        $cabecera[] = array("a" => $campo["aa"], "m" => $campo["mes"], "i" => $campo["aa"] . $campo["mes"], "th" => utf8_encode(strftime("%b", mktime(0, 0, 0, $campo["mes"], $campo["dia"], $campo["aa"]))));
                        $cabeceraT[$campo["aa"] . $campo["mes"]] = utf8_encode(strftime("%b", mktime(0, 0, 0, $campo["mes"], $campo["dia"], $campo["aa"])));
                        break;
                }
            } else {
                // operacion suma agrupada y diferencia
                // para ambos casos el valor es el del rango nomas
                $cabecera[] = array("a" => $campo["aa"], "m" => $campo["mes"], "i" => $campo["rango"], "th" => $campo["rangotxt"]);

                $cabeceraT[$campo["rango"]] = $campo["rangotxt"];
            }
            if (isset($campo["total"])) {
                $totalGral = $totalGral  + $campo["total"];
            }
            //$totalGral = $totalGral  + $campo["total"];
        }

        //print_r($totalGral);
        $cabeceraTT = $cabeceraT;
        // este indice ith, es para guardar las claves de combinacion de dia -mes, semana- mes, aa-mes
        $ith = array();

        /* evaluo operacion */
        if ($operacionRango == "suma") {
            switch ($periodo) {
                case "dia":
                    $ith = array("dia", "mes");
                    $cadTitulo = "%b del %Y";
                    //$textoT = utf8_encode(strftime("%B del %Y", mktime(0, 0, 0, $aa, $campo["dia"], $campo["aa"])));                        
                    break;
                case "semana":
                    $ith = array("semana", "mes");
                    $cadTitulo = "%b del %Y";
                    ///$textoT = utf8_encode(strftime("%B del %Y", mktime(0, 0, 0, $aa, $campo["dia"], $campo["aa"])));                        
                    break;
                case "mes":
                    //                $ith = array("mes","aa");
                    $ith = array("aa", "mes");
                    $cadTitulo = "%Y";
                    //$textoT = utf8_encode(strftime("%Y", mktime(0, 0, 0, $campo["mes"], $campo["dia"], $aa)));                        
                    break;
            }
        } else {
            $ith = array("rango");
            $cadTitulo = "Rango";
        }
        /*
         * TITULOS
         */
        // recorro las cabeceras y tengo que obtener el mes o dia u año segun lo que tenga.
        // en el periodo para armar los titulos .
        $tit = 0;
        $aaa = 0;
        $mm = 0;
        $colspan = 0;
        $col = 0;
        $ii = array();
        //        print_r($cabecera);
        foreach ($cabecera as $ca) {
            if ($operacionRango == "suma") {
                if ($tit == 0) {
                    $mm = $ca["m"];
                    $aaa = $ca["a"];
                    $ii[] = $ca["i"];
                    $tit++;
                } else {
                    if ($mm != $ca["m"] || $aaa != $ca["a"]) {
                        $colspan = count($ii);
                        $textoT = utf8_encode(strftime($cadTitulo, mktime(0, 0, 0, $mm, 1, $aaa)));
                        //                    $titulo[$mm.$aaa]= array("titulo" => $textoT,"span" => $colspan,"rowspan"=>1);
                        $titulo[$aaa . $mm] = array("titulo" => $textoT, "span" => $colspan, "rowspan" => 1);
                        $mm = $ca["m"];
                        $aaa = $ca["a"];
                        $ii = array($ca["i"]);
                    }
                    if (!in_array($ca["i"], $ii)) {
                        $ii[] = $ca["i"];
                    }
                }
            } else {
                //Suma agregada y resta

                if ($tit == 0) {
                    $mm = $ca["m"];
                    $aaa = $ca["a"];
                    $ii[] = $ca["i"];
                    $rr = $ca["i"] + 1000;
                    $tit++;
                } else {
                    if ($rr != $ca["i"]) {
                        $colspan = count($ii);
                        $textoT = "Rango " . ($rr - 1000);
                        //                    $titulo[$mm.$aaa]= array("titulo" => $textoT,"span" => $colspan,"rowspan"=>1);
                        $titulo[$rr] = array("titulo" => $textoT, "span" => $colspan, "rowspan" => 1);
                        $mm = $ca["m"];
                        $aaa = $ca["a"];
                        $rr = $ca["i"] + 1000;
                        $ii = array($ca["i"]);
                    }
                    if (!in_array($ca["i"], $ii)) {
                        $ii[] = $ca["i"];
                    }
                }
            }
        }
        $colspan = count($ii);
        $colspan = count($ii);
        if ($operacionRango == "suma") {
            ///suma
            $textoT = utf8_encode(strftime($cadTitulo, mktime(0, 0, 0, $mm, 1, $aaa)));
            $titulo[$aaa . $mm] = array("titulo" => $textoT, "span" => $colspan, "rowspan" => 1);
        } else {
            // suma agregada y diferencia.
            $textoT =  "Rango " . $ca["i"];
            $titulo[$rr] = array("titulo" => $textoT, "span" => $colspan, "rowspan" => 1);
        }


        if ($operacionRango == "suma") {
            $titulo[] = array("titulo" => "SubTotal", "span" => 1, "rowspan" => 2);
        }
        if ($operacionRango == "sumag") {
            $titulo[] = array("titulo" => "SubTotal", "span" => 1, "rowspan" => 2);
        }
        if ($operacionRango == "resta") {
            $titulo[] = array("titulo" => "diferencia", "span" => 1, "rowspan" => 2);
        }
        //        $textoT = utf8_encode(strftime($cadTitulo, mktime(0, 0, 0, $mm, 1, $aaa))); 
        //        $titulo[$aaa.$mm]= array("titulo" => $textoT,"span" => $colspan,"rowspan"=>1);
        //        
        //        if($operacionRango=="suma"){
        //            $titulo[] = array("titulo" => "SubTotal","span" => 1,"rowspan"=>2);
        //        }else{
        //            $titulo[] = array("titulo" => "diferencia","span" => 1,"rowspan"=>2);
        //        }
        //$titulo[] = array("titulo" => "SubTotal","span" => 1,"rowspan"=>2);

        /***Cambio de cabeceras*/

        /*
         * DATOS
         */
        $rng = 0;
        // rubro es el cod de cualquier campo.
        $rubro = "";
        $totaFila = 0;
        if ($grafico == 1) {
            // grafico de barras
            $arrayChart = array(
                "cols" => array(
                    array(
                        "id" => 0,
                        "label" => "Periodo {$periodo}",
                        "type" => "string"
                    ),

                ),
                "rows" => array()
            );
            // grafico de torta
            $arrayChartT = array(
                "cols" => array(
                    array(
                        "id" => 0,
                        "label" => "Rubro",
                        "type" => "string"
                    ),
                    array(
                        "id" => 1,
                        "label" => "Ventas",
                        "type" => "number"
                    ),
                    array(
                        "id" => null,
                        "label" => "",
                        "pattern" => "",
                        "type" => "string",
                        "p" => array("role" => "style"),
                    ),

                ),
                "rows" => array()
            );
        }
        $arrVentas = $arrResultado;
        if ($traigoArrayNc == 0) {
            $arrVentas = fusion_ventas_nc($arrResultado, $arrayNc);
        }
        // dato de tabla html   
        //echo "<pre>";
        //        echo "ith<pre>";
        //        print_r($ith);
        //        echo "</pre><br>";
        foreach ($arrVentas as $idC => $campo) {
            //             echo " renglon <pre>";
            //             print_r($campo);
            //             echo "</pre>";

            $importeNc = 0;
            // buscar la notas de credito desde el array solo si correspondde.
            if (isset($ith[1])) {
                $icampo = intval($campo[$ith[0]] . $campo[$ith[1]]);
            } else {
                $icampo = intval($campo[$ith[0]]);
            }

            if ($rubro === "") {
                $rubro = $campo["cod"];
                //tabla
                $renglon[$rubro][] = $campo["nom"];

                //grafico
                $arrayChart["cols"][] = array(
                    "id" => $rubro,
                    "label" => $campo["nom"],
                    "type" => "number"
                );
                //recorro las cabeceras y coloco lo que hace falta
                foreach ($cabeceraT as $key => $ca) {
                    if ($icampo === $key) {
                        //controlo si hay nc para buscar datos

                        if ($traigoArrayNc == 0) {
                            //                            $arrValor = array("cod"=> $rubro, 
                            //                                $ith[0]=> $campo[$ith[0]],
                            //                                $ith[1] => $campo[$ith[1]]
                            //                            );
                            //                            $keyNc = multidimensional_search($arrayNc, $arrValor);
                            //                            $importeNc = $arrayNc[$keyNc]["importe"];
                            if (isset($arrayNc[$idC]["total"])) {
                                //                                $keyNc = multidimensional_search($arrayNc, $arrValor);
                                $importeNc = $arrayNc[$idC]["total"];
                            } else {
                                $importeNc = 0;
                            }
                        }

                        $renglon[$rubro][$icampo] = $campo["total"] + $importeNc;

                        // localizar en el array nc las nc.
                    } else {

                        $renglon[$rubro][$key] = 0;
                    }
                }
                $totalFila += $campo["total"] + $importeNc;
            } else {
                if ($rubro != $campo["cod"]) {
                    $renglon[$rubro]["subt"] = $totalFila;
                    $rubro = $campo["cod"];
                    $renglon[$rubro][] = $campo["nom"];
                    //                     $renglon[$rubro][]= getSubString($campo["nom"]);
                    $arrayChart["cols"][] = array(
                        "id" => $rubro,
                        "label" => $campo["nom"],
                        "type" => "number"
                    );
                    // cabeceras
                    foreach ($cabeceraT as $key => $ca) {
                        if ($icampo === $key) {

                            if ($traigoArrayNc == 0) {
                                //                                $arrValor = array("cod"=> $rubro, $ith[0]=> $campo[$ith[0]],$ith[1] => $campo[$ith[1]]);
                                if (isset($arrayNc[$idC]["total"])) {
                                    //                                $keyNc = multidimensional_search($arrayNc, $arrValor);
                                    $importeNc = $arrayNc[$idC]["total"];
                                } else {
                                    $importeNc = 0;
                                }
                            }

                            $renglon[$rubro][$icampo] = $campo["total"] + $importeNc;
                        } else {
                            $renglon[$rubro][$key] = 0;
                        }
                    }

                    $totalFila = $campo["total"] + $importeNc;
                } else {
                    // cabeceras
                    foreach ($cabeceraT as $key => $ca) {
                        if ($icampo === $key) {
                            $importeNc = 0;
                            if ($traigoArrayNc == 0) {
                                //                                $arrValor = array("cod"=> $rubro, 
                                //                                    $ith[0]=> $campo[$ith[0]],
                                //                                    $ith[1] => $campo[$ith[1]]);

                                if (isset($arrayNc[$idC]["total"])) {
                                    //                                $keyNc = multidimensional_search($arrayNc, $arrValor);
                                    $importeNc = $arrayNc[$idC]["total"];
                                } else {
                                    $importeNc = 0;
                                }
                            }

                            $renglon[$rubro][$icampo] = $campo["total"] + $importeNc;
                        }
                    }
                    $totalFila += $campo["total"] + $importeNc;
                }
            }
        }

        //        $renglon[$rubro]["subt"]= number_format($totalFila,2,".","");
        $renglon[$rubro]["subt"] = $totalFila;

        /*doble rango con suma o diferencia.*/
        if ($operacionRango == "resta") {
            foreach ($renglon as $k => $r) {
                $suma = 0;
                $vuelta = 0;
                foreach ($r as $kk => $valor) {
                    //                    echo "<pre>";
                    //                    print_r($r);
                    //                    echo "</pre><br>";
                    if ($kk != 0 && $kk != "subt") {
                        if ($vuelta == 0) {
                            $suma = $suma - $valor;
                        } else {
                            $suma = $suma + $valor;
                        }
                        $vuelta++;
                    }
                }
                $renglon[$k]["subt"] = $suma;
            }
        }

        if ($grafico == 1) {
            /*
             * Grafico
             * **/
            $arrColor = array(
                1 => array(
                    "rgba(0, 172, 212, 0.5)",
                    "rgba(0, 172, 212, 0.8)",
                    "rgba(0, 172, 212, 0.75)",
                    "rgba(0, 172, 212, 1)",
                    "#00A4CC"
                ),

                2 => array(
                    "rgba(248, 147, 31, 0.5)",
                    "rgba(248, 147, 31, 0.8)",
                    "rgba(248, 147, 31, 0.75)",
                    "rgba(248, 147, 31, 1)",
                    "#109618"
                ),
                3 => array(
                    "rgba(34, 181, 75, 0.5)",
                    "rgba(34, 181, 75, 0.8)",
                    "rgba(34, 181, 75, 0.75)",
                    "rgba(34, 181, 75, 1)",
                    //"lightskyblue"
                    "#FF9900"
                )
            );

            $optionChart = array(
                "title" => "Ventas netas {$listarPor} - {$periodo} / {$tituloFil}",
                "width" => 700,
                "height" => 400,
                "hAxis" => array("title" => "Período " . $periodo)
            );
            $optionChartT = array(
                "title" => "Ventas netas {$listarPor} - {$periodo} / {$tituloFil}",
                "is3D" => true,
                "width" => 700,
                "height" => 400
            );

            //$linea=array();
            foreach ($cabeceraTT as $key => $c) {
                $linea = array();
                // recorro todas las cabeceras y me tiene que hacer una linea nueva
                $linea[] = array("v" => $c);
                foreach ($renglon as $clave => $reng) {
                    // recorro los renglones y prgunto si hay cabecera
                    foreach ($reng as $keyR => $r) {
                        //print_r($keyR);
                        if ($key == $keyR) {
                            $valor = $r;
                            $valorF = "$" . number_format($r, 2, ",", ".");
                            $linea[] = array("v" => $valor, "f" => $valorF);
                        }
                    }
                    // coloco el color de la series
                    $optionChart["series"][$clave - 1] = array("color" => $arrColor[$clave][4]);
                    $optionChartT["slices"][$clave - 1] = array("color" => $arrColor[$clave][4]);
                }
                //$style= "opacity: 0.2";
                $arrayChart["rows"][] = array("c" => $linea);
            }


            //series:{0:{color:'green'},1:{color:'yellow'}}

            foreach ($renglon as $key => $reng) {
                $nombre = $reng[0];
                $valorT = $reng["subt"] + 0;
                unset($reng[0]);
                unset($reng[$cuantos]);

                $cabeza = $nombre;
                $valor = $valorT;
                $valorF = "$" . number_format($valorT, 2, ",", ".");
                $style = "color: " . $arrColor[$key][4];
                $arrayChartT["rows"][] = array(
                    "c" => array(
                        array("v" => $cabeza),
                        array("v" => $valor, "f" => $valorF),
                        array("v" => $style)
                    )
                );
            }
        } /* Fin de graficos*/


        //        print_r($arrayChartT);
        //        echo "<pre>";
        //        print_r($titulo);
        //        echo "<br>";
        //        print_r($cabeceraTT);
        //        echo "<br>";
        //        print_r($renglon);
        //        echo "</pre>";
        //        /**Envio Final*/
        if ($traigoArrayNc == 0 || $tipo == "un" || $tipo == "peso") {
            if ($grafico == 1) {
                $arrayFinal = array(
                    "titulos" => $titulo,
                    "cabeceras" => $cabeceraTT,
                    "data" => $renglon,
                    "goption" => $optionChart,
                    "gdata" => $arrayChart,
                    "goptionT" => $optionChartT,
                    "gdataT" => $arrayChartT
                );
            } else {
                $arrayFinal = array(
                    "titulos" => $titulo,
                    "cabeceras" => $cabeceraTT,
                    "data" => $renglon
                );
            }
        } else {
            if ($grafico == 1) {
                $arrayFinal = array(
                    "titulos" => $titulo,
                    "cabeceras" => $cabeceraTT,
                    "data" => $renglon,
                    "goption" => $optionChart,
                    "gdata" => $arrayChart,
                    "goptionT" => $optionChartT,
                    "gdataT" => $arrayChartT,
                    "impNC" => $arrayNc
                );
            } else {
                $arrayFinal = array(
                    "titulos" => $titulo,
                    "cabeceras" => $cabeceraTT,
                    "data" => $renglon,
                    "impNC" => $arrayNc
                );
            }
        }
        //        echo "<pre>";
        //        print_r($arrayFinal);
        //        echo "</pre>";
        return json_encode($arrayFinal);
    }
}

function fusion_ventas_nc($arrayVentas, $arrayNC)
{
    //$arrNcAd=array(); 
    $arrNcAd = array_diff_key($arrayNC, $arrayVentas);
    //    echo "Diferencia;=><pre>";
    //    print_r($arrNcAd);
    //    echo "</pre><br>";

    if (!empty($arrNcAd)) {
        foreach ($arrNcAd as $clave => $dif) {
            $arrNcAd[$clave]["total"] = 0;

            $arrayVentas[$clave] = $arrNcAd[$clave];
        }
    }
    return $arrayVentas;
}


# Manejador de funciones VENTAS NETAS

if (isset($_REQUEST['ajax'])) {

    //    echo "<pre>";
    //    print_r($_REQUEST);
    //    echo "</pre>";
    if (isset($_REQUEST["tabla"])) {
        $queTabla = $_REQUEST["tabla"];
    } else {
        $queTabla = null;
    }
    $usaIdManual = $_SESSION["usa_id_manual"];
    $listarPor = null;
    $filtrarPor = null;
    $consulta = "";

    $objVendedor = $_SESSION['vendedor'];
    $codViajante = $objVendedor->CodViajante;
    $queInforme     = $_REQUEST["queInforme"];

    // tabla de seleccion de filtros.

    if ($queInforme !== "seleccion") {
        /* 
            * filtro por dia , semana , mes 
            */
        $TipoResumen    = $_REQUEST["tipoResumen"];

        $queVista       = $_REQUEST["queSalida"];

        $salida       = $_REQUEST["queSalida"];

        $rangoDoble = $_REQUEST["rangoDoble"];

        //$fed = explode("-",$_REQUEST['fechaDesde']);
        $fed = $_REQUEST['fechaDesde'];
        //$fechaDesde = $fed[2].'/'. $fed[1].'/'.$fed[0];
        //$feh = explode("-",$_REQUEST['fechaHasta']);
        $feh = $_REQUEST['fechaHasta'];
        //$fechaHasta = $feh[2].'/'. $feh[1].'/'.$feh[0];
        $fechaDesde = $fed;
        $fechaHasta = $feh;
        $operacionRango = $_REQUEST["opRango"];
        if ($rangoDoble == 1) {
            $fedDos = $_REQUEST['fechaDesdeDos'];
            $fehDos = $_REQUEST['fechaHastaDos'];
            $fechaDesdeDos = $fedDos;
            $fechaHastaDos = $fehDos;
        } else {
            $fechaDesdeDos = null;
            $fechaHastaDos = null;
            $operacionRango = "suma";
        }
        if ($_REQUEST["filtrarPor"]) {
            $filtrarPor = $_REQUEST["filtrarPor"];
        }
        if (isset($_REQUEST["listarPor"])) {
            $listarPor = $_REQUEST["listarPor"];
        }



        //$rangoDoble=0;
        $tipo =  $_REQUEST["tipo"];
        $grafico = 0;
        $periodo = $TipoResumen;
        $puntoVenta = null;
    }
    /*
         * Seleccionando el informe a buscar
         */
    $resultado = "nada";
    switch ($queInforme) {
        case "vt":
            //ventas totales
            //            $resultado = ventas_totales($listarPor,$filtrarPor,$codViajante, $fechaDesde, $fechaHasta, $TipoResumen, $queVista);
            $resultado = ventas_totales_todos($usaIdManual, $connV, $codViajante, $tipo, $listarPor, $filtrarPor, $puntoVenta, $fechaDesde, $fechaHasta, $periodo, $salida, $grafico, $rangoDoble, $fechaDesdeDos, $fechaHastaDos, $operacionRango);
            break;
        case "vtr":
            // ventas totales por rubro
            $resultado = ventas_totales_rubro($connV, $codViajante, $fechaDesde, $fechaHasta, $rangoDoble, $fechaDesdeDos, $fechaHastaDos, $operacionRango, $TipoResumen, $queVista, $tipo, $filtrarPor);
            break;
        case "vtrp":
            // ventas totales por rubro y proveedor
            $resultado = ventas_totales_rubro_proveedor($usaIdManual, $connV, $codViajante, $fechaDesde, $fechaHasta, $rangoDoble, $fechaDesdeDos, $fechaHastaDos, $operacionRango, $TipoResumen, $queVista, $tipo, $filtrarPor);
            break;
        case "seleccion":
            $resultado = listado_seleccion($queTabla, $codViajante, $connV);
            // debo obtener del filtro tambien el nombre de lo que sea asi los voy colocando en la cabecera de la tabla.
            break;
    }
    echo $resultado;
}
