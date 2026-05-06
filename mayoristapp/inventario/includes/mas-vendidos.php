<?php

//set_time_limit(60); 
// detalle del articulo.

/**
 * # consultar stock disponible real
 * recupera el stock disponible calculado
 */
function buscar_saldo_pendiente_unico($idArt, $producto)
{
    $multiplicadorEmbalaje = 1;
    $idDeposito = $_SESSION['deposito'];
    //$arrProductos=$this->colArticulos; // productos ya asignados al objeto
    $saldoPendiente = $producto->saldo;
    $sqlSaldosPedidos = "SELECT 
                            stockp.IDArt,       			
                        SUM(stockp.salida)  AS saldo_pedido_cliente 
                        FROM stockp
                        LEFT JOIN comp_ped ON (stockp.CodigoMovimiento = comp_ped.CodigoMovimiento) 
                        WHERE 
                        stockp.IDArt =" . $idArt . "
                        AND  comp_ped.TipoComprobante = 'PED' 
                        AND stockp.CodDeposito=" . $idDeposito . "

                        AND (comp_ped.Estado = 'Pendiente')
                        #AND (comp_ped.Estado = 'Pendiente' OR comp_ped.Estado='En proceso pago') 
                        AND comp_ped.Anulado = 'No'
                        
                        GROUP BY stockp.IDArt";
    // la conexion debe ser a la base real.                    
    $connBaseCarrito = mysqli_connect(carrito_servidor_db, carrito_usuario_db, carrito_pass_db, carrito_base_de_datos, carrito_puerto_db);
    if ($connBaseCarrito) {
        $hacerPendiente = mysqli_query($connBaseCarrito, $sqlSaldosPedidos) or die('No puedo recueprar pendientes ' . mysqli_error($connBaseCarrito) . ' SQL:' . $sqlSaldosPedidos . PHP_EOL);
        while ($pendiente = mysqli_fetch_assoc($hacerPendiente)) {
            $idProducto = $pendiente['IDArt'];

            $saldo = $producto->saldo;

            //echo "SAldo::<pre>";
            // print_r($producto->saldo);
            //echo "<br>Pendiente::";
            //print_r($pendiente['saldo_pedido_cliente']);
            //echo "</pre>";
            if (key_exists('saldo_pedido_cliente', $pendiente) && $pendiente['saldo_pedido_cliente'] != null) {
                if ($_SESSION['utilizaEmbalaje'] == 'Si') {
                    $multiplicadorEmbalaje = 1;
                    if ($producto['multiplicador_comp'] != null) {
                        $multiplicadorEmbalaje = $producto['multiplicador_comp'];
                    }
                    $saldoPendiente = $saldo - ($pendiente['saldo_pedido_cliente'] / $multiplicadorEmbalaje);
                }

                if ($_SESSION['utilizaEmbalaje'] != 'Si') {

                    $saldoPendiente = $saldo - $pendiente['saldo_pedido_cliente'];
                }

                if ($saldoPendiente < 0) {
                    // resultado negativo seteo en CERO
                    $saldoPendiente = 0;
                }
            }
        }
    }
    return $saldoPendiente;
}

/**
 * # busqueda SQL datos del articulo a mostrar
 * luego analiza si con precio o no.
 */
function mostrar_articulo_detalle($link)
{
    $idArt = $_GET["IDArt"];
    $where = " articulo.IdArt={$idArt}";
    // if(defined(CODIGOPRODUCTO)&&CODIGOPRODUCTO=='manual'){
    //     $where = " articulo.id_manual='{$idArt}'";
    // }

    //    echo "dentro";
    $campoReglaPrecio = "";
    $sqlReglaPrecio = "";
    $usoRegla = "No";

    # analisis reglas de precio
    if (isset($_SESSION["cliente"])) {
        //            echo "<br>cliente___::".print_r($_SESSION["cliente"]);
        $codCliente = $_SESSION["cliente"];
        $usoRegla = $_SESSION["usaReglaPrecio"];
    }
    if ($usoRegla == "Si" && $codCliente != null) {
        $campoReglaPrecio = "rp.tipo_calculo,rp.importe_regla,rp.id_cliente AS clienteRegla,";
        $sqlReglaPrecio = "LEFT JOIN reglas_precio AS rp ON  
                        (rp.id_articulo = articulo.IDArt 
                        AND rp.id_cliente={$codCliente} 
                        AND  ('" . date('Y-m-d') . "' BETWEEN rp.vigencia_desde AND rp.vigencia_hasta) 
                        AND rp.anulado='No' )";
    }
    # deposito con stock, lo busco despues veo si lo muestro.
    $depositoWeb = $_SESSION['deposito'];

    $sqlArticulo = "SELECT 
                        articulo.id_manual,
                        marca.NombreMarca AS Marca,
                        modelo.NombreModelo AS Modelo,
                        articulo.IDArt,
                        articulo.detalle_web,
                        articulo.IDSubRubro, 
                        articulo.CodigoSubRubro,
                        articulo.CodigoProveedor, 
                        articulo.CodigoRubro,
                        articulo.CodigoMarca,
                        cat.id_categoria,
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
                        articulo.tipoIVA,
                        iva.Alicuota AS Alic,
                        DATE_FORMAT(articulo.promocion_vigencia_hasta,'%W %d de %M del %Y') as fhastaT,
                        DATE_FORMAT(articulo.promocion_vigencia_desde,'%W %d de %M del %Y') as fdesdeT,                       
                        rubro.NombreRubro AS NombRub, 
                        subrubro.NombreSubRubro AS NombSubRub,
                        cat.nombre_categoria AS NombCat,
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
                        {$campoReglaPrecio}
                        articulo.impuesto_interno,    
                        articulo_prov.multiplicador_comp,
                        articulo_prov.cantidad_uni, 
                        unidmed.descrip_corta AS nombre_unimed,                        
                        articulo_prov.id_presentacionC, 
                        articulo_prov.id_unimed,
                        articulo.cantidad_promedio_bulto,
                        mart.tipo_unidad,
                        mart.descrip_corta AS uniArt, 
                        presentacion_abmV.nombre_presentacion AS nombre_presentacion_vta,                 
                        stock_deposito.saldo,
                        stock_deposito.id_deposito,
                        articulo.ecommerce,
                        ecom.descuento_solo_web,
                        ecom.promo_solo_web,
                        ecom.vigencia_desde_solo_web,
                        ecom.vigencia_hasta_solo_web,
                        ecom.destacado_ecom,
                        ecom.detalle_ecom,
                        ecom.nombre_articulo_ecom,
                        ecom.link_articulo_ecom,
                        ecom.link_video_articulo_ecom,
                        ecom.garantia_articulo,
                        ecom.usa_nombre_articulo_ecom
                        
                        FROM articulo
                            LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt AND articulo_prov.CodProveedor = articulo.CodigoProveedor)
                            LEFT JOIN stock_deposito ON (stock_deposito.id_articulo=articulo.IDArt AND stock_deposito.id_deposito={$depositoWeb})
                            LEFT JOIN unidmed ON (unidmed.id_unimed = articulo_prov.id_unimed) 
                            LEFT JOIN unidmed AS mart ON (mart.id_unimed=articulo.id_unimed)
                            LEFT JOIN presentacion_abm AS presentacion_abmV ON (presentacion_abmV.id_presentacion = articulo.id_presentacionV)                            
                            LEFT JOIN iva  ON articulo.Alicuota = iva.id 
                            LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
                            LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                            LEFT JOIN rubro_categoria AS cat ON cat.id_categoria = rubro.id_categoria
                            LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
                            LEFT JOIN marca ON marca.CodMarca = articulo.CodigoMarca
                            LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt
                            {$sqlReglaPrecio} 
                        WHERE
                            {$where}
                        ORDER BY articulo.NombreArticulo LIMIT 1;";
    // echo "<pre>";
    // print_r($sqlArticulo);
    // echo "</pre>";
    $hacer = mysqli_query($link, $sqlArticulo) or die('No puedo recuperar los articulos en promocion' . mysqli_error($link) . $sqlArticulo);
    $objArticulos = array();
    # articulo con datos
    while ($art = mysqli_fetch_object($hacer)) {


        $objArticulos = $art;
    }

    # fotos con multi foto.
    $sqlArticuloFoto = "SELECT
                            articulo_foto.id_articulo_foto,
                            articulo_foto.foto_principal 
                            FROM articulo_foto 
                            WHERE articulo_foto.IdArt=" . $idArt;
    $hacerFoto = mysqli_query($link, $sqlArticuloFoto);
    $arrFotos = array();
    while ($ff = mysqli_fetch_assoc($hacerFoto)) {
        $arrFotos[] = $ff;
    }

    if (isset($_SESSION["muestra_precio"]) && $_SESSION["muestra_precio"] == "Si") {
        //       echo "En mas vendidos producto<br>";
        $htmlF = producto_precio($objArticulos, $link, $arrFotos);
    }

    if (isset($_SESSION["muestra_precio"]) && $_SESSION["muestra_precio"] == "No") {
        $htmlF = producto_sin_precio($objArticulos, $link, $arrFotos);
    }


    return $htmlF;
}

/*
 * function: videos
 * desc: me fijo en el array de videos cual tengo que poner de acuerdo tambien 
 * a si es movil o no 
 */





/*
 * Function mas pedidos. todos
 */

function mas_vendidos_todos($link = null, $idCategoria = null, $idRubro = null, $idSubRubro = null)
{
    $html = '';
    /* chapini dorrego */
    //$servidor="chapinidorrego.dyndns.org:30804";
    //$user="administranet";
    //$pass="a7v8xx0805";
    //$baseConecto="administranet";
    //
    //$html="";
    //if(!defined("servidor_db"))define("servidor_db",$servidor);
    //if(!defined("usuario_db"))define("usuario_db",$user);
    //if(!defined("pass_db"))define("pass_db",$pass);
    //if(!defined("base_de_datos"))define("base_de_datos",$baseConecto);
    //$link=mysqli_connect($servidor,$user,$pass,$baseConecto) or die("no me puedo conectar carrito/conexion.inc.php \n". mysqli_connect_error());
    if ($link) {
        //mysqli_select_db($connV,$baseConecto);
        mysqli_set_charset($link, 'utf8');





        $colorRubro = array(
            1 => '3px solid #00acd4;',
            2 => '3px solid #22b54b;',
            3 => '3px solid #f8931f;'
        );
        $filtro = "";
        if ($idCategoria) {
            $filtro .= " AND rubro.id_categoria=" . $idCategoria;
        }
        if ($idRubro) {
            $filtro .= " AND articulo.CodigoRubro=" . $idRubro;
        }
        if ($idSubRubro) {
            $filtro .= " AND articulo.IdSubRubro=" . $idSubRubro;
        }



        // voy a dar vueltas por cada rubro.
        //print_r($rr);
        $sqlTop = "SELECT                                 
                                rubro.id_categoria,
                                rubro_categoria.nombre_categoria,
                                rubro.NombreRubro AS NombRub,
                                subrubro.NombreSubRubro AS NombSubRub,
                                COUNT(stock.IDArt) AS Cuantos,
                                articulo.id_manual,
                                articulo.IDArt,
                                articulo.IDSubRubro, 
                                articulo.CodigoSubRubro,
                                articulo.CodigoRubro,
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
                                articulo.tipoIVA,
                                iva.Alicuota AS Alic, 
                                
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
                                (articulo.PNOficial*articulo.impuesto_interno/100) AS imp_interoOf

                                FROM stock 
                                    LEFT JOIN articulo ON (articulo.IDArt = stock.IDArt)                                                                    
                                    LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                                    LEFT JOIN rubro_categoria ON rubro_categoria.id_categoria=rubro.id_categoria
                                    LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
                                    LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
                                    LEFT JOIN marca ON marca.CodMarca = modelo.CodMarca
                                     LEFT JOIN iva  ON articulo.Alicuota = iva.id  
                                WHERE 
                                    articulo.Discontinuo='No'
                                    AND articulo.ecommerce='Si'									
                                    AND articulo.tipo_art='Articulo' 
                                    AND stock.Anulado='No'
                                    {$filtro}
                                    AND rubro.ecommerce='Si'									
                                    AND stock.TipoComp IN('Venta','Venta TPV')
                                GROUP BY stock.IDArt
                                ORDER BY  cuantos DESC ,rubro.id_categoria,rubro.CodigoRubro,subrubro.IdSubRubro ASC 
                                LIMIT 15;";
        $hacerTop = mysqli_query($link, $sqlTop) or die('No puedo recuperar los articulos mas vendidos <<' . mysqli_error($link) . ">> sql::<pre>" . $sqlTop . "</pre>");
        //            echo "<pre>";
        //            print_r($sqlTop);
        //            echo "</pre>";

        while ($artTop = mysqli_fetch_object($hacerTop)) {
            $objArticulosTop[] = $artTop;
        }


        $html = '<div class="vendidos-wrapper row">';
        $html .= '<div class="col-xs-12">';

        //devolver el html

        $html .= '<h2 class="mas-vendidos t-font-size t-margin">Más vendidos</h2>';
        $html .= '<div class="masVendidosTodos">';
        //$html .='<div class="masVendidosTodosChico">';





        foreach ($objArticulosTop as $promoT) {

            //$precioT = calculaPrecioTodos($promoT, $objCliente);      
            $srcArticulo = 'articulo-descripcion.php?IDArt=' . $promoT->IDArt . '&nombArt=' . $promoT->NombreArticulo;

            $html .= ' 
            <div class="item">
                <div class="item-wrapepr">
                  
                    <a href="' . $srcArticulo . '" title="Ver Detalle">
                      
                        <div class="imagenArt hvr-bobd">
                          <img class="primaria" src="foto.php?origen=foto1|' . $promoT->IDArt . '&mini=0"/>                          
                        </div>
                        <div class="tituloArtLista"> 
                      
                            <div class="nombreArticulo" style="border-top:' . $colorRubro[$promoT->id_categoria] . '"><h4>' . $promoT->NombreArticulo . '</h4></div>';
            $html .= ' <div class="tituloRubro" >'
                . $promoT->nombre_categoria . ' > '
                . $promoT->NombRub . ' > '
                . $promoT->NombSubRub . '</div>';

            if ($promoT->promocion == 'Si') :
                $html .= '<div class="promo-esquina-grilla"><div class="lazo">  <span><i class="fa fa-gift fa-lg"></i> Promoción  </span></div></div>';
            endif;

            $html .= '</div>';

            $html .= '</a>';
            $html .= '</div>';
            $html .= '</div>';
        }


        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        mysqli_close($link);
    }
    return $html;
}

