<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//error_reporting('E_ALL');
require_once 'sesion.inc.php';

//    echo "<pre>";
//    print_r($_SESSION);
//    echo "</pre>";
// * busquedad de clientes
function buscarCliente($arrOpciones){
    $todosClientes      = $_SESSION['todos_clientes'];
    $usaIdManual        = $_SESSION["usa_id_manual"];
    $soySupervisorVenta = $_SESSION["supervisor_venta"];
    $arrVendedorCargo   = $_SESSION["vendedor_a_cargo"];
    $caminoDispo        = $_SESSION["caminoDisp"];
    $permisoAltaCliente = $_SESSION["permiso_alta_cliente"];
    $objVendedor        = $_SESSION['vendedor'];


    $patronCliente      = $arrOpciones['patronCliente'];
    $tipoBusqueda       = $arrOpciones["modoBus"]; // codigo o texto
    $codCliente         = $arrOpciones["codCliente"]; // idCliente / puede ser el cod manual    
    $connV              = $arrOpciones['connV']; // conexion.

    $where = "";


    if (strlen($codCliente)==0 && strlen($patronCliente)==0) {
        $textoCliente = "";
        $textoCliente .= "<thead>";
        $textoCliente .= "  <tr>";
        // $textoCliente .= "      <th>Id</th>";
        $textoCliente .= "      <th>Cliente</th>";
        //                        $textoCliente .= "      <th>Tipo</th>";
        //            $textoCliente .= "      <th>Telefono</th>";
        // $textoCliente .= "      <th>IVA</th>";
        $textoCliente .= "      <th>&nbsp;</th>";
        $textoCliente .= "   </tr>";
        $textoCliente .= "</thead>";
        $textoCliente .= "<tbody>";
        // Dont do anything.
        $textoCliente .= "<tr><td>Debe ingresar una busqueda</td><td></td></tr>";
        $textoCliente .= "</tbody>";
        echo $textoCliente;
        exit();
    } // There is a queryString.

    if (isset($_SESSION['permiso_uso_vendedor_cliente']) && $_SESSION['permiso_uso_vendedor_cliente'] == 'Si') {
        // dentro de arrayOpciones['listaClientesVendedor'] viene un array con los codigos de clientes que puede ver el vendedor, si esta vacio no se hace nada, si tiene datos entonces se agrega al where de la busqueda.
        if (isset($arrOpciones['listaClientesVendedor']) && !empty($arrOpciones['listaClientesVendedor'])) {
            $listaClientesVendedor = $arrOpciones['listaClientesVendedor'];
            $where .= " AND cliente.Codigo IN(" . implode(',', $listaClientesVendedor) . ') ' . PHP_EOL;
        }
    }
    
    if ($tipoBusqueda == "codigo") {
        $where .= " AND cliente.Codigo = '{$codCliente}'" . PHP_EOL;
    }

    if ($tipoBusqueda == "texto") {
        // buscar por multiples palabras
        preg_match_all('/\w+/', $patronCliente, $matches);    // match words
        $matchesUnique = array_unique($matches[0]); // get new array w/o duplicates
        //print_r($matchesUnique);
        // mas de un elemento a buscar
        if (sizeof($matchesUnique) > 1) {
            $listaPalabras = join('%', $matchesUnique);
        }
        // 1 solo elemento a buscar
        if (sizeof($matchesUnique) == 1) {
            $listaPalabras = $matchesUnique[0];
        }

        if ($usaIdManual === "Si") {

            $where .= " AND (cliente.nombre_cliente LIKE '%{$listaPalabras}%' " . PHP_EOL;
            $where .= " OR  cliente.id_manual_cli LIKE '%{$listaPalabras}%' )" . PHP_EOL;
        }

        if ($usaIdManual === "No") {
            $where .= " AND (cliente.nombre_cliente LIKE '%{$listaPalabras}%' " . PHP_EOL;
            $where .= " OR cliente.Codigo LIKE '%{$listaPalabras}%' )" . PHP_EOL;
        }
    }

    if ($todosClientes == 'No') {

        if ($soySupervisorVenta == 'No') {
            $where .= " AND cliente.CodViajante =" . $objVendedor->CodViajante . PHP_EOL;
        }

        if ($soySupervisorVenta == 'Si') {
            // verifico si tengo vendedores a cargo
            if (!empty($arrVendedorCargo)) {
                $where .= " AND cliente.CodViajante IN(" . $objVendedor->CodViajante . ',' . implode(',', $arrVendedorCargo) . ') ' . PHP_EOL;
            }
            // supervisor pero no mer cargaron nada
            if (empty($arrVendedorCargo)) {
                $where .= " AND cliente.CodViajante =" . $objVendedor->CodViajante . PHP_EOL;
            }
        }
    }

    






        $query = "SELECT 
                        cliente.nombre_cliente,
                        {$objVendedor->CodViajante} AS codViajante,
                        cliente.IDIVA,
                        cliente.Codigo,
                        cliente.Estado,
                        cliente.saldo,
                        cliente.telefono,
                        cliente.listaPrecio,
                        cliente.Email As email,
                        cliente.EmailContacto AS emailContacto,
                        cliente.id_manual_cli,
                        Tipo_Cliente.Nombretipocliente AS NombTC, 
                        Contribuyentes.Abreviado AS IVA 
                FROM cliente 
                LEFT JOIN tipo_cliente ON cliente.TipoCliente = tipo_cliente.idTipoCliente
                LEFT JOIN contribuyentes ON contribuyentes.idIVA = cliente.idIVA
                WHERE
                cliente.Codigo <> 1
                AND cliente.Estado='Activo'                            
                $where
                
                ORDER BY cliente.nombre_cliente  LIMIT 0,10";
        //  echo "<pre>";
        //  print_r($query).PHP_EOL;
        //  echo "</pre>";

        $hacer = mysqli_query($connV, $query) or die('No puedo ubicar el busqueda rapida Art.' .  mysqli_error($connV) . '<br>' . $query);
        
        $clientes = array();
        if ($hacer) {
            // While there are results loop through them - fetching an Object (i like PHP5 btw!).
            $textoCliente = "";
            $textoCliente .= "<thead>";
            $textoCliente .= "  <tr>";
            // $textoCliente .= "      <th>Id</th>";
            $textoCliente .= "      <th>Cliente</th>";
            //                        $textoCliente .= "      <th>Tipo</th>";
            //                        $textoCliente .= "      <th>Telefono</th>";
            // $textoCliente .= "      <th>IVA</th>";
        $textoCliente .= "      <th>&nbsp</th>";
            $textoCliente .= "   </tr>";
            $textoCliente .= "</thead>";
            $textoCliente .= "<tbody>";
            while ($result = mysqli_fetch_object($hacer)) {
                $clientes[] = $result;
            }

            

            if (!empty($clientes)) {
                
                foreach ($clientes as $cliente) {
                    
                    $codCliente = $cliente->Codigo;
                    $_SESSION['tipoCliente'] = $cliente->NombTC;
                    if ($usaIdManual == "Si") {
                        $codClienteT = $cliente->id_manual_cli;
                        // id manual vacio...suele pasr..
                        if(strlen($cliente->id_manual_cli)==0){
                            $codClienteT=$codCliente;
                        }
                    }

                    if ($usaIdManual == "No") {
                        $codClienteT = $codCliente;
                    }

                    //                                $telefono = $cliente->telefono;
                    //                                if($caminoDispo!="" && strlen($cliente->telefono)>14){ 
                    //                                        $telefono=substr($cliente->telefono,12)."...";
                    //                                    
                    //                                }else{
                    //                                     $telefono=$cliente->telefono;
                    //                                }
                    $textoCliente .= "   <tr>";
                    // $textoCliente .="       <td class='dt-body-left'>{$codClienteT}</td>";
                    $textoCliente .= "       <td>";
                    $textoCliente .= "<div class='nombreClienteLista'>" . ucwords($cliente->nombre_cliente) . "</div>";
                    $textoCliente .= "          <p class='datosClienteLista'>Cod: <strong>{$codClienteT}</strong> | Saldo: <strong>$" . number_format($cliente->saldo, 2, ',', '.') . "</strong> | Iva: <strong>{$cliente->IVA}</strong> </p>";
                    $textoCliente .= "          <p class='datosClienteLista'>Tel: <strong>{$cliente->telefono}</strong> | Email: <strong>" . $cliente->email . "</strong> </p>";
                    $textoCliente .= "</td>";

                    // $textoCliente .="       <td>{$telefono}</td>";
                    // $textoCliente .="       <td>{$cliente->IVA}</td>";

                    //                                if($caminoDispo!=""){
                    //                                    $textoCliente .="<td class='acciones'>";
                    //                                    $textoCliente .= "<a class='selCliente' rel='{$cliente->Codigo}' title='Seleccionar el Cliente' ><i class='fa fa-check-circle fa-3x fa-lg fa-fw'></i></a>";                                    
                    //                                    $textoCliente .= " <a class='editCliente' rel='{$cliente->Codigo}' title='Editar datos del cliente' ><i class='fa fa-pen-square fa-3x fa-lg fa-fw'></i></a>";
                    //                                    $textoCliente .= " <a class='editDomicilios' rel='{$cliente->Codigo}' title='Domicilios adicionales' ><i class='fa fa-address-card fa-3x fa-lg fa-fw'></i></a>";                                            
                    //                                    $textoCliente .= "</td>";
                    //                                }else{
                    //                                    $textoCliente .="<td class='acciones dt-right'>";
                    //                                    $textoCliente .= "<a class='selCliente' rel='{$cliente->Codigo}' title='Seleccionar el Cliente' ><i class='fa fa-check-circle fa-lg fa-fw fa-2x' ></i></a>";                                                
                    //                                    $textoCliente .= " <a class='editCliente' rel='{$cliente->Codigo}' title='Editar datos del cliente' ><i class='fa fa-pen-square fa-lg fa-fw fa-2x'></i></a>";
                    //                                    $textoCliente .= " <a class='editDomicilios' rel='{$cliente->Codigo}' title='Domicilios adicionales' ><i class='fa fa-address-card fa-lg fa-fw fa-2x'></i></a>";                                        $textoCliente .= "</td>";
                    //                                                    
                    //                                }

                    // if ($caminoDispo != "") {
                        $textoCliente .= "<td class='acciones'>";
                        $textoCliente .= "<span class='selCliente' rel='{$cliente->Codigo}' title='Seleccionar el Cliente' ><i class='fa fa-check-circle fa-fw fa-2x'></i></span>";

                        $textoCliente .= " <span class='editCliente' rel='{$cliente->Codigo}' title='Editar datos del cliente' ><i class='fa fa-pen-square fa-2x fa-fw'></i></span>";
                        $textoCliente .= " <span class='editDomicilios' rel='{$cliente->Codigo}' title='Domicilios adicionales' ><i class='fa fa-address-card fa-2x fa-fw'></i></span>";
                        $textoCliente .= "</td>";
                    // } else {
                        // $textoCliente .= "<td class='acciones dt-right'>";
                        // $textoCliente .= "<a class='selCliente' rel='{$cliente->Codigo}' title='Seleccionar el Cliente' ><i class='fa fa-check-circle fa-lg fa-fw ' ></i></a>";
                        // $textoCliente .= " <a class='editCliente' rel='{$cliente->Codigo}' title='Editar datos del cliente' ><i class='fa fa-pen-square fa-lg fa-fw '></i></a>";
                        // $textoCliente .= " <a class='editDomicilios' rel='{$cliente->Codigo}' title='Domicilios adicionales' ><i class='fa fa-address-card fa-lg fa-fw '></i></a>";
                        // $textoCliente .= "</td>";
                    // }
                    $textoCliente .= "   </tr>";
                }
                $textoCliente .= "</tbody>";
                echo $textoCliente;
            } else {
                $textoCliente .= "<tr><td>No se encontraron clientes</td><td></td></tr>";
                $textoCliente .= "</tbody>";

                echo $textoCliente;
            }
        } else {
            echo 'ERROR: There was a problem with the query.';
        }
    }

    



