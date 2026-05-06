<?php
// ajax/json_pantalla_pedidos.php
require_once '../sesion.inc.php';
header('Content-Type: application/json');

// Si se pide la lista de sucursales
if (isset($_GET['sucursales'])) {
    $sucursales = array();
    $sqlSuc = "SELECT 
                id_sucursal, nombre_sucursal, domicilio_sucursal 
                FROM sucursales";
    $resSuc = mysqli_query($connV, $sqlSuc);
    if (!$resSuc) {
        echo json_encode(["error" => "Error en la consulta de sucursales: " . mysqli_error($connV)]);
        exit;
    }
    if ($resSuc) {
        while ($row = mysqli_fetch_assoc($resSuc)) {
            $sucursales[] = $row;
        }
        mysqli_free_result($resSuc);
    }
    echo json_encode(["sucursales" => $sucursales]);
    exit;
}

// --- CONEXIÓN A LA BASE DE DATOS ---
// Reemplaza los valores de conexión según tu entorno
// Conexión a la base de datos usando mysqli procedural
// $conexion = mysqli_connect('localhost', 'usuario', 'contraseña', 'nombre_base_datos');
// if (!$conexion) {
//     echo json_encode(["error" => "Error de conexión a la base de datos"]);
//     exit;
// }
$conexion = $connV;
// 1. Obtener Pedidos "En preparación"
// Consulta los pedidos con estado 'En preparación'
// Filtro por sucursal si se recibe
$filtroSucursal = '';
if (isset($_GET['cod_sucursal']) && is_numeric($_GET['cod_sucursal'])) {
    $codSucursal = intval($_GET['cod_sucursal']);
    $filtroSucursal = " AND pedido.CodSucursal = $codSucursal ";
}

$en_preparacion = array();
$sqlEnPreparacion = "SELECT
                        pedido.Fecha,
                        pedido.fecha_hora_fin_preparacion,
                        pedido.fecha_control,
                        pedido.CodigoMovimiento,
                        pedido.NroComprobante AS nroPedido,
                        CONCAT(usuario_prepara.apellido_usuario,' ',  usuario_prepara.nombre_usuario, ' (',usuario_prepara.cod_usuario,')') AS persona
                    FROM
                        comp_ped AS pedido
                    LEFT JOIN usuarios AS usuario_prepara ON usuario_prepara.id_usuario = pedido.id_usuario_preparacion    
                    WHERE 
                        pedido.Estado ='En preparacion' $filtroSucursal
                    ORDER BY pedido.fecha_hora_fin_preparacion ASC";
$resPrep = mysqli_query($conexion, $sqlEnPreparacion);
if ($resPrep) {
    while ($row = mysqli_fetch_assoc($resPrep)) {
        // $en_preparacion[] = array("comprobante" => $row['nroPedido']);
                                
        $en_preparacion[] = array(
            "comprobante" => $row['nroPedido'],
            "usuario" => $row['persona']
        );
    }
    mysqli_free_result($resPrep);
}

// 2. Obtener Pedidos "Preparado" (con usuario)
// Consulta los pedidos con estado 'Preparado' y su usuario
$preparado = array();
$sqlPreparado = " SELECT
                    pedido.Fecha,
                    pedido.fecha_control,
                    pedido.fecha_hora_fin_preparacion,
                    pedido.CodigoMovimiento,
                    pedido.NroComprobante AS nroPedido,
                    CONCAT(usuario_prepara.apellido_usuario,' ',  usuario_prepara.nombre_usuario, ' (',usuario_prepara.cod_usuario,')') AS persona
                FROM
                    comp_ped AS pedido
                LEFT JOIN usuarios AS usuario_prepara ON usuario_prepara.id_usuario = pedido.id_usuario_preparacion
                WHERE            
                    pedido.Estado ='Preparado' $filtroSucursal
                ORDER BY pedido.fecha_hora_fin_preparacion ASC;";
