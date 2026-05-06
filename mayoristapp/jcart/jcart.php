<?php

// jCart v1.3
// http://conceptlogic.com/jcart/
//error_reporting(E_ALL);
// Cart logic based on Webforce Cart: http://www.webforcecart.com/
class Jcart
{

    public $config = array();
    private $items = array();
    private $names = array();
    private $idManual = array();
    private $prices = array();
    private $pricesN = array();
    private $tipoIva = array();
    private $impIva = array();
    private $iva = array();
    private $alicuota = array();
    private $impInterno = array();
    private $qtys = array();
    private $netos = array();
    private $netosN = array();
    private $descTotal = array();
    private $descPor = array();
    private $netoOld = array();
    private $urls = array();
    private $promo = array();
    private $promoCant = array();
    private $promoPorc = array();
    private $promoTipo = array();
    private $impInternoTasa = array();
    private $lote = array();
    private $idLote = array();
    private $nomLote = array();
    private $deposito = array();
    private $entregados = array();
    private $percepciones = array();
    // unidad display Bulto
    private $comoCuento = array();
    private $cantidadUnidadDisplay = array();
    private $divisorCantidad = array();
    private $cantidadMinimaContada = array();

    private $subtotal = 0;
    private $subtotalNeto = 0;
    private $subtotalNetoIva21 = 0;
    private $subtotalNetoIva105 = 0;
    private $subtotalIva21 = 0;
    private $subtotalIva105 = 0;
    private $subtotalImpInt = 0;
    private $subtotalExento = 0;
    private $percepcionesT = 0;
    // descuentos al pie
    private $porDescPie = 0;
    private $subtotalDesc21 = 0;
    private $subtotalDesc105 = 0;
    private $subtotalDesc = 0;
    private $importeDesc21 = 0;
    private $importeDesc105 = 0;
    // descuentos x condVenta
    private $porDesCondVta = 0;
    // datos del pedido cuando sea
    private $fechaTalonario = "";
    private $pventaTalonario = 0;
    private $nroTalonario = 0;
    private $itemCount = 0;

    function __construct()
    {

        // Get $config array
        include_once 'config-loader.php';
        $this->config = $config;
    }



    /**
     * Get cart contents
     *
     * @return array
     */
    public function get_contents()
    {
        $items = array();
        // echo "items en propiedad;<pre>";
        // print_r($this->items).PHP_EOL;
        // echo "</pre>".PHP_EOL;
        foreach ($this->items as $key => $tmpItemC) {
            $item = null;
            $tmpItem = $key;
            $item['key'] = $key;
            $item['id'] = $tmpItemC;
            $item['idManual']= $this->idManual[$tmpItem];
            $item['name'] = $this->names[$tmpItem];
            $item['price'] = $this->prices[$tmpItem];
            $item['priceN'] = $this->pricesN[$tmpItem];
            $item['qty'] = $this->qtys[$tmpItem];
            $item['url'] = $this->urls[$tmpItem];
            $item['tipoIva'] = $this->tipoIva[$tmpItem];
            $item['impIva'] = $this->impIva[$tmpItem];
            $item['iva'] = $this->iva[$tmpItem];
            $item['alicuota'] = $this->alicuota[$tmpItem];
            $item['impInterno'] = $this->impInterno[$tmpItem]; // contiene impuesto interno por el total del renglon.
            $item['neto'] = $this->netos[$tmpItem];
            $item['netoN'] = $this->netosN[$tmpItem];
            $item['descPor'] = $this->descPor[$tmpItem];
            $item['descTotal'] = $this->descTotal[$tmpItem];
            //            $item['netoOld']        = $this->netoOld[$tmpItem];
            $item['promo'] = $this->promo[$tmpItem];
            $item['promoCant'] = $this->promoCant[$tmpItem];
            $item['promoPorc'] = $this->promoPorc[$tmpItem];
            $item['promoTipo'] = $this->promoTipo[$tmpItem];
            $item['impInternoTasa'] = $this->impInternoTasa[$tmpItem];

            // unidad display bulto

            $item['comoCuento']             = $this->comoCuento[$tmpItem];
            $item['cantidadUnidadDisplay']  = $this->cantidadUnidadDisplay[$tmpItem];
            $item['divisorCantidad']        = $this->divisorCantidad[$tmpItem];
            $item['cantidadMinimaContada']  = $this->cantidadMinimaContada[$tmpItem];


            if (isset($this->idLote[$tmpItem])) {
                $item['idLote'] = $this->idLote[$tmpItem];
                $item['lote'] = $this->lote[$tmpItem];
                $item['nomLote'] = $this->nomLote[$tmpItem];
            }

            $item['deposito'] = $this->deposito[$tmpItem];
            $item['entregado'] = $this->entregados[$tmpItem];
            if (isset($this->percepciones[$tmpItem])) {
                $item['percepciones'] = $this->percepciones[$tmpItem];
            }
            $item['subtotalIva'] = $item['impIva'] * $item['qty'];
            $item['subtotalImpInt'] = $item['impInterno']; // ya viene calculado
            $item['subtotalExento'] = $item['priceN'] * $item['qty'];
            $item['subtotalNeto'] = $item['netoN'] * $item['qty'];
            $item['subtotal'] = $item['priceN'] * $item['qty'];
            if (isset($item['percepciones'])) {
                $item['percepcionesT'] = $item['percepciones'] * $item['qty'];
            }
            $item['porDescPie'] = $this->porDescPie;
            $items[] = $item;
        }
        //        echo "items en carrito;;<pre>";
        //        print_r($items);
        //        echo "</pre><br>";
        return $items;
    }

    /**
     * Add an item to the cart
     *
     * @param string $id
     * @param string $name
     * @param float $price
     * @param mixed $qty
     * @param string $url
     * 
     * Add por sti cuyo
     * 
     * @param string $tipoIva
     * @param float $impIva
     * @param string $alicuota
     * @param float $impInterno1676
     * $id, $name, $price, $qty = 1, $url, $iva, $tipoIva, $impIva, $alicuota, $impInterno, $neto, $descTotal = 0, $descPor = 0, $promo = null, $promoCant = null, $promoPorc = null, $promoTipo = null, $impInternoT, $lote = null, $arrIdLote = null, $nomLote = null, $deposito = null, $entregado = null
     * @return mixed
     */
    private function add_item(
        $id,
        $name,
        $price,
        $qty = 1,
        $url,
        $iva,
        $tipoIva,
        $impIva,
        $alicuota,
        $impInterno,
        $neto,
        $descTotal = 0,
        $descPor = 0,
        $promo = null,
        $promoCant = null,
        $promoPorc = null,
        $promoTipo = null,
        $impInternoT,
        $lote = null,
        $arrIdLote = null,
        $nomLote = null,
        $deposito = null,
        $entregado = null,
        $costo=null,
        $impInternoDescripcion = null,
        $impInternoTipo = null,
        $impInternoPorcentaje = null,
        $impInternoMontoFijo = null,
        $impInternoPesoCalculado = null,
        $impInternoPagoMinimo = null,
        $impInternoUnidMed = null,
        $comoCuento = 'Unidad',
        $cantidadUnidadDisplay = 1,
        $divisorCantidad = 1,
        $cantidadMinimaContada = 1,
        $idManual =''
    ) {

        $arrQueViene = func_get_args();

        //  echo 'func param<pre>'.PHP_EOL;
        //  print_r($arrQueViene).PHP_EOL;
        // echo 'internoDescripcion::',$impInternoDescripcion,PHP_EOL,
        // 'internoTipo::',$impInternoTipo,PHP_EOL,
        // 'internoPorcentaje::',$impInternoPorcentaje,PHP_EOL,
        // 'internoMontoFijo::',$impInternoMontoFijo,PHP_EOL,
        // 'internoPesoCalculado::',$impInternoPesoCalculado,PHP_EOL,
        // 'internoPagoMinimo::',$impInternoPagoMinimo,PHP_EOL,
        // 'internoUnidMed::',$impInternoUnidMed,PHP_EOL;
        //  echo '</pre>';

        $validPrice = true;
        $validQty = false;
        $validLimite = false;
        $price = str_replace(",", ".", $price);
        $netoN = $neto;
        $priceN = $price;
        $qtyPed = 0;
        $idPed = $id;
        //$cuantos=0;
        $cuantos = count($this->items);
        $limiteRenglon = $this->config["limiteRenglon"];
        //* divido el lote*/
        $saldoLote = $qty;
        $datoLote = explode("|", $arrIdLote);
        if (!empty($datoLote) && count($datoLote) == 2) {
            $idLote = $datoLote[0];
            $saldoLote = $datoLote[1];
        }
        
       
        /*
         * evaluar si hay remitido y que se sobre pasa del lote elegido x lo que hay que mandar a pedido lo que
          falta o sea la division. y ponerle valor no.
         */
        if ($entregado == "Si" && $qty > $saldoLote) {
            // voy a remitar y ademas tengo que entrego mas que el saldo lote.
            $qtyPed = $qty - $saldoLote;
            $qty = $saldoLote;
        }



        //        if ($_SESSION['limite_renglon'] > $cuantos) {
        //            $validLimite = true;
        //        }
        //        echo "que tengo:{<pre>";
        //        print_r($this);
        //        echo "}</pre>{";
        //        echo var_dump($cuantos);
        //        echo "</pre>";
        if ($limiteRenglon > $cuantos && is_numeric($limiteRenglon)) {
            $validLimite = true;
            $cuantos = count($this->items) + 1;
            $_SESSION["totalCarrito"] = $cuantos;
        }
        //        conservar el neto nuevo y despues practicar las promociones como un descuento comun
        // guardar la hora y la fech
        // limitar la cantidad de articulos.
        $descTotal = 0;

        // Verify the price is numeric
        //		if (is_numeric($price)) {
        //			$validPrice = true;
        //		}
        // If decimal quantities are enabled, verify the quantity is a positive float
        if ($this->config['decimalQtys'] === true && filter_var($qty, FILTER_VALIDATE_FLOAT) && $qty > 0) {
            $validQty = true;
        }
        // By default, verify the quantity is a positive integer
        elseif (filter_var($qty, FILTER_VALIDATE_INT) && $qty > 0) {
            $validQty = true;
        }

        // Add the item
        if ($validPrice !== false && $validQty !== false && $validLimite !== false) {



            if ($promoTipo == "Cantidad") {
                $promo = "si";
            }

            if ($promo == "si") {
                /* promo por Cantidad - intervalo 
                 * ==============================
                 */
                // las cantidades ahora son la cantidad minima contada.

                if ($promoTipo == 'Cantidad - Intervalo') {
                    // $pint = $this->promocion_art_cant_intervalo($id, $qty);
                    $pint = $this->promocion_art_cant_intervalo($id, $cantidadMinimaContada);


                    if (!empty($pint)) {
                        $descPor = $pint["monto_descuento"];
                        $promoCant = $pint["desde_cantidad"];
                    } else {
                        $descPor = 0;
                        $promoCant = 1;
                    }
                }
                /*
                 * Fin cantidad
                 */
                // if ($qty >= $promoCant) {
                if ($cantidadMinimaContada >= $promoCant) {

                    // la oferta es valida y hay que aplicarle el descuento.
                    // 
                    //neto
                    if ($promoTipo != 'Cantidad - Unidad') {

                        $descTotal = (($neto * $descPor) / 100);
                        $netoN = $neto - $descTotal;
                        //importe de iva
                        $impIva = (($netoN * $alicuota) / 100);
                        //impuesto interno
                        $impInterno = (($netoN * $impInternoT) / 100);
                        //precio de venta.
                        $priceN = $netoN + $impIva + $impInterno;
                    } else {

                        //hago la formula y debo duplicar esta entrada
                        $cuantosGratis = floor(($qty / $promoCant)) * $promoPorc;
                        $idCantidad = $id;
                        $priceN = $netoN + $impIva + $impInterno;
                    }
                    //neto nuevo

                    $nArticulo = explode('-(', $name);
                    //                            echo "<pre>con promo1-> conPromo->".print_r($nArticulo).'</pre>';
                    if (!empty($nArticulo)) {
                        $name = $nArticulo[0] . '-(promoción)';
                    } else {
                        $name = $name . '-(promoción)';
                    }
                } else {
                    $nArticulo = explode('-(', $name);
                    //echo "<pre>con promo1-> conPromo->" . print_r($nArticulo) . '</pre>';
                    if (!empty($nArticulo)) {
                        $name = $nArticulo[0];
                    } else {
                        $name = $name;
                    }
                    // en promocion pero no me alcanza la cantidad
                    //evaluamos los porcentajes

                    $priceN = $netoN + $impIva + $impInterno;
                }
            } else {

                //evaluamos los porcentajes
                $descTotal = (($neto * $descPor) / 100);
                $netoN = $neto - $descTotal;
                $impIva = (($netoN * $alicuota) / 100);
                $priceN = $netoN + $impIva + $impInterno;
            }

            // * calculo de impuesto interno Nuevo.
            if ($impInternoDescripcion != null) {
                // todo calcular el impuesto interno.
                $arrInterno = array();
                // $arrInterno['cantidad'] = $qty;
                $arrInterno['cantidad'] = $cantidadMinimaContada;
                $arrInterno['neto'] = $netoN;
                $arrInterno['costo'] = $costo;
                $arrInterno['descripcion'] = $impInternoDescripcion;
                $arrInterno['tipo'] = $impInternoTipo;
                $arrInterno['porcentaje'] = $impInternoPorcentaje;
                $arrInterno['montoFijo'] = $impInternoMontoFijo;
                $arrInterno['pesoCalculado'] = $impInternoPesoCalculado;
                $arrInterno['pagoMinimo'] = $impInternoPagoMinimo;
                $arrInterno['idUnimed'] = $impInternoUnidMed;

                $impInterno = $this->calcularImpuestoInterno($arrInterno); // funcion calcula nuevo impuesto interno
            }




            /*
             * genero array Pedido    
             */
            /*valido la url del bulto promedio para que no de error*/
            if (!is_numeric($url)) {
                $url = 1;
            }
            //            echo "que habia antes::=><pre>";
            //                print_r($this->items);
            //            echo "</pre><br>";
            $this->items[] = $id;
            end($this->items);
            //            echo "que habia desupes::=><pre>";
            //                print_r($this->items);
            //            echo "</pre><br>";
            $claveId = key($this->items);

            //            echo "que agrego id::{".$id."}<br>";
            //            echo "que claveid:={".$claveId.")<br>";


            $id = $claveId;
            $this->names[$id] = $name;
            $this->idManual[$id] = $idManual;
            $this->prices[$id] = $price;
            $this->pricesN[$id] = $priceN;
            $this->netos[$id] = $neto;
            $this->netosN[$id] = $netoN;
            $this->qtys[$id] = $qty;
            $this->descTotal[$id] = $descTotal;
            $this->descPor[$id] = $descPor;
            $this->urls[$id] = $url;
            $this->tipoIva[$id] = $tipoIva;
            $this->impIva[$id] = $impIva;
            $this->iva[$id] = $iva;
            $this->alicuota[$id] = $alicuota;
            $this->impInterno[$id] = $impInterno;
            $this->promo[$id] = $promo;
            $this->promoCant[$id] = $promoCant;
            $this->promoPorc[$id] = $promoPorc;
            $this->promoTipo[$id] = $promoTipo;
            $this->impInternoTasa[$id] = $impInternoT;
            $this->comoCuento[$id] = $comoCuento;
            $this->cantidadUnidadDisplay[$id] = $cantidadUnidadDisplay;
            $this->divisorCantidad[$id] = $divisorCantidad;
            $this->cantidadMinimaContada[$id] = $cantidadMinimaContada;
            // unidad display bulto.

            if (isset($idLote)) {
                $this->idLote[$id] = $idLote;
                $this->lote[$id] = $lote;
                $this->nomLote[$id] = $nomLote;
            }

            $this->deposito[$id] = $deposito;
            $this->entregados[$id] = $entregado;
            //$this->percepciones[$id]    = $montoPercepcion;
            /* test de */
            /* generar una resta porque si pongo REMITO entregado */
            if ($qtyPed != 0) {
                $this->items[] = $idPed;
                end($this->items);
                $claveId = key($this->items);
                $id = $claveId;
                $this->names[$id] = $name;
                $this->idManual[$id] = $idManual;
                $this->prices[$id] = $price;
                $this->pricesN[$id] = $priceN;
                $this->netos[$id] = $neto;
                $this->netosN[$id] = $netoN;
                $this->qtys[$id] = $qtyPed;
                $this->descTotal[$id] = $descTotal;
                $this->descPor[$id] = $descPor;
                $this->urls[$id] = $url;
                $this->tipoIva[$id] = $tipoIva;
                $this->impIva[$id] = $impIva;
                $this->iva[$id] = $iva;
                $this->alicuota[$id] = $alicuota;
                $this->impInterno[$id] = $impInterno;
                $this->promo[$id] = $promo;
                $this->promoCant[$id] = $promoCant;
                $this->promoPorc[$id] = $promoPorc;
                $this->promoTipo[$id] = $promoTipo;
                $this->impInternoTasa[$id] = $impInternoT;
                if (isset($idLote)) {
                    $this->idLote[$id] = $idLote;
                    $this->lote[$id] = $lote;
                    $this->nomLote[$id] = $nomLote;
                }
                $this->deposito[$id] = $deposito;
                $this->entregados[$id] = 'No';
            }

            /* */

            //                echo "<pre>";
            //                print_r($idCantidad);
            //                echo "</pre>";
            //promocion gratis
            if (isset($cuantosGratis) && $cuantosGratis != 0) {

                $id = $idCantidad . "p";
                $this->items[] = $id;
                end($this->items);
                $claveId = key($this->items);
                $id = $claveId;

                $this->names[$id] = $name;
                $this->idManual[$id] = $idManual;
                $this->prices[$id] = 0.00;
                $this->pricesN[$id] = 0;
                $this->netos[$id] = 0;
                $this->netosN[$id] = 0;
                $this->qtys[$id] = $cuantosGratis;
                $this->descTotal[$id] = 0;
                $this->descPor[$id] = 0;
                $this->urls[$id] = $url;
                $this->tipoIva[$id] = $tipoIva;
                $this->impIva[$id] = 0;
                $this->iva[$id] = $iva;
                $this->alicuota[$id] = $alicuota;
                $this->impInterno[$id] = 0;
                $this->promo[$id] = $promo;
                $this->promoCant[$id] = $promoCant;
                $this->promoPorc[$id] = $promoPorc;
                $this->promoTipo[$id] = $promoTipo;
                $this->impInternoTasa[$id] = $impInternoT;
                if (isset($idLote)) {
                    $this->idLote[$id] = $idLote;
                    $this->lote[$id] = $lote;
                    $this->nomLote[$id] = $nomLote;
                }
                $this->deposito[$id] = $deposito;
                $this->entregados[$id] = $entregado;
                $this->percepciones[$id] = 0;
                $this->impInternoTasa[$id] = $impInternoT;
                $this->comoCuento[$id] = $comoCuento;
                $this->cantidadUnidadDisplay[$id] = $cantidadUnidadDisplay;
                $this->divisorCantidad[$id] = $divisorCantidad;
                $this->cantidadMinimaContada[$id] = $cantidadMinimaContada;
            }

            $this->update_subtotal();
            return true;
        } elseif ($validPrice !== true) {
            $errorType = 'price';
            return $errorType;
        } elseif ($validQty !== true) {
            $errorType = 'qty';
            return $errorType;
        } elseif ($validLimite !== true) {
            $errorType = 'limite';
            return $errorType;
        }
    }

    /**
     * Devuelvo cantidad pedida del carrito
     */
    public function totalCarrito()
    {
        return count($this->items);
    }

    /**
     * Update an item in the cart
     *
     * @param string $id
     * @param mixed $qty
     *
     * @return boolean
     */
    private function update_item($id, $qty)
    {

        // If the quantity is zero, no futher validation is required
        if ((int) $qty === 0) {
            $validQty = true;
        }
        // If decimal quantities are enabled, verify it's a float
        elseif ($this->config['decimalQtys'] === true && filter_var($qty, FILTER_VALIDATE_FLOAT)) {
            $validQty = true;
        }
        // By default, verify the quantity is an integer
        elseif (filter_var($qty, FILTER_VALIDATE_INT)) {
            $validQty = true;
        }

        // If it's a valid quantity, remove or update as necessary
        if ($validQty === true) {
            if ($qty < 1) {
                $this->remove_item($id);
            } else {
                //                                echo '<pre>'. print_r($this) .'</pre>';
                $this->qtys[$id] = $qty;
                // recalcular las promociones.
                $neto = $this->netos[$id];
                $promo = $this->promo[$id];
                $descPor = $this->descPor[$id];
                $descTotal = $this->descTotal[$id];
                $alicuota = $this->alicuota[$id];
                $promoCant = $this->promoCant[$id];
                $promoPorc = $this->promoPorc[$id];
                $impInternoT = $this->impInternoTasa[$id];
                $netoN = $this->netosN[$id];
                $promoTipo = $this->promoTipo[$id];
                $impuestoInterno = $this->impInterno[$id];
                $name = $this->names[$id];
                $idManual = $this->idManual[$id];
                if ($promo == "si") {

                    //                    echo 'dentro promo';
                    if ($qty >= $promoCant) {
                        //                        echo 'promocant';
                        // la oferta es valida y hay que aplicarle el descuento.
                        //neto
                        if ($promoTipo != 'Cantidad - Unidad') {
                            $descTotal = (($neto * $promoPorc) / 100);
                            $netoN = $neto - (($neto * $promoPorc) / 100);
                            //importe de iva
                            $impIva = (($netoN * $alicuota) / 100);
                            //impuesto interno
                            // $impInterno = (($netoN * $impInternoT) / 100);
                            //precio de venta.
                            // $priceN = $netoN + $impIva + $impInterno;
                            $priceN = $netoN + $impIva;
                        } else {
                            //hago la formula y debo duplicar esta entrada
                            $cuantosGratis = floor(($qty / $promoCant)) * $promoPorc;
                        }
                        //neto nuevo
                        //                        $neto = $netoNuevo;
                        $nArticulo = explode('-(', $name);
                        //                        echo "<pre>con promo1-> conPromo->".print_r($nArticulo).'</pre>';
                        if (!empty($nArticulo)) {
                            $this->names[$id] = $nArticulo[0] . '-(promoción)';
                        } else {
                            $this->names[$id] = $name . '-(promoción)';
                        }
                        //modificarlo si existe y si no existe...agregarlo
                        if (isset($cuantosGratis) && $cuantosGratis > 0) {
                            $idP = $id . "p";
                            if ($this->qtys[$idP] != "") {
                                //existe el articulo en promocion lo actualizo
                                $this->qtys[$idP] = $cuantosGratis;
                            } else {
                                //no existe debo crearlo
                                //$idP .="p";
                                $this->items[] = $idP;
                                $this->names[$idP] = $this->names[$id];
                                $this->idManual[$id] = $idManual;
                                $this->prices[$idP] = 0;
                                $this->pricesN[$idP] = 0;
                                $this->netos[$idP] = 0;
                                $this->netosN[$idP] = 0;
                                $this->qtys[$idP] = $cuantosGratis;
                                $this->descTotal[$idP] = $this->descTotal[$id];
                                $this->descPor[$idP] = $this->descPor[$id];
                                $this->urls[$idP] = $this->urls[$id];
                                $this->tipoIva[$idP] = $this->tipoIva[$id];
                                $this->impIva[$idP] = 0;
                                $this->iva[$idP] = $this->iva[$id];
                                $this->alicuota[$idP] = $this->alicuota[$id];
                                $this->impInterno[$idP] = $this->impInterno[$id];
                                $this->promo[$idP] = $this->promo[$id];
                                $this->promoCant[$idP] = $this->promoCant[$id];
                                $this->promoPorc[$idP] = $this->promoPorc[$id];
                                $this->promoTipo[$idP] = $this->promoTipo[$id];
                                $this->impInternoTasa[$idP] = $this->impInternoTasa[$id];
                                $this->lote[$idP] = $this->lote[$id];
                                $this->idLote[$idP] = $this->idLote[$id];
                                $this->deposito[$idP] = $this->deposito[$id];
                                $this->entregados[$idP] = $this->entregados[$id];
                            }
                        }
                    } else {
                        $descTotal = 0;
                        $netoN = $neto;
                        //importe de iva
                        $impIva = (($netoN * $alicuota) / 100);
                        //impuesto interno
                        $impInterno = (($netoN * $impInternoT) / 100);
                        //precio de venta.
                        $priceN = $netoN + $impIva + $impInterno;
                        $nArticulo = explode('-(', $name);
                        //                        echo "<pre>con sin promo->".print_r($nArticulo).'</pre>';
                        $this->names[$id] = $nArticulo[0];
                    }
                } else {
                    //$descTotal = (($this->prices[$id] * $descPor) /100);
                    //$price =        $price - $descTotal;
                    $priceN = $this->pricesN[$id];
                    $netoN = $this->netosN[$id];
                    $impIva = $this->impIva[$id];

                    $impInterno = $this->impInterno[$id];
                    //                     $nArticulo = explode('-(',$name);
                    //                        echo "<pre>sin promo->".print_r($nArticulo).'</pre>';
                }
                //                echo  '<pre>'.$priceN .'</pre>';
                //                echo  '<pre>'.$netoN.'</pre>';
                //                echo  '<pre>'.$priceN.'</pre>';
                //                echo '<pre>'.$impIva.'</pre>';
                //                echo '<pre>'.$impInterno.'</pre>';
                $this->pricesN[$id] = $priceN;
                $this->netosN[$id] = $netoN;
                $this->prices[$id] = $priceN;
                $this->impIva[$id] = $impIva;
                $this->impInterno[$id] = $impInterno;
                $this->descTotal[$id] = $descTotal;
            }
            $this->update_subtotal();
            return true;
        }
    }

    /* Using post vars to remove items doesn't work because we have to pass the
      id of the item to be removed as the value of the button. If using an input
      with type submit, all browsers display the item id, instead of allowing for
      user-friendly text. If using an input with type image, IE does not submit
      the	value, only x and y coordinates where button was clicked. Can't use a
      hidden input either since the cart form has to encompass all items to
      recalculate	subtotal when a quantity is changed, which means there are
      multiple remove	buttons and no way to associate them with the correct
      hidden input. */

