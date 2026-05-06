<?

// jCart v1.3
// http://conceptlogic.com/jcart/

// This file takes input from Ajax requests and passes data to jcart.php
// Returns updated cart HTML back to submitting page

header('Content-type: text/html; charset=utf-8');

// Include jcart before session start
require_once 'sesion.inc.php';
require_once 'ajax-articulos.php';

// Process input and return updated cart HTML
$claseLista = null;
$tipoCliente = null;

if (isset($_POST['buscarProducto']) && $_POST['buscarProducto'] == 1) {
    $categoria = null;
    $rubro          = null;
    $subRubro       = null;
    $marca          = null;
    $modelo         = null;
    $laboratorio    = null;
    $buscRapida     = null; // nombre del producto
    $idArt          = null;
    $idDeposito     = null;
    $claseBusqueda  = null; // si busco por texto o id articul o texto,codigo
    $tipoCliente    = null;
    $idTipoCliente  = null;
    $queCampo       = null;
    $promo          = null;
    $consumo        = null;
    $tacc = null;
    $proveedor = null;
    $listaDePrecio=null;
    $ivaIncluido    ='No';
    $arrParametros = array();

    // "buscarProducto"    : 1,                               
    // "queArticulo"       : nombreArticulo,                   
    // "idArticulo"        : idArticulo,                   
    // "claseBusca"        : claseBusca, texto o codigo

    // echo 'Posteo<pre>',print_r($_POST),'</pre>',PHP_EOL;

    if (isset($_POST["queCampo"])) {
        $queCampo = $_POST["queCampo"];
    }

    if (isset($_POST['categoria']) && $_POST['categoria'] != "") {
        $categoria = $_POST['categoria'];
    }
    if (isset($_POST['rubro']) && $_POST['rubro'] != "") {
        $rubro = $_POST['rubro'];
    }
    if (isset($_POST['subrubro']) && $_POST['subrubro'] != "") {
        $subRubro = $_POST['subrubro'];
    }
    if (isset($_POST['marca']) && $_POST['marca'] != "") {
        $marca = $_POST['marca'];
    }
    if (isset($_POST['modelo']) && $_POST['modelo'] != "") {
        $modelo = $_POST['buscaModelo'];
    }
    if (isset($_POST['laboratorio']) && $_POST['laboratorio'] != "") {
        $laboratorio = $_POST['buscaLaboratorio'];
    }
    if (isset($_POST['queArticulo']) && $_POST['queArticulo'] != "") {
        $buscRapida = $_POST['queArticulo'];
    }

    if (isset($_POST['idArticulo']) && $_POST['idArticulo'] != "") {
        $idArt = $_POST['idArticulo'];
    }
    // si texto o codigo
    if (isset($_POST["claseBusca"])) {
        $claseBusqueda = $_POST["claseBusca"];
    }
    if (isset($_POST['promo']) && $_POST['promo'] == "1") {
        $promo = $_POST['promo'];
    }
    if (isset($_POST['consumo']) && $_POST['consumo'] == "1") {
        $consumo = $_POST['consumo'];
    }
    if (isset($_POST['ivaIncluido'])) {
        $ivaIncluido = $_POST['ivaIncluido'];
    }
    if (isset($_POST['tipoCliente'])) {
        $tipoCliente = $_POST['tipoCliente'];
    }
    if(isset($_POST['imagenProducto'])){
        $imagenProducto = $_POST['imagenProducto'];
    }
    if(isset($_POST['proveedor'])&& $_POST['proveedor'] != ""){
        $proveedor = $_POST['proveedor'];
    }
    if(isset($_POST['tacc'])&& $_POST['tacc'] != ""){
        $tacc = $_POST['tacc'];
    }
    if(isset($_POST['listaDePrecio'])&& $_POST['listaDePrecio'] != ""){
        $listaDePrecio = $_POST['listaDePrecio'];
    }

    if(isset($_POST['queAccion'])&&$_POST['queAccion']){
        $queAccion=$_POST['queAccion'];
    }



    $arrParametros['categoria'] = $categoria;
    $arrParametros['rubro']          = $rubro;
    $arrParametros['subrubro']       = $subRubro;
    $arrParametros['marca']          = $marca;
    $arrParametros['modelo']         = $modelo;
    $arrParametros['laboratorio']    = $laboratorio;
    $arrParametros['buscRapida']     = $buscRapida; // nombre del producto
    $arrParametros['idArt']          = $idArt;
    $arrParametros['idDeposito']     = $idDeposito;
    $arrParametros['claseBusqueda']  = $claseBusqueda; // si busco por texto o id articul o texto,codigo
    $arrParametros['tipoCliente']    = $tipoCliente;
    $arrParametros['idTipoCliente']  = $idTipoCliente;
    $arrParametros['queCampo']       = $queCampo;
    $arrParametros['promo']          = $promo;
    $arrParametros['consumo']        = $consumo;
    $arrParametros['ivaIncluido']        = $ivaIncluido;
    $arrParametros['imagenProducto'] = $imagenProducto;
    $arrParametros['proveedor'] = $proveedor;
    $arrParametros['tacc'] = $tacc;
    $arrParametros['listaDePrecio'] = $listaDePrecio;

    $arrParametros['queAccion'] = $queAccion;
    // echo '<pre>',print_r($arrParametros),'</pre>';

    // diferente tipo de lista de productos.
    if (isset($_POST["queAccion"])) {

        //dependiendo del dato de tipo de cliente
        if ($queAccion == 'consumo') {
            $articulos->mostrar_consumos(0);
        }
        if ($queAccion == 'listaPrecios') {
            //  *lista de precios 
            // echo 'dentro del tipocliente Si<pre>',$tipoCliente,'</pre>';
            $articulos->mostrar_articulo_lista($arrParametros, "listap");
        }
        // lista de precios para pdf , uso mostarr articulo lista le paso que es listaprecioPdf y que si es asi no imprima simular lo que aca se hace en exporta lista
        //* lista de promcciones
        if ($queAccion == 'promociones') {
            //$articulos->mostrar_articulo_lista(0, "ranking", $claseLista);
            $articulos->mostrar_listado_promociones($arrParametros);
        }
        // listado de promociones
        if ($queAccion == 'ranking') {
            $articulos->mostrar_articulo_lista(0, "ranking", $claseLista);
        }
        // lista de precios catalogo.
        if ($queAccion == 'catalogo') {

            $articulos->mostrar_articulo_lista(0, "catalogo", $claseLista);
        }
    }


    // lista de productos para pedido,remito, etc...
    if (!isset($_POST['queAccion'])) {
        $articulos->mostrar_articulo($arrParametros);
    }

}


