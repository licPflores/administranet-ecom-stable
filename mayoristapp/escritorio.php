<?php 
// lo saco y lo mando directo al pedido.

session_start();
    $caminoDispo="";
    if(isset( $_SESSION['caminoDisp'])){
        $caminoDispo = $_SESSION['caminoDisp'];
    }
    session_write_close();
    // require_once $caminoDispo.'jcart/jcart.php';
    require_once 'jcart/jcart.php';

    require_once 'sesion.inc.php';
 require_once 'funciones-comunes.php';
 
 
/**
 * variables de configuracion para colocar los encabezados
 */
// echo "sesion:=><pre>";
// print_r($_SESSION);
// echo "</pre>";

$uTablas    = 0;
$uModal     = 0;
$uSlider    = 1;
$uGui       = 0;
$iconoDisabled = 1;
$usaZoom =0;
$consultaLista = "";
$masComprados="no";
$verOfertas ="no";
// codigo de redireccion directa
//  if($_SESSION["tipousuario"]== "vendedor"){
    // header("Location: listado-clientes.php");
    header("Location: dashboard-estadisticas.php");

//  }
//  if($_SESSION["tipousuario"]== "cliente"){
//     header("Location: alta_pedido.php");
//  }
 
    if(!empty($objCliente)){
        $promoLista     = strtolower(str_replace(' ','',$objCliente->listaPrecio));
        $consultaLista  = "AND articulo.promocion_{$promoLista} ='Si'";
        
    }else{
        //localizar la lista de precios del consumidor final
        
    }
    /*
     * Tipo de usuario.
     */
    // VENDEDOR
    if($_SESSION['tipousuario']=="vendedor"){
        $consultaLista .=" AND (articulo.ecommerce = 'Si' OR articulo.ecommerce='No') ";
        $masComprados="no";
        $verOfertas ="no";
    }
    //CLIENTE
    if($_SESSION['tipousuario']=="cliente"){
        $consultaLista .=" AND articulo.ecommerce = 'Si'  ";
        $masComprados="si";
        $verOfertas = "si";
    }
    /*
     * Gerencia
     */
    // para los gerentes no les interesa ver los mas comprados
    if($_SESSION["inf_gerenciales"]=="Si"){
        $masComprados ="no";
        $verOfertas ="no";
    }
    
    
    /*
     * Colores Rubros
     */
    $coloRubro = array();
    if(isset($_SESSION["colorRubro"])){
        $colorRubro = $_SESSION["colorRubro"];
    }
//    print_r($colorRubro);
/**
 * OFERTAS
 */    
//     consultando las ofertas
   if($verOfertas=="si"){
   }
    
    
    
    /*
     * Mas Vendidos
     * ajusto la version para mostrar por cada uno de los rubros que hay  
     */
    
    

    // determino el tipo de cliente, si no se ha seleccionado que me traiga los mas vendidos
    //de todos los tipos de cliente
    if($masComprados=="si"){
        
    }
    
    
//    if(empty($objArticulos)&& empty($objArticulosTop)){
//        header('Location:listado-clientes.php');
//    }
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $localidad = setlocale(LC_TIME, NULL); #Guarda localización regional actual
    //$localidad = setlocale(LC_TIME, 'es_AR.utf8'); # Localiza en español es_Cenezuela
    setlocale(LC_TIME, 'es_AR');
	
    ?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Escritorio | administraNET e-com </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    <?php require_once 'cabecera.php';?>
   
</head>
<body>
    <div id="wrapper">
        <?php             require_once $barra;             ?>
        
        <div id="content">
           
</div>
</div>
             
       

 <?php require_once 'footer.php';?>
    
    </div>
    </body>
</html>