    /**
     * Reamove an item from the cart
     *
     * @param string $id	*
     */
    private function remove_item($id)
    {
        $tmpItems = array();

        unset($this->names[$id]);
        unset($this->prices[$id]);
        unset($this->pricesN[$id]);
        unset($this->qtys[$id]);
        unset($this->urls[$id]);
        unset($this->netos[$id]);
        unset($this->promo[$id]);
        unset($this->descPor[$id]);
        unset($this->descTotal[$id]);
        unset($this->alicuota[$id]);
        unset($this->promoCant[$id]);
        unset($this->promoPorc[$id]);
        unset($this->promoTipo[$id]);
        unset($this->impInternoTasa[$id]);
        unset($this->netosN[$id]);
        unset($this->netos[$id]);
        unset($this->tipoIva[$id]);
        unset($this->impIva[$id]);
        unset($this->iva[$id]);
        unset($this->impInterno[$id]);
        unset($this->promo[$id]);
        unset($this->impInternoTasa[$id]);
        unset($this->lote[$id]);
        unset($this->idLote[$id]);
        unset($this->deposito[$id]);
        unset($this->idManual[$id]);
        unset($this->entregados[$id]);

        //si hay promocion la reviento tambien.
        if (isset($this->names[$id . "p"]) && $this->names[$id . "p"] != '') {
            //hay promociones

            $idP = $id . "p";
            unset($this->names[$idP]);
            unset($this->prices[$idP]);
            unset($this->pricesN[$idP]);
            unset($this->qtys[$idP]);
            unset($this->urls[$idP]);
            unset($this->netos[$idP]);
            unset($this->promo[$idP]);
            unset($this->descPor[$idP]);
            unset($this->descTotal[$idP]);
            unset($this->alicuota[$idP]);
            unset($this->promoCant[$idP]);
            unset($this->promoPorc[$idP]);
            unset($this->promoTipo[$id]);
            unset($this->impInternoTasa[$idP]);
            unset($this->netosN[$idP]);
            unset($this->netos[$idP]);
            unset($this->tipoIva[$idP]);
            unset($this->impIva[$idP]);
            unset($this->iva[$idP]);
            unset($this->impInterno[$idP]);
            unset($this->promo[$idP]);
            unset($this->impInternoTasa[$idP]);
            unset($this->lote[$idP]);
            unset($this->idLote[$idP]);
            unset($this->deposito[$idP]);
            unset($this->entregados[$idP]);
            unset($this->idManual[$id]);

            foreach ($this->items as $key => $item) {
                if ($key != $id && $key != $idP) {
                    $tmpItems[$key] = $item;
                }
            }
        } else {
            foreach ($this->items as $key => $item) {
                if ($key != $id) {
                    $tmpItems[$key] = $item;
                }
            }
        }

        $this->items = $tmpItems;
        $this->update_subtotal();
        $_SESSION["totalCarrito"] = $_SESSION["totalCarrito"] - 1;
    }

    /**
     * Empty the cart
     */
    public function empty_cart()
    {
        //		$this->items     = array();
        //		$this->names     = array();
        //		$this->prices    = array();
        //		$this->qtys      = array();
        //		$this->urls      = array();
        //		$this->subtotal  = 0;
        //		$this->itemCount = 0;
        $this->items = array();
        $this->names = array();
        $this->prices = array();
        $this->idManual = array();
        $this->pricesN = array();
        $this->tipoIva = array();
        $this->impIva = array();
        $this->iva = array();
        $this->alicuota = array();
        $this->impInterno = array();
        $this->qtys = array();
        $this->netos = array();
        $this->descTotal = array();
        $this->descPor = array();
        $this->netosN = array();
        $this->urls = array();
        $this->promo = array();
        $this->promoCant = array();
        $this->promoPorc = array();
        $this->promoTipo = array();
        $this->impInternoTasa = array();
        $this->lote = array();
        $this->idLote = array();
        $this->deposito = array();
        $this->entregados = array();
        $this->percepciones = array();
        $this->subtotal = 0;
        $this->subtotalNeto = 0;
        $this->subtotalNetoIva21 = 0;
        $this->subtotalNetoIva105 = 0;
        $this->subtotalIva21 = 0;
        $this->subtotalIva105 = 0;
        $this->subtotalImpInt = 0;
        $this->subtotalExento = 0;
        $this->percepcionesT = 0;
        // descuentos al pie
        $this->porDescPie = 0;
        $this->subtotalDesc21 = 0;
        $this->subtotalDesc105 = 0;
        $this->subtotalDesc = 0;
        $this->importeDesc21 = 0;
        $this->importeDesc105 = 0;

        // descuentos x condVenta
        $this->porDesCondVta = 0;

        // datos del pedido cuando sea
        $this->fechaTalonario = "";
        $this->pventaTalonario = 0;
        $this->nroTalonario = 0;

        $this->itemCount = 0;

        $_SESSION["totalCarrito"] = 0;
    }

    /**
     * Update the entire cart
     */
    public function update_cart()
    {

        // Post value is an array of all item quantities in the cart
        // Treat array as a string for validation
        if (is_array($_POST['jcartItemQty'])) {
            $qtys = implode($_POST['jcartItemQty']);
        }

        // If no item ids, the cart is empty
        if ($_POST['jcartItemId']) {

            $validQtys = false;

            // If decimal quantities are enabled, verify the combined string only contain digits and decimal points
            if ($this->config['decimalQtys'] === true && preg_match("/^[0-9.]+$/i", $qtys)) {
                $validQtys = true;
            }
            // By default, verify the string only contains integers
            elseif (filter_var($qtys, FILTER_VALIDATE_INT) || $qtys == '') {
                $validQtys = true;
            }

            if ($validQtys === true) {

                // The item index
                $count = 0;

                // For each item in the cart, remove or update as necessary
                foreach ($_POST['jcartItemId'] as $id) {

                    $qty = $_POST['jcartItemQty'][$count];

                    if ($qty < 1) {
                        $this->remove_item($id);
                        //si hay promocion la borro...
                    } else {
                        $this->update_item($id, $qty);
                    }

                    // Increment index for the next item
                    $count++;
                }
                return true;
            }
        }
        // If no items in the cart, return true to prevent unnecssary error message
        elseif (!$_POST['jcartItemId']) {
            return true;
        }
    }

    /**
     * Recalculate subtotal
     */
    private function update_subtotal()
    {
        $this->itemCount = 0;
        $this->subtotal = 0;
        $this->subtotalNeto = 0;
        $this->subtotalNetoIva21 = 0;
        $this->subtotalNetoIva105 = 0;
        $this->subtotalExento = 0;
        $this->subtotalImpInt = 0;
        $this->subtotalIva105 = 0;
        $this->subtotalIva21 = 0;
        $this->importeDesc21 = 0;
        $this->importeDesc105 = 0;
        $this->subtotalDesc = 0;
        $this->subtotalDesc21 = 0;
        $this->subtotalDesc105 = 0;
        $this->subtotalDesc = 0;
        $this->percepcionesT = 0;

        $totalNetoPer = 0;
        $this->percepciones = array();
        /*
         * Cliente
         */
        if (is_object($_SESSION['cliente'])) {
            $cliente = $_SESSION['cliente'];
        } else {
            $cliente = $_SESSION['cliente'][0];
        }

        /*
         * Descuento al pie
         */
        //        if($cliente->descPie>0){
        //            /*
        //             * el cliente tiene un descuento al pie para aplicar me lo tomo
        //             */
        //            $this->porDescPie = $cliente->descPie;
        //            
        //        
        //        }

        /*
         * El cliente no tiene descuento al pie para aplicar entonces queda en cero
         * salvo que le permita al vendedor hacer un descuento de a cuerdo al
         * permiso de modificar el descuento al pie.
         */

        //echo "que onda{".var_dump(count($a=$this->items))."}";
        if (count($a = $this->items) > 0) {
            foreach ($this->items as $key => $itemc) {
                $item = $key;
                /*
                 * Debo recalcular el neto por el descuento al pie 
                 * debo recuperar alicuota y recalcular todo.
                 */
                $netoTotal = $this->netosN[$item];
                $netoTotalDesc = $this->netosN[$item];
                $alicuota = $this->alicuota[$item];
                $tasaInt = $this->impInternoTasa[$item];
                $impuestoInterno = $this->impInterno[$item];

                if ($this->porDescPie > 0) {
                    /*
                     * Cambio el neto y debo hacer el recalculo del descuento pie
                     */
                    if ($this->entregados[$item] != "Si") {
                        $netoTotalDesc = $netoTotal - ($netoTotal * $this->porDescPie / 100);
                    }
                }
                /*
                 * Precio de Venta con impuesto 
                 */
                $precioTotal = $netoTotalDesc + ($netoTotalDesc * $alicuota / 100);
                /*
                 * Impuesto Iva recalculado.
                 */
                $impIva = $netoTotalDesc * $alicuota / 100;

                // impuesto interno se suma de los productos.
                $impInt = 0;
                //$impInt = $netoTotalDesc * $tasaInt / 100;
                /*
                 * Calculo subtotales....
                 */
                $this->subtotalNeto += ($this->qtys[$item] * $netoTotal);
                $this->subtotalDesc += ($this->qtys[$item] * $netoTotalDesc);
                $this->subtotal += ($this->qtys[$item] * $precioTotal);


                /*
                 * Aplico Percepciones 
                 */
                if ($this->entregados[$item] !== "Si") {
                    $totalNetoPer += ($this->qtys[$item] * $netoTotalDesc);
                }

                if ($this->tipoIva[$item] == 'Exento') {
                    $this->subtotalExento += ($this->qtys[$item] * $netoTotalDesc);
                } else {
                    if ($this->iva[$item] == 1) {
                        $this->subtotalIva21 += ($this->qtys[$item] * $impIva);
                        $this->subtotalNetoIva21 += ($this->qtys[$item] * $netoTotal);
                        $this->subtotalDesc21 += ($this->qtys[$item] * $netoTotalDesc);
                        $this->importeDesc21 += ($this->qtys[$item] * ($netoTotal - $netoTotalDesc));
                    }
                    if ($this->iva[$item] == 2) {
                        $this->subtotalIva105 += ($this->qtys[$item] * $impIva);
                        $this->subtotalNetoIva105 += ($this->qtys[$item] * $netoTotal);
                        $this->subtotalDesc105 += ($this->qtys[$item] * $netoTotalDesc);
                        $this->importeDesc105 += ($this->qtys[$item] * ($netoTotal - $netoTotalDesc));
                    }
                    // $this->subtotalImpInt += ($this->qtys[$item] * $impInt);
                    $this->subtotalImpInt += $impuestoInterno;
                }

                // $this->subtotal += $this->subtotalImpInt;
                //$this->subtotalNeto += ($this->qtys[$item] * ($this->prices[$item]- $this->impInterno[$item]));
                // Total number of items
                $this->itemCount += $this->qtys[$item];
            }
            //                        aplico descuento al pie si lo tiene
        }
        /*
         * PERCEPCIONES 
         */


        $montoPercepcion = 0;
        require_once '../sesion.inc.php';
        if ($_SESSION["tipousuario"] == "cliente") {
            require_once '../conexion.inc.php';
        } else {
            require_once '../conexion-vendedor-empresa.inc.php';
        }
        if ($_SESSION['agente_percep'] == 'Si') {

            $sqlPercepCliParam = "SELECT * FROM "
                . "percep_cli_param "
                . "WHERE id_cliente = '" . $cliente->Codigo . "'";

            $res = mysqli_query($connV, $sqlPercepCliParam) or die('No puedo recuperar las percepciones del cliente' . mysqli_error($connV) . '<br>' . $sqlPercepCliParam);
            $totalRecep = mysqli_num_rows($res);
            if ($totalRecep === 0) {
                //no hay configuradas percepciones del cliente 

                $errorType = 'percepcion';
                return $errorType;
            }
            //            si es la primera vez que cargo percepciones, configuro el array, sino
            //           me lo traigo del objeto

            if (empty($this->percepciones)) {
                $arrPercep = array('totalP' => 0, 'detalle' => array());
            } else {
                $arrPercep = $this->percepciones;
            }

            while ($perC = mysqli_fetch_assoc($res)) {
                // tipo de percepciones
                //======================
                $montoPercepcionCalculo = 0;
                $sqlPercepCliTipo = "SELECT * FROM "
                    . "percep_cli_tipo "
                    . "WHERE "
                    . "id_percep_cli_tipo = " . $perC['id_percep_cli_tipo'];

                $resT = mysqli_query($connV, $sqlPercepCliTipo) or die("No puedo recuperar el percep tipo" . mysqli_error($connV));
                $recTipo = mysqli_fetch_assoc($resT);

                //                        echo "<pre>PP::";
                //                        print_r($recTipo);
                //                        echo "</pre>";
                if (!empty($recTipo)) {
                    $idTipo = $recTipo["id_percep_cli_tipo"];
                    $aliTipo = $recTipo["alicuota_percep_cli_tipo"];
                    $afipTipo = $recTipo["cod_afip"];
                    $nomTipo = $recTipo["nombre_percep_cli_tipo"];

                    $montoPercepcionCalculo = ($totalNetoPer * $recTipo["alicuota_percep_cli_tipo"] / 100);
                    if (empty($arrPercep['detalle'][$idTipo])) {
                        $arrPercep['detalle'][$idTipo] = array(
                            'id' => $idTipo,
                            'nombP' => $nomTipo,
                            'alic' => $aliTipo,
                            'afip' => $afipTipo,
                            'monto' => $montoPercepcionCalculo
                        );
                    } else {
                        $arrPercep['detalle'][$idTipo]["monto"] += $montoPercepcionCalculo;
                    }
                }
                $montoPercepcion += $montoPercepcionCalculo;
            }
            $arrPercep["totalP"] += $montoPercepcion;
            $this->percepciones = $arrPercep;
            $this->percepcionesT = $arrPercep["totalP"];
            $this->subtotal += $this->percepcionesT;
            //           echo "<pre> Array:<br>";
            //           print_r($this);
            //           echo "</pre>";
        }
        /**
         * FIN de Perecepciones
         * ***** */
    }

    /*
     * Para poder traer los valores del la promicon
     * Cantidad - Intervalo
     * ====================================================================
     */

    private function promocion_art_cant_intervalo($idArt = null, $cantidad = null)
    {
        include_once('../includes/includes.inc.php');
        $base = $_SESSION["baseConecto"];
        $servidorDb = servidor_db;
        $usuarioDb = usuario_db;
        $passDb = password_db;
        $puertoDb = puerto_db;
                //     echo"<pre>";
                //    print_r($_SESSION);
                //    echo"</pre>";
        $link = mysqli_connect($servidorDb, $usuarioDb, $passDb, $base,$puertoDb);
        //$db=mysqli_select_db($base, $link);
        //require_once 'sesion.inc.php';
        if(!$link){
                echo "fallo conexion ".mysqli_connect_error()." datos:",$servidorDb, $usuarioDb, $passDb, $base,$puertoDb,PHP_EOL;
        }
        $sqlIntervalo = "SELECT pint.* "
            . "FROM articulo_promo_intervalo AS pint "
            . "WHERE "
            . "(pint.desde_cantidad <= {$cantidad} AND pint.hasta_cantidad >= {$cantidad}) "
            . "AND pint.id_articulo = {$idArt} And pint.anulado = 'No'";
        $hacerInt = mysqli_query($link, $sqlIntervalo) or die("no pude recuperar la promocion." . mysqli_error($link) . "<pre>" . $sqlIntervalo . "</pre>");
        $pp = mysqli_fetch_assoc($hacerInt);
        mysqli_close($link);
        //            echo"<pre>";
        //            print_r($pp);
        //            echo"</pre>";
        return $pp;
    }


    /** 
     ** #Calcula el impuesto interno Nuevo calculo con Abm.
     * @param $arrParametros: array()
     * @return double importe del impuesto calculado.
     */

    private function calcularImpuestoInterno($arrParametros)
    {
        /*
         ' Calculo del tipo de impuesto interno

        ' Porcentaje

        ' Porcentaje - Minimo

        ' Monto fijo

        ' Peso

        ' Peso - Monto fijo

       

        ' Porcentaje

        If rs_consulta.Fields!tipo_impuesto_interno = "Porcentaje" Then

            Calculo_Nuevo_Impuesto_Interno = monto_neto_calculo * rs_consulta.Fields!porcentaje / 100

        End If

       

        ' Porcentaje - Minimo

        If rs_consulta.Fields!tipo_impuesto_interno = "Porcentaje - Minimo" Then

            monto_imp_interno = monto_neto_calculo * rs_consulta.Fields!porcentaje / 100

            If monto_imp_interno < rs_consulta.Fields!pago_minimo Then

                Calculo_Nuevo_Impuesto_Interno = rs_consulta.Fields!pago_minimo

            Else

                Calculo_Nuevo_Impuesto_Interno = monto_neto_calculo * rs_consulta.Fields!porcentaje / 100

            End If

        End If

       

        ' Monto fijo

        If rs_consulta.Fields!tipo_impuesto_interno = "Monto fijo" Then

            Calculo_Nuevo_Impuesto_Interno = rs_consulta.Fields!monto_fijo * Cantidad

        End If
         */
        // echo 'parametros funcion impuesto interno.<pre>'.PHP_EOL;
        // print_r($arrParametros);

        $valorImpuesto = 0;
        // Porcentaje 
        if ($arrParametros['tipo'] == 'Porcentaje') {
            $valorImpuesto = (($arrParametros['cantidad'] * $arrParametros['costo']) * $arrParametros['porcentaje']) / 100;
        }
        // Porcentaje Minimo
        if ($arrParametros['tipo'] == 'Porcentaje - Minimo') {
            $montoCalculado = (($arrParametros['cantidad'] * $arrParametros['costo']) * $arrParametros['porcentaje']) / 100;
            if ($montoCalculado < $arrParametros['montoMinimo']) {
                $valorImpuesto = $arrParametros['montoMinimo'];
            }
            if ($montoCalculado >= $arrParametros['montoMinimo']) {
                $valorImpuesto = $montoCalculado;
            }
        }

        // Monto Fijo
        if ($arrParametros['tipo'] == 'Monto fijo') {
            $valorImpuesto = $arrParametros['cantidad'] * $arrParametros['montoFijo'];
        }
        // Peso 
        // Peso - Monto fijo
        //  echo 'vuelta del impuesto',$valorImpuesto;
        return $valorImpuesto;
    }

    public function muestra_pedido()
    {
        $pedido = array();
        // * el subtotal de la venta no tiene sumado el impuesto interno, debo sumarlo.
        $pedido['subtotal'] = $this->subtotal + $this->subtotalImpInt;

        $pedido['subtotalNeto'] = $this->subtotalNeto;
        $pedido['subtotalNetoIva21'] = $this->subtotalNetoIva21;
        $pedido['subtotalNetoIva105'] = $this->subtotalNetoIva105;
        $pedido['subtotalExento'] = $this->subtotalExento;
        $pedido['subtotalImpInt'] = $this->subtotalImpInt;
        $pedido['subtotalIva105'] = $this->subtotalIva105;
        $pedido['subtotalIva21'] = $this->subtotalIva21;
        $pedido['percepcionesT'] = $this->percepcionesT;
        $pedido['percepciones'] = $this->percepciones;
        $pedido['subtotalDesc'] = $this->subtotalDesc;
        $pedido['subtotalDesc21'] = $this->subtotalDesc21;
        $pedido['subtotalDesc105'] = $this->subtotalDesc105;
        $pedido['porDescPie'] = $this->porDescPie;
        $pedido['importeDesc21'] = $this->importeDesc21;
        $pedido['importeDesc105'] = $this->importeDesc105;

        return $pedido;
    }

