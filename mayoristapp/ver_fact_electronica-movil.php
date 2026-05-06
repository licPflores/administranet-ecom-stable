<?php require_once 'sesion.inc.php';
//phpinfo();
// FACTURA ELECTRONICA 
/*
 * Parametros de empresa
 */
$empresa=array(
            "nombre_empresa"=>$_SESSION['nombre_empresa'],
            "domicilio_empresa"=>$_SESSION['domicilio_empresa'],
            "telefono_empresa"=>$_SESSION['telefono_empresa'],
            "email_empresa"=>$_SESSION['email_empresa'],
            "iva_empresa"=>$_SESSION['iva_empresa'],
            "cuit_empresa"=>$_SESSION['cuit_empresa'],
            "iniact_empresa"=> $_SESSION['iniact_empresa'],
            "ingbrutos_empresa"=>$_SESSION['ingbrutos_empresa']    
);    

/*
 * FUNCIONES
 * ============================================================================
 * ============================================================================
 * 
 */

/*function traeLogo
 * =============================================================================
 *  */
function traeLogo($connV){
    // $query = "SELECT 
    //                 logo AS Foto,
    //                 'image/pjpeg' AS Tipo 
    //         FROM configuracion;";
    // $sal=mysqli_query($connV,$query)or die("no anduvo".mysqli_error($connV));
    // $fila=mysqli_fetch_array($sal);
    
    $fileName ="_img/logototal.png";
//    echo "Logo<pre>";
//    echo $fila["Foto"];
//    echo "</pre>";
    // $foto = imagepng(imagecreatefromstring($fila["Foto"]), $fileName) ;
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
    $url="_img/barcode/".$nroCodBarra.".png";
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
/* funcion que creo la imagen del codigo de barra*/
function genera_imagen_barra($nroCodBarra){
    $url="http://www.barcodes4.me/barcode/c128b/".$nroCodBarra.".png?IsTextDrawn=1&width=400&height=100";
    $destino="_img/barcode/".$nroCodBarra.".png";
    //$im=curl_init($url);
    $vuelta=0; // todo bien
//    echo $url;
//    $ch = curl_init ($url);
//    curl_setopt($ch, CURLOPT_HEADER, 0);
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
//    curl_setopt($ch, CURLOPT_FAILONERROR, true);
//
//    $rawdata=curl_exec($ch);
//    echo var_dump($ch);
//    if (curl_errno($ch)) {
//        $error_msg = curl_error($ch);
//        $vuelta++;
//    }
//    curl_close ($ch);
//    if(!isset($error_msg)){
//        $fp = fopen($destino,'x');
//        fwrite($fp, $rawdata);
//        fclose($fp); 
//    }
//    if(isset($error_msg)){
//        echo "<pre>";
//        echo var_dump($error_msg);
//    }
    
    
    // codigo que si anda
//    $filename =$destino;
//    $complete_save_loc = $destino;
//    //echo $complete_save_loc;
//    $fp = fopen($complete_save_loc, 'wb');
//    $ch = curl_init ($url);
//   
//    curl_setopt($ch, CURLOPT_FILE, $fp);
//    curl_setopt($ch, CURLOPT_HEADER, 0);
//    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
////    curl_setopt($ch, CURLOPT_VERBOSE, true);
//    curl_exec($ch);
//     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//     echo "<pre>";
//    print_r(curl_getinfo($ch));
//   
//    var_dump($ch);
//    var_dump($httpCode);
//    if ($errno = curl_errno($ch)) {
//        $error_message = curl_strerror($errno);
//        echo "cURL error ({$errno}):\n {$error_message}";
//    }
//    curl_close($ch);
//    fclose($fp);
    

//The cURL stuff...
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
    
    
    return $vuelta;
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
 * Generar HTML Factura electronica
 */

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
    $feHtml .='                   <td colspan="2" class="izquierda">Descuento '.number_format($fact->PorDesc1,2,",",".").'%: </td><td class="derecha">'.number_format($fact->ImpDesc1,2,",",".").'</td>';
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
    $feHtml .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.number_format(($fact->Total+$fact->total_percep),2,",",".").'</td>';
    $feHtml .='               </tr>';

    $feHtml .='           </table>';

    $feHtml .='       </div>';
    
    $feHtml .='       <div id="final">';
    $feHtml .='         <div id="final-izq">';
    $feHtml .='             <img src="_img/logo_afip.jpg" style="width:350px">';
    $feHtml .='             '.$imgCodBarra.'';
    $feHtml .='         </div>';
    $feHtml .='         <div id="final-der">';
    $feHtml .='             <p><label><strong>Nro CAE: </strong></label> '.$fact->fe_cae.'<br>';
    $feHtml .='                 <label><strong>Vto CAE: </strong></label> '.$fact->vtoCae.'</p>';
    $feHtml .='             <p>Comprobante generado por: ';
    $feHtml .='             <img src="_img/logo_administranet_chico.gif"></p>';
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
        $faHtml .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxU,4,",",".").'</td>';
        $faHtml .='<td class="derecha">'. $renglon->PorDesc.'</td>';
        $faHtml .='<td class="derecha">$'. number_format($renglon->PrecioVentaxU,4,",",".").'</td>';

        //$faHtml .='                    <td class="derecha">'. $renglon->Alicuota.'</td>';
        //$faHtml .='                    <td class="derecha">'. $renglon->ImpDesc.'</td>';

        //$faHtml .='                    <td class="derecha">$'.$renglon->PrecioNetoxR.'</td>';
        //$faHtml .='                    <td class="izquierda">'.$renglon->NroPresupuesto.'</td>';
        $faHtml .='<td class="derecha">$'. number_format($renglon->PrecioBrutoxR,4,",",".").'</td>';
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
    $faHtml .='                   <td colspan="2" class="izquierda">Neto:</td><td class="derecha">'.number_format($fact->SubTotalGral,2,",",".").'</td>';
    $faHtml .='               </tr>';
    $faHtml .='               <tr>';
    $faHtml .='                   <td colspan="2" class="izquierda">Descuento '.number_format($fact->PorDesc1,2,",",".").'%: </td><td class="derecha">'.number_format($fact->ImpDesc1,2,",",".").'</td>';
    $faHtml .='               </tr>';
    $faHtml .='               <tr>';
    $faHtml .='                   <td colspan="2" class="izquierda">Exento:</td><td class="derecha">'.number_format($fact->Exento,2,",",".").'</td>';
    $faHtml .='               </tr>';
    $faHtml .='               <tr>';
    $faHtml .='                   <td  class="izquierda">IVA:</td><td class="derecha">21,00</td><td class="derecha">'.number_format($fact->IVA1,2,",",".").'</td>';
    $faHtml .='               </tr>';
    $faHtml .='               <tr>';
    $faHtml .='                   <td  class="izquierda">IVA:</td><td class="derecha">10.50</td><td class="derecha">'.number_format($fact->IVA2,2,",",".").'</td>';
    $faHtml .='               </tr>';
    $faHtml .='               <tr>';
    $faHtml .='                   <td  class="izquierda">Percep IIBB Mza:</td><td class="derecha"></td><td class="derecha">'.number_format($fact->total_percep,2,",",".").'</td>';
    $faHtml .='               </tr>';
    $faHtml .='               <tr>';
    $faHtml .='                   <td colspan="3" class="separador">&nbsp;</td>';
    $faHtml .='               </tr>';
    $faHtml .='               <tr >';
    $faHtml .='                   <td class="total" colspan="2">Total:</td><td class="total derecha">'.number_format(($fact->Total+$fact->total_percep),2,",",".").'</td>';
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
    $faHtml .='               <img src="_img/logo_administranet_chico.gif"></p>';
    $faHtml .='               <p>Tel:(0261)- 4274480 / 4283071 |  <a href="http://www.administranet.com.ar">www.administranet.com.ar</a></p>';
    $faHtml .='           </div>';    
    $faHtml .='       </div>';
    $faHtml .='   </div>';
    $faHtml .='</div>';
    return $faHtml;
}

