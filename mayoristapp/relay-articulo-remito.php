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
                // evaluamos si tenemos cliente o vendededor
                $condicion ="";    
                if(isset($_SESSION['vendedor'])){
                        $objVendedor = $_SESSION['vendedor'];
                        $condicion .= " AND comp_ped.idUsuario=".$objVendedor->id_usuario;
                }else{
                    if(isset($_SESSION['idcliente'])){
                        $condicion .=" AND comp_ped.Codigo=" .$_SESSION['idcliente'];
                    }
                
                }
                $query = "SELECT comp_ped.NroCompBusq 
                            FROM comp_ped 
                            WHERE 
                            comp_ped.TipoComprobante ='REM'
                            
                           $condicion
                            AND comp_ped.NroCompBusq LIKE '$queryString%'
                            ORDER BY comp_ped.NroCompBusq DESC  LIMIT 10";
                $hacer = mysqli_query($connV,$query) or die('No puedo ubicar el busqueda rapida.'.  mysqli_error($link).$query);
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
    $tipoRemito="Sistema";
    $usaIdManual = $_SESSION["usa_id_manual"];
    
    
    
//    busco por campo ademas del estado si estuviere.
//    @numeroComp: buscar por el numero de pedido
//    @fechaDesde : desde el periodo
//    @fechaHasta : hasta del periodo
    /*
     * tipo de remito para no duplicar pantallas
     */
    
    if($_REQUEST["tipoRemito"]!="1"){
        $tipoRemito=$_REQUEST["tipoRemito"];
        $consulta .=" AND comp_ped.TipoPedido='".$tipoRemito."'";
    }
    
    if($_REQUEST['campoBusca']!="-"){
        $campoBusca = $_REQUEST['campoBusca'];
        switch($campoBusca){
            case 'Fecha':
//                $fed = explode("/",$_REQUEST['fechaDesde']);
//                $fechaDesde = $fed[2].'/'. $fed[1].'/'.$fed[0];
//                $feh = explode("/",$_REQUEST['fechaHasta']);
//                $fechaHasta = $feh[2].'/'. $feh[1].'/'.$feh[0];
                $fechaDesde=$_REQUEST["fechaDesde"];
                $fechaHasta=$_REQUEST["fechaHasta"];

                if($fechaDesde!='' && $fechaHasta!=''){
                    $consulta .=" AND comp_ped.Fecha BETWEEN '".$fechaDesde . "' AND '". $fechaHasta."'";
                }
                
                
                break;
            case 'NroComprobante':
                $numeroComp = $_REQUEST['numeroComp'];
                if($numeroComp!=''){
                    $consulta .=" AND comp_ped.NroCompBusq LIKE '".$numeroComp."%'";
                }
               
                break;
        }
        
                
    }
//    busco el estado del pedido
    if($_REQUEST['estadoPedido']!="1"){
        $estadoPedido = $_REQUEST['estadoPedido'];
        $consulta .= " AND comp_ped.Estado='".$_REQUEST['estadoPedido']."'";
    }
    if (isset($_SESSION['idcliente'])) {
        $consulta .=" AND comp_ped.Codigo=" . $_SESSION['idcliente'];
    }else{
        if (isset($_SESSION['vendedor'])) {
            $objVendedor = $_SESSION['vendedor'];
            $consulta .= " AND comp_ped.idUsuario=" . $objVendedor->id_usuario;
        }
    
    }
    $caminoDispo="";
    $soyMovil=0;
//    echo "<pre>";
//    print_r($_SESSION);
//    echo "</pre>";
    if(isset($_SESSION["caminoDisp"])){
        $caminoDispo=$_SESSION["caminoDisp"];
        $soyMovil=1;
    }

    $sqlPedido = "SELECT 
                            stock.id_manual,
                            stock.IDArt,
                            stock.Descripcion,
                            CONCAT('(',stock.id_manual,') ',stock.Descripcion) AS artManual,
                            CONCAT('(',stock.IDArt,') ',stock.Descripcion) AS artId,
                            stock.Cantidad,
                            comp_ped.CodigoMovimiento,
                            comp_ped.id_comp_ped AS id,
                            DATE_FORMAT(comp_ped.Fecha,'%Y%m%d') AS Fecha,
                            DATE_FORMAT(comp_ped.Fecha,'%d/%m/%Y') AS FechaB,
                            comp_ped.NroComprobante,
                            comp_ped.CondVenta,
                            comp_ped.SubTotalGral,
                            cliente.nombre_cliente,
                            viajantes.Nombre,
                            DATE_FORMAT(comp_ped.FechaEntrega,'%d/%m/%Y') AS FechaEntrega,
                            comp_ped.FormaEntrega,
                            comp_ped.Estado,
                            comp_ped.TipoPedido,
                            comp_ped.Tipo,
                            comp_ped.autorizacion_sistema,
                            comp_ped.autorizacion_web,
                            comp_ped.Anulado,
                            (comp_ped.IVA1+
                            comp_ped.IVA2)AS IVA,
                            (comp_ped.SubTotalDesc+
                            comp_ped.IVA1+
                            comp_ped.IVA2) AS Total
                            
                    FROM 
                       stock
                        LEFT JOIN comp_ped  ON comp_ped.CodigoMovimiento = stock.CodigoMovimiento
                        LEFT JOIN cliente ON cliente.Codigo = comp_ped.Codigo
                        LEFT JOIN viajantes ON viajantes.CodViajante = comp_ped.CodViajante
                    WHERE
                    comp_ped.TipoComprobante ='REM'
                   
                    {$consulta}
                     
                    ORDER BY comp_ped.Fecha DESC";
    
    //echo $sqlPedido;
    $hacer = mysqli_query($connV,$sqlPedido) or die('No puedo consultar el pedido '.$sqlPedido);
    
    
