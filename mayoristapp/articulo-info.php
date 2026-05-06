<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * title= articulo info
 * Desc: voy a mostrar la info del articulo con la foto y la descripcin de la oferta
 * mas los otros datos y desde aqui si ya comprar el articulo es mas que nada una vista
 * para el cliente pero hace falta.
 */

session_start();
$caminoDispo = $_SESSION['caminoDisp'];
session_write_close();
require_once $caminoDispo.'jcart/jcart.php';
require_once 'sesion.inc.php';
 require_once 'funciones-comunes.php';
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 0;
$uModal     = 0;
$uSlider    = 0;
$uGui       = 0;
$usaZoom    = 1;
$iconoDisabled  = 1;
$consultaLista = "";
$clienteObj=array();
$codCliente=null;
if(isset($_SESSION['cliente'])&&is_object($_SESSION['cliente'])){
        $clienteObj = $_SESSION['cliente'];
        $codCliente=$clienteObj->Codigo;
}
if(isset($_SESSION['cliente'])&& is_array($_SESSION['cliente'][0])){
        $clienteObj = $_SESSION['cliente'][0];
        $codCliente=$clienteObj->Codigo;
    }
//    echo "<pre>";
//    print_r($clienteObj);
//    echo "</pre>";
if(!empty($clienteObj)){
    $listaPrecioCliente = $clienteObj->listaPrecio;
    $promoLista     = strtolower(str_replace(' ','',$clienteObj->listaPrecio));
    $consultaLista  = "AND articulo.promocion_{$promoLista} ='Si'";
    
}else{
    //localizar la lista de precios del consumidor final
    $listaPrecioCliente = "Lista 1";
    $promoLista     = "lista 1";
    $consultaLista  = "AND articulo.promocion_lista1 ='Si'";

}

/*
 * Embalaje
 */
if($_SESSION["utilizaEmbalaje"]=="Si"){
    $usaEmbalaje = "Si";
}else{
    $usaEmbalaje = "No";
}
/*
* Colores Rubros
*/
$coloRubro = array();
if(isset($_SESSION["colorRubro"])){
   $colorRubro = $_SESSION["colorRubro"];
}

/**
 * Reglas de precio?
 */

$usoRegla = $_SESSION["usaReglaPrecio"];
$campoReglaPrecio="";
$sqlReglaPrecio="";
 if($usoRegla=="Si" && $codCliente!=null){
            $campoReglaPrecio="rp.tipo_calculo,rp.importe_regla,";
            $sqlReglaPrecio="LEFT JOIN reglas_precio AS rp ON  
                            (rp.id_articulo = articulo.IDArt 
                            AND rp.id_cliente={$codCliente} 
                            AND  ('".date('Y-m-d')."' BETWEEN rp.vigencia_desde AND rp.vigencia_hasta) 
                            AND rp.anulado='No' )";
        }

/**
 * OFERTAS UNICA
 */
$idArticulo = $_GET["IDArt"];
$artEnPromocion = $_GET["buscaOferta"];