function calculaPrecios($connV, $arti = null, $listaPrecioCliente = null, $descRenglon = null, $usaReglaPrecio = null, $codCliente = null)
{
    $nombreArticulo = "";
    $haySoloWeb = 'no';
    $hayPromocionSoloWeb = 'No';
    $cantidad = 1;
    switch ($listaPrecioCliente) {
        case 'Lista 1':
            $precioNeto = $arti->Precio1V;
            $importeIva = $arti->impIva1;
            $importeInterno = $arti->imp_interno1;
            $precioVenta = $arti->Precio1VI;
            if ($arti->promocion_lista1 == "Si") {
                $promoLista = "si";
            } else {
                $promoLista = "no";
            }
            break;
        case 'Lista 2':
            $precioNeto = $arti->Precio2V;
            $importeIva = $arti->impIva2;
            $importeInterno = $arti->imp_interno2;
            $precioVenta = $arti->Precio2VI;
            if ($arti->promocion_lista2 == "Si") {
                $promoLista = "si";
            } else {
                $promoLista = "no";
            }
            break;
        case 'Lista 3':
            $precioNeto = $arti->Precio3V;
            $importeIva = $arti->impIva3;
            $importeInterno = $arti->imp_interno3;
            $precioVenta = $arti->Precio3VI;
            //                        echo "<pre> ".$arti->IDArt." - ".$arti->promocion_lista3."</pre>";
            if ($arti->promocion_lista3 == "Si") {
                $promoLista = "si";
            } else {
                $promoLista = "no";
            }
            break;
        case 'Lista 4':
            $precioNeto = $arti->Precio4V;
            $importeIva = $arti->impIva4;
            $importeInterno = $arti->imp_interno4;
            $precioVenta = $arti->Precio4VI;
            if ($arti->promocion_lista4 == "Si") {
                $promoLista = "si";
            } else {
                $promoLista = "no";
            }
            break;
        case 'Lista 5':
            $precioNeto = $arti->Precio5V;
            $importeIva = $arti->impIva5;
            $importeInterno = $arti->imp_interno5;
            $precioVenta = $arti->Precio5VI;
            if ($arti->promocion_lista5 == "Si") {
                $promoLista = "si";
            } else {
                $promoLista = "no";
            }
            break;
        case 'Lista Oficial':
            $precioNeto = $arti->PNOficial;
            $importeIva = $arti->impOf;
            $importeInterno = $arti->imp_internoOF;
            $precioVenta = $arti->PFOficial;
            $promoLista = "si";

            break;
    }
    /*
     * REGLAS DE PRECIO primero y si hay reglas no se hacen descuentos
     * las reglas se buscan solo si hay permiso de reglas.
     * 
     */
    // echo "articulo precio:{<pre>";
    // print_r($arti);
    // echo "}}}}</pre>";

    $precioVentaFinal = $precioVenta;
    $precioNetoCalc = $precioNeto;

    $descFinal = 0;
    $clase = "";
    $clasePrecio = "";

    $promoCant = "";
    $promoPorc = "";
    $promoTipo = $arti->promocion_tipo;
    $promo = "no";

    $aplicaPromo = "no";
    $aplicoRegla = "no";
    $cualRegla = "";
    $desc = "si";
    $usoPromocion = "Si";
    $encontreRegla = 0;


    /**
     * Descuento Solo Web - descuento absoluto.
     */

    // ecomm.descuento_solo_web,
    //ecomm.promo_solo_web,
    //ecomm.vigencia_desde_solo_web,
    //ecomm.vigencia_hasta_solo_web
    if ($arti->promo_solo_web == 'Si') {
        // ver la vigencia primero 
        $haySoloWeb = vigencia_promo($arti->vigencia_desde_solo_web, $arti->vigencia_hasta_solo_web);
        // echo "hay vigencia solo web=:".var_dump($haySoloWeb);
        if ($haySoloWeb == 'si') {
            $usaReglaPrecio = 'No';
            $usoPromocion = 'No';
            $hayPromocionSoloWeb = 'Si';
        }
    }
    /*
     * Si no hay cliente seleccionado no hace el descuento.
     */
    if ($usaReglaPrecio == "Si") {
        /* Variables de Reglas 
         * ======================
         */
        $idArtR = $arti->IDArt;
        $codigoRubroR = $arti->CodigoRubro;
        $idSubRubroR = $arti->IDSubRubro;
        $codigoProveedorR = $arti->CodigoProveedor;
        $codClienteR = $codCliente;

        /* Reglas Particulares
         * ===================
         */

        if (property_exists($arti, 'tipo_calculo') && $arti->tipo_calculo != '') {
            // regla plarticular
            //echo "particular";
            $hayRegla = "Si";
            $usoPromocion = "No";
            $encontreRegla++;
            $tipoCalculo = $arti->tipo_calculo;
            $importeRegla = $arti->importe_regla;
        }
        /* Reglas Masivas
         * ======================== 
         */
        if ($encontreRegla == 0) {
            //echo "regla MAsivas";
            // ir a buscar la funcion que recupera si hay alguna regla masiva.
            $idReglaMasiva = reglasPrecioMasivas($connV, $idArtR, $codigoProveedorR, $codigoRubroR, $idSubRubroR, $codClienteR);
            //echo "hay regla masiva=?:{<pre>";
            //echo $idArtR."},{".$codigoProveedorR."},{".$codigoRubroR."},{".$idSubRubroR."},{".$codClienteR;
            //            echo "}<Br>id regla=:{";
            //            var_dump($idReglaMasiva);
            //            echo "}</pre>";
            if ($idReglaMasiva != null) {
                // hay regla masiva

                $sqlReglaM = "SELECT * FROM reglas_precio_masivas "
                    . "WHERE id_regla_precio_masivas ={$idReglaMasiva} ";
                $hacerRM = mysqli_query($connV, $sqlReglaM) or die("No puedo recuperar la Regla masiva encontrada " . mysqli_error($connV) . "<pre>" . $sqlReglaM . "</pre>");
                $rm = mysqli_fetch_assoc($hacerRM);
                //                echo "<pre>";
                //                print_r($rm);
                //                echo "</pre>";
                $hayRegla = "Si";
                $encontreRegla++;
                $tipoCalculo = $rm["tipo_calculo"];
                $importeRegla = $rm["importe_regla"];
            }
        }
        /*
         * Reglas Generales
         * =====================================================================
         */

        if ($encontreRegla == 0) {
            //echo "reglas generales";
            // ir a buscar la funcion que recupera si hay alguna regla General.
            $idReglaGeneral = reglasPrecioGeneral($connV, $idArtR, $codigoProveedorR, $codigoRubroR, $idSubRubroR);
            if ($idReglaGeneral != null) {
                // hay regla general

                $sqlReglaG = "SELECT * FROM reglas_precio_alta_art "
                    . "WHERE id_regla_precio_alta_art = {$idReglaGeneral}";
                $hacerRG = mysqli_query($connV, $sqlReglaG) or die("No puedo recuperar la Regla general encontrada " . mysqli_error($connV) . "<pre>" . $sqlReglaG . "</pre>");
                $rg = mysqli_fetch_assoc($hacerRG);
                //                echo "regka general<pre>";
                //                print_r($rg);
                //                echo "</pre>";
                $hayRegla = "Si";
                $encontreRegla++;
                $tipoCalculo = $rg["tipo_calculo"];
                $importeRegla = $rg["importe_regla"];
                $prioridad_regla = $rg["prioridad_regla"];
            }
        }


        /** encontre alguna Regla y la tengo que usar. */
        if ($encontreRegla != 0) {
            //echo "<pre>encontre regla:".$encontreRegla."</pre>";
            $usoPromocion = "No";
            $aplicoRegla = "si";
            //            $usoPromocion
            // vemos el tipo de regla si Descuento - Marcacion o Precio Fijo
            //echo "<pre> ImpoPromo=>";
            //print_r($importeRegla);
            //echo "<BR>Tipoc=>";
            //print_r($tipoCalculo);
            //echo "</PRE>";
            switch ($tipoCalculo) {
                case "Descuento":
                    //cargo descuento
                    // analizo la prioridad de la regla del cliente

                    if (isset($prioridad_regla) && $prioridad_regla != "Desc. Cliente") {
                        // aplico el descuento de menor valor.

                        $descRenglon = $importeRegla;
                    } else {
                        // prioridad descuento de cliente
                        // aplico el descuento de menor valor.
                        if ($descRenglon > $importeRegla) {
                            $descRenglon = $importeRegla;
                        }
                    }


                    $precioNetoNuevo = $precioNeto;
                    $descRenglonCalc = ($descRenglon * $precioNeto / 100);
                    $precioNetoCalc = $precioNetoNuevo - $descRenglonCalc;
                    $importeIva = ($precioNetoNuevo * $arti->Alic) / 100;
                    $importeInterno = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                    $precioVenta = $precioNetoNuevo + $importeIva + $importeInterno;
                    $precioNeto = $precioNetoNuevo;
                    $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                    $descFinal = $descRenglon;
                    $promoCant = "";
                    //                            $promoPorc          = "";
                    $promo = "no";
                    $cantidad = 1;
                    //                    echo "<pre>";
                    //                    var_dump($descRenglon);
                    //                    var_dump($precioNetoNuevo);
                    //                    var_dump($descRenglonCalc);
                    //                    var_dump($precioNetoCalc);
                    //                    var_dump($importeIva);
                    //                    var_dump($importeInterno);
                    //                    var_dump($precioVenta);
                    //                    echo "</pre>";
                    break;
                case "Marcacion":
                    $descRenglon = $importeRegla;
                    $precioNetoNuevo = $precioNeto;
                    $descRenglonCalc = ($descRenglon * $precioNeto / 100);
                    $precioNetoCalc = $precioNetoNuevo + $descRenglonCalc;
                    $importeIva = ($precioNetoNuevo * $arti->Alic) / 100;
                    $importeInterno = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                    $precioVenta = $precioNetoNuevo + $importeIva + $importeInterno;
                    $precioNeto = $precioNetoCalc;
                    $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                    $descFinal = 0;
                    $promoCant = "";
                    //                 $promoPorc          = "";
                    $promo = "no";
                    $cantidad = 1;
                    $descFinal = 0;
                    $precioVenta = $precioVentaFinal;
                    //                    echo "<pre>";
                    //                    echo "arti{".$arti->IDArt."}<Br>";
                    //                    echo "descRenglon:=>{".var_dump($descRenglon)."}<br>----";
                    //                    echo "precioNetoNuevo:=>{".var_dump($precioNetoNuevo)."}<br>----";
                    //                    echo "descRenglonCalc:=>{".var_dump($descRenglonCalc)."}<br>---";
                    //                    echo "precioNetoCalc:=>{".var_dump($precioNetoCalc)."}<br>----";
                    //                    echo "importeIva:=>{".var_dump($importeIva)."}<br>----";
                    //                    echo "importeInterno:=>{".var_dump($importeInterno)."}<br>---";
                    //                    echo "precioVenta:=>{".var_dump($precioVenta)."}<br>----";
                    //                     echo "precioVentaFinal:=>{".var_dump($precioVentaFinal)."}+++++<br>";
                    //                    echo "</pre>";
                    // hago el aumento pero no muestro descuento
                    break;
                case "Precio Fijo":
                    $descuento = $importeRegla;
                    $precioNetoNuevo = $descuento;
                    $descRenglonCalc = ($descRenglon * $precioNeto / 100);
                    $precioNetoCalc = $precioNetoNuevo - $descRenglonCalc;
                    $importeIva = ($precioNetoNuevo * $arti->Alic) / 100;
                    $importeInterno = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                    $precioVenta = $precioNetoNuevo + $importeIva + $importeInterno;
                    $precioNeto = $precioNetoNuevo;
                    $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                    $descFinal = $descRenglon;
                    $promoCant = "";
                    //                            $promoPorc          = "";
                    $promo = "no";
                    $cantidad = 1;
                    $descFinal = 0;
                    //reemplazo el neto x este nuevo y cero descuento
                    break;

                case "Cantidad - Unidad":

                    $descRenglon = 0;
                    $precioNetoNuevo = $precioNeto;
                    $descRenglonCalc = ($descRenglon * $precioNeto / 100);
                    $precioNetoCalc = $precioNetoNuevo - $descRenglonCalc;
                    $importeIva = ($precioNetoNuevo * $arti->Alic) / 100;
                    $importeInterno = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                    $precioVenta = $precioNetoNuevo + $importeIva + $importeInterno;
                    $precioNeto = $precioNetoNuevo;
                    $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));


                    $sqlPCant = "SELECT rp.promocion_por, rp.promocion_cant "
                        . "FROM reglas_precio AS rp"
                        . "WHERE rp.id_articulo ={$idArtR}  AND "
                        . "rp.tipo_calculo = 'Cantidad - Unidad' AND "
                        . "rp.id_cliente ={$codClienteR} ";

                    $hacerPcant = mysqli_query($connV, $sqlPCant) or die("No puedo recuperar la promocion cantidad de las reglas " . mysqli_error($connV) . "<pre>" . $sqlPCant . "</pre>");
                    $arrPcant = mysqli_fetch_assoc($hacerPcant);
                    $promoCant = $arrPcant["promocion_cant"];
                    $promo = "si";
                    $cantidad = number_format($promoCant);
                    break;
            }
            $cualRegla = $tipoCalculo;
        } else {
            // no hay reglas ni una entonces reviso promociones
            $usoPromocion = "Si";
        }
    }
    /* NO HAY REGLA o no utiliza regla de precios.
     * USO PROMOCION
     * ============================================
     */
    if ($usoPromocion == "Si") {

        /*
         * Articulo en promocion
         * =========================================================
         * coloco los datos de la promocion para saber si se aplica y que descuentos
         * la promocion se aplica cuando se compra la cantidad, 
         * * */

        if ($arti->promocion == 'Si' && $promoLista == "si") {
            /*
             * Hay promocion cargada
             */
            $promoCant = $arti->promocion_cant;
            $promoPorc = $arti->promocion_por;
            $promoTipo = $arti->promocion_tipo;
            $aplicaPromo = "no";
            /*
             * Evaluo si la promocion que podria aplicar tiene un porcentaje
             * que sea mayor al descuento del renglon del cliente, si no
             * dejo el descuento del cliente. 
             */

            /* PROMOCION PERIODO  PARA TODAS LAS PROMOS EXCEPTO CANT-INTERVALO */
            /* ===============================================================
             *  */
            $hayVigencia = vigencia_promo($arti->promocion_vigencia_desde, $arti->promocion_vigencia_hasta);

            //               echo "y la vigencia? {".$hayVigencia."}";

            if ($hayVigencia == "si") {
                $aplicaPromo = "si";
            }

            // calculo promociones.
            if ($aplicaPromo == "si") {
                switch ($promoTipo) {
                    case 'Cantidad - Intervalo':
                        // no hago nada porque ni siquiera se si esta vigente.
                        //                            echo "adentro cantidad intervalo";
                        $promo = "si";
                        $descFinal = 0;
                        $cantidad = 1;

                        break;
                    case 'Importe descuento':
                        if ($descRenglon > $promoPorc) {
                            /*
                             * el descuento x renglon es mayor que la promocion
                             * la desactivo
                             */
                            $descFinal = $descRenglon;
                            $promo = "no";
                        } else {
                            /*
                             * el descuento x renglon es menor uso la promocion
                             */
                            $descFinal = $promoPorc;
                            $promo = "si";
                        }

                        $precioNetoNuevo = $precioNeto;
                        $precioNetoCalc = $precioNeto - ($precioNeto * $descFinal / 100);

                        $importeIva = ($precioNetoNuevo * $arti->Alic) / 100;
                        $importeInterno = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                        $precioVenta = $precioNetoNuevo + $importeIva + $importeInterno;
                        $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                        $precioNeto = $precioNetoNuevo;
                        $promoCant = "";
                        $cantidad = 1;



                        break;
                    case 'Cantidad':
                        // % descuento por la compra de X unidades
                        if ($descRenglon > $promoPorc) {
                            /*
                             * el descuento x renglon es mayor que la promocion
                             * la desactivo
                             */
                            $descFinal = $descRenglon;
                            $promo = "no";
                        } else {

                            // el descuento x renglon es menor uso la promocion
                            // recalculo porque asumo que debo comprar la promo completa de la cantidad.
                            $descFinal = $promoPorc;
                            $promo = "si";
                            $precioNetoNuevo = $precioNeto;
                            $precioNetoCalc = $precioNeto - ($precioNeto * $descFinal / 100);

                            $importeIva = ($precioNetoNuevo * $arti->Alic) / 100;
                            $importeInterno = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                            $precioVenta = $precioNetoNuevo + $importeIva + $importeInterno;
                            $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                            $precioNeto = $precioNetoNuevo;
                        }

                        $cantidad = $promoCant;

                        break;
                    case 'Cantidad - Unidad':

                        // 2 x 1 gratis
                        $promo = "si";
                        // cantidad Gratis
                        $descFinal = $promoPorc;
                        // cantidad a comprar
                        $cantidad = $promoCant;

                        break;
                }
            }

            // articulo en promocion pero fuera de intervalo
            // aplico descuento del cliente
            if ($aplicaPromo == "no" && $descRenglon > 0) {
                //                    echo "hay descuento del cliente no promocion".$aplicaPromo." des c".$descRenglon;
                /*
                 * el descuento 
                 * la desactivo
                 */
                $precioNetoNuevo = $precioNeto;
                $descRenglonCalc = ($descFinal * $precioNeto / 100);
                $precioNetoCalc = $precioNetoNuevo - $descRenglonCalc;
                $importeIva = ($precioNetoNuevo * $arti->Alic) / 100;
                $importeInterno = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                $precioVenta = $precioNetoNuevo + $importeIva + $importeInterno;
                $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                $precioNeto = $precioNetoNuevo;
                //$precioNetoCalc     = $precioNeto;
                $promoCant = "";
                //                                $promoPorc          = "";
                $promo = "no";
                $cantidad = 1;

                $descFinal = $descRenglon;
            }
        }




        /* ARTICULOS SIN PROMOCION
          // SIN PRoMOCION DESCUENTO X CLIENTE
         * ==================================================================
         */
        if ($arti->promocion == 'No' || $promoLista == "no") {
            //                echo "hay articulo sin promocion";
            // SIN PRoMOCION DESCUENTO X CLIENTE
            /*
             * No existe promocion asi que evaluo si aplico el descuento
             * x renglon del articulo.
             */
            if ($descRenglon > 0) {
                /*
                 * Debo recalcular el precio de acuerdo al descuento
                 */

                $descFinal = $descRenglon;
                $precioNetoNuevo = $precioNeto;
                $descRenglonCalc = ($descFinal * $precioNeto / 100);
                $precioNetoCalc = $precioNetoNuevo - $descRenglonCalc;
                $importeIva = ($precioNetoNuevo * $arti->Alic) / 100;
                $importeInterno = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                $precioVenta = $precioNetoNuevo + $importeIva + $importeInterno;
                $precioNeto = $precioNetoNuevo;
                $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
            }
            $cantidad = 1;
            $promo = "no";
        }
    }

    /**
     * Armado promocion SOLO WEB
     * - soy el tipo promocion solo web  y estoy vigente.
     */
    //echo var_dump($hayPromocionSoloWeb=='Si' && $haySoloWeb=='si');   
    if ($hayPromocionSoloWeb == 'Si' && $haySoloWeb == 'si') {
        // echo "dentro promocion solo Web";
        $descFinal = $arti->descuento_solo_web;
        $promo = "si";
        $promoTipo = "soloWeb";

        $precioNetoNuevo = $precioNeto;
        $precioNetoCalc = $precioNeto - ($precioNeto * $descFinal / 100);

        $importeIva = ($precioNetoNuevo * $arti->Alic) / 100;
        $importeInterno = $precioNetoNuevo * ($arti->impuesto_interno / 100);
        $precioVenta = $precioNetoNuevo + $importeIva + $importeInterno;
        $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
        $precioNeto = $precioNetoNuevo;
        $promoCant = "";
        $cantidad = 1;
    }
    /* elimina precios y descuentos si no hay cliente .
    if(MuestraPrecioSiempre=='No'){
        if (isset($idCliente) && $idCliente == 1) {
            $precioNeto = 0;
            $precioNetoCalc = 0;
            $precioVenta = 0;
            $descFinal = 0;
            $precioVentaFinal = 0;
        }
    }*/

    # Moneda 
    $moneda = 'pesos';
    $cotizacion = 1;

    if (isset($_SESSION['moneda']) && $_SESSION['moneda'] == 'dolar' && isset($_SESSION['CambioPesos'])) {
        //echo "<pre>";
        //print_r($_SESSION['moneda']);
        //echo "<br>CambioPEsos:".print_r($_SESSION['CambioPesos']);
        //echo "</pre>";
        $moneda = $_SESSION['moneda'];
        $cotizacion = $_SESSION['CambioPesos'];
    }

    # promociones con decimales.
    if (is_int($cantidad)) {
        $cantidad = round($cantidad, 0);
    }

    # TODOS LOS PRECIOS VAN CON LA COTIZACION
    $precios = array(
        "idart" => $arti->IDArt,
        "neto" => ($precioNeto * $cotizacion),
        "netoCalc" => ($precioNetoCalc * $cotizacion),
        "precioVenta" => ($precioVenta * $cotizacion),
        "descuento" => $descFinal,
        "precioFinal" => ($precioVentaFinal * $cotizacion),
        "promoNombre" => $nombreArticulo,
        "clase" => $clase,
        "clasePrecio" => $clasePrecio,
        "importeIva" => ($importeIva * $cotizacion),
        "importeInterno" => ($importeInterno * $cotizacion),
        "promo" => $promo,
        "descCli" => $descRenglon,
        "montoDescuento" => ($precioNeto - $precioNetoCalc) * $cotizacion,
        "cantidad" => $cantidad,
        "promoTipo" => $promoTipo,
        "usoRegla" => $aplicoRegla,
        "queRegla" => $cualRegla,
        "importeIvaViejo" => ($importeIva * $cotizacion),
        "ivaAlic" => $arti->Alic,
        "impIvaFinal" => ($precioVentaFinal - $precioNetoCalc) * $cotizacion,
        "moneda" => $moneda,
        "cotizacion" => $cotizacion
    );


    return $precios;
}

function reglasPrecioMasivas($connV, $idArt = null, $codigoProveedor = null, $codigoRubro = null, $idSubRubro = null, $codCliente = null)
{

    /*  ''''''''''''''''
      'Reglas Masivas'
      ''''''''''''''''
     */
    //echo "<pre>";
    //var_dump($connV);
    //echo "</pre>";
    $varR = null;
    $fecha = date("Y-m-d");

    //    'Existen reglas de alta
    $sqlRegla = "SELECT * FROM reglas_precio_masivas WHERE Anulado = 'No'";
    $hacer = mysqli_query($connV, $sqlRegla) or die('No puedo recuperar reglas de precio ' . mysqli_error($connV) . ' <pre>' . $sqlRegla . '</pre>');
    $arrReglas = array();
    while ($rr = mysqli_fetch_assoc($hacer)) {
        $arrReglas[] = $rr;
    }

    if (empty($arrReglas)) {
        // sin reglas masivas me vuelvo si o si
        return $varR;
    }

    // no tengo cliente asi que le asigno el cliente consumidor final. por defecto
    if ($codCliente == null) {
        $codCliente = 1;
    }

    //    '''''''''''
    //    '#Vigencia'
    //    '''''''''''
    //    fechita = Split(CStr(Principal.Fecha), "/")
    //    Fecha3 = fechita(2) & "-" & fechita(1) & "-" & fechita(0)
    //    'Inicializo
    //    $VarR = 0;
    //    'Obtengo datos del articulo
    //    rs_r.Open "SELECT CodigoProveedor, CodigoRubro, IDSubRubro FROM articulo " & _
    //              "WHERE articulo.IDART = " & IDArt & " ", conn, adOpenDynamic, adLockOptimistic
    //
    //    If Not IsNull(rs_r.Fields!CodigoProveedor) Then
    //        CodProv = rs_r.Fields!CodigoProveedor
    //    End If
    //    
    //     If Not IsNull(rs_r.Fields!CodigoRubro) Then
    //        CodRubro = rs_r.Fields!CodigoRubro
    //    End If
    //    
    //    If Not IsNull(rs_r.Fields!IDSubRubro) Then
    //        IDSubRubro = rs_r.Fields!IDSubRubro
    //    End If
    //    
    //    rs_r.Close

    /* ''''''''''''
      //' X CLIENTE'
      //'''''''''''' */

    if ($codigoProveedor != null) {

        //        '5Rubro
        //        ================================
        //            rs_r.Open "SELECT id_regla_precio_masivas " & _
        //                      "FROM reglas_precio_masivas " & _
        //                      "WHERE Anulado = 'No' AND " & _
        //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
        //                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
        //                      "id_rubro = " & CodRubro & " AND " & _
        //                      "isnull(id_proveedor) AND isnull(id_sub_rubro) ", conn, adOpenDynamic, adLockOptimistic
        $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
            . "FROM reglas_precio_masivas AS rpm "
            . "WHERE rpm.Anulado = 'No' AND "
            . " '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
            . "rpm.id_cliente = {$codCliente} AND "
            . "rpm.id_rubro = {$codigoRubro} AND "
            . "ISNULL(rpm.id_proveedor) AND ISNULL(rpm.id_sub_rubro)";
        $hacerRpm = mysqli_query($connV, $sqlRegla) or die("no pude buscar regla Cliente-Rubro" . mysqli_error($connV) . "<pre>" . $sqlRegla . "</pre>");
        $arrRrubro = array();
        while ($rrubro = mysqli_fetch_assoc($hacerRpm)) {
            $arrRrubro = $rrubro;
        }
        if (!empty($arrRrubro)) {
            // habia regla del rubro devuelvo la regla.
            $varR = $arrRrubro["id_regla_precio_masivas"];
            //                return $varR;
        }

        //            If rs_r.RecordCount = 1 Then
        //                VarR = rs_r.Fields!id_regla_precio_masivas
        //            End If
        //
        //            rs_r.Close
        //        '4SubRubro
        //        =================================================              
        //            rs_r.Open "SELECT id_regla_precio_masivas " & _
        //                      "FROM reglas_precio_masivas " & _
        //                      "WHERE Anulado = 'No' AND " & _
        //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
        //                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
        //                      "id_sub_rubro = " & IDSubRubro & " AND " & _
        //                      "isnull(id_proveedor) AND isnull(id_rubro) LIMIT 1 ", conn, adOpenDynamic, adLockOptimistic
        $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
            . "FROM reglas_precio_masivas AS rpm "
            . "WHERE rpm.Anulado = 'No' AND "
            . " '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
            . "rpm.id_cliente = {$codCliente} AND "
            . "rpm.id_sub_rubro = {$idSubRubro} AND "
            . "ISNULL(rpm.id_proveedor) AND ISNULL(rpm.id_rubro) LIMIT 1 ";
        $hacerRpm = mysqli_query($connV, $sqlRegla) or die("no pude buscar regla Cliente-SubRubro" . mysqli_error($connV) . "<pre>" . $sqlRegla . "</pre>");
        $arrRrubro = array();
        while ($rrubro = mysqli_fetch_assoc($hacerRpm)) {
            $arrRrubro = $rrubro;
        }
        if (!empty($arrRrubro)) {
            // habia regla del Subrubro devuelvo la regla.
            $varR = $arrRrubro["id_regla_precio_masivas"];
            //                return $varR;
        }

        //            If rs_r.RecordCount = 1 Then
        //                VarR = rs_r.Fields!id_regla_precio_masivas
        //            End If
        //
        //            rs_r.Close
        //        '3Proveedor
        //         ========================================================================             
        //            rs_r.Open "SELECT id_regla_precio_masivas " & _
        //                      "FROM reglas_precio_masivas " & _
        //                      "WHERE Anulado = 'No' AND " & _
        //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
        //                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
        //                      "id_proveedor = " & CodProv & " AND " & _
        //                      "isnull(id_rubro) AND isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic

        $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
            . "FROM reglas_precio_masivas AS rpm "
            . "WHERE rpm.Anulado = 'No' AND "
            . " '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
            . "rpm.id_cliente = {$codCliente} AND "
            . "rpm.id_proveedor ={$codigoProveedor} AND "
            . "ISNULL(rpm.id_rubro) AND ISNULL(rpm.id_sub_rubro)";

        $hacerRpm = mysqli_query($connV, $sqlRegla) or die("no pude buscar regla Cliente-Proveedor" . mysqli_error($connV) . "<pre>" . $sqlRegla . "</pre>");
        $arrRrubro = array();
        while ($rrubro = mysqli_fetch_assoc($hacerRpm)) {
            $arrRrubro = $rrubro;
        }
        if (!empty($arrRrubro)) {
            // habia regla del proveedor devuelvo la regla.
            $varR = $arrRrubro["id_regla_precio_masivas"];
            //                return $varR;
        }

        //            If rs_r.RecordCount = 1 Then
        //                VarR = rs_r.Fields!id_regla_precio_masivas
        //            End If
        //
        //            rs_r.Close
        //        '2Proveedor Rubro
        //          ========================================================================  
        //            rs_r.Open "SELECT id_regla_precio_masivas " & _
        //                      "FROM reglas_precio_masivas " & _
        //                      "WHERE Anulado = 'No' AND " & _
        //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
        //                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
        //                      "id_proveedor = " & CodProv & " AND " & _
        //                      "id_rubro = " & CodRubro & " AND " & _
        //                      "isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic
        $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
            . "FROM reglas_precio_masivas AS rpm "
            . "WHERE rpm.Anulado = 'No' AND "
            . " '{$fecha}' BETWEEN rpm.vigencia_desde AND rpm.vigencia_hasta AND "
            . "rpm.id_cliente = {$codCliente} AND "
            . "rpm.id_proveedor ={$codigoProveedor} AND "
            . "rpm.id_rubro ={$codigoRubro}  AND "
            . "ISNULL(rpm.id_sub_rubro)";
        $hacerRpm = mysqli_query($connV, $sqlRegla) or die("no pude buscar regla Cliente-Proveedor-Rubro" . mysqli_error($connV) . "<pre>" . $sqlRegla . "</pre>");
        $arrRrubro = array();
        while ($rrubro = mysqli_fetch_assoc($hacerRpm)) {
            $arrRrubro = $rrubro;
        }
        if (!empty($arrRrubro)) {
            // habia regla del cliente proveedor rubro devuelvo la regla.
            $varR = $arrRrubro["id_regla_precio_masivas"];
            //                return $varR;
        }

        //            If rs_r.RecordCount = 1 Then
        //                VarR = rs_r.Fields!id_regla_precio_masivas
        //            End If
        //
        //            rs_r.Close
        //        '1Proveedor SubRubro
        //        =====================================================================
        //            rs_r.Open "SELECT id_regla_precio_masivas " & _
        //                      "FROM reglas_precio_masivas " & _
        //                      "WHERE Anulado = 'No' AND " & _
        //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
        //                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
        //                      "id_proveedor = " & CodProv & " AND " & _
        //                      "id_sub_rubro = " & IDSubRubro & " AND " & _
        //                      "isnull(id_rubro)  ", conn, adOpenDynamic, adLockOptimistic
        $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
            . "FROM reglas_precio_masivas AS rpm "
            . "WHERE rpm.Anulado = 'No' AND "
            . " '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
            . "rpm.id_cliente ={$codCliente}  AND "
            . "rpm.id_proveedor ={$codigoProveedor}  AND "
            . "rpm.id_sub_rubro ={$idSubRubro} AND "
            . "ISNULL(rpm.id_rubro)";
        $hacerRpm = mysqli_query($connV, $sqlRegla) or die("no pude buscar regla Cliente-Proveedor-SubRubro" . mysqli_error($connV) . "<pre>" . $sqlRegla . "</pre>");
        $arrRrubro = array();
        while ($rrubro = mysqli_fetch_assoc($hacerRpm)) {
            $arrRrubro = $rrubro;
        }
        if (!empty($arrRrubro)) {
            // habia regla del cliente proveedor Subrubro devuelvo la regla.
            $varR = $arrRrubro["id_regla_precio_masivas"];
            //                return $varR;
        }
        //            If rs_r.RecordCount = 1 Then
        //                VarR = rs_r.Fields!id_regla_precio_masivas
        //            End If
        //
        //            rs_r.Close
    } else {

        //        '5Rubro
        //        ============================================================================    
        //            rs_r.Open "SELECT id_regla_precio_masivas " & _
        //                      "FROM reglas_precio_masivas " & _
        //                      "WHERE Anulado = 'No' AND " & _
        //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
        //                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
        //                      "id_rubro = " & CodRubro & " AND " & _
        //                      "isnull(id_proveedor) AND isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic

        $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
            . "FROM reglas_precio_masivas AS rpm "
            . "WHERE rpm.Anulado = 'No' AND "
            . " '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
            . "rpm.id_cliente ={$codCliente} AND "
            . "rpm.id_rubro ={$codigoRubro} AND "
            . "ISNULL(rpm.id_proveedor) AND ISNULL(rpm.id_sub_rubro)";
        $hacerRpm = mysqli_query($connV, $sqlRegla) or die("no pude buscar regla Cliente-Rubro Sin Proveedor" . mysqli_error($connV) . "<pre>" . $sqlRegla . "</pre>");
        $arrRrubro = array();
        while ($rrubro = mysqli_fetch_assoc($hacerRpm)) {
            $arrRrubro = $rrubro;
        }
        if (!empty($arrRrubro)) {
            // habia regla del cliente rubro sin proveedor devuelvo la regla.
            $varR = $arrRrubro["id_regla_precio_masivas"];
            //                return $varR;
        }
        //            If rs_r.RecordCount = 1 Then
        //                VarR = rs_r.Fields!id_regla_precio_masivas
        //            End If
        //
        //            rs_r.Close
        //        '4SubRubro
        //        ==========================================================================    
        //            rs_r.Open "SELECT id_regla_precio_masivas " & _
        //                      "FROM reglas_precio_masivas " & _
        //                      "WHERE Anulado = 'No' AND " & _
        //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
        //                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
        //                      "id_sub_rubro = " & IDSubRubro & " AND " & _
        //                      "isnull(id_proveedor) AND isnull(id_rubro)  ", conn, adOpenDynamic, adLockOptimistic
        $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
            . "FROM reglas_precio_masivas AS rpm "
            . "WHERE rpm.Anulado = 'No' AND "
            . " '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
            . "rpm.id_cliente ={$codCliente}  AND "
            . "rpm.id_sub_rubro ={$idSubRubro} AND "
            . "ISNULL(rpm.id_proveedor) AND ISNULL(rpm.id_rubro)";

        //            If rs_r.RecordCount = 1 Then
        //                VarR = rs_r.Fields!id_regla_precio_masivas
        //            End If
        //
        //            rs_r.Close
        $hacerRpm = mysqli_query($connV, $sqlRegla) or die("no pude buscar regla Cliente-SubRubro Sin Proveedor" . mysqli_error($connV) . "<pre>" . $sqlRegla . "</pre>");
        $arrRrubro = array();
        while ($rrubro = mysqli_fetch_assoc($hacerRpm)) {
            $arrRrubro = $rrubro;
        }
        if (!empty($arrRrubro)) {
            // habia regla del cliente subrubro sin proveedor devuelvo la regla.
            $varR = $arrRrubro["id_regla_precio_masivas"];
            //                return $varR;
        }
    }

    /* 'Resultado'''''''
      RPrecioM = VarR '
      ''''''''''''''''' */
    return $varR;
}

