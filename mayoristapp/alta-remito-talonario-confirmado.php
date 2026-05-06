<?php

// jCart v1.3
// http://conceptlogic.com/jcart/

// This file is called when any button on the checkout page (PayPal checkout, update, or empty) is clicked
/**
 * Remito x Talonario
 * 
 * Obtengo los datos del remito x talonario sacados de carrito
 * Fecha, NroSucursal, Numero de Comprobante
 * @cartel: 0-> todo bien muestro si hice pedido o remito
 *          1-> ya existe el numero del remito talonario
 */

// Include jcart before session start
/*
 * Parametrizacion de los datos a utilizar.
 */

/*
require_once 'jcart/numero_a_letra.php';
require_once $caminoDispo.'jcart/jcart.php';
require_once 'sesion.inc.php';
require_once '_scripts/php/funciones.php';
$config = $jcart->config;
$urlVuelta = "alta-remito-talonario";
*/

require_once 'jcart/numero_a_letra.php';
require_once 'jcart/jcart.php';
require_once 'sesion.inc.php';
$config = $jcart->config;
$urlVuelta = "alta-remito-talonario";
//echo "<pre>";
//print_r($config);
//echo "</pre>";

/* control de numero de remito talonario repetido */
/**************************************************/

$fechaComp = $_POST['jcart-fecha-rem']; 
$nroPv =   explode("|",$_POST['jcart-suc']);
$nroSuc = $objVendedor->id_sucursal;
$nroForm = $_POST['jcart-nro-rem'];

$nroFormRem = str_pad($nroPv[1], 4, '0', STR_PAD_LEFT) . "-" . str_pad($nroForm, 8, '0', STR_PAD_LEFT);
$nroCompBusqForm = $nroForm;

$sqlControlNum = "SELECT comp_ped.CodigoMovimiento 
                  FROM comp_ped 
                  WHERE 
                  comp_ped.Tipo='Talonario'
                  AND comp_ped.Anulado='No'

                  AND comp_ped.NroComprobante ='{$nroFormRem}'
                  AND comp_ped.NroCompBusq='{$nroForm}'";


$hacerControl = mysqli_query($connV,$sqlControlNum) or die('No puedo consultar el talonario');
$resControl = mysqli_num_rows($hacerControl);
//echo "<pre>";
//print_r($sqlControlNum);
//print_r($resControl);
//echo "</pre>";
///echo $resControl;
if ($resControl > 0) {
    //echo "ADENtRO";

    header('Location: ' . $urlVuelta . '.php?cartel=errorTalonario');
    exit();
}

/* fin de control */

/*
 * Validacion para saber si hago pedido o remito.
 */

$articulos = $jcart->get_contents();

if(empty($articulos)){
    //header('Location: '.$urlVuelta.'.php');
}



$artRem = array();
$artPed = array();

foreach($articulos as $item){
    if($item['entregado']=='Si'){
        // remito
        $artRem[] = $item;
    }else{
        // pedido
        $artPed[] = $item;
        
    }
}
$caminoDispo="";
if (isset($_SESSION["caminoDisp"])){
	$caminoDispo=$_SESSION["caminoDisp"];
}


/*
 * EJECUCION del codigo para hacer un pedido o un remito x talonario
 * 
 * **/
