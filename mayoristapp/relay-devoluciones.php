<?php
//header('Content-type: text/html; charset=utf-8');
require_once 'sesion.inc.php';
/*
 * busco el valor del auto suggest
 * 
 */
if(isset($_POST['queryString'])) {
        $queryString = mysqli_real_escape_string($_POST['queryString']);

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
                            comp_ped.TipoComprobante ='DEV'
                            $condicion
                            AND comp_ped.NroCompBusq LIKE '$queryString%'
                            ORDER BY comp_ped.NroCompBusq DESC  LIMIT 10";
                //echo $query;
                $hacer = mysqli_query($connV,$query) or die('No puedo ubicar el busqueda rapida.'.mysqli_error($connV).'<pre>'.$query.'</pre>');
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
/*
 * PROCESO AJAX
 * =============================================================================
 * =============================================================================
 */

if(isset($_REQUEST['ajax'])){
    
//    echo "<pre>";
//    print_r($_REQUEST);
//    echo "<pre>";
    
    $campoBusca = null;
    $estadoPedido = null;
    $consulta="";
    $tipoResumen=null;
    $queTabla=null;
    $deQuien="";
    $filtrarPor="";
    if(isset($_REQUEST["tipoResumen"])){

        $tipoResumen    = $_REQUEST["tipoResumen"];
    }
    $queInforme     = $_REQUEST["queAccion"];
   
    $supervisorVenta = $_SESSION["supervisor_venta"];
    $arrVendCargo = $_SESSION["vendedor_a_cargo"];
    if(isset($_REQUEST["tabla"])){
        $queTabla       = $_REQUEST["tabla"];
    }
    if(isset($_REQUEST["filtrarPor"]) && $_REQUEST['filtrarPor']!='1' && $_REQUEST['filtrarPor']!=''){
        $filtrarPor = $_REQUEST["filtrarPor"];
    }


    
//    busco por campo ademas del estado si estuviere.
//    @numeroComp: buscar por el numero de pedido
//    @fechaDesde : desde el periodo
//    @fechaHasta : hasta del periodo
    /*
     * SELECCION CLIENTE
     * =========================================================================
     */
     if($queInforme=="seleccion"){
         $resultado = listado_seleccion($queTabla,$arrVendCargo,$connV);
//             echo "resultado <pre>";
//    print_r($resultado);
//    echo "<pre>";
         echo $resultado;
         
     }
    
    /*
     * LISTAR DEVOLUCIONES X FILTRO Y CAMPO A BUSCAR
     * =========================================================================
     */
     
    if($queInforme=="listar"){
        // combinacion por campo busca 
        if($_REQUEST['campoBusca']!="1" ){
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

                    if($fechaDesde != '' && $fechaHasta !=''){
                        $consulta .=" AND comp_ped.Fecha BETWEEN '".$fechaDesde . "' AND '". $fechaHasta."'";
                    }
                   
                    break;
                case 'NroComprobante':
                    $numeroComp = $_REQUEST['numeroComp'];
                    if($numeroComp != '' ){
                        $consulta .=" AND NroCompBusq LIKE '%".$numeroComp."%'";
                    }
                    
                    break;
                // case 'TipoPedido':
                //     $tipoPedido = $_REQUEST['tipoPedido'];
                //     if($tipoPedido !== ""){
                //         $consulta =" AND comp_ped.TipoPedido='".$tipoPedido."'";
                //     }
                //    break;
                    
            }


        }
        
        /*
         * Filtro de cliente
         */
        if($filtrarPor!=""){
           
            $consulta .=lee_filtro($filtrarPor);;
        }
        
        
        /*
         * Estado de las devoluciones
         */
        if($_REQUEST['estadoPedido']!="1"){
            $estadoPedido = $_REQUEST['estadoPedido'];
            $consulta .= " AND comp_ped.Estado='".$estadoPedido."'";
        }
  

            // si tiene el permiso de ver todos los clientes le da bola al vendedor.
    
    
        $sqlPedido = "SELECT     comp_ped.CodigoMovimiento,
                                 comp_ped.id_comp_ped AS id,
                                 DATE_FORMAT(comp_ped.Fecha,'%d/%m/%Y') AS FechaB,
                                 comp_ped.Fecha,
								 punto_venta.nro_punto_venta,
                                 comp_ped.NroCompBusq,
                                 comp_ped.NroComprobante,
                                 comp_ped.SubTotalDesc,
                                 comp_ped.IVA1,
                                 comp_ped.IVA2,
                                 comp_ped.Exento,
                                 comp_ped.CondVenta,
                                 DATE_FORMAT(comp_ped.FechaEntrega,'%d/%m/%Y') AS FechaEntrega,
                                 comp_ped.FormaEntrega,
                                 comp_ped.Estado,
								 comp_ped.Detalle,
								 
                                 cliente.nombre_cliente,
                                 cliente.Codigo,
                                 cliente.id_manual_cli AS idManual,
                                 viajantes.Nombre AS viajante,
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
                         LEFT JOIN stockp ON stockp.CodigoMovimiento=comp_ped.CodigoMovimiento
                         LEFT JOIN articulo ON articulo.IDArt=stockp.IDArt
                         LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
                         LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                         LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt AND articulo_prov.CodProveedor = articulo.CodigoProveedor)
                         LEFT JOIN punto_venta ON punto_venta.id_punto_venta=comp_ped.id_pv
                         WHERE 

                         comp_ped.TipoComprobante ='DEV'
                 
                         {$consulta}
                         GROUP BY comp_ped.CodigoMovimiento    
                         ORDER BY comp_ped.Fecha DESC";
    
    
        $hacer = mysqli_query($connV,$sqlPedido) or die('No puedo consultar el devolucion:: '.mysqli_error($connV).'<br> <pre>'.$sqlPedido.'</pre>');
        
    //    echo "<pre>";
    //    print_r($sqlPedido);
    //    echo "</pre>";
        $arrDev = array();
        while ($d = mysqli_fetch_assoc($hacer) ) {
            $arrDev[] = $d;
        }
       
        $tabla ="";
        if ($caminoDispo == "") {
          
            $tabla = vista_html($arrDev);
        } else {
            $tabla = vista_html_mobil($arrDev);
        }
        echo $tabla;
    }


    /* 
     * PROCESAR DEVOLUCIONES 
     * =========================================================================
     * 
     */
  
   if($queInforme=="procesar"){
       // obtener el listado de los pedidos a devolver.. 
//          echo "<pre>";
//            print_r($_REQUEST);
//          echo "<pre>";
          $arrDevSimple = $_REQUEST["arrDev"];
          $arrDev=array();
          foreach($arrDevSimple as $a){
              $arrDev[]=$a["value"];
          }
//          echo "ArrayCopado:<pre>";
//          print_r(implode(",",$arrDev));
//          echo "</pre>";
        $procesar = procesar_devolucion($arrDev,$connV);
        if($procesar=="ok"){
        
            $textoCartel = '<div id="alertas-formulario" class="alerta-exito">'
                            . 'Se han Computado las devoluciones';            
            $textoCartel .='</div >';
        }else{
            $textoCartel = '<div id="alertas-formulario" class="alerta-error">'
                            . 'Ocurrio un inconveniente '.$procesar;            
            $textoCartel .='</div >';
        }    
        echo $textoCartel;   
   } 

}
/*
 * FUNCIONES
 * =============================================================================
 * =============================================================================
 */



