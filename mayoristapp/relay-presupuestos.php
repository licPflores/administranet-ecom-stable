<?php
//header('Content-type: text/html; charset=utf-8');
require_once 'sesion.inc.php';
/*
 * busco el valor del auto suggest
 * 
 */
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
                        $condicion .=" AND comp_ped.`CodViajante`=".$objVendedor->CodViajante;
                }else{
                
                    if(isset($_SESSION['idcliente'])){
                        $condicion .=" AND comp_ped.Codigo=" .$_SESSION['idcliente'];
                    }
                
                }
                $query = "SELECT comp_ped.NroCompBusq 
                            FROM comp_ped 
                            WHERE 
                            comp_ped.TipoComprobante ='PRE'
                            $condicion
                            AND comp_ped.NroCompBusq LIKE '$queryString%'
                            ORDER BY comp_ped.NroCompBusq DESC  LIMIT 10";
                //echo $query;
                $hacer = mysqli_query($connV,$query) or die('No puedo ubicar el busqueda rapida.'.mysqli_error($connV).$query);
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
    
    
    if($_REQUEST['campoBusca']!="-"){
        $campoBusca = $_REQUEST['campoBusca'];
        switch($campoBusca){
            case 'Fecha':
                if($_REQUEST['fechaDesde']!='' && $_REQUEST['fechaHasta']!=''){
                    //$fed = explode("-",$_REQUEST['fechaDesde']);
                    $fed = $_REQUEST['fechaDesde'];
                    //$fechaDesde = $fed[2].'/'. $fed[1].'/'.$fed[0];
                    //$feh = explode("-",$_REQUEST['fechaHasta']);
                    $feh =$_REQUEST['fechaHasta'];
                    //$fechaHasta = $feh[2].'/'. $feh[1].'/'.$feh[0];
                    $fechaDesde = $fed;
                    $fechaHasta = $feh;
                    
                    $consulta .=" AND Fecha BETWEEN '".$fechaDesde . "' AND '". $fechaHasta."'";
                }
                break;
            case 'NroComprobante':
                if($_REQUEST['numeroComp']!=''){
                    $numeroComp = $_REQUEST['numeroComp'];
                    $consulta .=" AND NroCompBusq LIKE '%".$numeroComp."%'";
                    
                }
                break;
            case 'TipoPedido':
                
                $tipoPedido = $_REQUEST['tipoPedido'];
                if($tipoPedido !== "1"){
                    $consulta =" AND comp_ped.TipoPedido='".$tipoPedido."'";
                }
               break;
        }
        
                
    }
//    busco el estado del pedido
    if($_REQUEST['estadoPedido']!="1"){
        $estadoPedido = $_REQUEST['estadoPedido'];
        $consulta .= " AND comp_ped.Estado='".$_REQUEST['estadoPedido']."'";
    }
