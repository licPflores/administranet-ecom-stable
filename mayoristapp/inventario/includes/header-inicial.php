<?php

/* variables de sesion y parametros INICIALES
 * ==============================================================================
 */
$conexionT = $connV;

// manejo de CATEGORIA RUBROS SUBRUBROS  
// -----------------------------------------------------------------------------
if (!isset($_SESSION["f"])) {
    // echo "no hay sesion busco de nuevo";
    $f = arrCatRubSub($conexionT, 1);


    $_SESSION["f"]  = $f;
} else {
    //    echo "tengo la sesion a mano.<pre>";
    //    print_r($_SESSION);
    $f = $_SESSION["f"];
}


/*
 * Manejo de menu SUPERIOR
 * -----------------------------------------------------------------------------
 */
if (!isset($_SESSION["t"])) {
    $t = arrCatRubSub($conexionT, 0);
    $_SESSION["t"]  = $t;
} else {
    $t = $_SESSION["t"];
}

/*
 * manejo de MARCAS
 * -----------------------------------------------------------------------------
 */

if (!isset($_SESSION["m"])) {

    $m = arrBuscaMarca($conexionT);
    $_SESSION["m"]  = $m;
} else {
    $m = $_SESSION["m"];
}


//exit;
// evalua si ha sido cargado los articulos para la busqueda rapida.
// -----------------------------------------------------------------------------
if (!isset($_SESSION["ar"])) {
    $artRapido = arrArtiRapido($conexionT);
    $_SESSION["ar"] = $artRapido;
}
// el precio x defcto. si se muestra o no precio cuando se loguea una persona
// -----------------------------------------------------------------------------


// el cliente
$buscaArt = null;
$breadCrumb = "";
$arrBcrumb = array();
$arrFiltroSeleccion = array(); // filtra opciones.

# FILTROS
// ---------------------------------------------------------------------------
// se resetean siempre 
if (isset($_SESSION["arrFiltros"])) {
    $arrFiltroSeleccion = $_SESSION["arrFiltros"];
    //echo tengo filtros si son promociones, o mis compras o filtro sr.
    if (isset($arrFiltroSeleccion["promo"]) && isset($_GET["categoria"])) {
        // echo " isset arrFiltros hay filtros a aplicar.... ::<br>BORRO RUBROS";
        //echo print_R($arrFiltroSeleccion);
        unset($_SESSION["f"]);
        unset($_SESSION["m"]);
        $f = arrCatRubSub($conexionT, 1);
        $m = arrBuscaMarca($conexionT);
        $_SESSION["f"] = $f;
        $_SESSION["m"] = $m;
    }
}

//echo "<pre>";
//print_r($_SESSION["arrFiltros"]);
//echo "</pre>";

// ordenamiento de catalogo
// -----------------------------------------------------------------------------
$orden = null;
if (isset($_SESSION["ordenBy"])) {
    $orden = $_SESSION["ordenBy"];
}

$miEleccion = "";
$enpromocion = null;
$esCatalogo = 'no';
$claseLista = "galeria";


// agregar tambien limpieza de breadcrumb y busqueda.
//echo "antes del get<pre>";
//echo var_dump (empty($_GET)&&empty($_POST));
// echo "</pre>";
//echo "<pre>";
//print_r($_SERVER);
//echo "</pre>";



/*
 * VARIABLES GET estoy navegando el catalogo.
 * ============================================================================
 */
// no debe ingresar si hay vuelta de ml

