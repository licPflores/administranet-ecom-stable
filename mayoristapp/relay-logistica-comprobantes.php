<?php
// # gestion de comprbantes por ruta con mysql y phpp 5.6
require_once 'sesion.inc.php';


// * FUNCIONES

// # listado de comprboantes
function listadoComprobantes($parametros,$conexion) {
    // Obtener la lista de comprobantes para la ruta especificada usare mysqli  
    $filtros = "";
    /*
        fechaDesde: fechaDesde,
        fechaHasta: fechaHasta,
        numeroComp: numeroComp,
        estado: estadoPedido,
        idCliente: clienteId,
        idRuta: rutaId
    */
    // Filtros de busqueda
    if (!empty($parametros['fechaDesde']) && !empty($parametros['fechaHasta'])) {
        $fechaDesde = mysqli_real_escape_string($conexion, $parametros['fechaDesde']);
        $fechaHasta = mysqli_real_escape_string($conexion, $parametros['fechaHasta']);
        $filtros .= " AND remito.Fecha BETWEEN '$fechaDesde' AND '$fechaHasta'";
    }
    // numero de comproba te
    if (isset($parametros['numeroComp']) && $parametros['numeroComp'] != '') {
        $filtros .= " AND remito.NroComprobante LIKE '%" . mysqli_real_escape_string($conexion, $parametros['numeroComp']) . "%'";
    }
    // estado el pedido , entregado='Si' o entregado='No' si estado en 'sinDatos'  es entregado='No' y id_usuario_no_entrega = null
    if (isset($parametros['estado']) && $parametros['estado'] != '') {
        if($parametros['estado'] == 'SinDatos'){
            $filtros .= " AND remito.entregado = 'No' AND remito.id_usuario_no_entrega IS NULL";    
        }else{
            // si el estado es entregado o no entregado
            if($parametros['estado'] == 'Si'){
                $filtros .= " AND remito.entregado = 'Si'";
            }
            
            if($parametros['estado'] == 'No'){
                $filtros .= " AND remito.entregado = 'No' AND NOT ISNULL(remito.id_usuario_no_entrega)";
            }
            
        }
    }

    // idCliente
    if (isset($parametros['idCliente']) && $parametros['idCliente'] != '') {
        $filtros .= " AND cliente.Codigo = '" . mysqli_real_escape_string($conexion, $parametros['idCliente']) . "'";
    }
    // idRuta
    if (isset($parametros['idRuta']) && $parametros['idRuta'] != '') {
        $filtros .= " AND hoja_ruta.id_ruta = '" . mysqli_real_escape_string($conexion, $parametros['idRuta']) . "'";
    }

    // clientes
    // estado ruta
    // entregado
    // traigo los remito de los usua rios que tienen la ruta asignada
    $sql="SELECT   
                remito.Fecha AS fechaRemito,
                DATE_FORMAT(remito.Fecha,'%d/%m/%Y') AS FechaRemitoB,
                remito.NroComprobante AS nroRemito,
                remito.CodigoMovimiento AS codMovRemito,
                pedido.NroComprobante AS nroPedido,
                pedido.CodigoMovimiento AS codMovPedido,
                factura.NroComprobante AS nroFactura,
                factura.CodigoMovimiento AS codMovFactura,
                remito.entregado,
                remito.id_usuario_no_entrega,
                remito.motivo_no_entrega,
                remito.fecha_hora_entrega,
                DATE_FORMAT(remito.fecha_hora_entrega,'%d/%m/%Y %H:%i') AS fechaHoraEntregaB,
                CONCAT(cliente.nombre_cliente,' (',cliente.Codigo,')') AS cliente,
                chofer.nombre_chofer,
                CONCAT(usuario_entrega.apellido_usuario , ' ', usuario_entrega.nombre_usuario) AS nombreUsuarioEntrega,
                #chofer_entrega.nombre_chofer AS nombreChoferEntrega,
                (remito.SubTotalDesc+remito.IVA1+
                            remito.IVA2) AS totalRemito 
          FROM
          rem_ped 
          LEFT JOIN comp_ped AS remito ON remito.CodigoMovimiento = rem_ped.codmov_remito
          LEFT JOIN comp_ped AS pedido ON pedido.CodigoMovimiento = rem_ped.codmov_pedido
          LEFT JOIN rem_fact ON rem_fact.CodigoMovimientoR = rem_ped.codmov_remito
          LEFT JOIN cuentacliente AS factura ON factura.CodigoMovimiento = rem_fact.CodigoMovimientoF
          LEFT JOIN cliente_datos_adicionales AS ped_ruta ON ped_ruta.CodigoMovimiento = pedido.CodigoMovimiento
          LEFT JOIN logi_hoja_ruta AS hoja_ruta  ON hoja_ruta.id_ruta = ped_ruta.id_ruta
          LEFT JOIN cliente ON cliente.Codigo = remito.Codigo
          LEFT JOIN logi_ruta_chofer AS ruta_chofer ON ruta_chofer.id_ruta = hoja_ruta.id_ruta 
          LEFT JOIN logi_abm_chofer AS chofer ON chofer.id_chofer= ruta_chofer.id_chofer  
          LEFT JOIN usuarios AS usuario_prepara ON usuario_prepara.id_usuario = remito.id_usuario_preparacion
          LEFT JOIN usuarios AS usuario_entrega ON usuario_entrega.id_usuario =remito.id_usuario_no_entrega
          #LEFT JOIN logi_abm_chofer AS chofer_entrega ON chofer_entrega.id_usuario = usuario_entrega.id_usuario
          
          WHERE    
          	rem_ped.Anulado='No'
          	AND remito.Anulado='No'    
            AND chofer.id_usuario = '".$_SESSION['idusuario']."'     
          	#AND remito.entregado='No' 
            ".$filtros."
            ORDER BY remito.fecha DESC LIMIT 30";
           // guardar en archivo el sql tipo log
             $comprobantes = array();
           $hacer = mysqli_query($conexion,$sql);
           file_put_contents('log/log_sql_'.date('Y-m-d_H-i-s').'.txt', date('Y-m-d H:i:s')." ".$sql."\n", FILE_APPEND);
           if(!$hacer){
               // echo "Error en la consulta: " . mysqli_error($conexion)." sql:".$sql;
                file_put_contents('log/log_sql_'.date('Y-m-d_H-i-s').'.txt', date('Y-m-d H:i:s')." ".$sql."\n", FILE_APPEND);
                return $comprobantes;
            }
          
            while($row = mysqli_fetch_assoc($hacer)){
                $comprobantes[] = $row;
            }
            mysqli_free_result($hacer);
            return $comprobantes; 
}