/*
 * Marcar Devoluciones Como Procesadas
 * =============================================================================
 */
function procesar_devolucion($arrDevoluciones,$connV){
    $where = " AND dev.CodigoMovimiento IN (".implode(",",$arrDevoluciones).")";
    $sql="UPDATE comp_ped AS dev "
            . "SET dev.Estado='Computada' "
            . "WHERE dev.TipoComprobante ='DEV' "
            . "{$where}";
            
    $hacer = mysqli_query($connV,$sql) or die("no puedo ejecutar la consulta " . mysqli_error($connV) .'<pre>'.$sql.'</pre>');
    if($hacer){
        // todo bien
        return "ok";
    }else{
        return "error: ". mysqli_error($connV).'<pre>'.$sql.'</pre>';
    }
            
}



/*
 * function: listado_seleccion
 * =============================================================================
 * desc:    busca el total de la tabla pasado como parametro y devuelve un
 * listado de options para llenar una lista
 * @tabla: valor para saber de que tabla debo buscar los options.
 * @salida: es un texto con options.
 */
function listado_seleccion($tabla=null,$arrVendCargo=null,$connV){
    // filtrar los clientes por los vendedores  salvo que tenga
    $sql = "";
    $lista ="";
    $usaIdManual = $_SESSION["usa_id_manual"];
    switch($tabla){
        case "cliente":
            if($arrVendCargo!=null&&!empty($arrVendCargo)){
                $listaVendedor=" AND viajantes.CodViajante IN (". implode(',',$arrVendCargo).")";
            }
            if($usaIdManual=="Si"){
                     $sql="SELECT cliente.id_manual_cli AS valor,"
                    . " CONCAT(cliente.nombre_cliente,' (cod:',cliente.id_manual_cli,')') AS texto "
                    . " FROM cliente"
                    . " WHERE cliente.Estado='Activo'"
                    . " ORDER BY texto ASC";
            } else{
                $sql="SELECT cliente.Codigo AS valor,"
                    . " CONCAT(cliente.nombre_cliente,' (cod:',cliente.Codigo,')') AS texto "
                    . " FROM cliente"
                    . " WHERE cliente.Estado='Activo'"
                    . " ORDER BY texto ASC";
            }
            break;
        case "tipocliente":
            $sql="SELECT tipo_cliente.IDTipoCliente AS valor,"
                . " CONCAT(tipo_cliente.NombreTipoCliente,' (cod:',tipo_cliente.IDTipoCliente,')') AS texto "
                . " FROM tipo_cliente"
                . " WHERE tipo_cliente.Anulado='No'"
                . " ORDER BY texto ASC";
            break;
        case "articulo":
            if($usaIdManual=="Si"){
                $sql="SELECT articulo.id_manual AS valor,"
                    . " CONCAT(articulo.NombreArticulo,' (cod:',articulo.id_manual,')') AS texto "
                    . " FROM articulo"
                    . " WHERE articulo.Discontinuo='No'"
                    . " ORDER BY texto ASC";
            }else{
                $sql="SELECT articulo.IDArt AS valor,"
                    . " CONCAT(articulo.NombreArticulo,' (cod:',articulo.IDArt,')') AS texto "
                    . " FROM articulo"
                    . " WHERE articulo.Discontinuo='No'"
                    . " ORDER BY texto ASC";
            }
            break;
        case "vendedor":
            $listaVendedor="";
            if($arrVendCargo!=null&&!empty($arrVendCargo)){
                $listaVendedor=" AND viajantes.CodViajante IN (". implode(',',$arrVendCargo).")";
            }
            
            $sql="SELECT viajantes.CodViajante AS valor,"
                . " CONCAT(viajantes.Nombre,' (cod:',viajantes.CodViajante,')') AS texto "
                . " FROM viajantes"
                . " WHERE viajantes.Anulado='No'"
                . $listaVendedor    
                . " ORDER BY texto ASC";
            break;
        case "proveedor":
            $sql="SELECT proveedor.Codigo AS valor,"
                . " CONCAT(proveedor.Nombre,' (cod:',proveedor.Codigo,')') AS texto "
                . " FROM proveedor"
                . " WHERE proveedor.Estado='Activo' AND proveedor.Tipo='Mercaderias'"
                . " ORDER BY texto ASC";    
            break;
        
        case "zona":
            $sql="SELECT erp_zona.id_zona AS valor,"
                . " CONCAT(erp_zona.nombre_zona,' (cod:',erp_zona.id_zona,')') AS texto "
                . " FROM erp_zona"
                . " WHERE erp_zona.anulado='No'"
                . " ORDER BY texto ASC";
            break;
        case "rubro":
            $sql="SELECT rubro.CodigoRubro AS valor,"
                . " CONCAT(rubro.NombreRubro,' (cod:',rubro.CodigoRubro,')') AS texto "
                . " FROM rubro"
                . " WHERE rubro.anulado='No'"
                . " ORDER BY texto ASC";
            break;
        case "subrubro":
            $sql="SELECT subrubro.IDSubRubro AS valor,"
                . " CONCAT(subrubro.NombreSubRubro,' (ru: ',rubro.NombreRubro,' - cod: ', subrubro.IDSubRubro ,')') AS texto "
                . " FROM subrubro "
                . " LEFT JOIN rubro ON rubro.CodigoRubro = subrubro.CodigoRubro "
                . " WHERE subrubro.anulado='No'"
                . " ORDER BY texto ASC";
            break;
                         
    }
    $hacer = mysqli_query($connV,$sql) or die("no puedo ejecutar el listado " . mysqli_error($connV) .'<pre>'.$sql.'</pre>');
    $arrLista[] = array("label"=>" - Todos -","value"=>"todos|todos");
    while($listado = mysqli_fetch_assoc($hacer)){
        $lista .= '<option value="'.$listado["valor"].'|'.$listado["texto"].'"> '.$listado["texto"].' </option>' . "\n";
        $arrLista[] = array(                        
                            "label"=>$listado["texto"],
                            "value"=>$listado["valor"].'|'.$listado["texto"],
            );
    }
//    return $lista;
    return json_encode($arrLista);
}

