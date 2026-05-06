<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* consultas Base de datos. */
/* ============================================================================ */

// sql anida categoria rubros subrubros

function arrCatRubSub($base, $conFiltro = null)
{
    $filtro = "";
    //    if(isset($_SESSION["arrFiltros"])){
    //        echo "tengo la sesion en arrCatRubSub=><br>";
    //        echo "<pre>";
    //        print_r($_SESSION["arrFiltros"]);
    ////    
    //        echo "</pre>";
    //    }
    if ($conFiltro == 1) {
        if (isset($_SESSION["arrFiltros"]["promo"]) || isset($_SESSION["arrFiltros"]["promociones"])) {
            $filtro .= " AND articulo.promocion='Si' AND articulo.promocion_vigencia_hasta>='" . date('Y-m-d') . "'";
        }
    }
    //    case "promocion":
    //                        $arrFand[] .= " articulo.promocion='Si' AND articulo.promocion_vigencia_hasta>='" . date('Y-m-d') . "'";
    //                        break;
    //    
    # articulo publicado ecomm
    $filtroArticuloExterno = " AND articulo.ecommerce='Si' ";
    $leftJoinEcomm = "";
    // if (VALIDOECOMMEXTERNO == 'Si') {
    //     $leftJoinEcomm .= " LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt ";
    //     $filtroArticuloExterno = " AND ecom.publicado_ecom_externo='Si' ";
    // }
    $sqlTodo = "SELECT
                    
                    cat.id_categoria AS idcategoria,
                    cat.nombre_categoria As categoria,
                    rubro.CodigoRubro As idrubro,
                    rubro.NombreRubro AS rubro,
                    subrubro.IDSubRubro AS idsubrubro,
                    subrubro.NombreSubRubro  AS subrubro,
                    COUNT(articulo.idart) AS total
                    
                FROM articulo
                LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                LEFT JOIN rubro_categoria AS cat  ON cat.id_categoria = rubro.id_categoria
                LEFT JOIN subrubro ON subrubro.IDSubRubro=articulo.IDSubRubro
                {$leftJoinEcomm}
                WHERE                     
                        articulo.Discontinuo='No'
                        AND cat.ecommerce='Si'
                        
                        {$filtroArticuloExterno}
                        AND articulo.tipo_art='Articulo'
                        AND rubro.ecommerce='Si'
						AND subrubro.ecommerce='Si'
                 {$filtro}       
                GROUP BY cat.id_categoria,rubro.CodigoRubro,subrubro.IDSubRubro
           
                HAVING  COUNT(articulo.IDArt) > 0
                ORDER BY cat.nombre_categoria ASC,rubro.NombreRubro,subrubro.NombreSubRubro ASC";

    //echo "sql rubro subrubro Categoria -><pre>".$sqlTodo."</pre>";                
    $resultado = mysqli_query($base, $sqlTodo) or die("no puedo recuperar consulta " . mysqli_error($base) . "<br><pre>" . $sqlTodo . "</pre>");

    $arrCat = array();
    $arrRu = array();
    $arrSrU = array();
    while ($v = mysqli_fetch_assoc($resultado)) {
        $arrCat[$v["idcategoria"]]["categoria"] = $v["categoria"];


        if (isset($arrCat[$v["idcategoria"]]["total"])) {
            $arrCat[$v["idcategoria"]]["total"] += $v["total"];
        } else {
            $arrCat[$v["idcategoria"]]["total"] = $v["total"];
        }
        $arrRu[$v["idcategoria"]][$v["idrubro"]]["rubro"] = $v["rubro"];

        if (isset($arrRu[$v["idcategoria"]][$v["idrubro"]]["total"])) {
            $arrRu[$v["idcategoria"]][$v["idrubro"]]["total"] += $v["total"];
        } else {
            $arrRu[$v["idcategoria"]][$v["idrubro"]]["total"] = $v["total"];
        }

        $arrSrU[$v["idcategoria"]][$v["idrubro"]][$v["idsubrubro"]]["subrubro"] = $v["subrubro"];

        if (isset($arrSrU[$v["idcategoria"]][$v["idrubro"]][$v["idsubrubro"]]["total"])) {
            $arrSrU[$v["idcategoria"]][$v["idrubro"]][$v["idsubrubro"]]["total"] += $v["total"];
        } else {
            $arrSrU[$v["idcategoria"]][$v["idrubro"]][$v["idsubrubro"]]["total"] = $v["total"];
        }
    }
    $arrTodos = array("cate" => $arrCat, "rub" => $arrRu, "srub" => $arrSrU);
    //     echo "nuevo rubro encontardo y devuelto luego de...<pre>";
    //    print_r($arrTodos);
    //    echo "</pre>";
    return $arrTodos;
}

/*
 * Busqueda rapida de articulos
 *  */

function arrArtiRapido($base)
{
    # articulo publicado ecomm
    $filtroArticuloExterno = " AND articulo.ecommerce='Si' ";
    $leftJoinEcomm = "";
    if (VALIDOECOMMEXTERNO == 'Si') {
        $leftJoinEcomm .= " LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt ";
        $filtroArticuloExterno = " AND ecom.publicado_ecom_externo='Si' ";
    }

    # cambio de codigo de prodcuto.
    /*
    if (defined('CODIGOPRODUCTO') && CODIGOPRODUCTO == 'manual') {
        $sqlArti = "SELECT
                        articulo.id_manual AS id,
                        CONCAT(articulo.NombreArticulo,' Cod: ',articulo.id_manual) AS articulo
                        
                    FROM articulo 
                    {$leftJoinEcomm}
                    LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                    LEFT JOIN rubro_categoria AS cat ON cat.id_categoria = rubro.id_categoria
                    WHERE 
                    articulo.tipo_art='Articulo'     
                    AND articulo.Discontinuo='No'    
                    AND cat.ecommerce='Si'        
                    AND rubro.ecommerce='Si'                               
                   
                    {$filtroArticuloExterno}
                    ORDER BY articulo.NombreArticulo ASC";
    } else {
        $sqlArti = "SELECT
                        articulo.IDArt AS id,
                        CONCAT(articulo.NombreArticulo,' Cod: ',articulo.IDArt) AS articulo
                        
                    FROM articulo 
                    {$leftJoinEcomm}
                    LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                    LEFT JOIN rubro_categoria AS cat ON cat.id_categoria = rubro.id_categoria
                    WHERE 
                    articulo.tipo_art='Articulo'     
                    AND articulo.Discontinuo='No'    
                    AND cat.ecommerce='Si'        
                    AND rubro.ecommerce='Si'                               
                    AND articulo.ecommerce='Si'
                    {$filtroArticuloExterno}
                    ORDER BY articulo.NombreArticulo ASC";
    }*/

    if (defined('CODIGOPRODUCTO') && CODIGOPRODUCTO == 'manual') {
        $sqlArti = "SELECT
                        #articulo.id_manual AS id,
                        articulo.IDArt AS id,
                        #CONCAT(articulo.NombreArticulo,' Cod: ',articulo.id_manual) AS articulo
                        -- IF(NOT ISNULL(ecom.nombre_articulo_ecom) AND ecom.nombre_articulo_ecom<>'',
                        --     CONCAT(ecom.nombre_articulo_ecom,' Cod: ',articulo.id_manual),
                        --     CONCAT(articulo.NombreArticulo,' Cod: ',articulo.id_manual)) AS articulo 
                            IF(NOT ISNULL(ecom.nombre_articulo_ecom) AND ecom.nombre_articulo_ecom<>'',
                            CONCAT(ecom.nombre_articulo_ecom,' Cod: ',articulo.IDArt),
                            CONCAT(articulo.NombreArticulo,' Cod: ',articulo.IDArt)) AS articulo    
                        
                    FROM articulo 
                     
                    {$leftJoinEcomm}
                    LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt
                    LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                    LEFT JOIN rubro_categoria AS cat ON cat.id_categoria = rubro.id_categoria
                    WHERE 
                    articulo.tipo_art='Articulo'     
                    AND articulo.Discontinuo='No'    
                    AND cat.ecommerce='Si'        
                    AND rubro.ecommerce='Si'                               
                   
                    {$filtroArticuloExterno}
                    ORDER BY articulo.NombreArticulo ASC";
    } else {
        $sqlArti = "SELECT
                        articulo.IDArt AS id,
                        #CONCAT(articulo.NombreArticulo,' Cod: ',articulo.IDArt) AS articulo
                        IF(NOT ISNULL(ecom.nombre_articulo_ecom) AND ecom.nombre_articulo_ecom<>'',
                            CONCAT(ecom.nombre_articulo_ecom,' Cod: ',articulo.IDArt),
                            CONCAT(articulo.NombreArticulo,' Cod: ',articulo.IDArt)) AS articulo
                        
                    FROM articulo 
                    {$leftJoinEcomm}
                    LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt 
                    LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                    LEFT JOIN rubro_categoria AS cat ON cat.id_categoria = rubro.id_categoria
                    WHERE 
                    articulo.tipo_art='Articulo'     
                    AND articulo.Discontinuo='No'    
                    AND cat.ecommerce='Si'        
                    AND rubro.ecommerce='Si'                               
                    AND articulo.ecommerce='Si'
                    {$filtroArticuloExterno}
                    ORDER BY articulo.NombreArticulo ASC";
    }

    //ORDER BY rubro.id_categoria,rubro.NombreRubro,subrubro.NombreSubRubro,marca.NombreMarca ASC";

    $resultado = mysqli_query($base, $sqlArti) or die("no puedo recuperar las articulo rapido" . mysqli_error($base) . "<pre>" . $sqlArti . "</pre>");
    $arrArti = array();

    while ($arti = mysqli_fetch_assoc($resultado)) {
        $arrArti[] = ucwords($arti["articulo"]);
    }
    return json_encode($arrArti);
    //    return join(",",$arrArti);
}

/** 
 * genera un array con las marcas */