//     consultando las ofertas
   $sqlDate="SET lc_time_names = 'es_AR';";
   mysqli_query($connV,$sqlDate);
   $sqlArticulo = "SELECT 
                        articulo.id_manual,
                        marca.NombreMarca AS Marca,
                        modelo.NombreModelo AS Modelo,
                        articulo.IDArt,
                        articulo.detalle_web,
                        articulo.Detalle,
                        articulo.IDSubRubro, 
                        articulo.CodigoSubRubro,
                        articulo.CodigoRubro,
                        articulo.CodigoMarca,
                        articulo.Moneda,
                        articulo.CodigoArticuloT,
                        articulo.NombreArticulo,
                        articulo.impuesto_interno,
                        articulo.promocion,
                        articulo.promocion_por,
                        articulo.promocion_cant,
                        articulo.promocion_alcance,
                        articulo.promocion_tipo,
                        articulo.promocion_listaoficial,
                        articulo.promocion_lista1,
                        articulo.promocion_lista2,
                        articulo.promocion_lista3,
                        articulo.promocion_lista4,
                        articulo.promocion_lista5,
                        articulo.promocion_destacado_web,
                        articulo.promocion_vigencia_desde,
                        articulo.promocion_vigencia_hasta,
                        articulo.CodigoProveedor,
                        DATE_FORMAT(articulo.promocion_vigencia_hasta,'%W %d de %M del %Y') as fhastaT,
                        DATE_FORMAT(articulo.promocion_vigencia_desde,'%W %d de %M del %Y') as fdesdeT,
                        articulo.tipoIVA,
                        iva.Alicuota AS Alic, 
                        rubro.NombreRubro AS NombRub, 
                        subrubro.NombreSubRubro AS NombSubRub,
                        rubro_categoria.nombre_categoria AS NombCategoria,
                        articulo.Precio1V,
                        articulo.Precio2V,
                        articulo.Precio3V,
                        articulo.Precio4V,
                        articulo.Precio5V,
                        articulo.PNOficial, 
                        articulo.PFOficial,
                        articulo.Precio1VI,
                        articulo.Precio2VI,
                        articulo.Precio3VI,
                        articulo.Precio4VI,
                        articulo.Precio5VI,
                        (articulo.Precio1VI-articulo.Precio1V) AS impIva1,
                        (articulo.Precio2VI-articulo.Precio2V) AS impIva2,
                        (articulo.Precio3VI-articulo.Precio3V) AS impIva3,
                        (articulo.Precio4VI-articulo.Precio4V) AS impIva4,
                        (articulo.Precio5VI-articulo.Precio5V) AS impIva5,
                        (articulo.PFOficial-articulo.PNOficial) AS impOf,
                        (articulo.Precio1V*articulo.impuesto_interno/100) AS imp_interno1,
                        (articulo.Precio2V*articulo.impuesto_interno/100) AS imp_interno2,
                        (articulo.Precio3V*articulo.impuesto_interno/100) AS imp_interno3,
                        (articulo.Precio4V*articulo.impuesto_interno/100) AS imp_interno4,
                        (articulo.Precio5V*articulo.impuesto_interno/100) AS imp_interno5,
                        (articulo.PNOficial*articulo.impuesto_interno/100) AS imp_interoOf,
                        articulo.impuesto_interno,    
                        articulo_prov.multiplicador_comp,
                        articulo_prov.cantidad_uni, 
                        unidmed.descrip_corta AS nombre_unimed,
                        presentacion_abm.nombre_presentacion, 
                        articulo_prov.id_presentacionC,
                         {$campoReglaPrecio}
                        articulo_prov.id_unimed
                        
                        FROM articulo
                            LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt AND articulo_prov.CodProveedor = articulo.CodigoProveedor)
                            LEFT JOIN unidMed ON (unidMed.id_unimed = articulo_prov.id_unimed) 
                            LEFT JOIN presentacion_abm ON (presentacion_abm.id_presentacion = articulo_prov.id_presentacionC)
                            
                            LEFT JOIN iva  ON articulo.Alicuota = iva.id 
                            LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
                            LEFT JOIN rubro ON Rubro.CodigoRubro = articulo.CodigoRubro
                            LEFT JOIN rubro_categoria ON rubro_categoria.id_categoria=rubro.id_categoria
                            LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
                            LEFT JOIN marca ON marca.CodMarca = modelo.CodMarca
                             {$sqlReglaPrecio}
                        WHERE
                            articulo.IdArt={$idArticulo}
                                                       
                          ORDER BY articulo.NombreArticulo LIMIT 30;";
                            //echo $sqlArticulo
                            
    $hacer = mysqli_query($connV,$sqlArticulo) or die('No puedo recuperar los articulos en promocion'.mysqli_error($connV).$sqlArticulo);
    $objArticulos = array();
    while($art = mysqli_fetch_object($hacer)){
        $objArticulos[] = $art;
    }
    if(empty($objArticulos)){
        header('Location:listado-clientes.php');
    }
    $artt = $objArticulos[0];
//    echo "<pre>";
//    print_r($artt);
//    echo "</pre>";
    $nombreArt= $artt->NombreArticulo;
    
    //Promocion por Cantidad - intervalo.  [promocion_tipo] => Cantidad - Intervalo
    $arrPromoIntervalo = array();
    if($artt->promocion_tipo=='Cantidad - Intervalo'){
        $sqlPi="SELECT "
                . "ai.desde_cantidad,ai.hasta_cantidad,ai.monto_descuento "
                . "FROM articulo_promo_intervalo AS ai "
                . "WHERE ai.id_articulo=" . $artt->IDArt." "
                . "AND ai.anulado='No'";
        
        $hacerAi = mysqli_query($connV,$sqlPi) or die("No puedo recuperar las promociones por intervalo <pre>".$sqlPi."</pre>".mysqli_error($connV));
        while($ai=  mysql_fetch_assoc($hacerAi)){
            $arrPromoIntervalo[] = $ai;
        }
        
    }
//        echo "<pre>";
//    print_r($arrPromoIntervalo);
//    echo "</pre>";
    
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $localidad = setlocale(LC_TIME, NULL); #Guarda localización regional actual
    //$localidad = setlocale(LC_TIME, 'es_AR.utf8'); # Localiza en español es_Cenezuela
    setlocale(LC_TIME, 'es_AR');