function reglasPrecioGeneral($connV, $idArt = null, $codigoProveedor = null, $codigoRubro = null, $idSubRubro = null)
{
    //    ''''''''''''''''''''''''''''
    //    'Reglas Masivas - Generales'
    //    ''''''''''''''''''''''''''''    
    $varR = null;
    $fecha = date("Y-m-d");

    //    'Existen reglas de alta
    //    Dim rs_r As New ADODB.Recordset
    //    rs_r.Open "SELECT * FROM reglas_precio_alta_art WHERE Anulado = 'No' ", conn, adOpenDynamic, adLockOptimistic
    //
    //    If rs_r.RecordCount = 0 Then
    //        rs_r.Close
    //        Exit Function
    //    End If
    //
    //    rs_r.Close
    $sqlG = "SELECT id_regla_precio_alta_art FROM reglas_precio_alta_art WHERE Anulado='No' LIMIT 1";
    $hacerG = mysqli_query($connV, $sqlG) or die("No puedo recuper reglas precio alta art " . mysqli_error($connV) . "<PRE>" . $sqlG . "</PRE>");
    $hayR = mysqli_fetch_array($hacerG);
    if (empty($hayR)) {
        // no hay reglas asi que me vuelvo sin nada
        return $varR;
    }


    //          '''''''''''''''
    //          ' No X CLIENTE'
    //          '''''''''''''''

    if ($codigoProveedor != null) {

        //        '5Rubro
        //        ======================================================    
        //            rs_r.Open "SELECT id_regla_precio_alta_art " & _
        //                      "FROM reglas_precio_alta_art " & _
        //                      "WHERE Anulado = 'No' AND " & _
        //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
        //                      "id_rubro = " & CodRubro & " AND " & _
        //                      "isnull(id_proveedor) AND isnull(id_sub_rubro) ", conn, adOpenDynamic, adLockOptimistic
        $sqlRegla = "SELECT rpma.id_regla_precio_alta_art "
            . "FROM reglas_precio_alta_art AS rpma "
            . "WHERE rpma.Anulado = 'No' AND "
            . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
            . "rpma.id_rubro = {$codigoRubro} AND "
            . "ISNULL(rpma.id_proveedor) AND ISNULL(rpma.id_sub_rubro)";

        $hacerR = mysqli_query($connV, $sqlRegla) or die("No puedo recuperar la lista precio alta art por Rubro " . mysqli_error($connV) . "<pre>" . $sqlRegla . " </pre>");
        $arrRegla = array();
        while ($ff = mysqli_fetch_assoc($hacerR)) {
            $arrRegla = $ff;
        }
        if (!empty($arrRegla)) {
            // hay regla general de articulo por Proveedo y Rubro.
            $varR = $arrRegla["id_regla_precio_alta_art"];
            //                return $varR;
        }


        //        '4SubRubro
        //        '==========================================================    
        //
        //            rs_r.Open "SELECT id_regla_precio_alta_art " & _
        //                      "FROM reglas_precio_alta_art " & _
        //                      "WHERE Anulado = 'No' AND " & _
        //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
        //                      "id_sub_rubro = " & IDSubRubro & " AND " & _
        //                      "isnull(id_proveedor) AND isnull(id_rubro) LIMIT 1 ", conn, adOpenDynamic, adLockOptimistic

        $sqlRegla = "SELECT rpma.id_regla_precio_alta_art "
            . "FROM reglas_precio_alta_art AS rpma "
            . "WHERE rpma.Anulado = 'No' AND "
            . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
            . "rpma.id_sub_rubro = {$idSubRubro} AND "
            . "ISNULL(rpma.id_proveedor) AND ISNULL(rpma.id_rubro) LIMIT 1 ";
        $hacerR = mysqli_query($connV, $sqlRegla) or die("No puedo recuperar la lista precio alta art por SubRubro " . mysqli_error($connV) . "<pre>" . $sqlRegla . " </pre>");
        $arrRegla = array();
        while ($ff = mysqli_fetch_assoc($hacerR)) {
            $arrRegla = $ff;
        }
        if (!empty($arrRegla)) {
            // hay regla general de articulo por Proveedo y Subrubro.
            $varR = $arrRegla["id_regla_precio_alta_art"];
            //                return $varR;
        }


        //        '3Proveedor
        //        ==========================================================================
        //            rs_r.Open "SELECT id_regla_precio_alta_art " & _
        //                      "FROM reglas_precio_alta_art " & _
        //                      "WHERE Anulado = 'No' AND " & _
        //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
        //                      "id_proveedor = " & CodProv & " AND " & _
        //                      "isnull(id_rubro) AND isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic

        $sqlRegla = "SELECT rpma.id_regla_precio_alta_art "
            . "FROM reglas_precio_alta_art AS rpma "
            . "WHERE rpma.Anulado = 'No' AND "
            . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
            . "rpma.id_proveedor = {$codigoProveedor} AND "
            . "ISNULL(rpma.id_rubro) AND ISNULL(rpma.id_sub_rubro)";
        $hacerR = mysqli_query($connV, $sqlRegla) or die("No puedo recuperar la lista precio alta art por Proveedor " . mysqli_error($connV) . "<pre>" . $sqlRegla . " </pre>");
        $arrRegla = array();
        while ($ff = mysqli_fetch_assoc($hacerR)) {
            $arrRegla = $ff;
        }
        if (!empty($arrRegla)) {
            // hay regla general de articulo por Proveedo .
            $varR = $arrRegla["id_regla_precio_alta_art"];
            //                return $varR;
        }


        //        '2Proveedor Rubro
        //        ===========================================================================    
        //            rs_r.Open "SELECT id_regla_precio_alta_art " & _
        //                      "FROM reglas_precio_alta_art " & _
        //                      "WHERE Anulado = 'No' AND " & _
        //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
        //                      "id_proveedor = " & CodProv & " AND " & _
        //                      "id_rubro = " & CodRubro & " AND " & _
        //                      "isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic

        $sqlRegla = "SELECT rpma.id_regla_precio_alta_art "
            . "FROM reglas_precio_alta_art AS rpma "
            . "WHERE rpma.Anulado = 'No' AND "
            . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
            . "rpma.id_proveedor = {$codigoProveedor} AND "
            . "rpma.id_rubro = {$codigoRubro} AND "
            . "isnull(rpma.id_sub_rubro)  ";
        $hacerR = mysqli_query($connV, $sqlRegla) or die("No puedo recuperar la lista precio alta art por Proveedor y Rubro juntos" . mysqli_error($connV) . "<pre>" . $sqlRegla . " </pre>");
        $arrRegla = array();
        while ($ff = mysqli_fetch_assoc($hacerR)) {
            $arrRegla = $ff;
        }
        if (!empty($arrRegla)) {
            // hay regla general de articulo por Proveedor y rubro juntos .
            $varR = $arrRegla["id_regla_precio_alta_art"];
            //                return $varR;
        }
        //        '1Proveedor SubRubro
        //        =======================================================================
        //            rs_r.Open "SELECT id_regla_precio_alta_art " & _
        //                      "FROM reglas_precio_alta_art " & _
        //                      "WHERE Anulado = 'No' AND " & _
        //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
        //                      "id_proveedor = " & CodProv & " AND " & _
        //                      "id_sub_rubro = " & IDSubRubro & " AND " & _
        //                      "isnull(id_rubro)  ", conn, adOpenDynamic, adLockOptimistic

        $sqlRegla = "SELECT rpma.id_regla_precio_alta_art "
            . "FROM reglas_precio_alta_art AS rpma "
            . "WHERE rpma.Anulado = 'No' AND "
            . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
            . "rpma.id_proveedor = {$codigoProveedor} AND "
            . "rpma.id_sub_rubro = {$idSubRubro} AND "
            . "ISNULL(rpma.id_rubro) ";
        $hacerR = mysqli_query($connV, $sqlRegla) or die("No puedo recuperar la lista precio alta art por Proveedor y SubRubro juntos" . mysqli_error($connV) . "<pre>" . $sqlRegla . " </pre>");
        $arrRegla = array();
        while ($ff = mysqli_fetch_assoc($hacerR)) {
            $arrRegla = $ff;
        }
        if (!empty($arrRegla)) {
            // hay regla general de articulo por Proveedor y Subrubro juntos .
            $varR = $arrRegla["id_regla_precio_alta_art"];
            //                return $varR;
        }
    } else {

        //        '5Rubro
        //        =========================================================================    
        //            rs_r.Open "SELECT id_regla_precio_alta_art " & _
        //                      "FROM reglas_precio_alta_art " & _
        //                      "WHERE Anulado = 'No' AND " & _
        //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
        //                      "id_rubro = " & CodRubro & " AND " & _
        //                      "isnull(id_proveedor) AND isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic

        $sqlRegla = "SELECT rpma.id_regla_precio_alta_art "
            . "FROM reglas_precio_alta_art  AS rpma"
            . " WHERE rpma.Anulado = 'No' AND "
            . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
            . "rpma.id_rubro ={$codigoRubro} AND "
            . "ISNULL(rpma.id_proveedor) AND ISNULL(rpma.id_sub_rubro)";
        $hacerR = mysqli_query($connV, $sqlRegla) or die("No puedo recuperar la lista precio alta art SIN Proveedor y por Rubro" . mysqli_error($connV) . "<pre>" . $sqlRegla . " </pre>");
        $arrRegla = array();
        while ($ff = mysqli_fetch_assoc($hacerR)) {
            $arrRegla = $ff;
        }
        if (!empty($arrRegla)) {
            // hay regla general de articulo Sin Proveedor y por Rubro .
            $varR = $arrRegla["id_regla_precio_alta_art"];
            //                return $varR;
        }

        //        '4SubRubro
        //        ===========================================================================
        //            rs_r.Open "SELECT id_regla_precio_alta_art " & _
        //                      "FROM reglas_precio_alta_art " & _
        //                      "WHERE Anulado = 'No' AND " & _
        //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
        //                      "id_sub_rubro = " & IDSubRubro & " AND " & _
        //                      "isnull(id_proveedor) AND isnull(id_rubro)  ", conn, adOpenDynamic, adLockOptimistic

        $sqlRegla = "SELECT rpma.id_regla_precio_alta_art "
            . "FROM reglas_precio_alta_art AS rpma "
            . "WHERE rpma.Anulado = 'No' AND "
            . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
            . "rpma.id_sub_rubro ={$idSubRubro} AND "
            . "ISNULL(rpma.id_proveedor) AND ISNULL(rpma.id_rubro)";
        $hacerR = mysqli_query($connV, $sqlRegla) or die("No puedo recuperar la lista precio alta art SIN Proveedor y por SubRubro" . mysqli_error($connV) . "<pre>" . $sqlRegla . " </pre>");
        $arrRegla = array();
        while ($ff = mysqli_fetch_assoc($hacerR)) {
            $arrRegla = $ff;
        }
        if (!empty($arrRegla)) {
            // hay regla general de articulo Sin Proveedor y por Rubro .
            $varR = $arrRegla["id_regla_precio_alta_art"];
            //                return $varR;
        }
    }

    //    'Resultado'''''''
    //    RPrecioG = VarR '
    //    '''''''''''''''''    
    return $varR;
}

/**
 * Funcion producto INDIVIDUAL el detalle con PRECIO.
 * ------------------------------------------------
 * detalle del producto completo Con precio y demas datos.
 */