function arrBuscaMarca($base)
{
    $filtro = "";
    /// traigo las marcas de un articulo
    //echo "<pre>";
    //print_r(  $_SESSION["arrFiltros"]);
    //echo "</pre>";

    // si he seleccionado rubro y subrubro, debo traer los valores
    //    $filtro="";
    //    if($categoria!=null){
    //        $filtro .=" AND rubro_categoria.id_categoria=".$categoria;
    //    }
    //    if($rubro!=null){
    //        $filtro .=" AND articulo.CodigoRubro=".$rubro;
    //    }

    //    if($subrubro!=null){
    //        $filtro .=" AND articulo.IDSubRubro=".$subrubro;
    //    }
    if(isset($_SESSION["arrFiltros"])){
        $arrFiltros= $_SESSION["arrFiltros"];
        foreach($arrFiltros as $clave=>$fil){
            if ($clave=='promo' ||$clave=='promociones') {
                $filtro .= " AND articulo.promocion='Si' AND articulo.promocion_vigencia_hasta>='" . date('Y-m-d') . "'";
            }
            if(isset($fil['tipo'])&& $fil['tipo']=='subrubro'){
                $filtro .=" AND articulo.idsubrubro=".$fil['id'];
            }
            

        }
    }    

    /*
    if (isset($_SESSION["arrFiltros"]["promo"]) || isset($_SESSION["arrFiltros"]["promociones"])) {
        $filtro .= " AND articulo.promocion='Si' AND articulo.promocion_vigencia_hasta>='" . date('Y-m-d') . "'";
    }
    */
    $filtroArticuloExterno = " AND articulo.ecommerce='Si' ";

    $leftJoinEcomm = "";
    if (VALIDOECOMMEXTERNO == 'Si') {
        $leftJoinEcomm .= " LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt ";
        $filtroArticuloExterno = " AND ecom.publicado_ecom_externo='Si' ";
    }

    $sqlMarca = "SELECT
                    rubro.id_categoria AS idcategoria,
                    rubro.CodigoRubro AS idrubro,
                    subrubro.IDSubRubro AS idsubrubro,
                    marca.CodMarca AS idmarca,
                    rubro.NombreRubro AS rubro,                    
                    subrubro.NombreSubRubro AS subrubro,                    
                    rubro_categoria.nombre_categoria AS categoria,                    
                    marca.NombreMarca AS marca,                    
                    COUNT(articulo.IDArt) AS total
                    
                FROM marca 
                LEFT JOIN articulo ON articulo.CodigoMarca = marca.CodMarca    
                {$leftJoinEcomm}
                LEFT JOIN subrubro ON subrubro.IDSubRubro=articulo.IDSubRubro
                LEFT JOIN rubro ON rubro.CodigoRubro = subrubro.CodigoRubro
                LEFT JOIN rubro_categoria ON rubro_categoria.id_categoria=rubro.id_categoria
                
                WHERE 
                    articulo.Discontinuo='No'
                    AND articulo.tipo_art='Articulo'                                             
                    {$filtroArticuloExterno}
                    AND rubro_categoria.ecommerce='Si'
                    AND rubro.ecommerce='Si'
                    AND marca.CodMarca<>1
                    AND marca.anulado = 'No'
                    AND marca.ecommerce='Si'
                    
                {$filtro}
                   
                GROUP BY rubro.id_categoria,articulo.CodigoRubro,articulo.IDSubRubro, articulo.CodigoMarca
                HAVING COUNT(articulo.IDArt)>0
                ORDER BY marca.NombreMarca ASC";
    //ORDER BY rubro.id_categoria,rubro.NombreRubro,subrubro.NombreSubRubro,marca.NombreMarca ASC";

    $resultado = mysqli_query($base, $sqlMarca) or die("no puedo recuperar las marcas" . mysqli_error($base) . "<pre>" . $sqlMarca . "</pre>");
    $arrMarcas = array();

    while ($marca = mysqli_fetch_assoc($resultado)) {
        $arrMarcas[] = $marca;
    }


    return $arrMarcas;
}

// sql categorias
function buscaCategorias($base)
{
    $filtroArticuloExterno = " AND articulo.ecommerce='Si' ";
    $leftJoinEcomm = "";
    if (VALIDOECOMMEXTERNO == 'Si') {
        $leftJoinEcomm .= " LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt ";
        $filtroArticuloExterno = " AND ecom.publicado_ecom_externo='Si' ";
    }

    $filtro = "";
    $sqlCat = "SELECT
                    rubro_categoria.id_categoria AS idcategoria,
                    rubro_categoria.nombre_categoria As categoria,
                    COUNT(articulo.idart) AS total
                    
                FROM rubro_categoria 
                LEFT JOIN rubro ON rubro.id_categoria = rubro_categoria.id_categoria
                LEFT JOIN articulo ON articulo.CodigoRubro = rubro.CodigoRubro
                {$leftJoinEcomm}
                
                WHERE 
                    rubro_categoria.anulado = 'No'
                AND rubro_categoria.ecommerce='Si'
                {$filtroArticuloExterno}
                {$filtro}
                GROUP BY rubro_categoria.id_categoria
                HAVING  COUNT(articulo.IDArt) > 0
                ORDER BY rubro_categoria.nombre_categoria ASC";

    $resultado = mysqli_query($base, $sqlCat) or die("no puedo recuperar categorias" . mysqli_error($base) . "<pre>" . $sqlCat . "</pre>");
    $arrCat = array();

    while ($cate = mysqli_fetch_assoc($resultado)) {
        $arrCat[] = $cate;
    }


    // html
    return $arrCat;
}

// sql subrubros
function buscaSubRubro($base, $rubro)
{
    $filtro = "";

    if ($rubro != null) {
        $filtro .= " AND subrubro.Codigorubro=" . $rubro;
    }
    $filtroArticuloExterno = " AND articulo.ecommerce='Si' ";
    $leftJoinEcomm = "";
    if (VALIDOECOMMEXTERNO == 'Si') {
        $leftJoinEcomm .= " LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt ";
        $filtroArticuloExterno = " AND ecom.publicado_ecom_externo='Si' ";
    }
    $sqlSubRubro = "SELECT
                    rubro.CodigoRubro AS idrubro,
                    rubro.NombreRubro AS rubro,
                    subrubro.IDSubRubro AS idsubrubro,
                    subrubro.NombreSubRubro AS subrubro,
                    COUNT(articulo.idart) AS total
                    
                FROM subrubro 
                LEFT JOIN articulo ON articulo.IDSubRubro = subrubro.IDSubRubro
                {$leftJoinEcomm}
                LEFT JOIN rubro ON rubro.CodigoRubro = subrubro.CodigoRubro
                WHERE 
                    subrubro.anulado = 'No'
				AND subrubro.ecommerce = 'Si'	
                AND rubro.ecommerce='Si'
                {$filtroArticuloExterno}
                {$filtro}
                GROUP BY articulo.IDSubRubro
                HAVING  COUNT(articulo.idart) > 0
                ORDER BY subrubro.NombreSubRubro ASC";

    $resultado = mysqli_query($base, $sqlSubRubro) or die("no puedo recuperar los Sub rubros" . mysqli_error($base) . "<pre>" . $sqlSubRubro . "</pre>");
    $arrSrubros = array();

    while ($rubro = mysqli_fetch_assoc($resultado)) {
        $arrSrubros[] = $rubro;
    }


    // html
    return $arrSrubros;
}

//sql rubro
function buscaRubro($base, $categoria = null)
{

    $filtro = "";

    if ($categoria != null) {
        $filtro .= " AND rubro.id_categoria=" . $categoria;
    }
    $filtroArticuloExterno = " AND articulo.ecommerce='Si' ";
    $leftJoinEcomm = "";
    if (VALIDOECOMMEXTERNO == 'Si') {
        $leftJoinEcomm .= " LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt ";
        $filtroArticuloExterno = " AND ecom.publicado_ecom_externo='Si' ";
    }
    $sqlSubRubro = "SELECT
                   
                    rubro.id_categoria AS idcategoria,
                    rubro_categoria.nombre_categoria As categoria,
                    rubro.CodigoRubro As idrubro,
                    rubro.NombreRubro AS rubro,                    
                    COUNT(articulo.idart) AS total
                    
                FROM rubro
                LEFT JOIN articulo ON articulo.CodigoRubro = rubro.CodigoRubro
                {$leftJoinEcomm}
                LEFT JOIN rubro_categoria ON rubro_categoria.id_categoria= rubro.id_categoria
                WHERE 
                    rubro.anulado = 'No'
                    AND rubro.ecommerce='Si'
                    {$filtroArticuloExterno}
                    {$filtro}
                GROUP BY articulo.CodigoRubro
                HAVING  COUNT(articulo.idart) > 0
                ORDER BY rubro.Nombrerubro ASC";

    $resultado = mysqli_query($base, $sqlSubRubro) or die("no puedo recuperar los rubros" . mysqli_error($base) . "<pre>" . $sqlSubRubro . "</pre>");
    $arrRubros = array();

    while ($rubro = mysqli_fetch_assoc($resultado)) {
        $arrRubros[] = $rubro;
    }


    // html
    return $arrRubros;
}

// sql tipo cliente
function buscaTipoCliente($base)
{
}

//sqlmarca





/*
 * listado menu SUPERIOR Categoria/rubro/subrubro 
 * =============================================================================
 */