if (isset($_GET) && !empty($_GET) && !isset($_GET["trx"]) && !isset($_GET["estado"])&& !isset($_GET["link-pago"])) {

    //echo "GET::::::::::INICIAL heder inicial. <PRE>";
    //print_r($_GET);
    ////    print_r($f);
    //echo "</pre>";
    //PROMOCIONES sera la categoria CERO hacer que muestre las categorias listadas y agrupadas.

    $esCatalogo = 'si';

    $usaTabla = 1;
    // si hice click en un rubro traigo el rubro, si no en el subrubro trae el rubro
    // categoria categoria
    // rubro categoria
    //subrubro rubro categoria
    $arrArg = array();
    //    $arrArg=array(
    //            "idcategoria"=>$idcategoria,
    //            "categoria"=>$categoria,
    //            "idrubro"=>$idrubro,
    //            "rubro"=>$rubro,
    //            "idsubrubro"=>$idsubrubro,
    //            "subrubro"=>$subrubro["subrubro"],
    //            "idmarca"=>null,
    //            "marca"=>null,
    //            "idtipocliente"=>null,
    //            "tipocliente"=>null
    //		);
    // debo usar el sessin f, donde tengo todos los rubros, subrubros y categorias
    // por el cambio de nombre de ruta ahora debo sacarlos de esta opcion.

    //echo "LOS GET QUE VIENEN:::::<br><pre>";
    //print_r($_GET);
    //echo "</pre>";



    /*
    * PROMOCIONES y CATEGORIAS
    * =========================================================================
    */

    if(isset($_GET['categoria']) && isset($_GET['rubro']) && isset($_GET['subrubro']) && isset($_GET['marca']) && isset($_GET['cliente'])){
        
        //control valor en cero todos
        if($_GET['categoria'] == "0" && $_GET['rubro'] == "0" && $_GET['subrubro'] == "0" && $_GET['marca'] == "0" && $_GET['cliente'] == "0" ){

            if(!isset($_SESSION['arrFiltros']['promo'])){

                //echo  'alert("Soy las promociones de afuera")'.PHP_EOL;

                $_SESSION['arrFiltros']['promo'] = array(
                            "link" => '<a class="link-filtro-activo" id="promo" value="1" >'
                                    . '<span>En Promoción</span>'
                                    . '</a>',
                            "tipo" => 'promociones',
                            "id" => 1
                );

                $claseLista = "galeria";
                $idCategoria = 0; 
                $idRubro = 0; 
                $idSubRubro = 0; 
                $idMarca = 0; 
                $buscaArt = null; 
                $enpromocion = "si"; 
                $arrFiltroSeleccion = $_SESSION['arrFiltros']; 
                $orden = null;

                $tabla = $articulos->mostrar_productos_listado(1, "catalogo", $claseLista, $idCategoria, $idRubro, $idSubRubro, $idMarca, $buscaArt, $enpromocion, $arrFiltroSeleccion, $orden);
                
                //una vez puesto los filtros hay que volver a cargar
                //header("Location: promociones_____0-0-0-0-0.htm");
                //die();

            }
        }
    }
    
    if (isset($_GET["categoria"]) && $_GET["categoria"] == "0") {




        $idCategoria = $_GET["categoria"];
        //        echo var_dump($f["cate"][$idCategoria]["categoria"]);
        //        $categoria= $_GET["categoria"];
        $categoria = "Promociones";


        $arrArg["idcategoria"] = $idCategoria;
        $arrArg["categoria"] = $categoria;
        $linkAnte = hacer_url("index.php", $arrArg);
        $_SESSION['rastroDeMigas'] = $linkAnte;
        //$arrBcrumb[]='<a href="'.$linkAnte.'" class="bread-crumb" >'.$categoria.'</a>';
        //$arrBcrumb[]='<a href="'.$linkAnte.'" class="bread-crumb" >'.htmlentities($categoria).'</a>';
        $miEleccion = $categoria;
    }
    /*
     * RUBRO SIN PROMOCIONES
     * ========================================================================
     */

    //    if(isset($_GET["rubro"])&&$_GET["rubro"]!=="0" && (isset($_GET["categoria"])&&$_GET["categoria"]==="0")){
    //        $idRubro= $_GET["rubro"];
    //        $rubro = $f["rub"][$idCategoria][$idRubro]["rubro"];
    //        
    //        //$rubro=$_GET["rubro"];
    //        
    //        
    //        $arrArg["idrubro"]=$idRubro;
    //        $arrArg["rubro"]=$rubro;
    //        $linkAnte= hacer_url("index.php", $arrArg); 
    //       // $arrBcrumb[]='<a href="'.$linkAnte.'" class="bread-crumb" >'.$rubro.'</a>';
    //        $arrBcrumb[]='<a  href="'.$linkAnte.'" class="bread-crumb" >'.htmlentities($rubro).'</a>';
    //        $miEleccion=$rubro;
    //        
    //    }

    /*
    * CATEGORIAS NO PROOMOCIONES
    * ==========================================================================
    */

    if (isset($_GET["categoria"]) && $_GET["categoria"] !== "0") {
        //echo "estoy dentro de categorias no promociones<br>";
        //debo restablecer marcas y rubros.
        $m = arrBuscaMarca($conexionT);

        $_SESSION["m"]  = $m;
        // sin filtro piso la variable sesion.
        if (!isset($arrFiltroSeleccion)) {
            $_SESSION["f"]  = $t;
            $f = $t;
        }

        $idCategoria = $_GET["categoria"];
        //echo var_dump($f["cate"][$idCategoria]["categoria"]);
        //echo var_dump($f);

        //echo var_dump($_SESSION['f']);
        //        $categoria= $_GET["categoria"];
        $categoria = $f["cate"][$idCategoria]["categoria"];


        $arrArg["idcategoria"] = $idCategoria;
        $arrArg["categoria"] = $categoria;
        $linkAnte = hacer_url("index.php", $arrArg);
        //$arrBcrumb[]='<a href="'.$linkAnte.'" class="bread-crumb" >'.$categoria.'</a>';
        $arrBcrumb[] = '<a href="' . $linkAnte . '" class="bread-crumb" >' . htmlentities($categoria) . '</a>';
        $_SESSION['rastroDeMigas'] = array(
            'link' => $linkAnte,
            'tipo' => 'categoria',
            'id' => $idCategoria,
            'datos' => $categoria
        );

        $miEleccion = $categoria;
    }

    /*
     * RUBRO SIN PROMOCIONES
     * ========================================================================
     */

    if (isset($_GET["rubro"]) && $_GET["rubro"] !== "0") {
        $idRubro = $_GET["rubro"];
        $rubro = $f["rub"][$idCategoria][$idRubro]["rubro"];

        //echo print_R($_GET);


        $arrArg["idrubro"] = $idRubro;
        $arrArg["rubro"] = $rubro;
        $linkAnte = hacer_url("index.php", $arrArg);
        // $arrBcrumb[]='<a href="'.$linkAnte.'" class="bread-crumb" >'.$rubro.'</a>';
        $arrBcrumb[] = '<a  href="' . $linkAnte . '" class="bread-crumb" >' . htmlentities($rubro) . '</a>';
        $_SESSION['rastroDeMigas'] = array(
            'link' => $linkAnte,
            'tipo' => 'rubro',
            'id' => $idRubro,
            'datos' => $rubro
        );
        // filtros 

        $miEleccion = $rubro;
    }


    //    if(!isset($_GET["idsubrubro"])&&isset($_SESSION["arrFiltros"])){
    //        unset($_SESSION["arrFiltros"]);
    //        unset($_SESSION["ordenBy"]);
    //        $arrFiltroSeleccion=array();
    //        $orden=null;
    //    }


    /** 
     * SUBRUBRO SIN PROMOCIONES
     * -----------------------------------------------------------------------------
     */
    if (isset($_GET["subrubro"]) && $_GET["subrubro"] !== "0") {
        $idSubRubro = $_GET["subrubro"];
        $subRubro = $f["srub"][$idCategoria][$idRubro][$idSubRubro]["subrubro"];

        $arrArg["idsubrubro"] = $idSubRubro;
        $arrArg["subrubro"] = $subRubro;
        //puede haber mas de un filtro aca
        $linkAnte = hacer_url("index.php", $arrArg);
        $arrBcrumb[] = '<a href="' . $linkAnte . '">' . $subRubro . '<a/>';
        //$arrFiltroSeleccion["sr".$idSubRubro] = '<a class="link-filtro-activo" id="sr'.$idSubRubro.'" value="sr'.$idSubRubro.'" >'
        //                    .'<span>'.$subRubro.'</span>'
        //                    .' <i class="fas fa-times fa-lg fa-fw"></i>'
        //                .'</a>';
        //$usoFiltro++;

        $_SESSION["arrFiltros"] = $arrFiltroSeleccion;
        $_SESSION['rastroDeMigas'] = array(
            'link' => $linkAnte,
            'tipo' => 'subrubro',
            'id' => $idSubRubro,
            'datos' => $subRubro
        );
        $miEleccion = $subRubro;
    }

    /*
    if(isset($_GET["idmarca"])){
        $idMarca= $_GET["idmarca"];
        $marca=$_GET["marca"];
       
        // link de eliminacion del filtro.
        $linkAnte= hacer_url("index.php", $arrArg);
        
        // hacer la url sin los filtros que voy a eliminar.
        $arrFiltroSeleccion["marc".$idMarca] = '<a class="link-filtro-activo" href="" id="marc'.$idMarca.'" value="marc'.$idMarca.'">'
                            .'<span>'.$marca.'</span>'
                            .' <i class="fas fa-times fa-lg fa-fw"></i>'
                        .'</a>';
          $_SESSION["arrFiltros"]=$arrFiltroSeleccion;
        $arrArg["idmarca"]=$idMarca;
        $arrArg["marca"]=$marca;
        $usoFiltro++;
        $linkPost= hacer_url("index.php", $arrArg);
         $arrBcrumb[]='<a href="'.$linkPost.'">'.$marca.'<a/>';
    }
    
     */

    // en promocion cuando se loguea muestra los porcentajes disponibles.
    /*if(isset($_GET["enpromocion"])){
        $enpromocion="si";
        $linkAnte= hacer_url("index.php", $arrArg);
        $arrFiltroSeleccion["prom"] = '<a class="link-filtro-activo" href="" id="prom" value="prom">'
                            .'<span>promociones</span>'
                            .' <i class="fas fa-times fa-lg fa-fw"></i>'
                        .'</a>';
          $_SESSION["arrFiltros"]=$arrFiltroSeleccion;
        $usoFiltro++;
        $linkPromo= hacer_url("index.php", $arrArg)."&enpromocion=si";
    }

    <a tipo="subrubro" href="lacteos_frescos_lacteos_frescos_arroces___0-4-109-242-0.htm" key="sr242" datos="ARROCES" id="242" class="superiorSubrubros">Arroces</a>
    */
    //echo print_r($arrFiltroSeleccion);

    # armando el breadcrumb
    $breadCrumb = implode(' / ', $arrBcrumb);
    //$_SESSION['rastroDeMigas'] = $breadCrumb;
    $_SESSION["rastroDeMigasTexto"] = strip_tags($breadCrumb);
    $_SESSION["rastroDeMigasTexto"] = ucwords(strtolower($_SESSION["rastroDeMigasTexto"]));

    if (isset($_SESSION["arrFiltros"])) {
        $losFiltros = $_SESSION["arrFiltros"];
        foreach ($losFiltros as $idF => $elFiltro) {
            $_SESSION['rastroDeMigasTexto'] .= ' / ' . strip_tags($elFiltro["link"]);

            caminoFiltroHtml($idF, $elFiltro['id']);
        }
    }

    $_SESSION["rastroDeMigasTexto"] = ucwords(strtolower($_SESSION["rastroDeMigasTexto"]));
    $_SESSION["rastroDeMigasTexto"] = str_replace(' / ', '/', $_SESSION["rastroDeMigasTexto"]);


    // directamente busco lo que haya...

    //    echo "<pre>ingrese aca llamo al ajax....<br>";
    //    echo var_dump($articulos->mostrar_articulo_lista_cat(1,"catalogo",$claseLista,$idCategoria,$idRubro,$idSubRubro,$idMarca,$buscaArt,$enpromocion,$arrFiltroSeleccion,$orden));

    // no tengo que ingresar aca 
    if (!isset($_REQUEST['IDArt'])) {
        if (!isset($idCategoria)) {
            $idCategoria = null;
        }
        if (!isset($idRubro)) {
            $idRubro = null;
        }
        if (!isset($idSubRubro)) {
            $idSubRubro = null;
        }
        if (!isset($idMarca)) {
            $idMarca = null;
        }
        if (!isset($buscaArt)) {
            $buscaArt = null;
        }
        if (!isset($enpromocion)) {
            $enpromocion = null;
        }
        if (!isset($arrFiltroSeleccion)) {
            $arrFiltroSeleccion = null;
        }
        if (!isset($orden)) {
            $orden = null;
        }

        // funcion que recupera los productos a mostrar 
        //echo __DIR__;
        # PREMIOS

        if(defined('USOPREMIOS')&& USOPREMIOS=='Si'&& isset($_GET['idcatpremio'])){
            $esCatalogo='no';

        }

        if($esCatalogo=='si'){
            $tabla = $articulos->mostrar_productos_listado(1, "catalogo", $claseLista, $idCategoria, $idRubro, $idSubRubro, $idMarca, $buscaArt, $enpromocion, $arrFiltroSeleccion, $orden);
        }
    }
}


/*
 * VARIABLES POST el producto seleccionado individual
 * ============================================================================
 */



if (isset($_GET["producto"])) {
    $esCatalogo = 'si';
    $usaTabla = 1;
    $miEleccion = $_GET["producto"];
    $buscaArt = $_GET["producto"];

    $idCategoria = null;
    $idRubro = null;
    $idSubRubro = null;
    $idMarca = null;
    $tabla = $articulos->mostrar_productos_listado(1, "catalogo", $claseLista, $idCategoria, $idRubro, $idSubRubro, $idMarca, $buscaArt);
}

// voy a traer los mas vendidos con la funcion mostrar_articulo_lista 
// solo en el inicio.
#index inicial sin catalogo.
if (empty($_GET) && empty($_POST)) {
    $esCatalogo = 'no';
    unset($_SESSION["arrFiltros"]);
    unset($_SESSION["ordenBy"]);
    $arrFiltroSeleccion = array();
    $orden = null;
}
