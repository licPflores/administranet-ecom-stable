<?php
require_once '../../sesion.inc.php';
//require_once '../../conexion-general.inc.php';
header("Content-type:application/json");
/**
 * Funciones
 * ========================================================================================
 */

/**
 * busca los clientes inscriptos en el programa de puntos.
 */
function listar_clientes_asociados($connV){
    $sqlClientes=  "SELECT
                        cliente.id_manual_cli,
                        cliente.Codigo,      
                        cliente.nombre_cliente,
                        scliente.saldo_premios,
                        scliente.id_sp_saldo,
                        IF(ISNULL(scliente.saldo_premios),0,scliente.saldo_premios) AS saldoTotal,                                        
                        CONCAT(cliente.id_manual_cli,' ',cliente.nombre_cliente,' [', cliente.Estado, '] puntos: ', IF(ISNULL(scliente.saldo_premios),0,scliente.saldo_premios)) AS ncliente
                    FROM 
                        cliente
                    LEFT JOIN sp_saldo_cliente_premios AS scliente ON cliente.Codigo=scliente.id_cliente 
                    WHERE
                        cliente.habilita_sp ='Si' 
                        AND NOT ISNULL(cliente.id_manual_cli)
                    ORDER BY cliente.nombre_cliente ASC ";
    $hcliente= mysqli_query($connV,$sqlClientes);
    $arrClientes = array();
    if(!$hcliente){
        $vuelta = array('msg'=>'error','desc'=>mysqli_error($connV));
    }
    if($hcliente){
        while($c=mysqli_fetch_assoc($hcliente)){
            $arrClientes[] =$c;
        }
        //file_put_contents('lista-clientes-puntos.json',json_encode($arrClientes));
        $vuelta = array('msg'=>'ok','data'=>$arrClientes);
        //$vuelta = array('msg'=>'ok');
    }   
    print json_encode($vuelta);             

}

/**
 * guarda la accion en la base, y deja un log con el movimiento.
 */