function listaMenuRubroSubRubro($arrFiltro) {
    //    $categ= buscaCategorias($base);
    $categ = $arrFiltro["cate"];
    $i_contador_nv = 0;

    $html = '<nav class="navbar navbar-expand-lg menu-categorias">';
    $html .= '<div class="container menu-panel">';
    $html .= '<ul class="nav navbar-nav">';
    // categoria
    foreach ($categ as $idCat => $cate) {
        $rubroTot = $arrFiltro["rub"][$idCat];

        if ($i_contador_nv == 0) {
            $class_menu_nv = "active";
        } else {
            $class_menu_nv = "";
        };

        $html .= '<li class="' . rtrim($class_menu_nv) . ' dropdown">';
        $html .= '<a class="dropdown-toggle ' . str_replace(" ", "-", strtolower(rtrim($cate["categoria"]))) . '" data-toggle="dropdown" href="#">' . ucfirst(strtolower(rtrim($cate["categoria"])));
        //$html .= '<span></span>';
        $html .= '</a>' . PHP_EOL;
        $html .= '<div class="dropdown-menu">';
        $html .= '<div class="row contenedor-menu-general ' . str_replace(" ", "-", strtolower(rtrim($cate["categoria"]))) . '">';
        $html .= '<h2 class="titulo ' . str_replace(" ", "-", strtolower(rtrim($cate["categoria"]))) . '">' . ucfirst(strtolower(rtrim($cate["categoria"]))) . '</h2>';
        // rubro
        foreach ($rubroTot as $idrubro => $rubro) {

            $subRu = $arrFiltro["srub"][$idCat][$idrubro];
            $arrArgR = array(
                "idcategoria" => $idCat,
                "categoria" => $cate["categoria"],
                "idrubro" => $idrubro,
                "rubro" => $rubro["rubro"],
                "idsubrubro" => null,
                "subrubro" => null,
                "idmarca" => null,
                "marca" => null,
                "idtipocliente" => null,
                "tipocliente" => null
            );

            $html .= '<div class="col-xs-12 col-sm-4 col-md-3 col-lg-2 contenedor-categoria-menu">'
                . '<h5 class="titulo-categoria-menu">'
                //. '<a class="nv_subcat" href="#" tabindex="-1" >'.rtrim($rubro["rubro"])
                . '<a href="' . hacer_url("__", $arrArgR) . '" class="nv_subcat superiorRubros" tabindex="-1" >' . ucfirst(strtolower(rtrim($rubro["rubro"]))) . '</a>'
                . '</h5>'
                . '<ul class="lista-sub-categorias">';

                if (USOVERMAS == 'Si') {
                    $cuantosSr = 0;
                    $btnVerMas = '<li class="li_ver_mas"><a  href="javascript:void(0) "  onclick="verMasMenu(\'' . hacer_url("index.php", $arrArgR) . '\');" class="nv_link_todas_cat" tabindex="-1" >ver mas</a></li>' . PHP_EOL;
                    //subrubro
                    foreach ($subRu as $idsubrubro => $sr) {
                        $arrArg = array(
                            "idcategoria" => $idCat,
                            "categoria" => $cate["categoria"],
                            "idrubro" => $idrubro,
                            "rubro" => $rubro["rubro"],
                            "idsubrubro" => $idsubrubro,
                            "subrubro" => $sr["subrubro"],
                            "idmarca" => null,
                            "marca" => null,
                            "idtipocliente" => null,
                            "tipocliente" => null
                        );
                        if ($cuantosSr > 5) {
                            $html .= $btnVerMas;
                            $cuantos = 0;
                            break;
                        }
                        if ($cuantosSr < 5) {
                            $html .= '<li>'
                                . '<a tipo="subrubro" href="' . hacer_url("index.php", $arrArg) . '"  key="sr' . $idsubrubro . '" datos="' . $sr["subrubro"] . '" id="' . $idsubrubro . '" class="superiorSubrubros">' . ucfirst(strtolower($sr["subrubro"])) . '</a>'
                                . '</li>' . PHP_EOL;
                        }
                        $cuantosSr++;
                    }
                }

                if (USOVERMAS == 'No') {
                    foreach ($subRu as $idsubrubro => $sr) {
                        $arrArg = array(
                            "idcategoria" => $idCat,
                            "categoria" => $cate["categoria"],
                            "idrubro" => $idrubro,
                            "rubro" => $rubro["rubro"],
                            "idsubrubro" => $idsubrubro,
                            "subrubro" => $sr["subrubro"],
                            "idmarca" => null,
                            "marca" => null,
                            "idtipocliente" => null,
                            "tipocliente" => null
                        );

                        $html .= '<li>'
                            . '<a tipo="subrubro" href="' . hacer_url("index.php", $arrArg) . '"  key="sr' . $idsubrubro . '" datos="' . $sr["subrubro"] . '" id="' . $idsubrubro . '" class="superiorSubrubros">' . ucfirst(strtolower($sr["subrubro"])) . '</a>'
                            . '</li>' . PHP_EOL;
                    }
                }

                $html .= '</ul>' . PHP_EOL;
                $html .= '</div>';

            }
            
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</li>';
            $i_contador_nv++;
        }
    
    // boton Promociones
    $html .= '<li class="dropdown">';
    $html .= '<a class="menu-promociones Promociones" href="#" onclick="promociones();">Promociones ';
    $html .= '<i class="fas fa-tag"></i>';
    //$html .= '<span></span>';
    $html .= '</a>';
    $html .= '</li>';
    // fin boton promociones

    $html .= '</ul>';
    $html .= '</div>';
    $html .= '</nav>';

    return $html;
}

/*
 * listado menu SUPERIOR Categoria/rubro/subrubro CON IMAGENES 2022
 * =============================================================================
 */


function listaMenuRubroSubRubroImagen($arrFiltro) {
    //    $categ= buscaCategorias($base);
    $categ = $arrFiltro["cate"];
    $i_contador_nv = 0;

    $html = '<nav class="navbar navbar-expand-lg menu-categorias-imagen" id="menu-categorias-imagen">';
    $html .= '<div class="container-fluid menu-panel">';
    $html .= '<ul class="nav navbar-nav">';
    // categoria
    foreach ($categ as $idCat => $cate) {
        $rubroTot = $arrFiltro["rub"][$idCat];

        if ($i_contador_nv == 0) {
            $class_menu_nv = "active";
        } else {
            $class_menu_nv = "";
        };

        $html .= '<li class="' . rtrim($class_menu_nv) . ' dropdown">';
        $html .= '<a class="dropdown-toggle ' . str_replace(" ", "-", strtolower(rtrim($cate["categoria"]))) . '" data-toggle="dropdown" href="#">' . ucfirst(strtolower(rtrim($cate["categoria"])));
        $html .= '</a>' . PHP_EOL;
        $html .= '<div class="dropdown-menu">';
        $html .= '<div class="container">';
        $html .= '<div class="row contenedor-menu-general ' . str_replace(" ", "-", strtolower(rtrim($cate["categoria"]))) . '">';
        //$html .= '<h2 class="titulo ' . str_replace(" ", "-", strtolower(rtrim($cate["categoria"]))) . '">' . ucfirst(strtolower(rtrim($cate["categoria"]))) . '</h2>';
        // rubro

        //$html .= '<div class="col-sm-12 col-lg-8 col-xl-9 menu">';

        // imagenes tienda - rubro/subrubro 
        $html .= '<div class="col-sm-12 col-lg-4 col-xl-3 imagen">';

        //$contadorRubros = 1;
        // instanciar objetos
        $Content = new CONTENT();
        $images = "";

        //foreach ($categ as $idCat => $cate) {
            //$rubroTot = $arrFiltro["rub"][$idCat];


            $html .= '<div class="contenedor-img-general">';
        
                $tipo_orden = 'asc';
                $campo_orden = 'orden';
                $rs_condicion['type'] = 'defines-img-menu-header';
                $rs_condicion['status'] = 'active';
                $rs_condicion['sub_type'] = ucfirst(strtolower(rtrim($cate["categoria"])));
                $lst_content = $Content->getTodos($campo_orden, $tipo_orden, true, $rs_condicion);

                if(is_array($lst_content)) {
                    foreach($lst_content as $rs_content) {
                        $images = explode(",", $rs_content['image']);
                        $html .= '<img src="nv/uploads/content/' . $images[0] . '">';
                    }
                }
                
            $html .= '</div>';

            //$contadorRubros++;
        //}

        $html .= '</div>';

        // listas tienda - rubro/subrubro
        $html .= '<div class="col-sm-12 col-lg-8 col-xl-9 menu">';
        $html .= '<div class="row">';

        foreach ($rubroTot as $idrubro => $rubro) {

            $subRu = $arrFiltro["srub"][$idCat][$idrubro];
            $arrArgR = array(
                "idcategoria" => $idCat,
                "categoria" => $cate["categoria"],
                "idrubro" => $idrubro,
                "rubro" => $rubro["rubro"],
                "idsubrubro" => null,
                "subrubro" => null,
                "idmarca" => null,
                "marca" => null,
                "idtipocliente" => null,
                "tipocliente" => null
            );

            $html .= '<div class="col-xs-12 col-sm-4 col-md-3 col-lg-2 contenedor-categoria-menu">'
                . '<h5 class="titulo-categoria-menu">'
                . '<a href="' . hacer_url("__", $arrArgR) . '" class="nv_subcat superiorRubros" tabindex="-1" >' . ucfirst(strtolower(rtrim($rubro["rubro"]))) . '</a>'
                . '</h5>'
                . '<ul class="lista-sub-categorias">';

                if (USOVERMAS == 'Si') {
                    $cuantosSr = 0;
                    $btnVerMas = '<li class="li_ver_mas"><a  href="javascript:void(0) "  onclick="verMasMenu(\'' . hacer_url("index.php", $arrArgR) . '\');" class="nv_link_todas_cat" tabindex="-1" >ver mas</a></li>' . PHP_EOL;
                    //subrubro
                    foreach ($subRu as $idsubrubro => $sr) {
                        $arrArg = array(
                            "idcategoria" => $idCat,
                            "categoria" => $cate["categoria"],
                            "idrubro" => $idrubro,
                            "rubro" => $rubro["rubro"],
                            "idsubrubro" => $idsubrubro,
                            "subrubro" => $sr["subrubro"],
                            "idmarca" => null,
                            "marca" => null,
                            "idtipocliente" => null,
                            "tipocliente" => null
                        );
                        if ($cuantosSr > 5) {
                            $html .= $btnVerMas;
                            $cuantos = 0;
                            break;
                        }
                        if ($cuantosSr < 5) {
                            $html .= '<li>'
                                . '<a tipo="subrubro" href="' . hacer_url("index.php", $arrArg) . '"  key="sr' . $idsubrubro . '" datos="' . $sr["subrubro"] . '" id="' . $idsubrubro . '" class="superiorSubrubros">' . ucfirst(strtolower($sr["subrubro"])) . '</a>'
                                . '</li>' . PHP_EOL;
                        }
                        $cuantosSr++;
                    }
                }

                if (USOVERMAS == 'No') {
                    foreach ($subRu as $idsubrubro => $sr) {
                        $arrArg = array(
                            "idcategoria" => $idCat,
                            "categoria" => $cate["categoria"],
                            "idrubro" => $idrubro,
                            "rubro" => $rubro["rubro"],
                            "idsubrubro" => $idsubrubro,
                            "subrubro" => $sr["subrubro"],
                            "idmarca" => null,
                            "marca" => null,
                            "idtipocliente" => null,
                            "tipocliente" => null
                        );

                        $html .= '<li>'
                            . '<a tipo="subrubro" href="' . hacer_url("index.php", $arrArg) . '"  key="sr' . $idsubrubro . '" datos="' . $sr["subrubro"] . '" id="' . $idsubrubro . '" class="superiorSubrubros">' . ucfirst(strtolower($sr["subrubro"])) . '</a>'
                            . '</li>' . PHP_EOL;
                    }
                }

                $html .= '</ul>' . PHP_EOL;
                $html .= '</div>';

            }

            $html .= '</div>';
            $html .= '</div>';
            
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</li>';
            $i_contador_nv++;
        }

        //$html .= '</div>';
    
    // boton Promociones
    $html .= '<li class="dropdown">';
    $html .= '<a class="menu-promociones Promociones" href="#" onclick="promociones();">Promociones ';
    $html .= '<i class="fas fa-tag"></i>';
    $html .= '</a>';
    $html .= '</li>';
    // fin boton promociones

    $html .= '</ul>';
    $html .= '</div>';
    $html .= '</nav>';

    return $html;
}

