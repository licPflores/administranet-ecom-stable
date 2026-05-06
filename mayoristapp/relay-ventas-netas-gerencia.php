<?php
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_startup_errors', 1);
ini_set('display_errors', '1');
//header('Content-type: text/html; charset=utf-8');
/*
 * Todo...
 * armar funcion para que mustre el html,
 * armar funcion para buscar el importe de notas de credito.
 * separar mejor el codigo para mejor mantenimiento.
 */
// funcion para saber si tengo que volver a generar la sesion de nuevo.

require_once 'sesion.inc.php';
/*
 * Ventas netas gerenciales.
 * funciones para armar todos los informes de ventas y los filtros
 * implementar la busqueda rapida.
 * 
 */

// debuguare sql
define('debugSql',false);

if (isset($_GET['ajax'])) {

    $queInforme     = $_GET["queInforme"];

    if ($queInforme == "seleccion") {
        if (isset($_GET["tabla"])) {
            $queTabla = $_GET["tabla"];
        } else {
            $queTabla = null;
        }
        $consulta = "";
        $arrVendCargo = $_SESSION["vendedor_a_cargo"];
    } else {

        /*  El resto de los informes.
        * filtro por dia , semana , mes 
        */
        $rangoDoble = null;
        $decimales = null;
        $TipoResumen    = $_GET["tipoResumen"];

        $salida       = $_GET["queSalida"];
        if (isset($_GET["tabla"])) {
            $queTabla = $_GET["tabla"];
        } else {
            $queTabla = null;
        }
        $consulta = "";
        $objVendedor = $_SESSION['vendedor'];
        $codViajante = $objVendedor->CodViajante;
        if (isset($_SESSION["pemiso_supervisor_venta"])) {
            $supervisorVenta = $_SESSION["pemiso_supervisor_venta"];
        } else {
            $supervisorVenta = null;
        }

        $arrVendCargo = $_SESSION["vendedor_a_cargo"];
        // me fijo si agrupo por dia , semana o

        //    busco por campo ademas del estado si estuviere.
        //    @numeroComp: buscar por el numero de pedido
        //    @fechaDesde : desde el periodo
        //    @fechaHasta : hasta del periodo
        if (isset($_REQUEST["rangoDoble"])) {
            $rangoDoble = $_GET["rangoDoble"];
        }
        //$fed = explode("-",$_REQUEST['fechaDesde']);
        $fed = $_GET['fechaDesde'];

        //$fechaDesde = $fed[2].'/'. $fed[1].'/'.$fed[0];
        //$feh = explode("-",$_REQUEST['fechaHasta']);
        $feh = $_GET['fechaHasta'];

        //$fechaHasta = $feh[2].'/'. $feh[1].'/'.$feh[0];
        $fechaDesde = $fed;
        $fechaHasta = $feh;
        // rango doble
        if ($rangoDoble == 1) {
            $fedDos = $_GET['fechaDesdeDos'];
            $fehDos = $_GET['fechaHastaDos'];
            $fechaDesdeDos = $fedDos;
            $fechaHastaDos = $fehDos;
            $operacionRango = $_GET["opRango"];
        } else {
            $fechaDesdeDos = null;
            $fechaHastaDos = null;
            if (isset($_GET["opRango"])) {
                $operacionRango = $_GET["opRango"];
            } else {
                $operacionRango = null;
            }
        }
        if (isset($_GET["decimales"])) {
            $decimales = $_GET["decimales"];
        }
        $periodo = $TipoResumen;
        $tipo = null;
        if (isset($_GET["tipo"])) {
            $tipo =  $_GET["tipo"];
        }
        $listarPor = $_GET["listarPor"];
        $filtrarPor = $_GET["filtrarPor"];
        //    $valorFiltro = $_REQUEST["valorFiltro"];
        $puntoVenta = $_GET["puntoVenta"];
        $grafico = null;
        if (isset($_GET["grafico"])) {
            $grafico = $_GET["grafico"];
        }

        if (isset($_GET["tipoInflacion"])) {
            $tipoInflacion = $_GET["tipoInflacion"];
        } else {
            $tipoInflacion = null;
        }
        // articulos ensamblados en la venta
        if (isset($_GET["artEnsambVenta"])) {
            $artEnsamblado = $_GET["artEnsambVenta"];
        } else {
            $artEnsamblado = null;
        }

           
    }
    
    /*
     * Seleccionando el informe a buscar
     */
    $resultado = "nada";

    switch ($queInforme) {
        case "vt":
            /* VENTAS */
            /////// agregar el filtro de clientes, y lista por clientes o articulos. 
            //poder hacer las ventas e incluir la exportacion de datos
            //
            $resultado = ventas_totales_todos($connV, $tipo, $listarPor, $filtrarPor, $puntoVenta, $fechaDesde, $fechaHasta, $periodo, $salida, $grafico, $rangoDoble, $fechaDesdeDos, $fechaHastaDos, $operacionRango, $decimales, $artEnsamblado);
            break;
        case "ut":
            /* UTILIDAD*/
            $resultado = utilidades_totales_todos($connV, $tipo, $listarPor, $filtrarPor, $puntoVenta, $fechaDesde, $fechaHasta, $periodo, $salida, $grafico, $rangoDoble, $fechaDesdeDos, $fechaHastaDos, $operacionRango);
            break;
        case "uti":
            /* UTILIDAD x  INFLACION*/
            $resultado = utilidades_totales_todos_inflacion($connV, $tipo, $listarPor, $filtrarPor, $puntoVenta, $fechaDesde, $fechaHasta, $periodo, $salida, $grafico, $rangoDoble, $fechaDesdeDos, $fechaHastaDos, $operacionRango, $tipoInflacion);
            break;
        case "seleccion":
            $resultado = listado_seleccion($connV, $queTabla, $arrVendCargo);
            // debo obtener del filtro tambien el nombre de lo que sea asi los voy colocando en la cabecera de la tabla.
            break;
    }
    echo $resultado;
}

/*
 * function: listado_seleccion
 * desc:    busca el total de la tabla pasado como parametro y devuelve un
 * listado de options para llenar una lista
 * @tabla: valor para saber de que tabla debo buscar los options.
 * @salida: es un texto con options.
 */
function listado_seleccion($connV, $tabla = null, $arrVendCargo = null)
{
    $sql = "";
    $lista = "";
    $usaIdManual = $_SESSION["usa_id_manual"];
    $vendedor = (array) $_SESSION['vendedor'];
    $permisoGerencial = $_SESSION['inf_gerenciales'];// Si/No
    $supervisorVenta = $_SESSION['supervisor_venta'];// Si/No
    $verTodosClientes = $_SESSION['todos_clientes']; // Si/ No

    switch ($tabla) {
        case "cliente":
            // no puedo ver a todos los clientes o no tengo el permiso para ver informes gerenciales 
            // si tengo el permiso no debo filtrar por nada de gerenciales pues soy gerente.
            $listaVendedor = "";
            // no puedo ver todos los clientes, y no tengo permiso gerencial.
            if($verTodosClientes =='No' || $permisoGerencial=='No'){
                // no puedo ver todos los clientes o bien no tengo permiso gerencial.
                if($supervisorVenta=='Si'){
                    // soy supervisor con gente a cargo.
                    if ($arrVendCargo != null && !empty($arrVendCargo)) {
                        $listaVendedor = " AND cliente.CodViajante IN (" . implode(',', $arrVendCargo) . ")";
                    }

                    // soy supervisor pero no tengo gente a cargo
                    if ($arrVendCargo == null || empty($arrVendCargo)) {
                        $arrVendCargo = array($vendedor['CodViajante']);
                        $listaVendedor = " AND cliente.CodViajante IN (" . implode(',',$arrVendCargo) . ")";
                    }
                }
                // no soy supervisor de venta, no tengo permiso gerencial ni puedo ver todos los clientes solo lo mio.
                if($supervisorVenta=='No'){
                    $arrVendCargo = array($vendedor['CodViajante']);
                        $listaVendedor = " AND cliente.CodViajante IN (" . implode(',',$arrVendCargo) . ")";
                }
            }

            // tengo permiso gerencial pero soy supervidor, entonces solo puedo ver lo de mis vendedores.
            if($permisoGerencial=='Si'){
                if($supervisorVenta=='Si'){
                    // soy supervisor con gente a cargo.
                    if ($arrVendCargo != null && !empty($arrVendCargo)) {
                        $listaVendedor = " AND cliente.CodViajante IN (" . implode(',', $arrVendCargo) . ")";
                    }

                    // soy supervisor pero no tengo gente a cargo
                    if ($arrVendCargo == null || empty($arrVendCargo)) {
                        $arrVendCargo = array($vendedor['CodViajante']);
                        $listaVendedor = " AND cliente.CodViajante IN (" . implode(',',$arrVendCargo) . ")";
                    }
                }
            }



            if ($usaIdManual == "Si") {
                $sql = "SELECT cliente.id_manual_cli AS valor,"
                    . " CONCAT(cliente.nombre_cliente,' (cod:',cliente.id_manual_cli,')') AS texto "
                    . " FROM cliente"
                    . " WHERE cliente.Estado='Activo'"
                    . $listaVendedor
                    . " ORDER BY texto ASC";
            } else {
                $sql = "SELECT cliente.Codigo AS valor,"
                    . " CONCAT(cliente.nombre_cliente,' (cod:',cliente.Codigo,')') AS texto "
                    . " FROM cliente"
                    . " WHERE cliente.Estado='Activo'"
                    . $listaVendedor
                    . " ORDER BY texto ASC";
            }
            break;
        case "tipocliente":
            $sql = "SELECT tipo_cliente.IDTipoCliente AS valor,"
                . " CONCAT(tipo_cliente.NombreTipoCliente,' (cod:',tipo_cliente.IDTipoCliente,')') AS texto "
                . " FROM tipo_cliente"
                . " WHERE tipo_cliente.Anulado='No'"
                . " ORDER BY texto ASC";
            break;
        case "articulo":
            if ($usaIdManual == "Si") {
                $sql = "SELECT articulo.id_manual AS valor,"
                    . " CONCAT(articulo.NombreArticulo,' (cod:',articulo.id_manual,')') AS texto "
                    . " FROM articulo"
                    . " WHERE articulo.Discontinuo='No' AND articulo.id_manual<>''"
                    . " ORDER BY texto ASC";
            } else {
                $sql = "SELECT articulo.IDArt AS valor,"
                    . " CONCAT(articulo.NombreArticulo,' (cod:',articulo.IDArt,')') AS texto "
                    . " FROM articulo"
                    . " WHERE articulo.Discontinuo='No'"
                    . " ORDER BY texto ASC";
            }
            break;
        case "vendedor":
            $listaVendedor = "";
            
                // filtro de vendedor 
                if($supervisorVenta=='Si'){
                    // soy supervisor con gente a cargo.
                    if ($arrVendCargo != null && !empty($arrVendCargo)) {
                        $listaVendedor = " AND viajantes.CodViajante IN (" . implode(',', $arrVendCargo) . ")";
                    }

                    // soy supervisor pero no tengo gente a cargo
                    if ($arrVendCargo == null || empty($arrVendCargo)) {
                        $arrVendCargo = array($vendedor['CodViajante']);
                        $listaVendedor = " AND viajantes.CodViajante IN (" . implode(',',$arrVendCargo) . ")";
                    }
                }
                // no soy supervisor de venta, no tengo permiso gerencial ni puedo ver todos los clientes solo lo mio.
                if($supervisorVenta=='No' && $permisoGerencial=='No'){
                    $arrVendCargo = array($vendedor['CodViajante']);
                        $listaVendedor = " AND viajantes.CodViajante IN (" . implode(',',$arrVendCargo) . ")";
                }
            

            


            $sql = "SELECT viajantes.CodViajante AS valor,"
                . " CONCAT(viajantes.Nombre,' (cod:',viajantes.CodViajante,')') AS texto "
                . " FROM viajantes"
                . " WHERE viajantes.Anulado='No'"
                . $listaVendedor
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
                . " CONCAT(rubro.NombreRubro,' (cod:',rubro.CodigoRubro,')') AS texto "
                . " FROM rubro"
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
        case "usuario":
            $sql = "SELECT usuarios.id_usuario AS valor,"
                    . " CONCAT(usuarios.cod_usuario,' (cod:',usuarios.id_usuario,')') AS texto "
                    . " FROM usuarios"
                    . " WHERE usuarios.baja_usuario='No'"
                    . " ORDER BY texto ASC";
            break;    
    }
    $hacer = mysqli_query($connV, $sql) or die("no puedo ejecutar el listado " . mysqli_error($connV) . '<pre>' . $sql . '</pre>');
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

/**
 * # VENTAS NETAS funcion de ventas netas por todos los filtros
 * hay una agrupacion primaria , una secundaria y un periodo para cada combinacion.
 * usar funciones seguramente para armar el cuerpo del informe sin pasar por el grafico
 * que no sabemos si se puede hacer.
 * en caso de que seleccione el filtro Todos, no se usa el where pero si son iguales el filtro con 
 * el campo de la lista, solo uso un agrupamiento y si trae un valor uso el where.
 * hacer conversiones 
 */

function ventas_totales_todos(
    $connV,
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
    $operacionRango = null,
    $decimales = null,
    $artEnsamblado = null
) {

    $pvLista = explode("||", $puntoVenta);
    $listaFiltro = explode("||", $filtrarPor);
    $ithClave = array();
    $usoBultoPromedio = $_SESSION["uso_bulto_promedio"];
    // tengo que pasar el array con los vendedores en caso que sea venta por Vendedor.


    foreach ($pvLista as $pv) {
        $arrPuntoVenta = explode("|", $pv);
        //        echo "PV:><pre>";
        //        print_r($arrPuntoVenta);
        //        echo "</pre>";

        if (isset($arrPuntoVenta[1]) && $arrPuntoVenta[1] == "Todos") {
            $puntoVentaId[] = "todos";
        } else {
            if ($arrPuntoVenta[0] != "") {
                $puntoVentaId[] = $arrPuntoVenta[0];
            }
        }
        if (isset($arrPuntoVenta[1])) {
            $puntoVentaTxt[] = $arrPuntoVenta[1];
        }
    }
    $tituloFil = " PV: " . implode(",", $puntoVentaTxt);

    // filtro valores

    $arrFiltros = array();
    if (!empty($listaFiltro) && $listaFiltro[0] != "") {

        foreach ($listaFiltro as $valorFiltro) {

            $datoFiltro = explode("|", $valorFiltro);
            if (isset($datoFiltro[2])) {
                $arrFiltros[$datoFiltro[0]][] = $datoFiltro[2];
            }
        }
    }
    // return $sqlTotal;
    $arrResultado = array();
    $arrResultadoNc = array();
    $arrPer = array();
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
    /*Traer el sql con el periodo a evaluar para que no se pierda nada*/
    // PERIODO 

    if ($rangoDoble == 1) {
        $sqlPeriodo = armar_sql_periodo($tipo, $puntoVenta, $periodo, $desde, $hasta, $rangoDoble, $desdeDos, $hastaDos, $operacionRango);
    } else {
        $sqlPeriodo = armar_sql_periodo($tipo, $puntoVenta, $periodo, $desde, $hasta, null, null, null, $operacionRango);
    }
        //    echo "<pre>";
        //            print_r($sqlPeriodo);
        //    echo "</pre><br>";
        if(debugSql==true){
            $sqlLog =file_put_contents('sql_ventas_netas'.date('Ymd').'.sql','Periodo:'.PHP_EOL.$sqlPeriodo.PHP_EOL,FILE_APPEND);
        }
    /*
     * PERIODOS
     */
    $hacerP = mysqli_query($connV, $sqlPeriodo) or die("no puedo hacer la consulta de las ventas x periodo" . mysqli_error($connV) . " <br><pre>" . $sqlPeriodo . "</pre>");
    while ($p = mysqli_fetch_assoc($hacerP)) {
        $arrPer[] = $p;
    }

    /* no hay un solo movimiento en esos periodos*/
    if (empty($arrPer)) {
        return "vacio";
    }


    //    print_r($arrPer);
    // permiso para mostrar los domicilio al lado del cliente en la ibero.
    if (isset($_SESSION["usa_domicilio_cliente_informes"]) && $_SESSION["usa_domicilio_cliente_informes"] == 1) {
        $domCliente = $_SESSION["usa_domicilio_cliente_informes"];
    } else {
        $domCliente = null;
    }



    /* evaluar si es rango doble y pasar las dos variables o nada.*/
    /** 
     *  # SQL DE VENTAS 
     */
    if ($rangoDoble == 1) {
        $sqlTotal = armar_sql($tipo, $listarPor, $listaFiltro, $puntoVentaId, $periodo, $desde, $hasta, $rangoDoble, $desdeDos, $hastaDos, $operacionRango, $domCliente, $artEnsamblado);
    } else {
        $sqlTotal = armar_sql($tipo, $listarPor, $listaFiltro, $puntoVentaId, $periodo, $desde, $hasta, null, null, null, $operacionRango, $domCliente, $artEnsamblado);
    }
    //  echo "VTAS:=><pre>".PHP_EOL;
    //          print_r($sqlTotal).PHP_EOL;
    //  echo "</pre>".PHP_EOL; 
    if(debugSql==true){
        $sqlLog =file_put_contents('log/sql_ventas_netas'.date('Ymd').'.sql','Ventas:'.PHP_EOL.$sqlTotal.PHP_EOL,FILE_APPEND);
    }
    $hacer = mysqli_query($connV, $sqlTotal) or die("no puedo hacer la consulta de las ventas completas" . mysqli_error($connV) . " <br><pre>" . $sqlTotal . "</pre>");

    while ($r = mysqli_fetch_assoc($hacer)) {
        $k = "";
        foreach ($ithClave as $c) {
            $k .= $r[$c];
        }
        $k .= $r["cod"];
        $arrResultado[$k] = $r;
    }

    //    echo "Resultado::<pre>";
    //    print_r($arrResultado);
    //    echo "</pre><br>";
    // SIN RESULTADO
    if (empty($arrResultado)) {
        return "vacio";
    }

    // validar si es con valor o con array las notas de credito.

    $valorNc = array('articulo', 'proveedor', 'rubro', 'subrubro', 'categoria', 'marca');
    $traigoArrayNc = 0;
    if (in_array($listarPor, $valorNc)) {
        $traigoArrayNc++;
    }

    foreach ($arrFiltros as $cc => $vv) {
        if (in_array($cc, $valorNc)) {
            $traigoArrayNc++;
        }
    }

    /**
     * # NOTAS DE CREDITO
     */
    if ($traigoArrayNc == 0) {
        // busco array notas de credito por descuento en facturas
        if ($rangoDoble == 1) {
            $sqlTotalNc = armar_sql_nc($tipo, $listarPor, $listaFiltro, $puntoVentaId, $periodo, $desde, $hasta, $rangoDoble, $desdeDos, $hastaDos, $operacionRango);
        } else {
            $sqlTotalNc = armar_sql_nc($tipo, $listarPor, $listaFiltro, $puntoVentaId, $periodo, $desde, $hasta, null, null, null, $operacionRango);
        }
        //        echo "<br>NCPeriodo:=><pre>";
        //        print_r($sqlTotalNc);
        //        echo "</pre><br>";
        if(debugSql==true){
            $sqlLog =file_put_contents('log/sql_ventas_netas'.date('Ymd').'.sql','Notas de credito:'.PHP_EOL.$sqlTotalNc.PHP_EOL,FILE_APPEND);
        }
        $hacerNc = mysqli_query($connV, $sqlTotalNc) or die("no puedo hacer la consulta de las notas de credito" . mysqli_error($connV) . "<pre>" . $sqlTotalNc . "</pre>");

        while ($n = mysqli_fetch_assoc($hacerNc)) {
            $k = "";
            foreach ($ithClave as $c) {
                $k .= $n[$c];
            }
            $k .= $n["cod"];

            $arrayNc[$k] = $n;
        }

        

        //        echo "Resultado NC::<pre>";
        //    print_r($arrayNc);
        //    echo "</pre><br>";
               
    } else {
        // * # NOTAS DE CREDITO POR VALOR
        
        if ($rangoDoble == 1) {

            $arrayNc = traer_valor_nc($connV, $puntoVentaId, $desde, $hasta, $periodo, $rangoDoble, $desdeDos, $hastaDos, $operacionRango, $listaFiltro);
        } else {
            $arrayNc = traer_valor_nc($connV, $puntoVentaId, $desde, $hasta, $periodo, $rangoDoble, null, null, $operacionRango, $listaFiltro);
        }
        //        echo "<br>NCValor:=><pre>";
        //        print_r($sqlTotalNc);
        //        echo "</pre><br>";

        
    }

    if ($tipo == "un" || $tipo == "peso" || $tipo == "pieza") {
        $traigoArrayNc++;
    }
    //print_r($tipo);
    // temporal solo para la prueba.
    //$arrayNc = traer_valor_nc($puntoVentaId, $desde, $hasta,$periodo);       
    // notas de credito nc x articulo

    /*
     * SALIDA TABLA HTML
     */
    if ($salida == "html") {
        //        echo "<pre>";
        //        print_r($arrayNc);
        //        echo "</pre>";
        /* pasando todo a funciones para ser mas ordenado*/
        $arrCabeceras = getCabeceraVentas($arrPer, $periodo, $listarPor, $arrFiltros, $tipo, $arrResultado, $operacionRango);
        //        echo "<pre>";
        //        print_r($arrCabeceras);
        //        echo "</pre>";

        $titulo = $arrCabeceras["titulo"];
        $cabeceraTT = $arrCabeceras["cabecera"];
        $ith = $arrCabeceras["ith"];
        /***Cambio de cabeceras*/
        /* TRANSFORMAR ARRP*/
        //        print_r($ith);

        $arrPListo = array();
        foreach ($arrPer as $per) {

            //            echo "ith:<pre>";
            //            print_r($ith);
            //            echo "</pre>";
            if (isset($ith[0])) {
                $valorClave = $per[$ith[0]];
            }
            if (isset($ith[1])) {
                $valorClave .= $per[$ith[1]];
            }
            $valorClave = intval($valorClave);
            $arrPListo[$valorClave] = $per;
        }
        //        print_r($arrPListo);
        /*
         * DATOS
         */

        //echo "<pre>";
        //        print_r($arrResultado);
        //        echo "</pre>";
        $arrVentas = $arrResultado;
        if ($traigoArrayNc == 0) {
            //            echo "dentro del fusionado";
            $arrVentas = fusion_ventas_nc($arrResultado, $arrayNc);
            // fusion de las notas de debito.
            // $arrVentas = fusion_ventas_nd($arrResultado,$arrayNDeb);
            //            echo "arra fusionado::<pre>";
            //            print_r($arrVentas);
            //            echo "</pre><br>";
        }
        $renglon = getDatosVentas($arrPListo, $arrVentas, $ith, $cabeceraTT, $arrayNc, $operacionRango, $traigoArrayNc, $decimales);
        // rubro es el cod de cualquier campo.
        //        echo "<pre>";
        // print_r($renglon);
        //        
        //echo "</pre>";

        //calculo de porcentaje 


        /* Fin de graficos*/



        //        print_r($arrayChartT);
        /**Envio Final*/
        if ($traigoArrayNc == 0 || $tipo == "un" || $tipo == "peso" || $tipo == "pieza") {
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
            // traigo el array de nc por descuentos.
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
                // calculo del porcentaje total 

                $arrayFinal = array(
                    "titulos" => $titulo,
                    "cabeceras" => $cabeceraTT,
                    "data" => $renglon,
                    "impNC" => $arrayNc
                );
            }
        }
        //        echo "<pre>";
        //        print_r($renglon);
        //        echo "</pre>";
        return json_encode($arrayFinal);
    }
}

