<?php
require_once 'sesion.inc.php';
//echo "<pre>";
//   echo $_POST;
define('REGISTRARLOGFISICO',true);

//echo "</pre>";
function logError($mensaje)
{
    // guardar log de errores 
    //$urlLog ='../log/log_errores_'.date('Y-m-d_H').'.txt';
    $urlLog = 'log/log_clientes_' . date('Y-m-d_H_i') . '.txt';
    $mensajeLog = date('Y-m-d H:i:s') . ' ==> ' . $mensaje . PHP_EOL;
    
    if(defined('REGISTRARLOGFISICO')&&REGISTRARLOGFISICO==true){
        file_put_contents($urlLog, $mensajeLog, FILE_APPEND);
    }
}
// busca los domicilios adicionales de los clientes,
function buscarDomicilioAdicionales($conexion, $codigo)
{
    // busco domicilios para este cliente, si no tiene llamo a la rutina que lo arregla tuti y vuelvo a consultar.
    logError('entre a buscar mis domicilios buscardomicilio adicional ');
    $error = 0;
    $sqlDomicilios = "SELECT 
                        cm.id_cliente_domicilio AS idDom,
                        cm.Calle,
                        cm.NroCalle,
                        cm.Dpto,
                        pv.Provincia,
                        dp.NombreDepartamento,
                        dt.NombreDistrito,
                        z.nombre_zona,
                        z.id_zona
                    FROM cliente_domicilio AS cm
                    LEFT JOIN provincia  AS pv ON (pv.CodProvincia = cm.CodProvincia)
                    LEFT JOIN departamento AS dp ON (dp.IDDepartamento = cm.IDDepartamento)
                    LEFT JOIN distrito AS dt ON(dt.IDDistrito = cm.IDDistrito)
                    LEFT JOIN erp_zona AS z ON(z.id_zona = cm.id_zona)
                    WHERE cm.id_cliente = {$codigo}
                    AND cm.anulado ='No' ";
    $hacerDom = mysqli_query($conexion, $sqlDomicilios);
    $domEntrega = array();
    if(!$hacerDom){
        $error++;
        logError('No puedo recuperar los domicilios err:' . mysqli_error($conexion) . ' | sql: ' . $sqlDomicilios );
    }

    if($hacerDom){
        while ($dd = mysqli_fetch_assoc($hacerDom)) {
            $domEntrega[] = $dd;
        }
    }

    // analisis del error.
    if($error==0){
        // no tengo errores pero puedo tener 
        logError('sin error de consulta sql domicilios debo saber si hay domicilios ');
        if(empty($domEntrega)){
            logError('vacio domicilios de entrega');
            $estadoRutina =true; // sin errores.
            $estadoRutina=rutinaDomicilioAdicionalesCliente($conexion);
            // recursividad debo volver a ejecutar voy a consultar 
            if($estadoRutina){
                logError('se ejecuto oka rutina de clientes domicilios');

                $domEntrega=buscarDomicilioAdicionales($conexion,$codigo);
            }
            if(!$estadoRutina){
                logError('fallo rutina de clientes... no pudo buscar mas debo fallar ');

            }
        }

    }
    

    return $domEntrega;
}

// arregla los domicilios para todos
function rutinaDomicilioAdicionalesCliente($conexion)
{
    logError('ingreso rutina de domicilio clientes completo');
    $estado = true;
    $sqlClienteDomicilios = "INSERT INTO cliente_domicilio (`Calle`,
                        `NroCalle`,
                        `Dpto`,
                        `IDDistrito`,
                        `CodProvincia`,
                        `IDDepartamento`,
                        `id_zona`,
                        `id_cliente`)
                        SELECT cc.Calle,
                        cc.NroCalle,
                        cc.Dpto,
                        cc.IDDistrito,
                        cc.CodProvincia,
                        cc.IDDepartamento,
                        cc.id_zona,cc.Codigo  
                        FROM cliente AS cc 
                        LEFT JOIN cliente_domicilio ON cc.Codigo = cliente_domicilio.id_cliente
                        WHERE ISNULL(cliente_domicilio.id_cliente);";
    $hacer = mysqli_query($conexion, $sqlClienteDomicilios);
    if (!$hacer) {
        logError("fallo ejecucion de rutina cliente domicilios " . mysqli_error($conexion));
        $estado = false;
    }
    logError('exito ejecucion para todos es hora de volver a buscar.');

    return $estado;
}