    /**
     * Process and display cart PEDIDO
     * ******************************
     */
    public function display_carrito_pedido_mobil()    {

        $config = $this->config;
        $errorMessage = null;
        $modDescPie = 0;
        $modDescPieCondVta = 'No';
        $limDescPie = 0;
        $porDescPie = 0;
        $porDescCondVta = 0;
        /*
         * Usuario VENDEDOR
         */
        if (isset($_SESSION['vendedor'])) {
            $objVendedor = $_SESSION['vendedor'];
            //echo print_r($objVendedor);
            // permiso para modificar el descuento del pie.
            if (property_exists($objVendedor, 'mod_descuento_pie')) {
                $modDescPie = $objVendedor->mod_descuento_pie;
            }
            // permiso para aplicar descuento al pie por Cond Venta
            if (property_exists($objVendedor, 'descuento_cv')) {
                $modDescPieCondVta = $objVendedor->descuento_cv;
            }
            // permiso Limite de descuento al pie
            if (property_exists($objVendedor, 'lim_desc_pie')) {
                $limDescPie = $objVendedor->lim_desc_pie;
            }
        }
        $usaIdManual = $_SESSION["usa_id_manual"];
        $tipoUsuario = $_SESSION["tipousuario"];

        /* domicilio obligatorio */

        /* domicilio obligatorio */
        $permisoClientesDom = $_SESSION["obliga_domicilio_cliente"];
        //    echo "<pre>";
        //    print_r($_SESSION);
        //    echo "</pre>";

        /* activacion de logistica */
        $permisoLogistica = $_SESSION["activ_logistica"];

        /*
         * Datos del CLIENTE
         */
        if (is_object($_SESSION['cliente'])) {
            $clienteObj = $_SESSION['cliente'];
        } else {
            $clienteObj = $_SESSION['cliente'][0];
        }
        // Descuento al pie
        if (property_exists($clienteObj, 'descPie')) {
            $porDescPie = $clienteObj->descPie;
        }
        // Descuento por Condicion de Venta del cliente
        if (property_exists($clienteObj, 'descCondVta')) {
            $porDescCondVta = $clienteObj->descCondVta;
        }
        // Permiso para utilizar el descuento por condicion de venta del cliente
        if ($modDescPieCondVta == "No") {
            // NO tengo permiso para aplicar descuento por cond venta
            $porDescCondVta = 0;
        }

        // Simplify some config variables
        $checkout = $config['checkoutPath'];
        $priceFormat = $config['priceFormat'];
        // echo "<pre>";
        // print_r($config['item']);
        // echo "</pre>";
        $id = $config['item']['id'];
        $idManual = $config['item']['idManual'];
        $name = $config['item']['name'];
        $tipoIva = $config['item']['tipoIva'];
        $alicuota = $config['item']['alicuota'];
        $iva = $config['item']['iva'];
        $impIva = $config['item']['impIva'];
        $costo = $config['item']['costo'];
        // imp interno
        $impInterno = $config['item']['impInterno'];
        $impInternoTasa = $config['item']['impInternoTasa'];
        $impInternoDescripcion = $config['item']['impInternoDescripcion'];
        $impInternoTipo = $config['item']['impInternoTipo'];
        $impInternoPorcentaje = $config['item']['impInternoPorcentaje'];
        $impInternoMontoFijo = $config['item']['impInternoMontoFijo'];
        $impInternoPesoCalculado = $config['item']['impInternoPesoCalculado'];
        $impInternoPagoMinimo = $config['item']['impInternoPagoMinimo'];
        $impInternoUnidMed = $config['item']['impInternoIdUnimed'];

        // unidad display -Bulto

        $price = $config['item']['price'];
        $priceN = $config['item']['priceN'];
        $descPor = $config['item']['descPor'];
        //$descTotal = $config['item']['descTotal'];
        $neto = $config['item']['neto'];
        $netoN = $config['item']['netoN'];
        $promo = $config['item']['promo'];
        $promoCant = $config['item']['promoCant'];
        $promoPorc = $config['item']['promoPorc'];
        $promoTipo = $config['item']['promoTipo'];

        $qty = $config['item']['qty'];
        // unidad display bulto
        $comoCuento  = $config['item']['tipoUnidadContada']; // si conte por bulto unidad o display
        $cantidadUnidadDisplay = $config['item']['cantidadUnidadDisplay']; // la unidad display a guardar
        $divisorCantidad = $config['item']['cantidadDividir']; // el divisor final, puede venir dle bulto....        
        $cantidadMinimaContada = $config['item']['cantidadUnidadMinimaContada']; // la cantidad calculada con los 

        $url = $config['item']['url'];
        $add = $config['item']['add'];



        // Use config values as literal indices for incoming POST values
        // Values are the HTML name attributes set in config.json

        // echo "POST::<pre>";
        // print_r($_POST);
        // echo "</pre>";
        if (isset($_POST[$id])) {

            $id = $_POST[$id];
            $name = $_POST[$name];
            $price = $_POST[$price];
            $idManual =$_POST[$idManual];
            if (isset($_POST[$priceN])) {
                $priceN = $_POST[$priceN];
                $netoN = $_POST[$netoN];
            } else {
                $priceN = '';
                $netoN = '';
            }
            $qty = $_POST[$qty];
            $url = $_POST[$url];
            $neto = $_POST[$neto];

            $tipoIva = $_POST[$tipoIva];
            $alicuota = $_POST[$alicuota];
            $iva = $_POST[$iva];
            $impIva = $_POST[$impIva];
            $costo = $_POST[$costo];

            if (isset($_POST[$impInterno])) {
                $impInterno = $_POST[$impInterno];
                $impInternoT = $_POST[$impInternoTasa];
                $impInternoDescripcion = $_POST[$impInternoDescripcion];
                $impInternoTipo = $_POST[$impInternoTipo];
                $impInternoPorcentaje = $_POST[$impInternoPorcentaje];
                $impInternoMontoFijo = $_POST[$impInternoMontoFijo];
                $impInternoPesoCalculado = $_POST[$impInternoPesoCalculado];
                $impInternoPagoMinimo = $_POST[$impInternoPagoMinimo];
                $impInternoUnidMed = $_POST[$impInternoUnidMed];
            } else {
                $impInterno = '';
                $impInternoT = 0;
                $impInternoDescripcion = null;
                $impInternoTipo = null;
                $impInternoPorcentaje = null;
                $impInternoMontoFijo = null;
                $impInternoPesoCalculado = null;
                $impInternoPagoMinimo = null;
                $impInternoUnidMed = null;
            }

            // unidad - display - bulto
            $comoCuento  = $_POST[$comoCuento]; // si conte por bulto unidad o display
            $cantidadUnidadDisplay = $_POST[$cantidadUnidadDisplay];
            $divisorCantidad = $_POST[$divisorCantidad];
            $cantidadMinimaContada = $_POST[$cantidadMinimaContada];

            $promo = $_POST[$promo];
            $promoCant = $_POST[$promoCant];
            $promoPorc = $_POST[$promoPorc];
            $promoTipo = $_POST[$promoTipo];

            $descPor = $_POST[$descPor];
            if (isset($descTotal)) {
                $descTotal = $_POST[$descTotal];
            } else {
                $descTotal = '';
            }


            // Optional CSRF protection, see: http://conceptlogic.com/jcart/security.php
            $jcartToken = $_POST['jcartToken'];
        }



        // Only generate unique token once per session
        if (!isset($_SESSION['jcartToken'])) {
            $_SESSION['jcartToken'] = md5(session_id() . time() . $_SERVER['HTTP_USER_AGENT']);
        }
        // If enabled, check submitted token against session token for POST requests
        if ($config['csrfToken'] === 'true' && $_POST && $jcartToken != $_SESSION['jcartToken']) {
            $errorMessage = 'Invalid token!' . $jcartToken . ' / ' . $_SESSION['jcartToken'];
        }

        // Sanitize values for output in the browser
        $id = filter_var($id, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        //		$name  = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        $url = filter_var($url, FILTER_SANITIZE_URL);

        // Round the quantity if necessary
        if ($config['decimalPlaces'] === true) {
            $qty = round($qty, $config['decimalPlaces']);
        }

        // Add an item
        if (isset($_POST[$add])) {
            // todo armar un array asociativo con las variables porque son muchas , pasar el array armado.
            $itemAdded = $this->add_item(
                $id,
                $name,
                $price,
                $qty,
                $url,
                $iva,
                $tipoIva,
                $impIva,
                $alicuota,
                $impInterno,
                $neto,
                $descTotal,
                $descPor,
                $promo,
                $promoCant,
                $promoPorc,
                $promoTipo,
                $impInternoT,
                null,
                null,
                null,
                null,
                null,
                $costo,
                $impInternoDescripcion,
                $impInternoTipo,
                $impInternoPorcentaje,
                $impInternoMontoFijo,
                $impInternoPesoCalculado,
                $impInternoPagoMinimo,
                $impInternoUnidMed,
                $comoCuento, // si conte por bulto unidad o display
                $cantidadUnidadDisplay,
                $divisorCantidad,
                $cantidadMinimaContada,
                $idManual
            );
            // If not true the add item function returns the error type
            if ($itemAdded !== true) {
                $errorType = $itemAdded;
                switch ($errorType) {
                    case 'qty':
                        $errorMessage = $config['text']['quantityError'];
                        break;
                    case 'price':
                        $errorMessage = $config['text']['priceError'];
                        break;
                    case 'limite':
                        $errorMessage = $config['text']['limiteError'];
                        break;
                    case 'percepcion':
                        $errorMessage = $config['text']['percepError'];
                        break;
                }
            }
        }

        // Update a single item
        if (isset($_POST['jcartUpdate'])) {
            $itemUpdated = $this->update_item($_POST['itemId'], $_POST['itemQty']);
            if ($itemUpdated !== true) {
                $errorMessage = $config['text']['quantityError'];
            }
        }

        // Update all items in the cart
        if (isset($_POST['jcartUpdateCart']) || isset($_POST['jcartCheckout'])) {
            $cartUpdated = $this->update_cart();
            if ($cartUpdated !== true) {
                $errorMessage = $config['text']['quantityError'];
            }
        }

        // Remove an item
        /* After an item is removed, its id stays set in the query string,
          preventing the same item from being added back to the cart in
          subsequent POST requests.  As result, it's not enough to check for
          GET before deleting the item, must also check that this isn't a POST
          request. */
        //        echo var_dump($_GET['jcartRemove'] && !$_POST);
        if (isset($_GET['jcartRemove']) && !isset($_POST['jcartRemove'])) {
            //            echo "<pre>removing</pre>";
            $this->remove_item($_GET['jcartRemove']);
        }

        // Empty the cart
        if (isset($_POST['jcartEmpty'])) {
            $this->empty_cart();
        }
        /*
         * Descuentos
         * 
         * Si actualizo el carrito verifico que me traiga el descuento al pie
         * modificado o no y debo evaluar con el descuento del cliente con
         * la condicion de venta que tiene otro descuento.
         */
        if (isset($_POST['jcart-por-desc-pie']) && $_POST['jcart-por-desc-pie'] != 0) {
            $this->porDescPie = $_POST['jcart-por-desc-pie'];
        } else {
            /* evaluo aca si el descuento al pie del ciente como esta con respecto
             * al descuento por su condicion de venta.
             */
            if ($porDescPie > $porDescCondVta) {
                // descuento al pie del cliente es mayor

                $this->porDescPie = $porDescPie;
            } else {
                // descuento por la condicion de venta

                $this->porDescPie = $porDescCondVta;
            }
        }

        // Determine which text to use for the number of items in the cart
        $itemsText = $config['text']['multipleItems'];
        if ($this->itemCount == 1) {
            $itemsText = $config['text']['singleItem'];
        }

        // Determine if this is the checkout page
        /* First we check the request uri against the config checkout (set when
          the visitor first clicks checkout), then check for the hidden input
          sent with Ajax request (set when visitor has javascript enabled and
          updates an item quantity). */
        $isCheckout = strpos(request_uri(), $checkout);
        if ($isCheckout !== false || isset($_REQUEST['jcartIsCheckout']) && ($_REQUEST['jcartIsCheckout'] == 'true')) {
            $isCheckout = true;
        } else {
            $isCheckout = false;
        }

        // Overwrite the form action to post to gateway.php instead of posting back to checkout page
        if ($isCheckout === true) {

            // Sanititze config path
            $path = filter_var($config['jcartPath'], FILTER_SANITIZE_URL);

            // Trim trailing slash if necessary
            $path = rtrim($path, '/');

            $checkout = $path . '/gateway.php';
        }

        // Default input type
        // Overridden if using button images in config.php
        $inputType = 'submit';

        // If this error is true the visitor updated the cart from the checkout page using an invalid price format
        // Passed as a session var since the checkout page uses a header redirect
        // If passed via GET the query string stays set even after subsequent POST requests
        if (isset($_SESSION['quantityError']) && $_SESSION['quantityError'] === true) {
            $errorMessage = $config['text']['quantityError'];
            unset($_SESSION['quantityError']);
        }

        // Set currency symbol based on config currency code
        $currencyCode = trim(strtoupper($config['currencyCode']));
        switch ($currencyCode) {
            case 'EUR':
                $currencySymbol = '&#128;';
                break;
            case 'GBP':
                $currencySymbol = '&#163;';
                break;
            case 'JPY':
                $currencySymbol = '&#165;';
                break;
            case 'CHF':
                $currencySymbol = 'CHF&nbsp;';
                break;
            case 'SEK':
            case 'DKK':
            case 'NOK':
                $currencySymbol = 'Kr&nbsp;';
                break;
            case 'PLN':
                $currencySymbol = 'z&#322;&nbsp;';
                break;
            case 'HUF':
                $currencySymbol = 'Ft&nbsp;';
                break;
            case 'CZK':
                $currencySymbol = 'K&#269;&nbsp;';
                break;
            case 'ILS':
                $currencySymbol = '&#8362;&nbsp;';
                break;
            case 'TWD':
                $currencySymbol = 'NT$';
                break;
            case 'THB':
                $currencySymbol = '&#3647;';
                break;
            case 'MYR':
                $currencySymbol = 'RM';
                break;
            case 'PHP':
                $currencySymbol = 'Php';
                break;
            case 'BRL':
                $currencySymbol = 'R$';
                break;
            case 'USD':
            default:
                $currencySymbol = '$';
                break;
        }

        ////////////////////////////////////////////////////////////////////////
        // Output the cart
        // ////////////////////////////////////////////////////////////////////
        // Return specified number of tabs to improve readability of HTML output
        $limiteDespacho = 500;
        $validoLimiteDespacho = true;
        //$this->update_subtotal();
        //  echo "<pre>";
        //      print_R($this);
        //      echo "</pre>";

        // $this->update_subtotal();

        $valorPedido = $this->subtotal + $this->subtotalImpInt;
        // If there's an error message wrap it in some HTML
        if ($errorMessage) {
            $errorMessage = "<div id='jcart-error' class='alerta-error' >"
                . "<strong><i class='fa fa-warning'></i> Advertencia: </strong> $errorMessage</div>";
        }
        //        echo "<pre>";
        //        var_dump($valorPedido);
        //        echo "<br>";
        //        var_dump($limiteDespacho);
        //        echo "<br>";
        //        var_dump($valorPedido>$limiteDespacho);
        //        echo "</pre>";
        // Display the cart header
        echo tab(1) . "$errorMessage\n";
        echo tab(1) . "<form method='post' action='$checkout' id='jcart-form-checkout'>\n";
        echo tab(2) . "<fieldset>\n";
        echo tab(3) . "<input type='hidden' name='jcartToken' value='{$_SESSION['jcartToken']}' />\n";
        echo tab(3) . "<div id='cabeza'>";
        //echo tab(4) . "<div id='subTitulo'>";
        //echo tab(5) . "Pedido";
        // echo tab(5) .$config["text"]["cartTitle"];
        // echo tab(4) . "</div>";

        //echo tab(4) . "<img src='_img/carrito/carrito32x32.png'>";
        //                echo tab(4) . "<div id='jcart-title'>{$config['text']['cartTitle']} ($this->itemCount $itemsText)</div>\n";
        echo tab(4) . "<div id='jcart-title'> <i class='fa fa-shopping-cart'></i> (<strong>" . count($this->items) . " " . $itemsText . "</strong>) </div>\n";
        echo tab(4) . "<div id='jcart-subtotal'><strong>$" . number_format($this->subtotal + $this->subtotalImpInt, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</strong></div>";
        echo tab(3) . "</div>";
        echo tab(3) . "<div id='cuerpo'>";
        echo tab(3) . "<table>\n";
        echo tab(4) . "<thead>\n";
        echo tab(5) . "<tr>\n";

        //echo tab(6) . "<th>Cod.</th>\n";
        echo tab(6) . "<th>Producto</th>\n";
        echo tab(6) . "<th>Unit c/IVA</th>\n";
        echo tab(6) . "<th>Cant.</th>\n";
        //echo tab(6) . "<th>Desc</th>\n";
        //                echo tab(6) . "<th>Alic</th>\n";
        //                echo tab(6) . "<th></th>";
        echo tab(6) . "<th>Total</th>\n";
        // echo tab(6) . "<th>&nbsp</th>\n";
        //		echo tab(7) . "<strong id='jcart-title'>{$config['text']['cartTitle']}</strong> ($this->itemCount $itemsText)\n";
        //                echo tab(7) . "<span id='jcart-subtotal'>{$config['text']['subtotal']}: <strong>$currencySymbol" . number_format($this->subtotal, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</strong></span>";
        //		echo tab(6) . "</th>\n";
        echo tab(5) . "</tr>" . "\n";

        echo tab(4) . "</thead>\n";

        //		
        echo tab(4) . "<tbody>\n";

        // If any items in the cart
        if ($this->itemCount > 0) {

            // Display line items
            foreach ($this->get_contents() as $item) {
                //evaluar las promociones si son por cantidad aca es donde hay
                //que duplicar el articulo x la cantidad en promo
                $codigoSistema =$item['id'];
                if($usaIdManual=='Si'){
                    $codigoSistema=$item['idManual'];
                }
                if($usaIdManual=='No'){
                    $codigoSistema=$item['id'];

                }
                //  echo "<pre>";
                //  print_r($item);
                //  echo "</pre>";
                if ($item['qty'] >= $item['promoCant']) {
                    if ($item['promo'] == 'si' && $item['promoTipo'] == 'Cantidad - Unidad') {
                        //duplicar los productos, solo para mostrar.

                        $cuantosGratis = floor(($item['qty'] * $item['promoPorc']) / $item['promoCant']);
                    }
                }

                echo tab(5) . "<tr>\n";
                $datosProducto ='';
                // $datosProducto .= '<span>'.$item['name'] . '</span><br>';
               
                $datosProducto .='Código: <strong>'.$codigoSistema.'</strong><br>';
                $datosProducto .='Presentación: <strong>'.$item['comoCuento'].'(x'.round($item['divisorCantidad'],0).')</strong><br>';
               
                $datosProducto .= 'Unitario: <strong>' . $currencySymbol . number_format($item['priceN'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>';
                $datosProducto .= 'Cantidad: <strong>' . $item['qty'] . '</strong><br>';
                $datosProducto .= 'Neto x Renglon: <strong>' . $currencySymbol . number_format($item['neto'] * $item['qty'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>';
                if($item['descPor']!=0):
                    $datosProducto .= 'Descuento: <strong>' . $item['descPor'] . '%</strong><br>';
                    $datosProducto .= 'Neto x Renglon: <strong>' . $currencySymbol . number_format($item['subtotalNeto'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>'; 
                endif;
                
                $datosProducto .= 'Iva (' . $item['alicuota'] . '%): <strong>' . $currencySymbol . number_format($item['subtotalIva'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>';

                echo tab(6) . "<td class='jcart-item-name' title=''>\n";

                echo tab(6) . " ";
                //if ($item['url']) {
                //    echo tab(7) . "<a  href='{$item['url']}'>{$item['name']}</a>\n";
                //} else {
                    // evaluar si muestro id manual o cod idar at, tambien poner la info que se despliegue..

                echo tab(7) ."<div class='tdNombreArticuloResumen'>";
                echo "(".$codigoSistema.") ".$item['name'];
                if ($item['descTotal'] != 0) {
                    $partes = explode(".", $item['descPor']);
                    if (isset($partes[1])) {
                        // decimal
                        echo tab(7) . "<span class='verde'>" . number_format($item['descPor'], 2) . "% off</span>\n";
                    } else {
                        // entero
                        echo tab(7) . "<span class='verde'>" . number_format($item['descPor'], 0) . "% off</span>\n";
                    }
                }
                echo tab(7)."</div>".PHP_EOL;
                echo tab(7) ."<span class='spanVerProductoInfo' idProducto='".$item['key']."'><i class='fa fa-angle-down fa-lg'></i> Mas</span>".PHP_EOL;
                echo tab(7) ."<span><a class='jcart-remove' href='?jcartRemove={$item['key']}'><i class='fa fa-trash-alt fa-lg'></i> Borrar</a></span>";
                echo tab(7) ."<div class='tdDetalleProducto' id='jcartDivInfoProducto".$item['key']."' >".$datosProducto."</div>".PHP_EOL;

                //}
                echo tab(7) . "<input name='jcartItemName[]' type='hidden' value='{$item['name']}' />\n";
               

                echo tab(6) . "</td>\n";
                echo tab(6) . "<td class='jcart-item-price'>\n";
                echo tab(6) . $currencySymbol . number_format($item['priceN'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']);
                
                echo tab(6) ."<br><strong class='".strtolower($item['comoCuento'])."'>".$item['comoCuento']."</strong>";
                echo tab(6) . "</td>\n";

                echo tab(6) . "<td class='jcart-item-qty'>\n";
                echo tab(7) . "<input name='jcartItemId[]' type='hidden' value='{$item['id']}' />\n";

                // echo tab(7) . $item['qty']."\n";
                echo tab(7) . "<input id='jcartItemQty-{$item['key']}' name='jcartItemQty[]' size='4'  type='text' value='{$item['qty']}' />\n";
                echo tab(6) . "</td>\n";
                //                echo tab(6) . "<td class='jcart-item-price'>\n";
                //                if ($item['descTotal'] != 0) {
                //                    echo tab(7) . "<span>" . $item['descPor'] . "%</span>\n";
                //                } else {
                //                    echo tab(7) . "<span>0%</span>\n";
                //                }
                //                echo tab(6) . "</td>\n";


                echo tab(6) . "<td class='jcart-item-price-tot' title=''>";

                echo tab(7) . "<span>$currencySymbol" . number_format($item['subtotal'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</span>"
                    . "<input name='jcartItemPrice[]' type='hidden' value='{$item['priceN']}' />\n";
                //echo tab(7) . "<a class='jcart-remove' href='?jcartRemove={$item['id']}'><img src='_img/action_delete.png'></a>\n";
                // echo tab(6) . "</td>\n";
                // echo tab(6) . "<td><a class='jcart-remove' href='?jcartRemove={$item['key']}'><i class='fa fa-trash-alt'></i></a>\n</td>";
                // echo tab(5) . "</tr>\n";
            }
        }

        // The cart is empty
        else {
            echo tab(5) . "<tr><td id='jcart-empty' class='vacio' colspan='7'>{$config['text']['emptyMessage']}</td></tr>\n";
        }
        echo tab(4) . "</tbody>\n";
        echo tab(3) . "</table>\n\n";
        echo tab(3) . "</div>";

        /* USUARIO VENDEDOR
         * ===================================================================== 
         */
        if ($tipoUsuario == "vendedor") {
            echo tab(3) . "<div id='pie'>";


            if ($isCheckout !== true) {
                if ($config['button']['checkout']) {
                    $inputType = "image";
                    $src = " src='{$config['button']['checkout']}' alt='{$config['text']['checkout']}' title='' ";
                }
                
                //			echo tab(4) . "<input type='$inputType' $src id='jcart-checkout' name='jcartCheckout' class='jcart-buttons-ok' alt='{$config['text']['checkout']}' title='{$config['text']['checkout']}' />\n";
            }

            echo tab(4) . "<div class='jcart-panel-uno'>";
            echo tab(5) . "<div class='textoPie'>ST Neto: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalNeto, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";

            echo tab(5) . "<div class='textoPie'>Desc Pie: </div><div class='importePie'>";
            if ($modDescPie == "Si") {
                echo tab(5) . "<input type='number' id='jcart-por-desc-pie' name='jcart-por-desc-pie' min='0' disabled max='" . $limDescPie . "' value='" . $this->porDescPie . "'>%</div>\n";
            } else {
                echo tab(5) . "<input type='number' id='jcart-por-desc-pie' name='jcart-por-desc-pie' min='0' disabled max='20' value='" . $this->porDescPie . "'>%</div>\n";
            }
            echo tab(5) . "<div class='textoPie'>Imp Desc: </div><div class='importePie verde'>($currencySymbol" . number_format(($this->importeDesc21 + $this->subtotalDesc105), $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . ")</div>\n";

            echo tab(5) . "<div class='textoPie'>ST Desc: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalDesc, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";

            if ($this->subtotalIva105 > 0) {
                echo tab(5) . "<div class='textoPie'>ST 21%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalDesc21, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
                echo tab(5) . "<div class='textoPie'>ST 10,5%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalDesc105, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            }

            echo tab(4) . "</div>";
            echo tab(4) . "<div class='jcart-panel-dos'>";
            if ($this->subtotalExento > 0) {
                echo tab(5) . "<div class='textoPie'>ST Exto: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalExento, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            }

            echo tab(5) . "<div class='textoPie'>IVA 21%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalIva21, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            if ($this->subtotalIva105 > 0) {
                echo tab(5) . "<div class='textoPie'>IVA 10,5%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalIva105, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";

                //echo tab(5) . "<span id='jcart-subtotal'>SubTotal Neto: <strong>$currencySymbol" . number_format($this->subtotalNeto, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</strong></span>\n";
            }

            echo tab(5) . "<div class='textoPie'>Imp. Int: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalImpInt, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            echo tab(5) . "<div class='textoPie'>Perc: </div><div class='importePie'>$currencySymbol" . number_format($this->percepcionesT, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            echo tab(5) . "<div class='textoPie azulImp'>Total: </div><div class='importePie azulImp'><i class='fa fa-dollar'></i> " . number_format($this->subtotal + $this->subtotalImpInt, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            echo tab(4) . "</div>";


            echo tab(4) . "<div class='jcart-panel-tres'>";
            echo tab(5) . " <div class='opcionesPie'>";
            // echo tab(5) . "<div  title='Ingresar hora de envio'> 
             echo tab(5) . "<label for='jcart-detalle'><i class='far fa-clock fa-lg fa-fw'></i> Detalle</label>";
             echo tab(5) . "<input type='text' autocomplete='off' id='jcart-detalle' required='required' placeholder='hh:mm o detalle' name='jcart-detalle' class='detallePedido'>";
            echo tab(5) . "</div>";// div detalle
            echo tab(5) . " <div class='opcionesPie'>";
            echo tab(5) . "<div  title='Seleccionar Forma de entrega'>
                            <label for='formaEntrega'><i class='fa fa-truck fa-lg fa-fw'></i> Envio</label> 
                            <select name='formaEntrega' id='formaEntrega' size='2'>";

            echo tab(6) . " <option selected value='Envía por despacho'>ENVIAR A DOMICILIO</option>";

            echo tab(6) . " <option value='Retira cliente despacho'>RETIRO EN TIENDA</option>";
            //echo tab(6) . "<option selected value='Envía por despacho'>Envía por despacho</option>";
            //            echo tab(6) . "<option value='Entrega vendedor'>ENTREGA VENDEDOR</option>";
            //            echo tab(6) . "<option value='Transporte'>ENVIO POR TRANSPORTE</option>";
            //            echo tab(6) . "<option value='Reservado en depósito'>RESERVADO EN TIENDA</option>";



            echo tab(5) . " </select>";     

            echo tab(5) . "</div>"; // div envio

            //                echo tab(5) . "</br>";
            //                echo tab(5) . "<div class='texto'>";
            //                echo tab(5) . "</div>";
            

            echo tab(5) . "</div>"; // div opcionesPie caontiene estas dos opciones para usar flex
            
            // echo tab(5) . "</div>"; // fin div opciones pie detalle

                        /* logistica */
            /* domicilios */
            //                echo var_dump($permisoClientesDom);
            $cuantosDom = sizeof($_SESSION['domicilios_cliente'])+1;
            if ($permisoClientesDom == 'Si') {
                
                echo tab(6) . '<div class="opcionesPie" >
                                <label for="domicilio_entrega">
                                    <i class="fa fa-map-marker fa-lg fa-fw"></i> Domicilio
                                        <select name="domicilio_entrega" id="domicilio_entrega" required=required title="Ingresar Domicilio de entrega" size="'.$cuantosDom.'">';
                                        // . '<option value="">- domicilio- </option>';
                foreach ($_SESSION['domicilios_cliente'] as $dom) {
                    echo tab(7) . '<option value="' . $dom["idDom"] . '|' . $dom["id_zona"] . '">'
                        . $dom["Calle"] . ', '
                        . $dom["NroCalle"] . ', '
                        . $dom["Dpto"] . ' - '
                        . $dom["Provincia"] . ' - '
                        . $dom["NombreDepartamento"] . ' - '
                        . $dom["NombreDistrito"] . ' - '
                        . $dom["nombre_zona"]
                        . '</option>';
                }
                echo tab(6) . ' </select>
                            </label>
                </div>';// did del domicilio cliente. envio.

                //                                        echo str_pad(substr("Capital,Godoy Cruz,Zona Sur,Zona Este,Guaymallen,Las Heras,Lujan,Maipu,Zona Norte,San Luis",0,20),23,'.');
            }

            if ($permisoClientesDom != 'Si') {
                
                echo tab(6) . '<div class="opcionesPie">
                    <label for="domicilio_entrega"><i class="fa fa-map-marker fa-lg fa-fw"></i> Domicilio</label>'
                    . '<select name="domicilio_entrega" id="domicilio_entrega" title="Ingresar Domicilio de entrega" size="'.$cuantosDom.'"> ';
                    // . '<option value="">- domicilio - </option>';
                foreach ($_SESSION['domicilios_cliente'] as $dom) {
                    echo tab(7) . '<option value="' . $dom["idDom"] . '|' . $dom["id_zona"] . '">'
                        . $dom["Calle"] . ' '
                        . $dom["NroCalle"] . ' '
                        . $dom["Dpto"] . ' - '
                        . $dom["Provincia"] . ' - '
                        . $dom["NombreDepartamento"] . ' - '
                        . $dom["NombreDistrito"] . ' - '
                        . $dom["nombre_zona"]
                        . '</option>';
                }
                echo tab(6) . '</select>
                </div>';
            }
            /* Logistica */

            if ($permisoLogistica == "Si") {
                echo tab(6) . '<div class="opcionesPie" title="Ingresar la hoja de ruta">
                <label for="hoja_ruta">
                <i class="fa fa-share-alt fa-lg fa-fw"></i> Ruta</label>
                    <select name="hoja_ruta" id="hoja_ruta" required=required  size="1">';
                    //  . '<option value="" selected>- seleccione ruta - </option>';

                echo tab(6) . '</select></div>'; // div conetiene hoja de ruta
            }


            echo tab(4) . "</div>"; // fin div jcar-pie tres
            // el boton 
            echo tab(4) . "<div class='finCarrito'>"; // div contiene boton finalizas

            echo tab(4) . "<button type='button' id='jcart-checkout' name='jcartCheckout' class='botonCheckout'>"
                    . "<i class='fa fa-check'></i> Finalizar "
                    . "</button>\n";


            echo tab(4) . "</div>"; // fin div carrito fin

            echo tab(3) . "</div>";

            // echo tab(3) . "<div id='jcart-buttons'>\n";

            // if ($config['button']['update']) {
            //     $inputType = "image";
            //     $src = " src='{$config['button']['update']}' alt='{$config['text']['update']}' title='' ";
            // }

            // echo tab(4) . "<input type='$inputType' $src name='jcartUpdateCart' value='{$config['text']['update']}' class='jcart-button' />\n";

            // if ($config['button']['empty']) {
            //     $inputType = "image";
            //     $src = " src='{$config['button']['empty']}' alt='{$config['text']['emptyButton']}' title='' ";
            // }

            // echo tab(4) . "<input type='$inputType' $src name='jcartEmpty' value='{$config['text']['emptyButton']}' class='jcart-button' />\n";

            // // fin del pie
            // echo tab(3) . "</div>\n";
        }
        /*
         * PIE DEL CLIENTE
         * =====================================================================
         */
        if ($tipoUsuario == "cliente") {
            echo tab(3) . "<div id='pie'>";


            if ($isCheckout !== true) {
                if ($config['button']['checkout']) {
                    $inputType = "image";
                    $src = " src='{$config['button']['checkout']}' alt='{$config['text']['checkout']}' title='' ";
                }
                echo tab(4) . "<button type='submit' id='jcart-checkout' name='jcartCheckout' class='botonNuevo grande azul'>"
                    . "Continuar <i class='fa fa-chevron-right'></i>"
                    . "</button>\n";
                //			echo tab(4) . "<input type='$inputType' $src id='jcart-checkout' name='jcartCheckout' class='jcart-buttons-ok' alt='{$config['text']['checkout']}' title='{$config['text']['checkout']}' />\n";
            }


            echo tab(3) . "<div id='jcart-buttons'>\n";

            if ($config['button']['update']) {
                $inputType = "image";
                $src = " src='{$config['button']['update']}' alt='{$config['text']['update']}' title='' ";
            }

            echo tab(4) . "<input type='$inputType' $src name='jcartUpdateCart' value='{$config['text']['update']}' class='jcart-button' />\n";

            if ($config['button']['empty']) {
                $inputType = "image";
                $src = " src='{$config['button']['empty']}' alt='{$config['text']['emptyButton']}' title='' ";
            }

            echo tab(4) . "<input type='$inputType' $src name='jcartEmpty' value='{$config['text']['emptyButton']}' class='jcart-button' />\n";

            // fin del pie
            echo tab(3) . "</div>\n";
        }


        echo tab(2) . "</fieldset>\n";
        echo tab(1) . "</form>\n\n";

        echo tab(1) . "<div id='jcart-tooltip'></div>\n";

        echo "<script>"
            . "</script>\n";
    }

   
    // * funcion para mostrar carrito Pedido DESKTOP
    public function display_carrito_pedido_desktop(){

        $config = $this->config;
        $errorMessage = null;
        $modDescPie = 0;
        $modDescPieCondVta = 'No';
        $limDescPie = 0;
        $porDescPie = 0;
        $porDescCondVta = 0;
        /*
         * Usuario VENDEDOR
         */
        if (isset($_SESSION['vendedor'])) {
            $objVendedor = $_SESSION['vendedor'];
            //echo print_r($objVendedor);
            // permiso para modificar el descuento del pie.
            if (property_exists($objVendedor, 'mod_descuento_pie')) {
                $modDescPie = $objVendedor->mod_descuento_pie;
            }
            // permiso para aplicar descuento al pie por Cond Venta
            if (property_exists($objVendedor, 'descuento_cv')) {
                $modDescPieCondVta = $objVendedor->descuento_cv;
            }
            // permiso Limite de descuento al pie
            if (property_exists($objVendedor, 'lim_desc_pie')) {
                $limDescPie = $objVendedor->lim_desc_pie;
            }
        }
        $usaIdManual = $_SESSION["usa_id_manual"];
        $tipoUsuario = $_SESSION["tipousuario"];

        /* domicilio obligatorio */

        /* domicilio obligatorio */
        $permisoClientesDom = $_SESSION["obliga_domicilio_cliente"];
        //    echo "<pre>";
        //    print_r($_SESSION);
        //    echo "</pre>";

        /* activacion de logistica */
        $permisoLogistica = $_SESSION["activ_logistica"];

        /*
         * Datos del CLIENTE
         */
        if (is_object($_SESSION['cliente'])) {
            $clienteObj = $_SESSION['cliente'];
        } else {
            $clienteObj = $_SESSION['cliente'][0];
        }
        // Descuento al pie
        if (property_exists($clienteObj, 'descPie')) {
            $porDescPie = $clienteObj->descPie;
        }
        // Descuento por Condicion de Venta del cliente
        if (property_exists($clienteObj, 'descCondVta')) {
            $porDescCondVta = $clienteObj->descCondVta;
        }
        // Permiso para utilizar el descuento por condicion de venta del cliente
        if ($modDescPieCondVta == "No") {
            // NO tengo permiso para aplicar descuento por cond venta
            $porDescCondVta = 0;
        }

        // Simplify some config variables
        $checkout = $config['checkoutPath'];
        $priceFormat = $config['priceFormat'];
        // echo "<pre>";
        // print_r($config['item']);
        // echo "</pre>";
        $id = $config['item']['id'];
        $idManual = $config['item']['idManual'];
        $name = $config['item']['name'];
        $tipoIva = $config['item']['tipoIva'];
        $alicuota = $config['item']['alicuota'];
        $iva = $config['item']['iva'];
        $impIva = $config['item']['impIva'];
        $costo = $config['item']['costo'];
        // imp interno
        $impInterno = $config['item']['impInterno'];
        $impInternoTasa = $config['item']['impInternoTasa'];
        $impInternoDescripcion = $config['item']['impInternoDescripcion'];
        $impInternoTipo = $config['item']['impInternoTipo'];
        $impInternoPorcentaje = $config['item']['impInternoPorcentaje'];
        $impInternoMontoFijo = $config['item']['impInternoMontoFijo'];
        $impInternoPesoCalculado = $config['item']['impInternoPesoCalculado'];
        $impInternoPagoMinimo = $config['item']['impInternoPagoMinimo'];
        $impInternoUnidMed = $config['item']['impInternoIdUnimed'];

        // unidad display -Bulto

        $price = $config['item']['price'];
        $priceN = $config['item']['priceN'];
        $descPor = $config['item']['descPor'];
        //$descTotal = $config['item']['descTotal'];
        $neto = $config['item']['neto'];
        $netoN = $config['item']['netoN'];
        $promo = $config['item']['promo'];
        $promoCant = $config['item']['promoCant'];
        $promoPorc = $config['item']['promoPorc'];
        $promoTipo = $config['item']['promoTipo'];

        $qty = $config['item']['qty'];
        // unidad display bulto
        $comoCuento  = $config['item']['tipoUnidadContada']; // si conte por bulto unidad o display
        $cantidadUnidadDisplay = $config['item']['cantidadUnidadDisplay']; // la unidad display a guardar
        $divisorCantidad = $config['item']['cantidadDividir']; // el divisor final, puede venir dle bulto....        
        $cantidadMinimaContada = $config['item']['cantidadUnidadMinimaContada']; // la cantidad calculada con los 

        $url = $config['item']['url'];
        $add = $config['item']['add'];



        // Use config values as literal indices for incoming POST values
        // Values are the HTML name attributes set in config.json

        // echo "POST::<pre>";
        // print_r($_POST);
        // echo "</pre>";
        if (isset($_POST[$id])) {

            $id = $_POST[$id];
            $name = $_POST[$name];
            $price = $_POST[$price];
            $idManual =$_POST[$idManual];
            if (isset($_POST[$priceN])) {
                $priceN = $_POST[$priceN];
                $netoN = $_POST[$netoN];
            } else {
                $priceN = '';
                $netoN = '';
            }
            $qty = $_POST[$qty];
            $url = $_POST[$url];
            $neto = $_POST[$neto];

            $tipoIva = $_POST[$tipoIva];
            $alicuota = $_POST[$alicuota];
            $iva = $_POST[$iva];
            $impIva = $_POST[$impIva];
            $costo = $_POST[$costo];

            if (isset($_POST[$impInterno])) {
                $impInterno = $_POST[$impInterno];
                $impInternoT = $_POST[$impInternoTasa];
                $impInternoDescripcion = $_POST[$impInternoDescripcion];
                $impInternoTipo = $_POST[$impInternoTipo];
                $impInternoPorcentaje = $_POST[$impInternoPorcentaje];
                $impInternoMontoFijo = $_POST[$impInternoMontoFijo];
                $impInternoPesoCalculado = $_POST[$impInternoPesoCalculado];
                $impInternoPagoMinimo = $_POST[$impInternoPagoMinimo];
                $impInternoUnidMed = $_POST[$impInternoUnidMed];
            } else {
                $impInterno = '';
                $impInternoT = 0;
                $impInternoDescripcion = null;
                $impInternoTipo = null;
                $impInternoPorcentaje = null;
                $impInternoMontoFijo = null;
                $impInternoPesoCalculado = null;
                $impInternoPagoMinimo = null;
                $impInternoUnidMed = null;
            }

            // unidad - display - bulto
            $comoCuento  = $_POST[$comoCuento]; // si conte por bulto unidad o display
            $cantidadUnidadDisplay = $_POST[$cantidadUnidadDisplay];
            $divisorCantidad = $_POST[$divisorCantidad];
            $cantidadMinimaContada = $_POST[$cantidadMinimaContada];

            $promo = $_POST[$promo];
            $promoCant = $_POST[$promoCant];
            $promoPorc = $_POST[$promoPorc];
            $promoTipo = $_POST[$promoTipo];

            $descPor = $_POST[$descPor];
            if (isset($descTotal)) {
                $descTotal = $_POST[$descTotal];
            } else {
                $descTotal = '';
            }


            // Optional CSRF protection, see: http://conceptlogic.com/jcart/security.php
            $jcartToken = $_POST['jcartToken'];
        }



        // Only generate unique token once per session
        if (!isset($_SESSION['jcartToken'])) {
            $_SESSION['jcartToken'] = md5(session_id() . time() . $_SERVER['HTTP_USER_AGENT']);
        }
        // If enabled, check submitted token against session token for POST requests
        if ($config['csrfToken'] === 'true' && $_POST && $jcartToken != $_SESSION['jcartToken']) {
            $errorMessage = 'Invalid token!' . $jcartToken . ' / ' . $_SESSION['jcartToken'];
        }

        // Sanitize values for output in the browser
        $id = filter_var($id, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        //		$name  = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        $url = filter_var($url, FILTER_SANITIZE_URL);

        // Round the quantity if necessary
        if ($config['decimalPlaces'] === true) {
            $qty = round($qty, $config['decimalPlaces']);
        }

        // Add an item
        if (isset($_POST[$add])) {
            // todo armar un array asociativo con las variables porque son muchas , pasar el array armado.
            $itemAdded = $this->add_item(
                $id,
                $name,
                $price,
                $qty,
                $url,
                $iva,
                $tipoIva,
                $impIva,
                $alicuota,
                $impInterno,
                $neto,
                $descTotal,
                $descPor,
                $promo,
                $promoCant,
                $promoPorc,
                $promoTipo,
                $impInternoT,
                null,
                null,
                null,
                null,
                null,
                $costo,
                $impInternoDescripcion,
                $impInternoTipo,
                $impInternoPorcentaje,
                $impInternoMontoFijo,
                $impInternoPesoCalculado,
                $impInternoPagoMinimo,
                $impInternoUnidMed,
                $comoCuento, // si conte por bulto unidad o display
                $cantidadUnidadDisplay,
                $divisorCantidad,
                $cantidadMinimaContada,
                $idManual
            );
            // If not true the add item function returns the error type
            if ($itemAdded !== true) {
                $errorType = $itemAdded;
                switch ($errorType) {
                    case 'qty':
                        $errorMessage = $config['text']['quantityError'];
                        break;
                    case 'price':
                        $errorMessage = $config['text']['priceError'];
                        break;
                    case 'limite':
                        $errorMessage = $config['text']['limiteError'];
                        break;
                    case 'percepcion':
                        $errorMessage = $config['text']['percepError'];
                        break;
                }
            }
        }

        // Update a single item
        if (isset($_POST['jcartUpdate'])) {
            $itemUpdated = $this->update_item($_POST['itemId'], $_POST['itemQty']);
            if ($itemUpdated !== true) {
                $errorMessage = $config['text']['quantityError'];
            }
        }

        // Update all items in the cart
        if (isset($_POST['jcartUpdateCart']) || isset($_POST['jcartCheckout'])) {
            $cartUpdated = $this->update_cart();
            if ($cartUpdated !== true) {
                $errorMessage = $config['text']['quantityError'];
            }
        }

        // Remove an item
        /* After an item is removed, its id stays set in the query string,
          preventing the same item from being added back to the cart in
          subsequent POST requests.  As result, it's not enough to check for
          GET before deleting the item, must also check that this isn't a POST
          request. */
        //        echo var_dump($_GET['jcartRemove'] && !$_POST);
        if (isset($_GET['jcartRemove']) && !isset($_POST['jcartRemove'])) {
            //            echo "<pre>removing</pre>";
            $this->remove_item($_GET['jcartRemove']);
        }

        // Empty the cart
        if (isset($_POST['jcartEmpty'])) {
            $this->empty_cart();
        }
        /*
         * Descuentos
         * 
         * Si actualizo el carrito verifico que me traiga el descuento al pie
         * modificado o no y debo evaluar con el descuento del cliente con
         * la condicion de venta que tiene otro descuento.
         */
        if (isset($_POST['jcart-por-desc-pie']) && $_POST['jcart-por-desc-pie'] != 0) {
            $this->porDescPie = $_POST['jcart-por-desc-pie'];
        } else {
            /* evaluo aca si el descuento al pie del ciente como esta con respecto
             * al descuento por su condicion de venta.
             */
            if ($porDescPie > $porDescCondVta) {
                // descuento al pie del cliente es mayor

                $this->porDescPie = $porDescPie;
            } else {
                // descuento por la condicion de venta

                $this->porDescPie = $porDescCondVta;
            }
        }

        // Determine which text to use for the number of items in the cart
        $itemsText = $config['text']['multipleItems'];
        if ($this->itemCount == 1) {
            $itemsText = $config['text']['singleItem'];
        }

        // Determine if this is the checkout page
        /* First we check the request uri against the config checkout (set when
          the visitor first clicks checkout), then check for the hidden input
          sent with Ajax request (set when visitor has javascript enabled and
          updates an item quantity). */
        $isCheckout = strpos(request_uri(), $checkout);
        if ($isCheckout !== false || isset($_REQUEST['jcartIsCheckout']) && ($_REQUEST['jcartIsCheckout'] == 'true')) {
            $isCheckout = true;
        } else {
            $isCheckout = false;
        }

        // Overwrite the form action to post to gateway.php instead of posting back to checkout page
        if ($isCheckout === true) {

            // Sanititze config path
            $path = filter_var($config['jcartPath'], FILTER_SANITIZE_URL);

            // Trim trailing slash if necessary
            $path = rtrim($path, '/');

            $checkout = $path . '/gateway.php';
        }

        // Default input type
        // Overridden if using button images in config.php
        $inputType = 'submit';

        // If this error is true the visitor updated the cart from the checkout page using an invalid price format
        // Passed as a session var since the checkout page uses a header redirect
        // If passed via GET the query string stays set even after subsequent POST requests
        if (isset($_SESSION['quantityError']) && $_SESSION['quantityError'] === true) {
            $errorMessage = $config['text']['quantityError'];
            unset($_SESSION['quantityError']);
        }

        // Set currency symbol based on config currency code
        $currencyCode = trim(strtoupper($config['currencyCode']));
        switch ($currencyCode) {
            case 'EUR':
                $currencySymbol = '&#128;';
                break;
            case 'GBP':
                $currencySymbol = '&#163;';
                break;
            case 'JPY':
                $currencySymbol = '&#165;';
                break;
            case 'CHF':
                $currencySymbol = 'CHF&nbsp;';
                break;
            case 'SEK':
            case 'DKK':
            case 'NOK':
                $currencySymbol = 'Kr&nbsp;';
                break;
            case 'PLN':
                $currencySymbol = 'z&#322;&nbsp;';
                break;
            case 'HUF':
                $currencySymbol = 'Ft&nbsp;';
                break;
            case 'CZK':
                $currencySymbol = 'K&#269;&nbsp;';
                break;
            case 'ILS':
                $currencySymbol = '&#8362;&nbsp;';
                break;
            case 'TWD':
                $currencySymbol = 'NT$';
                break;
            case 'THB':
                $currencySymbol = '&#3647;';
                break;
            case 'MYR':
                $currencySymbol = 'RM';
                break;
            case 'PHP':
                $currencySymbol = 'Php';
                break;
            case 'BRL':
                $currencySymbol = 'R$';
                break;
            case 'USD':
            default:
                $currencySymbol = '$';
                break;
        }

        ////////////////////////////////////////////////////////////////////////
        // Output the cart
        // ////////////////////////////////////////////////////////////////////
        // Return specified number of tabs to improve readability of HTML output
        $limiteDespacho = 500;
        $validoLimiteDespacho = true;
        //$this->update_subtotal();
        //  echo "<pre>";
        //      print_R($this);
        //      echo "</pre>";

        // $this->update_subtotal();

        $valorPedido = $this->subtotal + $this->subtotalImpInt;
        // If there's an error message wrap it in some HTML
        if ($errorMessage) {
            $errorMessage = "<div id='jcart-error' class='alerta-error' >"
                . "<strong><i class='fa fa-warning'></i> Advertencia: </strong> $errorMessage</div>";
        }
        //        echo "<pre>";
        //        var_dump($valorPedido);
        //        echo "<br>";
        //        var_dump($limiteDespacho);
        //        echo "<br>";
        //        var_dump($valorPedido>$limiteDespacho);
        //        echo "</pre>";
        // Display the cart header
        echo tab(1) . "$errorMessage\n";
        echo tab(1) . "<form method='post' action='$checkout' id='jcart-form-checkout'>\n";
        echo tab(2) . "<fieldset>\n";
        echo tab(3) . "<input type='hidden' name='jcartToken' value='{$_SESSION['jcartToken']}' />\n";
        echo tab(3) . "<div id='cabeza'>";
        //echo tab(4) . "<div id='subTitulo'>";
        //echo tab(5) . "Pedido";
        // echo tab(5) .$config["text"]["cartTitle"];
        // echo tab(4) . "</div>";

        //echo tab(4) . "<img src='_img/carrito/carrito32x32.png'>";
        //                echo tab(4) . "<div id='jcart-title'>{$config['text']['cartTitle']} ($this->itemCount $itemsText)</div>\n";
        echo tab(4) . "<div id='jcart-title'> <i class='fa fa-shopping-cart'></i> (<strong>" . count($this->items) . " " . $itemsText . "</strong>) </div>\n";
        echo tab(4) . "<div id='jcart-subtotal'><strong>$" . number_format($this->subtotal + $this->subtotalImpInt, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</strong></div>";
        echo tab(3) . "</div>";
        echo tab(3) . "<div id='cuerpo'>";
        echo tab(3) . "<table>\n";
        echo tab(4) . "<thead>\n";
        echo tab(5) . "<tr>\n";

        //echo tab(6) . "<th>Cod.</th>\n";
        echo tab(6) . "<th>Producto</th>\n";
        echo tab(6) . "<th>Unit c/IVA</th>\n";
        echo tab(6) . "<th>Cant.</th>\n";
        //echo tab(6) . "<th>Desc</th>\n";
        //                echo tab(6) . "<th>Alic</th>\n";
        //                echo tab(6) . "<th></th>";
        echo tab(6) . "<th>Total</th>\n";
        // echo tab(6) . "<th>&nbsp</th>\n";
        //		echo tab(7) . "<strong id='jcart-title'>{$config['text']['cartTitle']}</strong> ($this->itemCount $itemsText)\n";
        //                echo tab(7) . "<span id='jcart-subtotal'>{$config['text']['subtotal']}: <strong>$currencySymbol" . number_format($this->subtotal, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</strong></span>";
        //		echo tab(6) . "</th>\n";
        echo tab(5) . "</tr>" . "\n";

        echo tab(4) . "</thead>\n";

        //		
        echo tab(4) . "<tbody>\n";

        // If any items in the cart
        if ($this->itemCount > 0) {

            // Display line items
            foreach ($this->get_contents() as $item) {
                //evaluar las promociones si son por cantidad aca es donde hay
                //que duplicar el articulo x la cantidad en promo
                $codigoSistema =$item['id'];
                if($usaIdManual=='Si'){
                    $codigoSistema=$item['idManual'];
                }
                if($usaIdManual=='No'){
                    $codigoSistema=$item['id'];

                }
                //  echo "<pre>";
                //  print_r($item);
                //  echo "</pre>";
                if ($item['qty'] >= $item['promoCant']) {
                    if ($item['promo'] == 'si' && $item['promoTipo'] == 'Cantidad - Unidad') {
                        //duplicar los productos, solo para mostrar.

                        $cuantosGratis = floor(($item['qty'] * $item['promoPorc']) / $item['promoCant']);
                    }
                }

                echo tab(5) . "<tr>\n";
                $datosProducto ='';
                // $datosProducto .= '<span>'.$item['name'] . '</span><br>';
               
                $datosProducto .='Código: <strong>'.$codigoSistema.'</strong><br>';
                $datosProducto .='Presentación: <strong>'.$item['comoCuento'].'(x'.round($item['divisorCantidad'],0).')</strong><br>';
               
                $datosProducto .= 'Unitario: <strong>' . $currencySymbol . number_format($item['priceN'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>';
                $datosProducto .= 'Cantidad: <strong>' . $item['qty'] . '</strong><br>';
                $datosProducto .= 'Neto x Renglon: <strong>' . $currencySymbol . number_format($item['neto'] * $item['qty'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>';
                if($item['descPor']!=0):
                    $datosProducto .= 'Descuento: <strong>' . $item['descPor'] . '%</strong><br>';
                    $datosProducto .= 'Neto x Renglon: <strong>' . $currencySymbol . number_format($item['subtotalNeto'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>'; 
                endif;
                
                $datosProducto .= 'Iva (' . $item['alicuota'] . '%): <strong>' . $currencySymbol . number_format($item['subtotalIva'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>';

                echo tab(6) . "<td class='jcart-item-name' title=''>\n";

                echo tab(6) . " ";
                //if ($item['url']) {
                //    echo tab(7) . "<a  href='{$item['url']}'>{$item['name']}</a>\n";
                //} else {
                    // evaluar si muestro id manual o cod idar at, tambien poner la info que se despliegue..

                echo tab(7) ."<div class='tdNombreArticuloResumen'>";
                echo "(".$codigoSistema.") ".$item['name'];
                if ($item['descTotal'] != 0) {
                    $partes = explode(".", $item['descPor']);
                    if (isset($partes[1])) {
                        // decimal
                        echo tab(7) . "<span class='verde'>" . number_format($item['descPor'], 2) . "% off</span>\n";
                    } else {
                        // entero
                        echo tab(7) . "<span class='verde'>" . number_format($item['descPor'], 0) . "% off</span>\n";
                    }
                }
                echo tab(7)."</div>".PHP_EOL;
                echo tab(7) ."<span class='spanVerProductoInfo' idProducto='".$item['key']."'><i class='fa fa-angle-down fa-lg'></i> Mas</span>".PHP_EOL;
                echo tab(7) ."<span><a class='jcart-remove' href='?jcartRemove={$item['key']}'><i class='fa fa-trash-alt fa-lg'></i> Borrar</a></span>";
                echo tab(7) ."<div class='tdDetalleProducto' id='jcartDivInfoProducto".$item['key']."' >".$datosProducto."</div>".PHP_EOL;

                //}
                echo tab(7) . "<input name='jcartItemName[]' type='hidden' value='{$item['name']}' />\n";
               

                echo tab(6) . "</td>\n";
                echo tab(6) . "<td class='jcart-item-price'>\n";
                echo tab(6) . $currencySymbol . number_format($item['priceN'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']);
                
                echo tab(6) ."<br><strong class='".strtolower($item['comoCuento'])."'>".$item['comoCuento']."</strong>";
                echo tab(6) . "</td>\n";

                echo tab(6) . "<td class='jcart-item-qty'>\n";
                echo tab(7) . "<input name='jcartItemId[]' type='hidden' value='{$item['id']}' />\n";

                // echo tab(7) . $item['qty']."\n";
                echo tab(7) . "<input id='jcartItemQty-{$item['key']}' name='jcartItemQty[]' size='4'  type='text' value='{$item['qty']}' />\n";
                echo tab(6) . "</td>\n";
                //                echo tab(6) . "<td class='jcart-item-price'>\n";
                //                if ($item['descTotal'] != 0) {
                //                    echo tab(7) . "<span>" . $item['descPor'] . "%</span>\n";
                //                } else {
                //                    echo tab(7) . "<span>0%</span>\n";
                //                }
                //                echo tab(6) . "</td>\n";


                echo tab(6) . "<td class='jcart-item-price-tot' title=''>";

                echo tab(7) . "<span>$currencySymbol" . number_format($item['subtotal'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</span>"
                    . "<input name='jcartItemPrice[]' type='hidden' value='{$item['priceN']}' />\n";
                //echo tab(7) . "<a class='jcart-remove' href='?jcartRemove={$item['id']}'><img src='_img/action_delete.png'></a>\n";
                // echo tab(6) . "</td>\n";
                // echo tab(6) . "<td><a class='jcart-remove' href='?jcartRemove={$item['key']}'><i class='fa fa-trash-alt'></i></a>\n</td>";
                // echo tab(5) . "</tr>\n";
            }
        }

        // The cart is empty
        else {
            echo tab(5) . "<tr><td id='jcart-empty' class='vacio' colspan='7'>{$config['text']['emptyMessage']}</td></tr>\n";
        }
        echo tab(4) . "</tbody>\n";
        echo tab(3) . "</table>\n\n";
        echo tab(3) . "</div>";

        /* USUARIO VENDEDOR
         * ===================================================================== 
         */
        if ($tipoUsuario == "vendedor") {
            echo tab(3) . "<div id='pie'>";


            if ($isCheckout !== true) {
                if ($config['button']['checkout']) {
                    $inputType = "image";
                    $src = " src='{$config['button']['checkout']}' alt='{$config['text']['checkout']}' title='' ";
                }
                
                //			echo tab(4) . "<input type='$inputType' $src id='jcart-checkout' name='jcartCheckout' class='jcart-buttons-ok' alt='{$config['text']['checkout']}' title='{$config['text']['checkout']}' />\n";
            }

            echo tab(4) . "<div class='jcart-panel-uno'>";
            echo tab(5) . "<div class='textoPie'>ST Neto: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalNeto, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";

            echo tab(5) . "<div class='textoPie'>Desc Pie: </div><div class='importePie'>";
            if ($modDescPie == "Si") {
                echo tab(5) . "<input type='number' id='jcart-por-desc-pie' name='jcart-por-desc-pie' min='0' disabled max='" . $limDescPie . "' value='" . $this->porDescPie . "'>%</div>\n";
            } else {
                echo tab(5) . "<input type='number' id='jcart-por-desc-pie' name='jcart-por-desc-pie' min='0' disabled max='20' value='" . $this->porDescPie . "'>%</div>\n";
            }
            echo tab(5) . "<div class='textoPie'>Imp Desc: </div><div class='importePie verde'>($currencySymbol" . number_format(($this->importeDesc21 + $this->subtotalDesc105), $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . ")</div>\n";

            echo tab(5) . "<div class='textoPie'>ST Desc: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalDesc, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";

            if ($this->subtotalIva105 > 0) {
                echo tab(5) . "<div class='textoPie'>ST 21%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalDesc21, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
                echo tab(5) . "<div class='textoPie'>ST 10,5%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalDesc105, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            }

            echo tab(4) . "</div>";
            echo tab(4) . "<div class='jcart-panel-dos'>";
            if ($this->subtotalExento > 0) {
                echo tab(5) . "<div class='textoPie'>ST Exto: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalExento, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            }

            echo tab(5) . "<div class='textoPie'>IVA 21%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalIva21, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            if ($this->subtotalIva105 > 0) {
                echo tab(5) . "<div class='textoPie'>IVA 10,5%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalIva105, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";

                //echo tab(5) . "<span id='jcart-subtotal'>SubTotal Neto: <strong>$currencySymbol" . number_format($this->subtotalNeto, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</strong></span>\n";
            }

            echo tab(5) . "<div class='textoPie'>Imp. Int: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalImpInt, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            echo tab(5) . "<div class='textoPie'>Perc: </div><div class='importePie'>$currencySymbol" . number_format($this->percepcionesT, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            echo tab(5) . "<div class='textoPie azulImp'>Total: </div><div class='importePie azulImp'><i class='fa fa-dollar'></i> " . number_format($this->subtotal + $this->subtotalImpInt, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            echo tab(4) . "</div>";
            echo tab(4) . "<div class='jcart-panel-tres'>";
            echo tab(5) . " <div class='opcionesPie'>";
            // echo tab(5) . "<div  title='Ingresar hora de envio'> 
            echo tab(5) . "<label for='jcart-detalle'><i class='far fa-clock fa-lg fa-fw'></i> Detalle</label>";
            echo tab(5) . "<input type='text' autocomplete='off' id='jcart-detalle' required='required' placeholder='hh:mm o detalle' name='jcart-detalle' class='detallePedido'>";
            echo tab(5) . "</div>";// div detalle

            echo tab(5) . " <div class='opcionesPie'>";
            echo tab(5) . " <div  title='Seleccionar Forma de entrega'>
                            <label for='formaEntrega'><i class='fa fa-truck fa-lg fa-fw'></i> Envio </label>
                            <select name='formaEntrega' id='formaEntrega' size='2'>";

            echo tab(6) . " <option selected value='Envía por despacho'>ENVIAR A DOMICILIO</option>";

            echo tab(6) . " <option value='Retira cliente despacho'>RETIRO EN TIENDA</option>";
            //echo tab(6) . "<option selected value='Envía por despacho'>Envía por despacho</option>";
            //            echo tab(6) . "<option value='Entrega vendedor'>ENTREGA VENDEDOR</option>";
            //            echo tab(6) . "<option value='Transporte'>ENVIO POR TRANSPORTE</option>";
            //            echo tab(6) . "<option value='Reservado en depósito'>RESERVADO EN TIENDA</option>";
            echo tab(5) . " </select>";            
            echo tab(5) . "</div>"; // div envio

            //                echo tab(5) . "</br>";
            //                echo tab(5) . "<div class='texto'>";
            //                echo tab(5) . "</div>";
            // echo tab(5) . "<div  title='Ingresar hora de envio'> 
            // <label for='jcart-detalle'><i class='far fa-clock fa-lg fa-fw'></i> Detalle
            // <input type='text' autocomplete='off' id='jcart-detalle' required='required' placeholder='hh:mm o detalle' name='jcart-detalle' class='detallePedido'>
            // </label>";
            // echo tab(5) . "</div>";// div detalle

            echo tab(5) . "</div>"; // div opcionesPie caontiene estas dos opciones para usar flex

                        /* logistica */
            /* domicilios */
            //                echo var_dump($permisoClientesDom);
            $cuantosDom = sizeof($_SESSION['domicilios_cliente'])+1;
            if ($permisoClientesDom == 'Si') {
                
                echo tab(6) . '<div class="domiciliosPie" >
                                <label for="domicilio_entrega">
                                    <i class="fa fa-map-marker fa-lg fa-fw"></i> Domicilio</label>
                                        <select name="domicilio_entrega" id="domicilio_entrega" required=required title="Ingresar Domicilio de entrega" size="'.$cuantosDom.'">';
                                    
                foreach ($_SESSION['domicilios_cliente'] as $dom) {
                    echo tab(7) . '<option value="' . $dom["idDom"] . '|' . $dom["id_zona"] . '">'
                        . $dom["Calle"] . ', '
                        . $dom["NroCalle"] . ', '
                        . $dom["Dpto"] . ' - '
                        . $dom["Provincia"] . ' - '
                        . $dom["NombreDepartamento"] . ' - '
                        . $dom["NombreDistrito"] . ' - '
                        . $dom["nombre_zona"]
                        . '</option>';
                }
                echo tab(6) . ' </select>
                            
                </div>';// did del domicilio cliente. envio.

                
            }

            if ($permisoClientesDom != 'Si') {
                echo tab(6) . '<div class="domiciliosPie">
                    <label for="domicilio_entrega"><i class="fa fa-map-marker fa-lg fa-fw"></i> Domicilio </label>'
                    . '<select name="domicilio_entrega" id="domicilio_entrega" title="Ingresar Domicilio de entrega" size="'.$cuantosDom.'"> ';
                    
                foreach ($_SESSION['domicilios_cliente'] as $dom) {
                    echo tab(7) . '<option value="' . $dom["idDom"] . '|' . $dom["id_zona"] . '">'
                        . $dom["Calle"] . ' '
                        . $dom["NroCalle"] . ' '
                        . $dom["Dpto"] . ' - '
                        . $dom["Provincia"] . ' - '
                        . $dom["NombreDepartamento"] . ' - '
                        . $dom["NombreDistrito"] . ' - '
                        . $dom["nombre_zona"]
                        . '</option>';
                }
                echo tab(6) . '</select>
                </div>';
            }
            /* Logistica */

            if ($permisoLogistica == "Si") {
                echo tab(6) . '<div class="importe" title="Ingresar la hoja de ruta">
                <label for="hoja_ruta">
                <i class="fa fa-share-alt fa-lg fa-fw"></i> Ruta
                    <select name="hoja_ruta" id="hoja_ruta" required=required size="1">';                    

                echo tab(6) . '</select></div>'; // div conetiene hoja de ruta
            }


            echo tab(4) . "</div>"; // fin div jcar-pie tres
            // el boton 
            echo tab(4) . "<div class='finCarrito'>"; // div contiene boton finalizas

            echo tab(4) . "<button type='button' id='jcart-checkout' name='jcartCheckout' class='botonCheckout'>"
                    . "<i class='fa fa-check'></i> Finalizar "
                    . "</button>\n";


            echo tab(4) . "</div>"; // fin div carrito fin

            echo tab(3) . "</div>";

            // echo tab(3) . "<div id='jcart-buttons'>\n";

            // if ($config['button']['update']) {
            //     $inputType = "image";
            //     $src = " src='{$config['button']['update']}' alt='{$config['text']['update']}' title='' ";
            // }

            // echo tab(4) . "<input type='$inputType' $src name='jcartUpdateCart' value='{$config['text']['update']}' class='jcart-button' />\n";

            // if ($config['button']['empty']) {
            //     $inputType = "image";
            //     $src = " src='{$config['button']['empty']}' alt='{$config['text']['emptyButton']}' title='' ";
            // }

            // echo tab(4) . "<input type='$inputType' $src name='jcartEmpty' value='{$config['text']['emptyButton']}' class='jcart-button' />\n";

            // // fin del pie
            // echo tab(3) . "</div>\n";
        }
        /*
         * PIE DEL CLIENTE
         * =====================================================================
         */
        if ($tipoUsuario == "cliente") {
            echo tab(3) . "<div id='pie'>";


            if ($isCheckout !== true) {
                if ($config['button']['checkout']) {
                    $inputType = "image";
                    $src = " src='{$config['button']['checkout']}' alt='{$config['text']['checkout']}' title='' ";
                }
                echo tab(4) . "<button type='submit' id='jcart-checkout' name='jcartCheckout' class='botonNuevo grande azul'>"
                    . "Continuar <i class='fa fa-chevron-right'></i>"
                    . "</button>\n";
                //			echo tab(4) . "<input type='$inputType' $src id='jcart-checkout' name='jcartCheckout' class='jcart-buttons-ok' alt='{$config['text']['checkout']}' title='{$config['text']['checkout']}' />\n";
            }


            echo tab(3) . "<div id='jcart-buttons'>\n";

            if ($config['button']['update']) {
                $inputType = "image";
                $src = " src='{$config['button']['update']}' alt='{$config['text']['update']}' title='' ";
            }

            echo tab(4) . "<input type='$inputType' $src name='jcartUpdateCart' value='{$config['text']['update']}' class='jcart-button' />\n";

            if ($config['button']['empty']) {
                $inputType = "image";
                $src = " src='{$config['button']['empty']}' alt='{$config['text']['emptyButton']}' title='' ";
            }

            echo tab(4) . "<input type='$inputType' $src name='jcartEmpty' value='{$config['text']['emptyButton']}' class='jcart-button' />\n";

            // fin del pie
            echo tab(3) . "</div>\n";
        }


        echo tab(2) . "</fieldset>\n";
        echo tab(1) . "</form>\n\n";

        echo tab(1) . "<div id='jcart-tooltip'></div>\n";

        
    }

    // * fin pedido desktop



    public function display_carrito_presupuesto_mobil(){

        $config = $this->config;
        $errorMessage = null;
        $modDescPie = 0;
        $modDescPieCondVta = 'No';
        $limDescPie = 0;
        $porDescPie = 0;
        $porDescCondVta = 0;
        /*
         * Usuario VENDEDOR
         */
        if (isset($_SESSION['vendedor'])) {
            $objVendedor = $_SESSION['vendedor'];
            //echo print_r($objVendedor);
            // permiso para modificar el descuento del pie.
            if (property_exists($objVendedor, 'mod_descuento_pie')) {
                $modDescPie = $objVendedor->mod_descuento_pie;
            }
            // permiso para aplicar descuento al pie por Cond Venta
            if (property_exists($objVendedor, 'descuento_cv')) {
                $modDescPieCondVta = $objVendedor->descuento_cv;
            }
            // permiso Limite de descuento al pie
            if (property_exists($objVendedor, 'lim_desc_pie')) {
                $limDescPie = $objVendedor->lim_desc_pie;
            }
        }
        $usaIdManual = $_SESSION["usa_id_manual"];
        $tipoUsuario = $_SESSION["tipousuario"];

        /* domicilio obligatorio */

        /* domicilio obligatorio */
        $permisoClientesDom = $_SESSION["obliga_domicilio_cliente"];
        //    echo "<pre>";
        //    print_r($_SESSION);
        //    echo "</pre>";

        /* activacion de logistica */
        $permisoLogistica = $_SESSION["activ_logistica"];

        /*
         * Datos del CLIENTE
         */
        if (is_object($_SESSION['cliente'])) {
            $clienteObj = $_SESSION['cliente'];
        } else {
            $clienteObj = $_SESSION['cliente'][0];
        }
        // Descuento al pie
        if (property_exists($clienteObj, 'descPie')) {
            $porDescPie = $clienteObj->descPie;
        }
        // Descuento por Condicion de Venta del cliente
        if (property_exists($clienteObj, 'descCondVta')) {
            $porDescCondVta = $clienteObj->descCondVta;
        }
        // Permiso para utilizar el descuento por condicion de venta del cliente
        if ($modDescPieCondVta == "No") {
            // NO tengo permiso para aplicar descuento por cond venta
            $porDescCondVta = 0;
        }

        // Simplify some config variables
        $checkout = $config['checkoutPath'];
        $priceFormat = $config['priceFormat'];
        // echo "<pre>";
        // print_r($config['item']);
        // echo "</pre>";
        $id = $config['item']['id'];
        $idManual = $config['item']['idManual'];
        $name = $config['item']['name'];
        $tipoIva = $config['item']['tipoIva'];
        $alicuota = $config['item']['alicuota'];
        $iva = $config['item']['iva'];
        $impIva = $config['item']['impIva'];
        // imp interno
        $impInterno = $config['item']['impInterno'];
        $impInternoTasa = $config['item']['impInternoTasa'];
        $impInternoDescripcion = $config['item']['impInternoDescripcion'];
        $impInternoTipo = $config['item']['impInternoTipo'];
        $impInternoPorcentaje = $config['item']['impInternoPorcentaje'];
        $impInternoMontoFijo = $config['item']['impInternoMontoFijo'];
        $impInternoPesoCalculado = $config['item']['impInternoPesoCalculado'];
        $impInternoPagoMinimo = $config['item']['impInternoPagoMinimo'];
        $impInternoUnidMed = $config['item']['impInternoIdUnimed'];

        // unidad display -Bulto

        $price = $config['item']['price'];
        $priceN = $config['item']['priceN'];
        $descPor = $config['item']['descPor'];
        //$descTotal = $config['item']['descTotal'];
        $neto = $config['item']['neto'];
        $netoN = $config['item']['netoN'];
        $promo = $config['item']['promo'];
        $promoCant = $config['item']['promoCant'];
        $promoPorc = $config['item']['promoPorc'];
        $promoTipo = $config['item']['promoTipo'];

        $qty = $config['item']['qty'];
        // unidad display bulto
        $comoCuento  = $config['item']['tipoUnidadContada']; // si conte por bulto unidad o display
        $cantidadUnidadDisplay = $config['item']['cantidadUnidadDisplay']; // la unidad display a guardar
        $divisorCantidad = $config['item']['cantidadDividir']; // el divisor final, puede venir dle bulto....        
        $cantidadMinimaContada = $config['item']['cantidadUnidadMinimaContada']; // la cantidad calculada con los 

        $url = $config['item']['url'];
        $add = $config['item']['add'];



        // Use config values as literal indices for incoming POST values
        // Values are the HTML name attributes set in config.json

        // echo "POST::<pre>";
        // print_r($_POST);
        // echo "</pre>";
        if (isset($_POST[$id])) {

            $id = $_POST[$id];
            $name = $_POST[$name];
            $price = $_POST[$price];
            $idManual =$_POST[$idManual];
            if (isset($_POST[$priceN])) {
                $priceN = $_POST[$priceN];
                $netoN = $_POST[$netoN];
            } else {
                $priceN = '';
                $netoN = '';
            }
            $qty = $_POST[$qty];
            $url = $_POST[$url];
            $neto = $_POST[$neto];

            $tipoIva = $_POST[$tipoIva];
            $alicuota = $_POST[$alicuota];
            $iva = $_POST[$iva];
            $impIva = $_POST[$impIva];

            if (isset($_POST[$impInterno])) {
                $impInterno = $_POST[$impInterno];
                $impInternoT = $_POST[$impInternoTasa];
                $impInternoDescripcion = $_POST[$impInternoDescripcion];
                $impInternoTipo = $_POST[$impInternoTipo];
                $impInternoPorcentaje = $_POST[$impInternoPorcentaje];
                $impInternoMontoFijo = $_POST[$impInternoMontoFijo];
                $impInternoPesoCalculado = $_POST[$impInternoPesoCalculado];
                $impInternoPagoMinimo = $_POST[$impInternoPagoMinimo];
                $impInternoUnidMed = $_POST[$impInternoUnidMed];
            } else {
                $impInterno = '';
                $impInternoT = 0;
                $impInternoDescripcion = null;
                $impInternoTipo = null;
                $impInternoPorcentaje = null;
                $impInternoMontoFijo = null;
                $impInternoPesoCalculado = null;
                $impInternoPagoMinimo = null;
                $impInternoUnidMed = null;
            }

            // unidad - display - bulto
            $comoCuento  = $_POST[$comoCuento]; // si conte por bulto unidad o display
            $cantidadUnidadDisplay = $_POST[$cantidadUnidadDisplay];
            $divisorCantidad = $_POST[$divisorCantidad];
            $cantidadMinimaContada = $_POST[$cantidadMinimaContada];

            $promo = $_POST[$promo];
            $promoCant = $_POST[$promoCant];
            $promoPorc = $_POST[$promoPorc];
            $promoTipo = $_POST[$promoTipo];

            $descPor = $_POST[$descPor];
            if (isset($descTotal)) {
                $descTotal = $_POST[$descTotal];
            } else {
                $descTotal = '';
            }


            // Optional CSRF protection, see: http://conceptlogic.com/jcart/security.php
            $jcartToken = $_POST['jcartToken'];
        }



        // Only generate unique token once per session
        if (!isset($_SESSION['jcartToken'])) {
            $_SESSION['jcartToken'] = md5(session_id() . time() . $_SERVER['HTTP_USER_AGENT']);
        }
        // If enabled, check submitted token against session token for POST requests
        if ($config['csrfToken'] === 'true' && $_POST && $jcartToken != $_SESSION['jcartToken']) {
            $errorMessage = 'Invalid token!' . $jcartToken . ' / ' . $_SESSION['jcartToken'];
        }

        // Sanitize values for output in the browser
        $id = filter_var($id, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        //		$name  = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        $url = filter_var($url, FILTER_SANITIZE_URL);

        // Round the quantity if necessary
        if ($config['decimalPlaces'] === true) {
            $qty = round($qty, $config['decimalPlaces']);
        }

        // Add an item
        if (isset($_POST[$add])) {
            // todo armar un array asociativo con las variables porque son muchas , pasar el array armado.
            $itemAdded = $this->add_item(
                $id,
                $name,
                $price,
                $qty,
                $url,
                $iva,
                $tipoIva,
                $impIva,
                $alicuota,
                $impInterno,
                $neto,
                $descTotal,
                $descPor,
                $promo,
                $promoCant,
                $promoPorc,
                $promoTipo,
                $impInternoT,
                null,
                null,
                null,
                null,
                null,
                $impInternoDescripcion,
                $impInternoTipo,
                $impInternoPorcentaje,
                $impInternoMontoFijo,
                $impInternoPesoCalculado,
                $impInternoPagoMinimo,
                $impInternoUnidMed,
                $comoCuento, // si conte por bulto unidad o display
                $cantidadUnidadDisplay,
                $divisorCantidad,
                $cantidadMinimaContada,
                $idManual
            );
            // If not true the add item function returns the error type
            if ($itemAdded !== true) {
                $errorType = $itemAdded;
                switch ($errorType) {
                    case 'qty':
                        $errorMessage = $config['text']['quantityError'];
                        break;
                    case 'price':
                        $errorMessage = $config['text']['priceError'];
                        break;
                    case 'limite':
                        $errorMessage = $config['text']['limiteError'];
                        break;
                    case 'percepcion':
                        $errorMessage = $config['text']['percepError'];
                        break;
                }
            }
        }

        // Update a single item
        if (isset($_POST['jcartUpdate'])) {
            $itemUpdated = $this->update_item($_POST['itemId'], $_POST['itemQty']);
            if ($itemUpdated !== true) {
                $errorMessage = $config['text']['quantityError'];
            }
        }

        // Update all items in the cart
        if (isset($_POST['jcartUpdateCart']) || isset($_POST['jcartCheckout'])) {
            $cartUpdated = $this->update_cart();
            if ($cartUpdated !== true) {
                $errorMessage = $config['text']['quantityError'];
            }
        }

        // Remove an item
        /* After an item is removed, its id stays set in the query string,
          preventing the same item from being added back to the cart in
          subsequent POST requests.  As result, it's not enough to check for
          GET before deleting the item, must also check that this isn't a POST
          request. */
        //        echo var_dump($_GET['jcartRemove'] && !$_POST);
        if (isset($_GET['jcartRemove']) && !isset($_POST['jcartRemove'])) {
            //            echo "<pre>removing</pre>";
            $this->remove_item($_GET['jcartRemove']);
        }

        // Empty the cart
        if (isset($_POST['jcartEmpty'])) {
            $this->empty_cart();
        }
        /*
         * Descuentos
         * 
         * Si actualizo el carrito verifico que me traiga el descuento al pie
         * modificado o no y debo evaluar con el descuento del cliente con
         * la condicion de venta que tiene otro descuento.
         */
        if (isset($_POST['jcart-por-desc-pie']) && $_POST['jcart-por-desc-pie'] != 0) {
            $this->porDescPie = $_POST['jcart-por-desc-pie'];
        } else {
            /* evaluo aca si el descuento al pie del ciente como esta con respecto
             * al descuento por su condicion de venta.
             */
            if ($porDescPie > $porDescCondVta) {
                // descuento al pie del cliente es mayor

                $this->porDescPie = $porDescPie;
            } else {
                // descuento por la condicion de venta

                $this->porDescPie = $porDescCondVta;
            }
        }

        // Determine which text to use for the number of items in the cart
        $itemsText = $config['text']['multipleItems'];
        if ($this->itemCount == 1) {
            $itemsText = $config['text']['singleItem'];
        }

        // Determine if this is the checkout page
        /* First we check the request uri against the config checkout (set when
          the visitor first clicks checkout), then check for the hidden input
          sent with Ajax request (set when visitor has javascript enabled and
          updates an item quantity). */
        $isCheckout = strpos(request_uri(), $checkout);
        if ($isCheckout !== false || isset($_REQUEST['jcartIsCheckout']) && ($_REQUEST['jcartIsCheckout'] == 'true')) {
            $isCheckout = true;
        } else {
            $isCheckout = false;
        }

        // Overwrite the form action to post to gateway.php instead of posting back to checkout page
        if ($isCheckout === true) {

            // Sanititze config path
            $path = filter_var($config['jcartPath'], FILTER_SANITIZE_URL);

            // Trim trailing slash if necessary
            $path = rtrim($path, '/');

            $checkout = $path . '/gateway.php';
        }

        // Default input type
        // Overridden if using button images in config.php
        $inputType = 'submit';

        // If this error is true the visitor updated the cart from the checkout page using an invalid price format
        // Passed as a session var since the checkout page uses a header redirect
        // If passed via GET the query string stays set even after subsequent POST requests
        if (isset($_SESSION['quantityError']) && $_SESSION['quantityError'] === true) {
            $errorMessage = $config['text']['quantityError'];
            unset($_SESSION['quantityError']);
        }

        // Set currency symbol based on config currency code
        $currencyCode = trim(strtoupper($config['currencyCode']));
        switch ($currencyCode) {
            case 'EUR':
                $currencySymbol = '&#128;';
                break;
            case 'GBP':
                $currencySymbol = '&#163;';
                break;
            case 'JPY':
                $currencySymbol = '&#165;';
                break;
            case 'CHF':
                $currencySymbol = 'CHF&nbsp;';
                break;
            case 'SEK':
            case 'DKK':
            case 'NOK':
                $currencySymbol = 'Kr&nbsp;';
                break;
            case 'PLN':
                $currencySymbol = 'z&#322;&nbsp;';
                break;
            case 'HUF':
                $currencySymbol = 'Ft&nbsp;';
                break;
            case 'CZK':
                $currencySymbol = 'K&#269;&nbsp;';
                break;
            case 'ILS':
                $currencySymbol = '&#8362;&nbsp;';
                break;
            case 'TWD':
                $currencySymbol = 'NT$';
                break;
            case 'THB':
                $currencySymbol = '&#3647;';
                break;
            case 'MYR':
                $currencySymbol = 'RM';
                break;
            case 'PHP':
                $currencySymbol = 'Php';
                break;
            case 'BRL':
                $currencySymbol = 'R$';
                break;
            case 'USD':
            default:
                $currencySymbol = '$';
                break;
        }

        ////////////////////////////////////////////////////////////////////////
        // Output the cart
        // ////////////////////////////////////////////////////////////////////
        // Return specified number of tabs to improve readability of HTML output
        $limiteDespacho = 500;
        $validoLimiteDespacho = true;
        //$this->update_subtotal();
        //  echo "<pre>";
        //      print_R($this);
        //      echo "</pre>";

        // $this->update_subtotal();

        $valorPedido = $this->subtotal + $this->subtotalImpInt;
        // If there's an error message wrap it in some HTML
        if ($errorMessage) {
            $errorMessage = "<div id='jcart-error' class='alerta-error' >"
                . "<strong><i class='fa fa-warning'></i> Advertencia: </strong> $errorMessage</div>";
        }
        //        echo "<pre>";
        //        var_dump($valorPedido);
        //        echo "<br>";
        //        var_dump($limiteDespacho);
        //        echo "<br>";
        //        var_dump($valorPedido>$limiteDespacho);
        //        echo "</pre>";
        // Display the cart header
        echo tab(1) . "$errorMessage\n";
        echo tab(1) . "<form method='post' action='$checkout' id='jcart-form-checkout'>\n";
        echo tab(2) . "<fieldset>\n";
        echo tab(3) . "<input type='hidden' name='jcartToken' value='{$_SESSION['jcartToken']}' />\n";
        echo tab(3) . "<div id='cabeza'>";
        //echo tab(4) . "<div id='subTitulo'>";
        //echo tab(5) . "Pedido";
        // echo tab(5) .$config["text"]["cartTitle"];
        // echo tab(4) . "</div>";

        //echo tab(4) . "<img src='_img/carrito/carrito32x32.png'>";
        //                echo tab(4) . "<div id='jcart-title'>{$config['text']['cartTitle']} ($this->itemCount $itemsText)</div>\n";
        echo tab(4) . "<div id='jcart-title'> <i class='fa fa-shopping-cart'></i> (<strong>" . count($this->items) . " " . $itemsText . "</strong>) </div>\n";
        echo tab(4) . "<div id='jcart-subtotal'><strong>$" . number_format($this->subtotal + $this->subtotalImpInt, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</strong></div>";
        echo tab(3) . "</div>";
        echo tab(3) . "<div id='cuerpo'>";
        echo tab(3) . "<table>\n";
        echo tab(4) . "<thead>\n";
        echo tab(5) . "<tr>\n";

        //echo tab(6) . "<th>Cod.</th>\n";
        echo tab(6) . "<th>Producto</th>\n";
        echo tab(6) . "<th>Unit c/IVA</th>\n";
        echo tab(6) . "<th>Cant.</th>\n";
        //echo tab(6) . "<th>Desc</th>\n";
        //                echo tab(6) . "<th>Alic</th>\n";
        //                echo tab(6) . "<th></th>";
        echo tab(6) . "<th>Total</th>\n";
        // echo tab(6) . "<th>&nbsp</th>\n";
        //		echo tab(7) . "<strong id='jcart-title'>{$config['text']['cartTitle']}</strong> ($this->itemCount $itemsText)\n";
        //                echo tab(7) . "<span id='jcart-subtotal'>{$config['text']['subtotal']}: <strong>$currencySymbol" . number_format($this->subtotal, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</strong></span>";
        //		echo tab(6) . "</th>\n";
        echo tab(5) . "</tr>" . "\n";

        echo tab(4) . "</thead>\n";

        //		
        echo tab(4) . "<tbody>\n";

        // If any items in the cart
        if ($this->itemCount > 0) {

            // Display line items
            foreach ($this->get_contents() as $item) {
                //evaluar las promociones si son por cantidad aca es donde hay
                //que duplicar el articulo x la cantidad en promo
                $codigoSistema =$item['id'];
                if($usaIdManual=='Si'){
                    $codigoSistema=$item['idManual'];
                }
                if($usaIdManual=='No'){
                    $codigoSistema=$item['id'];

                }
                //  echo "<pre>";
                //  print_r($item);
                //  echo "</pre>";
                if ($item['qty'] >= $item['promoCant']) {
                    if ($item['promo'] == 'si' && $item['promoTipo'] == 'Cantidad - Unidad') {
                        //duplicar los productos, solo para mostrar.

                        $cuantosGratis = floor(($item['qty'] * $item['promoPorc']) / $item['promoCant']);
                    }
                }

                echo tab(5) . "<tr>\n";
                $datosProducto ='';
                // $datosProducto .= '<span>'.$item['name'] . '</span><br>';
               
                $datosProducto .='Código: <strong>'.$codigoSistema.'</strong><br>';
                $datosProducto .='Presentación: <strong>'.$item['comoCuento'].'(x'.round($item['divisorCantidad'],0).')</strong><br>';
               
                $datosProducto .= 'Unitario: <strong>' . $currencySymbol . number_format($item['priceN'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>';
                $datosProducto .= 'Cantidad: <strong>' . $item['qty'] . '</strong><br>';
                $datosProducto .= 'Neto x Renglon: <strong>' . $currencySymbol . number_format($item['neto'] * $item['qty'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>';
                if($item['descPor']!=0):
                    $datosProducto .= 'Descuento: <strong>' . $item['descPor'] . '%</strong><br>';
                    $datosProducto .= 'Neto x Renglon: <strong>' . $currencySymbol . number_format($item['subtotalNeto'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>'; 
                endif;
                
                $datosProducto .= 'Iva (' . $item['alicuota'] . '%): <strong>' . $currencySymbol . number_format($item['subtotalIva'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>';

                echo tab(6) . "<td class='jcart-item-name' title=''>\n";

                echo tab(6) . " ";
                //if ($item['url']) {
                //    echo tab(7) . "<a  href='{$item['url']}'>{$item['name']}</a>\n";
                //} else {
                    // evaluar si muestro id manual o cod idar at, tambien poner la info que se despliegue..

                echo tab(7) ."<div class='tdNombreArticuloResumen'>";
                echo "(".$codigoSistema.") ".$item['name'];
                if ($item['descTotal'] != 0) {
                    $partes = explode(".", $item['descPor']);
                    if (isset($partes[1])) {
                        // decimal
                        echo tab(7) . "<span class='verde'>" . number_format($item['descPor'], 2) . "% off</span>\n";
                    } else {
                        // entero
                        echo tab(7) . "<span class='verde'>" . number_format($item['descPor'], 0) . "% off</span>\n";
                    }
                }
                echo tab(7)."</div>".PHP_EOL;
                echo tab(7) ."<span class='spanVerProductoInfo' idProducto='".$item['key']."'><i class='fa fa-angle-down fa-lg'></i> Mas</span>".PHP_EOL;
                echo tab(7) ."<span><a class='jcart-remove' href='?jcartRemove={$item['key']}'><i class='fa fa-trash-alt fa-lg'></i> Borrar</a></span>";
                echo tab(7) ."<div class='tdDetalleProducto' id='jcartDivInfoProducto".$item['key']."' >".$datosProducto."</div>".PHP_EOL;

                //}
                echo tab(7) . "<input name='jcartItemName[]' type='hidden' value='{$item['name']}' />\n";
               

                echo tab(6) . "</td>\n";
                echo tab(6) . "<td class='jcart-item-price'>\n";
                echo tab(6) . $currencySymbol . number_format($item['priceN'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']);
                
                echo tab(6) ."<br><strong class='".strtolower($item['comoCuento'])."'>".$item['comoCuento']."</strong>";
                echo tab(6) . "</td>\n";

                echo tab(6) . "<td class='jcart-item-qty'>\n";
                echo tab(7) . "<input name='jcartItemId[]' type='hidden' value='{$item['id']}' />\n";

                // echo tab(7) . $item['qty']."\n";
                echo tab(7) . "<input id='jcartItemQty-{$item['key']}' name='jcartItemQty[]' size='4'  type='text' value='{$item['qty']}' />\n";
                echo tab(6) . "</td>\n";
                //                echo tab(6) . "<td class='jcart-item-price'>\n";
                //                if ($item['descTotal'] != 0) {
                //                    echo tab(7) . "<span>" . $item['descPor'] . "%</span>\n";
                //                } else {
                //                    echo tab(7) . "<span>0%</span>\n";
                //                }
                //                echo tab(6) . "</td>\n";


                echo tab(6) . "<td class='jcart-item-price-tot' title=''>";

                echo tab(7) . "<span>$currencySymbol" . number_format($item['subtotal'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</span>"
                    . "<input name='jcartItemPrice[]' type='hidden' value='{$item['priceN']}' />\n";
                //echo tab(7) . "<a class='jcart-remove' href='?jcartRemove={$item['id']}'><img src='_img/action_delete.png'></a>\n";
                // echo tab(6) . "</td>\n";
                // echo tab(6) . "<td><a class='jcart-remove' href='?jcartRemove={$item['key']}'><i class='fa fa-trash-alt'></i></a>\n</td>";
                // echo tab(5) . "</tr>\n";
            }
        }

        // The cart is empty
        else {
            echo tab(5) . "<tr><td id='jcart-empty' class='vacio' colspan='7'>{$config['text']['emptyMessage']}</td></tr>\n";
        }
        echo tab(4) . "</tbody>\n";
        echo tab(3) . "</table>\n\n";
        echo tab(3) . "</div>";

        /* USUARIO VENDEDOR
         * ===================================================================== 
         */
        if ($tipoUsuario == "vendedor") {
            echo tab(3) . "<div id='pie'>";


            if ($isCheckout !== true) {
                if ($config['button']['checkout']) {
                    $inputType = "image";
                    $src = " src='{$config['button']['checkout']}' alt='{$config['text']['checkout']}' title='' ";
                }
                
                //			echo tab(4) . "<input type='$inputType' $src id='jcart-checkout' name='jcartCheckout' class='jcart-buttons-ok' alt='{$config['text']['checkout']}' title='{$config['text']['checkout']}' />\n";
            }

            echo tab(4) . "<div class='jcart-panel-uno'>";
            echo tab(5) . "<div class='textoPie'>ST Neto: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalNeto, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";

            echo tab(5) . "<div class='textoPie'>Desc Pie: </div><div class='importePie'>";
            if ($modDescPie == "Si") {
                echo tab(5) . "<input type='number' id='jcart-por-desc-pie' name='jcart-por-desc-pie' min='0' disabled max='" . $limDescPie . "' value='" . $this->porDescPie . "'>%</div>\n";
            } else {
                echo tab(5) . "<input type='number' id='jcart-por-desc-pie' name='jcart-por-desc-pie' min='0' disabled max='20' value='" . $this->porDescPie . "'>%</div>\n";
            }
            echo tab(5) . "<div class='textoPie'>Imp Desc: </div><div class='importePie verde'>($currencySymbol" . number_format(($this->importeDesc21 + $this->subtotalDesc105), $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . ")</div>\n";

            echo tab(5) . "<div class='textoPie'>ST Desc: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalDesc, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";

            if ($this->subtotalIva105 > 0) {
                echo tab(5) . "<div class='textoPie'>ST 21%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalDesc21, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
                echo tab(5) . "<div class='textoPie'>ST 10,5%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalDesc105, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            }

            echo tab(4) . "</div>";
            echo tab(4) . "<div class='jcart-panel-dos'>";
            if ($this->subtotalExento > 0) {
                echo tab(5) . "<div class='textoPie'>ST Exto: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalExento, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            }

            echo tab(5) . "<div class='textoPie'>IVA 21%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalIva21, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            if ($this->subtotalIva105 > 0) {
                echo tab(5) . "<div class='textoPie'>IVA 10,5%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalIva105, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";

                //echo tab(5) . "<span id='jcart-subtotal'>SubTotal Neto: <strong>$currencySymbol" . number_format($this->subtotalNeto, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</strong></span>\n";
            }

            echo tab(5) . "<div class='textoPie'>Imp. Int: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalImpInt, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            echo tab(5) . "<div class='textoPie'>Perc: </div><div class='importePie'>$currencySymbol" . number_format($this->percepcionesT, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            echo tab(5) . "<div class='textoPie azulImp'>Total: </div><div class='importePie azulImp'><i class='fa fa-dollar'></i> " . number_format($this->subtotal + $this->subtotalImpInt, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            echo tab(4) . "</div>";
            echo tab(4) . "<div class='jcart-panel-tres'>";
            echo tab(5) . " <div class='opcionesPie'>";
            echo tab(5) . "<div  title='Seleccionar Forma de entrega'>
                            <label for='formaEntrega'><i class='fa fa-truck fa-lg fa-fw'></i> Envio 
                            <select name='formaEntrega' id='formaEntrega'>";

            echo tab(6) . " <option selected value='Envía por despacho'>ENVIAR A DOMICILIO</option>";

            echo tab(6) . " <option value='Retira cliente despacho'>RETIRO EN TIENDA</option>";
            //echo tab(6) . "<option selected value='Envía por despacho'>Envía por despacho</option>";
            //            echo tab(6) . "<option value='Entrega vendedor'>ENTREGA VENDEDOR</option>";
            //            echo tab(6) . "<option value='Transporte'>ENVIO POR TRANSPORTE</option>";
            //            echo tab(6) . "<option value='Reservado en depósito'>RESERVADO EN TIENDA</option>";



            echo tab(5) . " </select></label>";            
            echo tab(5) . "</div>"; // div envio

            //                echo tab(5) . "</br>";
            //                echo tab(5) . "<div class='texto'>";
            //                echo tab(5) . "</div>";
            echo tab(5) . "<div  title='Ingresar hora de envio'> 
            <label for='jcart-detalle'><i class='far fa-clock fa-lg fa-fw'></i> Detalle
            <input type='text' autocomplete='off' id='jcart-detalle' required='required' placeholder='hh:mm o detalle' name='jcart-detalle' class='detallePedido'>
            </label>";
            echo tab(5) . "</div>";// div detalle
            echo tab(5) . "</div>"; // div opcionesPie caontiene estas dos opciones para usar flex

                        /* logistica */
            /* domicilios */
            //                echo var_dump($permisoClientesDom);
            if ($permisoClientesDom == 'Si') {
                echo tab(6) . '<div class="domiciliosPie" >
                                <label for="domicilio_entrega">
                                    <i class="fa fa-map-marker fa-lg fa-fw"></i> Domicilio
                                        <select name="domicilio_entrega" id="domicilio_entrega" required=required title="Ingresar Domicilio de entrega">'
                                        . '<option value="">- domicilio- </option>';
                foreach ($_SESSION['domicilios_cliente'] as $dom) {
                    echo tab(7) . '<option value="' . $dom["idDom"] . '|' . $dom["id_zona"] . '">'
                        . $dom["Calle"] . ', '
                        . $dom["NroCalle"] . ', '
                        . $dom["Dpto"] . ' - '
                        . $dom["Provincia"] . ' - '
                        . $dom["NombreDepartamento"] . ' - '
                        . $dom["NombreDistrito"] . ' - '
                        . $dom["nombre_zona"]
                        . '</option>';
                }
                echo tab(6) . ' </select>
                            </label>
                </div>';// did del domicilio cliente. envio.

                //                                        echo str_pad(substr("Capital,Godoy Cruz,Zona Sur,Zona Este,Guaymallen,Las Heras,Lujan,Maipu,Zona Norte,San Luis",0,20),23,'.');
            }

            if ($permisoClientesDom != 'Si') {
                echo tab(6) . '<div class="domiciliosPie">
                    <label for="domicilio_entrega"><i class="fa fa-map-marker fa-lg fa-fw"></i> Domicilio'
                    . '<select name="domicilio_entrega" id="domicilio_entrega" title="Ingresar Domicilio de entrega"> '
                    . '<option value="">- domicilio - </option>';
                foreach ($_SESSION['domicilios_cliente'] as $dom) {
                    echo tab(7) . '<option value="' . $dom["idDom"] . '|' . $dom["id_zona"] . '">'
                        . $dom["Calle"] . ' '
                        . $dom["NroCalle"] . ' '
                        . $dom["Dpto"] . ' - '
                        . $dom["Provincia"] . ' - '
                        . $dom["NombreDepartamento"] . ' - '
                        . $dom["NombreDistrito"] . ' - '
                        . $dom["nombre_zona"]
                        . '</option>';
                }
                echo tab(6) . '</select></label>
                </div>';
            }
            /* Logistica */

            if ($permisoLogistica == "Si") {
                echo tab(6) . '<div class="importe" title="Ingresar la hoja de ruta">
                <label for="hoja_ruta">
                <i class="fa fa-share-alt fa-lg fa-fw"></i> Zona
                    <select name="hoja_ruta" id="hoja_ruta" required=required>'
                    . '<option value="" selected>- seleccione ruta - </option>';

                echo tab(6) . '</select></label></div>'; // div conetiene hoja de ruta
            }


            echo tab(4) . "</div>"; // fin div jcar-pie tres
            // el boton 
            echo tab(4) . "<div class='finCarrito'>"; // div contiene boton finalizas

            echo tab(4) . "<button type='button' id='jcart-checkout' name='jcartCheckout' class='botonCheckout'>"
                    . "<i class='fa fa-check'></i> Finalizar "
                    . "</button>\n";


            echo tab(4) . "</div>"; // fin div carrito fin

            echo tab(3) . "</div>";

            // echo tab(3) . "<div id='jcart-buttons'>\n";

            // if ($config['button']['update']) {
            //     $inputType = "image";
            //     $src = " src='{$config['button']['update']}' alt='{$config['text']['update']}' title='' ";
            // }

            // echo tab(4) . "<input type='$inputType' $src name='jcartUpdateCart' value='{$config['text']['update']}' class='jcart-button' />\n";

            // if ($config['button']['empty']) {
            //     $inputType = "image";
            //     $src = " src='{$config['button']['empty']}' alt='{$config['text']['emptyButton']}' title='' ";
            // }

            // echo tab(4) . "<input type='$inputType' $src name='jcartEmpty' value='{$config['text']['emptyButton']}' class='jcart-button' />\n";

            // // fin del pie
            // echo tab(3) . "</div>\n";
        }
        /*
         * PIE DEL CLIENTE
         * =====================================================================
         */
        if ($tipoUsuario == "cliente") {
            echo tab(3) . "<div id='pie'>";


            if ($isCheckout !== true) {
                if ($config['button']['checkout']) {
                    $inputType = "image";
                    $src = " src='{$config['button']['checkout']}' alt='{$config['text']['checkout']}' title='' ";
                }
                echo tab(4) . "<button type='submit' id='jcart-checkout' name='jcartCheckout' class='botonNuevo grande azul'>"
                    . "Continuar <i class='fa fa-chevron-right'></i>"
                    . "</button>\n";
                //			echo tab(4) . "<input type='$inputType' $src id='jcart-checkout' name='jcartCheckout' class='jcart-buttons-ok' alt='{$config['text']['checkout']}' title='{$config['text']['checkout']}' />\n";
            }


            echo tab(3) . "<div id='jcart-buttons'>\n";

            if ($config['button']['update']) {
                $inputType = "image";
                $src = " src='{$config['button']['update']}' alt='{$config['text']['update']}' title='' ";
            }

            echo tab(4) . "<input type='$inputType' $src name='jcartUpdateCart' value='{$config['text']['update']}' class='jcart-button' />\n";

            if ($config['button']['empty']) {
                $inputType = "image";
                $src = " src='{$config['button']['empty']}' alt='{$config['text']['emptyButton']}' title='' ";
            }

            echo tab(4) . "<input type='$inputType' $src name='jcartEmpty' value='{$config['text']['emptyButton']}' class='jcart-button' />\n";

            // fin del pie
            echo tab(3) . "</div>\n";
        }


        echo tab(2) . "</fieldset>\n";
        echo tab(1) . "</form>\n\n";

        echo tab(1) . "<div id='jcart-tooltip'></div>\n";

        echo "<script>"
            . "</script>\n";
    }

   


    // * funcion presupesto DESKTOP
    public function display_carrito_presupuesto_desktop() {

        $config = $this->config;
        $errorMessage = null;
        $modDescPie = 0;
        $modDescPieCondVta = 'No';
        $limDescPie = 0;
        $porDescPie = 0;
        $porDescCondVta = 0;
        /*
         * Usuario VENDEDOR
         */
        if (isset($_SESSION['vendedor'])) {
            $objVendedor = $_SESSION['vendedor'];
            //echo print_r($objVendedor);
            // permiso para modificar el descuento del pie.
            if (property_exists($objVendedor, 'mod_descuento_pie')) {
                $modDescPie = $objVendedor->mod_descuento_pie;
            }
            // permiso para aplicar descuento al pie por Cond Venta
            if (property_exists($objVendedor, 'descuento_cv')) {
                $modDescPieCondVta = $objVendedor->descuento_cv;
            }
            // permiso Limite de descuento al pie
            if (property_exists($objVendedor, 'lim_desc_pie')) {
                $limDescPie = $objVendedor->lim_desc_pie;
            }
        }
        $usaIdManual = $_SESSION["usa_id_manual"];
        $tipoUsuario = $_SESSION["tipousuario"];

        /* domicilio obligatorio */

        /* domicilio obligatorio */
        $permisoClientesDom = $_SESSION["obliga_domicilio_cliente"];
        //    echo "<pre>";
        //    print_r($_SESSION);
        //    echo "</pre>";

        /* activacion de logistica */
        $permisoLogistica = $_SESSION["activ_logistica"];

        /*
         * Datos del CLIENTE
         */
        if (is_object($_SESSION['cliente'])) {
            $clienteObj = $_SESSION['cliente'];
        } else {
            $clienteObj = $_SESSION['cliente'][0];
        }
        // Descuento al pie
        if (property_exists($clienteObj, 'descPie')) {
            $porDescPie = $clienteObj->descPie;
        }
        // Descuento por Condicion de Venta del cliente
        if (property_exists($clienteObj, 'descCondVta')) {
            $porDescCondVta = $clienteObj->descCondVta;
        }
        // Permiso para utilizar el descuento por condicion de venta del cliente
        if ($modDescPieCondVta == "No") {
            // NO tengo permiso para aplicar descuento por cond venta
            $porDescCondVta = 0;
        }

        // Simplify some config variables
        $checkout = $config['checkoutPath'];
        $priceFormat = $config['priceFormat'];
        // echo "<pre>";
        // print_r($config['item']);
        // echo "</pre>";
        $id = $config['item']['id'];
        $idManual = $config['item']['idManual'];
        $name = $config['item']['name'];
        $tipoIva = $config['item']['tipoIva'];
        $alicuota = $config['item']['alicuota'];
        $iva = $config['item']['iva'];
        $impIva = $config['item']['impIva'];
        $costo = $config['item']['costo'];
        // imp interno
        $impInterno = $config['item']['impInterno'];
        $impInternoTasa = $config['item']['impInternoTasa'];
        $impInternoDescripcion = $config['item']['impInternoDescripcion'];
        $impInternoTipo = $config['item']['impInternoTipo'];
        $impInternoPorcentaje = $config['item']['impInternoPorcentaje'];
        $impInternoMontoFijo = $config['item']['impInternoMontoFijo'];
        $impInternoPesoCalculado = $config['item']['impInternoPesoCalculado'];
        $impInternoPagoMinimo = $config['item']['impInternoPagoMinimo'];
        $impInternoUnidMed = $config['item']['impInternoIdUnimed'];

        // unidad display -Bulto

        $price = $config['item']['price'];
        $priceN = $config['item']['priceN'];
        $descPor = $config['item']['descPor'];
        //$descTotal = $config['item']['descTotal'];
        $neto = $config['item']['neto'];
        $netoN = $config['item']['netoN'];
        $promo = $config['item']['promo'];
        $promoCant = $config['item']['promoCant'];
        $promoPorc = $config['item']['promoPorc'];
        $promoTipo = $config['item']['promoTipo'];

        $qty = $config['item']['qty'];
        // unidad display bulto
        $comoCuento  = $config['item']['tipoUnidadContada']; // si conte por bulto unidad o display
        $cantidadUnidadDisplay = $config['item']['cantidadUnidadDisplay']; // la unidad display a guardar
        $divisorCantidad = $config['item']['cantidadDividir']; // el divisor final, puede venir dle bulto....        
        $cantidadMinimaContada = $config['item']['cantidadUnidadMinimaContada']; // la cantidad calculada con los 

        $url = $config['item']['url'];
        $add = $config['item']['add'];



        // Use config values as literal indices for incoming POST values
        // Values are the HTML name attributes set in config.json

        // echo "POST::<pre>";
        // print_r($_POST);
        // echo "</pre>";
        if (isset($_POST[$id])) {

            $id = $_POST[$id];
            $name = $_POST[$name];
            $price = $_POST[$price];
            $idManual =$_POST[$idManual];
            if (isset($_POST[$priceN])) {
                $priceN = $_POST[$priceN];
                $netoN = $_POST[$netoN];
            } else {
                $priceN = '';
                $netoN = '';
            }
            $qty = $_POST[$qty];
            $url = $_POST[$url];
            $neto = $_POST[$neto];

            $tipoIva = $_POST[$tipoIva];
            $alicuota = $_POST[$alicuota];
            $iva = $_POST[$iva];
            $impIva = $_POST[$impIva];
            $costo = $_POST[$costo];

            if (isset($_POST[$impInterno])) {
                $impInterno = $_POST[$impInterno];
                $impInternoT = $_POST[$impInternoTasa];
                $impInternoDescripcion = $_POST[$impInternoDescripcion];
                $impInternoTipo = $_POST[$impInternoTipo];
                $impInternoPorcentaje = $_POST[$impInternoPorcentaje];
                $impInternoMontoFijo = $_POST[$impInternoMontoFijo];
                $impInternoPesoCalculado = $_POST[$impInternoPesoCalculado];
                $impInternoPagoMinimo = $_POST[$impInternoPagoMinimo];
                $impInternoUnidMed = $_POST[$impInternoUnidMed];
            } else {
                $impInterno = '';
                $impInternoT = 0;
                $impInternoDescripcion = null;
                $impInternoTipo = null;
                $impInternoPorcentaje = null;
                $impInternoMontoFijo = null;
                $impInternoPesoCalculado = null;
                $impInternoPagoMinimo = null;
                $impInternoUnidMed = null;
            }

            // unidad - display - bulto
            $comoCuento  = $_POST[$comoCuento]; // si conte por bulto unidad o display
            $cantidadUnidadDisplay = $_POST[$cantidadUnidadDisplay];
            $divisorCantidad = $_POST[$divisorCantidad];
            $cantidadMinimaContada = $_POST[$cantidadMinimaContada];

            $promo = $_POST[$promo];
            $promoCant = $_POST[$promoCant];
            $promoPorc = $_POST[$promoPorc];
            $promoTipo = $_POST[$promoTipo];

            $descPor = $_POST[$descPor];
            if (isset($descTotal)) {
                $descTotal = $_POST[$descTotal];
            } else {
                $descTotal = '';
            }


            // Optional CSRF protection, see: http://conceptlogic.com/jcart/security.php
            $jcartToken = $_POST['jcartToken'];
        }



        // Only generate unique token once per session
        if (!isset($_SESSION['jcartToken'])) {
            $_SESSION['jcartToken'] = md5(session_id() . time() . $_SERVER['HTTP_USER_AGENT']);
        }
        // If enabled, check submitted token against session token for POST requests
        if ($config['csrfToken'] === 'true' && $_POST && $jcartToken != $_SESSION['jcartToken']) {
            $errorMessage = 'Invalid token!' . $jcartToken . ' / ' . $_SESSION['jcartToken'];
        }

        // Sanitize values for output in the browser
        $id = filter_var($id, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        //		$name  = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        $url = filter_var($url, FILTER_SANITIZE_URL);

        // Round the quantity if necessary
        if ($config['decimalPlaces'] === true) {
            $qty = round($qty, $config['decimalPlaces']);
        }

        // Add an item
        if (isset($_POST[$add])) {
            // todo armar un array asociativo con las variables porque son muchas , pasar el array armado.
            $itemAdded = $this->add_item(
                $id,
                $name,
                $price,
                $qty,
                $url,
                $iva,
                $tipoIva,
                $impIva,
                $alicuota,
                $impInterno,
                $neto,
                $descTotal,
                $descPor,
                $promo,
                $promoCant,
                $promoPorc,
                $promoTipo,
                $impInternoT,
                null,
                null,
                null,
                null,
                null,
                $costo,
                $impInternoDescripcion,
                $impInternoTipo,
                $impInternoPorcentaje,
                $impInternoMontoFijo,
                $impInternoPesoCalculado,
                $impInternoPagoMinimo,
                $impInternoUnidMed,
                $comoCuento, // si conte por bulto unidad o display
                $cantidadUnidadDisplay,
                $divisorCantidad,
                $cantidadMinimaContada,
                $idManual
            );
            // If not true the add item function returns the error type
            if ($itemAdded !== true) {
                $errorType = $itemAdded;
                switch ($errorType) {
                    case 'qty':
                        $errorMessage = $config['text']['quantityError'];
                        break;
                    case 'price':
                        $errorMessage = $config['text']['priceError'];
                        break;
                    case 'limite':
                        $errorMessage = $config['text']['limiteError'];
                        break;
                    case 'percepcion':
                        $errorMessage = $config['text']['percepError'];
                        break;
                }
            }
        }

        // Update a single item
        if (isset($_POST['jcartUpdate'])) {
            $itemUpdated = $this->update_item($_POST['itemId'], $_POST['itemQty']);
            if ($itemUpdated !== true) {
                $errorMessage = $config['text']['quantityError'];
            }
        }

        // Update all items in the cart
        if (isset($_POST['jcartUpdateCart']) || isset($_POST['jcartCheckout'])) {
            $cartUpdated = $this->update_cart();
            if ($cartUpdated !== true) {
                $errorMessage = $config['text']['quantityError'];
            }
        }

        // Remove an item
        /* After an item is removed, its id stays set in the query string,
          preventing the same item from being added back to the cart in
          subsequent POST requests.  As result, it's not enough to check for
          GET before deleting the item, must also check that this isn't a POST
          request. */
        //        echo var_dump($_GET['jcartRemove'] && !$_POST);
        if (isset($_GET['jcartRemove']) && !isset($_POST['jcartRemove'])) {
            //            echo "<pre>removing</pre>";
            $this->remove_item($_GET['jcartRemove']);
        }

        // Empty the cart
        if (isset($_POST['jcartEmpty'])) {
            $this->empty_cart();
        }
        /*
         * Descuentos
         * 
         * Si actualizo el carrito verifico que me traiga el descuento al pie
         * modificado o no y debo evaluar con el descuento del cliente con
         * la condicion de venta que tiene otro descuento.
         */
        if (isset($_POST['jcart-por-desc-pie']) && $_POST['jcart-por-desc-pie'] != 0) {
            $this->porDescPie = $_POST['jcart-por-desc-pie'];
        } else {
            /* evaluo aca si el descuento al pie del ciente como esta con respecto
             * al descuento por su condicion de venta.
             */
            if ($porDescPie > $porDescCondVta) {
                // descuento al pie del cliente es mayor

                $this->porDescPie = $porDescPie;
            } else {
                // descuento por la condicion de venta

                $this->porDescPie = $porDescCondVta;
            }
        }

        // Determine which text to use for the number of items in the cart
        $itemsText = $config['text']['multipleItems'];
        if ($this->itemCount == 1) {
            $itemsText = $config['text']['singleItem'];
        }

        // Determine if this is the checkout page
        /* First we check the request uri against the config checkout (set when
          the visitor first clicks checkout), then check for the hidden input
          sent with Ajax request (set when visitor has javascript enabled and
          updates an item quantity). */
        $isCheckout = strpos(request_uri(), $checkout);
        if ($isCheckout !== false || isset($_REQUEST['jcartIsCheckout']) && ($_REQUEST['jcartIsCheckout'] == 'true')) {
            $isCheckout = true;
        } else {
            $isCheckout = false;
        }

        // Overwrite the form action to post to gateway.php instead of posting back to checkout page
        if ($isCheckout === true) {

            // Sanititze config path
            $path = filter_var($config['jcartPath'], FILTER_SANITIZE_URL);

            // Trim trailing slash if necessary
            $path = rtrim($path, '/');

            $checkout = $path . '/gateway.php';
        }

        // Default input type
        // Overridden if using button images in config.php
        $inputType = 'submit';

        // If this error is true the visitor updated the cart from the checkout page using an invalid price format
        // Passed as a session var since the checkout page uses a header redirect
        // If passed via GET the query string stays set even after subsequent POST requests
        if (isset($_SESSION['quantityError']) && $_SESSION['quantityError'] === true) {
            $errorMessage = $config['text']['quantityError'];
            unset($_SESSION['quantityError']);
        }

        // Set currency symbol based on config currency code
        $currencyCode = trim(strtoupper($config['currencyCode']));
        switch ($currencyCode) {
            case 'EUR':
                $currencySymbol = '&#128;';
                break;
            case 'GBP':
                $currencySymbol = '&#163;';
                break;
            case 'JPY':
                $currencySymbol = '&#165;';
                break;
            case 'CHF':
                $currencySymbol = 'CHF&nbsp;';
                break;
            case 'SEK':
            case 'DKK':
            case 'NOK':
                $currencySymbol = 'Kr&nbsp;';
                break;
            case 'PLN':
                $currencySymbol = 'z&#322;&nbsp;';
                break;
            case 'HUF':
                $currencySymbol = 'Ft&nbsp;';
                break;
            case 'CZK':
                $currencySymbol = 'K&#269;&nbsp;';
                break;
            case 'ILS':
                $currencySymbol = '&#8362;&nbsp;';
                break;
            case 'TWD':
                $currencySymbol = 'NT$';
                break;
            case 'THB':
                $currencySymbol = '&#3647;';
                break;
            case 'MYR':
                $currencySymbol = 'RM';
                break;
            case 'PHP':
                $currencySymbol = 'Php';
                break;
            case 'BRL':
                $currencySymbol = 'R$';
                break;
            case 'USD':
            default:
                $currencySymbol = '$';
                break;
        }

        ////////////////////////////////////////////////////////////////////////
        // Output the cart
        // ////////////////////////////////////////////////////////////////////
        // Return specified number of tabs to improve readability of HTML output
        $limiteDespacho = 500;
        $validoLimiteDespacho = true;
        //$this->update_subtotal();
        //  echo "<pre>";
        //      print_R($this);
        //      echo "</pre>";

        // $this->update_subtotal();

        $valorPedido = $this->subtotal + $this->subtotalImpInt;
        // If there's an error message wrap it in some HTML
        if ($errorMessage) {
            $errorMessage = "<div id='jcart-error' class='alerta-error' >"
                . "<strong><i class='fa fa-warning'></i> Advertencia: </strong> $errorMessage</div>";
        }
        //        echo "<pre>";
        //        var_dump($valorPedido);
        //        echo "<br>";
        //        var_dump($limiteDespacho);
        //        echo "<br>";
        //        var_dump($valorPedido>$limiteDespacho);
        //        echo "</pre>";
        // Display the cart header
        echo tab(1) . "$errorMessage\n";
        echo tab(1) . "<form method='post' action='$checkout' id='jcart-form-checkout'>\n";
        echo tab(2) . "<fieldset>\n";
        echo tab(3) . "<input type='hidden' name='jcartToken' value='{$_SESSION['jcartToken']}' />\n";
        echo tab(3) . "<div id='cabeza'>";
        //echo tab(4) . "<div id='subTitulo'>";
        //echo tab(5) . "Pedido";
        // echo tab(5) .$config["text"]["cartTitle"];
        // echo tab(4) . "</div>";

        //echo tab(4) . "<img src='_img/carrito/carrito32x32.png'>";
        //                echo tab(4) . "<div id='jcart-title'>{$config['text']['cartTitle']} ($this->itemCount $itemsText)</div>\n";
        echo tab(4) . "<div id='jcart-title'> <i class='fa fa-shopping-cart'></i> (<strong>" . count($this->items) . " " . $itemsText . "</strong>) </div>\n";
        echo tab(4) . "<div id='jcart-subtotal'><strong>$" . number_format($this->subtotal + $this->subtotalImpInt, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</strong></div>";
        echo tab(3) . "</div>";
        echo tab(3) . "<div id='cuerpo'>";
        echo tab(3) . "<table>\n";
        echo tab(4) . "<thead>\n";
        echo tab(5) . "<tr>\n";

        //echo tab(6) . "<th>Cod.</th>\n";
        echo tab(6) . "<th>Producto</th>\n";
        echo tab(6) . "<th>Unit c/IVA</th>\n";
        echo tab(6) . "<th>Cant.</th>\n";
        //echo tab(6) . "<th>Desc</th>\n";
        //                echo tab(6) . "<th>Alic</th>\n";
        //                echo tab(6) . "<th></th>";
        echo tab(6) . "<th>Total</th>\n";
        // echo tab(6) . "<th>&nbsp</th>\n";
        //		echo tab(7) . "<strong id='jcart-title'>{$config['text']['cartTitle']}</strong> ($this->itemCount $itemsText)\n";
        //                echo tab(7) . "<span id='jcart-subtotal'>{$config['text']['subtotal']}: <strong>$currencySymbol" . number_format($this->subtotal, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</strong></span>";
        //		echo tab(6) . "</th>\n";
        echo tab(5) . "</tr>" . "\n";

        echo tab(4) . "</thead>\n";

        //		
        echo tab(4) . "<tbody>\n";

        // If any items in the cart
        if ($this->itemCount > 0) {

            // Display line items
            foreach ($this->get_contents() as $item) {
                //evaluar las promociones si son por cantidad aca es donde hay
                //que duplicar el articulo x la cantidad en promo
                $codigoSistema =$item['id'];
                if($usaIdManual=='Si'){
                    $codigoSistema=$item['idManual'];
                }
                if($usaIdManual=='No'){
                    $codigoSistema=$item['id'];

                }
                //  echo "<pre>";
                //  print_r($item);
                //  echo "</pre>";
                if ($item['qty'] >= $item['promoCant']) {
                    if ($item['promo'] == 'si' && $item['promoTipo'] == 'Cantidad - Unidad') {
                        //duplicar los productos, solo para mostrar.

                        $cuantosGratis = floor(($item['qty'] * $item['promoPorc']) / $item['promoCant']);
                    }
                }

                echo tab(5) . "<tr>\n";
                $datosProducto ='';
                // $datosProducto .= '<span>'.$item['name'] . '</span><br>';
               
                $datosProducto .='Código: <strong>'.$codigoSistema.'</strong><br>';
                $datosProducto .='Presentación: <strong>'.$item['comoCuento'].'(x'.round($item['divisorCantidad'],0).')</strong><br>';
               
                $datosProducto .= 'Unitario: <strong>' . $currencySymbol . number_format($item['priceN'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>';
                $datosProducto .= 'Cantidad: <strong>' . $item['qty'] . '</strong><br>';
                $datosProducto .= 'Neto x Renglon: <strong>' . $currencySymbol . number_format($item['neto'] * $item['qty'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>';
                if($item['descPor']!=0):
                    $datosProducto .= 'Descuento: <strong>' . $item['descPor'] . '%</strong><br>';
                    $datosProducto .= 'Neto x Renglon: <strong>' . $currencySymbol . number_format($item['subtotalNeto'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>'; 
                endif;
                
                $datosProducto .= 'Iva (' . $item['alicuota'] . '%): <strong>' . $currencySymbol . number_format($item['subtotalIva'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']).'</strong><br>';

                echo tab(6) . "<td class='jcart-item-name' title=''>\n";

                echo tab(6) . " ";
                //if ($item['url']) {
                //    echo tab(7) . "<a  href='{$item['url']}'>{$item['name']}</a>\n";
                //} else {
                    // evaluar si muestro id manual o cod idar at, tambien poner la info que se despliegue..

                echo tab(7) ."<div class='tdNombreArticuloResumen'>";
                echo "(".$codigoSistema.") ".$item['name'];
                if ($item['descTotal'] != 0) {
                    $partes = explode(".", $item['descPor']);
                    if (isset($partes[1])) {
                        // decimal
                        echo tab(7) . "<span class='verde'>" . number_format($item['descPor'], 2) . "% off</span>\n";
                    } else {
                        // entero
                        echo tab(7) . "<span class='verde'>" . number_format($item['descPor'], 0) . "% off</span>\n";
                    }
                }
                echo tab(7)."</div>".PHP_EOL;
                echo tab(7) ."<span class='spanVerProductoInfo' idProducto='".$item['key']."'><i class='fa fa-angle-down fa-lg'></i> Mas</span>".PHP_EOL;
                echo tab(7) ."<span><a class='jcart-remove' href='?jcartRemove={$item['key']}'><i class='fa fa-trash-alt fa-lg'></i> Borrar</a></span>";
                echo tab(7) ."<div class='tdDetalleProducto' id='jcartDivInfoProducto".$item['key']."' >".$datosProducto."</div>".PHP_EOL;

                //}
                echo tab(7) . "<input name='jcartItemName[]' type='hidden' value='{$item['name']}' />\n";
               

                echo tab(6) . "</td>\n";
                echo tab(6) . "<td class='jcart-item-price'>\n";
                echo tab(6) . $currencySymbol . number_format($item['priceN'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']);
                
                echo tab(6) ."<br><strong class='".strtolower($item['comoCuento'])."'>".$item['comoCuento']."</strong>";
                echo tab(6) . "</td>\n";

                echo tab(6) . "<td class='jcart-item-qty'>\n";
                echo tab(7) . "<input name='jcartItemId[]' type='hidden' value='{$item['id']}' />\n";

                // echo tab(7) . $item['qty']."\n";
                echo tab(7) . "<input id='jcartItemQty-{$item['key']}' name='jcartItemQty[]' size='4'  type='text' value='{$item['qty']}' />\n";
                echo tab(6) . "</td>\n";
                //                echo tab(6) . "<td class='jcart-item-price'>\n";
                //                if ($item['descTotal'] != 0) {
                //                    echo tab(7) . "<span>" . $item['descPor'] . "%</span>\n";
                //                } else {
                //                    echo tab(7) . "<span>0%</span>\n";
                //                }
                //                echo tab(6) . "</td>\n";


                echo tab(6) . "<td class='jcart-item-price-tot' title=''>";

                echo tab(7) . "<span>$currencySymbol" . number_format($item['subtotal'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</span>"
                    . "<input name='jcartItemPrice[]' type='hidden' value='{$item['priceN']}' />\n";
                //echo tab(7) . "<a class='jcart-remove' href='?jcartRemove={$item['id']}'><img src='_img/action_delete.png'></a>\n";
                // echo tab(6) . "</td>\n";
                // echo tab(6) . "<td><a class='jcart-remove' href='?jcartRemove={$item['key']}'><i class='fa fa-trash-alt'></i></a>\n</td>";
                // echo tab(5) . "</tr>\n";
            }
        }

        // The cart is empty
        else {
            echo tab(5) . "<tr><td id='jcart-empty' class='vacio' colspan='7'>{$config['text']['emptyMessage']}</td></tr>\n";
        }
        echo tab(4) . "</tbody>\n";
        echo tab(3) . "</table>\n\n";
        echo tab(3) . "</div>";

        /* USUARIO VENDEDOR
         * ===================================================================== 
         */
        if ($tipoUsuario == "vendedor") {
            echo tab(3) . "<div id='pie'>";


            if ($isCheckout !== true) {
                if ($config['button']['checkout']) {
                    $inputType = "image";
                    $src = " src='{$config['button']['checkout']}' alt='{$config['text']['checkout']}' title='' ";
                }
                
                //			echo tab(4) . "<input type='$inputType' $src id='jcart-checkout' name='jcartCheckout' class='jcart-buttons-ok' alt='{$config['text']['checkout']}' title='{$config['text']['checkout']}' />\n";
            }

            echo tab(4) . "<div class='jcart-panel-uno'>";
            echo tab(5) . "<div class='textoPie'>ST Neto: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalNeto, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";

            echo tab(5) . "<div class='textoPie'>Desc Pie: </div><div class='importePie'>";
            if ($modDescPie == "Si") {
                echo tab(5) . "<input type='number' id='jcart-por-desc-pie' name='jcart-por-desc-pie' min='0' disabled max='" . $limDescPie . "' value='" . $this->porDescPie . "'>%</div>\n";
            } else {
                echo tab(5) . "<input type='number' id='jcart-por-desc-pie' name='jcart-por-desc-pie' min='0' disabled max='20' value='" . $this->porDescPie . "'>%</div>\n";
            }
            echo tab(5) . "<div class='textoPie'>Imp Desc: </div><div class='importePie verde'>($currencySymbol" . number_format(($this->importeDesc21 + $this->subtotalDesc105), $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . ")</div>\n";

            echo tab(5) . "<div class='textoPie'>ST Desc: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalDesc, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";

            if ($this->subtotalIva105 > 0) {
                echo tab(5) . "<div class='textoPie'>ST 21%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalDesc21, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
                echo tab(5) . "<div class='textoPie'>ST 10,5%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalDesc105, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            }

            echo tab(4) . "</div>";
            echo tab(4) . "<div class='jcart-panel-dos'>";
            if ($this->subtotalExento > 0) {
                echo tab(5) . "<div class='textoPie'>ST Exto: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalExento, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            }

            echo tab(5) . "<div class='textoPie'>IVA 21%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalIva21, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            if ($this->subtotalIva105 > 0) {
                echo tab(5) . "<div class='textoPie'>IVA 10,5%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalIva105, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";

                //echo tab(5) . "<span id='jcart-subtotal'>SubTotal Neto: <strong>$currencySymbol" . number_format($this->subtotalNeto, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</strong></span>\n";
            }

            echo tab(5) . "<div class='textoPie'>Imp. Int: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalImpInt, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            echo tab(5) . "<div class='textoPie'>Perc: </div><div class='importePie'>$currencySymbol" . number_format($this->percepcionesT, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            echo tab(5) . "<div class='textoPie azulImp'>Total: </div><div class='importePie azulImp'><i class='fa fa-dollar'></i> " . number_format($this->subtotal + $this->subtotalImpInt, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
            echo tab(4) . "</div>";
            echo tab(4) . "<div class='jcart-panel-tres'>";
            echo tab(5) . " <div class='opcionesPie'>";
            echo tab(5) . "<div  title='Seleccionar Forma de entrega'>
                            <label for='formaEntrega'><i class='fa fa-truck fa-lg fa-fw'></i> Envio 
                            <select name='formaEntrega' id='formaEntrega'>";

            echo tab(6) . " <option selected value='Envía por despacho'>ENVIAR A DOMICILIO</option>";

            echo tab(6) . " <option value='Retira cliente despacho'>RETIRO EN TIENDA</option>";
            //echo tab(6) . "<option selected value='Envía por despacho'>Envía por despacho</option>";
            //            echo tab(6) . "<option value='Entrega vendedor'>ENTREGA VENDEDOR</option>";
            //            echo tab(6) . "<option value='Transporte'>ENVIO POR TRANSPORTE</option>";
            //            echo tab(6) . "<option value='Reservado en depósito'>RESERVADO EN TIENDA</option>";



            echo tab(5) . " </select></label>";            
            echo tab(5) . "</div>"; // div envio

            //                echo tab(5) . "</br>";
            //                echo tab(5) . "<div class='texto'>";
            //                echo tab(5) . "</div>";
            echo tab(5) . "<div  title='Ingresar hora de envio'> 
            <label for='jcart-detalle'><i class='far fa-clock fa-lg fa-fw'></i> Detalle
            <input type='text' autocomplete='off' id='jcart-detalle' required='required' placeholder='hh:mm o detalle' name='jcart-detalle' class='detallePedido'>
            </label>";
            echo tab(5) . "</div>";// div detalle
            echo tab(5) . "</div>"; // div opcionesPie caontiene estas dos opciones para usar flex

                        /* logistica */
            /* domicilios */
            //                echo var_dump($permisoClientesDom);
            // if ($permisoClientesDom == 'Si') {
            //     echo tab(6) . '<div class="domiciliosPie" >
            //                     <label for="domicilio_entrega">
            //                         <i class="fa fa-map-marker fa-lg fa-fw"></i> Domicilio
            //                             <select name="domicilio_entrega" id="domicilio_entrega" required=required title="Ingresar Domicilio de entrega">'
            //                             . '<option value="">- domicilio- </option>';
            //     foreach ($_SESSION['domicilios_cliente'] as $dom) {
            //         echo tab(7) . '<option value="' . $dom["idDom"] . '|' . $dom["id_zona"] . '">'
            //             . $dom["Calle"] . ', '
            //             . $dom["NroCalle"] . ', '
            //             . $dom["Dpto"] . ' - '
            //             . $dom["Provincia"] . ' - '
            //             . $dom["NombreDepartamento"] . ' - '
            //             . $dom["NombreDistrito"] . ' - '
            //             . $dom["nombre_zona"]
            //             . '</option>';
            //     }
            //     echo tab(6) . ' </select>
            //                 </label>
            //     </div>';// did del domicilio cliente. envio.

            //     //                                        echo str_pad(substr("Capital,Godoy Cruz,Zona Sur,Zona Este,Guaymallen,Las Heras,Lujan,Maipu,Zona Norte,San Luis",0,20),23,'.');
            // }

            // if ($permisoClientesDom != 'Si') {
            //     echo tab(6) . '<div class="domiciliosPie">
            //         <label for="domicilio_entrega"><i class="fa fa-map-marker fa-lg fa-fw"></i> Domicilio'
            //         . '<select name="domicilio_entrega" id="domicilio_entrega" title="Ingresar Domicilio de entrega"> '
            //         . '<option value="">- domicilio - </option>';
            //     foreach ($_SESSION['domicilios_cliente'] as $dom) {
            //         echo tab(7) . '<option value="' . $dom["idDom"] . '|' . $dom["id_zona"] . '">'
            //             . $dom["Calle"] . ' '
            //             . $dom["NroCalle"] . ' '
            //             . $dom["Dpto"] . ' - '
            //             . $dom["Provincia"] . ' - '
            //             . $dom["NombreDepartamento"] . ' - '
            //             . $dom["NombreDistrito"] . ' - '
            //             . $dom["nombre_zona"]
            //             . '</option>';
            //     }
            //     echo tab(6) . '</select></label>
            //     </div>';
            // }
            /* Logistica */

            // if ($permisoLogistica == "Si") {
            //     echo tab(6) . '<div class="importe" title="Ingresar la hoja de ruta">
            //     <label for="hoja_ruta">
            //     <i class="fa fa-share-alt fa-lg fa-fw"></i> Zona
            //         <select name="hoja_ruta" id="hoja_ruta" required=required>'
            //         . '<option value="" selected>- seleccione ruta - </option>';

            //     echo tab(6) . '</select></label></div>'; // div conetiene hoja de ruta
            // }


            echo tab(4) . "</div>"; // fin div jcar-pie tres
            // el boton 
            echo tab(4) . "<div class='finCarrito'>"; // div contiene boton finalizas

            echo tab(4) . "<button type='button' id='jcart-checkout' name='jcartCheckout' class='botonCheckout'>"
                    . "<i class='fa fa-check'></i> Finalizar "
                    . "</button>\n";


            echo tab(4) . "</div>"; // fin div carrito fin

            echo tab(3) . "</div>";

            // echo tab(3) . "<div id='jcart-buttons'>\n";

            // if ($config['button']['update']) {
            //     $inputType = "image";
            //     $src = " src='{$config['button']['update']}' alt='{$config['text']['update']}' title='' ";
            // }

            // echo tab(4) . "<input type='$inputType' $src name='jcartUpdateCart' value='{$config['text']['update']}' class='jcart-button' />\n";

            // if ($config['button']['empty']) {
            //     $inputType = "image";
            //     $src = " src='{$config['button']['empty']}' alt='{$config['text']['emptyButton']}' title='' ";
            // }

            // echo tab(4) . "<input type='$inputType' $src name='jcartEmpty' value='{$config['text']['emptyButton']}' class='jcart-button' />\n";

            // // fin del pie
            // echo tab(3) . "</div>\n";
        }
        /*
         * PIE DEL CLIENTE
         * =====================================================================
         */
        if ($tipoUsuario == "cliente") {
            echo tab(3) . "<div id='pie'>";


            if ($isCheckout !== true) {
                if ($config['button']['checkout']) {
                    $inputType = "image";
                    $src = " src='{$config['button']['checkout']}' alt='{$config['text']['checkout']}' title='' ";
                }
                echo tab(4) . "<button type='submit' id='jcart-checkout' name='jcartCheckout' class='botonNuevo grande azul'>"
                    . "Continuar <i class='fa fa-chevron-right'></i>"
                    . "</button>\n";
                //			echo tab(4) . "<input type='$inputType' $src id='jcart-checkout' name='jcartCheckout' class='jcart-buttons-ok' alt='{$config['text']['checkout']}' title='{$config['text']['checkout']}' />\n";
            }


            echo tab(3) . "<div id='jcart-buttons'>\n";

            if ($config['button']['update']) {
                $inputType = "image";
                $src = " src='{$config['button']['update']}' alt='{$config['text']['update']}' title='' ";
            }

            echo tab(4) . "<input type='$inputType' $src name='jcartUpdateCart' value='{$config['text']['update']}' class='jcart-button' />\n";

            if ($config['button']['empty']) {
                $inputType = "image";
                $src = " src='{$config['button']['empty']}' alt='{$config['text']['emptyButton']}' title='' ";
            }

            echo tab(4) . "<input type='$inputType' $src name='jcartEmpty' value='{$config['text']['emptyButton']}' class='jcart-button' />\n";

            // fin del pie
            echo tab(3) . "</div>\n";
        }


        echo tab(2) . "</fieldset>\n";
        echo tab(1) . "</form>\n\n";

        echo tab(1) . "<div id='jcart-tooltip'></div>\n";
    }
    // * fin presupuesto DESKTOP

    
    public function display_carrito_remito_talonario_mobil(){

        $config = $this->config;
        $errorMessage = null;
        $tipoForm = $_SESSION['formulario'];
        $modDescPie = 0;
        $modDescPieCondVta = 'No';
        $limDescPie = 0;
        $porDescPie = 0;
        $porDescCondVta = 0;

        /*
         * Usuario VENDEDOR
         */

        if (isset($_SESSION['vendedor'])) {
            $objVendedor = $_SESSION['vendedor'];
            //echo print_r($objVendedor);
            // permiso para modificar el descuento del pie.
            if (property_exists($objVendedor, 'mod_descuento_pie')) {
                $modDescPie = $objVendedor->mod_descuento_pie;
            }
            // permiso para aplicar descuento al pie por Cond Venta
            if (property_exists($objVendedor, 'descuento_cv')) {
                $modDescPieCondVta = $objVendedor->descuento_cv;
            }
            // permiso Limite de descuento al pie
            if (property_exists($objVendedor, 'lim_desc_pie')) {
                $limDescPie = $objVendedor->lim_desc_pie;
            }
        }

        /*
         * Datos del CLIENTE
         */
        if (is_object($_SESSION['cliente'])) {
            $clienteObj = $_SESSION['cliente'];
        } else {
            $clienteObj = $_SESSION['cliente'][0];
        }

        // Descuento al pie
        if (property_exists($clienteObj, 'descPie')) {
            $porDescPie = $clienteObj->descPie;
        }
        // Descuento por Condicion de Venta del cliente
        if (property_exists($clienteObj, 'descCondVta')) {
            $porDescCondVta = $clienteObj->descCondVta;
        }

        // Permiso para utilizar el descuento por condicion de venta del cliente
        if ($modDescPieCondVta == "No") {
            // NO tengo permiso para aplicar descuento por cond venta
            $porDescCondVta = 0;
        }


        // Simplify some config variables
        if ($tipoForm == "remitoTalonario") {
            $config['checkoutPath'] = 'alta-remito-talonario-confirmado.php';
        } else {
            $config['checkoutPath'] = 'alta-remito-sistema-confirmado.php';
        }
        $config['text']['emptyMessage'] = 'El remito esta vacío';
        $checkout = $config['checkoutPath'];
        $priceFormat = $config['priceFormat'];

        $id = $config['item']['id'];
        $name = $config['item']['name'];
        $tipoIva = $config['item']['tipoIva'];
        $alicuota = $config['item']['alicuota'];
        $iva = $config['item']['iva'];
        $impIva = $config['item']['impIva'];
        $impInterno = $config['item']['impInterno'];
        $price = $config['item']['price'];
        $priceN = $config['item']['priceN'];
        $descPor = $config['item']['descPor'];
        //        $descTotal = $config['item']['descTotal'];
        $neto = $config['item']['neto'];
        $netoN = $config['item']['netoN'];
        $promo = $config['item']['promo'];
        $promoCant = $config['item']['promoCant'];
        $promoPorc = $config['item']['promoPorc'];
        $promoTipo = $config['item']['promoTipo'];
        $impInternoTasa = $config['item']['impInternoTasa'];
        $lote = $config['item']['lote'];
        $idLote = $config['item']['idLote'];
        $nomLote = $config['item']['nomLote'];
        $deposito = $config['item']['deposito'];
        $entregado = $config['item']['entregado'];
        $qty = $config['item']['qty'];
        $url = $config['item']['url'];
        $add = $config['item']['add'];

        // Use config values as literal indices for incoming POST values
        // Values are the HTML name attributes set in config.json
        //echo print_r($_POST);

        if (isset($_POST['jcart-fecha-rem-clon']) && $_POST['jcart-fecha-rem-clon'] != "") {
            $this->fechaTalonario = $_POST['jcart-fecha-rem-clon'];
        } else {
            $this->fechaTalonario = date("Y-m-d");
        }

        if (isset($_POST['jcart-suc-clon']) && $_POST['jcart-suc-clon'] != "") {
            $this->pventaTalonario = $_POST['jcart-suc-clon'];
        }
        if (isset($_POST['jcart-nro-rem-clon']) && ($_POST['jcart-nro-rem-clon'] != "" || $_POST['jcart-nro-rem-clon'] != 0)) {
            $this->nroTalonario = $_POST['jcart-nro-rem-clon'];
        }
        /*
         * Descuentos
         * 
         * Si actualizo el carrito verifico que me traiga el descuento al pie
         * modificado o no y debo evaluar con el descuento del cliente con
         * la condicion de venta que tiene otro descuento.
         */
        if (isset($_POST['jcart-por-desc-pie']) && $_POST['jcart-por-desc-pie'] != 0) {
            $this->porDescPie = $_POST['jcart-por-desc-pie'];
        } else {
            /* evaluo aca si el descuento al pie del ciente como esta con respecto
             * al descuento por su condicion de venta.
             */
            if ($porDescPie > $porDescCondVta) {
                // descuento al pie del cliente es mayor

                $this->porDescPie = $porDescPie;
            } else {
                // descuento por la condicion de venta

                $this->porDescPie = $porDescCondVta;
            }
        }

        // echo "<pre>";
        // print_r($_POST);
        // echo "</pre>";
        if (isset($_POST[$id])) {

            $id = $_POST[$id];
            $idManual = $_POST[$id];
            $name = $_POST[$name];
            $price = $_POST[$price];
            if (isset($_POST[$priceN])) {
                $priceN = $_POST[$priceN];
                $netoN = $_POST[$netoN];
            } else {
                $priceN = '';
                $netoN = '';
            }
            // $priceN = $_POST[$priceN];
            $qty = $_POST[$qty];
            $url = $_POST[$url];
            $neto = $_POST[$neto];
            //$netoN = $_POST[$netoN];
            $tipoIva = $_POST[$tipoIva];
            $alicuota = $_POST[$alicuota];
            $iva = $_POST[$iva];
            $impIva = $_POST[$impIva];
            $impInterno = $_POST[$impInterno];
            $promo = $_POST[$promo];
            $promoCant = $_POST[$promoCant];
            $promoPorc = $_POST[$promoPorc];
            $promoTipo = $_POST[$promoTipo];

            $impInternoT = $_POST[$impInternoTasa];




            $descPor = $_POST[$descPor];
            //            if(isset($_POST[$descTotal])){
            //                $descTotal = $_POST[$descTotal];
            //            }else{
            //                $descTotal=0;
            //            }
            if (isset($_POST[$lote]) && isset($_POST[$idLote]) && isset($_POST[$nomLote])) {
                $lote = $_POST[$lote];
                $idLote = $_POST[$idLote];
                $nombLote = $_POST[$nomLote];
            } else {
                $lote = null;
                $idLote = null;
                $nombLote = null;
            }
            $deposito = $_POST[$deposito];
            $entregado = $_POST[$entregado];




            // Optional CSRF protection, see: http://conceptlogic.com/jcart/security.php
            $jcartToken = $_POST['jcartToken'];
        }
        // Only generate unique token once per session
        if (!isset($_SESSION['jcartToken'])) {
            $_SESSION['jcartToken'] = md5(session_id() . time() . $_SERVER['HTTP_USER_AGENT']);
        }
        // If enabled, check submitted token against session token for POST requests
        if ($config['csrfToken'] === 'true' && $_POST && $jcartToken != $_SESSION['jcartToken']) {
            $errorMessage = 'Invalid token!' . $jcartToken . ' / ' . $_SESSION['jcartToken'];
        }

        // Sanitize values for output in the browser
        $id = filter_var($id, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        //		$name  = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        $url = filter_var($url, FILTER_SANITIZE_URL);

        // Round the quantity if necessary
        if ($config['decimalPlaces'] === true) {
            $qty = round($qty, $config['decimalPlaces']);
        }


        // codigo para ingresar la fecha el pv y el numero
        // Add an item

        if (isset($_POST[$add])) {

            $itemAdded = $this->add_item($id, $name, $price, $qty, $url, $iva, $tipoIva, $impIva, $alicuota, $impInterno, $neto, 0, $descPor, $promo, $promoCant, $promoPorc, $promoTipo, $impInternoT, $lote, $idLote, $nombLote, $deposito, $entregado,$idManual);
            // If not true the add item function returns the error type
            if ($itemAdded !== true) {
                $errorType = $itemAdded;
                switch ($errorType) {
                    case 'qty':
                        $errorMessage = $config['text']['quantityError'];
                        break;
                    case 'price':
                        $errorMessage = $config['text']['priceError'];
                        break;
                    case 'limite':
                        $errorMessage = $config['text']['limiteError'];
                        break;
                }
            }
        }

        // Update a single item
        if (isset($_POST['jcartUpdate'])) {
            $itemUpdated = $this->update_item($_POST['itemId'], $_POST['itemQty']);
            if ($itemUpdated !== true) {
                $errorMessage = $config['text']['quantityError'];
            }
        }
        //Descuento al pie
        /*
         * Voy a crear una funcion nueva para actualizar todo solo modifico el
         * valor de porDescPie y luego recacular el subtotal al pie.
         */



        // Update all items in the cart
        if (isset($_POST['jcartUpdateCart']) || isset($_POST['jcartCheckout'])) {
            $cartUpdated = $this->update_cart();
            if ($cartUpdated !== true) {
                $errorMessage = $config['text']['quantityError'];
            }
        }

        // Remove an item
        /* After an item is removed, its id stays set in the query string,
          preventing the same item from being added back to the cart in
          subsequent POST requests.  As result, it's not enough to check for
          GET before deleting the item, must also check that this isn't a POST
          request. */
        //        echo "<pre>";
        //        var_dump($_POST['jcartRemove']);
        //        var_dump($_GET['jcartRemove']);
        //        var_dump($_GET['jcartRemove'] && !$_POST['jcartRemove']);
        //        echo "</pre>";
        if (isset($_GET['jcartRemove']) && !isset($_POST['jcartRemove'])) {

            $this->remove_item($_GET['jcartRemove']);
        }

        // Empty the cart
        if (isset($_POST['jcartEmpty'])) {
            $this->empty_cart();
        }

        // Determine which text to use for the number of items in the cart
        $itemsText = $config['text']['multipleItems'];
        if ($this->itemCount == 1) {
            $itemsText = $config['text']['singleItem'];
        }

        // Determine if this is the checkout page
        /* First we check the request uri against the config checkout (set when
          the visitor first clicks checkout), then check for the hidden input
          sent with Ajax request (set when visitor has javascript enabled and
          updates an item quantity). */
        $isCheckout = strpos(request_uri(), $checkout);
        if ($isCheckout !== false || (isset($_REQUEST['jcartIsCheckout']) && $_REQUEST['jcartIsCheckout'] == 'true')) {
            $isCheckout = true;
        } else {
            $isCheckout = false;
        }

        // Overwrite the form action to post to gateway.php instead of posting back to checkout page
        if ($isCheckout === true) {

            // Sanititze config path
            $path = filter_var($config['jcartPath'], FILTER_SANITIZE_URL);

            // Trim trailing slash if necessary
            $path = rtrim($path, '/');

            $checkout = $path . '/gateway.php';
        }

        // Default input type
        // Overridden if using button images in config.php
        $inputType = 'submit';

        // If this error is true the visitor updated the cart from the checkout page using an invalid price format
        // Passed as a session var since the checkout page uses a header redirect
        // If passed via GET the query string stays set even after subsequent POST requests
        if (isset($_SESSION['quantityError']) && $_SESSION['quantityError'] === true) {
            $errorMessage = $config['text']['quantityError'];
            unset($_SESSION['quantityError']);
        }

        // Set currency symbol based on config currency code
        $currencyCode = trim(strtoupper($config['currencyCode']));
        switch ($currencyCode) {
            case 'EUR':
                $currencySymbol = '&#128;';
                break;
            case 'GBP':
                $currencySymbol = '&#163;';
                break;
            case 'JPY':
                $currencySymbol = '&#165;';
                break;
            case 'CHF':
                $currencySymbol = 'CHF&nbsp;';
                break;
            case 'SEK':
            case 'DKK':
            case 'NOK':
                $currencySymbol = 'Kr&nbsp;';
                break;
            case 'PLN':
                $currencySymbol = 'z&#322;&nbsp;';
                break;
            case 'HUF':
                $currencySymbol = 'Ft&nbsp;';
                break;
            case 'CZK':
                $currencySymbol = 'K&#269;&nbsp;';
                break;
            case 'ILS':
                $currencySymbol = '&#8362;&nbsp;';
                break;
            case 'TWD':
                $currencySymbol = 'NT$';
                break;
            case 'THB':
                $currencySymbol = '&#3647;';
                break;
            case 'MYR':
                $currencySymbol = 'RM';
                break;
            case 'PHP':
                $currencySymbol = 'Php';
                break;
            case 'BRL':
                $currencySymbol = 'R$';
                break;
            case 'USD':
            default:
                $currencySymbol = '$';
                break;
        }

        ////////////////////////////////////////////////////////////////////////
        // Output the cart
        ///////////////////////////////////////////////////////////////////////
        // Return specified number of tabs to improve readability of HTML output


        // If there's an error message wrap it in some HTML
        if ($errorMessage) {
            $errorMessage = "<p id='jcart-error'>$errorMessage</p>";
        }
        ///punto de venta//
        ////////////////////

        $lista_Pv = $_SESSION['lista_pv'];
        $contactoCliente = "";
        if (isset($_SESSION["contacto_cliente"])) {
            $contactoCliente = $_SESSION["contacto_cliente"];
        }
        //        print_r($this);
        // Display the cart header
        echo tab(1) . "$errorMessage\n";
        echo tab(1) . "<form method='post' action='$checkout' id='jcart-form-checkout'>\n";
        echo tab(2) . "<fieldset>\n";
        echo tab(3) . "<input type='hidden' name='jcartToken' value='{$_SESSION['jcartToken']}' />\n";
        echo tab(3) . "<div id='cabeza' >";
        if ($tipoForm == 'remitoTalonario') {
            //            echo tab(4) . "<div id='subTitulo'>";
            //            echo tab(5) . "Remito por talonario";
            //            echo tab(4) . "</div>";
            echo tab(4) . "<div id='RemComprobante'>";
            echo tab(5) . "<div id='fechaRem'>"
                . "<label for='jcart-fecha-rem'>"
                . "<i class='fa fa-calendar'></i>"
                . "</label>"
                . "<input type='text' id='jcart-fecha-rem' name='jcart-fecha-rem' value='{$this->fechaTalonario}'>";
            echo tab(5) . "</div>";

            echo tab(5) . "<div id='nroRem'> "
                . "<label for='jcart-suc'>"
                . "<i class='fa fa-edit'></i>"
                . "</label>{$lista_Pv}";

            echo tab(5) . "<input type='number' id='jcart-nro-rem' name='jcart-nro-rem' min='0' value='{$this->nroTalonario}'>";
            echo tab(4) . "</div>"
                . "</div>";
        }
        //echo tab(4) . "<img src='_img/carrito/carrito32x32.png'>";
        //                echo tab(4) . "<div id='jcart-title'>{$config['text']['cartTitle']} ($this->itemCount $itemsText)</div>\n";
        echo tab(4) . "<div id='jcart-title'> <i class='fa fa-shopping-cart'></i> (<strong>" . count($this->items) . " " . $itemsText . "</strong>)</div>\n";
        echo tab(4) . "<div id='jcart-subtotal'><strong><i class='fa fa-dollar-sign'></i> " . number_format($this->subtotal + $this->subtotalImpInt, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</strong></div>";
        echo tab(3) . "</div>";
        echo tab(3) . "<div id='cuerpo'>";
        echo tab(3) . "<table>\n";
        echo tab(4) . "<thead>\n";
        echo tab(5) . "<tr>\n";
        //echo tab(6) . "<th class='aLeft'>Cod.</th>\n";
        echo tab(6) . "<th class='aLeft'>artículo</th>\n";
        echo tab(6) . "<th>P.unit/<br>IVA</th>\n";
        echo tab(6) . "<th class='aCenter'>Cant</th>\n";
        //echo tab(6) . "<th class='aCenter'>Desc</th>\n";
        //                echo tab(6) . "<th>Alic</th>\n";
        echo tab(6) . "<th class='aRight'>P total</th>\n";
        // echo tab(6) . "<th class='aCenter'>en</th>\n";
        echo tab(6) . "<th>&nbsp</th>\n";
        echo tab(5) . "</tr>" . "\n";
        echo tab(4) . "</thead>\n";
        echo tab(4) . "<tbody>\n";
        //        echo "<pre>";
        //        print_r($this);
        //        echo "</pre>";
        // If any items in the cart
        if ($this->itemCount > 0) {

            // Display line items
            //ordeno por si son entregados o no.
            /** ordenar por carrito por entregado si o no 
             * @ retun orden
             */
            function ordenEntrega($a, $b)
            {
                return (strcmp($a['entregado'], $b['entregado']));
            }

            $itemsOr = $this->get_contents();
            uasort($itemsOr, 'ordenEntrega');
            $itemsOr = array_reverse($itemsOr);
            //            foreach($this->get_contents() as $item)	{


            $contaItem = 0;
            foreach ($itemsOr as $item) {
                //                print_r($item);
                if ($contaItem % 2 == 0) {
                    $claseTr = "par";
                } else {
                    $claseTr = "impar";
                }


                echo tab(5) . "<tr class='{$claseTr}'>\n";

                //echo tab(6) . "<td>\n";
                //echo tab(7) . "<span>{$item['id']}</span>";
                //echo tab(6) . "</td>\n";
                echo tab(6) . "<td class='jcart-item-name' title='" . $item['name'] . "\n"
                    . "P unitario: " . $currencySymbol . number_format($item['priceN'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "  \n"
                    . "Neto x R: " . $currencySymbol . number_format($item['neto'] * $item['qty'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "\n"
                    . "Descuento: " . $item['descPor'] . "%\n"
                    . "Neto x R N: " . $currencySymbol . number_format($item['subtotalNeto'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "\n"
                    . "Iva.(" . $item['alicuota'] . "%): " . $currencySymbol . number_format($item['subtotalIva'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "'>\n";

                echo tab(6) . "<i class='fa fa-info-circle'></i> ";
                if ($item['url']) {
                    echo tab(7) . $item['name'] . "</div>\n";
                } else {

                    echo tab(7) . $item['name'] . "</div>\n";
                }
                echo tab(7) . "<input name='jcartItemName[]' type='hidden' value='{$item['name']}' />\n";

                if ($item['descTotal'] != 0) {
                    echo tab(7) . "<span class='verde'>" . number_format($item['descPor'], 0) . "% off</span>\n";
                }
                echo tab(6) . "</td>\n";
                echo tab(6) . "<td class='jcart-item-price'>\n";
                echo tab(6) . $currencySymbol . number_format($item['priceN'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']);
                echo tab(6) . "</td>\n";

                echo tab(6) . "<td class='jcart-item-qty aCenter'>\n";
                echo tab(7) . "<input name='jcartItemId[]' type='hidden' value='{$item['id']}' />\n";
                echo tab(7) . $item['qty'] . "\n";
                echo tab(6) . "</td>\n";
                //                echo tab(6) . "<td class='aCenter'>\n";
                //                if ($item['descTotal'] != 0) {
                //                    echo tab(7) . "<span>" . $item['descPor'] . "%</span>\n";
                //                } else {
                //                    echo tab(7) . "<span>0%</span>\n";
                //                }
                //                echo tab(6) . "</td>\n";

                echo tab(6) . "<td class='jcart-item-price'>\n";
                echo tab(7) . "<span>$currencySymbol" . number_format($item['subtotal'], $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</span><input name='jcartItemPrice[]' type='hidden' value='{$item['priceN']}' />\n";
                //echo tab(7) . "<a class='jcart-remove' href='?jcartRemove={$item['id']}'><img src='_img/action_delete.png'></a>\n";
                echo tab(6) . "</td>\n";
                //                echo tab(6) . "<td class='aCenter'>\n";
                //                echo tab(7) . $item['entregado'] . "\n";
                //                echo tab(6) . "</td>\n";
                echo tab(6) . "<td><a class='jcart-remove' title='Eliminar articulo del carrito' href='?jcartRemove={$item['key']}'><i class='fa fa-trash-alt'></i></a>\n</td>";
                echo tab(5) . "</tr>\n";
                $contaItem++;
            }
        }

        // The cart is empty
        else {
            echo tab(5) . "<tr><td id='jcart-empty' class='vacio' colspan='7'>{$config['text']['emptyMessage']}</td></tr>\n";
        }
        echo tab(4) . "</tbody>\n";
        echo tab(3) . "</table>\n\n";
        echo tab(3) . "</div>";
        echo tab(3) . "<div id='pie'>";
        //        if ($isCheckout !== true) {
        //            if ($config['button']['checkout']) {
        //                $inputType = "image";
        //                $src = " src='{$config['button']['checkout']}' alt='{$config['text']['checkout']}' title='' ";
        //            }
        ////              echo tab(4) . "<input type='$inputType' $src id='jcart-checkout' name='jcartCheckout' class='jcart-buttons-ok' alt='{$config['text']['checkout']}' title='{$config['text']['checkout']}' />\n";
        //
        //            echo tab(4) . "<button type='submit' id='jcart-checkout' name='jcartCheckout' class='botonNuevo grande azul'>"
        //            . "generar <i class='fa fa-check'></i>"
        //            . "</button>\n";
        //        }

        echo tab(4) . "<div class='jcart-panel-uno'>";
        echo tab(5) . "<div class='textoPie'>ST Neto: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalNeto, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
        echo tab(5) . "<div class='textoPie'>Desc Pie: </div><div class='importePie'>";
        if ($modDescPie == "Si") {
            echo tab(5) . "<input type='number' id='jcart-por-desc-pie' name='jcart-por-desc-pie' min='0'  disabled max='" . $limDescPie . "' value='" . $this->porDescPie . "'>%</div>\n";
        } else {
            echo tab(5) . "<input type='number' id='jcart-por-desc-pie' name='jcart-por-desc-pie' min='0' disabled max='20' value='" . $this->porDescPie . "'>%</div>\n";
        }
        echo tab(5) . "<div class='textoPie'>Imp Desc: </div><div class='importePie verde'>($currencySymbol" . number_format(($this->importeDesc21 + $this->importeDesc105), $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . ")</div>\n";
        echo tab(5) . "<div class='textoPie'>ST Desc: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalDesc, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
        echo tab(5) . "<div class='textoPie'>ST 21%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalDesc21, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
        echo tab(5) . "<div class='textoPie'>ST 10,5%: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalDesc105, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";

        echo tab(4) . "</div>";
        echo tab(4) . "<div class='jcart-panel-dos'>";
        echo tab(4) . "<div class='jcart-checkout'>";
        if ($isCheckout !== true) {
            if ($config['button']['checkout']) {
                $inputType = "image";
                $src = " src='{$config['button']['checkout']}' alt='{$config['text']['checkout']}' title='' ";
            }
            echo tab(5) . "<button type='submit' id='jcart-checkout' name='jcartCheckout' class='botonNuevo grande azul'>"
                . "generar <i class='fa fa-check'></i>"
                . "</button></div>\n";
            //			echo tab(4) . "<input type='$inputType' $src id='jcart-checkout' name='jcartCheckout' class='jcart-buttons-ok' alt='{$config['text']['checkout']}' title='{$config['text']['checkout']}' />\n";
        }
        echo tab(5) . "<div class='textoPie'>ST Exto: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalExento, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
        echo tab(5) . "<div class='textoPie'>Iva 21: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalIva21, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
        echo tab(5) . "<div class='textoPie'>Iva 10,5: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalIva105, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
        //echo tab(5) . "<span id='jcart-subtotal'>SubTotal Neto: <strong>$currencySymbol" . number_format($this->subtotalNeto, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</strong></span>\n";

        echo tab(5) . "<div class='textoPie'>Imp. Int: </div><div class='importePie'>$currencySymbol" . number_format($this->subtotalImpInt, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
        echo tab(5) . "<div class='textoPie'>Perc: </div><div class='importePie'>$currencySymbol" . number_format($this->percepcionesT, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
        echo tab(5) . "<div class='textoPie azulImp'>Total: </div><div class='importePie azulImp'><i class='fa fa-dollar'></i> " . number_format($this->subtotal + $this->subtotalImpInt, $priceFormat['decimals'], $priceFormat['dec_point'], $priceFormat['thousands_sep']) . "</div>\n";
        echo tab(4) . "</div>";
        echo tab(4) . "<div class='jcart-panel-tres'>";
        echo tab(5) . "<div class='texto'><i class='fa fa-truck'></i></div>"
            . "<div class='importe'><select name='formaEntrega' id='formaEntrega'>";
        echo tab(6) . "<option value='Retira cliente despacho'>Retira cliente despacho</option>";
        echo tab(6) . "<option selected value='Envía por despacho'>Envía por despacho</option>";
        echo tab(6) . "<option value='Entrega vendedor'>Entrega vendedor</option>";
        echo tab(6) . "<option value='Transporte'>Transporte</option>";
        echo tab(6) . "<option value='Reservado en depósito'>Reservado en depósito</option>";

        echo tab(5) . "</select>";

        //voy a buscar el transporte.


        echo tab(5) . "</div>";
        //                echo tab(5) . "</br>";
        //        echo tab(5) . "<div class='texto'>";
        //        echo tab(5) . " </div>";
        echo tab(5) . "<div class='texto'>  <i class='fa fa-tags'></i> </div><div class='importe'><input type='text' id='jcart-detalle' placeholder='Detalle...' name='jcart-detalle' class='detallePedido' value='" . $contactoCliente . "'>";
        echo tab(5) . "</div>";
        echo tab(4) . "</div>";

        echo tab(3) . "</div>";
        echo tab(3) . "<div id='jcart-buttons'>\n";

        if ($config['button']['update']) {
            $inputType = "image";
            $src = " src='{$config['button']['update']}' alt='{$config['text']['update']}' title='' ";
        }

        echo tab(4) . "<input type='$inputType' $src name='jcartUpdateCart' value='{$config['text']['update']}' class='jcart-button' />\n";

        if ($config['button']['empty']) {
            $inputType = "image";
            $src = " src='{$config['button']['empty']}' alt='{$config['text']['emptyButton']}' title='' ";
        }

        echo tab(4) . "<input type='$inputType' $src name='jcartEmpty' value='{$config['text']['emptyButton']}' class='jcart-button' />\n";
        echo tab(3) . "</div>\n";

        // If this is the checkout display the PayPal checkout button
        if ($isCheckout === true) {
            // Hidden input allows us to determine if we're on the checkout page
            // We normally check against request uri but ajax update sets value to relay.php
            echo tab(3) . "<input type='hidden' id='jcart-is-checkout' name='jcartIsCheckout' value='true' />\n";

            // PayPal checkout button
            if ($config['button']['checkout']) {
                $inputType = "image";
                $src = " src='{$config['button']['checkout']}' alt='{$config['text']['checkoutPaypal']}' title='' ";
            }

            if ($this->itemCount <= 0) {
                $disablePaypalCheckout = " disabled='disabled'";
            }

            echo tab(3) . "<input type='$inputType' $src id='jcart-paypal-checkout' name='jcartPaypalCheckout' value='{$config['text']['checkoutPaypal']}' $disablePaypalCheckout />\n";
        }

        echo tab(2) . "</fieldset>\n";
        echo tab(1) . "</form>\n\n";

        echo tab(1) . "<div id='jcart-tooltip'></div>\n";
        echo tab(1) . '<script>$(document).ready(function(){$( "#jcart-fecha-rem" ).datepicker({ dateFormat: "yy-mm-dd" });});</script>';
    }
    // * fin display remito Talonario Mobil

    

}

// * fin objeto JCART



// Example
if ( is_session_started_jcart() === FALSE ){ session_start();}
//session_start();
// Initialize jcart after session start

if(isset($_SESSION['jcart'])){
	$jcart = $_SESSION['jcart'];
}else{
    // inicializi
    $jcart = new Jcart();
    $_SESSION['jcart'] = $jcart;

}


// if(isset($_SESSION['jcart'])){
// 	$jcart = $_SESSION['jcart'];
// }else{
//     $jcart = new Jcart();
// }

// if (isset($jcart)) {
//     if(!is_object($jcart)){
        
//         $jcart = new Jcart();
//     }else{
//         $_SESSION['jcart'] = $jcart;
//         $jcart = $_SESSION['jcart'];
//     }
    
// }


// if (isset($jcart)) {
//     if(!is_object($jcart)){

//         $jcart = new Jcart();
//     }else{
//         $_SESSION['jcart'] = $jcart;
//         $jcart = $_SESSION['jcart'];
//     }

// }

// if(!isset($jcart)){
//     $jcart = new Jcart();
//     $_SESSION['jcart'] = $jcart;
// }


// Enable request_uri for non-Apache environments
// See: http://api.drupal.org/api/function/request_uri/7
if (!function_exists('request_uri')) {

    function request_uri()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        } else {
            if (isset($_SERVER['argv'])) {
                $uri = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['argv'][0];
            } elseif (isset($_SERVER['QUERY_STRING'])) {
                $uri = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
            } else {
                $uri = $_SERVER['SCRIPT_NAME'];
            }
        }
        $uri = '/' . ltrim($uri, '/');
        return $uri;
    }
}

function tab($n)
{
    $tabs = null;
    while ($n > 0) {
        $tabs .= "\t";
        --$n;
    }
    return $tabs;
}

function is_session_started_jcart()
{
    if (php_sapi_name() !== 'cli') {
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        } else {
            return session_id() === '' ? FALSE : TRUE;
        }
    }
    return FALSE;
}
