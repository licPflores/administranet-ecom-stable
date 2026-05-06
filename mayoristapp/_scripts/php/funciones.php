<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * funciones varias que hagan un par de cosas.
 */

/*control de horario.*/
function fn_control_horario($permisoControl,$link){

    if($permisoControl=="si"){
        $sqlHora="SELECT CURTIME()as horaControl;";
        $hcontrol = mysqli_query($link,$sqlHora) or die("No puedo controlar la hora".mysqli_error($link));
        $hora= mysqli_fetch_assoc($hcontrol);

    //    $desdeHora=mktime(07,00,00);
        $desdeHora=mktime(07,00,00);
        $hastaHora=mktime(23,30,00);

        $hh=  explode(":", $hora["horaControl"]);
        $horahora= mktime($hh[0],$hh[1],$hh[2]);
   // var_dump(!$horahora<=$desdeHora||$horahora>=$hastaHora);
        if($horahora<=$desdeHora||$horahora>=$hastaHora){
            // no ingreso restriccion horaria.
            // no debe ingresar hay que sacarlo
            return 0;
            
        }else{
            // puede ingresar al sistema
            return 1;
        }

    }else{
        // no se controla el horario
        return 2;
    }
}
function fn_cerrar_sesion(){
        session_unset();
        session_destroy();
        unset($_SESSION);
}

function encriptar_comprobante($cadena){
    
    $vuelta=rtrim(base64_encode($cadena));
    return $vuelta;
}

function desencriptar_comprobante($cadena){
    
    $json = rtrim(base64_decode($cadena));
    $arr=json_decode($json,TRUE);
    return $arr;
}
/*
 * trae_pre_movil
 * @empresa:= array()
 * @codMov:= int
 * @link:= dblink
 * @db:= dbase
 */
function trae_pre_movil($empresa,$codMov,$link,$db){
    mysqli_select_db($link,$db);
    mysqli_set_charset($link,'utf8');
    
    // recupero los datos del comprobante
    $sqlPedido="SELECT 
                        comp_ped.CodigoMovimiento,
                        comp_ped.id_comp_ped AS id,
                        DATE_FORMAT(comp_ped.Fecha,'%d/%m/%Y') AS Fecha,
                        comp_ped.NroComprobante,
                        comp_ped.SubTotalDesc,
                        comp_ped.IVA1,
                        comp_ped.IVA2,
                        comp_ped.Exento,
                        comp_ped.CondVenta,
                        DATE_FORMAT(comp_ped.FechaEntrega,'%d/%m/%Y') AS FechaEntrega,
                        comp_ped.FormaEntrega,
                        comp_ped.Estado,
                        viajantes.Nombre AS Vendedor,
                        comp_ped.ImporteVenta,
                        comp_ped.ImporteVentaL,
                        comp_ped.SubTotalGral,
                        comp_ped.SubTotal1,
                        comp_ped.SubTotal2,
                        comp_ped.SubTotalDesc1,
                        comp_ped.SubTotalDesc2,
                        comp_ped.total_percep,
                        comp_ped.PorDesc1,
                        comp_ped.ImpDesc1,
                        transporte.nombre_transporte,
                        DATE_FORMAT(comp_ped.Vencimiento,'%d/%m/%Y') AS Vencimiento,
                        CONCAT(usuarios.nombre_usuario,' ',usuarios.apellido_usuario) AS repartidor,
                        cliente.nombre_cliente,
                        cliente.Calle,
                        cliente.NroCalle,
                        cliente.Dpto,
                        cliente.CUIT,
                        Contribuyentes.IVA as iva_cliente,
                        comp_ped.PorDesc1,
                        comp_ped.ImpDesc1,
                        rp.detalle_comprobante,
                        comp_ped.Detalle,
                        (comp_ped.IVA1+
                        comp_ped.IVA2)AS IVA,
                        (comp_ped.SubTotalDesc+
                        comp_ped.IVA1+
                        comp_ped.IVA2) AS Total,
                        comp_ped.id_deposito_despacho,
                        deposito.NombreDeposito
                FROM 
                    comp_ped
                    LEFT JOIN cliente ON cliente.Codigo = comp_ped.Codigo    
                    LEFT JOIN usuarios ON comp_ped.id_repartidor = usuarios.id_usuario
                    LEFT JOIN viajantes ON comp_ped.CodViajante = viajantes.CodViajante
                    LEFT JOIN contribuyentes ON contribuyentes.idIVA = cliente.idIVA
                    LEFT JOIN transporte ON transporte.id_transporte = comp_ped.id_transporte
                    LEFT JOIN deposito ON deposito.CodDeposito = comp_ped.id_deposito_despacho

                    LEFT JOIN reporte_comprobante AS rp ON (rp.nombre_reporte_comprobante = comp_ped.TipoComprobante AND rp.id_sucursal = comp_ped.CodSucursal AND rp.id_punto_venta = comp_ped.id_pv)   

                WHERE  
                 comp_ped.CodigoMovimiento=".$codMov." 

                ORDER BY comp_ped.id_comp_ped";

    $hacerPed = mysqli_query($link,$sqlPedido) or die('No puedo recuperar el pedido'.mysqli_error($link).'<br>'.$sqlPedido);
    $pedido = mysqli_fetch_object($hacerPed);
    $renglones = array();
    $sqlRenglon="SELECT     stockp.IDArt,
                            stockp.CodigoArticulo,
                            stockp.Descripcion,
                            stockp.Salida,
                            stockp.PrecioVentaxU,
                            stockp.PrecioCostoxU,
                            stockp.PrecioIVAxU,
                            stockp.PrecioBrutoxU,
                            stockp.ImpDesc,
                            stockp.PorDesc,
                            stockp.PrecioVentaxR,
                            stockp.PrecioCostoxR,
                            stockp.PrecioIVAxR,
                            stockp.PrecioBrutoxR,
                            stockp.PrecioNetoxR,
                            iva.Alicuota AS Alicuota,
                            stockp.CodDeposito,
                            stockp.TipoIVA,
                            stockp.impuesto_interno,
                            stockp.impuesto_interno_subtotal,
                            stockp.id_manual,
                            stockp.NroPresupuesto,
                            stockp.TipoIVA,
                            stockp.promocion,
                            stockp.cantidad_pendiente,
                            (stockp.Salida - stockp.cantidad_pendiente) AS cantEntregada

                          FROM stockp
                          LEFT JOIN iva ON stockp.Alicuota = iva.ID
                          WHERE stockp.CodigoMovimiento=".$pedido->CodigoMovimiento;

    $hacerRenglon = mysqli_query($link,$sqlRenglon) or die('no puedo recuperar el renglon'.mysqli_error($link));
    while($renglon=  mysqli_fetch_object($hacerRenglon)){
        $renglones[]=$renglon;
    }
  

    /*
     * Dibujo del comprobante.
     */

    $muestraR = "";        
    $muestraR .='    <div id="comprobante">';
    $muestraR .='    <div id="cabeceraComprobante">';
    //$muestraR .='    <input type="image" src="_img/imprimir.png" id="imprimir" value="Imprimir">';
    $muestraR .='        <div id="izquierda">';
    $muestraR .='           <img src="'.traeLogo($link,null).'" alt="'.$empresa->nombre_empresa.'" title="'.$empresa->nombre_empresa.'" class="asBlock" />';

    $muestraR .='        </div>';
    $muestraR .='        <div id="tipoComp"><strong>PRE</strong><div id="leyenda">[Documento no válido como factura]</div></div>';
    $muestraR .='        <div id="derecha">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li class="destacado"><strong>PRESUPUESTO</strong></li>';
    $muestraR .='                   <li class="destacado"><strong>Nro: '.$pedido->NroComprobante.'</strong></li>';
    $muestraR .='                   <li><strong>Fecha: </strong>'.$pedido->Fecha.'</li>';
    //$muestraR .='                   <li><strong>Usuario: </strong> '.$objVendedor->nombre_usuario .' ' .$objVendedor->apellido_usuario.'</li>';
    $muestraR .='                   <li><strong>Vendedor: </strong> '.$pedido->Vendedor.'</li>';
    $muestraR .='                   <li><strong>Venc.: </strong>'.$pedido->Vencimiento.'</li>';

    $muestraR .='               </ul>';
    $muestraR .='        </div>';
    $muestraR .='        </div>';

    $muestraR .='    <div id="membrete">';
    $muestraR .='       <div class="columna">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li class="destacado"><strong>'.$empresa->nombre_empresa.'</strong></li>';
    $muestraR .='                   <li>Tel: '.$empresa->telefono_empresa.'</li>';
    $muestraR .='                   <li>E-mail: '.$empresa->email_empresa.'</li>';
    $muestraR .='                   <li>IVA: '.$empresa->iva_empresa.'</li>';
    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='       <div class="columna">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li>&nbsp;</li>';
    $muestraR .='                   <li>CUIT: '.$empresa->cuit_empresa.'</li>';
    $muestraR .='                   <li>Inic. Act.: '.  $empresa->iniact_empresa.'</li>';
    $muestraR .='                   <li>Ing. Brutos: '.$empresa->ingbrutos_empresa.'</li>';
    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='    </div>';  
    $muestraR .='    <div id="datosCliente">';
    $muestraR .='       <div class="columna">';

    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li><strong>Cliente: </strong>'.$pedido->nombre_cliente.'</li>';
    $muestraR .='                   <li><strong>Domicilio: </strong>'.$pedido->Calle.' Nº'. $pedido->NroCalle .' depto :'.$pedido->Dpto.   '</li>';
    $muestraR .='                   <li><strong>IVA: </strong>'.$pedido->iva_cliente.'</li>';
    $muestraR .='                   <li><strong>Cuit: </strong>'.$pedido->CUIT.'</li>';
    $muestraR .='                   <li><strong>Cond.Venta: </strong>'.$pedido->CondVenta.'</li>';
    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='       <div class="columna">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li><strong>Depósito despacho: </strong>'.$pedido->NombreDeposito.'</li>';
    $muestraR .='                   <li><strong>Fecha entrega: </strong>'.$pedido->FechaEntrega.'</li>';
    $muestraR .='                   <li><strong>Forma entrega: </strong>'.$pedido->FormaEntrega.'</li>';
    $muestraR .='                   <li><strong>Transporte: </strong>'.$pedido->nombre_transporte.'</li>';
    $muestraR .='                   <li><strong>Repartidor: </strong>'.$pedido->repartidor.'</li>';

    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='    </div>';  
    $muestraR .='   <div id="cuerpoComprobante">';
    $muestraR .='        <table id="tablaComp" >';
    $muestraR .='            <thead>';
    $muestraR .='                <tr>';
    $muestraR .='                    <th>Cant</th>';
    $muestraR .='                    <th>Cod.</th>';
    $muestraR .='                    <th>Descripcion</th>';
    $muestraR .='                    <th>Cant E</th>';
    $muestraR .='                    <th>Cant P</th>';
    $muestraR .='                    <th>P x U Lista</th>';
    //$muestraR .='                    <th>%Alic</th>';
    //$muestraR .='                    <th>Imp. Desc</th>';
    $muestraR .='                    <th>%Desc.</th>';
    $muestraR .='                    <th>P x U</th>';
    $muestraR .='                    <th>Total</th>';
    //$muestraR .='                    <th>Nº Presup.</th>';
    //$muestraR .='                    <th>IVA</th>';
    //$muestraR .='                    <th>Tipo IVA</th>';
    //$muestraR .='                    <th>Prom.</th>';                  
    $muestraR .='                </tr>';
    $muestraR .='            </thead>';
    $muestraR .='            <tbody>';
    $cantItems =0;
    foreach($renglones as $renglon):
        $cantItems++;
        $muestraR .='<tr>';
        $muestraR .='<td class="derecha">'. $renglon->Salida.'</td>';
        $muestraR .='<td class="izquierda">'.$renglon->IDArt .'</td>';
        if($renglon->promocion=="Si"){
            $muestraR .='<td class="izquierda">* '.$renglon->Descripcion.'</td>';
        }else{
            $muestraR .='<td class="izquierda">'.$renglon->Descripcion.'</td>';
        }
        $muestraR .='<td class="derecha">'. $renglon->cantEntregada.'</td>';
        $muestraR .='<td class="derecha">'. $renglon->cantidad_pendiente.'</td>';
        $muestraR .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxU,4).'</td>';
        $muestraR .='<td class="derecha">'. $renglon->PorDesc.'</td>';
        $muestraR .='<td class="derecha">$'. number_format($renglon->PrecioVentaxU,4).'</td>';

        //$muestraR .='                    <td class="derecha">'. $renglon->Alicuota.'</td>';
        //$muestraR .='                    <td class="derecha">'. $renglon->ImpDesc.'</td>';

        //$muestraR .='                    <td class="derecha">$'.$renglon->PrecioNetoxR.'</td>';
        //$muestraR .='                    <td class="izquierda">'.$renglon->NroPresupuesto.'</td>';
        $muestraR .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxR,4).'</td>';
        //$muestraR .='                    <td class="derecha">'. $renglon->TipoIVA.'</td>';
    //    $muestraR .='<td class="derecha">'. $renglon->promocion.'</td>';
        $muestraR .='</tr>';
    endforeach;
    $muestraR .='               </tbody>';
    $muestraR .='           </table>';
    $muestraR .='   </div>';
    $muestraR .='   <div id="pieComprobante">';
    $muestraR .='       <div id="detalle">';
    $muestraR .='       <p><strong>Cant Items: '.$cantItems.' </strong> </p>';
    $muestraR .='       <p><strong>Detalle: </strong> '.$pedido->Detalle.'</p>';
    $muestraR .='       <p><strong>Observ: </strong> '.$pedido->detalle_comprobante.' </p>';
    $muestraR .='       <p><strong> <u>* Articulo en promoción </u></strong> </p>';
    $muestraR .='       <p><strong>Son Pesos: '.$pedido->ImporteVentaL.' </strong> </p>';
    $muestraR .='       </div>';
    $muestraR .='       <div id="importe">';
    $muestraR .='           <table>';
    $muestraR .='               <tr>';
    $muestraR .='                   <td colspan="2" class="izquierda">Neto:</td><td class="derecha">'.$pedido->SubTotalGral.'</td>';
    $muestraR .='               </tr>';
    $muestraR .='               <tr>';
    $muestraR .='                   <td colspan="2" class="izquierda">Descuento '.number_format($pedido->PorDesc1,2).'%: </td><td class="derecha">'.$pedido->ImpDesc1.'</td>';
    $muestraR .='               </tr>';
    $muestraR .='               <tr>';
    $muestraR .='                   <td colspan="2" class="izquierda">Exento:</td><td class="derecha">'.$pedido->Exento.'</td>';
    $muestraR .='               </tr>';
    $muestraR .='               <tr>';
    $muestraR .='                   <td  class="izquierda">IVA:</td><td class="derecha">21,00</td><td class="derecha">'.$pedido->IVA1.'</td>';
    $muestraR .='               </tr>';
    $muestraR .='               <tr>';
    $muestraR .='                   <td  class="izquierda">IVA:</td><td class="derecha">10.50</td><td class="derecha">'.$pedido->IVA2.'</td>';
    $muestraR .='               </tr>';
    $muestraR .='               <tr>';
    $muestraR .='                   <td  class="izquierda">Percep IIBB Mza:</td><td class="derecha"></td><td class="derecha">'.$pedido->total_percep.'</td>';
    $muestraR .='               </tr>';
    $muestraR .='               <tr>';
    $muestraR .='                   <td colspan="3" class="separador">&nbsp;</td>';
    $muestraR .='               </tr>';
    $muestraR .='               <tr >';
    $muestraR .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.($pedido->Total+$pedido->total_percep).'</td>';
    $muestraR .='               </tr>';

    $muestraR .='           </table>';

    $muestraR .='       </div>';
    $muestraR .='       <div id="final">';

    $muestraR .='       <p>Comprobante generado por: ';
    $muestraR .='       <img src="sistema/_img/logo_administranet_chico.gif"></p>';
    $muestraR .='           <p>Tel:(0261)- 4274480 / 4283071 |  <a target="_blank" title="administraNET Gestión software de Facturación y Stock" href="http://www.administranet.com.ar">www.administranet.com.ar</a></p>';
    $muestraR .='       </div>';
    $muestraR .='   </div>';
    $muestraR .='</div>';

    //PDF
    //echo traeLogo();
    require_once 'sistema/_lib/mpdf/mpdf.php';

    $mpdf = new mPDF('c','A4');
    $stylesheet = file_get_contents('sistema/_css/pdf.css');
    $mpdf->WriteHTML($stylesheet,1);
    $mpdf->WriteHTML($muestraR);
    $mpdf->Output('Pre-'.$pedido->NroComprobante.'.pdf','D');
    exit; 
    
    
}
/*
 * trae_ped_movil
 * @empresa:= array()
 * @codMov:= int
 * @link:= dblink
 * @db:= dbase
 */
function trae_ped_movil($empresa,$codMov,$link,$db){
    mysqli_select_db($link,$db);
    mysqli_set_charset($link,'utf8');
    
    // recupero los datos del comprobante
    $sqlPedido="SELECT 
                        comp_ped.CodigoMovimiento,
                        comp_ped.id_comp_ped AS id,
                        DATE_FORMAT(comp_ped.Fecha,'%d/%m/%Y') AS Fecha,
                        comp_ped.NroComprobante,
                        comp_ped.SubTotalDesc,
                        comp_ped.IVA1,
                        comp_ped.IVA2,
                        comp_ped.Exento,
                        comp_ped.CondVenta,
                        DATE_FORMAT(comp_ped.FechaEntrega,'%d/%m/%Y') AS FechaEntrega,
                        comp_ped.FormaEntrega,
                        comp_ped.Estado,
                        viajantes.Nombre AS Vendedor,
                        comp_ped.ImporteVenta,
                        comp_ped.ImporteVentaL,
                        comp_ped.SubTotalGral,
                        comp_ped.SubTotal1,
                        comp_ped.SubTotal2,
                        comp_ped.SubTotalDesc1,
                        comp_ped.SubTotalDesc2,
                        comp_ped.total_percep,
                        comp_ped.PorDesc1,
                        comp_ped.ImpDesc1,
                        transporte.nombre_transporte,
                        DATE_FORMAT(comp_ped.Vencimiento,'%d/%m/%Y') AS Vencimiento,
                        CONCAT(usuarios.nombre_usuario,' ',usuarios.apellido_usuario) AS repartidor,
                        cliente.nombre_cliente,
                        cliente.Calle,
                        cliente.NroCalle,
                        cliente.Dpto,
                        cliente.CUIT,
                        Contribuyentes.IVA as iva_cliente,
                        comp_ped.PorDesc1,
                        comp_ped.ImpDesc1,
                        rp.detalle_comprobante,
                        comp_ped.Detalle,
                        (comp_ped.IVA1+
                        comp_ped.IVA2)AS IVA,
                        (comp_ped.SubTotalDesc+
                        comp_ped.IVA1+
                        comp_ped.IVA2) AS Total,
                        comp_ped.id_deposito_despacho,
                        deposito.NombreDeposito
                FROM 
                    comp_ped
                    LEFT JOIN cliente ON cliente.Codigo = comp_ped.Codigo    
                    LEFT JOIN usuarios ON comp_ped.id_repartidor = usuarios.id_usuario
                    LEFT JOIN viajantes ON comp_ped.CodViajante = viajantes.CodViajante
                    LEFT JOIN contribuyentes ON contribuyentes.idIVA = cliente.idIVA
                    LEFT JOIN transporte ON transporte.id_transporte = comp_ped.id_transporte
                    LEFT JOIN deposito ON deposito.CodDeposito = comp_ped.id_deposito_despacho

                    LEFT JOIN reporte_comprobante AS rp ON (rp.nombre_reporte_comprobante = comp_ped.TipoComprobante AND rp.id_sucursal = comp_ped.CodSucursal AND rp.id_punto_venta = comp_ped.id_pv)   

                WHERE  
                 comp_ped.CodigoMovimiento=".$codMov." 

                ORDER BY comp_ped.id_comp_ped";

    $hacerPed = mysqli_query($link,$sqlPedido) or die('No puedo recuperar el pedido'.mysqli_error($link).'<br>'.$sqlPedido);
    $pedido = mysqli_fetch_object($hacerPed);
    $renglones = array();
    $sqlRenglon="SELECT     stockp.IDArt,
                            stockp.CodigoArticulo,
                            stockp.Descripcion,
                            stockp.Salida,
                            stockp.PrecioVentaxU,
                            stockp.PrecioCostoxU,
                            stockp.PrecioIVAxU,
                            stockp.PrecioBrutoxU,
                            stockp.ImpDesc,
                            stockp.PorDesc,
                            stockp.PrecioVentaxR,
                            stockp.PrecioCostoxR,
                            stockp.PrecioIVAxR,
                            stockp.PrecioBrutoxR,
                            stockp.PrecioNetoxR,
                            iva.Alicuota AS Alicuota,
                            stockp.CodDeposito,
                            stockp.TipoIVA,
                            stockp.impuesto_interno,
                            stockp.impuesto_interno_subtotal,
                            stockp.id_manual,
                            stockp.NroPresupuesto,
                            stockp.TipoIVA,
                            stockp.promocion,
                            stockp.cantidad_pendiente,
                            (stockp.Salida - stockp.cantidad_pendiente) AS cantEntregada

                          FROM stockp
                          LEFT JOIN iva ON stockp.Alicuota = iva.ID
                          WHERE stockp.CodigoMovimiento=".$pedido->CodigoMovimiento;

    $hacerRenglon = mysqli_query($link,$sqlRenglon) or die('no puedo recuperar el renglon'.mysqli_error($link));
    while($renglon=  mysqli_fetch_object($hacerRenglon)){
        $renglones[]=$renglon;
    }
  

    /*
     * Dibujo del comprobante.
     */

    $muestraR = "";        
    $muestraR .='    <div id="comprobante">';
    $muestraR .='    <div id="cabeceraComprobante">';
    //$muestraR .='    <input type="image" src="_img/imprimir.png" id="imprimir" value="Imprimir">';
    $muestraR .='        <div id="izquierda">';
    $muestraR .='           <img src="'.traeLogo($link,null).'" alt="'.$empresa->nombre_empresa.'" title="'.$empresa->nombre_empresa.'" class="asBlock" />';


    $muestraR .='        </div>';
    $muestraR .='        <div id="tipoComp"><strong>PED</strong><div id="leyenda">[Documento no válido como factura]</div></div>';
    $muestraR .='        <div id="derecha">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li class="destacado"><strong>PEDIDO</strong></li>';
    $muestraR .='                   <li class="destacado"><strong>Nro: '.$pedido->NroComprobante.'</strong></li>';
    $muestraR .='                   <li><strong>Fecha: </strong>'.$pedido->Fecha.'</li>';
    //$muestraR .='                   <li><strong>Usuario: </strong> '.$objVendedor->nombre_usuario .' ' .$objVendedor->apellido_usuario.'</li>';
    $muestraR .='                   <li><strong>Vendedor: </strong> '.$pedido->Vendedor.'</li>';
    $muestraR .='                   <li><strong>Venc.: </strong>'.$pedido->Vencimiento.'</li>';

    $muestraR .='               </ul>';
    $muestraR .='        </div>';
    $muestraR .='        </div>';

    $muestraR .='    <div id="membrete">';
    $muestraR .='       <div class="columna">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li class="destacado"><strong>'.$empresa->nombre_empresa.'</strong></li>';
    $muestraR .='                   <li>Tel: '.$empresa->telefono_empresa.'</li>';
    $muestraR .='                   <li>E-mail: '.$empresa->email_empresa.'</li>';
    $muestraR .='                   <li>IVA: '.$empresa->iva_empresa.'</li>';
    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='       <div class="columna">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li>&nbsp;</li>';
    $muestraR .='                   <li>CUIT: '.$empresa->cuit_empresa.'</li>';
    $muestraR .='                   <li>Inic. Act.: '.  $empresa->iniact_empresa.'</li>';
    $muestraR .='                   <li>Ing. Brutos: '.$empresa->ingbrutos_empresa.'</li>';
    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='    </div>';  
    $muestraR .='    <div id="datosCliente">';
    $muestraR .='       <div class="columna">';

    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li><strong>Cliente: </strong>'.$pedido->nombre_cliente.'</li>';
    $muestraR .='                   <li><strong>Domicilio: </strong>'.$pedido->Calle.' Nº'. $pedido->NroCalle .' depto :'.$pedido->Dpto.   '</li>';
    $muestraR .='                   <li><strong>IVA: </strong>'.$pedido->iva_cliente.'</li>';
    $muestraR .='                   <li><strong>Cuit: </strong>'.$pedido->CUIT.'</li>';
    $muestraR .='                   <li><strong>Cond.Venta: </strong>'.$pedido->CondVenta.'</li>';
    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='       <div class="columna">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li><strong>Depósito despacho: </strong>'.$pedido->NombreDeposito.'</li>';
    $muestraR .='                   <li><strong>Fecha entrega: </strong>'.$pedido->FechaEntrega.'</li>';
    $muestraR .='                   <li><strong>Forma entrega: </strong>'.$pedido->FormaEntrega.'</li>';
    $muestraR .='                   <li><strong>Transporte: </strong>'.$pedido->nombre_transporte.'</li>';
    $muestraR .='                   <li><strong>Repartidor: </strong>'.$pedido->repartidor.'</li>';

    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='    </div>';  
    $muestraR .='   <div id="cuerpoComprobante">';
    $muestraR .='        <table id="tablaComp" >';
    $muestraR .='            <thead>';
    $muestraR .='                <tr>';
    $muestraR .='                    <th>Cant</th>';
    $muestraR .='                    <th>Cod.</th>';
    $muestraR .='                    <th>Descripcion</th>';
    $muestraR .='                    <th>Cant E</th>';
    $muestraR .='                    <th>Cant P</th>';
    $muestraR .='                    <th>P x U Lista</th>';
    //$muestraR .='                    <th>%Alic</th>';
    //$muestraR .='                    <th>Imp. Desc</th>';
    $muestraR .='                    <th>%Desc.</th>';
    $muestraR .='                    <th>P x U</th>';
    $muestraR .='                    <th>Total</th>';
    //$muestraR .='                    <th>Nº Presup.</th>';
    //$muestraR .='                    <th>IVA</th>';
    //$muestraR .='                    <th>Tipo IVA</th>';
    //$muestraR .='                    <th>Prom.</th>';                  
    $muestraR .='                </tr>';
    $muestraR .='            </thead>';
    $muestraR .='            <tbody>';
    $cantItems =0;
    foreach($renglones as $renglon):
        $cantItems++;
        $muestraR .='<tr>';
        $muestraR .='<td class="derecha">'. $renglon->Salida.'</td>';
        $muestraR .='<td class="izquierda">'.$renglon->IDArt .'</td>';
        if($renglon->promocion=="Si"){
            $muestraR .='<td class="izquierda">* '.$renglon->Descripcion.'</td>';
        }else{
            $muestraR .='<td class="izquierda">'.$renglon->Descripcion.'</td>';
        }
        $muestraR .='<td class="derecha">'. $renglon->cantEntregada.'</td>';
        $muestraR .='<td class="derecha">'. $renglon->cantidad_pendiente.'</td>';
        $muestraR .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxU,4).'</td>';
        $muestraR .='<td class="derecha">'. $renglon->PorDesc.'</td>';
        $muestraR .='<td class="derecha">$'. number_format($renglon->PrecioVentaxU,4).'</td>';

        //$muestraR .='                    <td class="derecha">'. $renglon->Alicuota.'</td>';
        //$muestraR .='                    <td class="derecha">'. $renglon->ImpDesc.'</td>';

        //$muestraR .='                    <td class="derecha">$'.$renglon->PrecioNetoxR.'</td>';
        //$muestraR .='                    <td class="izquierda">'.$renglon->NroPresupuesto.'</td>';
        $muestraR .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxR,4).'</td>';
        //$muestraR .='                    <td class="derecha">'. $renglon->TipoIVA.'</td>';
    //    $muestraR .='<td class="derecha">'. $renglon->promocion.'</td>';
        $muestraR .='</tr>';
    endforeach;
    $muestraR .='               </tbody>';
    $muestraR .='           </table>';
    $muestraR .='   </div>';
    $muestraR .='   <div id="pieComprobante">';
    $muestraR .='       <div id="detalle">';
    $muestraR .='       <p><strong>Cant Items: '.$cantItems.' </strong> </p>';
    $muestraR .='       <p><strong>Detalle: </strong> '.$pedido->Detalle.'</p>';
    $muestraR .='       <p><strong>Observ: </strong> '.$pedido->detalle_comprobante.' </p>';
    $muestraR .='       <p><strong> <u>* Articulo en promoción </u></strong> </p>';
    $muestraR .='       <p><strong>Son Pesos: '.$pedido->ImporteVentaL.' </strong> </p>';
    $muestraR .='       </div>';
    $muestraR .='       <div id="importe">';
    $muestraR .='           <table>';
    $muestraR .='               <tr>';
    $muestraR .='                   <td colspan="2" class="izquierda">Neto:</td><td class="derecha">'.$pedido->SubTotalGral.'</td>';
    $muestraR .='               </tr>';
    $muestraR .='               <tr>';
    $muestraR .='                   <td colspan="2" class="izquierda">Descuento '.number_format($pedido->PorDesc1,2).'%: </td><td class="derecha">'.$pedido->ImpDesc1.'</td>';
    $muestraR .='               </tr>';
    $muestraR .='               <tr>';
    $muestraR .='                   <td colspan="2" class="izquierda">Exento:</td><td class="derecha">'.$pedido->Exento.'</td>';
    $muestraR .='               </tr>';
    $muestraR .='               <tr>';
    $muestraR .='                   <td  class="izquierda">IVA:</td><td class="derecha">21,00</td><td class="derecha">'.$pedido->IVA1.'</td>';
    $muestraR .='               </tr>';
    $muestraR .='               <tr>';
    $muestraR .='                   <td  class="izquierda">IVA:</td><td class="derecha">10.50</td><td class="derecha">'.$pedido->IVA2.'</td>';
    $muestraR .='               </tr>';
    $muestraR .='               <tr>';
    $muestraR .='                   <td  class="izquierda">Percep IIBB Mza:</td><td class="derecha"></td><td class="derecha">'.$pedido->total_percep.'</td>';
    $muestraR .='               </tr>';
    $muestraR .='               <tr>';
    $muestraR .='                   <td colspan="3" class="separador">&nbsp;</td>';
    $muestraR .='               </tr>';
    $muestraR .='               <tr >';
    $muestraR .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.($pedido->Total+$pedido->total_percep).'</td>';
    $muestraR .='               </tr>';

    $muestraR .='           </table>';

    $muestraR .='       </div>';
    $muestraR .='       <div id="final">';

    $muestraR .='       <p>Comprobante generado por: ';
    $muestraR .='       <img src="_img/logo_administranet_chico.gif"></p>';
    $muestraR .='           <p>Tel:(0261)- 4274480 / 4283071 |  <a href="http://www.administranet.com.ar">www.administranet.com.ar</a></p>';
    $muestraR .='       </div>';
    $muestraR .='   </div>';
    $muestraR .='</div>';

    //PDF
    //echo traeLogo();
    require_once 'sistema/_lib/mpdf/mpdf.php';

    $mpdf = new mPDF('c','A4');
    $stylesheet = file_get_contents('sistema/_css/pdf.css');
    $mpdf->WriteHTML($stylesheet,1);
    $mpdf->WriteHTML($muestraR);
    $mpdf->Output('Ped-'.$pedido->NroComprobante.'.pdf','D');
    exit; 
    
}
/*
 * trae_fact_movil
 * @empresa:= array()
 * @codMov:= int
 * @link:= dblink
 * @db:= dbase
 */
