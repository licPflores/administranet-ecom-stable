<?php
//header('Content-type: text/html; charset=utf-8');
require_once 'sesion.inc.php';
/*
 * busco el valor del auto suggest
 * 
 */
if(is_object($_SESSION['cliente'])){
    $clienteObj = $_SESSION['cliente'];
}else{
    $clienteObj = $_SESSION['cliente'][0];
}
if(isset($_POST['queryString'])) {
        $queryString = mysqli_real_escape_string($connV,$_POST['queryString']);

        // Is the string length greater than 0?

        if(strlen($queryString) >0) {
                // Run the query: We use LIKE '$queryString%'
                // The percentage sign is a wild-card, in my example of countries it works like this...
                // $queryString = 'Uni';
                // Returned data = 'United States, United Kindom';

                // YOU NEED TO ALTER THE QUERY TO MATCH YOUR DATABASE.
                // eg: SELECT yourColumnName FROM yourTable WHERE yourColumnName LIKE '$queryString%' LIMIT 10
                $condicion =" AND  rf.Codigo={$clienteObj->Codigo}";
                 
                
                
                $query= "SELECT rf.*
                        FROM recibo_factura AS rf                            
                        WHERE   
                            rf.Estado = 'N/Canc' 
                            AND
                            (rf.TipoComprobante = 'FA' Or             
                                rf.TipoComprobante = 'FB' Or 
                                rf.TipoComprobante = 'FM' Or 
                                rf.TipoComprobante = 'FC' Or 
                                rf.TipoComprobante = 'FE' Or 
                                rf.TipoComprobante = 'NDA' Or 
                                rf.TipoComprobante = 'NDM' Or 
                                rf.TipoComprobante = 'NDC' Or 
                                rf.TipoComprobante = 'NDE' Or 
                                rf.TipoComprobante = 'NDB' Or             
                                rf.TipoComprobante = 'AJD' Or 
                                rf.TipoComprobante = 'INID') 
                            AND rf.Saldo <> 0 AND  rf.Anulado = 'No' 
                            {$condicion}
                             AND rf.NroComprobante LIKE '%$queryString%'    
                            ORDER BY rf.CodigoMovimiento ASC";
                
//                $query = "SELECT comp_ped.NroCompBusq 
//                            FROM comp_ped 
//                            WHERE 
//                            comp_ped.TipoComprobante ='PED'
//                            $condicion
//                            AND comp_ped.NroCompBusq LIKE '$queryString%'
//                            ORDER BY comp_ped.NroCompBusq DESC  LIMIT 10";
                echo $query;
                $hacer = mysqli_query($connV,$query) or die('No puedo ubicar el busqueda rapida de facturas.'.mysqli_error($connV).$query);
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
    
//    echo "<pre>";
//    print_r($_REQUEST);
//    echo "<pre>";
    
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
                //$fed = explode("-",$_REQUEST['fechaDesde']);
                $fed = $_REQUEST['fechaDesde'];
                //$fechaDesde = $fed[2].'/'. $fed[1].'/'.$fed[0];
                //$feh = explode("-",$_REQUEST['fechaHasta']);
                $feh =$_REQUEST['fechaHasta'];
                //$fechaHasta = $feh[2].'/'. $feh[1].'/'.$feh[0];
                $fechaDesde = $fed;
                $fechaHasta = $feh;
                
                $consulta .=" AND rf.Fecha BETWEEN '".$fechaDesde . "' AND '". $fechaHasta."'";
                break;
            case 'NroComprobante':
                $numeroComp = $_REQUEST['numeroComp'];
                $consulta .=" AND rf.NroComprobante LIKE '%".$numeroComp."%'";
                break;
            case 'TipoPedido':
                $tipoPedido = $_REQUEST['tipoPedido'];
                if($tipoPedido !== ""){
                    $consulta =" AND rf.TipoComprobante='".$tipoPedido."'";
                }
               break;
        }
        
                
    }
//    busco el estado del pedido
//    if($_REQUEST['estadoPedido']!=""){
//        $estadoPedido = $_REQUEST['estadoPedido'];
//        $consulta .= " AND comp_ped.Estado='".$_REQUEST['estadoPedido']."'";
//    }
//    controlo si viene de los vendedores o de los clientes
    $vendedor = $_REQUEST['vendedor'];
    $deQuien = " AND rf.Codigo={$objCliente->Codigo} ";