function producto_precio($promo, $connV, $arrFotos)
{

    $descRenglon = 0;
    $codCliente = 1;
    $tStock = "";
    $sufijoPrecio = "";
    $tBultoPromedio = "";
    $bultoPromedio = 0;

    $precioNeto = 0;
    $importeIva = 0;
    $importeInterno = 0;
    $precioVenta = 0;
    $bulto = "";
    $codigoProducto = '';
    $idArt = $promo->IDArt;
    $idManual = $promo->id_manual;
    $nombreArticulo = '';
    $promoDesde = $promo->promocion_vigencia_desde;
    $promoHasta = $promo->promocion_vigencia_hasta;
    $videoProducto = '';
    $hayPromo = 'no';
    $tipoPromo = '';
    $linkAfuera = '';
    $linkVideo = '';
    $detalleProducto = '';
    $propiedadesProducto = '';
    $saldoArticulo = 0;
    $mostrarControlesVenta = 0; // inicial los muestro.
    $usaReglaPrecio = $_SESSION['usaReglaPrecio'];
    $verStock = $_SESSION["verStock"]; // mostrar stock 
    $ventaSinStock = $_SESSION["venta_sin_stock"]; // validar stock cero no mostrar controles.
    $ivaIncluido =   $_SESSION["ivaIncluido"];
    $usaBultoPromedio = $_SESSION["uso_bulto_promedio"];
    $usaVenta = 'Si';

    # carrito habilitado 
    if (defined('VENTACARRITO')) {
        $usaVenta = VENTACARRITO;
    }
    # embalaje
    $usaEmbalaje = $_SESSION["utilizaEmbalaje"];

    # hay un cliente seleccionado logueado
    if (isset($_SESSION['clienteDetalle']) && is_object($_SESSION['clienteDetalle'])) {
        $objCliente = $_SESSION['clienteDetalle'];
        $descRenglon = $objCliente->descRenglon;
        $codCliente = $objCliente->Codigo;
    }

    # codigo a mostrar de articulo
    if (CODIGOPRODUCTO == 'sistema') {
        $codigoProducto = $idArt;
    } else {
        $codigoProducto = $idManual;
    }



    # nombre de articulo    
    if ($promo->usa_nombre_articulo_ecom == 'Si') {
        $nombreArticulo = $promo->nombre_articulo_ecom;
    } else {
        $nombreArticulo = $promo->NombreArticulo;
    }
    $nombreArticulo = ucwords($nombreArticulo);
    # url
    $srcArticulo = urlAmigable($nombreArticulo, $codigoProducto);

    #linkAfuera
    if ($promo->link_video_articulo_ecom !== null && $promo->link_video_articulo_ecom !== '') {
        $linkAfuera = $promo->link_articulo_ecom;
    }
    # link video youtube
    if ($promo->link_video_articulo_ecom != null && $promo->link_video_articulo_ecom !== '') {
        $linkVideo = $promo->link_video_articulo_ecom;
    }
    #lista de precios
    $listaPrecioCliente =  $_SESSION["lista_precio_defecto"];

    # detalle del producto
    if ($promo->detalle_ecom != '') {
        $detalleProducto = nl2br($promo->detalle_ecom);
    } else {
        if ($promo->detalle_web != '') {
            $detalleProducto = nl2br($promo->detalle_web);
        }
    }





    if ($usaEmbalaje == "Si") {
        // tengo que hacer la busqueda de los valores para mostrar
        $bulto = $promo->nombre_presentacion . " x " . $promo->cantidad_uni;
        if ($promo->nombre_unimed != "") {
            $bulto .= " (" . $promo->nombre_unimed . ")";
        }
    }

    # precios
    $precios = calculaPrecios($connV, $promo, $listaPrecioCliente, $descRenglon, $usaReglaPrecio, $codCliente);
    // echo "<pre>";
    // print_r($promo);
    // print_r($listaPrecioCliente);
    // print_r($precios);
    // echo "</pre>";


    $tipoPromo = $precios['promoTipo'];
    if ($precios["cantidad"] < 1) {
        $cantidad = 1;
    } else {
        $cantidad = $precios["cantidad"];
    }

    $promoCant = $precios["cantidad"];
    $usoPromo = $precios["promo"];


    # stock  
    /*
    if($_SESSION["verStock"]=="Si"){
        if ($promo->cantidad_promedio_bulto > 0 && $promo->tipo_unidad == "Peso") {
            $tStock .= " Stock: " . number_format(($promo->saldo / $promo->cantidad_promedio_bulto), 2, ',', '');
            $tStock .= ", Disp: " . number_format(($promo->disponible / $promo->cantidad_promedio_bulto), 2, ',', '');
        
            $tStock .= ", Pres: (" . $promo->cantidad_promedio_bulto . " " . $promo->uniArt . ")";
        } else {
            $tStock .= "Stock: " . $promo->saldo;
            $tStock .= ", Disp: " . $promo->disponible;
        
        }
    }
    */
    #stock visualiza y valida stock.
    if ($verStock == 'Si' || $ventaSinStock == 'No') {
        // buscar disponible 
        $stockDisponible = buscar_saldo_pendiente_unico($idArt, $promo);
        //$saldoArticulo = round($promo->saldo);
        $saldoArticulo = round($stockDisponible);
    }
    //echo "<pre>";
    //echo "stockDisponible:: ".$stockDisponible."<br>";
    //echo "SaldoPromo:: ".$promo->saldo."<br>";
    //echo "soyEcomm::".$promo->ecommerce."<br>";
    //echo "</pre>";

    # stock visualiza

    if ($verStock == "Si") {


        if ($saldoArticulo <= 0 || $promo->ecommerce == 'No') {
            $mostrarControlesVenta++;
            $tStock .= "Stock: <span> Agotado.</span>";
        }

        if ($saldoArticulo > 0) {
            $tStock .= "Stock: <span>" . $saldoArticulo . " unidades.</span>";
            //$tStock .= ", Disp: " . $promo->disponible;
        }
    }

    # producto discontinuado
    if ($promo->ecommerce == 'No') {
        $mostrarControlesVenta++;
    }



    $tagPromo = "";
    $txtDescuento = "";
    $textoPromo = "";
    # promociones
    if ($usoPromo == "si") {

        if ($tipoPromo == 'soloWeb') {
            $promoDesde = $promo->vigencia_desde_solo_web;
            $promoHasta = $promo->vigencia_hasta_solo_web;
        }

        $detallePromo = detalle_promo($tipoPromo, $precios["descuento"], $promoCant, $idArt, $connV);

        if ($detallePromo != 'no') {
            $textoPromo .= '<div class="promocion promo-grilla"><i class="fa fa-gift fa-lg fa-fw"></i> Promoción</div>' . PHP_EOL;

            $textoPromo .= '<div class="text-promo">' . PHP_EOL;
            $textoPromo .= $detallePromo . PHP_EOL;
            $textoPromo .= vigencia_promo_detalle($promoDesde, $promoHasta) . PHP_EOL;

            $textoPromo .= '</div>';
        }
    }



    $importeInterno = $precios["importeInterno"];
    $importeIva = $precios["impIvaFinal"];
    $alicuotaIva = $precios["ivaAlic"];

    if ($ivaIncluido == 'No') {

        $precioVenta = $precios["neto"];
        $precioVentaFinal = $precios["netoCalc"];
        $precioVentaFinalIva = $precios["precioFinal"]; // debo mostrar el precio con iva aun no siendo incluido
        $precioVentaFinalIvaF = number_format($precioVentaFinalIva, DECIMALES, ',', '.'); // precio formateado con iva. sumado al final.

    } else {

        $precioVenta = $precios["precioVenta"];
        $precioVentaFinal = $precios["precioFinal"];
        $precioVentaFinalIva = $precios["precioFinal"]; // debo mostrar el precio con iva aun no siendo incluido
        $precioVentaFinalIvaF = number_format($precioVentaFinalIva, DECIMALES, ',', '.'); // precio formateado con iva.

    }

    $descFinal = $precios["descuento"];

    $precioVentaF = number_format($precioVenta, DECIMALES, ',', '.'); // precio viejo sin descuento
    $precioVentaFinalF = number_format($precioVentaFinal, DECIMALES, ',', '.'); // precio iva incluido con descuento. sin iva es el neto.    
    $precioIvaFinalF = number_format($importeIva, DECIMALES, ',', '.'); // valor de iva.
    
    $descFinalF = number_format($descFinal, 0);
    $maxCant = "";
    $campoMax = "";



    # descuentos 
    if ($usoPromo == 'No' || $usoPromo == "no") {
        /*
         * Soy un cliente y tengo descuento pero no puedo tocarlo
         */
        if ($descFinal != 0) {
            //$txtDescuento = "<span class='verde'>{$descFinalF}% OFF</span>\n";
            $txtDescuento = $descFinalF;
        }
    }

    #promocion

    if ($usoPromo == 'si' || $usoPromo == "Si") {

        if ($tipoPromo == "Importe descuento") {
            //$txtDescuento = "<span class='verde'>{$descFinalF}% OFF</span>\n";
            $txtDescuento = $descFinalF;
        }
        if ($tipoPromo == 'soloWeb') {
            $txtDescuento = $descFinalF;
        }
        if ($tipoPromo == 'Cantidad') {
            $txtDescuento = $descFinalF;
        }
    }




    /*************************************************************************
     * fin variables de pedidos
     * ************************************************************************
     */


    $htmlF = '<section class="product-info">' . PHP_EOL;
    $htmlF .= '<div class="container">' . PHP_EOL;

    /*
     * Bread crumbs
     * ======================================================================
     */

    $arrBcrumb = array();
    $arrArg = array();
    //categoria
    $idCat = $promo->id_categoria;
    $categoria = $promo->NombCat;
    $idrubro = $promo->CodigoRubro;
    $rubro = $promo->NombRub;
    $idsubrubro = $promo->IDSubRubro;
    $subrubro = $promo->NombSubRub;

    // categoria
    $arrArg["idcategoria"] = $idCat;
    $arrArg["categoria"] = $categoria;
    $linkAnte = hacer_url("index.php", $arrArg);
    $arrBcrumb[] = '<a href="' . $linkAnte . '" class="breadcrumbs">' . $categoria . '<a/>';

    // rubro
    $arrArg["idrubro"] = $idrubro;
    $arrArg["rubro"] = $rubro;
    $linkAnte = hacer_url("index.php", $arrArg);
    $arrBcrumb[] = '<a href="' . $linkAnte . '" class="breadcrumbs">' . $rubro . '<a/>';
    //subrubro

    $arrArg["idsubrubro"] = $idsubrubro;
    $arrArg["subrubro"] = $subrubro;
    $arrArg["idmarca"] = null;
    $arrArg["marca"] = null;
    $arrArg["idtipocliente"] = null;
    $arrArg["tipocliente"] = null;

    $arrBcrumb[] = '<a tipo="subrubro" href="' . hacer_url("index.php", $arrArg) . '"  key="sr' . $idsubrubro . '" datos="' . $subrubro . '" id="' . $idsubrubro . '" class="breadcrumbs">' . $subrubro . '</a>';
    $breadCrumb = implode(" / ", $arrBcrumb);



    $htmlF .= '<div class="titulo-breadcrumbs">' . PHP_EOL;
    $htmlF .= '<div class="barra-breadcrumbs">' . PHP_EOL;
    $htmlF .= '<h4>' . PHP_EOL;
    $htmlF .= $breadCrumb . PHP_EOL;
    $htmlF .= '<h4>' . PHP_EOL;
    $htmlF .= '</div>' . PHP_EOL;
    $htmlF .= '</div>' . PHP_EOL;


    $htmlF .= '<div class="container">' . PHP_EOL;
    $htmlF .= '<div class="row justify-content-center">' . PHP_EOL;
    $htmlF .= '<div class="col-12 col-xl-10 producto">' . PHP_EOL;
    $htmlF .= '<div class="row">' . PHP_EOL;
    #nombre o titulo superior
    $htmlF .= '  <div class="col-12 top-nombre-producto">' . PHP_EOL;
    $htmlF .= '      <h3>' . $nombreArticulo . '</h3>' . PHP_EOL;
    $htmlF .= '   </div>' . PHP_EOL;

    # imagenes multifoto. 
    # miniaturas imagenes

    // no tengo fotos cargadas ponga unica foto el sin foto.
    if (empty($arrFotos)) {
        $htmlF .= '<div class="col-2 col-lg-1 thum">' . PHP_EOL; // contenedeor miniaturas multi foto.
        $htmlF .= '     <div class="product-images">' . PHP_EOL;
        $htmlF .= '         <div class="img-box">' . PHP_EOL;
        $htmlF .= '             <img src="Articulo_Foto/foto1_' . $idArt . '_1.jpeg" title="' . $nombreArticulo . '">' . PHP_EOL;
        $htmlF .= '         </div>' . PHP_EOL;
        $htmlF .= '     </div>' . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL; // fin contenededor multifoto
        # imagenes grandes
        $htmlF .= '<div class="col-10 col-lg-7 img">' . PHP_EOL;

        $htmlF .= '<div class="product-images">' . PHP_EOL;
        $htmlF .= '    <div class="slide-producto">' . PHP_EOL;
        $htmlF .= '        <a data-fancybox="images" href="Articulo_Foto/foto1_' . $idArt . '_0.jpeg"><img alt="" src="Articulo_Foto/foto1_' . $idArt . '_0.jpeg" title="' . $nombreArticulo . '"></a>' . PHP_EOL;
        $htmlF .= '    </div>' . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL;
    }
    // hay fotos mostrar lo que haya
    if (!empty($arrFotos)) {
        $htmlMiniaturas = '';
        $htmlGrandes = '';
        $contador = 0;
        // ordeno
        foreach ($arrFotos as $f) {


            $ppal = '';
            $ppalG = '';
            if ($f['foto_principal'] == 'Si') {
                $ppal .= '         <div class="img-box">' . PHP_EOL;
                $ppal .= '             <img title="' . $nombreArticulo . ' ' . $contador . ' miniatura" src="Articulo_Foto_Multi/' . $f['id_articulo_foto'] . '_1.jpeg">' . PHP_EOL;
                $ppal .= '         </div>' . PHP_EOL;

                $ppalG .= '        <a data-fancybox="images" href="Articulo_Foto_Multi/' . $f['id_articulo_foto'] . '_0.jpeg"><img title="' . $nombreArticulo . ' ' . $contador . '" alt="" src="Articulo_Foto_Multi/' . $f['id_articulo_foto'] . '_0.jpeg"></a>' . PHP_EOL;
            } 

                $htmlMiniaturas .= '         <div class="img-box">' . PHP_EOL;
                $htmlMiniaturas .= '             <img title="' . $nombreArticulo . ' ' . $contador . ' miniatura" src="Articulo_Foto_Multi/' . $f['id_articulo_foto'] . '_1.jpeg">' . PHP_EOL;
                $htmlMiniaturas .= '         </div>' . PHP_EOL;

                $htmlGrandes .= '        <a data-fancybox="images" href="Articulo_Foto_Multi/' . $f['id_articulo_foto'] . '_0.jpeg"><img title="' . $nombreArticulo . ' ' . $contador . '" alt="" src="Articulo_Foto_Multi/' . $f['id_articulo_foto'] . '_0.jpeg"></a>' . PHP_EOL;
                //$htmlGrandes .='        <a data-fancybox="images" href="foto-multi.php?id=' . $f['id_articulo_foto'] . '&mini=0"><img title="'.$nombreArticulo.' '.$contador.'" alt="" src="foto-multi.php?id=' . $f['id_articulo_foto'] . '&mini=0"></a>'.PHP_EOL;

            
            $contador++;
        }
        $htmlMiniaturas = $ppal . $htmlMiniaturas;
        $htmlGrandes = $ppalG . $htmlGrandes;

        # VIDEO como IMAGEN MINI.
        if ($linkVideo != '') {
            # miniatura video
            $htmlMiniaturas .= '         <div class="img-box">' . PHP_EOL;
            $htmlMiniaturas .= '             <img title="' . $nombreArticulo . ' ' . $contador . ' miniatura" src="images/video-2.jpg">' . PHP_EOL;
            $htmlMiniaturas .= '         </div>' . PHP_EOL;

            # video grande
            $htmlGrandes .= '<a data-fancybox="images" href="https://www.youtube.com/embed/' . $linkVideo . '">' . PHP_EOL;

            $htmlGrandes .= '<iframe width="100%" height="462" src="https://www.youtube.com/embed/' . $linkVideo . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>' . PHP_EOL;

            $htmlGrandes .= '</a>' . PHP_EOL;
        }


        $htmlF .= '<div class="col-2 col-lg-1 thum">' . PHP_EOL; // contenedeor miniaturas multi foto.
        $htmlF .= '     <div class="product-images">' . PHP_EOL;

        $htmlF .= $htmlMiniaturas . PHP_EOL;
        $htmlF .= '     </div>' . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL; // fin contenededor multifoto
        # imagenes grandes
        $htmlF .= '<div class="col-10 col-lg-7 img">' . PHP_EOL;

        $htmlF .= '     <div class="product-images">' . PHP_EOL;
        $htmlF .= '         <div class="slide-producto">' . PHP_EOL;
        $htmlF .= $htmlGrandes . PHP_EOL;
        $htmlF .= '         </div>' . PHP_EOL;
        $htmlF .= '     </div>' . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL;
    }

    # fin multi foto
    /* codigo una sola foto. VIEJO 
    $htmlF .= '<div class="col-md-6 img">';

    $htmlF .= '<div class="product-images">';

    $htmlF .= '<div class="primary targetarea diffheight">';

    ////$htmlF .= '<img class="custom" alt="zoomable" id="papito" src="foto.php?origen=foto1|' .$idArt . '&mini=0" >';
    $htmlF .= '<img class="custom" alt="zoomable" id="papito" src="Articulo_Foto/foto1_' . $idArt . '_0.jpeg" title="'.$nombreArticulo.'">';
    $htmlF .= '</div>';

    

    $htmlF .= '</div>'; // product-images
    $htmlF .= '</div>'; // img

 <div class="col-md-4 cont">
                                <!-- NUEVO - esta clase es nueva -->
                                <div class="product-content">

    <div class="precio-producto">26.990</div>
                                    <div class="descuento">10</div>
                                    <div class="marca-producto">MOTOROLA</div>
                                    <div class="precio-anterior-producto">184,46</div>
                                    <div class="cuotas-producto">12</div>
                                    <div class="codigo-producto">5183</div>
                                    <div class="texto-producto">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris et est libero. Suspendisse vitae commodo risus. Nunc at ante auctor, sollicitudin enim non, venenatis mauris. Ut vulputate vestibulum venenatis. Vestibulum dignissim congue feugiat. Proin consequat dui odio, non varius metus placerat at. Nam pulvinar commodo maximus. Donec ornare sem sit amet leo gravida viverra. </div>
                            

    */


    $htmlF .= '<div class="col-12 col-md-6 col-lg-4 cont">' . PHP_EOL;
    $htmlF .= '<div class="product-content">' . PHP_EOL;

    # compartir share
    $htmlF .= '<div class="share-producto">' . PHP_EOL;
    $htmlF .= '<a class="btn-share-producto" href="whatsapp://send?text=' . URL . '/' . $srcArticulo . '" data-action="share/whatsapp/share">' . PHP_EOL;
    $htmlF .= '<i class="fas fa-share-alt fa-fw fa-lg"></i>' . PHP_EOL;
    $htmlF .= '</a>' . PHP_EOL;
    $htmlF .= '</div>' . PHP_EOL;
    # whatsapp consultar
    $htmlF .= '<div class="whatsapp-producto">' . PHP_EOL;
    $htmlF .= '<a class="btn-whatsapp-producto" href="https://wa.me/' . NUMEROWHTSAPPARTICULO . '?text=' . URL . '/' . $srcArticulo . '%20::' . urlencode(' me interesa este producto ') . '">' . PHP_EOL;
    $htmlF .= '<i class="fab fa-whatsapp fa-fw fa-lg"></i>' . PHP_EOL;
    $htmlF .= '</a>' . PHP_EOL;
    $htmlF .= '</div>' . PHP_EOL;

    # nombre articulo
    $htmlF .= '<div class="nombre-producto"><h3>' . $nombreArticulo . '</h3></div>' . PHP_EOL;

    # bulto promedio
    if ($usaBultoPromedio == 'Si') {
        $bultoPromedio = round($promo->cantidad_promedio_bulto, 2);
        // tengo bulto promedio habilitado.
        if ($promo->cantidad_promedio_bulto > 0 && $promo->tipo_unidad == "Peso") {
            //$tBultoPromedio = "Venta por ". strtolower($promo->nombre_presentacion_vta) .", contiene: <span>".$bultoPromedio." ".$promo->uniArt." aproxim.</span>";
            $sufijoPrecio .= ' x ' . $promo->uniArt . '';
            $tBultoPromedio = "$" . $precioVentaFinalF . $sufijoPrecio . " - contiene: <span>" . $bultoPromedio . " " . $promo->uniArt . "</span>";
            # precio anterior.
            if ($precioVenta != $precioVentaFinal) {

                # mostrar precio con iva aun si es iva no incluido
                if ($ivaIncluido == 'No') {
                    $htmlF .= "<div class='precio-anterior-producto'>" . number_format(($precioVenta * $bultoPromedio), DECIMALES, ',', '.') . "</div>" . PHP_EOL;
                } else {

                    $htmlF .= "<div class='precio-anterior-producto'>" . number_format(($precioVenta * $bultoPromedio), DECIMALES, ',', '.') . "</div>" . PHP_EOL;
                }
            }


            # precio final

            $htmlF .= '<div class="precio-producto">' . number_format(($precioVentaFinal * $bultoPromedio), DECIMALES, ',', '.') . '</div>' . PHP_EOL;
            if($ivaIncluido=='No'){
            
                //$htmlF .= '<div class="precio-producto-iva">' . number_format(($importeIva*$bultoPromedio), DECIMALES, ',', '.').' Alic: ('.$alicuotaIva . '%)</div>' . PHP_EOL; // iva
                $htmlF .= '<div class="precio-producto-iva">IVA ('.$alicuotaIva . '%): U$S ' . number_format(($importeIva*$bultoPromedio), DECIMALES, ',', '.') . '</div>' . PHP_EOL;
                $htmlF .= '<div class="precio-producto-neto-iva">' . number_format(($precioVentaFinalIva*$bultoPromedio), DECIMALES, ',', '.') . $sufijoPrecio . '</div>' . PHP_EOL;// neto + iva
                
            }
        }
		
        // uso bulto promedio pero no tengo nada precio normal
        if ($promo->cantidad_promedio_bulto == 0 || $promo->tipo_unidad != "Peso") {
            # precio anterior.
            if ($precioVenta != $precioVentaFinal) {

                # mostrar precio con iva aun si es iva no incluido
                if ($ivaIncluido == 'No') {
                    $htmlF .= "<div class='precio-anterior-producto'>" . $precioVentaF . $sufijoPrecio . "</div>" . PHP_EOL;
                } else {

                    $htmlF .= "<div class='precio-anterior-producto'>" . $precioVentaF . $sufijoPrecio . "</div>" . PHP_EOL;
                }
            }


            # precio final

            $htmlF .= '<div class="precio-producto">' . $precioVentaFinalF . $sufijoPrecio . '</div>' . PHP_EOL;
            if($ivaIncluido=='No'){
            
                //$htmlF .= '<div class="precio-producto-iva">' . $precioIvaFinalF . ' Alic: ('.$alicuotaIva . '%)</div>' . PHP_EOL; // iva 
                $htmlF .= '<div class="precio-producto-iva"> IVA (' . $alicuotaIva . '%): U$S ' . $precioIvaFinalF . '</div>' . PHP_EOL; 
                $htmlF .= '<div class="precio-producto-neto-iva">' . $precioVentaFinalIvaF . $sufijoPrecio . '</div>' . PHP_EOL;// neto + iva
                
            }
        }
    }

    # Sin bulto promedio
    if ($usaBultoPromedio == 'No') {
        # precio anterior.
        if ($precioVenta != $precioVentaFinal) {

            # mostrar precio con iva aun si es iva no incluido
            // if ($ivaIncluido == 'No') {
            //     $htmlF .= "<div class='precio-anterior-producto'>" . $precioVentaF . $sufijoPrecio . "</div>" . PHP_EOL;
            // } else {

            //     $htmlF .= "<div class='precio-anterior-producto'>" . $precioVentaF . $sufijoPrecio . "</div>" . PHP_EOL;
            // }
            $htmlF .= "<div class='precio-anterior-producto'>" . $precioVentaF . $sufijoPrecio . "</div>" . PHP_EOL;

        }


        # precio final

        $htmlF .= '<div class="precio-producto">' . $precioVentaFinalF . $sufijoPrecio . '</div>' . PHP_EOL;
        // discriminar el precio neto.
        if($ivaIncluido=='No'){
            
            //$htmlF .= '<div class="precio-producto-iva">' . $precioIvaFinalF . ' Alic: ('.$alicuotaIva . '%)</div>' . PHP_EOL; // iva 
            $htmlF .= '<div class="precio-producto-iva"> IVA (' . $alicuotaIva . '%): U$S ' . $precioIvaFinalF . '</div>' . PHP_EOL; 
            $htmlF .= '<div class="precio-producto-neto-iva">' . $precioVentaFinalIvaF . $sufijoPrecio . '</div>' . PHP_EOL;// neto + iva
            
        }
    }

    # descuento
    if ($txtDescuento != '') {
        $htmlF .= '<div class="descuento-producto">' . $txtDescuento . '</div>' . PHP_EOL;
    }

    #iva 
    if ($ivaIncluido == 'Si') {
        $htmlF .= '<div class="iva-producto">IVA incluido</div>';
    } else {
        // $htmlF .= '<div class="iva-producto">Sin IVA</div>';
    }

    #cuotas

    # marca
    if ($promo->CodigoMarca <> 1) {
        $htmlF .= '<div class="marca-producto">' . $promo->Marca . '</div>' . PHP_EOL;
    }

    #codigo de producto
    $htmlF .= '<div class="codigo-producto">' . $codigoProducto . '</div>' . PHP_EOL;


    //if ($promo->Detalle != ""): sera detalle web :S
    if ($detalleProducto != "") :
        $htmlF .= '<div class="texto-producto">' . PHP_EOL;
        $htmlF .= '<p>' . $detalleProducto . '</p>' . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL;
    endif;

    # embalaje
    if ($usaEmbalaje == "Si") {
        $htmlF .= '<div class="embalaje-producto"> ' . $bulto . '</div>' . PHP_EOL;
    }

    # bulto promedio.
    if ($usaBultoPromedio == 'Si' && $bultoPromedio > 0 && $promo->tipo_unidad == "Peso") {
        $htmlF .= '<div class="bultopromedio-producto">' . $tBultoPromedio . '</div>' . PHP_EOL;
    }

    # stock 
    if ($verStock == "Si") {
        $htmlF .= '<div class="stock-producto"> ' . $tStock . '</div>' . PHP_EOL;
    }

    if ($textoPromo != "") :
        $htmlF .= $tagPromo;
        // $htmlF .= '<div class="short-description">'.PHP_EOL;
        $htmlF .= $textoPromo . PHP_EOL;
    //$htmlF .= '</div>'.PHP_EOL;

    endif;
    /*
    if (($videoProducto!=='')):
        $htmlF .= '<div class="videos-producto">'.PHP_EOL;
        $htmlF .= ' <div class="titulo-videos-producto">'.PHP_EOL;
        $htmlF .= ' <p><i class="fab fa-youtube fa-fw fa-lg"></i> Video </p><br>'.PHP_EOL;
        $htmlF .= ' </div>'.PHP_EOL;      
        $htmlF .= $videoProducto.PHP_EOL;   
        $htmlF .= '</div>'.PHP_EOL;
    endif;
*/
    // si no hay sesion de usuario no se puede comprar o bien si no hay permiso de mostrar precio.




    //$htmlF .= '<div class="precio-descuento">$ ' . $precioVentaFinalF . </div>';
    //$htmlF .=$txtDescuento;
    // valido stock que no se pueda comprar si no hay stock.
    //if($usaVenta=='Si'&&($ventaSinStock=='Si'|| ($ventaSinStock=='No'&&$saldoArticulo>0))){
    if ($mostrarControlesVenta == 0) {
        $htmlF .= '<div class="cantidad">' . PHP_EOL;
        $htmlF .= '<div class="input-group">' . PHP_EOL;
        $htmlF .= '<span class="input-group-btn">' . PHP_EOL;
        $htmlF .= '<button type="button" id="DisminuirCarrito' . $idArt . '" onclick="Disminuir(' . $idArt . ');" class="btn btn-default btn-number cantidad-carrito" data-type="minus" data-field="quant[1]">' . PHP_EOL;
        $htmlF .= '<span>-</span>' . PHP_EOL;
        $htmlF .= '</button>' . PHP_EOL;
        $htmlF .= '</span>' . PHP_EOL;
        $htmlF .= '<input id="CantidadID' . $idArt . '" type="text" name="quant[1]" max="' . $saldoArticulo . '" sinStock="' . $ventaSinStock . '"  class="form-control input-number" value="' . $cantidad . '">' . PHP_EOL;
        $htmlF .= '<span class="input-group-btn">' . PHP_EOL;
        $htmlF .= '<button type="button" id="AgregarCarrito' . $idArt . '" onclick="Aumentar(' . $idArt . ');" class="btn btn-default btn-number cantidad-carrito" data-type="plus" data-field="quant[1]">' . PHP_EOL;
        $htmlF .= '<span>+</span>' . PHP_EOL;
        $htmlF .= '</button>' . PHP_EOL;
        $htmlF .= '</span>' . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL;

        $htmlF .= '<div class="comprar">' . PHP_EOL;
        $htmlF .= '<button id="EnviarCarrito' . $idArt . '" type="button" onclick="EnviarCarrito(' . $idArt . ');" class="btn btn-default navbar-btn comprar-btn">Comprar ahora</button>' . PHP_EOL;

        $htmlF .= '<a href="javascript:void(0);" id="VerCarrito" onclick="verCarrito();"  class="btn btn-default navbar-btn carrito-btn">Ver Carrito</a>' . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL;
        
        if(file_exists('nv/plugin-layout/item-promocion-productos.php')) { require_once 'nv/plugin-layout/item-promocion-productos.php'; }
    }


    # BOTON DE CONSULTA / NO USO VENTA
    if ($usaVenta == 'No') {
        $htmlF .= '                <div class="consultar">' . PHP_EOL;
        $htmlF .= '                    <button id="ConsultarCarrito' . $idArt . '" onclick="location.href=\'https://wa.me/' . NUMEROWHTSAPPARTICULO . '?text=' . URL . '/' . $srcArticulo . '%20::' . urlencode(' me interesa este producto ') . '\'" type="button" class="btn btn-default navbar-btn comprar-btn">' . PHP_EOL;
        $htmlF .= '                        <i class="fab fa-whatsapp"></i>  Consultar' . PHP_EOL;
        $htmlF .= '                    </button>' . PHP_EOL;
        $htmlF .= '                </div>' . PHP_EOL;
    }


    $htmlF .= '</div>' . PHP_EOL; // product-content
    $htmlF .= '</div>' . PHP_EOL; //cont

    $htmlF .= '<div class="col-12 col-md-6 col-lg-8 bottom-datos-producto">' . PHP_EOL;

    # marca
    if ($promo->CodigoMarca <> 1) {
        $htmlF .= ' <div class="marca-producto">' . $promo->Marca . '</div>' . PHP_EOL;
    }

    if (($videoProducto !== '')) :
        $htmlF .= '<div class="videos-producto">' . PHP_EOL;
        $htmlF .= ' <div class="titulo-videos-producto">' . PHP_EOL;
        $htmlF .= ' <p><i class="fab fa-youtube fa-fw fa-lg"></i> Video </p><br>' . PHP_EOL;
        $htmlF .= ' </div>' . PHP_EOL;
        $htmlF .= $videoProducto . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL;
    endif;

    if ($detalleProducto != '') {
        $htmlF .= '     <div class="texto-producto">' . $detalleProducto . '</div>' . PHP_EOL;
    }

    $htmlF .= '</div>' . PHP_EOL; // fin div bottom datos producto

    $htmlF .= '</div>' . PHP_EOL; //row

    $htmlF .= '</div>' . PHP_EOL;
    $htmlF .= '</div>' . PHP_EOL;
    $htmlF .= '</div>' . PHP_EOL;

    $htmlF .= '</div>' . PHP_EOL;
    $htmlF .= '</section>' . PHP_EOL;






    return $htmlF;
}