function trae_fact_movil($empresa,$codMov,$link,$db){
   if(is_object($empresa)){
       $empresa= (array) $empresa;
   }
    mysqli_select_db($link,$db);
    mysqli_set_charset($link,'utf8');
    if(isset($codMov)){                              
                
                $sqlPedido="SELECT 
                                CASE 
                                    WHEN cc.TipoComprobante='FA' THEN 'A'
                                    WHEN cc.TipoComprobante='FB' THEN 'B'
                                    WHEN cc.TipoComprobante='FC' THEN 'C'
                                    WHEN cc.TipoComprobante='FM' THEN 'M'
                                END AS tipoFactura,                                    
                                    cc.CodigoMovimiento,
                                    cc.id_cuentacliente AS id,
                                    DATE_FORMAT(cc.Fecha,'%d/%m/%Y') AS Fecha,
                                    cc.NroComprobante,
                                    cc.SubTotalDesc,
                                    cc.IVA1,
                                    cc.IVA2,
                                    cc.Exento,
                                    cc.CondVenta,
                                               cc.Estado,
                                    viajantes.Nombre AS Vendedor,
                                    cc.ImporteVenta,
                                    cc.ImporteVentaL,
                                    cc.SubTotalGral,
                                    cc.SubTotal1,
                                    cc.SubTotal2,
                                    cc.SubTotalDesc1,
                                    cc.SubTotalDesc2,
                                    cc.total_percep,
                                    cc.PorDesc1,
                                    cc.ImpDesc1,                                    
                                    DATE_FORMAT(cc.Vencimiento,'%d/%m/%Y') AS Vencimiento,
                                    CONCAT(usuarios.nombre_usuario,' ',usuarios.apellido_usuario) AS facturador,
                                    cliente.nombre_cliente,
                                    cliente.Calle,
                                    cliente.NroCalle,
                                    cliente.Dpto,
                                    cliente.CUIT,
                                    Contribuyentes.IVA as iva_cliente,
                                    cc.PorDesc1,
                                    cc.ImpDesc1,
                                    rp.detalle_comprobante,
                                    cc.Detalle,
                                    (cc.IVA1+
                                    cc.IVA2)AS IVA,
                                    (cc.SubTotalDesc+
                                    cc.IVA1+
                                    cc.IVA2) AS Total,
                                    cc.id_deposito_despacho,
                                    deposito.NombreDeposito,
                                    cc.fe_cae,
                                    DATE_FORMAT(cc.fe_vto_cae,'%d/%m/%Y') AS vtoCae,
                                    DATE_FORMAT(cc.fe_vto_cae,'%Y%m%d') AS vtoCaeN,
                                    cc.fe_comp,
                                    pv.nro_punto_venta
                            FROM 
                                cuentacliente AS cc
                                LEFT JOIN cliente ON cliente.Codigo = cc.Codigo
                                LEFT JOIN punto_venta AS pv ON pv.id_punto_venta=cc.id_pv
                                LEFT JOIN usuarios ON cc.IdUsuario = usuarios.id_usuario
                                LEFT JOIN viajantes ON cc.CodViajante = viajantes.CodViajante
                                LEFT JOIN contribuyentes ON contribuyentes.idIVA = cliente.idIVA
                               
                                LEFT JOIN deposito ON deposito.CodDeposito = cc.id_deposito_despacho
                            
                                LEFT JOIN reporte_comprobante AS rp ON (rp.nombre_reporte_comprobante = cc.TipoComprobante AND rp.id_sucursal = cc.CodSucursal AND rp.id_punto_venta = cc.id_pv)   
                            
                            WHERE  
                             cc.CodigoMovimiento=".$codMov."
                                
                            ORDER BY cc.id_cuentacliente;";
                
                $hacerPed = mysqli_query($link,$sqlPedido) or die('No puedo recuperar la factura'.mysqli_error($link).'<br><pre>'.$sqlPedido.'</pre>');
                $factura = mysqli_fetch_object($hacerPed);
                $renglones = array();
                $sqlRenglon="SELECT     stock.IDArt,
                                        stock.CodigoArticulo,
                                        stock.Descripcion,
                                        stock.Salida,
                                        stock.PrecioVentaxU,
                                        stock.PrecioCostoxU,
                                        stock.PrecioIVAxU,
                                        stock.PrecioBrutoxU,
                                        stock.ImpDesc,
                                        stock.PorDesc,
                                        stock.PrecioVentaxR,
                                        stock.PrecioCostoxR,
                                        stock.PrecioIVAxR,
                                        stock.PrecioBrutoxR,
                                        stock.PrecioNetoxR,
                                        iva.Alicuota AS Alicuota,
                                        stock.CodDeposito,
                                        stock.TipoIVA,
                                        stock.impuesto_interno,
                                        stock.impuesto_interno_subtotal,
                                        stock.id_manual,
                                        stock.NroPresupuesto,
                                        stock.TipoIVA,
                                        stock.promocion
                                      FROM stock
                                      LEFT JOIN iva ON stock.Alicuota = iva.ID
                                      WHERE stock.CodigoMovimiento=".$factura->CodigoMovimiento;
                
                $hacerRenglon = mysqli_query($link,$sqlRenglon) or die('no puedo recuperar el renglon'.mysqli_error($link));
                while($renglon=  mysqli_fetch_object($hacerRenglon)){
                    $renglones[]=$renglon;
                }
                
//                echo '<pre>'.print_r($renglones).'</pre>';
//                echo '<pre>';
//                print_r($factura->fe_comp);
//                echo '</pre>';
    if($factura->fe_comp=="Si"){
        $m = hacer_factura_electronica($factura, $renglones, $empresa, $codMov,$link);
    }else{
        $m = hacer_factura($factura, $renglones, $empresa,$link);
    }
/* generar PDF 
 * =============================================================================
 */
//echo traeLogo();
//echo "<head><style>";    
//echo $stylesheet = file_get_contents('_css/pdf.css');    
//echo "</style></head>";
//echo $m;
    
/*/pdf m 2 genero pdf
 * =============================================================================
 */
require_once  'sistema/_lib/mpdf2/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf(['mode' => 'c',
    'margin_left' => 5,
	'margin_right' => 5,
	'margin_top' => 4,
	'margin_bottom' => 5,
	'margin_header' => 5,
	'margin_footer' => 7
    ]);


$stylesheet = file_get_contents('sistema/_css/pdf.css');
    $mpdf->WriteHTML($stylesheet,1);
    $mpdf->WriteHTML($m,2);
    $mpdf->SetDisplayMode('fullpage');
   
    //$mpdf->Output();
    $mpdf->Output('FACT-'.$factura->NroComprobante.'.pdf','D');
    exit; 
}else{
    echo "Falta parametro obligatorio";
}
    
    
    
    
}

function hacer_factura_electronica($fact,$renglones,$empresa,$codMov,$connV){
    
    $imgCodBarra= trae_cod_barra($fact,$empresa['cuit_empresa']);
    $feHtml = "";        
    $feHtml .='    <div id="comprobante">';
    $feHtml .='    <div id="cabeceraComprobante">';
    //$feHtml .='    <input type="image" src="_img/imprimir.png" id="imprimir" value="Imprimir">';
    $feHtml .='        <div id="izquierda">';
    $feHtml .='           <div id="logo"><img src="'.traeLogo($connV).'" alt="'.$empresa['nombre_empresa'].'" title="'.$empresa['nombre_empresa'].'"  /></div>';
    $feHtml .='           <div id="leyenda">Comprobante electrónico</div>'; 
    $feHtml .='        </div>';
    $feHtml .='        <div id="tipoComp"><strong>'.$fact->tipoFactura.'</strong><br>Cod.01</div>';
    $feHtml .='        <div id="derecha">';
    $feHtml .='               <ul class="datoComprobante">';
    $feHtml .='                   <li class="destacadoF"><strong>FACTURA</strong></li>';
    $feHtml .='                   <li class="destacado"><strong>Nro: '.$fact->NroComprobante.'</strong></li>';
    $feHtml .='                   <li><strong>Fecha: </strong>'.$fact->Fecha.'</li>';
    $feHtml .='                   <li><strong>Usuario: </strong> '.$fact->facturador.'</li>';
    $feHtml .='                   <li><strong>Vendedor: </strong> '.$fact->Vendedor.'</li>';
    $feHtml .='                   <li><strong>Venc.: </strong>'.$fact->Vencimiento.'</li>';

    $feHtml .='               </ul>';
    $feHtml .='        </div>';
    $feHtml .='        </div>';

    $feHtml .='    <div id="membrete">';
    $feHtml .='       <div class="columna">';
    $feHtml .='               <ul class="datoComprobante">';
    $feHtml .='                   <li class="destacado"><strong>'.$empresa['nombre_empresa'].'</strong></li>';
    $feHtml .='                   <li>'.$empresa['domicilio_empresa'].'</li>';
    $feHtml .='                   <li>Tel: '.$empresa['telefono_empresa'].'</li>';
    $feHtml .='                   <li>E-mail: '.$empresa['email_empresa'].'</li>';
    $feHtml .='                   <li>IVA: '.$empresa['iva_empresa'].'</li>';
    $feHtml .='               </ul>';
    $feHtml .='       </div>';
    $feHtml .='       <div class="columna">';
    $feHtml .='               <ul class="datoComprobante">';
    $feHtml .='                   <li>&nbsp;</li>';
    $feHtml .='                   <li>CUIT: '.$empresa['cuit_empresa'].'</li>';
    $feHtml .='                   <li>Inic. Act.: '.  $empresa['iniact_empresa'].'</li>';
    $feHtml .='                   <li>Ing. Brutos: '.$empresa['ingbrutos_empresa'].'</li>';
    $feHtml .='               </ul>';
    $feHtml .='       </div>';
    $feHtml .='    </div>';  
    $feHtml .='    <div id="datosCliente">';
    $feHtml .='       <div class="columna">';

    $feHtml .='               <ul class="datoComprobante">';
    $feHtml .='                   <li><strong>Cliente: </strong>'.$fact->nombre_cliente.'</li>';
    $feHtml .='                   <li><strong>Domicilio: </strong>'.$fact->Calle.' Nº'. $fact->NroCalle .' depto '.$fact->Dpto.   '</li>';
    $feHtml .='                   <li><strong>IVA: </strong>'.$fact->iva_cliente.' <strong>CUIT: </strong>'.$fact->CUIT.'</li>';
    $feHtml .='                   <li><strong>Entrega: </strong>-</li>';
    $feHtml .='               </ul>';
    $feHtml .='       </div>';
    $feHtml .='       <div class="columna">';
    $feHtml .='               <ul class="datoComprobante">';
    $feHtml .='                   <li><strong>Cond.Venta: </strong>'.$fact->CondVenta.'</li>';
    $feHtml .='                   <li><strong>Venc.: </strong>'.$fact->Vencimiento.'</li>';

    $feHtml .='               </ul>';
    $feHtml .='       </div>';
    $feHtml .='    </div>';  
    $feHtml .='   <div id="cuerpoComprobante">';
    $feHtml .='        <table id="tablaComp" >';
    $feHtml .='            <thead>';
    $feHtml .='                <tr>';
    $feHtml .='                    <th>Cantidad</th>';
    $feHtml .='                    <th>Código</th>';
    $feHtml .='                    <th>Descripción</th>';

    $feHtml .='                    <th>Prec x U Lista</th>';
    $feHtml .='                    <th>%Alic</th>';
    //$feHtml .='                    <th>Imp. Desc</th>';
    $feHtml .='                    <th>%Desc.</th>';
    $feHtml .='                    <th>Prec x U</th>';
    $feHtml .='                    <th>Total</th>';
    //$feHtml .='                    <th>Nº Presup.</th>';
    //$feHtml .='                    <th>IVA</th>';
    //$feHtml .='                    <th>Tipo IVA</th>';
    //$feHtml .='                    <th>Prom.</th>';                  
    $feHtml .='                </tr>';
    $feHtml .='            </thead>';
    $feHtml .='            <tbody>';
    $cantItems =0;
    foreach($renglones as $renglon):
        $cantItems++;
        $feHtml .='<tr>';
        $feHtml .='<td class="derecha">'. $renglon->Salida.'</td>';
        $feHtml .='<td class="izquierda">'.$renglon->IDArt .'</td>';
        if($renglon->promocion=="Si"){
            $feHtml .='<td class="izquierda">* '.$renglon->Descripcion.'</td>';
        }else{
            $feHtml .='<td class="izquierda">'.$renglon->Descripcion.'</td>';
        }
       
        $feHtml .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxU,4,",",".").'</td>';
        $feHtml .='<td class="derecha">'. $renglon->PorDesc.'</td>';
        $feHtml .='<td class="derecha">$'. number_format($renglon->PrecioVentaxU,4,",",".").'</td>';

        $feHtml .='                    <td class="derecha">'. $renglon->Alicuota.'</td>';
        //$feHtml .='                    <td class="derecha">'. $renglon->ImpDesc.'</td>';

        //$feHtml .='                    <td class="derecha">$'.$renglon->PrecioNetoxR.'</td>';
        //$feHtml .='                    <td class="izquierda">'.$renglon->NroPresupuesto.'</td>';
        $feHtml .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxR,4,",",".").'</td>';
        //$feHtml .='                    <td class="derecha">'. $renglon->TipoIVA.'</td>';
    //    $feHtml .='<td class="derecha">'. $renglon->promocion.'</td>';
        $feHtml .='</tr>';
    endforeach;
    $feHtml .='               </tbody>';
    $feHtml .='           </table>';
    $feHtml .='   </div>';
    $feHtml .='   <div id="pieComprobante">';
    $feHtml .='       <div id="detalle">';
    $feHtml .='       <p><strong>Cant Items: '.$cantItems.' </strong> </p>';
    $feHtml .='       <p><strong>Detalle: </strong> '.$fact->Detalle.'</p>';
    $feHtml .='       <p><strong>Observ: </strong> '.$fact->detalle_comprobante.' </p>';
    $feHtml .='       <p><strong> <u>* Articulo en promoción </u></strong> </p>';
    $feHtml .='       <p><strong>Son Pesos: '.$fact->ImporteVentaL.' </strong> </p>';
    $feHtml .='       </div>';
    $feHtml .='       <div id="importe">';
    $feHtml .='           <table>';
    $feHtml .='               <tr>';
    $feHtml .='                   <td colspan="2" class="izquierda">Neto:</td><td class="derecha">'.number_format($fact->SubTotalGral,2,",",".").'</td>';
    $feHtml .='               </tr>';
    $feHtml .='               <tr>';
    $feHtml .='                   <td colspan="2" class="izquierda">Descuento '.number_format($fact->PorDesc1,2,",",".").'%: </td><td class="derecha">'.number_format($fact->ImpDesc1,4,",",".").'</td>';
    $feHtml .='               </tr>';
    $feHtml .='               <tr>';
    $feHtml .='                   <td colspan="2" class="izquierda">Exento:</td><td class="derecha">'.number_format($fact->Exento,2,",",".").'</td>';
    $feHtml .='               </tr>';
    $feHtml .='               <tr>';
    $feHtml .='                   <td  class="izquierda">IVA:</td><td class="derecha">21,00</td><td class="derecha">'.number_format($fact->IVA1,2,",",".").'</td>';
    $feHtml .='               </tr>';
    $feHtml .='               <tr>';
    $feHtml .='                   <td  class="izquierda">IVA:</td><td class="derecha">10.50</td><td class="derecha">'.number_format($fact->IVA2,2,",",".").'</td>';
    $feHtml .='               </tr>';
    $feHtml .='               <tr>';
    $feHtml .='                   <td  class="izquierda" colspan="2">Percepciones:</td><td class="derecha">'.number_format($fact->total_percep,2,",",".").'</td>';
    $feHtml .='               </tr>';
    $feHtml .='               <tr>';
    $feHtml .='                   <td colspan="3" class="separador">&nbsp;</td>';
    $feHtml .='               </tr>';
    $feHtml .='               <tr >';
    $feHtml .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'. number_format(($fact->Total+$fact->total_percep),2,",",".").'</td>';
    $feHtml .='               </tr>';

    $feHtml .='           </table>';

    $feHtml .='       </div>';
    
    $feHtml .='       <div id="final">';
    $feHtml .='         <div id="final-izq">';
    $feHtml .='             <img src="sistema/_img/logo_afip.jpg" style="width:350px">';
    $feHtml .='             '.$imgCodBarra.'';
    $feHtml .='         </div>';
    $feHtml .='         <div id="final-der">';
    $feHtml .='             <p><label><strong>Nro CAE: </strong></label> '.$fact->fe_cae.'<br>';
    $feHtml .='                 <label><strong>Vto CAE: </strong></label> '.$fact->vtoCae.'</p>';
    $feHtml .='             <p>Comprobante generado por: ';
    $feHtml .='             <img src="sistema/_img/logo_administranet_chico.gif"></p>';
    $feHtml .='             <p>Tel:(0261)- 4274480 / 4283071 |  <a href="https://www.administranet.com.ar">www.administranet.com.ar</a></p>';
    $feHtml .='         </div>';
    $feHtml .='       </div>';
    $feHtml .='   </div>';
    $feHtml .='</div>';
    return $feHtml;
}

/*
 * Generar HTML Factura Comun
 */

