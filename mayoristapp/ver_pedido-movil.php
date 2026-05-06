<?php require_once 'sesion.inc.php';

function traeLogo($link){
    // $query = "SELECT 
    //                 logo AS Foto,
    //                 'image/pjpeg' AS Tipo 
    //         FROM configuracion;";
    // $sal=mysqli_query($link,$query)or die("no anduvo".mysqli_error($link));
    // $fila=mysqli_fetch_array($sal);
    // $fotoCadena = $fila['Foto'];
    // $fileName ="_img/logototal.png";
    // $foto = imagepng(imagecreatefromstring($fila["Foto"]), $fileName) ;
    $fileName ="";
    $fileJpg ='_img/logototal.jpg';
    $filePng ='_img/logototal.png';
    $fileBmp ='_img/logototal.bmp';

    // $logo=fopen('_img/logototal.bmp',"w");
    // fwrite($logo, $fila["Foto"]);
    // fclose($logo);
    
    // $size_info2 = getimagesizefromstring($fotoCadena);
    // echo print_r($size_info2);
    // //$fotoString = imagecreatefromstring($fila["Foto"]);
    // $fotoString = imagecreatefromstring($fotoCadena);
    // if($fotoString){
    //     $fotoJpg = imagejpeg($fotoString, $fileJpg) ;
    //     $fotoPng = imagepng($fotoString,$filePng);
    //     $fotoBmp = imagebmp($fotoString,$fileBmp);

    //     if($fotoJpg){
    //         echo $fileName=$fileJpg;
    //     }

    //     if($fotoPng){
    //         echo $fileName=$filePng;
    //     }
    //     if($fotoBmp){
    //         echo $fileName=$fileBmp;
    //     }
    // }
    
//    $logo=fopen($fileName,"w");
//    fwrite($logo, $foto);
//    fclose($logo);
    //file_put_contents($fileName, $logo);
    // return $fileName;
    return $filePng;
    
}
/*
 * Parametros
 */

$link=$connV;
$codMov=$_GET['codigomovimiento'];

$usaIdManual = $_SESSION['usa_id_manual'];
$usaImpInterno = $_SESSION['usa_impuesto_interno_abm'];
$decimales = 2;
//empresa datos
$sqlEmpresa = "SELECT Nombre AS nombre_empresa,
                            Telefono AS telefono_empresa,
                            Cuit AS cuit_empresa,
                            Domicilio AS domicilio_empresa,
                            CONCAT(datosempresa.Domicilio ,' - ',departamento.NombreDepartamento, ', ', provincia.Provincia) AS domicilio_fiscal,
                            Email AS email_empresa,
                            IngBrutos AS ingbrutos_empresa,
                            InicioAct AS iniact_empresa,
                            contribuyentes.IVA AS iva_empresa
                           
                      FROM datosempresa
                      LEFT JOIN contribuyentes ON contribuyentes.IDIva = datosempresa.IDIva  
                      LEFT JOIN provincia ON provincia.CodProvincia = datosempresa.CodProvincia
                      LEFT JOIN departamento ON departamento.IDDepartamento = datosempresa.CodDepartamento
                      LEFT JOIN distrito ON distrito.IDDistrito = datosempresa.id_localidad 
                        WHERE id_empresa=1";
$hacerEmpresa = mysqli_query($link,$sqlEmpresa) 
                                    or die(
                                            'No puedo recuperar los datos de la empresa'. mysqli_error($link).'<br>'.$sqlEmpresa
                                            );
        $empresa = mysqli_fetch_object($hacerEmpresa);

                $sqlPedido="SELECT 
                                    comp_ped.CodigoMovimiento,
                                    comp_ped.id_comp_ped AS id,
                                    -- DATE_FORMAT(comp_ped.Fecha,'%d/%m/%Y %h:%i') AS Fecha,
                                    comp_ped.fecha_control AS Fecha,

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
                                    comp_ped.impuesto_interno_interes,
                                    transporte.nombre_transporte,
                                    DATE_FORMAT(comp_ped.Vencimiento,'%d/%m/%Y') AS Vencimiento,
                                    CONCAT(usuarios.nombre_usuario,' ',usuarios.apellido_usuario) AS repartidor,
                                    cliente.nombre_cliente,
                                    cliente.Codigo,
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
                
                $hacerPed = mysqli_query($connV,$sqlPedido) or die('No puedo recuperar el pedido'.mysqli_error($connV).'<br>'.$sqlPedido);
                $pedido = mysqli_fetch_object($hacerPed);
                $renglones = array();
                $sqlRenglon="SELECT     stockp.IDArt,
                                       
                                        stockp.CodigoArticulo,
                                        stockp.Descripcion,
                                        stockp.Salida,
                                        stockp.PrecioNetoxU,
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
                
                $hacerRenglon = mysqli_query($connV,$sqlRenglon) or die('no puedo recuperar el renglon'.mysqli_error($connV));
                while($renglon=  mysqli_fetch_object($hacerRenglon)){
                    $renglones[]=$renglon;
                }
                
