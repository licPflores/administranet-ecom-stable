<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * Anulacion de comprobantes.
 */
require_once 'sesion.inc.php';
/**
 * PEDIDO
 *  
 */
if(isset($_REQUEST["tipoComp"])&&$_REQUEST["tipoComp"]=="PED"){
    $tipoComp   = $_REQUEST["tipoComp"];
    $codmovP    = $_REQUEST["codMovP"];
    $control    = 0;
    $mensaje    = "";
    //analizando la relacion pedido - factura
    //SELECT * FROM ped_fact WHERE CodigoMovimientoP = " & DataConsulta.Recordset.Fields!CodigoMovimiento & " And Anulado = 'No'"
    $sql = "SELECT cc.NroComprobante "
            . " FROM ped_fact AS pf"
            . " LEFT JOIN cuentacliente AS cc ON (cc.CodigoMovimiento=pf.CodigoMovimientoF)"
            . " WHERE pf.CodigoMovimientoP = ". $codmovP. " AND pf.Anulado = 'No'";
    
    $hacer = mysql_query($sql) or die("no puedo ejecutar la consulta por pedido-factura".  mysql_error() .$sql );
    
    if($hacer){
        $cuantos = mysql_num_rows($hacer);
        
        if($cuantos>0){
            $renglon = mysql_fetch_assoc($hacer);
            $control++;
            $mensaje = "El pedido que desea anular corresponde a la factura Nro:". $renglon["NroComprobante"] .". Antes anule dicha factura";
            
        }
    }
    //analizando la relacion pedido - remito
    if($control==0){
        $sql = "SELECT  cp.NroComprobante"
                . " FROM rem_ped  AS rp"
                . " LEFT JOIN comp_ped AS cp ON (cp.CodigoMovimiento=rp.codmov_remito)"
                . " WHERE rp.codmov_pedido = " . $codmovP . " AND rp.Anulado = 'No'";
        
        $hacer = mysql_query($sql) or die("no puedo ejecutar la consulta por pedido-remito".  mysql_error() .$sql );
        $cuantos = mysql_num_rows($hacer);        
        if($cuantos>0){
            $renglon = mysql_fetch_assoc($hacer);
            $control++;
            $mensaje = "<p>El pedido que desea anular corresponde al remito Nro:". $renglon["NroComprobante"] .".<br> Antes anule dicho remito</p>";
            
        }
    
        
    }
    if($control==0){
        $errores  = 0;
        
        $sql = " SET AUTOCOMMIT=0;";
        $resultado = mysql_query($sql);
        if(!$resultado){
            $errores++;
        }
        
        $sql = " BEGIN;";
        $resultado = mysql_query($sql);
        if(!$resultado){
            $errores++;
        }
        
        //comp_ped
        $sql = "UPDATE comp_ped SET anulado='Si' WHERE CodigoMovimiento=" .$codmovP;
        $resultado = mysql_query($sql) or die("no puedo anular" . mysql_error() . $sql);   
        if(!$resultado){
            $errores++;
        }
        // presupuesto pedido
        $sql = "UPDATE comp_ped "
                . " LEFT JOIN ped_presup AS pp ON (pp.codigo_movimiento_presup = comp_ped.CodigoMovimiento"
                . " SET pp.anulado='Si',"
                . "   comp_ped.Estado ='Pendiente'"
                .  " WHERE pp.codigo_movimiento_ped=".$codmovP;
        
        $resultado = mysql_query($sql) or die("no puedo anular" . mysql_error() . $sql);
        if(!$resultado){
            $errores++;
        }
        //articlos en pedido
        $sql ="UPDATE stockp "
                . " LEFT JOIN stock_deposito AS sd "
                . " ON (sd.id_articulo=stockp.IDArt "
                . " AND sd.id_deposito=stockp.CodDeposito)"
                . " SET sd.saldo_pedido_cliente = sd.saldo_pedido_cliente - stockp.Cantidad,"
                . "     stockp.anulado='Si'"
                . " WHERE stockp.CodigoMovimiento=". $codmovP;
        $resultado = mysql_query($sql) or die("no puedo anular" . mysql_error() . $sql);
        if (!$resultado) {
            $errores++;
        }
        
        ///proyectos
        $sql = "UPDATE erp_parte_diario "
                . " LEFT JOIN ped_pd AS pd ON (pd.codigo_movimiento_pd  = erp_parte_diario.CodigoMovimiento"
                . " SET pd.anulado='Si',"
                . "   erp_parte_diario.Estado ='Reportado'"
                .  " WHERE pd.codigo_movimiento_ped=".$codmovP;
        
        $resultado = mysql_query($sql) or die("no puedo anular" . mysql_error() . $sql);
        if(!$resultado){
            $errores++;
        }
        if($errores==0){
            $sql="COMMIT;";
            $hacer = mysql_query($sql);
            $mensaje = "El pedido se anulo correctamente";
        }else{
            $sql="ROLLBACK;";
            $hacer = mysql_query($sql);
            $mensaje = "Ocurrio un error y no se puedo anular el pedido";
        }
        
    }
    
    echo $mensaje;
    
    //anulo el pedido
    
}
/**
 * REMITO
 *  
 */