// armo tabla html de comprobantes para destktop
function armoTablaComprobantesDesktop($comprobantes){
    $html = "<table>";
    $html .= "<thead>";
    $html .= "<tr>";
    // $html .= "  <th>#</th>";
    $html .= "  <th>Fecha</th>";
    $html .= "  <th>Comp.</th>";
    $html .= "  <th>Cliente</th>";
    // $html .= "  <th>Trazab.</th>";         
    $html .= "  <th>Total</th>";
    $html .= "  <th>Estado</th>";
    $html .= "  <th>&nbsp</th>";
    $html .= "</tr>";
    $html .= "</thead>";
    $html .= "<tbody>";
    
    foreach ($comprobantes as $comprobante) {
        $muestroEdicion = 0;
        $claseEntregado = '';
        $html .= "<tr>";
        // $html .= "  <td></td>";
        $html .=    "<td class='dt-nowrap'>{$comprobante['FechaRemitoB']}</td>";
        $html .=    "<td>";
        $html .=        $comprobante['nroRemito'];
       
        $html .="   </td>";
         $html .=    "<td>{$comprobante['cliente']}</td>";
        // $html .=    "<td>";
        // if($comprobante['nroFactura'] != ''){
        //     $html .="    <span>Fact: {$comprobante['nroFactura']}</span>";
        // }
        // if($comprobante['nroPedido'] != ''){
        //     $html .=    "<span>Ped: {$comprobante['nroPedido']}</span>";
        // }
        
        // $html .=    "</td>";
                
       
        $html .=    "<td>{$comprobante['totalRemito']}</td>";
        
        $entregadoTexto = '';
        // estado dif(e la entrega si esta entregado o no pero si no esta ver si tiene deatos si no debe decir sin datos entregaa
        if($comprobante['entregado'] == 'No'){
            if($comprobante['id_usuario_no_entrega'] == null){
                $entregadoTexto .="Sin datos";
            }else{
                $muestroEdicion++;
                $claseEntregado = 'class="estado-entregado-no"';
                $entregadoTexto .="<strong>No entregado</strong><br>";
                $entregadoTexto .="<i class='fas fa-user'></i>:{$comprobante['nombreUsuarioEntrega']}";

            }
        }

        if($comprobante['entregado'] == 'Si'){
            $muestroEdicion++;
            $claseEntregado = 'class="estado-entregado-si"';
            $entregadoTexto .="<strong>Entregado </strong><br>";
            $entregadoTexto .="<i class='far fa-calendar-check'></i>:{$comprobante['fechaHoraEntregaB']}";
            
        }
        $html .=    "<td ".$claseEntregado.">";
        $html  .= $entregadoTexto;    
        $html .="</td>";
        $html .=    "<td>";
        // $html .="<button onclick=\"abrirModal('{$comprobante['codMovRemito']}','{$comprobante['codMovPedido']}','{$comprobante['nroRemito']}')\">Actualizar</button>";
        // $html .="<button onclick=\"abrirVerMas('{$comprobante['codMovRemito']}')\">Ver Mas</button>";
        if($muestroEdicion == 0){
            $html .="<span class=\"acciones\">";
            $html .="   <a href=\"javascript:void(0);\" onclick=\"abrirModal(this)\" "; 
                $html .=" data-codmov-remito='".$comprobante['codMovRemito']."' "; 
                $html .=" data-codmov-pedido='".$comprobante['codMovPedido']."' "; 
                $html .=" data-nro-remito='".$comprobante['nroRemito']."' ";
                $html .=" data-fecha-remito='".$comprobante['FechaRemitoB']."' ";
                $html .=" data-cliente='".$comprobante['cliente']."' ";
                $html .=" data-total-remito='$".number_format($comprobante['totalRemito'],2,',','.')."' ";
            $html .=" >";
            $html .="   <i class=\"fas barrita fas fa-edit fa-2x\"></i> ";
            $html .="</a></span>";
        }
        
        $html .="<span class=\"acciones\"><a href=\"javascript:void(0);\" onclick=\"abrirVerMas('{$comprobante['codMovRemito']}')\"> <i class=\"fas barrita fas fa-ellipsis-h fa-2x\"></i></a></span>";
        $html .=    "</td>";
        $html .="</tr>";
    }

    $html .= "</tbody>";
    $html .= "</table>";

    return $html;
}