function hacer_factura($fact,$renglones,$empresa,$connV){
    
    
    $faHtml = "";        
    $faHtml .='    <div id="comprobante">';

    //$faHtml .='    <input type="button" id="imprimir" value="Imprimir">';

    $faHtml .='    <div id="cabeceraComprobante">';
    //$faHtml .='    <input type="image" src="_img/imprimir.png" id="imprimir" value="Imprimir">';
    $faHtml .='        <div id="izquierda">';
    $faHtml .='           <img src="'.traeLogo($connV).'" alt="'.$empresa['nombre_empresa'].'" title="'.$empresa['nombre_empresa'].'" class="asBlock" />';

    $faHtml .='        </div>';
    $faHtml .='        <div id="tipoComp"><strong>PED</strong><div id="leyenda">[Documento no válido como factura]</div></div>';
    $faHtml .='        <div id="derecha">';
    $faHtml .='               <ul class="datoComprobante">';
    $faHtml .='                   <li class="destacado"><strong>PEDIDO</strong></li>';
    $faHtml .='                   <li class="destacado"><strong>Nro: '.$fact->NroComprobante.'</strong></li>';
    $faHtml .='                   <li><strong>Fecha: </strong>'.$fact->Fecha.'</li>';
    $faHtml .='                   <li><strong>Usuario: </strong> '.$fact->facturador.'</li>';
    $faHtml .='                   <li><strong>Vendedor: </strong> '.$fact->vendedor.'</li>';
    $faHtml .='                   <li><strong>Venc.: </strong>'.$fact->Vencimiento.'</li>';

    $faHtml .='               </ul>';
    $faHtml .='        </div>';
    $faHtml .='        </div>';

    $faHtml .='    <div id="membrete">';
    $faHtml .='       <div class="columna">';
    $faHtml .='               <ul class="datoComprobante">';
    $faHtml .='                   <li class="destacado"><strong>'.$_SESSION['nombre_empresa'].'</strong></li>';
    $faHtml .='                   <li>'.$_SESSION['domicilio_empresa'].'</li>';
    $faHtml .='                   <li>Tel: '.$_SESSION['telefono_empresa'].'</li>';
    $faHtml .='                   <li>E-mail: '.$_SESSION['email_empresa'].'</li>';
    $faHtml .='                   <li>IVA: '.$_SESSION['iva_empresa'].'</li>';
    $faHtml .='               </ul>';
    $faHtml .='       </div>';
    $faHtml .='       <div class="columna">';
    $faHtml .='               <ul class="datoComprobante">';
    $faHtml .='                   <li>&nbsp;</li>';
    $faHtml .='                   <li>CUIT: '.$_SESSION['cuit_empresa'].'</li>';
    $faHtml .='                   <li>Inic. Act.: '.  $_SESSION['iniact_empresa'].'</li>';
    $faHtml .='                   <li>Ing. Brutos: '.$_SESSION['ingbrutos_empresa'].'</li>';
    $faHtml .='               </ul>';
    $faHtml .='       </div>';
    $faHtml .='    </div>';  
    $faHtml .='    <div id="datosCliente">';
    $faHtml .='       <div class="columna">';

    $faHtml .='               <ul class="datoComprobante">';
    $faHtml .='                   <li><strong>Cliente: </strong>'.$fact->nombre_cliente.'</li>';
    $faHtml .='                   <li><strong>Domicilio: </strong>'.$fact->Calle.' Nº'. $fact->NroCalle .' depto :'.$fact->Dpto.   '</li>';
    $faHtml .='                   <li><strong>IVA: </strong>'.$fact->iva_cliente.'</li>';
    $faHtml .='                   <li><strong>Cuit: </strong>'.$fact->CUIT.'</li>';
    $faHtml .='                   <li><strong>Cond.Venta: </strong>'.$fact->CondVenta.'</li>';
    $faHtml .='               </ul>';
    $faHtml .='       </div>';
    $faHtml .='       <div class="columna">';
    $faHtml .='               <ul class="datoComprobante">';
    $faHtml .='                   <li><strong>Depósito despacho: </strong>'.$fact->NombreDeposito.'</li>';
    $faHtml .='                   <li><strong>Fecha entrega: </strong>'.$fact->FechaEntrega.'</li>';
    $faHtml .='                   <li><strong>Forma entrega: </strong>'.$fact->FormaEntrega.'</li>';
    $faHtml .='                   <li><strong>Transporte: </strong>'.$fact->nombre_transporte.'</li>';
    $faHtml .='                   <li><strong>Repartidor: </strong>'.$fact->repartidor.'</li>';

    $faHtml .='               </ul>';
    $faHtml .='       </div>';
    $faHtml .='    </div>';  
    $faHtml .='   <div id="cuerpoComprobante">';
    $faHtml .='        <table id="tablaComp" >';
    $faHtml .='            <thead>';
    $faHtml .='                <tr>';
    $faHtml .='                    <th>Cant</th>';
    $faHtml .='                    <th>Cod.</th>';
    $faHtml .='                    <th>Descripcion</th>';
    $faHtml .='                    <th>Cant E</th>';
    $faHtml .='                    <th>Cant P</th>';
    $faHtml .='                    <th>P x U Lista</th>';
    //$faHtml .='                    <th>%Alic</th>';
    //$faHtml .='                    <th>Imp. Desc</th>';
    $faHtml .='                    <th>%Desc.</th>';
    $faHtml .='                    <th>P x U</th>';
    $faHtml .='                    <th>Total</th>';
    //$faHtml .='                    <th>Nº Presup.</th>';
    //$faHtml .='                    <th>IVA</th>';
    //$faHtml .='                    <th>Tipo IVA</th>';
    //$faHtml .='                    <th>Prom.</th>';                  
    $faHtml .='                </tr>';
    $faHtml .='            </thead>';
    $faHtml .='            <tbody>';
    $cantItems =0;
    foreach($renglones as $renglon):
        $cantItems++;
        $faHtml .='<tr>';
        $faHtml .='<td class="derecha">'. $renglon->Salida.'</td>';
        $faHtml .='<td class="izquierda">'.$renglon->IDArt .'</td>';
        if($renglon->promocion=="Si"){
            $faHtml .='<td class="izquierda">* '.$renglon->Descripcion.'</td>';
        }else{
            $faHtml .='<td class="izquierda">'.$renglon->Descripcion.'</td>';
        }
        $faHtml .='<td class="derecha">'. $renglon->cantEntregada.'</td>';
        $faHtml .='<td class="derecha">'. $renglon->cantidad_pendiente.'</td>';
        $faHtml .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxU,4).'</td>';
        $faHtml .='<td class="derecha">'. $renglon->PorDesc.'</td>';
        $faHtml .='<td class="derecha">$'. number_format($renglon->PrecioVentaxU,4).'</td>';

        //$faHtml .='                    <td class="derecha">'. $renglon->Alicuota.'</td>';
        //$faHtml .='                    <td class="derecha">'. $renglon->ImpDesc.'</td>';

        //$faHtml .='                    <td class="derecha">$'.$renglon->PrecioNetoxR.'</td>';
        //$faHtml .='                    <td class="izquierda">'.$renglon->NroPresupuesto.'</td>';
        $faHtml .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxR,4).'</td>';
        //$faHtml .='                    <td class="derecha">'. $renglon->TipoIVA.'</td>';
    //    $faHtml .='<td class="derecha">'. $renglon->promocion.'</td>';
        $faHtml .='</tr>';
    endforeach;
    $faHtml .='               </tbody>';
    $faHtml .='           </table>';
    $faHtml .='   </div>';
    $faHtml .='   <div id="pieComprobante">';
    $faHtml .='       <div id="detalle">';
    $faHtml .='       <p><strong>Cant Items: '.$cantItems.' </strong> </p>';
    $faHtml .='       <p><strong>Detalle: </strong> '.$fact->Detalle.'</p>';
    $faHtml .='       <p><strong>Observ: </strong> '.$fact->detalle_comprobante.' </p>';
    $faHtml .='       <p><strong> <u>* Articulo en promoción </u></strong> </p>';
    $faHtml .='       <p><strong>Son Pesos: '.$fact->ImporteVentaL.' </strong> </p>';
    $faHtml .='       </div>';
    $faHtml .='       <div id="importe">';
    $faHtml .='           <table>';
    $faHtml .='               <tr>';
    $faHtml .='                   <td colspan="2" class="izquierda">Neto:</td><td class="derecha">'.$fact->SubTotalGral.'</td>';
    $faHtml .='               </tr>';
    $faHtml .='               <tr>';
    $faHtml .='                   <td colspan="2" class="izquierda">Descuento '.number_format($fact->PorDesc1,2).'%: </td><td class="derecha">'.$fact->ImpDesc1.'</td>';
    $faHtml .='               </tr>';
    $faHtml .='               <tr>';
    $faHtml .='                   <td colspan="2" class="izquierda">Exento:</td><td class="derecha">'.$fact->Exento.'</td>';
    $faHtml .='               </tr>';
    $faHtml .='               <tr>';
    $faHtml .='                   <td  class="izquierda">IVA:</td><td class="derecha">21,00</td><td class="derecha">'.$fact->IVA1.'</td>';
    $faHtml .='               </tr>';
    $faHtml .='               <tr>';
    $faHtml .='                   <td  class="izquierda">IVA:</td><td class="derecha">10.50</td><td class="derecha">'.$fact->IVA2.'</td>';
    $faHtml .='               </tr>';
    $faHtml .='               <tr>';
    $faHtml .='                   <td  class="izquierda">Percep IIBB Mza:</td><td class="derecha"></td><td class="derecha">'.$fact->total_percep.'</td>';
    $faHtml .='               </tr>';
    $faHtml .='               <tr>';
    $faHtml .='                   <td colspan="3" class="separador">&nbsp;</td>';
    $faHtml .='               </tr>';
    $faHtml .='               <tr >';
    $faHtml .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.($fact->Total+$fact->total_percep).'</td>';
    $faHtml .='               </tr>';

    $faHtml .='           </table>';

    $faHtml .='       </div>';
    $faHtml .='       <div id="final">';
    $faHtml .='           <div id="final-der">';
//    $faHtml .='               <img src="_img/logo_afip.jpg">';
//    $faHtml .='               <img src="'.$co.'">';
    $faHtml .='           </div>';
    $faHtml .='           <div id="final-izq">';
    $faHtml .='               <p>Comprobante generado por: ';
    $faHtml .='               <img src="sistema/_img/logo_administranet_chico.gif"></p>';
    $faHtml .='               <p>Tel:(0261)- 4274480 / 4283071 |  <a href="http://www.administranet.com.ar">www.administranet.com.ar</a></p>';
    $faHtml .='           </div>';    
    $faHtml .='       </div>';
    $faHtml .='   </div>';
    $faHtml .='</div>';
    return $faHtml;
}




/*
 * trae_rem_movil
 * @empresa:= array()
 * @codMov:= int
 * @link:= dblink
 * @db:= dbase
 */
function trae_rem_movil($empresa,$codMov,$link,$db){
    mysqli_select_db($link,$db);
    mysqli_set_charset($link,'utf8');
    
    // recupero los datos del comprobante
    $sqlPedido="SELECT 
                        comp_ped.CodigoMovimiento,
                        comp_ped.id_comp_ped AS id,
                        DATE_FORMAT(comp_ped.Fecha,'%d/%m/%Y') AS Fecha,
                        comp_ped.NroComprobante,
                        comp_ped.SubTotalDesc,
                        comp_ped.IVA1,
                        comp_ped.IVA2,
                        comp_ped.Exento,
                        comp_ped.CondVenta,
                        DATE_FORMAT(comp_ped.FechaEntrega,'%d/%m/%Y') AS FechaEntrega,
                        comp_ped.FormaEntrega,
                        comp_ped.Estado,
                        viajantes.Nombre AS Vendedor,
                        comp_ped.ImporteVenta,
                        comp_ped.ImporteVentaL,
                        comp_ped.SubTotalGral,
                        comp_ped.SubTotal1,
                        comp_ped.SubTotal2,
                        comp_ped.SubTotalDesc1,
                        comp_ped.SubTotalDesc2,
                        comp_ped.total_percep,
                        comp_ped.PorDesc1,
                        comp_ped.ImpDesc1,
                        transporte.nombre_transporte,
                        DATE_FORMAT(comp_ped.Vencimiento,'%d/%m/%Y') AS Vencimiento,
                        CONCAT(usuarios.nombre_usuario,' ',usuarios.apellido_usuario) AS repartidor,
                        cliente.nombre_cliente,
                        cliente.Calle,
                        cliente.NroCalle,
                        cliente.Dpto,
                        cliente.CUIT,
                        Contribuyentes.IVA as iva_cliente,
                        comp_ped.PorDesc1,
                        comp_ped.ImpDesc1,
                        rp.detalle_comprobante,
                        comp_ped.Detalle,
                        (comp_ped.IVA1+
                        comp_ped.IVA2)AS IVA,
                        (comp_ped.SubTotalDesc+
                        comp_ped.IVA1+
                        comp_ped.IVA2) AS Total,
                        comp_ped.id_deposito_despacho,
                        deposito.NombreDeposito,
                        cc.TipoComprobante AS tipoFact,
                        cc.NroComprobante AS nroFact
                FROM 
                    comp_ped
                    LEFT JOIN cliente ON cliente.Codigo = comp_ped.Codigo    
                    LEFT JOIN usuarios ON comp_ped.id_repartidor = usuarios.id_usuario
                    LEFT JOIN viajantes ON comp_ped.CodViajante = viajantes.CodViajante
                    LEFT JOIN contribuyentes ON contribuyentes.idIVA = cliente.idIVA
                    LEFT JOIN transporte ON transporte.id_transporte = comp_ped.id_transporte
                    LEFT JOIN deposito ON deposito.CodDeposito = comp_ped.id_deposito_despacho
                    LEFT JOIN reporte_comprobante AS rp ON (rp.nombre_reporte_comprobante = comp_ped.TipoComprobante AND rp.id_sucursal = comp_ped.CodSucursal AND rp.id_punto_venta = comp_ped.id_pv)   
                    LEFT JOIN rem_fact AS rm On rm.CodigoMovimientoR=comp_ped.CodigoMovimiento
                    LEFT JOIN cuentacliente AS cc ON cc.CodigoMovimiento=rm.CodigoMovimientoF
                WHERE  
                 comp_ped.CodigoMovimiento=".$codMov." 

                ORDER BY comp_ped.id_comp_ped";

    $hacerPed = mysqli_query($link,$sqlPedido) or die('No puedo recuperar el pedido'.mysqli_error($link).'<br>'.$sqlPedido);
    $remito = mysqli_fetch_object($hacerPed);
    $renglones = array();
    $sqlRenglon="SELECT     stock.IDArt,
                            stock.CodigoArticulo,
                            stock.Descripcion,
                            stock.Salida,
                            stock.PrecioVentaxU,
                            stock.PrecioCostoxU,
                            stock.PrecioIVAxU,
                            stock.PrecioBrutoxU,
                            stock.ImpDesc,
                            stock.PorDesc,
                            stock.PrecioVentaxR,
                            stock.PrecioCostoxR,
                            stock.PrecioIVAxR,
                            stock.PrecioBrutoxR,
                            stock.PrecioNetoxR,
                            iva.Alicuota AS Alicuota,
                            stock.CodDeposito,
                            stock.TipoIVA,
                            stock.impuesto_interno,
                            stock.impuesto_interno_subtotal,
                            stock.id_manual,
                            stock.NroPresupuesto,
                            stock.NroPedido,
                            stock.TipoIVA,
                            stock.promocion,
                            lote.cod_lote AS Lote,
                            lote.fecha_vto_lote AS FechaLote
                          FROM stock
                          LEFT JOIN iva ON stock.Alicuota = iva.ID
                          LEFT JOIN lote ON lote.id_lote = stock.id_lote
                          WHERE stock.CodigoMovimiento=".$remito->CodigoMovimiento;

    $hacerRenglon = mysqli_query($link,$sqlRenglon) or die('no puedo recuperar el renglon'.mysqli_error($link));
    while($renglon=  mysqli_fetch_object($hacerRenglon)){
        $renglones[]=$renglon;
    }
  
    /*
     * Defino contacto responsable TANKITO
     */
    $contacto="";
    $documento="";
    $arrC=explode(" - ",$remito->Detalle);
    if(is_array($arrC)){
        $contacto=$arrC[0];
        $documento=$arrC[1];
    }
    
    
    /*
     * Dibujo del comprobante.
     */

    $muestraR = "";        
    $muestraR .='    <div id="comprobante">';
    $muestraR .='    <div id="cabeceraComprobante">';
    //$muestraR .='    <input type="image" src="_img/imprimir.png" id="imprimir" value="Imprimir">';
    $muestraR .='        <div id="izquierda">';
    $muestraR .='           <img src="'.traeLogo($link,null).'" alt="'.$empresa->nombre_empresa.'" title="'.$empresa->nombre_empresa.'" class="asBlock" />';

    $muestraR .='        </div>';
    $muestraR .='        <div id="tipoComp"><strong>REM</strong><div id="leyenda">[Documento no válido como factura]</div></div>';
    $muestraR .='        <div id="derecha">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li class="destacado"><strong>REMITO</strong></li>';
    $muestraR .='                   <li class="destacado"><strong>Nro: '.$remito->NroComprobante.'</strong></li>';
    $muestraR .='                   <li><strong>Fecha: </strong>'.$remito->Fecha.'</li>';
    //$muestraR .='                   <li><strong>Usuario: </strong> '.$objVendedor->nombre_usuario .' ' .$objVendedor->apellido_usuario.'</li>';
    $muestraR .='                   <li><strong>Vendedor: </strong> '.$remito->Vendedor.'</li>';
    $muestraR .='                   <li><strong>Venc.: </strong>'.$remito->Vencimiento.'</li>';

    $muestraR .='               </ul>';
    $muestraR .='        </div>';
    $muestraR .='        </div>';

    $muestraR .='    <div id="membrete">';
    $muestraR .='       <div class="columna">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li class="destacado"><strong>'.$empresa->nombre_empresa.'</strong></li>';
    $muestraR .='                   <li>Tel: '.$empresa->telefono_empresa.'</li>';
    $muestraR .='                   <li>E-mail: '.$empresa->email_empresa.'</li>';
    $muestraR .='                   <li>IVA: '.$empresa->iva_empresa.'</li>';
    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='       <div class="columna">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li>&nbsp;</li>';
    $muestraR .='                   <li>CUIT: '.$empresa->cuit_empresa.'</li>';
    $muestraR .='                   <li>Inic. Act.: '.  $empresa->iniact_empresa.'</li>';
    $muestraR .='                   <li>Ing. Brutos: '.$empresa->ingbrutos_empresa.'</li>';
    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='    </div>';  
    $muestraR .='    <div id="datosCliente">';
    $muestraR .='       <div class="columna">';

    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li><strong>Cliente: </strong>'.$remito->nombre_cliente.'</li>';
    $muestraR .='                   <li><strong>Domicilio: </strong>'.$remito->Calle.' Nº'. $remito->NroCalle .' depto :'.$remito->Dpto.   '</li>';
    $muestraR .='                   <li><strong>IVA: </strong>'.$remito->iva_cliente.'</li>';
    $muestraR .='                   <li><strong>Cuit: </strong>'.$remito->CUIT.'</li>';
    $muestraR .='                   <li><strong>Cond.Venta: </strong>'.$remito->CondVenta.'</li>';
    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='       <div class="columna">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li><strong>Factura: </strong>'.$remito->tipoFact.' '.$remito->nroFact.'</li>';
    $muestraR .='                   <li><strong>Depósito despacho: </strong>'.$remito->NombreDeposito.'</li>';
    $muestraR .='                   <li><strong>Fecha entrega: </strong>'.$remito->FechaEntrega.'</li>';
    $muestraR .='                   <li><strong>Forma entrega: </strong>'.$remito->FormaEntrega.'</li>';
    $muestraR .='                   <li><strong>Transporte: </strong>'.$remito->nombre_transporte.'</li>';
    $muestraR .='                   <li><strong>Repartidor: </strong>'.$remito->repartidor.'</li>';

    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='    </div>';  
    $muestraR .='   <div id="cuerpoComprobante">';
    $muestraR .='        <table id="tablaComp" >';
    $muestraR .='            <thead>';
    $muestraR .='                <tr>';
    $muestraR .='                    <th>Cod.</th>';
    $muestraR .='                    <th>Cod. Manual</th>';
    $muestraR .='                    <th>Descripcion</th>';
//    $muestraR .='                    <th>Cant E</th>';
//    $muestraR .='                    <th>Cant P</th>';
//    $muestraR .='                    <th>P x U Lista</th>';
    //$muestraR .='                    <th>%Alic</th>';
    //$muestraR .='                    <th>Imp. Desc</th>';
//    $muestraR .='                    <th>%Desc.</th>';
//    $muestraR .='                    <th>P x U</th>';
//    $muestraR .='                    <th>Total</th>';
    //$muestraR .='                    <th>Nº Presup.</th>';
    //$muestraR .='                    <th>IVA</th>';
    //$muestraR .='                    <th>Tipo IVA</th>';
    $muestraR .='                    <th>Cantidad</th>';                  
    $muestraR .='                </tr>';
    $muestraR .='            </thead>';
    $muestraR .='            <tbody>';
    $cantItems =0;
    foreach($renglones as $renglon):
        $cantItems++;
        $muestraR .='<tr>';
        $muestraR .='<td class="derecha">'. $renglon->IDArt.'</td>';
        $muestraR .='<td class="izquierda">'.$renglon->id_manual.'</td>';
        if($renglon->promocion=="Si"){
            $muestraR .='<td class="izquierda">* '.$renglon->Descripcion.'</td>';
        }else{
            $muestraR .='<td class="izquierda">'.$renglon->Descripcion.'</td>';
        }
//        $muestraR .='<td class="derecha">'. $renglon->cantEntregada.'</td>';
//        $muestraR .='<td class="derecha">'. $renglon->cantidad_pendiente.'</td>';
//        $muestraR .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxU,4).'</td>';
//        $muestraR .='<td class="derecha">'. $renglon->PorDesc.'</td>';
//        $muestraR .='<td class="derecha">$'. number_format($renglon->PrecioVentaxU,4).'</td>';

        //$muestraR .='                    <td class="derecha">'. $renglon->Alicuota.'</td>';
        //$muestraR .='                    <td class="derecha">'. $renglon->ImpDesc.'</td>';

        //$muestraR .='                    <td class="derecha">$'.$renglon->PrecioNetoxR.'</td>';
        //$muestraR .='                    <td class="izquierda">'.$renglon->NroPresupuesto.'</td>';
//        $muestraR .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxR,4).'</td>';
        //$muestraR .='                    <td class="derecha">'. $renglon->TipoIVA.'</td>';
        $muestraR .='<td class="derecha">'. $renglon->Salida.'</td>';
        $muestraR .='</tr>';
    endforeach;
    $muestraR .='               </tbody>';
    $muestraR .='           </table>';
    $muestraR .='   </div>';
    $muestraR .='   <div id="pieComprobante">';
    $muestraR .='       <div id="detalle">';
    $muestraR .='       <p><strong>Cant Items: '.$cantItems.' </strong> </p>';
    $muestraR .='       <p><strong>Recibí conforme: </strong> '.$contacto.'</p>';
    $muestraR .='       <p><strong>Documento: </strong> '.$documento.' </p>';
//    $muestraR .='       <p><strong> <u>* Articulo en promoción </u></strong> </p>';
//    $muestraR .='       <p><strong>Son Pesos: '.$remito->ImporteVentaL.' </strong> </p>';
    $muestraR .='       <p><strong>El presente documento da conformidad de recepción de la mercadería detallada  </strong></p>';
    $muestraR .='       </div>';
    $muestraR .='       <div id="importe">';

    //$muestraR .='       <div style="width:99%;height:50px;padding-top:30px;text-align:center;"><p>------------------------------------<br>FIRMA CLIENTE</p></div>';
    $muestraR .='       <div style="width:99%;height:50px;padding-top:30px;text-align:center;"><p>'.$contacto.'<br>'.$documento.'<br>------------------------------------<br>FIRMA<br>'
                        .'</p></div>';

    $muestraR .='       </div>';
    $muestraR .='       <div id="final">';

    $muestraR .='       <p>Comprobante generado por: ';
    $muestraR .='       <img src="sistema/_img/logo-administranet-ecommerce.png" style="height:25px;"></p>';
    $muestraR .='           <p>Tel:(0261)- 4274480 / 4283071 |  <a href="https://www.administranet.com.ar">www.administranet.com.ar</a></p>';
    $muestraR .='       </div>';
    $muestraR .='   </div>';
    $muestraR .='</div>';

    /*PDF
     * ========================================================================
     */
    
    //mpdf1
    
//    require_once 'sistema/_lib/mpdf/mpdf.php';
//
//    $mpdf = new mPDF('c','A4');
//    $stylesheet = file_get_contents('sistema/_css/pdf.css');
//    $mpdf->WriteHTML($stylesheet,1);
//    $mpdf->WriteHTML($muestraR);
//    $mpdf->Output('Rem-'.$remito->NroComprobante.'.pdf','D');
//    exit; 
    
    // mpdf2
    require_once  'sistema/_lib/mpdf2/vendor/autoload.php';

    $mpdf = new \Mpdf\Mpdf(['mode' => 'c',
        'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 4,
            'margin_bottom' => 5,
            'margin_header' => 5,
            'margin_footer' => 7
        ]);


    $stylesheet = file_get_contents('sistema/_css/pdf.css');
        $mpdf->WriteHTML($stylesheet,1);
        $mpdf->WriteHTML($muestraR,2);
        $mpdf->SetDisplayMode('fullpage');
       //$mpdf->Output();
    $mpdf->Output('Rem-'.$remito->NroComprobante.'.pdf','D');
    exit(); 
}



/*
 * trae_rec_movil
 * @empresa:= array()
 * @codMov:= int
 * @link:= dblink
 * @db:= dbase
 */
