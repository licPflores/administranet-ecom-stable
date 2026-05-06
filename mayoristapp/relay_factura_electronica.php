<?php
//header('Content-type: text/html; charset=utf-8');
require_once 'sesion.inc.php';
/*
 * busco el valor del auto suggest
 * 
 */

function html_movil($facturas,$vendedor){
     $usaIdManual = $_SESSION["usa_id_manual"];  
    $muestro = "";
        $muestro .='<thead>';
        $muestro .='            <tr>';
        $muestro .='                <th>Fecha</th>';
        $muestro .='                <th>Comprob.</th>';
        $muestro .='                <th class="right">Total</th>';
        $muestro .='                <th>&nbsp;</th>';
        if($vendedor=="true"){
            $muestro .='                <th>Cliente</th>';
        }
        $muestro .='                <th>Cond.Vta</th>';
        $muestro .='                <th class="right">SubTotal</th>';
        $muestro .='                <th class="right">Iva</th>';
        $muestro .='                <th class="right">Total</th>';        
        $muestro .='                <th>Tipo</th>';
        $muestro .='                <th>Estado</th>';       
        $muestro .='                <th>Anul.</th>';       
        $muestro .='            </tr>';
        $muestro .='        </thead>';
        if(!empty($facturas)){
            
        
            $muestro .='</tbody>';
                foreach($facturas as $fact){
                    $muestro .='<tr>';
                    $muestro .='    <td class="dt-nowrap">'.$fact->FechaB.'</td>';
                    $muestro .='    <td >'.$fact->TipoComprobante.' '.$fact->NroComprobante.'</td>';
                    $muestro .='    <td class="importe">$'.$fact->Total.'</td>';
                    $muestro .='    <td >'
                                . '<a  target="blank" href="ver_fact_electronica-movil.php?codigomovimiento='.$fact->CodigoMovimiento.'&tipocomprobante='.$fact->TipoFact.'"  title="Visualizar comprobante" alt="Visualizar comprobante" mov="'. $fact->CodigoMovimiento.'" comprobante="'.$fact->TipoFact.'">' 
                                . '<i class="fa fa-file-pdf barrita fa-lg fa-fw fa-2x"></i>'
                                . '</a>';
                     $muestro .= '<a  href="relay-comprobante-a-mail.php?codMov='.$fact->CodigoMovimiento.'&tipocomprobante=1"  title="mandar Email" alt="enviar email" mov="'.$fact->CodigoMovimiento.'" comprobante="FACT">
' 
                                . '<i class="fa fa-envelope barrita fa-lg fa-fw fa-2x"></i>'
                                . '</a>';
                     $muestro .=' </td>';
                    if($vendedor=="true"){
                        if($usaIdManual=="si"){
                            $muestro .='<td class="dt-nowrap">'.$fact->id_manual_cli .' - '.$fact->nombre_cliente.'</td>';
                        }else{
                            $muestro .='<td class="dt-nowrap">'.$fact->Codigo.' - '. $fact->nombre_cliente.'</td>';
                        }
                    }
                    $muestro .='                <td>'.$fact->CondVenta.'</td>';
                    $muestro .='                <td class="importe">$'.$fact->SubTotalDesc.'</td>';
                    $muestro .='                <td class="importe">$'.$fact->IVA.'</td>';
                    $muestro .='                <td class="importe">$'.$fact->Total.'</td>';
                    $muestro .='                <td>'.$fact->TipoFact.'</td>';
                    switch ($fact->Estado) {
                        case 'Canc':
                            $claseEstado = 'facturado';
                            break;

                        case 'N/Canc':
                            $claseEstado = 'pendienteRemito';
                            break;
                        default:
                            $claseEstado = 'promocion';
                            break;
                    }

                     $muestro .='               <td class="'.$claseEstado.'">'.$fact->Estado.'</td>';
                     
                     $muestro .='               <td><strong>'.$fact->Anulado.'</strong></td>';
                     

                    $muestro .='            </tr>';

                }
                $muestro .='</tbody>';    
            
            
        }
        
     return $muestro;   
}