//                echo '<pre>'.print_r($renglones).'</pre>';
//                echo '<pre>';
//                print_r($pedido);
//                echo '</pre>';
               


            
$muestraR = "";        
$muestraR .='    <div id="comprobante">';

//$muestraR .='    <input type="button" id="imprimir" value="Imprimir">';

$muestraR .='    <div id="cabeceraComprobante">';
//$muestraR .='    <input type="image" src="_img/imprimir.png" id="imprimir" value="Imprimir">';
$muestraR .='        <div id="izquierda">';
$muestraR .='           <img src="'.traeLogo($link).'" width="150" alt="'.$empresa->nombre_empresa.'" title="'.$empresa->nombre_empresa.'" class="asBlock" />';

$muestraR .='        </div>';
$muestraR .='        <div id="tipoComp"><strong>PED</strong><div id="leyenda">[Documento no válido como factura]</div></div>';
$muestraR .='        <div id="derecha">';
$muestraR .='               <ul class="datoComprobante">';
$muestraR .='                   <li class="destacado"><strong>PEDIDO</strong></li>';
$muestraR .='                   <li class="destacado"><strong>Nro: '.$pedido->NroComprobante.'</strong></li>';
$muestraR .='                   <li><strong>Fecha: </strong>'.$pedido->Fecha.'</li>';
$muestraR .='                   <li><strong>Usuario: </strong> '.$objVendedor->nombre_usuario .' ' .$objVendedor->apellido_usuario.'</li>';
$muestraR .='                   <li><strong>Vendedor: </strong> '.$pedido->Vendedor.'</li>';
$muestraR .='                   <li><strong>Venc.: </strong>'.$pedido->Vencimiento.'</li>';

$muestraR .='               </ul>';
$muestraR .='        </div>';
$muestraR .='        </div>';
      
$muestraR .='    <div id="membrete">';
$muestraR .='       <div class="columna">';
$muestraR .='               <ul class="datoComprobante">';
$muestraR .='                   <li class="destacado"><strong>'.$empresa->nombre_empresa.'</strong></li>';
$muestraR .='                   <li>Domicilio Fiscal:'.$empresa->domicilio_fiscal.'</strong></li>';
$muestraR .='                   <li>Domicilio Comercial:'.$empresa->domicilio_fiscal.'</strong></li>';

$muestraR .='                   <li>Tel: '.$empresa->telefono_empresa.'</li>';
$muestraR .='                   <li>E-mail: '.$empresa->email_empresa.'</li>';
$muestraR .='               </ul>';
$muestraR .='       </div>';
$muestraR .='       <div class="columna">';
$muestraR .='               <ul class="datoComprobante">';
$muestraR .='                   <li>IVA: '.$empresa->iva_empresa.'</li>';

$muestraR .='                   <li>CUIT: '.$empresa->cuit_empresa.'</li>';
$muestraR .='                   <li>Inic. Act.: '.  $empresa->iniact_empresa.'</li>';
$muestraR .='                   <li>Ing. Brutos: '.$empresa->ingbrutos_empresa.'</li>';
$muestraR .='               </ul>';
$muestraR .='       </div>';
$muestraR .='    </div>';  
$muestraR .='    <div id="datosCliente">';
// $muestraR .='               <h2>Datos del cilente</h2>';

$muestraR .='       <div class="columna">';

$muestraR .='               <ul class="datoComprobante">';
$muestraR .='                   <li class="destacado">Cliente: '.$pedido->nombre_cliente.' - ('.$pedido->Codigo.')</li>';
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

$muestraR .='                    <th>Código</th>';
$muestraR .='                    <th>Descripción</th>';
$muestraR .='                    <th>Cantidad</th>';
$muestraR .='                    <th>% Alic</th>';