/*
 * Funcion para interpretar el filtro de clientes
 * =============================================================================
 */function lee_filtro($filtrarPor){
    $usaIdManual = $_SESSION["usa_id_manual"];
    $listaFiltro = explode("||", $filtrarPor);
    $arrFiltros = array();
    $deQuien ="";
    
    foreach ($listaFiltro as $valorFiltro){
        $datoFiltro = explode("|", $valorFiltro);
        if(count($datoFiltro) > 1){
            $tabla = $datoFiltro[0];
            $valor = $datoFiltro[1];
            switch ($tabla) {
                case 'cliente':
                    $campo = ($usaIdManual == "Si") ? "cliente.id_manual_cli" : "comp_ped.Codigo";
                    break;
                case 'vendedor':
                    $campo = "comp_ped.CodViajante";
                    break;
                case 'articulo':
                    $campo = ($usaIdManual == "Si") ? "articulo.id_manual" : "articulo.IDArt";
                    break;
                case 'rubro':
                    $campo = "rubro.CodigoRubro";
                    break;
                case 'subrubro':
                    $campo = "subrubro.IDSubRubro";
                    break;
                case 'proveedor':
                    $campo = "articulo_prov.CodProveedor";
                    break;
                default:
                    $campo = "";
            }
            if (!empty($campo)) {
                $arrFiltros[$campo][] = $valor;
            }
        }
    }
    
    foreach ($arrFiltros as $campo => $valores) {
        if (count($valores) > 1) {
            $deQuien .= " AND $campo IN (".implode(",", $valores).")";
        } else {
            $deQuien .= " AND $campo = ".$valores[0];
        }
    }
    
    return $deQuien;
}