// armo el html para la tabla comprobantes movil 
function armoTablaComprobantes($comprobantes){
    $html = "<table>";
    $html .= "<thead>";
    $html .= "<tr>";
    // $html .= "  <th>#</th>";
    $html .= "  <th>Fecha</th>";
    $html .= "  <th>Comprobante</th>";
    // $html .= "  <th>Cliente</th>";
    // $html .= "  <th>Trazab.</th>";         
    // $html .= "  <th>Total</th>";
    $html .= "  <th class='dt-center'>Estado</th>";
    $html .= "  <th>&nbsp</th>";
    $html .= "</tr>";
    $html .= "</thead>";
    $html .= "<tbody>";
    if(count($comprobantes) == 0){
        $html .= "<tr><td colspan='4'>No hay comprobantes para mostrar</td></tr>";
        $html .= "</tbody>";
        $html .= "</table>";
        return $html;
    }
    
    foreach ($comprobantes as $comprobante) {
        $muestroEdicion = 0;
        $claseEntregado = '';
        $html .= "<tr>";
        // $html .= "  <td></td>";
        $html .=    "<td class='dt-nowrap'>{$comprobante['FechaRemitoB']}</td>";
        $html .=    "<td>";
        $html .=     "<div style='white-space: nowrap;margin-bottom:2px;'><strong>REM: " . $comprobante['nroRemito']."</strong></div>";
        $html .="       Cliente: ".$comprobante['cliente'];
        $html .="       <br>Total: <strong>$".number_format($comprobante['totalRemito'],2,',','.')."</strong>";
        $html .="   </td>";
        //  $html .=    "<td>{$comprobante['cliente']}</td>";
        // $html .=    "<td>";
        // if($comprobante['nroFactura'] != ''){
        //     $html .="    <span>Fact: {$comprobante['nroFactura']}</span>";
        // }
        // if($comprobante['nroPedido'] != ''){
        //     $html .=    "<span>Ped: {$comprobante['nroPedido']}</span>";
        // }
        
        // $html .=    "</td>";
                
       
        // $html .=    "<td>{$comprobante['totalRemito']}</td>";
        
        $entregadoTexto = '';
        // estado dif(e la entrega si esta entregado o no pero si no esta ver si tiene deatos si no debe decir sin datos entregaa
        if($comprobante['entregado'] == 'No'){
            if($comprobante['id_usuario_no_entrega'] == null){
                $entregadoTexto .="Sin datos";
            }else{
                $muestroEdicion++;
                $claseEntregado = 'class="estado-entregado-no"';
                $entregadoTexto .="<div ".$claseEntregado.">No entregado</div>";
                // agregar el motivos de no entreaga
                // $entregadoTexto .=" {$comprobante['motivo_no_entrega']}<br><br>";
                // $entregadoTexto .="<i class='fas fa-user'></i> {$comprobante['nombreUsuarioEntrega']}";

            }
        }

        if($comprobante['entregado'] == 'Si'){
            $muestroEdicion++;
            $claseEntregado = 'class="estado-entregado-si"';
            $entregadoTexto .="<div ".$claseEntregado.">Entregado</div>";
            // $entregadoTexto .=" {$comprobante['fechaHoraEntregaB']}";
            
        }
        // $html .=    "<td ".$claseEntregado.">";
        $html .=    "<td>";
        $html  .= $entregadoTexto;    
        $html .="</td>";
        $html .=    "<td>";
        // $html .="<button onclick=\"abrirModal('{$comprobante['codMovRemito']}','{$comprobante['codMovPedido']}','{$comprobante['nroRemito']}')\">Actualizar</button>";
        // $html .="<button onclick=\"abrirVerMas('{$comprobante['codMovRemito']}')\">Ver Mas</button>";
        if($muestroEdicion == 0){
            $html .="<span class=\"acciones\">";
            $html .="   <a href=\"javascript:void(0);\" onclick=\"abrirModal(this)\" "; 
                $html .=" data-codmov-remito='".$comprobante['codMovRemito']."' "; 
                $html .=" data-codmov-pedido='".$comprobante['codMovPedido']."' "; 
                $html .=" data-nro-remito='".$comprobante['nroRemito']."' ";
                $html .=" data-fecha-remito='".$comprobante['FechaRemitoB']."' ";
                $html .=" data-cliente='".$comprobante['cliente']."' ";
                $html .=" data-total-remito='$".number_format($comprobante['totalRemito'],2,',','.')."' ";
            $html .=" >";
            $html .="   <i class=\"fas barrita fas fa-edit fa-2x\"></i> ";
            $html .="</a></span>";
        }
        
        $html .="<span class=\"acciones\">"; 
        $html .="   <a href=\"javascript:void(0);\"  ";
                $html .=" data-codmov-remito='".$comprobante['codMovRemito']."' "; 
                $html .=" data-codmov-pedido='".$comprobante['codMovPedido']."' "; 
                $html .=" data-nro-remito='".$comprobante['nroRemito']."' ";
                $html .=" data-fecha-remito='".$comprobante['FechaRemitoB']."' ";
                $html .=" data-cliente='".$comprobante['cliente']."' ";
                $html .=" data-total-remito='$".number_format($comprobante['totalRemito'],2,',','.')."' ";
        $html.=" onclick=\"abrirVerMas(this)\">";
        $html .="       <i class=\"fas barrita fas fa-ellipsis-h fa-2x\"></i> ";
        $html .="   </a>"; 
        $html .="</span>";
        $html .="</td>";
        $html .="</tr>";
    }

    $html .= "</tbody>";
    $html .= "</table>";

    return $html;
}


