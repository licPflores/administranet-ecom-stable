<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'sesion.inc.php';

function htmlUnidadDisplayBulto($objArticulo){
    $arrArticulo = (Array) $objArticulo;
    $idArt= $arrArticulo['IDArt'];
    $tipoUnidad=$arrArticulo['tipoPrecioUnidad']; // como viene el precio descuento
    
    $cantidadUnidadDisplay = 1; // cuantas unidaees minimas hay en un display
    $cantidadDisplayBulto = 1; // cuantos display hay es una caja o bulto 
    $cantidadUnidadMinimaCaja = 1;
    $cantidadMinimaFinal=1;
    // validando
    // display
    if ($arrArticulo['cantidad_unidad_display'] != 0 && $arrArticulo['cantidad_unidad_display'] != null) {
        $cantidadUnidadDisplay = (int)$arrArticulo['cantidad_unidad_display'];
    }

    // bulto
    if ($arrArticulo['cantidad_display_bulto'] != 0 && $arrArticulo['cantidad_display_bulto'] != null) {
        $cantidadDisplayBulto = $arrArticulo['cantidad_display_bulto'];
    }

    $cantidadUnidadMinimaCaja = $cantidadUnidadDisplay * $cantidadDisplayBulto; // cuantas unidades minimas hay en una caja.
    //$cantidadMinimaFinal = $cantidadUnidadDisplay * $cantidadDisplayBulto;
   
    $html="";
    $html .='<div id="divUnidadDisplayBultoPopUp">';
    
    //*  unidad
    // precios en unidad
    if($tipoUnidad=='Unidad'){
        $html .='       <input type="radio" name="tipoUnidad'.$idArt.'" ';
        $html .='               id="tipoUnidadUnidad'.$idArt.'" ';
        $html .='               value="Unidad" ';
        $html .='               checked="checked"';            
        $html .='               >';
        $html .='       <label class="cantidad-unidad elegida" for="tipoUnidadUnidad'.$idArt.'">Unidad <strong>x1</strong></label>';
       

    }

    if($tipoUnidad!='Unidad'){
        $html .='       <input type="radio" name="tipoUnidad'.$idArt.'" ';
        $html .='               id="tipoUnidadUnidad'.$idArt.'" ';
        $html .='               value="Unidad" ';                      
        $html .='               >';
        $html .='       <label class="cantidad-unidad" for="tipoUnidadUnidad'.$idArt.'"> Unidad <strong>x1</strong></label>';
    }

    //* display

    if($tipoUnidad=='Display'){
        $html .='       <input type="radio" name="tipoUnidad'.$idArt.'"';
        $html .='               id="tipoUnidadDisplay'.$idArt.'" ';
        $html .='               checked="checked"';            
        $html .='               value="Display" '; 
        $html .='               >';
        $html .='       <label class="cantidad-display elegida" for="tipoUnidadDisplay'.$idArt.'">Display <strong>x'.round($cantidadUnidadDisplay,0).'</strong></label>';
        // $html .='       <label class="cantidad-display elegida" for="tipoUnidadDisplay'.$idArt.'"><i class="fa-solid fa-check"></i> Display (x'.round($cantidadUnidadDisplay,0).')</label>';


    }

    if($tipoUnidad!='Display'){
        $html .='       <input type="radio" name="tipoUnidad'.$idArt.'"';
        $html .='               id="tipoUnidadDisplay'.$idArt.'" ';            
        $html .='               value="Display" '; 
        $html .='               >';
        $html .='       <label class="cantidad-display" for="tipoUnidadDisplay'.$idArt.'">Display <strong>x'.round($cantidadUnidadDisplay,0).'</strong></label>';


    }
    
    //* bulto
    if($tipoUnidad=='Bulto'){
        $html .='       <input type="radio" name="tipoUnidad'.$idArt.'" ';
        $html .='               id="tipoUnidadBulto'.$idArt.'" ';
        $html .='               checked="checked"';                       
        $html .='              value="Bulto" ';
        $html .='              >';
        $html .='       <label class=" cantidad-bulto elegida"  for="tipoUnidadBulto'.$idArt.'">Bulto <strong>x'.round($cantidadUnidadMinimaCaja,0).'</strong></label>';
        // $html .='       <label class=" cantidad-bulto elegida"  for="tipoUnidadBulto'.$idArt.'"><i class="fa-solid fa-check"></i> Bulto (x'.round($cantidadUnidadMinimaCaja,0).')</label>';

        
    }

    if($tipoUnidad!='Bulto'){
        $html .='       <input type="radio" name="tipoUnidad'.$idArt.'" ';
        $html .='               id="tipoUnidadBulto'.$idArt.'" ';                                 
        $html .='              value="Bulto" ';
        $html .='              >';
        $html .='       <label class=" cantidad-bulto"  for="tipoUnidadBulto'.$idArt.'">Bulto <strong>x'.round($cantidadUnidadMinimaCaja,0).'</strong></label>';
    }

    $html .='</div>'.PHP_EOL;
    return $html;
}



