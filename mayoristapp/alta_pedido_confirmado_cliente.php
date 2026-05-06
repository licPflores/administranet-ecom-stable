<?php
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// jCart v1.3
// http://conceptlogic.com/jcart/

// This file is called when any button on the checkout page (PayPal checkout, update, or empty) is clicked

// Include jcart before session start
require_once 'jcart/numero_a_letra.php';
require_once 'jcart/jcart.php';
require_once 'sesion.inc.php';

require_once '_scripts/php/funciones.php';



$config = $jcart->config;
/*control de sesion*/
//echo "<pre>";
//print_r($_SESSION);
//echo "</pre>";
$controlH = $_SESSION["utiliza_control_horario"];
$soyMovil="si";
if($_SESSION["caminoDisp"]==""){
    $soyMovil="no";
}

//echo "<pre>";
//var_dump(fn_control_horario($controlH));
//echo "</pre>";
//if(fn_control_horario($controlH)==0){
//    // no hay permiso.
//    fn_cerrar_sesion();
//    header('Location: index.php?cartel=3');
//}

// una vez que tenemos el carrito listo hay que validar que haya algo para eso 
// preguntamos si no esta vacio el objeto con las entradas de stock del OBJ jcart
// recuperamos la numeracion del pedido
// guardamos el pedidos
// guardamos las entradas en el stock para que se ponga todo ok
//
// validamos
$queTipoUsuario=$_SESSION["tipousuario"];
$urlVuelta = "alta_pedido";
$tipoPedido="Web";
$idUsuarioPed=1;
$articulos = $jcart->get_contents();

if(is_object($_SESSION['cliente'])){
    $clienteObj = $_SESSION['cliente'];
}else{
    $clienteObj = $_SESSION['cliente'][0];
}
$utilizaEmbalaje=$_SESSION['utilizaEmbalaje'] ;

    // soy el cliente yo tengo que tener el viajante 
    // por defecto.
    $usuario = $clienteObj;
    if(isset($_SESSION["usa_viajante_cliente"])&&$_SESSION["usa_viajante_cliente"]=='Si'){
        // uso el viajante asignado al cliente
		
        
		$codViajante = $clienteObj->CodViajanteCli;
    }else{
        //uso el viajante por defecto en sistema
        $codViajante = $clienteObj->CodViajante;
    }
    $tipoPedido="Web cliente";


if(isset($_SESSION["idusuario"])&&$_SESSION["idusuario"]!==""){
    $idUsuarioPed=$_SESSION['idusuario'];
}

if(isset($_SESSION["id_sucursal"])){
    $idSucVendedor = $_SESSION["id_sucursal"];
}else{
    
    $idSucVendedor = $clienteObj->id_sucursal;
}
/*
 * PUNTO DE VENTA
 * ==================================================
 */ 
/* seleccion multiple de punto de venta.*/
/* Si el permiso de session es que selecciona punto venta == Si, Traigo el POST del punto de venta.
 * para los clientes x defecto es no y mi punto de venta ese el del usuario.
 */
 //$nroPv =   explode("|",$_POST['jcart-suc']);
$idPuntoVenta = $usuario->id_punto_venta;
// deposito
$idDeposito=$_SESSION['deposito'];

/* GEOLOCALIZACION
 * ===========================
 */
$geo_long="0";
$geo_lat="0";
if(isset($_SESSION["latitud"])){
    $geo_long=$_SESSION["longitud"];
    $geo_lat=$_SESSION["latitud"];
}

//asigno el valor del pedido antes
$pedidoArr = $jcart->muestra_pedido();

//controlo que el array de pedido no venga con campos en blanco.

//$articulos=array();
/*
 * PEDIDO INICIO 
 * =============================================================================
 *  */

/**
 * Controlar que se hace despues del POST
 * =============================================================================
 */