// * seleccion de comprobante.
function seleccionarComprobante($arrParametros)
{
    $frm = $arrParametros['frm'];
    $formulario = 'pedido';
    $uFormulario = 'alta_pedido.php';
    switch ($frm) {
        case 0:
            #Pedido
            $formulario = 'pedido';
            $uFormulario = 'alta_pedido.php';
            break;
        case 1:
            #remito por sistema
            $formulario = 'remitoSistema';
            $uFormulario = 'lista-facturas-sin-stock.php';
            break;
        case 2:
            #remito talonario
            $formulario = 'remitoTalonario';

            ///$uFormulario = 'alta-remito-talonario.php';
            $uFormulario = 'lista-facturas-sin-stock.php';
            break;
        case 3:
            #Presupuesto
            $formulario = 'presupuesto';
            $uFormulario = 'alta_presupuesto.php';
            break;
        case 4:
            #recibos
            $formulario = 'recibo';
            //                $uFormulario = 'alta_recibo_seleccion_factura.php';
            $uFormulario = 'recibo/alta_recibo.php';
            //$uFormulario = 'recibo-old/alta_recibo.php';
            break;
        case 5:
            #Devolucion
            $formulario = 'devolucion';
            $uFormulario = 'alta-devolucion.php';
            break;
    }

    $_SESSION['formulario']     = $formulario;
    $_SESSION['uFormulario']    = $uFormulario;

    header('Content-Type: application/json');
    $vuelta = array('estado' => 'ok', 'url' => $uFormulario);
    print json_encode($vuelta);
}