function html_web($facturas,$vendedor){
     $usaIdManual = $_SESSION["usa_id_manual"];  
    $muestro = "";
        $muestro .='<thead>';
        $muestro .='            <tr>';
        $muestro .='                <th>Fecha</th>';
        $muestro .='                <th>Tipo</th>';
        $muestro .='                <th>N°Comprob.</th>';
        if($vendedor=="true"){
            $muestro .='                <th>Cliente</th>';
        }
        $muestro .='                <th>Cond.Vta</th>';
        $muestro .='                <th class="right">SubTotal</th>';
        $muestro .='                <th class="right">Iva</th>';
        $muestro .='                <th class="right">Total</th>';
        $muestro .='                <th>Tipo</th>';
        $muestro .='                <th>Estado</th>';
       
        $muestro .='                <th>Anul.</th>';
        $muestro .='                <th>&nbsp</th>';
        $muestro .='            </tr>';
        $muestro .='        </thead>';
        if(!empty($facturas)){            
        
            $muestro .='</tbody>';
                foreach($facturas as $fact){
                    $muestro .='<tr>';
                    $muestro .='    <td class="dt-nowrap">'.$fact->FechaB.'</td>';
                    $muestro .='    <td class="dt-nowrap">'.$fact->TipoComprobante.'</td>';
                    $muestro .='    <td class="dt-nowrap">'.$fact->NroComprobante.'</td>';
                    if($vendedor=="true"){
                        if($usaIdManual=="si"){
                            $muestro .='<td class="dt-nowrap">'.$fact->id_manual_cli .' - '.$fact->nombre_cliente.'</td>';
                        }else{
                            $muestro .='<td class="dt-nowrap">'.$fact->Codigo.' - '. $fact->nombre_cliente.'</td>';
                        }
                    }
                    $muestro .='                <td>'.$fact->CondVenta.'</td>';
                    $muestro .='                <td class="importe">$'.$fact->SubTotalDesc.'</td>';
                    $muestro .='                <td class="importe">$'.$fact->IVA.'</td>';
                    $muestro .='                <td class="importe">$'.$fact->Total.'</td>';
                    $muestro .='                <td>'.$fact->TipoFact.'</td>';
                    switch ($fact->Estado) {
                        case 'Canc':
                            $claseEstado = 'facturado';
                            break;

                        case 'N/Canc':
                            $claseEstado = 'pendienteRemito';
                            break;
                        default:
                            $claseEstado = 'promocion';
                            break;
                    }

                     $muestro .='               <td class="'.$claseEstado.'">'.$fact->Estado.'</td>';
                     
                     $muestro .='               <td><strong>'.$fact->Anulado.'</strong></td>';
                     $muestro .='               <td>'
                                . '<a  target="blank" href="ver_fact_electronica-movil.php?codigomovimiento='.$fact->CodigoMovimiento.'&tipocomprobante='.$fact->TipoFact.'"  title="Visualizar comprobante" alt="Visualizar comprobante" mov="'. $fact->CodigoMovimiento.'" comprobante="'.$fact->TipoFact.'">' 
                                . '<i class="fa fa-file-pdf barrita fa-lg fa-fw fa-2x"></i>'
                                . '</a>'; 
                     $muestro .= '<a  href="relay-comprobante-a-mail.php?codMov='.$fact->CodigoMovimiento.'&tipocomprobante=1"  title="mandar Email" alt="enviar email" mov="'.$fact->CodigoMovimiento.'" comprobante="FACT">
' 
                                . '<i class="fa fa-envelope barrita fa-lg fa-fw fa-2x"></i>'
                                . '</a>';
                     $muestro .='               </td>';

                    $muestro .='            </tr>';

                }
                $muestro .='</tbody>';    
            
            
        }
    return $muestro;
}

$usaIdManual = $_SESSION["usa_id_manual"];  
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
                $condicion ="";
                 
                if(isset($_SESSION['tipousuario'])&& $_SESSION['tipousuario']=='vendedor' ){
                        $objVendedor = $_SESSION['vendedor'];
                        //$condicion .= " AND comp_ped.idUsuario=".$objVendedor->id_usuario;
                        $condicion .=" AND cc.CodViajante=".$objVendedor->CodViajante;
                }else{
                
                    if(isset($_SESSION['idcliente'])){
                        $condicion .=" AND cc.Codigo=" .$_SESSION['idcliente'];
                    }
                
                }
                $query = "SELECT 
                            cc.NroCompBusq 
                        FROM comp_ped AS cc
                        WHERE 
                            cc.TipoComprobante IN('FA','FB','FC')
                            
                            $condicion
                        AND cc.NroCompBusq LIKE '$queryString%'
                        ORDER BY cc.NroCompBusq DESC  LIMIT 10";
                //echo $query;
                $hacer = mysqli_query($query) or die('No puedo ubicar el busqueda rapida.'.mysqli_error($connV).$query);
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
    $estadoFact = null;
    $consulta="";
    $soyMovil=0;
    
    if(isset($_SESSION["caminoDisp"])){
        // soy movil
        $caminoDispo=$_SESSION["caminoDisp"];
        $soyMovil=1;
    }
    
