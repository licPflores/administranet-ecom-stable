<?php
//header('Content-type: text/html; charset=utf-8');
require_once 'sesion.inc.php';
/*
 * busco el valor del auto suggest
 * 
 */
$usaIdManual = $_SESSION["usa_id_manual"];  

if (!defined('RP_PDF_DETALLE_MAX_ITEMS')) {
    define('RP_PDF_DETALLE_MAX_ITEMS', 2500);
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
                            comp_ped.TipoComprobante ='PED'
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

if (!function_exists('rp_h')) {
    function rp_h($valor)
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

function rp_tipo_informe($post)
{
    if (isset($post['tipoInforme']) && strtolower($post['tipoInforme']) === 'detallado') {
        return 'detallado';
    }
    return 'resumen';
}

function rp_build_condiciones($post, $connV, $objVendedor)
{
    $consulta = "";
    $deQuien = "";

    $campoBusca = isset($post['campoBusca']) ? $post['campoBusca'] : '';
    if ($campoBusca !== '' && $campoBusca !== '-') {
        switch ($campoBusca) {
            case 'Fecha':
                $fechaDesde = isset($post['fechaDesde']) ? mysqli_real_escape_string($connV, $post['fechaDesde']) : '';
                $fechaHasta = isset($post['fechaHasta']) ? mysqli_real_escape_string($connV, $post['fechaHasta']) : '';
                if ($fechaDesde !== '' && $fechaHasta !== '') {
                    $consulta .= " AND Fecha BETWEEN '" . $fechaDesde . "' AND '" . $fechaHasta . "'";
                }
                break;
            case 'NroComprobante':
                $numeroComp = isset($post['numeroComp']) ? mysqli_real_escape_string($connV, $post['numeroComp']) : '';
                if ($numeroComp !== '') {
                    $consulta .= " AND NroCompBusq LIKE '" . $numeroComp . "%'";
                }
                break;
            case 'TipoPedido':
                $tipoPedido = isset($post['tipoPedido']) ? mysqli_real_escape_string($connV, $post['tipoPedido']) : '';
                if ($tipoPedido !== '' && $tipoPedido !== '1') {
                    $consulta .= " AND comp_ped.TipoPedido='" . $tipoPedido . "'";
                }
                break;
        }
    }

    if (isset($post['estadoPedido']) && $post['estadoPedido'] !== '' && $post['estadoPedido'] !== '1') {
        $estadoPedido = mysqli_real_escape_string($connV, $post['estadoPedido']);
        $consulta .= " AND comp_ped.Estado='" . $estadoPedido . "'";
    }

    $vendedor = isset($post['vendedor']) ? $post['vendedor'] : 'true';
    if ($vendedor === 'true') {
        $codViajante = isset($objVendedor->CodViajante) ? $objVendedor->CodViajante : 0;
        if (isset($post['filtraVendedor'])) {
            $codViajante = $post['filtraVendedor'];
        }

        if ($codViajante !== 'todos') {
            $deQuien = " AND comp_ped.CodViajante=" . intval($codViajante);
        }

        if (isset($post['listaPed']) && $post['listaPed'] === 'cliente' && isset($_SESSION['idcliente']) && $_SESSION['idcliente'] !== '') {
            $deQuien .= " AND comp_ped.Codigo=" . intval($_SESSION['idcliente']);
        }
    } else {
        if (isset($_SESSION['idcliente']) && $_SESSION['idcliente'] !== '') {
            $deQuien .= " AND comp_ped.Codigo=" . intval($_SESSION['idcliente']);
        }
    }

    return array($consulta, $deQuien);
}

function rp_get_pedidos($connV, $consulta, $deQuien)
{
    $sqlPedido = "SELECT     comp_ped.CodigoMovimiento,
                            comp_ped.id_comp_ped AS id,
                            DATE_FORMAT(comp_ped.Fecha,'%d/%m/%Y') AS FechaB,
                            comp_ped.fecha_control AS FechaHoraCreado,
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
                            cliente.Codigo,
                            cliente.id_manual_cli,
                            CONCAT(viajantes.CodViajante,' - ',viajantes.Nombre) AS NombViajante,
                            comp_ped.TipoPedido,
                            comp_ped.autorizacion_sistema,
                            comp_ped.autorizacion_web,
                            comp_ped.Anulado,
                            (comp_ped.IVA1 + comp_ped.IVA2) AS IVA,
                            (comp_ped.SubTotalDesc + comp_ped.IVA1 + comp_ped.IVA2) AS Total,
                                COALESCE(stockp_count.CantidadItems, 0) AS CantidadItems
                    FROM comp_ped
                    LEFT JOIN cliente ON cliente.Codigo = comp_ped.Codigo
                    LEFT JOIN viajantes ON viajantes.CodViajante = comp_ped.CodViajante
                            LEFT JOIN (
                            SELECT stockp.CodigoMovimiento, COUNT(*) AS CantidadItems
                            FROM stockp
                            GROUP BY stockp.CodigoMovimiento
                            ) AS stockp_count ON stockp_count.CodigoMovimiento = comp_ped.CodigoMovimiento
                    WHERE comp_ped.TipoComprobante = 'PED'
                    {$deQuien}
                    {$consulta}
                    ORDER BY comp_ped.Fecha DESC";

    $hacer = mysqli_query($connV, $sqlPedido) or die('No puedo consultar el pedido ' . $sqlPedido);
    $pedidos = array();
    if ($hacer) {
        while ($p = mysqli_fetch_object($hacer)) {
            $pedidos[] = $p;
        }
    }
    return $pedidos;
}

function rp_get_detalle_pedido($connV, $codigoMovimiento, $usaIdManual = 'no')
{
    $codigoMovimiento = intval($codigoMovimiento);
    $joinArticulo = '';
    $selectIdCodigo = 'stockp.IDArt AS CodigoArticulo';
    if ($usaIdManual === 'si') {
        $joinArticulo = ' LEFT JOIN articulo ON articulo.IDArt = stockp.IDArt ';
        $selectIdCodigo = 'COALESCE(articulo.id_manual, stockp.IDArt) AS CodigoArticulo';
    }
    $sql = "SELECT
                stockp.IDArt,
                {$selectIdCodigo},
                stockp.Descripcion,
                stockp.Salida,
                stockp.PrecioVentaxU,
                stockp.PrecioNetoxU,
                stockp.PrecioIVAxU,
                stockp.PrecioNetoxR,
                stockp.PrecioIVAxR,
                stockp.TipoIVA,
                stockp.promocion,
                stockp.tipo_unidad,
                stockp.cantidad_dividir,
                stockp.cantidad_unidad_display
            FROM stockp
            {$joinArticulo}
            WHERE stockp.CodigoMovimiento = " . $codigoMovimiento . "
            ORDER BY stockp.id_stock ASC";

    $res = mysqli_query($connV, $sql);
    $detalle = array();
    if ($res) {
        while ($row = mysqli_fetch_object($res)) {
            $detalle[] = $row;
        }
    }
    return $detalle;
}

function rp_get_detalle_pedidos_map($connV, $codigoMovimientos, $usaIdManual = 'no')
{
    $map = array();
    $ids = array();

    if (!is_array($codigoMovimientos)) {
        return $map;
    }

    foreach ($codigoMovimientos as $codigoMovimiento) {
        $codigoMovimiento = (int)$codigoMovimiento;
        if ($codigoMovimiento > 0) {
            $ids[$codigoMovimiento] = $codigoMovimiento;
        }
    }

    if (empty($ids)) {
        return $map;
    }

    $joinArticulo = '';
    $selectIdCodigo = 'stockp.IDArt AS CodigoArticulo';
    if ($usaIdManual === 'si') {
        $joinArticulo = ' LEFT JOIN articulo ON articulo.IDArt = stockp.IDArt ';
        $selectIdCodigo = 'COALESCE(articulo.id_manual, stockp.IDArt) AS CodigoArticulo';
    }

    $sql = "SELECT
                stockp.CodigoMovimiento,
                stockp.IDArt,
                {$selectIdCodigo},
                stockp.Descripcion,
                stockp.Salida,
                stockp.PrecioVentaxU,
                stockp.PrecioNetoxU,
                stockp.PrecioIVAxU,
                stockp.PrecioNetoxR,
                stockp.PrecioIVAxR,
                stockp.TipoIVA,
                stockp.promocion,
                stockp.tipo_unidad,
                stockp.cantidad_dividir,
                stockp.cantidad_unidad_display
            FROM stockp
            {$joinArticulo}
            WHERE stockp.CodigoMovimiento IN (" . implode(',', $ids) . ")
            ORDER BY stockp.CodigoMovimiento ASC, stockp.id_stock ASC";

    $res = mysqli_query($connV, $sql);
    if ($res) {
        while ($row = mysqli_fetch_object($res)) {
            $codigoMovimiento = (int)$row->CodigoMovimiento;
            if (!isset($map[$codigoMovimiento])) {
                $map[$codigoMovimiento] = array();
            }
            $map[$codigoMovimiento][] = $row;
        }
    }

    return $map;
}

function rp_formatear_cantidad_presentacion($item)
{
    $presentacion = rp_describir_presentacion($item);
    return $presentacion['texto'];
}

function rp_describir_presentacion($item)
{
    $salidaUnidades = (float)$item->Salida;
    $divisor = (int)$item->cantidad_dividir;
    $tipoUnidad = trim((string)$item->tipo_unidad);
    if ($tipoUnidad === '') {
        $tipoUnidad = 'Unidad';
    }

    if ($divisor <= 0) {
        $divisor = 1;
    }

    $tipoLower = strtolower($tipoUnidad);
    if ($tipoLower === 'unidad') {
        $cantidad = ((float)(int)$salidaUnidades === $salidaUnidades)
            ? number_format($salidaUnidades, 0, ',', '.')
            : number_format($salidaUnidades, 2, ',', '.');

        return array(
            'cantidad' => $cantidad,
            'badge' => 'UNIDAD x 1',
            'detalle' => 'unidades',
            'texto' => $cantidad . ' unidades'
        );
    }

    $cantidadPresentacion = $salidaUnidades / $divisor;
    $cantidad = ((float)(int)$cantidadPresentacion === $cantidadPresentacion)
        ? number_format($cantidadPresentacion, 0, ',', '.')
        : number_format($cantidadPresentacion, 2, ',', '.');
    $etiqueta = ($tipoLower === 'bulto') ? 'bultos' : 'display';
    $badge = (($tipoLower === 'bulto') ? 'BULTO' : 'DISPLAY') . ' x ' . number_format($divisor, 0, ',', '.');
    $detalle = number_format($salidaUnidades, 0, ',', '.') . ' un';

    return array(
        'cantidad' => $cantidad,
        'badge' => $badge,
        'detalle' => $detalle,
        'texto' => $cantidad . ' ' . $etiqueta . ' ' . $detalle
    );
}

function rp_render_presentacion_badge_html($item)
{
    $presentacion = rp_describir_presentacion($item);

    return '<div class="detalle-cantidad" style="display:inline-block; min-width:120px; text-align:right;">'
        . '<div class="detalle-cantidad-linea" style="white-space:nowrap;">'
        . '<span class="detalle-cantidad-numero" style="font-weight:700; color:#20335f; font-size:13px;">' . rp_h($presentacion['cantidad']) . '</span> '
    . '<span class="detalle-cantidad-badge" style="display:inline-block; padding:2px 8px; border-radius:999px; background:#d9e4ff; border:1px solid #8ea4de; color:#2a3e72; font-size:10px; font-weight:700; letter-spacing:0.4px;">' . rp_h($presentacion['badge']) . '</span>'
        . '</div>'
    . '<div class="detalle-cantidad-meta" style="margin-top:2px; font-size:11px; color:#5a6480;">' . rp_h($presentacion['detalle']) . '</div>'
        . '</div>';
}

function rp_render_detalle_html($detalle, $usaIdManual = 'no')
{
    if (empty($detalle)) {
        return '<div class="detalle-pedido-vacio">Sin items para este comprobante.</div>';
    }

    $html = '';
    $html .= '<div class="detalle-card" style="padding:8px 10px; background:#f5f7fc; border:1px solid #d8dfef; border-radius:8px;">';
    $html .= '<table class="tabla-detalle-pedido" width="100%" border="0" cellspacing="0" cellpadding="5" style="border-collapse:collapse; border:1px solid #d8dfef; background:#fff;">';
    $html .= '<thead><tr>';
    $html .= '<th style="background-color:#2a3e72; color:#fff; border:1px solid #fff; padding:5px;">' . ($usaIdManual === 'si' ? 'Cod. Manual' : 'Codigo') . '</th>';
    $html .= '<th style="background-color:#2a3e72; color:#fff; border:1px solid #fff; padding:5px;">Descripcion</th>';
    $html .= '<th style="background-color:#2a3e72; color:#fff; border:1px solid #fff; padding:5px; text-align:right;">Cantidad + Presentacion</th>';
    $html .= '<th style="background-color:#2a3e72; color:#fff; border:1px solid #fff; padding:5px; text-align:right;">Unidades</th>';
    $html .= '<th style="background-color:#2a3e72; color:#fff; border:1px solid #fff; padding:5px; text-align:right;">Precio Unit. c/IVA</th>';
    $html .= '<th style="background-color:#2a3e72; color:#fff; border:1px solid #fff; padding:5px; text-align:right;">Total Renglon c/IVA</th>';
    $html .= '</tr></thead><tbody>';

    foreach ($detalle as $item) {
        $precioNetoxR = (float)$item->PrecioNetoxR;
        $precioIVAxR = (float)$item->PrecioIVAxR;
        $salidaUnidades = (float)$item->Salida;
        
        // Precio unitario con IVA = (Neto + IVA del renglón) / cantidad
        // el precio esta dado por la presentacion, el precio de costo es por unidad, pero el precio unitario.es x unidad.
        $precioUnitarioConIVA = ($salidaUnidades > 0) ? ($item->PrecioNetoxU+ $item->PrecioIVAxU) : 0;
        // Total del renglón con IVA
        $totalRenglonConIVA = $precioNetoxR + $precioIVAxR;
        
        $html .= '<tr>';
        $html .= '<td style="border:1px solid #d8dfef; padding:4px;">' . rp_h($item->CodigoArticulo) . '</td>';
        $html .= '<td style="border:1px solid #d8dfef; padding:4px;">' . rp_h($item->Descripcion) . '</td>';
        $html .= '<td style="border:1px solid #d8dfef; padding:4px; text-align:right;">' . rp_render_presentacion_badge_html($item) . '</td>';
        $html .= '<td style="border:1px solid #d8dfef; padding:4px; text-align:right;">' . number_format($salidaUnidades, 0, ',', '.') . '</td>';
        $html .= '<td style="border:1px solid #d8dfef; padding:4px; text-align:right;">$ ' . number_format($precioUnitarioConIVA, 2, ',', '.') . '</td>';
        $html .= '<td style="border:1px solid #d8dfef; padding:4px; text-align:right;">$ ' . number_format($totalRenglonConIVA, 2, ',', '.') . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';
    $html .= '</div>';
    return $html;
}

function rp_estado_clase($estado)
{
    switch ($estado) {
        case 'Facturado':
            return 'facturado';
        case 'Parcial':
            return 'parcial';
        case 'Pendiente':
            return 'pendiente';
        case 'Cerrado':
            return 'cerrado';
        case 'Preparado':
            return 'preparado';
        case 'En Remito':
            return 'pendienteRemito';
        default:
            return 'promocion';
    }
}

function rp_render_columna_gestion($ped)
{
    $html = '<div class="pedido-gestion">';
    $html .= '<div class="pedido-gestion-tipo">Tipo: ' . rp_h($ped->TipoPedido) . '</div>';
    $html .= '<div class="pedido-gestion-estado ' . rp_estado_clase($ped->Estado) . '">Estado: ' . rp_h($ped->Estado) . '</div>';
    $html .= '<div class="pedido-gestion-autorizado">' . rp_h($ped->autorizacion_sistema) . '</div>';
    $html .= '</div>';

    return $html;
}

function rp_render_tabla_pedidos($pedidos, $usaIdManual, $tipoInforme, $connV, $modoExport = false)
{
    $totalFinal = 0;
    $mostrarAcciones = !$modoExport;
    $thead = '';
    $thead .= '<thead><tr>';
    $thead .= '<th>#</th>';
    $thead .= '<th>Fecha</th>';
    $thead .= '<th>N°Comprob.</th>';
    $thead .= '<th>Cliente</th>';
    $thead .= '<th>Vendedor</th>';
    $thead .= '<th>Cond.Vta</th>';
    $thead .= '<th class="dt-right">Total</th>';
    $thead .= '<th>Gestion</th>';
    $thead .= '<th>Entrega</th>';
    $thead .= '<th>Creado</th>';
    $thead .= '<th>Items</th>';
    $thead .= '<th>Anul.</th>';
    if ($mostrarAcciones) {
        $thead .= '<th>&nbsp;</th>';
    }
    $thead .= '</tr></thead>';

    $tbody = '<tbody>';
    if (count($pedidos) > 0) {
        foreach ($pedidos as $ped) {
            $totalFinal += (float)$ped->Total;
            $claseEstado = rp_estado_clase($ped->Estado);

            $tbody .= '<tr>';
            $tbody .= '<td></td>';
            $tbody .= '<td class="dt-nowrap">' . rp_h($ped->FechaB) . '</td>';
            $tbody .= '<td class="dt-nowrap">' . rp_h($ped->NroComprobante) . '</td>';
            if ($usaIdManual == 'si') {
                $tbody .= '<td>' . rp_h($ped->id_manual_cli) . ' - ' . rp_h($ped->nombre_cliente) . '</td>';
            } else {
                $tbody .= '<td>' . rp_h($ped->Codigo) . ' - ' . rp_h($ped->nombre_cliente) . '</td>';
            }
            $tbody .= '<td class="dt-nowrap">' . rp_h($ped->NombViajante) . '</td>';
            $tbody .= '<td>' . rp_h($ped->CondVenta) . '</td>';
            $tbody .= '<td class="dt-right">' . number_format((float)$ped->Total, 2, '.', '') . '</td>';
            $tbody .= '<td>' . rp_render_columna_gestion($ped) . '</td>';
            $tbody .= '<td>' . rp_h($ped->FormaEntrega) . '</td>';
            $tbody .= '<td>' . rp_h($ped->FechaHoraCreado) . '</td>';
            $tbody .= '<td>' . rp_h($ped->CantidadItems) . '</td>';
            $tbody .= '<td><strong>' . rp_h($ped->Anulado) . '</strong></td>';

            if ($mostrarAcciones) {
                $tbody .= '<td>';
                $tbody .= '<span class="acciones"><a target="blank" href="ver_pedido-movil.php?codigomovimiento=' . rp_h($ped->CodigoMovimiento) . '&tipocomprobante=PED" title="Visualizar comprobante" alt="Visualizar comprobante" mov="' . rp_h($ped->CodigoMovimiento) . '" comprobante="PED"><i class="fa fa-file-pdf barrita fa-lg fa-2x"></i></a></span>';

                if ($tipoInforme === 'detallado') {
                    $tbody .= '<span class="acciones"><a href="javascript:void(0);" class="toggleDetallePedido" mov="' . rp_h($ped->CodigoMovimiento) . '" title="Ver detalle"><i class="fa fa-list-ul barrita fa-lg fa-2x"></i></a></span>';
                }

                if ($ped->Estado == 'Pendiente' && $ped->Anulado == 'No') {
                    $tbody .= '<span class="acciones rojo"><a href="javascript:void();" title="Anular pedido" alt="Anular pedido" mov="' . rp_h($ped->CodigoMovimiento) . '" comprobante="PED" nrocomprobante="' . rp_h($ped->NroComprobante) . '" class="anularPedido"><i class="fa fa-trash barrita fa-lg fa-2x"></i></a></span>';
                }
                $tbody .= '</td>';
            }
            $tbody .= '</tr>';

            if ($modoExport && $tipoInforme === 'detallado') {
                $colspan = $mostrarAcciones ? 13 : 12;
                $tbody .= '<tr><td colspan="' . $colspan . '">' . rp_render_detalle_html(rp_get_detalle_pedido($connV, $ped->CodigoMovimiento, $usaIdManual), $usaIdManual) . '</td></tr>';
            }
        }
    }
    $tbody .= '</tbody>';

    $tfoot = '<tfoot><tr>';
    $tfoot .= '<th></th>';
    $tfoot .= '<th>Total</th>';
    $tfoot .= '<th></th><th></th><th></th><th></th>';
    $tfoot .= '<th class="dt-right">' . number_format($totalFinal, 2, '.', '') . '</th>';
    $tfoot .= '<th></th><th></th><th></th><th></th><th></th>';
    if ($mostrarAcciones) {
        $tfoot .= '<th></th>';
    }
    $tfoot .= '</tr></tfoot>';

    return $thead . $tbody . $tfoot;
}

function rp_build_filtros_export($post)
{
    $filtros = array();

    $formatearFechaFiltro = function ($fecha) {
        $fecha = trim((string)$fecha);
        if ($fecha === '') {
            return '-';
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha, $m)) {
            return $m[3] . '/' . $m[2] . '/' . $m[1];
        }

        return $fecha;
    };

    $txtTipoInforme = isset($post['tipoInformeTexto']) && trim($post['tipoInformeTexto']) !== ''
        ? trim($post['tipoInformeTexto'])
        : (rp_tipo_informe($post) === 'detallado' ? 'Detallado' : 'Resumen');

    $txtClientes = isset($post['listaPedTexto']) && trim($post['listaPedTexto']) !== ''
        ? trim($post['listaPedTexto'])
        : ((isset($post['listaPed']) && $post['listaPed'] === 'cliente') ? 'Seleccionado' : 'Todos');

    $txtVendedor = isset($post['filtraVendedorTexto']) && trim($post['filtraVendedorTexto']) !== ''
        ? trim($post['filtraVendedorTexto'])
        : (isset($post['filtraVendedor']) && $post['filtraVendedor'] !== '' ? $post['filtraVendedor'] : 'Todos');

    $txtEstado = isset($post['estadoPedidoTexto']) && trim($post['estadoPedidoTexto']) !== ''
        ? trim($post['estadoPedidoTexto'])
        : (isset($post['estadoPedido']) && $post['estadoPedido'] !== '' ? $post['estadoPedido'] : 'Todos');

    $txtTipoPedido = isset($post['tipoPedidoTexto']) && trim($post['tipoPedidoTexto']) !== ''
        ? trim($post['tipoPedidoTexto'])
        : (isset($post['tipoPedido']) && $post['tipoPedido'] !== '' ? $post['tipoPedido'] : 'Todos');

    $filtros[] = 'Tipo informe: ' . $txtTipoInforme;
    $filtros[] = 'Clientes: ' . $txtClientes;
    $filtros[] = 'Vendedor: ' . $txtVendedor;
    $filtros[] = 'Estado: ' . $txtEstado;
    $filtros[] = 'Tipo pedido: ' . $txtTipoPedido;

    $campoBusca = isset($post['campoBusca']) ? $post['campoBusca'] : '';
    $txtCampoBusca = isset($post['campoBuscaTexto']) && trim($post['campoBuscaTexto']) !== ''
        ? trim($post['campoBuscaTexto'])
        : $campoBusca;
    if ($campoBusca === 'Fecha') {
        $desde = isset($post['fechaDesde']) ? $post['fechaDesde'] : '';
        $hasta = isset($post['fechaHasta']) ? $post['fechaHasta'] : '';
        $filtros[] = 'Rango fechas: ' . $formatearFechaFiltro($desde) . ' a ' . $formatearFechaFiltro($hasta);
    } elseif ($campoBusca === 'NroComprobante') {
        $numero = isset($post['numeroComp']) ? $post['numeroComp'] : '';
        $filtros[] = 'Nro. comprobante: ' . ($numero !== '' ? $numero : 'Todos');
    } elseif ($campoBusca === 'TipoPedido') {
        $filtros[] = 'Busqueda por: ' . ($txtCampoBusca !== '' ? $txtCampoBusca : 'TipoPedido');
    } else {
        $filtros[] = 'Busqueda por: Sin filtro especifico';
    }

    return $filtros;
}

function rp_build_html_export($tablaHtml, $tipoInforme, $post)
{
    $titulo = $tipoInforme === 'detallado' ? 'Listado de Pedidos (Detallado)' : 'Listado de Pedidos (Resumen)';
    $empresa = isset($_SESSION['nombre_empresa']) ? $_SESSION['nombre_empresa'] : 'administraNET';
    $fecha = date('d/m/Y H:i');
    $filtros = rp_build_filtros_export($post);

    $html = '';
    $html .= '<h2 style="margin:0 0 8px 0;">' . rp_h($empresa) . '</h2>';
    $html .= '<h3 style="margin:0 0 6px 0;">' . rp_h($titulo) . '</h3>';
    $html .= '<div style="margin-bottom:12px;font-size:12px;">Emitido: ' . rp_h($fecha) . '</div>';
    $html .= '<div style="margin:0 0 10px 0;padding:8px;border:1px solid #cbd5e1;background:#f8fafc;font-size:11px;">';
    $html .= '<strong>Filtros aplicados</strong><br>';
    foreach ($filtros as $f) {
        $html .= '- ' . rp_h($f) . '<br>';
    }
    $html .= '</div>';
    $html .= '<table width="100%" border="1" cellspacing="0" cellpadding="4">' . $tablaHtml . '</table>';
    return $html;
}

function rp_excel_clean($valor)
{
    $valor = (string)$valor;
    $valor = str_replace(array("\r", "\n", "\t"), ' ', $valor);
    return trim($valor);
}

function rp_excel_decimal($valor, $escala = 8)
{
    if ($valor === null || $valor === '') {
        return '';
    }

    if (function_exists('bcadd')) {
        return str_replace('.', ',', bcadd((string)$valor, '0', (int)$escala));
    }

    return number_format((float)$valor, (int)$escala, ',', '');
}

function rp_excel_decimal_sum($valorA, $valorB, $escala = 8)
{
    if (function_exists('bcadd')) {
        return str_replace('.', ',', bcadd((string)$valorA, (string)$valorB, (int)$escala));
    }

    return number_format((float)$valorA + (float)$valorB, (int)$escala, ',', '');
}

function rp_build_excel_rows($pedidos, $tipoInforme, $connV, $usaIdManual)
{
    $rows = array();

    if ($tipoInforme === 'detallado') {
        $codigosMovimiento = array();
        foreach ($pedidos as $ped) {
            $codigosMovimiento[] = (int)$ped->CodigoMovimiento;
        }
        $detallePorMovimiento = rp_get_detalle_pedidos_map($connV, $codigosMovimiento, $usaIdManual);

        $rows[] = array(
            'Fecha', 'NroComprobante', 'Cliente', 'Vendedor',
            'TipoPedido', 'Estado', 'Entrega',
            'CodigoArticulo', 'Descripcion', 'CantidadPresentacion', 'Unidades',
            'PrecioUnitarioConIVA', 'TotalRenglonConIVA'
        );

        foreach ($pedidos as $ped) {
            $cliente = ($usaIdManual == 'si')
                ? ($ped->id_manual_cli . ' - ' . $ped->nombre_cliente)
                : ($ped->Codigo . ' - ' . $ped->nombre_cliente);

            $detalle = isset($detallePorMovimiento[(int)$ped->CodigoMovimiento])
                ? $detallePorMovimiento[(int)$ped->CodigoMovimiento]
                : array();
            if (empty($detalle)) {
                $rows[] = array(
                    $ped->FechaB,
                    $ped->NroComprobante,
                    $cliente,
                    $ped->NombViajante,
                    $ped->TipoPedido,
                    $ped->Estado,
                    $ped->FormaEntrega,
                    '', '', '', '', '', ''
                );
                continue;
            }

            foreach ($detalle as $item) {
                $rows[] = array(
                    $ped->FechaB,
                    $ped->NroComprobante,
                    $cliente,
                    $ped->NombViajante,
                    $ped->TipoPedido,
                    $ped->Estado,
                    $ped->FormaEntrega,
                    $item->CodigoArticulo,
                    $item->Descripcion,
                    rp_formatear_cantidad_presentacion($item),
                    rp_excel_decimal($item->Salida, 8),
                    rp_excel_decimal_sum($item->PrecioNetoxU, $item->PrecioIVAxU, 8),
                    rp_excel_decimal_sum($item->PrecioNetoxR, $item->PrecioIVAxR, 8)
                );
            }
        }
    } else {
        $rows[] = array(
            'Fecha', 'NroComprobante', 'Cliente', 'Vendedor',
            'CondVenta', 'Total', 'TipoPedido', 'Estado', 'Entrega', 'Creado', 'Items', 'Anulado'
        );

        foreach ($pedidos as $ped) {
            $cliente = ($usaIdManual == 'si')
                ? ($ped->id_manual_cli . ' - ' . $ped->nombre_cliente)
                : ($ped->Codigo . ' - ' . $ped->nombre_cliente);

            $rows[] = array(
                $ped->FechaB,
                $ped->NroComprobante,
                $cliente,
                $ped->NombViajante,
                $ped->CondVenta,
                rp_excel_decimal($ped->Total, 8),
                $ped->TipoPedido,
                $ped->Estado,
                $ped->FormaEntrega,
                $ped->FechaHoraCreado,
                $ped->CantidadItems,
                $ped->Anulado
            );
        }
    }

    return $rows;
}

function rp_mpdf_write_html_chunked($mpdf, $html, $maxChars = 40000)
{
    $html = (string)$html;
    $maxChars = (int)$maxChars;

    if ($html === '') {
        return;
    }

    if ($maxChars < 10000) {
        $maxChars = 10000;
    }

    $remaining = $html;
    while (strlen($remaining) > $maxChars) {
        $head = substr($remaining, 0, $maxChars);

        $cutPos = strrpos($head, '</tr>');
        $cutLen = 5;

        if ($cutPos === false || $cutPos < (int)($maxChars * 0.35)) {
            $cutPos = strrpos($head, '</div>');
            $cutLen = 6;
        }

        if ($cutPos === false || $cutPos < (int)($maxChars * 0.35)) {
            $cutPos = strrpos($head, '>');
            $cutLen = 1;
        }

        if ($cutPos === false || $cutPos <= 0) {
            $cutPos = $maxChars;
            $cutLen = 0;
        }

        $chunk = substr($remaining, 0, $cutPos + $cutLen);
        $mpdf->WriteHTML($chunk, 2);
        $remaining = substr($remaining, $cutPos + $cutLen);
    }

    if ($remaining !== '') {
        $mpdf->WriteHTML($remaining, 2);
    }
}

function rp_build_pdf_table_chunk_html($headers, $rows)
{
    if (empty($headers)) {
        return '';
    }

    $html = '<table width="100%" cellpadding="3" cellspacing="0" style="border-collapse:collapse;">';
    $html .= '<thead><tr>';
    foreach ($headers as $h) {
        $html .= '<th style="border:1px solid #999;background:#efefef;font-size:8.5pt;">' . rp_h($h) . '</th>';
    }
    $html .= '</tr></thead><tbody>';

    if (empty($rows)) {
        $html .= '<tr><td colspan="' . count($headers) . '" style="border:1px solid #999;">Sin resultados</td></tr>';
    } else {
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td style="border:1px solid #d4d4d4;font-size:8pt;">' . rp_h((string)$cell) . '</td>';
            }
            $html .= '</tr>';
        }
    }

    $html .= '</tbody></table>';
    return $html;
}

