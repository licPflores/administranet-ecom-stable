<?php

// Endpoint para obtener depósitos en formato JSON
if (isset($_POST['obtenerDepositos']) && $_POST['obtenerDepositos'] == 1) {
    require_once 'sesion.inc.php';
    $depositos = array();
    $idDepositoUsuario = $_SESSION['deposito'];
    $idUSuario =  $_SESSION['idusuario'];
    $seleccionDeposito = $_SESSION["seleccion_deposito_inventario"];
    $where = "";

    if ($seleccionDeposito == 'Seleccionado') {
        $where .= "AND depu.id_deposito = " . $idDepositoUsuario;
    }
    $sqlDepositos = "SELECT
                        dep.CodDeposito AS id_deposito,
                        dep.NombreDeposito AS nombre,    
                        IF(dep.CodDeposito='" . $idDepositoUsuario . "','Si','No') AS defecto
                    FROM
                        deposito_usr AS depu
                    LEFT JOIN deposito AS dep ON dep.CodDeposito = depu.id_deposito
                    WHERE
                        depu.id_usuario = " . $idUSuario . "
                        " . $where . "
                        AND dep.anulado = 'No'    ";
    // En PHP 5.6, $connV ya está definido por sesion.inc.php
    $resDepositos = mysqli_query($connV, $sqlDepositos) or die(mysqli_error($connV));
    while ($dep = mysqli_fetch_assoc($resDepositos)) {
        $depositos[] = $dep;
    }
    echo json_encode(['depositos' => $depositos]);
    exit;
}

// Constante para activar/desactivar logging
define('LOG_ACTIVO', true); // true para activar, false para desactivar

// Función de log
function log_accion($mensaje) {
    if (!LOG_ACTIVO) return;
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $dir = __DIR__ . '/log';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $archivo = $dir . '/log_' . date('Y-m-d-h-i') . '.txt';
    $fecha = date('Y-m-d H:i:s');
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'CLI';
    // Si el archivo no existe, escribir la fecha de creación
    if (!file_exists($archivo)) {
        $creacion = "# Log creado el: $fecha zona_horaria: America/Argentina/Buenos_Aires\n";
        file_put_contents($archivo, $creacion, FILE_APPEND);
    }
    $linea = "[$fecha][$ip] $mensaje\n";
    file_put_contents($archivo, $linea, FILE_APPEND);
}

// Configuración: tipo de obtención de disponible ('directo' o 'calculado')
define('TIPODISPONIBLE', 'calculado'); // 'directo' o 'calculado'

require_once 'sesion.inc.php';

header('Content-Type: application/json');

// -- ACCIONES DE AUTOCOMPLETADO Y FILTRADO --
 log_accion("POST : " . json_encode($_POST));
// autocompletar articulos
if (isset($_POST['autocomplete']) && $_POST['autocomplete'] === 1) {
    $term = isset($_POST['term']) ? trim($_POST['term']) : '';


    if ($term === '') {
        echo json_encode(array());
        exit;
    }

    log_accion("Accion: Autocomplete | Term: $term");
    $result = buscarArticulosAutocomplete($connV, $term);
    log_accion("Resultado Autocomplete: " . json_encode($result));
    echo json_encode($result);
    exit;
}


// buscar stock de articulos con paginacion server side
if (isset($_POST['buscarStock']) && $_POST['buscarStock'] == 1) {

    $term = isset($_POST['term']) ? trim($_POST['term']) : '';
    $deposito = isset($_POST['deposito']) ? $_POST['deposito'] : '';

    // if ($term === '') {
    //     echo json_encode(array());
    //     exit;
    // }

    log_accion("Accion: BuscarStock | Term: $term | Deposito: $deposito");
    $result = buscarStockArticulos($connV, $term, $deposito);
    log_accion("Resultado BuscarStock: " . json_encode($result));
    echo json_encode($result);
    exit; 
}