//    busco por campo ademas del estado si estuviere.
//    @numeroComp: buscar por el numero de pedido
//    @fechaDesde : desde el periodo
//    @fechaHasta : hasta del periodo
    
    
    if($_REQUEST['campoBusca']!="-"){
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

                if($fechaDesde != '' && $fechaHasta != ''){
                    $consulta .=" AND cc.Fecha BETWEEN '".$fechaDesde . "' AND '". $fechaHasta."'";
                }
  
                break;
            case 'NroComprobante':
                $numeroComp = $_REQUEST['numeroComp'];
                if($numeroComp != '' && $numeroComp>=0){
                    $consulta .=" AND cc.NroCompBusq LIKE '%".$numeroComp."%'";
                }
                
                break;
            case 'TipoComprobante':
                $tipoPedido = $_REQUEST['tipoFact'];
                if($tipoPedido !== "-"){
                    $consulta =" AND cc.TipoComprobante='".$tipoPedido."'";
                }
               break;
        }
        
                
    }

//    busco el estado del pedido
    if(isset($_REQUEST['estadoFact']) && $_REQUEST["estadoFact"]!=""){
        $estadoFact= $_REQUEST['estadoFact'];
        $consulta .= " AND cc.Estado='".$_REQUEST['estadoFact']."'";
    }
//    controlo si viene de los vendedores o de los clientes
    $vendedor = $_REQUEST['vendedor'];
    $deQuien = "";
    if($vendedor=="true"){
        // vendedor
        //echo "VENDEDOR";
        //permiso para ver todos los clientes o no.
        if(isset($_SESSION["todos_clientes"]) && $_SESSION["todos_clientes"]=="No"){
            $deQuien = " AND cc.CodViajante=".$objVendedor->CodViajante;
        }

        if(isset($_REQUEST["listaFact"])&& $_REQUEST["listaFact"]=="cliente"){
            if(isset($_SESSION['idcliente'])&& $_SESSION['idcliente']!=""){
                $deQuien .= " AND cc.Codigo=".$_SESSION['idcliente'];
            }
        }
    }else{
        //cliente
        $deQuien .= " AND cc.Codigo=".$_SESSION['idcliente'];
    }
    
            // si tiene el permiso de ver todos los clientes le da bola al vendedor.
 
   $sqlPedido = "SELECT     
                        cc.CodigoMovimiento,
                        cc.id_cuentacliente AS id,
                        DATE_FORMAT(cc.Fecha,'%d/%m/%Y') AS FechaB,
                        cc.Fecha,
                        cc.TipoComprobante,
                        cc.NroComprobante,
                        cc.SubTotalDesc,
                        cc.IVA1,
                        cc.IVA2,
                        IF(cc.fe_comp='si','ELECT','TALON') AS TipoFact,
                        cc.Exento,
                        cc.Estado,
                        cc.Detalle,
                        cc.CondVenta,                            
                        cliente.nombre_cliente,
                        cliente.Codigo,
                        cliente.id_manual_cli,
                        viajantes.Nombre,                            
                        cc.Anulado,
                        (cc.IVA1+
                        cc.IVA2)AS IVA,
                        (cc.SubTotalDesc+
                        cc.IVA1+
                        cc.IVA2) AS Total
                            
                    FROM 
                        cuentacliente AS cc
                    LEFT JOIN cliente ON cliente.Codigo = cc.Codigo
                    LEFT JOIN viajantes ON viajantes.CodViajante= cc.CodViajante
                    WHERE                     
                    cc.TipoComprobante IN('FA','FB','FC','FM')
                           
                    
                    {$deQuien}
                    {$consulta}
                     
                    ORDER BY cc.Fecha DESC LIMIT 60";
 

    $hacer = mysqli_query($connV,$sqlPedido) or die('No puedo consultar el factura '.$sqlPedido);
    
        
        if($hacer){
            $facturas = array();
            while($p=  mysqli_fetch_object($hacer)){
                $facturas[] = $p;
            }
            if($soyMovil==0){
                $muestro = html_web($facturas,$vendedor);
            }
            if($soyMovil==1){
                $muestro= html_movil($facturas,$vendedor);
            }
             
            echo $muestro;
        }

        
}