function trae_rec_movil($empresa,$codMov,$link,$db){
    mysqli_select_db($link,$db);
    mysqli_set_charset($link,'utf8');    


//inicio Buscando en base de datos

    $sqlRec="SELECT                   
                                
                DATE_FORMAT(cc.Fecha,'%d/%m/%Y') AS FechaB,
                DATE_FORMAT(cc.Fecha,'%Y%m%d') AS Fecha,
                 cc.TipoComprobante,
                 cliente.nombre_cliente AS nombre_cliente,
                 cc.NroComprobante,                           
                 cc.ImporteCobro AS Importe,
                 cc.TotalEfectivoP AS Efectivo,
                 cc.TotalEfectivoD AS Dolar,
                 cc.TotalCheque As Cheque,
                 cc.TotalRetencion As Retencion,
                 cc.TotalDescRec As Descuento,
                 cc.Anulado,
                 cc.TipoRecibo,
                 cliente.CUIT,
                 cc.Codigo,
                 cc.ImporteVentaL AS importeLetra,
                 viajantes.Nombre AS cobrador,
                 caja_abm.nombre_caja  AS nombreCaja                 
                FROM cuentacliente AS cc
                LEFT JOIN cliente ON cliente.Codigo=cc.Codigo
                LEFT JOIN viajantes ON viajantes.CodViajante=cc.CodViajante 
                LEFT JOIN caja ON caja.codigo_movimiento=cc.CodigoMovimiento AND caja.tipo='Cobranza Efectivo'   
                LEFT JOIN caja_abm ON caja_abm.id_caja=caja.id_caja_abm_origen
            WHERE
                 cc.CodigoMovimiento=".$codMov;

    $hacerRec = mysqli_query($link,$sqlRec) or die('No puedo recuperar el pedido'.mysqli_error($link).'<br>'.$sqlRec);
    $recibo = mysqli_fetch_object($hacerRec);
    
    // debo saber si traigo efectivo.
   if($recibo->Efectivo!=0){
       $efectivo['monto']=$recibo->Efectivo;
       $efectivo['caja']=$recibo->nombreCaja;
   }
    // si traigo cheques
   if($recibo->Cheque!=0){
       $sqlChequeTercero=" SELECT 
                                ch.NroCheque,
                                ch.FechaEmision,
                                ch.FechaCobro,
                                DATE_FORMAT(ch.FechaEmision,'%d/%m/%Y') AS fEmision,
                                DATE_FORMAT(ch.FechaCobro,'%d/%m/%Y') AS fCobro,
                                ch.Librador,
                                ch.Importe,
                                banco.Nombre AS nombreBanco
                            FROM
                            chequetercero AS ch
                            LEFT JOIN banco ON banco.CodBanco=ch.CodBanco
                            WHERE
                            ch.CodigoMovimientoREC=".$codMov;
      $hacerCh = mysqli_query($link,$sqlChequeTercero) or die('No puedo recuperar el pedido'.mysqli_error($link).'<br>'.$sqlChequeTercero);
      $arrCheques=array();
      while($ch = mysqli_fetch_assoc($hacerCh)){
          $arrCheques[]=$ch;
      } 
   } 
   // facturas a imputar.
   if($recibo->TipoRecibo=='Imputacion'){
       $sqlImp=" SELECT 
                    impu.Fecha,
                    DATE_FORMAT(impu.Fecha,'%d/%m/%Y') as fechaB,
                    impu.TipoComprobante,
                    impu.NroComprobante,
                    impu.CondVenta,
                    impu.Importe,
                    impu.Cancelado,
                    impu.CanceladoActual,
                    impu.Saldo,
                    impu.Vencimiento,
                    DATE_FORMAT(impu.Vencimiento,'%d/%m/%Y')as vencimientoB
                FROM
                recibo_factura_par AS impu
                WHERE
                    impu.ReciboMov=".$codMov;
       $hacerImp = mysqli_query($link,$sqlImp) or die('No puedo recuperar el pedido'.mysqli_error($link).'<br>'.$sqlImp);
       $arrImpu=array();
       while($im=mysqli_fetch_assoc($hacerImp)){
           $arrImpu[]=$im;
       }
   }
   
    /*
     * Dibujo del comprobante.
     */

    $muestraR = "";        
    $muestraR .='    <div id="comprobante">';
    $muestraR .='    <div id="cabeceraComprobante">';
    //$muestraR .='    <input type="image" src="_img/imprimir.png" id="imprimir" value="Imprimir">';
    $muestraR .='        <div id="izquierda">';
    $muestraR .='           <img src="'.traeLogo($link,null).'" alt="'.$empresa->nombre_empresa.'" title="'.$empresa->nombre_empresa.'" class="asBlock" />';

    $muestraR .='        </div>';
    $muestraR .='        <div id="tipoComp"><strong>REC</strong><div id="leyenda">[Documento no válido como factura]</div></div>';
    $muestraR .='        <div id="derecha">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li class="destacadoComp"><strong>RECIBO</strong></li>';
    $muestraR .='                   <li class="destacado"><strong>Nro: '.$recibo->NroComprobante.'</strong></li>';
    $muestraR .='                   <li><strong>Fecha: </strong>'.$recibo->FechaB.'</li>';
    //$muestraR .='                   <li><strong>Usuario: </strong> '.$objVendedor->nombre_usuario .' ' .$objVendedor->apellido_usuario.'</li>';
    $muestraR .='                   <li><strong>Cobrador: </strong> '.$recibo->cobrador.'</li>';
//    $muestraR .='                   <li><strong>Venc.: </strong>'.$recibo->Vencimiento.'</li>';

    $muestraR .='               </ul>';
    $muestraR .='        </div>';
    $muestraR .='        </div>';

    $muestraR .='    <div id="membrete">';
    $muestraR .='       <div class="columna">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li class="destacado"><strong>'.$empresa->nombre_empresa.'</strong></li>';
    $muestraR .='                   <li>Tel: '.$empresa->telefono_empresa.'</li>';
    $muestraR .='                   <li>E-mail: '.$empresa->email_empresa.'</li>';
    $muestraR .='                   <li>IVA: '.$empresa->iva_empresa.'</li>';
    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='       <div class="columna">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li>&nbsp;</li>';
    $muestraR .='                   <li>CUIT: '.$empresa->cuit_empresa.'</li>';
    $muestraR .='                   <li>Inic. Act.: '.  $empresa->iniact_empresa.'</li>';
    $muestraR .='                   <li>Ing. Brutos: '.$empresa->ingbrutos_empresa.'</li>';
    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='    </div>';  
    $muestraR .='    <div id="datosCliente">';
    $muestraR .='       <div class="columna">';

    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li><strong>Cliente: </strong>'.$recibo->nombre_cliente.' - '.$recibo->Codigo.'</li>';
    $muestraR .='                   <li><strong>Cuit: </strong>'.$recibo->CUIT.'</li>';

    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='       <div class="columna">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='               </ul>';
    $muestraR .='       </div>';
    $muestraR .='    </div>';
//  EFECTIVO
//  ============================================================================        
    if(isset($efectivo)){
        $muestraR .='   <div id="cuerpoComprobante">';
        $muestraR .='       <p>Medios de cobro</p>   ';
        $muestraR .='       <p>Efectivo: <strong>$'.number_format($efectivo['monto'],2,",",".").'</strong>  Caja: <strong>'.$efectivo['caja'].'</strong></p>   ';
        $muestraR .='   </div>';    
        
    }
//  CHEQUES
//  ============================================================================        
    if(isset($arrCheques)){
        $muestraR .='   <div id="cuerpoComprobante">';
        $muestraR .='   <p>Cheques de tercero:</p>';
        $muestraR .='        <table id="tablaComp" >';
        $muestraR .='            <thead>';
        $muestraR .='                <tr>';
        $muestraR .='                    <th class="liso">Nro cheque</th>';
        $muestraR .='                    <th class="liso">Banco</th>';
        $muestraR .='                    <th class="liso">Emisión</th>';
        $muestraR .='                    <th class="liso">Cobro</th>';
        $muestraR .='                    <th class="liso">Librador</th>';
        $muestraR .='                    <th class="liso" style="text-align:right">Importe</th>';                  
        $muestraR .='                </tr>';
        $muestraR .='            </thead>';
        $muestraR .='            <tbody>';
        foreach($arrCheques as $ch){
            $muestraR .='<tr>';
            $muestraR .='<td class="derecha">'. $ch["NroCheque"].'</td>';
            $muestraR .='<td class="izquierda">'.$ch["nombreBanco"].'</td>';            
            $muestraR .='<td class="izquierda">'.$ch["fEmision"].'</td>';
            $muestraR .='<td class="izquierda">'.$ch["fCobro"].'</td>';
            $muestraR .='<td class="izquierda">'.$ch["Librador"].'</td>';
            $muestraR .='<td class="derecha">$'.number_format($ch["Importe"],2,",",".").'</td>';
            
            
            
            
            $muestraR .='</tr>';
        }
        $muestraR .='                   <tr><td colspan="5" class="derecha"><strong>Total</strong></td><td class="derecha"><strong>$'.number_format($recibo->Cheque,2,",",".").'</strong></td></tr>';
        $muestraR .='               </tbody>';
        $muestraR .='           </table>';
        $muestraR .='   </div>';
    }
    
//  IMPUTACIONES
//  ============================================================================            
    if(isset($arrImpu)){
        $muestraR .='   <div id="cuerpoComprobante">';
        $muestraR .='   <p>Imputación de comprobantes:</p>';
        $muestraR .='        <table id="tablaComp" >';
        $muestraR .='            <thead>';
        $muestraR .='                <tr>';
        $muestraR .='                    <th class="liso">Fecha</th>';
        $muestraR .='                    <th class="liso">Comp</th>';
        $muestraR .='                    <th class="liso">Nro Comp</th>';
        $muestraR .='                    <th class="liso">Cond Venta</th>';
        $muestraR .='                    <th class="liso">Vencimiento</th>';
        $muestraR .='                    <th class="liso" style="text-align:right">Importe</th>';  
        $muestraR .='                    <th class="liso" style="text-align:right">Canc Total</th>';
        $muestraR .='                    <th class="liso" style="text-align:right">Canc Actual</th>';
        $muestraR .='                    <th class="liso" style="text-align:right">Saldo</th>';
        $muestraR .='                    <th class="liso">Detalle</th>';
        $muestraR .='                </tr>';
        $muestraR .='            </thead>';
        $muestraR .='            <tbody>';
        foreach($arrImpu as $f){
            $muestraR .='<tr>';
            $muestraR .='<td class="izquierda">'. $f["fechaB"].'</td>';
            $muestraR .='<td class="izquierda">'.$f["TipoComprobante"].'</td>';            
            $muestraR .='<td class="izquierda">'.$f["NroComprobante"].'</td>';
            $muestraR .='<td class="izquierda">'.$f["CondVenta"].'</td>';
            $muestraR .='<td class="izquierda">'.$f["vencimientoB"].'</td>';
            $muestraR .='<td class="derecha">$'.number_format($f["Importe"],2,",",".").'</td>';
            $muestraR .='<td class="derecha">$'.number_format($f["Cancelado"],2,",",".").'</td>';
            $muestraR .='<td class="derecha">$'.number_format($f["CanceladoActual"],2,",",".").'</td>';
            $muestraR .='<td class="derecha">$'.number_format($f["Saldo"],2,",",".").'</td>';
            $detalle="Comp Imputado";
            if($f["TipoComprobante"]=="REC"){
                $detalle="A favor cliente";
            }
            $muestraR .='<td class="izquierda">'.$detalle.'</td>';
            
            $muestraR .='</tr>';
        }
       
        $muestraR .='               </tbody>';
        $muestraR .='           </table>';
        $muestraR .='   </div>';
    }
    
    
    
    $muestraR .='   <div id="pieComprobante">';
    $muestraR .='       <div id="detalle">';   
    $muestraR .='       <p id="importeLetra"><strong>Son Pesos: '.$recibo->importeLetra.' </strong> </p>';
    $muestraR .='       <div style="width:99%;height:50px;padding-top:30px;text-align:center;"><p>------------------------------------<br>FIRMA CLIENTE</p></div>';
    $muestraR .='       <div style="width:99%;height:50px;padding-top:30px;text-align:center;"><p>------------------------------------<br>FIRMA RESPONSABLE<br>'
                        .'</p></div>';
    
    $muestraR .='       </div>';
    $muestraR .='       <div id="importe">';
    $muestraR .='           <table style="margin-top:40%">';
    $muestraR .='               <tr>';
    $muestraR .='                   <td colspan="2" class="izquierda">Descuento</td><td class="derecha">'.number_format($recibo->Descuento,2,",",".").'</td>';
    $muestraR .='               </tr>';
    $muestraR .='               <tr>';
    $muestraR .='                   <td colspan="2" class="izquierda">Retenciones </td><td class="derecha">'.number_format($recibo->Retencion,2,",",".").'</td>';
    $muestraR .='               </tr>';

    $muestraR .='               <tr >';
    $muestraR .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.number_format($recibo->Importe,2,",",".").'</td>';
    $muestraR .='               </tr>';

    $muestraR .='           </table>';
    

    $muestraR .='       </div>';
    $muestraR .='       <div id="final">';

    $muestraR .='       <p>Comprobante generado por: ';
    $muestraR .='       <img src="sistema/_img/logo-administranet-ecommerce.png" style="height:25px;"></p>';
    $muestraR .='           <p>Tel:(0261)- 4274480 / 4283071 |  <a href="http://www.administranet.com.ar">www.administranet.com.ar</a></p>';
    $muestraR .='       </div>';
    $muestraR .='   </div>';
    $muestraR .='</div>';

    /*PDF
     * =========================================================================
     */

    
   
    require_once  'sistema/_lib/mpdf2/vendor/autoload.php';
//print $muestraR;
    $mpdf = new \Mpdf\Mpdf(['mode' => 'c',
        'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 4,
            'margin_bottom' => 5,
            'margin_header' => 5,
            'margin_footer' => 7
        ]);


    $stylesheet = file_get_contents('sistema/_css/pdf.css');
        $mpdf->WriteHTML($stylesheet,1);
        $mpdf->WriteHTML($muestraR,2);
        $mpdf->SetDisplayMode('fullpage');
    $mpdf->Output('Rec-'.$recibo->NroComprobante.'.pdf','D');
    
    exit(); 
    
}



/*  
 * traeLogo con CUIT RUTA o nombre
 * @link:= db link
 * @ruta:= ruta foto
 * @queCuit:= cuit de empresa
 */
 
function traeLogo($link,$ruta=null,$queCuit=null){
    
    
    if($ruta==null){
        $fileName ="_img/logo_".$queCuit.".jpg";
    }else{
        $fileName =$ruta."/logo_".$queCuit.".jpg";
    }
    // no existe lo genero
    if(!file_exists($fileName)){
        $query = "SELECT 
                        logo AS Foto,
                        'image/pjpeg' AS Tipo 
            FROM configuracion;";

            $sal=mysqli_query($link,$query);
            $fila=mysqli_fetch_array($sal);    
            $foto = imagejpeg(imagecreatefromstring($fila["Foto"]), $fileName) ;
    }
//    $logo=fopen($fileName,"w");
//    fwrite($logo, $foto);
//    fclose($logo);
    //file_put_contents($fileName, $logo);
    return $fileName;
    
}



/*
 * logo base 64
 */
function traeLogo64($link){
    $query = "SELECT 
                    logo AS Foto,
                    'image/pjpeg' AS Tipo 
            FROM configuracion;";
    $sal=mysqli_query($link,$query)or die("no anduvo".mysqli_error($link));
    $fila=mysqli_fetch_array($sal);
    
    
//    if($ruta==null){
//        $fileName ="sistema/_img/logototal.png";
//    }else{
//        $fileName =$ruta."/logototal.png";
//    }
    $compressed = base64_encode($fila["Foto"]);
    $fileName="data:image/pjpeg;base64,".$compressed;
//    $12$data = base64_encode(file_get_contents( $_FILES["fileToUpload"]["tmp_name"] ));
//				echo "copy + paste the data below, use it as a string in ur JavaScript Code<br><br>";
//				echo "<textarea id='data' style=''>data:".$check["mime"].";base64,".$data."</textarea>";
    //    $foto = imagepng(imagecreatefromstring($fila["Foto"]), $fileName) ;
//    $logo=fopen($fileName,"w");
//    fwrite($logo, $foto);
//    fclose($logo);
    //file_put_contents($fileName, $logo);
    return $fileName;
    
}



/* function recuperar el codigo de barra electronico.
 * =============================================================================
 */
function trae_cod_barra($fact,$cuit){
//    echo "barra<pre>";
//    echo print_r($fact);
//    echo "</pre>";
    
    $nroCodBarra= generar_nro_cod_barra($fact,$cuit);
//    echo "barra{<pre>";
//    echo $nroCodBarra;
//    echo "</pre>}";
    $url="sistema/_img/barcode/".$nroCodBarra.".png";
    // si la foto existe la muestro
    
    if(file_exists($url)){
        $foto='<img src="'.$url.'" style="width:250px;">';
    }else{
        // la imagen no existe... la creo.
        genera_imagen_barra($nroCodBarra);
        $foto='<img src="'.$url.'" style="width:250px;">';
    }
    
    
    //preguntar si la imagen existe...sino..
    //
    // generar un codigo de barra con los codigos de generar codbarra
    //$foto='<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($nroCodBarra, $generator::TYPE_CODE_128_B)) . '"><label>'.$nroCodBarra.'</label>';
    
    return $foto;
}
/* funcion que creo la imagen del codigo de barra*/
function genera_imagen_barra($nroCodBarra){
    $url="http://www.barcodes4.me/barcode/c128b/".$nroCodBarra.".png?IsTextDrawn=1&width=400&height=100";
    $destino="sistema/_img/barcode/".$nroCodBarra.".png";
    //$im=curl_init($url);
    
    
//    $ch = curl_init ($url);
//    curl_setopt($ch, CURLOPT_HEADER, 0);
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
//    $rawdata=curl_exec($ch);
//    curl_close ($ch);
//
//    $fp = fopen($destino,'x');
//    fwrite($fp, $rawdata);
//    fclose($fp); 
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    $picture = curl_exec($ch);
    curl_close($ch);
    //Display the image in the browser
    //header('Content-type: image/png');
    //echo $picture;
    $fp = fopen($destino,'x');
    fwrite($fp, $picture);
    fclose($fp);    
    
}


/*
 * funcion para generar el numero del codigo de barra.
 */

function generar_nro_cod_barra($fact,$cuit){
    $tipoF=array("A"=>"01","B"=>"06","M"=>"51","C"=>"11","E"=>"19");
    $tipoComp= $tipoF[$fact->tipoFactura]; 
    $cuitCodBarra= str_replace("-", "", $cuit); 
    $puntoVenta=$fact->nro_punto_venta; 
    $caeFactura=$fact->fe_cae;  
    $caeVto=$fact->vtoCaeN; 
    
    $puntoVentaCero= str_pad($puntoVenta, 4, "0", STR_PAD_LEFT);
    
   
    //Armo el Nro Completo
    $digito = $cuitCodBarra.$tipoComp.$puntoVentaCero.$caeFactura.$caeVto;
    
    //Llamo a la funcion para que calcule el digito Verificado
    $verif = verificador($digito);
    
    //DV = Verif




    return $digito.$verif;
    
    
    
}

function verificador($texto){    
    $nume=0;
    $listapar="";
    $listaimpar="";
    $par = 0;
    $impar = 0;
    $largo = strlen($texto);
//    echo "largo{".$largo."}<br>";
    For($i=1;$i<=$largo;$i++){
        //$pos = $i Mod 2
//        echo "analizis{".var_dump(($i-1)%2)."}<br>";
        $pos=$i%2;
//        echo "paridad:{".$pos."}<br>";
        //$nume = Mid($texto, $i, 1);
        $nume=intval(substr($texto,($i-1),1));
        //saber si es par o impar
//        echo "numer:{".$nume.":".$pos."-".($i-1)."}<br>";
        If($pos == 0){
            $listapar = $listapar.$nume.",";
            $par = $par + $nume;

        }else{
            $listaimpar = $listaimpar.$nume.",";
            $impar = $impar + $nume;
        }

        
    //Next $i
    }
//    echo "listapar{".$listapar."}<br>";
//    echo "listaImpar{".$listaimpar."}<br>";
    $impar = $impar * 3;
    $total = $par+ $impar;
    //buscar el menor numero que sumado al total me de multiplo de 10
    $menora = "";
    $menor = "";
    For($i = 1; $i<=9;$i++){

        $evalu = ($total + $i)%10;
        If($i == 1){ 
            If($evalu == 0){
                $menor = $i;
                $menora = $i;
            }

        }Else{
            If($evalu == 0){
                $menor = $i;
                    
                If ($menor < $menora){
                    $menora = $menor;
                }
            }
        }
    }
    return $menor;
}

/*
 * NOTA DE CREDITO
 * =============================================================================
 */

function trae_nota_credito_movil($empresa,$codMov,$link,$db){
    if(is_object($empresa)){
       $empresa= (array) $empresa;
   }
    mysqli_select_db($link,$db);
    mysqli_set_charset($link,'utf8');
    if(isset($codMov)){                              
               // $codMov=$_GET["codigomovimiento"];
                
                $sqlPedido="SELECT 
                                CASE 
                                    WHEN cc.TipoComprobante='NCA' THEN 'A'
                                    WHEN cc.TipoComprobante='NCB' THEN 'B'
                                    WHEN cc.TipoComprobante='NCC' THEN 'C'
                                    WHEN cc.TipoComprobante='NCM' THEN 'M'
                                    WHEN cc.TipoComprobante='NCE' THEN 'E'
                                END AS tipoNcredito,
                                cc.TipoNC,
                                    cc.CodigoMovimiento,
                                    cc.id_cuentacliente AS id,
                                    DATE_FORMAT(cc.Fecha,'%d/%m/%Y') AS Fecha,
                                    cc.NroComprobante,
                                    cc.SubTotalDesc,
                                    cc.IVA1,
                                    cc.IVA2,
                                    cc.Alicuota1,
                                    cc.Alicuota2,
                                    cc.Exento,
                                    cc.CondVenta,
                                    cc.Estado,
                                    viajantes.Nombre AS Vendedor,
                                    cc.ImporteVenta,
                                    cc.ImporteVentaL,
                                    cc.SubTotalGral,
                                    cc.SubTotal1,
                                    cc.SubTotal2,
                                    cc.SubTotalDesc1,
                                    cc.SubTotalDesc2,
                                    cc.total_percep,
                                    cc.PorDesc1,
                                    cc.ImpDesc1,                                    
                                    DATE_FORMAT(cc.Vencimiento,'%d/%m/%Y') AS Vencimiento,
                                    CONCAT(usuarios.nombre_usuario,' ',usuarios.apellido_usuario) AS facturador,
                                    cliente.nombre_cliente,
                                    cliente.Calle,
                                    cliente.NroCalle,
                                    cliente.Dpto,
                                    cliente.CUIT,
                                    Contribuyentes.IVA as iva_cliente,
                                    cc.PorDesc1,
                                    cc.ImpDesc1,
                                    rp.detalle_comprobante,
                                    cc.Detalle,
                                    (cc.IVA1+
                                    cc.IVA2)AS IVA,
                                    (cc.SubTotalDesc+
                                    cc.IVA1+
                                    cc.IVA2) AS Total,
                                    cc.id_deposito_despacho,
                                    deposito.NombreDeposito,
                                    cc.fe_cae,
                                    DATE_FORMAT(cc.fe_vto_cae,'%d/%m/%Y') AS vtoCae,
                                    DATE_FORMAT(cc.fe_vto_cae,'%Y%m%d') AS vtoCaeN,
                                    cc.fe_comp,
                                    pv.nro_punto_venta,
                                    cc.NroFactura,
                                     GROUP_CONCAT(CONCAT(percep_cli_tipo.nombre_percep_cli_tipo,' = $', percep_cli.importe_percep_cli) SEPARATOR ' - ') AS detPercep
                            FROM 
                                cuentacliente AS cc
                                LEFT JOIN cliente ON cliente.Codigo = cc.Codigo
                                LEFT JOIN punto_venta AS pv ON pv.id_punto_venta=cc.id_pv
                                LEFT JOIN usuarios ON cc.IdUsuario = usuarios.id_usuario
                                LEFT JOIN viajantes ON cc.CodViajante = viajantes.CodViajante
                                LEFT JOIN contribuyentes ON contribuyentes.idIVA = cliente.idIVA
                               
                                LEFT JOIN deposito ON deposito.CodDeposito = cc.id_deposito_despacho
                            
                                LEFT JOIN reporte_comprobante AS rp ON (rp.nombre_reporte_comprobante = cc.TipoComprobante AND rp.id_sucursal = cc.CodSucursal AND rp.id_punto_venta = cc.id_pv)
                                LEFT JOIN percep_cli ON percep_cli.codigo_movimiento=cc.CodigoMovimiento
 				LEFT JOIN percep_cli_tipo  ON  percep_cli.id_percep_cli_tipo = percep_cli_tipo.id_percep_cli_tipo   
                            
                            WHERE  
                             cc.CodigoMovimiento=".$codMov."
                                
                            ORDER BY cc.id_cuentacliente;";
//                echo "<pre>";
//                echo $sqlPedido;
//                echo "</pre>";
                $hacerPed = mysqli_query($link,$sqlPedido) or die('No puedo recuperar la factura'.mysqli_error($link).'<br><pre>'.$sqlPedido.'</pre>');
                $notaCredito = mysqli_fetch_object($hacerPed);
                $renglones = array();
                /* NOTA DE CREDITO X CONCEPTO
                 * =============================================================
                 */
                if($notaCredito->TipoNC=="Concepto"){
                     //rs_stock.Open "SELECT * FROM nc_concepto WHERE CodigoMovimiento = " & DataConsulta.Recordset.Fields!CodigoMovimiento
                     $sqlRenglon="SELECT * FROM nc_concepto WHERE nc_concepto.CodigoMovimiento=".$notaCredito->CodigoMovimiento;    
                     $hacerRenglon = mysqli_query($link,$sqlRenglon) or die('no puedo recuperar el renglon'.mysqli_error($link));
                     while($renglon=  mysqli_fetch_object($hacerRenglon)){
                        $renglones[]=$renglon;
                    }
                }
                
                if($notaCredito->TipoNC=="Descuento" || $notaCredito->TipoNC=="Importe"){
                    
//                    while($renglon=  mysqli_fetch_object($hacerRenglon)){
//                        $renglones[]=$renglon;
//                    }
                }
                
                
                if($notaCredito->TipoNC=="Devolucion"){
                    $sqlRenglon="SELECT     stock.IDArt,
                                            stock.CodigoArticulo,
                                            stock.Descripcion,
                                            stock.Salida,
                                            stock.Cantidad,
                                            stock.PrecioVentaxU,
                                            stock.PrecioCostoxU,
                                            stock.PrecioIVAxU,
                                            stock.PrecioBrutoxU,
                                            stock.ImpDesc,
                                            stock.PorDesc,
                                            stock.PrecioVentaxR,
                                            stock.PrecioCostoxR,
                                            stock.PrecioIVAxR,
                                            stock.PrecioBrutoxR,
                                            stock.PrecioNetoxR,
                                            iva.Alicuota AS Alicuota,
                                            stock.CodDeposito,
                                            stock.TipoIVA,
                                            stock.impuesto_interno,
                                            stock.impuesto_interno_subtotal,
                                            stock.id_manual,
                                            stock.NroPresupuesto,
                                            stock.TipoIVA,
                                            stock.promocion
                                          FROM stock
                                          LEFT JOIN iva ON stock.Alicuota = iva.ID
                                          WHERE stock.CodigoMovimiento=".$notaCredito->CodigoMovimiento;

                    $hacerRenglon = mysqli_query($link,$sqlRenglon) or die('no puedo recuperar el renglon'.mysqli_error($link));
                    while($renglon=  mysqli_fetch_object($hacerRenglon)){
                        $renglones[]=$renglon;
                    }
                }
//                echo '<pre>'.print_r($renglones).'</pre>';
//                echo '<pre>';
//                print_r($factura->fe_comp);
//                echo '</pre>';
    if($notaCredito->fe_comp=="Si"){
        $m = hacer_nc_electronica($notaCredito, $renglones, $empresa, $codMov,$link);
    }else{
        $m = hacer_nc($notaCredito, $renglones, $empresa,$link);
    }
/* generar PDF 
 * =============================================================================
 */
//echo traeLogo();
//echo "<head><style>";    
//echo $stylesheet = file_get_contents('_css/pdf.css');    
//echo "</style></head>";
//echo $m;
    
/*/pdf m 2 genero pdf
 * =============================================================================
 */
require_once  'sistema/_lib/mpdf2/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf(['mode' => 'c',
    'margin_left' => 5,
	'margin_right' => 5,
	'margin_top' => 4,
	'margin_bottom' => 5,
	'margin_header' => 5,
	'margin_footer' => 7
    ]);


