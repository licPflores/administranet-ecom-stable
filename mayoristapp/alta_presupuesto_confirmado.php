<?php

// jCart v1.3
// http://conceptlogic.com/jcart/

// This file is called when any button on the checkout page (PayPal checkout, update, or empty) is clicked

// Include jcart before session start
require_once 'jcart/numero_a_letra.php';
require_once 'jcart/jcart.php';
require_once 'sesion.inc.php';
require_once '_scripts/php/funciones.php';
$config = $jcart->config;

// una vez que tenemos el carrito listo hay que validar que haya algo para eso 
// preguntamos si no esta vacio el objeto con las entradas de stock del OBJ jcart
// recuperamos la numeracion del pedido
// guardamos el pedidos
// guardamos las entradas en el stock para que se ponga todo ok
//
// validamos
$urlVuelta = "alta_presupuesto";
$tipoPedido = "Ecom vendedor";
$articulos = $jcart->get_contents();

// debo saber si soy cliente.

if (is_object($_SESSION['cliente'])) {
    $clienteObj = $_SESSION['cliente'];
} else {
    $clienteObj = $_SESSION['cliente'][0];
}

$utilizaEmbalaje = $_SESSION['utilizaEmbalaje'];

if ($_SESSION["tipousuario"] == "vendedor") {
    $usuario = $_SESSION["vendedor"];
    $codViajante = $usuario->CodViajante;
} else {
    // soy el cliente yo tengo que tener el viajante 
    // por defecto.
    $usuario = $clienteObj;
    $codViajante = $clienteObj->CodViajante;
}


$idPuntoVenta = $usuario->id_punto_venta;
$idDeposito = $_SESSION['deposito'];

$usoBultoCerrado = 'No';
$usaDisplay = 'No';

if ($_SESSION["utiliza_bulto_cerrado"] == "Si") {
    $usoBultoCerrado = $_SESSION["utiliza_bulto_cerrado"];
}

// display 
if ($_SESSION['utiliza_display'] == "Si") {
    $usaDisplay = $_SESSION['utiliza_display'];
}

$soyMovil = "si";
if ($_SESSION["caminoDisp"] == "") {
    $soyMovil = "no";
}
// The update and empty buttons are displayed when javascript is disabled 
// Re-display the cart if the visitor has clicked either button
//asigno el valor del pedido antes
$pedidoArr = $jcart->muestra_pedido();