//    controlo si viene de los vendedores o de los clientes
    $vendedor = $_REQUEST['vendedor'];
    $deQuien = "";
    if($vendedor=="true"){
        // vendedor
        //echo "VENDEDOR";
        //permiso para ver todos los clientes o no.
        if($_SESSION["todos_clientes"]=="No"){
            $deQuien = " AND comp_ped.CodViajante=".$objVendedor->CodViajante;
        }

        if(isset($_REQUEST["listaPed"])&& $_REQUEST["listaPed"]=="cliente"){
            if(isset($_SESSION['idcliente'])&& $_SESSION['idcliente']!=""){
                $deQuien .= " AND comp_ped.Codigo=".$_SESSION['idcliente'];
            }
        }
        $codViajante = $objVendedor->CodViajante;
        // filtro por vendedor si existe permiso.
        if(isset($_POST["filtraVendedor"])){
            $codViajante = $_POST["filtraVendedor"];
        }


        //permiso para ver todos los clientes o no.
        // if($_SESSION["todos_clientes"]=="No"&&$codViajante!="todos"){
        //     $deQuien = " AND comp_ped.CodViajante=".$codViajante;
        // }

        if($codViajante!="todos"){
            $deQuien = " AND comp_ped.CodViajante=".$codViajante;
        }
    }else{
        //cliente
        $deQuien .= " AND comp_ped.Codigo=".$_SESSION['idcliente'];
    }
    
            // si tiene el permiso de ver todos los clientes le da bola al vendedor.
    $usaIdManual='No';
    if(isset($_SESSION["usa_id_manual"])){
        $usaIdManual = $_SESSION["usa_id_manual"];  
    } 
    
   $sqlPedido = "SELECT     comp_ped.CodigoMovimiento,
                            comp_ped.id_comp_ped AS id,
                            DATE_FORMAT(comp_ped.Fecha,'%d/%m/%Y') AS FechaB,
                            comp_ped.Fecha,
                            comp_ped.NroComprobante,
                            comp_ped.SubTotalDesc,
                            comp_ped.IVA1,
                            comp_ped.IVA2,
                            comp_ped.Exento,
                            comp_ped.CondVenta,
                            DATE_FORMAT(comp_ped.FechaEntrega,'%d/%m/%Y') AS FechaEntrega,
                            comp_ped.FormaEntrega,
                            comp_ped.Estado,
                            cliente.nombre_cliente,
                            cliente.Codigo As codCliente,
                            cliente.id_manual_cli AS codManualCliente,
                            viajantes.Nombre As nombreViajante,
                            viajantes.CodViajante AS codViajante,
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
                    LEFT JOIN cliente ON cliente.Codigo = comp_ped.Codigo
                    LEFT JOIN viajantes ON viajantes.CodViajante= comp_ped.CodViajante
           
                    WHERE 
                    
                    comp_ped.TipoComprobante ='PRE'
                    {$deQuien}
                    {$consulta}
                     
                    ORDER BY comp_ped.Fecha DESC ";
    
    
    $hacer = mysqli_query($connV,$sqlPedido) or die('No puedo consultar el pedido '.$sqlPedido);
    
        $muestro = "";
        $muestro .='<thead>';
        $muestro .='            <tr>';
        $muestro .='                <th>#</th>';
        $muestro .='                <th>Fecha</th>';
        $muestro .='                <th>N° Comp.</th>';
        
        $muestro .='                <th>Cliente</th>';
        
        $muestro .='                <th>Cond.Vta</th>';
        $muestro .='                <th class="right">SubTotal</th>';
        $muestro .='                <th class="right">Iva</th>';
        $muestro .='                <th class="right">Total</th>';
        $muestro .='                <th>Tipo</th>';
        $muestro .='                <th>Estado</th>';
        $muestro .='                <th>Autorizado</th>';
    
        $muestro .='                <th>Entrega</th>';
        $muestro .='                <th>Viajante</th>';
        $muestro .='                <th>Anul.</th>';
        $muestro .='                <th>&nbsp</th>';
        $muestro .='            </tr>';
        $muestro .='        </thead>';
        $muestro .='        <tbody>';
        if($hacer){
            $pedidos = array();
            while($p=  mysqli_fetch_object($hacer)){
                $pedidos[] = $p;
            }
            if(count($pedidos)==0){
                
                $muestro = "0";
				// $muestro .='<thead>';
				// $muestro .='            <tr>';                
				// $muestro .='                <th></th>';				
				// $muestro .='            </tr>';
				// $muestro .='        </thead>';
				// $muestro .='        <tbody>';
                // $muestro .='<tr>';               
                // $muestro .='<td>';
                // $muestro .='No se encontaron resultados';
                // $muestro .='</td>';
                // $muestro .='</tr>';
                // $muestro .='</tbody>';
            }else{
                $totalNeto =0;
                $totalIva =0;
                $totalFinal =0; 
                foreach($pedidos as $ped){
                    $totalNeto +=$ped->SubTotalDesc;
                    $totalIva +=$ped->IVA;
                    $totalFinal +=$ped->Total;

                    $muestro .='<tr>';
                    $muestro .='    <td></td>';
                    $muestro .='    <td class="dt-nowrap" data-order="'.$ped->Fecha.'">'
                                .$ped->FechaB.'</td>';
                    $muestro .='    <td class="dt-nowrap">'.$ped->NroComprobante.'</td>';
                    
                    if($usaIdManual=="si"){
                        $muestro .='<td >'.$ped->codManualCliente .' - '.$ped->nombre_cliente.'</td>';
                    }else{
                        $muestro .='<td >'.$ped->codCliente.' - '. $ped->nombre_cliente.'</td>';
                    }
                    
                    $muestro .='                <td>'.$ped->CondVenta.'</td>';
                    $muestro .='                <td class="importe">'.$ped->SubTotalDesc.'</td>';
                    $muestro .='                <td class="importe">'.$ped->IVA.'</td>';
                    $muestro .='                <td class="importe">'.$ped->Total.'</td>';
                    $muestro .='                <td>'.$ped->TipoPedido.'</td>';
                    switch ($ped->Estado) {
                        case 'Facturado':
                            $claseEstado = 'facturado';
                            break;

                        case 'En Remito':
                            $claseEstado = 'pendienteRemito';
                            break;
                        case 'En Pedido':
                            $claseEstado = 'pendienteRemito';
                            break;
                        default:
                            $claseEstado = 'promocion';
                            break;
                    }

                     $muestro .='               <td class="'.$claseEstado.'">'.$ped->Estado.'</td>';
                     $muestro .='               <td>'.$ped->autorizacion_sistema.'</td>';
    //                 $muestro .='               <td style="width:80px; ">'.$ped->FechaEntrega.'</td>';
                     $muestro .='               <td>'.$ped->FormaEntrega.'</td>';
                     $muestro .='               <td >'.$ped->codViajante.' - '. $ped->nombreViajante.'</td>';
                     $muestro .='               <td><strong>'.$ped->Anulado.'</strong></td>';
                     $muestro .='               <td><span class="acciones">'
                                . '<a  target="blank" href="ver_presupuesto-movil.php?codigomovimiento='.$ped->CodigoMovimiento.'&tipocomprobante=PED"  title="Visualizar comprobante" alt="Visualizar comprobante" mov="'. $ped->CodigoMovimiento.'" comprobante="PRE">' 
                                . '<i class="fa fa-file-pdf barrita fa-lg fa-2x"></i>'
                                . '</a>';
                     $muestro .='<a  href="relay-comprobante-a-mail.php?codMov='.$ped->CodigoMovimiento.'&tipocomprobante=0"  title="mandar Email" alt="enviar email" mov="'.$ped->CodigoMovimiento.'" comprobante="PRE">
                                        <i class="fa fa-envelope barrita fa-lg fa-2x"></i>    
                                        </a>';
                    
                     $muestro .='               </span></td>';

                    $muestro .='            </tr>';

                }
                $muestro .='</tbody>';
                // armando el footer de los pedidos listados.
                
                $muestro .='<tfoot>';
                $muestro .='<tr>'; 
                $muestro .= '<th></th>';               
                $muestro .= '<th></th>';
                $muestro .= '<th></th>';
                $muestro .='<th>Total </th>';
                $muestro .= '<th></th>';     
                $muestro .='<th class="dt-right">'.$totalNeto.'</th>';
                $muestro .='<th class="dt-right">'.$totalIva.'</th>';
                $muestro .='<th class="dt-right">'.$totalFinal.'</th>';                
                $muestro .='<th></th>';
                $muestro .='<th></th>';
                $muestro .='<th></th>';
                $muestro .='<th></th>';
                $muestro .='<th></th>';               
                $muestro .='<th></th>';  
                $muestro .='<th></th>';                
                $muestro .='</tr>';
                $muestro .='</tfoot>';
                        
            }

            echo $muestro;
        }
        
}