if(isset($_POST['buscarStockSinPaginacion']) && $_POST['buscarStockSinPaginacion'] == 1) {

    $term = isset($_POST['term']) ? trim($_POST['term']) : '';
    $deposito = isset($_POST['deposito']) ? $_POST['deposito'] : '';
    $stockCero = isset($_POST['stockCero']) ? $_POST['stockCero'] : '';
    $orden = isset($_POST['orden']) ? $_POST['orden'] : 'nombre';

    // if ($term === '') {
    //     echo json_encode(array());
    //     exit;
    // }
    $arrPametros = array(
        'term' => $term,
        'deposito' => $deposito,
        'stockCero' => $stockCero,
        'orden' => $orden
    );
    log_accion("Accion: BuscarStockSinPaginacion | Term: $term | Deposito: $deposito");
    // $result = buscarStockArticulos($connV, $term, $deposito);
    $result = buscarStockArticulosSinPaginacion($connV, $arrPametros);
    log_accion("Resultado BuscarStockSinPaginacion: " . json_encode($result));
    echo json_encode($result);
    exit; 
}

// funcion para buscar stock de articulos 


// --- FUNCIONES ---
function buscarArticulosAutocomplete($conn, $term)
{
    $usaIdManual = $_SESSION['usa_id_manual'];

    $where = " a.activo='Si' AND (a.codigo LIKE ? OR a.nombre LIKE ?)";

    $sql = "SELECT a.id_articulo, a.codigo, a.nombre, a.id_manual
            FROM articulo AS a
            LEFT JOIN stock AS  s ON s.id_articulo = a.id_articulo
            WHERE a.activo='Si' AND (a.codigo LIKE ? OR a.nombre LIKE ? OR a.id_manual LIKE ?)";
    log_accion("SQL Autocomplete: $sql | Params: $term");
    $params = array("%$term%", "%$term%", "%$term%");
    
    $sql .= " GROUP BY a.id_articulo ORDER BY a.nombre LIMIT 15";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        // Compatibilidad PHP 5.6: bind_param requiere referencias
        $types = str_repeat('s', count($params));
        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array(array($stmt, 'bind_param'), $bind_names);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $result = array();
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $result[] = array(
                    'id' => $row['id_articulo'],
                    'label' => $row['codigo'] . ' - ' . $row['nombre'],
                    'value' => $row['codigo']
                );
            }
        }
        mysqli_stmt_close($stmt);
        return $result;
    } else {
        return array();
    }
}