$stylesheet = file_get_contents('sistema/_css/pdf.css');
    $mpdf->WriteHTML($stylesheet,1);
    $mpdf->WriteHTML($m,2);
    $mpdf->SetDisplayMode('fullpage');
   
    //$mpdf->Output();
   //echo"<style>". $stylesheet ."</style>";
   //echo $m;
    $mpdf->Output('NC-'.$notaCredito->NroComprobante.'.pdf','D');
    exit; 
}else{
    echo "Falta parametro obligatorio";
}
    
    
}


function generar_nro_cod_barra_nc($fact,$cuit){
//    $tipoF=array("A"=>"01","B"=>"06","M"=>"51","C"=>"11","E"=>"19");
    $tipoF=array("A"=>"03","B"=>"08","M"=>"53","C"=>"13","E"=>"21");
    
    $tipoComp= $tipoF[$fact->tipoNcredito]; 
    $cuitCodBarra= str_replace("-", "", $cuit); 
    $puntoVenta=$fact->nro_punto_venta; 
    $caeFactura=$fact->fe_cae;  
    $caeVto=$fact->vtoCaeN; 
    
    $puntoVentaCero= str_pad($puntoVenta, 4, "0", STR_PAD_LEFT);
    
   
    //Armo el Nro Completo
    $digito = $cuitCodBarra.$tipoComp.$puntoVentaCero.$caeFactura.$caeVto;
    
    //Llamo a la funcion para que calcule el digito Verificado
    $verif = verificador($digito);
    
    //DV = Verif

    return $digito.$verif;
}

function trae_cod_barra_nc($fact,$cuit){
//    echo "barra<pre>";
//    echo print_r($fact);
//    echo "</pre>";
    
    $nroCodBarra= generar_nro_cod_barra_nc($fact,$cuit);
//    echo "barra{<pre>";
//    echo $nroCodBarra;
//    echo "</pre>}";
    $url="sistema/_img/barcode/".$nroCodBarra.".png";
    // si la foto existe la muestro
    
    if(file_exists($url)){
        $foto='<img src="'.$url.'" style="width:250px;">';
    }else{
        // la imagen no existe... la creo.
        $hiceBarra=genera_imagen_barra($nroCodBarra);
        if($hiceBarra==0){
            $foto='<img src="'.$url.'" style="width:250px;">';
        }else{
            $foto='<a style="width:250px;">Sin foto</a>';
        }
    }
    
    
    //preguntar si la imagen existe...sino..
    //
    // generar un codigo de barra con los codigos de generar codbarra
    //$foto='<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($nroCodBarra, $generator::TYPE_CODE_128_B)) . '"><label>'.$nroCodBarra.'</label>';
    
    return $foto;
}

function hacer_nc_electronica($ncredito,$renglones,$empresa,$codMov,$connV){
    //echo print_r($ncredito);
    $imgCodBarra= trae_cod_barra_nc($ncredito,$empresa['cuit_empresa']);
    
     $tipoF=array("A"=>"03","B"=>"08","M"=>"53","C"=>"13","E"=>"21");
    
    $codigoTipoComp= $tipoF[$ncredito->tipoNcredito]; 
    
    $feHtml = "";        
    $feHtml .='    <div id="comprobante" style="height:847px;">';
    $feHtml .='    <div id="cabeceraComprobante">';
    //$feHtml .='    <input type="image" src="_img/imprimir.png" id="imprimir" value="Imprimir">';
    $feHtml .='        <div id="izquierda">';
    $feHtml .='           <div id="logo"><img src="'.traeLogo($connV).'" alt="'.$empresa['nombre_empresa'].'" title="'.$empresa['nombre_empresa'].'"  /></div>';
    $feHtml .='           <div id="leyenda">Comprobante electrónico</div>'; 
    $feHtml .='        </div>';
    $feHtml .='        <div id="tipoComp"><strong>'.$ncredito->tipoNcredito.'</strong><br>Cod.'.$codigoTipoComp.'</div>';
    $feHtml .='        <div id="derecha">';
    $feHtml .='               <ul class="datoComprobante">';
    $feHtml .='                   <li class="destacadoF"><strong>NOTA DE CREDITO</strong></li>';
    $feHtml .='                   <li class="destacado"><strong>Nro: '.$ncredito->NroComprobante.'</strong></li>';
    $feHtml .='                   <li><strong>Fecha: </strong>'.$ncredito->Fecha.'</li>';
    $feHtml .='                   <li><strong>Usuario: </strong> '.$ncredito->facturador.'</li>';
    $feHtml .='                   <li><strong>Vendedor: </strong> '.$ncredito->Vendedor.'</li>';


    $feHtml .='               </ul>';
    $feHtml .='        </div>';
    $feHtml .='        </div>';

    $feHtml .='    <div id="membrete">';
    $feHtml .='       <div class="columna">';
    $feHtml .='               <ul class="datoComprobante">';
    $feHtml .='                   <li class="destacado"><strong>'.$empresa['nombre_empresa'].'</strong></li>';
    $feHtml .='                   <li>'.$empresa['domicilio_empresa'].'</li>';
    $feHtml .='                   <li>Tel: '.$empresa['telefono_empresa'].'</li>';
    $feHtml .='                   <li>E-mail: '.$empresa['email_empresa'].'</li>';
    $feHtml .='                   <li>IVA: '.$empresa['iva_empresa'].'</li>';
    $feHtml .='               </ul>';
    $feHtml .='       </div>';
    $feHtml .='       <div class="columna">';
    $feHtml .='               <ul class="datoComprobante">';
    $feHtml .='                   <li>&nbsp;</li>';
    $feHtml .='                   <li>CUIT: '.$empresa['cuit_empresa'].'</li>';
    $feHtml .='                   <li>Inic. Act.: '.  $empresa['iniact_empresa'].'</li>';
    $feHtml .='                   <li>Ing. Brutos: '.$empresa['ingbrutos_empresa'].'</li>';
    $feHtml .='               </ul>';
    $feHtml .='       </div>';
    $feHtml .='    </div>';  
    $feHtml .='    <div id="datosCliente">';
    $feHtml .='       <div class="columna">';

    $feHtml .='               <ul class="datoComprobante">';
    $feHtml .='                   <li><strong>Cliente: </strong>'.$ncredito->nombre_cliente.'</li>';
    $feHtml .='                   <li><strong>Domicilio: </strong>'.$ncredito->Calle.' Nº'. $ncredito->NroCalle .' depto '.$ncredito->Dpto.   '</li>';
    $feHtml .='                   <li><strong>IVA: </strong>'.$ncredito->iva_cliente.'</li>';
    $feHtml .='                   <li><strong>CUIT: </strong>'.$ncredito->CUIT.'</li>';
    
    $feHtml .='               </ul>';
    $feHtml .='       </div>';
    $feHtml .='       <div class="columna">';
    $feHtml .='               <ul class="datoComprobante">';
    $feHtml .='                   <li><strong>Comp. asociado: </strong>'.$ncredito->NroFactura.'</li>';
    

    $feHtml .='               </ul>';
    $feHtml .='       </div>';
    $feHtml .='    </div>';  
    $feHtml .='   <div id="cuerpoComprobante">';
    
    switch($ncredito->TipoNC){
        case "Devolucion":
            if($ncredito->tipoNcredito =="A" || $ncredito->tipoNcredito =="M" || $ncredito->tipoNcredito =="E"){
                $arrHtml= cuerpo_dev_nc_iva($renglones, $ncredito);
            }
            if($ncredito->tipoNcredito =="B"){
                $arrHtml= cuerpo_dev_nc_no_iva($renglones,$ncredito);
            }
            if($ncredito->tipoNcredito =="C"){
                $arrHtml = cuerpo_dev_nc_c($renglones,$ncredito);
            }
            
            break;
        case "Importe":
            if($ncredito->tipoNcredito =="C"){
                $arrHtml= cuerpo_importe_ncc($ncredito);
            }else{
                $arrHtml= cuerpo_importe_nc($ncredito);
            }
            break;
        case "Descuento":
            if($ncredito->tipoNcredito =="C"){
                $arrHtml= cuerpo_descuento_ncc($ncredito);
            }else{
                $arrHtml= cuerpo_descuento_nc($ncredito);
            }
            break;
        case "Concepto":
            if($ncredito->tipoNcredito =="C"){
                $arrHtml= cuerpo_concepto_ncc($renglones,$ncredito);
            }else{
                $arrHtml= cuerpo_concepto_nc($renglones,$ncredito);
            }
            break;
    }
    
    $cuerpo=$arrHtml['cuerpo'];
    $pie=$arrHtml['pie'];
    
     $feHtml .=$cuerpo;
    
    $feHtml .='   </div>';
    $feHtml .='   <div id="pieComprobante">';
    $feHtml .='       <div id="detalle">';   
    $feHtml .='       <p style="height:70px;"><strong>Detalle: </strong> '.$ncredito->Detalle.'</p>';
    $feHtml .='       <p style="height:60px;"><strong>Observ: </strong> '.$ncredito->detalle_comprobante.' </p>';   
    if($ncredito->total_percep!=0){
        $feHtml .='       <p style="height:15px;"><strong>Percep: </strong> '.$ncredito->detPercep.' </p>';   
    }
    $feHtml .='       <p style="height:15px;"><strong>Son Pesos: '.$ncredito->ImporteVentaL.' </strong> </p>';
    $feHtml .='       </div>';
    $feHtml .= $pie;
   
    
    $feHtml .='       <div id="final">';
    $feHtml .='         <div id="final-izq">';
    $feHtml .='             <img src="sistema/_img/logo_afip.jpg" style="width:350px">';
    $feHtml .='             '.$imgCodBarra.'';
    $feHtml .='         </div>';
    $feHtml .='         <div id="final-der">';
    $feHtml .='             <p><label><strong>Nro CAE: </strong></label> '.$ncredito->fe_cae.'<br>';
    $feHtml .='                 <label><strong>Vto CAE: </strong></label> '.$ncredito->vtoCae.'</p>';
    $feHtml .='             <p>Comprobante generado por: ';
    $feHtml .='             <img src="sistema/_img/logo_administranet_chico.gif"></p>';
    $feHtml .='             <p>Tel:(0261)- 4274480 / 4283071 |  <a href="https://www.administranet.com.ar">www.administranet.com.ar</a></p>';
    $feHtml .='         </div>';
    $feHtml .='       </div>';
    $feHtml .='   </div>';
    $feHtml .='</div>';
    return $feHtml;
}

/*
 * Generar HTML Factura Comun
 */

function hacer_nc($ncredito,$renglones,$empresa,$connV){
    $tipoF=array("A"=>"03","B"=>"08","M"=>"53","C"=>"13","E"=>"21");
    
    $codigoTipoComp= $tipoF[$ncredito->tipoNcredito]; 
    
    $faHtml = "";        
    $faHtml .='    <div id="comprobante">';

    //$faHtml .='    <input type="button" id="imprimir" value="Imprimir">';

    $faHtml .='    <div id="cabeceraComprobante">';
    //$faHtml .='    <input type="image" src="_img/imprimir.png" id="imprimir" value="Imprimir">';
    $faHtml .='        <div id="izquierda">';
    $faHtml .='           <img src="'.traeLogo($connV).'" alt="'.$empresa['nombre_empresa'].'" title="'.$empresa['nombre_empresa'].'" class="asBlock" />';

    $faHtml .='        </div>';
    $faHtml .='         <div id="tipoComp"><strong>'.$ncredito->tipoNcredito.'</strong><br>Cod.'.$codigoTipoComp.'</div>';
    $faHtml .='        <div id="derecha">';
    $faHtml .='               <ul class="datoComprobante">';
    $faHtml .='                   <li class="destacado"><strong>NOTA DE CREDITO</strong></li>';
    $faHtml .='                   <li class="destacado"><strong>Nro: '.$ncredito->NroComprobante.'</strong></li>';
    $faHtml .='                   <li><strong>Fecha: </strong>'.$ncredito->Fecha.'</li>';
    $faHtml .='                   <li><strong>Usuario: </strong> '.$ncredito->facturador.'</li>';
    $faHtml .='                   <li><strong>Vendedor: </strong> '.$ncredito->vendedor.'</li>';
   

    $faHtml .='               </ul>';
    $faHtml .='        </div>';
    $faHtml .='        </div>';

    $faHtml .='    <div id="membrete">';
    $faHtml .='       <div class="columna">';
    $faHtml .='               <ul class="datoComprobante">';
    $faHtml .='                   <li class="destacado"><strong>'.$_SESSION['nombre_empresa'].'</strong></li>';
    $faHtml .='                   <li>'.$_SESSION['domicilio_empresa'].'</li>';
    $faHtml .='                   <li>Tel: '.$_SESSION['telefono_empresa'].'</li>';
    $faHtml .='                   <li>E-mail: '.$_SESSION['email_empresa'].'</li>';
    $faHtml .='                   <li>IVA: '.$_SESSION['iva_empresa'].'</li>';
    $faHtml .='               </ul>';
    $faHtml .='       </div>';
    $faHtml .='       <div class="columna">';
    $faHtml .='               <ul class="datoComprobante">';
    $faHtml .='                   <li>&nbsp;</li>';
    $faHtml .='                   <li>CUIT: '.$_SESSION['cuit_empresa'].'</li>';
    $faHtml .='                   <li>Inic. Act.: '.  $_SESSION['iniact_empresa'].'</li>';
    $faHtml .='                   <li>Ing. Brutos: '.$_SESSION['ingbrutos_empresa'].'</li>';
    $faHtml .='               </ul>';
    $faHtml .='       </div>';
    $faHtml .='    </div>';  
    $faHtml .='    <div id="datosCliente">';
    $faHtml .='       <div class="columna">';

    $faHtml .='               <ul class="datoComprobante">';
    $faHtml .='                   <li><strong>Cliente: </strong>'.$ncredito->nombre_cliente.'</li>';
    $faHtml .='                   <li><strong>Domicilio: </strong>'.$ncredito->Calle.' Nº'. $ncredito->NroCalle .' depto :'.$ncredito->Dpto.   '</li>';
    $faHtml .='                   <li><strong>IVA: </strong>'.$ncredito->iva_cliente.'</li>';
    $faHtml .='                   <li><strong>Cuit: </strong>'.$ncredito->CUIT.'</li>';
    $faHtml .='                   <li><strong>Cond.Venta: </strong>'.$ncredito->CondVenta.'</li>';
    $faHtml .='               </ul>';
    $faHtml .='       </div>';
    $faHtml .='       <div class="columna">';
    $faHtml .='               <ul class="datoComprobante">';
    $feHtml .='               <ul class="datoComprobante">';
    $feHtml .='                   <li><strong>Comp. asociado: </strong>'.$ncredito->NroFactura.'</li>';    

    $faHtml .='               </ul>';
    $faHtml .='       </div>';
    $faHtml .='    </div>';  
    $faHtml .='   <div id="cuerpoComprobante">';
   
    switch($ncredito->TipoNC){
        case "Devolucion":
            if($ncredito->tipoNcredito =="A" || $ncredito->tipoNcredito =="M" || $ncredito->tipoNcredito =="E"){
                $arrHtml= cuerpo_dev_nc_iva($renglones, $ncredito);
            }
            if($ncredito->tipoNcredito =="B"){
                $arrHtml= cuerpo_dev_nc_no_iva($renglones,$ncredito);
            }
            if($ncredito->tipoNcredito =="C"){
                $arrHtml = cuerpo_dev_nc_c($renglones,$ncredito);
            }
            
            break;
        case "Importe":
            if($ncredito->tipoNcredito =="C"){
                $arrHtml= cuerpo_importe_ncc($ncredito);
            }else{
                $arrHtml= cuerpo_importe_nc($ncredito);
            }
            break;
        case "Descuento":
            if($ncredito->tipoNcredito =="C"){
                $arrHtml= cuerpo_descuento_ncc($ncredito);
            }else{
                $arrHtml= cuerpo_descuento_nc($ncredito);
            }
            break;
        case "Concepto":
            if($ncredito->tipoNcredito =="C"){
                $arrHtml= cuerpo_concepto_ncc($renglones,$ncredito);
            }else{
                $arrHtml= cuerpo_concepto_nc($renglones,$ncredito);
            }
            break;
    }
    
    $cuerpo=$arrHtml['cuerpo'];
    $pie=$arrHtml['pie'];
    
    $feHtml .= $cuerpo;
    $faHtml .='   </div>';
    $faHtml .='   <div id="pieComprobante">';
    $faHtml .='       <div id="detalle">';
    
    $faHtml .='       <p style="height:35%;"><strong>Detalle: </strong> '.$ncredito->Detalle.'</p>';
    $faHtml .='       <p style="height:35%;"><strong>Observ: </strong> '.$ncredito->detalle_comprobante.' </p>';
    
    $faHtml .='       <p style="height:10%;"><strong>Son Pesos: '.$ncredito->ImporteVentaL.' </strong> </p>';
    $faHtml .='       </div>';
    $feHtml .= $pie;
    $faHtml .='       <div id="final">';
    $faHtml .='           <div id="final-der">';
//    $faHtml .='               <img src="_img/logo_afip.jpg">';
//    $faHtml .='               <img src="'.$co.'">';
    $faHtml .='           </div>';
    $faHtml .='           <div id="final-izq">';
    $faHtml .='               <p>Comprobante generado por: ';
    $faHtml .='               <img src="sistema/_img/logo_administranet_chico.gif"></p>';
    $faHtml .='               <p>Tel:(0261)- 4274480 / 4283071 |  <a href="http://www.administranet.com.ar">www.administranet.com.ar</a></p>';
    $faHtml .='           </div>';    
    $faHtml .='       </div>';
    $faHtml .='   </div>';
    $faHtml .='</div>';
    return $faHtml;
}

// funcion para traer detalle y pie de las notas de credito.

// tipo devolucion.

