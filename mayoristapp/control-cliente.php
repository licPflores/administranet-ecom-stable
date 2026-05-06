<?php
require_once 'conexion.inc.php';

$usuario = mysqli_real_escape_string($connV,$_POST["usuario"]);
$pass = mysqli_real_escape_string($connV,$_POST["clave"]);
//$dispositivo = mysqli_real_escape_string($connV,$_POST["dispositivo"]);


$sql = "SELECT 
            clientes_web.id_usuario_web,
            clientes_web.codigo_usuario,
            clientes_web.tipo_cliente,
            clientes_web.Codigo,
            cliente.nombre_cliente AS cliente,
            cliente.id_cv,
            vj.id_punto_venta,
            vj.id_punto_ventac,
            cond_venta.Descripcion AS condVenta,
            cliente.listaPrecio,
            SUBSTRING(cliente.listaPrecio,6) AS codListaPrecio,
            cliente.Credito,
            cliente.credito_limite_dias,
            cliente.id_sucursal,
            cliente.saldo,
            cliente.CodViajante AS CodViajanteCli,
            cliente.TipoCliente,
            cliente.Email As email,
            cliente.EmailContacto AS emailContacto,
            cliente.Descuento AS descPie,
            cliente.descuento_por_cli AS descRenglon,
            cond_venta.descuento AS descCondventa,
            contribuyentes.IDIva,
            contribuyentes.abreviado,
            vj.CodViajante,
            vj.vendedor,            
            vj.id_deposito,
            vj.id_usuario,
            vj.tipo_busqueda_defecto,
            'Si' AS usa_viajante_cliente,
            sucursales.nombre_sucursal AS sucursal,
            sucursales.salida_sin_stock,
            sucursales.agente_retib,
            sucursales.agente_retg,
            sucursales.agente_reti,
            sucursales.agente_percep
     FROM clientes_web 
        LEFT JOIN cliente ON clientes_web.Codigo = cliente.Codigo
        LEFT JOIN cond_venta ON cond_venta.Codigo = cliente.id_cv
        LEFT JOIN contribuyentes ON contribuyentes.IDIva = cliente.IDIVa
        LEFT JOIN sucursales ON sucursales.id_sucursal = cliente.id_sucursal
        ,(SELECT
                        usuarios.id_usuario,        
                        viajantes.CodViajante,
                        usuarios.id_deposito,
                        viajantes.Nombre AS vendedor ,
                        usuarios.id_punto_venta,
                        usuarios.id_punto_ventac,
                        usuarios.tipo_busqueda_defecto
                        FROM configuracion 
                    LEFT JOIN usuarios ON usuarios.id_usuario=configuracion.id_usuario_web
                    LEFT JOIN viajantes ON viajantes.CodViajante = usuarios.CodViajante) AS vj 
     WHERE clientes_web.codigo_usuario='".$usuario."' 
        AND AES_DECRYPT(clientes_web.clave_usuario,'a7v8xx2')='".$pass."'
        AND cliente.cliente_ecommerce ='Si'";