function alta_movimiento($connV,$datos){
    $errores=0;
    $descError='';
    /*
    [idUsuario] => 5
    [codUSuario] => javier
    [idCliente] => 23022
    [idSaldoCliente] => 
    [saldoAnte] => 0
    [puntos] => 33
    [saldoConsolidado] => 33
    [idClienteDestino] => 
    [idSaldoClienteDestino] => 
    [saldoClienteDestino] => 
    [accion] => colocar
    */
    $idCliente  = $datos['idCliente'];
    $idSaldoCliente  = $datos['idSaldoCliente'];
    $saldoCliente  = $datos['saldoAnte'];
    $idUsuario  = $datos['idUsuario'];
    $codUsuario = $datos['codUsuario'];
    $saldoConsolidado = $datos['saldoConsolidado'];
    $puntos = $datos['puntos'];
    $idClienteDestino = $datos['idClienteDestino'];
    $idSaldoClienteDestino = $datos['idSaldoClienteDestino'];
    $saldoClienteDestino = $datos['saldoClienteDestino'];
    $accion     = $datos['accion'];

    # segun accion agregar o colocar cambian el saldo.
    if($accion=='agregar'||$accion=='colocar'){

        #insertar nuevo movimiento en sp_movimiento_premio.
        mysqli_begin_transaction($connV);
        $sqlInsertarMov="INSERT INTO sp_movimiento_premios
                        SET    
                        id_cliente=".$idCliente.",
                        fecha='".date('Y/m/d')."',
                        tipo_comp='AJS',
                        nro_comp='".$accion."',
                        monto_neto=0,
                        monto_final=0,
                        puntos_acumulados='".$saldoConsolidado."',
                        valor_calculo_puntaje=1,
                        valor_cada_puntaje=1,
                        codigo_movimiento=NULL,
                        codigo_movimiento_anul=NULL;";
        $hacerIns=mysqli_query($connV,$sqlInsertarMov);
        
        if(!$hacerIns){
            $descError .=mysqli_error($connV).' sql:'.$sqlInsertarMov.PHP_EOL;
            $errores++;
        }

        #actualizar sp_saldo_cliente_premio.
        
        if($idSaldoCliente==''){
            # No tiene tiene id en sp_cliente_premio
            # insertar
            $sqlInsertaSaldo = "INSERT INTO sp_saldo_cliente_premios 
                                SET 
                                    id_cliente=".$idCliente.",
                                    saldo_premios=".$saldoConsolidado.";";                                     
            
            $hacerUpd=mysqli_query($connV,$sqlInsertaSaldo);
            # analizo resultado.
            if(!$hacerUpd){
                $errores++;
                $descError.=mysqli_error($connV).' sql:' .$sqlInsertaSaldo.PHP_EOL;
            }
        }else{
        
            # Tiene id en sp_cliente_premio
            # actualizar
        
            $sqlActualizaSaldo = "UPDATE sp_saldo_cliente_premios 
                                    SET 
                                    saldo_premios=".$saldoConsolidado." 
                                    WHERE 
                                    sp_saldo_cliente_premios.id_sp_saldo=".$idSaldoCliente;
            $hacerUpd=mysqli_query($connV,$sqlActualizaSaldo);
            # analizo resultado.
            if(!$hacerUpd){
                $errores++;
                $descError.=mysqli_error($connV).' sql:' .$sqlActualizaSaldo.PHP_EOL;
            }
            
        }

    

    }
    
    # transferencia de puntos de cliente origen a cliente destino
    if($accion=='transferir'){
        //echo '<pre>';
        //print_r($datos);
        //echo '</pre>';
        // insertar movimiento saca puntos origen
        #insertar nuevo movimiento en sp_movimiento_premio.
        mysqli_begin_transaction($connV);
        // movimiento AJR restar puntos
        $sqlInsertarMov="INSERT INTO sp_movimiento_premios
                        SET    
                        id_cliente=".$idCliente.",
                        fecha='".date('Y/m/d')."',
                        tipo_comp='AJR',
                        nro_comp='".$accion."',
                        monto_neto=0,
                        monto_final=0,
                        puntos_acumulados='".$saldoCliente."',
                        valor_calculo_puntaje=1,
                        valor_cada_puntaje=1,
                        codigo_movimiento=NULL,
                        codigo_movimiento_anul=NULL;";
        $hacerIns=mysqli_query($connV,$sqlInsertarMov);
        
        if(!$hacerIns){
            $descError .=mysqli_error($connV).' sql:'.$sqlInsertarMov.PHP_EOL;
            $errores++;
        }
        // insertar movimiento AJS suma puntos.
        $sqlInsertarMov="INSERT INTO sp_movimiento_premios
                        SET    
                        id_cliente=".$idClienteDestino.",
                        fecha='".date('Y/m/d')."',
                        tipo_comp='AJS',
                        nro_comp='".$accion."',
                        monto_neto=0,
                        monto_final=0,
                        puntos_acumulados='".$puntos."',
                        valor_calculo_puntaje=1,
                        valor_cada_puntaje=1,
                        codigo_movimiento=NULL,
                        codigo_movimiento_anul=NULL;";
        $hacerIns=mysqli_query($connV,$sqlInsertarMov);
        
        if(!$hacerIns){
            $descError .=mysqli_error($connV).' sql:'.$sqlInsertarMov.PHP_EOL;
            $errores++;
        }

        // si el cliente destino no tiene registro
        if($idSaldoClienteDestino==''){
            # No tiene tiene id en sp_cliente_premio
            # insertar
            $sqlInsertaSaldo = "INSERT INTO sp_saldo_cliente_premios 
                                SET 
                                    id_cliente=".$idClienteDestino.",
                                    saldo_premios=".$saldoConsolidado.";";                                         
            $hacerUpd=mysqli_query($connV,$sqlInsertaSaldo);
            # analizo resultado.
            if(!$hacerUpd){
                $errores++;
                $descError.=mysqli_error($connV).' sql:' .$sqlInsertaSaldo.PHP_EOL;
            }
        }else{
        
            # Tiene id en sp_cliente_premio
            # actualizar
        
            $sqlActualizaSaldo = "UPDATE sp_saldo_cliente_premios 
                                    SET 
                                    saldo_premios=".$saldoConsolidado." 
                                    WHERE 
                                    sp_saldo_cliente_premios.id_sp_saldo=".$idSaldoClienteDestino;
            $hacerUpd=mysqli_query($connV,$sqlActualizaSaldo);
            # analizo resultado.
            if(!$hacerUpd){
                $errores++;
                $descError.=mysqli_error($connV).' sql:' .$sqlActualizaSaldo.PHP_EOL;
            }
            
        }

        
        
        // actualizar a 0 sp_clientes_premio origen
        $sqlActualizaSaldo = "UPDATE sp_saldo_cliente_premios 
                                    SET 
                                    saldo_premios=0 
                                    WHERE 
                                    sp_saldo_cliente_premios.id_sp_saldo=".$idSaldoCliente;
        $hacerUpd=mysqli_query($connV,$sqlActualizaSaldo);
        if(!$hacerUpd){
            $errores++;
            $descError.=mysqli_error($connV).' sql:' .$sqlActualizaSaldo.PHP_EOL;
        }

    }
    
    if($errores==0){
        #todo bien se guardo.
        mysqli_commit($connV);
        if($accion=='transferir'){
            $lineaLog=date('Y-m-d-h-i-s').'|'.$idUsuario.'|'.$codUsuario.'|clienteOrigen: '.$idCliente.'|saldoOrigen: '.$saldoCliente.'|puntos: '.$puntos.'|saldoNuevoOrigen: 0|accion: '.$accion.'||'.PHP_EOL;
            $lineaLog .=date('Y-m-d-h-i-s').'|'.$idUsuario.'|'.$codUsuario.'|clienteDestino: '.$idClienteDestino.'|saldo: '.$saldoClienteDestino.'|puntos: '.$puntos.'|saldoNuevo: '.$saldoConsolidado.'|accion: '.$accion.'||'.PHP_EOL;
        }else{
            $lineaLog=date('Y-m-d-h-i-s').'|'.$idUsuario.'|'.$codUsuario.'|cliente: '.$idCliente.'|saldo: '.$saldoCliente.'|puntos: '.$puntos.'|saldoNuevo: '.$saldoConsolidado.'|accion: '.$accion.'||'.PHP_EOL;
        }
        $vuelta = array('msg'=>'ok');
    }
    if($errores!=0){
        mysqli_rollback($connV);
        #fallo sql
        $lineaLog=date('Y-m-d-h-i-s').'|'.$idUsuario.'|'.$codUsuario.'|cliente:'.$idCliente.'|error:'.$descError.'||'.PHP_EOL;
        $vuelta = array('msg'=>'error','desc'=>$descError);
    } 
    #guardar Log fisico porque no tengo en la base de datos.
    file_put_contents('log/puntoslog_'.date('Y-m-d-h-i-s'),$lineaLog,FILE_APPEND);
    print json_encode($vuelta);
}