if(isset($_POST["confOperacion"])&&$_POST["confOperacion"]=="ok"){

    if(!empty($articulos)&& $pedidoArr["subtotal"]>0){

        // guardamos el pedido primero 
        // inicio de transacciones
        //mysqli_begin_transaction();
        $errores = 0;

        $sqlTotal = "SET AUTOCOMMIT =0;";
        $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo iniciar autocommit '.mysqli_error($connV));
        $sqlTotal = "BEGIN;";
        $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo hacer Begin '.mysqli_error($connV));

        $buscoCod=0;
        // recupero el codigo de movimiento
        // es un bucle que deberia evitar o ejecutarse hasta que sea el mismo codigomov
        // en caso de que se cambie de cod mov antes no se pueda pisar.
        while($buscoCod==0){
            $sqlMovi = "SELECT CodigoMovimiento + 1 as CodigoMovNew,CodigoMovimiento FROM codmov WHERE codigo = 1";
            $resultado = mysqli_query($connV,$sqlMovi) or die('No puedo recuperar el codigo de movimiento'.mysqli_error($connV));
            if(!$resultado){
                $errores++;
            }
            // recupero el nuevo codigo de movimiento
            $codMovResult = mysqli_fetch_assoc($resultado);
            $codMov = $codMovResult["CodigoMovNew"];
            $codMovViejo=$codMovResult["CodigoMovimiento"];
            // actualizo el codigo de movimiento en la tabla codigo de movimiento.
            $sqlMoviUp  = "UPDATE codmov 
                            SET CodigoMovimiento=" . $codMov. " 
                            WHERE codmov.codigo=1 AND codmov.CodigoMovimiento=".$codMovViejo.";";
            $resultado = mysqli_query($connV,$sqlMoviUp) or die('No puedo modificar el codigo de movimiento'.mysql_error($connV));
            if(!$resultado){
                $errores++;
            }
            $buscoCod= mysqli_affected_rows($connV);
            // cierro la transaccion.
            if($errores == 0 && $buscoCod!=0){
                $sqlTotal= "COMMIT;";
                $resultado = mysqli_query($connV,$sqlTotal);
                //echo "todo bien";
            }else{
                $sqlTotal = "ROLLBACK;";
                $resultado = mysqli_query($connV,$sqlTotal);
                //echo "todo mal";
            }
        }

        // reinicio la transaccion
        $errores = 0;
        $sqlTotal = "SET AUTOCOMMIT =0;";
        $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo iniciar autocommit '.mysqli_error($connV));
        $sqlTotal = "BEGIN;";
        $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo hacer Begin '.mysqli_error($connV)); 
        // obtengo el numero de comprobante del pedido

        $sqlTalon = "SELECT * 
                        FROM talonarios 
                        WHERE id_punto_venta = '".$idPuntoVenta."' 
                        AND TipoComprobante = 'PED'";
        $resultado = mysqli_query($connV,$sqlTalon) or die('No puedo recuperar el talonario' . mysqli_error($connV) );
        if(!$resultado){
            $errores++;
        }

        $objTalonario = mysqli_fetch_assoc($resultado);

        if(!empty($objTalonario)){
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
        }else{
            $errores++;
        }




        $vencimiento = date('Y/m/d', mktime(0,0,0,date('m')+1,date('d'),date('Y')));

        /*Control de la fecha de entrega*/
        /*==============================*/


        //$fechaE = date_create(date('Y-m-d'));
        $arrNoLaborable=$_SESSION["arr_dias_no_laborables"];
        $intervalo=$_SESSION["cant_dias_entrega"];
    //    
    //
         $fechaE= date_create(date('Y/m/d',mktime(0,0,0,date('m'),date('d')+$intervalo,date('Y')))); 
        //date_add($fechaE, date_interval_create_from_date_string($intervalo.' days'));
       $diaEntrega= date_format($fechaE, 'N');
    //   
       if(in_array($diaEntrega,$arrNoLaborable)){
           $intervalo++;
           $fechaE= date_create(date('Y/m/d',mktime(0,0,0,date('m'),date('d')+$intervalo,date('Y')))); 

       }
       $fechaEntrega = date_format($fechaE, 'Y/m/d'); 
       $fechaEntregaH=  date_format($fechaE, 'Y/m/d' ); 

    //    estados del pedido
    //    -> si es pedido del vendedor entra autorizado salvo que no pueda por los dias
    //    -> si es pedido del cliente entra No autorizado 
    //    Autorizado
    //    No Autorizado
    //    
        $autorizaPedido = '';
        if(isset($objVendedor)){
    //        existe el vendedor a comprobar si el cliente esta o no autorizado.

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
      /* Domicilios del cliente */
//        echo "<pre>";
//        print_r($_POST);
//        echo "</pre>";

        if($_POST["domicilio_entrega"]==""){
            $idDomEntrega = 'NULL';
        }else{
            $domEntrega = explode("|", $_POST["domicilio_entrega"]);
            $idDomEntrega = "'".$domEntrega[0]."'";
        }
        /*Logistica*/
        if($_SESSION["activ_logistica"]=="Si"){
            // la ruta viene vacia o no la tengo.
            if(!isset($_POST["hoja_ruta"])&&$_POST["hoja_ruta"]==""){
                $idRuta = "NULL";
            }else{
                $idRuta ="'".$_POST["hoja_ruta"]."'";
            }
        }else{
            $idRuta = "NULL";
        }
        
        $detalle="";
        // Orden de compra
        if(isset($_POST["jcart-orden-compra"]) && $_POST["jcart-orden-compra"]!==""){
            $detalle .="OC: ".$_POST["jcart-orden-compra"]."\r\n ";
        }
        
        if($_POST['jcart-detalle']!==""){
            $detalle .=$_POST['jcart-detalle'];
        }
        
        
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
                                                                . "'".$fechaEntrega."',"
                                                                . "'".$_SESSION['deposito']."',"
                                                                . "'".$_POST['formaEntrega']."',"
                                                                . "'Web',"
                                                                . "'PED',"
                                                                . " '".$clienteObj->Codigo."',"
                                                                . "'".$codMov."',"
                                                                . $idDomEntrega.","
                                                                . $idRuta.")";    
        $resultado = mysqli_query($connV,$sqlDatoCliente) or die('No puedo insertar dato adicional del cliente'.  mysqli_error($connV).$sqlDatoCliente);
        if(!$resultado){
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
        if(!empty($percepciones)){
            foreach($percepciones as $kp => $per){
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
                                                        '".$per["id"]."',
                                                        '".$per["alic"]."',
                                                        '".$per["monto"]."',
                                                        '".$codMov."',
                                                        '".$clienteObj->Codigo."',
                                                        'PED'    
                                                    )";
                $resultado = mysqli_query($connV,$sqlPerc) or die("No puedo insertar la percepcion" . mysqli_error($connV));
                if(!$resultado){
                    $errores++;
                }
            }
        }
    //    echo "Percepciones OK<br>";

    /*    
     * alta del PEDIDO
     */

        //echo "<pre>".print_r($pedidoArr)."</pre>";
        $sqlPedidoIns = "INSERT INTO 
                                comp_ped 
                        SET
                        Fecha='".date('Y/m/d')."',
                        Tipocomprobante= 'PED',
                        CodSucursal='".$clienteObj->id_sucursal."',
                        IdUsuario='".$_SESSION['idusuario']."',
                        NroComprobante= '".$numeroPedido."',
                        NroCompBusq='".$nroCompBusqPedido."',
                        id_pv='".$idPuntoVenta."',
                        Detalle= '". $detalle ."',
                        ImporteVenta='".$pedidoArr['subtotal']."',
                        ImporteVentaL='".num2letras(number_format($pedidoArr['subtotal'],2,",",""))."',
                        Iva1='".$pedidoArr['subtotalIva21']."',
                        Iva2='".$pedidoArr['subtotalIva105']."',
                        Alicuota1='21',
                        Alicuota2='10.5',
                        Exento= '".$pedidoArr['subtotalExento']."',
                        anulado='No',
                        Subtotal1= '".$pedidoArr['subtotalNetoIva21']."',
                        Subtotal2='".$pedidoArr['subtotalNetoIva105']."',
                        SubtotalGral='".($pedidoArr['subtotalNetoIva21']+$pedidoArr['subtotalNetoIva105'])."',
                        PorDesc1='".$pedidoArr['porDescPie']."',
                        PorDesc2='".$pedidoArr['porDescPie']."',
                        ImpDesc1='".$pedidoArr['importeDesc21']."',
                        ImpDesc2='".$pedidoArr['importeDesc105']."',
                        SubTotalDesc1= '".$pedidoArr['subtotalDesc21']."',
                        SubTotalDesc2='".$pedidoArr['subtotalDesc105']."',
                        SubtotalDesc= '".$pedidoArr['subtotalDesc']."',
                        Codigo='".$clienteObj->Codigo."',
                        CondVenta='".$clienteObj->condVenta ."',
                        id_condventa='".$clienteObj->id_cv."',
                        CodigoMovimiento='".$codMov."',
                        Estado='Pendiente',
                        Vencimiento='".$vencimiento."',
                        CodViajante= '".$codViajante."',
                        TipoPedido='".$tipoPedido."',
                        impuesto_interno_total='".$pedidoArr['subtotalExento']."',
                        autorizacion_sistema='".$autorizaPedido."',
                        formaentrega= '". $_POST['formaEntrega'] ."',
                        fecha_control='". date('d/m/Y H:i') ."',
                        id_deposito_despacho= '".$_SESSION['deposito']."',
                        FechaEntrega='". $fechaEntregaH ."',
                        total_percep='".$pedidoArr["percepcionesT"]."',
                        geo_latitud='".$geo_lat."',
                        geo_longitud= '".$geo_long."';";
        $resultado = mysqli_query($connV,$sqlPedidoIns) or die('No puedo insertar el pedido'.  mysqli_error($connV).$sqlPedidoIns);

    //   echo "<pre>";
    //   print_r($sqlPedidoIns);
    //   echo "</pre>";

        if(!$resultado){
            $errores++;
        }
    //echo "Pedido OK<br>";
        // controlar que si no hay articulos.. o al menos dio una vuelta...hago rollback directamente.
        $controlRenglones=0;
        foreach($articulos as $cc => $articulo){
            //inserto actualizar la tabla stock_deposito.
            //echo "<pre>". print_r($articulo) ."</pre>";

    //        seteando las promociones.

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

            $sqlStockDep = "SELECT saldo_pedido_cliente 
                            FROM stock_deposito 
                            WHERE id_articulo=" . $idArt . "
                          AND id_deposito=".$idDeposito;
            $resultado = mysqli_query($connV,$sqlStockDep) or die("No puedo recuperar el stock_deposito" . mysqli_error($connV)."<pre>".$sqlStockDep."</pre>");
            if(!$resultado){
                $errores++;
            }

            $stockDeposito = mysqli_fetch_object($resultado);

            $saldoArt = $stockDeposito->saldo_pedido_cliente ;
            $saldoArt += $articulo['qty'];

            $sqlStockDepUp= "UPDATE stock_deposito 
                          SET saldo_pedido_cliente = " . $saldoArt . " 
                          WHERE id_articulo=" . $idArt . "
                          AND id_deposito=".$idDeposito;

           $resultado = mysqli_query($connV,$sqlStockDepUp) or die('No puedo actualizar el stock_deposito' .mysqli_error($connV));
            if(!$resultado){
                $errores++;
            }
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
            if($utilizaEmbalaje=='Si'){

                $artEmbV = "SELECT articulo.multiplicador_vta, "
                                . "articulo.CodigoProveedor, "
                                . "articulo.id_unimed,"
                                . "articulo.id_presentacionV, "
                                . "unidMed.nombre_unimed, "
                                . "presentacion_abm.nombre_presentacion 
                            FROM articulo 
                                LEFT JOIN unidmed ON (unidMed.id_unimed = articulo.id_unimed) 
                                LEFT JOIN presentacion_abm ON (presentacion_abm.id_presentacion = articulo.id_presentacionV)
                                WHERE articulo.idArt=".$idArt;
                $resultado = mysqli_query($connV,$artEmbV) or die("No puedo recuperar los articulos de embalaje".mysqli_error($connV));
                if(!$resultado){
                    $errores++;
                }
                $artEmV = mysqli_fetch_assoc($resultado);
    //            echo "<pre>";
    //            print_r($artEmV);
    //            echo "</pre>";
                if(!empty($artEmV)){
                    $idProveedor = $artEmV["CodigoProveedor"];
                    $campEmbV = ", multiplicador_vta,"
                            . " id_unimed_vta,"
                            . " id_presentacion_vta,"
                            . " nombre_unimed_vta,"
                            . " nombre_presentacion_vta";

                    $datoEmbV =",'".$artEmV["multiplicador_vta"]."',"
                            . "'".$artEmV["id_unimed"]."',"
                            . "'".$artEmV["id_presentacionV"]."',"
                            . "'".$artEmV["nombre_unimed"]."',"
                            . "'".$artEmV["nombre_presentacion"]."'";
                }

               $artEmC = "SELECT multiplicador_comp,"
                                . "cantidad_uni, "
                                . "unidMed.nombre_unimed, "
                                . "presentacion_abm.nombre_presentacion, "
                                . "articulo_prov.id_presentacionC, "
                                . "articulo_prov.id_unimed "
                        ." FROM articulo_prov "
                        ." LEFT JOIN unidMed ON (unidMed.id_unimed = articulo_prov.id_unimed) "
                        ." LEFT JOIN presentacion_abm ON (presentacion_abm.id_presentacion = articulo_prov.id_presentacionC)"
                        ." WHERE idArt= ". $idArt ." AND CodProveedor = ".$idProveedor;
                $resultado = mysqli_query($connV,$artEmC) or die("No puedo recuperar los datos del proveedor".mysqli_error($connV));
                if(!$resultado){
                    $errores++;
                }
                $artEmC = mysqli_fetch_assoc($resultado);
                if(!empty($artEmC)){
                    $campEmbC =",multiplicador_comp,"
                            . "id_unimed_comp,"
                            . "id_presentacion_comp,"
                            . "nombre_unimed_comp,"
                            . "nombre_presentacion_comp";
                    $datoEmbC =",'".$artEmC["multiplicador_comp"]."',"
                            . "'".$artEmC["id_unimed"]."',"
                            . "'".$artEmC["id_presentacionC"]."',"
                            . "'".$artEmC["nombre_unimed"]."',"
                            . "'".$artEmC["nombre_presentacion"]."'";
                }

    //          echo "Embalaje OK<br>";  
            }
            /*Fin Embalaje*/

            /**
             * STOCK P
             */
            $sqlStock = "INSERT INTO stockp(Saldo,
                                            impuesto_interno,
                                            impuesto_interno_subtotal,
                                            Fecha,
                                            CodigoArticulo,
                                            Descripcion,
                                            PrecioVentaxU,
                                            PrecioCostoxU,
                                            PrecioIVAxU,
                                            PrecioBrutoxU,
                                            PrecioNetoxU,
                                            PrecioVentaxR,
                                            PrecioCostoxR,
                                            PrecioIVAxR,
                                            PrecioBrutoxR,
                                            PrecioNetoxR,
                                            Alicuota,
                                            AlicuotaIB,
                                            imp_alicuota_iva,
                                            imp_alicuota_iibb,
                                            Salida,
                                            Cantidad,
                                            ImpDesc,
                                            PorDesc,
                                            CodViajante,
                                            CodLaboratorio,
                                            CodigoMovimiento,
                                            CodDeposito,
                                            IDArt,
                                            id_manual,
                                            CodSucursal,
                                            idusuario,
                                            TipoIVA,
                                            CodigoCP,
                                            Tipo,
                                            TipoComp,
                                            anulado,
                                            Comprobante,
                                            NroComprobante,
                                            lista_precio,
                                            promocion,
                                            promocion_por,
                                            promocion_tipo,
                                            promocion_cant,
                                            tipo_art,
                                            Orden,
                                            cantidad_entregada,
                                            cantidad_pendiente,
                                            detalle,
                                            unidad_art_peso
                                            ". $campEmbV.$campEmbC."

                                )VALUES(
                                            '". $saldoArt . "',
                                            '". $artObj->impuesto_interno . "',
                                            '". $articulo['impInterno'] ."',
                                            '". date('Y/m/d') ."',
                                            '". $artObj->CodigoArticuloT."',
                                            '". $artObj->NombreArticulo."',
                                            '". $articulo['neto'] ."',
                                            '". $artObj->PrecioCosto ."',
                                            '". $articulo['impIva']."',
                                            '". $articulo['priceN']."',
                                            '". $articulo['netoN'] ."',
                                            '". ($articulo['neto'] * $articulo['qty']) ."',
                                            '". ($artObj->PrecioCosto * $articulo['qty'])."',
                                            '". $articulo['subtotalIva'] ."',
                                            '". $articulo['subtotal'] ."',
                                            '". $articulo['subtotalNeto']."',
                                            '". $articulo['iva']."',
                                            '". $artObj->IdAlicuotaIb."',
                                            '". $articulo['alicuota']."',
                                            '". $artObj->alicuotaIb."',
                                            '". $articulo['qty']."',
                                            '". $articulo['qty'] ."',
                                            '". ($articulo['qty'] * $articulo['descTotal']) ."',
                                            ROUND('". $descuento_por ."',2),
                                            '". $codViajante ."',
                                            '". $artObj->CodLaboratorio ."',
                                            '". $codMov."',
                                            '".$_SESSION['deposito']."',
                                            '". $idArt ."',
                                            '". $artObj->id_manual."',
                                            '". $idSucVendedor."',
                                            '1',
                                            '". $articulo['tipoIva']."',
                                            '". $clienteObj->Codigo."',
                                            'Cliente',
                                            'Pedido',
                                            'No',
                                            'PED',
                                            '". $numeroPedido."',
                                            '". $clienteObj->codListaPrecio."',
                                            '".$promocion."',
                                            '".$promocion_por."',
                                            '".$promocion_tipo."',
                                            '".$promocion_cant."',
                                            '".$artObj->tipo_art."',
                                            ".($cc+1).",    
                                            '". $articulo['qty']."',
                                            '". $articulo['qty']."',
                                            '".$articulo['url']."',
                                            '".$articulo['url']."'    
                                            ". $datoEmbV . $datoEmbC."                                            
                                                )";
    //        echo "<pre>";
    //        print_r($sqlStock);
    //        echo "</pre><br>";
            $resultado = mysqli_query($connV,$sqlStock) or die('No puedo insertar el articulo' . mysqli_error($connV).$sqlStock ."<br><br>");
            if(!$resultado){
                $errores++;
            }
            if($resultado){
                $controlRenglones++;
            }

        }

        if($errores == 0 && $controlRenglones>0){
            $sqlTotal= "COMMIT;";
            $resultado = mysqli_query($connV,$sqlTotal);
            //echo "todo bien";
        }else{
            $sqlTotal = "ROLLBACK;";
            $resultado = mysqli_query($connV,$sqlTotal);
            //echo "todo mal";
        }
    //    si termina tengo que vaciar el carrito y despues tengo que volver a la lista de pedidos
        $jcart->empty_cart();
        unset($_SESSION["jcart"]);
        if(isset($numeroPedido)){
            $urlVuelta .=".php?cartel=0";
            $urlVuelta .="&ped=".$numeroPedido;
            $urlVuelta .="&est=".$autorizaPedido;
        }
        header('Location: '.$urlVuelta);
         


        //revisar si el codigo de movimiento no es autocommit y ver como me lo traigo
    //    y lo cambio antes de poder usarlo en este formulario.
    //    $sqlTotal  .="SELECT "

    }else{
        //verificar si el pedido se hizo vacio y vaciarlo para que lo vuelva a hacer.
        //
        $jcart->empty_cart();
        unset($_SESSION["jcart"]);
        header('Location: alta_pedido.php?cartel=1');
    //echo "<p>VACIO</p>";

    }
} // fin del POST EN DOS PASOS
/*
 * PASO DOS PEDIDO SOLICITAR DATOS DEL DOMICILIO Y MOSTRAR ZONA Y LOGISTICA
 * ========================================================================
 */

    $uTablas        = 1;
    $uModal         = 0;
    $uSlider        = 0;
    $uGui           = 1;
    $iconoDisabled  = 1;
    $usaZoom        = 0;