//    echo "<pre>";
//    print_r($_SESSION);
//    echo "</pre>";
    
    ?>
<!DOCTYPE HTML>
<html>
<head>
    <title><?php echo $nombreArt;?> | administraNET e-com </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    <?php require_once 'cabecera.php';?>

</head>
<body>
    <div id="wrapper">
        <?php 
            require_once $barra;
        ?>
        
        <div id="content">
<!--          <h1 style="text-align:center;"><img src="_img/logo_administranet_mediano.png"/></h1> -->
            
          <?php if(!empty($objArticulos)):?>
            <section class="product-info">
                <div class="container">
                        <div class="row">
                            <?php foreach ($objArticulos as $promo): ?>
                            <?php 
                                /*
                                 * EMBALAJE
                                 */
                                $bulto="";
                                if($usaEmbalaje=="Si"){
                                    // tengo que hacer la busqueda de los valores para mostrar
                                    $bulto = $promo->nombre_presentacion ." x ".$promo->cantidad_uni;
                                    if($promo->nombre_unimed!=""){
                                        $bulto .= " (".$promo->nombre_unimed.")";
                                    }
                                }
                                $precioT = calculaPrecioTodos($connV,$promo, $clienteObj);
//                                echo "<pre>";
//                                print_r($precioT);
//                                echo "</pre>";
//                                echo "<pre>";
//                                print_r($promo);
//                                echo "</pre>";
                                /*
                                 * PROMOCIONES
                                 */
                                 $srcArticulo   ="";
                                 $textoPromo    ="";
                                 
                                 //* validez de la promocion /*//
                                 
                                 if($precioT["promo"]=="si"){
        
//                                        $textoPromo .='<div class="promocion promo-grilla"><i class="fa fa-gift fa-lg fa-fw"></i> Promoción</div>';
                                         $textoPromo ='<div><p><i class="fa fa-gift fa-lg fa-fw"></i>PROMOCIÓN</p>';
//                                        $textoPromo .='<div class="text-promo">';
                                        $textoPromo .= "<p><i>";
                                        $textoPromo .= detalle_promo($precioT["promoTipo"],$precioT["descuento"],$precioT["cantidad"],$promo->IDArt,$connV);
                                        $textoPromo .= vigencia_promo_detalle($promo->promocion_vigencia_desde, $promo->promocion_vigencia_hasta);

                                        $textoPromo .="</i></p></div>";
                                }else{
                                    $srcArticulo = 'alta_pedido.php?buscaOferta=no&IDArt='.$promo->IDArt.'&cant=1';
                                }
                                 
                                 