// listado de rutas
function listadoRutas($parametros, $conexion) {
    $search = isset($parametros['q']) ? mysqli_real_escape_string($conexion, $parametros['q']) : '';
    $sql = "SELECT 
                hoja_ruta.id_ruta AS id, 
                CONCAT(hoja_ruta.desc_ruta, ' | ', hoja_ruta.estado_ruta) AS text
            FROM 
                logi_hoja_ruta AS hoja_ruta
            WHERE 
                hoja_ruta.anulado = 'No'
                AND (hoja_ruta.desc_ruta LIKE '%$search%' OR hoja_ruta.estado_ruta LIKE '%$search%')
            ORDER BY hoja_ruta.id_ruta DESC 
            LIMIT 100";
    $hacer = mysqli_query($conexion, $sql);
    if (!$hacer) {
        echo json_encode(['success' => false, 'error' => 'Error en la consulta: ' . mysqli_error($conexion)]);
        exit;
    }
    $rutas = [];
    while ($row = mysqli_fetch_assoc($hacer)) {
        $rutas[] = $row;
    }
    mysqli_free_result($hacer);
    return $rutas;
}

// listado de clientes
function listadoClientes($parametros, $conexion) {
    $search = isset($parametros['q']) ? mysqli_real_escape_string($conexion, $parametros['q']) : '';
    $sql = "SELECT 
                cliente.Codigo AS id, 
                cliente.nombre_cliente AS text
            FROM cliente 
            WHERE cliente.Estado = 'Activo'
                AND cliente.nombre_cliente LIKE '%$search%'
            ORDER BY cliente.nombre_cliente ASC 
            LIMIT 100";
    $hacer = mysqli_query($conexion, $sql);
    if (!$hacer) {
        echo json_encode(['success' => false, 'error' => 'Error en la consulta: ' . mysqli_error($conexion)]);
        exit;
    }
    $clientes = [];
    while ($row = mysqli_fetch_assoc($hacer)) {
        $clientes[] = $row;
    }
    mysqli_free_result($hacer);
    return $clientes;
}

