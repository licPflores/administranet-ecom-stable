<?php
header('Content-type: text/html; charset=utf-8');
require_once 'sesion.inc.php';
/*
 * busco el valor del auto suggest
 * 
 */
//if(isset($_POST['queryString'])) {
//        $queryString = mysql_real_escape_string($_POST['queryString']);
//
//        // Is the string length greater than 0?
//
//        if(strlen($queryString) >0) {
//                // Run the query: We use LIKE '$queryString%'
//                // The percentage sign is a wild-card, in my example of countries it works like this...
//                // $queryString = 'Uni';
//                // Returned data = 'United States, United Kindom';
//
//                // YOU NEED TO ALTER THE QUERY TO MATCH YOUR DATABASE.
//                // eg: SELECT yourColumnName FROM yourTable WHERE yourColumnName LIKE '$queryString%' LIMIT 10
//
//                $query = "SELECT comp_ped.NroCompBusq 
//                            FROM comp_ped 
//                            WHERE 
//                            comp_ped.TipoComprobante ='PED'
//                            AND comp_ped.`Codigo`=".$_SESSION['idcliente']."
//                            AND comp_ped.NroCompBusq LIKE '$queryString%'
//                            ORDER BY comp_ped.NroCompBusq DESC  LIMIT 10";
//                $hacer = mysqli_query($connV,$query) or die('No puedo ubicar el busqueda rapida.');
//                if($hacer) {
//                        // While there are results loop through them - fetching an Object (i like PHP5 btw!).
//                        while ($result = mysql_fetch_object($hacer)) {
//                                // Format the results, im using <li> for the list, you can change it.
//                                // The onClick function fills the textbox with the result.
//
//                                // YOU MUST CHANGE: $result->value to $result->your_colum
//                        echo '<li>'.$result->NroCompBusq.'</li>';
//                }
//                } else {
//                        echo 'ERROR: There was a problem with the query.';
//                }
//        } else {
//                // Dont do anything.
//        } // There is a queryString.
//} else {
//        echo 'There should be no direct access to this script!';
//}


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
    
    
    if(isset($_POST['campoBusca'])&& $_POST['campoBusca']!="-"){

        $fechaDesde = $_POST['fechaDesde'];
        $fechaHasta = $_POST['fechaHasta'];
        if($fechaDesde!='' && $fechaHasta!=''){
            $consulta .=" AND Fecha BETWEEN '".$fechaDesde . "' AND '". $fechaHasta."'";
        }

//         $campoBusca = $_POST['campoBusca'];
//         switch($campoBusca){
//             case 'Fecha':
// //                $fed = explode("/",$_REQUEST['fechaDesde']);
// //                $fechaDesde = $fed[2].'/'. $fed[1].'/'.$fed[0];
// //                $feh = explode("/",$_REQUEST['fechaHasta']);
// //                $fechaHasta = $feh[2].'/'. $feh[1].'/'.$feh[0];
//                 $fechaDesde = $_POST['fechaDesde'];
//                 $fechaHasta = $_POST['fechaHasta'];
//                 if($fechaDesde!='' && $fechaHasta!=''){
//                     $consulta .=" AND Fecha BETWEEN '".$fechaDesde . "' AND '". $fechaHasta."'";
//                 }
                
                
//                 break;
//             case 'NroComprobante':
//                 $numeroComp = $_POST['numeroComp'];
//                 if($numeroComp!=''){
//                     $consulta .=" AND NroCompBusq LIKE '".$numeroComp."%'";
//                 }
               
//                 break;
//         }
        
                
    }
//    busco el estado del pedido
    // if(isset($_POST['estadoPedido'])&&$_POST['estadoPedido']!=""){
    //     $estadoPedido = $_REQUEST['estadoPedido'];
    //     $consulta .= " AND comp_ped.Estado='".$_REQUEST['estadoPedido']."'";
    // }
    
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
                    ORDER BY recibo_factura.Fecha ASC;";
    

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
            $muestro .='                <th>Comp</th>';
            $muestro .='                <th>N°Comprobante</th>';
            $muestro .='                <th>Cancelado</th>';
            $muestro .='                <th class="dt-right">Importe</th>';
            $muestro .='                <th>Cond.Venta</th>';
            $muestro .='                <th class="dt-right">Saldo</th>';
            $muestro .='                <th class="dt-right">Saldo Acum</th>';                 
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
                                $muestro .='    <td>'. $comprobante->TipoComprobante .'</td>';
                                $muestro .='    <td>'. $comprobante->NroComprobante.'</td>';
                                $muestro .='    <td class="importe"><i class="fa fa-dollar"></i>'. $comprobante->Cancelado.'</td>';
                                $muestro .='    <td class="importe"><i class="fa fa-dollar"></i>'. $comprobante->Importe.'</td>';
                                $muestro .='    <td>'. $comprobante->CondVenta.'</td>';
                                $muestro .='    <td class="importe"><i class="fa fa-dollar"></i>'. $saldo.'</td>';
                                $muestro .='    <td class="importe"><i class="fa fa-dollar"></i>'. $saldoAcum.'</td>';                            
                                $muestro .='</tr>';

            }
            $muestro .='</tbody>';
            $muestro .='<tfooter>';
            $muestro .='            <tr>';
            $muestro .='                <td colspan="7" class="dt-right"><strong>Saldo al '.date('d/m/Y').'</strong></td>';
            $muestro .='                <td class="importe"><strong> <i class="fa fa-dollar"></i>'.$saldoAcum.'</strong></td>';
            $muestro .='            </tr>';
            $muestro .='         </tfooter>   ';
            echo $muestro;

        }
//    }
    
}
