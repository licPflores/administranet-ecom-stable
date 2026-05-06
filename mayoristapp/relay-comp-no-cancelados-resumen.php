<?php
header('Content-type: text/html; charset=utf-8');
require_once 'sesion.inc.php';



if(isset($_REQUEST['ajax'])){
        
    $campoBusca = null;
    $estadoPedido = null;
    $consulta="";
    $soyMovil=0;
    $caminoDispo= $_SESSION['caminoDisp'];
    if($caminoDispo!=""){
        $soyMovil=1;
    }
    
    
    
    
//    busco por campo ademas del estado si estuviere.
//    @numeroComp: buscar por el numero de pedido
//    @fechaDesde : desde el periodo
//    @fechaHasta : hasta del periodo
    
    
    if(isset($_REQUEST['campoBusca'])&& $_REQUEST['campoBusca']!=""){
        $campoBusca = $_REQUEST['campoBusca'];
        switch($campoBusca){
            case 'Fecha':
//                $fed = explode("/",$_REQUEST['fechaDesde']);
//                $fechaDesde = $fed[2].'/'. $fed[1].'/'.$fed[0];
//                $feh = explode("/",$_REQUEST['fechaHasta']);
//                $fechaHasta = $feh[2].'/'. $feh[1].'/'.$feh[0];
                $fechaDesde = $_REQUEST['fechaDesde'];
                $fechaHasta = $_REQUEST['fechaHasta'];
                
                $consulta .=" AND Fecha BETWEEN '".$fechaDesde . "' AND '". $fechaHasta."'";
                break;
            case 'NroComprobante':
                $numeroComp = $_REQUEST['numeroComp'];
                $consulta .=" AND NroCompBusq LIKE '".$numeroComp."%'";
                break;
        }
        
                
    }
//    busco el estado del pedido
    if(isset($_REQUEST['estadoPedido'])&&$_REQUEST['estadoPedido']!=""){
        $estadoPedido = $_REQUEST['estadoPedido'];
        $consulta .= " AND comp_ped.Estado='".$_REQUEST['estadoPedido']."'";
    }
    
   $sqlComp = "SELECT 
                        DATE_FORMAT(recibo_factura.Fecha,'%d/%m/%Y') AS FechaB,
                        recibo_factura.Fecha, 
                        recibo_factura.TipoComprobante,
                        recibo_factura.Cancelado,
                        recibo_factura.NroComprobante,
                        recibo_factura.Importe,
                        recibo_factura.CondVenta,
                        recibo_factura.Saldo
                            
                    FROM 
                        recibo_factura           
                    WHERE 
                    recibo_factura.TipoComprobante<>'INIC'
                    AND recibo_factura.TipoComprobante<>'INID'
                    AND recibo_factura.Saldo<>0
                    AND recibo_factura.Codigo =".$_SESSION['idcliente']."
                    AND recibo_factura.Anulado ='No'
                    AND recibo_factura.Estado = 'N/Canc'
                    $consulta
                    ORDER BY recibo_factura.Fecha ASC";
    
    
    $hacer = mysqli_query($connV,$sqlComp) or die('No puedo consultar el pedido '.$sqlComp);
    /*
     * Listado WEB de los recibos.
     * =========================================================================
     */
//    if($soyMovil==0){
    
        if($hacer){
            $muestro = "";
            $muestro .='<thead>';
            $muestro .='            <tr>';
            $muestro .='                <th>Fecha</th>';
            $muestro .='                <th>N°Comprobante</th>';
            $muestro .='                <th class="dt-right">Resumen</th>';
//            $muestro .='                <th >Importe</th>';
//            $muestro .='                <th>Cond.Venta</th>';
//            $muestro .='                <th class="dt-right">Saldo</th>';
//            $muestro .='                <th class="dt-right">Saldo Acum</th>';                 
            $muestro .='            </tr>';        
            $muestro .='        </thead>';
            $muestro .='        <tbody>';
            $saldoAcum =0;
        
            while($comprobante = mysqli_fetch_object($hacer)){


                            $saldo = 0;
                            switch($comprobante->TipoComprobante){
                                case 'AJC':
                                    $saldo = $comprobante->Saldo * (-1);
                                    break;
                                case 'REC':
                                    $saldo = $comprobante->Saldo * (-1);
                                    break;
                                case 'NCA':
                                    $saldo = $comprobante->Saldo * (-1);
                                    break;
                                case 'NCB':
                                    $saldo = $comprobante->Saldo * (-1);
                                    break;
                                case 'NCC':
                                    $saldo = $comprobante->Saldo * (-1);
                                    break;
                                case 'NCM':
                                    $saldo = $comprobante->Saldo * (-1);
                                    break;
                                case 'NCE':
                                    $saldo = $comprobante->Saldo * (-1);
                                    break;
                               default:
                                        $saldo = $comprobante->Saldo;
                                    break;
                            }
                            $saldoAcum +=  $saldo;


                                $muestro .='<tr>';
                                $muestro .='    <td>'
                                        . $comprobante->FechaB.'</td>';
                                $muestro .='    <td>'. $comprobante->TipoComprobante .' '.$comprobante->NroComprobante.'<br>'. $comprobante->CondVenta.' </td>';
//                                $muestro .='    <td>'. $comprobante->NroComprobante.'</td>';
                                $muestro .='    <td class="importe">$'. $comprobante->Importe
                                                .' <br>$'. $comprobante->Cancelado. '<br>$'. $saldo.'</td>';
//                                $muestro .='    <td class="importe">$'. $comprobante->Importe.'<br>'
//                                        . '$'. $saldo.'</td>';
      
//                                $muestro .='    <td class="importe"></td>';
//                                $muestro .='    <td class="importe">$'. $saldoAcum.'</td>';                            
                                $muestro .='</tr>';

            }
            $muestro .='</tbody>';
            $muestro .='<tfooter>';
            $muestro .='            <tr>';
            $muestro .='                <td colspan="2" class="dt-right"><strong>Saldo al '.date('d/m/Y').'</strong></td>';
            $muestro .='                <td class="importe"><strong>$'.$saldoAcum.'</strong></td>';
            $muestro .='            </tr>';
            $muestro .='         </tfooter>   ';
            echo $muestro;

        }
//    }
    
}