if(isset($_REQUEST["tipoComp"])&&$_REQUEST["tipoComp"]=="REM"){
    $tipoComp   = $_REQUEST["tipoComp"];
    $codmovR    = $_REQUEST["codMovR"];
    $control    = 0;
    $mensaje    = "";
    $sql = "SELECT cc.NroComprobante cc.remite_factura_art"
            . "FROM rem_fact AS rf"
            . "LEFT JOIN cuentacliente AS cc ON (cc.CodigoMovimiento=rf.CodigoMovimientoF)"
            . "WHERE rf.CodigoMovimientoR = ". $codmovR. " AND rf.Anulado = 'No'";
    $hacer = mysql_query($sql) or die("no puedo recupear facturas-rem" . mysql_error(). $sql);
    if($hacer){
        $cuantos = mysql_num_rows($hacer);
        
        if($cuantos>0){
            $renglon = mysql_fetch_assoc($hacer);
            if($renglon["remite_factura_art"]=="Si"){
                $control++;
                $remite_factura = "Si";
                $mensaje = "El remito que desea anular corresponde a la factura Nro:". $renglon["NroComprobante"]. ". Antes anule dicha factura";
            }else{
                $remite_factura = "No";
            }
        }
    }
    if($control==0){
        //anulo Remito
        $errores  = 0;
        
        $sql = " SET AUTOCOMMIT=0;";
        $resultado = mysql_query($sql);
        if(!$resultado){
            $errores++;
        }
        
        $sql = " BEGIN;";
        $resultado = mysql_query($sql);
        if(!$resultado){
            $errores++;
        }
        $sql = "UPDATE comp_ped SET anulado='Si' WHERE CodigoMovimiento=" .$codmovR;
        $resultado = mysql_query($sql) or die("no puedo anular" . mysql_error() . $sql);   
        if(!$resultado){
            $errores++;
        }
        //articlos en pedido
        $sql ="UPDATE stock "
                . " LEFT JOIN stock_deposito AS sd "
                . " ON (sd.id_articulo=stock.IDArt "
                . " AND sd.id_deposito=stock.CodDeposito)"
                . " SET sd.saldo_pedido_cliente = sd.saldo_pedido_cliente - stock.Cantidad,"
                . "     stock.anulado='Si'"
                . " WHERE stock.CodigoMovimiento=". $codmovR;
        $resultado = mysql_query($sql) or die("no puedo anular" . mysql_error() . $sql);
        if (!$resultado) {
            $errores++;
        }
        //el stock del remito
        $sql = "SELECT * FROM stock WHERE stock.CodigoMovimiento=". $codmovR;
        $resultado = mysql_query($sql) or die("no puedo recuperar el stock" .mysql_error() .$sql);
        while ($st = mysql_fetch_assoc($resultado)){
            //control de tipo articulo
            
            $codDeposito = 0;
            if($st["tipo_art"] == "Articulo"){
                $codDeposito = $st["CodDeposito"]; 
            }
            
            // control de lote        
            $stockLote = $st["stock_lote_deposito"];
            
            if($st["id_lote"]!=""){
                    
                $sqlLote = "SELECT lote_stock.stock_lote,lote.stock_total_lote,lote.devuelto "
                        . " FROM lote " 
                        ." LEFT JOIN lote_stock ON (lote.id_lote = lote_stock.id_lote) " 
                        ." WHERE lote.id_lote = ".$st["id_lote"]." AND "
                        ." lote_stock.id_deposito = ". $st["CodDeposito"]." AND "
                        ." lote.anulado = 'No'";
    
                $hacer = mysql_query($sqlLote) or die("no puedo recuperar datos de lotes" . mysql_error().$sqlLote);
                $lote = mysql_fetch_assoc($hacer);
                $stockLote = $lote["stock_lote"] + $st["Cantidad"];
                $stockTotalLote = $lote["stock_total_lote"] + $st["Cantidad"];    
                
                $sqlLote = "UPDATE lote LEFT JOIN lote_stock ON(lote.id_lote = lote_stock.id_lote)"
                        . " SET lote_stock.stock_lote=".$stockLote.","
                        . " lote.stock_total_lote=".$stockTotalLote.","
                        . " lote.devuelto='Si'"
                        . " WHERE lote.id_lote = ".$st["id_lote"]." AND "
                        . " lote_stock.id_deposito = ". $st["CodDeposito"]." AND "
                        . " lote.anulado = 'No'";
                $resul = mysql_query($sqlLote) or die("fallo de updeate lote" . mysql_error().$sqlLote);
                if(!$resul){
                    $errores++;
                }
                            
            }
            $objV = $_SESSION['vendedor'];
            //control de numero de pedido.
            $codSucursal = $objV->id_sucursal;
            $idUsuario = $_SESSION["idusuario"];
            $tipo = "Cliente";
            $tipoComp = "Anul Remito";
            $anulado = "No";
            
            $nroPedido = "NULL";
            $codMovPedido = "NULL";
            if($st["NroPedido"]!=""){
                $nroPedido = $st["NroPedido"];
                $codMovPedido = $st["codmov_pedido"]; 
            }
        
            $sqlI = "INSERT INTO stock ("
                    . "Fecha,"
                    ."CodigoArticulo,"
                    ."Descripcion,"
                    ."PrecioVentaxU,"
                    ."PrecioCostoxU ,"
                    ."PrecioIVAxU ,"
                    ."PrecioBrutoxU ,"
                    ."PrecioNetoxU ,"
                    ."Impdesc ,"
                    ."Pordesc ,"
                    ."PrecioVentaxR ,"
                    ."PrecioCostoxR ,"
                    ."PrecioIVAxR ,"
                    ."PrecioBrutoxR ,"
                    ."PrecioNetoxR ,"
                    ."alicuota ,"
                    ."AlicuotaIB ,"
                    ."NetoIB ,"
                    ."impIB ,"                    
                    ."entrada,"
                    ."Saldov,"                            
                    ."Cantidad,"
                    ."CodViajante,"
                    ."CodLaboratorio,"
                    ."codigo_movimiento_anul,"            
                    ."CodDeposito,"                            
                    ."IDArt,"
                    ."id_lote,"                            
                    ."stock_lote_deposito,"
                    ."CodSucursal,"
                    ."idUsuario,"
                    ."TipoIVA,"
                    ."CodigoCP,"
                    ."Tipo,"
                    ."TipoComp,"
                    ."anulado,"
                    ."Comprobante,"
                    ."NroComprobante,"
                    ."NroPedido,"
                    ."codmov_pedido)VALUES("
                    . "'".date('Y-m-d')."',"
                    ."'". $st["CodigoArticulo"]."',"
                    ."'". $st["Descripcion"]."',"
                    ."'". $st["PrecioVentaxU"]."',"
                    ."'". $st["PrecioCostoxU"]."' ,"
                    ."'". $st["PrecioIVAxU"]."' ,"
                    ."'". $st["PrecioBrutoxU"]."' ,"
                    ."'". $st["PrecioNetoxU"]."' ,"
                    ."'". $st["Impdesc"]."' ,"
                    ."'". $st["Pordesc"]."' ,"
                    ."'". $st["PrecioVentaxR"]."' ,"
                    ."'". $st["PrecioCostoxR"]."' ,"
                    ."'". $st["PrecioIVAxR"]."' ,"
                    ."'". $st["PrecioBrutoxR"]."' ,"
                    ."'". $st["PrecioNetoxR"]."' ,"
                    ."'". $st["alicuota"]."',"
                    ."'". $st["AlicuotaIB"]."' ,"
                    ."'". $st["NetoIB"]."' ,"
                    ."'". $st["impIB"]."' ,"                    
                    ."'". $st["entrada"]."',"
                    ."'". $st["Saldov"]."',"                            
                    ."'". $st["Cantidad"]."',"
                    ."'". $st["CodViajante"]."',"
                    ."'". $st["CodLaboratorio"]."',"
                    ."'". $st["CodigoMovimiento"]."',"            
                    ."'". $st["CodDeposito"]."',"                            
                    ."'". $st["IDArt"]."',"
                    ."'". $st["id_lote"]."',"                            
                    ."'". $stockLote."',"
                    ."'". $codSucursal."',"
                    ."'". $idUsuario."',"
                    ."'". $st["TipoIVA"]."',"
                    ."'". $st["CodigoCP"]."',"
                    ."'". $tipo."',"
                    ."'". $tipoComp."',"
                    ."'". $anulado."',"
                    ."'". $st["Comprobante"]."',"
                    ."'". $st["NroComprobante"]."',"
                    ."'". $nroPedido."',"
                    ."'". $codMovPedido ."')";
            $resultadoS = mysql_query($sqlI) or die("no puedo anular" . mysql_error() . $sqlI);
            if (!$resultadoS) {
                $errores++;
            }

        }
        //remito - factura
        if($remite_factura=="Si"){
            // Anulo la relacion Remito-Pedido si existe y vuelvo el PED a estado "Pendiente"
            $sql = "UPDATE comp_ped LEFT JOIN rem_ped ON (rem_ped.codmov_pedido=comp_ped.CodigoMovimiento)"
                    . "SET rem_ped.anulado='Si',"
                    . "comp_ped.Estado ='Pendiente'"
                    . "WHERE rem_ped.codmov_remito=" . $codmovR;

            $resultado = mysql_query($sql) or die("no puedo anular relacion pedido" . mysql_error(). $sql);
            if(!$resultado){
                $errores++;
            }
                
        }else{
            
            $sql ="UPDATE cuentacliente LEFT JOIN rem_fact ON (rem_fact.CodigoMovimientoF=cuentacliente.CodigoMovimiento)"
            . " SET rem_fact.anulado ='Si',"
            . " cuentacliente.estado_fact_remito='Pendiente'"
            . " WHERE rem_fact.CodigoMovimientoR=".$codmovR;
            $resultado = mysql_query($sql) or die("no puedo actualizar la relacion remito-factura" . mysql_error.$sql);
            if(!$resultado){
                $errores++;
            }
            $sql = "SELECT stock.id_stock_factura,"
                    . "stock.id_stock,stock.CodigoMovimiento,"
                    . "stock.Cantidad,"
                    . "stock.cantidad_entregada_pend "
                    . " FROM stock "
                    . " WHERE CodigoMovimiento= ".$codmovR;
            
            $hacer = mysql_query($sql) or die("no puedo recuperar el stock" . mysql_error().$sql);
            while($s=  mysql_fetch_assoc($hacer)){
                $sqlU = "UPDATE stock SET "
                        . "cantidad_entregada_pend=cantidad_entregada_pend + ".$s["Cantidad"].","
                        . "entregado_fact_total = 'No'"
                        . " WHERE stock.id_stock= ".$s["id_stock_factura"];
                $h = mysql_query($sqlU)or die("no puedo actualizar stock fact" . mysql_error().$sqlU);
                if(!$h){
                    $errores++;
                }
            }
        }
        if ($errores == 0) {
            $sql = "COMMIT;";
            $hacer = mysql_query($sql);
            $mensaje = "El remito se anulo correctamente";
        } else {
            $sql = "ROLLBACK;";
            $hacer = mysql_query($sql);
            $mensaje = "Ocurrio un error y no se puedo anular el remito";
        }
    }
    echo $mensaje;
    
}    