/*
 * Preparo Tablas HTML
 * ============================================================================
 */function vista_html($arrDev){
    $muestro = "";
    $usaIdManual = $_SESSION["usa_id_manual"];
    
    if($usaIdManual=="Si"){
        $codCliente ="idManual";
       
    }else{
        $codCliente ="Codigo";
       
    }
    
    // cabecera de la tabla
    //=====================
    $muestro .= '<thead>';
    $muestro .= '<tr>';
    $muestro .= '<th>&nbsp;</th>';
    $muestro .= '<th>Fecha</th>';
	$muestro .= '<th>Pvta</th>';
    $muestro .= '<th>Numero</th>';          
    $muestro .= '<th>Cliente</th>';
    $muestro .= '<th>Viajante</th>';
	$muestro .= '<th>Detalle</th>';
    $muestro .= '<th class="right">SubTotal</th>';
    $muestro .= '<th class="right">Iva</th>';
    $muestro .= '<th class="right">Total</th>';
    $muestro .= '<th>Estado</th>';
    $muestro .= '<th>Ver</th>';
    $muestro .= '<th>Anul.</th>';
    $muestro .= '</tr>';
    $muestro .= '</thead>';
    $muestro .= '<tbody>';
        
     // datos de la tabla
    //=====================
    if (count($arrDev) == 0) {
//        $muestro .= '<tr>';
//        $muestro .= '<td>&nbsp;</td>';
//        $muestro .= '<td>&nbsp;</td>';
//        $muestro .= '<td>&nbsp;</td>';          
//        $muestro .= '<td>No se encontraron resultados</td>';
//        $muestro .= '<td>&nbsp;</td>';
//        $muestro .= '<td class="right">&nbsp;</td>';
//        $muestro .= '<td class="right">&nbsp;</td>';
//        $muestro .= '<td class="right">&nbsp;</td>';
//        $muestro .= '<td>&nbsp;</td>';
//        $muestro .= '<td>&nbsp;</td>';
//        $muestro .= '<td>&nbsp;.</td>';
//        $muestro .= '</tr>';
        $muestro .= '</tbody>';
    } else {

        foreach ($arrDev as $dv) {
            $muestro .= '<tr>';
            $muestro .='<td>';
            //$muestro .='<input type="checkbox" name="dev[]" id="dev-'.$dv["CodigoMovimiento"].'" value="'.$dv["CodigoMovimiento"].'">';
            
//            $muestro .='<input type="checkbox" name="dev[]" id="dev-'.$dv["CodigoMovimiento"].'" value="'.$dv["CodigoMovimiento"].'"> ';
            if($dv["Estado"]!=="Pendiente"){
            $muestro .='<input type="checkbox" style="display:none;" class="input-chk-dev" value="'.$dv["CodigoMovimiento"].'" name="chk-dev" id="chk-dev-'.$dv["CodigoMovimiento"].'" >';
            $muestro .='<i class="fas fa-square fa-2x i-chk-dev" name="'.$dv["CodigoMovimiento"].'"></i>';
            $muestro .='<input type="hidden" value="'.$dv["SubTotalDesc"].'|'.$dv["IVA"] .'|'.$dv["Total"].'" id="mi-dev-'.$dv["CodigoMovimiento"].'"></i>';
            }else{
                $muestro .="&nbsp;";
            }
            $muestro .='</td>';
            $muestro .='</td>';
            $muestro .= '<td class="dt-nowrap"><span style="display:none">' . $dv["Fecha"]
                     . '</span>' . $dv["FechaB"] . '</td>';
					 
            //$muestro .= '<td class="dt-nowrap">' . $dv["NroComprobante"] . '</td>';                       
			$muestro .= '<td class="dt-nowrap">' . $dv["nro_punto_venta"] . '</td>';                      
			$muestro .= '<td class="dt-nowrap">' . $dv["NroCompBusq"] . '</td>';                       			
            $muestro .= '<td class="dt-nowrap">( '.$dv[$codCliente].' ) ' . $dv["nombre_cliente"] . '</td>';
            $muestro .= '<td class="dt-nowrap">' . $dv["viajante"] . '</td>';
			$muestro .= '<td class="dt-nowrap">' . $dv["Detalle"] . '</td>';
            $muestro .= '<td class="importe">$' . $dv["SubTotalDesc"] . '</td>';
            $muestro .= '<td class="importe">$' . $dv["IVA"] . '</td>';
            $muestro .= '<td class="importe">$' . $dv["Total"] . '</td>';
            switch ($dv["Estado"]) {
                case 'Facturado':
                    $claseEstado = 'facturado';
                    break;

                case 'Computada':
                    $claseEstado = 'pendienteRemito';
                    break;
                default:
                    $claseEstado = 'promocion';
                    break;
            }

            $muestro .= '<td class="' . $claseEstado . '">' . $dv["Estado"] . '</td>';
            $muestro .='<td>';
            $muestro .='<a href="#" class="verComprobante"  title="Visualizar comprobante" alt="Visualizar comprobante" mov="'.$dv["CodigoMovimiento"].'" comprobante="DEV">';
            $muestro .='<i class="fas fa-file-pdf barrita fa-lg"></i>';
            $muestro .='</a>';
            $muestro .='</td>';
            $muestro .= '<td><strong>' . $dv["Anulado"] . '</strong></td>';
       
            $muestro .= '</tr>';
        }
        $muestro .= '</tbody>';
    }

              return $muestro;
            
}