// listado de choferes
function listadoChoferes($parametros, $conexion) {
    $search = isset($parametros['q']) ? mysqli_real_escape_string($conexion, $parametros['q']) : '';
    $filtro="";
    if($search !== 'todos'){
        $filtro .= " AND (chofer.nombre_chofer LIKE '%$search%' OR usuario.apellido_usuario LIKE '%$search%' OR usuario.nombre_usuario LIKE '%$search%')";
    }
    $sql = "SELECT 
                CONCAT(chofer.id_chofer, '|', chofer.id_usuario) AS id, 
                CONCAT(chofer.nombre_chofer, ' - ', usuario.apellido_usuario, ' ', usuario.nombre_usuario) AS text
            FROM logi_abm_chofer AS chofer
            LEFT JOIN usuarios AS usuario ON usuario.id_usuario = chofer.id_usuario
            WHERE 
                chofer.anulado = 'No'
                ".$filtro."               
            ORDER BY chofer.nombre_chofer ASC 
            LIMIT 100";
    $hacer = mysqli_query($conexion, $sql);
    if (!$hacer) {
        echo json_encode(['success' => false, 'error' => 'Error en la consulta: ' . mysqli_error($conexion)]);
        exit;
    }
    $choferes = [];
    while ($row = mysqli_fetch_assoc($hacer)) {
        $choferes[] = $row;
    }
    mysqli_free_result($hacer);
    return $choferes;
}