/*
 * listado menu SUPERIOR tienda tipo mercado libre Categoria/rubro/subrubro 
 * =============================================================================
 */

function listaMenuModalRubroSubRubro($arrFiltro) {
    //    $categ= buscaCategorias($base);
    $categ = $arrFiltro["cate"];

    //$html = '<nav class="navbar navbar-expand-lg menu-modal">';
    //$html .= '<div class="container">';
    //$html = '<button class="btn-tienda" id="menuTienda" data-toggle="modal" data-target="#menuModal">Tienda</button>';
    //$html .= '</div>';
    $html = '<div class="menu-general-tienda modal" id="menuModal" tabindex="-1" aria-labelledby="menuModalLabel" aria-hidden="true">';
    $html .= '<div class="modal-dialog container">';
    $html .= '<div class="modal-content" onmouseleave="hoverPanelModal()">';

    $html .= '<div class="modal-body">';
    $html .= '<div class="row">';

                // menu tienda - categoria    
                $html .= '<div class="col-sm-12 col-lg-3 col-xl-2 menu" id="menu-tienda">';
                    $html .= '<ul>';

                    $contadorRubros = 1;

                    foreach ($categ as $idCat => $cate) {
                        $rubroTot = $arrFiltro["rub"][$idCat];

                        $html .= '<li><button class="btn-item-tienda" id="btn-modal-tienda-' . $contadorRubros . '" onclick="menuCategoriasModal(this.id)">' . ucfirst(strtolower(rtrim($cate["categoria"]))) . '</button></li>';

                        $contadorRubros++;
                    }
                    
                    // boton Promociones

                    $html .= '<li><button class="menu-promociones Promociones" onclick="promociones();">Promociones <i class="fas fa-tag"></i></a></li>';

                    $html .= '</ul>';
                $html .= '</div>';
                // fin menu tienda - categoria 



                // panel tienda - rubro/subrubro 
                $html .= '<div class="col-sm-12 col-lg-5 col-xl-7 paneles">';

                $contadorRubros = 1;

                    foreach ($categ as $idCat => $cate) {
                        $rubroTot = $arrFiltro["rub"][$idCat];

                        $html .= '<div class="row contenedor-menu-general d-none" id="panel-modal-tienda-' . $contadorRubros . '">';

                        $html .= '<div class="col-12">';
                        $html .= '<h2 class="titulo">' . ucfirst(strtolower(rtrim($cate["categoria"]))) . '</h2>';
                        $html .= '</div>';

                        foreach ($rubroTot as $idrubro => $rubro) {

                            $subRu = $arrFiltro["srub"][$idCat][$idrubro];
                            $arrArgR = array(
                                "idcategoria" => $idCat,
                                "categoria" => $cate["categoria"],
                                "idrubro" => $idrubro,
                                "rubro" => $rubro["rubro"],
                                "idsubrubro" => null,
                                "subrubro" => null,
                                "idmarca" => null,
                                "marca" => null,
                                "idtipocliente" => null,
                                "tipocliente" => null
                            );

                                $html .= '<div class="col-sm-12 col-lg-6 col-xl-4 contenedor-categoria-menu">';
                                $html .= '<h5 class="titulo-categoria-menu"><a href="' . hacer_url("__", $arrArgR) . '" class="nv_subcat superiorRubros" tabindex="-1" >' . ucfirst(strtolower(rtrim($rubro["rubro"]))) . '</a></h5>';
                                $html .= '<ul class="lista-sub-categorias">';

                                        if (USOVERMAS == 'Si') {
                                            $cuantosSr = 0;
                                            $btnVerMas = '<li class="li_ver_mas"><a  href="javascript:void(0) "  onclick="verMasMenu(\'' . hacer_url("index.php", $arrArgR) . '\');" class="nv_link_todas_cat" tabindex="-1" >ver mas</a></li>';
                                            //subrubro
                                            foreach ($subRu as $idsubrubro => $sr) {
                                                $arrArg = array(
                                                    "idcategoria" => $idCat,
                                                    "categoria" => $cate["categoria"],
                                                    "idrubro" => $idrubro,
                                                    "rubro" => $rubro["rubro"],
                                                    "idsubrubro" => $idsubrubro,
                                                    "subrubro" => $sr["subrubro"],
                                                    "idmarca" => null,
                                                    "marca" => null,
                                                    "idtipocliente" => null,
                                                    "tipocliente" => null
                                                );
                                                if ($cuantosSr > 5) {
                                                    $html .= $btnVerMas;
                                                    $cuantos = 0;
                                                    break;
                                                }
                                                if ($cuantosSr < 5) {
                                                    $html .= '<li>'
                                                            . '<a tipo="subrubro" href="' . hacer_url("index.php", $arrArg) . '"  key="sr' . $idsubrubro . '" datos="' . $sr["subrubro"] . '" id="' . $idsubrubro . '" class="superiorSubrubros">' . ucfirst(strtolower($sr["subrubro"])) . '</a>'
                                                            . '</li>';
                                                }
                                                $cuantosSr++;
                                            }
                                        }

                                        if (USOVERMAS == 'No') {
                                            foreach ($subRu as $idsubrubro => $sr) {
                                                $arrArg = array(
                                                    "idcategoria" => $idCat,
                                                    "categoria" => $cate["categoria"],
                                                    "idrubro" => $idrubro,
                                                    "rubro" => $rubro["rubro"],
                                                    "idsubrubro" => $idsubrubro,
                                                    "subrubro" => $sr["subrubro"],
                                                    "idmarca" => null,
                                                    "marca" => null,
                                                    "idtipocliente" => null,
                                                    "tipocliente" => null
                                                );

                                                $html .= '<li>'
                                                        . '<a tipo="subrubro" href="' . hacer_url("index.php", $arrArg) . '"  key="sr' . $idsubrubro . '" datos="' . $sr["subrubro"] . '" id="' . $idsubrubro . '" class="superiorSubrubros">' . ucfirst(strtolower($sr["subrubro"])) . '</a>'
                                                        . '</li>';
                                            }
                                        }

                                $html .= '</ul>';
                                $html .= '</div>';

                            //$html .= '</div>';

                        }

                        $html .= '</div>';

                        $contadorRubros++;

                    }

                    $html .= '</div>';
                    // fin panel tienda - rubro/subrubro 


                    // imagenes tienda - rubro/subrubro 
                    $html .= '<div class="col-sm-12 col-lg-4 col-xl-3 imagen">';

                        $contadorRubros = 1;
                        // instanciar objetos
                        $Content = new CONTENT();

                        foreach ($categ as $idCat => $cate) {
                            $rubroTot = $arrFiltro["rub"][$idCat];

                            $images = "";

                            $html .= '<div class="contenedor-img-general d-none" id="panel-img-tienda-' . $contadorRubros . '">';
                        
                                $tipo_orden = 'asc';
                                $campo_orden = 'orden';
                                $rs_condicion['type'] = 'defines-img-menu-header';
                                $rs_condicion['status'] = 'active';
                                $rs_condicion['sub_type'] = ucfirst(strtolower(rtrim($cate["categoria"])));
                                $lst_content = $Content->getTodos($campo_orden, $tipo_orden, true, $rs_condicion);

                                if(is_array($lst_content)) {
									foreach($lst_content as $rs_content) {
										$images = explode(",", $rs_content['image']);
                                        $html .= '<img src="nv/uploads/content/' . $images[0] . '">';
                                    }
                                }
                                
                            $html .= '</div>';

                            $contadorRubros++;
                        }

                    $html .= '</div>';


                $html .= '</div>';

            $html .= '</div>';
        $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    //$html .= '</nav>';

    return $html;
}

/*
 * listado menu ICONOS tipo modal Categoria/rubro/subrubro 
 * =============================================================================
 */