/*
 * Preparo Tablas HTML Mobil
 * =============================================================================
 */

function vista_html_mobil($arrDev){
    $muestro = "";
     $usaIdManual = $_SESSION["usa_id_manual"];
    
    if($usaIdManual=="Si"){
        $codCliente ="idManual";
        
    }else{
        $codCliente ="Codigo";
        
    }
    
    $muestro .= '<thead>';
    $muestro .= '<tr>';
    $muestro .= '<th>&nbsp</th>';
    $muestro .= '<th>Fecha</th>';
    $muestro .= '<th>N°Comp.</th>';           
    $muestro .= '<th>Cliente</th>';
    $muestro .= '<th>Viajante</th>';
    $muestro .= '<th class="right">Total</th>';
    $muestro .= '<th>Estado</th>';
	//$muestro .= '<th>Detalle</th>';
    $muestro .= '<th>Ver</th>';
    $muestro .= '<th>Anul.</th>';

    $muestro .= '</tr>';
    $muestro .= '</thead>';
    $muestro .= '<tbody>';
           
               
    if (count($arrDev) == 0) {
        
        $muestro = "";
				$muestro .='<thead>';
				$muestro .='            <tr>';
				$muestro .='                <th></th>';				
				$muestro .='            </tr>';
				$muestro .='        </thead>';
				$muestro .='        <tbody>';
                $muestro .='<tr>';
                $muestro .='<td>';
                $muestro .='No se encontaron resultados';
                $muestro .='</td>';
                $muestro .='</tr>';
                $muestro .='</tbody>';
      
    } else {

        foreach ($arrDev as $dv) {
            $muestro .= '<tr>';
            $muestro .='<td>'
                    .'<input type="checkbox" name="dev[]" id="dev-'.$dv["CodigoMovimiento"].'" value="'.$dv["CodigoMovimiento"].'"> ';
            // $muestro .='<input type="checkbox" class="input-chk-fact" value="'.$dv["CodigoMovimiento"].'" name="chk-factura[]" id="chk-factura-'.$dv["CodigoMovimiento"].'">';
            // $muestro .='<i class="fa fa-square fa-2x i-chk-factura" name="'.$dv["CodigoMovimiento"].'"></i>';
            $muestro .='</td>';
            $muestro .= '<td style="dt-nowrap"><span style="display:none">' . $dv["Fecha"] . '  </span>' . $dv["FechaB"] . '</td>';
            $muestro .= '<td style="dt-nowrap">' . $dv["NroComprobante"] . '</td>';
            $muestro .= '<td class="dt-nowrap">( '.$dv[$codCliente].' ) ' . $dv["nombre_cliente"] . '</td>';
            $muestro .= '<td class="dt-nowrap">' . $dv["viajante"] . '</td>';
            $muestro .= '<td class="importe">$' . $dv["Total"] . '</td>';

            switch ($dv["Estado"]) {
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

            $muestro .= '<td class="' . $claseEstado . '">' . $dv["Estado"] . '</td>';
             
            $muestro .='<td>';
            $muestro .='<a  target="blank" href="#" class="verComprobante"  title="Visualizar comprobante" alt="Visualizar comprobante" mov="'.$dv["CodigoMovimiento"].'" comprobante="DEV">';
            $muestro .='<i class="fa fa-file-pdf-o barrita fa-lg"></i>';
            $muestro .='</a>';
            $muestro .='</td>';
            $muestro .= '<td><strong>' . $dv["Anulado"] . '</strong></td>';
            $muestro .= '            </tr>';
        }
        $muestro .= '</tbody>';
    }

    return $muestro;
            
}