if(isset($_POST["confOperacion"])){
    //print_r($_POST);
    //exit(0);

    $fechaComp = $_POST['jcart-fecha-rem']; 
    $nroPv =   explode("|",$_POST['jcart-suc']);
    $nroSuc = $objVendedor->id_sucursal;
    $nroForm = $_POST['jcart-nro-rem'];

    $codViajante = $objVendedor->CodViajante;
    $codUsuario = $objVendedor->id_usuario;
    
    $detalle = mysqli_real_escape_string($connV,$_POST['jcart-detalle']);
    $formaEntrega = mysqli_real_escape_string($connV,$_POST['formaEntrega']);
    $utilizaEmbalaje=$_SESSION['utilizaEmbalaje'] ;
    /*
     * Cliente
     */
    if(is_object($_SESSION['cliente'])){
        $clienteObj = $_SESSION['cliente'];
    }else{
        $clienteObj = $_SESSION['cliente'][0];
    }
     $cliente_codigo            = $clienteObj->Codigo;
     $cliente_condVta           = $clienteObj->condVenta;
     $cliente_idCv              = $clienteObj->id_cv;
     $cliente_idSucursal        = $clienteObj->id_sucursal;
     $cliente_cod_viajante      = $clienteObj->codViajante;
     $cliente_cod_lista_precio  = $clienteObj->codListaPrecio;
        
    /*
     * REMITO X TALONARIO
     */

    if (!empty($artRem)) {

        // colocar el recalculo de los valores totales.
        
        $subtotalNeto       = 0;
        $subtotalNetoIva21  = 0;
        $subtotalNetoIva105 = 0;
        $subtotalExento     = 0;
        $subtotalImpInt     = 0;
        $subtotalIva105     = 0;
        $subtotalIva21      = 0;
        $subtotalDesc       = 0;
        $subtotalDesc21     = 0;
        $subtotalDesc105    = 0;
        $importeDesc21      = 0;
        $importeDesc105     = 0;
        $importeDesc        = 0;
        //$this->descuentoPie         = 0;
        foreach ($artRem as $item) {
            //            $item['subtotalIva']    = $item['impIva'] * $item['qty'];
            //            $item['subtotalImpInt'] = $item['impInterno'] * $item['qty'];
            //            $item['subtotalExento'] = $item['priceN'] * $item['qty'];
            //            $item['subtotalNeto']   = $item['netoN'] * $item['qty'];
            //            $item['subtotal']       = $item['priceN'] * $item['qty'];

            
            $subtotalNeto +=$item['subtotalNeto'];
            $netoT = $item['subtotalNeto'];
            // descuento al Pie
            $porDescPie     = 0;
            $impDescPie     = ($netoT * $porDescPie/100);
            $netoDescuento  = $netoT - ($netoT * $porDescPie/100);
            
            $subtotalDesc   += $netoDescuento; 
            $importeDesc    += $impDescPie;
            
            // EXENTO
            if($item['tipoIva']=="Exento"){
                
                // calculo el exento
                $subtotalExento += $netoDescuento;
            }else{
            
                // IVA
                $alicuota = $item['alicuota'];
                $totalImpuesto = $netoDescuento * $alicuota /100;

                if ($item['iva'] == 1) {
                    $subtotalIva21      += $totalImpuesto;
                    $subtotalNetoIva21  += $netoT;
                    $subtotalDesc21     += $netoDescuento;
                    $importeDesc21      += $impDescPie;   
                }
                if ($item['iva'] == 2) {
                    $subtotalIva105     += $totalImpuesto;
                    $subtotalNetoIva105 += $netoT;
                    $subtotalDesc105    += $netoDescuento;
                    $importeDesc105     += $impDescPie;
                }
            }
            // impuesto interno
            $impInterno = $item['impInterno'];
            $subtotalImpInt += $netoDescuento * $impInterno / 100;
            
            //$subtotal += $item['subtotal'];

            //$this->subtotalNeto += ($this->qtys[$item] * ($this->prices[$item]- $this->impInterno[$item]));
            // Total number of items
        }
        
        // SubTotal Remito
        $subtotal = $subtotalDesc + $subtotalImpInt + $subtotalIva21 + $subtotalIva105;

        //$puntoVentaRem = $nroPv[1];
        $nroFormRem = str_pad($nroPv[1], 4, '0', STR_PAD_LEFT) . "-" . str_pad($nroForm, 8, '0', STR_PAD_LEFT);
        $nroCompBusqForm = $nroForm;

        $sqlControlNum = "SELECT comp_ped.CodigoMovimiento 
                          FROM comp_ped 
                          WHERE 
                          comp_ped.Tipo='Talonario'

                          AND comp_ped.NroComprobante ='{$nroFormRem}'
                          AND comp_ped.NroCompBusq='{$nroForm}'";


        $hacerControl = mysqli_query($connV,$sqlControlNum) or die('No puedo consultar el talonario');
        $resControl = mysqli_num_rows($hacerControl);
        ///echo $resControl;
        if ($resControl > 0) {
            //echo "ADENtRO";

            header('Location: ' . $urlVuelta . '.php?cartel=errorTalonario');
            exit();
        }



        // inicio de transacciones
        $errores = 0;
        $sqlTotal = "SET AUTOCOMMIT =0;";
        $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo iniciar autocommit ' . mysqli_error($connV));
        $sqlTotal = "BEGIN;";
        $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo hacer Begin ' . mysqli_error($connV));

        // recupero el codigo de movimiento
        $sqlMovi = "SELECT CodigoMovimiento + 1 as CodigoMovNew FROM codmov WHERE codigo = 1";
        $resultado = mysqli_query($connV,$sqlMovi) or die('No puedo recuperar el codigo de movimiento' . mysqli_error($connV));
        if (!$resultado) {
            $errores++;
        }
        // recupero el nuevo codigo de movimiento
        $codMov = mysqli_fetch_assoc($resultado);
        $codMovRemito = $codMov["CodigoMovNew"];
//        echo "<pre>";
//        print_r($codMov);
//        echo "</pre>";
        // actualizo el codigo de movimiento en la tabla codigo de movimiento.
        $sqlMoviUp = "UPDATE codmov 
                        SET CodigoMovimiento=" . $codMovRemito . " 
                        WHERE codmov.codigo=1;";
        $resultado = mysqli_query($connV,$sqlMoviUp) or die('No puedo modificar el codigo de movimiento' . mysqli_error($connV).$sqlMoviUp);
        if (!$resultado) {
            $errores++;
        }


        // $formArr = $jcart->muestra_pedido();
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
            $arrCliente = $_SESSION['cliente'][1];
            if ($arrCliente['exceso'] == 1) {
                $autorizaPedido = 'No Autorizado';
            } else {
                $autorizaPedido = 'Autorizado';
            }
        } else {
            $autorizaPedido = 'No Autorizado';
        }
        
        /*
        * REMITO de FACTURA SIN STOCK
        * =========================================================================
        */
       $datoFact="";
       $datoStFact="";
       $estadoRem="Pendiente";
       if(isset($_SESSION["sel_factura"])){
           $fact = $_SESSION["sel_factura"]["fact"];
           $stFact= $_SESSION["sel_factura"]["art"];
           $estadoRem="Facturado";

       }
        
        
        // alta del pedido
        //echo "<pre>".print_r($formArr)."</pre>";
        $sqlFormIns = "INSERT INTO 
                                comp_ped
						SET		
                        Fecha='" . $fechaComp . "',
                        Tipocomprobante='REM',
                        CodSucursal= '" . $nroSuc . "',
                        idUsuario='" . $codUsuario . "',
                        NroComprobante='" . $nroFormRem . "',
                        NroCompBusq='" . $nroCompBusqForm . "',
                        id_pv='" . $$nroPv[0] . "',
                        Detalle='" .  $detalle . "',
                        ImporteVenta='" . $subtotal . "',
                        ImporteVentaL='" . num2letras(number_format($subtotal, 2,".","")). "',
                        Iva1='" . $subtotalIva21 . "',
                        Iva2='" . $subtotalIva105 . "',
                        Alicuota1= '21',
                        Alicuota2='10.5',
                        Exento= '" . $subtotalExento . "',
                        anulado='No',
                        Subtotal1='" . $subtotalNetoIva21 . "',
                        Subtotal2='" . $subtotalNetoIva105 . "',
                        SubtotalGral= '" . ($subtotalNetoIva21 + $subtotalNetoIva105) . "',
                        PorDesc1='".$porDescPie."',
                        PorDesc2='".$porDescPie."',
                        ImpDesc1='".$importeDesc21."',
                        ImpDesc2= '".$importeDesc105."',
                        SubTotalDesc1='" . $subtotalDesc21 . "',
                        SubTotalDesc2='" . $subtotalDesc105 . "',
                        SubtotalDesc='" . $subtotalDesc . "',
                        Codigo='" . $cliente_codigo. "',
                        CondVenta='" . $cliente_condVta . "',
                        id_condventa='" . $cliente_idCv . "',
                        CodigoMovimiento='" . $codMovRemito . "',
                        Estado= '".$estadoRem."',
                        Vencimiento='" . $vencimiento . "',
                        CodViajante='" . $codViajante . "',
                        TipoPedido= 'Web',
                        impuesto_interno_total='" . $subtotalExento . "',
                        autorizacion_sistema='" . $autorizaPedido . "',
                        formaentrega='" . $formaEntrega . "',
                        FechaEntrega='" . $fechaComp . "',
                        fecha_control='" . date('d/m/Y H:i') . "',
                        id_deposito_despacho='" . $_SESSION['deposito'] . "',
                        Tipo='Talonario';";
        $resultado = mysqli_query($connV,$sqlFormIns) or die('No puedo insertar el remito' . mysqli_error($connV));
        if (!$resultado) {
            $errores++;
        }
        //    echo "<p>$sqlFormIns</p>";

        $controlFactCompleta=0;
        foreach ($artRem as $cc => $articulo) {
            //inserto actualizar la tabla stock_deposito.
            //        echo "<pre>". print_r($articulo) ."</pre>";
            //        seteando las promociones.
            $idArt = str_replace('p', '', $articulo['id']);

            $promocion = 'No';
            $promocion_por = 0;
            $descuento_por = 0;
            $promocion_tipo = '';
            $promocion_cant = 0;

            //pregunto si tiene promociones
            if ($articulo['promo'] == 'si') {
                $promocion = 'Si';
                if ($articulo['descTotal'] != 0) {
                    $promocion_por = $articulo['promoPorc'];
                    $descuento_por = $promocion_por;
                }
                if ($articulo['promoCant'] > 0) {

                    $promocion_cant = $articulo['promoCant'];
                }
                $promocion_tipo = $articulo['promoTipo'];
            } else {
                $descuento_por = $articulo['descPor'];
            }
            
            /*
         * CONTROL STOCK FACTURA SIN STOCK
         */
            $stFt="";
            if(isset($stFact)){
                $saldoEntrega   = $stFact[$idArt]["cuanto"] - $articulo['qty'];
                $idStock        = $stFact[$idArt]["id_stock"];
                $ceroStock="";
                if($saldoEntrega>0){
                    $controlFactCompleta++;
                }
				
                if($saldoEntrega==0){
                    $ceroStock=",st.entregado_fact_total='Si'";
                }
                //
                $stFt=  ",codmov_factura={$fact["codmov"]},"
                        . "NroFactura='{$fact["nrofact"]}',"
                        . "id_stock_factura={$idStock}";

                $sqlSfact="UPDATE Stock AS st SET "
                        . "st.cantidad_entregada_pend='{$saldoEntrega}'  {$ceroStock}"
                        . "WHERE st.id_stock={$idStock}";
                $hst=mysqli_query($connV,$sqlSfact) or die("No puedo actualizar el renglon factura no stock ".mysqli_error($connV)."<pre>".$sqlSfact."</pre>");  
                if(!$hst){
                   $errores++; 
                }
            }
            
            
            
            /**
             * SALDO
             */
            $sqlStockDep = "SELECT stock_deposito.saldo 
                            FROM stock_deposito 
                            WHERE id_articulo=" . $idArt . "
                          AND id_deposito=" . $articulo['deposito'];
            $resultado = mysqli_query($connV,$sqlStockDep) or die("No puedo recuperar el stock_deposito" . mysqli_error($connV));
            if (!$resultado) {
                $errores++;
            }
            $stockDeposito = mysqli_fetch_object($resultado);
            // valido que no pueda remitar mas de lo que tiene en el stock.
            //        if($stockDeposito->saldo<$articulo['qty']){
            //             $sqlTotal = "ROLLBACK;";
            //             $resultado = mysqli_query($connV,$sqlTotal);
            //             header('Location: '.$urlVuelta.'.php?cartel=errorStock&art='.$idArt);
            //             exit();
            //        }

            $saldoArt = $stockDeposito->saldo;
            $saldoArt -= $articulo['qty'];

            $sqlStockDepUp = "UPDATE stock_deposito 
                          SET saldo = " . $saldoArt . " 
                          WHERE id_articulo=" . $idArt . "
                          AND id_deposito=" . $articulo['deposito'];

            $resultado = mysqli_query($connV,$sqlStockDepUp) or die('No puedo actualizar el stock_deposito' . mysqli_error($connV));
            if (!$resultado) {
                $errores++;
            }

            /*
             * LOTE
             */
            if ($articulo['lote'] == 'Si') {
                $idLote = $articulo['idLote'];
                // control de lote descargar el stock del lote
                $sqlLote = "SELECT * FROM Lote 
                            INNER JOIN lote_stock ON (lote.id_lote = lote_stock.id_lote) 
                            WHERE lote.id_lote = " . $idLote . " 
                            AND lote_stock.id_deposito = " . $articulo['deposito'] . " 
                            AND lote.anulado = 'No'";
                $resultado = mysqli_query($connV,$sqlLote) or die("Error al localizar el lote" . mysqli_error($connV));
                if (!$resultado) {
                    $errores++;
                }
                $lotes = mysqli_fetch_assoc($resultado);
                //control si sobre pasa el stock del lote por deposito
                if ($lotes["stock_lote"] < $articulo['qty']) {
                    // el stock es inferior.
                    
                } else {
                    $stockLoteTotal = $lotes["stock_total_lote"] - $articulo['qty'];
                    $stockLoteDeposito = $lotes["stock_lote"] - $articulo['qty'];
                    
                    // el stock esta bien actualizo el lote.
                    $sqlUpLoteT = "UPDATE lote SET stock_total_lote=" . $stockLoteTotal . "
                                    WHERE id_lote=" . $idLote;
                    $resultado = mysqli_query($connV,$sqlUpLoteT) or die('No puedo modificar el stock en Lote' . mysqli_error($connV));
                    if (!$resultado) {
                        $errores++;
                    }

                    $sqlUpLoteD = "UPDATE lote_stock "
                                . "SET stock_lote=" . $stockLoteDeposito . " 
                                   WHERE id_lote=" . $idLote . " 
                                   AND id_deposito=" . $articulo['deposito'];
                    $resultado = mysqli_query($connV,$sqlUpLoteD) or die("No puedo modificar el lote_stock" . mysqli_error($connV));
                    if (!$resultado) {
                        $errores++;
                    }
                }
            } else {
                $idLote = "NULL";
            }

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
                                articulo.tipo_art
                                FROM
                                    articulo
                                LEFT JOIN activ_iibb ON activ_iibb.ID = articulo.AlicuotaIb
                                WHERE articulo.IDArt = " . $idArt;
            $resultado = mysqli_query($connV,$sqlArtDb) or die('No puedo consultar el articulo en la base de datos' . mysqli_error($connV));
            if (!$resultado) {
                $errores++;
            }
            $artObj = mysqli_fetch_object($resultado);

            /*
             * Embalaje
             */
            if ($usaEmbalaje == 'Si') {

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
                $resultado = mysqli_query($connV,$artEmbV) or die("No puedo recuperar los articulos de embalaje" . mysqli_error($connV));
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
                $resultado = mysqli_query($connV,$artEmC) or die("No puedo recuperar los datos del proveedor" . mysqli_error($connV));
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
            /* Fin Embalaje */

            $sqlStock = "INSERT INTO stock SET 
                        IDArt='" . $idArt . "',
                        Fecha='" . date('Y/m/d') . "',
                        CodigoArticulo='" . $artObj->CodigoArticuloT . "',
                        CodigoMovimiento='" . $codMovRemito . "',
                        Descripcion='" . $artObj->NombreArticulo . "',
                        Salida='" . $articulo['qty'] . "',
                        saldo='" . $saldoArt . "',
                        ImpDesc='" . ($articulo['qty'] * $articulo['descTotal']) . "',
                        PorDesc='" . $descuento_por . "',
                        PrecioCostoxU='" . $artObj->PrecioCosto . "',
                        PrecioVentaxU='" . $articulo['neto'] . "',
                        PrecioBrutoxU='" . $articulo['priceN'] . "',
                        PrecioIVAxU='" . $articulo['impIva'] . "',
                        PrecioNetoxU='" . $articulo['netoN'] . "',
                        PrecioCostoxR='" . ($artObj->PrecioCosto * $articulo['qty']) . "',
                        PrecioVentaxR='" . ($articulo['neto'] * $articulo['qty']) . "',
                        PrecioBrutoxR='" . $articulo['subtotal'] . "',
                        PrecioNetoxR='" . $articulo['subtotalNeto'] . "',
                        PrecioIVAxR='" . $articulo['subtotalIva'] . "',
                        Alicuota='" . $articulo['iva'] . "',
                        AlicuotaIB='" . $artObj->IdAlicuotaIb . "',
                        Cantidad='" . $articulo['qty'] . "',
                        CodigoCP='" . $cliente_codigo . "',
                        Tipo='Cliente',
                        Comprobante='REM',
                        TipoComp='Remito Salida',
                        NroComprobante='" . $nroFormRem . "',
                        NroRemito='" . $nroFormRem . "',
                        Anulado='No',
                        TipoIVA='" . $articulo['tipoIva'] . "',
                        CodDeposito='" . $articulo['deposito'] . "',
                        IdUsuario='" . $codUsuario . "',
                        CodSucursal='" . $nroPv[0] . "',                                        
                        CodViajante='" . $codViajante . "',
                        CodLaboratorio='" . $artObj->CodLaboratorio . "',
                        id_lote=" . $idLote . ",
                        stock_lote_deposito='" . $stockLoteDeposito . "',
                        tipo_art='" . $artObj->tipo_art . "',
                        imp_alicuota_iva='" . $articulo['alicuota'] . "',
                        imp_alicuota_iibb='" . $artObj->alicuotaIb . "',
                        id_manual='" . $artObj->id_manual . "',
                        Orden=".$cc.",
                        lista_precio='" . $cliente_cod_lista_precio . "',
                        promocion='" . $promocion . "',
                        promocion_por='" . $promocion_por . "',
                        promocion_tipo='" . $promocion_tipo . "',
                        promocion_cant='" . $promocion_cant . "'
                        " . $campEmbV . $campEmbC . $stFt . "; ";
            $resultado = mysqli_query($connV,$sqlStock) or die('No puedo insertar el articulo x remito' . mysqli_error($connV) . "<pre>" . $sqlStock . "</pre>");
            if (!$resultado) {
                $errores++;
            }
        }
        
        if(isset($fact)){
            $estadoFactura="Parcial";
            if($controlFactCompleta==0){
                $estadoFactura="En Remito";
            }
			
            $codMovFact=$fact["codmov"];
			
            $sqlF="UPDATE cuentacliente AS cc SET "
                    . "cc.estado_fact_remito='{$estadoFactura}'"
                    . "WHERE cc.CodigoMovimiento={$codMovFact}";
            $r=mysqli_query($connV,$sqlF) or die("No puedo modificar Fact ".mysqli_error($connV)."<pre>".$sqlF."</pre>");
            if(!$r){
                $errores++;
            }
            $sqlIfact="INSERT INTO rem_fact SET "
                    . "rem_fact.CodigoMovimientoR={$codMovRemito},"
                    . "rem_fact.CodigoMovimientoF={$codMovFact};";
             $r=mysqli_query($connV,$sqlIfact) or die("No puedo inserta relacion Fact Re ".mysqli_error($connV)."<pre>".$sqlIfact."</pre>");
            if(!$r){
                $errores++;
            }
            //termine con la factura seleccionada, elimino de la sesion.

        }
        
        

        if ($errores == 0) {
            $sqlTotal = "COMMIT;";
            $resultado = mysqli_query($connV,$sqlTotal);
            //echo "todo bien";
        } else {
            $sqlTotal = "ROLLBACK;";
            $resultado = mysqli_query($connV,$sqlTotal);
            //echo "todo mal";
        }
    } // Fin remito


    /*
     * PEDIDO X Talonario.
     */

    if(count($artPed)>0){
        //    echo "<pre>";
        //    print_r($artPed);
        //    echo "</pre>";
        $errores = 0;
        $sqlTotal = "SET AUTOCOMMIT =0;";
        $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo iniciar autocommit ' . mysqli_error($connV));
        $sqlTotal = "BEGIN;";
        $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo hacer Begin ' . mysqli_error($connV));

        // recupero el codigo de movimiento
        $sqlMovi = "SELECT CodigoMovimiento + 1 as CodigoMovNew FROM codmov WHERE codigo = 1";
        $resultado = mysqli_query($connV,$sqlMovi) or die('No puedo recuperar el codigo de movimiento' . mysqli_error($connV));
        if (!$resultado) {
            $errores++;
        }
        // recupero el nuevo codigo de movimiento
        $codMov = mysql_fetch_assoc($resultado);
        $codMovPedido = $codMov["CodigoMovNew"];
        $idPuntoVenta = $objVendedor->id_punto_venta;
        // actualizo el codigo de movimiento en la tabla codigo de movimiento.
        $sqlMoviUp = "UPDATE codmov 
                    SET CodigoMovimiento=" . $codMovPedido . " 
                    WHERE codmov.codigo=1;";
        $resultado = mysqli_query($connV,$sqlMoviUp) or die('No puedo modificar el codigo de movimiento' . mysqli_error($connV));
        if (!$resultado) {
            $errores++;
        }
                
        // cierro la transaccion.
        if($errores == 0){
            $sqlTotal= "COMMIT;";
            $resultado = mysqli_query($connV,$sqlTotal);
            //echo "todo bien";
        }else{
            $sqlTotal = "ROLLBACK;";
            $resultado = mysqli_query($connV,$sqlTotal);
            //echo "todo mal";
        }
        
        $pedidoArr = $jcart->muestra_pedido();             
        
        // colocar el recalculo de los valores totales.
        $subtotalNeto       = 0;
        $subtotalNetoIva21  = 0;
        $subtotalNetoIva105 = 0;
        $subtotalExento     = 0;
        $subtotalImpInt     = 0;
        $subtotalIva105     = 0;
        $subtotalIva21      = 0;
        $subtotalDesc       = 0;
        $subtotalDesc21     = 0;
        $subtotalDesc105    = 0;
        $importeDesc21      = 0;
        $importeDesc105     = 0;
        $importeDesc        = 0;

        //$this->descuentoPie         = 0;
        foreach ($artPed as $item) {


            $subtotalNeto +=$item['subtotalNeto'];
            $netoT = $item['subtotalNeto'];
            // descuento al Pie
            $porDescPie     = $item['porDescPie'];
            $impDescPie     = ($netoT * $porDescPie/100);
            $netoDescuento  = $netoT - ($netoT * $porDescPie/100);
            
            $subtotalDesc   += $netoDescuento; 
            $importeDesc    += $impDescPie;
            
            // EXENTO
            if($item['tipoIva']=="Exento"){
                
                // calculo el exento
                $subtotalExento += $netoDescuento;
            }else{
            
                // IVA
                $alicuota = $item['alicuota'];
                $totalImpuesto = $netoDescuento * $alicuota /100;

                if ($item['iva'] == 1) {
                    $subtotalIva21      += $totalImpuesto;
                    $subtotalNetoIva21  += $netoT;
                    $subtotalDesc21     += $netoDescuento;
                    $importeDesc21      += $impDescPie;   
                }
                if ($item['iva'] == 2) {
                    $subtotalIva105     += $totalImpuesto;
                    $subtotalNetoIva105 += $netoT;
                    $subtotalDesc105    += $netoDescuento;
                    $importeDesc105     += $impDescPie;
                }
            }
            // impuesto interno
            $impInterno = $item['impInterno'];
            $subtotalImpInt += $netoDescuento * $impInterno / 100;

        }
        
        $subtotal = $subtotalDesc + $subtotalImpInt + $subtotalIva21 + $subtotalIva105 + $pedidoArr["percepcionesT"];
        
        // reinicio la transaccion
        $errores = 0;
        $sqlTotal = "SET AUTOCOMMIT =0;";
        $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo iniciar autocommit '.mysqli_error($connV));
        $sqlTotal = "BEGIN;";
        $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo hacer Begin '.mysqli_error($connV)); 
        // obtengo el numero de comprobante del pedido

        $sqlTalon = "SELECT * 
                    FROM talonarios 
                    WHERE id_punto_venta = " . $idPuntoVenta . " 
                    AND TipoComprobante = 'PED'";
        $resultado = mysqli_query($connV,$sqlTalon) or die('No puedo recuperar el talonario' . mysqli_error($connV));
        if (!$resultado) {
            $errores++;
        }
        $objTalonario = mysqli_fetch_assoc($resultado);
//        echo "<pre>";
//        var_dump($objTalonario);
//        echo "</pre>";
        $numeroPedido = str_pad($objTalonario["PV"],4,"0",STR_PAD_LEFT) . "-" . str_pad($objTalonario["Nro"], 8, '0',STR_PAD_LEFT);
        $nroCompBusqPedido = $objTalonario["Nro"];

        // actualizo el talonario
        $sqlTalonUp = "UPDATE talonarios 
                            SET Nro = ".$objTalonario["Nro"]."+1 
                            WHERE id_punto_venta = '".$idPuntoVenta."' 
                            AND TipoComprobante = 'PED'"; 
        $resultado = mysqli_query($connV,$sqlTalonUp) or die('No puedo actualizar el talonario' . mysqli_error($connV)."<p>".$sqlTalonUp."</p>");
        if(!$resultado){
            $errores++;
        }
         
        $vencimiento = date('Y/m/d', mktime(0,0,0,date('m')+1,date('d'),date('Y')));

    //    estados del pedido
    //    -> si es pedido del vendedor entra autorizado salvo que no pueda por los dias
    //    -> si es pedido del cliente entra No autorizado 
    //    Autorizado
    //    No Autorizado
    //    
        $autorizaPedido = '';
        if(isset($objVendedor)){
    //        existe el vendedor a comprobar si el cliente esta o no autorizado.
    //        $arrCliente = $_SESSION['cliente'][1];
            if($arrCliente['exceso']==1){
                $autorizaPedido = 'No Autorizado';
            }else{
                $autorizaPedido = 'Autorizado';
            }
        }else{
            $autorizaPedido = 'No Autorizado';
        }
        
        /*
         *     alta de datos adicionales del CLIENTE
         */
        
        if($_POST["domicilio_entrega"]==""){
            $idDomEntrega = 'NULL';
        }else{
            $domEntrega = explode("|", $_POST["domicilio_entrega"]);
            $idDomEntrega = "'".$domEntrega[0]."'";
        }
        /*Logistica*/
        if($_SESSION["activ_logistica"]=="Si"){
            if($_POST["hoja_ruta"]==""){
                $idRuta = "NULL";
            }else{
                $idRuta ="'".$_POST["hoja_ruta"]."'";
            }
        }else{
            $idRuta = "NULL";
        }
        $depositoOrigen = $_SESSION['deposito'];
        
        
        $sqlDatoCliente = "INSERT INTO cliente_datos_adicionales 
							SET "
                . " fechaEntrega='" . date('Y/m/d') . "' ,"
                . " id_deposito_despacho='" . $depositoOrigen . "',"
                . " Fentrega='" . $formaEntrega . "',"
                . " origen_pedido='Web',"
                . " TipoComprobante='PED', "
                . " id_cliente='" . $cliente_codigo . "', "
                . " CodigoMovimiento='" . $codMovPedido . "',"
                . " id_cliente_domicilio=". $idDomEntrega.","
                . "id_ruta=". $idRuta.";";
                
                
        $resultado = mysqli_query($connV,$sqlDatoCliente) or die('No puedo insertar dato adicional del cliente' . mysqli_error($connV));
        if (!$resultado) {
            $errores++;
        }
//    echo "Datos adicionales OK<br>";
        /*
         * PERCEPCIONES
         * 
         */
        $percepciones = $pedidoArr["percepciones"]["detalle"];
//    echo "<pre>";
//    print_r($pedidoArr);
//    echo "</pre>";
        if(!empty($percepciones)){
            foreach($percepciones as $kp => $per){
        //        echo "<pre>";
        //    print_r($per);
        //    echo "</pre>";
                $sqlPerc = "INSERT INTO percep_cli 
							SET 
							id_percep_cli_tipo='".$per["id"]."',
							alicuota_percep_cli='".$per["alic"]."',
							importe_percep_cli='".$per["monto"]."',
							codigo_movimiento='".$codMovPedido."',
							id_cliente='".$clienteObj->Codigo."',
							tipo_comp='PED';                                            
							";
                $resultado = mysqli_query($connV,$sqlPerc) or die("No puedo insertar la percepcion" . mysqli_error($connV));
                if(!$resultado){
                    $errores++;
                }
            }
        }
//    echo "Percepciones OK<br>";
        // alta del pedido
        //echo "<pre>".print_r($pedidoArr)."</pre>";
        $sqlPedidoIns = "INSERT INTO 
                            comp_ped
						SET
						Fecha='" . date('Y/m/d') . "',
						Tipocomprobante='PED',
						CodSucursal='" . $nroSuc . "',
						idUsuario='" . $_SESSION['idusuario'] . "',
						NroComprobante='" . $numeroPedido . "',
						NroCompBusq='" . $nroCompBusqPedido . "',
						id_pv=1,
						Detalle='" . $detalle . "\n pedido automático generado por remito x talonario " . $nroFormRem . "',
						ImporteVenta='" . $subtotal . "',
						ImporteVentaL='" . num2letras(number_format($subtotal, 2)) . "',
						Iva1='" . $subtotalIva21 . "',
						Iva2='" . $subtotalIva105 . "',
						Alicuota1='21',
						Alicuota2= '10.5',
						Exento='" . $subtotalExento . "',
						anulado='No',
						Subtotal1='" . $subtotalNetoIva21 . "',
						Subtotal2= '" . $subtotalNetoIva105 . "',
						SubtotalGral='" . ($subtotalNetoIva21 + $subtotalNetoIva105) . "',
						PorDesc1='".$porDescPie."',
						PorDesc2='".$porDescPie."',
						ImpDesc1='".$importeDesc21."',
						ImpDesc2='".$importeDesc105."',
						SubTotalDesc1='" . $subtotalDesc21 . "',
						SubTotalDesc2='" . $subtotalDesc105 . "',
						SubtotalDesc='" . $subtotalDesc . "',
						Codigo='" . $cliente_codigo . "',
						CondVenta='" . $cliente_condVta . "',
						id_condventa='" . $cliente_idCv . "',
						CodigoMovimiento='" . $codMovPedido . "',
						Estado='Pendiente',
						Vencimiento='" . $vencimiento . "',
						CodViajante='" . $cliente_cod_viajante . "',
						TipoPedido= 'Web',
						impuesto_interno_total='" . $subtotalExento . "',
						autorizacion_sistema='" . $autorizaPedido . "',
						formaentrega='" . $formaEntrega . "',
						fecha_control='" . date('d/m/Y H:i') . "',
						id_deposito_despacho='" . $depositoOrigen . "',
						FechaEntrega='" . date('d/m/Y H:i') . "',
						total_percep='" . $pedidoArr["percepcionesT"] . "';";
        $resultado = mysqli_query($connV,$sqlPedidoIns) or die('No puedo insertar el pedido' . mysqli_error($connV) . "<br>" . $sqlPedidoIns . "</br>");
        if (!$resultado) {
            $errores++;
        }
//    echo "<p>$sqlPedidoIns</p>";


        foreach ($artPed as $articulo) {
            //inserto actualizar la tabla stock_deposito.
//        echo "<pre>". print_r($articulo) ."</pre>";
//        seteando las promociones.

            $idArt = str_replace('p', '', $articulo['id']);
            $promocion = 'No';
            $promocion_por = 0;
            $descuento_por = 0;
            $promocion_tipo = '';
            $promocion_cant = 0;

            //pregunto si tiene promociones
            if ($articulo['promo'] == 'si') {
                $promocion = 'Si';
                if ($articulo['descTotal'] != 0) {
                    $promocion_por = $articulo['promoPorc'];
                    $descuento_por = $promocion_por;
                }
                if ($articulo['promoCant'] > 0) {
                    $promocion_tipo = 'Cantidad';
                    $promocion_cant = $articulo['promoCant'];
                }
            } else {
                $descuento_por = $articulo['descPor'];
            }

            $sqlStockDep = "SELECT saldo_pedido_cliente 
                        FROM stock_deposito 
                        WHERE id_articulo=" . $idArt . "
                      AND id_deposito=1";
            $resultado = mysqli_query($connV,$sqlStockDep) or die("No puedo recuperar el stock_deposito" . mysqli_error($connV));
            if (!$resultado) {
                $errores++;
            }
            $stockDeposito = mysql_fetch_object($resultado);

            $saldoArt = $stockDeposito->saldo_pedido_cliente;
            $saldoArt += $articulo['qty'];

            $sqlStockDepUp = "UPDATE stock_deposito 
                      SET saldo_pedido_cliente = " . $saldoArt . " 
                      WHERE id_articulo=" . $idArt . "
                      AND id_deposito=1";

            $resultado = mysqli_query($connV,$sqlStockDepUp) or die('No puedo actualizar el stock_deposito' . mysqli_error($connV));
            if (!resultado) {
                $errores++;
            }
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
                            articulo.tipo_art
                            FROM
                                articulo
                            LEFT JOIN activ_iibb ON activ_iibb.ID = articulo.AlicuotaIb
                            WHERE articulo.IDArt = " . $idArt;
            $resultado = mysqli_query($connV,$sqlArtDb) or die('No puedo consultar el articulo en la base de datos' . mysqli_error($connV));
            if (!$resultado) {
                $errores++;
            }
            $artObj = mysqli_fetch_object($resultado);
            /*
             * Embalaje
             */
            if ($usuario->utiliza_embalaje == 'Si') {

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
                $resultado = mysqli_query($connV,$artEmbV) or die("No puedo recuperar los articulos de embalaje" . mysqli_error($connV));
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
                $resultado = mysqli_query($connV,$artEmC) or die("No puedo recuperar los datos del proveedor" . mysqli_error($connV));
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

            $sqlStock = "INSERT INTO stockp 
						SET 
						Saldo='" . $saldoArt . "',
						impuesto_interno='" . $artObj->impuesto_interno . "',
						impuesto_interno_subtotal= '" . $articulo['impInterno'] . "',
						Fecha='" . date('Y/m/d') . "',
						CodigoArticulo=  '" . $artObj->CodigoArticuloT . "',
						Descripcion= '" . $artObj->NombreArticulo . "',
						PrecioVentaxU='" . $articulo['neto'] . "',
						PrecioCostoxU= '" . $artObj->PrecioCosto . "',
						PrecioIVAxU= '" . $articulo['impIva'] . "',
						PrecioBrutoxU='" . $articulo['priceN'] . "',
						PrecioNetoxU= '" . $articulo['netoN'] . "',
						PrecioVentaxR='" . ($articulo['neto'] * $articulo['qty']) . "',
						PrecioCostoxR='" . ($artObj->PrecioCosto * $articulo['qty']) . "',
						PrecioIVAxR= '" . $articulo['subtotalIva'] . "',
						PrecioBrutoxR= '" . $articulo['subtotal'] . "',
						PrecioNetoxR='" . $articulo['subtotalNeto'] . "',
						Alicuota='" . $articulo['iva'] . "',
						AlicuotaIB='" . $artObj->IdAlicuotaIb . "',
						imp_alicuota_iva='" . $articulo['alicuota'] . "',
						imp_alicuota_iibb='" . $artObj->alicuotaIb . "',
						Salida='" . $articulo['qty'] . "',
						Cantidad='" . $articulo['qty'] . "',
						ImpDesc= '" . ($articulo['qty'] * $articulo['descTotal']) . "',
						PorDesc='" . $descuento_por . "',
						CodViajante='" . $cliente_cod_viajante . "',
						CodLaboratorio='" . $artObj->CodLaboratorio . "',
						CodigoMovimiento='" . $codMovPedido . "',
						CodDeposito=1,
						IDArt='" . $idArt . "',
						id_manual='" . $artObj->id_manual . "',
						CodSucursal= '" . $cliente_idSucursal . "',
						idusuario='1',
						TipoIVA='" . $articulo['tipoIva'] . "',
						CodigoCP='" . $cliente_codigo . "',
						Tipo='Cliente',
						TipoComp= 'Pedido',
						anulado='No',
						Comprobante='PED',
						NroComprobante=  '" . $numeroPedido . "',
						lista_precio='" . $cliente_cod_lista_precio . "',
						Orden= ".($cc+1).",
						promocion='" . $promocion . "',
						promocion_por='" . $promocion_por . "',
						promocion_tipo= '" . $promocion_tipo . "',
						promocion_cant='" . $promocion_cant . "',						
						tipo_art='" . $artObj->tipo_art . "',						
						cantidad_entregada='" . $articulo['qty'] . "',
						cantidad_pendiente='" . $articulo['qty'] . "';";
            $resultado = mysqli_query($connV,$sqlStock) or die('No puedo insertar el articulo x pedido' . mysqli_error($connV) . "<pre>" . $sqlStock . "</pre>");
            if (!$resultado) {
                $errores++;
            }
        }

        if ($errores == 0) {
            $sqlTotal = "COMMIT;";
            $resultado = mysqli_query($connV,$sqlTotal);
            //echo "todo bien";
        } else {
            $sqlTotal = "ROLLBACK;";
            $resultado = mysqli_query($connV,$sqlTotal);
            //echo "todo mal";
        }
    }//fin pedido

//    si termina tengo que vaciar el carrito y despues tengo que volver a la lista de pedidos
    
	
    $jcart->empty_cart();
    unset($_SESSION["jcart"]);
    
   /*
    * ENVIO CORREO
    * ==========================================================================
    */ 
    
    $_SESSION["tipoComprobante"] = "REM";
    $_SESSION["nroComprobante"]=$numeroFormRem;
    $_SESSION["codigoMovimiento"]=$codMovRemito;
    
    header('Location: fin-comprobante.php');
	
// viejo codigo de vuelta.	
//	if(isset($objVendedor)){
//        //echo "aca estoy";
//        $urlVuelta .=".php?cartel=0";
//        if(isset($nroFormRem)){
//            $urlVuelta .="&rem=".$nroFormRem;
//        }
//        if(isset($numeroPedido)){
//            $urlVuelta .="&ped=".$numeroPedido;
//        }
//        header('Location: '.$urlVuelta);
//    }else{
//        //echo "aca no estoy";
//        header('Location: '.$urlVuelta);
//    }   
	
	
	
	
    //revisar si el codigo de movimiento no es autocommit y ver como me lo traigo
//    y lo cambio antes de poder usarlo en este formulario.
//    $sqlTotal  .="SELECT "
    

    /*
     * Formulario de preproceso si confirma hago todo si no que vuelva a la pagina anterior..
     * **/
}

// busco el lote
//$sqlLote = "SELECT lote.id_lote,lote.cod_lote,lote.fecha_vto_lote,lote.stock_total_lote 
//                    FROM lote
//                    INNER JOIN lote_stock ON (lote.id_lote = lote_stock.id_lote) 
//                    WHERE lote.id_articulo = {$idArt} 
//                    AND lote.anulado ='No' 
//                    AND lote_stock.stock_lote > 0 
//                    AND lote_stock.id_deposito = {$idDeposito} 
//                    ORDER BY lote.fecha_vto_lote ASC";


?>    
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
hacer una unica consulta con los depositos y mostrar los datos en un array para 
hacer la tabla y que las dos tablas o solo las remitidas lo muestren.
-->
<html>
    <head>
        <title>Remito talonario resumen | administraNET </title>
        
       
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
       
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="<?php echo $caminoDispo; ?>_css/main_styles.css"  />
        <link rel="stylesheet" type="text/css" href="<?php echo $caminoDispo; ?>_css/style.css"  />
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">
        
        <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
        <script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>        <link href="<?php echo $caminoDispo;?>_css/tablas.css" rel="stylesheet" type="text/css" />

        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.16/b-1.5.1/b-flash-1.5.1/b-html5-1.5.1/r-2.2.1/datatables.min.css"/> 
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.16/b-1.5.1/b-flash-1.5.1/b-html5-1.5.1/r-2.2.1/datatables.min.js"></script>

        
        <script>
            $(document).ready(function(){
                $("#canceloOp").click(function(){
                    //alert("chau");
                    $(location).attr("href","alta-remito-talonario.php");
                });
            });
        </script>
    </head>
    <body>
        
        <div id="wrapper">
            
                <div id="content">
                    <?php 
                         $pedidoArr = $jcart->muestra_pedido();
                        
                    ?>
                <h1 class="black" style=" text-align: center;">Resumen Total</h1>
                <?php if(!empty($artRem)):?>
                <table class="dataTable"  id="myTable" style="background-color: white" > 
                    <thead>
                        <tr>
                            <th colspan="5" class="dt-center even">Remito Talonario  <strong>Nº <?php echo $nroFormRem;?></strong></th>
                        </tr>
                        <tr>
                            <th>Cod.</th>
                            <th class="dt-left">Artículo</th>
                            <th>Cant.</th>
                            
                            <th>Precio Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $conta=0; $clase="";$subtotal=0;?>
                        <?php foreach($artRem as $a):?>
                            <?php ($conta%2)?$clase="even":$clase="odd";?>
                             <?php $subtotal += $a['subtotal'];?>
                            <tr class="<?php echo $clase?>">
                                <td class="dt-center"><?php echo str_replace('p','', $a['id']);?></td>
                                <td class="dt-left"><?php echo $a['name'];?></td>
                                <td class="dt-center"><strong><?php echo $a['qty'];?></strong></td>
                               
                                <td class="dt-right">$ <?php echo number_format($a['subtotal'], 2, ',', '.');?></td>
                            </tr>
                            <?php $conta++;?>
                        <?php endforeach;?>
                            <tr class="even"><td colspan="5" class="dt-right">Total: $ <?php echo number_format($subtotal, 2, ',', '.')?></td></tr>       
                    </tbody>
                </table>
                <?php endif;?>
                <?php if(!empty($artPed)):?>
                <table class="tablesorter" cellspacing="1" id="myTable" style='width: 98%;padding:1%;margin:1%;  margin-top:10px'> 
                    <thead>
                        <tr>
                            <th colspan="5" style=" text-align: center;">Pedido</th>
                        </tr>
                        <tr>
                            <th>cod</th>
                            <th>Artículo</th>
                            <th>Cant.</th>
                            <th>Ent.</th>
                            <th>Precio Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $conta=0; $clase="";$subtotal=0;?>
                        <?php 
                            foreach($artPed as $item){
                            
                                $subtotalNeto +=$item['subtotalNeto'];
            
                                // descuento al Pie
                                $porDescPie     = $item['porDescPie'];
                                $impDescPie     = ($item['subtotalNeto'] * $porDescPie/100);
                                $netoDescuento  = $item['subtotalNeto'] - ($item['subtotalNeto'] * $porDescPie/100);
                                $subtotalDesc   += $netoDescuento; 
                                $importeDesc    += $impDescPie;

                                // EXENTO
                                if($item['tipoIva']=="Exento"){

                                    // calculo el exento
                                    $subtotalExento += $netoDescuento;
                                }else{

                                    // IVA
                                    $alicuota = $item['alicuota'];
                                    $totalImpuesto = $netoDescuento * $alicuota /100;

                                    if ($item['iva'] == 1) {
                                        $subtotalIva21      += $totalImpuesto;
                                        $subtotalNetoIva21  += $netoDescuento;
                                    }
                                    if ($item['iva'] == 2) {
                                        $subtotalIva105     += $totalImpuesto;
                                        $subtotalNetoIva105 += $netoDescuento;
                                    }
                                }
                                // impuesto interno
                                $impInterno = $item['impInterno'];
                                $subtotalImpInt += $netoDescuento * $impInterno / 100;
                            }
                            // SubTotal Remito
                            $subtotal = $subtotalDesc + $subtotalImpInt + $subtotalIva21 + $subtotalIva105;
                            ?>
                        <?php foreach($artPed as $b):?>
                            <?php ($conta%2)?$clase="even":$clase="odd";?>
                            <tr class="<?php echo $clase?>">
                                <td><?php echo str_replace('p','', $b['id']);?></td>
                                <td><?php echo $b['name'];?></td>
                                <td><?php echo $b['qty'];?></td>
                                <td>No</td>
                                <td><i class='fa fa-dollar'></i><?php echo number_format($b['subtotal'] , 4, '.', ',');?></td>
                            </tr>
                            <?php $conta++;?>
                        <?php endforeach;?>
                            <tr class="even"><td style="text-align: right" colspan="5">Sub Total: <i class='fa fa-dollar'></i> <?php echo number_format($subtotalNeto , 4, '.', ',')?></td></tr>
                            <tr class="even"><td style="text-align: right" colspan="5">Desc Pie:  <?php echo number_format($porDescPie , 4, '.', ',')?>%</td></tr>
                            
                            <tr class="even"><td style="text-align: right" colspan="5">Neto: <i class='fa fa-dollar'></i> <?php echo number_format($subtotalDesc , 4, '.', ',')?></td></tr>
                            <tr class="even"><td style="text-align: right" colspan="5">Iva: <i class='fa fa-dollar'></i> <?php echo number_format($subtotalIva105 + $subtotalIva21, 4, '.', ',')?></td></tr>
                            <tr class="even"><td style="text-align: right" colspan="5">Subtotal Imp Int: <i class='fa fa-dollar'></i> <?php echo number_format($subtotalImpInt, 4, '.', ',')?></td></tr>       
                            <tr class="even"><td style="text-align: right" colspan="5">Percepciones: <i class='fa fa-dollar'></i> <?php echo number_format($pedidoArr["percepcionesT"], 4, '.', ',')?></td></tr>
                            <tr class="even"><td style="text-align: right" colspan="5">Total: <i class='fa fa-dollar'></i> <?php echo number_format($subtotal +$pedidoArr["percepcionesT"], 4, '.', ',')?></td></tr>       
                    </tbody>
                </table>
                <?php endif;?>
            
            
            <form name="frmAcepto" id="frmAcepto" method="post" action="">
                <input type="hidden" name="jcart-fecha-rem" id="jcart-fecha-rem" value="<?php echo $_POST['jcart-fecha-rem'];?>">
                <input type="hidden" name="jcart-suc" id="jcart-suc" value="<?php echo $_POST['jcart-suc'];?>">
                <input type="hidden" name="jcart-nro-rem" id="jcart-nro-rem" value="<?php echo $_POST['jcart-nro-rem'];?>">
                <input type="hidden" name="jcart-detalle" id="jcart-detalle" value="<?php echo $_POST['jcart-detalle'];?>">
                <input type="hidden" name="formaEntrega" id="formaEntrega" value="<?php echo $_POST['formaEntrega'];?>">
                <div id="botonesConfirmado">
                <button name="confOperacion" id="aceptoOp" type="submit" value="ok" class='botonNuevo grande azul'>Aceptar <i class='fa fa-check fa-lg'></i></button>
                <button name="confOperacion" id="canceloOp" type="button" class='botonNuevo grande gris'>Cancelar <i class='fa fa-times fa-lg'></i></button>
                </div>
            </form>
        </div>
            </div>
    </body>
</html>