/**
 * # ARMA SQL PARA para procesar para hacer mas prolijo la consulta
 */

function armar_sql(
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
    $operacionRango = null,
    $domCliente = null,
    $artEsamblado = null
) {
    /*
     * inicializacion de variables 
     * =======================================================================
     */
    $comoSumo = "";
    $primerAgrupo = "";
    $segundoAgrupo = "";
    $where = "";
    $orderby = "";
    $agrupar = "mes";
    $nombreRango = "";
    $numeroRango = "";

    /*Fechas para titulos*/

    $supVenta = $_SESSION["supervisor_venta"];
    $vendACargo = $_SESSION["vendedor_a_cargo"];
    $permisoGerencial = $_SESSION['inf_gerenciales'];
    
    // si soy un vendedor normal por mas que no sea supervisor de venta,
    // voy a ponerme a mi mismo como vendedro a cargo asi puedo ver solo mis datos.
  
    
    $usaIdManual = $_SESSION["usa_id_manual"];



    $desdT =  implode("/", array_reverse(explode("-", $desde)));
    $hastaT = implode("/", array_reverse(explode("-", $hasta)));
    $desdeDosT = implode("/", array_reverse(explode("-", $desdeDos)));
    $hastaDosT = implode("/", array_reverse(explode("-", $hastaDos)));
    $filtroEnsambVta = "";
    $usaBultoPromedio = $_SESSION["uso_bulto_promedio"];

    //$campo["total"]
    //    print_r($desdT);
    //    print_r($hastaT);
    //    print_r($desdeDosT);
    //    print_r($hastaDosT);
    //    argumentos recibidos.
    //   echo  "tipo:=>". var_dump($tipo)." \n";
    //   echo  "listarpor:=>".var_dump($listarPor)."\n ";
    //   echo "filtrapor:=>".var_dump($filtrarPor)." \n";
    //   echo "puntoVenta:=>".var_dump($puntoVenta)." \n";
    //   echo "periodo:=>".var_dump($periodo)." \n";
    //   echo "desde:=>".var_dump($desde)." \n";
    //   echo "hasta:=>".var_dump($hasta)." \n";
    //   echo "rangodoble:=>".var_dump($rangoDoble)." \n";
    //   echo "desdeDos:=>".var_dump($desdeDos)." \n";
    //   echo "hastaDos:=>".var_dump($hastaDos)." \n";
    //   echo "operacionRango:=>".var_dump($operacionRango)."\n ";



    /* PERIODO DE AGRUPACION, RANGO Y AGRUPACION DE PERIODOS*/
    /* Periodo, segun el tipo de operacin si es suma rango o resta */
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
            if($usaBultoPromedio=='Si'){
                // uso bulto promedio tengo que dividir.
                $comoSumo =" SUM( IF(
                    stock.TipoComp ='Venta' OR
                    stock.TipoComp ='Venta TPV'                  
                    #stock.TipoComp ='ND Anul NC'
					OR stock.TipoComp ='ND Anul NC'
                ,IF(CAST(Stock.Detalle AS DECIMAL)=0,
                CEIL(stock.Cantidad /cantidad_promedio_bulto),
                CAST(Stock.Detalle AS DECIMAL)),(IF(CAST(Stock.Detalle AS DECIMAL)=0,
                CEIL(stock.Cantidad /cantidad_promedio_bulto),
                CAST(Stock.Detalle AS DECIMAL))) * -1)) AS total";


            }
            if($usaBultoPromedio=='No'){
                $comoSumo =" SUM( IF(
                    stock.TipoComp ='Venta' OR
                    stock.TipoComp ='Venta TPV'  
                    #stock.TipoComp ='ND Anul NC'
					OR stock.TipoComp ='ND Anul NC'
                ,stock.Cantidad,stock.Cantidad * -1)) AS total";
            }
            break;
        // pierde sentido al modificar los bultos promedios.anque hay que ver.        
        case 'pieza':

            $comoSumo =" SUM( IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV'                  
                #stock.TipoComp ='ND Anul NC'
				OR stock.TipoComp ='ND Anul NC'
            ,IF(CAST(Stock.Detalle AS DECIMAL)=0,
    		CEIL(stock.Cantidad /cantidad_promedio_bulto),
            CAST(Stock.Detalle AS DECIMAL)),(IF(CAST(Stock.Detalle AS DECIMAL)=0,
    		CEIL(stock.Cantidad /cantidad_promedio_bulto),
            CAST(Stock.Detalle AS DECIMAL))) * -1)) AS total";
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
            // debo consultar si tengo peso promedio. 
            //if($usaBultoPromedio=='Si'){
            $comoSumo =" arti.cantidad_promedio_bulto AS kg,
                SUM( IF(
                                stock.TipoComp ='Venta' OR
                                stock.TipoComp ='Venta TPV'                                  
                                #stock.TipoComp ='ND Anul NC'  
								OR stock.TipoComp ='ND Anul NC'								
                                ,IF(arti.id_unimed=1,stock.Cantidad * arti.cantidad_promedio_bulto,stock.Cantidad)
                                ,IF(arti.id_unimed=1,stock.Cantidad * -1 * arti.cantidad_promedio_bulto, stock.Cantidad * -1 )
		
                                )
                            ) AS total";
            /* ojo  que en el caso de las los pesos por bultos hay que ver como motrarlo. */
            // si esta opcion tomamos vamos a tener que filtrar articulos que no se pueden
            $where .=" AND (arti.id_unimed=3 OR arti.id_unimed=2 OR (arti.id_unimed=1 AND arti.cantidad_promedio_bulto<>1))";
            // medir por peso.
            break;
        case 'monto':
//            $comoSumo =" SUM( IF(
//                stock.TipoComp ='Venta' OR
//                stock.TipoComp ='Venta TPV' OR 
//                stock.TipoComp = 'Anul NC Devol' OR 
//                stock.TipoComp ='ND Anul NC'
//            ,stock.PrecioNetoxR,stock.PrecioNetoxR * -1)) AS total";
            