/**
 * ACCIONES
 * ==================================================================================================
 */
//$connV=$conexionT;
/**
 * listar los clientes inicial con saldo
 */
if(isset($_REQUEST['accion'])&&$_REQUEST['accion']=='clientes'){
    listar_clientes_asociados($connV);
}


/**
 * guardar el movimiento.
 */

if(isset($_REQUEST['accion'])&&$_REQUEST['accion']=='alta'){
    /*
    [accion] => alta
    [motivo] => colocar
    [idCliente] => 23022
    [idSaldoCliente] => 
    [saldoCliente] => 0
    [puntos] => 33
    [idClienteDestino] => 
    [idSaldoClienteDestino] => 
    [saldoClienteDestino] => 
    [saldoConsolidado] => 33
     */
    $arrDatos=array(
        'idUsuario'=>$_SESSION['idusuario'],
        'codUsuario'=>$_SESSION['usuario'],
        'idCliente'=>$_REQUEST['idCliente'],        
        'idSaldoCliente'=>$_REQUEST['idSaldoCliente'],
        'saldoAnte'=>$_REQUEST['saldoCliente'],
        'puntos'=>$_REQUEST['puntos'],
        'saldoConsolidado'=>$_REQUEST['saldoConsolidado'],
        'idClienteDestino'=>$_REQUEST['idClienteDestino'],
        'idSaldoClienteDestino'=>$_REQUEST['idSaldoClienteDestino'],
        'saldoClienteDestino'=>$_REQUEST['saldoClienteDestino'],
        'accion'=>$_REQUEST['motivo']
    );
    alta_movimiento($connV,$arrDatos);
    //echo "<pre>";
    //print_r($_REQUEST);
    //print_r($arrDatos);
    //echo var_dump($arrDatos['idSaldoCliente']=='');
    //echo "</pre>";
}