//                                if($promo->promocion=="Si"){ 
//                                    $textoPromo ='<div><p>PROMOCIÓN</p>';
//                                    // si la promocion es cantidad intervalo se hace diferente la promocion.
//                                    
//                                        // evaluar si es valida la promocion.
//                                        if($promo->promocion_vigencia_desde != null || $promo->promocion_vigencia_hasta != null){
//                                            $fd = explode('-', $promo->promocion_vigencia_desde);
//                                            $fh = explode('-', $promo->promocion_vigencia_hasta);
//                                            $desde = mktime(0, 0, 0, $fd[1], $fd[2], $fd[0]);
//                                            $hasta = mktime(0, 0, 0, $fh[1], $fh[2], $fh[0]);
//                                            $hoyD = getdate();
//                                            $hoy = mktime(0, 0, 0, $hoyD['mon'], $hoyD['mday'], $hoyD['year']);
//                                           
//                                            if ($hoy <= $desde && $hoy >= $hasta) {
//                                                /*
//                                                 * Promocion NO Valida de intervalo
//                                                 */
//
//                                                $textoPromo = "";
//                                                $srcArticulo = 'alta_pedido.php?buscaOferta=no&IDArt='.$promo->IDArt.'&cant=1';
//                                            }else{
//                                                // intervalo valido 
//                                               // trabajar con fechas de las promociones
////                                                $desde = $promo->fdesdeT;
////                                                $hasta = $promo->fhastaT;
//                                               
//                                                //setlocale(LC_TIME, $localidad); // Vuelve a la localidad regional anterior  C
//                                                
//                                                
//                                                
//                                                // la promocion aplica entonces debo analizar el tipo de promocion.
//                                                if($promo->promocion_tipo=="Cantidad - Intervalo"){
////                                                    echo "<pre>";
////                                                print_R($arrPromoIntervalo);
////                                                echo "</pre>";
//                                        
//                                                    if(!empty($arrPromoIntervalo)){
//                                                        foreach($arrPromoIntervalo as $ii){
//            //                                                $textoPromo .="<p><i class='fa fa-gift fa-lg'></i> ". number_format($promo->promocion_por,null) ."% de descuento</p>";
//
//                                                            $textoPromo .= "<p class='verde'> <i class='fa fa-gift fa-lg'></i>  ". number_format($ii["monto_descuento"],0) ."% de descuento por la compra de <strong>" . number_format($ii["desde_cantidad"],null) ."</strong> o más unidades.</p>";
//                                                        }
//                                                    }
//                                                    $srcArticulo = 'alta_pedido.php?buscaOferta=no&IDArt='.$promo->IDArt.'&cant=1';
//                                                }else{
//                                                    // promocion comun
//                                                    if($precioT["descuento"]!=0){
//                                                        $textoPromo .="<p class='verde'><i class='fa fa-gift fa-lg'></i> ". number_format($promo->promocion_por,0) ."% de descuento</p>";
//                                                    }
//                                                    if($promo->promocion_cant>0){
//                                                        $textoPromo .= "<p>por la compra de <strong>" . number_format($promo->promocion_cant,null) ."</strong> o más unidades.</p>";
//                                                    }
//            ////                                    
//
//                                                    $srcArticulo = 'alta_pedido.php?buscaOferta=si&IDArt='.$promo->IDArt.'&cant='.number_format($promo->promocion_cant,null);
//                                                }
//                                            }
//                                                                                           
//                                            
//                                        }else{
//                                        // la promocion no vence.x lo tanto es valida , debo hacer evaluacion del tipo de promcion.
//                                            if($promo->promocion_tipo=="Cantidad - Intervalo"){
//                                                
//                                        
//                                                    if(!empty($arrPromoIntervalo)){
//                                                        foreach($arrPromoIntervalo as $ii){
//            //                                                $textoPromo .="<p><i class='fa fa-gift fa-lg'></i> ". number_format($promo->promocion_por,null) ."% de descuento</p>";
//
//                                                            $textoPromo .= "<p> <i class='fa fa-gift fa-lg'></i> <strong>". number_format($ii["desde_cantidad"],null) ."%</strong> de descuento por la compra de <strong>" . number_format($ii["monto_descuento"],null) ."</strong> o más unidades.</p>";
//                                                        }
//                                                    }
//                                            }else{
//                                                if($precioT["descuento"]!=0){
//                                                    $textoPromo .="<p class='verde'><i class='fa fa-gift fa-lg'></i> ". number_format($promo->promocion_por,2) ."% de descuento</p>";
//                                                }
//                                                if($promo->promocion_cant>0){
//                                                    $textoPromo .= "<p>por la compra de <strong>" . number_format($promo->promocion_cant,null) ."</strong> o más unidades.</p>";
//                                                }
//        ////                                    
//
//                                                $srcArticulo = 'alta_pedido.php?buscaOferta=si&IDArt='.$promo->IDArt.'&cant='.number_format($promo->promocion_cant,null);
//                                            }
//                                        }
                                    
//                                }else{
                                    $srcArticulo = 'alta_pedido.php?buscaOferta=no&IDArt='.$promo->IDArt.'&cant=1';
//                                }    
                            ?>
                                <div class="span4">
                                    <div class="product-images">
                                        <div class="box">
                                            <div class="primary targetarea diffheight">
                                                <img class="custom" alt="zoomable" id="papito" src="foto.php?origen=foto1|<?php echo $promo->IDArt; ?>&mini=1" >
                                            </div>
                                       
                                            <div class="papito thumbs" id="gallery">
                                                <ul class="thumbs-list">
                                                    <li>
                                                        <a data-large="foto.php?origen=foto2|<?php echo $promo->IDArt; ?>&mini=0" href="foto.php?origen=foto1|<?php echo $promo->IDArt; ?>&mini=1">
                                                            <img src="foto.php?origen=foto1|<?php echo $promo->IDArt; ?>&mini=2" alt="Thumbnails">
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a data-large="foto.php?origen=foto2|<?php echo $promo->IDArt; ?>&mini=0" href="foto.php?origen=foto2|<?php echo $promo->IDArt; ?>&mini=1">
                                                            <img src="foto.php?origen=foto2|<?php echo $promo->IDArt; ?>&mini=2" alt="Thumbnails">
                                                        </a>
                                                    </li>
                                                </ul>    
                                            </div>
                                        </div>       
                                    </div>
                                </div>    

                                <div class="span8">
                                    <div class="product-content">
                                        <div class="box">
                                            <ul class="nav nav-tabs">
                                                <li class="active">
                                                    <a href="#product" >
                                                        <i class="fa fa-bars fa-lg"></i>
                                                        <span>Producto</span>
                                                    </a>
                                                </li>
                                                
                                            </ul>
                                            <div class="tab-content">
                                                <div class="tab-pane active" id="product">
                                                    
                                                    <div class="details">
                                                        <h1><?php echo $promo->NombreArticulo;?></h1>
                                                        <div class="prices">
                                                            <?php
