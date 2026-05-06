<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'sesion.inc.php';
/*
 *  FUNCIONES
 */


function lista_recibos($connV,$arrFiltros=null){
    // retorna el html por ahora.
    $caminoDispo="";
    $soyMovil=0;
    $idUsuario=$_SESSION['idusuario'] ;
    $consulta="";
//    echo "<pre>";
//    print_r($_SESSION);
//    echo "</pre>";

//    echo "<pre>";
//    print_r($arrFiltros);
//    echo "</pre>";
    
    if(isset($_SESSION["caminoDisp"])&&$_SESSION["caminoDisp"]!=''){
        // soy movil
        $caminoDispo=$_SESSION["caminoDisp"];
        $soyMovil=1;
    }
    if(isset($arrFiltros['vendedor'])&&$arrFiltros['vendedor']!='todos'){
        $codVendedor = $arrFiltros['vendedor'];
        $consulta .=" AND cuentacliente.CodViajante=".$codVendedor;

    }

    if(!isset($arrFiltros['vendedor'])){
        $consulta .=" AND cuentacliente.IdUsuario=".$idUsuario;
    }
    if(isset($arrFiltros["fecha"])){
        $fechaDesde=$arrFiltros["fecha"]["desde"];
        $fechaHasta=$arrFiltros["fecha"]["hasta"];
        $consulta .=" AND Fecha BETWEEN '".$fechaDesde . "' AND '". $fechaHasta."'";        
    }

    
    // filtro de clientes
    if(isset($arrFiltros["cliente"])){
        $consulta .=" AND cuentacliente.Codigo=".$arrFiltros["cliente"];
    }
    
    
    
    
    $sqlRecibo= "SELECT 
                           DATE_FORMAT(cuentacliente.Fecha,'%d/%m/%Y') AS FechaB,
                           DATE_FORMAT(cuentacliente.Fecha,'%Y%m%d') AS Fecha,
                            cuentacliente.TipoComprobante,
                            cuentacliente.tiporec AS tipoRecibo,
                            cliente.Codigo AS codCliente,
                            cliente.id_manual_cli AS codManualCliente,
                            cliente.nombre_cliente AS nombre_cliente,
                            cuentacliente.NroComprobante,
                            cuentacliente.TotalImputacionRec AS ImporteTotal,                           
                            cuentacliente.ImporteCobro AS Importe,                            
                            cuentacliente.TotalEfectivoP AS Efectivo,
                            cuentacliente.TotalEfectivoD AS Dolar,
                            cuentacliente.TotalCheque As Cheque,
                            cuentacliente.TotalRetencion As Retencion,
                            COALESCE(cuentacliente.total_trans,0) AS Transferencia,
                            cuentacliente.Total_Tarjeta AS Tarjeta,
                            cuentacliente.TotalDescRec As Descuento,
                            cuentacliente.CodigoMovimiento,
                            cuentacliente.codigo_movimiento_anul,
                            cuentacliente.Detalle,
                            CONCAT(cuenta_banco.NroCuenta,' ',banco.Nombre)AS transfDetalle,
                            CONCAT(viajantes.Nombre,' (Cod: ',viajantes.CodViajante,')') AS viajante,
                            cuentacliente.Anulado
                            
                            
                    FROM 
                        cuentacliente
                    LEFT JOIN cliente ON cliente.Codigo=cuentacliente.Codigo
                    #detalle transferencia
                    
                    LEFT JOIN cuenta_banco ON cuenta_banco.CodCuenta = cuentacliente.ctabanc_trans
                    LEFT JOIN banco ON cuenta_banco.CodBanco = banco.CodBanco   
                    LEFT JOIN viajantes ON viajantes.CodViajante=cuentacliente.CodViajante                      
           
                    WHERE 
                     
                        cuentacliente.TipoComprobante='REC' 
                        AND ISNULL(cuentacliente.codigo_movimiento_anul)
                        $consulta
                    ORDER BY cuentacliente.Fecha DESC,cuentacliente.CodigoMovimiento DESC";
    
//    echo $sqlRecibo;
    // echo '<pre>',$sqlRecibo,'</pre>';
    $hacer = mysqli_query($connV,$sqlRecibo) or die('No puedo consultar el recibo '.$sqlRecibo.PHP_EOL.'error:'.mysqli_error($connV));
    
    
//    if($soyMovil==0){
    /* SOY WEB **/
     $muestro='';  
        if($hacer){
             
            $arrRecibos = array();
            while($p=  mysqli_fetch_assoc($hacer)){
                $arrRecibos[] = $p;
            }
            // if($soyMovil==0){
            //     $muestro = html_web($arrRecibos);
            // }
            // if($soyMovil==1){
            //     $muestro= html_movil($arrRecibos);
            // }
            $muestro = html_web($arrRecibos);
            
        }
    echo $muestro;
    
}