?>

<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
hacer una unica consulta con los depositos y mostrar los datos en un array para 
hacer la tabla y que las dos tablas o solo las remitidas lo muestren.
-->
 <?php 
$pedidoArr = $jcart->muestra_pedido();
$artPed = $jcart->get_contents();
$limiteDespacho =500;
$validoLimiteDespacho=true;
$valorPedido=$pedidoArr["subtotalNeto"];
$permisoClientesDom = $_SESSION["obliga_domicilio_cliente"];
//        echo "<pre>";
//        print_r($pedidoArr);
//        echo "</pre>";

/* activacion de logistica */
$permisoLogistica = $_SESSION["activ_logistica"];
$ivaIncluido=$_SESSION["ivaIncluido"];

?>
<html>
    <head>
        <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
        <title>Mi Pedido Finalizar | administraNET </title>
        <?php require_once 'cabecera.php';?> 
       
       
        <script>
            $(document).ready(function(){

                
                $("#canceloOp").click(function(){
                    //alert("chau");
                    $(location).attr("href","alta_pedido.php");
                });
                
                // control de la rutas con los domicilios.
               // $('#domicilio_entrega').on("change",selecRuta);
                //$('#domicilio_entrega  option:eq(1)').prop('selected', true);
                
                // hacer control de campos en el submit.
                var horario = $('#jcart-detalle');
//                    if(horario.val()==""){
//                        alert("Debe completar el horario de entrega - detalle");
//                        horario.focus();
//                        return false;
//                    }
            $('#frmAcepto').on("submit",function(){
               
                var domicilio=$('input[name="domicilio_entrega"]:checked').val();
                console.log(domicilio);
                if(domicilio===undefined){
                     event.preventDefault();
                    alert("Debe seleccionar una ubicacion");
                    return false;
                }
                
            });
            });
        </script>
    </head>
    <body>
        
        <div id="wrapper">
            <?php  require_once $barra; ?>   
                <div id="content">
                   <h2 class="alignLeft"><i class="fa fa-shopping-cart fa-lg"></i> Mi Pedido </h2> 
               
                  <div id="divMiPedido"  > 
                <?php if(!empty($artPed)):?>
                    
                    <table  id="carrito" class="dataTable"> 
                    <thead>
                        
                        <tr>
                           
                            <th>Artículo</th>
                            <?php if($ivaIncluido=="no"):?>
                            <?php if($soyMovil=="no"):?>
                            <th>Neto</th>
                            <?php endif;?>
                            <th>Precio<br>C/iva</th>
                            <th>Cant</th>                            
                            <th>SubTotal</th>
                            <?php else:?>
                            <th>Precio</th>
                            <th>Cant</th>                            
                            <th>SubTotal</th>
                            <?php endif;?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // parametros
                        
                        $conta=0; 
                        $clase="";
                        $subtotalImp=0;
                        $subtotalNeto=0;
                        $subtotalDesc=0;
                        $importeDesc=0;
                        $subtotalIva21=0;
                        $subtotalNetoIva21=0;
                        $subtotalIva105=0;
                        $subtotalNetoIva105=0;
                        $subtotalImpInt=0;
                        ?>
                        <?php 
                            foreach($artPed as $item){
//                                echo "<pre>";
//                                print_r($item);
//                                echo "</pre>";
                                $subtotalNeto +=$item['subtotalNeto'];
            
                                // descuento al Pie
                                $porDescPie     = $item['porDescPie'];
                                $impDescPie     = ($item['subtotalNeto'] * $porDescPie/100);
                                $netoDescuento  = $item['subtotalNeto'] - ($item['subtotalNeto'] * $porDescPie/100);
                                $subtotalDesc   += $netoDescuento; 
                                $importeDesc    += $impDescPie;
//                                $precioNeto=    $item["price"];
//                                $precioFinal= $item["priceN"];       

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
                        <?php foreach($artPed as $b):
//                             echo "<pre>";
//                                print_r($b);
//                                echo "</pre>";
                            ?>
                            <?php ($conta%2)?$clase="even":$clase="odd";?>
                            <tr class="<?php echo $clase?>">
                               
                                <td><?php echo $b['name'];?>
                                <?php if($b["descPor"]>0){ echo '<br><span class="verde">'.number_format($b["descPor"],0).'% off</span>';}?>
                                </td>
                                 
                                
                               
                                <?php if($ivaIncluido=="no"):?>
                                <?php if($soyMovil=="no"):?>
                                <td class="dt-right">$<?php echo number_format($b['netoN'] , 2, ',', '.'); ?></td>
                                <?php endif;?>
                                <td class="dt-right">$<?php echo number_format($b['priceN'] , 2, ',', '.');?></td>
                                <?php else:?>
                                
                                <td class="dt-right">$<?php echo number_format($b['priceN'] , 2, ',', '.');?></td>
                                <?php endif;?>
                                 <td class="dt-center"><?php echo $b['qty'];?></td>
                                <td class="dt-right"><strong>$<?php echo number_format($b['subtotal'] , 2, ',', '.');?></strong></td>
                            </tr>
                            <?php $conta++;?>
                        <?php endforeach;?>
                           
                    </tbody>
                </table>
                </div> 
                   
                <div id="divMiPedidoPie">
                    
                     <table class="dataTable" id="">
                         <thead>                             
                         <tr>                            
                             <th colspan="2">Resumen de Pedido</th>                                                        
                        </tr>
                    </thead>
                    <tbody>
                          <?php if($_SESSION["ivaIncluido"]!="no"):?>
                            <tr class="even">
                                <td class="alignRight" >Sub Total: </td>
                                <td class="alignRight">$<?php echo number_format($subtotal , 2, ',', '.')?></td>
                            </tr>
                            
                            <tr class="even">
                                <td class="alignRight" >Subtotal Imp Int: </td>
                                <td class="alignRight">$<?php echo number_format($subtotalImpInt, 2, ',', '.')?></td>
                            </tr>       
                            <tr class="even">
                                <td class="alignRight" >Percepciones: </td>
                                <td class="alignRight">$<?php echo number_format($pedidoArr["percepcionesT"], 2, ',', '.')?></td>
                            </tr>
                            <tr class="even">
                                <td class="alignRight" >Total: </td>
                                <td class="alignRight">$<?php echo number_format($subtotal +$pedidoArr["percepcionesT"], 2, ',', '.')?></td>
                            </tr>       
                            
                            <?php else:?>
                                                        
                           
                            <tr class="even">
                                <td class="alignRight" >Sub Total: </td>
                                <td class="alignRight">$<?php echo number_format($subtotalNeto , 2, ',', '.')?></td>
                            </tr>
                            <tr class="even">
                                <td class="alignRight" >Desc Pie:  </td>
                                <td class="alignRight"><?php echo number_format($porDescPie , 2, ',', '.')?>%</td>
                            </tr>
                            
                            <tr class="even">
                                <td class="alignRight" >Neto: </td>
                                <td class="alignRight">$<?php echo number_format($subtotalDesc , 2, ',', '.')?></td>
                            </tr>
                            <tr class="even">
                                <td class="alignRight" >Iva: </td>
                                <td class="alignRight">$<?php echo number_format($subtotalIva105 + $subtotalIva21, 2, ',', '.')?></td>
                            </tr>
                            <tr class="even"> 
                                <td class="alignRight" >Subtotal Imp Int: </td>
                                <td class="alignRight">$<?php echo number_format($subtotalImpInt, 2, ',', '.')?></td>
                            </tr>       
                            <tr class="even">
                                <td class="alignRight" >Percepciones: </td>
                                <td class="alignRight">$<?php echo number_format($pedidoArr["percepcionesT"], 2, ',', '.')?></td>
                            </tr>
                            <tr class="even">
                                <td class="importe" >Total: </td>
                                <td class="importe">$<?php echo number_format($subtotal +$pedidoArr["percepcionesT"], 2, ',', '.')?></td>
                            </tr>       
                                
                                <?php endif;?>
                     </table>
                </div>
                <?php endif;?>
                   <div class="w100p floatLeft"><h2 class="alignLeft"> <i class="fa fa-truck fa-lg"></i> Envio</h2></div>
            <div id="divMiPedidoDomicilio">
                 
            <form name="frmAcepto" id="frmAcepto" method="post" action="">
                <div class="domicilios" >
                    <h3><i class="fa fa-map-marker-alt fa-lg "></i> Mi Ubicación</h3>
                    <?php foreach ($_SESSION['domicilios_cliente'] as $dom):?>
                        <div class="unDomicilio">
                            <input type="radio" name="domicilio_entrega" id="dom-<?php echo $dom["idDom"];?>" value="<?php echo $dom["idDom"] . '|' . $dom["id_zona"]; ?>">
                            <label for="dom-<?php echo $dom["idDom"];?>" >
                                
                                    <strong>
                                        <?php echo $dom["Calle"] . ' '. $dom["NroCalle"] . ' '. $dom["Dpto"];?>                                
                                    </strong>
                                <br>    
                                
                                    <?php echo $dom["Provincia"] . ', '. $dom["NombreDepartamento"] . ', '. $dom["NombreDistrito"];?>
                                
                            
                            </label>
                        </div>
                    <?php endforeach;?>
                </div>
               
               
               <div  title='Seleccionar Forma de entrega'>
                    <h3 for='formaEntrega'><i class='fa fa-dolly fa-lg'></i> Forma de Entrega</h3>
                    <select name='formaEntrega' id='formaEntrega' >
                        <option value='Retira cliente despacho'>RETIRO EN TIENDA</option>
                        <?php if($validoLimiteDespacho==true):?>
                        <?php   if($valorPedido>$limiteDespacho):?>
                               <option selected value='Envía por despacho'>ENVIAR A DOMICILIO </option>
                            <?php endif;?>
                        <?php else:?>
                            <option selected value='Envía por despacho'>ENVIAR A DOMICILIO </option>
                        <?php endif;?>


                        
                    </select>
                </div>
                <div>
                     <h3><i class='fa fa-info-circle fa-lg'></i> N° Orden Compra</h3>
                     <input type="text" name="jcart-orden-compra" id="jcart-orden-compra" placeholder="nro orden compra...">
                    <h3><i class='fa fa-info-circle fa-lg'></i> Observaciones</h3>
                    <textarea id='jcart-detalle' placeholder='hh:mm horario de entrega o comentarios' name='jcart-detalle' class='detallePedido'></textarea>
                </div>
                
                
                    
                
                </div>
            <div id="botonesConfirmado" class="w100p floatLeft">
                <button name="confOperacion" id="aceptoOp" type="submit" value="ok" class='botonNuevo grande azul'>Finalizar <i class='fa fa-check fa-lg'></i></button>
                <button name="confOperacion" id="canceloOp" type="button" class='botonNuevo grande gris'>Cancelar <i class='fa fa-times fa-lg'></i></button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