// * manejador de cosas de clientes.

if (isset($_POST['codCliente'])) {
    $codigo = $_POST['codCliente'];
    //    $codigo = 936;
    logError('Ingresando a seleccion de cliente...');
    // consultar por si existe el campo de los premios.

    $sqlCampoPrem = "SHOW COLUMNS FROM cliente LIKE 'habilita_sp';";
    $hacerc = mysqli_query($connV, $sqlCampoPrem) or die("fallo el test de premios" . mysqli_error($connV));
    $premios = (mysqli_num_rows($hacerc)) ? TRUE : FALSE;
    $habilitaSp = "";
    $saldoPuntos = 0;
    if ($premios) {
        $habilitaSp = " habilita_sp AS usaPremio";
        // buscar el saldo si tiene
        $sqlPuntos = "SELECT                     
                        ROUND(saldo_premios,0) AS saldo_premios                    
                    FROM sp_saldo_cliente_premios                  
                    WHERE id_cliente = " . $codigo . " LIMIT 1;";
        $hacerP = mysqli_query($connV, $sqlPuntos) or die('no puedo consultar los puntos del cliente ' . mysqli_error($connV));
        $arrPuntos = mysqli_fetch_assoc($hacerP);
        if ($arrPuntos !== null) {
            $saldoPuntos = $arrPuntos["saldo_premios"];
        }
        $habilitaSp .= ",{$saldoPuntos} AS puntos ";
    }

    $sqlCliente = "SELECT 
                    cliente.nombre_cliente AS cliente,
                    cliente.id_cv,
                    cond_venta.Descripcion AS condVenta,
                    cliente.listaPrecio,
                    SUBSTRING(cliente.listaPrecio,6) AS codListaPrecio,
                    {$objVendedor->CodViajante} AS codViajante,
                    cliente.CodViajante AS CodViajanteCli,
                    cliente.Credito,
                    cliente.credito_limite_dias,
                    cliente.id_sucursal,
                    cliente.saldo,
                    cliente.Codigo,
                    cliente.id_manual_cli,
                    cliente.Email AS email,
                    cliente.EmailContacto As emailcontacto,
                    cliente.TipoCliente,
                    cliente.Descuento AS descPie,
                    cliente.descuento_por_cli AS descRenglon,
                    cond_venta.descuento AS descCondventa,
                    contribuyentes.IDIva,
                    cliente.CUIT,
                    cliente.id_pc,
                    contribuyentes.abreviado,
                    {$habilitaSp}
                FROM cliente
                LEFT JOIN cond_venta ON cond_venta.Codigo = cliente.id_cv
                LEFT JOIN contribuyentes ON contribuyentes.IDIva = cliente.IDIva
                
                WHERE cliente.Codigo={$codigo}";
    $hacer = mysqli_query($connV, $sqlCliente);
    logError('buscando al cliente=> '.$sqlCliente);
    if(!$hacer){
        logError('error de consulta de cliente err=> '.mysqli_error($connV).' sql: =>'.$sqlCliente);
        print json_encode(array('msg'=>'error','desc'=>'no es posible seleccionar el cliente'));
        exit();
    }

    // * todo bien encontre el cliente
    if ($hacer) {
        $objClienteBusq = mysqli_fetch_object($hacer);
        $sqlAtraso =    "SELECT 
                            MIN(cuentacliente.Fecha) as ultimaf 
                        FROM CuentaCliente 
                        WHERE (cuentacliente.TipoComprobante = 'FA' OR 
                            cuentacliente.TipoComprobante = 'FB' OR 
                            cuentacliente.TipoComprobante = 'FC' OR
                            cuentacliente.TipoComprobante = 'FE' OR 
                            cuentacliente.TipoComprobante = 'FM' OR 
                            cuentacliente.TipoComprobante = 'NDA' OR 
                            cuentacliente.TipoComprobante = 'NDC' OR 
                            cuentacliente.TipoComprobante = 'NDE' OR 
                            cuentacliente.TipoComprobante = 'NDM' OR 
                            cuentacliente.TipoComprobante = 'NDB') AND 
                            cuentacliente.Estado = 'N/Canc' AND 
                            cuentacliente.Anulado = 'No' AND 
                            cuentacliente.Codigo = {$codigo}";

        $hacerDias = mysqli_query($connV, $sqlAtraso) or die('No puedo consultar los dias de atraso' .  mysqli_error($connV));
        $limitesCli = mysqli_fetch_object($hacerDias);
        $autorizaCredito = array();
        if (($limitesCli->ultimaf) && ($limitesCli->ultimaf != '')) {

            //          Resto ultima fecha de F o Nd a la fecha actual
            $datetime1 = strtotime(date('Y-m-d'));
            $datetime2 = strtotime($limitesCli->ultimaf);
            $intervalo = round(abs($datetime1 - $datetime2) / 60 / 60 / 24);
            if ($objClienteBusq->credito_limite_dias != 0 && $intervalo > $objClienteBusq->credito_limite_dias) {
                $aut = 'No Autorizado';
                $detalle = 'Se sobrepaso el limite de vencimiento en dias';
                $autorizaCredito = array(
                    'limite_credito_dias' => 'No autorizado',
                    'dias_credigo' => $objClienteBusq->credito_limite_dias,
                    'dias_exceso_limite' => $intervalo,
                    'exceso' => 1
                );
            } else {
                $autorizaCredito = array(
                    'limite_credito_dias' => 'Autorizado',
                    'dias_exceso_limite' => 0,
                    'dias_credigo' => $objClienteBusq->credito_limite_dias,
                    'exceso' => 0
                );
            }
        }

        // promedio de pago en dias
        //======================================================================
        $sqlPago = "";

        if ($objClienteBusq->IDIva == 1) {
            $ivaIncluido = 'no';
        } else {
            $ivaIncluido = 'si';
        }
        
        $domEntrega= array();

        //          echo '<pre>'.print_r($autorizaCredito).'</pre>';
        if (!empty($objClienteBusq)) {
            //            session_start();
            // buscar domicilios adicionesl
            $domEntrega = buscarDomicilioAdicionales($connV,$codigo);
            // no tengo domicilios adicionales se lo creo a todos y vuelvo a buscar
            if(empty($domEntrega)){
                // sin domicilios de entrega no puedo continar
                logError('sin Domiciliio de entrega');
                print json_encode(array('msg'=>'error','desc'=>'el cliente no tiene domiciliios habilitados'));
                unset($_SESSION['domicilios_cliente']);
                unset($_SESSION['cliente']);
                unset($_SESSION['idcliente']);
                unset($_SESSION['ivaIncluido']);
                exit();
            }
            
            $_SESSION['cliente'] = array($objClienteBusq, $autorizaCredito);
            $_SESSION['idcliente'] = $codigo;
            $_SESSION['domicilios_cliente'] = $domEntrega;
            $_SESSION['ivaIncluido'] = $ivaIncluido;
            unset($_SESSION["jcart"]);
            print json_encode(array('msg'=>'ok','desc'=>'cliente seleccionado'));

            //            echo print_r($objClienteBusq);

        } else {
            // cliente vacio

            // $autorizaCredito = array();
            // $_SESSION['cliente'] = array($objClienteBusq, $autorizaCredito);
            // $_SESSION['idcliente'] = $codigo;
            // $_SESSION['ivaIncluido'] = $ivaIncluido;
            // $_SESSION['domicilios_cliente'] = $domEntrega;
            // unset($_SESSION["jcart"]);
                unset($_SESSION['domicilios_cliente']);
                unset($_SESSION['cliente']);
                unset($_SESSION['idcliente']);
                unset($_SESSION['ivaIncluido']);
            print json_encode(array('msg'=>'error','desc'=>'no es posible seleccionar el cliente'));
        }
    }
}