function html_web($arrRecibos){

    $html='';
    $subTotalImputado = 0;
    $subTotalImporte=0;
    $subTotalEfectivo=0;
    $subTotalDolar=0;
    $subTotalCheque=0;
    $subTotalDescuento=0;
    $subTotalRetencion=0;
    $subTotalTarjeta=0;
    $subTotalTransferencia = 0;

    $usaIdManual='No';
    if(isset($_SESSION["usa_id_manual"])){
        $usaIdManual = $_SESSION["usa_id_manual"];  
    } 

    if(count($arrRecibos)==0){
                $html = "";
                $html .='<thead>';
                $html .='            <tr>';                       
                $html .='                <th>Fecha</th>';
                $html .='                <th>N°Comp.</th>';
                $html .='                <th>Cliente</th>';
                $html .='                <th>Tipo</th>';
                $html .='                <th>Detalle</th>';
                $html .='                <th class="dt-right">Importe</th>';
                $html .='                <th class="dt-right">Efectivo</th>';                
                $html .='                <th class="dt-right">Dolar</th>';
                $html .='                <th class="dt-right">Cheque</th>'; 
                $html .='                <th class="dt-right">Trasnf</th>'; 
                $html .='                <th class="dt-right">Tarjeta</th>'; 
                $html .='                <th>Descuento</th>'; 
                $html .='                <th>Retención</th>';
                $html .='            </tr>';
                $html .='</thead>';
                $html .='<tbody>';
//                $html .='<tr>';
//                $html .='<td><td>';
//                $html .='No se encontaron resultados';
//                $html .='</td>';
//                $html .='</tr>';
                $html .='</tbody>';
            }else{
                $html = "";
                $html .='<thead>';
                $html .='            <tr>';
                $html .='                <th>Fecha</th>';
                $html .='                <th>N°Comp.</th>';
                $html .='                <th>Cliente</th>';
                $html .='                <th>Tipo</th>';
                $html .='                <th>Detalle</th>';
                $html .='                <th class="dt-right">Importe</th>';
                $html .='                <th class="dt-right">Cobrado</th>';
                $html .='                <th class="dt-right">Efectivo</th>';                
                $html .='                <th class="dt-right">Dolar</th>';
                $html .='                <th class="dt-right">Cheque</th>';  
                $html .='                <th class="dt-right">Trasnf</th>'; 
                $html .='                <th class="dt-right">Tarjeta</th>'; 
                $html .='                <th class="dt-right">Descuento</th>';  
                $html .='                <th class="dt-right">Retención</th>';
                $html .='                <th>DetalleTransf</th>';
                $html .='                <th>Viajante</th>';

                $html .='                <th>&nbsp;</th>';
                $html .='               <th>Anul.</th>';
                
                $html .='            </tr>';
                $html .='        </thead>';
                $html .='        <tbody>';
                foreach($arrRecibos as $recibo){
                    if($recibo['Anulado']=='No'){
                        $cssAnulado = "";
                        $subTotalImporte += $recibo['Importe'];
                        $subTotalImputado += $recibo['ImporteTotal'];
                        $subTotalEfectivo +=$recibo['Efectivo'];
                        $subTotalDolar += $recibo['Dolar'];
                        $subTotalCheque += $recibo['Cheque'];
                        $subTotalDescuento +=$recibo['Descuento'];
                        $subTotalRetencion +=$recibo['Retencion'];
                        $subTotalTarjeta +=$recibo['Tarjeta'];
                        $subTotalTransferencia +=$recibo['Transferencia'];
                    }
                    if($recibo['Anulado']=='Si'){
                        $cssAnulado = "rojo";
                    }

                    $html .='        <tr>';
                    $html .='                <td class="'.$cssAnulado.' dt-nowrap" data-order="'.$recibo['Fecha'].'">
                                                '.$recibo['FechaB'].'</td>';
                    $html .='                <td class="'.$cssAnulado.' dt-nowrap">'.$recibo['NroComprobante'].'</td>';
                    if($usaIdManual=='No'){
                        $html .='<td class="'.$cssAnulado.'">'.$recibo['nombre_cliente'].' (Cod: '.$recibo['codCliente'] .')</td>';
                    }
                    if($usaIdManual=='Si'){
                        $html .='<td class="'.$cssAnulado.'">'.$recibo['nombre_cliente'].' (Cod: '.$recibo['codigoManualCliente'] .')</td>';

                    }
                    $html .='                <td class="'.$cssAnulado.'">'.$recibo['tipoRecibo'].'</td>';
                    $html .='                <td class="'.$cssAnulado.'">'.$recibo['Detalle'].'</td>';
                    $html .='                <td class="'.$cssAnulado.' dt-right" data-display="'.number_format($recibo['ImporteTotal'],2,',','.').'">'.$recibo['ImporteTotal'].'</td>';
                    $html .='                <td class="'.$cssAnulado.' dt-right" data-display="'.number_format($recibo['Importe'],2,',','.').'">'.$recibo['Importe'].'</td>';
                    $html .='                <td class="'.$cssAnulado.' dt-right">'.$recibo['Efectivo'].'</td>';              
                    $html .='                <td class="'.$cssAnulado.' dt-right">'.$recibo['Dolar'].'</td>';              
                    $html .='                <td class="'.$cssAnulado.' dt-right">'.$recibo['Cheque'].'</td>'; 
                    $html .='                <td class="'.$cssAnulado.' dt-right">'.$recibo['Transferencia'].'</td>'; 
                    $html .='                <td class="'.$cssAnulado.' dt-right">'.$recibo['Tarjeta'].'</td>'; 
                    $html .='                <td class="'.$cssAnulado.' dt-right">'.$recibo['Descuento'].'</td>';         
                    $html .='                <td class="'.$cssAnulado.' dt-right">'.$recibo['Retencion'].'</td>';
                    $html .='                <td class="'.$cssAnulado.'">'.$recibo['transfDetalle'].'</td>';
                    $html .='                <td class="'.$cssAnulado.'">'.$recibo['viajante'].'</td>';

                    $html .='                <td class="'.$cssAnulado.'dt-nowrap">
                                                <span class="acciones">
                                                    <a  target="blank" href="ver_recibo_movil.php?codigomovimiento='.$recibo["CodigoMovimiento"].'&tipocomprobante=REC"  title="Visualizar comprobante" alt="Visualizar comprobante" mov="'.$recibo["CodigoMovimiento"].'" comprobante="REC">
                                                        <i class="fas fa-file-pdf barrita fa-lg fa-fw "></i>    
                                                    </a>
                                                    <a  href="relay-comprobante-a-mail.php?codMov='.$recibo["CodigoMovimiento"].'&tipocomprobante=1"  title="mandar Email" alt="enviar email" mov="'.$recibo["CodigoMovimiento"].'" comprobante="REC">
                                                        <i class="fas fa-envelope barrita fa-lg fa-fw "></i>    
                                                    </a>
                                                    </span>
                                                </td>';


                    $html .='                <td class="'.$cssAnulado.'">'.$recibo['Anulado'].'</td>';

                    $html .='        </tr>';
            }
                $html .='</tbody>';
                // el pie 
                $html .='<tfoot>';
                $html .='<tr>';
                $html .='<th></th>'
                        . '<th></th>'    
                        . '<th></th>'    
                        . '<th></th>'               
                        . '<th>Total</th>';
                $html .='<th class="dt-right">'.$subTotalImputado.'</th>';
                $html .='<th class="dt-right">'.$subTotalImporte.'</th>';
                $html .='<th class="dt-right">'.$subTotalEfectivo.'</th>';              
                $html .='<th class="dt-right">'.$subTotalDolar.'</th>';              
                $html .='<th class="dt-right">'.$subTotalCheque.'</th>'; 
                $html .='<th class="dt-right">'.$subTotalTransferencia.'</th>'; 
                $html .='<th class="dt-right">'.$subTotalTarjeta.'</th>'; 
                $html .='<th class="dt-right">'.$subTotalDescuento.'</th>';         
                $html .='<th class="dt-right">'.$subTotalRetencion.'</th>';    
                $html .='<th></th>';    
                $html .='<th></th>';  
                $html .='<th></th>';
                $html .='<th></th>';  
                $html .='</tr>';
                $html .='</tfoot>';
            }
    
    return $html;
}