// if($renglon->cantidad_pendiente)
// $muestraR .='                    <th>Cant E</th>';
// $muestraR .='                    <th>Cant P</th>';
$muestraR .='                    <th>Precio Unitario</th>';
//$muestraR .='                    <th>Imp. Desc</th>';
$muestraR .='                    <th>% Desc</th>';
$muestraR .='                    <th>PrecioNeto x U</th>';
if($usaImpInterno=='Si'){
    $muestraR .='                    <th>Imp int</th>';

}
$muestraR .='                    <th>Precio total</th>';
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
   
    if($usaIdManual=='Si'){
        $muestraR .='<td class="izquierda">'.$renglon->id_manual.'</td>';
    }
    if($usaIdManual=='No'){
        $muestraR .='<td class="izquierda">'.$renglon->IDArt .'</td>';
    }
    
    if($renglon->promocion=="Si"){
        $muestraR .='<td class="izquierda">* '.$renglon->Descripcion.'</td>';
    }
    if($renglon->promocion=="No"){
        $muestraR .='<td class="izquierda">'.$renglon->Descripcion.'</td>';
    }
    $muestraR .='<td class="derecha">'. $renglon->Salida.'</td>';
    $muestraR .='<td class="derecha">'. $renglon->Alicuota.'</td>';
    // $muestraR .='<td class="derecha">'. $renglon->cantEntregada.'</td>';
    // $muestraR .='<td class="derecha">'. $renglon->cantidad_pendiente.'</td>';
    $muestraR .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxU,$decimales,',','.').'</td>';
    $muestraR .='<td class="derecha">'. $renglon->PorDesc.'</td>';
    $muestraR .='<td class="derecha">$'. number_format($renglon->PrecioNetoxU,$decimales,',','.').'</td>';
    if($usaImpInterno=='Si'){
        $muestraR .='<td class="derecha">$'. number_format($renglon->impuesto_interno_subtotal,$decimales,',','.').'</td>';

    }
    //$muestraR .='                    <td class="derecha">'. $renglon->Alicuota.'</td>';
    //$muestraR .='                    <td class="derecha">'. $renglon->ImpDesc.'</td>';

    //$muestraR .='                    <td class="derecha">$'.$renglon->PrecioNetoxR.'</td>';
    //$muestraR .='                    <td class="izquierda">'.$renglon->NroPresupuesto.'</td>';
    $muestraR .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxR,$decimales,',','.').'</td>';
    //$muestraR .='                    <td class="derecha">'. $renglon->TipoIVA.'</td>';
//    $muestraR .='<td class="derecha">'. $renglon->promocion.'</td>';
    $muestraR .='</tr>';
endforeach;
$muestraR .='               </tbody>';
$muestraR .='           </table>';
$muestraR .='   </div>';
$muestraR .='   <div id="pieComprobante">';
// $muestraR .='       <div id="detalle">';
// $muestraR .='       <p><strong>Cant Items: '.$cantItems.' </strong> </p>';
// $muestraR .='       <p><strong>Detalle: </strong> '.$pedido->Detalle.'</p>';
// $muestraR .='       <p><strong>Observ: </strong> '.$pedido->detalle_comprobante.' </p>';
// $muestraR .='       <p><strong> <u>* Articulo en promoción </u></strong> </p>';
// $muestraR .='       <p><strong>Son Pesos: '.$pedido->ImporteVentaL.' </strong> </p>';
// $muestraR .='       </div>';
// $muestraR .='       <div id="importe">';
// $muestraR .='           <table>';
// $muestraR .='               <tr>';
// $muestraR .='                   <td colspan="2" class="izquierda">Neto:</td><td class="derecha">'.$pedido->SubTotalGral.'</td>';
// $muestraR .='               </tr>';
// $muestraR .='               <tr>';
// $muestraR .='                   <td colspan="2" class="izquierda">Descuento '.number_format($pedido->PorDesc1,$decimales).'%: </td><td class="derecha">'.$pedido->ImpDesc1.'</td>';
// $muestraR .='               </tr>';
// $muestraR .='               <tr>';
// $muestraR .='                   <td colspan="2" class="izquierda">Exento:</td><td class="derecha">'.$pedido->Exento.'</td>';
// $muestraR .='               </tr>';
// $muestraR .='               <tr>';
// $muestraR .='                   <td  class="izquierda">IVA:</td><td class="derecha">21,00</td><td class="derecha">'.$pedido->IVA1.'</td>';
// $muestraR .='               </tr>';
// $muestraR .='               <tr>';
// $muestraR .='                   <td  class="izquierda">IVA:</td><td class="derecha">10.50</td><td class="derecha">'.$pedido->IVA2.'</td>';
// $muestraR .='               </tr>';
// $muestraR .='               <tr>';
// $muestraR .='                   <td  class="izquierda">Percep IIBB Mza:</td><td class="derecha"></td><td class="derecha">'.$pedido->total_percep.'</td>';
// $muestraR .='               </tr>';
// $muestraR .='               <tr>';
// $muestraR .='                   <td colspan="3" class="separador">&nbsp;</td>';
// $muestraR .='               </tr>';
// $muestraR .='               <tr >';
// $muestraR .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.($pedido->ImporteVenta).'</td>';
// $muestraR .='               </tr>';