/**
 * Funcion que trae el Producto sin el precio
 * -------------------------------------------
 * mostrar foto y demas caracteristicas.
 */
function producto_sin_precio($promo, $connV, $arrFotos)
{
    // echo "arti<pre>";
    //print_r($promo);
    //echo "</pre>";
    $descRenglon = 0;
    $codCliente = 1;
    $tStock = "";
    $precioNeto = 0;
    $importeIva = 0;
    $importeInterno = 0;
    $precioVenta = 0;
    $bulto = "";
    $nroListaPrecio = 'promocion_lista' . str_replace('Lista ', '', $_SESSION["lista_precio_defecto"]);
    $codigoProducto = '';
    $idArt = $promo->IDArt;
    $idManual = $promo->id_manual;
    $nombreArticulo = '';
    $promoDesde = $promo->promocion_vigencia_desde;
    $promoHasta = $promo->promocion_vigencia_hasta;
    $videoProducto = '';

    $tipoPromo = '';
    $linkAfuera = '';
    $detalleProducto = '';
    $propiedadesProducto = '';
    $usaBultoPromedio = $_SESSION["uso_bulto_promedio"];
    $verStock = $_SESSION["verStock"]; // mostrar stock 
    $ventaSinStock = $_SESSION["venta_sin_stock"]; // validar stock cero no mostrar controles.
    $ivaIncluido =   $_SESSION["ivaIncluido"];


    # hay un cliente seleccionado logueado
    if (isset($_SESSION['clienteDetalle']) && is_object($_SESSION['clienteDetalle'])) {
        $objCliente = $_SESSION['clienteDetalle'];
        $descRenglon = $objCliente->descRenglon;
        $codCliente = $objCliente->Codigo;
    }

    # codigo a mostrar de articulo
    if (CODIGOPRODUCTO == 'sistema') {
        $codigoProducto = $idArt;
    } else {
        $codigoProducto = $idManual;
    }

    $listaPrecioCliente =  $_SESSION["lista_precio_defecto"];


    # nombre de articulo    
    if ($promo->usa_nombre_articulo_ecom == 'Si') {
        $nombreArticulo = $promo->nombre_articulo_ecom;
    } else {
        $nombreArticulo = $promo->NombreArticulo;
    }
    $nombreArticulo = ucwords($nombreArticulo);
    # url
    $srcArticulo = urlAmigable($nombreArticulo, $idArt);
    # detalle del producto
    if ($promo->detalle_ecom != '') {
        $detalleProducto = nl2br($promo->detalle_ecom);
    } else {
        if ($promo->detalle_web != '') {
            $detalleProducto = nl2br($promo->detalle_web);
        }
    }

    #video
    if ($promo->link_video_articulo_ecom !== null && $promo->link_video_articulo_ecom !== '') {

        $videoProducto  .= '<div class="video-responsive">';
        $videoProducto  .= '     <iframe  src="' . $promo->link_video_articulo_ecom . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        //$videoProducto  .='     <iframe  src="https://www.youtube.com/embed/1UchhksqUgA" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';

        //" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        $videoProducto  .= '</div>';
    }

    #linkAfuera
    if ($promo->link_video_articulo_ecom !== null && $promo->link_video_articulo_ecom !== '') {
        $linkAfuera = $promo->link_articulo_ecom;
    }

    $usaReglaPrecio = $_SESSION['usaReglaPrecio'];
    $verStock = $_SESSION["verStock"];
    $ivaIncluido = $_SESSION["ivaIncluido"];


    # embalaje


    $usaEmbalaje = $_SESSION["utilizaEmbalaje"];




    if ($usaEmbalaje == "Si") {
        // tengo que hacer la busqueda de los valores para mostrar
        $bulto = $promo->nombre_presentacion . " x " . $promo->cantidad_uni;
        if ($promo->nombre_unimed != "") {
            $bulto .= " (" . $promo->nombre_unimed . ")";
        }
    }
    # bulto promedio
    if ($usaBultoPromedio == 'Si') {
        $bultoPromedio = round($promo->cantidad_promedio_bulto, 2);
        if ($promo->cantidad_promedio_bulto > 0 && $promo->tipo_unidad == "Peso") {
            //$tBultoPromedio = "Venta por ". strtolower($promo->nombre_presentacion_vta) .", contiene: <span>".$bultoPromedio." ".$promo->uniArt." aproxim.</span>";
            $tBultoPromedio = "Cada unidad contiene: <span>" . $bultoPromedio . " " . $promo->uniArt . " aprox.</span>";
            ///$textoPrecio = "precio por <span>".$promo->uniArt.'</span>';
        }
    }

    # stock


    // if ($_SESSION["verStock"] == "Si") {
    //     if ($promo->cantidad_promedio_bulto > 0 && $promo->tipo_unidad == "Peso") {
    //         $tStock .= " Stock: " . number_format(($promo->saldo / $promo->cantidad_promedio_bulto), 2, ',', '');
    //         $tStock .= ", Disp: " . number_format(($promo->disponible / $promo->cantidad_promedio_bulto), 2, ',', '');

    //         $tStock .= ", Pres: (" . $promo->cantidad_promedio_bulto . " " . $promo->uniArt . ")";
    //     } else {
    //         $tStock .= "Stock: " . $promo->saldo;
    //         $tStock .= ", Disp: " . $promo->disponible;
    //     }
    // }
    #stock visualiza y valida stock.
    if ($verStock == 'Si' || $ventaSinStock == 'No') {
        // buscar disponible 
        $stockDisponible = buscar_saldo_pendiente_unico($idArt, $promo);
        //$saldoArticulo = round($promo->saldo);
        $saldoArticulo = round($stockDisponible);
    }
    //echo "<pre>";
    //echo "stockDisponible:: ".$stockDisponible."<br>";
    //echo "SaldoPromo:: ".$promo->saldo."<br>";
    //echo "soyEcomm::".$promo->ecommerce."<br>";
    //echo "</pre>";

    # stock visualiza

    if ($verStock == "Si") {


        if ($saldoArticulo <= 0 || $promo->ecommerce == 'No') {
            //$mostrarControlesVenta++;
            $tStock .= "Stock: <span> Agotado.</span>";
        }

        if ($saldoArticulo > 0) {
            $tStock .= "Stock: <span>" . $saldoArticulo . " unidades.</span>";
            //$tStock .= ", Disp: " . $promo->disponible;
        }
    }


    $cantidad = 1;

    # promociones
    $usoPromo = "no";

    // si hay promo web tambien lo analizo.
    if (($promo->promocion == "Si" && $promo->$nroListaPrecio == "Si") || $promo->promo_solo_web == 'Si') {
        if ($promo->promo_solo_web == 'Si') {
            $promoTipo = 'soloWeb';
            $promoDesde = $promo->vigencia_desde_solo_web;
            $promoHasta = $promo->vigencia_hasta_solo_web;
            $txtDescuento =  $promo->descuento_solo_web;
        } else {
            $promoTipo = $promo->promocion_tipo;
            $txtDescuento = $promo->promocion_por;
        }

        $usoPromo = vigencia_promo($promoDesde, $promoHasta);
    }

    $tagPromo = "";

    $textoPromo = "";

    if ($usoPromo == "si") {
        $detallePromo = detalle_promo($promoTipo, $txtDescuento, $promo->promocion_cant, $idArt, $connV);
        if ($detallePromo != 'no') {
            $textoPromo .= '<div class="promocion promo-grilla" style="visibility:hidden;"><i class="fa fa-gift fa-lg fa-fw"></i> Promoción</div>' . PHP_EOL;

            $textoPromo .= '<div class="text-promo" style="visibility:hidden;">' . PHP_EOL;
            $textoPromo .= $detallePromo . PHP_EOL;
            $textoPromo .= vigencia_promo_detalle($promoDesde, $promoHasta) . PHP_EOL;

            $textoPromo .= '</div>' . PHP_EOL;
        }
    }


    $htmlF = '<section class="product-info">' . PHP_EOL;
    $htmlF .= '<div class="container">' . PHP_EOL;

    /*
     * Bread crumbs
     * ======================================================================
     */

    $arrBcrumb = array();
    $arrArg = array();
    //categoria
    $idCat = $promo->id_categoria;
    $categoria = $promo->NombCat;
    $idrubro = $promo->CodigoRubro;
    $rubro = $promo->NombRub;
    $idsubrubro = $promo->IDSubRubro;
    $subrubro = $promo->NombSubRub;
    // categoria
    $arrArg["idcategoria"] = $idCat;
    $arrArg["categoria"] = $categoria;
    $linkAnte = hacer_url("index.php", $arrArg);
    $arrBcrumb[] = '<a href="' . $linkAnte . '" class="breadcrumbs">' . $categoria . '<a/>';

    // rubro
    $arrArg["idrubro"] = $idrubro;
    $arrArg["rubro"] = $rubro;
    $linkAnte = hacer_url("index.php", $arrArg);
    $arrBcrumb[] = '<a href="' . $linkAnte . '" class="breadcrumbs">' . $rubro . '<a/>';
    //subrubro

    $arrArg["idsubrubro"] = $idsubrubro;
    $arrArg["subrubro"] = $subrubro;
    $arrArg["idmarca"] = null;
    $arrArg["marca"] = null;
    $arrArg["idtipocliente"] = null;
    $arrArg["tipocliente"] = null;

    $arrBcrumb[] = '<a tipo="subrubro" href="' . hacer_url("index.php", $arrArg) . '"  key="sr' . $idsubrubro . '" datos="' . $subrubro . '" id="' . $idsubrubro . '" class="breadcrumbs">' . $subrubro . '</a>';
    $breadCrumb = implode(" / ", $arrBcrumb);



    $htmlF .= '<div class="titulo-breadcrumbs">' . PHP_EOL;
    $htmlF .= '<div class="barra-breadcrumbs">' . PHP_EOL;
    $htmlF .= '<h4>' . PHP_EOL;
    $htmlF .= $breadCrumb . PHP_EOL;
    $htmlF .= '<h4>' . PHP_EOL;
    $htmlF .= '</div>' . PHP_EOL;
    $htmlF .= '</div>' . PHP_EOL;


    $htmlF .= '<div class="container">' . PHP_EOL;
    $htmlF .= '<div class="row justify-content-center">' . PHP_EOL;
    $htmlF .= '<div class="col-12 col-xl-10 producto">' . PHP_EOL;
    $htmlF .= '<div class="row">' . PHP_EOL;
    #nombre o titulo superior
    $htmlF .= '  <div class="col-12 top-nombre-producto">' . PHP_EOL;
    $htmlF .= '      <h3>' . $nombreArticulo . '</h3>' . PHP_EOL;
    $htmlF .= '   </div>' . PHP_EOL;

    # imagenes multifoto. 
    # miniaturas imagenes

    // no tengo fotos cargadas ponga unica foto el sin foto.
    if (empty($arrFotos)) {
        $htmlF .= '<div class="col-2 col-lg-1 thum">' . PHP_EOL; // contenedeor miniaturas multi foto.
        $htmlF .= '     <div class="product-images">' . PHP_EOL;
        $htmlF .= '         <div class="img-box">' . PHP_EOL;
        $htmlF .= '             <img src="Articulo_Foto/foto1_' . $idArt . '_1.jpeg" title="' . $nombreArticulo . '">' . PHP_EOL;
        $htmlF .= '         </div>' . PHP_EOL;
        $htmlF .= '     </div>' . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL; // fin contenededor multifoto
        # imagenes grandes
        $htmlF .= '<div class="col-10 col-lg-7 img">' . PHP_EOL;

        $htmlF .= '<div class="product-images">' . PHP_EOL;
        $htmlF .= '    <div class="slide-producto">' . PHP_EOL;
        $htmlF .= '        <a data-fancybox="images" href="Articulo_Foto/foto1_' . $idArt . '_0.jpeg"><img alt="" src="Articulo_Foto/foto1_' . $idArt . '_0.jpeg" title="' . $nombreArticulo . '"></a>' . PHP_EOL;
        $htmlF .= '    </div>' . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL;
    }
    // hay fotos mostrar lo que haya
    if (!empty($arrFotos)) {
        $htmlMiniaturas = '';
        $htmlGrandes = '';
        $contador = 0;
        // ordeno
        foreach ($arrFotos as $f) {


            $ppal = '';
            $ppalG = '';
            if ($f['foto_principal'] == 'Si') {
                $ppal .= '         <div class="img-box">' . PHP_EOL;
                $ppal .= '             <img title="' . $nombreArticulo . ' ' . $contador . ' miniatura" src="Articulo_Foto_Multi/' . $f['id_articulo_foto'] . '_1.jpeg">' . PHP_EOL;
                $ppal .= '         </div>' . PHP_EOL;

                $ppalG .= '        <a data-fancybox="images" href="Articulo_Foto_Multi/' . $f['id_articulo_foto'] . '_0.jpeg"><img title="' . $nombreArticulo . ' ' . $contador . '" alt="" src="Articulo_Foto_Multi/' . $f['id_articulo_foto'] . '_0.jpeg"></a>' . PHP_EOL;
            } 

                $htmlMiniaturas .= '         <div class="img-box">' . PHP_EOL;
                $htmlMiniaturas .= '             <img title="' . $nombreArticulo . ' ' . $contador . ' miniatura" src="Articulo_Foto_Multi/' . $f['id_articulo_foto'] . '_1.jpeg">' . PHP_EOL;
                $htmlMiniaturas .= '         </div>' . PHP_EOL;

                $htmlGrandes .= '        <a data-fancybox="images" href="Articulo_Foto_Multi/' . $f['id_articulo_foto'] . '_0.jpeg"><img title="' . $nombreArticulo . ' ' . $contador . '" alt="" src="Articulo_Foto_Multi/' . $f['id_articulo_foto'] . '_0.jpeg"></a>' . PHP_EOL;
            
            $contador++;
        }
        $htmlMiniaturas = $ppal . $htmlMiniaturas;
        $htmlGrandes = $ppalG . $htmlGrandes;

        $htmlF .= '<div class="col-2 col-lg-1 thum">' . PHP_EOL; // contenedeor miniaturas multi foto.
        $htmlF .= '     <div class="product-images">' . PHP_EOL;

        $htmlF .= $htmlMiniaturas . PHP_EOL;
        $htmlF .= '     </div>' . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL; // fin contenededor multifoto
        # imagenes grandes
        $htmlF .= '<div class="col-10 col-lg-7 img">' . PHP_EOL;

        $htmlF .= '     <div class="product-images">' . PHP_EOL;
        $htmlF .= '         <div class="slide-producto">' . PHP_EOL;
        $htmlF .= $htmlGrandes . PHP_EOL;
        $htmlF .= '         </div>' . PHP_EOL;
        $htmlF .= '     </div>' . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL;
    }
    $htmlF .= '<div class="col-12 col-md-6 col-lg-4 cont">' . PHP_EOL;
    $htmlF .= '<div class="product-content">' . PHP_EOL;

    # compartir
    $htmlF .= '<div class="share-producto">' . PHP_EOL;
    $htmlF .= '<a class="btn-share-producto" href="whatsapp://send?text=' . URL . '/' . $srcArticulo . '" data-action="share/whatsapp/share">' . PHP_EOL;
    $htmlF .= '<i class="fas fa-share-alt fa-fw fa-lg"></i>' . PHP_EOL;
    $htmlF .= '</a>' . PHP_EOL;
    $htmlF .= '</div>' . PHP_EOL;
    # whatsapp consultar
    $htmlF .= '<div class="whatsapp-producto">' . PHP_EOL;
    $htmlF .= '<a class="btn-whatsapp-producto" href="https://wa.me/' . NUMEROWHTSAPPARTICULO . '?text=' . URL . '/' . $srcArticulo . '%20::' . urlencode(' me interesa este producto ') . '">' . PHP_EOL;
    $htmlF .= '<i class="fab fa-whatsapp fa-fw fa-lg"></i>' . PHP_EOL;
    $htmlF .= '</a>' . PHP_EOL;
    $htmlF .= '</div>' . PHP_EOL;

    # nombre articulo
    $htmlF .= '<div class="nombre-producto"><h3>' . $nombreArticulo . '</h3></div>' . PHP_EOL;

    #cuotas

    # marca
    if ($promo->CodigoMarca <> 1) {
        $htmlF .= '<div class="marca-producto">' . $promo->Marca . '</div>' . PHP_EOL;
    }

    #codigo de producto
    $htmlF .= '<div class="codigo-producto">' . $codigoProducto . '</div>' . PHP_EOL;


    //if ($promo->Detalle != ""): sera detalle web :S
    if ($detalleProducto != "") :
        $htmlF .= '<div class="texto-producto">' . PHP_EOL;
        $htmlF .= ' <p>' . $detalleProducto . '</p>' . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL;
    endif;

    if ($usaEmbalaje == "Si") {
        $htmlF .= '<div class="descLista"><i class="fa fa-briefcase fa-lg fa-fw"></i> ' . $bulto . '</div>' . PHP_EOL;
    }

    # bulto promedio.
    if ($usaBultoPromedio == 'Si' && $bultoPromedio > 0) {
        $htmlF .= '<div class="bultopromedio">' . $tBultoPromedio . '</div>' . PHP_EOL;
    }

    if ($verStock == "Si") {
        $htmlF .= '<div class="descLista" style="visibility:hidden;"><i class="fa fa-briefcase fa-lg fa-fw"></i> ' . $tStock . '</div>' . PHP_EOL;
    }
    if ($textoPromo != "") :
        $htmlF .= $tagPromo;
        //$htmlF .= '<div class="texto-promocion">'.PHP_EOL;
        $htmlF .=  $textoPromo . PHP_EOL;
    //$htmlF .= '</div>'.PHP_EOL;

    endif;
    if (($videoProducto !== '')) :
        $htmlF .= '<div class="videos-producto">' . PHP_EOL;
        $htmlF .= ' <div class="titulo-videos-producto">' . PHP_EOL;
        $htmlF .= ' <p><i class="fab fa-youtube fa-fw fa-lg"></i> Video </p><br>' . PHP_EOL;
        $htmlF .= ' </div>' . PHP_EOL;
        $htmlF .= $videoProducto . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL;
    endif;


    $htmlF .= '<div class="cantidad" style="visibility:hidden;">' . PHP_EOL;
    $htmlF .= '<div class="input-group">' . PHP_EOL;
    $htmlF .= '<span class="input-group-btn">' . PHP_EOL;
    $htmlF .= '<button type="button" id="DisminuirCarrito' . $idArt . '" onclick="Disminuir(' . $idArt . ');" class="btn btn-default btn-number cantidad-carrito" data-type="minus" data-field="quant[1]">' . PHP_EOL;
    $htmlF .= '<span>-</span>' . PHP_EOL;
    $htmlF .= '</button>' . PHP_EOL;
    $htmlF .= '</span>' . PHP_EOL;
    $htmlF .= '<input id="CantidadID' . $idArt . '" type="text" name="quant[1]" class="form-control input-number" value="' . $cantidad . '">' . PHP_EOL;
    $htmlF .= '<span class="input-group-btn">' . PHP_EOL;
    $htmlF .= '<button type="button" id="AgregarCarrito' . $idArt . '" onclick="Aumentar(' . $idArt . ');" class="btn btn-default btn-number cantidad-carrito" data-type="plus" data-field="quant[1]">' . PHP_EOL;
    $htmlF .= '<span>+</span>' . PHP_EOL;
    $htmlF .= '</button>' . PHP_EOL;
    $htmlF .= '</span>' . PHP_EOL;
    $htmlF .= '</div>' . PHP_EOL;
    $htmlF .= '</div>' . PHP_EOL;

    $htmlF .= '<div class="comprar" style="visibility:hidden;">' . PHP_EOL;
    $htmlF .= '<button id="EnviarCarrito' . $idArt . '" type="button" onclick="EnviarCarrito(' . $idArt . ');" class="btn btn-default navbar-btn comprar-btn">Comprar ahora</button>' . PHP_EOL;

    $htmlF .= '<a href="javascript:void(0);" id="VerCarrito" onclick="verCarrito();"  class="btn btn-default navbar-btn carrito-btn">Ver Carrito</a>' . PHP_EOL;
    $htmlF .= '</div>' . PHP_EOL;



    $htmlF .= '</div>'; // product-content
    $htmlF .= '</div>'; //cont

    $htmlF .= '<div class="col-12 col-md-6 col-lg-8 bottom-datos-producto">' . PHP_EOL;

    # marca
    if ($promo->CodigoMarca <> 1) {
        $htmlF .= ' <div class="marca-producto">' . $promo->Marca . '</div>' . PHP_EOL;
    }

    if (($videoProducto !== '')) :
        $htmlF .= '<div class="videos-producto">' . PHP_EOL;
        $htmlF .= ' <div class="titulo-videos-producto">' . PHP_EOL;
        $htmlF .= ' <p><i class="fab fa-youtube fa-fw fa-lg"></i> Video </p><br>' . PHP_EOL;
        $htmlF .= ' </div>' . PHP_EOL;
        $htmlF .= $videoProducto . PHP_EOL;
        $htmlF .= '</div>' . PHP_EOL;
    endif;

    if ($detalleProducto != '') {
        $htmlF .= '     <div class="texto-producto">' . $detalleProducto . '</div>' . PHP_EOL;
    }
    $htmlF .= '</div>' . PHP_EOL; // fin div bottom datos producto
    $htmlF .= '</div>'; //row

    $htmlF .= '</div>';
    $htmlF .= '</div>';
    $htmlF .= '</div>';

    $htmlF .= '</div>';
    $htmlF .= '</section>';


    return $htmlF;
}