function listaMenuIconosModalRubroSubRubro($arrFiltro) {
    //    $categ= buscaCategorias($base);
    $categ = $arrFiltro["cate"];

    //$html = '<nav class="navbar navbar-expand-lg menu-modal">';
    //$html .= '<div class="container">';
    //$html = '<button class="btn-tienda" id="menuTienda" data-toggle="modal" data-target="#menuModal">Tienda</button>';
    //$html .= '</div>';
    $html = '<div class="menu-iconos-modal modal" id="menuIconosModal" tabindex="-1" aria-hidden="true">';
    $html .= '<div class="modal-dialog modal-lg">';
    $html .= '<div class="modal-content">';

    $html .= '<div class="modal-body">';
    //$html .= '<div class="row">';


                // panel tienda - rubro/subrubro 
                $html .= '<div class="paneles">';

                $contadorRubros = 1;

                    foreach ($categ as $idCat => $cate) {
                        $rubroTot = $arrFiltro["rub"][$idCat];

                        $html .= '<div class="row contenedor-menu-general d-none" id="panel-iconos-modal-tienda-' . $contadorRubros . '">';

                        $html .= '<div class="col-12">';
                        $html .= '<h2 class="titulo">' . ucfirst(strtolower(rtrim($cate["categoria"]))) . '</h2>';
                        $html .= '<button type="button" class="btn-close close-iconos-modal" onclick="closeIconosModal()"><i class="fa fa-times"></i></button>';
                        $html .= '</div>';

                        foreach ($rubroTot as $idrubro => $rubro) {

                            $subRu = $arrFiltro["srub"][$idCat][$idrubro];
                            $arrArgR = array(
                                "idcategoria" => $idCat,
                                "categoria" => $cate["categoria"],
                                "idrubro" => $idrubro,
                                "rubro" => $rubro["rubro"],
                                "idsubrubro" => null,
                                "subrubro" => null,
                                "idmarca" => null,
                                "marca" => null,
                                "idtipocliente" => null,
                                "tipocliente" => null
                            );

                                $html .= '<div class="col-sm-6 col-lg-4 col-xl-3 contenedor-categoria-menu">';
                                $html .= '<h5 class="titulo-categoria-menu"><a href="' . hacer_url("__", $arrArgR) . '" class="nv_subcat superiorRubros" tabindex="-1" >' . ucfirst(strtolower(rtrim($rubro["rubro"]))) . '</a></h5>';
                                $html .= '<ul class="lista-sub-categorias">';

                                        if (USOVERMAS == 'Si') {
                                            $cuantosSr = 0;
                                            $btnVerMas = '<li class="li_ver_mas"><a  href="javascript:void(0) "  onclick="verMasMenu(\'' . hacer_url("index.php", $arrArgR) . '\');" class="nv_link_todas_cat" tabindex="-1" >ver mas</a></li>';
                                            //subrubro
                                            foreach ($subRu as $idsubrubro => $sr) {
                                                $arrArg = array(
                                                    "idcategoria" => $idCat,
                                                    "categoria" => $cate["categoria"],
                                                    "idrubro" => $idrubro,
                                                    "rubro" => $rubro["rubro"],
                                                    "idsubrubro" => $idsubrubro,
                                                    "subrubro" => $sr["subrubro"],
                                                    "idmarca" => null,
                                                    "marca" => null,
                                                    "idtipocliente" => null,
                                                    "tipocliente" => null
                                                );
                                                if ($cuantosSr > 5) {
                                                    $html .= $btnVerMas;
                                                    $cuantos = 0;
                                                    break;
                                                }
                                                if ($cuantosSr < 5) {
                                                    $html .= '<li>'
                                                            . '<a tipo="subrubro" href="' . hacer_url("index.php", $arrArg) . '"  key="sr' . $idsubrubro . '" datos="' . $sr["subrubro"] . '" id="' . $idsubrubro . '" class="superiorSubrubros">' . ucfirst(strtolower($sr["subrubro"])) . '</a>'
                                                            . '</li>';
                                                }
                                                $cuantosSr++;
                                            }
                                        }

                                        if (USOVERMAS == 'No') {
                                            foreach ($subRu as $idsubrubro => $sr) {
                                                $arrArg = array(
                                                    "idcategoria" => $idCat,
                                                    "categoria" => $cate["categoria"],
                                                    "idrubro" => $idrubro,
                                                    "rubro" => $rubro["rubro"],
                                                    "idsubrubro" => $idsubrubro,
                                                    "subrubro" => $sr["subrubro"],
                                                    "idmarca" => null,
                                                    "marca" => null,
                                                    "idtipocliente" => null,
                                                    "tipocliente" => null
                                                );

                                                $html .= '<li>'
                                                        . '<a tipo="subrubro" href="' . hacer_url("index.php", $arrArg) . '"  key="sr' . $idsubrubro . '" datos="' . $sr["subrubro"] . '" id="' . $idsubrubro . '" class="superiorSubrubros">' . ucfirst(strtolower($sr["subrubro"])) . '</a>'
                                                        . '</li>';
                                            }
                                        }

                                $html .= '</ul>';
                                $html .= '</div>';

                            //$html .= '</div>';

                        }

                        $html .= '</div>';

                        $contadorRubros++;

                    }

                    $html .= '</div>';
                    // fin panel tienda - rubro/subrubro 

                $html .= '</div>';

            //$html .= '</div>';
        $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    //$html .= '</nav>';

    return $html;
}

/*
 * Funcion que envia las categorias al panel
 * =============================================================================
 */

function listaRubroSubRubroPanelNv($arrFiltro) {
    $categ = $arrFiltro["cate"];

    $categoriaNv = [];

    foreach ($categ as $idCat => $cate) {
        $rubroTot = $arrFiltro["rub"][$idCat];

        array_push($categoriaNv, ucfirst(strtolower(rtrim($cate["categoria"]))) );
    }

    //array_push($categoriaNv, 'Promociones' );

    return $categoriaNv;
}

/*
 * menu mobil 2020
 * =============================================================================
 */
function MenuMobilListaMenuRubroSubRubro($arrFiltro)
{
    //    $categ= buscaCategorias($base);
    $categ = $arrFiltro["cate"];

    $html = "";

    // categoria
    foreach ($categ as $idCat => $cate) {
        $rubroTot = $arrFiltro["rub"][$idCat];

        $html .= '<li>';

        $html .= '<span class="titulo ' . str_replace(" ", "-", strtolower(rtrim($cate["categoria"]))) . '">' . ucfirst(strtolower(rtrim($cate["categoria"]))) . '</span>';

        $html .= '<ul>';
        // rubro
        foreach ($rubroTot as $idrubro => $rubro) {
            $subRu = $arrFiltro["srub"][$idCat][$idrubro];
            $arrArgR = array(
                "idcategoria" => $idCat,
                "categoria" => $cate["categoria"],
                "idrubro" => $idrubro,
                "rubro" => $rubro["rubro"],
                "idsubrubro" => null,
                "subrubro" => null,
                "idmarca" => null,
                "marca" => null,
                "idtipocliente" => null,
                "tipocliente" => null
            );

            $html .= '<li>';

            $html .= '<span class="titulo ' . str_replace(" ", "-", strtolower(rtrim($rubro["rubro"]))) . '">' . ucfirst(strtolower(rtrim($rubro["rubro"]))) . '</span>';

            $html .= '<ul>';

            //subrubro
            foreach ($subRu as $idsubrubro => $sr) {
                $arrArg = array(
                    "idcategoria" => $idCat,
                    "categoria" => $cate["categoria"],
                    "idrubro" => $idrubro,
                    "rubro" => $rubro["rubro"],
                    "idsubrubro" => $idsubrubro,
                    "subrubro" => $sr["subrubro"],
                    "idmarca" => null,
                    "marca" => null,
                    "idtipocliente" => null,
                    "tipocliente" => null
                );

                $html .= '<li>'
                    . '<a tipo="subrubro" href="' . hacer_url("index.php", $arrArg) . '"  key="sr' . $idsubrubro . '" datos="' . $sr["subrubro"] . '" id="' . $idsubrubro . '" class="superiorSubrubros">' . ucfirst(strtolower($sr["subrubro"])) . '</a>'
                    . '</li>' . PHP_EOL;
            }

            $html .= '</ul></li>';
        }

        $html .= '</ul></li>';
    }

    // boton Promociones
    $html .= '<li>';
    $html .= '<a class="menu-promociones Promociones" href="#" onclick="promociones();"><i class="fas fa-tag"></i> ';
    $html .= 'Promociones';
    $html .= '</a>';
    $html .= '</li>';
    // fin boton promociones

    return $html;
}

// menu mobil 2019
function listaMenuRubroSubRubroMobil($arrFiltro)
{

    $categ = $arrFiltro["cate"];
    $html = "";

    // categoria
    foreach ($categ as $idCat => $cate) {
        $rubroTot = $arrFiltro["rub"][$idCat];

        $nombreCategoria = ucfirst(mb_strtolower(rtrim($cate["categoria"]), 'UTF-8'));

        foreach ($rubroTot as $idrubro => $rubro) {
            $subRu = $arrFiltro["srub"][$idCat][$idrubro];
            $arrArgR = array(
                "idcategoria" => $idCat,
                "categoria" => $cate["categoria"],
                "idrubro" => null,
                "rubro" => null,
                "idsubrubro" => null,
                "subrubro" => null,
                "idmarca" => null,
                "marca" => null,
                "idtipocliente" => null,
                "tipocliente" => null
            );

            $html .= '<li>'
                . '<a class="" href="' . hacer_url("index.php", $arrArgR) . '"  tabindex="-1" >' . $nombreCategoria . '</a>'
                . '</li>' . PHP_EOL;

            break;
        }
    }

    $html .= '<li><a href="#" onclick="promociones();"><i class="fas fa-tag"></i> Promociones</a></li>';
    $html .= '<li><a href="guia.php" ><i class="fas fa-store-alt"></i> Ver todos los articulos disponibles</a></li>';

    return $html;
}

function listaMenuRubroSubRubroGuia($arrFiltro)
{

    $categ = $arrFiltro["cate"];

    $html = '<div class="row contenedor-guia-general">';
    $html .= '<h2 class="titulo">' . rtrim($categ["categoria"]) . '</h2>';

    foreach ($categ as $idCat => $cate) {
        $rubroTot = $arrFiltro["rub"][$idCat];

        // cuantos rubros
        $cuantosRubros = 0;
        // rubro
        //$image = 1;
        foreach ($rubroTot as $idrubro => $rubro) {

            $subRu = $arrFiltro["srub"][$idCat][$idrubro];
            $arrArgR = array(
                "idcategoria" => $idCat,
                "categoria" => $cate["categoria"],
                "idrubro" => $idrubro,
                "rubro" => $rubro["rubro"],
                "idsubrubro" => null,
                "subrubro" => null,
                "idmarca" => null,
                "marca" => null,
                "idtipocliente" => null,
                "tipocliente" => null
            );

            $html .= '<div class="col-xs-12 col-sm-4 col-md-3 col-lg-3">'
                . '<h5 class="titulo-categoria-guia">'
                . '<a class="nv_subcat" href="#" tabindex="-1" >' . rtrim($rubro["rubro"])
                . '</h5>'
                . '<ul class="lista-sub-categorias">';

            //subrubro

            foreach ($subRu as $idsubrubro => $sr) {
                $arrArg = array(
                    "idcategoria" => $idCat,
                    "categoria" => $cate["categoria"],
                    "idrubro" => $idrubro,
                    "rubro" => $rubro["rubro"],
                    "idsubrubro" => $idsubrubro,
                    "subrubro" => $sr["subrubro"],
                    "idmarca" => null,
                    "marca" => null,
                    "idtipocliente" => null,
                    "tipocliente" => null
                );


                $html .= '<li>'
                    . '<a tipo="subrubro" href="' . hacer_url("index.php", $arrArg) . '"  key="sr' . $idsubrubro . '" datos="' . $sr["subrubro"] . '" id="' . $idsubrubro . '" class="lateralSubrubros">' . mb_convert_case($sr["subrubro"], MB_CASE_TITLE, "UTF-8") . '</a>'
                    . '</li>';
            }

            //$image++;

            $html .= '</ul>';
            $html .= '</div>';
        }
    }

    $html .= '</div>';

    return $html;
}

