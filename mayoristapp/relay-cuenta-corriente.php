<?php
header('Content-type: text/html; charset=utf-8');
require_once 'sesion.inc.php';
/*
 * busco el valor del auto suggest
 * 
 */
if(isset($_POST['queryString'])) {
        $queryString = mysql_real_escape_string($_POST['queryString']);

        // Is the string length greater than 0?

        if(strlen($queryString) >0) {
                // Run the query: We use LIKE '$queryString%'
                // The percentage sign is a wild-card, in my example of countries it works like this...
                // $queryString = 'Uni';
                // Returned data = 'United States, United Kindom';

                // YOU NEED TO ALTER THE QUERY TO MATCH YOUR DATABASE.
                // eg: SELECT yourColumnName FROM yourTable WHERE yourColumnName LIKE '$queryString%' LIMIT 10

                $query = "SELECT comp_ped.NroCompBusq 
                            FROM comp_ped 
                            WHERE 
                            comp_ped.TipoComprobante ='PED'
                            AND comp_ped.`Codigo`=".$_SESSION['idcliente']."
                            AND comp_ped.NroCompBusq LIKE '$queryString%'
                            ORDER BY comp_ped.NroCompBusq DESC  LIMIT 10";
                $hacer = mysql_query($query) or die('No puedo ubicar el busqueda rapida.');
                if($hacer) {
                        // While there are results loop through them - fetching an Object (i like PHP5 btw!).
                        while ($result = mysql_fetch_object($hacer)) {
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
} else {
        echo 'There should be no direct access to this script!';
}


if(isset($_REQUEST['ajax'])){
        
    $campoBusca = null;
    $estadoPedido = null;
    $consulta="";
    
    
    
    
//    busco por campo ademas del estado si estuviere.
//    @numeroComp: buscar por el numero de pedido
//    @fechaDesde : desde el periodo
//    @fechaHasta : hasta del periodo
    
    
    if($_REQUEST['campoBusca']!=""){
        $campoBusca = $_REQUEST['campoBusca'];
        switch($campoBusca){
            case 'Fecha':
                $fed = explode("/",$_REQUEST['fechaDesde']);
                $fechaDesde = $fed[2].'/'. $fed[1].'/'.$fed[0];
                $feh = explode("/",$_REQUEST['fechaHasta']);
                $fechaHasta = $feh[2].'/'. $feh[1].'/'.$feh[0];
                $consulta .=" AND Fecha BETWEEN '".$fechaDesde . "' AND '". $fechaHasta."'";
                break;
            case 'NroComprobante':
                $numeroComp = $_REQUEST['numeroComp'];
                $consulta .=" AND NroCompBusq LIKE '".$numeroComp."%'";
                break;
        }
        
                
    }
//    busco el estado del pedido
    if($_REQUEST['estadoPedido']!=""){
        $estadoPedido = $_REQUEST['estadoPedido'];
        $consulta .= " AND comp_ped.Estado='".$_REQUEST['estadoPedido']."'";
    }
    
   $sqlPedido = "SELECT 
                            comp_ped.CodigoMovimiento,
                            comp_ped.id_comp_ped AS id,
                            DATE_FORMAT(comp_ped.Fecha,'%d/%m/%Y') AS FechaB,
                            DATE_FORMAT(comp_ped.Fecha,'%Y%m%d') AS Fecha,
                            comp_ped.NroComprobante,
                            comp_ped.SubTotalDesc,
                            comp_ped.IVA1,
                            comp_ped.IVA2,
                            comp_ped.Exento,
                            comp_ped.CondVenta,
                            comp_ped.FechaEntrega,
                            comp_ped.FormaEntrega,
                            comp_ped.Estado,
                            comp_ped.TipoPedido,
                            comp_ped.autorizacion_sistema,
                            comp_ped.autorizacion_web,
                            comp_ped.Anulado,
                            (comp_ped.IVA1+
                            comp_ped.IVA2)AS IVA,
                            (comp_ped.SubTotalDesc+
                            comp_ped.IVA1+
                            comp_ped.IVA2) AS Total
                            
                    FROM 
                        comp_ped
           
                    WHERE 
                    
                    comp_ped.TipoComprobante ='PED'
                    AND comp_ped.`Codigo`=".$_SESSION['idcliente']."
                        $consulta
                     
                    ORDER BY comp_ped.Fecha DESC";
    
    
    $hacer = mysql_query($sqlPedido) or die('No puedo consultar el pedido '.$sqlPedido);
    if($hacer){
        $muestro = "";
        $muestro .='<thead>';
        $muestro .='            <tr>';
        $muestro .='                <th>Fecha</th>';
        $muestro .='                <th>Número</th>';
        $muestro .='                <th>Cond.Venta</th>';
        $muestro .='                <th>SubTotal</th>';
        $muestro .='                <th>Iva</th>';
        $muestro .='                <th>Total</th>';
        $muestro .='                <th>Tipo</th>';
        $muestro .='                <th>Estado</th>';
        $muestro .='                <th>Autorizado</th>';
        $muestro .='                <th>f.Entrega</th>';
        $muestro .='               <th>Forma Entrega</th>';
        $muestro .='               <th>Anul.</th>';
        $muestro .='                <th>&nbsp;</th>';
        $muestro .='            </tr>';
        $muestro .='        </thead>';
        $muestro .='        <tbody>';
        while($ped = mysql_fetch_object($hacer)){
            $muestro .='        <tr>';
            $muestro .='                <td data-order="'.$ped->Fecha.'">'.$ped->FechaB.'</td>';
            $muestro .='                <td>'.$ped->NroComprobante.'</td>';
            $muestro .='                <td>'.$ped->CondVenta.'</td>';
            $muestro .='                <td>'.$ped->SubTotalDesc.'</td>';
            $muestro .='                <td>'.$ped->IVA.'</td>';
            $muestro .='                <td>'.$ped->Total.'</td>';
            $muestro .='                <td>'.$ped->TipoPedido.'</td>';
            $muestro .='                <td>'.$ped->Estado.'</td>';
            $muestro .='                <td>'.$ped->autorizacion_sistema.'</td>';
            $muestro .='                <td>'.$ped->FechaEntrega.'</td>';
            $muestro .='                <td>'.$ped->FormaEntrega.'</td>';
            $muestro .='                <td>'.$ped->Anulado.'</td>';
            $muestro .="                <td><a href=# onclick=\"ver_pedido('".$ped->CodigoMovimiento."','".$ped->NroComprobante."')\">ver</a></td>";
            $muestro .='        </tr>';
        }
        $muestro .='</tbody>';
        
        echo $muestro;
        
    }
    
    
    
        
}