/**
 * Analiza la vigencia de las promos y evuelvo si esta dentro o no..
 */
function vigencia_promo($desde, $hasta)
{
    // hay un rango valido 
    //    echo "y las vigencias???<br>";
    //    echo var_dump($desde);
    //    echo "<br>";
    //    echo var_dump($hasta);
    //    echo "<pre>";
    $vigencia = "no";
    if ($desde !== null && $hasta !== null) {
        $fd = explode('-', $desde);
        $fh = explode('-', $hasta);

        if ($fh[0] > 2038) {
            $fh[0] = 2037;
        }
        //                            $desde  = mktime(0, 0, 0, $fd[1], $fd[2], $fd[0]);
        //                            $hasta  = mktime(0, 0, 0, $fh[1], $fh[2], $fh[0]);
        $desde = new DateTime($desde);
        $hasta = new DateTime($hasta);


        $hoy = new DateTime(date('Y-m-d'));
        //        echo "y las nueasvas???<br><pre>";
        //        echo var_dump($desde);
        //        echo "<br>";
        //        echo var_dump($hasta);
        //        echo "<br>";
        //        echo var_dump($hoy);
        //        echo "<br>";
        //        echo var_dump($hoy>=$desde && $hoy<=$hasta);
        //        echo "</pre>";
        // VIGENTE
        if ($hoy >= $desde && $hoy <= $hasta) {
            //echo "no entras";
            $vigencia = "si";
        }
    }
    // vigencia infinita
    if ($desde == null && $hasta == null) {
        $vigencia = "si";
    }

    // inicio infinito pero con fin
    if ($desde == null && $hasta !== null) {

        $fh = explode('-', $hasta);

        if ($fh[0] > 2038) {
            $fh[0] = 2037;
        }

        $hasta = new DateTime($hasta);

        $hoy = new DateTime(date('Y-m-d'));

        if ($hoy <= $hasta) {

            $vigencia = "si";
        }
    }

    // inicio desde peor sin fin o fin nulo
    if ($desde !== null && $hasta == null) {

        $fd = explode('-', $desde);
        $desde = new DateTime($desde);
        $hoy = new DateTime(date('Y-m-d'));

        if ($hoy >= $desde) {
            $vigencia = "si";
        }
    }
    // echo  "que deuvelvo.{$vigencia}<br>";

    return $vigencia;
}

function detalle_promo($tipoPromo, $descuento, $cantidad, $idArt, $connV, $tipoUnidad = null, $uMedida = null)
{
    $detalle = "";
    switch ($tipoPromo) {
        case 'Cantidad - Intervalo':
            $detalle = detalle_promo_intervalo($idArt, $connV);
            break;
        case 'Importe descuento':
            $detalle = '<p class="valor-descuento-detalle">' . round($descuento, 0) . '</p><br>';
            break;
        case 'Cantidad':
            // cantidad entera
            $nombreUnidadTexto = 'unidades';
            if ($tipoUnidad != null && $uMedida != null && $tipoUnidad == 'Peso') {
                $nombreUnidadTexto = $uMedida;
            }
            if (is_int($cantidad)) {
                $detalle = "<p>Llevando <strong>" . round($cantidad, 0) . "</strong> " . $nombreUnidadTexto . ", <strong>" . round($descuento, 0) . "% OFF</strong></p><br>";
            }
            // cantidad decimal
            if (!is_int($cantidad)) {
                $detalle = "<p>Llevando <strong>" . round($cantidad, 0) . "</strong> " . $nombreUnidadTexto . ", <strong>" . round($descuento, 0) . "% OFF</strong></p><br>";
            }

            break;
        case 'Cantidad - Unidad':
            $nombreUnidadTexto = 'unidades';
            $detalle = "<p>Llevando <strong>" . round($cantidad, 0) . "</strong> " . $nombreUnidadTexto . " , <strong >" . round($descuento, 0) . "</strong> un gratis (" . round($cantidad, 0) . " x " . round($descuento, 0) . ").</p><br>";
            break;
        case 'soloWeb':
            $detalle = '<p class="valor-descuento-detalle">' . round($descuento, 0) . '</p><br>';
            break;
    }
    //    echo "<detalle promo><pre>";
    //    echo $detalle;
    //    echo "<pre>";
    return $detalle;
}

/**
 * detalle promocion intervalo
 * No valido vigencia por intervarlo no lo hace administranet.
 */
function detalle_promo_intervalo($idArt, $connV)
{

    $hoy = date('Y-m-d');
    $link = $connV;
    //$db=mysqli_select_db($base, $link);
    //require_once 'sesion.inc.php';
    $sqlIntervalo = "SELECT pint.* "
        . "FROM articulo_promo_intervalo AS pint "
        . "WHERE "
        . "pint.id_articulo = {$idArt} And pint.anulado = 'No'"
        //. " AND '" . $hoy . "' BETWEEN pint.vigencia_desde AND pint.vigencia_hasta "
        . " ORDER BY pint.desde_cantidad ASC";
    $hacerInt = mysqli_query($link, $sqlIntervalo) or die("no pude recuperar la promocion." . mysqli_error($link) . "<pre>" . $sqlIntervalo . "</pre>");

    $arrDetalle = array();

    while ($pi = mysqli_fetch_assoc($hacerInt)) {
        $arrDetalle[] = $pi;
    }
    if (!empty($arrDetalle)) {
        $detallito = "<span>Por la compra de: <br>";
        foreach ($arrDetalle as $pi) {
            $uMedida = 'unidades';

            if ($pi['desde_cantidad'] == $pi['hasta_cantidad']) {
                if ($pi['desde_cantidad'] == 1) {
                    $uMedida = 'unidad';
                }
                $detallito .= "<p><i class='far fa-check-circle'></i>  <strong>" . round($pi["desde_cantidad"], 0) . '</strong> ' . $uMedida . ', <strong>' . round($pi["monto_descuento"], 0) . '% OFF</strong> </p><br>';
            }
            if ($pi['desde_cantidad'] != $pi['hasta_cantidad']) {

                $detallito .= "<p><i class='far fa-check-circle'></i>  <strong>" . round($pi["desde_cantidad"], 0) . '</strong> a <strong>' . round($pi["hasta_cantidad"], 0) . '</strong> ' . $uMedida . ', <strong>' . round($pi["monto_descuento"], 0) . '% OFF</strong></p><br>';
            }
        }
        $detallito .= "</span>";
    }


    if (empty($arrDetalle)) {
        $detallito = "no";
    }

    //mysqli_close($link);


    return $detallito;
}

function vigencia_promo_detalle($desde, $hasta)
{
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    setlocale(LC_TIME, 'spanish');
    if ($desde !== null && $hasta !== null) {
        $hastaM = strtotime('+3 month');
        //echo "hasta{".print_r(strtotime($hasta))."} hastam{ ".print_r($hastaM)."}";
        if (strtotime($hasta) < $hastaM) {
            $hastaTxt = utf8_encode(strftime("%A, %d de %B del %Y", strtotime($hasta)));
        } else {
            $hastaTxt = utf8_encode(strftime("%A, %d de %B del %Y", strtotime('+3 month')));
        }
        $desdeTxt = utf8_encode(strftime("%A, %d de %B del %Y", strtotime('now')));
    }

    if ($desde == null && $hasta == null) {
        $desdeTxt = utf8_encode(strftime("%A, %d de %B del %Y", strtotime('now')));
        $hastaTxt = utf8_encode(strftime("%A, %d de %B del %Y", strtotime('+3 month')));
    }
    if ($desde == null && $hasta !== null) {
        $hastaM = strtotime('+3 month');
        //echo "hasta{".print_r(strtotime($hasta))."} hastam{ ".print_r($hastaM)."}";
        if (strtotime($hasta) < $hastaM) {
            $hastaTxt = utf8_encode(strftime("%A, %d de %B del %Y", strtotime($hasta)));
        } else {
            $hastaTxt = utf8_encode(strftime("%A, %d de %B del %Y", strtotime('+3 month')));
        }
        $desdeTxt = utf8_encode(strftime("%A, %d de %B del %Y", strtotime('now')));
        //        $hastaTxt= utf8_encode(strftime("%A, %d de %B del %Y",strtotime($hasta)));
    }
    if ($desde !== null && $hasta == null) {
        $desdeTxt = utf8_encode(strftime("%A, %d de %B del %Y", strtotime($desde)));
        $hastaTxt = utf8_encode(strftime("%A, %d de %B del %Y", strtotime('+3 month')));
    }





    // inicio infinito pero con fin


    $textoFecha = "<p>Válida del " . $desdeTxt . " al " . $hastaTxt . "</p>";
    return $textoFecha;
}

/** 
 * DESTACADOS FUNCIONES 
 * =============================================================================================
 */


/**
 * Funcion para Productos Destacados
 * ---------------------------------
 * busco los productos destacados y los recupero.
 * @param link conexion a la base de datos local.
 * @return arrDestacados lista de articulos destacados a mostrar.
 * Productos por Categoria. por ahora consulta en cada cambio.
 * 
 */
function trae_productos_destacados($link)
{
    $idCategoria=null;    
    $filtroCategoria ="";
    $htmlVuelta = '';
    $campoReglaPrecio = "";
    $sqlReglaPrecio = "";
    $usoRegla = "No";
    $arrCateProductos = array();
    $f = $_SESSION["f"];

    if(isset($_GET['categoria'])&&$_GET['categoria']!=0){
        $idCategoria = $_GET["categoria"];
        $filtroCategoria .=" AND cat.id_categoria = ".$idCategoria." ".PHP_EOL;
    }
    # analisis reglas de precio
    if (isset($_SESSION["cliente"])) {
        //            echo "<br>cliente___::".print_r($_SESSION["cliente"]);
        $codCliente = $_SESSION["cliente"];
        $usoRegla = $_SESSION["usaReglaPrecio"];
    }
    if ($usoRegla == "Si" && $codCliente != null) {
        $campoReglaPrecio = "rp.tipo_calculo,rp.importe_regla,";
        $sqlReglaPrecio = "LEFT JOIN reglas_precio AS rp ON  
                        (rp.id_articulo = articulo.IDArt 
                        AND rp.id_cliente={$codCliente} 
                        AND  ('" . date('Y-m-d') . "' BETWEEN rp.vigencia_desde AND rp.vigencia_hasta) 
                        AND rp.anulado='No' )";
    }

    $filtroArticuloExterno = '';
    if (VALIDOECOMMEXTERNO == 'Si') {
        $filtroArticuloExterno .= " AND ecom.ecom_externo='Si' ";
    }

    $sqlDestacados = "SELECT 
                        articulo.id_manual,
                        marca.NombreMarca AS Marca,
                        modelo.NombreModelo AS Modelo,
                        articulo.IDArt,
                        articulo.detalle_web,
                        articulo.IDSubRubro, 
                        articulo.CodigoSubRubro,
                        articulo.CodigoRubro,
                        articulo.CodigoMarca,
                        cat.id_categoria,
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
                        articulo.tipoIVA,
                        iva.Alicuota AS Alic,
                        DATE_FORMAT(articulo.promocion_vigencia_hasta,'%W %d de %M del %Y') as fhastaT,
                        DATE_FORMAT(articulo.promocion_vigencia_desde,'%W %d de %M del %Y') as fdesdeT,                       
                        rubro.NombreRubro AS NombRub, 
                        subrubro.NombreSubRubro AS NombSubRub,
                        cat.nombre_categoria AS NombCat,
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
                        {$campoReglaPrecio}
                        articulo.impuesto_interno,    
                        articulo_prov.multiplicador_comp,
                        articulo_prov.cantidad_uni, 
                        articulo.CodigoProveedor,
                        unidmed.descrip_corta AS nombre_unimed,
                        presentacion_abm.nombre_presentacion, 
                        articulo_prov.id_presentacionC, 
                        articulo_prov.id_unimed,
                        articulo.cantidad_promedio_bulto,
                        mart.tipo_unidad,
                        mart.descrip_corta AS uniArt, 
                        presentacion_abmV.nombre_presentacion AS nombre_presentacion_vta, 
                        ecom.descuento_solo_web,
                        ecom.promo_solo_web,
                        ecom.vigencia_desde_solo_web,
                        ecom.vigencia_hasta_solo_web,
                        ecom.destacado_ecom,
                        ecom.detalle_ecom,
                        ecom.nombre_articulo_ecom,
                        ecom.link_articulo_ecom,
                        ecom.link_video_articulo_ecom,
                        ecom.garantia_articulo,
                        ecom.usa_nombre_articulo_ecom
                        FROM articulo
                            LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt AND articulo_prov.CodProveedor = articulo.CodigoProveedor)
                            LEFT JOIN unidmed ON (unidmed.id_unimed = articulo_prov.id_unimed) 
                            LEFT JOIN presentacion_abm ON (presentacion_abm.id_presentacion = articulo_prov.id_presentacionC)
                            LEFT JOIN unidmed AS mart ON (mart.id_unimed=articulo.id_unimed)
                            LEFT JOIN presentacion_abm AS presentacion_abmV ON (presentacion_abmV.id_presentacion = articulo.id_presentacionV) 
                            LEFT JOIN iva  ON articulo.Alicuota = iva.id 
                            LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
                            LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                            LEFT JOIN rubro_categoria AS cat ON cat.id_categoria = rubro.id_categoria
                            LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
                            LEFT JOIN marca ON marca.CodMarca = articulo.CodigoMarca
                            LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo = articulo.IDArt
                            {$sqlReglaPrecio} 
                        WHERE
                            articulo.promocion_destacado_web='Si'
                            AND cat.ecommerce='Si'
                            AND articulo.ecommerce='Si' 
                            {$filtroCategoria}
                            {$filtroArticuloExterno}                          
                        ORDER BY cat.id_categoria ASC ,articulo.NombreArticulo ASC;";

    $hacer = mysqli_query($link, $sqlDestacados) or die('No puedo recuperar los articulos destacados' . mysqli_error($link) . $sqlDestacados);
    $objDestacados = array();
    while ($art = mysqli_fetch_object($hacer)) {
        $objDestacados[] = $art;
    }

    # si hay destacados lo muestro si no devuelvo vacio.

    $numeroDeCategoria = array();

    foreach ($objDestacados as $obj) {
        array_push($numeroDeCategoria, $obj->id_categoria);
    };

    $numeroDeCategoria = array_unique($numeroDeCategoria);

    if (!empty($objDestacados)) {


        // destacados por categoria divididos

        if(defined('DESTACADOSPORCATEGORIA')&&DESTACADOSPORCATEGORIA=='Si'){
            $htmlVuelta = destacados_por_categoria($numeroDeCategoria,$objDestacados,$f,$link);
        }
        // destacados mezclados.
        if(!defined('DESTACADOSPORCATEGORIA')||(defined('DESTACADOSPORCATEGORIA')&&DESTACADOSPORCATEGORIA=='No')){

            $htmlVuelta = destacados_todos_juntos($objDestacados, $link);
        }
    } // fin si hay destacados

    return $htmlVuelta;
}

/**
 * funcion que arma destacados por Categoria 
 * @param numeroDeCategoria as array de categorias en destacados
 * @param objDestacados as array de productos destacados ordenados.
 * @param arrCate as array de las categorias con el nombre
 * @param link as mysql link  conexion a la base de datos.
 */