function buscarStockArticulos($connV, $term, $deposito)
{
    // Parámetros de DataTables
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 20;
    $search = isset($_POST['busqueda']) ? trim($_POST['busqueda']) : '';
    $deposito = isset($_POST['deposito']) ? $_POST['deposito'] : 'todos';
    $usaIdManual = $_SESSION['usa_id_manual'];

    // 1. Obtener depósitos permitidos para el usuario (aquí puedes filtrar según permisos)
    $depositos = array();
    $sqlDepositos = "SELECT 
                        deposito.CodDeposito AS id_deposito, 
                        deposito.NombreDeposito AS nombre 
                    FROM deposito 
                    WHERE 
                        deposito.Anulado='No' 
                    ORDER BY deposito.NombreDeposito ASC";
    log_accion("SQL Depositos: $sqlDepositos");
    $resDepositos = mysqli_query($connV, $sqlDepositos);
    while ($dep = mysqli_fetch_assoc($resDepositos)) {
        $depositos[$dep['id_deposito']] = $dep['nombre'];
    }

    // 2. Armar consulta principal de productos
    $where = "WHERE a.Discontinuo='No'
            AND a.disponible_vta='Si'
            AND a.tipo_art='Articulo' ";
    if ($search !== '') {
        $search = mysqli_real_escape_string($connV, $search);
        // if($usaIdManual=='Si'){

        // }
        // if($usaIdManual=='No') {
        //     $where .= " AND (a.nombrere LIKE '%$search%' OR a.codigo LIKE '%$search%' OR a.id_manual LIKE '%$search%')";
        // } else {
        //     $where .= " AND (a.nombre LIKE '%$search%' OR a.codigo LIKE '%$search%')";
        // }

        $where .= " AND (a.nombre LIKE '%$search%' OR a.codigo LIKE '%$search%' OR a.id_manual LIKE '%$search%')";
    }

    $sqlCount = "SELECT COUNT(*) as total FROM articulo a $where";
    log_accion("SQL Count: $sqlCount");
    $resCount = mysqli_query($connV, $sqlCount);
    $totalReg = ($row = mysqli_fetch_assoc($resCount)) ? intval($row['total']) : 0;

    $sql = "SELECT 
                    a.IDArt AS id_articulo, 
                    a.id_manual AS id_manual, 
                    a.NombreArticulo as nombre  
            FROM 
                articulo AS a 
            $where 
            LIMIT $start, $length";
    log_accion("SQL Articulos: $sql");
    $res = mysqli_query($connV, $sql);

    $data = array();
    $articulos_ids = array();
    $articulos_rows = array();
    while ($row = mysqli_fetch_assoc($res)) {
        $id_articulo = $row['id_articulo'];
        $articulos_ids[] = $id_articulo;
        $articulos_rows[$id_articulo] = $row;
    }

    // Obtener stock y disponible para todos los artículos y depósitos
    $stocks = array();
    $disponibles = array();
    if (!empty($articulos_ids)) {
        $inIds = implode(",", array_map('intval', $articulos_ids));
        $whereDep = ($deposito === 'todos') ? '' : "AND sd.id_deposito='" . mysqli_real_escape_string($connV, $deposito) . "'";
        $sqlStockDisp = "SELECT sd.id_articulo, sd.id_deposito, sd.saldo AS stock, sd.saldo_pedido_cliente AS disponible 
                            FROM stock_deposito AS sd 
                            WHERE sd.id_articulo IN ($inIds) $whereDep";
        log_accion("SQL StockDisp: $sqlStockDisp");
        $resSD = mysqli_query($connV, $sqlStockDisp);
        if (!$resSD) {
            log_accion("Error en SQL StockDisp: " . mysqli_error($connV));
            return array('error' => 'Error al obtener stock de artículos');
        }
        while ($r = mysqli_fetch_assoc($resSD)) {
            $stocks[$r['id_articulo']][$r['id_deposito']] = floatval($r['stock']);
            $disponibles[$r['id_articulo']][$r['id_deposito']] = floatval($r['disponible']);
        }
    }

    // Si el disponible es calculado, obtener los pendientes de todos los artículos y depósitos
    $pendientes = array();
    if (TIPODISPONIBLE === 'calculado' && !empty($articulos_ids)) {
        $whereDep = ($deposito === 'todos') ? '' : "AND stockp.CodDeposito='" . mysqli_real_escape_string($connV, $deposito) . "'";
        $sqlPend = "SELECT stockp.IDArt as id_articulo, stockp.CodDeposito as id_deposito, SUM(stockp.salida) AS saldo_pedido_cliente
                    FROM stockp
                    LEFT JOIN comp_ped ON (stockp.CodigoMovimiento = comp_ped.CodigoMovimiento)
                    WHERE stockp.IDArt IN ($inIds)
                        $whereDep
                        AND (comp_ped.Estado = 'Pendiente' OR comp_ped.Estado = 'En Preparación')
                        AND comp_ped.Anulado = 'No'
                        AND comp_ped.TipoComprobante = 'PED'
                    GROUP BY stockp.IDArt, stockp.CodDeposito";
        log_accion("SQL Pendientes: $sqlPend");
        $resPend = mysqli_query($connV, $sqlPend);
        while ($p = mysqli_fetch_assoc($resPend)) {
            $pendientes[$p['id_articulo']][$p['id_deposito']] = floatval($p['saldo_pedido_cliente']);
        }
    }

    // Armar la respuesta
    foreach ($articulos_rows as $id_articulo => $row) {
        $stock = array();
        $disponible = array();
        if ($deposito === 'todos') {
            foreach ($depositos as $id_dep => $nombre_dep) {
                $stockVal = isset($stocks[$id_articulo][$id_dep]) ? $stocks[$id_articulo][$id_dep] : 0;
                if (TIPODISPONIBLE === 'calculado') {
                    $pend = isset($pendientes[$id_articulo][$id_dep]) ? $pendientes[$id_articulo][$id_dep] : 0;
                    $dispVal = max(0, $stockVal - $pend);
                } else {
                    $dispVal = isset($disponibles[$id_articulo][$id_dep]) ? $disponibles[$id_articulo][$id_dep] : 0;
                }
                $stock[$nombre_dep] = $stockVal;
                $disponible[$nombre_dep] = $dispVal;
            }
        } else {
            $id_dep = $deposito;
            $stockVal = isset($stocks[$id_articulo][$id_dep]) ? $stocks[$id_articulo][$id_dep] : 0;
            if (TIPODISPONIBLE === 'calculado') {
                $pend = isset($pendientes[$id_articulo][$id_dep]) ? $pendientes[$id_articulo][$id_dep] : 0;
                $dispVal = max(0, $stockVal - $pend);
            } else {
                $dispVal = isset($disponibles[$id_articulo][$id_dep]) ? $disponibles[$id_articulo][$id_dep] : 0;
            }
            $stock = $stockVal;
            $disponible = $dispVal;
        }
        $data[] = array(
            'codigo' => isset($row['id_articulo']) ? $row['id_articulo'] : '',
            'nombre' => $row['nombre'],
            'id_manual' => $row['id_manual'],
            'stock' => $stock,
            'disponible' => $disponible
        );
    }

    // Respuesta para DataTables
    $response = array(
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
        'recordsTotal' => $totalReg,
        'recordsFiltered' => $totalReg,
        'data' => $data
    );
    return($response);
}

