<?php
header('Content-type: text/html; charset=utf-8');
require_once 'sesion.inc.php';
/*
 * busco el valor del auto suggest
 * 
 */
if(isset($_POST['queryString'])&&$_POST['queryString']!="") {
        $queryString = mysqli_real_escape_string($connV,$_POST['queryString']);

        // Is the string length greater than 0?

        if(strlen($queryString) >0) {
                // Run the query: We use LIKE '$queryString%'
                // The percentage sign is a wild-card, in my example of countries it works like this...
                // $queryString = 'Uni';
                // Returned data = 'United States, United Kindom';

                // YOU NEED TO ALTER THE QUERY TO MATCH YOUR DATABASE.
                // eg: SELECT yourColumnName FROM yourTable WHERE yourColumnName LIKE '$queryString%' LIMIT 10

                $query = "SELECT cuentacliente.NroCompBusq 
                            FROM cuentacliente 
                            WHERE 
                                
                             cuentacliente.`Codigo`=".$_SESSION['idcliente']."
                            AND cuentacliente.NroCompBusq LIKE '$queryString%'
                            ORDER BY cuentacliente.NroCompBusq DESC  LIMIT 10";
                $hacer = mysqli_query($connV,$query) or die('No puedo ubicar el busqueda rapida.');
                if($hacer) {
                        // While there are results loop through them - fetching an Object (i like PHP5 btw!).
                        while ($result = mysqli_fetch_object($hacer)) {
                                // Format the results, im using <li> for the list, you can change it.
                                // The onClick function fills the textbox with the result.

                                // YOU MUST CHANGE: $result->value to $result->your_colum
                        echo '<li>'.$result->NroCompBusq.'</li>';
                }
                } else {
                        echo 'ERROR: There was a problem with the query.';
                }
        } else {
                // Dont do anything.
        } // There is a queryString.
} 


if(isset($_REQUEST['ajax'])){
        
    $campoBusca = null;
    $estadoPedido = null;
    $consulta="";
    
    
    
    
//    busco por campo ademas del estado si estuviere.
//    @numeroComp: buscar por el numero de pedido
//    @fechaDesde : desde el periodo
//    @fechaHasta : hasta del periodo
    
    
    if(isset($_REQUEST['campoBusca'])&&$_REQUEST['campoBusca']!="-"){
        $campoBusca = $_REQUEST['campoBusca'];
        switch($campoBusca){
            case 'Fecha':
//                $fed = explode("/",$_REQUEST['fechaDesde']);
//                $fechaDesde = $fed[2].'/'. $fed[1].'/'.$fed[0];
//                $feh = explode("/",$_REQUEST['fechaHasta']);
//                $fechaHasta = $feh[2].'/'. $feh[1].'/'.$feh[0];
                $fechaDesde= $_REQUEST["fechaDesde"];
                $fechaHasta= $_REQUEST["fechaHasta"];
                if($fechaDesde!='' && $fechaHasta!=''){
                    $consulta .=" AND Fecha BETWEEN '".$fechaDesde . "' AND '". $fechaHasta."'";
                }
               
                break;
            case 'NroComprobante':
                $numeroComp = $_REQUEST['numeroComp'];
                if($numeroComp!=''){
                    $consulta .=" AND NroCompBusq LIKE '".$numeroComp."%'";
                }
               
                break;
        }
        
                
    }
//    busco el estado del pedido
//    if($_REQUEST['estadoPedido']!=""){
//        $estadoPedido = $_REQUEST['estadoPedido'];
//        $consulta .= " AND comp_ped.Estado='".$_REQUEST['estadoPedido']."'";
//    }
    
   $sqlCtaCte = "SELECT 
                           DATE_FORMAT(cuentacliente.Fecha,'%d/%m/%Y') AS FechaB,
                           DATE_FORMAT(cuentacliente.Fecha,'%Y%m%d') AS Fecha,
                            cuentacliente.TipoComprobante,
                            cuentacliente.NroComprobante,
                            cuentacliente.CondVenta,
                            cuentacliente.ImporteVenta AS Debito,
                            cuentacliente.ImporteCobro AS Credito,
                            cuentacliente.saldo,
                            DATE_FORMAT(cuentacliente.Vencimiento,'%d/%m/%Y') AS Vencimiento,
                            cuentacliente.Vencido,
                            cuentacliente.Estado,
                            cuentacliente.Anulado,
                            cuentacliente.Recibo,
                            cuentacliente.NroFactura,
                            cuentacliente.Detalle
                            
                    FROM 
                        cuentacliente
           
                    WHERE 
                        cuentacliente.`Codigo`=".$_SESSION['idcliente']."
                        $consulta
                    ORDER BY cuentacliente.Fecha DESC";
    
    
    $hacer = mysqli_query($connV,$sqlCtaCte) or die('No puedo consultar el pedido '.$sqlCtaCte);
    $cuantos = mysqli_num_rows($hacer);
    if($hacer){
        
        $muestro = "";
        $muestro .='<thead>';
        $muestro .='            <tr>';
        $muestro .='                <th>Fecha</th>';
        $muestro .='                <th>Comp</th>';
        $muestro .='                <th>N°Comprobante</th>';
        $muestro .='                <th>Cond.Venta</th>';
        $muestro .='                <th class="right">Debitos</th>';
        $muestro .='                <th class="right">Creditos</th>';
        $muestro .='                <th class="right">Saldo</th>';
        $muestro .='                <th>Vencimiento</th>';
        $muestro .='                <th>Venci</th>';
        $muestro .='                <th>Estado</th>';
        $muestro .='                <th>Anul.</th>';
        $muestro .='                <th>Recibo</th>';
        $muestro .='                <th>Factura Nd/NC</th>';
        $muestro .='                <th>Detalle</th>';
        $muestro .='            </tr>';
        $muestro .='        </thead>';
        if($cuantos>0){
            while($renglon = mysqli_fetch_object($hacer)){
                $muestro .='<tr>';
                $muestro .='                <td class="dt-nowrap" data-order="'.$renglon->Fecha.'">'.$renglon->FechaB.'</td>';
                $muestro .='                <td>'.$renglon->TipoComprobante.'</td>';
                $muestro .='                <td>'.$renglon->NroComprobante.'</td>';
                $muestro .='                <td>'.$renglon->CondVenta.'</td>';
                $muestro .='                <td class="importe">$'.($renglon->Debito + 0.00).'</td>';
                $muestro .='                <td class="importe">$'.($renglon->Credito + 0.00).'</td>';
                $muestro .='                <td class="importe">$'.($renglon->saldo + 0.00).'</td>';
                $muestro .='                <td>'.$renglon->Vencimiento.'</td>';
                $muestro .='                <td>'.$renglon->Vencido.'</td>';
                $muestro .='                <td>'.$renglon->Estado.'</td>';
                $muestro .='                <td>'.$renglon->Anulado.'</td>';
                $muestro .='                <td>'.$renglon->Recibo.'</td>';
                $muestro .='                <td>'.$renglon->NroFactura.'</td>';
                $muestro .='                <td>'.$renglon->Detalle.'</td>';
                $muestro .='            </tr>';
            }
        }else{
            $muestro .='<tr><td colspan="14">No se encontraron resultados</td></tr>';
        }    
        $muestro .='</tbody>';
        
        echo $muestro;
        
    }
    
    
    
        
}