//    if($vendedor=="true"){
//        // vendedor
//        echo "VENDEDOR";
//        //permiso para ver todos los clientes o no.
//        if($_SESSION["todos_clientes"]=="No"){
//            $deQuien = " AND comp_ped.CodViajante=".$objVendedor->CodViajante;
//        }
//
//        if(isset($_REQUEST["listaPed"])&& $_REQUEST["listaPed"]=="cliente"){
//            if(isset($_SESSION['idcliente'])&& $_SESSION['idcliente']!=""){
//                $deQuien .= " AND comp_ped.Codigo=".$_SESSION['idcliente'];
//            }
//        }
//    }else{
//        //cliente
//        $deQuien .= " AND comp_ped.Codigo=".$_SESSION['idcliente'];
//    }
    
            // si tiene el permiso de ver todos los clientes le da bola al vendedor.
    
    
   $sqlPedido = "SELECT     
                    rf.id_recibo_factura,
                    rf.TipoComprobante,
                    rf.Fecha,
                    DATE_FORMAT(rf.Fecha,'%d/%m/%Y') AS FechaB,
                    rf.NroComprobante,
                    rf.CodigoMovimiento,
                    rf.Importe,
                    rf.ImporteNC,
                    rf.Cancelado,
                    rf.Saldo,
                    rf.Neto
                    
                    FROM 
                      recibo_factura AS rf
                    WHERE 
                    rf.Estado = 'N/Canc' AND 
                    (rf.TipoComprobante = 'FA' Or             
                        rf.TipoComprobante = 'FB' Or 
                        rf.TipoComprobante = 'FM' Or 
                        rf.TipoComprobante = 'FC' Or 
                        rf.TipoComprobante = 'FE' Or 
                        rf.TipoComprobante = 'NDA' Or 
                        rf.TipoComprobante = 'NDM' Or 
                        rf.TipoComprobante = 'NDC' Or 
                        rf.TipoComprobante = 'NDE' Or 
                        rf.TipoComprobante = 'NDB' Or             
			rf.TipoComprobante = 'AJD' Or 
                         rf.TipoComprobante = 'INID') 
			AND rf.Saldo <> 0 AND  rf.Anulado = 'No'
                    
                    {$deQuien}
                    {$consulta}
                     
                    ORDER BY rf.Fecha DESC LIMIT 60";
    
    
    $hacer = mysqli_query($connV,$sqlPedido) or die('No puedo consultar el pedido '.$sqlPedido);
    $facturas = array();
    if ($hacer) {

        while ($f = mysqli_fetch_assoc($hacer)) {
            $facturas[] = $f;
        }
    }


    if($caminoDispo==""){
        
    }
    $tabla = armo_lista($facturas);
    echo $tabla;    
}
        
// funciones para dibujar y tener mas atomizado el codigo
// dibujo tabla web
function armo_lista($facturas){
    $muestro = "";
        $muestro .='<thead>';
        $muestro .='            <tr>';
        $muestro .='                <th>Fecha</th>';
        $muestro .='                <th>Tipo</th>    ';
        $muestro .='                <th>N°Comprob.</th>';        
        $muestro .='                <th class="right">Importe</th>';
        $muestro .='                <th class="right">Importe NC</th>';
        $muestro .='                <th class="right">Cancelado</th>';
        $muestro .='                <th class="right">Saldo</th>';
        $muestro .='                <th>Imputado</th>';
        $muestro .='                <th>&nbsp</th>';
        $muestro .='            </tr>';
        $muestro .='        </thead>';
        $muestro .='        <tbody>';
       
            if(empty($facturas)){
                $muestro .='<tr>';
                $muestro .='<td colspan="7">';
                $muestro .='No se encontaron resultados';
                $muestro .='</td>';
                $muestro .='</tr>';
                $muestro .='</tbody>';
            }else{

                foreach($facturas as $ff){
                    // listado para enviar.
                    
                
//                    $lista = implode("|", $ff);
                    $lista = json_encode($ff);
                
                    $muestro .='<tr>';
                    $muestro .='    <td class="dt-nowrap"><span style="display:none">'.$ff["Fecha"]
                            . '</span>'.$ff["FechaB"].'</td>';
                    $muestro .='    <td class="dt-nowrap">'.$ff["TipoComprobante"].'</td>';
                    $muestro .='    <td class="dt-nowrap">'.$ff["NroComprobante"].'</td>';
                    $muestro .='                <td class="importe">'.$ff["Importe"].'</td>';
                    $muestro .='                <td class="importe">$'.$ff["ImporteNC"].'</td>';
                    $muestro .='                <td class="importe">$'.$ff["Cancelado"].'</td>';
                    $muestro .='                <td class="importe">$<label id="mi-saldo-'.$ff["CodigoMovimiento"].'">'.$ff["Saldo"].'</label></td>';
                    $muestro .='                <td class="importe">';
                    $muestro .= "<input type='number' id='mi-cantidad-{$ff["CodigoMovimiento"]}' name='mi-cantidad[{$ff["CodigoMovimiento"]}]'  value='{$ff["Saldo"]}' max='{$ff["Saldo"]}' min='1.01' step='0.00'  style='width:100px;' />\n"; 
                    $muestro .="</td>";
                    $muestro .="<td>   ";
                    $muestro .='<input type="checkbox" class="input-chk-fact" value="'.$ff["CodigoMovimiento"].'" name="chk-factura[]" id="chk-factura-'.$ff["CodigoMovimiento"].'">';
                    $muestro .='<i class="fa fa-square fa-2x i-chk-factura" name="'.$ff["CodigoMovimiento"].'"></i>';
                    //$muestro .="<i class='fa fa-plus-circle fa-2x tecompro' name='{$ff["CodigoMovimiento"]}'  alt='agregar Comprobante' title='agregar Comprobante'></i>";
                    $muestro .="<input type='hidden' id='mi-fact-{$ff["CodigoMovimiento"]}' name='mi-fact[{$ff["CodigoMovimiento"]}]' value='{$lista}'/>";
                    $muestro .="</td>";
                    $muestro .='</tr>';

                }
                $muestro .='</tbody>';        
            }

            return $muestro;
}
// dibujo tabala web mobil pattern='[0-9]+([,\.][0-9]+)?'
function armo_lista_mobil(){
    
}