// listado medio de los comestibles..
function treslistasMenuRubroSubRubro($arrFiltros)
{
    $cat = $arrFiltros["cate"];
    // categorias

    $html = '<div class="row igualar-altura-padre-smp ajuste-margen">';
    foreach ($cat as $idCat => $cate) {

        $rubroTot = $arrFiltros["rub"][$idCat];
        $colores = array(1 => "descartablesL", 3 => "comestiblesL", 2 => "higieneL");
        $class = array(1 => "lista-descartables", 3 => "lista-comestibles", 2 => "lista-higiene");


        $html .= '<div class="col-md-4 col-sm-4 col-xs-12 ' . $class[$idCat] . '" >';
        $html .= '<h3>' . rtrim($cate["categoria"]) . '</h3>';
        $html .= '<div class="row">';
        $html .= '<ul class="nav navbar-nav">';
        //rubros
        foreach ($rubroTot as $idR => $rubro) {

            $html .= '<li class="dropdown">';
            $html .= '<a class="dropdown-toggle" data-toggle="dropdown" href="#">' . rtrim($rubro["rubro"]) . ' <i class="flecha"></i></a></li>';
            $html .= '<ul class="dropdown-menu">';
            $subR = $arrFiltros["srub"][$idCat][$idR];
            //subrubros
            foreach ($subR as $idSr => $sr) {
                $arrArg = array(
                    "idcategoria" => $idCat,
                    "categoria" => $cate["categoria"],
                    "idrubro" => $idR,
                    "rubro" => $rubro["rubro"],
                    "idsubrubro" => $idSr,
                    "subrubro" => $sr["subrubro"],
                    "idmarca" => null,
                    "marca" => null,
                    "idtipocliente" => null,
                    "tipocliente" => null
                );

                $html .= '<li>'
                    . '<a tipo="subrubro" href="' . hacer_url("index.php", $arrArg) . '" key="sr' . $idSr . '" datos="' . $sr["subrubro"] . '" id="' . $idSr . '" class="lateralSubrubros">' . $sr["subrubro"] . ' </a>'
                    . '</li>';
            }
            $html .= '</ul>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';
    }
    $html .= '</div>';


    return $html;
}

function listaOpcionRubroSub($base)
{
    $rubroTot = buscaRubro($base);
    $html = "<option value='|| Todas las categorías || '>Todos</option>";
    foreach ($rubroTot as $rubro) {
        $html .= '<option value="' . $rubro["idrubro"] . '| ||' . $rubro["rubro"] . '||" class="opTrubros" style="font-weight:bold;" ><b>' . $rubro["rubro"] . '</b></option>';
        $subRub = buscaSubRubro($base, $rubro["idrubro"]);
        foreach ($subRub as $sr) {
            $html .= '<option value="' . $rubro["idrubro"] . '|' . $sr["idsubrubro"] . '||' . $rubro["rubro"] . '||' . $sr["subrubro"] . '" class="opTsubrubros">&nbsp;&nbsp;&nbsp;  ' . $sr["subrubro"] . '</option>';
        }
    }
    return $html;
}

/*
 * Funcion Categorias menu LATERAL
 * -----------------------------------------------------------------------------
 */

function listaUlCategorias($arrFiltros)
{
    //    echo "rubros-subrubros listaulcategorias :::<pre>";
    //    print_r($arrFiltros);

    $cate = $arrFiltros["cate"];
    $html = '<ul id="filtro-categoria">';

    foreach ($cate as $idCat => $cat) {

        $arrArg = array(
            "idcategoria" => $idCat,
            "categoria" => ucwords($cat["categoria"]),
            "idrubro" => null,
            "rubro" => null,
            "idsubrubro" => null,
            "subrubro" => null,
            "idmarca" => null,
            "marca" => null,
            "idtipocliente" => null,
            "tipocliente" => null
        );
        $html .= '<li>'
            . '<a href="' . hacer_url("index.php", $arrArg) . '" tipo="categoria"  id="' . $idCat . '" class="lateralSubrubros">' .ucwords($cat["categoria"]) . ' (' . $cat["total"] . ')</a>'
            . '</li>';
    }
    $html .= '</ul>';
    return $html;
}

/*
 * funcion Rubros menu LATERAL
 * -----------------------------------------------------------------------------
 */

function listaUlRubro($arrFiltros, $codCategoria = null, $categoria = null)
{
    //$rubroTot = buscaRubro($base,$codCategoria);
    //    echo "rubros-subrubros.php=> listaUlrubro <pre>";
    //    echo "rubros lateral de una categoria:".$categoria.". arrFiltrosl que recibo promocion.-----<br>";
    //    print_R($arrFiltros);

    $rubroTot = $arrFiltros["rub"][$codCategoria];
    $cuantos = 1;
    /// $html='<h4>Rubros '.$categoria.'</h4>'; 
    $html = '<ul id="filtro-rubro">';
    if (USOVERMAS == 'Si') {
        foreach ($rubroTot as $idrubro => $rubro) {
            $arrArg = array(
                "idcategoria" => $codCategoria,
                "categoria" => ucwords($categoria),
                "idrubro" => $idrubro,
                "rubro" => ucwords($rubro["rubro"]),
                "idsubrubro" => null,
                "subrubro" => null,
                "idmarca" => null,
                "marca" => null,
                "idtipocliente" => null,
                "tipocliente" => null
            );

            // 
            if ($cuantos == 6) {
                $html .= '<div class="panel-group">'
                    . '<ul class="collapse" id="collapseExample">';
            }

            $html .= '<li>'
                . '<a href="' . hacer_url("index.php", $arrArg) . '" tipo="rubro"  id="' . $idrubro . '" class="lateralSubrubros">' . ucwords($rubro["rubro"]) . ' (' . $rubro["total"] . ')</a>'
                . '</li>';
            if ($cuantos == 5) {
                $html .= '</ul>';
            }
            $cuantos++;
        }


        // no llegue a 5
        if ($cuantos < 5) {
            $html .= '</ul>';
        }

        //supero las cinco opciones
        if ($cuantos > 6) {
            $html .= '</ul>'
                . '<button class="panel-heading btn btn-primary ver_mas collapsed" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">'
                . '<i class="fa fa-arrow-circle-up fa-md nv-up"><span> Ver menos</span></i><i class="fa fa-arrow-circle-down fa-md nv-down"><span> Ver mas</span></i>'
                . '</button>'
                . '</div>';
        }
    }

    if (USOVERMAS == 'No') {
        foreach ($rubroTot as $idrubro => $rubro) {
            $arrArg = array(
                "idcategoria" => $codCategoria,
                "categoria" => ucwords($categoria),
                "idrubro" => $idrubro,
                "rubro" => ucwords($rubro["rubro"]),
                "idsubrubro" => null,
                "subrubro" => null,
                "idmarca" => null,
                "marca" => null,
                "idtipocliente" => null,
                "tipocliente" => null
            );

            $html .= '<li>'
                . '<a href="' . hacer_url("index.php", $arrArg) . '" tipo="rubro"  id="' . $idrubro . '" class="lateralSubrubros">' . ucwords($rubro["rubro"]) . ' (' . $rubro["total"] . ')</a>'
                . '</li>';
        }
        $html .= '</ul>';
    }

    return $html;
}

/*
 * funcion subrubro LATERAL
 * ----------------------------------------------------------------------------
 * trae link con el subrubro pero ademas se lo genera como filtro.
 */

function listaUlSubrubro($arrFiltros, $idcategoria, $idrubro, $categoria, $rubro)
{
    $cuantos = 1;
    $html = "";
    // que sucede si no hay promociones para el rubro.... no poner el submenu.
    if (isset($arrFiltros["srub"][$idcategoria][$idrubro])) {

        $subRubroT = $arrFiltros["srub"][$idcategoria][$idrubro];

        // $html='<h4>'.$rubro.'</h4>';
        $html .= '<ul id="filtro-subrubro">';
        if(USOVERMAS=='Si'){
            foreach ($subRubroT as $idsubrubro => $subrubro) {
                $arrArg = array(
                    "idcategoria" => $idcategoria,
                    "categoria" => ucwords($categoria),
                    "idrubro" => $idrubro,
                    "rubro" => ucwords($rubro),
                    "idsubrubro" => $idsubrubro,
                    "subrubro" => ucwords($subrubro["subrubro"]),
                    "idmarca" => null,
                    "marca" => null,
                    "idtipocliente" => null,
                    "tipocliente" => null
                );
                if ($cuantos == 6) {
                    $html .= '<div class="panel-group">'
                        . '<ul class="collapse" id="collapseExample">';
                }
                $html .= '<li>'
                    //             . '<a href="'.hacer_url("index.php",$arrArg).'"  id="'.$idsubrubro.'" class="lateralSubrubros">'.$subrubro["subrubro"].' ('.$subrubro["total"].')</a>'
                    . '<a href="" tipo="subrubro"  key="sr' . $idsubrubro . '" datos="' . $subrubro["subrubro"] . '" id="' . $idsubrubro . '" class="lateralSubrubros">' . ucwords($subrubro["subrubro"]) . ' (' . $subrubro["total"] . ')</a>'
                    . '</li>';
                // limite de opciones
                if ($cuantos == 5) {
                    $html .= '</ul>';
                }
                $cuantos++;
            }


            // no llegue a 5
            if ($cuantos < 5) {
                $html .= '</ul>';
            }

            //supero las cinco opciones
            if ($cuantos > 6) {
                $html .= '</ul>'
                    . '<button class="panel-heading btn btn-primary ver_mas collapsed" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">'
                    . '<i class="fa fa-arrow-circle-up fa-md nv-up"><span> Ver menos</span></i><i class="fa fa-arrow-circle-down fa-md nv-down"><span> Ver mas</span></i>'
                    . '</button>'
                    . '</div>';
            }
        }

        if(USOVERMAS=='No'){
            foreach ($subRubroT as $idsubrubro => $subrubro) {
                $arrArg = array(
                    "idcategoria" => $idcategoria,
                    "categoria" => ucwords($categoria),
                    "idrubro" => $idrubro,
                    "rubro" => ucwords($rubro),
                    "idsubrubro" => $idsubrubro,
                    "subrubro" => ucwords($subrubro["subrubro"]),
                    "idmarca" => null,
                    "marca" => null,
                    "idtipocliente" => null,
                    "tipocliente" => null
                );
                
                $html .= '<li>'
                    // . '<a href="'.hacer_url("index.php",$arrArg).'"  id="'.$idsubrubro.'" class="lateralSubrubros">'.$subrubro["subrubro"].' ('.$subrubro["total"].')</a>'
                    . '<a href="'.hacer_url("index.php",$arrArg).'" tipo="subrubro"  key="sr' . $idsubrubro . '" datos="' . $subrubro["subrubro"] . '" id="' . $idsubrubro . '" class="lateralSubrubros">' . ucwords($subrubro["subrubro"]) . ' (' . $subrubro["total"] . ')</a>'
                    . '</li>';
                
            }
            $html .= '</ul>';

        }
    }

    if (!isset($arrFiltros["srub"][$idcategoria][$idrubro])) {
        $html .= '<ul id="filtro-subrubro">';
        $html .= '<li>'
            //             . '<a href="'.hacer_url("index.php",$arrArg).'"  id="'.$idsubrubro.'" class="lateralSubrubros">'.$subrubro["subrubro"].' ('.$subrubro["total"].')</a>'
            . 'sin resultados'
            . '</li>';
        $html .= '</ul>';
    }
    return $html;
}

function listaULMarcas($arrMc, $categoria = null, $rubro = null, $subRubro = null, $marca = null)
{

    $arrMarca = array();
    $arrM = array();
    $html = "";
    //    echo "<pre>";
    //    echo var_dump($marca);
    //    echo "</pre>";
    if (!empty($arrMc)) {
        //       [idcategoria] => 3
        //    [idrubro] => 21
        //    [idsubrubro] => 325
        //    [idmarca] => 130
        //    [rubro] => Gastronomía
        //    [subrubro] => Enlatados
        //    [categoria] => Comestibles
        //    [marca] => SILVIA
        //    [total] => 10
        // voy a buscar y sumar las marcas que haya.
        //voy a elimiar las marcas que seleccione.
        foreach ($arrMc as $id => $m) {

            if ($marca == null || $marca !== $m["idmarca"]) {
                $arrM[$id] = $m;
            }
        }
        //        echo "<pre>";
        //print_r($arrM);
        //echo "</pre>";
        foreach ($arrM as $id => $m) {
            //hay categoria

            if ($categoria != null && $categoria != 0) {
                //hay rubro
                if ($rubro != null) {
                    //hay subrubro
                    if ($subRubro != null) {
                        if ($m["idcategoria"] == $categoria && $m["idrubro"] == $rubro && $m["idsubrubro"] == $subRubro) {
                            if (key_exists($m["idmarca"], $arrMarca)) {
                                $arrMarca[$m["idmarca"]]["total"] = $arrMarca[$m["idmarca"]]["total"] + $m["total"];
                            } else {
                                $arrMarca[$m["idmarca"]]["idmarca"] = $m["idmarca"];
                                $arrMarca[$m["idmarca"]]["idcategoria"] = $m["idcategoria"];
                                $arrMarca[$m["idmarca"]]["categoria"] = $m["categoria"];
                                $arrMarca[$m["idmarca"]]["idrubro"] = $m["idrubro"];
                                $arrMarca[$m["idmarca"]]["rubro"] = $m["rubro"];
                                $arrMarca[$m["idmarca"]]["idsubrubro"] = $m["idsubrubro"];
                                $arrMarca[$m["idmarca"]]["subrubro"] = $m["subrubro"];
                                $arrMarca[$m["idmarca"]]["marca"] = $m["marca"];
                                $arrMarca[$m["idmarca"]]["total"] = $m["total"];
                            }
                        }
                    } else {
                        // sin subrubro
                        if ($m["idcategoria"] == $categoria && $m["idrubro"] == $rubro) {
                            if (key_exists($m["idmarca"], $arrMarca)) {
                                $arrMarca[$m["idmarca"]]["total"] = $arrMarca[$m["idmarca"]]["total"] + $m["total"];
                            } else {
                                $arrMarca[$m["idmarca"]]["idmarca"] = $m["idmarca"];
                                $arrMarca[$m["idmarca"]]["idcategoria"] = $m["idcategoria"];
                                $arrMarca[$m["idmarca"]]["categoria"] = $m["categoria"];
                                $arrMarca[$m["idmarca"]]["idrubro"] = $m["idrubro"];
                                $arrMarca[$m["idmarca"]]["rubro"] = $m["rubro"];
                                $arrMarca[$m["idmarca"]]["marca"] = $m["marca"];
                                $arrMarca[$m["idmarca"]]["total"] = $m["total"];
                            }
                        }
                    }
                } else {
                    // sin rubro
                    if ($m["idcategoria"] == $categoria) {
                        if (key_exists($m["idmarca"], $arrMarca)) {
                            $arrMarca[$m["idmarca"]]["total"] = $arrMarca[$m["idmarca"]]["total"] + $m["total"];
                        } else {
                            //$arrMarca[$m["idmarca"]]=$m;
                            $arrMarca[$m["idmarca"]]["idmarca"] = $m["idmarca"];
                            $arrMarca[$m["idmarca"]]["idcategoria"] = $m["idcategoria"];
                            $arrMarca[$m["idmarca"]]["categoria"] = $m["categoria"];
                            $arrMarca[$m["idmarca"]]["marca"] = $m["marca"];
                            $arrMarca[$m["idmarca"]]["total"] = $m["total"];
                        }
                    }
                }
            } else {
                // sin categoria o con promociones traigo todo
                if (key_exists($m["idmarca"], $arrMarca)) {
                    $arrMarca[$m["idmarca"]]["total"] = $arrMarca[$m["idmarca"]]["total"] + $m["total"];
                } else {
                    $arrMarca[$m["idmarca"]]["idmarca"] = $m["idmarca"];
                    $arrMarca[$m["idmarca"]]["marca"] = $m["marca"];
                    $arrMarca[$m["idmarca"]]["total"] = $m["total"];
                }
            }
        }



        //$html ='<h4>Marcas</h4>';
        $html .= '<ul id="filtro-marcas">';
        //        echo "arrm<pre>";
        //        print_r($arrMarca);
        //        echo "</pre>";
        $cuantos = 1;
        if (USOVERMAS == 'Si') {
            foreach ($arrMarca as $ma) {
                //               echo "arrm<pre>";
                //        print_r($ma);
                //        echo "</pre>";
                if ($cuantos == 6) {
                    $html .= '<div class="panel-group">'
                        . '<ul class="collapse" id="collapseMarca">';
                }

                $html .= '<li>'
                    //. '<a href="'.hacer_url("index.php",$ma).'"  clave="'.$ma["idmarca"].'| ||'.$ma["marca"].'||" id="'.$ma["idmarca"].'" class="lateralSubrubros">'.ucfirst(strtolower($ma["marca"])).' ('.$ma["total"].') </a>'
                    . '<a tipo="marca" datos="' . $ma["marca"] . '" key="ma' . $ma["idmarca"] . '" id="' . $ma["idmarca"] . '" class="lateralSubrubros">' . $ma["marca"] . ' (' . $ma["total"] . ')</a>'
                    . '</li>';
                // limite de opciones
                if ($cuantos == 5) {
                    $html .= '</ul>';
                }
                $cuantos++;
            } // fin de las marcas
            // no llegue a 5
            if ($cuantos < 5) {
                $html .= '</ul>';
            }

            //supero las cinco opciones
            if ($cuantos > 6) {
                $html .= '</ul>'
                    . '<button class="panel-heading btn btn-primary ver_mas collapsed" type="button" data-toggle="collapse" data-target="#collapseMarca" aria-expanded="false" aria-controls="collapseMarca">'
                    . '<i class="fa fa-arrow-circle-up fa-md nv-up"><span> Ver menos</span></i><i class="fa fa-arrow-circle-down fa-md nv-down"><span> Ver mas</span></i>'
                    . '</button>'
                    . '</div>';
            }
        }

        if (USOVERMAS == 'No') {
            foreach ($arrMarca as $ma) {
               
                $html .= '<li>'
                    //. '<a href="'.hacer_url("index.php",$ma).'"  clave="'.$ma["idmarca"].'| ||'.$ma["marca"].'||" id="'.$ma["idmarca"].'" class="lateralSubrubros">'.ucfirst(strtolower($ma["marca"])).' ('.$ma["total"].') </a>'
                    . '<a href="'.hacer_url("index.php",$ma).'" tipo="marca" datos="' . $ma["marca"] . '" key="ma' . $ma["idmarca"] . '" id="' . $ma["idmarca"] . '" class="lateralSubrubros">' . $ma["marca"] . ' (' . $ma["total"] . ')</a>'
                    . '</li>';
                // limite de opciones

                $cuantos++;
            } // fin de las marcas
            $html .= '</ul>';
        }
    }

    return $html;
}

function listaUlTipoNegocio($base)
{
}

function LimpiarURL($texto)
{

    $salida = $texto;

    $salida = preg_replace("/Á/", "A", $salida);
    $salida = preg_replace("/á/", "a", $salida);
    $salida = preg_replace("/É/", "E", $salida);
    $salida = preg_replace("/é/", "e", $salida);
    $salida = preg_replace("/Í/", "I", $salida);
    $salida = preg_replace("/í/", "i", $salida);
    $salida = preg_replace("/Ó/", "O", $salida);
    $salida = preg_replace("/ó/", "o", $salida);
    $salida = preg_replace("/Ú/", "U", $salida);
    $salida = preg_replace("/ú/", "u", $salida);
    $salida = preg_replace("/Ñ/", "N", $salida);
    $salida = preg_replace("/ñ/", "n", $salida);

    $salida = preg_replace("/[\/\&%#\$]/", "_", $salida);
    $salida = preg_replace("/[\"\']/", "", $salida);

    $salida = str_replace(" ", "_", $salida);
    $salida = strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $salida));
    return $salida;
}