$tipoUsuario = $_SESSION["tipousuario"];
if (isset($_GET['idArticulo'])) {

    $idArticulo = mysqli_real_escape_string($connV, $_GET['idArticulo']);
    $jsonConDatos = json_decode($_GET['jsonProducto']);
    // echo '<pre>',print_r($jsonConDatos),'</pre>';
    if ($tipoUsuario == "vendedor") {
        $vendedor = $_SESSION['vendedor'];
    }
    $idDeposito = $_SESSION["deposito"];
    // usa lote
    if (isset($_GET['idLote']) && $_GET['idLote'] != "") {
        $idLote = mysqli_real_escape_string($connV, $_GET['idLote']);
    } else {
        $idLote = null;
    }
   // embalaje
    if ($_SESSION["utilizaEmbalaje"] == "Si") {
        $usaEmbalaje = "Si";
    } else {
        $usaEmbalaje = "No";
    }

    
    
    // bulto promedio
    $usoBultoPromedio = $_SESSION["uso_bulto_promedio"];

    // muestra stock
    $muestraStock = $_SESSION["verStock"];

    // display unidad bulto
    $usaDisplay =  $_SESSION['utiliza_display'] ;
    $usaBultoCerrado = $_SESSION['utiliza_bulto_cerrado'];
    

    // $muestraStock ="no";
    // Is the string length greater than 0?

    if (strlen($idArticulo) > 0) {
        
        if (is_object($_SESSION['cliente'])) {
            $objCliente = $_SESSION['cliente'];
        } else {
            $objCliente = $_SESSION['cliente'][0];
        }
        // While there are results loop through them - fetching an Object (i like PHP5 btw!).
        date_default_timezone_set('America/Argentina/Buenos_Aires');

        // Verifica la codificación
        setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain', 'es_AR.UTF-8', 'es_AR');
        $textoStock = "";
        $result = $jsonConDatos;
        // while ($result = mysqli_fetch_object($hacer)) {

        /**
         * LOTE
         * =================================================
         */
        $textoLote  = "";
        if ($result->lote == 'Si') {

            $textoLote  = ' <div class="titulo-segundo">Lote</div>'
                . '<div class="detalle">';
            /* codigo de lote anterior*/
            if ($idLote != null) {
                $loteCons = "AND lote_stock.id_lote = {$idLote}";
            } else {
                //                                    $loteCons ="";
                $loteCons = "AND lote_stock.id_deposito = {$idDeposito} ";
            }
            /* codigo de lote*/
            //                                if($idLote!=null){
            //                                    $loteCons = "AND lote_stock.id_lote = {$idLote}";
            //                                }else{
            //                                    $loteCons ="AND lote_stock.id_deposito = {$idDeposito} ";
            //                                }

            $sqlLote = "SELECT lote.id_lote,
                                                   lote.cod_lote,
                                                   DATE_FORMAT(lote.fecha_vto_lote,'%d/%m/%Y') AS fecha_vto_lote,
                                                   lote.stock_total_lote,
                                                   lote_stock.stock_lote 
                                            FROM lote
                                            INNER JOIN lote_stock ON (lote.id_lote = lote_stock.id_lote) 
                                            WHERE lote.id_articulo = {$idArticulo} 
                                            AND lote.anulado ='No' 
                                            AND lote_stock.stock_lote > 0 
                                            {$loteCons}
                                            ORDER BY lote.fecha_vto_lote ASC";

            $hacerLote = mysqli_query($connV, $sqlLote) or die('No puedo recuperar el lote' . mysqli_error($connV) . $sqlLote);
            //$lotes  = array();
            //$textoLote = "<ul>";
            //                                echo "<pre>";
            //                                print_r($sqlLote);
            //                                echo "</pre>";

            while ($lotes =  mysqli_fetch_object($hacerLote)) {

                $textoLote .= '<div class="renglon">'
                                . '<span class="opcion">Id: <strong>' . $lotes->id_lote . '</strong></span> '
                                . '<span class="opcion">Nº Lote: <strong>' . $lotes->cod_lote . '</strong></span>'
                                . '<span class="opcion">Vto: <strong>' . $lotes->fecha_vto_lote . '</strong> </span>'
                                . '<span class="opcion">Stock : <strong>' . $lotes->stock_lote . '</strong> de ' . $lotes->stock_total_lote . '</span>'
                                //.'<span class="opcion">Stock Lote: <strong>'.$lotes->stock_total_lote.'</strong></span>'
                            . '</div>';
            }
            //$textoLote .='</ul>';
            $textoLote .= '</div>';
        }

        // voy a buscar y colocar bien armadito el tema
        // hago un join con la tabla deposito_reposicion, para
        // poder traer por deposito los limites y si no hay no muestro nada
        // y luego con esos datos armar los niveles de stock.
        $sqlStock = "SELECT
                                            stock_deposito.id_articulo,
                                            stock_deposito.id_deposito,
                                            stock_deposito.saldo,
                                            (stock_deposito.saldo - stock_deposito.saldo_pedido_cliente) AS disponible,
                                            deposito.NombreDeposito,
                                            
                                            deposito_reposicion.stock_minimo,
                                            deposito_reposicion.stock_maximo,
                                            deposito_reposicion.punto_pedido 
                                        FROM stock_deposito
                                        LEFT JOIN deposito_reposicion ON (stock_deposito.id_articulo = deposito_reposicion.id_articulo)
                                            AND(stock_deposito.id_deposito = deposito_reposicion.id_deposito)
                                        LEFT JOIN deposito ON deposito.`CodDeposito` = `stock_deposito`.`id_deposito`                                        
                                        WHERE
                                        stock_deposito.id_deposito=" . $idDeposito . "
                                        AND     
                                        stock_deposito.id_articulo=" . $result->IDArt;

        $hacerStock = mysqli_query($connV, $sqlStock) or die('no puedo recuperar el stock deposito');

        if ($hacerStock) {
            while ($stockDeposito = mysqli_fetch_object($hacerStock)) {
                //echo var_dump(number_format($stockDeposito->stock_minimo));
                //echo var_dump(number_format($stockDeposito->stock_minimo) >= number_format($stockDeposito->saldo));
                                                //   echo  '<pre>'.print_r($stockDeposito).'</pre>';
                /*
                * BULTO PROMEDIO X DEPOSITO.
                * =========================================
                */
                $sinStock = '<strong class="destacado">sin stock</strong> '.PHP_EOL;
                if (!$stockDeposito->stock_minimo || !$stockDeposito->stock_maximo) {
                    $minimo = 0;
                    $maximo = 0;
                    
                }

                if ($result->cantidad_promedio_bulto <> 0 && $result->tipo_unidad == "Peso") {
                    $stockF = number_format(($stockDeposito->saldo / $result->cantidad_promedio_bulto), 2, ',', '');
                    $disponibleF = number_format(($stockDeposito->disponible / $result->cantidad_promedio_bulto), 2, ',', '');                    
                    $minimoF = number_format($stockDeposito->stock_minimo / $result->cantidad_promedio_bulto);
                    $maximoF = number_format($stockDeposito->stock_maximo / $result->cantidad_promedio_bulto);

                    $stock = ($stockDeposito->saldo / $result->cantidad_promedio_bulto);
                    $disponible = ($stockDeposito->disponible / $result->cantidad_promedio_bulto);                    
                    $minimo = ($stockDeposito->stock_minimo / $result->cantidad_promedio_bulto);
                    $maximo = ($stockDeposito->stock_maximo / $result->cantidad_promedio_bulto);
                    
                } else {
                    $stockF = number_format($stockDeposito->saldo, 0);
                    $disponibleF = number_format($stockDeposito->disponible, 0);                    
                    $minimoF = number_format($stockDeposito->stock_minimo);
                    $maximoF = number_format($stockDeposito->stock_maximo);
                   
                   $minimo = $stockDeposito->stock_minimo;
                   $maximo = $stockDeposito->stock_maximo;
                   $stock  = $stockDeposito->saldo;
                   $disponible = $stockDeposito->disponible;
                   
                }
                $cuanto=$stockF;
                if($stock<0){
                    $cuanto = $sinStock;
                }
                // $textoStock .='<li>'.PHP_EOL;
                // stock minimo o maximo
                if (!$stockDeposito->stock_minimo || !$stockDeposito->stock_maximo) {
                    //                                        no hay datos de stock...veremoq que metemos..
                    $textoStock .= '<span class="opcion">Depósito: <strong> ' . $stockDeposito->NombreDeposito;
                    $textoStock .= '</strong></span>'.PHP_EOL;  
                    $textoStock .= '<span class="opcion">Stock: <strong class="destacado">' . $cuanto . '</strong></span>'.PHP_EOL;
                    $textoStock .= '<span class="opcion">Disponible: <strong class="destacado">' . $disponibleF . '</strong></span>'.PHP_EOL;                        
                }  

                if ($stockDeposito->stock_minimo!=null || $stockDeposito->stock_maximo!=null) {
                    //                                        aca voy a hacer el calculo de los stocks! si es alto medio o bajo , ya tengo los datitos

                    //                                        $minimo = number_format($stockDeposito->stock_minimo);
                    //                                        $maximo = number_format($stockDeposito->stock_maximo);
                    //                                        $stock  = number_format($stockDeposito->saldo);
                    //                                        $disponible= number_format($stockDeposito->disponible);
                    //                                        $cuanto = '<strong class="destacado">sin stock</strong> ';
                        $textoStock .= '<span class="opcion">Depósito: <strong>'. $stockDeposito->NombreDeposito . '</strong></span> '.PHP_EOL;
                        $textoStock .=   '<span class="opcion">Stock: <strong class="destacado">' . $cuanto. '</strong></span>'.PHP_EOL;
                        // echo var_dump($minimo <= $stock && $stock <= $maximo);
                        // echo 'evaluando datos:',$minimo,$stock,$maximo,PHP_EOL;
                        if ($minimo >= $stock) {
                            
                            $textoStock .=   '<span class="opcion">Disponible: <strong class="destacado">' . $disponibleF. '</strong></span>'.PHP_EOL;
                            $textoStock .=   '<span class="opcion"> <strong class="bajo">Bajo</strong></span>'.PHP_EOL;
                        } 
                        
                        if (($minimo <= $stock) && ($stock <= $maximo)) {
                                // $textoStock .=   '<span class="opcion">Stock: <strong class="destacado">' . $cuanto. '</strong></span>'.PHP_EOL;
                                $textoStock .=   '<span class="opcion">Disponible: <strong class="destacado">' . $disponibleF. '</strong></span>'.PHP_EOL;
                                $textoStock .=   '<span class="opcion"> <strong class="medio">Medio</strong></span>'.PHP_EOL;
                            
                        }

                        if ($stock > $maximo) {
                            // $textoStock .=   '<span class="opcion">Stock: <strong class="destacado">' . $cuanto. '</strong></span>'.PHP_EOL;
                            $textoStock .=   '<span class="opcion">Disponible: <strong class="destacado">' . $disponible. '</strong></span>'.PHP_EOL;
                            $textoStock .=   '<span class="opcion"> <strong class="alto">Alto</strong></span>'.PHP_EOL;
                        }
                }
                // $textoStock .='</li>'.PHP_EOL;

            }
        }

        // * Imagen
        $textoImagen = '';
        $fotoPrimero = '';
        $thumbNail = '';

        $textoImagen .='<span class="product-images">'.PHP_EOL;
        $fotoPrimero = '<img title="' . $result->NombreArticulo . '" src="foto.php?origen=foto1|' . $result->IDArt . '&mini=1" >';
        // $textoImagen .= $fotoPrimero. $thumbNail . '</div>';
        $textoImagen .= $fotoPrimero;
        $textoImagen .= '</span>'.PHP_EOL;



        /* PROMOCIONES  */
        /***************************************************/
        $textoPromo = "";
        //    echo "<pre>";
        //        print_r($result);
        //        echo "</pre>";
        if ($result->promocion == "Si") {
            //analizar si se aplica por las fechas.
            if ($result->promocion_vigencia_desde != null || $result->promocion_vigencia_hasta != null) {
                $fd = explode('-', $result->promocion_vigencia_desde);
                $fh = explode('-', $result->promocion_vigencia_hasta);
                $desde = mktime(0, 0, 0, $fd[1], $fd[2], $fd[0]);
                $hasta = mktime(0, 0, 0, $fh[1], $fh[2], $fh[0]);
                $hoyD = getdate();
                $hoy = mktime(0, 0, 0, $hoyD['mon'], $hoyD['mday'], $hoyD['year']);
                //                                    echo "<pre>";
                //                                    var_dump( $hasta);
                //                                    echo "resultado";
                //                                    var_dump($hoy >= $desde && ($hoy <= $hasta||$hasta==null));
                //                                    echo "</pre>";
                if ($hoy >= $desde && ($hoy <= $hasta || $hasta == null)) {
                    /* entra dentro de intervalo*/
                    $textoPromo = '<div class="titulo-segundo">
                                            Promoción
                                            </div>
                                            <div class="detalle">';
                    // preguntar por el tipo de promocion 
                                                        //  echo $result->promocion_vigencia_desde.PHP_EOL;
                                                        //   echo $result->promocion_vigencia_hasta.PHP_EOL;
                    
                    $fecha_unix_desde = strtotime($result->promocion_vigencia_desde);

                    // Formatear la fecha en castellano
                    
                    $desdeT = strftime('%A %d de %B del %Y', $fecha_unix_desde);

                    $fecha_unix_hasta = strtotime($result->promocion_vigencia_hasta);

                    // Formatear la fecha en castellano
                    
                    $hastaT =strftime('%A %d de %B del %Y', $fecha_unix_hasta);



                    // $desdeT = $result->fdesdeT;
                    // $hastaT = $result->fhastaT;
                    //setlocale(LC_TIME, $localidad); // Vuelve a la localidad regional anterior  C
                    $vigencia = '<span class="opcion">Vigencia: Promoción válida del ' . $desdeT . ' al ' .  $hastaT . '</span>'.PHP_EOL;
                    switch ($result->promocion_tipo) {
                        case "Cantidad":
                            //porcentaje de descuento por cantidad
                            $textoPromo .= '<span class="opcion"><strong class="descuento">' . number_format($result->promocion_por, 0) . '% OFF</strong></span>'.PHP_EOL;
                            $textoPromo .= '<span class="opcion">Por la compra de <strong>' . number_format($result->promocion_cant, 0) . '</strong> o más unidades.</span>: '.PHP_EOL;

                            break;
                        case "Cantidad - Unidad":
                            $textoPromo .= '<span class="opcion">Por la compra de <strong>' . number_format($result->promocion_cant, 0) . '</strong> unidades, ' . number_format($result->promocion_por, 2) . ' de regalo.</span>'.PHP_EOL;
                            //$textoPromo .= "cada " . number_format($result->promocion_cant,null) ." . Vigencia: ";
                            // unidades gratis x cantidad
                            break;
                        case "Importe descuento":
                            $textoPromo .= '<span class="opcion"><strong class="descuento">' . number_format($result->promocion_por, 0) . '% OFF</strong></span>'.PHP_EOL;
                            $textoPromo .= '<span class="opcion">En todas las unidades.</span>';
                            // porcentaje de descuento sin importar cantidad
                            break;
                        case "Cantidad - Intervalo":
                            $arrPromoIntervalo = array();

                            $sqlPi = "SELECT "
                                . "ai.desde_cantidad,ai.hasta_cantidad,ai.monto_descuento "
                                . "FROM articulo_promo_intervalo AS ai "
                                . "WHERE ai.id_articulo=" . $result->IDArt . " "
                                . "AND ai.anulado='No'";

                            $hacerAi = mysqli_query($connV, $sqlPi) or die("No puedo recuperar las promociones por intervalo <pre>" . $sqlPi . "</pre>" . mysqli_error($connV));
                            while ($ai =  mysqli_fetch_assoc($hacerAi)) {
                                $textoPromo .= '<span class="opcion"><strong class="descuento">' . number_format($ai["monto_descuento"], 0) . '% OFF</strong> por la compra de <strong>' . number_format($ai["desde_cantidad"], 1) . '</strong> o más unidades.</span><br>'.PHP_EOL;
                            }
                            break;
                    }
                    $textoPromo .= $vigencia;
                }
            } else {
                // promocion eterna
                $textoPromo = '<div class="titulo-segundo">
                                             Promoción
                                            </div>
                                            <div class="detalle">';
                //preguntar por el tipo de promocion 
                switch ($result->promocion_tipo) {
                    case "Cantidad":
                        //porcentaje de descuento por cantidad
                        $textoPromo .= '<span class="opcion"><strong class="descuento">' . number_format($result->promocion_por, 0) . '% OFF</strong> de descuento</span>'.PHP_EOL;
                        $textoPromo .= '<span class="opcion">por la compra de <strong>' . number_format($result->promocion_cant, 0) . '</strong> o más unidades.</span>'.PHP_EOL;

                        break;
                    case "Cantidad - Unidad":
                        $textoPromo .= '<span class="opcion">Por la compra de <strong>' . number_format($result->promocion_cant, 0) . '</strong> unidades, ' . number_format($result->promocion_por, 2) . ' de regalo.</span>:'.PHP_EOL;
                        //$textoPromo .= "cada " . number_format($result->promocion_cant,null) ." . Vigencia: ";
                        // unidades gratis x cantidad
                        break;
                    case "Importe descuento":
                        $textoPromo .= '<span class="opcion"><strong class="descuento">' . number_format($result->promocion_por, 0) . '% OFF</strong> de descuento, </span>'.PHP_EOL;
                        $textoPromo .= '<span class="opcion">en todas las unidades.</span>'.PHP_EOL;
                        // porcentaje de descuento sin importar cantidad
                        break;
                    case "Cantidad - Intervalo":
                        $arrPromoIntervalo = array();

                        $sqlPi = "SELECT "
                            . "ai.desde_cantidad,ai.hasta_cantidad,ai.monto_descuento "
                            . "FROM articulo_promo_intervalo AS ai "
                            . "WHERE ai.id_articulo=" . $result->IDArt . " "
                            . "AND ai.anulado='No'";

                        $hacerAi = mysqli_query($connV, $sqlPi) or die("No puedo recuperar las promociones por intervalo <pre>" . $sqlPi . "</pre>" . mysqli_error($connV));
                        while ($ai =  mysqli_fetch_assoc($hacerAi)) {
                            $textoPromo .= '<span class="opcion"><strong class="descuento">' . number_format($ai["desde_cantidad"], 0) . '% OFF</strong> de descuento por la compra de <strong>' . number_format($ai["monto_descuento"], 0) . '</strong> o más unidades.</span>'.PHP_EOL;
                        }
                        break;
                }
            }
        } // fin si hay promocion.

        $bulto = "";
        if ($usaEmbalaje == "Si") {
            // tengo que hacer la busqueda de los valores para mostrar
            $bulto = $result->nombre_presentacion . " x " . number_format($result->cantidad_uni, 0);
            if ($result->nombre_unimed != "") {
                $bulto .= " (" . $result->nombre_unimed . ")";
            }
        }
        $textoArt = '<div class="product-info">
                        <div class="titulo-primero">
                            <div class="texto">' . $result->NombreArticulo . '</div>
                            <div class="cerrar">
                            <button class="botonAccionPrimario" id="cerrarModalD" >Cerrar</button>
                            </div>       
                        </div>'.PHP_EOL;
        $textoArt .= '<div class="detalle-datos">'.PHP_EOL;
        $textoArt .= $textoImagen;

        $textoArt .='<div class="listaopciones">'.PHP_EOL;
        $textoArt .= '<span class="opcion">Código: <strong>' . $result->IDArt . '</strong></span>'.PHP_EOL;
        if ($result->id_manual != "") {
            $textoArt .= '<span class="opcion">Cod. Manual: <strong>' . $result->id_manual . '</strong></span>'.PHP_EOL;
        }

        $textoArt .= '<span class="opcion">Categoría:	<strong>' . $result->NombreCategoria . '</strong></span>'.PHP_EOL;
        $textoArt .=  '<span class="opcion">Rubro:	<strong>' . $result->NombRub . '</strong></span>'.PHP_EOL;
        
        $textoArt .= '<span class="opcion">SubRubro:	<strong>' . $result->NombSubRub . '</strong></span>'.PHP_EOL;
        if ($result->Marca != "-Ninguno-") {
            $textoArt .= '<span class="opcion">Marca:	<strong>' . $result->Marca . '</strong></span>'.PHP_EOL;
        }
        if ($result->Modelo != "-Ninguno-") {
            $textoArt .= '<span class="opcion">Modelo:	<strong>' . $result->Modelo . '</strong></span>'.PHP_EOL;
        }
        if ($result->Laboratorio != "-Ninguno-") {
            $textoArt .= '<span class="opcion">Laboratorio:	<strong>' . $result->Laboratorio . '</strong></span>'.PHP_EOL;
        }
        $textoArt .= '<span class="opcion">Bulto cerrado:	<strong>' . $bulto . '</strong></span>'.PHP_EOL;
        /* USA BULTO PROMEDIO */
        if ($usoBultoPromedio == "Si") {
            $textoArt .= '<span class="opcion">Pres:	<strong>(' . round($result->cantidad_promedio_bulto,2) . " " . $result->uniArt . ')</strong></span>'.PHP_EOL;
        }

       
        $textoArt .='</div>'.PHP_EOL;// fin div que contiene las opciones laterlaes
        
         // * Presentaciones Unidad Display Bulto
        // vlidar si tengo permiso display bulto y display bulto promeiod
        if($usaDisplay=='Si' && $usaBultoCerrado=='Si'){
            $textoArt .='<div class="titulo-segundo">Presentación</div>'.PHP_EOL;
            $textoArt .='<span class="opcion">'.PHP_EOL;
            $textoArt .=htmlUnidadDisplayBulto($result);
            $textoArt .='</span>'.PHP_EOL;

        }
        $textoArt .= '</div>'.PHP_EOL; // fin div que contiene la info del producto
        
        
        //$textoArt .= $textoPromo; 
        if ($muestraStock == "Si") {
            $textoArt .= '   <div class="titulo-segundo">Stock</div>'.PHP_EOL;
            $textoArt .= '   <div class="detalle">' . $textoStock . '</div>'.PHP_EOL;
            $textoArt .=  $textoLote;
        }

        if ($result->promocion == 'Si') {


            switch ($objCliente->listaPrecio) {
                case 'Lista 1':

                    if ($result->promocion_lista1 == "Si") {
                        $textoArt .= $textoPromo;
                    }
                    break;
                case 'Lista 2':

                    if ($result->promocion_lista2 == "Si") {
                        $textoArt .= $textoPromo;
                    }
                    break;
                case 'Lista 3':

                    if ($result->promocion_lista3 == "Si") {
                        $textoArt .= $textoPromo;
                    }
                    break;
                case 'Lista 4':

                    if ($result->promocion_lista4 == "Si") {
                        $textoArt .= $textoPromo;
                    }
                    break;
                case 'Lista 5':

                    if ($result->promocion_lista5 == "Si") {
                        $textoArt .= $textoPromo;
                    }
                    break;
                case 'Lista Oficial':
                    $textoArt .= $textoPromo;

                    break;
            }
           
            
            $textoArt .= '</div>'.PHP_EOL;
        }
        $textoArt .= '</div>'.PHP_EOL;



        // $textoArt = '<div class="container">' . $textoArt . $textoImagen . '</div>';
        $textoArt = '<div class="container">' . $textoArt .'</div>'.PHP_EOL;


       
        echo $textoArt;


        // } else {
        //         echo 'ERROR: There was a problem with the query.';
        // }
    } else {
        // Dont do anything.
    } // There is a queryString.
} else {
    echo 'There should be no direct access to this script!';
}