function cuerpo_dev_nc_iva($renglones,$ncredito){
    
    $pieHtml ='       <div id="importe">';
    $pieHtml .='           <table>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="2" class="izquierda">Neto:</td><td class="derecha">'.number_format($ncredito->SubTotalGral,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="2" class="izquierda">Descuento '.number_format($ncredito->PorDesc1,2,",",".").'%: </td><td class="derecha">'.number_format($ncredito->ImpDesc1,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="2" class="izquierda">Exento:</td><td class="derecha">'.number_format($ncredito->Exento,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td  class="izquierda">IVA:</td><td class="derecha">21,00</td><td class="derecha">'.number_format($ncredito->IVA1,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td  class="izquierda">IVA:</td><td class="derecha">10.50</td><td class="derecha">'.number_format($ncredito->IVA2,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td  class="izquierda" colspan="2">Percepciones:</td><td class="derecha">'.number_format($ncredito->total_percep,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="3" class="separador">&nbsp;</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr >';
    $pieHtml .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.number_format(($ncredito->Total+$ncredito->total_percep),2,",",".").'</td>';
    $pieHtml .='               </tr>';

    $pieHtml .='           </table>';

    $pieHtml .='       </div>';
    
    // detalle
    $feHtml ='        <table id="tablaComp" >';
            $feHtml .='            <thead>';
            $feHtml .='                <tr>';            
            $feHtml .='                    <th>Código</th>';
            $feHtml .='                    <th>Descripción</th>';
            $feHtml .='                    <th>Cantidad</th>';            
            $feHtml .='                    <th>%Alic</th>';            
            $feHtml .='                    <th>%Desc.</th>';
            $feHtml .='                    <th>Desc</th>';            
            $feHtml .='                    <th>Precio Unitario</th>';
            $feHtml .='                    <th>Precio Total</th>';                          
            $feHtml .='                </tr>';
            $feHtml .='            </thead>';
            $feHtml .='            <tbody>';
            $cantItems =0;
    foreach($renglones as $renglon):
        $cantItems++;
        $feHtml .='<tr>';       
        $feHtml .='<td class="izquierda">'.$renglon->IDArt .'</td>';             
        $feHtml .='<td class="izquierda">'.$renglon->Descripcion.'</td>';
        $feHtml .='<td class="derecha">'.$renglon->Cantidad.'</td>';
        $feHtml .='<td class="derecha">'. $renglon->Alicuota.'</td>';
        $feHtml .='<td class="derecha">'. $renglon->PorDesc.'</td>';
        $feHtml .='<td class="derecha">'. $renglon->ImpDesc.'</td>';
        $feHtml .='<td class="derecha">$'. number_format($renglon->PrecioVentaxU,4,",",".").'</td>';       
        $feHtml .='<td class="derecha">$'. number_format($renglon->PrecioNetoxR,4,",",".").'</td>';
        $feHtml .='</tr>';
    endforeach;
    $feHtml .='</tbody>';
    $feHtml .=' </table>';
    $vuelta['pie'] = $pieHtml;
    $vuelta ['cuerpo'] = $feHtml;        
    return $vuelta;        
}



function cuerpo_dev_nc_no_iva($renglones,$ncredito){
    $pieHtml ='       <div id="importe">';
    $pieHtml .='           <table style="margin-top:165px;">';   
    $pieHtml .='               <tr >';
    $pieHtml .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.number_format(($ncredito->Total+$ncredito->total_percep),2,",",".").'</td>';
    $pieHtml .='               </tr>';

    $pieHtml .='           </table>';

    $pieHtml .='       </div>';
    $feHtml ='        <table id="tablaComp" >';
            $feHtml .='            <thead>';
            $feHtml .='                <tr>';            
            $feHtml .='                    <th>Código</th>';
            $feHtml .='                    <th>Descripción</th>';
            $feHtml .='                    <th>Cantidad</th>';                                  
            $feHtml .='                    <th>%Desc.</th>';
            $feHtml .='                    <th>Desc</th>';            
            $feHtml .='                    <th>Precio Unitario</th>';
            $feHtml .='                    <th>Precio Total</th>';                          
            $feHtml .='                </tr>';
            $feHtml .='            </thead>';
            $feHtml .='            <tbody>';
            $cantItems =0;
    foreach($renglones as $renglon):
        $cantItems++;
        $feHtml .='<tr>';       
        $feHtml .='<td class="izquierda">'.$renglon->IDArt .'</td>';             
        $feHtml .='<td class="izquierda">'.$renglon->Descripcion.'</td>'; 
        $feHtml .='<td class="derecha">'.$renglon->Cantidad.'</td>'; 
        $feHtml .='<td class="derecha">'. $renglon->PorDesc.'</td>';
        $feHtml .='<td class="derecha">'. $renglon->ImpDesc.'</td>';
        $feHtml .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxU,4,",",".").'</td>';       
        $feHtml .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxR,4,",",".").'</td>';
        $feHtml .='</tr>';
    endforeach;
    $feHtml .='</tbody>';
    $feHtml .=' </table>';
            
    $vuelta['pie'] = $pieHtml;
    $vuelta ['cuerpo'] = $feHtml;        
    return $vuelta;    
}


function cuerpo_dev_nc_c($renglones,$ncredito){
    //pie
    $pieHtml ='       <div id="importe">';
    $pieHtml .='          <table style="margin-top:165px;">';   
    $pieHtml .='               <tr >';
    $pieHtml .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.number_format(($ncredito->Total+$ncredito->total_percep),2,",",".").'</td>';
    $pieHtml .='               </tr>';

    $pieHtml .='           </table>';

    $pieHtml .='       </div>';
    
    // cuerpo
    $feHtml ='        <table id="tablaComp" >';
            $feHtml .='            <thead>';
            $feHtml .='                <tr>';            
            $feHtml .='                    <th>Código</th>';
            $feHtml .='                    <th>Descripción</th>';
            $feHtml .='                    <th>Cantidad</th>';                                  
            $feHtml .='                    <th>%Desc.</th>';
            $feHtml .='                    <th>Desc</th>';            
            $feHtml .='                    <th>Precio Unitario</th>';
            $feHtml .='                    <th>Precio Total</th>';                          
            $feHtml .='                </tr>';
            $feHtml .='            </thead>';
            $feHtml .='            <tbody>';
            $cantItems =0;
    foreach($renglones as $renglon):
        $cantItems++;
        $feHtml .='<tr>';       
        $feHtml .='<td class="izquierda">'.$renglon->IDArt .'</td>';             
        $feHtml .='<td class="izquierda">'.$renglon->Descripcion.'</td>';   
        $feHtml .='<td class="derecha">'. $renglon->PorDesc.'</td>';
        $feHtml .='<td class="derecha">'. $renglon->ImpDesc.'</td>';
        $feHtml .='<td class="derecha">$'. number_format($renglon->PrecioVentaxU,4,",",".").'</td>';       
        $feHtml .='<td class="derecha">$'. number_format($renglon->PrecioNetoxR,4,",",".").'</td>';
        $feHtml .='</tr>';
    endforeach;
    $feHtml .='</tbody>';
    $feHtml .=' </table>';
            
      $vuelta['pie'] = $pieHtml;
    $vuelta ['cuerpo'] = $feHtml;        
    return $vuelta;  
}

// tipo importe descuento en factura tipo IMPORTE
function cuerpo_importe_nc($ncredito){
    
    $pieHtml ='       <div id="importe">';
    $pieHtml .='           <table>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="2" class="izquierda">Neto:</td><td class="derecha">'.number_format($ncredito->SubTotalGral,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="2" class="izquierda">Descuento '.number_format($ncredito->PorDesc1,2,",",".").'%: </td><td class="derecha">'.number_format($ncredito->ImpDesc1,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="2" class="izquierda">Exento:</td><td class="derecha">'.number_format($ncredito->Exento,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td  class="izquierda">IVA:</td><td class="derecha">21,00</td><td class="derecha">'.number_format($ncredito->IVA1,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td  class="izquierda">IVA:</td><td class="derecha">10.50</td><td class="derecha">'.number_format($ncredito->IVA2,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td  class="izquierda" colspan="2">Percepciones:</td><td class="derecha">'.number_format($ncredito->total_percep,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="3" class="separador">&nbsp;</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr >';
    $pieHtml .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.number_format(($ncredito->Total+$ncredito->total_percep),2,",",".").'</td>';
    $pieHtml .='               </tr>';

    $pieHtml .='           </table>';

    $pieHtml .='       </div>';
    
    // cuerpo
    $feHtml ='        <table id="tablaComp" >';
    $feHtml .='            <thead>';
    $feHtml .='                <tr>';            
    $feHtml .='                    <th>Código</th>';
    $feHtml .='                    <th>Descripción</th>';
    $feHtml .='                    <th>Cantidad</th>'; 
    $feHtml .='                    <th>%Alic</th>'; 
    $feHtml .='                    <th>%Desc.</th>';
    $feHtml .='                    <th>Desc</th>';            
    $feHtml .='                    <th>Precio Unitario</th>';
    $feHtml .='                    <th>Precio Total</th>';                          
    $feHtml .='                </tr>';
    $feHtml .='            </thead>';
    $feHtml .='            <tbody>';
    $cantItems =0;
    if($ncredito->SubTotal1!==0){
        $cantItems++;
        $feHtml .='<tr>';       
        $feHtml .='<td class="izquierda">&nbsp;</td>';             
        $feHtml .='<td class="izquierda">Descuento en factura - Alicuota '.$ncredito->Alicuota1.'</td>'; 
        $feHtml .='<td class="derecha">1</td>';
        $feHtml .='<td class="derecha">&nbsp;</td>';
        $feHtml .='<td class="derecha">&nbsp;</td>';
        $feHtml .='<td class="derecha">&nbsp;</td>';
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal1,4,",",".").'</td>';       
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal1,4,",",".").'</td>';
        $feHtml .='</tr>';
    }
    if($ncredito->SubTotal2!=0){
        $cantItems++;
        $feHtml .='<tr>';       
        $feHtml .='<td class="izquierda">&nbsp;</td>';             
        $feHtml .='<td class="izquierda">Descuento en factura - Alicuota '.$ncredito->Alicuota2.'</td>'; 
        $feHtml .='<td class="derecha">1</td>'; 
        $feHtml .='<td class="derecha">&nbsp;</td>';
        $feHtml .='<td class="derecha">&nbsp;</td>';
        $feHtml .='<td class="derecha">&nbsp;</td>';
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal2,4,",",".").'</td>';       
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal2,4,",",".").'</td>';
        $feHtml .='</tr>';
        
    }
    $feHtml .='</tbody>';
    $feHtml .=' </table>';
            
      $vuelta['pie'] = $pieHtml;
    $vuelta ['cuerpo'] = $feHtml;        
    return $vuelta;  
}
// tipo importe descuento en factura tipo IMPORTE nota credito C
function cuerpo_importe_ncc($ncredito){
    $pieHtml ='       <div id="importe">';
    $pieHtml .='           <table style="margin-top:165px;">';   
    $pieHtml .='               <tr >';
    $pieHtml .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.number_format(($ncredito->Total+$ncredito->total_percep),2,",",".").'</td>';
    $pieHtml .='               </tr>';

    $pieHtml .='           </table>';

    $pieHtml .='       </div>';
    
    // cuerpo
    $feHtml ='        <table id="tablaComp" >';
    $feHtml .='            <thead>';
    $feHtml .='                <tr>';            
    $feHtml .='                    <th>Código</th>';
    $feHtml .='                    <th>Descripción</th>';
    $feHtml .='                    <th>Cantidad</th>';                                  
    $feHtml .='                    <th>%Desc.</th>';
    $feHtml .='                    <th>Desc</th>';            
    $feHtml .='                    <th>Precio Unitario</th>';
    $feHtml .='                    <th>Precio Total</th>';                          
    $feHtml .='                </tr>';
    $feHtml .='            </thead>';
    $feHtml .='            <tbody>';
    $cantItems =0;

    if($ncredito->SubTotal1!==0){
        $cantItems++;
        $feHtml .='<tr>';       
        $feHtml .='<td class="izquierda">&nbsp;</td>';             
        $feHtml .='<td class="izquierda">Descuento en factura - Alicuota '.$ncredito->Alicuota1.'</td>'; 
        $feHtml .='<td class="derecha">1</td>';                    
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal1,4,",",".").'</td>';       
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal1,4,",",".").'</td>';
        $feHtml .='</tr>';
    }
    if($ncredito->SubTotal2!=0){
        $cantItems++;
        $feHtml .='<tr>';       
        $feHtml .='<td class="izquierda">&nbsp;</td>';             
        $feHtml .='<td class="izquierda">Descuento en factura - Alicuota '.$ncredito->Alicuota2.'</td>'; 
        $feHtml .='<td class="derecha">1</td>';
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal2,4,",",".").'</td>';       
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal2,4,",",".").'</td>';
        $feHtml .='</tr>';
        
    }
            
    $feHtml .='</tbody>';
    $feHtml .=' </table>';
            
      $vuelta['pie'] = $pieHtml;
    $vuelta ['cuerpo'] = $feHtml;        
    return $vuelta;  
}

// tipo descuento en recibo Tipo DESCUENTO en recibo

function cuerpo_descuento_nc($ncredito){ 
    $pieHtml ='       <div id="importe">';
    $pieHtml .='           <table>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="2" class="izquierda">Neto:</td><td class="derecha">'.number_format($ncredito->SubTotalGral,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="2" class="izquierda">Descuento '.number_format($ncredito->PorDesc1,2,",",".").'%: </td><td class="derecha">'.number_format($ncredito->ImpDesc1,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="2" class="izquierda">Exento:</td><td class="derecha">'.number_format($ncredito->Exento,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td  class="izquierda">IVA:</td><td class="derecha">21,00</td><td class="derecha">'.number_format($ncredito->IVA1,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td  class="izquierda">IVA:</td><td class="derecha">10.50</td><td class="derecha">'.number_format($ncredito->IVA2,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td  class="izquierda" colspan="2">Percepciones:</td><td class="derecha">'.number_format($ncredito->total_percep,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="3" class="separador">&nbsp;</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr >';
    $pieHtml .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.number_format(($ncredito->Total+$ncredito->total_percep),2,",",".").'</td>';
    $pieHtml .='               </tr>';

    $pieHtml .='           </table>';

    $pieHtml .='       </div>';
    
    // cuerpo
    $feHtml ='        <table id="tablaComp" >';
    $feHtml .='            <thead>';
    $feHtml .='                <tr>';            
    $feHtml .='                    <th>Código</th>';
    $feHtml .='                    <th>Descripción</th>';
    $feHtml .='                    <th>Cantidad</th>'; 
    $feHtml .='                    <th>%Alic</th>'; 
    $feHtml .='                    <th>%Desc.</th>';
    $feHtml .='                    <th>Desc</th>';            
    $feHtml .='                    <th>Precio Unitario</th>';
    $feHtml .='                    <th>Precio Total</th>';                          
    $feHtml .='                </tr>';
    $feHtml .='            </thead>';
    $feHtml .='            <tbody>';
    $cantItems =0;
    if($ncredito->SubTotal1!==0){
        $cantItems++;
        $feHtml .='<tr>';       
        $feHtml .='<td class="izquierda">&nbsp;</td>';             
        $feHtml .='<td class="izquierda">Descuento en recibo</td>'; 
        $feHtml .='<td class="derecha">1</td>';
        $feHtml .='<td class="derecha">&nbsp;</td>';
        $feHtml .='<td class="derecha">&nbsp;</td>';
        $feHtml .='<td class="derecha">&nbsp;</td>';
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal1,4,",",".").'</td>';       
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal1,4,",",".").'</td>';
        $feHtml .='</tr>';
    }
    if($ncredito->SubTotal2!=0){
        $cantItems++;
        $feHtml .='<tr>';       
        $feHtml .='<td class="izquierda">&nbsp;</td>';             
        $feHtml .='<td class="izquierda">Descuento en recibo</td>'; 
        $feHtml .='<td class="derecha">1</td>'; 
        $feHtml .='<td class="derecha">&nbsp;</td>';
        $feHtml .='<td class="derecha">&nbsp;</td>';
        $feHtml .='<td class="derecha">&nbsp;</td>';
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal2,4,",",".").'</td>';       
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal2,4,",",".").'</td>';
        $feHtml .='</tr>';
        
    }
    $feHtml .='</tbody>';
    $feHtml .=' </table>';
            
      $vuelta['pie'] = $pieHtml;
    $vuelta ['cuerpo'] = $feHtml;        
    return $vuelta;  
}

// nota de credito DESCUENTO en recibo TIPO C

function cuerpo_descuento_ncc($ncredito){ 
    $pieHtml ='       <div id="importe">';
    $pieHtml .='          <table style="margin-top:165px;">';   
    $pieHtml .='               <tr >';
    $pieHtml .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.number_format(($ncredito->Total+$ncredito->total_percep),2,",",".").'</td>';
    $pieHtml .='               </tr>';

    $pieHtml .='           </table>';

    $pieHtml .='       </div>';
    
    // cuerpo
    $feHtml ='        <table id="tablaComp" >';
    $feHtml .='            <thead>';
    $feHtml .='                <tr>';            
    $feHtml .='                    <th>Código</th>';
    $feHtml .='                    <th>Descripción</th>';
    $feHtml .='                    <th>Cantidad</th>';                                  
    $feHtml .='                    <th>%Desc.</th>';
    $feHtml .='                    <th>Desc</th>';            
    $feHtml .='                    <th>Precio Unitario</th>';
    $feHtml .='                    <th>Precio Total</th>';                          
    $feHtml .='                </tr>';
    $feHtml .='            </thead>';
    $feHtml .='            <tbody>';
    $cantItems =0;

    if($ncredito->SubTotal1!==0){
        $cantItems++;
        $feHtml .='<tr>';       
        $feHtml .='<td class="izquierda">&nbsp;</td>';             
        $feHtml .='<td class="izquierda">Descuento en factura - Alicuota '.$ncredito->Alicuota1.'</td>'; 
        $feHtml .='<td class="derecha">1</td>';                    
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal1,4,",",".").'</td>';       
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal1,4,",",".").'</td>';
        $feHtml .='</tr>';
    }
    if($ncredito->SubTotal2!=0){
        $cantItems++;
        $feHtml .='<tr>';       
        $feHtml .='<td class="izquierda">&nbsp;</td>';             
        $feHtml .='<td class="izquierda">Descuento en factura - Alicuota '.$ncredito->Alicuota2.'</td>'; 
        $feHtml .='<td class="derecha">1</td>';
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal2,4,",",".").'</td>';       
        $feHtml .='<td class="derecha">$'. number_format($ncredito->SubTotal2,4,",",".").'</td>';
        $feHtml .='</tr>';
        
    }
            
    $feHtml .='</tbody>';
    $feHtml .=' </table>';
            
      $vuelta['pie'] = $pieHtml;
    $vuelta ['cuerpo'] = $feHtml;        
    return $vuelta;  
}

