<?php

// jCart v1.3
// http://conceptlogic.com/jcart/

// This file is called when any button on the checkout page (PayPal checkout, update, or empty) is clicked
/**
 * Remito x Sistema
 * 
 * Obtengo los datos del remito x talonario sacados de carrito
 * Fecha, NroSucursal, Numero de Comprobante
 * @cartel: 0-> todo bien muestro si hice pedido o remito
 *          1-> ya existe el numero del remito talonario
 */
// Include jcart before session start
require_once 'jcart/numero_a_letra.php';
require_once 'jcart/jcart.php';
require_once 'sesion.inc.php';
$config = $jcart->config;
$urlVuelta = "alta-remito-sistema";

$articulos = $jcart->get_contents();
if(empty($articulos)){
    header('Location: '.$urlVuelta.'.php');
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







$idPvVendedor =   $objVendedor->id_punto_venta;

$codViajante = $objVendedor->CodViajante;
$codUsuario = $objVendedor->id_usuario;

// obtengo los articulos del carrito de compras

//$articulos = $jcart->get_contents();


// The update and empty buttons are displayed when javascript is disabled 
// Re-display the cart if the visitor has clicked either button

//if(!empty($articulos)){

/*
 * REMITO X SISTEMA
 */
$utilizaEmbalaje=$_SESSION['utilizaEmbalaje'] ;
if(!empty($artRem)){
    if(is_object($_SESSION['cliente'])){
        $clienteObj = $_SESSION['cliente'];
    }else{
        $clienteObj = $_SESSION['cliente'][0];
    }
    
    if(isset($_SESSION["id_sucursal"])){
        $idSucVendedor = $_SESSION["id_sucursal"];
    }else{
        
        $idSucVendedor = $clienteObj->id_sucursal;
    }
    
    
    
    $codListaPrecio=$clienteObj->codListaPrecio;
    // guardamos el pedido primero 
    // inicio de transacciones
    $errores = 0;
    $sqlTotal = "SET AUTOCOMMIT =0;";
    $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo iniciar autocommit '.mysqli_error($connV));
    $sqlTotal = "BEGIN;";
    $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo hacer Begin '.mysqli_error($connV));
    
    // recupero el codigo de movimiento
    $sqlMovi = "SELECT CodigoMovimiento + 1 as CodigoMovNew FROM codmov WHERE codigo = 1";
    $resultado = mysqli_query($connV,$sqlMovi) or die('No puedo recuperar el codigo de movimiento'.mysqli_error($connV));
    if(!$resultado){
        $errores++;
    }
    // recupero el nuevo codigo de movimiento
   $codMov = mysqli_fetch_assoc($resultado);
   $codMovRemito = $codMov["CodigoMovNew"];
    
    // actualizo el codigo de movimiento en la tabla codigo de movimiento.
    $sqlMoviUp  = "UPDATE codmov 
                    SET CodigoMovimiento=" . $codMovRemito . " 
                    WHERE codmov.codigo=1;";
    $resultado = mysqli_query($connV,$sqlMoviUp) or die('No puedo modificar el codigo de movimiento'.mysqli_error($connV));
    if(!$resultado){
        $errores++;
    }
//    
//    // obtengo el numero de comprobante del pedido
    // revisar que hago con el punto de venta del vendedor si lo hago 
    $sqlTalon = "SELECT * 
                    FROM talonarios 
                    WHERE id_punto_venta ={$idPvVendedor}   
                    AND TipoComprobante = 'REM'";
    $resultado = mysqli_query($connV,$sqlTalon) or die('No puedo recuperar el talonario' . mysqli_error($connV) );
    if(!$resultado){
        $errores++;
    }
    
    $objTalonario = mysqli_fetch_object($resultado);
//    echo "<pre>".print_r($objTalonario)."</pre>";
    $nroPv = $objTalonario->PV;
    $nroForm = $objTalonario->Nro;

    
    $numeroFormRem = str_pad($nroPv, 4, '0',STR_PAD_LEFT). "-" . str_pad($nroForm, 8, '0',STR_PAD_LEFT);
    $nroCompBusqForm = $nroForm;
    $fechaComp = date('Y/m/d');
    
    // actualizo el talonario
    $sqlTalonUp = "UPDATE talonarios 
                        SET Nro = ".$objTalonario->Nro."+1 
                        WHERE id_punto_venta = {$idPvVendedor}   
                        AND TipoComprobante = 'REM'"; 
    $resultado = mysqli_query($connV,$sqlTalonUp) or die('No puedo actualizar el talonario' . mysqli_error($connV)."<p>".$sqlTalonUp."</p>");
    if(!$resultado){
        $errores++;
    }
    
    $formArr = $jcart->muestra_pedido();
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
    // cabecera del remito 
    //==========================================================================
    $sqlFormIns = "INSERT INTO comp_ped SET                            
                    Fecha='".$fechaComp."',
                    Tipocomprobante='REM',
                    CodSucursal='".$idSucVendedor."',
                    idUsuario='".$codUsuario."',
                    NroComprobante='".$numeroFormRem."',
                    NroCompBusq= '".$nroCompBusqForm."',
                    id_pv= '".$idPvVendedor."',
                    Detalle= '". $_POST['jcart-detalle'] ."',
                    ImporteVenta='".$formArr['subtotal']."',
                    ImporteVentaL='".num2letras($formArr['subtotal'])."',
                    Iva1='".$formArr['subtotalIva21']."',
                    Iva2= '".$formArr['subtotalIva105']."',
                    Alicuota1='21',
                    Alicuota2='10.5',
                    Exento= '".$formArr['subtotalExento']."',
                    anulado='No',
                    Subtotal1='".$formArr['subtotalNetoIva21']."',
                    Subtotal2='".$formArr['subtotalNetoIva105']."',
                    SubtotalGral='".$formArr['subtotalNeto']."',
                    PorDesc1='0.00',
                    PorDesc2='0.00',
                    ImpDesc1='0.00',
                    ImpDesc2='0.00',
                    SubTotalDesc1='".$formArr['subtotalNeto']."',
                    SubTotalDesc2='".$formArr['subtotalNeto']."',
                    SubtotalDesc='".$formArr['subtotalNeto']."',
                    Codigo='".$clienteObj->Codigo."',
                    CondVenta='".$clienteObj->condVenta ."',
                    id_condventa='".$clienteObj->id_cv."',
                    CodigoMovimiento='".$codMovRemito."',
                    Estado= '".$estadoRem."',
                    Vencimiento='".$vencimiento."',
                    CodViajante='".$clienteObj->codViajante."',
                    TipoPedido='Web',
                    impuesto_interno_total='".$formArr['subtotalExento']."',
                    autorizacion_sistema='".$autorizaPedido."',
                    formaentrega='". $_POST['formaEntrega'] ."',
                    FechaEntrega='".$fechaComp."',
                    fecha_control='". date('d/m/Y H:i') ."';";
    $resultado = mysqli_query($connV,$sqlFormIns) or die('No puedo insertar el remito'.  mysqli_error($connV).'<pre>'.$sqlFormIns.'</pre>');
    if(!$resultado){
        $errores++;
    }
//    echo "<p>$sqlFormIns</p>";
    
    $controlFactCompleta=0;
    foreach($artRem as $articulo){
        $idArt = str_replace('p','', $articulo['id']);  
        $promocion      ='No';
        $promocion_por  =0;
        $descuento_por  =0;
        $promocion_tipo ='';
        $promocion_cant =0;
        
        //pregunto si tiene promociones
        if($articulo['promo']=='si'){
           $promocion       = 'Si';
           if($articulo['descTotal']!=0){
                $promocion_por   = $articulo['promoPorc'];
                $descuento_por   = $promocion_por; 
           }
            if($articulo['promoCant']>0){
               
               $promocion_cant  =$articulo['promoCant'];
           }
           $promocion_tipo  =$articulo['promoTipo'];
        }else{
            $descuento_por   = $articulo['descPor'];
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
                      AND id_deposito=".$articulo['deposito'];
        $resultado = mysqli_query($connV,$sqlStockDep) or die("No puedo recuperar el stock_deposito" . mysqli_error($connV));
        if(!$resultado){
            $errores++;
        }
        $stockDeposito = mysqli_fetch_object($resultado);
        
        $saldoArt = $stockDeposito->saldo ;
        $saldoArt -= $articulo['qty'];
        
        $sqlStockDepUp= "UPDATE stock_deposito 
                      SET saldo = " . $saldoArt . " 
                      WHERE id_articulo=" . $idArt . "
                      AND id_deposito=".$articulo['deposito'];
        
        $resultado = mysqli_query($connV,$sqlStockDepUp) or die('No puedo actualizar el stock_deposito' .mysqli_error($connV));
        if(!$resultado){
            $errores++;
        }
        
        
        /*
         * LOTE
         */
        if($articulo['lote']=='Si'){
            $idLote = $articulo['idLote'];
            // control de lote descargar el stock del lote
            $sqlLote ="SELECT * FROM Lote 
                        INNER JOIN lote_stock ON (lote.id_lote = lote_stock.id_lote) 
                        WHERE lote.id_lote = ".$articulo['idLote']." 
                        AND lote_stock.id_deposito = " .$articulo['deposito']. " 
                        AND lote.anulado = 'No'";
            $resultado = mysqli_query($connV,$sqlLote) or die("Error al localizar el lote".mysqli_error($connV)."<p>".$sqlLote."</p>");
            if(!$resultado){
                $errores++;
            }
            $lotes = mysqli_fetch_object($resultado);
            //control si sobre pasa el stock del lote por deposito
            if($lotes->stock_lote < $articulo['qty']){
                // el stock es inferior.
                
            }else{
                $stockLoteTotal = $lotes->stock_total_lote - $articulo['qty'];
                $stockLoteDeposito = $lotes->stock_lote - $articulo['qty'];
                // el stock esta bien actualizo el lote.
                $sqlUpLoteT = "UPDATE Lote SET stock_total_lote=".$stockLoteTotal."
                                WHERE id_lote=".$lotes->id_lote;
                $resultado = mysqli_query($connV,$sqlUpLoteT) or die('No puedo modificar el stock en Lote'.mysqli_error($connV));
                if(!$resultado){
                    $errores++;
                }
                
                $sqlUpLoteD = "UPDATE lote_stock SET stock_lote=" . $stockLoteTotal ." 
                               WHERE id_lote=".$lotes->id_lote." 
                               AND id_deposito=".$articulo['deposito'];
                $resultado = mysqli_query($connV,$sqlUpLoteD) or die("No puedo modificar el lote_stock".mysqli_error($connV));
                if(!$resultado){
                    $errores++;
                }
            }
            
        }else{
            $idLote = "NULL";
            $stockLoteDeposito=0;
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
        if(!$resultado){
            $errores++;
        }
        $artObj = mysqli_fetch_object($resultado); 
        
        /*
             * Embalaje
             */
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
                    IDArt= '".$idArt."',
                    Fecha='". date('Y/m/d') ."',
                    CodigoArticulo='". $artObj->CodigoArticuloT."',
                    CodigoMovimiento='". $codMovRemito ."',
                    Descripcion='". $artObj->NombreArticulo."',
                    Salida='". $articulo['qty']."',
                    saldo='". $saldoArt . "',
                    ImpDesc='". ($articulo['qty'] * $articulo['descTotal']) ."',
                    PorDesc='". $descuento_por ."',
                    PrecioCostoxU='". $artObj->PrecioCosto ."',
                    PrecioVentaxU='". $articulo['neto'] ."',
                    PrecioBrutoxU='". $articulo['priceN']."',
                    PrecioIVAxU='". $articulo['impIva']."',
                    PrecioNetoxU='". $articulo['netoN'] ."',
                    PrecioCostoxR= '". ($artObj->PrecioCosto * $articulo['qty'])."',
                    PrecioVentaxR='". ($articulo['neto'] * $articulo['qty']) ."',
                    PrecioBrutoxR='". $articulo['subtotal'] ."',
                    PrecioNetoxR='". $articulo['subtotalNeto']."',
                    PrecioIVAxR='". $articulo['subtotalIva'] ."',
                    Alicuota='". $articulo['iva']."',
                    AlicuotaIB='". $artObj->IdAlicuotaIb."',
                    Cantidad='". $articulo['qty']."',
                    CodigoCP='". $clienteObj->Codigo."',
                    Tipo='Cliente',
                    Comprobante='REM',
                    TipoComp='Remito Salida',
                    NroComprobante='". $numeroFormRem."',
                    NroRemito='". $numeroFormRem."',    
                    Anulado='No',
                    TipoIVA='". $articulo['tipoIva']."',
                    CodDeposito='".$articulo['deposito']."',
                    IdUsuario= '".$codUsuario."',
                    CodSucursal='". $idSucVendedor."',                                        
                    CodViajante='". $codViajante ."',
                    CodLaboratorio='". $artObj->CodLaboratorio ."',
                    id_lote=".$idLote.",
                    stock_lote_deposito=".$stockLoteDeposito.",
                    tipo_art='".$artObj->tipo_art."',
                    imp_alicuota_iva='". $articulo['alicuota']."',
                    imp_alicuota_iibb='". $artObj->alicuotaIb."',
                    id_manual='". $artObj->id_manual."',
                    lista_precio='".$codListaPrecio."',
                    promocion='".$promocion."',
                    promocion_por='".$promocion_por."',
                    promocion_tipo='".$promocion_tipo."',
                    promocion_cant='".$promocion_cant."'
                    " . $campEmbV . $campEmbC.$stFt.";";
        
        
       $resultado = mysqli_query($connV,$sqlStock) or die('No puedo insertar el articulo' . mysqli_error($connV)."<br><pre>".$sqlStock."</pre>");
        if(!$resultado){
            $errores++;
        }
       
    }
    
    
    /*
     * ACTUALIZO ESTADO DE FACTURA QUE NO ENTREGA STOCK
     */
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
    
    
    if($errores == 0){
        $sqlTotal= "COMMIT;";
        $resultado = mysqli_query($connV,$sqlTotal);
        //echo "todo bien";
    }else{
        $sqlTotal = "ROLLBACK;";
        $resultado = mysqli_query($connV,$sqlTotal);
        //echo "todo mal";
    }
}// fin remito  

/*
* PEDIDO X Talonario.
 * =============================================================================
 */

//if(!empty($artPed)){
//    $errores = 0;
//    $sqlTotal = "SET AUTOCOMMIT =0;";
//    $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo iniciar autocommit '.mysqli_error($connV));
//    $sqlTotal = "BEGIN;";
//    $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo hacer Begin '.mysqli_error($connV));
//    
//    // recupero el codigo de movimiento
//    $sqlMovi = "SELECT CodigoMovimiento + 1 as CodigoMovNew FROM codmov WHERE codigo = 1";
//    $resultado = mysqli_query($connV,$sqlMovi) or die('No puedo recuperar el codigo de movimiento'.mysqli_error($connV));
//    if(!$resultado){
//        $errores++;
//    }
//    // recupero el nuevo codigo de movimiento
//    $codMov = mysqli_fetch_object($resultado);
//    // actualizo el codigo de movimiento en la tabla codigo de movimiento.
//    $sqlMoviUp  = "UPDATE codmov 
//                    SET CodigoMovimiento=" . $codMov->CodigoMovNew . " 
//                    WHERE codmov.codigo=1;";
//    $resultado = mysqli_query($connV,$sqlMoviUp) or die('No puedo modificar el codigo de movimiento'.mysqli_error($connV));
//    if(!$resultado){
//        $errores++;
//    }
//    
//    // obtengo el numero de comprobante del pedido
//    
//    $sqlTalon = "SELECT * 
//                    FROM talonarios 
//                    WHERE id_punto_venta = 1 
//                    AND TipoComprobante = 'PED'";
//    $resultado = mysqli_query($connV,$sqlTalon) or die('No puedo recuperar el talonario' . mysqli_error($connV) );
//    if(!$resultado){
//        $errores++;
//    }
//    
//    $objTalonario = mysqli_fetch_object($resultado);
////    echo "<pre>".print_r($objTalonario)."</pre>";
//    $numeroPedido = str_pad($objTalonario->PV, 4, '0',STR_PAD_LEFT). "-" . str_pad($objTalonario->Nro, 8, '0',STR_PAD_LEFT);
//    $nroCompBusqPedido = $objTalonario->Nro;
//    
//    // actualizo el talonario
//    $sqlTalonUp = "UPDATE talonarios 
//                        SET Nro = ".$objTalonario->Nro."+1 
//                        WHERE id_punto_venta = 1 
//                        AND TipoComprobante = 'PED'"; 
//    $resultado = mysqli_query($connV,$sqlTalonUp) or die('No puedo actualizar el talonario' . mysqli_error($connV)."<p>".$sqlTalonUp."</p>");
//    if(!$resultado){
//        $errores++;
//    }
//    $pedidoArr = $jcart->muestra_pedido();
//    $vencimiento = date('Y/m/d', mktime(0,0,0,date('m')+1,date('d'),date('Y')));
//    
////    estados del pedido
////    -> si es pedido del vendedor entra autorizado salvo que no pueda por los dias
////    -> si es pedido del cliente entra No autorizado 
////    Autorizado
////    No Autorizado
////    
//    $autorizaPedido = '';
//    if(isset($objVendedor)){
////        existe el vendedor a comprobar si el cliente esta o no autorizado.
////        $arrCliente = $_SESSION['cliente'][1];
//        if($arrCliente['exceso']==1){
//            $autorizaPedido = 'No Autorizado';
//        }else{
//            $autorizaPedido = 'Autorizado';
//        }
//    }else{
//        $autorizaPedido = 'No Autorizado';
//    }
//    // alta del pedido
//    //echo "<pre>".print_r($pedidoArr)."</pre>";
//    $sqlPedidoIns = "INSERT INTO 
//                            comp_ped
//                    (Fecha,
//                    Tipocomprobante,
//                    CodSucursal,
//                    idUsuario,
//                    NroComprobante,
//                    NroCompBusq,
//                    id_pv,
//                    Detalle,
//                    ImporteVenta,
//                    ImporteVentaL,
//                    Iva1,
//                    Iva2,
//                    Alicuota1,
//                    Alicuota2,
//                    Exento,
//                    anulado,
//                    Subtotal1,
//                    Subtotal2,
//                    SubtotalGral,
//                    PorDesc1,
//                    PorDesc2,
//                    ImpDesc1,
//                    ImpDesc2,
//                    SubTotalDesc1,
//                    SubTotalDesc2,
//                    SubtotalDesc,
//                    Codigo,
//                    CondVenta,
//                    id_condventa,
//                    CodigoMovimiento,
//                    Estado,
//                    Vencimiento,
//                    CodViajante,
//                    TipoPedido,
//                    impuesto_interno_total,
//                    autorizacion_sistema,
//                    formaentrega,
//                    fecha_control
//                    )VALUES(
//                    '".date('Y/m/d')."',
//                    'PED',
//                    '".$nroSuc."',
//                    '".$_SESSION['idusuario']."',
//                    '".$numeroPedido."',
//                    '".$nroCompBusqPedido."',
//                    1,
//                    '". $_POST['jcart-detalle'] ."\n pedido automático generado por remito por sistema ". $numeroFormRem."',
//                    '".$pedidoArr['subtotal']."',
//                    '".num2letras(number_format($pedidoArr['subtotal'],2))."',
//                    '".$pedidoArr['subtotalIva21']."',
//                    '".$pedidoArr['subtotalIva105']."',
//                    '21',
//                    '10.5',
//                    '".$pedidoArr['subtotalExento']."',
//                    'No',
//                    '".$pedidoArr['subtotalNetoIva21']."',
//                    '".$pedidoArr['subtotalNetoIva105']."',
//                    '".$pedidoArr['subtotalNeto']."',
//                    '0.00',
//                    '0.00',
//                    '0.00',
//                    '0.00',
//                    '".$pedidoArr['subtotalNeto']."',
//                    '".$pedidoArr['subtotalNeto']."',
//                    '".$pedidoArr['subtotalNeto']."',
//                    '".$clienteObj->Codigo."',
//                    '".$clienteObj->condVenta ."',    
//                    '".$clienteObj->id_cv."',
//                    '".$codMov->CodigoMovNew."',
//                    'Pendiente',
//                    '".$vencimiento."',
//                    '".$clienteObj->codViajante."',
//                    'Web',
//                    '".$pedidoArr['subtotalExento']."',
//                    '".$autorizaPedido."',
//                    '". $_POST['formaEntrega'] ."',
//                    '". date('d/m/Y H:i') ."'    
//                    );";
//    $resultado = mysqli_query($connV,$sqlPedidoIns) or die('No puedo insertar el pedido'.  mysqli_error($connV));
//    if(!$resultado){
//        $errores++;
//    }
////    echo "<p>$sqlPedidoIns</p>";
//    
//    
//    foreach($artPed as $articulo){
//        //inserto actualizar la tabla stock_deposito.
////        echo "<pre>". print_r($articulo) ."</pre>";
//        
////        seteando las promociones.
//        
//    
//        $promocion      ='No';
//        $promocion_por  =0;
//        $descuento_por  =0;
//        $promocion_tipo ='';
//        $promocion_cant =0;
//        
//        //pregunto si tiene promociones
//        if($articulo['promo']=='si'){
//           $promocion       = 'Si';
//           if($articulo['descTotal']!=0){
//                $promocion_por   = $articulo['promoPorc'];
//                $descuento_por   = $promocion_por; 
//           }
//            if($articulo['promoCant']>0){
//               $promocion_tipo  ='Cantidad';
//               $promocion_cant  =$articulo['promoCant'];
//           }
//           
//        }else{
//            $descuento_por   = $articulo['descPor'];
//        }
//        
//        $sqlStockDep = "SELECT saldo_pedido_cliente 
//                        FROM stock_deposito 
//                        WHERE id_articulo=" . $idArt . "
//                      AND id_deposito=1";
//        $resultado = mysqli_query($connV,$sqlStockDep) or die("No puedo recuperar el stock_deposito" . mysqli_error($connV));
//        if(!$resultado){
//            $errores++;
//        }
//        $stockDeposito = mysqli_fetch_object($resultado);
//        
//        $saldoArt = $stockDeposito->saldo_pedido_cliente ;
//        $saldoArt += $articulo['qty'];
//        
//        $sqlStockDepUp= "UPDATE stock_deposito 
//                      SET saldo_pedido_cliente = " . $saldoArt . " 
//                      WHERE id_articulo=" . $idArt . "
//                      AND id_deposito=1";
//        
//        $resultado = mysqli_query($connV,$sqlStockDepUp) or die('No puedo actualizar el stock_deposito' .mysqli_error($connV));
//        if(!resultado){
//            $errores++;
//        }
//        $sqlArtDb = "SELECT 
//                            articulo.impuesto_interno,
//                            articulo.CodigoArticulo,
//                            articulo.CodigoArticuloT,
//                            articulo.PrecioCosto,
//                            articulo.NombreArticulo,
//                            articulo.AlicuotaIB as IdAlicuotaIb,
//                            activ_iibb.alicuota AS alicuotaIb,
//                            articulo.CodLaboratorio,
//                            articulo.id_manual,
//                            articulo.tipo_art
//                            FROM
//                                articulo
//                            LEFT JOIN activ_iibb ON activ_iibb.ID = articulo.AlicuotaIb
//                            WHERE articulo.IDArt = " . $idArt;
//        $resultado = mysqli_query($connV,$sqlArtDb) or die('No puedo consultar el articulo en la base de datos' . mysqli_error($connV));
//        if(!$resultado){
//            $errores++;
//        }
//        $artObj = mysqli_fetch_object($resultado); 
//        
//        
//        $sqlStock = "INSERT INTO stockp(Saldo,
//                                        impuesto_interno,
//                                        impuesto_interno_subtotal,
//                                        Fecha,
//                                        CodigoArticulo,
//                                        Descripcion,
//                                        PrecioVentaxU,
//                                        PrecioCostoxU,
//                                        PrecioIVAxU,
//                                        PrecioBrutoxU,
//                                        PrecioNetoxU,
//                                        PrecioVentaxR,
//                                        PrecioCostoxR,
//                                        PrecioIVAxR,
//                                        PrecioBrutoxR,
//                                        PrecioNetoxR,
//                                        Alicuota,
//                                        AlicuotaIB,
//                                        imp_alicuota_iva,
//                                        imp_alicuota_iibb,
//                                        Salida,
//                                        Cantidad,
//                                        ImpDesc,
//                                        PorDesc,
//                                        CodViajante,
//                                        CodLaboratorio,
//                                        CodigoMovimiento,
//                                        CodDeposito,
//                                        IDArt,
//                                        id_manual,
//                                        CodSucursal,
//                                        idusuario,
//                                        TipoIVA,
//                                        CodigoCP,
//                                        Tipo,
//                                        TipoComp,
//                                        anulado,
//                                        Comprobante,
//                                        NroComprobante,
//                                        Lista_Precio,
//                                        promocion,
//                                        promocion_por,
//                                        promocion_tipo,
//                                        promocion_cant,
//                                        tipo_art
//                            )VALUES(
//                                        '". $saldoArt . "',
//                                        '". $artObj->impuesto_interno . "',
//                                        '". $articulo['impInterno'] ."',
//                                        '". date('Y/m/d') ."',
//                                        '". $artObj->CodigoArticuloT."',
//                                        '". $artObj->NombreArticulo."',
//                                        '". $articulo['neto'] ."',
//                                        '". $artObj->PrecioCosto ."',
//                                        '". $articulo['impIva']."',
//                                        '". $articulo['priceN']."',
//                                        '". $articulo['netoN'] ."',
//                                        '". ($articulo['neto'] * $articulo['qty']) ."',
//                                        '". ($artObj->PrecioCosto * $articulo['qty'])."',
//                                        '". $articulo['subtotalIva'] ."',
//                                        '". $articulo['subtotal'] ."',
//                                        '". $articulo['subtotalNeto']."',
//                                        '". $articulo['iva']."',
//                                        '". $artObj->IdAlicuotaIb."',
//                                        '". $articulo['alicuota']."',
//                                        '". $artObj->alicuotaIb."',
//                                        '". $articulo['qty']."',
//                                        '". $articulo['qty'] ."',
//                                        '". ($articulo['qty'] * $articulo['descTotal']) ."',
//                                        '". $descuento_por ."',
//                                        '". $clienteObj->codViajante ."',
//                                        '". $artObj->CodLaboratorio ."',
//                                        '". $codMov->CodigoMovNew ."',
//                                        1,
//                                        '". $idArt ."',
//                                        '". $artObj->id_manual."',
//                                        '". $clienteObj->id_sucursal."',
//                                        '1',
//                                        '". $articulo['tipoIva']."',
//                                        '". $clienteObj->Codigo."',
//                                        'Cliente',
//                                        'Pedido',
//                                        'No',
//                                        'PED',
//                                        '". $numeroPedido."',
//                                        '". $clienteObj->listaPrecio."',
//                                        '".$promocion."',
//                                        '".$promocion_por."',
//                                        '".$promocion_tipo."',
//                                        '".$promocion_cant."',
//                                        '".$artObj->tipo_art."'    
//                                            )";
//        $resultado = mysqli_query($connV,$sqlStock) or die('No puedo insertar el articulo' . mysqli_error($connV));
//        if(!$resultado){
//            $errores++;
//        }
////        echo $var = htmlentities($articulo['name'], ENT_NOQUOTES, 'UTF-8');
////        echo "<p>$sqlStock</p>";
/////       echo "<p><pre>".print_r($articulo)."</pre></p>";
//    }
//    
//    if($errores == 0){
//        $sqlTotal= "COMMIT;";
//        $resultado = mysqli_query($connV,$sqlTotal);
//        //echo "todo bien";
//    }else{
//        $sqlTotal = "ROLLBACK;";
//        $resultado = mysqli_query($connV,$sqlTotal);
//        //echo "todo mal";
//    }
//    
//    
//}//fin pedido


    
//    si termina tengo que vaciar el carrito y despues tengo que volver a la lista de pedidos
    $jcart->empty_cart();
    
   /*
    * ENVIO CORREO
    * ==========================================================================
    */ 
    
    $_SESSION["tipoComprobante"] = "REM";
    $_SESSION["nroComprobante"]=$numeroFormRem;
    $_SESSION["codigoMovimiento"]=$codMovRemito;
    
    header('Location: fin-comprobante.php');
    
    
    
    // pasar a enviar el remito por email.
//    if(isset($objVendedor)){
//        //echo "aca estoy";
//        $urlVuelta .=".php?cartel=0";
//        if(isset($numeroFormRem)){
//            $urlVuelta .="&rem=".$numeroFormRem;
//        }
//        if(isset($numeroPedido)){
//            $urlVuelta .="&ped=".$numeroPedido;
//        }
//        header('Location: '.$urlVuelta);
//    }else{
////        echo "aca no estoy";
//        header('Location: '.$urlVuelta);
//    }  
    //revisar si el codigo de movimiento no es autocommit y ver como me lo traigo
//    y lo cambio antes de poder usarlo en este formulario.
//    $sqlTotal  .="SELECT "
    
//}else{
//    header('Location: '.$urlVuelta.'.php');
////echo "<p>VACIO</p>";
//    
//}

//echo "<pre>".print_r($articulos)."</pre>";


//$objCliente = $_SESSION['cliente'];
//echo "</body>";
//echo"</html>";
