<?php require_once 'sesion.inc.php';
$comprobante=$_POST["comprobante"];
$textoComp ="PEDIDO";
if($comprobante=="DEV"){
    $textoComp="DEVOLUCION";
}
$usaIdManual = $_SESSION["usa_id_manual"];
    
    if($usaIdManual=="Si"){
        $codCliente ="idManual";
        $idArt ="id_manual";
       
    }else{
        $codCliente ="Codigo";
        $idArt="IDArt";
       
    }       
//echo "<pre>";
//echo print_r($_POST);
//echo "</pre>";
            if(isset($_POST['codigomovimiento'])){
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
                                    comp_ped.IVA2) AS Total
                            FROM 
                                comp_ped
                                LEFT JOIN cliente ON cliente.Codigo = comp_ped.Codigo    
                                LEFT JOIN usuarios ON comp_ped.id_repartidor = usuarios.id_usuario
                                LEFT JOIN viajantes ON comp_ped.CodViajante = viajantes.CodViajante
                                LEFT JOIN contribuyentes ON contribuyentes.idIVA = cliente.idIVA
                                LEFT JOIN transporte ON transporte.id_transporte = comp_ped.id_transporte
                            
                                LEFT JOIN reporte_comprobante AS rp ON (rp.nombre_reporte_comprobante = comp_ped.TipoComprobante AND rp.id_sucursal = comp_ped.CodSucursal AND rp.id_punto_venta = comp_ped.id_pv)   
                            
                            WHERE  
                             comp_ped.CodigoMovimiento=".$_POST['codigomovimiento']." 
                                
                            ORDER BY comp_ped.id_comp_ped";
                
                $hacerPed = mysqli_query($connV,$sqlPedido) or die('No puedo recuperar el pedido'.mysqli_error($connV).'<br>'.$sqlPedido);
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
                                        stockp.promocion
                                        
                                      FROM stockp
                                      LEFT JOIN iva ON stockp.Alicuota = iva.ID
                                      WHERE stockp.CodigoMovimiento=".$pedido->CodigoMovimiento;
                
                $hacerRenglon = mysqli_query($connV,$sqlRenglon) or die('no puedo recuperar el renglon'.mysqli_error($connV));
                while($renglon=  mysqli_fetch_object($hacerRenglon)){
                    $renglones[]=$renglon;
                }
                
//                echo '<pre>'.print_r($renglones).'</pre>';
//                echo '<pre>'.print_r($pedido).'</pre>';
            }     


$muestraR = "";        
$muestraR .='    <div id="comprobante">';

//$muestraR .='    <input type="button" id="imprimir" value="Imprimir">';

$muestraR .='    <div id="cabeceraComprobante">';
$muestraR .='    <input type="button" src="_img/imprimir.png" id="imprimir" value="Imprimir">';
$muestraR .='        <div id="izquierda">';
$muestraR .='           <img src="foto.php?origen=logo|0" alt="'.$_SESSION['nombre_empresa'].'" title="'.$_SESSION['nombre_empresa'].'" class="asBlock" />';

$muestraR .='        </div>';
$muestraR .='        <div id="tipoComp"><strong>'.$comprobante.'</strong><div id="leyenda">[Documento no válido como factura]</div></div>';
$muestraR .='        <div id="derecha">';
$muestraR .='               <ul class="datoComprobante">';
$muestraR .='                   <li class="destacado"><strong>'.$textoComp.'</strong></li>';
$muestraR .='                   <li class="destacado"><strong>Nro: '.$pedido->NroComprobante.'</strong></li>';
$muestraR .='                   <li><strong>Fecha: </strong>'.$pedido->Fecha.'</li>';
$muestraR .='                   <li><strong>Usuario: </strong> '.$objVendedor->nombre_usuario .' ' .$objVendedor->apellido_usuario.'</li>';
$muestraR .='                   <li><strong>Vendedor: </strong> '.$pedido->Vendedor.'</li>';
$muestraR .='                   <li><strong>Venc.: </strong>'.$pedido->Vencimiento.'</li>';

$muestraR .='               </ul>';
$muestraR .='        </div>';
$muestraR .='        </div>';
      