if (!empty($articulos) && $pedidoArr["subtotal"] > 0) {

    // guardamos el pedido primero 
    // inicio de transacciones
    $errores = 0;
    $sqlTotal = "SET AUTOCOMMIT =0;";
    $resultado = mysqli_query($connV, $sqlTotal) or die('No puedo iniciar autocommit ' . mysqli_error($connV));
    $sqlTotal = "BEGIN;";
    $resultado = mysqli_query($connV, $sqlTotal) or die('No puedo hacer Begin ' . mysqli_error($connV));

    // recupero el codigo de movimiento
    $sqlMovi = "SELECT CodigoMovimiento + 1 as CodigoMovNew FROM codmov WHERE codigo = 1";
    $resultado = mysqli_query($connV, $sqlMovi) or die('No puedo recuperar el codigo de movimiento' . mysqli_error($connV));
    if (!$resultado) {
        $errores++;
    }
    // recupero el nuevo codigo de movimiento
    $codMovResult = mysqli_fetch_assoc($resultado);
    $codMov = $codMovResult["CodigoMovNew"];
    // actualizo el codigo de movimiento en la tabla codigo de movimiento.
    $sqlMoviUp  = "UPDATE codmov 
                    SET CodigoMovimiento=" . $codMov . " 
                    WHERE codmov.codigo=1;";
    $resultado = mysqli_query($connV, $sqlMoviUp) or die('No puedo modificar el codigo de movimiento' . mysqli_error($connV));
    if (!$resultado) {
        $errores++;
    }

    // cierro la transaccion.
    if ($errores == 0) {
        $sqlTotal = "COMMIT;";
        $resultado = mysqli_query($connV, $sqlTotal);
        //echo "todo bien";
    } else {
        $sqlTotal = "ROLLBACK;";
        $resultado = mysqli_query($connV, $sqlTotal);
        //echo "todo mal";
    }

    // reinicio la transaccion
    $errores = 0;
    $sqlTotal = "SET AUTOCOMMIT =0;";
    $resultado = mysqli_query($connV, $sqlTotal) or die('No puedo iniciar autocommit ' . mysqli_error($connV));
    $sqlTotal = "BEGIN;";
    $resultado = mysqli_query($connV, $sqlTotal) or die('No puedo hacer Begin ' . mysqli_error($connV));
    // obtengo el numero de comprobante del pedido

    $sqlTalon = "SELECT * 
                    FROM talonarios 
                    WHERE id_punto_venta = '" . $idPuntoVenta . "' 
                    AND TipoComprobante = 'PRE'";
    $resultado = mysqli_query($connV, $sqlTalon) or die('No puedo recuperar el talonario' . mysqli_error($connV));
    if (!$resultado) {
        $errores++;
    }

    $objTalonario = mysqli_fetch_assoc($resultado);

    if (!empty($objTalonario)) {
        $numeroPedido = str_pad($objTalonario["PV"], 4, "0", STR_PAD_LEFT) . "-" . str_pad($objTalonario["Nro"], 8, '0', STR_PAD_LEFT);
        $nroCompBusqPedido = $objTalonario["Nro"];

        // actualizo el talonario
        $sqlTalonUp = "UPDATE talonarios 
                            SET Nro = " . $objTalonario["Nro"] . "+1 
                            WHERE id_punto_venta = '" . $idPuntoVenta . "' 
                            AND TipoComprobante = 'PRE'";
        $resultado = mysqli_query($connV, $sqlTalonUp) or die('No puedo actualizar el talonario' . mysqli_error($connV) . "<p>" . $sqlTalonUp . "</p>");
        if (!$resultado) {
            $errores++;
        }
    } else {
        $errores++;
    }



    // $pedidoArr = $jcart->muestra_pedido();
    $vencimiento = date('Y/m/d', mktime(0, 0, 0, date('m') + 1, date('d'), date('Y')));

    //    estados del pedido
    //    -> si es pedido del vendedor entra autorizado salvo que no pueda por los dias
    //    -> si es pedido del cliente entra No autorizado 
    //    Autorizado
    //    No Autorizado
    //    
    $autorizaPedido = '';
    if (isset($objVendedor)) {
        //        existe el vendedor a comprobar si el cliente esta o no autorizado.

        if ($arrCliente['exceso'] == 1) {
            $autorizaPedido = 'No Autorizado';
        } else {
            $autorizaPedido = 'Autorizado';
        }
    } else {
        $autorizaPedido = 'No Autorizado';
    }

    /*
 *     alta de datos adicionales del CLIENTE
 */
    /* Domicilios del cliente */
    //    echo "<pre>";
    //    print_r($_POST);
    //    echo "</pre>";


    /*Logistica*/
    /* no se usa en presupeustos*/
    $idRuta = "NULL";
    $idDomEntrega = "NULL";

    $sqlDatoCliente = "INSERT INTO cliente_datos_adicionales ("
        . " fechaEntrega ,"
        . " id_deposito_despacho,"
        . " Fentrega,"
        . " origen_pedido,"
        . " TipoComprobante, "
        . " id_cliente, "
        . " CodigoMovimiento,"
        . " id_cliente_domicilio,"
        . "id_ruta)"
        . " VALUES ("
        . "'" . date('Y/m/d') . "',"
        . "'" . $_SESSION['deposito'] . "',"
        . "'" . $_POST['formaEntrega'] . "',"
        . "'Web',"
        . "'PRE',"
        . " '" . $clienteObj->Codigo . "',"
        . "'" . $codMov . "',"
        . $idDomEntrega . ","
        . $idRuta . ")";
    $resultado = mysqli_query($connV, $sqlDatoCliente) or die('No puedo insertar dato adicional del cliente' .  mysqli_error($connV) . $sqlDatoCliente);
    if (!$resultado) {
        $errores++;
    }

    /* 
     * PERCEPCIONES
     * 
     */
    $percepciones = $pedidoArr["percepciones"]["detalle"];
    //    echo "<pre>";
    //    print_r($pedidoArr);
    //    echo "</pre>";
    if (!empty($percepciones)) {
        foreach ($percepciones as $kp => $per) {
            //        echo "<pre>";
            //    print_r($per);
            //    echo "</pre>";
            $sqlPerc = "INSERT INTO percep_cli (
                                                `id_percep_cli_tipo`,
                                                `alicuota_percep_cli`,
                                                `importe_percep_cli`,
                                                `codigo_movimiento`,
                                                `id_cliente`,
                                                `tipo_comp`                                            
                                                )VALUES (
                                                    '" . $per["id"] . "',
                                                    '" . $per["alic"] . "',
                                                    '" . $per["monto"] . "',
                                                    '" . $codMov . "',
                                                    '" . $clienteObj->Codigo . "',
                                                    'PED'    
                                                )";
            $resultado = mysqli_query($connV, $sqlPerc) or die("No puedo insertar la percepcion" . mysqli_error($connV));
            if (!$resultado) {
                $errores++;
            }
        }
    }
    // * COTIZACION DEL DOLAR
    $sqlCotizacion = "SELECT cotizacion.ValorPesos FROM cotizacion WHERE cotizacion.id_cotizacion=1 LIMIT 1 ;";
    $resultado = mysqli_query($connV, $sqlCotizacion) or die("No puedo recuperar la cotizacion del dolar." . mysqli_error($connV));
    if (!$resultado) {
        $errores++;
    }

    $cotiDolar = 1;
    $arrCoti = array();
    if ($resultado) {
        $arrCoti = mysqli_fetch_assoc($resultado);
        if (!empty($arrCoti)) {
            $cotiDolar = $arrCoti['ValorPesos'];
        }
    }

    /*    
 * alta del PRESUPUESTO
 */
    //echo "<pre>".print_r($pedidoArr)."</pre>";
    $sqlPedidoIns = "INSERT INTO 
            comp_ped 
        SET
        Fecha='" . date('Y/m/d') . "',
        Tipocomprobante= 'PRE',
        CodSucursal='" . $clienteObj->id_sucursal . "',
        IdUsuario='" . $_SESSION['idusuario'] . "',
        NroComprobante= '" . $numeroPedido . "',
        NroCompBusq='" . $nroCompBusqPedido . "',
        id_pv='" . $idPuntoVenta . "',
        Detalle= '" . $detalle . "',
        ImporteVenta='" . $pedidoArr['subtotal'] . "',
        ImporteVentaL='" . num2letras(number_format($pedidoArr['subtotal'], 2, ",", "")) . "',
        Iva1='" . $pedidoArr['subtotalIva21'] . "',
        Iva2='" . $pedidoArr['subtotalIva105'] . "',
        Alicuota1='21',
        Alicuota2='10.5',
        Exento= '" . $pedidoArr['subtotalExento'] . "',
        exento_interes= '" . $pedidoArr['subtotalExento'] . "',
        anulado='No',
        Subtotal1= '" . $pedidoArr['subtotalNetoIva21'] . "',
        Subtotal2='" . $pedidoArr['subtotalNetoIva105'] . "',
        SubtotalGral='" . ($pedidoArr['subtotalNetoIva21'] + $pedidoArr['subtotalNetoIva105']) . "',
        PorDesc1='" . $pedidoArr['porDescPie'] . "',
        PorDesc2='" . $pedidoArr['porDescPie'] . "',
        ImpDesc1='" . $pedidoArr['importeDesc21'] . "',
        ImpDesc2='" . $pedidoArr['importeDesc105'] . "',
        SubTotalDesc1= '" . $pedidoArr['subtotalDesc21'] . "',
        SubTotalDesc2='" . $pedidoArr['subtotalDesc105'] . "',
        SubtotalDesc= '" . $pedidoArr['subtotalDesc'] . "',
        Codigo='" . $clienteObj->Codigo . "',
        CondVenta='" . $clienteObj->condVenta . "',
        id_condventa='" . $clienteObj->id_cv . "',
        CodigoMovimiento='" . $codMov . "',
        Estado='Pendiente',
        Vencimiento='" . $vencimiento . "',
        CodViajante= '" . $codViajante . "',
        TipoPedido='" . $tipoPedido . "',
        impuesto_interno_total='" . $pedidoArr['subtotalImpInt'] . "',
        impuesto_interno_interes='" . $pedidoArr['subtotalImpInt'] . "',
        autorizacion_sistema='" . $autorizaPedido . "',
        formaentrega= '" . $_POST['formaEntrega'] . "',
        fecha_control='" . date('d/m/Y H:i') . "',
        id_deposito_despacho= '" . $_SESSION['deposito'] . "',
        FechaEntrega='" . $fechaEntregaH . "',
        total_percep='" . $pedidoArr["percepcionesT"] . "',
        CotiDolar ='" . $cotiDolar . "',
        geo_latitud='" . $geo_lat . "',
        geo_longitud= '" . $geo_long . "';";
    $resultado = mysqli_query($connV, $sqlPedidoIns) or die('No puedo insertar el pedido' .  mysqli_error($connV) . $sqlPedidoIns);

    //   echo "<pre>";
    //   print_r($sqlPedidoIns);
    //   echo "</pre>";

    if (!$resultado) {
        $errores++;
    }
    //echo "Pedido OK<br>";

    $controlRenglones = 0;
    foreach ($articulos as $cc => $articulo) {
        //inserto actualizar la tabla stock_deposito.
        //echo "<pre>". print_r($articulo) ."</pre>";

        //        seteando las promociones.

        $idArt = str_replace('p', '', $articulo['id']);

        $promocion      = 'No';
        $promocion_por  = 0;
        $descuento_por  = 0;
        $promocion_tipo = '';
        $promocion_cant = 0;
        $cantidadContada = 1; // seria el QTY 
        $cantidadMinima = 1; // seria la cantidad minima del producto , por calculo de display o bulto.

        $cantidadContada = $articulo['qty'];
        $cantidadMinima = $articulo['cantidadMinimaContada'];

        //pregunto si tiene promociones
        if ($articulo['promo'] == 'si') {
            $promocion       = 'Si';
            if ($articulo['descTotal'] != 0) {
                $promocion_por   = $articulo['promoPorc'];
                $descuento_por   = $promocion_por;
            }
            if ($articulo['promoCant'] > 0) {

                $promocion_cant  = $articulo['promoCant'];
            }
            $promocion_tipo  = $articulo['promoTipo'];
        } else {
            $descuento_por   = $articulo['descPor'];
        }
        /*este codigo no sirve porque no bajo mercaderia.*/
        $sqlStockDep = "SELECT saldo
                        FROM stock_deposito 
                        WHERE id_articulo=" . $idArt . "
                      AND id_deposito=" . $idDeposito;
        $resultado = mysqli_query($connV, $sqlStockDep) or die("No puedo recuperar el stock_deposito" . mysqli_error($connV) . "<pre>" . $sqlStockDep . "</pre>");
        if (!$resultado) {
            $errores++;
        }
        $stockDeposito = mysqli_fetch_object($resultado);

        $saldoArt = $stockDeposito->saldo;
        $saldoArt += $articulo['qty'];

        //        $sqlStockDepUp= "UPDATE stock_deposito 
        //                      SET saldo_pedido_cliente = " . $saldoArt . " 
        //                      WHERE id_articulo=" . $idArt . "
        //                      AND id_deposito=1";

        //       $resultado = mysqli_query($connV,$sqlStockDepUp) or die('No puedo actualizar el stock_deposito' .mysqli_error($connV));
        //        if(!resultado){
        //            $errores++;
        //        }
        /**
         * Datos Adicionales del ARticulo
         */
        $sqlArtDb = "SELECT 
                articulo.impuesto_interno,
                articulo.CodigoArticulo,
                articulo.CodigoArticuloT,
                articulo.PrecioCosto,
                articulo.NombreArticulo,
                articulo.AlicuotaIB as IdAlicuotaIb,
                activ_iibb.alicuota AS alicuotaIb,
                articulo.CodLaboratorio,
                articulo.id_manual,
                articulo.tipo_art,
                articulo_prov.cantidad_unidad_display,
                articulo_prov.cantidad_display_bulto,
                articulo_prov.cantidad_bulto_pallet,
                articulo_prov.precio_unidad AS tipoPrecioUnidad
                FROM
                    articulo
                LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt AND articulo_prov.CodProveedor = articulo.CodigoProveedor)
                LEFT JOIN activ_iibb ON activ_iibb.ID = articulo.AlicuotaIb
                WHERE articulo.IDArt = " . $idArt;
        $resultado = mysqli_query($connV, $sqlArtDb) or die('No puedo consultar el articulo en la base de datos' . mysqli_error($connV));
        if (!$resultado) {
            $errores++;
        }
        $artObj = mysqli_fetch_object($resultado);


        /*
         * Embalaje
         */
        /*
             * Embalaje
             */
        $campEmbV = "";
        $datoEmbV = "";
        $campEmbC = "";
        $datoEmbC = "";
        if ($utilizaEmbalaje == 'Si') {

            $artEmbV = "SELECT articulo.multiplicador_vta, "
                . "articulo.CodigoProveedor, "
                . "articulo.id_unimed,"
                . "articulo.id_presentacionV, "
                . "unidMed.nombre_unimed, "
                . "presentacion_abm.nombre_presentacion 
                           FROM articulo 
                               LEFT JOIN unidmed ON (unidMed.id_unimed = articulo.id_unimed) 
                               LEFT JOIN presentacion_abm ON (presentacion_abm.id_presentacion = articulo.id_presentacionV)
                               WHERE articulo.idArt=" . $idArt;
            $resultado = mysqli_query($connV, $artEmbV) or die("No puedo recuperar los articulos de embalaje" . mysqli_error($connV));
            if (!$resultado) {
                $errores++;
            }
            $artEmV = mysqli_fetch_assoc($resultado);
            //            echo "<pre>";
            //            print_r($artEmV);
            //            echo "</pre>";
            if (!empty($artEmV)) {
                $idProveedor = $artEmV["CodigoProveedor"];
                $campEmbV = ", multiplicador_vta='" . $artEmV["multiplicador_vta"] . "',"
                    . " id_unimed_vta='" . $artEmV["id_unimed"] . "',"
                    . " id_presentacion_vta='" . $artEmV["id_presentacionV"] . "',"
                    . " nombre_unimed_vta='" . $artEmV["nombre_unimed"] . "',"
                    . " nombre_presentacion_vta='" . $artEmV["nombre_presentacion"] . "'";
            }

            $artEmC = "SELECT multiplicador_comp,"
                . "cantidad_uni, "
                . "unidMed.nombre_unimed, "
                . "presentacion_abm.nombre_presentacion, "
                . "articulo_prov.id_presentacionC, "
                . "articulo_prov.id_unimed "
                . " FROM articulo_prov "
                . " LEFT JOIN unidMed ON (unidMed.id_unimed = articulo_prov.id_unimed) "
                . " LEFT JOIN presentacion_abm ON (presentacion_abm.id_presentacion = articulo_prov.id_presentacionC)"
                . " WHERE idArt= " . $idArt . " AND CodProveedor = " . $idProveedor;
            $resultado = mysqli_query($connV, $artEmC) or die("No puedo recuperar los datos del proveedor" . mysqli_error($connV));
            if (!$resultado) {
                $errores++;
            }
            $artEmC = mysqli_fetch_assoc($resultado);

            if (!empty($artEmC)) {
                $campEmbC = ",multiplicador_comp='" . $artEmC["multiplicador_comp"] . "',"
                    . "id_unimed_comp='" . $artEmC["id_unimed"] . "',"
                    . "id_presentacion_comp='" . $artEmC["id_presentacionC"] . "',"
                    . "nombre_unimed_comp='" . $artEmC["nombre_unimed"] . "',"
                    . "nombre_presentacion_comp='" . $artEmC["nombre_presentacion"] . "'";
            }

            //          echo "Embalaje OK<br>";  
        }
        /*Fin Embalaje*/

        // calculando el nuevo precio de costo
        $datosCosto = array(
            'cantidadUnidadDisplay' => $artObj->cantidad_unidad_display,
            'cantidadDisplayBulto' => $artObj->cantidad_display_bulto,
            'tipoPrecioUnidad' => $artObj->tipoPrecioUnidad,
            'precioCosto' => $artObj->PrecioCosto
        );
        // el precio viene en display o bulto ya multiplicado.
        $precioCostoCalculado = calculaPrecioCostoUnidad($datosCosto);

        // * calculo del precio Costo x renglon.

        // if($tipoCuenta=='Unidad'){
        //     $detalle = "Ajuste Movil Tipo " . $tipoCuenta . ': ' . $cantidadContadaUnidad;
        // }
        $tipoCuenta = $articulo['comoCuento'];
        $cantidadDiferencia = $cantidadMinima;
        $cantidadMultiplica = 1;
        if ($tipoCuenta == 'Unidad') {
            $cantidadDividir = 1;
            $cantidadDiferencia = 1;
            $cantidadMultiplica = 1;
        }
        if ($tipoCuenta == 'Display') {
            $cantidadDividir = $datosCosto['cantidadUnidadDisplay'];
            $cantidadDiferencia = $cantidadMinima / $cantidadDividir;
            $cantidadMultiplica = 1;
        }
        if ($tipoCuenta == 'Bulto') {
            $cantidadDividir = $datosCosto['cantidadDisplayBulto'] *  $datosCosto['cantidadUnidadDisplay'];
            $cantidadDiferencia = $cantidadMinima / $cantidadDividir; // la cantidad final a ajustar expresada en bultos
            $cantidadMultiplica = $datosCosto['cantidadDisplayBulto'];
        }
        // el precio viene en display siempre .
        // $precioCosto= $precioCostoCalculado; 

        // el precio de costo viene por unidad, multiplicar
        $precioCosto = $precioCostoCalculado * $cantidadMultiplica;

        // contenido de caculo final de pedido
        // echo '<pre>';
        // print_r($datosCosto);
        // echo 'precioCosto:: ',$datosCosto['precioCosto'],PHP_EOL;
        // echo 'precioCosto Callculado:: ',$precioCostoCalculado,PHP_EOL;
        // echo 'precio Costo final:: ',$precioCosto,PHP_EOL;
        // echo 'tipo de Precio:: ', $datosCosto['tipoPrecioUnidad'],PHP_EOL;
        // echo 'TipoCuenta:: ',$tipoCuenta,PHP_EOL;
        // echo 'cantidadDividir:: ',$cantidadDividir,PHP_EOL;
        // echo 'cantdiadDiferencia:: ',$cantidadDiferencia,PHP_EOL;
        // echo 'multplicador del costo Multiplica:: ',$cantidadMultiplica,PHP_EOL;
        // echo 'cantidad Contada:: ',$cantidadContada,PHP_EOL;
        // echo 'cantidad unidad Minima:: ',$cantidadMinima,PHP_EOL;

        // echo '</pre>';
        // el costo del renglon... va por l cantidad contada ( en realida dpor la cantidad diferencia expresasda en como cuento.)
        $precioCostoXRenglon = ($precioCosto * $cantidadContada);




        // FORZANDO EL DESCUENTO EN EL NETO X UNIDAD tener en cuenta qle precio que viene que ahora puede esta cambiado.
        // hay que recalcular las cantidades, x cantidad minima , y el precio de costo 


        $precioNetoxUnidadDesc = $articulo['neto']; // precio Neto con Descuento
        $precioVentaxUnidad = $articulo['neto']; //precio Neto sin Descuento
        // $precioBrutoxUnidad=$articulo['priceN']; //precio Neto con o sin Descuento + Iva
        $precioBrutoxUnidad = ($articulo['netoN'] + $articulo['impIva']); //precio Neto con o sin Descuento + Iva
        $precioBrutoxRenglon = ($articulo['subtotalNeto'] + $articulo['subtotalIva']);

        if ($descuento_por !== 0) {
            // hay descuento x las dudas vuelvo a recalcular.
            //$descuento = ($articulo['neto'] * $desc_por)/100;
            //echo "<pre>".print_R($articulo)."</pre>".PHP_EOL;
            $precioNetoxUnidadDesc = ($articulo['neto'] - $articulo['descTotal']);
            $precioVentaxUnidad = $articulo['neto'];
            // $precioBrutoxUnidad=$articulo['priceN'];
            $precioBrutoxUnidad = ($articulo['netoN'] + $articulo['impIva']); //precio Neto con o sin Descuento + Iva



        }
        // * STOCK P
        $sqlStock = "INSERT INTO stockp SET 
                        Saldo= '" . $saldoArt . "',
                        impuesto_interno='" . $artObj->impuesto_interno . "',
                        impuesto_interno_subtotal='" . $articulo['subtotalImpInt'] . "',
                        Fecha= '" . date('Y/m/d') . "',
                        CodigoArticulo='" . $artObj->CodigoArticuloT . "',
                        Descripcion='" . $artObj->NombreArticulo . "',
                        PrecioVentaxU= '" . $precioVentaxUnidad . "',
                        PrecioCostoxU= '" . $precioCosto . "',
                        PrecioIVAxU='" . $articulo['impIva'] . "',
                        PrecioBrutoxU='" . $precioBrutoxUnidad . "',
                        PrecioNetoxU= '" . $precioNetoxUnidadDesc . "',
                        PrecioVentaxR='" . ($articulo['neto'] * $cantidadDiferencia) . "',
                        PrecioCostoxR= '" . $precioCostoXRenglon . "',
                        PrecioIVAxR= '" . $articulo['subtotalIva'] . "',
                        PrecioBrutoxR='" . $precioBrutoxRenglon . "',
                        PrecioNetoxR='" . $articulo['subtotalNeto'] . "',
                        Alicuota= '" . $articulo['iva'] . "',
                        AlicuotaIB='" . $artObj->IdAlicuotaIb . "',
                        imp_alicuota_iva= '" . $articulo['alicuota'] . "',
                        imp_alicuota_iibb='" . $artObj->alicuotaIb . "',
                        Salida='" . $cantidadMinima . "',
                        Cantidad='" . $cantidadMinima . "',
                        ImpDesc='" . ($cantidadMinima * $articulo['descTotal']) . "',
                        PorDesc=ROUND('" . $descuento_por . "',2),
                        CodViajante= '" . $codViajante . "',
                        CodLaboratorio='" . $artObj->CodLaboratorio . "',
                        CodigoMovimiento= '" . $codMov . "',
                        CodDeposito= '" . $_SESSION['deposito'] . "',
                        IDArt=  '" . $idArt . "',
                        id_manual= '" . $artObj->id_manual . "',
                        CodSucursal= '" . $idSucVendedor . "',
                        idusuario='" . $_SESSION['idusuario'] . "',
                        TipoIVA='" . $articulo['tipoIva'] . "',
                        CodigoCP='" . $clienteObj->Codigo . "',
                        Tipo='Cliente',
                        TipoComp='Presupuesto',
                        anulado='No',
                        Comprobante='PRE',
                        coti_dolar='" . $cotiDolar . "',
                        NroComprobante='" . $numeroPedido . "',
                        lista_precio='" . $clienteObj->codListaPrecio . "',
                        promocion='" . $promocion . "',
                        promocion_por='" . $promocion_por . "',
                        promocion_tipo='" . $promocion_tipo . "',
                        promocion_cant='" . $promocion_cant . "',
                        tipo_art='" . $artObj->tipo_art . "',
                        Orden=" . ($cc + 1) . ",
                        cantidad_entregada='" . $cantidadMinima . "',
                        cantidad_pendiente= '" . $cantidadMinima . "',
                        detalle='" . $articulo['url'] . "',
                        unidad_art_peso='" . $articulo['url'] . "',
                        nro_despacho='',  
                        cantidad_unidad_display='" . $articulo['cantidadUnidadDisplay'] . "',
                        cantidad_dividir='" . $articulo['divisorCantidad'] . "',
                        tipo_unidad='" . $articulo['comoCuento'] . "'  
                        " . $campEmbV . $campEmbC;
        //        echo "<pre>";
        //        print_r($sqlStock);
        //        echo "</pre><br>";
        $resultado = mysqli_query($connV, $sqlStock) or die('No puedo insertar el articulo' . mysqli_error($connV) . $sqlStock . "<br><br>");
        if (!$resultado) {
            $errores++;
        } else {
            $controlRenglones++;
        }
    }

    if ($errores == 0 && $controlRenglones > 0) {
        // todo bien sin errores y con renglones mas de uno.
        $sqlTotal = "COMMIT;";
        $resultado = mysqli_query($connV, $sqlTotal);
        $urlVuelta .= ".php?cartel=0";
        //echo "todo bien";
    } else {
        // todo mal errores mas de cero y renglones inserto o en cero.
        $sqlTotal = "ROLLBACK;";
        $resultado = mysqli_query($connV, $sqlTotal);
        $urlVuelta .= ".php?cartel=1";
        unset($numeroPedido);

        // por las dudas borro todo lo que haya.. si salio mal.

        // percepciones

        $sqlBorra = "DELETE FROM percep_cli WHERE percep_cli.codigo_movimiento=" . $codMov;
        $resultado = mysqli_query($connV, $sqlBorra);
        // cliente domicilioles
        $sqlBorra = "DELETE FROM cliente_datos_adicionales WHERE cliente_datos_adicionales.CodigoMovimiento=" . $codMov;
        $resultado = mysqli_query($connV, $sqlBorra);

        // comp_ped.
        $sqlBorra = "DELETE FROM comp_ped WHERE comp_ped.CodigoMovimiento=" . $codMov;
        $resultado = mysqli_query($connV, $sqlBorra);
        unset($codMov);

        // echo "todo mal";
    }
    //    si termina tengo que vaciar el carrito y despues tengo que volver a la lista de pedidos
    $jcart->empty_cart();


    /** Envio de correo**/
    unset($_SESSION["jcart"]);
    unset($codMov);

    if (isset($numeroPedido)) {
        $urlVuelta .= "&pre=" . $numeroPedido;
        $urlVuelta .= "&est=" . $autorizaPedido;
    }
    // echo $urlVuelta;
    // echo "tudo ben papu";
    header('Location: ' . $urlVuelta);
    /*fin**/

    //revisar si el codigo de movimiento no es autocommit y ver como me lo traigo
    //    y lo cambio antes de poder usarlo en este formulario.
    //    $sqlTotal  .="SELECT "

} else {
    $jcart->empty_cart();
    unset($_SESSION["jcart"]);
    unset($codMov);
    //echo "<p>VACIO</p>";

}