function guardarDatosEntrega($parametros, $conexion) {
    // Guardar los datos de entrega en la base de datos
    // Verificar si el remito ya tiene datos de entrega
    // Si no tiene datos de entrega guardar los datos de entrega
    // Si tiene datos de entrega actualizar los datos de entrega
    /*
    [fechaHoraEntrega] => 
    [selectMotivo] => Error de facturación
    [selectChoferModal] => 4|5
    [detalleNoEntrega] => me cobraron en dolares
    [codigoMovimientoRemito] => 805968
    [codigoMovimientoPedido] => 805967
    [guardarDatosEntrega] => 1
    [idChofer] => 
    [idUsuario] =>
    */
    $codMovRemito = $parametros['codigoMovimientoRemito'];
    $codMovPedido = $parametros['codigoMovimientoPedido'];
// campo fecha hora entrega mysql
    //$fechaHoraEntrega = $parametros['fechaHoraEntrega']; 
    $fechaHoraEntrega   = date('Y-m-d H:i:s');
    $entregado= $parametros['selectEntregado'];    
    //$idUsuarioNoEntrega = $parametros['idUsuario'];
    // id usuario de la sesion
    $idUsuarioNoEntrega = $_SESSION['idusuario'];

    $motivoNoEntrega = $parametros['selectMotivo'];
    $detalleNoEntrega = $parametros['detalleNoEntrega'];
    $error =0;
    // autocommit con mysqli
    mysqli_autocommit($conexion, false);
    // begin transaction
    mysqli_begin_transaction($conexion);
    if($entregado == 'Si'){
        $sql = "UPDATE comp_ped  SET 
                    fecha_hora_entrega ='".$fechaHoraEntrega."',                
                    entregado = 'Si'
                WHERE 
                comp_ped.CodigoMovimiento = '".$codMovRemito."'";
        $sqlPedido = "UPDATE comp_ped  SET 
                    fecha_hora_entrega ='".$fechaHoraEntrega."',                
                    entregado = 'Si'
                WHERE 
                comp_ped.CodigoMovimiento = '".$codMovPedido."'";        
    }

    if($entregado == 'No'){
        $sql = "UPDATE comp_ped  SET                 
                    id_usuario_no_entrega = '".$idUsuarioNoEntrega."',
                    motivo_no_entrega = '".$motivoNoEntrega."',
                    detalle_no_entrega = '".$detalleNoEntrega."'
                WHERE
                comp_ped.CodigoMovimiento = '".$codMovRemito."'";
        $sqlPedido = "UPDATE comp_ped  SET
                    id_usuario_no_entrega = '".$idUsuarioNoEntrega."',
                    motivo_no_entrega = '".$motivoNoEntrega."',
                    detalle_no_entrega = '".$detalleNoEntrega."'
                WHERE 
                comp_ped.CodigoMovimiento = '".$codMovPedido."'";        
        
    }

    
    $hacer = mysqli_query($conexion, $sql); 
    if(!$hacer){
        $error = 1;
        $errorMsg = "sql:".$sql ."error: ".mysqli_error($conexion);
    }
    
    $hacer = mysqli_query($conexion, $sqlPedido);     
    if(!$hacer){
        $error = 1;
        $errorMsg = "sql:".$sql ."error: ".mysqli_error($conexion);
    }
    if($error == 0){
        mysqli_commit($conexion);
        echo json_encode(array('msg'=>'ok', 'mensaje' => 'Datos guardados correctamente'));
        exit;
    }else{  
        mysqli_rollback($conexion);
        echo json_encode(array('msg'=>'error', 'error' => $errorMsg));
        exit;
    }
}