// tipo CONCEPTO NC 
function cuerpo_concepto_nc($renglones,$ncredito){
    $pieHtml ='       <div id="importe">';
    $pieHtml .='           <table>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="2" class="izquierda">Neto:</td><td class="derecha">'.number_format($ncredito->SubTotalGral,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="2" class="izquierda">Descuento '.number_format($ncredito->PorDesc1,2,",",".").'%: </td><td class="derecha">'.number_format($ncredito->ImpDesc1,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="2" class="izquierda">Exento:</td><td class="derecha">'.number_format($ncredito->Exento,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td  class="izquierda">IVA:</td><td class="derecha">21,00</td><td class="derecha">'.number_format($ncredito->IVA1,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td  class="izquierda">IVA:</td><td class="derecha">10.50</td><td class="derecha">'.number_format($ncredito->IVA2,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td  class="izquierda" colspan="2">Percepciones:</td><td class="derecha">'.number_format($ncredito->total_percep,2,",",".").'</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr>';
    $pieHtml .='                   <td colspan="3" class="separador">&nbsp;</td>';
    $pieHtml .='               </tr>';
    $pieHtml .='               <tr >';
    $pieHtml .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.number_format(($ncredito->Total+$ncredito->total_percep),2,",",".").'</td>';
    $pieHtml .='               </tr>';

    $pieHtml .='           </table>';

    $pieHtml .='       </div>';
    
    // cuerpo
    $feHtml ='        <table id="tablaComp" >';
            $feHtml .='            <thead>';
            $feHtml .='                <tr>';            
            $feHtml .='                    <th>Código</th>';
            $feHtml .='                    <th>Descripción</th>';
            $feHtml .='                    <th>Cantidad</th>';
            $feHtml .='                    <th>%Alic.</th>';
            $feHtml .='                    <th>%Desc.</th>';
            $feHtml .='                    <th>Desc</th>';            
            $feHtml .='                    <th>Precio Unitario</th>';
            $feHtml .='                    <th>Precio Total</th>';                          
            $feHtml .='                </tr>';
            $feHtml .='            </thead>';
            $feHtml .='            <tbody>';
            $cantItems =0;
    foreach($renglones as $renglon):
        $cantItems++;
        $feHtml .='<tr>';       
        $feHtml .='<td class="izquierda">&nbsp;</td>';             
        $feHtml .='<td class="izquierda">'.$renglon->nombre.'</td>';
        $feHtml .='<td class="derecha">1</td>';
        $feHtml .='<td class="derecha">&nbsp;</td>';
        $feHtml .='<td class="derecha">&nbsp;</td>';         
        $feHtml .='<td class="derecha">&nbsp;</td>';         
        $feHtml .='<td class="derecha">$'. number_format($renglon->importe,4,",",".").'</td>';
        $feHtml .='<td class="derecha">$'. number_format($renglon->importe,4,",",".").'</td>';
        $feHtml .='</tr>';
    endforeach;
    $feHtml .='</tbody>';
    $feHtml .=' </table>';
            
      $vuelta['pie'] = $pieHtml;
    $vuelta ['cuerpo'] = $feHtml;        
    return $vuelta;  
}




// tipo CONCEPTO NCC
function cuerpo_concepto_ncc($renglones,$ncredito){
    $pieHtml ='       <div id="importe">';
    $pieHtml .='           <table style="margin-top:165px;">';   
    $pieHtml .='               <tr >';
    $pieHtml .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.number_format(($ncredito->Total+$ncredito->total_percep),2,",",".").'</td>';
    $pieHtml .='               </tr>';

    $pieHtml .='           </table>';

    $pieHtml .='       </div>';
    
    // cuerpo
    $feHtml ='        <table id="tablaComp" >';
            $feHtml .='            <thead>';
            $feHtml .='                <tr>';            
            $feHtml .='                    <th>Código</th>';
            $feHtml .='                    <th>Descripción</th>';
            $feHtml .='                    <th>Cantidad</th>';                                  
            $feHtml .='                    <th>%Desc.</th>';
            $feHtml .='                    <th>Desc</th>';            
            $feHtml .='                    <th>Precio Unitario</th>';
            $feHtml .='                    <th>Precio Total</th>';                          
            $feHtml .='                </tr>';
            $feHtml .='            </thead>';
            $feHtml .='            <tbody>';
            $cantItems =0;
    foreach($renglones as $renglon):
        $cantItems++;
        $feHtml .='<tr>';       
        $feHtml .='<td class="izquierda">&nbsp;</td>';             
        $feHtml .='<td class="izquierda">'.$renglon->nombre.'</td>';
        $feHtml .='<td class="derecha">1</td>';       
        $feHtml .='<td class="derecha">&nbsp;</td>';         
        $feHtml .='<td class="derecha">&nbsp;</td>';         
        $feHtml .='<td class="derecha">$'. number_format($renglon->importe+$renglon->IvaxR,4,",",".").'</td>';
        $feHtml .='<td class="derecha">$'. number_format($renglon->importe+$renglon->IvaxR,4,",",".").'</td>';
        $feHtml .='</tr>';
    endforeach;
    $feHtml .='</tbody>';
    $feHtml .=' </table>';
            
      $vuelta['pie'] = $pieHtml;
    $vuelta ['cuerpo'] = $feHtml;        
    return $vuelta;  
}

/**
 * # RECIBO PDF funcion UNICA
 */
function hacer_recibo_pdf($codMov,$connV,$tipoSalida=null,$ruta=null){
    // sacar de ver_recibo_pdf
    // ruta: por compatibilidad yo se desde donde llamo a la funcion para que se genere bien el pdf.


/*
 * Parametros
 */


$link=$connV;
if($tipoSalida==null){
    $tipoSalida='D';
}
if($ruta==null){
    //$ruta='../../';
}

$rutaLogo =$ruta.'_img';



//empresa datos
$sqlEmpresa = "SELECT Nombre AS nombre_empresa,
                            Telefono AS telefono_empresa,
                            Cuit AS cuit_empresa,
                            Domicilio AS domicilio_empresa,
                            Email AS email_empresa,
                            IngBrutos AS ingbrutos_empresa,
                            InicioAct AS iniact_empresa,
                            contribuyentes.IVA AS iva_empresa
                           
                      FROM datosempresa
                      LEFT JOIN contribuyentes ON contribuyentes.IDIva = datosempresa.IDIva  
                        WHERE id_empresa=1";
        $hacerEmpresa = mysqli_query($link,$sqlEmpresa) ;
        if($hacerEmpresa){
            $empresa = mysqli_fetch_object($hacerEmpresa);
        }                                    
        
        if(!$hacerEmpresa){
            file_put_contents('error-empresa'.date('Y-m-d').'.txt','Err: '.mysqli_error($connV).PHP_EOL.'SQL::'.$sqlEmpresa.PHP_EOL,FILE_APPEND);
        }


// * recupero datos del recibo de cuenta cliente
$sqlRec="SELECT                   
                                
                DATE_FORMAT(cc.Fecha,'%d/%m/%Y') AS FechaB,
                DATE_FORMAT(cc.Fecha,'%Y%m%d') AS Fecha,
                 cc.TipoComprobante,
                 cliente.nombre_cliente AS nombre_cliente,
                 cc.NroComprobante,                           
                 cc.ImporteCobro AS Importe,
                 cc.TotalEfectivoP AS Efectivo,
                 cc.TotalEfectivoD AS Dolar,
                 cc.TotalCheque As Cheque,
                 cc.TotalRetencion As Retencion,
                 cc.Total_Tarjeta AS Tarjeta,
                 cc.total_trans AS Transferencia,
                 cc.ctabanc_trans AS codCuentaBcaria,
                 cc.nroref_trans AS numeroTrans,
                 cc.fecha_trans AS fecha_trans,
                 banco.Nombre AS Banco,
                 cuenta_banco.NroCuenta AS numeroCuentaBanco,
                 cc.TotalDescRec As Descuento,
                 cc.Detalle AS Detalle, 
                 cc.Anulado,
                 cc.TipoRecibo,
                 cliente.CUIT,
                 cc.Codigo,
                 cc.ImporteVentaL AS importeLetra,
                 viajantes.Nombre AS cobrador,
                 caja_abm.nombre_caja  AS nombreCaja                 
                FROM cuentacliente AS cc
                LEFT JOIN cliente ON cliente.Codigo=cc.Codigo
                LEFT JOIN viajantes ON viajantes.CodViajante=cc.CodViajante 
                LEFT JOIN caja ON caja.codigo_movimiento=cc.CodigoMovimiento AND caja.tipo='Cobranza Efectivo'   
                LEFT JOIN caja_abm ON caja_abm.id_caja=caja.id_caja_abm_origen
                LEFT JOIN cuenta_banco ON cuenta_banco.CodCuenta = cc.ctabanc_trans
                LEFT JOIN banco ON banco.CodBanco = cuenta_banco.CodBanco
                
            WHERE
                 cc.CodigoMovimiento=".$codMov;

    $hacerRec = mysqli_query($link,$sqlRec) or die('<pre>No puedo recuparar cuenta cliente del recibo.'.mysqli_error($link).PHP_EOL.$sqlRec.PHP_EOL.__FILE__.'->'.__LINE__);
    $recibo = mysqli_fetch_object($hacerRec);
    
    // * efectivo
   if($recibo->Efectivo!=0){
       // debo localizar la caja efectivo.
       $efectivo['monto']=$recibo->Efectivo;
       $efectivo['caja']=$recibo->nombreCaja;
   }
    //  * cheques
   if($recibo->Cheque!=0){
       $sqlChequeTercero=" SELECT 
                                ch.NroCheque,
                                ch.FechaEmision,
                                ch.FechaCobro,
                                DATE_FORMAT(ch.FechaEmision,'%d/%m/%Y') AS fEmision,
                                DATE_FORMAT(ch.FechaCobro,'%d/%m/%Y') AS fCobro,
                                ch.Librador,
                                ch.Importe,
                                banco.Nombre AS nombreBanco
                            FROM
                            chequetercero AS ch
                            LEFT JOIN banco ON banco.CodBanco=ch.CodBanco
                            WHERE
                            ch.CodigoMovimientoREC=".$codMov;
      $hacerCh = mysqli_query($link,$sqlChequeTercero) or die('<pre>No puedo recuperar cheques de tercero'.mysqli_error($link).'<br>'.$sqlChequeTercero.'</pre>'.__FILE__.'->'.__LINE__.PHP_EOL);
      $arrCheques=array();
      while($ch = mysqli_fetch_assoc($hacerCh)){
          $arrCheques[]=$ch;
      } 
   } 

//  * Tarjeta de credito
// puede ser mas de una tarjeta por lo que hay que traer agrupada la caja de tarjetas.
   if($recibo->Tarjeta<>0){
    $sqlCajaTarjeta = "SELECT 
                        caja_abm.nombre_caja AS nombreCajaT
                        FROM caja 
                        LEFT JOIN caja_abm ON caja_abm.id_caja=caja.id_caja_abm_origen
                        WHERE 
                        caja.tipo ='Tarjeta'
                        AND 
                        caja.codigo_movimiento=".$codMov." GROUP BY caja.id_caja_abm_origen ";
    $hacerTj = mysqli_query($link,$sqlCajaTarjeta);
    $cajaTarjeta = mysqli_fetch_assoc($hacerTj);
    $tarjeta['caja'] = $cajaTarjeta['nombreCajaT'];
    
    $sqlTarjetas = "SELECT 
                        tj.nombre_tc_comprobante AS tarjeta,
                        tj.nombre_plan_tc_comprobante AS plan,
                        tj.nro_tarjeta_tc_comprobante AS numero,
                        tj.cuotas_tc_comprobante AS cuotas,
                        tj.nro_cupon_tc_comprobante As cupon,
                        tj.importe_cuota As importeCuota,
                        tj.importe_con_interes AS importeTarjeta
                    FROM tc_comprobante AS tj 
                    WHERE
                    tj.codigo_movimiento =".$codMov;
    
    $arrTarjetas = array();
    $hacerTj = mysqli_query($link,$sqlTarjetas);
    while($tj = mysqli_fetch_assoc($hacerTj)){
        $arrTarjetas[] = $tj;
    }

   }

// * transferencias
   if($recibo->Transferencia<>0){
       $textoTransferencia = $recibo->Banco . ' - Cuenta: '.$recibo->numeroCuentaBanco . ' - importe: <strong>$'.number_format($recibo->Transferencia,2,",",".").'</strong>';

   }

   // facturas a imputar.
   if($recibo->TipoRecibo=='Imputacion'){
       $sqlImp=" SELECT 
                    impu.Fecha,
                    DATE_FORMAT(impu.Fecha,'%d/%m/%Y') as fechaB,
                    impu.TipoComprobante,
                    impu.NroComprobante,
                    impu.CondVenta,
                    impu.Importe,
                    impu.Cancelado,
                    impu.CanceladoActual,
                    impu.Saldo,
                    impu.Vencimiento,
                    DATE_FORMAT(impu.Vencimiento,'%d/%m/%Y')as vencimientoB
                FROM
                recibo_factura_par AS impu
                WHERE
                    impu.ReciboMov=".$codMov;
       $hacerImp = mysqli_query($link,$sqlImp) or die('No puedo recuperar el pedido'.mysqli_error($link).'<br>'.$sqlImp);
       $arrImpu=array();
       while($im=mysqli_fetch_assoc($hacerImp)){
           $arrImpu[]=$im;
       }
   }
   
   
    
    
    /*
     * Dibujo del comprobante.
     * =================================================================================
     */

    $muestraR = "";        
    $muestraR .='    <div id="comprobante">'.PHP_EOL;
    $muestraR .='    <div id="cabeceraComprobante">'.PHP_EOL;
    //$muestraR .='    <input type="image" src="_img/imprimir.png" id="imprimir" value="Imprimir">'.PHP_EOL;
    $muestraR .='        <div id="izquierda">'.PHP_EOL;
    //$muestraR .='           <img src="data:image/jpg;base64,'.traeLogo64($link).'" alt="'.$empresa->nombre_empresa.'" title="'.$empresa->nombre_empresa.'" class="asBlock" />';
    $muestraR .='           <img src="'.traeLogo($link,$rutaLogo,$empresa->cuit_empresa).'" alt="'.$empresa->nombre_empresa.'" title="'.$empresa->nombre_empresa.'" class="asBlock" />'.PHP_EOL;
//$photo = "<img src=\"data:image/jpg;base64, ".$kad_photo."\"/>";
    $muestraR .='        </div>'.PHP_EOL;
    $muestraR .='        <div id="tipoComp"><strong>REC</strong><div id="leyenda">[Documento no válido como factura]</div></div>'.PHP_EOL;
    $muestraR .='        <div id="derecha">'.PHP_EOL;
    $muestraR .='               <ul class="datoComprobante">'.PHP_EOL;
    $muestraR .='                   <li class="destacadoComp"><strong>RECIBO</strong></li>'.PHP_EOL;
    $muestraR .='                   <li class="destacado"><strong>Nro: '.$recibo->NroComprobante.'</strong></li>'.PHP_EOL;
    if($recibo->Detalle=='WEB'):
    $muestraR .='                   <li class="destacado"><strong>WEB</strong></li>'.PHP_EOL;    
    endif;
    $muestraR .='                   <li><strong>Fecha: </strong>'.$recibo->FechaB.'</li>'.PHP_EOL;
    //$muestraR .='                   <li><strong>Usuario: </strong> '.$objVendedor->nombre_usuario .' ' .$objVendedor->apellido_usuario.'</li>';
    $muestraR .='                   <li><strong>Cobrador: </strong> '.$recibo->cobrador.'</li>'.PHP_EOL;
//    $muestraR .='                   <li><strong>Venc.: </strong>'.$recibo->Vencimiento.'</li>';

    $muestraR .='               </ul>'.PHP_EOL;
    $muestraR .='        </div>'.PHP_EOL;
    $muestraR .='        </div>'.PHP_EOL;

    $muestraR .='    <div id="membrete">'.PHP_EOL;
    $muestraR .='       <div class="columna">'.PHP_EOL;
    $muestraR .='               <ul class="datoComprobante">'.PHP_EOL;
    $muestraR .='                   <li class="destacado"><strong>'.$empresa->nombre_empresa.'</strong></li>'.PHP_EOL;
    $muestraR .='                   <li>Tel: '.$empresa->telefono_empresa.'</li>'.PHP_EOL;
    $muestraR .='                   <li>E-mail: '.$empresa->email_empresa.'</li>'.PHP_EOL;
    $muestraR .='                   <li>IVA: '.$empresa->iva_empresa.'</li>'.PHP_EOL;
    $muestraR .='               </ul>'.PHP_EOL;
    $muestraR .='       </div>'.PHP_EOL;
    $muestraR .='       <div class="columna">'.PHP_EOL;
    $muestraR .='               <ul class="datoComprobante">'.PHP_EOL;
    $muestraR .='                   <li>&nbsp;</li>'.PHP_EOL;
    $muestraR .='                   <li>CUIT: '.$empresa->cuit_empresa.'</li>'.PHP_EOL;
    $muestraR .='                   <li>Inic. Act.: '.  $empresa->iniact_empresa.'</li>'.PHP_EOL;
    $muestraR .='                   <li>Ing. Brutos: '.$empresa->ingbrutos_empresa.'</li>'.PHP_EOL;
    $muestraR .='               </ul>'.PHP_EOL;
    $muestraR .='       </div>'.PHP_EOL;
    $muestraR .='    </div>'.PHP_EOL;  
    $muestraR .='    <div id="datosCliente">'.PHP_EOL;
    $muestraR .='       <div class="columna">'.PHP_EOL;

    $muestraR .='               <ul class="datoComprobante">'.PHP_EOL;
    $muestraR .='                   <li>Cliente: <strong>'.$recibo->nombre_cliente.' - ('.$recibo->Codigo.')</strong></li>'.PHP_EOL;


    $muestraR .='                   <li>CUIT: <strong>'.$recibo->CUIT.'</strong></li>'.PHP_EOL;

    $muestraR .='               </ul>'.PHP_EOL;
    $muestraR .='       </div>'.PHP_EOL;
    $muestraR .='       <div class="columna">'.PHP_EOL;
    $muestraR .='               <ul class="datoComprobante">'.PHP_EOL;


    $muestraR .='               </ul>'.PHP_EOL;
    $muestraR .='       </div>'.PHP_EOL;
    $muestraR .='    </div>'.PHP_EOL;

    $muestraR .='   <div id="cuerpoComprobante">'.PHP_EOL;
    $muestraR .='       <h3><strong>Medios de cobro</strong></h3>   '.PHP_EOL;
//  * EFECTIVO
//  ============================================================================        
    if(isset($efectivo)){
       
        // $muestraR .='       <p>Medios de cobro</p>   '.PHP_EOL;
        $muestraR .='       <p style="padding-left:5px;"> > Efectivo: <strong>$'.number_format($efectivo['monto'],2,",",".").'</strong>  Caja: <strong>'.$efectivo['caja'].'</strong></p>   '.PHP_EOL;
         
        
    }

//  * CHEQUES
//  ============================================================================        
    if(isset($arrCheques)){
        // $muestraR .='   <div id="cuerpoComprobante">'.PHP_EOL;
        $muestraR .='   <p style="padding-left:5px;"> <strong> > Cheques de tercero:</strong></p>'.PHP_EOL;
        $muestraR .='        <table id="tablaComp" >'.PHP_EOL;
        $muestraR .='            <thead>'.PHP_EOL;
        $muestraR .='                <tr>'.PHP_EOL;
        $muestraR .='                    <th class="liso">Nro cheque</th>'.PHP_EOL;;
        $muestraR .='                    <th class="liso">Banco</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso">Emisión</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso">Cobro</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso">Librador</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso" style="text-align:right">Importe</th>'.PHP_EOL;                  
        $muestraR .='                </tr>'.PHP_EOL;
        $muestraR .='            </thead>'.PHP_EOL;
        $muestraR .='            <tbody>'.PHP_EOL;
        foreach($arrCheques as $ch){
            $muestraR .='<tr>'.PHP_EOL;
            $muestraR .='<td class="derecha">'. $ch["NroCheque"].'</td>'.PHP_EOL;
            $muestraR .='<td class="izquierda">'.$ch["nombreBanco"].'</td>'.PHP_EOL;            
            $muestraR .='<td class="izquierda">'.$ch["fEmision"].'</td>'.PHP_EOL;
            $muestraR .='<td class="izquierda">'.$ch["fCobro"].'</td>'.PHP_EOL;
            $muestraR .='<td class="izquierda">'.$ch["Librador"].'</td>'.PHP_EOL;
            $muestraR .='<td class="derecha">$'.number_format($ch["Importe"],2,",",".").'</td>'.PHP_EOL;
            
            
            
            
            $muestraR .='</tr>'.PHP_EOL;
        }
        $muestraR .='                   <tr><td colspan="5" class="derecha"><strong>Total</strong></td><td class="derecha subtotal"><strong>$'.number_format($recibo->Cheque,2,",",".").'</strong></td></tr>'.PHP_EOL;
        $muestraR .='               </tbody>'.PHP_EOL;
        $muestraR .='           </table>'.PHP_EOL;
        // $muestraR .='   </div>';
    }
    // * # TRANSFERENCIA BANCARIA
    // ==================================================
    if(isset($textoTransferencia)){
       
        $muestraR .='<p style="padding-left:5px;">
        
        <strong>Transferencia Bancaria:</strong> <br><span style="padding-left:5px;">'.$textoTransferencia.'</span></p>'.PHP_EOL;
    }

    // * # TARJETAS 
    // ==================================================
    if(isset($arrTarjetas)&&!empty($arrTarjetas)){
        $muestraR .='   <p style="padding-left:5px;"><strong>Tarjetas Crédito / Débito:</strong></p>'.PHP_EOL;
        $muestraR .='        <table id="tablaComp" >'.PHP_EOL;
        $muestraR .='            <thead>'.PHP_EOL;
        $muestraR .='                <tr>'.PHP_EOL;
        $muestraR .='                    <th class="liso" >Tarjeta</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso">Plan</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso centro" >Cuotas</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso derecha" >Importe Cuota</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso">Cupon</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso">Número</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso derecha" >Importe</th>'.PHP_EOL;                  
        $muestraR .='                </tr>'.PHP_EOL;
        $muestraR .='            </thead>'.PHP_EOL;
        $muestraR .='            <tbody>'.PHP_EOL;
        foreach($arrTarjetas as $tj){
            $muestraR .='<tr>'.PHP_EOL;
            $muestraR .='<td>'. $tj["tarjeta"].'</td>'.PHP_EOL;
            $muestraR .='<td class="izquierda">'.$tj["plan"].'</td>'.PHP_EOL;            
            $muestraR .='<td class="centro">'.$tj["cuotas"].'</td>'.PHP_EOL;
            $muestraR .='<td class="derecha">$'.number_format($tj["importeCuota"],2,",",".").'</td>'.PHP_EOL;
            $muestraR .='<td class="izquierda">'.$tj["cupon"].'</td>'.PHP_EOL;
            $muestraR .='<td class="izquierda">'.$tj["numero"].'</td>'.PHP_EOL;
            $muestraR .='<td class="derecha">$'.number_format($tj["importeTarjeta"],2,",",".").'</td>'.PHP_EOL;
            
            
            
            
            $muestraR .='</tr>'.PHP_EOL;
        }
        $muestraR .='                   <tr><td colspan="6" class="derecha"><strong>Total</strong></td><td class="subtotal derecha">$'.number_format($recibo->Tarjeta,2,",",".").'</td></tr>'.PHP_EOL;
        $muestraR .='               </tbody>'.PHP_EOL;
        $muestraR .='           </table>'.PHP_EOL;
    }
    
//  IMPUTACIONES
//  ============================================================================            
    if(isset($arrImpu)){
        // $muestraR .='   <div id="cuerpoComprobante">'.PHP_EOL;
        $muestraR .='   <p><strong>Imputación de comprobantes:</strong></p>'.PHP_EOL;
        $muestraR .='        <table id="tablaComp" >'.PHP_EOL;
        $muestraR .='            <thead>'.PHP_EOL;
        $muestraR .='                <tr>'.PHP_EOL;
        $muestraR .='                    <th class="liso">Fecha</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso">Comp</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso">Nro Comp</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso">Cond Venta</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso">Vencimiento</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso" style="text-align:right">Importe</th>'.PHP_EOL;  
        $muestraR .='                    <th class="liso" style="text-align:right">Canc Total</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso" style="text-align:right">Canc Actual</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso" style="text-align:right">Saldo</th>'.PHP_EOL;
        $muestraR .='                    <th class="liso">Detalle</th>'.PHP_EOL;
        $muestraR .='                </tr>'.PHP_EOL;
        $muestraR .='            </thead>'.PHP_EOL;
        $muestraR .='            <tbody>'.PHP_EOL;
        foreach($arrImpu as $f){
            $muestraR .='<tr>'.PHP_EOL;
            $muestraR .='<td class="izquierda">'. $f["fechaB"].'</td>'.PHP_EOL;
            $muestraR .='<td class="izquierda">'.$f["TipoComprobante"].'</td>'.PHP_EOL;            
            $muestraR .='<td class="izquierda">'.$f["NroComprobante"].'</td>'.PHP_EOL;
            $muestraR .='<td class="izquierda">'.$f["CondVenta"].'</td>'.PHP_EOL;
            $muestraR .='<td class="izquierda">'.$f["vencimientoB"].'</td>'.PHP_EOL;
            $muestraR .='<td class="derecha">$'.number_format($f["Importe"],2,",",".").'</td>'.PHP_EOL;
            $muestraR .='<td class="derecha">$'.number_format($f["Cancelado"],2,",",".").'</td>'.PHP_EOL;
            $muestraR .='<td class="derecha">$'.number_format($f["CanceladoActual"],2,",",".").'</td>'.PHP_EOL;
            $muestraR .='<td class="derecha">$'.number_format($f["Saldo"],2,",",".").'</td>'.PHP_EOL;
            $detalle="Comp Imputado";
            if($f["TipoComprobante"]=="REC"){
                $detalle="A favor cliente";
            }
            $muestraR .='<td class="izquierda">'.$detalle.'</td>'.PHP_EOL;
            
            $muestraR .='</tr>'.PHP_EOL;
        }
       
        $muestraR .='               </tbody>'.PHP_EOL;
        $muestraR .='           </table>'.PHP_EOL;
        // $muestraR .='   </div>';
    }
    $muestraR .='   </div>'.PHP_EOL;  
    
    
    $muestraR .='   <div id="pieComprobante">'.PHP_EOL;
    $muestraR .='       <div id="detalle">'.PHP_EOL;   
    $muestraR .='       <p id="importeLetra"><!--<span style="font-family:fontawesome;">&#xf3ed;</span>--><strong>Son Pesos: '.$recibo->importeLetra.' </strong> </p>'.PHP_EOL;
    // $muestraR .='       <div style="width:99%;height:20px;padding-top:30px;text-align:center;"><p>------------------------------------<br>FIRMA CLIENTE</p></div>'.PHP_EOL;
    // $muestraR .='       <div style="width:99%;height:20px;padding-top:30px;text-align:center;"><p>------------------------------------<br>FIRMA RESPONSABLE<br>'
                        // .'</p></div>'.PHP_EOL;
    
    $muestraR .='       </div>'.PHP_EOL;
    $muestraR .='       <div id="importe">'.PHP_EOL;
    $muestraR .='           <table style="margin-top:125px">'.PHP_EOL;
    $muestraR .='               <tr>'.PHP_EOL;
    $muestraR .='                   <td colspan="2" class="izquierda">Descuento</td><td class="derecha">'.$recibo->Descuento.'</td>'.PHP_EOL;
    $muestraR .='               </tr>'.PHP_EOL;
    $muestraR .='               <tr>'.PHP_EOL;
    $muestraR .='                   <td colspan="2" class="izquierda">Retenciones </td><td class="derecha">'.$recibo->Retencion.'</td>'.PHP_EOL;
    $muestraR .='               </tr>'.PHP_EOL;

    $muestraR .='               <tr >'.PHP_EOL;
    $muestraR .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.number_format($recibo->Importe,2,",",".").'</td>'.PHP_EOL;
    $muestraR .='               </tr>'.PHP_EOL;

    $muestraR .='           </table>'.PHP_EOL;
    

    $muestraR .='       </div>'.PHP_EOL;
    $muestraR .='       <div id="final">'.PHP_EOL;

    $muestraR .='       <p>Comprobante generado por: ';
    $muestraR .='       <img src="_img/logo-administranet-ecommerce.png" style="height:25px;"></p>'.PHP_EOL;
    $muestraR .='           <p>Tel:(0261)- 4274480 / 4283071 |  <a href="http://www.administranet.com.ar">www.administranet.com.ar</a></p>'.PHP_EOL;
    $muestraR .='       </div>'.PHP_EOL;
    $muestraR .='   </div>'.PHP_EOL;
    $muestraR .='</div>'.PHP_EOL;

    /* 
     * PDF
     * =========================================================================
     */

    // mpdf 

require_once  '_lib/mpdf2/vendor/autoload.php';
   
// * font awesome
    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];
    // echo '<pre>';
    // print_r($fontData);
    // echo PHP_EOL;
    // print_r($fontDirs);
    // echo '</pre>';
/*
$mpdf = new \Mpdf\Mpdf([
    'fontDir' => array_merge($fontDirs, [
        __DIR__ . '/custom/font/directory',
    ]),
    'fontdata' => $fontData + [
        'frutiger' => [
            'R' => 'Frutiger-Normal.ttf',
            'I' => 'FrutigerObl-Normal.ttf',
        ]
    ],
    'default_font' => 'frutiger'
]);
*/

$mpdf = new \Mpdf\Mpdf([
    'default_font' => 'fontawesome',
    'margin_left' => 5,
	'margin_right' => 5,
	'margin_top' => 4,
	'margin_bottom' => 5,
	'margin_header' => 5,
	'margin_footer' => 7
    ]);


$stylesheet = file_get_contents('_css/pdf.css');
    $mpdf->WriteHTML($stylesheet,1);// constante que es un csss
    $mpdf->WriteHTML($muestraR,2); // que es html body

    $mpdf->SetDisplayMode('fullpage');
    file_put_contents('log/pdf-html'.date('Y-m-d-h-i-s').'.html','<html><header><style>'.$stylesheet.'</style></header><body>'.PHP_EOL.$muestraR.'</body>');
    //$mpdf->Output();
    
    // * descarga no hago nada
    if($tipoSalida=='D'){
        $mpdf->Output('Rec-'.$recibo->NroComprobante.'.pdf',$tipoSalida);
        return true;
    }
    // * S hago el binario. lo regreso
    if($tipoSalida=='S'){
        $volver=$mpdf->Output('',$tipoSalida);
        return $volver;
    }

    
    
}

/**
 * FUNCIONES DE fin- comprobante PARA ENVIO DE EMAIL de COMPROBANTES.
 */


 /**
 * #Arma el html de envio del email V1
 * 
 * */ 
function trae_html($datos){
    $url = $datos["urlComprobante"];// nombre del archivo en formato 64 
    $vendedor = $datos["vendedor"];
    $empresa = $datos["empresa"];
    $tipoComp = $datos["tipoComp"];
    $nroComp = $datos["comprobante"];
    $total = $datos["total"];
    $fecha = $datos["fecha"];
    $destinatario = $datos["cliente"];
    // pasar el codigo de movimiento.
    

    $rcss = "_lib/mail/p-comp.css"; //ruta de archivo css
    $fcss = fopen($rcss, "r"); //abrir archivo css
    $scss = fread($fcss, filesize($rcss)); //leer contenido de css
    fclose($fcss); //cerrar archivo css
    $txtHtml = '';
    $txtHtml .= '<!DOCTYPE html>';
    $txtHtml .= '<head>';
    $txtHtml .= '<meta charset="UTF-8">';
    $txtHtml .= '<style>' . $scss . '</style>';
    $txtHtml .= '    <title>Envio de comprobantes electrónicos</title>';
    $txtHtml .= '</head>';
    $txtHtml .= '<body>';
    $txtHtml .= '    <div id="contenedor">';
    $txtHtml .= '        <div id="cabecera">Envio electrónico de comprobante</div>';
    $txtHtml .= '        <div id="cuerpo">';
    $txtHtml .= '            <p>Fecha: ' . $fecha . '</p>';
    $txtHtml .= '            <p>Gracias ' . $destinatario . !'</p>';
    $txtHtml .= '            <p>Descargue su comprobante aquí <a alt="' . $url . '" title="' . $url . '" href="' . $url . '" target="blank">' . $tipoComp . ' ' . $nroComp . ' $' . number_format($total, 2, ",", ".") . '</a></p>';
    $txtHtml .= '        </div>';
    $txtHtml .= '        <div id="firma">';
    $txtHtml .= '            <div id="logoFirma">';
    $txtHtml .='                <img src="'.$empresa["logo"].'"/>';
    $txtHtml .= '                <img src="cid:logoempresa"/>';

    $txtHtml .= '            </div>';
    $txtHtml .= '            <div id="textoFirma">';
    $txtHtml .= '                <label><strong>' . $vendedor . '</strong></label><br>';
    $txtHtml .= '                <label>' . $empresa["nombreempresa"] . '</label><br>';
    $txtHtml .= '                <label>' . $empresa["domicilioempresa"] . '</label><br>';
    $txtHtml .= '                <label>Tel: ' . $empresa["telefonoempresa"] . '</label><br>';
    $txtHtml .= '                <label><a href="' . $empresa["urlempresa"] . '" target="_blank">' . $empresa["urlempresa"] . '</a></label>';
    $txtHtml .= '            </div>';
    $txtHtml .= '        </div>';
    $txtHtml .= '        <div id="pie">';
    $txtHtml .= '            Mail generado por  <a href="https://www.administranet.com.ar" target="_blank">administraNET gestión e-commerce</a> <img src="cid:logoadministranet"/>';
    $txtHtml .= '        </div>';
    $txtHtml .= '    </div>';
    $txtHtml .= '</body>';
    $txtHtml .= '</html>';
    return $txtHtml;
}


 /**
 * # Arma el html de envio del email V2
 * 
 * */ 
function trae_html_mail($datos){
    $urlComprobante = $datos['urlComprobante'];// nombre del archivo en formato 64 
    $vendedor = $datos['vendedor'];
    $empresa = $datos['empresa'];
    $tipoComp = $datos['tipoComp'];
    $nroComp = $datos['comprobante'];
    $total = number_format($datos['total'],2,",",".");
    $fecha = $datos['fecha'];
    
    $queNombre = $datos['cliente'];

    // pasar el codigo de movimiento.
    
    define('COLORCABECERAMAIL','#ffffff');



# nuevo pedido con diseño html. copado.

    $txtHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . PHP_EOL;

    $txtHtml .= '<html xmlns="http://www.w3.org/1999/xhtml">' . PHP_EOL;

    $txtHtml .= '<head>' . PHP_EOL;

    $txtHtml .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . PHP_EOL;

    $txtHtml .= '    <title>Comprobantes electrónicos</title>' . PHP_EOL;

    $txtHtml .= '    <meta name="viewport" content="width=device-width, initial-scale=1.0" />' . PHP_EOL;

    $txtHtml .= '</head>' . PHP_EOL;


    $txtHtml .= '<body style="margin: 0; padding: 0;">' . PHP_EOL;

    $txtHtml .= '    <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f5f5f5" style="font-family: sans-serif;">' . PHP_EOL;

    $txtHtml .= '        <tr>' . PHP_EOL;
    $txtHtml .= '            <td align="center">' . PHP_EOL;

    $txtHtml .= '                <!--======================= Encabezado y Agradecimiento =======================-->' . PHP_EOL;
    $txtHtml .= '                <table cellpadding="0" cellspacing="0" border="0" width="540px" align="center" bgcolor="'.COLORCABECERAMAIL.'" style="padding: 10px 0 10px 0;">' . PHP_EOL;
    $txtHtml .= '                    <tr>' . PHP_EOL;
    $txtHtml .= '                        <td align="center">' . PHP_EOL;
    $txtHtml .= '                            <img src="cid:logo-empresa-png" style="display: block;max-height:80px;" />' . PHP_EOL;
    $txtHtml .= '                        </td>' . PHP_EOL;
    $txtHtml .= '                    </tr>' . PHP_EOL;
    $txtHtml .= '                </table>' . PHP_EOL;
    $txtHtml .= '                <!--======================= FIN =======================-->' . PHP_EOL;

    $txtHtml .= '            </td>' . PHP_EOL;
    $txtHtml .= '        </tr>' . PHP_EOL;

    $txtHtml .= '        <tr>' . PHP_EOL;
    $txtHtml .= '            <td align="center">' . PHP_EOL;

    $txtHtml .= '                <!--======================= Encabezado y Agradecimiento =======================-->' . PHP_EOL;
    $txtHtml .= '                <table cellpadding="0" cellspacing="0" border="0" width="540px" align="center" bgcolor="#f5f5f5">' . PHP_EOL;
    $txtHtml .= '                    <tr>' . PHP_EOL;
    $txtHtml .= '                        <td align="center">' . PHP_EOL;
    $txtHtml .= '                            <h1 style="font-size: 24px; color:#333333">Muchas gracias por confiar en nosotros!</h1>' . PHP_EOL;
    $txtHtml .= '                        </td>' . PHP_EOL;
    $txtHtml .= '                    </tr>' . PHP_EOL;
    $txtHtml .= '                </table>' . PHP_EOL;
    $txtHtml .= '                <!--======================= FIN =======================-->' . PHP_EOL;

    $txtHtml .= '            </td>' . PHP_EOL;
    $txtHtml .= '        </tr>' . PHP_EOL;


    $txtHtml .= '        <tr>' . PHP_EOL;
    $txtHtml .= '            <td align="center">' . PHP_EOL;

    $txtHtml .= '                <!--======================= Estado del pedido =======================-->' . PHP_EOL;
    $txtHtml .= '                <table cellpadding="0" cellspacing="0" border="0" width="540px" align="center" bgcolor="#f5f5f5">' . PHP_EOL;

    $txtHtml .= '                    <tr>' . PHP_EOL;
    $txtHtml .= '                        <td align="center">' . PHP_EOL;
    $txtHtml .= '                            <h2 style="font-size: 18px; color:#333333"><strong>' . $queNombre . '</strong></h2>' . PHP_EOL;
    $txtHtml .= '                            <p style="color: rgb(255,255,255);' . PHP_EOL;
    $txtHtml .= '                            background: rgb(163, 164, 183);' . PHP_EOL;
    $txtHtml .= '                            margin: 10px 10px 10px 10px;' . PHP_EOL;
    $txtHtml .= '                            border-radius: 4px;' . PHP_EOL;
    $txtHtml .= '                            border-width: 12px 12px;' . PHP_EOL;
    $txtHtml .= '                            border-style: solid;' . PHP_EOL;
    $txtHtml .= '                            border-color: rgb(163, 164, 183);' . PHP_EOL;
    $txtHtml .= '                            display: table;' . PHP_EOL;
    $txtHtml .= '                            font-size: 18px;' . PHP_EOL;
    $txtHtml .= '                            font-weight: bold;' . PHP_EOL;
    $txtHtml .= '                            text-align: center;' . PHP_EOL;
    $txtHtml .= '                            text-decoration: none;">'.$tipoComp.' - ' . $nroComp . '</p>' . PHP_EOL;
    $txtHtml .= '                        </td>' . PHP_EOL;
    $txtHtml .= '                    </tr>' . PHP_EOL;
    $txtHtml .= '                </table>' . PHP_EOL;
    $txtHtml .= '                <!--======================= FIN =======================-->' . PHP_EOL;

    $txtHtml .= '            </td>' . PHP_EOL;
    $txtHtml .= '        </tr>' . PHP_EOL;
    
    $txtHtml .= '        <tr>' . PHP_EOL;
    $txtHtml .= '            <td align="center">' . PHP_EOL;

    $txtHtml .= '                <!--======================= Datos del Comprobante =======================-->' . PHP_EOL;
    $txtHtml .= '                <table cellpadding="0" cellspacing="0" border="0" width="520px" align="center" bgcolor="#fff" style="margin: 10px 10px 10px 10px; padding: 10px 10px 10px 10px; border-width: 1px 1px 2px; border-style: solid; border-color: #dedede;">' . PHP_EOL;


    $txtHtml .= '                    <tr>' . PHP_EOL;
    $txtHtml .= '                        <td colspan="2" align="center">' . PHP_EOL;
    $txtHtml .= '                            <h2 style="font-size: 18px; color:#333333">Datos</h2>' . PHP_EOL;
    $txtHtml .= '                        </td>' . PHP_EOL;
    $txtHtml .= '                    </tr>' . PHP_EOL;
    
    $txtHtml .= '                    <tr>' . PHP_EOL;
    $txtHtml .= '                        <td style="width: 30%;">' . PHP_EOL;
    $txtHtml .= '                            <p style="font-size: 14px; font-weight: bold; color:#333333">Fecha:</p>' . PHP_EOL;
    $txtHtml .= '                        </td>' . PHP_EOL;
    $txtHtml .= '                        <td style="width: 70%;">' . PHP_EOL;
    $txtHtml .= '                            <p style="font-size: 14px; color:#333333">' . $fecha . '</p>' . PHP_EOL;
    $txtHtml .= '                        </td>' . PHP_EOL;
    $txtHtml .= '                    </tr>' . PHP_EOL;

    // $txtHtml .= '                    <tr>' . PHP_EOL;
    // $txtHtml .= '                        <td style="width: 30%;">' . PHP_EOL;
    // $txtHtml .= '                            <p style="font-size: 14px; font-weight: bold; color:#333333">Comprobante:</p>' . PHP_EOL;
    // $txtHtml .= '                        </td>' . PHP_EOL;
    // $txtHtml .= '                        <td style="width: 70%;">' . PHP_EOL;
    // $txtHtml .= '                            <p style="font-size: 14px; color:#333333">' . $tipoComp.' - '.$nroComp. '</p>' . PHP_EOL;
    // $txtHtml .= '                        </td>' . PHP_EOL;
    // $txtHtml .= '                    </tr>' . PHP_EOL;

    $txtHtml .= '                    <tr>' . PHP_EOL;
    $txtHtml .= '                        <td style="width: 30%;">' . PHP_EOL;
    $txtHtml .= '                            <p style="font-size: 14px; font-weight: bold; color:#333333">Importe:</p>' . PHP_EOL;
    $txtHtml .= '                        </td>' . PHP_EOL;
    $txtHtml .= '                        <td style="width: 70%;">' . PHP_EOL;
    $txtHtml .= '                            <p style="font-size: 14px; color:#333333">$' . $total . '</p>' . PHP_EOL;
    $txtHtml .= '                        </td>' . PHP_EOL;
    $txtHtml .= '                    </tr>' . PHP_EOL;

       
    $txtHtml .= '                </table>' . PHP_EOL;
    $txtHtml .= '                <!--======================= FIN =======================-->' . PHP_EOL;

    $txtHtml .= '            </td>' . PHP_EOL;
    $txtHtml .= '        </tr>' . PHP_EOL;

    $txtHtml .= '        <tr>' . PHP_EOL;
    $txtHtml .= '            <td align="center">' . PHP_EOL;
   

    

    # si hay LEYENDA la colocamos.
    if(defined('LEYENDAMAIL') && LEYENDAMAIL!=''){
    $txtHtml .= '        <tr>' . PHP_EOL;
    $txtHtml .= '            <td align="center">' . PHP_EOL;

    $txtHtml .= '                <!--======================= Leyenda / advertencia =======================-->' . PHP_EOL;
    $txtHtml .= '                <table cellpadding="0" cellspacing="0" border="0" width="520px" align="center" bgcolor="#fff" style="margin: 10px 10px 10px 10px; padding: 10px 10px 10px 10px; border-width: 1px 1px 2px; border-style: solid; border-color: #dedede;">' . PHP_EOL;

    $txtHtml .= '                    <tr>' . PHP_EOL;
    $txtHtml .= '                        <td colspan="2" align="center">' . PHP_EOL;
    $txtHtml .= '                            <h2 style="font-size: 18px; color:#333333">Aviso</h2>' . PHP_EOL;
    $txtHtml .= '                        </td>' . PHP_EOL;
    $txtHtml .= '                    </tr>' . PHP_EOL;

    $txtHtml .= '                    <tr>' . PHP_EOL;
    $txtHtml .= '                        <td style="width: 100%;">' . PHP_EOL;
    $txtHtml .= '                            <p style="font-size: 14px; color:#333333">' . PHP_EOL;
    $txtHtml .= '                               ' . LEYENDAMAIL . '' . PHP_EOL;
    $txtHtml .= '                            </p>' . PHP_EOL;
    $txtHtml .= '                        </td>' . PHP_EOL;
    $txtHtml .= '                    </tr>' . PHP_EOL;
    $txtHtml .= '                </table>' . PHP_EOL;
    $txtHtml .= '                <!--======================= FIN =======================-->' . PHP_EOL;

    $txtHtml .= '            </td>' . PHP_EOL;
    $txtHtml .= '        </tr>' . PHP_EOL;
    }
    # fin de leyenda

    // $txtHtml .= '        <tr>' . PHP_EOL;
    // $txtHtml .= '            <td align="center">' . PHP_EOL;

    // $txtHtml .= '                <!--======================= Descarga  =======================-->' . PHP_EOL;
    // $txtHtml .= '                <table cellpadding="0" cellspacing="0" border="0" width="540px" align="center" bgcolor="#f5f5f5">' . PHP_EOL;
    // $txtHtml .= '                    <tr class="descarga">' . PHP_EOL;
    // $txtHtml .= '                        <td colspan="2" align="center" valign="middle">' . PHP_EOL;
    // $txtHtml .= '                            <a href="url" style="color: rgb(255,255,255);' . PHP_EOL;
    // $txtHtml .= '                            background: rgb(220, 25, 100);' . PHP_EOL;
    // $txtHtml .= '                            margin: 10px 10px 10px 10px;' . PHP_EOL;
    // $txtHtml .= '                            border-radius: 4px;' . PHP_EOL;
    // $txtHtml .= '                            border-width: 12px 12px;' . PHP_EOL;
    // $txtHtml .= '                            border-style: solid;' . PHP_EOL;
    // $txtHtml .= '                            border-color: rgb(220, 25, 100);' . PHP_EOL;
    // $txtHtml .= '                            display: block;' . PHP_EOL;
    // $txtHtml .= '                            font-size: 18px;' . PHP_EOL;
    // $txtHtml .= '                            font-weight: 400;' . PHP_EOL;
    // $txtHtml .= '                            text-align: center;' . PHP_EOL;
    // $txtHtml .= '                            text-decoration: none;">Comprobante: '.$urlComprobante.'</a>' . PHP_EOL;
    // $txtHtml .= '                        </td>' . PHP_EOL;
    // $txtHtml .= '                    </tr>' . PHP_EOL;
    // $txtHtml .= '                </table>' . PHP_EOL;
    // $txtHtml .= '                <!--======================= FIN =======================-->' . PHP_EOL;

    // $txtHtml .= '            </td>' . PHP_EOL;
    // $txtHtml .= '        </tr>' . PHP_EOL;

    $txtHtml .= '        <tr>' . PHP_EOL;
    $txtHtml .= '            <td align="center">' . PHP_EOL;

    $txtHtml .= '                <!--======================= Pie =======================-->' . PHP_EOL;
    $txtHtml .= '                <table cellpadding="0" cellspacing="0" border="0" width="100%" align="center" bgcolor="#ededed">' . PHP_EOL;
    $txtHtml .= '                    <tr>' . PHP_EOL;
    $txtHtml .= '                        <td colspan="2" align="center">' . PHP_EOL;
    $txtHtml .= '                            <p style="font-size: 16px; color:#333333">';

    $txtHtml .= '                               <span>' . $vendedor . '</span>' . PHP_EOL;
    $txtHtml .= '                            </p>' . PHP_EOL;

    $txtHtml .= '                            <p style="font-size: 14px; color:#333333">';
    $txtHtml .= '                               <span style="font-weight: bold;">' . $empresa["nombreempresa"] . '</span>' . PHP_EOL;
    $txtHtml .= '                            <br> ' . $empresa["domicilioempresa"] . '' . PHP_EOL;
    $txtHtml .= '                            <br> Tel: ' . $empresa["telefonoempresa"] . '' . PHP_EOL;
    $txtHtml .= '                            <br> ' . $empresa["urlempresa"] . '' . PHP_EOL;
    $txtHtml .= '                            </p>' . PHP_EOL;
    $txtHtml .= '                        </td>' . PHP_EOL;
    $txtHtml .= '                    </tr>' . PHP_EOL;
    $txtHtml .= '                </table>' . PHP_EOL;
    $txtHtml .= '                <!--======================= FIN =======================-->' . PHP_EOL;

    $txtHtml .= '            </td>' . PHP_EOL;
    $txtHtml .= '        </tr>' . PHP_EOL;

    $txtHtml .= '        <tr>' . PHP_EOL;
    $txtHtml .= '            <td align="center">' . PHP_EOL;

    $txtHtml .= '                <table cellpadding="0" cellspacing="0" border="0" width="100%" align="center" bgcolor="#ffffff">' . PHP_EOL;
    $txtHtml .= '                    <tr class="administranet">' . PHP_EOL;
    $txtHtml .= '                        <td colspan="2" align="center" valign="middle">' . PHP_EOL;
    $txtHtml .= '                            <p>Mail generado por <a href="https://www.administranet.com.ar" target="_blank">administraNET gestión e-commerce</a></p>' . PHP_EOL;
    $txtHtml .= '                            <img src="cid:adm-logo-png" style="display: block;" />' . PHP_EOL;
    $txtHtml .= '                        </td>' . PHP_EOL;
    $txtHtml .= '                    </tr>' . PHP_EOL;
    $txtHtml .= '                </table>' . PHP_EOL;
    $txtHtml .= '                <!--======================= FIN =======================-->' . PHP_EOL;

    $txtHtml .= '            </td>' . PHP_EOL;
    $txtHtml .= '        </tr>' . PHP_EOL;

    $txtHtml .= '    </table>' . PHP_EOL;

    $txtHtml .= '</body>' . PHP_EOL;

    $txtHtml .= '</html>' . PHP_EOL;

    return $txtHtml;
}

/**
 * #Busca datos del comprobante a enviar por email.
 * =========================================================================
 */

function traer_info_comprobante($connV,$codigoMovimiento,$tipo){
    $vuelta = array();
    $arrCuentaCliente = array('FA','FB','FC','NCA','NCB','NCC','NDA','NDB','REC');
    $arrCompPed = array('PED','REM','DEV','PRE');
    $tablaPrincipal ='';
    $campos='';

    // cuenta cliente
    if(in_array($tipo,$arrCuentaCliente)){   
        $tablaPrincipal ='cuentacliente';
        $campos .='cta.NroCompBusq, '.PHP_EOL;    
        $campos .='cta.NroComprobante, '.PHP_EOL;
        $campos .='cta.ImporteCobro, '.PHP_EOL;
        $campos .='cta.ImporteVenta, '.PHP_EOL;
        $campos .='COALESCE(cta.ImporteVenta,cta.ImporteCobro) AS totalComprobante, '.PHP_EOL;
        $campos .='cta.Fecha, '.PHP_EOL;
        $campos .='cta.TipoComprobante '.PHP_EOL;
        

    }
    // comp ped
    if(in_array($tipo,$arrCompPed)){
        $tablaPrincipal ='comp_ped';
        $campos .='cta.NroCompBusq, '.PHP_EOL;    
        $campos .='cta.NroComprobante, '.PHP_EOL;        
        $campos .='cta.ImporteVenta AS totalComprobante, '.PHP_EOL;
        $campos .='cta.Fecha, '.PHP_EOL;
        $campos .='cta.TipoComprobante '.PHP_EOL;
        

    }

    if($tablaPrincipal!=''){
        $sqlComprobante = "SELECT 
                            ".$campos."
                            FROM ".$tablaPrincipal." AS cta                             
                            WHERE 
                            cta.codigoMovimiento=".$codigoMovimiento;
        $hcomp= mysqli_query($connV,$sqlComprobante);
        if($hcomp){
            $vuelta = mysqli_fetch_assoc($hcomp);
        }
        // error sql
        if(!$hcomp){
            file_put_contents('log/err-infoComprobante'.date('Y-m-d').'.txt','ERROR:'.mysqli_error($connV).PHP_EOL.'SQL::'.$sqlComprobante.PHP_EOL,FILE_APPEND);
        }                    
    }
    


    return $vuelta;
}

/** 
 * proceso y envio el mail generio por eso no se que comprobante es.
 * =========================================================================
*/
function enviar_mail_comprobante($connV,$datos,$archivo)
{
    require '_lib/mail/PHPMailerAutoload.php';

            //echo "<pre>";
            //print_r($datos);
            //echo "</pre>";
    //$p = json_decode($datos, true);
    //        echo "<pre>";
    //        var_dump($p);
    //        echo "</pre>";

    //$p = json_decode(file_get_contents("php://input"),TRUE);
    /*RECOGER VALORES ENVIADOS DESDE INDEX.PHP*/

    //    $sDestino = $_POST['txtDestin'];
    //    $sAsunto = $_POST['txtAsunto'];
    //    $sMensaje = $_POST['txtMensa'];

    $url = $datos['urlComprobante'];// nombre del archivo en formato 64 
    $vendedor = $datos['vendedor'];
    $empresa = $datos['empresa'];
    $tipoComp = $datos['tipoComp'];
    $nroComp = $datos['comprobante'];
    $total = $datos['total'];
    $fecha = $datos['fecha'];
    $cliente = $datos['cliente'];
    // pasar el codigo de movimiento.
    $mailvendedor = $datos['correo']['nombre_usuario'];
    $fotoEmpresa = traeLogo($connV,null,$datos['empresa']['cuitempresa']);

    
    /*CONFIGURACIÓN DE CLASE*/
    $mail = new PHPMailer;
    try {
        $mail->isSMTP(); //Indicar que se usará SMTP
        $mail->CharSet = 'UTF-8'; //permitir envío de caracteres especiales (tildes y ñ)
        /*CONFIGURACIÓN DE DEBUG (DEPURACIÓN)*/
        $mail->SMTPDebug = 0; //Mensajes de debug; 0 = no mostrar (en producción), 1 = de cliente, 2 = de cliente y servidor
        $mail->Debugoutput = 'html'; //Mostrar mensajes (resultados) de depuración(debug) en html
        /*CONFIGURACIÓN DE PROVEEDOR DE CORREO QUE USARÁ EL EMISOR(GMAIL)*/
        $mail->Host = $datos["correo"]["nombre_servidor_smtp"]; //'smtp.gmail.com'; //Nombre de host
        // $mail->Host = gethostbyname('smtp.gmail.com'); // Si su red no soporta SMTP sobre IPv6
        $mail->Port = 587; //Puerto SMTP, 587 para autenticado TLS
        $mail->SMTPSecure = 'tls'; //Sistema de encriptación - ssl (obsoleto) o tls
        $mail->SMTPAuth = true; //Usar autenticación SMTP
        $mail->SMTPOptions = array(
            'ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true)
        ); //opciones para "saltarse" comprobación de certificados (hace posible del envío desde localhost)
        //CONFIGURACIÓN DEL EMISOR
        $mail->Username = $datos["correo"]["nombre_usuario"];
        $mail->Password = $datos["correo"]["pass_usuario"];
        //            echo "pppp:". $p["correo"]["pass_usuario"];
        //            echo " nombre:". $p["correo"]["nombre_usuario"];

        $mail->setFrom($datos["correo"]["nombre_usuario"], 'Notificador: ' . $empresa["nombreempresa"]);

        //CONFIGURACIÓN DEL MENSAJE, EL CUERPO DEL MENSAJE SERA UNA PLANTILLA HTML QUE INCLUYE IMAGEN Y CSS
        $mail->Subject = $empresa["nombreempresa"] . " " . $tipoComp . " " . $nroComp . " Comprobante Electrónico"; //asunto del mensaje
        //incrustar imagen para cuerpo de mensaje(no confundir con Adjuntar)
        $mail->AddEmbeddedImage($fotoEmpresa, 'logo-empresa-png'); //ruta de archivo de imagen
        $mail->AddEmbeddedImage('_lib/mail/logo-administranet-ecommerce.png', 'adm-logo-png'); //ruta de archivo de imagen

        // crear el attachment base 64
        $mail->AddStringAttachment($archivo, $url);

        $mail->isHTML(true);
        //$cuerpo = trae_html($datos);
        $cuerpo = trae_html_mail($datos);
        //$cuerpo = trae_html($cliente, $url, $tipoComp, $nroComp, $empresa, $fecha, $vendedor, $total,$logoEmpresa64);
        $mail->Body = $cuerpo; //cuerpo del mensaje
        $mail->AltBody = '---'; //Mensaje de sólo texto si el receptor no acepta HTML

        $mail->addAddress($datos["emailCliente"], $cliente); 
       //$mail->addAddress("pflores@administranet.com.ar", $cliente.'Em:'.$datos["emailCliente"]);
        //$mail->addCC($datos["correo"]["nombre_usuario"], $vendedor);
        //$mail->Debugoutput();
        //CONFIGURACIÓN DE RECEPTORES
        //file_put_contents('log/mail-comprobantes-' . date('Y-m-d_h_i') . '.html', $cuerpo);
        $mail->send();
        unlink($fotoEmpresa);
        return 0;

        //ENVIAR MENSAJE
    } catch (Exception $e) {
        //echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        file_put_contents('log/err-mail-comprobantes-' . date('Y-m-d_h_i') . '.txt', $mail->ErrorInfo,FILE_APPEND);
        return 1;
    }
}


/** 
 *# Recuperar datos de email del cliente, con codmov.
 * */ 
function datos_cliente_para_envio($connV,$codigoCliente){
    $arrCliente=array();
    
    $sqlCliente = "SELECT 
                        cliente.nombre_cliente AS cliente,
                        cliente.Email AS email,
                        cliente.EmailContacto As emailcontacto,
                        clientes_web.codigo_usuario AS emailUsuarioWeb
                        
                    FROM cliente
                    LEFT JOIN clientes_web ON clientes_web.Codigo = cliente.Codigo                  

                    WHERE cliente.Codigo=".$codigoCliente;

    $hacer = mysqli_query($connV,$sqlCliente);
  
    if($hacer){
        $arrCliente = mysqli_fetch_assoc($hacer);
    }
    if(!$hacer){
        file_put_contents('log/error-datos_clientepara_envio.txt','ERR::'.mysqli_error($connV).PHP_EOL.'SQL::'.$sqlCliente.PHP_EOL);
    }
    return $arrCliente;
}

/**
 * FUNCIONES BASE 64 encode
 */
function base64url_encode( $data ){
    return rtrim( strtr( base64_encode( $data ), '+/', '-_'), '=');
  }
/**
 * FUNCIONES BASE 64 DECODE 
 */  
  function base64url_decode( $data ){
    return base64_decode( strtr( $data, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $data )) % 4 ));
  }

  // * funcion para display bulto para guardar comprobantes, con jcart pedido, remito, presupuesto y devolucion