function html_movil($arrRecibos){
    $html='';
    $subTotalImporte=0;
    $subTotalImputado=0;
    $subTotalEfectivo=0;
    $subTotalDolar=0;
    $subTotalCheque=0;
    $subTotalDescuento=0;
    $subTotalRetencion=0;
    $subTotalTarjeta=0;
    $subTotalTransferencia=0;

    if(count($arrRecibos)==0){
                $html = "";
                $html .='<thead>';
                $html .='            <tr>';                       
                $html .='                <th>Fecha</th>';
                $html .='                <th>N°Comp.</th>';               
                $html .='                <th>Cliente</th>';
                $html .='                <th>Tipo</th>';
                $html .='                <th>Importe</th>';
                $html .='                <th>Efectivo</th>';                
                $html .='                <th>Dolar</th>';
                $html .='                <th>Cheque</th>'; 
                $html .='                <th>Transf</th>'; 
                $html .='                <th>Tarjeta</th>'; 
                $html .='                <th>Descuento</th>'; 
                $html .='                <th>Retención</th>';
                $html .='            </tr>';
                $html .='</thead>';
                $html .='<tbody>';
//                $html .='<tr>';
//                $html .='<td><td>';
//                $html .='No se encontaron resultados';
//                $html .='</td>';
//                $html .='</tr>';
                $html .='</tbody>';
            }else{
                $html = "";
                $html .='<thead>';
                $html .='            <tr>';
                $html .='                <th>Fecha</th>';
                $html .='                <th>N°Comp.</th>';                
                $html .='                <th>Cliente</th>';
                $html .='                <th>Tipo</th>';
                $html .='                <th>Importe</th>';
                $html .='                <th>Cobrado</th>';
                $html .='                <th>Efectivo</th>';                
                $html .='                <th>Dolar</th>';
                $html .='                <th>Cheque</th>';
                $html .='                <th>Transf</th>'; 
                $html .='                <th>Tarjeta</th>'; 
                $html .='                <th>Descuento</th>';  
                $html .='                <th>Retención</th>';
                $html .='                <th>DetalleTransf</th>';
                $html .='                <th>Viajante</th>';
                $html .='                <th>&nbsp;</th>';
                $html .='               <th>Anul.</th>';
                
                $html .='            </tr>';
                $html .='        </thead>';
                $html .='        <tbody>';
                foreach($arrRecibos as $recibo){    
                    
                    if($recibo['Anulado']=='Si'){
                        $cssAnulado = "rojo";
                    //    $recibo['Importe']=$recibo['Importe']*-1;
                    //    $recibo['Efectivo'] = $recibo['Efectivo']*-1;
                    //    $recibo['Dolar'] = $recibo['Dolar']*-1;
                    //    $recibo['Cheque'] =$recibo['Cheque']*-1;
                    //    $recibo['Descuento'] =$recibo['Descuento']*-1;
                    //    $recibo['Retencion']=$recibo['Retencion']*-1;
                    //    $recibo['Tarjeta']=$recibo['Tarjeta']*-1;
                    //    $recibo['Transferencia']=$recibo['Transferencia']*-1;
                    }

                    if($recibo['Anulado']=='No'){
                        $cssAnulado = "";
                        $subTotalImporte += $recibo['Importe'];
                        $subTotalImputado += $recibo['ImporteTotal'];
                        $subTotalEfectivo +=$recibo['Efectivo'];
                        $subTotalDolar += $recibo['Dolar'];
                        $subTotalCheque += $recibo['Cheque'];
                        $subTotalDescuento +=$recibo['Descuento'];
                        $subTotalRetencion +=$recibo['Retencion'];
                        $subTotalTarjeta +=$recibo['Tarjeta'];
                        $subTotalTransferencia +=$recibo['Transferencia'];

                        
                    }
                    
                    

                    $html .='        <tr>';
                    $html .='                <td class="'.$cssAnulado.' dt-nowrap" data-order="'.$recibo['Fecha'].'">
                                                '.$recibo['FechaB'].'</td>';
                    $html .='                <td class="'.$cssAnulado.' dt-nowrap">'.$recibo['NroComprobante'].'</td>';
                    
                    $html .='                <td class="'.$cssAnulado.'">'.$recibo['nombre_cliente'].'</td>';
                    $html .='                <td class="'.$cssAnulado.'">'.$recibo['Detalle'].'</td>';
                    $html .='                <td class="'.$cssAnulado.' dt-right" data-display="'.number_format($recibo['Importe'],2,',','.').'">'.$recibo['ImporteTotal'].'</td>';
                    $html .='                <td class="'.$cssAnulado.' dt-right" data-display="'.number_format($recibo['Importe'],2,',','.').'">'.$recibo['Importe'].'</td>';
                    $html .='                <td class="'.$cssAnulado.' dt-right">'.$recibo['Efectivo'].'</td>';              
                    $html .='                <td class="'.$cssAnulado.' dt-right">'.$recibo['Dolar'].'</td>';              
                    $html .='                <td class="'.$cssAnulado.' dt-right">'.$recibo['Cheque'].'</td>'; 
                    $html .='                <td class="'.$cssAnulado.' dt-right">'.$recibo['Transferencia'].'</td>'; 
                    $html .='                <td class="'.$cssAnulado.' dt-right">'.$recibo['Tarjeta'].'</td>'; 
                    $html .='                <td class="'.$cssAnulado.' dt-right">'.$recibo['Descuento'].'</td>';         
                    $html .='                <td class="'.$cssAnulado.' dt-right">'.$recibo['Retencion'].'</td>';
                    $html .='                <td class="'.$cssAnulado.'">'.$recibo['transfDetalle'].'</td>';
                    $html .='                <td class="'.$cssAnulado.'">'.$recibo['viajante'].'</td>';

                    $html .='                <td class="'.$cssAnulado.'dt-nowrap">
                                                <span class="acciones">
                                                    <a  target="blank" href="ver_recibo_movil.php?codigomovimiento='.$recibo["CodigoMovimiento"].'&tipocomprobante=REC"  title="Visualizar comprobante" alt="Visualizar comprobante" mov="'.$recibo["CodigoMovimiento"].'" comprobante="REC">
                                                        <i class="fas fa-file-pdf barrita fa-lg fa-fw "></i>    
                                                    </a>
                                                    <a  href="relay-comprobante-a-mail.php?codMov='.$recibo["CodigoMovimiento"].'&tipocomprobante=1"  title="mandar Email" alt="enviar email" mov="'.$recibo["CodigoMovimiento"].'" comprobante="REC">
                                                        <i class="fas fa-envelope barrita fa-lg fa-fw "></i>    
                                                    </a>
                                                    </span>
                                                </td>';


                    $html .='                <td class="'.$cssAnulado.'">'.$recibo['Anulado'].'</td>';

                    $html .='        </tr>';
                }
            $html .='</tbody>';
            $html .='<tfoot>';
                $html .='<tr>';
                $html .='<th>Total recibos</th>'
                        . '<th></th>'     
                        . '<th></th>'
                        . '<th></th>';
                $html .='<th class="dt-right">'.$subTotalImputado.'</th>';
                $html .='<th class="dt-right">'.$subTotalImporte.'</th>';
                $html .='<th class="dt-right">'.$subTotalEfectivo.'</th>';              
                $html .='<th class="dt-right">'.$subTotalDolar.'</th>';              
                $html .='<th class="dt-right">'.$subTotalCheque.'</th>'; 
                $html .='<th class="dt-right">'.$subTotalTransferencia.'</th>'; 
                $html .='<th class="dt-right">'.$subTotalTarjeta.'</th>'; 
                $html .='<th class="dt-right">'.$subTotalDescuento.'</th>';         
                $html .='<th class="dt-right">'.$subTotalRetencion.'</th>';    
                $html .='<th></th>';
                $html .='<th></th>';
                $html .='<th></th>';
                $html .='<th></th>';
                $html .='</tr>';
                $html .='</tfoot>';
            }
    return $html;
}

function ver_recibo_pdf($connV,$codMovRec){}

/*
 * CONTROLADOR
 */

// listado de recibos nuevo formato.
if(isset($_REQUEST["consulta"])&&$_REQUEST["consulta"]==1){
    $arrFiltros=array();
    if(isset($_REQUEST["campoBusca"])&&$_REQUEST["campoBusca"]==="Fecha"){
        $arrFiltros["fecha"]["desde"]=$_REQUEST["fechaDesde"];
        $arrFiltros["fecha"]["hasta"]=$_REQUEST["fechaHasta"];
    }
    if(isset($_REQUEST["filtraCliente"])&&$_REQUEST["filtraCliente"]!=="todos"){
        $arrFiltros["cliente"] = $_REQUEST["filtraCliente"];
    }
    // vendedor
    if(isset($_REQUEST["filtraVendedor"])){
        $arrFiltros["vendedor"] = $_REQUEST["filtraVendedor"];
    }
  //print_r($_REQUEST);
    lista_recibos($connV,$arrFiltros);
        
}