function buscarStockArticulosSinPaginacion($connV, $params)
{
    // Parámetros de DataTables
    $search = isset($params['term']) ? trim($params['term']) : '';
    $deposito = isset($params['deposito']) ? $params['deposito'] : 'todos';
    $stockCero = isset($params['stockCero']) ? $params['stockCero'] : '';
    $orden = isset($params['orden']) ? $params['orden'] : 'nombre';

    
    
    $usaIdManual = $_SESSION['usa_id_manual'];

    // 1. Obtener depósitos permitidos para el usuario (aquí puedes filtrar según permisos)
    $depositos = array();
    $sqlDepositos = "SELECT 
                        deposito.CodDeposito AS id_deposito, 
                        deposito.NombreDeposito AS nombre 
                    FROM deposito 
                    WHERE 
                        deposito.Anulado='No' 
                    ORDER BY deposito.NombreDeposito ASC";
    log_accion("SQL Depositos: $sqlDepositos");
    $resDepositos = mysqli_query($connV, $sqlDepositos);
    while ($dep = mysqli_fetch_assoc($resDepositos)) {
        $depositos[$dep['id_deposito']] = $dep['nombre'];
    }

    // 2. Armar consulta principal de productos
    $where = "WHERE a.Discontinuo='No'
            AND a.disponible_vta='Si'
            AND a.tipo_art='Articulo' ";

    
    if ($search !== '') {
        $search = mysqli_real_escape_string($connV, $search);
        // if($usaIdManual=='Si'){

        // }
        // if($usaIdManual=='No') {
        //     $where .= " AND (a.nombrere LIKE '%$search%' OR a.codigo LIKE '%$search%' OR a.id_manual LIKE '%$search%')";
        // } else {
        //     $where .= " AND (a.nombre LIKE '%$search%' OR a.codigo LIKE '%$search%')";
        // }

        $where .= " AND (a.nombre LIKE '%$search%' OR a.codigo LIKE '%$search%' OR a.id_manual LIKE '%$search%')";
    }

    $sqlCount = "SELECT COUNT(*) as total FROM articulo a $where";
    log_accion("SQL Count: $sqlCount");
    $resCount = mysqli_query($connV, $sqlCount);
    $totalReg = ($row = mysqli_fetch_assoc($resCount)) ? intval($row['total']) : 0;

    $sql = "SELECT 
                    a.IDArt AS id_articulo, 
                    a.id_manual AS id_manual, 
                    a.NombreArticulo as nombre  
            FROM 
                articulo AS a 
            $where 
            ORDER BY $orden ASC";
    log_accion("SQL Articulos: $sql");
    $res = mysqli_query($connV, $sql);

    $data = array();
    $articulos_ids = array();
    $articulos_rows = array();
    while ($row = mysqli_fetch_assoc($res)) {
        $id_articulo = $row['id_articulo'];
        $articulos_ids[] = $id_articulo;
        $articulos_rows[$id_articulo] = $row;
    }

    // Obtener stock y disponible para todos los artículos y depósitos
    $stocks = array();
    $disponibles = array();
    if (!empty($articulos_ids)) {
        $inIds = implode(",", array_map('intval', $articulos_ids));
        $whereDep = ($deposito === 'todos') ? '' : "AND sd.id_deposito='" . mysqli_real_escape_string($connV, $deposito) . "'";
        $sqlStockDisp = "SELECT sd.id_articulo, sd.id_deposito, sd.saldo AS stock, sd.saldo_pedido_cliente AS disponible 
                            FROM stock_deposito AS sd 
                            WHERE sd.id_articulo IN ($inIds) $whereDep";
        log_accion("SQL StockDisp: $sqlStockDisp");
        $resSD = mysqli_query($connV, $sqlStockDisp);
        if (!$resSD) {
            log_accion("Error en SQL StockDisp: " . mysqli_error($connV));
            return array('error' => 'Error al obtener stock de artículos');
        }
        while ($r = mysqli_fetch_assoc($resSD)) {
            $stocks[$r['id_articulo']][$r['id_deposito']] = floatval($r['stock']);
            $disponibles[$r['id_articulo']][$r['id_deposito']] = floatval($r['disponible']);
        }
    }

    // Si el disponible es calculado, obtener los pendientes de todos los artículos y depósitos
    $pendientes = array();
    if (TIPODISPONIBLE === 'calculado' && !empty($articulos_ids)) {
        $whereDep = ($deposito === 'todos') ? '' : "AND stockp.CodDeposito='" . mysqli_real_escape_string($connV, $deposito) . "'";
        $sqlPend = "SELECT stockp.IDArt as id_articulo, stockp.CodDeposito as id_deposito, SUM(stockp.salida) AS saldo_pedido_cliente
                    FROM stockp
                    LEFT JOIN comp_ped ON (stockp.CodigoMovimiento = comp_ped.CodigoMovimiento)
                    WHERE stockp.IDArt IN ($inIds)
                        $whereDep
                        AND (comp_ped.Estado = 'Pendiente' OR comp_ped.Estado = 'En Preparación')
                        AND comp_ped.Anulado = 'No'
                        AND comp_ped.TipoComprobante = 'PED'
                    GROUP BY stockp.IDArt, stockp.CodDeposito";
        log_accion("SQL Pendientes: $sqlPend");
        $resPend = mysqli_query($connV, $sqlPend);
        while ($p = mysqli_fetch_assoc($resPend)) {
            $pendientes[$p['id_articulo']][$p['id_deposito']] = floatval($p['saldo_pedido_cliente']);
        }
    }

    // Armar la respuesta
    foreach ($articulos_rows as $id_articulo => $row) {
        $item = array(
            'codigo' => isset($row['id_articulo']) ? $row['id_articulo'] : '',
            'nombre' => $row['nombre'],
            'id_manual' => $row['id_manual']
        );
        $totalStock = 0;
        if ($deposito === 'todos') {
            foreach ($depositos as $id_dep => $nombre_dep) {
                $stockVal = isset($stocks[$id_articulo][$id_dep]) ? $stocks[$id_articulo][$id_dep] : 0;
                if (TIPODISPONIBLE === 'calculado') {
                    $pend = isset($pendientes[$id_articulo][$id_dep]) ? $pendientes[$id_articulo][$id_dep] : 0;
                    $dispVal = max(0, $stockVal - $pend);
                } else {
                    $dispVal = isset($disponibles[$id_articulo][$id_dep]) ? $disponibles[$id_articulo][$id_dep] : 0;
                }
                $item['stock_' . $id_dep] = $stockVal;
                $item['disp_' . $id_dep] = $dispVal;
                $totalStock += $stockVal;
            }
        } else {
            $id_dep = $deposito;
            $stockVal = isset($stocks[$id_articulo][$id_dep]) ? $stocks[$id_articulo][$id_dep] : 0;
            if (TIPODISPONIBLE === 'calculado') {
                $pend = isset($pendientes[$id_articulo][$id_dep]) ? $pendientes[$id_articulo][$id_dep] : 0;
                $dispVal = max(0, $stockVal - $pend);
            } else {
                $dispVal = isset($disponibles[$id_articulo][$id_dep]) ? $disponibles[$id_articulo][$id_dep] : 0;
            }
            $item['stock_' . $id_dep] = $stockVal;
            $item['disp_' . $id_dep] = $dispVal;
            $totalStock += $stockVal;
        }
        // si filro por stock en cero y el stock es 0, no lo agrego
        if ($stockCero === 'no' && $totalStock <= 0) {
            continue; // salto este articulo
        }
        $data[] = $item;
    }


    // Respuesta para DataTables
    $response = array(
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
        'recordsTotal' => $totalReg,
        'recordsFiltered' => $totalReg,
        'data' => $data
    );
    return($response);
}