// * funcion de calcular el precio costo por la unidad display bulto
// @datos:array
function calculaPrecioCostoUnidad($datos){
    // $datos es un array con los datos para hacer calculo.

    $cantidadUnidadDisplay=$datos['cantidadUnidadDisplay'];
    $cantidadDisplayBulto=$datos['cantidadDisplayBulto'];
    $tipoPrecioUnidad=$datos['tipoPrecioUnidad'];
    $precioCosto = $datos['precioCosto'];
    $divisor=1;
    $precioCostoUnidad = $precioCosto;


    // el precio de costo siempre va por la unidad minima. 
    //  =====================================================
    // if($tipoPrecioUnidad=="Unidad"){
    //     $precioCostoUnidad = $precioCosto;
        
        
    // }
    // if($tipoPrecioUnidad=="Display"){
    //     $divisor = (int)$cantidadUnidadDisplay;// cuantas unidades tengo 
    //     if($divisor == 0) $divisor=1;
    //     $precioCostoUnidad = $precioCosto /$divisor;
        
    // }
    // if($tipoPrecioUnidad=="Bulto"){
    //     $divisor = (int)($cantidadUnidadDisplay*$cantidadDisplayBulto);// cuantas unidades tengo 
    //     if($divisor == 0) $divisor=1;
    //     $precioCostoUnidad = $precioCosto /$divisor;
    // }

    // calculo el precio de costo por la DISPLAY ( asi esta fijado)
    // =============================
    if($tipoPrecioUnidad=="Unidad"){
        $precioCostoUnidad = $precioCosto;
        
        
    }

    if($tipoPrecioUnidad=="Display"){
        // $divisor = (int)$cantidadUnidadDisplay;// cuantas unidades tengo 
        // if($divisor == 0) $divisor=1;
        // $precioCostoUnidad = $precioCosto /$divisor;
        $precioCostoUnidad = $precioCosto;
        
    }

    if($tipoPrecioUnidad=="Bulto"){
        // $divisor = (int)($cantidadUnidadDisplay*$cantidadDisplayBulto);// cuantas unidades tengo 
        $divisor = (int)($cantidadDisplayBulto);// cuantas display tengo en el bulto

        if($divisor == 0) $divisor=1;
        $precioCostoUnidad = $precioCosto /$divisor;
    }

    return $precioCostoUnidad; //siempre vuelve precio por unidad
}