$muestraR .='    <div id="membrete">';
$muestraR .='               <ul class="datoComprobante">';
$muestraR .='                   <li class="destacado"><strong>'.$_SESSION['nombre_empresa'].'</strong></li>';
$muestraR .='                   <li>'.$_SESSION['domicilio_empresa'].'</li>';
$muestraR .='                   <li>Tel: '.$_SESSION['telefono_empresa'].'</li>';
$muestraR .='                   <li>E-mail: '.$_SESSION['email_empresa'].'</li>';
$muestraR .='                   <li>IVA: '.$_SESSION['iva_empresa'].'</li>';
$muestraR .='               </ul>';
$muestraR .='               <ul class="datoComprobante derecha">';
$muestraR .='                   <li>&nbsp;</li>';
$muestraR .='                   <li>CUIT: '.$_SESSION['cuit_empresa'].'</li>';
$muestraR .='                   <li>Inic. Act.: '.  $_SESSION['iniact_empresa'].'</li>';
$muestraR .='                   <li>Ing. Brutos: '.$_SESSION['ingbrutos_empresa'].'</li>';
$muestraR .='               </ul>';
$muestraR .='    </div>';  
$muestraR .='    <div id="datosCliente">';
//$muestraR .='            <p><label>Detalle</label><br/><textarea class="area">'.$pedido->Detalle.'</textarea></p>';
$muestraR .='               <ul class="datoComprobante">';
$muestraR .='                   <li><strong>Cliente: </strong>'.$pedido->nombre_cliente.'</li>';
$muestraR .='                   <li><strong>Domicilio: </strong>'.$pedido->Calle.' Nº'. $pedido->NroCalle .' depto :'.$pedido->Dpto.   '</li>';
$muestraR .='                   <li><strong>Fecha entrega: </strong>'.$pedido->FechaEntrega.'</li>';
$muestraR .='                   <li><strong>Forma entrega: </strong>'.$pedido->FormaEntrega.'</li>';
$muestraR .='                   <li><strong>Transporte: </strong>'.$pedido->nombre_transporte.'</li>';
$muestraR .='                   <li><strong>Repartidor: </strong>'.$pedido->repartidor.'</li>';
$muestraR .='               </ul>';
$muestraR .='               <ul class="datoComprobante derecha">';
$muestraR .='                   <li><strong>IVA: </strong>'.$pedido->iva_cliente.'</li>';
$muestraR .='                   <li><strong>Cuit: </strong>'.$pedido->CUIT.'</li>';
$muestraR .='               </ul>';
$muestraR .='               <ul class="datoComprobante derecha">';
$muestraR .='                   <li><strong>Cond.Venta: </strong>'.$pedido->CondVenta.'</li>';
$muestraR .='               </ul>';
$muestraR .='    </div>';  
$muestraR .='   <div id="cuerpoComprobante">';
$muestraR .='        <table id="tablaComp" >';
$muestraR .='            <thead>';
//$muestraR .='                <tr>';
//$muestraR .='                    <th colspan="14" style="text-align: center;">Renglón</th>';
//$muestraR .='                </tr>';
$muestraR .='                <tr>';
$muestraR .='                    <th>Cod.</th>';
$muestraR .='                    <th>Descripcion</th>';
$muestraR .='                    <th>PrecioxU</th>';
$muestraR .='                    <th>Cant</th>';
$muestraR .='                    <th>%Alic</th>';
$muestraR .='                    <th>Imp. Desc</th>';
$muestraR .='                    <th>%Desc.</th>';
$muestraR .='                    <th>Precio Neto</th>';
$muestraR .='                    <th>Nº Presup.</th>';
$muestraR .='                    <th>IVA</th>';
$muestraR .='                    <th>Tipo IVA</th>';
$muestraR .='                    <th>Prom.</th>';                  
$muestraR .='                </tr>';
$muestraR .='            </thead>';
$muestraR .='            <tbody>';
                                foreach($renglones as $renglon):
$muestraR .='                <tr>';
$muestraR .='                    <td class="izquierda">'.$renglon->IDArt .'</td>';
$muestraR .='                    <td class="izquierda">'.$renglon->Descripcion.'</td>';
$muestraR .='                    <td class="derecha">$'. number_format($renglon->PrecioVentaxU,4).'</td>';
$muestraR .='                    <td class="derecha">'. $renglon->Salida.'</td>';
$muestraR .='                    <td class="derecha">'. $renglon->Alicuota.'</td>';
$muestraR .='                    <td class="derecha">'. $renglon->ImpDesc.'</td>';
$muestraR .='                    <td class="derecha">'. $renglon->PorDesc.'</td>';
$muestraR .='                    <td class="derecha">$'.$renglon->PrecioNetoxR.'</td>';
$muestraR .='                    <td class="izquierda">'.$renglon->NroPresupuesto.'</td>';
$muestraR .='                    <td class="derecha">$'. $renglon->PrecioIVAxR.'</td>';
$muestraR .='                    <td class="derecha">'. $renglon->TipoIVA.'</td>';
$muestraR .='                    <td class="derecha">'. $renglon->promocion.'</td>';
$muestraR .='                   </tr>';
                    endforeach;
$muestraR .='               </tbody>';
$muestraR .='           </table>';
$muestraR .='   </div>';
$muestraR .='   <div id="pieComprobante">';
$muestraR .='       <div id="detalle">';
$muestraR .='       <p><strong>Detalle: </strong> '.$pedido->Detalle.'</p>';
$muestraR .='       <p><strong>Observ: </strong> '.$pedido->detalle_comprobante.' </p>';
$muestraR .='       <p><strong>Son Pesos: '.$pedido->ImporteVentaL.' </strong> </p>';
$muestraR .='       </div>';
$muestraR .='       <div id="importe">';
$muestraR .='           <table>';
$muestraR .='               <tr>';
$muestraR .='                   <td colspan="2" class="izquierda">Neto:</td><td class="derecha">'.$pedido->SubTotalGral.'</td>';
$muestraR .='               </tr>';
$muestraR .='               <tr>';
$muestraR .='                   <td colspan="2" class="izquierda">Descuento:</td><td class="derecha">'.$pedido->ImpDesc1.'</td>';
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
$muestraR .='                   <td  class="izquierda">Perc:</td><td class="derecha"></td><td class="derecha">'.$pedido->total_percep.'</td>';
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
$muestraR .='       <img src="_img/logo_administranet_chico.gif">';
$muestraR .='       Comprobante generado por:<br>Tel:(0261)- 4274480 / 4283071 | http://www.administranet.com.ar';
$muestraR .='       </div>';
$muestraR .='   </div>';
$muestraR .='</div>';

echo $muestraR;