//echo "<pre>";
//print_r($_GET);
//echo "</pre>";
if(isset($_GET['codigomovimiento'])){
                
                $codMov=$_GET["codigomovimiento"];
                
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
                
                $hacerPed = mysqli_query($connV,$sqlPedido) or die('No puedo recuperar la factura'.mysqli_error($connV).'<br><pre>'.$sqlPedido.'</pre>');
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
                
                $hacerRenglon = mysqli_query($connV,$sqlRenglon) or die('no puedo recuperar el renglon'.mysqli_error($connV));
                while($renglon=  mysqli_fetch_object($hacerRenglon)){
                    $renglones[]=$renglon;
                }
                
//                echo '<pre>'.print_r($renglones).'</pre>';
//                echo '<pre>';
//                print_r($factura->fe_comp);
//                echo '</pre>';
    if($factura->fe_comp=="Si"){
        $m = hacer_factura_electronica($factura, $renglones, $empresa, $codMov,$connV);
    }else{
        $m = hacer_factura($factura, $renglones, $empresa,$connV);
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
    $mpdf->WriteHTML($m,2);
    $mpdf->SetDisplayMode('fullpage');
   
   //echo $m;
   $mpdf->Output('FACT-'.$factura->NroComprobante.'.pdf','D');
    exit; 
}else{
    echo "Falta parametro obligatorio";
}