function destacados_por_categoria($numeroDeCategoria, $objDestacados, $arrCate, $link)
{
    $htmlVuelta = '';
    # inicio html seccion completa general.
    
    foreach ($numeroDeCategoria as $key) {

        $objDestacadosPorGrupo = array_filter($objDestacados, function ($objDestacados) use ($key) {
            return $objDestacados->id_categoria == $key;
        });

        $htmlVuelta .= '<!-- productos destacados -->' . PHP_EOL;
        $htmlVuelta .= '<section class="productos-destacados categoria-' . $key . '">' . PHP_EOL;
        $htmlVuelta .= '     <div class="container">' . PHP_EOL;
        $htmlVuelta .= '         <h3 class="carousel-en-linea">' . ucwords($arrCate["cate"][$key]['categoria']) . '</h3>' . PHP_EOL;
        $htmlVuelta .= '         <div class="row">' . PHP_EOL;

        foreach ($objDestacadosPorGrupo as $prod) {
            # destacado con precio
            if (isset($_SESSION["muestra_precio"]) && $_SESSION["muestra_precio"] == "Si") {
                $htmlVuelta .= producto_destacado_precio($prod, $link);
            }
            # destacado sin precio
            if (isset($_SESSION["muestra_precio"]) && $_SESSION["muestra_precio"] == "No") {
                $htmlVuelta .= producto_destacado_sin_precio($prod, $link);
            }
        } // fin for de articulos destacados

        $htmlVuelta .= '         </div>' . PHP_EOL; // fin div row
        $htmlVuelta .= '     </div>' . PHP_EOL; // fin div container
        $htmlVuelta .= '</section>' . PHP_EOL; //fin seccion destacados
        $htmlVuelta .= '<!-- /fin productos destacados seccion -->' . PHP_EOL;
    }

    return $htmlVuelta;
}


/**
 * funcion que trae destacados en un solo bloque
 * Antigua.
 * @param objDestacados as array de productos
 * @param link as mysql link de conexion 
 */
function destacados_todos_juntos($objDestacados, $link)
{
    $htmlVuelta = '';
    # inicio html seccion completa.
    $htmlVuelta .= '<!-- productos destacados -->' . PHP_EOL;
    $htmlVuelta .= '<section class="productos-destacados">' . PHP_EOL;
    $htmlVuelta .= '     <div class="container">' . PHP_EOL;
    $htmlVuelta .= '         <h3 class="carousel-en-linea">Productos Destacados</h3>' . PHP_EOL;
    $htmlVuelta .= '         <div class="row">' . PHP_EOL;

    foreach ($objDestacados as $prod) {
        # destacado con precio
        if (isset($_SESSION["muestra_precio"]) && $_SESSION["muestra_precio"] == "Si") {
            $htmlVuelta .= producto_destacado_precio($prod, $link);
        }
        # destacado sin precio
        if (isset($_SESSION["muestra_precio"]) && $_SESSION["muestra_precio"] == "No") {
            $htmlVuelta .= producto_destacado_sin_precio($prod, $link);
        }
    } // fin for de articulos destacados
    $htmlVuelta .= '         </div>' . PHP_EOL; // fin div row
    $htmlVuelta .= '     </div>' . PHP_EOL; // fin div container
    $htmlVuelta .= '</section>' . PHP_EOL; //fin seccion destacados
    $htmlVuelta .= '<!-- /fin productos destacados seccion -->' . PHP_EOL;
    return $htmlVuelta;
}

/**
 * Funcion PRODUCTO DESTACADO INDIVIDUAL el detalle con PRECIO.
 * ------------------------------------------------
 * detalle del producto completo Con precio y demas datos.
 */
function producto_destacado_precio($promo, $connV)
{

    $descRenglon = 0;
    $codCliente = 1;
    $tStock = "";
    $precioNeto = 0;
    $importeIva = 0;
    $importeInterno = 0;
    $precioVenta = 0;
    $bulto = "";
    $codigoProducto = '';
    $idArt = $promo->IDArt;
    $idManual = $promo->id_manual;
    $nombreArticulo = '';
    $promoDesde = $promo->promocion_vigencia_desde;
    $promoHasta = $promo->promocion_vigencia_hasta;
    # var HTML 
    $htmlDestacado = '';
    $fotoGrilla = '';
    $nombreGrilla = '';
    $precioGrilla = '';
    $precioProductoIva= '';
    $precioProductoNetoIva = '';
    $ivaProducto = '';
    $compartirProducto = '';
    $consultarProducto = '';
    $descuentoGrilla = '';
    $detallePrecioGrilla = '';
    $composicionBultoGrilla = '';
    $cuotasGrilla = '';
    $precioAnteriorGrilla = '';
    $marcaGrilla = '';
    $codigoGrilla = '';
    $promocionListon = '';
    $usaBultoPromedio = $_SESSION['uso_bulto_promedio'];
    $sufijoPrecio = '';

    # Inicio carga de datos
    # -----------------------------------------------------------------------------


    # hay un cliente seleccionado logueado
    if (isset($_SESSION['clienteDetalle']) && is_object($_SESSION['clienteDetalle'])) {
        $objCliente = $_SESSION['clienteDetalle'];
        $descRenglon = $objCliente->descRenglon;
        $codCliente = $objCliente->Codigo;
    }

    # nombre del articulo.
    if ($promo->usa_nombre_articulo_ecom == 'Si') {
        $nombreArticulo = $promo->nombre_articulo_ecom;
    } else {
        $nombreArticulo = $promo->NombreArticulo;
    }
    $nombreArticulo = ucwords($nombreArticulo);
    # url 
    $srcArticulo = 'articulo-descripcion.php?IDArt=' . $idArt . '&articulo=' . $nombreArticulo . '&id=' . $idArt;
    $srcArticulo = urlAmigable($nombreArticulo, $idArt);

    # codigo a mostrar de articulo
    if (CODIGOPRODUCTO == 'sistema') {
        $codigoProducto = $idArt;
    } else {
        $codigoProducto = $idManual;
    }

    $precioAnteriorGrilla = '<div class="precio-anterior-grilla" style="visibility: hidden;"></div>' . PHP_EOL;

    $listaPrecioCliente =  $_SESSION["lista_precio_defecto"];
    // es el descuento por cli del cilente


    $usaReglaPrecio = $_SESSION['usaReglaPrecio'];
    $verStock = $_SESSION["verStock"];
    $ivaIncluido = $_SESSION["ivaIncluido"];


    # precios
    $precios = calculaPrecios($connV, $promo, $listaPrecioCliente, $descRenglon, $usaReglaPrecio, $codCliente);

    //echo "<br>PROD--<pre>";
    //echo var_dump($promo, $listaPrecioCliente, $descRenglon, $usaReglaPrecio, $codCliente);
    //print_r($precios);
    //echo "----</pre>";

    $cantidad = $precios["cantidad"];
    $usoPromo = $precios["promo"];
    $tagPromo = "";

    $txtDescuento = "";
    $textoPromo = "";

    $importeInterno = $precios["importeInterno"];

    $importeIva = $precios["impIvaFinal"];
    $alicuotaIva = $precios["ivaAlic"];

    if ($ivaIncluido == 'No') {

        $precioViejo = $precios["neto"];
        $precioVentaFinal = $precios["netoCalc"];
        $precioVentaFinalIva = $precios["precioFinal"]; // debo mostrar el precio con iva aun no siendo incluido
        $precioVentaFinalIvaF = number_format($precioVentaFinalIva, DECIMALES, ',', '.'); // precio formateado con iva. sumado al final.

    } else {

        $precioViejo = $precios["precioVenta"];
        $precioVentaFinal = $precios["precioFinal"];
        $precioVentaFinalIva = $precios["precioFinal"]; // debo mostrar el precio con iva aun no siendo incluido
        $precioVentaFinalIvaF = number_format($precioVentaFinalIva, DECIMALES, ',', '.'); // precio formateado con iva.
    }

    $descFinal = $precios["descuento"];

    $precioViejoF = number_format($precioViejo, DECIMALES, ',', '.'); // precio viejo sin descuento
    $precioVentaFinalF = number_format($precioVentaFinal, DECIMALES, ',', '.'); // precio iva incluido con descuento. sin iva es el neto.
    $precioIvaFinalF = number_format($importeIva, DECIMALES, ',', '.'); // valor de iva.

    $descFinalF = number_format($descFinal, 0);
    $maxCant = "";
    $campoMax = "";




    # COMPARTIR SHARE PRODUCTO
    $compartirProducto .= '<!-- compartir -->' . PHP_EOL;
    $compartirProducto .= '<div class="share-producto" >' . PHP_EOL;
    $compartirProducto .= '    <a class="btn-share-producto" href="whatsapp://send?text=' . URL . '/' . $srcArticulo . '" data-action="share/whatsapp/share">' . PHP_EOL;
    $compartirProducto .= '        <i class="fas fa-share-alt fa-fw fa-lg"></i>' . PHP_EOL;
    $compartirProducto .= '    </a>' . PHP_EOL;
    $compartirProducto .= '</div>' . PHP_EOL;

    # CONSULTAR WP
    $consultarProducto .= '<!-- compartir -->' . PHP_EOL;
    $consultarProducto .= '<div class="whatsapp-producto">' . PHP_EOL;
    $consultarProducto .= '    <a class="btn-whatsapp-producto" href="https://wa.me/' . NUMEROWHTSAPPARTICULO . '?text=' . URL . '/' . $srcArticulo . '%20::' . urlencode(' me interesa este producto ') . '" >' . PHP_EOL;
    $consultarProducto .= '        <i class="fab fa-whatsapp fa-fw fa-lg"></i>' . PHP_EOL;
    $consultarProducto .= '    </a>' . PHP_EOL;
    $consultarProducto .= '</div>' . PHP_EOL;

    # DESCUENTOS
    $txtDescuento = '';
    $valorDescuento = '';
    $txtDetallePromo = '';
    # sin promocion evalua descuento final
    if ($usoPromo == 'No' || $usoPromo == "no") {

        #si hay descuento del cliente lo aplico
        if ($descFinal != 0) {
            $txtDescuento = $descFinalF;
        }
    }

    # hay promocion evaluar descuento. o cantidades.
    if ($usoPromo == 'si' || $usoPromo == "si") {
        // descuento solo web
        if ($precios["promoTipo"] == "soloWeb") {
            $valorDescuento = $descFinalF;
        }
        // promocion tipo descuento .
        if ($precios["promoTipo"] == "Importe descuento") {
            $valorDescuento = $descFinalF;
        }

        // cantidad unidad
        if ($precios["promoTipo"] == "Cantidad - Unidad") {
            // en teoria soy promocion descuento.
            // promocion cantidad unidad interval-Cantidad - Unidad
            $valorDescuento = '';
            $txtDetallePromo .= '(' . $precios['cantidad'] . 'x' . $precios['descuento'] . ')';
        }

        // tanto si es con precio como si no..  si es por cantidad , cantidad intervalo 
        if ($precios["promoTipo"] == "Cantidad") {
            // en teoria soy promocion descuento.
            // promocion cantidad unidad interval-Cantidad - Unidad
            $valorDescuento = round($descFinal, 0);
            $txtDetallePromo .= '' . $precios['cantidad'] . ', ' . $precios['descuento'] . '% off';
        }

        if ($promo->promocion_tipo == "Cantidad - Intervalo") {
            $valorDescuento = '';
            $txtDetallePromo .= '(*) en detalle';
        }
    }

    //detalle de la promo en destacados
    if ($txtDetallePromo != '') {
        $txtDescuento = '<div><i class="fa fa-gift fa-lg fa-fw"></i>' . $txtDetallePromo . '</div>' . PHP_EOL;
    }

    # LISTON
    if ($usoPromo == 'si' || $usoPromo == "si") {
        $promocionListon .= '<!-- liston de promocion -->' . PHP_EOL;
        $promocionListon .= '<div class="promo-esquina-grilla wrapper_pagination">' . PHP_EOL;
        $promocionListon .= '    <div class="lazo">' . PHP_EOL;
        $promocionListon .= '        <span>' . PHP_EOL;
        $promocionListon .= '            <i class="fa fa-gift fa-lg fa-fw"></i>' . PHP_EOL; //indicarmos que hay promocion sin poner porcentaje o nada.
        //$promocionListon .='            <strong>'.$valorDescuento.'</strong><i class="fas fa-percentage fa-lg fa-fw"></i>'.PHP_EOL; //indicarmos que hay promocion sin poner porcentaje o nada.
        $promocionListon .= '        </span>' . PHP_EOL;
        $promocionListon .= '    </div>' . PHP_EOL;
        $promocionListon .= '</div>' . PHP_EOL;
    }

    # bulto promedio
    $tBultoPromedio = "";
    if ($usaBultoPromedio == 'Si') {
        $bultoPromedio = round($promo->cantidad_promedio_bulto, 2);
        if ($promo->cantidad_promedio_bulto > 0 && $promo->tipo_unidad == "Peso") {
            //$tBultoPromedio = "Venta por ". strtolower($promo->nombre_presentacion_vta) .", contiene: <span>".$bultoPromedio." ".$promo->uniArt." aproxim.</span>";
            $tBultoPromedio = "Contiene: <span>" . $bultoPromedio . " " . $promo->uniArt . " ($" . number_format(($precioVentaFinal * $bultoPromedio), DECIMALES, ',', '.') . ") aprox.</span>";
            $sufijoPrecio = ' x ' . $promo->uniArt . '';
        }

        if($ivaIncluido=='No'){           
            //$precioProductoIva .= '<div class="precio-producto-iva">' . number_format(($importeIva*$bultoPromedio), DECIMALES, ',', '.').' Alic: ('.$alicuotaIva . '%)</div>' . PHP_EOL; // iva 
            $precioProductoIva .= '<div class="precio-producto-iva">IVA ('.$alicuotaIva . '%): U$S ' . number_format(($importeIva*$bultoPromedio), DECIMALES, ',', '.') . '</div>' . PHP_EOL; 
            $precioProductoNetoIva .= '<div class="precio-producto-neto-iva">' . number_format(($precioVentaFinalIva*$bultoPromedio), DECIMALES, ',', '.') . $sufijoPrecio . '</div>' . PHP_EOL;// neto + iva
    
        }
    }

    #No usa bulto promedio
    if ($usaBultoPromedio == 'No') {
        if($ivaIncluido=='No'){           
            //$precioProductoIva .= '<div class="precio-producto-iva">' . $precioIvaFinalF .' Alic: ('.$alicuotaIva . '%)</div>' . PHP_EOL; // iva 
            $precioProductoIva .= '<div class="precio-producto-iva"> IVA (' . $alicuotaIva . '%): U$S ' . $precioIvaFinalF . '</div>' . PHP_EOL; 
            $precioProductoNetoIva .= '<div class="precio-producto-neto-iva">' . $precioVentaFinalIvaF . $sufijoPrecio . '</div>' . PHP_EOL;// neto + iva

        }
    }

    # descuento con precios
    if ($valorDescuento != '') {
        $descuentoGrilla .= '<div class="descuento-grilla">' . $valorDescuento . '</div>' . PHP_EOL;
    }

    # precio viejo.
    if ($precioViejo != $precioVentaFinal) {
        $precioAnteriorGrilla = '<div class="precio-anterior-grilla">' . $precioViejoF . $sufijoPrecio . '</div>' . PHP_EOL;
    }

    # precio Final
    $precioGrilla .= '<div class="precio-grilla">' . $precioVentaFinalF . $sufijoPrecio . '</div>' . PHP_EOL;

    if ($ivaIncluido == 'Si') {
        $ivaProducto .= '<div class="iva-producto">c/IVA</div>';
    } else {
        //$ivaProducto .= '<div class="iva-producto">Sin IVA</div>';
    }

    # iniciando llenado de html

    # FOTO
    $fotoGrilla .= '<!--foto grilla-->' . PHP_EOL;
    $fotoGrilla .= '<div class="foto-producto">' . PHP_EOL;
    $fotoGrilla .= ' <img src="Articulo_Foto/foto1_' . $idArt . '_1.jpeg" title="' . $nombreArticulo . '" >' . PHP_EOL;
    $fotoGrilla .= '</div>' . PHP_EOL;
    $fotoGrilla .= '<!--fin foto grilla-->' . PHP_EOL;

    # NOMBRE
    $nombreGrilla .= '<div class="nombre-grilla">' . $nombreArticulo . '</div>' . PHP_EOL;

    # CODIGO
    $codigoGrilla .= '<div class="codigo-grilla">' . $codigoProducto . '</div>' . PHP_EOL;

    # MARCA 
    if ($promo->CodigoMarca != "1") {
        $marcaGrilla .= '<div class="marca-grilla">' . $promo->Marca . '</div>' . PHP_EOL;
    }


    # SIN BOTONES QUE VAYA A LA DESCRIPCION DIRECTO

    # caja html de producto
    $htmlDestacado .= '<!-- producto destacado -->' . PHP_EOL;
    $htmlDestacado .= '<div class="col-sm-6 col-md-4 col-lg-3 producto-destacado">' . PHP_EOL;
    $htmlDestacado .=   '<div class="contieneOferta card-producto">'; // contiene la tarjeta del producto
    $htmlDestacado .= $compartirProducto; //share link
    $htmlDestacado .= $consultarProducto; // compartir de whatsapp
    $htmlDestacado .= $promocionListon; // liston de promocion
    $htmlDestacado .= '      <!-- Area de producto -->' . PHP_EOL;
    $htmlDestacado .= '      <a href="' . $srcArticulo . '"  title="ver mas ' . $nombreArticulo . '">' . PHP_EOL; // link hacia el detalle del producto
    $htmlDestacado .= '          <div class="body-producto">' . PHP_EOL;
    $htmlDestacado .= $fotoGrilla; // la foto producto
    $htmlDestacado .= '              <div class="cuerpo-grilla">' . PHP_EOL; //opciones de producto.
    //$htmlDestacado .=$nombreGrilla;
    $htmlDestacado .= $precioGrilla;
    $htmlDestacado .= $descuentoGrilla;
    $htmlDestacado .= $ivaProducto;
    //$htmlDestacado .= $precioAnteriorGrilla;
    $htmlDestacado .= $precioProductoIva;
    $htmlDestacado .= $precioProductoNetoIva;
    //$htmlDestacado .=$detallePrecioGrilla;
    $htmlDestacado .= $nombreGrilla; // aca mostrar otro espacio.
    $htmlDestacado .= $cuotasGrilla;
    // $htmlDestacado .=$marcaGrilla;
    $htmlDestacado .= $codigoGrilla;
    $htmlDestacado .= $tBultoPromedio; // descripcion del precio promedio.
    //$htmlDestacado .=$txtDetallePromo;
    //$htmlDestacado .=$txtDescuento;
    $htmlDestacado .= '              </div>' . PHP_EOL; // fin cuerpo opciones
    $htmlDestacado .= '          </div>' . PHP_EOL; // fin body producto
    $htmlDestacado .= '      </a>' . PHP_EOL; // fin link que muestra detalle
    $htmlDestacado .= '<!-- /Area de producto -->' . PHP_EOL;
    //$htmlDestacado .=$controlesVentaGrilla;    
    $htmlDestacado .= '  </div>' . PHP_EOL; // fin div contiene oferta
    $htmlDestacado .= '</div>' . PHP_EOL; // fin div col-sm
    $htmlDestacado .= '<!-- / fin producto destacado -->' . PHP_EOL;

    # no voy a poner botones aca.

    return $htmlDestacado;
}

/**
 * Funcion que trae el Producto Destacado sin el precio
 * -------------------------------------------
 * mostrar foto y demas caracteristicas.
 */
