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
    $query = "SELECT 
                    logo AS Foto,
                    'image/pjpeg' AS Tipo 
            FROM configuracion;";
    $sal=mysqli_query($connV,$query)or die("no anduvo".mysqli_error($connV));
    $fila=mysqli_fetch_array($sal);
    
    $fileName ="_img/logototal.png";
//    echo "Logo<pre>";
//    echo $fila["Foto"];
//    echo "</pre>";
    $foto = imagepng(imagecreatefromstring($fila["Foto"]), $fileName) ;
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

function hacer_nc_electronica($ncredito,$renglones,$empresa,$codMov,$connV){
    $imgCodBarra= trae_cod_barra($ncredito,$empresa['cuit_empresa']);
    
     $tipoF=array("A"=>"03","B"=>"08","M"=>"53","C"=>"13","E"=>"21");
    
    $codigoTipoComp= $tipoF[$ncredito->tipoNcredito]; 
//    echo "<pre>";
//    print_r($ncredito);
//    echo "</pre>";
    
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
                $arrHtml= cuerpo_concepto_ncc_ncc($renglones,$ncredito);
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
    $feHtml .='             <img src="_img/logo_afip.jpg" style="width:350px">';
    $feHtml .='             '.$imgCodBarra.'';
    $feHtml .='         </div>';
    $feHtml .='         <div id="final-der">';
    $feHtml .='             <p><label><strong>Nro CAE: </strong></label> '.$ncredito->fe_cae.'<br>';
    $feHtml .='                 <label><strong>Vto CAE: </strong></label> '.$ncredito->vtoCae.'</p>';
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
                $arrHtml= cuerpo_concepto_ncc_ncc($renglones,$ncredito);
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
    $faHtml .='               <img src="_img/logo_administranet_chico.gif"></p>';
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
    //echo "soy cuerpo_dev_nc_iva";
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
//    echo "<pre>";
//    print_r($renglon);
//    echo "</pre>";
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





/// ANALISIS DE PARAMETROS
//==============================================================================
//==============================================================================

//echo "<pre>";
//print_r($_GET);
//echo "</pre>";
if(isset($_GET['codigomovimiento'])){
    $accion="descarga";
    // determino si mando el pdf como adjunto o lo quier simplemente descargar.
    if(isset($_GET["accion"])){
        $accion=$_GET["accion"];
    }
                $codMov=$_GET["codigomovimiento"];
                
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
                                     GROUP_CONCAT(CONCAT(percep_cli_tipo.nombre_percep_cli_tipo,'= $', percep_cli.importe_percep_cli) SEPARATOR ' - ') AS detPercep
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
                $hacerPed = mysqli_query($connV,$sqlPedido) or die('No puedo recuperar la factura'.mysqli_error($connV).'<br><pre>'.$sqlPedido.'</pre>');
                $notaCredito = mysqli_fetch_object($hacerPed);
                $renglones = array();
                /* NOTA DE CREDITO X CONCEPTO
                 * =============================================================
                 */
                if($notaCredito->TipoNC=="Concepto"){
                     //rs_stock.Open "SELECT * FROM nc_concepto WHERE CodigoMovimiento = " & DataConsulta.Recordset.Fields!CodigoMovimiento
                     $sqlRenglon="SELECT * FROM nc_concepto WHERE nc_concepto.CodigoMovimiento=".$notaCredito->CodigoMovimiento; 
                     $hacerRenglon = mysqli_query($connV,$sqlRenglon) or die('no puedo recuperar el renglon'.mysqli_error($connV));
                     while($renglon=  mysqli_fetch_object($hacerRenglon)){
                        $renglones[]=$renglon;
                    }
                     
                }
                
                if($notaCredito->TipoNC=="Descuento" || $notaCredito->TipoNC=="Importe"){
//                    $hacerRenglon = mysqli_query($connV,$sqlRenglon) or die('no puedo recuperar el renglon'.mysqli_error($connV));
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

                    $hacerRenglon = mysqli_query($connV,$sqlRenglon) or die('no puedo recuperar el renglon'.mysqli_error($connV));
                    while($renglon=  mysqli_fetch_object($hacerRenglon)){
                        $renglones[]=$renglon;
                    }
                }
//                echo '<pre>'.print_r($renglones).'</pre>';
//                echo '<pre>';
//                print_r($factura->fe_comp);
//                echo '</pre>';
    if($notaCredito->fe_comp=="Si"){
        $m = hacer_nc_electronica($notaCredito, $renglones, $empresa, $codMov,$connV);
    }else{
        $m = hacer_nc($notaCredito, $renglones, $empresa,$connV);
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
//   echo"<style>". $stylesheet ."</style>";
//   echo $m;
    if($accion=="descarga"){
        $mpdf->Output('NC-'.$notaCredito->tipoNcredito.'_'.$notaCredito->NroComprobante.'.pdf','D');
    }else{
        $mpdf->Output('NC-'.$notaCredito->tipoNcredito.'_'.$notaCredito->NroComprobante.'.pdf','I');
    }
    exit; 
}else{
    echo "Falta parametro obligatorio";
}