function rp_pdf_detalle_estadisticas($pedidos)
{
    $cantidadPedidos = 0;
    $cantidadItems = 0;

    if (!is_array($pedidos)) {
        return array('pedidos' => 0, 'items' => 0);
    }

    foreach ($pedidos as $ped) {
        $cantidadPedidos++;
        $cantidadItems += isset($ped->CantidadItems) ? (int)$ped->CantidadItems : 0;
    }

    return array('pedidos' => $cantidadPedidos, 'items' => $cantidadItems);
}

function rp_pdf_detalle_limite_html($stats, $limiteItems)
{
    $pedidos = isset($stats['pedidos']) ? (int)$stats['pedidos'] : 0;
    $items = isset($stats['items']) ? (int)$stats['items'] : 0;
    $volverUrl = isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] !== ''
        ? $_SERVER['HTTP_REFERER']
        : 'lista-pedidos-total.php';

    $html = '';
    $html .= '<!doctype html><html><head><meta charset="utf-8"><title>Exportacion PDF con mucho volumen</title>';
    $html .= '<style>body{font-family:Arial,sans-serif;margin:24px;color:#1f2937;background:#f5f7fb;}';
    $html .= '.box{border:1px solid #d1d5db;border-radius:10px;padding:18px;max-width:820px;background:#fff;box-shadow:0 8px 20px rgba(15,23,42,.08);}';
    $html .= 'h2{margin:0 0 10px 0;font-size:22px;color:#0f172a;}';
    $html .= 'p{margin:8px 0;line-height:1.45;}';
    $html .= '.dato{font-weight:700;color:#0f172a;}';
    $html .= '.bloque{margin-top:12px;padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;background:#f8fafc;}';
    $html .= '.acciones{margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;}';
    $html .= '.btn{display:inline-block;padding:9px 14px;border-radius:8px;text-decoration:none;border:1px solid #2a3e72;background:#2a3e72;color:#fff;font-weight:700;}';
    $html .= '.btn-sec{background:#fff;color:#2a3e72;}';
    $html .= '</style></head><body>';
    $html .= '<div class="box">';
    $html .= '<h2>No se genero el PDF detallado por exceso de informacion</h2>';
    $html .= '<p>Este reporte tiene demasiados renglones y puede tardar mucho o cortar la conexion. Por seguridad se frena automaticamente.</p>';
    $html .= '<div class="bloque">';
    $html .= '<p>Pedidos encontrados: <span class="dato">' . number_format($pedidos, 0, ',', '.') . '</span></p>';
    $html .= '<p>Renglones de detalle: <span class="dato">' . number_format($items, 0, ',', '.') . '</span></p>';
    $html .= '<p>Limite permitido para PDF detallado: <span class="dato">' . number_format((int)$limiteItems, 0, ',', '.') . '</span></p>';
    $html .= '</div>';
    $html .= '<p><strong>Que podes hacer:</strong> usar Excel para alto volumen, o filtrar por un rango de fechas mas corto / vendedor / estado.</p>';
    $html .= '<div class="acciones">';
    $html .= '<button class="btn" onclick="history.back();return false;">Volver y cambiar filtros</button>';
    $html .= '<a class="btn btn-sec" href="' . rp_h($volverUrl) . '">Ir al listado</a>';
    $html .= '</div>';
    $html .= '</div></body></html>';

    return $html;
}