// $muestraR .='           </table>';
$muestraR .='           <table id="subtotales">';
$muestraR .='            <thead>';
$muestraR .='               <tr>';
$muestraR .='                   <th  class="izquierda">Neto</th>';
$muestraR .='                   <th  class="izquierda">Neto Desc/Int: '.number_format($pedido->PorDesc1,$decimales).'%</th>';
$muestraR .='                   <th  class="izquierda">Exento</th>';
$muestraR .='                   <th  class="izquierda">Percep</th>';
$muestraR .='                   <th  class="izquierda">Impuesto int</th>';
$muestraR .='                   <th  class="izquierda">IVA 21</th>';
$muestraR .='                   <th  class="izquierda">IVA 10,5</th>';
$muestraR .='                   <th  class="izquierda">Total</th>';


$muestraR .='               </tr>';
$muestraR .='            </thead>';
$muestraR .='            <tbody>';
$muestraR .='               <tr>';
$muestraR .='                   <td class="derecha">'.number_format($pedido->SubTotalGral,$decimales,',','.').'</td>';
$muestraR .='                   <td class="derecha">'.number_format($pedido->ImpDesc1,$decimales,',','.').'</td>';

$muestraR .='                   <td class="derecha">'.number_format($pedido->Exento,$decimales,',','.').'</td>';
$muestraR .='                   <td class="derecha">'.number_format($pedido->total_percep,$decimales,',','.').'</td>';
$muestraR .='                   <td class="derecha">'.number_format($pedido->impuesto_interno_interes,$decimales,',','.').'</td>';

$muestraR .='                   <td class="derecha">'.number_format($pedido->IVA1,$decimales,',','.').'</td>';
$muestraR .='                   <td class="derecha">'.number_format($pedido->IVA2,$decimales,',','.').'</td>';
$muestraR .='                   <td class="derecha">'.number_format($pedido->ImporteVenta,$decimales,',','.').'</td>';
$muestraR .='               </tr>';
$muestraR .='            </tbody>';
$muestraR .='           </table>';
$muestraR .='           <div id="detalle">';
$muestraR .='               <p><strong>Cant Items: '.$cantItems.' </strong> </p>';
$muestraR .='               <p><strong>Detalle: </strong> '.$pedido->Detalle;
$muestraR .='                 <strong>Observ: </strong> '.$pedido->detalle_comprobante.' </p>';
// $muestraR .='               <p><strong> <u>* Articulo en promoción </u></strong> </p>';
$muestraR .='               <p><strong>Son Pesos: '.$pedido->ImporteVentaL.' </strong> </p>';
$muestraR .='           </div>';
$muestraR .='       </div>'; // fin del pie del comprobante
$muestraR .='       <div id="final">'.PHP_EOL;

    $muestraR .='       <p class="administranet"> <img src="_img/logo-administranet-ecommerce.png" style="height:20px;"> Comprobante generado por: <strong>administraNET Gestión</strong> |';
    $muestraR .='      '.PHP_EOL;
    $muestraR .='           Comunicate a: <a  href="https://wa.me/5492615595573?text=%2A%3A%3Ahttps%3A%2F%2Fwww.administranet.com.ar%3A%3A%2A++Hola+tengo+una+consulta%21"><strong>54 9 2615 59-5573</strong></a> |  Visitanos en: <a href="http://www.administranet.com.ar"><strong>www.administranet.com.ar</strong></a></p>'.PHP_EOL;
$muestraR .='       </div>'.PHP_EOL;
$muestraR .='   </div>';
$muestraR .='</div>';

//PDF
//echo traeLogo();
require_once  '_lib/mpdf2/vendor/autoload.php';
$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];
// echo '<pre>';
// print_r($fontData);
// echo PHP_EOL;
// print_r($fontDirs);
// echo '</pre>';
$mpdf = new \Mpdf\Mpdf(['mode' => 'c',
        'fontDir' => array_merge($fontDirs, [
            __DIR__ . '/webfonts',
        ]),
        'fontdata' => $fontData + [ // lowercase letters only in font key
            'roboto' => [
                'R' => 'Roboto-Regular.ttf',
                'I' => 'Roboto-Regular.ttf',
            ],
            'fontawesome'=>[
                'R' => 'fa-regular-400.woff2'
            ]

        ],
        'default_font' => 'roboto',
    'margin_left' => 5,
	'margin_right' => 5,
	'margin_top' => 4,
	'margin_bottom' => 5,
	'margin_header' => 5,
	'margin_footer' => 7,
    'dpi' => 100,
    'img_dpi' => 100,
    ]);


$stylesheet = file_get_contents('_css/pdf.css');
    $mpdf->WriteHTML($stylesheet,1);
    $mpdf->WriteHTML($muestraR,2);
    $mpdf->SetDisplayMode('fullpage');
//    echo $muestraR;
    $mpdf->Output();
    // $mpdf->Output('Ped-'.$pedido->NroComprobante.'.pdf','D');
    exit; 