// buscador de cliente
if (isset($_POST['buscarCliente']) && $_POST['buscarCliente'] == "1") {
    $parametros = array();

    $parametros['patronCliente']    = mysqli_real_escape_string($connV, $_POST['queCliente']); // texto a buscar

    $parametros['modoBus']          = mysqli_real_escape_string($connV, $_POST["claseBusqueda"]); // codigo o texto
    $parametros['codCliente']          = mysqli_real_escape_string($connV, $_POST["codigo"]);
    $parametros['connV']            = $connV;

    // agregar si existe la sesion que valida el uso de vendedores cliente, revisar si el listado de clientes no viene vacio,
    // si viene vacio no hacer nada, si viene con datos entonces agregar de forma fija el filtro de clientes de esta lista
    if (isset($_SESSION['permiso_uso_vendedor_cliente']) && $_SESSION['permiso_uso_vendedor_cliente'] == 'Si') {
        if (!empty($_SESSION['lista_clientes_vendedor'])) {
            $parametros['listaClientesVendedor'] = $_SESSION['lista_clientes_vendedor'];
        }
    }
    buscarCliente($parametros);
}

// seleccion de comprobante
if (isset($_GET['seleccionarComprobante']) && $_GET['seleccionarComprobante'] == 1) {
    $parametros = array();
    $parametros['frm']        = $_GET['frm'];



    seleccionarComprobante($parametros);
}

// obtener datos del cliente seleccionado.
if(isset($_GET['traeDatosClienteSeleccionado'])&&$_GET['traeDatosClienteSeleccionado']==1){
    $clienteSeleccionado = array();
    if(isset( $_SESSION['cliente'])&&!empty($_SESSION['cliente'])){
        $clienteSeleccionado = $_SESSION['cliente'];
    }
    header('Content-Type: application/json');
    print json_encode($clienteSeleccionado);
}