$resPreparado = mysqli_query($conexion, $sqlPreparado);
if ($resPreparado) {
    while ($row = mysqli_fetch_assoc($resPreparado)) {
        // $preparado[] = array(
        //     "comprobante" => $row['nroPedido'],
        //     "usuario" => $row['persona']
        // );
        $preparado[] = array("comprobante" => $row['nroPedido']);
    }
    mysqli_free_result($resPreparado);
}

// 3. Obtener Pedidos "En Remito"
// Consulta los pedidos con estado 'En remito'

$en_remito = array();
$sqlEnRemito = "SELECT
                    pedido.Fecha,
                    pedido.fecha_hora_fin_preparacion,
                    pedido.fecha_control,
                    pedido.NroComprobante AS nroPedido,
                    pedido.CodigoMovimiento,
                    remito.NroComprobante AS nroRemito,
                    CONCAT(usuario_prepara.apellido_usuario,' ',  usuario_prepara.nombre_usuario, ' (',usuario_prepara.cod_usuario,')') AS persona
                FROM
                    comp_ped AS pedido
                LEFT JOIN rem_ped ON rem_ped.codmov_pedido= pedido.CodigoMovimiento
                LEFT JOIN comp_ped AS remito ON remito.CodigoMovimiento = rem_ped.codmov_remito
                LEFT JOIN usuarios AS usuario_prepara ON usuario_prepara.id_usuario = pedido.id_usuario_preparacion
                WHERE
					rem_ped.Anulado='No'
                    AND  pedido.Estado ='En remito' $filtroSucursal
                    AND remito.Estado='Pendiente'
                ORDER BY remito.fecha_control ASC ;";
$resRemito = mysqli_query($conexion, $sqlEnRemito);
if ($resRemito) {
    while ($row = mysqli_fetch_assoc($resRemito)) {
        
        $en_remito[] = array(
            "comprobante" => $row['nroPedido'],
            "usuario" => $row['persona']
        );
    }
    mysqli_free_result($resRemito);
}

// Cierra la conexión
mysqli_close($conexion);

// Creamos la respuesta final con las claves actualizadas
$respuesta = [
    "en_preparacion" => $en_preparacion,
    "preparado" => $preparado,
    "en_remito" => $en_remito
];




// Enviamos la respuesta en formato JSON
echo json_encode($respuesta);

// --- CÓDIGO ANTERIOR SIMULADO (comentado para referencia) ---
/*
// --- SIMULACIÓN DE CONSULTAS A BASE DE DATOS ---
// vamos a hacer consultas  con la bae de datos a la tabla comp_ped

$sqlEnPreparacion = "SELECT * FROM comp_ped WHERE estado = 'En preparación'";
$sqlPreparado = "SELECT * FROM comp_ped WHERE estado = 'Preparado'";    
$sqlEnRemito = "SELECT * FROM comp_ped WHERE estado = 'En remito'";
// 1. Obtener Pedidos "En preparación"
$hacerEnPreparacion = true; // Simulamos que se hace la consulta
$en_preparacion = [];
$cantidad_preparacion = rand(5, 12);
for ($i = 0; $i < $cantidad_preparacion; $i++) {
    $en_preparacion[] = 'P-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
}

// 2. Obtener Pedidos "Preparado" (con usuario)
$usuarios_ejemplo = ['Ana S.', 'Juan P.', 'Luis M.', 'Sara G.', 'Carlos V.', 'Laura R.'];
$preparado = [];
$cantidad_preparados = rand(6, 15);
for ($i = 0; $i < $cantidad_preparados; $i++) {
    $preparado[] = [
        "comprobante" => 'P-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
        "usuario" => $usuarios_ejemplo[array_rand($usuarios_ejemplo)]
    ];
}

// 3. Obtener Pedidos "En Remito"
$en_remito = [];
$cantidad_en_remito = rand(3, 10);
for ($i = 0; $i < $cantidad_en_remito; $i++) {
    $en_remito[] = 'R-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
}
*/