<?php require_once 'sesion.inc.php';
// phpinfo();
error_reporting(0);

function traeLogo($link){
    $query = "SELECT 
                    logo AS Foto,
                    'image/pjpeg' AS Tipo 
            FROM configuracion;";
    $sal=mysqli_query($link,$query)or die("no anduvo".mysqli_error($link));
    $fila= mysqli_fetch_assoc($sal);
    
    $fileName ="_img/logototal.png";
    $foto = imagepng(imagecreatefromstring($fila["Foto"]), $fileName) ;
//    $logo=fopen($fileName,"w");
//    fwrite($logo, $foto);
//    fclose($logo);
    //file_put_contents($fileName, $logo);
    return $fileName;
    
}
/*
 * Parametros
 */

$link=$connV;
$codMov=$_GET['codigomovimiento'];

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
        $hacerEmpresa = mysqli_query($link,$sqlEmpresa) 
                                            or die(
                                                    'No puedo recuperar los datos de la empresa'. mysqli_error($link).'<br>'.$sqlEmpresa
                                                    );
        $empresa = mysqli_fetch_object($hacerEmpresa);


//inicio Buscando en base de datos

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
  
    /*contacto tankito
     * 
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
    $muestraR .='           <img src="'.traeLogo($link).'" alt="'.$empresa->nombre_empresa.'" title="'.$empresa->nombre_empresa.'" class="asBlock" />';

    $muestraR .='        </div>';
    $muestraR .='        <div id="tipoComp"><strong>REM</strong><div id="leyenda">[Documento no válido como factura]</div></div>';
    $muestraR .='        <div id="derecha">';
    $muestraR .='               <ul class="datoComprobante">';
    $muestraR .='                   <li class="destacadoComp"><strong>REMITO</strong></li>';
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
        $muestraR .='<td class="derecha">'.trim($renglon->id_manual).'</td>';
        if($renglon->promocion=="Si"){
            $muestraR .='<td class="izquierda" style="width:67%;">* '.$renglon->Descripcion.'</td>';
        }else{
            $muestraR .='<td class="izquierda" style="width:67%;">'.$renglon->Descripcion.'</td>';
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
//    $muestraR .='       <p><strong>Detalle: </strong> </p>';
//    $muestraR .='       <p><strong>Observ: </strong> '.$remito->detalle_comprobante.' </p>';
//    $muestraR .='       <p><strong> <u>* Articulo en promoción </u></strong> </p>';
//    $muestraR .='       <p><strong>Son Pesos: '.$remito->ImporteVentaL.' </strong> </p>';
     $muestraR .='       <p><strong>El presente documento da conformidad de recepción de la mercadería detallada  </strong></p>';
    $muestraR .='       </div>';
    $muestraR .='       <div id="importe">';
//    $muestraR .='           <table>';
//    $muestraR .='               <tr>';
//    $muestraR .='                   <td colspan="2" class="izquierda">Neto:</td><td class="derecha">'.$remito->SubTotalGral.'</td>';
//    $muestraR .='               </tr>';
//    $muestraR .='               <tr>';
//    $muestraR .='                   <td colspan="2" class="izquierda">Descuento '.number_format($remito->PorDesc1,2).'%: </td><td class="derecha">'.$remito->ImpDesc1.'</td>';
//    $muestraR .='               </tr>';
//    $muestraR .='               <tr>';
//    $muestraR .='                   <td colspan="2" class="izquierda">Exento:</td><td class="derecha">'.$remito->Exento.'</td>';
//    $muestraR .='               </tr>';
//    $muestraR .='               <tr>';
//    $muestraR .='                   <td  class="izquierda">IVA:</td><td class="derecha">21,00</td><td class="derecha">'.$remito->IVA1.'</td>';
//    $muestraR .='               </tr>';
//    $muestraR .='               <tr>';
//    $muestraR .='                   <td  class="izquierda">IVA:</td><td class="derecha">10.50</td><td class="derecha">'.$remito->IVA2.'</td>';
//    $muestraR .='               </tr>';
//    $muestraR .='               <tr>';
//    $muestraR .='                   <td  class="izquierda">Percep IIBB Mza:</td><td class="derecha"></td><td class="derecha">'.$remito->total_percep.'</td>';
//    $muestraR .='               </tr>';
//    $muestraR .='               <tr>';
//    $muestraR .='                   <td colspan="3" class="separador">&nbsp;</td>';
//    $muestraR .='               </tr>';
//    $muestraR .='               <tr >';
//    $muestraR .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.($remito->Total+$remito->total_percep).'</td>';
//    $muestraR .='               </tr>';
//
//    $muestraR .='           </table>';
//    $muestraR .='       <div style="width:99%;height:50px;padding-top:30px;text-align:center;"><p>------------------------------------<br>FIRMA CLIENTE</p></div>';
    $muestraR .='       <div style="width:99%;height:50px;padding-top:30px;text-align:center;"><p>'.$contacto.'<br>'.$documento.'<br>------------------------------------<br>FIRMA<br>'
                        .'</p></div>';

    $muestraR .='       </div>';
    $muestraR .='       <div id="final">';

    $muestraR .='       <p>Comprobante generado por: ';
    $muestraR .='       <img src="_img/logo-administranet-ecommerce.png" style="height:25px;"></p>';
    $muestraR .='           <p>Tel:(0261)- 4274480 / 4283071 |  <a href="http://www.administranet.com.ar">www.administranet.com.ar</a></p>';
    $muestraR .='       </div>';
    $muestraR .='   </div>';
    $muestraR .='</div>';

    /*PDF
     * =========================================================================
     */
    //echo traeLogo();
    // mpdf 
    
//    require_once '_lib/mpdf/mpdf.php'; // DEPRECATED
//
//    $mpdf = new mPDF('c','A4');
//    $stylesheet = file_get_contents('_css/pdf.css');
//    $mpdf->WriteHTML($stylesheet,1);
//    $mpdf->WriteHTML($muestraR);
//    $mpdf->Output('Rem-'.$remito->NroComprobante.'.pdf','D');
    
    // mpdf2
    require_once  '_lib/mpdf2/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf(['mode' => 'c',
    'margin_left' => 5,
	'margin_right' => 5,
	'margin_top' => 4,
	'margin_bottom' => 5,
	'margin_header' => 5,
	'margin_footer' => 7
    ]);


$stylesheet = file_get_contents('_css/pdf.css');
    $mpdf->WriteHTML($stylesheet,1);
    $mpdf->WriteHTML($muestraR,2);
    $mpdf->SetDisplayMode('fullpage');
   
    //$mpdf->Output();
    $mpdf->Output('Rem-'.$remito->NroComprobante.'.pdf','D');
    exit; 
    

    
    