function producto_destacado_sin_precio($promo, $connV)
{

    $descRenglon = 0;
    $codCliente = 1;
    $tStock = "";
    $precioNeto = 0;
    $importeIva = 0;
    $importeInterno = 0;
    $precioVenta = 0;
    $bulto = "";
    $codigoProducto = '';
    $idArt = $promo->IDArt;
    $idManual = $promo->id_manual;
    $nombreArticulo = '';
    $promoDesde = $promo->promocion_vigencia_desde;
    $promoHasta = $promo->promocion_vigencia_hasta;
    $nroListaPrecio = 'promocion_lista' . str_replace('Lista ', '', $_SESSION["lista_precio_defecto"]);
    $valorDescuento = '';
    # var HTML 
    $htmlDestacado = '';
    $fotoGrilla = '';
    $nombreGrilla = '';
    $precioGrilla = '';
    $compartirProducto = '';
    $consultarProducto = '';
    $descuentoGrilla = '';
    $cuotasGrilla = '';
    $precioAnteriorGrilla = '';
    $marcaGrilla = '';
    $codigoGrilla = '';
    $promocionListon = '';

    
    # hay un cliente seleccionado logueado
    if (isset($_SESSION['clienteDetalle']) && is_object($_SESSION['clienteDetalle'])) {
        $objCliente = $_SESSION['clienteDetalle'];
        $descRenglon = $objCliente->descRenglon;
        $codCliente = $objCliente->Codigo;
    }



    $listaPrecioCliente =  $_SESSION["lista_precio_defecto"];
    // es el descuento por cli del cilente

    # nombre del articulo.
    if ($promo->usa_nombre_articulo_ecom == 'Si') {
        $nombreArticulo = $promo->nombre_articulo_ecom;
    } else {
        $nombreArticulo = $promo->NombreArticulo;
    }
    $nombreArticulo = ucwords($nombreArticulo);
    # url
    $srcArticulo = urlAmigable($nombreArticulo, $idArt);
    
    # codigo a mostrar de articulo
    if (CODIGOPRODUCTO == 'sistema') {
        $codigoProducto = $idArt;
    } else {
        $codigoProducto = $idManual;
    }

    $usaReglaPrecio = $_SESSION['usaReglaPrecio'];
    $verStock = $_SESSION["verStock"];
    $ivaIncluido = $_SESSION["ivaIncluido"];


    # promocion
    $usoPromo = "no";

    // si hay promo web tambien lo analizo.
    if (($promo->promocion == "Si" && $promo->$nroListaPrecio == "Si") || $promo->promo_solo_web == 'Si') {
        if ($promo->promo_solo_web == 'Si') {
            $promoTipo = 'soloWeb';
            $promoDesde = $promo->vigencia_desde_solo_web;
            $promoHasta = $promo->vigencia_hasta_solo_web;
            $valorDescuento =  round($promo->descuento_solo_web, 0);
        } else {
            $promoTipo = $promo->promocion_tipo;
            $valorDescuento = round($promo->promocion_por, 0);
        }

        $usoPromo = vigencia_promo($promoDesde, $promoHasta);
    }



    # LISTON
    if ($usoPromo !== 'no') {
        $promocionListon .= '<!-- liston de promocion -->' . PHP_EOL;
        $promocionListon .= '<div class="promo-esquina-grilla wrapper_pagination" style="visibility:hidden;">' . PHP_EOL;
        $promocionListon .= '    <div class="lazo">' . PHP_EOL;
        $promocionListon .= '        <span>' . PHP_EOL;
        $promocionListon .= '            <i class="fas fa-percentage fa-lg fa-fw"></i> OFF' . PHP_EOL;
        $promocionListon .= '        </span>' . PHP_EOL;
        $promocionListon .= '    </div>' . PHP_EOL;
        $promocionListon .= '</div>' . PHP_EOL;
    }

    # COMPARTIR SHARE PRODUCTO
    $compartirProducto .= '<!-- compartir -->' . PHP_EOL;
    $compartirProducto .= '<div class="share-producto">' . PHP_EOL;
    $compartirProducto .= '    <a class="btn-share-producto" href="whatsapp://send?text=' . URL . '/' . $srcArticulo . '" data-action="share/whatsapp/share">' . PHP_EOL;
    $compartirProducto .= '        <i class="fas fa-share-alt fa-fw fa-lg"></i>' . PHP_EOL;
    $compartirProducto .= '    </a>' . PHP_EOL;
    $compartirProducto .= '</div>' . PHP_EOL;

    # CONSULTAR WP
    $consultarProducto .= '<!-- compartir -->' . PHP_EOL;
    $consultarProducto .= '<div class="whatsapp-producto">' . PHP_EOL;
    $consultarProducto .= '    <a class="btn-whatsapp-producto" href="https://wa.me/' . NUMEROWHTSAPPARTICULO . '?text=' . URL . '/' . $srcArticulo . '%20::' . urlencode(' me interesa este producto ') . '" >' . PHP_EOL;
    $consultarProducto .= '        <i class="fab fa-whatsapp fa-fw fa-lg"></i>' . PHP_EOL;
    $consultarProducto .= '    </a>' . PHP_EOL;
    $consultarProducto .= '</div>' . PHP_EOL;

    # DESCUENTOS
    $txtDescuento = '';
    //$valorDescuento='';
    # Promociones 
    if ($usoPromo == 'si') {
        // tanto si es con precio como si no..  si es por cantidad , cantidad intervalo 
        if ($promoTipo != "Cantidad - Intervalo" && $promoTipo != "Importe descuento" && $promoTipo != "soloWeb") {
            $valorDescuento = '*';
        }

        //if($promoTipo=="Importe descuento"){                                    
        // $txtDescuento=detalle_promo($promoTipo, $valorDescuento, $promo->promocion_cant, $idArt,$connV);   
        //  $valorDescuento=
        //}
        //if($promoTipo=="soloWeb"){                                    
        //$txtDescuento=detalle_promo($promoTipo, $valorDescuento, $promo->promocion_cant, $idArt,$connV);  
        //    valorDescuento 
        //}

        if ($promoTipo == "Cantidad - Intervalo") {
            //$txtDescuento = '*';
            $valorDescuento = '*';
        }
    }

    # descuento con precios
    if ($valorDescuento != '') {
        $descuentoGrilla = '<div class="descuento-grilla" style="visibility:hidden;">' . $valorDescuento . '</div>' . PHP_EOL;
    }

    # iniciando llenado de html

    # FOTO
    $fotoGrilla .= '<!--foto grilla-->' . PHP_EOL;
    $fotoGrilla .= '<div class="foto-producto">' . PHP_EOL;
    $fotoGrilla .= ' <img src="Articulo_Foto/foto1_' . $idArt . '_1.jpeg" title="' . $nombreArticulo . '" >' . PHP_EOL;
    $fotoGrilla .= '</div>' . PHP_EOL;
    $fotoGrilla .= '<!--fin foto grilla-->' . PHP_EOL;

    # NOMBRE
    $nombreGrilla .= '<div class="nombre-grilla">' . $nombreArticulo . '</div>' . PHP_EOL;

    # CODIGO
    $codigoGrilla .= '<div class="codigo-grilla">' . $codigoProducto . '</div>' . PHP_EOL;

    # MARCA 
    if ($promo->CodigoMarca != "1") {
        $marcaGrilla .= '<div class="marca-grilla">' . $promo->Marca . '</div>' . PHP_EOL;
    }


    # SIN BOTONES QUE VAYA A LA DESCRIPCION DIRECTO

    # caja html de producto
    $htmlDestacado .= '<!-- producto destacado -->' . PHP_EOL;
    $htmlDestacado .= '<div class="col-sm-6 col-md-4 col-lg-3 producto-destacado">' . PHP_EOL;
    $htmlDestacado .=   '<div class="contieneOferta card-producto">'; // contiene la tarjeta del producto
    $htmlDestacado .= $compartirProducto; //share link
    $htmlDestacado .= $consultarProducto; // compartir de whatsapp
    $htmlDestacado .= $promocionListon; // liston de promocion
    $htmlDestacado .= '      <!-- Area de producto -->' . PHP_EOL;
    $htmlDestacado .= '      <a href="' . $srcArticulo . '"  title="ver mas ' . $nombreArticulo . '">' . PHP_EOL; // link hacia el detalle del producto
    $htmlDestacado .= '          <div class="body-producto">' . PHP_EOL;
    $htmlDestacado .= $fotoGrilla; // la foto producto
    $htmlDestacado .= '              <div class="cuerpo-grilla">' . PHP_EOL; //opciones de producto.
    $htmlDestacado .= $nombreGrilla;
    #$htmlDestacado .=$precioGrilla;
    $htmlDestacado .= $descuentoGrilla;
    #$htmlDestacado .=$precioAnteriorGrilla;
    $htmlDestacado .= $cuotasGrilla;
    $htmlDestacado .= $marcaGrilla;
    $htmlDestacado .= $codigoGrilla;
    // $htmlDestacado .=$detallePromoGrilla;
    $htmlDestacado .= '              </div>' . PHP_EOL; // fin cuerpo opciones
    $htmlDestacado .= '          </div>' . PHP_EOL; // fin body producto
    $htmlDestacado .= '      </a>' . PHP_EOL; // fin link que muestra detalle
    $htmlDestacado .= '<!-- /Area de producto -->' . PHP_EOL;
    //$htmlDestacado .=$controlesVentaGrilla;    
    $htmlDestacado .= '  </div>' . PHP_EOL; // fin div contiene oferta
    $htmlDestacado .= '</div>' . PHP_EOL; // fin div col-sm
    $htmlDestacado .= '<!-- / fin producto destacado -->' . PHP_EOL;

    # no voy a poner botones aca.

    return $htmlDestacado;
}

/**
 * ===========================DESTACADOS FUNCIONES FIN====================================
 */

/**
 * Funcion que devuelve las compras validar si es con envio o con pago.
 */
function mis_compras($link, $cc)
{
    $cliente = $_SESSION["cliente"];
    $arrCompras = buscar_mis_compras($link, $cliente);
    $usaBultoPromedio = $_SESSION['uso_bulto_promedio'];
    //echo "<pre>";
    //echo print_r($arrCompras);
    //echo "</pre>";

    $html = '<section id="misCompras">';
    $html .= '   <div class="container">';
    $html .= '       <div class="compras">';
    $html .= '           <h2>Mis compras</h2>';
    $html .= '           <div class="row">';
    foreach ($arrCompras as $compra) {
        # la fecha.
        date_default_timezone_set('Europe/Madrid');
        setlocale(LC_TIME, 'es_ES.UTF-8');

        $dia = date("n", strtotime($compra['Fecha']));
        $mes = date("j", strtotime($compra['Fecha']));
        $year = date("Y", strtotime($compra['Fecha']));

        $fechaLinda = strftime("%#d de %B del %Y", mktime(0, 0, 0, $dia, $mes, $year));
        #armando el dato del envio EXTERNO
        //print_r($datosEnvio=json_decode($compra['json_envio'], true ));
        if (USAENVIO == 'Si') {
            $datosEnvio = json_decode($compra['json_envio'], true);
            /*
            [domicilio] => Array
            (
                [idDomicilio] => 11
                [codPostal] => 5516
                [domicilioTexto] => RAMON LISTA 2837 LUZURIAGA, MAIPU, Mendoza
            )

            [operador] => OCA
            [tipo] => Array
                (
                    [operativa] => 299014
                    [neto] => 228.0300
                    [dias] => 3
                    [importe] => 275.9163
                    [sucursal] => 25 DE MAYO 333 CNEL.DORREGO, MENDOZA
                    [idSucursal] => 742
                    [observaciones] => Antartida DOS
                )

            [neto] => 228.03
            [total] => 275.9163
            [tipo_envio] => Empresa de envios
            [empresa_envio_ecom] => OCA
            [id_transporte] => NULL
            [forma_envio_ml] => Retiro en sucursal
            [domicilio_texto_ml] => RAMON LISTA 2837 LUZURIAGA, MAIPU, Mendoza cp: 5516
            [ordenCompra] => 
            */

            // analizar el operador si es proio.. opciones.
            $divEnvio = ' <div id="dato-envio-' . $compra['codigo_movimiento_ped'] . '" style="display:none;">';
            $divEnvio .= '       <h5>Envio</h5>';
            $divEnvio .= '       <div><span>Seguimiento: </span>' . $compra['nro_transaccion_envio'] . '</div>';
            $divEnvio .= '       <div><span>Tipo:</span> ' . $datosEnvio['operador'] . ' ' . $datosEnvio['forma_envio_ml'] . '</div>';
            // envio a domicilio.
            if ($datosEnvio['tipo']['idSucursal'] == 0) {
                $divEnvio .= '       <div><span>Domicilio:</span> ' . $datosEnvio['domicilio_texto_ml'] . '</div>';
            }

            // retira sucursal
            if ($datosEnvio['tipo']['idSucursal'] != 0) {
                $divEnvio .= '       <div><span>Sucursal:</span> ' . $datosEnvio['tipo']['sucursal'] . '</div>';
            }
            if ($datosEnvio['tipo']['observaciones'] != "") {
                $divEnvio .= '       <div><span>Observaciones:</span> ' . $datosEnvio['tipo']['observaciones'] . '</div>';
            }
            $divEnvio .= '       <div><span>Importe:</span> $' . number_format($datosEnvio['total'], DECIMALES, ',', '.') . '</div>';
            $divEnvio .= '   </div>';
        }

        if (USAENVIO == 'No') {
            $datosEnvio = json_decode($compra['json_envio'], true);
            $divEnvio = ' <div id="dato-envio-' . $compra['codigo_movimiento_ped'] . '" style="display:none;">';
            $divEnvio .= '       <h5>Envio</h5>';
            $divEnvio .= '       <div><span>Seguimiento: </span>' . $compra['nro_transaccion_envio'] . '</div>';
            $divEnvio .= '       <div><span>Tipo:</span> ' . $datosEnvio['forma_envio_ml'] . '</div>';
            // envio a domicilio.
            if ($datosEnvio['tipo']['idSucursal'] == 0) {
                $divEnvio .= '       <div><span>Domicilio:</span> ' . $datosEnvio['domicilio_texto_ml'] . '</div>';
            }

            // retira sucursal
            if ($datosEnvio['tipo']['idSucursal'] != 0) {
                $divEnvio .= '       <div><span>Sucursal:</span> ' . $datosEnvio['tipo']['sucursal'] . '</div>';
            }

            if ($datosEnvio['tipo']['observaciones'] != "") {
                $divEnvio .= '       <div><span>Observaciones:</span> ' . $datosEnvio['tipo']['observaciones'] . '</div>';
            }
            if ($datosEnvio['total'] != 0) {
                $divEnvio .= '       <div><span>Importe:</span> $' . number_format($datosEnvio['total'], DECIMALES, ',', '.') . '</div>';
            }
            $divEnvio .= '   </div>';
        }

        # armando el div del pago    
        $divPago = '  <div id="dato-pago-' . $compra['codigo_movimiento_ped'] . '" style="display:none;">';
        if ($compra['estado_ecom_pedido'] == 'Pagado') {
            $divPago .= '        <h5>Pago</h5>';
            $divPago .= '        <div><span>Entidad:</span> ' . $compra['entidad_pago'] . '</div>';
            $divPago .= '        <div><span>Importe:</span> $' . number_format($compra['importe_tarjeta_mp'], DECIMALES, ',', '.') . '</div>';
            $divPago .= '        <div><span>Financiación:</span> ' . $compra['forma_pago_mp'] . ' <strong>' . $compra['datos_tarjeta_cobro'] . '</strong></div>';
        }
        $divPago .= '    </div>';

        # armando html de las compras.       
        $html .= '               <!-- col dentro de los row> -->';
        $html .= '               <div class="col-md-6 col-sm-12 col-center">';
        $html .= '                   <!-- icono en el h3 -->';
        //$html .='                   <h3><i class="fas fa-home"></i> #compra</h3>';
        if (MercadoPago == 'Si') {
            $html .= '                   <h4><strong>' . $fechaLinda . '</strong> <br>#' . $compra['NroComprobante'] . ' <span class="precio left"><strong>$' . number_format($compra['importe_tarjeta_mp'], DECIMALES, ',', '.') . '</strong></h4>';
        } else {
            $html .= '                   <h4><strong>' . $fechaLinda . '</strong> <br>#' . $compra['NroComprobante'] . ' <span class="precio left"><strong>$' . number_format($compra['ventaTotal'], DECIMALES, ',', '.') . '</strong></h4>';
        }
        foreach ($compra['productos'] as $prod) {
            //echo '<pre>'.print_r($prod).'</pre>';
            $html .= '                   <div>'; // contenedor de articulos            

            $html .= '                       <div class="card">';
            $html .= '                           <img class="card-img-left" src="foto.php?origen=foto1|' . $prod['IDArt'] . '&mini=2" alt="' . $prod['Descripcion'] . '">';
            $html .= '                           <div class="card-body">';
            $html .= '                               <h5 class="card-title">' . $prod['Descripcion'] . '</h5>';

            if (CODIGOPRODUCTO == 'manual') {
                $html .= '                               <p class="card-text"><strong>Id:</strong> ' . $prod['id_manual'] . '<br>';
            } else {
                $html .= '                               <p class="card-text"><strong>Id:</strong> ' . $prod['IDArt'] . '<br>';
            }

            if ($usaBultoPromedio == 'Si' && $prod['tipo_unidad'] == "Peso") {
                $html .= '                               <strong>Unidad:</strong> ' . $prod['cantBulto'] . '<br>';
                $html .= '                               cada unidad contiene ' . round($prod['cantidad_promedio_bulto'], 2) . ' ' . $prod['nombre_unimed'] . ' aprox.<br>';
                if ($prod['Cantidad'] == 1) {
                    $html .= '                               <span class="precio-grilla">' . number_format($prod['PrecioBrutoxU'], DECIMALES, ',', '.') . ' x ' . number_format($prod['Cantidad'], DECIMALES) . ' ' . $prod['nombre_unimed'] . '</span></p>';
                } else {
                    $html .= '                               <span class="precio-grilla">' . number_format($prod['PrecioBrutoxU'], DECIMALES, ',', '.') . ' x ' . number_format($prod['Cantidad'], DECIMALES) . '  ' . $prod['nombre_unimed'] . '</span><br>';
                    $html .= '                               <span class="precio-grilla">' . number_format($prod['PrecioBrutoxR'], DECIMALES, ',', '.') . '</span></p>';
                }
            } else {

                if ($prod['Cantidad'] == 1) {
                    $html .= '                               <span class="precio-grilla">' . number_format($prod['PrecioBrutoxU'], DECIMALES, ',', '.') . ' x ' . number_format($prod['Cantidad'], DECIMALES) . ' unidad </span></p>';
                } else {
                    $html .= '                               <span class="precio-grilla">' . number_format($prod['PrecioBrutoxU'], DECIMALES, ',', '.') . ' x ' . number_format($prod['Cantidad'], DECIMALES) . ' unidad </span><br>';
                    $html .= '                               <span class="precio-grilla">' . number_format($prod['PrecioBrutoxR'], DECIMALES, ',', '.') . '</span></p>';
                }
            }
            //$html .='                               <a href="#" class="btn btn-primary">Go somewhere</a>';
            $html .= '                           </div>';
            $html .= '                       </div>';

            $html .= '                   </div>'; // contenedor de fichas articulo 
        }
        // meter el envio

        $html .= $divEnvio;
        $html .= $divPago;
        $html .= '   <div><button class="btn btn-info"  id="boton-detalle-' . $compra['codigo_movimiento_ped'] . '" onclick="mostrarDetalleCompra(\'boton-detalle-' . $compra['codigo_movimiento_ped'] . '\',' . $compra['codigo_movimiento_ped'] . ')">Ver detalle</button></div>';
        // meter boton de detalle el div oculto y el boton que muestre su propio dato y listo. boton ocultar y mostrar dependiendo.
        $html .= '               </div>'; // contenedor de fichas compras
    }

    $html .= '           </div>'; // div row
    $html .= '       </div>'; //div clas compras
    $html .= '   </div>'; //div container

    $html .= '</section>'; // secion
    return $html;
}

function buscar_mis_compras($conexionCarrito, $cliente)
{
    # busco los pedidos
    $sqlCompras = "SELECT 	
                pd.NroComprobante,
                pd.Fecha,
                pd.importeVenta AS ventaTotal,
                pd.SubtotalDesc As neto,
                (pd.IVA1+pd.IVA2) AS ivaTotal,
                pdml.codigo_movimiento_ped,
                pdml.entidad_pago,
                pdml.estado_ecom_pedido,
                pdml.forma_pago_mp,
                pdml.datos_tarjeta_cobro,
                pdml.importe_total_pago_mp,
                pdml.tipo_envio,
                pdml.empresa_envio_ecom,
                pdml.forma_envio_ml,
                pdml.domicilio_texto_ml,
                pdml.nro_transaccion_envio,
                pdml.importe_envio,
                pdml.importe_tarjeta_mp,
                pdml.json_envio
                
                
            FROM  
            ecom_pedido AS pdml
            LEFT JOIN comp_ped AS pd ON pd.CodigoMovimiento=pdml.codigo_movimiento_ped
            WHERE 
            pd.Codigo=" . $cliente . "
            AND pdml.estado_ecom_pedido='Pagado'
            AND pd.Anulado='No'
            AND NOT ISNULL(pd.CodigoMovimiento)
            ORDER BY pd.Fecha DESC";
    $hacerC = mysqli_query($conexionCarrito, $sqlCompras);
    //echo mysqli_error($conexionCarrito);
    $arrCompras = array();
    while ($pdml = mysqli_fetch_assoc($hacerC)) {
        $arrCompras[$pdml["codigo_movimiento_ped"]] = $pdml;
        $arrCompras[$pdml["codigo_movimiento_ped"]]['productos'] = array();
    }

    # busco los productos consumidos
    $sqlProductos = "SELECT
                    stockp.CodigoMovimiento,
                    stockp.IDArt,
                    stockp.id_manual,
                    stockp.Descripcion,
                    stockp.Cantidad,
                    stockp.PrecioBrutoxU,
                    stockp.PrecioBrutoxR,
                    stockp.unidad_art_peso AS cantBulto,
                    articulo.cantidad_promedio_bulto,
                    unidmed.tipo_unidad,
                    unidmed.descrip_corta AS nombre_unimed
                    FROM
                    stockp 

                    LEFT JOIN articulo ON (articulo.IDArt=stockp.IDArt)
                    LEFT JOIN unidmed ON (unidmed.id_unimed = articulo.id_unimed) 
                    WHERE
                    stockp.Anulado='No'
                    AND stockp.CodigoCp=" . $cliente . "
                    ORDER BY stockp.CodigoMovimiento DESC, stockP.Fecha DESC";
    $hacerP = mysqli_query($conexionCarrito, $sqlProductos);
    while ($prod = mysqli_fetch_assoc($hacerP)) {
        if (key_exists($prod['CodigoMovimiento'], $arrCompras)) {
            $arrCompras[$prod["CodigoMovimiento"]]['productos'][] = $prod;
        }
    }

    return $arrCompras;
}

/**
 * Funcion que hace bonita la URL 
 */
//Limpia URL para colocarlas amigablemente
function urlAmigable($texto, $id)
{

    $texto = preg_replace("/Á/", "A", $texto);
    $texto = preg_replace("/á/", "a", $texto);
    $texto = preg_replace("/É/", "E", $texto);
    $texto = preg_replace("/é/", "e", $texto);
    $texto = preg_replace("/Í/", "I", $texto);
    $texto = preg_replace("/í/", "i", $texto);
    $texto = preg_replace("/Ó/", "O", $texto);
    $texto = preg_replace("/ó/", "o", $texto);
    $texto = preg_replace("/Ú/", "U", $texto);
    $texto = preg_replace("/ú/", "u", $texto);
    $texto = preg_replace("/Ñ/", "N", $texto);
    $texto = preg_replace("/ñ/", "n", $texto);

    $texto = preg_replace("/[\/\&%#\$]/", "_", $texto);
    $texto = preg_replace("/[\"\']/", "", $texto);
    $texto = str_replace("-", "_", $texto);
    $texto = str_replace(".", "", $texto);
    $texto = str_replace(" ", "_", $texto);
    //$texto = strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/','_', $texto));
    $texto = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $texto);
    $texto = str_replace("__", "_", $texto);

    $texto = trim($texto);
    $texto = substr($texto, 0, 101); //corto a los 101
    //$texto = str_replace("//", "", $texto);
    $urlVuelta = $texto . '-' . $id . '.shtml';

    return $urlVuelta;
}