// funcion que buscar infomracion del remito mas data
function obtenerInfoRemito($parametros,$conexion){
    $codMovRemito = $parametros['codMovRemito'];
    $sql = "SELECT 
                remito.Fecha AS fechaRemito,
                DATE_FORMAT(remito.Fecha,'%d/%m/%Y') AS FechaRemitoB,
                remito.NroComprobante AS nroRemito,
                remito.CodigoMovimiento AS codMovRemito,
                pedido.NroComprobante AS nroPedido,
                pedido.CodigoMovimiento AS codMovPedido,
                DATE_FORMAT(pedido.Fecha,'%d/%m/%Y') AS FechaPedidoB,
                factura.NroComprobante AS nroFactura,
                factura.CodigoMovimiento AS codMovFactura,
                DATE_FORMAT(factura.Fecha,'%d/%m/%Y') AS FechaFacturaB,
                remito.entregado,
                remito.id_usuario_no_entrega,
                remito.motivo_no_entrega,
                remito.detalle_no_entrega,
                remito.fecha_hora_entrega,
                DATE_FORMAT(remito.fecha_hora_entrega,'%d/%m/%Y %H:%i') AS fechaHoraEntregaB,
                CONCAT(cliente.nombre_cliente,' (',cliente.Codigo,')') AS cliente,
                chofer.nombre_chofer,
                CONCAT(usuario_entrega.apellido_usuario , ' ', usuario_entrega.nombre_usuario) AS nombreUsuarioNoEntrega,
                
                hoja_ruta.desc_ruta,    
                hoja_ruta.estado_ruta,
                (remito.SubTotalDesc+remito.IVA1+
                            remito.IVA2) AS totalRemito,
                (pedido.SubTotalDesc+pedido.IVA1+
                            pedido.IVA2) AS totalPedido,
                 (factura.SubTotalDesc+factura.IVA1+
                            factura.IVA2) AS totalFactura                        
          FROM  
          rem_ped 
          LEFT JOIN comp_ped AS remito ON remito.CodigoMovimiento = rem_ped.codmov_remito
          LEFT JOIN comp_ped AS pedido ON pedido.CodigoMovimiento = rem_ped.codmov_pedido
          LEFT JOIN rem_fact ON rem_fact.CodigoMovimientoR = rem_ped.codmov_remito
          LEFT JOIN cuentacliente AS factura ON factura.CodigoMovimiento = rem_fact.CodigoMovimientoF
          LEFT JOIN cliente_datos_adicionales AS ped_ruta ON ped_ruta.CodigoMovimiento = pedido.CodigoMovimiento
          LEFT JOIN logi_hoja_ruta AS hoja_ruta  ON hoja_ruta.id_ruta = ped_ruta.id_ruta
          LEFT JOIN cliente ON cliente.Codigo = remito.Codigo
          LEFT JOIN logi_ruta_chofer AS ruta_chofer ON ruta_chofer.id_ruta = hoja_ruta.id_ruta 
          LEFT JOIN logi_abm_chofer AS chofer ON chofer.id_chofer= ruta_chofer.id_chofer  
          LEFT JOIN usuarios AS usuario_prepara ON usuario_prepara.id_usuario = remito.id_usuario_preparacion
            LEFT JOIN usuarios AS usuario_entrega ON usuario_entrega.id_usuario =remito.id_usuario_no_entrega       
            LEFT JOIN logi_abm_chofer AS chofer_entrega ON chofer_entrega.id_usuario = usuario_entrega.id_usuario
            WHERE
                rem_ped.Anulado='No'
                AND remito.Anulado='No'          
                AND remito.CodigoMovimiento = '".$codMovRemito."'";
    $hacer = mysqli_query($conexion,$sql);  
    if(!$hacer){
        echo "Error en la consulta: " . mysqli_error($conexion);
        return false;
    }

    
    $comprobante = mysqli_fetch_assoc($hacer);
    
    mysqli_free_result($hacer); 

    if(count($comprobante) == 0){
        return false;
    }
    
    return $comprobante;
}






// * # Acciones
// * ------------------------------------------------------