function rp_pdf_estado_color($estado)
{
    switch (rp_estado_clase($estado)) {
        case 'facturado':
            return '#02bd02';
        case 'pendiente':
            return '#E36673';
        case 'parcial':
            return '#3473c3';
        case 'cerrado':
            return '#186d51';
        case 'preparado':
            return '#642C8A';
        case 'pendienteRemito':
            return '#FE6C01';
        default:
            return '#20b689';
    }
}

function rp_build_pdf_detalle_por_pedido_html($pedidosChunk, $detallePorMovimiento, $usaIdManual)
{
    $html = '';

    foreach ($pedidosChunk as $ped) {
        $cliente = ($usaIdManual == 'si')
            ? ($ped->id_manual_cli . ' - ' . $ped->nombre_cliente)
            : ($ped->Codigo . ' - ' . $ped->nombre_cliente);

        $detalle = isset($detallePorMovimiento[(int)$ped->CodigoMovimiento])
            ? $detallePorMovimiento[(int)$ped->CodigoMovimiento]
            : array();

        $estadoColor = rp_pdf_estado_color($ped->Estado);

        $html .= '<div style="border:1px solid #dbe2ef;border-radius:7px;padding:8px;margin:0 0 10px 0;background:#f8fbff;">';
        $html .= '<table width="100%" cellpadding="2" cellspacing="0" style="font-size:8.5pt;table-layout:fixed;">';
        $html .= '<tr>';
        $html .= '<td width="18%"><strong>Fecha:</strong> ' . rp_h($ped->FechaB) . '</td>';
        $html .= '<td width="18%"><strong>Nro:</strong> ' . rp_h($ped->NroComprobante) . '</td>';
        $html .= '<td width="34%"><strong>Cliente:</strong> ' . rp_h($cliente) . '</td>';
        $html .= '<td width="30%"><strong>Vendedor:</strong> ' . rp_h($ped->NombViajante) . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="2"><strong>Entrega:</strong> ' . rp_h($ped->FormaEntrega) . '</td>';
        $html .= '<td><strong>Tipo:</strong> ' . rp_h($ped->TipoPedido) . '</td>';
        $html .= '<td><strong>Estado:</strong> <span style="color:' . $estadoColor . ';font-weight:700;">' . rp_h($ped->Estado) . '</span></td>';
        $html .= '</tr>';
        $html .= '</table>';

        $html .= '<table width="100%" cellpadding="3" cellspacing="0" style="border-collapse:collapse;background:#fff;margin-top:6px;">';
        $html .= '<thead><tr>';
        $html .= '<th style="border:1px solid #fff;background:#2a3e72;color:#fff;font-size:8pt;">Codigo</th>';
        $html .= '<th style="border:1px solid #fff;background:#2a3e72;color:#fff;font-size:8pt;">Descripcion</th>';
        $html .= '<th style="border:1px solid #fff;background:#2a3e72;color:#fff;font-size:8pt;">Cantidad + Presentacion</th>';
        $html .= '<th style="border:1px solid #fff;background:#2a3e72;color:#fff;font-size:8pt;">Unidades</th>';
        $html .= '<th style="border:1px solid #fff;background:#2a3e72;color:#fff;font-size:8pt;">Precio Unit c/IVA</th>';
        $html .= '<th style="border:1px solid #fff;background:#2a3e72;color:#fff;font-size:8pt;">Total Renglon c/IVA</th>';
        $html .= '</tr></thead><tbody>';

        if (empty($detalle)) {
            $html .= '<tr><td colspan="6" style="border:1px solid #d8dfef;font-size:8pt;">Sin items.</td></tr>';
        } else {
            foreach ($detalle as $item) {
                $salidaUnidades = (float)$item->Salida;
                $precioUnitarioConIVA = ($salidaUnidades > 0) ? ((float)$item->PrecioNetoxU + (float)$item->PrecioIVAxU) : 0;
                $totalRenglonConIVA = (float)$item->PrecioNetoxR + (float)$item->PrecioIVAxR;

                $html .= '<tr>';
                $html .= '<td style="border:1px solid #d8dfef;font-size:8pt;">' . rp_h($item->CodigoArticulo) . '</td>';
                $html .= '<td style="border:1px solid #d8dfef;font-size:8pt;">' . rp_h($item->Descripcion) . '</td>';
                $html .= '<td style="border:1px solid #d8dfef;font-size:8pt;">' . rp_h(rp_formatear_cantidad_presentacion($item)) . '</td>';
                $html .= '<td style="border:1px solid #d8dfef;font-size:8pt;text-align:right;">' . number_format($salidaUnidades, 0, ',', '.') . '</td>';
                $html .= '<td style="border:1px solid #d8dfef;font-size:8pt;text-align:right;">$ ' . number_format($precioUnitarioConIVA, 2, ',', '.') . '</td>';
                $html .= '<td style="border:1px solid #d8dfef;font-size:8pt;text-align:right;">$ ' . number_format($totalRenglonConIVA, 2, ',', '.') . '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table>';
        $html .= '</div>';
    }

    return $html;
}

if (isset($_POST['exportarArchivo']) && $_POST['exportarArchivo'] == '1') {
    $tipoInforme = rp_tipo_informe($_POST);
    list($consulta, $deQuien) = rp_build_condiciones($_POST, $connV, $objVendedor);
    $pedidos = rp_get_pedidos($connV, $consulta, $deQuien);
    $formato = isset($_POST['formato']) ? strtolower($_POST['formato']) : '';

    if ($formato === 'excel') {
        $nombreArchivo = 'listado_pedidos_' . date('YmdHis') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=' . $nombreArchivo);
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, array('sep=;'), ';');

        fputcsv($out, array('Filtros aplicados'), ';');
        $filtrosExcel = rp_build_filtros_export($_POST);
        foreach ($filtrosExcel as $filtro) {
            $partesFiltro = explode(': ', $filtro, 2);
            $etiqueta = isset($partesFiltro[0]) ? rp_excel_clean($partesFiltro[0]) : '';
            $valor = isset($partesFiltro[1]) ? rp_excel_clean($partesFiltro[1]) : '';
            if ($valor !== '') {
                fputcsv($out, array($etiqueta, $valor), ';');
            } else {
                fputcsv($out, array(rp_excel_clean($filtro)), ';');
            }
        }
        fputcsv($out, array(), ';');

        $rows = rp_build_excel_rows($pedidos, $tipoInforme, $connV, $usaIdManual);
        foreach ($rows as $row) {
            $cleanRow = array_map('rp_excel_clean', $row);
            fputcsv($out, $cleanRow, ';');
        }
        fclose($out);
        exit;
    }

    if ($formato === 'pdf') {
        if ($tipoInforme === 'detallado') {
            $statsPdf = rp_pdf_detalle_estadisticas($pedidos);
            if ((int)$statsPdf['items'] > RP_PDF_DETALLE_MAX_ITEMS) {
                header('Content-Type: text/html; charset=UTF-8');
                echo rp_pdf_detalle_limite_html($statsPdf, RP_PDF_DETALLE_MAX_ITEMS);
                exit;
            }
        }

        require_once __DIR__ . '/_lib/mpdf2/vendor/autoload.php';
        $nombreArchivo = 'listado_pedidos_' . date('YmdHis') . '.pdf';

        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '0');
        set_time_limit(0);
        ini_set('pcre.backtrack_limit', '5000000');
        ini_set('pcre.recursion_limit', '500000');
        ob_start();

        $mpdf = new \Mpdf\Mpdf(array(
            'mode' => 'utf-8',
            'format' => 'A3-L',
            'margin_top' => 45,
            'margin_header' => 8,
            'compress' => true,
            'packTableData' => true,
            'simpleTables' => true,
            'useSubstitutions' => false,
            'useActiveForms' => false
        ));

        $mpdf->backupSubsFont = array();

        $nombreEmpresa = isset($_SESSION['nombre_empresa']) ? $_SESSION['nombre_empresa'] : 'administraNET';
        $direccionEmpresa = isset($_SESSION['domicilio_empresa']) ? $_SESSION['domicilio_empresa'] : '';
        $telefonoEmpresa = isset($_SESSION['telefono_empresa']) ? $_SESSION['telefono_empresa'] : '';
        $cuitEmpresa = isset($_SESSION['cuit_empresa']) ? $_SESSION['cuit_empresa'] : '';
        $logo = '_img/logo_' . $cuitEmpresa . '.jpg';

        $header = '';
        $header .= '<div style="font-family:Arial, sans-serif;border-bottom:1px solid #d7deeb;padding:0 0 8px 0;">';
        $header .= '<table width="100%" cellpadding="0" cellspacing="0">';
        $header .= '<tr>';
        $header .= '<td width="18%" style="vertical-align:middle;"><img src="' . $logo . '" style="width:110px;"></td>';
        $header .= '<td width="57%" align="center" style="vertical-align:middle;">';
        $header .= '<div style="font-weight:bold;font-size:16pt;color:#111827;">' . rp_h($nombreEmpresa) . '</div>';
        $header .= '<div style="font-size:8pt;color:#64748b;">' . rp_h(trim($direccionEmpresa . ' | ' . $telefonoEmpresa, ' |')) . '</div>';
        $header .= '</td>';
        $header .= '<td width="25%" align="right" style="vertical-align:middle;">';
        $header .= '<div style="font-size:13pt;font-weight:bold;color:#2a3e72;">Listado de Pedidos</div>';
        $header .= '<div style="font-size:7.5pt;color:#64748b;margin-top:4px;">Emision: ' . date('d/m/Y H:i') . '</div>';
        $header .= '</td>';
        $header .= '</tr>';
        $header .= '</table>';
        $header .= '</div>';

        $css = 'body,table,td,th,div,span{font-family:Arial, sans-serif;} table{border-collapse:collapse;font-size:10px;}th{background:#efefef;}th,td{border:1px solid #999;padding:4px;} .dt-right{text-align:right;}';
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLFooter('<div style="font-size:9px;text-align:right;">Pagina {PAGENO} de {nbpg}</div>');
        $mpdf->shrink_tables_to_fit = 1;

        session_write_close();
        $mpdf->WriteHTML($css, 1);

        $filtrosPdf = rp_build_filtros_export($_POST);
        $htmlFiltros = '<div style="font-size:8.5pt;margin-bottom:10px;padding:6px 0 8px 0;border-bottom:1px solid #e2e8f0;">';
        $htmlFiltros .= '<table width="100%" cellpadding="2" cellspacing="0" style="border-collapse:collapse;">';
        $columnas = 3;
        $chunks = array_chunk($filtrosPdf, $columnas);
        foreach ($chunks as $filaFiltros) {
            $htmlFiltros .= '<tr>';
            for ($i = 0; $i < $columnas; $i++) {
                if (isset($filaFiltros[$i])) {
                    $partesFiltro = explode(': ', $filaFiltros[$i], 2);
                    $tituloFiltro = isset($partesFiltro[0]) ? $partesFiltro[0] : '';
                    $valorFiltro = isset($partesFiltro[1]) ? $partesFiltro[1] : '';
                    $htmlFiltros .= '<td width="33%" style="padding:2px 8px 2px 0;vertical-align:top;color:#475569;">';
                    $htmlFiltros .= rp_h($tituloFiltro) . ': <strong style="color:#111827;">' . rp_h($valorFiltro) . '</strong>';
                    $htmlFiltros .= '</td>';
                } else {
                    $htmlFiltros .= '<td width="33%">&nbsp;</td>';
                }
            }
            $htmlFiltros .= '</tr>';
        }
        $htmlFiltros .= '</table>';
        $htmlFiltros .= '</div>';
        $mpdf->WriteHTML($htmlFiltros, 2);

        if ($tipoInforme === 'detallado') {
            $codigosMovimiento = array();
            foreach ($pedidos as $ped) {
                $codigosMovimiento[] = (int)$ped->CodigoMovimiento;
            }
            $detallePorMovimiento = rp_get_detalle_pedidos_map($connV, $codigosMovimiento, $usaIdManual);

            if (empty($pedidos)) {
                $mpdf->WriteHTML('<div style="padding:10px;border:1px solid #ccc;">Sin resultados para exportar.</div>', 2);
            } else {
                $pedidosPorPagina = 8;
                $totalPedidos = count($pedidos);

                for ($offset = 0; $offset < $totalPedidos; $offset += $pedidosPorPagina) {
                    $pedidosChunk = array_slice($pedidos, $offset, $pedidosPorPagina);
                    $chunkHtml = rp_build_pdf_detalle_por_pedido_html($pedidosChunk, $detallePorMovimiento, $usaIdManual);
                    rp_mpdf_write_html_chunked($mpdf, $chunkHtml, 14000);

                    if (($offset + $pedidosPorPagina) < $totalPedidos) {
                        $mpdf->AddPage();
                    }
                }
            }
        } else {
            $rowsPdf = rp_build_excel_rows($pedidos, $tipoInforme, $connV, $usaIdManual);
            if (empty($rowsPdf)) {
                $mpdf->WriteHTML('<div style="padding:10px;border:1px solid #ccc;">Sin resultados para exportar.</div>', 2);
            } else {
                $headersPdf = array_shift($rowsPdf);
                $chunkSize = 140;
                $totalRows = count($rowsPdf);

                if ($totalRows === 0) {
                    $mpdf->WriteHTML(rp_build_pdf_table_chunk_html($headersPdf, array()), 2);
                } else {
                    for ($offset = 0; $offset < $totalRows; $offset += $chunkSize) {
                        $rowsChunk = array_slice($rowsPdf, $offset, $chunkSize);
                        $chunkHtml = rp_build_pdf_table_chunk_html($headersPdf, $rowsChunk);
                        rp_mpdf_write_html_chunked($mpdf, $chunkHtml, 12000);

                        if (($offset + $chunkSize) < $totalRows) {
                            $mpdf->AddPage();
                        }
                    }
                }
            }
        }

        if (ob_get_length()) {
            ob_clean();
        }
        $mpdf->Output($nombreArchivo, 'D');
        exit;
    }
}