function hacer_url($url, $arrArg = null)
{
    // seteo
    $urlVuelta = $url;
    $idCategoria = null;
    $nomCategoria = null;
    $idRubro = null;
    $nomRubro = null;
    $idSubRubro = null;
    $nomSubRubro = null;
    $idMarca = null;
    $nomMarca = null;
    $idTipoCliente = null;
    $nomTipoCliente = null;
    $argumentos = array();
    $ids = array();
    //    echo "arg<pre>";
    //    print_r($arrArg);
    //    echo "</pre>";
    if ($arrArg != null) {
        $idCategoria = 0;
        $nomCategoria = '';
        $idRubro = 0;
        $nomRubro = '';
        $idSubRubro = 0;
        $nomSubRubro = '';
        $idMarca = 0;
        $nomMarca = '';
        $idTipoCliente = 0;
        $nomTipoCliente = '';


        if (isset($arrArg["idcategoria"]) && $arrArg["idcategoria"] != null)
            $idCategoria = $arrArg["idcategoria"];
        if (isset($arrArg["categoria"]) && $arrArg["categoria"] != null)
            $nomCategoria = LimpiarURL($arrArg["categoria"]);
        if (isset($arrArg["idrubro"]) && $arrArg["idrubro"] != null)
            $idRubro = $arrArg["idrubro"];
        if (isset($arrArg["rubro"]) && $arrArg["rubro"] != null)
            $nomRubro = LimpiarURL($arrArg["rubro"]);
        if (isset($arrArg["idsubrubro"]) && $arrArg["idsubrubro"] != null)
            $idSubRubro = $arrArg["idsubrubro"];
        if (isset($arrArg["subrubro"]) && $arrArg["subrubro"] != null)
            $nomSubRubro = LimpiarURL($arrArg["subrubro"]);
        if (isset($arrArg["idmarca"]) && $arrArg["idmarca"] != null)
            $idMarca = $arrArg["idmarca"];
        if (isset($arrArg["marca"]) && $arrArg["marca"] != null)
            $nomMarca = LimpiarURL($arrArg["marca"]);
        if (isset($arrArg["idtipocliente"]) && $arrArg["idtipocliente"] != null)
            $idTipoCliente = $arrArg["idtipocliente"];
        if (isset($arrArg["tipocliente"]) && $arrArg["tipocliente"] != null)
            $nomTipoCliente = LimpiarURL($arrArg["tipocliente"]);




        $pre = '' . $nomCategoria . O . $nomRubro . O . $nomSubRubro . O . $nomMarca . O . $nomTipoCliente . O;
        $pre = str_replace("//", "", $pre);
        $pre .= $idMarca . P . $idCategoria . P . $idRubro . P . $idSubRubro . P . $idTipoCliente . '.htm';
    }
    /*
      if($idCategoria!=null){
      $argumentos[]="categoria=".$nomCategoria;
      $ids[]="idcategoria=".$idCategoria;
      }
      if($idRubro!=null){
      $argumentos[]="rubro=".$nomRubro;
      $ids[]="idrubro=".$idRubro;
      }
      if($idSubRubro!=null){
      $argumentos[]="subrubro=".$nomSubRubro;
      $ids[]="idsubrubro=".$idSubRubro;
      }
      if($idMarca!=null){
      $argumentos[]="marca=".$nomMarca;
      $ids[]="idmarca=".$idMarca;
      }
      if($idTipoCliente!=null){
      $argumentos[]="tipocliente=".$nomTipoCliente;
      $ids[]="idtipocliente=".$idTipoCliente;
      }

      // pego los resultados
      if(!empty($argumentos)){
      $urlVuelta .="?".implode("&", $argumentos);
      }


      if(!empty($ids)&&!empty($argumentos)){
      $urlVuelta .="&".implode("&", $ids);
      }else{
      if(!empty($ids)){
      $urlVuelta .="?".implode("&", $ids);
      }
      } */

    $urlVuelta = $pre;
    /* 	
      https://www.chapini.com/catalogo-nv-nv/index.php?categoria=Comestibles&rubro=Chocolate&subrubro=Ba%C3%B1os%20de%20Reposter%C3%ADa&idcategoria=3&idrubro=17&idsubrubro=303
     */
    return $urlVuelta;
}