$ejecutar = mysqli_query($connV,$sql) or die ("No se pudo ejecutar la consulta".mysqli_error($connV));
$hay = mysqli_num_rows($ejecutar);
$campo = mysqli_fetch_object($ejecutar);
if($hay>0){
    /*
     * DATOS DE LA EMPRESA
     */
    $sqlEmpresa = "SELECT 
                            Nombre,
                            Telefono,
                            Cuit,
                            Domicilio,
                            Email,
                            IngBrutos,
                            InicioAct,
                            contribuyentes.IVA,
                            agente_retib,
                            agente_retg,
                            agente_reti,
                            agente_percep
                    FROM datosempresa
                    LEFT JOIN contribuyentes ON contribuyentes.IDIva = datosempresa.IDIva  
                    WHERE id_empresa=1";
    $hacerEmpresa = mysqli_query($connV,$sqlEmpresa) 
                                            or die(
                                                    'No puedo recuperar los datos de la empresa'. mysqli_error($connV).'<br>'.$sqlEmpresa
                                                    );
    $empresa = mysqli_fetch_object($hacerEmpresa);
    
    $sqlSucursal = "SELECT cant_renglon_venta FROM sucursales WHERE id_sucursal=1";
    $hacerSucursal = mysqli_query($connV,$sqlSucursal) or die("No me puedo conectar con la sucursal <br>" . mysqli_error($connV));
    $sucursal = mysqli_fetch_object($hacerSucursal);
    
    /*
     * EMBALAJE
     */
     $sqlConf = "SELECT "
            . "utiliza_embalaje,"
            . "activ_logistica,"
            . "reglas_precios,"
            . "usa_multiplica_bulto_promedio,"
            . "lista_precio_web"
            . " FROM configuracion WHERE id_configuracion = 1";
    $hacerConf = mysqli_query($connV,$sqlConf) or die("No puedo recuperar la configuracion".mysqli_error($connV));
    $permisoEmbalaje = "No";
//    if($hacerConf){
//        $conf = mysqli_fetch_assoc($hacerConf);
//        $permisoEmbalaje = $conf["utiliza_embalaje"];
//        $activ_logistica = $conf["activ_logistica"];
//        $utilizaReglasPrecio = $conf["reglas_precios"];
//        
//    }
    
    if($hacerConf){
        $conf = mysqli_fetch_assoc($hacerConf);
        $permisoEmbalaje = $conf["utiliza_embalaje"];
        $activ_logistica = $conf["activ_logistica"];
        $utilizaReglasPrecio = $conf["reglas_precios"];
        $usaBultoPromedio = $conf["usa_multiplica_bulto_promedio"];
        $usaIdManual ="No";
        $listaPrecioDefecto = "Lista ".$conf["lista_precio_web"];
        if(isset($conf["alta_cliente_web"]) && $conf["alta_cliente_web"]!=""){
            $permisoAltaCliente =$conf["alta_cliente_web"];
        }
        
    }else{
        // no dejo permisos
        
        $permisoEmbalaje = "No";
        $activ_logistica = "No";
        $utilizaReglasPrecio = "No";
        $usaBultoPromedio = "No";
        $usaIdManual ="No";
        $listaPrecioDefecto="Lista 1";
        $permisoAltaCliente ="No";
    }
    
     /*
     * DATOS MAIL usuario vendedor
     * =========================================================================
     */
    
    $sqlCo="SELECT id_usuario,
                    nombre_servidor_smtp,
                    nombre_servidor_pop3,
                    puerto_servidor_smtp,
                    puerto_servidor_pop,
                    nombre_usuario,
                    pass_usuario,
                    firma_mensaje 
                    FROM correo_usr WHERE correo_usr.id_usuario=".$campo->id_usuario;
    $hacerm=mysqli_query($connV,$sqlCo)or die("no puedo recuperar los datos de mail<pre>".mysqli_error($connV)."<br>".$sqlCo."</pre>");
    $arrCo=mysqli_fetch_assoc($hacerm);
    
    /*
     * DIAS DE ATRASO del CLIENTE
     */
    
        $objClienteBusq = $campo;
        $codigo = $objClienteBusq->Codigo;
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
                                        
        $hacerDias = mysqli_query($connV,$sqlAtraso) or die('No puedo consultar los dias de atraso'.  mysqli_error($connV));                              
        $limitesCli = mysqli_fetch_object($hacerDias);
        $autorizaCredito = array();
        if(($limitesCli->ultimaf) && ($limitesCli->ultimaf !='')){
              
//          Resto ultima fecha de F o Nd a la fecha actual
            $datetime1 = strtotime(date('Y-m-d'));
            $datetime2 = strtotime($limitesCli->ultimaf);
            $intervalo = round(abs($datetime1 - $datetime2)/60/60/24); 
            if($objClienteBusq->credito_limite_dias!=0 && $intervalo>$objClienteBusq->credito_limite_dias){
                $aut= 'No Autorizado';
                $detalle = 'Se sobrepaso el limite de vencimiento en dias';
                $autorizaCredito = array(
                                            'limite_credito_dias' => 'No autorizado',
                                            'dias_exceso_limite' => $intervalo ,
                                            'exceso' => 1);
            }else{
                $autorizaCredito = array(
                                            'limite_credito_dias' => 'Autorizado',
                                            'dias_exceso_limite' => 0,
                                            'exceso' => 0);
            }                            
        }
        /* DOMICILIOS DE ENTREGA DEL CLIENTE*/
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
        $hacerDom = mysqli_query($connV,$sqlDomicilios) or die('No puedo recuperar los domicilios' . mysqli_error($connV) .'<pre>'.$sqlDomicilios.'</pre>');
        $domEntrega = array();
        while($dd = mysqli_fetch_assoc($hacerDom)){
            $domEntrega[] = $dd;
        }
        
        
       /*
     * CONFIGURO DISPOSITIVO
     */    
        require_once 'api/Mobile_Detect.php';
        $detect = new Mobile_Detect;
        if($detect->isMobile()||$detect->isTablet()){
            $deviceType ="tablet";
        }else{
            $deviceType ="computer";
        }
        //$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
        $caminoDisp = "";
        //$deviceType="c";
        $usaZoom = 0;
        $cantMasVendidos = 4;
        $usaLaboratorio="si";
        
        
        if($deviceType!="computer"){
            $caminoDisp = "tmobile/";
            
        }
        
        /*
         * Colores en Rubros
         */
        $colorRubro = array();
        /*
         * 1 - descartables  #00acd4
         * 2 - higiene #22b54b
         * 3 - comestibles #f8931f
         */
        $colorRubro[1] = 'style="border-bottom:3px solid #00A4CC"'; 
        $colorRubro[2] = 'style="border-bottom:3px solid #109618"'; 
        $colorRubro[3] = 'style="border-bottom:3px solid #FF9900"'; 
        
        /*
         * Verifico el tipo de iva del cliente
         */
        if($campo->IDIva == 1){
            $ivaIncluido = 'no';
        }else{
            $ivaIncluido = 'si';
        }
            
        
         /**
         * Fecha Entrega con 48 hs o 2 dias
         * ================================
         */
        $arrDiaNoLaborable =array(7);
        $cantDiasEntrega=2;
        
        
	//entre el usuario existe y ahora tengo que comprobar que no haya iniciado sesion antes.////
        session_start();
        session_destroy();
        unset($_SESSION);
        session_start();  
        
        /*solo amicosa*/
//            $arrTipo=array(21,19,2,3);
//            if(!in_array($campo->TipoCliente,$arrTipo)){
//                $categNo=array(3);
//            }
        /**/
            
//        inicializo las sesiones
        $_SESSION['id_sesion']         = session_id();
        $_SESSION['usuario']            = $campo->codigo_usuario;
        $_SESSION['nombre_empresa']     = $empresa->Nombre;
        $_SESSION['telefono_empresa']   = $empresa->Telefono;
        $_SESSION['cuit_empresa']       = $empresa->Cuit;
        $_SESSION['domicilio_empresa']  = $empresa->Domicilio;
        $_SESSION['apenom']             = $campo->cliente;
        $_SESSION['idusuario']          = $campo->id_usuario_web;
        $_SESSION['nombre_empresa']     = $empresa->Nombre;
        $_SESSION['telefono_empresa']   = $empresa->Telefono;
        $_SESSION['cuit_empresa']       = $empresa->Cuit;
        $_SESSION['domicilio_empresa']  = $empresa->Domicilio;
        $_SESSION['email_empresa']      = $empresa->Email;
        $_SESSION['ingbrutos_empresa']  = $empresa->IngBrutos;
        $_SESSION['iniact_empresa']     = $empresa->InicioAct;
        $_SESSION['iva_empresa']        = $empresa->IVA;
        $_SESSION['agente_retib']       = $campo->agente_retib;
        $_SESSION['agente_retg']        = $campo->agente_retg;
        $_SESSION['agente_reti']        = $campo->agente_reti;
        $_SESSION['agente_percep']      = $campo->agente_percep;
        $_SESSION['venta_sin_stock']    = 'Si';
        $_SESSION['obliga_domicilio_cliente'] = 'No';
        $_SESSION['domicilios_cliente'] = $domEntrega;
        $_SESSION['tipocliente']        = $campo->tipo_cliente;
        $_SESSION['tipousuario']        = 'cliente';
        $_SESSION['cliente']            = array($objClienteBusq,$autorizaCredito);
        $_SESSION['idcliente']          = $codigo;
        $_SESSION['limite_renglon']     = $sucursal->cant_renglon_venta;
        
        $_SESSION['tipo_busqueda']      = $campo->tipo_busqueda_defecto;
        /* cambiar el deposito por defecto para los clientes.*/
        $_SESSION['deposito']           = $campo->id_deposito;
        $_SESSION['caminoDisp']         = $caminoDisp;
        $_SESSION['formulario']         ='pedido';
        $_SESSION['usaRemito']          = 'No';
        $_SESSION['utilizaEmbalaje']    = $permisoEmbalaje;
        $_SESSION['colorRubro']         = $colorRubro;
        $_SESSION['ivaIncluido']        = $ivaIncluido;
        $_SESSION['totalCarrito']       = 0;
        $_SESSION['inf_gerenciales']    = 'No';
        $_SESSION['activ_logistica']    = $activ_logistica;
        $_SESSION['usaReglaPrecio']     = $utilizaReglasPrecio;
        $_SESSION['utiliza_control_horario'] = $controlHorario;
        $_SESSION['uso_bulto_promedio'] = $usaBultoPromedio;
        $_SESSION['arr_dias_no_laborables'] = $arrDiaNoLaborable;
        $_SESSION['cant_dias_entrega'] = $cantDiasEntrega;
        $_SESSION['usa_id_manual'] = $usaIdManual;
        $_SESSION['baseConecto']        = $baseConecto;
        $_SESSION['servidor']           = $servidor;
        $_SESSION['supervisor_venta'] = 'No';
        $_SESSION['vendedor_a_cargo'] = array();
        $_SESSION['lista_precio_defecto'] = $campo->listaPrecio;
        $_SESSION['permiso_alta_cliente']='No';
        $_SESSION['contacto_completo']=array();
        $_SESSION['id_bd'] = $idEmpresa;
        $_SESSION['correo'] = $arrCo;
        $_SESSION['usa_viajante_cliente']= $campo->usa_viajante_cliente;
        $_SESSION["verStock"]="No";
        if(isset($categNo)){
            $_SESSION["categoriaNo"] = $categNo;
        }
        
        if(!isset($_SESSION['id_sesion'])){
        /*
         * No se creo el usuario
         */
            header('Location:../index.php?cartel=1');
        }else{
    //        echo "vamos al escritorio";
            header('Location: escritorio.php');
        }
        
}else{
//    echo "pa la casa";
    header('Location: ../index.php?cartel=2');

}