if(isset($_POST['ajax'])){
    $tipoInforme = rp_tipo_informe($_POST);
    list($consulta, $deQuien) = rp_build_condiciones($_POST, $connV, $objVendedor);
    $pedidos = rp_get_pedidos($connV, $consulta, $deQuien);
    echo rp_render_tabla_pedidos($pedidos, $usaIdManual, $tipoInforme, $connV, false);
    exit;
}

if (isset($_POST['ajaxDetallePedido']) && $_POST['ajaxDetallePedido'] == '1') {
    $codigoMovimiento = isset($_POST['codMovPedido']) ? (int)$_POST['codMovPedido'] : 0;
    if ($codigoMovimiento <= 0) {
        echo '<div class="detalle-pedido-vacio">Movimiento invalido.</div>';
        exit;
    }

    echo rp_render_detalle_html(rp_get_detalle_pedido($connV, $codigoMovimiento, $usaIdManual), $usaIdManual);
    exit;
}

// anulacion de pedido.
if(isset($_POST['anularPedido'])&& $_POST['anularPedido']==1){
    $codMovPedido = $_POST['codMovPedido'];
    $errores = 0;
    $erroTexto = "";
    $sqlTotal = "SET AUTOCOMMIT =0;";
    $resultado = mysqli_query($connV,$sqlTotal);
    if(!$resultado){
        $errores++;
        $erroTexto .= 'No puedo iniciar autocommit '.mysqli_error($connV).PHP_EOL;
        $vuelta = array('msg'=>'error','error'=>$erroTexto);
        header('Content-type: application/json');
        print json_encode($vuelta);
        exit();
    }

    $sqlTotal = "BEGIN;";
    $resultado = mysqli_query($connV,$sqlTotal);

    if(!$resultado){
        $errores++;
        $erroTexto .= 'No puedo hacer Begin '.mysqli_error($connV).PHP_EOL;
        $vuelta = array('msg'=>'error','error'=>$erroTexto);
        header('Content-type: application/json');
        print json_encode($vuelta);
        exit();
    }

     //iniciamos la anulacion del pedidos.

     // comp_ped
    $sqlTotal="UPDATE comp_ped SET comp_ped.Anulado='Si' WHERE comp_ped.CodigoMovimiento='".$codMovPedido."'";
    $resultado = mysqli_query($connV,$sqlTotal);
    if(!$resultado){
        $errores++;
        $erroTexto .= 'No puedo anular el comped. '.mysqli_error($connV).PHP_EOL;
    }

    // stockp

    $sqlTotal="UPDATE stockp SET stockp.Anulado='Si' WHERE stockp.CodigoMovimiento='".$codMovPedido."'";
    $resultado = mysqli_query($connV,$sqlTotal);
    if(!$resultado){
        $errores++;
        $erroTexto .= 'No puedo anular el stockp. '.mysqli_error($connV).PHP_EOL;
    }

    // percep_cli
    $sqlTotal="UPDATE percep_cli SET percep_cli.Anulado='Si' WHERE percep_cli.codigo_movimiento='".$codMovPedido."'";
    $resultado = mysqli_query($connV,$sqlTotal);
    if(!$resultado){
        $errores++;
        $erroTexto .= 'No puedo anular el percep_cli. '.mysqli_error($connV).PHP_EOL;
    }
        
    if($errores == 0 ){
        $sqlTotal= "COMMIT;";
        $resultado = mysqli_query($connV,$sqlTotal);
        //echo "todo bien";
        $vuelta = array('msg'=>'ok','error'=>$erroTexto);
    }else{
        $sqlTotal = "ROLLBACK;";
        $resultado = mysqli_query($connV,$sqlTotal);
        $vuelta = array('msg'=>'error','error'=>$erroTexto);
        //echo "todo mal";
    }

    header('Content-type: application/json');
    print json_encode($vuelta);
}