//    if($soyMovil==0){
    /* SOY WEB **/
       
        if($hacer){
             
            $remitos = array();
            while($p=  mysqli_fetch_object($hacer)){
                $remitos[] = $p;
            }
            if(count($remitos)==0){
                $muestro = "";
                $muestro .='<thead>';
                $muestro .='            <tr>';                       
                $muestro .='                <th>&nbsp;</th>';
                $muestro .='            </tr>';
                $muestro .='</thead>';
                $muestro .='<tbody>';
                $muestro .='<tr>';
                $muestro .='<td>';
                $muestro .='No se encontaron resultados';
                $muestro .='</td>';
                $muestro .='</tr>';
                $muestro .='</tbody>';
            }else{
                $muestro = "";
                $muestro .='<thead>';
                $muestro .='            <tr>';
                $muestro .='                <th>Fecha</th>';
                $muestro .='                <th>Artículo</th>';
                $muestro .='                <th>Entregado</th>';
                $muestro .='                <th>N°Comp.</th>';

                $muestro .='                <th>Cliente</th>';
                $muestro .='                <th>Vendedor</th>';
                $muestro .='                <th>Tipo</th>';

                //$muestro .='                <th>Total</th>';

                $muestro .='                <th>Estado</th>';
              
                $muestro .='               <th>Anul.</th>';
                $muestro .='                <th>&nbsp;</th>';
                $muestro .='            </tr>';
                $muestro .='        </thead>';
                $muestro .='        <tbody>';
                foreach ($remitos as $remito) {
                    $muestro .= '        <tr>';
                    $muestro .= '                <td class="dt-nowrap" data-order="' . $remito->Fecha . '">
                                                ' . $remito->FechaB . '</td>';
                    if($usaIdManual=="Si"){
                        $muestro .= '                <td class="dt-nowrap" data-order="'.$remito->id_manual.'">' . $remito->artManual. '</td>';
                    }else{
                        $muestro .= '                <td class="dt-nowrap" data-order="'.$remito->IDArt.'">' . $remito->artId . '</td>';
                    }
                    $muestro .= '                <td class="dt-nowrap">' . $remito->Cantidad . '</td>';
                    $muestro .= '                <td class="dt-nowrap">' . $remito->NroComprobante . '</td>';

                    $muestro .= '                <td>' . $remito->nombre_cliente . '</td>';
                    $muestro .= '                <td>' . $remito->Nombre . '</td>';
                    $muestro .= '                <td>' . $remito->TipoPedido . '</td>';

                    switch ($remito->Estado) {
                        case 'Facturado':
                            $claseEstado = 'facturado';
                            break;

                        case 'En Remito':
                            $claseEstado = 'pendienteRemito';
                            break;
                        default:
                            $claseEstado = 'promocion';
                            break;
                    }

                    $muestro .= '                <td class="' . $claseEstado . '">' . $remito->Estado . '</td>';
                    $muestro .= '                <td>' . $remito->Anulado . '</td>';
                    $muestro .= '                <td>
                                                    <a  target="blank" href="ver_remito-movil.php?codigomovimiento=' . $remito->CodigoMovimiento . '&tipocomprobante=REM"  title="Visualizar comprobante" alt="Visualizar comprobante" mov="' . $remito->CodigoMovimiento . '" comprobante="REM">
                                                        <i class="fa fa-file-pdf barrita fa-lg fa-2x"></i>    
                                                    </a>
                                                    <a  href="relay-comprobante-a-mail.php?codMov=' . $remito->CodigoMovimiento . '&tipocomprobante=0"  title="mandar Email" alt="enviar email" mov="' . $remito->CodigoMovimiento . '" comprobante="REM">
                                                        <i class="fa fa-envelope barrita fa-lg fa-2x"></i>    
                                                    </a>
                                                </td>';
                    $muestro .= '        </tr>';
            }
            $muestro .='</tbody>';

            }
        }
        echo $muestro;
    }
    
    
    
        
//}