function makeing_url($url, $arrArg = null)
{
    // seteo
    $urlVuelta = $url;
    $idCategoria = null;
    $nomCategoria = null;
    $idRubro = null;
    $nomRubro = null;
    $idSubRubro = null;
    $nomSubRubro = null;
    $idMarca = null;
    $nomMarca = null;
    $idTipoCliente = null;
    $nomTipoCliente = null;
    $argumentos = array();
    $ids = array();
    //    echo "arg<pre>";
    //    print_r($arrArg);
    //    echo "</pre>";
    if ($arrArg != null) {
        $idCategoria = $arrArg["idcategoria"];
        $nomCategoria = $arrArg["categoria"];
        $idRubro = $arrArg["idrubro"];
        $nomRubro = $arrArg["rubro"];
        $idSubRubro = $arrArg["idsubrubro"];
        $nomSubRubro = $arrArg["subrubro"];
        $idMarca = $arrArg["idmarca"];
        $nomMarca = $arrArg["marca"];
        $idTipoCliente = $arrArg["idtipocliente"];
        $nomTipoCliente = $arrArg["tipocliente"];
    }

    if ($idCategoria != null) {
        $argumentos[] = "categoria=" . $nomCategoria;
        $ids[] = "idcategoria=" . $idCategoria;
    }
    if ($idRubro != null) {
        $argumentos[] = "rubro=" . $nomRubro;
        $ids[] = "idrubro=" . $idRubro;
    }
    if ($idSubRubro != null) {
        $argumentos[] = "subrubro=" . $nomSubRubro;
        $ids[] = "idsubrubro=" . $idSubRubro;
    }
    if ($idMarca != null) {
        $argumentos[] = "marca=" . $nomMarca;
        $ids[] = "idmarca=" . $idMarca;
    }
    if ($idTipoCliente != null) {
        $argumentos[] = "tipocliente=" . $nomTipoCliente;
        $ids[] = "idtipocliente=" . $idTipoCliente;
    }

    // pego los resultados
    if (!empty($argumentos)) {
        $urlVuelta .= "?" . implode("&", $argumentos);
    }


    if (!empty($ids) && !empty($argumentos)) {
        $urlVuelta .= "&" . implode("&", $ids);
    } else {
        if (!empty($ids)) {
            $urlVuelta .= "?" . implode("&", $ids);
        }
    }
    return $urlVuelta;
}

/**
 * a partir de un filtro de subrubro armo el link para el filtro. y se lo agrego a la session.
 */
function caminoFiltroHtml($keySubRubro, $idSubrubroFiltro)
{
    $categ = $_SESSION["t"];
    //echo "<pre>";
    //echo "idSubRubroFiltro::".$idSubrubroFiltro;
    //print_r($categ);
    //echo "<hr>";

    #subrubro

    foreach ($categ['srub'] as $idCat => $cate) {
        //print_r($idCat);

        foreach ($cate as $idrubro => $rubro) {
            //print_r($idrubro);

            foreach ($rubro as $idsubrubro => $subrubro) {
                //echo var_dump($idsubrubro);
                //echo "<hr>";
                if ($idSubrubroFiltro == $idsubrubro) {
                    $arrArg = array(
                        "idcategoria" => $idCat,
                        "categoria" => $categ["cate"][$idCat]["categoria"],
                        "idrubro" => $idrubro,
                        "rubro" => $categ["rub"][$idCat][$idrubro]["rubro"],
                        "idsubrubro" => $idsubrubro,
                        "subrubro" => $subrubro["subrubro"],
                        "idmarca" => null,
                        "marca" => null,
                        "idtipocliente" => null,
                        "tipocliente" => null
                    );
                    //print_r($arrArg);
                    //echo "<hr>";
                    //$link = '<a tipo="subrubro" href="' . hacer_url("index.php", $arrArg) . '"  key="sr' . $idsubrubro . '" datos="' . $subrubro["subrubro"] . '" id="' . $idsubrubro . '" class="superiorSubrubros">' . ucfirst(strtolower($subrubro["subrubro"])) . '</a>';
                    //$_SESSION['rastroDeMigas'] = 
                    $_SESSION['rastroDeMigas']= array(
                        'link' => hacer_url("index.php", $arrArg),
                        'tipo' => 'subrubro',
                        'id' => $idsubrubro,
                        'datos'=>$subrubro["subrubro"]
                    );
                    
                    
                    //hacer_url("index.php", $arrArg);
                    //$_SESSION['rastroDeMigasTexto'] .='/'.ucfirst(strtolower($subrubro["subrubro"]));  
                    return;
                }
            }
        }
    }


    ///echo "</pre>";
}

# PREMIOS 


/**
 * # recupero las categorias para los premios 
 */
function listaMenuCategoriasPremios($connV){
    //echo var_dump($connV);
    $html='';
    $sqlCategoria="SELECT 
                    catep.id_categoria_abm_premios As id,
                    catep.descripcion_categoria_premios AS categoria,
                    catep.url_foto As foto 
                    FROM sp_categoria_abm_premios AS catep
                    WHERE catep.anulado='No';";
    $hacerC=mysqli_query($connV,$sqlCategoria)or die('hubo un inconveniente con el lista menu categorias'.mysqli_error($connV).'<pre>'.$sqlCategoria.'</pre>');
    
    if($hacerC){
        while($c= mysqli_fetch_assoc($hacerC)){
//            $html .='<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 contenedor-categoria-menu">';
//            $html .='<a href="premios.php?idcat='.$c["id"].'&cat='.$c["categoria"].'">';
//            $html .='        <img src="foto.php?catp='.$c["id"].'&url='.$c["foto"].'&mini=1" title="IberoClub '.$c["categoria"].'" alt="IberoClub '.$c["categoria"].'">';
//            $html .='        <br>'.$c["categoria"];
//            $html .='</a>';
//            $html .='</div>';
            
            $html .='<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 contenedor-categoria-menu">';
            $html .='        <a href="premios.php?idcatpremio='.$c["id"].'&cat='.$c["categoria"].'">';
            $html .='                <img src="foto.php?catp='.$c["id"].'&url='.$c["foto"].'&mini=1" title="'.NOMBRETIENDA.' '.$c["categoria"].'" alt="'.NOMBRETIENDA.' '.$c["categoria"].'">';
            $html .='                <br><div><span>'.$c["categoria"].'</span></div>';
            $html .='        </a>';
            $html .='</div>';
        }
    }
    echo $html;
                                                                                                    
}


/**
 * #Listado de cateogorias para opciones
 */
function trae_arr_categoria_premios($connV){
    $sqlCategoria="SELECT 
                    catep.id_categoria_abm_premios As id,
                    catep.descripcion_categoria_premios AS categoria,
                    catep.url_foto As foto 
                    FROM sp_categoria_abm_premios AS catep
                    WHERE catep.anulado='No';";
    $hacerC=mysqli_query($connV,$sqlCategoria)or die('hubo un inconveniente con el array de las categorias de premios.'.mysqli_error($connV).'<pre>'.$sqlCategoria.'</pre>');
    $arrCatP=array();
    if($hacerC){
        while($c= mysqli_fetch_assoc($hacerC)){
            $arrCatP[]=$c;
        }
    }
    return $arrCatP;
}


/** 
 * #Limite menor y mayor PREMIOS
 * ====================
 */
function trae_limite_premios($connV,$cat=null){
    $where="";
    if($cat){
        $where=" WHERE p.id_categoria_abm_premios=".$cat;
    }
    $sqlLim="SELECT
                    MAX(p.puntos_premios)AS maximo,
                    MIN(p.puntos_premios) AS minimo 
                    FROM sp_abm_premios AS p {$where}";
    $hacerL=mysqli_query($connV,$sqlLim)or die('fallo el limite'.mysqli_error($connV).'<pre>'.$sqlLim.'</pre>');
    $arrL=mysqli_fetch_assoc($hacerL);
//    echo $sqlLim;
    return $arrL;
}