//            $comoSumo="SUM(IF(ppv.cont='No',
//              IF(
//                stock.TipoComp ='Venta' OR
//                stock.TipoComp ='Venta TPV' OR 
//                stock.TipoComp = 'Anul NC Devol' OR 
//                stock.TipoComp ='ND Anul NC'
//            	,stock.PrecioBrutoxR,
//            	stock.PrecioBrutoxR * -1), 
//              IF(
//                stock.TipoComp ='Venta' OR
//                stock.TipoComp ='Venta TPV' OR 
//                stock.TipoComp = 'Anul NC Devol' OR 
//                stock.TipoComp ='ND Anul NC'
//            	,stock.PrecioNetoxR,
//            	stock.PrecioNetoxR * -1) 
//            
//           )) AS total ";
           $comoSumo="SUM(
              
              IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV'                 
                #stock.TipoComp ='ND Anul NC'
				OR stock.TipoComp ='ND Anul NC'
            	,stock.PrecioNetoxR,
            	stock.PrecioNetoxR * -1) 
            
           ) AS total "; 
            break;
		}
    /*campos a listar*/
    switch ($listarPor) {
        case 'cliente':
            // codigo manual o Cod sistema
            if ($domCliente == null) {
                $tdomCliente = "";
            } else {
                // los domicilios trae el primero de se usa
                $tdomCliente = ",' | ',(SELECT  CONCAT(cd.Calle , ' ', cd.NroCalle,' ', cd.Dpto) as cliDom
                FROM cliente_domicilio AS cd 
                WHERE cd.id_cliente = cli.Codigo 
                ORDER BY cd.id_cliente_domicilio DESC 
                LIMIT 1)";
            }
           // file_put_contents('log/sql_ventas_gerencia_'.date('Y-m-d-h').'.txt',"domCliente=>[".$domCliente."] tdomCliente: ".$tdomCliente.PHP_EOL,FILE_APPEND); 
            if ($usaIdManual == 'Si') {

                $primerAgrupo = "cli.id_manual_cli AS cod,CONCAT(cli.nombre_cliente,' (Cod: ',cli.id_manual_cli,')'" . $tdomCliente . ")  As nom,";
                $agrupar .= ",cli.Codigo";
                $orderby .= "cli.nombre_cliente ASC, ";
            } else {
                $primerAgrupo = "cli.Codigo AS cod,CONCAT(cli.nombre_cliente,' (Cod: ',cli.Codigo,')'" . $tdomCliente . ")  As nom,";
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
            $primerAgrupo = "vend.CodViajante AS cod, CONCAT(vend.Nombre,' (Cod: ',vend.CodViajante,')') As nom,";
            $agrupar .= ",vend.CodViajante";
            $orderby .= "vend.Nombre ASC, ";
            break;
        case 'articulo':
            /*evaluo que codigo uso para los articulos y clientes ...*/
            if ($usaIdManual == 'Si') {
                $primerAgrupo = "arti.id_manual AS cod,concat(' (cod: ',arti.id_manual,') ',arti.NombreArticulo)  As nom,";
                $agrupar .= ",arti.IDArt";
                $orderby .= "arti.NombreArticulo ASC, ";
                $where .= " AND NOT ISNULL(arti.id_manual) ";
            } else {
                $primerAgrupo = "arti.IDArt AS cod,concat(' (cod: ',arti.IDArt,') ',arti.NombreArticulo)  As nom,";
                $agrupar .= ",arti.IDArt";
                $orderby .= "arti.NombreArticulo ASC, ";
            }
            break;
        case 'proveedor':
            $primerAgrupo = " prov.Codigo AS cod,prov.Nombre As nom,";
            $agrupar .= ",prov.Codigo";
            $orderby .= " prov.Nombre ASC, ";
            break;
        case 'zona':
            $primerAgrupo = " zonas.id_zona AS cod,zonas.nombre_zona As nom,";
            $agrupar .= ",zonas.id_zona";
            $orderby .= " zonas.nombre_zona ASC, ";

            break;
        case 'categoria':
            $primerAgrupo = " cat.id_categoria AS cod, cat.nombre_categoria AS nom,";
            $agrupar .= ",cat.id_categoria";
            $orderby .= " cat.nombre_categoria ASC, ";
            break;
        case 'rubro':
            $primerAgrupo = " ru.CodigoRubro AS cod, ru.NombreRubro AS nom"
                . ",cat.id_categoria AS cod3, cat.nombre_categoria AS nom3,";
            $agrupar .= ",ru.CodigoRubro";
            $orderby .= " cat.nombre_categoria, ru.NombreRubro ASC, ";
            break;
        case 'subrubro':
            $primerAgrupo = "srub.IdSubRubro AS cod,srub.NombreSubRubro As nom"
                . ",ru.CodigoRubro AS cod3, ru.NombreRubro AS nom3,";
            $agrupar .= ",srub.IdSubRubro";
            $orderby .= " ru.NombreRubro ASC, srub.NombreSubRubro ASC, ";
            break;
        case 'marca':
            $primerAgrupo = "marca.CodMarca AS cod,marca.NombreMarca As nom,";

            $agrupar .= ",marca.CodMarca";
            $orderby .= " marca.NombreMarca ASC, ";
            break;
        case 'usuario':
            $primerAgrupo = "usu.id_usuario AS cod, CONCAT(usu.cod_usuario,' (Cod: ',usu.id_usuario,')') As nom,";
            $agrupar .= ",usu.id_usuario";
            $orderby .= "usu.cod_usuario ASC, ";
            break;
    }

    /* punto de venta
     * reviso el punto de venta si elegi la opcion todos en algun
     * parametro borro toda seleccion, si no , con el explode me queda
     * todo guardado. 
     *      */
    //    print_r($puntoVenta);
    //    var_dump(empty($puntoVenta));
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
    foreach ($filtrarPor as $ff) {
        $datoFiltro = explode("|", $ff);
        if (isset($datoFiltro[1])) {
            $arrFiltros[$datoFiltro[0]][] = $datoFiltro[1];
        }
    }

    //    print_r($arrFiltros);
    //    echo "permiso<pre>";
    //    print_r($_SESSION["supervisor_venta"]); 
    //    echo "</pre><br>";
    //    echo "vendedores<pre>";
    //    print_r( $_SESSION["vendedor_a_cargo"]); 
    //    echo "</pre><br>";
    /*
     * Supervisor de Ventas
     * ====================
     */



    $aplicoSupVentas = 0;
    foreach ($arrFiltros as $clave => $fi) {
        //        echo "<pre>";
        //        print_r($clave);
        //        print_r($fi);
        //        echo "</pre>";
        switch ($clave) {
            case 'cliente':
                // valida id manual o codigo sistema
                if ($usaIdManual == 'Si') {
                    // no puedo volver a agrupar.
                    if ($listarPor != 'cliente') {
                        $primerAgrupo .= 'cli.id_manual_cli AS cod2,cli.nombre_cliente  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND cli.id_manual_cli IN (' . implode(",", $fi) . ")";
                    }
                } else {

                    // no puedo volver a agrupar.
                    if ($listarPor != 'cliente') {
                        $primerAgrupo .= 'cli.Codigo AS cod2,cli.nombre_cliente  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                    }
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
                    $where  .= ' AND vend.CodViajante IN (' . implode(",", $fi) . ") AND vend.anulado='No'";
                    $aplicoSupVentas++;
                }

                break;
            case 'articulo':
                // uso idmanual o codigo de sistema
                if ($usaIdManual == 'Si') {
                    // no puedo volver a agrupar.
                    if ($listarPor != 'articulo') {
                        $primerAgrupo .= 'arti.id_manual AS cod2,arti.NombreArticulo  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND arti.id_manual IN (' . implode(",", $fi) . ")";
                    }
                } else {
                    // no puedo volver a agrupar.
                    if ($listarPor != 'articulo') {
                        $primerAgrupo .= 'arti.IDArt AS cod2,arti.NombreArticulo  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND arti.IDArt IN (' . implode(",", $fi) . ")";
                    }
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
            case 'categoria':
                // no puedo volver a agrupar.
                if ($listarPor != 'categoria') {
                    $primerAgrupo .= 'cat.id_categoria AS cod2, cat.nombre_categoria AS nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND cat.id_categoria IN (' . implode(",", $fi) . ")";
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
                    $primerAgrupo .= 'srub.IdSubRubro AS cod2,srub.NombreSubRubro As nom2,';
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
                case 'usuario':
                    // no puedo volver a agrupar.
                    if (!in_array("todos", $fi)) {
                        $primerAgrupo .= 'usu.id_usuario AS cod2,usu.cod_usuario  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.
                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND usu.id_usuario IN (' . implode(",", $fi) . ") AND usu.baja_usuario='No'";
                        $aplicoSupVentas++;
                    }
    
                    break;    

        }
    }
    //    echo "vdump=><pre>";
    //    var_dump($supVenta=="Si" && $aplicoSupVentas==0 && !empty($vendACargo));
    //    echo "</pre>";

    if ($supVenta == "Si" && $aplicoSupVentas == 0 && !empty($vendACargo)) {
        $vv = $vendACargo;

        $where .= ' AND vend.CodViajante IN (' . implode(",", $vv) . ') ';
    }

    // no soy supervisor de ventas
    if ($supVenta == "No") {
        $vendedor = (array) $_SESSION['vendedor'];
        $vv = array($vendedor['CodViajante']);
        // evaluar si tengo permiso para acceso a gerenciales. 
        // no soy supervisor y no tengo permiso para ver inf gerenciales 
        // soy solo un vendedor.
        if($permisoGerencial=='No'){
            $where .= ' AND vend.CodViajante IN (' . implode(",", $vv) . ') ';
        }
    }


    // Ensamblaje en la VENTA 
    // ------------------------------------------------------------------------
    if ($artEsamblado != null) {
        if ($artEsamblado == "detalle") {
            // si es detalle , tomo los articulos en composicion del ensamblado
            //        $filtroEnsambVta="AND (stock.visualiza_ensamble='No'  OR (stock.visualiza_ensamble='Si' AND stock.Entrada=0 ))";
            $filtroEnsambVta = "AND (arti.ensamblado='No')";
        }
        if ($artEsamblado == "simple") {
            // si es simple tomo el articulo ensamblado Contenedor.
            $filtroEnsambVta = "AND stock.visualiza_ensamble='No'";
        }
    }


    /*
     * armando de SQL
     * =========================================================================
     */
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
                LEFT JOIN rubro AS ru ON ru.CodigoRubro = arti.CodigoRubro
                LEFT JOIN rubro_categoria AS cat ON cat.id_categoria=ru.id_categoria
                LEFT JOIN subrubro AS srub ON srub.IDSubRubro = arti.IDSubRubro
                LEFT JOIN marca ON marca.CodMarca=arti.CodigoMarca
                LEFT JOIN proveedor AS prov ON prov.Codigo = arti.CodigoProveedor
                LEFT JOIN cliente AS cli ON (cli.Codigo= stock.CodigoCP)
                #LEFT JOIN viajantes AS vend ON (vend.CodViajante= cli.CodViajante)
                LEFT JOIN viajantes AS vend ON (vend.CodViajante= cc.CodViajante)
                LEFT JOIN usuarios AS usu ON (usu.id_usuario=stock.IdUsuario)
                LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
                LEFT JOIN punto_venta AS ppv ON ( ppv.id_punto_venta=cc.id_pv)
                LEFT JOIN tipo_cliente AS tpcli ON(tpcli.IDTipoCliente=cli.TipoCliente)
           WHERE
                ({$rangoFecha})
               
               AND stock.anulado='No'
               {$filtroEnsambVta}
               
                AND (stock.TipoComp = 'Venta' 
                    OR stock.TipoComp = 'Venta TPV' 
                    OR stock.TipoComp = 'Devol - Cliente' 
                    #OR stock.TipoComp = 'ND Anul NC'                   
					OR stock.TipoComp = 'ND Anul NC'                   
                    )
                 {$where}    

            GROUP BY {$agrupar} ORDER BY {$orderby} stock.Fecha ASC";
    //   echo "ventas NETAS \n";
    //            echo $sql;
    // file_put_contents('log/sql_ventas_gerencia_'.date('Y-m-d-h').'.txt',$sql.PHP_EOL,FILE_APPEND);       
    return $sql;
}
// * armar sql de notas de credito 
function armar_sql_nc(
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
    $supVenta = $_SESSION["supervisor_venta"];
    $vendACargo = $_SESSION["vendedor_a_cargo"];
    $permisoGerencial = $_SESSION['inf_gerenciales'];
    $usaIdManual = $_SESSION["usa_id_manual"];
    //    echo "<pre>";
    //    print_R($usaIdManual);
    //    echo "</pre>";
    $aplicoSupVentas = 0;

    /* PERIODO DE AGRUPACION, RANGO Y AGRUPACION DE PERIODOS*/
    /* Periodo, segun el tipo de operacin si es suma rango o resta */
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
            if ($usaIdManual == 'Si') {
                $primerAgrupo = "cli.id_manual_cli AS cod,CONCAT(cli.nombre_cliente,' (Cod: ',cli.id_manual_cli,')')  As nom,";
                $agrupar .= ",cli.id_manual_cli";
                $orderby .= "cli.nombre_cliente ASC, ";
            } else {
                $primerAgrupo = "cli.Codigo AS cod,CONCAT(cli.nombre_cliente,' (Cod: ',cli.Codigo,')')  As nom,";
                $agrupar .= ",cli.Codigo";
                $orderby .= "cli.nombre_cliente ASC, ";
            }
            //            if($usaIdManual='Si'){
            //                $primerAgrupo = "cc.Codigo AS cod,";
            //                $agrupar .=",cc.Codigo";
            //                $orderby .="cc.Codigo ASC, ";
            //            }else{
            //                $primerAgrupo = "cc.Codigo AS cod,";
            //                $agrupar .=",cc.Codigo";
            //                $orderby .="cc.Codigo ASC, ";
            //            }
            break;
        case 'vendedor':
            $primerAgrupo = "vend.CodViajante AS cod,vend.Nombre  As nom,";
            $agrupar .= ",vend.CodViajante";
            $orderby .= "vend.Nombre ASC, ";
            break;

        case 'zona':
            $primerAgrupo = " zonas.id_zona AS cod,zonas.nombre_zona As nom,";
            $agrupar .= ",zonas.id_zona";
            $orderby .= " zonas.nombre_zona ASC, ";

            break;
        case 'tipocliente':
            $primerAgrupo .= 'tpcli.IDTipoCliente AS cod,tpcli.NombreTipoCliente  As nom,';
            $agrupar .= ",tpcli.IDTipoCliente";
            $orderby .= "tpcli.NombreTipoCliente ASC,";
            break;
        case 'usuario':
            $primerAgrupo = "usu.id_usuario AS cod,usu.cod_usuario  As nom,";
            $agrupar .= ",usu.id_usuario";
            $orderby .= "usu.cod_usuario ASC, ";
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
    foreach ($filtrarPor as $ff) {

        $datoFiltro = explode("|", $ff);
        if (isset($datoFiltro[1])) {
            $arrFiltros[$datoFiltro[0]][] = $datoFiltro[1];
        }
    }

    foreach ($arrFiltros as $clave => $fi) {
        //        print_r($clave);
        //        print_r($fi);
        switch ($clave) {
            case 'cliente':
                if ($usaIdManual == 'Si') {
                    // no puedo volver a agrupar.
                    if ($listarPor != 'cliente') {
                        $primerAgrupo .= 'cli.id_manual_cli AS cod2,cli.nombre_cliente  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND cli.id_manual_cli IN (' . implode(",", $fi) . ")";
                    }
                } else {
                    // no puedo volver a agrupar.
                    if ($listarPor != 'cliente') {
                        $primerAgrupo .= 'cli.Codigo AS cod2,cli.nombre_cliente  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                    }
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
                    $aplicoSupVentas++;
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
            case 'tipocliente':
                // no puedo volver a agrupar.
                if ($listarPor != 'tipocliente') {
                    $primerAgrupo .= ' tpcli.IDTipoCliente AS cod2,tpcli.NombreTipoCliente  As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.

                if (!in_array("todos", $fi)) {
                    $where  .= ' AND tpcli.IDTipoCliente IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'usuario':
                // no puedo volver a agrupar.
                if (!in_array("todos", $fi)) {
                    $primerAgrupo .= 'usu.id_usuario AS cod2,usu.cod_usuario  As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND usu.id_usuario IN (' . implode(",", $fi) . ")";
                    $aplicoSupVentas++;
                }
                break;
        }
    }
    /* rango de fechas doble.
     */
    //    $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
    //    if($rangoDoble==1){
    //        $rangoFecha .=" OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
    //    }

    /* 
     * filtrar por grupo de Vendedores 
     */

    

    if ($supVenta == "Si" && $aplicoSupVentas == 0 && !empty($vendACargo)) {
        $vv = $vendACargo;

        $where .= ' AND vend.CodViajante IN (' . implode(",", $vv) . ') ';
    }

    // no soy supervisor de ventas
    if ($supVenta == "No") {
        $vendedor = (array) $_SESSION['vendedor'];
        $vv = array($vendedor['CodViajante']);
        // evaluar si tengo permiso para acceso a gerenciales. 
        // no soy supervisor y no tengo permiso para ver inf gerenciales 
        // soy solo un vendedor.
        if($permisoGerencial=='No'){
            $where .= ' AND vend.CodViajante IN (' . implode(",", $vv) . ') ';
        }
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
        LEFT JOIN cliente AS cli ON cli.Codigo = cc.Codigo
        #LEFT JOIN viajantes AS vend ON (vend.CodViajante= cli.CodViajante)
        LEFT JOIN viajantes AS vend ON (vend.CodViajante= cc.CodViajante)
        LEFT JOIN usuarios AS usu ON (usu.id_usuario= cc.IdUsuario)
        LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
        LEFT JOIN tipo_cliente AS tpcli ON(tpcli.IDTipoCliente=cli.TipoCliente)
        WHERE
          ({$rangoFecha})       
        AND cc.Anulado ='No'
       
        #AND cc.TipoComprobante IN ('NCA','NCB','NCE','NCC','NCM','FA','FB','FE','FC','FM')
         AND cc.TipoComprobante IN ('NCA','NCB', 'NCE','NCC','NCM','FA','FB','FE','FC','FM','NDA', 
                                'NDB', 
                                'NDE', 
                                'NDC', 
                                'NDM')
    AND (ISNULL(cc.concepto_nd) OR cc.concepto_nd<>'Anulacion NC - Mercaderia') 
        {$where}
        GROUP BY    
        {$agrupar} ORDER BY {$orderby} cc.`Fecha`";
    //        echo "nc NETAS \n";
    //            echo $sql;
    return $sql;
}

// * armar sql de notas de Debito 
function armar_sql_nd(
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
    $supVenta = $_SESSION["supervisor_venta"];
    $vendACargo = $_SESSION["vendedor_a_cargo"];
    $permisoGerencial = $_SESSION['inf_gerenciales'];
    $usaIdManual = $_SESSION["usa_id_manual"];
    //    echo "<pre>";
    //    print_R($usaIdManual);
    //    echo "</pre>";
    $aplicoSupVentas = 0;

    /* PERIODO DE AGRUPACION, RANGO Y AGRUPACION DE PERIODOS*/
    /* Periodo, segun el tipo de operacin si es suma rango o resta */
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
            if ($usaIdManual == 'Si') {
                $primerAgrupo = "cli.id_manual_cli AS cod,CONCAT(cli.nombre_cliente,' (Cod: ',cli.id_manual_cli,')')  As nom,";
                $agrupar .= ",cli.id_manual_cli";
                $orderby .= "cli.nombre_cliente ASC, ";
            } else {
                $primerAgrupo = "cli.Codigo AS cod,CONCAT(cli.nombre_cliente,' (Cod: ',cli.Codigo,')')  As nom,";
                $agrupar .= ",cli.Codigo";
                $orderby .= "cli.nombre_cliente ASC, ";
            }
            //            if($usaIdManual='Si'){
            //                $primerAgrupo = "cc.Codigo AS cod,";
            //                $agrupar .=",cc.Codigo";
            //                $orderby .="cc.Codigo ASC, ";
            //            }else{
            //                $primerAgrupo = "cc.Codigo AS cod,";
            //                $agrupar .=",cc.Codigo";
            //                $orderby .="cc.Codigo ASC, ";
            //            }
            break;
        case 'vendedor':
            $primerAgrupo = "vend.CodViajante AS cod,vend.Nombre  As nom,";
            $agrupar .= ",vend.CodViajante";
            $orderby .= "vend.Nombre ASC, ";
            break;

        case 'zona':
            $primerAgrupo = " zonas.id_zona AS cod,zonas.nombre_zona As nom,";
            $agrupar .= ",zonas.id_zona";
            $orderby .= " zonas.nombre_zona ASC, ";

            break;
        case 'tipocliente':
            $primerAgrupo .= 'tpcli.IDTipoCliente AS cod,tpcli.NombreTipoCliente  As nom,';
            $agrupar .= ",tpcli.IDTipoCliente";
            $orderby .= "tpcli.NombreTipoCliente ASC,";
            break;
        case 'usuario':
            $primerAgrupo = "usu.id_usuario AS cod,usu.cod_usuario  As nom,";
            $agrupar .= ",usu.id_usuario";
            $orderby .= "usu.cod_usuario ASC, ";
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
    foreach ($filtrarPor as $ff) {

        $datoFiltro = explode("|", $ff);
        if (isset($datoFiltro[1])) {
            $arrFiltros[$datoFiltro[0]][] = $datoFiltro[1];
        }
    }

    foreach ($arrFiltros as $clave => $fi) {
        //        print_r($clave);
        //        print_r($fi);
        switch ($clave) {
            case 'cliente':
                if ($usaIdManual == 'Si') {
                    // no puedo volver a agrupar.
                    if ($listarPor != 'cliente') {
                        $primerAgrupo .= 'cli.id_manual_cli AS cod2,cli.nombre_cliente  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND cli.id_manual_cli IN (' . implode(",", $fi) . ")";
                    }
                } else {
                    // no puedo volver a agrupar.
                    if ($listarPor != 'cliente') {
                        $primerAgrupo .= 'cli.Codigo AS cod2,cli.nombre_cliente  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                    }
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
                    $aplicoSupVentas++;
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
            case 'tipocliente':
                // no puedo volver a agrupar.
                if ($listarPor != 'tipocliente') {
                    $primerAgrupo .= ' tpcli.IDTipoCliente AS cod2,tpcli.NombreTipoCliente  As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.

                if (!in_array("todos", $fi)) {
                    $where  .= ' AND tpcli.IDTipoCliente IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'usuario':
                // no puedo volver a agrupar.
                if (!in_array("todos", $fi)) {
                    $primerAgrupo .= 'usu.id_usuario AS cod2,usu.cod_usuario  As nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND usu.id_usuario IN (' . implode(",", $fi) . ")";
                    $aplicoSupVentas++;
                }
                break;
        }
    }
    /* rango de fechas doble.
     */
    //    $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
    //    if($rangoDoble==1){
    //        $rangoFecha .=" OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
    //    }

    /* 
     * filtrar por grupo de Vendedores 
     */

    

    if ($supVenta == "Si" && $aplicoSupVentas == 0 && !empty($vendACargo)) {
        $vv = $vendACargo;

        $where .= ' AND vend.CodViajante IN (' . implode(",", $vv) . ') ';
    }

    // no soy supervisor de ventas
    if ($supVenta == "No") {
        $vendedor = (array) $_SESSION['vendedor'];
        $vv = array($vendedor['CodViajante']);
        // evaluar si tengo permiso para acceso a gerenciales. 
        // no soy supervisor y no tengo permiso para ver inf gerenciales 
        // soy solo un vendedor.
        if($permisoGerencial=='No'){
            $where .= ' AND vend.CodViajante IN (' . implode(",", $vv) . ') ';
        }
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
                    cc.SubtotalDesc
                    ) AS total
        FROM   cuentacliente AS cc
        LEFT JOIN cliente AS cli ON cli.Codigo = cc.Codigo
        #LEFT JOIN viajantes AS vend ON (vend.CodViajante= cli.CodViajante)
        LEFT JOIN viajantes AS vend ON (vend.CodViajante= cc.CodViajante)
        LEFT JOIN usuarios AS usu ON (usu.id_usuario= cc.IdUsuario)
        LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
        LEFT JOIN tipo_cliente AS tpcli ON(tpcli.IDTipoCliente=cli.TipoCliente)
        WHERE
          ({$rangoFecha})       
        AND cc.Anulado ='No'
       
       
         AND cc.TipoComprobante IN ('NDA', 
                                'NDB', 
                                'NDE', 
                                'NDC', 
                                'NDM')
    AND (NOT ISNULL(cc.concepto_nd) OR cc.concepto_nd = 'Anulacion NC - Descuento') 
        {$where}
        GROUP BY    
        {$agrupar} ORDER BY {$orderby} cc.`Fecha`";
    //        echo "nc NETAS \n";
    //            echo $sql;
    return $sql;
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

// * funcion para recupear las notas de debito que anulan nc ( no tienen stock, con lo cual esun valor global a sumar )
function traer_valor_nd( $connV,
$puntoVentaId = null,
$desde = null,
$hasta = null,
$periodo,
$rangoDoble = null,
$desdeDos = null,
$hastaDos = null,
$operacionRango = null,
$filtrarPor = null){
/// filtrar segun el filtro las notas de credito.
$nombreRango = "";
$numeroRango = "";
$where = "";
$desdT =  implode("/", array_reverse(explode("-", $desde)));
$hastaT = implode("/", array_reverse(explode("-", $hasta)));
$desdeDosT = implode("/", array_reverse(explode("-", $desdeDos)));
$hastaDosT = implode("/", array_reverse(explode("-", $hastaDos)));
$usaIdManual = $_SESSION["usa_id_manual"];

if (!empty($puntoVentaId)) {
    if (!in_array("todos", $puntoVentaId)) {
        $where = " AND cc.id_pv IN (" . implode(",", $puntoVentaId) . ")";
    }
}
// Rangos
if ($operacionRango == "suma") {
    // solo se suma
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


    /*
     * Rango de fecha
     */

    $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta} ') ";
    if ($rangoDoble == 1) {
        $rangoFecha .= " OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}')";
    }
} else {
    // suma agrupada y diferencia
    $agrupar = "rango";
    $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta} ') ";
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
    switch ($clave) {
        case 'cliente':
            // no puedo volver a agrupar.

            // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
            // donde puede tener un valor o muchos.

            if (!in_array("todos", $fi)) {
                if ($usaIdManual == "Si") {
                    $where  .= ' AND cli.id_manual_cli IN (' . implode(",", $fi) . ")";
                } else {
                    $where  .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                }
            }

            break;
        case 'vendedor':

            // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
            // donde puede tener un valor o muchos.
            if (!in_array("todos", $fi)) {
                $where  .= ' AND vend.CodViajante IN (' . implode(",", $fi) . ") AND vend.anulado='No'";
            }
            break;

        case 'zona':
            // no puedo volver a agrupar.

            // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
            // donde puede tener un valor o muchos.
            if (!in_array("todos", $fi)) {
                $where  .= ' AND  zonas.id_zona IN (' . implode(",", $fi) . ")";
            }

            break;

        case 'usuario':

            // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
            // donde puede tener un valor o muchos.
            if (!in_array("todos", $fi)) {
                $where  .= ' AND usu.id_usuario IN (' . implode(",", $fi) . ") AND usu.baja_usuario='No'";
            }
            break;    
    }
}




/*
 * SQL FINAL DE NOTAS DE CREDITO DEL ARTICULO
 */

$sqlNC = " SELECT 
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
            SUM( cc.SubtotalDesc) AS importe
 FROM   cuentacliente AS cc
    
    LEFT JOIN cliente AS cli ON cli.Codigo = cc.Codigo
    #LEFT JOIN viajantes AS vend ON (vend.CodViajante= cli.CodViajante)
    LEFT JOIN viajantes AS vend ON (vend.CodViajante= cc.CodViajante)
    LEFT JOIN usuarios AS usu ON (usu.id_usuario= cc.IdUsuario)                        
    LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
 WHERE
  ({$rangoFecha})
      
  {$where}    
AND cc.Anulado ='No'


 AND cc.TipoComprobante IN ('NDA','NDB','NDE','NDC','NDM','NCA','NCB')                            
GROUP BY {$agrupar} 
   
 ORDER BY cc.`Fecha` ASC";
//           AND cc.TipoComprobante IN ('NDA','NDB','NDE','NDC','NDM','NCA','NCB',
//                                'NCE','NCC','NCM','FA','FB','FE','FC','FM')
//  print_r($sqlNC);
if(debugSql==true){
    $sqlLog =file_put_contents('log/sql_utilidad'.date('Ymd').'.sql','Notas Debito:'.PHP_EOL.$sqlNC.PHP_EOL,FILE_APPEND);
}
$hacerNc = mysqli_query($connV, $sqlNC) or die("no puedo recuperar las notas de Debito" . mysqli_error($connV) . $sqlNC);
$arrayNc = array();
while ($nc = mysqli_fetch_assoc($hacerNc)) {
    if ($operacionRango == "suma") {
        $icampo = intval($nc[$ith[0]] . $nc[$ith[1]]);
    } else {
        $icampo = intval($nc["rango"]);
    }
    $arrayNc[$icampo] = $nc["importe"];
}
//    print_r($sqlNC);
return $arrayNc;
}

// * funcion que recupera las notas de credito con valores porque no hay en stock, 
function traer_valor_nc(
    $connV,
    $puntoVentaId = null,
    $desde = null,
    $hasta = null,
    $periodo,
    $rangoDoble = null,
    $desdeDos = null,
    $hastaDos = null,
    $operacionRango = null,
    $filtrarPor = null
) {
    /// filtrar segun el filtro las notas de credito.
    $nombreRango = "";
    $numeroRango = "";
    $where = "";
    $desdT =  implode("/", array_reverse(explode("-", $desde)));
    $hastaT = implode("/", array_reverse(explode("-", $hasta)));
    $desdeDosT = implode("/", array_reverse(explode("-", $desdeDos)));
    $hastaDosT = implode("/", array_reverse(explode("-", $hastaDos)));
    $usaIdManual = $_SESSION["usa_id_manual"];

    if (!empty($puntoVentaId)) {
        if (!in_array("todos", $puntoVentaId)) {
            $where = " AND cc.id_pv IN (" . implode(",", $puntoVentaId) . ")";
        }
    }
    // Rangos
    if ($operacionRango == "suma") {
        // solo se suma
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


        /*
         * Rango de fecha
         */

        $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta} ') ";
        if ($rangoDoble == 1) {
            $rangoFecha .= " OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}')";
        }
    } else {
        // suma agrupada y diferencia
        $agrupar = "rango";
        $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta} ') ";
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
        switch ($clave) {
            case 'cliente':
                // no puedo volver a agrupar.

                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.

                if (!in_array("todos", $fi)) {
                    if ($usaIdManual == "Si") {
                        $where  .= ' AND cli.id_manual_cli IN (' . implode(",", $fi) . ")";
                    } else {
                        $where  .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                    }
                }

                break;
            case 'vendedor':

                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND vend.CodViajante IN (' . implode(",", $fi) . ") AND vend.anulado='No'";
                }
                break;

            case 'zona':
                // no puedo volver a agrupar.

                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND  zonas.id_zona IN (' . implode(",", $fi) . ")";
                }

                break;

            case 'usuario':

                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND usu.id_usuario IN (' . implode(",", $fi) . ") AND usu.baja_usuario='No'";
                }
                break;    
        }
    }




    /*
     * SQL FINAL DE NOTAS DE CREDITO DEL ARTICULO
     */

    $sqlNC = " SELECT 
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
        
        LEFT JOIN cliente AS cli ON cli.Codigo = cc.Codigo
        #LEFT JOIN viajantes AS vend ON (vend.CodViajante= cli.CodViajante)
        LEFT JOIN viajantes AS vend ON (vend.CodViajante= cc.CodViajante)
        LEFT JOIN usuarios AS usu ON (usu.id_usuario= cc.IdUsuario)                        
        LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
     WHERE
      ({$rangoFecha})
          
      {$where}    
    AND cc.Anulado ='No'

   
    AND cc.TipoComprobante IN ('NCA','NCB',
                                    'NCE','NCC','NCM','FA','FB','FE','FC','FM')                            
    GROUP BY {$agrupar} 
       
     ORDER BY cc.`Fecha` ASC";
    //           AND cc.TipoComprobante IN ('NDA','NDB','NDE','NDC','NDM','NCA','NCB',
    //                                'NCE','NCC','NCM','FA','FB','FE','FC','FM')
    //  print_r($sqlNC);
    if(debugSql==true){
        $sqlLog =file_put_contents('log/sql_utilidad'.date('Ymd').'.sql','Notas Credito:'.PHP_EOL.$sqlNC.PHP_EOL,FILE_APPEND);
    }
    $hacerNc = mysqli_query($connV, $sqlNC) or die("no puedo recuperar las notas de C" . mysqli_error($connV) . $sqlNC);
    $arrayNc = array();
    while ($nc = mysqli_fetch_assoc($hacerNc)) {
        if ($operacionRango == "suma") {
            $icampo = intval($nc[$ith[0]] . $nc[$ith[1]]);
        } else {
            $icampo = intval($nc["rango"]);
        }
        $arrayNc[$icampo] = $nc["importe"];
    }
    //    print_r($sqlNC);
    return $arrayNc;
}

/*
 * Utilidades 
 */
function utilidades_totales_todos(
    $connV,
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
    $pvLista = explode("||", $puntoVenta);
    $listaFiltro = explode("||", $filtrarPor);
    $puntoVentaId = array();
    foreach ($pvLista as $pv) {
        $arrPuntoVenta = explode("|", $pv);
        //        echo "<pre>";
        //        print_r($arrPuntoVenta);
        //        echo "</pre>";
        if (isset($arrPuntoVenta[1]) && $arrPuntoVenta[1] == "todos") {
            $puntoVentaId[] = "todos";
        } else {
            if ($arrPuntoVenta[0] != "") {
                $puntoVentaId[] = $arrPuntoVenta[0];
            }
        }
        if (isset($arrPuntoVenta[1])) {
            $puntoVentaTxt[] = $arrPuntoVenta[1];
        }
    }
    $tituloFil = " PV: " . implode(",", $puntoVentaTxt);

    // filtro valores


    $arrFiltros = array();
    if (!empty($listaFiltro) && $listaFiltro[0] != "") {

        foreach ($listaFiltro as $valorFiltro) {

            $datoFiltro = explode("|", $valorFiltro);
            if (isset($datoFiltro[2])) {
                $arrFiltros[$datoFiltro[0]][] = $datoFiltro[2];
            }
        }
    }

    // return $sqlTotal;
    $arrResultado = array();
    $arrResultadoNc = array();
    $agrupar = "mes";

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
    /* evaluar si es rango doble y pasar las dos variables o nada.*/
    /*Todo*/
    //    if($rangoDoble==1){
    //        $sqlTotal = armar_sql_utlidad($tipo, $listarPor, $listaFiltro, $puntoVentaId, $periodo, $desde, $hasta,$rangoDoble,$desdeDos,$hastaDos );   
    //    }else{ 
    //        $sqlTotal = armar_sql_utilidad($tipo, $listarPor, $listaFiltro, $puntoVentaId, $periodo, $desde, $hasta);   
    //    }
    $sqlTotal = armar_sql_utilidad($tipo, $listarPor, $listaFiltro, $puntoVentaId, $periodo, $desde, $hasta);
            // echo "Utilidad <pre>",print_r($sqlTotal), "</pre>";
            if(debugSql==true){
                $sqlLog =file_put_contents('log/sql_utilidad'.date('Ymd').'.sql','Utilidad:'.PHP_EOL.$sqlTotal.PHP_EOL,FILE_APPEND);
            }
    $hacer = mysqli_query($connV, $sqlTotal) or die("no puedo hacer la consulta de las ventas" . mysqli_error($connV) . "<pre>" . $sqlTotal . "</pre>");
    while ($r = mysqli_fetch_assoc($hacer)) {
        $arrResultado[] = $r;
    }
    // SIN RESULTADO

    if (empty($arrResultado)) {
        return "vacio";
    }

    // validar si es con valor o con array las notas de credito.

    $valorNc = array('articulo', 'proveedor', 'rubro', 'subrubro', 'categoria', 'marca');
    $traigoArrayNc = 0;
    if (in_array($listarPor, $valorNc)) {
        $traigoArrayNc++;
    }

    foreach ($arrFiltros as $cc => $vv) {
        if (in_array($cc, $valorNc)) {
            $traigoArrayNc++;
        }
    }
    //    
    //    //evaluo si nc con array o valor
    ////    $traigoArrayNc++;
    $arrayNc=array();
    if ($traigoArrayNc == 0) {
        // busco array
        //        if($rangoDoble==1){
        //            $sqlTotalNc = armar_sql_nc($tipo, $listarPor, $listaFiltro, $puntoVentaId, $periodo, $desde, $hasta,$rangoDoble,$desdeDos,$hastaDos);
        //        }else{
        $sqlTotalNc = armar_sql_nc_utlidad($tipo, $listarPor, $listaFiltro, $puntoVentaId, $periodo, $desde, $hasta);
        //        }
        //        echo "<pre>";
        //        print_r($sqlTotalNc);
        //        echo "</pre>";
        if(debugSql==true){
            $sqlLog =file_put_contents('log/sql_utilidad'.date('Ymd').'.sql','Traigo NC:'.PHP_EOL.$sqlTotalNc.PHP_EOL,FILE_APPEND);
        }
        $hacerNc = mysqli_query($connV, $sqlTotalNc) or die("no puedo hacer la consulta de las ventas" . mysqli_error($connV) . "<pre>" . $sqlTotalNc . "</pre>");
        while ($n = mysqli_fetch_assoc($hacerNc)) {
            $arrayNc[] = $n;
        }
    } else {
        //recupero el valor
        $arrayNc = traer_valor_nc_utilidad($connV, $puntoVentaId, $desde, $hasta, $periodo, $listaFiltro);
    }
    //    if($tipo=="un" || $tipo == "peso"){
    //        $traigoArrayNc++;
    //    }
    //    print_r($arrayNc);
    // temporal solo para la prueba.
    //$arrayNc = traer_valor_nc($puntoVentaId, $desde, $hasta,$periodo);       
    // notas de credito nc x articulo

    /*
     * SALIDA TABLA HTML
     */
    if ($salida == "html") {
        //        echo "<pre>";
        //        print_r($arrayNc);
        //        echo "</pre>";


        $arrCabeceras = getCabeceraUtilidad($desde, $hasta, $periodo, $listarPor, $arrFiltros, $tipo, $arrResultado);
        //        echo "<pre>";
        //        print_r($arrCabeceras);
        //        echo "</pre>";

        $titulo = $arrCabeceras["titulo"];
        $cabeceraTT = $arrCabeceras["cabecera"];
        $ith = $arrCabeceras["ith"];
        /***Cambio de cabeceras*/

        /*
         * DATOS
         */

        $renglon = getDatosUtilidad($arrResultado, $ith, $cabeceraTT, $arrayNc, $operacionRango, $traigoArrayNc, null, null);
        //        echo "renglon utilidad:{{<pre>";
        //        print_r($renglon);
        //        echo "</pre>";


        /*
         * DATOS
         */
        $rng = 0;
        // rubro es el cod de cualquier campo.



        // dato de tabla html 
        // lo que tengo que hacer aca es agregar los demas datos de costo,venta
        // utilidad, y porcentaje utilidad.  
        //echo "<pre>";



        //        print_r($arrayChartT);
        /**Envio Final*/
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
        //        print_r($renglon);
        //        echo "</pre>";
        return json_encode($arrayFinal);
    }
}

//function traer_graficos($arrFiltros,){
//    
//}
/*
 * Sacar en dos procedimientos la generacion de graficos de torta y de barras y 
 * el procesamiento de los arrays para la tabla html.
 */

/*
 * funcion para buscar el valor dentro de un array de las notas de credito por descuento al pie.
 * **/
function multidimensional_search($parents, $searched)
{
    if (empty($searched) || empty($parents)) {
        return false;
    }
    //  echo "\n padre:=>";
    //  print_r($parents);
    //  echo "\ buscsado:";
    //  print_r($searched);

    foreach ($parents as $key => $value) {
        $exists = true;
        //echo "\n key:=>{".$key."} value:=>{".$value."}\n";
        foreach ($searched as $skey => $svalue) {
            //       echo "\n valor:=>";
            //        print_r($svalue);
            //       echo "";
            //       echo "\n claveSkey:=>";
            //       var_dump($skey);
            //       echo "\n valor que encontro:=>"; 
            //       print_r($parents[$key][$skey]);
            //       var_dump($parents[$key][$skey]);
            //       echo "";
            //echo "skey:=>{".$skey."} svalue:=>{".$svalue."}";
            $exists = ($exists && isset($parents[$key][$skey]) && $parents[$key][$skey] == $svalue);
            //       echo "\n existe?:=>"; 
            //       var_dump($exists);
            //       echo "";
        }
        //     echo "\n Existefinal:=>"; 
        //        //print_r($searched);
        //        var_dump($exists);
        //       echo "\n";

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
        $length = 150;
    //Primero eliminamos las etiquetas html y luego cortamos el string
    $stringDisplay = substr(strip_tags($string), 0, $length);
    //Si el texto es mayor que la longitud se agrega puntos suspensivos
    if (strlen(strip_tags($string)) > $length)
        $stringDisplay .= ' ...';
    return $stringDisplay;
}
/* armado sql de utilidad */
function armar_sql_utilidad(
    $tipo = null,
    $listarPor = null,
    $filtrarPor = null,
    $puntoVenta = null,
    $periodo = null,
    $desde = null,
    $hasta = null,
    $rangoDoble = null,
    $desdeDos = null,
    $hastaDos = null
) {
    $comoSumo = "";
    $primerAgrupo = "";
    $segundoAgrupo = "";
    $where = "";
    $orderby = "";
    //    $agrupar = "mes";
    $agrupar = "";
    $supVenta = $_SESSION["supervisor_venta"];
    $vendACargo = $_SESSION["vendedor_a_cargo"];
    $permisoGerencial = $_SESSION['inf_gerenciales'];
    $usaIdManual = $_SESSION["usa_id_manual"];


    // calculo los tres campos.
    if ($rangoDoble != null) {
        $comoSumo = " SUM( 
                    IF(stock.Fecha>='{$desde}' AND stock.Fecha<='{$hasta}',
                        IF(
                                stock.TipoComp ='Venta' OR
                                stock.TipoComp ='Venta TPV'                                              
                                OR stock.TipoComp ='ND Anul NC'
                        ,stock.PrecioNetoxR,stock.PrecioNetoxR * -1)
                            ,0
                    )
                ) AS Neto,
                SUM( 
			IF(stock.Fecha>='{$desde}' AND stock.Fecha<='{$hasta}',
				IF(
                                    stock.TipoComp ='Venta' OR
                                    stock.TipoComp ='Venta TPV'  						
                                    OR stock.TipoComp ='ND Anul NC'
				#,(stock.PrecioCostoxU*stock.Cantidad),
                #(stock.PrecioCostoxU * stock.Cantidad)* -1),
                ,(stock.PrecioCostoxR),
                (stock.PrecioCostoxR)* -1),
				0
			)
		) AS Costo,
       
                SUM(
			IF(stock.Fecha>='{$desde}' AND stock.Fecha<='{$hasta}',
                            
                            IF(
                                    stock.TipoComp ='Venta' OR
                                    stock.TipoComp ='Venta TPV'  

                                    OR stock.TipoComp ='ND Anul NC'
                           # ,stock.PrecioNetoxR-(stock.PrecioCostoxU*stock.Cantidad),
                           # (stock.PrecioNetoxR-(stock.PrecioCostoxU*stock.Cantidad))* -1) 
                            ,stock.PrecioNetoxR-(stock.PrecioCostoxR),
                            (stock.PrecioNetoxR-(stock.PrecioCostoxR))* -1)                            
                            , 0
			)
                ) AS Utilidad,
				
                
                SUM(
                    IF(stock.Fecha>='{$desde}' AND stock.Fecha<='{$hasta}',
                        IF(
                            stock.TipoComp ='Venta' OR
                            stock.TipoComp ='Venta TPV'                              
                             OR stock.TipoComp ='ND Anul NC'
                            ,
                            stock.PrecioNetoxR ,
                            stock.PrecioNetoxR * -1
                        ),
                        0      
                    )
                )
                / 
                SUM( 
                    IF(stock.Fecha>='{$desde}' AND stock.Fecha<='{$hasta}',
                        IF(
                                stock.TipoComp ='Venta' OR
                                stock.TipoComp ='Venta TPV'                               
                                OR stock.TipoComp ='ND Anul NC'
                                ,
                                #(stock.PrecioCostoxU*stock.Cantidad),
                                #(stock.PrecioCostoxU*stock.Cantidad) * -1
                                (stock.PrecioCostoxR),
                                (stock.PrecioCostoxR) * -1
                        )
                        , 0
                    )
                )         
        AS PorUtil,
            SUM( 
                IF(stock.Fecha>='{$desdeDos}' AND stock.Fecha<='{$hastaDos}',
                    IF(
                        stock.TipoComp ='Venta' OR
                        stock.TipoComp ='Venta TPV'                                              
                        OR stock.TipoComp ='ND Anul NC'
                        ,
                        stock.PrecioNetoxR,
                        stock.PrecioNetoxR * -1
                    ),
                    0
                )
        ) AS Neto2";
    } else {

        $comoSumo = "SUM( 	
            IF(
                    stock.TipoComp ='Venta' OR
                    stock.TipoComp ='Venta TPV' 			
                    OR stock.TipoComp ='ND Anul NC'
                    ,
                    stock.PrecioNetoxR,
                    stock.PrecioNetoxR * -1
            )	
        ) AS Neto,
        SUM( IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV'                 
                OR stock.TipoComp ='ND Anul NC'
        #,(stock.PrecioCostoxU*stock.Cantidad),(stock.PrecioCostoxU*stock.Cantidad) * -1)
        ,(stock.PrecioCostoxR),(stock.PrecioCostoxR) * -1)
        ) AS Costo,
        SUM( IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV'                 
                 OR stock.TipoComp ='ND Anul NC'
       # ,stock.PrecioVentaxU*stock.Cantidad ,(stock.PrecioVentaxU*stock.Cantidad) * -1)
        ,stock.PrecioVentaxR ,(stock.PrecioVentaxR) * -1)
        ) AS Venta,
        SUM(                
            IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV'                                 
                 OR stock.TipoComp ='ND Anul NC'
                ,
                stock.PrecioNetoxR-(stock.PrecioCostoxR),
                #stock.PrecioNetoxR-(stock.PrecioCostoxU*stock.Cantidad),
                (stock.PrecioNetoxR-(stock.PrecioCostoxR))* -1
                #(stock.PrecioNetoxR-(stock.PrecioCostoxU*stock.Cantidad))* -1
            )                
         ) AS Utilidad,
        
        SUM(
            IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV'                          
                 OR stock.TipoComp ='ND Anul NC'
                ,
                stock.PrecioNetoxR ,
                (stock.PrecioNetoxR * -1)
            )
        )
        / 
        SUM( 
            IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV'                         
                 OR stock.TipoComp ='ND Anul NC'
                ,
                #(stock.PrecioCostoxU*stock.Cantidad),
                #((stock.PrecioCostoxU*stock.Cantidad) * -1)
                (stock.PrecioCostoxR),
                ((stock.PrecioCostoxR) * -1)
            ) 
        )
        AS PorUtil";
    }


    /*campos a listar*/
    switch ($listarPor) {
        case 'cliente':
            if ($usaIdManual == 'Si') {

                $primerAgrupo = "cli.id_manual_cli AS cod,CONCAT(cli.nombre_cliente,' (Cod: ',cli.id_manual_cli,')')  As nom,";
                $agrupar .= "cli.Codigo";
                $orderby .= "cli.nombre_cliente ASC, ";
            } else {
                $primerAgrupo = "cli.Codigo AS cod,CONCAT(cli.nombre_cliente,' (Cod: ',cli.Codigo,')')  As nom,";
                $agrupar .= "cli.Codigo";
                $orderby .= "cli.nombre_cliente ASC, ";
            }

            break;
        case 'tipocliente':
                $primerAgrupo = "tpcli.IDTipoCliente AS cod,tpcli.NombreTipoCliente  As nom,";
                $agrupar .= "tpcli.IDTipoCliente";
                $orderby .= "tpcli.NombreTipoCliente ASC, ";
                break;    
        case 'vendedor':
            $primerAgrupo = "vend.CodViajante AS cod,vend.Nombre  As nom,";
            $agrupar .= "vend.CodViajante";
            $orderby .= "vend.Nombre ASC, ";
            break;
        case 'articulo':
            $primerAgrupo = "arti.IDArt AS cod,arti.NombreArticulo  As nom,";
            $agrupar .= "arti.IDArt";
            $orderby .= "arti.NombreArticulo ASC, ";
            break;
        case 'proveedor':
            $primerAgrupo = " prov.Codigo AS cod,prov.Nombre As nom,";
            $agrupar .= "prov.Codigo";
            $orderby .= " prov.Nombre ASC, ";
            break;
        case 'zona':
            $primerAgrupo = " zonas.id_zona AS cod,zonas.nombre_zona As nom,";
            $agrupar .= "zonas.id_zona";
            $orderby .= " zonas.nombre_zona ASC, ";

            break;
        case 'categoria':
            $primerAgrupo = " cat.id_categoria AS cod, cat.nombre_categoria AS nom,";
            $agrupar .= "cat.id_categoria";
            $orderby .= " cat.nombre_categoria ASC, ";
            break;
        case 'rubro':
            $primerAgrupo = " ru.CodigoRubro AS cod, ru.NombreRubro AS nom,";
            $agrupar .= "ru.CodigoRubro";
            $orderby .= " ru.NombreRubro ASC, ";
            break;
        case 'subrubro':
            $primerAgrupo = "srub.IdSubRubro AS cod,srub.NombreSubRubro As nom"
                . ",ru.CodigoRubro AS cod3, ru.NombreRubro AS nom3,";
            $agrupar .= "srub.IdSubRubro";
            $orderby .= " ru.NombreRubro ASC, srub.NombreSubRubro ASC, ";
            break;
        case 'marca':
            $primerAgrupo = " marca.CodMarca AS cod, marca.NombreMarca AS nom,";
            $agrupar .= "marca.CodMarca";
            $orderby .= " marca.NombreMarca ASC, ";
            break;
    }

    /* punto de venta
     * reviso el punto de venta si elegi la opcion todos en algun
     * parametro borro toda seleccion, si no , con el explode me queda
     * todo guardado. 
     *      */
    //    print_r($puntoVenta);
    //    var_dump(empty($puntoVenta));
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
    foreach ($filtrarPor as $ff) {
        $datoFiltro = explode("|", $ff);
        if (!empty($datoFiltro[0])) {
            $arrFiltros[$datoFiltro[0]][] = $datoFiltro[1];
        }
    }
    // aplico supervisor de venta
    $aplicoSupVentas = 0;

    foreach ($arrFiltros as $clave => $fi) {
        switch ($clave) {
            case 'cliente':
                if ($usaIdManual == 'Si') {
                    // no puedo volver a agrupar.
                    if ($listarPor != 'cliente') {
                        $primerAgrupo .= 'cli.id_manual_cli AS cod2,cli.nombre_cliente  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND cli.id_manual_cli IN (' . implode(",", $fi) . ")";
                    }
                } else {

                    // no puedo volver a agrupar.
                    if ($listarPor != 'cliente') {
                        $primerAgrupo .= 'cli.Codigo AS cod2,cli.nombre_cliente  As nom2,';
                    }
                    // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                    // donde puede tener un valor o muchos.

                    if (!in_array("todos", $fi)) {
                        $where  .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                    }
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
                    $aplicoSupVentas++;
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
            case 'categoria':
                // no puedo volver a agrupar.
                if ($listarPor != 'categoria') {
                    $primerAgrupo .= 'cat.id_categoria AS cod2, cat.nombre_categoria AS nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND cat.id_categoria IN (' . implode(",", $fi) . ")";
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
                    $primerAgrupo .= 'marca.CodMarca AS cod2, marca.NombreMarca AS nom2,';
                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND marca.CodMarca IN (' . implode(",", $fi) . ")";
                }

                break;
        }
    }

    // validar supervisor de venta solo puede ver sus vendedores.
    if ($supVenta == "Si" && $aplicoSupVentas == 0 && !empty($vendACargo)) {
        $vv = $vendACargo;

        $where .= ' AND vend.CodViajante IN (' . implode(",", $vv) . ') ';
    }

    /* armar el rango de fechas.*/
    /* */
    $rangoFecha = " (stock.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
    if ($rangoDoble == 1) {
        $rangoFecha .= " OR (stock.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
    }

    /*armando de sql*/
    $sql = "SELECT
            {$primerAgrupo}
            {$segundoAgrupo}
              
            {$comoSumo} 
            FROM stock
                LEFT JOIN cuentacliente AS cc ON (cc.CodigoMovimiento= stock.CodigoMovimiento) 
                LEFT JOIN articulo AS arti ON arti.IDArt = stock.IDArt              
                LEFT JOIN rubro AS ru ON ru.CodigoRubro = arti.CodigoRubro
                LEFT JOIN rubro_categoria AS cat ON cat.id_categoria=ru.id_categoria
                LEFT JOIN subrubro AS srub ON srub.IDSubRubro = arti.IDSubRubro
                LEFT JOIN marca ON marca.CodMarca=arti.CodigoMarca
                LEFT JOIN proveedor AS prov ON prov.Codigo = arti.CodigoProveedor
                LEFT JOIN cliente AS cli ON (cli.Codigo= stock.CodigoCP)                
                #LEFT JOIN viajantes AS vend ON (vend.CodViajante= cli.CodViajante)
                LEFT JOIN viajantes AS vend ON (vend.CodViajante= cc.CodViajante)
                LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
                LEFT JOIN punto_venta AS ppv ON ( ppv.id_punto_venta=cc.id_pv)
                LEFT JOIN tipo_cliente AS tpcli ON(tpcli.IDTipoCliente=cli.TipoCliente)
           WHERE
                ({$rangoFecha})
               
               AND stock.Anulado='No'
               AND stock.visualiza_ensamble='No' 
               
                AND (stock.TipoComp = 'Venta' 
                    OR stock.TipoComp = 'Venta TPV' 
                    OR stock.TipoComp = 'Devol - Cliente' 
                    OR stock.TipoComp = 'ND Anul NC'
                    )
                 {$where}    
                 GROUP BY {$agrupar} "            
        . "ORDER BY {$orderby} stock.Fecha ASC";
    //            echo "ventas<pre>";
    //            echo $sql;
    //GROUP BY {$agrupar} HAVING Costo <>0 "

    return $sql;
}
function getCabeceraVentas($arrP, $periodo, $listarPor, $arrFiltros, $tipo, $arrResultado, $operacionRango)
{
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $localidad = setlocale(LC_TIME, NULL); #Guarda localización regional actual
    setlocale(LC_TIME, 'es_AR'); # Localiza en español es_Cenezuela

    $cabecera = array();
    $cabeceraT = array();
    $totalFila = 0;
    $totalGral = 0;
    $tituloFil = "";
    //$renglon[0][] = "Ventas";

    $mes = 0;
    $aa = 0;

    //listar por lo coloco en el primer titulo
    switch ($listarPor) {
        case 'cliente':
            $titulo[0] = array("titulo" => "Cliente", "span" => 2, "rowspan" => 1);
            break;
        case 'tipocliente':
            $titulo[0] = array("titulo" => "Tipo Cliente", "span" => 2, "rowspan" => 1);
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
        case 'categoria':
            $titulo[0] = array("titulo" => "Categoría", "span" => 2, "rowspan" => 1);
            break;
        case 'rubro':
            $titulo[0] = array("titulo" => "Rubro", "span" => 2, "rowspan" => 1);
            break;
        case 'subrubro':
            $titulo[0] = array("titulo" => "Sub Rubro - Rubro", "span" => 2, "rowspan" => 1);
            break;
        case 'marca':
            $titulo[0] = array("titulo" => "Marca", "span" => 2, "rowspan" => 1);
            break;
        case 'usuario':
            $titulo[0] = array("titulo" => "Usuario", "span" => 2, "rowspan" => 1);    
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
            case 'categoria':
                // no puedo volver a agrupar.
                $tituloFil .= " - Categoría: " . implode(",", $fil);
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
        case "pieza":
            $tituloTipo = "PIEZA";
            break;
    }

    if (empty($arrFiltros)) {
        $titulo[1] = array("titulo" => "{$tituloTipo} x {$periodo} ", "span" => 2, "rowspan" => 1);
    } else {
        $titulo[1] = array("titulo" => "{$tituloTipo} x {$periodo}  /  {$tituloFil}", "span" => 2, "rowspan" => 1);
    }


    /**
     * CABECERAS TH de las tablas.
     */
    foreach ($arrP as $campo) {
        //aca tengo que ver el tema de agrupacion si agrupo los nombres por rango
        // dependendiendo de la operacion donde vamos a colocar el texto.
        /* evaluo operacion*/
        if ($operacionRango == "suma") {
            // operacion suma
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
    }
    //        print_r($totalGral);
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
        // este calculo tambien hay que cambiarlo si es agrupacion.
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

    $vuelta = array("titulo" => $titulo, "cabecera" => $cabeceraTT, "ith" => $ith);
    //        print_r($vuelta);
    return $vuelta;
}

function getDatosVentas($arrP, $arrResultado, $ith, $cabeceraT, $arrayNc, $operacionRango, $traigoArrayNc, $decimales)
{
    $rubro = null;
    $totalFila = 0;
    if ($decimales) {
        $redondeo = $decimales;
    } else {
        $redondeo = 0;
    }
    if ($operacionRango == "suma") {
        /*
         * Codigo para armar el arrP como lo necesito
         * **/
        //        echo "NC <pre>";
        //        print_r($arrayNc);
        //        echo "</pre>";
        /*
         * Operacion de Suma
         */
        /**
         *  solucionar las diferencias, con las notas de credito hacer un rango de fechas y recorrerlas 
         * para encontrar los rangos, creo el espacio por mes o por periodo y lo hago con 
         * un array , entonces al cambiar de codigo o cliente ahi busco si hay nc y ventas.
         */
        /* buscando el rango en php*/
            //    echo "\CAMPO <pre>";
            //    print_r($arrResultado);
            //    echo "</pre>";
        foreach ($arrResultado as $idC => $campo) {
            if ($campo["nom"] != null) {
                $importeNc = 0;
                $valorCelda = 0;
                //            echo var_dump($campo["nom"]==null)."<br>";
                $valorTotalCampo = round($campo["total"], $redondeo);
                // buscar la notas de credito desde el array solo si correspondde.
                // valor de operacion rango
                //            print_r($campo);
                $icampo = intval($campo[$ith[0]] . $campo[$ith[1]]);
                if ($rubro == null) {
                    $rubro = $campo["cod"];
                    //tabla
                    $renglon[$rubro][] = getSubString($campo["nom"]);
                    //agregar rubro o subrubro o categoria anexado.
                    //                   if(isset($campo["nom2"])){
                    //                       $renglon[$rubro][2]= getSubString($campo["nom2"]);
                    //                   }
                    //                   if(isset($campo["nom3"])){
                    //                       $renglon[$rubro][3]= getSubString($campo["nom3"]);
                    //                   }

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
                                $arrValor = array("cod" => $rubro, $ith[0] => $campo[$ith[0]], $ith[1] => $campo[$ith[1]]);
                                //                              echo "ha key {";
                                //                                        print_r($key);
                                //                                        echo "} idc{";
                                //                                        print_r($idC);
                                //                                        echo "}...0000\n";
                                if (isset($arrayNc[$idC]["total"])) {
                                    //                                $keyNc = multidimensional_search($arrayNc, $arrValor);
                                    //                                    echo "valor nc {";
                                    $importeNc = round($arrayNc[$idC]["total"], $redondeo);
                                    //                                    echo $arrayNc[$idC]["total"];
                                    //                                    echo "}\n<br>";
                                } else {
                                    $importeNc = 0;
                                }
                            }

                            $renglon[$rubro][$icampo] = $valorTotalCampo + $importeNc;

                            // localizar en el array nc las nc.
                        } else {
                            // la clave no existe pero puede que haya una nota de credito.
                            if ($traigoArrayNc == 0) {
                                $arrValor = array("cod" => $rubro, $ith[0] => $arrP[$key][$ith[0]], $ith[1] => $arrP[$key][$ith[1]]);
                                //                                echo "ha key {";
                                //                                        print_r($key);
                                //                                        echo "} idc{";
                                //                                        print_r($idC);
                                //                                        echo "}...aaa\n";
                                if (isset($arrayNc[$key]["total"])) {
                                    //                                $keyNc = multidimensional_search($arrayNc, $arrValor);
                                    $importeNc = round($arrayNc[$key]["total"], $redondeo);
                                } else {
                                    $importeNc = 0;
                                }
                            }
                            //                            echo "cod={".$campo["cod"]."} quien={".$campo["nom"]."} idc={".$idC."} valor:{".$campo["total"] ."} nc:{". $importeNc."}\n";
                            $renglon[$rubro][$key] = 0 + $importeNc;
                        }
                    }

                    // corroborar la operacion a realizar si es suma o resta segun el rango doble.



                } else {
                    // SEGUNDO RENGLON 
                    //                    echo "SEGUNDO RENGLOIN\n";
                    if ($rubro !== $campo["cod"]) {
                        //CAMBIO DE CLIENTE O DE CODIGO
                        //                       echo "CAMBIO DE CLIENTE\n";
                        // // sumatoria de columnas
                        //                       if($campo["cod"]==3011){
                        //                           echo "<pre>1.-aparece 3011:".$campo["cod"]." primera vez vez<br>Rubro:";
                        //                           print_r($rubro);
                        //                           echo "<br>Campo:";
                        //                           print_r($campo);
                        //                           echo "</pre>====================================<br>";
                        //                       }
                        $totalFila = 0;
                        foreach ($renglon[$rubro] as $cla => $val) {
                            if ($cla != 0) {
                                $totalFila += $val;
                            }
                        }
                        $renglon[$rubro]["subt"] = $totalFila;
                        $totalFila = 0;
                        //                       if($rubro==3011){
                        //                           echo "<pre>2.Nuevo Rubro  <br>Rubro Viejo:".$rubro."<br>Rubro Nuevo:".$campo["cod"]."<br>";
                        //                           print_r($renglon[$rubro]);
                        //                           echo "<br>Campo:<br>";
                        //                           print_r($campo);
                        //                           echo "</pre>===============================================<br>";
                        //                       }
                        $rubro = $campo["cod"];

                        //                           echo "<pre>3. Rubro Cambiado:";
                        //                           print_r($rubro);
                        //                           echo "<br>Campo actual:<br>";
                        //                           print_r($campo);
                        //                           echo "<br>REnglon con rubro Nuevo:<br>";
                        //                           print_r( $renglon[$rubro]);
                        //                           echo "</pre>==========================";

                        $renglon[$rubro][0] = $campo["nom"];
                        //                        if (isset($campo["nom2"])) {
                        //                            $renglon[$rubro][1] = getSubString($campo["nom2"]);
                        //                        }
                        //                        if (isset($campo["nom3"])) {
                        //                            $renglon[$rubro][2] = getSubString($campo["nom3"]);
                        //                        }
                        $valorTotalCampo = round($campo["total"], $redondeo);

                        // cabeceras
                        foreach ($cabeceraT as $key => $ca) {

                            if ($icampo === $key) {

                                if ($traigoArrayNc == 0) {

                                    //                                echo "ha key {";
                                    //                                        print_r($key);
                                    //                                        echo "} idc{";
                                    //                                        print_r($idC);
                                    //                                        echo "}...bbb\n";
                                    if (isset($arrayNc[$idC]["total"])) {

                                        $importeNc = round($arrayNc[$idC]["total"], $redondeo);
                                    } else {
                                        $importeNc = 0;
                                    }
                                }
                                //                               echo "cod={".$campo["cod"]."} quien={".$campo["nom"]."} idc={".$idC."} valor:{".$campo["total"] ."} nc:{". $importeNc."}\n";
                                $renglon[$rubro][$icampo] = $valorTotalCampo + $importeNc;
                            } else {



                                if (!isset($renglon[$rubro][$key])) {

                                    if ($traigoArrayNc == 0) {

                                        $idCnuevo = intval($key . $rubro);
                                        //                                        echo "ha key {";
                                        //                                        print_r($key);
                                        //                                        echo "} idc{";
                                        //                                        print_r($idC);
                                        //                                        echo "} idcnuevo{";
                                        //                                        print_r($idCnuevo);
                                        //                                        echo "}...ccc\n";
                                        if (isset($arrayNc[$idCnuevo]["total"])) {
                                            //                                $keyNc = multidimensional_search($arrayNc, $arrValor);
                                            $importeNc = round($arrayNc[$idCnuevo]["total"], $redondeo);
                                        } else {
                                            $importeNc = 0;
                                        }
                                    }
                                    //                                    echo "cod={".$campo["cod"]."} quien={".$campo["nom"]."} idc={".$idC."} valor:{".$campo["total"] ."} nc:{". $importeNc."}\n";
                                    $renglon[$rubro][$key] = 0 + $importeNc;
                                }
                            }
                            //                           echo var_dump($renglon[$rubro][$key]);
                        }
                    } else {
                        //* SOY EL MISMO CLIENTE O CODIGO PERO DIFERENTE PERIODO
                        //                       echo "OTRO RENGLIN MISMO CLIENTE\n";
                        $valorTotalCampo = round($campo["total"], $redondeo);
                        // cabeceras
                        foreach ($cabeceraT as $key => $ca) {
                            if ($icampo === $key) {
                                //$importeNc = 0;
                                if ($traigoArrayNc == 0) {
                                    $arrValor = array("cod" => $rubro, $ith[0] => $campo[$ith[0]], $ith[1] => $campo[$ith[1]]);
                                    //                                        echo "ha key {";
                                    //                                        print_r($key);
                                    //                                        echo "} idc{";
                                    //                                        print_r($idC);
                                    //                                        echo "}...dddd\n";
                                    if (isset($arrayNc[$idC]["total"])) {
                                        //                                $keyNc = multidimensional_search($arrayNc, $arrValor);
                                        $importeNc = round($arrayNc[$idC]["total"], $redondeo);
                                    } else {
                                        $importeNc = 0;
                                    }
                                }
                                //                            echo "cod={".$campo["cod"]."} quien={".$campo["nom"]."} idc={".$idC."} valor:{".$campo["total"] ."} nc:{". $importeNc."}\n";
                                $renglon[$rubro][$icampo] = $valorTotalCampo + $importeNc;
                            }
                            //                           else{
                            //                               
                            //                               if(empty($renglon[$rubro][$key])){
                            //                                    if($traigoArrayNc==0){ 
                            //                                        $arrValor = array("cod"=> $rubro, $ith[0]=> $arrP[$key][$ith[0]],$ith[1] => $arrP[$key][$ith[1]]);
                            //                                echo "ha key {";
                            //                                        print_r($key);
                            //                                        echo "} idc{";
                            //                                        print_r($idC);
                            //                                        echo "}...eee\n";
                            //                                        if(isset($arrayNc[$idC]["total"])){
                            ////                                $keyNc = multidimensional_search($arrayNc, $arrValor);
                            //                                            $importeNc = $arrayNc[$idC]["total"];
                            //                                        }else{
                            //                                            $importeNc=0;
                            //                                        }  
                            //                                    }
                            ////                                    echo "cod={".$campo["cod"]."} quien={".$campo["nom"]."} idc={".$idC."} valor:{".$campo["total"] ."} nc:{". $importeNc."}\n";
                            //                                    $renglon[$rubro][$key] = 0 + $importeNc;
                            //                                    
                            //                                }
                            //                           } 
                        }
                    }
                } // if de si es primero o segundo intento.
            } // end if de renglones vacios...que no falle..
        } // fin del foreach todo los renglones  

        $totalFila = 0;
        foreach ($renglon[$rubro] as $cla => $val) {
            if ($cla != 0) {
                $totalFila += $val;
            }
        }

        $renglon[$rubro]["subt"] = $totalFila;
    } else {
        /*
         * Operacion Suma agrupada y Resta
         * =====================================================================
         */
        //                echo "resultado:=>{<pre>";
        //                print_r($arrResultado);
        //                echo "</pre>}\n";

        foreach ($arrResultado as $idC => $campo) {
            if ($campo["nom"] != null) {

                $importeNc = 0;
                $valorCelda = 0;
                // buscar la notas de credito desde el array solo si correspondde.
                // valor de operacion rango
                //            echo "idC:=>{";
                //                echo print_r($idC);
                //                echo "}\n";
                $icampo = intval($campo[$ith[0]]);
                if ($rubro === null) {
                    //                echo "primer renglon::<pre>";
                    //                print_r($campo);
                    //                echo "</pre><br>";
                    $rubro = $campo["cod"];
                    //tabla
                    $renglon[$rubro][0] = getSubString($campo["nom"]);
                    //               if (isset($campo["nom2"])) {
                    //                    $renglon[$rubro][1] = getSubString($campo["nom2"]);
                    //                }
                    //                if (isset($campo["nom3"])) {
                    //                    $renglon[$rubro][2] = getSubString($campo["nom3"]);
                    //                }
                    //grafico
                    //               $arrayChart["cols"][]= array(
                    //                                       "id"=>$rubro,
                    //                                       "label"=>$campo["nom"],
                    //                                       "type"=>"number"
                    //               );
                    //recorro las cabeceras y coloco lo que hace falta
                    foreach ($cabeceraT as $key => $ca) {
                        if ($icampo === $key) {
                            //controlo si hay nc para buscar datos

                            if ($traigoArrayNc == 0) {
                                $arrValor = array("cod" => $rubro, $ith[0] => $campo[$ith[0]]);

                                if (isset($arrayNc[$idC]["total"])) {
                                    //                                $keyNc = multidimensional_search($arrayNc, $arrValor);
                                    $importeNc = round($arrayNc[$idC]["total"], $redondeo);
                                } else {
                                    $importeNc = 0;
                                }
                            }

                            $renglon[$rubro][$icampo] = round($campo["total"], $redondeo) + $importeNc;
                            //                       echo "cod={".$campo["cod"]."} quien={".$campo["nom"]."} idc={".$idC."} valor:{".$campo["total"] ."} nc:{". $importeNc."}\n";
                            //                       print_r($renglon[$rubro][$icampo]);
                            // localizar en el array nc las nc.
                        } else {

                            if (!isset($renglon[$rubro][$key])) {
                                $renglon[$rubro][$key] = 0;
                            }
                        }
                    }
                    //echo var_dump($operacionRango);
                    // corroborar la operacion a realizar si es suma o resta segun el rango doble.
                    //               echo "\n Rng0:".$campo["nom"]."  importeNC:=>".$importeNc." importeTotal:=>".$campo["total"];
                    //               echo "\n";

                    $totalFila += round($campo["total"], $redondeo) + $importeNc;
                } else {
                    if ($rubro != $campo["cod"]) {
                        //                   echo "segundo renglon cambio de vendedor::<pre>";
                        //                print_r($campo);
                        //                echo "</pre><br>";
                        $renglon[$rubro]["subt"] = $totalFila;
                        //                echo "rngPPP <pre>";
                        //                print_r($renglon);
                        //                echo "</pre>";
                        $rubro = $campo["cod"];
                        $renglon[$rubro][0] = $campo["nom"];
                        //                   if (isset($campo["nom2"])) {
                        //                        $renglon[$rubro][1] = getSubString($campo["nom2"]);
                        //                    }
                        //                    if (isset($campo["nom3"])) {
                        //                        $renglon[$rubro][2] = getSubString($campo["nom3"]);
                        //                    }
                        //                     $renglon[$rubro][]= getSubString($campo["nom"]);
                        //                   $arrayChart["cols"][]= array(
                        //                                       "id"=>$rubro,
                        //                                       "label"=>$campo["nom"],
                        //                                       "type"=>"number"
                        //                   );                                       
                        // cabeceras
                        foreach ($cabeceraT as $key => $ca) {
                            if ($icampo === $key) {

                                if ($traigoArrayNc == 0) {
                                    //                               $arrValor = array("cod"=> $rubro, 
                                    //                                                        $ith[0]=> $campo[$ith[0]]
                                    //                                                        );

                                    if (isset($arrayNc[$idC]["total"])) {
                                        //                                $keyNc = multidimensional_search($arrayNc, $arrValor);
                                        $importeNc = round($arrayNc[$idC]["total"], $redondeo);
                                    } else {
                                        $importeNc = 0;
                                    }
                                }

                                $renglon[$rubro][$icampo] = round($campo["total"], $redondeo) + $importeNc;
                                //                           echo "cod={".$campo["cod"]."} quien={".$campo["nom"]."} idc={".$idC."} valor:{".$campo["total"] ."} nc:{". $importeNc."}\n";
                            } else {
                                //$renglon[$rubro][$key]=0;
                                if (!isset($renglon[$rubro][$key])) {
                                    $renglon[$rubro][$key] = 0;
                                }
                            }
                        }

                        $totalFila = round($campo["total"], $redondeo) + $importeNc;
                    } else {
                        // echo "otro renglon mismo vendedor::<pre>";

                        // cabeceras
                        foreach ($cabeceraT as $key => $ca) {
                            if ($icampo === $key) {
                                $importeNc = 0;
                                if ($traigoArrayNc == 0) {
                                    //$arrValor = array("cod"=> $rubro, $ith[0]=> $campo[$ith[0]]);
                                    if (isset($arrayNc[$idC]["total"])) {
                                        //                                $keyNc = multidimensional_search($arrayNc, $arrValor);
                                        $importeNc = round($arrayNc[$idC]["total"], $redondeo);
                                    } else {
                                        $importeNc = 0;
                                    }
                                }

                                $renglon[$rubro][$icampo] = round($campo["total"], $redondeo) + $importeNc;
                                //                           echo "cod={".$campo["cod"]."} quien={".$campo["nom"]."} idc={".$idC."} valor:{".$campo["total"] ."} nc:{". $importeNc."}\n";
                            }
                        }

                        $totalFila += round($campo["total"], $redondeo) + $importeNc;
                    }
                }
            } // fin del if renglon con null
        }   // fin del foreach pero con la resta.
        //        $renglon[$rubro]["subt"]= number_format($totalFila,2,".","");
        //         echo "antes <pre>";
        //                print_r($renglon);
        //                echo "</pre>";
        $renglon[$rubro]["subt"] = $totalFila;
        //         echo "despues <pre>";
        //                print_r($renglon);
        //                echo "</pre>";

    }


    /*
     * Operacion de diferencia hago las restas correspondientes
     */
    if ($operacionRango == "resta") {
        //        echo "renglon::<pre>";
        //                    print_r($renglon);
        //                    echo "</pre><br>";
        foreach ($renglon as $k => $r) {
            //$suma=0;
            $vuelta = 0;
            //             echo "r::<pre>";
            //                    print_r($r);
            //                    echo "</pre><br>";
            //            foreach($r as $kk =>$valor){
            //                   
            //                if($kk!=0&&$kk!="subt"){
            //                    if($vuelta==0){
            //                        $suma = $suma - $valor;
            //                    }else{
            //                        $suma = $suma +$valor;
            //                    }
            //                    $vuelta++;
            //                }
            //            }
            $suma = $r[2] - $r[1];
            $renglon[$k]["subt"] = round($suma, $redondeo);
        }
    }
    //     echo "renglon fixed::<pre>";
    //                    print_r($renglon);
    //                    echo "</pre><br>";
    return $renglon;
}
function armarGraficos()
{
    $rng = 0;
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
}
function getCabeceraUtilidad(
    $desde,
    $hasta,
    $periodo,
    $listarPor,
    $arrFiltros,
    $tipo,
    $arrResultado,
    $rangoDoble = null,
    $desdeDos = null,
    $hastaDos = null
) {
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $localidad = setlocale(LC_TIME, NULL); #Guarda localización regional actual
    setlocale(LC_TIME, 'es_AR'); # Localiza en español es_Cenezuela
    $desdT =  implode("/", array_reverse(explode("-", $desde)));
    $hastaT = implode("/", array_reverse(explode("-", $hasta)));
    if ($rangoDoble == 1) {
        $desdeDosT = implode("/", array_reverse(explode("-", $desdeDos)));
        $hastaDosT = implode("/", array_reverse(explode("-", $hastaDos)));
    }
    $cabecera = array();
    $cabeceraT = array();
    $totalFila = 0;
    $totalGral = 0;
    $tituloFil = "";
    //$renglon[0][] = "Ventas";

    $mes = 0;
    $aa = 0;

    //listar por lo coloco en el primer titulo
    switch ($listarPor) {
        case 'cliente':
            $titulo[0] = array("titulo" => "Cliente", "span" => 2, "rowspan" => 1);
            break;
        case 'tipocliente':
                $titulo[0] = array("titulo" => "Tipo Cliente", "span" => 2, "rowspan" => 1);    
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
        case 'categoria':
            $titulo[0] = array("titulo" => "Categoría", "span" => 2, "rowspan" => 1);
            break;
        case 'rubro':
            $titulo[0] = array("titulo" => "Rubro", "span" => 2, "rowspan" => 1);
            break;
        case 'subrubro':
            $titulo[0] = array("titulo" => "Sub Rubro - Rubro", "span" => 2, "rowspan" => 1);
            break;
        case 'marca':
            $titulo[0] = array("titulo" => "Marca", "span" => 2, "rowspan" => 1);
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
            case 'categoria':
                // no puedo volver a agrupar.
                $tituloFil .= " - Categoria: " . implode(",", $fil);
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

    $tituloTipo = "UTILIDADES NETAS";

    if (empty($arrFiltros)) {
        $titulo[1] = array("titulo" => "{$tituloTipo} x Rango ", "span" => 2, "rowspan" => 1);
    } else {
        $titulo[1] = array("titulo" => "{$tituloTipo} x Rango  /  {$tituloFil}", "span" => 2, "rowspan" => 1);
    }


    /**
     * CABECERAS TH de las tablas.
     */
    if ($rangoDoble == 1) {
        $cabecera = array(
            array("th" => "Venta Neta"),
            array("th" => "Desc"),
            array("th" => "Venta Neta Desc"),
            array("th" => "Costo"),
            array("th" => "Utilidad"),
            array("th" => "Utilidad%"),
            array("th" => "Venta Inf"),
            array("th" => "Desc Inf"),
            array("th" => "Inflacion"),
            array("th" => "Venta Esp"),
            array("th" => "Resultado"),
        );
        //print_r($totalGral);
        $cabeceraTT = array(0 => "Venta", 1 => "Desc", 2 => "Venta Neta", 3 => "Costo", 4 => "Utilidad", 5 => "Utilidad %", 6 => "Venta Ant", 7 => "Desc Ant", 8 => "Indice", 9 => "Venta Esp", 10 => "Resultado");
        // este indice ith, es para guardar las claves de combinacion de dia -mes, semana- mes, aa-mes

    } else {
        $cabecera = array(
            array("th" => "Venta Neta"),
            array("th" => "Desc"),
            array("th" => "Venta Neta Desc"),
            array("th" => "Costo"),
            array("th" => "Utilidad"),
            array("th" => "Utilidad%"),

        );
        //print_r($totalGral);
        $cabeceraTT = array(0 => "Venta", 1 => "Desc", 2 => "Venta Neta", 3 => "Costo", 4 => "Utilidad", 5 => "Utilidad %");
        // este indice ith, es para guardar las claves de combinacion de dia -mes, semana- mes, aa-mes

    }
    /* evaluo operacion */

    $ith = array("rango");
    $cadTitulo = "Rango";
    /*
         * TITULOS
         */
    // recorro las cabeceras y tengo que obtener el mes o dia u año segun lo que tenga.
    // en el periodo para armar los titulos .

    $colspan = count($cabeceraTT);
    $col = 0;
    //        print_r($titulo);


    $colspan = 6;

    // suma agregada y diferencia.
    $textoT =  "Rango " . $desdT . " al " . $hastaT;
    $titulo[] = array("titulo" => $textoT, "span" => $colspan, "rowspan" => 1);
    // inflacion
    if ($rangoDoble == 1) {
        $textoT =  "Anterior " . $desdeDosT . " al " . $hastaDosT;
        $titulo[] = array("titulo" => $textoT, "span" => $colspan, "rowspan" => 1);
    }
    //$titulo[] = array("titulo" => "utilidad","span" => 1,"rowspan"=>2);


    $vuelta = array("titulo" => $titulo, "cabecera" => $cabeceraTT, "ith" => $ith);
    return $vuelta;
}

function getDatosUtilidad($arrResultado, $ith, $cabeceraT, $arrayNc, $operacionRango, $traigoArrayNc, $indiceInflacion = null, $arrayNcInf)
{
    $rubro = "";
    $totalFila = 0;
    $renglon = array();
    /*
     * Operacion Suma agrupada y Resta
     */
    //    print_r($arrResultado);
    foreach ($arrResultado as $campo) {

        $rubro = $campo["cod"];
        $importeNc = 0;
        $importeNcInf = 0;
        $valorCelda = 0;
        // buscar la notas de credito desde el array solo si correspondde.
        // valor de operacion rango

        if ($traigoArrayNc == 0 && !empty($arrayNc)) {
            $arrValor = array("cod" => $rubro);
            $keyNc = multidimensional_search($arrayNc, $arrValor);
            if ($arrayNcInf != null) {
                $keyNcInf = multidimensional_search($arrayNcInf, $arrValor);
                $importeNcInf = $arrayNcInf[$keyNcInf]["importe"];
            }
            //            echo "<pre>";
            //            print_r($keyNc);
            //            echo "</pre><br><pre>";
            $importeNc = $arrayNc[$keyNc]["importe"];

            //            print_r($importeNc);
            //            echo "</pre><br>";
        }

        //$renglon[$rubro][$icampo] = $campo["total"] + $importeNc;
        //
        //// localizar en el array nc las nc.
        //                   
        $renglon[$campo["cod"]][] = $campo["nom"];
        $renglon[$campo["cod"]][] = $campo["Neto"];
        $renglon[$campo["cod"]][] = $importeNc;

        $renglon[$campo["cod"]][] = $campo["Neto"] + $importeNc;
        $renglon[$campo["cod"]][] = $campo["Costo"];
        $renglon[$campo["cod"]][] = $campo["Utilidad"] + $importeNc;
        //        $renglon[$campo["cod"]]["porc"] =  $campo["PorUtil"];
        if($campo["Costo"]!=0){
            $renglon[$campo["cod"]]["porc"] = ($campo["Neto"] + $importeNc) / $campo["Costo"];
        }
        if($campo["Costo"]==0){
            $renglon[$campo["cod"]]["porc"] = 0;
        }
        //inflacion 
        // 1 - busco si hay coincidencia en la clave...
        $vIndiceInflacion = 1;
        if (!empty($indiceInflacion)) {
            if (key_exists($campo["cod"], $indiceInflacion)) {
                //$vIndiceInflacion = $indiceInflacion[$campo["cod"]]["indice"];
                $vIndiceInflacion = floatval($indiceInflacion[$campo["cod"]]["indice"]);
                $vIndiceInflacion = round($vIndiceInflacion, 2);
            }
        }
        if ($indiceInflacion != null) {
            $renglon[$campo["cod"]]["venta2"] = $campo["Neto2"];
            $renglon[$campo["cod"]]["nc2"] = $importeNcInf;
            $renglon[$campo["cod"]]["indice"] = $vIndiceInflacion;
            $renglon[$campo["cod"]]["esperada"] = ($campo["Neto2"] + $importeNcInf) * $vIndiceInflacion;
            if (($campo["Neto2"] * $vIndiceInflacion) == 0) {
                $renglon[$campo["cod"]]["resultado"] = 1;
            } else {
                $renglon[$campo["cod"]]["resultado"] = $campo["Neto"] / (($campo["Neto2"] + $importeNcInf) * $vIndiceInflacion);
            }
        }
    }
    //        $renglon[$rubro]["subt"]= number_format($totalFila,2,".","");
    //$renglon[$rubro]["subt"]= $totalFila;



    return $renglon;
}

function armar_sql_nc_utlidad(
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
    $agrupar = "";

    $nombreRango = "";
    $numeroRango = "";


    /* PERIODO DE AGRUPACION, RANGO Y AGRUPACION DE PERIODOS*/
    /* Periodo, segun el tipo de operacin si es suma rango o resta */

    /*PERIODO*/
    //        switch($periodo){
    //            case "dia":
    //                $agrupar = "dia,semana";
    //                break;
    //            case "semana":
    //                $agrupar = "semana,mes";
    //                break;
    //            case "mes":
    //                $agrupar = "mes,aa";
    //                break;
    //        }
    /* armar el rango de fechas.*/
    /* rango de fehcas.*/

    $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
    if ($rangoDoble == 1) {
        $rangoFecha .= " OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
    }


    /*campos a listar*/
    switch ($listarPor) {
        case 'cliente':
            $primerAgrupo = "cc.Codigo AS cod,";
            $agrupar .= "cc.Codigo";
            $orderby .= "cc.Codigo ASC, ";
            break;
        case 'tipocliente':
            $primerAgrupo .= 'tpcli.IDTipoCliente AS cod,tpcli.NombreTipoCliente  As nom,';
            $agrupar .= "tpcli.IDTipoCliente";
            $orderby .= "tpcli.NombreTipoCliente ASC,";
            break;    
        case 'vendedor':
            $primerAgrupo = "vend.CodViajante AS cod,vend.Nombre  As nom,";
            $agrupar .= "vend.CodViajante";
            $orderby .= "vend.Nombre ASC, ";
            break;

        case 'zona':
            $primerAgrupo = " zonas.id_zona AS cod,zonas.nombre_zona As nom,";
            $agrupar .= "zonas.id_zona";
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
    foreach ($filtrarPor as $ff) {

        $datoFiltro = explode("|", $ff);
        //         print_r($datoFiltro);
        if (!empty($datoFiltro[0])) {
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
                    $where  .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'tipocliente':
                // no puedo volver a agrupar.
                if ($listarPor != 'tipocliente') {
                    $primerAgrupo .= ' tpcli.IDTipoCliente AS cod2,tpcli.NombreTipoCliente  As nom2,';
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
        }
    }


    $sql = "SELECT
                {$primerAgrupo}
                {$segundoAgrupo}
                {$nombreRango}
                {$numeroRango}
                           
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
                    )) AS importe
        FROM   cuentacliente AS cc
        LEFT JOIN cliente AS cli ON cli.Codigo = cc.Codigo
        #LEFT JOIN viajantes AS vend ON (vend.CodViajante= cli.CodViajante)
        LEFT JOIN viajantes AS vend ON (vend.CodViajante= cc.CodViajante)
        LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
        LEFT JOIN tipo_cliente AS tpcli ON(tpcli.IDTipoCliente=cli.TipoCliente)
        WHERE
          ({$rangoFecha})       
        AND cc.Anulado ='No'
       
        #AND cc.TipoComprobante IN ('NCA','NCB',                                    'NCE','NCC','NCM','FA','FB','FE','FC','FM')
        AND cc.TipoComprobante IN ('NCA','NCB', 'NCE','NCC','NCM','FA','FB','FE','FC','FM','NDA', 
                                'NDB', 
                                'NDE', 
                                'NDC', 
                                'NDM')
    AND (ISNULL(cc.concepto_nd) OR cc.concepto_nd<>'Anulacion NC - Mercaderia') 

        {$where}
        GROUP BY    
        {$agrupar} ORDER BY {$orderby} cc.`Fecha`";
    //        echo "nc<pre>";
    //        echo $sql;

    return $sql;
}
function traer_valor_nc_utilidad($connV, $puntoVentaId = null, $desde = null, $hasta = null, $periodo, $filtrarPor = null)
{
    // filtrar las notas de credito segun el filtro que teden
    $agrupar = "";
    $usaIdManual = $_SESSION["usa_id_manual"];
    $where = "";
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
    if (!empty($puntoVentaId)) {
        if (!in_array("todos", $puntoVentaId)) {
            $where = " AND cc.id_pv IN (" . implode(",", $puntoVentaId) . ")";
        }
    }
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
        switch ($clave) {
            case 'cliente':
                // no puedo volver a agrupar.

                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.

                if (!in_array("todos", $fi)) {
                    if ($usaIdManual == "Si") {
                        $where  .= ' AND cli.id_manual_cli IN (' . implode(",", $fi) . ")";
                    } else {
                        $where  .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                    }
                }

                break;
            case 'vendedor':

                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND vend.CodViajante IN (' . implode(",", $fi) . ") AND vend.anulado='No'";
                }
                break;

            case 'zona':
                // no puedo volver a agrupar.

                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND  zonas.id_zona IN (' . implode(",", $fi) . ")";
                }

                break;
        }
    }

    /*
     * Rango de fecha
     */
    $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta} ') ";
    //    if($rangoFecha==1){
    //        $rangoFecha .=" OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}')";
    //    }
    $sqlNC = " SELECT 
                
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
        LEFT JOIN cliente AS cli ON cli.Codigo = cc.Codigo
        #LEFT JOIN viajantes AS vend ON (vend.CodViajante= cli.CodViajante)
        LEFT JOIN viajantes AS vend ON (vend.CodViajante= cc.CodViajante)
        LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
     WHERE
      ({$rangoFecha})
      {$where}    
    AND cc.Anulado ='No'
    #AND cc.TipoComprobante IN ('NCA','NCB',
    #                                'NCE','NCC','NCM','FA','FB','FE','FC','FM')
    AND cc.TipoComprobante IN ('NCA','NCB', 'NCE','NCC','NCM','FA','FB','FE','FC','FM','NDA', 
                                'NDB', 
                                'NDE', 
                                'NDC', 
                                'NDM')
    AND (ISNULL(cc.concepto_nd) OR cc.concepto_nd<>'Anulacion NC - Mercaderia') 
     ORDER BY cc.`Fecha` ASC";

    //print_r($sqlNC);
    if(debugSql==true){
        $sqlLog =file_put_contents('log/sql_utilidad'.date('Ymd').'.sql','Traigo Descuentos :'.PHP_EOL.$sqlNC.PHP_EOL,FILE_APPEND);
    }
    $hacerNc = mysqli_query($connV, $sqlNC) or die("no puedo recuperar las notas de C<pre>" . mysqli_error($connV) . $sqlNC) . "</pre>";
    $arrayNc = array();
    while ($nc = mysqli_fetch_assoc($hacerNc)) {
        //$icampo = intval($nc[$ith[0]].$nc[$ith[1]]);
        $arrayNc = $nc["importe"];
    }
    //    print_r($arrayNc);
    return $arrayNc;
}
function indice_inflacion($desde = null, $hasta = null, $desdeDos = null, $hastaDos = null, $filtrarPor = null, $listarPor = null)
{

    // Fecha
    $rangoFecha = " (cc.Fecha BETWEEN '{$desde}' AND '{$hasta}') ";
    $rangoFecha .= " OR (cc.Fecha BETWEEN '{$desdeDos}' AND '{$hastaDos}') ";
    // array de retorno de la inflacion.
    $arrInf = array();


    //listado de listar por

    switch ($listarPor) {
        case 'cliente':
            $primerAgrupo = "cli.Codigo AS cod,CONCAT(cli.nombre_cliente,'(Cod: ',cli.Codigo,')')  As nom,";
            $agrupar .= "cli.Codigo";
            $orderby .= "cli.nombre_cliente ASC, ";
            break;
        case 'vendedor':
            $primerAgrupo = "vend.CodViajante AS cod,vend.Nombre  As nom,";
            $agrupar .= "vend.CodViajante";
            $orderby .= "vend.Nombre ASC, ";
            break;
        case 'articulo':
            $primerAgrupo = "arti.IDArt AS cod,arti.NombreArticulo  As nom,";
            $agrupar .= "arti.IDArt";
            $orderby .= "arti.NombreArticulo ASC, ";
            break;
        case 'proveedor':
            $primerAgrupo = " prov.Codigo AS cod,prov.Nombre As nom,";
            $agrupar .= "prov.Codigo";
            $orderby .= " prov.Nombre ASC, ";
            break;
        case 'zona':
            $primerAgrupo = " zonas.id_zona AS cod,zonas.nombre_zona As nom,";
            $agrupar .= "zonas.id_zona";
            $orderby .= " zonas.nombre_zona ASC, ";

            break;
        case 'rubro':
            $primerAgrupo = " ru.CodigoRubro AS cod, ru.NombreRubro AS nom,";
            $agrupar .= "ru.CodigoRubro";
            $orderby .= " ru.NombreRubro ASC, ";
            break;
        case 'subrubro':
            $primerAgrupo = "srub.IdSubRubro AS cod,srub.NombreSubRubro As nom"
                . ",ru.CodigoRubro AS cod3, ru.NombreRubro AS nom3,";
            $agrupar .= "srub.IdSubRubro";
            $orderby .= " ru.NombreRubro ASC, srub.NombreSubRubro ASC, ";
            break;
    }




    // armado de ifs.
    /*filtrar por y su valor
     * se agrego un multiple filtros.
     * creo array con las claves.      
     */
    $arrFiltros = array();
    //     print_r($filtrarPor);
    foreach ($filtrarPor as $ff) {
        $datoFiltro = explode("|", $ff);
        $arrFiltros[$datoFiltro[0]][] = $datoFiltro[1];
    }

    foreach ($arrFiltros as $clave => $fi) {
        switch ($clave) {
            case 'cliente':
                // no puedo volver a agrupar.
                //                if($listarPor!='cliente'){
                //                    $primerAgrupo .='cli.Codigo AS cod2,cli.nombre_cliente  As nom2,';
                //
                //                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.

                if (!in_array("todos", $fi)) {
                    $where  .= ' AND cli.Codigo IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'tipocliente':
                // no puedo volver a agrupar.
                //                if($listarPor!='tipocliente'){
                //                    $primerAgrupo .='tpcli.IDTipoCliente AS cod2,tpcli.NombreTipoCliente  As nom2,';
                //
                //                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.

                if (!in_array("todos", $fi)) {
                    $where  .= ' AND tpcli.IDTipoCliente IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'vendedor':
                // no puedo volver a agrupar.
                //                if(!in_array("todos", $fi)){
                //                    $primerAgrupo .='vend.CodViajante AS cod2,vend.Nombre  As nom2,';
                //
                //                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND vend.CodViajante IN (' . implode(",", $fi) . ") AND vend.anulado='No'";
                }
                break;
            case 'articulo':
                // no puedo volver a agrupar.
                //                if($listarPor!='articulo'){
                //                    $primerAgrupo .='arti.IDArt AS cod2,arti.NombreArticulo  As nom2,';
                //
                //                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND arti.IDArt IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'proveedor':
                // no puedo volver a agrupar.
                //                if($listarPor!='proveedor'){
                //                    $primerAgrupo .='prov.Codigo AS cod2,prov.Nombre As nom2,';
                //
                //                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND prov.Codigo IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'zona':
                // no puedo volver a agrupar.
                //                if($listarPor!='zona'){
                //                    $primerAgrupo .=' zonas.id_zona AS cod2,zonas.nombre_zona As nom2,';
                //
                //                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND  zonas.id_zona IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'rubro':
                // no puedo volver a agrupar.
                //                if($listarPor!='rubro'){
                //                    $primerAgrupo .='ru.CodigoRubro AS cod2, ru.NombreRubro AS nom2,';
                //
                //                }
                // controlo el comportamiento del filtro valor filtro siempre sera un array de valores
                // donde puede tener un valor o muchos.
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND ru.CodigoRubro IN (' . implode(",", $fi) . ")";
                }

                break;
            case 'subrubro':
                // no puedo volver a agrupar.
                //                if($listarPor!='subrubro'){
                //                    $primerAgrupo .='srub.IdSubRubro AS cod2,srub.NombreSubRubro As nom2,';
                //
                //                }
                if (!in_array("todos", $fi)) {
                    $where  .= ' AND srub.IdSubRubro IN (' . implode(",", $fi) . ")";
                }

                break;
        }
    }


    $sqlInflacion = "SELECT
                       {$primerAgrupo}
                        (
                            AVG(
                                    IF(stock.Fecha>='{$desde}' AND stock.Fecha<='{$hasta}',
                                            stock.PrecioCostoxU ,NULL
                                    )
                            )/ 
                            AVG(
                                    IF(stock.Fecha>='{$desdeDos}' AND stock.Fecha<='{$hastaDos}',
                                            stock.PrecioCostoxU ,NULL
                                    )
                            ) 
                        ) AS indice
            FROM stock
                LEFT JOIN cuentacliente AS cc ON (cc.CodigoMovimiento= stock.CodigoMovimiento) 
                LEFT JOIN articulo AS arti ON arti.IDArt = stock.IDArt
                
                LEFT JOIN articulo_valor_ce AS kg ON kg.id_articulo = arti.IDArt
                LEFT JOIN rubro AS ru ON ru.CodigoRubro = arti.CodigoRubro
                LEFT JOIN subrubro AS srub ON srub.IDSubRubro = arti.IDSubRubro
                LEFT JOIN proveedor AS prov ON prov.Codigo = arti.CodigoProveedor
                LEFT JOIN cliente AS cli ON (cli.Codigo= stock.CodigoCP)
                #LEFT JOIN viajantes AS vend ON (vend.CodViajante= cli.CodViajante)
                LEFT JOIN viajantes AS vend ON (vend.CodViajante= cc.CodViajante)
                LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
                LEFT JOIN punto_venta AS ppv ON ( ppv.id_punto_venta=cc.id_pv)
               
           WHERE
                ({$rangoFecha})
               AND cc.Anulado='No' 
               AND stock.Anulado='No'
               {$where}
                AND (stock.TipoComp = 'Venta' 
                    OR stock.TipoComp = 'Venta TPV' 
                    OR stock.TipoComp = 'Devol - Cliente' 
                    OR stock.TipoComp = 'ND Anul NC'
                    )
            GROUP BY {$agrupar} HAVING indice <>0        
           ORDER BY arti.NombreArticulo ASC,  stock.Fecha ASC";


    //    "SELECT
    //            {$primerAgrupo}
    //            {$segundoAgrupo}
    //              
    //            {$comoSumo} 
    //            FROM stock
    //                LEFT JOIN cuentacliente AS cc ON (cc.CodigoMovimiento= stock.CodigoMovimiento) 
    //                LEFT JOIN articulo AS arti ON arti.IDArt = stock.IDArt
    //                LEFT JOIN articulo_valor_ce AS kg ON kg.id_articulo = arti.IDArt
    //                LEFT JOIN rubro AS ru ON ru.CodigoRubro = arti.CodigoRubro
    //                LEFT JOIN subrubro AS srub ON srub.IDSubRubro = arti.IDSubRubro
    //                LEFT JOIN proveedor AS prov ON prov.Codigo = arti.CodigoProveedor
    //                LEFT JOIN cliente AS cli ON (cli.Codigo= stock.CodigoCP)
    //                LEFT JOIN viajantes AS vend ON (vend.CodViajante= cc.CodViajante)
    //                LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
    //                LEFT JOIN punto_venta AS ppv ON ( ppv.id_punto_venta=cc.id_pv)
    //           WHERE
    //                ({$rangoFecha})
    //               AND cc.Anulado='No' 
    //               AND stock.Anulado='No'
    //               
    //                AND (stock.TipoComp = 'Venta' 
    //                    OR stock.TipoComp = 'Venta TPV' 
    //                    OR stock.TipoComp = 'Devol - Cliente' 
    //                    OR stock.TipoComp = 'ND Anul NC'
    //                    )
    //                 {$where}    
    //
    //            GROUP BY {$agrupar} HAVING Costo <>0 "
    //            . "ORDER BY {$orderby} stock.Fecha ASC";           


    $hacer = mysqli_query($connV, $sqlInflacion) or die("No puedo calcular la inflacion" . mysqli_error($connV) . "<pre>" . $sqlInflacion . "</pre>");
    echo "<pre>";
    print_r($sqlInflacion);
    echo "</pre>";
    while ($inf = mysqli_fetch_assoc($hacer)) {
        $arrInf[$inf["cod"]] = $inf;
    }
    //$valInflacion = mysqli_fetch_assoc($hacer);
    echo "<pre>";
    print_r($arrInf);
    echo "</pre>";
    //    $vuelta=floatval($valInflacion["indice"]);
    //    $vuelta =round($vuelta,2);
    //     echo "<pre>";
    //    var_dump($valInflacion);
    //    var_dump($vuelta);
    //    var_dump(number_format($valInflacion["indice"],3,'.',""));
    //    echo "</pre>";
    return $arrInf;
}
function utilidades_totales_todos_inflacion(
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
    $operacionRango = null,
    $inflacion = null
) {

    // configuracion

    $pvLista = explode("||", $puntoVenta);
    $listaFiltro = explode("||", $filtrarPor);

    // fecha de la inflacion.
    $rangoDoble = 1;
    //    print_r($inflacion);
    if ($inflacion == "mensual") {
        //mes   
        // analizar cantidad de meses entre desde y hasta porque para ir un mes
        // o un periodo hacia atras no es lo mismo 
        $diferencia = date_diff(date_create($hasta), date_create($desde));
        //       $desdeDos=date("Y-m-d", strtotime("-1 month", strtotime($desde))); 
        //       $hastaDos=date("Y-m-d", strtotime("-1 month", strtotime($hasta)));
        $desdeDos = date("Y-m-d", strtotime("-" . $diferencia->m . " month -" . $diferencia->d . " day", strtotime($desde)));
        $hastaDos = date("Y-m-d", strtotime("-" . $diferencia->m . " month -" . $diferencia->d . " day", strtotime($hasta)));
    } else {
        // anual
        $desdeDos = date("Y-m-d", strtotime("-1 year", strtotime($desde)));
        $hastaDos = date("Y-m-d", strtotime("-1 year", strtotime($hasta)));
    }



    // punto de venta
    foreach ($pvLista as $pv) {
        $arrPuntoVenta = explode("|", $pv);
        if ($arrPuntoVenta[1] == "todos") {
            $puntoVentaId[] = "todos";
        } else {
            if ($arrPuntoVenta[0] != "") {
                $puntoVentaId[] = $arrPuntoVenta[0];
            }
        }

        $puntoVentaTxt[] = $arrPuntoVenta[1];
    }
    $tituloFil = " PV: " . implode(",", $puntoVentaTxt);

    // filtro valores

    $arrFiltros = array();

    foreach ($listaFiltro as $valorFiltro) {
        $datoFiltro = explode("|", $valorFiltro);
        $arrFiltros[$datoFiltro[0]][] = $datoFiltro[2];
    }

    // return $sqlTotal;
    $arrResultado = array();
    $arrayNcInf = array();
    $arrayNc = array();
    $arrayNcInf = array();
    //    $arrResultadoNc = array();
    $agrupar = "mes";

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
    /* evaluar si es rango doble y pasar las dos variables o nada.*/
    /*Todo*/

    $sqlTotal = armar_sql_utilidad($tipo, $listarPor, $listaFiltro, $puntoVentaId, $periodo, $desde, $hasta, $rangoDoble, $desdeDos, $hastaDos);
    //        echo "<pre>";
    //                print_r($sqlTotal);
    //        echo "</pre>";
    $hacer = mysqli_query($connV, $sqlTotal) or die("no puedo hacer la consulta de las ventas" . mysqli_error($connV) . "<pre>" . $sqlTotal . "</pre>");
    while ($r = mysqli_fetch_assoc($hacer)) {
        $arrResultado[] = $r;
    }
    // SIN RESULTADO

    if (empty($arrResultado)) {
        return "vacio";
    }

    // validar si es con valor o con array las notas de credito.

    $valorNc = array('articulo', 'proveedor', 'rubro', 'subrubro');
    $traigoArrayNc = 0;
    if (in_array($listarPor, $valorNc)) {
        $traigoArrayNc++;
    }
    foreach ($arrFiltros as $cc => $vv) {
        if (in_array($cc, $valorNc)) {
            $traigoArrayNc++;
        }
    }
    //    
    //    //evaluo si nc con array o valor
    ////    $traigoArrayNc++;
    if ($traigoArrayNc == 0) {

        $sqlTotalNc = armar_sql_nc_utlidad($tipo, $listarPor, $listaFiltro, $puntoVentaId, $periodo, $desde, $hasta);
        //        }
        //        echo "<pre>";
        //        print_r($sqlTotalNc);
        //        echo "</pre>";
        $hacerNc = mysqli_query($connV, $sqlTotalNc) or die("no puedo hacer la consulta de las ventas" . mysqli_error($connV) . "<pre>" . $sqlTotalNc . "</pre>");
        while ($n = mysqli_fetch_assoc($hacerNc)) {
            $arrayNc[] = $n;
        }

        // nc venta anterior
        $sqlTotalNcInf = armar_sql_nc_utlidad($tipo, $listarPor, $listaFiltro, $puntoVentaId, $periodo, $desdeDos, $hastaDos);
        $hacerNcInf = mysqli_query($connV, $sqlTotalNcInf) or die("no puedo hacer la consulta de las nc de ventas x inflacion" . mysqli_error($connV) . "<pre>" . $sqlTotalNcInf . "</pre>");
        while ($nInf = mysqli_fetch_assoc($hacerNcInf)) {
            $arrayNcInf[] = $nInf;
        }
    } else {
        //recupero el valor
        $arrayNc = traer_valor_nc_utilidad($puntoVentaId, $desde, $hasta, $periodo, $listaFiltro);
        $arrayNcInf = traer_valor_nc_utilidad($puntoVentaId, $desdeDos, $hastaDos, $periodo, $listaFiltro);
    }

    /* INFLACION**/

    //$indiceInflacion = indice_inflacion($desde, $hasta, $desdeDos, $hastaDos,$listaFiltro,$listarPor);
    $arrIndiceInflacion = indice_inflacion($desde, $hasta, $desdeDos, $hastaDos, $listaFiltro, $listarPor);
    //        print_r($indiceInflacion);

    /*
     * SALIDA TABLA HTML
     */
    if ($salida == "html") {
        //        echo "<pre>";
        //        print_r($arrayNc);
        //        echo "</pre>";
        //        echo "<pre>";
        //        print_r($arrayNcInf);
        //        echo "</pre>";


        $arrCabeceras = getCabeceraUtilidad($desde, $hasta, $periodo, $listarPor, $arrFiltros, $tipo, $arrResultado, $rangoDoble, $desdeDos, $hastaDos);
        //        echo "<pre>";
        //        print_r($arrCabeceras);
        //        echo "</pre>";

        $titulo = $arrCabeceras["titulo"];
        $cabeceraTT = $arrCabeceras["cabecera"];
        $ith = $arrCabeceras["ith"];
        /***Cambio de cabeceras*/

        /*
         * DATOS
         */

        $renglon = getDatosUtilidad($arrResultado, $ith, $cabeceraTT, $arrayNc, $operacionRango, $traigoArrayNc, $arrIndiceInflacion, $arrayNcInf);
        //        echo "<pre>";
        //        print_r($renglon);                      
        //        echo "</pre>";


        /*
         * DATOS
         */
        $rng = 0;

        //        print_r($arrayChartT);
        /**Envio Final*/
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
                    "impNC" => $arrayNc,
                    "impNCInf" => $arrayNcInf
                );
            }
        }
        //        echo "<pre>";
        //        print_r($renglon);
        //        echo "</pre>";
        return json_encode($arrayFinal);
    }
}
// agrega a las ventas  movimientos. de nc
function fusion_ventas_nc($arrayVentas, $arrayNC)
{
    //$arrNcAd=array(); 
    $arrNcAd = array_diff_key($arrayNC, $arrayVentas);
    //    echo "Diferencia;=><pre>";
    //    print_r($arrayNC);
    //    echo "</pre><br>";

    if (!empty($arrNcAd)) {
        foreach ($arrNcAd as $clave => $dif) {
            $arrNcAd[$clave]["total"] = 0;

            $arrayVentas[$clave] = $arrNcAd[$clave];
        }
    }
    return $arrayVentas;
}
// agrega a las ventas las notas de debito que anula fc.
function fusion_ventas_nd($arrayVentas, $arrayNDeb)
{
    //$arrNcAd=array(); 
    $arrNcAd = array_diff_key($arrayNDeb, $arrayVentas);
    //    echo "Diferencia;=><pre>";
    //    print_r($arrayNC);
    //    echo "</pre><br>";

    if (!empty($arrNcAd)) {
        foreach ($arrNcAd as $clave => $dif) {
            $arrNcAd[$clave]["total"] = 0;

            $arrayVentas[$clave] = $arrNcAd[$clave];
        }
    }
    return $arrayVentas;
}