// listado de comprobantes
if(isset($_GET['listarComprobantes'])&&$_GET['listarComprobantes'] == 1){
    $parametros = $_GET;
    
    $comprobantes = listadoComprobantes($parametros,$connV);

    header('Content-Type: application/json');
    // echo "que son los comprobantes::",var_dump($comprobantes);    
    // if($comprobantes == false){
    //     echo "dentro del false";
    //     echo json_encode(array('msg'=>'error', 'error' => 'Error al obtener los comprobantes'));
    //     exit;
    // }

    if(count($comprobantes) == 0){

        
        $html = armoTablaComprobantes($comprobantes);
       
        echo json_encode(array('msg'=>'vacio', 'html' => $html));
        exit;
    }

    // if(count($comprobantes) > 0){
        $html = armoTablaComprobantes($comprobantes);
       
        echo json_encode(array('msg'=>'ok', 'html' => $html));
        exit;
    // }
    

}


// obtener listado de rutas
if (isset($_GET['action']) && $_GET['action'] == 'obtenerHojasRuta') {
    $parametros = $_GET;
    $rutas = listadoRutas($parametros, $connV);
    header('Content-Type: application/json');
    echo json_encode(array('results' => $rutas));
    exit;
}

// obtener listado de clientes
if (isset($_GET['action']) && $_GET['action'] == 'obtenerClientes') {
    $parametros = $_GET;
    $clientes = listadoClientes($parametros, $connV);
    header('Content-Type: application/json');
    echo json_encode(array('results' => $clientes));
    exit;
}

// obtener listado de choferes
if (isset($_GET['action']) && $_GET['action'] == 'obtenerChoferes') {
    $parametros = $_GET;
    $choferes = listadoChoferes($parametros, $connV);
    header('Content-Type: application/json');
    echo json_encode(array('results' => $choferes));
    exit;
}
// obtener listado de choferes completo
if(isset($_GET['action']) && $_GET['action'] == 'obtenerChoferesTodos') {
    $parametros = array('q' => 'todos');
    $choferes = listadoChoferes($parametros, $connV);
    header('Content-Type: application/json');
    echo json_encode(array('results' => $choferes));
    exit;
}

// guardar datos de enrega
if (isset($_POST['guardarDatosEntrega']) && $_POST['guardarDatosEntrega'] == 1) {
    $parametros = $_POST;
    /*[selectEntregado] => No
    [fechaHoraEntrega] => 
    [selectMotivo] => Error de facturación
    [selectChoferModal] => 4|5
    [detalleNoEntrega] => me cobraron en dolares
    [codigoMovimientoRemito] => 805968
    [codigoMovimientoPedido] => 805967
    [guardarDatosEntrega] => 1
    [idChofer] => 
    [idUsuario] =>  */

    // inicializo datos del chocer
    $parametros['idChofer'] = null;
    $parametros['idUsuario'] = null;

    if($parametros['selectEntregado'] == 'No'){
       // $chofer = $parametros['selectChoferModal'];
        //$arrChofer = explode('|', $chofer);
        //$idChofer = $arrChofer[0];
        //$idUsuario = $arrChofer[1];
        //$parametros['idChofer'] = $idChofer;    
        //$parametros['idUsuario'] = $idUsuario;
    }
    
    // echo 'datos guardados<pre>',print_r($parametros),'</pre>';
    guardarDatosEntrega($parametros, $connV);

    // guardar los datos de entrega
    // verificar si el remito ya tiene datos de entrega
    // si no tiene datos de entrega guardar los datos de entrega
    // si tiene datos de entrega actualizar los datos de entrega
    

}

if(isset($_GET['obtenerInfo']) && $_GET['obtenerInfo'] == 1){
    $parametros = $_GET;
    $comprobante = obtenerInfoRemito($parametros,$connV);
    header('Content-Type: application/json');
    if($comprobante == false){
        echo json_encode(array('msg'=>'error', 'error' => 'Error al obtener los comprobantes'));
        exit;
    }
    echo json_encode(array('msg'=>'ok', 'data' => $comprobante));
    exit;
    
}