//                                                            echo "<pre>";
//                                                                 echo var_dump(number_format($precioT["precioVenta"],2,',','')==number_format($precioT["precioFinal"],2,',',''));
//                                                            echo "</pre>";
                                                                ?>
                                                            
                                                            <?php if(number_format($precioT["precioVenta"],2,',','')!=number_format($precioT["precioFinal"],2,',','')):?>
                                                            <span class="base"><strong>$<?php echo number_format($precioT["precioVenta"],2,',','.');?></strong></span>
                                                            <span class="price"><strong>$<?php echo number_format($precioT["precioFinal"],2,',','.');?></strong></span>
                                                            <?php else:?>
                                                            
                                                            <span class="price"><strong>$<?php echo number_format($precioT["precioFinal"],2,',','.');?></strong></span>
                                                            <?php endif;?>
                                                        </div>

                                                        <div class="meta">
                                                            <div class="sku">
                                                                <i class="fa fa-hashtag"></i>
                                                                <span rel="tooltip" title="SKU es <?php echo str_pad($promo->IDArt,8 , "0",STR_PAD_LEFT);?>"><?php echo str_pad($promo->IDArt,8 , "0",STR_PAD_LEFT);?></span>
                                                            </div>
                                                            <div class="categories" >
                                                                <span>
                                                                    <i class="fa fa-cube"></i>
                                                                    <a href="#" title="<?php echo $promo->NombCategoria;?>"><?php echo strtoupper($promo->NombCategoria);?> </a>
                                                                    <i class="fa fa-cube"></i>
                                                                    <a href="#" title="<?php echo $promo->NombRub;?>"><?php echo strtoupper($promo->NombRub);?> </a>
                                                                    <i class="fa fa-cubes"></i>
                                                                    <a href="#" title="<?php echo $promo->NombSubRub;?>"><?php echo $promo->NombSubRub;?> </a>
                                                                    <?php if($promo->CodigoMarca!=1):?>                                                                           
                                                                    <i class="fa fa-tags"></i>
                                                                    <a href="#" title="<?php echo $promo->Marca;?>"><?php echo $promo->Marca;?> </a><br>
                                                                    <?php endif;?>
                                                                        <?php if($usaEmbalaje=="Si"):?>
                                                                    <i class="fa fa-briefcase "></i>
                                                                    <a href="#" title="Bulto cerrado: <?php echo $bulto;?>"><?php echo $bulto;?> </a>
                                                                    <?php endif;?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php if($promo->Detalle!=""||$promo->detalle_web!=""):?>
                                                        <div class="short-description">
                                                            <p><?php echo $promo->Detalle;?></p>
                                                            <p><?php echo $promo->detalle_web;?></p>
                                                        </div>
                                                    <?php endif;?>
                                                    <?php if($textoPromo!=""):?>
                                                            <div class="short-description">
                                                                <?php echo $textoPromo;?>
                                                            </div>
                                                    <?php else:?>
                                                        <?php if($precioT["descuento"]!==0 && $precioT["descuento"]!==null):?>
                                                        <div class="short-description verde">
                                                            <p><i class='fa fa-gift fa-lg'></i> <?php echo number_format($precioT["descuento"],0); ?>% de OFF</p>
                                                        </div>
                                                        <?php endif;?>    
                                                    <?php endif;?>                                                                                                           					
                                                </div>
                                            <?php if($codCliente):?>    
                                            <div class="add-to-cart">
                                                <button class='botonNuevo grande azul' >
                                                    <a href="<?php echo $srcArticulo;?>">
                                                        <i class="fa fa-plus fa-lg"></i> &nbsp; Comprar
                                                    </a>
                                                </button>
                                            </div>
                                             <?php endif;?>   
                                            </div>
                                        </div>        
                                    </div>
                                </div>    
                        </div>
                    </div>
            </section>
             <?php endforeach; ?>
          <?php endif;?>
        </div>

 <?php require_once 'footer.php';?>
    
    </div>
    <script>
//        $(document).ready(function(){
//            $('.thumbs-list').simpleGal({
//            mainImage: '#papito'
//            });
//        });
        </script>
    </body>
</html>